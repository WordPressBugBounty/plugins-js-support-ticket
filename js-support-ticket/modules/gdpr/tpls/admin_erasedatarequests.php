<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');

$jsst_jssupportticket_js ="
    function resetFrom() {
        document.getElementById('email').value = '';
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
            'title' => __('Erase Data Requests', 'js-support-ticket'),
            'count' => isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0,
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=gdpr&jstlay=erasedatarequests"),"erase-data-requests")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('email', jssupportticket::$jsst_data['filter']['email'], array('placeholder' => esc_html(__('Search by user email', 'js-support-ticket')),'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-name"><?php echo esc_html(__('Request', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Email', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Request Status', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $jsst_request) {
                            if ($jsst_request->status == 1) {
                                $jsst_state = array('jsst-mark jsst-mark-warn', __('Awaiting response','js-support-ticket'));
                            } elseif ($jsst_request->status == 2) {
                                $jsst_state = array('jsst-mark jsst-mark-on', __('Erased identifying data','js-support-ticket'));
                            } else {
                                $jsst_state = array('jsst-mark', __('Deleted','js-support-ticket'));
                            }
                            ?>
                            <tr>
                                <th scope="row" class="jsst-col-name">
                                    <span class="jsst-table-name"><?php echo esc_html($jsst_request->subject); ?></span>
                                    <span class="jsst-table-sub"><?php echo wp_kses($jsst_request->message, JSST_ALLOWED_TAGS); ?></span>
                                </th>
                                <td class="jsst-col-fit"><?php echo esc_html($jsst_request->user_email); ?></td>
                                <td class="jsst-col-fit"><span class="<?php echo esc_attr($jsst_state[0]); ?>"><?php echo esc_html($jsst_state[1]); ?></span></td>
                                <td class="jsst-col-fit"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_request->created))); ?></td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" onclick="return confirm('<?php echo esc_js(__('Are you sure to erase identifying data', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=gdpr&task=eraseidentifyinguserdata&action=jstask&jssupportticketid='.esc_attr($jsst_request->uid),'erase-userdata')); ?>"><?php echo esc_html(__('Erase identifying data', 'js-support-ticket')); ?></a>
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=gdpr&task=deleteuserdata&action=jstask&jssupportticketid='.esc_attr($jsst_request->uid),'delete-userdata')); ?>"><?php echo esc_html(__('Delete data', 'js-support-ticket')); ?></a>
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
                        __('No erase data requests.', 'js-support-ticket'),
                        __('When a customer asks for their data to be erased, the request appears here.', 'js-support-ticket')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
