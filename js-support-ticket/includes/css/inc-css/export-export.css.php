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
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{float: left;width: 100%;margin-bottom: 5px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{float: left;width: 100%; position: relative;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .jsst-formfield-radio-button-wrap {display: inline-block;margin-right: 10px;}
	div.js-ticket-radio-btn-wrp{float: left;width: 100%;padding: 10px;height: 50px;}
	div.js-ticket-radio-btn-wrp input.js-ticket-form-field-radio-btn{margin-right: 5px; vertical-align: top;}
	div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;}
	div.js-ticket-select-user-btn {float: left;width: 30%;position: absolute;top: 0;right: 0;}
	div.js-ticket-select-user-btn a#userpopup {display: inline-block;width: 100%;text-align: center;padding: 10px 12px;text-decoration: none;outline: 0px;line-height: initial;height: 50px;}
	div.js-ticket-select-user-field {float: left;width: 100%;position: relative;}
	/*popup*/
	div#userpopupblack {background: rgba(0,0,0,0.5);position: fixed;width: 100%;height: 100%;top: 0px;left: 0px;z-index: 9989;}
	div#userpopup * {box-sizing: border-box;}
	div#userpopup{position: fixed;top:50%;left:50%;width:50%;z-index: 9999999999;transform: translate(-50%, -50%);background: #fff;box-sizing: border-box;max-height: 70%;overflow-x: hidden;overflow-y: auto;}
	div#userpopup .userpopup-top {float: left;width: 100%;background: #1572e8;padding: 15px;}
	div#userpopup .userpopup-top .userpopup-heading {float: left;color: #fff;font-weight: bold;font-size: 20px;line-height: initial;text-transform: capitalize;}
	div#userpopup .userpopup-top .userpopup-close {float: right;cursor: pointer;}
	div#userpopup .userpopup-search {float: left;width: 100%;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp {float: left;width: 100%;padding: 10px;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-fields {float: left;width: calc(100% / 3 - 10px);margin: 0 5px;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-fields input {display: inline-block;width: 100%;padding: 10px;height: 40px;border: 1px solid #ebecec;background: #f8fafc;color: #6c757d;box-shadow: unset;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-btn-wrp {float: left;width: 100%;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-btn-wrp input {float: left;padding: 10px 35px;border: 1px solid;margin: 8px 0 0 5px;cursor: pointer;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-btn-wrp .userpopup-search-btn {background: #1572e8;border-color: #1572e8;color: #fff;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-btn-wrp .userpopup-search-btn:hover {background: #fff;color: #1572e8;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-btn-wrp .userpopup-reset-btn {background: #32373c;border-color: #32373c;color: #fff;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-btn-wrp .userpopup-reset-btn:hover {background: #fff;color: #32373c;}
	div#userpopup .userpopup-search div.popup-field-title {float: left;width: 100%;padding: 10px;}
	div#userpopup .userpopup-search div.popup-field-obj {float: left;width: 100%;padding: 0 10px;}
	div#userpopup .userpopup-search div.popup-field-obj input {float: left;width: 100%;height: 45px;padding: 10px;margin: 0;box-shadow: unset;color: #6c757d;background: #f8fafc;border: 1px solid #d1d3d3;}
	div#userpopup div.popup-act-btn-wrp {float: left;width: 100%;text-align: center;padding: 15px;}
	div#userpopup div.popup-act-btn-wrp .popup-act-btn {display: inline-block;padding: 10px 35px;border-radius: unset;height: auto;line-height: auto;font-size: 14px;border: 1px solid #1572e8;color: #fff;background-color: #1572e8;}

	div#userpopup #userpopup-records-wrp {float: left;width: 100%;}
	div#userpopup #userpopup-records-wrp #userpopup-records {}
	div#userpopup #userpopup-records-wrp #userpopup-records .userpopup-records-desc {text-align: center;padding: 50px 15px;color: #23282d;}
	#jssupportticketform #userpopup {float: left;height: 45px;line-height: initial;border-radius: unset;box-shadow: unset;margin: 3px 3px;padding: 15px 10px;text-decoration: underline;color: #23282d;}
	#jssupportticketform #userpopup:hover {color: #23282d;text-decoration: none;}
	/*table*/
	.js-ticket-table-wrp {float: left;width: 100%;}
	.js-ticket-table-wrp .js-ticket-table-header {float: left;width: 100%;padding: 10px 15px;border-top: 1px solid #ebecec;border-bottom: 1px solid #ebecec;}
	.js-ticket-table-wrp .js-ticket-table-header .js-ticket-table-header-col {float: left;width: calc(100% / 4);font-weight: bold;color: #23282d;}
	.js-ticket-table-wrp .js-ticket-table-body {float: left;width: 100%;}
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row {float: left;width: 100%;padding: 10px 15px;border-bottom: 1px solid #ebecec;}
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row .js-ticket-table-body-col {float: left;width: calc(100% / 4);color: #23282d;}
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row .js-ticket-table-body-col .js-userpopup-link {color: #1572e8;}
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row .js-ticket-table-body-col .js-userpopup-link:hover {color: #1572e8;}
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row .js-ticket-table-body-col .js-ticket-display-block {display: none;}
	.js-ticket-table-wrp .js-ticket-table-header div:nth-child(1),
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row div:nth-child(1) {width: 10% !important;padding: 5px 0;}
	.js-ticket-table-wrp .js-ticket-table-header div:nth-child(2),
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row div:nth-child(2) {width: 25% !important;padding: 5px 0;}
	.js-ticket-table-wrp .js-ticket-table-header div:nth-child(3),
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row div:nth-child(3) {width: 40% !important;padding: 5px 0;}
	.js-ticket-table-wrp .js-ticket-table-header div:nth-child(4),
	.js-ticket-table-wrp .js-ticket-table-body div.js-ticket-data-row div:nth-child(4) {width: 25% !important;padding: 5px 0;}
	/* pagination */
	.jsst_userpages {float: left;width: 100%;padding: 10px 15px;text-align: right;}
	.jsst_userpages .jsst_userlink {display: inline-block;text-decoration: none;padding: 5px 10px;margin-left: 5px;background: #f8fafc;color: #1572e8;}
	.jsst_userpages .jsst_userlink:hover {background: #1572e8;color: #fff;}
	.jsst_userpages .jsst_userlink.selected {color: #23282d;background: transparent;}
	.jsst_userpages .jsst_userlink.selected:hover {color: #23282d;background: transparent;}

';
/*Code For Colors*/
$jssupportticket_css .= '
/* Add Form */
	div.js-ticket-select-user-btn a#userpopup {background-color: '.$color1.';color: '.$color7.';border: 1px solid '.$color5.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: '.$color2.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border: 1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color: '.$color2.';}
	div.js-ticket-radio-btn-wrp{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}

/* Add Form */

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
