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
	div.js-ticket-mail-wrapper{float: left;width: 100%;margin-top: 17px;}
	div.js-ticket-mails-btn-wrp{float: left;width: 100%;margin-top: 20px;padding: 0px 5px; }
	div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn{float: left;width:calc(100% / 3 - 10px);margin: 0px 5px;text-align: center;}
	div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link{display: inline-block;float: left;width: 100%;padding: 15px;text-decoration: none;outline: 0;line-height: initial;}
	div.js-ticket-margin-top{margin-top: 50px !important;}
	th:first-child, td:first-child{padding-left: 10px !important;}
	img.js-ticket-mail-img{vertical-align: sub;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#to{background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat;line-height: initial;height: 60px;padding: 10px;}
	div.js-ticket-top-search-wrp{float: left;width: 100%;}
	div.js-ticket-search-heading-wrp{float: left;width: 100%; padding: 10px 10px 10px 0px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{float: left;width: 70%;padding: 10px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right{float: left;width: 30%;text-align: right;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{display: inline-block;padding: 10px;text-decoration: none;outline: 0px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn span.js-ticket-add-img-wrp{display: inline-block;margin-right: 5px;}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn span.js-ticket-add-img-wrp img{vertical-align: text-bottom;}

	div.js-ticket-post-reply-wrapper {float: left;width: 100%;margin-top: 20px;}
	div.js-ticket-post-reply-wrapper div.js-ticket-thread-heading{display: inline-block;width: 100%;padding: 15px;font-size: 18px;margin-bottom: 20px;line-height: initial;}
	div.js-ticket-post-reply-box{margin-bottom: 20px;}
	div.js-ticket-detail-box{float: left;width: 100%;}
	div.js-ticket-detail-box div.js-ticket-detail-left{float: left;width: 20%;padding: 20px 5px;}
	div.js-ticket-detail-left div.js-ticket-user-img-wrp{display:inline-block;width:100%;text-align: center;margin: 0 0 5px;height: 100px;position: relative;}
	div.js-ticket-detail-left div.js-ticket-user-img-wrp img.js-ticket-staff-img{width: auto;max-width: 100%;max-height: 100%;height: auto;position: absolute;top: 0;left: 0;right: 0;bottom: 0;margin: auto;}
	div.js-ticket-detail-left div.js-ticket-user-name-wrp{display:inline-block;width:100%;text-align: center;margin: 5px 0px;line-height: initial;}
	div.js-ticket-detail-left div.js-ticket-user-email-wrp{display:inline-block;width:100%;text-align: center;margin: 5px 0px;line-height: initial;}
	div.js-ticket-detail-box div.js-ticket-detail-right{float: left;width: calc(100% - 20%);}
	div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp{float: left;width: 100%;position: relative;padding:15px;line-height: initial;}
	div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp.js-ticket-min-height{min-height:292px;}
	div.js-ticket-detail-right div.js-ticket-row{float: left;width: 100%;padding: 0px 0 8px 0px;}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-title{display: inline-block;width:auto;margin: 0px 5px 0px 0px;}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value{display: inline-block;width:auto;}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value p {margin: 0;}
	div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 20px 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-decoration: none;}


';
/*Code For Colors*/
$jssupportticket_css .= '
	div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link{background-color: '.$color3.';border:1px solid  '.$color5.'; color: '.$color2.';}
	div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link:hover{background-color: '.$color1.';border:1px solid  '.$color2.'; color: '.$color7.';}
	div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link.active{background-color: '.$color1.' !important; border:1px solid  '.$color2.' !important; color: '.$color7.' !important;}
	div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link img{display:inline-block;}
	div.js-ticket-detail-box{border:1px solid  '.$color5.';}
	div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp{color: '.$color2	.';}
	div.js-ticket-detail-box div.js-ticket-detail-right{border-left:1px solid  '.$color5.';}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-title{color: '.$color1.';}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value span.js-ticket-subject-link{color: '.$color2.';}
	div.js-ticket-detail-right div.js-ticket-openclosed-box{color: '.$color7.';}
	div.js-ticket-detail-left div.js-ticket-user-name-wrp{color: '.$color4.';}
	div.js-ticket-detail-left div.js-ticket-user-email-wrp{color: '.$color4.';}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value{color: '.$color4.';}
	div.js-ticket-detail-right div.js-ticket-right-bottom{background-color:#fef1e6;color: '.$color4.';border-top:1px solid  '.$color5.';}
	div.js-ticket-detail-wrapper div.js-ticket-action-btn-wrp div.js-ticket-btn-box{background-color:#e7ecf2;border:1px solid  '.$color5.';}
	div.js-ticket-post-reply-wrapper div.js-ticket-thread-heading{background-color:'.$color2.';border:1px solid '.$color5.';color:'.$color7.';}
	div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border: 1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {border-color:'.$color2.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background: '.$color2.';color:'.$color7.';border: 1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{border-color: '.$color1.';}


';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
