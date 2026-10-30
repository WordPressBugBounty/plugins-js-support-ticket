<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
        JSSTmessage::getMessage();
        /*JSSTbreadcrumbs::getBreadcrumbs();*/
        include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
        <div class="jsst-visitor-message-wrapper" >
            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/jsst-support-icon.png'; ?>" />
            <span class="jsst-visitor-message" >
                <?php echo wp_kses(jssupportticket::$_config['visitor_message'], JSST_ALLOWED_TAGS)?>
            </span>
        </div>
        <?php
        $jsst_token = JSSTRequest::getVar('jssupportticketid');
        /* The short tracking ID is what a customer can keep: with their email
           it opens the ticket on Check Ticket Status, and it is in the email
           they get. The 64-character token is still here, folded away, for
           anyone who wants it. */
        $jsst_tracking = '';
        if (is_string($jsst_token) && $jsst_token !== '') {
            $jsst_tracking = (string) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT ticketid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE token = %s", $jsst_token));
        }
        ?>
        <div class="jsst-visitor-token-message">
            <?php if ($jsst_tracking !== '') { ?>
                <p class="jsst-visitor-token-message-heading"><?php echo esc_html(__('Your tracking ID', 'js-support-ticket')); ?></p>
                <p class="jsst-visitor-token-message-token-number"><?php echo esc_html($jsst_tracking); ?></p>
                <p class="jsst-visitor-token-message-discription"><?php echo esc_html(__('We also sent it to you by email. To see your ticket later, open "Check Ticket Status" and enter your email address and this tracking ID.', 'js-support-ticket')); ?></p>
                <details class="jsst-visitor-token-more">
                    <summary><?php echo esc_html(__('Or use your private ticket link code', 'js-support-ticket')); ?></summary>
            <?php } else { ?>
                <p class="jsst-visitor-token-message-heading"><?php echo esc_html(__('Remember Your Ticket Token for Tracking (Save It!)', 'js-support-ticket')); ?></p>
            <?php } ?>
            <p class="jsst-visitor-token-message-token-number" style="word-break:break-all">
                <?php echo esc_html($jsst_token);?>
                <a title="<?php echo esc_attr(__('Copy Token','js-support-ticket')); ?>" class="mjtc-sprt-det-copy-id" id="ticketidcopybtn" success="<?php echo esc_attr(__('Copied','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Copy Token','js-support-ticket')); ?>
                </a>
            </p>
            <?php if ($jsst_tracking !== '') { ?>
                </details>
            <?php } ?>
        </div>
<?php
echo wp_kses(JSSTformfield::hidden('ticketrandomid', $jsst_token), JSST_ALLOWED_TAGS);
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
$jsst_jssupportticket_js ="
jQuery(document).delegate('#ticketidcopybtn', 'click', function() {
    var temp = jQuery('<input>');
    jQuery('body').append(temp);
    temp.val(jQuery('#ticketrandomid').val()).select();
    document.execCommand('copy');
    temp.remove();
    jQuery('#ticketidcopybtn').text(jQuery('#ticketidcopybtn').attr('success'));
});
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
// new end
?>
</div>
