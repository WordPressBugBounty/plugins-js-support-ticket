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
	/*Annoucment Detail*/
	div.js-ticket-knowledgebase-wrapper{float: left;width:100%;padding: 10px;}
	div.js-ticket-knowledgebase-details{float: left;width: 100%;padding: 15px;}
	div.js-ticket-top-search-wrp{float: left;width: 100%;}
	div.js-ticket-search-heading-wrp{float: left;width: 100%;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{float: left;width: 100%;font-weight: 600;text-transform: capitalize;font-size: 26px;line-height: initial;}
	div.js-ticket-knowledgebase-details {float: left;width: 100%;padding: 15px 0;line-height: 1.8;}
	div.js-ticket-categories-wrp {float: left;width: 100%;margin-top: 25px;}
	div.js-ticket-margin-bottom {margin-bottom: 20px;margin-top: 10px;}
	div.js-ticket-categories-heading-wrp {float: left;width: 100%;padding: 15px;line-height: initial;}


';
/*Code For Colors*/
$jssupportticket_css .= '
	div.js-ticket-search-heading-wrp{color:'.$color2.';}
	div.js-ticket-knowledgebase-details{color:'.$color4.';}
	div.js-ticket-categories-heading-wrp {background-color: '.$color3.';border: 1px solid '.$color5.';color: '.$color2.';}

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
