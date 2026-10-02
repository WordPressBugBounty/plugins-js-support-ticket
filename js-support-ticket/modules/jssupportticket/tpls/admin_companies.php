<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Companies. (Roadmap 5.5-COM-06)
 *
 * The companies written down, with the numbers counted from their tickets.
 * One company opens in its own form (admin_addcompany.php).
 */
if (!class_exists('JSSTcompanies')) {
    echo esc_html(__('This is not available.', 'js-support-ticket'));
    return;
}
$jsst_companies = isset(jssupportticket::$jsst_data['companies']) ? jssupportticket::$jsst_data['companies'] : array();
$jsst_totals    = isset(jssupportticket::$jsst_data['companytotals']) ? jssupportticket::$jsst_data['companytotals'] : array();
$jsst_suggest   = isset(jssupportticket::$jsst_data['companysuggest']) ? jssupportticket::$jsst_data['companysuggest'] : array();
$jsst_addurl    = admin_url('admin.php?page=jssupportticket&jstlay=addcompany');
$jsst_words = array(
    'none'    => array('jsst-mark', __('none recorded', 'js-support-ticket')),
    'active'  => array('jsst-mark jsst-mark-on', __('in force', 'js-support-ticket')),
    'pending' => array('jsst-mark jsst-mark-warn', __('not started', 'js-support-ticket')),
    'expired' => array('jsst-mark jsst-mark-bad', __('expired', 'js-support-ticket')),
);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Companies', 'js-support-ticket'),
            'count'   => count($jsst_companies),
            'actions' => array(
                array('text' => __('Add Company', 'js-support-ticket'), 'url' => $jsst_addurl, 'icon' => 'plus'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <p class="jsst-lede"><?php echo esc_html(__('Group customers into companies to see all their tickets together and apply one contract or entitlement to everyone in it.', 'js-support-ticket')); ?></p>

            <div class="jsst-card">
                <?php if (empty($jsst_companies)) { ?>
                    <?php JSSTlayout::adminEmpty(__('No companies yet', 'js-support-ticket'), __('Until you add one, customers are grouped by their email domain (for example, everyone @acme.com).', 'js-support-ticket'), __('Add Company', 'js-support-ticket'), $jsst_addurl); ?>
                <?php } else { ?>
                    <div class="jsst-table-wrap">
                        <table class="jsst-table">
                            <thead>
                                <tr>
                                    <th class="jsst-col-name"><?php echo esc_html(__('Company', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit"><?php echo esc_html(__('People', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit"><?php echo esc_html(__('Tickets', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit"><?php echo esc_html(__('Open', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit"><?php echo esc_html(__('Contract', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_companies AS $jsst_cid => $jsst_row) {
                                $jsst_t = isset($jsst_totals[$jsst_cid]) ? $jsst_totals[$jsst_cid] : false;
                                $jsst_state = $jsst_t ? $jsst_t['contract'] : 'none';
                                $jsst_word = isset($jsst_words[$jsst_state]) ? $jsst_words[$jsst_state] : array('jsst-mark', $jsst_state);
                                $jsst_editurl = admin_url('admin.php?page=jssupportticket&jstlay=addcompany&companyid=' . $jsst_cid);
                                $jsst_archived = ((int) $jsst_row->status !== JSSTcompanies::STATUS_ACTIVE); ?>
                                <tr<?php if ($jsst_archived) { ?> class="jsst-row-muted"<?php } ?>>
                                    <th scope="row" class="jsst-col-name">
                                        <a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html($jsst_row->name); ?></a>
                                        <span class="jsst-table-sub"><?php
                                            $jsst_domains = JSSTcompanies::domainsOf($jsst_row);
                                            echo esc_html(empty($jsst_domains)
                                                ? __('named people only', 'js-support-ticket')
                                                : implode(', ', $jsst_domains));
                                            if ($jsst_archived) {
                                                echo ' — ' . esc_html(__('archived', 'js-support-ticket'));
                                            } ?></span>
                                    </th>
                                    <td class="jsst-col-fit jsst-num"><?php echo esc_html($jsst_t ? $jsst_t['people'] : 0); ?></td>
                                    <td class="jsst-col-fit jsst-num"><?php echo esc_html($jsst_t ? $jsst_t['tickets'] : 0); ?></td>
                                    <td class="jsst-col-fit jsst-num"><?php echo esc_html($jsst_t ? $jsst_t['openticket'] : 0); ?></td>
                                    <td class="jsst-col-fit"><span class="<?php echo esc_attr($jsst_word[0]); ?>"><?php echo esc_html($jsst_word[1]); ?></span></td>
                                    <td class="jsst-col-act">
                                        <span class="jsst-rowactions">
                                            <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit', 'js-support-ticket')); ?></a>
                                            <a class="jsst-act jsst-act-danger" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=deletecompany&action=jstask&companyid=' . $jsst_cid), 'jsst-company-delete')); ?>"
                                               onclick="return confirm('<?php echo esc_js(__('Delete this company? Their tickets are untouched — the company was never written on to one — and they become ordinary tickets again.', 'js-support-ticket')); ?>');"><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></a>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
            <?php if (!empty($jsst_companies)) { ?>
                <p class="jsst-hint"><?php echo esc_html(__('Every figure is counted from the tickets when this page is drawn, so moving somebody between companies is right immediately.', 'js-support-ticket')); ?></p>
            <?php } ?>

            <?php if (!empty($jsst_suggest)) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Domains writing in most', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('A suggestion, not a migration. There is no button here making one company per domain: on a site with nine thousand customers that produces four thousand records, of which about eleven are companies. Pick one and give it its real name.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <div class="jsst-chips">
                            <?php foreach ($jsst_suggest AS $jsst_row) { ?>
                                <span class="jsst-chip"><?php echo esc_html($jsst_row->company); ?>&nbsp;<span class="jsst-num">(<?php
                                    echo esc_html(sprintf(
                                        /* translators: %d: number of tickets */
                                        _n('%d ticket', '%d tickets', (int) $jsst_row->tickets, 'js-support-ticket'),
                                        (int) $jsst_row->tickets)); ?>)</span></span>
                            <?php } ?>
                        </div>
                        <p class="jsst-hint"><?php echo esc_html(__('Mail providers are left out of this list, because a company cannot claim one.', 'js-support-ticket')); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
