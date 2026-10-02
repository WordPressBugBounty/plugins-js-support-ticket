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
if (class_exists('JSSTpermissioninspector')) {
    return;
}

/**
 * The effective-permission inspector. (Roadmap 4.5-ARCH-04)
 *
 * "Why can't Sara close this ticket?" is the most expensive question this
 * product produces. Answering it today means opening four screens, holding the
 * WordPress role, the help-desk role, the department scope and the per-agent
 * permissions in your head at once, and then guessing which one won.
 *
 * This asks the software instead. Pick a person, optionally name a ticket, and
 * every action is listed with the answer and the ordered reasoning behind it —
 * the same reasoning the software used, because it is the same call. There is
 * no second implementation of the rules here: this class formats what
 * JSSTcapability decided, and if the two ever disagreed the inspector would be
 * worthless, so they cannot be allowed to.
 *
 * It is a read-only screen. It never acts on a ticket, and it never signs in as
 * anybody: it asks the capability service about an actor, which is possible
 * only because that service takes the actor as an argument.
 */
class JSSTpermissioninspector {

    /**
     * Everybody worth asking about — anyone who could plausibly be the subject
     * of the question. Administrators, agents, and anybody holding a help-desk
     * capability from a WordPress role, which is the group most likely to hold
     * access nobody remembers granting.
     *
     * @return array wpuid => label
     */
    public static function people() {
        $jsst_people = array();
        /* The same derived list the Agent Access screen is built from, rather
           than a get_users() capability query — that argument only arrived in
           WordPress 5.9 and this plugin supports older, and the two screens
           disagreeing about who counts as an agent would be its own bug. */
        foreach (JSSTagentaccess::capabilityHolders() as $jsst_holder) {
            $jsst_people[(int) $jsst_holder['wpuid']] = ($jsst_holder['user_email'] !== '')
                ? $jsst_holder['display_name'] . ' (' . $jsst_holder['user_email'] . ')'
                : $jsst_holder['display_name'];
        }
        foreach (get_users(array('role' => 'administrator', 'fields' => array('ID', 'display_name', 'user_email'))) as $jsst_user) {
            $jsst_people[(int) $jsst_user->ID] = self::label($jsst_user);
        }
        /* Agents come from the Agents list rather than from a capability,
           because that list is what grants them their access at check time —
           somebody added there this morning holds no capability of their own
           and would otherwise be missing from the one screen built to explain
           them. */
        foreach (JSSTagentaccess::agents() as $jsst_agent) {
            $jsst_wpuid = isset($jsst_agent->wpuid) ? (int) $jsst_agent->wpuid : 0;
            if ($jsst_wpuid <= 0 || isset($jsst_people[$jsst_wpuid])) {
                continue;
            }
            $jsst_user = get_userdata($jsst_wpuid);
            if ($jsst_user) {
                $jsst_people[$jsst_wpuid] = self::label($jsst_user);
            }
        }
        asort($jsst_people);
        return $jsst_people;
    }

    private static function label($jsst_user) {
        $jsst_name = isset($jsst_user->display_name) ? $jsst_user->display_name : '';
        $jsst_email = isset($jsst_user->user_email) ? $jsst_user->user_email : '';
        return ($jsst_email !== '') ? $jsst_name . ' (' . $jsst_email . ')' : $jsst_name;
    }

    /**
     * The whole answer for one person, optionally against one ticket.
     *
     * @param int $jsst_wpuid    Who is being asked about. 0 is a guest, which
     *                           is a legitimate question: it is what an
     *                           unauthenticated request can reach.
     * @param int $jsst_ticketid Which ticket, or 0 for "in general".
     * @return array
     */
    public static function report($jsst_wpuid, $jsst_ticketid = 0) {
        $jsst_wpuid = (int) $jsst_wpuid;
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = JSSTcapability::actor($jsst_wpuid);

        $jsst_subject = array();
        $jsst_ticket = false;
        if ($jsst_ticketid > 0) {
            $jsst_ticket = JSSTcapability::ticket($jsst_ticketid);
            $jsst_subject = array('ticket' => $jsst_ticketid);
        }

        $jsst_groups = array();
        foreach (JSSTcapability::actions() as $jsst_action => $jsst_ignored) {
            $jsst_def = JSSTcapability::definition($jsst_action);
            if ($jsst_def === false) {
                continue;
            }
            $jsst_decision = JSSTcapability::explain($jsst_action, $jsst_subject, $jsst_actor);
            $jsst_groups[$jsst_def['group']][] = array(
                'action'  => $jsst_action,
                'label'   => $jsst_def['label'],
                'scoped'  => !empty($jsst_def['scoped']),
                'allowed' => !empty($jsst_decision['allowed']),
                'reason'  => $jsst_decision['reason'],
                'code'    => $jsst_decision['code'],
                'trace'   => $jsst_decision['trace'],
            );
        }

        return array(
            'actor'    => $jsst_actor,
            'summary'  => self::summary($jsst_actor, $jsst_ticket),
            'ticket'   => $jsst_ticket,
            'ticketid' => $jsst_ticketid,
            'groups'   => $jsst_groups,
            'warnings' => self::warnings($jsst_actor),
        );
    }

    /**
     * The two or three sentences that answer most questions before anybody
     * reads the table.
     */
    private static function summary($jsst_actor, $jsst_ticket) {
        $jsst_lines = array();
        $jsst_lines[] = sprintf(
            /* translators: 1: person's name, 2: what kind of actor they are */
            esc_html(__('%1$s is %2$s.', 'js-support-ticket')),
            esc_html($jsst_actor['display']), JSSTcapability::kindLabel($jsst_actor['kind'])
        );
        $jsst_lines[] = sprintf(
            /* translators: %s: which tickets this person can see */
            esc_html(__('Tickets they can reach: %s.', 'js-support-ticket')),
            JSSTcapability::scopeLabel($jsst_actor['scope'])
        );
        if (!empty($jsst_actor['governed'])) {
            $jsst_lines[] = esc_html(__('They are on the Agents list, so the per-agent permissions on that screen decide what they may do — a WordPress role cannot add anything back that was switched off there.', 'js-support-ticket'));
        } elseif ($jsst_actor['kind'] === JSSTcapability::ACTOR_AGENT) {
            $jsst_lines[] = esc_html(__('They are not on the Agents list, so their WordPress role decides everything.', 'js-support-ticket'));
        }
        if ($jsst_ticket) {
            $jsst_lines[] = sprintf(
                /* translators: 1: ticket reference, 2: assignment state */
                esc_html(__('Ticket %1$s is %2$s.', 'js-support-ticket')),
                esc_html($jsst_ticket->ticketid),
                ((int) $jsst_ticket->staffid > 0 && (int) $jsst_ticket->staffid === (int) $jsst_actor['staffid'])
                    ? esc_html(__('assigned to this person', 'js-support-ticket'))
                    : (((int) $jsst_ticket->staffid > 0)
                        ? esc_html(__('assigned to somebody else', 'js-support-ticket'))
                        : esc_html(__('unassigned', 'js-support-ticket')))
            );
        }
        return $jsst_lines;
    }

    /**
     * States that are not answers to the question asked, but are worth saying
     * out loud on the screen that explains access.
     */
    private static function warnings($jsst_actor) {
        $jsst_warnings = array();
        if ($jsst_actor['kind'] === JSSTcapability::ACTOR_AGENT && empty($jsst_actor['governed']) && !empty($jsst_actor['caps'])) {
            $jsst_warnings[] = esc_html(__('This person can work tickets because of a WordPress capability, but is not on the Agents list. Nothing on the Agents screen limits them.', 'js-support-ticket'));
        }
        if (!empty($jsst_actor['governed']) && empty($jsst_actor['active'])) {
            $jsst_warnings[] = esc_html(__('This agent is switched off, so everything below is refused regardless of their permissions.', 'js-support-ticket'));
        }
        if ($jsst_actor['kind'] === JSSTcapability::ACTOR_AGENT && !empty($jsst_actor['all_tickets']) && empty($jsst_actor['governed'])) {
            $jsst_warnings[] = esc_html(__('Without the Agents add-on there is no department scoping, so this agent sees every ticket on the site.', 'js-support-ticket'));
        }
        if ($jsst_actor['kind'] === JSSTcapability::ACTOR_CUSTOMER && $jsst_actor['uid'] <= 0) {
            $jsst_warnings[] = esc_html(__('This WordPress account has no help desk user record yet, so it owns no tickets and can see none.', 'js-support-ticket'));
        }
        return $jsst_warnings;
    }

    /**
     * Turn a ticket reference typed by a person into an id.
     *
     * Administrators paste the customer-facing reference, not the row id, and a
     * screen that only accepts the row id is one they stop using.
     */
    public static function resolveTicket($jsst_reference) {
        $jsst_reference = trim((string) $jsst_reference);
        if ($jsst_reference === '') {
            return 0;
        }
        if (ctype_digit($jsst_reference)) {
            $jsst_byid = JSSTcapability::ticket((int) $jsst_reference);
            if ($jsst_byid) {
                return (int) $jsst_byid->id;
            }
        }
        $jsst_id = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT id FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE ticketid = %s',
            $jsst_reference
        ));
        return $jsst_id ? (int) $jsst_id : 0;
    }

    /** The readable name of one action group. */
    public static function groupLabel($jsst_group) {
        $jsst_labels = array(
            'ticket'    => __('Working a ticket', 'js-support-ticket'),
            'state'     => __('Moving a ticket through its life', 'js-support-ticket'),
            'queue'     => __('The queue', 'js-support-ticket'),
            'people'    => __('Customers and reporting', 'js-support-ticket'),
            'knowledge' => __('Knowledge and drafting', 'js-support-ticket'),
            'admin'     => __('Administration', 'js-support-ticket'),
            'other'     => __('Other', 'js-support-ticket'),
        );
        return isset($jsst_labels[$jsst_group]) ? $jsst_labels[$jsst_group] : $jsst_group;
    }
}
