<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_b        = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : false;
$jsst_heading  = !empty($jsst_b->id) ? __('Edit Banned Email', 'js-support-ticket') : __('Add Banned Email', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_b->id) ? $jsst_b->id : '';
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('Banned Emails', 'js-support-ticket'), 'url' => admin_url('admin.php?page=banemail&jstlay=banemails'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=banemail&task=savebanemail"),"save-ban-email-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <div class="jsst-formgrid">
                            <div class="jsst-frow jsst-frow-lg">
                                <label class="jsst-flabel" for="email"><?php echo esc_html(__('Email', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('email', isset($jsst_b->email) ? $jsst_b->email : '', array('data-validation' => 'email')), JSST_ALLOWED_TAGS) ?></div>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Tickets and replies from this address are refused.', 'js-support-ticket')); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_b->id) ? $jsst_b->id : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('created', isset($jsst_b->created) ? $jsst_b->created : ''), JSST_ALLOWED_TAGS); ?>
                    <?php
                    if(in_array('agent', jssupportticket::$_active_addons)){
                        echo wp_kses(JSSTformfield::hidden('submitter', JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid())), JSST_ALLOWED_TAGS);
                    }else{
                        echo wp_kses(JSSTformfield::hidden('submitter', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS);
                    }
                    ?>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'banemail_savebanemail'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=banemail&jstlay=banemails')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Banned Email', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
