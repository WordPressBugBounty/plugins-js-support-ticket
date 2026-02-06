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
    display: inline-block;
    width: 100%;
    padding: 40px; /* Increased padding */
    border-radius: 20px; /* Softer rounded corners */
    animation: fadeIn 0.8s ease-out forwards;
    opacity: 0;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    column-gap: 25px;
    }

    /* Keyframe Animations */
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

    /* Form Fields Wrapper - Converted to Flexbox */
    div.js-ticket-add-form-wrapper {
        width: 100%;
        display: flex; /* Use Flexbox for layout */
        flex-wrap: wrap; /* Allow items to wrap to new lines */
        gap: 25px; /* Modern spacing between fields */
        background-color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.8s ease-out forwards;
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
    }

    /* Field Container */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field {
        width: 100%;
        position: relative;
    }

    /* Unified Input and Select Fields */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#visible,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid {
        width: 100%;
        border-radius: 10px; /* Softer corners */
        padding: 12px 18px; /* More padding */
        line-height: normal;
        height: auto; /* Automatic height */
        min-height: 52px; /* Minimum height for better touch targets */
        border-width: 1px;
        border-style: solid;
        transition: all 0.3s ease; /* Generic transition */
        box-sizing: border-box; /* Consistent box model */
    }

    /* Specific styling for Select fields */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#visible,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%230c0c0c\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.2em;
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

    /* Save & Cancel Button Styling */
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
        border-width: 1px;
        border-style: solid;
    }
    
    div.js-ticket-form-btn-wrp input.js-ticket-save-button {
        margin-right:0;
    }

    /* Button Hover Effects */
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
        transform: translateY(-3px);
        box-shadow:0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);;
        filter: brightness(1.1);
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
        transform: translateY(-2px);
    }

    /* Help Block for validation */
    span.jsst-help-block {
        display: block !important;
        font-size: 14px;
        padding: 5px 15px;
        border-radius: 8px;
        font-weight: 600;
        box-sizing: border-box;
        width: 100%;
        position: relative;
        z-index: 2;
        bottom: 15px; /* Adjusted position */
        animation: slideInFromTop 0.4s ease-out forwards;
        opacity: 0;
    }

    select::-ms-expand {display:none !important;}
    
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
        }
    }
';
/*Code For Colors*/
$jsst_jssupportticket_css .= '

    /* Add Form Colors */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
        color: ' . $jsst_color2 . ';
    }
   
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#visible,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid {
        background-color:#fcfcfc;
        border:1px solid ' . $jsst_color5 . ';
        color: ' . $jsst_color4 . ';
    }

    div.js-ticket-form-btn-wrp{
        border-top:1px solid ' . $jsst_color5 . ';
    }

    div.js-ticket-form-btn-wrp input.js-ticket-save-button{
        background-color:' . $jsst_color1 . ' !important;
        color:' . $jsst_color7 . ' !important;
        border-color: ' . $jsst_color1 . ';
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
    }

    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{
        border-color:' . $jsst_color2 . ';
        background-color:' . $jsst_color2 . '!important;
    }

    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
        background: #f5f2f5;
        color: #636363;
        border:1px solid '. $jsst_color5 .';
    }

    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
        border-color: ' . $jsst_color2 . ';
        background-color:' . $jsst_color2 . '!important;
        color:' . $jsst_color7 . ' !important;
    }
    
    span.jsst-help-block {
        color: red;
        border: 1px solid red;
        background-color: #fff0f0;
    }
    /* End Add Form Colors */
';


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
