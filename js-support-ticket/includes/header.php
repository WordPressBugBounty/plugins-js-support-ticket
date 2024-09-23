<?php

if (!defined('ABSPATH'))
    die('Restricted Access');
if (jssupportticket::$_config['show_header'] != 1)
    return false;
$isUserStaff = false;
if (in_array('agent', jssupportticket::$_active_addons)) {
    $isUserStaff = JSSTincluder::getJSModel('agent')->isUserStaff();
}
$div = '';
$headertitle = '';
$editid = JSSTrequest::getVar('jssupportticketid');
$isnew = ($editid == null) ? true : false;
$array[] = array('link' => jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')), 'text' => esc_html(__('Control Panel', 'js-support-ticket')));
$module = JSSTrequest::getVar('jstmod', null, 'jssupportticket');
$layout = JSSTrequest::getVar('jstlay', null);
/*if (isset(jssupportticket::$_data['short_code_header'])) {
    switch (jssupportticket::$_data['short_code_header']){
        case 'myticket':
            $module = 'ticket';
            $layout = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
            break;
        case 'addticket':
            $module = 'ticket';
            $layout = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffaddticket' : 'addticket';
            break;
        case 'downloads':
            $module = 'download';
            $layout = 'downloads';
            break;
        case 'faqs':
            $module = 'faq';
            $layout = 'faqs';
            break;
        case 'announcements':
            $module = 'announcement';
            $layout = 'announcements';
            break;
        case 'userknowledgebase':
            $module = 'knowledgebase';
            $layout = 'userknowledgebase';
            $layout = 'articledetails';
            break;
    }
}

 if ($module != null) {
    switch ($module) {
        case 'announcement':
            switch ($layout) {
                case 'announcements':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    break;
                case 'announcementdetails':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffannouncement' : 'announcements';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout1)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    $array[] = array('link' =>'#', 'text' => esc_html(__('Announcement Detail', 'js-support-ticket')));
                    break;
                case 'addannouncement':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffannouncements' : 'announcements';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout1)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Announcement', 'js-support-ticket')) : esc_html(__('Edit Announcement', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'staffannouncements':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Announcements', 'js-support-ticket')));
                    break;
            }
            break;
        case 'department':
            switch ($layout) {
                case 'adddepartment':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'departments')), 'text' => esc_html(__('Departments', 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Department', 'js-support-ticket')) : esc_html(__('Edit Department', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'departments':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Departments', 'js-support-ticket')));
                    break;
            }
            break;
        case 'reports':
            switch ($layout) {
                case 'staffdetailreport':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'staffreports')), 'text' => esc_html(__('Staff reports', 'js-support-ticket')));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Staff report', 'js-support-ticket')));
                    break;
                case 'staffreports':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Staff reports', 'js-support-ticket')));
                    break;
                case 'departmentreports':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Departments report', 'js-support-ticket')));
                    break;
            }
            break;
        case 'download':
            switch ($layout) {
                case 'adddownload':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffdownloads' : 'downloads';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout1)), 'text' => esc_html(__('Downloads', 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Download', 'js-support-ticket')) : esc_html(__('Edit Download', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'downloads':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Downloads', 'js-support-ticket')));
                    break;
                case 'staffdownloads':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Downloads', 'js-support-ticket')));
                    break;
            }
            break;
        case 'faq':
            switch ($layout) {
                case 'addfaq':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'stafffaqs' : 'faqs';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout1)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add FAQ', 'js-support-ticket')) : esc_html(__('Edit FAQ', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'faqdetails':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'stafffaqs' : 'faqs';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout1)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    $array[] = array('link' => '#', 'text' => esc_html(__('FAQ Detail', 'js-support-ticket')));
                    break;
                case 'faqs':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    break;
                case 'stafffaqs':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                    break;
            }
            break;
        case 'jssupportticket':
            switch ($layout) {
                case 'login':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Login', 'js-support-ticket')));
                    break;
            }
            break;
        case 'feedback':
            switch ($layout) {
                case 'feedbacks':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Feedbacks', 'js-support-ticket')));
                    break;
            }
            break;
        case 'knowledgebase':
            switch ($layout) {
                case 'addarticle':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'stafflistarticles')), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Knowledge Base', 'js-support-ticket')) : esc_html(__('Edit Knowledge Base', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'addcategory':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'stafflistcategories')), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Category', 'js-support-ticket')) : esc_html(__('Edit Category', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'articledetails':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'userknowledgebase')), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    $array[] = array('link' => '#', 'text' => esc_html(__('Knowledge Base Detail', 'js-support-ticket')));
                    break;
                case 'listarticles':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
                case 'listcategories':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                    break;
                case 'stafflistarticles':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
                case 'stafflistcategories':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                    break;
                case 'userknowledgebase':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
                case 'userknowledgebasearticles':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Knowledge Base', 'js-support-ticket')));
                    break;
            }
            break;
        case 'mail':
            switch ($layout) {
                case 'formmessage':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Send Message', 'js-support-ticket')));
                    break;
                case 'inbox':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Inbox', 'js-support-ticket')));
                    break;
                case 'outbox':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Outbox', 'js-support-ticket')));
                    break;
                case 'message':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'inbox')), 'text' => esc_html(__('Message', 'js-support-ticket')));
                    $array[] = array('link' => '#', 'text' => esc_html(__('Message', 'js-support-ticket')));
                    break;
            }
            break;
        case 'role':
            switch ($layout) {
                case 'addrole':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'roles')), 'text' => esc_html(__('Roles', 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Role', 'js-support-ticket')) : esc_html(__('Edit Role', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'rolepermission':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'roles')), 'text' => esc_html(__('Roles', 'js-support-ticket')));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Role permissions', 'js-support-ticket')));
                    break;
                case 'roles':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Roles', 'js-support-ticket')));
                    break;
            }
            break;
        case 'agent':
            switch ($layout) {
                case 'addstaff':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'staffs')), 'text' => esc_html(__('Staffs', 'js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Staff', 'js-support-ticket')) : esc_html(__('Edit Staff', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                    break;
                case 'staffpermissions':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Staff Permissions', 'js-support-ticket')));
                    break;
                case 'staffs':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Staffs', 'js-support-ticket')));
                    break;
                case 'myprofile':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('My Profile', 'js-support-ticket')));
                    break;
            }
            break;
        case 'ticket':
            // Add default module link
            switch ($layout) {
                case 'addticket':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket':'myticket';
                    $module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$layout1)), 'text'=> esc_html(__('My Tickets','js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Ticket', 'js-support-ticket')) : esc_html(__('Edit Ticket', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket')), 'text' => $text);
                    break;
                case 'myticket':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket')), 'text' => esc_html(__('My Tickets', 'js-support-ticket')));
                    break;
                case 'staffaddticket':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket':'myticket';
                    $module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$layout1)), 'text'=>esc_html(__('My Tickets','js-support-ticket')));
                    $text = ($isnew) ? esc_html(__('Add Ticket', 'js-support-ticket')) : esc_html(__('Edit Ticket', 'js-support-ticket'));
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffaddticket')), 'text' => $text);
                    break;
                case 'staffmyticket':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket')), 'text' => esc_html(__('My Tickets', 'js-support-ticket')));
                    break;
                case 'ticketdetail':
                    $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
                    $module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$layout1)), 'text' => esc_html(__('My Tickets', 'js-support-ticket')));
                    $array[] = array('link' => '#', 'text' => esc_html(__('Ticket Detail', 'js-support-ticket')));
                    break;
                case 'ticketstatus':
                    $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')), 'text' => esc_html(__('Ticket Status', 'js-support-ticket')));
                    break;
            }
            break;
    }
}*/

//Layout variy for Staff Member and User
if ($isUserStaff) {
    $linkname = 'staff';
    $myticket = 'staffmyticket';
    $addticket = 'staffaddticket';
    $announcements = 'staffannouncements';
    $downloads = 'staffdownloads';
    $adddownload = 'adddownload';
    $faqs = 'stafffaqs';
    $addfaq = 'addfaq';
    $addcategory = 'addcategory';
    $categories = 'stafflistarticles';
    $addarticle = 'addarticle';
    $articles = 'stafflistarticles';
    $addannouncement = 'addannouncement';
    $login = 'login';
} else {
    $linkname = 'user';
    $myticket = 'myticket';
    $addticket = 'addticket';
    $categories = 'userknowledgebase';
    $announcements = 'announcements';
    $downloads = 'downloads';
    $faqs = 'faqs';
    $login = 'login';
}
$flage = true;
if (jssupportticket::$_config['tplink_home_' . $linkname] == 1) {
    $linkarray[] = array(
        'class' => 'js-ticket-homeclass',
        'link' => jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')),
        'title' => esc_html(__('Dashboard', 'js-support-ticket')),
        'jstmod' => '',
        'imgsrc' => JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/dashboard.png',
        'imgtitle' => 'Dashboard-icon',
    );
    $flage = false;
}
if (jssupportticket::$_config['tplink_openticket_' . $linkname] == 1) {
    $module = $isUserStaff ? 'agent' : 'ticket';
    $linkarray[] = array(
        'class' => 'js-ticket-openticketclass',
        'link' => jssupportticket::makeUrl(array('jstmod' => $module, 'jstlay' => $addticket)),
        'title' => esc_html(__('Submit Ticket', 'js-support-ticket')),
        'jstmod' => 'ticket',
        'imgsrc' => JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/add-ticket.png',
        'imgtitle' => 'Submit Ticket',
    );
    $flage = false;
}
if (jssupportticket::$_config['tplink_tickets_' . $linkname] == 1) {
    $module = $isUserStaff ? 'agent' : 'ticket';
    $linkarray[] = array(
        'class' => 'js-ticket-myticket',
        'link' => jssupportticket::makeUrl(array('jstmod' => $module, 'jstlay' => $myticket)),
        'title' => esc_html(__('My Tickets', 'js-support-ticket')),
        'jstmod' => 'ticket',
        'imgsrc' => JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/my-tickets.png',
        'imgtitle' => 'My Tickets',
    );
    $flage = false;
}

if (jssupportticket::$_config['tplink_login_logout_' . $linkname] == 1) {
    $loginval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_login_link');
    $loginlink = JSSTincluder::getJSModel('configuration')->getConfigValue('login_link');
    if ($loginval == 3){
        $hreflink = wp_login_url();
    }
    else if ($loginval == 2 && $loginlink != "") {
        $hreflink = $loginlink;
    } else {
        $hreflink = jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'login'));
    }
    if (JSSTincluder::getObjectClass('user')->isguest()) {
        $imgsrc = JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/login.png';
        $title = esc_html(__('Login', 'js-support-ticket'));
    } else {
        $imgsrc = JSST_PLUGIN_URL . 'includes/images/dashboard-icon/header-icon/logout.png';
        $title = esc_html(__('Log out', 'js-support-ticket'));
        $hreflink = wp_logout_url(jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')));

        if (isset($_COOKIE['jssupportticket-socialmedia']) && !empty($_COOKIE['jssupportticket-socialmedia'])) {
            switch ($_COOKIE['jssupportticket-socialmedia']) {
                case 'facebook':
                    $hreflink = jssupportticket::makeUrl(array('jstmod' => 'sociallogin', 'task' => 'logout', 'action' => 'jstask', 'media' => 'facebook', 'jsstpageid' => jssupportticket::getPageid()));
                    break;
                case 'linkedin':
                    $hreflink = jssupportticket::makeUrl(array('jstmod' => 'sociallogin', 'task' => 'logout', 'action' => 'jstask', 'media' => 'linkedin', 'jsstpageid' => jssupportticket::getPageid()));
                    break;
                default:
                    $hreflink =  $hreflink = wp_logout_url(jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel')));
                    break;
            }
        }

    }
    $linkarray[] = array(
        'class' => 'js-ticket-loginlogoutclass',
        'link' => $hreflink,
        'title' => $title,
        'jstmod' => 'ticket',
        'imgsrc' => $imgsrc,
        'imgtitle' => 'Login',
    );
    $flage = false;
}

// if (isset($array)) {
//     foreach ($array AS $obj);
// }
$extramargin = '';
$displayhidden = '';
if ($flage)
    $displayhidden = 'display:none;';
$div .= '
		<div id="jsst-header-main-wrapper" style="' . esc_attr($displayhidden) . '">';
$div .= '<div id="jsst-header" class="' . esc_attr($extramargin) . '" >';
/*$div .='<div id="jsst-header-heading" class="" ><a class="js-ticket-header-links" href="' . esc_url($obj['link']) . '">' . esc_html($obj['text']) . '</a></div>';*/
$div .= '<div id="jsst-tabs-wrp" class="" >';
if (isset($linkarray))
    foreach ($linkarray as $link) {
	    $id='';
        if(in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
            if($link['class'] == "js-ticket-openticketclass"){ $id="id=multiformpopup";}
        }
        //$div .= '<span class="jsst-header-tab ' . esc_attr($link['class']) . '"><a class="js-cp-menu-link" href="' . esc_url($link['link']) . '"><img class="cp-menu-link-img" title="'. esc_attr($link['imgtitle']). '" src="'.esc_url($link['imgsrc']).'">' . esc_html($link['title']) . '</a></span>';
        $div .= '<span class="jsst-header-tab ' . esc_attr($link['class']) . '"><a '.esc_attr($id).' class="js-cp-menu-link" href="' . esc_url($link['link']) . '">' . esc_html($link['title']) . '</a></span>';
    }

$div .= '</div></div></div>';
echo wp_kses($div, JSST_ALLOWED_TAGS);
?>
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
    jQuery(document).ready(function ($) {

        jQuery("a#multiformpopup").click(function (e) {
            e.preventDefault();
            var url = jQuery("a#multiformpopup").prop("href");
            jQuery("div#multiformpopupblack").show();
            var ajaxurl ="'. admin_url('admin-ajax.php').'";
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "multiform", task: "getmultiformlistajax", url:url, "_wpnonce":"'. esc_attr(wp_create_nonce("get-multi-form-list-ajax")).'"}, function (data) {
                if(data){
                    jQuery("div#records").html("");
                    jQuery("div#records").html(data);
                    //setUserLink(); generate error
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
';
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>