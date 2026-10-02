<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_r        = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : false;
$jsst_heading  = !empty($jsst_r->id) ? __('Edit Canned Response', 'js-support-ticket') : __('Add Canned Response', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_r->id) ? $jsst_r->id : '';
$jsst_departmentid = isset($jsst_r->departmentid) ? $jsst_r->departmentid : JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
?>
<div id="jsstadmin-wrapper">
    <?php JSSTsidemenu::render(); ?>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('Canned Responses', 'js-support-ticket'), 'url' => admin_url('admin.php?page=cannedresponses&jstlay=premademessages'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=cannedresponses&task=savepremademessage"),"save-premade-message-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The response', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-lg">
                                    <label class="jsst-flabel" for="title"><?php echo esc_html(__('Title', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('title', isset($jsst_r->title) ? $jsst_r->title : '', array('data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="departmentid"><?php echo esc_html(__('Department', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, __('Select Department', 'js-support-ticket'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('The department under which the answer will be made is available', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => __('Active', 'js-support-ticket'), '0' => __('Disabled', 'js-support-ticket')), isset($jsst_r->status) ? $jsst_r->status : '1'), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Customer suggestions', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval">
                                        <input type="hidden" name="customersuggestbox" value="1" />
                                        <label class="jsst-check">
                                            <input type="checkbox" name="customersuggest" value="1" <?php checked(!empty($jsst_r->customersuggest)); ?> />
                                            <span><?php echo esc_html(__('Also suggest this to customers on the ticket form', 'js-support-ticket')); ?></span>
                                        </label>
                                    </div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Off: only agents see it. On: a customer typing a matching question sees this answer, with placeholders like {customer_name} left blank. Tick only answers written for anyone to read.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Answer', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><?php wp_editor(isset($jsst_r->answer) ? $jsst_r->answer : '', 'answer', array('media_buttons' => false)); ?></div>
                                </div>
                                <?php include JSST_PLUGIN_PATH . 'modules/cannedresponses/tpls/placeholders.php'; ?>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_r->id) ? $jsst_r->id : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('updated', isset($jsst_r->updated) ? $jsst_r->updated : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('created', isset($jsst_r->created) ? $jsst_r->created : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'premademessage_savepremademessage'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses&jstlay=premademessages')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Canned Response', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
