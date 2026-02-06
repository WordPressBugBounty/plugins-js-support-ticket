<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpriorityModel {

    function getPriorities() {
        // Filter
        $jsst_prioritytitle = jssupportticket::$_search['priority']['title'];
        $jsst_pagesize = jssupportticket::$_search['priority']['pagesize'];
        $jsst_inquery = '';

        if ($jsst_prioritytitle != null){
            $jsst_inquery .= " WHERE priority.priority LIKE '%".esc_sql($jsst_prioritytitle)."%'";
        }

        jssupportticket::$jsst_data['filter']['title'] = $jsst_prioritytitle;
        jssupportticket::$jsst_data['filter']['pagesize'] = $jsst_pagesize;

        // Pagination
        if($jsst_pagesize){
            JSSTpagination::setLimit($jsst_pagesize);
        }
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        // Data
        $jsst_query = "SELECT priority.*
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY priority.ordering ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getPriorityForCombobox() {
        $jsst_query = "SELECT id, priority AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`";
        if( in_array('agent',jssupportticket::$_active_addons) ){
            $jsst_agent = JSSTincluder::getJSModel('agent')->isUserStaff();
        }else{
            $jsst_agent = false;
        }

        if (!is_admin() && !$jsst_agent) {
            $jsst_query .= ' WHERE ispublic = 1 ';
        }
        $jsst_query .= 'ORDER BY ordering ASC';
        $jsst_priorities = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return apply_filters('jsst_priorities_for_combobox', $jsst_priorities);
    }

    function getDefaultPriorityID() {
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE isdefault = 1";
        $jsst_id = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_id;
    }

    function getPriorityForForm($jsst_id) {
        $jsst_result=array();
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT priority.*
						FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
						WHERE priority.id = " . esc_sql($jsst_id);
            $jsst_result = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        jssupportticket::$jsst_data[0]=$jsst_result;
        return;
    }

    function storePriority($jsst_data) {
        if (!$this->validatePriority($jsst_data['priority'], $jsst_data['id'])) {
            JSSTmessage::setMessage(esc_html(__('Priority Title Already Exist', 'js-support-ticket')), 'error');
            return;
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions
        $jsst_data['prioritycolour'] = $jsst_data['prioritycolor'];

        if (!$jsst_data['id']) { //new
            $jsst_data['ordering'] = $this->getNextOrdering();
        }
        $jsst_row = JSSTincluder::getJSTable('priorities');
        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            $jsst_id = $jsst_row->id;
            if ($jsst_data['isdefault'] == 1) {
                $this->setDefaultPriority($jsst_id);
            }
            JSSTmessage::setMessage(esc_html(__('Priority has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function validatePriority($jsst_priority, $jsst_id) {
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT priority FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE id = " . esc_sql($jsst_id);
            $jsst_result = jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_result == $jsst_priority) {
                return true;
            }
        }

        $jsst_query = 'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_priorities` WHERE priority = "' . esc_sql($jsst_priority) . '"';
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result == 0)
            return true;
        else
            return false;
    }

    private function getNextOrdering() {
        $jsst_query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_result + 1;
    }

    function setDefaultPriority($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET isdefault = 2";
        jssupportticket::$_db->query($jsst_query);
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET isdefault = 1 WHERE id = " . esc_sql($jsst_id);
        jssupportticket::$_db->query($jsst_query);
        return;
    }

    function removePriority($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_canremove = $this->canRemovePriority($jsst_id);
        if ($jsst_canremove == 1) {
            $jsst_row = JSSTincluder::getJSTable('priorities');
            if ($jsst_row->delete($jsst_id)) {
                JSSTmessage::setMessage(esc_html(__('Priority has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Priority has not been deleted', 'js-support-ticket')), 'error');
            }
        } elseif ($jsst_canremove == 2)
            JSSTmessage::setMessage(esc_html(__('Priority','js-support-ticket')).' '. esc_html(__('in use cannot deleted', 'js-support-ticket')), 'error');
        elseif ($jsst_canremove == 3)
            JSSTmessage::setMessage(esc_html(__('Default priority cannot delete', 'js-support-ticket')), 'error');

        return;
    }

    function makeDefault($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        //Reset all priorities to non-default
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . 'js_ticket_priorities` SET isdefault = 0';
        jssupportticket::$_db->query($jsst_query);
        //Make the selected priority as default
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . 'js_ticket_priorities` SET isdefault = 1 WHERE id = ' . esc_sql($jsst_id);
        jssupportticket::$_db->query($jsst_query);
        if (jssupportticket::$_db->last_error == null) {
            JSSTmessage::setMessage(esc_html(__('Priority has been make default', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority has not been make default', 'js-support-ticket')), 'error');
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
        $jsst_query = "SELECT t.ordering,t.id,t2.ordering AS ordering2 FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS t,`" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS t2 WHERE t.ordering $jsst_order t2.ordering AND t2.id = ".esc_sql($jsst_id)." ORDER BY t.ordering $jsst_direction LIMIT 1";
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET ordering = " . esc_sql($jsst_result->ordering) . " WHERE id = " . esc_sql($jsst_id);
        jssupportticket::$_db->query($jsst_query);
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_priorities` SET ordering = " . esc_sql($jsst_result->ordering2) . " WHERE id = " . esc_sql($jsst_result->id);
        jssupportticket::$_db->query($jsst_query);

        $jsst_row = JSSTincluder::getJSTable('priorities');
        if ($jsst_row->update(array('id' => $jsst_id, 'ordering' => $jsst_result->ordering)) && $jsst_row->update(array('id' => $jsst_result->id, 'ordering' => $jsst_result->ordering2))) {
            JSSTmessage::setMessage(esc_html(__('Priority','js-support-ticket')).' '. esc_html(__('ordering has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority','js-support-ticket')).' '. esc_html(__('ordering has not changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function canRemovePriority($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT (
					(SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE priorityid = " . esc_sql($jsst_id) . ")
					) AS total";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result == 0) {
            $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE isdefault = 1 AND id = " . esc_sql($jsst_id);
            $jsst_result = jssupportticket::$_db->get_var($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            if ($jsst_result == 0)
                return 1;
            else
                return 3;
        } else
            return 2;
    }

    function getPriorityById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT priority FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` WHERE id = ". esc_sql($jsst_id);
        $jsst_priority = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_priority;
    }

    function getAdminSearchFormDataPriority(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'priorities') ) {
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
