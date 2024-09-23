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
/* Tickets Details*/
	div.js-ticket-ticket-detail-wrapper{float: left;width: 100%;padding: 10px;}
	div.js-ticket-detail-wrapper{float: left;width: 100%; padding: 0px}
	div.js-ticket-detail-box{float: left;width: 100%;}
	div.js-ticket-detail-box div.js-ticket-detail-left{float: left;width: 20%;padding: 20px 5px;}
	div.js-ticket-detail-left div.js-ticket-user-img-wrp{display:inline-block;width:100%;text-align: center;margin: 5px 0px;height: 120px;position: relative;border-radius: 100%;}
	div.js-ticket-detail-left div.js-ticket-user-img-wrp img{width: auto;max-width: 100%;max-height: 100%;height: auto;position: absolute;top: 0;left: 0;right: 0;bottom: 0;margin: auto;border-radius: 100%;}
	div.js-ticket-detail-left div.js-ticket-user-name-wrp{display:inline-block;width:100%;text-align: center;margin: 5px 0px;}
	div.js-ticket-detail-left div.js-ticket-user-email-wrp{display:inline-block;width:100%;text-align: center;margin: 5px 0px;}
	div.js-ticket-detail-box div.js-ticket-detail-right{float: left;width: calc(100% - 20%);}
	div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrapper{float: left;}
	div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp{float: left;width: 100%;position: relative;padding:20px 20px 0px 20px;}
	div.js-ticket-detail-right div.js-ticket-row{float: left;width: 100%;padding: 0px 0 8px 0px;}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-title{display: inline-block;width:auto;margin: 0px 5px 0px 0px;}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value{display: inline-block;width:auto;}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value .js-ticket-field-value-t {display: inline-block;}
	div.js-ticket-status-note{display: inline-block;}
	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value.js-ticket-priorty{padding: 3px;min-width: 120px;text-align: center;}
	div.js-ticket-detail-right div.js-ticket-openclosed-box{display: inline-block;position: absolute;padding: 20px 5px; text-align: center;right: 10px;font-weight:bold;min-width: 80px;}
	div.js-ticket-detail-right div.js-ticket-right-bottom{display: inline-block;float: left;width: 100%;padding:10px 0px 0px 20px;}
	div.js-ticket-detail-wrapper div.js-ticket-action-btn-wrp{display: inline-block;float: left;width: 100%; padding:8px 5px;}
	div.js-ticket-detail-wrapper div.js-ticket-action-btn-wrp div.js-ticket-btn-box{display: inline-block;float: left;min-width:89px;text-align: center;margin-right: 5px;margin-left: 5px; margin-bottom: 5px;margin-top: 5px;}
	div.js-ticket-detail-wrapper div.js-ticket-action-btn-wrp div.js-ticket-btn-box a.js-button{display: inline-block;width: 100%;padding: 5px;}
	div.js-ticket-detail-wrapper div.js-ticket-more-actions-btn-wrp{display: inline-block;width: 100%;float: left;z-index: 9;text-align: center;}
	div#action-div div.js-row{display: inline-block; border-top: 1px solid #ddeeee;width:60%;margin:0px 20%;padding-top: 10px;margin-top: 10px;}
	div#userpopupforchangepriority{position: fixed;top:50%;left:50%;width:40%;max-height:40%;z-index: 99999;overflow-y: auto; overflow-x: hidden;text-align: left;transform: translate(-50%,-50%);}
	div#userpopupforchangepriority div.js-ticket-priorty-header{float: left;width: 100%;padding: 20px 15px;font-weight: bold;font-size: 18px;position: relative;}
	div#userpopupforchangepriority div.js-ticket-priorty-header span.close-history{position: absolute;top: 22px;right: 16px;background:url('.JSST_PLUGIN_URL.'includes/images/close-icon-white.png) no-repeat;background-size: 100%;width:25px;height: 25px;cursor: pointer;font-weight: bold;font-size: 18px;}
	div#userpopupforchangepriority div.js-ticket-priorty-fields-wrp{float: left;width: 100%;}
	div#userpopupforchangepriority div.js-ticket-priorty-fields-wrp div.js-ticket-select-priorty{float: left;width: 100%;text-align: center;padding: 35px 20px;}
	div#userpopupforchangepriority div.js-ticket-priorty-fields-wrp div.js-ticket-select-priorty select#priority{width: 80%;border-radius: 0;float: none;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;}
	div#userpopupforchangepriority div.js-ticket-priorty-fields-wrp div.js-ticket-select-priorty select#prioritytemp{width: 80%;border-radius: 0;float: none;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;}
	div#popupforagenttransfer{position: fixed;top:50%;left:50%;width:40%;max-height:70%;z-index: 99999;overflow-y: auto; overflow-x: hidden;text-align: left;transform: translate(-50%,-50%);}
	div#popupforagenttransfer form {float: left;width: 100%;padding: 30px;}
	div#popupfordepartmenttransfer{position: fixed;top:50%;left:50%;width:40%;max-height:70%;z-index: 99999;overflow-y: auto; overflow-x: hidden;text-align: left;transform: translate(-50%,-50%);}
	div#popupfordepartmenttransfer form {float: left;width: 100%;padding: 30px;}
	div#popupforinternalnote{position: fixed;top:50%;left:50%;width:40%;max-height:70%;z-index: 99999;overflow-y: auto; overflow-x: hidden;text-align: left;transform: translate(-50%,-50%);}
	div#popupforinternalnote form {float: left;width: 100%;padding: 30px;}
	div.js-ticket-priorty-btn-wrp{width: calc(100% - 20px);float: left;text-align: center; padding: 15px 0px;margin: 0px 10px;}
	div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-save{min-width:150px; padding: 20px 5px;line-height: initial;border-radius: 0;}
	div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-cancel{min-width:150px; padding: 20px 5px;line-height: initial;border-radius: 0;}
	div.js-ticket-post-reply-wrapper {float: left;width: 100%;margin-top: 20px;}
	div.js-ticket-post-reply-wrapper div.js-ticket-thread-heading{display: inline-block;width: 100%;padding: 13px 15px;font-size: 18px;margin-bottom: 20px;}
	div.js-ticket-post-reply-box{margin-bottom: 20px;}
	div.js-ticket-attachments-wrp{display: inline-block;width: calc(100% - 40px); margin:0px 20px;padding: 15px 0px 10px 0px;}
	div.js-ticket-attachments-wrp div.js_ticketattachment{display: inline-block;width: calc(100% / 2 - 10px);padding: 5px 5px;margin: 0px 5px 10px;float:left;line-height: initial;}
	div.js-ticket-attachments-wrp div.js_ticketattachment span.js_ticketattachment_fname {display: inline-block;padding: 5px 0;width: 60%;height: 25px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;}
	div.js-ticket-attachments-wrp div.js_ticketattachment span.js-ticket-download-file-title{padding: 4px 0px;display: inline-block;float: left;width: 55%;overflow: hidden;height: 36px;text-overflow: ellipsis;white-space: nowrap;}
	div.js-ticket-attachments-wrp div.js_ticketattachment a.js-download-button{display: inline-block;width: auto;padding: 3px 3px;text-align: center;float: right;text-decoration: none !important;margin: 0 2px;}
	div.js-ticket-attachments-wrp div.js_ticketattachment a.js-download-button img.js-ticket-download-img{vertical-align:unset;}
	div.js-ticket-attachments-wrp div.js_ticketattachment a.js-download-button img {height: 23px;}
	div.js-ticket-attachments-wrp a.js-all-download-button{display: inline-block;padding: 9px 5px;text-align: center;min-width: 145px;margin-left: 5px;text-decoration: none;line-height: initial;}
	div.js-ticket-attachments-wrp a.js-all-download-button img.js-ticket-all-download-img{vertical-align: baseline;margin-right: 5px;}
	div.js-ticket-edit-options-wrp{float: left;width: calc(100% - 40px);padding: 15px 0px;margin: 5px 20px 0px;}
	div.js-ticket-edit-options-wrp a.js-button{display: inline-block;width:auto;padding: 5px;margin-right: 5px;}
	div.js-ticket-edit-options-wrp a.js-button img{display: inline-block;max-width:100%;height:auto;}
	div.js-ticket-edit-options-wrp .js-ticket-thread-time {display: inline-block;}
	div.js-ticket-field-value p {margin: 0px;line-height: 30px;}
	div.js-ticket-time-stamp-wrp{float: left;width: calc(100% - 40px);margin: 5px 20px 0px;}
	div.js-ticket-time-stamp-wrp span.js-ticket-ticket-created-date{display: inline-block;float: left;padding: 10px 10px;font-size: 14px;}
	/* Post Reply Section */
	div.js-ticket-reply-forms-wrapper {float:left;width: 100%;}
	div.js-ticket-reply-forms-wrapper div.js-ticket-reply-forms-heading{display: inline-block;width: 100%;padding: 13px 15px;font-size: 18px;margin-bottom: 20px;}
	div.js-ticket-reply-forms-wrapper div.js-ticket-post-reply{display: inline-block;width: 100%;}
	div.js-ticket-reply-forms-wrapper div.js-ticket-post-reply div.js-ticket-reply-field-wrp{display: inline-block;width: 100%;}
	div.js-ticket-reply-attachments{display: inline-block;width: 100%;margin-bottom: 20px;}
	div.js-ticket-reply-attachments div.js-attachment-field-title{display: inline-block;width: 100%;padding: 15px 0px;}
	div.js-ticket-reply-attachments div.js-attachment-field{display: inline-block;width: 100%;}
	div.tk_attachment_value_wrapperform{float: left;width:100%;padding:0px 0px;}
	div.tk_attachment_value_wrapperform span.tk_attachment_value_text{float: left;width: calc(100% / 2 - 10px);padding: 5px 5px;margin: 5px 5px;position: relative;}
	div.tk_attachment_value_wrapperform span.tk_attachment_value_text input.js-attachment-inputbox{width: 100%;max-width: 100%;max-height:100%;}
	span.tk_attachment_value_text span.tk_attachment_remove{background: url("'.JSST_PLUGIN_URL.'includes/images/close.png") no-repeat;background-size: 100% 100%;position: absolute;width: 25px;height: 25px;top: 12px;right:6px;cursor: pointer;}
	span.tk_attachments_configform{display: inline-block;float:left;line-height: 25px;margin-top: 10px;width: 100%; font-size: 14px;}
	span.tk_attachments_addform{position: relative;display: inline-block;padding: 8px 10px;cursor: pointer;margin-top: 10px;min-width: 120px;text-align: center;line-height: initial;}
	div.js-ticket-closeonreply-wrp{float: left;width: 100%; margin-bottom: 10px;}
	div.js-ticket-closeonreply-wrp div.js-ticket-closeonreply-title{float: left;width: 100%;margin-bottom: 10px;}
	div.js-ticket-closeonreply-wrp div.js-form-title-position-reletive-left{width: 50%;padding: 10px;float: left;}
	div.js-ticket-reply-form-button-wrp{float: left;width: 100%;text-align: center;padding: 20px 0px 0px;margin-top: 40px;}
	div.js-ticket-reply-form-button-wrp input.js-ticket-save-button{min-width:150px;padding: 15px 20px;border-radius: 0px;line-height: unset;line-height: initial;}
	div.js-ticket-reply-form-button-wrp a.js-ticket-cancel-button{min-width:150px;padding: 14px 20px;border-radius: 0px;display: inline-block;line-height: initial;}
	div.replyFormStatus{width: 50%;padding: 10px;}
	div.replyFormStatus{width: 50%;padding: 10px;}
	/* Tabs Section */
	div.js-ticket-tabs-wrapper{float: left;width: 100%;}
	div.js-ticket-tabs-wrapper ul.js-ticket-ul-style{float: left;width: 100%;list-style: none;padding: 10px 0px 0px;}
	div.js-ticket-tabs-wrapper li.js-ticket-li-style{display: inline-block;float: left;margin-right:10px;border-bottom: 0;}
	div.js-ticket-tabs-wrapper li.js-ticket-li-style a.js-ticket-tab-link{display: inline-block;padding: 15px 20px;text-decoration: none;outline:0;width: 100%;}
	div.js-ticket-tabs-wrapper li.js-ticket-li-style a.js-ticket-tab-link img.js-ticket-tab-img{}
	div.js-ticket-tabs-wrapper div.js-ticket-inner-tab{float: left;width: 100%;}
	div.js-ticket-inner-tab div.js-ticket-post-reply-wrp{float: left;width: 100%;}
	div.js-ticket-premade-msg-wrp{float: left;width: 100%;margin-top: 20px;}
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-title{float: left;width: 100%;margin-bottom: 7px;}
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp{float: left;width: 100%;}
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select.js-ticket-premade-select{display: inline-block;width: 50%;float: left;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat #eee;padding: 10px;line-height: initial;height: 50px;}
	span.js-ticket-apend-radio-btn{display: inline-block;float: left;width: auto;padding: 10px;margin-left: 5px;height: 50px;}
	span.js-ticket-apend-radio-btn input#append_premade1display{vertical-align: middle;}
	label#forappend_premade{display: inline-block; margin: 0px;}
	div.js-ticket-text-editor-wrp{display: inline-block;float: left;width: 100%;margin-top: 20px;}
	div.js-ticket-append-signature-wrp{float: left;width: 100%;margin-bottom: 20px;}
	div.js-ticket-append-signature-wrp.js-ticket-append-signature-wrp-full-width{width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-append-field-title{float: left;width: 100%;margin-bottom: 15px;}
	div.js-ticket-append-signature-wrp div.js-ticket-append-field-wrp{float: left;width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box{float: left;margin: 0px 5px;padding: 11px;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box.js-ticket-signature-radio-box-full-width{width: 100%;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box input#ownsignature1{margin-right: 5px; vertical-align: middle; }
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box label#forownsignature{margin: 0px;display: inline-block;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box input#departmentsignature1{margin-right: 5px; vertical-align: middle;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box label#fordepartmentsignature{margin: 0px;display: inline-block;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box label#forcanappendsignature{margin: 0px;display: inline-block;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box input#nonesignature1{margin-right: 5px; vertical-align: middle;}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box label#fornonesignature{margin: 0px;display: inline-block;}
	div.js-ticket-assigntome-wrp{float: left;width: calc(100% / 2);margin-bottom: 20px;}
	div.js-ticket-assigntome-wrp div.js-ticket-assigntome-field-title{float: left;width: 100%;margin-bottom: 15px;}
	div.js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp{float: left;width: 100%;padding: 11px 10px;}
	div.js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp input#assigntome1{margin-right: 5px; vertical-align: middle;}
	div.js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp label#forassigntome{margin: 0px;display: inline-block;}
	div.js-ticket-internalnote-wrp{float: left;width: 100%;margin-top: 20px;}
	div.js-ticket-internalnote-wrp div.js-ticket-internalnote-field-title{float: left;width: 100%;margin-bottom: 7px;}
	div.js-ticket-internalnote-wrp div.js-ticket-internalnote-field-wrp{float: left;width: 100%;}
	div.js-ticket-internalnote-wrp div.js-ticket-internalnote-field-wrp input.js-ticket-internalnote-input{border-radius: 0px;width:100%;}
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#departmentid{display: inline-block;width: 100%;float: left;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 96% / 4% no-repeat !important;}
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#staffid{display: inline-block;width: 100%;float: left;border-radius: 0px;background: url('.JSST_PLUGIN_URL.'includes/images/selecticon.png) 98% / 2% no-repeat #eee;}
	/* Pop up Sections */
	div#userpopupblack{background: rgba(0,0,0,0.5);position: fixed;width: 100%;height: 100%;top:0px;left:0px;z-index: 9989;}
	div.js-ticket-popup-row{float: left;width: 100%;margin: 0;}
	form#userpopupsearch{margin-bottom: 0px;}
	form#userpopupsearch div.search-center div.js-search-value{padding: 0 5px;}
	form#userpopupsearch div.search-center div.js-search-value input{min-height: 28px;}
	form#userpopupsearch div.search-center div.js-search-value-button{padding: 0;}
	form#userpopupsearch div.search-center div.js-search-value-button div.js-button{padding: 0 5px; width: 50%; float: left; display: inline-block;}
	form#userpopupsearch{margin-bottom: 10px;float: left;width: 100%;}
	form#userpopupsearch div.search-center{width:99%;margin-left:4px;font-size:15px;float:left;font-weight: bold;}
	form#userpopupsearch div.search-center-history{width:100%;font-size:17px;float:left;padding: 20px 10px; font-weight: bold;}
	form#userpopupsearch div.search-center input{width: 100% !important;padding: 17px 15px;}
	form#userpopupsearch div.search-center-heading{padding:10px 0px 10px 10px;margin-bottom: 10px;}
	form#userpopupsearch div.search-center span.close{position: absolute;top:10px;right: 10px;background-image:url('.JSST_PLUGIN_URL.'includes/images/ticketdetailicon/popup-close.png);background-size: 100%;width:20px;height: 20px;opacity: 1;}
	form#userpopupsearch div.search-center-history span.close-history{position: absolute;top: 22px;right: 16px;background:url('.JSST_PLUGIN_URL.'includes/images/close-icon-white.png) no-repeat;background-size: 100%;width:25px;height: 25px;cursor: pointer;}
	div#usercredentailspopup div.js-ticket-usercredentails-header span.close-credentails{position: absolute;top: 22px;right: 16px;background:url('.JSST_PLUGIN_URL.'includes/images/close-icon-white.png) no-repeat;background-size: 100%;width:25px;height: 25px;cursor: pointer;}
	div#userpopup{position: fixed;top:50%;left:50%;width:60%; max-height: 50%; padding-top:0px;z-index: 99999;overflow-y: auto; overflow-x: hidden;transform: translate(-50%,-50%);}
	.js-ticket-textalign-center{text-align: center;}
	div.jsst-popup-background{background: rgba(0,0,0,0.5);position: fixed;width: 100%;height: 100%;top:0px;left:0px;z-index: 9989;}
	div.jsst-popup-wrapper{position: fixed;top:50%;left:50%;width:65%;z-index: 1000000;overflow-y: auto; overflow-x: hidden;display: inline-block;max-height:60%;transform: translate(-50%,-50%);}
	div.jsst-merge-popup-wrapper{width:75%;max-height:65%;}
	div.jsst-popup-header{width:100%;font-size:17px;float:left;padding: 20px 10px; font-weight: bold;}
	div.popup-header-close-img{position: absolute;top:25px;right: 25px;background-image:url('.JSST_PLUGIN_URL.'includes/images/close-icon-white.png);background-size: 100%;width:25px;height: 25px;opacity: 1;cursor: pointer;}
	img.popup-header-close-img{position: absolute;top:25px;right: 25px;background-image:url('.JSST_PLUGIN_URL.'includes/images/close-icon-white.png);background-size: 100%;width:25px;height: 25px;opacity: 1;cursor: pointer;}
	div.jsst-popup-wrapper input{margin-bottom:0px; }
	div.jsst-popup-wrapper input#edited_time{font-size: 16px;width: 100%;}
	div.jsst-popup-wrapper input#systemtime{width: 100%;}
	div.jsst-popup-wrapper textarea{width: 100%;}
	div.jsst-popup-wrapper div.js-form-button-wrapper{text-align: center;border-top: 1px solid #e0dce0;width: 94%;margin: 0px 3%;margin-top: 20px;}
	div.jsst-popup-wrapper div.js-form-button-wrapper input.button{display: inline-block;float: none;padding: 5px 20px;border-radius: 2px;margin-top: 15px;margin-bottom: 15px;min-width: 100px;}
	div.jsst-popup-wrapper div.js-form-button-wrapper input.js-merge-cancel-btn{padding: 16px 10px;min-width: 120px;border-radius: unset;}
	div.jsst-popup-wrapper div.js-form-button-wrapper input.js-merge-save-btn{padding: 16px 10px;min-width: 120px;border-radius: unset;}
	div.js-ticket-edit-form-wrp{float: left;width: 100%; padding: 20px 20px;}
	div.js-ticket-edit-form-wrp div.js-ticket-form-field-wrp{float: left;width: 100%;}
	div.js-ticket-edit-field-title{float: left;width: 100%;margin-bottom: 5px;}
	div.js-ticket-edit-field-wrp{float: left;width: 100%;margin-bottom: 10px;}
	div.js-ticket-popup-search-wrp{float: left;width: 100%;padding: 30px 5px 15px;}
	div.js-ticket-search-top{float: left;width: 100%;}
	div.js-ticket-search-top div.js-ticket-search-left{float: left;width: 70%;}
	div.js-ticket-search-top div.js-ticket-search-left div.js-ticket-search-fields-wrp{float: left;width: 100%;padding: 0px}
	div.js-ticket-search-top div.js-ticket-search-left div.js-ticket-search-fields-wrp input.js-ticket-search-input-fields{float: left;width: calc(100% / 3 - 10px);margin:0px 5px;padding: 11px 5px;border-radius: 0px}
	div.js-ticket-search-top div.js-ticket-search-right{float: left;width: 30%;}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp{float: left;width: 100%;}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-search-btn{width: calc(100% / 2 - 5px);padding: 17px;border-radius: 0px;}
	div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-reset-btn{width: calc(100% / 2 - 5px);padding: 17px;border-radius: 0px;}
	div.jsst_userlink.selected
	div.js-ticket-detail-wrapper div.js-ticket-openclosed{font-size:24px;text-align: center; line-height: 60px;height: 60px;white-space: nowrap;padding-left: 5px;padding-right: 5px; overflow: hidden;text-overflow: ellipsis}
	div.js-ticket-detail-wrapper div.js-ticket-topbar{padding: 0px 0px 10px 0px;margin: 10px 5px 15px 5px;}
	div.js-ticket-detail-wrapper div.js-ticket-topbar div.js-openclosed{padding:0px;}
	div.js-ticket-detail-wrapper div.js-ticket-topbar div.js-last-left{padding:0px 5px;}
	div.js-ticket-detail-wrapper div.js-ticket-topbar div.js-last-left div.js-ticket-value{padding:0px;}
	div.js-ticket-detail-wrapper div.js-mid-ticketdetail-part{padding:0px 5px;}
	div.js-ticket-detail-wrapper div.js-ticket-middlebar{margin: 2px 0px;}
	div.js-ticket-detail-wrapper div.js-margin-bottom{margin-bottom: 10px;}
	div.js-ticket-detail-wrapper div.js-button-margin{margin-top: 15px;}
	div.js-ticket-detail-wrapper div.js-ticket-moredetail{margin-bottom: 10px;display:inline-block;}
	div.js-ticket-detail-wrapper div.js-ticket-moredetail div.js-ticket-data-value{margin-bottom: 10px; min-height: 22px;}
	div.js-ticket-detail-wrapper div.js-ticket-requester{margin:0px 15px;font-size: 16px;padding-bottom: 5px;margin-bottom: 10px;}
	div.js-ticket-detail-wrapper div.js-ticket-bottombar{margin:10px;}
	div.js-ticket-detail-wrapper div.js-ticket-bottombar img{width:20px;height:20px;}
	div.js-ticket-detail-wrapper div.js-ticket-bottombar img.js-showdetail{float:left;margin-right:5px;-webkit-transform: rotate(180deg);-moz-transform: rotate(180deg);-o-transform: rotate(180deg);-ms-transform: rotate(180deg);transition:all .3s;}
	div.js-ticket-detail-wrapper div.js-ticket-bottombar img.js-hidedetail{float:left;margin-right:5px;-webkit-transform: rotate(0deg);-moz-transform: rotate(0deg);-o-transform: rotate(0deg);-ms-transform: rotate(0deg);transition:all .3s;}
	label#forcloseonreply{display: inline-block;margin: 0px;}
	#records{float: left;width: 100%;padding: 0px 10px;}
	th:first-child, td:first-child{padding-left: 10px !important;}

	/*Merge Form Css*/
	div.js-ticket-merge-ticket-wrapper{float: left;width: 100%;padding: 20px;}
	div.js-merge-form-wrapper{padding-bottom: 20px;border-bottom: 1px solid lightgrey;}
	div.js-tickets-list-wrp{float: left;width: 100%;padding-top: 10px;padding-bottom: 25px;}
	div.js-merge-form-title{padding:0px; }
	div.js-merge-padding{padding: 15px;}
	div.js-merge-ticket{float: left;width: 100%;padding: 5px;}
	span.js-bold-text{font-weight: bold;display: inline-block;}
	div.js-bold-text{font-weight: bold;display: inline-block;}
	div.js-merge-form-wrp{float: left;width: 73%;}
	div.js-merge-form-value{float: left;width:calc(100% / 2 - 5px);padding: 0;margin-right: 5px;margin-top: 10px;}
	div.js-merge-form-value input.inputbox{width: 100%;padding: 11px !important;}
	div.js-merge-form-btn-wrp{float: left;width: 26%;}
	div.js-view-tickets{float: left;width: calc(100% - 30px);margin-top: 23px;margin-left: 15px; margin-right: 15px; padding: 10px 0px 0px 0px;}
	div.js-view-last-tickets{border-top:0px !important; margin-top: 0px !important; padding-top: 0px !important;}
	span.js-merge-btn{float: left;display: inline-block;width:calc(100% / 2 - 5px);margin-right: 5px;}
	input.js-merge-button{padding: 13px 5px !important; width: 100% !important;margin: 10px auto;line-height: unset !important;border-radius: unset !important;}
	div.js-recently-viewed{width:100%; float: left;}
	div.js-margin{margin-bottom: 20px;margin-top: 10px;}
	div.js-merge-ticket-overlay{position: relative;}
	div.js-merge-ticket-overlay:hover .js-over-lay{opacity: 1;}
	div.js-merge-ticket-overlay .js-over-lay{height:100%;background:rgba(0,0,0,.5);text-align:center;padding:0;opacity:0;-webkit-transition: opacity .25s ease;-moz-transition: opacity .25s ease;position: absolute;top: 0;left: 0;width: 100%;}
	a.js-merge-btn{position: absolute;top: 50%; left: 50%;transform: translate(-50%,-50%);display: inline-block;padding: 10px 10px;min-width: 110px;text-decoration: none;}
	input.js-merge-field{padding: 5px 5px !important;border-radius: 0px !important;}
	span.js-edit-msg-heading{display: inline-block;float: left;width: auto;font-size: 13px;margin: 5px auto; }
	textarea.js-merge-field{border-radius: 0;padding-left: 5px;}
	span.js-heading{display: inline-block;float: left;width: 100%;font-weight: bold;margin-top: 25px;padding-top: 15px;}
	span.js-heading-text{margin-top: 0px !important; border-top: 0px !important;padding-top: 0px !important;}
	span.js-sub-heading{display: inline-block;width: 100%;float: left;font-size: 12px;margin: 5px auto;}
	div.js-form-button-wrapper-merge{border-top:none !important ;margin-top: 0px !important;float: left;}
	div.jsst_userpages{border: 1px solid #f1f1fc;width: calc(100% - 30px);display: inline-block;text-align: center;vertical-align: middle;margin: 0 15px;}
	.jsst_userlink{display: inline-block;padding: 5px 15px; margin-right: 5px; background: -moz-linear-gradient(top, #ffffff 0%, #f2f2f2 100%);background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(100%,#f2f2f2));background: -webkit-linear-gradient(top, #ffffff 0%,#f2f2f2 100%);background: -o-linear-gradient(top, #ffffff 0%,#f2f2f2 100%);background: -ms-linear-gradient(top, #ffffff 0%,#f2f2f2 100%);background: linear-gradient(to bottom, #ffffff 0%,#f2f2f2 100%);filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#ffffff", endColorstr="#f2f2f2",GradientType=0 );color: #373435;border: 1px solid #b8b8b8;}
	.js-text-align-right{text-align: right;float: right;margin-right: 0px;}
	.js-text-align-left{text-align: left;float: left;}
	.js-no-padding{padding-right: 0 !important;padding-left: 0!important;}
	div.js-ticket-wrapper{margin:8px 0px;padding-left: 0px;padding-right: 0px;}
	div.js-ticket-wrapper div.js-ticket-pic{margin: 10px 0px;padding: 0px;padding: 0px 10px;text-align: center;position: relative;float: left;width: 16%;height: 96px;}
	div.js-ticket-wrapper div.js-ticket-pic img {width: auto;max-width: 96px;max-height: 96px;height: auto;position: absolute;top: 0;left: 0;right: 0;bottom: 0;margin: auto;}
	div.js-ticket-wrapper div.js-ticket-data{position: relative;padding: 23px 0px;}
	div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status{position: absolute;top:41%;right:2%;padding: 10px 10px;border-radius: 20px;font-size: 10px;line-height: 1;font-weight: bold;}
	div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status img.ticketstatusimage{position: absolute;top:0px;}
	div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status img.ticketstatusimage.one{left:-25px;}
	div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status img.ticketstatusimage.two{left:-50px;}
	div.js-ticket-wrapper div.js-ticket-data1{margin:0px 0px;padding: 17px 15px;}
	div.js-ticket-wrapper div.js-ticket-data1 div.js-row {float: left;width: 100%;}
	div.js-ticket-wrapper div.js-ticket-bottom-line{position:absolute;display: inline-block;width:90%;margin:0 5%;height:1px;left:0px;bottom: 0px;}
	div.js-ticket-wrapper div.js-ticket-toparea{position: relative;padding:0px;}
	div.js-ticket-wrapper div.js-ticket-bottom-data-part{padding: 0px;margin-bottom: 10px;}
	div.js-ticket-wrapper div.js-ticket-bottom-data-part a.button{float:right;margin-left: 10px;padding:0px 20px;line-height: 30px;height:32px;}
	div.js-ticket-wrapper div.js-ticket-bottom-data-part a.button img{height:16px;margin-right:5px;}
	span.js-ticket-wrapper-textcolor{display: inline-block;padding: 5px 10px;min-width: 85px;text-align: center;}

	/* Timer CSS */
	div.jsst-ticket-detail-timer-wrapper{display: inline-block;width: 100%;line-height: initial;}
	div.jsst-ticket-detail-timer-wrapper div.timer-left{;float: left;font-weight: bold;padding: 20px;}
	div.jsst-ticket-detail-timer-wrapper div.timer-right{float: right;}
	div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-total-time{padding: 20px;float: left;font-size: 15px;}
	div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer{padding:20px 5px;font-weight: bold;float: left;min-width: 120px;text-align: center;font-size:17px; }
	div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons{float: left;padding: 10px 15px;}
	div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button{float: left;display: inline-block;margin-left: 5px;cursor: pointer;}
	div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button img{padding: 5px;display: inline-block;float: left;}

	select ::-ms-expand {display:none !important;}
	select{-webkit-appearance:none !important;}

	div#usercredentailspopup{}
	div#usercredentailspopup{position: fixed;top:50%;left:50%;width:60%;z-index: 99999;overflow-x: hidden;text-align: left;transform: translate(-50%,-50%);}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp{height:450px;overflow-y:auto;}
	div#usercredentailspopup div.js-ticket-usercredentails-header{float: left;width: 100%;padding: 20px 10px;font-weight: bold;font-size: 18px;position: relative;}
	div#usercredentailspopup div.js-ticket-usercredentails-header span.close-history{position: absolute;top: 22px;right: 16px;background:url('.JSST_PLUGIN_URL.'includes/images/ticketdetailicon/popup-close.png) no-repeat;background-size: 100%;width:20px;height: 20px;cursor: pointer;float: left;width: 100%;padding: 20px 5px;font-weight: bold;font-size: 18px;}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp{float: left;width: 100%;padding: 30px;}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp div.js-ticket-select-usercredentails{float: left;width: 100%;padding: 10px 10px 0px;}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp div.js-ticket-select-usercredentails label{float: left;width: 100%;padding-bottom: 5px}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp div.js-ticket-select-usercredentails input.inputbox{width: 100%;display:inline-block;}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp div.js-ticket-select-usercredentails textarea.inputbox{width: 100%;display:inline-block;}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp{width: calc(100% - 20px);float: left;text-align: center; padding: 15px 0px;margin: 0px 10px;}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp input.js-ticket-usercredentails-save{min-width:150px; padding: 15px 5px;}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp input.js-ticket-usercredentails-cancel{min-width:150px; padding: 15px 5px;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp{display:inline-block;width:100%;padding:30px;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single{display:inline-block;width:100%;padding: 15px 10px 20px;margin-bottom: 15px;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-title{display:inline-block;width:100%;font-weight:bold;color: '.$color2.';padding-bottom: 15px;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data{display:inline-block;width:50%;float:left;padding-bottom:10px;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data.js-ticket-usercredentail-data-full-width{display:inline-block;width:100%;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data .js-ticket-usercredentail-data-label{display:inline-block;color: '.$color2.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data .js-ticket-usercredentail-data-value{display:inline-block;color: '.$color4.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data-button-wrap{display:inline-block;width:100%;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data-button-wrap .js-ticket-usercredentail-data-button-edit {display:inline-block;padding:10px 15px;border-radius:0px;min-width: 120px;background: '.$color1.';color: '.$color7.';border: 1px solid '.$color5.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data-button-wrap .js-ticket-usercredentail-data-button-edit:hover {border-color: '.$color2.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data-button-wrap .js-ticket-usercredentail-data-button-delete {display:inline-block;padding:10px 15px;border-radius:0px;min-width: 120px;background: '.$color2.';color: '.$color7.';border: 1px solid '.$color5.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single .js-ticket-usercredentail-data-button-wrap .js-ticket-usercredentail-data-button-delete:hover {border-color: '.$color1.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentail-data-add-new-button-wrap{display:inline-block;width:100%;margin-top:20px;}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentail-data-add-new-button-wrap .js-ticket-usercredentail-data-add-new-button {display:inline-block;padding:10px 15px;border-radius:0px;background: '.$color2.';border: 1px solid '.$color5.';color: '.$color7.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentail-data-add-new-button-wrap .js-ticket-usercredentail-data-add-new-button:hover {border-color: '.$color1.';}
	div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single{background:  #fff;border: 1px solid  '.$color5.';}
	div#usercredentailspopup{background:  #fff;border: 1px solid  '.$color5.';}
	div#usercredentailspopup div.js-ticket-usercredentails-header{background:  '.$color1.';color: '.$color7.';}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp  div.js-ticket-select-usercredentails label {color: '.$color2.';}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp div.js-ticket-select-usercredentails input.inputbox{background-color: #fff;border:1px solid  '.$color5.';color: '.$color4.';}
	div#usercredentailspopup div.js-ticket-usercredentails-fields-wrp div.js-ticket-select-usercredentails textarea.inputbox{background-color: #fff;border:1px solid  '.$color5.';color: '.$color4.';}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp{border-top:2px solid  '.$color2.';}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp input.js-ticket-usercredentails-save{background-color: '.$color1.';color: '.$color7.';border:1px solid  '.$color5.';}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp input.js-ticket-usercredentails-save:hover {border-color: '.$color2.';}
	div#usercredentailspopup div.js-ticket-usercreden1tails-btn-wrp input.js-ticket-usercredentails-cancel{background-color: '.$color2.';color: '.$color7.';border:1px solid  '.$color5.';}
	div#usercredentailspopup div.js-ticket-usercreden1tails-btn-wrp input.js-ticket-usercredentails-cancel:hover{border-color: '.$color1.';}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp input.js-ticket-usercredentails-cancel{background-color: '.$color2.';color: '.$color7.';border:1px solid  '.$color5.';}
	div#usercredentailspopup div.js-ticket-usercredentails-btn-wrp input.js-ticket-usercredentails-cancel:hover{border-color: '.$color1.';}

	/*new css*/
	.js-tkt-det-left {float: left;width: 70%;padding-right: 30px;}
	.js-tkt-det-cnt {float: left;width: 100%;margin-bottom: 20px;}
	.js-tkt-det-user {float: left;width: 100%;padding: 15px;}
	.js-tkt-det-user .js-tkt-det-user-image {float: left;width: 100px;height: 80px;text-align: center;border-radius: 100%;position: relative;}
	.js-tkt-det-user .js-tkt-det-user-image img {border-radius: 100%;display: inline-block;margin: 0 auto;position: absolute;top: 0;right: 0;bottom: 0;left: 0;max-width: 100%;max-height: 100%;}
	.js-tkt-det-user .js-tkt-det-user-cnt {float: left;width: calc(100% - 100px);padding: 0 0 0 10px;}
	.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data {float: left;width: 100%;padding-bottom: 8px;line-height: initial;}
	.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data:last-child {padding-bottom: 0;}
	.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.name {font-size: 15px;text-decoration: underline;text-transform: capitalize;line-height: initial;}
	.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.subject {font-weight: bold;}
	.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.agent-email{word-break: break-all;}
	.js-tkt-det-other-tkt {float: left;width: 100%;padding: 15px;line-height: initial;}
	.js-tkt-det-other-tkt .js-tkt-det-other-tkt .js-tkt-det-other-tkt-btn {display: inline-block;text-decoration: underline;}
	.js-tkt-det-tkt-msg {float: left;width: 100%;padding: 15px;line-height: 1.8;}
	.js-tkt-det-tkt-msg p {line-height: 1.8;}
	.js-tkt-det-actn-btn-wrp {float: left;width: 100%;padding: 15px;}
	.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn {float: left;padding: 5px;margin: 2px;text-decoration: none !important;line-height: initial;}
	.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn img{display: inline-block;max-width: 100%;height: auto;}
	.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn span {display: inline-block;vertical-align: middle;}
	.js-tkt-det-right {float: left;width: 30%;}
	.js-tkt-det-right .js-tkt-det-cnt {padding: 15px;}
	.js-tkt-det-hdg {float: left;width: 100%;padding-bottom: 15px;}
	.js-tkt-det-hdg .js-tkt-det-hdg-txt {float: left;font-size: 20px;line-height: initial;}
	.js-tkt-det-hdg .js-tkt-det-hdg-btn {float: right;line-height: initial;}
	.js-tkt-det-status {float: left;width: 100%;padding: 25px 10px;margin-bottom: 15px;text-align: center;font-size: 24px;font-weight: bold;line-height: initial;}
	.js-tkt-det-info-cnt {float: left;width: 100%;}
	.js-tkt-det-info-data {float: left;width: 100%;padding-bottom: 10px;line-height: initial;}
	.js-tkt-det-info-data .js-tkt-det-info-tit {float: left;margin-right: 8px;}
	.js-tkt-det-info-data .js-tkt-det-info-val {float: left;}
	.js-tkt-det-copy-id {display: inline-block;margin-left: 3px;text-decoration: underline !important;cursor:pointer;}
	.js-tkt-det-tkt-prty-txt {float: left;width: 100%;text-align: center;padding: 15px;color: #fff;margin-bottom: 10px;font-size: 18px;line-height: initial;}
	.js-tkt-det-tkt-asgn-cnt .js-tkt-det-hdg .js-tkt-det-hdg-txt {font-size: 15px;}
	.js-tkt-det-tkt-asgn-cnt .js-tkt-det-user {padding: 10px 0;}
	.js-tkt-det-trsfer-dep {float: left;width: 100%;padding: 15px 0 7px;}
	.js-tkt-det-trsfer-dep .js-tkt-det-trsfer-dep-txt {float: left;width: 75%;}
	.js-tkt-det-trsfer-dep .js-tkt-det-trsfer-dep-txt .js-tkt-det-trsfer-dep-txt-tit {display: inline-block;}
	.js-tkt-det-trsfer-dep .js-tkt-det-hdg-btn {float: right;text-align: right;}
	.js-tkt-det-right .js-tkt-det-user-tkts {padding: 0;}
	.js-tkt-det-user-tkts .js-tkt-det-hdg {padding: 15px;}
	.js-tkt-det-usr-tkt-list {float: left;width: 100%;}
	.js-tkt-det-usr-tkt-list .js-tkt-det-user:last-child {border-bottom: 0;padding-bottom: 20px;}
	.js-tkt-det-usr-tkt-list .js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.name {text-decoration: none;font-size: inherit;}
	.js-tkt-det-usr-tkt-list .js-tkt-det-user .js-tkt-det-user-image {height: 60px;width: 80px;}
	.js-tkt-det-usr-tkt-list .js-tkt-det-user .js-tkt-det-user-image img {height: 60px;width: 60px;}
	.js-tkt-det-usr-tkt-list .js-tkt-det-user .js-tkt-det-user-cnt {width: calc(100% - 80px);}
	.js-tkt-det-usr-tkt-list .js-tkt-det-prty {float: left;padding: 5px 10px;margin-right: 3px;font-size: 14px;font-weight: bold;text-transform: uppercase;margin-bottom: 5px;}
	.js-tkt-det-usr-tkt-list .js-tkt-det-status {float: left;padding: 5px 10px;margin: 0;width: auto;font-size: 14px;font-weight: normal;}
	.js-tkt-det-title {float: left;width: 100%;padding: 20px;font-size: 20px;margin-bottom: 20px;line-height: initial;}
	.js-ticket-thread {float: left;width: 100%;padding: 20px;margin-bottom: 20px;}
	.js-ticket-thread .js-ticket-thread-image {float: left;width: 100px;height: 80px;border-radius: 100%;text-align: center;position: relative;}
	.js-ticket-thread .js-ticket-thread-image img {border-radius: 100%;height: 80px;width: 80px;display: inline-block;margin: 0 auto;position: absolute;top: 0;right: 0;bottom: 0;left: 0;max-width: 100%;max-height: 100%;}
	.js-ticket-thread .js-ticket-thread-cnt {float: left;width: calc(100% - 100px);padding: 10px 0 0 15px;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data {float: left;width: 100%;padding-bottom: 8px;line-height: initial;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data:last-child {padding-bottom: 0;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-person {float: left;text-decoration:underline;text-transform: capitalize;font-size: 15px;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-note {float: left;text-transform: capitalize;padding-top: 5px;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-email {float: left;padding-top: 5px;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-date {float: right;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-time {float: right;margin-left: 10px;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data.note-msg {line-height: 1.8;}
	.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data.note-msg p {line-height: 1.8;margin: 10px 0 20px;}
	.js-ticket-thread .js-ticket-thread-cnt-btm {float: left;width: 100%;padding: 10px 0 0;}
	.js-ticket-thread .js-ticket-thread-cnt-btm .js-ticket-thread-date {float: left;padding: 10px 0;line-height: initial;}
	.js-ticket-thread .js-ticket-thread-actions {float: right;}
	.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn {display: inline-block;margin: 0 2px;padding: 5px;text-decoration: none;line-height: initial;}
	.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn span {display: inline-block;margin-left: 2px;vertical-align: middle;line-height: initial;}
	.js-tkt-det-timer-wrp {float: left;width: 100%;padding: 15px 0;}
	.js-tkt-det-timer-wrp .timer {float: left;width: 100%;font-size: 50px;line-height: initial;}
	.js-tkt-det-timer-wrp .timer .timer-box {display: inline-block;width: calc(100% / 3 - 24px);padding: 20px 15px;text-align: center;}
	.js-tkt-det-timer-wrp .timer-buttons {float: left;width: 100%;text-align: center;padding: 20px 0;}
	.js-tkt-det-timer-wrp .timer-buttons .timer-button {display: inline-block;cursor: pointer;padding: 9px 18px;}
	.js-tkt-det-timer-wrp .timer-total-time  {float: left;width: 100%;}
	.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-title {float: left;margin-right: 5px;}
	.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-value {float: left;width: 100%;font-size: 40px;line-height: initial;}
	.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-value .timer-box {display: inline-block;width: calc(100% / 3 - 10px);padding: 10px;text-align: center;float: left;margin-right: 15px;position: relative;}
	.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-value .timer-box:last-child {margin-right: 0;}
	.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-value .timer-box::after {content: \':\';display: block;position: absolute;top: 10px;right: -14px;}
	.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-value .timer-box:last-child::after {display: none;}
	.js-ticket-thread-add-btn {float: left;width: 100%;margin-bottom: 20px;}
	.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link {display: inline-block;text-decoration: none !important;padding: 7px 12px;line-height: initial;}
	.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link img {margin-right: 3px;display:inline-block;}
	.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link span {display: inline-block;vertical-align: middle;}
	.js-det-tkt-frm {float: left;width: 100%;}
	.jsst-ticket-detail-timer-wrapper {float: left;width: 100%;margin-bottom: 20px;}
	.jsst-ticket-detail-timer-wrapper .timer-left {float: left;font-weight: bold;padding: 20px;}
	.jsst-ticket-detail-timer-wrapper .timer-right {float: right;}
	.jsst-ticket-detail-timer-wrapper .timer-total-time {float: left;font-size: 15px;}
	.jsst-ticket-detail-timer-wrapper .timer {padding: 20px 5px;font-weight: bold;float: left;min-width: 120px;text-align: center;font-size: 17px;}
	.jsst-ticket-detail-timer-wrapper .timer-buttons {float: left;padding: 10px 15px;}
	.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button {float: left;margin-left: 5px;cursor: pointer;}
	.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button img {padding: 5px;}
	.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button img.default-hide {display: none;}
	.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button:hover img.default-hide {display: block;}
	.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button:hover img.default-show {display: none;}
	.js-det-tkt-form .js-form-wrapper {float: left;width: 100%;margin-bottom: 20px;}
	.js-det-tkt-form .js-form-wrapper .js-form-title {float: left;width: 100%;margin-bottom: 8px;}
	.js-det-tkt-form .js-form-wrapper .js-form-value {float: left;width: 100%;}
	.js-det-tkt-form .js-form-wrapper .js-form-value .js-admin-popup-input-field {display: inline-block;width: 100%;height: 45px;padding: 10px;margin: 0;box-shadow: unset;}
	.js-det-tkt-form .js-form-wrapper .js-form-value .js-admin-popup-select-field {display: inline-block;width: 100%;height: 45px;padding: 10px;margin: 0;box-shadow: unset;background-image: url(../images/selecticon.png);background-repeat: no-repeat;background-position: calc(100% - 15px);-webkit-appearance: none;-moz-appearance: none;appearance: none;background-size: 16px;}
	.js-det-tkt-form .js-form-wrapper .tk_attachment_value_wrapperform .tk_attachment_value_text {float: left;width: calc(33.33% - 5px);margin-right: 5px;}
	.js-det-tkt-form .js-form-wrapper .tk_attachments_configform {float: left;width: 100%;}
	.js-det-tkt-form .js-form-wrapper .tk_attachments_addform {display: inline-block;padding: 10px 15px;line-height: inherit;border-radius: 0;height: auto;cursor: pointer;margin: 10px 0;}
	.js-det-tkt-form .js-form-wrapper .jsst-formfield-radio-button-wrap {float: left;padding: 10px;height: 45px;margin-right: 5px;width: calc(33.33% - 5px);}
	.js-det-tkt-form .js-form-wrapper .jsst-formfield-radio-button-wrap input {margin-right: 5px;margin-top: 1px;}
	.js-det-tkt-form .js-form-wrapper .js-tkt-det-perm-msg {float: left;margin: 0 5px 5px 0;}
	.js-det-tkt-form .js-form-wrapper .js-tkt-det-perm-msg a {display: inline-block;width: 100%;text-decoration: none;padding: 10px;}
	#jsst-note-edit-form {float: left;width: 100%;padding: 25px;}
	#jsst-note-edit-form .js-form-wrapper {margin-bottom: 15px;}
	#jsst-note-edit-form .js-form-wrapper .js-form-title {margin-bottom: 7px;}
	#jsst-note-edit-form div.js-form-button-wrapper input.button {min-width: 150px;padding: 20px 5px;line-height: initial;border-radius: 0;}


	/* ticket detail woocommerce */
	.js-tkt-wc-order-box {float: left;width: 100%;}
	.js-tkt-wc-order-box .js-tkt-wc-order-item {float: left;width: 100%;padding-bottom: 10px;}
	.js-tkt-wc-order-box .js-tkt-wc-order-item .js-tkt-wc-order-item-title {float: left;margin-right: 8px;}
	.js-tkt-wc-order-box .js-tkt-wc-order-item .js-tkt-wc-order-item-value {float: left;}

	/*ticket histort*/
	.js-ticket-history-table-wrp table {width: 100%;margin: 20px 0;}
	.js-ticket-history-table-wrp table th,
	.js-ticket-history-table-wrp table td {width: 25%;padding: 10px;}
	.js-ticket-history-table-wrp table th:last-child,
	.js-ticket-history-table-wrp table td:last-child {width: 50%;padding-left: 15px;}

';
/*Code For Colors*/
$jssupportticket_css .= '


/* Ticket Details*/
		div.js-ticket-wrapper{border:1px solid '.$color5.';box-shadow: 0 8px 6px -6px #dedddd;}
		div.js-ticket-wrapper:hover{border:1px solid '.$color2.';}
		div.js-ticket-wrapper:hover div.js-ticket-pic{border-right:1px solid '.$color2.';}
		div.js-ticket-wrapper:hover div.js-ticket-data1{border-left:1px solid '.$color2.';}
		div.js-ticket-wrapper:hover div.js-ticket-bottom-line{background:'.$color2.';}
		div.js-ticket-wrapper div.js-ticket-pic{border-right:1px solid '.$color5.';}
		div.js-ticket-wrapper div.js-ticket-data span.js-ticket-status{color:#FFFFFF;}
		div.js-ticket-wrapper div.js-ticket-data1{border-left:1px solid '.$color5.';}
		div.js-ticket-wrapper div.js-ticket-data span.js-ticket-title{color:'.$color2.';}
		div.js-ticket-wrapper div.js-ticket-data a.js-ticket-merge-ticket-title {color:'.$color4.';}
		a.js-ticket-title-anchor:hover{color:'.$color2.' !important;}
		div.js-ticket-wrapper div.js-ticket-data span.js-ticket-value{color:'.$color4.';}
		div.js-ticket-wrapper div.js-ticket-bottom-line{background:'.$color2.';}
		div.js-ticket-assigned-tome{border:1px solid '.$color5.';background-color:'.$color3.';}
		div.js-ticket-sorting span.js-ticket-sorting-link a{background:#373435;color:'.$color7.';}
		div.js-ticket-sorting span.js-ticket-sorting-link a.selected,
		div.js-ticket-sorting span.js-ticket-sorting-link a:hover{background: '.$color2.';}
	/* My Tickets $ Staff My Tickets*/

		div.js-ticket-detail-wrapper{border:1px solid  '.$color5.';}
		div.js-ticket-detail-box{border-bottom:1px solid  '.$color5.';}
		div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp{background-color:  '.$color3.';}
		div.js-ticket-detail-box div.js-ticket-detail-right{background: #fff;}
		div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp{background: #fff;}
		div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-title{color: '.$color1.';}
		div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value span.js-ticket-subject-link{color: '.$color2.';}
		div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp .js-ticket-field-value {color: '.$color4.';}
		div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp .js-ticket-field-value-t {color: '.$color2.';}
		div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp .js-ticket-field-value.name {color: '.$color1.';}
		.js-tkt-det-trsfer-dep .js-tkt-det-hdg-btn {color: '.$color1.';}
		.js-tkt-det-trsfer-dep .js-tkt-det-hdg-btn:hover {color: '.$color2.';}
		div.js-ticket-detail-right div.js-ticket-row .js-ticket-title{color: '.$color1.';}
		div.js-ticket-detail-right div.js-ticket-row .js-ticket-value span.js-ticket-subject-link{color: '.$color2.';}

		div.js-ticket-detail-right div.js-ticket-openclosed-box{color: '.$color7.';}
		div.js-ticket-detail-right div.js-ticket-right-bottom{background-color:#fef1e6;color: '.$color4.';border-top:1px solid  '.$color5.';}
		div.js-ticket-detail-wrapper div.js-ticket-action-btn-wrp div.js-ticket-btn-box{background-color:#e7ecf2;border:1px solid  '.$color5.';}
		div#userpopupforchangepriority{background:  #fff;border: 1px solid  '.$color5.';}
		div#userpopupforchangepriority div.js-ticket-priorty-header{background:  '.$color1.';color: '.$color7.';}
		div#userpopupforchangepriority div.js-ticket-priorty-fields-wrp div.js-ticket-select-priorty select#priority{background-color: '.$color3.';border:1px solid  '.$color5.';}
		div#userpopupforchangepriority div.js-ticket-priorty-fields-wrp div.js-ticket-select-priorty select#prioritytemp{background-color: #fff;border:1px solid  '.$color5.';}
		div#userpopupforchangepriority div.js-ticket-priorty-btn-wrp{border-top:2px solid  '.$color2.';}
		div#popupforagenttransfer {background:  #fff;border: 1px solid  '.$color5.';}
		div#popupfordepartmenttransfer {background:  #fff;border: 1px solid  '.$color5.';}
		div#popupforinternalnote {background: #fff;border: 1px solid  '.$color5.';}
		div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-save{background-color: '.$color1.';color: '.$color7.';border: 1px solid  '.$color2.';}
		div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-save:hover{border-color: '.$color2.';}
		div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-cancel{background-color:'.$color2.';color: '.$color7.';border: 1px solid  '.$color2.';}
		div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-cancel:hover{border-color: '.$color1.';}
		div.js-ticket-post-reply-wrapper div.js-ticket-thread-heading{background-color:#e7ecf2;border:1px solid  '.$color5.';color: '.$color4.';}
		div.js-ticket-post-reply-box{border:1px solid  '.$color5.';box-shadow: 0 0 3px 2px rgba(162, 162, 162, 0.71);background: #fff;}
		div.js-ticket-white-background{background-color: '.$color7.';}
		div.js-ticket-background{background-color: '.$color3.';}
		div.js-ticket-attachments-wrp{border-top:1px solid  '.$color5.';}
		div.js-ticket-attachments-wrp div.js_ticketattachment{border:1px solid  '.$color5.';background-color: '.$color7.';}
		div.js-ticket-attachments-wrp div.js_ticketattachment a.js-download-button{background-color: '.$color3.';color: '.$color4.';border:1px solid  '.$color5.';}
		div.js-ticket-attachments-wrp a.js-all-download-button{background-color: '.$color1.';color: '.$color7.';border:1px solid  '.$color5.';}
		div.js-ticket-attachments-wrp a.js-all-download-button:hover{border-color: '.$color2.';}
		.js-ticket-thread .js-ticket-thread-cnt-btm .js-ticket-thread-date{color: '.$color4.';}
		div.js-ticket-edit-options-wrp{border-top:1px solid  '.$color5.';}
		div.js-ticket-edit-options-wrp a.js-button{background-color: '.$color3.';border:1px solid  '.$color5.';}
		div.js-ticket-edit-options-wrp .js-ticket-thread-time {color: '.$color4.';}
		div.js-ticket-time-stamp-wrp span.js-ticket-ticket-created-date {color: '.$color4.';}
		div.js-ticket-detail-left div.js-ticket-user-name-wrp {color: '.$color2.';}
		div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button{background-color: '.$color2.';color: '.$color7.';}
		div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button:hover {background-color: '.$color1.';}
		div.jsst-ticket-detail-timer-wrapper{border:1px solid  '.$color5.';background: '.$color3.';}
		div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer{}
		div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button.selected{background:'.$color1.';border: 1px solid '.$color5.';}
		select#premadeid{background-color: #fff !important;border:1px solid  '.$color5.';}
		div.js-ticket-time-stamp-wrp{border-top:1px solid  '.$color5.';}
		/*Post Reply Section*/
			div.js-ticket-reply-forms-wrapper div.js-ticket-reply-forms-heading{background-color: '.$color2.';border: 1px solid '.$color5.';color: '.$color7.';}
			div.tk_attachment_value_wrapperform{border: 1px solid  '.$color5.';background:  #fff;}
			span.tk_attachment_value_text{border: 1px solid  '.$color5.';background-color: '.$color7.';}
			div.js-ticket-reply-form-button-wrp{border-top: 2px solid  '.$color2.';}
			span.tk_attachments_configform{color: '.$color4.'}
			div.js-ticket-reply-attachments div.js-attachment-field-title{color: '.$color2.'}
			div.js-ticket-reply-form-button-wrp input.js-ticket-save-button{background-color: '.$color1.' !important;color: '.$color7.' !important;border: 1px solid '.$color5.';}
			div.js-ticket-reply-form-button-wrp input.js-ticket-save-button:hover{border-color: '.$color2.';}
			div.js-ticket-reply-form-button-wrp a.js-ticket-cancel-button{background-color:#48484a;color: '.$color7.'}
			div.js-ticket-tabs-wrapper ul.js-ticket-ul-style{background-color:#e7ecf2;border:1px solid #DEDFE0;border-bottom:2px solid  '.$color2.'; }
			div.js-ticket-tabs-wrapper li.js-ticket-li-style{background-color: '.$color7.';border:1px}
			div.js-ticket-tabs-wrapper li.js-ticket-li-style a.js-ticket-tab-link{color: '.$color4.';}
			div.js-ticket-tabs-wrapper li.js-ticket-li-style a.js-ticket-tab-link:hover{background-color: '.$color2.';color: '.$color7.';}
			div.js-ticket-tabs-wrapper li.js-ticket-li-style.ui-tabs-active a.js-ticket-tab-link{background-color: '.$color2.';color: '.$color7.';}
			div.js-ticket-tabs-wrapper li.js-ticket-li-style a.js-ticket-tab-link:focus{background-color: '.$color2.';color: '.$color7.';}
			div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select.js-ticket-premade-select{background-color: '.$color3.';border:1px solid  '.$color5.';}
			div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#staffid {background: #fff;}
			div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#departmentid {background: #fff;}
			span.js-ticket-apend-radio-btn{border:1px solid  '.$color5.';background-color: #fff;}
			div.js-ticket-append-signature-wrp div.js-ticket-append-field-title {color: '.$color2.';}
			div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-title{color: '.$color2.';}
			div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box{border:1px solid  '.$color5.';background-color: #fff;}
			div.js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp{border:1px solid  '.$color5.';background-color: #fff;}
			div.js-ticket-closeonreply-wrp div.js-form-title-position-reletive-left{border:1px solid  '.$color5.';background-color: #fff;color: '.$color2.';}
			div.js-ticket-internalnote-wrp div.js-ticket-internalnote-field-wrp input.js-ticket-internalnote-input{border:1px solid  '.$color5.';background-color: '.$color3.';}
			span.tk_attachments_addform{background-color:'.$color1.';color:'.$color7.';border:1px solid '.$color5.';}
			span.tk_attachments_addform:hover{border-color:'.$color2.';}
			div#userpopupforchangepriority div.js-ticket-priorty-btn-wrp{border-top:2px solid '.$color2.';}
			div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-save{background-color:'.$color1.';color:'.$color7.';border:1px solid  '.$color5.';}
			div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-save:hover{border-color:'.$color2.';}
			div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-cancel{background-color:'.$color2.';color:'.$color7.';border:1px solid  '.$color5.';}
			div.js-ticket-priorty-btn-wrp input.js-ticket-priorty-cancel:hover{border-color:'.$color1.';}
		/*Post Reply Section*/

		/*Pop Up Section*/
			form#userpopupsearch div.search-center-history{background:  '.$color1.';color: '.$color7.';}
			div.jsst-popup-header{background:  '.$color1.';color: '.$color7.';}
			div#userpopup{background:  '.$color7.';}
			div.jsst-popup-wrapper{background-color: '.$color7.';}
			div.js-ticket-search-top div.js-ticket-search-left div.js-ticket-search-fields-wrp input.js-ticket-search-input-fields{border:1px solid  '.$color5.';background-color: '.$color3.';}
			div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-search-btn{background:  '.$color2.';color: '.$color7.';}
			div.js-ticket-search-top div.js-ticket-search-right div.js-ticket-search-btn-wrp input.js-ticket-reset-btn{background: #606062;color: '.$color7.';}
		/*Pop Up Section*/

		/* Merge Ticket */
			div.js-merge-ticket{background-color:  '.$color7.';}
			div.js-ticket-merge-white-bg{background-color:  '.$color7.';}
			span.js-img-wrp{border: 1px solid  '.$color5.';}
			span.js-ticket-info2{border-left: 1px solid  '.$color5.';}
			div.js-tickets-list-wrp{border-top: 2px solid  '.$color5.';background-color: '.$color3.';border-bottom: 1px solid  '.$color2.';}
			div.js-merge-form-value input.inputbox{border: 1px solid  '.$color5.';}
			div.js-view-tickets{border-top: 1px solid  '.$color2.';}
			span.js-merge-btn input.js-search{background-color:'.$color1.' !important;color: '.$color7.';border: 1px solid '.$color5.';}
			span.js-merge-btn input.js-search:hover{border-color: '.$color2.';}
			span.js-merge-btn input.js-cancel{background-color:'.$color2.' !important;color: '.$color7.';border: 1px solid '.$color5.';}
			span.js-merge-btn input.js-cancel:hover{border-color: '.$color1.';}
			input.js-merge-cancel-btn{background-color:'.$color2.' !important;color: '.$color7.';border: 1px solid '.$color5.';}
			input.js-merge-cancel-btn:hover{border-color: '.$color1.';}
			div.jsst-popup-wrapper div.js-form-button-wrapper input.js-merge-save-btn{background-color: '.$color2.' !important;}
			a.js-merge-btn{color:  '.$color7.' !important;background-color: '.$color2.' !important;}
			a.js-merge-btn:hover{color:  '.$color7.' !important;background-color: '.$color2.' !important;}
			span.js-ticket-wrapper-textcolor{color:'.$color7.';}
		/* Merge Ticket */

		/*new css*/
		.js-tkt-det-cnt {background: '.$color7.';border: 1px solid '.$color5.';box-shadow: 0 0 3px 2px rgba(162, 162, 162, 0.71);}
		.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.name {color: '.$color1.';}
		.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.subject {color: '.$color2.';}
		.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data {color: '.$color4.';}
		.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data span.js-tkt-det-user-tit {color: '.$color2.';}
		.js-tkt-det-other-tkt {background: #fef1e6;border: 1px solid '.$color5.';border-left: 0;border-right: 0;}
		.js-tkt-det-other-tkt .js-tkt-det-other-tkt-btn {color: '.$color1.';}
		.js-tkt-det-tkt-msg {color: '.$color4.';}
		.js-tkt-det-tkt-msg p {color: '.$color4.';}
		.js-tkt-det-actn-btn-wrp {border-top: 1px solid '.$color5.';}
		.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn {border: 1px solid '.$color5.';background: '.$color3.';}
		.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn:hover {border-color:  '.$color2.';}
		.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn span {color: '.$color4.';}
		.js-tkt-det-hdg .js-tkt-det-hdg-txt {color: '.$color2.';}
		.js-tkt-det-hdg .js-tkt-det-hdg-btn {color: '.$color1.';}
		.js-tkt-det-hdg .js-tkt-det-hdg-btn:hover {color: '.$color2.';}
		.js-tkt-det-status {background: #5bb02f;color: '.$color7.';}
		.js-tkt-det-info-data .js-tkt-det-info-tit {color: '.$color2.';}
		.js-tkt-det-info-data .js-tkt-det-info-val {color: '.$color4.';}
		.js-tkt-det-copy-id {color: '.$color1.' !important;}
		.js-tkt-det-tkt-prty-txt {color: '.$color7.';}
		.js-tkt-det-tkt-asgn-cnt .js-tkt-det-hdg .js-tkt-det-hdg-txt {color: '.$color4.';}
		.js-tkt-det-trsfer-dep {border-top: 1px solid '.$color5.';}
		.js-tkt-det-trsfer-dep .js-tkt-det-trsfer-dep-txt {color: '.$color4.';}
		.js-tkt-det-trsfer-dep .js-tkt-det-trsfer-dep-txt span.js-tkt-det-trsfer-dep-txt-tit {color: '.$color2.';}
		.js-tkt-det-usr-tkt-list .js-tkt-det-user {border-bottom: 1px solid '.$color5.';}
		.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data .js-tkt-det-user-val {color: '.$color4.';}
		.js-tkt-det-usr-tkt-list .js-tkt-det-prty {color: '.$color7.';}
		.js-tkt-det-usr-tkt-list .js-tkt-det-status {color: '.$color4.';background: '.$color3.';border: 1px solid '.$color5.';}
		.js-tkt-det-title {color: '.$color7.';background: '.$color2.';}
		.js-ticket-thread {border: 1px solid '.$color5.';background: '.$color7.';box-shadow: 0 0 3px 2px rgba(0,0,0,0.2);}
		.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-person {color: '.$color1.';}
		.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-note {color: '.$color4.';}
		.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-email {color: '.$color4.';}
		.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-date {color: '.$color4.';}
		.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-time {color: '.$color4.';}
		.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data.note-msg {color: '.$color4.';}
		.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data.note-msg p {color: '.$color4.';}
		.js-ticket-thread .js-ticket-thread-cnt-btm {border-top: 1px solid '.$color5.';}
		.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn {color: '.$color4.';border: 1px solid '.$color5.';}
		.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn:hover {border-color: '.$color2.';}
		.js-tkt-det-time-tracker {background: '.$color3.';}
		.js-tkt-det-timer-wrp .timer {color: '.$color4.';}
		.js-tkt-det-timer-wrp .timer .timer-box {color: '.$color4.';background: '.$color7.';border: 1px solid '.$color5.';}
		.js-tkt-det-timer-wrp .timer-buttons .timer-button {background: '.$color7.';border: 1px solid '.$color5.';color: '.$color4.';}
		.js-tkt-det-timer-wrp .timer-buttons .timer-button.active {background: '.$color2.';color: '.$color7.';}
		.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-title {color: '.$color4.';}
		.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-value {color: '.$color4.';}
		.js-tkt-det-timer-wrp .timer-total-time .timer-total-time-value .timer-box {color: '.$color4.';background: '.$color7.';border: 1px solid '.$color5.';}
		.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link {color: '.$color7.';background: '.$color1.';border: 1px solid '.$color5.';}
		.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link:hover {border-color: '.$color1.';color: '.$color7.';}
		.jsst-ticket-detail-timer-wrapper {background: '.$color7.';border: 1px solid '.$color5.';}
		.jsst-ticket-detail-timer-wrapper .timer-total-time {color: '.$color4.';}
		.jsst-ticket-detail-timer-wrapper .timer {color: '.$color4.';}
		.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button {background: '.$color7.';border: 1px solid '.$color5.';}
		.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button:hover {background: '.$color2.';}
		.jsst-ticket-detail-timer-wrapper .timer-buttons .timer-button.selected {background: '.$color2.';}
		.js-det-tkt-form .js-form-wrapper .js-form-title {color: '.$color4.';}
		.js-det-tkt-form .js-form-wrapper .js-form-value {color: '.$color4.';}
		.js-det-tkt-form .js-form-wrapper .js-form-value .js-admin-popup-input-field {color: '.$color4.';background: '.$color3.';border: 1px solid '.$color5.';}
		.js-det-tkt-form .js-form-wrapper .js-form-value .js-admin-popup-select-field {color: '.$color4.';background: '.$color3.';border: 1px solid '.$color5.';}
		.js-det-tkt-form .js-form-wrapper .tk_attachment_value_wrapperform .tk_attachment_value_text {background: '.$color7.';}
		.js-det-tkt-form .js-form-wrapper .tk_attachments_addform {border: 1px solid '.$color5.';color: '.$color7.';background: '.$color2.';}
		.js-det-tkt-form .js-form-wrapper .jsst-formfield-radio-button-wrap {color: '.$color4.';border: 1px solid '.$color5.';background: '.$color7.';}
		.js-det-tkt-form .js-form-wrapper .js-tkt-det-perm-msg a {border: 1px solid '.$color5.';background: '.$color7.';color: '.$color4.';}
		.js-det-tkt-form .js-form-wrapper .js-tkt-det-perm-msg a:hover {border-color: '.$color2.';background: '.$color7.';color: '.$color4.';}
		#jsst-note-edit-form div.js-form-button-wrapper input#ppppok {background-color:'.$color1.';color: '.$color7.';border: 1px solid '.$color5.';}
		#jsst-note-edit-form div.js-form-button-wrapper input#ppppok:hover {border-color: '.$color2.';}
	#jsst-note-edit-form div.js-form-button-wrapper input#cancele {background-color:'.$color2.';color: '.$color7.';border: 1px solid '.$color5.';}
	#jsst-note-edit-form div.js-form-button-wrapper input#cancele:hover {border-color: '.$color1.';}
		div.jsst-popup-wrapper div.js-form-button-wrapper input.button{color:#fff;}
		/* ticket detail woocommerce */
		.js-tkt-wc-order-box .js-tkt-wc-order-item .js-tkt-wc-order-item-title {color: '.$color2.';}
		.js-tkt-wc-order-box .js-tkt-wc-order-item .js-tkt-wc-order-item-value {color: '.$color4.';}

	/*ticket histort*/
	.js-ticket-history-table-wrp table,
	.js-ticket-history-table-wrp table th,
	.js-ticket-history-table-wrp table td {border: 1px solid '.$color5.';}


	/* Ticket Details*/';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
