<?php
   if(!defined('ABSPATH')){
    die('Restricted Access');
}
    require_once JSST_PLUGIN_PATH.'includes/addon-updater/jsstupdater.php';
    $jsst_JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
    $jsst_cdnversiondata = $jsst_JS_SUPPORTTICKETUpdater->getPluginVersionDataFromCDN();
    $jsst_not_installed = array();

    $jsst_jssupportticket_addons = JSSTincluder::getJSModel('jssupportticket')->getJSSTAddonsArray();
?>
<?php JSSTmessage::getMessage(); ?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
    	<div id="jsstadmin-wrapper-top">
    	    <div id="jsstadmin-wrapper-top-left">
    	        <div id="jsstadmin-breadcrunbs">
    	            <ul>
    	                <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
    	                <li><?php echo esc_html(__('Addons Status','js-support-ticket')); ?></li>
    	            </ul>
    	        </div>
    	    </div>
    	    <div id="jsstadmin-wrapper-top-right">
    	        <div id="jsstadmin-config-btn">
    	            <a href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>" title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>">
    	                <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
    	            </a>
    	        </div>
    	        <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
    	            <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
    	                <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
    	            </a>
    	        </div>
    	        <div id="jsstadmin-vers-txt">
    	            <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
    	            <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
    	        </div>
    	    </div>
    	</div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Addons Status','js-support-ticket')); ?></h1>
        </div>

    	<div id="jsstadmin-data-wrp" class="jsstadmin-addons-list-data">
            <div class="jsstadmin-autoupdte-addons-title">
                <?php echo esc_html(__('Auto Update Add-Ons','js-support-ticket')); ?>
            </div>
            <div class="jsstadmin-autoupdte-addons-cardwrp">
                <div class="jsstadmin-autoupdte-addons-cardlogo">
                    <img alt = "<?php echo esc_attr(__('Auto Update','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/addon-images/addons/icon.png" />
                </div>
                <div class="jsstadmin-autoupdte-addons-cardwrp-rightwrp">
                    <div class="jsstadmin-autoupdte-addons-card-title">
                        <?php echo esc_html(__('Addon will automatically update to the newest version','js-support-ticket')); ?>
                    </div>
                    <?php
                    $jsst_addons_auto_update = jssupportticket::$_config['jsst_addons_auto_update'];
                    if($jsst_addons_auto_update == 1 ){ ?>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=configuration&task=saveautoupdateconfiguration&action=jstask&jsst_addons_auto_update=0'),'jsst_configuration_nonce')); ?>" class="jsstadmin-autoupdte-addons-card-btn">
                            <?php echo esc_html(__('Auto Update','js-support-ticket')).': '.esc_html(__('On','js-support-ticket')); ?>
                        </a>
                        <?php
                    } else { ?>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=configuration&task=saveautoupdateconfiguration&action=jstask&jsst_addons_auto_update=1'),'jsst_configuration_nonce')); ?>"  class="jsstadmin-autoupdte-addons-card-btn jsstadmin-autoupdte-addons-card-offbtn">
                            <?php echo esc_html(__('Auto Update','js-support-ticket')).': '.esc_html(__('Off','js-support-ticket')); ?>
                        </a>
                        <?php
                    } ?>
                </div>
            </div>
            <div class="jsstadmin-addons-alladdon-title">
                <?php echo esc_html(__('Add-Ons','js-support-ticket')); ?>
            </div>
    		<!-- admin addons status -->
            <div id="black_wrapper_translation"></div>
            <div id="jstran_loading">
                <img alt = "<?php echo esc_attr(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
            </div>
            <div class="jsstadmin-addons-list-wrp">
                <?php
                $jsst_installed_plugins = get_plugins();
                ?>
                <?php
                    foreach ($jsst_jssupportticket_addons as $jsst_key1 => $jsst_value1) {
                        $jsst_matched = 0;
                        $jsst_version = "";
                        foreach ($jsst_installed_plugins as $jsst_name => $jsst_value) {
                            $jsst_install_plugin_name = str_replace(".php","",basename($jsst_name));
                            if($jsst_key1 == $jsst_install_plugin_name){
                                $jsst_matched = 1;
                                $jsst_version = $jsst_value["Version"];
                                $jsst_install_plugin_matched_name = $jsst_install_plugin_name;
                            }
                        }
                        $jsst_status = '';
                        if($jsst_matched == 1){ //installed
                            $jsst_name = $jsst_key1;
                            $jsst_title = $jsst_value1['title'];
                            $jsst_img = str_replace("js-support-ticket-", "", $jsst_key1).'.png';
                            $jsst_cdnavailableversion = "";
                            foreach ($jsst_cdnversiondata as $jsst_cdnname => $jsst_cdnversion) {
                                $jsst_install_plugin_name_simple = str_replace("-", "", $jsst_install_plugin_matched_name);
                                if($jsst_cdnname == str_replace("-", "", $jsst_install_plugin_matched_name)){
                                    if($jsst_cdnversion > $jsst_version){ // new version available
                                        $jsst_status = 'update_available';
                                        $jsst_cdnavailableversion = $jsst_cdnversion;
                                    }else{
                                        $jsst_status = 'updated';
                                    }
                                }    
                            }
                            JSST_PrintAddoneStatus($jsst_name, $jsst_title, $jsst_img, $jsst_version, $jsst_status, $jsst_cdnavailableversion);
                        }else{ // not installed
                            $jsst_img = str_replace("js-support-ticket-", "", $jsst_key1).'.png';
                            $jsst_not_installed[] = array("name" => $jsst_key1, "title" => $jsst_value1['title'], "img" => $jsst_img, "status" => 'not-installed', "version" => "---");
                        }
                    }
                    foreach ($jsst_not_installed as $jsst_notinstall_addon) {
                        JSST_PrintAddoneStatus($jsst_notinstall_addon["name"], $jsst_notinstall_addon["title"], $jsst_notinstall_addon["img"], $jsst_notinstall_addon["version"], $jsst_notinstall_addon["status"]);
                    }
                ?>
            </div>
		</div>
	</div>
</div>

<?php
function JSST_PrintAddoneStatus($jsst_name, $jsst_title, $jsst_img, $jsst_version, $jsst_status, $jsst_cdnavailableversion = ''){
    $jsst_addoneinfo = JSSTincluder::getJSModel('jssupportticket')->checkJSSTAddoneInfo($jsst_name);
    if ($jsst_status == 'update_available') {
        $jsst_wrpclass = 'jsst-admin-addon-status jsst-admin-addons-status-update-wrp';
        $jsst_btnclass = 'jsst-admin-addons-update-btn';
        $jsst_btntxt = 'Update Now';
        $jsst_btnlink = 'id="jsst-admin-addons-update" data-for="'.$jsst_name.'"';
        $jsst_msg = '<span id="jsst-admin-addon-status-cdnversion">'.esc_html(__('New Update Version','js-support-ticket'));
        $jsst_msg .= '<span>'." ".$jsst_cdnavailableversion." ".'</span>';
        $jsst_msg .= esc_html(__('is Available','js-support-ticket')).'</span>';
    } elseif ($jsst_status == 'expired') {
        $jsst_wrpclass = 'jsst-admin-addon-status jsst-admin-addons-status-expired-wrp';
        $jsst_btnclass = 'jsst-admin-addons-expired-btn';
        $jsst_btntxt = 'Expired';
        $jsst_btnlink = '';
        $jsst_msg = '';
    } elseif ($jsst_status == 'updated') {
        $jsst_wrpclass = 'jsst-admin-addon-status';
        $jsst_btnclass = '';
        $jsst_btntxt = 'Updated';
        $jsst_btnlink = '';
        $jsst_msg = '';
    } else {
        $jsst_wrpclass = 'jsst-admin-addon-status';
        $jsst_btnclass = 'jsst-admin-addons-buy-btn';
        $jsst_btntxt = 'Buy Now';
        $jsst_btnlink = 'href="https://jshelpdesk.com/add-ons/"';
        $jsst_msg = '';
    }
    $jsst_html = '
    <div class="'.$jsst_wrpclass.'" id="'.$jsst_name.'">
        <div class="jsst-addon-status-image-wrp">
            <img alt="Addone image" src="'.esc_url(JSST_PLUGIN_URL).'includes/images/admincp/addon/'.$jsst_img.'" />
        </div>
        <div class="jsst-admin-addon-status-title-wrp">
            <h2>'. jssupportticket::JSST_getVarValue($jsst_title) .'</h2>
            <a class="'. $jsst_addoneinfo["actionClass"] .'" href="'. $jsst_addoneinfo["url"] .'">
                '. jssupportticket::JSST_getVarValue($jsst_addoneinfo["action"]) .'
            </a>
            '.$jsst_msg.'
        </div>
        <div class="jsst-admin-addon-status-addonstatus-wrp">
            <span>'. esc_html(__('Status: ','js-support-ticket')) .'</span>
            <span class="jsst-admin-adons-status-Active" href="#">
                '. jssupportticket::JSST_getVarValue($jsst_addoneinfo["status"]) .'
            </span>
        </div>
        <div class="jsst-admin-addon-status-addonsversion-wrp">
            <span id="jsst-admin-addon-status-cversion">
                '. esc_html(__('Version','js-support-ticket')).': 
                <span>
                    '. $jsst_version .'
                </span>
            </span>
        </div>
        <div class="jsst-admin-addon-status-addonstatusbtn-wrp">
            <a '.$jsst_btnlink.' class="'.$jsst_btnclass.'">'. jssupportticket::JSST_getVarValue($jsst_btntxt) .'</a>
        </div>
        <div class="jsst-admin-addon-status-msg jsst_admin_success">
            <img src="'. esc_url(JSST_PLUGIN_URL) .'includes/images/admincp/addon/success.png" />
            <span class="jsst-admin-addon-status-msg-txt"></span>
        </div>
        <div class="jsst-admin-addon-status-msg jsst_admin_error">
            <img src="'. esc_url(JSST_PLUGIN_URL) .'includes/images/admincp/addon/error.png" />
            <span class="jsst-admin-addon-status-msg-txt"></span>
        </div>
    </div>';
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

?>

<script>
    jQuery(document).ready(function(){
        jQuery(document).on("click", "a#jsst-admin-addons-update", function(){
            jsShowLoading();
            var dataFor = jQuery(this).attr("data-for");
            var cdnVer = jQuery('#'+ dataFor +' #jsst-admin-addon-status-cdnversion span').text();
            var currentVer = jQuery('#'+ dataFor +' #jsst-admin-addon-status-cversion span').text();
            var cdnVersion = cdnVer.trim();
            var currentVersion = currentVer.trim();
            jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'JSSTdownloadandinstalladdonfromAjax', dataFor:dataFor, currentVersion:currentVersion, cdnVersion:cdnVersion, '_wpnonce':'<?php echo esc_attr(wp_create_nonce("download-and-install-addon")); ?>'}, function (data) {
                if (data) {
                    jsHideLoading();
                    data = JSON.parse(data);
                    if(data['error']){
                        jQuery('#' + dataFor).css('background-color', '#fff');
                        jQuery('#' + dataFor).css('border-color', '#FF4F4E');
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-title-wrp span').hide();
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-msg.jsst_admin_error').show();
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-msg.jsst_admin_error span.jsst-admin-addon-status-msg-txt').html(data['error']);
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-msg.mjsst_admin_error').slideDown('slow');
                    } else if(data['success']) {
                        jQuery('#' + dataFor).css('background-color', '#fff');
                        jQuery('#' + dataFor).css('border-color', '#0C6E45');
                        jQuery('#' + dataFor + ' a#jsst-admin-addons-update').hide();
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-title-wrp span').hide();
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-msg.jsst_admin_success').show();
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-msg.jsst_admin_success span.jsst-admin-addon-status-msg-txt').html(data['success']);
                        jQuery('#' + dataFor + ' .jsst-admin-addon-status-msg.jsst_admin_success').slideDown('slow');
                    }
                }
            });
        });
    });
    function jsShowLoading(){
        jQuery('div#black_wrapper_translation').show();
        jQuery('div#jstran_loading').show();
    }

    function jsHideLoading(){
        jQuery('div#black_wrapper_translation').hide();
        jQuery('div#jstran_loading').hide();
    }
</script>
