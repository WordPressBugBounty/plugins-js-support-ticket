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
                        <form class="js-ticket-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'task'=>'savehelptopic')),"save-help-topic-".$jsst_nonce_id)); ?>">
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Topic', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                                </div>
                                <div class="js-ticket-from-field">
                                    <?php echo wp_kses(JSSTformfield::text('topic', isset(jssupportticket::$jsst_data[0]->topic) ? jssupportticket::$jsst_data[0]->topic : '', array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
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
                            <?php
                            // Nesting, routing, ownership and the default topic -
                            // the same four the admin form offers. They were never
                            // withheld here for safety: canManage() gates the whole
                            // save and there is no per-field check, so a form that
                            // merely left them out protected nothing and only meant
                            // storeHelpTopic() could not tell "not rendered" from
                            // "cleared". (Roadmap 4.0-CORE-20)
                            $jsst_topicid = isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : 0;
                            $jsst_parents = JSSTincluder::getJSModel('helptopic')->getParentsForCombobox($jsst_topicid);
                            ?>
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Parent Topic', 'js-support-ticket')); ?>
                                </div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php echo wp_kses(JSSTformfield::select('parentid', $jsst_parents, isset(jssupportticket::$jsst_data[0]->parentid) ? jssupportticket::$jsst_data[0]->parentid : '', __('No parent (top level)', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-input')), JSST_ALLOWED_TAGS); ?>
                                </div>
                            </div>
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Priority', 'js-support-ticket')); ?>
                                </div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), isset(jssupportticket::$jsst_data[0]->priorityid) ? jssupportticket::$jsst_data[0]->priorityid : '', __('No priority', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-input')), JSST_ALLOWED_TAGS); ?>
                                </div>
                            </div>
                            <?php if (in_array('agent', jssupportticket::$_active_addons)) { ?>
                                <div class="js-ticket-from-field-wrp">
                                    <div class="js-ticket-from-field-title">
                                        <?php echo esc_html(__('Owned By', 'js-support-ticket')); ?>
                                    </div>
                                    <div class="js-ticket-from-field js-ticket-form-field-select">
                                        <?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), isset(jssupportticket::$jsst_data[0]->staffid) ? jssupportticket::$jsst_data[0]->staffid : '', __('Nobody in particular', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-input')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="js-ticket-from-field-wrp">
                                <div class="js-ticket-from-field-title">
                                    <?php echo esc_html(__('Default Topic', 'js-support-ticket')); ?>
                                </div>
                                <div class="js-ticket-from-field">
                                    <?php /* As on the admin form: an unticked box posts
                                       nothing, and the model keeps the stored value when
                                       the key is absent, so without this hidden field the
                                       box could never be unticked. A ticked box wins,
                                       because PHP keeps the last value of a repeated
                                       name. */ ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('isdefault', 0), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::checkbox('isdefault', array('1' => __('Preselect this topic on the ticket form', 'js-support-ticket')), isset(jssupportticket::$jsst_data[0]->isdefault) ? jssupportticket::$jsst_data[0]->isdefault : 0, array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
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
                            <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$jsst_data[0]->ordering) ? jssupportticket::$jsst_data[0]->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'helptopic_savehelptopic'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                            <div class="js-ticket-form-btn-wrp">
                                <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Topic', 'js-support-ticket'), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                                <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics')));?>" class="js-ticket-cancel-button"><?php echo esc_html(__('Cancel','js-support-ticket')); ?></a>
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
            $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'addhelptopic'));
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
