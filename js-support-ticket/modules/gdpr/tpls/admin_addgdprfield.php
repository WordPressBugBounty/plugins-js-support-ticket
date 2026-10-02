<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');

$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $.validate();
        function jsstGdprLinkType(value, animate) {
            var show1 = (value == 1), show2 = (value == 2);
            jQuery('.for-terms-condtions-linktype1')[show1 ? (animate ? 'slideDown' : 'show') : 'hide']();
            jQuery('.for-terms-condtions-linktype2')[show2 ? (animate ? 'slideDown' : 'show') : 'hide']();
        }
        jQuery('#termsandconditions_linktype').on('change', function() {
            jsstGdprLinkType(this.value, true);
        });
        jsstGdprLinkType(jQuery('#termsandconditions_linktype').val(), false);
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);

$jsst_uf       = isset(jssupportticket::$jsst_data[0]['userfield']) ? jssupportticket::$jsst_data[0]['userfield'] : false;
$jsst_heading  = !empty($jsst_uf->id) ? __('Edit GDPR Field', 'js-support-ticket') : __('Add GDPR Field', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_uf->id) ? $jsst_uf->id : '';
$jsst_params   = (isset(jssupportticket::$jsst_data[0]['userfieldparams']) && is_array(jssupportticket::$jsst_data[0]['userfieldparams'])) ? jssupportticket::$jsst_data[0]['userfieldparams'] : array();
$jsst_termsandconditions_text     = isset($jsst_params['termsandconditions_text']) ? $jsst_params['termsandconditions_text'] : '';
$jsst_termsandconditions_linktype = isset($jsst_params['termsandconditions_linktype']) ? $jsst_params['termsandconditions_linktype'] : '';
$jsst_termsandconditions_link     = isset($jsst_params['termsandconditions_link']) ? $jsst_params['termsandconditions_link'] : '';
$jsst_termsandconditions_page     = isset($jsst_params['termsandconditions_page']) ? $jsst_params['termsandconditions_page'] : '';
$jsst_linktype = array(
    (object) array('id' => 1, 'text' => esc_html(__('Direct Link', 'js-support-ticket'))),
    (object) array('id' => 2, 'text' => esc_html(__('WordPress Page', 'js-support-ticket'))),
    (object) array('id' => 3, 'text' => esc_html(__('None', 'js-support-ticket'))));
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('GDPR Fields', 'js-support-ticket'), 'url' => admin_url('admin.php?page=gdpr&jstlay=gdprfields'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=gdpr&task=savegdprfield"),"save-gdprfield-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The consent box', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="fieldtitle"><?php echo esc_html(__('Field Title', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('fieldtitle', isset($jsst_uf->fieldtitle) ? $jsst_uf->fieldtitle : ''), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <label class="jsst-flabel" for="termsandconditions_text"><?php echo esc_html(__('Field Text', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('termsandconditions_text', $jsst_termsandconditions_text, array('data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__("e.g ' I have read and agree to the [link] Terms and Conditions[/link]. ' The text between [link] and [/link] will be linked to provided url or wordpress page.", 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('What it links to', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="termsandconditions_linktype"><?php echo esc_html(__('Link Type', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('termsandconditions_linktype', $jsst_linktype, $jsst_termsandconditions_linktype, esc_html(__('Select Link Type', 'js-support-ticket'))), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-md for-terms-condtions-linktype2" style="display: none;">
                                    <label class="jsst-flabel" for="termsandconditions_page"><?php echo esc_html(__('Link Page', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('termsandconditions_page', JSSTincluder::getJSModel('configuration')->getPageList(), $jsst_termsandconditions_page, esc_html(__('Select Page', 'js-support-ticket'))), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-lg for-terms-condtions-linktype1" style="display: none;">
                                    <label class="jsst-flabel" for="termsandconditions_link"><?php echo esc_html(__('URL', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('termsandconditions_link', $jsst_termsandconditions_link, array('placeholder' => 'https://')), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_uf->id) ? $jsst_uf->id : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('created', isset($jsst_uf->created) ? $jsst_uf->created : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering', isset($jsst_uf->ordering) ? $jsst_uf->ordering : ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('userfieldtype', 'termsandconditions'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('isuserfield', 1), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('fieldfor', 3), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('published', 1), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('required', 1), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('isvisitorpublished', 1), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=gdpr&jstlay=gdprfields')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
