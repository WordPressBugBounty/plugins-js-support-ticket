<?php
if (!defined('ABSPATH')) die('Restricted Access');
$c = JSSTrequest::getVar('page',null,'jsjobs');
$layout = JSSTrequest::getVar('jstlay');
$ff = JSSTrequest::getVar('fieldfor');
$for = JSSTrequest::getVar('for');
$jssupportticket_js ='
    jQuery( function() {
        jQuery( ".accordion" ).accordion({
            heightStyle: "content",
            collapsible: true,
            active: true,
        });
    });
    ';
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
<div id="jsstadmin-logo">
    <a title="<?php echo esc_attr(jssupportticket::$_config['title']); ?>" class="jsst-anchor" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket'));?>">
        <img alt="<?php echo esc_attr(jssupportticket::$_config['title']); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/logo.png" />
    </a>
    <img id="jsstadmin-menu-toggle" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/menu.png" />
</div>
<ul class="jsstadmin-sidebar-menu tree accordion" data-widget="tree" id="accordion">
    <li class="treeview <?php if(($c == 'jssupportticket' && $layout != 'shortcodes' && $layout != 'addonstatus') || $c == 'systemerror' || $c == 'slug') echo 'active'; ?>">
        <a href="admin.php?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/dashboard.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?> </span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'jssupportticket' && ($layout == 'controlpanel' || $layout == '')) echo 'active'; ?>">
                <a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Dashboard','js-support-ticket')); ?>
                </a>
            </li>
            <?php /*
            <li class="<?php if($c == 'jssupportticket' && $layout == 'aboutus') echo 'active'; ?>">
                <a href="?page=jssupportticket&jstlay=aboutus" title="<?php echo esc_html(__('About Us','js-support-ticket')); ?>">
                    <?php echo esc_html(__('About Us','js-support-ticket')); ?>
                </a>
            </li> */?>
            <li class="<?php if($c == 'jssupportticket' && $layout == 'translations') echo 'active'; ?>">
                <a href="?page=jssupportticket&jstlay=translations" title="<?php echo esc_html(__('Translations','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Translations','js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'systemerror') echo 'active'; ?>">
                <a href="?page=systemerror" title="<?php echo esc_html(__('System Errors','js-support-ticket')); ?>">
                    <?php echo esc_html(__('System Errors','js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'slug' && ($layout == 'slug')) echo 'active'; ?>">
                <a href="admin.php?page=slug&jstlay=slug" title="<?php echo esc_html(__('slug','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Slug','js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'ticket' || ($c == 'fieldordering' && $ff == 1 || $c == 'export' || $c == 'multiform') ) echo 'active'; ?>">
        <a href="admin.php?page=ticket" title="<?php echo esc_html(__('Tickets','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Tickets','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/tickets.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Tickets','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <?php 
                $id='';
                $href="?page=ticket&jstlay=addticket&formid=".JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
                if(in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
                    $id="id=multiformpopup";
                    $href='#';
                }
            ?>
            <li class="<?php if($c == 'ticket' && ($layout == '')) echo 'active'; ?>">
                <a href="?page=ticket" title="<?php echo esc_html(__('Tickets','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Tickets','js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'ticket' && ($layout == 'addticket')) echo 'active'; ?>">
                <a <?php echo esc_attr($id); ?> href="<?php echo esc_url($href); ?>" class="?page=ticket&jstlay=addticket&formid=<?php echo esc_html(JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId()) ?>" title="<?php echo esc_html(__('Create Ticket','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Create Ticket','js-support-ticket')); ?>
                </a>
            </li>
            <?php if(!in_array('multiform', jssupportticket::$_active_addons)){ ?>
            <li class="<?php if($c == 'fieldordering') echo 'active'; ?>">
                <a href="?page=fieldordering&fieldfor=1&formid=<?php echo esc_html(JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId()) ?>" title="<?php echo esc_html(__('Fields','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Fields','js-support-ticket')); ?>
                </a>
            </li>
            <?php } ?>
            <?php if(in_array('export', jssupportticket::$_active_addons)){ ?>
                <li class="<?php if($c == 'export') echo 'active'; ?>">
                    <a href="?page=export" title="<?php echo esc_html(__('Export','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Export','js-support-ticket')); ?>
                    </a>
                </li>
            <?php } ?>
            <?php if(in_array('multiform', jssupportticket::$_active_addons)){ ?>
                <li class="<?php if($c == 'multiform' || $c == 'fieldordering') echo 'active'; ?>">
                    <a href="?page=multiform" title="<?php echo esc_html(__('multiform', 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Multiform','js-support-ticket')); ?>
                    </a>
                </li>
            <?php }else{ ?>
                <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-multiform/js-support-ticket-multiform.php');
                    if($plugininfo['availability'] == "1"){
                        $text = $plugininfo['text'];
                        $url = "plugins.php?s=js-support-ticket-multiform&plugin_status=inactive";
                    }elseif($plugininfo['availability'] == "0"){
                        $text = $plugininfo['text'];
                        $url = "https://jshelpdesk.com/product/multi-forms/";
                    }
                ?>
                <li>
                    <a class="jsstadmin-sidebar-submenu-grey" href="javascript:void(0);" title="<?php echo esc_html(__('Multiform','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Multiform','js-support-ticket')); ?>
                    </a>
                    <a class="jsstadmin-sidebar-active-btn" href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>">
                        <?php echo esc_html($text); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </li>
    <?php if ( in_array('agent',jssupportticket::$_active_addons)) { ?>
        <li class="treeview <?php if($c == 'agent' || $c == 'agentautoassign') echo 'active'; ?>">
            <a class="" href="admin.php?page=agent" title="<?php echo esc_html(__('Agents','js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Agents','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/staff.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Agents','js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'agent' && ($layout == '')) echo 'active'; ?>">
                    <a href="?page=agent" title="<?php echo esc_html(__('Agents','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Agents','js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'agent' && ($layout == 'addstaff')) echo 'active'; ?>">
                    <a href="?page=agent&jstlay=addstaff" title="<?php echo esc_html(__('Add Agent' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Agent','js-support-ticket')); ?>
                    </a>
                </li>
                <?php if(in_array('agentautoassign', jssupportticket::$_active_addons)){ ?>
                    <li class="<?php if($c == 'agentautoassign') echo 'active'; ?>">
                        <a href="?page=agentautoassign" title="<?php echo esc_html(__('Agent Auto Assign','js-support-ticket')); ?>">
                            <?php echo esc_html(__('Agent Auto Assign','js-support-ticket')); ?>
                        </a>
                    </li>
                <?php }else{ ?>
                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-agentautoassign/js-support-ticket-agentautoassign.php');
                        if($plugininfo['availability'] == "1"){
                            $text = $plugininfo['text'];
                            $url = "plugins.php?s=js-support-ticket-agentautoassign&plugin_status=inactive";
                        }elseif($plugininfo['availability'] == "0"){
                            $text = $plugininfo['text'];
                            $url = "https://jshelpdesk.com/product/agentautoassign/";
                        }
                    ?>
                    <li>
                        <a class="jsstadmin-sidebar-submenu-grey" href="javascript:void(0);" title="<?php echo esc_html(__('Agent Auto Assign','js-support-ticket')); ?>">
                            <?php echo esc_html(__('Auto Assign', 'js-support-ticket')); ?>
                        </a>
                        <a class="jsstadmin-sidebar-active-btn" href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>">
                            <?php echo esc_html($text); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-agent/js-support-ticket-agent.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-agent&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/agents/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Agents','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/staff.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Agents','js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'agent' && ($layout == '')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Agents','js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <li class="treeview <?php if($c == 'configuration') echo 'active'; ?>">
        <a class="" href="?page=configuration&jsstconfigid=general" title="<?php echo esc_html(__('Configurations','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Configurations','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/config.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Configurations','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'configuration' && $layout != 'cronjoburl') echo 'active'; ?>">
                <a href="?page=configuration&jsstconfigid=general" title="<?php echo esc_html(__('Configurations','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Configurations','js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'configuration' && $layout == 'cronjoburl') echo 'active'; ?>">
                <a href="?page=configuration&jstlay=cronjoburl" title="<?php echo esc_html(__('Cron Job URLs' , 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Cron Job URLs', 'js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'jssupportticket' && $layout == 'shortcodes') echo 'active'; ?>">
        <a class="" href="?page=shortcodes" title="<?php echo esc_html(__('Shortcodes','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Shortcodes','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/short-code.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Short Codes','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'jssupportticket' && $layout == 'shortcodes') echo 'active'; ?>">
                <a href="?page=jssupportticket&jstlay=shortcodes" title="<?php echo esc_html(__('Short Codes', 'js-support-ticket'));; ?>">
                    <?php echo esc_html(__('Short Codes', 'js-support-ticket'));; ?>
                </a>
            </li>

        </ul>
    </li>
    <li class="treeview <?php if($c == 'themes') echo 'active'; ?>">
        <a class="" href="?page=themes" title="<?php echo esc_html(__('Themes','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Themes','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/theme.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Themes','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'themes' && ($layout == 'themes')) echo 'active'; ?>">
                <a href="?page=themes&jstlay=themes" title="<?php echo esc_html(__('Themes','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Themes','js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'reports') echo 'active'; ?>">
        <a class="" href="?page=reports&jstlay=overallreport" title="<?php echo esc_html(__('Reports','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Reports','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/report.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Reports','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'reports' && ($layout == 'overallreport')) echo 'active'; ?>">
                <a href="?page=reports&jstlay=overallreport" title="<?php echo esc_html(__('Overall Statistics','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Overall Statistics','js-support-ticket')); ?>
                </a>
            </li>
            <?php if ( in_array('agent',jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($c == 'reports' && ($layout == 'staffreport') || ($layout == 'staffdetailreport')) echo 'active'; ?>">
                    <a href="?page=reports&jstlay=staffreport" title="<?php echo esc_html(__('Agent Reports','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Agent Reports','js-support-ticket')); ?>
                    </a>
                </li>
            <?php } ?>
            <li class="<?php if($c == 'reports' && ($layout == 'departmentreport') || ($layout == 'departmentdetailreport')) echo 'active'; ?>">
                <a href="?page=reports&jstlay=departmentreport" title="<?php echo esc_html(__('Department Reports','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Department Reports','js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'reports' && ($layout == 'userreport') || ($layout == 'userdetailreport')) echo 'active'; ?>">
                <a href="?page=reports&jstlay=userreport" title="<?php echo esc_html(__('User Reports', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('User Reports', 'js-support-ticket')); ?>
                </a>
            </li>
            <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
                <li class="<?php if($c == 'reports' && ($layout == 'satisfactionreport')) echo 'active'; ?>">
                    <a href="?page=reports&jstlay=satisfactionreport" title="<?php echo esc_html(__('Satisfaction Report','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Satisfaction Report','js-support-ticket')); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </li>
    <?php if(in_array('emailpiping', jssupportticket::$_active_addons)){ ?>
    <li class="treeview <?php if($c == 'emailpiping') echo 'active'; ?>">
        <a href="?page=emailpiping" title="<?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/email-piping-2.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'emailpiping') echo 'active'; ?>">
                <a href="?page=emailpiping" title="<?php echo esc_html(__('Email Piping','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Email Piping','js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <?php }else{ ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-emailpiping/js-support-ticket-emailpiping.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-emailpiping&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/email-piping/";
            } ?>
    <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/email-piping-grey.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'emailpiping') echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Email Piping','js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <li class="treeview <?php if($c == 'gdpr') echo 'active'; ?>">
        <a class="" href="admin.php?page=gdpr&jstlay=gdprfields" title="<?php echo esc_html(__('GDPR','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('GDPR','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/gdpr.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('GDPR','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li style="display: none;" class="<?php if($c == 'gdpr' && ($layout == 'gdprfields') || ($layout == 'addgdprfield')) echo 'active'; ?>">
                <a href="?page=gdpr&jstlay=gdprfields" title="<?php echo esc_html(__('GDPR Fields','js-support-ticket')); ?>">
                    <?php echo esc_html(__('GDPR Fields','js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'gdpr' && ($layout == 'erasedatarequests')) echo 'active'; ?>">
                <a href="?page=gdpr&jstlay=erasedatarequests" title="<?php echo esc_html(__('Erase Data Requests','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Erase Data Requests','js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'premiumplugin') echo 'active'; ?>">
        <a class="" href="admin.php?page=premiumplugin" title="<?php echo esc_html(__('Premium Addons','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Premium Addons','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/ad.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Premium Addons','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'premiumplugin' && ($layout == 'step1') || ($layout == 'step2') || ($layout == 'step3')) echo 'active'; ?>">
                <a href="?page=premiumplugin&jstlay=step1" title="<?php echo esc_html(__('Install Addons','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Install Addons','js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'premiumplugin' && ($layout == 'addonfeatures')) echo 'active'; ?>">
                <a href="?page=premiumplugin&jstlay=addonfeatures" title="<?php echo esc_html(__('Addons List','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Addons List','js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'jssupportticket' && $layout == 'addonstatus') echo 'active'; ?>">
        <a class="" href="admin.php?page=jssupportticket" title="<?php echo esc_html(__('Addons Status','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Addons Status','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/addon-status.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Addons Status','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'jssupportticket' && $layout == 'addonstatus') echo 'active'; ?>">
                <a href="?page=jssupportticket&jstlay=addonstatus" title="<?php echo esc_html(__('Addons Status','js-support-ticket')); ?>">
                    <?php echo esc_html(__('Addons Status','js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'feedback'  || ($c == 'fieldordering' && $ff == 2) ) echo 'active'; ?>">
            <a class="" href="?page=feedback&jstlay=feedbacks" title="<?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/feedback.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Feedbacks','js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'feedback' && ($layout == 'feedbacks')) echo 'active'; ?>">
                    <a href="?page=feedback&jstlay=feedbacks" title="<?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'fieldordering') echo 'active'; ?>">
                    <a href="?page=fieldordering&fieldfor=2" title="<?php echo esc_html(__('Feedback Fields' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Feedback Fields','js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-feedback/js-support-ticket-feedback.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-feedback&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/feedback/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/feedback.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Feedbacks','js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'feedback' && ($layout == 'feedbacks')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <li class="treeview <?php if($c == 'department') echo 'active'; ?>">
        <a class="" href="admin.php?page=department" title="<?php echo esc_html(__('Departments' , 'js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Departments' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/department.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Departments' , 'js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'department' && ($layout == '')) echo 'active'; ?>">
                <a href="?page=department" title="<?php echo esc_html(__('Departments' , 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Departments', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'department' && ($layout == 'adddepartment')) echo 'active'; ?>">
                <a href="?page=department&jstlay=adddepartment" title="<?php echo esc_html(__('Add Department' , 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Add Department', 'js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'priority') echo 'active'; ?>">
        <a class="" href="admin.php?page=priority" title="<?php echo esc_html(__('Priorities' , 'js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Priorities' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/priorities.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Priorities' , 'js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'priority' && ($layout == '')) echo 'active'; ?>">
                <a href="?page=priority" title="<?php echo esc_html(__('Priorities' , 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Priorities', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'priority' && ($layout == 'addpriority')) echo 'active'; ?>">
                <a href="?page=priority&jstlay=addpriority" title="<?php echo esc_html(__('Add Priority' , 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Add Priority', 'js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <?php if(in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'knowledgebase' && ($layout == 'listcategories' || $layout == 'addcategory')) echo 'active'; ?>">
            <a class="" href="admin.php?page=knowledgebase&jstlay=listcategories" title="<?php echo esc_html(__('Categories','js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Categories','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/category.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Categories','js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'knowledgebase' && ($layout == 'listcategories')) echo 'active'; ?>">
                    <a href="?page=knowledgebase&jstlay=listcategories" title="<?php echo esc_html(__('Categories','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Categories', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'knowledgebase' && ($layout == 'addcategory')) echo 'active'; ?>">
                    <a href="?page=knowledgebase&jstlay=addcategory" title="<?php echo esc_html(__('Add Category','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Category', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
        <li class="treeview <?php if($c == 'knowledgebase' && ($layout == 'listarticles' || $layout == 'addarticle')) echo 'active'; ?>">
            <a class="" href="admin.php?page=knowledgebase&jstlay=listarticles" title="<?php echo esc_html(__('Knowledge Base' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Knowledge Base' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/kb.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Knowledge Base' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'knowledgebase' && ($layout == 'listarticles')) echo 'active'; ?>">
                    <a href="?page=knowledgebase&jstlay=listarticles" title="<?php echo esc_html(__('Knowledge Base' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Knowledge Base', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'knowledgebase' && ($layout == 'addarticle')) echo 'active'; ?>">
                    <a href="?page=knowledgebase&jstlay=addarticle" title="<?php echo esc_html(__('Add Knowledge Base' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Knowledge Base', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-knowledgebase/js-support-ticket-knowledgebase.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-knowledgebase&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/knowledge-base/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Categories' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/category.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Categories' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'knowledgebase' && ($layout == 'listcategories')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Categories', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Knowledge Base' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/kb.png'; ?>"/> <span class="jsst_text"><?php echo esc_html(__('Knowledge Base' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'knowledgebase' && ($layout == 'listarticles')) echo 'active'; ?>">
                    <span href="?page=knowledgebase&jstlay=listarticles" title="<?php echo esc_html(__('Knowledge Base' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Knowledge Base', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('download', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'download') echo 'active'; ?>">
            <a class="" href="admin.php?page=download" title="<?php echo esc_html(__('Downloads' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Downloads' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/download.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Downloads' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'download' && ($layout == '')) echo 'active'; ?>">
                    <a href="?page=download" title="<?php echo esc_html(__('Downloads' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Downloads', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'download' && ($layout == 'adddownload')) echo 'active'; ?>">
                    <a href="?page=download&jstlay=adddownload" title="<?php echo esc_html(__('Add Download' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Download', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-download/js-support-ticket-download.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-download&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/download/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Download' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/download.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Download' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'download' && ($layout == '')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Downloads', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('announcement', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'announcement') echo 'active'; ?>">
            <a class="" href="admin.php?page=announcement" title="<?php echo esc_html(__('Announcements' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Announcements' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/announcements.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Announcements' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'announcement' && ($layout == '')) echo 'active'; ?>">
                    <a href="?page=announcement" title="<?php echo esc_html(__('Announcements' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Announcements', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'announcement' && ($layout == 'addannouncement')) echo 'active'; ?>">
                    <a href="?page=announcement&jstlay=addannouncement" title="<?php echo esc_html(__('Add Announcement' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Announcement', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-announcement/js-support-ticket-announcement.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-announcement&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/announcements/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Announcements' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/announcements.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Announcements' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'announcement' && ($layout == '')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Announcements', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('faq', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'faq') echo 'active'; ?>">
            <a class="" href="admin.php?page=faq" title="<?php echo esc_html(__('FAQ\'S' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('FAQ\'S' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/faq.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('FAQ\'S' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'faq' && ($layout == '')) echo 'active'; ?>">
                    <a href="?page=faq" title="<?php echo esc_html(__("FAQ'S" , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__("FAQ'S", 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'faq' && ($layout == 'addfaq')) echo 'active'; ?>">
                    <a href="?page=faq&jstlay=addfaq" <?php echo esc_html(__('Add FAQ' , 'js-support-ticket')); ?>>
                        <?php echo esc_html(__( 'Add FAQ', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-faq/js-support-ticket-faq.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-faq&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/faq/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('FAQs' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/faq.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('FAQs' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'faq' && ($layout == '')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__("FAQ'S", 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('helptopic', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'helptopic') echo 'active'; ?>">
            <a class="" href="admin.php?page=helptopic" title="<?php echo esc_html(__('Help Topics' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Help Topics' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/help-topic.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Help Topics' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'helptopic' && ($layout == '')) echo 'active'; ?>">
                    <a href="?page=helptopic" title="<?php echo esc_html(__('Help Topics' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Help Topics', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'helptopic' && ($layout == 'addhelptopic')) echo 'active'; ?>">
                    <a href="?page=helptopic&jstlay=addhelptopic" tite="<?php echo esc_html(__('Add Help Topic' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Help Topic', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-helptopic/js-support-ticket-helptopic.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-helptopic&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/helptopic/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Helptopics' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/help-topic.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Helptopics' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'helptopic' && ($layout == '')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Help Topics', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <li class="treeview <?php if($c == 'email') echo 'active'; ?>">
        <a class="" href="admin.php?page=email" title="<?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/system-email.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'email' && ($layout == '')) echo 'active'; ?>">
                <a href="?page=email" title="<?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('System Emails', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'email' && ($layout == 'addemail')) echo 'active'; ?>">
                <a href="?page=email&jstlay=addemail" title="<?php echo esc_html(__('Add Email' , 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Add Email', 'js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <?php if(in_array('cannedresponses', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'cannedresponses') echo 'active'; ?>">
            <a class="" href="admin.php?page=cannedresponses" title="<?php echo esc_html(__('Canned Responses' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Canned Responses' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/canned-response.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Canned Responses' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'cannedresponses' && ($layout == '')) echo 'active'; ?>">
                    <a href="?page=cannedresponses" title="<?php echo esc_html(__('Canned Responses' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Canned Responses', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'cannedresponses' && ($layout == 'addpremademessage')) echo 'active'; ?>">
                    <a href="?page=cannedresponses&jstlay=addpremademessage" title="<?php echo esc_html(__('Add Canned Response' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Canned Response', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-cannedresponses/js-support-ticket-cannedresponses.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-cannedresponses&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/canned-responses/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Canned Responses' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/canned-response.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Canned Responses' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'cannedresponses' && ($layout == '')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Canned Responses', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if ( in_array('agent',jssupportticket::$_active_addons)) { ?>
        <li class="treeview <?php if($c == 'role') echo 'active'; ?>">
            <a class="" href="admin.php?page=role" title="<?php echo esc_html(__('Roles' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Roles' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/role.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Roles' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'role' && ($layout == '')) echo 'active'; ?>">
                    <a href="?page=role" title="<?php echo esc_html(__('Roles' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Roles', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'role' && ($layout == 'addrole')) echo 'active'; ?>">
                    <a href="?page=role&jstlay=addrole" title="<?php echo esc_html(__('Add Role' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Role', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-agent/js-support-ticket-agent.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-agent&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/agents/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Roles' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/role.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Roles' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'role' && ($layout == '')) echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Roles', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('mail', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'mail') echo 'active'; ?>">
            <a class="" href="admin.php?page=mail" title="<?php echo esc_html(__('Mail' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Mail' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/mails.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Mail' , 'js-support-ticket')); ?></span>
            </a>
           <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'mail') echo 'active'; ?>">
                    <a href="?page=mail" title="<?php echo esc_html(__('Mail' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Mail', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-mail/js-support-ticket-mail.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-mail&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/internal-mail/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Mail' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/mails.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Mail' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'mail') echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Mail', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('banemail', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'banemail' || $c == 'banemaillog') echo 'active'; ?>">
            <a class="" href="admin.php?page=banemail" title="<?php echo esc_html(__('Banned Emails' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Banned Emails' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/ban.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Banned Emails' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'banemail') echo 'active'; ?>">
                    <a href="?page=banemail" title="<?php echo esc_html(__('Banned Emails' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Banned Emails', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'banemaillog') echo 'active'; ?>">
                    <a href="?page=banemaillog" title="<?php echo esc_html(__('Banned Email Log List', 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Banned Email Log List', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-banemail/js-support-ticket-banemail.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-banemail&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/ban-email/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Ban Emails', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/ban.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Ban Emails' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'banemail') echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Ban Emails', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('emailcc', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'emailcc') echo 'active'; ?>">
            <a class="" href="admin.php?page=emailcc" title="<?php echo esc_html(__('Emial CC' , 'js-support-ticket')); ?>">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Emial CC' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/emailcc.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Email CC' , 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'emailcc' && $layout != 'addemailcc') echo 'active'; ?>">
                    <a href="?page=emailcc" title="<?php echo esc_html(__('Emial CC' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Email CC', 'js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($c == 'emailcc' && $layout == 'addemailcc') echo 'active'; ?>">
                    <a href="?page=emailcc&jstlay=addemailcc" title="<?php echo esc_html(__('Add Emial CC', 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Add Email CC', 'js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-emailcc/js-support-ticket-emailcc.php');
            if($plugininfo['availability'] == "1"){
                $text = $plugininfo['text'];
                $url = "plugins.php?s=js-support-ticket-emailcc&plugin_status=inactive";
            }elseif($plugininfo['availability'] == "0"){
                $text = $plugininfo['text'];
                $url = "https://jshelpdesk.com/product/email-cc/";
            } ?>
        <li class="disabled-menu treeview">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Email CC', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu-grey/emailcc.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Email CC' , 'js-support-ticket')); ?></span>
            <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'emailcc') echo 'active'; ?>">
                    <span>
                        <?php echo esc_html(__('Email CC', 'js-support-ticket')); ?>
                    </span>
                    <a href="<?php echo esc_url($url); ?>" class="jsst_js-install-btn" title="<?php echo esc_attr($text); ?>"><?php echo esc_html($text); ?></a>
                </li>
            </ul>
        </li>
    <?php } ?>
    <li class="treeview <?php if($c == 'emailtemplate') echo 'active'; ?>">
        <a class="" href="admin.php?page=emailtemplate" title="<?php echo esc_html(__('Email Templates' , 'js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Email Templates' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/email-template.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('Email Templates' , 'js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'emailtemplate' && $for == 'tk-nw') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=tk-nw" title="<?php echo esc_html(__('New Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('New Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'sntk-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=sntk-tk" title="<?php echo esc_html(__('Agent Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Agent Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <?php /*<li class="<?php if($c == 'emailtemplate' && $for == 'ew-md') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ew-md" title="<?php echo esc_html(__('New Department', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('New Department', 'js-support-ticket')); ?>
                </a>
            </li>*/ ?>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ew-sm') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ew-sm" title="<?php echo esc_html(__('New Agent', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('New Agent', 'js-support-ticket')); ?>
                </a>
            </li>
            <?php /*<li class="<?php if($c == 'emailtemplate' && $for == 'ew-ht') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ew-ht" title="<?php echo esc_html(__('New Help Topic', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('New Help Topic', 'js-support-ticket')); ?>
                </a>
            </li> */ ?>
            <li class="<?php if($c == 'emailtemplate' && $for == 'rs-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=rs-tk" title="<?php echo esc_html(__('Reassign Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Reassign Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'cl-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=cl-tk" title="<?php echo esc_html(__('Close Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Close Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'dl-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=dl-tk" title="<?php echo esc_html(__('Delete Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Delete Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'mo-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=mo-tk" title="<?php echo esc_html(__('Mark Overdue', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Mark Overdue', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'be-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=be-tk" title="<?php echo esc_html(__('Ban Email', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Ban Email', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'be-trtk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=be-trtk" title="<?php echo esc_html(__('Ban email try to create ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Ban email try to create ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'dt-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=dt-tk" title="<?php echo esc_html(__('Department Transfer', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Department Transfer', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ebct-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ebct-tk" title="<?php echo esc_html(__('Ban Email and Close Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Ban Email and Close Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ube-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ube-tk" title="<?php echo esc_html(__('Unban Email', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Unban Email', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'rsp-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=rsp-tk" title="<?php echo esc_html(__('Response Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Response Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'rpy-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=rpy-tk" title="<?php echo esc_html(__('Reply Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Reply Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'tk-ew-ad') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=tk-ew-ad" title="<?php echo esc_html(__('New Ticket Admin Alert', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('New Ticket Admin Alert', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'lk-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=lk-tk" title="<?php echo esc_html(__('Lock Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Lock Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ulk-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ulk-tk" title="<?php echo esc_html(__('Unlock Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Unlock Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'minp-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=minp-tk" title="<?php echo esc_html(__('In Progress Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('In Progress Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'pc-tk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=pc-tk" title="<?php echo esc_html(__('Ticket priority is changed by', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Ticket priority is changed by', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ml-ew') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ml-ew" title="<?php echo esc_html(__('New Mail Received', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('New Mail Received', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ml-rp') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=ml-rp" title="<?php echo esc_html(__('New Mail Message Received', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('New Mail Message Received', 'js-support-ticket')); ?>
                </a>
            <li class="<?php if($c == 'emailtemplate' && $for == 'fd-bk') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=fd-bk" title="<?php echo esc_html(__('Feedback Email To User', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('Feedback Email To User', 'js-support-ticket')); ?>
                </a>
            </li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'no-rp') echo 'active'; ?>">
                <a href="?page=emailtemplate&for=no-rp" title="<?php echo esc_html(__('User Reply On Closed Ticket', 'js-support-ticket')); ?>">
                    <?php echo esc_html(__('User Reply On Closed Ticket', 'js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'jssupportticket' && $layout == 'help') echo 'active'; ?>">
        <a href="?page=jssupportticket&jstlay=help" title="<?php echo esc_html(__('help','js-support-ticket')); ?>">
            <img class="jsst_menu-icon" alt="<?php echo esc_html(__('help' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/left-icons/menu/help.png'; ?>"/>
            <span class="jsst_text"><?php echo esc_html(__('help','js-support-ticket')); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'jssupportticket' && $layout == 'help') echo 'active'; ?>">
                <a href="?page=jssupportticket&jstlay=help" title="<?php echo esc_html(__('help','js-support-ticket')); ?>">
                    <?php echo esc_html(__('help','js-support-ticket')); ?>
                </a>
            </li>
        </ul>
    </li>
</ul>
<?php if(in_array('multiform', jssupportticket::$_active_addons)){ ?>
    <div id="multiformpopupblack" style="display:none;"></div>
    <div id="multiformpopup" class="" style="display:none;"><!-- Select User Popup -->
        <div class="jsst-multiformpopup-header">
            <div class="multiformpopup-header-text">
                <?php echo esc_html(__('Select Form','js-support-ticket')); ?>
            </div>
            <div class="multiformpopup-header-close-img">
                <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png">
            </div>
        </div>
        <div id="records">
            <div id="records-inner">
                <div class="js-staff-searc-desc">
                    <?php echo esc_html(__('No Record Found','js-support-ticket')); ?>
                </div>
            </div>
        </div>
    </div>
<?php }
$jssupportticket_js ='
    var cookielist = document.cookie.split(";");
    for (var i=0; i<cookielist.length; i++) {
        if (cookielist[i].trim() == "jsst_collapse_admin_menu=1") {
            jQuery("#jsstadmin-wrapper").addClass("menu-collasped-active");
            break;
        }
    }

    jQuery(document).ready(function(){
        var pageWrapper = jQuery("#jsstadmin-wrapper");
        var sideMenuArea = jQuery("#jsstadmin-leftmenu");
        jQuery("#jsstadmin-menu-toggle").on("click", function () {
            if (pageWrapper.hasClass("menu-collasped-active")) {
                pageWrapper.removeClass("menu-collasped-active");
                document.cookie = "jsst_collapse_admin_menu=0; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            }else{
                pageWrapper.addClass("menu-collasped-active");
                document.cookie = "jsst_collapse_admin_menu=1; expires=Sat, 01 Jan 2050 00:00:00 UTC; path=/";
            }

        });

        // to set anchor link active on menu collpapsed
        jQuery(".jsstadmin-leftmenu .jsstadmin-sidebar-menu li.treeview a").on("click", function() {
            if (!(pageWrapper.hasClass("menu-collasped-active"))) {
                window.location.href = jQuery(this).attr("href");
            }
        })
    });
    jQuery(document).ready(function ($) {

        jQuery("a#multiformpopup").click(function (e) {
            e.preventDefault();
            var url = jQuery("a#multiformpopup").prop("class");
            jQuery("div#multiformpopupblack").show();
            var ajaxurl ="'.admin_url('admin-ajax.php').'";
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "multiform", task: "getmultiformlistajax", url:url, "_wpnonce":"'.esc_attr(wp_create_nonce("get-multi-form-list-ajax")).'"}, function (data) {
                if(data){
                    jQuery("div#records").html("");
                    jQuery("div#records").html(data);
                    // setUserLink(); generate error
                }
            });
            jQuery("div#multiformpopup").slideDown("slow");
        });

        jQuery("div#multiformpopupblack , div.multiformpopup-header-close-img").click(function (e) {
            jQuery("div#multiformpopup").slideUp("slow", function () {
                jQuery("div#multiformpopupblack").hide();
            });
        });
    });

    function makeFormSelected(divelement){
        jQuery("div.js-ticket-multiform-row").removeClass("selected");
        jQuery(divelement).addClass("selected");  
    }
    function makeMultiFormUrl(id){
        var oldUrl = jQuery("a.js-multiformpopup-link").attr("id"); // Get current url
        var newUrl = oldUrl+"&formid="+id; // Create new url
        window.location.href = newUrl;
    }
';
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
