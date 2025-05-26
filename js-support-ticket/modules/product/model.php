<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTproductModel {

    function getProducts() {
        // Filter
        $producttitle = jssupportticket::$_search['product']['product'];
        $pagesize = jssupportticket::$_search['product']['pagesize'];
        $inquery = '';

        if ($producttitle != null){
            $inquery .= " WHERE product.product LIKE '%".esc_sql($producttitle)."%'";
        }

        jssupportticket::$_data['filter']['title'] = $producttitle;
        jssupportticket::$_data['filter']['pagesize'] = $pagesize;

        // Pagination
        if($pagesize){
            JSSTpagination::setLimit($pagesize);
        }
        $query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ";
        $query .= $inquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        // Data
        $query = "SELECT product.*
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ";
        $query .= $inquery;
        $query .= " ORDER BY product.ordering ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getProductForCombobox() {
        $query = "SELECT id, product AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE status = 1 ";
        $query .= 'ORDER BY ordering ASC';
        $products = jssupportticket::$_db->get_results($query);

        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $products;
    }

    function getProductForForm($id) {
        $result=array();
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT product.*
				FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product
				WHERE product.id = " . esc_sql($id);
            $result = jssupportticket::$_db->get_row($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        jssupportticket::$_data[0]=$result;
        return;
    }

    function storeProduct($data) {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        if (!$this->validateProduct($data['product'], $data['id'])) {
            JSSTmessage::setMessage(esc_html(__('Product Title Already Exist', 'js-support-ticket')), 'error');
            return;
        }
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions

        if (!$data['id']) { //new
            $data['ordering'] = $this->getNextOrdering();
        }
        $row = JSSTincluder::getJSTable('products');
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
            JSSTmessage::setMessage(esc_html(__('Product has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Product has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function validateProduct($product, $id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT product FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE id = " . esc_sql($id);
            $result = jssupportticket::$_db->get_var($query);
            if ($result == $product) {
                return true;
            }
        }

        $query = 'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_products` WHERE product = "' . esc_sql($product) . '"';
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
        $query = "SELECT MAX(ordering) FROM `" . jssupportticket::$_db->prefix . "js_ticket_products`";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $result + 1;
    }

    function removeProduct($id) {
        if (!is_numeric($id))
            return false;
        $canremove = $this->canRemoveProduct($id);
        if ($canremove == 1) {
            $row = JSSTincluder::getJSTable('products');
            if ($row->delete($id)) {
                JSSTmessage::setMessage(esc_html(__('Product has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Product has not been deleted', 'js-support-ticket')), 'error');
            }
        } elseif ($canremove == 2)
            JSSTmessage::setMessage(esc_html(__('Product','js-support-ticket')).' '. esc_html(__('in use cannot deleted', 'js-support-ticket')), 'error');

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
        $query = "SELECT t.ordering,t.id,t2.ordering AS ordering2 FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS t,`" . jssupportticket::$_db->prefix . "js_ticket_products` AS t2 WHERE t.ordering $order t2.ordering AND t2.id = ".esc_sql($id)." ORDER BY t.ordering $direction LIMIT 1";
        $result = jssupportticket::$_db->get_row($query);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_products` SET ordering = " . esc_sql($result->ordering) . " WHERE id = " . esc_sql($id);
        jssupportticket::$_db->query($query);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_products` SET ordering = " . esc_sql($result->ordering2) . " WHERE id = " . esc_sql($result->id);
        jssupportticket::$_db->query($query);

        $row = JSSTincluder::getJSTable('products');
        if ($row->update(array('id' => $id, 'ordering' => $result->ordering)) && $row->update(array('id' => $result->id, 'ordering' => $result->ordering2))) {
            JSSTmessage::setMessage(esc_html(__('Product','js-support-ticket')).' '. esc_html(__('ordering has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Product','js-support-ticket')).' '. esc_html(__('ordering has not changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function canRemoveProduct($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT (
					(SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE productid = " . esc_sql($id) . ")
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

    function changeStatus($id) {
        if (!is_numeric($id))
            return false;

        $query = "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE id=" . esc_sql($id);
        $status = jssupportticket::$_db->get_var($query);

        $status = 1 - $status;

        $row = JSSTincluder::getJSTable('products');

        if ($row->update(array('id' => $id, 'status' => $status))) {
            JSSTmessage::setMessage(__('Product','js-support-ticket').' '.__('status has been changed', 'js-support-ticket'), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(__('Product','js-support-ticket').' '.__('status has not been changed', 'js-support-ticket'), 'error');
        }
        return;
    }

    function getProductById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT product FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` WHERE id = ". esc_sql($id);
        $product = jssupportticket::$_db->get_var($query);
        return $product;
    }

    function getAdminSearchFormDataProduct(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'products') ) {
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
