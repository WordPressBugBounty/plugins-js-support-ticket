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
        display: inline-block;
        width: 100%;
        padding: 40px; /* More generous padding for a spacious feel */
        background-color: #ffffff; /* Crisp white background */
        border-radius: 20px; /* Larger, softer rounded corners */
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1); /* Deeper, more diffused shadow */
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 1;
        float: left;
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Form Layout Wrapper */
    div.js-ticket-add-form-wrapper {
        float: none;
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 25px; /* Consistent gap between fields */
    }

    /* Form Field Wrappers */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
        flex: 1 1 calc(50% - 12.5px); /* Two-column layout */
        margin: 0; /* Reset margin due to gap */
        min-width: 300px;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width {
        flex: 1 1 100%; /* Full-width layout */
       
    }

    /* Field Titles */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
        font-weight: 600;
        margin-bottom: 15px;
        color: ' . $color2 . ';
    }
    div.js-ticket-append-signature-wrp div.js-ticket-append-field-title {
        font-size: 17px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    /* Field Containers */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field {
        float: left;
        width: 100%;
    }

    /* Input and Select Fields Styling */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select {
        float: left;
        width: 100%;
        border-radius: 10px; /* Softer rounded corners */
        padding: 12px 18px; /* More generous padding */
        line-height: normal;
        height: auto;
        min-height: 52px;
        border: 1px solid ' . $color5 . ' !important;
        color: ' . $color4 . ';
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        background-color: #fcfcfc;
    }

    /* Select Field Specifics (Custom Arrow) */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E");
        background-position: calc(100% - 15px) center;
        background-repeat: no-repeat;
        background-size: 1.5em;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Focus State for Inputs and Selects */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus {
        outline: none;
        border-color: ' . $color1 . ';
        box-shadow: 0 0 0 4px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.25);
        background-color: #ffffff;
    }

    /* Radio Button Styling */
     div.js-ticket-radio-btn-wrp{width: 100%;
    border-radius: 12px;
    align-items: center;
    gap: 20px;
    height: auto;
    min-height: 52px;
    box-sizing: border-box;}
    div.js-ticket-signature-radio-box {
        width: 100%;
        background-color: ' . $color3 . ';
        border: 1px solid ' . $color5 . ';
        border-radius: 12px; /* Softer rounded container */
        padding: 12px 20px; /* More padding */
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
        height: auto;
        min-height: 52px;
        box-sizing: border-box;
    }
    div.js-ticket-signature-radio-box {
        width: auto;
        flex-grow: 1;
    }
    .jsst-formfield-radio-button-wrap {
        display: flex;
        align-items: center;
        cursor: pointer;
        display: inline-flex
;
    align-items: center;
    background-color: white;
    border: 1px solid ' . $color5 . ';
    padding: 13px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 10px;
    }
     .jsst-formfield-radio-button-wrap input[type="radio"] {
        margin-right: 8px;
        vertical-align: middle;
        width: 18px;
        height: 18px;
        accent-color: ' . $color1 . ';
        margin-top:0;

    }
    input.radiobutton.js-ticket-append-radio-btn {
        display: inline-block !important;
        margin: 0 12px 0 0 !important;
        transform: scale(1.3);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        border: 1px solid '. $color5 .';
        border-radius: 4px;
        background-color: #ffffff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
    }
    input.radiobutton.js-ticket-append-radio-btn::after{
        content: "";
        position: absolute;
        top: 0px;
        left: 0px;
        width: 12px;
        height: 12px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E"); /* White checkmark SVG */
        background-size: contain;
        background-repeat: no-repeat;
    }
        input.radiobutton.js-ticket-append-radio-btn:checked{
        border-color: ' . $color1 . ';
        background-color: ' . $color1 . ';
        color:#fff;
    
    }
    .jsst-formfield-radio-button-wrap label {
        cursor: pointer;
        color: ' . $color4 . ';
        font-weight: 500;
        margin: 0;
    }
    
    /* Signature section layout */
    div.js-ticket-append-signature-wrp{
        float: none;
        width: 100%;
        display: flex;
        flex-direction: column;
    }
     div.js-ticket-append-signature-wrp div.js-ticket-append-field-wrp{
        display: flex;
        gap: 15px;
    }
    
    /* Form Buttons Wrapper */
    div.js-ticket-form-btn-wrp {
        float: left;
        width: 100%;
        margin: 40px 0 10px 0;
        text-align: center;
        padding: 30px 0 10px 0;
        border-top: 1px solid ' . $color5 . ';
    }
    
    /* Save Button */
    div.js-ticket-form-btn-wrp input.js-ticket-save-button {
        background-color: ' . $color1 . ' !important;
        color: ' . $color7 . ' !important;
        border: 1px solid ' . $color1 . ';
        margin-right: 20px;
        box-shadow: 0 8px 25px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
        padding: 16px 30px;
        min-width: 160px;
        border-radius: 10px;
        line-height: initial;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
        background-color: ' . $color2 . ' !important;
        border-color: ' . $color2 . ' !important;
        transform: translateY(-3px);
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
    }
    
    /* Cancel Button */
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
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
        background-color: ' . $color2 . ';
        color:#fff;
        border-color: ' . $color4 . ';
        transform: translateY(-2px);
    }
    
    /* Error Message Styling */
    span.jsst-help-block {
        display: flex !important;
        font-size: 14px;
        color: #c0392b !important;
        margin-top: 10px;
        padding: 5px 15px;
        background-color: #fff0f0;
        border: 1px solid #e74c3c;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 3px 10px rgba(231, 76, 60, 0.15);
        position: relative;
        bottom: 7px;
        width: 100%;
    }

   
   .js-ticket-from-field.js-ticket-form-field-select.has-error span.jsst-help-block {
        font-size: 14px;
        position: relative;
        bottom: 7px;
        width: 100%;
        left: 0;
        z-index: 1;
    }

    .js-support-ticket-outgoing-email-message {
        font-size: 15px;
        color: #636363;
        opacity: 0.9;
        line-height: 1.5;
        padding:5px 5px 0 5px;
        display:flex;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
            flex: 1 1 100%; /* Single column on smaller screens */
        }
    }
    @media (max-width: 767px) {
        form.js-ticket-form {
            padding: 25px; /* Adjust padding for smaller screens */
        }
        div.js-ticket-form-btn-wrp input.js-ticket-save-button,
        div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
        div.js-ticket-append-signature-wrp div.js-ticket-append-field-wrp {
            flex-direction: column;
        }
    }
';

/* Simplified color block - most colors are now inline with the modern styles */
$jssupportticket_css .= '
    /* Cancel Button Hover Color - specific override */
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
        border-color: ' . $color1 . ';
    }
    
';

wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);
?>
