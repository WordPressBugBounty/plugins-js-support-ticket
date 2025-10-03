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

    /* Main Wrappers */
    div.js-ticket-mail-wrapper,
    div.js-ticket-post-reply-wrapper {
        width: 100%;
        margin-top: 17px;
        box-sizing: border-box;
    }

    /* Top Button Bar */
    div.js-ticket-mails-btn-wrp {
        width:calc(100% - 20px);
        margin-left: 10px;
        margin-right: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 2rem;
    }

    div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn {
        flex: 1 1 0;
    }

    div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.75rem 1.5rem;
        text-decoration: none;
        outline: 0;
        border-radius: 8px;
        border: 1px solid '. $color5 .';
        transition: background-color 0.2s ease, border-color 0.2s ease;
        font-weight: 600;
    }

    img.js-ticket-mail-img {
        vertical-align: middle;
    }

    /* Thread Heading - Styled like table heading */
    div.js-ticket-post-reply-wrapper div.js-ticket-thread-heading {
        padding: 15px 20px;
        margin-bottom: 1rem;
        font-weight: 700;
        border-radius: 8px;
        font-size: 17px;
    }

    /* Main Message Box - Styled as a card */
    div.js-ticket-detail-box {
        display: flex;
        width: 100%;
        border: 1px solid '. $color5 .';
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.04);
        background: #fff;
        margin-bottom: 2rem;
        overflow: hidden; /* For border-radius */
    }

    /* Message Left Panel (User Info) */
    div.js-ticket-detail-box div.js-ticket-detail-left {
        flex: 0 0 200px; /* Fixed width for the user info panel */
        padding: 1.5rem;
        text-align: center;
        border-right: 1px solid ' . $color5 . ';
    }

    div.js-ticket-detail-left div.js-ticket-user-img-wrp {
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem;
        position: relative;
    }

    div.js-ticket-detail-left div.js-ticket-user-img-wrp img.js-ticket-staff-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    div.js-ticket-detail-left div.js-ticket-user-name-wrp,
    div.js-ticket-detail-left div.js-ticket-user-email-wrp {
        width: 100%;
        margin: 0.25rem 0;
        word-wrap: break-word;
    }

    /* Message Right Panel (Content) */
    div.js-ticket-detail-box div.js-ticket-detail-right {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }

    div.js-ticket-detail-box div.js-ticket-detail-right div.js-ticket-rows-wrp {
        padding: 1.5rem;
        flex-grow: 1;
    }

    div.js-ticket-detail-right div.js-ticket-row {
        margin-bottom: 0.75rem;
    }

    div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-title {
        font-weight: 700;
        margin-right: 0.5rem;
    }

	div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value p {
		margin: 0;
		display: inline;
	}

    /* Bottom Button Wrapper */
    div.js-ticket-form-btn-wrp {
        width: 100%;
        text-align: center;
        padding: 1.5rem 0;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    /* Save and Cancel Buttons */
    div.js-ticket-form-btn-wrp input.js-ticket-save-button,
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        margin: 0;
        border-radius: 8px;
        height: auto;
        line-height: 1.5;
        border: 1px solid '. $color5 .';
        cursor: pointer;
        transition: opacity 0.2s ease, background-color 0.2s ease;
        text-decoration: none;
        min-width: 120px;
        font-weight: 600;
    }

    /* Select Field Styling */
    select#to {
        width: 100%;
        padding: 12px 18px;
        min-height:52px;
        border: 1px solid '. $color5 .';
        border-radius: 8px;
        line-height: 1.5;
        box-sizing: border-box;
        height: 100%;
        -webkit-appearance: none;
        appearance: none;
        background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/selecticon.png);
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 0.75rem;
        padding-right: 2.5rem; /* Space for arrow */
    }

';
/*Code For Colors*/
$jssupportticket_css .= '
    div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link {
        background-color: #f5f2f5;
        border: 1px solid ' . $color5 . ';
        color: ' . $color2 . ';
    }
    div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link:hover,
    div.js-ticket-mails-btn-wrp div.js-ticket-mail-btn a.js-add-link.active {
        background-color: ' . $color1 . ';
        border-color: ' . $color1 . ';
        color: ' . $color7 . ';
    }
    div.js-ticket-post-reply-wrapper div.js-ticket-thread-heading {
        background-color: ' . $color2 . ';
        color: ' . $color7 . ';
    }
    div.js-ticket-detail-box {
        border-color: ' . $color5 . ';
    }
    div.js-ticket-detail-box div.js-ticket-detail-left {
        border-color: ' . $color5 . ';
    }
    div.js-ticket-detail-left div.js-ticket-user-name-wrp,
    div.js-ticket-detail-left div.js-ticket-user-email-wrp,
    div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-value {
        color: ' . $color4 . ';
    }
    div.js-ticket-detail-right div.js-ticket-row div.js-ticket-field-title {
        color: ' . $color1 . ';
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button {
        background: ' . $color1 . ' !important;
        color: ' . $color7 . ' !important;
        border-color: ' . $color1 . ';
    }
    div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
        background: ' . $color2 . ' !important;
        border-color: ' . $color2 . ';
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
        background-color: #f5f2f5;
        color: #636363;
        border: 1px solid '. $color5 .';
    }
    div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover {
        background: ' . $color2 . ' !important;
        border-color: ' . $color2 . ';
        color:' . $color7 . ';
    }
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
