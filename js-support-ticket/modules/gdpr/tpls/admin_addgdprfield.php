<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');

$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $.validate();
        jQuery('#termsandconditions_linktype').on('change', function() {
            if(this.value == 1){
                jQuery('.for-terms-condtions-linktype1').slideDown();
                jQuery('.for-terms-condtions-linktype2').hide();
            }else if(this.value == 2){
                jQuery('.for-terms-condtions-linktype1').hide();
                jQuery('.for-terms-condtions-linktype2').slideDown();
            }else{
                jQuery('.for-terms-condtions-linktype1').hide();
                jQuery('.for-terms-condtions-linktype2').hide();
            }
        });";
        if(isset(jssupportticket::$jsst_data[0]['userfield']->id)){
            $jsst_jssupportticket_js .="
            var intial_val = jQuery('#termsandconditions_linktype').val();
            if(intial_val == 1){
                jQuery('.for-terms-condtions-linktype1').slideDown();
                jQuery('.for-terms-condtions-linktype2').hide();
            }else if(intial_val == 2){
                jQuery('.for-terms-condtions-linktype1').hide();
                jQuery('.for-terms-condtions-linktype2').slideDown();
            }else{
                jQuery('.for-terms-condtions-linktype1').hide();
                jQuery('.for-terms-condtions-linktype2').hide();
            }";
        }
        $jsst_jssupportticket_js .="
    });
";
    wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Add GDPR Field','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text">
                <?php echo esc_html(__('Add GDPR Field', 'js-support-ticket')); ?>
            </h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <?php $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]['userfield']->id) ? jssupportticket::$jsst_data[0]['userfield']->id : '';?>
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=gdpr&task=savegdprfield"),"save-gdprfield-".$jsst_nonce_id)); ?>">
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Field Title', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('fieldtitle', isset(jssupportticket::$jsst_data[0]['userfield']->fieldtitle) ? jssupportticket::$jsst_data[0]['userfield']->fieldtitle : '', array('class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS) ?></div>
                </div>
                <?php
                $jsst_termsandconditions_text = '';
                $jsst_termsandconditions_linktype = '';
                $jsst_termsandconditions_link = '';
                $jsst_termsandconditions_page = '';
                if( isset(jssupportticket::$jsst_data[0]['userfieldparams']) && jssupportticket::$jsst_data[0]['userfieldparams'] != '' && is_array(jssupportticket::$jsst_data[0]['userfieldparams']) && !empty(jssupportticket::$jsst_data[0]['userfieldparams'])){
                    $jsst_termsandconditions_text = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_text']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_text'] :'' ;
                    $jsst_termsandconditions_linktype = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_linktype']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_linktype'] :'' ;
                    $jsst_termsandconditions_link = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_link']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_link'] :'' ;
                    $jsst_termsandconditions_page = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_page']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_page'] :'' ;
                } ?>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Field Text', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('termsandconditions_text', $jsst_termsandconditions_text, array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                    <div class="js-form-desc">
                        <?php echo esc_html(__("e.g ' I have read and agree to the [link] Terms and Conditions[/link].  ' The text between [link] and [/link] will be linked to provided url or wordpress page.", 'js-support-ticket')); ?>
                    </div>
                </div>
                <?php
                $jsst_yesno = array(
                    (object) array('id' => 1, 'text' => esc_html(__('Yes', 'js-support-ticket'))),
                    (object) array('id' => 0, 'text' => esc_html(__('No', 'js-support-ticket'))));
                /*
                ?>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Required', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span> </div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('required', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->required) ? jssupportticket::$jsst_data[0]['userfield']->required : '', esc_html(__('Select Required', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php
                */
                $jsst_linktype = array(
                    (object) array('id' => 1, 'text' => esc_html(__('Direct Link', 'js-support-ticket'))),
                    (object) array('id' => 2, 'text' => esc_html(__('Wordpress Page', 'js-support-ticket'))),
                    (object) array('id' => 3, 'text' => esc_html(__('None', 'js-support-ticket'))));
                ?>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Link Type', 'js-support-ticket')); ?> </div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('termsandconditions_linktype', $jsst_linktype, $jsst_termsandconditions_linktype, esc_html(__('Select Link Type', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper for-terms-condtions-linktype2" style="display: none;">
                    <div class="js-form-title"><?php echo esc_html(__('Link Page', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('termsandconditions_page', JSSTincluder::getJSModel('configuration')->getPageList(), $jsst_termsandconditions_page, esc_html(__('Select Page', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper for-terms-condtions-linktype1" style="display: none;">
                    <div class="js-form-title"><?php echo esc_html(__('URL', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('termsandconditions_link', $jsst_termsandconditions_link, array('class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS) ?></div>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]['userfield']->id) ? jssupportticket::$jsst_data[0]['userfield']->id : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$jsst_data[0]['userfield']->created) ? jssupportticket::$jsst_data[0]['userfield']->created : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$jsst_data[0]['userfield']->ordering) ? jssupportticket::$jsst_data[0]['userfield']->ordering : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('userfieldtype', 'termsandconditions'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('isuserfield', 1), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('fieldfor', 3), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('published', 1), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('required', 1), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('isvisitorpublished', 1), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>
