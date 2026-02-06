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
/* Help Topics - Matched with Announcement CSS */

    /* General Wrappers - Modern Layout */
    div.js-ticket-helptopic-wrapper, /* Assuming a unique wrapper */
    div.js-ticket-announcement-wrapper,
    div.js-ticket-top-search-wrp,
    div.js-ticket-download-content-wrp,
    div.js-ticket-table-wrp {
        width: 100%;
        float: none; /* Remove float */
        box-sizing: border-box;
    }
    div.js-ticket-top-search-wrp{display:flex;}

    div.js-ticket-top-search-wrp,div.js-ticket-download-content-wrp {
    border: 1px solid '. $jsst_color5 .';
    width: 100%;
}
div.jsst-main-up-wrapper input, div.jsst-main-up-wrapper button, div.jsst-main-up-wrapper select, div.jsst-main-up-wrapper textarea
{
    line-height: 1.3;
    margin-bottom: 0px;
}

    /* Card Styling for Search and Content Areas */
    div.js-ticket-top-search-wrp,
    div.js-ticket-download-content-wrp {
        padding: 1.5rem;
        border: 1px solid '. $jsst_color5 .';
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.04);
        margin-bottom: 2rem;
        margin-top: 17px;
        background: #fff; /* Added for card effect */
    }

    /* Main Form Container - Flexbox Layout */
    form#jssupportticketform {
        width: 100%;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    div.js-ticket-search-fields-wrp {
        width: 100%;
    }

    div.js-ticket-fields-wrp {
        display: contents; /* Allow parent form to control flex layout */
    }

    /* Individual Form Fields */
    div.js-ticket-fields-wrp div.js-ticket-form-field{
        float: none; /* Remove float */
        position: relative;
        flex: 1 1 200px; /* Responsive fields */
        margin: 0;
        width: auto;
    }

    /* Input and Select Styling */
    div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input,
    select.js-ticket-select-field,
    select.js-ticket-field-input {
        width: 100%;
        padding: 12px 18px;
        border: 1px solid '. $jsst_color5 .';
        border-radius: 8px;
        height: auto; /* Remove fixed height */
        line-height: 1.5;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        box-sizing: border-box;
        height: 100%;
        min-height:52px;
    }

    select.js-ticket-select-field,
    select.js-ticket-field-input {
        -webkit-appearance: none;
        appearance: none;
        background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/selecticon.png);
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 0.75rem;
        padding-right: 2.5rem; /* Space for arrow */
    }

    /* Button Wrapper */
    div.js-ticket-search-form-btn-wrp{
        float: none;
        width: auto;
        padding: 0;
        margin-top: 0; /* Use flex-gap for alignment */
        display: flex;
        gap: 0.5rem;
    }

    /* Buttons */
    div.js-ticket-search-form-btn-wrp input {
        float: none;
        width: auto;
        padding: 5px 20px;
        min-height:52px;
        min-width:120px;
        font-weight:600;
        margin: 0;
        border-radius: 8px;
        height: auto;
        line-height: 1.5;
        border: 1px solid '. $jsst_color5 .';
        cursor: pointer;
        transition: opacity 0.2s ease, background-color 0.2s ease;
    }

    /* Table Heading Wrapper */
    div.js-ticket-table-heading-wrp{
        width: 100%;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        float: none;
        font-weight: 700;
        border-radius: 8px;
    }

    div.js-ticket-table-heading-wrp div.js-ticket-table-heading-left,
    div.js-ticket-table-heading-wrp div.js-ticket-table-heading-right{
        float: none;
        width: auto;
    }

    /* Add Button */
    a.js-ticket-table-add-btn{
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.2s ease;
        font-size: initial;
    }
    a.js-ticket-table-add-btn span.js-ticket-table-add-img-wrp {
        margin: 0;
    }
    a.js-ticket-table-add-btn span.js-ticket-table-add-img-wrp img{
        vertical-align: middle;
    }

    /* Table Header & Rows - Flexbox Layout */
    div.js-ticket-table-wrp div.js-ticket-table-header,
    div.js-ticket-table-body div.js-ticket-data-row{
        width: 100%;
        display: flex;
        align-items: center;
        padding: 10px 10px;
        box-sizing: border-box;
        float: none;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    div.js-ticket-table-body {
        width: 100%;
        float: none;
    }

    div.js-ticket-table-body div.js-ticket-data-row {
        border-bottom: 1px solid ' . $jsst_color5 . ';
        transition: background-color 0.2s ease;
    }

    /* Table Columns */
    div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col,
    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{
        padding: 1rem 0.5rem;
        text-align: left;
        line-height: 1.5;
        flex-grow: 1;
        flex-shrink: 1;
        flex-basis: 0; /* Let flex-grow handle the sizing */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col:first-child,
    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:first-child{
        padding-left: 10px;
    }

    /* Action column styling */
    div.js-ticket-table-wrp div.js-ticket-table-header div.js-ticket-table-header-col:last-child,
    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:last-child {
        text-align: right;
        flex-grow: 0;
        flex-shrink: 0;
        flex-basis: 150px; /* Give action buttons a consistent width */
        white-space: normal;
    }

    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col .js-ticket-title-anchor {
        text-decoration: none;
        font-weight: 600;
        width: 100%;
    }

    a.js-ticket-table-action-btn {
        padding: 0.25rem;
        margin: 0 0.125rem;
        display: inline-flex;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }
    a.js-ticket-table-action-btn img {
        display:inline-block;
        vertical-align: middle;
    }
    span.js-ticket-display-block{display: none;}
    select ::-ms-expand {display:none !important;}
    select{-webkit-appearance:none !important;}
';

/*Code For Colors*/
$jsst_jssupportticket_css .= '
    div.jsst-main-up-wrapper a{color: ' . $jsst_color7 . ';}
    div.js-ticket-top-search-wrp{border:1px solid  ' . $jsst_color5 . ';}
    div.js-ticket-search-fields-wrp {background: #fff;}
    select.js-ticket-select-field, select.js-ticket-field-input {background-color: #fff !important;border:1px solid  ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
    div.js-ticket-search-form-btn-wrp input.js-search-button{background: ' . $jsst_color1 . ' !important;color: ' . $jsst_color7 . ' !important;}
    div.js-ticket-search-form-btn-wrp input.js-search-button:hover {background: ' . $jsst_color2 . ' !important;color: ' . $jsst_color7 . ' !important;}
    div.js-ticket-search-form-btn-wrp input.js-reset-button{background-color: #f5f2f5; color: #636363; border: 1px solid '. $jsst_color5 .'; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);}
    div.js-ticket-search-form-btn-wrp input.js-reset-button:hover {background: ' . $jsst_color2 . ' !important;color: ' . $jsst_color7 . ' !important;}
    div.js-ticket-table-heading-wrp{background-color: ' . $jsst_color2 . ';color: ' . $jsst_color7 . ';}
    div.jsst-main-up-wrapper a.js-ticket-table-add-btn{background: ' . $jsst_color2 . ';color: ' . $jsst_color7 . ';border:1px solid  ' . $jsst_color7 . ';}
    div.jsst-main-up-wrapper a.js-ticket-table-add-btn:hover{border-color: ' . $jsst_color1 . ';text-decoration: none;color: ' . $jsst_color7 . '}
    div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color: #fff;border:1px solid  ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
    div.js-ticket-table-header{background-color:' . $jsst_color3 . '!important;border:1px solid  ' . $jsst_color5 . ';}
    div.js-ticket-table-header div.js-ticket-table-header-col{color: ' . $jsst_color2 . ';}
    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col .js-ticket-title-anchor{color: ' . $jsst_color1 . ';}
    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col .js-ticket-title-anchor:hover{color: ' . $jsst_color2 . ';}
    div.js-ticket-table-body div.js-ticket-data-row{border:1px solid  ' . $jsst_color5 . ';}
    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{color: ' . $jsst_color4 . ';}
    a.js-ticket-table-action-btn {border: 1px solid ' . $jsst_color5 . ';background: #fff;}
    a.js-ticket-table-action-btn:hover {border-color: ' . $jsst_color1 . ';}
    div.js-ticket-announcement-wrapper div.js-ticket-table-body div.js-ticket-data-row {border:1px solid' . $jsst_color5 . ';}
';


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
