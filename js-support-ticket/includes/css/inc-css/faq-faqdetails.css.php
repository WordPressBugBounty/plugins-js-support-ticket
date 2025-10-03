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

    /* Main container with card styling */
    div.js-ticket-knowledgebase-wrapper {
        width: 100%;
        margin-bottom: 30px;
        padding: 2rem;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        gap: 1.5rem; /* Space between sections */
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
    }

    /* Heading section for the title */
    div.js-ticket-search-heading-wrp {
        width: 100%;
        padding-bottom: 1rem;
        border-bottom: 1px solid; /* A subtle separator */
    }

    /* The main title of the FAQ */
    div.js-ticket-search-heading-wrp div.js-ticket-heading-left {
        width: 100%;
        font-weight: 700;
        font-size: 30px;
        line-height: 1.3;
        text-transform: capitalize;
    }

    /* The main content/details of the FAQ */
    div.js-ticket-knowledgebase-details {
        width: 100%;
        padding: 0;
        line-height: 1.8;
        margin-top: 15px;
    }
    
    div.js-ticket-knowledgebase-details p {
        margin: 0;
    }

    /* Wrapper for the "Related" section */
    div.js-ticket-categories-wrp {
        width: 100%;
        margin-top: 0;
    }

    /* Heading for the "Related" section */
    div.js-ticket-categories-heading-wrp {
        width: 100%;
        padding: 1rem 1.5rem;
        line-height: initial;
        font-weight: 600;
        border-radius: 8px;
    }
';
/*Code For Colors*/
$jssupportticket_css .= '
    /* Colors for Modern Layout */

    div.js-ticket-knowledgebase-wrapper {
        background-color: #fff;
        border: 1px solid ' . $color5 . ';
    }

    div.js-ticket-search-heading-wrp {
        color:' . $color2 . ';
        border-color: ' . $color5 . '; /* Separator color */
    }

    div.js-ticket-knowledgebase-details {
        color:' . $color4 . ';
    }

    div.js-ticket-categories-heading-wrp {
        background-color: ' . $color3 . ';
        border: 1px solid ' . $color5 . ';
        color: ' . $color2 . ';
    }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
