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
	/* Staff Report Main Wrapper */
	div.js-ticket-staff-report-wrapper{
		float: left;
		width: 100%;
		box-sizing: border-box;
	}

	/* Card Styling for Search and Content Areas */
	div.js-ticket-top-search-wrp,
	div.js-ticket-downloads-wrp {
		padding: 1.5rem;
		border: 1px solid '. $jsst_color5 .';
		border-radius: 12px;
		box-shadow: 0 4px 6px rgba(0,0,0,0.04);
		margin-bottom: 2rem;
		background: #fff;
		float: left;
		box-sizing: border-box;
		width:100%;
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
	.js-ticket-downloads-heading-wrp {
    width: 100%;
    padding: 15px 20px;
    display: flex
;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    float: none;
   
    font-weight: 700;
    border-radius: 8px;
}
.js-col-md-4.js-admin-box-image {
    margin-top: 10px;
    vertical-align: middle;
    display: flex
;
    justify-content: center
}
div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-content{padding:5px;}


.js-admin-report-box-wrapper {float:left;width:100%;margin-top:20px;margin-bottom: 10px;}



.js-admin-report-box-wrapper .js-col-md-4.js-admin-box-image{width: 100%;}

	/* Main Form Container - Flexbox Layout */
	form#jssupportticketform {
		width: 100%;
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
	}

	div.js-ticket-fields-wrp {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		align-items: stretch;
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
		height:100%;
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
		    margin-bottom: 0;
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
        font-weight:600;
		margin: 0;
		border-radius: 8px;
		height: auto;
		line-height: 1.5;
		border: 1px solid '. $jsst_color5 .';
		cursor: pointer;
		transition: opacity 0.2s ease, background-color 0.2s ease;
	}

	/* Report Stats Boxes - Flexbox Layout */
	div.js-admin-report-box-wrapper {
		display: flex;
		flex-wrap: wrap;
		gap: 15px;
		margin-top: 20px;
		margin-bottom: 10px;
		width: 100%;
	}

	div.js-admin-report-box-wrapper div.js-admin-box {
	
		border: 1px solid '. $jsst_color5 .';
		border-radius: 8px;
		flex: 1 1 18%; /* Responsive boxes */
		display: flex;
		flex-direction: column;
		overflow: hidden; /* For border-radius on children */
		box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	}

	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-content {
		padding: 15px;
		flex-grow: 1;
		width:100%;
	}
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-content div.js-admin-box-content-number{text-align:center;font-size:24px;font-weight: bold;}
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-content div.js-admin-box-content-label{font-weight: 600;text-align:center;font-size:14px;padding:0px;margin-top:5px;color:' . $jsst_color4 . ';white-space: nowrap;overflow: hidden;text-overflow:ellipsis;}

	div.js-admin-report-box-wrapper div.js-admin-box.box1 div.js-admin-box-content div.js-admin-box-content-number{color:#1EADD8;}
	div.js-admin-report-box-wrapper div.js-admin-box.box2 div.js-admin-box-content div.js-admin-box-content-number{color:#179650;}
	div.js-admin-report-box-wrapper div.js-admin-box.box3 div.js-admin-box-content div.js-admin-box-content-number{color:#D98E11;}
	div.js-admin-report-box-wrapper div.js-admin-box.box4 div.js-admin-box-content div.js-admin-box-content-number{color:#DB624C;}
	div.js-admin-report-box-wrapper div.js-admin-box.box5 div.js-admin-box-content div.js-admin-box-content-number{color:#5F3BBB;}

	/* Colored label at the bottom of stat boxes */
	div.js-admin-report-box-wrapper div.js-admin-box div.js-admin-box-label { height: 5px; }
	div.js-admin-report-box-wrapper div.js-admin-box.box1 div.js-admin-box-label{background:#1EADD8;}
	div.js-admin-report-box-wrapper div.js-admin-box.box2 div.js-admin-box-label{background:#179650;}
	div.js-admin-report-box-wrapper div.js-admin-box.box3 div.js-admin-box-label{background:#D98E11;}
	div.js-admin-report-box-wrapper div.js-admin-box.box4 div.js-admin-box-label{background:#DB624C;}
	div.js-admin-report-box-wrapper div.js-admin-box.box5 div.js-admin-box-label{background:#5F3BBB;}


	/* Staff Details Section - Flexbox Layout */
	div.js-admin-staff-wrapper {
		
	}

	div.js-admin-staff-wrapper div.js-report-staff-image-wrapper {
		height: 100px;
		width: 100px;
		border-radius: 50%;
		flex-shrink: 0;
	}

	div.js-admin-staff-wrapper div.js-report-staff-image-wrapper img.js-report-staff-pic {
		width: 100%;
		height: 100%;
		background-color:white;
		object-fit: cover;
	}

	div.js-admin-staff-wrapper div.js-report-staff-cnt-wrapper {
		flex-grow: 1;
	}

	div.js-admin-staff-wrapper div.js-festaffreport-data {
		width: 100%;
		display: flex
;
    flex-wrap: wrap;
    gap: 15px;
        margin-bottom: 20px;
	}
	div.js-admin-staff-wrapper div.js-report-staff-name{display: block;padding:3px 0px;font-weight: bold;font-size: 18px;color:' . $jsst_color1 . ';margin-bottom:5px;}
	div.js-admin-staff-wrapper div.js-departmentname{font-weight: bold;font-size: 18px;color:#666666; margin: 15px 0px;}
	div.js-admin-staff-wrapper div.js-report-staff-username{display: block;padding:3px 0px;font-size: 15px;color: ' . $jsst_color4 . ';}
	div.js-admin-staff-wrapper div.js-report-staff-email{display: block;padding:3px 0px;font-size: 15px;color:' . $jsst_color4 . ';}


	/* Modern Table Styling */
	table.js-admin-report-tickets {
		width: 100%;
		border-collapse: collapse;
		margin-top: 1.5rem;
	}
	table.js-admin-report-tickets tr th {
		background: #ecf0f5;
		color: #333333;
		padding: 12px 15px;
		font-size: 16px;
		text-align: left;
		border-bottom: 2px solid ' . $jsst_color5 . ';
	}
	table.js-admin-report-tickets tr td {
		text-align: left;
		background: #FFFFFF;
		padding: 12px 15px;
		border-bottom: 1px solid ' . $jsst_color5 . ';
	}
	table.js-admin-report-tickets tr:last-child td {
		border-bottom: none;
	}
	table.js-admin-report-tickets tr td.overflow {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 200px; /* Adjust as needed */
	}
	table.js-admin-report-tickets tr td span.js-responsive-heading{display:none;}
	
	/* Other styles */
	div#no_message{background: #f6f6f6 none repeat scroll 0 0; border: 1px solid '. $jsst_color5 .'; color: #723776; display: inline-block; font-size: 15px; left: 50%; min-width: 80%; padding: 15px 20px; position: absolute; text-align: center; top: 50%; transform: translate(-50%, -50%); }
	h1.js-department-margin{padding-top: 15px;}
	.leftrightnull{padding-left: 0px; padding-right: 0px;}
	.js-admin-staff-anchor-wrapper {
    align-items: stretch; /* Make children equal height */
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px var(--shadow-color), 0 2px 4px -2px var(--shadow-color);
    text-decoration: none;
    color: var(--text-primary);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    max-width: 800px;
    margin: 20px auto; /* Center the card */
}

.js-admin-staff-anchor-wrapper:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px var(--shadow-color), 0 4px 6px -4px var(--shadow-color);
}
/* Left side: Profile image and info */
.js-festaffreport-img {
    display: flex;
    align-items: center;
    padding: 24px;
    

    flex-shrink: 0; /* Prevent this section from shrinking */
    margin: 20px;
    border: 1px solid;
    border-radius: 8px;
}

.js-report-staff-image-wrapper {
    flex-shrink: 0;
}

.js-report-staff-pic {
    width: 64px;
    height: 64px;
  	padding: 10px;
    object-fit: cover;
}
.js-report-staff-cnt-wrapper {
    margin-left: 16px;
    line-height: 1.4;
}

.js-report-staff-name {
    font-weight: 700;
    font-size: 18px; /* 18px */
    color: var(--text-primary);
}

.js-report-staff-username {
    font-size: 14px; /* 14px */
    color: var(--text-secondary);
}.js-report-staff-email {
    font-size: 14px; /* 14px */
    color: var(--text-secondary);
}

/* Right side: Statistics data */
.js-festaffreport-data {
    display: flex;
    justify-content: space-around;
    align-items: center;
    flex-grow: 1; /* Allow this section to take remaining space */
    padding: 16px 20px;
        padding-top: 0;
}
div.js-admin-staff-wrapper {
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 8px;
    margin-bottom: 30px;
}

.js-admin-report-box {
   
    border: 1px solid '. $jsst_color5 .';
    border-radius: 8px;
    flex: 1 1 18%;
    display: flex
;
    flex-direction: column-reverse;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    padding: 15px;
    justify-content: center;
    align-items: anchor-center;
}
/* Remove offset from the first box to help with centering */
.js-col-md-offset-1 {
    margin-left: 0;
}

.js-report-box-number {
    font-size: 30px; /* 32px */
    font-weight: 700;
    line-height: 1;
    color: var(--text-primary);
}
js-report-box-title {
    font-size: 14px; /* 12px */
    font-weight: 500;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 8px;
}

/* Colored indicator bars */
.js-report-box-color {
    width: 32px;
    height: 4px;
    
    border-radius: 2px;
}
.box1 .js-report-box-color { background-color: var(--color-new); }
.box2 .js-report-box-color { background-color: var(--color-answered); }
.box3 .js-report-box-color { background-color: var(--color-pending); }
.box4 .js-report-box-color { background-color: var(--color-overdue); }
.box5 .js-report-box-color { background-color: var(--color-closed); }
/* Responsive design for smaller screens */
@media (max-width: 768px) {
    .js-admin-staff-anchor-wrapper {
        flex-direction: column;
    }

    .js-festaffreport-img {
        border-right: none;
       
        justify-content: center; /* Center content on smaller screens */
    }
    
    .js-festaffreport-data {
        flex-wrap: wrap; /* Allow boxes to wrap */
        padding: 20px;
        gap: 16px; /* Add gap between wrapped items */
    }
}

@media (max-width: 480px) {
    .js-festaffreport-img {
        flex-direction: column;
        text-align: center;
    }
    
    .js-report-staff-cnt-wrapper {
        margin-left: 0;
        margin-top: 12px;
    }

    .js-admin-report-box {
        flex-basis: calc(33.33% - 12px); /* Three items per row */
    }
}

/*
=================================================================
== Comprehensive Responsive Media Queries for Staff Reports
=================================================================
*/

/* == Tablet View and Smaller Laptops == */
@media (max-width: 992px) {
    /* Adjust stat boxes to fit 3 per row */
    div.js-admin-report-box-wrapper div.js-admin-box,
    .js-admin-report-box {
        flex: 1 1 30%; /* 3 columns */
    }
}


/* == Mobile View == */
@media (max-width: 768px) {

    div.js-ticket-top-search-wrp,
    div.js-ticket-downloads-wrp {
        width: 100%;
        margin-left: 0;
    }

    /* --- Search Form --- */
    form#jssupportticketform {
        flex-direction: column;
        align-items: stretch; /* Make items full-width */
        gap: 1rem;
    }

    div.js-ticket-fields-wrp div.js-ticket-form-field {
        flex-basis: 100%;
    }

    div.js-ticket-search-form-btn-wrp {
        flex-direction: column;
        gap: 0.5rem;
    }

    div.js-ticket-search-form-btn-wrp input {
        width: 100%;
        padding: 0.9rem;
    }

    /* --- Stat Boxes --- */
    /* Adjust stat boxes to fit 2 per row */
    div.js-admin-report-box-wrapper div.js-admin-box,
    .js-admin-report-box {
        flex: 1 1 45%; /* 2 columns */
    }

    /* --- Staff Details Card --- */
    .js-admin-staff-anchor-wrapper,
    .js-festaffreport-img {
        flex-direction: column;
        text-align: center;
    }
    .js-report-staff-cnt-wrapper {
        margin-left: 0;
        margin-top: 1rem;
    }

    /* --- Table to Card Transformation --- */
    table.js-admin-report-tickets {
        border: 0;
    }

    /* Hide the table header */
    table.js-admin-report-tickets thead {
        display: none;
    }

    table.js-admin-report-tickets tr {
        display: block;
        margin-bottom: 1.5rem;
        border: 1px solid '. $jsst_color5 .';
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        padding: 0.5rem;
    }
    
    table.js-admin-report-tickets tr td {
        display: block;
        padding: 10px;
        padding-left: 50%; /* Create space for the label */
        text-align: right; /* Align data to the right */
        position: relative;
        border-bottom: 1px solid ' . $jsst_color5 . ';
        white-space: normal; /* Allow text to wrap */
        max-width: 100%; /* Override desktop max-width */
    }

    table.js-admin-report-tickets tr td:last-child {
        border-bottom: 0;
    }

    /* The magic: create labels from data attributes */
    table.js-admin-report-tickets tr td::before {
        content: attr(data-label); /* Reads the data-label attribute */
        position: absolute;
        left: 10px;
        width: calc(50% - 20px); /* Calculate width for the label */
        padding-right: 10px;
        white-space: nowrap;
        text-align: left;
        font-weight: bold;
        color: #333;
    }
}

/* == Small Mobile View == */
@media (max-width: 480px) {
    /* Adjust stat boxes to be full-width (1 per row) */
    div.js-admin-report-box-wrapper div.js-admin-box,
    .js-admin-report-box {
        flex: 1 1 100%; /* 1 column */
    }
}

';

/*Code For Colors*/
$jsst_jssupportticket_css .= '
.js-festaffreport-img{border:1px solid' . $jsst_color5 . ';background-color:#fff;}
	div.js-ticket-top-search-wrp{border: 1px solid ' . $jsst_color5 . ';background-color:#fff;}
	div.js-admin-staff-wrapper {
		border: 1px solid ' . $jsst_color5 . ';background-color: ' . $jsst_color3 . ';}
	.js-admin-report-box {background-color: #fff;}
	div.js-admin-report-box-wrapper div.js-admin-box {background-color:#fff !important;}
	span.js-report-box-title {
    color:' . $jsst_color2 . ';
    font-weight: 700;
    margin-bottom:10px;
}
.js-report-box-number {color:' . $jsst_color1 . ';}
	.js-report-staff-pic {border: 1px solid ' . $jsst_color5 . ';}
	div.js-ticket-search-heading-wrp{
		color:' . $jsst_color4 . ';
	}
	div.js-ticket-fields-wrp div.js-ticket-form-field input.js-ticket-field-input,
	select.js-ticket-select-field {
		background-color:#fff;
		border:1px solid ' . $jsst_color5 . ';
		color: ' . $jsst_color4 . ';
	}
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
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
	}
	div.js-ticket-search-form-btn-wrp input.js-reset-button:hover {
		background: ' . $jsst_color2 . ' !important;
		color: ' . $jsst_color7 . ';
	}
	table.js-admin-report-tickets tr th {
		background-color: ' . $jsst_color3 . ';
	}
	div.js-ticket-table-body div.js-ticket-data-row {
        border: 1px solid ' . $jsst_color5 . ';
    }

';


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
