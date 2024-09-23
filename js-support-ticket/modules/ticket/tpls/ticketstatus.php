<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (JSSTincluder::getObjectClass('user')->uid() != 0 || jssupportticket::$_config['visitor_can_create_ticket'] == 1) {
        JSSTmessage::getMessage();
        ?>
        <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
        <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
        <div class="js-ticket-checkstatus-wrp">
            <form class="js-ticket-form form-validate" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'showticketstatus')),"show-ticket-status")); ?>" method="post" id="adminForm" enctype="multipart/form-data">
                <div class="js-ticket-checkstatus-field-wrp">
                    <div class="js-ticket-field-title">
                        <?php echo esc_html(__('Email','js-support-ticket')); ?>&nbsp;<span style="color: red">*</span>
                    </div>
                    <div class="js-ticket-field-wrp">
                        <input class="inputbox js-ticket-form-input-field required validate-email" data-validation="email" type="text" name="email" id="email" size="40" maxlength="255" value="<?php if (isset(jssupportticket::$_data['0']->email)) echo esc_attr(jssupportticket::$_data['0']->email); ?>" required />
                    </div>
                </div>
                <div class="js-ticket-checkstatus-field-wrp">
                    <div class="js-ticket-field-title">
                        <?php echo esc_html(__('Ticket ID','js-support-ticket')); ?>&nbsp;<span style="color: red">*</span>
                    </div>
                    <div class="js-ticket-field-wrp">
                        <input class="inputbox js-ticket-form-input-field required" type="text" name="ticketid" id="ticketid" size="40" maxlength="255" value=""  required/>
                    </div>
                </div>
                <div class="js-ticket-form-btn-wrp">
                    <input class="tk_dft_btn js-ticket-save-button" type="submit" name="submit_app" value="<?php echo esc_html(__('Check Status','js-support-ticket')); ?>" />
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('checkstatus', 1), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('jsstpageid',get_the_ID()), JSST_ALLOWED_TAGS); ?>
            </form>
        </div>
    <?php
    }else {// User is guest
        $redirect_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketstatus'));
        $redirect_url = jssupportticketphplib::JSST_safe_encoding($redirect_url);
        JSSTlayout::getUserGuest($redirect_url);
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
