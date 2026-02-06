<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
class JSSTbreadcrumbs {

    static function getBreadcrumbs() {
        if (jssupportticket::$_config['show_breadcrumbs'] != 1)
            return false;
        if (!is_admin()) {
            $jsst_editid = JSSTrequest::getVar('jssupportticketid');
            $jsst_isnew = ($jsst_editid == null) ? true : false;
            $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel')), 'text' => esc_html(__('Control Panel', 'js-support-ticket')));
            $jsst_module = JSSTrequest::getVar('jstmod');
            $jsst_layout = JSSTrequest::getVar('jstlay');
            if (isset(jssupportticket::$jsst_data['short_code_header'])) {
                switch (jssupportticket::$jsst_data['short_code_header']){
                    case 'myticket':

                        $jsst_module = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                        $jsst_layout = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
                        break;
                    case 'addticket':
                        $jsst_module = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
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
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Announcement Detail', 'js-support-ticket')));
                                break;
                            case 'addannouncement':
                                $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffannouncements' : 'announcements';
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
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('FAQ Detail', 'js-support-ticket')));
                                break;
                            case 'faqs':
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                                break;
                            case 'stafffaqs':
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__("FAQ's", 'js-support-ticket')));
                                break;
                        }
                        break;
                    case 'feedback':
                        switch ($jsst_layout) {
                            case 'feedbacks':
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'feedback', 'jstlay'=>'feedbacks')), 'text' => esc_html(__("Feedbacks", 'js-support-ticket')));
                                break;
                        }
                        break;
                    case 'jssupportticket':
                        break;
                    case 'knowledgebase':
                        switch ($jsst_layout) {
                            case 'addarticle':
                                $jsst_text = ($jsst_isnew) ? esc_html(__('Add Knowledge Base', 'js-support-ticket')) : esc_html(__('Edit Knowledge Base', 'js-support-ticket'));
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                                break;
                            case 'addcategory':
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>'stafflistcategories')), 'text' => esc_html(__('Categories', 'js-support-ticket')));
                                $jsst_text = ($jsst_isnew) ? esc_html(__('Add Category', 'js-support-ticket')) : esc_html(__('Edit Category', 'js-support-ticket'));
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => $jsst_text);
                                break;
                            case 'articledetails':
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Knowledge Base Detail', 'js-support-ticket')));
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
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module, 'jstlay'=>$jsst_layout)), 'text' => esc_html(__('Message', 'js-support-ticket')));
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
                        }
                        break;
                    case 'ticket':
                        // Add default module link
                        switch ($jsst_layout) {
                            case 'addticket':
                                $jsst_layout1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket':'myticket';
                                $jsst_module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent':'ticket';
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_layout1)), 'text'=>esc_html(__('My Tickets','js-support-ticket')));
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
                                $jsst_module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_layout1)), 'text'=>esc_html(__('My Tickets','js-support-ticket')));
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail')), 'text' => esc_html(__('Ticket Detail', 'js-support-ticket')));
                                break;
                            case 'ticketstatus':
                                $jsst_array[] = array('link' => jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')), 'text' => esc_html(__('Ticket Status', 'js-support-ticket')));
                                break;
                        }
                        break;
                }
            }
        }

        if (isset($jsst_array)) {
            $jsst_count = count($jsst_array);
            $jsst_i = 0;
            echo '<div class="js-ticket-breadcrumb-wrp">
                    <ul class="breadcrumb js-ticket-breadcrumb">';
                        foreach ($jsst_array AS $jsst_obj) {
                            if ($jsst_i == 0) {
                                echo '
                                <li>
                                    <a href="' . esc_url($jsst_obj['link']) . '">
                                        <img class="homeicon" alt="'.esc_attr(__('home icon', 'js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/homeicon-white.png"/>
                                    </a>
                                </li>';
                            } else {
                                if ($jsst_i == ($jsst_count - 1)) {
                                    echo '
                                    <li>
                                        <a href="">
                                            ' . esc_html($jsst_obj['text']) . '
                                        </a>
                                    </li>';
                                } else {
                                    echo '
                                    <li>
                                        <a href="' . esc_url($jsst_obj['link']) . '">
                                            ' . esc_html($jsst_obj['text']) . '
                                        </a>
                                    </li>';
                                }
                            }
                        $jsst_i++;
                        }
            echo ' </ul>
                </div>';
        }
    }

}

$jsst_jsbreadcrumbs = new JSSTbreadcrumbs;
?>
