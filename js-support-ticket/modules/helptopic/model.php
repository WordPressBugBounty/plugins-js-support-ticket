<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Topics — part of the free core. (Roadmap 4.0-CORE-06, 4.0-CORE-20)
 *
 * CORE-06 was a behaviour-preserving merge: the add-on's model with prepared
 * statements, a self-healing table check, and the permission checks the write
 * paths were missing. CORE-20 is the upgrade on top of it — the name agents see
 * is now "Topic", and a topic can have a parent, an owning agent, and can be the
 * one marked as the default.
 *
 * The table, module name, task names, nonce names and the helptopicid column on
 * tickets are all still unchanged. The rename is a label, not a migration: a
 * site that has the add-on keeps its topics and its tickets exactly as they are,
 * and the new columns are all nullable with a default that means "as before".
 *
 * Loaded only when the stand-alone Help Topic add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'helptopic' module to the add-on
 * directory while that add-on is active. (Roadmap 4.0-CORE-19)
 */
class JSSThelptopicModel {

    /** Bumped when the table layout below changes. */
    const SCHEMA_VERSION = '400-CORE20';

    /**
     * How deep a topic tree may go, counting the top level as 1.
     *
     * Three is enough to say "Billing → Refunds → Chargebacks" and shallow
     * enough that the indented list on the ticket form stays readable. A tree
     * that has to go deeper is a sign the departments are wrong, not the topics.
     */
    const MAX_DEPTH = 3;

    /**
     * A hard stop when walking up a tree.
     *
     * The loop guard below is what stops a cycle being created, but data that
     * predates it — or that arrived through an import — could already contain
     * one. Every walk is bounded so a corrupt row cannot hang the request.
     */
    const MAX_WALK = 50;

    /**
     * Create the table if this site never had the add-on, and add the columns
     * 4.0 introduces.
     *
     * Self-healing rather than relying on one upgrade route: the table may have
     * been created years ago by the add-on, by an import, or not at all. Each
     * new column is checked before it is added, so an existing table is upgraded
     * in place and a site that already has them reports nothing.
     */
    public static function ensureSchema() {
        // The stored version is only a hint. This table has already been seen
        // to lose parentid/staffid/isdefault while the option still claimed the
        // 4.0 layout, so the columns are checked. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_helptopic_schema', self::SCHEMA_VERSION,
                array('js_ticket_help_topics' => array('parentid', 'staffid', 'isdefault')))) {
            return;
        }
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_help_topics';
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    isactive tinyint(1) DEFAULT NULL,
                    autoresponce tinyint(1) DEFAULT NULL,
                    departmentid int(11) DEFAULT NULL,
                    priorityid int(11) DEFAULT NULL,
                    topic varchar(32) DEFAULT NULL,
                    ordering int(11) NOT NULL,
                    created datetime DEFAULT NULL,
                    updated datetime DEFAULT NULL,
                    status tinyint(1) DEFAULT NULL,
                    PRIMARY KEY (id)
                ) " . $jsst_charset);

        // The 4.0 columns. Every one of them defaults to the behaviour the site
        // already had: no parent, no owner, not the default topic.
        // (Roadmap 4.0-CORE-20)
        $jsst_columns = jssupportticket::$_db->get_col('SHOW COLUMNS FROM `' . $jsst_table . '`', 0);
        if (!is_array($jsst_columns)) {
            $jsst_columns = array();
        }
        $jsst_wanted = array(
            'parentid'  => "ADD `parentid` int(11) DEFAULT NULL",
            'staffid'   => "ADD `staffid` int(11) DEFAULT NULL",
            'isdefault' => "ADD `isdefault` tinyint(1) NOT NULL DEFAULT '0'",
        );
        foreach ($jsst_wanted as $jsst_column => $jsst_clause) {
            if (!in_array($jsst_column, $jsst_columns, true)) {
                jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ' . $jsst_clause);
            }
        }

        // Topics are read by department and by status on every ticket form, and
        // by parent whenever the tree is drawn. (Roadmap 4.0-PERF-01)
        $jsst_indexes = jssupportticket::$_db->get_col('SHOW INDEX FROM `' . $jsst_table . '`', 2);
        if (!is_array($jsst_indexes)) {
            $jsst_indexes = array();
        }
        if (!in_array('jsst_department', $jsst_indexes, true)) {
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_department` (`departmentid`, `status`)');
        }
        if (!in_array('jsst_parent', $jsst_indexes, true)) {
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_parent` (`parentid`)');
        }

        update_option('jsst_helptopic_schema', self::SCHEMA_VERSION, false);
    }

    /* ------------------------------------------------------------------ *
     * Nesting. (Roadmap 4.0-CORE-20)
     * ------------------------------------------------------------------ */

    /**
     * The parent of one topic, or 0.
     */
    private static function parentOf($jsst_id) {
        if (!is_numeric($jsst_id) || (int) $jsst_id <= 0) {
            return 0;
        }
        $jsst_parent = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT parentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE id = %d",
            (int) $jsst_id
        ));
        return (int) $jsst_parent;
    }

    /**
     * Would making $jsst_parentid the parent of $jsst_id create a loop?
     *
     * True when the proposed parent is the topic itself or any of its
     * descendants — the two ways a tree can be made to point back at itself.
     * Walked upwards from the proposed parent, which is bounded work and needs
     * no recursion.
     */
    public static function wouldLoop($jsst_id, $jsst_parentid) {
        $jsst_id = (int) $jsst_id;
        $jsst_parentid = (int) $jsst_parentid;
        if ($jsst_id <= 0 || $jsst_parentid <= 0) {
            return false;
        }
        if ($jsst_id === $jsst_parentid) {
            return true;
        }
        $jsst_walk = $jsst_parentid;
        $jsst_steps = 0;
        while ($jsst_walk > 0 && $jsst_steps < self::MAX_WALK) {
            $jsst_walk = self::parentOf($jsst_walk);
            if ($jsst_walk === $jsst_id) {
                return true;
            }
            $jsst_steps++;
        }
        return false;
    }

    /**
     * How many levels down a topic sits, counting the top level as 1.
     */
    public static function depthOf($jsst_id) {
        $jsst_depth = 1;
        $jsst_walk = (int) $jsst_id;
        $jsst_steps = 0;
        while ($jsst_walk > 0 && $jsst_steps < self::MAX_WALK) {
            $jsst_walk = self::parentOf($jsst_walk);
            if ($jsst_walk > 0) {
                $jsst_depth++;
            }
            $jsst_steps++;
        }
        return $jsst_depth;
    }

    /**
     * How many levels of descendants hang below a topic. A leaf is 0.
     */
    public static function subtreeHeight($jsst_id) {
        $jsst_id = (int) $jsst_id;
        if ($jsst_id <= 0) {
            return 0;
        }
        $jsst_height = 0;
        $jsst_level = array($jsst_id);
        $jsst_steps = 0;
        while (!empty($jsst_level) && $jsst_steps < self::MAX_WALK) {
            $jsst_placeholders = implode(',', array_fill(0, count($jsst_level), '%d'));
            $jsst_children = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
                "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics`
                    WHERE parentid IN (" . $jsst_placeholders . ")",
                $jsst_level
            ));
            if (empty($jsst_children)) {
                break;
            }
            $jsst_height++;
            $jsst_level = array_map('intval', $jsst_children);
            $jsst_steps++;
        }
        return $jsst_height;
    }

    /**
     * How many children a topic has.
     */
    public static function countChildren($jsst_id) {
        if (!is_numeric($jsst_id)) {
            return 0;
        }
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE parentid = %d",
            (int) $jsst_id
        ));
    }

    /**
     * Check a proposed parent, and say why it is refused.
     *
     * @return true|string true when allowed, otherwise the reason to show.
     */
    public static function checkParent($jsst_id, $jsst_parentid) {
        $jsst_id = (int) $jsst_id;
        $jsst_parentid = (int) $jsst_parentid;
        if ($jsst_parentid <= 0) {
            return true; // top level is always allowed
        }
        if ($jsst_id > 0 && $jsst_id === $jsst_parentid) {
            return esc_html(__('A topic cannot be its own parent.', 'js-support-ticket'));
        }
        if (self::wouldLoop($jsst_id, $jsst_parentid)) {
            return esc_html(__('That parent sits underneath this topic, so choosing it would make a loop.', 'js-support-ticket'));
        }
        // The new depth of this topic, plus however far its own descendants
        // already reach below it. Moving a branch has to keep the whole branch
        // inside the limit, not just the topic being moved.
        $jsst_depth = self::depthOf($jsst_parentid) + 1;
        if ($jsst_id > 0) {
            $jsst_depth += self::subtreeHeight($jsst_id);
        }
        if ($jsst_depth > self::MAX_DEPTH) {
            return sprintf(
                /* translators: %d: how many levels of topics are allowed */
                esc_html(__('Topics can be nested %d levels deep. Choose a parent nearer the top.', 'js-support-ticket')),
                self::MAX_DEPTH
            );
        }
        return true;
    }

    /**
     * Every topic that may be offered as a parent of $jsst_id, as a tree.
     *
     * The topic itself, its descendants, and anything already at the deepest
     * allowed level are left out, so the control cannot offer a choice that the
     * save would then refuse.
     */
    public function getParentsForCombobox($jsst_id = 0) {
        self::ensureSchema();
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT id, topic, parentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` ORDER BY ordering ASC"
        );
        if (!is_array($jsst_rows)) {
            return array();
        }
        $jsst_options = array();
        foreach (self::asTree($jsst_rows) AS $jsst_node) {
            if (self::checkParent($jsst_id, $jsst_node->id) !== true) {
                continue;
            }
            $jsst_options[] = (object) array(
                'id'   => $jsst_node->id,
                // A non-breaking space keeps the indent through esc_html and
                // through a select, where leading whitespace is collapsed.
                'text' => str_repeat("\xC2\xA0\xC2\xA0\xC2\xA0", $jsst_node->jsst_depth - 1) . ($jsst_node->jsst_depth > 1 ? '— ' : '') . $jsst_node->topic,
            );
        }
        return $jsst_options;
    }

    /**
     * Flatten rows into parent-then-children order, each carrying its depth.
     *
     * Done in PHP rather than SQL because the tree is small, bounded by
     * MAX_DEPTH, and read on screens that are already loading the whole list.
     * Any row whose parent is missing is treated as top level, so a broken
     * parentid shows the topic rather than hiding it.
     */
    public static function asTree($jsst_rows) {
        $jsst_children = array();
        $jsst_known = array();
        foreach ($jsst_rows AS $jsst_row) {
            $jsst_known[(int) $jsst_row->id] = true;
        }
        foreach ($jsst_rows AS $jsst_row) {
            $jsst_parent = isset($jsst_row->parentid) ? (int) $jsst_row->parentid : 0;
            if ($jsst_parent > 0 && !isset($jsst_known[$jsst_parent])) {
                $jsst_parent = 0;
            }
            $jsst_children[$jsst_parent][] = $jsst_row;
        }
        $jsst_out = array();
        self::walkTree($jsst_children, 0, 1, $jsst_out);
        // A row inside a cycle is never reached by the walk. Append it flat
        // rather than losing it from the screen entirely.
        if (count($jsst_out) < count($jsst_rows)) {
            $jsst_seen = array();
            foreach ($jsst_out AS $jsst_row) {
                $jsst_seen[(int) $jsst_row->id] = true;
            }
            foreach ($jsst_rows AS $jsst_row) {
                if (!isset($jsst_seen[(int) $jsst_row->id])) {
                    $jsst_row->jsst_depth = 1;
                    $jsst_out[] = $jsst_row;
                }
            }
        }
        return $jsst_out;
    }

    private static function walkTree($jsst_children, $jsst_parent, $jsst_depth, &$jsst_out) {
        if ($jsst_depth > self::MAX_WALK || !isset($jsst_children[$jsst_parent])) {
            return;
        }
        foreach ($jsst_children[$jsst_parent] AS $jsst_row) {
            $jsst_row->jsst_depth = $jsst_depth;
            $jsst_out[] = $jsst_row;
            self::walkTree($jsst_children, (int) $jsst_row->id, $jsst_depth + 1, $jsst_out);
        }
    }

    /* ------------------------------------------------------------------ *
     * The default topic, and what a topic sets on the form.
     * (Roadmap 4.0-CORE-20)
     * ------------------------------------------------------------------ */

    /**
     * The one topic marked as the default, or 0.
     *
     * Only an active topic counts: a default that has been disabled must not go
     * on being preselected on the ticket form.
     */
    public static function getDefaultTopicId() {
        self::ensureSchema();
        $jsst_id = jssupportticket::$_db->get_var(
            "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics`
                WHERE isdefault = 1 AND status = 1 ORDER BY ordering ASC LIMIT 1"
        );
        return (int) $jsst_id;
    }

    /**
     * Make this topic the only default.
     */
    private static function setDefaultTopic($jsst_id) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_help_topics';
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . $jsst_table . "` SET isdefault = 0 WHERE id != %d",
            (int) $jsst_id
        ));
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . $jsst_table . "` SET isdefault = 1 WHERE id = %d",
            (int) $jsst_id
        ));
    }

    /**
     * The department and priority a topic routes to.
     *
     * Used by the ticket form rules. Returns an empty array for a topic that
     * does not exist, so a stale id on a form sets nothing rather than failing.
     */
    public static function getRouting($jsst_id) {
        if (!is_numeric($jsst_id) || (int) $jsst_id <= 0) {
            return array();
        }
        self::ensureSchema();
        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT departmentid, priorityid FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics`
                WHERE id = %d AND status = 1",
            (int) $jsst_id
        ));
        if (empty($jsst_row)) {
            return array();
        }
        return array(
            'departmentid' => (int) $jsst_row->departmentid,
            'priorityid'   => (int) $jsst_row->priorityid,
        );
    }

    /**
     * The browser half of the form rules. (Roadmap 4.0-CORE-20)
     *
     * Fills the department and priority in front of the person filling in the
     * form, so they can see what the topic did and change it, rather than being
     * surprised by routing that only happened after they pressed send. The
     * server-side rule in applyFormRules() is still what decides: this is a
     * convenience, and a form with JavaScript off is routed identically.
     *
     * The map covers every active topic, not just the ones currently in the
     * select. Choosing a department replaces that select over AJAX, and a map
     * built from what was on screen at page load would go stale the moment it
     * did. For the same reason the change is bound to the document rather than
     * to the element.
     */
    public static function formRulesScript($jsst_autofilled = array()) {
        $jsst_map = array();
        $jsst_model = new self();
        foreach ($jsst_model->getHelpTopicsForCombobox(0) AS $jsst_topic) {
            $jsst_map[(int) $jsst_topic->id] = array(
                'departmentid' => isset($jsst_topic->jsst_departmentid) ? (int) $jsst_topic->jsst_departmentid : 0,
                'priorityid'   => isset($jsst_topic->jsst_priorityid) ? (int) $jsst_topic->jsst_priorityid : 0,
            );
        }
        if (empty($jsst_map)) {
            return '';
        }
        $jsst_json = wp_json_encode($jsst_map, JSON_HEX_TAG | JSON_HEX_AMP);
        /* Which fields the template filled in by itself. Only these may be
           rewritten when the topic changes. */
        $jsst_seed = wp_json_encode(array_values(array_intersect(
            (array) $jsst_autofilled,
            array('departmentid', 'priorityid')
        )));
        return "<script type=\"text/javascript\">\n"
            . "(function(){\n"
            . "    var jsstTopicRouting = " . $jsst_json . ";\n"
            . "    /* Fields holding a value this script or the template chose,\n"
            . "       as opposed to one a person chose. Seeded from the render so\n"
            . "       the first topic change can replace a default that was filled\n"
            . "       in before anybody touched the form. */\n"
            . "    var jsstAuto = {};\n"
            . "    var jsstSeed = " . $jsst_seed . ";\n"
            . "    for (var s = 0; s !== jsstSeed.length; s++) { jsstAuto[jsstSeed[s]] = true; }\n"
            . "    function jsstApplyTopicRules(){\n"
            . "        var topic = document.getElementById('helptopicid');\n"
            . "        if (!topic) { return; }\n"
            . "        var rule = jsstTopicRouting[topic.value];\n"
            . "        if (!rule) { return; }\n"
            . "        var fields = ['departmentid', 'priorityid'];\n"
            . "        for (var i = 0; i !== fields.length; i++) {\n"
            . "            var name = fields[i];\n"
            . "            var field = document.getElementById(name);\n"
            . "            if (!field || !rule[name]) { continue; }\n"
            . "            /* A blank is always fillable. A value already there may\n"
            . "               be replaced only when this script or the template put\n"
            . "               it there - never a choice the person made. Testing\n"
            . "               emptiness alone froze the field after the first topic\n"
            . "               change, because the rule could not tell its own output\n"
            . "               from somebody's decision. */\n"
            . "            var blank = (field.value === '' || field.value === '0');\n"
            . "            if (!blank && !jsstAuto[name]) { continue; }\n"
            . "            field.value = rule[name];\n"
            . "            jsstAuto[name] = true;\n"
            . "        }\n"
            . "    }\n"
            . "    document.addEventListener('change', function(e){\n"
            . "        if (!e.target) { return; }\n"
            . "        if (e.target.id === 'helptopicid') { jsstApplyTopicRules(); return; }\n"
            . "        /* Set by hand, so it stops being ours to overwrite. Assigning\n"
            . "           .value from script fires no change event, so the rule never\n"
            . "           clears its own flag this way. */\n"
            . "        if (e.target.id === 'departmentid' || e.target.id === 'priorityid') {\n"
            . "            jsstAuto[e.target.id] = false;\n"
            . "        }\n"
            . "    });\n"
            . "})();\n"
            . "</script>";
    }

    /**
     * Fill in the department and priority a topic implies, without ever
     * overriding a choice that has already been made. (Roadmap 4.0-CORE-20)
     *
     * This is the server-side half of the form rules, and the authoritative one:
     * it also covers a ticket arriving by e-mail piping, by import or from a
     * form whose JavaScript never ran. "Has not already been set" is the whole
     * rule — a customer who picked a department keeps it, and only the blanks
     * are filled.
     */
    public static function applyFormRules($jsst_data) {
        $jsst_topicid = isset($jsst_data['helptopicid']) ? $jsst_data['helptopicid'] : 0;
        if (!is_numeric($jsst_topicid) || (int) $jsst_topicid <= 0) {
            return $jsst_data;
        }
        $jsst_routing = self::getRouting($jsst_topicid);
        if (empty($jsst_routing)) {
            return $jsst_data;
        }
        foreach (array('departmentid', 'priorityid') AS $jsst_field) {
            $jsst_current = isset($jsst_data[$jsst_field]) ? $jsst_data[$jsst_field] : '';
            if ($jsst_current !== '' && $jsst_current !== null && (int) $jsst_current > 0) {
                continue; // already set — leave it alone
            }
            if (!empty($jsst_routing[$jsst_field])) {
                $jsst_data[$jsst_field] = $jsst_routing[$jsst_field];
            }
        }
        return $jsst_data;
    }

    /**
     * May the current user manage topics?
     *
     * The add-on checked this for agents on delete only, which left the save and
     * status paths open to any logged-in user who could reach the task. Core
     * checks all three.
     *
     * The task names still say "Help Topic". They are not labels — they are the
     * permission identifiers stored against every agent role, so renaming them
     * would silently revoke the permission on every existing site. The screens
     * say Topic; the stored keys keep their name. (Roadmap 4.0-CORE-20)
     */
    private static function canManage($jsst_task) {
        if (current_user_can('manage_options')) {
            return true;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            return JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_task) == true;
        }
        return false;
    }

    function getHelpTopics() {
        self::ensureSchema();
        // Filter
        $jsst_helptopic = isset(jssupportticket::$_search['helptopic']) ? jssupportticket::$_search['helptopic']['topic'] : '';
        $jsst_statusid = isset(jssupportticket::$_search['helptopic']) ? jssupportticket::$_search['helptopic']['status'] : '';
        $jsst_pagesize = isset(jssupportticket::$_search['helptopic']) ? jssupportticket::$_search['helptopic']['pagesize'] : '';

        $jsst_where = array();
        $jsst_args = array();
        if ($jsst_helptopic != null) {
            $jsst_where[] = "helptopic.topic LIKE %s";
            $jsst_args[] = '%' . jssupportticket::$_db->esc_like($jsst_helptopic) . '%';
        }
        if (is_numeric($jsst_statusid) && $jsst_statusid >= 0) {
            $jsst_where[] = "helptopic.status = %d";
            $jsst_args[] = $jsst_statusid;
        }
        $jsst_inquery = empty($jsst_where) ? '' : ' WHERE ' . implode(' AND ', $jsst_where);

        jssupportticket::$jsst_data['filter']['topic'] = $jsst_helptopic;
        jssupportticket::$jsst_data['filter']['status'] = $jsst_statusid;
        jssupportticket::$jsst_data['filter']['pagesize'] = $jsst_pagesize;

        // Pagination
        if($jsst_pagesize){
            JSSTpagination::setLimit($jsst_pagesize);
        }
        $jsst_query = "SELECT COUNT(helptopic.id)
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` AS helptopic
					JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS dep ON helptopic.departmentid = dep.id ";
        $jsst_query .= $jsst_inquery;
        if (!empty($jsst_args)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_args);
        }
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total,'helptopics');

        // Data.
        //
        // Read whole and paginated in PHP rather than with a LIMIT, because a
        // tree cannot be ordered by a single column: a child has to follow its
        // parent, and SQL cannot produce that from `ordering` alone. Topics are
        // a taxonomy — a working set of tens, not thousands — so reading them
        // all to sort the tree is cheaper than it looks, and it is the only way
        // the indent stays correct across page boundaries. (Roadmap 4.0-CORE-20)
        $jsst_query = " SELECT helptopic.* ,dep.departmentname AS departmentname
			FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` AS helptopic
			JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS dep on helptopic.departmentid = dep.id
			";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY helptopic.ordering ASC,helptopic.status ASC";
        if (!empty($jsst_args)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_args);
        }
        $jsst_rows = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        $jsst_rows = is_array($jsst_rows) ? self::asTree($jsst_rows) : array();
        jssupportticket::$jsst_data[0] = array_slice($jsst_rows, JSSTpagination::getOffset(), JSSTpagination::getLimit());

        // The owning agent's name for the rows on this page, in one query rather
        // than one per row. (Roadmap 4.0-CORE-20, 4.0-PERF-01)
        jssupportticket::$jsst_data['topic_owners'] = self::getOwnerNames(jssupportticket::$jsst_data[0]);
        return;
    }

    /**
     * The owning agents' names for a set of topic rows, keyed by staff id.
     *
     * Empty when the Agents add-on is not active: without it there are no agents
     * to own anything, and the column is not shown.
     */
    public static function getOwnerNames($jsst_rows) {
        $jsst_out = array();
        if (!in_array('agent', jssupportticket::$_active_addons)) {
            return $jsst_out;
        }
        $jsst_ids = array();
        foreach ((array) $jsst_rows AS $jsst_row) {
            if (!empty($jsst_row->staffid)) {
                $jsst_ids[(int) $jsst_row->staffid] = (int) $jsst_row->staffid;
            }
        }
        if (empty($jsst_ids)) {
            return $jsst_out;
        }
        $jsst_placeholders = implode(',', array_fill(0, count($jsst_ids), '%d'));
        $jsst_staff = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, CONCAT(firstname, ' ', lastname) AS name
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` WHERE id IN (" . $jsst_placeholders . ")",
            array_values($jsst_ids)
        ));
        if (!is_array($jsst_staff)) {
            return $jsst_out;
        }
        foreach ($jsst_staff AS $jsst_member) {
            $jsst_out[(int) $jsst_member->id] = $jsst_member->name;
        }
        return $jsst_out;
    }

    function getHelpTopicForForm($jsst_id) {
        self::ensureSchema();
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = " SELECT helptopic.* ,dep.departmentname AS departmentname
			FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` AS helptopic
			JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS dep on helptopic.departmentid = dep.id
			WHERE helptopic.id = %d";
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_id);
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    private function getNextOrdering() {
        $jsst_query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics`";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_result + 1;
    }

    function storeHelpTopic($jsst_data) {
        $jsst_data['id'] = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        if (!self::canManage($jsst_data['id'] ? 'Edit Help Topic' : 'Add Help Topic')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return false;
        }
        self::ensureSchema();
        if ($jsst_data['id'])
            $jsst_data['updated'] = date_i18n('Y-m-d H:i:s');
        elseif (!$jsst_data['id']) {
            $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);

        if (!$jsst_data['id']) { //new
            $jsst_data['ordering'] = $this->getNextOrdering();
        }

        // The four columns below are filled in by this method rather than
        // taken as they come, which is right for a new topic and wrong for an
        // edit: a form that does not render one of them posts nothing, and
        // "nothing" was being read as "set it to the default". The agent form
        // (tpls/addhelptopic.php) posts only topic, department and status, so
        // every save from it blanked the parent, the owning agent and the
        // default flag; autoresponce is rendered by no form at all — the block
        // in admin_addhelptopic.php is commented out — so every save from
        // either form cleared that one. Same shape as the ticket status reset.
        // One read, and only for an edit. (Roadmap 4.0-CORE-20)
        $jsst_existing = null;
        if ($jsst_data['id']) {
            $jsst_existing = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                    "SELECT autoresponce, parentid, staffid, isdefault FROM `"
                    . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE id = %d",
                    $jsst_data['id']));
        }

        if (isset($jsst_data['autoresponce'])) {
            $jsst_data['autoresponce'] = ($jsst_data['autoresponce'] == 1) ? 1 : 0;
        } elseif ($jsst_existing !== null) {
            $jsst_data['autoresponce'] = (int) $jsst_existing->autoresponce;
        } else {
            $jsst_data['autoresponce'] = 0;
        }

        // Nesting. Checked before anything is written, and refused with the
        // reason rather than silently corrected, so an administrator finds out
        // that the tree they drew is not the tree they got.
        // (Roadmap 4.0-CORE-20)
        if (isset($jsst_data['parentid'])) {
            $jsst_parentid = is_numeric($jsst_data['parentid']) ? (int) $jsst_data['parentid'] : 0;
        } elseif ($jsst_existing !== null) {
            $jsst_parentid = (int) $jsst_existing->parentid;
        } else {
            $jsst_parentid = 0;
        }
        $jsst_parentcheck = self::checkParent($jsst_data['id'], $jsst_parentid);
        if ($jsst_parentcheck !== true) {
            JSSTmessage::setMessage($jsst_parentcheck, 'error');
            return false;
        }
        $jsst_data['parentid'] = $jsst_parentid > 0 ? $jsst_parentid : null;

        // The owning agent. Stored as 0 when nobody owns the topic, and only
        // meaningful while the Agents add-on is managing agents.
        if (isset($jsst_data['staffid'])) {
            $jsst_data['staffid'] = is_numeric($jsst_data['staffid']) && $jsst_data['staffid'] > 0
                    ? (int) $jsst_data['staffid']
                    : null;
        } elseif ($jsst_existing !== null && $jsst_existing->staffid > 0) {
            $jsst_data['staffid'] = (int) $jsst_existing->staffid;
        } else {
            $jsst_data['staffid'] = null;
        }

        // One topic is the default. The column is written here and then made
        // exclusive below, once this row has an id to exclude.
        if (isset($jsst_data['isdefault'])) {
            $jsst_isdefault = ($jsst_data['isdefault'] == 1) ? 1 : 0;
        } elseif ($jsst_existing !== null) {
            $jsst_isdefault = (int) $jsst_existing->isdefault;
        } else {
            $jsst_isdefault = 0;
        }
        $jsst_data['isdefault'] = $jsst_isdefault;

        $jsst_row = JSSTincluder::getJSTable('helptopic');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            // Exactly one default. Done after the store so a new topic has an id
            // to be the exception to the clearing update. Unticking the box on
            // the topic that was the default simply leaves the site with none,
            // which is the same as a site that never set one.
            // (Roadmap 4.0-CORE-20)
            if ($jsst_isdefault) {
                $jsst_savedid = $jsst_data['id'] ? $jsst_data['id'] : $jsst_row->id;
                self::setDefaultTopic($jsst_savedid);
            }
            JSSTmessage::setMessage(__('Topic has been stored', 'js-support-ticket'), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(__('Topic has not been stored', 'js-support-ticket'), 'error');
        }
        return;
    }

    function removeHelpTopic($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if (!self::canManage('Delete Help Topic')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return;
        }
        // A parent is protected while it still has children. Deleting it would
        // leave them pointing at a topic that is gone — they would still be
        // shown, because asTree() treats a missing parent as top level, but the
        // structure an administrator built would be silently flattened.
        // (Roadmap 4.0-CORE-20)
        if (self::countChildren($jsst_id) > 0) {
            JSSTmessage::setMessage(esc_html(__('This topic has topics underneath it. Move or delete those first.', 'js-support-ticket')), 'error');
            return;
        }
        if ($this->canremoveHelpTopic($jsst_id)) {
            // The add-on deleted the row twice — once directly and once through
            // the table class, whose result then decided the message. One delete.
            $jsst_row = JSSTincluder::getJSTable('helptopic');
            if ($jsst_row->delete($jsst_id)) {
                JSSTmessage::setMessage(__('Topic has been deleted', 'js-support-ticket'), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                JSSTmessage::setMessage(__('Topic has not been deleted', 'js-support-ticket'), 'error');
            }
        } else {
            JSSTmessage::setMessage(__('Topic in use cannot be deleted', 'js-support-ticket'), 'error');
        }
        return;
    }

    private function canremoveHelpTopic($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE helptopicid = %d",
            $jsst_id
        );
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result == 0)
            return true; /* if there is no record in ticket  which has this help topic */
        else
            return false; /* if there is ticket having this help topic */
    }

    /**
     * Active topics for a select, as a tree.
     *
     * Children follow their parent and are indented, so the structure survives
     * the trip into a flat <select>. Each option also carries the department and
     * priority the topic routes to, which is what lets the form rules run in the
     * browser without another request. (Roadmap 4.0-CORE-20)
     *
     * Filtering by department can leave a child whose parent is in a different
     * department; asTree() shows it at the top level rather than dropping it.
     */
    function getHelpTopicsForCombobox($jsst_departmentid=0) {
        self::ensureSchema();
        $jsst_query = "SELECT id, topic, parentid, departmentid, priorityid FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE status = 1";
        if($jsst_departmentid > 0){
            $jsst_query = jssupportticket::$_db->prepare($jsst_query . " AND departmentid = %d ORDER BY ordering ASC", $jsst_departmentid);
        }else{
            $jsst_query.= "  ORDER BY ordering ASC";
        }
        $jsst_rows = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if (!is_array($jsst_rows)) {
            return array();
        }
        $jsst_list = array();
        foreach (self::asTree($jsst_rows) AS $jsst_node) {
            $jsst_list[] = (object) array(
                'id'   => $jsst_node->id,
                // Non-breaking spaces: a select collapses ordinary leading
                // whitespace, so an ordinary indent would not survive.
                'text' => str_repeat("\xC2\xA0\xC2\xA0\xC2\xA0", $jsst_node->jsst_depth - 1) . ($jsst_node->jsst_depth > 1 ? '— ' : '') . $jsst_node->topic,
                'jsst_departmentid' => (int) $jsst_node->departmentid,
                'jsst_priorityid'   => (int) $jsst_node->priorityid,
            );
        }
        return $jsst_list;
    }

    function changeStatus($jsst_id) {

        if (!is_numeric($jsst_id))
            return false;
        if (!self::canManage('Edit Help Topic')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return false;
        }

        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE id = %d",
            $jsst_id
        );
        $jsst_status = jssupportticket::$_db->get_var($jsst_query);
        $jsst_status = 1 - $jsst_status;

        $jsst_row = JSSTincluder::getJSTable('helptopic');
        if ($jsst_row->update(array('id' => $jsst_id, 'status' => $jsst_status))) {
            JSSTmessage::setMessage(__('Help topic','js-support-ticket').' '.__('status has been changed', 'js-support-ticket'), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(__('Help topic','js-support-ticket').' '.__('status has not been changed', 'js-support-ticket'), 'error');
        }
        return;
    }

    function setOrdering($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if (!self::canManage('Edit Help Topic')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return false;
        }
        $jsst_order = JSSTrequest::getVar('order', 'get');
        if ($jsst_order == 'down') {
            $jsst_order = ">";
            $jsst_direction = "ASC";
        } else {
            $jsst_order = "<";
            $jsst_direction = "DESC";
        }
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT t.ordering,t.id,t2.ordering AS ordering2
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` AS t,
                     `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` AS t2
                WHERE t.ordering " . $jsst_order . " t2.ordering AND t2.id = %d
                ORDER BY t.ordering " . $jsst_direction . " LIMIT 1",
            $jsst_id
        );
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
        if (empty($jsst_result)) {
            return; // already first or last in that direction
        }

        $jsst_row = JSSTincluder::getJSTable('helptopic');
        if ($jsst_row->update(array('id' => $jsst_id, 'ordering' => $jsst_result->ordering)) && $jsst_row->update(array('id' => $jsst_result->id, 'ordering' => $jsst_result->ordering2))) {
            if (jssupportticket::$_db->last_error == null) {
                JSSTmessage::setMessage(__('Help topic','js-support-ticket').' '.__('ordering has been changed', 'js-support-ticket'), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(__('Help topic','js-support-ticket').' '.__('ordering has not changed', 'js-support-ticket'), 'error');
            }
        }
        return;
    }

    /* ------------------------------------------------------------------ *
     * What the add-on's main plugin file used to contribute. Registered by the
     * core bootstrap only when core owns the feature, so the two can never both
     * add the same join. (Roadmap 4.0-CORE-06, 4.0-CORE-19)
     * ------------------------------------------------------------------ */

    /**
     * Puts the topic name on ticket list and mail queries.
     */
    public static function ticketListQuery() {
        // This join is what makes every ticket list and mail query depend on
        // the topics table, and JSSTticketModel never goes through this module
        // - so on a site that never had the legacy addon nothing else would
        // have created it, and the list query fails before any topic screen is
        // ever opened. (Roadmap 4.0-CORE-19)
        JSSTmergedaddon::ensureSchema('helptopic');
        jssupportticket::$_addon_query['select'] .= " ,helptopic.topic ";
        jssupportticket::$_addon_query['join'] .= " LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` AS helptopic ON helptopic.id = ticket.helptopicid ";
    }

    /**
     * Puts the topic name on the ticket detail query.
     */
    public static function ticketDetailQuery() {
        // Same dependency as ticketListQuery(), on the detail query.
        JSSTmergedaddon::ensureSchema('helptopic');
        jssupportticket::$_addon_query['select'] .= " , helptopic.topic AS helptopic ";
        jssupportticket::$_addon_query['join'] .= " LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` AS helptopic ON ticket.helptopicid = helptopic.id ";
    }

    /**
     * The search state for the topic list screens, read from the submitted form
     * or from the saved search cookie.
     */
    public static function handleSearchFormData() {
        $jsst_jstlay = '';
        if(isset($_REQUEST['jstlay'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['jstlay']);
        }elseif(isset($_REQUEST['page'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['page']);
        }elseif(isset($_REQUEST['jshdlay'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['jshdlay']);
        }
        if (!in_array($jsst_jstlay, array('helptopic', 'helptopics', 'agenthelptopics'), true)) {
            return;
        }
        $jsst_callfrom = 0;
        if(isset($_REQUEST['JSST_form_search']) && $_REQUEST['JSST_form_search'] == 'JSST_SEARCH'){
            $jsst_callfrom = 1;
        }elseif(JSSTrequest::getVar('pagenum', 'get', null) != null){
            $jsst_callfrom = 2;
        }
        $jsst_setcookies = false;
        $jsst_ticket_search_cookie_data = '';
        $jsst_search_array = array();
        if($jsst_callfrom == 1){
            $jsst_nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $jsst_nonce, 'helptopic') ) {
                die( 'Security check Failed' );
            }
            $jsst_search_array['topic'] = JSSTrequest::getVar('topic');
            $jsst_search_array['status'] = JSSTrequest::getVar('status');
            $jsst_search_array['pagesize'] = absint(JSSTrequest::getVar('pagesize'));
            $jsst_search_array['search_from_helptopic'] = 1;
            $jsst_setcookies = true;
        }elseif($jsst_callfrom == 2){
            if(isset($_COOKIE['jsst_ticket_search_data'])){
                $jsst_ticket_search_cookie_data = $_COOKIE['jsst_ticket_search_data'];
                $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
            }
            if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_helptopic'])){
                $jsst_search_array['topic'] = $jsst_ticket_search_cookie_data['topic'];
                $jsst_search_array['status'] = $jsst_ticket_search_cookie_data['status'];
                $jsst_search_array['pagesize'] = $jsst_ticket_search_cookie_data['pagesize'];
            }
        }else{
            $jsst_search_array = array();
            jssupportticket::removeusersearchcookies();
        }
        jssupportticket::$_search['helptopic']['topic'] = isset($jsst_search_array['topic']) ? $jsst_search_array['topic'] : null;
        jssupportticket::$_search['helptopic']['status'] = isset($jsst_search_array['status']) ? $jsst_search_array['status'] : null;
        jssupportticket::$_search['helptopic']['pagesize'] = isset($jsst_search_array['pagesize']) ? $jsst_search_array['pagesize'] : null;

        if($jsst_setcookies){
            jssupportticket::setusersearchcookies($jsst_setcookies,$jsst_search_array);
        }
    }
}

?>
