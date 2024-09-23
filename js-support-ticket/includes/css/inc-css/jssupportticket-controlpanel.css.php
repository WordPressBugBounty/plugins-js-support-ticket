<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
// if header is calling later
JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();
$color1 = jssupportticket::$_colors['color1'];
$color2 = jssupportticket::$_colors['color2'];
$color3 = jssupportticket::$_colors['color3'];
$color4 = jssupportticket::$_colors['color4'];
$color5 = jssupportticket::$_colors['color5'];
$color6 = jssupportticket::$_colors['color6'];
$color7 = jssupportticket::$_colors['color7'];
$color8 = jssupportticket::$_colors['color8'];
$color9 = jssupportticket::$_colors['color9'];





/*

*/

$jssupportticket_css = '';

/*Code for Css*/
$jssupportticket_css .= '
	/*new css*/
	div.js-cp-main-wrp {float: left;width: 100%;margin: 0 !important;}
	div.js-cp-main-wrp div.js-cp-left {float: left;width: 30%;padding-right: 25px;}
	div.js-cp-main-wrp div.js-cp-right {float: left;width: 70%;}
	div.js-cp-main-wrp div.js-cp-right.js-cp-right-fullwidth {width: 100%;}

	/* Dashboard Menu Links */
	div#js-dash-menu-link-wrp{float: left;width: 100%;}
	div#js-dash-menu-link-wrp.js-dash-menu-link-hide{display:none}
	div.js-section-heading{float: left;width: 100%;padding: 20px 15px;font-size: 20px;font-weight: bold;line-height: initial;}
	div.js-menu-links-wrp{float: left;width: 100%;}
	a.js-ticket-dash-menu{float: left;width: 100%;padding: 10px;line-height: initial;}
	a.js-ticket-dash-menu span.js-ticket-dash-menu-icon{display: inline-block;margin-right: 5px;vertical-align: middle;}
	a.js-ticket-dash-menu span.js-ticket-dash-menu-icon img.js-ticket-dash-menu-img {}
	a.js-ticket-dash-menu span.js-ticket-dash-menu-text{display: inline-block;vertical-align: middle;}

	/* Count Box */
	div#js-main-cp-wrapper{display: inline-block; float: left; width: 100%; padding: 15px 15px;}
	div#js-main-head-cp{display: inline-block;float: left;width: calc(25% - 10px);padding: 9px 9px;margin: 0px 5px;}
	div#js-main-head-cp .js-cptext{display: inline-block; float: left; font-size: 25px;}
	div#js-main-head-cp .js-cpmenu{display: inline-block; float: right;}
	.js-ticket-count {float: left;width: 100%;margin-bottom: 20px;padding: 10px;}
	.js-ticket-count div.js-ticket-link {float: left;width: calc(100% / 4);text-align: center;padding: 0 5px;}
	.js-ticket-count a.js-ticket-link {display: inline-block;padding: 15px 0px;text-decoration: none;min-width: 100%;}
	.js-ticket-count .js-ticket-cricle-wrp {float: left;width: 100%;margin-bottom: 10px;}
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp {margin: auto;}
	.js-ticket-count .js-myticket-link-text {float: left;width: 100%;}
	.js-ticket-count .js-ticket-link-text {float: left;width: 100%;}
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp {width: 100px;height: 100px;}
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp .circle .mask {clip: rect(0px, 100px, 100px, 50px);}
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp .circle .mask, 
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp .circle .fill, 
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp .circle .shadow {height: 100px;width: 100px;}
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp .circle .mask .fill {clip: rect(0px, 50px, 100px, 0px);}
	.js-ticket-count .js-ticket-cricle-wrp .js-mr-rp .inset {height: 70px;width: 70px;}

	/* User Links */
	.js-support-ticket-cont {float: left;width: 100%;padding: 20px 10px;margin-bottom: 20px;}
	.js-support-ticket-cont .js-support-ticket-box {float: left;width: calc(100% / 3 - 20px);margin: 0 10px;padding: 30px 20px;min-height: 365px;text-align: center;}
	.js-support-ticket-cont .js-support-ticket-box img {display: inline-block;}
	.js-support-ticket-cont .js-support-ticket-box .js-support-ticket-title {margin: 25px 0 17px;font-size: 20px;line-height: initial;font-weight: bold;}
	.js-support-ticket-cont .js-support-ticket-box .js-support-ticket-desc {font-weight: 400;line-height: initial;}
	.js-support-ticket-cont .js-support-ticket-box .js-support-ticket-btn {display: inline-block;width: 100%;padding: 10px;margin-top: 33px;line-height: initial;text-decoration: none !important;font-weight: 600;}
	
	/* Ticket Data Lists */
	.js-ticket-data-list-wrp {float: left;width: 100%;margin-bottom: 20px;}
	.js-ticket-data-list {float: left;width: 100%;padding: 10px 10px 0;max-height: 350px;overflow-x: hidden;overflow-y: auto;}
	.js-ticket-data-list .js-ticket-data {float: left;width: 100%;padding: 10px;}
	.js-ticket-data-list .js-ticket-data:last-child {border-bottom: 0;}
	.js-ticket-data-list .js-ticket-data .js-ticket-data-image {float: left;}
	.js-ticket-data-list .js-ticket-data .js-ticket-data-tit {float: left;line-height: initial;padding: 15px 10px 5px;text-decoration: none;height: 40px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;width: calc(100% - 260px);}
	.latst-ancmts .js-ticket-data-list .js-ticket-data .js-ticket-data-tit {width: calc(100% - 50px);}
	.latst-kb .js-ticket-data-list .js-ticket-data .js-ticket-data-tit {width: calc(100% - 50px);}
	.latst-faqs .js-ticket-data-list .js-ticket-data .js-ticket-data-tit {width: calc(100% - 50px);}
	.js-ticket-data-list .js-ticket-data .js-ticket-data-btn {float: right;text-decoration: none;padding: 10px 15px;text-align: center;line-height: initial;border-radius: unset;font-weight: normal;}
	
	

	/* Ticket Stats */
	div.js-pm-graphtitle-wrp{float: left;width: 100%;margin-bottom: 20px;}
	div.js-pm-graphtitle{font-size: 20px;float: left; padding: 20px 15px; width: 100%;font-weight: bold;line-height: initial;}
	div#js-pm-grapharea{display: inline-block;float: left; width: 100%;padding-top: 20px;}
	div.js-ticket-latest-ticket-header-txt {float: left;padding: 10px 0;}
	a.js-ticket-latest-ticket-link {float: right;text-decoration: none !important;background: #fff;padding: 10px 15px;}

	/* Latest Tickets */
	div.js-ticket-latest-ticket-wrapper{float: left;width: 100%;margin: 20px 0;}
	div.js-ticket-haeder {float: left;width: 100%;padding: 15px;}
	div.js-ticket-haeder div.js-ticket-header-txt {float: left;padding: 6px 0 0;line-height: initial;font-size: 20px;font-weight: bold;}
	div.js-ticket-haeder a.js-ticket-header-link {float: right;padding: 7px 15px;line-height: initial;text-decoration: none;}
	div.js-ticket-latest-tickets-wrp{float: left;width: 100%;max-height: 350px;overflow-x: hidden;overflow-y: auto;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row{float: left;width: calc(100% - 10px); margin: 0px 5px;padding: 15px 10px;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row .js-ticket-toparea {padding: 0;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left{float: left;width: 50%;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-user-img-wrp{float: left;width: 80px;height: 80px;position: relative;border-radius: 100%;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-user-img-wrp img.js-ticket-staff-img{width: auto;max-width: 100%;max-height: 100%;height: auto;position: absolute;top: 0;left: 0;right: 0;bottom: 0;margin: auto;border-radius: 100%}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-user-img-wrp img{width: auto;max-width: 100%;max-height: 100%;height: auto;position: absolute;top: 0;left: 0;right: 0;bottom: 0;margin: auto;border-radius: 100%}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject{float: left;width: calc(100% - 100px);padding: 5px 0 0 20px;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row {line-height: initial;padding-bottom: 8px;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row:last-child {padding-bottom: 0;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row.name {text-decoration: underline;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row a.js-ticket-data-link {font-weight:500;line-height: initial;text-decoration: underline;display: inline-block;width: 95%;padding-top:5px;height: 32px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-second-left{float: left;width: 20%; text-align: center;padding: 22px 0;line-height: initial;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-second-left span.js-ticket-status {padding: 7px 12px;display: inline-block;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-third-left{float: left;width: 15%;text-align: center;padding: 30px 0;line-height: initial;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-fourth-left{float: left;width: 15%;padding: 23px 0;text-align: center;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-fourth-left span.js-ticket-priorty{text-align: center;padding: 7px 12px;display: inline-block;line-height: initial;text-transform: uppercase;font-weight: bold;}
	span.js-ticket-latest-ticket-heading{display: none;}
	div.js-ticket-zero-padding{padding: 0px !important;}
	
	/*download popup */	
	div#js-ticket-main-black-background{position: fixed;width: 100%;height: 100%;background: rgba(0,0,0,0.7);z-index: 998;top:0px;left:0px;}
	div#js-ticket-main-popup {position: fixed;top: 50%;left: 50%;width: 60%;max-height: 70%;padding-top: 0px;z-index: 99999;overflow-y: auto;overflow-x: hidden;background: #fff;transform: translate(-50%,-50%);}
	span#js-ticket-popup-title {width: 100%;display: inline-block;padding: 20px;font-size: 20px;line-height: initial;text-transform: capitalize;}
	span#js-ticket-popup-close-button{position: absolute;top:18px;right: 18px;width:25px;height: 25px;}
	span#js-ticket-popup-close-button:hover{cursor: pointer;}
	div#js-ticket-main-content {float: left;width: 100%;padding: 0px 25px;}
	div.js-ticket-downloads-content {float: left;width: 100%;padding: 20px 0px;}
	div.js-ticket-download-description {float: left;width: 100%;padding: 0px 0px 15px;line-height: 1.8;}
	div.js-ticket-downloads-content div.js-ticket-download-box {float: left;width: 100%;padding: 10px;margin-bottom: 10px;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left {float: left;width: 80%;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title {float: left;width: 100%;padding: 9px;cursor: pointer;line-height: initial;text-decoration: none;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title img.js-ticket-download-icon {float: left;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {width: calc(100% - 60px); display: inline-block;padding: 10px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
	div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-right {float: left;width: 20%;}
	div#js-ticket-main-downloadallbtn {float: left;width: 100%;padding: 0px 25px 20px;}
	#js-ticket-main-popup div.js-ticket-download-btn {padding: 8px 0;text-align: right;}
	div.js-ticket-download-btn a.js-ticket-download-btn-style {display: inline-block;padding: 15px 20px;border-radius: unset;font-weight: unset;text-decoration: none;outline: 0;line-height: initial;}
	#js-ticket-main-popup #js-ticket-main-downloadallbtn .js-ticket-download-btn {text-align: left;}


';
/*Code For Colors*/
$jssupportticket_css .= '
/*Count Box*/
.js-ticket-brown {color: #d89922;} 
.js-ticket-red {color: #e92d3e;} 
.js-ticket-blue {color: #5ab9ea;} 
.js-ticket-green {color: #14a76c;} 
.js-ticket-orange {color: #ff652f;}
.js-ticket-mariner {color: #2265D8;}
.js-ticket-purple {color: #9922D8;}
.js-ticket-open {background-color: #14a76c;} 
.js-ticket-close {background-color: #e92d3e;}
.js-ticket-answer {background-color: #d89922;}
.js-ticket-overdue {background-color: #ff652f;}
.js-ticket-allticket {background-color: #5ab9ea;}
.js-ticket-count {background: '.$color7.';;border: 1px solid '.$color5.';}
.js-ticket-count a.js-ticket-link {background-color: '.$color7.';border: 1px solid '.$color5.';}
.js-ticket-count a.js-ticket-link:hover {box-shadow: 0 1px 3px 0 rgba(60,64,67,0.302),0 4px 8px 3px rgba(60,64,67,0.149);}
.js-ticket-count a.js-ticket-link.js-ticket-brown.active {border-color: #d89922;}
.js-ticket-count a.js-ticket-link.js-ticket-brown:hover{border-color: #d89922;}
.js-ticket-count a.js-ticket-link.js-ticket-red.active {border-color: #e92d3e;}
.js-ticket-count a.js-ticket-link.js-ticket-red:hover{border-color: #e92d3e;}
.js-ticket-count a.js-ticket-link.js-ticket-blue.active {border-color: #5ab9ea;}
.js-ticket-count a.js-ticket-link.js-ticket-blue:hover{border-color: #5ab9ea;}
.js-ticket-count a.js-ticket-link.js-ticket-green.active {border-color: #14a76c;}
.js-ticket-count a.js-ticket-link.js-ticket-green:hover{border-color: #14a76c;}
.js-ticket-count a.js-ticket-link.js-ticket-orange.active {border-color: #ff652f;}
.js-ticket-count a.js-ticket-link.js-ticket-orange:hover{border-color: #ff652f;}
.js-ticket-count a.js-ticket-link.js-ticket-mariner:hover{border-color: #2265d8;}
.js-ticket-count a.js-ticket-link.js-ticket-purple:hover{border-color: #9922d9;}
.js-ticket-count a.js-ticket-link.js-ticket-mariner .js-report-box-title {color: #2265d8;}
.js-ticket-count a.js-ticket-link.js-ticket-purple .js-report-box-title {color: #9922d9;}
}

	
/*Count Box*/
	
/*download popup */	

div#js-ticket-main-popup {background: #fff !important;}
span#js-ticket-popup-title {background: '.$color1.';color: '.$color7.';}
div.js-ticket-download-description {color: '.$color4.';}
div.js-ticket-downloads-content div.js-ticket-download-box {border: 1px solid '.$color5.';box-shadow: 0 8px 6px -6px #dedddd;}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name {color: '.$color4.';}
div.js-ticket-downloads-content div.js-ticket-download-box div.js-ticket-download-left a.js-ticket-download-title span.js-ticket-download-name:hover {color: '.$color2.';}
div.js-ticket-download-btn a.js-ticket-download-btn-style {color: '.$color1.';border: 1px solid '.$color1.';}
div.js-ticket-download-btn a.js-ticket-download-btn-style:hover {color: '.$color2.';border: 1px solid '.$color2.';}
#js-ticket-main-popup #js-ticket-main-downloadallbtn .js-ticket-download-btn a.js-ticket-download-btn-style {background-color: '.$color1.';color: #ffffff;border-color: '.$color1.';}
#js-ticket-main-popup #js-ticket-main-downloadallbtn .js-ticket-download-btn a.js-ticket-download-btn-style:hover {border-color: '.$color2.';}

/* User Links */
.js-support-ticket-cont {border: 1px solid '.$color5.';}
.js-support-ticket-cont .js-support-ticket-box {background: #f6f6f6;border: 1px solid '.$color5.';box-shadow: 3px solid rgba(0,0,0,0.5);}
.js-support-ticket-cont .js-support-ticket-box .js-support-ticket-title {color: '.$color2.';}
.js-support-ticket-cont .js-support-ticket-box .js-support-ticket-desc {color: '.$color2.';}
.js-support-ticket-cont .js-support-ticket-box .js-support-ticket-btn {color: '.$color7.';background: '.$color2.';border-bottom: 3px solid rgba(0,0,0,0.5);}
.js-support-ticket-cont .js-support-ticket-box .js-support-ticket-btn:hover {background: '.$color1.';}

/* User Links */


/* Ticket Data Lists */
.js-ticket-data-list-wrp {border: 1px solid '.$color5.';}
.js-ticket-data-list .js-ticket-data .js-ticket-data-tit {color: '.$color4.';}
.js-ticket-data-list .js-ticket-data .js-ticket-data-tit:hover {color: '.$color2.';}
.js-ticket-data-list .js-ticket-data {border-bottom: 1px dashed '.$color5.';}
.js-ticket-data-list .js-ticket-data .js-ticket-data-btn {border: 1px solid '.$color1.';background: '.$color3.';color: '.$color1.';}
.js-ticket-data-list .js-ticket-data .js-ticket-data-btn:hover {border-color: '.$color2.';background: #fff;color: '.$color2.';}

/* Ticket Data Lists */

/*Ticket Stats*/
	div.js-pm-graphtitle{border:1px solid '.$color5.';background-color: #ffff;border-bottom:1px solid '.$color5.';color :'.$color2.';}
	a.js-ticket-latest-ticket-link {color: '.$color4.';background: '.$color7.';}
/*Ticket Stats*/
/* Dashboard Menu Links */
	div#js-dash-menu-link-wrp {border:1px solid '.$color5.';}
	div.js-section-heading{border-bottom:1px solid '.$color5.';color :'.$color2.';}
	a.js-ticket-dash-menu{border-bottom:1px solid '.$color5.';}
	a.js-ticket-dash-menu:last-child{border-bottom:0;}
	a.js-ticket-dash-menu span.js-ticket-dash-menu-text{color:'.$color4.';}
	a.js-ticket-dash-menu span.js-ticket-dash-menu-text:hover{color:'.$color2.';}
	
/* Dashboard Menu Links */
/* latest Tickets */
	div.js-ticket-latest-ticket-wrapper{border: 1px solid '.$color5.';}
	div.js-ticket-haeder {background-color: '.$color1.';color: '.$color7.';}
	div.js-ticket-haeder div.js-ticket-header-txt {color: '.$color7.';}
	div.js-ticket-haeder a.js-ticket-header-link {color: '.$color2.';background: '.$color7.';border: 1px solid '.$color5.';}
	div.js-ticket-haeder a.js-ticket-header-link:hover {color: '.$color1.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row{border-bottom:1px dashed '.$color5.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row:last-child{border-bottom:none;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row {color: '.$color4.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row.name {color: '.$color2.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row a.js-ticket-data-link {color: '.$color1.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row a.js-ticket-data-link:hover {color: '.$color2.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left div.js-ticket-ticket-subject div.js-ticket-data-row span.js-ticket-title {color: '.$color2.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-second-left span.js-ticket-status {border: 1px solid '.$color5.';background: #fff;}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-third-left {color: '.$color4.';}
	div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-fourth-left span.js-ticket-priorty{color:'.$color7.';}
/* latest Tickets */

';


wp_add_inline_style('jssupportticket-main-css',$jssupportticket_css);


?>
