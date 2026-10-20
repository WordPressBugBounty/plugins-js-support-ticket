<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

if (class_exists('JSSTmultiformschema')) {
    return;
}

/**
 * Repairs the Multi Form register, `js_ticket_multiform`, in place.
 *
 * Every definition of the table - the stand-alone Multi Form add-on's and the
 * Customer Experience add-on's - has an `ordering` column, and core sorts the
 * forms by it (JSSTforms::formRows(), the e-mail template form picker, the
 * Forms screen's move up / move down). But both create the table with
 * `CREATE TABLE IF NOT EXISTS`, which cannot touch a table that is already
 * there. A desk whose table was built by an early Multi Form release, before
 * forms could be reordered, keeps the old layout through every upgrade, and
 * every one of those screens answers `Unknown column 'ordering'`.
 *
 * Kept in core rather than in JSSTforms: on a site still running Customer
 * Experience 1.2.0 the add-on's copy of that class is the one that loads, and
 * a repair living there would never run.
 *
 * The table itself is never created here. It belongs to the add-on, and a desk
 * that never had one has nothing to repair.
 */
class JSSTmultiformschema {

    /** Bumped when the columns below change. */
    const SCHEMA_VERSION = '501';

    const OPT_SCHEMA = 'jsst_multiform_schema';

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
    }

    public static function ensureSchema() {
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION,
                array('js_ticket_multiform' => array('ordering')))) {
            return;
        }
        $jsst_columns = JSSTschemaguard::columns('js_ticket_multiform');
        if (empty($jsst_columns)) {
            return;
        }
        if (!in_array('ordering', $jsst_columns, true)) {
            $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_multiform';
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD `ordering` int(11) NOT NULL DEFAULT 0');
            // The forms keep the order they were listed in until now, which
            // was by id - the column's 0 default would leave them all equal.
            jssupportticket::$_db->query('UPDATE `' . $jsst_table . '` SET `ordering` = `id`');
        }
        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
    }
}
