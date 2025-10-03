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

/* Modernized CSS Code */
$jssupportticket_css .= '
    /* General Styles & Box Sizing */
    div.js-ticket-profile-wrp, div.js-ticket-profile-wrp *,
    div.js-ticket-add-form-wrapper, div.js-ticket-add-form-wrapper * {
        box-sizing: border-box;
    }

    /* Main Profile Container */
    div.js-ticket-profile-wrp {
        float: left;
        width: 100%;
        margin-top: 30px;
        background-color: #FFFFFF;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.07);
        border: 1px solid ' . $color5 . ';
    }

    /* Profile Left - User Image Section */
    div.js-ticket-profile-wrp div.js-ticket-profile-left {
        float: left;
        width: 200px;
        text-align: center;
        padding-right: 20px;
    }

    div.js-ticket-profile-wrp div.js-ticket-profile-left div.js-ticket-user-img-wrp {
        float: none;
        width: 180px;
        height: 180px;
        position: relative;
      
        overflow: hidden;
        margin: 0 auto 20px auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    div.js-ticket-profile-wrp div.js-ticket-profile-left div.js-ticket-user-img-wrp img.profile-image {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        object-fit: cover; /* Prevents image stretching */
    }

    /* Profile Right - Form Area */
    div.js-ticket-profile-wrp div.js-ticket-profile-right {
        float: left;
        width: calc(100% - 200px);
        margin: 0;
    }

    /* Form Wrapper */
    div.js-ticket-add-form-wrapper {
        float: left;
        width: 100%;
    }

    /* Form Field Wrapper (for 2-column layout) */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
        float: left;
        width: calc(50% - 15px);
        margin: 0 7.5px 25px;
    }

    /* Full-width Form Field Wrapper */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width {
        width: calc(100% - 15px);
    }

    /* Form Field Title */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
        float: left;
        width: 100%;
        margin-bottom: 8px;
        font-weight: 600;
    }

    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field {
        float: left;
        width: 100%;
        position: relative;
    }

    /* General Input, Select, Textarea Styles */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    textarea, span.js-ticket-input-field-style {
        width: 100%;
        border-radius: 8px !important;
        padding: 12px 18px;
        min-height:52px;
        line-height: 1.6;
        height: auto; /* Auto height for flexibility */
        transition: border-color .2s ease-in-out, box-shadow .2s ease-in-out;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    
    /* Focus States for inputs */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus,
    textarea:focus {
        outline: none !important;
    }

    /* Specific Select Styling to add dropdown arrow */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#categoryid,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#status,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width div.js-ticket-from-field select#status,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select#parentid {
        background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/selecticon.png) !important;
        background-position: right 18px center !important;
        background-repeat: no-repeat !important;
        background-size: 14px !important;
        padding-right: 45px !important; /* Make space for arrow */
    }

    /* Password eye icon */
    img.js-ticket-profile-form-img {
        position:absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 15px;
        cursor: pointer;
        opacity: 0.5;
        transition: opacity .2s;
    }
    img.js-ticket-profile-form-img:hover { opacity: 1; }

    /* File Upload Button */
    div#showhidemouseover {
        position: relative;
        display: inline-block;
        min-width: 180px;
        text-align: center;
        margin: 0;
    }

    label.js-ticket-file-upload-label {
        display: block;
        padding: 12px 0px;
        border-radius: 8px;
        transition: all .25s ease;
        font-weight: 500;
        cursor: pointer;
    }

    label.js-ticket-file-upload-label:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    input.js-ticket-upload-input {
        position: absolute; left: 0; top: 0; right: 0; bottom: 0;
        width: 100%; height: 100%; opacity: 0; cursor: pointer;
    }

    /* Button Wrapper */
    div.js-ticket-form-btn-wrp {
        float: left;
        width: 100%;
        margin: 15px 0 0 0;
        text-align: right; /* Align buttons to the right */
        padding: 25px 0 0 0;
    }

    /* Save & Cancel Buttons */
    div.js-ticket-form-btn-wrp input.js-ticket-save-button,
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
        display: inline-block;
        padding: 12px 30px;
        margin-left: 10px;
        min-width: 130px;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid transparent;
        transition: all .25s ease;
        cursor: pointer;
        text-decoration: none;
        line-height: 1.6;
    }

    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover,
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    /* Downloads section */
    div.js-ticket-downloads-wrp {
        float: left;     
        width: 100%;
    }
    
    div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp {
        float: left; width: 100%; padding: 18px 25px;
        line-height: 1.4; font-weight: 600; font-size: 17px;
        border-radius: 12px;
    }

    /* Help block */
    span.jsst-help-block {
        font-size: 14px;
        display: block; margin-top: 6px;
        color:red;
    }
';
/*Code For Modern Colors*/
$jssupportticket_css .= '


    /* Form field titles */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {
        color: ' . $color2 . ';
    }

    /* Inputs, Selects, Textarea styling */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
    textarea, span.js-ticket-input-field-style, input.js-ticket-recaptcha {
        background-color: #fff;
        border: 1px solid ' . $color5 . ';
        color: ' . $color4 . ';
    }

    /* Focus state color */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus,
    textarea:focus {
        border-color: ' . $color2 . ' !important;
        box-shadow: 0 0 0 3px ' . $color2 . '33; /* Use primary color with low opacity for glow */
    }

    /* User image wrapper border */
    div.js-ticket-profile-wrp div.js-ticket-profile-left div.js-ticket-user-img-wrp {
        background-color: #fff;
        border: 1px solid ' . $color5 . ';
    }

    /* Button colors */
    div.js-ticket-form-btn-wrp { border-top: 1px solid ' . $color5 . '; }
    
    div.js-ticket-form-btn-wrp input.js-ticket-save-button {
        background-color: ' . $color2 . ' !important;
        color: ' . $color7 . ' !important;
        border-color: ' . $color2 . ' !important;
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
        filter: brightness(110%);
    }

    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
        background-color: #F1F3F5;
        color: #555;
        border: 1px solid ' . $color5 . ';
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
        background-color: #E9ECEF;
        border-color: '. $color5 .';
    }

    /* File upload button colors */
    label.js-ticket-file-upload-label {
        background-color: ' . $color2 . ';
        color: ' . $color7 . ';
        border: 1px solid ' . $color2 . ';
    }
    label.js-ticket-file-upload-label:hover {
        filter: brightness(110%);
    }

    /* Downloads heading colors */
    div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp {
        background-color: ' . $color2 . ';
        color: ' . $color7 . ';
        border: none;
    }

    /* Specific white background override */
    input.js-ticket-white-background{ background-color:' . $color7 . ' !important; }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
