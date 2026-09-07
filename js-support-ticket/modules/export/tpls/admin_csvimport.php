<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Tickets in from a spreadsheet. (Roadmap 4.0-DATA-02)
 *
 * The format is documented on the screen that uses it rather than in a manual,
 * and the table below is generated from the same declaration the importer reads
 * — so what it says is what the importer does, permanently.
 */
if (!class_exists('JSSTcsvimport')) {
    echo '<div class="notice notice-error"><p>' . esc_html(__('The CSV importer could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
JSSTmessage::getMessage();

$jsst_columns     = jssupportticket::$jsst_data['columns'];
$jsst_limitations = jssupportticket::$jsst_data['limitations'];
$jsst_checked     = jssupportticket::$jsst_data['checked'];
$jsst_done        = isset(jssupportticket::$jsst_data['done']) ? jssupportticket::$jsst_data['done'] : null;
$jsst_heldtoken   = isset(jssupportticket::$jsst_data['heldtoken']) ? jssupportticket::$jsst_data['heldtoken'] : '';
$jsst_listurl     = admin_url('admin.php?page=export&jstlay=csvimport');
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
                        <li><?php echo esc_html(__('Import from CSV','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Import from CSV', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <?php
            if (!empty($jsst_done)) {
                /* ---------------------------------------------------------- *
                 * What the import did, and how to undo it
                 * ---------------------------------------------------------- */
                $jsst_rolledback = ($jsst_done['status'] === 'rolledback');
                ?>
                <div class="jsst-status-card <?php echo esc_attr($jsst_rolledback ? '' : 'jsst-status-ok'); ?>">
                    <div class="jsst-status-title"><?php echo esc_html($jsst_rolledback ? __('This import was taken back out', 'js-support-ticket') : __('The import is done', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php
                        if ($jsst_rolledback) {
                            echo esc_html($jsst_done['notes']);
                        } else {
                            echo esc_html(sprintf(
                                /* translators: 1: tickets created, 2: rows that could not be created */
                                _n('%1$s ticket was created. %2$s row could not be.', '%1$s tickets were created. %2$s rows could not be.', (int) $jsst_done['made'], 'js-support-ticket'),
                                number_format_i18n((int) $jsst_done['made']),
                                number_format_i18n((int) $jsst_done['failed'])
                            ));
                        } ?></div>
                    <?php if (!empty($jsst_done['journal'])) { ?>
                        <table class="jsst-status-table">
                            <tr>
                                <td><strong><?php echo esc_html(__('Table', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Rows created', 'js-support-ticket')); ?></strong></td>
                            </tr>
                            <?php foreach ($jsst_done['journal'] AS $jsst_tablename => $jsst_total) { ?>
                                <tr>
                                    <td><span class="jsst-status-path"><?php echo esc_html($jsst_tablename); ?></span></td>
                                    <td><?php echo esc_html(number_format_i18n((int) $jsst_total)); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } ?>
                    <div class="jsst-migration-actions">
                        <?php if (!$jsst_rolledback && !empty($jsst_done['journal'])) { ?>
                            <a class="button js-form-cancel" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=export&task=rollbackcsvimport&action=jstask&done=' . rawurlencode($jsst_done['token'])), 'jsst-csv-rollback')); ?>"><?php echo esc_html(__('Take this import back out', 'js-support-ticket')); ?></a>
                            <span class="jsst-status-correlation"><?php echo esc_html(__('Deletes exactly what this file created, and nothing else.', 'js-support-ticket')); ?></span>
                        <?php } ?>
                        <a class="button js-form-save" href="<?php echo esc_url($jsst_listurl); ?>"><?php echo esc_html(__('Import another file', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <?php
            } elseif (empty($jsst_checked)) {
                /* ---------------------------------------------------------- *
                 * The format, and choosing a file
                 * ---------------------------------------------------------- */
                ?>
                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html(__('The format', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(__('One ticket per row, with a header row naming the columns. Column order does not matter and unknown columns are ignored, so a file with extra columns of your own is fine. Save as CSV in UTF-8.', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(__('A file exported from this help desk can be sent straight back in — its English headings are recognised as well as the names below.', 'js-support-ticket')); ?></div>
                    <table class="jsst-status-table">
                        <tr>
                            <td><strong><?php echo esc_html(__('Column', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('Required', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('What it holds', 'js-support-ticket')); ?></strong></td>
                        </tr>
                        <?php foreach ($jsst_columns AS $jsst_key => $jsst_column) { ?>
                            <tr>
                                <td><code><?php echo esc_html($jsst_key); ?></code></td>
                                <td><?php if (!empty($jsst_column['required'])) { ?>
                                        <span class="jsst-status-flag jsst-status-flag-bad"><?php echo esc_html(__('required', 'js-support-ticket')); ?></span>
                                    <?php } else { ?>
                                        <span class="jsst-status-flag"><?php echo esc_html(__('optional', 'js-support-ticket')); ?></span>
                                    <?php } ?></td>
                                <td><?php echo esc_html($jsst_column['notes']); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                    <div class="jsst-migration-actions">
                        <a class="button js-form-save" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=export&task=downloadcsvtemplate&action=jstask'), 'jsst-csv-template')); ?>"><?php echo esc_html(__('Download the template', 'js-support-ticket')); ?></a>
                        <span class="jsst-status-correlation"><?php echo esc_html(__('A CSV with the header row and one example row.', 'js-support-ticket')); ?></span>
                    </div>
                </div>

                <div class="jsst-status-card jsst-status-warn">
                    <div class="jsst-status-title"><?php echo esc_html(__('What this does not bring across', 'js-support-ticket')); ?></div>
                    <table class="jsst-status-table">
                        <?php foreach ($jsst_limitations AS $jsst_limit) { ?>
                            <tr><td colspan="2"><?php echo esc_html($jsst_limit); ?></td></tr>
                        <?php } ?>
                    </table>
                </div>

                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html(__('Choose a file', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(__('The file is read and checked first. Nothing is created until you have seen what it found.', 'js-support-ticket')); ?></div>
                    <form class="jsstadmin-form" method="post" enctype="multipart/form-data"
                          action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=export&task=checkcsvfile'), 'jsst-csv-check')); ?>">
                        <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                        <div class="jsst-migration-actions">
                            <input type="file" name="csvfile" accept=".csv,text/csv" />
                            <?php echo wp_kses(JSSTformfield::submitbutton('checkcsv', esc_html(__('Check the file', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </form>
                </div>
                <?php
            } else {
                /* ---------------------------------------------------------- *
                 * What the file says
                 * ---------------------------------------------------------- */
                $jsst_rows = $jsst_checked['rows'];
                $jsst_problems = $jsst_checked['problems'];
                ?>
                <div class="jsst-status-card <?php echo esc_attr(empty($jsst_problems) ? 'jsst-status-ok' : 'jsst-status-warn'); ?>">
                    <div class="jsst-status-title"><?php echo esc_html(__('What the file says', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php
                        echo esc_html(sprintf(
                            /* translators: 1: rows that can be imported, 2: rows that cannot */
                            _n('%1$s row can be imported. %2$s cannot.', '%1$s rows can be imported. %2$s cannot.', count($jsst_rows), 'js-support-ticket'),
                            number_format_i18n(count($jsst_rows)),
                            number_format_i18n(count($jsst_problems))
                        ));
                        if (!empty($jsst_problems)) {
                            echo '&nbsp;' . esc_html(__('Rows with a problem are left out entirely — fix them in the spreadsheet and import that part separately, or import what is good now and the rest later.', 'js-support-ticket'));
                        } ?></div>
                </div>

                <?php if (!empty($jsst_problems)) { ?>
                    <div class="jsst-status-card jsst-status-warn">
                        <div class="jsst-status-title"><?php echo esc_html(__('Rows that cannot be imported', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <tr>
                                <td><strong><?php echo esc_html(__('Line', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Why', 'js-support-ticket')); ?></strong></td>
                            </tr>
                            <?php foreach ($jsst_problems AS $jsst_problem) { ?>
                                <tr>
                                    <td><?php echo esc_html((int) $jsst_problem['line']); ?></td>
                                    <td><?php echo esc_html($jsst_problem['why']); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php }

                if (!empty($jsst_rows)) {
                    /* The first few, so somebody can see the columns landed
                       where they meant them to rather than trusting that they
                       did. A header row shifted by one is invisible in a count
                       and obvious in a table. */
                    $jsst_preview = array_slice($jsst_rows, 0, 5); ?>
                    <div class="jsst-status-card">
                        <div class="jsst-status-title"><?php echo esc_html(__('The first few, as they will be created', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <tr>
                                <td><strong><?php echo esc_html(__('Line', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Requester', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Created', 'js-support-ticket')); ?></strong></td>
                            </tr>
                            <?php foreach ($jsst_preview AS $jsst_row) { ?>
                                <tr>
                                    <td><?php echo esc_html((int) $jsst_row['line']); ?></td>
                                    <td><?php echo esc_html(wp_trim_words($jsst_row['subject'], 12)); ?></td>
                                    <td><?php echo esc_html($jsst_row['email']); ?></td>
                                    <td><?php echo esc_html($jsst_row['created']); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>

                    <div class="jsst-status-card">
                        <div class="jsst-status-title"><?php echo esc_html(__('Import them', 'js-support-ticket')); ?></div>
                        <div class="jsst-status-note"><?php echo esc_html(__('Every ticket this creates is recorded, so the whole import can be taken back out in one action if it is not what you wanted. Nobody is emailed.', 'js-support-ticket')); ?></div>
                        <div class="jsst-migration-actions">
                            <a class="button js-form-save" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=export&task=runcsvimport&action=jstask&held=' . rawurlencode($jsst_heldtoken)), 'jsst-csv-import')); ?>"><?php echo esc_html(sprintf(
                                /* translators: %s: number of tickets that will be created */
                                __('Create %s tickets', 'js-support-ticket'),
                                number_format_i18n(count($jsst_rows))
                            )); ?></a>
                            <a class="button js-form-cancel" href="<?php echo esc_url($jsst_listurl); ?>"><?php echo esc_html(__('Start again', 'js-support-ticket')); ?></a>
                        </div>
                    </div>
                    <?php
                } else { ?>
                    <div class="jsst-status-card">
                        <div class="jsst-migration-actions">
                            <a class="button js-form-save" href="<?php echo esc_url($jsst_listurl); ?>"><?php echo esc_html(__('Try another file', 'js-support-ticket')); ?></a>
                        </div>
                    </div>
                    <?php
                }
            } ?>

        </div>
    </div>
</div>
