<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$jsst_jssupportticket_js ="
    function resetFrom() {
        document.getElementById('topic').value = '';
        document.getElementById('status').value = '';
        document.getElementById('jssupportticketform').submit();
    }
    jQuery(document).ready(function () {
        jQuery('table.jsst-table tbody').sortable({
            handle : '.jsst-grab',
            axis : 'y',
            update  : function () {
                jQuery('.jsst-orderbar').slideDown('slow');
                var abc =  jQuery('table.jsst-table tbody').sortable('serialize');
                jQuery('input#fields_ordering_new').val(abc);
            }
        });
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_status = array((object) array('id' => '1', 'text' => __('Active', 'js-support-ticket')),
    (object) array('id' => '0', 'text' => __('Disabled', 'js-support-ticket'))
);
// The owner column only means something while the Agents add-on is managing
// agents; without it there is nobody to own a topic. (Roadmap 4.0-CORE-20)
$jsst_showowner = in_array('agent', jssupportticket::$_active_addons);
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Ticket Topics', 'js-support-ticket'),
            'count'   => isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0,
            'actions' => array(
                array('text' => __('Watch Video', 'js-support-ticket'), 'url' => 'https://www.youtube.com/watch?v=-eh4XuDwXoY', 'style' => 'ghost', 'target' => '_blank'),
                array('text' => __('Add Ticket Topic', 'js-support-ticket'), 'url' => admin_url('admin.php?page=helptopic&jstlay=addhelptopic'), 'icon' => 'plus'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=helptopic&jstlay=helptopics"),"helptopic")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('topic', jssupportticket::$jsst_data['filter']['topic'], array('placeholder' => __('Search topics', 'js-support-ticket'),'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::select('status', $jsst_status, jssupportticket::$jsst_data['filter']['status'], __('Any status', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', __('Search', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', __('Reset', 'js-support-ticket'), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
                <div class="jsst-filterbar-end">
                    <label class="jsst-filterbar-label" for="pagesize"><?php echo esc_html(__('Rows per page', 'js-support-ticket')); ?></label>
                    <?php echo wp_kses(JSSTformfield::select('pagesize', array((object) array('id'=>20,'text'=>20), (object) array('id'=>50,'text'=>50), (object) array('id'=>100,'text'=>100)), jssupportticket::$jsst_data['filter']['pagesize'], '', array('class' => 'jsst-select','onchange'=>'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=jssupportticket&task=saveordering"),"save-ordering")); ?>">
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-grab"><span class="screen-reader-text"><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></span></th>
                            <th class="jsst-col-name"><?php echo esc_html(__('Topic', 'js-support-ticket')); ?></th>
                            <?php if ($jsst_showowner) { ?>
                                <th class="jsst-col-fit"><?php echo esc_html(__('Owned By', 'js-support-ticket')); ?></th>
                            <?php } ?>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Last Updated', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Owners for the rows on this page, fetched once by the model.
                        $jsst_owners = isset(jssupportticket::$jsst_data['topic_owners']) ? jssupportticket::$jsst_data['topic_owners'] : array();
                        foreach (jssupportticket::$jsst_data[0] AS $jsst_helptopic) {
                            if (empty($jsst_helptopic->updated) || $jsst_helptopic->updated == '0000-00-00 00:00:00') {
                                $jsst_updated = __('Not updated', 'js-support-ticket');
                            } else {
                                $jsst_updated = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_helptopic->updated));
                            }
                            // One indent step per level, so the tree an administrator
                            // built is the tree they see. (Roadmap 4.0-CORE-20)
                            $jsst_depth = isset($jsst_helptopic->jsst_depth) ? (int) $jsst_helptopic->jsst_depth : 1;
                            $jsst_editurl = admin_url('admin.php?page=helptopic&jstlay=addhelptopic&jssupportticketid=' . $jsst_helptopic->id);
                            ?>
                            <tr id="id_<?php echo esc_attr($jsst_helptopic->id); ?>"<?php echo !empty($jsst_helptopic->isdefault) ? ' class="jsst-row-default"' : ''; ?>>
                                <td class="jsst-col-grab">
                                    <span class="jsst-grab" role="img" aria-label="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>" title="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>"></span>
                                </td>
                                <th scope="row" class="jsst-col-name">
                                    <span class="jsst-ident">
                                        <?php if ($jsst_depth > 1) { ?>
                                            <span class="jsst-indent" aria-hidden="true"><?php echo esc_html(str_repeat("\xC2\xA0\xC2\xA0\xC2\xA0", $jsst_depth - 1) . "\xe2\x94\x94"); ?></span>
                                        <?php } ?>
                                        <span class="jsst-ident-text">
                                            <a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html($jsst_helptopic->topic); ?></a>
                                            <span class="jsst-table-sub"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_helptopic->departmentname)); ?></span>
                                        </span>
                                        <?php if (!empty($jsst_helptopic->isdefault)) { ?>
                                            <span class="jsst-chip jsst-chip-lead"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                    </span>
                                </th>
                                <?php if ($jsst_showowner) { ?>
                                    <td class="jsst-col-fit"><?php
                                    $jsst_ownerid = isset($jsst_helptopic->staffid) ? (int) $jsst_helptopic->staffid : 0;
                                    if ($jsst_ownerid && isset($jsst_owners[$jsst_ownerid])) {
                                        echo esc_html($jsst_owners[$jsst_ownerid]);
                                    } else { ?>
                                        <span class="jsst-dash" aria-hidden="true">&mdash;</span>
                                    <?php } ?></td>
                                <?php } ?>
                                <td class="jsst-col-fit">
                                    <a class="jsst-mark<?php echo ($jsst_helptopic->status == 1) ? ' jsst-mark-on' : ''; ?>" title="<?php echo esc_attr(__('Change status','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url('?page=helptopic&task=changestatus&action=jstask&helptopicid='.$jsst_helptopic->id,'change-status-'.$jsst_helptopic->id)); ?>"><?php echo ($jsst_helptopic->status == 1) ? esc_html(__('Active', 'js-support-ticket')) : esc_html(__('Disabled', 'js-support-ticket')); ?></a>
                                </td>
                                <td class="jsst-col-fit"><?php echo esc_html($jsst_updated); ?></td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=helptopic&task=deletehelptopic&action=jstask&helptopicid='.$jsst_helptopic->id,'delete-helptopic-'.$jsst_helptopic->id)); ?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                    <?php echo wp_kses(JSSTformfield::hidden('fields_ordering_new', '123'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering_for', 'helptopic'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('pagenum_for_ordering', JSSTrequest::getVar('pagenum', 'get', 1)), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-orderbar" style="display: none;">
                        <span class="jsst-orderbar-note"><?php echo esc_html(__('You changed the order of this list.', 'js-support-ticket')); ?></span>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Ordering', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </form>
                <?php JSSTlayout::adminPager(jssupportticket::$jsst_data[1]); ?>
            <?php } else { ?>
                <div class="jsst-card">
                    <?php JSSTlayout::adminEmpty(
                        __('No topics found.', 'js-support-ticket'),
                        __('A topic is what a customer says their ticket is about; it can route the ticket to a department and priority.', 'js-support-ticket'),
                        __('Add topic', 'js-support-ticket'),
                        admin_url('admin.php?page=helptopic&jstlay=addhelptopic')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
