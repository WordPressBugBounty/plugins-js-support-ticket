<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');

wp_enqueue_script('iris');
$jsst_jssupportticket_js ="
    jQuery(document).ready(function () {
        jQuery.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_s        = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : false;
$jsst_isedit   = !empty($jsst_s->id);
$jsst_heading  = $jsst_isedit ? __('Edit Ticket Status', 'js-support-ticket') : __('Add Ticket Status', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_s->id) ? $jsst_s->id : '';
$jsst_colour   = !empty($jsst_s->statuscolour) ? $jsst_s->statuscolour : '';
$jsst_bgcolour = !empty($jsst_s->statusbgcolour) ? $jsst_s->statusbgcolour : '';
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('Ticket Statuses', 'js-support-ticket'), 'url' => admin_url('admin.php?page=status&jstlay=statuses'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=status&task=savestatus"),"save-status-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The status', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="status"><?php echo esc_html(__('Status', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('status', isset($jsst_s->status) ? $jsst_s->status : '', array('data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                                    <?php if (!empty($jsst_s->custom_status)) { ?>
                                        <p class="jsst-fhelp"><?php echo esc_html($jsst_s->custom_status); ?></p>
                                    <?php } ?>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('How it looks', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="statuscolor"><?php echo esc_html(__('Text Color', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval jsst-colour">
                                        <span class="jsst-colour-chip js-form-statuscolor-wrp" style="<?php echo esc_attr($jsst_colour ? 'background:'.$jsst_colour : ''); ?>"></span>
                                        <?php echo wp_kses(JSSTformfield::text('statuscolor', $jsst_colour, array('class' => 'jsst-colour-input', 'data-validation' => 'required', 'autocomplete' => 'off', 'placeholder' => '#FFFFFF')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="statusbgcolor"><?php echo esc_html(__('Background Color', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval jsst-colour">
                                        <span class="jsst-colour-chip js-form-statusbgcolor-wrp" style="<?php echo esc_attr($jsst_bgcolour ? 'background:'.$jsst_bgcolour : ''); ?>"></span>
                                        <?php echo wp_kses(JSSTformfield::text('statusbgcolor', $jsst_bgcolour, array('class' => 'jsst-colour-input', 'data-validation' => 'required', 'autocomplete' => 'off', 'placeholder' => '#000000')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Preview', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><span class="jsst-chip js-form-status-preview" style="<?php echo esc_attr(($jsst_colour ? 'color:'.$jsst_colour.';' : '') . ($jsst_bgcolour ? 'background:'.$jsst_bgcolour.';border-color:'.$jsst_bgcolour : '')); ?>"><?php echo esc_html(isset($jsst_s->status) && $jsst_s->status !== '' ? jssupportticket::JSST_getVarValue($jsst_s->status) : __('Status', 'js-support-ticket')); ?></span></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Click a swatch to pick a colour.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_s->id) ? $jsst_s->id : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering', isset($jsst_s->ordering) ? $jsst_s->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'status_savestatus'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=status&jstlay=statuses')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Status', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
        <?php
        $jsst_jssupportticket_js ="
            jQuery(document).ready(function () {
                function jsstStatusPicker(field, swatch, prop) {
                    jQuery('input#' + field).iris({
                        color: jQuery('input#' + field).val(),
                        change: function (c_event, ui) {
                            var hex = ui.color.toString();
                            jQuery(swatch).css('background', hex);
                            jQuery('input#' + field).val(hex);
                            jQuery('.js-form-status-preview').css(prop, hex);
                            if (prop === 'background') {
                                jQuery('.js-form-status-preview').css('border-color', hex);
                            }
                        }
                    });
                    jQuery('#' + field + ', ' + swatch).click(function () {
                        jQuery('#statuscolor, #statusbgcolor').iris('hide');
                        jQuery('#' + field).iris('show');
                        return false;
                    });
                }
                jsstStatusPicker('statuscolor', '.js-form-statuscolor-wrp', 'color');
                jsstStatusPicker('statusbgcolor', '.js-form-statusbgcolor-wrp', 'background');
                jQuery('input#status').on('input', function () {
                    jQuery('.js-form-status-preview').text(jQuery(this).val() || '" . esc_js(__('Status', 'js-support-ticket')) . "');
                });
                jQuery(document).click(function (e) {
                    if (!jQuery(e.target).is('.colour-picker, .iris-picker, .iris-picker-inner, .js-form-statuscolor-wrp, .js-form-statusbgcolor-wrp')) {
                        jQuery('#statuscolor, #statusbgcolor').iris('hide');
                    }
                });
            });
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        ?>
    </div>
</div>
