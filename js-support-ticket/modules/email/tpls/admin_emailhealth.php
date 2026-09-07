<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Email health. (Roadmap 4.0-OPS-01)
 *
 * Every row answers one question an administrator actually asks when a customer
 * says they never got the e-mail, in the order they should be checked: who sends
 * it, what it claims to be from, did the last one work, and is the scheduled
 * work running. Nothing here changes a setting — it reports, and says what to do.
 */
$jsst_health   = isset(jssupportticket::$jsst_data['mailhealth']) ? jssupportticket::$jsst_data['mailhealth'] : array();
$jsst_provider = isset($jsst_health['provider']) ? $jsst_health['provider'] : array('type' => 'phpmail', 'label' => '');
$jsst_align    = isset($jsst_health['alignment']) ? $jsst_health['alignment'] : array('status' => 'ok', 'message' => '');
$jsst_last     = isset($jsst_health['last']) ? $jsst_health['last'] : array();
$jsst_cron     = isset($jsst_health['cron']) ? $jsst_health['cron'] : array('disabled' => false, 'hooks' => array());
$jsst_from     = isset($jsst_health['from']) ? $jsst_health['from'] : array('email' => '', 'name' => '');
$jsst_piping   = isset($jsst_health['piping']) ? $jsst_health['piping'] : array();

JSSTmessage::getMessage();
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
                        <li><?php echo esc_html(__('Email Health','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Email Health', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <?php
            // 1. Who sends the mail.
            $jsst_providerclass = ($jsst_provider['type'] === 'phpmail') ? 'jsst-health-warn' : 'jsst-health-ok';
            ?>
            <div class="jsst-health-card <?php echo esc_attr($jsst_providerclass); ?>">
                <div class="jsst-health-title"><?php echo esc_html(__('Who sends your e-mail', 'js-support-ticket')); ?></div>
                <div class="jsst-health-value"><?php echo esc_html($jsst_provider['label']); ?></div>
                <div class="jsst-health-note">
                    <?php if ($jsst_provider['type'] === 'phpmail') { ?>
                        <?php echo esc_html(__('Nothing on this site is handling e-mail, so WordPress falls back to PHP mail(). Most hosts either block that or send it from an address that receiving servers do not trust, which is why notifications go missing. Installing a dedicated mail plugin and pointing it at a real mail service is the fix.', 'js-support-ticket')); ?>
                        <div class="jsst-health-note">
                            <?php echo esc_html(__('JS Help Desk deliberately does not run its own mail stack. The dedicated plugins do it better and keep up with the providers; this screen simply reports which one is in charge.', 'js-support-ticket')); ?>
                        </div>
                    <?php } elseif ($jsst_provider['type'] === 'filter') { ?>
                        <?php echo esc_html(__('Something is intercepting WordPress mail — a plugin, your theme, or a file your host installed — but it is not one this screen recognises. If mail is arriving, nothing needs doing.', 'js-support-ticket')); ?>
                    <?php } else { ?>
                        <?php echo esc_html(__('Mail leaves this site through that plugin. If messages are not arriving, its own logs will say why.', 'js-support-ticket')); ?>
                    <?php } ?>
                </div>
            </div>

            <?php if (!empty($jsst_health['legacy_smtp'])) { ?>
                <div class="jsst-health-card jsst-health-warn">
                    <div class="jsst-health-title"><?php echo esc_html(__('The SMTP add-on is still active', 'js-support-ticket')); ?></div>
                    <div class="jsst-health-note">
                        <?php echo esc_html(__('It still works and nothing has changed for you. It is no longer being developed, though: a dedicated mail plugin handles more providers and is maintained by people who do only that. Move when it suits you — there is no hurry and no data to migrate.', 'js-support-ticket')); ?>
                    </div>
                </div>
            <?php } ?>

            <?php
            // 2. What it claims to be from.
            $jsst_alignclass = ($jsst_align['status'] === 'ok') ? 'jsst-health-ok' : (($jsst_align['status'] === 'critical') ? 'jsst-health-bad' : 'jsst-health-warn');
            ?>
            <div class="jsst-health-card <?php echo esc_attr($jsst_alignclass); ?>">
                <div class="jsst-health-title"><?php echo esc_html(__('The address your mail is sent from', 'js-support-ticket')); ?></div>
                <div class="jsst-health-value">
                    <?php
                    if ($jsst_from['email'] !== '') {
                        echo esc_html(trim($jsst_from['name'] . ' <' . $jsst_from['email'] . '>'));
                    } else {
                        echo esc_html(__('Not set', 'js-support-ticket'));
                    }
                    ?>
                </div>
                <div class="jsst-health-note"><?php echo esc_html($jsst_align['message']); ?></div>
            </div>

            <?php
            // 3. Did the last one work.
            $jsst_hasresult = !empty($jsst_last) && isset($jsst_last['ok']);
            $jsst_lastclass = !$jsst_hasresult ? '' : (!empty($jsst_last['ok']) ? 'jsst-health-ok' : 'jsst-health-bad');
            ?>
            <div class="jsst-health-card <?php echo esc_attr($jsst_lastclass); ?>">
                <div class="jsst-health-title"><?php echo esc_html(__('The last message this site tried to send', 'js-support-ticket')); ?></div>
                <?php if (!$jsst_hasresult) { ?>
                    <div class="jsst-health-note"><?php echo esc_html(__('Nothing has been sent since this was added, so there is nothing to report yet. Send a test below.', 'js-support-ticket')); ?></div>
                <?php } else { ?>
                    <div class="jsst-health-value">
                        <?php
                        echo esc_html(!empty($jsst_last['ok'])
                                ? __('Accepted by the mail service', 'js-support-ticket')
                                : __('Refused', 'js-support-ticket'));
                        if (!empty($jsst_last['time'])) {
                            echo esc_html(' — ' . date_i18n(jssupportticket::$_config['date_format'] . ' H:i', (int) $jsst_last['time']));
                        }
                        ?>
                    </div>
                    <?php if (!empty($jsst_last['to'])) { ?>
                        <div class="jsst-health-note"><?php echo esc_html(sprintf(
                            /* translators: %s: the recipient address */
                            __('Recipient: %s', 'js-support-ticket'),
                            $jsst_last['to']
                        )); ?></div>
                    <?php } ?>
                    <?php if (!empty($jsst_last['error'])) { ?>
                        <div class="jsst-health-error"><?php echo esc_html($jsst_last['error']); ?></div>
                    <?php } ?>
                    <?php if (!empty($jsst_last['ok'])) { ?>
                        <div class="jsst-health-note"><?php echo esc_html(__('Accepted means your mail service took the message. Whether it reached the inbox is between that service and the recipient — check its logs if the customer still says nothing arrived.', 'js-support-ticket')); ?></div>
                    <?php } ?>
                <?php } ?>
            </div>

            <?php
            // 4. Is the scheduled work running.
            $jsst_cronbad = !empty($jsst_cron['disabled']);
            foreach ($jsst_cron['hooks'] AS $jsst_hook) {
                if (empty($jsst_hook['next']) || !empty($jsst_hook['stalled'])) {
                    $jsst_cronbad = true;
                }
            }
            ?>
            <div class="jsst-health-card <?php echo esc_attr($jsst_cronbad ? 'jsst-health-warn' : 'jsst-health-ok'); ?>">
                <div class="jsst-health-title"><?php echo esc_html(__('Scheduled work', 'js-support-ticket')); ?></div>
                <?php if (!empty($jsst_cron['disabled'])) { ?>
                    <div class="jsst-health-note">
                        <?php echo esc_html(__('WP-Cron is switched off in wp-config.php. That is a perfectly good setup, but only if a real cron job on the server is calling wp-cron.php. If nobody set one up, none of the jobs below ever run — no mail is collected and no ticket is ever marked overdue.', 'js-support-ticket')); ?>
                    </div>
                <?php } ?>
                <table class="jsst-health-table">
                    <?php foreach ($jsst_cron['hooks'] AS $jsst_hook) { ?>
                        <tr>
                            <td><?php echo esc_html($jsst_hook['label']); ?></td>
                            <td>
                                <?php
                                if (empty($jsst_hook['next'])) { ?>
                                    <span class="jsst-health-flag jsst-health-flag-bad"><?php echo esc_html(__('Not scheduled', 'js-support-ticket')); ?></span>
                                <?php } elseif (!empty($jsst_hook['stalled'])) { ?>
                                    <span class="jsst-health-flag jsst-health-flag-bad"><?php echo esc_html(__('Overdue — nothing is running it', 'js-support-ticket')); ?></span>
                                <?php } else { ?>
                                    <span class="jsst-health-flag jsst-health-flag-ok"><?php echo esc_html(sprintf(
                                        /* translators: %s: a date and time */
                                        __('Next run %s', 'js-support-ticket'),
                                        date_i18n(jssupportticket::$_config['date_format'] . ' H:i', (int) $jsst_hook['next'])
                                    )); ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <?php
            /*
             * 5. Mail coming the other way. (Roadmap 4.0-OPS-01)
             *
             * Collection runs unattended and marks the mail read as it goes, so
             * when it goes wrong the evidence deletes itself: the inbox is
             * empty, no ticket exists, and every setting on the piping screen
             * still looks correct. This is the record of what actually happened,
             * and the only card here about mail arriving rather than leaving.
             */
            if (!empty($jsst_piping)) {
                $jsst_pipingstate = isset($jsst_piping['state']) ? $jsst_piping['state'] : 'never';
                $jsst_pipinglast  = isset($jsst_piping['last']) ? $jsst_piping['last'] : array();
                $jsst_pipingclass = ($jsst_pipingstate === 'ok') ? 'jsst-health-ok' : 'jsst-health-warn';
                ?>
                <div class="jsst-health-card <?php echo esc_attr($jsst_pipingclass); ?>">
                    <div class="jsst-health-title"><?php echo esc_html(__('Mail collected from your mailbox', 'js-support-ticket')); ?></div>

                    <?php if ($jsst_pipingstate === 'never') { ?>
                        <div class="jsst-health-value"><?php echo esc_html(__('Collection has never run', 'js-support-ticket')); ?></div>
                        <div class="jsst-health-note">
                            <?php echo esc_html(__('Email piping is switched on but has not collected anything yet. If that does not change within the hour, the scheduled work above is the thing to look at.', 'js-support-ticket')); ?>
                        </div>
                    <?php } else {
                        $jsst_totals = isset($jsst_pipinglast['totals']) ? $jsst_pipinglast['totals'] : array();
                        ?>
                        <div class="jsst-health-value">
                            <?php echo esc_html(sprintf(
                                /* translators: 1: a date and time, 2: what started the run, e.g. Scheduled */
                                __('Last collected %1$s (%2$s)', 'js-support-ticket'),
                                date_i18n(jssupportticket::$_config['date_format'] . ' H:i', (int) $jsst_pipinglast['started']),
                                JSSTpipinglog::triggerLabel($jsst_pipinglast['trigger'])
                            )); ?>
                        </div>
                        <?php if ($jsst_pipingstate === 'incomplete') { ?>
                            <div class="jsst-health-note">
                                <?php echo esc_html(__('That run started and never finished. Something ended the request while the mailbox was open — a PHP error or a timeout — so some of the mail in it may have been marked read without becoming a ticket.', 'js-support-ticket')); ?>
                            </div>
                        <?php } elseif ($jsst_pipingstate === 'failed') { ?>
                            <div class="jsst-health-note">
                                <?php echo esc_html(__('A mailbox could not be read. The reason the mail server gave is below — a wrong password and a blocked port both end up here, saying different things.', 'js-support-ticket')); ?>
                            </div>
                        <?php } ?>

                        <?php if (!empty($jsst_pipinglast['mailboxes'])) { ?>
                            <table class="jsst-health-table">
                                <?php foreach ($jsst_pipinglast['mailboxes'] AS $jsst_box) { ?>
                                    <tr>
                                        <td><?php echo esc_html($jsst_box['address']); ?></td>
                                        <td>
                                            <?php if ($jsst_box['state'] === 'failed') { ?>
                                                <span class="jsst-health-flag jsst-health-flag-bad"><?php echo esc_html(__('Could not be read', 'js-support-ticket')); ?></span>
                                            <?php } elseif ($jsst_box['state'] === 'skipped') { ?>
                                                <span class="jsst-health-flag"><?php echo esc_html(__('Not collected', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <span class="jsst-health-flag jsst-health-flag-ok"><?php echo esc_html(
                                                    /* translators: %d: number of e-mail messages read from the mailbox. */
                                                    sprintf(_n('%d message', '%d messages', (int) $jsst_box['messages'], 'js-support-ticket'), (int) $jsst_box['messages'])
                                                    . ' — '
                                                    /* translators: %d: number of tickets created from the mail read. */
                                                    . sprintf(_n('%d ticket', '%d tickets', (int) $jsst_box['tickets'], 'js-support-ticket'), (int) $jsst_box['tickets'])
                                                    . ', '
                                                    /* translators: %d: number of replies added to existing tickets. */
                                                    . sprintf(_n('%d reply', '%d replies', (int) $jsst_box['replies'], 'js-support-ticket'), (int) $jsst_box['replies'])
                                                ); ?></span>
                                                <?php
                                                // The number that explains a
                                                // complaint: mail that was read
                                                // and became nothing.
                                                if (!empty($jsst_box['rejected'])) { ?>
                                                    <span class="jsst-health-flag jsst-health-flag-bad"><?php echo esc_html(sprintf(
                                                        /* translators: %d: how many messages produced neither a ticket nor a reply */
                                                        _n('%d produced nothing', '%d produced nothing', (int) $jsst_box['rejected'], 'js-support-ticket'),
                                                        (int) $jsst_box['rejected']
                                                    )); ?></span>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if (!empty($jsst_box['reason'])) { ?>
                                                <div class="jsst-health-note"><?php echo esc_html($jsst_box['reason']); ?></div>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } ?>

                        <?php
                        /* The lines that say why a message produced nothing.
                           A message the plugin read and deliberately dropped is
                           indistinguishable, from the outside, from one it never
                           received — this is the difference. */
                        if (!empty($jsst_pipinglast['events'])) {
                            // A run that dropped forty messages has one problem,
                            // not forty. Show enough to recognise it and say how
                            // many more there were.
                            $jsst_eventshown = array_slice($jsst_pipinglast['events'], 0, 10);
                            $jsst_eventmore = count($jsst_pipinglast['events']) - count($jsst_eventshown);
                            ?>
                            <div class="jsst-health-note"><?php echo esc_html(__('What the last run found:', 'js-support-ticket')); ?></div>
                            <?php foreach ($jsst_eventshown AS $jsst_event) { ?>
                                <?php if ($jsst_event['level'] === 'error') { ?>
                                    <div class="jsst-health-error"><?php echo esc_html($jsst_event['message']); ?></div>
                                <?php } else { ?>
                                    <div class="jsst-health-note"><?php echo esc_html($jsst_event['message']); ?></div>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($jsst_eventmore > 0) { ?>
                                <div class="jsst-health-note"><?php echo esc_html(sprintf(
                                    /* translators: %d: how many further lines were recorded */
                                    _n('and %d more like it.', 'and %d more like these.', $jsst_eventmore, 'js-support-ticket'),
                                    $jsst_eventmore
                                )); ?></div>
                            <?php } ?>
                        <?php } ?>

                        <?php
                        // The runs before this one, so a fault that comes and
                        // goes can be told apart from one that has always been
                        // there.
                        if (count($jsst_piping['runs']) > 1) { ?>
                            <div class="jsst-health-note"><?php echo esc_html(__('Earlier runs:', 'js-support-ticket')); ?></div>
                            <table class="jsst-health-table">
                                <?php foreach (array_slice($jsst_piping['runs'], 1) AS $jsst_run) {
                                    $jsst_runtotals = isset($jsst_run['totals']) ? $jsst_run['totals'] : array(); ?>
                                    <tr>
                                        <td><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'] . ' H:i', (int) $jsst_run['started'])); ?></td>
                                        <td><?php echo esc_html(JSSTpipinglog::triggerLabel($jsst_run['trigger'])); ?></td>
                                        <td>
                                            <?php if (empty($jsst_run['finished'])) { ?>
                                                <span class="jsst-health-flag jsst-health-flag-bad"><?php echo esc_html(__('Did not finish', 'js-support-ticket')); ?></span>
                                            <?php } elseif (!empty($jsst_runtotals['errors'])) { ?>
                                                <span class="jsst-health-flag jsst-health-flag-bad"><?php echo esc_html(__('A mailbox could not be read', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <?php
                                                $jsst_runmessages = isset($jsst_runtotals['messages']) ? (int) $jsst_runtotals['messages'] : 0;
                                                $jsst_runtickets  = isset($jsst_runtotals['tickets']) ? (int) $jsst_runtotals['tickets'] : 0;
                                                $jsst_runreplies  = isset($jsst_runtotals['replies']) ? (int) $jsst_runtotals['replies'] : 0;
                                                echo esc_html(
                                                    /* translators: %d: number of e-mail messages read from the mailbox. */
                                                    sprintf(_n('%d message', '%d messages', $jsst_runmessages, 'js-support-ticket'), $jsst_runmessages)
                                                    . ' — '
                                                    /* translators: %d: number of tickets created from the mail read. */
                                                    . sprintf(_n('%d ticket', '%d tickets', $jsst_runtickets, 'js-support-ticket'), $jsst_runtickets)
                                                    . ', '
                                                    /* translators: %d: number of replies added to existing tickets. */
                                                    . sprintf(_n('%d reply', '%d replies', $jsst_runreplies, 'js-support-ticket'), $jsst_runreplies)
                                                ); ?>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } ?>
                    <?php } ?>

                    <div class="jsst-health-note">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=emailpiping')); ?>"><?php echo esc_html(__('Mailbox settings', 'js-support-ticket')); ?></a>
                        <?php if (class_exists('JSSTdocs') && JSSTdocs::exists('email-piping')) { ?>
                            &nbsp;·&nbsp;
                            <a href="<?php echo esc_url(JSSTdocs::url('email-piping')); ?>"><?php echo esc_html(__('Mail is not becoming tickets', 'js-support-ticket')); ?></a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <?php // 6. Prove it. ?>
            <div class="jsst-health-card">
                <div class="jsst-health-title"><?php echo esc_html(__('Send a test message', 'js-support-ticket')); ?></div>
                <div class="jsst-health-note"><?php echo esc_html(__('This goes out the same way a real notification does, so whatever handles your mail handles this too. Send it to an address on a different provider from your own — that is where delivery problems show up.', 'js-support-ticket')); ?></div>
                <form class="jsst-health-testform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=email&task=sendtestmail&action=jstask'), 'jsst-mail-health-test')); ?>">
                    <label class="screen-reader-text" for="jsst-health-testemail"><?php echo esc_html(__('Send the test to', 'js-support-ticket')); ?></label>
                    <input type="email" class="inputbox" id="jsst-health-testemail" name="testemail" required
                           value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>"
                           placeholder="<?php echo esc_attr(__('you@example.com', 'js-support-ticket')); ?>" />
                    <?php echo wp_kses(JSSTformfield::submitbutton('sendtest', esc_html(__('Send test', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                </form>
            </div>

        </div>
    </div>
</div>
