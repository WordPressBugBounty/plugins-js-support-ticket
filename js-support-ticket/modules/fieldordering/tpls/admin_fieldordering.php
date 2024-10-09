<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');

$jssupportticket_js ='
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
        jQuery("table#js-support-ticket-table tbody").sortable({
            handle : ".jsst-order-grab-column",
            update  : function () {
                jQuery(".js-form-button").slideDown("slow");
                var abc =  jQuery("table#js-support-ticket-table tbody").sortable("serialize");
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
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);

wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');

JSSTmessage::getMessage(); ?>
<?php
$type = array(
    (object) array('id' => '1', 'text' => esc_html(__('Public', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Private', 'js-support-ticket')))
);
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <?php if(in_array('multiform', jssupportticket::$_active_addons)){ ?>
                            <li><a href="?page=multiform" title="<?php echo esc_html(__('Multiform','js-support-ticket')); ?>"><?php echo esc_html(__('Multiform','js-support-ticket')); ?></a></li>
                        <?php } ?>
                        <li><?php echo esc_html(__('Fields','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_html(__('Help','js-support-ticket')); ?>">
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
            <h1 class="jsstadmin-head-text">
                <?php echo esc_html(__('Fields','js-support-ticket')); ?>
                <?php if(isset(jssupportticket::$_data['multiFormTitle'])){ ?>
                    <span class="jsstadmin-head-sub-text">
                        <?php echo ' ('.esc_html(jssupportticket::$_data["multiFormTitle"]).')'; ?>
                    </span>
                <?php }?>
            </h1>
	    <?php if(isset(jssupportticket::$_data['formid']) && jssupportticket::$_data['formid'] != null){ $mformid = jssupportticket::$_data['formid'];}else{ $mformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();} ?>
            <a title="<?php echo esc_html(__('Add','js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=fieldordering&jstlay=adduserfeild&&fieldfor=<?php echo esc_attr(jssupportticket::$_data['fieldfor']); ?>&formid=<?php echo esc_attr($mformid) ?>"><img alt="<?php echo esc_html(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Add Field', 'js-support-ticket')); ?></a>
            <a target="blank" href="https://www.youtube.com/watch?v=c7whQ6F70yM" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_html(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                <?php echo esc_html(__('Watch Video','js-support-ticket')); ?>
            </a>
        </div>
        <div id="userpopupblack" style="display:none;"></div>
        <div id="userpopup" style="display:none;">
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <?php if (!empty(jssupportticket::$_data[0])) { ?>
                <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=jssupportticket&task=saveordering&formid=".esc_attr($mformid)),"save-ordering")); ?>">
                <table id="js-support-ticket-table">
                    <thead>
                    <tr class="js-support-ticket-table-heading">
                        <th><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('S.No', 'js-support-ticket')); ?></th>
                        <th class="left"><?php echo esc_html(__('Field Title', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('User Publish', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Visitor Publish', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Required', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 0;
                    $count = count(jssupportticket::$_data[0]) - 1;
                    foreach (jssupportticket::$_data[0] AS $field) {
                        if($field->field == 'wcorderid' || $field->field == 'wcproductid' || $field->field == 'wcitemid'){
                            if(!in_array('woocommerce', jssupportticket::$_active_addons)){
                                continue;
                            }
                            if(!class_exists('WooCommerce')){
                                continue;
                            }
                        }

                        if($field->field == 'eddorderid' || $field->field == 'eddproductid'){
                            if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                continue;
                            }
                            if(!class_exists('Easy_Digital_Downloads')){
                                continue;
                            }
                        }

                        if($field->field == 'eddlicensekey'){
                            if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                continue;
                            }
                            if(!class_exists('Easy_Digital_Downloads')){
                                continue;
                            }
                            if(!class_exists('EDD_Software_Licensing')){
                                continue;
                            }
                        }
                        if($field->field == 'wcitemid'){
                            continue;
                        }

                        if($field->field == 'envatopurchasecode'){
                            if(!in_array('envatovalidation', jssupportticket::$_active_addons)){
                                continue;
                            }
                        }

                        $alt = $field->published ? esc_html(__('Published','js-support-ticket')) : esc_html(__('Unpublished','js-support-ticket'));
                        $reqalt = $field->required ? esc_html(__('Required','js-support-ticket')) : esc_html(__('Not required','js-support-ticket'));
                        ?>
                        <tr id="id_<?php echo esc_attr($field->id); ?>">
                            <td class="js-textaligncenter jsst-order-grab-column">
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Ordering', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <img alt="<?php echo esc_html(__('grab','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/list-full.png'?>"/>
                            </td>

                            <td>
                            <span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('S.No','js-support-ticket')); ?>:</span>
                            <?php echo esc_html($field->id); ?></td>
                            <td class="left">
                            <span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Field Title','js-support-ticket')); ?>:</span>
                                <?php
                                    if ($field->fieldtitle)
                                        echo '<a title="'. esc_html(__('users popup','js-support-ticket')).'" href="#" id="userpopup" data-id='.esc_attr($field->id).'>'.esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)).'</a>';
                                    else echo esc_html($field->userfieldtitle);
                                    if($field->cannotunpublish == 1){
                                        echo '<font style="color:#1C6288;font-size:20px;margin:0px 5px;">*</font>';
                                    }
                                ?>
                            </td>
                            <td>
                            <span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('User Publish','js-support-ticket')); ?>:</span>
                                <?php if ($field->cannotunpublish == 1) { ?>
                                    <img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" title="<?php echo esc_html(__('Can Not Unpublished','js-support-ticket')); ?>" alt="<?php echo esc_html(__('good','js-support-ticket')); ?>" />
                                <?php }elseif ($field->published == 1) {
                                    $url  = "?page=fieldordering&task=changepublishstatus&action=jstask&status=unpublish&fieldorderingid=".esc_attr($field->id).'&fieldfor='.esc_attr(jssupportticket::$_data['fieldfor']).'&formid='.esc_attr($field->multiformid);
                                         ?>
                                        <a title="<?php echo esc_html(__('good','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url($url, 'change-publish-status')); ?>" ><img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" alt="<?php echo esc_html(__('good','js-support-ticket')); ?>" /></a>
                                <?php }else{
                                    $url  = "?page=fieldordering&task=changepublishstatus&action=jstask&status=publish&fieldorderingid=".esc_attr($field->id).'&fieldfor='.esc_attr(jssupportticket::$_data['fieldfor']).'&formid='.esc_attr($field->multiformid);
                                         ?>
                                        <a title="<?php echo esc_html(__('cross','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url($url, 'change-publish-status')); ?>" ><img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/close.png'; ?>" alt="<?php echo esc_html(__('cross','js-support-ticket')); ?>" /></a>
                                <?php } ?>
                            </td>
                            <td>
                            <span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Visitor Publish','js-support-ticket')); ?>:</span>
                                <?php if ($field->cannotunpublish == 1) { ?>
                                    <img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" title="<?php echo esc_html(__('Can Not Unpublished','js-support-ticket')); ?>" />
                                <?php }elseif ($field->isvisitorpublished == 1) {
                                    $url  = "?page=fieldordering&task=changevisitorpublishstatus&action=jstask&status=unpublish&fieldorderingid=".esc_attr($field->id).'&fieldfor='.esc_attr(jssupportticket::$_data['fieldfor']).'&formid='.esc_attr($field->multiformid);
                                         ?>
                                        <a title="<?php echo esc_html(__('good','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url($url, 'change-visitor-publish-status')); ?>" ><img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" alt="<?php echo esc_html(__('good','js-support-ticket')); ?>" /></a>
                                <?php }else{
                                    $url  = "?page=fieldordering&task=changevisitorpublishstatus&action=jstask&status=publish&fieldorderingid=".esc_attr($field->id).'&fieldfor='.esc_attr(jssupportticket::$_data['fieldfor']).'&formid='.esc_attr($field->multiformid);
                                         ?>
                                        <a title="<?php echo esc_html(__('cross','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url($url, 'change-visitor-publish-status')); ?>" ><img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/close.png'; ?>" alt="<?php echo esc_html(__('cross','js-support-ticket')); ?>" /></a>
                                <?php } ?>
                            </td>
                            <td>
                            <span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Required','js-support-ticket')); ?>:</span>
                                <?php if ($field->cannotunpublish == 1 || ($field->userfieldtype == 'termsandconditions' && $field->required == 1) ) { ?>
                                    <img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" alt="<?php echo esc_html(__('good','js-support-ticket')); ?>" title="<?php echo esc_html(__('can not mark as not required','js-support-ticket')); ?>" />
                                <?php }elseif ($field->required == 1) {
                                    $url  = "?page=fieldordering&task=changerequiredstatus&action=jstask&status=unrequired&fieldorderingid=".esc_attr($field->id).'&fieldfor='.esc_attr(jssupportticket::$_data['fieldfor']).'&formid='.esc_attr($field->multiformid);
                                         ?>
                                        <a title="<?php echo esc_html(__('good','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url($url, 'change-required-status')); ?>" ><img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" alt="<?php echo esc_html(__('good','js-support-ticket')); ?>" /></a>
                                <?php }else{
                                    $url  = "?page=fieldordering&task=changerequiredstatus&action=jstask&status=required&fieldorderingid=".esc_attr($field->id).'&fieldfor='.esc_attr(jssupportticket::$_data['fieldfor']).'&formid='.esc_attr($field->multiformid);
                                         ?>
                                        <a title="<?php echo esc_html(__('Close','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url($url, 'change-required-status')); ?>" ><img height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/close.png'; ?>" title="<?php echo esc_html(__('Close','js-support-ticket')); ?>" /></a>
                                <?php } ?>
                            </td>
                            <td>
                            <span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Action','js-support-ticket')); ?>:</span>
                                <?php
                                    if($field->isuserfield==1){
                                        echo wp_kses('<a title="'. esc_html(__('Edit','js-support-ticket')).'" class="action-btn" href="?page=fieldordering&jstlay=adduserfeild&jssupportticketid='.esc_attr($field->id).'&fieldfor='.jssupportticket::$_data['fieldfor'].'&formid='.esc_attr($field->multiformid).'"><img alt="'. esc_html(__('Edit','js-support-ticket')).'" src="'.JSST_PLUGIN_URL.'includes/images/edit.png" /></a>&nbsp;', JSST_ALLOWED_TAGS);
                                        echo wp_kses('<a title="'. esc_html(__('Delete','js-support-ticket')).'" class="action-btn" onclick="return confirm(\''. esc_html(__('Are you sure you want to delete it?','js-support-ticket')).'\');" href="'.esc_url(wp_nonce_url('?page=fieldordering&task=removeuserfeild&action=jstask&jssupportticketid='.esc_attr($field->id).'&fieldfor='.jssupportticket::$_data['fieldfor'].'&formid='.esc_attr($field->multiformid),'remove-userfeild')).'"><img alt="'. esc_html(__('Delete','js-support-ticket')).'" src="'.JSST_PLUGIN_URL.'includes/images/delete.png" /></a>', JSST_ALLOWED_TAGS);
                                    }else{
                                        echo '---';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                 </tbody>
                 </table>
                 <?php echo wp_kses(JSSTformfield::hidden('fields_ordering_new', '123'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering_for', 'fieldordering'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('fieldfor', jssupportticket::$_data['fieldfor']), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('pagenum_for_ordering', JSSTrequest::getVar('pagenum', 'get', 1)), JSST_ALLOWED_TAGS); ?>
                    <div class="js-form-button" style="display: none;">
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Ordering', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </form>
                <div class="jsstadmin-help-msg">
                    <?php echo wp_kses('<font style="color:#1C6288;font-size:20px;margin:0px 5px;vertical-align: middle;">*</font>'. esc_html(__('Cannot unpublished field','js-support-ticket')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php
                /*
                  if ( jssupportticket::$_data[1] ) {
                  echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
                  }
                 */
            } else {
                JSSTlayout::getNoRecordFound();
            }
            ?>
        </div>
    </div>
</div>
