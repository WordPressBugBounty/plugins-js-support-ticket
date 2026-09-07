<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTupdates {

    /**
     * Option recording the release whose SQL file has been applied.
     *
     * The version comparison below only ever runs the files *between* two
     * versions, which is right for an upgrade and wrong for everything else: a
     * fresh install seeds js_ticket_config with the current versioncode before
     * this class runs, so installed and current are already equal and the
     * current release's own file never executes. Everything that file owns —
     * in 4.0, every table absorbed from a merged add-on — is then missing on a
     * brand new site, and the first screen that reads one reports the table as
     * not existing. (Roadmap 4.0-CORE-19)
     */
    const APPLIED_OPTION = 'jsst_sql_applied';

    static function checkUpdates($jsst_cversion=null) {
        if (is_null($jsst_cversion)) {
            $jsst_cversion = jssupportticket::$_currentversion;
        }
        $jsst_installedversion = JSSTupdates::getInstalledVersion();
        if ($jsst_installedversion == $jsst_cversion) {
            // Fresh install, or a reactivation of a site whose current-release
            // file never ran. Applying just that file is safe however many
            // times it happens: every statement in it is CREATE TABLE IF NOT
            // EXISTS, INSERT IGNORE or REPLACE, so it cannot conflict with what
            // activation has already created or overwrite a configured value.
            if ((string) $jsst_cversion === (string) jssupportticket::$_currentversion
                && get_option(self::APPLIED_OPTION) !== (string) $jsst_cversion) {
                // Marked only on success. Only a file that actually ran
                // may record the release.
                // applySqlFiles() returns how many it applied, so 0 - meaning
                // it found no such file - has to fail here exactly as false
                // does. Recording 400 for a file that was never opened is what
                // made the missing tables permanent, because this option is
                // then what stops it ever being tried again.
                if (JSSTupdates::applySqlFiles($jsst_cversion, $jsst_cversion)) {
                    update_option(self::APPLIED_OPTION, (string) $jsst_cversion, false);
                }
            }
            return;
        }
        if ($jsst_installedversion != $jsst_cversion) {
			//UPDATE the last_version of the plugin
			$jsst_query = "REPLACE INTO `".jssupportticket::$_db->prefix."js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('last_version','','default');";
			jssupportticket::$_db->query($jsst_query); //old actual
			/*jssupportticket::$_db->show_errors(false);
			@jssupportticket::$_db->query($jsst_query);			*/
			$jsst_query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname='versioncode'";
			$jsst_versioncode = jssupportticket::$_db->get_var($jsst_query);
			$jsst_versioncode = jssupportticketphplib::JSST_str_replace('.','',$jsst_versioncode);
			$jsst_query = jssupportticket::$_db->prepare("UPDATE `".jssupportticket::$_db->prefix."js_ticket_config` SET configvalue = %s WHERE configname = 'last_version';", $jsst_versioncode);
			jssupportticket::$_db->query($jsst_query);
            $jsst_applied = JSSTupdates::applySqlFiles($jsst_installedversion + 1, $jsst_cversion);
            if ($jsst_applied === false) {
                return false;
            }
            // Only a run that targets the release this build ships records the
            // marker, and only when it actually applied something. Callers that
            // pin an older code — the legacy checkUpdates('311') /
            // checkUpdates('317') screens — must not claim the current file has
            // been applied when their range never reached it, and neither may a
            // range that turned out to have no files in it at all.
            if ((string) $jsst_cversion === (string) jssupportticket::$_currentversion && $jsst_applied > 0) {
                update_option(self::APPLIED_OPTION, (string) $jsst_cversion, false);
            }
        }
    }

    /**
     * Run every update SQL file from $jsst_from to $jsst_to inclusive.
     *
     * Missing files are skipped: the version numbers are release codes, not a
     * dense sequence, so most of the range has no file at all. That is exactly
     * why this counts what it applied instead of answering true or false: a
     * caller that needs one particular release's file to have run cannot
     * otherwise tell "there was nothing in the range to do" apart from "the
     * file is sitting right there and was never opened". It used to return
     * true for both. (Roadmap 4.0-CORE-19)
     *
     * @return int|false Files applied, or false when one that is present could
     *                   not be read — the caller must not record the release
     *                   as applied in either the false or the zero case.
     */
    static function applySqlFiles($jsst_from, $jsst_to) {
        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        // Not fatal any more. WP_Filesystem() answers for the filesystem the
        // site has credentials for, which on plenty of hosts is not the one
        // these files live on; returning false here aborted the whole update
        // and left the site with no tables. readSqlFile() falls back to a plain
        // read, which always works because these files ship inside the plugin
        // directory PHP is already running from.
        //
        // Its return value is what has to be carried, not $wp_filesystem: a
        // failed WP_Filesystem() still leaves a live object in that global -
        // an unconnected WP_Filesystem_FTPext when the host resolves to ftpext
        // without credentials - and calling exists() on it is a fatal, not a
        // false.
        $jsst_fs_ok = WP_Filesystem();

        $jsst_applied = 0;
        for ($jsst_i = $jsst_from; $jsst_i <= $jsst_to; $jsst_i++) {
            $jsst_installfile = JSST_PLUGIN_PATH . 'includes/updates/sql/' . $jsst_i . '.sql';
            $jsst_file_content = self::readSqlFile($jsst_installfile, $jsst_fs_ok);

            if ($jsst_file_content === null) {
                continue;   // no file for this release code, which is normal
            }
            if ($jsst_file_content === false) {
                error_log(sprintf(
                    'JS Help Desk: update file %s is present but could not be read; it is not being recorded as applied.',
                    $jsst_installfile
                ));
                return false;
            }

            // Split queries by semicolon (;) followed by a newline or end of string
            // This is more efficient than reading line-by-line in PHP
            $jsst_queries = preg_split("/;(?=\s*$|[\r\n])/m", $jsst_file_content);

            foreach ($jsst_queries as $jsst_query) {
                // The character list is passed explicitly: JSST_trim()
                // defaults it to '', and trim($s, '') strips nothing at
                // all. Without it the chunk after the file's last
                // semicolon is "\n" rather than '', empty() says it is
                // not empty, and the newline is handed to MySQL as a
                // statement — one "Query was empty" per file applied.
                $jsst_query = jssupportticketphplib::JSST_trim($jsst_query, " \t\n\r\0\x0B");

                // Replace the prefix placeholder
                $jsst_query = jssupportticketphplib::JSST_str_replace("#__", jssupportticket::$_db->prefix, $jsst_query);

                if (!empty($jsst_query)) {
                    jssupportticket::$_db->query($jsst_query);
                    // The statements in a file are independent, so one failure
                    // does not stop the rest — but it must not pass unnoticed
                    // either. This is the other way a release ends up marked
                    // applied with its tables missing, and the only trace it
                    // leaves is here.
                    if (jssupportticket::$_db->last_error != null) {
                        error_log(sprintf(
                            'JS Help Desk: %s statement failed: %s',
                            basename($jsst_installfile), jssupportticket::$_db->last_error
                        ));
                    }
                }
            }
            $jsst_applied++;
        }
        return $jsst_applied;
    }

    /**
     * The contents of one bundled update file.
     *
     * WP_Filesystem is asked first, because writing through it is what the
     * plugin is expected to do, but it is the wrong authority on whether a file
     * the plugin ships actually exists: it answers for a filesystem the site may
     * hold no credentials for, and says "not there" for a file PHP can read
     * perfectly well. So a plain read decides, and it is the absence of the file
     * on disk — not WP_Filesystem's opinion — that counts as "no such release".
     *
     * $jsst_use_fs must be WP_Filesystem()'s own return value. The global alone
     * cannot be trusted: a failed init still populates it with an unconnected
     * transport whose exists() throws.
     *
     * @return string|null|false Contents; null when there is no such file;
     *                           false when there is one and it cannot be read.
     */
    private static function readSqlFile($jsst_path, $jsst_use_fs = false) {
        global $wp_filesystem;
        if ($jsst_use_fs && !empty($wp_filesystem) && $wp_filesystem->exists($jsst_path)) {
            $jsst_content = $wp_filesystem->get_contents($jsst_path);
            if ($jsst_content !== false) {
                return $jsst_content;
            }
        }
        if (!file_exists($jsst_path)) {
            return null;
        }
        $jsst_content = file_get_contents($jsst_path);
        return ($jsst_content === false) ? false : $jsst_content;
    }

    static function getInstalledVersion() {
        $jsst_query = "SELECT configvalue FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = 'versioncode'";
        $jsst_version = jssupportticket::$_db->get_var($jsst_query);
        if (!$jsst_version)
            $jsst_version = '102';
        else
            $jsst_version = jssupportticketphplib::JSST_str_replace('.', '', $jsst_version);
        return $jsst_version;
    }

}

?>
