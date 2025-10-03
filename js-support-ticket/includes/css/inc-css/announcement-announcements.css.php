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
    /* General Wrappers - Modern Layout */
    div.js-ticket-announcement-wrapper,
    div.js-ticket-download-wrapper,
    div.js-ticket-top-search-wrp,
    div.js-ticket-download-content-wrp,
    div.js-ticket-table-wrp {
        width: 100%;
        float: none; /* Remove float */
        box-sizing: border-box;
    }
    div.js-ticket-top-search-wrp{display:flex;}

    div.js-ticket-top-search-wrp,div.js-ticket-download-content-wrp {
        border: 1px solid ' . $color5 . ';
        width: 100%;
    }
    .js-ticket-categories-wrp {
        width: 100%;
}
    .js-ticket-downloads-wrp {
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
        border: 1px solid ' . $color5 . ';
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
    select.js-ticket-select-field{
        width: 100%;
        padding:12px 18px;
        border: 1px solid ' . $color5 . ';
        border-radius: 8px;
        height: auto; /* Remove fixed height */
        line-height: 1.5;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        box-sizing: border-box;
        height: 100%;
        min-height:52px;
    }

    select.js-ticket-select-field{
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
        border: 1px solid ' . $color5 . ';
        cursor: pointer;
        transition: opacity 0.2s ease, background-color 0.2s ease;
    }

    /* Heading Wrapper (for all sections) */
    div.js-ticket-search-heading-wrp,
    div.js-ticket-categories-heading-wrp,
    div.js-ticket-downloads-heading-wrp {
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

    div.js-ticket-search-heading-wrp div.js-ticket-heading-left,
    div.js-ticket-search-heading-wrp div.js-ticket-heading-right{
        float: none;
        width: auto;
    }

    /* Add Button */
    div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.2s ease;
        font-size: initial;
    }

    /* Categories Section */
    div.js-ticket-categories-content {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    div.js-ticket-category-box {
        border-radius: 8px;
        border: 1px solid ' . $color5 . ';
        transition: all 0.2s ease;
        text-align: center;
    }

    div.js-ticket-category-box:hover {
        border-color: ' . $color5 . ';
        transform: translateY(-2px);
    }

    a.js-ticket-category-title {
        display: block;
        padding: 1.5rem;
        text-decoration: none;
    }

    img.js-ticket-download-img {
        max-height: 80px;
        margin-bottom: 1rem;
    }

    span.js-ticket-category-name {
        font-weight: 600;
        display: block;
    }

    /* Downloads Section */
    div.js-ticket-downloads-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    div.js-ticket-download-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid ' . $color5 . ';
        transition: background-color 0.2s ease;
    }

    a.js-ticket-download-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        font-weight: 600;
    }

    a.js-ticket-download-btn-style {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
';

/*Code For Colors*/
$jssupportticket_css .= '
    div.js-ticket-top-search-wrp{border:1px solid  ' . $color5 . ';}
    div.js-ticket-search-fields-wrp {background: #fff;}
    select.js-ticket-select-field{background-color: #fff !important;border:1px solid  ' . $color5 . ';color: ' . $color4 . ';}
    div.js-ticket-search-form-btn-wrp input.js-search-button{background: ' . $color1 . ' !important;color: ' . $color7 . ' !important;}
    div.js-ticket-search-form-btn-wrp input.js-search-button:hover {background: ' . $color2 . ' !important;color: ' . $color7 . ' !important;}
    div.js-ticket-search-form-btn-wrp input.js-reset-button{background-color: #f5f2f5; color: #636363; border: 1px solid ' . $color5 . '; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);}
    div.js-ticket-search-form-btn-wrp input.js-reset-button:hover {background: ' . $color2 . ' !important;color: ' . $color7 . ' !important;}
    span.js-ticket-download-name {color: ' . $color2 . '}
     span.js-ticket-download-name:hover {color: ' . $color1 . '}
    /* Headings */
    div.js-ticket-search-heading-wrp,
    div.js-ticket-categories-heading-wrp,
    div.js-ticket-downloads-heading-wrp {
        background-color: ' . $color2 . ';
        color: ' . $color7 . ';
    }
    div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{background: ' . $color2 . ';color: ' . $color7 . ';border:1px solid  ' . $color5 . ';}
    div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{border-color: ' . $color1 . ';}

    div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color: #fff;border:1px solid  ' . $color5 . ';color: ' . $color4 . ';}

    /* Categories */
    div.js-ticket-category-box { border-color: ' . $color5 . '; background-color:#fff; }
    div.js-ticket-category-box:hover { border-color: ' . $color1 . '; }
    a.js-ticket-category-title span.js-ticket-category-name { color: ' . $color4 . '; }
    a.js-ticket-category-title:hover span.js-ticket-category-name { color: ' . $color1 . '; }

    /* Downloads */
    div.js-ticket-download-box { border-color: ' . $color5 . '; background-color: #fff; }
    a.js-ticket-download-title { color: ' . $color1 . '; }
    a.js-ticket-download-title:hover { color: ' . $color2 . '; }
    a.js-ticket-download-btn-style { background-color: ' . $color1 . '; color: ' . $color7 . '; }
    a.js-ticket-download-btn-style:hover { background-color: ' . $color2 . '; }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
