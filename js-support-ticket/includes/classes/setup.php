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
if (class_exists('JSSTsetup')) {
    return;
}

/**
 * Five-minute setup. (Roadmap 4.0-UX-01)
 *
 * The plugin already had a wizard, but it is a tour of settings — date formats,
 * ticket numbering — and a site can finish every page of it and still not be
 * able to take a ticket. This is the other thing: six checks that answer "is
 * this help desk actually working yet?", each one verified against the database
 * rather than against a "you have seen this screen" flag.
 *
 * That distinction is the whole design. A checklist that remembers being clicked
 * tells an administrator what they did; a checklist that re-derives its state
 * tells them what is true. Only the second kind is any use when somebody
 * inherits a site, or when a page gets deleted six months later.
 *
 * Two of the six can be done from here in one click, because they need no
 * decisions. The rest link to the screen that already does that job properly —
 * there is no value in a second, worse department form living in a wizard.
 */
class JSSTsetup {

    /** Marks the checklist dismissed. Progress itself is never stored. */
    const DISMISSED_OPTION = 'jsst_setup_dismissed';

    /** The page the portal shortcode lives on. */
    const PORTAL_SLUG = 'js-support-ticket-controlpanel';

    /**
     * Is there a published page carrying the portal shortcode?
     *
     * Checked by content rather than by the stored page id, because the id is a
     * setting and the page is a fact. A site whose page was trashed still has
     * the id pointing at it.
     */
    public static function portalPage() {
        $jsst_pageid = (int) JSSTincluder::getJSModel('configuration')->getConfigValue('default_pageid');
        if ($jsst_pageid > 0) {
            $jsst_post = get_post($jsst_pageid);
            if ($jsst_post && $jsst_post->post_status === 'publish' && has_shortcode((string) $jsst_post->post_content, 'jssupportticket')) {
                return $jsst_post;
            }
        }
        // The configured page is gone or unpublished. Fall back to finding one.
        $jsst_found = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            's'              => '[jssupportticket',
            'fields'         => 'ids',
        ));
        foreach ((array) $jsst_found as $jsst_id) {
            $jsst_post = get_post($jsst_id);
            if ($jsst_post && has_shortcode((string) $jsst_post->post_content, 'jssupportticket')) {
                return $jsst_post;
            }
        }
        return null;
    }

    /**
     * Create the portal page and point the setting at it.
     *
     * Safe to run twice: an existing page is adopted rather than duplicated,
     * which matters because the most common way to end up here is a page that
     * was unpublished rather than deleted.
     */
    public static function createPortalPage() {
        $jsst_existing = get_page_by_path(self::PORTAL_SLUG, OBJECT, 'page');
        if ($jsst_existing) {
            if ($jsst_existing->post_status !== 'publish') {
                wp_update_post(array('ID' => $jsst_existing->ID, 'post_status' => 'publish'));
            }
            $jsst_pageid = $jsst_existing->ID;
        } else {
            $jsst_pageid = wp_insert_post(array(
                'post_name'    => self::PORTAL_SLUG,
                'post_title'   => 'JS Help Desk',
                'post_status'  => 'publish',
                'post_content' => '[jssupportticket]',
                'post_type'    => 'page',
            ));
        }
        if (is_wp_error($jsst_pageid) || !$jsst_pageid) {
            return false;
        }
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_config` SET configvalue = %s WHERE configname = 'default_pageid'",
            (string) $jsst_pageid
        ));
        update_option('rewrite_rules', '');
        return (int) $jsst_pageid;
    }

    /** How many rows a table has matching a condition, guarding a missing table. */
    private static function countRows($jsst_table, $jsst_where = '1 = 1') {
        $jsst_full = jssupportticket::$_db->prefix . $jsst_table;
        $jsst_exists = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_full));
        if (!$jsst_exists) {
            return 0;
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from the wpdb prefix, condition is a literal from this file.
        return (int) jssupportticket::$_db->get_var("SELECT COUNT(*) FROM `" . $jsst_full . "` WHERE " . $jsst_where);
    }

    /**
     * Is a usable sender address configured?
     */
    public static function senderConfigured() {
        $jsst_sender = JSSTincluder::getJSModel('email')->getDefaultSender();
        return (!empty($jsst_sender['email']) && is_email($jsst_sender['email'])) ? $jsst_sender : null;
    }

    /**
     * The six things that have to be true before this help desk works.
     *
     * Each step reports its own state, so the screen is a rendering of reality
     * rather than a stored position in a flow.
     */
    public static function steps() {
        $jsst_portal   = self::portalPage();
        $jsst_sender   = self::senderConfigured();
        $jsst_depts    = self::countRows('js_ticket_departments', 'status = 1');
        $jsst_tickets  = self::countRows('js_ticket_tickets');
        $jsst_hasagent = in_array('agent', jssupportticket::$_active_addons);
        $jsst_agents   = $jsst_hasagent ? self::countRows('js_ticket_staff', 'status = 1') : 0;
        $jsst_mail     = class_exists('JSSTmailhealth') ? JSSTmailhealth::lastResult() : array();

        $jsst_steps = array();

        $jsst_steps['portal'] = array(
            'title'  => esc_html(__('A page your customers can use', 'js-support-ticket')),
            'why'    => esc_html(__('The help desk lives on a page carrying its shortcode. Without one, customers have nowhere to raise or read a ticket.', 'js-support-ticket')),
            'done'   => (bool) $jsst_portal,
            'detail' => $jsst_portal
                    ? sprintf(
                        /* translators: %s: the page title */
                        esc_html(__('Using the page "%s".', 'js-support-ticket')),
                        esc_html(get_the_title($jsst_portal))
                    )
                    : esc_html(__('No published page carries the [jssupportticket] shortcode.', 'js-support-ticket')),
            'action' => $jsst_portal ? '' : esc_html(__('Create the page', 'js-support-ticket')),
            'task'   => 'createportalpage',
            'link'   => $jsst_portal ? get_permalink($jsst_portal) : '',
        );

        $jsst_steps['sender'] = array(
            'title'  => esc_html(__('An address to send from', 'js-support-ticket')),
            'why'    => esc_html(__('Every notification goes out as this address. It should be on your own domain, or receiving servers will treat it as forged.', 'js-support-ticket')),
            'done'   => (bool) $jsst_sender,
            'detail' => $jsst_sender
                    // A sender with no display name reads as "<a@b.com>" with a
                    // stray pair of brackets if the name is pasted in blindly.
                    ? esc_html(trim($jsst_sender['name']) !== ''
                            ? trim($jsst_sender['name']) . ' <' . $jsst_sender['email'] . '>'
                            : $jsst_sender['email'])
                    : esc_html(__('No sender address is set, so notifications go out as whatever WordPress decides.', 'js-support-ticket')),
            'action' => esc_html(__('Set the sender', 'js-support-ticket')),
            'url'    => admin_url('admin.php?page=email'),
        );

        $jsst_steps['department'] = array(
            'title'  => esc_html(__('Somewhere for tickets to go', 'js-support-ticket')),
            'why'    => esc_html(__('Every ticket belongs to a department. One is enough to start; more can be added whenever you need them.', 'js-support-ticket')),
            'done'   => ($jsst_depts > 0),
            'detail' => $jsst_depts > 0
                    ? sprintf(
                        /* translators: %d: how many active departments exist */
                        esc_html(_n('%d active department.', '%d active departments.', $jsst_depts, 'js-support-ticket')),
                        $jsst_depts
                    )
                    : esc_html(__('There are no active departments yet.', 'js-support-ticket')),
            'action' => esc_html(__('Add a department', 'js-support-ticket')),
            'url'    => admin_url('admin.php?page=department&jstlay=adddepartment'),
        );

        $jsst_steps['agent'] = array(
            'title'  => esc_html(__('Somebody to answer them', 'js-support-ticket')),
            'why'    => $jsst_hasagent
                    ? esc_html(__('Agents work tickets without needing full access to your WordPress site.', 'js-support-ticket'))
                    : esc_html(__('Without the Agents add-on, administrators answer tickets. That is a complete setup — nothing is missing.', 'js-support-ticket')),
            // Not applicable is a finished state, not a failure: a one-person
            // help desk is a legitimate configuration and should not be shown a
            // permanently unfinished checklist.
            'done'   => (!$jsst_hasagent || $jsst_agents > 0),
            'detail' => !$jsst_hasagent
                    ? esc_html(__('Administrators answer tickets on this site.', 'js-support-ticket'))
                    : ($jsst_agents > 0
                        ? sprintf(
                            /* translators: %d: how many active agents exist */
                            esc_html(_n('%d active agent.', '%d active agents.', $jsst_agents, 'js-support-ticket')),
                            $jsst_agents
                        )
                        : esc_html(__('No agents have been added yet.', 'js-support-ticket'))),
            'action' => $jsst_hasagent ? esc_html(__('Add an agent', 'js-support-ticket')) : '',
            'url'    => $jsst_hasagent ? admin_url('admin.php?page=agent&jstlay=addstaff') : '',
        );

        $jsst_steps['testmail'] = array(
            'title'  => esc_html(__('Proof that e-mail leaves the building', 'js-support-ticket')),
            'why'    => esc_html(__('A help desk that cannot send e-mail looks fine from the inside and silent from the outside. This is the only step that proves otherwise.', 'js-support-ticket')),
            'done'   => (!empty($jsst_mail) && !empty($jsst_mail['ok'])),
            'detail' => empty($jsst_mail)
                    ? esc_html(__('Nothing has been sent from this site yet.', 'js-support-ticket'))
                    : (!empty($jsst_mail['ok'])
                        ? esc_html(__('The last message was accepted by your mail service.', 'js-support-ticket'))
                        : esc_html(__('The last message was refused. Email Health explains why.', 'js-support-ticket'))),
            'action' => esc_html(__('Open Email Health', 'js-support-ticket')),
            'url'    => admin_url('admin.php?page=email&jstlay=emailhealth'),
        );

        $jsst_steps['testticket'] = array(
            'title'  => esc_html(__('One ticket, end to end', 'js-support-ticket')),
            'why'    => esc_html(__('Raise one yourself. It is the fastest way to see what a customer sees and to confirm the notification arrives.', 'js-support-ticket')),
            'done'   => ($jsst_tickets > 0),
            'detail' => $jsst_tickets > 0
                    ? sprintf(
                        /* translators: %d: how many tickets exist */
                        esc_html(_n('%d ticket so far.', '%d tickets so far.', $jsst_tickets, 'js-support-ticket')),
                        $jsst_tickets
                    )
                    : esc_html(__('No tickets have been raised yet.', 'js-support-ticket')),
            'action' => esc_html(__('Raise a test ticket', 'js-support-ticket')),
            'url'    => admin_url('admin.php?page=ticket&jstlay=addticket'),
        );

        return apply_filters('jsst_setup_steps', $jsst_steps);
    }

    /** How many of the steps are done, and how many there are. */
    public static function progress() {
        $jsst_steps = self::steps();
        $jsst_done = 0;
        foreach ($jsst_steps as $jsst_step) {
            if (!empty($jsst_step['done'])) {
                $jsst_done++;
            }
        }
        return array('done' => $jsst_done, 'total' => count($jsst_steps));
    }

    /** Has an administrator put the checklist away? */
    public static function dismissed() {
        return (get_option(self::DISMISSED_OPTION) === '1');
    }

    public static function dismiss() {
        update_option(self::DISMISSED_OPTION, '1', false);
    }

}
