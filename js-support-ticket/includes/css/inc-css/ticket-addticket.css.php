<div></div><?php 
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
	form.js-ticket-form{display:inline-block; width: 100%;padding: 10px;}
	div.js-ticket-add-form-wrapper{float: left;width: 100%;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{float: left;width: calc(100% / 2 - 10px);margin: 0px 5px 20px; }
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{float: left;width: calc(100% / 1 - 10px); margin-bottom: 30px; }
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{float: left;width: 100%;margin-bottom: 5px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{float: left;width: 100%;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.inputbox{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #fff;}
	div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 20px 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-decoration: none;}
	select.js-ticket-select-field{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee; }
	div.js-ticket-reply-attachments{display: inline-block;width: 100%;margin-bottom: 20px;}
	div.js-ticket-reply-attachments div.js-attachment-field-title{display: inline-block;width: 100%;padding: 15px 0px;}
	div.js-ticket-reply-attachments div.js-attachment-field{display: inline-block;width: 100%;}
	div.tk_attachment_value_wrapperform{float: left;width:100%;padding:0px 0px;}
	div.tk_attachment_value_wrapperform span.tk_attachment_value_text{float: left;width: calc(100% / 3 - 10px);padding: 5px 5px;margin: 5px 5px;position: relative;}
	div.tk_attachment_value_wrapperform span.tk_attachment_value_text input.js-attachment-inputbox{width: 100%;max-width: 100%;max-height:100%;}
	span.tk_attachment_value_text span.tk_attachment_remove{background: url("'.JSST_PLUGIN_URL.'includes/images/close.png") no-repeat;background-size: 100% 100%;position: absolute;width: 30px;height: 30px;top: 5px;right:7px;cursor: pointer;}
	span.tk_attachments_configform{display: inline-block;float:left;line-height: 25px;margin-top: 10px;width: 100%; font-size: 14px;}
	span.tk_attachments_addform{position: relative;display: inline-block;padding: 13px 10px;cursor: pointer;margin-top: 10px;min-width: 120px;text-align: center;line-height: initial;} 
	span.help-block{font-size:13px;color:red !important;bottom: -30px;}
	select ::-ms-expand {display:none !important;}
	select{-webkit-appearance:none !important;}
	div.js-ticket-custom-radio-box {width: 20%;}
	div.js-ticket-radio-box {width: 20%;}
	div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-form-date-field {float: left;width: 100%;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea {float: left;width: 100%;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field {float: left;width: 100%;padding: 10px;line-height: initial;height: 50px;}
	span.js-attachment-file-box {padding: 9px 10px 8px;}

	
';
/*Code For Colors*/
$jssupportticket_css .= '

	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{border:1px solid '.$color5.';color: '.$color2.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.inputbox{border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: '.$color2.';}
	select.js-ticket-select-field{border:1px solid '.$color5.';color: '.$color2.';background-color: #fff !important;}
	div.js-ticket-reply-attachments div.js-attachment-field-title{color:'.$color2.';}
	span.tk_attachments_configform{color:'.$color4.';}
	div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border: 1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color: '.$color2.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background-color:'.$color2.';color:'.$color7.';border: 1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{border-color: '.$color1.';}
	a.js-ticket-delete-attachment{background-color:#ed3237;color:'.$color7.';}
	div.js-ticket-radio-btn-wrp{background-color:'.$color3.';border:1px solid '.$color5.';}
	span.tk_attachments_addform{background-color:'.$color1.';color:'.$color7.';border: 1px solid '.$color1.';}
	span.tk_attachments_addform:hover{border-color: '.$color2.';}
	span.js-ticket-apend-radio-btn{border:1px solid '.$color5.';background-color: '.$color3.';}
	div.tk_attachment_value_wrapperform{border: 1px solid '.$color5.';}
	span.tk_attachment_value_text{border: 1px solid '.$color5.';background-color:'.$color7.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select{border: 1px solid '.$color5.';color: '.$color4.';}
	span.help-block{color:red !important;}
';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
