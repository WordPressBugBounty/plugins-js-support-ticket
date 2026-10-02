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
if (class_exists('JSSTinternalmail')) {
    return;
}

/**
 * Retiring Internal Mail. (Roadmap 4.5-FE-12)
 *
 * The Internal Mail add-on is a second inbox: agents write to each other in a
 * mailbox that sits beside the help desk and knows nothing about it. It is a
 * dated idea and, more to the point, it puts the conversation in the wrong
 * place - a question about a ticket belongs on the ticket, where the next
 * person to open it will find it, not in a private mailbox two people share.
 * Everything it was used for now exists on the ticket itself: @mentions and
 * restricted notes from 4.5-FE-08, watchers, the notification centre from
 * 4.5-UX-02, and the handover summary.
 *
 * So this is the retirement, and the whole of its difficulty is in one fact
 * about the old table: **a message has no ticket**. `js_ticket_staff_mail`
 * holds a sender, a recipient, a subject, a body and a thread parent, and
 * nothing else. There is no column to migrate from. Which means:
 *
 *   Where the message names a ticket   it becomes an internal note on that
 *                                      ticket, attributed to whoever sent it
 *                                      and dated when they sent it.
 *   Where it does not                  it is archived and left alone. There is
 *                                      no honest way to attach "can you look
 *                                      at this when you get a minute" to a
 *                                      ticket nobody named, and inventing one
 *                                      would put private messages on customer
 *                                      records at random.
 *
 * The roadmap says "notes or team conversations". There is no such thing as a
 * team conversation in this product - 4.5-FE-05 gave teams members, hours and
 * a signature, not a thread - so building one to hold migrated messages would
 * mean shipping a second inbox to retire the first. The archive is the honest
 * answer instead: everything is preserved, downloadable, and nothing is thrown
 * away or guessed at.
 *
 * Nothing here deletes a message, drops the table or deactivates the add-on.
 * That is the same courtesy 4.5-PRO-02 gives every add-on it supersedes, and
 * it is what makes the operation reversible: if the migration turns out to
 * have been a mistake, the mailbox is exactly where it was.
 */
class JSSTinternalmail {

    /** The add-on's own table. */
    const TABLE = 'js_ticket_staff_mail';

    /** Which messages have already been turned into notes. */
    const OPT_MIGRATED = 'jsst_internalmail_migrated';

    /** How a message was matched to a ticket. */
    const MATCH_REFERENCE = 'reference';
    const MATCH_NUMBER    = 'number';
    const MATCH_NONE      = '';

    /* =====================================================================
     * Is there anything to retire?
     * ================================================================== */

    /** Is the add-on's table present at all? */
    public static function available() {
        $jsst_table = jssupportticket::$_db->prefix . self::TABLE;
        return (bool) jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)
        );
    }

    /**
     * What is in there, and what would become of it.
     *
     * @return array total, migrated, matched, unmatched, people, first, last
     */
    public static function survey() {
        if (!self::available()) {
            return array('available' => false, 'total' => 0, 'migrated' => 0,
                'matched' => 0, 'unmatched' => 0, 'people' => 0, 'first' => '', 'last' => '');
        }
        $jsst_table = jssupportticket::$_db->prefix . self::TABLE;
        $jsst_total = (int) jssupportticket::$_db->get_var('SELECT COUNT(id) FROM `' . $jsst_table . '`');
        $jsst_matched = 0;
        foreach (self::messages() as $jsst_message) {
            if (self::matchTicket($jsst_message)->ticketid > 0) {
                $jsst_matched++;
            }
        }
        return array(
            'available' => true,
            'total'     => $jsst_total,
            'migrated'  => count(self::migrated()),
            'matched'   => $jsst_matched,
            'unmatched' => $jsst_total - $jsst_matched,
            'people'    => (int) jssupportticket::$_db->get_var(
                'SELECT COUNT(DISTINCT fromid) FROM `' . $jsst_table . '`'),
            'first'     => (string) jssupportticket::$_db->get_var(
                'SELECT MIN(created) FROM `' . $jsst_table . '`'),
            'last'      => (string) jssupportticket::$_db->get_var(
                'SELECT MAX(created) FROM `' . $jsst_table . '`'),
        );
    }

    /** Every message, with the names either end of it. */
    public static function messages($jsst_limit = 0) {
        if (!self::available()) {
            return array();
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        /* fromid and toid are js_ticket_staff ids - the add-on's own queries
           join them that way - and not the users ids the notes table calls
           staffid. The two are different numbers for the same person. */
        $jsst_sql = 'SELECT mail.*, '
            . 'sender.name AS fromname, recipient.name AS toname, sender.id AS fromuserid '
            . 'FROM `' . $jsst_prefix . self::TABLE . '` AS mail '
            . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_staff` AS fromstaff ON fromstaff.id = mail.fromid '
            . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_users` AS sender ON sender.id = fromstaff.uid '
            . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_staff` AS tostaff ON tostaff.id = mail.toid '
            . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_users` AS recipient ON recipient.id = tostaff.uid '
            . 'ORDER BY mail.created ASC, mail.id ASC';
        if ((int) $jsst_limit > 0) {
            $jsst_sql .= ' LIMIT ' . (int) $jsst_limit;
        }
        $jsst_rows = jssupportticket::$_db->get_results($jsst_sql);
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /* =====================================================================
     * Finding the ticket somebody meant
     * ================================================================== */

    /**
     * The ticket a message is about, if it names one.
     *
     * Two ways, in order of how certain they are. A ticket's own reference -
     * the token this plugin prints on every ticket - appearing anywhere in the
     * subject or the body is as good as a link. A plain "#123" is weaker: it
     * matches the row id, which somebody may have meant and may not, so it is
     * reported as the weaker match and shown as such on the preview rather
     * than being treated as equivalent.
     *
     * Nothing is guessed at beyond those two. A message that merely mentions a
     * customer's name is not evidence of anything, and attaching private mail
     * to a ticket on that basis is how a migration becomes a data incident.
     *
     * @return object ticketid, how, reference
     */
    public static function matchTicket($jsst_message) {
        $jsst_out = (object) array('ticketid' => 0, 'how' => self::MATCH_NONE, 'reference' => '');
        $jsst_haystack = (string) $jsst_message->subject . ' ' . wp_strip_all_tags((string) $jsst_message->message);
        if (trim($jsst_haystack) === '') {
            return $jsst_out;
        }
        $jsst_prefix = jssupportticket::$_db->prefix;

        /* The reference first. Compared in the database rather than by pulling
           every ticket into PHP: a desk being migrated may have a hundred
           thousand of them. */
        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT id, ticketid FROM `' . $jsst_prefix . 'js_ticket_tickets` '
            . 'WHERE ticketid <> %s AND LOCATE(ticketid, %s) > 0 LIMIT 1',
            '', $jsst_haystack
        ));
        if ($jsst_row) {
            $jsst_out->ticketid = (int) $jsst_row->id;
            $jsst_out->how = self::MATCH_REFERENCE;
            $jsst_out->reference = $jsst_row->ticketid;
            return $jsst_out;
        }
        if (preg_match('/#(\d{1,10})\b/', $jsst_haystack, $jsst_m)) {
            $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                'SELECT id, ticketid FROM `' . $jsst_prefix . 'js_ticket_tickets` WHERE id = %d',
                (int) $jsst_m[1]
            ));
            if ($jsst_row) {
                $jsst_out->ticketid = (int) $jsst_row->id;
                $jsst_out->how = self::MATCH_NUMBER;
                $jsst_out->reference = $jsst_row->ticketid;
            }
        }
        return $jsst_out;
    }

    /**
     * Every message with what would happen to it, for the screen.
     *
     * Read-only. Nothing on this path writes, which is what makes it safe to
     * look at a migration before running it - the lesson 4.5-PRO-02 was built
     * around.
     */
    public static function preview($jsst_limit = 50) {
        $jsst_migrated = self::migrated();
        $jsst_out = array();
        foreach (self::messages($jsst_limit) as $jsst_message) {
            $jsst_match = self::matchTicket($jsst_message);
            $jsst_out[] = array(
                'id'        => (int) $jsst_message->id,
                'subject'   => $jsst_message->subject,
                'excerpt'   => wp_trim_words(wp_strip_all_tags((string) $jsst_message->message), 18),
                'from'      => $jsst_message->fromname ? $jsst_message->fromname : ('#' . $jsst_message->fromid),
                'to'        => $jsst_message->toname ? $jsst_message->toname : ('#' . $jsst_message->toid),
                'created'   => $jsst_message->created,
                'ticketid'  => $jsst_match->ticketid,
                'reference' => $jsst_match->reference,
                'how'       => $jsst_match->how,
                'done'      => in_array((int) $jsst_message->id, $jsst_migrated, true),
            );
        }
        return $jsst_out;
    }

    /* =====================================================================
     * Doing it
     * ================================================================== */

    /** The message ids already turned into notes. */
    public static function migrated() {
        $jsst_done = get_option(self::OPT_MIGRATED, array());
        return is_array($jsst_done) ? array_map('intval', $jsst_done) : array();
    }

    /**
     * Turn every message that names a ticket into a note on it.
     *
     * Idempotent: a message that has already been migrated is skipped, so
     * running this twice does not put the same conversation on a ticket twice.
     * Nothing is deleted at any point and the add-on is left running.
     *
     * @return array moved, skipped, unmatched, failed
     */
    public static function migrate() {
        $jsst_out = array('moved' => 0, 'skipped' => 0, 'unmatched' => 0, 'failed' => 0);
        if (!self::available()) {
            return $jsst_out;
        }
        $jsst_done = self::migrated();
        foreach (self::messages() as $jsst_message) {
            if (in_array((int) $jsst_message->id, $jsst_done, true)) {
                $jsst_out['skipped']++;
                continue;
            }
            $jsst_match = self::matchTicket($jsst_message);
            if ($jsst_match->ticketid <= 0) {
                $jsst_out['unmatched']++;
                continue;
            }
            $jsst_noteid = self::writeNote($jsst_message, $jsst_match);
            if ($jsst_noteid > 0) {
                $jsst_done[] = (int) $jsst_message->id;
                $jsst_out['moved']++;
            } else {
                $jsst_out['failed']++;
            }
        }
        update_option(self::OPT_MIGRATED, array_values(array_unique($jsst_done)), false);
        return $jsst_out;
    }

    /**
     * One message, as an internal note.
     *
     * Written straight to the table rather than through the note model,
     * because that model attributes a note to whoever is signed in and stamps
     * it with now - and this note belongs to the person who sent the message,
     * on the day they sent it. An administrator's name and today's date on
     * three years of somebody else's correspondence would make the archive
     * useless as a record.
     *
     * The `staffid` column is a js_ticket_users id, which is not the id the
     * mail table holds - the join in messages() has already converted it.
     */
    private static function writeNote($jsst_message, $jsst_match) {
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_authoruid = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT staff.uid FROM `' . $jsst_prefix . 'js_ticket_staff` AS staff WHERE staff.id = %d',
            (int) $jsst_message->fromid
        ));
        $jsst_body = '<p><em>' . esc_html(sprintf(
            /* translators: 1: sender, 2: recipient, 3: the date it was sent */
            __('Moved from Internal Mail. Sent by %1$s to %2$s on %3$s.', 'js-support-ticket'),
            $jsst_message->fromname ? $jsst_message->fromname : ('#' . $jsst_message->fromid),
            $jsst_message->toname ? $jsst_message->toname : ('#' . $jsst_message->toid),
            $jsst_message->created
        )) . '</em></p>' . wp_kses_post((string) $jsst_message->message);

        $jsst_ok = jssupportticket::$_db->insert($jsst_prefix . 'js_ticket_notes', array(
            'ticketid' => (int) $jsst_match->ticketid,
            'staffid'  => $jsst_authoruid,
            'title'    => mb_substr((string) $jsst_message->subject, 0, 200),
            'note'     => $jsst_body,
            'status'   => 1,
            'created'  => $jsst_message->created,
        ));
        if (!$jsst_ok) {
            return 0;
        }
        $jsst_noteid = (int) jssupportticket::$_db->insert_id;
        /* Private mail becomes a private note: only the two people who were on
           the message can read it. Migrating a two-person conversation into
           something the whole desk can read would be a disclosure performed by
           an upgrade, which is the one outcome this must not have. */
        if (class_exists('JSSTcollab')) {
            JSSTcollab::restrictNote($jsst_noteid, JSSTcollab::NOTE_NAMED,
                array((int) $jsst_message->fromid, (int) $jsst_message->toid));
        }
        return $jsst_noteid;
    }

    /* =====================================================================
     * Keeping everything
     * ================================================================== */

    /**
     * The whole mailbox as a file.
     *
     * Taken before anything is migrated and offered whether or not anything
     * can be. The messages that name no ticket have nowhere to go, and an
     * archive is the difference between "we retired that feature" and "we lost
     * three years of your colleagues' notes".
     *
     * @return string CSV
     */
    public static function archive() {
        $jsst_rows = array(array('id', 'from', 'to', 'subject', 'message', 'sent', 'ticket'));
        foreach (self::messages() as $jsst_message) {
            $jsst_match = self::matchTicket($jsst_message);
            $jsst_rows[] = array(
                $jsst_message->id,
                $jsst_message->fromname ? $jsst_message->fromname : ('#' . $jsst_message->fromid),
                $jsst_message->toname ? $jsst_message->toname : ('#' . $jsst_message->toid),
                (string) $jsst_message->subject,
                wp_strip_all_tags((string) $jsst_message->message),
                (string) $jsst_message->created,
                $jsst_match->reference,
            );
        }
        $jsst_handle = fopen('php://temp', 'r+'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- php:// stream, not a file: WP_Filesystem has no API for stream wrappers
        foreach ($jsst_rows as $jsst_row) {
            // Escape `''` for RFC 4180, and named rather than defaulted: PHP 8.4
            // deprecates the implicit default. Matches JSSTcsvwriter::row().
            fputcsv($jsst_handle, $jsst_row, ',', '"', '');
        }
        rewind($jsst_handle);
        $jsst_csv = stream_get_contents($jsst_handle);
        fclose($jsst_handle); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- php:// stream, not a file: WP_Filesystem has no API for stream wrappers
        return $jsst_csv;
    }

    /** What replaces it, named, so the screen is not just a farewell. */
    public static function replacements() {
        return array(
            array(
                'what' => __('Asking a colleague to look at a ticket', 'js-support-ticket'),
                'now'  => __('Write @ and their name in an internal note. They are told, and they start following the ticket.', 'js-support-ticket'),
            ),
            array(
                'what' => __('Telling somebody something only they should read', 'js-support-ticket'),
                'now'  => __('An internal note restricted to the people you name.', 'js-support-ticket'),
            ),
            array(
                'what' => __('Keeping up with a ticket that is not yours', 'js-support-ticket'),
                'now'  => __('Follow it. Anything that happens to it reaches your notifications.', 'js-support-ticket'),
            ),
            array(
                'what' => __('Handing work over', 'js-support-ticket'),
                'now'  => __('The handover summary on the ticket, written from what actually happened rather than typed at five o\'clock.', 'js-support-ticket'),
            ),
        );
    }
}
