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
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;line-height: initial;min-height: 50px;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat;padding: 10px;line-height: initial;height: 50px;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea {width: 100%;padding: 10px;line-height: initial;min-height: 50px;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp .tk_attachments_configform {float: left;width: 100%;margin-top: 5px;font-size: 14px;line-height: 25px;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;height: 50px;line-height: initial;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat ;padding: 10px;height: 50px;line-height: initial;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat ;padding: 10px;height: 50px;line-height: initial;}

div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 20px 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-decoration: none;}
span.js-ticket-sub-fields{float:left;display: inline-block;width: calc(100% / 4 - 10px);margin-right: 10px;padding: 10px;line-height: initial;height: 50px;}
span.js-ticket-sub-fields label {vertical-align: middle;margin-left: 5px !important;}

input#kb1{width: 20px;height:20px;vertical-align: sub;}
input#downloads1{width: 20px;height:20px;vertical-align: sub;}
input#announcement1{width: 20px;height:20px;vertical-align: sub;}
input#faqs1{width: 20px;height:20px;vertical-align: sub;}
label#forkb{display: inline-block;margin: 0px; }
label#fordownloads{display: inline-block;margin: 0px; }
label#forannouncement{display: inline-block;margin: 0px; }
label#forfaqs{display: inline-block;margin: 0px; }
input#append1{vertical-align: sub;}
label#forappend{display: inline-block;margin: 0px;}

div.js-ticket-radio-btn-wrp{float: left;width: 100%;padding: 11px}
div.js-ticket-radio-btn-wrp input.js-ticket-form-field-radio-btn{margin-right: 5px; vertical-align: top;}
div.js-ticket-radio-btn-wrp label#forsendmail{margin: 0px;display: inline-block; margin-right: 30px;}
img.js-ticket-category-img{display: inline-block;max-width: 100px;margin-top: 10px;}
div.js-ticket-from-field{position: relative;}

div#msgshowcategory{float: left;width: 100%;}
div#msgshowcategory div.js-ticket-notice-wrapper{float: left; box-sizing:border-box;padding: 15px;margin-bottom: 10px; width: 100%;}
div#msgshowcategory div.js-ticket-notice-wrapper div.js-ticket-notice{float: left;width: auto;margin: 0px 5px 0px 0px;}
div#msgshowcategory div.js-ticket-notice-wrapper div.js-ticket-question{float: left;width: auto;}

div.js-ticket-answer-btn{float: left;width: 100%;padding-top: 10px;}
div.js-ticket-answer-btn a.js-ticket-yes{display: inline-block;min-width: 100px;text-align: center;padding: 8px 5px;margin:0px 10px 0px 0px;}
div.js-ticket-answer-btn a.js-ticket-no{display: inline-block;min-width: 100px;text-align: center;padding: 8px 5px;}
span.help-block{font-size:14px;}
span.help-block{color:red;}

select ::-ms-expand {display:none !important;}
select{-webkit-appearance:none !important;}

';
/*Code For Colors*/
$jssupportticket_css .= '

/* Add Form */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: '.$color2.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:#fff;border:1px solid '.$color5.';color:'.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid{background-color:'.$color3.';border:1px solid '.$color5.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea {background-color:#fff;border:1px solid '.$color5.';color:'.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{background-color:'.$color3.' !important;border:1px solid '.$color5.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
span.js-ticket-sub-fields{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp .tk_attachments_configform {color: '.$color4.';}
.js-userpopup-link{color:'.$color2.';}
div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border:1px solid '.$color5.';}
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color:'.$color2.';}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background: '.$color2.';color:'.$color7.';border:1px solid '.$color5.';}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{border-color:'.$color1.';}
a.js-ticket-delete-attachment{background-color:#ed3237;color:'.$color7.';}
div.js-ticket-radio-btn-wrp{background-color:'.$color3.';border:1px solid '.$color5.';}
span.tk_attachments_addform{background-color:'.$color2.';color:'.$color7.';}
/* Add Form */

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
