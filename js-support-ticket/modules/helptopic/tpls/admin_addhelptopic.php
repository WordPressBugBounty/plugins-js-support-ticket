<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_t        = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : false;
$jsst_topicid  = isset($jsst_t->id) ? $jsst_t->id : 0;
$jsst_heading  = $jsst_topicid ? __('Edit Ticket Topic', 'js-support-ticket') : __('Add Ticket Topic', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_t->id) ? $jsst_t->id : '';
$jsst_departmentid = isset($jsst_t->departmentid) ? $jsst_t->departmentid : JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
// Nesting, ownership, routing and the default topic. (Roadmap 4.0-CORE-20)
$jsst_parents = JSSTincluder::getJSModel('helptopic')->getParentsForCombobox($jsst_topicid);
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('Ticket Topics', 'js-support-ticket'), 'url' => admin_url('admin.php?page=helptopic&jstlay=helptopics'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=helptopic&task=savehelptopic"),"save-help-topic-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The topic', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="topic"><?php echo esc_html(__('Topic', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('topic', isset($jsst_t->topic) ? $jsst_t->topic : '', array('data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="parentid"><?php echo esc_html(__('Parent Topic', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('parentid', $jsst_parents, isset($jsst_t->parentid) ? $jsst_t->parentid : '', __('No parent (top level)', 'js-support-ticket')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(sprintf(
                                        /* translators: %d: how many levels of topics are allowed */
                                        __('Topics can be nested %d levels deep. A topic that would sit under itself, or too deep, is refused.', 'js-support-ticket'),
                                        JSSThelptopicModel::MAX_DEPTH
                                    )); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => __('Active', 'js-support-ticket'), '0' => __('Disabled', 'js-support-ticket')), isset($jsst_t->status) ? $jsst_t->status : '1'), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Routing', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="departmentid"><?php echo esc_html(__('Department', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, __('Select Department', 'js-support-ticket'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="priorityid"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), isset($jsst_t->priorityid) ? $jsst_t->priorityid : '', __('No priority', 'js-support-ticket')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <p class="jsst-fhelp"><?php echo esc_html(__('The department and priority above are applied to a new ticket when the person filling in the form has not chosen them. An explicit choice is never overridden.', 'js-support-ticket')); ?></p>
                                <?php if (in_array('agent', jssupportticket::$_active_addons)) { ?>
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="staffid"><?php echo esc_html(__('Owned By', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), isset($jsst_t->staffid) ? $jsst_t->staffid : '', __('Nobody in particular', 'js-support-ticket')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('The agent who looks after this topic. Recorded for reporting and routing; it does not assign tickets on its own.', 'js-support-ticket')); ?></p>
                                </div>
                                <?php } ?>
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Default Topic', 'js-support-ticket')); ?></span>
                                    <?php /* An unticked checkbox posts nothing at all, so the
                                       hidden 0 always posts and a ticked box wins because
                                       PHP keeps the last value of a repeated name.
                                       (Roadmap 4.0-CORE-20) */ ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('isdefault', 0), JSST_ALLOWED_TAGS); ?>
                                    <span class="jsst-check"><?php echo wp_kses(JSSTformfield::checkbox('isdefault', array('1' => __('Preselect this topic on the ticket form', 'js-support-ticket')), isset($jsst_t->isdefault) ? $jsst_t->isdefault : 0), JSST_ALLOWED_TAGS); ?></span>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Only one topic can be the default. Ticking this here unticks it wherever it was before.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_t->id) ? $jsst_t->id : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('created', isset($jsst_t->created) ? $jsst_t->created : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('updated', isset($jsst_t->updated) ? $jsst_t->updated : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering', isset($jsst_t->ordering) ? $jsst_t->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'helptopic_savehelptopic'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=helptopic&jstlay=helptopics')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Topic', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
