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
        width: 100%;
        padding: 40px;
        background-color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 0;
        column-gap: 25px;
        box-sizing: border-box;
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
        display: flex;
        flex-wrap: wrap;
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
    }

    /* Field Title */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
        width: 100%;
        margin-bottom: 10px;
        font-weight: 600;
        float: none;
    }

    /* Field Container */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field {
        width: 100%;
        position: relative;
        float: none;
    }

    /* Unified Input, Select, and Textarea Fields */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select {
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
        float: none;
    }

    /* Custom Select Arrow */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat;
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
        bottom: 15px;
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
        padding: 30px 0 10px 0;
        border-top-width: 1px;
        border-top-style: solid;
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        float: none;
    }

    /* Modern Buttons */
    div.js-ticket-form-btn-wrp input.js-ticket-save-button,
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
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
        margin: 0;
    }

    /* Button Hover Effects */
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
        border-color: ' . $color2 . ' !important;
        transform: translateY(-3px);
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
        filter: brightness(1.1);
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
        border-color: ' . $color2 . ';
        transform: translateY(-2px);
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
    }

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
        div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
            flex: 1 1 100%; /* Single column on smaller screens */
        }
        div.js-ticket-form-btn-wrp input.js-ticket-save-button,
        div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
    }
';
/*Code For Colors*/
$jssupportticket_css .= '
    /* Add Form Colors */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color: ' . $color2 . ';}

    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select {
        background-color:#fcfcfc;
        border:1px solid ' . $color5 . ';
        color: ' . $color4 . ';
    }
    
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field textarea:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select:focus {
        outline: none;
        border-color: ' . $color1 . ';
        box-shadow: 0 0 0 4px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.25);
    }

    div.js-ticket-form-btn-wrp{border-top:1px solid ' . $color2 . ';}
    
    /* Save Button */
    div.js-ticket-form-btn-wrp input.js-ticket-save-button{
        background-color:' . $color1 . ' !important;
        color:' . $color7 . ' !important;
        border: 1px solid ' . $color1 . ';
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{
        background-color:' . $color2 . ' !important;
        color:' . $color7 . ' !important;
    }

    /* Cancel Button */
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
        background-color: #f5f2f5;
        color: ' . $color4 . ';
        border: 1px solid ' . $color5 . ';
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
        background-color:' . $color2 . ' !important;
        color:' . $color7 . ' !important;
        border-color: ' . $color2 . ' !important;
    }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
