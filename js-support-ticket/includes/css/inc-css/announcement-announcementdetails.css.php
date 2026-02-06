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


    /* Announcement Detail Modern Layout */

    /* Main container with card styling */
    div.js-ticket-knowledgebase-wrapper {
        width: 100%;
        padding: 2rem;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        gap: 1.5rem; /* Space between sections */
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
        margin-bottom:30px;
    }

    /* Heading section for the title */
    div.js-ticket-search-heading-wrp {
        width: 100%;
        padding-bottom: 1rem;
        border-bottom: 1px solid; /* A subtle separator */
    }

    /* The main title of the announcement */
    div.js-ticket-search-heading-wrp div.js-ticket-heading-left {
        width: 100%;
        font-weight: 700;
        font-size: 30px;
        line-height: 1.3;
    }

    /* The main content/details of the announcement */
    div.js-ticket-knowledgebase-details {
        width: 100%;
        padding: 0;
        line-height: 1.8;
            margin-top: 15px;
    }

    /* Wrapper for the "Related" section */
    div.js-ticket-categories-wrp {
        width: 100%;
       
    }

    /* Heading for the "Related" section */
    div.js-ticket-categories-heading-wrp {
        width: 100%;
        padding: 1rem 1.5rem;
        line-height: initial;
    
        font-weight: 600;
        border-radius: 8px;
    }
    div.js-ticket-download-description {margin-top: 15px;}
';

/*Code For Colors*/
$jsst_jssupportticket_css .= '
    /* Colors for Modern Layout */

    div.js-ticket-knowledgebase-wrapper {
        background-color: #fff;
        border: 1px solid ' . $jsst_color5 . ';
    }

    div.js-ticket-search-heading-wrp {
        color:' . $jsst_color2 . ';
        border-color: ' . $jsst_color5 . '; /* Separator color */
    }

    div.js-ticket-knowledgebase-details {
        color:' . $jsst_color4 . ';
    }

    div.js-ticket-categories-heading-wrp {
        background-color: ' . $jsst_color3 . ';
        border: 1px solid ' . $jsst_color5 . ';
        color: ' . $jsst_color2 . ';
    }
';


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
