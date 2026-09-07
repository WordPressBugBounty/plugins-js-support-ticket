<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $.validate();
    });
</script>
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
                        <li><?php echo esc_html(__('Add Topic','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Add Topic', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <?php $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : '';?>
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=helptopic&task=savehelptopic"),"save-help-topic-".$jsst_nonce_id)); ?>">
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Topic', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('topic', isset(jssupportticket::$jsst_data[0]->topic) ? jssupportticket::$jsst_data[0]->topic : '', array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Department', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value">
                        <?php
                            $jsst_departmentid = isset(jssupportticket::$jsst_data[0]->departmentid) ? jssupportticket::$jsst_data[0]->departmentid : JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
                            echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, __('Select Department', 'js-support-ticket'), array('class' => 'inputbox js-form-select-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS);
                        ?>
                    </div>
                </div>
                <?php
                // Nesting, ownership, routing and the default topic.
                // (Roadmap 4.0-CORE-20)
                $jsst_topicid = isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : 0;
                $jsst_parents = JSSTincluder::getJSModel('helptopic')->getParentsForCombobox($jsst_topicid);
                ?>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Parent Topic', 'js-support-ticket')); ?></div>
                    <div class="js-form-value">
                        <?php echo wp_kses(JSSTformfield::select('parentid', $jsst_parents, isset(jssupportticket::$jsst_data[0]->parentid) ? jssupportticket::$jsst_data[0]->parentid : '', __('No parent (top level)', 'js-support-ticket'), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                        <div class="js-form-note"><?php echo esc_html(sprintf(
                            /* translators: %d: how many levels of topics are allowed */
                            __('Topics can be nested %d levels deep. A topic that would sit under itself, or too deep, is refused.', 'js-support-ticket'),
                            JSSThelptopicModel::MAX_DEPTH
                        )); ?></div>
                    </div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></div>
                    <div class="js-form-value">
                        <?php echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), isset(jssupportticket::$jsst_data[0]->priorityid) ? jssupportticket::$jsst_data[0]->priorityid : '', __('No priority', 'js-support-ticket'), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                        <div class="js-form-note"><?php echo esc_html(__('The department and priority above are applied to a new ticket when the person filling in the form has not chosen them. An explicit choice is never overridden.', 'js-support-ticket')); ?></div>
                    </div>
                </div>
                <?php if (in_array('agent', jssupportticket::$_active_addons)) { ?>
                    <div class="js-form-wrapper">
                        <div class="js-form-title"><?php echo esc_html(__('Owned By', 'js-support-ticket')); ?></div>
                        <div class="js-form-value">
                            <?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), isset(jssupportticket::$jsst_data[0]->staffid) ? jssupportticket::$jsst_data[0]->staffid : '', __('Nobody in particular', 'js-support-ticket'), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                            <div class="js-form-note"><?php echo esc_html(__('The agent who looks after this topic. Recorded for reporting and routing; it does not assign tickets on its own.', 'js-support-ticket')); ?></div>
                        </div>
                    </div>
                <?php } ?>
                <div class="js-form-wrapper jsst-default-topic">
                    <div class="js-form-title"><?php echo esc_html(__('Default Topic', 'js-support-ticket')); ?></div>
                    <div class="js-form-value">
                        <div class="jsst-default-topic-choice">
                            <?php /* An unticked checkbox posts nothing at all, and
                               the model can no longer read "nothing" as "set it to
                               0" - it has to keep the stored value for the forms
                               that never render this field. This is what tells the
                               two apart: it always posts, and a ticked box wins
                               because PHP keeps the last value of a repeated name.
                               (Roadmap 4.0-CORE-20) */ ?>
                            <?php echo wp_kses(JSSTformfield::hidden('isdefault', 0), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::checkbox('isdefault', array('1' => __('Preselect this topic on the ticket form', 'js-support-ticket')), isset(jssupportticket::$jsst_data[0]->isdefault) ? jssupportticket::$jsst_data[0]->isdefault : 0, array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                        </div>
                        <div class="js-form-note"><?php echo esc_html(__('Only one topic can be the default. Ticking this here unticks it wherever it was before.', 'js-support-ticket')); ?></div>
                    </div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => __('Active', 'js-support-ticket'), '0' => __('Disabled', 'js-support-ticket')), isset(jssupportticket::$jsst_data[0]->status) ? jssupportticket::$jsst_data[0]->status : '1', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php /*
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Auto Response', 'js-support-ticket')); ?></div>
                    <div class="js-form-value">
                    <?php
                    if(isset(jssupportticket::$jsst_data[0])){
                        $jsst_checked = jssupportticket::$jsst_data[0]->autoresponce==1 ? 1 : 0;
                    }else{
                        $jsst_checked = 1;
                    }
                    echo wp_kses(JSSTformfield::checkbox('autoresponce', array('1' => __('Auto response for this topic','js-support-ticket').' ( '.__('override department setting', 'js-support-ticket').' )'),$jsst_checked , array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                */
                ?>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$jsst_data[0]->created) ? jssupportticket::$jsst_data[0]->created : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('updated', isset(jssupportticket::$jsst_data[0]->updated) ? jssupportticket::$jsst_data[0]->updated : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$jsst_data[0]->ordering) ? jssupportticket::$jsst_data[0]->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'helptopic_savehelptopic'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Topic', 'js-support-ticket'), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>
