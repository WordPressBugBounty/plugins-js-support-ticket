<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('ticket-apexcharts', JSST_PLUGIN_URL . 'includes/js/apexcharts.min.js');
wp_enqueue_script('ticket-notify-app', JSST_PLUGIN_URL . 'includes/js/firebase-app.js');
wp_enqueue_script('ticket-notify-message', JSST_PLUGIN_URL . 'includes/js/firebase-messaging.js');

wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css');
do_action('ticket-notify-generate-token');
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="js-main-cp-wrapper">
            <div id="jsstadmin-wrapper-top">
                <div id="jsstadmin-wrapper-top-left">
                    <div id="jsstadmin-breadcrunbs">
                        <ul>
                            <li><a href="?page=jssupportticket"
                                    title="<?php echo esc_html(__('Dashboard', 'js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard', 'js-support-ticket')); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="jsstadmin-wrapper-top-right">
                    <div id="jsstadmin-config-btn">
                        <a href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>"
                            title="<?php echo esc_html(__('Configuration', 'js-support-ticket')); ?>">
                            <img alt="<?php echo esc_html(__('Configuration', 'js-support-ticket')); ?>"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                        </a>
                    </div>
                    <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                        <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>"
                            title="<?php echo esc_html(__('Help', 'js-support-ticket')); ?>">
                            <img alt="<?php echo esc_html(__('Help', 'js-support-ticket')); ?>"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                        </a>
                    </div>
                    <div id="jsstadmin-vers-txt">
                        <?php echo esc_html(__("Version", 'js-support-ticket')); ?>:
                        <span
                            class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                    </div>
                </div>
            </div>
            <div id="jsstadmin-head">
                <h1 class="jsstadmin-head-text">
                    <?php echo esc_html(__('Dashboard', 'js-support-ticket')); ?>
                </h1>
                <button id="js-customize-dashboard-btn" class="js-customize-dashboard-btn" title="<?php echo esc_attr(__('Dashboard Options', 'js-support-ticket')); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                      <!-- Eye outline -->
                      <path stroke-linecap="round" stroke-linejoin="round" d="M1 12C2.73 7.61 7 4.5 12 4.5s9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12z"/>
                      
                      <!-- Pupil -->
                      <circle cx="12" cy="12" r="2"/>
                    </svg>
                    <?php echo esc_html(__('Dashboard Options', 'js-support-ticket')); ?>
                </button>
                <?php if (in_array('agent', jssupportticket::$_active_addons)) { ?>
                    <a href="?page=agent" class="jsstadmin-add-link orange-bg button"
                        title="<?php echo esc_html(__('Agents', 'js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('Staff', 'js-support-ticket')); ?>"
                            src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/staff-1.png" />
                        <?php echo esc_html(__('Agents', 'js-support-ticket')); ?>
                    </a>
                <?php } ?>
                <a href="?page=ticket" class="jsstadmin-add-link button"
                    title="<?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>">
                    <img alt="<?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>"
                        src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/all-tickets.png" />
                    <?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>
                </a>
            </div>
            <div id="js-dashboard-customize-panel-overlay" class="js-support-ticket-customize-panel-overlay">
                <div id="js-dashboard-customize-panel" class="js-support-ticket-customize-panel">
                    <h3 class="jsstadmin-head-text" style="font-size: 1.25rem; margin-bottom: 1rem">
                        <?php echo esc_html(__('Dashboard Options', 'js-support-ticket')); ?>
                    </h3>
                    <p class="text-gray-600 mb-6"><?php echo esc_html(__('Select which charts and sections you would like to see on your dashboard.', 'js-support-ticket')); ?></p>
                    <form id="js-customize-form">
                        <div class="space-y-4">
                            <?php if( in_array('agent',jssupportticket::$_active_addons) ){ ?>
                                <div class="js-ticket-customize-option-item">
                                    <label for="toggle-unassigned_ticket" class="text-gray-700"><?php echo esc_html(__('Unassigned Ticket', 'js-support-ticket')); ?></label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="toggle-unassigned_ticket" name="unassigned_ticket">
                                        <span class="toggle-switch-slider"></span>
                                    </label>
                                </div>
                            <?php } ?>
                            <?php if( in_array('actions',jssupportticket::$_active_addons) ){ ?>
                                <div class="js-ticket-customize-option-item">
                                    <label for="toggle-ticket_history" class="text-gray-700"><?php echo esc_html(__('Ticket Action History', 'js-support-ticket')); ?></label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="toggle-ticket_history" name="ticket_history">
                                        <span class="toggle-switch-slider"></span>
                                    </label>
                                </div>
                            <?php } ?>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-ticket_trends" class="text-gray-700"><?php echo esc_html(__('Ticket Trends', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-ticket_trends" name="ticket_trends">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-today_distribution" class="text-gray-700"><?php echo esc_html(__("Today's Distribution", 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-today_distribution" name="today_distribution">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-latest_tickets" class="text-gray-700"><?php echo esc_html(__('Latest Tickets', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-latest_tickets" name="latest_tickets">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <?php if( in_array('agent',jssupportticket::$_active_addons) ){ ?>
                                <div class="js-ticket-customize-option-item">
                                    <label for="toggle-agent_workload" class="text-gray-700"><?php echo esc_html(__('Agent Workload', 'js-support-ticket')); ?></label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="toggle-agent_workload" name="agent_workload">
                                        <span class="toggle-switch-slider"></span>
                                    </label>
                                </div>
                            <?php } ?>
                            <?php if( in_array('overdue',jssupportticket::$_active_addons) ){ ?>
                                <div class="js-ticket-customize-option-item">
                                    <label for="toggle-overdue_ticket" class="text-gray-700"><?php echo esc_html(__('Overdue Ticket', 'js-support-ticket')); ?></label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="toggle-overdue_ticket" name="overdue_ticket">
                                        <span class="toggle-switch-slider"></span>
                                    </label>
                                </div>
                            <?php } ?>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-tickets_by_department" class="text-gray-700"><?php echo esc_html(__('Tickets by Department', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-tickets_by_department" name="tickets_by_department">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-tickets_by_status" class="text-gray-700"><?php echo esc_html(__('Tickets by Status', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-tickets_by_status" name="tickets_by_status">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-tickets_by_priorities" class="text-gray-700"><?php echo esc_html(__('Tickets by Priorities', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-tickets_by_priorities" name="tickets_by_priorities">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-tickets_by_products" class="text-gray-700"><?php echo esc_html(__('Tickets by Products', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-tickets_by_products" name="tickets_by_products">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <?php if( in_array('cannedresponses',jssupportticket::$_active_addons) ){ ?>
                                <div class="js-ticket-customize-option-item">
                                    <label for="toggle-canned_responses" class="text-gray-700"><?php echo esc_html(__('List of Canned Responses', 'js-support-ticket')); ?></label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="toggle-canned_responses" name="canned_responses">
                                        <span class="toggle-switch-slider"></span>
                                    </label>
                                </div>
                            <?php } ?>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-tickets_by_age" class="text-gray-700"><?php echo esc_html(__('Open Tickets By Age', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-tickets_by_age" name="tickets_by_age">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-active_customers" class="text-gray-700"><?php echo esc_html(__('Most Active Customers', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-active_customers" name="active_customers">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <?php if( in_array('timetracking',jssupportticket::$_active_addons) ){ ?>
                                <div class="js-ticket-customize-option-item">
                                    <label for="toggle-active_timer" class="text-gray-700"><?php echo esc_html(__('Active Timer', 'js-support-ticket')); ?></label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="toggle-active_timer" name="active_timer">
                                        <span class="toggle-switch-slider"></span>
                                    </label>
                                </div>
                            <?php } ?>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-available_addons" class="text-gray-700"><?php echo esc_html(__('Available Addons', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-available_addons" name="available_addons">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                            <div class="js-ticket-customize-option-item">
                                <label for="toggle-installation_guide" class="text-gray-700"><?php echo esc_html(__('Quick installation Guide', 'js-support-ticket')); ?></label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="toggle-installation_guide" name="installation_guide">
                                    <span class="toggle-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end space-x-4">
                            <button type="button" id="js-close-customize-panel" class="js-hlpdsk-cancel-btn"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></button>
                            <button type="button" id="js-save-customize-panel" class="js-hlpdsk-save-btn"><?php echo esc_html(__('Save Changes', 'js-support-ticket')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
                <main class="js-hlpdsk-main-content-wrapper">
                    <section class="js-hlpdsk-section-container">
                        <a href="?page=ticket" data-tab-number="1"
                            class="js-hlpdsk-like-card js-hlpdsk-card js-hlpdsk-metric-card js-hlpdsk-border-blue js-hlpdsk-card-third">
                            <div class="js-hlpdsk-content-left">
                                <span class="js-hlpdsk-value js-hlpdsk-text-blue"><?php echo esc_html(jssupportticket::$_data['new_tickets']); ?></span>
                                <p class="js-hlpdsk-label">
                                    <?php echo esc_html(__('New Tickets', 'js-support-ticket')); ?>
                                </p>
                            </div>
                            <div class="js-hlpdsk-icon-right">
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    class="js-hlpdsk-addon-icon" 
                                     fill="none" viewBox="0 0 24 24" 
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" 
                                        d="M9 2h6a2 2 0 012 2v2a2 2 0 002 2h2v8h-2a2 2 0 00-2 2v2a2 2 0 01-2 2H9a2 2 0 01-2-2v-2a2 2 0 00-2-2H3V8h2a2 2 0 002-2V4a2 2 0 012-2z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8"/>
                                </svg>
                            </div>
                        </a>
                        <a href="?page=ticket" data-tab-number="1" class="js-hlpdsk-like-card js-hlpdsk-card js-hlpdsk-metric-card js-hlpdsk-border-yellow js-hlpdsk-card-third">
                            <div class="js-hlpdsk-content-left">
                                <span class="js-hlpdsk-value js-hlpdsk-text-yellow"><?php echo esc_html(jssupportticket::$_data['pending_tickets']); ?></span>
                                <p class="js-hlpdsk-label">
                                    <?php echo esc_html(__('Pending Tickets', 'js-support-ticket')); ?>
                                </p>
                            </div>
                            <div class="js-hlpdsk-icon-right">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor" stroke="none">
                                    <path
                                        d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm0 18a8 8 0 118-8A8 8 0 0112 20zm0-16a1 1 0 00-1 1v6a1 1 0 002 0V5a1 1 0 00-1-1z" />
                                </svg>
                            </div>
                        </a>
                        <a href="?page=ticket" data-tab-number="2" class="js-hlpdsk-like-card js-hlpdsk-card js-hlpdsk-metric-card js-hlpdsk-border-green js-hlpdsk-card-third">
                            <div class="js-hlpdsk-content-left">
                                <span class="js-hlpdsk-value js-hlpdsk-text-green"><?php echo esc_html(jssupportticket::$_data['answered_tickets']); ?></span>
                                <p class="js-hlpdsk-label">
                                    <?php echo esc_html(__('Answered Tickets', 'js-support-ticket')); ?>
                                </p>
                            </div>
                            <div class="js-hlpdsk-icon-right">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor" stroke="none">
                                    <path
                                        d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.58L19 8.01z" />
                                </svg>
                            </div>
                        </a>
                        <a  href="?page=ticket" data-tab-number="5" class="js-hlpdsk-like-card js-hlpdsk-card js-hlpdsk-metric-card js-hlpdsk-border-purple js-hlpdsk-card-third">
                            <div class="js-hlpdsk-content-left">
                                <span class="js-hlpdsk-value js-hlpdsk-text-purple"><?php echo esc_html(jssupportticket::$_data['total_tickets']); ?></span>
                                <p class="js-hlpdsk-label">
                                    <?php echo esc_html(__('Total Tickets', 'js-support-ticket')); ?>
                                </p>
                            </div>
                            <div class="js-hlpdsk-icon-right">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 2h6m-5 4h4m-7 4h10m-10 4h10m-10 4h10M6 6h12v14H6V6z" />
                                </svg>
                            </div>
                        </a>
                        <a href="?page=ticket" data-tab-number="4" class="js-hlpdsk-like-card js-hlpdsk-card js-hlpdsk-metric-card js-hlpdsk-border-cyan js-hlpdsk-card-third">
                            <div class="js-hlpdsk-content-left">
                                <span class="js-hlpdsk-value js-hlpdsk-text-cyan"><?php echo esc_html(jssupportticket::$_data['tickets_closed_today']); ?></span>
                                <p class="js-hlpdsk-label">
                                    <?php echo esc_html(__('Tickets Closed Today', 'js-support-ticket')); ?>
                                </p>
                            </div>
                            <div class="js-hlpdsk-icon-right">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor" stroke="none">
                                    <path
                                        d="M19 4h-2V2h-2v2H9V2H7v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zM5 20V8h14v12zm10.4-8.8l-4.4 4.4-2.4-2.4 1.4-1.4 1 1 3-3 1.4 1.4z" />
                                </svg>
                            </div>
                        </a>
                        <div class="js-hlpdsk-card js-hlpdsk-daily-flow-card js-hlpdsk-card-third">
                            <h3 class="js-hlpdsk-section-heading"
                                style="font-size: 1.25rem; margin-bottom: 0; margin-top: 0">
                                <?php echo esc_html(__('Daily Ticket Flow', 'js-support-ticket')); ?>
                            </h3>
                            <div class="js-hlpdsk-daily-flow-content">
                                <div>
                                    <p style="color: #6b7280; font-size: 0.875rem; margin: 0">
                                        <?php echo esc_html(__('Created', 'js-support-ticket')); ?>
                                    </p>
                                    <span class="js-hlpdsk-value js-hlpdsk-text-blue"><?php echo esc_html(jssupportticket::$_data['tickets_created_today']); ?></span>
                                </div>
                                <div class="js-hlpdsk-separator">/</div>
                                <div>
                                    <p style="color: #6b7280; font-size: 0.875rem; margin: 0">
                                        <?php echo esc_html(__('Resolved', 'js-support-ticket')); ?>
                                    </p>
                                    <span class="js-hlpdsk-value js-hlpdsk-text-green"><?php echo esc_html(jssupportticket::$_data['tickets_closed_today']); ?></span>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php if (jssupportticket::$_data['update_avaliable_for_addons'] != 0) {?>
                        <section class="js-hlpdsk-section-container js-hlpdsk-addon-updatesection">
                            <div class="js-hlpdeske-message-bar">
                                <div class="js-hlpdeske-message-icon">
                                    <!-- CloudDownload icon from Lucide as SVG -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                        <path d="M4 14.899A7 7 0 0 1 15.602 9.023C15.895 5.258 13.917 2 10 2 6.136 2 3 5.136 3 9c0 0.817.158 1.6.44 2.332L2.5 12.5C1.12 12.5 0 13.62 0 15s1.12 2.5 2.5 2.5H4c0 1.933 1.567 3.5 3.5 3.5h7c1.933 0 3.5-1.567 3.5-3.5 0-1.78-.96-3.32-2.39-4.14z"></path>
                                        <polyline points="7 13 12 18 17 13"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="6"></line>
                                    </svg>
                                </div>
                                <div class="js-hlpdeske-message-content">
                                    <div class="js-hlpdeske-message-text">
                                        <p class="js-hlpdeske-message-title"><?php echo esc_html(__('New Addon Update Available', 'js-support-ticket')); ?></p>
                                        <span class="js-hlpdeske-message-description">
                                            <?php echo esc_html(__('Install the latest version to unlock new features and security patches.', 'js-support-ticket')); ?>
                                        </span>
                                    </div>
                                    <div class="js-hlpdeske-message-actions">
                                        <a href="?page=jssupportticket&jstlay=addonstatus" class="js-hlpdeske-action-button"><?php echo esc_html(__('Update Now', 'js-support-ticket')); ?></a>
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php } ?>
                    <?php if( in_array('agent',jssupportticket::$_active_addons)|| in_array('actions',jssupportticket::$_active_addons) ) { ?>
                        <section class="js-hlpdsk-section-container">
                            <?php if( in_array('agent',jssupportticket::$_active_addons) && !empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['unassigned_ticket']) ){ ?>
                                <div class="js-hlpdsk-card js-hlpdsk-card-half">
                                    <h3 class="js-hlpdsk-section-heading" style="font-size: 1.25rem; margin-bottom: 1rem">
                                        <?php echo esc_html(__('Unassigned Tickets', 'js-support-ticket')); ?>
                                    </h3>
                                    <?php if (!empty(jssupportticket::$_data['unassigned_tickets'])) { ?>
                                        <ul class="js-hlpdsk-list-card">
                                            <?php foreach (jssupportticket::$_data['unassigned_tickets'] as $ticket) { ?>
                                                <li>
                                                    <span class="js-hlpdsk-list-item-title"><?php echo esc_html($ticket->subject); ?></span>
                                                    <span class="js-hlpdsk-list-item-value"><?php echo human_time_diff(jssupportticketphplib::JSST_strtotime($ticket->created), current_time('timestamp')) . ' ago'; ?></span>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } else { ?>
                                        <div class="js-hlpdsk-empty-state">
                                            <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <h4><?php echo esc_html(__('No Unassigned Tickets', 'js-support-ticket')); ?></h4>
                                            <p><?php echo esc_html(__('All tickets have been assigned. Great job!', 'js-support-ticket')); ?>
                                            </p>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php
                            }
                            if( in_array('actions',jssupportticket::$_active_addons) && !empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['ticket_history']) ){ ?>
                                <div class="js-hlpdsk-card js-hlpdsk-ticket-history-card js-hlpdsk-card-half">
                                    <h3 class="js-hlpdsk-section-heading" style="font-size: 1.25rem; margin-bottom: 1rem">
                                        <?php echo esc_html(__('Ticket Action History', 'js-support-ticket')); ?>
                                    </h3>
                                    <?php if (!empty(jssupportticket::$_data['ticket_action_history'])) { ?>
                                        <div class="js-hlpdsk-table-wrapper">
                                            <table class="js-hlpdsk-modern-table">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                                        <th><?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?></th>
                                                        <th><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></th>
                                                        <th><?php echo esc_html(__('Time', 'js-support-ticket')); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (jssupportticket::$_data['ticket_action_history'] as $action) { ?>
                                                        <tr>
                                                            <td><?php echo esc_html($action->message); ?></td>
                                                            <td>
                                                                <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($action->referenceid); ?>" class="js-hlpdsk-text-blue-link">#<?php echo esc_html($action->ticketid); ?></a>
                                                            </td>
                                                            <td><?php echo esc_html($action->agent_name); ?></td>
                                                            <td><?php echo esc_html(date('g:i A', jssupportticketphplib::JSST_strtotime($action->datetime))); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } else { ?>
                                        <div class="js-hlpdsk-empty-state">
                                            <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5e" strok="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <h4><?php echo esc_html(__('No Recent Actions', 'js-support-ticket')); ?></h4>
                                            <p><?php echo esc_html(__('There is no ticket activity to show at the moment.', 'js-support-ticket')); ?>
                                            </p>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </section>
                    <?php } ?>
                    <section class="js-hlpdsk-section-container">
                        <?php if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['ticket_trends'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-chart-card js-hlpdsk-card-70">
                                <h3><?php echo esc_html(__('Ticket Trends (Last 7 Days)', 'js-support-ticket')); ?></h3>
                                <div id="statisticsChart" class="js-hlpdsk-chart-container-inner"></div>
                                <div class="js-hlpdsk-chart-legend js-hlpdsk-statistics-chart">
                                    <span class="js-hlpdsk-legend-item"><span
                                            class="js-hlpdsk-legend-dot js-hlpdsk-dot-cyan"></span><?php echo esc_html(__('Open', 'js-support-ticket')); ?></span>
                                    <span class="js-hlpdsk-legend-item"><span
                                            class="js-hlpdsk-legend-dot js-hlpdsk-dot-purple"></span><?php echo esc_html(__('Pending', 'js-support-ticket')); ?></span>
                                    <span class="js-hlpdsk-legend-item"><span
                                            class="js-hlpdsk-legend-dot js-hlpdsk-dot-yellow"></span><?php echo esc_html(__('Answered', 'js-support-ticket')); ?></span>
                                </div>
                            </div>
                        <?php
                        }
                        if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['today_distribution'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-chart-card js-hlpdsk-card-30">
                                <h3><?php echo esc_html(__('Today\'s Ticket Distribution', 'js-support-ticket')); ?></h3>
                                <div id="todayTicketsChart" class="js-hlpdsk-chart-container-inner"></div>
                                <div class="js-hlpdsk-chart-legend">
                                    <span class="js-hlpdsk-legend-item"><span
                                            class="js-hlpdsk-legend-dot js-hlpdsk-dot-blue"></span><?php echo esc_html(__('New', 'js-support-ticket')); ?></span>
                                    <span class="js-hlpdsk-legend-item"><span
                                            class="js-hlpdsk-legend-dot js-hlpdsk-dot-green"></span><?php echo esc_html(__('Answered', 'js-support-ticket')); ?></span>
                                    <span class="js-hlpdsk-legend-item"><span
                                            class="js-hlpdsk-legend-dot js-hlpdsk-dot-orange"></span><?php echo esc_html(__('Pending', 'js-support-ticket')); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </section>
                    <?php if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['latest_tickets'])) { ?>
                        <section class="js-hlpdsk-section-container">
                            <div class="js-hlpdsk-card js-hlpdsk-card-full latest-tickets-card">
                                <div class="card-header-flex">
                                    <h3><?php echo esc_html(__('Latest Tickets', 'js-support-ticket')); ?></h3>
                                    <a href="?page=ticket"
                                        class="view-all-btn"><?php echo esc_html(__('View All', 'js-support-ticket')); ?></a>
                                </div>
                                <?php if (!empty(jssupportticket::$_data['latest_tickets'])) { ?>
                                    <div class="js-hlpdsk-table-wrapper">
                                        <table class="js-hlpdsk-modern-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 15%">
                                                        <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?>
                                                    </th>
                                                    <th style="width: 30%">
                                                        <?php echo esc_html(__('Subject', 'js-support-ticket')); ?>
                                                    </th>
                                                    <th style="width: 15%">
                                                        <?php echo esc_html(__('Status', 'js-support-ticket')); ?>
                                                    </th>
                                                    <th style="width: 20%">
                                                        <?php echo esc_html(__('Department', 'js-support-ticket')); ?>
                                                    </th>
                                                    <th style="width: 15%">
                                                        <?php echo esc_html(__('Last Reply', 'js-support-ticket')); ?>
                                                    </th>
                                                    <th style="width: 5%">
                                                        <?php echo esc_html(__('Action', 'js-support-ticket')); ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (jssupportticket::$_data['latest_tickets'] as $ticket) { ?>
                                                    <tr>
                                                        <td>#<?php echo esc_html($ticket->ticketid); ?></td>
                                                        <td><?php echo esc_html($ticket->subject); ?></td>
                                                        <td>
                                                            <span class="js-hlpdsk-status-tag" style="background-color:<?php echo esc_attr(jssupportticket::$_data['status_map'][$ticket->status]->statusbgcolour); ?>; color:<?php echo esc_attr(jssupportticket::$_data['status_map'][$ticket->status]->statuscolour); ?>;"><?php echo esc_html(jssupportticket::$_data['status_map'][$ticket->status]->status); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (!empty($ticket->departmentid)) {
                                                                echo esc_html(jssupportticket::$_data['department_map'][$ticket->departmentid]);
                                                            } ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $last_reply_timestamp = jssupportticketphplib::JSST_strtotime($ticket->lastreply);
                                                            if (!empty($last_reply_timestamp) && $last_reply_timestamp > 0) {
                                                                echo human_time_diff($last_reply_timestamp, current_time('timestamp')) . ' ago';
                                                            } else {
                                                                echo esc_html(__('', 'js-support-ticket'));
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($ticket->id); ?>" class="js-hlpdsk-text-blue-link">
                                                                <?php echo esc_html(__('View', 'js-support-ticket')); ?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="js-hlpdsk-empty-state">
                                        <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h12A2.25 2.25 0 0120.25 6v3.776" />
                                        </svg>
                                        <h4><?php echo esc_html(__('No Tickets Found', 'js-support-ticket')); ?></h4>
                                        <p><?php echo esc_html(__('There are currently no tickets to display in this view.', 'js-support-ticket')); ?>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                        </section>
                    <?php } ?>

                    <section class="js-hlpdsk-section-container">
                        <?php if( in_array('agent',jssupportticket::$_active_addons) && !empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['agent_workload']) ){ ?>
                            <div class="js-hlpdsk-card js-hlpdsk-work-load-card js-hlpdsk-card-70">
                                <div class="card-header-flex">
                                    <h3 class="js-hlpdsk-section-heading">
                                        <?php echo esc_html(__('Agent Workload', 'js-support-ticket')); ?>
                                    </h3>
                                    <a href="?page=agent" class="view-all-btn">
                                        <?php echo esc_html(__('View All', 'js-support-ticket')).' '.esc_html(__('Agents', 'js-support-ticket')); ?>
                                    </a>
                                </div>
                                <?php if (!empty(jssupportticket::$_data['agent_workload'])) { ?>
                                    <div class="js-hlpdsk-table-wrapper">
                                        <table class="js-hlpdsk-modern-table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></th>
                                                    <th><?php echo esc_html(__('New', 'js-support-ticket')); ?></th>
                                                    <th><?php echo esc_html(__('Awaiting Customer', 'js-support-ticket')); ?>
                                                    </th>
                                                    <th><?php echo esc_html(__('Awaiting Agent', 'js-support-ticket')); ?></th>
                                                    <th><?php echo esc_html(__('Total', 'js-support-ticket')); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (jssupportticket::$_data['agent_workload'] as $agent) { ?>
                                                    <tr>
                                                        <td><?php echo esc_html($agent->agent_name); ?></td>
                                                        <td><?php echo esc_html($agent->open_tickets); ?></td>
                                                        <td><?php echo esc_html($agent->awaiting_customer); ?></td>
                                                        <td><?php echo esc_html($agent->awaiting_agent); ?></td>
                                                        <td><?php echo esc_html($agent->total_tickets); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="js-hlpdsk-empty-state">
                                        <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                        <h4><?php echo esc_html(__('No Agent Data', 'js-support-ticket')); ?></h4>
                                        <p><?php echo esc_html(__('Workload data for agents is not available at this time.', 'js-support-ticket')); ?>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        if (in_array('overdue', jssupportticket::$_active_addons) && !empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['overdue_ticket'])) {
                            if(jssupportticket::$_data['overdue_tickets_count'] > 0) {?>
                                <a href="?page=ticket" data-tab-number="3" class="js-hlpdsk-like-card js-hlpdsk-card js-hlpdsk-overdue-card js-hlpdsk-card-30">
                                    <div class="js-hlpdsk-icon-right">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.731 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.352zM12 20.25h.008v.008H12v-.008z" />
                                        </svg>
                                    </div>
                                    <div class="js-hlpdsk-content-left">
                                        <span class="js-hlpdsk-label"><?php echo esc_html(__('Total overdue tickets', 'js-support-ticket')); ?></span>
                                        <span class="js-hlpdsk-value"><?php echo esc_html(jssupportticket::$_data['overdue_tickets_count']); ?></span>
                                    </div>
                                </a>
                            <?php } else { ?>
                                <div class="js-hlpdsk-card js-hlpdsk-card-30">
                                    <div class="js-hlpdsk-empty-state">
                                        <svg class="js-hlpdsk-empty-state-icon" style="color: #166534;" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h4 style="color: #166534;">
                                            <?php echo esc_html(__('Zero Overdue Tickets!', 'js-support-ticket')); ?></h4>
                                        <p style="color: #15803d;">
                                            <?php echo esc_html(__('Congratulations! The queue is clear of overdue tickets.', 'js-support-ticket')); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </section>

                    <section class="js-hlpdsk-section-container">
                        <?php if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['tickets_by_department'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-chart-card js-hlpdsk-card-half">
                                <h3><?php echo esc_html(__('Tickets by Department', 'js-support-ticket')); ?></h3>
                                <div id="departmentChart" class="js-hlpdsk-chart-container-inner"></div>
                                <div class="js-hlpdsk-chart-legend">
                                    <?php
                                    if (!empty(jssupportticket::$_data['tickets_by_department'])) {
                                        foreach (jssupportticket::$_data['tickets_by_department'] as $key => $department) { ?>
                                            <span class="js-hlpdsk-legend-item">
                                                <span class="js-hlpdsk-legend-dot" style="background-color:<?php echo esc_attr(jssupportticket::$_data['department_colors'][$key] ?? '#6b7280'); ?>"></span>
                                                <?php echo esc_html($department->departmentname); ?>
                                            </span>
                                        <?php }
                                    } ?>
                                </div>
                            </div>
                            <?php
                        }
                        if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['tickets_by_status'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-chart-card js-hlpdsk-card-half">
                                <h3><?php echo esc_html(__('Tickets by Status', 'js-support-ticket')); ?></h3>
                                <div id="statusChart" class="js-hlpdsk-chart-container-inner"></div>
                                <div class="js-hlpdsk-chart-legend">
                                    <?php if (!empty(jssupportticket::$_data['tickets_by_status'])) {
                                        foreach (jssupportticket::$_data['tickets_by_status'] as $status) { ?>
                                            <span class="js-hlpdsk-legend-item">
                                                <span class="js-hlpdsk-legend-dot" style="background-color:<?php echo esc_attr($status->statusbgcolour); ?>"></span>
                                                <?php echo esc_html($status->status); ?>
                                            </span>
                                        <?php }
                                    } ?>
                                </div>
                            </div>
                            <?php
                        }
                        if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['tickets_by_priorities'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-chart-card js-hlpdsk-card-half">
                                <h3><?php echo esc_html(__('Tickets by Priorities', 'js-support-ticket')); ?></h3>
                                <div id="prioritiesChart" class="js-hlpdsk-chart-container-inner"></div>
                                <div class="js-hlpdsk-chart-legend">
                                    <?php
                                    if (!empty(jssupportticket::$_data['tickets_by_priorities'])) {
                                        foreach (jssupportticket::$_data['tickets_by_priorities'] as $priority) { ?>
                                            <span class="js-hlpdsk-legend-item">
                                                <span class="js-hlpdsk-legend-dot" style="background-color:<?php echo esc_attr($priority->prioritycolour); ?>"></span>
                                                <?php echo esc_html($priority->priority); ?>
                                            </span>
                                        <?php }
                                    } ?>
                                </div>
                            </div>
                            <?php
                        }
                        if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['tickets_by_products'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-chart-card js-hlpdsk-card-half">
                                <h3><?php echo esc_html(__('Tickets by Products', 'js-support-ticket')); ?></h3>
                                <div id="productsChart" class="js-hlpdsk-chart-container-inner"></div>
                                <div class="js-hlpdsk-chart-legend">
                                    <?php
                                    if (!empty(jssupportticket::$_data['tickets_by_products'])) {
                                        foreach (jssupportticket::$_data['tickets_by_products'] as $key => $product) { ?>
                                            <span class="js-hlpdsk-legend-item">
                                                <span class="js-hlpdsk-legend-dot" style="background-color:<?php echo esc_attr(jssupportticket::$_data['product_colors'][$key] ?? '#6b7280'); ?>"></span>
                                                <?php echo esc_html($product->product); ?>
                                            </span>
                                        <?php }
                                    } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </section>

                    <section class="js-hlpdsk-section-container">
                        <?php
                        if (in_array('cannedresponses', jssupportticket::$_active_addons) && !empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['canned_responses']) ) {?>
                            <div class="js-hlpdsk-card js-hlpdsk-card-half">
                                <div class="card-header-flex">
                                    <h3 class="js-hlpdsk-section-heading">
                                        <?php echo esc_html(__('List of Canned Responses', 'js-support-ticket')); ?>
                                    </h3>
                                    <a href="?page=cannedresponses" class="view-all-btn">
                                        <?php echo esc_html(__('View All', 'js-support-ticket')); ?>
                                    </a>
                                </div>
                                <?php if (!empty(jssupportticket::$_data['saved_replies'])) { ?>
                                    <ul class="js-hlpdsk-list-card">
                                        <?php foreach (jssupportticket::$_data['saved_replies'] as $reply) { ?>
                                            <li>
                                                <a href="?page=cannedresponses&jstlay=addpremademessage&jssupportticketid=<?php echo esc_attr($reply->id); ?>" class="js-hlpdsk-list-item-title"><?php echo esc_html($reply->title); ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } else { ?>
                                    <div class="js-hlpdsk-empty-state">
                                        <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                        <h4><?php echo esc_html(__('No Canned Responses', 'js-support-ticket')); ?></h4>
                                        <p><?php echo esc_html(__('Create your first canned response to speed up your workflow.', 'js-support-ticket')); ?>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['tickets_by_age'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-ticket-age-card js-hlpdsk-card-half">
                                <h3 class="js-hlpdsk-section-heading" style="font-size: 1.25rem; margin-bottom: 1rem">
                                    <?php echo esc_html(__('Open Tickets by Age', 'js-support-ticket')); ?>
                                </h3>
                                <?php if (!empty(jssupportticket::$_data['tickets_by_age']['lt_1_day']) || !empty(jssupportticket::$_data['tickets_by_age']['1_3_days']) || !empty(jssupportticket::$_data['tickets_by_age']['3_7_days'])) { ?>
                                    <div class="js-hlpdsk-table-wrapper">
                                        <table class="js-hlpdsk-modern-table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo esc_html(__('Age', 'js-support-ticket')); ?></th>
                                                    <th><?php echo esc_html(__('Count', 'js-support-ticket')); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo esc_html__('&lt; 1 Day', 'js-support-ticket'); ?></td>
                                                    <td><?php echo esc_html(jssupportticket::$_data['tickets_by_age']['lt_1_day']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo esc_html(__('1-3 Days', 'js-support-ticket')); ?></td>
                                                    <td><?php echo esc_html(jssupportticket::$_data['tickets_by_age']['1_3_days']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo esc_html(__('3-7 Days', 'js-support-ticket')); ?></td>
                                                    <td><?php echo esc_html(jssupportticket::$_data['tickets_by_age']['3_7_days']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo esc_html__('&gt; 7 Days', 'js-support-ticket'); ?></td>
                                                    <td><?php echo esc_html(jssupportticket::$_data['tickets_by_age']['gt_7_days']); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="js-hlpdsk-empty-state">
                                        <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0h18" />
                                        </svg>
                                        <h4><?php echo esc_html(__('No Open Tickets', 'js-support-ticket')); ?></h4>
                                        <p><?php echo esc_html(__('There are no open tickets to analyze by age.', 'js-support-ticket')); ?>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['active_customers'])) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-active-customer-card js-hlpdsk-card-half">
                                <h3 class="js-hlpdsk-section-heading" style="font-size: 1.25rem; margin-bottom: 1rem">
                                    <?php echo esc_html(__('Most Active Customers', 'js-support-ticket')); ?>
                                </h3>
                                <?php if (!empty(jssupportticket::$_data['most_active_customers'])) { ?>
                                    <div class="js-hlpdsk-table-wrapper">
                                        <table class="js-hlpdsk-modern-table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo esc_html(__('Customer Name', 'js-support-ticket')); ?></th>
                                                    <th><?php echo esc_html(__('Tickets Count', 'js-support-ticket')); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (jssupportticket::$_data['most_active_customers'] as $customer) { ?>
                                                    <tr>
                                                        <td><?php echo esc_html($customer->name); ?></td>
                                                        <td><?php echo esc_html($customer->ticket_count); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="js-hlpdsk-empty-state">
                                        <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.962c.57-1.023-.19-2.3-1.35-2.45-1.16-.15-2.23.6-2.23 1.602 0 .56.32 1.07 1.006 1.466l.657.377a2.25 2.25 0 01-2.25 3.865l-.83.083a2.25 2.25 0 00-2.25 2.25v.52a2.25 2.25 0 002.25 2.25H18a2.25 2.25 0 002.25-2.25v-.52a2.25 2.25 0 00-2.25-2.25l-.83-.083a2.25 2.25 0 01-2.25-3.865l.657-.377c.686-.396 1.006-.906 1.006-1.466 0-1.002-1.07-1.752-2.23-1.602-1.16.15-1.92 1.43-1.35 2.45z" />
                                        </svg>
                                        <h4><?php echo esc_html(__('No Active Customers', 'js-support-ticket')); ?></h4>
                                        <p><?php echo esc_html(__('Customer activity data is not yet available.', 'js-support-ticket')); ?>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        if( in_array('agent',jssupportticket::$_active_addons) && !empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['active_timer']) ) { ?>
                            <div class="js-hlpdsk-card js-hlpdsk-active-timer-card js-hlpdsk-card-half">
                                <h3 class="js-hlpdsk-section-heading" style="font-size: 1.25rem; margin-bottom: 1rem">
                                    <?php echo esc_html(__('Active Timer', 'js-support-ticket')); ?>
                                </h3>
                                <?php if (!empty(jssupportticket::$_data['active_timers'])) { ?>
                                    <div class="js-hlpdsk-table-wrapper">
                                        <table class="js-hlpdsk-modern-table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo esc_html(__('Ticket', 'js-support-ticket')); ?></th>
                                                    <th><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></th>
                                                    <th><?php echo esc_html(__('Timer', 'js-support-ticket')); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (jssupportticket::$_data['active_timers'] as $timer) { 
                                                    $time_diff = $timer->usertime;

                                                    $hours = floor($time_diff / 3600);
                                                    $minutes = floor($time_diff / 60);
                                                    $seconds = $time_diff % 60;
                                                ?>
                                                    <tr>
                                                        <td><a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($timer->id); ?>" class="js-hlpdsk-text-blue-link">#<?php echo esc_html($timer->ticketid); ?></a></td>
                                                        <td><?php echo esc_html($timer->subject); ?></td>
                                                        <td>
                                                            <?php
                                                            if ($hours > 0) {
                                                                echo esc_html($hours).' h';
                                                            } elseif ($minutes > 0) {
                                                                echo esc_html($minutes).' m';
                                                            } elseif ($seconds > 0) {
                                                                echo esc_html($seconds). '  ';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="js-hlpdsk-empty-state">
                                        <svg class="js-hlpdsk-empty-state-icon" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h4><?php echo esc_html(__('No Active Timers', 'js-support-ticket')); ?></h4>
                                        <p><?php echo esc_html(__('No agents have active timers running on tickets.', 'js-support-ticket')); ?>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                        } ?>
                    </section>
                <?php if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['available_addons'])) { ?>
                    <?php
                    // Define the dynamic list of available addons.
                    $available_addons = [
                        'agent' => [
                            'title' => __('Agents', 'js-support-ticket'),
                            'description' => __('Add agents and assign roles and permissions to provide assistance.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-agent/js-support-ticket-agent.php',
                            'url' => 'https://jshelpdesk.com/product/agents/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                            'icon_svg' => '<svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/></svg>',
                        ],
                        'autoclose' => [
                            'title' => __('Ticket Auto Close', 'js-support-ticket'),
                            'description' => __('Define rules for the ticket to auto-close after a specific interval of time.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-autoclose/js-support-ticket-autoclose.php',
                            'url' => 'https://jshelpdesk.com/product/close-ticket/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                        ],
                        'feedback' => [
                            'title' => __('Feedbacks', 'js-support-ticket'),
                            'description' => __('Get a survey from customers on ticket closing to improve quality.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-feedback/js-support-ticket-feedback.php',
                            'url' => 'https://jshelpdesk.com/product/feedback/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
                        ],
                        'helptopic' => [
                            'title' => __('Help Topics', 'js-support-ticket'),
                            'description' => __('Help users to find and select the area with which they need assistance.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-helptopic/js-support-ticket-helptopic.php',
                            'url' => 'https://jshelpdesk.com/product/helptopic/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                        ],
                        'note' => [
                            'title' => __('Private Note', 'js-support-ticket'),
                            'description' => __('The private note is used as reminders or to give other agents insights into the ticket issue.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-note/js-support-ticket-note.php',
                            'url' => 'https://jshelpdesk.com/product/internal-note/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a5.5 5.5 0 017.778 7.778L13.828 17.828a5.5 5.5 0 01-7.778-7.778l2.036-2.036m3.536 3.536L10 14m0 0l-1-1m-1-1l-3 3" /></svg>',
                        ],
                        'knowledgebase' => [
                            'title' => __('Knowledge Base', 'js-support-ticket'),
                            'description' => __('Stop losing productivity on repetitive queries, build your knowledge base.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-knowledgebase/js-support-ticket-knowledgebase.php',
                            'url' => 'https://jshelpdesk.com/product/knowledge-base/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                        ],
                        'maxticket' => [
                            'title' => __('Max Tickets', 'js-support-ticket'),
                            'description' => __('Enables admin to set N numbers of tickets for users and agents separately.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-maxticket/js-support-ticket-maxticket.php',
                            'url' => 'https://jshelpdesk.com/product/max-ticket/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V9m6 10V5m6 14V3M3 21h18" /></svg>',
                        ],
                        'mergeticket' => [
                            'title' => __('Merge Tickets', 'js-support-ticket'),
                            'description' => __('Enables agents to merge two tickets of the same user into one instead of dealing with the same issue on many tickets.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-mergeticket/js-support-ticket-mergeticket.php',
                            'url' => 'https://jshelpdesk.com/product/merge-ticket/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>',
                        ],
                        'overdue' => [
                            'title' => __('Overdue', 'js-support-ticket'),
                            'description' => __('Defines rules or set specific intervals of time to make ticket auto overdue.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-overdue/js-support-ticket-overdue.php',
                            'url' => 'https://jshelpdesk.com/product/overdue/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                        ],
                        'smtp' => [
                            'title' => __('SMTP', 'js-support-ticket'),
                            'description' => __('SMTP enables you to add custom mail protocol to send and receive emails.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-smtp/js-support-ticket-smtp.php',
                            'url' => 'https://jshelpdesk.com/product/smtp/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V6a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>',
                        ],
                        'tickethistory' => [
                            'title' => __('Ticket History', 'js-support-ticket'),
                            'description' => __('Displays complete ticket history along with the ticket status, currently assigned user and other actions performed on each ticket.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-tickethistory/js-support-ticket-tickethistory.php',
                            'url' => 'https://jshelpdesk.com/product/ticket-history/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8M8 11h8M8 15h6" /></svg>',
                        ],
                        'cannedresponses' => [
                            'title' => __('Canned Responses', 'js-support-ticket'),
                            'description' => __('Pre-populated messages allow support agents to respond quickly.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-cannedresponses/js-support-ticket-cannedresponses.php',
                            'url' => 'https://jshelpdesk.com/product/canned-responses/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                        ],
                        'emailpiping' => [
                            'title' => __('Email Piping', 'js-support-ticket'),
                            'description' => __('Enables users to reply to the tickets via email without login.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-emailpiping/js-support-ticket-emailpiping.php',
                            'url' => 'https://jshelpdesk.com/product/email-piping/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14v6m0 0l-2-2m2 2l2-2" /></svg>',
                        ],
                        'timetracking' => [
                            'title' => __('Time Tracking', 'js-support-ticket'),
                            'description' => __('Track the time spent on each ticket by each agent and each reply.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-timetracking/js-support-ticket-timetracking.php',
                            'url' => 'https://jshelpdesk.com/product/time-tracking/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                        ],
                        'useroptions' => [
                            'title' => __('User Options', 'js-support-ticket'),
                            'description' => __('User options enable you to add Google Re-captcha or JS Help Desk Re-captcha for a registration form.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-useroptions/js-support-ticket-useroptions.php',
                            'url' => 'https://jshelpdesk.com/product/user-options/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
                        ],
                        'actions' => [
                            'title' => __('Ticket Actions', 'js-support-ticket'),
                            'description' => __('Get multiple action options on each ticket like Print Ticket, Lock Ticket, Transfer ticket, etc.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-actions/js-support-ticket-actions.php',
                            'url' => 'https://jshelpdesk.com/product/actions/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                        ],
                        'announcement' => [
                            'title' => __('Announcements', 'js-support-ticket'),
                            'description' => __('Make unlimited announcements associated with the support system.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-announcement/js-support-ticket-announcement.php',
                            'url' => 'https://jshelpdesk.com/product/announcements/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 8.5V5a2 2 0 00-2-2l-7 4H3v6h3l7 4a2 2 0 002-2v-3.5M19 10v4m0-4a2 2 0 010 4" /></svg>',
                        ],
                        'banemail' => [
                            'title' => __('Ban Emails', 'js-support-ticket'),
                            'description' => __('It allows you to block the email of any user to restrict him to create new tickets.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-banemail/js-support-ticket-banemail.php',
                            'url' => 'https://jshelpdesk.com/product/ban-email/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                            'icon_svg' => '<svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8" /><circle cx="18" cy="18" r="4" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 16.5L19.5 19.5" /></svg>',
                        ],
                        'notification' => [
                            'title' => __('Desktop Notification', 'js-support-ticket'),
                            'description' => __('The Desktop notifications will keep you up to date about anything happens on your support system.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-notification/js-support-ticket-notification.php',
                            'url' => 'https://jshelpdesk.com/product/desktop-notification/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
                        ],
                        'export' => [
                            'title' => __('Export', 'js-support-ticket'),
                            'description' => __('Save the ticket as a PDF in your system and able to export all data.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-export/js-support-ticket-export.php',
                            'url' => 'https://jshelpdesk.com/product/export/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>',
                        ],
                        'download' => [
                            'title' => __('Downloads', 'js-support-ticket'),
                            'description' => __('Create downloads to ensure the user to get downloads from downloads.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-download/js-support-ticket-download.php',
                            'url' => 'https://jshelpdesk.com/product/downloads/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h5l2 2h11a1 1 0 011 1v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3" /></svg>',
                        ],
                        'faq' => [
                            'title' => __("FAQ's", 'js-support-ticket'),
                            'description' => __('Add FAQs to drastically reduce the number of common questions.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-faq/js-support-ticket-faq.php',
                            'url' => 'https://jshelpdesk.com/product/faq/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10a2 2 0 012 2v7a2 2 0 01-2 2H9l-4 3v-3H5a2 2 0 01-2-2v-7a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 11.5a1.5 1.5 0 113 0c0 .75-.5 1.5-1.5 1.5v1m0 2h.01" /></svg>',
                        ],
                        'dashboardwidgets' => [
                            'title' => __('Dashboard Widgets', 'js-support-ticket'),
                            'description' => __('Get immediate data of your support operations as soon as you log into your WordPress administration area.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-dashboardwidgets/js-support-ticket-dashboardwidgets.php',
                            'url' => 'https://jshelpdesk.com/product/admin-widget/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>',
                        ],
                        'mail' => [
                            'title' => __('Internal Mail', 'js-support-ticket'),
                            'description' => __('Use an internal email to send emails to one agent to another agent.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-mail/js-support-ticket-mail.php',
                            'url' => 'https://jshelpdesk.com/product/internal-mail/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12h4m0 0l-2-2m2 2l-2 2" /></svg>',
                        ],
                        'widgets' => [
                            'title' => __('Front-End Widgets', 'js-support-ticket'),
                            'description' => __('Widgets in WordPress allow you to add content and features in the widgetized areas of your theme.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-widgets/js-support-ticket-widgets.php',
                            'url' => 'https://jshelpdesk.com/product/widget/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></svg>',
                        ],
                        'woocommerce' => [
                            'title' => __('WooCommerce', 'js-support-ticket'),
                            'description' => __('JS Help Desk WooCommerce provides the much-needed bridge between your WooCommerce store and the JS Help Desk.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-woocommerce/js-support-ticket-woocommerce.php',
                            'url' => 'https://jshelpdesk.com/product/woocommerce/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" /><path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" /></svg>',
                        ],
                        'privatecredentials' => [
                            'title' => __('Private Credentials', 'js-support-ticket'),
                            'description' => __('Collect your customer\'s private data, sensitive information from credit card to health information and store them encrypted.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-privatecredentials/js-support-ticket-privatecredentials.php',
                            'url' => 'https://jshelpdesk.com/product/private-credentials/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c-1.657 0-3 1.343-3 3v4h6v-4c0-1.657-1.343-3-3-3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a5 5 0 0110 0v4" /><rect x="5" y="11" width="14" height="10" rx="2" ry="2" /></svg>',
                        ],
                        'envatovalidation' => [
                            'title' => __('Envato Validation', 'js-support-ticket'),
                            'description' => __('Without valid Envato, license clients won\'t be able to open a new ticket.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-envatovalidation/js-support-ticket-envatovalidation.php',
                            'url' => 'https://jshelpdesk.com/product/envato/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" /></svg>',
                        ],
                        'mailchimp' => [
                            'title' => __('Mailchimp', 'js-support-ticket'),
                            'description' => __('Adds the option to the registration form for prompting new users to subscribe to your email list.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-mailchimp/js-support-ticket-mailchimp.php',
                            'url' => 'https://jshelpdesk.com/product/mail-chimp/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l-2-2m12 2l2-2" /></svg>',
                        ],
                        'paidsupport' => [
                            'title' => __('Paid Support', 'js-support-ticket'),
                            'description' => __('Paid Support is the easiest way to integrate and manage payments for your tickets.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-paidsupport/js-support-ticket-paidsupport.php',
                            'url' => 'https://jshelpdesk.com/product/paid-support/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0c1.5 0 2.5-.5 2.5-1.5S13.5 13 12 13m0 3c-1.5 0-2.5-.5-2.5-1.5S10.5 13 12 13" /></svg>',
                        ],
                        'easydigitaldownloads' => [
                            'title' => __('Easy Digital Downloads', 'js-support-ticket'),
                            'description' => __('EDD offers customers to open new tickets just one click from their EDD account.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-easydigitaldownloads/js-support-ticket-easydigitaldownloads.php',
                            'url' => 'https://jshelpdesk.com/product/easy-digital-download/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12v6m0 0l-3-3m3 3l3-3m4-2V7a2 2 0 00-2-2h-4l-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2h8" /></svg>',
                        ],
                        'multilanguageemailtemplates' => [
                            'title' => __('Multi Language Email Templates', 'js-support-ticket'),
                            'description' => __('It allows to create language-based email templates for all JS Help Desk email templates.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-multilanguageemailtemplates/js-support-ticket-multilanguageemailtemplates.php',
                            'url' => 'https://jshelpdesk.com/product/multi-language-email-templates',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 4h4v3H7V4zm6 0h4v3h-4V4z" /></svg>',
                        ],
                        'emailcc' => [
                            'title' => __('Email Cc', 'js-support-ticket'),
                            'description' => __('CC(Carbon Copy) - the people who should know about the information which is being shared and the people included are able to see who is there in the list.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-emailcc/js-support-ticket-emailcc.php',
                            'url' => 'https://jshelpdesk.com/product/emailcc/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><circle cx="7" cy="19" r="2" /><circle cx="17" cy="19" r="2" /></svg>',
                        ],
                        'multiform' => [
                            'title' => __('Multiform', 'js-support-ticket'),
                            'description' => __('It allows user to add more than one form based on requirements.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-multiform/js-support-ticket-multiform.php',
                            'url' => 'https://jshelpdesk.com/product/multi-forms/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="12" height="12" rx="2" ry="2"/><rect x="8" y="8" width="12" height="12" rx="2" ry="2"/></svg>',
                        ],
                        'agentautoassign' => [
                            'title' => __('Agent Auto Assign', 'js-support-ticket'),
                            'description' => __('When a ticket is created, an appropriate agent is automatically assigned to the ticket and it is moved to the Assigned state.', 'js-support-ticket'),
                            'plugin_file' => 'js-support-ticket-agentautoassign/js-support-ticket-agentautoassign.php',
                            'url' => 'https://jshelpdesk.com/product/agentautoassign/',
                            'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                            'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="js-hlpdsk-addon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3"/><path d="M6 21v-2a6 6 0 0112 0v2"/><path d="M19.4 15a2 2 0 010-6"/><path d="M4.6 9a2 2 0 010 6"/></svg>',
                        ],
                    ];

                    // Filter out active addons to get a list of inactive ones
                    $inactive_addons = array_filter($available_addons, function($addon_slug) {
                        return !in_array($addon_slug, jssupportticket::$_active_addons);
                    }, ARRAY_FILTER_USE_KEY);

                    // Show the section only if there are inactive addons
                    if (count($inactive_addons) > 0) {
                        ?>
                        <section class="js-hlpdsk-section-container">
                            <div class="js-hlpdsk-data-card">
                                <div class="js-hlpdsk-header-container">
                                    <h3 class="js-hlpdsk-section-heading" style="font-size: 1.25rem; margin-bottom: 1rem">
                                        <?php echo esc_html(__('Available Addons', 'js-support-ticket')); ?>
                                    </h3>
                                    <?php if (count($inactive_addons) > 3) { ?>
                                        <a href="?page=jssupportticket&jstlay=addonstatus" class="js-hlpdsk-view-all-link">
                                            <?php echo esc_html(__('View All Addons', 'js-support-ticket')); ?>
                                        </a>
                                    <?php } ?>
                                </div>
                                <div class="js-hlpdsk-addons-grid">
                                    <?php
                                    // Show the first 3 inactive addons
                                    $addons_to_display = array_slice($inactive_addons, 0, 3, true);
                                    foreach ($addons_to_display as $addon_slug => $addon) {
                                        $plugininfo = JSSTCheckPluginInfo($addon['plugin_file']);
                                        if($plugininfo['availability'] == "1"){
                                            $button_text = $plugininfo['text'];
                                            $button_url = "plugins.php?s=".$addon_slug."&plugin_status=inactive";
                                        }elseif($plugininfo['availability'] == "0"){
                                            $button_text = isset($plugininfo['text']) ? $plugininfo['text'] : __('View Details', 'js-support-ticket');
                                            $button_url = $addon['url'];
                                        } else {
                                            // Default to install button
                                            $button_text = __('Install Now', 'js-support-ticket');
                                            $button_url = "#";
                                        }
                                        ?>
                                        <div class="js-hlpdsk-addon-card">
                                            <div class="js-hlpdsk-addon-icon-container <?php echo esc_attr($addon['icon_bg']); ?>">
                                                <?php echo $addon['icon_svg']; ?>
                                            </div>
                                            <h4 class="js-hlpdsk-addon-title"><?php echo esc_html($addon['title']); ?></h4>
                                            <p class="js-hlpdsk-addon-description"><?php echo esc_html($addon['description']); ?></p>
                                            <a href="<?php echo esc_url($button_url); ?>" class="js-hlpdsk-install-button">
                                                <?php echo esc_html($button_text); ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </section>
                        <?php
                    }
                }
                if (!empty(jssupportticket::$_data['jssupportticket_admin_charts_visibility']['installation_guide'])) { ?>
                    <section class="js-hlpdsk-section-container">
                        <div class="js-cp-cnt-sec js-cp-video-baner">
                            <div class="js-cp-video-baner-cnt">
                                <div class="js-cp-video-baner-tit">
                                    <?php echo esc_html(__('Quick installation Guide','js-support-ticket')); ?>
                                </div>
                                <div class="js-cp-video-baner-desc">
                                    <?php echo esc_html(__('The best support system plugin for WordPress has everything you need.','js-support-ticket')); ?>
                                </div>
                                <div class="js-cp-video-baner-btn-wrp">
                                    <a target="blank" href="https://www.youtube.com/watch?v=Honmzw892ZE" class="js-cp-video-baner-btn js-cp-video-baner-1">
                                        <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                        <?php echo esc_html(__('How to setup','js-support-ticket')); ?>
                                    </a>
                                    <a target="blank" href="https://www.youtube.com/watch?v=dNYnZw8WK0M" class="js-cp-video-baner-btn js-cp-video-baner-2">
                                        <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                        <?php echo esc_html(__('System Emails','js-support-ticket')); ?>
                                    </a>
                                    <a target="blank" href="https://www.youtube.com/watch?v=zmQ4bpqSYnk" class="js-cp-video-baner-btn js-cp-video-baner-3">
                                        <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                        <?php echo esc_html(__('Ticket Creation','js-support-ticket')); ?>
                                    </a>
                                    <a target="blank" href="https://www.youtube.com/watch?v=c7whQ6F70yM" class="js-cp-video-baner-btn js-cp-video-baner-4">
                                        <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                        <?php echo esc_html(__('Custom Fields','js-support-ticket')); ?>
                                    </a>
                                    <a target="blank" href="https://www.youtube.com/watch?v=LvsrMtEqRms" class="js-cp-video-baner-btn js-cp-video-baner-5">
                                        <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                        <?php echo esc_html(__('Email Notification Problems','js-support-ticket')); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php } ?>
                </main>
            </div>

        </div>
        <?php
        $jssupportticket_js = '
            jQuery(document).ready(function () {
                jQuery("span.dashboard-icon").find("span.download").hover(function(){
                    jQuery(this).find("span").toggle("slide");
                    }, function(){
                    jQuery(this).find("span").toggle("slide");
                });

                jQuery("a.js-hlpdsk-like-card").click(function(e){
                    e.preventDefault();
                    var list = jQuery(this).attr("data-tab-number");
                    var oldUrl = jQuery(this).attr("href");
                    var newUrl = oldUrl+"&list="+list;
                    window.location.href = newUrl;
                });
            });
        ';
        wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
        ?>
    </div>
</div>
<?php
$nonce = wp_create_nonce('jssupportticket_admin_nonce');
$jssupportticket_js_charts = "
    jQuery(document).ready(function() {
        // Chart: Today's Ticket Distribution (Radial Bar)
        const todayTicketsOptions = {
            series: ". json_encode([jssupportticket::$_data['today_distribution']['new'] ?? 0, jssupportticket::$_data['today_distribution']['answered'] ?? 0, jssupportticket::$_data['today_distribution']['pending'] ?? 0]) .",
            chart: { height: 300, type: 'radialBar', toolbar: { show: false } },
            plotOptions: {
                radialBar: {
                    dataLabels: {
                        name: { fontSize: '18px' },
                        value: {
                            fontSize: '16px',
                            formatter: (val) => parseInt(val) + '',
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: (w) =>
                                w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                        },
                    },
                },
            },
            labels: ['New', 'Answered', 'Pending'],
            colors: ['#3b82f6', '#10b981', '#f59e0b'],
            legend: { show: false },
        };
        new ApexCharts(
            document.querySelector('#todayTicketsChart'),
            todayTicketsOptions
        ).render();

        // Chart: Ticket Trends (Area Chart)
        const statisticsOptions = {
            series: [
                { name: 'Open', data: ". json_encode(is_array(jssupportticket::$_data['ticket_trends']['new']) ? jssupportticket::$_data['ticket_trends']['new'] : []) ." },
                { name: 'Pending', data: ". json_encode(is_array(jssupportticket::$_data['ticket_trends']['pending']) ? jssupportticket::$_data['ticket_trends']['pending'] : []) ." },
                { name: 'Answered', data: ". json_encode(is_array(jssupportticket::$_data['ticket_trends']['answered']) ? jssupportticket::$_data['ticket_trends']['answered'] : []) ." },
            ],
            chart: {
                type: 'area', // Corrected to area chart type
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            dataLabels: {
                enabled: false, // Hiding data labels for cleaner area chart.
            },
            stroke: {
                curve: 'smooth', // Using a smooth curve for better area visualization.
                show: true,
                width: 2,
            },
            xaxis: {
                type: 'datetime',
                categories: ". json_encode(is_array(jssupportticket::$_data['ticket_trends']['dates']) ? jssupportticket::$_data['ticket_trends']['dates'] : []) .",
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    format: 'dd MMM',
                    style: { colors: '#6b7280', fontSize: '12px' },
                },
            },
            yaxis: {
                title: {
                    text: 'Tickets',
                },
            },
            fill: {
                opacity: 1,
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + ' tickets';
                    },
                },
            },
            colors: ['#06b6d4', '#a855f7', '#f59e0b'],
            legend: { show: false },
            grid: {
                show: true,
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
            },
        };
        new ApexCharts(
            document.querySelector('#statisticsChart'),
            statisticsOptions
        ).render();

        // Chart: Tickets by Status (Horizontal Bar)
        const statusOptions = {
            series: [{
                name: 'Tickets',
                data: ". json_encode(array_map('intval', array_column(jssupportticket::$_data['tickets_by_status'] ?? [], 'ticket_count'))) ."
            }],
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            xaxis: {
                categories: ". json_encode(array_column(jssupportticket::$_data['tickets_by_status'] ?? [], 'status')) .",
                labels: {
                    show: true,
                    style: { colors: '#6b7280', fontSize: '12px' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                show: true,
                labels: { style: { colors: '#6b7280', fontSize: '12px' } },
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 8,
                    distributed: true, // ✅ multiple colors enabled
                    dataLabels: { position: 'top' },
                },
            },
            //  Now colors array will apply to each bar
            colors: ". json_encode(array_column(jssupportticket::$_data['tickets_by_status'] ?? [], 'statusbgcolour')) .",
            dataLabels: {
                enabled: true,
                offsetX: -15,
                style: { fontSize: '12px', colors: ['#fff'] },
            },
            legend: { show: false },
            grid: {
                show: true,
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
            },
        };
        new ApexCharts(
            document.querySelector('#statusChart'),
            statusOptions
        ).render();

        // Chart: Tickets by Priorities (Pie)
        const prioritiesOptions = {
            series: ". json_encode(array_map('intval', array_column(jssupportticket::$_data['tickets_by_priorities'] ?? [], 'ticket_count'))) .",
            chart: { type: 'pie', height: 300, toolbar: { show: false } },
            labels: ". json_encode(array_column(jssupportticket::$_data['tickets_by_priorities'] ?? [], 'priority')) .",
            colors: ". json_encode(array_column(jssupportticket::$_data['tickets_by_priorities'] ?? [], 'prioritycolour')) .",
            legend: { show: false },
            dataLabels: {
                enabled: true,
                formatter: (val) => val.toFixed(1) + '%',
            },
        };
        new ApexCharts(
            document.querySelector('#prioritiesChart'),
            prioritiesOptions
        ).render();

        // Chart: Tickets by Department (Horizontal Bar)
        const departmentOptions = {
            series: [{
                name: 'Tickets',
                data: ". json_encode(array_map('intval', array_column(jssupportticket::$_data['tickets_by_department'] ?? [], 'ticket_count'))) ."
            }],
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            xaxis: {
                categories: ". json_encode(array_column(jssupportticket::$_data['tickets_by_department'] ?? [], 'departmentname')) .",
                labels: {
                    show: true,
                    style: { colors: '#6b7280', fontSize: '12px' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                show: true,
                labels: { style: { colors: '#6b7280', fontSize: '12px' } },
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 8,
                    distributed: true, // ✅ Added to allow multiple colors
                    dataLabels: { position: 'top' },
                },
            },
            colors: ". json_encode(jssupportticket::$_data['department_colors'] ?? []) .",
            dataLabels: {
                enabled: true,
                offsetX: -15,
                style: { fontSize: '12px', colors: ['#fff'] },
            },
            legend: { show: false },
            grid: {
                show: true,
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
            },
        };
        new ApexCharts(
            document.querySelector('#departmentChart'),
            departmentOptions
        ).render();

        // Chart: Tickets by Products (Bar)
        const productsOptions = {
            series: [{ name: 'Tickets', data: ". json_encode(array_map('intval', array_column(jssupportticket::$_data['tickets_by_products'] ?? [], 'ticket_count'))) ." }],
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    distributed: true   // ✅ Each bar gets a different color
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ". json_encode(array_column(jssupportticket::$_data['tickets_by_products'] ?? [], 'product')) .",
                labels: { style: { colors: '#6b7280', fontSize: '12px' } },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: { labels: { style: { colors: '#6b7280', fontSize: '12px' } } },
            grid: {
                show: true,
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } },
            },
            colors: ". json_encode(jssupportticket::$_data['product_colors'] ?? ['#008FFB', '#00E396', '#FEB019', '#FF4560']) .",
            legend: { show: false },
        };
        new ApexCharts(
            document.querySelector('#productsChart'),
            productsOptions
        ).render();

        // Start of new panel JS
        const customizeBtn = jQuery('#js-customize-dashboard-btn');
        const panelOverlay = jQuery('#js-dashboard-customize-panel-overlay');
        const panel = jQuery('#js-dashboard-customize-panel');
        const closeBtn = jQuery('#js-close-customize-panel');
        const saveBtn = jQuery('#js-save-customize-panel');
        const preferencesData = " . json_encode(jssupportticket::$_data['jssupportticket_admin_charts_visibility']) . ";

        function loadPreferencesIntoPanel() {
            for (const chartId in preferencesData) {
                const toggle = jQuery('#toggle-' + chartId);
                if (toggle.length) {
                    toggle.prop('checked', preferencesData[chartId]);
                }
            }
        }

        function savePreferences() {
            const preferences = {};
            jQuery('#js-customize-form input[type=\"checkbox\"]').each(function() {
                preferences[this.name] = this.checked;
            });

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_dashboard_preferences',
                    nonce: '" . esc_js($nonce) . "',
                    preferences: preferences,
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        console.error('Failed to save preferences.');
                    }
                }
            });
            panelOverlay.removeClass('is-visible');
            panel.removeClass('is-visible');
        }

        customizeBtn.on('click', function() {
            loadPreferencesIntoPanel();
            panelOverlay.addClass('is-visible');
            panel.addClass('is-visible');
        });

        closeBtn.on('click', function() {
            panelOverlay.removeClass('is-visible');
            panel.removeClass('is-visible');
        });

        panelOverlay.on('click', function(e) {
            if (e.target.id === 'js-dashboard-customize-panel-overlay') {
                panelOverlay.removeClass('is-visible');
                panel.removeClass('is-visible');
            }
        });

        saveBtn.on('click', savePreferences);

        function applyInitialLayout() {
            const initialPreferences = " . json_encode(jssupportticket::$_data['jssupportticket_admin_charts_visibility']) . ";
            const chartSections = {
                'ticket_trends': jQuery('#statisticsChart').closest('.js-hlpdsk-card'),
                'today_distribution': jQuery('#todayTicketsChart').closest('.js-hlpdsk-card'),
                'tickets_by_status': jQuery('#statusChart').closest('.js-hlpdsk-card'),
                'tickets_by_priorities': jQuery('#prioritiesChart').closest('.js-hlpdsk-card'),
                'tickets_by_department': jQuery('#departmentChart').closest('.js-hlpdsk-card'),
                'tickets_by_products': jQuery('#productsChart').closest('.js-hlpdsk-card'),
                'latest_tickets': jQuery('.latest-tickets-card'),
                'agent_workload': jQuery('.js-hlpdsk-work-load-card'),
                'ticket_history': jQuery('.js-hlpdsk-ticket-history-card'),
            };

            for (const chartName in initialPreferences) {
                if (initialPreferences.hasOwnProperty(chartName) && initialPreferences[chartName] === false) {
                    if (chartSections[chartName]) {
                        chartSections[chartName].closest('.js-hlpdsk-section-container').addClass('hidden');
                    }
                }
            }
        }
        applyInitialLayout();
    });
";
// Add the inline script to the page
wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js_charts);
?>
