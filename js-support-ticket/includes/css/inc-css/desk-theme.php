<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * The desk screens, in the site's own colours. (Roadmap 4.5-FE-02)
 *
 * Notifications, Customers, Live Chat and the workspace Home are styled from
 * `style.css`, which is a static file and therefore cannot know what palette
 * this desk is set to. Everything in it that should have been the brand colour
 * was written as `#4f46e5` - the *default* primary - so a site that had chosen
 * any other one got a green header, green links and green tabs with an indigo
 * search button and an indigo active tab sitting in the middle of them.
 *
 * This file is the part that has to be generated. It is `require`d by the four
 * per-screen `.css.php` files the includer already loads, rather than being one
 * of them, because the rules are identical on all four and a copy per screen is
 * four places to edit a colour.
 *
 * `color1` is the primary - the default is the indigo those literals were - and
 * `color5` and `color7` are the hairline and the colour that reads on top of
 * the primary. They come from `jsst_get_theme_colors()`, which is what every
 * other themed stylesheet in this plugin reads, so a palette change reaches
 * these screens the same way it reaches the rest of the portal.
 *
 * Loaded once a request. Two desk screens never render together, but the
 * include is cheap to reach twice through a shortcode and a widget, and adding
 * the same inline style twice is a duplicate rule in the page source.
 */
if (defined('JSST_DESK_THEME_EMITTED')) {
    return;
}
define('JSST_DESK_THEME_EMITTED', true);

JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();
JSSTincluder::getJSModel('jssupportticket')->jsst_get_theme_colors();

$jsst_primary = jssupportticket::$jsst_colors['color1'];
$jsst_line    = jssupportticket::$jsst_colors['color5'];
$jsst_on      = jssupportticket::$jsst_colors['color7'];

/**
 * The primary at low opacity, for the one place a tint is needed.
 *
 * Worked out here rather than written as a second colour, because an unread
 * notification's ground has to be the brand colour barely present - and a
 * literal would be wrong on every palette but the one it was picked for.
 * Falls back to the colour itself if it is not a hex, which is all
 * `jsst_validate_css_color()` guarantees.
 */
if (!function_exists('jsst_desk_tint')) {
    function jsst_desk_tint($jsst_hex, $jsst_alpha) {
        $jsst_hex = ltrim((string) $jsst_hex, '#');
        if (strlen($jsst_hex) === 3) {
            $jsst_hex = $jsst_hex[0] . $jsst_hex[0] . $jsst_hex[1] . $jsst_hex[1] . $jsst_hex[2] . $jsst_hex[2];
        }
        if (strlen($jsst_hex) !== 6 || !ctype_xdigit($jsst_hex)) {
            return '#' . $jsst_hex;
        }
        return sprintf('rgba(%d, %d, %d, %s)',
            hexdec(substr($jsst_hex, 0, 2)),
            hexdec(substr($jsst_hex, 2, 2)),
            hexdec(substr($jsst_hex, 4, 2)),
            $jsst_alpha);
    }
}

$jsst_tint = jsst_desk_tint($jsst_primary, '.07');

$jsst_desk_css = '
/* The navigation strip. The current tab and the count carry the brand. */
.jsst-main-up-wrapper .jsst-nav {border-color: ' . esc_attr($jsst_line) . ';}
.jsst-main-up-wrapper .jsst-nav-on a {color: ' . esc_attr($jsst_primary) . ';border-bottom-color: ' . esc_attr($jsst_primary) . ';}
.jsst-main-up-wrapper .jsst-nav-count {color: ' . esc_attr($jsst_on) . ';background: ' . esc_attr($jsst_primary) . ';}

/* Panels and fields. */
.jsst-main-up-wrapper .jsst-card,
.jsst-main-up-wrapper .jsst-people-search,
.jsst-main-up-wrapper .jsst-people-companies,
.jsst-main-up-wrapper .jsst-people-history,
.jsst-main-up-wrapper .jsst-people-wrap,
.jsst-main-up-wrapper .jsst-chat-thread,
.jsst-main-up-wrapper .jsst-chat-open,
.jsst-main-up-wrapper .jsst-chat-waiting {border-color: ' . esc_attr($jsst_line) . ';}
.jsst-main-up-wrapper .jsst-field input:focus,
.jsst-main-up-wrapper .jsst-field select:focus,
.jsst-main-up-wrapper .jsst-field textarea:focus,
.jsst-main-up-wrapper .jsst-people-search input[type="text"]:focus {border-color: ' . esc_attr($jsst_primary) . ';}

/* Buttons. `.button` and `.button-primary` are wp-admin class names and the
   portal styles neither, so the Save at the foot of Notifications drew as the
   browser default - a grey system button under a designed form. They are given
   the same shape as every other control out here rather than being renamed,
   because the same markup is what wp-admin renders and styles for itself. */
.jsst-main-up-wrapper .jsst-actions .button,
.jsst-main-up-wrapper .jsst-people-go,
.jsst-main-up-wrapper .jsst-people-clear {display: inline-flex;align-items: center;justify-content: center;
    box-sizing: border-box;height: 44px;margin: 0;padding: 0 20px;
    font-size: 14px;font-weight: 600;line-height: 1;text-decoration: none;cursor: pointer;
    border: 1px solid ' . esc_attr($jsst_line) . ';border-radius: 8px;box-shadow: none;}
.jsst-main-up-wrapper .jsst-actions .button,
.jsst-main-up-wrapper .jsst-people-clear {color: #23282d;background: ' . esc_attr($jsst_on) . ';}
.jsst-main-up-wrapper .jsst-actions .button:hover,
.jsst-main-up-wrapper .jsst-actions .button:focus,
.jsst-main-up-wrapper .jsst-people-clear:hover,
.jsst-main-up-wrapper .jsst-people-clear:focus {color: #23282d;background: #f8fafc;border-color: ' . esc_attr($jsst_primary) . ';}
.jsst-main-up-wrapper .jsst-actions .button-primary,
.jsst-main-up-wrapper .jsst-people-go {color: ' . esc_attr($jsst_on) . ';background: ' . esc_attr($jsst_primary) . ';border-color: ' . esc_attr($jsst_primary) . ';}
/* The hover is the same colour at nine tenths rather than a second literal,
   which is the only darkening that works on a palette nobody has seen. */
.jsst-main-up-wrapper .jsst-actions .button-primary:hover,
.jsst-main-up-wrapper .jsst-actions .button-primary:focus,
.jsst-main-up-wrapper .jsst-people-go:hover,
.jsst-main-up-wrapper .jsst-people-go:focus {color: ' . esc_attr($jsst_on) . ';background: ' . esc_attr($jsst_primary) . ';border-color: ' . esc_attr($jsst_primary) . ';opacity: .9;}
.jsst-main-up-wrapper .jsst-actions .button:focus-visible,
.jsst-main-up-wrapper .jsst-people-go:focus-visible,
.jsst-main-up-wrapper .jsst-people-clear:focus-visible {outline: 2px solid ' . esc_attr($jsst_primary) . ';outline-offset: 2px;}

/* Links and markers that carry the brand. */
.jsst-main-up-wrapper .jsst-card-tools a,
.jsst-main-up-wrapper .jsst-card-tools .button-link,
.jsst-main-up-wrapper .jsst-home-more,
.jsst-main-up-wrapper .jsst-notif-title a:hover,
.jsst-main-up-wrapper .jsst-notif-title a:focus,
.jsst-main-up-wrapper .jsst-home-subject:hover,
.jsst-main-up-wrapper .jsst-home-subject:focus,
.jsst-main-up-wrapper .jsst-people-history a:hover,
.jsst-main-up-wrapper .jsst-people-history a:focus,
.jsst-main-up-wrapper .jsst-people-domain {color: ' . esc_attr($jsst_primary) . ';}
.jsst-main-up-wrapper .jsst-notif-dot,
.jsst-main-up-wrapper .jsst-chat-mine {background: ' . esc_attr($jsst_primary) . ';}
.jsst-main-up-wrapper .jsst-chat-mine {color: ' . esc_attr($jsst_on) . ';}
.jsst-main-up-wrapper .jsst-notif-new {background: ' . esc_attr($jsst_tint) . ';}
.jsst-main-up-wrapper .jsst-pill-info {color: ' . esc_attr($jsst_primary) . ';background: ' . esc_attr($jsst_tint) . ';border-color: ' . esc_attr($jsst_tint) . ';}
.jsst-main-up-wrapper .jsst-people-company-on {border-color: ' . esc_attr($jsst_primary) . ';}

/* The chat console. (Roadmap 6.0-CH-01)
   `console.css` ships with the module and is written for wp-admin, where the
   body is 13px and the panels are the admin greys. Out here it inherits the
   theme size - the transcript rendered at 22px - and paints the thread in
   colours nothing else on the portal uses. What it says about *structure* is
   right and is left alone; only the sizes and the palette are restated. */
.jsst-main-up-wrapper .jsst-chatconsole-thread {font-size: 14px;line-height: 1.6;
    background: #fff;border: 1px solid ' . esc_attr($jsst_line) . ';border-radius: 12px;padding: 14px 16px;}
.jsst-main-up-wrapper .jsst-chatconsole-line {font-size: 14px;line-height: 1.6;margin-bottom: 12px;}
.jsst-main-up-wrapper .jsst-chatconsole-name {font-size: 14px;}
.jsst-main-up-wrapper .jsst-chatconsole-preview,
.jsst-main-up-wrapper .jsst-chatconsole-context {font-size: 13px;line-height: 1.55;}
.jsst-main-up-wrapper .jsst-chatconsole-who {font-size: 11.5px;}
.jsst-main-up-wrapper .jsst-chatconsole-system {font-size: 13px;}
.jsst-main-up-wrapper .jsst-chatconsole-body {font-size: 14px;line-height: 1.6;padding: 8px 12px;
    border: 1px solid ' . esc_attr($jsst_line) . ';border-radius: 10px;}
.jsst-main-up-wrapper .jsst-chatconsole-row {border-bottom-color: ' . esc_attr($jsst_line) . ';}
/* The unread marker, in the site palette rather than the admin indigo
   console.css falls back to. (Roadmap 6.0-CH-01) */
.jsst-main-up-wrapper .jsst-chatconsole-row.jsst-chatconsole-unread {box-shadow: inset 2px 0 0 ' . esc_attr($jsst_primary) . ';}
.jsst-main-up-wrapper .jsst-chatconsole-newflag {color: ' . esc_attr($jsst_on) . ';background: ' . esc_attr($jsst_primary) . ';font-size: 11px;line-height: 1.6;}
[dir="rtl"] .jsst-main-up-wrapper .jsst-chatconsole-row.jsst-chatconsole-unread {box-shadow: inset -2px 0 0 ' . esc_attr($jsst_primary) . ';}
/* The agent\'s own lines carry the brand, so a transcript reads at a glance as
   ours and theirs rather than as two shades of grey. */
.jsst-main-up-wrapper .jsst-chatconsole-agent .jsst-chatconsole-body {color: ' . esc_attr($jsst_on) . ';
    background: ' . esc_attr($jsst_primary) . ';border-color: ' . esc_attr($jsst_primary) . ';}
.jsst-main-up-wrapper .jsst-chatconsole-ai .jsst-chatconsole-body {background: ' . esc_attr($jsst_tint) . ';border-color: ' . esc_attr($jsst_tint) . ';}
.jsst-main-up-wrapper .jsst-chatconsole-ai .jsst-chatconsole-who {color: ' . esc_attr($jsst_primary) . ';}
.jsst-main-up-wrapper .jsst-chatconsole-system .jsst-chatconsole-body {background: none;border: 0;}

[dir="rtl"] .jsst-main-up-wrapper .jsst-chatconsole-agent {text-align: left;}
[dir="rtl"] .jsst-main-up-wrapper .jsst-chatconsole-main {margin-right: 0;margin-left: 12px;}
[dir="rtl"] .jsst-main-up-wrapper .jsst-chat-mine {color: ' . esc_attr($jsst_on) . ';}
';

wp_add_inline_style('jssupportticket-main-css', $jsst_desk_css);
