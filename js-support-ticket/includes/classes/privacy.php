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
if (class_exists('JSSTprivacy')) {
    return;
}

/**
 * WordPress privacy tools. (Roadmap 4.0-SEC-02)
 *
 * The plugin already had its own export and erase screens, but they were only
 * its own: a site owner answering a subject-access request through WordPress's
 * Tools → Export Personal Data got everything on the site except the help desk,
 * which is where the most sensitive material usually is. That is a compliance
 * hole rather than a missing feature, and it is why this registers with the
 * platform's own hooks instead of adding a fourth screen.
 *
 * Matching is by e-mail address, not by user id. A help desk takes tickets from
 * people who never had an account, and their data is no less personal for it;
 * matching on the plugin's uid would silently miss every guest ticket.
 *
 * Erasure anonymises rather than deletes. A ticket is a business record and a
 * conversation between two parties: destroying it would remove the other side's
 * data too, and take the record of what was agreed with it. Removing the
 * personal fields and keeping the rest is what the regulation asks for and what
 * the erase screen in this plugin has always done — this makes the same
 * behaviour reachable from the platform's tools, and says plainly in the
 * response that it is what happened.
 */
class JSSTprivacy {

    /** How many tickets one page of an export or erase handles. */
    const PAGE_SIZE = 20;

    /**
     * Hook into WordPress's privacy tools. Called once from the bootstrap.
     */
    public static function register() {
        add_filter('wp_privacy_personal_data_exporters', array(__CLASS__, 'registerExporter'));
        add_filter('wp_privacy_personal_data_erasers', array(__CLASS__, 'registerEraser'));
        add_action('admin_init', array(__CLASS__, 'addPolicyContent'));
    }

    public static function registerExporter($jsst_exporters) {
        $jsst_exporters['js-support-ticket'] = array(
            'exporter_friendly_name' => esc_html(__('JS Help Desk tickets', 'js-support-ticket')),
            'callback'               => array(__CLASS__, 'exportForEmail'),
        );
        return $jsst_exporters;
    }

    public static function registerEraser($jsst_erasers) {
        $jsst_erasers['js-support-ticket'] = array(
            'eraser_friendly_name' => esc_html(__('JS Help Desk tickets', 'js-support-ticket')),
            'callback'             => array(__CLASS__, 'eraseForEmail'),
        );
        return $jsst_erasers;
    }

    /**
     * One page of this person's tickets.
     *
     * @param string $jsst_email the address WordPress is acting on
     * @param int    $jsst_page  1-based
     */
    public static function exportForEmail($jsst_email, $jsst_page = 1) {
        $jsst_page = max(1, (int) $jsst_page);
        $jsst_export = array();

        $jsst_tickets = self::ticketsFor($jsst_email, $jsst_page);
        foreach ($jsst_tickets as $jsst_ticket) {
            $jsst_items = array(
                array('name' => esc_html(__('Ticket reference', 'js-support-ticket')), 'value' => $jsst_ticket->ticketid),
                array('name' => esc_html(__('Subject', 'js-support-ticket')),          'value' => $jsst_ticket->subject),
                array('name' => esc_html(__('Message', 'js-support-ticket')),          'value' => $jsst_ticket->message),
                array('name' => esc_html(__('Name given', 'js-support-ticket')),       'value' => $jsst_ticket->name),
                array('name' => esc_html(__('E-mail address', 'js-support-ticket')),   'value' => $jsst_ticket->email),
                array('name' => esc_html(__('Telephone', 'js-support-ticket')),        'value' => $jsst_ticket->phone),
                array('name' => esc_html(__('Raised', 'js-support-ticket')),           'value' => $jsst_ticket->created),
            );
            // The customer's own replies. An agent's replies are the other party
            // to the conversation and are not this person's personal data.
            foreach (self::repliesFor($jsst_ticket->id, $jsst_email) as $jsst_i => $jsst_reply) {
                $jsst_items[] = array(
                    'name'  => sprintf(
                        /* translators: %d: which reply, counting from one */
                        esc_html(__('Your reply %d', 'js-support-ticket')),
                        $jsst_i + 1
                    ),
                    'value' => $jsst_reply->message . ' (' . $jsst_reply->created . ')',
                );
            }
            $jsst_export[] = array(
                'group_id'    => 'jsst-tickets',
                'group_label' => esc_html(__('Support tickets', 'js-support-ticket')),
                'item_id'     => 'jsst-ticket-' . (int) $jsst_ticket->id,
                'data'        => $jsst_items,
            );
        }

        /* Anything else in this product that holds personal data about the
           same address adds it here. Only on the first page: what these
           subscribers return is not paged, and adding it to every page would
           repeat it once per twenty tickets. (Roadmap 5.5-SEC-02) */
        if ($jsst_page === 1) {
            $jsst_export = apply_filters('jsst_privacy_export', $jsst_export, $jsst_email);
        }

        return array(
            'data' => $jsst_export,
            // Done when this page came back short: WordPress calls again with
            // the next page until this says so.
            'done' => (count($jsst_tickets) < self::PAGE_SIZE),
        );
    }

    /**
     * Anonymise one page of this person's tickets.
     */
    public static function eraseForEmail($jsst_email, $jsst_page = 1) {
        $jsst_page = max(1, (int) $jsst_page);
        $jsst_removed = false;
        $jsst_messages = array();

        $jsst_tickets = self::ticketsFor($jsst_email, $jsst_page);
        $jsst_anonymous = esc_html(__('Anonymised', 'js-support-ticket'));

        foreach ($jsst_tickets as $jsst_ticket) {
            jssupportticket::$_db->update(
                jssupportticket::$_db->prefix . 'js_ticket_tickets',
                array(
                    'name'     => $jsst_anonymous,
                    'email'    => 'deleted-' . (int) $jsst_ticket->id . '@example.invalid',
                    'phone'    => '',
                    'phoneext' => '',
                ),
                array('id' => (int) $jsst_ticket->id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
            jssupportticket::$_db->update(
                jssupportticket::$_db->prefix . 'js_ticket_replies',
                array('name' => $jsst_anonymous),
                array('ticketid' => (int) $jsst_ticket->id, 'staffid' => 0),
                array('%s'),
                array('%d', '%d')
            );
            $jsst_removed = true;
        }

        if ($jsst_removed) {
            // Said plainly, because "erased" and "anonymised" are different
            // promises and the person asking is entitled to know which they got.
            $jsst_messages[] = esc_html(__('Help desk tickets were anonymised rather than deleted: the name, e-mail address and telephone number were removed, and the ticket itself was kept as a record of the support provided. Deleting it outright would also remove the replies written by support staff, which are not the requester\'s personal data.', 'js-support-ticket'));
        }

        return array(
            'items_removed'  => $jsst_removed,
            'items_retained' => $jsst_removed,
            'messages'       => $jsst_messages,
            'done'           => (count($jsst_tickets) < self::PAGE_SIZE),
        );
    }

    /**
     * One page of tickets belonging to an address.
     */
    private static function ticketsFor($jsst_email, $jsst_page) {
        if (!is_email($jsst_email)) {
            return array();
        }
        $jsst_offset = ($jsst_page - 1) * self::PAGE_SIZE;
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, ticketid, subject, message, name, email, phone, created
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`
                WHERE email = %s ORDER BY id ASC LIMIT %d OFFSET %d",
            $jsst_email,
            self::PAGE_SIZE,
            $jsst_offset
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * The customer's own replies on one ticket.
     *
     * Scoped to replies with no staff id: an agent's words belong to the agent
     * and to the business, not to the person making the request.
     */
    private static function repliesFor($jsst_ticketid, $jsst_email) {
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT message, created FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies`
                WHERE ticketid = %d AND (staffid IS NULL OR staffid = 0) ORDER BY id ASC",
            (int) $jsst_ticketid
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * The retention policy, offered to the site's privacy policy draft.
     *
     * WordPress collects these from every plugin so a site owner writing their
     * policy is told what each one keeps. Stating the retention behaviour here
     * is the "documented data-retention policy" half of this work — and it is
     * generated from the site's own settings rather than being boilerplate, so
     * it stays true when those settings change. (Roadmap 4.0-SEC-02, 4.0-CORE-13)
     */
    public static function addPolicyContent() {
        if (!function_exists('wp_add_privacy_policy_content')) {
            return;
        }
        $jsst_parts = array();
        $jsst_parts[] = '<p>' . esc_html(__('This site runs a help desk. When you raise a ticket it stores the name, e-mail address and any telephone number you give, the subject and text of your ticket, every reply on it, any files attached to it, and the date and time of each. If you raise a ticket without an account, that information is still stored and is still yours to ask about.', 'js-support-ticket')) . '</p>';

        $jsst_cleanup = self::retentionSummary();
        $jsst_parts[] = '<p>' . $jsst_cleanup . '</p>';

        $jsst_parts[] = '<p>' . esc_html(__('You can ask for a copy of your help desk data or ask for it to be removed. Tickets are anonymised rather than deleted: your name, e-mail address and telephone number are removed, and the ticket is kept as a record of the support given, because it also contains replies written by support staff.', 'js-support-ticket')) . '</p>';

        wp_add_privacy_policy_content(
            esc_html(__('JS Help Desk', 'js-support-ticket')),
            wp_kses_post(implode("\n", $jsst_parts))
        );
    }

    /**
     * One retention interval, in months, as the cleanup itself reads it.
     *
     * Read by name rather than through JSSTautocleanupModel on purpose: while
     * the stand-alone Auto Cleanup add-on is active, `getJSModel('autocleanup')`
     * resolves into that add-on, which is a different class. The two share these
     * setting names - the module's own docblock says so - so the name is the
     * part that can be relied on. (Roadmap 4.0-CORE-13)
     */
    private static function retentionMonths($jsst_name) {
        $jsst_value = isset(jssupportticket::$_config[$jsst_name]) ? jssupportticket::$_config[$jsst_name] : 0;
        return is_numeric($jsst_value) ? (int) $jsst_value : 0;
    }

    /** Is anything held back from retention by department or by priority? */
    private static function retentionExcludes() {
        foreach (array('autocleanup_exclude_departments', 'autocleanup_exclude_priorities') as $jsst_name) {
            $jsst_raw = isset(jssupportticket::$_config[$jsst_name]) ? (string) jssupportticket::$_config[$jsst_name] : '';
            $jsst_decoded = json_decode($jsst_raw, true);
            $jsst_pieces = is_array($jsst_decoded) ? $jsst_decoded : explode(',', $jsst_raw);
            foreach ($jsst_pieces as $jsst_piece) {
                if (is_scalar($jsst_piece) && is_numeric(trim((string) $jsst_piece)) && (int) $jsst_piece > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * How long tickets are kept, in the site's own terms.
     *
     * Reads the retention cleanup settings rather than asserting a policy the
     * site may not be following - which is the whole idea, and which is exactly
     * what it failed to do until now.
     *
     * It read `autocleanup_enable` and `autocleanup_ticket_days`. Neither name
     * exists: nothing in this product writes either one and there is no row for
     * either in `js_ticket_config`, so both reads came back null, the summary
     * took its "no automatic deletion is switched on" branch on every site there
     * has ever been, and a desk running the cleanup cron told its customers - in
     * a published privacy policy - that nothing was being deleted while it was
     * deleting their tickets on a schedule. A wrong sentence here is worse than
     * a wrong number on an admin screen: it is the statement the site makes to
     * the people whose data it is.
     *
     * The real names are `autocleanup_ticket_interval` and
     * `autocleanup_attachment_interval`, and they are **months**, not days -
     * the second half of the same mistake, and the half that would have
     * survived a fix that only corrected the name.
     *
     * The two intervals are reported separately because they are set
     * separately, and the common configuration is the one the old sentence
     * could not express at all: keep the tickets, purge the files.
     */
    public static function retentionSummary() {
        $jsst_tickets = self::retentionMonths('autocleanup_ticket_interval');
        $jsst_files   = self::retentionMonths('autocleanup_attachment_interval');

        if ($jsst_tickets <= 0 && $jsst_files <= 0) {
            return esc_html(__('Tickets on this site are kept until somebody removes them. No automatic deletion is switched on, so a ticket stays until it is deleted by hand or by a retention rule an administrator sets up later.', 'js-support-ticket'));
        }

        $jsst_said = '';
        if ($jsst_tickets > 0) {
            /* Deleting the ticket takes its files with it, so a separate
               sentence about attachments is only worth saying when they go
               sooner than the ticket does. */
            $jsst_said = sprintf(
                /* translators: %s: a number of months, already formatted. */
                esc_html(_n(
                    'Closed tickets on this site are deleted automatically once they are %s month old, along with anything attached to them.',
                    'Closed tickets on this site are deleted automatically once they are %s months old, along with anything attached to them.',
                    $jsst_tickets, 'js-support-ticket')),
                number_format_i18n($jsst_tickets)
            );
            if ($jsst_files > 0 && $jsst_files < $jsst_tickets) {
                $jsst_said .= ' ' . sprintf(
                    /* translators: %s: a number of months, already formatted. */
                    esc_html(_n(
                        'The files attached to them go sooner, once the ticket is %s month old.',
                        'The files attached to them go sooner, once the ticket is %s months old.',
                        $jsst_files, 'js-support-ticket')),
                    number_format_i18n($jsst_files)
                );
            }
        } else {
            $jsst_said = sprintf(
                /* translators: %s: a number of months, already formatted. */
                esc_html(_n(
                    'Closed tickets on this site are kept, but the files attached to them are deleted automatically once the ticket is %s month old.',
                    'Closed tickets on this site are kept, but the files attached to them are deleted automatically once the ticket is %s months old.',
                    $jsst_files, 'js-support-ticket')),
                number_format_i18n($jsst_files)
            );
        }

        /* Said because the sentence above is otherwise a promise the site does
           not keep for every ticket, and this is a privacy policy. */
        if (self::retentionExcludes()) {
            $jsst_said .= ' ' . esc_html(__('Tickets in some departments, or at some priorities, are held back from this and are kept until somebody removes them.', 'js-support-ticket'));
        }
        return $jsst_said;
    }

}
