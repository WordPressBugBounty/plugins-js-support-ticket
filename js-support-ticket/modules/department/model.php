<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTdepartmentModel {

    function getDepartments() {
        // Filter
        $isadmin = is_admin();
        $deptname = ($isadmin) ? 'departmentname' : 'jsst-dept';

        $departmentname = isset(jssupportticket::$_search['department']) ? jssupportticket::$_search['department']['departmentname'] : '';
        $pagesize = isset(jssupportticket::$_search['department']) ? jssupportticket::$_search['department']['pagesize'] : '';

        $departmentname = jssupportticket::parseSpaces($departmentname);
        $inquery = '';
        if ($departmentname != null)
            $inquery .= " WHERE department.departmentname LIKE '%".esc_sql($departmentname)."%'";

        jssupportticket::$_data['filter'][$deptname] = $departmentname;
        jssupportticket::$_data['filter']['pagesize'] = $pagesize;

        // Pagination
        if($pagesize){
            JSSTpagination::setLimit($pagesize);
        }
        $query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department";
        $query .= $inquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total,'departments');

        // Data
        $query = "SELECT department.*,email.email AS outgoingemail
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ON email.id = department.emailid ";
        $query .= $inquery;
        $query .= " ORDER BY department.ordering ASC,department.departmentname ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getDepartmentForForm($id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT department.*,email.email AS outgoingemail
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ON email.id = department.emailid
                        WHERE department.id = " . esc_sql($id);
            jssupportticket::$_data[0] = jssupportticket::$_db->get_row($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    private function getNextOrdering() {
        $query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments`";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $result + 1;
    }

    function storeDepartment($data) {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-department') ) {
            die( 'Security check Failed' );
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $task_allow = ($data['id'] == '') ? 'Add Department' : 'Edit Department';
            $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($task_allow);
            if ($allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')) . ' ' . $task_allow, 'error');
                return;
            }
        }

        if($data['sendmail'] == 1 && is_numeric($data['emailid'])){
            if ( in_array('emailpiping',jssupportticket::$_active_addons)) {
                $query = "SELECT emailaddress FROM `" . jssupportticket::$_db->prefix . "js_ticket_ticketsemail` ";
                $emailaddresses = jssupportticket::$_db->get_results($query);
            }else{
                $emailaddresses = array();
            }
            $query = "SELECT email FROM `" . jssupportticket::$_db->prefix . "js_ticket_email`
                WHERE id = ".esc_sql($data['emailid']);
            $email = jssupportticket::$_db->get_var($query);

            foreach ($emailaddresses as $edata) {
                if($email == $edata->emailaddress){
                    JSSTmessage::setMessage(esc_html(__('You cannot use this email, it is used in email piping', 'js-support-ticket')), 'error');
                    return;
                }
            }
        }

        if ($data['id'])
            $data['updated'] = date_i18n('Y-m-d H:i:s');
        else
            $data['created'] = date_i18n('Y-m-d H:i:s');

        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        $data['departmentsignature'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['departmentsignature']);

        if (!$data['id']) { //new
            $data['ordering'] = $this->getNextOrdering();
        }
        if (isset($data['canappendsignature'])) { //new
            $data['canappendsignature'] = 1;
        }else{
            $data['canappendsignature'] = 0;
        }

        $row = JSSTincluder::getJSTable('departments');

        $data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }

        if ($error == 0) {
            if ($row->isdefault) {
                if ($row->isdefault == 1) {
                    $this->changeDefault($row->id, 0);
                } elseif ($row->isdefault == 2) {
                    $this->changeDefault($row->id, -1);
                }
            }
            JSSTmessage::setMessage(esc_html(__('The department has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The department has not been stored', 'js-support-ticket')), 'error');
        }

        return;
    }

    function setOrdering($id) {
        if (!is_numeric($id))
            return false;
        $order = JSSTrequest::getVar('order', 'get');
        if ($order == 'down') {
            $order = ">";
            $direction = "ASC";
        } else {
            $order = "<";
            $direction = "DESC";
        }
        $query = "SELECT t.ordering,t.id,t2.ordering AS ordering2 FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS t,`" . jssupportticket::$_db->prefix . "js_ticket_departments` AS t2 WHERE t.ordering $order t2.ordering AND t2.id = ".esc_sql($id)." ORDER BY t.ordering $direction LIMIT 1";
        $result = jssupportticket::$_db->get_row($query);

        $row = JSSTincluder::getJSTable('departments');
        if ($row->update(array('id' => $id, 'ordering' => $result->ordering)) && $row->update(array('id' => $result->id, 'ordering' => $result->ordering2))) {
            JSSTmessage::setMessage(esc_html(__('Departments','js-support-ticket')).' '.esc_html(__('ordering has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Departments','js-support-ticket')).' '. esc_html(__('ordering has not changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    function removeDepartment($id) {
        if (!is_numeric($id))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Department');
            if ($allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        if ($this->canRemoveDepartment($id)) {

            $row = JSSTincluder::getJSTable('departments');
            if ($row->delete($id)) {
                if(in_array('agent',jssupportticket::$_active_addons)){
                    $query = "DELETE
                                FROM `".jssupportticket::$_db->prefix . "js_ticket_acl_role_access_departments`
                                WHERE departmentid = ".esc_sql($id);
                    jssupportticket::$_db->query($query);
                }
                JSSTmessage::setMessage(esc_html(__('The department has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                JSSTmessage::setMessage(esc_html(__('The department has not been deleted', 'js-support-ticket')), 'error');
            }
        } else {
            JSSTmessage::setMessage(esc_html(__('The department in use cannot be delete', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function canRemoveDepartment($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT (
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE departmentid = " . esc_sql($id) . ")
                    + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id = " . esc_sql($id) . " AND isdefault = 1) ";

                    if(in_array('agent', jssupportticket::$_active_addons)){
                        $query .= " + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` WHERE departmentid = " . esc_sql($id) . ") ";
                    }

                    if(in_array('helptopic', jssupportticket::$_active_addons)){
                        $query .= " + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE departmentid = " . esc_sql($id) . ") ";
                    }

                    if(in_array('cannedresponses', jssupportticket::$_active_addons)){
                        $query .= " + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` WHERE departmentid = " . esc_sql($id) . ")";
                    }

                    $query .= " ) AS total";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($result == 0)
            return true;
        else
            return false;
    }

    function getDepartmentForCombobox() {
        $query = "SELECT id, departmentname AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE status = 1";
        /*if (!is_admin()) {
            $query .= '  AND ispublic = 1';
        }*/
        $query .= " ORDER BY ordering";
        $list = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $list;
    }

    function changeStatus($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT status  FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id=" . esc_sql($id);
           $status = jssupportticket::$_db->get_var($query);
       $status = 1 - $status;

       $row = JSSTincluder::getJSTable('departments');
       if ($row->update(array('id' => $id, 'status' => $status))) {
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('status has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('status has not been changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    function changeDefault($id,$default) {
        if (!is_numeric($id))
            return false;

        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_departments` SET isdefault = 0 WHERE id != " . esc_sql($id);
        jssupportticket::$_db->query($query);

        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_departments` SET isdefault = 1 - $default WHERE id=" . esc_sql($id);
        jssupportticket::$_db->query($query);

        if (jssupportticket::$_db->last_error == null) {
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('default has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('default has not been changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    function getHelpTopicByDepartment() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-help-topic-by-department') ) {
            die( 'Security check Failed' );
        }
        if(!in_array('helptopic', jssupportticket::$_active_addons)){
            return;
        }

        $departmentid = JSSTrequest::getVar('val');
        if (!is_numeric($departmentid)){
            return false;
        }

        $query = "SELECT id, topic AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE status = 1 AND departmentid = " . esc_sql($departmentid) . " ORDER BY ordering ASC";
        $list = jssupportticket::$_db->get_results($query);

        $query = "SELECT required FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field='helptopic'";
        $isRequired = jssupportticket::$_db->get_var($query);

        $combobox = false;
        if(!empty($list)){
            $combobox = JSSTformfield::select('helptopicid', $list, '', esc_html(__('Select Help Topic', 'js-support-ticket')), array('class' => 'inputbox js-ticket-select-field','data-validation'=>($isRequired ? 'required' : '')));
        }
        return $combobox;
    }

    function getPremadeByDepartment() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-premade-by-department') ) {
            die( 'Security check Failed' );
        }
        if(!in_array('cannedresponses', jssupportticket::$_active_addons)){
            return false;
        }
        $departmentid = JSSTrequest::getVar('val');
        if (!is_numeric($departmentid))
            return false;
        $query = "SELECT id, title AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` WHERE status = 1 AND departmentid = " . esc_sql($departmentid);
        $list = jssupportticket::$_db->get_results($query);
        $combobox = false;
        $html = '';
        if(!empty($list)){
            foreach($list as $premade){
                $html .= '<div class="js-form-perm-msg" onclick="getpremade('.esc_js($premade->id).');">
                    <a href="#" title="'. esc_html(__('Canned response','js-support-ticket')).'">'.wp_kses($premade->text, JSST_ALLOWED_TAGS).'</a>
                </div>';


            }
        }else{
            $html = '<div class="js-form-perm-msg">
                <div>'. esc_html(__('No Record Found','js-support-ticket')) .'</div>
            </div>';
        }

        /*if(!empty($list)){
            $combobox = JSSTformfield::select('premadeid', $list, '', esc_html(__('Select Premade', 'js-support-ticket')), array('class' => 'inputbox js-ticket-select-field', 'onchange' => 'getpremade(this.value)'));
        }else{
            $combobox .= '<span id = "js-ticket-no-premade">' . esc_html(__('No premade found','js-support-ticket')).'</span>';
        }*/
        return jssupportticketphplib::JSST_htmlentities($html);
    }

    function getSignatureByID($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT departmentsignature FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id = " . esc_sql($id);
        $signature = jssupportticket::$_db->get_var($query);
        return $signature;
    }

    function getDepartmentById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT departmentname FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id = " . esc_sql($id);
        $departmentname = jssupportticket::$_db->get_var($query);
        return $departmentname;
    }

    function getDefaultDepartmentID() {
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE isdefault = 1 OR isdefault = 2";
        $departmentid = jssupportticket::$_db->get_var($query);
        return $departmentid;
    }

    function getDepartmentIDForAutoAssign() {
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE isdefault = 2 AND status = 1";
        $departmentid = jssupportticket::$_db->get_var($query);
        return $departmentid;
    }

    function getAdminDepartmentSearchFormData(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'departments') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $isadmin = is_admin();
        $deptname = ($isadmin) ? 'departmentname' : 'jsst-dept';
        $jsst_search_array['departmentname'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar($deptname)));
        $jsst_search_array['pagesize'] = absint(JSSTrequest::getVar('pagesize'));
        $jsst_search_array['search_from_department'] = 1;
        return $jsst_search_array;
    }

}

?>
