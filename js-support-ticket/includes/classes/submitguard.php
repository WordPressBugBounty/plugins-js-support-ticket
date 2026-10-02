<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
if (class_exists('JSSTsubmitguard')) {
    return;
}
/**
 * One ticket or reply per submission, however many times it arrives.
 *
 * Customers reported duplicate tickets and replies. The cause is the network,
 * not the customer: on a slow connection the first POST is still uploading when
 * they press Submit again, or the browser resends it after a timeout, or they
 * refresh the "thank you" page. The old checks only caught the same subject
 * within 15 seconds (tickets) or any reply by the same user within 7 seconds
 * (replies) - a retry after that got through, two requests arriving at the
 * same moment both passed (each checked before the other had written), and the
 * reply check's email-piping variant was not valid SQL at all.
 *
 * This claims a fingerprint of the submission - who, where, and exactly what
 * they wrote - with an INSERT IGNORE on the options table's unique key, so of
 * two identical requests exactly one wins, whatever the timing. The claim is
 * taken just before the row is written and released again if the write fails,
 * so a submission refused for a wrong captcha or an empty field can simply be
 * sent again. Identical content inside the window is the only thing treated as
 * a duplicate: a second, different message is always accepted.
 *
 * Rows are named jsst_submit_<md5>, never autoloaded, and swept as they
 * expire; uninstall in "delete" mode removes them with every other jsst_ row.
 */
class JSSTsubmitguard {

    const PREFIX = 'jsst_submit_';

    /** How long an identical submission counts as the same one. */
    const WINDOW = 600;

    /**
     * The fingerprint for a submission.
     *
     * @param string $jsst_kind  'ticket' or 'reply'.
     * @param array  $jsst_parts Who, where and what - compared after tags and
     *                           whitespace are normalised, so the same text
     *                           resent from a slower editor still matches.
     */
    public static function key($jsst_kind, array $jsst_parts) {
        $jsst_norm = array();
        foreach ($jsst_parts as $jsst_part) {
            $jsst_part = wp_strip_all_tags((string) $jsst_part);
            $jsst_part = html_entity_decode($jsst_part, ENT_QUOTES, 'UTF-8');
            $jsst_norm[] = strtolower(trim((string) preg_replace('/\s+/u', ' ', $jsst_part)));
        }
        return self::PREFIX . md5((string) $jsst_kind . '|' . implode('|', $jsst_norm));
    }

    /**
     * Claim a submission.
     *
     * @return true|int true when this request is the first; the id of what the
     *                  earlier identical request created; or 0 when that
     *                  request is still being processed after a short wait.
     */
    public static function claim($jsst_key) {
        global $wpdb;
        $jsst_now = time();
        // An expired claim for the same fingerprint no longer counts.
        $wpdb->query($wpdb->prepare(
            "DELETE FROM `{$wpdb->options}` WHERE option_name = %s AND CAST(SUBSTRING_INDEX(option_value, '|', 1) AS UNSIGNED) < %d",
            $jsst_key, $jsst_now - self::WINDOW
        ));
        $jsst_won = $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO `{$wpdb->options}` (option_name, option_value, autoload) VALUES (%s, %s, 'no')",
            $jsst_key, $jsst_now . '|0'
        ));
        if (1 === (int) $jsst_won) {
            self::sweep($jsst_now);
            return true;
        }
        /* An identical request got there first. It usually finishes within a
           second or two; wait for it so the customer can be sent to what it
           created rather than told to check. */
        for ($jsst_i = 0; $jsst_i < 10; $jsst_i++) {
            $jsst_value = (string) $wpdb->get_var($wpdb->prepare("SELECT option_value FROM `{$wpdb->options}` WHERE option_name = %s", $jsst_key));
            $jsst_bits = explode('|', $jsst_value);
            if ('' === $jsst_value) {
                return self::claim($jsst_key); // released meanwhile: try again
            }
            if (isset($jsst_bits[1]) && (int) $jsst_bits[1] > 0) {
                return (int) $jsst_bits[1];
            }
            usleep(500000);
        }
        return 0;
    }

    /** Record what the claimed submission created. */
    public static function done($jsst_key, $jsst_id) {
        global $wpdb;
        $wpdb->update($wpdb->options, array('option_value' => time() . '|' . (int) $jsst_id), array('option_name' => $jsst_key));
    }

    /** The write failed: let the same submission be sent again. */
    public static function release($jsst_key) {
        global $wpdb;
        $wpdb->delete($wpdb->options, array('option_name' => $jsst_key));
    }

    /** Remove a batch of expired claims. */
    private static function sweep($jsst_now) {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE %s AND CAST(SUBSTRING_INDEX(option_value, '|', 1) AS UNSIGNED) < %d LIMIT 100",
            $wpdb->esc_like(self::PREFIX) . '%', $jsst_now - self::WINDOW
        ));
    }
}
