<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (jssupportticket::$jsst_data['permission_granted'] == 1) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                if (jssupportticket::$jsst_data['staff_enabled']) {
                    $jsst_type = array((object) array('id' => '1', 'text' => esc_html(__('Public', 'js-support-ticket'))),
                        (object) array('id' => '0', 'text' => esc_html(__('Private', 'js-support-ticket')))
                    );
                    $jsst_status = array((object) array('id' => '1', 'text' => esc_html(__('Enabled', 'js-support-ticket'))),
                        (object) array('id' => '0', 'text' => esc_html(__('Disabled', 'js-support-ticket')))
                    );
                    $jsst_yesno = array((object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
                        (object) array('id' => '0', 'text' => esc_html(__('No', 'js-support-ticket')))
                    );
                    $jsst_jssupportticket_js ='
                        jQuery(document).ready(function ($) {
                            $.validate();
                        });
                    ';
                    wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
                    ?>
                    <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
                    <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php');
                    $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : ''; ?>

                    <div class="js-ticket-add-form-wrapper">
                        <form class="js-ticket-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'task'=>'savedepartment')),"save-department-".$jsst_nonce_id)); ?>">
                            <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Title', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field">
                                    <?php echo wp_kses(JSSTformfield::text('departmentname', isset(jssupportticket::$jsst_data[0]->departmentname) ? jssupportticket::$jsst_data[0]->departmentname : '', array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Outgoing Email', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php echo wp_kses(JSSTformfield::select('emailid', JSSTincluder::getJSModel('email')->getEmailForDepartment(), isset(jssupportticket::$jsst_data[0]->emailid) ? jssupportticket::$jsst_data[0]->emailid : '', esc_html(__('Select Email', 'js-support-ticket')), array('class' => 'inputbox js-ticket-form-field-select', 'data-validation' => 'required')), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <span class="js-support-ticket-outgoing-email-message" >(<?php echo esc_html(__('Tickets of this department will receive emails from this email','js-support-ticket'));?>)</span>
                            </div>
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Receive Email', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field">
                                    <div class="js-ticket-radio-btn-wrp">
                                        <?php echo wp_kses(JSSTformfield::radiobutton('sendmail', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset(jssupportticket::$jsst_data[0]->sendmail) ? jssupportticket::$jsst_data[0]->sendmail : '0', array('class' => 'radiobutton js-ticket-form-field-radio-btn')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="js-ticket-append-signature-wrp js-ticket-append-signature-wrp-full-width"><!-- Append Signature -->
                                <div class="js-ticket-append-field-title"><?php echo esc_html(__('Append Signature','js-support-ticket')); ?></div>
                                <div class="js-ticket-append-field-wrp">
                                    <div class="js-ticket-signature-radio-box js-ticket-signature-radio-box-full-width ">
                                        <?php echo wp_kses(JSSTformfield::checkbox('canappendsignature', array('1' => esc_html(__('Append signature with a reply', 'js-support-ticket'))), isset(jssupportticket::$jsst_data[0]->canappendsignature) ? jssupportticket::$jsst_data[0]->canappendsignature : '', array('class' => 'radiobutton js-ticket-append-radio-btn')), JSST_ALLOWED_TAGS); ?>
                                    </div>

                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Signature', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field">
                                    <?php wp_editor(isset(jssupportticket::$jsst_data[0]->departmentsignature) ? jssupportticket::$jsst_data[0]->departmentsignature : '', 'departmentsignature', array('media_buttons' => false)); ?>
                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Status', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php echo wp_kses(JSSTformfield::select('status', $jsst_status, isset(jssupportticket::$jsst_data[0]->status) ? jssupportticket::$jsst_data[0]->status : '', esc_html(__('Select Status', 'js-support-ticket')), array('class' => 'inputbox js-ticket-form-field-input')), JSST_ALLOWED_TAGS); ?>
                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Default', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php echo wp_kses(JSSTformfield::radiobutton('isdefault', array('2' => esc_html(__('Default with auto assign', 'js-support-ticket')), '1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset(jssupportticket::$jsst_data[0]->isdefault) ? jssupportticket::$jsst_data[0]->isdefault : '0', array('class' => 'radiobutton js-ticket-form-field-radio-btn')), JSST_ALLOWED_TAGS); ?>

                                </div>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : ''), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$jsst_data[0]->created) ? jssupportticket::$jsst_data[0]->created : ''), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('updated', isset(jssupportticket::$jsst_data[0]->updated) ? jssupportticket::$jsst_data[0]->updated : ''), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$jsst_data[0]->ordering) ? jssupportticket::$jsst_data[0]->ordering : ''), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'department_savedepartment'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <div class="js-ticket-form-btn-wrp">
                                <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Department', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                                <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments')));?>" class="js-ticket-cancel-button"><?php echo esc_html(__('Cancel','js-support-ticket')); ?></a>
                            </div>
                        </form>
                    </div>
                    <?php
                } else {
                    JSSTlayout::getStaffMemberDisable();
                }
            } else { // user not Staff
                JSSTlayout::getNotStaffMember();
            }
        } else {
            $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'adddepartment'));
            $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
            JSSTlayout::getUserGuest($jsst_redirect_url);
        }
    } else { // User permission not granted
        JSSTlayout::getPermissionNotGranted();
    }
} else {
    JSSTlayout::getSystemOffline();
} ?>
</div>
