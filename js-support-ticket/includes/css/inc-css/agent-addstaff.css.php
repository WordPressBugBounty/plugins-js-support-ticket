<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
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
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{float: left;width: 100%;position: relative;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{float: left;width: 100%;border-radius: 0px;padding: 10px;height: 50px;line-height: initial;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;height: 60px;line-height: initial;padding: 10px;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field.js-ticket-from-field-wrp-full-width select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat;}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width div.js-ticket-from-field select#status{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat;}

	div.js-ticket-form-btn-wrp{float: left;width:calc(100% - 20px);margin: 0px 10px;text-align: center;padding: 25px 0px 10px 0px;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{padding: 20px 10px;margin-right: 10px;min-width: 120px;border-radius: 0px;line-height: initial;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{display: inline-block; padding: 20px 10px;min-width: 120px;border-radius: 0px;line-height: initial;text-decoration: none;}

	span.tk_attachments_configform {float: left;width: 100%;font-size: 14px;line-height: 25px;margin-top: 5px;}


	div.js-ticket-select-user-field{float: left;width: 100%;}
	div.js-ticket-select-user-field input#username-text{width: 100%;}
	div.js-ticket-select-user-btn{float: left;width: 30%;position: absolute;top: 0;right: 0;	}
	div.js-ticket-select-user-btn a#userpopup{display: inline-block;width: 100%;text-align: center;padding: 10px 10px;text-decoration: none;outline: 0px;line-height: initial;height:50px;}

	#records{float: left;width: 100%;padding: 0px 10px;}
	select.js-ticket-select-field{float: left;width: 100%;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee; }
	div.js-ticket-reply-attachments{display: inline-block;width: 100%;margin-bottom: 20px;}
	div.js-ticket-reply-attachments div.js-attachment-field-title{display: inline-block;width: 100%;padding: 15px 0px;}
	div.js-ticket-reply-attachments div.js-attachment-field{display: inline-block;width: 100%;}
	div.tk_attachment_value_wrapperform{float: left;width:100%;padding:0px 0px;}
	div.tk_attachment_value_wrapperform span.tk_attachment_value_text{float: left;width: calc(100% / 3 - 10px);padding: 5px 5px;margin: 5px 5px;position: relative;}
	div.tk_attachment_value_wrapperform span.tk_attachment_value_text input.js-attachment-inputbox{width: 100%;max-width: 100%;max-height:100%;}
	span.tk_attachment_value_text span.tk_attachment_remove{background: url('.JSST_PLUGIN_URL.'includes/images/close.png) no-repeat;background-size: 100% 100%;position: absolute;width: 20px;height: 20px;top: 12px;right:6px;}
	span.js-ticket-staff-img {float: left;margin: 10px 0;width: 120px;}
	span.js-ticket-staff-img img {}

	div#userpopupblack{background: rgba(0,0,0,0.7);position: fixed;width: 100%;height: 100%;top:0px;left:0px;z-index: 9989;}
	div#userpopup{position: fixed;top:50%;left:50%;width:60%; max-height: 70%; padding-top:0px;z-index: 99999;overflow-y: auto; overflow-x: hidden;transform: translate(-50%,-50%);}
	div.jsst-popup-header{width:100%;font-size:20px;float:left;padding: 20px 10px; font-weight: bold;line-height: initial;}
	div.popup-header-close-img{position: absolute;top:22px;right: 22px;background-image:url('.JSST_PLUGIN_URL.'includes/images/close-icon-white.png);background-size: 100%;width:20px;height: 20px;opacity: 1;cursor: pointer;}

	div.jsst-popup-wrapper input{margin-bottom:0px; }
	div.jsst-popup-wrapper input#edited_time{font-size: 16px;}
	div.jsst-popup-wrapper textarea{width: 100%;}
	div.jsst-popup-wrapper div.js-form-button-wrapper{text-align: center;border-top: 1px solid #e0dce0;width: 94%;margin: 0px 3%;margin-top: 20px;}
	div.jsst-popup-wrapper div.js-form-button-wrapper input.button{display: inline-block;float: none;padding: 5px 20px;border-radius: 2px;margin-top: 15px;margin-bottom: 15px;min-width: 100px;}
	div.jsst-popup-wrapper div.js-form-button-wrapper input.js-merge-cancel-btn{padding: 16px 10px;min-width: 120px;border-radius: unset;}
	div.jsst-popup-wrapper div.js-form-button-wrapper input.js-merge-save-btn{padding: 16px 10px;min-width: 120px;border-radius: unset;}

	div.js-ticket-popup-search-wrp{float: left;width: 100%;padding: 30px 5px 15px;}

	div.js-ticket-search-top{float: left;width: 100%;}
	div.js-ticket-search-top div.js-ticket-search-left{float: left;width: 70%;}
	div.js-ticket-search-top div.js-ticket-search-left div.js-ticket-search-fields-wrp{float: left;width: 100%;padding: 0px}
	div.js-ticket-search-top div.js-ticket-search-left div.js-ticket-search-fields-wrp input.js-ticket-search-input-fields{float: left;width: calc(100% / 3 - 10px);margin:0px 5px;padding: 10px;border-radius: 0px;line-height: initial;height: 50px;}
	div.js-ticket-search-top div.js-ticket-search-right{float: left;width: 30%;}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp{float: left;width: 100%;}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-search-btn{width: calc(100% / 2 - 5px);padding: 10px;border-radius: 0px;line-height: initial;height: 50px;}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-reset-btn{width: calc(100% / 2 - 5px);padding: 10px;border-radius: 0px;line-height: initial;height: 50px;}

	div.js-ticket-table-wrp{float: left;width: 100%;padding: 0;}
	div.js-ticket-table-wrp div.js-ticket-table-header{float: left;width: 100%;margin-bottom: 10px;}
	#userpopup div.js-ticket-table-header{margin-bottom: 0px;}
	div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col{padding: 15px;text-align: center;float: left;width: 25%;line-height: initial;}
	div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col:first-child{text-align: left;padding-left: 10px;width: 10%;}
	div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col:nth-child(3){width: 40%;}
	div.js-ticket-table-body{float: left;width: 100%;}
	div.js-ticket-table-body div.js-ticket-data-row{float: left;width: 100%;margin-bottom: 10px;}
	#userpopup div.js-ticket-data-row{margin-bottom: 0px;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{padding: 15px;text-align: center;float: left;width: 25%;line-height: initial;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col span.js-ticket-title {display: inine-block;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col a.js-userpopup-link {display: inine-block;text-decoration: none;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:first-child{text-align: left;padding-left: 10px;width: 10%;}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:nth-child(3){width: 40%;}
	span.js-ticket-display-block{display: none;}

	div#records div.jsst_userpages{text-align: right;padding:5px; margin: 10px 5px;width: calc(100% - 10px);float:left;}
	div#records div.jsst_userpages a.jsst_userlink{display: inline-block;padding:5px 15px;margin-left:5px;text-decoration: none;background:rgba(0, 0, 0, 0.05) none repeat scroll 0 0;line-height: initial;}
	div#records div.jsst_userpages span.jsst_userlink{display: inline-block;padding:5px 15px;margin-left:5px;line-height: initial;}

	span.help-block{font-size:14px;}
	span.help-block{color:red;}

	div.js-ticket-append-signature-wrp{float: left;width: calc(100% / 2 - 25px); margin-right:25px;margin-bottom: 20px;}
	div.js-ticket-append-signature-wrp.js-ticket-append-signature-wrp-full-width{width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box{float: left;width: calc(100% / 3 - 10px);margin: 0px 5px;padding: 11px;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box.js-ticket-signature-radio-box-full-width{width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-append-field-title{float: left;width: 100%;margin-bottom: 15px;}
	div.js-ticket-append-signature-wrp div.js-ticket-append-field-wrp{float: left;width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box label#forcanappendsignature{margin: 0px;display: inline-block;vertical-align:text-bottom;line-height: initial;margin-left: 5px;}

	select ::-ms-expand {display:none !important;}
	select{-webkit-appearance:none !important;}








';
/*Code For Colors*/
$jssupportticket_css .= '

/* Add Form */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color:'.$color2.';}
	div.js-ticket-select-user-btn a#userpopup{background-color:'.$color1.';color:'.$color7.';border: 1px solid '.$color5.';}
	div.js-ticket-select-user-btn a#userpopup:hover{border-color: '.$color1.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{background-color:#fff;border:1px solid '.$color5.';color: '.$color4.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid{background-color:'.$color3.';border:1px solid '.$color5.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{background-color:'.$color3.' !important;border:1px solid '.$color5.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status{background-color:#fff !important;border:1px solid '.$color5.';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid{background-color:'.$color3.' !important;border:1px solid '.$color5.';}
	span.js-ticket-sub-fields{background-color:'.$color3.' !important;border:1px solid '.$color5.';}
	.js-userpopup-link{color:'.$color2.';}
	div.js-ticket-form-btn-wrp{border-top:2px solid '.$color2.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:'.$color1.' !important;color:'.$color7.' !important;border:1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color:'.$color2.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background: '.$color2.';color:'.$color7.';border:1px solid '.$color5.';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{border-color:'.$color1.';}
	a.js-ticket-delete-attachment{background-color:#ed3237;color:'.$color7.';}
	div.js-ticket-radio-btn-wrp{background-color:'.$color3.';border:1px solid '.$color5.';}
	span.tk_attachments_addform{background-color:'.$color2.';color:'.$color7.';}
	select.js-ticket-select-field{background-color:#fff !important;border:1px solid '.$color5.';color: '.$color4.';}
	div.jsst-popup-header{background: '.$color1.';color:'.$color7.';}
	div#userpopup{background: '.$color7.';}
	div.jsst-popup-wrapper{background-color:'.$color7.';}
	span.tk_attachments_configform{color:'.$color4.';}
	div.tk_attachment_value_wrapperform{border: 1px solid '.$color5.';background: #fff;}
	span.tk_attachment_value_text{border: 1px solid '.$color5.';background-color:'.$color7.';}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box{border:1px solid '.$color5.';background-color:#fff;}
	div.js-ticket-search-top div.js-ticket-search-left div.js-ticket-search-fields-wrp input.js-ticket-search-input-fields{border:1px solid '.$color5.';background-color:#fff;color: '.$color4.';}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-search-btn{background: '.$color1.';color:'.$color7.';border:1px solid '.$color5.';}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-search-btn:hover{border-color:'.$color2.';}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-reset-btn{background: '.$color2.';color:'.$color7.';border:1px solid '.$color5.';}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-reset-btn:hover{border-color: '.$color1.';}

	div.js-ticket-table-header{background-color:#ecf0f5;border:1px solid '.$color5.';}
	div.js-ticket-table-header div.js-ticket-table-header-col{}
	div.js-ticket-table-header div.js-ticket-table-header-col:last-child{}
	div.js-ticket-table-body div.js-ticket-data-row{border:1px solid '.$color5.';}

	#userpopup div.js-ticket-table-header, 
	#userpopup div.js-ticket-table-body div.js-ticket-data-row {border: 0;border-bottom:1px solid '.$color5.';}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:last-child{}
	th.js-ticket-table-th{border-right:1px solid '.$color5.';}
	tbody.js-ticket-table-tbody{border:1px solid '.$color5.';}
	td.js-ticket-table-td{border-right:1px solid '.$color5.';}




/* Add Form */

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
