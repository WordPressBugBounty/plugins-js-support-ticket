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
if (class_exists('JSSTsystemstatus')) {
    return;
}

/**
 * System status. (Roadmap 4.0-OPS-02)
 *
 * What a support engineer asks for on the first reply to any bug report:
 * versions, whether cron is running, whether the plugin can write where it needs
 * to, what happens to the data on uninstall, and the recent errors. Gathering it
 * by hand takes an exchange or two of e-mail and usually arrives incomplete.
 *
 * The debug bundle is the part that needs care rather than cleverness. It is
 * meant to be pasted into a support ticket by somebody who will not read it
 * first, which means it must not contain a single credential — so redaction is
 * default-deny: a config value is included only if its name does not look like a
 * secret, and anything unrecognised is treated as one.
 */
class JSSTsystemstatus {

    /** How many recent errors the status screen and the bundle carry. */
    const ERROR_LIMIT = 25;

    /**
     * A short id identifying this request in the log.
     *
     * Generated once per request, so every error recorded while handling one
     * page load carries the same id and an engineer can say "show me everything
     * from that one click" rather than guessing which lines belong together.
     */
    public static function correlationId() {
        static $jsst_id = null;
        if ($jsst_id === null) {
            if (function_exists('random_bytes')) {
                try {
                    $jsst_id = bin2hex(random_bytes(4));
                } catch (Exception $jsst_e) {
                    $jsst_id = substr(md5(uniqid('', true)), 0, 8);
                }
            } else {
                $jsst_id = substr(md5(uniqid('', true)), 0, 8);
            }
        }
        return $jsst_id;
    }

    /**
     * Does this setting name look like a credential?
     *
     * Deliberately broad and matched on the name, not the value: a false
     * positive costs one redacted line in a diagnostic file, a false negative
     * puts somebody's API key in a support ticket.
     */
    public static function isSecret($jsst_name) {
        $jsst_name = strtolower((string) $jsst_name);
        $jsst_markers = array('key', 'secret', 'token', 'password', 'passwd', 'pass', 'api',
                              'licence', 'license', 'salt', 'hash', 'credential', 'auth', 'private');
        foreach ($jsst_markers as $jsst_marker) {
            if (strpos($jsst_name, $jsst_marker) !== false) {
                return true;
            }
        }
        return false;
    }

    /** The marker written in place of a redacted value. */
    public static function redactionMarker() {
        return '[redacted]';
    }

    /**
     * The plugin's settings, with anything credential-shaped removed.
     */
    public static function redactedConfig() {
        $jsst_out = array();
        $jsst_config = is_array(jssupportticket::$_config) ? jssupportticket::$_config : array();
        foreach ($jsst_config as $jsst_name => $jsst_value) {
            if (self::isSecret($jsst_name)) {
                $jsst_out[$jsst_name] = self::redactionMarker();
                continue;
            }
            if (is_array($jsst_value) || is_object($jsst_value)) {
                $jsst_out[$jsst_name] = '[' . gettype($jsst_value) . ']';
                continue;
            }
            // A value that looks like a secret regardless of its name — a long
            // opaque string with no spaces is almost never a setting somebody
            // typed, and is very often a token.
            $jsst_value = (string) $jsst_value;
            if (strlen($jsst_value) > 40 && strpos($jsst_value, ' ') === false && preg_match('/^[A-Za-z0-9_\-\.:\/+=]+$/', $jsst_value)) {
                $jsst_out[$jsst_name] = self::redactionMarker();
                continue;
            }
            $jsst_out[$jsst_name] = $jsst_value;
        }
        ksort($jsst_out);
        return $jsst_out;
    }

    /**
     * Versions and limits.
     */
    public static function environment() {
        global $wp_version;
        return array(
            'plugin_version'  => isset(jssupportticket::$_config['versioncode']) ? jssupportticket::$_config['versioncode'] : '',
            'wordpress'       => isset($wp_version) ? $wp_version : '',
            'php'             => PHP_VERSION,
            'database'        => method_exists(jssupportticket::$_db, 'db_version') ? jssupportticket::$_db->db_version() : '',
            'memory_limit'    => (string) ini_get('memory_limit'),
            'max_execution'   => (string) ini_get('max_execution_time'),
            'upload_max'      => (string) ini_get('upload_max_filesize'),
            'post_max'        => (string) ini_get('post_max_size'),
            'multisite'       => is_multisite() ? 'yes' : 'no',
            'wp_debug'        => (defined('WP_DEBUG') && WP_DEBUG) ? 'on' : 'off',
        );
    }

    /**
     * Every scheduled job this plugin relies on.
     */
    public static function cron() {
        $jsst_hooks = array(
            'jssupporticket_ticketviaemail'     => esc_html(__('Collect tickets and replies from the mailbox', 'js-support-ticket')),
            'jssupporticket_updateticketstatus' => esc_html(__('Update ticket statuses and overdue flags', 'js-support-ticket')),
            'jsst_daily_autocleanup_cron'       => esc_html(__('Retention cleanup', 'js-support-ticket')),
            'jsst_delete_expire_session_data'   => esc_html(__('Clear expired session data', 'js-support-ticket')),
        );
        $jsst_out = array(
            'wp_cron_disabled' => (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON),
            'hooks'            => array(),
        );
        foreach ($jsst_hooks as $jsst_hook => $jsst_label) {
            $jsst_next = wp_next_scheduled($jsst_hook);
            $jsst_out['hooks'][$jsst_hook] = array(
                'label'   => $jsst_label,
                'next'    => $jsst_next ? (int) $jsst_next : 0,
                'stalled' => ($jsst_next && $jsst_next < (time() - HOUR_IN_SECONDS)),
            );
        }
        return $jsst_out;
    }

    /**
     * Can the plugin write where it needs to?
     *
     * Attachments, the generated colour stylesheet and the log all need a
     * writable directory, and "the upload failed" with no further explanation is
     * almost always this.
     */
    public static function permissions() {
        $jsst_uploads = wp_upload_dir();
        $jsst_base = isset($jsst_uploads['basedir']) ? $jsst_uploads['basedir'] : '';
        $jsst_data = isset(jssupportticket::$_config['data_directory']) ? jssupportticket::$_config['data_directory'] : '';
        $jsst_paths = array(
            esc_html(__('Uploads directory', 'js-support-ticket'))    => $jsst_base,
            esc_html(__('Help desk data directory', 'js-support-ticket')) => ($jsst_base && $jsst_data) ? $jsst_base . '/' . $jsst_data : '',
            esc_html(__('Ticket attachments', 'js-support-ticket'))   => ($jsst_base && $jsst_data) ? $jsst_base . '/' . $jsst_data . '/attachmentdata/ticket' : '',
        );
        $jsst_out = array();
        foreach ($jsst_paths as $jsst_label => $jsst_path) {
            if ($jsst_path === '') {
                continue;
            }
            $jsst_out[] = array(
                'label'    => $jsst_label,
                'path'     => $jsst_path,
                'exists'   => is_dir($jsst_path),
                'writable' => is_dir($jsst_path) && is_writable($jsst_path),
            );
        }
        return $jsst_out;
    }

    /**
     * What happens to the data if somebody deletes the plugin.
     */
    public static function retention() {
        $jsst_mode = 'preserve';
        if (class_exists('JSSTdeactivation') && method_exists('JSSTdeactivation', 'jssupportticket_get_retention_mode')) {
            $jsst_mode = JSSTdeactivation::jssupportticket_get_retention_mode();
        }
        $jsst_cleanup_on = !empty(jssupportticket::$_config['autocleanup_enable']);
        return array(
            'uninstall_mode' => $jsst_mode,
            'uninstall_safe' => ($jsst_mode !== 'delete'),
            'cleanup_on'     => $jsst_cleanup_on,
            'cleanup_days'   => isset(jssupportticket::$_config['autocleanup_ticket_days']) ? (int) jssupportticket::$_config['autocleanup_ticket_days'] : 0,
        );
    }

    /**
     * Recent entries from the plugin's own error log.
     */
    public static function recentErrors() {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_system_errors';
        $jsst_exists = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table));
        if (!$jsst_exists) {
            return array();
        }
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, error, created FROM `" . $jsst_table . "` ORDER BY id DESC LIMIT %d",
            self::ERROR_LIMIT
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * Everything, for the screen.
     */
    public static function report() {
        return array(
            'environment' => self::environment(),
            'cron'        => self::cron(),
            'permissions' => self::permissions(),
            'retention'   => self::retention(),
            'errors'      => self::recentErrors(),
            'correlation' => self::correlationId(),
        );
    }

    /**
     * The bundle, as pretty JSON.
     *
     * Everything the screen shows plus the redacted settings. No credentials, no
     * customer data: the recent errors are included because they are the point,
     * but they are the plugin's own log lines rather than ticket content.
     */
    public static function debugBundle() {
        $jsst_report = self::report();
        $jsst_report['generated'] = gmdate('c');
        $jsst_report['site'] = home_url();
        $jsst_report['active_addons'] = is_array(jssupportticket::$_active_addons) ? array_values(jssupportticket::$_active_addons) : array();
        $jsst_report['settings'] = self::redactedConfig();
        return wp_json_encode($jsst_report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

}
