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
form.js-ticket-form {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    padding: 40px;
    background-color: #ffffff;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.8s ease-out forwards;
    opacity: 0;
    column-gap: 25px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Form Layout Wrappers */
div.js-ticket-add-form-wrapper {
    width: 100%;
    display: flex; /* Use Flexbox for layout */
    flex-wrap: wrap; /* Allow items to wrap to new lines */
    gap: 25px; /* Modern spacing between fields */
}

/* Individual Form Field Wrapper */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
    flex: 1 1 calc(50% - 12.5px); /* Responsive two-column layout */
    margin: 0; /* Margin is replaced by gap */
    min-width: 300px;
    position: relative; /* For absolute positioned error messages */
    margin-bottom: 30px;
}

/* Full-Width Field Wrapper */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width {
    flex: 1 1 100%; /* Take up full width */
    margin-bottom: 30px;
    display: flex;
    flex-wrap: wrap;
}

/* Field Title */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
    width: 100%;
    margin-bottom: 10px;
    font-weight: 600;
}

div.js-ticket-append-field-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 15px;
}

/* Field Container */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field {
    width: 100%;
    position: relative;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

/* Unified Input, Select, and Textarea Fields */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
select.js-ticket-select-field {
    width: 100%;
    border-radius: 10px;
    padding: 12px 18px;
    line-height: normal;
    height: auto;
    min-height: 52px;
    border-width: 1px;
    border-style: solid;
    transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    box-sizing: border-box;
    max-width:100%;
}

div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea:focus,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status:focus {
    outline: none;
    border-color: ' . $jsst_color1 . ';
    box-shadow: 0 0 0 4px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.25);
    background-color: #ffffff;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select{
    background-color: #fcfcfc;
    min-height:52px;
}
/* Custom Select Arrow */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
select.js-ticket-select-field {
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($jsst_color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}


/* Styling for required field errors */
.js-ticket-from-field-wrp.error input,
.js-ticket-from-field-wrp.error textarea,
.js-ticket-from-field-wrp.error select {
    border-color: #e74c3c !important;
    box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.25) !important;
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
    bottom: 15px !important;
}

@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Button Wrapper */
div.js-ticket-form-btn-wrp {
    width: 100%;
    margin: 40px 0 10px 0;
    text-align: center;
    padding: 30px 0px 10px 0px;
    border-top-width: 1px;
    border-top-style: solid;
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

/* Save Button */
div.js-ticket-form-btn-wrp input.js-ticket-save-button {
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
    margin-right: 0;
}

/* Cancel Button */
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
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
    border-width: 1px;
    border-style: solid;
}

/* Button Hover Effects */
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
    border-color: ' . $jsst_color2 . ' !important;
    transform: translateY(-3px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
    filter: brightness(1.1);
}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
    border-color: ' . $jsst_color2 . ';
    transform: translateY(-2px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
}

/* Attachments Section */
div.js-ticket-reply-attachments{
    display: inline-block;
    width: 100%;
    margin-bottom: 40px;
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
div.tk_attachment_value_wrapperform {
    float: left;
    width: 100%;
    padding: 15px;
    border: 2px dashed ' . $jsst_color5 . ';
    border-radius: 12px;
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
span.tk_attachments_configform{
    display: inline-block;
    float: left;
    line-height: 1.6;
    width: 100%;
    font-size: 15px;
    opacity: 0.75;
    text-align: left;
    box-sizing: border-box;
    padding: 0 5px;
    word-break: break-all;
    margin-top: 5px;
}
span.tk_attachments_addform{
    position: relative;
    display: inline-block;
    padding: 15px 25px;
    cursor: pointer;
    margin-top: 25px;
    min-width: 150px;
    text-align: center;
    line-height: initial;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
}
span.tk_attachments_addform:hover{
    transform: translateY(-3px);
    box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
    filter: brightness(1.1);
}

select::-ms-expand {display:none !important;}
select{-webkit-appearance:none !important;}

/* Responsive Media Queries */
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
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
        width: calc(100% - 20px);
    }
}
@media (max-width: 480px) {
    div.tk_attachment_value_wrapperform span.tk_attachment_value_text{
        width: calc(100% - 20px);
    }
}
';

/*Code For Colors*/
$jsst_jssupportticket_css .= '
/* Add Form */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status {
    border: 1px solid ' . $jsst_color5 . ' !important;
    color: ' . $jsst_color4 . ';
    background-color: #fcfcfc;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
    color: ' . $jsst_color2 . ';
}
div.js-ticket-form-btn-wrp {
    border-top-color: ' . $jsst_color2 . ';
}
div.js-ticket-form-btn-wrp input.js-ticket-save-button {
    background-color: ' . $jsst_color1 . ' !important;
    color: ' . $jsst_color7 . ' !important;
}
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
    background-color: ' . $jsst_color2 . ' !important;
    color: ' . $jsst_color7 . ' !important;
}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
    background-color: #f5f2f5;
    color: ' . $jsst_color4 . ';
    border-color: ' . $jsst_color5 . ';
}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
    background-color: ' . $jsst_color2 . ' !important;
    color: ' . $jsst_color7 . ' !important;
    border-color: ' . $jsst_color2 . ' !important;
}
a.js-ticket-delete-attachment {
    color: ' . $jsst_color1 . ';
}
div.js-ticket-reply-attachments div.js-attachment-field-title {
    color: ' . $jsst_color2 . '!important;
}
span.tk_attachments_addform {
    background-color: ' . $jsst_color1 . ';
    color: ' . $jsst_color7 . ';
}
span.tk_attachments_addform:hover {
    background-color: ' . $jsst_color2 . ';
}
select.js-ticket-select-field {
    background-color: #fcfcfc !important;
    border: 1px solid ' . $jsst_color5 . ';
    color: ' . $jsst_color4 . ';
}
span.tk_attachments_configform {
    color: ' . $jsst_color4 . ';
}
div.tk_attachment_value_wrapperform {
    background: #ffffff;
}
div.tk_attachment_value_wrapperform span.tk_attachment_value_text {
    background-color: ' . $jsst_color7 . ';
    border: 1px solid ' . $jsst_color5 . ';
}
div.tk_attachment_value_wrapperform span.tk_attachment_value_text input.js-attachment-inputbox{
    color: ' . $jsst_color4 . ';
}
';

wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);

?>
