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

/*Code for Css - Modern Layout Inspired by agent-staffs.css*/
$jsst_jssupportticket_css .= '
/* General Wrappers */
	div.js-ticket-feedback-wrapper,
	div.js-ticket-top-search-wrp {
		width: 100%;
		float: none;
		box-sizing: border-box;
		margin-bottom:10px!important;
	}
	.js-ticket-top-search-wrp {
    display: flex;

}
div.js-ticket-feedback-wrapper, div.js-ticket-top-search-wrp{
	width: 100%;
	}
div.js-ticket-feedback-heading{width: 100%;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    float: none;
    font-weight: 700;
    border-radius: 8px;}

/* Search Area Card Styling */
	div.js-ticket-top-search-wrp {
		padding: 1.5rem;
		border: 1px solid '. $jsst_color5 .';
		border-radius: 12px;
		box-shadow: 0 4px 6px rgba(0,0,0,0.04);
		margin-bottom: 2rem;
		background: #fff;
	}

	div.js-ticket-search-heading-wrp {
		display: flex;
		justify-content: space-between;
		align-items: center;
		width: 100%;
		padding: 15px;
		font-size: 17px;
		margin-bottom: 20px;
		line-height: initial;
		border-radius: 8px;
	}

	div.js-ticket-search-heading-wrp div.js-ticket-heading-left,
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right {
		float: none;
		width: auto;
	}

	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn {
		display: inline-flex;
		align-items: center;
		gap: 0.5rem;
		padding: 0.6rem 1.2rem;
		text-decoration: none;
		border-radius: 8px;
		transition: background-color 0.2s ease;
	}

/* Search Form Flexbox Layout */
	form#jssupportticketform {
		width: 100%;
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
		align-items: flex-end; /* Align buttons with form fields */
	}
	div.js-ticket-search-fields-wrp {
		width: 100%;
		
	}
	div.js-ticket-fields-wrp {
		display: flex; /* Allow parent form to control flex layout */
		align-items:stretch;
		flex-wrap:wrap;
		gap:10px;
	}

/* Individual Form Fields */
	div.js-ticket-fields-wrp div.js-ticket-form-field {
		float: none;
		position: relative;
		flex: 1 1 200px; /* Responsive fields */
		margin: 0;
		width: auto;
	}

/* Input and Select Styling */
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input,
	select.js-ticket-select-field {
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

	select.js-ticket-select-field {
		-webkit-appearance: none;
		appearance: none;
		background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/selecticon.png);
		background-repeat: no-repeat;
		background-position: right 1rem center;
		background-size: 0.75rem;
		padding-right: 2.5rem; /* Space for arrow */
	}

/* Button Wrapper */
	div.js-ticket-search-form-btn-wrp {
		float: none;
		width: auto;
		padding: 0;
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
		height: auto;
	}

/* FeedBack List */
	div.js-ticket-feedback-list-wrapper{padding: 1.5rem;
    border: 1px solid '. $jsst_color5 .';
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
    margin-bottom: 2rem;
    margin-top: 17px;
    background: #fff;}

	div.jsst-feedback-det-wrp {
		width: 100%;
		border-radius: 12px;
		box-shadow: 0 4px 8px rgba(0,0,0,0.06);
		margin-bottom: 1.5rem;
		    border-radius: 12px;
		background: #fff;
	}

	div.jsst-feedback-det-list {
		width: 100%;
	}

	div.jsst-feedback-det-list-top {
		display: flex;
		width: 100%;
		padding: 15px;
		gap: 15px;
		align-items: flex-start;
	}

	div.jsst-feedback-det-list-img-wrp {
		width: 80px;
		height: 80px;
		border-radius: 50%;
		flex-shrink: 0;
		text-align: center;
	}

	div.jsst-feedback-det-list-img-wrp img {
		max-width: 100%;
		height: auto;
		border-radius: 50%;
	}

	div.jsst-feedback-det-list-data-wrp {
		flex-grow: 1;
		padding: 0;
	}

	div.jsst-feedback-det-list-data-row,
	div.jsst-feedback-det-list-cust-flds {
		display: flex;
		flex-wrap: wrap;
		gap: 5px;
		width: 100%;
		padding-bottom: 10px;
		line-height: 1.5;
	}
	div.jsst-feedback-det-list-data-title,
	.jsst-feedback-det-list-cust-flds-title{font-weight: 600;}

	div.jsst-feedback-det-list-btm {
		display: flex;
		gap: 5px;
		width:100%;
		padding: 15px;
		background: #fafafa;
		border-top: 1px solid ' . $jsst_color5 . ';
		line-height: 1.5;
	}
	div.jsst-feedback-det-list-btm-title{font-weight: 600;}

/* Cleanup Floats */
	div.jsst-feedback-det-wrp div.jsst-feedback-det-list{    border-radius: 12px;}
	div.jsst-feedback-det-list-btm{border-radius: 0px 0 12px 12px;}
	div.js-ticket-feedback-heading,
	div.jsst-feedback-det-list, div.jsst-feedback-det-list-top,
	div.jsst-feedback-det-list-data-wrp, div.jsst-feedback-det-list-data-row,
	div.jsst-feedback-det-list-data-title, div.jsst-feedback-det-list-data-val,
	div.jsst-feedback-det-list-cust-flds, .jsst-feedback-det-list-cust-flds-title,
	.jsst-feedback-det-list-cust-flds-val, div.jsst-feedback-det-list-btm-title,
	div.jsst-feedback-det-list-btm-val, div.jsst-feedback-det-list-img-wrp {
		float: none;
	}

/* Helpers */
	select::-ms-expand { display:none !important; }
';
/*Code For Colors*/
$jsst_jssupportticket_css .= '
	div.js-ticket-search-fields-wrp {background: transparent;} /* Removed background, handled by parent */
	div.js-ticket-top-search-wrp{border:1px solid ' . $jsst_color5 . ';}
	div.js-ticket-search-heading-wrp{background-color:' . $jsst_color4 . ';color:' . $jsst_color7 . ';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{background:' . $jsst_color2 . ';color:' . $jsst_color7 . ';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{background:rgba(125, 135, 141, 0.4);color:' . $jsst_color7 . ';}

	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val a.jsst-feedback-det-list-data-anch {color: ' . $jsst_color4 . ';}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val.name {color: ' . $jsst_color1 . ';}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-title {color: ' . $jsst_color2 . ';}
	div.jsst-feedback-det-list-data-row div.jsst-feedback-det-list-data-val {color: ' . $jsst_color4 . ';}

	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color:#fff;border:1px solid ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
	select.js-ticket-select-field{background-color:#fff !important;border:1px solid ' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}

	div.js-ticket-search-form-btn-wrp input.js-search-button{background: ' . $jsst_color1 . ' !important;color:' . $jsst_color7 . ' !important;border: 1px solid ' . $jsst_color1 . ';}
	div.js-ticket-search-form-btn-wrp input.js-search-button:hover{background: ' . $jsst_color2 . ' !important; border-color: ' . $jsst_color2 . ';}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{    background-color: #f5f2f5;
    color: #636363;
    border: 1px solid '. $jsst_color5 .';
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);}
	div.js-ticket-search-form-btn-wrp input.js-reset-button:hover{background: ' . $jsst_color2 . ' !important;color: ' . $jsst_color7 . ';}

	div.jsst-feedback-det-list-cust-flds .jsst-feedback-det-list-cust-flds-title {color: ' . $jsst_color2 . ';}
	div.jsst-feedback-det-list-cust-flds .jsst-feedback-det-list-cust-flds-val {color: ' . $jsst_color4 . ';} /* Corrected class name from value to val */
	div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-btm div.jsst-feedback-det-list-btm-title {color: ' . $jsst_color4 . ';}
	div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-btm div.jsst-feedback-det-list-btm-val {color: ' . $jsst_color2 . ';}
';


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
