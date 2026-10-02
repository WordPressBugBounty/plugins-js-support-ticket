<?php
if (!defined('ABSPATH')) die('Restricted Access');
/**
 * Quick setup: install the translation for this site's language.
 *
 * The controller only shows this screen when jshelpdesk.com has a translation
 * for the site language and it is not installed yet - normally it already is
 * by now (JSSTtranslations), and the step does not appear.
 */
$jsst_wizard_current = 'translation';
$jsst_tran_data = jssupportticket::$jsst_data[0]['jstran'];
?>
<?php /* No side menu here: setup is one focused task, and the breadcrumb is the way out. */ ?>
<div id="jsstadmin-wrapper" class="jsst-wizard-page">
    <div id="jsstadmin-data">
        <?php include __DIR__ . '/admin_wizardnav.php'; ?>
        <div id="jsstadmin-data-wrp">
            <?php $jsst_wizard_part = 'steps'; include __DIR__ . '/admin_wizardnav.php'; ?>
            <form id="jssupportticket-form-ins" class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=postinstallation&task=save&action=jstask'), 'save')); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Language file', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="codelang"><?php echo esc_html(__('Language', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('codelang', is_array($jsst_tran_data) ? $jsst_tran_data['name'] . ' (' . $jsst_tran_data['locale'] . ')' : '', array('readonly' => 'readonly')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Download puts the help desk in this language. It can take a minute.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                            <div id="js-emessage-wrapper" class="notice notice-error inline" hidden><p><?php echo esc_html(__('The language file could not be downloaded. Try again, or skip this step.', 'js-support-ticket')); ?> <span id="js-emessage-reason"></span></p></div>
                            <div id="js-emessage-wrapper_ok" class="notice notice-success inline" hidden><p><?php echo esc_html(__('Language file downloaded.', 'js-support-ticket')); ?></p></div>
                            <p id="jstran_loading" class="jsst-fhelp" role="status" hidden><span class="spinner is-active"></span><?php echo esc_html(__('Downloading the language file...', 'js-support-ticket')); ?></p>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('step', 'translationoption'), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=steptwo')); ?>"><?php echo esc_html(__('Back', 'js-support-ticket')); ?></a>
                        <button type="submit" class="jsst-btn"><?php echo esc_html(__('Skip this step', 'js-support-ticket')); ?></button>
                        <button type="button" class="jsst-btn jsst-btn-primary" id="jsdownloadbutton"><?php echo esc_html(__('Download and continue', 'js-support-ticket')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$jsst_jssupportticket_js = "
    jQuery(document).ready(function(){
        jQuery('#jsdownloadbutton').on('click', function(){
            var button = jQuery(this);
            jQuery('#js-emessage-wrapper, #js-emessage-wrapper_ok').prop('hidden', true);
            button.prop('disabled', true);
            jQuery('#jstran_loading').prop('hidden', false);
            jQuery.post(ajaxurl, {action: 'jsst_translations_install', '_wpnonce': '" . esc_js(wp_create_nonce('jsst-translations')) . "'}, function (result) {
                jQuery('#jstran_loading').prop('hidden', true);
                if (!result || !result.success) {
                    button.prop('disabled', false);
                    jQuery('#js-emessage-reason').text(result && result.data && result.data.message ? result.data.message : '');
                    jQuery('#js-emessage-wrapper').prop('hidden', false);
                    return;
                }
                jQuery('#js-emessage-wrapper_ok').prop('hidden', false);
                document.getElementById('jssupportticket-form-ins').submit();
            }).fail(function () {
                jQuery('#jstran_loading').prop('hidden', true);
                button.prop('disabled', false);
                jQuery('#js-emessage-wrapper').prop('hidden', false);
            });
        });
    });
";
wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
?>
