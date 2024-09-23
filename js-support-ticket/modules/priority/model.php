<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpriorityModel {

    function getPriorities() {
        // Filter
        $prioritytitle = jssupportticket::$_search['priority']['title'];
        $pagesize = jssupportticket::$_search['priority']['pagesize'];
        $inquery = '';

        if ($prioritytitle != null){
            $inquery .= " WHERE priority.priority LIKE '%".esc_sql($prioritytitle)."%'";
        }

        jssupportticket::$_data['filter']['title'] = $prioritytitle;
        jssupportticket::$_data['filter']['pagesize'] = $pagesize;

        // Pagination
        if($pagesize){
            JSSTpagination::setLimit($pagesize);
        }
        $query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ";
        $query .= $inquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        // Data
        $query = "SELECT priority.*
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ";
        $query .= $inquery;
        $query .= " ORDER BY priority.ordering ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getPriorityForCombobox() {
        $query = "SELECT id, priority AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`";
        if( in_array('agent',jssupportticket::$_active_addons) ){
            $agent = JSSTincluder::getJSModel('agent')->isUserStaff();
        }else{
            $agent = false;
        }

        if (!is_admin() && !$agent) {
            $query .= ' WHERE ispublic = 1 ';
        }
        $query .= 'ORDER BY ordering ASC';
        $priorities = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return apply_filters('jsst_priorities_for_combobox', $priorities);
    }

    function getDefaultPriorityID() {
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE isdefault = 1";
        $id = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $id;
    }

    function getPriorityForForm($id) {
        $result=array();
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT priority.*
						FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
						WHERE priority.id = " . esc_sql($id);
            $result = jssupportticket::$_db->get_row($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        jssupportticket::$_data[0]=$result;
        return;
    }

    function storePriority($data) {
        if (!$this->validatePriority($data['priority'], $data['id'])) {
            JSSTmessage::setMessage(esc_html(__('Priority Title Already Exist', 'js-support-ticket')), 'error');
            return;
        }
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        $data['prioritycolour'] = $data['prioritycolor'];

        if (!$data['id']) { //new
            $data['ordering'] = $this->getNextOrdering();
        }
        $row = JSSTincluder::getJSTable('priorities');
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
            if ($data['isdefault'] == 1) {
                $this->setDefaultPriority($id);
            }
            JSSTmessage::setMessage(esc_html(__('Priority has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function validatePriority($priority, $id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT priority FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE id = " . esc_sql($id);
            $result = jssupportticket::$_db->get_var($query);
            if ($result == $priority) {
                return true;
            }
        }

        $query = 'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_priorities` WHERE priority = "' . esc_sql($priority) . '"';
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
        $query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $result + 1;
    }

    function setDefaultPriority($id) {
        if (!is_numeric($id))
            return false;
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET isdefault = 2";
        jssupportticket::$_db->query($query);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET isdefault = 1 WHERE id = " . esc_sql($id);
        jssupportticket::$_db->query($query);
        return;
    }

    function removePriority($id) {
        if (!is_numeric($id))
            return false;
        $canremove = $this->canRemovePriority($id);
        if ($canremove == 1) {
            $row = JSSTincluder::getJSTable('priorities');
            if ($row->delete($id)) {
                JSSTmessage::setMessage(esc_html(__('Priority has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Priority has not been deleted', 'js-support-ticket')), 'error');
            }
        } elseif ($canremove == 2)
            JSSTmessage::setMessage(esc_html(__('Priority','js-support-ticket')).' '. esc_html(__('in use cannot deleted', 'js-support-ticket')), 'error');
        elseif ($canremove == 3)
            JSSTmessage::setMessage(esc_html(__('Default priority cannot delete', 'js-support-ticket')), 'error');

        return;
    }

    function makeDefault($id) {
        if (!is_numeric($id))
            return false;
        //Reset all priorities to non-default
        $query = "UPDATE `" . jssupportticket::$_db->prefix . 'js_ticket_priorities` SET isdefault = 0';
        jssupportticket::$_db->query($query);
        //Make the selected priority as default
        $query = "UPDATE `" . jssupportticket::$_db->prefix . 'js_ticket_priorities` SET isdefault = 1 WHERE id = ' . esc_sql($id);
        jssupportticket::$_db->query($query);
        if (jssupportticket::$_db->last_error == null) {
            JSSTmessage::setMessage(esc_html(__('Priority has been make default', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority has not been make default', 'js-support-ticket')), 'error');
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
        $query = "SELECT t.ordering,t.id,t2.ordering AS ordering2 FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS t,`" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS t2 WHERE t.ordering $order t2.ordering AND t2.id = $id ORDER BY t.ordering $direction LIMIT 1";
        $result = jssupportticket::$_db->get_row($query);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET ordering = " . esc_sql($result->ordering) . " WHERE id = " . esc_sql($id);
        jssupportticket::$_db->query($query);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET ordering = " . esc_sql($result->ordering2) . " WHERE id = " . esc_sql($result->id);
        jssupportticket::$_db->query($query);

        $row = JSSTincluder::getJSTable('priorities');
        if ($row->update(array('id' => $id, 'ordering' => $result->ordering)) && $row->update(array('id' => $result->id, 'ordering' => $result->ordering2))) {
            JSSTmessage::setMessage(esc_html(__('Priority','js-support-ticket')).' '. esc_html(__('ordering has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority','js-support-ticket')).' '. esc_html(__('ordering has not changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function canRemovePriority($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT (
					(SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE priorityid = " . esc_sql($id) . ")
					) AS total";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($result == 0) {
            $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE isdefault = 1 AND id = " . esc_sql($id);
            $result = jssupportticket::$_db->get_var($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            if ($result == 0)
                return 1;
            else
                return 3;
        } else
            return 2;
    }

    function getPriorityById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT priority FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE id = ". esc_sql($id);
        $priority = jssupportticket::$_db->get_var($query);
        return $priority;
    }

    function getAdminSearchFormDataPriority(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'priorities') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['title'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('title')));
        $jsst_search_array['pagesize'] = absint(JSSTrequest::getVar('pagesize'));
        $jsst_search_array['search_from_priority'] = 1;
        return $jsst_search_array;
    }
}

?>
