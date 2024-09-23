<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
    <?php
    if (jssupportticket::$_config['offline'] == 2) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            $yesno = array((object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
                (object) array('id' => '0', 'text' => esc_html(__('No', 'js-support-ticket')))
            );
            $jssupportticket_js ='
                jQuery(document).ready(function ($) {
                    $.validate();
                });
            ';
            wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
            ?>
            <?php JSSTmessage::getMessage(); ?>
            <?php $formdata = JSSTformfield::getFormData(); ?>
            <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
            <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
            <div class="js-ticket-add-form-wrapper">
                <div class="js-ticket-top-search-wrp">
                    <div class="js-ticket-search-heading-wrp">
                        <div class="js-ticket-heading-left">
                            <?php echo esc_html(__('Export your data', 'js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-heading-right">
                            <a class="js-ticket-add-download-btn" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'gdpr','task'=>'exportusereraserequest','action'=>'jstask','jssupportticketid'=> JSSTincluder::getObjectClass('user')->uid() ,'jsstpageid'=>get_the_ID())),'export-usereraserequest')); ?>"><span class="js-ticket-add-img-wrp"></span><?php echo esc_html(__('Export', 'js-support-ticket')); ?></a>
                        </div>
                    </div>
                </div>
            <?php if(isset(jssupportticket::$_data[0]) && !empty(jssupportticket::$_data[0])) { ?>
                <div class="js-ticket-top-search-wrp second-style">
                    <div class="js-ticket-search-heading-wrp second-style">
                        <div class="js-ticket-heading-left">
                            <?php echo esc_html(__('You have filed a request to remove your data.', 'js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-heading-right">
                            <a class="js-ticket-add-download-btn" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'gdpr','task'=>'removeusereraserequest','action'=>'jstask','jssupportticketid'=> jssupportticket::$_data[0]->id ,'jsstpageid'=>get_the_ID())),'delete-usereraserequest')); ?>"><span class="js-ticket-add-img-wrp"></span><?php echo esc_html(__('To withdraw erases data request', 'js-support-ticket')); ?></a>
                        </div>
                    </div>
                </div>
            <?php }else{ ?>
                <div class="js-ticket-top-search-wrp second-style">
                    <div class="js-ticket-search-heading-wrp second-style">
                        <div class="js-ticket-heading-left">
                            <?php echo esc_html(__('Request data removal from the system.', 'js-support-ticket')); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
                <form class="js-ticket-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'task'=>'saveusereraserequest')),"save-usereraserequest")); ?>">
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Subject', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                        </div>
                        <div>
              
                        </div>

                        <div class="js-ticket-from-field">
                            <?php
                                if(isset($formdata['subject'])) $subject = $formdata['subject'];
                                elseif(isset(jssupportticket::$_data[0]->subject)) $subject = jssupportticket::$_data[0]->subject;
                                else $subject = '';
                                echo wp_kses(JSSTformfield::text('subject', $subject, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => 'required')), JSST_ALLOWED_TAGS);
                            ?>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Message','js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                        </div>
                        <div class="js-ticket-from-field">
                            <?php wp_editor(isset(jssupportticket::$_data[0]->message) ? jssupportticket::$_data[0]->message : '', 'message', array('media_buttons' => false)); ?>
                        </div>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$_data[0]->id) ?jssupportticket::$_data[0]->id :'' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <div class="js-ticket-form-btn-wrp">
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                        <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel')));?>" class="js-ticket-cancel-button"><?php echo esc_html(__('Cancel','js-support-ticket')); ?></a>
                    </div>
                </form>
            </div>
            <?php
        } else {
            JSSTlayout::getUserGuest();
        }
    } else {
        JSSTlayout::getSystemOffline();
} ?>
</div>
