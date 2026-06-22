<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');
    $jsst_jssupportticket_js ="
    function resetFrom() {
        document.getElementById('error').value = '';
        document.getElementById('jssupportticketform').submit();
    }
    ";
    wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
?>
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
                        <li><?php echo esc_html(__('System Errors','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('System Errors','js-support-ticket')); ?></h1>
            <a class="jsstadmin-add-link button" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=systemerror&task=deletesystemerror&action=jstask&systemerrorid=all','delete-systemerror-all'));?>"><img alt = "<?php echo esc_attr(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /><?php echo esc_html(__('Remove All', 'js-support-ticket')); ?></a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <?php
            if (!empty(jssupportticket::$jsst_data[0])) {
                ?>
                <table id="js-support-ticket-table">
                    <tr class="js-support-ticket-table-heading">
                        <th class="left w70"><?php echo esc_html(__('Error Details', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    <?php
                    foreach (jssupportticket::$jsst_data[0] AS $jsst_systemerror) {
                        ?>
                        <tr>
                            <td class="left w70">
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Error', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php
                                $jsst_raw_error = $jsst_systemerror->error;
                                $jsst_error_data = json_decode($jsst_raw_error, true);

                                if (is_array($jsst_error_data)) : ?>
                                    <div class="jsst-system-error-card">
                                        <div class="jsst-system-error-row">
                                            <span class="jsst-support-system-error-icon-badge system-error-badge-rose"><?php echo esc_html(__('Error', 'js-support-ticket')); ?></span>
                                            <span class="jsst-system-error-txt"><?php echo esc_html(isset($jsst_error_data['error']) ? $jsst_error_data['error'] : __('Unknown Error', 'js-support-ticket')); ?></span>
                                        </div>
                                        <div class="jsst-system-error-row">
                                            <span class="jsst-ai-badge"><?php echo esc_html(__('URL', 'js-support-ticket')); ?></span>
                                            <span class="jsst-system-error-url"><?php echo esc_html(isset($jsst_error_data['url']) ? $jsst_error_data['url'] : __('N/A', 'js-support-ticket')); ?></span>
                                        </div>
                                        <details class="jsst-system-error-details">
                                            <summary>
                                                <span><?php echo esc_html(__('View Query & Trace', 'js-support-ticket')); ?></span>
                                            </summary>
                                            <div class="jsst-system-error-expanded">
                                                <div class="jsst-system-error-group">
                                                    <div class="jsst-system-error-title"><?php echo esc_html(__('Path Execution Trace', 'js-support-ticket')); ?></div>
                                                    <div class="jsst-system-error-code"><?php echo esc_html(isset($jsst_error_data['path']) ? $jsst_error_data['path'] : __('N/A', 'js-support-ticket')); ?></div>
                                                </div>
                                                <div class="jsst-system-error-group">
                                                    <div class="jsst-system-error-title"><?php echo esc_html(__('Database Query', 'js-support-ticket')); ?></div>
                                                    <div class="jsst-system-error-code"><?php echo esc_html(isset($jsst_error_data['query']) ? $jsst_error_data['query'] : __('N/A', 'js-support-ticket')); ?></div>
                                                </div>
                                            </div>
                                        </details>
                                    </div>
                                <?php elseif (!empty($jsst_raw_error)) : ?>
                                    <div class="jsst-system-error-card">
                                        <div class="jsst-system-error-row">
                                            <span class="jsst-support-system-error-icon-badge system-error-badge-rose"><?php echo esc_html(__('Legacy Log', 'js-support-ticket')); ?></span>
                                        </div>
                                        <div class="jsst-system-error-code">
                                            <?php echo esc_html($jsst_raw_error); ?>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <span><?php echo esc_html(__('No error metadata tracking data recorded.', 'js-support-ticket')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Created', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_systemerror->created))); ?>
                            </td>
                            <td>
                                <a title="<?php echo esc_attr(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=systemerror&task=deletesystemerror&action=jstask&systemerrorid='.esc_attr($jsst_systemerror->id),'delete-systemerror-'.$jsst_systemerror->id));?>"><img alt = "<?php echo esc_attr(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                            </td>
                        </tr>
                    <?php } ?>
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
