<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('file_validate.js', JSST_PLUGIN_URL . 'includes/js/file_validate.js');
    wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');
?>
<?php JSSTmessage::getMessage(); ?>
<?php $formdata = JSSTformfield::getFormData(); ?>
<?php
$js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
$jssupportticket_js ='
    function updateuserlist(pagenum){
        jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "jssupportticket", task: "getuserlistajax",userlimit:pagenum, "_wpnonce":"'. esc_attr(wp_create_nonce("get-user-list-ajax")) .'"}, function (data) {
            if(data){
                jQuery("div#userpopup-records").html("");
                jQuery("div#userpopup-records").html(data);
                setUserLink();
            }
        });
    }
    function setUserLink() {
        jQuery("a.js-userpopup-link").each(function () {
            var anchor = jQuery(this);
            jQuery(anchor).click(function (e) {
                var id = jQuery(this).attr("data-id");
                var name = jQuery(this).html();
                var email = jQuery(this).attr("data-email");
                var displayname = jQuery(this).attr("data-name");
                jQuery("input#username-text").val(name);
                // if(jQuery("input#name").val() == ""){
                    jQuery("input#name").val(displayname);
                // }
                // if(jQuery("input#email").val() == ""){
                    jQuery("input#email").val(email);
                // }
                jQuery("input#uid").val(id);
                jQuery("div#userpopup").slideUp("slow", function () {
                    jQuery("div#userpopupblack").hide();
                });
            });
        });
    }
    jQuery(document).ready(function () {
        
        jQuery("a#userpopup").click(function (e) {
            e.preventDefault();
            jQuery("div#userpopupblack").show();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "jssupportticket", task: "getuserlistajax", "_wpnonce":"'. esc_attr(wp_create_nonce("get-user-list-ajax")) .'"}, function (data) {
                if(data){
                    jQuery("div#userpopup-records").html("");
                    jQuery("div#userpopup-records").html(data);
                    setUserLink();
                }
            });
            jQuery("div#userpopup").slideDown("slow");
        });
        jQuery("form#userpopupsearch").submit(function (e) {
            e.preventDefault();
            var username = jQuery("input#username").val();
            var name = jQuery("input#name").val();
            var emailaddress = jQuery("input#emailaddress").val();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", name: name, username: username, emailaddress: emailaddress, jstmod: "jssupportticket", task: "getusersearchajax", "_wpnonce":"'. esc_attr(wp_create_nonce("get-usersearch-ajax")) .'"}, function (data) {
                if (data) {
                    jQuery("div#userpopup-records").html(data);
                    setUserLink();
                }
            });//jquery closed
        });
        jQuery(".userpopup-close, div#userpopupblack").click(function (e) {
            jQuery("div#userpopup").slideUp("slow", function () {
                jQuery("div#userpopupblack").hide();
            });

        });
    });
    // to get premade and append to isssue summery
    function getpremade(val) {
        jQuery.post(ajaxurl, {action: "jsticket_ajax", val: val, jstmod: "cannedresponses", task: "getpremadeajax", "_wpnonce":"'. esc_attr(wp_create_nonce("get-premade-ajax")) .'"}, function (data) {
            if (data) {
                var append = jQuery("input#append1:checked").length;
                if (append == 1) {
                    var content = tinyMCE.get("jsticket_message").getContent();
                    content = content + data;
                    tinyMCE.get("jsticket_message").execCommand("mceSetContent", false, content);
                }
                else {
                    tinyMCE.get("jsticket_message").execCommand("mceSetContent", false, data);
                }
            }
        });//jquery closed
    }
    // to get premade and append to isssue summery
    function getHelpTopicByDepartment(val) {
        jQuery.post(ajaxurl, {action: "jsticket_ajax", val: val, jstmod: "department", task: "getHelpTopicByDepartment", "_wpnonce":"'. esc_attr(wp_create_nonce("get-help-topic-by-department")) .'"}, function (data) {
            if (data != false) {
                jQuery("div#helptopic").html(data);
            }else{
                jQuery("div#helptopic").html( "<div class=\'helptopic-no-rec\'>'. esc_html(__('No help topic found','js-support-ticket')).'</div>");
            }
        });//jquery closed
    }

    function getPremadeByDepartment(val) {
        jQuery.post(ajaxurl, {action: "jsticket_ajax", val: val, jstmod: "department", task: "getPremadeByDepartment", "_wpnonce":"'. esc_attr(wp_create_nonce("get-premade-by-department")) .'"}, function (data) {
            if (data != false) {
                jQuery("div#premade").html(jsstDecodeHTML(data));
            }else{
                jQuery("div#premade").html("<div class=\'premade-no-rec\'>'. esc_html(__('No canned response found','js-support-ticket')) .'</div>");
            }
        });//jquery closed
    }
    ';
$jssupportticket_js .='
    jQuery(document).ready(function ($) {
        $(".custom_date").datepicker({dateFormat: "'. esc_html($js_scriptdateformat) .'"});
        jQuery("#tk_attachment_add").click(function () {
            var obj = this;
            var current_files = jQuery(\'input[name="filename[]"]\').length;
            var total_allow ='. esc_attr(jssupportticket::$_config["no_of_attachement"]).'
            var append_text = "<span class=\'tk_attachment_value_text\'><input name=\'filename[]\' type=\'file\' onchange=\"uploadfile(this,\''. esc_js(jssupportticket::$_config['file_maximum_size']) .'\',\''. esc_js(jssupportticket::$_config['file_extension']) .'\');\" size=\'20\' maxlenght=\'30\'  /><span  class=\'tk_attachment_remove\'></span></span>";

            if (current_files < total_allow) {
                jQuery(".tk_attachment_value_wrapperform").append(append_text);
            } else if ((current_files === total_allow) || (current_files > total_allow)) {
                alert("'. __("File upload limit exceeds", "js-support-ticket") .'");
                jQuery(obj).hide();
            }
        });

        jQuery(document).delegate(".tk_attachment_remove", "click", function (e) {
            jQuery(this).parent().remove();
            var current_files = jQuery("input[name=\"filename[]\"]").length;
            var total_allow ='. esc_attr(jssupportticket::$_config["no_of_attachement"]).'
            if (current_files < total_allow) {
                jQuery("#tk_attachment_add").show();
            }
        });
        $.validate();
    });
    // woocomerce
    function jsst_wc_order_products(productid){
        var orderid = jQuery("#wcorderid").val();
        if(orderid){
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "woocommerce", task: "getWcOrderProductsAjax",orderid: orderid,productid: productid, "_wpnonce":"'. esc_attr(wp_create_nonce("get-wcorder-products-ajax")).'"},function (data) {
                    data = JSON.parse(data);
                    jQuery("#wcproductid-wrap").html(jsstDecodeHTML(data.html));
                    if(data.productfound){
                        jQuery(".jsst_product_found").show();
                    }else{
                        jQuery(".jsst_product_not_found").show();
                    }
                }
            );
        }
    }
    function jsst_edd_order_products(){
        var orderid = jQuery("select#eddorderid").val();
        jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "easydigitaldownloads", task: "getEDDOrderProductsAjax", eddorderid:orderid, "_wpnonce":"'. esc_attr(wp_create_nonce("get-eddorder-products-ajax")) .'"}, function (data) {
                jQuery("#eddproductid-wrap").html(data);
            }
        );
    }

    function jsst_eed_product_licenses(){
        var eddproductid = jQuery("select#eddproductid").val();
        var orderid = jQuery("select#eddorderid").val();
        jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "easydigitaldownloads", task: "getEDDProductlicensesAjax", eddproductid:eddproductid, eddorderid:orderid, "_wpnonce":"'. esc_attr(wp_create_nonce("get-edd-productlicenses-ajax")) .'"}, function (data) {
                jQuery("#eddlicensekey-wrap").html(data);
            }
        );
    }

    jQuery(document).ready(function(){
        //jQuery("select#eddorderid").change(function(){
        jQuery(document).on("change", "select#eddorderid", function() {
            jsst_edd_order_products();
        });';
        if(!isset(jssupportticket::$_data[0]->id)){
            $jssupportticket_js .='
            if(jQuery("select#eddorderid").val()){
                jsst_edd_order_products();
            }';
        }
        $jssupportticket_js .='
        jQuery(document).on("change", "select#eddproductid", function() {
            jsst_eed_product_licenses();
        });
        if(jQuery("select#eddproductid").val()){
            jsst_eed_product_licenses();
        }

        jQuery("#wcorderid").focusout(function(){
            jsst_wc_order_products();
            jQuery("input#wcorderid").removeClass("loading");
        });
        jQuery("#wcorderid").keyup(function(){
            jQuery(".jsst_product_found").hide();
            jQuery(".jsst_product_not_found").hide();
            if(jQuery("#wcorderid").val()){
                jQuery("input#wcorderid").addClass("loading");
            }else{
                jQuery("input#wcorderid").removeClass("loading");
            }
        });
        if(jQuery("#wcorderid").val()){
            jsst_wc_order_products();
        }
    });
';
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
<span style="display:none" id="filesize"><?php echo esc_html(__('Error file size too large', 'js-support-ticket')); ?></span>
<span style="display:none" id="fileext"><?php echo esc_html(__('The uploaded file extension not valid', 'js-support-ticket')); ?></span>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php
        if(current_user_can('jsst_support_ticket')){
            JSSTincluder::getClassesInclude('jsstadminsidemenu');
        }
        ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Create Ticket','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Create Ticket','js-support-ticket')); ?></h1>
            <a target="blank" href="https://www.youtube.com/watch?v=zmQ4bpqSYnk" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_html(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                <?php echo esc_html(__('Watch Video','js-support-ticket')); ?>
            </a>
        </div>
        <div id="jsstadmin-data-wrp">
            <div id="userpopupblack" style="display:none;"></div>
            <div id="userpopup" style="display:none;">
                <div class="userpopup-top">
                    <div class="userpopup-heading">
                        <?php echo esc_html(__('Select user','js-support-ticket')); ?>
                    </div>
                    <img alt="<?php echo esc_html(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                </div>
                <div class="userpopup-search">
                    <form id="userpopupsearch">
                        <div class="userpopup-fields-wrp">
                            <div class="userpopup-fields">
                                <input type="text" name="username" id="username" placeholder="<?php echo esc_html(__('Username','js-support-ticket')); ?>" />
                            </div>
                            <div class="userpopup-fields">
                                <input type="text" name="name" id="name" placeholder="<?php echo esc_html(__('Name','js-support-ticket')); ?>" />
                            </div>
                            <div class="userpopup-fields">
                                <input type="text" name="emailaddress" id="emailaddress" placeholder="<?php echo esc_html(__('Email Address','js-support-ticket')); ?>"/>
                            </div>
                            <div class="userpopup-btn-wrp">
                                <input class="userpopup-search-btn" type="submit" value="<?php echo esc_html(__('Search','js-support-ticket')); ?>" />
                                <input class="userpopup-reset-btn" type="submit" onclick="document.getElementById('name').value = '';document.getElementById('username').value = ''; document.getElementById('emailaddress').value = '';" value="<?php echo esc_html(__('Reset','js-support-ticket')); ?>" />
                            </div>
                        </div>
                    </form>
                </div>
                <div id="userpopup-records-wrp">
                    <div id="userpopup-records">
                        <div class="userpopup-records-desc">
                            <?php echo esc_html(__('Use search feature to select the user','js-support-ticket')); ?>
                        </div>
                    </div>
                </div>
            </div>
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=saveticket"),"save-ticket")); ?>" id="adminTicketform" enctype="multipart/form-data">
                <?php
                    $i = '';
                    foreach (jssupportticket::$_data['fieldordering'] AS $field):
                        switch ($field->field) {
                            case 'users':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php if (isset(jssupportticket::$_data[0]->uid)) { ?>
                                            <input class="js-form-diabled-field" type="text" id="username-text" value="<?php if(isset($formdata['username-text'])) echo esc_attr($formdata['username-text']); else echo esc_attr(jssupportticket::$_data[0]->name); ?>" readonly="readonly" <?php if($field->required == 1) echo 'data-validation="required"'; ?>/><div id="username-div"></div>
                                            <?php } else {
                                            ?>
                                            <input class="js-form-diabled-field" type="text" value="<?php if(isset($formdata['username-text'])) echo esc_attr($formdata['username-text']); ?>" id="username-text" readonly="readonly" <?php if($field->required == 1) echo 'data-validation="required"'; ?>/><a href="javascript:void(0);" id="userpopup" title="<?php echo esc_html(__('Select User', 'js-support-ticket')); ?>"><?php echo esc_html(__('Select User', 'js-support-ticket')); ?></a><div id="username-div"></div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'email':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['email'])) $email =  $formdata['email'];
                                            elseif(isset(jssupportticket::$_data[0]->email)) $email = jssupportticket::$_data[0]->email;
                                            else $email = ''; // Admin email not appear in form
                                            echo wp_kses(JSSTformfield::text('email', $email, array('class' => 'inputbox js-form-input-field', 'data-validation' => 'email')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'fullname':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['name'])) $name = $formdata['name'];
                                            elseif(isset(jssupportticket::$_data[0]->name)) $name = jssupportticket::$_data[0]->name;
                                            else $name = ''; // Admin full name not appear in form
                                            echo wp_kses(JSSTformfield::text('name', $name, array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'phone':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['phone'])) $phone = $formdata['phone'];
                                            elseif(isset(jssupportticket::$_data[0]->phone)) $phone = jssupportticket::$_data[0]->phone;
                                            else $phone = '';
                                            echo wp_kses(JSSTformfield::text('phone', $phone, array('class' => 'inputbox js-form-input-field','data-validation'=>($field->required) ? 'required':'')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'phoneext':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['phoneext'])) $phoneext = $formdata['phoneext'];
                                            elseif(isset(jssupportticket::$_data[0]->phoneext)) $phoneext = jssupportticket::$_data[0]->phoneext;
                                            else $phoneext = '';
                                            echo wp_kses(JSSTformfield::text('phoneext', $phoneext, array('class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'department':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['departmentid'])) $departmentid = $formdata['departmentid'];
                                            elseif(isset(jssupportticket::$_data[0]->departmentid)) $departmentid = jssupportticket::$_data[0]->departmentid;
                                            elseif(JSSTrequest::getVar('departmentid',0) > 0) $departmentid = JSSTrequest::getVar('departmentid');
                                            else $departmentid = JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
                                            $jsVisibleFunction = '';
                                            $defaultFunc = '';
                                            // code for visible field
                                            if ($field->visible_field != null) {
                                                $visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($field->visible_field);
                                                foreach ($visibleparams as $visibleparam) {
                                                    $wpnonce = wp_create_nonce("is-field-required");
                                                    $jsVisibleFunction .= " getDataForVisibleField('".$wpnonce."', this.value, '" . $visibleparam->visibleParent . "','" . $visibleparam->visibleParentField . "','".$visibleparam->visibleValue."','".$visibleparam->visibleCondition."');";
                                                    //for default value
                                                    if (($visibleparam->visibleValue == $departmentid && $visibleparam->visibleCondition == 1) || ($visibleparam->visibleValue != $departmentid && $visibleparam->visibleCondition == 0)) {
                                                        $wpnonce = wp_create_nonce("is-field-required");
                                                        $defaultFunc .= " getDataForVisibleField('".$wpnonce."', '".$departmentid."', '" . $visibleparam->visibleParent . "','" . $visibleparam->visibleParentField . "','".$visibleparam->visibleValue."','".$visibleparam->visibleCondition."');";
                                                    }
                                                }
                                                $script = '';
                                                if (isset($defaultFunc) && !isset(jssupportticket::$_data[0]->id)) {
                                                    $jssupportticket_js ='
                                                        jQuery(document).ready(function(){
                                                            '.esc_js($defaultFunc).'
                                                        });
                                                    ';
                                                    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
                                                }
                                            }
                                            if(in_array('cannedresponses', jssupportticket::$_active_addons)){
                                                echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $departmentid, esc_html(__('Select Department', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field', 'onchange' => $jsVisibleFunction.' getHelpTopicByDepartment(this.value);getPremadeByDepartment(this.value);', 'data-validation' => ($field->required) ? 'required':'')), JSST_ALLOWED_TAGS);
                                            }else{
                                                echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $departmentid, esc_html(__('Select Department', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field', 'onchange' => $jsVisibleFunction.' getHelpTopicByDepartment(this.value);', 'data-validation' => ($field->required) ? 'required':'')), JSST_ALLOWED_TAGS);
                                            }
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'helptopic':
                                if(!in_array('helptopic', jssupportticket::$_active_addons)){
                                    break;
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value" id="helptopic">
                                        <?php
                                            if(isset($formdata['helptopicid'])) $helptopicid = $formdata['helptopicid'];
                                            elseif(isset(jssupportticket::$_data[0]->helptopicid)) $helptopicid = jssupportticket::$_data[0]->helptopicid;
                                            elseif(JSSTrequest::getVar('helptopicid',0) > 0) $helptopicid = JSSTrequest::getVar('helptopicid');
                                            else $helptopicid = '';
                                            if (isset($departmentid)) {
                                                $dep_id = $departmentid;
                                            } else{
                                                $dep_id = 0;
                                            }
                                            echo wp_kses(JSSTformfield::select('helptopicid', JSSTincluder::getJSModel('helptopic')->getHelpTopicsForCombobox($dep_id), $helptopicid, esc_html(__('Select Help Topic', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field','data-validation'=>($field->required) ? 'required': '')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'priority':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['priorityid'])) $priorityid = $formdata['priorityid'];
                                            elseif(isset(jssupportticket::$_data[0]->priorityid)) $priorityid = jssupportticket::$_data[0]->priorityid;
                                            else $priorityid = JSSTincluder::getJSModel('priority')->getDefaultPriorityID();
                                            echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), $priorityid, esc_html(__('Select Priority', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                                case 'internalnotetitle':
                                    if(!in_array('note', jssupportticket::$_active_addons)){
                                        break;
                                    }
                                        ?>
                                        <div class="js-form-wrapper">
                                            <div class="js-form-title">
                                                <a target="blank" href="https://www.youtube.com/watch?v=p3vT2vhSkjk" class="js-tkt-det-hdg-img js-cp-video-internal-note">
                                                    <img title="<?php echo esc_html(__('watch video','js-support-ticket')); ?>" alt="<?php echo esc_html(__('watch video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) ?>/includes/images/watch-video-icon.png" />
                                                </a>
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                            <div class="js-form-value">
                                                <?php
                                                    if(isset($formdata['internalnotetitle'])) $internalnotetitle = $formdata['internalnotetitle'];
                                                    else $internalnotetitle = '';
                                                    echo wp_kses(JSSTformfield::text('internalnotetitle', $internalnotetitle, array('class' => 'inputbox js-form-input-field','data-validation'=>($field->required == 1) ? 'required': '')), JSST_ALLOWED_TAGS);
                                                ?>
                                            </div>
                                        </div>
                                        <div class="js-form-wrapper fullwidth">
                                            <div class="js-form-title"><?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?></div>
                                            <div class="js-form-value">
                                                <?php if (isset(jssupportticket::$_data[0]->id)) { ?>
                                                    <div class="js-form-title"><?php echo esc_html(__('Reason for edit', 'js-support-ticket')); ?><br></div>
                                                <?php } ?>
                                                <?php
                                                    if(isset($formdata['internalnote'])) $internalnote = $formdata['internalnote'];
                                                    elseif(isset(jssupportticket::$_data[0]->internalnote)) $internalnote = jssupportticket::$_data[0]->internalnote;
                                                    else $internalnote = '';
                                                    wp_editor($internalnote, 'internalnote', array('media_buttons' => false));
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                    break;
                            case 'duedate':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['duedate'])) $duedate = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($formdata['duedate']));
                                            elseif(isset(jssupportticket::$_data[0]->duedate) && jssupportticket::$_data[0]->duedate != '0000-00-00 00:00:00'){
                                                $duedate = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$_data[0]->duedate));
                                            }else $duedate = '';
                                            echo wp_kses(JSSTformfield::text('duedate', $duedate, array('class' => 'custom_date js-form-date-field','data-validation'=>($field->required) ? 'required': '')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'status':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['status'])) $status = $formdata['status'];
                                            elseif(isset(jssupportticket::$_data[0]->status)) $status = jssupportticket::$_data[0]->status;
                                            else $status = '1';
                                            echo wp_kses(JSSTformfield::select('status', array((Object) array('id' => '0', 'text' => esc_html(__('Active', 'js-support-ticket'))), (Object) array('id' => '', 'text' => esc_html(__('Disabled', 'js-support-ticket'))), (Object) array('id' => '2', 'text' => esc_html(__('Waiting agent reply', 'js-support-ticket'))), (Object) array('id' => '3', 'text' => esc_html(__('Waiting customer reply', 'js-support-ticket'))), (Object) array('id' => '4', 'text' => esc_html(__('Close ticket', 'js-support-ticket')))), $status, esc_html(__('Select Status', 'js-support-ticket')), array('class' => 'radiobutton js-form-select-field','data-validation'=>($field->required) ? 'required': '')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'assignto':
                                if (! in_array('agent',jssupportticket::$_active_addons)) {
                                    break;
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['staffid'])) $staffid = $formdata['staffid'];
                                            elseif(isset(jssupportticket::$_data[0]->staffid)) $staffid = jssupportticket::$_data[0]->staffid;
                                            else $staffid = '';
                                            echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), $staffid, esc_html(__('Select Agent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field','data-validation'=>($field->required) ? 'required': '')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'subject':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['subject'])) $subject = $formdata['subject'];
                                            elseif(isset(jssupportticket::$_data[0]->subject)) $subject = jssupportticket::$_data[0]->subject;
                                            else $subject = '';
                                            echo wp_kses(JSSTformfield::text('subject', $subject, array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required','style'=>'width:100%;')), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'premade':
                                if(!in_array('cannedresponses', jssupportticket::$_active_addons)){
                                    break;
                                }
                                // if($fieldcounter != 0){
                                //     echo '</div>';
                                //     $fieldcounter = 0;
                                // }
                                $text = JSSTincluder::getJSModel('cannedresponses')->getPreMadeMessageForCombobox();
                                ?>
                                <div class="js-form-wrapper fullwidth">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?></div>
                                    <div class="js-form-value">
                                        <div id="premade">
                                            <?php
                                                foreach($text as $premade){
                                                    ?>
                                                    <div class="js-form-perm-msg" onclick="getpremade(<?php echo esc_js($premade->id); ?>);">
                                                        <a href="javascript:void(0)" title="<?php echo esc_html(__('premade','js-support-ticket')); ?>"><?php echo esc_html($premade->text); ?></a>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                        </div>
                                        <div class="js-form-append">
                                            <?php echo wp_kses(JSSTformfield::checkbox('append', array('1' => esc_html(__('Append', 'js-support-ticket'))), '', array('class' => 'radiobutton js-form-radio-field')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'issuesummary':
                                ?>
                                <div class="js-form-wrapper fullwidth">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($formdata['message'])) $message = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($formdata['message']);
                                            elseif(isset(jssupportticket::$_data[0]->message)) $message = jssupportticket::$_data[0]->message;
                                            else $message = '';
                                            wp_editor($message, 'jsticket_message', array('media_buttons' => false));
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;

                            case 'wcorderid':
                                if(!in_array('woocommerce', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!class_exists('WooCommerce')){
                                    break;
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                        // $orderlist = array();
                                        // foreach(wc_get_orders(array()) as $order){
                                        //     $orderlist[] = (object) array('id' => $order->get_id(),'text'=>'#'.$order->get_id().' - '.$order->get_date_created()->date_i18n(wc_date_format()).' - '.$order->get_billing_first_name().' '.$order->get_billing_last_name());
                                        // }
                                        if(isset($formdata['wcorderid'])) $wcorderid = $formdata['wcorderid'];
                                        elseif(isset(jssupportticket::$_data[0]->wcorderid)) $wcorderid = jssupportticket::$_data[0]->wcorderid;
                                        else $wcorderid = '';
                                        // echo wp_kses(JSSTformfield::select('wcorderid', $orderlist, $wcorderid, esc_html(__('Select Order', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::text('wcorderid', $wcorderid, array('class' => 'inputbox js-form-input-field', 'data-validation' => ($field->required == 1) ? 'required' : '','style'=>'width:100%;', 'placeholder' => esc_html(__('Enter valid woocommerce order#' , 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                                        <span class="jsst_product_found" title="<?php echo esc_html(__("Order id found","js-support-ticket")); ?>" style="display: none;"></span>
                                        <span class="jsst_product_not_found" title="<?php echo esc_html(__("Order id not found","js-support-ticket")); ?>" style="display: none;"></span>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'wcproductid':
                                if(!in_array('woocommerce', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!class_exists('WooCommerce')){
                                    break;
                                }
                                 ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value" id="wcproductid-wrap">
                                        <?php
                                            $itemlist = array();
                                            if(isset($formdata['wcproductid'])) $wcproductid = $formdata['wcproductid'];
                                            elseif(isset(jssupportticket::$_data[0]->wcproductid)) $wcproductid = jssupportticket::$_data[0]->wcproductid;
                                            else $wcproductid = '';
                                            echo wp_kses(JSSTformfield::select('wcproductid', $itemlist, $wcproductid, esc_html(__('Select Product', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'eddorderid':
                                if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!class_exists('Easy_Digital_Downloads')){
                                    break;
                                }
                                $itemlist = array();

                                if(isset($formdata['eddorderid'])) $eddorderid = $formdata['eddorderid'];
                                elseif(isset(jssupportticket::$_data[0]->eddorderid)) $eddorderid = jssupportticket::$_data[0]->eddorderid;
                                elseif(isset(jssupportticket::$_data['edd_order_id'])) $eddorderid = jssupportticket::$_data['edd_order_id'];
                                else $eddorderid = '';
                                    $blogusers = get_users( array( 'fields' => array( 'ID' ) ) );
                                    $user_purchase_array = array();
                                    foreach ($blogusers AS $b_user) {
                                        $user_purchases = edd_get_users_purchases($b_user->ID);
                                        if($user_purchases){
                                            foreach ($user_purchases AS $user_purchase) {
                                                $user_purchase_array[] = (object) array('id' => $user_purchase->ID, 'text' => '#'.$user_purchase->ID.'&nbsp;('. esc_html(__('Dated','js-support-ticket')).':&nbsp;' .date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($user_purchase->post_date)).')');
                                            }
                                        }
                                    }
                                     ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<?php if($field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                        <div class="js-form-value" id="eddorderid-wrap">
                                            <?php echo wp_kses(JSSTformfield::select('eddorderid', $user_purchase_array, $eddorderid, esc_html(__('Select Order ID', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                <?php
                                break;
                            case 'eddproductid':
                                if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!class_exists('Easy_Digital_Downloads')){
                                    break;
                                }

                                $order_products_array = array();
                                if($eddorderid != '' && is_numeric($eddorderid)){
                                    $order_products = edd_get_payment_meta_cart_details($eddorderid);
                                    foreach ($order_products as $order_product) {
                                        $order_products_array[] = (object) array('id'=>$order_product['id'], 'text'=>$order_product['name']);
                                    }
                                }

                                if(isset($formdata['eddproductid'])) $eddproductid = $formdata['eddproductid'];
                                elseif(isset(jssupportticket::$_data[0]->eddproductid)) $eddproductid = jssupportticket::$_data[0]->eddproductid;
                                else $eddproductid = '';  ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<?php if($field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                    <div class="js-form-value" id="eddproductid-wrap">
                                        <?php echo wp_kses(JSSTformfield::select('eddproductid', $order_products_array, $eddproductid, esc_html(__('Select Product', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'eddlicensekey':
                                if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!class_exists('Easy_Digital_Downloads')){
                                    break;
                                }
                                if(!class_exists('EDD_Software_Licensing')){
                                    break;
                                }

                                $license_key_array = array();
                                if($eddorderid != '' && is_numeric($eddorderid)){
                                    $license = EDD_Software_Licensing::instance();
                                    $result = $license->get_licenses_of_purchase($eddorderid);
                                    if($result){
                                        foreach ($result AS $license_record) {
                                            $license_record_licensekey = $license->get_license_key($license_record->ID);
                                            if($license_record_licensekey != ''){
                                                $license_key_array[] = (object) array('id' => $license_record_licensekey,'text' => $license_record_licensekey);
                                            }
                                        }
                                    }
                                }

                                $itemlist = array();
                                if(isset($formdata['eddlicensekey'])) $eddlicensekey = $formdata['eddlicensekey'];
                                elseif(isset(jssupportticket::$_data[0]->eddlicensekey)) $eddlicensekey = jssupportticket::$_data[0]->eddlicensekey;
                                else $eddlicensekey = '';
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?>&nbsp;<?php if($field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                    <div class="js-form-value" id="eddlicensekey-wrap">
                                        <?php echo wp_kses(JSSTformfield::select('eddlicensekey', $license_key_array, $eddlicensekey, esc_html(__('Select license key', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <?php

                                break;
                            case 'attachments':
                                ?>
                                <div class="js-form-wrapper fullwidth">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <?php
                                    if(isset(jssupportticket::$_data[5]) && count(jssupportticket::$_data[5]) > 0){
                                        $attachmentreq = '';
                                    }else{
                                        $attachmentreq = $field->required == 1 ? 'required' : '';
                                    }
                                    ?>
                                    <div class="js-form-value">
                                        <div class="tk_attachment_value_wrapperform">
                                            <span class="tk_attachment_value_text">
                                                <input type="file" class="inputbox" name="filename[]" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" maxlenght='30' data-validation="<?php echo esc_attr($attachmentreq); ?>" />
                                                <span class='tk_attachment_remove'></span>
                                            </span>
                                        </div>
                                        <div class="tk_attachments_desc">
                                            <span class="tk_attachments_configform">
                                                <small><?php echo esc_html(__('Maximum File Size', 'js-support-ticket')); echo ' (' . esc_html(jssupportticket::$_config['file_maximum_size']); ?>KB)<br>
                                                <?php echo esc_html(__('File Extension Type', 'js-support-ticket')); echo ' (' . esc_html(jssupportticket::$_config['file_extension']) . ')'; ?></small>
                                            </span>
                                            <span id="tk_attachment_add" class="tk_attachments_addform jsst-button-link jsst-button-bg-link"><?php echo esc_html(__('Add More File', 'js-support-ticket')); ?></span>
                                        </div>
                                        <?php
                                        if (!empty(jssupportticket::$_data[5])) {
                                            foreach (jssupportticket::$_data[5] AS $attachment) {
                                                $attachmentid = isset(jssupportticket::$_data[0]->id) ? jssupportticket::$_data[0]->id : '';
                                                echo wp_kses('
                                                    <div class="js_ticketattachment">
                                                            ' . esc_html($attachment->filename) . /*' ( ' . $attachment->filesize . ' ) ' .*/ '
                                                            <a title="'. esc_html(__('Delete','js-support-ticket')).'" href="?page=attachment&task=deleteattachment&action=jstask&id=' . $attachment->id . '&ticketid=' . $attachmentid . '"><img alt="'. esc_html(__('Delete','js-support-ticket')).'" src="'.JSST_PLUGIN_URL.'includes/images/delete.png" /></a>
                                                    </div>', JSST_ALLOWED_TAGS);
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            case 'envatopurchasecode':
                                if(!in_array('envatovalidation', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!empty(jssupportticket::$_data[0]->envatodata)){
                                    $envlicense = json_decode(jssupportticket::$_data[0]->envatodata, true);
                                }else{
                                    $envlicense = array();
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field->fieldtitle)); ?><?php if($field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-field">
                                        <?php
                                        if(isset($formdata['envatopurchasecode'])) $envatopurchasecode = $formdata['envatopurchasecode'];
                                        elseif(isset($envlicense['license'])) $envatopurchasecode = $envlicense['license'];
                                        else $envatopurchasecode = '';
                                        echo wp_kses(JSSTformfield::text('envatopurchasecode', $envatopurchasecode, array('class' => 'inputbox inputbox js-form-input-field', 'data-validation' => ($field->required ? 'required' : ''))), JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::hidden('prev_envatopurchasecode', $envatopurchasecode), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            default:
                                JSSTincluder::getObjectClass('customfields')->formCustomFields($field);
                                break;
                        }
                        //do_action('jsst_ticket_form_admin_field_loop', $field);
                    endforeach;
                    echo wp_kses('<input type="hidden" id="userfeilds_total" name="userfeilds_total"  value="' . esc_attr($i) . '"  />', JSST_ALLOWED_TAGS);
                ?>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$_data[0]->id) ? jssupportticket::$_data[0]->id : ''), JSST_ALLOWED_TAGS) ?>
                <?php echo wp_kses(JSSTformfield::hidden('multiformid', isset(jssupportticket::$_data['formid']) ? jssupportticket::$_data['formid'] : '1'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('attachmentdir', isset(jssupportticket::$_data[0]->attachmentdir) ? jssupportticket::$_data[0]->attachmentdir : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ticketid', isset(jssupportticket::$_data[0]->ticketid) ? jssupportticket::$_data[0]->ticketid : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$_data[0]->created) ? jssupportticket::$_data[0]->created : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('lastreply', isset(jssupportticket::$_data[0]->lastreply) ? jssupportticket::$_data[0]->lastreply : ''), JSST_ALLOWED_TAGS); ?>
                <?php
                    if (isset(jssupportticket::$_data[0]->uid))
                        $uid = jssupportticket::$_data[0]->uid;
                    else
                        $uid = get_current_user_id();
                    echo wp_kses(JSSTformfield::hidden('uid', $uid), JSST_ALLOWED_TAGS);
                ?>
                <?php echo wp_kses(JSSTformfield::hidden('updated', isset(jssupportticket::$_data[0]->updated) ? jssupportticket::$_data[0]->updated : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_saveticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Submit Ticket', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>
