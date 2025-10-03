<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
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
    column-gap: 25px; /* Space between form sections */
    width: 100%;
    padding: 40px; /* More generous padding for a spacious feel */
    background-color: #ffffff; /* Crisp white background */
    border-radius: 20px; /* Larger, softer rounded corners */
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1); /* Deeper, more diffused shadow for a floating effect */
    animation: fadeIn 0.8s ease-out forwards; /* Gentle fade-in animation for overall form */
    opacity: 0; /* Start invisible for animation */
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* New Ticket Message Styling */
div.js-ticket-form-instruction-message {
    background: linear-gradient(135deg, #f0f8ff, #e6f7ff); /* Soft blue gradient background */
    color: #334e68; /* Deep, calming blue for text */
    padding: 20px; /* Increased padding */
    margin-bottom: 10px; /* More space below the message */
    border-radius: 15px; /* Soft rounded corners */
    font-size: 17px; /* Slightly larger, more inviting font size */
    line-height: 1.8; /* Enhanced line height for readability */
    border: 1px solid ' . $color5 . '; /* Very subtle light blue border */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); /* Modern, elegant shadow */
    display: flex; /* Flexbox for icon and text alignment */
    align-items: center; /* Vertically center content */
    position: relative;
    overflow: hidden;
    font-weight: 500; /* Medium font weight */
}
div.js-ticket-form-instruction-message::before {
    content: "";
    display: block;
    width: 35px; /* Larger icon size */
    height: 35px;
    margin-right: 20px; /* More space between icon and text */
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%2342a5f5\'%3E%3Cpath d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z\'/%3E%3C/svg%3E"); /* Vibrant blue info icon */
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    flex-shrink: 0;
}

div.js-ticket-add-form-wrapper{
    float: left;
    width: 100%;
    display: flex; /* Flexbox for better layout control */
    flex-wrap: wrap; /* Allow items to wrap */
    gap: 25px; /* Increased space between form fields */
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
    flex: 0 0 calc(50% - 12.5px); /* Two columns layout with larger gap */
    margin: 0; /* Reset margin due to gap */
    min-width: 300px; /* Ensure fields are comfortably sized */
    margin-bottom: 30px;
    position: relative;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{
    flex: 1 1 100%; /* Full width for specific fields */
    margin-bottom: 30px; /* Slightly increased margin for full-width fields */
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{
    float: left;
    width: 100%;
    margin-bottom: 10px; /* More space below titles */
    font-weight: 600; /* Bolder titles */
    color: ' . $color2 . '; /* Rich dark text color */
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{
    float: left;
    width: 100%;
    position: relative;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field #premade{
    flex:1 1 auto;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-select-user-field{
    flex: 1 1 auto;
}

/* Unified Input, Select, and Textarea Fields */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea,
div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field {
    float: left;
    width: 100%;
    border-radius: 10px; /* Softer rounded corners for all input fields */
    padding: 12px 18px;
    min-height:52px;
    line-height: normal;
    height: auto;
    min-height: 52px; /* Increased min-height for better tap targets */
    border: 1px solid ' . $color5 . '; /* Subtle border color */
    color: ' . $color4 . '; /* Darker text color for input values */
    transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease; /* Smooth transitions */
    background-color: #fcfcfc; /* Slightly off-white input background */
    max-width:100%;
    box-sizing: border-box;
}

div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea:focus,
div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field:focus {
    outline: none;
    border-color: ' . $color1 . '; /* Primary color highlight on focus */
    box-shadow: 0 0 0 4px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.25); /* More prominent, soft glow */
    background-color: #ffffff; /* White background on focus */
}

/* Styling for required field errors */
.js-ticket-from-field-wrp.error input.js-ticket-form-field-input,
.js-ticket-from-field-wrp.error textarea.js-ticket-custom-textarea,
.js-ticket-from-field-wrp.error select.js-form-input-field,
.js-ticket-from-field-wrp.error select.js-ticket-form-field-select,
.js-ticket-from-field-wrp.error select.js-ticket-select-field {
    border-color: #e74c3c !important; /* Prominent red border for error fields */
    box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.25) !important; /* Red glow for error fields */
}

/* Improved error message styling */
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
    width: 100%; /* Ensure it takes full width */
    box-sizing: border-box; /* Include padding and border in width/height */
    position: relative;
    z-index: 2;
    bottom: 15px !important;
}

@keyframes slideInFromTop {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
select.js-ticket-select-field,
select.js-ticket-custom-select,
div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select.js-ticket-premade-select {
    float: left;
    width: 100%;
    border-radius: 10px; /* Softer rounded corners for selects */
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat #fcfcfc; /* Custom SVG arrow */
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding: 14px 18px;
    min-height: 52px;
    border: 1px solid ' . $color5 . ';
    color: ' . $color4 . ';
    transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    box-sizing: border-box;
}

div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus,
select.js-ticket-select-field:focus,
div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select.js-ticket-premade-select:focus {
    outline: none;
    border-color: ' . $color1 . ';
    box-shadow: 0 0 0 4px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.25);
    background-color: #ffffff;
}

/* Styling for js-ticket-from-field-description */
.js-ticket-from-field-description {
    float: left;
    margin-top: 5px;
    width: 100%;
    display: block;
    font-size: 15px;
    color: #636363;
    opacity: 0.7;
    line-height: 1.5;
    padding: 0 5px;
    box-sizing: border-box;
}

/* Duedate field icon */
input#duedate {
    background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/calender.png);
    background-repeat: no-repeat;
    background-position: right 18px center;
    background-size: 22px;
}

div.js-ticket-form-btn-wrp{
    float: left;
    width: 100%;
    margin: 40px 0 10px 0;
    text-align: center;
    padding: 30px 0px 10px 0px;
    border-top: 1px solid ' . $color5 . '; /* Subtle separator */
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    box-sizing: border-box;
}
div.js-ticket-form-btn-wrp input.js-ticket-save-button{
    background-color: ' . $color1 . ' !important; /* Solid primary color */
    color: ' . $color7 . ' !important; /* White text */
    border: 1px solid ' . $color1 . '; /* Border matches background */
    box-shadow: 0 8px 25px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4); /* More prominent shadow */
    padding: 16px 30px; /* More padding for larger buttons */
    min-width: 160px; /* Wider buttons */
    border-radius: 10px; /* Softer rounded buttons */
    line-height: initial;
    font-weight: 700; /* Bolder button text */
    cursor: pointer;
    transition: all 0.3s ease; /* Smooth transitions */
    text-decoration: none;
    display: inline-block;
    letter-spacing: 0.5px;
    margin-right:0;
}
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{
    background-color: ' . $color2 . ' !important; /* Secondary color on hover */
    border-color: ' . $color2 . ' !important; /* Secondary color border on hover */
    transform: translateY(-3px); /* More pronounced lift effect */
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5); /* Enhanced shadow on hover */
    filter: brightness(1.1); /* Slightly brighter on hover */
}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
    background-color: ' . $color3 . '; /* Light background for cancel button */
    color: ' . $color4 . '; /* Dark text for cancel button */
    border: 1px solid ' . $color5 . '; /* Soft border */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); /* Subtle shadow */
    padding: 16px 30px; /* More padding for larger buttons */
    min-width: 160px; /* Wider buttons */
    border-radius: 10px; /* Softer rounded buttons */
    line-height: initial;
    font-weight: 700; /* Bolder button text */
    cursor: pointer;
    transition: all 0.3s ease; /* Smooth transitions */
    text-decoration: none;
    display: inline-block;
    letter-spacing: 0.5px;
}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
    background-color: ' . $color2 . '; /* Slightly darker on hover */
    color: ' . $color7 . ';
    border-color: ' . $color2 . '; /* Darker border on hover */
    transform: translateY(-2px); /* Slight lift effect */
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5); /* More prominent shadow on hover */
}

/* Attachments Section */
div.js-ticket-reply-attachments {
    display: inline-block;
    width: 100%;
    margin-bottom: 40px; /* More space below attachments */
    padding-top: 30px; /* More padding above attachment section */
    border-top: 1px solid ' . $color5 . '; /* Separator line */
}
div.js-ticket-reply-attachments div.js-attachment-field-title {
   display: inline-block;
    width: 100%;
    padding: 15px 0px;
    font-weight: 600;
    border-bottom: 1px solid ' . $color5 . '; /* Separator below the title */
    margin-bottom: 20px; /* Space below the title */
}
div.js-attachment-field {
    display: inline-block;
    width: 100%;
    float: left;
    padding: 15px;
    border: 2px dashed ' . $color5 . ';
    border-radius: 12px;
    background-color: ' . $color3 . ';
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    min-height: 120px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}
div.tk_attachment_value_wrapperform {
    float: left;
    width: 100%;
    padding: 15px;
    border: 2px dashed ' . $color5 . ';
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
    background-color: #f0f8ff; /* Lighter background on hover */
}
div.tk_attachment_value_wrapperform span.tk_attachment_value_text {
    width: calc(50% - 20px); /* Three columns with spacing */
    padding: 12px; /* More padding for each attachment item */
    margin: 10px; /* Margin around each item */
    position: relative;
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
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field #premade ~ .js-ticket-apend-radio-btn{
    padding: 12px 18px;
    display: flex;
    gap: 10px;
    align-items:center;
    border:1px solid ' . $color5 . ';
    border-radius:8px;
}
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp .js-ticket-from-field label{
        font-weight: normal;
        color: ' . $color4 . ';
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp .js-ticket-assigned-tome label{
        font-weight: normal;
        color: ' . $color4 . ';
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field #premade ~ .js-ticket-apend-radio-btn input, div.js-ticket-assigned-tome input{
    display: inline-block !important;
    margin-bottom: 0 !important;
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
    margin-top:0;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field #premade ~ .js-ticket-apend-radio-btn input::before, div.js-ticket-assigned-tome input::before{
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
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field #premade ~ .js-ticket-apend-radio-btn [type="checkbox"]:checked, div.js-ticket-assigned-tome input:checked{
    background-color: ' . $color1 . ';
    border-color: ' . $color1 . ';
}
div.js-ticket-assigned-tome{
    display: flex;
    align-items: center;
    gap: 5px;
    width: 100%;
    border:1px solid ' . $color5 . ';
    padding: 12px 18px;
    line-height: initial;
    min-height: 52px;
    border-radius: 8px;
    width: 100%;
}
div.js-ticket-assigned-tome input{
    margin-top:0 !important;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-system-terms-and-condition-box input[type="checkbox"], .js-ticket-custom-terms-and-condition-box jsst-formfield-radio-button-wrap, .js-ticket-from-field-wrp-full-width.js-ticket-from-field-wrp .js-ticket-from-field .js-ticket-custom-terms-and-condition-box.jsst-formfield-radio-button-wrap input.radiobutton.js-ticket-append-radio-btn, .jsst-formfield-radio-button-wrap.js-ticket-custom-radio-box .radiobutton.js-ticket-append-radio-btn{
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
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-system-terms-and-condition-box input[type="checkbox"]:checked, .js-ticket-from-field-wrp-full-width.js-ticket-from-field-wrp .js-ticket-from-field .js-ticket-custom-terms-and-condition-box.jsst-formfield-radio-button-wrap input.radiobutton.js-ticket-append-radio-btn:checked, .jsst-formfield-radio-button-wrap.js-ticket-custom-radio-box .radiobutton.js-ticket-append-radio-btn:checked{
    background-color: ' . $color1 . ';
    border-color: ' . $color1 . ';      
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-system-terms-and-condition-box input[type="checkbox"]:checked::after, .js-ticket-from-field-wrp-full-width.js-ticket-from-field-wrp .js-ticket-from-field .js-ticket-custom-terms-and-condition-box.jsst-formfield-radio-button-wrap input.radiobutton.js-ticket-append-radio-btn:checked::after, .jsst-formfield-radio-button-wrap.js-ticket-custom-radio-box .radiobutton.js-ticket-append-radio-btn:after{
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
div.js-ticket-custom-radio-box,
div.js-ticket-radio-box {
    flex: 1 1 auto;
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
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
}
span.tk_attachments_addform:hover {
    background-color: ' . $color2 . ';
    border-color: ' . $color2 . ';
    transform: translateY(-3px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
    filter: brightness(1.1);
}

/* User select button (Agent/Staff Specific) */
div.js-ticket-select-user-btn{
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 30%;
}
div.js-ticket-select-user-btn a#userpopup{
    border-radius: 0 10px 10px 0;
    padding: 14px 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    text-align: center;
    text-decoration: none;
    outline: 0px;
    line-height: initial;
    min-height: 52px;
    height:100%;
    box-sizing: border-box;
}

/* Popup Styling (Agent/Staff Specific) */
div#userpopupblack {
    background: rgba(0,0,0,0.7);
    position: fixed;
    width: 100%;
    height: 100%;
    top:0px;
    left:0px;
    z-index: 9989;
}
div#userpopup {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 60%;
    max-height: 70%;
    padding-top: 0px;
    z-index: 99999;
    overflow-y: auto;
    overflow-x: hidden;
    transform: translate(-50%,-50%);
    border-radius:15px;
}
.jsst-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid ' . $color5 . ';
}
.popup-header-text {
    font-size: 21px;
    font-weight: 600;
}
.popup-header-close-img {
    width: 28px;height: 28px;cursor: pointer;background-repeat: no-repeat;background-position: center;background-size: contain;transition: transform 0.3s ease;
    background-image: url(\'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"%3e%3cline x1="18" y1="6" x2="6" y2="18"%3e%3c/line%3e%3cline x1="6" y1="6" x2="18" y2="18"%3e%3c/line%3e%3c/svg%3e\') !important;
    background-repeat: no-repeat;
    background-position: center;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}
.popup-header-close-img:hover {
    opacity: 1;
}
.js-ticket-popup-search-wrp {
   padding: 24px;
    margin: 10px;
}
.js-ticket-search-top {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}
.js-ticket-search-left {
    flex: 3;
}
.js-ticket-search-fields-wrp {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    height:100%;
}
.js-ticket-search-input-fields {
    flex: 1;
    min-width: 150px;
    padding: 12px 16px;
    border-radius: 10px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.js-ticket-search-input-fields:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.js-ticket-search-right {
    flex: 1;
    display: flex;
    align-items: flex-start;
}
.js-ticket-search-btn-wrp {
    display: flex;
    gap: 12px;
    width: 100%;
}
.js-ticket-search-btn,
.js-ticket-reset-btn {
    flex: 1;
    padding: 12px 16px;
    font-weight: 500;
    border-radius: 10px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    white-space: nowrap;
}
#records {
    flex-grow: 1;
    overflow-y: auto;
    padding: 0 10px 24px;
}
div.js-ticket-table-wrp div.js-ticket-table-header{
    border-top-right-radius:10px;
    border-top-left-radius:10px;
}
div.js-ticket-table-body div.js-ticket-data-row:last-child:last-child{
    border-bottom-right-radius:10px;
    border-bottom-left-radius:10px;
}
.js-ticket-table-header,
.js-ticket-data-row {
    display: flex;
    width: 100%;
    padding: 0 16px;
}
.js-ticket-table-header {
    padding-top: 16px;
    padding-bottom: 16px;
    font-weight: 600;
}
.js-ticket-data-row:hover {
    background-color: #f1f3f5;
}
.js-ticket-table-header-col,
.js-ticket-table-body-col {
    flex: 1;
    padding: 16px 8px;
    text-align: left;
    display: flex;
    align-items: center;
}
.js-ticket-table-header-col:nth-child(1), .js-ticket-table-body-col:nth-child(1) { flex-basis: 10%; }
.js-ticket-table-header-col:nth-child(2), .js-ticket-table-body-col:nth-child(2) { flex-basis: 25%; }
.js-ticket-table-header-col:nth-child(3), .js-ticket-table-body-col:nth-child(3) { flex-basis: 40%; }
.js-ticket-table-header-col:nth-child(4), .js-ticket-table-body-col:nth-child(4) { flex-basis: 25%; }

.js-userpopup-link:hover {
    text-decoration: underline;
}
.js-ticket-display-block {
    display: none;
}
.jsst_userpages {
    text-align: center;
    padding: 24px 0 0;
}
.jsst_userlink {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 4px;
    border-radius: 10px;
    text-decoration: none;
}
.jsst_userlink.selected {
    font-weight: 600;
}

select::-ms-expand {display:none !important;}
select{-webkit-appearance:none !important;}

div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input.loading {background-image: url("' . esc_url(JSST_PLUGIN_URL) . 'includes/images/spinning-wheel.gif");background-size: 25px 25px;background-position:right center;background-repeat: no-repeat;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field span.jsst_product_found{background-image: url("' . esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png");background-size: 25px 25px;background-position:right center;background-repeat: no-repeat;width:30px;height:30px;top:10px;right:10px;position:absolute;}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field span.jsst_product_not_found{background-image: url("' . esc_url(JSST_PLUGIN_URL) . 'includes/images/close.png");background-size: 25px 25px;background-position:right center;background-repeat: no-repeat;width:30px;height:30px;top:10px;right:10px;position:absolute;}


/* Responsive */
@media (max-width: 991px) {
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
        flex: 1 1 100%; /* Single column on smaller screens */
    }
}
@media (max-width: 768px) {
    form.js-ticket-form {
        padding: 25px;
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button,
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
        width: 100%;
        margin-right: 0;
        margin-bottom: 20px;
    }
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text {
        width: calc(50% - 10px);
    }
    .js-ticket-table-header { display: none; }
    .js-ticket-data-row { flex-direction: column; padding: 12px 0; }
    .js-ticket-table-body-col {
        flex-basis: auto !important;
        width: 100%;
        padding: 8px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed ' . $color5 . ';
    }
    .js-ticket-data-row .js-ticket-table-body-col:last-child { border-bottom: none; }
    .js-ticket-display-block {
        display: inline-block;
        font-weight: 600;
        margin-right: 8px;
    }
}
@media (max-width: 480px) {
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
        width: calc(100% - 20px); /* Single column on mobile */
    }
}

';
/*Code For Colors*/
$jssupportticket_css .= '
/* Add Form */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: ' . $color2 . ';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
	div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea {background-color:#fcfcfc;border:1px solid ' . $color5 . ' !important;color: ' . $color4 . ';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-select-field,
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select.js-ticket-premade-select {background-color: #fcfcfc;border:1px solid ' . $color5 . ' !important;color: ' . $color4 . ';}
	.js-userpopup-link{color:' . $color2 . ';}
	div.js-ticket-form-btn-wrp{border-top:2px solid ' . $color2 . ';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:' . $color1 . ' !important;color:' . $color7 . ' !important;}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{background-color:' . $color2 . '!important;color:' . $color7 . ' !important;}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background-color: #f5f2f5;color: #636363;border: 1px solid ' . $color5 . ';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{background-color:' . $color2 . '!important;color:' . $color7 . ' !important;}
	div.js-ticket-reply-attachments div.js-attachment-field-title {  border-bottom-color: ' . $color5 . '; }
	div.tk_attachment_value_wrapperform{border-color: ' . $color5 . '; background: #fff;}
	span.tk_attachment_value_text{border: 1px solid ' . $color5 . '; background-color:' . $color7 . ';}
	span.tk_attachments_configform {color: ' . $color4 . ';}
	/* User Select Popup */
	div.js-ticket-select-user-btn a#userpopup{background-color:' . $color1 . ';color:' . $color7 . ';border: 1px solid ' . $color1 . ';}
	div.js-ticket-select-user-btn a#userpopup:hover{background-color:' . $color2 . '; border-color:' . $color2 . ';}
	div#userpopup{background: #fff;}
	div.jsst-popup-header{background-color: ' . $color3 . '; color:' . $color2 . ';}
	div.js-ticket-popup-search-wrp {background-color:' . $color3 . '; border: 1px solid ' . $color5 . '!important;border-radius:10px;}
	div.js-ticket-search-top div.js-ticket-search-left div.js-ticket-search-fields-wrp input.js-ticket-search-input-fields{border:1px solid ' . $color5 . ';background-color:#fff;color: ' . $color4 . ';height:100%;border-radius:10px;}
	.js-ticket-search-btn{background-color: ' . $color1 . ';color:' . $color7 . ';}
	.js-ticket-search-btn:hover{background-color:' . $color2 . ';}
	.js-ticket-reset-btn{background-color: #f5f2f5;color: '. $color4 .';border: 1px solid ' . $color5 . ';}
	.js-ticket-reset-btn:hover{background-color:'. $color2 .';color:'. $color7 .';}
	div.js-ticket-table-header{background-color:' . $color3 . ';border-bottom:1px solid ' . $color5 . '; color:' . $color2 . ';}
	div.js-ticket-data-row{border-bottom: 1px solid ' . $color5 . ';}
    .js-userpopup-link{color:' . $color2 . ';}
    .jsst_userlink { color: ' . $color1 . '; background-color: #e9ecef; }
    .jsst_userlink.selected { background-color: ' . $color1 . '; color: ' . $color7 . '; }
';

wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);
?>
