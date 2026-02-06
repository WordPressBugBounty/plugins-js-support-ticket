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
        flex-direction: column;
        width: 100%;
        padding: 40px;
        background-color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 0;
        margin-top: 17px;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Form Layout Wrapper */
    div.js-ticket-add-form-wrapper{
        float: left;
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
		width: 100%;
		padding: 25px;
		background-color: #ffffff;
		border-radius: 20px;
		box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
		animation: fadeIn 0.8s ease-out forwards;
		opacity: 0;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
        flex: 1 1 calc(50% - 12.5px);
        margin: 0;
        min-width: 300px;
        margin-bottom: 30px;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{
        flex: 1 1 100%;
        margin-bottom: 30px;
        display:flex;
        flex-wrap:wrap;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{
        float: left;
        width: 100%;
        margin-bottom: 10px;
        font-weight: 600;
        color:' . $jsst_color2 . ';
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{
        float: left;
        width: 100%;
        position: relative;
    }
    
    /* Input, Select, Textarea Styling */
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
    div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field {
        float: left;
        width: 100%;
        border-radius: 10px;
        padding: 12px 18px;
        min-height:52px;
        line-height: normal;
        height: auto;
        min-height: 52px;
        border: 1px solid ' . $jsst_color5 . ';
        color: ' . $jsst_color4 . ';
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        background-color: #fcfcfc;
        max-width:100%;
    }
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
    div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-form-input-field:focus {
        outline: none;
        border-color: ' . $jsst_color1 . ';
        box-shadow: 0 0 0 4px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.25);
        background-color: #ffffff;
    }

    /* Error Styling */
    .js-ticket-from-field-wrp.error input.js-ticket-form-field-input,
    .js-ticket-from-field-wrp.error select.js-form-input-field {
        border-color: #e74c3c !important;
        box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.25) !important;
    }
    span.jsst-help-block,
    .jsst-help-block.form-error {
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
     .jsst-help-block.form-error::before {
        content: none;
     }
    @keyframes slideInFromTop {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Roles Section Wrapper Styling */
    div.js-ticket-roles-wrapper,
    div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width {
        padding: 25px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid ' . $jsst_color5 . ';
        background-color: ' . $jsst_color3 . ';
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin: 0 0 30px 0;
        flex: 1 1 100%;
        box-sizing: border-box;
		display: flex;
		flex-wrap: wrap;
    }
    div.js-ticket-role-wrp {
        float: left;
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
        align-items: flex-start;
    }

    /* Permission Item "Card" Styling */
    div.js-ticket-role-wrp div.js-ticket-add-role-field-wrp {
        width:calc(100% / 3 - 14px);
        margin: 0;
        padding: 14px 18px;
        background-color: #ffffff;
        border: 1px solid ' . $jsst_color5 . ';
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
        display: flex;
        align-items: center;
    }
    
    /* Roles Section Heading */
    div.js-ticket-categories-heading-wrp {
        width: 100%;
        padding: 15px;
        font-weight: 700;
        color:#fff;
        border-bottom: 1px solid ' . $jsst_color5 . ';
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #000;
        border-radius: 10px;
    }
    span.js-ticket-roles-section-heading-right {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    span.js-ticket-roles-section-heading-right label {
        font-weight: 700;
        margin: 0;
        cursor: pointer;
        line-height: 1;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Custom Checkbox Styling */
    input.js-ticket-checkbox,
    span.js-ticket-roles-section-heading-right input,
    span.js-ticket-roles-section-heading-right input {
        display: inline-block !important;
        margin:5px 0 0 0 !important;
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
    }
    span.js-ticket-roles-section-heading-right input{
        margin:0 !important;
    }
    input.js-ticket-checkbox:checked,
    span.js-ticket-roles-section-heading-right input:checked {
        background-color: ' . $jsst_color1 . ';
        border-color: ' . $jsst_color1 . ';
    }
    input.js-ticket-checkbox:checked::after,
    span.js-ticket-roles-section-heading-right input:checked::after {
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
    label.js-ticket-label {
        display:flex;
        gap: 10px;
        cursor: pointer;
        color: ' . $jsst_color4 . ';
        font-weight: normal;
        align-items: flex-start;
    }
        

    /* Button Wrapper & Styling */
    div.js-ticket-form-btn-wrp{
        float: left;
        width: 100%;
        margin: 40px 0 10px 0;
        text-align: center;
        padding: 30px 0 10px 0;
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
        box-shadow: 0 8px 25px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.4);
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
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($jsst_color2, 1, 2)) . ', ' . hexdec(substr($jsst_color2, 3, 2)) . ', ' . hexdec(substr($jsst_color2, 5, 2)) . ', 0.5);
        filter: brightness(1.1);
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
        background-color: ' . $jsst_color3 . ';
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
    
    /* Responsive adjustments */
    @media (max-width: 991px) {
        div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
            flex: 1 1 100%;
        }
        div.js-ticket-role-wrp div.js-ticket-add-role-field-wrp {
            flex: 1 1 calc(50% - 10px);
        }
    }
    @media (max-width: 767px) {
        form.js-ticket-form{
            padding: 25px;
        }
        div.js-ticket-form-btn-wrp input.js-ticket-save-button,
        div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
    }
    @media (max-width: 650px) {
       div.js-ticket-role-wrp div.js-ticket-add-role-field-wrp {
           flex: 1 1 100%;
       }
    }
';

/*Code For Colors*/
$jsst_jssupportticket_css .= '
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input{border:1px solid ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
    div.js-ticket-form-btn-wrp{border-top:1px solid ' . $jsst_color5 . ';}
    div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:' . $jsst_color1 . ' !important;color:' . $jsst_color7 . ' !important;border: 1px solid ' . $jsst_color1 . ';}
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{border-color: ' . $jsst_color2 . ';}
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background-color:#f5f2f5;color:' . $jsst_color4 . ';}
    label.js-ticket-label{color:' . $jsst_color4 . ';}
    span.jsst-help-block, .jsst-help-block.form-error {color:#c0392b !important;}
';

wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);

?>
