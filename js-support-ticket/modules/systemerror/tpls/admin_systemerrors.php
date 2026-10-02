<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');
JSSTmessage::getMessage();
$jsst_hasrows = !empty(jssupportticket::$jsst_data[0]);
$jsst_actions = array();
if ($jsst_hasrows) {
    $jsst_actions[] = array(
        'text'  => __('Remove All', 'js-support-ticket'),
        'url'   => wp_nonce_url('?page=systemerror&task=deletesystemerror&action=jstask&systemerrorid=all','delete-systemerror-all'),
        'style' => 'danger',
        'attrs' => array('onclick' => "return confirm('" . esc_js(__('Are you sure you want to delete?', 'js-support-ticket')) . "');"),
    );
}
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('System Errors', 'js-support-ticket'),
            'count'   => isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0,
            'actions' => $jsst_actions,
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if ($jsst_hasrows) { ?>
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-name"><?php echo esc_html(__('Error Details', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $jsst_systemerror) {
                            $jsst_raw_error = $jsst_systemerror->error;
                            $jsst_error_data = json_decode($jsst_raw_error, true);
                            ?>
                            <tr>
                                <th scope="row" class="jsst-col-name">
                                    <?php if (is_array($jsst_error_data)) { ?>
                                        <span class="jsst-table-name"><?php echo esc_html(isset($jsst_error_data['error']) ? $jsst_error_data['error'] : __('Unknown Error', 'js-support-ticket')); ?></span>
                                        <span class="jsst-table-sub"><?php echo esc_html(isset($jsst_error_data['url']) ? $jsst_error_data['url'] : __('N/A', 'js-support-ticket')); ?></span>
                                        <details class="jsst-details">
                                            <summary><?php echo esc_html(__('View Query & Trace', 'js-support-ticket')); ?></summary>
                                            <div class="jsst-details-body">
                                                <span class="jsst-flabel"><?php echo esc_html(__('Path Execution Trace', 'js-support-ticket')); ?></span>
                                                <div class="jsst-codeblock"><?php echo esc_html(isset($jsst_error_data['path']) ? $jsst_error_data['path'] : __('N/A', 'js-support-ticket')); ?></div>
                                                <span class="jsst-flabel"><?php echo esc_html(__('Database Query', 'js-support-ticket')); ?></span>
                                                <div class="jsst-codeblock"><?php echo esc_html(isset($jsst_error_data['query']) ? $jsst_error_data['query'] : __('N/A', 'js-support-ticket')); ?></div>
                                            </div>
                                        </details>
                                    <?php } elseif (!empty($jsst_raw_error)) { ?>
                                        <span class="jsst-table-name"><?php echo esc_html(__('Legacy Log', 'js-support-ticket')); ?></span>
                                        <div class="jsst-codeblock"><?php echo esc_html($jsst_raw_error); ?></div>
                                    <?php } else { ?>
                                        <span class="jsst-table-sub"><?php echo esc_html(__('No error metadata tracking data recorded.', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                </th>
                                <td class="jsst-col-fit"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_systemerror->created))); ?></td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=systemerror&task=deletesystemerror&action=jstask&systemerrorid='.esc_attr($jsst_systemerror->id),'delete-systemerror-'.$jsst_systemerror->id)); ?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php JSSTlayout::adminPager(jssupportticket::$jsst_data[1]); ?>
            <?php } else { ?>
                <div class="jsst-card">
                    <?php JSSTlayout::adminEmpty(
                        __('No system errors.', 'js-support-ticket'),
                        __('Errors the plugin records while it runs appear here.', 'js-support-ticket')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
