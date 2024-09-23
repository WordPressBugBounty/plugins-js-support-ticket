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
	/* staffs */
	div.js-ticket-staff-wrapper{float: left;width: 100%;margin-top: 17px;}
	div.js-ticket-staff-search-btn-wrp{width: auto;padding: 0px 5px;}
	span.js-ticket-staff-img{display: inline-block;width: 30px;margin: 18px 10px;}
	span.js-ticket-staff-img img.jsticketstafflogo{max-width: 100%;width: auto;}

	div.js-ticket-top-search-wrp{float: left;width: 100%;}
	div.js-ticket-search-fields-wrp{float: left;width: 100%;padding: 10px;}
	
	form#jssupportticketform{float: left;width: 100%;}
	div.js-ticket-fields-wrp{float: left;width: 100%;}
	div.js-ticket-fields-wrp div.js-ticket-form-field{float: left; width: calc(25% - 10px);margin: 0px 5px;position: relative;}
	div.js-ticket-fields-wrp div.js-ticket-form-field-download-search{width:75%;margin: 0px;}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{float: left;width: 100%;border-radius: 0px; padding: 10px;line-height: initial;height: 50px;}
	select.js-ticket-select-field{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;line-height: initial;height: 50px;}
	
	div.js-ticket-search-form-btn-wrp{float: left;width: 25%; padding: 0px 5px;}
	div.js-ticket-search-form-btn-wrp-download {width:25%;padding: 0px;margin-top: 0px;}
	div.js-ticket-search-form-btn-wrp input {float: left;width: calc(100% / 2 - 5px); padding: 13px 0px;text-align: center;margin: 0px 5px 0px 0; border-radius: 0px;line-height: initial;height: 50px;}
	div.js-ticket-search-form-btn-wrp-download input.js-search-button{float: left;width: calc(100% / 2 - 10px); padding: 17px 0px;text-align: center;margin: 0px 0px 0px 10px; border-radius: 0px; }
	div.js-ticket-search-form-btn-wrp-download input.js-reset-button{float: left;width: calc(100% / 2 - 10px); padding: 17px 0px;text-align: center; margin: 0px 0px 0px 10px; border-radius: 0px;}
	
	div.js-ticket-download-content-wrp{float: left;width: 100%;margin-top: 30px;}
	div.js-ticket-table-heading-wrp{float: left;width: 100%; padding: 10px;}
	div.js-ticket-table-heading-wrp div.js-ticket-table-heading-left{float: left;width: 70%;padding: 15px 10px;line-height: initial;}
	div.js-ticket-table-heading-wrp div.js-ticket-table-heading-right{float: left;width: 30%;text-align: right;}
	div.js-ticket-table-heading-wrp div.js-ticket-table-heading-right a.js-ticket-table-add-btn{display: inline-block;padding: 15px 25px;text-decoration: none;outline: 0px;line-height: initial;}
	div.js-ticket-table-heading-wrp div.js-ticket-table-heading-right a.js-ticket-table-add-btn span.js-ticket-table-add-img-wrp{display: inline-block;margin-right: 5px;}
	div.js-ticket-table-heading-wrp div.js-ticket-table-heading-right a.js-ticket-table-add-btn span.js-ticket-table-add-img-wrp img{vertical-align: text-bottom;}
	div.js-ticket-table-wrp{float: left;width: 100%;padding: 0;}
	div.js-ticket-table-wrp div.js-ticket-table-header{float: left;width: 100%;margin-bottom: 15px;}
	div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col{padding: 15px;text-align: center;line-height: initial;}
	div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col:first-child{text-align: left;}
	div.js-ticket-table-body{float: left;width: 100%;}
	div.js-ticket-table-body div.js-ticket-data-row{float: left;width: 100%;margin-bottom: 15px;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{padding: 15px;text-align: center;line-height: initial;min-height: 60px;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:first-child{text-align: left;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col .js-ticket-title-anchor {text-decoration: none;display: inline-block;height: 25px;width: 95%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col a.js-ticket-table-action-btn {padding: 4px 5px 8px;margin: 0 2px;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col img {display:inline-block;}
	
	span.js-ticket-display-block{display: none;} 

	select ::-ms-expand {display:none !important;}
	select{-webkit-appearance:none !important;}
';
/*Code For Colors*/
$jssupportticket_css .= '

	div.js-ticket-search-fields-wrp {background: '.$color3.';}
	div.js-ticket-top-search-wrp{border:1px solid '.$color5.';}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	select.js-ticket-select-field{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-search-form-btn-wrp input.js-search-button{background: '.$color1.' !important;color:'.$color7.' !important;border:1px solid '.$color5.';}
	div.js-ticket-search-form-btn-wrp input.js-search-button:hover{border-color:'.$color2.';}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{background: '.$color2.';color:'.$color7.';border:1px solid '.$color5.';}
	div.js-ticket-search-form-btn-wrp input.js-reset-button:hover{border-color:'.$color1.';}
	div.js-ticket-table-heading-wrp{background-color:'.$color3.';color:'.$color2.';}
	div.js-ticket-table-heading-wrp div.js-ticket-table-heading-right a.js-ticket-table-add-btn{background:'.$color2.';color:'.$color7.';border:1px solid '.$color5.';}
	div.js-ticket-table-heading-wrp div.js-ticket-table-heading-right a.js-ticket-table-add-btn:hover{border-color:'.$color1.';}
	div.js-ticket-table-header{background-color:#ecf0f5;border:1px solid '.$color5.';}
	div.js-ticket-table-header div.js-ticket-table-header-col{color: '.$color2.';}
	div.js-ticket-table-header div.js-ticket-table-header-col:last-child{}
	div.js-ticket-table-body div.js-ticket-data-row{border:1px solid '.$color5.';}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{color: '.$color4.';}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:last-child{}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col .js-ticket-title-anchor {color: '.$color1.';}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col a.js-ticket-table-action-btn {border:1px solid '.$color5.';background: #ffff;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col a.js-ticket-table-action-btn:hover {border-color:'.$color1.';}
	div.js-ticket-staff-wrapper div.js-ticket-table-body div.js-ticket-data-row {border:1px solid'.$color5.';}
	th.js-ticket-table-th{border-right:1px solid '.$color5.';}
	tbody.js-ticket-table-tbody{border:1px solid '.$color5.';}
	td.js-ticket-table-td{border-right:1px solid '.$color5.';}

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
