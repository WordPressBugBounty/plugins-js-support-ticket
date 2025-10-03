<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
// if header is calling later
JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();
JSSTincluder::getJSModel('jssupportticket')->jsst_get_theme_colors();

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

/* General Form Styling */
form.js-ticket-form {
    display: flex;
    flex-wrap: wrap;
    column-gap:25px;
    width: 100%;
    padding: 40px;
    background-color: #ffffff;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.8s ease-out forwards;
    opacity: 0;
}
form.js-ticket-form div.js-form-wpuser-data-wrapper{display:flex;flex-wrap: wrap;width:100%;column-gap:25px;}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form Fields Wrapper */
div.js-ticket-add-form-wrapper {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
}
/* Individual Form Field Wrapper */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
    flex: 1 1 calc(50% - 12.5px);
    margin: 0;
    min-width: 300px;
    position: relative;
    margin-bottom: 30px;
}
/* Full-Width Field Wrapper */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width {
    flex: 1 1 100%;
    margin-bottom: 30px;
}
/* Field Title */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title,
.js-ticket-append-field-title {
    width: 100%;
    margin-bottom: 10px;
    font-weight: 600;
}
/* Field Container */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field {
    width: 100%;
    position: relative;
}

/* Unified Input, Select, and Textarea Fields */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea,
div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
select.js-ticket-select-field {
    width: 100%;
    border-radius: 10px;
    padding: 12px 18px;
    line-height: normal;
    height: auto;
    min-height: 52px;
    border: 1px solid ' . $color5 . ';
    color: ' . $color4 . ';
    transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    background-color: #fcfcfc;
    max-width:100%;
    box-sizing: border-box;
}

/* Focus state for all fields */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea:focus,
div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field:focus,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus {
    outline: none;
    border-color: ' . $color1 . ';
    box-shadow: 0 0 0 4px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.25);
    background-color: #ffffff;
}

/* Improved error message styling */
span.jsst-help-block {
    display: block !important;
    font-size: 14px;
    color: #c0392b !important;
    padding: 5px 15px;
    background-color: #fff0f0;
    border: 1px solid #e74c3c;
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 3px 10px rgba(231, 76, 60, 0.15);
    animation: slideInFromTop 0.4s ease-out forwards;
    opacity: 0;
    clear: both;
    width: 100%;
    box-sizing: border-box;
    position: relative;
    z-index: 2;
    bottom: 7px;
}

@keyframes slideInFromTop {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom Select Arrow */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
select.js-ticket-select-field {
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat #fcfcfc;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Buttons */
div.js-ticket-form-btn-wrp {
    width: 100%;
    margin: 40px 0 10px 0;
    text-align: center;
    padding: 30px 0px 10px 0px;
    border-top: 1px solid ' . $color5 . ';
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    box-sizing: border-box;
}

div.js-ticket-form-btn-wrp input.js-ticket-save-button {
    background-color: ' . $color1 . ' !important;
    color: ' . $color7 . ' !important;
    border: 1px solid ' . $color1 . ';
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
    padding: 16px 30px;
    min-width: 160px;
    border-radius: 10px;
    line-height: initial;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    letter-spacing: 0.5px;
    margin-right:0;
}
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
    background-color: ' . $color2 . ' !important;
    border-color: ' . $color2 . ' !important;
    transform: translateY(-3px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
    filter: brightness(1.1);
}

div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
    background-color: #f5f2f5;
    color: ' . $color4 . ';
    border: 1px solid ' . $color5 . ';
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    padding: 16px 30px;
    min-width: 160px;
    border-radius: 10px;
    line-height: initial;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    letter-spacing: 0.5px;
}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
    background-color: ' . $color2 . ';
    color: ' . $color7 . ';
    border-color: ' . $color2 . ';
    transform: translateY(-2px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
}

/* Attachments Section */
div.js-ticket-reply-attachments {
    display: inline-block;
    width: 100%;
    margin-bottom: 40px;
    padding-top: 30px;
    border-top: 1px solid ' . $color5 . ';
}
.js-ticket-add-form-wrapper label{
    font-weight: inherit;
    color: ' . $color4 . ';
    
}
div.js-ticket-append-signature-wrp.js-ticket-append-signature-wrp-full-width{
    flex: 1 1 100%;
    margin-bottom: 30px;
}
    div.js-ticket-append-signature-wrp{
    margin-bottom: 30px;
}
div.js-ticket-reply-attachments div.js-attachment-field-title {
   display: inline-block;
    width: 100%;
    padding: 15px 0px;
    font-weight: 600;
    border-bottom: 1px solid ' . $color5 . ';
    margin-bottom: 20px;
}
div.js-attachment-field,
div.tk_attachment_value_wrapperform {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    width: 100%;
    float: left;
    padding: 15px;
    border: 2px dashed ' . $color5 . ';
    border-radius: 12px;
    background-color: ' . $color3 . ';
    margin-bottom: 20px;
    min-height: 120px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}
div.tk_attachment_value_wrapperform {
    background-color: #ffffff;
}
div.tk_attachment_value_wrapperform:hover {
    background-color: #f0f8ff;
}
div.tk_attachment_value_wrapperform span.tk_attachment_value_text {
    width: calc(50% - 20px);
    padding: 12px;
    margin: 10px;
    position: relative;
    background-color: #fff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
}
div.tk_attachment_value_wrapperform span.tk_attachment_value_text input.js-attachment-inputbox {
    width: calc(100% - 40px);
    max-width: 100%;
    max-height: 100%;
    border: none;
    background: transparent;
    font-size: 15px;
    cursor: pointer;
    color: ' . $color4 . ';
}
span.tk_attachment_value_text span.tk_attachment_remove {
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23e74c3c\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") no-repeat center center;
    background-size: 26px 26px;
    position: absolute;
    width: 35px;
    height: 35px;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s ease, transform 0.3s ease;
}
span.tk_attachment_value_text span.tk_attachment_remove:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}
span.tk_attachments_configform {
    display: inline-block;
    float: left;
    line-height: 1.6;
    word-break: break-all;
    width: 100%;
    font-size: 15px;
    color: ' . $color4 . ';
    opacity: 0.75;
    text-align: left;
    box-sizing: border-box;
    padding: 0 5px;
}
span.tk_attachments_addform {
    position: relative;
    display: inline-block;
    padding: 15px 25px;
    cursor: pointer;
    margin-top: 25px;
    min-width: 150px;
    text-align: center;
    line-height: initial;
    background-color: ' . $color1 . ';
    color: ' . $color7 . ';
    border: none;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
}
span.tk_attachments_addform:hover {
    background-color: ' . $color2 . ';
    border-color: ' . $color2 . ';
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
    filter: brightness(1.1);
}

/* Custom Checkboxes & Radios */
div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box {
    border: 1px solid ' . $color5 . ';
    background-color: #fff;
    display: inline-flex;
    align-items: center;
    padding: 12px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
input.radiobutton.js-ticket-append-radio-btn,
.js-ticket-custom-terms-and-condition-box input.radiobutton.js-ticket-append-radio-btn,
.jsst-formfield-radio-button-wrap input.js-ticket-append-radio-btn {
    display: inline-block !important;
    margin: 0 12px 0 0 !important;
    transform: scale(1.3);
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    border: 1px solid ' . $color5 . ';
    border-radius: 4px;
    background-color: #ffffff;
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
    transition: all 0.2s ease;
    opacity: 1;
    vertical-align: middle;
}
input.radiobutton.js-ticket-append-radio-btn:checked {
    background-color: ' . $color1 . ';
    border-color: ' . $color1 . ';      
}
input.radiobutton.js-ticket-append-radio-btn:checked::after {
    content: "";
    position: absolute;
    top: 0px;
    left: 0px;
    width: 12px;
    height: 12px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E");
    background-size: contain;
    background-repeat: no-repeat;
}

/* User Popup */
div#userpopupblack {
    background: rgba(0,0,0,0.7);
    position: fixed;
    width: 100%;
    height: 100%;
    top:0px;
    left:0px;
    z-index: 9989;
}
div.js-ticket-select-user-btn{float: left;width: 30%;position: absolute;top: 0;right: 0;}
div.js-ticket-select-user-btn a#userpopup{border-radius: 0 10px 10px 0; padding: 12px 10px;display:flex;align-items:center;justify-content:center; width: 100%;text-align: center;text-decoration: none;outline: 0px;line-height: initial;min-height:52px; box-sizing: border-box;height:100%;}

/* General Popup Styling */
div#userpopup {position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);width:60%;background-color: #ffffff;border-radius: 15px;box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);z-index: 99999;overflow: hidden;display: flex;flex-direction: column;max-height: 90vh;}
/* --- Header --- */
.jsst-popup-header {display: flex;justify-content: space-between;align-items: center;padding: 16px 24px;background-color: #f8f9fa;border-bottom: 1px solid ' . $color5 . ';}
.popup-header-text {font-size: 21px;font-weight: 600;color: #343a40;}
.popup-header-close-img {width: 28px;height: 28px;cursor: pointer;background-repeat: no-repeat;background-position: center;background-size: contain;transition: transform 0.3s ease;background-image: url(\'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"%3e%3cline x1="18" y1="6" x2="6" y2="18"%3e%3c/line%3e%3cline x1="6" y1="6" x2="18" y2="18"%3e%3c/line%3e%3c/svg%3e\') !important;}
.popup-header-close-img:hover {opacity: 1;}
/* --- Search Section --- */
.js-ticket-popup-search-wrp {padding: 24px;background-color:' . $color3 . ';margin: 10px;border-radius:10px; border: 1px solid ' . $color5 . '!important;}
.js-ticket-search-top {display: flex;flex-wrap: wrap;gap: 16px;}
.js-ticket-search-left {flex: 3;}
.js-ticket-search-fields-wrp {display: flex;flex-wrap: wrap;gap: 12px;height:100%;}
.js-ticket-search-input-fields {flex: 1;min-width: 150px;padding: 12px 16px;border: 1px solid ' . $color5 . ';border-radius: 10px;background-color: #fff;transition: border-color 0.2s ease, box-shadow 0.2s ease;}
.js-ticket-search-input-fields:focus {outline: none;border-color: #80bdff;box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);}
.js-ticket-search-right {flex: 1;display: flex;align-items: flex-start;}
.js-ticket-search-btn-wrp {display: flex;gap: 12px;width: 100%;}
.js-ticket-search-btn,
.js-ticket-reset-btn {flex: 1;padding: 12px 16px;font-weight: 500;border-radius: 10px;border: 1px solid transparent;cursor: pointer;transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;white-space: nowrap;}
/* --- Results Table --- */
#records {flex-grow: 1;overflow-y: auto;padding: 0 10px 24px;}
div.js-ticket-table-wrp div.js-ticket-table-header{
    border-top-right-radius: 10px;
    border-top-left-radius: 10px;
}
div.js-ticket-table-body div.js-ticket-data-row:last-child:last-child{
    border-bottom-right-radius: 10px;
    border-bottom-left-radius: 10px;
}
.js-ticket-table-header,
.js-ticket-data-row {display: flex;width: 100%;padding: 0 16px;}
.js-ticket-table-header {background-color: #f8f9fa;border-bottom: 2px solid ' . $color5 . ';padding-top: 16px;padding-bottom: 16px;font-weight: 600;color: #495057;}
.js-ticket-data-row {border-bottom: 1px solid ' . $color5 . ';transition: background-color 0.2s ease;}
.js-ticket-data-row:hover {background-color: #f1f3f5;}
.js-ticket-data-row:last-child {border-bottom: none;}
.js-ticket-table-header-col,
.js-ticket-table-body-col {flex: 1;padding: 16px 8px;text-align: left;display: flex;align-items: center;}
/* Adjust column widths */
.js-ticket-table-header-col:nth-child(1), .js-ticket-table-body-col:nth-child(1) {flex-basis: 10%;}
.js-ticket-table-header-col:nth-child(2), .js-ticket-table-body-col:nth-child(2) {flex-basis: 25%;}
.js-ticket-table-header-col:nth-child(3), .js-ticket-table-body-col:nth-child(3) {flex-basis: 40%;}
.js-ticket-table-header-col:nth-child(4), .js-ticket-table-body-col:nth-child(4) {flex-basis: 25%;}
.js-userpopup-link {text-decoration: none;font-weight: 500;}
.js-userpopup-link:hover {text-decoration: underline;}
.js-ticket-display-block {display: none;}
/* --- Pagination --- */
.jsst_userpages {text-align: center;padding: 24px 0 0;}
.jsst_userlink {display: inline-block;padding: 8px 12px;margin: 0 4px;border-radius: 10px;text-decoration: none;}

/* --- Responsive Design --- */
@media (max-width: 991px) {
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {flex: 1 1 100%;}
}
@media (max-width: 768px) {
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{width: calc(50% - 20px);}
    form.js-ticket-form {padding: 25px;}
    div.js-ticket-form-btn-wrp input.js-ticket-save-button,
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {width: 100%;margin-right: 0;margin-bottom: 20px;}
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text {width: calc(100% - 20px);}
    .js-ticket-search-top,
    .js-ticket-search-fields-wrp {flex-direction: column;}
    .js-ticket-table-header {display: none;}
    .js-ticket-data-row {flex-direction: column;padding: 12px 0;}
    .js-ticket-table-body-col {flex-basis: auto !important;width: 100%;padding: 8px 16px;display: flex;justify-content: space-between;align-items: center;border-bottom: 1px dashed ' . $color5 . ';}
    .js-ticket-data-row .js-ticket-table-body-col:last-child {border-bottom: none;}
    .js-ticket-display-block {display: inline-block;font-weight: 600;color: #495057;margin-right: 8px;}
    .js-ticket-title,
    .js-ticket-table-body-col {text-align: right;}
}
@media (max-width: 480px) {
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{width: calc(100% - 20px);}
}
';
/*Code For Colors*/
$jssupportticket_css .= '
/* Add Form Colors */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
	div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea {
		border: 1px solid ' . $color5 . '!important;color: ' . $color4 . ';background-color: #fcfcfc;
	}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title,
	.js-ticket-append-field-title {color:' . $color2 . ';}
	div.js-ticket-select-user-btn a#userpopup{background-color:' . $color1 . ';color:' . $color7 . ';border: 1px solid ' . $color1 . ';}
	div.js-ticket-select-user-btn a#userpopup:hover{background-color:' . $color2 . '; border-color:' . $color2 . ';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
	select.js-ticket-select-field {background-color: #fcfcfc;border:1px solid ' . $color5 . ' !important;color: ' . $color4 . ';}
	span.js-ticket-sub-fields{background-color:' . $color3 . ' !important;border:1px solid ' . $color5 . ';}
	.js-userpopup-link{color:' . $color2 . ';}
	div.js-ticket-form-btn-wrp{border-top:1px solid ' . $color5 . ';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:' . $color1 . ' !important;color:' . $color7 . ' !important; border-color:' . $color1 . ';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{background-color:' . $color2 . '!important;color:' . $color7 . ' !important; border-color:' . $color2 . '!important;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background-color:#f5f2f5;color: ' . $color4 . ';border:1px solid ' . $color5 . ';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{background-color:' . $color2 . '!important;color:' . $color7 . ' !important; border-color:' . $color2 . '!important;}
	div.js-ticket-reply-attachments div.js-attachment-field-title {border-bottom-color: ' . $color5 . ';}
	div.tk_attachment_value_wrapperform{border-color: ' . $color5 . '; background: #fff;}
	span.tk_attachment_value_text{border: 1px solid ' . $color5 . '; background-color:' . $color7 . ';}
	span.tk_attachments_addform{background-color:' . $color1 . ';color:' . $color7 . ';}
    span.tk_attachments_addform:hover{background-color: ' . $color2 . ';border-color: ' . $color2 . ';}
	span.tk_attachments_configform{color:' . $color4 . ';}
	div.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box{border:1px solid ' . $color5 . ';background-color:#fff;}
	/* User Select Popup Colors */
	div#userpopup{background: ' . $color7 . ';}
	div.jsst-popup-header{background-color: ' . $color3 . '; color:' . $color2 . ';}
	.js-ticket-popup-search-wrp {background-color:' . $color3 . '; border: 1px solid ' . $color5 . '!important;}
	.js-ticket-search-input-fields{border:1px solid ' . $color5 . ';background-color:#fff;color: ' . $color4 . ';}
	.js-ticket-search-btn{background-color: ' . $color1 . ';color:' . $color7 . ';}
	.js-ticket-search-btn:hover{background-color:' . $color2 . ';}
	.js-ticket-reset-btn{background-color: #f5f2f5;color: '. $color4 .';border: 1px solid #ced4da;}
	.js-ticket-reset-btn:hover{background-color:'. $color2 .';color:'. $color7 .';}
	div.js-ticket-table-header{background-color:#f8f9fa; border-bottom: 2px solid ' . $color5 . '; color: #495057;}
	div.js-ticket-data-row{border-bottom: 1px solid ' . $color5 . ';}
    .jsst_userlink { color: ' . $color1 . '; background-color: #e9ecef; }
    .jsst_userlink.selected { background-color: ' . $color1 . '; color: ' . $color7 . '; }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
