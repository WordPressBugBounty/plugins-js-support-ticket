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
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Import Data Report', 'js-support-ticket'),
        )); ?>
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
                                        <?php echo esc_html( $jsst_imported .' '. __('Imported','js-support-ticket') ); ?>
                                    </td>

                                    <td class="jsst-import-data-result-similar">
                                        <?php echo esc_html( $jsst_skipped .' '. __('Skipped','js-support-ticket') ); ?>
                                    </td>

                                    <td class="jsst-import-data-result-failed">
                                        <?php echo esc_html( $jsst_failed .' '. __('Failed','js-support-ticket') ); ?>
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
