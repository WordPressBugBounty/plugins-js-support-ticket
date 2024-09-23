<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class jssupportticketadmin {

    function __construct() {
        add_action('admin_menu', array($this, 'mainmenu'));
    }

    function mainmenu() {
        if (current_user_can('jsst_support_ticket')) {
            add_menu_page(esc_html(__('JS Help Desk Control Panel', 'js-support-ticket')), // Page title
                    esc_html(__('Help Desk', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'jssupportticket', //menu slug
                    array($this, 'showAdminPage'), // function name
    			  JSST_PLUGIN_URL.'includes/images/admin_ticket.png',26
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Slug', 'js-support-ticket')), // Page title
                    esc_html(__('Slug', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'slug', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Tickets', 'js-support-ticket')), // Page title
                    esc_html(__('Tickets', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'ticket', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            if(!in_array('multiform', jssupportticket::$_active_addons)){ 
				add_submenu_page('jssupportticket', // parent slug
						esc_html(__('Fields', 'js-support-ticket')), // Page title
						esc_html(__('Fields', 'js-support-ticket')), // menu title
						'jsst_support_ticket', // capability
						'fieldordering', //menu slug
						array($this, 'showAdminPage') // function name
				);
            } 
            if(in_array('agent', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket', // parent slug
                        esc_html(__('Agents', 'js-support-ticket')), // Page title
                        esc_html(__('Agents', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'agent', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('agent');
            }
              add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Configurations', 'js-support-ticket')), // Page title
                    esc_html(__('Configurations', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'configuration', //menu slug
                    array($this, 'showAdminPage') // function name
            );
             add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Priorities', 'js-support-ticket')), // Page title
                    esc_html(__('Priority', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'priority', //menu slug
                    array($this, 'showAdminPage') // function name
            );
             add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Department', 'js-support-ticket')), // Page title
                    esc_html(__('Departments', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'department', //menu slug
                    array($this, 'showAdminPage') // function name
            );
             add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Themes', 'js-support-ticket')), // Page title
                    esc_html(__('Themes', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'themes', //menu slug
                    array($this, 'showAdminPage') // function name
            );
             add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('JS Help Desk', 'js-support-ticket')), // Page title
                    esc_html(__('Reports', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'reports', //menu slug
                    array($this, 'showAdminPage') // function name
            );

              if(in_array('announcement', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket', // parent slug
                        esc_html(__('Announcements', 'js-support-ticket')), // Page title
                        esc_html(__('Announcements', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'announcement', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('announcement');
            }
            if(in_array('knowledgebase', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket', // parent slug
                        esc_html(__('Knowledge Base', 'js-support-ticket')), // Page title
                        esc_html(__('Knowledge Base', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'knowledgebase', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('knowledgebase');
            }
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Emails', 'js-support-ticket')), // Page title
                    esc_html(__('System Emails', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'email', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('System Error', 'js-support-ticket')), // Page title
                    esc_html(__('System Errors', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'systemerror', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Email Templates', 'js-support-ticket')), // Page title
                    esc_html(__('Email Templates', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'emailtemplate', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket', // parent slug
                esc_html(__('Translations')), // Page title
                esc_html(__('Translations')), // menu title
                'jsst_support_ticket', // capability
                'jssupportticket&jstlay=translations', //menu slug
                array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('User Fields', 'js-support-ticket')), // Page title
                    esc_html(__('User Fields', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'userfeild', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            if(in_array('cannedresponses', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Canned Responses', 'js-support-ticket')), // Page title
                        esc_html(__('Canned Responses', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'cannedresponses', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('cannedresponses');
            }

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Roles', 'js-support-ticket')), // Page title
                    esc_html(__('Roles', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'role', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            if(in_array('mail', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Mail', 'js-support-ticket')), // Page title
                        esc_html(__('Mail', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'mail', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('mail');
            }

            if(in_array('banemail', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Ban Email', 'js-support-ticket')), // Page title
                        esc_html(__('Ban Emails', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'banemail', //menu slug
                        array($this, 'showAdminPage') // function name
                );
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Ban list log', 'js-support-ticket')), // Page title
                        esc_html(__('Ban list log', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'banemaillog', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('banemail');
                $this->addMissingAddonPage('banemaillog');
            }
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Field Ordering', 'js-support-ticket')), // Page title
                    esc_html(__('Field Ordering', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'fieldordering', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            if(in_array('emailpiping', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('JS Help Desk', 'js-support-ticket')), // Page title
                        esc_html(__('Email Piping', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'emailpiping', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('emailpiping');
            }


            if(in_array('export', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Export', 'js-support-ticket')), // Page title
                        esc_html(__('Export', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'export', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('export');
            }

            if(in_array('feedback', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Feedbacks', 'js-support-ticket')), // Page title
                        esc_html(__('Feedbacks', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'feedback', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('feedback');
            }
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Post Installation', 'js-support-ticket')), // Page title
                    esc_html(__('Post Installation', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'postinstallation', //menu slug
                    array($this, 'showAdminPage') // function name
            );

           if(in_array('faq', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__("FAQs", 'js-support-ticket')), // Page title
                        esc_html(__("FAQs", 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'faq', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('faq');
            }

            if(in_array('emailcc', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__("Emailcc", 'js-support-ticket')), // Page title
                        esc_html(__("Emailcc", 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'emailcc', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('emailcc');
            }

            if(in_array('agentautoassign', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket', // parent slug
                        esc_html(__("Agent Auto Assign", 'js-support-ticket')), // Page title
                        esc_html(__("Agent Auto Assign", 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'agentautoassign', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('agentautoassign');
            }


            if(in_array('multiform', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket', // parent slug
                        esc_html(__("Multiform", 'js-support-ticket')), // Page title
                        esc_html(__("Multiform", 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'multiform', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('multiform');
            }

            if(in_array('download', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Downloads', 'js-support-ticket')), // Page title
                    esc_html(__('Downloads', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'download', //menu slug
                    array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('download');
            }


            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Premium Addons', 'js-support-ticket')), // Page title
                    esc_html(__('Premium Addons', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'premiumplugin', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Help', 'js-support-ticket')), // Page title
                    esc_html(__('Help', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'jssupportticket&jstlay=help', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            // adddons mpage code.



            // if(in_array('knowledgebase', jssupportticket::$_active_addons)){
            //     add_submenu_page('jssupportticket', // parent slug
            //         esc_html(__('Knowledge Base', 'js-support-ticket')), // Page title
            //         esc_html(__('Knowledge Base', 'js-support-ticket')), // menu title
            //         'jsst_support_ticket', // capability
            //         'knowledgebase', //menu slug
            //         array($this, 'showAdminPage') // function name
            //     );
            // }

            if(in_array('helptopic', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Help Topics', 'js-support-ticket')), // Page title
                        esc_html(__('Help Topics', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'helptopic', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('helptopic');
            }

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Themes', 'js-support-ticket')), // Page title
                    esc_html(__('Themes', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'themes', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('GDPR', 'js-support-ticket')), // Page title
                    esc_html(__('GDPR', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'gdpr', //menu slug
                    array($this, 'showAdminPage') // function name
            );

        }else{
            add_menu_page(esc_html(__('JS Help Desk Control Panel', 'js-support-ticket')), // Page title
                    esc_html(__('JS Help Desk', 'js-support-ticket')), // menu title
                    'jsst_support_ticket_tickets', // capability
                    'ticket', //menu slug
                    array($this, 'showAdminPage'), // function name
                  JSST_PLUGIN_URL.'includes/images/admin_ticket.png', 26
            );
        }
    }

    function addMissingAddonPage($module_name){
        add_submenu_page('jssupportticket_hide', // parent slug
                esc_html(__('Premium Addon', 'js-support-ticket')), // Page title
                esc_html(__('Premium Addon', 'js-support-ticket')), // menu title
                'jsst_support_ticket', // capability
                $module_name, //menu slug
                array($this, 'showMissingAddonPage') // function name
        );
    }

    function showAdminPage() {
        $page = JSSTrequest::getVar('page');
        JSSTincluder::include_file($page);
    }

    function showMissingAddonPage() {
        JSSTincluder::include_file('admin_missingaddon','premiumplugin');
    }

}

$jssupportticketAdmin = new jssupportticketadmin();
?>
