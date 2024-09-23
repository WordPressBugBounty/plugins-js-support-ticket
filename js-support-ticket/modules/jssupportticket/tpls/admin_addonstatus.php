<?php
   if(!defined('ABSPATH')){
    die('Restricted Access');
}
    require_once JSST_PLUGIN_PATH.'includes/addon-updater/jsstupdater.php';
    $JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
    $cdnversiondata = $JS_SUPPORTTICKETUpdater->getPluginVersionDataFromCDN();
    $not_installed = array();

    $jssupportticket_addons = JSSTincluder::getJSModel('jssupportticket')->getJSSTAddonsArray();
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
    	                <li><?php echo esc_html(__('Addons Status','js-support-ticket')); ?></li>
    	            </ul>
    	        </div>
    	    </div>
    	    <div id="jsstadmin-wrapper-top-right">
    	        <div id="jsstadmin-config-btn">
    	            <a href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>" title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>">
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Addons Status','js-support-ticket')); ?></h1>
        </div>
    	<div id="jsstadmin-data-wrp" class="jsstadmin-addons-list-data">
    		<!-- admin addons status -->
            <div id="black_wrapper_translation"></div>
            <div id="jstran_loading">
                <img alt="<?php echo esc_html(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
            </div>
            <div class="jsstadmin-addons-list-wrp">
                <?php
                $installed_plugins = get_plugins();
                ?>
                <?php
                    foreach ($jssupportticket_addons as $key1 => $value1) {
                        $matched = 0;
                        $version = "";
                        foreach ($installed_plugins as $name => $value) {
                            $install_plugin_name = str_replace(".php","",basename($name));
                            if($key1 == $install_plugin_name){
                                $matched = 1;
                                $version = $value["Version"];
                                $install_plugin_matched_name = $install_plugin_name;
                            }
                        }
                        if($matched == 1){ //installed
                            $name = $key1;
                            $title = $value1['title'];
                            $img = str_replace("js-support-ticket-", "", $key1).'.png';
                            $cdnavailableversion = "";
                            foreach ($cdnversiondata as $cdnname => $cdnversion) {
                                $install_plugin_name_simple = str_replace("-", "", $install_plugin_matched_name);
                                if($cdnname == str_replace("-", "", $install_plugin_matched_name)){
                                    if($cdnversion > $version){ // new version available
                                        $status = 'update_available';
                                        $cdnavailableversion = $cdnversion;
                                    }else{
                                        $status = 'updated';
                                    }
                                }    
                            }
                            JSST_PrintAddoneStatus($name, $title, $img, $version, $status, $cdnavailableversion);
                        }else{ // not installed
                            $img = str_replace("js-support-ticket-", "", $key1).'.png';
                            $not_installed[] = array("name" => $key1, "title" => $value1['title'], "img" => $img, "status" => 'not-installed', "version" => "---");
                        }
                    }
                    foreach ($not_installed as $notinstall_addon) {
                        JSST_PrintAddoneStatus($notinstall_addon["name"], $notinstall_addon["title"], $notinstall_addon["img"], $notinstall_addon["version"], $notinstall_addon["status"]);
                    }
                ?>
            </div>
		</div>
	</div>
</div>

<?php
function JSST_PrintAddoneStatus($name, $title, $img, $version, $status, $cdnavailableversion = ''){
    $addoneinfo = JSSTincluder::getJSModel('jssupportticket')->checkJSSTAddoneInfo($name);
    if ($status == 'update_available') {
        $wrpclass = 'jsst-admin-addon-status jsst-admin-addons-status-update-wrp';
        $btnclass = 'jsst-admin-addons-update-btn';
        $btntxt = 'Update Now';
        $btnlink = 'id="jsst-admin-addons-update" data-for="'.$name.'"';
        $msg = '<span id="jsst-admin-addon-status-cdnversion">'.esc_html(__('New Update Version','js-support-ticket'));
        $msg .= '<span>'." ".$cdnavailableversion." ".'</span>';
        $msg .= esc_html(__('is Available','js-support-ticket')).'</span>';
    } elseif ($status == 'expired') {
        $wrpclass = 'jsst-admin-addon-status jsst-admin-addons-status-expired-wrp';
        $btnclass = 'jsst-admin-addons-expired-btn';
        $btntxt = 'Expired';
        $btnlink = '';
        $msg = '';
    } elseif ($status == 'updated') {
        $wrpclass = 'jsst-admin-addon-status';
        $btnclass = '';
        $btntxt = 'Updated';
        $btnlink = '';
        $msg = '';
    } else {
        $wrpclass = 'jsst-admin-addon-status';
        $btnclass = 'jsst-admin-addons-buy-btn';
        $btntxt = 'Buy Now';
        $btnlink = 'href="https://jshelpdesk.com/add-ons/"';
        $msg = '';
    }
    $html = '
    <div class="'.$wrpclass.'" id="'.$name.'">
        <div class="jsst-addon-status-image-wrp">
            <img alt="Addone image" src="'.esc_url(JSST_PLUGIN_URL).'includes/images/admincp/addon/'.$img.'" />
        </div>
        <div class="jsst-admin-addon-status-title-wrp">
            <h2>'. jssupportticket::JSST_getVarValue($title) .'</h2>
            <a class="'. $addoneinfo["actionClass"] .'" href="'. $addoneinfo["url"] .'">
                '. jssupportticket::JSST_getVarValue($addoneinfo["action"]) .'
            </a>
            '.$msg.'
        </div>
        <div class="jsst-admin-addon-status-addonstatus-wrp">
            <span>'. esc_html(__('Status: ','js-support-ticket')) .'</span>
            <span class="jsst-admin-adons-status-Active" href="#">
                '. jssupportticket::JSST_getVarValue($addoneinfo["status"]) .'
            </span>
        </div>
        <div class="jsst-admin-addon-status-addonsversion-wrp">
            <span id="jsst-admin-addon-status-cversion">
                '. esc_html(__('Version','js-support-ticket')).': 
                <span>
                    '. $version .'
                </span>
            </span>
        </div>
        <div class="jsst-admin-addon-status-addonstatusbtn-wrp">
            <a '.$btnlink.' class="'.$btnclass.'">'. jssupportticket::JSST_getVarValue($btntxt) .'</a>
        </div>
        <div class="jsst-admin-addon-status-msg jsst_admin_success">
            <img src="'. JSST_PLUGIN_URL .'includes/images/admincp/addon/success.png" />
            <span class="jsst-admin-addon-status-msg-txt"></span>
        </div>
        <div class="jsst-admin-addon-status-msg jsst_admin_error">
            <img src="'. JSST_PLUGIN_URL .'includes/images/admincp/addon/error.png" />
            <span class="jsst-admin-addon-status-msg-txt"></span>
        </div>
    </div>';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
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
