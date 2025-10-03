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
/* Ticket Status Form Styling */
form.js-ticket-form {
   
    width: 100%;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
    display: contents;
    justify-content: center;
    gap: 15px;
    justify-content: center;
    justify-self: center;
}

/* Form Fields Wrapper - Converted to Flexbox */
div.js-ticket-checkstatus-wrp p.js-support-tkentckt-centrmainwrp {
    font-size: 17px !important;
    position: relative;
    display: inline-block;
    text-align: center;
    margin: 20px 10px 20px 10px;
    font-weight: bold;
    width: calc(100% - 20px);
}

div.js-ticket-checkstatus-wrp {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    width: 100%;
    padding: 40px;
    border-radius: 20px;
    animation: fadeIn 0.8s ease-out forwards;
    /*opacity: 0;*/
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    background-color:#fff;
    border-radius:20px;
}
/* Individual Form Field Wrapper */
div.js-ticket-checkstatus-wrp div.js-ticket-checkstatus-field-wrp {
    flex: 1 1 calc(50% - 12.5px); /* Responsive two-column layout */
    margin: 0; /* Margin is replaced by gap */
    min-width: 300px;
    position: relative;
}

/* Field Title */
div.js-ticket-field-title {
    width: 100%;
    margin-bottom: 10px;
    font-weight: 600;
}

/* Field Container */
div.js-ticket-field-wrp {
    width: 100%;
    position: relative;
}

/* Unified Input Field Style */
div.js-ticket-field-wrp input.js-ticket-form-input-field {
    width: 100%;
    border-radius: 10px; /* Softer corners */
    padding: 14px 18px; /* More padding */
    line-height: normal;
    height: auto; /* Automatic height */
    min-height: 52px; /* Minimum height */
    border-width: 1px;
    border-style: solid;
    transition: all 0.3s ease;
    box-sizing: border-box;
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
    box-sizing: border-box;
}
    

/* Button Hover Effects */
div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
    transform: translateY(-3px);
    filter: brightness(1.1);
}
div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 991px) {
    div.js-ticket-checkstatus-wrp div.js-ticket-checkstatus-field-wrp {
        flex: 1 1 100%; /* Single column */
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
$jssupportticket_css .= '

/*Ticket Status*/
	div.js-ticket-field-wrp input.js-ticket-form-input-field{background-color:#fcfcfc; border:1px solid ' . $color5 . ' !important;color:' . $color4 . ';}
	div.js-ticket-field-title{color:' . $color2 . ';}
	div.js-ticket-form-btn-wrp{border-top:1px solid ' . $color5 . ';}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button{background-color:' . $color1 . ' !important;color:' . $color7 . ' !important;border: none;box-shadow: 0 2px 10px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{background-color:' . $color2 . '!important;color:' . $color7 . ' !important;box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{background-color: #f5f2f5;color: #636363;border: 1px solid '. $color5 .';}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{background-color:' . $color2 . '!important;color:' . $color7 . ' !important;border-color:' . $color2 . '!important;}

/*Ticket Status*/

';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
