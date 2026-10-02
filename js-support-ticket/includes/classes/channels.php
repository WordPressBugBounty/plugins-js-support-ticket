<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

if (class_exists('JSSTchannels')) {
    return;
}

/**
 * One customer, however they reached you. (Roadmap 6.0-CH-02)
 *
 * The problem this exists for is created by shipping chat. Before 6.0-CH-01 a
 * customer had one way in and their history was their ticket list; now the same
 * person can e-mail on Monday, chat on Tuesday and file through the portal on
 * Wednesday, and an agent opening the Wednesday ticket sees none of the other
 * two. They then ask a question the customer already answered, which is the
 * single most reliable way to make somebody feel that a business is not
 * listening to them.
 *
 * ## Identity is an e-mail address, again
 *
 * The same rule the Customers screen has used since 4.5-FE-02 and that chat
 * reuses in `JSSTlivechat::whoIs()`: a signed-in person resolves to their
 * `js_ticket_users` row, and everybody else is an address. There is deliberately
 * no new identity store here - a second answer to "who is this" is a second
 * answer that disagrees with the first within a month, and this class exists
 * precisely because two views of one person is the bug.
 *
 * ## What a channel is, and what it is not
 *
 * A channel is **how something arrived**, not what module handled it. E-mail
 * and portal tickets are both tickets and live in the same table; the column
 * that tells them apart already exists. Chat is a different table because a
 * conversation is a different shape from a ticket, which 6.0-CH-01 argues at
 * length - so this class reads two stores and merges them rather than proposing
 * a third that both would have to be copied into.
 *
 * ## Per-channel policy is a refusal, never a permission
 *
 * `aiAllowed()` can only ever narrow what the AI policy already permits. A site
 * that has switched AI off does not get it back by enabling a channel here, and
 * a channel switch cannot open a lane `JSSTaipolicy` has closed. That direction
 * is the whole safety property: this is a fourth place somebody can say no, and
 * not a place anybody can say yes.
 */
class JSSTchannels {

    /** Filed through the support portal on the site. */
    const PORTAL = 'portal';

    /** Arrived as an e-mail. */
    const EMAIL = 'email';

    /** Started as a live chat conversation. */
    const CHAT = 'chat';

    /** Where per-channel AI decisions are stored. */
    const OPT_AI = 'jsst_channel_ai';

    /* ------------------------------------------------------------------ *
     * The channels
     * ------------------------------------------------------------------ */

    /**
     * Every way in, and whether this site actually has it.
     *
     * `present` is answered from what is installed rather than from a setting,
     * because a channel a site cannot receive on is not a channel it should be
     * asked to write a policy for.
     */
    public static function channels() {
        $jsst_channels = array(
            self::PORTAL => array(
                'label'   => esc_html(__('Support portal', 'js-support-ticket')),
                'blurb'   => esc_html(__('Filed on your site, by somebody looking at the form.', 'js-support-ticket')),
                'present' => true,
            ),
            self::EMAIL => array(
                'label'   => esc_html(__('E-mail', 'js-support-ticket')),
                'blurb'   => esc_html(__('Arrived in a mailbox and was piped in. The customer may never have seen your site.', 'js-support-ticket')),
                'present' => in_array('emailpiping', jssupportticket::$_active_addons),
            ),
            self::CHAT => array(
                'label'   => esc_html(__('Live chat', 'js-support-ticket')),
                'blurb'   => esc_html(__('Typed into the widget, usually while the customer is still on the page that confused them.', 'js-support-ticket')),
                'present' => in_array('livechat', jssupportticket::$_active_addons),
            ),
        );

        return apply_filters('jsst_channels', $jsst_channels);
    }

    public static function isChannel($jsst_channel) {
        return array_key_exists((string) $jsst_channel, self::channels());
    }

    public static function label($jsst_channel) {
        $jsst_channels = self::channels();
        return isset($jsst_channels[$jsst_channel]) ? $jsst_channels[$jsst_channel]['label'] : (string) $jsst_channel;
    }

    /**
     * How this ticket arrived.
     *
     * Chat first, because a chat that became a ticket is still a chat as far as
     * the customer is concerned - they typed it into a widget, and telling them
     * "you filed a ticket" would be describing our filing rather than their
     * experience.
     */
    public static function ofTicket($jsst_ticket) {
        $jsst_row = is_object($jsst_ticket) ? $jsst_ticket : self::ticket($jsst_ticket);
        if (!$jsst_row) return self::PORTAL;

        if (class_exists('JSSTlivechat') && JSSTlivechat::available()) {
            $jsst_fromchat = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT id FROM `" . JSSTlivechat::convos() . "` WHERE ticketid = %d LIMIT 1",
                (int) $jsst_row->id));
            if ($jsst_fromchat) return self::CHAT;
        }

        return (!empty($jsst_row->ticketviaemail)) ? self::EMAIL : self::PORTAL;
    }

    /* ------------------------------------------------------------------ *
     * One person's history
     * ------------------------------------------------------------------ */

    /**
     * Everything this customer has done, in one list, newest first.
     *
     * Two stores read and merged rather than a third written. That costs a sort
     * in PHP and buys the property that matters: nothing has to be kept in step,
     * so a chat deleted by the retention sweep or a ticket merged away simply
     * stops appearing rather than leaving a dangling row in a unified table
     * nobody maintains.
     *
     * Bounded on both sides before the merge, because a customer with four
     * hundred tickets and four hundred chats is not a reason to read eight
     * hundred rows to show ten.
     *
     * @return array Each: channel, when, title, preview, url, id, kind.
     */
    public static function timelineFor($jsst_uid, $jsst_email, $jsst_limit = 15, $jsst_excludeticket = 0) {
        $jsst_uid   = (int) $jsst_uid;
        $jsst_email = sanitize_email((string) $jsst_email);

        if ($jsst_uid < 1 && ($jsst_email === '' || !is_email($jsst_email))) {
            return array();
        }

        $jsst_out = array_merge(
            self::ticketsFor($jsst_uid, $jsst_email, $jsst_limit, (int) $jsst_excludeticket),
            self::chatsFor($jsst_uid, $jsst_email, $jsst_limit)
        );

        usort($jsst_out, array(__CLASS__, 'byWhen'));
        return array_slice($jsst_out, 0, (int) $jsst_limit);
    }

    private static function byWhen($jsst_a, $jsst_b) {
        return ($jsst_b['when'] - $jsst_a['when']);
    }

    /** Their tickets, with the channel each arrived by. */
    private static function ticketsFor($jsst_uid, $jsst_email, $jsst_limit, $jsst_exclude) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_tickets';

        $jsst_where = ($jsst_uid > 0)
            ? jssupportticket::$_db->prepare('uid = %d', $jsst_uid)
            : jssupportticket::$_db->prepare('email = %s', $jsst_email);

        if ($jsst_exclude > 0) {
            $jsst_where .= jssupportticket::$_db->prepare(' AND id <> %d', $jsst_exclude);
        }

        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, ticketid, subject, message, created, status, ticketviaemail
               FROM `" . $jsst_table . "` WHERE " . $jsst_where . "
              ORDER BY created DESC LIMIT %d", (int) $jsst_limit));

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[] = array(
                'kind'    => 'ticket',
                'id'      => (int) $jsst_row->id,
                'channel' => self::ofTicket($jsst_row),
                'when'    => (int) strtotime($jsst_row->created),
                'title'   => (string) $jsst_row->subject,
                'preview' => self::shorten(wp_strip_all_tags((string) $jsst_row->message)),
                'ref'     => (string) $jsst_row->ticketid,
                'url'     => admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_row->id),
            );
        }
        return $jsst_out;
    }

    /**
     * Their chats - the ones that never became tickets.
     *
     * A conversation that did become a ticket is already in the list above,
     * carrying the chat channel, so including it here as well would show the
     * same conversation twice under two headings. Which is precisely the
     * complaint this task exists to fix, arrived at from the other direction.
     */
    private static function chatsFor($jsst_uid, $jsst_email, $jsst_limit) {
        if (!class_exists('JSSTlivechat') || !JSSTlivechat::available()) return array();

        $jsst_where = ($jsst_uid > 0)
            ? jssupportticket::$_db->prepare('uid = %d', $jsst_uid)
            : jssupportticket::$_db->prepare('email = %s', $jsst_email);

        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, started, status, ticketid FROM `" . JSSTlivechat::convos() . "`
              WHERE " . $jsst_where . " AND ticketid = 0
              ORDER BY started DESC LIMIT %d", (int) $jsst_limit));

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_first = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT body FROM `" . JSSTlivechat::messages() . "`
                  WHERE conversationid = %d AND sender = %s ORDER BY id ASC LIMIT 1",
                (int) $jsst_row->id, JSSTlivechat::SENDER_VISITOR));

            $jsst_out[] = array(
                'kind'    => 'chat',
                'id'      => (int) $jsst_row->id,
                'channel' => self::CHAT,
                'when'    => (int) strtotime($jsst_row->started),
                'title'   => self::shorten((string) $jsst_first, 70),
                'preview' => '',
                'ref'     => '',
                'url'     => admin_url('admin.php?page=livechat&jstlay=livechat_history'),
            );
        }
        return $jsst_out;
    }

    private static function shorten($jsst_text, $jsst_max = 120) {
        $jsst_text = trim(preg_replace('/\s+/u', ' ', (string) $jsst_text));
        if ($jsst_text === '') return '';

        return (jssupportticketphplib::JSST_strlen($jsst_text) > $jsst_max)
            ? jssupportticketphplib::JSST_substr($jsst_text, 0, $jsst_max) . '…'
            : $jsst_text;
    }

    /* ------------------------------------------------------------------ *
     * Per-channel policy
     * ------------------------------------------------------------------ */

    /**
     * May the AI answer automatically on this channel?
     *
     * Narrows, never widens. The master policy is asked first and its "no" is
     * final; this can only turn a yes into a no. A site can therefore let the
     * engine answer e-mail while insisting a chat always reaches a person -
     * which is a real thing desks want, because a customer waiting in a widget
     * behaves very differently from one who has sent an e-mail and gone away.
     */
    public static function aiAllowed($jsst_channel) {
        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled()) return false;
        if (!self::isChannel($jsst_channel)) return true;

        $jsst_off = get_option(self::OPT_AI, array());
        if (!is_array($jsst_off)) return true;

        return !in_array((string) $jsst_channel, $jsst_off, true);
    }

    /** The channels the AI has been told to stay out of. */
    public static function aiRefused() {
        $jsst_off = get_option(self::OPT_AI, array());
        if (!is_array($jsst_off)) return array();

        return array_values(array_intersect($jsst_off, array_keys(self::channels())));
    }

    /**
     * Record which channels the AI may not answer on.
     *
     * Stored as the refusals rather than the permissions, deliberately: a
     * channel added in a later release is then allowed by default and inherits
     * whatever the master policy says, rather than being silently switched off
     * on every existing site because it was not in a list written before it
     * existed.
     */
    public static function setAiRefused($jsst_channels) {
        $jsst_clean = array();
        foreach ((array) $jsst_channels as $jsst_channel) {
            if (self::isChannel($jsst_channel)) $jsst_clean[] = (string) $jsst_channel;
        }

        update_option(self::OPT_AI, array_values(array_unique($jsst_clean)), false);
        return true;
    }

    /* ------------------------------------------------------------------ *
     * On a ticket
     * ------------------------------------------------------------------ */

    /**
     * The customer's other conversations, printed above the reply box.
     *
     * Printed by the class for 4.5-FE-02's reason - small markup, identical in
     * both desks. Renders nothing when there is nothing else, which on a desk
     * without chat is almost always: this is a panel about a problem that only
     * exists once a second channel does.
     */
    public static function panel($jsst_ticketid) {
        $jsst_ticket = self::ticket($jsst_ticketid);
        if (!$jsst_ticket) return '';

        $jsst_other = self::timelineFor(
            (int) $jsst_ticket->uid, (string) $jsst_ticket->email, 6, (int) $jsst_ticket->id);

        if (empty($jsst_other)) return '';

        /* Only worth a panel when more than one channel is involved. A list of
           somebody's previous tickets is what the Customers screen is for, and
           repeating it on every ticket would be noise on the screen an agent
           reads most. */
        $jsst_channels = array();
        foreach ($jsst_other as $jsst_item) $jsst_channels[$jsst_item['channel']] = true;
        $jsst_channels[self::ofTicket($jsst_ticket)] = true;

        if (count($jsst_channels) < 2) return '';

        $jsst_out  = '<div class="jsst-channels">';
        $jsst_out .= '<span class="jsst-channels-lead">'
                   . esc_html(__('This customer has also reached you another way', 'js-support-ticket')) . '</span>';

        foreach ($jsst_other as $jsst_item) {
            $jsst_out .= '<span class="jsst-channels-item">'
                       . '<span class="jsst-chip">' . esc_html(self::label($jsst_item['channel'])) . '</span> '
                       . '<a href="' . esc_url($jsst_item['url']) . '">'
                       . esc_html($jsst_item['title'] !== '' ? $jsst_item['title'] : __('(no subject)', 'js-support-ticket'))
                       . '</a> <span class="jsst-channels-when">'
                       . esc_html(sprintf(
                           /* translators: %s: a human readable interval, e.g. "3 days" */
                           __('%s ago', 'js-support-ticket'), human_time_diff($jsst_item['when'], time())))
                       . '</span></span>';
        }

        return $jsst_out . '</div>';
    }

    private static function ticket($jsst_ticketid) {
        return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT id, uid, email, subject, created, ticketviaemail
               FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            (int) $jsst_ticketid));
    }
}
