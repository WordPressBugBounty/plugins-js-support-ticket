<?php
if(!defined('ABSPATH')) die('Restricted Access');

/**
 * Zywrap errors: the calls to the Zywrap API that failed.
 *
 * On the shared admin vocabulary like the Zywrap requests tab beside it. The
 * page-size field, nonce and delete task are unchanged. The old "Agent / UID"
 * column is gone: the log table has no such column, so it printed a PHP
 * warning on every row and nothing else. The delete link is an
 * admin address now - it was built with the front-end URL helper and the
 * current post id, on a screen that has neither.
 */
$jsst_errors = !empty(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : array();
$jsst_total  = isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0;
$jsst_size   = isset(jssupportticket::$jsst_data['filter']['pagesize']) ? jssupportticket::$jsst_data['filter']['pagesize'] : 20;
$jsst_sizes  = array((object) array('id' => 20, 'text' => 20), (object) array('id' => 50, 'text' => 50), (object) array('id' => 100, 'text' => 100));
$jsst_format = jssupportticket::$_config['date_format'] . ' H:i';
$jsst_pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- carried into the delete link so it returns to this page

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper" class="js-ticket-zywrap-errors-page">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => __('Zywrap errors', 'js-support-ticket'),
            'count'  => $jsst_total,
            'crumbs' => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('zywrap_errors'); } ?>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Failed calls to Zywrap', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <?php if ($jsst_total > 0) { ?>
                            <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php
                                /* translators: %d: number of failed calls */
                                echo esc_html(sprintf(_n('%d failure recorded', '%d failures recorded', $jsst_total, 'js-support-ticket'), $jsst_total)); ?></span>
                            <form method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=zywrap&jstlay=zywrap_errors"),"zywrap_errors")); ?>" name="jssupportticketform" id="jssupportticketform">
                                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                                <label class="screen-reader-text" for="pagesize"><?php echo esc_html(__('Records per page', 'js-support-ticket')); ?></label>
                                <?php echo wp_kses(JSSTformfield::select('pagesize', $jsst_sizes, $jsst_size, __('Records per page', 'js-support-ticket'), array('class' => 'jsst-select', 'onchange' => 'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
                            </form>
                        <?php } else { ?>
                            <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('No failures recorded', 'js-support-ticket')); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="jsst-card-body jsst-card-flush">
                    <?php if (empty($jsst_errors)) {
                        JSSTlayout::adminEmpty(
                            __('No failed calls', 'js-support-ticket'),
                            __('Every call to Zywrap has succeeded so far. A call that fails is listed here with the reason Zywrap gave.', 'js-support-ticket'));
                    } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo esc_html(__('What failed', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-col-fit"><?php echo esc_html(__('When', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_errors AS $err) {
                                    $jsst_deleteurl = admin_url('admin.php?page=zywrap&task=delete_log&action=jstask&id=' . (int) $err->id
                                        . ($jsst_pagenum > 0 ? '&pagenum=' . $jsst_pagenum : '')); ?>
                                    <tr>
                                        <td>
                                            <span class="jsst-table-name"><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $err->wrapper_code))); ?></span>
                                            <span class="jsst-table-sub"><?php echo esc_html($err->error_message); ?></span>
                                        </td>
                                        <td class="jsst-col-fit"><span class="jsst-table-sub"><?php echo esc_html(date_i18n($jsst_format, jssupportticketphplib::JSST_strtotime($err->created_at))); ?></span></td>
                                        <td class="jsst-col-act">
                                            <span class="jsst-rowactions">
                                                <a class="jsst-act jsst-act-danger"
                                                   onclick="return confirm('<?php echo esc_js(__('Delete this error from the log?', 'js-support-ticket')); ?>');"
                                                   href="<?php echo esc_url(wp_nonce_url($jsst_deleteurl, 'delete_log_' . (int) $err->id)); ?>"><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></a>
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php
            if (!empty($jsst_errors) && isset(jssupportticket::$jsst_data[1])) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
            }
            ?>
        </div>
    </div>
</div>
