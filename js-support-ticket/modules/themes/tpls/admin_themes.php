<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
require_once JSST_PLUGIN_PATH . 'includes/css/inc-css/ticket-myticket.css.php';
// require_once JSST_PLUGIN_PATH . 'includes/css/style.php';
wp_enqueue_script('iris');
// wp_enqueue_style('jssupportticket-main-css', JSST_PLUGIN_URL . 'includes/css/style.css');
// wp_enqueue_style('jssupportticket-color-css', JSST_PLUGIN_URL . 'includes/css/color.css');
wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css', array(), jssupportticket::$_config['productversion']);
JSSTmessage::getMessage();
?>
<style type="text/css">
    <?php
    $jsst_color1 = jssupportticket::$jsst_data[0]['color1'];
    $jsst_color2 = jssupportticket::$jsst_data[0]['color2'];
    $jsst_color3 = jssupportticket::$jsst_data[0]['color3'];
    $jsst_color4 = jssupportticket::$jsst_data[0]['color4'];
    $jsst_color5 = jssupportticket::$jsst_data[0]['color5'];
    $jsst_color6 = jssupportticket::$jsst_data[0]['color6'];
    $jsst_color7 = jssupportticket::$jsst_data[0]['color7'];

    echo '


    div#jsst-header{background:' . esc_attr($jsst_color1) . ';}
        #jsstadmin-data-wrp .job_sharing_text::before {
        background: ' . esc_attr($jsst_color1) . ';
    }
    #jsstadmin-data-wrp .color_portion input[type="text"]:focus {
        outline: 2px solid ' . esc_attr($jsst_color1) . ';
    }
';
    ?>

    /*new */
    div#jsstadmin-data-wrp {
        box-sizing: border-box;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #DEE2E6;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        /* Ensure font inheritance */
    }

    form.js-filter-form {
        padding: 0;
        margin: 0;
        border: unset;
    }

    div.js-ticket-search-wrp {
        overflow: hidden;
    }

    div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper {
        border-bottom: 11px;
    }

    div.js-ticket-wrapper div.js-ticket-toparea {
        width: calc(100% - 150px);
        border-bottom: unset;
    }

    div.js-ticket-wrapper div.js-ticket-toparea div.js-ticket-data {
        width: 50%;
    }

    div.js_theme_section {
        width: 20%;
        min-width: 250px;
        flex: 1 1 auto;
    }

    div.js_effect_preview {
        width: 70%;
        flex: 1 1 auto;
        font-size: 16px;
        padding: 20px;
    }

    div.js_effect_preview_section_mainwrp {
        align-items: stretch;
    }

    div.js_themepreview_colorwrp {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        align-items: stretch;
        position: relative;
        background-color: #fff;
        border-radius: 12px;
    }

    div.js_themepreview_colorwrp input {
        padding-left: 60px;
    }

    span.js_themepreview_color {
        display: inline-block;
        width: 50px;
        height: 45px;
        margin-right: 10px;
        border-radius: 8px;
        border-top-right-radius: 0px;
        border-bottom-right-radius: 0px;
        position: absolute;
        top: 0px;
        left: 0;
        border-right: 1px solid #DEE2E6 !important;
    }

    div.js_themepreview_colorwrp:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    div.js_effect_preview div.js-ticket-wrapper div.js-ticket-toparea {
        padding: 0px;
    }
    div.js-ticket-top-cirlce-count-wrp{display: flex;flex-wrap: wrap;row-gap: 10px;}
    div.js-ticket-myticket-link-myticket{min-width: fit-content;}
    .js-sugestion-alert svg{min-width: 25px;height: auto;}
    #jsstadmin-wrapper .iris-picker{margin: 10px;border-radius: 12px;}
    .js_jobapply_main_wrapper{border-radius: 12px;}
    div.js-sugestion-alert-wrp{margin-top: 0;}
    div.js-myticket-link a.js-myticket-link.js-ticket-green.active{border-color: #2ECC71 !important;}
</style>
<div id="jsstadmin-wrapper"class="jsstadmin_themepaage_mainwrp">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket"
                                title="<?php echo esc_attr(__('Dashboard', 'js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard', 'js-support-ticket')); ?></a>
                        </li>
                        <li><?php echo esc_html(__('Themes', 'js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration', 'js-support-ticket')); ?>"
                        href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt = "<?php echo esc_attr(__('Configuration', 'js-support-ticket')); ?>"
                            src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>"
                        title="<?php echo esc_attr(__('Help', 'js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('Help', 'js-support-ticket')); ?>"
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Themes", 'js-support-ticket')); ?></h1>
            <a target="blank" href="https://www.youtube.com/watch?v=oOOr869FOyA"
                class="jsstadmin-add-link black-bg button js-cp-video-popup"
                title="<?php echo esc_attr(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt = "<?php echo esc_attr(__('arrow', 'js-support-ticket')); ?>"
                    src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png" />
                <?php echo esc_html(__('Watch Video', 'js-support-ticket')); ?>
            </a>
        </div>
        <div id="jsstadmin-data-wrp" class="">
            <?php do_action('jsst_cm_theme_colors_message', 'js-support-ticket'); ?>
            <div id="theme_heading">
                <div class="left_side">
                    <span
                        class="job_sharing_text"><?php echo esc_html(__('Theme Chooser', 'js-support-ticket')); ?></span>
                </div>
                <div class="right_side">
                    <a href="#" id="preset_theme"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" data-lucide="leaf" size="14"
                            class="lucide lucide-leaf">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z">
                            </path>
                            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                        </svg><span
                            class="theme_presets_theme"><?php echo esc_html(__('Preset Theme', 'js-support-ticket')); ?></span></a>
                </div>
            </div>
            <div class="js_effect_preview_section_mainwrp">
                <div class="js_theme_section">
                    <form
                        action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=themes&task=savetheme'), "save-theme")); ?>"
                        method="POST" name="adminForm" id="adminForm">
                        <span class="js_theme_heading">
                            <?php echo esc_html(__('Color Chooser', 'js-support-ticket')); ?>
                        </span>
                        <div class="color_portion">
                            <span class="color_title"><?php echo esc_html(__('Color 1', 'js-support-ticket')); ?></span>
                            <div class="js_themepreview_colorwrp">
                                <span class="js_themepreview_color js_themepreview_color1"
                                    style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color1']); ?>;border-color:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color1']); ?>66;"></span>
                                <input type="text" name="color1" id="color1"
                                    value="<?php echo esc_attr(jssupportticket::$jsst_data[0]['color1']); ?>"
                                    maxlength="15" />
                            </div>
                            <span class="color_location">
                                <?php echo esc_html(__('Top menu heading background', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="color_portion">
                            <span class="color_title"><?php echo esc_html(__('Color 2', 'js-support-ticket')); ?></span>
                            <div class="js_themepreview_colorwrp">
                                <span class="js_themepreview_color js_themepreview_color2"
                                    style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color2']); ?>;border-color:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color2']); ?>66;"></span>
                                <input type="text" name="color2" id="color2"
                                    value="<?php echo esc_attr(jssupportticket::$jsst_data[0]['color2']); ?>"
                                    maxlength="15" />
                            </div>
                            <span class="color_location">
                                <?php echo esc_html(__('Top header line color', 'js-support-ticket')); ?>,
                                <?php echo esc_html(__('Button Hover', 'js-support-ticket')); ?>,
                                <?php echo esc_html(__('Heading text', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="color_portion">
                            <span class="color_title"><?php echo esc_html(__('Color 3', 'js-support-ticket')); ?></span>
                            <div class="js_themepreview_colorwrp">
                                <span class="js_themepreview_color js_themepreview_color3"
                                    style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color3']); ?>;border-color:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color3']); ?>66;"></span>
                                <input type="text" name="color3" id="color3"
                                    value="<?php echo esc_attr(jssupportticket::$jsst_data[0]['color3']); ?>"
                                    maxlength="15" />
                            </div>
                            <span
                                class="color_location"><?php echo esc_html(__('Content Background Color', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="color_portion">
                            <span class="color_title"><?php echo esc_html(__('Color 4', 'js-support-ticket')); ?></span>
                            <div class="js_themepreview_colorwrp">
                                <span class="js_themepreview_color js_themepreview_color4"
                                    style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color4']); ?>;border-color:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color4']); ?>66;"></span>
                                <input type="text" name="color4" id="color4"
                                    value="<?php echo esc_attr(jssupportticket::$jsst_data[0]['color4']); ?>"
                                    maxlength="15" />
                            </div>
                            <span
                                class="color_location"><?php echo esc_html(__('Content Text Color', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="color_portion">
                            <span class="color_title"><?php echo esc_html(__('Color 5', 'js-support-ticket')); ?></span>
                            <div class="js_themepreview_colorwrp">
                                <span class="js_themepreview_color js_themepreview_color5"
                                    style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color5']); ?>;border-color:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color5']); ?>66;"></span>
                                <input type="text" name="color5" id="color5"
                                    value="<?php echo esc_attr(jssupportticket::$jsst_data[0]['color5']); ?>"
                                    maxlength="15" />
                            </div>
                            <span class="color_location">
                                <?php echo esc_html(__('Border color', 'js-support-ticket')); ?>,
                                <?php echo esc_html(__('Lines', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="color_portion">
                            <span class="color_title"><?php echo esc_html(__('Color 6', 'js-support-ticket')); ?></span>
                            <div class="js_themepreview_colorwrp">
                                <span class="js_themepreview_color js_themepreview_color6"
                                    style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color6']); ?>;border-color:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color6']); ?>66;"></span>
                                <input type="text" name="color6" id="color6"
                                    value="<?php echo esc_attr(jssupportticket::$jsst_data[0]['color6']); ?>"
                                    maxlength="15" />
                            </div>
                            <span
                                class="color_location"><?php echo esc_html(__('Button Color', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="color_portion">
                            <span class="color_title"><?php echo esc_html(__('Color 7', 'js-support-ticket')); ?></span>
                            <div class="js_themepreview_colorwrp">
                                <span class="js_themepreview_color js_themepreview_color7"
                                    style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color7']); ?>;border-color:<?php echo esc_attr(jssupportticket::$jsst_data[0]['color7']); ?>66;"></span>
                                <input type="text" name="color7" id="color7"
                                    value="<?php echo esc_attr(jssupportticket::$jsst_data[0]['color7']); ?>"
                                    maxlength="15" />
                            </div>
                            <span
                                class="color_location"><?php echo esc_html(__('Top header text color', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="color_submit_button_hide">
                            <input type="hidden" name="form_request" value="jssupportticket" />
                        </div>
                        <div class="color_submit_button">
                        <a class="js-color-submit-button" href="#"
                            onclick="document.getElementById('adminForm').submit();">
                            <?php echo esc_html(__('Save Theme', 'js-support-ticket')); ?>
                        </a>
                        <div class="js-sugestion-alert-wrp">
                            <div class="js-sugestion-alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="info" size="14" style="display:inline; vertical-align:middle;" class="lucide lucide-info"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                                <?php echo esc_html(__('Changes may require cache clearing to take effect. ', 'js-support-ticket')); ?>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="js_effect_preview">
                    <div class="jsst-main-up-wrapper">
                        <div id="jsst-header-main-wrapper">
                            <div id="jsst-header" class="">
                                <div id="jsst-tabs-wrp" class=""><span class="jsst-header-tab js-ticket-homeclass"><a
                                            class="js-cp-menu-link"
                                            href="#"><?php echo esc_html(__('Dashboard', 'js-support-ticket')); ?></a></span><span
                                        class="jsst-header-tab js-ticket-openticketclass"><a class="js-cp-menu-link"
                                            href="#"><?php echo esc_html(__('Submit Ticket', 'js-support-ticket')); ?></a></span><span
                                        class="jsst-header-tab js-ticket-myticket"><a class="js-cp-menu-link"
                                            href="#"><?php echo esc_html(__('My Tickets', 'js-support-ticket')); ?></a></span><span
                                        class="jsst-header-tab js-ticket-loginlogoutclass"><a class="js-cp-menu-link"
                                            href="#"><?php echo esc_html(__('Log Out', 'js-support-ticket')); ?></a></span>
                                </div>
                            </div>
                        </div>
                        <!-- Top Circle Count Boxes -->
                        <div class="js-row js-ticket-top-cirlce-count-wrp">
                            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                                <a class="js-ticket-green js-myticket-link active" href="#" data-tab-number="1">
                                    <div class="js-ticket-cricle-wrp" data-per="100">
                                        <div class="js-mr-rp" data-progress="100">
                                            <div class="circle">
                                                <div class="mask full">
                                                    <div class="fill js-ticket-open"></div>
                                                </div>
                                                <div class="mask half">
                                                    <div class="fill js-ticket-open"></div>
                                                    <div class="fill fix"></div>
                                                </div>
                                                <div class="shadow"></div>
                                            </div>
                                            <div class="inset">
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="js-ticket-circle-count-text js-ticket-green"><?php echo esc_html(__('Open', 'js-support-ticket')); ?>( 4 )</span>
                                </a>
                            </div>
                            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                                <a class="js-ticket-red js-myticket-link " href="#" data-tab-number="2">
                                    <div class="js-ticket-cricle-wrp" data-per="0">
                                        <div class="js-mr-rp" data-progress="0">
                                            <div class="circle">
                                                <div class="mask full">
                                                    <div class="fill js-ticket-close"></div>
                                                </div>
                                                <div class="mask half">
                                                    <div class="fill js-ticket-close"></div>
                                                    <div class="fill fix"></div>
                                                </div>
                                                <div class="shadow"></div>
                                            </div>
                                            <div class="inset">
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="js-ticket-circle-count-text js-ticket-red"><?php echo esc_html(__('Closed', 'js-support-ticket')); ?>
                                    ( 0 )</span>
                                </a>
                            </div>
                            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                                <a class="js-ticket-blue js-myticket-link " href="#" data-tab-number="3">
                                    <div class="js-ticket-cricle-wrp" data-per="0">
                                        <div class="js-mr-rp" data-progress="0">
                                            <div class="circle">
                                                <div class="mask full">
                                                    <div class="fill js-ticket-answer"></div>
                                                </div>
                                                <div class="mask half">
                                                    <div class="fill js-ticket-answer"></div>
                                                    <div class="fill fix"></div>
                                                </div>
                                                <div class="shadow"></div>
                                            </div>
                                            <div class="inset">
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="js-ticket-circle-count-text js-ticket-blue"><?php echo esc_html(__('Answered', 'js-support-ticket')); ?>( 1 )</span>
                                </a>
                            </div>
                            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                                <a class="js-ticket-brown js-myticket-link " href="#" data-tab-number="4">
                                    <div class="js-ticket-cricle-wrp" data-per="100">
                                        <div class="js-mr-rp" data-progress="100">
                                            <div class="circle">
                                                <div class="mask full">
                                                    <div class="fill js-ticket-allticket"></div>
                                                </div>
                                                <div class="mask half">
                                                    <div class="fill js-ticket-allticket"></div>
                                                    <div class="fill fix"></div>
                                                </div>
                                                <div class="shadow"></div>
                                            </div>
                                            <div class="inset">
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="js-ticket-circle-count-text js-ticket-brown"><?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>( 4 )</span>
                                </a>
                            </div>
                        </div>

                        <!-- Search Form -->
                        <div class="js-ticket-search-wrp">
                            <div class="js-ticket-form-wrp">
                                <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform"
                                    method="POST"
                                    action="">
                                    <div class="js-filter-wrapper">
                                        <div class="js-filter-form-fields-wrp" id="js-filter-wrapper-toggle-search"
                                            style="">
                                            <input type="text" name="jsst-ticketsearchkeys" id="jsst-ticketsearchkeys"
                                                value="<?php echo esc_attr(__('Ticket ID Or Email Address Or Subject', 'js-support-ticket')); ?>" class="js-ticket-input-field"
                                                placeholder="<?php echo esc_attr(__('Ticket ID Or Email Address Or Subject', 'js-support-ticket')); ?>">
                                        </div>
                                        <div id="js-filter-wrapper-toggle-area"
                                            class="js-filter-wrapper-toggle-ticketid" style="display: none;">
                                            <div
                                                class="js-col-md-3 js-filter-form-fields-wrp js-filter-wrapper-toggle-ticketid">
                                                <input type="text" name="jsst-ticket" id="jsst-ticket" value=""
                                                    class="js-ticket-input-field"
                                                    placeholder="<?php echo esc_attr(__('Ticket ID', 'js-support-ticket')); ?>">
                                            </div>
                                            <div class="js-col-md-3 js-filter-field-wrp">
                                                <input type="text" name="jsst-subject" id="jsst-subject" value=""
                                                    class="js-ticket-input-field"
                                                    placeholder="<?php echo esc_attr(__('Subject', 'js-support-ticket')); ?>">
                                            </div>
                                            <div class="js-col-md-3 js-filter-field-wrp">
                                                <select name="jsst-departmentid" id="jsst-departmentid">
                                                    <option value="">
                                                        <?php echo esc_html(__('Select Department', 'js-support-ticket')); ?>
                                                    </option>
                                                    <option value="1">
                                                        <?php echo esc_html(__('Support', 'js-support-ticket')); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="js-col-md-3 js-filter-field-wrp">
                                                <input type="text" name="jsst-email" id="jsst-email" value=""
                                                    class="js-ticket-input-field"
                                                    placeholder="<?php echo esc_attr(__('Email Address', 'js-support-ticket')); ?>">
                                            </div>
                                            <div class="js-col-md-3 js-filter-field-wrp">
                                                <select name="jsst-priorityid" id="jsst-priorityid">
                                                    <option value="">
                                                        <?php echo esc_html(__('Select Priority', 'js-support-ticket')); ?>
                                                    </option>
                                                    <option value="1">
                                                        <?php echo esc_html(__('Low', 'js-support-ticket')); ?></option>
                                                    <option value="3">
                                                        <?php echo esc_html(__('Normal', 'js-support-ticket')); ?>
                                                    </option>
                                                    <option value="2">
                                                        <?php echo esc_html(__('High', 'js-support-ticket')); ?>
                                                    </option>
                                                    <option value="4">
                                                        <?php echo esc_html(__('Urgent', 'js-support-ticket')); ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="js-filter-button-wrp">
                                            <a href="#" class="js-search-filter-btn"
                                                id="js-search-filter-toggle-btn"><?php echo esc_html(__('Show All', 'js-support-ticket')); ?></a>
                                            <input type="submit" name="" id=""
                                                value="<?php echo esc_attr(__('Search', 'js-support-ticket')); ?>"
                                                class="js-ticket-filter-button js-ticket-search-btn">
                                            <input type="submit" name="jsst-reset" id="jsst-reset"
                                                value="<?php echo esc_attr(__('Reset', 'js-support-ticket')); ?>"
                                                class="js-ticket-filter-button js-ticket-reset-btn"
                                                onclick="return resetForm();">
                                        </div>
                                    </div>
                                    <input type="hidden" name="sortby" id="sortby" value=""> <input type="hidden"
                                        name="list" id="list" value="1"> <input type="hidden" name="JSST_form_search"
                                        id="JSST_form_search" value="JSST_SEARCH">
                                    <input type="hidden" name="jsstpageid" id="jsstpageid" value="6"> <input
                                        type="hidden" name="jshdlay" id="jshdlay" value="myticket">
                                </form>
                            </div>
                        </div>
                        <!-- Sorting Wrapper -->
                        <div class="js-ticket-sorting js-col-md-12">
                            <div class="js-ticket-sorting-left">
                                <div class="js-ticket-sorting-heading">
                                    <?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="js-ticket-sorting-right">
                                <div class="js-ticket-sort">
                                    <select class="js-ticket-sorting-select">
                                        <?php echo esc_html(__('Subject', 'js-support-ticket')); ?>
                                        <option value="subjectdesc">
                                            <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></option>
                                        <option value="prioritydesc">
                                            <?php echo esc_html(__('Priority', 'js-support-ticket')); ?></option>
                                        <option value="ticketiddesc">
                                            <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?></option>
                                        <option value="isanswereddesc">
                                            <?php echo esc_html(__('Answered', 'js-support-ticket')); ?></option>
                                        <option value="statusasc" selected="">
                                            <?php echo esc_html(__('Status', 'js-support-ticket')); ?></option>
                                        <option value="createddesc">
                                            <?php echo esc_html(__('Created', 'js-support-ticket')); ?></option>
                                    </select>
                                    <a href="#" class="js-admin-sort-btn" title="sort">
                                        <img decoding="async" alt="sort"
                                            src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/sorting-2.png">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                            <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                                <img decoding="async" alt="image"
                                    src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/user.png"
                                    class="">
                            </div>
                            <div class="js-ticket-toparea">
                                <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Sophie Martinez', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <a class="js-ticket-title-anchor"
                                            href="#"><?php echo esc_html(__('I’m Not Getting Access to My Subscription', 'js-support-ticket')); ?></a>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <span
                                            class="js-ticket-field-title"><?php echo esc_html(__('Department', 'js-support-ticket')); ?>:&nbsp;</span>
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Support', 'js-support-ticket')); ?></span>
                                    </div>
                                    <span class="js-ticket-wrapper-textcolor" style="background:#c90000;">
                                        <?php echo esc_html(__('Urgent', 'js-support-ticket')); ?></span>

                                    <span class="js-ticket-status" style="background-color: #186e83;color:#FFFFFF;">
                                        <?php echo esc_html(__('Replied', 'js-support-ticket')); ?></span>
                                </div>
                                <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('D48ym7TJY', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Created', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('26-11-2025', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Assign To', 'js-support-ticket')); ?>:
                                        </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('Daniel Brooks', 'js-support-ticket')); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                                <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                            <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                                <img decoding="async" alt="image"
                                    src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/user.png"
                                    class="">
                            </div>
                            <div class="js-ticket-toparea">
                                <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Liam Turner', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <a class="js-ticket-title-anchor"
                                            href="#"><?php echo esc_html(__('I Want to Upgrade My Plan — How Can I Do It?', 'js-support-ticket')); ?></a>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <span
                                            class="js-ticket-field-title"><?php echo esc_html(__('Department', 'js-support-ticket')); ?>:&nbsp;</span>
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Support', 'js-support-ticket')); ?></span>
                                    </div>
                                    <span class="js-ticket-wrapper-textcolor" style="background:#ed8e00;">
                                        <?php echo esc_html(__('High', 'js-support-ticket')); ?></span>
                                    <img decoding="async" class="ticketstatusimage one" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/lock.png" title="The ticket is locked">
                                    <span class="js-ticket-status" style="background-color: #69d2e7;color:#FFFFFF;">
                                        <?php echo esc_html(__('In Progress', 'js-support-ticket')); ?></span>
                                </div>
                                <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('ZJ6YydCNF', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Created', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('26-11-2025', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Assign To', 'js-support-ticket')); ?>:
                                        </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('Emily Harper', 'js-support-ticket')); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                                <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                            <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                                <img decoding="async" alt="image"
                                    src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/user.png"
                                    class="">
                            </div>
                            <div class="js-ticket-toparea">
                                <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Noah Bennett', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <a class="js-ticket-title-anchor"
                                            href="#"><?php echo esc_html(__('I Can’t Log Into My Account', 'js-support-ticket')); ?></a>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <span
                                            class="js-ticket-field-title"><?php echo esc_html(__('Department', 'js-support-ticket')); ?>:&nbsp;</span>
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Support', 'js-support-ticket')); ?></span>
                                    </div>
                                    <span class="js-ticket-wrapper-textcolor" style="background:#86f793;">
                                        <?php echo esc_html(__('Low', 'js-support-ticket')); ?></span>
                                    <img decoding="async" class="ticketstatusimage one" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/over-due.png" title="This ticket is marked as overdue">
                                    <span class="js-ticket-status" style="background-color: #28abe3;color:#FFFFFF;">
                                        <?php echo esc_html(__('Waiting Reply', 'js-support-ticket')); ?></span>
                                </div>
                                <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('yTbM7qDmH', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Created', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('26-11-2025', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Assign To', 'js-support-ticket')); ?>:
                                        </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('Olivia Chase', 'js-support-ticket')); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                                <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                            <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                                <img decoding="async" alt="image"
                                    src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/user.png"
                                    class="">
                            </div>
                            <div class="js-ticket-toparea">
                                <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Maya Collins', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <a class="js-ticket-title-anchor"
                                            href="#"><?php echo esc_html(__('How Long Will My Support Stay Active?', 'js-support-ticket')); ?></a>
                                    </div>
                                    <div
                                        class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <span
                                            class="js-ticket-field-title"><?php echo esc_html(__('Department', 'js-support-ticket')); ?>:&nbsp;</span>
                                        <span
                                            class="js-ticket-value"><?php echo esc_html(__('Support', 'js-support-ticket')); ?></span>
                                    </div>
                                    <span class="js-ticket-wrapper-textcolor" style="background:#c7cbf5;">
                                        <?php echo esc_html(__('Normal', 'js-support-ticket')); ?></span>

                                    <span class="js-ticket-status" style="background-color: #5bb12f;color:#FFFFFF;">
                                        <?php echo esc_html(__('New', 'js-support-ticket')); ?></span>
                                </div>
                                <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('GxvdTBWMY', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Created', 'js-support-ticket')); ?>: </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('26-11-2025', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit">
                                            <?php echo esc_html(__('Assign To', 'js-support-ticket')); ?>:
                                        </div>
                                        <div class="js-ticket-data-val">
                                            <?php echo esc_html(__('Ethan Reynolds', 'js-support-ticket')); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $jsst_jssupportticket_js = "
            jQuery(document).ready(function () {
                makeColorPicker('" . esc_js(jssupportticket::$jsst_data[0]['color1']) . "', '" . esc_js(jssupportticket::$jsst_data[0]['color2']) . "', '" . esc_js(jssupportticket::$jsst_data[0]['color3']) . "', '" . esc_js(jssupportticket::$jsst_data[0]['color4']) . "', '" . esc_js(jssupportticket::$jsst_data[0]['color5']) . "', '" . esc_js(jssupportticket::$jsst_data[0]['color6']) . "', '" . esc_js(jssupportticket::$jsst_data[0]['color7']) . "');
                    /* --- NEW CODE: CLICK OUTSIDE TO CLOSE LOGIC --- */
    
    // 1. Close picker when clicking outside the input or the picker box
    jQuery(document).click(function (e) {
        if (!jQuery(e.target).is('input[id^=\"color\"]') && !jQuery(e.target).closest('.iris-picker').length) {
            jQuery('input[id^=\"color\"]').iris('hide');
        }
    });

    // 2. Prevent the document click from firing when clicking the input itself
    //    and ensure other pickers close when opening a new one.
    jQuery('input[id^=\"color\"]').click(function (event) {
        jQuery('input[id^=\"color\"]').not(this).iris('hide'); // Optional: Close other open pickers
        jQuery(this).iris('show');
        event.stopPropagation();
    });


            });
            function makeColorPicker(color1, color2, color3, color4, color5, color6, color7) {
                jQuery('input#color1').iris({
                    color: color1,
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        //hex = ui.color.toString();
                        let color1 = ui.color.toString();
                        let color7 = jQuery('#color7').val();
                        let color2 = jQuery('#color2').val();   
                        jQuery('.js_themepreview_color1').css('background-color', color1);
                        jQuery('.js_themepreview_color1').css('border-color', color1);
                        jQuery('.js-ticket-data-tit, .js-ticket-field-title, .js-ticket-title-anchor').css('color', color2);
                        jQuery('a.js-ticket-title-anchor').mouseover(function () {
                            jQuery('a.js-ticket-title-anchor').css('color', jQuery('color1').val());
                        }).mouseout(function () {
                            jQuery('a.js-ticket-title-anchor').css('color', jQuery('color2').val());
                        });
                        jQuery('div#jsst-header span.jsst-header-tab.active a.js-cp-menu-link').css('background-color', color1);
                        jQuery('div#jsst-header').css('background-color', color1);
                        jQuery('.js-ticket-search-btn').css('background-color', color1);
                        jQuery('.js-ticket-search-btn').css('border-color', color1);
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('background-color', color1);
                        jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link').css('color', color1);
                        jQuery('div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a').css('color', color7);
                        jQuery('div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a').css('background-color', color1);
                        jQuery('div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn')
                            .off('mouseenter mouseleave')   
                            .on('mouseenter', function () {
                                let color = jQuery('#color2').val();
                                jQuery(this).css('background-color', color);
                                jQuery(this).css('border-color', color);
                            })
                            .on('mouseleave', function () {
                                let color = jQuery('#color1').val();
                                jQuery(this).css('background-color', color); // remove inline color
                                jQuery(this).css('border-color', color); // remove inline color
                        });
                        jQuery('div.js-ticket-body-data-elipses a')
                            .off('mouseenter mouseleave')  
                            .on('mouseenter', function () {
                                let color = jQuery('#color1').val();
                                jQuery(this).css('color', color);
                            })
                            .on('mouseleave', function () {
                                jQuery(this).css('color', '');
                            });
                        /* border color 1 on hover */
                        jQuery('div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn, .js-ticket-wrapper, div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn')
                            .off('mouseenter mouseleave')  
                            .on('mouseenter', function () {
                                let color = jQuery('#color1').val();
                                jQuery(this).css('border-color', color);
                            })
                            .on('mouseleave', function () {
                                jQuery(this).css('border-color', '');
                            });
                        /* color 1 on hover */
                            jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link')
                            .off('mouseenter mouseleave')  
                            .on('mouseenter', function () {
                                let bgColor = jQuery('#color1').val();   // background color
                                let textColor = jQuery('#color7').val(); // text color
                                jQuery(this).css('background-color', bgColor);
                                jQuery(this).css('color', textColor);
                            })
                            .on('mouseleave', function () {
                                let color = jQuery('#color1').val();
                                jQuery(this).css('background-color', '');
                                jQuery(this).css('color', color);
                            });
                            /*logoout hover*/
                            jQuery('div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a')
                                .off('mouseenter mouseleave')
                                .on('mouseenter', function () {
                                    jQuery(this).css({'background-color': color7, 'color': color1});
                                })
                                .on('mouseleave', function () {
                                    jQuery(this).css({'background-color': color1, 'color': color7});
                                });
                    }
                });
                jQuery('input#color2').iris({
                    color: color2,
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        hex = ui.color.toString();
                        let color1 = jQuery('#color1').val();
                        jQuery('.js_themepreview_color2').css('background-color', hex);
                        jQuery('.js_themepreview_color2').css('border-color', hex);
                        jQuery('div.js-ticket-sorting').css('background-color', hex);
                        jQuery('.js-ticket-data-tit, .js-ticket-field-title, .js-ticket-title-anchor').css('color', hex);
                        jQuery('.js-ticket-title-anchor').mouseover(function () {
                            jQuery('.js-ticket-title-anchor').css('color', jQuery('color1').val());
                        }).mouseout(function () {
                            jQuery('.js-ticket-title-anchor').css('color', jQuery('input#color2').val());
                        });
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a.selected').css('background-color', jQuery('input#color2').val());
                        jQuery('div.js-ticket-flat a.active').css('borderColor', jQuery('input#color2').val());
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').mouseover(function () {
                            jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('background-color', jQuery('input#color2').val());
                        }).mouseout(function () {
                            jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('background-color', jQuery('input#color5').val());
                        });
                        jQuery('div.js-ticket-flat a').mouseover(function () {
                            jQuery('div.js-ticket-flat a').css('background-color', jQuery('input#color2').val());
                        }).mouseout(function () {
                            jQuery('div.js-ticket-flat a').css('background-color', jQuery('input#color5').val());
                        });
                        jQuery('div.js-ticket-body-data-elipses a')
                            .off('mouseenter mouseleave')  
                            .on('mouseenter', function () {
                                let color = jQuery('#color1').val();
                                jQuery(this).css('color', color);
                            })
                            .on('mouseleave', function () {
                                jQuery(this).css('color', hex);
                            });
                        /*logoout hover*/ 
                        jQuery('div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn')
                            .off('mouseenter mouseleave')   
                            .on('mouseenter', function () {
                                let color = jQuery('#color2').val();
                                jQuery(this).css('background-color', color);
                                jQuery(this).css('border-color', color);
                            })
                            .on('mouseleave', function () {
                                let color = jQuery('#color1').val();
                                jQuery(this).css('background-color', color); // remove inline color
                                jQuery(this).css('border-color', color); // remove inline color
                            });
                        jQuery('div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn')
                            .off('mouseenter mouseleave')   
                            .on('mouseenter', function () {
                                let color = jQuery('#color2').val();
                                let brderColor = jQuery('#color5').val();
                                jQuery(this).css('background-color', color);
                                jQuery(this).css('border-color', brderColor);
                            })
                            .on('mouseleave', function () {
                                jQuery(this).css('background-color', ''); // remove inline color
                                jQuery(this).css('border-color', ''); // remove inline color
                            });

                    }
                });
                jQuery('input#color3').iris({
                    color: color3,
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        hex = ui.color.toString();
                        jQuery('.js_themepreview_color3').css('background-color', hex);
                        jQuery('.js_themepreview_color3').css('border-color', hex);
                        jQuery('div#jsst-header div#jsst-header-heading').css('color', hex);
                        jQuery('div.js-ticket-assigned-tome').css('background-color', hex);
                        jQuery('div.jsst-main-up-wrapper, div.js-myticket-link a.js-myticket-link').css('background-color', hex);
                    }
                });
                jQuery('input#color4').iris({
                    color: color4,
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        hex = ui.color.toString();
                        jQuery('.js_themepreview_color4').css('background-color', hex);
                        jQuery('.js_themepreview_color4').css('border-color', hex);
                        jQuery('div.js-ticket-breadcrumb-wrp .breadcrumb li a, div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select, div.js-ticket-wrapper div.js-ticket-data .name span.js-ticket-value').css('color', hex);
                        jQuery('div.js-ticket-wrapper div.js-ticket-data span.js-ticket-title, div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn, div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-val').css('color', hex);
                        jQuery('div.js-ticket-wrapper div.js-ticket-data span.js-ticket-value, div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn').css('color', hex);
                        jQuery('div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field').css('color', hex);
                        
                    }
                });
                
                jQuery('input#color5').iris({
                    color: color5,
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        hex = ui.color.toString();
                        jQuery('.js_themepreview_color5').css('background-color', hex);
                        jQuery('.js_themepreview_color5').css('border-color', hex);
                        jQuery('div.js-ticket-assigned-tome').css('border-color', hex);
                        jQuery('div.js-ticket-top-cirlce-count-wrp, div.js-ticket-search-wrp, div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field').css('border-color', hex);
                        jQuery('div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn, div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn').css('border-color', hex);
                        jQuery('div.js-ticket-search-wrp, div.js-ticket-sorting, div.js-ticket-top-cirlce-count-wrp, .jsst-main-up-wrapper .js-ticket-data1, div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select').css('border-color', hex);
                        jQuery('div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn').css('border-color', hex);
                        jQuery('div.js-myticket-link a.js-myticket-link').css('border-color', hex);
                        jQuery('div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn')
                            .off('mouseenter mouseleave')   
                            .on('mouseenter', function () {
                                let borderColor = jQuery('#color1').val();
                                jQuery(this).css('border-color', borderColor);
                            })
                            .on('mouseleave', function () {
                                jQuery(this).css('border-color', hex);
                            });
                        
                    }
                });
                jQuery('input#color6').iris({
                    color: color6,
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        hex = ui.color.toString();
                        jQuery('.js_themepreview_color6').css('background-color', hex);
                        jQuery('.js_themepreview_color6').css('border-color', hex);
                    }
                });
                jQuery('input#color7').iris({
                    color: color7,
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        //hex = ui.color.toString();
                        let color7 = ui.color.toString();
                        let color1 = jQuery('#color1').val(); 
                        jQuery('.js_themepreview_color7').css('background-color', color7);
                        jQuery('.js_themepreview_color7').css('border-color', color7);
                        jQuery('a.js-myticket-link,span.js-ticket-sorting-link a').each(function () {
                            jQuery(this).css('color', color7)
                        });
                        jQuery('a.js-ticket-header-links').mouseover(function () {
                            jQuery('a.js-ticket-header-links').css('color', jQuery('input#color7').val());
                        }).mouseout(function () {
                            jQuery('a.js-ticket-header-links').css('color', jQuery('input#color7').val());
                        });
                        jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link').css('background-color', color7);
                        jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link').css('border-color', color7);
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('color', color7);
                        jQuery('div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn').css('color', color7);
                        jQuery('div.js-ticket-sorting').css('color', color7);
                        jQuery('div#jsst-header div#jsst-header-heading a').css('color', color7);
                        jQuery('div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a').css('background-color', color1);
                        jQuery('div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a').css('color', color7);
                        /* color 7 on hover */
                            jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link')
                            .off('mouseenter mouseleave')  
                            .on('mouseenter', function () {
                                let bgColor = jQuery('#color1').val();  
                                let textColor = jQuery('#color7').val();
                                jQuery(this).css('background-color', bgColor);
                                jQuery(this).css('color', textColor);
                            })
                            .on('mouseleave', function () {
                                let bgColor = jQuery('#color1').val(); 
                                let color = jQuery('#color7').val();
                                jQuery(this).css('background-color', color);
                                jQuery(this).css('color', bgColor);
                            });
                            /*logoout hover*/
                            jQuery('div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a')
                                .off('mouseenter mouseleave')  
                                .on('mouseenter', function () {
                                    let bgColor = jQuery('#color1').val();  
                                    let textColor = jQuery('#color7').val();
                                    jQuery(this).css('background-color', textColor);
                                    jQuery(this).css('border-color', textColor);
                                    jQuery(this).css('color', bgColor);
                                })
                                .on('mouseleave', function () {
                                    let bgColor = jQuery('#color1').val(); 
                                    let color = jQuery('#color7').val();
                                    jQuery(this).css('border-color', color);
                                    jQuery(this).css('background-color', bgColor);
                                    jQuery(this).css('color', color);
                                });
                    }
                });

            }
        ";
        wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
        ?>
        <div id="black_wrapper_jobapply" style="display:none;"></div>
        <div id="js_jobapply_main_wrapper" style="display:none;padding:0px 5px;">
            <div id="js_job_wrapper">
                <span
                    class="js_job_controlpanelheading"><?php echo esc_html(__('Preset Theme', 'js-support-ticket')); ?></span>
                <div class="js_theme_wrapper">
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#4f6df5;"></div>
                            <div class="color 2" style="background:#2b2b2b;"></div>
                            <div class="color 3" style="background:#f5f2f5;"></div>
                            <div class="color 4" style="background:#636363;"></div>
                            <div class="color 5" style="background:#d1d1d1;"></div>
                            <div class="color 6" style="background:#E7E7E7;"></div>
                            <div class="color 7" style="background:#FFFFFF;"></div>
                            <span class="theme_name"><?php echo esc_html(__('Blue', 'js-support-ticket')); ?></span>
                            <img class="preview"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview1.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#E43039;"></div>
                            <div class="color 2" style="background:#2b2b2b;"></div>
                            <div class="color 3" style="background:#f5f2f5;"></div>
                            <div class="color 4" style="background:#636363;"></div>
                            <div class="color 5" style="background:#d1d1d1;"></div>
                            <div class="color 6" style="background:#E7E7E7;"></div>
                            <div class="color 7" style="background:#FFFFFF;"></div>
                            <span class="theme_name"><?php echo esc_html(__('Red', 'js-support-ticket')); ?></span>
                            <img class="preview"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview2.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#36BC9A;"></div>
                            <div class="color 2" style="background:#2b2b2b;"></div>
                            <div class="color 3" style="background:#f5f2f5;"></div>
                            <div class="color 4" style="background:#636363;"></div>
                            <div class="color 5" style="background:#d1d1d1;"></div>
                            <div class="color 6" style="background:#E7E7E7;"></div>
                            <div class="color 7" style="background:#FFFFFF;"></div>
                            <span class="theme_name"><?php echo esc_html(__('Greenish', 'js-support-ticket')); ?></span>
                            <img class="preview"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview3.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#A601E1;"></div>
                            <div class="color 2" style="background:#2b2b2b;"></div>
                            <div class="color 3" style="background:#f5f2f5;"></div>
                            <div class="color 4" style="background:#636363;"></div>
                            <div class="color 5" style="background:#d1d1d1;"></div>
                            <div class="color 6" style="background:#E7E7E7;"></div>
                            <div class="color 7" style="background:#FFFFFF;"></div>
                            <span class="theme_name"><?php echo esc_html(__('Purple', 'js-support-ticket')); ?></span>
                            <img class="preview"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview4.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#F48243;"></div>
                            <div class="color 2" style="background:#2b2b2b;"></div>
                            <div class="color 3" style="background:#f5f2f5;"></div>
                            <div class="color 4" style="background:#636363;"></div>
                            <div class="color 5" style="background:#d1d1d1;"></div>
                            <div class="color 6" style="background:#E7E7E7;"></div>
                            <div class="color 7" style="background:#FFFFFF;"></div>
                            <span class="theme_name"><?php echo esc_html(__('Orange', 'js-support-ticket')); ?></span>
                            <img class="preview"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview5.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#8CC051;"></div>
                            <div class="color 2" style="background:#2b2b2b;"></div>
                            <div class="color 3" style="background:#f5f2f5;"></div>
                            <div class="color 4" style="background:#636363;"></div>
                            <div class="color 5" style="background:#d1d1d1;"></div>
                            <div class="color 6" style="background:#E7E7E7;"></div>
                            <div class="color 7" style="background:#FFFFFF;"></div>
                            <span class="theme_name"><?php echo esc_html(__('Green', 'js-support-ticket')); ?></span>
                            <img class="preview"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview6.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                    <div class="theme_platte">
                        <div class="color_wrapper">
                            <div class="color 1" style="background:#57585A;"></div>
                            <div class="color 2" style="background:#2b2b2b;"></div>
                            <div class="color 3" style="background:#f5f2f5;"></div>
                            <div class="color 4" style="background:#636363;"></div>
                            <div class="color 5" style="background:#d1d1d1;"></div>
                            <div class="color 6" style="background:#E7E7E7;"></div>
                            <div class="color 7" style="background:#FFFFFF;"></div>
                            <span class="theme_name"><?php echo esc_html(__('Black', 'js-support-ticket')); ?></span>
                            <img class="preview"
                                src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview7.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $jsst_jssupportticket_js = '
            jQuery(document).ready(function () {
                jQuery("a#preset_theme").click(function (e) {
                    e.preventDefault();
                    jQuery("div#js_jobapply_main_wrapper").fadeIn();
                    jQuery("div#black_wrapper_jobapply").fadeIn();
                });
                jQuery("div#black_wrapper_jobapply").click(function () {
                    jQuery("div#js_jobapply_main_wrapper").fadeOut();
                    jQuery("div#black_wrapper_jobapply").fadeOut();
                });
                jQuery("a.preview").each(function (index, element) {
                    jQuery(this).hover(function () {
                        if (index > 2)
                            jQuery(this).parent().find("img.preview").css("top", "-110px");
                        jQuery(jQuery(this).parent().find("img.preview")).show();
                    }, function () {
                        jQuery(jQuery(this).parent().find("img.preview")).hide();
                    });
                });
                jQuery("a.set_theme").each(function (index, element) {
                    jQuery(this).click(function (e) {
                        e.preventDefault();
                        var div = jQuery(this).parent();
                        var color1 = rgb2hex(jQuery(div.find("div.1")).css("backgroundColor"));
                        var color2 = rgb2hex(jQuery(div.find("div.2")).css("backgroundColor"));
                        var color3 = rgb2hex(jQuery(div.find("div.3")).css("backgroundColor"));
                        var color4 = rgb2hex(jQuery(div.find("div.4")).css("backgroundColor"));
                        var color5 = rgb2hex(jQuery(div.find("div.5")).css("backgroundColor"));
                        var color6 = rgb2hex(jQuery(div.find("div.6")).css("backgroundColor"));
                        var color7 = rgb2hex(jQuery(div.find("div.7")).css("backgroundColor"));
                        jQuery("input#color1").val(color1);
                        jQuery("input#color2").val(color2);
                        jQuery("input#color3").val(color3);
                        jQuery("input#color4").val(color4);
                        jQuery("input#color5").val(color5);
                        jQuery("input#color6").val(color6);
                        jQuery("input#color7").val(color7);
                        jQuery(".js_themepreview_color1").val(color1).css("backgroundColor", color1);
                        jQuery(".js_themepreview_color2").val(color2).css("backgroundColor", color2);
                        jQuery(".js_themepreview_color3").val(color3).css("backgroundColor", color3);
                        jQuery(".js_themepreview_color4").val(color4).css("backgroundColor", color4);
                        jQuery(".js_themepreview_color5").val(color5).css("backgroundColor", color5);
                        jQuery(".js_themepreview_color6").val(color6).css("backgroundColor", color6);
                        jQuery(".js_themepreview_color7").val(color7).css("backgroundColor", color7);
                        themeSelectionEffect();
                        jQuery("div#js_jobapply_main_wrapper").fadeOut();
                        jQuery("div#black_wrapper_jobapply").fadeOut();
                    });
                });
            });
            function rgb2hex(rgb) {
                rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
                function hex(x) {
                    return ("0" + parseInt(x).toString(16)).slice(-2);
                }
                return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
            }
            function themeSelectionEffect() {
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").mouseover(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color2").val());
                });
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").mouseout(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color1").val());
                });
                jQuery("div#jsst-header").css("backgroundColor", jQuery(".js_themepreview_color1").val());
                jQuery(".js-ticket-title-anchor").mouseover(function () {
                    jQuery(this).css("color", jQuery(".js_themepreview_color1").val());
                });
                jQuery(".js-ticket-title-anchor").mouseout(function () {
                    jQuery(this).css("color", jQuery(".js_themepreview_color2").val());
                });
                jQuery(".js-ticket-search-btn").css("background-color", jQuery(".js_themepreview_color1").val());
                jQuery(".js-ticket-search-btn").css("border-color", jQuery(".js_themepreview_color1").val());
                jQuery(".js-ticket-search-btn").mouseover(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color2").val());
                    jQuery(this).css("color", jQuery(".js_themepreview_color7").val());
                    jQuery(this).css("borderColor", jQuery(".js_themepreview_color2").val());
                });
                jQuery(".js-ticket-search-btn").mouseout(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color1").val());
                    jQuery(this).css("color", jQuery(".js_themepreview_color7").val());
                    jQuery(this).css("borderColor", jQuery(".js_themepreview_color1").val());
                });
                jQuery("#js-search-filter-toggle-btn").mouseover(function () {
                    jQuery(this).css("border-color", jQuery(".js_themepreview_color1").val());
                });
                jQuery("#js-search-filter-toggle-btn").mouseout(function () {
                     jQuery(this).css("border-color", "#d1d1d1");
                });
                jQuery(".js-ticket-wrapper").mouseover(function () {
                    jQuery(this).css("border-color", jQuery(".js_themepreview_color1").val());
                });
                jQuery(".js-ticket-wrapper").mouseout(function () {
                     jQuery(this).css("border-color", "#d1d1d1");
                });
                jQuery("div.js-ticket-sorting").css("backgroundColor", jQuery(".js_themepreview_color2").val());
                jQuery("div#jsst-header span.jsst-header-tab a.js-cp-menu-link").css("backgroundColor", jQuery(".js_themepreview_color7").val());
                jQuery("div#jsst-header span.jsst-header-tab a.js-cp-menu-link").css("borderColor", jQuery(".js_themepreview_color7").val());
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn").css("color", jQuery(".js_themepreview_color7").val());
                jQuery(".js-ticket-body-data-elipses .js-ticket-field-title").css("color", jQuery(".js_themepreview_color2").val());
                jQuery(".js-ticket-body-data-elipses a").css("color", jQuery(".js_themepreview_color2").val());
                jQuery("div.jsst-main-up-wrapper").css("backgroundColor", jQuery(".js_themepreview_color3").val());
                jQuery("div.js-myticket-link a.js-myticket-link").css("backgroundColor", jQuery(".js_themepreview_color3").val());
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-val").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("div.js-ticket-top-cirlce-count-wrp, div.js-ticket-search-wrp, div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery("div.js-myticket-link a.js-myticket-link").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery("div.js-ticket-search-wrp, div.js-ticket-sorting, div.js-ticket-top-cirlce-count-wrp").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery("div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery("div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery(".jsst-main-up-wrapper .js-ticket-data1").css("borderColor", jQuery(".js_themepreview_color5").val());
                jQuery("div.js-ticket-sorting").css("color", jQuery(".js_themepreview_color7").val());
                jQuery(".js-ticket-data-tit").css("color", jQuery(".js_themepreview_color2").val());
                jQuery("div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("span.jsst-header-tab a").css("color", jQuery(".js_themepreview_color1").val());
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn").mouseover(function () {
                jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color2").val());
                });
                jQuery("div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn").mouseout(function () {
                    jQuery(this).css("backgroundColor", "#f5f5f5");
                });
                jQuery("span.jsst-header-tab a").mouseover(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color1").val());
                    jQuery(this).css("color", jQuery(".js_themepreview_color7").val());
                });
                jQuery("span.jsst-header-tab a").mouseout(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color7").val());
                    jQuery(this).css("color", jQuery(".js_themepreview_color1").val());
                });
                jQuery("div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a").css("backgroundColor", jQuery(".js_themepreview_color1").val());
                jQuery("div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a").css("color", jQuery(".js_themepreview_color7").val());
                jQuery("div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a").mouseover(function () {
                jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color7").val());
                jQuery(this).css("color", jQuery(".js_themepreview_color1").val());
                });
                jQuery("div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a").mouseout(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color1").val());
                    jQuery(this).css("color", jQuery(".js_themepreview_color7").val());
                });
                jQuery("span.jsst-header-tab.active a").css("color", jQuery(".js_themepreview_color3").val());
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").css("backgroundColor", jQuery(".js_themepreview_color1").val());
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").mouseover(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color2").val());
                });
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").mouseout(function () {
                    jQuery(this).css("backgroundColor", jQuery(".js_themepreview_color1").val());
                });
                jQuery("span.js-ticket-title").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("span.js-ticket-value").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("div.js-ticket-data1").css("color", jQuery(".js_themepreview_color4").val());
                jQuery("span.js-ticket-sorting-link a").each(function () {
                    jQuery(this).css("color", jQuery(".js_themepreview_color7").val())
                });
            }
        ';
        wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
        ?>
    </div>
</div>
