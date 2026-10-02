<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The notification centre. (Roadmap 4.5-UX-02)
 *
 * What happened that concerns you, what you can do about it from here, and how
 * you want to be told next time.
 *
 * The screen itself, with no chrome around it, so that the wp-admin copy and
 * the portal copy are the same screen rather than two that drift. Same
 * arrangement as `customerspanel.php`, and made for the same reason: the
 * portal had no notifications template at all, so the desk offered agents a
 * link that landed on "Page Not Found !!" - the includer falling through to
 * `missingaddon.php` because `notifications.php` did not exist.
 */
if (!class_exists('JSSTnotifications')) {
    echo esc_html(__('Notifications are not available.', 'js-support-ticket'));
    return;
}
$jsst_me         = isset(jssupportticket::$jsst_data['nfme']) ? (int) jssupportticket::$jsst_data['nfme'] : 0;
$jsst_rows       = isset(jssupportticket::$jsst_data['nfrows']) ? jssupportticket::$jsst_data['nfrows'] : array();
$jsst_prefs      = isset(jssupportticket::$jsst_data['nfprefs']) ? jssupportticket::$jsst_data['nfprefs'] : array();
$jsst_categories = isset(jssupportticket::$jsst_data['nfcategories']) ? jssupportticket::$jsst_data['nfcategories'] : array();
$jsst_channels   = isset(jssupportticket::$jsst_data['nfchannels']) ? jssupportticket::$jsst_data['nfchannels'] : array();
$jsst_held       = isset(jssupportticket::$jsst_data['nfheld']) ? (int) jssupportticket::$jsst_data['nfheld'] : 0;
$jsst_push       = isset(jssupportticket::$jsst_data['nfpush']) ? jssupportticket::$jsst_data['nfpush'] : false;

/* The form posts back to whichever desk it is being read from. In wp-admin
   that is admin.php; on the portal it is the page carrying the shortcode, and
   `makeUrl()` is what knows which. Hard-coding admin_url() here - which is what
   this screen did while it existed only in wp-admin - posts a portal form into
   wp-admin, where an agent who is not an administrator is refused. */
$jsst_action = is_admin()
    ? wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=savenotifications&action=jstask'), 'jsst-notifications')
    : wp_nonce_url(jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'task' => 'savenotifications', 'action' => 'jstask')), 'jsst-notifications');
?>
            <?php /* No navigation here. This file is the screen with no chrome
                     around it - the docblock above says so and customerspanel.php,
                     which it was written to match, has never drawn any - and both
                     shells that include it own that decision. The portal shell
                     drew one and so did this, which is why the strip appeared
                     twice on the agent desk; wp-admin's shell drew none and this
                     one covered for it, which is why the same bug looked fine
                     there. Both shells draw it now. (Roadmap 4.5-FE-02) */ ?>

            <?php if ($jsst_me <= 0) { ?>
                <div class="jsst-card">
                    <div class="jsst-empty">
                        <p class="jsst-empty-title"><?php echo esc_html(__('You are not on the agent list.', 'js-support-ticket')); ?></p>
                        <?php /* Two readers reach this, and the reassurance
                                 that fits one is false to the other. An
                                 administrator without a staff record still runs
                                 the whole desk; an agent without one does not,
                                 and telling them the site is theirs to
                                 administer is both untrue and no use. What an
                                 agent needs is what to do about it.
                                 (Roadmap 4.5-UX-02) */ ?>
                        <p class="jsst-empty-text"><?php echo esc_html(current_user_can('manage_options')
                            ? __('Notifications are addressed to an agent record, and you do not have one — so there is nothing here to show you. Everything on this help desk is still yours to administer.', 'js-support-ticket')
                            : __('Notifications are addressed to an agent record, and you do not have one — so there is nothing here to show you. An administrator can add you to the Agents list, and what happens on your tickets will start arriving here. Everything else on the desk works as it does now.', 'js-support-ticket')); ?></p>
                    </div>
                </div>
            <?php } else { ?>

                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('What happened', 'js-support-ticket')); ?></h2>
                        <?php if (!empty($jsst_rows)) { ?>
                            <div class="jsst-card-tools">
                                <form method="post" action="<?php echo esc_url($jsst_action); ?>">
                                    <button type="submit" name="nfseenall" value="1" class="button-link"><?php echo esc_html(__('Mark all as read', 'js-support-ticket')); ?></button>
                                </form>
                            </div>
                        <?php } ?>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Everything that concerns you: tickets assigned to you, the ones you follow, where you were named, and anything about to be late.', 'js-support-ticket')); ?></p>
                    </div>
                    <?php if (empty($jsst_rows)) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('Nothing yet.', 'js-support-ticket')); ?></p>
                            <p class="jsst-empty-text"><?php echo esc_html(__('You are told when a ticket is assigned to you, when somebody answers or notes on one you follow, when you are named in a note, and when something is about to be late. Following happens by itself: answer a ticket or note on it and you follow it.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-card-body jsst-card-flush">
                            <ul class="jsst-notifs">
                                <?php foreach ($jsst_rows AS $jsst_row) {
                                    $jsst_actions = JSSTnotifications::actionsOn($jsst_row); ?>
                                    <li class="jsst-notif <?php echo ((int) $jsst_row->seen === 0) ? 'jsst-notif-new' : ''; ?>">
                                        <div class="jsst-notif-main">
                                            <p class="jsst-notif-title">
                                                <?php if ((int) $jsst_row->seen === 0) { ?>
                                                    <span class="jsst-dot jsst-notif-dot"></span>
                                                <?php } ?>
                                                <?php echo esc_html($jsst_row->title); ?>
                                                <span class="jsst-pill <?php
                                                    echo ($jsst_row->category === JSSTnotifications::CAT_ESCALATION) ? 'jsst-pill-warn'
                                                        : (($jsst_row->category === JSSTnotifications::CAT_MINE) ? 'jsst-pill-info' : 'jsst-pill-off'); ?>"><?php
                                                    /* The short half of the category label: the
                                                       full one explains what the category means and
                                                       is right on a settings row, but on a pill
                                                       beside a headline it is a paragraph. */
                                                    $jsst_catlabel = isset($jsst_categories[$jsst_row->category])
                                                        ? $jsst_categories[$jsst_row->category] : $jsst_row->category;
                                                    $jsst_dash = mb_strpos($jsst_catlabel, '—');
                                                    echo esc_html($jsst_dash !== false
                                                        ? trim(mb_substr($jsst_catlabel, 0, $jsst_dash))
                                                        : $jsst_catlabel); ?></span>
                                            </p>
                                            <?php if ($jsst_row->body !== '') { ?>
                                                <p class="jsst-notif-body"><?php echo esc_html($jsst_row->body); ?></p>
                                            <?php } ?>
                                            <p class="jsst-notif-when"><?php
                                                $jsst_when = strtotime($jsst_row->created);
                                                /* translators: %s: how long ago, e.g. "5 mins" */
                                                echo esc_html($jsst_when > 0 ? sprintf(__('%s ago', 'js-support-ticket'), human_time_diff($jsst_when)) : '');
                                                if ((int) $jsst_row->held === 1) {
                                                    echo ' · ' . esc_html(__('held for your digest', 'js-support-ticket'));
                                                }
                                            ?></p>
                                        </div>
                                        <div class="jsst-notif-actions">
                                            <form method="post" action="<?php echo esc_url($jsst_action); ?>">
                                                <?php foreach ($jsst_actions AS $jsst_a) {
                                                    /* The link is built HERE, not read from the row.

                                                       `url` is frozen when the notification is written, and
                                                       at that moment there is no shell to speak of - the
                                                       recipient is not reading anything. Stored, it sent an
                                                       agent reading their notifications in the portal into
                                                       wp-admin, while "What happened lately" on the home
                                                       screen two clicks away opened the same ticket in the
                                                       portal, because that panel builds its links from the
                                                       shell it is being drawn in.

                                                       `ticketUrl()` with no shell resolves the current one,
                                                       so the reader stays where they are. The stored url is
                                                       still the right answer for e-mail and push, which have
                                                       no current shell and keep using it. */
                                                    $jsst_openurl = ((int) $jsst_row->ticketid > 0 && class_exists('JSSTnavigation'))
                                                        ? JSSTnavigation::ticketUrl((int) $jsst_row->ticketid)
                                                        : '';
                                                    if ($jsst_openurl === '') {
                                                        $jsst_openurl = $jsst_row->url;
                                                    }
                                                    if ($jsst_a['key'] === 'open' && $jsst_openurl !== '') { ?>
                                                        <a class="button" href="<?php echo esc_url($jsst_openurl); ?>"><?php echo esc_html($jsst_a['label']); ?></a>
                                                    <?php } elseif ($jsst_a['key'] === 'take' && (int) $jsst_row->ticketid > 0) { ?>
                                                        <button type="submit" name="nftake" value="<?php echo esc_attr($jsst_row->ticketid); ?>" class="button"><?php echo esc_html($jsst_a['label']); ?></button>
                                                    <?php }
                                                } ?>
                                                <?php if ((int) $jsst_row->seen === 0) { ?>
                                                    <button type="submit" name="nfseen" value="<?php echo esc_attr($jsst_row->id); ?>" class="button-link"><?php echo esc_html(__('Mark read', 'js-support-ticket')); ?></button>
                                                <?php } ?>
                                            </form>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>

                <h2 class="jsst-groupheading"><?php echo esc_html(__('How you are told', 'js-support-ticket')); ?></h2>
                <div class="jsst-cards">
                    <div class="jsst-card jsst-card-half">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('Which of these reach you, and how', 'js-support-ticket')); ?></h2>
                            <p class="jsst-card-sub"><?php echo esc_html(__('This list always fills — it is a list you come and look at. What you are choosing here is what interrupts you.', 'js-support-ticket')); ?></p>
                        </div>
                        <div class="jsst-card-body">
                            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                                <input type="hidden" name="nfprefs" value="1" />
                                <div class="jsst-table-wrap">
                                    <table class="jsst-table jsst-table-prefs">
                                        <thead>
                                            <tr>
                                                <th scope="col"><?php echo esc_html(__('When', 'js-support-ticket')); ?></th>
                                                <?php foreach ($jsst_channels AS $jsst_ckey => $jsst_clabel) { ?>
                                                    <th scope="col"><?php echo esc_html($jsst_clabel); ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($jsst_categories AS $jsst_cat => $jsst_catlabel) { ?>
                                            <tr>
                                                <th scope="row"><?php echo esc_html($jsst_catlabel); ?></th>
                                                <?php foreach ($jsst_channels AS $jsst_ckey => $jsst_clabel) {
                                                    $jsst_on = !empty($jsst_prefs['channels'][$jsst_cat][$jsst_ckey]);
                                                    $jsst_locked = ($jsst_ckey === JSSTnotifications::CHANNEL_INAPP); ?>
                                                    <td>
                                                        <input type="checkbox" name="channels[<?php echo esc_attr($jsst_cat); ?>][<?php echo esc_attr($jsst_ckey); ?>]"
                                                               value="1" <?php checked($jsst_on || $jsst_locked); ?> <?php disabled($jsst_locked); ?> />
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="jsst-field-note"><?php echo esc_html(__('The first column cannot be turned off. A bell that has been told not to mention things is a bell that lies about what happened; the honest way to be bothered less is to switch off the columns that interrupt.', 'js-support-ticket')); ?></p>

                                <fieldset class="jsst-fieldset">
                                    <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Outside your hours', 'js-support-ticket')); ?></legend>
                                    <label class="jsst-check">
                                        <input type="checkbox" name="quiet" value="1" <?php checked(!empty($jsst_prefs['quiet'])); ?> />
                                        <span><?php echo esc_html(__('Do not e-mail or push outside the hours I work, or while I am on leave', 'js-support-ticket')); ?></span>
                                    </label>
                                    <p class="jsst-field-note"><?php echo esc_html(__('Your working hours, as they are already set on Hours & Leave — not a second window here that could disagree with the first. Nothing is dropped: whatever arrives while you are off is still in the list above, and can be gathered into one e-mail when you are back.', 'js-support-ticket')); ?></p>
                                    <div class="jsst-fields">
                                        <div class="jsst-field">
                                            <label for="digest"><?php echo esc_html(__('And afterwards', 'js-support-ticket')); ?></label>
                                            <select name="digest" id="digest" class="inputbox">
                                                <option value="off" <?php selected($jsst_prefs['digest'], JSSTnotifications::DIGEST_OFF); ?>><?php echo esc_html(__('Nothing — I will look at the list', 'js-support-ticket')); ?></option>
                                                <option value="hourly" <?php selected($jsst_prefs['digest'], JSSTnotifications::DIGEST_HOURLY); ?>><?php echo esc_html(__('One e-mail as soon as I am back', 'js-support-ticket')); ?></option>
                                                <option value="daily" <?php selected($jsst_prefs['digest'], JSSTnotifications::DIGEST_DAILY); ?>><?php echo esc_html(__('One e-mail a day', 'js-support-ticket')); ?></option>
                                            </select>
                                            <?php if ($jsst_held > 0) { ?>
                                                <p class="jsst-field-note"><?php
                                                    /* translators: %d: how many notifications are waiting */
                                                    echo esc_html(sprintf(_n('%d notification is waiting to be gathered up.', '%d notifications are waiting to be gathered up.', $jsst_held, 'js-support-ticket'), $jsst_held)); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="jsst-actions">
                                    <button type="submit" class="button button-primary"><?php echo esc_html(__('Save', 'js-support-ticket')); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="jsst-card jsst-card-half">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('In your browser', 'js-support-ticket')); ?></h2>
                            <p class="jsst-card-sub"><?php echo esc_html(__('A notification from the browser itself, which arrives whether or not this tab is open. It uses the web push standard and your browser\'s own push service — there is no third-party account and nothing to install.', 'js-support-ticket')); ?></p>
                        </div>
                        <div class="jsst-card-body">
                            <?php if (!is_array($jsst_push) || empty($jsst_push['available']) || $jsst_push['reason'] !== '') { ?>
                                <p class="jsst-field-note"><?php
                                    echo esc_html(is_array($jsst_push) && $jsst_push['reason'] !== ''
                                        ? $jsst_push['reason']
                                        : __('Browser push is not available on this server.', 'js-support-ticket')); ?></p>
                            <?php } else { ?>
                                <p class="jsst-field-note" id="jsst-push-state"><?php
                                    echo esc_html($jsst_push['browsers'] > 0
                                        /* translators: %d: how many browsers are registered */
                                        ? sprintf(_n('%d browser is registered.', '%d browsers are registered.', $jsst_push['browsers'], 'js-support-ticket'), $jsst_push['browsers'])
                                        : __('This browser is not registered yet.', 'js-support-ticket')); ?></p>
                                <div class="jsst-actions">
                                    <button type="button" class="button" id="jsst-push-enable"><?php echo esc_html(__('Turn it on in this browser', 'js-support-ticket')); ?></button>
                                </div>
                                <input type="hidden" id="jsst-push-key" value="<?php echo esc_attr($jsst_push['key']); ?>" />
                                <input type="hidden" id="jsst-push-nonce" value="<?php echo esc_attr(wp_create_nonce('jsst-push')); ?>" />
                                <input type="hidden" id="jsst-push-worker" value="<?php echo esc_url(JSST_PLUGIN_URL . 'includes/js/pushworker.js'); ?>" />
                                <p class="jsst-field-note"><?php echo esc_html(__('Each browser is registered separately — the one on your desk and the one on your laptop are two answers to the same question. Turning it off in the browser\'s own settings stops it, and the desk notices and forgets the registration.', 'js-support-ticket')); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            <?php } ?>
