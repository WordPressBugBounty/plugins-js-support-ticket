<?php
if(!defined('ABSPATH')) die('Restricted Access');

JSSTmessage::getMessage(); 
?>
<div id="jsstadmin-wrapper" class="js-ticket-zywrap-errors-page">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Zywrap Errors','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text js-ticket-zywrap-text-error"><?php echo esc_html(__('Zywrap API Errors', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=zywrap&jstlay=zywrap_errors"),"zywrap_errors")); ?>">
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('pagesize', array((object) array('id'=>20,'text'=>20), (object) array('id'=>50,'text'=>50), (object) array('id'=>100,'text'=>100)), isset(jssupportticket::$jsst_data['filter']['pagesize']) ? jssupportticket::$jsst_data['filter']['pagesize'] : 20 ,esc_html(__("Records per page",'js-support-ticket')), array('class' => 'js-form-input-field js-right','onchange'=>'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
            </form>

            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <form class="jsstadmin-form" method="post" action="#">
                    <div class="js-ticket-zywrap-table-responsive">
                        <table id="js-support-ticket-table" class="js-ticket-zywrap-table">
                            <thead>
                                <tr class="js-support-ticket-table-heading">
                                    <th class="left"><?php echo esc_html(__('Action / Wrapper', 'js-support-ticket')); ?></th>
                                    <th class="left"><?php echo esc_html(__('Error Message', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Agent / UID', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Date', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach (jssupportticket::$jsst_data[0] AS $err) { ?>
                                <tr>
                                    <td class="left jsst-left-row">
                                        <strong class="js-ticket-zywrap-wrapper-code"><?php echo esc_html($err->wrapper_code); ?></strong>
                                    </td>
                                    <td class="left">
                                        <div class="js-ticket-zywrap-error-msg-box">
                                            <?php echo esc_html($err->error_message); ?>
                                        </div>
                                    </td>
                                    <td class="js-ticket-zywrap-text-center"><?php echo esc_html($err->agent_id); ?></td>
                                    <td class="js-ticket-zywrap-text-center"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'] . ' H:i', jssupportticketphplib::JSST_strtotime($err->created_at))); ?></td>
                                    <td class="js-ticket-zywrap-text-center">
                                        <a title="<?php echo esc_attr(__('Delete Log', 'js-support-ticket')); ?>" 
                                           class="action-btn" 
                                           onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" 
                                           href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'zywrap', 'task'=>'delete_log', 'action'=>'jstask', 'id'=>$err->id, 'jsstpageid'=>get_the_ID())),'delete_log_'.$err->id)); ?>">
                                            <img alt="<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" />
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </form>
                <?php
                if (isset(jssupportticket::$jsst_data[1])) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                }
            } else { ?>
                <div class="js-ticket-zywrap-empty-success-state">
                    <span class="dashicons dashicons-smiley" aria-hidden="true"></span><br>
                    <?php echo esc_html(__('No API errors logged! System is running perfectly.', 'js-support-ticket')); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>