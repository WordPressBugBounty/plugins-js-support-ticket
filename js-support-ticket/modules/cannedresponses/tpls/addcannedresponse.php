<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (jssupportticket::$jsst_data['permission_granted'] == 1) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                if (jssupportticket::$jsst_data['staff_enabled']) {
                    ?>
                    <?php
                    $jsst_status = array((object) array('id' => '1', 'text' => __('Active', 'js-support-ticket')),
                        (object) array('id' => '0', 'text' => __('Disabled', 'js-support-ticket'))
                    );
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            $.validate();
                        });
                    </script>
                    <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
                    <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
                    <div class="js-ticket-add-form-wrapper">
                        <?php $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : '';?>
                        <form class="js-ticket-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'task'=>'savepremademessage')),"save-premade-message-".$jsst_nonce_id)); ?>">
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Title', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field">
                                    <?php echo wp_kses(JSSTformfield::text('title', isset(jssupportticket::$jsst_data[0]->title) ? jssupportticket::$jsst_data[0]->title : '', array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Department', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field js-ticket-form-field-select"><?php
                                    $jsst_departmentid = isset(jssupportticket::$jsst_data[0]->departmentid) ? jssupportticket::$jsst_data[0]->departmentid : JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
                                    echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, __('Select Department', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => 'required')), JSST_ALLOWED_TAGS);?>
                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Response', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field">
                                    <?php wp_editor(isset(jssupportticket::$jsst_data[0]->answer) ? jssupportticket::$jsst_data[0]->answer : '', 'answer', array('media_buttons' => false)); ?>
                                    <?php
                                    // Placeholder reference. Click one to copy it into the
                                    // response. (Roadmap 4.0-CORE-03)
                                    include JSST_PLUGIN_PATH . 'modules/cannedresponses/tpls/placeholders.php';
                                    ?>
                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Status', 'js-support-ticket')); ?>
                                </div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php echo wp_kses(JSSTformfield::select('status', $jsst_status, isset(jssupportticket::$jsst_data[0]->status) ? jssupportticket::$jsst_data[0]->status : 1, __('Select Status', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-input')), JSST_ALLOWED_TAGS); ?>
                                </div>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : ''), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$jsst_data[0]->created) ? jssupportticket::$jsst_data[0]->created : '' ), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'cannedresponses_savepremademessage'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                            <div class="js-ticket-form-btn-wrp">
                                <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Canned Response', 'js-support-ticket'), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                                <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'agentcannedresponses')));?>" class="js-ticket-cancel-button"><?php echo esc_html(__('Cancel','js-support-ticket')); ?></a>
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
        } else {// User is guest
            $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'addcannedresponse'));
            $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
            JSSTlayout::getUserGuest($jsst_redirect_url);
        }
    } else { // User permission not granted
        JSSTlayout::getPermissionNotGranted();
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
