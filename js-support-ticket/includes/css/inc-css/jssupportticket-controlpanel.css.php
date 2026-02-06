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





/*

*/

$jsst_jssupportticket_css = '';

/*Code for Css*/
$jsst_jssupportticket_css .= '
	/*
         * NEW Dashboard CSS - Full-Width Design
        */

        
        /* --- Main Layout Wrapper --- */
        .js-ticket-dashboard-container {
            width:1080px;
            max-width:100%;
            margin:20px auto;
        }

        * {
            box-sizing: border-box;
        }

        /* --- Left Menu --- */
        .js-ticket-dashboard-left-menu {
            width: 240px;
            background-color: #FFF;
            padding: 24px 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .js-ticket-dashboard-left-menu.js-dash-menu-link-hide {
            display: none;
        }
        .js-ticket-menu-header {
            font-size: 18px;
            font-weight: 600;
            padding: 0 10px 10px 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid ' . $jsst_color5 . ';
            color:' . $jsst_color2 . ';
        }

        .js-ticket-menu-links {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .js-ticket-menu-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 10px;
            text-decoration: none;
            color: ' . $jsst_color2 . ';
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .js-ticket-menu-links a:hover,
        .js-ticket-menu-links a.active {
            background-color: ' . $jsst_color1 . ';
            color: ' . $jsst_color7 . '!important;
        }

        .js-ticket-menu-links a:hover svg,
        .js-ticket-menu-links a.active svg {
           stroke: ' . $jsst_color7 . ';
        }


        .js-ticket-menu-links a svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
            flex-shrink: 0;
        }

        .js-ticket-menu-links li a span {
        	color: ' . $jsst_color4 . ';
        }

        .js-ticket-menu-links li a:hover {
        	text-decoration: none;
        }

        .js-ticket-menu-links li a:hover span {
        	color: ' . $jsst_color7 . ';
        	text-decoration: none;
        }

        /* --- Main Content --- */
        .js-ticket-dashboard-main-content {
           /* No special layout properties needed now */
        }

        /* --- Plugin Header --- */
        .js-ticket-plugin-header {
              display: flex
;
    align-items: center;
    justify-content: space-between;
    padding: 15px 25px;
    background-color: ' . $jsst_color1 . ';
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .js-ticket-header-nav {
            display: flex;
            gap: 10px;
        }
        .js-ticket-header-nav a {
            padding: 8px 16px;
            text-decoration: none;
            color: ' . $jsst_color4 . ';
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .js-ticket-header-nav a:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        .js-ticket-header-nav a.active {
            background-color: #fff;
            color: ' . $jsst_color1 . ';
        }

        .js-ticket-header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .js-ticket-logout-btn {
            padding: 8px 16px;
            text-decoration: none;
            color: ' . $jsst_color4 . ';
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: 1px solid ' . $jsst_color4 . ';
        }
        .js-ticket-logout-btn:hover {
            background-color: #fff;
            color: ' . $jsst_color1 . ';
        }

        /* --- Dashboard Layout --- */
        .js-ticket-dashboard-main {
            display: flex;
            gap: 24px;
            width: 100%;
            flex-wrap: wrap;
            padding:0 20px;
        }

        .js-ticket-dashboard-content-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
            max-width: calc(100% - 264px);
        }
        .js-ticket-card,
        .js-ticket-tickets-container,
        .js-ticket-stats-container,
        .js-ticket-resources-container {
            background-color: #FFF;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .js-ticket-card span {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 700;
            display: block;
            color: ' . $jsst_color2 . ';
        }

        .js-ticket-stats-card span {
            display: flex;
		    align-items: center;
		    gap: 12px;
		    font-weight: 500;
        }

        .js-ticket-card p {
            margin: 0 0 20px 0;
            color: ' . $jsst_color4 . ';
            font-size: 15px;
            line-height: 1.5;
        }

        .js-ticket-button {
            width: 100%;
            background-color: ' . $jsst_color1 . ';
            color: #fff !important;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .js-ticket-button svg {
            width: 16px;
            height: 16px;
        }

        .js-ticket-dashboard-grid-2-col {
            display:flex;
            flex-wrap:wrap;
            gap: 24px;
        }

        .js-ticket-new-ticket-card, .js-ticket-stats-card, .js-ticket-tickets-container, .js-ticket-stats-container, .js-ticket-resources-container {
            background-color: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            flex:1 1 auto;
        }
            .js-ticket-new-ticket-card, .js-ticket-stats-card{
            width:calc(100% / 2 - 12px);
            }

        .js-ticket-new-ticket-card span, 
        .js-ticket-stats-card span.js-ticket-stats-card-heading {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: 700;
            display: block;
            color: ' . $jsst_color2 . ';
        }
        .js-ticket-new-ticket-card p {
            margin: 0 0 20px 0;
            color: ' . $jsst_color4 . ';
            font-size: 15px;
            line-height: 1.5;
        }
        div.jsst-main-up-wrapper .js-ticket-new-ticket-btn {
            width: 100%;
            background-color: ' . $jsst_color1 . ';
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
            
        div.jsst-main-up-wrapper .js-ticket-new-ticket-btn:hover {
        	color: #fff;
        	text-decoration:none;
            background-color: ' . $jsst_color2 . ';
            transform: translateY(-2px);
        }
        .js-ticket-new-ticket-btn:hover { background-color: #4338ca; }
        .js-ticket-new-ticket-btn svg { width: 16px; height: 16px; }
        .js-ticket-or-separator {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
               color: ' . $jsst_color2 . ';
        }

        .js-ticket-or-separator .js-ticket-line {
            flex-grow: 1;
            height: 1px;
            background-color: ' . $jsst_color5 . ';
        }

        .js-ticket-or-separator .js-ticket-or-text {
            padding: 0 10px;
            font-weight: 500;
            font-size: 15px;
        }

        .js-ticket-create-section {
            text-align: center;
        }

        .js-ticket-tag-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        div.jsst-main-up-wrapper a.js-ticket-tag-btn {
            display: inline-block;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: 500;
            color: ' . $jsst_color4 . ';
            background-color: ' . $jsst_color3 . ';
            border: 1px solid ' . $jsst_color5 . ';
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        div.jsst-main-up-wrapper a.js-ticket-tag-btn:hover {
//          background-color: ' . $jsst_color1 . ';
		    color: ' . $jsst_color1 . ';
		    border-color: ' . $jsst_color1 . ';
            transform: translateY(-2px);

        }

        .js-ticket-tag-btn:hover {
            background-color: ' . $jsst_color7 . ';
            color: ' . $jsst_color1 . ';
            border-color: ' . $jsst_color1 . ';
        }

        .js-ticket-stats-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        div.jsst-main-up-wrapper a.js-ticket-stat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            color: ' . $jsst_color4 . ';
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        div.jsst-main-up-wrapper a.js-ticket-stat-item:hover {
            background-color: ' . $jsst_color3 . ';
            transform: translateY(-2px);
        }
        div.jsst-main-up-wrapper a.js-ticket-stat-item:hover .js-ticket-stat-item-label span{
            color: ' . $jsst_color1 . ';
        }
        .js-ticket-stat-item-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
        .js-ticket-stat-item-label svg {

        }
        .js-ticket-stat-count {
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 15px;
        }

        .js-ticket-welcome-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-left: 5px;
           padding-top: 20px;
            padding-bottom: 20px;
        }
        .js-ticket-welcome-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .js-ticket-welcome-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        .js-ticket-welcome-header span {
            margin: 0 0 4px 0;
            font-size: 24px;
            color: ' . $jsst_color2 . ';
            font-weight: 700;
        }
        .js-ticket-welcome-header p {
            margin: 0;
            color: ' . $jsst_color4 . ';
        }
        .js-ticket-welcome-actions {
            display: flex;
            gap: 10px;
        }
        .js-ticket-user-data-btn {
            background-color: ' . $jsst_color2 . ';
            color: ' . $jsst_color1 . ';
            border: 1px solid ' . $jsst_color5 . ';
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .js-ticket-user-data-btn:hover {
            background-color: ' . $jsst_color1 . ';
            color: #fff;
            border-color: ' . $jsst_color1 . ';
        }
        .js-ticket-user-data-btn.primary {
            background-color: ' . $jsst_color1 . ';
            color: #fff;
            border-color: ' . $jsst_color1 . ';
        }
        .js-ticket-user-data-btn.primary:hover {
            background-color: '. $jsst_color1 .';
            border-color: '. $jsst_color1 .';
        }

        .js-ticket-container-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0 20px 0;
            border-bottom: 1px solid ' . $jsst_color5 . ';
            margin-bottom: 12px;
            font-size: 18px;
        }
        .js-ticket-container-header span {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: ' . $jsst_color2 . ';
        }
        div.jsst-main-up-wrapper .js-ticket-container-header a {
            font-size: 15px;
            font-weight: 500;
            color: ' . $jsst_color1 . ';
            text-decoration: none;
        }
        div.jsst-main-up-wrapper .js-ticket-container-header a:hover{
            color: ' . $jsst_color2 . ';
        
        }

        .js-ticket-container-content {
            padding: 0;
        }

        .js-ticket-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 10px;
            border-radius: 8px;
            transition: background-color 0.2s;
            border-bottom: 1px solid ' . $jsst_color5 . ';
            gap: 10px; /* Added gap for responsiveness */
        }
        .js-ticket-row:last-child { border-bottom: none; }
        .js-ticket-row:hover { background-color: ' . $jsst_color3 . '; }

        .js-ticket-first-left {
            display: flex;
            flex-wrap:wrap;
            align-items: center;
            gap: 15px;
            flex-grow: 1;
            min-width: 250px; /* Ensure it does not get too squished */
        }

        .js-ticket-user-img-wrp img {
            width: 40px;
            min-width:40px;
            height: 40px;
            border-radius: 50%;
        }

        .js-ticket-agent-avatar {
            margin-left: -20px;
            border: 2px solid ' . $jsst_color1 . ';
        }
        .js-ticket-ticket-subject{
            flex:1 1 auto;
            width:calc(100% - 50%);
            display:flex;
            flex-direction:column;
            min-width:50%;
        }
        .js-ticket-data-row.name a {
            font-weight: 600;
            color: ' . $jsst_color2 . ';
            text-decoration: none;
            display:inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width:100%;
        }
        .js-ticket-ticket-subject .js-ticket-data-row{
            display:inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width:100%;
        }
            .js-ticket-data-row.name a:hover{
                color: ' . $jsst_color1 . ';
            }

        .js-ticket-data-row {
            font-size: 15px;
            color: ' . $jsst_color4 . ';
        }
        .js-ticket-ticket-meta {
            display: flex;
            align-items: center;
            gap: 10px; /* spacing between tags */
            flex-wrap: wrap; /* Allow tags to wrap on smaller screens */
        }
        .js-ticket-priority-tag {
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }
        .js-ticket-priority-high { color: #FFF; }
        .js-ticket-priority-medium { background-color: #fef3c7; color: #f59e0b; }
        .js-ticket-priority-low { background-color: #dbeafe; color: #3b82f6; }

        .js-ticket-status {
            padding: 10px 15px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }

        .js-ticket-assign-tag {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            background-color: ' . $jsst_color3 . ';
            color: ' . $jsst_color4 . ';
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid ' . $jsst_color5 . ';
        }

        div.jsst-main-up-wrapper .js-ticket-assign-tag:hover {
            background-color: ' . $jsst_color1 . ';
            color: #fff;
            border-color: ' . $jsst_color1 . ';
        }

        #js-ticket-pm-grapharea {
            height: 300px;
        }

        .js-ticket-tabs-nav {
            display: flex;
            border-bottom: 1px solid ' . $jsst_color5 . ';
            margin-bottom: 10px;
        }

        .js-ticket-tab-pane { display: none; }
        .js-ticket-tab-pane.active { display: block; }

        .js-ticket-resource-list {
            display: flex;
            flex-direction: column;
        }
        .js-ticket-resource-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 10px;
            border-bottom: 1px solid ' . $jsst_color5 . ';
        }
        .js-ticket-resource-item:last-child { border-bottom: none; }

        .js-ticket-resource-link {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            flex-grow: 1;
        }

        .js-ticket-resource-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .js-ticket-resource-icon svg {  }

        div.jsst-main-up-wrapper .js-ticket-resource-link .js-ticket-resource-title:hover {
            color: ' . $jsst_color1 . ';
            text-decoration: none !important;
        }
        div.jsst-main-up-wrapper .js-ticket-resource-link:hover {
            text-decoration: none !important;
        }
        div.jsst-main-up-wrapper .js-ticket-resource-title {
            font-weight: 500;
            color: ' . $jsst_color2 . ';
            transition: color 0.2s;
            line-height: 1.5;
        }

        .js-ticket-resource-actions {
            display: flex;
            gap: 10px;
        }

        div.jsst-main-up-wrapper .js-ticket-action-btn {
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
            color: ' . $jsst_color4 . ';
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .js-ticket-action-btn svg {
            width: 18px;
            height: 18px;
        }

        div.jsst-main-up-wrapper .js-ticket-action-btn:hover {
            color: ' . $jsst_color2 . ';
        }

/* --- Existing Styles for resources-container --- */
.js-ticket-resources-container {
    background-color: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.js-ticket-tabs-nav {
    flex-wrap: wrap; /* Allow tabs to wrap on smaller screens */
}

.js-ticket-tab-btn {
    padding: 12px 16px; /* Adjusted padding for a better fit */
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 600;
    color: ' . $jsst_color4 . ';
    position: relative;
    text-align: center;
    flex-grow: 1; /* Allow buttons to grow and fill space */
}

.js-ticket-tab-btn.active {
    color: ' . $jsst_color1 . ';
    outline: 0;
}

.js-ticket-tab-btn.active::after {
    content: "";
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: ' . $jsst_color1 . ';
}

.js-ticket-tab-pane { display: none; }
.js-ticket-tab-pane.active { display: block; }

.js-ticket-resource-list {
    display: flex;
    flex-direction: column;
}
/*download popup */	
div#js-ticket-main-black-background{position: fixed;width: 100%;height: 100%;background: rgba(0,0,0,0.7);z-index: 998;top:0px;left:0px;}
div#js-ticket-main-popup {position: fixed;top: 50%;left: 50%;width: 60%;max-height: 70%;padding-top: 0px;z-index: 99999;overflow-y: auto;overflow-x: hidden;background: #fff;transform: translate(-50%,-50%);border-radius: 15px;box-shadow: 0 10px 30px rgba(0,0,0,0.2);}
span#js-ticket-popup-close-button{position: absolute;top:18px;right: 18px;width:25px;height: 25px;}
span#js-ticket-popup-close-button:hover{cursor: pointer;}
div#js-ticket-main-content {float: left;width: 100%;padding: 0px 25px;}
div.js-ticket-downloads-content {float: left;width: 100%;padding: 20px 0px;}
div.js-ticket-download-description {float: left;width: 100%;padding: 0px 0px 15px;line-height: 1.8;}
div.js-ticket-download-description p {margin: 0;}
div.js-ticket-downloads-content div.js-ticket-download-box {float: left;width: 100%;padding: 10px;margin-bottom: 10px;border-radius:8px;}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left {float: left;width: 80%;}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title {float: left;width: 100%;padding: 9px;cursor: pointer;line-height: initial;text-decoration: none;}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title img.js-ticket-download-icon {float: left;}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {width: calc(100% - 60px); display: inline-block;padding: 10px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-right {float: left;width: 20%;}
div#js-ticket-main-downloadallbtn {float: left;width: 100%;padding: 0px 25px 20px;}
#js-ticket-main-popup div.js-ticket-download-btn {padding: 8px 0;text-align: right;}
div.js-ticket-download-btn a.js-ticket-download-btn-style {display: inline-block;padding: 12px 20px;border-radius: unset;font-weight: unset;text-decoration: none;outline: 0;line-height: initial;}
#js-ticket-main-popup #js-ticket-main-downloadallbtn .js-ticket-download-btn {text-align: left;}
div#js-ticket-main-popup {background: #fff !important;}
div.js-ticket-download-description {color: ' . $jsst_color4 . ';}
div.js-ticket-downloads-content div.js-ticket-download-box {border: 1px solid ' . $jsst_color5 . ';box-shadow: 0 8px 6px -6px #dedddd;}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {color: ' . $jsst_color4 . ';}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name:hover {color: ' . $jsst_color2 . ';}
div.js-ticket-download-btn a.js-ticket-download-btn-style {background-color: ' . $jsst_color1 . ';color:' . $jsst_color7 . '; border: 1px solid ' . $jsst_color1 . ';border-radius:10px;font-weight:500;}
div.js-ticket-download-btn a.js-ticket-download-btn-style:hover {background-color: ' . $jsst_color2 . ';border: 1px solid ' . $jsst_color2 . ';color:' . $jsst_color7 . ';}
#js-ticket-main-popup #js-ticket-main-downloadallbtn .js-ticket-download-btn a.js-ticket-download-btn-style {background-color: ' . $jsst_color1 . ';color: #ffffff;border-color: ' . $jsst_color1 . ';}
#js-ticket-main-popup #js-ticket-main-downloadallbtn .js-ticket-download-btn a.js-ticket-download-btn-style:hover {border-color: ' . $jsst_color2 . ';}
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
span#js-ticket-popup-title { background-color: ' . $jsst_color3 . '; color: ' . $jsst_color2 . '; border-bottom-color: ' . $jsst_color5 . '; }
/* --- Responsive Design for resources-container --- */
@media (max-width: 768px) {
    .js-ticket-resources-container {
        padding: 20px;
    }

    .js-ticket-tab-btn {
        font-size: 15px;
        padding: 12px 10px;
    }
    .js-ticket-new-ticket-card{
        width:100%;
    }
}
@media (max-width: 650px) {

    .js-ticket-user-img-wrp{width:100%;text-align:center;}
    .js-ticket-ticket-meta{flex:1 1 auto;justify-content:center;}
    .js-ticket-row{flex-wrap:wrap;}
    .js-ticket-welcome-avatar{min-width:60px;}
    .js-ticket-welcome-left{flex-direction:column;align-items:center;text-align:center;width:100%;}
    
}

@media (max-width: 480px) {
    .js-ticket-tabs-nav {
        flex-direction: column; /* Stack tabs vertically on very small screens */
        border-bottom: none;
    }

    .js-ticket-tab-btn {
        border-bottom: 1px solid ' . $jsst_color5 . ';
        text-align: left;
        flex-grow: 0; /* Prevent buttons from growing */
    }

    .js-ticket-tab-btn.active::after {
        width: 4px; /* Change indicator to a side border */
        height: 100%;
        left: 0;
        top: 0;
        bottom: 0;
    }

    .js-ticket-resource-item {
        padding: 12px 5px;
        gap: 12px;
    }

    .js-ticket-resource-title {
        font-size: 15px;
    }
}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row div.js-ticket-table-body-col p{display:inline;}

/* --- Responsive Design --- */
@media (max-width: 992px) {
    .js-ticket-dashboard-main {
        flex-direction: column;
    }
    .js-ticket-dashboard-grid-2-col {
        grid-template-columns: 1fr;
    }
    .js-ticket-dashboard-left-menu {
        width: 100%;
        height: auto;
        border-right: none;
        border-bottom: 1px solid ' . $jsst_color5 . ';
    }
    .js-ticket-dashboard-content-area {
        max-width: 100%;
    }
}
@media (max-width: 768px) {
     .js-ticket-plugin-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    .js-ticket-welcome-header {
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    .js-ticket-welcome-actions {
        margin-top: 15px;

    }
}

';



wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
