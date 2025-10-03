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
	/* Global Box Sizing */
	form.js-ticket-form, form.js-ticket-form * {
		box-sizing: border-box;
	}

	form.js-ticket-form{
		display: flex;
		flex-wrap: wrap;
		width: 100%;
		padding: 40px;
		background-color: #ffffff;
		border-radius: 20px;
		box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
		gap: 25px;
		margin-top: 20px;
	}

	div.js-ticket-add-form-wrapper{
		width: 100%;
		display: flex;
		flex-wrap: wrap;
		gap: 25px;
	}
	div.js-ticket-add-form-wrapper .js-ticket-top-search-wrp{
		display: flex;
		flex:1 1 auto;
	}
	/* Form Field Layout */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp{
		flex: 1 1 calc(50% - 12.5px);
		margin: 0;
		min-width: 300px;
		position: relative;
		margin-bottom: 30px;
	}

	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp.js-ticket-from-field-wrp-full-width{
		flex: 1 1 100%;
		margin-bottom: 30px;
		display: flex;
		flex-wrap: wrap;
	}

	/* Form Field Titles */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title{
		width: 100%;
		margin-bottom: 10px;
		font-weight: 600;
	}

	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field{
		width: 100%;
		position: relative;
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		align-items: center;
	}

	/* General Input & Select Styles */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select {
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
		max-width:100%;
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
	}
	
	/* Focus Styles */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input:focus,
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select:focus {
		outline: none;
		border-color: ' . $color1 . ';
		box-shadow: 0 0 0 4px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.25);
	}

	/* Select Arrow Icon */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select {
		background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23' . substr($color2, 1) . '\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E") calc(100% - 15px) / 1.5em no-repeat;
	}

	/* Radio Button Area */
	div.js-ticket-radio-btn-wrp{
		float: left;
		width: 100%;
		padding: 15px 20px;
		border-radius: 10px;
	}
	div.js-ticket-radio-btn-wrp input.js-ticket-form-field-radio-btn{
		margin-right: 8px; 
		vertical-align: middle;
		width: 16px;
		height: 16px;
	}
	div.js-ticket-radio-btn-wrp label#forsendmail{
		margin: 0 30px 0 0;
		display: inline-block;
		vertical-align: middle;
		font-weight: 500;
	}

	/* Buttons Wrapper */
	div.js-ticket-form-btn-wrp{
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

	/* Save & Cancel Buttons */
	div.js-ticket-form-btn-wrp input.js-ticket-save-button,
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
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
	}

	div.js-ticket-form-btn-wrp input.js-ticket-save-button {
		border: 1px solid ' . $color1 . ';
		box-shadow: 0 2px 10px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
	}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button {
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
	}
	
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover{
		border-color: ' . $color2 . ' !important;
		transform: translateY(-3px);
		box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
		filter: brightness(1.1);
	}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
		border-color: ' . $color2 . ';
		transform: translateY(-2px);
		box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
	}
	
	/* Help Text */
	span.jsst-help-block{
		display: block !important;
		font-size: 14px;
		color: #c0392b !important;
		padding: 5px 15px;
		background-color: #fff0f0;
		border: 1px solid #e74c3c;
		border-radius: 8px;
		font-weight: 600;
		box-shadow: 0 3px 10px rgba(231, 76, 60, 0.15);
		clear: both;
		width: 100%;
		box-sizing: border-box;
		position: relative;
		z-index: 2;
		bottom: 15px !important;
	}

	/* Header Section */
	div.js-ticket-top-search-wrp.second-style{
		float: left;
		width: 100%;
		margin-top: 20px;
		border-radius: 10px;
		overflow: hidden; /* To contain the child elements within the border radius */
	}
	div.js-ticket-search-heading-wrp{
		width: 100%; 
		padding: 10px 20px;
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		border-radius: 10px;
		min-height: 55px;
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{
		float: none;
		width: auto;
		padding: 0;
		line-height: initial;
		font-size: 17px;
		font-weight: 600;
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right{
		float: none;
		width: auto;
		text-align: right;
		margin-left: auto;
		flex: 1 1 auto;
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{
		display: inline-flex; /* Use flexbox for alignment */
		align-items: center;
		padding: 14px 25px;
		text-decoration: none;
		outline: 0px;
		line-height: initial;
		border-radius: 10px;
		font-weight: 600;
		transition: all 0.3s ease;
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover {
		transform: translateY(-3px);
		box-shadow: 0 4px 12px rgba(0,0,0,0.15);
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn span.js-ticket-add-img-wrp{
		display: inline-block;
		margin-right: 8px;
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn span.js-ticket-add-img-wrp img{
		vertical-align: middle;
	}

';
/*Code For Modern Colors*/
$jssupportticket_css .= '


	/* Add Form */
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field-title {color:' . $color2 . ';}
	
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field input.js-ticket-form-field-input,
	div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select.js-ticket-form-field-select {
		border: 1px solid ' . $color5 . ';
		color: ' . $color4 . ';
		background-color: #fcfcfc;
	}

	div.js-ticket-form-btn-wrp { border-top: 1px solid ' . $color2 . '; }

	div.js-ticket-form-btn-wrp input.js-ticket-save-button{
		background-color:' . $color1 . ' !important;
		color:' . $color7 . ' !important;
	}
	div.js-ticket-form-btn-wrp input.js-ticket-save-button:hover {
		background-color: ' . $color2 . ' !important;
    	color: ' . $color7 . ' !important;
	}
	
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button{
		background-color: #f5f2f5;
		color: ' . $color4 . ';
		border-color: ' . $color5 . ';
	}
	div.js-ticket-form-btn-wrp a.js-ticket-cancel-button:hover{
		background-color: ' . $color2 . ' !important;
		color: ' . $color7 . ' !important;
		border-color: ' . $color2 . ' !important;
	}

	div.js-ticket-radio-btn-wrp{
		background-color: #fcfcfc;
		border:1px solid ' . $color5 . ';
	}
	
	/* Header Section */
	div.js-ticket-search-heading-wrp{
		background-color: ' . $color2 . ';
		color: ' . $color7 . ';
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{
		background: #fff;
		color: ' . $color2 . ';
	}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{
		background: ' . $color1 . ';
		color: ' . $color7 . ';
	}
	/* Secondary Header Style */
	div.js-ticket-top-search-wrp.second-style { border:1px solid ' . $color5 . '; }
	div.js-ticket-search-heading-wrp.second-style{background-color: ' . $color7 . ';color: ' . $color2 . ';}
	div.js-ticket-search-heading-wrp.second-style div.js-ticket-heading-right a.js-ticket-add-download-btn{
		background: ' . $color2 . ';
		color: ' . $color7 . ';
	}
	div.js-ticket-search-heading-wrp.second-style div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{
		background: ' . $color1 . ';
	}
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
