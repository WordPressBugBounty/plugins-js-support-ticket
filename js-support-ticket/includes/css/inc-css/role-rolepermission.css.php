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
	div.js-ticket-roles-wrapper{float: left;width: 100%;padding: 10px;}
	div.js-ticket-top-search-wrp{float: left;width: 100%;}
	div.js-ticket-search-heading-wrp{float: left;width: 100%; padding: 10px 10px 10px 0px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{float: left;width: 70%;padding: 10px;line-height: initial;}
	div.js-ticket-roles-list-wrapper{float: left;width: 100%;margin-top: 20px;}

	div.js-ticket-add-role-field-wrp-top{margin: 0px !important;width: 100% !important;}
	div.js-ticket-categories-heading-wrp{float: left;width: 100%;padding: 15px;line-height: initial;}
	div.js-ticket-role-wrp{float: left;width: 100%; margin-top: 20px;margin-bottom: 20px;}
	div.js-ticket-role-wrp div.js-ticket-add-role-field-wrp{float: left;width: calc(100% / 3 - 10px);margin: 0px 5px 10px;padding: 10px;line-height: initial;}
	div.js-ticket-role-wrp div.js-ticket-add-role-field-wrp.js-ticket-margin-bottom{margin-bottom: 10px;}
	input.js-ticket-checkbox{vertical-align: sub;}
	label.js-ticket-label{display: inline-block;margin: 0px;vertical-align:middle;}
	span.help-block{font-size:14px;}
span.help-block{color:red;}




';
/*Code For Colors*/
$jssupportticket_css .= '

	div.js-ticket-top-search-wrp{border:1px solid '.$color5.';}
	div.js-ticket-search-heading-wrp{background-color:'.$color3.';color:'.$color2.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:'.$color3.';border:1px solid '.$color5.';}

	div.js-ticket-categories-heading-wrp{background-color:'.$color2.';border:1px solid '.$color5.';color: '.$color7.';}
	div.js-ticket-add-role-field-wrp{background-color: #fff;border:1px solid '.$color5.';color: '.$color4.';}
	label.js-ticket-label{color:'.$color4.';}


';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
