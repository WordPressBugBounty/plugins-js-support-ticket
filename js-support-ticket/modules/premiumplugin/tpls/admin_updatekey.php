<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div id="jsstadmin-wrapper" class="jsstadmin-add-on-page-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Update Key','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Update Key", 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <form class="jsstadmin-update-key-form" id="jsticketfrom" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=premiumplugin&task=updatetransactionkey&action=jstask'),"update-transaction-key")); ?>" method="post">
                <div class="jsstadmin-update-key-wrp">
                    <div class="jsstadmin-update-key-section">
                        <h2 class="jsstadmin-update-key-title"><?php echo esc_html(__("JS Helpdesk Activation Key", 'js-support-ticket')); ?></h2>
                        <input id="transactionkey" name="transactionkey" required type="text" placeholder="<?php echo esc_attr(__( "XXXXX-XXXXX-XXXXX-XXXXX", 'js-support-ticket' )); ?>" value="<?php echo isset( jssupportticket::$jsst_data['token'] ) ? esc_attr( jssupportticket::$jsst_data['token'] ) : ''; ?>">
                    </div>
                    <div class="jsstadmin-update-key-custom-errormsgwrp">
                    <?php
                        JSSTmessage::getMessage(); ?>
                    </div>
                    <?php
                    if (!empty(jssupportticket::$jsst_data['extra_addons'])) { ?>
                        <div class="jsstadmin-update-key-errormsgwrp">
                            <img alt = "<?php echo esc_attr(__("Info", 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/icon.png" />
                            <?php echo esc_html(__("The highlighted addons are not included in your current license. Please adjust your selection accordingly.", 'js-support-ticket')); ?>
                        </div>
                        <?php
                    } ?>
                    <div class="jsstadmin-update-key-slctall-addonswrp">
                        <span class="jsstadmin-update-key-slctall-addon-title"><?php echo esc_html(__("Select Addons to Update with New Activation Key", 'js-support-ticket')); ?></span>
                        <div class="jsstadmin-update-key-slctall-addon-checkbox-wrp">
                            <input class="jsstadmin-update-key-checkbox"id="select-all" type="checkbox">
                            <?php echo esc_html(__("Select All Addons", 'js-support-ticket')); ?>
                        </div>
                    </div>
                    <?php
                    $jsst_addon_array = [];

                    $jsst_all_plugins = get_plugins();
                    $jsst_extra_addons = jssupportticket::$jsst_data['extra_addons'];
                    $jsst_allowed_addons = jssupportticket::$jsst_data['allowed_addons'];
                    

                    foreach ($jsst_all_plugins as $jsst_plugin_file => $jsst_plugin_data) {
                        // Match plugin directory or main file starting with 'js-support-ticket-'
                        if (strpos($jsst_plugin_file, 'js-support-ticket-') === 0) {
                            $jsst_slug = dirname($jsst_plugin_file); // Gets 'js-support-ticket-actions'
                            $jsst_addon_array[$jsst_slug] = $jsst_plugin_data;
                        }
                    }
                    ?>
                    <div class="jsstadmin-update-key-all-addons-wrp">
                        <?php 
                        if (!empty($jsst_addon_array)) {
                            $jsst_jssupportticket_addons = JSSTincluder::getJSModel('jssupportticket')->getJSSTAddonsArray();
                            foreach ($jsst_addon_array as $jsst_key => $jsst_value) {
                                $jsst_error_class = '';
                                $jsst_isChecked = false;
                                if (!empty($jsst_extra_addons)) {
                                    if(jssupportticketphplib::JSST_strpos($jsst_extra_addons, $jsst_key) !== false) {
                                        $jsst_error_class = 'jsstadmin-update-key-single-addon-red';
                                    }
                                }
                                if (!empty($jsst_allowed_addons)) {
                                    if(jssupportticketphplib::JSST_strpos($jsst_allowed_addons, $jsst_key) !== false) {
                                        $jsst_isChecked = true;
                                    }
                                } ?>
                                <div class="jsstadmin-update-key-single-addon <?php echo esc_attr( $jsst_error_class ); ?>">
                                    <input id="addon-<?php echo esc_attr( $jsst_key ); ?>" name="<?php echo esc_attr( $jsst_key ); ?>" class="jsstadmin-update-key-checkbox" type="checkbox" <?php echo ($jsst_isChecked) ? 'checked' : ''; ?>>
                                    <?php
                                    if (!empty($jsst_jssupportticket_addons[$jsst_value['TextDomain']]['title'])) {
                                        echo esc_html($jsst_jssupportticket_addons[$jsst_value['TextDomain']]['title']);
                                    } else {
                                        echo esc_html(jssupportticketphplib::JSST_str_replace('JS Help Desk ', '', $jsst_value['Name']));    
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                        } else { ?>
                            <div class="jsstadmin-update-key-no-addon-msg">
                                <?php echo esc_html(__("No Addon Installed!", 'js-support-ticket')); ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="jsstadmin-update-key-infomsgwrp">
                        <img alt = "<?php echo esc_attr(__("Info", 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/installer/info-icon.png" />
                        <?php echo esc_html(__("This will replace the old key with the new one.", 'js-support-ticket')); ?>
                    </div>
                    <div class="jsstadmin-update-key-updtebtn-wrp">
                        <button class="jsstadmin-update-key-updtebtn" type="submit"><?php echo esc_html(__("Update Key", 'js-support-ticket')); ?></button>
                    </div>
                </div>
            </form>
        </div>
        <?php 
        if (!empty(jssupportticket::$jsst_data['unused_keys'])) { ?>
            <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n" style="margin-top: 25px;">
                <form class="jsstadmin-update-key-form" id="jsticketfrom" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=premiumplugin&task=jssupportticket_remove_unused_keys&action=jstask'),"delete-transaction-key")); ?>" method="post">
                    <div class="jsstadmin-update-key-wrp">
                        <div class="jsstadmin-update-key-slctall-addonswrp">
                            <span class="jsstadmin-update-key-slctall-addon-title">
                                <?php echo esc_html(__("Manage Unused Keys", 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div id="delete-info" class="mb-4">
                            <p class="text-gray-600">
                                <?php echo esc_html(__("You have", 'js-support-ticket')); ?>
                                <span id="unused-count" class="font-bold text-red-600"><?php echo esc_html( jssupportticket::$jsst_data['unused_keys'] ); ?></span>
                                <?php echo esc_html(__("unused key(s) that can be safely removed.", 'js-support-ticket')); ?>
                            </p>
                        </div>
                        <div class="jsstadmin-update-key-updtebtn-wrp">
                            <button class="mb-4 jsstadmin-update-key-updtebtn" type="submit" style="margin-bottom: 0;">
                                <?php echo esc_html(__("Delete All Unused Keys", 'js-support-ticket')); ?>
                                </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php } ?>
    </div>
</div>
<?php
$jsst_jssupportticket_js = "
jQuery(document).ready(function() {
    jQuery(\"#select-all\").on(\"change\", function() {
        var isChecked = jQuery(this).is(\":checked\");
        jQuery(\".jsstadmin-update-key-checkbox\").prop(\"checked\", isChecked);
    });

    jQuery(\".jsstadmin-update-key-checkbox\").on(\"change\", function() {
        var allChecked = jQuery(\".jsstadmin-update-key-checkbox\").length === jQuery(\".jsstadmin-update-key-checkbox:checked\").length;
        jQuery(\"#select-all\").prop(\"checked\", allChecked);
    });
});
";

wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
?>
