<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTticketlink')) {
    return;
}


/**
 * Links to a ticket that survive being stored. (Roadmap 4.0-CORE-01)
 *
 * A ticket has two addresses — the admin screen and the front-end screen — and
 * which one is right depends on who is *reading*, not on who wrote the text. A
 * link baked into stored content therefore works for exactly one audience: the
 * merge feature wrote its cross-links from the agent's side, so the customer got
 * an /wp-admin/ address they cannot open, and a merge done by a customer left
 * the agent with a front-end address instead.
 *
 * So the link carries the ticket id with it, in a data attribute, and the href is
 * resolved when the content is rendered:
 *
 *   JSSTticketlink::anchor($jsst_id)     when the text is written and stored
 *   JSSTticketlink::resolve($jsst_html)  immediately before it is displayed
 *
 * resolve() also repairs links stored by earlier versions, which have no marker
 * but do carry jssupportticketid in the href — an old merge reply starts working
 * for both sides as soon as it is next rendered, with nothing to migrate.
 *
 * Run resolve() BEFORE wp_kses_post(): kses strips the data attribute, which is
 * fine once the href has been rewritten, but useless afterwards.
 */
class JSSTticketlink {

    /** Attribute that marks an anchor as pointing at a ticket. */
    const MARKER = 'data-jsst-ticket';

    /**
     * The address of a ticket for one side of the plugin.
     *
     * @param int       $jsst_id       Ticket id.
     * @param bool|null $jsst_foradmin true for the admin screen, false for the
     *                                 front end, null to follow the context the
     *                                 caller is rendering in.
     */
    public static function url($jsst_id, $jsst_foradmin = null) {
        $jsst_id = (int) $jsst_id;
        if ($jsst_foradmin === null) {
            $jsst_foradmin = is_admin();
        }
        if ($jsst_foradmin) {
            return admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . $jsst_id);
        }
        return jssupportticket::makeUrl(array(
            'jstmod'            => 'ticket',
            'jstlay'            => 'ticketdetail',
            'jssupportticketid' => $jsst_id,
            'jsstpageid'        => jssupportticket::getPageid(),
        ));
    }

    /**
     * An anchor to store inside a reply, a note or an activity entry.
     *
     * The href written here is the customer-facing one: a stored message is read
     * on the front end far more often than in wp-admin, and every screen that
     * calls resolve() overrides it anyway. The marker is what actually matters.
     */
    public static function anchor($jsst_id, $jsst_label = '') {
        $jsst_id = (int) $jsst_id;
        if ($jsst_label === '') {
            $jsst_label = '#' . $jsst_id;
        }
        return '<a class="jsst-ticket-link" ' . self::MARKER . '="' . $jsst_id . '"'
             . ' href="' . esc_url(self::url($jsst_id, false)) . '">' . esc_html($jsst_label) . '</a>';
    }

    /**
     * Point every ticket link in a block of HTML at the screen the reader is on.
     */
    public static function resolve($jsst_html) {
        if (!is_string($jsst_html) || stripos($jsst_html, '<a') === false) {
            return $jsst_html;
        }
        return preg_replace_callback('/<a\s[^>]*>/i', array(__CLASS__, 'resolveTag'), $jsst_html);
    }

    /**
     * One opening <a> tag: find the ticket it points at, rewrite its href.
     */
    private static function resolveTag($jsst_match) {
        $jsst_tag = $jsst_match[0];
        $jsst_href = null;
        if (preg_match('/href\s*=\s*(["\'])(.*?)\1/i', $jsst_tag, $jsst_hrefmatch, PREG_OFFSET_CAPTURE)) {
            $jsst_href = $jsst_hrefmatch;
        }

        $jsst_id = 0;
        if (preg_match('/' . self::MARKER . '\s*=\s*["\']?(\d+)/i', $jsst_tag, $jsst_marker)) {
            $jsst_id = (int) $jsst_marker[1];
        } elseif ($jsst_href !== null) {
            // Written before the marker existed. Both spellings of the address
            // name the ticket the same way, so the id is still recoverable, but
            // only rewrite a link that is unmistakably one of ours.
            $jsst_url = $jsst_href[2][0];
            if (preg_match('/jssupportticketid=(\d+)/', $jsst_url, $jsst_old)
                    && preg_match('/(jstlay=ticketdetail|jstmod=ticket|page=ticket)/', $jsst_url)) {
                $jsst_id = (int) $jsst_old[1];
            }
        }
        if ($jsst_id < 1) {
            return $jsst_tag;
        }

        // substr_replace, not preg_replace: a URL containing $ or \ would be read
        // as a back-reference in a replacement string.
        $jsst_new = 'href="' . esc_url(self::url($jsst_id)) . '"';
        if ($jsst_href !== null) {
            return substr_replace($jsst_tag, $jsst_new, $jsst_href[0][1], strlen($jsst_href[0][0]));
        }
        return '<a ' . $jsst_new . substr($jsst_tag, 2);
    }

}

?>
