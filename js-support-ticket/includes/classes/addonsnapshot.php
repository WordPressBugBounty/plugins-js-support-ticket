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
if (class_exists('JSSTaddonsnapshot')) {
    return;
}

if (!class_exists('JSSTaddonsnapshot')) {

/**
 * Surviving a legacy add-on's uninstall.php. (Roadmap 6.5-ECO-02)
 *
 * Deleting a plugin in WordPress runs its own uninstall.php, and this product's
 * legacy add-ons drop their tables there. That was correct when the add-on was
 * the only thing that owned them. It stopped being correct the moment the
 * capability moved: the rows are the same rows, so the tables the old add-on is
 * dropping on the way out are the tables the free core - or the bundle that
 * replaced it - is still reading. Nothing in WordPress stops a plugin dropping
 * a table, so the only defence is to take a copy first and put it back after.
 *
 * ## Why this is a class of its own
 *
 * `JSSTmergedaddon` has had exactly this since 4.0 and it works: the add-ons the
 * free core absorbed can be deleted and their data survives. `JSSTbundle` copied
 * that class's *deactivation* guard - parking settings rows across a switch-off -
 * and did not copy its *deletion* guard, so a site that deleted the add-ons a
 * bundle replaced lost every table those add-ons owned. Twenty-two of them, on
 * the site that found this.
 *
 * Two copies of a mechanism where only one is maintained is how that happened,
 * so this is the one copy, and both classes call it. The merged path keeps the
 * behaviour it already had - the option keys below are the ones it already
 * writes, so a site part-way through a delete when it updates still finds its
 * snapshot.
 *
 * ## The rules that make it safe
 *
 * - **Never overwrite a live table.** `restore()` puts a copy back only where
 *   the real table is actually gone. An uninstall that dropped nothing leaves
 *   the copy to be discarded, not swapped in over data that has moved on since.
 * - **Copy structure and rows, not just rows.** `CREATE TABLE ... LIKE` keeps
 *   the indexes and the column types, so what comes back is the table, not an
 *   approximation of it.
 * - **Record what was actually copied.** A table that did not exist, or whose
 *   copy failed, is not listed - so `restore()` never tries to rename a copy
 *   that was never made.
 * - **Leave nothing behind.** The record is deleted once the restore has run,
 *   and a copy that is not needed is dropped rather than left as a stray
 *   `wp_jsst_keep_*` table nobody can explain later.
 */
class JSSTaddonsnapshot {

    /** Prefix for the copy of a table taken before an uninstall. */
    const COPY_PREFIX = 'jsst_keep_';

    /**
     * The option that records what was copied for one add-on.
     *
     * Namespaced per caller so the merged path and the bundle path cannot
     * collide, and spelled exactly as `JSSTmergedaddon` already spelled it so
     * that class's existing snapshots keep being found.
     */
    private static function optionKey($jsst_namespace, $jsst_slug) {
        return $jsst_namespace . '_snapshot_' . $jsst_slug;
    }

    /** Does this table exist right now? */
    private static function tableExists($jsst_table) {
        $jsst_found = jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)
        );
        return ($jsst_found == $jsst_table);
    }

    /**
     * Copy the named tables before an add-on's uninstall.php is included.
     *
     * $jsst_tables are unprefixed names, the way both callers' maps hold them.
     * Returns the list actually copied, which is also what gets recorded.
     */
    public static function snapshot($jsst_namespace, $jsst_slug, $jsst_tables) {
        if (empty($jsst_tables) || !is_array($jsst_tables)) {
            return array();
        }
        $jsst_saved = array();
        foreach ($jsst_tables as $jsst_table) {
            $jsst_live = jssupportticket::$_db->prefix . $jsst_table;
            $jsst_copy = jssupportticket::$_db->prefix . self::COPY_PREFIX . $jsst_table;
            if (!self::tableExists($jsst_live)) {
                continue;
            }
            jssupportticket::$_db->query('DROP TABLE IF EXISTS `' . $jsst_copy . '`');
            jssupportticket::$_db->query('CREATE TABLE `' . $jsst_copy . '` LIKE `' . $jsst_live . '`');
            jssupportticket::$_db->query('INSERT INTO `' . $jsst_copy . '` SELECT * FROM `' . $jsst_live . '`');
            if (jssupportticket::$_db->last_error == null) {
                $jsst_saved[] = $jsst_table;
            }
        }
        if (!empty($jsst_saved)) {
            update_option(self::optionKey($jsst_namespace, $jsst_slug), $jsst_saved, false);
        }
        return $jsst_saved;
    }

    /**
     * Put back whatever the uninstall actually removed.
     *
     * Returns true if at least one table was restored, so a caller can record
     * that it happened and say so on screen.
     */
    public static function restore($jsst_namespace, $jsst_slug) {
        $jsst_key   = self::optionKey($jsst_namespace, $jsst_slug);
        $jsst_saved = get_option($jsst_key);
        if (empty($jsst_saved) || !is_array($jsst_saved)) {
            return false;
        }
        $jsst_restored = false;
        foreach ($jsst_saved as $jsst_table) {
            $jsst_live = jssupportticket::$_db->prefix . $jsst_table;
            $jsst_copy = jssupportticket::$_db->prefix . self::COPY_PREFIX . $jsst_table;
            if (!self::tableExists($jsst_copy)) {
                continue;
            }
            /* The uninstall left this one alone, so the live table is the
               current one and the copy is already out of date. Discard the
               copy; never rename it over data that has moved on. */
            if (self::tableExists($jsst_live)) {
                jssupportticket::$_db->query('DROP TABLE IF EXISTS `' . $jsst_copy . '`');
                continue;
            }
            jssupportticket::$_db->query('RENAME TABLE `' . $jsst_copy . '` TO `' . $jsst_live . '`');
            if (jssupportticket::$_db->last_error == null) {
                $jsst_restored = true;
            }
        }
        delete_option($jsst_key);
        return $jsst_restored;
    }

    /**
     * Map a plugin basename back to an add-on slug.
     *
     * Both callers are handed a plugin file by WordPress and both need the slug,
     * so the translation lives here rather than once in each.
     */
    public static function slugFromPlugin($jsst_plugin) {
        $jsst_dir = jssupportticketphplib::JSST_dirname((string) $jsst_plugin);
        if ($jsst_dir === '' || $jsst_dir === '.') {
            $jsst_dir = pathinfo((string) $jsst_plugin, PATHINFO_FILENAME);
        }
        if (strpos($jsst_dir, 'js-support-ticket-') !== 0) {
            return '';
        }
        return jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_dir);
    }
}

}
