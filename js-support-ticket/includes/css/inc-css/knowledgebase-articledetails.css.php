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
	div.js-ticket-search-heading-wrp{float: left;width: 100%;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{float: left;width: 100%;line-height: initial;font-size: 26px;font-weight: 600;text-transform: capitalize;}
	div.js-ticket-knowledgebase-details{float: left;width: 100%;padding: 15px 0;line-height: 1.8;}
	div.js-ticket-knowledgebase-details p {margin: 0;}
	div.js-ticket-categories-wrp{float: left;width: 100%;margin-top: 25px;}
	div.js-ticket-categories-heading-wrp{float: left;width: 100%;padding: 15px;line-height: initial;}
	div.js-ticket-margin-bottom{margin-bottom: 20px;margin-top: 10px;}

	div.js-ticket-downloads-wrp{float: left;width: 100%;padding: 10px;}
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{float: left;width: 100%;padding: 15px;line-height: initial;}
	div.js-ticket-downloads-content{float: left;width: 100%;padding: 20px 0px;}
	div.js-ticket-downloads-content div.js-ticket-download-box{float: left;width: 100%;padding: 10px;box-shadow: 0 8px 6px -6px #dedddd; margin-bottom: 10px;line-height: initial;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left{float: left;width: 80%;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title{float: left;width: 100%;padding: 9px; cursor: pointer;text-decoration: none;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title img.js-ticket-download-icon{float: left;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name{width: calc(100% - 60px); display: inline-block;padding: 10px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-right{float: left;width: 20%;}
	div.js-ticket-download-btn{float: left;width: 100%;text-align: right;padding: 7px 10px;}
	div.js-ticket-download-btn button.js-ticket-download-btn-style{display: inline-block;padding: 9px 20px;border-radius: unset;font-weight: unset;}
	div.js-ticket-download-btn a.js-ticket-download-btn-style{display: inline-block;padding: 10px 20px;border-radius: unset;font-weight: unset;text-decoration: none;outline: 0;line-height: initial;}
	div.js-ticket-download-btn button.js-ticket-download-btn-style img.js-ticket-download-btn-icon{vertical-align: text-top;margin-right: 5px;}
	div.js-ticket-download-btn a.js-ticket-download-btn-style img.js-ticket-download-btn-icon{vertical-align: text-top;margin-right: 5px;}
';
/*Code For Colors*/
$jssupportticket_css .= '
	div.js-ticket-top-search-wrp{}
	div.js-ticket-search-heading-wrp{color: '.$color2.';}
	div.js-ticket-knowledgebase-details{color: '.$color4.';}
	div.js-ticket-categories-heading-wrp{background-color:'.$color3.';border:1px solid  '.$color5.';color:'.$color2.';}
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{background-color:'.$color2.';border:1px solid  '.$color5.';color: '.$color7.';}
	div.js-ticket-downloads-content div.js-ticket-download-box{background-color: #fff;border:1px solid  '.$color5.';}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {color: '.$color4.';}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name:hover {color: '.$color2.';}
	div.js-ticket-download-btn button.js-ticket-download-btn-style{background-color: '.$color2.';}
	div.js-ticket-download-btn a.js-ticket-download-btn-style{color: '.$color1.'; background-color: '.$color7.';border:1px solid  '.$color1.';}
	div.js-ticket-download-btn a.js-ticket-download-btn-style:hover{color: '.$color2.';border-color:'.$color2.';}



';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
