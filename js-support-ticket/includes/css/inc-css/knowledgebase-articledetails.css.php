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
    /* Article Detail Modern Layout */

        div.js-ticket-downloads-wrp {
            width: 100%;
        }

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
    div.js-ticket-knowledgebase-wrapper p{
        color:' .$color4 .';
    }

    /* Heading section for the title */
    div.js-ticket-search-heading-wrp {
        width: 100%;
        padding-bottom: 1rem;
        border-bottom: 1px solid; /* A subtle separator */
    }

    /* The main title of the article */
    div.js-ticket-search-heading-wrp div.js-ticket-heading-left {
        width: 100%;
        font-weight: 700;
        font-size: 30px;
        line-height: 1.3;
        text-transform: capitalize;
    }

    /* The main content/details of the article */
    div.js-ticket-knowledge-details {
        width: 100%;
        padding: 0;
        line-height: 1.8;
        margin-top: 15px;
    }
    
    div.js-ticket-knowledge-details p {
        margin: 0;
    }

    /* Wrapper for the "Related" and "Attachments" sections */
    div.js-ticket-categories-wrp,
    div.js-ticket-downloads-wrp {
        width: 100%;
        margin-top: 0;
    }
    div.js-ticket-downloads-wrp {width:100%; margin-bottom:30px;}
    div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp {
 	   font-weight: 700;
}

    /* Heading for the sections */
    div.js-ticket-categories-heading-wrp,
    div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp {
        width: 100%;
        padding: 1rem 1.5rem;
        line-height: initial;
        
        border-radius: 8px;
    }

    /* Attachments content area */
    div.js-ticket-downloads-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 1rem;
    }

    /* Individual attachment box */
    div.js-ticket-downloads-content div.js-ticket-download-box {
        width: 100%;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-radius: 8px;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
    }

    div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {
        font-weight: 500;
    }

    div.js-ticket-download-btn a.js-ticket-download-btn-style {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.2s ease, color 0.2s ease;
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

    div.js-ticket-knowledge-details {
        color:' . $color4 . ';
    }

    div.js-ticket-categories-heading-wrp,
    div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp {
        background-color:' . $color3 . ';
        border:1px solid ' . $color5 . ';
        color:' . $color2 . ';
    }

    div.js-ticket-downloads-content div.js-ticket-download-box {
        background-color: #fff;
        border:1px solid ' . $color5 . ';
    }
    
    div.js-ticket-downloads-content div.js-ticket-download-box:hover {
        background-color: #fff;
        border-color: ' . $color1 . ';
    }

    div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {
        color: ' . $color4 . ';
    }

    div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title:hover span.js-ticket-download-name {
        color: ' . $color2 . ';
    }

    div.js-ticket-download-btn a.js-ticket-download-btn-style {
        color: ' . $color7 . ';
        background-color: ' . $color1 . ';
        border:1px solid ' . $color1 . ';
    }

    div.js-ticket-download-btn a.js-ticket-download-btn-style:hover {
        color: ' . $color7 . ';
        background-color: ' . $color2 . ';
        border-color:' . $color2 . ';
    }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
