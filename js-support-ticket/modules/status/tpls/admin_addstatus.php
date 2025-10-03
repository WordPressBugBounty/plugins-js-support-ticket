<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('iris');
$jssupportticket_js ="
    jQuery(document).ready(function () {
        jQuery.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
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
                        <li><?php echo esc_html(__('Add Ticket Status','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Add Ticket Status', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <?php $nonce_id = isset(jssupportticket::$_data[0]->id) ?jssupportticket::$_data[0]->id :''; ?>
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=status&task=savestatus"),"save-status-".$nonce_id)); ?>">
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Status', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('status', isset(jssupportticket::$_data[0]->status) ? jssupportticket::$_data[0]->status : '', array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                    <?php if(!empty(jssupportticket::$_data[0]->custom_status)) { ?>
                        <div class="js-form-desc">(<?php echo esc_html( jssupportticket::$_data[0]->custom_status ); ?>)</div>
                    <?php } ?>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Text Color', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value">
                        <?php
                        $style = '';
                        if (!empty(jssupportticket::$_data[0]->statuscolour)) {
                            $style = "background:".jssupportticket::$_data[0]->statuscolour;
                        } ?>
                        <span style="<?php echo esc_attr($style); ?>" class="js-form-statuscolor-wrp"></span>
                        <?php echo wp_kses(JSSTformfield::text('statuscolor', isset(jssupportticket::$_data[0]->statuscolour) ? jssupportticket::$_data[0]->statuscolour : '', array('class' => 'inputbox js-form-input-field js-form-statuscolor-field', 'data-validation' => 'required', 'autocomplete' => 'off')), JSST_ALLOWED_TAGS); ?>
                    </div>
                    <?php if(!empty(jssupportticket::$_data[0]->custom_status)) { ?>
                        <div class="js-form-desc js-form-status-desc"></div>
                    <?php } ?>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Background Color', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value">
                        <?php
                        $style = '';
                        if (!empty(jssupportticket::$_data[0]->statusbgcolour)) {
                            $style = "background:".jssupportticket::$_data[0]->statusbgcolour;
                        } ?>
                        <span style="<?php echo esc_attr($style); ?>" class="js-form-statusbgcolor-wrp"></span>
                        <?php echo wp_kses(JSSTformfield::text('statusbgcolor', isset(jssupportticket::$_data[0]->statusbgcolour) ? jssupportticket::$_data[0]->statusbgcolour : '', array('class' => 'inputbox js-form-input-field js-form-statuscolor-field', 'data-validation' => 'required', 'autocomplete' => 'off')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$_data[0]->id) ? jssupportticket::$_data[0]->id : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$_data[0]->ordering) ? jssupportticket::$_data[0]->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'status_savestatus'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Status', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
        <?php
        $jssupportticket_js ="
            jQuery(document).ready(function () {
                jQuery('input#statuscolor').iris({
                    color: jQuery('input#statuscolor').val(),
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
                        jQuery('.js-form-statuscolor-wrp').css( 'background', hex);
                        jQuery('.js-form-statuscolor-wrp').css( 'border', '1px solid #ebecec');
                        jQuery('input#statuscolor').css('backgroundColor', '#' + hex).val('#' + hex);
                    }
                });
                jQuery('input#statusbgcolor').iris({
                    color: jQuery('input#statusbgcolor').val(),
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
                        jQuery('.js-form-statusbgcolor-wrp').css( 'background', hex);
                        jQuery('.js-form-statusbgcolor-wrp').css( 'border', '1px solid #ebecec');
                        jQuery('input#statusbgcolor').css('backgroundColor', '#' + hex).val('#' + hex);
                    }
                });
                jQuery(document).click(function (e) {
                    if (!jQuery(e.target).is('.colour-picker, .iris-picker, .iris-picker-inner')) {
                        jQuery('#statuscolor').iris('hide');
                        jQuery('#statusbgcolor').iris('hide');
                    }
                });
                jQuery('#statuscolor').click(function (event) {
                    jQuery('#statuscolor').iris('hide');
                    jQuery(this).iris('show');
                    return false;
                });
                jQuery('#statusbgcolor').click(function (event) {
                    jQuery('#statusbgcolor').iris('hide');
                    jQuery(this).iris('show');
                    return false;
                });
            });
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
    </div>
</div>
