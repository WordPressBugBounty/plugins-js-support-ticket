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
	/* General Wrappers & Card Styling */
	div.js-ticket-categories-wrp,
	div.js-ticket-downloads-wrp,
	div.js-ticket-knowledgebase-wrapper {
		width: 100%;
		float: none;
		box-sizing: border-box;
		padding: 1.5rem;
		border: 1px solid '. $color5 .';
		border-radius: 12px;
		box-shadow: 0 4px 6px rgba(0,0,0,0.04);
		margin-bottom: 2rem;
		margin-top: 17px;
		background: #fff;
	}
	div.js-ticket-categories-wrp{
		display:flex;
		flex-wrap:wrap;
	}
	div.js-ticket-margin-bottom{margin-bottom: 20px;margin-top: 10px;}

	/* Heading Wrappers */
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp {
		width: 100%;
		padding: 15px 20px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 1rem;
		float: none;
		font-weight: 700;
		border-radius: 8px;
		line-height: initial;
	}

	div.js-ticket-categories-wrp div.js-ticket-position-relative{
		position: relative;
		display: flex;
		align-items: center;
		padding:20px;
		border-radius: 8px;
	
	}
	div.js-ticket-head-category-image{display: inline-block;width: 60px;}
	img.js-ticket-kb-dtl-img{max-width: 100%;}
	span.js-ticket-head-text{display: inline-block;margin-left: 8px;}
	div.js-ticket-knowledgebase-details{float: left;width: 100%;padding: 15px;}

	/* Categories Section - Grid Layout */
	div.js-ticket-categories-content {
		width: 100%;
		float: none;
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
		gap: 1.5rem;
	}
	div.js-ticket-category-box {
		border-radius: 8px;
		border: 1px solid '. $color5 .';
		transition: all 0.2s ease;
		text-align: center;
		float: none;
		width: auto;
		margin: 0;
	}
	div.js-ticket-category-box:hover {
		border-color: '. $color5 .';
		transform: translateY(-2px);
	}
	a.js-ticket-category-title {
		display: block;
		padding: 1.5rem;
		text-decoration: none;
		outline: 0;
	}
	a.js-ticket-category-title span.js-ticket-category-name {
		font-weight: 600;
		display: block;
		text-align: center;
		padding: 0;
		line-height: initial;
	}
	span.js-ticket-category-kb-logo {
		display: block;
		width: auto;
		height: 80px;
		position: relative;
		margin: 0 auto 1rem auto;
	}
	img.js-ticket-kb-img {
		position: absolute;
		top: 0; left: 0; right: 0; bottom: 0;
		margin: auto;
		max-width: 100%;
		max-height: 100%;
		width: auto;
	}

	/* Downloads Section - Flexbox Layout */
	div.js-ticket-downloads-content {
		width: 100%;
		float: none;
		display: flex;
		flex-direction: column;
		gap: 1rem;
		padding: 0;
	}
	div.js-ticket-download-box {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		padding: 10px;
		border-radius: 8px;
		border: 1px solid '. $color5 .';
		transition: background-color 0.2s ease;
		float: none;
		width: auto;
		margin: 0;
		box-shadow: none;
	}
	a.js-ticket-download-title {
		display: flex;
		align-items: center;
		gap: 1rem;
		text-decoration: none;
		font-weight: 600;
		padding: 9px;
		cursor: pointer;
		line-height: initial;
	}
	img.js-ticket-download-icon{float: none;}
	span.js-ticket-download-name {
		display: inline-block;
		padding: 0;
		white-space: initial;
		text-overflow: initial;
		overflow: initial;
	}

	/* Buttons */
	div.js-ticket-download-btn {
		text-align: center;
		margin: 0;
		padding: 0;
		float: none;
	}
	a.js-ticket-download-btn-style,
	button.js-ticket-download-btn-style {
		display: inline-block;
		padding: 0.6rem 1.2rem;
		border-radius: 8px;
		font-weight: 500;
		text-decoration: none;
		transition: background-color 0.2s ease, color 0.2s ease;
		border: 1px solid transparent;
	}
	img.js-ticket-download-btn-icon{vertical-align: text-top;margin-right: 5px;}
	
	/* Form Inputs */
	select.js-ticket-select-field, select#departmentid {
		-webkit-appearance: none !important;
		appearance: none !important;
		background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/selecticon.png);
		background-repeat: no-repeat;
		background-position: right 1rem center;
		background-size: 0.75rem;
		padding-right: 2.5rem;
		width: 100%;
		padding: 12px 18px;
		min-height:52px;
		border-radius: 8px;
		height: 100%;
		line-height: 1.5;
		transition: border-color 0.2s ease, box-shadow 0.2s ease;
		box-sizing: border-box;
	}
	select::-ms-expand {display:none !important;}

	/* Popup Modal Styles */
	div#js-ticket-main-black-background{position: fixed;width: 100%;height: 100%;background: rgba(0,0,0,0.7);z-index: 998;top:0px;left:0px;}
    div#js-ticket-main-popup{position: fixed;top:50%;left:50%;width:60%;max-width: 800px; max-height: 90vh;z-index: 99999;overflow-y: auto;transform: translate(-50%,-50%); border-radius: 12px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);}
    span#js-ticket-popup-close-button{position: absolute;top:15px;right: 15px;width:30px;height: 30px; cursor: pointer;}
    span#js-ticket-popup-title{width:100%;display: block;padding: 15px 20px; font-weight: 700; border-bottom: 1px solid ' . $color5 . ';}
    div#js-ticket-main-content{padding: 20px;}
    div.js-ticket-download-description{line-height: 1.6; margin-bottom: 1.5rem;}
    div#js-ticket-main-downloadallbtn{padding-top: 1rem; border-top: 1px solid ' . $color5 . ';}
';

/*Code For Colors*/
$jssupportticket_css .= '
	/* Main Wrappers */
	div.js-ticket-categories-wrp,
	div.js-ticket-downloads-wrp,
	div.js-ticket-knowledgebase-wrapper {
		background-color: #fff;
		border-color: ' . $color5 . ';
	}

	/* Headings */
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp {
		background-color: ' . $color2 . ';
		color: ' . $color7 . ';
		border: 1px solid ' . $color5 . ';
	}
	
	/* Categories */
    div.js-ticket-category-box { 
		border-color: ' . $color5 . '; 
		background-color: ' . $color7 . '; 
	}
    div.js-ticket-category-box:hover { border-color: ' . $color1 . '; }
    a.js-ticket-category-title span.js-ticket-category-name { color: ' . $color4 . '; }
    a.js-ticket-category-title:hover span.js-ticket-category-name { color: ' . $color1 . '; }

	/* Downloads / Articles */
	div.js-ticket-download-box { 
		border-color: ' . $color5 . '; 
		background-color: #fff;
	}
	a.js-ticket-download-title, 
	a.js-ticket-download-title span.js-ticket-download-name { 
		color: ' . $color1 . '; 
	}
	a.js-ticket-download-title:hover, 
	a.js-ticket-download-title:hover span.js-ticket-download-name { 
		color: ' . $color2 . ';
	}
	
	/* Buttons */
	a.js-ticket-download-btn-style, 
	button.js-ticket-download-btn-style { 
		background-color: ' . $color1 . '; 
		color: ' . $color7 . '; 
		border-color: ' . $color1 . '; 
	}
	a.js-ticket-download-btn-style:hover, 
	button.js-ticket-download-btn-style:hover { 
		background-color: ' . $color2 . '; 
		border-color: ' . $color2 . '; 
	}

	/* Popup Modal Colors */
    div#js-ticket-main-popup { background: ' . $color7 . '; }
    span#js-ticket-popup-title { background-color: ' . $color2 . '; color: ' . $color7 . '; border-bottom-color: ' . $color5 . '; }
    div.js-ticket-download-description { color: ' . $color4 . '; }
    div#js-ticket-main-downloadallbtn { border-top-color: ' . $color5 . '; }

	/* Preserved Original Rules For Other Pages */
	div.js-ticket-top-search-wrp{border:1px solid  ' . $color5 . ';}
	div.js-ticket-categories-heading-wrp{border: 1px solid  ' . $color5 . ';}
	div.js-ticket-search-heading-wrp{background-color: ' . $color4 . ';color: ' . $color7 . ';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn{background: ' . $color2 . ';color: ' . $color7 . ';}
	div.js-ticket-search-heading-wrp div.js-ticket-heading-right a.js-ticket-add-download-btn:hover{background:rgba(125, 135, 141, 0.4);color: ' . $color7 . ';}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input{background-color: ' . $color3 . ';border:1px solid  ' . $color5 . ';}
	select.js-ticket-select-field, select#departmentid{background-color: ' . $color7 . ' !important;border:1px solid  ' . $color5 . ';}
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#departmentid{background-color: ' . $color3 . ';border:1px solid  ' . $color5 . ';}
	div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#staffid{background-color: ' . $color3 . ';border:1px solid  ' . $color5 . ';}
	div.js-ticket-search-form-btn-wrp input.js-search-button{background: ' . $color2 . ' !important;color: ' . $color7 . ' !important;}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{background: #606062;color: ' . $color7 . ';}
	div.js-ticket-table-header{background-color:#ecf0f5;border:1px solid  ' . $color5 . ';}
	div.js-ticket-table-header div.js-ticket-table-header-col{border-right:1px solid  ' . $color5 . ';}
	div.js-ticket-table-header div.js-ticket-table-header-col:last-child{border-right:none;}
	div.js-ticket-table-body div.js-ticket-data-row{border:1px solid  ' . $color5 . ';border-top:none}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{border-right:1px solid  ' . $color5 . ';}
	div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col:last-child{border-right:none;}
	th.js-ticket-table-th{border-right:1px solid  ' . $color5 . ';}
	tbody.js-ticket-table-tbody{border:1px solid  ' . $color5 . ';}
	td.js-ticket-table-td{border-right:1px solid  ' . $color5 . ';}
	div.js_ticketattachment{background-color: ' . $color3 . ';border:1px solid  ' . $color5 . ';}
';

wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);

?>
