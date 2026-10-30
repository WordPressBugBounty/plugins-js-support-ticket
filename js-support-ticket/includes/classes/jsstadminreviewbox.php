<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
/* The class, not the file. This guard read `JSSTjsstadminreviewbox` - this
   file's basename with the prefix on it - which is not the name of anything,
   so `class_exists()` was false on every route and the early return above was
   unreachable. The declaration below is what has to be protected, and it is
   `JSSTreviewbox`. (Roadmap 4.0-CORE-19) */
/* A bare `if (class_exists(...)) return;` here never let the box run: PHP
   declares a top-level class when the file is compiled, so the check was
   already true on the first include and the constructor below never ran.
   Declared inside the conditional instead, as agent/shotcodes.php does. */
if (!class_exists('JSSTreviewbox')) {
class JSSTreviewbox {

    public function __construct() {
        add_action('admin_notices', array($this, 'jsstadmin_notices'));
    }

    public function jsstadmin_notices() {
        /* Administrators only - saving the choice (reviewBoxAction) needs
           manage_options, so anyone else could never close it - and only on the
           help desk's own screens and the WordPress Dashboard, not all of wp-admin. */
        if (!current_user_can('manage_options')) {
            return;
        }
        $jsst_screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $jsst_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- which screen, nothing is changed
        $jsst_ours = ($jsst_page === 'jssupportticket' || $jsst_page === 'ticket');
        if (!$jsst_ours && !($jsst_screen && $jsst_screen->id === 'dashboard')) {
            return;
        }
        $jsst_is_hidden = get_option("jssupportticket_hide_review_box");
        if($jsst_is_hidden !== false) {
            return;
        }
        $jsst_date_to_show = get_option("jssupportticket_show_review_box_after");
        if($jsst_date_to_show === false) {
			//jssupportticketphplib::JSST_strtotime not work porperly
            //$jsst_date = gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime("+30 days"));
            $jsst_date = gmdate("Y-m-d", strtotime("+30 days"));
			add_option("jssupportticket_show_review_box_after", $jsst_date);
            return;
        }
        /* Both are Y-m-d strings, so comparing them as strings is comparing dates.
           This used to also test the stored date against the number 35, which on
           PHP 8 compares as text ("2026-..." < "35") and hid the box for good. */
        if (gmdate("Y-m-d") < (string) $jsst_date_to_show) {
            return;
        }
        $jsst_star = '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M10 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.6 7.7l5.8-.8z"/></svg>';
        ?>
        <div class="jsst-rv" id="jsst-rv" role="region" aria-label="<?php echo esc_attr(__('Rate JS Help Desk', 'js-support-ticket')); ?>">
            <button type="button" class="jsst-rv-close" data-jsst-rv="later" aria-label="<?php echo esc_attr(__('Close', 'js-support-ticket')); ?>">&times;</button>
            <div class="jsst-rv-icon"><?php echo $jsst_star; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG above ?></div>
            <div class="jsst-rv-body">
                <div class="jsst-rv-ask">
                    <div class="jsst-rv-title"><?php echo esc_html(__('Enjoying JS Help Desk? Please rate us on WordPress.org', 'js-support-ticket')); ?></div>
                    <p class="jsst-rv-text"><?php echo esc_html(__('A short review helps other site owners find the plugin and keeps it growing. It takes about a minute.', 'js-support-ticket')); ?></p>
                    <div class="jsst-rv-actions">
                        <a class="jsst-rv-btn jsst-rv-primary" data-jsst-rv="rate" href="https://wordpress.org/support/plugin/js-support-ticket/reviews/#new-post" target="_blank" rel="noopener noreferrer">
                            <span class="jsst-rv-stars"><?php echo str_repeat($jsst_star, 5); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG above ?></span>
                            <?php echo esc_html(__('Rate JS Help Desk', 'js-support-ticket')); ?>
                        </a>
                        <button type="button" class="jsst-rv-btn" data-jsst-rv="later" aria-expanded="false" aria-controls="jsst-rv-later"><?php echo esc_html(__('Maybe later', 'js-support-ticket')); ?></button>
                        <button type="button" class="jsst-rv-link" data-jsst-rv="done"><?php echo esc_html(__('I already rated it', 'js-support-ticket')); ?></button>
                        <button type="button" class="jsst-rv-link" data-jsst-rv="never"><?php echo esc_html(__("Don't ask again", 'js-support-ticket')); ?></button>
                    </div>
                    <div class="jsst-rv-later" id="jsst-rv-later" hidden>
                        <span><?php echo esc_html(__('Remind me in', 'js-support-ticket')); ?></span>
                        <button type="button" class="jsst-rv-chip" data-days="3"><?php echo esc_html(__('3 days', 'js-support-ticket')); ?></button>
                        <button type="button" class="jsst-rv-chip" data-days="10"><?php echo esc_html(__('10 days', 'js-support-ticket')); ?></button>
                        <button type="button" class="jsst-rv-chip" data-days="30"><?php echo esc_html(__('30 days', 'js-support-ticket')); ?></button>
                    </div>
                </div>
                <div class="jsst-rv-thanks" hidden>
                    <div class="jsst-rv-title"><?php echo esc_html(__('Thank you!', 'js-support-ticket')); ?></div>
                    <p class="jsst-rv-text"><?php echo esc_html(__('Your support really helps. - The JS Help Desk Team', 'js-support-ticket')); ?></p>
                </div>
            </div>
        </div>
        <?php
        /* Rate: back in 30 days, in case the review was not finished. Already
           rated or don't ask again: never. A reminder: the days chosen. */
        $jsst_jssupportticket_js = '
            jQuery(function($){
                var box = $("#jsst-rv");
                function save(days){
                    $.post(ajaxurl, {action: "jsticket_ajax", jstmod: "jssupportticket", task: "reviewBoxAction", days: days, "_wpnonce": "' . esc_attr(wp_create_nonce("review-box-action")) . '"});
                }
                function thanks(){
                    box.find(".jsst-rv-ask").attr("hidden", true);
                    box.find(".jsst-rv-thanks").removeAttr("hidden");
                    setTimeout(function(){ box.fadeOut(300); }, 4000);
                }
                box.on("click", "[data-jsst-rv=rate]", function(){ save(30); thanks(); });
                box.on("click", "[data-jsst-rv=done]", function(){ save(-1); thanks(); });
                box.on("click", "[data-jsst-rv=never]", function(){ save(-1); box.fadeOut(200); });
                box.on("click", "[data-jsst-rv=later]", function(){
                    if (!box.find(".jsst-rv-thanks").is("[hidden]")) { box.fadeOut(200); return; }
                    var later = box.find(".jsst-rv-later");
                    var open = later.is("[hidden]");
                    later.attr("hidden", !open);
                    box.find("[aria-controls=jsst-rv-later]").attr("aria-expanded", open ? "true" : "false");
                    if (open) { later.find(".jsst-rv-chip").first().trigger("focus"); }
                });
                box.on("click", ".jsst-rv-chip", function(){ save($(this).data("days")); box.fadeOut(200); });
            });
        ';
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
    }
}
$JSSTreviewbox = new JSSTreviewbox();
}
