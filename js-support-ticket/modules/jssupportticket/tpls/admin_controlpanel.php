<?php
if(!defined('ABSPATH'))
    die('Restricted Access');

/**
 * =================================================================================
 * CORRECTED SCRIPT LOADING
 * This PHP block is corrected to ensure scripts load in the proper order.
 * =================================================================================
 */
// 1. Register Chart.js with a unique handle.
wp_register_script(
    'jsst-chart-js',
    JSST_PLUGIN_URL . 'includes/js/chart.umd.js',
    [],
    jssupportticket::$_config['productversion'], // Explicitly set your plugin version
    true
);

// 2. Register a master handle for this page's scripts with dependencies.
wp_register_script(
    'js-support-ticket-dashboard-scripts', // Master handle
    '', // No source file, we will use inline JS
    ['jquery', 'jsst-chart-js'], // This tells WordPress to load jQuery and Chart.js first
    jssupportticket::$_config['productversion'], // Explicitly set your plugin version,
    true
);

// 3. Enqueue the master handle. WordPress will now load the dependencies automatically.
wp_enqueue_script('js-support-ticket-dashboard-scripts');

// Enqueue your other scripts as before
wp_enqueue_script('ticket-apexcharts', JSST_PLUGIN_URL . 'includes/js/apexcharts.min.js', array(), jssupportticket::$_config['productversion'], true);
wp_enqueue_script('ticket-notify-app', JSST_PLUGIN_URL . 'includes/js/firebase-app.js', array(), jssupportticket::$_config['productversion'], true);
wp_enqueue_script('ticket-notify-message', JSST_PLUGIN_URL . 'includes/js/firebase-messaging.js', array(), jssupportticket::$_config['productversion'], true);

do_action('jsst_ticket-notify-generate-token');
JSSTmessage::getMessage();
?>

<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <main class="flex-1 flex flex-col overflow-y-auto jsstadmin-right-content">
        <div class="p-4 sm:p-6 lg:p-8 space-y-8">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <h1 class="text-3xl font-bold text-gray-900"><?php echo esc_html__('Dashboard', 'js-support-ticket'); ?></h1>
                <button id="menu-toggle" class="flex items-center gap-2 bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-gray-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span><?php echo esc_html__('Customize', 'js-support-ticket'); ?></span>
                </button>
            </div>

            <section id="quick-actions" class="bg-white p-6 rounded-xl shadow-lg flex flex-col md:flex-row justify-between items-center gap-6 flex-wrap">
                <div class="flex-1 text-center md:text-left md:rtl:text-right">
                    <h2 class="text-2xl font-bold text-gray-900"><?php echo esc_html__('Quick Actions', 'js-support-ticket'); ?></h2>
                    <p class="text-gray-500 text-sm mt-1">
                        <?php echo esc_html__('Easily access your main tasks from here.', 'js-support-ticket'); ?>
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto flex-wrap justify-center">
                    <?php
                    $jsst_href = admin_url('admin.php?page=ticket&jstlay=addticket&formid=' . JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId());
                    $jsst_extra_attributes = '';
                    if (in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1) {
                        $jsst_href = '#';
                        $jsst_extra_attributes = "id=multiformpopup";
                    }
                    ?>
                    <a <?php echo esc_attr($jsst_extra_attributes); ?> href="<?php echo esc_url($jsst_href); ?>" class="flex items-center justify-center gap-2 w-full sm:w-auto bg-[#4f46e5] text-white hover:text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <?php echo esc_html__('Create a Ticket', 'js-support-ticket'); ?>
                    </a>
                    <a href="?page=ticket" class="flex items-center justify-center gap-2 w-full sm:w-auto bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path></svg>
                        <?php echo esc_html__('All Tickets', 'js-support-ticket'); ?>
                    </a>
                    <?php if( in_array('agent',jssupportticket::$_active_addons) ) { ?>
                        <a href="?page=agent" class="flex items-center justify-center gap-2 w-full sm:w-auto bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <?php echo esc_html__('Agents', 'js-support-ticket'); ?>
                        </a>
                    <?php } ?>
                    <a href="<?php echo esc_url(wp_nonce_url('?page=jssupportticket&task=addmissingusers&action=jstask','add-missing-users'));?>" class="flex items-center justify-center gap-2 w-full sm:w-auto bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                            >
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                        </svg>
                        <?php echo esc_html__('Add WP Users', 'js-support-ticket'); ?>
                    </a>
                </div>
            </section>
            
            <div id="stats-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <a href="?page=ticket" data-tab-number="1" class="js-hlpdsk-like-card flex justify-between items-start flex-wrap">
                        <div>
                            <p class="text-sm font-medium text-gray-500"><?php echo esc_html__('New Tickets', 'js-support-ticket'); ?></p>
                            <p class="text-4xl font-bold text-gray-800"><?php echo esc_html(jssupportticket::$jsst_data['new_tickets']); ?></p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-lg">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-[#4f46e5]"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                        </div>
                    </a>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <a href="?page=ticket" data-tab-number="1" class="js-hlpdsk-like-card flex justify-between items-start flex-wrap">
                        <div>
                            <p class="text-sm font-medium text-gray-500"><?php echo esc_html__('Pending Tickets', 'js-support-ticket'); ?></p>
                            <p class="text-4xl font-bold text-gray-800"><?php echo esc_html(jssupportticket::$jsst_data['pending_tickets']); ?></p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-amber-600"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>
                        </div>
                    </a>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <a href="?page=ticket" data-tab-number="2" class="js-hlpdsk-like-card flex justify-between items-start flex-wrap">
                        <div>
                            <p class="text-sm font-medium text-gray-500"><?php echo esc_html__('Answered Tickets', 'js-support-ticket'); ?></p>
                            <p class="text-4xl font-bold text-gray-800"><?php echo esc_html(jssupportticket::$jsst_data['answered_tickets']); ?></p>
                        </div>
                        <div class="bg-teal-100 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-teal-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                    </a>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <a href="?page=ticket" data-tab-number="4" class="js-hlpdsk-like-card flex justify-between items-start flex-wrap">
                        <div>
                            <p class="text-sm font-medium text-gray-500"><?php echo esc_html__('Closed Tickets', 'js-support-ticket'); ?></p>
                            <p class="text-4xl font-bold text-gray-800"><?php echo esc_html(jssupportticket::$jsst_data['closed_today']); ?></p>
                        </div>
                          <div class="bg-rose-100 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-rose-600"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                    </a>
                </div>
            </div>

            <?php if (jssupportticket::$jsst_data['update_avaliable_for_addons'] != 0) {?>
                <section id="activation-key-expiry" class="bg-indigo-50 border-l-4 border-[#4f46e5] text-gray-700 p-6 rounded-xl shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 text-[#4f46e5]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <polyline points="23 4 23 10 17 10"></polyline>
                              <polyline points="1 20 1 14 7 14"></polyline>
                              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-black"><?php echo esc_html(__('New Addon Update Available', 'js-support-ticket')); ?></h2>
                            <p class="text-gray-600 text-sm mt-1">
                                <?php echo esc_html(__('Install the latest version to unlock new features and security patches.', 'js-support-ticket')); ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-full sm:w-auto">
                        <a href="?page=jssupportticket&jstlay=addonstatus" class="w-full sm:w-auto bg-[#4f46e5] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#4338ca] transition-colors whitespace-nowrap inline-block text-center hover:text-white">
                            <?php echo esc_html__('Update Now', 'js-support-ticket'); ?>
                        </a>
                    </div>
                </section>
            <?php } ?>
            
            <section id="daily-ticket-flow" class="bg-gradient-to-r from-indigo-500 to-indigo-700 text-white p-6 rounded-xl shadow-lg flex flex-col md:flex-row justify-between items-center gap-6 flex-wrap">
                <div class="flex-1 text-center md:text-left md:rtl:text-right">
                    <h2 class="text-2xl font-bold text-white"><?php echo esc_html__('Daily Ticket Flow', 'js-support-ticket'); ?></h2>
                    <p class="text-white/80 text-sm mt-2">
                        <?php echo esc_html__("Here's a summary of today's ticket activity. This helps in tracking our daily progress and workload.", 'js-support-ticket'); ?>
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                    <div class="bg-white/20 p-4 rounded-lg flex items-center gap-4 flex-1 min-w-[200px]">
                          <div class="bg-white/20 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white/80"><?php echo esc_html__('Created Tickets', 'js-support-ticket'); ?></p>
                            <p class="text-2xl font-bold"><?php echo esc_html(jssupportticket::$jsst_data['tickets_created_today']); ?></p>
                        </div>
                    </div>
                    <div class="bg-white/20 p-4 rounded-lg flex items-center gap-4 flex-1 min-w-[200px]">
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white/80"><?php echo esc_html__('Closed Tickets', 'js-support-ticket'); ?></p>
                            <p class="text-2xl font-bold"><?php echo esc_html(jssupportticket::$jsst_data['tickets_closed_today']); ?></p>
                        </div>
                    </div>
                </div>
            </section>
            
            <?php if (get_option('jsst_show_key_expiry_msg') == '1') { ?>
                <section id="activation-key-expiry" class="bg-rose-100 border-l-4 rtl:border-r-4 rtl:border-l-0 border-rose-500 text-rose-700 p-6 rounded-xl shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-rose-500">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-rose-800"><?php echo esc_html__('Your activation key has expired.', 'js-support-ticket'); ?></h2>
                            <p class="text-rose-700 text-sm mt-1">
                                <?php echo esc_html__('Please reactivate your key to continue receiving product updates, addons, and support.', 'js-support-ticket'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-full sm:w-auto">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=premiumplugin&jstlay=updatekey')); ?>" class="w-full sm:w-auto bg-rose-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-rose-600 transition-colors whitespace-nowrap inline-block text-center hover:text-white">
                            <?php echo esc_html__('Reactivate Activation Key', 'js-support-ticket'); ?>
                        </a>
                    </div>
                </section>
                <?php
            } ?>

            <section class="flex flex-col md:flex-row justify-between items-center gap-6 flex-wrap">
                <div id="ticket-analysis" class="w-full bg-white p-5 rounded-xl shadow-lg flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Ticket Analysis (Last 7 Days)', 'js-support-ticket'); ?></h3>
                    <div class="h-96">
                        <canvas id="ticketAnalysisChart"></canvas>
                    </div>
                </div>
                <div id="today-ticket-distribution" class="w-full bg-white p-5 rounded-xl shadow-lg flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__("Today's Ticket Distribution", 'js-support-ticket'); ?></h3>
                    <div class="h-96">
                        <canvas id="todayTicketDistributionChart"></canvas>
                    </div>
                </div>
            </section>

            <section id="recent-tickets" class="bg-white p-5 rounded-xl shadow-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Recent Tickets', 'js-support-ticket'); ?></h3>
                <?php if (!empty(jssupportticket::$jsst_data['latest_tickets'])) { ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach (jssupportticket::$jsst_data['latest_tickets'] as $jsst_ticket) { ?>
                            <div class="bg-white p-5 rounded-xl shadow-lg space-y-4 border border-gray-200 hover:shadow-xl transition-shadow duration-300 group">
                                <div class="jsst-admin-dashboard-ticket-top-wrp flex items-center justify-between gap-4">
                                    <div class="jsst-admin-dashboard-ticket-logowrp">
                                        <?php echo wp_kses_post(jsst_get_avatar($jsst_ticket->uid, 'h-10 w-10 rounded-full')); ?>
                                    </div>
                                    <div class="flex flex-auto flex-wrap items-center gap-4 jsst-admin-dashboard-ticket-rightmain-wrp">
                                        <div class="!ml-0 !mr-0 flex-auto jsst-admin-dashboard-ticket-middle-wrp">
                                            <p class="font-semibold text-sm text-gray-800"><?php echo esc_html($jsst_ticket->name); ?></p>
                                            <p class="text-xs text-gray-500">
                                                <?php
                                                $jsst_created_timestamp = jssupportticketphplib::JSST_strtotime( $jsst_ticket->created );
                                                echo ( ! empty( $jsst_created_timestamp ) && $jsst_created_timestamp > 0 )
                                                    ? esc_html( human_time_diff( $jsst_created_timestamp, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'js-support-ticket' ) )
                                                    : '';
                                                ?>
                                        </div>
                                        <span class="bg-indigo-100 text-[#4f46e5] px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statusbgcolour); ?>; color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statuscolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->status)); ?></span>
                                    </div>
                                </div>
                                <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($jsst_ticket->id); ?>" class="inline-block font-semibold text-gray-800 text-sm leading-6 group-hover:text-[#4f46e5] smooth-color-transition"><?php echo esc_html($jsst_ticket->subject); ?></a>
                                <div class="flex items-center justify-between text-sm pt-2 border-t border-gray-100">
                                    <p class="text-xs text-gray-500"><?php echo esc_html__('Priority:', 'js-support-ticket'); ?></p>
                                    <?php if (!empty($jsst_ticket->priority)) { ?>
                                        <span class="bg-red-100 text-red-800 px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>; color: #FFF;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="no-data-container py-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/></svg>
                        <p class="font-semibold"><?php echo esc_html__('No Recent Tickets', 'js-support-ticket'); ?></p>
                        <p class="text-sm"><?php echo esc_html__('New tickets will be displayed in this section.', 'js-support-ticket'); ?></p>
                    </div>
                <?php } ?>
                <a href="?page=ticket" class="flex flex-col text-center w-full mt-4 bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors"><?php echo esc_html__('View All Tickets', 'js-support-ticket'); ?></a>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 layout-grid">
                <?php if( in_array('agent',jssupportticket::$_active_addons) ){ ?>
                    <div id="unassigned-tickets" class="bg-white p-5 rounded-xl shadow-lg flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Unassigned Tickets', 'js-support-ticket'); ?></h3>
                        <?php if (!empty(jssupportticket::$jsst_data['unassigned_tickets'])) { ?>
                            <div class="space-y-4 flex-grow">
                                <?php foreach (jssupportticket::$jsst_data['unassigned_tickets'] as $jsst_ticket) { ?>
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 space-y-3 group">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <div class="jsst-admin-dashboard-ticket-top-wrp flex flex-auto items-center gap-4">
                                                <div class="jsst-admin-dashboard-ticket-logowrp h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center font-bold text-amber-600">
                                                    <?php echo wp_kses_post(jsst_get_avatar($jsst_ticket->uid, 'h-10 w-10 rounded-full')); ?>
                                                </div>
                                                <div class="flex flex-auto flex-wrap items-center gap-4 jsst-admin-dashboard-ticket-rightmain-wrp">
                                                    <div class="!ml-0 !mr-0 flex-auto jsst-admin-dashboard-ticket-middle-wrp">
                                                            <p class="font-semibold text-sm text-gray-800">
                                                                <?php echo esc_html($jsst_ticket->name); ?>
                                                            </p>
                                                            <p class="text-xs text-gray-500">
                                                                <?php echo esc_html( human_time_diff( jssupportticketphplib::JSST_strtotime( $jsst_ticket->created ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'js-support-ticket' ) ); ?>
                                                            </p>
                                                    </div>
                                                    <span class="bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statusbgcolour); ?>; color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statuscolour); ?>;">
                                                        <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->status)); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($jsst_ticket->id); ?>" class="inline-block font-semibold text-gray-800 text-sm leading-6 group-hover:text-[#4f46e5] smooth-color-transition"><?php echo esc_html($jsst_ticket->subject); ?></a>
                                        <div class="flex items-center justify-between text-sm pt-2 border-t border-gray-100">
                                            <p class="text-xs text-gray-500"><?php echo esc_html__('Priority:', 'js-support-ticket'); ?></p>
                                            <?php if (!empty($jsst_ticket->priority)) { ?>
                                                <span class="bg-red-100 text-red-800 px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>; color: #FFF;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="flex-grow no-data-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="M14 9a2 2 0 0 1-2-2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2"/><path d="M18 9a2 2 0 0 1-2-2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2"/><path d="M12 9a2 2 0 0 1-2-2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2"/><path d="M6 9a2 2 0 0 1-2-2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2"/><circle cx="12" cy="12" r="10"/></svg>
                                <p class="font-semibold"><?php echo esc_html__('All Tickets Assigned', 'js-support-ticket'); ?></p>
                                <p class="text-sm"><?php echo esc_html__('Great job! There are no unassigned tickets.', 'js-support-ticket'); ?></p>
                            </div>
                        <?php } ?>
                        <a href="?page=ticket" class="flex flex-col text-center w-full mt-4 bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors"><?php echo esc_html__('View All Tickets', 'js-support-ticket'); ?></a>
                    </div>
                <?php } ?>
                <?php if( in_array('actions',jssupportticket::$_active_addons) ){ ?>
                    <div id="ticket-action-history" class="bg-white p-5 rounded-xl shadow-lg flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Ticket Action History', 'js-support-ticket'); ?></h3>
                        <?php if (!empty(jssupportticket::$jsst_data['ticket_action_history'])) { ?>
                            <div class="space-y-6 flex-grow">
                                <?php 
                                // Define the three icons you want to rotate through
                                $jsst_icons = [
                                    [
                                        'bg' => 'bg-indigo-100',
                                        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-[#4f46e5]"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>'
                                    ],
                                    [
                                        'bg' => 'bg-green-100',
                                        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-green-600"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>'
                                    ],
                                    [
                                        'bg' => 'bg-red-100',
                                        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-500"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><line x1="8" y1="9" x2="16" y2="9"></line><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="12" y2="17"></line></svg>'
                                    ]
                                ];

                                foreach (jssupportticket::$jsst_data['ticket_action_history'] as $jsst_index => $jsst_action) {
                                    $jsst_icon = $jsst_icons[$jsst_index % count($jsst_icons)]; // rotate icons
                                ?>
                                    <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($jsst_action->referenceid); ?>" class="flex items-start space-x-4 gap-4">
                                        <div class="<?php echo esc_attr($jsst_icon['bg']); ?> bg-indigo-100 p-3 rounded-full">
                                            <?php echo wp_kses($jsst_icon['svg'], JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="!ml-0 !mr-0">
                                            <p class="text-sm text-gray-800">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_action->message)); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <?php
                                                if (!empty($jsst_action->name)) {
                                                    echo esc_html(__('By', 'js-support-ticket')) . ' ' . esc_html($jsst_action->name) . ' - ';
                                                }
                                                echo esc_html(human_time_diff(jssupportticketphplib::JSST_strtotime($jsst_action->datetime), current_time('timestamp'))) . ' ' . esc_html(__('ago', 'js-support-ticket'));?>
                                            </p>
                                        </div>
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="flex-grow no-data-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="M12 20v-6M12 8V2"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="M2 12h6"/><path d="M16 12h6"/><path d="m4.93 19.07 4.24-4.24"/><path d="m14.83 9.17 4.24-4.24"/></svg>
                                <p class="font-semibold"><?php echo esc_html__('No Recent Actions', 'js-support-ticket'); ?></p>
                                <p class="text-sm"><?php echo esc_html__('Ticket updates and changes will appear here.', 'js-support-ticket'); ?></p>
                            </div>
                        <?php } ?>
                        <a href="?page=ticket" class="flex flex-col text-center w-full mt-4 bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors"><?php echo esc_html__('View All Tickets', 'js-support-ticket'); ?></a>
                    </div>
                <?php } ?>
                <div id="recently-replied" class="bg-white p-5 rounded-xl shadow-lg flex flex-col">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Recently Replied', 'js-support-ticket'); ?></h3>
                    <?php if (!empty(jssupportticket::$jsst_data['recently_replied_tickets'])) { ?>
                        <div class="space-y-4 flex-grow">
                            <?php foreach (jssupportticket::$jsst_data['recently_replied_tickets'] as $jsst_ticket) { ?>
                                <div class="bg-white p-4 rounded-lg border border-gray-200 space-y-3 group">
                                    <div class="jsst-admin-dashboard-ticket-top-wrp flex flex-auto items-center gap-4">
                                        <div class="jsst-admin-dashboard-ticket-logowrp h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center font-bold text-amber-600">   
                                            <?php echo wp_kses_post(jsst_get_avatar($jsst_ticket->uid, 'h-10 w-10 rounded-full')); ?>
                                        </div>
                                        <div class="flex flex-auto flex-wrap items-center gap-4 jsst-admin-dashboard-ticket-rightmain-wrp">
                                            <div class="!ml-0 !mr-0 jsst-admin-dashboard-ticket-middle-wrp">
                                                <p class="font-semibold text-sm text-gray-800"><?php echo esc_html($jsst_ticket->name); ?></p>
                                                <p class="text-xs text-gray-500">
                                                    <?php
                                                    $jsst_created_timestamp = jssupportticketphplib::JSST_strtotime( $jsst_ticket->created );

                                                    if ( ! empty( $jsst_created_timestamp ) && $jsst_created_timestamp > 0 ) {
                                                        echo esc_html( human_time_diff( $jsst_created_timestamp, current_time( 'timestamp' ) ) ) . ' ' . esc_html(__( 'ago', 'js-support-ticket' ));
                                                    } else {
                                                        echo '';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                            <span class="bg-green-100 text-green-800 px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statusbgcolour); ?>; color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statuscolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->status)); ?></span>
                                        </div>
                                    </div>
                                    <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($jsst_ticket->id); ?>" class="inline-block font-semibold text-gray-800 text-sm leading-6 group-hover:text-[#4f46e5] smooth-color-transition"><?php echo esc_html($jsst_ticket->subject); ?></a>
                                    <div class="flex items-center justify-between text-sm pt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500"><?php echo esc_html__('Priority:', 'js-support-ticket'); ?></p>
                                        <?php if (!empty($jsst_ticket->priority)) { ?>
                                            <span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>; color: #FFF;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="flex-grow no-data-container">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                            <p class="font-semibold"><?php echo esc_html__('No Replies Yet', 'js-support-ticket'); ?></p>
                            <p class="text-sm"><?php echo esc_html__('Recently replied tickets will be shown here.', 'js-support-ticket'); ?></p>
                        </div>
                    <?php } ?>
                    <a href="?page=ticket" data-tab-number="2" class="js-hlpdsk-like-card flex flex-col text-center w-full mt-4 bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors"><?php echo esc_html__('View All Replied Tickets', 'js-support-ticket'); ?></a>
                </div>

                <div id="recently-closed" class="bg-white p-5 rounded-xl shadow-lg flex flex-col">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Recently Closed', 'js-support-ticket'); ?></h3>
                    <?php if (!empty(jssupportticket::$jsst_data['recently_closed_tickets'])) { ?>
                        <div class="space-y-4 flex-grow">
                            <?php foreach (jssupportticket::$jsst_data['recently_closed_tickets'] as $jsst_ticket) { ?>
                                <div class="bg-white p-4 rounded-lg border border-gray-200 space-y-3 group">
                                    <div class="jsst-admin-dashboard-ticket-top-wrp flex flex-auto items-center gap-4">
                                        <div class="jsst-admin-dashboard-ticket-logowrp h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center font-bold text-amber-600">
                                            <?php echo wp_kses_post(jsst_get_avatar($jsst_ticket->uid, 'h-10 w-10 rounded-full')); ?>
                                        </div>
                                        <div class="flex flex-auto flex-wrap items-center gap-4 jsst-admin-dashboard-ticket-rightmain-wrp">
                                            <div class="!ml-0 !mr-0 flex-auto jsst-admin-dashboard-ticket-middle-wrp">
                                                <p class="font-semibold text-sm text-gray-800"><?php echo esc_html($jsst_ticket->name); ?></p>
                                                <p class="text-xs text-gray-500">
                                                    <?php
                                                    $jsst_created_timestamp = jssupportticketphplib::JSST_strtotime( $jsst_ticket->created );

                                                    if ( ! empty( $jsst_created_timestamp ) && $jsst_created_timestamp > 0 ) {
                                                        echo esc_html( human_time_diff( $jsst_created_timestamp, current_time( 'timestamp' ) ) ) . ' ' . esc_html(__( 'ago', 'js-support-ticket' ));
                                                    } else {
                                                        echo '';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                            <span class="bg-gray-200 text-gray-800 px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statusbgcolour); ?>; color:<?php echo esc_attr(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->statuscolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data['status_map'][$jsst_ticket->status]->status)); ?></span>
                                        </div>
                                    </div>
                                    <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($jsst_ticket->id); ?>" class="inline-block font-semibold text-gray-800 text-sm leading-6 group-hover:text-[#4f46e5] smooth-color-transition"><?php echo esc_html($jsst_ticket->subject); ?></a>
                                    <div class="flex items-center justify-between text-sm pt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500"><?php echo esc_html__('Priority:', 'js-support-ticket'); ?></p>
                                        <?php if (!empty($jsst_ticket->priority)) { ?>
                                            <span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full font-medium text-xs" style="background-color:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>; color: #FFF;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="flex-grow no-data-container">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="M15 21h-9a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3l2-3h4l2 3h3a2 2 0 0 1 2 2v7.5"/><path d="M18 15.5V13a2 2 0 0 0-2-2h-2"/><path d="M20 19.5a2.5 2.5 0 0 1-5 0"/><path d="m17.5 17-2.5 5"/><path d="m20 22-2.5-5"/></svg>
                            <p class="font-semibold"><?php echo esc_html__('No Closed Tickets', 'js-support-ticket'); ?></p>
                            <p class="text-sm"><?php echo esc_html__('Recently closed tickets will be listed here.', 'js-support-ticket'); ?></p>
                        </div>
                    <?php } ?>
                    <a href="?page=ticket" data-tab-number="4" class="js-hlpdsk-like-card flex flex-col text-center w-full mt-4 bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors"><?php echo esc_html__('View All Closed Tickets', 'js-support-ticket'); ?></a>
                </div>
                <div id="tickets-by-priority" class="bg-white p-5 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Tickets by Priority', 'js-support-ticket'); ?></h3>
                    <div class="h-80">
                        <canvas id="ticketsByPriorityChart"></canvas>
                    </div>
                </div>
                <div id="tickets-by-department" class="bg-white p-5 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Tickets by Department', 'js-support-ticket'); ?></h3>
                    <div class="h-80">
                        <canvas id="ticketsByDepartmentChart"></canvas>
                    </div>
                </div>
                <?php if( in_array('agent',jssupportticket::$_active_addons)){ ?>
                    </section>
            
                    <section id="staff-performance">
                        <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Agent Performance', 'js-support-ticket'); ?></h3>
                        <div class="bg-white p-5 rounded-xl shadow-lg overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500" id="jsst-import-data-result-table">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 sm:px-4">
                                            <?php echo esc_html__('Agent', 'js-support-ticket'); ?>
                                        </th>
                                        <th scope="col" class="px-6 py-4 sm:px-4 text-center">
                                            <?php echo esc_html__('Assigned', 'js-support-ticket'); ?>
                                        </th>
                                        <th scope="col" class="px-6 py-4 sm:px-4 text-center">
                                            <?php echo esc_html__('Open', 'js-support-ticket'); ?>
                                        </th>
                                        <th scope="col" class="px-6 py-4 sm:px-4 text-center">
                                            <?php echo esc_html__('Closed', 'js-support-ticket'); ?>
                                        </th>
                                        <th scope="col" class="px-6 py-4 sm:px-4 text-center">
                                            <?php echo esc_html__('Pending', 'js-support-ticket'); ?>
                                        </th>
                                        <th scope="col" class="px-6 py-4 sm:px-4 text-center">
                                            <?php echo esc_html__('Overdue', 'js-support-ticket'); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty(jssupportticket::$jsst_data['agent_workload'])) { ?>
                                        <?php foreach (jssupportticket::$jsst_data['agent_workload'] as $jsst_agent) { ?>
                                            <tr class="bg-white border hover:bg-gray-50 group transition-colors duration-300">
                                                <td scope="row" class="before:!content-none px-6 py-4 sm:px-2 font-medium text-gray-900 whitespace-nowrap">
                                                    <div class="flex items-center space-x-3 flex-wrap gap-4">
                                                        <?php echo wp_kses_post(jsst_get_avatar($jsst_agent->uid, 'h-10 w-10 rounded-full')); ?>
                                                        <span href="?page=agent&jstlay=addstaff&jssupportticketid=<?php echo esc_attr($jsst_agent->id); ?>" class="!ml-0 !mr0 smooth-color-transition group-hover:text-[#4f46e5]"><?php echo esc_html($jsst_agent->agent_name); ?></a>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 sm:px-4 text-center">
                                                    <?php echo esc_html($jsst_agent->total_tickets); ?>
                                                </td>
                                                <td class="px-6 py-4 sm:px-4 text-center">
                                                    <?php echo esc_html($jsst_agent->open_tickets); ?>
                                                </td>
                                                <td class="px-6 py-4 sm:px-4 text-center">
                                                    <?php echo esc_html($jsst_agent->closed); ?>
                                                </td>
                                                <td class="px-6 py-4 sm:px-4 text-center">
                                                    <?php echo esc_html($jsst_agent->pending); ?>
                                                </td>
                                                <td class="px-6 py-4 sm:px-4 text-red-500 font-semibold text-center">
                                                    <?php echo esc_html($jsst_agent->overdue); ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-10">
                                                <div class="no-data-container">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                    <p class="font-semibold"><?php echo esc_html__('No Agent Performance Data', 'js-support-ticket'); ?></p>
                                                    <p class="text-sm"><?php echo esc_html__('Performance metrics will be displayed here.', 'js-support-ticket'); ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 layout-grid">
                    <?php
                } ?>
                <?php
                if (in_array('cannedresponses', jssupportticket::$_active_addons) ) { ?>
                    <div id="canned-responses" class="bg-white p-5 rounded-xl shadow-lg">
                        <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Recent Canned Responses', 'js-support-ticket'); ?></h3>
                        <?php if (!empty(jssupportticket::$jsst_data['saved_replies'])) { ?>
                            <div class="space-y-6">
                                <?php 
                                // Define a set of 5 unique icons
                                $jsst_icons = [
                                    // Link/chain style
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-[#4f46e5]"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.72"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.72-1.72"></path></svg>',
                                    
                                    // Message bubble
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-green-600"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',

                                    // Document/file
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-red-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>',

                                    // Tag/bookmark
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-yellow-500"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>',

                                    // Star (favorite reply)
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-500"><polygon points="12 2 15 8.5 22 9.3 17 14 18.5 21 12 17.8 5.5 21 7 14 2 9.3 9 8.5 12 2"></polygon></svg>'
                                ];

                                foreach (jssupportticket::$jsst_data['saved_replies'] as $jsst_index => $jsst_reply) {
                                    $jsst_icon = $jsst_icons[$jsst_index % count($jsst_icons)]; // pick unique icon
                                ?>
                                    <div class="space-y-3">
                                        <a href="?page=cannedresponses&jstlay=addpremademessage&jssupportticketid=<?php echo esc_attr($jsst_reply->id); ?>" class="flex justify-between items-center text-sm">
                                            <div class="flex items-center space-x-2">
                                                <?php echo wp_kses( $jsst_icon, JSST_ALLOWED_TAGS ); ?>
                                                <p class="font-semibold text-gray-700"><?php echo esc_html($jsst_reply->title); ?></p>
                                            </div>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="no-data-container h-full">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                                <p class="font-semibold"><?php echo esc_html__('No Canned Responses', 'js-support-ticket'); ?></p>
                                <p class="text-sm"><?php echo esc_html__('Recent canned responses will be listed here.', 'js-support-ticket'); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                }
                if( in_array('timetracking',jssupportticket::$_active_addons) ) { ?>
                    <div id="active-timers" class="bg-white p-5 rounded-xl shadow-lg">
                        <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Active Timers', 'js-support-ticket'); ?></h3>
                        <?php if (!empty(jssupportticket::$jsst_data['active_timers'])) { ?>
                            <div class="space-y-4">
                                <?php foreach (jssupportticket::$jsst_data['active_timers'] as $jsst_timer) { 
                                    $jsst_time_diff = $jsst_timer->usertime;

                                    $jsst_hours = floor($jsst_time_diff / 3600);
                                    $jsst_minutes = floor($jsst_time_diff / 60);
                                    $jsst_seconds = $jsst_time_diff % 60;
                                ?>
                                    <div class="flex justify-between items-center p-4 border rounded-lg">
                                        <div>
                                            <a href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($jsst_timer->id); ?>" class="inline-block font-semibold text-gray-800 hover:text-[#4f46e5]"><?php echo esc_html($jsst_timer->subject); ?></a>
                                            <p class="text-sm text-gray-500"><?php echo esc_html($jsst_timer->name); ?></p>
                                        </div>
                                        <p class="text-lg font-bold text-green-600 bg-green-100 px-3 py-1 rounded-md">
                                            <?php
                                                echo esc_html($jsst_hours).':'.esc_html($jsst_minutes).':'.esc_html($jsst_seconds);
                                            ?>
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="no-data-container h-full">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <p class="font-semibold"><?php echo esc_html(__('No Active Timers', 'js-support-ticket')); ?></p>
                                <p class="text-sm"><?php echo esc_html(__('Timers started by agent will be shown here.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                } ?>
                <div id="open-tickets-by-age" class="bg-white p-5 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Open Tickets by Age', 'js-support-ticket'); ?></h3>
                    <div class="h-80">
                        <canvas id="openTicketsByAgeChart"></canvas>
                    </div>
                </div>
                <div id="most-active-customers" class="bg-white p-5 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Most Active Customers', 'js-support-ticket'); ?></h3>
                    <?php if (!empty(jssupportticket::$jsst_data['most_active_customers'])) { ?>
                        <div class="space-y-2">
                            <?php foreach (jssupportticket::$jsst_data['most_active_customers'] as $jsst_customer) { ?>
                                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 group transition-colors duration-300">
                                    <div class="flex items-center space-x-3 gap-4">
                                        <?php echo wp_kses_post(jsst_get_avatar($jsst_customer->uid, 'h-10 w-10 rounded-full')); ?>
                                        <p class="!ml-0 !mr-0 font-semibold text-sm text-gray-800 smooth-color-transition group-hover:text-[#4f46e5]">
                                            <?php echo esc_html($jsst_customer->name); ?>
                                        </p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-600">
                                        <?php echo esc_html($jsst_customer->ticket_count).' '.esc_html(__('Tickets', 'js-support-ticket')); ?>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="no-data-container h-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 mb-4 text-gray-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <p class="font-semibold"><?php echo esc_html__('No Customer Activity', 'js-support-ticket'); ?></p>
                            <p class="text-sm"><?php echo esc_html__('Customers with the most tickets will be listed here.', 'js-support-ticket'); ?></p>
                        </div>
                    <?php } ?>
                </div>
                <div id="tickets-by-status" class="bg-white p-5 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Tickets by Status', 'js-support-ticket'); ?></h3>
                    <div class="h-80">
                        <canvas id="ticketsByStatusChart"></canvas>
                    </div>
                </div>
                <div id="tickets-by-products" class="bg-white p-5 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Tickets by Products', 'js-support-ticket'); ?></h3>
                    <div class="h-80">
                        <canvas id="ticketsByProductsChart"></canvas>
                    </div>
                </div>
            </section>

            <?php
            // Define the dynamic list of available addons.
            $jsst_available_addons = [
                'agent' => [
                    'title' => __('Agents', 'js-support-ticket'),
                    'description' => __('Add agents and assign roles and permissions to provide assistance.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-agent/js-support-ticket-agent.php',
                    'url' => 'https://jshelpdesk.com/product/agents/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/></svg>',
                ],
                'aipoweredreply' => [
                    'title' => __('AI Powered Reply', 'js-support-ticket'),
                    'description' => __('Get AI-powered, context-based suggestions to effortlessly create clear, relevant, and helpful replies.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-aipoweredreply/js-support-ticket-aipoweredreply.php',
                    'url' => 'https://jshelpdesk.com/product/ai-powered-reply/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <!-- Chat bubble -->
                        <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M4 5h16v10H7l-3 3V5z"/>
                        <!-- AI text -->
                        <text x="12" y="12" font-size="7" text-anchor="middle" 
                            fill="currentColor" font-weight="bold">AI</text>
                    </svg>',
                ],
                'autoclose' => [
                    'title' => __('Ticket Auto Close', 'js-support-ticket'),
                    'description' => __('Define rules for the ticket to auto-close after a specific interval of time.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-autoclose/js-support-ticket-autoclose.php',
                    'url' => 'https://jshelpdesk.com/product/close-ticket/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                ],
                'feedback' => [
                    'title' => __('Feedbacks', 'js-support-ticket'),
                    'description' => __('Get a survey from customers on ticket closing to improve quality.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-feedback/js-support-ticket-feedback.php',
                    'url' => 'https://jshelpdesk.com/product/feedback/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
                ],
                'helptopic' => [
                    'title' => __('Help Topics', 'js-support-ticket'),
                    'description' => __('Help users to find and select the area with which they need assistance.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-helptopic/js-support-ticket-helptopic.php',
                    'url' => 'https://jshelpdesk.com/product/helptopic/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                ],
                'note' => [
                    'title' => __('Private Note', 'js-support-ticket'),
                    'description' => __('The private note is used as reminders or to give other agents insights into the ticket issue.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-note/js-support-ticket-note.php',
                    'url' => 'https://jshelpdesk.com/product/internal-note/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a5.5 5.5 0 017.778 7.778L13.828 17.828a5.5 5.5 0 01-7.778-7.778l2.036-2.036m3.536 3.536L10 14m0 0l-1-1m-1-1l-3 3" /></svg>',
                ],
                'knowledgebase' => [
                    'title' => __('Knowledge Base', 'js-support-ticket'),
                    'description' => __('Stop losing productivity on repetitive queries, build your knowledge base.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-knowledgebase/js-support-ticket-knowledgebase.php',
                    'url' => 'https://jshelpdesk.com/product/knowledge-base/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                ],
                'maxticket' => [
                    'title' => __('Max Tickets', 'js-support-ticket'),
                    'description' => __('Enables admin to set N numbers of tickets for users and agents separately.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-maxticket/js-support-ticket-maxticket.php',
                    'url' => 'https://jshelpdesk.com/product/max-ticket/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V9m6 10V5m6 14V3M3 21h18" /></svg>',
                ],
                'mergeticket' => [
                    'title' => __('Merge Tickets', 'js-support-ticket'),
                    'description' => __('Enables agents to merge two tickets of the same user into one instead of dealing with the same issue on many tickets.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-mergeticket/js-support-ticket-mergeticket.php',
                    'url' => 'https://jshelpdesk.com/product/merge-ticket/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>',
                ],
                'overdue' => [
                    'title' => __('Overdue', 'js-support-ticket'),
                    'description' => __('Defines rules or set specific intervals of time to make ticket auto overdue.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-overdue/js-support-ticket-overdue.php',
                    'url' => 'https://jshelpdesk.com/product/overdue/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                ],
                'smtp' => [
                    'title' => __('SMTP', 'js-support-ticket'),
                    'description' => __('SMTP enables you to add custom mail protocol to send and receive emails.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-smtp/js-support-ticket-smtp.php',
                    'url' => 'https://jshelpdesk.com/product/smtp/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V6a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>',
                ],
                'tickethistory' => [
                    'title' => __('Ticket History', 'js-support-ticket'),
                    'description' => __('Displays complete ticket history along with the ticket status, currently assigned user and other actions performed on each ticket.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-tickethistory/js-support-ticket-tickethistory.php',
                    'url' => 'https://jshelpdesk.com/product/ticket-history/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8M8 11h8M8 15h6" /></svg>',
                ],
                'cannedresponses' => [
                    'title' => __('Canned Responses', 'js-support-ticket'),
                    'description' => __('Pre-populated messages allow support agents to respond quickly.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-cannedresponses/js-support-ticket-cannedresponses.php',
                    'url' => 'https://jshelpdesk.com/product/canned-responses/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                ],
                'emailpiping' => [
                    'title' => __('Email Piping', 'js-support-ticket'),
                    'description' => __('Enables users to reply to the tickets via email without login.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-emailpiping/js-support-ticket-emailpiping.php',
                    'url' => 'https://jshelpdesk.com/product/email-piping/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14v6m0 0l-2-2m2 2l2-2" /></svg>',
                ],
                'timetracking' => [
                    'title' => __('Time Tracking', 'js-support-ticket'),
                    'description' => __('Track the time spent on each ticket by each agent and each reply.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-timetracking/js-support-ticket-timetracking.php',
                    'url' => 'https://jshelpdesk.com/product/time-tracking/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                ],
                'useroptions' => [
                    'title' => __('User Options', 'js-support-ticket'),
                    'description' => __('User options enable you to add Google Re-captcha or JS Help Desk Re-captcha for a registration form.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-useroptions/js-support-ticket-useroptions.php',
                    'url' => 'https://jshelpdesk.com/product/user-options/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
                ],
                'actions' => [
                    'title' => __('Ticket Actions', 'js-support-ticket'),
                    'description' => __('Get multiple action options on each ticket like Print Ticket, Lock Ticket, Transfer ticket, etc.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-actions/js-support-ticket-actions.php',
                    'url' => 'https://jshelpdesk.com/product/actions/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.405 9.172 5 7.5 5S4.168 5.405 3 6.253v13C4.168 18.405 5.828 18 7.5 18s3.332.405 4.5 1.253m0-13C13.168 5.405 14.828 5 16.5 5s3.332.405 4.5 1.253v13C19.832 18.405 18.172 18 16.5 18s-3.332.405-4.5 1.253" /></svg>',
                ],
                'announcement' => [
                    'title' => __('Announcements', 'js-support-ticket'),
                    'description' => __('Make unlimited announcements associated with the support system.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-announcement/js-support-ticket-announcement.php',
                    'url' => 'https://jshelpdesk.com/product/announcements/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 8.5V5a2 2 0 00-2-2l-7 4H3v6h3l7 4a2 2 0 002-2v-3.5M19 10v4m0-4a2 2 0 010 4" /></svg>',
                ],
                'banemail' => [
                    'title' => __('Ban Emails', 'js-support-ticket'),
                    'description' => __('It allows you to block the email of any user to restrict him to create new tickets.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-banemail/js-support-ticket-banemail.php',
                    'url' => 'https://jshelpdesk.com/product/ban-email/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8" /><circle cx="18" cy="18" r="4" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 16.5L19.5 19.5" /></svg>',
                ],
                'notification' => [
                    'title' => __('Desktop Notification', 'js-support-ticket'),
                    'description' => __('The Desktop notifications will keep you up to date about anything happens on your support system.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-notification/js-support-ticket-notification.php',
                    'url' => 'https://jshelpdesk.com/product/desktop-notification/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
                ],
                'export' => [
                    'title' => __('Export', 'js-support-ticket'),
                    'description' => __('Save the ticket as a PDF in your system and able to export all data.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-export/js-support-ticket-export.php',
                    'url' => 'https://jshelpdesk.com/product/export/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>',
                ],
                'download' => [
                    'title' => __('Downloads', 'js-support-ticket'),
                    'description' => __('Create downloads to ensure the user to get downloads from downloads.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-download/js-support-ticket-download.php',
                    'url' => 'https://jshelpdesk.com/product/downloads/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h5l2 2h11a1 1 0 011 1v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3" /></svg>',
                ],
                'faq' => [
                    'title' => __("FAQ's", 'js-support-ticket'),
                    'description' => __('Add FAQs to drastically reduce the number of common questions.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-faq/js-support-ticket-faq.php',
                    'url' => 'https://jshelpdesk.com/product/faq/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10a2 2 0 012 2v7a2 2 0 01-2 2H9l-4 3v-3H5a2 2 0 01-2-2v-7a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 11.5a1.5 1.5 0 113 0c0 .75-.5 1.5-1.5 1.5v1m0 2h.01" /></svg>',
                ],
                'dashboardwidgets' => [
                    'title' => __('Dashboard Widgets', 'js-support-ticket'),
                    'description' => __('Get immediate data of your support operations as soon as you log into your WordPress administration area.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-dashboardwidgets/js-support-ticket-dashboardwidgets.php',
                    'url' => 'https://jshelpdesk.com/product/admin-widget/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>',
                ],
                'mail' => [
                    'title' => __('Internal Mail', 'js-support-ticket'),
                    'description' => __('Use an internal email to send emails to one agent to another agent.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-mail/js-support-ticket-mail.php',
                    'url' => 'https://jshelpdesk.com/product/internal-mail/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12h4m0 0l-2-2m2 2l-2 2" /></svg>',
                ],
                'widgets' => [
                    'title' => __('Front-End Widgets', 'js-support-ticket'),
                    'description' => __('Widgets in WordPress allow you to add content and features in the widgetized areas of your theme.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-widgets/js-support-ticket-widgets.php',
                    'url' => 'https://jshelpdesk.com/product/widget/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></svg>',
                ],
                'woocommerce' => [
                    'title' => __('WooCommerce', 'js-support-ticket'),
                    'description' => __('JS Help Desk WooCommerce provides the much-needed bridge between your WooCommerce store and the JS Help Desk.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-woocommerce/js-support-ticket-woocommerce.php',
                    'url' => 'https://jshelpdesk.com/product/woocommerce/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" /><path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" /></svg>',
                ],
                'privatecredentials' => [
                    'title' => __('Private Credentials', 'js-support-ticket'),
                    'description' => __('Collect your customer\'s private data, sensitive information from credit card to health information and store them encrypted.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-privatecredentials/js-support-ticket-privatecredentials.php',
                    'url' => 'https://jshelpdesk.com/product/private-credentials/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c-1.657 0-3 1.343-3 3v4h6v-4c0-1.657-1.343-3-3-3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a5 5 0 0110 0v4" /><rect x="5" y="11" width="14" height="10" rx="2" ry="2" /></svg>',
                ],
                'envatovalidation' => [
                    'title' => __('Envato Validation', 'js-support-ticket'),
                    'description' => __('Without valid Envato, license clients won\'t be able to open a new ticket.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-envatovalidation/js-support-ticket-envatovalidation.php',
                    'url' => 'https://jshelpdesk.com/product/envato/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" /></svg>',
                ],
                'mailchimp' => [
                    'title' => __('Mailchimp', 'js-support-ticket'),
                    'description' => __('Adds the option to the registration form for prompting new users to subscribe to your email list.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-mailchimp/js-support-ticket-mailchimp.php',
                    'url' => 'https://jshelpdesk.com/product/mail-chimp/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l-2-2m12 2l2-2" /></svg>',
                ],
                'paidsupport' => [
                    'title' => __('Paid Support', 'js-support-ticket'),
                    'description' => __('Paid Support is the easiest way to integrate and manage payments for your tickets.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-paidsupport/js-support-ticket-paidsupport.php',
                    'url' => 'https://jshelpdesk.com/product/paid-support/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0c1.5 0 2.5-.5 2.5-1.5S13.5 13 12 13m0 3c-1.5 0-2.5-.5-2.5-1.5S10.5 13 12 13" /></svg>',
                ],
                'easydigitaldownloads' => [
                    'title' => __('Easy Digital Downloads', 'js-support-ticket'),
                    'description' => __('EDD offers customers to open new tickets just one click from their EDD account.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-easydigitaldownloads/js-support-ticket-easydigitaldownloads.php',
                    'url' => 'https://jshelpdesk.com/product/easy-digital-download/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-red',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12v6m0 0l-3-3m3 3l3-3m4-2V7a2 2 0 00-2-2h-4l-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2h8" /></svg>',
                ],
                'multilanguageemailtemplates' => [
                    'title' => __('Multi Language Email Templates', 'js-support-ticket'),
                    'description' => __('It allows to create language-based email templates for all JS Help Desk email templates.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-multilanguageemailtemplates/js-support-ticket-multilanguageemailtemplates.php',
                    'url' => 'https://jshelpdesk.com/product/multi-language-email-templates',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-orange',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 4h4v3H7V4zm6 0h4v3h-4V4z" /></svg>',
                ],
                'emailcc' => [
                    'title' => __('Email Cc', 'js-support-ticket'),
                    'description' => __('CC(Carbon Copy) - the people who should know about the information which is being shared and the people included are able to see who is there in the list.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-emailcc/js-support-ticket-emailcc.php',
                    'url' => 'https://jshelpdesk.com/product/emailcc/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-blue',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /><circle cx="7" cy="19" r="2" /><circle cx="17" cy="19" r="2" /></svg>',
                ],
                'multiform' => [
                    'title' => __('Multiform', 'js-support-ticket'),
                    'description' => __('It allows user to add more than one form based on requirements.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-multiform/js-support-ticket-multiform.php',
                    'url' => 'https://jshelpdesk.com/product/multi-forms/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-pink',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="12" height="12" rx="2" ry="2"/><rect x="8" y="8" width="12" height="12" rx="2" ry="2"/></svg>',
                ],
                'agentautoassign' => [
                    'title' => __('Agent Auto Assign', 'js-support-ticket'),
                    'description' => __('When a ticket is created, an appropriate agent is automatically assigned to the ticket and it is moved to the Assigned state.', 'js-support-ticket'),
                    'plugin_file' => 'js-support-ticket-agentautoassign/js-support-ticket-agentautoassign.php',
                    'url' => 'https://jshelpdesk.com/product/agentautoassign/',
                    'icon_bg' => 'js-hlpdsk-addon-icon-bg-teal',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3"/><path d="M6 21v-2a6 6 0 0112 0v2"/><path d="M19.4 15a2 2 0 010-6"/><path d="M4.6 9a2 2 0 010 6"/></svg>',
                ],
            ];

            // Filter out active addons to get a list of inactive ones
            $jsst_inactive_addons = array_filter($jsst_available_addons, function($jsst_addon_slug) {
                return !in_array($jsst_addon_slug, jssupportticket::$_active_addons);
            }, ARRAY_FILTER_USE_KEY);

            // Show the section only if there are inactive addons
            if (count($jsst_inactive_addons) > 0) {?>
                <section id="available-addons" class="bg-white p-5 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Available Addons', 'js-support-ticket'); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                        // Show the first 6 inactive addons
                        $jsst_addons_to_display = array_slice($jsst_inactive_addons, 0, 6, true);
                        foreach ($jsst_addons_to_display as $jsst_addon_slug => $jsst_addon) {
                            $jsst_plugininfo = JSSTCheckPluginInfo($jsst_addon['plugin_file']);
                            if($jsst_plugininfo['availability'] == "1"){
                                $jsst_button_text = $jsst_plugininfo['text'];
                                $jsst_button_url = "plugins.php?s=".$jsst_addon_slug."&plugin_status=inactive";
                                $jsst_button_class = "flex flex-col text-center w-full bg-gradient-to-r from-green-500 to-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:from-green-600 hover:to-green-800 transition-colors hover:text-white";
                            }elseif($jsst_plugininfo['availability'] == "0"){
                                $jsst_button_text = isset($jsst_plugininfo['text']) ? $jsst_plugininfo['text'] : __('View Details', 'js-support-ticket');
                                $jsst_button_url = $jsst_addon['url'];
                                $jsst_button_class = "flex flex-col text-center w-full bg-gradient-to-r from-green-500 to-green-700 text-white font-semibold py-2 px-4 rounded-lg hover:from-green-600 hover:to-green-800 transition-colors hover:text-white";
                            } else {
                                // Default to install button
                                $jsst_button_text = __('Install Now', 'js-support-ticket');
                                $jsst_button_url = "#";
                                $jsst_button_class = "flex-1 flex flex-col text-center w-full bg-[#4f46e5] text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors hover:text-white";
                            }
                            ?>
                            <div class="bg-white p-5 rounded-xl shadow-lg flex flex-col items-start space-y-3 border border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gray-100 p-3 rounded-lg">
                                        <?php echo wp_kses( $jsst_addon['icon_svg'] , JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <h4 class="font-bold text-gray-800">
                                        <?php echo esc_html($jsst_addon['title']); ?>
                                    </h4>
                                </div>
                                <p class="text-sm text-gray-600 flex-grow">
                                    <?php echo esc_html($jsst_addon['description']); ?>
                                </p>
                                <a href="<?php echo esc_url($jsst_button_url); ?>" class="<?php echo esc_attr($jsst_button_class); ?>">
                                    <?php echo esc_html($jsst_button_text); ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (count($jsst_inactive_addons) > 6) { ?>
                        <a href="?page=jssupportticket&jstlay=addonstatus" class="flex flex-col text-center w-full mt-4 bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors"><?php echo esc_html__('View All Addons', 'js-support-ticket'); ?></a>
                    <?php } ?>
                </section>
                <?php
            } ?>
            <section id="quick-installation-guide">
                <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo esc_html__('Quick Installation Guide', 'js-support-ticket'); ?></h3>
                <div class="bg-white p-5 rounded-xl shadow-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                        <div class="accordion-item">
                            <button class="accordion-header w-full flex justify-between items-center text-left py-3 px-4 font-semibold text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <span><?php echo esc_html__('How to setup of the Help Desk', 'js-support-ticket'); ?></span>
                                <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="accordion-content">
                                <a target="blank" href="https://www.youtube.com/watch?v=Honmzw892ZE" class="p-4 flex flex-col md:flex-row gap-4 items-center">
                                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/setup.jpg" alt="<?php echo esc_attr__('Video thumbnail', 'js-support-ticket'); ?>" class="rounded-lg shadow-md w-full md:w-1/3" onerror="this.onerror=null;this.src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/setup.jpg'">
                                    <p class="text-sm text-gray-600"><?php echo esc_html__('Learn how to set up your Help Desk quickly and get it ready for use.', 'js-support-ticket'); ?></p>
                                </a>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-header w-full flex justify-between items-center text-left py-3 px-4 font-semibold text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <span><?php echo esc_html__('How to Setup System Emails', 'js-support-ticket'); ?></span>
                                <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="accordion-content">
                                <a target="blank" href="https://www.youtube.com/watch?v=dNYnZw8WK0M" class="p-4 flex flex-col md:flex-row gap-4 items-center">
                                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/system-email.jpg" alt="<?php echo esc_attr__('Video thumbnail', 'js-support-ticket'); ?>" class="rounded-lg shadow-md w-full md:w-1/3" onerror="this.onerror=null;this.src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/system-email.jpg'">
                                    <p class="text-sm text-gray-600"><?php echo esc_html__('Learn how to setup out going notification in the best Help Desk plugin for WordPress', 'js-support-ticket'); ?></p>
                                </a>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-header w-full flex justify-between items-center text-left py-3 px-4 font-semibold text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <span><?php echo esc_html__('How to Create a Ticket', 'js-support-ticket'); ?></span>
                                <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="accordion-content">
                                <a target="blank" href="https://www.youtube.com/watch?v=zmQ4bpqSYnk" class="p-4 flex flex-col md:flex-row gap-4 items-center">
                                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/create-icket.jpg" alt="<?php echo esc_attr__('Video thumbnail', 'js-support-ticket'); ?>" class="rounded-lg shadow-md w-full md:w-1/3" onerror="this.onerror=null;this.src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/create-icket.jpg'">
                                    <p class="text-sm text-gray-600"><?php echo esc_html__('A simple walkthrough of the ticket creation process for both user and administrator.', 'js-support-ticket'); ?></p>
                                </a>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-header w-full flex justify-between items-center text-left py-3 px-4 font-semibold text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <span><?php echo esc_html__('How to Set Email Notifications', 'js-support-ticket'); ?></span>
                                <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="accordion-content">
                                <a target="blank" href="https://www.youtube.com/watch?v=LvsrMtEqRms" class="p-4 flex flex-col md:flex-row gap-4 items-center">
                                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/emial-notifications.jpg" alt="<?php echo esc_attr__('Video thumbnail', 'js-support-ticket'); ?>" class="rounded-lg shadow-md w-full md:w-1/3" onerror="this.onerror=null;this.src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/emial-notifications.jpg'">
                                    <p class="text-sm text-gray-600"><?php echo esc_html__('Configure your email notification settings to stay updated on ticket activity.', 'js-support-ticket'); ?></p>
                                </a>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <button class="accordion-header w-full flex justify-between items-center text-left py-3 px-4 font-semibold text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <span><?php echo esc_html__('How to Setup Custom Fields', 'js-support-ticket'); ?></span>
                                <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="accordion-content">
                                <a target="blank" href="https://www.youtube.com/watch?v=c7whQ6F70yM" class="p-4 flex flex-col md:flex-row gap-4 items-center">
                                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/custom-fields.jpg" alt="<?php echo esc_attr__('Video thumbnail', 'js-support-ticket'); ?>" class="rounded-lg shadow-md w-full md:w-1/3" onerror="this.onerror=null;this.src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/videos/custom-fields.jpg'">
                                    <p class="text-sm text-gray-600"><?php echo esc_html__('Learn how to add custom fields to your ticket forms to collect more specific information.', 'js-support-ticket'); ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            </div>
    </main>

    <div id="side-menu" class="fixed top-4 right-0 h-full w-96 bg-gray-50 shadow-2xl p-8 transform translate-x-full overflow-y-auto">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo esc_html__('Customize Dashboard', 'js-support-ticket'); ?></h2>
            <button id="close-menu" class="text-gray-500 hover:text-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="menu-items" class="space-y-4"></div>
    </div>
<?php
/**
 * =================================================================================
 * PHP DATA & CONSOLIDATED INLINE JAVASCRIPT
 * =================================================================================
 */

$jsst_sections = [];
$jsst_sections[] = [ 'id' => 'ticket-analysis', 'name' => esc_html__('Ticket Analysis (Last 7 Days)', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'today-ticket-distribution', 'name' => esc_html__("Todays Ticket Distribution", 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'recent-tickets', 'name' => esc_html__('Recent Tickets', 'js-support-ticket') ];

// Addon-dependent
if ( in_array('agent', jssupportticket::$_active_addons) ) {
    $jsst_sections[] = [ 'id' => 'unassigned-tickets', 'name' => esc_html__('Unassigned Tickets', 'js-support-ticket') ];
    $jsst_sections[] = [ 'id' => 'staff-performance', 'name' => esc_html__('Agent Performance', 'js-support-ticket') ];
}
if ( in_array('actions', jssupportticket::$_active_addons) ) {
    $jsst_sections[] = [ 'id' => 'ticket-action-history', 'name' => esc_html__('Ticket Action History', 'js-support-ticket') ];
}
if ( in_array('cannedresponses', jssupportticket::$_active_addons) ) {
    $jsst_sections[] = [ 'id' => 'canned-responses', 'name' => esc_html__('Recent Canned Responses', 'js-support-ticket') ];
}
if ( in_array('timetracking', jssupportticket::$_active_addons) ) {
    $jsst_sections[] = [ 'id' => 'active-timers', 'name' => esc_html__('Active Timers', 'js-support-ticket') ];
}

// Always available (rest)
$jsst_sections[] = [ 'id' => 'recently-replied', 'name' => esc_html__('Recently Replied', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'recently-closed', 'name' => esc_html__('Recently Closed', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'tickets-by-priority', 'name' => esc_html__('Tickets by Priority', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'tickets-by-department', 'name' => esc_html__('Tickets by Department', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'open-tickets-by-age', 'name' => esc_html__('Open Tickets by Age', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'most-active-customers', 'name' => esc_html__('Most Active Customers', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'tickets-by-status', 'name' => esc_html__('Tickets by Status', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'tickets-by-products', 'name' => esc_html__('Tickets by Products', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'available-addons', 'name' => esc_html__('Available Addons', 'js-support-ticket') ];
$jsst_sections[] = [ 'id' => 'quick-installation-guide', 'name' => esc_html__('Quick Installation Guide', 'js-support-ticket') ];
$jsst_nonce = wp_create_nonce('jssupportticket_admin_nonce');

$jsst_jssupportticket_inline_js = '
    jQuery(document).ready(function ($) {
        
        // Original jQuery event handlers
        jQuery("span.dashboard-icon").find("span.download").hover(function(){
            jQuery(this).find("span").toggle("slide");
        }, function(){
            jQuery(this).find("span").toggle("slide");
        });
        jQuery("a.js-hlpdsk-like-card").click(function(e){
            e.preventDefault();
            var list = jQuery(this).attr("data-tab-number");
            var oldUrl = jQuery(this).attr("href");
            var newUrl = oldUrl + "&list=" + list;
            window.location.href = newUrl;
        });

        // Chart.js script for Ticket Analysis
        if(document.getElementById("ticketAnalysisChart")){
            const ctx = document.getElementById("ticketAnalysisChart").getContext("2d");
            const labels = ' . wp_json_encode(jssupportticket::$jsst_data['ticket_trends']['dates']) . ';
            const ticketAnalysisChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "' . esc_html__('Open', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['ticket_trends']['new']) . ',
                            backgroundColor: "' . esc_js(jssupportticket::$jsst_data['today_distribution']['colors']['new']) . '",
                        },
                        {
                            label: "' . esc_html__('Pending', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['ticket_trends']['pending']) . ',
                            backgroundColor: "' . esc_js(jssupportticket::$jsst_data['today_distribution']['colors']['pending']) . '",
                        },
                        {
                            label: "' . esc_html__('Answered', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['ticket_trends']['answered']) . ',
                            backgroundColor: "' . esc_js(jssupportticket::$jsst_data['today_distribution']['colors']['answered']) . '",
                        },
                        {
                            label: "' . esc_html__('Closed', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['ticket_trends']['closed']) . ',
                            backgroundColor: "' . esc_js(jssupportticket::$jsst_data['today_distribution']['colors']['closed']) . '",
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: "index", intersect: false },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: { display: true, text: "' . esc_html__('Number of Tickets', 'js-support-ticket') . '" }
                        }
                    },
                    plugins: { legend: { position: "top" }, tooltip: { mode: "index", intersect: false } }
                }
            });
        }

            // Chart.js script for Today\'s Ticket Distribution
            if(document.getElementById("todayTicketDistributionChart")){
                const todayCtx = document.getElementById("todayTicketDistributionChart").getContext("2d");
                const todayTicketDistributionChart = new Chart(todayCtx, {
                    type: "doughnut",
                    data: {
                        labels: ["' . esc_html__('New', 'js-support-ticket') . '", "' . esc_html__('Answered', 'js-support-ticket') . '", "' . esc_html__('Pending', 'js-support-ticket') . '"],
                        datasets: [{
                            label: "' . esc_html__('Today Tickets', 'js-support-ticket') . '",
                            data: [
                                ' . intval(jssupportticket::$jsst_data['today_distribution']['new']) . ',
                                ' . intval(jssupportticket::$jsst_data['today_distribution']['answered']) . ',
                                ' . intval(jssupportticket::$jsst_data['today_distribution']['pending']) . '
                            ],
                            backgroundColor: [
                                "' . esc_js(jssupportticket::$jsst_data['today_distribution']['colors']['new']) . '",
                                "' . esc_js(jssupportticket::$jsst_data['today_distribution']['colors']['answered']) . '",
                                "' . esc_js(jssupportticket::$jsst_data['today_distribution']['colors']['pending']) . '"
                            ],
                            borderColor: "#fff",
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: "70%",
                        plugins: {
                            legend: {
                                position: "bottom",
                                labels: { padding: 20, boxWidth: 12, font: { size: 14 } }
                            }
                        }
                    }
                });
            }

            // Chart.js script for Tickets by Priority
            if(document.getElementById("ticketsByPriorityChart")){
                const priorityCtx = document.getElementById("ticketsByPriorityChart").getContext("2d");
                const ticketsByPriorityChart = new Chart(priorityCtx, {
                    type: "doughnut",
                    data: {
                        labels: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_priorities']['labels']) . ',
                        datasets: [{
                            label: "' . esc_html__('Tickets by Priority', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_priorities']['data']) . ',
                            backgroundColor: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_priorities']['colors']) . ',
                            borderColor: "#fff",
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: "70%",
                        plugins: {
                            legend: {
                                position: "bottom",
                                labels: { padding: 20, boxWidth: 12, font: { size: 14 } }
                            }
                        }
                    }
                });
            }

            // Chart.js script for Tickets by Department
            if(document.getElementById("ticketsByDepartmentChart")){
                const departmentCtx = document.getElementById("ticketsByDepartmentChart").getContext("2d");
                const ticketsByDepartmentChart = new Chart(departmentCtx, {
                    type: "doughnut",
                    data: {
                        labels: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_department']['labels']) . ',
                        datasets: [{
                            label: "' . esc_html__('Tickets by Department', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_department']['data']) . ',
                            backgroundColor: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_department']['colors']) . ',
                            borderColor: "#fff",
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: "70%",
                        plugins: {
                            legend: {
                                position: "bottom",
                                labels: { padding: 20, boxWidth: 12, font: { size: 14 } }
                            }
                        }
                    }
                });
            }

            // Chart.js script for Open Tickets by Age
            if(document.getElementById("openTicketsByAgeChart")){
                const openTicketsCtx = document.getElementById("openTicketsByAgeChart").getContext("2d");
                const openTicketsByAgeChart = new Chart(openTicketsCtx, {
                    type: "bar",
                    data: {
                        labels: [
                            "' . esc_html__('Today', 'js-support-ticket') . '",
                            "' . esc_html__('Yesterday', 'js-support-ticket') . '",
                            "' . esc_html__('2 days ago', 'js-support-ticket') . '",
                            "' . esc_html__('3 days ago', 'js-support-ticket') . '",
                            "' . esc_html__('4 days ago', 'js-support-ticket') . '",
                            "' . esc_html__('5 days ago', 'js-support-ticket') . '",
                            "' . esc_html__('6+ days ago', 'js-support-ticket') . '"
                        ],
                        datasets: [{
                            label: "' . esc_html__('Number of Open Tickets', 'js-support-ticket') . '",
                            data: [
                                ' . (int) jssupportticket::$jsst_data['tickets_by_age']['today'] . ',
                                ' . (int) jssupportticket::$jsst_data['tickets_by_age']['yesterday'] . ',
                                ' . (int) jssupportticket::$jsst_data['tickets_by_age']['two_days'] . ',
                                ' . (int) jssupportticket::$jsst_data['tickets_by_age']['three_days'] . ',
                                ' . (int) jssupportticket::$jsst_data['tickets_by_age']['four_days'] . ',
                                ' . (int) jssupportticket::$jsst_data['tickets_by_age']['five_days'] . ',
                                ' . (int) jssupportticket::$jsst_data['tickets_by_age']['six_plus'] . '
                            ],
                            backgroundColor: "rgba(59, 130, 246, 0.7)",
                            borderColor: "rgba(59, 130, 246, 1)",
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: "y",
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: { display: true, text: "' . esc_html__('Number of Tickets', 'js-support-ticket') . '" }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            // Chart.js script for Tickets by Status
            if(document.getElementById("ticketsByStatusChart")){
                const statusCtx = document.getElementById("ticketsByStatusChart").getContext("2d");
                const ticketsByStatusChart = new Chart(statusCtx, {
                    type: "doughnut",
                    data: {
                        labels: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_status']['labels']) . ',
                        datasets: [{
                            label: "' . esc_html__('Tickets by Status', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_status']['data']) . ',
                            backgroundColor: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_status']['colors']) . ',
                            borderColor: "#fff",
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: "70%",
                        plugins: {
                            legend: {
                                position: "bottom",
                                labels: { padding: 20, boxWidth: 12, font: { size: 14 } }
                            }
                        }
                    }
                });
            }

            // Chart.js script for Tickets by Products
            if(document.getElementById("ticketsByProductsChart")){
                const productsCtx = document.getElementById("ticketsByProductsChart").getContext("2d");
                const ticketsByProductsChart = new Chart(productsCtx, {
                    type: "bar",
                    data: {
                        labels: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_products']['labels']) . ',
                        datasets: [{
                            label: "' . esc_html__('Tickets by Products', 'js-support-ticket') . '",
                            data: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_products']['data']) . ',
                            backgroundColor: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_products']['colors']) . ',
                            borderColor: ' . wp_json_encode(jssupportticket::$jsst_data['tickets_by_products']['border']) . ',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: "' . esc_html__('Number of Tickets', 'js-support-ticket') . '" }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            // Accordion script for Quick Installation Guide
        const accordionHeaders = document.querySelectorAll(".accordion-header");
        accordionHeaders.forEach(header => {
            header.addEventListener("click", () => {
                const content = header.nextElementSibling;
                const icon = header.querySelector("svg");
                const currentlyOpen = content.style.maxHeight;
                document.querySelectorAll(".accordion-content").forEach(c => c.style.maxHeight = null);
                document.querySelectorAll(".accordion-header svg").forEach(i => i.classList.remove("rotate-180"));
                if (!currentlyOpen) {
                    content.style.maxHeight = content.scrollHeight + "px";
                    icon.classList.add("rotate-180");
                }
            });
        });

        // Dashboard Chart Visibility Toggle Logic
        const savedPreferences = ' . json_encode(get_option('jssupportticket_admin_charts_visibility', [])) . ';
        const ajaxNonce = "' . esc_js($jsst_nonce) . '";
        const ajaxUrl = "' . esc_url(admin_url('admin-ajax.php')) . '";
        const sections = ' . json_encode($jsst_sections) . ';
        
        const menuToggle = document.getElementById("menu-toggle");
        const closeMenu = document.getElementById("close-menu");
        const sideMenu = document.getElementById("side-menu");
        const menuItemsContainer = document.getElementById("menu-items");

        if (menuItemsContainer) {
            sections.forEach(section => {
                const menuItem = document.createElement("div");
                menuItem.classList.add("flex", "items-center", "justify-between", "p-4", "bg-white", "rounded-lg", "shadow-sm");
                const label = document.createElement("span");
                label.textContent = section.name;
                label.classList.add("text-gray-700", "font-medium");
                menuItem.appendChild(label);
                const toggleContainer = document.createElement("label");
                toggleContainer.classList.add("relative", "inline-flex", "items-center", "cursor-pointer");
                const input = document.createElement("input");
                input.type = "checkbox";
                input.classList.add("sr-only", "peer");
                input.dataset.sectionId = section.id;
                input.checked = savedPreferences.hasOwnProperty(section.id) ? savedPreferences[section.id] : true;
                const toggleBg = document.createElement("div");
                const toggleClasses = [
                    "w-11", "h-6", "bg-gray-200", "peer-focus:outline-none",
                    "peer-focus:ring-4", "peer-focus:ring-indigo-300",
                    "rounded-full", "peer", "peer-checked:after:translate-x-full",
                    "peer-checked:after:border-white", "after:content-[\'\']",
                    "after:absolute", "after:top-[2px]", "after:left-[2px]",
                    "after:bg-white", "after:border-gray-300", "after:border",
                    "after:rounded-full", "after:h-5", "after:w-5", "after:transition-all",
                    "peer-checked:bg-indigo-600"
                ];
                toggleBg.className = toggleClasses.join(" ");
                toggleContainer.appendChild(input);
                toggleContainer.appendChild(toggleBg);
                menuItem.appendChild(toggleContainer);
                menuItemsContainer.appendChild(menuItem);
                const sectionElement = document.getElementById(section.id);
                if (sectionElement) {
                    sectionElement.style.display = input.checked ? "" : "none";
                }
                input.addEventListener("change", (event) => {
                    const sectionId = event.target.dataset.sectionId;
                    const sectionElement = document.getElementById(sectionId);
                    if (sectionElement) {
                        if (!sectionElement.parentElement.classList.contains("layout-grid")) {
                            sectionElement.style.display = event.target.checked ? "" : "none";
                        } else {
                            sectionElement.style.display = event.target.checked ? "" : "none";
                            const parentGrid = sectionElement.closest(".layout-grid");
                            if (parentGrid) {
                                const visibleChildren = Array.from(parentGrid.children).filter(child => child.style.display !== "none");
                                if (visibleChildren.length === 0) {
                                    parentGrid.style.display = "none";
                                } else {
                                    parentGrid.style.display = "";
                                    if (visibleChildren.length === 1) {
                                        parentGrid.classList.remove("lg:grid-cols-2");
                                        parentGrid.classList.add("lg:grid-cols-1");
                                    } else {
                                        parentGrid.classList.remove("lg:grid-cols-1");
                                        parentGrid.classList.add("lg:grid-cols-2");
                                    }
                                }
                            }
                        }
                    }
                    const preferences = { ...savedPreferences };
                    preferences[sectionId] = event.target.checked;
                    jQuery.ajax({
                        url: ajaxUrl,
                        type: "POST",
                        data: {
                            action: "save_dashboard_preferences",
                            nonce: ajaxNonce,
                            preferences: preferences,
                        },
                        success: function(response) {
                            if (response.success) {
                                Object.assign(savedPreferences, preferences);
                                console.log("Preferences updated");
                            } else {
                                console.error("Failed to save preferences.");
                            }
                        }
                    });
                });
            });
        }
        if(menuToggle && sideMenu){
            menuToggle.addEventListener("click", () => {
                sideMenu.classList.remove("translate-x-full");
            });
        }
        if(closeMenu && sideMenu){
            closeMenu.addEventListener("click", () => {
                sideMenu.classList.add("translate-x-full");
            });
        }
    });
';

// Attach the consolidated inline script to our main handle. This is the final step.
wp_add_inline_script('js-support-ticket-dashboard-scripts', $jsst_jssupportticket_inline_js);
?>
</div>
