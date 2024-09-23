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
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;line-height: initial;}
	
	span.js-ticket-roles-section-heading-right{display: inline-block;float: right;}
	span.js-ticket-roles-section-heading-right input#rad_alldepartmentaccess{vertical-align: sub;}
	span.js-ticket-roles-section-heading-right label{display: inline-block;margin:0px;vertical-align:middle;}
	
	div.js-ticket-roles-wrapper{float: left;width: 100%;margin-top:20px;}
	div.js-ticket-role-wrp{float: left;width: 100%; margin-top: 20px;margin-bottom: 20px;}
	div.js-ticket-role-wrp div.js-ticket-add-role-field-wrp{float: left;width: calc(100% / 3 - 10px);margin: 0px 5px 10px;padding: 10px;line-height: initial;}
	div.js-ticket-role-wrp div.js-ticket-add-role-field-wrp.js-ticket-margin-bottom{margin-bottom: 10px;}
	
	input.js-ticket-checkbox{vertical-align: sub;}
	label.js-ticket-label{display: inline-block;margin: 0px;vertical-align:middle;}
	
	div.js-ticket-roles-list-wrapper{float: left;width: 100%;margin-top: 20px;}
	div.js-ticket-add-role-field-wrp-top{margin: 0px !important;width: 100% !important;}
	div.js-ticket-categories-heading-wrp{float: left;width: 100%;padding: 15px;line-height: initial;}
	div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-transform: capitalize;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 20px 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-decoration: none;}
	span.help-block{font-size:14px;}
	span.help-block{color:red;}

	

';
/*Code For Colors*/
$jssupportticket_css .= '

/* Add Form */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color:'.$color2.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{background-color:'.$color3.' !important;border:1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border:1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color:'.$color2.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background: '.$color2.';color:'.$color7.';border:1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{border-color:'.$color1.';}

	div.js-ticket-categories-heading-wrp{background-color:'.$color2.';color: '.$color7.';border:1px solid '.$color5.';}
	div.js-ticket-add-role-field-wrp{background-color:#fff;border:1px solid '.$color5.';}
	label.js-ticket-label{color:'.$color4.';}


/* Add Form */
';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
