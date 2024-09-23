<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');
    $jssupportticket_js ="
    function resetFrom() {
        document.getElementById('error').value = '';
        document.getElementById('jssupportticketform').submit();
    }
    ";
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('System Errors','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('System Errors','js-support-ticket')); ?></h1>
            <a class="jsstadmin-add-link button" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=systemerror&task=deletesystemerror&action=jstask&systemerrorid=all','delete-systemerror'));?>"><img alt="<?php echo esc_html(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /><?php echo esc_html(__('Remove All', 'js-support-ticket')); ?></a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <?php
            if (!empty(jssupportticket::$_data[0])) {
                ?>
                <table id="js-support-ticket-table">
                    <tr class="js-support-ticket-table-heading">
                        <th class="left w70"><?php echo esc_html(__('Error', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    <?php
                    foreach (jssupportticket::$_data[0] AS $systemerror) {
                        $isview = ($systemerror->isview == 1) ? 'close.png' : 'good.png';
                        ?>
                        <tr>
                            <td class="left w70"><span class="js-support-ticket-table-responsive-heading"><?php
                                    echo esc_html(__('Error', 'js-support-ticket'));
                                    echo " : ";
                                    ?></span><?php echo esc_html($systemerror->error); ?></td>
                            <td><span class="js-support-ticket-table-responsive-heading"><?php
                            echo esc_html(__('Created', 'js-support-ticket'));
                            echo " : ";
                                    ?></span><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($systemerror->created))); ?></td>
                            <td>
                                <a title="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=systemerror&task=deletesystemerror&action=jstask&systemerrorid='.esc_attr($systemerror->id),'delete-systemerror'));?>"><img alt="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                            </td>
                        </tr>
                <?php }
                ?>
                </table>
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
