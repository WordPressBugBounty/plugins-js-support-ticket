<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Add / edit a priority - the reference form screen for the admin design
 * system: one form panel, groups as fieldsets, each field sized by what it
 * holds, and a footer that follows the page down.
 *
 * Field names, nonces, the iris colour picker's hooks and every posted value
 * are unchanged.
 */
wp_enqueue_script('iris');
$jsst_jssupportticket_js ="
    jQuery(document).ready(function () {
        jQuery.validate();
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_dayshours = array(
    (object) array('id' => '1', 'text' => esc_html(__('Days', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Hours', 'js-support-ticket')))
);
$jsst_p        = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : false;
$jsst_isedit   = !empty($jsst_p->id);
$jsst_heading  = $jsst_isedit ? __('Edit Priority', 'js-support-ticket') : __('Add Priority', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_p->id) ? $jsst_p->id : '';
$jsst_colour   = isset($jsst_p->prioritycolour) ? $jsst_p->prioritycolour : '';
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('Priorities', 'js-support-ticket'), 'url' => admin_url('admin.php?page=priority&jstlay=priorities'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=priority&task=savepriority"),"save-priority-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The priority', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="priority"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('priority', isset($jsst_p->priority) ? $jsst_p->priority : '', array('data-validation' => 'required')), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="prioritycolor"><?php echo esc_html(__('Color', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval jsst-colour">
                                        <?php /* js-form-prioritycolor-wrp is the swatch the iris picker below paints. */ ?>
                                        <span class="jsst-colour-chip js-form-prioritycolor-wrp" style="<?php echo esc_attr($jsst_colour ? 'background:'.$jsst_colour : ''); ?>"></span>
                                        <?php echo wp_kses(JSSTformfield::text('prioritycolor', $jsst_colour, array('class' => 'jsst-colour-input', 'data-validation' => 'required', 'autocomplete' => 'off', 'placeholder' => '#000000')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Click the swatch to pick a colour.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>

                        <?php if(in_array('overdue', jssupportticket::$_active_addons)){ ?>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('When it becomes overdue', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="overdueinterval"><?php echo esc_html(__('Ticket Overdue', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval jsst-pair">
                                        <?php echo wp_kses(JSSTformfield::text('overdueinterval', isset($jsst_p->overdueinterval) ? $jsst_p->overdueinterval : '', array('inputmode' => 'numeric')), JSST_ALLOWED_TAGS) ?>
                                        <?php echo wp_kses(JSSTformfield::select('overduetypeid', $jsst_dayshours, (isset($jsst_p->overduetypeid) ? $jsst_p->overduetypeid : ''), '', array('class' => 'jsst-pair-select')), JSST_ALLOWED_TAGS)?>
                                    </div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Leave empty for no overdue rule.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>
                        <?php } ?>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Availability', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Public', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('ispublic', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset($jsst_p->ispublic) ? $jsst_p->ispublic : '1'), JSST_ALLOWED_TAGS); ?></div></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Whether customers can choose it themselves.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-md">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('isdefault', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset($jsst_p->isdefault) &&  $jsst_p->isdefault == 1 ? 1 : 0), JSST_ALLOWED_TAGS); ?></div></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Only one priority is the default; choosing this releases the current one.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => esc_html(__('Enabled', 'js-support-ticket')), '0' => esc_html(__('Disabled', 'js-support-ticket'))), isset($jsst_p->status) ? $jsst_p->status : '1'), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_p->id) ? $jsst_p->id : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering', isset($jsst_p->ordering) ? $jsst_p->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'priority_savepriority'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=priority&jstlay=priorities')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Priority', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
        <?php
        $jsst_jssupportticket_js ="
            jQuery(document).ready(function () {
                jQuery('input#prioritycolor').iris({
                    color: jQuery('input#prioritycolor').val(),
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    change: function (c_event, ui) {
                        hex = ui.color.toString();
                        jQuery('.js-form-prioritycolor-wrp').css( 'background', hex);
                        jQuery('input#prioritycolor').val(hex);
                    }
                });
                jQuery(document).click(function (e) {
                    if (!jQuery(e.target).is('.colour-picker, .iris-picker, .iris-picker-inner, .js-form-prioritycolor-wrp')) {
                        jQuery('#prioritycolor').iris('hide');
                    }
                });
                /* The swatch opens the picker as well as the field. */
                jQuery('#prioritycolor, .js-form-prioritycolor-wrp').click(function (event) {
                    jQuery('#prioritycolor').iris('hide');
                    jQuery('#prioritycolor').iris('show');
                    return false;
                });
            });
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        ?>
    </div>
</div>
