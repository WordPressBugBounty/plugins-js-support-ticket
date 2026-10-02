<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$jsst_jssupportticket_js ="
    function resetFrom() {
        document.getElementById('loggeremail').value = '';
        document.getElementById('jssupportticketform').submit();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Ban Log', 'js-support-ticket'),
            'count' => isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0,
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=banemaillog&jstlay=banemaillogs"),"ban-email-log")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('loggeremail', jssupportticket::$jsst_data['filter']['loggeremail'], array('placeholder' => __('Search by logger email', 'js-support-ticket'),'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', __('Search', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', __('Reset', 'js-support-ticket'), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table jsst-table-dense">
                        <thead>
                        <tr>
                            <th class="jsst-col-name"><?php echo esc_html(__('Log', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Logger', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('IP Address', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $jsst_email) { ?>
                            <tr>
                                <th scope="row" class="jsst-col-name">
                                    <span class="jsst-table-name"><?php echo esc_html($jsst_email->title); ?></span>
                                    <span class="jsst-table-sub"><?php echo esc_html($jsst_email->log); ?></span>
                                </th>
                                <td class="jsst-col-fit">
                                    <?php echo esc_html($jsst_email->logger); ?>
                                    <span class="jsst-table-sub"><?php echo esc_html($jsst_email->loggeremail); ?></span>
                                </td>
                                <td class="jsst-col-fit jsst-num"><?php echo esc_html($jsst_email->ipaddress); ?></td>
                                <td class="jsst-col-fit"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_email->created))); ?></td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=banemaillog&task=deletebanemaillog&action=jstask&banemaillogid='.$jsst_email->id,'delete-banemaillog-'.$jsst_email->id)); ?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
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
                        __('Nothing has been blocked.', 'js-support-ticket'),
                        __('Each time a banned address tries to open a ticket, it is recorded here.', 'js-support-ticket')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
