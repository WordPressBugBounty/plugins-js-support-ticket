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
        column-gap: 25px; /* Increased gap between columns for better spacing */
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

    /* New Ticket Message Styling - Focus on inviting and modern look */
    div.js-ticket-form-instruction-message {
        background: linear-gradient(135deg, #f0f8ff, #e6f7ff); /* Soft blue gradient background */
        color: #334e68; /* Deep, calming blue for text */
        padding: 20px; /* Increased padding */
        margin-bottom: 10px; /* More space below the message */
        border-radius: 15px; /* Soft rounded corners */
        font-size: 17px; /* Slightly larger, more inviting font size */
        line-height: 1.8; /* Enhanced line height for readability */
        border: 1px solid #cceeff; /* Very subtle light blue border */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); /* Modern, elegant shadow */
        display: flex; /* Flexbox for icon and text alignment */
        align-items: center; /* Vertically center content */
        position: relative;
        overflow: hidden;
        font-weight: 500; /* Medium font weight */
        width:calc(100% - 40px);
        margin:0 20px 20px;
    }
    div.js-ticket-form-instruction-message::before {
        content: "";
        display: block;
        width: 35px; /* Larger icon size */
        height: 35px;
        margin-right: 20px; /* More space between icon and text */
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%2342a5f5\'%3E%3Cpath d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z\'/%3E%3C/svg%3E");
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
        column-gap: 25px; /* Increased space between form fields */
    }
    div.js-ticket-add-form-wrapper h3{
        color: ' . $jsst_color2 . ';
        margin-top: 0;
        font-weight: 600;
        font-size: 24px;
        font-family:inherit;
    }
    div.js-ticket-add-form-wrapper table th{background-color: ' . $jsst_color3 . '; color: ' . $jsst_color2 . ';border: 1px solid ' . $jsst_color5 . ';}
    div.js-ticket-add-form-wrapper table, div.js-ticket-add-form-wrapper table tr {border: 1px solid ' . $jsst_color5 . ';border-collapse: collapse;}
    div.js-ticket-add-form-wrapper table tr td{border: 1px solid ' . $jsst_color5 . ';}   
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
        flex: 0 0 calc(50% - 12.5px); /* Two columns layout with larger gap */
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
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp .js-ticket-from-field label{
        font-weight: normal;
        color: ' . $jsst_color4 . ';
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{
        float: left;
        width: 100%;
        margin-bottom: 10px; /* More space below titles */
        font-weight: 600; /* Bolder titles */
        color: ' . $jsst_color2 . ';
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{
        float: left;
        width: 100%;
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        align-items:center;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field div{
        flex:1 1 auto;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-recaptcha{
        margin-top:5px;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-form-date-field,
    div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea,
    div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field {
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
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
    div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-form-date-field:focus,
    div.js-ticket-from-field-wrp div.js-ticket-from-field textarea.js-ticket-custom-textarea:focus,
    div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field:focus {
        outline: none;
        border-color: ' . $jsst_color1 . '; /* Primary color highlight on focus */
        box-shadow: 0 0 0 4px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.25); /* More prominent, soft glow */
        background-color: #ffffff; /* White background on focus */
    }

    /* Styling for required field errors */
    .js-ticket-from-field-wrp.error input.js-ticket-form-field-input,
    .js-ticket-from-field-wrp.error input.js-form-date-field,
    .js-ticket-from-field-wrp.error textarea.js-ticket-custom-textarea,
    .js-ticket-from-field-wrp.error select.js-form-input-field,
    .js-ticket-from-field-wrp.error select.js-ticket-form-field-select,
    .js-ticket-from-field-wrp.error select.js-ticket-select-field,
    .js-ticket-from-field-wrp.error select.inputbox {
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
        /* Ensure it does not overlap with other content by giving it its own space */
        clear: both; /* Clear floats above it */
        width: 100%; /* Ensure it takes full width to prevent overlap with other elements in the same line */
        box-sizing: border-box; /* Include padding and border in the element\'s total width and height */
            position: relative;
            z-index: 2;
            bottom: 15px !important;
    }
     .js-ticket-from-field.js-ticket-form-field-select.has-error span.jsst-help-block.form-error {
        bottom: -10px !important;
}
.js-attachment-field .tk_attachment_value_wrapper .tk_attachment_value_text.has-error span.jsst-help-block.form-error {
    bottom: -232px !important;}
    @keyframes slideInFromTop {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width.js-ticket-system-terms-and-condition-box .js-ticket-from-field.js-ticket-form-field-select.has-error .jsst-help-block.form-error {
        bottom: -26px !important;
        position: absolute;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    select.js-ticket-select-field,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.inputbox {
        float: left;
        width: 100%;
        border-radius: 10px; /* Softer rounded corners for selects */
        background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($jsst_color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat #fcfcfc; /* Custom SVG arrow for select, off-white background */
        -webkit-appearance: none; /* Remove default arrow */
        -moz-appearance: none;
        appearance: none;
        padding: 12px 18px;
        min-height: 52px;
        border: 1px solid ' . $jsst_color5 . ';
        color: ' . $jsst_color4 . ';
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    }
        div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.inputbox[multiple="multiple"]{
        background:unset !important;
        }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus,
    select.js-ticket-select-field:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.inputbox:focus {
        outline: none;
        border-color: ' . $jsst_color1 . ';
        box-shadow: 0 0 0 4px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.25);
        background-color: #ffffff;
    }

    /* Styling for js-ticket-from-field-description */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-description{
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

    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-custom-terms-and-condition-box {
    padding:14px 18px;
    width: 100%;
    line-height: 1.6;
    height: auto;
    border-radius: 12px;
    border: 1px solid '. $jsst_color5 .';
    background-color: ' . $jsst_color3 . ' !important;
    color: #636363;
    display: flex
;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.js-ticket-custom-terms-and-condition-box jsst-formfield-radio-button-wrap {padding: 20px;
    width: 100%;
    line-height: 1.6;
    height: auto;
    border-radius: 12px;
    border: 1px solid '. $jsst_color5 .';
    background-color: #f5f2f5;
    color: #636363;
    display: flex;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width.js-ticket-system-terms-and-condition-box{
        padding:14px 18px; /* Increased padding */
        width: 100%;
        line-height: 1.6; /* Better line height */
        height: auto;
        border-radius: 12px; /* Softer rounded corners */
        border: 1px solid ' . $jsst_color5 . ';
        background-color: ' . $jsst_color3 . '; /* Light background */
        color: ' . $jsst_color4 . ';
        display: flex; /* Flex for alignment */
        align-items: center; /* Vertically align content */
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); /* Subtle shadow */
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-system-terms-and-condition-box input[type="checkbox"], .js-ticket-custom-terms-and-condition-box jsst-formfield-radio-button-wrap, .js-ticket-from-field-wrp-full-width.js-ticket-from-field-wrp .js-ticket-from-field .js-ticket-custom-terms-and-condition-box.jsst-formfield-radio-button-wrap input.radiobutton.js-ticket-append-radio-btn, .jsst-formfield-radio-button-wrap.js-ticket-custom-radio-box .radiobutton.js-ticket-append-radio-btn  {
        display: inline-block !important;
        margin: 0 12px 0 0 !important; /* More spacing for checkbox */
        transform: scale(1.3); /* Larger checkbox */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px; /* Larger size */
        height: 14px;
        border: 1px solid ' . $jsst_color5 . '; /* Border with secondary color */
        border-radius: 4px; /* Slightly more rounded */
        background-color: #fff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-system-terms-and-condition-box input[type="checkbox"]:checked, .js-ticket-from-field-wrp-full-width.js-ticket-from-field-wrp .js-ticket-from-field .js-ticket-custom-terms-and-condition-box.jsst-formfield-radio-button-wrap input.radiobutton.js-ticket-append-radio-btn:checked, .jsst-formfield-radio-button-wrap.js-ticket-custom-radio-box .radiobutton.js-ticket-append-radio-btn:checked {
        background-color: ' . $jsst_color1 . '; /* Primary color fill when checked */
        border-color: ' . $jsst_color1 . ';
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-system-terms-and-condition-box input[type="checkbox"]:checked::after, .js-ticket-from-field-wrp-full-width.js-ticket-from-field-wrp .js-ticket-from-field .js-ticket-custom-terms-and-condition-box.jsst-formfield-radio-button-wrap input.radiobutton.js-ticket-append-radio-btn:checked::after, .jsst-formfield-radio-button-wrap.js-ticket-custom-radio-box .radiobutton.js-ticket-append-radio-btn:after {
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

    div.js-ticket-form-btn-wrp{
        float: left;
        width: calc(100% - 20px);
        margin: 40px 10px 10px 10px; /* More space above buttons */
        text-align: center;
        padding: 30px 0px 10px 0px;
        border-top: 1px solid ' . $jsst_color5 . '; /* Subtle separator */
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button{
        background-color: ' . $jsst_color1 . ' !important; /* Solid primary color */
        color: ' . $jsst_color7 . ' !important; /* White text */
        border: 1px solid ' . $jsst_color1 . '; /* Border matches background */
        margin-right: 20px; /* More space between buttons */
        box-shadow: 0 8px 25px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4); /* More prominent shadow */
        padding: 16px 30px; /* More padding for larger buttons */
        min-width: 160px; /* Wider buttons */
        border-radius: 10px; /* Softer rounded buttons */
        line-height: initial;
        font-weight: 700; /* Bolder button text */
        cursor: pointer;
        transition: all 0.3s ease; /* Smooth transitions */
        text-decoration: none;
        display: inline-block;
        letter-spacing: 0.5px; /* Slight letter spacing */
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{
        background-color: ' . $jsst_color2 . ' !important; /* Secondary color on hover */
        border-color: ' . $jsst_color2 . ' !important; /* Secondary color border on hover */
        transform: translateY(-3px); /* More pronounced lift effect */
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5); /* Enhanced shadow on hover */
        filter: brightness(1.1); /* Slightly brighter on hover */
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
        background-color: ' . $jsst_color3 . '; /* Light background for cancel button */
        color: ' . $jsst_color4 . '; /* Dark text for cancel button */
        border: 1px solid ' . $jsst_color5 . '; /* Soft border */
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
        letter-spacing: 0.5px; /* Slight letter spacing */
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
        background-color: ' . $jsst_color2 . '; /* Slightly darker on hover */
        color: ' . $jsst_color7 . ';
        border-color: ' . $jsst_color2 . '; /* Darker border on hover */
        transform: translateY(-2px); /* Slight lift effect */
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5); /* More prominent shadow on hover */
    }

    /* Attachments Section */
    div.js-ticket-reply-attachments{
        display: inline-block;
        width: 100%;
        margin-bottom: 40px; /* More space below attachments */
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
        color: ' . $jsst_color2 . ';
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
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text input{
        font-size: 15px;
        color: ' . $jsst_color4 . ';
    }
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
        width: calc(50% - 20px); /* Three columns with spacing */
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
    @media (max-width: 768px) {
        div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
            width: calc(50% - 20px); /* Two columns on tablets */
        }
    }
    @media (max-width: 480px) {
        div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
            width: calc(100% - 20px); /* Single column on mobile */
        }
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
       word-break: break-all;
        width: 100%;
        font-size: 15px; /* Slightly larger font */
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
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4); /* Subtle shadow */
    } 
    span.tk_attachments_addform:hover{
        background-color: ' . $jsst_color2 . '; /* Secondary color on hover */
        border-color: ' . $jsst_color2 . '; /* Secondary color border on hover */
        transform: translateY(-3px);
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
        filter: brightness(1.1);
    }

    /* Radio Buttons */

        .js-ticket-from-field-wrp-full-width.js-ticket-from-field-wrp .js-ticket-from-field .js-ticket-custom-terms-and-condition-box.jsst-formfield-radio-button-wrap input.radiobutton.js-ticket-append-radio-btn {
        
}
    div.js-ticket-custom-radio-box,
    div.js-ticket-radio-box, .radiobutton js-ticket-append-radio-btn,
    .radiobutton js-ticket-append-radio-btn, input.radiobutton.js-ticket-append-radio-btn {
        width: auto;
        display: flex;
        align-items: center;
        margin-right: 25px; /* More space between radio options */
    }
    div.js-ticket-radio-btn-wrp {
        background-color: ' . $jsst_color3 . ';
        border: 1px solid ' . $jsst_color5 . ';
        border-radius: 12px; /* Softer rounded container */
        padding: 12px 20px; /* More padding inside container */
        display: flex;
        flex-wrap: wrap;
        gap: 20px; /* More space between radio buttons */
    }
    span.js-ticket-apend-radio-btn{
        border: 2px solid ' . $jsst_color2 . '; /* Secondary color border */
        background-color: ' . $jsst_color7 . ';
        border-radius: 50%;
        width: 24px; /* Larger size */
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px; /* More space */
        transition: all 0.2s ease;
    }
    span.js-ticket-apend-radio-btn:after {
        content: "";
        width: 12px; /* Larger inner dot */
        height: 12px;
        border-radius: 50%;
        background-color: ' . $jsst_color1 . '; /* Primary color inner dot */
        opacity: 0;
        transform: scale(0);
        transition: all 0.2s ease;
    }
    input[type="radio"]:checked + span.js-ticket-apend-radio-btn:after, input[type=checkbox], input[type=radio] {
        opacity: 1; 
        transform: scale(1);
    }
    input[type="radio"] {
       
    }
    label[for^="radio_"] {
        cursor: pointer;
        color: ' . $jsst_color4 . ';
        font-weight: 500;
    }

    /* Other elements */
    select::-ms-expand {
        display: none !important;
    }
    select {
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
    }
    span.js-attachment-file-box {
        padding: 12px 18px; /* More padding */
        border-radius: 10px; /* Softer rounded corners */
        background-color: ' . $jsst_color3 . ';
        border: 1px solid ' . $jsst_color5 . ';
        color: ' . $jsst_color4 . ';
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Subtle shadow */
    }
    
    /* Specific styles for existing attachments */
    div.js-ticket-attached-files-wrp {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px; /* More padding */
        margin-bottom: 15px; /* More space */
        background-color: #fcfcfc; /* Off-white background */
        border: 1px solid ' . $jsst_color5 . ';
        border-radius: 10px; /* Softer rounded corners */
        font-size: 15px;
        color: ' . $jsst_color4 . ';
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06); /* More prominent shadow */
    }
    div.js_ticketattachment {
        flex-grow: 1;
        padding-right: 20px; /* More space */
    }
    a.js-ticket-delete-attachment {
        color: ' . $jsst_color1 . ';
        padding: 10px 18px; /* More padding */
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.3s ease;
        box-shadow: 0 3px 10px rgba(231, 76, 60, 0.2); /* Subtle shadow */
    }
    a.js-ticket-delete-attachment:hover {
        transform: translateY(-2px); /* Slight lift */
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3); /* More prominent shadow */
    }

    /* WooCommerce and EDD specific fields */
    div.js-ticket-error-message-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 50px 30px; /* More padding */
        background-color: #fdfdfd; /* Lighter background */
        border: 1px solid '. $jsst_color5 .';
        border-radius: 20px; /* Softer rounded corners */
        margin-bottom: 40px; /* More space */
        box-shadow: 0 8px 25px rgba(0,0,0,0.07); /* More prominent shadow */
    }
    div.js-ticket-message-image-wrapper {
        margin-bottom: 25px; /* More space */
    }
    img.js-ticket-message-image {
        max-width: 150px; /* Larger image */
        height: auto;
    }
    span.js-ticket-messages-main-text {
        font-size: 22px; /* Larger font */
        font-weight: 700; /* Bolder text */
        color: ' . $jsst_color2 . '; /* Secondary accent color */
    }
    a.js-ticket-login-btn {
        background: ' . $jsst_color1 . '; /* Solid primary color */
        color: ' . $jsst_color7 . ';
        padding: 14px 30px; /* More padding */
        border-radius: 10px; /* Softer rounded corners */
        text-decoration: none;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
    }
    a.js-ticket-login-btn:hover {
        transform: translateY(-3px);
        background-color: ' . $jsst_color2 . ';
        box-shadow: 0 12px 30px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.5);
        filter: brightness(1.1);
    }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 40px; /* More space below table */
        border-radius: 15px; /* Softer rounded corners for the table */
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08); /* More prominent shadow */
    }
    th, td {
        padding: 18px 25px; /* More padding */
        text-align: left;
        border-bottom: 1px solid ' . $jsst_color5 . ';
    }
    th {
        background-color: ' . $jsst_color2 . '; /* Secondary color for header */
        color: ' . $jsst_color7 . ';
        font-weight: 700; /* Bolder font */
    }
    tr:last-child td {
        border-bottom: none;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9; /* Lighter zebra striping */
    }
    td {
        background-color: ' . $jsst_color7 . ';
        color: ' . $jsst_color4 . ';
        font-size: 15px;
    }
    table a {
        color: ' . $jsst_color1 . ';
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    table a:hover {
        color: ' . $jsst_color2 . ';
        text-decoration: underline;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991px) {
        div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
            flex: 1 1 100%; /* Single column layout on smaller screens */
        }
    }
    @media (max-width: 767px) {
        form.js-ticket-form{
            padding: 25px; /* Adjust padding for smaller screens */
        }
        div.js-ticket-form-instruction-message {
            padding: 20px 25px;
            font-size: 15px;
        }
        div.js-ticket-form-instruction-message::before {
            width: 28px;
            height: 28px;
            margin-right: 12px;
        }
        div.js-ticket-form-btn-wrp input.js-ticket-save-button,
        div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px; /* More space between stacked buttons */
        }
        div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
            width: calc(100% - 20px); /* Single column on mobile */
        }
    }
';
/*Code For Colors*/
$jsst_jssupportticket_css .= '

	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{border:1px solid ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select{border:1px solid ' . $jsst_color5 . ';color: ' . $jsst_color2 . ';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.inputbox{border:1px solid ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width.js-ticket-system-terms-and-condition-box{color: ' . $jsst_color4 . ';border:1px solid ' . $jsst_color5 . ';} /* Changed to $jsst_color4 for consistency */
	select.js-ticket-select-field{border:1px solid ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';background-color: #fff !important;} /* Changed to $jsst_color4 for consistency */
	span.tk_attachments_configform{color:' . $jsst_color4 . ';}
	div.js-ticket-form-btn-wrp{border-top:1px solid ' . $jsst_color5 . ';} /* Changed to $jsst_color5 for softer look */
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:' . $jsst_color1 . ' !important;color:' . $jsst_color7 . ' !important;border: 1px solid ' . $jsst_color1 . ';} /* Kept for fallback, but gradient is primary */
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color: ' . $jsst_color2 . ';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background-color:#f5f2f5;color:' . $jsst_color4 . ';} /* Changed to $jsst_color4 for consistency */
	 a.js-ticket-delete-attachment{color:' . $jsst_color1 . ';} /* Changed to #e74c3c for consistency with new red */
	div.js-ticket-radio-btn-wrp{background-color:' . $jsst_color3 . ';border:1px solid ' . $jsst_color5 . ';}
	span.tk_attachments_addform{background-color:' . $jsst_color1 . ';color:' . $jsst_color7 . ';border: 1px solid ' . $jsst_color1 . ';} /* Kept for fallback, but gradient is primary */
	span.tk_attachments_addform:hover{border-color: ' . $jsst_color2 . ';}
	span.js-ticket-apend-radio-btn{border:1px solid ' . $jsst_color5 . ';background-color: ' . $jsst_color3 . ';}
	span.tk_attachment_value_text{border: 1px solid ' . $jsst_color5 . ';background-color:' . $jsst_color7 . ';}
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select{border: 1px solid ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
	span.jsst-help-block{color:red !important;} /* This will be overridden by the more specific rule above */
';


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
