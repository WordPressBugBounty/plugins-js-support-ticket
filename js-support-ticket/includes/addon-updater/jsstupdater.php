<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Kept for the update FROM 4.0.0, and for nothing else.
 *
 * 5.0.0 no longer updates add-ons this way (the licence server does, through
 * JSSTlicense), so the old updater and its folder went. But while WordPress
 * replaces 4.0.0 with this version, the code still running is 4.0.0's: its
 * upgrader_process_complete handler calls its own JSSTAddonsAutoUpdate(),
 * which does
 *
 *     require_once JSST_PLUGIN_PATH . 'includes/addon-updater/jsstupdater.php';
 *     new JS_SUPPORTTICKETUpdater();
 *     ->getPluginVersionDataFromCDN();
 *
 * against the NEW files on disk. Without this file every update from a 4.0.0
 * site that had its add-ons active (so the old model was already loaded) ended
 * in a fatal error - found in the 29 September 2026 upgrade test with 23 old
 * add-ons active. The empty-method fix in the model does not reach this case:
 * the model running is 4.0.0's, already in memory.
 *
 * Answering "no versions" makes 4.0.0's loop find nothing to update and
 * return. Nothing is hooked, nothing is fetched.
 */
if (!class_exists('JS_SUPPORTTICKETUpdater')) {
    class JS_SUPPORTTICKETUpdater {

        public $jsst_addon_installed_array = '';

        public $jsst_addon_installed_version_data = '';

        public function __construct() {
        }

        public function getPluginVersionDataFromCDN() {
            return array();
        }

        /** Anything else 4.0.0 might ask of it is answered with nothing. */
        public function __call($jsst_name, $jsst_args) {
            return null;
        }
    }
}
