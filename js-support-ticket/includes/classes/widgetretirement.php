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
/* Named JSSTwidgetretirement and not JSSTwidgets: the add-on this retires
 * already declares JSSTWidgets, and PHP class names are case-insensitive - so
 * the shorter name resolved to the add-on's class, class_exists() answered
 * true for the wrong thing, and calling a method on it was a fatal error on
 * the screen. The two live in different plugins and neither can see the
 * other's naming, so the core one takes the longer name. */
if (class_exists('JSSTwidgetretirement')) {
    return;
}

/**
 * Retiring the Widgets add-on. (Roadmap 4.0-UX-07)
 *
 * The Front-End Widgets add-on puts two things in a sidebar: a list of the
 * signed-in customer's open tickets, and a count of unread internal mail. Both
 * are from the era when a sidebar was how you put anything anywhere, and both
 * are now better served by something else - the first by a block, which can go
 * in a sidebar or a page or anywhere else a block goes, and the second by a
 * feature that is itself being retired.
 *
 * This does not migrate anything, and saying why matters. A widget's placement
 * is a decision about somebody's theme: which sidebar, in what order, next to
 * what. Moving it automatically would mean this plugin rearranging a layout it
 * cannot see, and the failure mode is a support page that silently loses its
 * sidebar. So the honest deliverable is a report - what you have, where it is,
 * and what to put in its place - and the placing stays with the person who can
 * see the page.
 *
 * As everywhere else in this programme: nothing is deleted, the add-on is not
 * switched off, and a site that ignores this screen keeps working exactly as it
 * does today.
 */
class JSSTwidgetretirement {

    /** The widget classes the add-on registers. */
    public static function known() {
        return array(
            'JSSTmyticket_addon_widget' => array(
                'label'       => __('My Ticket Widget', 'js-support-ticket'),
                'does'        => __('The signed-in customer\'s open tickets.', 'js-support-ticket'),
                'replacement' => __('The "My tickets" block, which shows the same list and can go in a sidebar, a page, or anywhere else a block goes.', 'js-support-ticket'),
                'block'       => 'js-support-ticket/ticket-list',
                'shortcode'   => 'jssupportticket_mytickets',
            ),
            'JSSTmailnotification_addon_widget' => array(
                'label'       => __('Mail Notification Widget', 'js-support-ticket'),
                'does'        => __('A count of unread internal mail.', 'js-support-ticket'),
                /* No block for this one, deliberately. Internal Mail is being
                   retired in its own right, and what replaces it - the
                   notification centre - is a place an agent goes rather than a
                   number in a customer-facing sidebar. Offering a block would
                   be carrying a dated idea forward in a new wrapper. */
                'replacement' => __('Nothing, on purpose. Internal Mail is being retired, and what replaces it is the notification centre on the agent desk — a place an agent visits, rather than a number in a sidebar a customer can see.', 'js-support-ticket'),
                'block'       => '',
                'shortcode'   => '',
            ),
        );
    }

    /** Is the add-on on this site at all? */
    public static function available() {
        return in_array('widgets', jssupportticket::$_active_addons);
    }

    /**
     * What exists, and where it has been put.
     *
     * Placement comes from the sidebars_widgets option, which is the record of
     * which sidebar holds what - so this reports a widget sitting in a real
     * sidebar differently from one that is only configured, which is the
     * difference between something a visitor sees and something nobody does.
     */
    public static function survey() {
        $jsst_out = array('available' => self::available(), 'widgets' => array(), 'placed' => 0, 'configured' => 0);
        if (!$jsst_out['available']) {
            return $jsst_out;
        }
        $jsst_sidebars = get_option('sidebars_widgets', array());
        $jsst_sidebars = is_array($jsst_sidebars) ? $jsst_sidebars : array();

        foreach (self::known() as $jsst_class => $jsst_meta) {
            /* WordPress stores a widget's settings under the lower-cased class
               name, and its placement as "<id_base>-<number>" in whichever
               sidebar array holds it. */
            $jsst_base = strtolower($jsst_class);
            $jsst_settings = get_option('widget_' . $jsst_base, array());
            $jsst_instances = array();
            if (is_array($jsst_settings)) {
                foreach ($jsst_settings as $jsst_number => $jsst_instance) {
                    if (!is_numeric($jsst_number) || !is_array($jsst_instance)) {
                        continue;
                    }
                    $jsst_id = $jsst_base . '-' . $jsst_number;
                    $jsst_where = '';
                    foreach ($jsst_sidebars as $jsst_sidebar => $jsst_ids) {
                        if (is_array($jsst_ids) && in_array($jsst_id, $jsst_ids, true)) {
                            $jsst_where = $jsst_sidebar;
                            break;
                        }
                    }
                    $jsst_instances[] = array(
                        'id'       => $jsst_id,
                        'title'    => isset($jsst_instance['title']) ? $jsst_instance['title'] : '',
                        'rows'     => isset($jsst_instance['maxrecord']) ? (int) $jsst_instance['maxrecord'] : 0,
                        'sidebar'  => $jsst_where,
                        'live'     => ($jsst_where !== '' && $jsst_where !== 'wp_inactive_widgets'),
                    );
                    if ($jsst_where !== '' && $jsst_where !== 'wp_inactive_widgets') {
                        $jsst_out['placed']++;
                    } else {
                        $jsst_out['configured']++;
                    }
                }
            }
            $jsst_out['widgets'][$jsst_class] = array_merge($jsst_meta, array('instances' => $jsst_instances));
        }
        return $jsst_out;
    }

    /**
     * The row under the add-on on the Plugins screen. (Roadmap 4.0-UX-07)
     *
     * Every other legacy add-on grew one of these - JSSTbundle for the ones a
     * bundle replaced, JSSTmergedaddon for the ones the free core absorbed -
     * and this add-on belongs to neither, so it was the only one left with
     * nothing next to its Deactivate link. The report on the Shortcodes screen
     * is the right place for the detail, but it is not a place anybody looks
     * while they are deciding what to switch off.
     *
     * What it says depends on what the survey finds, because the two cases are
     * genuinely different. A widget sitting in a live sidebar is on somebody's
     * page right now, and deactivating removes it - that is a warning, and the
     * one piece of advice this class refuses to give automatically is where to
     * put the replacement. With nothing placed, there is no layout to lose and
     * it is ordinary information.
     */
    public static function pluginRow($jsst_file, $jsst_data) {
        $jsst_survey = self::survey();
        $jsst_live   = isset($jsst_survey['placed']) ? (int) $jsst_survey['placed'] : 0;

        if ($jsst_live > 0) {
            $jsst_class = 'notice-warning';
            $jsst_text  = sprintf(
                /* translators: %d: how many widgets are in a live sidebar. */
                _n(
                    'Being retired, and %d of its widgets is in a live sidebar right now — deactivating this removes it from that sidebar.',
                    'Being retired, and %d of its widgets are in live sidebars right now — deactivating this removes them from those sidebars.',
                    $jsst_live,
                    'js-support-ticket'
                ),
                $jsst_live
            );
            $jsst_text .= ' ' . __('The "My tickets" block does the same job anywhere a block goes. Nothing is moved for you, because where it goes is a decision about your theme.', 'js-support-ticket');
        } else {
            $jsst_class = 'notice-info';
            $jsst_text  = __('Being retired. Its widgets are not in any live sidebar, so deactivating this changes nothing a visitor sees. The "My tickets" block does the same job anywhere a block goes.', 'js-support-ticket');
        }

        $jsst_link = admin_url('admin.php?page=jssupportticket&jstlay=shortcodes');
        echo '<tr class="plugin-update-tr active"><td colspan="4" class="plugin-update colspanchange">'
            . '<div class="update-message notice inline ' . esc_attr($jsst_class) . ' notice-alt"><p>'
            . esc_html($jsst_text) . ' '
            . '<a href="' . esc_url($jsst_link) . '">'
            . esc_html(__('See what you have and where it is.', 'js-support-ticket'))
            . '</a></p></div></td></tr>';
    }

    /**
     * Put the row under the add-on, when the add-on is here.
     *
     * Called from the bootstrap beside the include. Guarded on available() so a
     * site without the add-on registers nothing at all.
     */
    public static function register() {
        if (!self::available()) {
            return;
        }
        $jsst_file = 'js-support-ticket-widgets/js-support-ticket-widgets.php';
        if (class_exists('JSSTlegacy') && method_exists('JSSTlegacy', 'pluginFile')) {
            $jsst_from_legacy = JSSTlegacy::pluginFile('widgets');
            if (!empty($jsst_from_legacy)) {
                $jsst_file = $jsst_from_legacy;
            }
        }
        add_action('after_plugin_row_' . $jsst_file, array(__CLASS__, 'pluginRow'), 10, 2);
    }

    /** The name a sidebar goes by on screen. */
    public static function sidebarName($jsst_id) {
        global $wp_registered_sidebars;
        if ($jsst_id === '' ) {
            return esc_html(__('not placed anywhere', 'js-support-ticket'));
        }
        if ($jsst_id === 'wp_inactive_widgets') {
            return esc_html(__('in the inactive widgets area, so nobody sees it', 'js-support-ticket'));
        }
        if (is_array($wp_registered_sidebars) && isset($wp_registered_sidebars[$jsst_id]['name'])) {
            return $wp_registered_sidebars[$jsst_id]['name'];
        }
        return $jsst_id;
    }
}
