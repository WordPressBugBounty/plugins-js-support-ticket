<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
$jssupportticket_js ='
    jQuery(document).ready(function ($) {
        $.validate();
    });
';
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
                        <li><?php echo esc_html(__('Add Department','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Add Department', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=department&task=savedepartment"),"save-department")); ?>">
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Title', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('departmentname', isset(jssupportticket::$_data[0]->departmentname) ? jssupportticket::$_data[0]->departmentname : '', array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title">
                        <?php echo esc_html(__('Outgoing Email', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                        <a title="<?php echo esc_html(__('Add New Email','js-support-ticket')); ?>" class="js-form-link" href="?page=email&jstlay=addemail"><?php echo esc_html(__('Add New Email','js-support-ticket')); ?></a>
                    </div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('emailid', JSSTincluder::getJSModel('email')->getEmailForDepartment(), isset(jssupportticket::$_data[0]->emailid) ? jssupportticket::$_data[0]->emailid : '', esc_html(__('Select Email', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS); ?>
                    </div>
                    <div class="js-form-desc">(<?php echo esc_html(__('The user of this department will receive email on the new ticket','js-support-ticket')); ?>)</div>
                </div>
                <div class="js-form-wrapper" style="display:none;">
                    <div class="js-form-title"><?php echo esc_html(__('Public', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('ispublic', array('1' => esc_html(__('Public', 'js-support-ticket')), '0' => esc_html(__('Private', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->ispublic) ? jssupportticket::$_data[0]->ispublic : '1', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper" >
                    <div class="js-form-title"><?php echo esc_html(__('Receive Email', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('sendmail', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->sendmail) ? jssupportticket::$_data[0]->sendmail : '0', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper fullwidth">
                    <div class="js-form-title"><?php echo esc_html(__('Signature', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php wp_editor(isset(jssupportticket::$_data[0]->departmentsignature) ? jssupportticket::$_data[0]->departmentsignature : '', 'departmentsignature', array('media_buttons' => false)); ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Append Signature','js-support-ticket')); ?></div>
                    <div class="js-form-value">
                        <div class="js-form-chkbox-field">
                            <?php echo wp_kses(JSSTformfield::checkbox('canappendsignature', array('1' => esc_html(__('Append signature with a reply', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->canappendsignature) ? jssupportticket::$_data[0]->canappendsignature : '1', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => esc_html(__('Enabled', 'js-support-ticket')), '0' => esc_html(__('Disabled', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->status) ? jssupportticket::$_data[0]->status : '1', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper" >
                    <div class="js-form-title"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('isdefault', array('2' => esc_html(__('Default with auto assign', 'js-support-ticket')), '1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->isdefault) ? jssupportticket::$_data[0]->isdefault : '0', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$_data[0]->id) ? jssupportticket::$_data[0]->id : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$_data[0]->created) ? jssupportticket::$_data[0]->created : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('updated', isset(jssupportticket::$_data[0]->updated) ? jssupportticket::$_data[0]->updated : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$_data[0]->ordering) ? jssupportticket::$_data[0]->ordering : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Department', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>
