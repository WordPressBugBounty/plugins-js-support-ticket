<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The placeholders a canned response may contain. (Roadmap 4.0-CORE-03)
 *
 * Included by both edit screens. Clicking one copies it, so an agent never has
 * to remember the exact spelling.
 */
$jsst_placeholders = JSSTincluder::getJSModel('cannedresponses')->placeholders();
?>
<?php
/*
 * jsst-placeholders is carried by this block and nothing else, and every style
 * rule for it is qualified with that class, so the appearance of this section is
 * independent of the form it is dropped into and cannot reach any other field.
 * The block is included by the admin screen and by the agent screen, which load
 * different stylesheets, so the rules live in both admincss.css and style.css.
 */
?>
<div class="js-form-wrapper fullwidth jsst-placeholders">
    <div class="js-form-title">
        <?php echo esc_html(__('Placeholders', 'js-support-ticket')); ?>
    </div>
    <div class="js-form-value">
        <p class="jsst-placeholders-intro"><?php echo esc_html(__('Type any of these into the response. Each one is replaced with the real value when an agent inserts the response into a reply. Click to copy.', 'js-support-ticket')); ?></p>
        <ul class="jsst-placeholder-list">
            <?php foreach ($jsst_placeholders AS $jsst_token => $jsst_description) { ?>
                <li>
                    <button type="button" class="jsst-placeholder-token" data-token="<?php echo esc_attr($jsst_token); ?>"><?php echo esc_html($jsst_token); ?></button>
                    <span class="jsst-placeholder-desc"><?php echo esc_html($jsst_description); ?></span>
                </li>
            <?php } ?>
        </ul>
        <span class="jsst-placeholder-copied" role="status" aria-live="polite"></span>
    </div>
</div>
<script>
(function(){
    var copied = document.querySelector('.jsst-placeholder-copied');
    var buttons = document.querySelectorAll('.jsst-placeholder-token');
    var message = <?php echo wp_json_encode(esc_html(__('Copied', 'js-support-ticket')), JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    /* No bare less-than and no bare ampersand below this point, in the code
       or in a comment. On the front end this template is shortcode output,
       which do_blocks renders before wptexturize runs, and wptexturize stops
       protecting a script element at the first bare less-than it meets: the
       rest is texturized as prose, which entity-encodes the ampersands and
       curls the quotes. Compare with !== and nest the conditions instead. */
    for (var i = 0; i !== buttons.length; i++) {
        buttons[i].addEventListener('click', function () {
            var token = this.getAttribute('data-token');
            var done = function () {
                if (copied) {
                    copied.textContent = token + ' — ' + message;
                }
            };
            if (navigator.clipboard) {
                if (navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(token).then(done, done);
                    return;
                }
            }
            // Older browsers: a throwaway field is the only way to reach the
            // clipboard without the async API.
            var field = document.createElement('textarea');
            field.value = token;
            field.setAttribute('readonly', 'readonly');
            field.style.position = 'absolute';
            field.style.left = '-9999px';
            document.body.appendChild(field);
            field.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(field);
            done();
        });
    }
})();
</script>
