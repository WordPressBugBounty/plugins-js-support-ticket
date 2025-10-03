<?php
if (!defined('ABSPATH')) {
    die('Restricted Access');
}

/**
 * Validates a CSS color string.
 *
 * Ensures the color is a valid hex, rgb, rgba, hsl, or named color to prevent injection.
 * Returns a safe default if the color is invalid.
 *
 * @param string $color The color string to validate.
 * @return string The sanitized color string, or a default color if invalid.
 */
function jsst_validate_css_color($color) {
    // A simple regex to check for valid color formats.
    // Allows for #, rgb, rgba, hsl, hsla, and color names.
    if (preg_match('/^(\#([a-fA-F0-9]{3}){1,2}|(rgb|rgba|hsl|hsla)\(.*\)|[a-zA-Z]+)$/', $color)) {
        return $color;
    }
    // Return a safe default color if validation fails to prevent errors.
    return '#000000';
}

/**
 * Converts a hex color to an array of RGB values.
 *
 * @param string $hex The hex color string (e.g., #4f46e5).
 * @return array An array with 'r', 'g', 'b' keys.
 */
function jsst_hex_to_rgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (strlen($hex) != 6) {
        return ['r' => 0, 'g' => 0, 'b' => 0]; // Return black for invalid hex
    }
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2)),
    ];
}


/**
 * Generates and enqueues the dynamic theme CSS for JS Support Ticket.
 */

// 1. Define Default Colors
$default_colors = [
    'color1' => '#4f46e5',
    'color2' => '#2b2b2b',
    'color3' => '#f8f8f8',
    'color4' => '#636363',
    'color5' => '#d1d1d1',
    'color6' => '#e7e7e7',
    'color7' => '#ffffff',
    'color8' => '#2DA1CB',
    'color9' => '#000000'
];

// 2. Get Saved Colors from Database
$saved_colors_json = get_option("jsst_set_theme_colors");
$saved_colors = json_decode($saved_colors_json, true);

$colors = is_array($saved_colors) && !empty($saved_colors)
    ? array_merge($default_colors, $saved_colors)
    : $default_colors;

// 3. IMPORTANT: Sanitize ALL values before using them.
// We use our custom validation function for security.
foreach ($colors as $key => $value) {
    $colors[$key] = jsst_validate_css_color($value);
}

// 4. Apply Filters for developer customization
$colors = apply_filters('cm_theme_colors', $colors, 'js-support-ticket');

// 5. CRITICAL: Sanitize ALL values AGAIN after the filter
// This protects against other plugins injecting malicious data.
foreach ($colors as $key => $value) {
    $colors[$key] = jsst_validate_css_color($value);
}

// Make variables available for the CSS string for readability
extract($colors);

// Pre-calculate RGB values for box-shadows to keep CSS clean and safe
$color1_rgb = jsst_hex_to_rgb($color1);
$color2_rgb = jsst_hex_to_rgb($color2);

// Store sanitized colors in the static class property if needed elsewhere
jssupportticket::$_colors = $colors;

// 6. Generate CSS using HEREDOC syntax for better readability
// Note: This is the same CSS you had before.
$dynamic_css = "
/*BreadCrumbs*/
div.js-ticket-flat a:hover, div.js-ticket-flat a.active, div.js-ticket-flat a:hover::after, div.js-ticket-flat a.active::after{background-color:{$color2};}
div.js-ticket-breadcrumb-wrp .breadcrumb li:first-child a{background-color:{$color2};}
div.js-ticket-breadcrumb-wrp .breadcrumb li:first-child a:hover::after{background-color:transparent !important;}
div.js-ticket-breadcrumb-wrp .breadcrumb li:first-child a::after {border-left-color:{$color2};}
div.js-ticket-breadcrumb-wrp .breadcrumb li a::after{border-left-color:#c9c9c9;}
div.js-ticket-breadcrumb-wrp .breadcrumb li a{color:{$color4};}
div.js-ticket-breadcrumb-wrp .breadcrumb li a:hover{color:{$color2};}
/*BreadCrumbs*/

/*Top Header*/
div.jsst-main-up-wrapper {background-color:{$color3};}
div.jsst-main-up-wrapper a{color:{$color1};}
div.jsst-main-up-wrapper a:hover{color:{$color2};}
div#jsst-header{background-color:{$color1};}
a.js-ticket-header-links{color:{$color7};}
a.js-ticket-header-links:hover{color: {$color7};}
div#jsst-header div#jsst-header-heading{color:{$color3};}
div#jsst-header span.jsst-header-tab a.js-cp-menu-link{background:{$color7};color:{$color1};border:1px solid {$color7};}
div#jsst-header span.jsst-header-tab a.js-cp-menu-link:hover{background:{$color1};color:{$color7};}
div#jsst-header span.jsst-header-tab.active a.js-cp-menu-link{background:{$color1};color:{$color7};}
div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a {border: 1px solid {$color7};color: {$color7};background:{$color1};}
div#jsst-header span.jsst-header-tab.js-ticket-loginlogoutclass a.js-cp-menu-link:hover {color: {$color1};background:{$color7};}
div#jsst_breadcrumbs_parent div.home a{background:{$color2};}

/* Error Message Page */
div.js-ticket-messages-data-wrapper span.js-ticket-messages-main-text {color:{$color4};}
div.js-ticket-messages-data-wrapper span.js-ticket-messages-block_text {color:{$color4};}
span.js-ticket-user-login-btn-wrp a.js-ticket-login-btn{background-color:{$color1};color:{$color7};box-shadow: 0 2px 10px rgba({$color1_rgb['r']}, {$color1_rgb['g']}, {$color1_rgb['b']}, 0.4);}
span.js-ticket-user-login-btn-wrp a.js-ticket-login-btn:hover{border-color: {$color2};background-color:{$color2};box-shadow: 0 2px 10px rgba({$color2_rgb['r']}, {$color2_rgb['g']}, {$color2_rgb['b']}, 0.5);}
span.js-ticket-user-login-btn-wrp a.js-ticket-register-btn{background-color:{$color2};color:{$color7};box-shadow: 0 2px 10px rgba({$color2_rgb['r']}, {$color2_rgb['g']}, {$color2_rgb['b']}, 0.5);}
span.js-ticket-user-login-btn-wrp a.js-ticket-register-btn:hover{border-color: {$color1};background-color:{$color1};box-shadow:0 2px 10px rgba({$color1_rgb['r']}, {$color1_rgb['g']}, {$color1_rgb['b']}, 0.4);}
div.jsst_errors span.error{color:#871414;border:1px solid #871414;background-color: #ffd2d3;}
.js-ticket-button:hover { background-color:{$color2}; }
div.jsst-main-up-wrapper input[type='radio'] {appearance: none;-webkit-appearance: none;max-width: 15px;min-width: 15px;max-height: 15px;min-height: 15px;border: 1px solid {$color5};   /* Default border color */border-radius: 50%;position: relative;cursor: pointer;}
div.jsst-main-up-wrapper input[type='radio']:checked {border-color: {$color1};    /* Custom border color when checked */}
div.jsst-main-up-wrapper input[type='radio']:checked::after {content: '';max-width: 70%;max-height: 70%;background:{$color1};      /* Inner dot color */border-radius: 50%;position: absolute;top: 0;left:0;right:0;bottom:0;margin:auto;text-allign:center;}

/* Form Buttons & Popups */
div.js-ticket-form-btn-wrp input.js-ticket-save-button { box-shadow: 0 2px 10px rgba({$color1_rgb['r']}, {$color1_rgb['g']}, {$color1_rgb['b']}, 0.4); }
div#multiformpopup div.jsst-multiformpopup-header{color:{$color2};border-bottom:1px solid {$color5};background-color: {$color3};}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row {border: 1px solid {$color5};background: #f5f5f5;}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row:hover {border: 1px solid {$color1};background: {$color7};}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row div.js-ticket-table-body-col{border-top: 1px solid {$color5};}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row div.js-ticket-table-body-col {color: {$color1};}
#wp-jsticket_message-wrap button, div.js-ticket-fields-wrp div.js-ticket-form-field select.js-ticket-field-input{border: 1px solid {$color5};}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row.selected div.js-ticket-table-body-col {color: {$color2};}
div#multiformpopup .multiformpopup-search form .multiformpopup-fields-wrp .multiformpopup-btn-wrp .multiformpopup-reset-btn{border-color: {$color5};}
.jsst-main-up-wrapper .wp-editor-container {border-color:{$color5} !important;}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row div.js-ticket-table-body-col:first-child{color: {$color2};}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row.selected div.js-ticket-table-body-col:first-child{color: {$color1};}
div#multiformpopup div.js-ticket-table-body div.js-ticket-multiform-row div.js-ticket-table-body-col:last-child{color: #6c757d;}
div#multiformpopup div.js-ticket-table-body div.js-multiformpopup-link-wrp{border-top: 1px solid {$color5};}
div#multiformpopup div.js-ticket-table-body div.js-multiformpopup-link-wrp a.js-multiformpopup-link:hover{background-color: {$color7};color: #1578e8;border: 1px solid #1578e8;}
div#multiformpopup div.js-ticket-table-body div.js-multiformpopup-link-wrp a.js-multiformpopup-link{background-color: {$color1};color: {$color7};border: 1px solid {$color5};}

/* Feedbacks */
div.js-ticket-feedback-heading{border: 1px solid {$color5};background-color: {$color2};color: {$color7};}
div.jsst-feedback-det-wrp div.jsst-feedback-det-list {border:1px solid {$color5};}
div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-top div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top div.jsst-feedback-det-list-data-top-title {color: {$color4};}
div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-top div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top div.jsst-feedback-det-list-data-top-val {color: {$color4};}
div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-top div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-top div.jsst-feedback-det-list-data-top-val a.jsst-feedback-det-list-data-top-val-txt {color: {$color2};}
div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-top div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-btm div.jsst-feedback-det-list-datea-btm-rec div.jsst-feedback-det-list-data-btm-title{color: {$color4};}
div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-top div.jsst-feedback-det-list-data-wrp div.jsst-feedback-det-list-data-btm div.jsst-feedback-det-list-datea-btm-rec div.jsst-feedback-det-list-data-btm-val{color: {$color4};}
div.jsst-feedback-det-wrp div.jsst-feedback-det-list div.jsst-feedback-det-list-btm div.jsst-feedback-det-list-btm-title{color:{$color4};}
input.js-ticket-radio-btn{accent-color:{$color1};}

/* Common Elements */
div.js-ticket-body-data-elipses a{color:{$color2};text-decoration:none;}
div.js-ticket-detail-wrapper div.js-ticket-openclosed{background:{$color6};color:{$color4};border-right:1px solid {$color5};}
div#records div.jsst_userpages a.jsst_userlink:hover{background: {$color2};color:{$color7};}
span.jsst_userlink.selected{background: {$color1};color: {$color7};}


/* Pagination */
div.tablenav div.tablenav-pages{border:1px solid #f1f1fc;width:100%;}
div.tablenav div.tablenav-pages span.page-numbers.current{background: {$color7};color: {$color2};border: 1px solid {$color1};padding:11px 20px;line-height: initial;display: inline-block;}
div.tablenav div.tablenav-pages a.page-numbers:hover{background:{$color7};color:{$color1};border: 1px solid {$color5};text-decoration: none;}
div.tablenav div.tablenav-pages a.page-numbers{background: {$color7}; background: -moz-linear-gradient(top,  {$color7} 0%, #f2f2f2 100%); /* FF3.6+ */background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,{$color7}), color-stop(100%,#f2f2f2)); /* Chrome,Safari4+ */background: -webkit-linear-gradient(top,  {$color7} 0%,#f2f2f2 100%); /* Chrome10+,Safari5.1+ */background: -o-linear-gradient(top,  {$color7} 0%,#f2f2f2 100%); /* Opera 11.10+ */background: -ms-linear-gradient(top,  {$color7} 0%,#f2f2f2 100%); /* IE10+ */background: linear-gradient(to bottom,  {$color7} 0%,#f2f2f2 100%); /* W3C */filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='{$color7}', endColorstr='#f2f2f2',GradientType=0 ); /* IE6-9 */color: {$color4};border:1px solid {$color5};padding:11px 20px;line-height: initial;display: inline-block;}
div.tablenav div.tablenav-pages a.page-numbers.next{background: {$color1};color: {$color7};border: 1px solid {$color1};}
div.tablenav div.tablenav-pages a.page-numbers.prev{background: {$color2};color: {$color7};border: 1px solid {$color2};}

/* Widgets */
div#jsst-widget-myticket-wrapper{background: {$color3};border:1px solid {$color5};}
div#jsst-widget-myticket-wrapper div.jsst-widget-myticket-topbar{border-bottom: 1px solid {$color5};}
div#jsst-widget-myticket-wrapper div.jsst-widget-myticket-topbar span.jsst-widget-myticket-subject a{color:{$color2};}
div#jsst-widget-myticket-wrapper div.jsst-widget-myticket-topbar span.jsst-widget-myticket-status{color:{$color7};}
div#jsst-widget-myticket-wrapper div.jsst-widget-myticket-bottombar span.jsst-widget-myticket-priority{color: {$color7};}
div#jsst-widget-myticket-wrapper div.jsst-widget-myticket-bottombar span.jsst-widget-myticket-from span.widget-from{color:{$color4};}
div#jsst-widget-myticket-wrapper div.jsst-widget-myticket-bottombar span.jsst-widget-myticket-from span.widget-fromname{color:{$color4};}
div#jsst-widget-mailnotification-wrapper{background:{$color3};border:1px solid {$color5};}
div#jsst-widget-mailnotification-wrapper span.jsst-widget-mailnotification-upper{color:{$color4};}
div#jsst-widget-mailnotification-wrapper span.jsst-widget-mailnotification-upper span.jsst-widget-mailnotification-created{color:{$color4};}
div#jsst-widget-mailnotification-wrapper span.jsst-widget-mailnotification-upper span.jsst-widget-mailnotification-new{color:#0752AD;}
div#jsst-widget-mailnotification-wrapper span.jsst-widget-mailnotification-upper span.jsst-widget-mailnotification-replied{color:#ED6B6D;}
div.jsst-visitor-message-wrapper{border:1px solid {$color5};}
div.jsst-visitor-message-wrapper img{border-right:1px solid {$color5}}
div.feedback-sucess-message{border:1px solid {$color5};}
div.feedback-sucess-message span.feedback-message-text{border-top:1px solid {$color5};}
div.js-ticket-thread-wrapper div.js-ticket-thread-upperpart a.ticket-edit-reply-button{border:1px solid {$color2};background:{$color3};color:{$color2};}
div.js-ticket-thread-wrapper div.js-ticket-thread-upperpart a.ticket-edit-time-button{border:1px solid {$color5};background:{$color3};color:{$color4};}
span.js-ticket-value.js-ticket-creade-via-email-spn{border:1px solid {$color5};background:{$color3};color:{$color4};}

/* Ticket Status */
div.js-ticket-checkstatus-wrp p.js-support-tkentckt-centrmainwrp::after{background:{$color1}; }
div.js-ticket-checkstatus-wrp p.js-support-tkentckt-centrmainwrp span.js-support-tkentckt-centrwrp{color:{$color2};}
div.jsst-visitor-token-message p.jsst-visitor-token-message-token-number a{background:{$color1};color:{$color7};}

/* Social Login */
.js-ticket-sociallogin .js-ticket-sociallogin-heading {color: {$color4};}

/* Admin Theme Page */
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn{background-color:{$color1};color:{$color7};}
.js-admin-theme-page div.js-ticket-top-cirlce-count-wrp{border:1px solid {$color5};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper{background-color:{$color3};}
.js-admin-theme-page div.js-ticket-search-wrp .js-ticket-form-wrp form.js-filter-form{border:1px solid {$color5};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn{background-color:{$color2};color:{$color7};border: 1px solid {$color5};}
.js-admin-theme-page div.js-ticket-sorting{background:{$color2};color:{$color7};}
.js-admin-theme-page div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select{background: #fff;color: {$color2};border: 1px solid {$color5};}
.js-admin-theme-page div.jsst-main-up-wrapper .js-ticket-wrapper a {color:{$color1};}
.js-admin-theme-page div.js-ticket-wrapper{border-color:{$color5};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn:hover{border-color:{$color1};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn:hover{border-color:{$color1};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn{border:1px solid {$color5};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn:hover{border-color:{$color2};}
.js-admin-theme-page div.jsst-main-up-wrapper .js-ticket-wrapper a:hover{color:{$color2};}
.js-admin-theme-page div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-tit{color:{$color2};}
.js-admin-theme-page div.js-ticket-wrapper span.js-ticket-wrapper-textcolor{color:{$color7};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn{border:1px solid {$color5};color:{$color4}}
.js-admin-theme-page div.js-ticket-wrapper div.js-ticket-toparea{color:{$color4};}
.js-admin-theme-page div.js-myticket-link a.js-myticket-link{border:1px solid {$color5};}
.js-admin-theme-page div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field{border:1px solid {$color5};}
.js-admin-theme-page div.js-myticket-link a.js-myticket-link.js-ticket-green.active{border-color:#14A76C;}
.js-admin-theme-page div.js-myticket-link a.js-myticket-link.js-ticket-green:hover{border-color:#14A76C;}
.js-admin-theme-page div.js-myticket-link a.js-myticket-link.js-ticket-blue:hover{border-color:#5AB9EA;}
.js-admin-theme-page div.js-myticket-link a.js-myticket-link.js-ticket-red:hover{border-color:#e82d3e;}
.js-admin-theme-page div.js-myticket-link a.js-myticket-link.js-ticket-brown:hover{border-color:#D79922;}

/*Custom Fields & Tables */
input.custom_date{background-color:#fff;border: 1px solid {$color5};}
select.js-ticket-custom-select{background-color:#fcfcfc;border: 1px solid {$color5};}
div.js-ticket-custom-radio-box{background-color:#fff;border: 1px solid {$color5};}
div.js-ticket-radio-box{border: 1px solid {$color5};background-color:#fff;}
.js-ticket-custom-textarea{border: 1px solid {$color5};background-color:{$color7};}
span.js-attachment-file-box{border: 1px solid {$color5};background-color:#fff;}
    .jsst-main-up-wrapper input.custom_date, .js-filter-wrapper input[type='text']{border: 1px solid {$color5};}
    select.js-ticket-select-field, select.js-ticket-premade-select, div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field select{border: 1px solid {$color5};}
div.js-ticket-table-body div.js-ticket-data-row, .js-filter-wrapper select{border:1px solid {$color5};}
    div.js-ticket-premade-msg-wrp div.js-ticket-premade-field-wrp select#staffid{border:1px solid  {$color5};}
div.js-ticket-table-header{background-color:{$color3};border:1px solid {$color5};}
	div.js-ticket-table-header div.js-ticket-table-header-col:last-child{border-right:none;}
	div.js-ticket-downloads-wrp div.js-ticket-downloads-heading-wrp{background-color: {$color2};border:1px solid {$color5};color: {$color7};}

/* JS Support Ticket Woocommerce & Terms */
	.js-ticket-wc-order-box .js-ticket-wc-order-item .js-ticket-wc-order-item-title{ color: {$color1}; }
.js-ticket-wc-order-box .js-ticket-wc-order-link{background-color: {$color2}; color: {$color7}; }
.js-ticket-wc-order-box, div#multiformpopup .multiformpopup-search form .multiformpopup-fields-wrp .multiformpopup-fields input{border: 1px solid {$color5};}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-custom-terms-and-condition-box{border: 1px solid {$color5};background:{$color3};color: {$color4};}
div.js-ticket-add-form-wrapper div.js-ticket-from-field-wrp div.js-ticket-from-field .js-ticket-custom-terms-and-condition-box label {color: {$color4};}
.termsandconditions_link_anchor {color: {$color4};}

/* Misc & Responsive */
div.jsst-main-up-wrapper a.js-ticket-delete-attachment {text-decoration:none}
.js-ticket-recaptcha{background-color:{$color7}; border:1px solid {$color5} !important;color:{$color4};}
@media (max-width: 782px){
    div.js-ticket-wrapper div.js-ticket-data1 {border-top: 1px solid {$color5};}
}
@media (max-width: 650px){
    div.js-ticket-latest-tickets-wrp div.js-ticket-row div.js-ticket-first-left { border-bottom: 0;}
}
";

// Add RTL-specific styles conditionally
if (is_rtl()) {
    $dynamic_css .= "
    div.js-ticket-wrapper div.js-ticket-pic{border:0px;border-left:1px solid {$color5};float:right;}
    div.js-ticket-wrapper div.js-ticket-data1{border-left:0px;border-right:1px solid {$color5};}
    div.js-ticket-detail-wrapper div.js-ticket-topbar div.js-openclosed{float:right;border:0px;border-left: 1px solid {$color5};}
    div.js-ticket-detail-wrapper div.js-ticket-openclosed{border-right:0px;border-left:1px solid {$color5};}
    div.js-ticket-detail-wrapper div.js-ticket-topbar div.js-last-left{border-left:0px;border-right: 1px solid {$color5};}
    div.js-filter-form-head div{border-right:0px; border-left: 1px solid {$color3};}
    div.js-filter-form-data div{border-right:0px; border-left: 1px solid {$color5};}
    div.js-ticket-body-row-button{border-left:0px;border-right: 1px solid {$color5};}
    div.jsst-visitor-message-wrapper img{border-right:none;border-left:1px solid {$color5}}
    /*My Ticket*/
    div.js-ticket-detail-box div.js-ticket-detail-right{border-right: 1px solid {$color5};border-left:unset;}
    /*Roles*/
    div.js-ticket-table-header div.js-ticket-table-header-col{border-right:unset;}
    div.js-ticket-table-body div.js-ticket-data-row div.js-ticket-table-body-col{border-right:unset;}
    /*BreadCrumbs*/
    div.js-ticket-breadcrumb-wrp .breadcrumb li a::after{border-right-color: #c9c9c9 !important;border-left-color: unset;}
    div.js-ticket-breadcrumb-wrp .breadcrumb li:first-child a::after{border-right-color:{$color2} !important;border-left-color: unset;}
    ";
}

// Screen Tag Logic
$screentag_position = isset(jssupportticket::$_config['screentag_position']) ? jssupportticket::$_config['screentag_position'] : 1;

// Default values
$location = 'left';
$borderradius = '0px 8px 8px 0px';
$padding = '5px 10px 5px 20px';
$top = 'auto';
$left = 'auto';
$right = 'auto';
$bottom = 'auto';

switch ($screentag_position) {
    case 1: $top = "30px"; $left = "0px"; break; // Top left
    case 2: // Top right
        $top = "30px"; $right = "0px"; $location = 'right';
        $borderradius = '8px 0px 0px 8px'; $padding = '5px 20px 5px 10px';
        break;
    case 3: $top = "48%"; $left = "0px"; break; // middle left
    case 4: // middle right
        $top = "48%"; $right = "0px"; $location = 'right';
        $borderradius = '8px 0px 0px 8px'; $padding = '5px 20px 5px 10px';
        break;
    case 5: $bottom = "30px"; $left = "0px"; break; // bottom left
    case 6: // bottom right
        $bottom = "30px"; $right = "0px"; $location = 'right';
        $borderradius = '8px 0px 0px 8px'; $padding = '5px 20px 5px 10px';
        break;
}

$dynamic_css .= "
    div#js-ticket_screentag {
        opacity:1;
        position: fixed;
        top: {$top};
        left: {$left};
        right: {$right};
        bottom: {$bottom};
        background: rgba(18, 17, 17, 0.5);
        z-index: 9999;
        border-radius: {$borderradius};
        padding: {$padding};
    }
    div#js-ticket_screentag img.js-ticket_screentag_image{margin-{$location}:10px;display:inline-block;width:40px;height:40px;}
    div#js-ticket_screentag a.js-ticket_screentag_anchor{color:{$color7};text-decoration:none;}
    div#js-ticket_screentag span.text{display:inline-block;font-family:sans-serif;font-size:15px;}
";

// 7. THE SECURE FIX: Use wp_add_inline_style to safely add the generated CSS.
// This assumes your main stylesheet is registered with the handle 'jssupportticket-main-css'.
// If it has a different name (e.g., 'jsticket-style'), change the first parameter here.
wp_add_inline_style('jssupportticket-main-css', $dynamic_css);
