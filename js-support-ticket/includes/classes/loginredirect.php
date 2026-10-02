<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Where somebody lands after logging in, decided once their role is known.
 *
 * ## The bug this exists to end
 *
 * A guest who opens My Tickets is shown `getUserGuest()` - "you are not logged
 * in" and a login link. That link has always carried the page to come back to
 * as `js_redirecturl`, and the page it carried was built like this:
 *
 *     $jsst_redirect_url = jssupportticket::makeUrl(
 *         array('jstmod' => 'ticket', 'jstlay' => 'myticket'));
 *
 * Which is the *customer* My Tickets. It is the right answer for a customer and
 * the wrong one for everybody else, and the template cannot tell the difference,
 * because at the moment it runs there is nobody to ask: the visitor is a guest.
 * So an agent who arrived at the desk, logged in on that page, and expected
 * their queue got the customer list of their own tickets instead - usually
 * empty, which reads as "the help desk has lost my work" rather than as a
 * routing mistake.
 *
 * ## Deciding late rather than guessing early
 *
 * The destination is not a URL until somebody has logged in. So the link carries
 * a *name* for where to go - `mytickets` - as a query argument on a real URL,
 * and `resolve()` turns that name into an address on `login_redirect`, which
 * WordPress fires after authentication with the user it authenticated.
 *
 * It has to be a real URL and not a bare token: `wp-login.php` puts
 * `redirect_to` through `wp_safe_redirect()`, and anything that is not a
 * same-host URL is discarded in favour of the dashboard. So the tag rides along
 * on the customer address, which is both a valid fallback if this class never
 * runs and the correct answer for the majority of the people who will use it.
 *
 * ## Why not read the role in the template and write the right URL there
 *
 * Because there is no role in the template. The alternative that looks simpler -
 * work it out on the login page instead - is the same bug moved: the login page
 * is also being read by a guest. Anything decided before the password is
 * checked is decided without knowing who is typing it.
 *
 * ## Scope
 *
 * Only destinations that genuinely differ by role belong in the map below. A
 * page that is the same page for everybody needs nothing here - the existing
 * `js_redirecturl` already carries it correctly, and adding an entry would put
 * a second answer in front of a question that only has one.
 */
class JSSTloginredirect {

    /** The query argument naming a role-dependent destination. */
    const ARG = 'jsst_after_login';

    /**
     * Destinations whose address depends on who is asking.
     *
     * `user` is what a customer gets and `staff` what an agent gets. The
     * customer entry doubles as the fallback, which is why it is also the URL
     * the tag is attached to.
     */
    private static $jsst_destinations = array(
        'mytickets' => array(
            'user'  => array('jstmod' => 'ticket', 'jstlay' => 'myticket'),
            'staff' => array('jstmod' => 'agent',  'jstlay' => 'staffmyticket'),
        ),
    );

    public static function registerHooks() {
        /* Priority 20, after anything a site has hooked at the default, because
           this is answering a narrower question than a general "send everybody
           to X" rule and should be able to override one for its own tagged
           links without disturbing the rest. */
        add_filter('login_redirect', array(__CLASS__, 'resolve'), 20, 3);
    }

    /** The map, filterable so an add-on can register a destination of its own. */
    public static function destinations() {
        return (array) apply_filters('jsst_login_destinations', self::$jsst_destinations);
    }

    /**
     * The URL to send a guest to log in *for*, tagged so that it can be decided
     * again once there is somebody to decide it about.
     *
     * Hand this to `JSSTlayout::getUserGuest()` in place of a hard-coded
     * customer address.
     *
     * @param string $jsst_key A key of the destination map.
     * @return string A URL, untagged and unchanged if the key is not one of ours.
     */
    public static function destination($jsst_key) {
        $jsst_all = self::destinations();
        if (!isset($jsst_all[$jsst_key])) {
            return jssupportticket::makeUrl(array(
                'jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel'));
        }
        return add_query_arg(self::ARG, $jsst_key, self::url($jsst_all[$jsst_key]['user']));
    }

    /**
     * Turn a tagged redirect into the address this particular user wants.
     *
     * Left alone if it is not tagged, or if the tag is not one of ours - which
     * covers every ordinary WordPress login on the site, including the ones
     * that never came near this plugin.
     *
     * @param string          $jsst_redirect_to Where WordPress means to send them.
     * @param string          $jsst_request     What was asked for.
     * @param WP_User|WP_Error $jsst_user       Who logged in, or why they did not.
     */
    public static function resolve($jsst_redirect_to, $jsst_request, $jsst_user) {
        if (!is_a($jsst_user, 'WP_User')) {
            return $jsst_redirect_to;    // a failed login; not ours to route
        }

        $jsst_key = self::tag($jsst_redirect_to);
        if ($jsst_key === '') {
            $jsst_key = self::tag($jsst_request);
        }
        if ($jsst_key === '') {
            return $jsst_redirect_to;
        }

        $jsst_all = self::destinations();
        if (!isset($jsst_all[$jsst_key])) {
            return $jsst_redirect_to;
        }

        $jsst_which = self::isStaff($jsst_user->ID) ? 'staff' : 'user';
        if (!isset($jsst_all[$jsst_key][$jsst_which])) {
            $jsst_which = 'user';
        }
        return self::url($jsst_all[$jsst_key][$jsst_which]);
    }

    /**
     * Is this WordPress user an agent on this desk?
     *
     * Asked of the desk's own roster rather than of WordPress roles, because
     * that is what every other "is this person staff" check in the product
     * asks - an agent is a `js_ticket_staff` row, and the WordPress role is not
     * evidence either way.
     *
     * Asked by id rather than of the current user, which matters here and
     * nowhere else: `login_redirect` fires from `wp-login.php` between
     * authentication and the redirect, and the authenticated user is not
     * necessarily the one `wp_get_current_user()` would name at that moment.
     * The user WordPress has just verified arrives as an argument, and that is
     * the only one worth asking about.
     *
     * ## Two id spaces, and the translation between them
     *
     * `$jsst_wpuid` is a WordPress user id. `isUserStaff()` does not take one.
     * Its parameter defaults to `JSSTincluder::getObjectClass('user')->uid()`,
     * which is the desk's own `js_ticket_users.id` - a different number for the
     * same person - and it looks that number up in `js_ticket_staff.uid`. Every
     * other query against that column agrees: the roster joins read
     * `WHERE user.id = staff.uid` against `js_ticket_users`, and availability
     * goes `staff.uid` -> `js_ticket_users` -> `wpuid` when it wants the
     * WordPress account.
     *
     * Handing it the WordPress id compared one id space against the other. That
     * is not a check that fails safe - it is a check that answers about
     * whichever unrelated person happens to hold that number, so an agent was
     * routed as a customer and some customer was routed as an agent, exactly
     * swapped and perfectly consistent about it. On the desk this was found on,
     * the one agent was `js_ticket_users.id` 3 / WordPress id 4, and WordPress
     * id 3 was a customer: the agent got the customer's list and the customer
     * got the agent's.
     *
     * So the WordPress id is translated first, and a person with no desk record
     * at all - possible for an account that has never opened a ticket - is a
     * customer, which is what they would have been told anyway. (Roadmap 4.5-FE-02)
     */
    private static function isStaff($jsst_wpuid) {
        if ((int) $jsst_wpuid <= 0) {
            return false;
        }
        if (!in_array('agent', (array) jssupportticket::$_active_addons)) {
            return false;    // no agents on this site; everybody is a customer
        }
        $jsst_user = JSSTincluder::getObjectClass('user');
        if (!is_object($jsst_user) || !method_exists($jsst_user, 'getUserIDByWPUid')) {
            return false;
        }
        $jsst_deskid = $jsst_user->getUserIDByWPUid((int) $jsst_wpuid);
        if (!is_numeric($jsst_deskid) || (int) $jsst_deskid <= 0) {
            return false;    // no desk record; nothing on the roster to match
        }
        $jsst_model = JSSTincluder::getJSModel('agent');
        if (!is_object($jsst_model) || !method_exists($jsst_model, 'isUserStaff')) {
            return false;
        }
        return (bool) $jsst_model->isUserStaff((int) $jsst_deskid);
    }

    /** The destination key carried by a URL, or '' when it carries none. */
    private static function tag($jsst_url) {
        if (!is_string($jsst_url) || $jsst_url === '') {
            return '';
        }
        $jsst_query = (string) wp_parse_url($jsst_url, PHP_URL_QUERY);
        if ($jsst_query === '') {
            return '';
        }
        $jsst_args = array();
        wp_parse_str($jsst_query, $jsst_args);
        if (empty($jsst_args[self::ARG])) {
            return '';
        }
        return sanitize_key($jsst_args[self::ARG]);
    }

    private static function url($jsst_route) {
        return jssupportticket::makeUrl($jsst_route);
    }

}
