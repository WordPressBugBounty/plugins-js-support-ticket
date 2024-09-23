<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('iris');
$jssupportticket_js ="
    jQuery(document).ready(function () {
        jQuery('select#overduetypeid').change(function(){
            changevalue();
        });
        changevalue();
        function changevalue()
        {
            var isselect = jQuery('select#overduetypeid').val();
            if(isselect == 1){
                jQuery('span.ticket_overdue_type_text').html('". esc_html(__('Days', 'js-support-ticket'))."');
            }else{
                jQuery('span.ticket_overdue_type_text').html('". esc_html(__('Hours', 'js-support-ticket'))."');
            }
        }
        jQuery.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
$dayshours = array(
    (object) array('id' => '1', 'text' => esc_html(__('Days', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Hours', 'js-support-ticket')))
);
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
                        <li><?php echo esc_html(__('Add Priority','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Add Priority', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=priority&task=savepriority"),"save-priority")); ?>">
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('priority', isset(jssupportticket::$_data[0]->priority) ? jssupportticket::$_data[0]->priority : '', array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Color', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('prioritycolor', isset(jssupportticket::$_data[0]->prioritycolour) ? jssupportticket::$_data[0]->prioritycolour : '', array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required', 'autocomplete' => 'off')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php if(in_array('overdue', jssupportticket::$_active_addons)){ ?>
                    <div class="js-form-wrapper">
                        <div class="js-form-title"><?php echo esc_html(__('Ticket Overdue Interval Type', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('overduetypeid', $dayshours , (isset(jssupportticket::$_data[0]->overduetypeid) ? jssupportticket::$_data[0]->overduetypeid : '' ), '',array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS)?></div>
                    </div>
                    <div class="js-form-wrapper">
                        <div class="js-form-title"><?php echo esc_html(__('Ticket Overdue', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('overdueinterval', isset(jssupportticket::$_data[0]->overdueinterval) ? jssupportticket::$_data[0]->overdueinterval : '', array('class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS) ?><span class="ticket_overdue_type_text" ><?php echo isset(jssupportticket::$_data[0]->overduetypeid) ? esc_html(jssupportticket::$_data[0]->overduetypeid) : '' ?></span></div>
                    </div>
                <?php } ?>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Public', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('ispublic', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->ispublic) ? jssupportticket::$_data[0]->ispublic : '1', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('isdefault', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->isdefault) &&  jssupportticket::$_data[0]->isdefault == 1 ? 1 : 0, array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => esc_html(__('Enabled', 'js-support-ticket')), '0' => esc_html(__('Disabled', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->status) ? jssupportticket::$_data[0]->status : '1', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$_data[0]->id) ? jssupportticket::$_data[0]->id : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$_data[0]->ordering) ? jssupportticket::$_data[0]->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'priority_savepriority'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Priority', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
        <?php
        $jssupportticket_js ="
            jQuery(document).ready(function () {
                jQuery('input#prioritycolor').iris({
                    color: jQuery('input#prioritycolor').val(),
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        jQuery('input#prioritycolor').css('backgroundColor', '#' + hex).val('#' + hex);
                    }
                });
                jQuery(document).click(function (e) {
                    if (!jQuery(e.target).is('.colour-picker, .iris-picker, .iris-picker-inner')) {
                        jQuery('#prioritycolor').iris('hide');
                    }
                });
                jQuery('#prioritycolor').click(function (event) {
                    jQuery('#prioritycolor').iris('hide');
                    jQuery(this).iris('show');
                    return false;
                });
            });
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
    </div>
</div>
