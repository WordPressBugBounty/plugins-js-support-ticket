<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * What installed add-ons ask of core, in 5.0.0.
 *
 * Every add-on - the nine 5.0.0 bundles and any 4.0.0-era add-on still on a
 * site - calls into this class by name: to say it is activating, being
 * switched off, being deleted, or updating its tables. Up to 4.0.0 each of
 * those went to jshelpdesk.com/setup/ with the add-on's own key.
 *
 * 5.0.0 does not talk to /setup/ at all. The licence is one key on the new
 * licence server (JSSTlicense); installing and updating go through it. So the
 * methods here keep their names, because add-ons in the field depend on them,
 * and do their work locally or not at all. Removed outright: the calls to
 * /setup/ themselves, and the housekeeping for the old per-add-on keys.
 * (Roadmap 6.5-ECO-02)
 */
class JSSTpremiumpluginModel {

    /** Activation check. No key is asked for in 5.0.0 - see updateDate(). */
    function verfifyAddonActivation($jsst_addon_name){
        return true;
    }

    /** Was a report to /setup/. Nothing is reported to anyone now. */
    function logAddonDeactivation($jsst_addon_name){
    }

    /** Was a report to /setup/. Nothing is reported to anyone now. */
    function logAddonDeletion($jsst_addon_name){
    }

    /**
     * The table SQL a 4.0.0-era add-on asks for on its first activation.
     *
     * Those add-ons shipped without their SQL and fetched it from /setup/ with
     * their key. An add-on that was already set up on the site never asks, so
     * a site updating from 4.0.0 is unaffected. The one that does ask is an old
     * add-on being installed fresh onto 5.0.0 - and it gets a reason it can
     * print, in the shape it already reads, rather than activating without its
     * tables. Its features live in a 5.0.0 bundle now.
     */
    function verifyAddonSqlFile($jsst_addon_name,$jsst_addon_version){
        return wp_json_encode(array(
            'error_code' => 'jsst_legacy_addon',
            'error'      => __('This add-on is from JS Help Desk 4.0.0 or earlier and cannot be set up on 5.0.0. Its features are in one of the 5.0.0 bundles: install that from Install Add-ons instead.', 'js-support-ticket'),
        ));
    }


    /**
     * Run one statement from a legacy add-on's upgrade file. (Roadmap 6.5-ECO-01)
     *
     * These files are not idempotent: they `ALTER TABLE ... ADD COLUMN`, they
     * `ADD FULLTEXT`, and they `INSERT` permission rows with explicit primary
     * keys. Run a second time - which is exactly what happens on a desk that
     * has both the old stand-alone add-ons and the new bundles installed, each
     * running its own update check - every one of them fails with "Duplicate
     * column name", "Duplicate key name" or "Duplicate entry".
     *
     * None of that is a problem: the column, the index and the row are already
     * there, which is what the statement was trying to achieve. But wpdb writes
     * every one into the log, and a customer mid-migration opens their error
     * log to hundreds of lines that look like a broken install and are not.
     *
     * So the "it is already done" answers are swallowed and everything else is
     * still reported. Swallowing the lot would be easier and would hide a real
     * migration failure on the one day somebody needed to see it.
     */
    private function runUpgradeStatement($jsst_query) {
        $jsst_db = jssupportticket::$_db;
        $jsst_was = $jsst_db->suppress_errors(true);
        $jsst_db->query($jsst_query);
        $jsst_error = $jsst_db->last_error;
        $jsst_db->suppress_errors($jsst_was);
        if ($jsst_error === '') {
            return;
        }
        $jsst_expected = array(
            'Duplicate column name',
            'Duplicate key name',
            'Duplicate entry',
            'already exists',
            'Multiple primary key defined',
            "Can't DROP",
        );
        foreach ($jsst_expected as $jsst_known) {
            if (stripos($jsst_error, $jsst_known) !== false) {
                return;
            }
        }
        /* Not one of the expected ones, so it is worth somebody's attention. */
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JS Help Desk: add-on upgrade statement failed - ' . $jsst_error);
        }
    }

    function getAddonUpdateSqlFromUpdateDir($jsst_installedversion, $jsst_newversion, $jsst_directory) {
        if ($jsst_installedversion != "" && $jsst_newversion != "") {

            // --- INITIALIZE WP_FILESYSTEM ---
            global $wp_filesystem;
            if (!function_exists('wp_handle_upload')) {
                do_action('jssupportticket_load_wp_file');
            }
            if ( ! WP_Filesystem() ) {
                return false;
            }
            $jsst_wp_filesystem = $wp_filesystem;

            for ($jsst_i = ($jsst_installedversion + 1); $jsst_i <= $jsst_newversion; $jsst_i++) {
                $jsst_installfile = $jsst_directory . '/' . $jsst_i . '.sql';

                // Replaced file_exists with $jsst_wp_filesystem->exists
                if ($jsst_wp_filesystem->exists($jsst_installfile)) {
                    
                    // Replaced fopen/fgets/fclose with $jsst_wp_filesystem->get_contents
                    $jsst_file_content = $jsst_wp_filesystem->get_contents($jsst_installfile);

                    if (!empty($jsst_file_content)) {
                        // Split the SQL file into individual queries by semicolon
                        // This handles multi-line queries effectively
                        $jsst_queries = preg_split("/;(?=\s*$|[\r\n])/m", $jsst_file_content);

                        foreach ($jsst_queries as $jsst_query) {
                            $jsst_query = jssupportticketphplib::JSST_trim($jsst_query);
                            
                            // Replace the table prefix placeholder
                            $jsst_query = jssupportticketphplib::JSST_str_replace("#__", jssupportticket::$_db->prefix, $jsst_query);

                            if (!empty($jsst_query)) {
                                $this->runUpgradeStatement($jsst_query);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Update SQL fetched from /setup/, for an add-on that did not ship its own.
     *
     * 5.0.0 bundles ship theirs in their sql/ folder, which
     * getAddonUpdateSqlFromUpdateDir() reads; this is only reached when there
     * is no such folder, which for a 5.0.0 release means no schema change.
     */
    function getAddonUpdateSqlFromLive($jsst_installedversion,$jsst_newversion,$jsst_plugin_slug){
    }
}

?>
