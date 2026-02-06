<?php

if (!defined('ABSPATH'))
    die('Restricted Access');
if (jssupportticket::$_config['show_header'] != 1)
    return false;
$jsst_isUserStaff = false;
if (in_array('agent', jssupportticket::$_active_addons)) {
    $jsst_isUserStaff = JSSTincluder::getJSModel('agent')->isUserStaff();
}
$jsst_div = '';
$jsst_headertitle = '';
$jsst_editid = JSSTrequest::getVar('jssupportticketid');
$jsst_isnew = ($jsst_editid == null) ? true : false;
$jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')), 'text' => esc_html(__('Control Panel', 'js-support-ticket')));
$jsst_module = JSSTrequest::getVar('jstmod', null, 'jssupportticket');
$jsst_layout = JSSTrequest::getVar('jstlay', null);
/*if (isset(jssupportticket::$jsst_data['short_code_header'])) {
    switch (jssupportticket::$jsst_data['short_code_header']){
        case 'myticket':
            $jsst_module = 'ticket';
            $jsst_layout = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
            break;
        case 'addticket':
            $jsst_module = 'ticket';
            $jsst_layout = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffaddticket' : 'addticket';
            break;
        case 'downloads':
            $jsst_module = 'download';
            $jsst_layout = 'downloads';
            break;
        case 'faqs':
            $jsst_module = 'faq';
            $jsst_layout = 'faqs';
            break;
        case 'announcements':
            $jsst_module = 'announcement';
            $jsst_layout = 'announcements';
            break;
        case 'userknowledgebase':
            $jsst_module = 'knowledgebase';
            $jsst_layout = 'userknowledgebase';
            $jsst_layout = 'articledetails';
            break;
    }
}

 if ($jsst_module != null) {
    switch ($jsst_module) {
        case 'announcement':
            switch ($jsst_layout) {
                case 'announcements':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    break;
                case 'announcementdetails':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffannouncement' : 'announcements';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout1)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    $jsst_array[] = array('link' =>'#', 'text' => esc_html(__('Announcement Detail', 'js-support-ticket')));
                    break;
                case 'addannouncement':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffannouncements' : 'announcements';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout1)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Announcement', 'js-support-ticket')) : esc_html(__('Edit Announcement', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'staffannouncements':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    break;
            }
            break;
        case 'department':
            switch ($jsst_layout) {
                case 'adddepartment':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'departments')), 'text' => esc_html(__('Departments', 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Department', 'js-support-ticket')) : esc_html(__('Edit Department', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'departments':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Departments', 'js-support-ticket')));
                    break;
            }
            break;
        case 'reports':
            switch ($jsst_layout) {
                case 'staffdetailreport':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'staffreports')), 'text' => esc_html(__('Staff reports', 'js-support-ticket')));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Staff report', 'js-support-ticket')));
                    break;
                case 'staffreports':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Staff reports', 'js-support-ticket')));
                    break;
                case 'departmentreports':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Departments report', 'js-support-ticket')));
                    break;
            }
            break;
        case 'download':
            switch ($jsst_layout) {
                case 'adddownload':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffdownloads' : 'downloads';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout1)), 'text' => esc_html(__('Downloads', 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Download', 'js-support-ticket')) : esc_html(__('Edit Download', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'downloads':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Downloads', 'js-support-ticket')));
                    break;
                case 'staffdownloads':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Downloads', 'js-support-ticket')));
                    break;
            }
            break;
        case 'faq':
            switch ($jsst_layout) {
                case 'addfaq':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'stafffaqs' : 'faqs';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout1)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add FAQ', 'js-support-ticket')) : esc_html(__('Edit FAQ', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'faqdetails':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'stafffaqs' : 'faqs';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout1)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    $jsst_array[] = array('link' => '#', 'text' => esc_html(__('FAQ Detail', 'js-support-ticket')));
                    break;
                case 'faqs':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    break;
                case 'stafffaqs':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    break;
            }
            break;
        case 'jssupportticket':
            switch ($jsst_layout) {
                case 'login':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Login', 'js-support-ticket')));
                    break;
            }
            break;
        case 'feedback':
            switch ($jsst_layout) {
                case 'feedbacks':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Feedbacks', 'js-support-ticket')));
                    break;
            }
            break;
        case 'knowledgebase':
            switch ($jsst_layout) {
                case 'addarticle':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'stafflistarticles')), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Knowledge Base', 'js-support-ticket')) : esc_html(__('Edit Knowledge Base', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'addcategory':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'stafflistcategories')), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Category', 'js-support-ticket')) : esc_html(__('Edit Category', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'articledetails':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'userknowledgebase')), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    $jsst_array[] = array('link' => '#', 'text' => esc_html(__('Knowledge Base Detail', 'js-support-ticket')));
                    break;
                case 'listarticles':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
                case 'listcategories':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                    break;
                case 'stafflistarticles':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
                case 'stafflistcategories':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                    break;
                case 'userknowledgebase':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
                case 'userknowledgebasearticles':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
            }
            break;
        case 'mail':
            switch ($jsst_layout) {
                case 'formmessage':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Send Message', 'js-support-ticket')));
                    break;
                case 'inbox':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Inbox', 'js-support-ticket')));
                    break;
                case 'outbox':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Outbox', 'js-support-ticket')));
                    break;
                case 'message':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'inbox')), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $jsst_array[] = array('link' => '#', 'text' => esc_html(__('Message', 'js-support-ticket')));
                    break;
            }
            break;
        case 'role':
            switch ($jsst_layout) {
                case 'addrole':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'roles')), 'text' => esc_html(__('Roles', 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Role', 'js-support-ticket')) : esc_html(__('Edit Role', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'rolepermission':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'roles')), 'text' => esc_html(__('Roles', 'js-support-ticket')));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Role permissions', 'js-support-ticket')));
                    break;
                case 'roles':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Roles', 'js-support-ticket')));
                    break;
            }
            break;
        case 'agent':
            switch ($jsst_layout) {
                case 'addstaff':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'staffs')), 'text' => esc_html(__('Staffs', 'js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Staff', 'js-support-ticket')) : esc_html(__('Edit Staff', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                    break;
                case 'staffpermissions':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Staff Permissions', 'js-support-ticket')));
                    break;
                case 'staffs':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Staffs', 'js-support-ticket')));
                    break;
                case 'myprofile':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('My Profile', 'js-support-ticket')));
                    break;
            }
            break;
        case 'ticket':
            // Add default module link
            switch ($jsst_layout) {
                case 'addticket':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket':'myticket';
                    $jsst_module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_layout1)), 'text'=> esc_html(__('My Tickets','js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Ticket', 'js-support-ticket')) : esc_html(__('Edit Ticket', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket')), 'text' => $jsst_text);
                    break;
                case 'myticket':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket')), 'text' => esc_html(__('My Tickets', 'js-support-ticket')));
                    break;
                case 'staffaddticket':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket':'myticket';
                    $jsst_module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_layout1)), 'text'=>esc_html(__('My Tickets','js-support-ticket')));
                    $jsst_text = ($jsst_isnew) ? esc_html(__('Add Ticket', 'js-support-ticket')) : esc_html(__('Edit Ticket', 'js-support-ticket'));
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffaddticket')), 'text' => $jsst_text);
                    break;
                case 'staffmyticket':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket')), 'text' => esc_html(__('My Tickets', 'js-support-ticket')));
                    break;
                case 'ticketdetail':
                    $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
                    $jsst_module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_layout1)), 'text' => esc_html(__('My Tickets', 'js-support-ticket')));
                    $jsst_array[] = array('link' => '#', 'text' => esc_html(__('Ticket Detail', 'js-support-ticket')));
                    break;
                case 'ticketstatus':
                    $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')), 'text' => esc_html(__('Ticket Status', 'js-support-ticket')));
                    break;
            }
            break;
    }
}*/

//Layout variy for Staff Member and User
if ($jsst_isUserStaff) {
    $jsst_linkname = 'staff';
    $jsst_myticket = 'staffmyticket';
    $jsst_addticket = 'staffaddticket';
    $jsst_announcements = 'staffannouncements';
    $jsst_downloads = 'staffdownloads';
    $jsst_adddownload = 'adddownload';
    $jsst_faqs = 'stafffaqs';
    $jsst_addfaq = 'addfaq';
    $jsst_addcategory = 'addcategory';
    $jsst_categories = 'stafflistarticles';
    $jsst_addarticle = 'addarticle';
    $jsst_articles = 'stafflistarticles';
    $jsst_addannouncement = 'addannouncement';
    $jsst_login = 'login';
} else {
    $jsst_linkname = 'user';
    $jsst_myticket = 'myticket';
    $jsst_addticket = 'addticket';
    $jsst_categories = 'userknowledgebase';
    $jsst_announcements = 'announcements';
    $jsst_downloads = 'downloads';
    $jsst_faqs = 'faqs';
    $jsst_login = 'login';
}
$jsst_flage = true;
if (jssupportticket::$_config['tplink_home_' . $jsst_linkname] == 1) {
    $jsst_linkarray[] = array(
        'class' => 'js-ticket-homeclass',
        'link' => jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')),
        'title' => esc_html(__('Dashboard', 'js-support-ticket')),
        'jstmod' => '',
        'imgsrc' => JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/dashboard.png',
        'imgtitle' => 'Dashboard-icon',
    );
    $jsst_flage = false;
}
if (jssupportticket::$_config['tplink_openticket_' . $jsst_linkname] == 1) {
    $jsst_module = $jsst_isUserStaff ? 'agent' : 'ticket';
    $jsst_linkarray[] = array(
        'class' => 'js-ticket-openticketclass',
        'link' => jssupportticket::makeUrl(array('jstmod' => $jsst_module, 'jstlay' => $jsst_addticket)),
        'title' => esc_html(__('Submit Ticket', 'js-support-ticket')),
        'jstmod' => 'ticket',
        'imgsrc' => JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/add-ticket.png',
        'imgtitle' => 'Submit Ticket',
    );
    $jsst_flage = false;
}
if (jssupportticket::$_config['tplink_tickets_' . $jsst_linkname] == 1) {
    $jsst_module = $jsst_isUserStaff ? 'agent' : 'ticket';
    $jsst_linkarray[] = array(
        'class' => 'js-ticket-myticket',
        'link' => jssupportticket::makeUrl(array('jstmod' => $jsst_module, 'jstlay' => $jsst_myticket)),
        'title' => esc_html(__('My Tickets', 'js-support-ticket')),
        'jstmod' => 'ticket',
        'imgsrc' => JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/my-tickets.png',
        'imgtitle' => 'My Tickets',
    );
    $jsst_flage = false;
}

if (jssupportticket::$_config['tplink_login_logout_' . $jsst_linkname] == 1) {
    $jsst_loginval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_login_link');
    $jsst_loginlink = JSSTincluder::getJSModel('configuration')->getConfigValue('login_link');
    if ($jsst_loginval == 3){
        $jsst_hreflink = wp_login_url();
    }
    else if ($jsst_loginval == 2 && $jsst_loginlink != "") {
        $jsst_hreflink = $jsst_loginlink;
    } else {
        $jsst_hreflink = jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'login'));
    }
    if (JSSTincluder::getObjectClass('user')->isguest()) {
        $jsst_imgsrc = JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/login.png';
        $jsst_title = esc_html(__('Login', 'js-support-ticket'));
    } else {
        $jsst_imgsrc = JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/logout.png';
        $jsst_title = esc_html(__('Log out', 'js-support-ticket'));
        $jsst_hreflink = wp_logout_url(jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')));

        if (isset($_COOKIE['jssupportticket-socialmedia']) && !empty($_COOKIE['jssupportticket-socialmedia'])) {
            switch ($_COOKIE['jssupportticket-socialmedia']) {
                case 'facebook':
                    $jsst_hreflink = jssupportticket::makeUrl(array('jstmod' => 'sociallogin', 'task' => 'logout', 'action' => 'jstask', 'media' => 'facebook', 'jsstpageid' => jssupportticket::getPageid()));
                    break;
                case 'linkedin':
                    $jsst_hreflink = jssupportticket::makeUrl(array('jstmod' => 'sociallogin', 'task' => 'logout', 'action' => 'jstask', 'media' => 'linkedin', 'jsstpageid' => jssupportticket::getPageid()));
                    break;
                default:
                    $jsst_hreflink =  $jsst_hreflink = wp_logout_url(jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')));
                    break;
            }
        }

    }
    $jsst_linkarray[] = array(
        'class' => 'js-ticket-loginlogoutclass',
        'link' => $jsst_hreflink,
        'title' => $jsst_title,
        'jstmod' => 'ticket',
        'imgsrc' => $jsst_imgsrc,
        'imgtitle' => 'Login',
    );
    $jsst_flage = false;
}

// if (isset($jsst_array)) {
//     foreach ($jsst_array AS $jsst_obj);
// }
$jsst_extramargin = '';
$jsst_displayhidden = '';
if ($jsst_flage)
    $jsst_displayhidden = 'display:none;';
$jsst_div .= '
		<div id="jsst-header-main-wrapper" style="' . esc_attr($jsst_displayhidden) . '">';
$jsst_div .= '<div id="jsst-header" class="' . esc_attr($jsst_extramargin) . '" >';
/*$jsst_div .='<div id="jsst-header-heading" class="" ><a class="js-ticket-header-links" href="' . esc_url($jsst_obj['link']) . '">' . esc_html($jsst_obj['text']) . '</a></div>';*/
$jsst_div .= '<div id="jsst-tabs-wrp" class="" >';
if (isset($jsst_linkarray))
    foreach ($jsst_linkarray as $jsst_link) {
	    $jsst_id='';
        if(in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
            if($jsst_link['class'] == "js-ticket-openticketclass"){ $jsst_id="id=multiformpopup";}
        }
        //$jsst_div .= '<span class="jsst-header-tab ' . esc_attr($jsst_link['class']) . '"><a class="js-cp-menu-link" href="' . esc_url($jsst_link['link']) . '"><img class="cp-menu-link-img" title="'. esc_attr($jsst_link['imgtitle']). '" src="'.esc_url($jsst_link['imgsrc']).'">' . esc_html($jsst_link['title']) . '</a></span>';
        $jsst_div .= '<span class="jsst-header-tab ' . esc_attr($jsst_link['class']) . '"><a '.esc_attr($jsst_id).' class="js-cp-menu-link" href="' . esc_url($jsst_link['link']) . '">' . esc_html($jsst_link['title']) . '</a></span>';
    }

$jsst_div .= '</div></div></div>';
echo wp_kses($jsst_div, JSST_ALLOWED_TAGS);
?>
<?php if(in_array('multiform', jssupportticket::$_active_addons)){ ?>
    <div id="multiformpopupblack" style="display:none;"></div>
    <div id="multiformpopup" class="" style="display:none;"><!-- Select User Popup -->
        <div class="jsst-multiformpopup-header">
            <div class="multiformpopup-header-text">
                <?php echo esc_html(__('Select Form','js-support-ticket')); ?>
            </div>
            <div class="multiformpopup-header-close-img">
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
        <img alt = "<?php echo esc_attr(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
    </div>
<?php }
$jsst_jssupportticket_js ='
    jQuery(document).ready(function ($) {

        jQuery("a#multiformpopup").click(function (e) {
            e.preventDefault();
            var url = jQuery("a#multiformpopup").prop("href");
            jQuery("div#multiformpopupblack").show();
            var ajaxurl ="'. admin_url('admin-ajax.php').'";
            jsShowLoading();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "multiform", task: "getmultiformlistajax", url:url, "_wpnonce":"'. esc_attr(wp_create_nonce("get-multi-form-list-ajax")).'"}, function (data) {
                if(data){
                    jsHideLoading();
                    jQuery("div#records").html("");
                    jQuery("div#records").html(data);
                    jQuery("div#multiformpopup").slideDown("slow");
                    //setUserLink(); generate error
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
        var opt = "?";
        var found = oldUrl.search("&");
        if(found > 0){
            opt = "&";
        }
        var found = oldUrl.search("[\?\]");
        if(found > 0){
            opt = "&";
        }
        var newUrl = oldUrl+opt+"formid="+id; // Create new url
        window.location.href = newUrl;
    }

    function jsShowLoading(){
        jQuery("div#jstran_loading").show();
    }

    function jsHideLoading(){
        jQuery("div#jstran_loading").hide();
    }
';
    wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
?>
