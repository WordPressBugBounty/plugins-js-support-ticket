<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Add / edit a department.
 *
 * Two groups in one form panel: where a ticket goes, and how a reply signs
 * off. `ispublic` is still posted as a hidden value.
 */
$jsst_jssupportticket_js ='
    jQuery(document).ready(function ($) {
        $.validate();
    });
';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);

$jsst_dept    = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : false;
$jsst_isedit  = !empty($jsst_dept->id);
$jsst_heading = $jsst_isedit ? __('Edit Department', 'js-support-ticket') : __('Add Department', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_dept->id) ? $jsst_dept->id : '';
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('Departments', 'js-support-ticket'), 'url' => admin_url('admin.php?page=department&jstlay=departments'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=department&task=savedepartment"),"save-department-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Where the ticket goes', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="departmentname"><?php echo esc_html(__('Title', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('departmentname', isset($jsst_dept->departmentname) ? $jsst_dept->departmentname : '', array('data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-md">
                                    <div class="jsst-flabel-row">
                                        <label class="jsst-flabel" for="emailid"><?php echo esc_html(__('Outgoing Email', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                        <a class="jsst-flabel-link" href="<?php echo esc_url(admin_url('admin.php?page=email&jstlay=addemail')); ?>"><?php echo esc_html(__('Add new email','js-support-ticket')); ?></a>
                                    </div>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('emailid', JSSTincluder::getJSModel('email')->getEmailForDepartment(), isset($jsst_dept->emailid) ? $jsst_dept->emailid : '', esc_html(__('Select Email', 'js-support-ticket')), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('New tickets in this department are sent from here.','js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow-break"></div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => esc_html(__('Enabled', 'js-support-ticket')), '0' => esc_html(__('Disabled', 'js-support-ticket'))), isset($jsst_dept->status) ? $jsst_dept->status : '1'), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Receive Email', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('sendmail', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset($jsst_dept->sendmail) ? $jsst_dept->sendmail : '0'), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                                <div class="jsst-frow jsst-frow-lg">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('isdefault', array('2' => esc_html(__('Default with auto assign', 'js-support-ticket')), '1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset($jsst_dept->isdefault) ? $jsst_dept->isdefault : '0'), JSST_ALLOWED_TAGS); ?></div></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('The department a ticket lands in when nobody picked one.','js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('How replies sign off', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Signature', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><?php wp_editor(isset($jsst_dept->departmentsignature) ? $jsst_dept->departmentsignature : '', 'departmentsignature', array('media_buttons' => false)); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-check"><?php echo wp_kses(JSSTformfield::checkbox('canappendsignature', array('1' => esc_html(__('Append this signature to every reply', 'js-support-ticket'))), isset($jsst_dept->canappendsignature) ? $jsst_dept->canappendsignature : '1'), JSST_ALLOWED_TAGS); ?></span>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('ispublic', isset($jsst_dept->ispublic) ? $jsst_dept->ispublic : '1'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_dept->id) ? $jsst_dept->id : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('created', isset($jsst_dept->created) ? $jsst_dept->created : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('updated', isset($jsst_dept->updated) ? $jsst_dept->updated : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering', isset($jsst_dept->ordering) ? $jsst_dept->ordering : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=department&jstlay=departments')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Department', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
