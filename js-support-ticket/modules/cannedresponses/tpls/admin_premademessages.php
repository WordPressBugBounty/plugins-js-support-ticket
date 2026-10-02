<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$jsst_jssupportticket_js ="
    function resetFrom() {
        document.getElementById('title').value = '';
        document.getElementById('status').value = '';
        document.getElementById('departmentid').value = '';
        document.getElementById('jssupportticketform').submit();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_status = array(
    (object) array('id' => '1', 'text' => __('Active', 'js-support-ticket')),
    (object) array('id' => '0', 'text' => __('Offline', 'js-support-ticket'))
);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <?php JSSTsidemenu::render(); ?>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Canned Responses', 'js-support-ticket'),
            'count'   => isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0,
            'actions' => array(
                array('text' => __('Add Canned Response', 'js-support-ticket'), 'url' => admin_url('admin.php?page=cannedresponses&jstlay=addpremademessage'), 'icon' => 'plus'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=cannedresponses&jstlay=premademessages"),"canned-responses")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('title', jssupportticket::$jsst_data['filter']['title'], array('placeholder' => __('Search responses', 'js-support-ticket'),'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), jssupportticket::$jsst_data['filter']['departmentid'], __('All departments', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('status', $jsst_status, jssupportticket::$jsst_data['filter']['status'], __('Any status', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', __('Search', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', __('Reset', 'js-support-ticket'), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-name"><?php echo esc_html(__('Response', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Last Updated', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $jsst_premade) {
                            if (empty($jsst_premade->updated) || $jsst_premade->updated == '0000-00-00 00:00:00') {
                                $jsst_updated = __('Not updated', 'js-support-ticket');
                            } else {
                                $jsst_updated = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_premade->updated));
                            }
                            $jsst_editurl = admin_url('admin.php?page=cannedresponses&jstlay=addpremademessage&jssupportticketid=' . $jsst_premade->id);
                            ?>
                            <tr>
                                <th scope="row" class="jsst-col-name">
                                    <a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html($jsst_premade->title); ?></a>
                                    <span class="jsst-table-sub"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_premade->departmentname)); ?></span>
                                </th>
                                <td class="jsst-col-fit">
                                    <a class="jsst-mark<?php echo ($jsst_premade->status == 1) ? ' jsst-mark-on' : ''; ?>" title="<?php echo esc_attr(__('Change status','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url('?page=cannedresponses&task=changestatus&action=jstask&premadeid='.$jsst_premade->id,'change-status-'.$jsst_premade->id)); ?>"><?php echo ($jsst_premade->status == 1) ? esc_html(__('Active', 'js-support-ticket')) : esc_html(__('Offline', 'js-support-ticket')); ?></a>
                                </td>
                                <td class="jsst-col-fit"><?php echo esc_html($jsst_updated); ?></td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=cannedresponses&task=deletepremademessage&action=jstask&premademessageid='.$jsst_premade->id,'delete-premademessage-'.$jsst_premade->id)); ?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
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
                        __('No canned responses found.', 'js-support-ticket'),
                        __('A canned response is a reply an agent can insert instead of typing it again.', 'js-support-ticket'),
                        __('Add canned response', 'js-support-ticket'),
                        admin_url('admin.php?page=cannedresponses&jstlay=addpremademessage')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
