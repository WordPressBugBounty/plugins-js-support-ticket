<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
class JSSTreviewbox {

    public function __construct() {
        add_action('admin_notices', array($this, 'jsstadmin_notices'));
    }

    public function jsstadmin_notices() {
        $is_hidden = get_option("jssupportticket_hide_review_box");
        if($is_hidden !== false) {
            return;
        }
        $current_count = get_option("jssupportticket_show_review_box_after");
        if($current_count === false) {
			//jssupportticketphplib::JSST_strtotime not work porperly
            //$date = date("Y-m-d", jssupportticketphplib::JSST_strtotime("+30 days"));
            $date = date("Y-m-d", strtotime("+30 days"));
			add_option("jssupportticket_show_review_box_after", $date);
            return;
        } else if($current_count < 35) {
            return;
        }
        $date_to_show = get_option("jssupportticket_show_review_box_after");
        if($date_to_show !== false) {
            $current_date = date("Y-m-d");
            if($current_date < $date_to_show) {
                return;
            }
        }
        ?>
        <div class="jssupportticket-premio-review-box">
            <div class="js-ticket-review-default" id="default-review-box-jssupportticket">
                <div class="js-ticket-review-default-cnt">
                    <div class="js-ticket-review-default-cnt-left">
                        <img alt="<?php echo esc_attr(__('stars','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/stars-icon.png">
                    </div>
                    <div class="js-ticket-review-default-cnt-right">
                        <div class="js-ticket-review-row review-head">
                            <?php echo esc_html(__("Please write appreciated review at WP Extension Directory",'js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-review-row review-description">
                            <?php echo esc_html(__("We'd love to hear from you. It'll only take 2 minutes of your time, and will really help us spread the word",'js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-review-row">
                            <a data-mode="love" class="jssupportticket-premio-review-box-hide-btn review-love" href="https://wordpress.org/support/plugin/js-support-ticket/reviews/?filter=5" target="_blank">
                                <img alt="<?php echo esc_attr(__('love','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/love.png">
                                <?php echo esc_html(__("I'd love to help :)",'js-support-ticket')); ?>
                            </a>
                            <a class="jssupportticket-premio-review-box-future-btn review-sad" href="javascript:;">
                                <img alt="<?php echo esc_attr(__('sad','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/sad.png">
                                <?php echo esc_html(__("Not this time",'js-support-ticket')); ?>
                            </a>
                            <a data-mode="happy" class="jssupportticket-premio-review-box-hide-btn review-happy" href="javascript:;">
                                <img alt="<?php echo esc_attr(__('happy','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/happy.png">
                                <?php echo esc_html(__("I've already rated you",'js-support-ticket')); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <img  class="js-ticket-review-default-img" alt="<?php echo esc_attr(__('stars','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/star.png">
                <a href="javascript:;" class="dismiss-btn jssupportticket-premio-review-dismiss-btn">
                    <img alt="<?php echo esc_attr(__('close','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/close_icon.png">
                </a>
            </div>
            <div class="js-ticket-review-thanks-box" id="review-thanks-jssupportticket">
                <div class="jssupportticket-thanks-box-popup">
                    <div class="jssupportticket-thanks-box-popup-content">
                        <div class="jssupportticket-thanks-box-popup-content-wrp">
                            <button class="jssupportticket-close-thanks-btn js-ticket-review-thanks-btn">
                                <img alt="<?php echo esc_attr(__('close','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/close_icon.png">
                            </button>
                            <div class="js-ticket-review-thanks-img">
                                <img alt="<?php echo esc_attr(__('thanks','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/thank_you.png">
                            </div>
                            <div class="js-ticket-review-thanks-msg">
                                <div class="thanks-msg-title"><?php echo esc_html(__("You are awesome !......",'js-support-ticket')); ?></div>
                                <div class="thanks-msg-desc"><?php echo esc_html(__("Thanks for your support, we really appreciate it.",'js-support-ticket')); ?></div>
                                <div class="thanks-msg-footer"><?php echo esc_html(__("JS Help Desk Team",'js-support-ticket')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear clearfix"></div>
            </div>
        </div>
        <div class="jssupportticket-review-box-popup">
            <div class="jssupportticket-review-box-popup-content">
                <div class="jssupportticket-review-box-popup-content-wrp">
                    <div class="jssupportticket-review-box-popup-content-left">
                        <img alt="<?php echo esc_attr(__('stars','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/popup-start-icon.png">
                    </div>
                    <div class="jssupportticket-review-box-popup-content-right">
                        <button class="jssupportticket-close-review-box-popup">
                            <img alt="<?php echo esc_attr(__('close','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/review/close_icon.png">
                        </button>
                        <div class="jssupportticket-review-box-title">
                            <?php echo esc_html(__("Remind you about this later?",'js-support-ticket')); ?>
                        </div>
                        <div class="jssupportticket-review-box-options">
                            <a class="three-days" href="javascript:;" data-days="3">
                                <?php echo esc_html(__("Remind me in 3 days",'js-support-ticket')); ?>
                            </a>
                            <a class="seven-days" href="javascript:;" data-days="10">
                                <?php echo esc_html(__("Remind me in 10 days",'js-support-ticket')); ?>
                            </a>
                            <a class="ten-days" href="javascript:;" data-days="30" class="dismiss">
                                <?php echo esc_html(__("Remind me in 30 days",'js-support-ticket')); ?>
                            </a>
                            <a class="zero-days" href="javascript:;" data-days="30" class="dismiss">
                                <?php echo esc_html(__("Don't remind me about this",'js-support-ticket')); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $jssupportticket_js ='
            jQuery(document).ready(function(){
                jQuery("body").addClass("has-premio-box");
                jQuery(document).on("click", ".jssupportticket-premio-review-dismiss-btn, .jssupportticket-premio-review-box-future-btn", function(){
                    jQuery(".jssupportticket-review-box-popup").show();
                });
                jQuery(document).on("click", ".jssupportticket-close-review-box-popup", function(){
                    jQuery(".jssupportticket-review-box-popup").hide();
                });
                jQuery(document).on("click", ".jssupportticket-close-thanks-btn", function(){
                    jQuery(".jssupportticket-review-box-popup").remove();
                    jQuery(".jssupportticket-premio-review-box").remove();
                });
                jQuery(document).on("click",".jssupportticket-premio-review-box-hide-btn",function(){
                    jQuery("#default-review-box-jssupportticket").hide();
                    jQuery("#review-thanks-jssupportticket").show();
                    jQuery(".jssupportticket-thanks-box-popup").show();
                    var dataMode = jQuery(this).attr("data-mode");
                    if (dataMode == "happy") {
                        var dataDays = "-1";
                    }else{
                        var dataDays = "30";
                    }
                    jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "jssupportticket", task: "reviewBoxAction", days:dataDays, "_wpnonce":"'.esc_attr(wp_create_nonce("review-box-action")).'"}, function (data) {
                        
                    });
                });
                jQuery(document).on("click", ".jssupportticket-review-box-options a", function(){
                    var dataDays = jQuery(this).attr("data-days");
                    jQuery(".jssupportticket-review-box-popup").remove();
                    jQuery(".jssupportticket-premio-review-box").remove();
                    jQuery("body").removeClass("has-premio-box");
                    jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "jssupportticket", task: "reviewBoxAction", days:dataDays, "_wpnonce":"'.esc_attr(wp_create_nonce("review-box-action")).'"}, function (data) {
                        if (data) {
                            jQuery(".jssupportticket-review-box-popup").remove();
                            jQuery(".jssupportticket-premio-review-box").remove();
                        }
                    });
                });
            });
        ';
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
    }
}
$JSSTreviewbox = new JSSTreviewbox();