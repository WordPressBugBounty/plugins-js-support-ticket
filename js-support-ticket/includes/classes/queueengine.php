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
if (class_exists('JSSTqueueengine')) {
    return;
}

/**
 * The queue engine. (Roadmap 4.5-UX-01)
 *
 * A team evaluating a help desk spends its first hour in the queue, and the
 * queue is where this product has been at its weakest: three implementations of
 * one list, filters that differ per workspace, columns an administrator sets
 * once for everybody, saved views nobody can share, bulk actions on one desk
 * only, and a tab row that counts the whole ticket table on every page load.
 *
 * This is one engine underneath all of it. It does not query anything itself -
 * JSSTticketquery already reads tickets, scoped once, and duplicating that here
 * would recreate the exact problem being fixed. What it adds is everything
 * around the query that a queue screen needs and that both workspaces were
 * inventing separately:
 *
 *   filters()  The filter set, described rather than hard-coded into a form, so
 *              the two desks offer the same filters and a new one is added in a
 *              single place.
 *   columns()  What a queue may show, and which of it this person wants shown.
 *              The site-wide listing configuration an administrator already set
 *              stays the default; the per-agent choice narrows or widens it for
 *              one person and nobody else.
 *   counts()   The tab numbers, cached against a version stamp that every
 *              ticket event bumps.
 *   state()    All of the above plus the tabs, the scopes, the views and the
 *              bulk actions, in one call - so a shell renders a queue instead
 *              of assembling one.
 *
 * Everything an actor may see or do still comes from the capability service and
 * the application layer. The engine composes; it does not decide.
 */
class JSSTqueueengine {

    /** Bumped by every ticket event; part of the counts cache key. */
    const OPT_COUNTS_VERSION = 'jsst_queue_counts_version';

    /** Where one person's chosen columns are kept. */
    const META_COLUMNS = 'jsst_queue_columns';

    /**
     * How long a cached count survives with nothing happening.
     *
     * The version stamp is what actually keeps the numbers honest - any ticket
     * event invalidates every cached count at once. This is only the backstop
     * for the ways a ticket row can change without an event: a direct database
     * edit, an import, an add-on writing to the table itself. Five minutes is
     * short enough that nobody files a bug about it and long enough that a busy
     * queue is not recounting on every page.
     */
    const COUNTS_TTL = 300;

    /** More columns than this and the row stops being readable. */
    const MAX_COLUMNS = 10;

    /* The two halves of the catalogue. Ticket fields belong to the site and are
       configured on Fields & Ordering; queue information belongs to the queue
       and exists nowhere else. (Roadmap 4.5-UX-01) */
    const GROUP_TICKET = 'ticket';
    const GROUP_QUEUE  = 'queue';

    /** Counts already worked out this request, keyed by cache key. */
    private static $jsst_counts = array();

    /* =====================================================================
     * Wiring
     * ================================================================== */

    /**
     * Invalidate the cached counts whenever anything happens to a ticket.
     *
     * Subscribed to every event rather than to a chosen few. A queue tab is a
     * count of tickets in a state, and almost every event in the catalogue
     * moves a ticket between states - working out which ones do not would save
     * one option write on a feedback event and would be wrong the first time
     * somebody adds an event that does.
     */
    public static function registerHooks() {
        /* Deleting a ticket is not one of the catalogue's events, so the
           listener below never heard it and the tabs went on counting a
           ticket that no longer existed - "Open 4" over an empty list. Both
           delete paths announce themselves with this action. */
        add_action('jsst-ticketdelete', array(__CLASS__, 'flushCounts'));
        if (!class_exists('JSSTevents')) {
            return;
        }
        JSSTevents::listen('', array(__CLASS__, 'flushCounts'), 1);
    }

    /**
     * Bump the version every cached count is keyed by.
     *
     * Bumping a number is used rather than deleting the transients because the
     * cache is keyed per scope signature and there is no way to enumerate those
     * - and a delete loop that misses one is a queue showing yesterday's
     * numbers to one group of agents and not to the others.
     */
    public static function flushCounts() {
        self::$jsst_counts = array();
        $jsst_version = (int) get_option(self::OPT_COUNTS_VERSION, 0);
        update_option(self::OPT_COUNTS_VERSION, $jsst_version + 1, false);
    }

    /* =====================================================================
     * Columns
     * ================================================================== */

    /**
     * Every column a queue can show.
     *
     * 'field' names the row in the site's listing configuration where one
     * exists, which is how an administrator's existing choices go on being the
     * default. Columns with no 'field' are the queue's own - a ticket reference
     * or a last-activity time is not a form field and never appears on that
     * screen, which is why they could not be turned on until now.
     *
     * 'addon' gates a column on the module that writes its data: a Tags column
     * on a site without tags is an empty column that makes the row narrower for
     * no reason.
     */
    public static function columns() {
        static $jsst_columns = null;
        if ($jsst_columns !== null) {
            return $jsst_columns;
        }
        $jsst_columns = array(
            'ticketid'   => array('label' => __('Reference', 'js-support-ticket'),    'sort' => 'ticketid',  'field' => '',           'default' => true,  'group' => self::GROUP_QUEUE),
            'subject'    => array('label' => __('Subject', 'js-support-ticket'),      'sort' => 'subject',   'field' => 'subject',    'default' => true,  'group' => self::GROUP_TICKET),
            'customer'   => array('label' => __('Customer', 'js-support-ticket'),     'sort' => '',          'field' => 'fullname',   'default' => true,  'group' => self::GROUP_TICKET),
            'email'      => array('label' => __('E-mail', 'js-support-ticket'),       'sort' => '',          'field' => 'email',      'default' => false, 'group' => self::GROUP_TICKET),
            'phone'      => array('label' => __('Telephone', 'js-support-ticket'),    'sort' => '',          'field' => 'phone',      'default' => false, 'group' => self::GROUP_TICKET),
            'department' => array('label' => __('Department', 'js-support-ticket'),   'sort' => '',          'field' => 'department', 'default' => true,  'group' => self::GROUP_TICKET),
            'helptopic'  => array('label' => __('Topic', 'js-support-ticket'),        'sort' => '',          'field' => 'helptopic',  'default' => false, 'group' => self::GROUP_TICKET),
            'product'    => array('label' => __('Product', 'js-support-ticket'),      'sort' => '',          'field' => 'product',    'default' => false, 'group' => self::GROUP_TICKET),
            'priority'   => array('label' => __('Priority', 'js-support-ticket'),     'sort' => 'priority',  'field' => 'priority',   'default' => true,  'group' => self::GROUP_TICKET),
            'status'     => array('label' => __('Status', 'js-support-ticket'),       'sort' => 'status',    'field' => 'status',     'default' => true,  'group' => self::GROUP_TICKET),
            'agent'      => array('label' => __('Assigned to', 'js-support-ticket'),  'sort' => '',          'field' => 'assignto',   'default' => true,  'group' => self::GROUP_TICKET),
            'created'    => array('label' => __('Raised', 'js-support-ticket'),       'sort' => 'created',   'field' => '',           'default' => true,  'group' => self::GROUP_QUEUE),
            'updated'    => array('label' => __('Last activity', 'js-support-ticket'),'sort' => 'updated',   'field' => '',           'default' => false, 'group' => self::GROUP_QUEUE),
            'lastreply'  => array('label' => __('Last reply', 'js-support-ticket'),   'sort' => 'lastreply', 'field' => '',           'default' => false, 'group' => self::GROUP_QUEUE),
            'duedate'    => array('label' => __('Due', 'js-support-ticket'),          'sort' => 'duedate',   'field' => 'duedate',    'default' => false, 'addon' => 'overdue', 'group' => self::GROUP_TICKET),
            /* Default on, unlike the other queue-native columns. Tags have been
               shown on every row that has one since 4.0, and a column picker
               whose arrival silently removes a feature is a bug wearing a
               setting. (Roadmap 4.0-CORE-17) */
            'tags'       => array('label' => __('Tags', 'js-support-ticket'),         'sort' => '',          'field' => '',           'default' => true,  'group' => self::GROUP_QUEUE),
        );
        foreach ($jsst_columns as $jsst_key => $jsst_column) {
            $jsst_columns[$jsst_key]['key'] = $jsst_key;
        }
        /* Everything else the site has decided a ticket carries. (Roadmap 4.5-UX-01) */
        $jsst_columns = array_merge($jsst_columns, self::fieldColumns($jsst_columns));
        $jsst_columns = apply_filters('jsst_queue_columns', $jsst_columns);
        /* Modules add columns through the filter, so the availability gate is
           applied after it rather than before - a module that adds its own
           column and its own gate should get both honoured. */
        foreach ($jsst_columns as $jsst_key => $jsst_column) {
            if (!empty($jsst_column['addon']) && !in_array($jsst_column['addon'], jssupportticket::$_active_addons)) {
                unset($jsst_columns[$jsst_key]);
            }
        }
        return $jsst_columns;
    }

    /**
     * The columns that exist because this site added a field.
     *
     * The hand-written catalogue above is the queue's own vocabulary: it knows
     * that 'agent' is stored as 'assignto', that 'created' can be sorted on and
     * that 'duedate' needs the Overdue module. It cannot know about the fields
     * an administrator adds, and those are not a rare case - a custom field is
     * how this product is fitted to a business, and the field screen has always
     * carried a "show on listing" switch for each one.
     *
     * Before this, the picker governed sixteen columns and quietly did not
     * govern any of the others: a custom field flagged for the listing appeared
     * on every row of both desks with no way for an agent to turn it off, and
     * it did not count towards the ten a queue will show. The picker was
     * telling the truth about a part of the row and nothing about the rest.
     *
     * So the catalogue is now the queue's own columns plus every field the site
     * says may appear on a listing. Fields the site marks as never listable
     * (cannotshowonlisting) are left out, because that is an administrator
     * saying this one is not a column - and a consent tick is left out for the
     * same reason JSSTcustomfields leaves it out, it is not ticket data.
     *
     * A field with the same name on several ticket forms is one column. Which
     * of them a given row actually shows is still decided per ticket, by that
     * ticket's own form - see customFieldsFor().
     *
     * @param array $jsst_known the hand-written catalogue, to map fields already named there.
     * @return array key => column descriptor, keyed 'field_<field>'.
     */
    private static function fieldColumns($jsst_known) {
        $jsst_mapped = array();
        foreach ($jsst_known as $jsst_column) {
            if (!empty($jsst_column['field'])) {
                $jsst_mapped[$jsst_column['field']] = 1;
            }
        }
        $jsst_db = jssupportticket::$_db;
        $jsst_table = $jsst_db->prefix . 'js_ticket_fieldsordering';
        /* The published and admin-only rules are the ones getFieldsForListing()
           and userFieldsData() already apply, mirrored here so that what the
           picker offers and what a row draws can never disagree. */
        $jsst_where = '';
        if (!is_admin()) {
            $jsst_where .= " AND published = 1 AND (adminonly IS NULL OR adminonly != 1) ";
        }
        $jsst_rows = $jsst_db->get_results(
            "SELECT field, fieldtitle, isuserfield, multiformid, showonlisting"
            . " FROM " . $jsst_table
            . " WHERE fieldfor = 1"
            . " AND (cannotshowonlisting IS NULL OR cannotshowonlisting != 1)"
            . " AND (userfieldtype IS NULL OR userfieldtype != 'termsandconditions')"
            . $jsst_where
            . " ORDER BY multiformid, ordering"
        );
        if (!is_array($jsst_rows)) {
            return array();
        }
        $jsst_out = array();
        foreach ($jsst_rows as $jsst_row) {
            $jsst_field = (string) $jsst_row->field;
            if ($jsst_field === '' || isset($jsst_mapped[$jsst_field])) {
                continue;
            }
            $jsst_key = 'field_' . sanitize_key($jsst_field);
            if (isset($jsst_out[$jsst_key])) {
                /* The same field on a second form. One column, on either form,
                   and shown by default if any form asks for it - the picker is
                   a list of what an agent might see, not of what one form has. */
                $jsst_out[$jsst_key]['forms'][] = (int) $jsst_row->multiformid;
                $jsst_out[$jsst_key]['default'] = $jsst_out[$jsst_key]['default'] || ((int) $jsst_row->showonlisting === 1);
                continue;
            }
            $jsst_title = trim((string) $jsst_row->fieldtitle);
            $jsst_out[$jsst_key] = array(
                'key'     => $jsst_key,
                'label'   => ($jsst_title !== '') ? $jsst_title : $jsst_field,
                'sort'    => '',
                'field'   => $jsst_field,
                'default' => ((int) $jsst_row->showonlisting === 1),
                'custom'  => ((int) $jsst_row->isuserfield === 1),
                'forms'   => array((int) $jsst_row->multiformid),
                'group'   => self::GROUP_TICKET,
            );
        }
        return $jsst_out;
    }

    /**
     * The catalogue split into the two questions it answers.
     *
     * A flat list of sixteen ticks was already long; on a site with a dozen
     * custom fields it is a wall. The split is not cosmetic - the two halves
     * have different owners. The ticket fields are the site's, set up on Fields
     * & Ordering and shared with the ticket form and the search; the queue's
     * own are this screen's, and exist nowhere else.
     *
     * @return array group key => array('label' => string, 'columns' => array)
     */
    public static function groupedColumns() {
        $jsst_groups = array(
            self::GROUP_TICKET => array('label' => __('Ticket fields', 'js-support-ticket'), 'columns' => array()),
            self::GROUP_QUEUE  => array('label' => __('Queue information', 'js-support-ticket'), 'columns' => array()),
        );
        foreach (self::columns() as $jsst_key => $jsst_column) {
            $jsst_group = isset($jsst_column['group']) ? $jsst_column['group'] : self::GROUP_TICKET;
            if (!isset($jsst_groups[$jsst_group])) {
                $jsst_groups[$jsst_group] = array('label' => $jsst_group, 'columns' => array());
            }
            $jsst_groups[$jsst_group]['columns'][$jsst_key] = $jsst_column;
        }
        foreach ($jsst_groups as $jsst_key => $jsst_group) {
            if (empty($jsst_group['columns'])) {
                unset($jsst_groups[$jsst_key]);
            }
        }
        return $jsst_groups;
    }

    /**
     * How many columns one person may choose.
     *
     * MAX_COLUMNS is a guard against an unreadable row, not a rule about how
     * this site is configured. If an administrator has flagged twelve fields
     * for the listing then twelve is what everybody already sees, and refusing
     * to save a personal choice of eleven would be this screen arguing with the
     * one next door. The guard is therefore never lower than the site's own
     * default.
     */
    public static function maxColumns() {
        return max(self::MAX_COLUMNS, count(self::defaultColumns()));
    }

    /**
     * The custom fields one row should draw, for the form that row was raised on.
     *
     * Two things were wrong where the templates asked for these directly. They
     * asked userFieldsData() for the *default* ticket form rather than the
     * ticket's own, so on a multiform site every row showed the default form's
     * custom fields - blank ones, for a ticket that was never asked those
     * questions. And they asked outside the column choice entirely, so an agent
     * who turned a custom field off still got it on every row.
     *
     * Memoised per form: the admin queue draws one page of rows from a handful
     * of forms, and this used to be a query per ticket.
     *
     * @param int|string $jsst_formid the ticket's multiformid.
     * @param array|null $jsst_columns the chosen columns, or null to ask.
     * @return array the rows userFieldsData() returns, narrowed to what is wanted.
     */
    public static function customFieldsFor($jsst_formid, $jsst_columns = null) {
        static $jsst_cache = array();
        $jsst_formid = (string) $jsst_formid;
        if (!isset($jsst_cache[$jsst_formid])) {
            $jsst_fields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, 1, $jsst_formid);
            $jsst_cache[$jsst_formid] = is_array($jsst_fields) ? $jsst_fields : array();
        }
        if ($jsst_columns === null) {
            $jsst_columns = self::visibleColumns();
        }
        $jsst_wanted = array();
        foreach ((array) $jsst_columns as $jsst_column) {
            if (!empty($jsst_column['field'])) {
                $jsst_wanted[$jsst_column['field']] = 1;
            }
        }
        $jsst_out = array();
        foreach ($jsst_cache[$jsst_formid] as $jsst_field) {
            if (isset($jsst_wanted[$jsst_field->field])) {
                $jsst_out[] = $jsst_field;
            }
        }
        return $jsst_out;
    }

    /**
     * The columns this site shows when nobody has chosen otherwise.
     *
     * Read from the listing configuration an administrator already maintains,
     * because that screen is where a site says which fields matter to it, and
     * ignoring it here would mean two places to set the same thing and no way
     * to tell which won. A column with no field behind it falls back to the
     * catalogue's own default; the reference and the subject are forced on,
     * because a queue whose rows cannot be told apart or clicked is not a queue.
     */
    public static function defaultColumns() {
        $jsst_listing = array();
        $jsst_model = JSSTincluder::getJSModel('fieldordering');
        if (is_object($jsst_model) && method_exists($jsst_model, 'getFieldsForListing')) {
            $jsst_listing = $jsst_model->getFieldsForListing(1, '');
        }
        if (!is_array($jsst_listing)) {
            $jsst_listing = array();
        }
        $jsst_keys = array();
        foreach (self::columns() as $jsst_key => $jsst_column) {
            $jsst_on = !empty($jsst_column['default']);
            if (!empty($jsst_column['field'])) {
                $jsst_on = !empty($jsst_listing[$jsst_column['field']]);
            }
            if ($jsst_key === 'ticketid' || $jsst_key === 'subject') {
                $jsst_on = true;
            }
            if ($jsst_on) {
                $jsst_keys[] = $jsst_key;
            }
        }
        return $jsst_keys;
    }

    /**
     * The columns this person sees, in catalogue order.
     *
     * @param array $jsst_options 'wpuid' to ask about somebody else.
     * @return array key => column descriptor.
     */
    public static function visibleColumns($jsst_options = array()) {
        $jsst_wpuid = isset($jsst_options['wpuid']) ? (int) $jsst_options['wpuid'] : (int) get_current_user_id();
        $jsst_chosen = ($jsst_wpuid > 0) ? get_user_meta($jsst_wpuid, self::META_COLUMNS, true) : '';
        if (!is_array($jsst_chosen) || empty($jsst_chosen)) {
            $jsst_chosen = self::defaultColumns();
        }
        $jsst_out = array();
        foreach (self::columns() as $jsst_key => $jsst_column) {
            if (in_array($jsst_key, $jsst_chosen, true)) {
                $jsst_out[$jsst_key] = $jsst_column;
            }
        }
        /* Rendered in catalogue order rather than in the order they were
           chosen. A column order of its own would need storing, would have to
           be reconciled every time a module adds a column, and buys an agent
           very little - what they actually asked for is fewer columns, not
           different ones in a different place. */
        if (empty($jsst_out)) {
            $jsst_out = array_intersect_key(self::columns(), array_flip(self::defaultColumns()));
        }
        return $jsst_out;
    }

    /**
     * Remember which columns one person wants.
     *
     * Anything not in the catalogue is dropped rather than refused: a stale
     * form posting a column a module has since removed should not stop the
     * agent saving the rest of their choice.
     *
     * @return true|string true, or why nothing was saved.
     */
    public static function saveColumns($jsst_wpuid, $jsst_keys) {
        $jsst_wpuid = (int) $jsst_wpuid;
        if ($jsst_wpuid <= 0) {
            return esc_html(__('Sign in before choosing columns.', 'js-support-ticket'));
        }
        $jsst_catalogue = self::columns();
        $jsst_clean = array();
        foreach ((array) $jsst_keys as $jsst_key) {
            $jsst_key = sanitize_key((string) $jsst_key);
            if (isset($jsst_catalogue[$jsst_key]) && !in_array($jsst_key, $jsst_clean, true)) {
                $jsst_clean[] = $jsst_key;
            }
        }
        /* The two that cannot be turned off, added back rather than refused.
           An agent who unticks everything has said "I want fewer columns", and
           answering that with an error message is a worse reading of it than
           giving them the two that make a row usable. */
        foreach (array('ticketid', 'subject') as $jsst_required) {
            if (isset($jsst_catalogue[$jsst_required]) && !in_array($jsst_required, $jsst_clean, true)) {
                $jsst_clean[] = $jsst_required;
            }
        }
        if (count($jsst_clean) > self::maxColumns()) {
            return sprintf(
                /* translators: %d: how many columns a queue will show */
                esc_html(__('A queue shows at most %d columns. Turn some off before adding more.', 'js-support-ticket')),
                self::maxColumns()
            );
        }
        update_user_meta($jsst_wpuid, self::META_COLUMNS, $jsst_clean);
        return true;
    }

    /** Forget one person's choice, so the site default applies again. */
    public static function resetColumns($jsst_wpuid) {
        $jsst_wpuid = (int) $jsst_wpuid;
        if ($jsst_wpuid <= 0) {
            return false;
        }
        delete_user_meta($jsst_wpuid, self::META_COLUMNS);
        return true;
    }

    /* =====================================================================
     * Filters
     * ================================================================== */

    /**
     * The filters a queue offers, described once.
     *
     * 'query' is the argument name JSSTticketquery::queues() understands, which
     * is not always the name the form has historically used - the admin queue
     * posts 'priority' and the query layer takes 'priorityid'. Mapping the two
     * here is what lets a filter be renamed on screen without a search-and-
     * replace through two workspaces.
     *
     * 'options' names a set of choices resolved by filterOptions() only when
     * something asks for it. A queue that eagerly loaded departments,
     * priorities, statuses, products, topics, agents and tags to draw a filter
     * bar would run seven queries before showing a single ticket.
     */
    public static function filters() {
        $jsst_filters = array(
            'keywords' => array(
                'label' => __('Search', 'js-support-ticket'), 'type' => 'search', 'query' => 'search',
                'placeholder' => __('Subject, message, replies, customer, e-mail or reference', 'js-support-ticket'),
            ),
            'departmentid' => array('label' => __('Department', 'js-support-ticket'), 'type' => 'select', 'query' => 'departmentid', 'options' => 'department'),
            'priority'     => array('label' => __('Priority', 'js-support-ticket'),   'type' => 'select', 'query' => 'priorityid',   'options' => 'priority'),
            'status'       => array('label' => __('Status', 'js-support-ticket'),     'type' => 'select', 'query' => 'status',       'options' => 'status'),
            'staffid'      => array('label' => __('Agent', 'js-support-ticket'),      'type' => 'select', 'query' => 'staffid',      'options' => 'agent', 'addon' => 'agent'),
            'helptopicid'  => array('label' => __('Topic', 'js-support-ticket'),      'type' => 'select', 'query' => 'helptopicid',  'options' => 'helptopic'),
            'productid'    => array('label' => __('Product', 'js-support-ticket'),    'type' => 'select', 'query' => 'productid',    'options' => 'product'),
            'tagid'        => array('label' => __('Tag', 'js-support-ticket'),        'type' => 'select', 'query' => 'tagid',        'options' => 'tag'),
            'datestart'    => array('label' => __('Raised from', 'js-support-ticket'),'type' => 'date',   'query' => 'datestart'),
            'dateend'      => array('label' => __('Raised until', 'js-support-ticket'),'type' => 'date',  'query' => 'dateend'),
            'ticketid'     => array('label' => __('Reference', 'js-support-ticket'),  'type' => 'text',   'query' => ''),
            'subject'      => array('label' => __('Subject', 'js-support-ticket'),    'type' => 'text',   'query' => ''),
            'name'         => array('label' => __('Customer', 'js-support-ticket'),   'type' => 'text',   'query' => ''),
            'email'        => array('label' => __('E-mail', 'js-support-ticket'),     'type' => 'text',   'query' => ''),
        );
        foreach ($jsst_filters as $jsst_key => $jsst_filter) {
            $jsst_filters[$jsst_key]['key'] = $jsst_key;
        }
        $jsst_filters = apply_filters('jsst_queue_filters', $jsst_filters);
        foreach ($jsst_filters as $jsst_key => $jsst_filter) {
            if (!empty($jsst_filter['addon']) && !in_array($jsst_filter['addon'], jssupportticket::$_active_addons)) {
                unset($jsst_filters[$jsst_key]);
            }
        }
        return $jsst_filters;
    }

    /**
     * The choices behind one filter, asked for only when it is drawn.
     *
     * Each comes from the module that owns the thing being filtered on. None of
     * them is cached here: they are small, they are already the cheapest
     * queries in the plugin, and a stale department list is the kind of bug
     * that takes an afternoon to believe.
     */
    public static function filterOptions($jsst_key) {
        switch ($jsst_key) {
            case 'department':
                return JSSTincluder::getJSModel('department')->getDepartmentForCombobox();
            case 'priority':
                return JSSTincluder::getJSModel('priority')->getPriorityForCombobox();
            case 'status':
                return JSSTincluder::getJSModel('status')->getStatusForFilter();
            case 'helptopic':
                return JSSTincluder::getJSModel('helptopic')->getHelpTopicsForCombobox();
            case 'product':
                return JSSTincluder::getJSModel('product')->getProductForCombobox();
            case 'tag':
                return JSSTincluder::getJSModel('tag')->getTagsForCombobox();
            case 'agent':
                return in_array('agent', jssupportticket::$_active_addons)
                    ? JSSTincluder::getJSModel('agent')->getStaffForCombobox() : array();
        }
        return apply_filters('jsst_queue_filter_options', array(), $jsst_key);
    }

    /**
     * The filter values on this request, keyed as the query layer wants them.
     *
     * Only filters the catalogue knows about, only ones with a value, and every
     * one cast or trimmed on the way through. A filter set assembled from a
     * request and handed straight to a query builder is the injection point for
     * both workspaces at once, which is why the query layer prepares everything
     * again on the other side.
     */
    public static function filtersFromRequest($jsst_source = 'request') {
        $jsst_out = array();
        foreach (self::filters() as $jsst_key => $jsst_filter) {
            if ($jsst_filter['query'] === '') {
                continue;
            }
            $jsst_value = JSSTrequest::getVar($jsst_key, ($jsst_source === 'post') ? 'post' : null, '');
            if (is_array($jsst_value)) {
                continue;
            }
            $jsst_value = trim(wp_strip_all_tags((string) $jsst_value));
            if ($jsst_value === '') {
                continue;
            }
            $jsst_out[$jsst_filter['query']] = $jsst_value;
        }
        return $jsst_out;
    }

    /* =====================================================================
     * Fast counts
     * ================================================================== */

    /**
     * The number on every tab, for this actor, without recounting the table.
     *
     * The count itself is one pass over the tickets - JSSTticketquery::counts()
     * does that already, and it is not the problem. The problem is that a queue
     * screen runs it on every page load, every tab click and every page of
     * results, and on a ten thousand ticket table that is a scan an agent waits
     * for each time. Cached against a version stamp that every ticket event
     * bumps, the scan happens once after something changes and not again.
     *
     * The key carries the scope rather than the person. Two agents in the same
     * departments with the same permissions are looking at the same numbers,
     * and giving them a cache entry each would multiply the work by the size of
     * the team for no difference in the answer.
     */
    public static function counts($jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_key = self::countsKey($jsst_actor, $jsst_options);
        if (isset(self::$jsst_counts[$jsst_key])) {
            return self::$jsst_counts[$jsst_key];
        }
        if (empty($jsst_options['fresh'])) {
            $jsst_cached = get_transient($jsst_key);
            if (is_array($jsst_cached)) {
                self::$jsst_counts[$jsst_key] = $jsst_cached;
                return $jsst_cached;
            }
        }
        $jsst_counts = JSSTticketquery::counts(array_merge(
            self::countFilters($jsst_options),
            array('actor' => $jsst_actor)
        ));
        if (!is_array($jsst_counts)) {
            $jsst_counts = array();
        }
        set_transient($jsst_key, $jsst_counts, self::COUNTS_TTL);
        self::$jsst_counts[$jsst_key] = $jsst_counts;
        return $jsst_counts;
    }

    /**
     * The cache key: what is being counted, for whom, at which version.
     *
     * A transient name is capped at 172 characters, and a scope signature with
     * a long department list plus a filter set would run past that and be
     * silently truncated - two different queues quietly sharing one cache
     * entry. Hashing makes the length fixed and the collision risk irrelevant.
     */
    private static function countsKey($jsst_actor, $jsst_options) {
        $jsst_departments = isset($jsst_actor['departments']) ? (array) $jsst_actor['departments'] : array();
        sort($jsst_departments);
        $jsst_signature = wp_json_encode(array(
            'v'     => (int) get_option(self::OPT_COUNTS_VERSION, 0),
            'kind'  => $jsst_actor['kind'],
            'scope' => $jsst_actor['scope'],
            'all'   => !empty($jsst_actor['all_tickets']),
            'staff' => (int) $jsst_actor['staffid'],
            'uid'   => (int) $jsst_actor['uid'],
            'dept'  => $jsst_departments,
            'f'     => self::countFilters($jsst_options),
        ));
        return 'jsst_qcount_' . md5((string) $jsst_signature);
    }

    /**
     * Which filters the tab numbers are counted under.
     *
     * A tab count has to answer "how many are in this tab of what I am looking
     * at", not "how many are in this tab of everything" - otherwise filtering
     * the queue to one department leaves the tabs claiming numbers the list
     * cannot show. The tab itself is excluded, because each tab counts its own.
     */
    private static function countFilters($jsst_options) {
        $jsst_filters = isset($jsst_options['filters']) && is_array($jsst_options['filters'])
            ? $jsst_options['filters'] : array();
        unset($jsst_filters['list'], $jsst_filters['limit'], $jsst_filters['offset'],
            $jsst_filters['orderby'], $jsst_filters['order'], $jsst_filters['actor']);
        ksort($jsst_filters);
        return $jsst_filters;
    }

    /* =====================================================================
     * The whole queue, in one call
     * ================================================================== */

    /**
     * Everything a queue screen needs to draw itself.
     *
     * One call rather than a dozen, and the same call in both workspaces. A
     * shell that assembles this itself is making a dozen decisions the other
     * shell will make slightly differently, which is the whole reason the two
     * queues in this product diverged.
     *
     * @param array $jsst_options
     *   actor    array  Ask on somebody else's behalf.
     *   filters  array  Query-layer filter values. Defaults to the request.
     *   list     int    Which tab.
     *   scope    string A key from JSSTworkspace::scopes().
     *   orderby  string
     *   order    string
     *   limit    int
     *   offset   int
     */
    public static function state($jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_filters = isset($jsst_options['filters']) && is_array($jsst_options['filters'])
            ? $jsst_options['filters'] : self::filtersFromRequest();

        /* The scope is applied over the filters, not beside them. "Unassigned"
           is a staff filter and "assigned to me" is the same filter with a
           different value, so a scope that did not overwrite an agent filter
           already in the request would produce a queue claiming to be both. */
        $jsst_scopes = class_exists('JSSTworkspace')
            ? JSSTworkspace::scopes(array('actor' => $jsst_actor)) : array();
        $jsst_scope = isset($jsst_options['scope']) ? (string) $jsst_options['scope'] : '';
        if ($jsst_scope !== '' && isset($jsst_scopes[$jsst_scope])) {
            $jsst_filters = array_merge($jsst_filters, $jsst_scopes[$jsst_scope]['filters']);
        } else {
            $jsst_scope = '';
        }

        $jsst_list = isset($jsst_options['list']) ? (int) $jsst_options['list'] : JSSTqueue::LIST_ALL;
        $jsst_list = JSSTqueue::normalizeList($jsst_list);
        $jsst_counts = self::counts(array('actor' => $jsst_actor, 'filters' => $jsst_filters));

        $jsst_tabs = array();
        foreach (JSSTqueue::tabs() as $jsst_tabid => $jsst_tab) {
            if (!empty($jsst_tab['addon']) && !in_array($jsst_tab['addon'], jssupportticket::$_active_addons)) {
                continue;
            }
            $jsst_tab['list'] = (int) $jsst_tabid;
            $jsst_tab['total'] = (isset($jsst_tab['count']) && isset($jsst_counts[$jsst_tab['count']]))
                ? (int) $jsst_counts[$jsst_tab['count']] : 0;
            $jsst_tab['current'] = ((int) $jsst_tabid === $jsst_list);
            $jsst_tabs[(int) $jsst_tabid] = $jsst_tab;
        }

        /* Filters carry their current value with them so a shell renders a
           control and its state together. A form that draws its filters from
           one place and reads their values from another is how a queue shows
           an empty search box over a filtered list. */
        $jsst_catalogue = self::filters();
        foreach ($jsst_catalogue as $jsst_key => $jsst_filter) {
            $jsst_query = $jsst_filter['query'];
            $jsst_catalogue[$jsst_key]['value'] = ($jsst_query !== '' && isset($jsst_filters[$jsst_query]))
                ? $jsst_filters[$jsst_query] : '';
        }

        return array(
            'actor'    => array('kind' => $jsst_actor['kind'], 'scope' => $jsst_actor['scope'],
                                'staffid' => (int) $jsst_actor['staffid']),
            'list'     => $jsst_list,
            'tabs'     => $jsst_tabs,
            'scope'    => $jsst_scope,
            'scopes'   => $jsst_scopes,
            'filters'  => $jsst_catalogue,
            'values'   => $jsst_filters,
            'columns'  => self::visibleColumns(array('wpuid' => (int) $jsst_actor['wpuid'])),
            'catalogue'=> self::columns(),
            'views'    => class_exists('JSSTqueue') ? JSSTqueue::getViews() : array(),
            'bulk'     => class_exists('JSSTworkspace') ? JSSTworkspace::bulkActions(array('actor' => $jsst_actor)) : array(),
            'sort'     => array(
                'orderby' => isset($jsst_options['orderby']) ? (string) $jsst_options['orderby'] : 'updated',
                'order'   => (isset($jsst_options['order']) && strtoupper((string) $jsst_options['order']) === 'ASC') ? 'ASC' : 'DESC',
            ),
        );
    }

    /**
     * The page of tickets a state describes.
     *
     * Kept apart from state() so that a screen showing counts, tabs and no
     * tickets - a workspace home, a widget - does not run the list query, and
     * so that paging through results does not rebuild the filter bar.
     */
    public static function results($jsst_state, $jsst_options = array()) {
        $jsst_args = array_merge(
            isset($jsst_state['values']) && is_array($jsst_state['values']) ? $jsst_state['values'] : array(),
            array(
                'list'    => isset($jsst_state['list']) ? (int) $jsst_state['list'] : JSSTqueue::LIST_ALL,
                'orderby' => isset($jsst_state['sort']['orderby']) ? $jsst_state['sort']['orderby'] : 'updated',
                'order'   => isset($jsst_state['sort']['order']) ? $jsst_state['sort']['order'] : 'DESC',
                'limit'   => isset($jsst_options['limit']) ? (int) $jsst_options['limit'] : 25,
                'offset'  => isset($jsst_options['offset']) ? (int) $jsst_options['offset'] : 0,
            )
        );
        if (isset($jsst_options['actor'])) {
            $jsst_args['actor'] = $jsst_options['actor'];
        }
        return JSSTticketquery::queues($jsst_args);
    }

    /* =====================================================================
     * Shared pieces
     * ================================================================== */

    /** Whoever the caller says is looking, or whoever is on the request. */
    private static function actor($jsst_options) {
        if (isset($jsst_options['actor']) && is_array($jsst_options['actor'])) {
            return $jsst_options['actor'];
        }
        if (isset($jsst_options['wpuid'])) {
            return JSSTcapability::actor((int) $jsst_options['wpuid']);
        }
        return JSSTcapability::actor();
    }
}
