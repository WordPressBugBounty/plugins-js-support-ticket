<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
class JSSTbreadcrumbs {

    static function getBreadcrumbs() {
        if (jssupportticket::$_config['show_breadcrumbs'] != 1)
            return false;
        if (!is_admin()) {
            $editid = JSSTrequest::getVar('jssupportticketid');
            $isnew = ($editid == null) ? true : false;
            $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel')), 'text' => esc_html(__('Control Panel', 'js-support-ticket')));
            $module = JSSTrequest::getVar('jstmod');
            $layout = JSSTrequest::getVar('jstlay');
            if (isset(jssupportticket::$_data['short_code_header'])) {
                switch (jssupportticket::$_data['short_code_header']){
                    case 'myticket':

                        $module = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                        $layout = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
                        break;
                    case 'addticket':
                        $module = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
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
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Announcement Detail', 'js-support-ticket')));
                                break;
                            case 'addannouncement':
                                $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffannouncements' : 'announcements';
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
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('FAQ Detail', 'js-support-ticket')));
                                break;
                            case 'faqs':
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                                break;
                            case 'stafffaqs':
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                                break;
                        }
                        break;
                    case 'feedback':
                        switch ($layout) {
                            case 'feedbacks':
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'feedback', 'jstlay'=>'feedbacks')), 'text' => esc_html(__("Feedbacks", 'js-support-ticket')));
                                break;
                        }
                        break;
                    case 'jssupportticket':
                        break;
                    case 'knowledgebase':
                        switch ($layout) {
                            case 'addarticle':
                                $text = ($isnew) ? esc_html(__('Add Knowledge Base', 'js-support-ticket')) : esc_html(__('Edit Knowledge Base', 'js-support-ticket'));
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                                break;
                            case 'addcategory':
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>'stafflistcategories')), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                                $text = ($isnew) ? esc_html(__('Add Category', 'js-support-ticket')) : esc_html(__('Edit Category', 'js-support-ticket'));
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => $text);
                                break;
                            case 'articledetails':
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Knowledge Base Detail', 'js-support-ticket')));
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
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module, 'jstlay'=>$layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
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
                        }
                        break;
                    case 'ticket':
                        // Add default module link
                        switch ($layout) {
                            case 'addticket':
                                $layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket':'myticket';
                                $module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$layout1)), 'text'=>esc_html(__('My Tickets','js-support-ticket')));
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
                                $module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$layout1)), 'text'=>esc_html(__('My Tickets','js-support-ticket')));
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail')), 'text' => esc_html(__('Ticket Detail', 'js-support-ticket')));
                                break;
                            case 'ticketstatus':
                                $array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')), 'text' => esc_html(__('Ticket Status', 'js-support-ticket')));
                                break;
                        }
                        break;
                }
            }
        }

        if (isset($array)) {
            $count = count($array);
            $i = 0;
            echo '<div class="js-ticket-breadcrumb-wrp">
                    <ul class="breadcrumb js-ticket-breadcrumb">';
                        foreach ($array AS $obj) {
                            if ($i == 0) {
                                echo '
                                <li>
                                    <a href="' . esc_url($obj['link']) . '">
                                        <img class="homeicon" alt="'.esc_attr(__('home icon', 'js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/homeicon-white.png"/>
                                    </a>
                                </li>';
                            } else {
                                if ($i == ($count - 1)) {
                                    echo '
                                    <li>
                                        <a href="">
                                            ' . esc_html($obj['text']) . '
                                        </a>
                                    </li>';
                                } else {
                                    echo '
                                    <li>
                                        <a href="' . esc_url($obj['link']) . '">
                                            ' . esc_html($obj['text']) . '
                                        </a>
                                    </li>';
                                }
                            }
                        $i++;
                        }
            echo ' </ul>
                </div>';
        }
    }

}

$jsbreadcrumbs = new JSSTbreadcrumbs;
?>
