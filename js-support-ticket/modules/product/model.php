<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTproductModel {

    function getProducts() {
        // Filter
        $jsst_producttitle = jssupportticket::$_search['product']['product'];
        $jsst_pagesize = jssupportticket::$_search['product']['pagesize'];
        $jsst_inquery = '';

        if ($jsst_producttitle != null){
            $jsst_inquery .= " WHERE product.product LIKE '%".esc_sql($jsst_producttitle)."%'";
        }

        jssupportticket::$jsst_data['filter']['title'] = $jsst_producttitle;
        jssupportticket::$jsst_data['filter']['pagesize'] = $jsst_pagesize;

        // Pagination
        if($jsst_pagesize){
            JSSTpagination::setLimit($jsst_pagesize);
        }
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        // Data
        $jsst_query = "SELECT product.*
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY product.ordering ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getProductForCombobox() {
        $jsst_query = "SELECT id, product AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE status = 1 ";
        $jsst_query .= 'ORDER BY ordering ASC';
        $jsst_products = jssupportticket::$_db->get_results($jsst_query);

        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_products;
    }

    function getProductForForm($jsst_id) {
        $jsst_result=array();
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT product.*
				FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product
				WHERE product.id = " . esc_sql($jsst_id);
            $jsst_result = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        jssupportticket::$jsst_data[0]=$jsst_result;
        return;
    }

    function storeProduct($jsst_data) {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        if (!$this->validateProduct($jsst_data['product'], $jsst_data['id'])) {
            JSSTmessage::setMessage(esc_html(__('Product Title Already Exist', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions

        if (!$jsst_data['id']) { //new
            $jsst_data['ordering'] = $this->getNextOrdering();
        }
        $jsst_row = JSSTincluder::getJSTable('products');
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
            JSSTmessage::setMessage(esc_html(__('Product has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Product has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function validateProduct($jsst_product, $jsst_id) {
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT product FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE id = " . esc_sql($jsst_id);
            $jsst_result = jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_result == $jsst_product) {
                return true;
            }
        }

        $jsst_query = 'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_products` WHERE product = "' . esc_sql($jsst_product) . '"';
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
        $jsst_query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_products`";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_result + 1;
    }

    function removeProduct($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_canremove = $this->canRemoveProduct($jsst_id);
        if ($jsst_canremove == 1) {
            $jsst_row = JSSTincluder::getJSTable('products');
            if ($jsst_row->delete($jsst_id)) {
                JSSTmessage::setMessage(esc_html(__('Product has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Product has not been deleted', 'js-support-ticket')), 'error');
            }
        } elseif ($jsst_canremove == 2)
            JSSTmessage::setMessage(esc_html(__('Product','js-support-ticket')).' '. esc_html(__('in use cannot deleted', 'js-support-ticket')), 'error');

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
        $jsst_query = "SELECT t.ordering,t.id,t2.ordering AS ordering2 FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS t,`" . jssupportticket::$_db->prefix . "js_ticket_products` AS t2 WHERE t.ordering $jsst_order t2.ordering AND t2.id = ".esc_sql($jsst_id)." ORDER BY t.ordering $jsst_direction LIMIT 1";
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_products` SET ordering = " . esc_sql($jsst_result->ordering) . " WHERE id = " . esc_sql($jsst_id);
        jssupportticket::$_db->query($jsst_query);
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_products` SET ordering = " . esc_sql($jsst_result->ordering2) . " WHERE id = " . esc_sql($jsst_result->id);
        jssupportticket::$_db->query($jsst_query);

        $jsst_row = JSSTincluder::getJSTable('products');
        if ($jsst_row->update(array('id' => $jsst_id, 'ordering' => $jsst_result->ordering)) && $jsst_row->update(array('id' => $jsst_result->id, 'ordering' => $jsst_result->ordering2))) {
            JSSTmessage::setMessage(esc_html(__('Product','js-support-ticket')).' '. esc_html(__('ordering has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Product','js-support-ticket')).' '. esc_html(__('ordering has not changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function canRemoveProduct($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT (
					(SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE productid = " . esc_sql($jsst_id) . ")
					) AS total";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result == 0) {
            return 1;
        } else
            return 2;
    }

    function changeStatus($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;

        $jsst_query = "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE id=" . esc_sql($jsst_id);
        $jsst_status = jssupportticket::$_db->get_var($jsst_query);

        $jsst_status = 1 - $jsst_status;

        $jsst_row = JSSTincluder::getJSTable('products');

        if ($jsst_row->update(array('id' => $jsst_id, 'status' => $jsst_status))) {
            JSSTmessage::setMessage(__('Product','js-support-ticket').' '.__('status has been changed', 'js-support-ticket'), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(__('Product','js-support-ticket').' '.__('status has not been changed', 'js-support-ticket'), 'error');
        }
        return;
    }

    function getProductById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT product FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE id = ". esc_sql($jsst_id);
        $jsst_product = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_product;
    }

    function getAdminSearchFormDataProduct(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'products') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['title'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('title')));
        $jsst_search_array['pagesize'] = absint(JSSTrequest::getVar('pagesize'));
        $jsst_search_array['search_from_product'] = 1;
        return $jsst_search_array;
    }
}

?>
