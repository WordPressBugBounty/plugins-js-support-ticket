<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/javascript">
    function resetFrom() {
        document.getElementById('loggeremail').value = '';
        document.getElementById('jssupportticketform').submit();
    }
</script>
<?php JSSTmessage::getMessage(); ?>
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
                        <li><?php echo esc_html(__('Banned Email Log List','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Banned Email Log List', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=banemaillog&jstlay=banemaillogs"),"ban-email-log")); ?>">
                <?php echo wp_kses(JSSTformfield::text('loggeremail', jssupportticket::$jsst_data['filter']['loggeremail'], array('placeholder' => __('Logger Email', 'js-support-ticket'),'class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', __('Search', 'js-support-ticket'), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', __('Reset', 'js-support-ticket'), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <table id="js-support-ticket-table">
                    <tr class="js-support-ticket-table-heading">
                        <th><?php echo esc_html(__('Title', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Log', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Logger', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Logger Email', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('IP Address', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    <?php
                    foreach (jssupportticket::$jsst_data[0] AS $jsst_email) {
                        ?>
                        <tr>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Title', 'js-support-ticket'));
                    echo " : "; ?></span><?php echo esc_html($jsst_email->title); ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Log', 'js-support-ticket'));
                    echo " : "; ?></span><?php echo esc_html($jsst_email->log); ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Logger', 'js-support-ticket'));
                    echo " : "; ?></span> <?php echo esc_html($jsst_email->logger); ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Logger Email', 'js-support-ticket'));
                    echo " : "; ?></span><?php echo esc_html($jsst_email->loggeremail); ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('IP Address', 'js-support-ticket'));
                    echo " : "; ?></span><?php echo esc_html($jsst_email->ipaddress); ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Created', 'js-support-ticket'));
                    echo " : "; ?></span><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_email->created))); ?></td>
                            <td>
                                <a class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=banemaillog&task=deletebanemaillog&action=jstask&banemaillogid='.$jsst_email->id,'delete-banemaillog-'.$jsst_email->id)) ?>"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                            </td>
                        </tr>

                    <?php }
                ?>
                </table>
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
</div>
