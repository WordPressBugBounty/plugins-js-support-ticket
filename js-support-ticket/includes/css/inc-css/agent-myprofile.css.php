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
	/* My Profile */
	div.js-ticket-downloads-wrp{float: left;width: 100%;margin-top: 17px;}
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{float: left;width: 100%;padding: 15px;line-height: initial;}
	div.js-ticket-profile-wrp{float: left;width: 100%;margin-top: 30px;}
	div.js-ticket-profile-wrp div.js-ticket-profile-left{float: left;width: 200px;text-align: center;}
	div.js-ticket-profile-wrp div.js-ticket-profile-left div.js-ticket-user-img-wrp{float: left; width: 100%;height: 200px;position: relative;}
	div.js-ticket-profile-wrp div.js-ticket-profile-left div.js-ticket-user-img-wrp img.profile-image{position: absolute;top: 0;bottom: 0;left: 0;right: 0;margin: auto;max-width: 100%;max-height:100%;}
	div.js-ticket-profile-wrp div.js-ticket-profile-right{float: left;width: calc(100% - 200px - 20px); margin:0px 0px 0px 20px;}
	div.js-ticket-from-field{position: relative;}
	img.js-ticket-profile-form-img{position:absolute; top:8px; right:12px; bottom:0;cursor:pointer;}
	div#showhidemouseover{position: relative;display: inline-block;margin-top: 30px;min-width: 170px;text-align: center;margin-bottom: 15px;}
	label.js-ticket-file-upload-label{display: block;padding:11px 0px; border-radius: 2px;transition: background .3s; font-weight: unset;}
	input.js-ticket-upload-input{position: absolute;left: 0;top: 0;right: 0;bottom: 0;width: 0;height: 100%;opacity: 0;cursor: pointer;}
	span.js-ticket-input-field-style{display: inline-block;float: left;width: 100%;padding: 11px 5px;}
	textarea{border-radius: unset !important;}

	div.js-ticket-add-form-wrapper{float: left;width: 100%;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{float: left;width: calc(100% / 2 - 10px);margin: 0px 5px; margin-bottom: 20px; }
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{float: left;width: calc(100% / 1 - 10px); margin-bottom: 30px; }
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{float: left;width: 100%;margin-bottom: 5px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{float: left;width: 100%;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field.js-ticket-from-field-wrp-full-width select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width div.js-ticket-from-field select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat ;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat ;padding: 10px;line-height: initial;height: 50px;}

	div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 14px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;}

	span.help-block{font-size:14px;}
span.help-block{color:red;}
';
/*Code For Colors*/
$jssupportticket_css .= '
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color2.' !important;color:'.$color7.' !important;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background: #606062;color:'.$color7.';}
	label.js-ticket-file-upload-label{background-color:'.$color2.';border:1px solid '.$color2.'; color:'.$color7.';}
	span.js-ticket-input-field-style{background-color:'.$color3.';border:1px solid '.$color5.'; color:'.$color4.';}
	input.js-ticket-white-background{background-color:'.$color7.' !important;}
	textarea{background-color:'.$color3.' !important;border:1px solid '.$color5.' !important;; color:'.$color4.' !important;}
	div.js-ticket-profile-wrp div.js-ticket-profile-left div.js-ticket-user-img-wrp{background-color:#fff;border:1px solid '.$color5.';}
	input.js-ticket-recaptcha{background-color:'.$color3.' !important;border:1px solid '.$color5.' !important;}
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{background-color: '.$color2.';border:1px solid '.$color5.';color: '.$color7.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: '.$color2.';}


';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
