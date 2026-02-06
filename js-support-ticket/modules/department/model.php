<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTdepartmentModel {

    function getDepartments() {
        // Filter
        $jsst_isadmin = is_admin();
        $jsst_deptname = ($jsst_isadmin) ? 'departmentname' : 'jsst-dept';

        $jsst_departmentname = isset(jssupportticket::$_search['department']) ? jssupportticket::$_search['department']['departmentname'] : '';
        $jsst_pagesize = isset(jssupportticket::$_search['department']) ? jssupportticket::$_search['department']['pagesize'] : '';

        $jsst_departmentname = jssupportticket::parseSpaces($jsst_departmentname);
        $jsst_inquery = '';
        if ($jsst_departmentname != null)
            $jsst_inquery .= " WHERE department.departmentname LIKE '%".esc_sql($jsst_departmentname)."%'";

        jssupportticket::$jsst_data['filter'][$jsst_deptname] = $jsst_departmentname;
        jssupportticket::$jsst_data['filter']['pagesize'] = $jsst_pagesize;

        // Pagination
        if($jsst_pagesize){
            JSSTpagination::setLimit($jsst_pagesize);
        }
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total,'departments');

        // Data
        $jsst_query = "SELECT department.*,email.email AS outgoingemail
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ON email.id = department.emailid ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY department.ordering ASC,department.departmentname ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getDepartmentForForm($jsst_id) {
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT department.*,email.email AS outgoingemail
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ON email.id = department.emailid
                        WHERE department.id = " . esc_sql($jsst_id);
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    private function getNextOrdering() {
        $jsst_query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments`";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_result + 1;
    }

    function storeDepartment($jsst_data) {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-department-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_task_allow = ($jsst_data['id'] == '') ? 'Add Department' : 'Edit Department';
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_task_allow);
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')) . ' ' . $jsst_task_allow, 'error');
                return;
            }
        } else if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }

        if($jsst_data['sendmail'] == 1 && is_numeric($jsst_data['emailid'])){
            if ( in_array('emailpiping',jssupportticket::$_active_addons)) {
                $jsst_query = "SELECT emailaddress FROM `" . jssupportticket::$_db->prefix . "js_ticket_ticketsemail` ";
                $jsst_emailaddresses = jssupportticket::$_db->get_results($jsst_query);
            }else{
                $jsst_emailaddresses = array();
            }
            $jsst_query = "SELECT email FROM `" . jssupportticket::$_db->prefix . "js_ticket_email`
                WHERE id = ".esc_sql($jsst_data['emailid']);
            $jsst_email = jssupportticket::$_db->get_var($jsst_query);

            foreach ($jsst_emailaddresses as $jsst_edata) {
                if($jsst_email == $jsst_edata->emailaddress){
                    JSSTmessage::setMessage(esc_html(__('You cannot use this email, it is used in email piping', 'js-support-ticket')), 'error');
                    return;
                }
            }
        }

        if ($jsst_data['id'])
            $jsst_data['updated'] = date_i18n('Y-m-d H:i:s');
        else
            $jsst_data['created'] = date_i18n('Y-m-d H:i:s');

        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions
        $jsst_data['departmentsignature'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData( wp_unslash($_POST['departmentsignature'] ?? '') );

        if (!$jsst_data['id']) { //new
            $jsst_data['ordering'] = $this->getNextOrdering();
        }
        if (isset($jsst_data['canappendsignature'])) { //new
            $jsst_data['canappendsignature'] = 1;
        }else{
            $jsst_data['canappendsignature'] = 0;
        }

        $jsst_row = JSSTincluder::getJSTable('departments');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            if ($jsst_row->isdefault) {
                if ($jsst_row->isdefault == 1) {
                    $this->changeDefault($jsst_row->id, 0);
                } elseif ($jsst_row->isdefault == 2) {
                    $this->changeDefault($jsst_row->id, -1);
                }
            }
            JSSTmessage::setMessage(esc_html(__('The department has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The department has not been stored', 'js-support-ticket')), 'error');
        }

        return;
    }

    function setOrdering($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_order = JSSTrequest::getVar('order', 'get');
        if ($jsst_order == 'down') {
            $jsst_order = ">";
            $jsst_direction = "ASC";
        } else {
            $jsst_order = "<";
            $jsst_direction = "DESC";
        }
        $jsst_query = "SELECT t.ordering,t.id,t2.ordering AS ordering2 FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS t,`" . jssupportticket::$_db->prefix . "js_ticket_departments` AS t2 WHERE t.ordering $jsst_order t2.ordering AND t2.id = ".esc_sql($jsst_id)." ORDER BY t.ordering $jsst_direction LIMIT 1";
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);

        $jsst_row = JSSTincluder::getJSTable('departments');
        if ($jsst_row->update(array('id' => $jsst_id, 'ordering' => $jsst_result->ordering)) && $jsst_row->update(array('id' => $jsst_result->id, 'ordering' => $jsst_result->ordering2))) {
            JSSTmessage::setMessage(esc_html(__('Departments','js-support-ticket')).' '.esc_html(__('ordering has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Departments','js-support-ticket')).' '. esc_html(__('ordering has not changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    function removeDepartment($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Department');
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        if ($this->canRemoveDepartment($jsst_id)) {

            $jsst_row = JSSTincluder::getJSTable('departments');
            if ($jsst_row->delete($jsst_id)) {
                if(in_array('agent',jssupportticket::$_active_addons)){
                    $jsst_query = "DELETE
                                FROM `".jssupportticket::$_db->prefix . "js_ticket_acl_role_access_departments`
                                WHERE departmentid = ".esc_sql($jsst_id);
                    jssupportticket::$_db->query($jsst_query);
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

    private function canRemoveDepartment($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT (
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE departmentid = " . esc_sql($jsst_id) . ")
                    + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id = " . esc_sql($jsst_id) . " AND isdefault = 1) ";

                    if(in_array('agent', jssupportticket::$_active_addons)){
                        $jsst_query .= " + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` WHERE departmentid = " . esc_sql($jsst_id) . ") ";
                    }

                    if(in_array('helptopic', jssupportticket::$_active_addons)){
                        $jsst_query .= " + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE departmentid = " . esc_sql($jsst_id) . ") ";
                    }

                    if(in_array('cannedresponses', jssupportticket::$_active_addons)){
                        $jsst_query .= " + (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` WHERE departmentid = " . esc_sql($jsst_id) . ")";
                    }

                    $jsst_query .= " ) AS total";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result == 0)
            return true;
        else
            return false;
    }

    function getDepartmentForCombobox() {
        $jsst_query = "SELECT id, departmentname AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE status = 1";
        /*if (!is_admin()) {
            $jsst_query .= '  AND ispublic = 1';
        }*/
        $jsst_query .= " ORDER BY ordering";
        $jsst_list = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_list;
    }

    function changeStatus($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT status  FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id=" . esc_sql($jsst_id);
           $jsst_status = jssupportticket::$_db->get_var($jsst_query);
       $jsst_status = 1 - $jsst_status;

       $jsst_row = JSSTincluder::getJSTable('departments');
       if ($jsst_row->update(array('id' => $jsst_id, 'status' => $jsst_status))) {
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('status has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('status has not been changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    function changeDefault($jsst_id,$jsst_default) {
        if (!is_numeric($jsst_id))
            return false;

        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_departments` SET isdefault = 0 WHERE id != " . esc_sql($jsst_id);
        jssupportticket::$_db->query($jsst_query);

        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_departments` SET isdefault = 1 - $jsst_default WHERE id=" . esc_sql($jsst_id);
        jssupportticket::$_db->query($jsst_query);

        if (jssupportticket::$_db->last_error == null) {
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('default has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Department','js-support-ticket')).' '. esc_html(__('default has not been changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    function getHelpTopicByDepartment() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-help-topic-by-department') ) {
            die( 'Security check Failed' );
        }
        if(!in_array('helptopic', jssupportticket::$_active_addons)){
            return;
        }

        $jsst_departmentid = JSSTrequest::getVar('val');
        if (!is_numeric($jsst_departmentid)){
            return false;
        }

        $jsst_query = "SELECT id, topic AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE status = 1 AND departmentid = " . esc_sql($jsst_departmentid) . " ORDER BY ordering ASC";
        $jsst_list = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT required FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field='helptopic'";
        $jsst_isRequired = jssupportticket::$_db->get_var($jsst_query);

        $jsst_combobox = false;
        if(!empty($jsst_list)){
            $jsst_combobox = JSSTformfield::select('helptopicid', $jsst_list, '', esc_html(__('Select Help Topic', 'js-support-ticket')), array('class' => 'inputbox js-ticket-select-field','data-validation'=>($jsst_isRequired ? 'required' : '')));
        }
        return $jsst_combobox;
    }

    function getPremadeByDepartment() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-premade-by-department') ) {
            die( 'Security check Failed' );
        }
        if(!in_array('cannedresponses', jssupportticket::$_active_addons)){
            return false;
        }
        $jsst_departmentid = JSSTrequest::getVar('val');
        if (!is_numeric($jsst_departmentid))
            return false;
        $jsst_query = "SELECT id, title AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` WHERE status = 1 AND departmentid = " . esc_sql($jsst_departmentid);
        $jsst_query .= " ORDER BY title ASC ";
        $jsst_list = jssupportticket::$_db->get_results($jsst_query);
        $jsst_combobox = false;
        $jsst_html = '';
        if(!empty($jsst_list)){
            foreach($jsst_list as $jsst_premade){
                $jsst_html .= '<div class="js-form-perm-msg" onclick="getpremade('.esc_js($jsst_premade->id).');">
                    <a href="#" title="'. esc_html(__('Canned response','js-support-ticket')).'">'.wp_kses($jsst_premade->text, JSST_ALLOWED_TAGS).'</a>
                </div>';


            }
        }else{
            $jsst_html = '<div class="js-form-perm-msg">
                <div>'. esc_html(__('No Record Found','js-support-ticket')) .'</div>
            </div>';
        }

        /*if(!empty($jsst_list)){
            $jsst_combobox = JSSTformfield::select('premadeid', $jsst_list, '', esc_html(__('Select Premade', 'js-support-ticket')), array('class' => 'inputbox js-ticket-select-field', 'onchange' => 'getpremade(this.value)'));
        }else{
            $jsst_combobox .= '<span id = "js-ticket-no-premade">' . esc_html(__('No premade found','js-support-ticket')).'</span>';
        }*/
        return jssupportticketphplib::JSST_htmlentities($jsst_html);
    }

    function getSignatureByID($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT departmentsignature FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id = " . esc_sql($jsst_id);
        $jsst_signature = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_signature;
    }

    function getDepartmentById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT departmentname FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id = " . esc_sql($jsst_id);
        $jsst_departmentname = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_departmentname;
    }

    function getDefaultDepartmentID() {
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE isdefault = 1 OR isdefault = 2";
        $jsst_departmentid = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_departmentid;
    }

    function getDepartmentIDForAutoAssign() {
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE isdefault = 2 AND status = 1";
        $jsst_departmentid = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_departmentid;
    }

    function getAdminDepartmentSearchFormData(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'departments') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_isadmin = is_admin();
        $jsst_deptname = ($jsst_isadmin) ? 'departmentname' : 'jsst-dept';
        $jsst_search_array['departmentname'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar($jsst_deptname)));
        $jsst_search_array['pagesize'] = absint(JSSTrequest::getVar('pagesize'));
        $jsst_search_array['search_from_department'] = 1;
        return $jsst_search_array;
    }

}

?>
