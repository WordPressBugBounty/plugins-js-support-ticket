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
/* Downloads */

    /* General Wrappers - Modern Layout */
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
        border: 1px solid '. $jsst_color5 .';
        width: 100%;
    }
    div.jsst-main-up-wrapper input, div.jsst-main-up-wrapper button, div.jsst-main-up-wrapper select, div.jsst-main-up-wrapper textarea
    {
        margin-bottom: 0px;
    }
    .js-ticket-categories-wrp {
    width: 100%;
}
.js-ticket-downloads-wrp {
    width:100%;

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
    select.js-ticket-select-field{
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
        border: 1px solid '. $jsst_color5 .';
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
        border: 1px solid '. $jsst_color5 .';
        transition: all 0.2s ease;
        text-align: center;
    }

    div.js-ticket-category-box:hover {
        border-color: '. $jsst_color5 .';
        transform: translateY(-2px);
    }

    a.js-ticket-category-title {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 1rem;
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
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid '. $jsst_color5 .';
        transition: background-color 0.2s ease;
    }

    a.js-ticket-download-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        font-weight: 600;
    }

    a.js-ticket-download-btn-style,
    button.js-ticket-download-btn-style {
        display: inline-block;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        transition: background-color 0.2s ease, color 0.2s ease;
        border: 1px solid transparent;
    }

    /* Popup Modal Styles */
    div#js-ticket-main-black-background{position: fixed;width: 100%;height: 100%;background: rgba(0,0,0,0.7);z-index: 998;top:0px;left:0px;}
    div#js-ticket-main-popup{position: fixed;top:50%;left:50%;width:60%;max-width: 800px; max-height: 90vh;z-index: 99999;overflow-y: auto;transform: translate(-50%,-50%); border-radius: 12px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);}
    span#js-ticket-popup-close-button{position: absolute;top:15px;right: 15px;width:30px;height: 30px; cursor: pointer;}
    span#js-ticket-popup-title{width:100%;display: block;padding: 15px 20px; font-weight: 700; border-bottom: 1px solid ' . $jsst_color5 . ';font-size:21px;}
    div#js-ticket-main-popup #js-ticket-popup-close-button{
    width: 24px;
    height: 24px;
    cursor: pointer;
    transition: transform 0.2s ease;
    background-image: url(\'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"%3e%3cline x1="18" y1="6" x2="6" y2="18"%3e%3c/line%3e%3cline x1="6" y1="6" x2="18" y2="18"%3e%3c/line%3e%3c/svg%3e\') !important;
    background-repeat: no-repeat !important;
    background-position: center center !important;
    background-size: contain !important;}
    div#js-ticket-main-popup #js-ticket-popup-close-button:hover{
     transform: rotate(90deg);
     }
    div#js-ticket-main-content{padding: 20px;}
    div.js-ticket-download-description{line-height: 1.6; margin-bottom: 1.5rem;}
    div#js-ticket-main-downloadallbtn{padding-top: 1rem; border-top: 1px solid ' . $jsst_color5 . ';}
    .js-ticket-download-btn {margin: 20px;}

';
/*Code For Colors*/
$jsst_jssupportticket_css .= '
    .js-ticket-download-box span.js-ticket-download-name{color: ' . $jsst_color2 . ';}
    .js-ticket-download-box span.js-ticket-download-name:hover{color: ' . $jsst_color1 . ';}
    div.js-ticket-top-search-wrp{border:1px solid  ' . $jsst_color5 . ';}
    div.js-ticket-search-fields-wrp {background:#fff;}
    select.js-ticket-select-field{background-color: #fff !important;border:1px solid  ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
    div.js-ticket-search-form-btn-wrp input.js-search-button{background: ' . $jsst_color1 . ' !important;color: ' . $jsst_color7 . ' !important;}
    div.js-ticket-search-form-btn-wrp input.js-search-button:hover {background: ' . $jsst_color2 . ' !important;color: ' . $jsst_color7 . ' !important;}
    div.js-ticket-search-form-btn-wrp input.js-reset-button{background-color: #f5f2f5; color: #636363; border: 1px solid '. $jsst_color5 .'; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);}
    div.js-ticket-search-form-btn-wrp input.js-reset-button:hover {background: ' . $jsst_color2 . ' !important;color: ' . $jsst_color7 . ' !important;}
div.jsst-main-up-wrapper a{color: ' . $jsst_color7 . ';}
    /* Headings */
    div.js-ticket-search-heading-wrp,
    div.js-ticket-categories-heading-wrp,
    div.js-ticket-downloads-heading-wrp {
        background-color: ' . $jsst_color2 . ';
        color: ' . $jsst_color7 . ';
    }
    div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{background: ' . $jsst_color2 . ';color: ' . $jsst_color7 . ';border:1px solid  ' . $jsst_color5 . ';}
    div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{border-color: ' . $jsst_color1 . ';}

    div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color: #fff;border:1px solid  ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}

    /* Categories */
    div.js-ticket-category-box { border-color: ' . $jsst_color5 . '; background-color:#fff; }
    div.js-ticket-category-box:hover { border-color: ' . $jsst_color1 . '; }
    a.js-ticket-category-title span.js-ticket-category-name { color: ' . $jsst_color4 . '; }
    a.js-ticket-category-title:hover span.js-ticket-category-name { color: ' . $jsst_color1 . '; }

    /* Downloads */
    div.js-ticket-download-box { border-color: ' . $jsst_color5 . '; background-color: #fff; }
    div.jsst-main-up-wrapper a.js-ticket-download-title { color: ' . $jsst_color1 . '; }
    div.jsst-main-up-wrapper a.js-ticket-download-title:hover { color: ' . $jsst_color2 . ';cursor: pointer;}
    div.jsst-main-up-wrapper a.js-ticket-download-btn-style, button.js-ticket-download-btn-style {cursor:pointer; background-color: ' . $jsst_color1 . '; color: ' . $jsst_color7 . '; border-color: ' . $jsst_color1 . '; }
    div.jsst-main-up-wrapper a.js-ticket-download-btn-style:hover, button.js-ticket-download-btn-style:hover { background-color: ' . $jsst_color2 . '; border-color: ' . $jsst_color2 . ';color: ' . $jsst_color7 . ';}

    /* Popup Modal Colors */
    div#js-ticket-main-popup { background: #fff; }
    span#js-ticket-popup-title { background-color: ' . $jsst_color3 . '; color: ' . $jsst_color2 . '; border-bottom-color: ' . $jsst_color5 . '; }
    div.js-ticket-download-description { color: ' . $jsst_color4 . '; }
    div#js-ticket-main-downloadallbtn { border-top-color: ' . $jsst_color5 . '; }
';

wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);

?>
