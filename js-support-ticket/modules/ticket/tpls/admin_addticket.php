<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('file_validate.js', JSST_PLUGIN_URL . 'includes/js/file_validate.js', array(), jssupportticket::$_config['productversion'], true);
    wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
?>
<?php JSSTmessage::getMessage(); ?>
<?php $jsst_formdata = JSSTformfield::getFormData(); ?>
<?php
$jsst_js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
$jsst_jssupportticket_js ='
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
                var name = jQuery(this).attr("data-username");
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
$jsst_jssupportticket_js .='
    jQuery(document).ready(function ($) {
        $(".custom_date").datepicker({dateFormat: "'. esc_html($jsst_js_scriptdateformat) .'"});
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
        if(!isset(jssupportticket::$jsst_data[0]->id)){
            $jsst_jssupportticket_js .='
            if(jQuery("select#eddorderid").val()){
                jsst_edd_order_products();
            }';
        }
        $jsst_jssupportticket_js .='
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
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Create Ticket','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Create Ticket','js-support-ticket')); ?></h1>
            <a target="blank" href="https://www.youtube.com/watch?v=zmQ4bpqSYnk" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_attr(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt = "<?php echo esc_attr(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
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
                    <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                </div>
                <div class="userpopup-search">
                    <form id="userpopupsearch">
                        <div class="userpopup-fields-wrp">
                            <div class="userpopup-fields">
                                <input type="text" name="username" id="username" placeholder="<?php echo esc_attr(__('Username','js-support-ticket')); ?>" />
                            </div>
                            <div class="userpopup-fields">
                                <input type="text" name="name" id="name" placeholder="<?php echo esc_attr(__('Name','js-support-ticket')); ?>" />
                            </div>
                            <div class="userpopup-fields">
                                <input type="text" name="emailaddress" id="emailaddress" placeholder="<?php echo esc_attr(__('Email Address','js-support-ticket')); ?>"/>
                            </div>
                            <div class="userpopup-btn-wrp">
                                <input class="userpopup-search-btn" type="submit" value="<?php echo esc_attr(__('Search','js-support-ticket')); ?>" />
                                <input class="userpopup-reset-btn" type="submit" onclick="document.getElementById('name').value = '';document.getElementById('username').value = ''; document.getElementById('emailaddress').value = '';" value="<?php echo esc_attr(__('Reset','js-support-ticket')); ?>" />
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
            <?php $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]->id) ?jssupportticket::$jsst_data[0]->id :''; ?>
            <form class="jsstadmin-form js-support-ticket-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=saveticket"),"save-ticket-".$jsst_nonce_id)); ?>" id="adminTicketform" enctype="multipart/form-data">
                <?php
                    $jsst_i = '';
                    foreach (jssupportticket::$jsst_data['fieldordering'] AS $jsst_field):
                        $jsst_readonlyclass = $jsst_field->readonly ? " js-form-ticket-readonly " : "";
                        $jsst_jsVisibleFunction = '';
                        if ($jsst_field->visible_field != null) {
                            $jsst_visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($jsst_field->visible_field);
                            if (!empty($jsst_visibleparams)) {
                                $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                                $jsst_wpnonce = wp_create_nonce("is-field-required-".$jsst_field->visible_field);
                                $jsst_jsVisibleFunction = " getDataForVisibleField('".$jsst_wpnonce."', this.value, '".esc_js($jsst_field->visible_field)."', ".$jsst_jsObject.");";
                            }
                        }
                        switch ($jsst_field->field) {
                            case 'users':
                                if ($jsst_field->readonly == 1) {
                                    $jsst_style = 'display:none;';
                                } else {
                                    $jsst_style = '';
                                } ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php if (isset(jssupportticket::$jsst_data[0]->uid)) { ?>
                                            <input class="js-form-diabled-field" type="text" id="username-text" value="<?php echo isset( $jsst_formdata['username-text'] ) ? esc_attr( $jsst_formdata['username-text'] ) : esc_attr( jssupportticket::$jsst_data[0]->name ); ?>" readonly="readonly" placeholder="<?php echo esc_attr( jssupportticket::JSST_getVarValue( $jsst_field->placeholder ) ); ?>" <?php echo ( $jsst_field->required == 1 ) ? 'data-validation="required"' : ''; ?> /><div id="username-div"></div>
                                            <?php } else {
                                            ?>
                                            <input class="js-form-diabled-field" type="text" value="<?php echo isset( $jsst_formdata['username-text'] ) ? esc_attr( $jsst_formdata['username-text'] ) : ''; ?>" id="username-text" readonly="readonly" placeholder="<?php echo esc_attr( jssupportticket::JSST_getVarValue( $jsst_field->placeholder ) ); ?>" <?php echo ( $jsst_field->required == 1 ) ? 'data-validation="required"' : ''; ?> /><a style="<?php echo esc_attr( $jsst_style ); ?>" href="javascript:void(0);" id="userpopup" title="<?php echo esc_attr(__( 'Select User', 'js-support-ticket' ) ); ?>"><?php echo esc_html(__( 'Select User', 'js-support-ticket' ) ); ?></a><div id="username-div"></div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'email':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['email'])) $jsst_email =  $jsst_formdata['email'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->email)) $jsst_email = jssupportticket::$jsst_data[0]->email;
                                            else $jsst_email = $jsst_field->defaultvalue; // Admin email not appear in form
                                            echo wp_kses(JSSTformfield::text('email', $jsst_email, array('class' => 'inputbox js-form-input-field', 'data-validation' => ($jsst_field->required) ? 'required email' : 'email', 'data-validation-optional' => ($jsst_field->required) ? 'false' : 'true', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'fullname':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['name'])) $jsst_name = $jsst_formdata['name'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->name)) $jsst_name = jssupportticket::$jsst_data[0]->name;
                                            else $jsst_name = $jsst_field->defaultvalue; // Admin full name not appear in form
                                            echo wp_kses(JSSTformfield::text('name', $jsst_name, array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'phone':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['phone'])) $jsst_phone = $jsst_formdata['phone'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->phone)) $jsst_phone = jssupportticket::$jsst_data[0]->phone;
                                            else $jsst_phone = $jsst_field->defaultvalue;
                                            echo wp_kses(JSSTformfield::text('phone', $jsst_phone, array('class' => 'inputbox js-form-input-field','data-validation'=>($jsst_field->required) ? 'required':'', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'phoneext':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['phoneext'])) $jsst_phoneext = $jsst_formdata['phoneext'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->phoneext)) $jsst_phoneext = jssupportticket::$jsst_data[0]->phoneext;
                                            else $jsst_phoneext = $jsst_field->defaultvalue;
                                            echo wp_kses(JSSTformfield::text('phoneext', $jsst_phoneext, array('class' => 'inputbox js-form-input-field', 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'department':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['departmentid'])) $jsst_departmentid = $jsst_formdata['departmentid'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->departmentid)) $jsst_departmentid = jssupportticket::$jsst_data[0]->departmentid;
                                            elseif(JSSTrequest::getVar('departmentid',0) > 0) $jsst_departmentid = JSSTrequest::getVar('departmentid');
                                            else $jsst_departmentid = JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
                                            // code for visible field
                                            if ($jsst_field->visible_field != null && !isset(jssupportticket::$jsst_data[0]->id)) {
                                                // For default function (initial value setting)
                                                if (!empty($jsst_visibleparams) && !isset(jssupportticket::$jsst_data[0]->id)) {
                                                    $jsst_wpnonce = wp_create_nonce("is-field-required-" . $jsst_field->visible_field);
                                                    $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                                                    // Build JS function without esc_js on JSON
                                                    $jsst_defaultFunc = "getDataForVisibleField('" . esc_js($jsst_wpnonce) . "', '" . esc_js($jsst_departmentid) . "', '" . esc_js($jsst_field->visible_field) . "', " . $jsst_jsObject . ");";
                                                    // Attach default function on document ready
                                                    $jsst_jssupportticket_js = "
                                                        jQuery(document).ready(function(){
                                                            ".$jsst_defaultFunc."
                                                        });
                                                    ";
                                                    wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                                                }
                                            }
                                            if(in_array('cannedresponses', jssupportticket::$_active_addons)){
                                                echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass), 'onchange' => $jsst_jsVisibleFunction.' getHelpTopicByDepartment(this.value);getPremadeByDepartment(this.value);', 'data-validation' => ($jsst_field->required) ? 'required':'') + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                            }else{
                                                echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass), 'onchange' => $jsst_jsVisibleFunction.' getHelpTopicByDepartment(this.value);', 'data-validation' => ($jsst_field->required) ? 'required':'') + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                            }
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'helptopic':
                                if(!in_array('helptopic', jssupportticket::$_active_addons)){
                                    break;
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value" id="helptopic">
                                        <?php
                                            if(isset($jsst_formdata['helptopicid'])) $jsst_helptopicid = $jsst_formdata['helptopicid'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->helptopicid)) $jsst_helptopicid = jssupportticket::$jsst_data[0]->helptopicid;
                                            elseif(JSSTrequest::getVar('helptopicid',0) > 0) $jsst_helptopicid = JSSTrequest::getVar('helptopicid');
                                            else $jsst_helptopicid = '';
                                            if (isset($jsst_departmentid)) {
                                                $jsst_dep_id = $jsst_departmentid;
                                            } else{
                                                $jsst_dep_id = 0;
                                            }
                                            echo wp_kses(JSSTformfield::select('helptopicid', JSSTincluder::getJSModel('helptopic')->getHelpTopicsForCombobox($jsst_dep_id), $jsst_helptopicid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass),'data-validation'=>($jsst_field->required) ? 'required': '', 'onchange' => $jsst_jsVisibleFunction) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'product':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value" id="product">
                                        <?php
                                            if(isset($jsst_formdata['productid'])) $jsst_productid = $jsst_formdata['productid'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->productid)) $jsst_productid = jssupportticket::$jsst_data[0]->productid;
                                            else $jsst_productid = '';
                                            echo wp_kses(JSSTformfield::select('productid', JSSTincluder::getJSModel('product')->getProductForCombobox(), $jsst_productid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass),'data-validation'=>($jsst_field->required) ? 'required': '', 'onchange' => $jsst_jsVisibleFunction) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'priority':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['priorityid'])) $jsst_priorityid = $jsst_formdata['priorityid'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->priorityid)) $jsst_priorityid = jssupportticket::$jsst_data[0]->priorityid;
                                            else $jsst_priorityid = JSSTincluder::getJSModel('priority')->getDefaultPriorityID();

                                            if (!empty($jsst_visibleparams) && !isset(jssupportticket::$jsst_data[0]->id)) {
                                                $jsst_wpnonce = wp_create_nonce("is-field-required-" . $jsst_field->visible_field);
                                                $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                                                // Build JS function without esc_js on JSON
                                                $jsst_defaultFunc = "getDataForVisibleField('" . esc_js($jsst_wpnonce) . "', '" . esc_js($jsst_priorityid) . "', '" . esc_js($jsst_field->visible_field) . "', " . $jsst_jsObject . ");";
                                                // Attach default function on document ready
                                                $jsst_jssupportticket_js = "
                                                    jQuery(document).ready(function(){
                                                        ".$jsst_defaultFunc."
                                                    });
                                                ";
                                                wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                                            }
                                            echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), $jsst_priorityid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass), 'data-validation' => 'required', 'onchange' => $jsst_jsVisibleFunction) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
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
                                                    <img title="<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" alt = "<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) ?>/includes/images/watch-video-icon.png" />
                                                </a>
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                            <div class="js-form-value">
                                                <?php
                                                    if(isset($jsst_formdata['internalnotetitle'])) $jsst_internalnotetitle = $jsst_formdata['internalnotetitle'];
                                                    else $jsst_internalnotetitle = $jsst_field->defaultvalue;
                                                    echo wp_kses(JSSTformfield::text('internalnotetitle', $jsst_internalnotetitle, array('class' => 'inputbox js-form-input-field','data-validation'=>($jsst_field->required == 1) ? 'required': '', 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                                ?>
                                            </div>
                                            <?php if(!empty($jsst_field->description)): ?>
                                                <div class="js-form-description">
                                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="js-form-wrapper fullwidth">
                                            <div class="js-form-title"><?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?></div>
                                            <div class="js-form-value">
                                                <?php if (isset(jssupportticket::$jsst_data[0]->id)) { ?>
                                                    <div class="js-form-title"><?php echo esc_html(__('Reason for edit', 'js-support-ticket')); ?><br></div>
                                                <?php } ?>
                                                <?php
                                                    if(isset($jsst_formdata['internalnote'])) $jsst_internalnote = $jsst_formdata['internalnote'];
                                                    elseif(isset(jssupportticket::$jsst_data[0]->internalnote)) $jsst_internalnote = jssupportticket::$jsst_data[0]->internalnote;
                                                    else $jsst_internalnote = '';
                                                    wp_editor($jsst_internalnote, 'internalnote', array('media_buttons' => false));
                                                ?>
                                            </div>
                                            <?php if(!empty($jsst_field->description)): ?>
                                                <div class="js-form-description">
                                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                    break;
                            case 'duedate':
                                // remove this from admin form
                                break;
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['duedate'])) $jsst_duedate = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_formdata['duedate']));
                                            elseif(isset(jssupportticket::$jsst_data[0]->duedate) && jssupportticket::$jsst_data[0]->duedate != '0000-00-00 00:00:00'){
                                                $jsst_duedate = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->duedate));
                                            }elseif(!empty($jsst_field->defaultvalue)){
                                                $jsst_duedate = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_field->defaultvalue));
                                            }else $jsst_duedate = '';
                                            echo wp_kses(JSSTformfield::text('duedate', $jsst_duedate, array('class' => 'custom_date js-form-date-field','data-validation'=>($jsst_field->required) ? 'required': '', 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'status':
                                // remove this from admin form
                                break;
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['status'])) $jsst_status = $jsst_formdata['status'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->status)) $jsst_status = jssupportticket::$jsst_data[0]->status;
                                            else $jsst_status = '1';
                                            echo wp_kses(JSSTformfield::select('status', JSSTincluder::getJSModel('status')->getStatusForCombobox(), $jsst_status, esc_html(__('Select Status', 'js-support-ticket')), array('class' => 'radiobutton js-form-select-field' . esc_attr($jsst_readonlyclass),'data-validation'=>($jsst_field->required) ? 'required': '') + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'assignto':
                                // remove this from admin form
                                break;
                                if (! in_array('agent',jssupportticket::$_active_addons)) {
                                    break;
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['staffid'])) $jsst_staffid = $jsst_formdata['staffid'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->staffid)) $jsst_staffid = jssupportticket::$jsst_data[0]->staffid;
                                            else $jsst_staffid = '';
                                            echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), $jsst_staffid, esc_html(__('Select Agent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass),'data-validation'=>($jsst_field->required) ? 'required': '') + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'subject':
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<span style="color: red;" >*</span></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['subject'])) $jsst_subject = $jsst_formdata['subject'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->subject)) $jsst_subject = jssupportticket::$jsst_data[0]->subject;
                                            else $jsst_subject = $jsst_field->defaultvalue;
                                            echo wp_kses(JSSTformfield::text('subject', $jsst_subject, array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required','style'=>'width:100%;', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'premade':
                                if(!in_array('cannedresponses', jssupportticket::$_active_addons)){
                                    break;
                                }
                                // if($jsst_fieldcounter != 0){
                                //     echo '</div>';
                                //     $jsst_fieldcounter = 0;
                                // }
                                $jsst_text = JSSTincluder::getJSModel('cannedresponses')->getPreMadeMessageForCombobox();
                                ?>
                                <div class="js-form-wrapper fullwidth">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?></div>
                                    <div class="js-form-value">
                                        <div id="premade">
                                            <?php
                                                foreach($jsst_text as $jsst_premade){
                                                    ?>
                                                    <div class="js-form-perm-msg" onclick="getpremade(<?php echo esc_js($jsst_premade->id); ?>);">
                                                        <a href="javascript:void(0)" title="<?php echo esc_attr(__('premade','js-support-ticket')); ?>"><?php echo esc_html($jsst_premade->text); ?></a>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                        </div>
                                        <div class="js-form-append">
                                            <?php echo wp_kses(JSSTformfield::checkbox('append', array('1' => esc_html(__('Append', 'js-support-ticket'))), '', array('class' => 'radiobutton js-form-radio-field')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'issuesummary':
                                ?>
                                <div class="js-form-wrapper fullwidth">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                            if(isset($jsst_formdata['message'])) $jsst_message = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($jsst_formdata['message']);
                                            elseif(isset(jssupportticket::$jsst_data[0]->message)) $jsst_message = jssupportticket::$jsst_data[0]->message;
                                            else $jsst_message = $jsst_field->defaultvalue;
                                            if ($jsst_field->readonly) {
                                                echo wp_kses(JSSTformfield::textarea('jsticket_message', $jsst_message, array('class' => 'inputbox js-form-textarea-field one', 'rows' => 5, 'cols' => 25, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder), 'readonly'=> 'readonly')), JSST_ALLOWED_TAGS);
                                            } else {
                                                wp_editor($jsst_message, 'jsticket_message', array('media_buttons' => false));
                                            }
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
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
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value">
                                        <?php
                                        // $jsst_orderlist = array();
                                        // foreach(wc_get_orders(array()) as $jsst_order){
                                        //     $jsst_orderlist[] = (object) array('id' => $jsst_order->get_id(),'text'=>'#'.$jsst_order->get_id().' - '.$jsst_order->get_date_created()->date_i18n(wc_date_format()).' - '.$jsst_order->get_billing_first_name().' '.$jsst_order->get_billing_last_name());
                                        // }
                                        if(isset($jsst_formdata['wcorderid'])) $jsst_wcorderid = $jsst_formdata['wcorderid'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->wcorderid)) $jsst_wcorderid = jssupportticket::$jsst_data[0]->wcorderid;
                                        else $jsst_wcorderid = $jsst_field->defaultvalue;
                                        // echo wp_kses(JSSTformfield::select('wcorderid', $jsst_orderlist, $jsst_wcorderid, esc_html(__('Select Order', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::text('wcorderid', $jsst_wcorderid, array('class' => 'inputbox js-form-input-field', 'data-validation' => ($jsst_field->required == 1) ? 'required' : '','style'=>'width:100%;', 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS); ?>
                                        <span class="jsst_product_found" title="<?php echo esc_attr(__("Order id found","js-support-ticket")); ?>" style="display: none;"></span>
                                        <span class="jsst_product_not_found" title="<?php echo esc_attr(__("Order id not found","js-support-ticket")); ?>" style="display: none;"></span>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
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
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-value" id="wcproductid-wrap">
                                        <?php
                                            $jsst_itemlist = array();
                                            if(isset($jsst_formdata['wcproductid'])) $jsst_wcproductid = $jsst_formdata['wcproductid'];
                                            elseif(isset(jssupportticket::$jsst_data[0]->wcproductid)) $jsst_wcproductid = jssupportticket::$jsst_data[0]->wcproductid;
                                            else $jsst_wcproductid = '';
                                            echo wp_kses(JSSTformfield::select('wcproductid', $jsst_itemlist, $jsst_wcproductid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass)) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
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
                                $jsst_itemlist = array();

                                if(isset($jsst_formdata['eddorderid'])) $jsst_eddorderid = $jsst_formdata['eddorderid'];
                                elseif(isset(jssupportticket::$jsst_data[0]->eddorderid)) $jsst_eddorderid = jssupportticket::$jsst_data[0]->eddorderid;
                                elseif(isset(jssupportticket::$jsst_data['edd_order_id'])) $jsst_eddorderid = jssupportticket::$jsst_data['edd_order_id'];
                                else $jsst_eddorderid = '';
                                    $jsst_blogusers = get_users( array( 'fields' => array( 'ID' ) ) );
                                    $jsst_user_purchase_array = array();
                                    foreach ($jsst_blogusers AS $jsst_b_user) {
                                        $jsst_user_purchases = edd_get_users_purchases($jsst_b_user->ID);
                                        if($jsst_user_purchases){
                                            foreach ($jsst_user_purchases AS $jsst_user_purchase) {
                                                $jsst_user_purchase_array[] = (object) array('id' => $jsst_user_purchase->ID, 'text' => '#'.$jsst_user_purchase->ID.'&nbsp;('. esc_html(__('Dated','js-support-ticket')).':&nbsp;' .date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_user_purchase->post_date)).')');
                                            }
                                        }
                                    }
                                     ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                        <div class="js-form-value" id="eddorderid-wrap">
                                            <?php echo wp_kses(JSSTformfield::select('eddorderid', $jsst_user_purchase_array, $jsst_eddorderid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass)) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <?php if(!empty($jsst_field->description)): ?>
                                            <div class="js-form-description">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                            </div>
                                        <?php endif; ?>
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

                                $jsst_order_products_array = array();
                                if($jsst_eddorderid != '' && is_numeric($jsst_eddorderid)){
                                    $jsst_order_products = edd_get_payment_meta_cart_details($jsst_eddorderid);
                                    foreach ($jsst_order_products as $jsst_order_product) {
                                        $jsst_order_products_array[] = (object) array('id'=>$jsst_order_product['id'], 'text'=>$jsst_order_product['name']);
                                    }
                                }

                                if(isset($jsst_formdata['eddproductid'])) $jsst_eddproductid = $jsst_formdata['eddproductid'];
                                elseif(isset(jssupportticket::$jsst_data[0]->eddproductid)) $jsst_eddproductid = jssupportticket::$jsst_data[0]->eddproductid;
                                else $jsst_eddproductid = '';  ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                    <div class="js-form-value" id="eddproductid-wrap">
                                        <?php echo wp_kses(JSSTformfield::select('eddproductid', $jsst_order_products_array, $jsst_eddproductid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass)) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
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

                                $jsst_license_key_array = array();
                                if($jsst_eddorderid != '' && is_numeric($jsst_eddorderid)){
                                    $jsst_license = EDD_Software_Licensing::instance();
                                    $jsst_result = $jsst_license->get_licenses_of_purchase($jsst_eddorderid);
                                    if($jsst_result){
                                        foreach ($jsst_result AS $jsst_license_record) {
                                            $jsst_license_record_licensekey = $jsst_license->get_license_key($jsst_license_record->ID);
                                            if($jsst_license_record_licensekey != ''){
                                                $jsst_license_key_array[] = (object) array('id' => $jsst_license_record_licensekey,'text' => $jsst_license_record_licensekey);
                                            }
                                        }
                                    }
                                }

                                $jsst_itemlist = array();
                                if(isset($jsst_formdata['eddlicensekey'])) $jsst_eddlicensekey = $jsst_formdata['eddlicensekey'];
                                elseif(isset(jssupportticket::$jsst_data[0]->eddlicensekey)) $jsst_eddlicensekey = jssupportticket::$jsst_data[0]->eddlicensekey;
                                else $jsst_eddlicensekey = '';
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                    <div class="js-form-value" id="eddlicensekey-wrap">
                                        <?php echo wp_kses(JSSTformfield::select('eddlicensekey', $jsst_license_key_array, $jsst_eddlicensekey, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field' . esc_attr($jsst_readonlyclass)) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php

                                break;
                            case 'attachments':
                                ?>
                                <div class="js-form-wrapper fullwidth">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <?php
                                    if(isset(jssupportticket::$jsst_data[5]) && count(jssupportticket::$jsst_data[5]) > 0){
                                        $jsst_attachmentreq = '';
                                    }else{
                                        $jsst_attachmentreq = $jsst_field->required == 1 ? 'required' : '';
                                    }
                                    ?>
                                    <div class="js-form-value">
                                        <div class="tk_attachment_value_wrapperform">
                                            <span class="tk_attachment_value_text">
                                                <input type="file" class="inputbox" name="filename[]" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" maxlenght='30' data-validation="<?php echo esc_attr($jsst_attachmentreq); ?>" />
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
                                        if (!empty(jssupportticket::$jsst_data[5])) {
                                            foreach (jssupportticket::$jsst_data[5] AS $jsst_attachment) {
                                                $jsst_attachmentid = isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : '';
                                                echo wp_kses('
                                                    <div class="js_ticketattachment">
                                                            ' . esc_html($jsst_attachment->filename) . /*' ( ' . $jsst_attachment->filesize . ' ) ' .*/ '
                                                            <a title="'. esc_html(__('Delete','js-support-ticket')).'" href="' . esc_url(wp_nonce_url('?page=attachment&task=deleteattachment&action=jstask&id=' . $jsst_attachment->id . '&ticketid=' . $jsst_attachmentid, 'delete-attachement-'.$jsst_attachment->id)) . '"><img alt="'. esc_html(__('Delete','js-support-ticket')).'" src="'.esc_url(JSST_PLUGIN_URL).'includes/images/delete.png" /></a>
                                                    </div>', JSST_ALLOWED_TAGS);
                                            }
                                        }
                                        ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-form-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                            case 'envatopurchasecode':
                                if(!in_array('envatovalidation', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!empty(jssupportticket::$jsst_data[0]->envatodata)){
                                    $jsst_envlicense = json_decode(jssupportticket::$jsst_data[0]->envatodata, true);
                                }else{
                                    $jsst_envlicense = array();
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color: red;" >*</span>'; ?></div>
                                    <div class="js-form-field js-form-value">
                                        <?php
                                        if(isset($jsst_formdata['envatopurchasecode'])) $jsst_envatopurchasecode = $jsst_formdata['envatopurchasecode'];
                                        elseif(isset($jsst_envlicense['license'])) $jsst_envatopurchasecode = $jsst_envlicense['license'];
                                        else $jsst_envatopurchasecode = $jsst_field->defaultvalue;
                                        echo wp_kses(JSSTformfield::text('envatopurchasecode', $jsst_envatopurchasecode, array('class' => 'inputbox inputbox js-form-input-field', 'data-validation' => ($jsst_field->required ? 'required' : ''), 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::hidden('prev_envatopurchasecode', $jsst_envatopurchasecode), JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                </div>
                                <?php
                                break;
                            default:
                                JSSTincluder::getObjectClass('customfields')->formCustomFields($jsst_field);
                                break;
                        }
                        //do_action('jsst_ticket_form_admin_field_loop', $jsst_field);
                    endforeach;
                    echo wp_kses('<input type="hidden" id="userfeilds_total" name="userfeilds_total"  value="' . esc_attr($jsst_i) . '"  />', JSST_ALLOWED_TAGS);
                ?>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : ''), JSST_ALLOWED_TAGS) ?>
                <?php echo wp_kses(JSSTformfield::hidden('multiformid', isset(jssupportticket::$jsst_data['formid']) ? jssupportticket::$jsst_data['formid'] : '1'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('attachmentdir', isset(jssupportticket::$jsst_data[0]->attachmentdir) ? jssupportticket::$jsst_data[0]->attachmentdir : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ticketid', isset(jssupportticket::$jsst_data[0]->ticketid) ? jssupportticket::$jsst_data[0]->ticketid : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$jsst_data[0]->created) ? jssupportticket::$jsst_data[0]->created : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('lastreply', isset(jssupportticket::$jsst_data[0]->lastreply) ? jssupportticket::$jsst_data[0]->lastreply : ''), JSST_ALLOWED_TAGS); ?>
                <?php
                    if (isset(jssupportticket::$jsst_data[0]->uid))
                        $jsst_uid = jssupportticket::$jsst_data[0]->uid;
                    else
                        $jsst_uid = get_current_user_id();
                    echo wp_kses(JSSTformfield::hidden('uid', $jsst_uid), JSST_ALLOWED_TAGS);
                ?>
                <?php echo wp_kses(JSSTformfield::hidden('updated', isset(jssupportticket::$jsst_data[0]->updated) ? jssupportticket::$jsst_data[0]->updated : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_saveticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Submit Ticket', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>
