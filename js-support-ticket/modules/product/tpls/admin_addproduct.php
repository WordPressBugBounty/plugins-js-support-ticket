<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
$jsst_jssupportticket_js ="
    jQuery(document).ready(function () {
        jQuery.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Add Product','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Add Product', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <?php $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]->id) ?jssupportticket::$jsst_data[0]->id :''; ?>
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=product&task=saveproduct"),"save-product-".$jsst_nonce_id)); ?>">
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Product', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('product', isset(jssupportticket::$jsst_data[0]->product) ? jssupportticket::$jsst_data[0]->product : '', array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => __('Active', 'js-support-ticket'), '0' => __('Disabled', 'js-support-ticket')), isset(jssupportticket::$jsst_data[0]->status) ? jssupportticket::$jsst_data[0]->status : '1', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$jsst_data[0]->ordering) ? jssupportticket::$jsst_data[0]->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'product_saveproduct'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Product', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>
