<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('iris');
wp_enqueue_style('jssupportticket-main-css', JSST_PLUGIN_URL . 'includes/css/style.css');
wp_enqueue_style('jssupportticket-color-css', JSST_PLUGIN_URL . 'includes/css/color.css');
wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css');
// include_once JSST_PLUGIN_PATH . 'includes/css/style.php';
JSSTmessage::getMessage();
?>
<style type="text/css">
<?php
$color1 = jssupportticket::$_data[0]['color1'];
$color2 = jssupportticket::$_data[0]['color2'];
$color3 = jssupportticket::$_data[0]['color3'];
$color4 = jssupportticket::$_data[0]['color4'];
$color5 = jssupportticket::$_data[0]['color5'];
$color6 = jssupportticket::$_data[0]['color6'];
$color7 = jssupportticket::$_data[0]['color7'];

echo '


div.js-ticket-wrapper{border:1px solid'.esc_attr($color5).';box-shadow: 0 8px 6px -6px #dedddd;}
div.js-ticket-wrapper:hover{border:1px solid'.esc_attr($color2).';}
div.js-ticket-wrapper:hover div.js-ticket-pic{border-right:1px solid'.esc_attr($color2).';}
div.js-ticket-wrapper:hover div.js-ticket-data1{border-left:1px solid'.esc_attr($color2).';}
div.js-ticket-wrapper:hover div.js-ticket-bottom-line{background'.esc_attr($color2).';}
div.js-ticket-wrapper div.js-ticket-pic{border-right:1px solid'.esc_attr($color5).';}
div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status{color:#FFFFFF;}
div.js-ticket-wrapper div.js-ticket-data1{border-left:1px solid'.esc_attr($color5).';}
div.js-ticket-wrapper div.js-ticket-data span.js-ticket-title{color:'.esc_attr($color4).';}
a.js-ticket-title-anchor:hover{color:'.esc_attr($color2).' !important;}
div.js-ticket-wrapper div.js-ticket-data span.js-ticket-value{color:'.esc_attr($color4).';}
div.js-ticket-wrapper div.js-ticket-bottom-line{background'.esc_attr($color2).';}
div.js-ticket-assigned-tome{border:1px solid'.esc_attr($color5).';background-color:'.esc_attr($color3).';}
div.js-ticket-sorting span.js-ticket-sorting-link a{background:#373435;color:'.esc_attr($color7).';color:#fff;}
div.js-ticket-sorting span.js-ticket-sorting-link a.selected,
div.js-ticket-sorting span.js-ticket-sorting-link a:hover{background:'.esc_attr($color2).';}
div#jsst-header div#jsst-header-heading a{color:'.esc_attr($color7).';}
';
?>
div.js-ticket-sorting{float: left;width: 100%;}
/* My Tickets $ Staff My Tickets*/
div.js-ticket-wrapper{margin:8px 0px;padding-left: 0px;padding-right: 0px;}
div.js-ticket-wrapper div.js-ticket-pic{margin: 10px 0px;padding: 0px;padding: 0px 10px;text-align: center;position: relative;float: left;width: 16% !important;height: 96px !important;}
div.js-ticket-wrapper div.js-ticket-pic img.js-ticket-staff-img{width: auto;max-width: 96px;max-height: 96px;height: auto;position: absolute;top: 0;left: 0;right: 0;bottom: 0;margin: auto;}
div.js-ticket-wrapper div.js-ticket-data{position: relative;padding: 23px 0px;width: 50% !important;}
div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status{position: absolute;top:41%;right:2%;padding: 10px 10px;border-radius: 20px;font-size: 10px;line-height: 1;font-weight: bold;}
div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status img.ticketstatusimage{position: absolute;top:0px;}
div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status img.ticketstatusimage.one{left:-25px;}
div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status img.ticketstatusimage.two{left:-50px;}
div.js-ticket-wrapper div.js-ticket-data1{margin:0px 0px;padding: 17px 15px !important;width: 33% !important;}
div.js-ticket-wrapper div.js-ticket-bottom-line{position:absolute;display: inline-block;width:90%;margin:0 5%;height:1px;left:0px;bottom: 0px;}
div.js-ticket-wrapper div.js-ticket-toparea{position: relative;padding:0px;}
div.js-ticket-wrapper div.js-ticket-bottom-data-part{padding: 0px;margin-bottom: 10px;}
div.js-ticket-wrapper div.js-ticket-bottom-data-part a.button{float:right;margin-left: 10px;padding:0px 20px;line-height: 30px;height:32px;}
div.js-ticket-wrapper div.js-ticket-bottom-data-part a.button img{height:16px;margin-right:5px;}
div.js-ticket-assigned-tome{float: left;width: 100%;padding: 11px 10px;}
div.js-ticket-assigned-tome input#assignedtome1{margin-right: 5px;vertical-align: middle;}
div.js-ticket-assigned-tome label#forassignedtome{margin: 0px;display: inline-block;}
label#forassigntome{margin: 0px;display: inline-block;}
span.js-ticket-wrapper-textcolor{display: inline-block;padding: 5px 10px;min-width: 85px;text-align: center;}
/* Sorting Section */
div.js-ticket-sorting{padding-right: 0px;padding-left: 0px;margin-bottom: 15px;}
div.js-ticket-sorting span.js-ticket-sorting-link{padding-right:0px;padding-left: 0px;}
div.js-ticket-sorting span.js-ticket-sorting-link a{text-decoration: none;display: block;padding: 15px;text-align:center;color: #fff !important;}
div.js-ticket-sorting span.js-ticket-sorting-link a img{display: inline-block;vertical-align: text-top;width: 24px;}
</style>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Themes','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_html(__('Help','js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Themes", 'js-support-ticket')); ?></h1>
            <a target="blank" href="https://www.youtube.com/watch?v=oOOr869FOyA" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_html(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                <?php echo esc_html(__('Watch Video','js-support-ticket')); ?>
            </a>
        </div>
        <div id="jsstadmin-data-wrp" class="">
            <?php do_action('cm_theme_colors_message', 'js-support-ticket'); ?>
            <div id="theme_heading">
                <div class="left_side">
                    <span class="job_sharing_text"><?php echo esc_html(__('Color Chooser', 'js-support-ticket')); ?></span>
                </div>
                <div class="right_side">
                    <a href="#" id="preset_theme"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/preset_theme.png" /><span class="theme_presets_theme"><?php echo esc_html(__('Preset Theme', 'js-support-ticket')); ?></span></a>
                </div>
            </div>
            <div class="js_theme_section">
                <form action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=themes&task=savetheme'),"save-theme")); ?>" method="POST" name="adminForm" id="adminForm">
                    <span class="js_theme_heading">
                        <?php echo esc_html(__('Color Chooser', 'js-support-ticket')); ?>
                    </span>
                    <div class="color_portion">
                        <span class="color_title"><?php echo esc_html(__('Color 1', 'js-support-ticket')); ?></span>
                        <input type="text" name="color1" id="color1" value="<?php echo esc_attr(jssupportticket::$_data[0]['color1']); ?>" style="background:<?php echo esc_attr(jssupportticket::$_data[0]['color1']); ?>;" maxlength="15" />
                        <span class="color_location">
                            <?php echo esc_html(__('Top menu heading background', 'js-support-ticket')); ?>
                        </span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title"><?php echo esc_html(__('Color 2', 'js-support-ticket')); ?></span>
                        <input type="text" name="color2" id="color2" value="<?php echo esc_attr(jssupportticket::$_data[0]['color2']); ?>" style="background:<?php echo esc_attr(jssupportticket::$_data[0]['color2']); ?>;" maxlength="15"/>
                        <span class="color_location">
                            <?php echo esc_html(__('Top header line color', 'js-support-ticket')); ?>,
                            <?php echo esc_html(__('Button Hover', 'js-support-ticket')); ?>,
                            <?php echo esc_html(__('Heading text', 'js-support-ticket')); ?>
                        </span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title"><?php echo esc_html(__('Color 3', 'js-support-ticket')); ?></span>
                        <input type="text" name="color3" id="color3" value="<?php echo esc_attr(jssupportticket::$_data[0]['color3']); ?>" style="background:<?php echo esc_attr(jssupportticket::$_data[0]['color3']); ?>;" maxlength="15"/>
                        <span class="color_location"><?php echo esc_html(__('Content Background Color', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title"><?php echo esc_html(__('Color 4', 'js-support-ticket')); ?></span>
                        <input type="text" name="color4" id="color4" value="<?php echo esc_attr(jssupportticket::$_data[0]['color4']); ?>" style="background:<?php echo esc_attr(jssupportticket::$_data[0]['color4']); ?>;" maxlength="15"/>
                        <span class="color_location"><?php echo esc_html(__('Content Text Color', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title"><?php echo esc_html(__('Color 5', 'js-support-ticket')); ?></span>
                        <input type="text" name="color5" id="color5" value="<?php echo esc_attr(jssupportticket::$_data[0]['color5']); ?>" style="background:<?php echo esc_attr(jssupportticket::$_data[0]['color5']); ?>;" maxlength="15"/>
                        <span class="color_location">
                            <?php echo esc_html(__('Border color', 'js-support-ticket')); ?>,
                            <?php echo esc_html(__('Lines', 'js-support-ticket')); ?>
                        </span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title"><?php echo esc_html(__('Color 6', 'js-support-ticket')); ?></span>
                        <input type="text" name="color6" id="color6" value="<?php echo esc_attr(jssupportticket::$_data[0]['color6']); ?>" style="background:<?php echo esc_attr(jssupportticket::$_data[0]['color6']); ?>;" maxlength="15"/>
                        <span class="color_location"><?php echo esc_html(__('Button Color', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="color_portion">
                        <span class="color_title"><?php echo esc_html(__('Color 7', 'js-support-ticket')); ?></span>
                        <input type="text" name="color7" id="color7" value="<?php echo esc_attr(jssupportticket::$_data[0]['color7']); ?>" style="background:<?php echo esc_attr(jssupportticket::$_data[0]['color7']); ?>;" maxlength="15"/>
                        <span class="color_location"><?php echo esc_html(__('Top header text color', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="color_submit_button_hide">
                        <input type="hidden" name="form_request" value="jssupportticket" />
                    </div>
                </form>
            </div>
            <div class="js_effect_preview">
                <span class="js_effect_preview_heading"><?php echo esc_html(__('Color Effect Preview', 'js-support-ticket')); ?></span>
                <main class="js-admin-theme-page">
                    <div class="jsst-main-up-wrapper">
                        <div id="jsst-header-main-wrapper">
                            <div id="jsst-header" class="">
                                <div id="jsst-tabs-wrp" class=""><span class="jsst-header-tab js-ticket-homeclass"><a class="js-cp-menu-link" href="#"><?php echo esc_html(__('Dashboard', 'js-support-ticket')); ?></a></span><span class="jsst-header-tab js-ticket-openticketclass"><a class="js-cp-menu-link" href="#"><?php echo esc_html(__('Submit Ticket', 'js-support-ticket')); ?></a></span><span class="jsst-header-tab js-ticket-myticket"><a class="js-cp-menu-link" href="#"><?php echo esc_html(__('My Tickets', 'js-support-ticket')); ?></a></span><span class="jsst-header-tab js-ticket-loginlogoutclass"><a class="js-cp-menu-link" href="#"><?php echo esc_html(__('Log out', 'js-support-ticket')); ?></a></span></div>
                            </div>
                        </div>
                        <!-- Top Circle Count Boxes -->
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
                                    <span class="js-ticket-circle-count-text js-ticket-green"><?php echo esc_html(__('Open', 'js-support-ticket'));?>( 3 )</span>
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
                                    <span class="js-ticket-circle-count-text js-ticket-red"><?php echo esc_html(__('Closed', 'js-support-ticket'));?>( 0 )</span>
                                </a>
                            </div>
                            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                                <a class="js-ticket-brown js-myticket-link " href="#" data-tab-number="3">
                                    <div class="js-ticket-cricle-wrp" data-per="25">
                                        <div class="js-mr-rp" data-progress="25">
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
                                    <span class="js-ticket-circle-count-text js-ticket-brown"><?php echo esc_html(__('Answered', 'js-support-ticket'));?>( 1 )</span>
                                </a>
                            </div>
                            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                                <a class="js-ticket-blue js-myticket-link " href="#" data-tab-number="4">
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
                                    <span class="js-ticket-circle-count-text js-ticket-blue"><?php echo esc_html(__('All Tickets', 'js-support-ticket'));?>( 3 )</span>
                                </a>
                            </div>
                        </div>
                        <!-- Search Form -->
                        <div class="js-ticket-search-wrp">
                            <div class="js-ticket-form-wrp">
                                <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="POST"
                                    action="#">
                                    <div class="js-filter-wrapper">
                                        <div class="js-filter-form-fields-wrp js-col-md-7" id="js-filter-wrapper-toggle-search">
                                                <input type="text" name="jsst-ticketsearchkeys" id="jsst-ticketsearchkeys" value="" class="js-ticket-input-field" placeholder="<?php echo esc_html(__('Ticket ID', 'js-support-ticket')) . ' ' . esc_html(__('Or', 'js-support-ticket')) . ' ' . esc_html(__('Email', 'js-support-ticket')) . ' ' . esc_html(__('Or', 'js-support-ticket')) . ' ' . esc_html(__('Subject', 'js-support-ticket'));?>" />
                                        </div>
                                        <div class="js-col-md-5 js-filter-button-wrp">
                                            <a href="#" class="js-search-filter-btn" id="js-search-filter-toggle-btn"><?php echo esc_html(__('Show All', 'js-support-ticket'));?></a>
                                            <input type="submit" name="jsst-goto" id="jsst-go" value="<?php echo esc_html(__('Search', 'js-support-ticket'));?>"
                                                class="js-ticket-filter-button js-ticket-search-btn"/><input type="submit"
                                                name="jsst-reset" id="jsst-reset" value="<?php echo esc_html(__('Reset', 'js-support-ticket'));?>"
                                                class="js-ticket-filter-button js-ticket-reset-btn" onclick="return resetForm();" />
                                        </div>
                                    </div>
                                    <input type="hidden" name="sortby" id="sortby" value="" /> <input type="hidden" name="list"
                                        id="list" value="1" /> <input type="hidden" name="JSST_form_search" id="JSST_form_search"
                                        value="JSST_SEARCH" /> <input type="hidden" name="jsstpageid" id="jsstpageid" value="5" />
                                    <input type="hidden" name="jshdlay" id="jshdlay" value="myticket" />
                                </form>
                            </div>
                        </div>
                        <!-- Sorting Wrapper -->
                        <div class="js-ticket-sorting js-col-md-12">
                            <div class="js-ticket-sorting-left">
                                <div class="js-ticket-sorting-heading"><?php echo esc_html(__('All Tickets', 'js-support-ticket'));?></div>
                            </div>
                            <div class="js-ticket-sorting-right">
                                <div class="js-ticket-sort">
                                    <select class="js-ticket-sorting-select"><?php echo esc_html(__(' Subject', 'js-support-ticket'));?><option value="subjectdesc"><?php echo esc_html(__(' Subject', 'js-support-ticket'));?></option>
                                        <option value="prioritydesc"><?php echo esc_html(__('Priority', 'js-support-ticket'));?> </option>
                                        <option value="ticketiddesc"><?php echo esc_html(__('Ticket ID', 'js-support-ticket'));?> </option>
                                        <option value="isanswereddesc"><?php echo esc_html(__('Answered', 'js-support-ticket'));?> </option>
                                        <option value="statusasc" selected><?php echo esc_html(__('Status', 'js-support-ticket'));?> </option>
                                        <option value="createddesc"><?php echo esc_html(__('Created', 'js-support-ticket'));?> </option>
                                    </select>
                                    <a href="#" class="js-admin-sort-btn" title="<?php echo esc_html(__('sort', 'js-support-ticket'));?>">
                                        <img decoding="async" alt="<?php echo esc_html(__('sort', 'js-support-ticket'));?>"
                                            src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/sorting-2.png">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-toparea">
                                <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                                <img alt="<?php echo esc_html(__('user', 'js-support-ticket'));?>" src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/user.png' class='avatar avatar-96 photo' height='96' width='96' />
                                </div>
                                <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                        <span class="js-ticket-value"><?php echo esc_html(__('Kylee Arroyo', 'js-support-ticket'));?></span>
                                    </div>
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <a class="js-ticket-title-anchor" href="#"><?php echo esc_html(__('Can I upgrade my plan?', 'js-support-ticket'));?></a>
                                    </div>
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <span class="js-ticket-field-title"><?php echo esc_html(__('Department', 'js-support-ticket'));?>&nbsp;:&nbsp;</span>
                                        <span class="js-ticket-value"><?php echo esc_html(__('Support', 'js-support-ticket'));?></span>
                                    </div>
                                    <div class="js-ticket-status-text-wrap">
                                        <span class="js-ticket-wrapper-textcolor" style="background:#ed8e00;"><?php echo esc_html(__('High', 'js-support-ticket'));?></span>
                                        <span class="js-ticket-status" style="color:#5bb12f;"><img decoding="async" class="ticketstatusimage one"src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/over-due.png"title="<?php echo esc_html(__('This ticket is marked as overdue ', 'js-support-ticket'));?>" /><?php echo esc_html(__('New', 'js-support-ticket'));?></span>
                                    </div>

                                </div>
                                <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit"><?php echo esc_html(__('Ticket ID', 'js-support-ticket'));?> : </div>
                                        <div class="js-ticket-data-val"><?php echo esc_html(__('pF3YczjML', 'js-support-ticket'));?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit"><?php echo esc_html(__('Created', 'js-support-ticket'));?>:</div>
                                        <div class="js-ticket-data-val"><?php echo esc_html(__('01-11-2023', 'js-support-ticket'));?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-toparea">
                                <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                                    <img alt="<?php echo esc_html(__('user', 'js-support-ticket'));?>" src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/user.png' class='avatar avatar-96 photo' height='96' width='96' />
                                </div>
                                <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                        <span class="js-ticket-value"><?php echo esc_html(__('Allison Carney', 'js-support-ticket'));?></span>
                                    </div>
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <a class="js-ticket-title-anchor" href="#"><?php echo esc_html(__('How Can I get Subscription?', 'js-support-ticket'));?></a>
                                    </div>
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <span class="js-ticket-field-title"><?php echo esc_html(__('Department', 'js-support-ticket'));?>&nbsp;:&nbsp;</span>
                                        <span class="js-ticket-value"><?php echo esc_html(__('Support', 'js-support-ticket'));?></span>
                                    </div>
                                    <div class="js-ticket-status-text-wrap">
                                    <span class="js-ticket-wrapper-textcolor" style="background:#c7cbf5;"><?php echo esc_html(__('Normal', 'js-support-ticket'));?></span>
                                        <span class="js-ticket-status replied-status" style="color:#FFB613;"><?php echo esc_html(__('Replied', 'js-support-ticket'));?></span>
                                    </div>
                                </div>
                                <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit"><?php echo esc_html(__('Ticket ID', 'js-support-ticket'));?> : </div>
                                        <div class="js-ticket-data-val"><?php echo esc_html(__('Tp3Y7zj01', 'js-support-ticket'));?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit"><?php echo esc_html(__('Created', 'js-support-ticket'));?> : </div>
                                        <div class="js-ticket-data-val"><?php echo esc_html(__('20-12-2023', 'js-support-ticket'));?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-toparea">
                                <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                                <img alt="<?php echo esc_html(__('user', 'js-support-ticket'));?>" src='<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/user.png' class='avatar avatar-96 photo' height='96' width='96' />
                                </div>
                                <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                        <span class="js-ticket-value"><?php echo esc_html(__('Aleena Adam', 'js-support-ticket'));?></span>
                                    </div>
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <a class="js-ticket-title-anchor" href="#"><?php echo esc_html(__('How Long do i have support access?', 'js-support-ticket'));?></a>
                                    </div>
                                    <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                        <span class="js-ticket-field-title"><?php echo esc_html(__('Department', 'js-support-ticket'));?>&nbsp;:&nbsp;</span>
                                        <span class="js-ticket-value"><?php echo esc_html(__('Support', 'js-support-ticket'));?></span>
                                    </div>
                                    <div class="js-ticket-status-text-wrap">
                                        <span class="js-ticket-wrapper-textcolor" style="background:#86f793;"><?php echo esc_html(__('Low', 'js-support-ticket'));?></span>
                                        <span class="js-ticket-status" style="color:#5bb12f;"><?php echo esc_html(__('New', 'js-support-ticket'));?></span>
                                    </div>
                                </div>
                                <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit"><?php echo esc_html(__('Ticket ID', 'js-support-ticket'));?> : </div>
                                        <div class="js-ticket-data-val"><?php echo esc_html(__('Ku3Y8zj3L', 'js-support-ticket'));?></div>
                                    </div>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit"><?php echo esc_html(__('Created', 'js-support-ticket'));?> : </div>
                                        <div class="js-ticket-data-val"><?php echo esc_html(__('11-6-2023', 'js-support-ticket'));?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div class="color_submit_button">
                <a class="js-color-submit-button" href="#" onclick="document.getElementById('adminForm').submit();" >
                    <?php echo esc_html(__('Save Theme','majestic-support')); ?>
                </a>
                <div class="js-sugestion-alert-wrp">
                    <div class="js-sugestion-alert">
                        <strong><?php echo esc_html(__('Note:','js-support-ticket'));?></strong>
                        <?php echo esc_html(__('If the colors have been saved but the user-side colors are still the same, it is advised to clear the cache.','js-support-ticket'));?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $jssupportticket_js ="
            jQuery(document).ready(function () {
                makeColorPicker('". esc_js(jssupportticket::$_data[0]['color1']) ."', '". esc_js(jssupportticket::$_data[0]['color2']) ."', '". esc_js(jssupportticket::$_data[0]['color3']) ."', '". esc_js(jssupportticket::$_data[0]['color4']) ."', '". esc_js(jssupportticket::$_data[0]['color5']) ."', '". esc_js(jssupportticket::$_data[0]['color6']) ."', '". esc_js(jssupportticket::$_data[0]['color7']) ."');
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
                        hex = ui.color.toString();
                        jQuery('input#color1').css('background-color', hex);
                        jQuery('div#jsst-header span.jsst-header-tab.active a.js-cp-menu-link').css('background-color', hex);
                        jQuery('div#jsst-header').css('background-color', hex);
                        jQuery('.js-ticket-search-btn').css('background-color', hex);
                        jQuery('.js-ticket-title-anchor').css('color', hex);
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('background-color', hex);
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
                        jQuery('input#color2').css('background-color', hex);
                        jQuery('.js-ticket-reset-btn').css('background-color', hex);
                        jQuery('div.js-ticket-sorting').css('background-color', hex);
                        jQuery('.js-ticket-data-tit').css('color', hex);
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a.selected').css('background-color', jQuery('input#color2').val());
                        jQuery('div.js-ticket-flat a.active').css('borderColor', jQuery('input#color2').val());
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').mouseover(function () {
                            jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('background-color', jQuery('input#color2').val());
                        }).mouseout(function () {
                            jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('background-color', jQuery('input#color5').val());
                        });
                        jQuery('a.js-ticket-title-anchor').mouseover(function () {
                            jQuery('a.js-ticket-title-anchor').css('color', jQuery('input#color2').val());
                        }).mouseout(function () {
                            jQuery('a.js-ticket-title-anchor').css('color', jQuery('input#color5').val());
                        });
                        jQuery('div.js-ticket-flat a').mouseover(function () {
                            jQuery('div.js-ticket-flat a').css('background-color', jQuery('input#color2').val());
                        }).mouseout(function () {
                            jQuery('div.js-ticket-flat a').css('background-color', jQuery('input#color5').val());
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
                        jQuery('input#color3').css('background-color', hex);
                        jQuery('div#jsst-header div#jsst-header-heading').css('color', hex);
                        jQuery('div.js-ticket-assigned-tome').css('background-color', hex);
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
                        jQuery('input#color4').css('background-color', hex);
                        jQuery('div.js-ticket-breadcrumb-wrp .breadcrumb li a').css('color', hex);
                        jQuery('div.js-ticket-wrapper div.js-ticket-data span.js-ticket-title').css('color', hex);
                        jQuery('div.js-ticket-wrapper div.js-ticket-data span.js-ticket-value').css('color', hex);
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
                        jQuery('input#color5').css('background-color', hex);
                        jQuery('div.js-ticket-wrapper').css('border-color', hex);
                        jQuery('div.js-ticket-wrapper div.js-ticket-pic').css('border-color', hex);
                        jQuery('div.js-ticket-wrapper div.js-ticket-data1').css('border-color', hex);
                        jQuery('div.js-ticket-assigned-tome').css('border-color', hex);
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
                        jQuery('input#color6').css('background-color', hex);
                        jQuery('a.js-myticket-link').css('background-color', hex);
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
                        hex = ui.color.toString();
                        jQuery('input#color7').css('background-color', hex);
                        jQuery('a.js-myticket-link,span.js-ticket-sorting-link a').each(function () {
                            jQuery(this).css('color', hex)
                        });
                        jQuery('a.js-ticket-header-links').mouseover(function () {
                            jQuery('a.js-ticket-header-links').css('color', jQuery('input#color7').val());
                        }).mouseout(function () {
                            jQuery('a.js-ticket-header-links').css('color', jQuery('input#color7').val());
                        });
                        jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link').mouseover(function () {
                            jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link').css('color', jQuery('input#color7').val());
                        }).mouseout(function () {
                            jQuery('div#jsst-header span.jsst-header-tab a.js-cp-menu-link').css('color', jQuery('input#color7').val());
                        });
                        jQuery('input#color7').css('background-color', hex);
                        jQuery('div#jsst-header span.jsst-header-tab.active a.js-cp-menu-link').css('color', hex);
                        jQuery('div.js-ticket-sorting span.js-ticket-sorting-link a').css('color', hex);
                        jQuery('div#jsst-header div#jsst-header-heading a').css('color', hex);
                    }
                });

            }
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
        <div id="black_wrapper_jobapply" style="display:none;"></div>
        <div id="js_jobapply_main_wrapper" style="display:none;padding:0px 5px;">
            <div id="js_job_wrapper">
                <span class="js_job_controlpanelheading"><?php echo esc_html(__('Preset Theme', 'js-support-ticket')); ?></span>
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
                            <img class="preview" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview1.png" />
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
                            <img class="preview" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview2.png" />
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
                            <img class="preview" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview3.png" />
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
                            <img class="preview" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview4.png" />
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
                            <img class="preview" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview5.png" />
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
                            <img class="preview" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview6.png" />
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
                            <img class="preview" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/themes/preview7.png" />
                            <a href="#" class="preview"></a>
                            <a href="#" class="set_theme"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $jssupportticket_js ='
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
                        jQuery("input#color1").val(color1).css("backgroundColor", color1);
                        jQuery("input#color2").val(color2).css("backgroundColor", color2);
                        jQuery("input#color3").val(color3).css("backgroundColor", color3);
                        jQuery("input#color4").val(color4).css("backgroundColor", color4);
                        jQuery("input#color5").val(color5).css("backgroundColor", color5);
                        jQuery("input#color6").val(color6).css("backgroundColor", color6);
                        jQuery("input#color7").val(color7).css("backgroundColor", color7);
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
                    jQuery(this).css("backgroundColor", jQuery("input#color2").val());
                });
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").mouseout(function () {
                    jQuery(this).css("backgroundColor", jQuery("input#color1").val());
                });
                jQuery("div#jsst-header").css("backgroundColor", jQuery("input#color1").val());
                jQuery(".js-ticket-search-btn").css("backgroundColor", jQuery("input#color1").val());
                jQuery(".js-ticket-title-anchor").css("color", jQuery("input#color1").val());
                jQuery(".js-ticket-reset-btn").css("background-color", jQuery("input#color2").val());
                jQuery("div.js-ticket-sorting").css("backgroundColor", jQuery("input#color2").val());
                jQuery(".js-ticket-data-tit").css("color", jQuery("input#color2").val());
                jQuery("span.jsst-header-tab a").mouseover(function () {
                    jQuery(this).css("color", jQuery("input#color2").val());
                });
                jQuery("span.jsst-header-tab a").mouseout(function () {
                    jQuery(this).css("color", jQuery("input#color7").val());
                });
                jQuery("span.jsst-header-tab.active a").css("color", jQuery("input#color3").val());
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").css("backgroundColor", jQuery("input#color1").val());
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").mouseover(function () {
                    jQuery(this).css("backgroundColor", jQuery("input#color2").val());
                });
                jQuery("div.js-ticket-sorting span.js-ticket-sorting-link a").mouseout(function () {
                    jQuery(this).css("backgroundColor", jQuery("input#color1").val());
                });
                jQuery("span.js-ticket-title").css("color", jQuery("input#color4").val());
                jQuery("span.js-ticket-value").css("color", jQuery("input#color4").val());
                jQuery("div.js-ticket-data1").css("color", jQuery("input#color4").val());
                jQuery("span.js-ticket-sorting-link a").each(function () {
                    jQuery(this).css("color", jQuery("input#color7").val())
                });
            }
        ';
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
    </div>
</div>
