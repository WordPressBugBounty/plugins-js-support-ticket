<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
if(isset($_SESSION['jsst_addon_install_data'])){
    unset($_SESSION['jsst_addon_install_data']);
}
?>
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
                            <li><?php echo esc_html(__('Install Addons','js-support-ticket')); ?></li>
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
                <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Install Addons','js-support-ticket')); ?></h1>
            </div>
            <div id="jsstadmin-data-wrp" class="p0">
                <div id="jssupportticket-content">
                    <div id="black_wrapper_translation"></div>
                    <div id="jstran_loading">
                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
                    </div>
                    <div id="jsst-lower-wrapper">
                        <div class="jsst-addon-installer-wrapper" >
                            <form id="jsticketfrom" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=premiumplugin&task=verifytransactionkey&action=jstask'),"verify-transaction-key")); ?>" method="post">
                                <div class="jsst-addon-installer-left-section-wrap" >
                                    <div class="jsst-addon-installer-left-image-wrap" >
                                        <img class="jsst-addon-installer-left-image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/addon-images/addon-installer-logo.png" />
                                    </div>
                                    <div class="jsst-addon-installer-left-heading" >
                                        <?php echo esc_html(__("JS Help Desk","js-support-ticket")); ?>
                                    </div>
                                    <div class="jsst-addon-installer-left-title" >
                                        <?php echo esc_html(__("Wordpress Plugin","js-support-ticket")); ?>
                                    </div>
                                    <div class="jsst-addon-installer-left-description" >
                                        <?php echo esc_html(__("JS Help Desk is a trusted open source ticket system. JS Help Desk is a simple, easy to use, web-based customer support system. Users can create a ticket from the front-end. JS Help Desk comes packed with lot features than most of the expensive(and complex) support ticket system on the market. The best part is, It completely free.","js-support-ticket")); ?>
                                    </div>
                                </div>
                                <div class="jsst-addon-installer-right-section-wrap" >
                                    <div class="jsst-addon-installer-right-heading" >
                                        <?php echo esc_html(__("JS Help Desk Addon Installer","js-support-ticket")); ?>
                                    </div>
                                    <div class="jsst-addon-installer-right-description" >
                                        >> <a class="jsst-addon-installer-install-btn" href="?page=premiumplugin&jstlay=addonfeatures" class="jsst-addon-installer-addon-list-link" >
                                            <?php echo esc_html(__("Add on list","js-support-ticket")); ?>
                                        </a> <<
                                    </div>
                                    <div class="jsst-addon-installer-right-key-section" >
                                        <div class="jsst-addon-installer-right-key-label" >
                                            <?php echo esc_html(__("Please Insert Your Activation key","js-support-ticket")); ?>.
                                        </div>

                                        <?php
                                        $error_message = '';
                                        $transactionkey = '';
                                        if(isset($_COOKIE['jsst_addon_return_data'])){
                                            $jsst_addon_return_data = json_decode(base64_decode(jssupportticket::JSST_sanitizeData($_COOKIE['jsst_addon_return_data'])),true); // JSST_sanitizeData() function uses wordpress santize functions
                                            $jsst_error_msg = $jsst_addon_return_data;
                                            if(isset($jsst_addon_return_data['status']) && $jsst_addon_return_data['status'] == 0){
                                                $error_message = $jsst_addon_return_data['message'];
                                                $transactionkey = $jsst_addon_return_data['transactionkey'];
                                            }
                                            unset($jsst_addon_return_data);
                                        }
                                        ?>
                                        <div class="jsst-addon-installer-right-key-field" >
                                            <input type="text" name="transactionkey" id="transactionkey" class="jsst_key_field" value="<?php echo esc_attr($transactionkey);?>" placeholder="<?php echo esc_html(__('Activation key','js-support-ticket')); ?>"/>
                                            <?php if($error_message != '' ){ ?>
                                                <div class="jsst-addon-installer-right-key-field-message" > <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/icon.png" /> <?php echo wp_kses_post($error_message);?></div>
                                            <?php } ?>
                                        </div>
                                        <div class="jsst-addon-installer-right-key-button" >
                                            <button type="submit" class="jsst_btn" role="submit" onclick="jsShowLoading();"><?php echo esc_html(__("Proceed","js-support-ticket")); ?></button>
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
$jssupportticket_js ="
    jQuery(document).ready(function(){
        jQuery('#jsticketfrom').on('submit', function() {
            jsShowLoading();
        });
    });

    function jsShowLoading(){
        jQuery('div#black_wrapper_translation').show();
        jQuery('div#jstran_loading').show();
    }
    ";
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
