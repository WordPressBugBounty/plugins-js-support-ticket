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
/* User Register Modernized */

/* General Error Styling */
div.jsst_errors {
    width: 100%;
    padding: 0;
    margin-bottom: 25px;
}
div.jsst_errors span.error {
    display: block;
    width: 100%;
    padding: 15px 20px;
    margin-bottom: 10px;
    line-height: 1.6;
    background-color: #fff0f0;
    border: 1px solid #e74c3c;
    color: #c0392b;
    border-radius: 8px;
    font-weight: 500;
    box-sizing: border-box;
}

/* Modern Form Styling */
form#jsst_registration_form {
    width: 100%;
    padding: 40px;
    margin: 0;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    background:#fff;
    border-radius:20px;
}

/* Form Fields Wrapper - Converted to Flexbox */
div.js-ticket-add-form-wrapper {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 25px; /* Modern spacing between fields */
    margin: 0 0 30px !important;
}

/* Individual Form Field Wrapper */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
    flex: 1 1 calc(50% - 12.5px); /* Responsive two-column layout */
    margin: 0; /* Margin is replaced by gap */
    min-width: 300px;
    position: relative; /* For error message positioning */
}

/* Full-Width Field Wrapper */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width {
    flex: 1 1 100%;
}

/* Field Title */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
    width: 100%;
    margin-bottom: 10px;
    font-weight: 600;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-subscribe {
    padding: 15px;
    background: #e9f7ef;
    border: 1px solid #28a745;
    border-radius: 8px;
    width: 100%;
    display:flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    min-height: 52px;
}
/* Field Container */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field {
    width: 100%;
    position: relative;
}

/* Unified Input, Select, and Recaptcha Fields */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
input.js-ticket-recaptcha {
    width: 100%;
    border-radius: 10px;
    padding: 12px 18px;
    line-height: normal;
    height: auto;
    min-height: 52px;
    border-width: 1px;
    border-style: solid;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

/* Focus effect for inputs from login form */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Specific styling for Select fields */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%230c0c0c\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1.2em;
}

/* Checkbox styling from login form */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp label,
div.js-ticket-subscribe label {
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp input[type="checkbox"],
div.js-ticket-subscribe input[type="checkbox"] {
    display: inline-block !important;
    margin: 0 !important;
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
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp input[type="checkbox"]::after,
div.js-ticket-subscribe input[type="checkbox"]::after {
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
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .jsst-formfield-radio-button-wrap{
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    background-color:'. $color3 .';
    border:1px solid '. $color5 .';
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp input[type="checkbox"]:checked,
div.js-ticket-subscribe input[type="checkbox"]:checked {
    background-color: ' . $color1 . ';
    border-color: ' . $color1 . ';
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-subscribe input[type="checkbox"]{
    border-color: #28a745;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-subscribe input[type="checkbox"]:checked{
    background-color: #28a745;
    border-color: #28a745;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-recaptcha{
    margin-top:5px;
    height:52px;
}

/* Button Wrapper */
div.js-ticket-form-btn-wrp {
    width: 100%;
    margin: 20px 0 10px 0;
    text-align: center;
    padding: 30px 0px 10px 0px;
    border-top-width: 1px;
    border-top-style: solid;
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    flex: 1 1 100%;
}

/* Save Button */
div.js-ticket-form-btn-wrp input.js-ticket-save-button {
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
    border: none;
}

/* Cancel Button */
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

/* Button Hover Effects */
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover,
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
    transform: translateY(-3px);
    filter: brightness(1.1);
}

/* Field-level error styling from login form */
span.jsst-help-block {
    display: block !important;
    font-size: 14px;
    padding: 5px 15px;
    border-width: 1px;
    border-style: solid;
    border-radius: 8px;
    font-weight: 600;
    box-sizing: border-box;
    width: 100%;
    position: relative;
    z-index: 2;
    color: #e74c3c;
    background-color: #fff0f0;
    border-color: #e74c3c;
}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-custom-terms-and-condition-box span.jsst-help-block{
    position: absolute;
    bottom: -26px;
}

select::-ms-expand { display:none !important; }

/* Responsive */
@media (max-width: 991px) {
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
        flex: 1 1 100%; /* Single column on smaller screens */
    }
}
@media (max-width: 768px) {
    form#jsst_registration_form {
        padding: 25px;
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button,
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
        width: 100%;
        margin-right: 0;
    }
}
@media (max-width: 480px) {
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{flex: 1 1 100%;}
}
';
/*Code For Colors*/
$jssupportticket_css .= '
/* Registration Form Colors */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title,
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field label {
        color: ' . $color2 . ';
    }

	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    input.js-ticket-recaptcha {
		background-color: #fcfcfc;
        border:1px solid ' . $color5 . ';
        color:' . $color4 . ';
	}

	div.js-ticket-form-btn-wrp {
        border-top:1px solid ' . $color2 . ';
    }
	
    div.js-ticket-form-btn-wrp input.js-ticket-save-button {
        background-color:' . $color1 . ' !important;
        color:' . $color7 . ' !important;
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
    }
	
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
        background-color:' . $color2 . '!important;
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
    }
	
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
        background-color: #f5f2f5;
        color: #636363;
        border: 1px solid '. $color5 .';
    }
	
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
        background-color:' . $color2 . '!important;
        color:' . $color7 . ' !important;
        border-color:' . $color2 . ';
    }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
