<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
if (JSSTincluder::getObjectClass('user')->isguest() && jssupportticket::$_config['show_captcha_on_visitor_from_ticket'] == 1 && jssupportticket::$_config['captcha_selection'] == 1) {
    wp_enqueue_script( 'ticket-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), jssupportticket::$_config['productversion'], true );
}
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (JSSTincluder::getObjectClass('user')->uid() != 0 || jssupportticket::$_config['visitor_can_create_ticket'] == 1) {
        JSSTmessage::getMessage();
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('file_validate.js', JSST_PLUGIN_URL . 'includes/js/file_validate.js', array(), jssupportticket::$_config['productversion'], true);

		wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
        $jsst_jssupportticket_js ="
            var ajaxurl ='".esc_url(admin_url('admin-ajax.php'))."';
            function onSubmit(token) {
                document.getElementById('adminTicketform').submit();
            }
            jQuery(document).ready(function ($) {
                $('.custom_date').datepicker({
                    dateFormat: 'yy-mm-dd'
                });
                jQuery('#tk_attachment_add').click(function () {
                    var obj = this;
                    var current_files = jQuery('input[name=\'filename[]\']').length;
                    var total_allow =". esc_attr(jssupportticket::$_config['no_of_attachement']) .";
                    var append_text = '<span class=\'tk_attachment_value_text\'><input name=\'filename[]\' type=\'file\' onchange=\"uploadfile(this,\'". esc_js(jssupportticket::$_config['file_maximum_size']) ."\',\'".esc_js(jssupportticket::$_config['file_extension']) ."\');\" size=\'20\' maxlenght=\'30\'  /><span  class=\'tk_attachment_remove\'></span></span>';
                    if (current_files < total_allow) {
                        jQuery('.tk_attachment_value_wrapperform').append(append_text);
                    } else if ((current_files === total_allow) || (current_files > total_allow)) {
                        alert('". esc_html(__('File upload limit exceeds', 'js-support-ticket'))."');
                        jQuery(obj).hide();
                    }
                });
                jQuery(document).delegate('.tk_attachment_remove', 'click', function (e) {
                    jQuery(this).parent().remove();
                    var current_files = jQuery('input[name=\'filename[]\']').length;
                    var total_allow =". esc_attr(jssupportticket::$_config['no_of_attachement']) .";
                    if (current_files < total_allow) {
                        jQuery('#tk_attachment_add').show();
                    }
                });
                $.validate();
            });
            // to get premade and append to isssue summery
            function getHelpTopicByDepartment(val) {
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: val, jstmod: 'department', task: 'getHelpTopicByDepartment', '_wpnonce':'".esc_attr(wp_create_nonce('get-help-topic-by-department')) ."'}, function (data) {
                    if (data != false) {
                        jQuery('div#helptopic').html(data);
                    }else{
                        jQuery('div#helptopic').html( '". esc_html(__('No help topic found','js-support-ticket')) ."');
                    }
                });//jquery closed
            }

            // woocommerce
            function jsst_wc_order_products(){
                var orderid = jQuery('#wcorderid').val();
				emptycombo = '<select name=\'wcproductid\' id=\'wcproductid\'  class=\'inputbox js-form-select-field js-ticket-select-field\' ><option value=\'\'>Select Product</option></select>';
				jQuery('#wcproductid-wrap').html(emptycombo);
                jQuery.post(
                    ajaxurl,
                    {action: 'jsticket_ajax', jstmod: 'woocommerce', task: 'getWcOrderProductsAjax',orderid:orderid, '_wpnonce':'". esc_attr(wp_create_nonce("get-wcorder-products-ajax"))."'},
                    function (data) {
						data1 = JSON.parse(data);
                        jQuery('#wcproductid-wrap').html(jsstDecodeHTML(data1['html']));
                    }
                );
            }

            function jsst_edd_order_products(){
                var orderid = jQuery('select#eddorderid').val();
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'easydigitaldownloads', task: 'getEDDOrderProductsAjax', eddorderid:orderid, '_wpnonce':'". esc_attr(wp_create_nonce('get-eddorder-products-ajax')) ."'}, function (data) {
                        jQuery('#eddproductid-wrap').html(data);
                    }
                );
            }

            function jsst_eed_product_licenses(){
                var eddproductid = jQuery('select#eddproductid').val();
                var orderid = jQuery('select#eddorderid').val();
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'easydigitaldownloads', task: 'getEDDProductlicensesAjax', eddproductid:eddproductid, eddorderid:orderid, '_wpnonce':'". esc_attr(wp_create_nonce('get-edd-productlicenses-ajax')) ."'}, function (data) {
                        jQuery('#eddlicensekey-wrap').html(data);
                    }
                );
            }

            jQuery(document).ready(function(){
                jQuery('select#eddorderid').change(function(){
                    jsst_edd_order_products();
                });
                ";
                if(!isset(jssupportticket::$jsst_data[0]->id)){
                    $jsst_jssupportticket_js .="
                    if(jQuery('select#eddorderid').val()){
                        jsst_edd_order_products();
                    }";
                }
                $jsst_jssupportticket_js .="
                jQuery(document).on('change', 'select#eddproductid', function() {
                    jsst_eed_product_licenses();
                });
                if(jQuery('select#eddproductid').val()){
                    jsst_eed_product_licenses();
                }

                jQuery('#wcorderid').change(function(){
                    jsst_wc_order_products();
                });
                if(jQuery('#wcorderid').val()){
                    jsst_wc_order_products();
                }
            });
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        ?>
        <span style="display:none" id="filesize"><?php echo esc_html(__('Error file size too large', 'js-support-ticket')); ?></span>
        <span style="display:none" id="fileext"><?php echo esc_html(__('The uploaded file extension not valid', 'js-support-ticket')); ?></span>
        <?php
        $jsst_loginuser_name = '';
        $jsst_loginuser_email = '';
        if (!JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser();
            /*$jsst_loginuser_name = $jsst_current_user->user_firstname . ' ' . esc_attr($jsst_current_user->user_lastname);
            if(str_replace(' ', '', $jsst_loginuser_name) == ''){
                $jsst_loginuser_name = $jsst_current_user->user_nicename;
            }*/
            if(empty($jsst_current_user->display_name) == true){
                $jsst_loginuser_name = $jsst_current_user->user_nicename;
            }else{
                $jsst_loginuser_name = $jsst_current_user->display_name;
            }
            $jsst_loginuser_email = $jsst_current_user->user_email;
        }
        ?>
        <?php JSSTmessage::getMessage(); ?>
        <?php $jsst_formdata = JSSTformfield::getFormData(); ?>
        <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
        <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
        <?php if (jssupportticket::$_config['new_ticket_message']) { ?>
            <div class="js-col-xs-12 js-col-md-12 js-ticket-form-instruction-message">
                <?php echo wp_kses(jssupportticket::$_config['new_ticket_message'], JSST_ALLOWED_TAGS); ?>
            </div>
            <?php }
        ?>
        <div class="js-ticket-add-form-wrapper">
            <?php
            $jsst_showform = true;
            if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce')){
                if(isset(jssupportticket::$jsst_data['paidsupport'])){
                    $jsst_row = jssupportticket::$jsst_data['paidsupport'];
                    $jsst_paidsupportid = $jsst_row->itemid;
                    ?>
                    <h3><?php echo esc_html(__("Paid support info",'js-support-ticket')); ?></h3>
                    <table border="1">
                        <tr>
                            <th><?php echo esc_html(__("Order ID",'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__("Product Name",'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__("Total Tickets",'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__("Remaining Tickets",'js-support-ticket')); ?></th>
                        </tr>
                        <tr>
                            <td>#<?php echo esc_html($jsst_row->orderid); ?></td>
                            <td><?php
                            echo esc_html($jsst_row->itemname);
                            if($jsst_row->qty > 1){
                                echo '<b> x '.esc_html($jsst_row->qty)."</b>";
                            }
                            ?></td>
                            <td><?php if ($jsst_row->total == -1)  echo esc_html(__("Unlimited",'js-support-ticket')); else echo esc_html($jsst_row->total); ?></td>
                            <td><?php if ($jsst_row->total == -1) echo esc_html(__("Unlimited",'js-support-ticket')); else echo esc_html($jsst_row->remaining); ?></td>
                        </tr>
                    </table>
                    <?php
                }elseif(isset(jssupportticket::$jsst_data['paidsupportitems'])){
                    $jsst_showform = false;
                    $jsst_paidsupportitems = jssupportticket::$jsst_data['paidsupportitems'];
                    if(empty($jsst_paidsupportitems)){
                        ?>
                        <div class="js-ticket-error-message-wrapper">
                            <div class="js-ticket-message-image-wrapper">
                                <img class="js-ticket-message-image" alt="message image" src="<?php echo esc_url(JSST_PLUGIN_URL).'/includes/images/error/not-permission-icon.png'; ?>">
                            </div>
                            <div class="js-ticket-messages-data-wrapper">
                                <span class="js-ticket-messages-main-text">
                                    <?php echo esc_html(__("You have not purchased any supported item",'js-support-ticket')); ?>
                                </span>
                                <span class="js-ticket-user-login-btn-wrp">
                                    <a class="js-ticket-login-btn" href="<?php echo esc_url(get_permalink( wc_get_page_id( 'shop' ) )); ?>"><?php echo esc_html(__("Go to shop",'js-support-ticket')); ?></a>
                                </span>
                            </div>
                        </div>
                        <?php
                    }else{
                        ?>
                        <h3><?php echo esc_html(__("Please select paid support item",'js-support-ticket')); ?> <span style="color:red">*</span></h3>
                        <table border="1">
                            <tr>
                                <th><?php echo esc_html(__("Order ID",'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__("Product Name",'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__("Total Tickets",'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__("Remaining Tickets",'js-support-ticket')); ?></th>
                                <th></th>
                            </tr>
                            <?php
                            foreach($jsst_paidsupportitems as $jsst_row){
                                ?>
                                <tr>
                                    <td>#<?php echo esc_html($jsst_row->orderid); ?></td>
                                    <td><?php
                                    echo esc_html($jsst_row->itemname);
                                    if($jsst_row->qty > 1){
                                        echo '<b> x '.esc_html($jsst_row->qty)."</b>";
                                    }
                                    ?></td>
                                    <td><?php if($jsst_row->total == -1) echo esc_html(__("Unlimited",'js-support-ticket')); else echo esc_html($jsst_row->total); ?></td>
                                    <td><?php if($jsst_row->total == -1) echo esc_html(__("Unlimited",'js-support-ticket')); else echo esc_html($jsst_row->remaining); ?></td>
                                    <td><a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'addticket','paidsupportid'=>$jsst_row->itemid))); ?>"><?php echo esc_html(__("Select",'js-support-ticket')); ?></a></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <?php
                    }
                }
            }
            ?>

            <?php if($jsst_showform): ?>
            <?php $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]->id) ?jssupportticket::$jsst_data[0]->id :''; ?>
            <form class="js-ticket-form js-support-ticket-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'saveticket')),"save-ticket-".$jsst_nonce_id)); ?>" id="adminTicketform" enctype="multipart/form-data">
                <?php
                $jsst_i = '';
                $jsst_fieldcounter = 0;
                $jsst_eddorderid = '';
                apply_filters('js_support_ticket_frontend_ticket_form_start',1);
                foreach (jssupportticket::$jsst_data['fieldordering'] AS $jsst_field):
                    $jsst_readonlyclass = $jsst_field->readonly ? " js-form-ticket-readonly " : "";
                    $jsst_visibleclass = "";
                    if (!empty($jsst_field->visibleparams) && $jsst_field->visibleparams != '[]'){
                        $jsst_visibleclass = ' visible ';
                    }
                    $jsst_jsVisibleFunction = '';
                    if ($jsst_field->visible_field != null) {
                        $jsst_visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($jsst_field->visible_field);
                        if (!empty($jsst_visibleparams)) {
                            $jsst_wpnonce = wp_create_nonce("is-field-required-".$jsst_field->visible_field);
                            $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                            $jsst_jsVisibleFunction = " getDataForVisibleField('".$jsst_wpnonce."', this.value, '".esc_js($jsst_field->visible_field)."', ".$jsst_jsObject.");";
                        }
                    }
                    switch ($jsst_field->field) {
                        case 'email':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field">
                                    <?php
                                        if(isset($jsst_formdata['email'])) $jsst_email = $jsst_formdata['email'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->email)) $jsst_email = jssupportticket::$jsst_data[0]->email;
                                        elseif(!empty($jsst_field->defaultvalue)) $jsst_email = $jsst_field->defaultvalue;
                                        else $jsst_email = $jsst_loginuser_email;

										$jsst_email = jssupportticketphplib::JSST_strip_tags($jsst_email); // in some case, p tag is attached to email
                                        echo wp_kses(JSSTformfield::text('email', $jsst_email, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => ($jsst_field->required) ? 'required email' : 'email', 'data-validation-optional' => ($jsst_field->required) ? 'false' : 'true', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'fullname':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field">
                                    <?php
                                        if(isset($jsst_formdata['name'])) $jsst_name = $jsst_formdata['name'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->name)) $jsst_name = jssupportticket::$jsst_data[0]->name;
                                        elseif(!empty($jsst_field->defaultvalue)) $jsst_name = $jsst_field->defaultvalue;
                                        else $jsst_name = $jsst_loginuser_name;
                                        echo wp_kses(JSSTformfield::text('name', $jsst_name, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => ($jsst_field->required) ? 'required' : '', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'phone':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field">
                                    <?php
                                        if(isset($jsst_formdata['phone'])) $jsst_phone = $jsst_formdata['phone'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->phone)) $jsst_phone = jssupportticket::$jsst_data[0]->phone;
                                        else $jsst_phone = $jsst_field->defaultvalue;
                                        echo wp_kses(JSSTformfield::text('phone', $jsst_phone, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => ($jsst_field->required) ? 'required' : '', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'phoneext':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field">
                                    <?php
                                        if(isset($jsst_formdata['phoneext'])) $jsst_phoneext = $jsst_formdata['phoneext'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->phoneext)) $jsst_phoneext = jssupportticket::$jsst_data[0]->phoneext;
                                        else $jsst_phoneext = $jsst_field->defaultvalue;
                                        echo wp_kses(JSSTformfield::text('phoneext', $jsst_phoneext, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => ($jsst_field->required) ? 'required' : '', 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'department':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php 
										$jsst_disabled ="";
                                        if(isset($jsst_formdata['departmentid'])) $jsst_departmentid = $jsst_formdata['departmentid'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->departmentid)) $jsst_departmentid = jssupportticket::$jsst_data[0]->departmentid;
                                        elseif(JSSTrequest::getVar('departmentid','get',0) > 0) $jsst_departmentid = JSSTrequest::getVar('departmentid','get');
                                        else $jsst_departmentid = JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
										if(isset(jssupportticket::$jsst_data['formid'])){
											if(in_array('multiform',jssupportticket::$_active_addons)){
												$jsst_departmentid = JSSTincluder::getJSModel('multiform')->getDepartmentIdByFormId(jssupportticket::$jsst_data['formid']);
												if($jsst_departmentid > 0){
													//$jsst_disabled = "disabled";
												}
											}
											
										}
                                        // code for visible field
                                        if ($jsst_field->visible_field != null) {
                                            $jsst_visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($jsst_field->visible_field);
                                            // For default function (initial value setting)
                                            if (!empty($jsst_visibleparams)) {
                                                $jsst_wpnonce = wp_create_nonce("is-field-required-" . $jsst_field->visible_field);
                                                $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                                                // Build JS function without esc_js on JSON
                                                $jsst_defaultFunc = "getDataForVisibleField('" . esc_js($jsst_wpnonce) . "', '" . esc_js($jsst_departmentid) . "', '" . esc_js($jsst_field->visible_field) . "', " . $jsst_jsObject . ");";
                                                // Attach default function on document ready
                                                if (!isset(jssupportticket::$jsst_data[0]->id)) {
                                                    $jsst_jssupportticket_js = "
                                                        jQuery(document).ready(function(){
                                                            ".$jsst_defaultFunc."
                                                        });
                                                    ";
                                                    wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                                                }
                                            }
                                        }
										if($jsst_disabled == ""){
											echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-ticket-select-field' . esc_attr($jsst_readonlyclass), 'onchange' => $jsst_jsVisibleFunction.' getHelpTopicByDepartment(this.value);', 'data-validation' => ($jsst_field->required) ? 'required' : '') + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
										}else{
											echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), $jsst_departmentid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-ticket-select-field', 'onchange' => $jsst_jsVisibleFunction.' getHelpTopicByDepartment(this.value);', 'data-validation' => ($jsst_field->required) ? 'required' : '','disabled'=>'disabled')), JSST_ALLOWED_TAGS);
										}
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
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
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field js-ticket-form-field-select" id="helptopic">
                                    <?php
                                        if(isset($jsst_formdata['helptopicid'])) $jsst_helptopicid = $jsst_formdata['helptopicid'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->helptopicid)) $jsst_helptopicid = jssupportticket::$jsst_data[0]->helptopicid;
                                        elseif(JSSTrequest::getVar('helptopicid','get',0) > 0) $jsst_helptopicid = JSSTrequest::getVar('helptopicid','get');
                                        else $jsst_helptopicid = '';
                                        if (isset($jsst_departmentid)) {
                                            $jsst_dep_id = $jsst_departmentid;
                                        } else{
                                            $jsst_dep_id = 0;
                                        }
                                        echo wp_kses(JSSTformfield::select('helptopicid', JSSTincluder::getJSModel('helptopic')->getHelpTopicsForCombobox($jsst_dep_id), $jsst_helptopicid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class ' => 'js-ticket-select-field' .esc_attr($jsst_readonlyclass), 'data-validation' => ($jsst_field->required) ? 'required' : '', 'onchange' => $jsst_jsVisibleFunction) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'product':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field js-ticket-form-field-select" id="product">
                                    <?php
                                        if(isset($jsst_formdata['productid'])) $jsst_productid = $jsst_formdata['productid'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->productid)) $jsst_productid = jssupportticket::$jsst_data[0]->productid;
                                        else $jsst_productid = '';
                                        echo wp_kses(JSSTformfield::select('productid', JSSTincluder::getJSModel('product')->getProductForCombobox(), $jsst_productid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class ' => 'js-ticket-select-field' .esc_attr($jsst_readonlyclass), 'data-validation' => ($jsst_field->required) ? 'required' : '', 'onchange' => $jsst_jsVisibleFunction) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'priority':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field js-ticket-form-field-select">
                                    <?php
                                        if(isset($jsst_formdata['priorityid'])) $jsst_priorityid = $jsst_formdata['priorityid'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->priorityid)) $jsst_priorityid = jssupportticket::$jsst_data[0]->priorityid;
                                        else $jsst_priorityid = JSSTincluder::getJSModel('priority')->getDefaultPriorityID();
                                        if (!empty($jsst_visibleparams)) {
                                            $jsst_wpnonce = wp_create_nonce("is-field-required-" . $jsst_field->visible_field);
                                            // Build JS function without esc_js on JSON
                                            $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                                            $jsst_defaultFunc = "getDataForVisibleField('" . esc_js($jsst_wpnonce) . "', '" . esc_js($jsst_priorityid) . "', '" . esc_js($jsst_field->visible_field) . "', " . $jsst_jsObject . ");";
                                            // Attach default function on document ready
                                            if (!isset(jssupportticket::$jsst_data[0]->id)) {
                                                $jsst_jssupportticket_js = "
                                                    jQuery(document).ready(function(){
                                                        ".$jsst_defaultFunc."
                                                    });
                                                ";
                                                wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                                            }
                                        }
                                        echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), $jsst_priorityid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-ticket-select-field' . esc_attr($jsst_readonlyclass), 'data-validation' => ($jsst_field->required) ? 'required' : '', 'onchange' => $jsst_jsVisibleFunction) + ($jsst_field->readonly ? ['tabindex' => '-1'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'subject':
                            if($jsst_fieldcounter % 2 == 0){
                                if($jsst_fieldcounter != 0){
                                    echo '</div>';
                                }
                                echo '<div class="js-ticket-add-form-wrapper">';
                            }
                            $jsst_fieldcounter++;
                            ?>
                            <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<span style="color:red">*</span></div>
                                <div class="js-ticket-from-field">
                                    <?php
                                        if(isset($jsst_formdata['subject'])) $jsst_subject = $jsst_formdata['subject'];
                                        elseif(isset(jssupportticket::$jsst_data[0]->subject)) $jsst_subject = jssupportticket::$jsst_data[0]->subject;
                                        else $jsst_subject = $jsst_field->defaultvalue;
                                        echo wp_kses(JSSTformfield::text('subject', $jsst_subject, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => 'required', 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : [])), JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'issuesummary':
                            if($jsst_fieldcounter != 0){
                                echo '</div>';
                                $jsst_fieldcounter = 0;
                            }
                            ?>
                            <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width <?php echo esc_attr($jsst_visibleclass); ?>">
                                <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <div class="js-ticket-from-field">
                                    <?php
                                        if(isset($jsst_formdata['message'])) $jsst_message = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($jsst_formdata['message']);
                                        elseif(isset(jssupportticket::$jsst_data[0]->message)) $jsst_message = jssupportticket::$jsst_data[0]->message;
                                        else $jsst_message = $jsst_field->defaultvalue;
                                        // $jsst_message = '';
                                        if ($jsst_field->readonly) {
                                            echo wp_kses(JSSTformfield::textarea('jsticket_message', $jsst_message, array('class' => 'inputbox js-form-textarea-field one', 'rows' => 5, 'cols' => 25, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder), 'readonly'=> 'readonly')), JSST_ALLOWED_TAGS);
                                        } else {
                                            wp_editor($jsst_message, 'jsticket_message', array('media_buttons' => false));
                                        }
                                        /*
                                        * Use following settings for minimal editor as all are offering
                                        */
                                        // $jsst_settings = array(
                                        //     'media_buttons' => false,
                                        //     'textarea_rows' => 10,
                                        //     'quicktags'     => false,
                                        //     'tinymce'       => array(
                                        //         'toolbar1' => 'bold,italic,underline,strikethrough,hr,|,bullist,numlist,|,link,unlink',
                                        //         'toolbar2' => ''
                                        //     ),
                                        // );
                                        // wp_editor($jsst_message, 'jsticket_message', $jsst_settings);
                                    ?>
                                </div>
                                <?php if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            break;
                        case 'attachments':
                            if($jsst_fieldcounter != 0){
                                echo '</div>';
                                $jsst_fieldcounter = 0;
                            }
                            ?>
                            <div class="js-ticket-reply-attachments"><!-- Attachments -->
                                <div class="js-attachment-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?><?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                <?php
                                if(isset(jssupportticket::$jsst_data[5]) && count(jssupportticket::$jsst_data[5]) > 0){
                                    $jsst_attachmentreq = '';
                                }else{
                                    $jsst_attachmentreq = $jsst_field->required == 1 ? 'required' : '';
                                }
                                ?>
                                <div class="js-attachment-field">
                                    <div class="tk_attachment_value_wrapperform tk_attachment_user_reply_wrapper">
                                        <span class="tk_attachment_value_text">
                                            <input type="file" class="inputbox js-attachment-inputbox" name="filename[]" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" data-validation="<?php echo esc_attr($jsst_attachmentreq); ?>" />
                                            <span class='tk_attachment_remove'></span>
                                        </span>
                                    </div>
                                    <span class="tk_attachments_configform">
                                        <?php echo esc_html(__('Maximum File Size', 'js-support-ticket'));
                                            echo ' (' . esc_html(jssupportticket::$_config['file_maximum_size']); ?>KB)<br><?php echo esc_html(__('File Extension Type', 'js-support-ticket'));
                                            echo ' (' . esc_html(jssupportticket::$_config['file_extension']) . ')'; ?>
                                    </span>
                                    <span id="tk_attachment_add" data-ident="tk_attachment_user_reply_wrapper" class="tk_attachments_addform"><?php echo esc_html(__('Add more','js-support-ticket')); ?></span>
                                </div>
                                <?php 
                                if (!empty(jssupportticket::$jsst_data[5])) {
                                    foreach (jssupportticket::$jsst_data[5] AS $jsst_attachment) {
                                        echo wp_kses('
                                        <div class="js-ticket-attached-files-wrp">
                                            <div class="js_ticketattachment">
                                                ' . esc_html($jsst_attachment->filename) . ' ( ' . esc_html($jsst_attachment->filesize) . ' ) ' . '
                                            </div>
                                            <a class="js-ticket-delete-attachment" href="'.wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'attachment', 'task'=>'deleteattachment', 'action'=>'jstask', 'id'=>$jsst_attachment->id, 'tikcetid'=>jssupportticket::$jsst_data[0]->id, 'jsstpageid'=>jssupportticket::getPageid())),'delete-attachement-'.$jsst_attachment->id) . '">' . esc_html(__('Remove','js-support-ticket')) . '</a>
                                        </div>', JSST_ALLOWED_TAGS);
                                    }
                                }
                                if(!empty($jsst_field->description)): ?>
                                    <div class="js-ticket-from-field-description">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                    </div>
                                    <?php 
                                endif; ?>
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
                                if($jsst_fieldcounter % 2 == 0){
                                    if($jsst_fieldcounter != 0){
                                        echo '</div>';
                                    }
                                    echo '<div class="js-ticket-add-form-wrapper">';
                                }
                                $jsst_fieldcounter++;

                                $jsst_orderlist = array();
								if(get_current_user_id() > 0){
									foreach(wc_get_orders(array('customer_id'=>JSSTincluder::getObjectClass('user')->wpuid(),'post_status' => 'wc-completed')) as $jsst_order){ // wp uid because of woocommerce store wp uid
										$jsst_orderlist[] = (object) array('id' => $jsst_order->get_id(),'text'=>'#'.$jsst_order->get_id().' - '.$jsst_order->get_date_created()->date_i18n(wc_date_format()));
									}
								}
                                if(isset($jsst_formdata['wcorderid'])) $jsst_wcorderid = $jsst_formdata['wcorderid'];
                                elseif(isset(jssupportticket::$jsst_data[0]->wcorderid)) $jsst_wcorderid = jssupportticket::$jsst_data[0]->wcorderid;
                                else $jsst_wcorderid = '';  ?>
                                <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                    <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                    <div class="js-ticket-from-field js-ticket-form-field-select">
                                        <?php echo wp_kses(JSSTformfield::select('wcorderid', $jsst_orderlist, $jsst_wcorderid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-ticket-select-field', 'data-validation' => ($jsst_field->required) ? 'required' : '')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-ticket-from-field-description">
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
                                if($jsst_fieldcounter % 2 == 0){
                                    if($jsst_fieldcounter != 0){
                                        echo '</div>';
                                    }
                                    echo '<div class="js-ticket-add-form-wrapper">';
                                }
                                $jsst_fieldcounter++;

                                $jsst_itemlist = array();
                                if(isset($jsst_formdata['wcproductid'])) $jsst_wcproductid = $jsst_formdata['wcproductid'];
                                elseif(isset(jssupportticket::$jsst_data[0]->wcproductid)) $jsst_wcproductid = jssupportticket::$jsst_data[0]->wcproductid;
                                else $jsst_wcproductid = '';  ?>
                                <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                    <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                    <div class="js-ticket-from-field js-ticket-form-field-select" id="wcproductid-wrap">
                                        <?php echo wp_kses(JSSTformfield::select('wcproductid', $jsst_itemlist, $jsst_wcproductid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-ticket-select-field', 'data-validation' => ($jsst_field->required) ? 'required' : '')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-ticket-from-field-description">
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
                                if($jsst_fieldcounter % 2 == 0){
                                    if($jsst_fieldcounter != 0){
                                        echo '</div>';
                                    }
                                    echo '<div class="js-ticket-add-form-wrapper">';
                                }
                                $jsst_fieldcounter++;

                                $jsst_itemlist = array();

                                if(isset($jsst_formdata['eddorderid'])) $jsst_eddorderid = $jsst_formdata['eddorderid'];
                                elseif(isset(jssupportticket::$jsst_data[0]->eddorderid)) $jsst_eddorderid = jssupportticket::$jsst_data[0]->eddorderid;
                                elseif(isset(jssupportticket::$jsst_data['edd_order_id'])) $jsst_eddorderid = jssupportticket::$jsst_data['edd_order_id'];
                                $jsst_user_id = JSSTincluder::getObjectClass('user')->uid();
                                if(is_numeric($jsst_user_id) && $jsst_user_id > 0){
                                    $jsst_user_purchases = edd_get_users_purchases($jsst_user_id);
                                    $jsst_user_purchase_array = array();
                                    if (is_array($jsst_user_purchases) || $jsst_user_purchases instanceof Countable) {
                                        foreach ($jsst_user_purchases AS $jsst_user_purchase) {
                                            $jsst_user_purchase_array[] = (object) array('id' => $jsst_user_purchase->ID, 'text' => '#'.$jsst_user_purchase->ID.'&nbsp;('. esc_html(__('Dated','js-support-ticket')).':&nbsp;' .date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_user_purchase->post_date)).')');
                                        }
                                    }
                                     ?>
                                    <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select" id="eddorderid-wrap">
                                            <?php echo wp_kses(JSSTformfield::select('eddorderid', $jsst_user_purchase_array, $jsst_eddorderid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-ticket-select-field', 'data-validation' => ($jsst_field->required) ? 'required' : '')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <?php if(!empty($jsst_field->description)): ?>
                                            <div class="js-ticket-from-field-description">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php
                                }else{ ?>
                                    <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select" id="eddorderid-wrap">
                                            <?php  echo wp_kses(JSSTformfield::text('eddorderid', $jsst_eddorderid, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => ($jsst_field->required) ? 'required' : '', 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder))), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <?php if(!empty($jsst_field->description)): ?>
                                            <div class="js-ticket-from-field-description">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php
                                }
                                break;
                            case 'eddproductid':
                                if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                    break;
                                }
                                if(!class_exists('Easy_Digital_Downloads')){
                                    break;
                                }
                                if(is_numeric($jsst_user_id) && $jsst_user_id > 0){
                                    if($jsst_fieldcounter % 2 == 0){
                                        if($jsst_fieldcounter != 0){
                                            echo '</div>';
                                        }
                                        echo '<div class="js-ticket-add-form-wrapper">';
                                    }
                                    $jsst_fieldcounter++;

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
                                    <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                        <div class="js-ticket-from-field" id="eddproductid-wrap">
                                            <?php echo wp_kses(JSSTformfield::select('eddproductid', $jsst_order_products_array, $jsst_eddproductid, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-form-select-field', 'data-validation' => ($jsst_field->required) ? 'required' : '')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <?php if(!empty($jsst_field->description)): ?>
                                            <div class="js-ticket-from-field-description">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php
                            }
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
                                if($jsst_fieldcounter % 2 == 0){
                                    if($jsst_fieldcounter != 0){
                                        echo '</div>';
                                    }
                                    echo '<div class="js-ticket-add-form-wrapper">';
                                }
                                $jsst_fieldcounter++;
                                $jsst_license_key_array = array();
                                if($jsst_eddorderid != '' && is_numeric($jsst_eddorderid)){
                                    $jsst_license = EDD_Software_Licensing::instance();
                                    $jsst_result = $jsst_license->get_licenses_of_purchase($jsst_eddorderid);
                                    foreach ($jsst_result AS $jsst_license_record) {
                                        $jsst_license_record_licensekey = $jsst_license->get_license_key($jsst_license_record->ID);
                                        if($jsst_license_record_licensekey != ''){
                                            $jsst_license_key_array[] = (object) array('id' => $jsst_license_record_licensekey,'text' => $jsst_license_record_licensekey);
                                        }
                                    }
                                }

                                $jsst_itemlist = array();
                                if(isset($jsst_formdata['eddlicensekey'])) $jsst_eddlicensekey = $jsst_formdata['eddlicensekey'];
                                elseif(isset(jssupportticket::$jsst_data[0]->eddlicensekey)) $jsst_eddlicensekey = jssupportticket::$jsst_data[0]->eddlicensekey;
                                else $jsst_eddlicensekey = '';
                                $jsst_user_id = JSSTincluder::getObjectClass('user')->uid();
                                if(is_numeric($jsst_user_id) && $jsst_user_id > 0){
                                ?>
                                    <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select" id="eddlicensekey-wrap">
                                            <?php echo wp_kses(JSSTformfield::select('eddlicensekey', $jsst_license_key_array, $jsst_eddlicensekey, esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_field->fieldtitle), array('class' => 'inputbox js-ticket-select-field', 'data-validation' => ($jsst_field->required) ? 'required' : '')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <?php if(!empty($jsst_field->description)): ?>
                                            <div class="js-ticket-from-field-description">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                }else{
                                    ?>
                                    <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select" id="eddlicensekey-wrap">
                                            <?php  echo wp_kses(JSSTformfield::text('eddlicensekey', $jsst_eddlicensekey, array('class' => 'inputbox js-ticket-form-field-input', 'data-validation' => ($jsst_field->required) ? 'required' : '', 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder))), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <?php if(!empty($jsst_field->description)): ?>
                                            <div class="js-ticket-from-field-description">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php
                                }
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
                                if($jsst_fieldcounter % 2 == 0){
                                    if($jsst_fieldcounter != 0){
                                        echo '</div>';
                                    }
                                    echo '<div class="js-ticket-add-form-wrapper">';
                                }
                                $jsst_fieldcounter++;

                                if(isset($jsst_formdata['envatopurchasecode'])) $jsst_envatopurchasecode = $jsst_formdata['envatopurchasecode'];
                                elseif(isset($jsst_envlicense['license'])) $jsst_envatopurchasecode = $jsst_envlicense['license'];
                                else $jsst_envatopurchasecode = '';  ?>
                                <div class="js-ticket-from-field-wrp <?php echo esc_attr($jsst_visibleclass); ?>">
                                    <div class="js-ticket-from-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->fieldtitle)); ?>&nbsp;<?php if($jsst_field->required == 1) echo '&nbsp;<span style="color:red">*</span>'; ?></div>
                                    <div class="js-ticket-from-field js-ticket-form-field-select" id="envatopurchasecode-wrap">
                                        <?php echo wp_kses(JSSTformfield::text('envatopurchasecode', $jsst_envatopurchasecode, array('class' => 'inputbox js-ticket-form-field-input','data-validation'=>($jsst_field->required ? 'required' : ''), 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder))), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php if(!empty($jsst_field->description)): ?>
                                        <div class="js-ticket-from-field-description">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                        default:
                            if ($jsst_field->userfieldtype != 'termsandconditions') {
                                JSSTincluder::getObjectClass('customfields')->formCustomFields($jsst_field);
                            }
                            break;
                    }

                    //do_action_ref_array('jsst_ticket_form_field_loop', array($jsst_field, &$jsst_fieldcounter));

                endforeach;
                if($jsst_fieldcounter != 0){
                    echo '</div>'; // close extra div open in user field
                }
                echo '<input type="hidden" id="userfeilds_total" name="userfeilds_total"  value="' . esc_attr($jsst_i) . '"  />';
                ?>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]->id) ? jssupportticket::$jsst_data[0]->id : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('multiformid', isset(jssupportticket::$jsst_data['formid']) ? jssupportticket::$jsst_data['formid'] : '1'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('attachmentdir', isset(jssupportticket::$jsst_data[0]->attachmentdir) ? jssupportticket::$jsst_data[0]->attachmentdir : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ticketid', isset(jssupportticket::$jsst_data[0]->ticketid) ? jssupportticket::$jsst_data[0]->ticketid : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$jsst_data[0]->created) ? jssupportticket::$jsst_data[0]->created : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('updated', isset(jssupportticket::$jsst_data[0]->updated) ? jssupportticket::$jsst_data[0]->updated : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                <?php
                if(isset($jsst_paidsupportid)){
                    echo wp_kses(JSSTformfield::hidden('paidsupportid', $jsst_paidsupportid), JSST_ALLOWED_TAGS);
                }
                ?>
                <?php
                foreach (jssupportticket::$jsst_data['fieldordering'] AS $jsst_field):
                    $jsst_visibleclass = "";
                    if (!empty($jsst_field->visibleparams) && $jsst_field->visibleparams != '[]'){
                        $jsst_visibleclass = ' visible ';
                    }
                    $jsst_jsVisibleFunction = '';
                    if ($jsst_field->visible_field != null) {
                        $jsst_visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($jsst_field->visible_field);
                        if (!empty($jsst_visibleparams)) {
                            $jsst_wpnonce = wp_create_nonce("is-field-required-".$jsst_field->visible_field);
                            $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                            $jsst_jsVisibleFunction = " getDataForVisibleField('".$jsst_wpnonce."', this.value, '".esc_js($jsst_field->visible_field)."', ".$jsst_jsObject.");";
                        }
                    }
                    switch ($jsst_field->field) {
                        case 'termsandconditions1':
                        case 'termsandconditions2':
                        case 'termsandconditions3':
                            if (isset(jssupportticket::$jsst_data[0]->id)) {
                                break;
                            }
                            if (!empty($jsst_field->userfieldparams)) {
                                $jsst_obj_option = json_decode($jsst_field->userfieldparams,true);

                                $jsst_url = '#';
                                if( isset($jsst_obj_option['termsandconditions_linktype']) && $jsst_obj_option['termsandconditions_linktype'] == 1){
                                    $jsst_url = $jsst_obj_option['termsandconditions_link'];
                                }if( isset($jsst_obj_option['termsandconditions_linktype']) && $jsst_obj_option['termsandconditions_linktype'] == 2){
                                    $jsst_url  = get_permalink($jsst_obj_option['termsandconditions_page']);
                                }

                                $jsst_link_start = '<a href="' . esc_url($jsst_url) . '" class="termsandconditions_link_anchor" target="_blank" >';
                                $jsst_link_end = '</a>';

                                if(strstr($jsst_obj_option['termsandconditions_text'], '[link]') && jssupportticketphplib::JSST_strstr($jsst_obj_option['termsandconditions_text'], '[/link]')){
                                    $jsst_label_string = jssupportticketphplib::JSST_str_replace('[link]', $jsst_link_start, $jsst_obj_option['termsandconditions_text']);
                                    $jsst_label_string = jssupportticketphplib::JSST_str_replace('[/link]', $jsst_link_end, $jsst_label_string);
                                }elseif($jsst_obj_option['termsandconditions_linktype'] == 3){
                                    $jsst_label_string = $jsst_obj_option['termsandconditions_text'];
                                }else{
                                    $jsst_label_string = $jsst_link_start.$jsst_obj_option['termsandconditions_text'].$jsst_link_end;
                                }
                                $jsst_c_field_required = '';
                                if($jsst_field->required == 1){
                                    $jsst_c_field_required = 'required';
                                }
                                // ticket terms and conditonions are required.
                                if($jsst_field->fieldfor == 1){
                                    if (!isset($jsst_field->visibleparams)) {
                                        $jsst_c_field_required = 'required';
                                    } else {
                                        $jsst_c_field_required = '';
                                    }
                                } ?>
                                <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width js-ticket-system-terms-and-condition-box ">
                                    <div class="js-ticket-from-field js-ticket-form-field-select" id="envatopurchasecode-wrap">
                                        <input type="checkbox" class="radiobutton js-ticket-append-radio-btn" value="1" id="<?php echo esc_attr($jsst_field->field); ?>" name="<?php echo esc_attr($jsst_field->field) ?>" data-validation="<?php echo esc_attr($jsst_c_field_required) ?>">
                                        <label for="<?php echo esc_attr($jsst_field->field) ?>" id="foruf_checkbox1"><?php echo wp_kses($jsst_label_string, JSST_ALLOWED_TAGS) ?></label>
                                    </div>
                                </div>   
                                <?php
                            }
                            break;
                        default:
                            if ($jsst_field->userfieldtype == 'termsandconditions') {
                                JSSTincluder::getObjectClass('customfields')->formCustomFields($jsst_field);
                            }
                            break;
                    }
                endforeach;
                // captcha
                $jsst_google_recaptcha_3 = false;
                if (JSSTincluder::getObjectClass('user')->isguest()) {
                    if (jssupportticket::$_config['show_captcha_on_visitor_from_ticket'] == 1) {  ?>
                        <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                            <div class="js-ticket-from-field-title">
                                <?php echo esc_html(__('Captcha', 'js-support-ticket')); ?>
                            </div>
                            <div class="js-ticket-from-field">
                                <?php
                                if (jssupportticket::$_config['captcha_selection'] == 1) { // Google recaptcha
                                    $jsst_error = null;
                                    if (jssupportticket::$_config['recaptcha_version'] == 1) {
                                        echo '<div class="g-recaptcha" data-sitekey="'.esc_attr(jssupportticket::$_config['recaptcha_publickey']).'"></div>';
                                    } else {
                                        $jsst_google_recaptcha_3 = true;
                                    }
                                } else { // own captcha
                                    // echo esc_attr(jssupportticket::$_captcha['captcha']);
                                    $jsst_captcha = new JSSTcaptcha;
                                    echo wp_kses($jsst_captcha->getCaptchaForForm(), JSST_ALLOWED_TAGS);
                                }
                                ?>
                            </div>
                        </div>
                    <?php
                    }
                }
                ?>
                <div class="js-ticket-form-btn-wrp">
                    <?php
                    if($jsst_google_recaptcha_3 == true && JSSTincluder::getObjectClass('user')->isguest()){ // to handle case of google recpatcha version 3
                        echo wp_kses(JSSTformfield::button('save', esc_html(__('Submit Ticket', 'js-support-ticket')), array('class' => 'js-ticket-save-button g-recaptcha', 'data-callback' => 'onSubmit', 'data-action' => 'submit', 'data-sitekey' => esc_attr(jssupportticket::$_config['recaptcha_publickey']))), JSST_ALLOWED_TAGS);
                    } else {
                        echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Submit Ticket', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS);
                    } ?>
                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel')));?>" class="js-ticket-cancel-button"><?php echo esc_html(__('Cancel','js-support-ticket'));?></a>
                </div>
            </form>
            <?php endif; ?>
        </div>
        <?php
    } else {// User is guest
        $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket'));
        $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
        JSSTlayout::getUserGuest($jsst_redirect_url);
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>

</div>
