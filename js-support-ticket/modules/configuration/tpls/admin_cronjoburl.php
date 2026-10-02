<?php
if(!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The two addresses a scheduler has to call. (Roadmap 6.5-UI-09)
 *
 * This screen was two copies of the same 120-line block, one per job, and both
 * copies used the same DOM ids - `id="tabs"`, `id="webcrown"`, `id="wget"`,
 * `id="curl"`, `id="phpscript"`, `id="url"`, and `id="cron_job"` eleven times
 * over. jQuery UI resolves a tab's panel by id, so the second set of tabs
 * drove the first set's panels: clicking "Curl" under Update Ticket Status
 * switched the block above it and left the block you were reading alone. One
 * loop over the two jobs, ids derived from the job key, and the bug cannot
 * come back by copy and paste.
 *
 * It also led with the thing nobody needs. The answer to "how do I set this
 * up" is the URL; wget, curl and a PHP snippet are three ways of fetching a
 * URL you already have. The URL is on the card with a copy button, and the
 * three recipes are folded underneath it.
 */
JSSTmessage::getMessage();

$jsst_jobs = array(
    'ticketviaemail' => array(
        'name'  => __('Ticket via email', 'js-support-ticket'),
        'what'  => __('Reads the mailbox and turns new messages into tickets, and replies into replies.', 'js-support-ticket'),
        'often' => __('Hourly', 'js-support-ticket'),
    ),
    'updateticketstatus' => array(
        'name'  => __('Update ticket status', 'js-support-ticket'),
        'what'  => __('Closes what has gone quiet and moves on anything whose promised time has passed.', 'js-support-ticket'),
        'often' => __('Daily', 'js-support-ticket'),
    ),
);
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Cron Job URLs', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <p class="jsst-lede"><?php echo esc_html(__('Two jobs run on a timer. Give each address below to whatever runs things on a schedule — your host\'s control panel, a cron line on the server, or a web cron service. Fetching the address is all a scheduler has to do.', 'js-support-ticket')); ?></p>

            <?php foreach ($jsst_jobs AS $jsst_key => $jsst_job) {
                $jsst_url = jssupportticket::makeUrl(array('jsstcron' => $jsst_key, 'jsstpageid' => jssupportticket::getPageid()));
                ?>
                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html($jsst_job['name']); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html($jsst_job['what']); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <div class="jsst-formgrid">
                            <div class="jsst-frow jsst-frow-full">
                                <span class="jsst-flabel"><?php echo esc_html(__('The address to call', 'js-support-ticket')); ?></span>
                                <div class="jsst-sc-code">
                                    <code><?php echo esc_html($jsst_url); ?></code>
                                    <button type="button" class="button-link jsst-sc-copy" data-jsst-copy="<?php echo esc_attr($jsst_url); ?>"><?php echo esc_html(__('Copy', 'js-support-ticket')); ?></button>
                                </div>
                                <p class="jsst-fhelp"><?php
                                    /* translators: %s: how often the job should run, e.g. "Hourly" */
                                    echo esc_html(sprintf(__('Run it: %s. No login, no password — leave those blank wherever you are asked for them.', 'js-support-ticket'), $jsst_job['often'])); ?></p>
                            </div>
                        </div>

                        <details class="jsst-details">
                            <summary><?php echo esc_html(__('Ways to call it', 'js-support-ticket')); ?></summary>
                            <div class="jsst-details-body">
                                <p class="jsst-flabel"><?php echo esc_html(__('wget', 'js-support-ticket')); ?></p>
                                <div class="jsst-sc-code">
                                    <code><?php echo esc_html('wget --max-redirect=10000 "' . $jsst_url . '" -O - 1>/dev/null 2>/dev/null'); ?></code>
                                    <button type="button" class="button-link jsst-sc-copy" data-jsst-copy="<?php echo esc_attr('wget --max-redirect=10000 "' . $jsst_url . '" -O - 1>/dev/null 2>/dev/null'); ?>"><?php echo esc_html(__('Copy', 'js-support-ticket')); ?></button>
                                </div>

                                <p class="jsst-flabel"><?php echo esc_html(__('curl', 'js-support-ticket')); ?></p>
                                <div class="jsst-sc-code">
                                    <code><?php echo esc_html('curl -L --max-redirs 1000 "' . $jsst_url . '" 1>/dev/null 2>/dev/null'); ?></code>
                                    <button type="button" class="button-link jsst-sc-copy" data-jsst-copy="<?php echo esc_attr('curl -L --max-redirs 1000 "' . $jsst_url . '" 1>/dev/null 2>/dev/null'); ?>"><?php echo esc_html(__('Copy', 'js-support-ticket')); ?></button>
                                </div>

                                <p class="jsst-flabel"><?php echo esc_html(__('A PHP script of your own', 'js-support-ticket')); ?></p>
                                <?php
                                $jsst_php = "\$handle = curl_init();\n"
                                    . "curl_setopt(\$handle, CURLOPT_URL, '" . $jsst_url . "');\n"
                                    . "curl_setopt(\$handle, CURLOPT_FOLLOWLOCATION, true);\n"
                                    . "curl_setopt(\$handle, CURLOPT_MAXREDIRS, 10000);\n"
                                    . "curl_setopt(\$handle, CURLOPT_RETURNTRANSFER, 1);\n"
                                    . "\$buffer = curl_exec(\$handle);\n"
                                    . "curl_close(\$handle);\n"
                                    . "echo empty(\$buffer) ? '" . esc_js(__('The cron job did not run', 'js-support-ticket')) . "' : \$buffer;";
                                ?>
                                <div class="jsst-sc-code">
                                    <code class="jsst-codeblock"><?php echo esc_html($jsst_php); ?></code>
                                    <button type="button" class="button-link jsst-sc-copy" data-jsst-copy="<?php echo esc_attr($jsst_php); ?>"><?php echo esc_html(__('Copy', 'js-support-ticket')); ?></button>
                                </div>

                                <p class="jsst-fhelp"><?php echo esc_html(__('Using a web cron service instead? Give it the address above, set the timeout to 180 seconds or more, and leave the login and password empty.', 'js-support-ticket')); ?></p>
                            </div>
                        </details>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
<?php /* Copy, without jQuery and without a library - the same delegated
         listener the Shortcodes screen uses, and for the same reason:
         `navigator.clipboard` is refused outside a secure context, and plenty
         of these desks are administered over plain http on a local network. */ ?>
<script>
(function () {
    function jsstFlash(jsst_button, jsst_text) {
        var jsst_was = jsst_button.getAttribute('data-jsst-was') || jsst_button.textContent;
        jsst_button.setAttribute('data-jsst-was', jsst_was);
        jsst_button.textContent = jsst_text;
        jsst_button.classList.add('jsst-sc-copied');
        window.setTimeout(function () {
            jsst_button.textContent = jsst_was;
            jsst_button.classList.remove('jsst-sc-copied');
        }, 1600);
    }
    function jsstFallback(jsst_button) {
        var jsst_code = jsst_button.parentNode ? jsst_button.parentNode.querySelector('code') : null;
        if (!jsst_code) { return false; }
        var jsst_range = document.createRange();
        jsst_range.selectNodeContents(jsst_code);
        var jsst_selection = window.getSelection();
        jsst_selection.removeAllRanges();
        jsst_selection.addRange(jsst_range);
        var jsst_done = false;
        try { jsst_done = document.execCommand('copy'); } catch (jsst_e) { jsst_done = false; }
        if (jsst_done) { jsst_selection.removeAllRanges(); }
        return jsst_done;
    }
    document.addEventListener('click', function (jsst_event) {
        var jsst_button = jsst_event.target.closest ? jsst_event.target.closest('.jsst-sc-copy') : null;
        if (!jsst_button) { return; }
        jsst_event.preventDefault();
        var jsst_value = jsst_button.getAttribute('data-jsst-copy') || '';
        var jsst_ok = <?php echo wp_json_encode(__('Copied', 'js-support-ticket')); ?>;
        var jsst_no = <?php echo wp_json_encode(__('Press Ctrl+C', 'js-support-ticket')); ?>;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(jsst_value).then(function () {
                jsstFlash(jsst_button, jsst_ok);
            }, function () {
                jsstFlash(jsst_button, jsstFallback(jsst_button) ? jsst_ok : jsst_no);
            });
            return;
        }
        jsstFlash(jsst_button, jsstFallback(jsst_button) ? jsst_ok : jsst_no);
    });
})();
</script>
