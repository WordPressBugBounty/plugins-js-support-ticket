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
                        <li><?php echo esc_html(__('Import Data','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Import Data', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <?php
            $jsst_count_for = jssupportticket::$jsst_data['count_for'];
            $jsst_entity_counts = [];
            if($jsst_count_for > 0 &&  !empty(jssupportticket::$jsst_data['entity_counts'])){
                $jsst_entity_counts = jssupportticket::$jsst_data['entity_counts'];
            }
            // plugins for which we support importing data
            $jsst_plguins_array = [];

            // plugin data
            $jsst_plguins_array['awesome-support'] = [];
            $jsst_plguins_array['awesome-support']['name'] = esc_html('Awesome Support');
            $jsst_plguins_array['awesome-support']['path'] = "awesome-support/awesome-support.php"; // needed to check if plugin is active
            $jsst_plguins_array['awesome-support']['internalid'] = 2; // value used to identfy the plugin

            // plugin data
            $jsst_plguins_array['supportcandy'] = [];
            $jsst_plguins_array['supportcandy']['name'] = esc_html('SupportCandy');
            $jsst_plguins_array['supportcandy']['path'] = "supportcandy/supportcandy.php";  // needed to check if plugin is active
            $jsst_plguins_array['supportcandy']['internalid'] = 1; // value used to identfy the plugin

            // plugin data
            $jsst_plguins_array['fluent-support'] = [];
            $jsst_plguins_array['fluent-support']['name'] = esc_html('Fluent Support');
            $jsst_plguins_array['fluent-support']['path'] = "fluent-support/fluent-support.php"; // needed to check if plugin is active
            $jsst_plguins_array['fluent-support']['internalid'] = 3; // value used to identfy the plugin


            foreach ($jsst_plguins_array as $jsst_plugin) {
                // check if Plugin is active
                if($jsst_count_for != $jsst_plugin['internalid']) {
                    $jsst_extr_clss = 'jsst-plugin-notinstalled';
                    if ( is_plugin_active( $jsst_plugin['path'] )) {
                        $jsst_extr_clss = '';
                    } ?>
                    <div class="jsst-plugins-imprt-datasec <?php echo esc_attr($jsst_extr_clss);?>">
                        <span class="jsst-plugins-imprt-data-plgnnme"><?php echo esc_html($jsst_plugin['name']); ?></span>
                        <?php if($jsst_extr_clss != ''){ ?>
                            <span class="jsst-plugins-imprt-databtn">
                                <img class="jsst-plugins-imprterror-image" alt = "<?php echo esc_attr(__('icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/imprt-icon.png" />
                                <?php echo esc_html(__('Plugin not installed','js-support-ticket')); ?>
                            </span>
                        <?php }else{ ?>
                            <a class="jsst-plugins-imprt-databtn" href="<?php echo esc_url_raw(admin_url("admin.php?page=thirdpartyimport&jstlay=importdata&selected_plugin=".$jsst_plugin['internalid'])); ?>" title="<?php echo esc_attr(__('Fetch Data','js-support-ticket')); ?>"><?php echo esc_html(__('Fetch Data','js-support-ticket')); ?></a>
                        <?php } ?>
                    </div>
                    <?php
                } else {
                    if(!empty($jsst_entity_counts)){ ?>
                        <div class="jsst-singleplugin-imprt-data-sec">
                            <span class="jsst-singleplugin-imprt-datatitle">
                                <?php echo esc_html($jsst_plugin['name']); ?>
                            </span>
                            <?php foreach ($jsst_entity_counts as $jsst_entity_val => $jsst_entity_val) {
                                $jsst_entity_val = ucwords(str_replace('_', ' ', $jsst_entity_val));
                                if($jsst_entity_val == 'Priority' && $jsst_entity_val > 1){
                                    $jsst_entity_val = 'Priorities';
                                }elseif($jsst_entity_val == 'Status' && $jsst_entity_val > 1){
                                    $jsst_entity_val = 'Statuses';
                                }elseif($jsst_entity_val > 1){
                                    $jsst_entity_val = $jsst_entity_val.'s';
                                }
                                $jsst_extr_clss = '';
                                if (in_array(strtolower($jsst_entity_val), ['agent', 'agent role', 'agents', 'agent roles'])) {
                                    if(!in_array('agent', jssupportticket::$_active_addons)){
                                        $jsst_extr_clss = 'jsst-singleplugin-imprt-data-addonnot-instllwrp';
                                    }
                                } elseif (in_array(strtolower($jsst_entity_val), ['canned response', 'canned responses'])) {
                                    if(!in_array('cannedresponses', jssupportticket::$_active_addons)){
                                        $jsst_extr_clss = 'jsst-singleplugin-imprt-data-addonnot-instllwrp';
                                    }
                                } ?>
                                <div class="jsst-singleplugin-imprt-datadisc <?php echo esc_attr($jsst_extr_clss);?>">
                                    <?php echo esc_html($jsst_entity_val).'&nbsp;'.esc_html(jssupportticket::JSST_getVarValue($jsst_entity_val)).'&nbsp;'.esc_html(__('found','js-support-ticket'));

                                    if (in_array(strtolower($jsst_entity_val), ['ticket', 'tickets'])) {
                                        if($jsst_plugin["internalid"] != 3 && !in_array('privatecredentials', jssupportticket::$_active_addons)){ ?>
                                            <br>
                                            <span class="jsst-import-data-addon-message"><?php echo esc_html(__('Private Credentials Addon missing, ticket private credentials data will not be imported!','js-support-ticket')); ?></span>
                                            <?php
                                        }
                                        if(!in_array('tickethistory', jssupportticket::$_active_addons)){ ?>
                                            <br>
                                            <span class="jsst-import-data-addon-message"><?php echo esc_html(__('Ticket History Addon missing, full ticket history will not be imported!','js-support-ticket')); ?></span>
                                            <?php
                                        }
                                        if(!in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                            <br>
                                            <span class="jsst-import-data-addon-message"><?php echo esc_html(__('Time Tracking Addon missing, ticket time tracking data will not be imported!','js-support-ticket')); ?></span>
                                            <?php
                                        }
                                        if(!in_array('note', jssupportticket::$_active_addons)){ ?>
                                            <br>
                                            <span class="jsst-import-data-addon-message"><?php echo esc_html(__('Note Addon missing, ticket internal note  data will not be imported!','js-support-ticket')); ?></span>
                                            <?php
                                        }
                                    }
                                    if($jsst_extr_clss != ''){ ?>
                                        <span class="jsst-singleplugin-imprt-data-addonnot-instll">
                                            <img class="jsst-plugins-imprterror-image" alt = "<?php echo esc_attr(__('icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/imprt-icon.png" />
                                            <?php echo esc_html(__('Addon not installed please install addon first.','js-support-ticket')); ?>
                                        </span>
                                        <?php
                                    } ?>
                                </div>
                                <?php
                            } ?>
                            <div class="jsst-singleplugin-imprt-databtn-wrp">
                                <a class="jsst-singleplugin-imprt-databtn" title="<?php echo esc_attr(__('Import Data','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url('?page=thirdpartyimport&task=importPluginData&action=jstask&&selected_plugin='.$jsst_plugin["internalid"], 'importPluginData'));?>"><?php echo esc_html(__('Import Data','js-support-ticket')); ?></a>
                            </div>
                        </div><?php
                    } else { ?>
                        <div class="jsst-singleplugin-imprt-data-sec">
                            <span class="jsst-singleplugin-imprt-datatitle">
                                <?php echo esc_html($jsst_plugin['name']); ?>
                            </span>
                            <div class="jsst-singleplugin-imprt-datadisc">
                                <?php echo esc_html(__('No Data Found!','js-support-ticket')); ?>
                            </div>
                        </div>
                        <?php
                    }
                }
            } ?>
        </div>
    </div>
</div>
