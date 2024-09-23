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
/* FeedBack */
	div.js-ticket-feedback-fields-margin-bottom{margin-bottom: 10px !important;}
	div.js-ticket-feedback-wrapper{float: left;width: 100%;padding: 10px;}
	div.js-ticket-feedback-list-wrapper{float: left;width: 100%; padding: 0px;margin-top: 20px;}
	div.js-ticket-feedback-heading{display: inline-block;width: 100%;padding: 15px;font-size: 18px;margin-bottom: 20px;line-height: initial;}
	div.jsst-feedback-det-wrp {float: left;width: 100%;box-shadow: 0 8px 6px -6px #dedddd;margin-bottom: 15px;}
	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list {float: left;width: 100%;margin: 0px 0;background: #fff;}
	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list div.jsst-feedback-det-list-top {float: left;width: 100%;padding: 10px;} 
	div.jsst-feedback-det-list-data-wrp {float: left;width: calc(100% - 100px);padding: 10px;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top {float: left;width: 100%;padding: 10px 0px;padding-top: 0px;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top div.jsst-feedback-det-list-data-top-title {float: left;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top div.jsst-feedback-det-list-data-top-val {float: left;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top div.jsst-feedback-det-list-data-top-val a.jsst-feedback-det-list-data-top-val-txt {display: inline-block;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top div.jsst-feedback-det-list-data-top-val a.jsst-feedback-det-list-data-top-val-txt img{display: inline-block;margin-left: 5px;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-btm{display:inline-block;float:left;width: calc(100% / 3);}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-btm div.jsst-feedback-det-list-datea-btm-rec{display:inline-block;float:left;padding: 10px 0px;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-btm div.jsst-feedback-det-list-datea-btm-rec div.jsst-feedback-det-list-data-btm-title{display:inline-block;float:left;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-btm div.jsst-feedback-det-list-datea-btm-rec div.jsst-feedback-det-list-data-btm-val{display:inline-block;float:left;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-left {float: left;width: 65%;}
	div.jsst-feedback-det-list-data-row {float: left;width: 100%;padding-bottom: 10px;}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-title {float: left;line-height: initial;}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val {float: left;margin-left: 5px;line-height: initial;}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val a.jsst-feedback-det-list-data-anch {display: inline-block;text-decoration: none;}
	div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-right {float: left;width: 35%;}
	div.jsst-feedback-det-list-cust-flds {float: left;width: 100%;padding-bottom: 10px;}
	div.jsst-feedback-det-list-cust-flds:last-child {padding-bottom: 0px;}
	div.jsst-feedback-det-list-cust-flds .jsst-feedback-det-list-cust-flds-title {float: left;line-height: initial;}
	div.jsst-feedback-det-list-cust-flds .jsst-feedback-det-list-cust-flds-val {float: left;margin-left: 5px;line-height: initial;}

	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list div.jsst-feedback-det-list-btm{display:inline-block;width:100%;float:left;padding: 15px;background: #fafafa;}
	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list div.jsst-feedback-det-list-btm div.jsst-feedback-det-list-btm-title{display:inline-block;float:left;line-height: initial;}
	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list div.jsst-feedback-det-list-btm div.jsst-feedback-det-list-btm-val{display:inline-block;float:left;line-height: initial;}
	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list-btm{display:inline-block;width:100%;}
	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list div.jsst-feedback-det-list-img-wrp{display:inline-block;float: left;text-align: center;padding: 10px;width: 100px;height: 100px;border-radius: 100%;}
	div.jsst-feedback-det-wrp  div.jsst-feedback-det-list div.jsst-feedback-det-list-img-wrp img{display:inline-block;max-width: 100%;height: auto;}
	div.js-ticket-top-search-wrp{float: left;width: 100%;}
	div.js-ticket-search-heading-wrp{float: left;width: 100%; padding: 10px 10px 10px 0px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{float: left;width: 70%;padding: 10px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right{float: left;width: 30%;text-align: right;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{display: inline-block;padding: 10px;text-decoration: none;outline: 0px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn span.js-ticket-add-img-wrp{display: inline-block;margin-right: 5px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn span.js-ticket-add-img-wrp img{vertical-align: text-bottom;}
	div.js-ticket-search-fields-wrp{float: left;width: 100%;padding: 10px;}
	form#jssupportticketform{float: left;width: 100%;}
	div.js-ticket-fields-wrp{float: left;width: 100%;}
	div.js-ticket-fields-wrp div.js-ticket-form-field{float: left; width: calc(100% / 2 - 10px);margin: 0px 5px;position: relative;}
	div.js-ticket-fields-wrp div.js-ticket-form-field-download-search{width:75%;margin: 0px;}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{float: left;width: 100%;border-radius: 0px; padding: 10px;line-height: initial;height: 50px;}
	select.js-ticket-select-field{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee; padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-search-form-btn-wrp{float: left;width: 100%; padding: 0px 5px;}
	div.js-ticket-search-form-btn-wrp-download {width:25%;padding: 0px;margin-top: 0px;}
	div.js-ticket-search-form-btn-wrp input.js-search-button{float: left;width: 120px;padding: 17px 0px;text-align: center;margin-right: 10px; border-radius: unset;line-height: initial;}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{float: left;width: 120px;padding: 17px 0px;text-align: center;border-radius: unset;line-height: initial;}
	div.js-ticket-search-form-btn-wrp-download input.js-search-button{float: left;width: calc(100% / 2 - 10px); padding: 17px 0px;text-align: center;margin: 0px 0px 0px 10px; border-radius: 0px; }
	div.js-ticket-search-form-btn-wrp-download input.js-reset-button{float: left;width: calc(100% / 2 - 10px); padding: 17px 0px;text-align: center; margin: 0px 0px 0px 10px; border-radius: 0px;}

	select ::-ms-expand {display:none !important;}
	select{-webkit-appearance:none !important;}
';
/*Code For Colors*/
$jssupportticket_css .= '
	div.js-ticket-search-fields-wrp {background: '.$color3.';}
	div.js-ticket-top-search-wrp{border:1px solid '.$color5.';}
	div.js-ticket-search-heading-wrp{background-color:'.$color4.';color:'.$color7.';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{background:'.$color2.';color:'.$color7.';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{background:rgba(125, 135, 141, 0.4);color:'.$color7.';}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val a.jsst-feedback-det-list-data-anch {color: '.$color4.';}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val.name {color: '.$color1.';}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-title {color: '.$color2.';}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val {color: '.$color4.';}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	select.js-ticket-select-field{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
	select#departmentid{background-color:'.$color3.';border:1px solid '.$color5.';}
	div.js-ticket-search-form-btn-wrp input.js-search-button{background: '.$color1.' !important;color:'.$color7.' !important;border: 1px solid '.$color5.';}
	div.js-ticket-search-form-btn-wrp input.js-search-button:hover{border-color: '.$color2.';}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{background: '.$color2.';color:'.$color7.';border: 1px solid '.$color5.';}
	div.js-ticket-search-form-btn-wrp input.js-reset-button:hover{border-color: '.$color1.';}
	div.jsst-feedback-det-list-cust-flds .jsst-feedback-det-list-cust-flds-title {color: '.$color2.';}
	div.jsst-feedback-det-list-cust-flds .jsst-feedback-det-list-cust-flds-value {color: '.$color4.';}
	div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-btm div.jsst-feedback-det-list-btm-title {color: '.$color4.';}
	div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-btm div.jsst-feedback-det-list-btm-val {color: '.$color2.';}


';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
