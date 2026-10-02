<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');

/**
 * The questions a form asks, in the order it asks them. (Roadmap 6.5-FORM-02)
 *
 * One screen for both sets: the ticket form's questions (fieldfor 1) and the
 * feedback form's (fieldfor 2). The three switches down the row - who sees it
 * and whether it must be answered - used to be a green tick or a red cross
 * you had to hover to read; they are words now, and the word is also the
 * control, so there is nothing to decode.
 *
 * `id_<n>` row ids, `input#fields_ordering_new` and the `a#userpopup` /
 * `close_popup()` pair that opens a field's options are unchanged - the
 * ordering post and the options dialog both still work the way they did.
 */
$jsst_jssupportticket_js ='
    function resetFrom() {
        document.getElementById("title").value = "";
        document.getElementById("categoryid").value = "";
        document.getElementById("type").value = "";
        document.getElementById("jssupportticketform").submit();
    }
    jQuery(document).ready(function () {
        jQuery("a#userpopup").click(function (e) {
            e.preventDefault();
            jQuery("div#userpopupblack").show();
            var f = jQuery(this).attr("data-id");
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "fieldordering", task: "getOptionsForFieldEdit",field:f, "_wpnonce":"'.esc_attr(wp_create_nonce("get-options-for-field-edit")).'"}, function (data) {
                if(data){
                    var abc = jQuery.parseJSON(data)
                    jQuery("div#userpopup").html("");
                    jQuery("div#userpopup").html(jsstDecodeHTML(abc));
                }
            });
            jQuery("div#userpopup").slideDown("slow");
        });
        jQuery("span.close, div#userpopupblack").click(function (e) {
            jQuery("div#userpopup").slideUp("slow", function () {
                jQuery("div#userpopupblack").hide();
            });

        });
        jQuery("table.jsst-table tbody").sortable({
            handle : ".jsst-grab",
            axis : "y",
            update  : function () {
                jQuery(".jsst-orderbar").slideDown("slow");
                var abc =  jQuery("table.jsst-table tbody").sortable("serialize");
                jQuery("input#fields_ordering_new").val(abc);
            }
        });
    });
    function close_popup(){
        jQuery("div#userpopup").slideUp("slow", function () {
            jQuery("div#userpopupblack").hide();
        });
    }

';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);

wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);

JSSTmessage::getMessage();

$jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
$jsst_mformid  = isset(jssupportticket::$jsst_data['formid']) ? jssupportticket::$jsst_data['formid'] : JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
$jsst_isfeedback = ((int) $jsst_fieldfor === 2);

/* Rows this site cannot use are skipped rather than drawn greyed: a question
   about a WooCommerce order on a desk with no WooCommerce is not a setting,
   it is noise. Unchanged from before - only moved out of the markup. */
$jsst_skip = function ($jsst_field) {
    $jsst_f = $jsst_field->field;
    if (in_array($jsst_f, array('wcorderid','wcproductid','wcitemid'), true)) {
        if (!in_array('woocommerce', jssupportticket::$_active_addons) || !class_exists('WooCommerce')) { return true; }
    }
    if (in_array($jsst_f, array('eddorderid','eddproductid'), true)) {
        if (!in_array('easydigitaldownloads', jssupportticket::$_active_addons) || !class_exists('Easy_Digital_Downloads')) { return true; }
    }
    if ($jsst_f === 'eddlicensekey') {
        if (!in_array('easydigitaldownloads', jssupportticket::$_active_addons) || !class_exists('Easy_Digital_Downloads') || !class_exists('EDD_Software_Licensing')) { return true; }
    }
    if ($jsst_f === 'envatopurchasecode' && !in_array('envatovalidation', jssupportticket::$_active_addons)) { return true; }
    /* Status, assignment and due date are set on the ticket, not asked for on
       the form, so they are not questions this screen governs. */
    if (in_array($jsst_f, array('wcitemid','status','assignto','duedate'), true)) { return true; }
    return false;
};

/* A switch that is a word and a link at once. $jsst_on decides which way it
   reads; $jsst_url is null when the field is one the form cannot do without,
   and it then says so instead of offering a switch. */
$jsst_toggle = function ($jsst_on, $jsst_url, $jsst_onword, $jsst_offword, $jsst_fixednote = '') {
    if ($jsst_url === null) {
        echo '<span class="jsst-mark jsst-mark-on" title="' . esc_attr($jsst_fixednote) . '">' . esc_html($jsst_onword) . '</span>';
        return;
    }
    $jsst_cls = $jsst_on ? 'jsst-mark jsst-mark-on' : 'jsst-mark';
    echo '<a class="' . esc_attr($jsst_cls) . '" href="' . esc_url($jsst_url) . '">' . esc_html($jsst_on ? $jsst_onword : $jsst_offword) . '</a>';
};
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => $jsst_isfeedback ? __('Feedback Fields','js-support-ticket') : __('Fields','js-support-ticket'),
            'sub'     => isset(jssupportticket::$jsst_data['multiFormTitle']) ? jssupportticket::$jsst_data['multiFormTitle'] : '',
            'actions' => array(
                array('text' => __('Add Field', 'js-support-ticket'), 'url' => admin_url('admin.php?page=fieldordering&jstlay=adduserfeild&fieldfor=' . $jsst_fieldfor . '&formid=' . $jsst_mformid), 'icon' => 'plus'),
            ),
        )); ?>
        <?php JSSTlayout::adminPopupShell(); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=jssupportticket&task=saveordering&formid=".esc_attr($jsst_mformid)),"save-ordering")); ?>">
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-grab"><span class="screen-reader-text"><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></span></th>
                            <th class="jsst-col-name"><?php echo esc_html(__('Field Title', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Signed-in users', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Visitors', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Required', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach (jssupportticket::$jsst_data[0] AS $jsst_field) {
                            if ($jsst_skip($jsst_field)) { continue; }

                            $jsst_locked  = ((int) $jsst_field->cannotunpublish === 1);
                            $jsst_istc    = in_array($jsst_field->field, array('termsandconditions1','termsandconditions2','termsandconditions3'), true)
                                            || ($jsst_field->userfieldtype === 'termsandconditions' && (int) $jsst_field->required === 1);
                            $jsst_editurl = '?page=fieldordering&jstlay=adduserfeild&jssupportticketid='.esc_attr($jsst_field->id).'&fieldfor='.esc_attr($jsst_fieldfor).'&formid='.esc_attr($jsst_field->multiformid);
                            $jsst_base    = '&action=jstask&fieldorderingid='.esc_attr($jsst_field->id).'&fieldfor='.esc_attr($jsst_fieldfor).'&formid='.esc_attr($jsst_field->multiformid);
                            ?>
                            <tr id="id_<?php echo esc_attr($jsst_field->id); ?>">
                                <td class="jsst-col-grab">
                                    <span class="jsst-grab" role="img" aria-label="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>" title="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>"></span>
                                </td>
                                <th scope="row" class="jsst-col-name">
                                    <span class="jsst-ident-text">
                                        <?php if ($jsst_field->fieldtitle) { ?>
                                            <a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>" data-id="<?php echo esc_attr($jsst_field->id); ?>"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?></a>
                                        <?php } else { ?>
                                            <span class="jsst-table-name"><?php echo esc_html($jsst_field->userfieldtitle); ?></span>
                                        <?php } ?>
                                        <span class="jsst-table-sub">
                                            <?php echo esc_html($jsst_field->userfieldtype); ?>
                                            <?php if ($jsst_locked) { ?>
                                                &middot; <?php echo esc_html(__('the form needs this one', 'js-support-ticket')); ?>
                                            <?php } ?>
                                        </span>
                                    </span>
                                </th>
                                <td class="jsst-col-fit"><?php
                                    $jsst_toggle(
                                        (int) $jsst_field->published === 1,
                                        $jsst_locked ? null : wp_nonce_url('?page=fieldordering&task=changepublishstatus&status=' . ((int) $jsst_field->published === 1 ? 'unpublish' : 'publish') . $jsst_base, 'change-publish-status-'.$jsst_field->id),
                                        __('Shown', 'js-support-ticket'), __('Hidden', 'js-support-ticket'),
                                        __('This question cannot be turned off.', 'js-support-ticket')
                                    ); ?></td>
                                <td class="jsst-col-fit"><?php
                                    $jsst_toggle(
                                        (int) $jsst_field->isvisitorpublished === 1,
                                        $jsst_locked ? null : wp_nonce_url('?page=fieldordering&task=changevisitorpublishstatus&status=' . ((int) $jsst_field->isvisitorpublished === 1 ? 'unpublish' : 'publish') . $jsst_base, 'change-visitor-publish-status-'.$jsst_field->id),
                                        __('Shown', 'js-support-ticket'), __('Hidden', 'js-support-ticket'),
                                        __('This question cannot be turned off.', 'js-support-ticket')
                                    ); ?></td>
                                <td class="jsst-col-fit"><?php
                                    $jsst_toggle(
                                        (int) $jsst_field->required === 1,
                                        ($jsst_locked || $jsst_istc) ? null : wp_nonce_url('?page=fieldordering&task=changerequiredstatus&status=' . ((int) $jsst_field->required === 1 ? 'unrequired' : 'required') . $jsst_base, 'change-required-status-'.$jsst_field->id),
                                        __('Required', 'js-support-ticket'), __('Optional', 'js-support-ticket'),
                                        __('This question must always be answered.', 'js-support-ticket')
                                    ); ?></td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                        <?php if ((int) $jsst_field->isuserfield === 1) { ?>
                                            <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?','js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=fieldordering&task=removeuserfeild&action=jstask&jssupportticketid='.esc_attr($jsst_field->id).'&fieldfor='.esc_attr($jsst_fieldfor).'&formid='.esc_attr($jsst_field->multiformid),'remove-userfeild-'.$jsst_field->id)); ?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
                                        <?php } ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                    <?php echo wp_kses(JSSTformfield::hidden('fields_ordering_new', '123'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering_for', 'fieldordering'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('fieldfor', $jsst_fieldfor), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('pagenum_for_ordering', JSSTrequest::getVar('pagenum', 'get', 1)), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-orderbar" style="display: none;">
                        <span class="jsst-orderbar-note"><?php echo esc_html(__('You changed the order of this list.', 'js-support-ticket')); ?></span>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Ordering', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </form>
                <p class="jsst-hint"><?php echo esc_html(__('Upload and tick-box questions cannot be made required. A question the form needs is marked as such and has no switch.', 'js-support-ticket')); ?></p>
            <?php } else { ?>
                <div class="jsst-card">
                    <?php JSSTlayout::adminEmpty(
                        __('No fields found.', 'js-support-ticket'),
                        $jsst_isfeedback
                            ? __('These are the questions asked when a customer rates a closed ticket.', 'js-support-ticket')
                            : __('These are the questions the ticket form asks, in the order it asks them.', 'js-support-ticket'),
                        __('Add field', 'js-support-ticket'),
                        admin_url('admin.php?page=fieldordering&jstlay=adduserfeild&fieldfor=' . $jsst_fieldfor . '&formid=' . $jsst_mformid)
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
