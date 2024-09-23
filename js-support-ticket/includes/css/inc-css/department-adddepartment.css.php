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

	form.js-ticket-form{display:inline-block; width: 100%;padding: 10px;}
	div.js-ticket-add-form-wrapper{float: left;width: 100%;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{float: left;width: calc(100% / 2 - 10px);margin: 0px 5px; margin-bottom: 20px; }
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{float: left;width: calc(100% / 1 - 10px); margin-bottom: 30px; }
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{float: left;width: 100%;margin-bottom: 5px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{float: left;width: 100%;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field.js-ticket-from-field-wrp-full-width select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .jsst-formfield-radio-button-wrap {display: inline-block;margin-right: 10px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .jsst-formfield-radio-button-wrap label {margin-left: 3px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width div.js-ticket-from-field select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat;}

	div.js-ticket-radio-btn-wrp{float: left;width: 100%;padding: 10px;height: 50px;}
	div.js-ticket-radio-btn-wrp input.js-ticket-form-field-radio-btn{margin-right: 5px; vertical-align: top;}
	div.js-ticket-radio-btn-wrp label#forsendmail{margin: 0px;display: inline-block; margin-right: 30px;}


	div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 20px 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-decoration: none;}

	div.js-ticket-append-signature-wrp{float: left;width: calc(100% / 2 - 25px); margin-right:25px;margin-bottom: 20px;}
	div.js-ticket-append-signature-wrp.js-ticket-append-signature-wrp-full-width{width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-append-field-title{float: left;width: 100%;margin-bottom: 15px;}
	div.js-ticket-append-signature-wrp div.js-ticket-append-field-wrp{float: left;width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box{float: left;width: calc(100% / 3 - 10px);margin: 0px 5px;padding: 11px;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box.js-ticket-signature-radio-box-full-width{width: 100%;margin: 0;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box label#forcanappendsignature{margin: 0 0 0 3px;display: inline-block;}

	span.help-block{font-size:14px;}
	span.help-block{color:red;}

	select ::-ms-expand {display:none !important;}
	select{-webkit-appearance:none !important;}
	.js-support-ticket-outgoing-email-message{font-size:14px;}

';
/*Code For Colors*/
$jssupportticket_css .= '
/* Add Form */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: '.$color2.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid{background-color:'.$color3.';border:1px solid '.$color5.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status{background-color:'.$color3.' !important;border:1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border: 1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color: '.$color2.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background: '.$color2.';color:'.$color7.';border: 1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{border-color: '.$color1.';}
	div.js-ticket-radio-btn-wrp{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	span.tk_attachments_addform{background-color:'.$color2.';color:'.$color7.';}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box{border:1px solid '.$color5.';background-color:#fff;color: '.$color4.';}
	.js-support-ticket-outgoing-email-message {color: '.$color4.';}
	div.js-ticket-append-signature-wrp div.js-ticket-append-field-title {color: '.$color2.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width div.js-ticket-from-field select#status {background-color: #fff !important;}

/* Add Form */

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
