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
	
	div.js-ticket-knowledgebase-wrapper{float: left;width:100%;padding: 10px;}
	div.js-ticket-top-search-wrp{float: left;width: 100%;}
	div.js-ticket-search-heading-wrp{float: left;width: 100%; padding: 10px 10px 10px 0px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{float: left;width: 70%;padding: 10px;}
	div.js-ticket-search-fields-wrp{float: left;width: 100%;padding: 10px;}
	
	form#jssupportticketform{float: left;width: 100%;}
	div.js-ticket-fields-wrp{float: left;width: 100%;}
	div.js-ticket-fields-wrp div.js-ticket-form-field{float: left; width: calc(100% / 2 - 10px);margin: 0px 5px;position: relative;}
	div.js-ticket-fields-wrp div.js-ticket-form-field-download-search{width:75%;margin: 0px;}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{float: left;width: 100%;border-radius: 0px; padding: 10px;line-height: initial;height: 50px;}
	div.js-ticket-fields-wrp div.js-ticket-form-field-download-search2 {margin-bottom: 10px;}
	
	div.js-ticket-search-form-btn-wrp{float: left;width: 100%; padding: 0px 5px;margin-top: 10px;}
	div.js-ticket-search-form-btn-wrp-download {width:25%;padding: 0px;margin-top: 0px;}
	div.js-ticket-search-form-btn-wrp input.js-search-button{float: left;width: 120px;padding: 13px 0px;height: 50px;text-align: center;margin-right: 10px; border-radius: unset;}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{float: left;width: 120px;padding: 13px 0px;height: 50px;text-align: center;border-radius: unset;}
	div.js-ticket-search-form-btn-wrp-download input.js-search-button{float: left;width: calc(100% / 2 - 10px); padding: 17px 0px;text-align: center;margin: 0px 0px 0px 10px; border-radius: 0px; line-height: initial;}
	div.js-ticket-search-form-btn-wrp-download input.js-reset-button{float: left;width: calc(100% / 2 - 10px); padding: 17px 0px;text-align: center; margin: 0px 0px 0px 10px; border-radius: 0px;line-height: initial;}
	
	div.js-ticket-categories-wrp{float: left;width: 100%;margin-top: 25px;}
	div.js-ticket-margin-bottom{margin-bottom: 20px;margin-top: 10px;}
	div.js-ticket-categories-heading-wrp{float: left;width: 100%;padding: 15px;line-height: initial;}
	div.js-ticket-categories-wrp div.js-ticket-position-relative{position: relative;}
	div.js-ticket-categories-content{float: left;width: 100%;padding: 20px 0px 0px;}
	div.js-ticket-categories-content div.js-ticket-category-box{float: left;width:calc(13.6% - 10px);margin: 0px 5px;margin-bottom: 10px;}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title{float: left;text-decoration: none;outline: 0px;width: 100%;}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-name{float: left;padding: 15px 5px;width: 100%;line-height: initial;text-align: center;}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-download-logo{display: inline-block;float: right;padding: 5px;width: 30px;height: 30px;text-align: center;margin: 10px 10px;}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-download-logo img.js-ticket-download-img{vertical-align: unset;}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-kb-logo{display: inline-block;float: left;height: 100px;width: 100%;position: relative;margin: 0;}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-kb-logo img.js-ticket-kb-img{position: absolute;top: 0px;left: 0px;right: 0px;bottom: 0px;margin:auto;max-width: 100%;max-height: 100%;}
	
	div.js-ticket-downloads-wrp{float: left;width: 100%;margin-top: 18px;}
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{float: left;width: 100%;padding: 15px;line-height: initial;}
	div.js-ticket-downloads-content{float: left;width: 100%;padding: 20px 0px;}
	div.js-ticket-downloads-content div.js-ticket-download-box{float: left;width: 100%;padding: 10px;box-shadow: 0 8px 6px -6px #dedddd; margin-bottom: 10px;line-height: initial;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left{float: left;width: 100%;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title{float: left;width: 100%;padding: 9px; cursor: pointer;text-decoration: none;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title img.js-ticket-download-icon{float: left;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name{width: calc(100% - 60px); display: inline-block;padding: 10px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-right{float: left;width: 20%;}
	div.js-ticket-download-btn{float: left;width: 100%;text-align: center;}
	div.js-ticket-download-btn button.js-ticket-download-btn-style{display: inline-block;padding: 9px 20px;border-radius: unset;font-weight: unset;}
	div.js-ticket-download-btn a.js-ticket-download-btn-style{display: inline-block;padding: 9px 20px;border-radius: unset;font-weight: unset;text-decoration: none;outline: 0;}
	div.js-ticket-download-btn button.js-ticket-download-btn-style img.js-ticket-download-btn-icon{vertical-align: text-top;margin-right: 5px;}
	div.js-ticket-download-btn a.js-ticket-download-btn-style img.js-ticket-download-btn-icon{vertical-align: text-top;margin-right: 5px;}

	select ::-ms-expand {display:none !important;}
	select{-webkit-appearance:none !important;}
';
/*Code For Colors*/
$jssupportticket_css .= '
	div.js-ticket-top-search-wrp{border:1px solid  '.$color5.';}
	div.js-ticket-search-heading-wrp{background-color: '.$color4.';color: '.$color7.';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{background: '.$color2.';color: '.$color7.';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{background:rgba(125, 135, 141, 0.4);color: '.$color7.';}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color: #fff;border:1px solid  '.$color5.';color: '.$color4.';}
	
	div.js-ticket-search-form-btn-wrp input.js-search-button{background: '.$color1.' !important;color: '.$color7.' !important;border:1px solid  '.$color5.';}
	div.js-ticket-search-form-btn-wrp input.js-search-button:hover{border-color:'.$color2.';}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{background: '.$color2.';color: '.$color7.';border:1px solid  '.$color5.';}
	div.js-ticket-search-form-btn-wrp input.js-reset-button:hover{border-color: '.$color1.';}
	
	div.js-ticket-table-header{background-color:#ecf0f5;border:1px solid  '.$color5.';}
	div.js-ticket-table-header div.js-ticket-table-header-col{border-right:1px solid  '.$color5.';}
	div.js-ticket-table-header div.js-ticket-table-header-col:last-child{border-right:none;}
	div.js-ticket-table-body div.js-ticket-data-row{border:1px solid  '.$color5.';border-top:none}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{border-right:1px solid  '.$color5.';}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:last-child{border-right:none;}
	
	th.js-ticket-table-th{border-right:1px solid  '.$color5.';}
	tbody.js-ticket-table-tbody{border:1px solid  '.$color5.';}
	td.js-ticket-table-td{border-right:1px solid  '.$color5.';}
	
	div.js_ticketattachment{background-color: '.$color3.';border:1px solid  '.$color5.';}
	div.js-ticket-categories-heading-wrp{background-color: '.$color2.';border:1px solid  '.$color5.';color: '.$color7.';}
	div.js-ticket-categories-content div.js-ticket-category-box{background-color: #fff;border:1px solid  '.$color5.';}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-download-logo{border-bottom: 1px solid '.$color5.';}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title:hover{color: '.$color2.';}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-name {color: '.$color2.';}
	div.js-ticket-categories-content div.js-ticket-category-box a.js-ticket-category-title span.js-ticket-category-name:hover {color: '.$color1.';}
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{background-color:'.$color2.';border:1px solid  '.$color5.';color: '.$color7.';}
	div.js-ticket-downloads-content div.js-ticket-download-box{background-color: #fff;border:1px solid  '.$color5.';}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {color: '.$color4.';}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name:hover {color: '.$color2.';}
	div.js-ticket-download-btn button.js-ticket-download-btn-style{background-color: '.$color2.';}
	div.js-ticket-download-btn a.js-ticket-download-btn-style{background-color: '.$color2.'; color: '.$color7.';}
	div#js-ticket-main-popup{background:  '.$color7.';}

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
