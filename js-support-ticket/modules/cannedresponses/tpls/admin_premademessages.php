<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/javascript">
    function resetFrom() {
        document.getElementById('title').value = '';
        document.getElementById('status').value = '';
        document.getElementById('departmentid').value = '';
        document.getElementById('jssupportticketform').submit();
    }
</script>
<?php
$jsst_status = array(
    (object) array('id' => '1', 'text' => __('Active', 'js-support-ticket')),
    (object) array('id' => '0', 'text' => __('Offline', 'js-support-ticket'))
);
?>
<?php JSSTmessage::getMessage(); ?>
<div id="jsstadmin-wrapper">
    <?php JSSTsidemenu::render(); ?>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Canned Responses','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Canned Responses', 'js-support-ticket')) ?></h1>
            <a title="<?php echo esc_attr(__('Add','js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=cannedresponses&jstlay=addpremademessage"><img alt="<?php echo esc_html(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Add Canned Response', 'js-support-ticket')) ?></a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=cannedresponses&jstlay=premademessages"),"canned-responses")); ?>">
                <?php echo wp_kses(JSSTformfield::text('title', jssupportticket::$jsst_data['filter']['title'], array('placeholder' => __('Title', 'js-support-ticket'),'class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), jssupportticket::$jsst_data['filter']['departmentid'], __('Select Department', 'js-support-ticket'), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('status', $jsst_status, jssupportticket::$jsst_data['filter']['status'], __('Select Status', 'js-support-ticket'), array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', __('Search', 'js-support-ticket'), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::button('reset', __('Reset', 'js-support-ticket'), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php
            if (!empty(jssupportticket::$jsst_data[0])) {
                ?>
                <table id="js-support-ticket-table">
                    <tr class="js-support-ticket-table-heading">
                        <th class="left"><?php echo esc_html(__('Title', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Department', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Last Updated', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    <?php
                    foreach (jssupportticket::$jsst_data[0] AS $jsst_premade) {
                        $jsst_status = ($jsst_premade->status == 1) ? 'good.png' : 'close.png';
                        if (empty($jsst_premade->updated) || $jsst_premade->updated == '0000-00-00 00:00:00') {
                            $jsst_updated = __('Not updated', 'js-support-ticket');
                        } else {
                            $jsst_updated = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_premade->updated));
                        }
                        ?>
                        <tr>
                            <td  class="left"><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Title', 'js-support-ticket'));
                        echo " : "; ?></span><a title="<?php echo esc_attr(__('Title','js-support-ticket')); ?>" href="?page=cannedresponses&jstlay=addpremademessage&jssupportticketid=<?php echo esc_attr($jsst_premade->id); ?>"><?php echo esc_html($jsst_premade->title); ?></a></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Department', 'js-support-ticket'));
                        echo " : "; ?></span><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_premade->departmentname)); ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Status', 'js-support-ticket'));
                        echo " : "; ?></span> <a title="<?php echo esc_attr(__('Status','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url('?page=cannedresponses&task=changestatus&action=jstask&premadeid='.$jsst_premade->id,'change-status-'.$jsst_premade->id));?>"><img alt="<?php echo esc_attr(__('Status','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/' . esc_attr($jsst_status); ?>"/></a></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Last Updated', 'js-support-ticket'));
                        echo " : "; ?></span><?php echo esc_html($jsst_updated) ?></td>
                            <td>
                                <a title="<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="action-btn" href="?page=cannedresponses&jstlay=addpremademessage&jssupportticketid=<?php echo esc_attr($jsst_premade->id); ?>"><img alt="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit.png" /></a>
                                <a title="<?php echo esc_attr(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=cannedresponses&task=deletepremademessage&action=jstask&premademessageid='.$jsst_premade->id,'delete-premademessage-'.$jsst_premade->id));?>"><img alt="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                            </td>
                        </tr>
                    <?php
                }
                ?>
                </table>
                <?php
                if (jssupportticket::$jsst_data[1]) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                }
            } else { // Record Not FOund
                JSSTlayout::getNoRecordFound();
            }
            ?>
        </div>
    </div>
</div>
