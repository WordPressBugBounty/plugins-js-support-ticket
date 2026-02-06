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
    /* General Form Styling */
    form.js-ticket-form{
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        padding: 40px; /* More generous padding for a spacious feel */
        background-color: #ffffff; /* Crisp white background */
        border-radius: 20px; /* Larger, softer rounded corners */
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1); /* Deeper, more diffused shadow for a floating effect */
        animation: fadeIn 0.8s ease-out forwards; /* Gentle fade-in animation for overall form */
        opacity: 0; /* Start invisible for animation */
        column-gap:25px;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Form Layout Wrappers */
    div.js-ticket-add-form-wrapper{
        float: left;
        width: 100%;
        display: flex; /* Flexbox for better layout control */
        flex-wrap: wrap; /* Allow items to wrap */
        gap: 25px; /* Increased space between form fields */
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
        flex: 1 1 calc(50% - 12.5px); /* Two columns layout with larger gap */
        margin: 0; /* Reset margin due to gap */
        min-width: 300px; /* Ensure fields are comfortably sized */
        margin-bottom: 30px;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{
        flex: 1 1 100%; /* Full width for specific fields */
        margin-bottom: 30px; /* Slightly increased margin for full-width fields */
        display:flex;
        flex-wrap:wrap;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title,
    .js-ticket-from-field-title-title {
        float: left;
        width: 100%;
        margin-bottom: 10px; /* More space below titles */
        font-weight: 600; /* Bolder titles */
    }
    .js-ticket-from-field-title-title {
        margin-top: 25px;
    }

    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{
        float: left;
        width: 100%;
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        align-items:center;
    }
    
    /* Unified Input, Select, and Textarea Fields */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-form-date-field,
    div.js-ticket-from-field-wrp div.js-ticket-from-field textarea,
    div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid {
        float: left;
        width: 100%;
        border-radius: 10px; /* Softer rounded corners for all input fields */
        padding: 12px 18px; /* More generous padding */
        line-height: normal;
        height: auto;
        min-height: 52px; /* Increased min-height for better tap targets */
        border: 1px solid ' . $jsst_color5 . '; /* Subtle border color */
        color: ' . $jsst_color4 . '; /* Darker text color for input values */
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease; /* Smooth transitions */
        background-color: #fcfcfc; /* Slightly off-white input background */
        max-width:100%;
        box-sizing: border-box;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
    div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-form-date-field:focus,
    div.js-ticket-from-field-wrp div.js-ticket-from-field textarea:focus,
    div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid:focus {
        outline: none;
        border-color: ' . $jsst_color1 . '; /* Primary color highlight on focus */
        box-shadow: 0 0 0 4px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.25); /* More prominent, soft glow */
        background-color: #ffffff; /* White background on focus */
    }

    /* Styling for required field errors */
    .js-ticket-from-field-wrp.error input,
    .js-ticket-from-field-wrp.error textarea,
    .js-ticket-from-field-wrp.error select {
        border-color: #e74c3c !important; /* Prominent red border for error fields */
        box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.25) !important; /* Red glow for error fields */
    }
    /* Improved error message styling for prominence and clarity */
    span.jsst-help-block {
        display: block !important; /* Ensure visibility */
        font-size: 14px; /* Clear font size */
        color: #c0392b !important; /* Deeper, more impactful red for text */
        padding: 5px 15px; /* Generous padding */
        background-color: #fff0f0; /* Very light red background */
        border: 1px solid #e74c3c; /* Solid red border */
        border-radius: 8px; /* Rounded corners for the message box */
        font-weight: 600; /* Bolder text for emphasis */
        box-shadow: 0 3px 10px rgba(231, 76, 60, 0.15); /* Subtle shadow for depth */
        animation: slideInFromTop 0.4s ease-out forwards; /* Gentle slide-in animation */
        opacity: 0; /* Start invisible for animation */
        clear: both; /* Clear floats above it */
        width: 100%; /* Ensure it takes full width to prevent overlap with other elements in the same line */
        box-sizing: border-box; /* Include padding and border in the element\'s total width and height */
        position: relative;
        z-index: 2;
        bottom: 15px !important;
    }
    @keyframes slideInFromTop {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Custom Select Arrow */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid {
        background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($jsst_color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat #fcfcfc;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Checkboxes */
    .js-ticket-from-field input[type="checkbox"],
    input#kb1, input#downloads1, input#announcement1, input#faqs1, input#append1 {
        display: inline-block !important;
        margin: 0 12px 0 0 !important;
        transform: scale(1.3);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        border: 1px solid ' . $jsst_color5 . ';
        border-radius: 4px;
        background-color: #fff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
        vertical-align: middle;
    }
    input#append1 {
        vertical-align: sub;
    }
    
    .js-ticket-from-field input[type="checkbox"]:checked,
    input#kb1:checked, input#downloads1:checked, input#announcement1:checked, input#faqs1:checked, input#append1:checked {
        background-color: ' . $jsst_color1 . ';
        border-color: ' . $jsst_color1 . ';
    }
    .js-ticket-from-field input[type="checkbox"]:checked::after,
    input#kb1:checked::after, input#downloads1:checked::after, input#announcement1:checked::after, input#faqs1:checked::after, input#append1:checked::after {
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

    /* Buttons */
    div.js-ticket-form-btn-wrp{
        float: left;
        width: 100%;
        margin: 40px 0 10px 0;
        text-align: center;
        padding: 30px 0px 10px 0px;
        border-top: 1px solid ' . $jsst_color5 . ';
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button{
        background-color: ' . $jsst_color1 . ' !important;
        color: ' . $jsst_color7 . ' !important;
        border: 1px solid ' . $jsst_color1 . ';
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
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
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{
        background-color: ' . $jsst_color2 . ' !important;
        border-color: ' . $jsst_color2 . ' !important;
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
        filter: brightness(1.1);
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
        background-color: #f5f2f5;
        color: ' . $jsst_color4 . ';
        border: 1px solid ' . $jsst_color5 . ';
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
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
        background-color: ' . $jsst_color2 . ';
        color: ' . $jsst_color7 . ';
        border-color: ' . $jsst_color2 . ';
        transform: translateY(-2px);
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
    }

    /* Attachments Section */
    div.js-ticket-reply-attachments{
        display: inline-block;
        width: 100%;
        margin-bottom: 40px;
        padding-top: 30px;
        border-top: 1px solid ' . $jsst_color5 . ';
    }
    div.js-ticket-reply-attachments div.js-attachment-field-title{
       display: inline-block;
        width: 100%;
        padding: 15px 0px;
        font-weight: 600;
        border-bottom: 1px solid ' . $jsst_color5 . ';
        margin-bottom: 20px;
    }
    
    div.js-ticket-reply-attachments div.js-attachment-field,
    .js-ticket-from-field-attch,
    input.inputbox.js-ticket-form-field-input-upload {
        float: left;
        width: 100%;
        padding: 15px;
        border: 2px dashed ' . $jsst_color5 . ';
        border-radius: 12px;
        background-color: ' . $jsst_color3 . ';
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        min-height: 120px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    div.tk_attachment_value_wrapperform{
        float: left;
        width: 100%;
        padding: 15px;
        border: 2px dashed ' . $jsst_color5 . ';
        border-radius: 12px;
        background-color: #ffffff;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        min-height: 120px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    div.tk_attachment_value_wrapperform:hover {
        background-color: #f0f8ff;
    }
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
        width: calc(50% - 20px);
        padding: 12px;
        margin: 10px;
        position: relative;
        background-color: ' . $jsst_color7 . ';
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
    }

    div.tk_attachment_value_wrapperform span.tk_attachment_value_text input.js-attachment-inputbox{
        width: calc(100% - 40px);
        max-width: 100%;
        max-height: 100%;
        border: none;
        background: transparent;
        color: ' . $jsst_color4 . ';
        font-size: 15px;
        cursor: pointer;
    }
    span.tk_attachment_value_text span.tk_attachment_remove{
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
    span.tk_attachment_value_text span.tk_attachment_remove:hover{
        opacity: 1;
        transform: translateY(-50%) scale(1.1);
    }
    
    span.tk_attachments_configform,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp .tk_attachments_configform {
        display: inline-block;
        float: left;
        line-height: 1.6;
       word-break: break-all;
        width: 100%;
        font-size: 15px;
        color: ' . $jsst_color4 . ';
        opacity: 0.75;
        text-align: left;
        box-sizing: border-box;
        padding: 0 5px;
        margin-top: 5px;
    }

    /* Component-Specific Styles from knowledgebase-addcategory */
    .js-ticket-from-field span.js-ticket-sub-fields {
        display: inline-flex;
        align-items: center;
        flex:1 1 auto;
        min-height: 52px;
        border-radius: 8px;
        padding: 12px 18px;
        background-color: #fcfcfc;
        border: 1px solid ' . $jsst_color5 . ';
    }
    .js-ticket-from-field span.js-ticket-sub-fields label{
        font-weight:normal;
        color:#636363;
    }
    label#forkb, label#fordownloads, label#forannouncement, label#forfaqs, label#forappend {
        display: inline-block;
        margin: 0;
    }
    div.js-ticket-radio-btn-wrp {
        width: 100%;
        padding: 11px;
        background-color: ' . $jsst_color3 . ';
        border: 1px solid ' . $jsst_color5 . ';
    }
    div.js-ticket-radio-btn-wrp input.js-ticket-form-field-radio-btn {
        margin-right: 5px;
        vertical-align: top;
    }
    div.js-ticket-radio-btn-wrp label#forsendmail {
        margin: 0 30px 0 0;
        display: inline-block;
    }
    img.js-ticket-category-img {
        display: inline-block;
        max-width: 100px;
        margin-top: 10px;
    }

    /* Notice/Message styling */
    div#msgshowcategory,
    div#msgshowcategory div.js-ticket-notice-wrapper,
    div#msgshowcategory div.js-ticket-notice-wrapper div.js-ticket-notice,
    div#msgshowcategory div.js-ticket-notice-wrapper div.js-ticket-question {
        width: 100%;
        box-sizing: border-box;
    }
    div.js-ticket-answer-btn {
        padding-top: 10px;
    }
    div.js-ticket-answer-btn a.js-ticket-yes,
    div.js-ticket-answer-btn a.js-ticket-no {
        display: inline-block;
        min-width: 100px;
        text-align: center;
        padding: 8px 5px;
        margin-right: 10px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991px) {
        div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
            flex: 1 1 100%; /* Single column layout on smaller screens */
        }
    }
    @media (max-width: 768px) {
        form.js-ticket-form{
            padding: 25px; /* Adjust padding for smaller screens */
        }
        div.js-ticket-form-btn-wrp input.js-ticket-save-button,
        div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
        div.tk_attachment_value_wrapperform span.tk_attachment_value_text,
        .js-ticket-from-field span.js-ticket-sub-fields {
            width: calc(100% - 20px);
        }
    }
    @media (max-width: 480px) {
        div.tk_attachment_value_wrapperform span.tk_attachment_value_text,
        .js-ticket-from-field span.js-ticket-sub-fields {
            width: calc(100% - 20px);
            margin-right: 0;
        }
    }
';

/*Code For Colors*/
$jsst_jssupportticket_css .= '
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title,
    .js-ticket-from-field-title-title {
        color: ' . $jsst_color2 . ';
    }
    .js-userpopup-link {
        color: ' . $jsst_color2 . ';
    }
    div.js-ticket-form-btn-wrp {
        border-top-color: ' . $jsst_color2 . ';
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
        background-color: ' . $jsst_color2 . ' !important;
        border-color: ' . $jsst_color2 . ' !important;
        color: ' . $jsst_color7 . ' !important;
    }
    span.tk_attachment_value_text {
        border: 1px solid ' . $jsst_color5 . ';
        background-color:' . $jsst_color7 . ';
    }
';

wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);
?>
