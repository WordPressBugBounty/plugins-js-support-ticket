<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Retiring Internal Mail. (Roadmap 4.5-FE-12)
 */
if (!class_exists('JSSTinternalmail')) {
    echo esc_html(__('This is not available.', 'js-support-ticket'));
    return;
}
$jsst_survey       = isset(jssupportticket::$jsst_data['imsurvey']) ? jssupportticket::$jsst_data['imsurvey'] : array();
$jsst_preview      = isset(jssupportticket::$jsst_data['impreview']) ? jssupportticket::$jsst_data['impreview'] : array();
$jsst_replacements = isset(jssupportticket::$jsst_data['imreplacements']) ? jssupportticket::$jsst_data['imreplacements'] : array();
$jsst_action       = wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=saveinternalmail&action=jstask'), 'jsst-internalmail');
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Internal Mail', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <p class="jsst-lede">
                <?php echo esc_html(__('Internal Mail is a second inbox sitting beside the help desk and knowing nothing about it — which puts a question about a ticket somewhere the next person to open that ticket will never look. Everything it was used for now lives on the ticket itself. This screen moves what can be moved, keeps everything else, and changes nothing you have not asked it to.', 'js-support-ticket')); ?>
            </p>

            <?php if (empty($jsst_survey['available'])) { ?>
                <div class="jsst-card">
                    <div class="jsst-empty">
                        <p class="jsst-empty-title"><?php echo esc_html(__('There is no internal mailbox on this site.', 'js-support-ticket')); ?></p>
                        <p class="jsst-empty-text"><?php echo esc_html(__('The Internal Mail add-on has never stored anything here, so there is nothing to retire. What replaces it is listed below and is already working.', 'js-support-ticket')); ?></p>
                    </div>
                </div>
            <?php } else { ?>

                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('What is in there', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('A message carries no ticket — the old table has no column for one — so a message can only be moved onto a ticket it actually names. The rest are kept exactly where they are.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <div class="jsst-metrics">
                            <span class="jsst-metric">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_survey['total']); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Messages', 'js-support-ticket')); ?></span>
                            </span>
                            <span class="jsst-metric">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_survey['matched']); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Name a ticket', 'js-support-ticket')); ?></span>
                            </span>
                            <span class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_survey['unmatched']); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Do not', 'js-support-ticket')); ?></span>
                            </span>
                            <span class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_survey['migrated']); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Already moved', 'js-support-ticket')); ?></span>
                            </span>
                        </div>
                        <?php if ($jsst_survey['first'] !== '') { ?>
                            <p class="jsst-fhelp"><?php
                                /* translators: 1: earliest message date, 2: latest */
                                echo esc_html(sprintf(__('From %1$s to %2$s.', 'js-support-ticket'), $jsst_survey['first'], $jsst_survey['last'])); ?></p>
                        <?php } ?>

                        <form method="post" action="<?php echo esc_url($jsst_action); ?>">
                            <div class="jsst-btnrow">
                                <button type="submit" name="imarchive" value="1" class="jsst-btn"><?php echo esc_html(__('Download all of it first', 'js-support-ticket')); ?></button>
                                <button type="submit" name="immigrate" value="1" class="jsst-btn jsst-btn-primary" onclick="return confirm('<?php echo esc_js(__('Move every message that names a ticket onto that ticket, as a note only the two people on the message can read? Nothing is deleted.', 'js-support-ticket')); ?>');"><?php echo esc_html(__('Move the ones that name a ticket', 'js-support-ticket')); ?></button>
                                <span class="jsst-formfoot-note"><?php echo esc_html(__('Running it twice does not move anything twice.', 'js-support-ticket')); ?></span>
                            </div>
                        </form>
                        <p class="jsst-hint"><?php echo esc_html(__('Nothing here deletes a message, empties the old mailbox or switches the add-on off. If this turns out to have been a mistake, the mailbox is exactly where it was.', 'js-support-ticket')); ?></p>
                    </div>
                </div>

                <?php if (!empty($jsst_preview)) { ?>
                    <h2 class="jsst-groupheading"><?php echo esc_html(__('What would happen to each one', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card">
                        <div class="jsst-card-body jsst-card-flush">
                            <div class="jsst-table-wrap">
                                <table class="jsst-table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><?php echo esc_html(__('Message', 'js-support-ticket')); ?></th>
                                            <th scope="col"><?php echo esc_html(__('Between', 'js-support-ticket')); ?></th>
                                            <th scope="col"><?php echo esc_html(__('Sent', 'js-support-ticket')); ?></th>
                                            <th scope="col"><?php echo esc_html(__('Where it would go', 'js-support-ticket')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($jsst_preview AS $jsst_row) { ?>
                                        <tr>
                                            <th scope="row">
                                                <?php echo esc_html($jsst_row['subject'] !== '' ? $jsst_row['subject'] : __('(no subject)', 'js-support-ticket')); ?>
                                                <span class="jsst-table-sub"><?php echo esc_html($jsst_row['excerpt']); ?></span>
                                            </th>
                                            <td><?php
                                                /* translators: 1: sender, 2: recipient */
                                                echo esc_html(sprintf(__('%1$s to %2$s', 'js-support-ticket'), $jsst_row['from'], $jsst_row['to'])); ?></td>
                                            <td><?php echo esc_html($jsst_row['created']); ?></td>
                                            <td><?php
                                                if ($jsst_row['done']) { ?>
                                                    <span class="jsst-pill jsst-pill-off"><?php echo esc_html(__('Already moved', 'js-support-ticket')); ?></span>
                                                <?php } elseif ($jsst_row['ticketid'] > 0) { ?>
                                                    <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html($jsst_row['reference']); ?></span>
                                                    <span class="jsst-table-sub"><?php
                                                        echo esc_html($jsst_row['how'] === JSSTinternalmail::MATCH_REFERENCE
                                                            ? __('It quotes the ticket\'s own reference', 'js-support-ticket')
                                                            : __('It says #number — weaker, check before you run it', 'js-support-ticket')); ?></span>
                                                <?php } else { ?>
                                                    <span class="jsst-pill jsst-pill-off"><?php echo esc_html(__('Stays where it is', 'js-support-ticket')); ?></span>
                                                    <span class="jsst-table-sub"><?php echo esc_html(__('It names no ticket', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            <?php } ?>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('What replaces it', 'js-support-ticket')); ?></h2>
            <div class="jsst-card">
                <div class="jsst-card-body jsst-card-flush">
                    <div class="jsst-table-wrap">
                        <table class="jsst-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo esc_html(__('What you used it for', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('What to do now', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_replacements AS $jsst_r) { ?>
                                <tr>
                                    <th scope="row"><?php echo esc_html($jsst_r['what']); ?></th>
                                    <td><?php echo esc_html($jsst_r['now']); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
