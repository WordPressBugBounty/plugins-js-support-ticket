<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
// if header is calling later
JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();

$color1 = jssupportticket::$_colors['color1'];
$color2 = jssupportticket::$_colors['color2'];
$color3 = jssupportticket::$_colors['color3'];
$color4 = jssupportticket::$_colors['color4'];
$color5 = jssupportticket::$_colors['color5'];
$color6 = jssupportticket::$_colors['color6'];
$color7 = jssupportticket::$_colors['color7'];
$color8 = jssupportticket::$_colors['color8'];
$color9 = jssupportticket::$_colors['color9'];

$jssupportticket_css = '';

/*Code for Css*/
$jssupportticket_css .= '

form.js-ticket-form{display:inline-block; width: 100%; padding: 10px;}
div.js-ticket-add-form-wrapper{float: left;width: 100%;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{float: left;width: calc(100% / 2 - 10px);margin: 0px 5px; margin-bottom: 20px; }
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{float: left;width: calc(100% / 1 - 10px); margin-bottom: 30px; }
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{float: left;width: 100%;margin-bottom: 5px;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{float: left;width: 100%;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;line-height: initial;height: 50px;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat;padding: 10px;line-height: initial;height: 50px;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;height: 50px;line-height: initial;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat ;padding: 10px;height: 50px;line-height: initial;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#visible{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat ;padding: 10px;height: 50px;line-height: initial;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea {width: 100%;padding: 10px;line-height: initial;min-height: 50px;}
div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 20px 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-decoration: none;}

div.js-ticket-reply-attachments{display: inline-block;width: 100%;margin-bottom: 20px;}
div.js-ticket-reply-attachments div.js-attachment-field-title{display: inline-block;width: 100%;padding: 15px 0px;}
div.js-ticket-reply-attachments div.js-attachment-field{display: inline-block;width: 100%;}
div.tk_attachment_value_wrapperform{float: left;width:100%;padding:0px 0px;}
div.tk_attachment_value_wrapperform span.tk_attachment_value_text{float: left;width: calc(100% / 3 - 10px);padding: 5px 5px;margin: 5px 5px;position: relative;}
div.tk_attachment_value_wrapperform span.tk_attachment_value_text input.js-attachment-inputbox{width: 100%;max-width: 100%;max-height:100%;}
span.tk_attachment_value_text span.tk_attachment_remove{background: url('.JSST_PLUGIN_URL.'includes/images/close.png) no-repeat;background-size: 100% 100%;position: absolute;width: 30px;height: 30px;top: 10px;right:6px;}
span.tk_attachments_configform{display: inline-block;float:left;line-height: 25px;margin-top: 10px;width: 100%; font-size: 14px;}
span.tk_attachments_addform{position: relative;display: inline-block;padding: 8px 10px;cursor: pointer;margin-top: 10px;min-width: 120px;text-align: center;}
div.js-ticket-attached-files-wrp{float: left;width: calc(100% / 2 - 10px);margin: 0px 5px;margin-top: 15px;}
div.js_ticketattachment{float: left;width: 70%;padding: 10px 5px;}
a.js-ticket-delete-attachment{display:inline-block;float: left;width: 30%;padding: 11px 5px;text-align: center;text-decoration: none;outline: 0px;}
span.help-block{font-size:14px;}
span.help-block{color:red;}

select ::-ms-expand {display:none !important;}
select{-webkit-appearance:none !important;}
';
/*Code For Colors*/
$jssupportticket_css .= '


/* Add Form */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: '.$color2.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#visible{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea {background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border: 1px solid '.$color5.';}
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color:'.$color2.';}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background: '.$color2.';color:'.$color7.';border: 1px solid '.$color5.';}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{border-color: '.$color1.';}
a.js-ticket-delete-attachment{background-color:#ed3237;color:'.$color7.';}
span.tk_attachments_addform{background-color:'.$color2.';color:'.$color7.';border: 1px solid '.$color5.';}
span.tk_attachments_addform:hover {border-color:'.$color1.';}
div.js_ticketattachment{background-color:#fff;border:1px solid '.$color5.';}
div.tk_attachment_value_wrapperform{border: 1px solid '.$color5.';background: #fff;color: '.$color4.';}
span.tk_attachments_configform {color: '.$color4.';}
span.tk_attachment_value_text{border: 1px solid '.$color5.';background-color:'.$color7.';}



/* Add Form */
';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
