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

div.js-ticket-search-form-btn-wrp input.js-search-button{
		background: ' . $jsst_color1 . ' !important;
		color: ' . $jsst_color7 . ' !important;
		border-color: ' . $jsst_color1 . ';
	}
	div.js-ticket-search-form-btn-wrp input.js-search-button:hover {
		background: ' . $jsst_color2 . ' !important;
		border-color: ' . $jsst_color2 . ';
	}
	div.js-ticket-search-form-btn-wrp input.js-reset-button{
		background-color: #f5f2f5;
		color: #636363;
		border: 1px solid '. $jsst_color5 .';
	}
	div.js-ticket-search-form-btn-wrp input.js-reset-button:hover {
		background: ' . $jsst_color2 . ' !important;
		color: ' . $jsst_color7 . ';
	}

div.js-ticket-table-header div.js-ticket-table-header-col {
  background: ' . $jsst_color3 . ' !important;
}
	/* Staff Report Main Wrapper */
	div.js-ticket-staff-report-wrapper{
		box-sizing: border-box;
		display:flex;
		flex-wrap:wrap;
		flex-direction:column;
	}

	/* Card Styling for Search Area */
div.js-ticket-top-search-wrp{display:flex;}
.js-ticket-search-fields-wrp

 {
    width: 100%;
}
	div.js-ticket-top-search-wrp {
		padding: 1.5rem;
		border: 1px solid '. $jsst_color5 .';
		border-radius: 12px;
		box-shadow: 0 4px 6px rgba(0,0,0,0.04);
		margin-bottom: 2rem;
		background: #fff;
		width: 100%;
		box-sizing: border-box;
	}

	div.js-ticket-search-heading-wrp{
		display: flex;
		justify-content: space-between;
		align-items: center;
		width: 100%;
		padding-bottom: 1rem;
		margin-bottom: 1.5rem;
		border-bottom: 1px solid ' . $jsst_color5 . ';
	}

	div.js-ticket-search-heading-wrp div.js-ticket-heading-left{
		font-size: 21px;
		font-weight: 700;
	}

	/* Main Form Container - Flexbox Layout */
	form#jssupportticketform {
		width: 100%;
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
	}

	div.js-ticket-fields-wrp {
		display: flex;
		align-items: stretch;
		flex-wrap: wrap;
		gap: 10px;
	}

	/* Individual Form Fields */
	div.js-ticket-fields-wrp div.js-ticket-form-field {
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
		margin-bottom: 0;
		min-height:52px;
	}

	/* Date Picker Icon Styling */
	input#jsst-date-start,
	input#jsst-date-end {
		background-image: url(' . esc_url(JSST_PLUGIN_URL) . 'includes/images/calender.png);
		background-repeat: no-repeat;
		background-position: right 1rem center;
		background-size: 20px;
		padding-right: 3rem; /* Space for icon */
		height:100%;
	}

	/* Button Wrapper */
	div.js-ticket-search-form-btn-wrp {
		width: auto;
		padding: 0;
		margin-top: 0;
		display: flex;
		gap: 0.5rem;
	}

	/* Buttons */
	div.js-ticket-search-form-btn-wrp input {
		width: auto;
        padding: 5px 20px;
        min-height:52px;
        min-width:120px;
        font-weight:600;;
		flex: 1 1 auto;
		margin: 0;
		border-radius: 8px;
		height: auto;
		line-height: 1.5;
		border: 1px solid '. $jsst_color5 .';
		cursor: pointer;
		transition: opacity 0.2s ease, background-color 0.2s ease;
	}

	/* Downloads Section */
	div.js-ticket-downloads-wrp{
		padding: 1.5rem;
		border: 1px solid '. $jsst_color5 .';
		border-radius: 12px;
		box-shadow: 0 4px 6px rgba(0,0,0,0.04);
		margin-top: 18px;
		background: #fff;
		box-sizing: border-box;
		width:100%;
		display:flex;
		flex-direction:column;
}
	div.js-ticket-error-message-wrapper{
		margin-top:20px;
	}
div.js-ticket-downloads-heading-wrp {
    color: #636363;
    width: 100%;
    padding: 15px 20px;
    display: flex
;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    float: none;
   width:100%;
    font-weight: 700;
    border-radius: 8px;
}

	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{
		    width: 100%;
    padding: 10px 20px;
    display: flex
;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    float: none;
    font-size: 21px;
    font-weight: 700;
    border-radius: 8px;
	}

	/* Stats boxes and other elements from original file */
	div.js-admin-report-box-wrapper{float:left;width:100%;margin-top:20px;margin-bottom: 10px;}
	
	div.js-admin-report-box-wrapper div.js-admin-box{background:#ffffff;border:1px solid ' . $jsst_color5 . ';padding:0px;width: calc(100% / 5 - 5px);margin: 0px 2.5px; }
	div.js-admin-report-box-wrapper.js-admin-controlpanel div.js-admin-box{margin-right: 0px;}
	div.js-admin-report-box-wrapper div.js-admin-box.js-col-md-offset-2{margin-left:11%;}
	div.js-admin-report-box-wrapper.js-admin-controlpanel div.js-admin-box.js-col-md-offset-2{margin-left:0px;}
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-image{padding:5px;}
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-image img{max-width: 100%;max-height: 100%;}
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-content{padding:5px;}
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-content div.js-admin-box-content-number{text-align: right;font-size:24px;font-weight: bold;}
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-content div.js-admin-box-content-label{text-align: right;font-size:12px;padding:0px;margin-top:5px;color:' . $jsst_color2 . ';white-space: nowrap;overflow: hidden;text-overflow:ellipsis;}
	div.js-admin-report-box-wrapper div.js-admin-box.box1 div.js-admin-box-content div.js-admin-box-content-number{color:#1EADD8;}
	div.js-admin-report-box-wrapper div.js-admin-box.box2 div.js-admin-box-content div.js-admin-box-content-number{color:#179650;}
	div.js-admin-report-box-wrapper div.js-admin-box.box3 div.js-admin-box-content div.js-admin-box-content-number{color:#D98E11;}
	div.js-admin-report-box-wrapper div.js-admin-box.box4 div.js-admin-box-content div.js-admin-box-content-number{color:#DB624C;}
	div.js-admin-report-box-wrapper div.js-admin-box.box5 div.js-admin-box-content div.js-admin-box-content-number{color:#5F3BBB;}

	div.js-admin-report-box-wrapper div.js-admin-box.box1 div.js-admin-box-label{height:20px;background:#1EADD8;}
	div.js-admin-report-box-wrapper div.js-admin-box.box2 div.js-admin-box-label{height:20px;background:#179650;}
	div.js-admin-report-box-wrapper div.js-admin-box.box3 div.js-admin-box-label{height:20px;background:#D98E11;}
	div.js-admin-report-box-wrapper div.js-admin-box.box4 div.js-admin-box-label{height:20px;background:#DB624C;}
	div.js-admin-report-box-wrapper div.js-admin-box.box5 div.js-admin-box-label{height:20px;background:#5F3BBB;}
	.js-col-md-8.nopadding.js-festaffreport-data{padding:none;}

	/* Staff Details Section */
	div.js-admin-staff-wrapper{display: inline-block;width:100%;margin-top:20px;border:1px solid ' . $jsst_color5 . ';background-color:' . $jsst_color3 . ' !important;}
	div.js-admin-staff-wrapper.js-departmentlist{padding: 10px;}
	div.js-admin-staff-wrapper.js-departmentlist div.departmentname{font-size: 20px;}
	div.js-admin-staff-wrapper.js-departmentlist div.jsposition-reletive{padding-top: 30px;}
	div.js-admin-staff-wrapper.padding{padding:10px;}
	div.js-admin-staff-wrapper .nopadding{display: flex
;
    align-items: center;
    
    flex-shrink: 0;
    margin: 20px;
    border: 1px solid;
    border-radius: 8px;
width: 95%;}
	.js-col-md-4.nopadding.js-festaffreport-img {
    padding: 20px;
    border:1px solid ' . $jsst_color5 . ';background-color:' . $jsst_color7 . ' !important;
}
.js-col-md-8.nopadding.js-festaffreport-data {
    border: none;
    padding:0px;
}
div.js-admin-staff-wrapper div.js-admin-report-box{width:100%;}
	div.js-admin-staff-wrapper div.js-report-staff-image-wrapper{height: 100px;width: 100px;text-align: center;line-height: 90px;float: left;border: 1px solid black;}
	div.js-admin-staff-wrapper div.js-report-staff-image-wrapper img.js-report-staff-pic{width:80px;height:80px;margin:0 auto;display: inline-block;}
	div.js-admin-staff-wrapper div.js-report-staff-name{display: block;padding:3px 0px;font-weight: bold;font-size: 18px;color:' . $jsst_color1 . ';margin-bottom:5px;}
		div.js-admin-staff-wrapper div.js-report-staff-name:hover{color:' . $jsst_color2 . ';}
	div.js-admin-staff-wrapper div.js-report-staff-cnt {float: left;width:calc(100% - 100px);padding: 6px 0 0 10px;}
	div.js-admin-staff-wrapper div.js-departmentname{font-weight: bold;font-size: 18px;color: ' . $jsst_color4 . '; margin: 15px 0px;}
	div.js-admin-staff-wrapper div.js-report-staff-username{display: block;padding:3px 0px;font-size: 14px;color: ' . $jsst_color4 . ';}
	div.js-admin-staff-wrapper div.js-report-staff-email{display: block;padding:3px 0px;font-size: 14px;color: ' . $jsst_color4 . ';}
	div.js-admin-staff-wrapper div.js-admin-report-box{background: #fff;border:1px solid ' . $jsst_color5 . ';margin-left:8px;padding:0px;padding-top:10px;}
	div.js-admin-staff-wrapper div.js-admin-report-box span.js-report-box-number{color:' . $jsst_color2 . ';display: block;font-size:30px;font-weight: bold;text-align: center;margin:5px 0px 10px 0px;}
	div.js-admin-staff-wrapper div.js-admin-report-box span.js-report-box-title{color:' . $jsst_color4 . ';display: block;font-size:16px;text-align: center;padding:5px 4px 10px 4px;white-space: nowrap;text-overflow:ellipsis;overflow: hidden;font-weight:bold;}
	
	div.js-admin-staff-wrapper div.js-admin-report-box.box1 div.js-report-box-color{height:5px;background:#1EADD8;}
	div.js-admin-staff-wrapper div.js-admin-report-box.box2 div.js-report-box-color{height:5px;background:#179650;}
	div.js-admin-staff-wrapper div.js-admin-report-box.box3 div.js-report-box-color{height:5px;background:#D98E11;}
	div.js-admin-staff-wrapper div.js-admin-report-box.box4 div.js-report-box-color{height:5px;background:#DB624C;}
	div.js-admin-staff-wrapper div.js-admin-report-box.box5 div.js-report-box-color{height:5px;background:#5F3BBB;}

	/* Table Styling */
	table.js-admin-report-tickets{width:100%; border-collapse: collapse; margin-top: 1.5rem;}
	table.js-admin-report-tickets tr th{background:#ecf0f5;color:#333333;padding:12px 15px;font-size:16px; text-align: left; border-bottom: 2px solid ' . $jsst_color5 . ';}
	table.js-admin-report-tickets tr td{text-align: left;background:#FFFFFF;padding:12px 15px; border-bottom: 1px solid ' . $jsst_color5 . ';}
	table.js-admin-report-tickets tr:last-child td { border-bottom: none; }
	table.js-admin-report-tickets tr td.overflow{white-space: nowrap;overflow: hidden;text-overflow:ellipsis;max-width: 200px;}
	table.js-admin-report-tickets tr td span.js-responsive-heading{display:none;}

	/* Other styles */
	div#no_message{background: #f6f6f6 none repeat scroll 0 0; border: 1px solid '. $jsst_color5 .'; color: #723776; display: inline-block; font-size: 15px; left: 50%; min-width: 80%; padding: 15px 20px; position: absolute; text-align: center; top: 50%; transform: translate(-50%, -50%); }
	h1.js-department-margin{padding-top: 15px;}
	.leftrightnull{padding-left: 0px; padding-right: 0px;}

/* 2. GENERAL STYLES & WRAPPER */
.js-ticket-download-content-wrp {
    margin: 20px 0;
    

}
.js-ticket-table-wrp {
    overflow: hidden; /* Ensures the content respects the border-radius */
}
/* 3. DESKTOP TABLE HEADER */
.js-ticket-table-header {
    display: flex;
    
    font-weight: 600;
    
    text-transform: uppercase;
    font-size:14px; /* 12px */
    letter-spacing: 0.5px;
    
}

.js-ticket-table-header-col {
    padding: 15px 20px;
}

/* 4. TABLE BODY & ROWS */
.js-ticket-data-row {
    display: flex;
    align-items: center;
    transition: background-color 0.2s ease-in-out;
}
.js-ticket-data-row:last-child {
    border-bottom: none;
}
.js-ticket-table-body-col {
    padding: 20px;
}
/* 5. GRID COLUMN WIDTHS (Desktop) */
.js-col-md-4 { width: 34%; }
.js-col-md-3 { width: 22%; }
.js-col-md-2 { width: 22%; } /* Adjusted for better spacing */

/* 6. CONTENT STYLING */
.js-ticket-title-anchor {
    font-weight: 500;

    text-decoration: none;
    transition: color 0.2s ease;
}
.js-ticket-title-anchor:hover {
   
    text-decoration: underline;
}
/* Status and Priority Badges */
.js-ticket-priority {
    padding: 6px 12px;
    border-radius: 15px; /* Pill shape */
    font-size: 14px;
    font-weight: 600;
    color: #fff; /* Default color, overridden by inline styles */
    display: inline-block;
    min-width: 80px;
    text-align: center;
}

/* Initially hide the mobile-only labels */
.js-ticket-display-block {
    display: none;
}


/* 7. RESPONSIVE DESIGN (For screens smaller than 768px) */
@media (max-width: 768px) {
    /* Hide the desktop header */
    .js-ticket-table-header {
        display: none;
    }

    /* Stack row items vertically */
    .js-ticket-data-row {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px 0;
    }

    /* Make columns full-width and add spacing */
    .js-ticket-table-body-col {
        width: 100%;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .js-ticket-data-row .js-ticket-table-body-col:last-child {
        border-bottom: none;
    }

    /* Show and style the mobile labels */
    .js-ticket-display-block {
        display: inline;
        font-weight: 600;
        margin-right: 10px;
    }

    .js-ticket-title {
        text-align: right;
    }
}
/*
=================================================================
== Comprehensive Responsive Media Queries for Staff Detail Report
=================================================================
*/

/* == Tablet View (and smaller laptops) == */
@media (max-width: 992px) {
    /* Adjust stat boxes to fit 3 per row instead of 5 */
    div.js-admin-report-box-wrapper div.js-admin-box {
        width: calc(33.333% - 10px);
        margin: 5px;
    }
}
/* == Mobile View == */
@media (max-width: 480px) {
div.js-ticket-fields-wrp div.js-ticket-form-field{    flex: 1 1 0;}

}
/* == Mobile View == */
@media (max-width: 768px) {
    /* --- General Wrappers & Layout --- */
    div.js-ticket-staff-report-wrapper,
    div.js-ticket-downloads-wrp {
        width: 100%;
        margin-left: 0;
        padding: 1rem;
    }

    /* --- Search Form --- */
    div.js-ticket-top-search-wrp,
    form#jssupportticketform {
        flex-direction: column;
        align-items: stretch;
    }

    div.js-ticket-search-form-btn-wrp {
        flex-direction: column;
        width: 100%;
        gap: 0.5rem;
    }

    div.js-ticket-search-form-btn-wrp input {
        width: 100%;
        padding: 0.9rem;
    }

    /* --- Statistics Boxes --- */
    /* Adjust stat boxes to fit 2 per row */
    div.js-admin-report-box-wrapper div.js-admin-box {
        width: calc(50% - 10px);
        margin: 5px;
    }
    
    div.js-admin-report-box-wrapper div.js-admin-box.js-col-md-offset-2 {
        margin-left: 5px; /* Reset large offset */
    }

    /* --- Staff Details Section --- */
    div.js-admin-staff-wrapper .nopadding {
        flex-direction: column;
        text-align: center;
        width: 100%;
        margin: 0;
    }

    .js-col-md-4.nopadding.js-festaffreport-img {
        margin-bottom: 1rem;
    }

    div.js-admin-staff-wrapper div.js-report-staff-image-wrapper {
        float: none;
        margin: 0 auto 1rem auto; /* Center the image */
    }

    div.js-admin-staff-wrapper div.js-report-staff-cnt {
        float: none;
        width: 100%;
        padding: 0;
        text-align: center;
    }
    
    div.js-admin-staff-wrapper div.js-admin-report-box {
        margin-left: 0;
        margin-bottom: 10px;
    }

    /* --- Main Data Table to Card Transformation --- */
    table.js-admin-report-tickets thead {
        display: none; /* Hide the desktop header */
    }

    table.js-admin-report-tickets tr {
        display: block;
        margin-bottom: 1.5rem;
        border: 1px solid '. $jsst_color5 .';
        border-radius: 8px;
        padding: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    table.js-admin-report-tickets tr td {
        display: block;
        padding-left: 50%; /* Create space for the label */
	}
}
';
/*Code For Colors*/
$jsst_jssupportticket_css .= '


div.js-admin-staff-wrapper{background-color:' . $jsst_color3 . ' !important;}

	table.js-admin-report-tickets tr th {
		background-color: ' . $jsst_color3 . ';
	}
div.js-admin-staff-wrapper div.js-report-staff-image-wrapper{border: 1px solid ' . $jsst_color5 . ';}
div.js-admin-staff-wrapper.padding{background-color:' . $jsst_color3 . ' !important;}
div.js-admin-staff-wrapper .nopadding{background-color:' . $jsst_color7 . ' !important;border: 1px solid ' . $jsst_color5 . ';}
	


	div.js-ticket-top-search-wrp{border: 1px solid ' . $jsst_color5 . ';background-color: ' . $jsst_color7 . ';}
	div.js-ticket-downloads-wrp{border: 1px solid ' . $jsst_color5 . ';background-color: ' . $jsst_color7 . ';}
	div.js-ticket-search-heading-wrp{color:' . $jsst_color4 . ';}
	.js-col-md-4.nopadding.js-festaffreport-img{border: 1px solid ' . $jsst_color5 . '!important;}
	div.js-ticket-table-header {background-color:' . $jsst_color3 . ' !important;border: 1px solid ' . $jsst_color5 . '; }
	.js-col-md-8.nopadding.js-festaffreport-data {background-color:' . $jsst_color3 . ' !important;}
	div.js-ticket-downloads-heading-wrp{
		color:' . $jsst_color4 . ';
	}
	div.js-report-staff-name:hover{ color:' . $jsst_color2 . ' !important;}
	.js-ticket-table-header {background-color:' . $jsst_color3 . ' !important; border-bottom: 2px solid solid ' . $jsst_color5 . ';}
	a.js-ticket-title-anchor { color:' . $jsst_color1 . ' !important;}
	a.js-ticket-title-anchor :hover {color:' . $jsst_color2 . ' !important;}


	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input,
	select.js-ticket-select-field {
		background-color:#fff;
		border:1px solid ' . $jsst_color5 . ';
		color: ' . $jsst_color4 . ';
	}
	
';


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
