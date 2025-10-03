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
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.js-export-wrapper {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
}

div.js-ticket-add-form-wrapper {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
}

/* Individual Form Field Wrapper */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
    flex: 1 1 calc(50% - 12.5px);
    margin: 0;
    min-width: 300px;
    position: relative;
    margin-bottom: 30px;
}

div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width {
    flex: 1 1 100%;
}

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

div.js-ticket-select-user-field {
    width: 100%;
    position: relative;
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
}

/* Custom Select Arrow */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select,
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat;
}

/* Styling for required field errors (Added from source) */
.js-ticket-from-field-wrp.error input,
.js-ticket-from-field-wrp.error textarea,
.js-ticket-from-field-wrp.error select {
    border-color: #e74c3c !important;
    box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.25) !important;
}

/* Improved error message styling (Added from source) */
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
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Radio buttons (Preserved from original file) */
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .jsst-formfield-radio-button-wrap {
    display: inline-flex;
    align-items: center;
    margin-right: 15px;
    accent-color: ' . $color1 . ';
}
div.js-ticket-radio-btn-wrp {
    width: 100%;
    padding: 14px 18px;
    min-height: 52px;
    border: 1px solid '. $color5 .';
    border-radius: 10px;
    box-sizing: border-box;
    display: flex;
    align-items: center;
}
div.js-ticket-radio-btn-wrp input.js-ticket-form-field-radio-btn{
    margin-right: 8px;
    vertical-align: middle;
    margin-top: 0px;
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

/* --- Unchanged Popup and Table Styles --- */
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
    min-height:52px;
    height: 100%;
    text-align: center;
    text-decoration: none;
    outline: 0px;
    line-height: initial;
    box-sizing: border-box;
}
/* Popup Styling */
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
    z-index: 99999;
    overflow-y: auto;
    overflow-x: hidden;
    transform: translate(-50%,-50%);
    box-sizing: border-box;
    border-radius:15px;
}
div#userpopup * {box-sizing: border-box;}
div#userpopup .userpopup-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid ' . $color5 . ';
}
div#userpopup .userpopup-top .userpopup-heading { font-size: 21px; font-weight: 600; }
div#userpopup .userpopup-top .userpopup-close {
    width: 24px;
    height: 24px;
    cursor: pointer;
    background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/close-icon-black.png);
    background-repeat: no-repeat;
    background-position: center;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}
div#userpopup span.userpopup-close {width: 28px;height: 28px;cursor: pointer;background-image: url(\'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"%3e%3cline x1="18" y1="6" x2="6" y2="18"%3e%3c/line%3e%3cline x1="6" y1="6" x2="18" y2="18"%3e%3c/line%3e%3c/svg%3e\') !important;background-repeat: no-repeat !important;background-position: center center !important;background-size: contain !important;transition: transform 0.3s ease-in-out;margin-left: auto;}
div#userpopup span.userpopup-close {transform: rotate(90deg);}
div#userpopup .userpopup-top .userpopup-close:hover { opacity: 1;transform: rotate(90deg); }
div#userpopup .userpopup-search { padding: 24px; margin: 10px;border-radius:10px; }
div.js-ticket-table-wrp div.js-ticket-table-header {
    border-top-right-radius: 10px;
    border-top-left-radius: 10px;
    border:1px solid '. $color5 .';
}
    div.js-ticket-table-body div.js-ticket-data-row:last-child:last-child {
    border-bottom-right-radius: 10px;
    border-bottom-left-radius: 10px;
}
div#userpopup .userpopup-search form { display: flex; flex-wrap: wrap; gap: 16px; }
div#userpopup .userpopup-search form .userpopup-fields-wrp { flex: 3; display: flex; flex-wrap: wrap; gap: 12px; }
div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-fields { flex: 1; min-width: 150px; margin:0; }
div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-fields input {
    width: 100%;
    padding: 12px 16px;
    border-radius: 10px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: unset;
    height: auto;
}
div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-fields input:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
div#userpopup .userpopup-search form .userpopup-btn-wrp { flex: 1; display: flex; align-items: flex-start; gap: 12px; width: 100%; }
div#userpopup .userpopup-search form .userpopup-btn-wrp input {
    flex: 1;
    padding: 12px 16px;
    font-weight: 500;
    border-radius: 10px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    white-space: nowrap;
    margin:0;
}
div#userpopup #userpopup-records-wrp { flex-grow: 1; overflow-y: auto; padding: 0 10px 24px; width:100%; }
.js-ticket-table-wrp .js-ticket-table-header, .js-ticket-table-wrp .js-ticket-data-row { display: flex; width: 100%; padding: 0 16px; border: none; }
.js-ticket-table-wrp .js-ticket-table-header { padding-top: 16px; padding-bottom: 16px; font-weight: 600; }
.js-ticket-table-wrp .js-ticket-data-row:hover { background-color: #f1f3f5; }
.js-ticket-table-wrp .js-ticket-table-header-col, .js-ticket-table-wrp .js-ticket-table-body-col { flex: 1; padding: 16px 8px; text-align: left; display: flex; align-items: center; }
.js-ticket-table-wrp .js-ticket-table-header-col:nth-child(1), .js-ticket-table-wrp .js-ticket-table-body-col:nth-child(1) { flex-basis: 10%; width: 10%; }
.js-ticket-table-wrp .js-ticket-table-header-col:nth-child(2), .js-ticket-table-wrp .js-ticket-table-body-col:nth-child(2) { flex-basis: 25%; width: 25%; }
.js-ticket-table-wrp .js-ticket-table-header-col:nth-child(3), .js-ticket-table-wrp .js-ticket-table-body-col:nth-child(3) { flex-basis: 40%; width: 40%; }
.js-ticket-table-wrp .js-ticket-table-header-col:nth-child(4), .js-ticket-table-wrp .js-ticket-table-body-col:nth-child(4) { flex-basis: 25%; width: 25%; }
.js-userpopup-link:hover { text-decoration: underline; }
.js-ticket-display-block { display: none; }
.jsst_userpages { text-align: center; padding: 24px 0 0; width: 100%; }
.jsst_userlink { display: inline-block; padding: 8px 12px; margin: 0 4px; border-radius: 10px; text-decoration: none; }
.jsst_userlink.selected { font-weight: 600; }
select::-ms-expand {display:none !important;}
select{-webkit-appearance:none !important;}
/* --- End of Unchanged Styles --- */

/* Responsive */
@media (max-width: 991px) {
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp {
        flex: 1 1 100%;
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
    .js-ticket-table-wrp .js-ticket-table-header { display: none; }
    .js-ticket-table-wrp .js-ticket-data-row { flex-direction: column; padding: 12px 0; }
    .js-ticket-table-wrp .js-ticket-table-body-col {
        flex-basis: auto !important;
        width: 100%;
        padding: 8px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed ' . $color5 . ';
    }
    .js-ticket-table-wrp .js-ticket-data-row .js-ticket-table-body-col:last-child { border-bottom: none; }
    .js-ticket-display-block {
        display: inline-block;
        font-weight: 600;
        margin-right: 8px;
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
    div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select,
    div.js-ticket-radio-btn-wrp {
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
        box-shadow: 0 8px 25px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
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

	/* --- Unchanged Popup and Table Colors --- */
	.js-userpopup-link{color:' . $color2 . ';}
	div.js-ticket-select-user-btn a#userpopup{background-color:' . $color1 . ';color:' . $color7 . ';border: 1px solid ' . $color1 . ';}
	div.js-ticket-select-user-btn a#userpopup:hover{background-color:' . $color2 . '; border-color:' . $color2 . ';}
	div#userpopup{background:#fff;}
	div#userpopup .userpopup-top {background-color: ' . $color3 . '; color:' . $color2 . ';}
	div#userpopup .userpopup-search {background-color:' . $color3 . '; border: 1px solid ' . $color5 . '!important;}
	div#userpopup .userpopup-search form .userpopup-fields-wrp .userpopup-fields input{border:1px solid ' . $color5 . ';background-color:#fff;color: ' . $color4 . ';}
    div#userpopup .userpopup-search form .userpopup-btn-wrp .userpopup-search-btn{background-color: ' . $color1 . ';color:' . $color7 . ';}
	div#userpopup .userpopup-search form .userpopup-btn-wrp .userpopup-search-btn:hover{background-color:' . $color2 . ';}
	div#userpopup .userpopup-search form .userpopup-btn-wrp .userpopup-reset-btn{background-color: #f5f2f5;color: '. $color4 .';border: 1px solid '. $color5 .';}
	div#userpopup .userpopup-search form .userpopup-btn-wrp .userpopup-reset-btn:hover{background-color: '. $color2 .';color:'. $color7 .';}
	.js-ticket-table-wrp .js-ticket-table-header{background-color: '. $color3 .';border-bottom:1px solid ' . $color5 . '; color: ' . $color2 . ';}
	.js-ticket-table-wrp .js-ticket-data-row{border-bottom: 1px solid ' . $color5 . ';}
    .jsst_userlink { color: ' . $color1 . '; background-color: #e9ecef; }
    .jsst_userlink.selected { background-color: ' . $color1 . '; color: ' . $color7 . '; }
	/* --- End of Unchanged Colors --- */
';

wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);
?>
