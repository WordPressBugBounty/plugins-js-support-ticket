<?php
/**
 * JS Support Ticket - Admin Left Menu
 *
 * This file contains the HTML for the admin sidebar menu.
 *
 * @package js-support-ticket
 * @subpackage templates
 */

if (!defined('ABSPATH')) {
    die('Restricted Access');
}
// Get current page and layout from request
$c = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : null;
$layout = isset($_GET['jstlay']) ? sanitize_text_field($_GET['jstlay']) : null;
$ff = isset($_GET['fieldfor']) ? sanitize_text_field($_GET['fieldfor']) : null;
$for = isset($_GET['for']) ? sanitize_text_field($_GET['for']) : null;

// Inline script for menu accordion and collapse functionality.
$jssupportticket_js = '
    jQuery( function() {
        jQuery( ".accordion" ).accordion({
            heightStyle: "content",
            collapsible: true,
            active: true,
        });
    });

    var cookielist = document.cookie.split(";");
    for (var i=0; i<cookielist.length; i++) {
        if (cookielist[i].trim() == "jsst_collapse_admin_menu=1") {
            jQuery("body").addClass("menu-collapsed");
            break;
        }
    }

    jQuery(document).ready(function(){
        var pageWrapper = jQuery("body");
        var sideMenuArea = jQuery("#jsstadmin-leftmenu");
        jQuery("#jsstadmin-menu-toggle").on("click", function (e) {
            e.preventDefault();
            if (pageWrapper.hasClass("menu-collapsed")) {
                pageWrapper.removeClass("menu-collapsed");
                document.cookie = "jsst_collapse_admin_menu=0; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            } else {
                pageWrapper.addClass("menu-collapsed");
                document.cookie = "jsst_collapse_admin_menu=1; expires=Sat, 01 Jan 2050 00:00:00 UTC; path=/";
            }
        });
        
        jQuery(".jsstadmin-sidebar-menu .treeview > a").on("click", function(event) {
            const parentLi = jQuery(this).parent();
            if (parentLi.hasClass("disabled-menu")) {
                event.preventDefault();
                return;
            }

            if (jQuery("body").hasClass("menu-collapsed")) {
                event.preventDefault();
            } else {
                if (parentLi.hasClass("treeview")) {
                    event.preventDefault();
                    if(parentLi.hasClass("active")) {
                        parentLi.removeClass("active");
                    } else {
                        parentLi.addClass("active");
                    }
                }
            }
        });
    });
';
// wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
?>
<div id="jsstadmin-logo">
    <a title="<?php echo esc_attr__('JS HelpDesk System', 'js-support-ticket'); ?>" class="jsst-anchor" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>">
        <div class="logo-icon">
            <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/left-icons/menu/logo.png">
        </div>
        <span class="logo-text"><?php echo esc_attr__('JS HelpDesk', 'js-support-ticket'); ?></span>
    </a>
    <svg id="jsstadmin-menu-toggle" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
    </svg>
</div>
<ul class="jsstadmin-sidebar-menu tree accordion">

    <li class="menu-header"><?php echo esc_html__('Main', 'js-support-ticket'); ?></li>
    <li class="treeview <?php if(($c == 'jssupportticket' && $layout != 'shortcodes' && $layout != 'addonstatus') || $c == 'systemerror' || $c == 'slug') echo 'active'; ?> menu-item-dashboard">
        <a href="#" title="<?php echo esc_attr__('Dashboard', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg><span class="jsst_text"><?php echo esc_html__('Dashboard', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'jssupportticket' && ($layout == 'controlpanel' || $layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>" title="<?php echo esc_attr__('Dashboard', 'js-support-ticket'); ?>"><?php echo esc_html__('Dashboard', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'jssupportticket' && $layout == 'translations') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=translations')); ?>" title="<?php echo esc_attr__('Translations', 'js-support-ticket'); ?>"><?php echo esc_html__('Translations', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'systemerror') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=systemerror')); ?>" title="<?php echo esc_attr__('System Errors', 'js-support-ticket'); ?>"><?php echo esc_html__('System Errors', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'slug' && ($layout == 'slug')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=slug&jstlay=slug')); ?>" title="<?php echo esc_attr__('Slug', 'js-support-ticket'); ?>"><?php echo esc_html__('Slug', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>

    <li class="menu-header"><?php echo esc_html__('Ticket Management', 'js-support-ticket'); ?></li>
    <li class="treeview <?php if($c == 'ticket' || ($c == 'fieldordering' && $ff == 1) || $c == 'export' || $c == 'multiform') echo 'active'; ?> menu-item-tickets">
        <a href="#" title="<?php echo esc_attr__('Tickets', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75h-9a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H16.5m-3.75 0V4.5m0 2.25V4.5m0 2.25v2.25m0-2.25v2.25m0-2.25V4.5m-3.75 2.25v2.25m0-2.25V4.5m0 2.25v2.25m0-2.25V4.5" /></svg><span class="jsst_text"><?php echo esc_html__('Tickets', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'ticket' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=ticket')); ?>" title="<?php echo esc_attr__('Tickets', 'js-support-ticket'); ?>"><?php echo esc_html__('Tickets', 'js-support-ticket'); ?></a></li>
            <?php
            $href = admin_url('admin.php?page=ticket&jstlay=addticket&formid=' . JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId());
            $extra_attributes = '';
            if (in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1) {
                $href = '#';
                $extra_attributes = "id=multiformpopup";
            }
            ?>
            <li class="<?php if($c == 'ticket' && ($layout == 'addticket')) echo 'active'; ?>"><a <?php echo $extra_attributes; ?> href="<?php echo esc_url($href); ?>" class="?page=ticket&jstlay=addticket&formid=<?php echo esc_html(JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId()) ?>" title="<?php echo esc_attr__('Create Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Create Ticket', 'js-support-ticket'); ?></a></li>
            <?php if (!in_array('multiform', jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($c == 'fieldordering') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=fieldordering&fieldfor=1&formid=' . JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId())); ?>" title="<?php echo esc_attr__('Fields', 'js-support-ticket'); ?>"><?php echo esc_html__('Fields', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php if (in_array('export', jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($c == 'export') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=export')); ?>" title="<?php echo esc_attr__('Export', 'js-support-ticket'); ?>"><?php echo esc_html__('Export', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php if (in_array('multiform', jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($c == 'multiform' || $c == 'fieldordering') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=multiform')); ?>" title="<?php echo esc_attr__('Multiform', 'js-support-ticket'); ?>"><?php echo esc_html__('Multiform', 'js-support-ticket'); ?></a></li>
            <?php } else {
                $plugininfo = JSSTCheckPluginInfo('js-support-ticket-multiform/js-support-ticket-multiform.php');
                $text = $plugininfo['availability'] == '1' ? $plugininfo['text'] : $plugininfo['text'];
                $url = $plugininfo['availability'] == '1' ? 'plugins.php?s=js-support-ticket-multiform&plugin_status=inactive' : 'https://jshelpdesk.com/product/multi-forms/';
                ?>
                <li class="disabled-menu">
                    <a class="jsstadmin-sidebar-submenu-grey" href="javascript:void(0);" title="<?php echo esc_attr__('Multiform', 'js-support-ticket'); ?>"><?php echo esc_html__('Multiform', 'js-support-ticket'); ?></a>
                </li>
            <?php } ?>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'department') echo 'active'; ?> menu-item-departments">
        <a href="#" title="<?php echo esc_attr__('Departments', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 11.25h6m-6 4.5h6M6.75 21v-2.25a2.25 2.25 0 012.25-2.25h6a2.25 2.25 0 012.25 2.25V21" /></svg><span class="jsst_text"><?php echo esc_html__('Departments', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'department' && ($layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=department')); ?>" title="<?php echo esc_attr__('Departments', 'js-support-ticket'); ?>"><?php echo esc_html__('Departments', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'department' && ($layout == 'adddepartment')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=department&jstlay=adddepartment')); ?>" title="<?php echo esc_attr__('Add Department', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Department', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'priority') echo 'active'; ?> menu-item-priorities">
        <a href="#" title="<?php echo esc_attr__('Priorities', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg><span class="jsst_text"><?php echo esc_html__('Priorities', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'priority' && ($layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=priority')); ?>" title="<?php echo esc_attr__('Priorities', 'js-support-ticket'); ?>"><?php echo esc_html__('Priorities', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'priority' && ($layout == 'addpriority')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=priority&jstlay=addpriority')); ?>" title="<?php echo esc_attr__('Add Priority', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Priority', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'status') echo 'active'; ?> menu-item-statuses">
        <a href="#" title="<?php echo esc_attr__('Ticket Statuses', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg><span class="jsst_text"><?php echo esc_html__('Ticket Statuses', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'status' && ($layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=status')); ?>" title="<?php echo esc_attr__('Ticket Statuses', 'js-support-ticket'); ?>"><?php echo esc_html__('Ticket Statuses', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'status' && ($layout == 'addstatus')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=status&jstlay=addstatus')); ?>" title="<?php echo esc_attr__('Add Status', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Ticket Status', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'feedback'  || ($c == 'fieldordering' && $ff == 2) ) echo 'active'; ?>">
            <a class="" href="#" title="<?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="jsst_menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M17 16h.01" />
                </svg>
                <span class="jsst_text"><?php echo esc_html(__('Feedbacks', 'js-support-ticket')); ?></span>
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
        <li class="disabled-menu treeview menu-item-feedbacks">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Feedbacks', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M17 16h.01" /></svg><span class="jsst_text"><?php echo esc_html__('Feedbacks', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if ($c == 'feedback' && ($layout == 'feedbacks')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('?page=feedback&jstlay=feedbacks')); ?>" title="<?php echo esc_attr__('Feedbacks', 'js-support-ticket'); ?>"><?php echo esc_html__('Feedbacks', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('cannedresponses', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'cannedresponses') echo 'active'; ?> menu-item-cannedresponses">
            <a href="#" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg><span class="jsst_text"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'cannedresponses' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses')); ?>" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'cannedresponses' && $layout == 'addpremademessage') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses&jstlay=addpremademessage')); ?>" title="<?php echo esc_attr__('Add Canned Response', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Canned Response', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="treeview disabled-menu menu-item-cannedresponses">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg><span class="jsst_text"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if ($c == 'cannedresponses' && ($layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses')); ?>" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></a></li>
                <li class="<?php if ($c == 'cannedresponses' && ($layout == 'addpremademessage')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses&jstlay=addpremademessage')); ?>" title="<?php echo esc_attr__('Add Canned Response', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Canned Response', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } ?>
    <li class="menu-header"><?php echo esc_html__('User Management', 'js-support-ticket'); ?></li>
    <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'agent' || $c == 'agentautoassign') echo 'active'; ?> menu-item-agents">
            <a href="#" title="<?php echo esc_attr__('Agents', 'js-support-ticket'); ?>">
                <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <!-- Head -->
                    <circle cx="12" cy="8" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Shoulders -->
                    <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M4 20c0-3.5 3.6-6 8-6s8 2.5 8 6"/>
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Agents', 'js-support-ticket'); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'agent' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agent')); ?>" title="<?php echo esc_attr__('Agents', 'js-support-ticket'); ?>"><?php echo esc_html__('Agents', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'agent' && $layout == 'addstaff') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agent&jstlay=addstaff')); ?>" title="<?php echo esc_attr__('Add Agent', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Agent', 'js-support-ticket'); ?></a></li>
                <?php if (in_array('agentautoassign', jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($c == 'agentautoassign') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agentautoassign')); ?>" title="<?php echo esc_attr__('Agent Auto Assign', 'js-support-ticket'); ?>"><?php echo esc_html__('Agent Auto Assign', 'js-support-ticket'); ?></a></li>
                <?php } else {
                    $plugininfo = JSSTCheckPluginInfo('js-support-ticket-agentautoassign/js-support-ticket-agentautoassign.php');
                    $text = $plugininfo['availability'] == '1' ? $plugininfo['text'] : $plugininfo['text'];
                    $url = $plugininfo['availability'] == '1' ? 'plugins.php?s=js-support-ticket-agentautoassign&plugin_status=inactive' : 'https://jshelpdesk.com/product/agentautoassign/';
                    ?>
                    <li class="disabled-menu">
                        <a class="jsstadmin-sidebar-submenu-grey" href="javascript:void(0);" title="<?php echo esc_attr__('Agent Auto Assign', 'js-support-ticket'); ?>"><?php echo esc_html__('Auto Assign', 'js-support-ticket'); ?></a>
                    </li>
                <?php } ?>
            </ul>
        </li>
        <li class="treeview <?php if($c == 'role') echo 'active'; ?> menu-item-roles">
            <a href="#" title="<?php echo esc_attr__('Roles', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12c0-5.03 4.403-9 9.75-9s9.75 3.97 9.75 9-4.403 9-9.75 9-9.75-3.97-9.75-9z" /></svg><span class="jsst_text"><?php echo esc_html__('Agent Roles', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'role' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=role')); ?>" title="<?php echo esc_attr__('Agent Roles', 'js-support-ticket'); ?>"><?php echo esc_html__('Agent Roles', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'role' && $layout == 'addrole') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=role&jstlay=addrole')); ?>" title="<?php echo esc_attr__('Add Role', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Agent Role', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-agents">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Agents', 'js-support-ticket'); ?>">
                <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <!-- Head -->
                    <circle cx="12" cy="8" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Shoulders -->
                    <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M4 20c0-3.5 3.6-6 8-6s8 2.5 8 6"/>
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Agents', 'js-support-ticket'); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
        <li class="disabled-menu treeview menu-item-roles">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Agent Roles', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12c0-5.03 4.403-9 9.75-9s9.75 3.97 9.75 9-4.403 9-9.75 9-9.75-3.97-9.75-9z" /></svg><span class="jsst_text"><?php echo esc_html__('Agent Roles', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if ($c == 'role' && ($layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=role')); ?>" title="<?php echo esc_attr__('Agent Roles', 'js-support-ticket'); ?>"><?php echo esc_html__('Agent Roles', 'js-support-ticket'); ?></a></li>
                <li class="<?php if ($c == 'role' && ($layout == 'addrole')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=role&jstlay=addrole')); ?>" title="<?php echo esc_attr__('Add Agent Role', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Agent Role', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } ?>

    <li class="menu-header"><?php echo esc_html__('Content & Knowledge', 'js-support-ticket'); ?></li>
    <?php if(in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'knowledgebase' && ($layout == 'listcategories' || $layout == 'addcategory')) echo 'active'; ?> menu-item-categories">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Categories', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg><span class="jsst_text"><?php echo esc_html__('Categories', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'knowledgebase' && $layout == 'listcategories') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase&jstlay=listcategories')); ?>" title="<?php echo esc_attr__('Categories', 'js-support-ticket'); ?>"><?php echo esc_html__('Categories', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'knowledgebase' && $layout == 'addcategory') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase&jstlay=addcategory')); ?>" title="<?php echo esc_attr__('Add Category', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Category', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
        <li class="treeview <?php if($c == 'knowledgebase' && ($layout == 'listarticles' || $layout == 'addarticle')) echo 'active'; ?> menu-item-kb">
            <a href="#" title="<?php echo esc_attr__('Knowledge Base', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg><span class="jsst_text"><?php echo esc_html__('Knowledge Base', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'knowledgebase' && $layout == 'listarticles') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase&jstlay=listarticles')); ?>" title="<?php echo esc_attr__('Knowledge Base', 'js-support-ticket'); ?>"><?php echo esc_html__('Knowledge Base', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'knowledgebase' && $layout == 'addarticle') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase&jstlay=addarticle')); ?>" title="<?php echo esc_attr__('Add Knowledge Base', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Knowledge Base', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-kb">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Knowledge Base', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg><span class="jsst_text"><?php echo esc_html__('Knowledge Base', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <?php if(in_array('helptopic', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'helptopic') echo 'active'; ?>">
            <a class="" href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Help Topics', 'js-support-ticket'); ?>" title="<?php echo esc_html(__('Help Topics' , 'js-support-ticket')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 26 24" stroke="currentColor" stroke-width="2" 
                    class="jsst_menu-icon" width="22" height="22">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M8 12h.01M12 12h.01M16 12h.01
                        M23 12c0-4.418-4.48-8-10-8S3 7.582 3 12
                        c0 1.638.502 3.197 1.378 4.48L3 21
                        l5.448-1.742c1.284.877 2.843 1.378 4.48 1.378
                        5.52 0 10-3.582 10-8z"/>
                </svg>
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
        <li class="disabled-menu treeview menu-item-helptopic">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Helptopic', 'js-support-ticket'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 26 24" stroke="currentColor" stroke-width="2"  class="jsst_menu-icon" width="22" height="22">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M8 12h.01M12 12h.01M16 12h.01
                        M23 12c0-4.418-4.48-8-10-8S3 7.582 3 12
                        c0 1.638.502 3.197 1.378 4.48L3 21
                        l5.448-1.742c1.284.877 2.843 1.378 4.48 1.378
                        5.52 0 10-3.582 10-8z"/>
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Help Topics', 'js-support-ticket'); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if ($c == 'helptopic' && ($layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('?page=helptopic&jstlay=helptopic')); ?>" title="<?php echo esc_attr__('Help Topics', 'js-support-ticket'); ?>"><?php echo esc_html__('Help Topics', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('faq', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'faq') echo 'active'; ?> menu-item-faqs">
            <a href="#" title="<?php echo esc_attr__('FAQ\'s', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg><span class="jsst_text"><?php echo esc_html__('FAQ\'s', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'faq' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=faq')); ?>" title="<?php echo esc_attr__('FAQ\'s', 'js-support-ticket'); ?>"><?php echo esc_html__('FAQ\'s', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'faq' && $layout == 'addfaq') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=faq&jstlay=addfaq')); ?>" title="<?php echo esc_attr__('Add FAQ', 'js-support-ticket'); ?>"><?php echo esc_html__('Add FAQ', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-faqs">
            <a href="#" title="<?php echo esc_attr__('FAQs', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg><span class="jsst_text"><?php echo esc_html__('FAQs', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <?php if(in_array('announcement', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'announcement') echo 'active'; ?> menu-item-announcements">
            <a href="#" title="<?php echo esc_attr__('Announcements', 'js-support-ticket'); ?>">
                <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 28 24" width="22" height="22" stroke="currentColor" stroke-width="2">
                    <!-- Megaphone body -->
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 10l13-5v14l-13-5v-4z" />
                    <!-- Broadcast waves -->
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9c1.5 1 1.5 5 0 6" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7c2.5 2 2.5 8 0 10" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 5c3.5 3 3.5 12 0 15" />
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Announcements', 'js-support-ticket'); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'announcement' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=announcement')); ?>" title="<?php echo esc_attr__('Announcements', 'js-support-ticket'); ?>"><?php echo esc_html__('Announcements', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'announcement' && $layout == 'addannouncement') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=announcement&jstlay=addannouncement')); ?>" title="<?php echo esc_attr__('Add Announcement', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Announcement', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-announcements">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Announcements', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84a3.75 3.75 0 01-5.68 0M19.5 6.375a9 9 0 01-12.728 0" /></svg><span class="jsst_text"><?php echo esc_html__('Announcements', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <?php if(in_array('download', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'download') echo 'active'; ?> menu-item-download">
            <a href="#" title="<?php echo esc_attr__('Download', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" /></svg><span class="jsst_text"><?php echo esc_html__('Download', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'download' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=download')); ?>" title="<?php echo esc_attr__('Downloads', 'js-support-ticket'); ?>"><?php echo esc_html__('Downloads', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'download' && $layout == 'adddownload') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=download&jstlay=adddownload')); ?>" title="<?php echo esc_attr__('Add Download', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Download', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-download">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Download', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" /></svg><span class="jsst_text"><?php echo esc_html__('Download', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <li class="menu-header"><?php echo esc_html__('Email & Communication', 'js-support-ticket'); ?></li>
    <li class="treeview <?php if($c == 'email') echo 'active'; ?> menu-item-systememails">
        <a href="#" title="<?php echo esc_attr__('System Emails', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg><span class="jsst_text"><?php echo esc_html__('System Emails', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'email' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=email')); ?>" title="<?php echo esc_attr__('System Emails', 'js-support-ticket'); ?>"><?php echo esc_html__('System Emails', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'email' && $layout == 'addemail') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=email&jstlay=addemail')); ?>" title="<?php echo esc_attr__('Add Email', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Email', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'emailtemplate') echo 'active'; ?> menu-item-emailtemplates">
        <a href="#" title="<?php echo esc_attr__('Email Templates', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg><span class="jsst_text"><?php echo esc_html__('Email Templates', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'emailtemplate' && $for == 'tk-nw') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=tk-nw')); ?>" title="<?php echo esc_attr__('New Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('New Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'sntk-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=sntk-tk')); ?>" title="<?php echo esc_attr__('Agent Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Agent Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ew-sm') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=ew-sm')); ?>" title="<?php echo esc_attr__('New Agent', 'js-support-ticket'); ?>"><?php echo esc_html__('New Agent', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'rs-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=rs-tk')); ?>" title="<?php echo esc_attr__('Reassign Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Reassign Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'cl-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=cl-tk')); ?>" title="<?php echo esc_attr__('Close Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Close Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'dl-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=dl-tk')); ?>" title="<?php echo esc_attr__('Delete Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Delete Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'mo-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=mo-tk')); ?>" title="<?php echo esc_attr__('Mark Overdue', 'js-support-ticket'); ?>"><?php echo esc_html__('Mark Overdue', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'be-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=be-tk')); ?>" title="<?php echo esc_attr__('Ban Email', 'js-support-ticket'); ?>"><?php echo esc_html__('Ban Email', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'be-trtk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=be-trtk')); ?>" title="<?php echo esc_attr__('Ban email try to create ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Ban email try to create ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'dt-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=dt-tk')); ?>" title="<?php echo esc_attr__('Department Transfer', 'js-support-ticket'); ?>"><?php echo esc_html__('Department Transfer', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ebct-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=ebct-tk')); ?>" title="<?php echo esc_attr__('Ban Email and Close Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Ban Email and Close Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ube-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=ube-tk')); ?>" title="<?php echo esc_attr__('Unban Email', 'js-support-ticket'); ?>"><?php echo esc_html__('Unban Email', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'rsp-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=rsp-tk')); ?>" title="<?php echo esc_attr__('Response Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Response Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'rpy-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=rpy-tk')); ?>" title="<?php echo esc_attr__('Reply Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Reply Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'tk-ew-ad') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=tk-ew-ad')); ?>" title="<?php echo esc_attr__('New Ticket Admin Alert', 'js-support-ticket'); ?>"><?php echo esc_html__('New Ticket Admin Alert', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'lk-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=lk-tk')); ?>" title="<?php echo esc_attr__('Lock Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Lock Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ulk-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=ulk-tk')); ?>" title="<?php echo esc_attr__('Unlock Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Unlock Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'minp-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=minp-tk')); ?>" title="<?php echo esc_attr__('In Progress Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('In Progress Ticket', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'pc-tk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=pc-tk')); ?>" title="<?php echo esc_attr__('Ticket priority is changed by', 'js-support-ticket'); ?>"><?php echo esc_html__('Ticket priority is changed by', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ml-ew') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=ml-ew')); ?>" title="<?php echo esc_attr__('New Mail Received', 'js-support-ticket'); ?>"><?php echo esc_html__('New Mail Received', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'ml-rp') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=ml-rp')); ?>" title="<?php echo esc_attr__('New Mail Message Received', 'js-support-ticket'); ?>"><?php echo esc_html__('New Mail Message Received', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'fd-bk') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=fd-bk')); ?>" title="<?php echo esc_attr__('Feedback Email To User', 'js-support-ticket'); ?>"><?php echo esc_html__('Feedback Email To User', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'emailtemplate' && $for == 'no-rp') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate&for=no-rp')); ?>" title="<?php echo esc_attr__('User Reply On Closed Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('User Reply On Closed Ticket', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <?php if(in_array('emailpiping', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'emailpiping') echo 'active'; ?> menu-item-emailpiping">
            <a href="#" title="<?php echo esc_attr__('Email Piping', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg><span class="jsst_text"><?php echo esc_html__('Email Piping', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'emailpiping') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailpiping')); ?>" title="<?php echo esc_attr__('Email Piping', 'js-support-ticket'); ?>"><?php echo esc_html__('Email Piping', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-emailpiping">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Email Piping', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg><span class="jsst_text"><?php echo esc_html__('Email Piping', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <?php if(in_array('mail', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'mail') echo 'active'; ?> menu-item-mail">
            <a href="#" title="<?php echo esc_attr__('Mail', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.12-1.588H6.88a2.25 2.25 0 00-2.12 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" /></svg><span class="jsst_text"><?php echo esc_html__('Mail', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if ($c == 'mail') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=mail')); ?>" title="<?php echo esc_attr__('Mail', 'js-support-ticket'); ?>"><?php echo esc_html__('Mail', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-mail">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Mail', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.12-1.588H6.88a2.25 2.25 0 00-2.12 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" /></svg><span class="jsst_text"><?php echo esc_html__('Mail', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <?php if(in_array('banemail', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'banemail' || $c == 'banemaillog') echo 'active'; ?> menu-item-banemails">
            <a href="#" title="<?php echo esc_attr__('Ban Emails', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg><span class="jsst_text"><?php echo esc_html__('Ban Emails', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'banemail' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=banemail')); ?>" title="<?php echo esc_attr__('Banned Emails', 'js-support-ticket'); ?>"><?php echo esc_html__('Banned Emails', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'banemaillog') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=banemaillog')); ?>" title="<?php echo esc_attr__('Banned Email Log List', 'js-support-ticket'); ?>"><?php echo esc_html__('Banned Email Log List', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-banemails">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Ban Emails', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg><span class="jsst_text"><?php echo esc_html__('Ban Emails', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <?php if(in_array('emailcc', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($c == 'emailcc') echo 'active'; ?> menu-item-emailcc">
            <a href="#" title="<?php echo esc_attr__('Email CC', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 1.5a10.5 10.5 0 100 21 10.5 10.5 0 000-21z" /></svg><span class="jsst_text"><?php echo esc_html__('Email CC', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($c == 'emailcc' && $layout != 'addemailcc') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailcc')); ?>" title="<?php echo esc_attr__('Email CC', 'js-support-ticket'); ?>"><?php echo esc_html__('Email CC', 'js-support-ticket'); ?></a></li>
                <li class="<?php if($c == 'emailcc' && $layout == 'addemailcc') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailcc&jstlay=addemailcc')); ?>" title="<?php echo esc_attr__('Add Email CC', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Email CC', 'js-support-ticket'); ?></a></li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu treeview menu-item-emailcc">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Email CC', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 1.5a10.5 10.5 0 100 21 10.5 10.5 0 000-21z" /></svg><span class="jsst_text"><?php echo esc_html__('Email CC', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu"></ul>
        </li>
    <?php } ?>
    <li class="menu-header"><?php echo esc_html__('Products & Sales', 'js-support-ticket'); ?></li>
    <li class="treeview <?php if($c == 'product') echo 'active'; ?> menu-item-products">
        <a href="#" title="<?php echo esc_attr__('Products', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l.383-1.437M7.5 14.25L5.106 5.165A2.25 2.25 0 002.854 3H2.25" /></svg><span class="jsst_text"><?php echo esc_html__('Products', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'product' && $layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=product')); ?>" title="<?php echo esc_attr__('Products', 'js-support-ticket'); ?>"><?php echo esc_html__('Products', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'product' && $layout == 'addproduct') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=product&jstlay=addproduct')); ?>" title="<?php echo esc_attr__('Add Product', 'js-support-ticket'); ?>"><?php echo esc_html__('Add Product', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="menu-header"><?php echo esc_html__('System & Settings', 'js-support-ticket'); ?></li>
    <li class="treeview <?php if($c == 'configuration') echo 'active'; ?> menu-item-configurations">
        <a href="#" title="<?php echo esc_attr__('Configurations', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.438.995s.145.755.438.995l1.003.827c.485.4.665 1.102.26 1.431l-1.296 2.247a1.125 1.125 0 01-1.37.49l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.332.183-.582.495-.645.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.37-.49l-1.296-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.437-.995s-.145-.755-.437-.995l-1.004-.827a1.125 1.125 0 01-.26-1.431l1.296-2.247a1.125 1.125 0 011.37-.49l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.645-.87l.213-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Configurations', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'configuration' && $layout != 'cronjoburl') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=configuration&jsstconfigid=general')); ?>" title="<?php echo esc_attr__('Configurations', 'js-support-ticket'); ?>"><?php echo esc_html__('Configurations', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'configuration' && $layout == 'cronjoburl') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=configuration&jstlay=cronjoburl')); ?>" title="<?php echo esc_attr__('Cron Job URLs', 'js-support-ticket'); ?>"><?php echo esc_html__('Cron Job URLs', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'jssupportticket' && $layout == 'shortcodes') echo 'active'; ?> menu-item-shortcodes">
        <a href="#" title="<?php echo esc_attr__('Shortcodes', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" /></svg><span class="jsst_text"><?php echo esc_html__('Shortcodes', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'jssupportticket' && $layout == 'shortcodes') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=shortcodes')); ?>" title="<?php echo esc_attr__('Shortcodes', 'js-support-ticket'); ?>"><?php echo esc_html__('Shortcodes', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'themes') echo 'active'; ?> menu-item-themes">
        <a href="#" title="<?php echo esc_attr__('Themes', 'js-support-ticket'); ?>">
            <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="6" x2="20" y2="6" stroke-linecap="round"></line>
                <circle cx="8" cy="6" r="1.6"></circle>
                <line x1="4" y1="12" x2="20" y2="12" stroke-linecap="round"></line>
                <circle cx="14" cy="12" r="1.6"></circle>
                <line x1="4" y1="18" x2="20" y2="18" stroke-linecap="round"></line>
                <circle cx="12" cy="18" r="1.6"></circle>
            </svg>
            <span class="jsst_text"><?php echo esc_html__('Themes', 'js-support-ticket'); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'themes' && ($layout == 'themes')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=themes&jstlay=themes')); ?>" title="<?php echo esc_attr__('Themes', 'js-support-ticket'); ?>"><?php echo esc_html__('Themes', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'thirdpartyimport') echo 'active'; ?> menu-item-import">
        <a href="#" title="<?php echo esc_attr__('Import Data', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg><span class="jsst_text"><?php echo esc_html__('Import Data', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'thirdpartyimport' && ($layout == 'importdata' || $layout == 'importdata')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=thirdpartyimport&jstlay=importdata')); ?>" title="<?php echo esc_attr__('Import Data', 'js-support-ticket'); ?>"><?php echo esc_html__('Import Data', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'gdpr') echo 'active'; ?> menu-item-gdpr">
        <a href="#" title="<?php echo esc_attr__('GDPR', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286zm0 13.036h.008v.008h-.008v-.008z" /></svg><span class="jsst_text"><?php echo esc_html__('GDPR', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'gdpr' && ($layout == 'gdprfields' || $layout == 'addgdprfield')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=gdpr&jstlay=gdprfields')); ?>" title="<?php echo esc_attr__('GDPR Fields', 'js-support-ticket'); ?>"><?php echo esc_html__('GDPR Fields', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'gdpr' && ($layout == 'erasedatarequests')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=gdpr&jstlay=erasedatarequests')); ?>" title="<?php echo esc_attr__('Erase Data Requests', 'js-support-ticket'); ?>"><?php echo esc_html__('Erase Data Requests', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'premiumplugin' || $layout == 'addonstatus') echo 'active'; ?> menu-item-addons">
        <a href="#" title="<?php echo esc_attr__('Addons', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5c0-.938.468-1.822 1.25-2.386l4-3.5a1.875 1.875 0 000-3.228l-4-3.5A1.875 1.875 0 0013.5 3V1.5m-3 19.5v-7.5c0-.938-.468-1.822-1.25-2.386l-4-3.5a1.875 1.875 0 010-3.228l4-3.5A1.875 1.875 0 0110.5 3V1.5" /></svg><span class="jsst_text"><?php echo esc_html__('Addons', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'premiumplugin' && ($layout == 'step1' || $layout == 'step2' || $layout == 'step3')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=premiumplugin&jstlay=step1')); ?>" title="<?php echo esc_attr__('Install Addons', 'js-support-ticket'); ?>"><?php echo esc_html__('Install Addons', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'jssupportticket' && $layout == 'addonstatus') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=addonstatus')); ?>" title="<?php echo esc_attr__('Addons Status', 'js-support-ticket'); ?>"><?php echo esc_html__('Addons Status', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'premiumplugin' && $layout == 'updatekey') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=premiumplugin&jstlay=updatekey')); ?>" title="<?php echo esc_attr__('Update Key', 'js-support-ticket'); ?>"><?php echo esc_html__('Update Key', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'premiumplugin' && $layout == 'addonfeatures') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=premiumplugin&jstlay=addonfeatures')); ?>" title="<?php echo esc_attr__('Addons List', 'js-support-ticket'); ?>"><?php echo esc_html__('Addons List', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'reports') echo 'active'; ?> menu-item-reports">
        <a href="#" title="<?php echo esc_attr__('Reports', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg><span class="jsst_text"><?php echo esc_html__('Reports', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'reports' && ($layout == 'overallreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=overallreport')); ?>" title="<?php echo esc_attr__('Overall Statistics', 'js-support-ticket'); ?>"><?php echo esc_html__('Overall Statistics', 'js-support-ticket'); ?></a></li>
            <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
            <li class="<?php if($c == 'reports' && ($layout == 'staffreport' || $layout == 'staffdetailreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=staffreport')); ?>" title="<?php echo esc_attr__('Agent Reports', 'js-support-ticket'); ?>"><?php echo esc_html__('Agent Reports', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <li class="<?php if($c == 'reports' && ($layout == 'departmentreport' || $layout == 'departmentdetailreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=departmentreport')); ?>" title="<?php echo esc_attr__('Department Reports', 'js-support-ticket'); ?>"><?php echo esc_html__('Department Reports', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($c == 'reports' && ($layout == 'userreport' || $layout == 'userdetailreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=userreport')); ?>" title="<?php echo esc_attr__('User Reports', 'js-support-ticket'); ?>"><?php echo esc_html__('User Reports', 'js-support-ticket'); ?></a></li>
            <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
            <li class="<?php if($c == 'reports' && ($layout == 'satisfactionreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=satisfactionreport')); ?>" title="<?php echo esc_attr__('Satisfaction Report', 'js-support-ticket'); ?>"><?php echo esc_html__('Satisfaction Report', 'js-support-ticket'); ?></a></li>
            <?php } ?>
        </ul>
    </li>
    <li class="treeview <?php if($c == 'jssupportticket' && $layout == 'help') echo 'active'; ?> menu-item-help">
        <a href="#" title="<?php echo esc_attr__('help', 'js-support-ticket'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"  stroke="currentColor" stroke-width="2" class="jsst_menu-icon" width=" 22" height="22">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="10,8 16,12 10,16" fill="currentColor"></polygon>
            </svg>
            <span class="jsst_text"><?php echo esc_html__('help', 'js-support-ticket'); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($c == 'jssupportticket' && $layout == 'help') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=help')); ?>" title="<?php echo esc_attr__('help', 'js-support-ticket'); ?>"><?php echo esc_html__('help', 'js-support-ticket'); ?></a></li>
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
    <!-- add loading for multiform -->
    <div id="jstran_loading">
        <img alt="<?php echo esc_html(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
    </div>
<?php }
$jssupportticket_js ='
    jQuery(document).ready(function ($) {
        jQuery("a#multiformpopup").click(function (e) {
            e.preventDefault();
            var url = jQuery("a#multiformpopup").prop("class");
            jQuery("div#multiformpopupblack").show();
            var ajaxurl ="'.admin_url('admin-ajax.php').'";
            jsShowLoading();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "multiform", task: "getmultiformlistajax", url:url, "_wpnonce":"'.esc_attr(wp_create_nonce("get-multi-form-list-ajax")).'"}, function (data) {
                if(data){
                    jsHideLoading();
                    jQuery("div#records").html("");
                    jQuery("div#records").html(data);
                    // setUserLink(); generate error
                    jQuery("div#multiformpopup").slideDown("slow");
                }
            });
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

    function jsShowLoading(){
        jQuery("div#black_wrapper_translation").show();
        jQuery("div#jstran_loading").show();
    }

    function jsHideLoading(){
        jQuery("div#black_wrapper_translation").hide();
        jQuery("div#jstran_loading").hide();
    }
';
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
$jssupportticket_js = "
    // --- Accordion Menu Logic ---
    document.querySelectorAll('.jsstadmin-sidebar-menu .treeview > a').forEach(item => {
        item.addEventListener('click', event => {
            const parentLi = item.parentElement;
            // Prevent default link behavior only if there is a submenu to toggle
            if (parentLi.querySelector('.jsstadmin-sidebar-submenu .treeview-menu')) {
                event.preventDefault();
            }

            const menu = document.getElementById('jsstadmin-leftmenu');

            // Do not allow opening accordion if menu is collapsed
            if (menu.classList.contains('menu-collapsed')) {
                return;
            }

            // Prevent interaction with disabled items
            if (parentLi.classList.contains('disabled-menu')) {
                event.preventDefault();
                return;
            }

            // Toggle active class
            if (parentLi.classList.contains('active')) {
                parentLi.classList.remove('active');
            } else {
                // Optional: Close other active submenus before opening a new one
                // document.querySelectorAll('.jsstadmin-sidebar-menu .treeview.active').forEach(el => el.classList.remove('active'));
                parentLi.classList.add('active');
            }
        });
    });

    // --- Expand/Collapse Menu Logic ---
    const toggleButton = document.getElementById('jsstadmin-menu-toggle');
    const logoLink = document.querySelector('#jsstadmin-logo .jsst-anchor'); // Select the logo link
    const menu = document.getElementById('jsstadmin-leftmenu');
    const body = document.body;

    // Create a reusable function to toggle the menu state
    const toggleMenu = (event) => {
        // Prevent the default link behavior for the logo click
        event.preventDefault();

        menu.classList.toggle('menu-collapsed');
        body.classList.toggle('menu-collapsed');

        // Close any open submenus when collapsing the main menu
        if (menu.classList.contains('menu-collapsed')) {
            document.querySelectorAll('.jsstadmin-sidebar-menu .treeview.active').forEach(el => el.classList.remove('active'));
        }
    };

    // Attach the event listener to both the button and the logo
    toggleButton.addEventListener('click', toggleMenu);
    logoLink.addEventListener('click', toggleMenu);
";
wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
?>
