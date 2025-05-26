<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTstatusModel {

    function getStatuses() {
        // Filter
        $statustitle = jssupportticket::$_search['status']['status'];
        $pagesize = jssupportticket::$_search['status']['pagesize'];
        $inquery = '';

        if ($statustitle != null){
            $inquery .= " WHERE status.status LIKE '%".esc_sql($statustitle)."%'";
        }

        jssupportticket::$_data['filter']['title'] = $statustitle;
        jssupportticket::$_data['filter']['pagesize'] = $pagesize;

        // Pagination
        if($pagesize){
            JSSTpagination::setLimit($pagesize);
        }
        $query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ";
        $query .= $inquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        // Data
        $query = "SELECT status.*
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ";
        $query .= $inquery;
        $query .= " ORDER BY status.ordering ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getStatusForCombobox() {
        $query = "SELECT id, status AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses`";
        
            
        $query .= 'ORDER BY ordering ASC';
        $statuses = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $statuses;
    }

    function getStatusForForm($id) {
        $result=array();
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT status.*
						FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status
						WHERE status.id = " . esc_sql($id);
            $result = jssupportticket::$_db->get_row($query);
            if ($result) {
                $customStatuses = [
                    1 => 'New',
                    2 => 'Waiting Reply',
                    3 => 'In Progress',
                    4 => 'Replied',
                    5 => 'Closed',
                    6 => 'Close due to merge'
                ];
                // add custom status
                $result->custom_status = isset($customStatuses[$result->id]) ? $customStatuses[$result->id] : '';
            }
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        jssupportticket::$_data[0]=$result;
        return;
    }

    function storeStatus($data) {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        if (!$this->validateStatus($data['status'], $data['id'])) {
            JSSTmessage::setMessage(esc_html(__('Status Title Already Exist', 'js-support-ticket')), 'error');
            return;
        }
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        $data['statuscolour'] = $data['statuscolor'];
        $data['statusbgcolour'] = $data['statusbgcolor'];

        if (!$data['id']) { //new
            $data['ordering'] = $this->getNextOrdering();
        }
        $row = JSSTincluder::getJSTable('statuses');
        $data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }

        if ($error == 0) {
            $id = $row->id;
            JSSTmessage::setMessage(esc_html(__('Status has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Status has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function validateStatus($status, $id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` WHERE id = " . esc_sql($id);
            $result = jssupportticket::$_db->get_var($query);
            if ($result == $status) {
                return true;
            }
        }

        $query = 'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_statuses` WHERE status = "' . esc_sql($status) . '"';
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($result == 0)
            return true;
        else
            return false;
    }

    private function getNextOrdering() {
        $query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses`";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $result + 1;
    }

    function removeStatus($id) {
        if (!is_numeric($id))
            return false;
        $canremove = $this->canRemoveStatus($id);
        if ($canremove == 1) {
            $row = JSSTincluder::getJSTable('statuses');
            if ($row->delete($id)) {
                JSSTmessage::setMessage(esc_html(__('Status has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Status has not been deleted', 'js-support-ticket')), 'error');
            }
        } elseif ($canremove == 2)
            JSSTmessage::setMessage(esc_html(__('Status','js-support-ticket')).' '. esc_html(__('in use cannot deleted', 'js-support-ticket')), 'error');

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
        $query = "SELECT t.ordering,t.id,t2.ordering AS ordering2 FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS t,`" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS t2 WHERE t.ordering $order t2.ordering AND t2.id = ".esc_sql($id)." ORDER BY t.ordering $direction LIMIT 1";
        $result = jssupportticket::$_db->get_row($query);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_statuses` SET ordering = " . esc_sql($result->ordering) . " WHERE id = " . esc_sql($id);
        jssupportticket::$_db->query($query);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_statuses` SET ordering = " . esc_sql($result->ordering2) . " WHERE id = " . esc_sql($result->id);
        jssupportticket::$_db->query($query);

        $row = JSSTincluder::getJSTable('statuses');
        if ($row->update(array('id' => $id, 'ordering' => $result->ordering)) && $row->update(array('id' => $result->id, 'ordering' => $result->ordering2))) {
            JSSTmessage::setMessage(esc_html(__('Status','js-support-ticket')).' '. esc_html(__('ordering has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Status','js-support-ticket')).' '. esc_html(__('ordering has not changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function canRemoveStatus($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT (
					(SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = " . esc_sql($id) . ")
					) AS total";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($result == 0) {
            return 1;
        } else
            return 2;
    }

    function getStatusById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` WHERE id = ". esc_sql($id);
        $status = jssupportticket::$_db->get_var($query);
        return $status;
    }

    function getAdminSearchFormDataStatus(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'statuses') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['title'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('title')));
        $jsst_search_array['pagesize'] = absint(JSSTrequest::getVar('pagesize'));
        $jsst_search_array['search_from_status'] = 1;
        return $jsst_search_array;
    }

    function getStatusForFilter() {
        $query = "SELECT id, status AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` WHERE id NOT IN (1 , 5, 6)";
        
            
        $query .= 'ORDER BY ordering ASC';
        $statuses = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $statuses;
    }
}

?>
