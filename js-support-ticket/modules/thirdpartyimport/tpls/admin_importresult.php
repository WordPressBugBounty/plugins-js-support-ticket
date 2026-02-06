<?php
if(!defined('ABSPATH'))
    die('Restricted Access');
// JSSTmessage::getMessage();
 ?>
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
                        <li><?php echo esc_html(__('Import Data Report','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Import Data Report', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <?php
            $jsst_results_array = get_option('jsst_import_counts');
            $jsst_plugin_label = 'SupportCandy';
            if(!empty(jssupportticket::$jsst_data['import_for'])){
                $jsst_import_for = jssupportticket::$jsst_data['import_for'];
                if($jsst_import_for == 1){
                    $jsst_plugin_label = 'SupportCandy';
                } elseif($jsst_import_for == 2){
                    $jsst_plugin_label = 'AwesomeSupport';
                } elseif($jsst_import_for == 3){
                    $jsst_plugin_label = 'FluentSupport';
                }
            }
            if(!empty($jsst_results_array)){ ?>
                <table class="jsst-import-data-result-import-table" id="jsst-import-data-result-table">
                    <thead>
                        <tr>
                            <th style="width:50%;"><?php echo esc_html(__('Entity','js-support-ticket')); ?></th>
                            <th style="text-align: center;background-color: #006D3A;width:16.6%;"><?php echo esc_html(__('Imported','js-support-ticket')); ?></th>
                            <th style="text-align: center;background-color: #A75424;width:16.6%;"><?php echo esc_html(__('Similar Found','js-support-ticket')); ?></th>
                            <th style="text-align: center;background-color: #891518;width:16.6%;"><?php echo esc_html(__('Not Imported','js-support-ticket')); ?></th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($jsst_results_array as $jsst_type => $jsst_counts){
                            $jsst_label = ucwords(str_replace(['_', 'jobtype', 'jobapply'], [' ', 'Job Type', 'Job Application'], $jsst_type));
                            $jsst_imported = (int) $jsst_counts['imported'];
                            $jsst_skipped  = (int) $jsst_counts['skipped'];
                            $jsst_failed   = (int) $jsst_counts['failed'];
                            if ($jsst_imported > 0 || $jsst_skipped > 0 || $jsst_failed > 0) {
                                if($jsst_label == 'Field') {
                                    $jsst_show_message = 1;
                                }
                                if($jsst_label == 'Priority') {
                                    $jsst_label = 'Priorities';
                                }elseif($jsst_label == 'Status') {
                                    $jsst_label = 'Statuses';
                                }else{
                                    $jsst_label = $jsst_label.'s';
                                }
                                ?>
                                <tr>
                                    <td><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_label)); ?></td>

                                    <td class="jsst-import-data-result-success">
                                        <?php echo esc_html( $jsst_imported .' '. __('imported.','js-support-ticket') ); ?>
                                    </td>

                                    <td class="jsst-import-data-result-similar">
                                        <?php echo esc_html( $jsst_skipped .' '. __('skipped.','js-support-ticket') ); ?>
                                    </td>

                                    <td class="jsst-import-data-result-failed">
                                        <?php echo esc_html( $jsst_failed .' '. __('failed.','js-support-ticket') ); ?>
                                    </td>
                                </tr>
                                <?php 
                            }
                        } ?>
                    </tbody>
                </table>
                <?php 
                if(!empty($jsst_show_message) && in_array('multiform', jssupportticket::$_active_addons)){ ?>
                    <div class="jsst-import-data-addon-messagewrp">
                        <span class="jsst-import-data-addon-message">
                            <?php echo esc_html(__('Fields are only available in the default form.','js-support-ticket')); ?>
                        </span>
                    </div>
                    <?php 
                }
            } ?>
        </div>
    </div>
</div>
