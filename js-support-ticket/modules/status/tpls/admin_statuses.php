<?php
if(!defined('ABSPATH'))
    die('Restricted Access');
$jssupportticket_js ="
    function resetFrom() {
        document.getElementById('title').value = '';
        document.getElementById('jssupportticketform').submit();
    }
    jQuery(document).ready(function () {
        jQuery('table#js-support-ticket-table tbody').sortable({
            handle : '.jsst-order-grab-column',
            update  : function () {
                jQuery('.js-form-button').slideDown('slow');
                var abc =  jQuery('table#js-support-ticket-table tbody').sortable('serialize');
                jQuery('input#fields_ordering_new').val(abc);
            }
        });
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Ticket Statuses','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_html(__('Help','js-support-ticket')); ?>">
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Ticket Statuses', 'js-support-ticket')); ?></h1>
            <a title="<?php echo esc_html(__('Add','js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=status&jstlay=addstatus"><img alt="<?php echo esc_html(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Add Ticket Status', 'js-support-ticket')); ?></a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=status&jstlay=statuses"),"statuses")); ?>">
                <?php echo wp_kses(JSSTformfield::text('title', jssupportticket::$_data['filter']['title'], array('placeholder' => esc_html(__('Title', 'js-support-ticket')),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button(esc_html(__('Reset', 'js-support-ticket')), esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('pagesize', array((object) array('id'=>20,'text'=>20), (object) array('id'=>50,'text'=>50), (object) array('id'=>100,'text'=>100)), jssupportticket::$_data['filter']['pagesize'],esc_html(__("Records per page",'js-support-ticket')), array('class' => 'js-form-input-field js-right','onchange'=>'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if (!empty(jssupportticket::$_data[0])) { ?>
                <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=jssupportticket&task=saveordering"),"save-ordering")); ?>">
                    <table id="js-support-ticket-table">
                        <thead>
                        <tr class="js-support-ticket-table-heading">
                            <th><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></th>
                            <th class="left"><?php echo esc_html(__('Title', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Text Color', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Background Color', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        $number = 0;
                        $count = COUNT(jssupportticket::$_data[0]) - 1; //For zero base indexing
                        $pagenum = JSSTrequest::getVar('pagenum', 'get', 1);
                        $islastordershow = JSSTpagination::isLastOrdering(jssupportticket::$_data['total'], $pagenum);
                        foreach (jssupportticket::$_data[0] AS $status) {
                            ?>

                            <tr id="id_<?php echo esc_attr($status->id); ?>">
                                <td class="js-textaligncenter jsst-order-grab-column">
                                    <span class="js-support-ticket-table-responsive-heading">
                                        <?php echo esc_html(__('Ordering', 'js-support-ticket')); echo " : "; ?>
                                    </span>
                                    <img alt="<?php echo esc_html(__('grab','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/list-full.png'?>"/>
                                </td>

                                <td class="left">
                                    <span class="js-support-ticket-table-responsive-heading">
                                        <?php
                                        echo esc_html(__('Title', 'js-support-ticket'));
                                        echo " : ";
                                        ?>
                                    </span>
                                    <a title="<?php echo esc_html(__('Status','js-support-ticket')); ?>" href="?page=status&jstlay=addstatus&jssupportticketid=<?php echo esc_attr($status->id); ?>">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($status->status)); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="js-support-ticket-table-responsive-heading">
                                        <?php
                                        echo esc_html(__('Text Color', 'js-support-ticket'));
                                        echo " : ";
                                        ?>
                                    </span>
                                    <span class="js-ticket-admin-prirrity-color" style="background:<?php echo esc_attr($status->statuscolour); ?>;color:#ffffff;">
                                        <?php echo esc_html($status->statuscolour); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="js-support-ticket-table-responsive-heading">
                                        <?php
                                        echo esc_html(__('Background Color', 'js-support-ticket'));
                                        echo " : ";
                                        ?>
                                    </span>
                                    <span class="js-ticket-admin-prirrity-color" style="background:<?php echo esc_attr($status->statusbgcolour); ?>;color:#ffffff;">
                                        <?php echo esc_html($status->statusbgcolour); ?>
                                    </span>
                                </td>
                                <td>
                                    <a title="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" class="action-btn" href="?page=status&jstlay=addstatus&jssupportticketid=<?php echo esc_attr($status->id); ?>"><img alt="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit.png" /></a>
                                    <?php if($status->sys != 1) { ?>
                                    &nbsp;&nbsp;
                                        <a title="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=status&task=deletestatus&action=jstask&statusid='.esc_attr($status->id),'delete-status-'.$status->id));?>"><img alt="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php
                        $number++;
                    }
                    ?>
                    </tbody>
                    </table>
                        <?php echo wp_kses(JSSTformfield::hidden('fields_ordering_new', '123'), JSST_ALLOWED_TAGS); ?>
                       <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                       <?php echo wp_kses(JSSTformfield::hidden('ordering_for', 'status'), JSST_ALLOWED_TAGS); ?>
                       <?php echo wp_kses(JSSTformfield::hidden('pagenum_for_ordering', JSSTrequest::getVar('pagenum', 'get', 1)), JSST_ALLOWED_TAGS); ?>
                       <div class="js-form-button" style="display: none;">
                           <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Ordering', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                       </div>
                   </form>
                <?php
                if (jssupportticket::$_data[1]) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
                }
            } else {
                JSSTlayout::getNoRecordFound();
            }
            ?>
        </div>
    </div>
</div>
