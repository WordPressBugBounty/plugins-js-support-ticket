<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Kept for the update FROM 4.0.0, like jsstupdater.php beside it.
 *
 * On a 4.0.0 site whose old updater class is already in memory when WordPress
 * swaps in this version, that class's constructor include_once's this file
 * from the NEW files, and its methods call jsSupportTicketServerCalls
 * statically. Every answer here is "nothing": no update check, no versions, no
 * token, no plugin information - 5.0.0 gets all of that from the licence
 * server through JSSTlicense.
 */
if (!class_exists('jsSupportTicketServerCalls')) {
    class jsSupportTicketServerCalls {

        public static function jsstPluginUpdateCheck() {
            return false;
        }

        public static function jsstPluginUpdateCheckFromCDN() {
            return array();
        }

        public static function jsstGenerateToken() {
            return false;
        }

        public static function jsstGetLatestVersions() {
            return array();
        }

        public static function jsstPluginInformation() {
            return false;
        }

        public static function __callStatic($jsst_name, $jsst_args) {
            return null;
        }
    }
}
