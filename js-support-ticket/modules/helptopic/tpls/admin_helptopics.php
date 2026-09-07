<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/javascript">
    function resetFrom() {
        document.getElementById('topic').value = '';
        document.getElementById('status').value = '';
        document.getElementById('jssupportticketform').submit();
    }
    jQuery(document).ready(function () {
        jQuery('table#js-support-ticket-table tbody').sortable({
            handle : ".jsst-order-grab-column",
            update  : function () {
                jQuery('.js-form-button').slideDown('slow');
                var abc =  jQuery('table#js-support-ticket-table tbody').sortable('serialize');
                jQuery('input#fields_ordering_new').val(abc);
            }
        });
    });
</script>
<?php
$jsst_status = array((object) array('id' => '1', 'text' => __('Active', 'js-support-ticket')),
    (object) array('id' => '0', 'text' => __('Disabled', 'js-support-ticket'))
);
// The owner column only means something while the Agents add-on is managing
// agents; without it there is nobody to own a topic. (Roadmap 4.0-CORE-20)
$jsst_showowner = in_array('agent', jssupportticket::$_active_addons);
?>
<?php
wp_enqueue_script('jquery-ui-sortable');
$jsst_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);

JSSTmessage::getMessage(); ?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Topics','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Topics', 'js-support-ticket')) ?></h1>
            <a title="<?php echo esc_attr(__('Add','js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=helptopic&jstlay=addhelptopic"><img alt="<?php echo esc_html(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Add Topic', 'js-support-ticket'))?></a>
            <a target="blank" href="https://www.youtube.com/watch?v=-eh4XuDwXoY" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_attr(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                <?php echo esc_html(__('Watch Video','js-support-ticket')); ?>
            </a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=helptopic&jstlay=helptopics"),"helptopic")); ?>">
                <?php echo wp_kses(JSSTformfield::text('topic', jssupportticket::$jsst_data['filter']['topic'], array('placeholder' => __('Topic', 'js-support-ticket'),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('status', $jsst_status, jssupportticket::$jsst_data['filter']['status'], __('Select Status', 'js-support-ticket'), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', __('Search', 'js-support-ticket'), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', __('Reset', 'js-support-ticket'), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('pagesize', array((object) array('id'=>20,'text'=>20), (object) array('id'=>50,'text'=>50), (object) array('id'=>100,'text'=>100)), jssupportticket::$jsst_data['filter']['pagesize'],__("Records per page",'js-support-ticket'), array('class' => 'js-form-input-field js-right','onchange'=>'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php
            if (!empty(jssupportticket::$jsst_data[0])) {
                ?>
                <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=jssupportticket&task=saveordering"),"save-ordering")); ?>">
                <table id="js-support-ticket-table">
                    <thead>
                    <tr class="js-support-ticket-table-heading">
                        <th><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></th>
                        <th class="left"><?php echo esc_html(__('Topic', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Department', 'js-support-ticket')); ?></th>
                        <?php if ($jsst_showowner) { ?>
                            <th><?php echo esc_html(__('Owned By', 'js-support-ticket')); ?></th>
                        <?php } ?>
                        <?php /*
                        <th><?php echo esc_html(__('Auto Response', 'js-support-ticket')); ?></th>
                        */?>
                        <th><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Last Updated', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $jsst_number = 0;
                    $jsst_count = COUNT(jssupportticket::$jsst_data[0]) - 1; //For zero base indexing
                    // Owners for the rows on this page, fetched once by the model.
                    // (Roadmap 4.0-CORE-20)
                    $jsst_owners = isset(jssupportticket::$jsst_data['topic_owners']) ? jssupportticket::$jsst_data['topic_owners'] : array();
                    $jsst_pagenum = JSSTrequest::getVar('pagenum', 'get', 1);
                    $jsst_islastordershow = JSSTpagination::isLastOrdering(jssupportticket::$jsst_data['total'], $jsst_pagenum);
                    foreach (jssupportticket::$jsst_data[0] AS $jsst_helptopic) {
                        $jsst_status = ($jsst_helptopic->status == 1) ? 'good.png' : 'close.png';
                        $jsst_autoreponce = ($jsst_helptopic->autoresponce == 1) ? 'good.png' : 'no.png';
                        if (empty($jsst_helptopic->updated) || $jsst_helptopic->updated == '0000-00-00 00:00:00') {
                            $jsst_updated = __('Not updated', 'js-support-ticket');
                        } else {
                            $jsst_updated = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_helptopic->updated));
                        }
                        ?>
                        <tr id="id_<?php echo esc_attr($jsst_helptopic->id); ?>">
                            <td class="js-textaligncenter jsst-order-grab-column">
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Ordering', 'js-support-ticket')); echo esc_html(" : "); ?>
                                </span>
                                <img alt="<?php echo esc_attr(__('grab','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/list-full.png'?>"/>
                            </td>

                            <td class="left"><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Topic', 'js-support-ticket'));
                        echo " : "; ?></span><?php
                        // One indent step per level, so the tree an administrator
                        // built is the tree they see. (Roadmap 4.0-CORE-20)
                        $jsst_depth = isset($jsst_helptopic->jsst_depth) ? (int) $jsst_helptopic->jsst_depth : 1;
                        if ($jsst_depth > 1) { ?>
                            <span class="jsst-topic-indent"><?php echo esc_html(str_repeat("\xC2\xA0\xC2\xA0\xC2\xA0", $jsst_depth - 1) . "\xe2\x94\x94\xC2\xA0"); ?></span>
                        <?php } ?><a title="<?php echo esc_attr(__('Title','js-support-ticket')); ?>" href="?page=helptopic&jstlay=addhelptopic&jssupportticketid=<?php echo esc_attr($jsst_helptopic->id); ?>"><?php echo esc_html($jsst_helptopic->topic); ?></a><?php
                        if (!empty($jsst_helptopic->isdefault)) { ?>
                            <span class="jsst-topic-default"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></span>
                        <?php } ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Department', 'js-support-ticket'));
                        echo " : "; ?></span><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_helptopic->departmentname)); ?></td>
                            <?php if ($jsst_showowner) { ?>
                                <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Owned By', 'js-support-ticket'));
                                echo " : "; ?></span><?php
                                $jsst_ownerid = isset($jsst_helptopic->staffid) ? (int) $jsst_helptopic->staffid : 0;
                                if ($jsst_ownerid && isset($jsst_owners[$jsst_ownerid])) {
                                    echo esc_html($jsst_owners[$jsst_ownerid]);
                                } else { ?>
                                    <span class="jsst-topic-owner-none"><?php echo esc_html(__('—', 'js-support-ticket')); ?></span>
                                <?php } ?></td>
                            <?php } ?>
                            <?php /*
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Auto Response', 'js-support-ticket'));
                            echo " : "; ?></span> <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/' . $jsst_autoreponce; ?>" /></td>
                            */ ?>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Status', 'js-support-ticket'));
                        echo " : "; ?></span><a title="<?php echo esc_attr(__('Status','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url('?page=helptopic&task=changestatus&action=jstask&helptopicid='.$jsst_helptopic->id,'change-status-'.$jsst_helptopic->id));?>"><img alt="<?php echo esc_attr(__('Status','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/' . esc_attr($jsst_status); ?>"/> </a></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Last Updated', 'js-support-ticket'));
                        echo " : "; ?></span><?php echo esc_html($jsst_updated); ?></td>
                            <td>
                                <a title="<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="action-btn" href="?page=helptopic&jstlay=addhelptopic&jssupportticketid=<?php echo esc_attr($jsst_helptopic->id); ?>"><img alt="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit.png" /></a>
                                <a title="<?php echo esc_attr(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=helptopic&task=deletehelptopic&action=jstask&helptopicid='.$jsst_helptopic->id,'delete-helptopic-'.$jsst_helptopic->id));?>"><img alt="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                            </td>
                        </tr>

                    <?php
                    $jsst_number++;
                }
                ?>
                 </tbody>
                 </table>
                 <?php echo wp_kses(JSSTformfield::hidden('fields_ordering_new', '123'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering_for', 'helptopic'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('pagenum_for_ordering', JSSTrequest::getVar('pagenum', 'get', 1)), JSST_ALLOWED_TAGS); ?>
                    <div class="js-form-button" style="display: none;">
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Save Ordering', 'js-support-ticket'), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </form>
        </div>
            <?php
            if (jssupportticket::$jsst_data[1]) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
            }
        } else {
            JSSTlayout::getNoRecordFound();
        }
        ?>
    </div>
</div>
