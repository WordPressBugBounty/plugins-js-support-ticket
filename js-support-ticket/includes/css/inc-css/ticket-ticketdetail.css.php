<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly 
// if header is calling later
JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();
JSSTincluder::getJSModel('jssupportticket')->jsst_get_theme_colors();

$jsst_color1 = jssupportticket::$jsst_colors['color1'];
$jsst_color2 = jssupportticket::$jsst_colors['color2'];
$jsst_color3 = jssupportticket::$jsst_colors['color3'];
$jsst_color4 = jssupportticket::$jsst_colors['color4'];
$jsst_color5 = jssupportticket::$jsst_colors['color5'];
$jsst_color6 = jssupportticket::$jsst_colors['color6'];
$jsst_color7 = jssupportticket::$jsst_colors['color7'];
$jsst_color8 = jssupportticket::$jsst_colors['color8'];
$jsst_color9 = jssupportticket::$jsst_colors['color9'];


$jsst_jssupportticket_css = '';

/*Code for Css*/
$jsst_jssupportticket_css .= '

/*
 * =================================================================
 * AI-POWERED REPLY MODE - MODERN REDESIGN
 * =================================================================
 */

/* Main wrapper for the entire component */
.js-ticket-ai-reply-status-wrapper {
    display: flex;
    gap: 15px; /* Space between label, icon, and control */
    padding: 10px 0;
    flex-direction: column;
}
div.js-ticket-thread-actions{
    margin-left:auto;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}
/* Label and Icon Styling */
.js-ticket-ai-reply-status-wrapper label {
    font-weight: 500;
    color: #4b5563; /* Medium grey text */
    margin: 0;
}

.js-ticket-info-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.js-ticket-info-icon img {
    width: 18px;
    height: 18px;
    cursor: help; /* Change cursor to indicate help is available */
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.js-ticket-info-icon:hover img {
    opacity: 1;
}

/* Tooltip for the info icon */
.js-ticket-info-icon::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 160%;
    left: -21px;
    transform: translateX(-50%);
    background-color: #1f2937; /* Dark background */
    color: #ffffff;
    padding: 8px 12px;
    border-radius: 8px;
    white-space: nowrap;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 10;
    min-height:fit-content;
    height:fit-content;
}

.js-ticket-info-icon:hover::after {
    opacity: 1;
    visibility: visible;
    bottom: 170%;
}

/*
 * Segmented Control Styling
 */

/* Container for the buttons */
.js-ticket-segmented-control {
    display: flex;
    position: relative;
    border-radius: 9px;
    padding: 4px;
    border: 1px solid '. $jsst_color5 .';
}

/* Individual buttons */
.js-ticket-segmented-control-option {
    padding: 6px 16px;
    border: none;
    background: none; /* Make buttons transparent */
    color: #4b5563;
    font-weight: 600;
    cursor: pointer;
    transition: color 0.3s ease;
    z-index: 2; /* Place buttons above the sliding indicator */
    border-radius: 7px;
}

/* Style for the active button */
.js-ticket-segmented-control-option.active {
    color: #ffffff; /* White text for the active button */
}

/* Sliding indicator for the active state */
.js-ticket-segmented-control::before {
    content:"";
    position: absolute;
    top: 4px;
    left: 4px;
    height: calc(100% - 8px);
    width: calc((100% - 8px) / 3); /* Assumes 3 buttons */
    background-color: #3b82f6; /* Blue indicator */
    border-radius: 7px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    z-index: 1;
}

/* Move the indicator based on which button is active */
.js-ticket-segmented-control-option.js-ticket-default.active ~ .js-ticket-segmented-control::before {
    transform: translateX(0%);
}
.js-ticket-segmented-control-option.js-ticket-enable.active ~ .js-ticket-segmented-control::before {
    transform: translateX(100%);
}
.js-ticket-segmented-control-option.js-ticket-disable.active ~ .js-ticket-segmented-control::before {
    transform: translateX(200%);
}

/* Main Wrapper & Typography */

.js-ticket-ticket-detail-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap:20px;
    line-height: 1.6;
    color: ' . esc_attr($jsst_color4) . ';
    width:100%;
}
.js-ticket-ticket-detail-wrapper #adminTicketform{
    flex:1 1 auto;
    width:calc(70% - 20px);
}
.js-tkt-det-user{
    margin-bottom:15px;
}
.js-tkt-det-user .js-tkt-det-user-data.email {
    margin-bottom: 5px;
}
.js-tkt-det-user {
    padding: 10px 0;
    border-bottom: 1px solid ' . esc_attr($jsst_color5) . ';
}
.js-tkt-det-usr-tkt-list .js-tkt-det-user:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.js-tkt-det-usr-tkt-list .js-tkt-det-user-image {
    width: 40px !important;
    height: 40px !important;
}
.js-tkt-det-usr-tkt-list .js-tkt-det-user-cnt {
    width: calc(100% - 60px) !important;
}
.js-tkt-det-usr-tkt-list .js-tkt-det-user-data.name {
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
}
.js-tkt-det-user .js-tkt-det-user-image {
    flex-shrink: 0;
    display:flex;
    margin-bottom:10px;
    width:40px;
    height:40px;
}
.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.name {
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
    text-decoration: none;
    margin-bottom: 5px;
}
.js-tkt-det-user .js-tkt-det-user-cnt .js-tkt-det-user-data.subject {
    font-weight: 500;
    color: ' . esc_attr($jsst_color2) . ';
    margin-bottom: 5px;
}
.js-tkt-det-other-tkt .jsst-main-up-wrapper a {
    color: ' . esc_attr($jsst_color1) . ' !important;}

.js-tkt-det-other-tkt {
    background: #fef1e6;
    border: 1px solid '. $jsst_color5 .';
    float: left;
    width: 100%;
    padding: 15px;
    line-height: initial;
}
.js-tkt-det-other-tkt {
    margin-bottom:15px;
}
/*
 * =================================================================
 * INTERNAL NOTE POPUP STYLES
 * =================================================================
 */

/* Main popup container */
#popupforinternalnote {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width:55%;
    max-width: 100%; /* A bit wider for the content */
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 10006;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Popup Header */
#popupforinternalnote .jsst-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background-color: '. $jsst_color3 .';
    border-bottom: 1px solid ' . $jsst_color5 . ';
    flex-shrink: 0;
}

#popupforinternalnote .popup-header-text {
    font-weight: 600;
    color: ' . $jsst_color2 . ';
}

/* Header Close Icon */
div.internalnote-popup-background {
    background: rgba(0,0,0,0.5);
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0px;
    left: 0px;
    z-index: 9988;
}
#popupforinternalnote .internalnote-popup-header-close-img {
background-image: url(\'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"%3e%3cline x1="18" y1="6" x2="6" y2="18"%3e%3c/line%3e%3cline x1="6" y1="6" x2="18" y2="18"%3e%3c/line%3e%3c/svg%3e\') !important;
    width: 28px;
    height: 28px;
    cursor: pointer;
    
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease;
}

#popupforinternalnote .internalnote-popup-header-close-img:hover {
    transform: rotate(90deg);
}

/* Form container */
#popupforinternalnote form {
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 25px;
    overflow-y: auto; /* Make the form area scrollable */
    max-height: 75vh;
}

/* Time Tracker Bar */
#popupforinternalnote .jsst-ticket-detail-timer-wrapper {
    background-color: #f8f9fa;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 12px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}

#popupforinternalnote .timer-left,
#popupforinternalnote .timer-total-time {
    font-weight: 500;
    color: #495057;
}

#popupforinternalnote .timer-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

#popupforinternalnote .timer {
    font-weight: 600;
    color: #212529;
    background-color: #ffffff;
    padding: 5px 12px;
    border-radius: 6px;
    border: 1px solid '. $jsst_color5 .';
}

#popupforinternalnote .timer-buttons {
    display: flex;
    gap: 8px;
}

#popupforinternalnote .timer-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
}

#popupforinternalnote .timer-button:hover {
    border-color: '. $jsst_color1 .';
    background-color: #f0f7ff;
}

/* Form Fields (Title, Note, Attachments) */
#popupforinternalnote .js-ticket-internalnote-field-title,
#popupforinternalnote .js-ticket-text-editor-field-title,
#popupforinternalnote .js-attachment-field-title,
#popupforinternalnote .js-ticket-closeonreply-title {
    margin-bottom: 10px;
}

#popupforinternalnote .inputbox {
    width: 100%;
    padding: 12px 15px;
    color: #495057;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#popupforinternalnote .inputbox:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* WP Editor Styling */
#popupforinternalnote .wp-editor-wrap {
    border-radius: 8px;
}

/* Attachments Section */
#popupforinternalnote .js-attachment-field {
    border: 2px dashed ' . esc_attr($jsst_color5) . ';
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: border-color 0.2s ease;
}

#popupforinternalnote .js-attachment-field:hover {
    border-color: ' . esc_attr($jsst_color1) . ';
}

#popupforinternalnote .tk_attachments_configform {
    color: #6c757d;
    margin-top: 15px;
    line-height: 1.5;
}

/* "Close on reply" Checkbox */
#popupforinternalnote

 /*

/*
 * =================================================================
 * EDIT TIME & EDIT REPLY POPUP STYLES
 * =================================================================
 */

/* Main popup container */
#jsst-popup-wrapper {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 650px;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 10005;
    overflow: hidden;
}

/* Popup Header */
#jsst-popup-wrapper .jsst-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background-color: '. $jsst_color3.';
    border-bottom: 1px solid ' . $jsst_color5 . ';
}

#jsst-popup-wrapper .popup-header-text {
    font-weight: 600;
    color:  ' . $jsst_color2 . ';
}

/* Header Close Icon */
#jsst-popup-wrapper .popup-header-close-img {
    width: 28px;
    height: 28px;
    cursor: pointer;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease;
}
form#jsst-note-edit-form .js-col-md-12.js-form-button-wrapper {
    display: flex;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid ' . $jsst_color5 . ';
    margin-top: 10px;
    margin-left:25px;
    justify-content:center;
    margin-right:25px;
    width:calc(100% - 50px);
}

input#ppppok {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: ' . esc_attr($jsst_color1) . '!important;
    color: ' . esc_attr($jsst_color7) . ' !important;
    border:unset !important;
}
input#ppppok:hover{
    background-color: ' . esc_attr($jsst_color2) . '!important;
    color: ' . esc_attr($jsst_color7) . ' !important;
}
input#cancele {
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    background-color: #f5f2f5 !important;
    color: ' . esc_attr($jsst_color4) . ' !important;
    border: 1px solid ' . esc_attr($jsst_color5) . ' !important;
}
input#cancele:hover{
        background-color: ' . esc_attr($jsst_color2) . '  !important;
    color: ' . esc_attr($jsst_color7) . ' !important;
}

#jsst-popup-wrapper .popup-header-close-img:hover {
    transform: rotate(90deg);
}

/* Form container */
#jsst-popup-wrapper .js-ticket-edit-form-wrp {
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Form field title */
#jsst-popup-wrapper .js-ticket-edit-field-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: -10px; /* Reduces space to associate title with field below */
}
.js-ticket-premade-msg-wrp span.js-ticket-apend-radio-btn input {
        display: inline-block !important;
        margin: 0 12px 0 0 !important;
        transform: scale(1.3);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        border: 1px solid '. $jsst_color5 .';
        border-radius: 4px;
        background-color: #ffffff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
    }
 .js-ticket-premade-msg-wrp span.js-ticket-apend-radio-btn input::after {
        content: "";
        position: absolute;
        top: 0px; /* Adjust checkmark position */
        left: 0px; /* Adjust checkmark position */
        width: 12px; /* Larger checkmark */
        height: 12px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E"); /* White checkmark SVG */
        background-size: contain;
        background-repeat: no-repeat;
    }
.js-ticket-premade-msg-wrp span.js-ticket-apend-radio-btn input:checked {
        background-color: ' . esc_attr($jsst_color1) . ';
        border-color: ' . esc_attr($jsst_color1) . ';
    }
/* Input and Textarea fields */
#jsst-popup-wrapper .inputbox,
#jsst-popup-wrapper .js-ticket-edit-field-input {
    width: 100%;
    padding: 12px 15px;
    color: #495057;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#jsst-popup-wrapper textarea.inputbox {
    min-height: 120px;
    resize: vertical;
}

#jsst-popup-wrapper .inputbox:focus,
#jsst-popup-wrapper .js-ticket-edit-field-input:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Styling for the WP Editor */
#jsst-popup-wrapper .wp-editor-wrap {
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
}

/* Buttons wrapper */
#jsst-popup-wrapper .js-ticket-priorty-btn-wrp {
    display: flex;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid '. $jsst_color5 .';
    margin-top: 10px;
}

/* Common button styles */
#jsst-popup-wrapper .js-ticket-priorty-save,
#jsst-popup-wrapper .js-ticket-priorty-cancel {
    padding: 10px 22px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* "Save" button */
#jsst-popup-wrapper .js-ticket-priorty-save {
    background-color: #007bff;
    color: #ffffff;
    border: 1px solid #007bff;
}

#jsst-popup-wrapper .js-ticket-priorty-save:hover {
    background-color: ' . esc_attr($jsst_color2) . '!important;
    border-color: ' . esc_attr($jsst_color2) . ' !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
}

/* "Cancel" button */
#jsst-popup-wrapper .js-ticket-priorty-cancel {
    background-color: #f5f2f5;
    color: '. $jsst_color4 .';
    border: 1px solid '. $jsst_color5 .';
}

#jsst-popup-wrapper .js-ticket-priorty-cancel:hover {
    background-color:'. $jsst_color2 .' ;
    border-color: '. $jsst_color2 .';
     color:'. $jsst_color7 .' ;
}

/*
 * =================================================================
 * MATCHING TICKETS SECTION STYLES
 * =================================================================
 */

/*
 * =================================================================
 * ASSIGN TO AGENT POPUP STYLES
 * =================================================================
 */

/* Main popup container */
#popupforagenttransfer {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 55%;
    max-width: 100%; /* Wider to accommodate the text editor */
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 10004;
    overflow: hidden;
}

/* Popup Header */
#popupforagenttransfer .jsst-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background-color: ' . $jsst_color3 . ';
    border-bottom: 1px solid ' . $jsst_color5 . ';
}

#popupforagenttransfer .popup-header-text {
    font-weight: 600;
    color: ' . $jsst_color2 . ';
    font-size: 21px;
}

/* Header Close Icon */
#popupforagenttransfer .popup-header-close-img {
    width: 28px;
    height: 28px;
    cursor: pointer;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease;
}

#popupforagenttransfer .popup-header-close-img:hover {
    transform: rotate(90deg);
}

/* Form Styling */
#popupforagenttransfer form {
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 0px; /* Space between form sections */
}

/* Agent Dropdown Section */
#popupforagenttransfer .js-ticket-premade-msg-wrp .js-ticket-premade-field-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
}

#popupforagenttransfer .js-ticket-premade-msg-wrp .js-ticket-premade-select {
    width: 100%;
    padding: 12px 15px;
    color: #495057;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#popupforagenttransfer .js-ticket-premade-msg-wrp .js-ticket-premade-select:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Assigning Note (WP Editor) Section */
#popupforagenttransfer .js-ticket-text-editor-wrp .js-ticket-text-editor-field-title {
    
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
}

/* Submit Button Wrapper */
#popupforagenttransfer .js-ticket-reply-form-button-wrp {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
    padding-top: 20px;
    border-top: 1px solid ' . $jsst_color5 . ';
    border-radius:0px;
}


/* Assign Button */
#popupforagenttransfer .js-ticket-save-button {
    background-color:' . $jsst_color1 . ';
    color: #ffffff;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}
#popupforagenttransfer .js-ticket-save-button:hover {
    background-color: ' . $jsst_color2 . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
}



/*
 * =================================================================
 * CHANGE TICKET PRIORITY POPUP STYLES
 * =================================================================
 */

/* Main popup container */
#userpopupforchangepriority {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 10003;
    overflow: hidden;
}

/* Popup Header */
#userpopupforchangepriority .js-ticket-priorty-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background-color: ' . $jsst_color3 . ';
    border-bottom: 1px solid ' . $jsst_color5 . ';
    font-weight: 600;
    color: ' . $jsst_color2 . ';
}

/* Close icon in header */
#userpopupforchangepriority .close-history {
    width: 28px;
    height: 28px;
    cursor: pointer;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease;
}

#userpopupforchangepriority .close-history:hover {
    transform: rotate(90deg);
}

/* Form fields wrapper */
#userpopupforchangepriority .js-ticket-priorty-fields-wrp {
    padding: 30px 25px;
}

/* Dropdown / select styling */
#userpopupforchangepriority #prioritytemp {
    width: 100%;
    padding: 12px 15px;
    color: #495057;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#userpopupforchangepriority #prioritytemp:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Buttons wrapper */
#userpopupforchangepriority .js-ticket-priorty-btn-wrp {
    display: flex;
    flex-wrap:wrap;
    gap: 10px;
    padding: 20px 25px;
    background-color: #f8f9fa;
    border-top: 1px solid ' . $jsst_color5 . ';
}

/* Common button styles */
#userpopupforchangepriority .js-ticket-priorty-save,
#userpopupforchangepriority .js-ticket-priorty-cancel {
    padding: 10px 22px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.js-row.js-ticket-popup-row{
    padding: 15px 20px;
    font-weight: 600;
    border-bottom: 1px solid ' . $jsst_color5 . ';
    background-color: ' . $jsst_color3 . ';
    color: ' . $jsst_color2 . ';
    display: flex;
    justify-content: center;
    align-items: center;
    margin:0;
    font-size:17px;
}
.js-row.js-ticket-popup-row form{
    width:100%;
}
 .js-row.js-ticket-popup-row form .search-center-history{
    display: flex;
    align-items: center;
    width:100%;
    font-size:21px;
 }
.js-row.js-ticket-popup-row form .search-center-history .close-history{
    margin-left:auto;
}
/* "Change Priority" button */
#userpopupforchangepriority .js-ticket-priorty-save {
    background-color: '.$jsst_color1.';
    color: #ffffff;
    border: 1px solid '.$jsst_color1.';
}

#userpopupforchangepriority .js-ticket-priorty-save:hover {
    background-color: '.$jsst_color2.';
    border-color: '.$jsst_color2.';
    transform: translateY(-2px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
}

/* "Cancel" button */
#userpopupforchangepriority .js-ticket-priorty-cancel {
    background-color: #f5f2f5;
    color: #6c757d;
    border: 1px solid '. $jsst_color5 .';
}

#userpopupforchangepriority .js-ticket-priorty-cancel:hover {
    background-color: '. $jsst_color2 .';
    color:'. $jsst_color7 .';
    transform: translateY(-2px);
    border-color: '. $jsst_color2 .';
}

/*
 * =================================================================
 * CHANGE TICKET STATUS POPUP STYLES
 * =================================================================
 */

/* Main popup container */
#userpopupforchangestatus {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 55%;
    max-width: 100%; /* Optimal width for a simple form */
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 10002; /* Ensure it appears above other elements */
    overflow: hidden; /* Keeps child elements within the rounded corners */
}

/* Popup Header */
#userpopupforchangestatus .jsst-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background-color: ' . $jsst_color3 . ';
    border-bottom: 1px solid ' . $jsst_color5 . ';
}
.jsst-popup-header .popup-header-text{
    font-size:21px;
}
#userpopupforchangestatus .popup-header-text {
    font-weight: 600;
    color: ' . $jsst_color2 . ';
}

/* Close icon in the header */
#userpopupforchangestatus .popup-header-close-img {
    width: 28px;
    height: 28px;
    cursor: pointer;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease;
}

#userpopupforchangestatus .popup-header-close-img:hover {
    transform: rotate(90deg);
}

/* Form container */
#userpopupforchangestatus form {
    padding: 25px;
}

/* Select Status Wrapper */
#userpopupforchangestatus .js-ticket-premade-msg-wrp {
    margin-bottom: 25px;
}

/* Field Title */
#userpopupforchangestatus .js-ticket-premade-field-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
}

/* Select (Dropdown) element */
#userpopupforchangestatus select.js-ticket-premade-select {
    width: 100%;
    padding: 12px 15px;
    
    color: #495057;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#userpopupforchangestatus select.js-ticket-premade-select:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Button Wrapper */
#userpopupforchangestatus .js-ticket-reply-form-button-wrp {
    display: flex;
    justify-content: flex-end;
    margin-top: 15px;
}

/* Save Button */
#userpopupforchangestatus .js-ticket-save-button {
    background-color:' . esc_attr($jsst_color1) . ';
    color: #ffffff;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 600;
    
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

#userpopupforchangestatus .js-ticket-save-button:hover {
    background-color: ' . esc_attr($jsst_color2) . ';
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
}
/*
 * =================================================================
 * PRIVATE CREDENTIALS POPUP STYLES
 * =================================================================
 */
   /* Form Container */
        .js-ticket-usercredentails-form {
            background-color: #ffffff;
            padding: 35px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
button.js-ticket-usercredentail-data-add-new-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background-color: '.$jsst_color1.';
    color: #fff !important;
    border: 1px solid '.$jsst_color1.';
    border-radius: 8px;
    text-decoration: none !important;
    font-weight: 600;
    line-height: 1.5;
    transition: all 0.2s ease-in-out;
    cursor:pointer;
    box-shadow:0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
}
button.js-ticket-usercredentail-data-add-new-button:hover{
    background-color: '.$jsst_color2.';
    color: #fff !important;
    border: 1px solid '.$jsst_color2.';
    box-shadow:0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
}

        /* Fields Wrapper */
        .js-ticket-usercredentails-fields-wrp {
            display: flex;
            flex-direction: column;
            gap: 22px; /* Provides spacing between each field */
        }

        /* Individual Field Container */
        .js-ticket-select-usercredentails {
            display: flex;
            flex-direction: column;
        }

        /* Field Labels */
        .js-ticket-select-usercredentails-label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        /* Input and Textarea Fields */
        .jsst-main-up-wrapper .jsst-popup-credentials-fields {
            width: 100%;
            padding: 12px 16px;
            line-height: 1.5;
            color: #343a40;
            background-color: #ffffff;
            border: 1px solid '. $jsst_color5 .';
            border-radius: 10px;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .jsst-popup-credentials-fields:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
        }

        /* Specific style for textarea */
        textarea.jsst-popup-credentials-fields {
            min-height: 120px;
            resize: vertical; /* Allow vertical resizing */
        }

        /* Button Wrapper */
        .js-ticket-usercredentails-btn-wrp {
            display: flex;
            justify-content: center; /* Align buttons to the right */
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid ' . $jsst_color5 . ';
        }

        /* Shared Button Styles */
        .js-ticket-usercredentails-save,
        .js-ticket-usercredentails-cancel {
            padding: 12px 24px;
            
            font-weight: 600;
            border-radius: 10px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            line-height: normal;
        }

        /* Save Button */
        .js-ticket-usercredentails-save {
            background-color: ' . esc_attr($jsst_color1) . ';
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .js-ticket-usercredentails-save:hover {
            background-color: ' . esc_attr($jsst_color2) . ';
            color:' . esc_attr($jsst_color7) . ';
             transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

       

        .js-ticket-usercredentails-cancel:hover {
            background-color: ' . esc_attr($jsst_color2) . ';
            color: ' . esc_attr($jsst_color7) . ' ;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            body {
                padding: 20px;
            }
            .js-ticket-usercredentails-form {
                padding: 25px;
            }
            .js-ticket-usercredentails-btn-wrp {
                flex-direction: column-reverse; /* Stack buttons vertically on small screens */
                gap: 10px;
            }
            .js-ticket-usercredentails-save,
            .js-ticket-usercredentails-cancel {
                width: 100%;
            }
        }
/* MERGE TICKET POPUP STYLES */
.jsst-popup-wrapper.jsst-merge-popup-wrapper {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 1280px;
    max-width: 100%; /* Increased width for better layout */
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    z-index: 100000;
    overflow-y:auto;
    overflow-x:hidden;
    max-height:65%;
}

.jsst-merge-popup-wrapper .jsst-popup-header {
    color: ' . esc_attr($jsst_color2) . ';
    padding: 15px 25px;
    font-weight: 600;
    border-bottom: 1px solid ' . esc_attr($jsst_color5) . ';
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.jsst-merge-popup-wrapper .popup-header-close-img {
    width: 28px;
    height: 28px;
    cursor: pointer;
    background-image: url(\'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"%3e%3cline x1="18" y1="6" x2="6" y2="18"%3e%3c/line%3e%3cline x1="6" y1="6" x2="18" y2="18"%3e%3c/line%3e%3c/svg%3e\') !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: contain !important;
    transition: transform 0.3s ease-in-out;
}

.jsst-merge-popup-wrapper .js-col-md-12.js-form-button-wrapper{
    margin-left:0;
}
    
.jsst-merge-popup-wrapper .popup-header-close-img:hover {
    transform: rotate(90deg);
}

#popup-record-data {
    max-height: 60vh;
    overflow-y: auto;
    background-color: ' . esc_attr($jsst_color7) . ';
}


#popup-record-data .js-form-field-wrp input[type="text"] {
    flex-grow: 1;
    padding: 10px 15px;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 8px;
    color: ' . esc_attr($jsst_color4) . ';
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#popup-record-data .js-form-field-wrp input[type="text"]:focus {
    outline: none;
    border-color: ' . esc_attr($jsst_color1) . ';
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

#popup-record-data .js-form-button-wrp .button {
    background-color: ' . esc_attr($jsst_color1) . ';
    color: ' . esc_attr($jsst_color7) . ';
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

#popup-record-data .js-form-button-wrp .button:hover {
    background-color: ' . esc_attr($jsst_color2) . ';
    transform: translateY(-1px);
}

/* Styling for the ticket list */
#popup-record-data .js-ticket-con {
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 10px;
    overflow: hidden;
}

#popup-record-data .js-ticket-body {
    max-height: 40vh;
    overflow-y: auto;
}

#popup-record-data .js-col-md-12:not(:last-child) {
    border-bottom: 1px solid ' . esc_attr($jsst_color5) . ';
}

#popup-record-data .js-ticket-datatb-head,
#popup-record-data .js-ticket-data-row {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    transition: background-color 0.2s ease;
}

#popup-record-data .js-ticket-data-row:hover {
    background-color: ' . esc_attr($jsst_color3) . ';
}

#popup-record-data .js-ticket-datatb-head {
    background-color: ' . esc_attr($jsst_color3) . ';
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#popup-record-data .js-ticket-data-row .js-ticket-action {
    flex-basis: 10%;
    text-align: center;
}

#popup-record-data .js-ticket-data-row .js-ticket-subject {
    flex-basis: 40%;
}

#popup-record-data .js-ticket-data-row .js-ticket-user {
    flex-basis: 25%;
}

#popup-record-data .js-ticket-data-row .js-ticket-priority {
    flex-basis: 15%;
}

#popup-record-data .js-ticket-data-row .js-ticket-created {
    flex-basis: 20%;
    text-align: right;
}

#popup-record-data .js-ticket-action input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.js-tkt-det-left {float: left;width: calc(70% - 20px);flex:1 1 auto; padding-left:0px;}
#adminTicketform .js-tkt-det-left {width:100%;}
.js-tkt-det-right {float: left;width: 30%;}

div.jsst-popup-background {
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0px;
    left: 0px;
    z-index: 10989;
}
div#userpopupblack {
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0px;
    left: 0px;
    z-index: 9989;
}
div#userpopup #records .js-ticket-empty-msg{
    padding:15px 20px;
}
.js-ticket-priorty-btn-wrp{
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    gap: 15px;
}
div#userpopupforchangepriority
 {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 55%;
    max-width:100%;
    max-height: 55%;
    z-index: 99999;
    overflow-y: auto;
    overflow-x: hidden;
    text-align: left;
    transform: translate(-50%, -50%);
}
div#userpopupforchangepriority {
    background: #fff;
    border: 1px solid '. $jsst_color5 .';
}
div.jsst-main-up-wrapper .js-tkt-wc-order-item {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;

}
div.jsst-main-up-wrapper .js-tkt-wc-order-item select{
    border:1px solid ' . $jsst_color5 . ';
    color: ' . $jsst_color2 . ';
    padding: 12px 15px;
    line-height: initial;
    height: 50px;
    -webkit-appearance: none !important;
    border-radius: 8px;
    width: 100%;
    background-color:#fcfcfc;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($jsst_color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat #fcfcfc; /* Custom SVG arrow for select, off-white background */
    -webkit-appearance: none; /* Remove default arrow */
    -moz-appearance: none;
    appearance: none;
    padding: 14px 18px;
    min-height: 52px;
}
    
div.jsst-main-up-wrapper .js-tkt-wc-order-item button{
    padding: 13px 30px;
    border-radius: 8px;
    line-height: initial;
    
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    letter-spacing: 0.5px;
    margin-right: 0;
    background-color:' . $jsst_color1 . ';
    box-shadow:0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
    border:unset;
    color:#fff;
}
div.jsst-main-up-wrapper .js-tkt-wc-order-box{
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}
div.jsst-main-up-wrapper .js-tkt-wc-order-box .js-tkt-wc-order-item{
    width:100%;
}
div.jsst-main-up-wrapper .js-tkt-wc-order-item button:hover{
    background-color:' . $jsst_color2 . ';
    box-shadow:0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
    color:#fff;
    transform: translateY(-2px);
}
div.jsst-main-up-wrapper .js-tkt-wc-order-item .js-tkt-wc-order-item-title{
    font-weight:600;
    color:'. $jsst_color2 .';
}
div.jsst-main-up-wrapper .js-tkt-wc-order-item .js-tkt-wc-order-item-value{
    margin-left:auto;
}
div.jsst-main-up-wrapper .js-tkt-wc-order-item-link{
    font-weight:600;
}
.js-ticket-text-editor-wrp .js-ticket-text-editor-field-title{
    font-weight: 600;
    color:' . $jsst_color2 . ' ;
    margin-bottom: 15px;
}
/* merge tict new popup */
/* Popup Header */
.jsst-merge-popup-wrapper .jsst-popup-header {
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.jsst-merge-popup-wrapper .popup-header-text {
    font-size: 21px;
    font-weight: bold;
}
.jsst-merge-popup-wrapper .js-form-wrapper.jsst-form-wrapper{
    display:flex;
    flex-wrap:wrap;
    padding:20px 0;
    gap:20px;
}
.jsst-merge-popup-wrapper .close-merge {
    cursor: pointer;
    width: 24px;
    height: 24px;
    transition: transform 0.2s ease;
}

.jsst-merge-popup-wrapper .close-merge:hover {
    transform: scale(1.1);
}

/* Base Input and Button Styles */
.jsst-merge-popup-wrapper div.jsst-main-up-wrapper input,
.jsst-merge-popup-wrapper div.jsst-main-up-wrapper button,
.jsst-merge-popup-wrapper div.jsst-main-up-wrapper select,
.jsst-merge-popup-wrapper div.jsst-main-up-wrapper textarea {
    font-family: inherit;
    font-size: inherit;
    line-height: inherit;
    max-width: 100%;
}
div.jsst-main-up-wrapper input,
div.jsst-main-up-wrapper button,
div.jsst-main-up-wrapper select,
div.jsst-main-up-wrapper textarea{
    max-width:100%;
}

/* Ticket Wrapper within Popup */
.jsst-merge-popup-wrapper .js-ticket-merge-ticket-wrapper {
    padding: 20px;
}

.jsst-merge-popup-wrapper .js-ticket-merge-white-bg {
    background: #ffffff;
    border-radius: 12px;
    padding:0;
    border:unset !important;
}
span.js-edit-msg-heading{
    font-size:21px;
    font-weight:600;
    display:flex;
    width:100%;
    padding-bottom:10px;
    padding-top:30px;
}
span.js-heading.js-heading-text{
    font-size:21px;
    font-weight:600;
    display:flex;
    width:100%;
    padding-bottom:10px;
}
#popup-record-data .jsst-merge-popup-wrapper .js-col-md-12:not(:last-child){
    border:unset !important;
}
.jsst-main-up-wrapper .jsst-merge-popup-wrapper .js-col-xs-12.js-ticket-wrapper{
    margin-bottom:20px;
}
#popup-record-data .jsst-merge-popup-wrapper .js-merge-form-title.js-col-md-12{
    border-bottom:1px solid ' . $jsst_color5 . ' !important;
}
#popup-record-data .jsst-merge-popup-wrapper .js-col-md-12.js-ticket-toparea{
    border:1px solid '. $jsst_color5 .' !important;
}

/* Reused Ticket Card Styles */
.jsst-merge-popup-wrapper .js-ticket-toparea {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 31, 63, 0.08);
    padding: 15px;
    margin: 0 auto; /* Added margin for separation */
    border: 1px solid '. $jsst_color5 .';
    width: 100%;
    transition: all 0.3s ease-in-out;
    padding-bottom: 25px;
}

.jsst-merge-popup-wrapper .js-ticket-toparea:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 31, 63, 0.12);
    border: 1px solid #F7B731;
}

.jsst-merge-popup-wrapper .js-ticket-data.js-nullpadding {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
}

.jsst-merge-popup-wrapper div.js-ticket-wrapper .js-ticket-toparea .js-ticket-pic {
    width: 80px !important;
    height: 80px;
    border-radius: 50%;
    position: relative;
    padding: 0;
    margin: 0 20px;
}
.jsst-merge-popup-wrapper div.js-ticket-wrapper .js-ticket-toparea .js-ticket-pic img{
    width: 80px !important;
    height: 80px;
    border-radius:50px;
}
.jsst-merge-popup-wrapper .js-ticket-pic {
    flex: 0 0 auto;
}

.jsst-merge-popup-wrapper .js-ticket-staff-img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.jsst-merge-popup-wrapper .js-ticket-data {
    flex: 1 1 auto;
    min-width: 250px;
}

.jsst-merge-popup-wrapper .js-ticket-data1 {
    text-align: left;
    padding-left: 10px;
    border-left: 1px solid ' . $jsst_color5 . ';
    flex: 0 0 290px;
    padding-right: 0;
}

.jsst-merge-popup-wrapper .js-nullpadding {
    padding: 0;
}

.jsst-merge-popup-wrapper .js-ticket-body-data-elipses {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 5px;
    color: #606770;
}


.jsst-merge-popup-wrapper .js-ticket-value {
    font-weight: 500;
    color: #4e555f;
}

.jsst-merge-popup-wrapper a.js-ticket-merge-ticket-title {
    color: #4e555f;
    text-decoration: none;
    font-weight: 500;
}

.jsst-merge-popup-wrapper a.js-ticket-merge-ticket-title:hover {
    color: #F7B731;
}

.jsst-merge-popup-wrapper div.js-ticket-wrapper div.js-ticket-data .name span.js-ticket-value {
    color: #4e555f;
}


.jsst-merge-popup-wrapper div.js-ticket-wrapper div.js-ticket-data span.js-ticket-value {
    color: #4e555f;
}

.jsst-merge-popup-wrapper div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-val {
    color: #4e555f;
}

.jsst-merge-popup-wrapper span.js-ticket-wrapper-textcolor {
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    padding: 6px 20px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.3px;
    margin-top: 16px;
    margin-right: 10px;
    margin-left: 10px;
    max-width:100%;
}

/* Search Form */
.jsst-merge-popup-wrapper .js-popup-search {
    padding: 20px;
    background-color: #f8f9fa;
    border-top: 1px solid ' . $jsst_color5 . ';
}

.jsst-merge-popup-wrapper .js-merge-form-title {
    font-size: 21px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #4e555f;
    padding-bottom: 20px;
}

.jsst-merge-popup-wrapper .js-merge-form-wrp {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex:1 1 auto;
}

.jsst-merge-popup-wrapper .js-merge-form-value {
    flex: 1;
    height:100%;
}

.jsst-merge-popup-wrapper .js-merge-field {
    width: 100%;
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid '. $jsst_color5 .';
    background-color: #fff;
    color: #4e555f;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    height:100%;
    min-height:52px;
}

.jsst-merge-popup-wrapper .js-merge-field:focus {
    border-color: #F7B731;
    box-shadow: 0 0 0 3px rgba(247, 183, 49, 0.2);
    outline: none;
}

/* Search and Reset Buttons */
.jsst-merge-popup-wrapper .js-merge-form-btn-wrp {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding-bottom: 20px;
}

.jsst-merge-popup-wrapper .js-merge-btn input {
    min-width: 120px;
    padding: 5px 20px;
    min-height:52px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.jsst-merge-popup-wrapper input.js-search {
    color: #fff;
    background-color: ' . $jsst_color1 . ';
}

.jsst-merge-popup-wrapper input.js-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    background-color: ' . $jsst_color2 . ';

}

.jsst-merge-popup-wrapper input.js-cancel {
    background-color: #f5f2f5;
    color: #4e555f;
}

.jsst-merge-popup-wrapper input.js-cancel:hover {
    background-color: ' . $jsst_color2 . ';
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Ticket List and Overlay */
.jsst-merge-popup-wrapper .js-tickets-list-wrp {
    padding: 20px;
}

.jsst-merge-popup-wrapper .js-merge-ticket-overlay {
    position: relative;
    cursor: pointer;
}

.jsst-merge-popup-wrapper .js-over-lay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: none;
    justify-content: center;
    align-items: center;
    background-color: rgba(0, 0, 0, 0.2);
    margin-bottom:30px;
    border-radius: 12px;
}

.jsst-merge-popup-wrapper .js-merge-ticket-overlay:hover .js-over-lay {
    display: flex;
}

.jsst-merge-popup-wrapper .js-over-lay a.js-merge-btn {
    background-color: ' . $jsst_color1 . ';
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.jsst-merge-popup-wrapper .js-over-lay a.js-merge-btn:hover {
    background-color: ' . $jsst_color2 . ';
    transform: translateY(-2px);
}

/* Pagination */
.jsst-merge-popup-wrapper .jsst_userpages {
    text-align: center;
    padding: 20px 0;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    width: 100%;
}

.jsst-merge-popup-wrapper .jsst_userlink {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 5px;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 5px;
    text-decoration: none;
    color: #4e555f;
    transition: all 0.3s ease;
}

.jsst-merge-popup-wrapper .jsst_userlink.selected,
.jsst-merge-popup-wrapper .jsst_userlink:hover {
    color: white;
}
.jsst-merge-popup-wrapper .jsst_userlink:hover{
    background-color:'. $jsst_color1 .';
}
/* Cancel Button at Bottom */
.jsst-merge-popup-wrapper .js-form-button-wrapper-merge {
    text-align:center;
    padding: 20px;
    border-top: 1px solid ' . $jsst_color5 . ';
    background-color: #f8f9fa;
}
div.jsst-main-up-wrapper .jsst-merge-popup-wrapper input.js-merge-save-btn{
    background-color: ' . $jsst_color1 . ';
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
}
div.jsst-main-up-wrapper .jsst-merge-popup-wrapper input.js-merge-save-btn:hover{
    background-color: ' . $jsst_color2 . ';
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
}

.jsst-merge-popup-wrapper .js-merge-cancel-btn {
    padding: 12px 24px;
    background-color: #f5f2f5;
    color: #4e555f;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.jsst-merge-popup-wrapper .js-merge-cancel-btn:hover {
    background-color: ' . $jsst_color2 . ';
    color: '. $jsst_color7 .';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Responsiveness */
@media (max-width: 991px) {
    .jsst-merge-popup-wrapper div.js-ticket-wrapper div.js-ticket-data1 {
        padding-top: 15px;
        padding-left: 0;
        margin-top: 15px;
        border-left: none;
        border-top: 1px solid ' . $jsst_color5 . ';
    }

    .jsst-merge-popup-wrapper .js-ticket-data1 {
        flex: 0 0 auto !important;
    }
}

@media (max-width: 768px) {
    .jsst-merge-popup-wrapper .js-ticket-toparea {
        flex-direction: column;
        align-items: flex-start;
    }

    .jsst-merge-popup-wrapper .js-ticket-pic {
        margin-bottom: 16px;
        padding-right: 5px;
    }

    .jsst-merge-popup-wrapper .js-ticket-data {
        padding-right: 0;
        margin-bottom: 24px;
        width: 100%;
    }

    .jsst-merge-popup-wrapper .js-ticket-data1 {
        width: 100%;
        text-align: left;
        padding-left: 0;
        padding-top: 24px;
        border-left: 10px;
        border-top: 1px solid ' . $jsst_color5 . '
    }

    .jsst-merge-popup-wrapper .js-merge-form-wrp {
        flex-direction: column;
        gap: 0;
    }

    .jsst-merge-popup-wrapper .js-merge-form-value {
        margin-bottom: 15px;
    }
}

@media (max-width: 480px) {
    .jsst-merge-popup-wrapper .js-col-xs-12.js-col-md-12.js-ticket-toparea .js-col-xs-2 {
        margin: auto;
        padding: 0;
    }

    .jsst-merge-popup-wrapper div.js-ticket-wrapper div.js-ticket-data {
        justify-content: center;
    }

    .jsst-merge-popup-wrapper span.js-ticket-wrapper-textcolor {
        margin-top: 0 !important;
    }
    .js-tkt-det-user{
        flex-wrap:wrap;
    }
    .js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data{
        justify-content:center;
    }
}
/* =================================================================
 * UPDATED LAYOUT RULES
 * ================================================================= */

/* Main Content Area (Left Column) */
.js-tkt-det-left {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

/* Sidebar (Right Column) */
.js-tkt-det-right {
    /* MODIFIED: Set the base width to 30% of the container */
    flex:1 1 auto;
    flex-shrink: 0; /* Keeps the sidebar from shrinking if space is tight */
    display: flex;
    flex-direction: column;
    gap: 25px;
}

 .js-ticket-segmented-control-wrp {
    display: contents;
}
.js-tkt-det-hdg .js-ticket-ai-reply-status-control-label{    
    font-weight: 600;
    color: #1f2937;
    font-family: inherit;
}
div#js-ticket-ai-reply-btn {
    
}

 .js-ticket-premade-msg-wrp{padding: 25px 0;}
/* Reply Edit/Time Options Wrapper */
.js-ticket-edit-options-wrp {
    padding-top: 15px;
    margin-top: 15px;
    border-top: 1px solid ' . esc_attr($jsst_color5) . ';
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    width:100%;
}
.js-ticket-edit-options-wrp a.js-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background-color: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    color: ' . esc_attr($jsst_color4) . ';
    transition: all 0.2s ease-in-out;
}
.js-ticket-edit-options-wrp a.js-button:hover {
    background-color: ' . esc_attr($jsst_color7) . ';
    border-color: ' . esc_attr($jsst_color1) . ';
    color: ' . esc_attr($jsst_color1) . ';
}
.js-ticket-edit-options-wrp .js-ticket-thread-time {
    
    font-weight: 500;
    color: ' . esc_attr($jsst_color4) . ';
    margin-left: auto;
}

/* Thread Attachments Wrapper */
.js-ticket-attachments-wrp {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px dashed ' . esc_attr($jsst_color5) . ';
}

.js-ticket-attachments-wrp div.js_ticketattachment {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: border-color 0.2s ease;
}
.js-ticket-attachments-wrp div.js_ticketattachment:hover {
    border-color: ' . esc_attr($jsst_color1) . ';
}
.js-ticket-attachments-wrp div.js_ticketattachment span.js-ticket-download-file-title {
    font-weight: 500;
    color: ' . esc_attr($jsst_color2) . ';
}
.js-ticket-attachments-wrp div.js_ticketattachment a.js-download-button {
    padding: 5px;
    border-radius: 6px;
    transition: background-color 0.2s ease;
}
.js-ticket-attachments-wrp div.js_ticketattachment a.js-download-button.vbox-item{
    margin-left:20px;
}

.js-ticket-attachments-wrp div.js_ticketattachment a.js-download-button:hover {
    background-color: ' . esc_attr($jsst_color5) . ';
}


/* Internal Note Card */
.js-ticket-detail-box.js-ticket-post-reply-box {
    display: flex;
    gap: 20px;
    padding: 20px;
    
    background-color: #fffbeb; /* Subtle yellow to indicate a private/internal note */
    border: 1px solid #fde68a; /* A matching yellow border */
    border-radius: 12px;
    box-shadow: none; /* Internal notes can have a flatter appearance */
}

/* Left side (avatar) of the internal note */
.js-ticket-post-reply-box div.js-ticket-detail-left {
    flex-shrink: 0;
    width: 50px;
    padding: 0;
    background-color: transparent; /* Override white background */
}

.js-ticket-post-reply-box div.js-ticket-user-img-wrp {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin: 0;
}
.js-ticket-post-reply-box div.js-ticket-user-img-wrp img {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
    border-radius: 50%;
}

/* Right side (content) of the internal note */
.js-ticket-post-reply-box div.js-ticket-detail-right {
    flex: 1;
    width: auto;
    background-color: transparent; /* Inherit the card background */
}

.js-ticket-post-reply-box div.js-ticket-rows-wrapper {
    width: 100%;
    float: none;
}

.js-ticket-post-reply-box div.js-ticket-rows-wrp {
    padding: 0;
    width: 100%;
}

.js-ticket-post-reply-box .js-ticket-field-value.name {
    font-weight: 600;
    color: #b45309; /* A darker text color for better contrast on yellow */
    margin-bottom: 5px;
}
.js-ticket-post-reply-box .js-ticket-rows-wrapper .js-ticket-field-value{
    margin-bottom: 5px;
    display:flex;
    flex-wrap:wrap;
}
.js-ticket-post-reply-box .js-ticket-row .js-ticket-field-value {
    line-height: 1.6;
    color: #78350f;
}

.js-ticket-post-reply-box .js-ticket-time-stamp-wrp {
    margin: 15px 0 0 0;
    padding-top: 15px;
    border-top: 1px solid #fde68a;
}

/* "Add Internal Note" Button */
.js-ticket-thread-add-btn {
    
}
.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background-color: ' . esc_attr($jsst_color1) . ';
    color: ' . esc_attr($jsst_color7) . ' !important;
    border: 1px solid ' . esc_attr($jsst_color1) . ';
    border-radius: 8px;
    text-decoration: none !important;
    font-weight: 600;
    line-height: 1.5;
    transition: all 0.2s ease-in-out;
}
.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link:hover {
    background-color: ' . esc_attr($jsst_color2) . ';
    border-color: ' . esc_attr($jsst_color2) . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.js-ticket-thread-add-btn .js-ticket-thread-add-btn-link img {
    filter: brightness(0) invert(1);
}

/* Time Tracker Bar */
.jsst-ticket-detail-timer-wrapper {
    background-color: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 10px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
}
.jsst-ticket-detail-timer-wrapper div.timer-left, .jsst-ticket-detail-timer-wrapper div.timer-total-time {
    font-weight: 500;
    color: ' . esc_attr($jsst_color4) . ';
}
.jsst-ticket-detail-timer-wrapper div.timer-right {
    display: flex;
    align-items: center;
    gap: 15px;
}
.jsst-ticket-detail-timer-wrapper div.timer {
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
    background-color:#fff;
    padding: 5px 12px;
    border-radius: 6px;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
}
.jsst-ticket-detail-timer-wrapper div.timer-buttons {
    display: flex;
    gap: 5px;
}
.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: ' . esc_attr($jsst_color7) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
}
.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button:hover {
    border-color: ' . esc_attr($jsst_color2) . ';
    background-color: ' . esc_attr($jsst_color2) . ';
}
.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button:hover img {
    filter: brightness(0) invert(1);
}
.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button.selected {
    background-color: ' . esc_attr($jsst_color8) . ';
    border-color: ' . esc_attr($jsst_color8) . ';
}
.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button.selected img {
    filter: brightness(0) invert(1);
}


/* Premade Messages / Canned Responses */
.js-ticket-premade-msg-wrp {
    margin-bottom: 25px;
    .js-ticket-premade-msg-wrp{    padding: 25px 0;}
}
.js-ticket-premade-msg-wrp div.js-ticket-premade-field-title {

    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
    margin-bottom: 10px;
}
.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp {
    display: flex;
    flex-wrap:wrap;
    gap: 15px;
}
.js-ticket-premade-msg-wrp select.js-ticket-premade-select {
    flex-grow: 1;
    background-color: white !important;
    border-radius: 8px;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    margin-bottom: 5px;
    min-height:52px;
}
.js-ticket-premade-msg-wrp span.js-ticket-apend-radio-btn {
    display: inline-flex;
    align-items: center;
    height: 60px;
    padding: 0 15px;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 8px;
    background-color: ' . esc_attr($jsst_color7) . ';
}
.js-ticket-premade-msg-wrp span.js-ticket-apend-radio-btn input {
    margin-right: 8px;
    opacity: 1;
}
.js-ticket-premade-msg-wrp label#forappend_premade {
    font-weight: 500;
}

/* Main wrapper for the "Append Signature" section */
.js-ticket-append-signature-wrp {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid ' . esc_attr($jsst_color5) . ';
}

/* Title for the section, e.g., "Append Signature" */
.js-ticket-append-signature-wrp div.js-ticket-append-field-title {
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
    margin-bottom: 15px;
}

/* Wrapper for the signature options */
.js-ticket-append-signature-wrp div.js-ticket-append-field-wrp {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

/* Styling for each individual signature option box */
.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box {
    display: inline-flex;
    align-items: center;
    background-color: white;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    padding: 12px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box:hover {
    border-color: ' . esc_attr($jsst_color1) . ';
}

/* The checkbox input element */
.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box input {
    margin-right: 8px;
    vertical-align: middle;
    opacity: 1;
}

/* The label text for the checkbox */
.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box label {
    margin: 0;
    display: inline-block;
    font-weight:normal;
}
/* Reply Form Attachments */
/* Attachments Section */
    div.js-ticket-reply-attachments{
        display: inline-block;
        width: 100%;
        
        padding-top: 30px; /* More padding above attachment section */
        border-top: 1px solid ' . $jsst_color5 . '; /* Separator line */
    }
    div.js-ticket-reply-attachments div.js-attachment-field-title{
        display: inline-block;
        width: 100%;
        padding: 15px 0px;
        font-weight: 600;
        border-bottom: 1px solid ' . $jsst_color5 . '; /* Separator below the title */
        margin-bottom: 20px; /* Space below the title */
    }
    div.js-ticket-reply-attachments div.js-attachment-field{
        display: inline-block;
        width: 100%;
        margin-bottom: 20px; /* Space below the file input area */
    }
    /* Styling for the file input wrapper */

    div.js-ticket-reply-attachments div.js-attachment-field {
        display: inline-block;
        width: 100%;
        float: left;
        width: 100%;
        padding: 15px;
        border: 2px dashed '. $jsst_color5 .';
        border-radius: 12px;
        background-color: ' . $jsst_color3 . ';
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        min-height: 120px;
        transition: all 0.3s ease;
        
    }
   
    div.tk_attachment_value_wrapperform{
        float: left;
        width: 100%;
        padding: 15px; /* More padding inside the wrapper */
        border: 2px dashed ' . $jsst_color5 . '; /* Slightly thicker dashed border */
        border-radius: 12px; /* Softer rounded corners */
        background-color: #ffffff; /* Light gray background */
        margin-bottom: 20px; /* Space below wrapper */
        display: flex; /* Use flexbox for internal alignment */
        align-items: center; /* Vertically center content */
        flex-wrap: wrap; /* Allow content to wrap */
        min-height: 120px; /* Minimum height for a clear drop zone */
        transition: all 0.3s ease; /* Smooth transitions */
        box-sizing: border-box; /* Include padding and border in the element\'s total width and height */
    }
    div.tk_attachment_value_wrapperform:hover {
        
        background-color: #f0f8ff; /* Lighter background on hover */
    }
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
        float: left;
        width: 100%; 
        padding: 12px; /* More padding for each attachment item */
        margin: 10px; /* Margin around each item */
        position: relative;
        
        background-color: #fff; /* White background */
        border-radius: 10px; /* Rounded corners */
        display: flex; /* Flex for alignment */
        align-items: center; /* Vertically align content */
        justify-content: space-between; /* Space out content */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07); /* More prominent shadow */
    }

    div.tk_attachment_value_wrapperform span.tk_attachment_value_text input{
        width: calc(100% - 40px);
        max-width: 100%;
        max-height: 100%;
        border: none;
        color: ' . $jsst_color4 . ';
        background: transparent;
        font-size: 15px;
        cursor: pointer;
    }
    span.tk_attachment_value_text span.tk_attachment_remove{
        background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23e74c3c\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") no-repeat center center; /* More prominent red for close icon */
        background-size: 26px 26px; /* Larger icon size */
        position: absolute;
        width: 35px; /* Larger clickable area */
        height: 35px;
        top: 50%;
        right: 8px; /* Adjusted position */
        transform: translateY(-50%);
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    span.tk_attachment_value_text span.tk_attachment_remove:hover{
        opacity: 1;
        transform: translateY(-50%) scale(1.1); /* Slight scale on hover */
    }
    /* Styling for max file size and file extension info */
    span.tk_attachments_configform{
        display: inline-block;
        float: left;
        line-height: 1.6;
       
        width: 100%;
       
        color: ' . $jsst_color4 . ';
        opacity: 0.75;
        text-align: left; /* Align text to the left */
        box-sizing: border-box; /* Include padding in width */
        padding: 0 5px; /* Small horizontal padding */
    }
    /* Styling for the Add more button */
    span.tk_attachments_addform{
        position: relative;
        display: inline-block;
        padding: 15px 25px; /* More padding */
        cursor: pointer;
        margin-top: 25px; /* More space above button */
        min-width: 150px; /* Wider button */
        text-align: center;
        line-height: initial;
        background-color: ' . $jsst_color1 . '; /* Solid primary color */
        color: ' . $jsst_color7 . ';
        border: none;
        border-radius: 10px; /* Softer rounded corners */
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
    } 
    span.tk_attachments_addform:hover{
        background-color: ' . $jsst_color2 . '; /* Secondary color on hover */
        border-color: ' . $jsst_color2 . '; /* Secondary color border on hover */
        transform: translateY(-3px);
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
        filter: brightness(1.1);
    }
.js-ticket-reply-attachments div.js-attachment-field-title {
   
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
    margin-bottom: 10px;
}
.js-ticket-reply-attachments .js-attachment-field-title {
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . '; /* Title text color */
    margin-bottom: 15px;
}
/* Styling the file input element itself */
.js-ticket-reply-attachments input {
    flex-grow: 1;
    color: ' . esc_attr($jsst_color2) . '; /* Text color */
}

/* Helper text for file size and extension limits */
.js-ticket-reply-attachments .tk_attachments_configform {
    display: block;
    color: ' . esc_attr($jsst_color2) . '; /* Helper text color */
    margin-top: 15px;
    line-height: 1.5;
}

/* "Add more" button for attachments */
.js-ticket-reply-attachments .tk_attachments_addform {
    display: inline-block;
    margin-top: 15px;
    background-color: ' . esc_attr($jsst_color3) . '; /* Button background */
    color: ' . esc_attr($jsst_color2) . '; /* Button text color */
    border: 1px solid ' . esc_attr($jsst_color2) . '; /* Button border color */
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.js-ticket-reply-attachments span.tk_attachments_configform{word-break: break-all;}

.js-ticket-reply-attachments .tk_attachments_addform:hover {
    color: ' . esc_attr($jsst_color1) . '; /* Hover text color */
    border-color: ' . esc_attr($jsst_color1) . '; /* Hover border color */
}
/* Container for a single file input and its remove button */
.js-ticket-reply-attachments .tk_attachment_value_text {
    flex: 1 1 calc(50% - 15px);
    min-width: 280px;
    display: flex;
    align-items: center;
    background: ' . esc_attr($jsst_color3) . '; /* Background color */
    border: 1px solid ' . esc_attr($jsst_color2) . '; /* Border color */
    border-radius: 8px;
    padding: 8px 12px;
}
/* Wrapper for the list of file inputs */
.js-ticket-reply-attachments .tk_attachment_value_wrapperform {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}
.js-ticket-reply-attachments div.tk_attachment_value_wrapperform {
    display: flex;
    
    gap: 10px;
    width: 100%;
}
.js-ticket-reply-attachments span.tk_attachment_value_text {
    display: flex;
    align-items: center;
    background: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    padding: 5px 10px;
    border-radius: 8px;
}
.js-ticket-reply-attachments input {
    flex-grow: 1;
}
.js-ticket-reply-attachments span.tk_attachment_remove {
    margin-left: 10px;
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    opacity: 0.6;
    transition: opacity 0.2s ease;
}
.js-ticket-reply-attachments span.tk_attachment_remove:hover {
    opacity: 1;
}
.js-ticket-reply-attachments span.tk_attachments_configform {
    
    color: ' . esc_attr($jsst_color4) . ';
    margin-top: 10px;
    display: block;
}
.js-ticket-reply-attachments span.tk_attachments_addform {
    display: inline-block;
    margin-top: 15px;
    background-color: ' . esc_attr($jsst_color1) . ';
    color: ' . esc_attr($jsst_color7) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    font-weight: 500;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.js-ticket-reply-attachments span.tk_attachments_addform:hover {
    color: ' . esc_attr($jsst_color7) . ';
    border-color: ' . esc_attr($jsst_color2) . ';
    background-color: ' . esc_attr($jsst_color2) . ';
}


/* "Assign to me" & "Close on reply" Wrappers */
.js-ticket-assigntome-wrp, .js-ticket-closeonreply-wrp {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid ' . esc_attr($jsst_color5) . ';
}
.js-ticket-assigntome-wrp div.js-ticket-assigntome-field-title,
.js-ticket-closeonreply-wrp div.js-ticket-closeonreply-title {
   
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
    margin-bottom: 10px;
}
.js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp,
.js-ticket-closeonreply-wrp div.js-form-title-position-reletive-left {
    display: inline-flex;
    align-items: center;
    background-color: white;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    padding: 12px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.js-ticket-assigntome-field-wrp input{opacity: 1;}
.replyFormStatus.js-form-title-position-reletive-left input{opacity: 1;}
.js-ticket-closeonreply-wrp div.js-form-title-position-reletive-left{margin-bottom: 20px;}
.js-ticket-assigntome-wrp label,
.js-ticket-closeonreply-wrp label {

    font-weight: normal;
}
 .js-ticket-append-signature-wrp div.js-ticket-signature-radio-box input[type="checkbox"], .js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp input, .replyFormStatus.js-form-title-position-reletive-left input{
    display: inline-block !important;
    margin: 0 12px 0 0 !important;
    transform: scale(1.3);
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    border: 1px solid '. $jsst_color2 .';
    border-radius: 4px;
    background-color: #ffffff;
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
    transition: all 0.2s ease;
    opacity: 1;
}
.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box input:checked, .js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp input:checked, .replyFormStatus.js-form-title-position-reletive-left input:checked {
    background-color: ' . $jsst_color1 . ';
    border-color: ' . $jsst_color1 . ';      
}
.js-ticket-append-signature-wrp div.js-ticket-signature-radio-box input:after, .js-ticket-assigntome-wrp div.js-ticket-assigntome-field-wrp input:after, .replyFormStatus.js-form-title-position-reletive-left input:after {
    content: "";
    position: absolute;
    top: 0px; /* Adjust checkmark position */
    left: 0px; /* Adjust checkmark position */
    width: 12px; /* Larger checkmark */
    height: 12px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E"); /* White checkmark SVG */
    background-size: contain;
    background-repeat: no-repeat;
}
/* General Content Card Style */
.js-tkt-det-cnt {
    background-color: #fff;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    width: 100%;
    margin: 0;
    padding: 0;
    
}
';

/*
 * =================================================================
 * SIDEBAR (RIGHT COLUMN) STYLES
 * =================================================================
 * Styles for all modules in the right sidebar for a consistent look.
 */
$jsst_jssupportticket_css .= '
.js-tkt-det-right .js-tkt-det-cnt {
    padding:20px 15px;
}
.js-tkt-det-right .js-tkt-det-cnt.js-tkt-det-tkt-info .js-tkt-det-status{
    margin-bottom: 15px;
}
.js-tkt-det-hdg {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding-bottom: 15px;
    margin-bottom: 15px;
    border-bottom: 1px solid ' . esc_attr($jsst_color5) . ';
}

.js-tkt-det-hdg .js-tkt-det-hdg-txt {
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
}

.js-tkt-det-hdg-txt {
    display: flex;
    align-items: center;
    gap: 8px; /* Creates space between the label and the icon */
}

/* Styling for the "AI-Powered Reply Mode" label */
.js-ticket-ai-reply-status-control-label {
    font-weight: 600;
    color: #343a40;
}

/* Wrapper for the info icon to handle positioning */
.js-ticket-info-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

/* The info icon itself */
.js-ticket-info-icon img {
    width: 18px;
    height: 18px;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s ease;
}

.js-ticket-info-icon:hover img {
    opacity: 1;
}

/* Show the tooltip on hover */
.js-ticket-info-icon:hover::after {
    opacity: 1;
    visibility: visible;
    bottom: 160%; /* Animate the tooltip moving up slightly */
}

/* Main container for the digital clock display */
.timer-total-time-value {
    display: flex;
    justify-content: center; /* Center the timer boxes */
    align-items: stretch; /* Make boxes equal height */
    width: 100%;
    padding: 10px 0;
}

/* Individual box for hours, minutes, or seconds */
.timer-total-time-value .timer-box {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-basis: 65px; /* Set a base width for each box */
    padding: 12px 0;
    margin: 0 4px; /* Create a small space between boxes */
    position: relative; /* Needed for positioning the colon separator */
    background: linear-gradient(145deg, ' . esc_attr($jsst_color7) . ', ' . esc_attr($jsst_color3) . ');
    color: ' . esc_attr($jsst_color2) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 8px;
    box-shadow: 0 3px 5px rgba(0, 0, 0, 0.05);
    font-weight: 700;
    line-height: 1;
}

/* Use a pseudo-element to add the colon separator between the boxes */
.timer-total-time-value .timer-box:not(:last-child)::after {
    content: ":";
    position: absolute;
    right: -8px; /* Position the colon perfectly in the gap */
    top: 50%;
    transform: translateY(-50%);
    color: ' . esc_attr($jsst_color2) . ';
    opacity: 0.4; /* Make the colon slightly less prominent */
    font-weight: 700;
}

.js-tkt-det-hdg .js-tkt-det-hdg-btn {
    font-weight: 500;
    color: ' . esc_attr($jsst_color1) . ';
    text-decoration: none;
    transition: color 0.2s ease;
}
.js-tkt-det-hdg .js-tkt-det-hdg-btn:hover {
    color: ' . esc_attr($jsst_color2) . ';
}

/* Status & Priority Pills */
.js-tkt-det-status, .js-tkt-det-tkt-prty-txt {
    width: 100%;
    padding: 12px 20px;
    font-weight: 600;
    text-align: center;
    border-radius: 8px;
}

/* Ticket Info List */
.js-tkt-det-info-cnt {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.js-tkt-det-info-data {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    width: 100%;
}
.js-tkt-det-info-data .js-tkt-det-info-tit {
    font-weight: 500;
    color: ' . esc_attr($jsst_color2) . ';
    padding-right: 10px;
    width:fit-content;
    max-width:100%;
}
.js-tkt-det-info-data.js-tkt-det-info-val {
    text-align: left;
}
.js-tkt-det-info-data .js-tkt-det-info-val{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:5px;
}
.js-tkt-det-copy-id {
    color: ' . esc_attr($jsst_color1) . ';
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
}

/* Assigned Agent & Department Transfer */
.js-tkt-det-tkt-asgn-cnt .js-tkt-det-hdg {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid ' . $jsst_color5 . ';
    margin-bottom: 12px;
}
/* Text within the header (e.g., "Not assigned to agent") */
.js-tkt-det-tkt-asgn-cnt .js-tkt-det-hdg-txt {
    font-weight: 600;
    color: #343a40;
}
.js-tkt-det-tkt-asgn-cnt .js-tkt-det-hdg .js-tkt-det-hdg-txt {
   
    color: ' . esc_attr($jsst_color4) . ';
}
.js-tkt-det-trsfer-dep {
   display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid '. $jsst_color5 .';
}

/* "Change" button in the header */
.js-tkt-det-tkt-asgn-cnt .js-tkt-det-hdg-btn {
    font-weight: 600;
    color:' . esc_attr($jsst_color1) . ';
    text-decoration: none;
    border-radius: 6px;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.js-tkt-det-tkt-asgn-cnt .js-tkt-det-hdg-btn:hover {
    color: ' . esc_attr($jsst_color2) . ';
}
/* Text block for department info */
.js-tkt-det-trsfer-dep-txt {
    color: #495057;
}

/* Title part of the department text (e.g., "Department :") */
.js-tkt-det-trsfer-dep-txt-tit {
    font-weight: 500;
    color: '. $jsst_color2 .';
    
}
.js-ticket-reply-forms-heading {
    font-weight: 600;
    padding: 20px;
    color: #0c0c0c;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 12px;
    margin-bottom: 0;
}
.js-tkt-det-tkt-asgn-cnt .js-tkt-det-user-image img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.js-tkt-det-cnt js-tkt-det-tkt-assign.js-tkt-det-tkt-asgn-cnt .js-tkt-det-user-cnt .js-tkt-det-user-data {
    font-weight: 600;
    color: #212529;
}

.js-col-md-12.js-form-button-wrapper {
    margin-bottom: 20px;
    margin-top: 15px;
    margin-left: 125px;
}
input#ppppok:hover {background-color: ' . esc_attr($jsst_color2) . ';color: ' . esc_attr($jsst_color7) . ';}

.js-tkt-det-tkt-asgn-cnt .js-tkt-det-info-wrp {padding: 0px;}

.js-tkt-det-tkt-asgn-cnt .js-tkt-det-user-cnt .agent-email {
    font-weight: 400;
    color: #6c757d;
}

/* Agent Info (when an agent is assigned) */
.js-tkt-det-tkt-asgn-cnt .js-tkt-det-user {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 10px 0;
}


/* Other Tickets List in Sidebar */
.js-tkt-det-usr-tkt-list  {
    padding: 10px 0;
}
.js-tkt-det-usr-tkt-list:not(:last-child){
    border-bottom: 1px solid ' . esc_attr($jsst_color5) . ';
}

.js-tkt-det-usr-tkt-list .js-tkt-det-prty,
.js-tkt-det-usr-tkt-list .js-tkt-det-status {
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 500;
}
.js-tkt-det-usr-tkt-list .js-tkt-det-status {
    background-color: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    color: ' . esc_attr($jsst_color4) . ';
}

/*
         * =================================================================
         * AI-POWERED REPLY CONTAINER (MAIN WRAPPER)
         * =================================================================
         */
        /* ticket detail AI Powered Reply */ 
    .js-ticket-container {background-color: #ffffff;padding: 24px;border-radius: 12px;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);width: 100%;max-width: 960px;margin-top: 20px;margin-bottom: 20px;display: none;}
    /* Headings */
    .js-ticket-heading {font-size: 24px;font-weight: 700;color: #1a202c;margin-bottom: 24px;text-align: center;}
    /* Section containers (e.g., Current Ticket, Matching Tickets, Replies) */
    .js-ticket-section {padding: 16px;border-radius: 8px;/*margin-bottom: 24px;*/border: 1px solid;}
    .js-ticket-current-ticket-section {background-color: #eff6ff;border-color: #bfdbfe;}
    .js-ticket-current-ticket-title {display: none;}
    .js-ticket-current-ticket-id {display: none;}
    .js-ticket-current-ticket-description {font-size: 14px;color: #1d4ed8;}
    .js-ticket-post-reply-section .js-ticket-section-heading {font-size: 20px;font-weight: 600;color: #4a5568;margin-bottom: 16px;}
    .js-ticket-textarea {width: 100%;padding: 16px;border: 1px solid '. $jsst_color5 .';border-radius: 8px;resize: vertical;min-height: 150px;transition: border-color 0.2s ease, box-shadow 0.2s ease;}
    .js-ticket-textarea:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);}
    .js-ticket-button-group {display: flex;flex-direction: column;gap: 12px;margin-top: 16px;}
    @media (min-width: 640px) {.js-ticket-button-group {flex-direction: row;gap: 16px;margin-top: 0;}}
    .js-ticket-button {display: block;margin-bottom: 25px;padding: 12px 24px;border-radius: 8px;font-weight: 500;transition: background-color 0.2s ease, box-shadow 0.2s ease;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);color: #ffffff;border: none;cursor: pointer;text-decoration: none;}
    .js-ticket-button:hover {text-decoration: none !important;}
    .js-ticket-post-reply-button {background-color: #2563eb;}
    .js-ticket-post-reply-button:hover {background-color: #1d4ed8;}
    .js-ticket-ai-reply-button-wrp {display: inline-block;width: 100%;margin-top: 20px;}
    .js-ticket-ai-reply-button {background-color: #16a34a;}
    .js-ticket-ai-reply-button:hover {background-color: #15803d;}
    .js-ticket-matching-tickets-section {background-color: #f5f3ff;border-color: #ddd6fe;}
    .js-ticket-selected-tickets-header {display: flex;align-items: center;justify-content: space-between;margin-bottom: 10px;flex-wrap: wrap;gap: 10px;}
    .js-ticket-matching-tickets-section .js-ticket-section-heading {color: #6d28d9;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;max-width: 60%;margin: 15px 0 10px;font-weight: bold;line-height: initial;}
    .js-ticket-list {list-style: none;padding: 8px;margin: 0 -8px;display: flex;flex-direction: column;gap: 12px;max-height: 500px;overflow-y: auto;}
    .js-ticket-list::-webkit-scrollbar {width: 8px;}
    .js-ticket-list::-webkit-scrollbar-track {background: #f1f1f1;border-radius: 10px;}
    .js-ticket-list::-webkit-scrollbar-thumb {background: #cbd5e1;border-radius: 10px;}
    .js-ticket-list::-webkit-scrollbar-thumb:hover {background: #94a3b8;}
    .js-ticket-list-item {background-color: #ffffff;padding: 16px;border-radius: 8px;box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);cursor: pointer;transition: background-color 0.15s ease-in-out;}
    .js-ticket-list-item:hover {background-color: #f9fafb;}
    .js-ticket-list-item p {margin: 0;}
    .js-ticket-list-item .js-ticket-title {font-weight: 500;margin-top: 4px;margin-bottom: 4px;}
    .js-ticket-list-item .js-ticket-id {display: -webkit-box;-webkit-line-clamp: 5;-webkit-box-orient: vertical;overflow: hidden;}
    .js-ticket-selected-replies-section {background-color: #ecfdf5;border-color: #a7f3d0;}
    .js-ticket-selected-replies-header {display: flex;align-items: center;justify-content: space-between;margin-bottom: 10px;}
    .js-ticket-selected-replies-header .js-ticket-section-heading {font-size: 22px;font-weight: 600;color: #047857;margin-bottom: 0;margin-top: 0;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;max-width: 50%;line-height: initial;font-family: inherit;}
    .js-ticket-close-button {/*background-color: #1E40AF;*/background-color: #16A34A;color: #FFFFFF;padding: 8px 16px;border-radius: 8px;font-weight: 500;transition: background-color 0.2s ease;cursor: pointer;border: none;}
    .js-ticket-close-button:hover {background-color: #2b2b2b;}
    #js-ticket-close-tickets-btn {background-color: #6d28d9;color: #FFF;}
    #js-ticket-close-tickets-btn:hover {background-color: #2b2b2b;}
    .js-ticket-replies-content {display: flex;flex-direction: column;gap: 24px;max-height: 500px;overflow-y: auto;padding: 8px;margin: 0 -8px;}
    /* Custom scrollbar for reply content */
    .reply-content::-webkit-scrollbar {width: 8px;}
    .reply-content::-webkit-scrollbar-track {background: #f1f1f1;border-radius: 10px;}
    .reply-content::-webkit-scrollbar-thumb {background: #cbd5e1;border-radius: 10px;}
    .reply-content::-webkit-scrollbar-thumb:hover {background: #94a3b8;}
    .js-ticket-reply-item {background-color: #ffffff;padding: 20px;border-radius: 8px;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);border: 1px solid #a7f3d0;}
    .js-ticket-reply-header {display: flex;justify-content: space-between;align-items: center;margin-bottom: 12px;}
    .js-ticket-reply-header .js-ticket-reply-id {font-weight: 600;color: #059669;}
    .js-ticket-reply-header .js-ticket-reply-timestamp {font-size: 14px;}
    .js-ticket-reply-text {line-height: 1.625;display: -webkit-box;-webkit-line-clamp: 10;-webkit-box-orient: vertical;overflow: hidden;}
    .js-ticket-reply-actions {display: flex;gap: 8px;margin-top: 10px; /* Space between text and action buttons */justify-content: flex-end; /* Align buttons to the right */}
    .js-ticket-reply-action-btn {background-color: #4CAF50; /* Green */color: white;padding: 8px 12px;border: none;border-radius: 5px;cursor: pointer;font-size: 14px;transition: background-color 0.2s ease;}
    .js-ticket-reply-action-btn.copy-btn {background-color: #ff9800; /* Orange */}
    .js-ticket-reply-action-btn.copy-btn:hover {background-color: #2b2b2b;}
    .js-ticket-reply-action-btn.append-btn {background-color: #1578e8;}
    .js-ticket-reply-action-btn.append-btn:hover {background-color: #2b2b2b;}
    /* Dropdown style */
    .js-ticket-filter-group {display: flex;align-items: center;gap: 10px;margin-left: auto;margin-right: 5px;margin-bottom: 0px;}
    .js-ticket-filter-label {font-weight: 500;}
    .js-ticket-filter-select {padding: 8px 12px;border: 1px solid '. $jsst_color5 .';border-radius: 8px;background-color: white;cursor: pointer;font-size: 14px;appearance: none; /* Remove default dropdown arrow */-webkit-appearance: none;-moz-appearance: none;background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2C118.8L146.2%2C259.6L5.4%2C118.8z%22%2F%3E%3C%2Fsvg%3E"); /* Custom arrow */background-repeat: no-repeat;background-position: right 10px center;background-size: 12px;transition: border-color 0.2s ease, box-shadow 0.2s ease;}
    .js-ticket-filter-select:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);}
    /* Modal styles */
    .js-ticket-modal {position: fixed;inset: 0;background-color: rgba(0, 0, 0, 0.5);display: flex;align-items: center;justify-content: center;z-index: 50;}
    .js-ticket-modal-content {background-color: #ffffff;padding: 24px;border-radius: 12px;box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);max-width: 384px;width: 100%;text-align: center;border: 1px solid '. $jsst_color5 .';}
    .js-ticket-modal-message {font-size: 18px;font-weight: 500;color: #1a202c;margin-bottom: 16px;}
    .js-ticket-modal-close-button {background-color: #2563eb;color: #ffffff;padding: 8px 20px;border-radius: 8px;font-weight: 500;transition: background-color 0.2s ease, box-shadow 0.2s ease;cursor: pointer;border: none;}
    .js-ticket-modal-close-button:hover {background-color: #1d4ed8;}
    /* Utility for hiding elements */
    .js-ticket-hidden {display: none;}
    /* Marked as AI-Powered Reply Feature  */
    div.js-ticket-ai-reply-status-wrapper {float: left;width: 100%;padding: 15px 0px 0px;box-sizing: border-box;}
    div.js-ticket-ai-reply-status-wrapper label {display: inline-block;margin-bottom: 10px;font-weight: 600;color: #444;}
    div.js-ticket-segmented-control-wrp {padding: 15px 0;}
    div.js-ticket-segmented-control {display: flex;border: 1px solid '. $jsst_color5 .';border-radius: 6px;overflow: hidden;width: 100%;max-width: 100%;box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);flex-wrap: wrap;}
    button.js-ticket-segmented-control-option {flex: 1;padding: 10px 18px;border: none;background-color: #f6f7f7;color: #40464d;cursor: pointer;font-size: 16px !important;font-weight: 500 !important;transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;outline: none;text-align: center;white-space: nowrap;line-height: 1.3em !important;}
    button.js-ticket-segmented-control-option:not(:last-child) {border-right: 1px solid #c3c4c7;}
    button.js-ticket-segmented-control-option:hover {background-color: #e0e0e0;}
    button.js-ticket-segmented-control-option.active {box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.1);font-weight: 700;}
    button.js-ticket-segmented-control-option.js-ticket-default.active {background-color: #808080;color: #ffffff;}
    button.js-ticket-segmented-control-option.js-ticket-enable.active {background-color: #28a745;color: #ffffff;}
    button.js-ticket-segmented-control-option.js-ticket-disable.active {background-color: #dc3545;color: #ffffff;}
    button.js-ticket-segmented-control-option:focus {box-shadow: 0 0 0 2px #2196f3;}
    .js-tkt-det-tkt-prty-txt.js-ticket-segmented-control-wrp button.js-ticket-segmented-control-option {padding: 10px 0 !important;border:1px solid '.$jsst_color5.';}
    label.js-ticket-ai-reply-status-control-label {font-size: 20px !important;}
    .js-ticket-info-icon-wrapper {position: relative;display: inline-block;cursor: help;padding: 0 5px;}
    .js-ticket-info-icon {font-size: 18px;color: #6c757d;transition: color 0.2s ease;}
    .js-ticket-info-icon-wrapper.tooltip-active .js-ticket-info-icon::after, .js-ticket-info-icon-wrapper.tooltip-active .js-ticket-info-icon::before {opacity: 1;visibility: visible;display:flex;flex-wrap:wrap;}
    .js-ticket-info-icon {display:flex;flex-wrap:wrap; font-size: 18px;color: #6c757d;transition: color 0.2s ease;}
    .js-ticket-info-icon::after, .js-ticket-info-icon::before, .status-segment::after, .status-segment::before {box-sizing: border-box;content: attr(data-tooltip);position: absolute;top: calc(100% + 20px);left: -25px;transform: translateX(-50%);background-color: #343a40;color: #fff;padding: 12px 18px;border-radius: 10px;font-size: 13.5px;line-height: 1.5;white-space: normal;width: 200px;text-align: center;opacity: 0;visibility: hidden;transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;z-index: 1000;box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);}
    .js-ticket-info-icon::before, .status-segment::before {content: "";top: calc(100% + 13px);left: 50%;transform: translateX(-50%) rotate(45deg);width: 14px;height: 35px;background-color: #343a40;z-index: 1000;}
    .js-ticket-info-icon img {width: 20px;}
        /* AI Reply */
    .js-tkt-det-tkt-prty div.js-ticket-segmented-control-wrp {border-top: 1px solid ' . $jsst_color5 . ';}

    .js-ticket-container {border: 1px solid ' . $jsst_color5 . ';}
    .js-ticket-list-item {border: 1px solid ' . $jsst_color5 . ';}
    .js-ticket-list-item .js-ticket-title {color: ' . $jsst_color2 . ';}
    .js-ticket-ai-powered-reply-title {color: ' . $jsst_color2 . ';}
    .js-ticket-ai-powered-reply-text {color: ' . $jsst_color4 . ';}
    .js-ticket-list-item .js-ticket-id {color: ' . $jsst_color4 . ';}
    .js-ticket-filter-label {color: ' . $jsst_color2 . ';}
    .js-ticket-filter-select {color: ' . $jsst_color2 . ';}
    .js-ticket-reply-text {color: ' . $jsst_color4 . ';}
    .js-ticket-reply-header .js-ticket-reply-timestamp {color: ' . $jsst_color4 . ';}

        /*
         * =================================================================
         * MATCHING TICKETS SECTION
         * =================================================================
         */

        #js-ticket-matching-tickets-list {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow-y: auto;
            padding-right: 10px;
            flex-grow: 1; /* Allows list to fill space */
            max-height: 60vh; /* Using viewport height for flexibility */
        }

        /* Custom Scrollbar for Webkit browsers */
        #js-ticket-matching-tickets-list::-webkit-scrollbar,
        #js-ticket-selected-ticket-replies-content::-webkit-scrollbar {
            width: 6px;
        }
        #js-ticket-matching-tickets-list::-webkit-scrollbar-track,
        #js-ticket-selected-ticket-replies-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        #js-ticket-matching-tickets-list::-webkit-scrollbar-thumb,
        #js-ticket-selected-ticket-replies-content::-webkit-scrollbar-thumb {
            background: #adb5bd;
            border-radius: 10px;
        }
        #js-ticket-matching-tickets-list::-webkit-scrollbar-thumb:hover,
        #js-ticket-selected-ticket-replies-content::-webkit-scrollbar-thumb:hover {
            background: #6c757d;
        }

        /* Individual Ticket Card */
        .js-ticket-list-item {
            background-color: #ffffff;
            border: 1px solid '. $jsst_color5 .';
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .js-ticket-list-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
        }
        .content-area ol li, .content-area ul li {
            margin: 0 0 .25rem 1rem;
            border-left: 4px solid ' . $jsst_color1 . ';
        }
        
        .js-ticket-list-item.selected {
            border-left: 4px solid ' . $jsst_color1 . ';
            background-color: #f0f8ff;
            border-color: ' . $jsst_color1 . ';
        }

        .js-ticket-list-item .js-ticket-id {
            color: #6c757d;
            margin: 0 0 10px 0;
        }

        .js-ticket-list-item .js-ticket-title {
            /* NEW: Fluid font size for ticket titles */
            font-weight: 600;
            color: #343a40;
            margin: 0 0 10px 0;
        }

        /* Ticket Description (with multi-line ellipsis) */
        .js-ticket-list-item p.js-ticket-description {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Show max 2 lines */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /*
         * =================================================================
         * SELECTED REPLIES SECTION
         * =================================================================
         */

        #js-ticket-selected-ticket-replies-content {
            overflow-y: auto;
            padding-right: 10px;
            flex-grow: 1; /* Allows content to fill space */
            max-height: 60vh; /* Using viewport height for flexibility */
        }

        /* Individual Reply Card (Assumed Structure) */
        .js-ticket-reply-item {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .js-ticket-reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid ' . $jsst_color5 . ';
        }

        .js-ticket-reply-id {
            font-weight: 600;
            color: #343a40;
        }

        .js-ticket-reply-timestamp {
            color: #6c757d;
        }

        .js-ticket-reply-text {
            color: #495057;
            line-height: 1.6;
        }

        .js-ticket-reply-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid ' . $jsst_color5 . ';
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap; /* Allows buttons to wrap on small screens */
        }

        .js-ticket-reply-actions button {
            padding: 6px 15px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .js-ticket-reply-actions button:hover {
            opacity: 0.85;
        }

        /*
         * =================================================================
         * MODAL MESSAGE STYLES
         * =================================================================
         */
        .js-ticket-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10010;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            padding: 15px;
        }
        
        .js-ticket-modal.visible {
            opacity: 1;
            visibility: visible;
        }

        .js-ticket-modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transform: scale(0.9);
            transition: transform 0.3s ease;
            width: 100%;
            max-width: 400px;
        }
        
        .js-ticket-modal.visible .js-ticket-modal-content {
            transform: scale(1);
        }

        .js-ticket-modal-message {
            margin: 0 0 20px 0;
            
            color: #343a40;
        }

        .js-ticket-modal-close-button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .js-ticket-modal-close-button:hover {
            background-color: #0056b3;
        }
        
        /* * =================================================================
         * RESPONSIVE ADJUSTMENTS
         * =================================================================
        */
        
        /* For tablets and smaller desktops */
        @media (max-width: 992px) {
            .js-ticket-container {
                /* UPDATED: Switch to a single column layout */
                grid-template-columns: 1fr;
            }
            body {
                padding: 15px;
            }
            .js-ticket-reply-attachments span.tk_attachments_configform{word-break: break-all;}
        }
        
        /* For mobile phones */
        @media (max-width: 768px) {
            .js-ticket-selected-tickets-header,
            .js-ticket-selected-replies-header {
                /* UPDATED: Stack header items vertically and align to the start */
                flex-direction: column;
                align-items: flex-start;
                .js-ticket-reply-attachments span.tk_attachments_configform{word-break: break-all;}
            }
            body {
                padding: 10px;
            }
            .js-ticket-container {
                padding: 10px;
            }
            .js-ticket-section {
                padding: 15px;
            }
            div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
                width: calc(50% - 20px); /* Two columns on tablets */
            }
        }
        /* For mobile phones */
        @media (max-width: 650px) {
            .js-tkt-det-right{width:100%;}
            .js-tkt-det-left{width:100%;}
            .js-ticket-reply-attachments span.tk_attachments_configform{word-break: break-all;}
        }
        @media (max-width: 480px) {
            .js-ticket-ai-powered-reply-wrapper{flex-wrap:wrap;}
            div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
                width: calc(100% - 20px); /* Single column on mobile */
            }
            .js-tkt-det-user .js-tkt-det-user-image{display:flex;justify-content:center;}
            .js-ticket-thread .js-ticket-thread-image{display:flex;justify-content:center;}
            .js-ticket-thread .js-ticket-thread-cnt{padding:20px 0;}
            .js-tkt-det-left{padding-left:0px;}
            .js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp{flex-wrap:wrap;}
            span.js-ticket-apend-radio-btn{margin-top:0px !important;}
            .js-tkt-det-usr-tkt-list .js-tkt-det-prty{width:100%;}
            .js-tkt-det-usr-tkt-list .js-tkt-det-user .js-tkt-det-user-image{justify-content:center;width:100% !important;}
            .js-ticket-thread .js-ticket-thread-cnt-btm{flex-wrap:wrap;gap:10px;}
            .js-ticket-thread-actions{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;}
            div.js-ticket-attachments-wrp div.js_ticketattachment{flex-wrap:wrap;}
            .jsst-ticket-detail-timer-wrapper div.timer-right{flex-wrap:wrap;justify-content:center;}
        }

';

/*
 * =================================================================
 * MAIN CONTENT (LEFT COLUMN) STYLES
 * =================================================================
 * Styles for the ticket header, thread, replies, and forms.
 */
$jsst_jssupportticket_css .= '
/* User/Ticket Info Header */
.js-tkt-det-info-wrp {
    padding: 20px;
}
.js-tkt-det-user-cnt .js-tkt-det-user-data {
    margin-bottom:10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.js-tkt-det-user {
    display: flex;
    width: 100%;
    gap: 20px;
}
.js-tkt-det-usr-tkt-list .js-tkt-det-prty, .js-tkt-det-usr-tkt-list .js-tkt-det-status {
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: 500;
    display:flex;
    align-items:center;
    width:fit-content;
}
.js-tkt-det-user-image{
    position:relative;
}
.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 3px;
}
.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-read-status-wrp{
    margin-left:auto;
    position:relative;
}
.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-read-status-btn {
    padding:5px 5px 8px;
    border:1px solid '.$jsst_color5.';
    border-radius:10px;
    cursor: pointer;
    display:flex;
    flex-wrap:wrap;
}
.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data .js-ticket-thread-read-status-wrp .js-ticket-thread-read-status-detail {
    position: absolute;
    top: 37px;
    right: 15px;
    background-color: '.$jsst_color3.';
    padding:10px;
    width:100%;
    min-width:200px;
    border:1px solid '.$jsst_color4.';
    border-radius:10px;
}
.js-tkt-det-user-image img {
    max-width:100%;
    max-height:100%;
    border-radius: 50%;
    position:absolute;
    top:0;
    bottom:0;
    left:0;
    right:0;
    margin:auto;
}
.js-tkt-det-left .js-tkt-det-user-image{
    width: 64px;
    height: 64px;
}
.js-tkt-det-left .js-tkt-det-user-image img {
    margin-top:10px;
}
span.js-tkt-det-prty {color:white;}



/* Action Buttons (Edit, Close, etc.) */
.js-tkt-det-actn-btn-wrp {
    
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid ' . esc_attr($jsst_color5) . ';
}
.js-tkt-det-left .js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    color: ' . esc_attr($jsst_color4) . ';
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    margin: 5px;
}
.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn:hover {
    border-color: ' . esc_attr($jsst_color1) . ';
    background-color: ' . esc_attr($jsst_color7) . ';
    color: ' . esc_attr($jsst_color1) . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.07);
}
.js-tkt-det-actn-btn-wrp .js-tkt-det-actn-btn img {
    width: 25px;
    height: 25px;
}

/* Ticket Thread Title */
.js-tkt-det-title.thread, .js-tkt-det-title {

    font-weight: 600;
    padding: 20px;
    color: ' . esc_attr($jsst_color2) . ';
    background-color: #fff;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 12px;
    margin-bottom: 0;
}

/* Ticket Thread & Replies */
.js-ticket-thread {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
    background-color: #fff;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 12px;
}
.js-ticket-thread.internal-note {
    background-color: #fffbeb; /* Light yellow for internal notes */
    border-color: #fde68a;
}
.js-ticket-thread .js-ticket-thread-image {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
}
.js-ticket-thread .js-ticket-thread-image img {
    border-radius: 50%;
    width: 50px;
    height: 50px;
}
.js-ticket-thread .js-ticket-thread-cnt {
    width: calc(100% - 70px);
}
.js-ticket-thread .js-ticket-thread-person {
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
}
.js-ticket-thread .note-msg {
    margin-top: 10px;
}
.js-ticket-thread .note-msg p {
    margin-top: 0;
}
.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data.note-msg{
    flex-direction:column;
    align-items:flex-start;
}
.js-ticket-thread .js-ticket-thread-cnt .js-ticket-thread-data.note-msg img{
    max-width:100%;
}
.js-ticket-thread .js-ticket-thread-cnt-btm {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid ' . esc_attr($jsst_color5) . ';
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 25px;
    
}
.js-ticket-thread .js-ticket-thread-cnt-btm .js-ticket-thread-date {
    color: ' . esc_attr($jsst_color4) . ';
}
.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn,.js_ticketattachment .button {
    color: ' . esc_attr($jsst_color4) . ';
    text-decoration: none;
    
    padding: 5px 10px;
    border: 1px solid transparent;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    background-color: #f9fafb;
    border: 1px solid '. $jsst_color5 .';
}
.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn{
    gap:8px;
}
.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn, .js_ticketattachment .button {
    font-family: inherit;
   
}
.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn, .js_ticketattachment .button:hover {
    border-color: ' . esc_attr($jsst_color2) . ';
    background-color: ' . esc_attr($jsst_color2) . ';
    color: ' . esc_attr($jsst_color7) . ';
}
#popupforinternalnote .js-ticket-internalnote-field-title{
    font-weight:600;
    color:' . esc_attr($jsst_color2) . ';
}
#popupforinternalnote .js-attachment-field:hover{border-color:' . esc_attr($jsst_color5) . ';}
.js-form-wrapper .js-form-title{
    font-weight:600;
    margin-bottom:10px;
     color:' . esc_attr($jsst_color4) . ';
}
.js-form-wrapper .js-form-value select{
    width: 100%;
    padding: 12px 15px;
    color: #495057;
    background-color: #ffffff;
    border: 1px solid #d1d1d1;
    border-radius: 8px;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
/* Attachments */
.js-ticket-attachments-wrp {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px dashed ' . esc_attr($jsst_color5) . ';
    width: 100%;
}
div.js-ticket-attachments-wrp div.js_ticketattachment {
    display: flex;
    align-items: center;
    background-color: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}
div.js-ticket-attachments-wrp div.js_ticketattachment .js_ticketattachment_fname{
    margin-right:auto;
}
div.js-ticket-attachments-wrp div.js_ticketattachment .js-ticket-download-file-title{
    margin-right:auto;
}
a.js-all-download-button {
    display: inline-block;
    background-color: ' . esc_attr($jsst_color1) . ';
    color: ' . esc_attr($jsst_color7) . ' !important;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s ease;
}
a.js-all-download-button:hover {
    background-color: ' . esc_attr($jsst_color2) . ';
}
.js-ticket-reply-form-button-wrp{
    display: inline-block;
    padding: 5px 0px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s ease;
    width: 100%;
    margin-top:15px;
    }
   .js-ticket-reply-form-button-wrp input:hover {
    background-color: ' . ($jsst_color2) . ';
    color: ' . ($jsst_color7) . ';
    box-shadow:0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
    transform: translateY(-3px);
}
.js-ticket-reply-form-button-wrp input{
    display: inline-block;
    color: ' . ($jsst_color7) . ' !important;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    border:unset;
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
    transition: background-color 0.2s ease;
    width: 100%;
    background-color: ' . ($jsst_color1) . ';
    transition: all 0.3s ease;
    color: ' . ($jsst_color7) . ';}
.js-ticket-priorty-btn-wrp {
    display: flex
;
    justify-content: center;
    margin-bottom: 20px;
    gap: 15px;
}
input#changepriority:hover {background-color: ' . esc_attr($jsst_color2) . '!important;
}

/* Close Buttons in AI Feature Headers */

.js-ticket-close-button:hover,
#js-ticket-close-tickets-btn:hover {
    border-color: ' . esc_attr($jsst_color2) . ';
    background-color: ' . esc_attr($jsst_color2) . ';
    color: ' . esc_attr($jsst_color7) . ';
}

';

';






/*
 * =================================================================
 * FORMS & INPUTS STYLES
 * =================================================================
 * Modern styling for all form elements, including the main reply form.
 */

/* Reply Form Wrapper */
.js-det-tkt-rply-frm {
    padding: 25px;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 12px;
    background-color: ' . esc_attr($jsst_color7) . ';
}
.js-det-tkt-rply-frm .js-tkt-det-title {
  
    font-weight: 600;
    padding: 20px;
    color: #1f2937;
    background-color: #ffffff;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 12px;
    margin-bottom: 0;
}

/* Premade/Canned Responses */
.js-ticket-premade-msg-wrp {
    margin-bottom: 20px;
}
.js-ticket-premade-field-title {
    font-weight: 500;
    margin-bottom: 8px;
    color: ' . esc_attr($jsst_color2) . ';
}
/* The container for the "Append" checkbox option */
.js-ticket-apend-radio-btn {
    display: inline-flex;
    align-items: center;
    padding: 10px;
    cursor: pointer;
}
.js-ticket-premade-field-wrp {
    display: flex;
    align-items: center;
    gap: 15px;
}

select.js-ticket-premade-select {
    flex-grow: 1;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    height: 44px;
    border-radius: 8px !important;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    padding: 10px;
}

/* Text Editor (wp_editor) */
.wp-editor-wrap {
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 8px;
}
.jsst-main-up-wrapper .wp-editor-container {
    border-radius: 0 0 8px 8px;
}

/* Checkboxes & Radios */
.js-ticket-append-signature-wrp, .js-ticket-assigntome-wrp, .js-ticket-closeonreply-wrp {
    margin-top: 20px;
}
.jsst-formfield-radio-button-wrap, .replyFormStatus {
    background-color: ' . esc_attr($jsst_color3) . ';
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    padding: 10px 15px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    margin-right: 10px;
}
label[for="closeonreply"], label[for="ownsignature"], label[for="assigntome"] {
    margin: 0 0 0 8px;
    font-weight: 500;
}

#userpopupforchangestatus .js-ticket-reply-form-button-wrp{}
js-tkt-det-hdg-txt .label{font-weight: 700;}
/* Main Form Button */
.js-ticket-reply-form-button-wrp {
    
 
    text-align: left;
}
input.js-ticket-save-button {
    background-color: ' . esc_attr($jsst_color1) . ' !important;
    color: ' . esc_attr($jsst_color7) . ' !important;
    border: none !important;
    padding: 12px 24px !important;
   
    font-weight: 600;
    border-radius: 8px !important;
    cursor: pointer;
    transition: all 0.2s ease;
}
input.js-ticket-save-button:hover {
    background-color: ' . esc_attr($jsst_color2) . ' !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
}
';

/*
 * =================================================================
 * POPUPS & MODALS STYLES
 * =================================================================
 * A unified design for all popups (History, Change Priority, etc.).
 */
$jsst_jssupportticket_css .= '
div#userpopupblack, div.jsst-popup-background {
    background: rgba(31, 41, 55, 0.7);
    backdrop-filter: blur(4px);
}
div#userpopup, div.jsst-popup-wrapper, div#usercredentailspopup, div#popupforagenttransfer, div#popupfordepartmenttransfer, div#userpopupforchangestatus {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 55%;
    max-width:100%;
    background-color:#fff;
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    z-index: 99999;
    overflow: hidden;
    max-height:100%;
    overflow-y:auto;
}

.jsst-popup-header, .js-ticket-priorty-header, .js-ticket-usercredentails-header, #userpopup.js-row js-ticket-popup-row {
    background-color:' . esc_attr($jsst_color3) . ';
    color: ' . esc_attr($jsst_color2) . ';
    padding: 15px 20px;
    font-weight: 600;
    border-bottom: 1px solid ' . esc_attr($jsst_color5) . ';
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 21px;
}

.popup-header-close-img, .close-history, .close-credentails {
    width: 24px;
    height: 24px;
    cursor: pointer;
   
    background-size: contain !important;
    transition: transform 0.2s ease;
}
.popup-header-close-img, .close-history, .close-credentails {
    width: 24px;
    height: 24px;
    cursor: pointer;
    transition: transform 0.2s ease;
    background-image: url(\'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"%3e%3cline x1="18" y1="6" x2="6" y2="18"%3e%3c/line%3e%3cline x1="6" y1="6" x2="18" y2="18"%3e%3c/line%3e%3c/svg%3e\') !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: contain !important;
}
.popup-header-close-img:hover, .close-history:hover, .close-credentails:hover {
    transform: rotate(90deg);
}

.js-ticket-priorty-fields-wrp, #popupforagenttransfer form, #popupfordepartmenttransfer form {
    padding: 25px;
}
form#jsst-note-edit-form{
    padding: 25px 0;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
form#jsst-note-edit-form .js-form-wrapper{
    display:flex;
    flex-wrap:wrap;
    width:100%;
    gap:10px;
}
form#jsst-note-edit-form .js-form-wrapper .js-form-title{
    font-weight:600;
    color:'.$jsst_color2.';
}

#prioritytemp, #departmentid, #staffid, #status {
    width: 100% !important;
    padding: 10px !important;
    border-radius: 8px !important;
    border: 1px solid ' . esc_attr($jsst_color5) . ' !important;
    background-color: #fff !important;

}
 .js-ticket-reply-form-button-wrp {
    padding: 20px 25px;
   
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#staffid {margin-bottom: 5px;}
.js-ticket-priorty-btn-wrp input {
    padding: 10px 20px !important;
    border-radius: 8px !important;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
input.js-ticket-priorty-save {
    background-color: ' . esc_attr($jsst_color1) . ' !important;
    color: ' . esc_attr($jsst_color7) . ' !important;
    border: 1px solid ' . esc_attr($jsst_color1) . ' !important;
}
input.js-ticket-priorty-save:hover{
    border-color:'.$jsst_color2.' !important;
}
input.js-ticket-priorty-cancel {

}
input.js-ticket-priorty-cancel:hover {
    border-color: ' . esc_attr($jsst_color2) . ';
    color: ' . esc_attr($jsst_color2) . ';
}

/* History Popup Table */
.js-ticket-history-table-wrp {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}
.js-ticket-history-table-wrp table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom:20px;
}
.js-ticket-history-table-wrp th {
    background-color: #f5f2f5;
    color: ' . esc_attr($jsst_color2) . ';
    text-align: left;
    font-weight: 600;
}
.js-ticket-history-table-wrp th, .js-ticket-history-table-wrp td {
    padding: 12px 15px;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
}
.js-ticket-history-table-wrp tbody tr:nth-child(odd) {
    background-color: ' . esc_attr($jsst_color3) . ';
}
#popupforinternalnote .timer-button:hover {
    background-color: ' . esc_attr($jsst_color2) . '!important;
    border-color: ' . esc_attr($jsst_color2) . '!important;
}


div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button, #popupforinternalnote .timer-button
 { 
    background-color: ' . esc_attr($jsst_color1) . ';
    border: 1px solid ' . esc_attr($jsst_color1) . ';
    border-radius: 50%;
    padding: 6px;
    align-items: center;
    display: inline-flex;
}

';

/*
 * =================================================================
 * AI-POWERED REPLY & ADVANCED FEATURES
 * =================================================================
 * Styles for the AI-powered reply feature and other interactive elements.
 */
$jsst_jssupportticket_css .= '
.js-ticket-ai-powered-reply-wrapper {
    display: flex;
    align-items: center;
    gap: 20px;
    background-color: #d9ffe7;
    border: 1px solid #16a34a;
    padding: 15px;
    border-radius: 12px;
    margin: 20px 0;
}
.js-ticket-ai-powered-reply-wrapper .js-ticket-ai-powered-reply-content-wrp{
    display:flex;
    flex-wrap:wrap; 
    align-items:center;
    gap:10px;
    flex:1 1 auto;
}
.js-ticket-ai-powered-reply-wrapper .js-ticket-ai-powered-reply-content-wrp .js-ticket-ai-powered-reply-content{
    margin-right:auto;
}
.js-ticket-ai-powered-reply-icon img { width: 50px; }
.js-ticket-ai-powered-reply-title { font-weight: 600; color: ' . esc_attr($jsst_color2) . ';  }
.js-ticket-ai-powered-reply-text {font-size: 15px; }
.js-ticket-ai-powered-reply-action a {
   background-color: #15803d; /* Darker Green */
    color: #fff !important;
    padding: 10px 15px;
    text-decoration: none;
    font-weight: 600;
    border-radius: 8px;
    transition: background-color 0.2s ease;
    display:flex;
    align-items:center;
}
.js-ticket-ai-powered-reply-action a:hover {
    background-color: #025621; /* Darker Green */
}


/*
 * =================================================================
 * MISCELLANEOUS & LEGACY STYLES
 * =================================================================
 * Overrides for remaining classes to ensure a cohesive design.
 */
.js-ticket-thread .js-ticket-thread-actions .js-ticket-thread-actn-btn:hover{background-color: ' . esc_attr($jsst_color1) . '; color:' . esc_attr($jsst_color7) . ';    border-color: ' . esc_attr($jsst_color1) . ';}
.js-tkt-det-tkt-custm-flds .js-tkt-det-info-data{
    margin-bottom: 10px;
    align-items: flex-start;
    justify-content:flex-start;
}
.js-tkt-det-tkt-custm-flds .js-tkt-det-info-data .js_ticketattachment{
    display:flex;
    align-items:flex-start;
    flex-wrap:wrap;
    gap:10px;
}
div.jsst-ticket-detail-timer-wrapper {
    background-color: #fff;
    border: 1px solid ' . esc_attr($jsst_color5) . ';
    border-radius: 12px;
    padding: 15px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
        margin-top: 20px;
}
div.jsst-ticket-detail-timer-wrapper .timer-right .timer {
   
    font-weight: 600;
    color: ' . esc_attr($jsst_color2) . ';
}
#popupforinternalnote .timer-button:hover{
     background-color: ' . esc_attr($jsst_color1) . ';
    border-color: ' . esc_attr($jsst_color1) . ';
}
div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button, #popupforinternalnote .timer-button
 { 
    background-color: ' . esc_attr($jsst_color1) . ';
    border: 1px solid ' . esc_attr($jsst_color1) . ';
    border-radius: 50%;
    padding: 6px;
    align-items: center;
    display: inline-flex;
}
div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button img
 { 
    max-width: 100%;
}
    .js-ticket-thread-read-status-detail{
        display:none;
    }
    div.jsst-main-up-wrapper input.radiobutton.js-ticket-append-radio-btn, .replyFormStatus.js-form-title-position-reletive-left input {
        display: inline-block !important;
        margin: 0 12px 0 0 !important;
        transform: scale(1.3);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        border: 1px solid '. $jsst_color5 .';
        border-radius: 4px;
        background-color: #ffffff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
    }
    div.jsst-main-up-wrapper input.radiobutton.js-ticket-append-radio-btn::after, .replyFormStatus.js-form-title-position-reletive-left input::after {
        content: "";
        position: absolute;
        top: 0px; /* Adjust checkmark position */
        left: 0px; /* Adjust checkmark position */
        width: 12px; /* Larger checkmark */
        height: 12px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E"); /* White checkmark SVG */
        background-size: contain;
        background-repeat: no-repeat;
    }
    div.jsst-main-up-wrapper input.radiobutton.js-ticket-append-radio-btn:checked, .replyFormStatus.js-form-title-position-reletive-left input:checked {
        background-color: ' . esc_attr($jsst_color1) . ';
        border-color: ' . esc_attr($jsst_color1) . ';
    }
  /* Main Container for a single credential entry */
    .js-ticket-usercredentails-single {
    background-color: #ffffff;
    padding: 25px 30px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: calc(100% - 40px);
    box-sizing: border-box;
    display: flex;
    flex-wrap: wrap;
    gap: 15px 20px;
    border: 1px solid '. $jsst_color5 .';
    margin: 20px
        }

        /* Title of the credential card */
        .js-ticket-usercredentail-title {
            width: 100%;
            font-size: 20px;
            font-weight: 600;
            color: #343a40;
            padding-bottom: 15px;
            border-bottom: 1px solid ' . $jsst_color5 . ';
            margin-bottom: 5px;
        }

        /* Wrapper for a single data point (label + value) */
        .js-ticket-usercredentail-data {
            display: flex;
            align-items: baseline;
            flex: 1 1 calc(50% - 10px); /* Two-column layout */
            min-width: 250px;
        }

        /* Full-width modifier for data fields */
        .js-ticket-usercredentail-data-full-width {
            flex-basis: 100%;
            flex-direction: column; /* Stack label and value */
        }
        
        .js-ticket-usercredentail-data-full-width .js-ticket-usercredentail-data-value {
             padding-top: 5px;
        }


        /* Label for the data (e.g., "User Name:") */
        .js-ticket-usercredentail-data-label {
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }

        /* The actual value of the data */
        .js-ticket-usercredentail-data-value {
            color: #6c757d;
            word-break: break-all; /* Break long strings */
        }

        /* Wrapper for the action buttons */
        .js-ticket-usercredentail-data-button-wrap {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
            padding-top: 20px;
            border-top: 1px solid ' . $jsst_color5 . ';
        }

        /* Shared button styles */
        .js-ticket-usercredentail-data-button-edit,
        .js-ticket-usercredentail-data-button-delete {
            padding: 9px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Edit Button */
        .js-ticket-usercredentail-data-button-edit {
            background-color: ' . esc_attr($jsst_color1) . ';
            color: #ffffff;
        }

        .js-ticket-usercredentail-data-button-edit:hover {
            background-color: ' . esc_attr($jsst_color2) . ';
             color: #ffffff;
            transform: translateY(-2px);
        }
        .js-ticket-usercredentail-data-add-new-button-wrap {
    display: flex
;
    justify-content: center;
    margin-bottom: 15px;
}

        /* Delete Button */
        .js-ticket-usercredentail-data-button-delete {
            background-color: #e74c3c;
            color: #ffffff;
        }

        .js-ticket-usercredentail-data-button-delete:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        .js-ticket-priorty-btn-wrp input{
            border:1px solid '. $jsst_color5 .';
            background:#f5f2f5;
        }
        .js-ticket-priorty-btn-wrp input:hover{
                border-color: '. $jsst_color2 .';
                background-color:'. $jsst_color2 .';
                color:'. $jsst_color7 .';
            }
        /* Responsive adjustments */
        @media (max-width: 600px) {
            body {
                padding: 20px;
            }
            .js-ticket-usercredentails-single {
                padding: 20px;
            }
            .js-ticket-usercredentail-data {
                flex-basis: 100%; /* Single column on small screens */
            }
            .js-ticket-usercredentail-data-button-wrap {
                justify-content: center;
                flex-direction: column;
            }
            .js-ticket-usercredentail-data-button-edit,
            .js-ticket-usercredentail-data-button-delete {
                width: 100%;
                text-align: center;
            }
            .js-row.js-ticket-popup-row {
                padding: 15px 20px;
                font-weight: 600;
                border-bottom: 1px solid ' . $jsst_color5 . ';
                background-color:' . $jsst_color3 .';
                display: flex;
                align-items: center;
            }
            .js-ticket-usercredentail-data-button-wrap {
                padding: 10px 15px;
            }
            button.js-ticket-usercredentail-data-button-edit {
                padding: 10px 15px;
            }

            button.js-ticket-usercredentail-data-button-delete {
                padding: 10px 15px;
            }
            button.js-ticket-usercredentail-data-add-new-button {
                padding: 10px 15px;
            }
            div#usercredentailspopup .js-ticket-usercredentails-wrp .js-ticket-usercredentails-single {
                background: #fff;
                border: 1px solid '. $jsst_color5 .';
            }
            div.jsst-ticket-detail-timer-wrapper div.timer-right div.timer-buttons span.timer-button,.selected {
                background-color: ' . esc_attr($jsst_color1) . ';
                border-color: ' . esc_attr($jsst_color1) . ';
            }
}
    ';

wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
