<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

if (class_exists('JSSTformlogicmigration')) {
    return;
}

/**
 * The per-field visibility rules, moved to where conditions live now.
 * (Roadmap 6.5-FORM-04)
 *
 * ## What is being moved
 *
 * A question this site added could be given conditions of its own, edited
 * inside the field editor and stored on the field's own row: `visibleparams`
 * holds them and `visible_field` on each parent holds a comma list of the
 * children watching it. The shape is a list of groups - every group ANDed with
 * the next, the conditions inside a group ORed with each other - and the
 * browser evaluated it that way.
 *
 * The Forms screen now holds conditions for every question, the product's own
 * as well as this site's, and enforces them on the server rather than only in
 * the page. Two mechanisms for one idea is one too many: which screen a rule
 * lived on depended on who had written the question, and only one of the two
 * survived a form posted with JavaScript switched off.
 *
 * ## What this does and does not touch
 *
 * It reads `visibleparams` and writes the same rules into the Forms screen's
 * own store. It does not clear `visibleparams`, and it does not drop a column.
 * Nothing is deleted by this migration at all: if it gets something wrong the
 * old rules are still exactly where they were, and running it again simply
 * overwrites what it wrote last time.
 *
 * ## When it refuses
 *
 * It refuses unless the JSSTforms that will read these rules back understands
 * groups. The Customer Experience add-on shipped its own copy of that class and
 * add-ons load first, so on a site still running 1.2.0 the add-on's copy is the
 * one that answers, and it evaluates a single condition per question. Writing
 * grouped rules where that copy is in charge would replace working browser-side
 * rules with rules the server reads as narrower than they are. Better to leave
 * the site exactly as it is and migrate when the add-on catches up.
 */
class JSSTformlogicmigration {

    /** Set once the move has been done, so it is not repeated on every load. */
    const OPT_DONE = 'jsst_form_logic_migrated';

    /** What it found and what it could not express, for the report. */
    const OPT_REPORT = 'jsst_form_logic_migration_report';

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'run'), 3);
    }

    /**
     * Can the class that will read these rules back evaluate them?
     *
     * Asked of the loaded class rather than of this plugin's version, because
     * which copy is loaded depends on what else is installed.
     */
    public static function supported() {
        return (class_exists('JSSTforms') && defined('JSSTforms::LOGIC_GROUPS'));
    }

    /* =====================================================================
     * Standing the old mechanism down
     * ================================================================== */

    /**
     * Which fields the form-level store has taken charge of. (Roadmap 6.5-FORM-04)
     *
     * Moving the rules is only half of it. The old mechanism is not the stored
     * JSON - it is the `visible` class `JSSTcustomfields` puts on a wrapper and
     * the `getDataForVisibleField()` call it wires to the parent question, and
     * both go on running off the same columns after the rules have been copied
     * out of them. A field governed by both is governed twice: the old half
     * toggles the wrapper's class while `printConditions()` sets its
     * `style.display`, and whichever ran last wins. The old half also drops the
     * required asterisk for anything conditional, so the two disagree about
     * whether an answer is insisted on as well as about whether it is shown.
     *
     * So the old half stands down for exactly the fields that moved, and not
     * one more. A field whose stored rule could not be read did not move, is
     * named in the report, and keeps working exactly as it did - which is the
     * whole reason this migration deletes nothing.
     *
     * Read from the report rather than from the form-level store, and that is
     * deliberate: an administrator who later deletes a migrated rule on the
     * Forms screen has decided that question is always asked, and reviving the
     * old rule underneath them would be this code overruling that.
     */
    public static function movedFields($jsst_formid) {
        static $jsst_cache = null;
        if ($jsst_cache === null) {
            $jsst_cache = array();
            if (get_option(self::OPT_DONE)) {
                foreach ((array) get_option(self::OPT_REPORT, array()) AS $jsst_row) {
                    if (!empty($jsst_row['moved']) && !empty($jsst_row['field'])) {
                        $jsst_cache[(int) $jsst_row['form']][$jsst_row['field']] = true;
                    }
                }
            }
        }
        return isset($jsst_cache[(int) $jsst_formid]) ? $jsst_cache[(int) $jsst_formid] : array();
    }

    /** Has this one field's rule moved to the Forms screen? */
    public static function moved($jsst_field, $jsst_formid) {
        $jsst_moved = self::movedFields($jsst_formid);
        return isset($jsst_moved[(string) $jsst_field]);
    }

    /**
     * A parent's watcher list with the moved children taken out.
     *
     * `visible_field` is a comma list of the questions watching this one, and
     * it is what the old browser half is handed. Filtering it here means a
     * parent that still has one unmigrated watcher goes on driving that one and
     * stops driving the rest, rather than the whole question being all-or-
     * nothing. An empty string back means there is nothing left to wire.
     */
    public static function stillWatching($jsst_list, $jsst_formid) {
        $jsst_list = trim((string) $jsst_list);
        if ($jsst_list === '') {
            return '';
        }
        $jsst_moved = self::movedFields($jsst_formid);
        if (!$jsst_moved) {
            return $jsst_list;
        }
        $jsst_kept = array();
        foreach (explode(',', $jsst_list) AS $jsst_one) {
            $jsst_one = trim($jsst_one);
            if ($jsst_one !== '' && !isset($jsst_moved[$jsst_one])) {
                $jsst_kept[] = $jsst_one;
            }
        }
        return implode(',', $jsst_kept);
    }

    /** Every field carrying a rule in the old shape, whether or not it moved. */
    public static function candidates() {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_fieldsordering';
        return (array) jssupportticket::$_db->get_results(
            "SELECT id, field, fieldtitle, multiformid, visibleparams
               FROM `" . $jsst_table . "`
              WHERE visibleparams IS NOT NULL AND visibleparams != '' AND visibleparams != '[]'
              ORDER BY multiformid ASC, ordering ASC, id ASC");
    }

    /**
     * Read one stored `visibleparams` into the groups the Forms screen keeps.
     *
     * Written to survive what is actually in these columns rather than what the
     * editor meant to put there: the value may be double-encoded, may be a flat
     * list of conditions rather than a list of groups, and may carry rows whose
     * parent field has since been deleted. Anything unreadable returns an empty
     * list and is reported rather than guessed at.
     *
     * @return array list of groups, each a list of when/op/value conditions.
     */
    public static function groupsFrom($jsst_stored) {
        $jsst_raw = $jsst_stored;
        for ($jsst_try = 0; $jsst_try < 2 && is_string($jsst_raw); $jsst_try++) {
            $jsst_raw = json_decode($jsst_raw, true);
        }
        if (!is_array($jsst_raw) || !$jsst_raw) {
            return array();
        }
        /* A flat list of conditions is wrapped, so one shape reaches the loop
           below. The editor wrote groups, but a hand-edited row or an older
           release may not have. */
        $jsst_first = reset($jsst_raw);
        if (isset($jsst_first['visibleParent'])) {
            $jsst_raw = array($jsst_raw);
        }
        $jsst_groups = array();
        foreach ($jsst_raw AS $jsst_group) {
            if (!is_array($jsst_group)) {
                continue;
            }
            $jsst_kept = array();
            foreach ($jsst_group AS $jsst_row) {
                if (!is_array($jsst_row) || empty($jsst_row['visibleParent'])) {
                    continue;
                }
                /* The old editor's four: "1" Equal, "0" Not Equal, "2" Contain,
                   "3" Not Contain (4.0.0). Contain and Not Contain used to be
                   read as Equal here, so a migrated rule silently changed
                   meaning. Anything else is still read as Equal, which is what
                   the old browser half did with an unrecognised value. */
                $jsst_ops = array('1' => 'is', '0' => 'isnot', '2' => 'contains', '3' => 'notcontains');
                $jsst_code = isset($jsst_row['visibleCondition']) ? (string) $jsst_row['visibleCondition'] : '1';
                $jsst_kept[] = array(
                    'when'  => sanitize_text_field($jsst_row['visibleParent']),
                    'op'    => isset($jsst_ops[$jsst_code]) ? $jsst_ops[$jsst_code] : 'is',
                    'value' => sanitize_text_field(isset($jsst_row['visibleValue']) ? $jsst_row['visibleValue'] : ''),
                );
            }
            if ($jsst_kept) {
                $jsst_groups[] = $jsst_kept;
            }
        }
        return $jsst_groups;
    }

    /**
     * Move them.
     *
     * @param bool $jsst_dryrun read and report, write nothing.
     * @return array what happened, per field.
     */
    public static function run($jsst_dryrun = false) {
        if (!$jsst_dryrun && get_option(self::OPT_DONE)) {
            return array();
        }
        if (!self::supported()) {
            return array(array('field' => '', 'title' => '',
                'note' => esc_html(__('Left alone: the ticket form code on this site reads one condition per question, so grouped rules would not be understood. Update the Customer Experience add-on and this runs on the next admin page.', 'js-support-ticket'))));
        }

        $jsst_all = get_option(JSSTforms::OPT_LOGIC, array());
        $jsst_all = is_array($jsst_all) ? $jsst_all : array();
        $jsst_report = array();

        foreach (self::candidates() AS $jsst_row) {
            $jsst_formid = (int) $jsst_row->multiformid;
            $jsst_groups = self::groupsFrom($jsst_row->visibleparams);
            if (!$jsst_groups) {
                $jsst_report[] = array('field' => $jsst_row->field, 'title' => $jsst_row->fieldtitle,
                    'form' => $jsst_formid, 'moved' => false,
                    'note' => esc_html(__('Its stored rule could not be read. Nothing was changed, and the rule is still on the field.', 'js-support-ticket')));
                continue;
            }
            /* A question already carrying a rule on the Forms screen is left as
               it is. Somebody wrote that one deliberately and more recently, and
               overwriting it with the older rule from the other screen would be
               this migration choosing for them. */
            if (isset($jsst_all[$jsst_formid][$jsst_row->field])) {
                $jsst_report[] = array('field' => $jsst_row->field, 'title' => $jsst_row->fieldtitle,
                    'form' => $jsst_formid, 'moved' => false,
                    'note' => esc_html(__('Already has a condition on the Forms screen, which was kept. The older rule is still on the field.', 'js-support-ticket')));
                continue;
            }
            $jsst_first = $jsst_groups[0][0];
            $jsst_entry = array(
                'show'  => $jsst_row->field,
                'when'  => $jsst_first['when'],
                'op'    => $jsst_first['op'],
                'value' => $jsst_first['value'],
            );
            $jsst_count = 0;
            foreach ($jsst_groups AS $jsst_group) {
                $jsst_count += count($jsst_group);
            }
            if ($jsst_count > 1) {
                $jsst_entry['groups'] = $jsst_groups;
            }
            if (!$jsst_dryrun) {
                $jsst_all[$jsst_formid][$jsst_row->field] = $jsst_entry;
            }
            $jsst_report[] = array('field' => $jsst_row->field, 'title' => $jsst_row->fieldtitle,
                'form' => $jsst_formid, 'moved' => true, 'conditions' => $jsst_count,
                'groups' => count($jsst_groups),
                'note' => sprintf(
                    /* translators: 1: how many conditions, 2: how many groups. */
                    esc_html(_n('%1$d condition moved, in %2$d group.', '%1$d conditions moved, in %2$d groups.', count($jsst_groups), 'js-support-ticket')),
                    $jsst_count, count($jsst_groups)));
        }

        if (!$jsst_dryrun) {
            update_option(JSSTforms::OPT_LOGIC, $jsst_all, false);
            update_option(self::OPT_REPORT, $jsst_report, false);
            update_option(self::OPT_DONE, current_time('mysql'), false);
        }
        return $jsst_report;
    }

}
