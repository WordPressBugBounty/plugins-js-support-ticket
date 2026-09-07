<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Setting up the AI Copilot. (Roadmap 4.0-AI-01)
 *
 * One screen, and most of it is not settings. The settings are a key and a
 * model; the rest is the two things somebody deciding whether to switch this on
 * actually needs — exactly what leaves the site and when, and a record of every
 * time it has.
 */
if (!class_exists('JSSTcopilot') || !class_exists('JSSTcopilotprovider')) {
    echo '<div class="notice notice-error"><p>' . esc_html(__('The AI Copilot could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
JSSTmessage::getMessage();

$jsst_providers  = jssupportticket::$jsst_data['providers'];
$jsst_provider   = jssupportticket::$jsst_data['provider'];
$jsst_configured = jssupportticket::$jsst_data['configured'];
$jsst_model      = jssupportticket::$jsst_data['model'];
$jsst_actions    = jssupportticket::$jsst_data['actions'];
$jsst_runs       = jssupportticket::$jsst_data['runs'];
$jsst_usage      = jssupportticket::$jsst_data['usage'];
$jsst_dateformat = jssupportticket::$_config['date_format'] . ' H:i';
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('AI Copilot','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('AI Copilot', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-status-card <?php echo esc_attr($jsst_configured ? 'jsst-status-ok' : ''); ?>">
                <div class="jsst-status-title"><?php echo esc_html($jsst_configured
                    ? __('The Copilot is on', 'js-support-ticket')
                    : __('Four things an agent can ask for', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('Summarize a ticket, read it in another language, pull out the details, or draft a reply — each one a button an agent presses while reading a ticket. Nothing runs on its own: there is no automatic summarising, no reply is ever sent, and no ticket is sent anywhere unless somebody asks for it.', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('It uses your own key with your own AI provider. This plugin does not resell, proxy or meter anything — requests go from this server straight to the provider you pick, and the bill is theirs to you.', 'js-support-ticket')); ?></div>
                <table class="jsst-status-table">
                    <?php foreach ($jsst_actions AS $jsst_key => $jsst_action) { ?>
                        <tr>
                            <td><strong><?php echo esc_html($jsst_action['label']); ?></strong></td>
                            <td><?php echo esc_html($jsst_action['title']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('Your provider and key', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('The key is stored on this site and sent only to the provider below. It is never shown again after saving, never written to the log, and is removed from the diagnostic debug file.', 'js-support-ticket')); ?></div>
                <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=copilot&task=savecopilotsettings'), 'jsst-copilot-settings')); ?>">
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <table class="jsst-status-table">
                        <tr>
                            <td><?php echo esc_html(__('Provider', 'js-support-ticket')); ?></td>
                            <td>
                                <select name="copilotprovider">
                                    <?php foreach ($jsst_providers AS $jsst_pkey => $jsst_pdef) { ?>
                                        <option value="<?php echo esc_attr($jsst_pkey); ?>" <?php selected($jsst_pkey, $jsst_provider['key']); ?>><?php echo esc_html($jsst_pdef['label']); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(__('Model', 'js-support-ticket')); ?></td>
                            <td>
                                <select name="copilotmodel">
                                    <?php foreach ($jsst_provider['def']['models'] AS $jsst_mkey => $jsst_mlabel) { ?>
                                        <option value="<?php echo esc_attr($jsst_mkey); ?>" <?php selected($jsst_mkey, $jsst_model); ?>><?php echo esc_html($jsst_mlabel); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(__('API key', 'js-support-ticket')); ?></td>
                            <td>
                                <input type="password" name="copilotkey" autocomplete="new-password" size="44"
                                       placeholder="<?php echo esc_attr($jsst_configured ? __('A key is saved. Type a new one to replace it.', 'js-support-ticket') : $jsst_provider['def']['keyhint']); ?>" />
                                <?php if ($jsst_configured) { ?>
                                    <label class="jsst-migration-confirm">
                                        <input type="checkbox" name="copilotclearkey" value="1" />
                                        <?php echo esc_html(__('Remove the saved key and switch the Copilot off.', 'js-support-ticket')); ?>
                                    </label>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(__('Translate into', 'js-support-ticket')); ?></td>
                            <td>
                                <input type="text" name="copilotlanguage" size="24"
                                       value="<?php echo esc_attr(JSSTcopilot::defaultLanguage()); ?>" />
                                <span class="jsst-status-correlation"><?php echo esc_html(__('The language the Translate button uses by default.', 'js-support-ticket')); ?></span>
                            </td>
                        </tr>
                    </table>
                    <div class="jsst-migration-actions">
                        <?php echo wp_kses(JSSTformfield::submitbutton('savecopilot', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                        <?php if ($jsst_configured) { ?>
                            <a class="button js-form-cancel" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=copilot&task=testcopilot&action=jstask'), 'jsst-copilot-test')); ?>"><?php echo esc_html(__('Test connection', 'js-support-ticket')); ?></a>
                            <span class="jsst-status-correlation"><?php echo esc_html(__('Sends one short question. No ticket content.', 'js-support-ticket')); ?></span>
                        <?php } ?>
                    </div>
                </form>
            </div>

            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('What leaves this site', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('When an agent presses one of the four buttons, the ticket subject, the first message and the replies on that ticket are sent to your provider as plain text. Attachments are not sent. Customer names, email addresses and any other field are not sent. Long threads are trimmed before sending.', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('Whether that is acceptable is a question about your provider and your customers, not about this plugin — check what your provider does with what you send it, and say so in your privacy notice if you turn this on.', 'js-support-ticket')); ?></div>
            </div>

            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('Recent runs', 'js-support-ticket')); ?></div>
                <?php if (empty($jsst_runs)) { ?>
                    <div class="jsst-status-note"><?php echo esc_html(__('Nothing has been sent yet.', 'js-support-ticket')); ?></div>
                <?php } else { ?>
                    <div class="jsst-status-note"><?php echo esc_html(sprintf(
                        /* translators: 1: number of runs, 2: input tokens, 3: output tokens */
                        __('The last %1$s runs used %2$s tokens in and %3$s out. Every run an agent has asked for is here, including the ones that failed.', 'js-support-ticket'),
                        number_format_i18n((int) $jsst_usage['runs']),
                        number_format_i18n((int) $jsst_usage['intokens']),
                        number_format_i18n((int) $jsst_usage['outtokens'])
                    )); ?></div>
                    <table class="jsst-status-table">
                        <tr>
                            <td><strong><?php echo esc_html(__('When', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('Action', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('Ticket', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('By', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('Result', 'js-support-ticket')); ?></strong></td>
                        </tr>
                        <?php foreach ($jsst_runs AS $jsst_run) {
                            $jsst_who = get_userdata((int) $jsst_run['user']); ?>
                            <tr>
                                <td class="jsst-status-when"><?php echo esc_html(date_i18n($jsst_dateformat, (int) $jsst_run['when'])); ?></td>
                                <td><?php echo esc_html(isset($jsst_actions[$jsst_run['action']]) ? $jsst_actions[$jsst_run['action']]['label'] : $jsst_run['action']); ?></td>
                                <td><a href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_run['ticket'])); ?>">#<?php echo esc_html((int) $jsst_run['ticket']); ?></a></td>
                                <td><?php echo esc_html($jsst_who ? $jsst_who->display_name : __('unknown', 'js-support-ticket')); ?></td>
                                <td><?php if (!empty($jsst_run['ok'])) { ?>
                                        <span class="jsst-status-flag jsst-status-flag-ok"><?php echo esc_html(sprintf(
                                            /* translators: %s: number of tokens the answer used */
                                            __('%s tokens', 'js-support-ticket'),
                                            number_format_i18n((int) $jsst_run['intokens'] + (int) $jsst_run['outtokens'])
                                        )); ?></span>
                                    <?php } else { ?>
                                        <span class="jsst-status-flag jsst-status-flag-bad"><?php echo esc_html($jsst_run['error']); ?></span>
                                    <?php } ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            </div>

        </div>
    </div>
</div>
