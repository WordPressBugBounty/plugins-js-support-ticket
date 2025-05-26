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
            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/jsst-support-icon.png'; ?>" />
            <span class="jsst-visitor-message" >
                <?php echo wp_kses(jssupportticket::$_config['visitor_message'], JSST_ALLOWED_TAGS)?>
            </span>
        </div>
        <div class="jsst-visitor-token-message">
            <p class="jsst-visitor-token-message-heading"><?php echo esc_html(__('Remember Your Ticket Token for Tracking (Save It!)', 'js-support-ticket')); ?></p>
            <p class="jsst-visitor-token-message-discription"><?php echo esc_html(__("You've received a token number to track your support ticket status. This is one-time code, so please save it carefully. ", "js-support-ticket")); ?></p>
            <p class="jsst-visitor-token-message-token-number">
                <?php $token = JSSTRequest::getVar('jssupportticketid');?>
                <?php echo esc_html($token);?>
                <a title="<?php echo esc_attr(__('Copy Token','js-support-ticket')); ?>" class="mjtc-sprt-det-copy-id" id="ticketidcopybtn" success="<?php echo esc_html(__('Copied','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Copy Token','js-support-ticket')); ?>
                </a>
            </p>

        </div>
<?php
echo wp_kses(JSSTformfield::hidden('ticketrandomid', $token), JSST_ALLOWED_TAGS);
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
$jssupportticket_js ="
jQuery(document).delegate('#ticketidcopybtn', 'click', function() {
    var temp = jQuery('<input>');
    jQuery('body').append(temp);
    temp.val(jQuery('#ticketrandomid').val()).select();
    document.execCommand('copy');
    temp.remove();
    jQuery('#ticketidcopybtn').text(jQuery('#ticketidcopybtn').attr('success'));
});
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
// new end
?>
</div>
