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
if (class_exists('JSSTcoredashboardwidgets')) {
    return;
}


/**
 * The two WordPress dashboard widgets. (Roadmap 4.0-CORE-09)
 *
 * Ported from the Dashboard Widgets add-on, which was nothing but these two
 * `wp_add_dashboard_widget` calls over model methods that already lived in core.
 * Registered only when core owns the feature, so a site that still has the add-on
 * active does not get each widget twice. (Roadmap 4.0-CORE-19)
 *
 * Two corrections came with the port:
 *   - the add-on gated on current_user_can('administrator'), which is a role name
 *     and not a capability. It happens to work on a default site and fails on any
 *     site with a custom administrator role, so it is a capability check now.
 *   - the ticket subject and customer name were interpolated into an HTML string
 *     that was then passed through wp_kses with a tag list allowing no anchors
 *     with hrefs of this shape, so the links were being stripped. The markup is
 *     printed directly with per-value escaping instead.
 */
class JSSTcoredashboardwidgets {

    /**
     * Hook the widgets up. Called once from the plugin bootstrap.
     */
    public static function register() {
        add_action('wp_dashboard_setup', array(__CLASS__, 'addWidgets'));
        add_action('wp_ajax_jsst_set_dashboard_range', array(__CLASS__, 'saveRange'));
    }

    /**
     * Who may see help-desk numbers on the WordPress dashboard.
     */
    private static function canView() {
        return current_user_can('manage_options') || current_user_can('jsst_support_ticket');
    }

    public static function addWidgets() {
        if (!self::canView()) {
            return;
        }
        $jsst_title = isset(jssupportticket::$_config['title']) ? jssupportticket::$_config['title'] : esc_html(__('JS Help Desk', 'js-support-ticket'));
        wp_add_dashboard_widget(
            'jssupportticket_dashboard_widget',
            $jsst_title,
            array(__CLASS__, 'renderLatestTickets')
        );
        wp_add_dashboard_widget(
            'jssupportticket_totalstats_dashboard_widget',
            esc_html(__('Ticket Stats', 'js-support-ticket')),
            array(__CLASS__, 'renderStats')
        );
    }

    /**
     * The most recent tickets, with a link into each one.
     */
    public static function renderLatestTickets() {
        if (!self::canView()) {
            return;
        }
        $jsst_tickets = JSSTincluder::getJSModel('ticket')->getLatestTicketForDashboard();
        if (empty($jsst_tickets)) {
            JSSTlayout::getNoRecordFound();
            return;
        }
        ?>
        <div class="js-row js-nullmargin">
            <span class="js-admin-title color-black"><?php echo esc_html(__('Latest Tickets', 'js-support-ticket')); ?></span>
            <div class="js-ticket-admin-cp-tickets js-nullpadding">
                <div class="js-row js-ticket-admin-cp-head color-blue js-ticket-admin-hide-head">
                    <div class="js-col-xs-12 js-col-md-7"><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></div>
                    <div class="js-col-xs-12 js-col-md-3"><?php echo esc_html(__('From', 'js-support-ticket')); ?></div>
                    <div class="js-col-xs-12 js-col-md-2"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></div>
                </div>
                <?php foreach ($jsst_tickets AS $jsst_ticket) { ?>
                    <div class="js-ticket-admin-cp-data">
                        <div class="js-col-xs-12 js-col-md-7 js-admin-cp-text-elipses">
                            <span class="js-ticket-admin-cp-showhide"><?php echo esc_html(__('Subject', 'js-support-ticket')); ?> : </span>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_ticket->id)); ?>"><?php echo esc_html($jsst_ticket->subject); ?></a>
                        </div>
                        <div class="js-col-xs-12 js-col-md-3">
                            <span class="js-ticket-admin-cp-showhide"><?php echo esc_html(__('From', 'js-support-ticket')); ?> : </span>
                            <?php echo esc_html($jsst_ticket->name); ?>
                        </div>
                        <div class="js-col-xs-12 js-col-md-2" style="background:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>;color:#ffffff;">
                            <span class="js-ticket-admin-cp-showhide"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?> : </span>
                            <?php echo esc_html($jsst_ticket->priority); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    /**
     * The four operational counts, each linking into the queue.
     */
    public static function renderStats() {
        if (!self::canView()) {
            return;
        }
        $jsst_stats = JSSTincluder::getJSModel('ticket')->getTotalStatsForDashboard();
        if (empty($jsst_stats)) {
            JSSTlayout::getNoRecordFound();
            return;
        }
        $jsst_cards = array(
            'open'     => array('label' => esc_html(__('New', 'js-support-ticket')),      'image' => 'new.png'),
            'answered' => array('label' => esc_html(__('Answered', 'js-support-ticket')), 'image' => 'answered.png'),
            'pending'  => array('label' => esc_html(__('Pending', 'js-support-ticket')),  'image' => 'pending.png'),
            'overdue'  => array('label' => esc_html(__('Overdue', 'js-support-ticket')),  'image' => 'overdue.png'),
        );
        $jsst_url = admin_url('admin.php?page=ticket&jstlay=tickets');
        ?>
        <div id="js-total-count-cp-dashbordapi">
            <?php foreach ($jsst_cards AS $jsst_key => $jsst_card) {
                // A count that is missing is a count of nothing. Skipping the card
                // instead left the 2x2 grid with a hole in it. (Roadmap 4.0-CORE-09)
                $jsst_count = isset($jsst_stats[$jsst_key]) ? (int) $jsst_stats[$jsst_key] : 0; ?>
                <a class="js-total-count-dashbordapi" href="<?php echo esc_url($jsst_url); ?>">
                    <img class="img" alt="" src="<?php echo esc_url(JSST_PLUGIN_URL . 'includes/images/admincp/' . $jsst_card['image']); ?>" />
                    <div class="data-dashbordapi">
                        <span class="jstotal-dashbordapi"><?php echo esc_html($jsst_count); ?></span>
                        <span class="jsstatus-dashbordapi"><?php echo esc_html($jsst_card['label']); ?></span>
                    </div>
                </a>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Save the 7/30-day report range for the current administrator.
     * (Roadmap 4.0-CORE-09)
     */
    public static function saveRange() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error();
        }
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jssupportticket_admin_nonce')) {
            wp_send_json_error();
        }
        // getVar() takes the method second and the default third. Passing them
        // the other way round read no value at all and fell back to the string
        // 'post', so every save landed on 7 and the 30-day button did nothing
        // but reload the page.
        $jsst_days = (int) JSSTrequest::getVar('days', 'post', 7);
        $jsst_days = ($jsst_days === 30) ? 30 : 7;
        update_user_meta(get_current_user_id(), 'jsst_dashboard_range', $jsst_days);
        wp_send_json_success(array('days' => $jsst_days));
    }

}
