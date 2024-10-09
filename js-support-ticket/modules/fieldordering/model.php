<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTfieldorderingModel {

    function getFieldOrderingForList($fieldfor) {
        if(!is_numeric($fieldfor)){
            return false;
        }
	$formid = jssupportticket::$_data['formid'];
        if (isset($formid) && $formid != null) {
            $inquery = " AND multiformid = ".esc_sql($formid);
        }
    	else{
            $inquery = " AND multiformid = ".JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
    	}

        // Pagination
        /*
          $query = "SELECT COUNT(`id`) FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE published = 1 AND fieldfor = 1";
          $total = jssupportticket::$_db->get_var($query);
          jssupportticket::$_data[1] = JSSTpagination::getPagination($total);
         */

        // Data
//        $query = "SELECT * FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE published = 1 AND fieldfor = 1 ORDER BY ordering LIMIT ".JSSTpagination::getOffset().", ".JSSTpagination::getLimit();
        $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = ".esc_sql($fieldfor);
        $query .= $inquery." ORDER BY ordering ";

        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function changePublishStatus($id, $status) {
        if (!is_numeric($id))
            return false;
        if ($status == 'publish') {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET published = 1 WHERE id = " . esc_sql($id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as published', 'js-support-ticket')),'updated');
        } elseif ($status == 'unpublish') {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET published = 0 WHERE id = " . esc_sql($id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as unpublished', 'js-support-ticket')),'updated');
        }
        return;
    }

    function changeVisitorPublishStatus($id, $status) {
        if (!is_numeric($id))
            return false;
        if ($status == 'publish') {
            $query = "SELECT userfieldtype FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($id);
            $userfieldtype = jssupportticket::$_db->get_var($query);
            if($userfieldtype == 'admin_only'){
                JSSTmessage::setMessage(esc_html(__('Field cannot be mark as published', 'js-support-ticket')),'error');
            }else{
                $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET isvisitorpublished = 1 WHERE id = " . esc_sql($id) . " AND cannotunpublish = 0";
                jssupportticket::$_db->query($query);
                if (jssupportticket::$_db->last_error != null) {
                    JSSTincluder::getJSModel('systemerror')->addSystemError();
                }
                JSSTmessage::setMessage(esc_html(__('Field mark as published', 'js-support-ticket')),'updated');
            }
        } elseif ($status == 'unpublish') {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET isvisitorpublished = 0 WHERE id = " . esc_sql($id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as unpublished', 'js-support-ticket')),'updated');
        }
        return;
    }

    function changeRequiredStatus($id, $status) {
        if (!is_numeric($id))
            return false;

        // $query = "SELECT field FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE id =".esc_sql($id);
        // $child = jssupportticket::$_db->get_var($query);
        // $query = "SELECT count(id) FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE visible_field = '".esc_sql($child)."'";
        // $count = jssupportticket::$_db->get_var($query);
        // if ($count > 0) {
        //     JSSTmessage::setMessage(esc_html(__('Field cannot mark as required', 'js-support-ticket')), 'error');
        //     return;
        // }
        if ($status == 'required') {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET required = 1 WHERE id = " . esc_sql($id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as required', 'js-support-ticket')),'updated');
        } elseif ($status == 'unrequired') {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET required = 0 WHERE id = " . esc_sql($id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as not required', 'js-support-ticket')),'updated');
        }
        return;
    }

    function changeOrder($id, $action) {
        if (!is_numeric($id))
            return false;
        if ($action == 'down') {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f1, `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f2
                        SET f1.ordering = f1.ordering - 1 WHERE f1.ordering = f2.ordering + 1 AND f1.fieldfor = f2.fieldfor
                        AND f2.id = " . esc_sql($id);
            jssupportticket::$_db->query($query);
            $query = " UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET ordering = ordering + 1 WHERE id = " . esc_sql($id);
            jssupportticket::$_db->query($query);
            JSSTmessage::setMessage(esc_html(__('Field ordering down', 'js-support-ticket')),'updated');
        } elseif ($action == 'up') {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f1, `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f2 SET f1.ordering = f1.ordering + 1
                        WHERE f1.ordering = f2.ordering - 1 AND f1.fieldfor = f2.fieldfor AND f2.id = " . esc_sql($id);
            jssupportticket::$_db->query($query);
            $query = " UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET ordering = ordering - 1 WHERE id = " . esc_sql($id);
            jssupportticket::$_db->query($query);
            JSSTmessage::setMessage(esc_html(__('Field ordering up', 'js-support-ticket')),'updated');
        }
        return;
    }

    function getFieldsOrderingforForm($fieldfor,$formid='') {
        if (!is_numeric($fieldfor))
            return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' isvisitorpublished = 1 ';
        } else {
            $published = ' published = 1 ';
        }
	    if(!isset($formid) || $formid==''){
		    $formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
	    }
        $query = "SELECT  * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE ".$published." AND fieldfor =  " . esc_sql($fieldfor) ." AND multiformid =  " . esc_sql($formid) . " ORDER BY ordering ";
        jssupportticket::$_data['fieldordering'] = jssupportticket::$_db->get_results($query);
        return;
    }

    function storeUserField($data) {
        if (empty($data)) {
            return false;
        }
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        if ($data['isuserfield'] == 1) {
            // value to add as field ordering
            if ($data['id'] == '') { // only for new
                $query = "SELECT max(ordering) FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor=".esc_sql($data['fieldfor']);
                $var = jssupportticket::$_db->get_var($query);
                $data['ordering'] = $var + 1;
                if(isset($data['userfieldtype']) && ($data['userfieldtype'] == 'file' || $data['userfieldtype'] == 'termsandconditions' ) ){
                    $data['cannotsearch'] = 1;
                    $data['cannotshowonlisting'] = 1;
                }else{
                    $data['cannotshowonlisting'] = 0;
                    $data['cannotsearch'] = 0;
                }
                $query = "SELECT max(id) FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering ";
                $var = jssupportticket::$_db->get_var($query);
                $var = $var + 1;
                $fieldname = 'ufield_'.$var;
            }else{
                $fieldname = $data['field'];
            }
            if ($data['userfieldtype'] == 'termsandconditions') { // only for terms and conditions
                $data['required'] = 1;
            }

            $params = array();
            //code for depandetn field
            if (isset($data['userfieldtype']) && $data['userfieldtype'] == 'depandant_field') {
                if ($data['id'] != '') {
                    //to handle edit case of depandat field
                    $data['arraynames'] = $data['arraynames2'];
                }
                $flagvar = $this->updateParentField($data['parentfield'], $fieldname, $data['fieldfor']);
                if ($flagvar == false) {
                    JSSTmessage::setMessage(esc_html(__('Parent field has not been stored', 'js-support-ticket')), 'error');
                }
                if (!empty($data['arraynames'])) {
                    $valarrays = jssupportticketphplib::JSST_explode(',', $data['arraynames']);
                    $empty_flag = 0;
                    $key_flag = '';
                    foreach ($valarrays as $key => $value) {
                        if($key != $key_flag){
                            $key_flag = $key;
                            $empty_flag = 0;
                        }
                        $keyvalue = $value;
                        $value = jssupportticketphplib::JSST_str_replace(' ','__',$value);
                        $value = jssupportticketphplib::JSST_str_replace('.','___',$value);
                        // if ( isset($data[$value]) && $data[$value] != null) {
                            $keyvalue = jssupportticketphplib::JSST_htmlentities($keyvalue);
                            $params[$keyvalue] = array_filter($data[$value]);
                            $empty_flag = 1;
                        // }
                    }
                    if($empty_flag == 0){
                        JSSTmessage::setMessage(esc_html(__('Please Insert At least one value for every option', 'js-support-ticket')), 'error');
                        return 2 ;
                    }
                }
            }
            if (!empty($data['values'])) {
                foreach ($data['values'] as $key => $value) {
                    if ($value != null) {
                        $params[] = jssupportticketphplib::JSST_trim($value);
                    }
                }
            }
            
            if (isset($data['visibleParent']) && $data['visibleParent'] != '' && isset($data['visibleValue']) && $data['visibleValue'] != '' && isset($data['visibleCondition']) && $data['visibleCondition'] != ''){
                $visible['visibleParentField'] = $fieldname;
                $visible['visibleParent'] = $data['visibleParent'];
                $visible['visibleCondition'] = $data['visibleCondition'];
                $visible['visibleValue'] = $data['visibleValue'];
                $visible_array = array_map(array($this,'sanitize_custom_field'), $visible);
                $data['visibleparams'] = wp_json_encode($visible_array);
                //$data['required'] = 0;

                $query = "SELECT visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($data['visibleParent']);
                $old_fieldname = jssupportticket::$_db->get_var($query);
                $new_fieldname = $fieldname;
                if ($data['id'] != '') {
                    $query = "SELECT id,visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE visible_field  LIKE '%".esc_sql($fieldname)."%'";
                    $query_run = jssupportticket::$_db->get_row($query);
                    if (isset($query_run)) {
                        $query_fieldname = $query_run->visible_field;
                        $query_fieldname =  jssupportticketphplib::JSST_str_replace(','.$fieldname, '', $query_fieldname);
                        $query_fieldname =  jssupportticketphplib::JSST_str_replace($fieldname, '', $query_fieldname);
                        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($query_fieldname) . "' WHERE id = " . esc_sql($query_run->id);
                        jssupportticket::$_db->query($query);
                    }

                    $old_fieldname =  jssupportticketphplib::JSST_str_replace(','.$fieldname, '', $old_fieldname);
                    $old_fieldname =  jssupportticketphplib::JSST_str_replace($fieldname, '', $old_fieldname);
                }
                if (isset($old_fieldname) && $old_fieldname != '') {
                    $new_fieldname = $old_fieldname.','.$new_fieldname;
                }
                // update value
                $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($new_fieldname) . "' WHERE id = " . esc_sql($data['visibleParent']);
                jssupportticket::$_db->query($query);
                if (jssupportticket::$_db->last_error != null) {

                    JSSTincluder::getJSModel('systemerror')->addSystemError();
                }
                
            } else if($data['id'] != ''){
                $data['visibleparams'] = '';
                $query = "SELECT visibleparams FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($data['id']);
                $visibleparams = jssupportticket::$_db->get_var($query);
                if (isset($visibleparams)) {
                    $decodedData = json_decode($visibleparams);
                    $visibleParent = $decodedData->visibleParent;
                }else{
                    $visibleParent = -1;
                }
                $query = "SELECT visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($visibleParent);
                $old_fieldname = jssupportticket::$_db->get_var($query);
                $new_fieldname = $fieldname;
                $query = "SELECT id,visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE visible_field  LIKE '%".esc_sql($fieldname)."%'";
                $query_run = jssupportticket::$_db->get_row($query);
                if (isset($query_run)) {
                    $query_fieldname = $query_run->visible_field;
                    $query_fieldname =  jssupportticketphplib::JSST_str_replace(','.$fieldname, '', $query_fieldname);
                    $query_fieldname =  jssupportticketphplib::JSST_str_replace($fieldname, '', $query_fieldname);
                    $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($query_fieldname) . "' WHERE id = " . esc_sql($query_run->id);
                    jssupportticket::$_db->query($query);
                }
            }
            //if(!empty($params)){

            if (isset($data['userfieldtype']) && $data['userfieldtype'] == 'termsandconditions') { // to manage terms and condition field
                $params['termsandconditions_text'] = $data['termsandconditions_text'];
                $params['termsandconditions_linktype'] = $data['termsandconditions_linktype'];
                $params['termsandconditions_link'] = $data['termsandconditions_link'];
                $params['termsandconditions_page'] = $data['termsandconditions_page'];
            }

                // $params = wp_json_encode($params);
                $params_array = array_map(array($this,'sanitize_custom_field'), $params);
                $data['userfieldparams'] = wp_json_encode($params_array, JSON_UNESCAPED_UNICODE);

            //}
            // for admin_only
            if(isset($data['userfieldtype']) && ($data['userfieldtype'] == 'admin_only') ){
                $data['isvisitorpublished'] = 0;
                $data['search_visitor'] = 0;
            }
        }else{
            $fieldname = $data['field'];
        }

        $data['field'] = $fieldname;
        $data['section'] = 10;

        if (!empty($data['depandant_field']) && $data['depandant_field'] != null ) {

            $query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering where
            field = '". esc_sql($data['depandant_field'])."'";
            $child = jssupportticket::$_db->get_row($query);
            $parent = $data;
            $flagvar = $this->updateChildField($parent, $child);
            if ($flagvar == false) {
                JSSTmessage::setMessage(esc_html(__('Child fields has not been stored', 'js-support-ticket')), 'error');
            }
        }

        $row = JSSTincluder::getJSTable('fieldsordering');
        $data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }

        if ($error == 1) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            JSSTmessage::setMessage(esc_html(__('Field has not been stored', 'js-support-ticket')), 'error');
        } else {
            JSSTmessage::setMessage(esc_html(__('Field has been stored', 'js-support-ticket')), 'updated');
        }
        return 1;
    }

    function updateField($data) {
        if (empty($data)) {
            return false;
        }
        $inquery = '';
        $clasue = '';
        if(isset($data['fieldtitle']) && $data['fieldtitle'] != null){
            $inquery .= $clasue." fieldtitle = '". esc_sql($data['fieldtitle'])."'";
            $clasue = ' , ';
        }
        if(isset($data['published']) && $data['published'] != null){
            $inquery .= $clasue." published = ". esc_sql($data['published']);
            $clasue = ' , ';
        }
        if(isset($data['isvisitorpublished']) && $data['isvisitorpublished'] != null){
            $inquery .= $clasue." isvisitorpublished = ". esc_sql($data['isvisitorpublished']);
            $clasue = ' , ';
        }
        if(isset($data['required']) && $data['required'] != null){
            $inquery .= $clasue." required = ". esc_sql($data['required']);
            $clasue = ' , ';
        }
        if(isset($data['search_user']) && $data['search_user'] != null){
            $inquery .= $clasue." search_user = ". esc_sql($data['search_user']);
            $clasue = ' , ';
        }
        if(isset($data['search_visitor']) && $data['search_visitor'] != null){
            $inquery .= $clasue." search_visitor = ". esc_sql($data['search_visitor']);
            $clasue = ' , ';
        }
        if(isset($data['showonlisting']) && $data['showonlisting'] != null){
            $inquery .= $clasue." showonlisting = ". esc_sql($data['showonlisting']);
            $clasue = ' , ';
        }

        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET ".$inquery." WHERE id = " . esc_sql($data['id']) ;
        jssupportticket::$_db->query($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        JSSTmessage::setMessage(esc_html(__('Field has been updated', 'js-support-ticket')),'updated');

        return;
    }

    function updateParentField($parentfield, $field, $fieldfor) {
        if(!is_numeric($parentfield)) return false;

        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET depandant_field = '" . esc_sql($field) . "' WHERE id = " . esc_sql($parentfield)." AND fieldfor = ".esc_sql($fieldfor);
        jssupportticket::$_db->query($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return true;
    }

    function updateChildField($parent, $child){
        $userfieldparams = json_decode( $child->userfieldparams);

        $childNew =  new stdclass();
        foreach ($parent['values'] as $key => $value) {
            if ($userfieldparams->$key) {
               $childNew->$value[0] = $userfieldparams->$key[0];
            } else {
                $childNew->$value[0] = "";
            }
        }
        $childNew = wp_json_encode( $childNew );
        $child->userfieldparams = $childNew;
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET userfieldparams = '" . esc_sql($childNew) . "' WHERE id = " . esc_sql($child->id);
        jssupportticket::$_db->query($query);
        if (jssupportticket::$_db->last_error != null) {

            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return true;
    }

    function getFieldsForComboByFieldFor() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-fields-for-combo-by-fieldfor') ) {
            die( 'Security check Failed' );
        }
        $fieldfor = JSSTrequest::getVar('fieldfor');
        $parentfield = JSSTrequest::getVar('parentfield');
        $wherequery = '';
        if(isset($parentfield) && $parentfield !='' ){
            $query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($fieldfor)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo'OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($parentfield) . "' ";
            $parent = jssupportticket::$_db->get_var($query);
            $wherequery = ' OR id = '.esc_sql($parent);
        }
        $query = "SELECT fieldtitle AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = $fieldfor AND (userfieldtype = 'radio' OR userfieldtype = 'combo' OR userfieldtype = 'depandant_field') AND (depandant_field = '' ".esc_sql($wherequery)." ) ";
        $data = jssupportticket::$_db->get_results($query);
        if(isset($parentfield) && $parentfield !='' ){
            $query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = $fieldfor AND (userfieldtype = 'radio' OR userfieldtype = 'combo'OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($parentfield) . "' ";
            $parent = jssupportticket::$_db->get_var($query);
        }
        $jsFunction = 'getDataOfSelectedField();';
        $html = JSSTformfield::select('parentfield', $data, (isset($parent) && $parent !='') ? $parent : '', esc_html(__('Select', 'js-support-ticket')) .'&nbsp;'. esc_html(__('Parent Field', 'js-support-ticket')), array('onchange' => $jsFunction, 'class' => 'inputbox one js-form-select-field', 'data-validation' => 'required'));
        $html = jssupportticketphplib::JSST_htmlentities($html);
        $data = wp_json_encode($html);
        return $data;
    }

    function getFieldsForVisibleCombobox($fieldfor, $multiformid, $field='', $cid='') {
        $wherequery = '';
        if(isset($field) && $field !='' ){
            $query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = $fieldfor AND (userfieldtype = 'combo') AND visible_field = '" . esc_sql($field) . "' ";
            $parent = jssupportticket::$_db->get_var($query);
            if ($parent) {
                $wherequery = ' OR id = '.esc_sql($parent);
            }
        }
        $wherequeryforedit = '';
        if(isset($cid) && $cid !='' ){
            $wherequeryforedit = ' AND id != '.esc_sql($cid);
        }
        
        $query = "SELECT fieldtitle AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE (fieldfor = $fieldfor AND multiformid = '".esc_sql($multiformid)."' AND field = 'department' ".$wherequeryforedit.$wherequery.") OR (fieldfor = $fieldfor AND multiformid = '".esc_sql($multiformid)."' AND userfieldtype = 'combo' ".$wherequeryforedit.$wherequery.')';
        $data = jssupportticket::$_db->get_results($query);
        return $data;
    }

    function getChildForVisibleCombobox() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-child-for-visible-combobox') ) {
            die( 'Security check Failed' );
        }
        $perentid = JSSTrequest::getVar('val');
        if (!is_numeric($perentid)){
            return false;
        }

        $query = "SELECT isuserfield, field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE id = " . esc_sql($perentid);
        $fieldType = jssupportticket::$_db->get_row($query);
        if (isset($fieldType->isuserfield) && $fieldType->isuserfield == 1) {
            $query = "SELECT userfieldparams AS params FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE id = " . esc_sql($perentid);
            $options = jssupportticket::$_db->get_var($query);
            $options = json_decode($options);
            foreach ($options as $key => $option) {
                $fieldtypes[$key] = (object) array('id' => $option, 'text' => $option);
            }
        } else if ($fieldType->field == 'department') {
            $query = "SELECT departmentname AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_departments";
            $fieldtypes = jssupportticket::$_db->get_results($query);
        }
        $combobox = false;
        if(!empty($fieldtypes)){
            $combobox = JSSTformfield::select('visibleValue', $fieldtypes, isset(jssupportticket::$_data[0]['userfield']->required) ? jssupportticket::$_data[0]['userfield']->required : 0, '', array('class' => 'inputbox one js-form-select-field js-form-input-field-visible'));
        }
        return jssupportticketphplib::JSST_htmlentities($combobox);
    }

    function getSectionToFillValues() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-section-to-fill-values') ) {
            die( 'Security check Failed' );
        }
        $field = JSSTrequest::getVar('pfield');
        if(!is_numeric($field)){
            return false;
        }
        $query = "SELECT userfieldparams FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id=".esc_sql($field);
        $data = jssupportticket::$_db->get_var($query);
        $datas = json_decode($data);
        $html = '';
        $fieldsvar = '';
        $comma = '';
        foreach ($datas as $data) {
            if(is_array($data)){
                for ($i = 0; $i < count($data); $i++) {
                    $fieldsvar .= $comma . "$data[$i]";
                    $textvar = $data[$i];
                    $textvar = jssupportticketphplib::JSST_str_replace(' ','__',$textvar);
                    $textvar = jssupportticketphplib::JSST_str_replace('.','___',$textvar);
                    $divid = $textvar;
                    $textvar .='[]';
                    $html .= "<div class='jsst-user-dd-field-wrap'>";
                    $html .= "<div class='jsst-user-dd-field-title'>" . esc_html($data[$i]) . "</div>";
                    $html .= "<div class='jsst-user-dd-field-value combo-options-fields' id=" . esc_attr($divid) . ">
                                    <span class='input-field-wrapper'>
                                        " . wp_kses(JSSTformfield::text($textvar, '', array('class' => 'inputbox one user-field')), JSST_ALLOWED_TAGS) . "
                                        <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete.png' />
                                    </span>
                                    <input type='button' class='jsst-button-link button user-field-val-button' id='depandant-field-button' onClick='getNextField(\"" . esc_js($divid) . "\", this);'  value='Add More' />
                                </div>";
                    $html .= "</div>";
                    $comma = ',';
                }
            }else{
                $fieldsvar .= $comma . "$data";
                $textvar = $data;
                $textvar = jssupportticketphplib::JSST_str_replace(' ','__',$textvar);
                $textvar = jssupportticketphplib::JSST_str_replace('.','___',$textvar);
                $divid = $textvar;
                $textvar .='[]';
                $html .= "<div class='jsst-user-dd-field-wrap'>";
                $html .= "<div class='jsst-user-dd-field-title'>" . esc_html($data) . "</div>";
                $html .= "<div class='jsst-user-dd-field-value combo-options-fields' id=" . esc_attr($divid) . ">
                                <span class='input-field-wrapper'>
                                    " . wp_kses(JSSTformfield::text($textvar, '', array('class' => 'inputbox one user-field')), JSST_ALLOWED_TAGS) . "
                                    <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete.png' />
                                </span>
                                <input type='button' class='jsst-button-link button user-field-val-button' id='depandant-field-button' onClick='getNextField(\"" . esc_js($divid) . "\", this);'  value='Add More' />
                            </div>";
                $html .= "</div>";
                $comma = ',';
            }

        }
        $html .= " <input type='hidden' name='arraynames' value='" . esc_attr($fieldsvar) . "' />";
        $html = jssupportticketphplib::JSST_htmlentities($html);
        $html = wp_json_encode($html);
        return $html;
    }

    function getOptionsForFieldEdit() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-options-for-field-edit') ) {
            die( 'Security check Failed' );
        }
        $field = JSSTrequest::getVar('field');
		if(!is_numeric($field)) return false;
        $yesno = array(
            (object) array('id' => 1, 'text' => esc_html(__('Yes', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('No', 'js-support-ticket'))));

        $query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id=".esc_sql($field);
        $data = jssupportticket::$_db->get_row($query);

        $html = '<div class="userpopup-top">
                    <div class="userpopup-heading" >
                    ' . esc_html(__("Edit Field", 'js-support-ticket')) . '
                    </div>
                    <img id="popup_cross" class="userpopup-close" onClick="close_popup();" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/close-icon-white.png" alt="'. esc_html(__('Close','js-support-ticket')).'">
                </div>';
        $adminurl = admin_url("?page=fieldordering&task=savefeild&formid=".esc_attr($data->multiformid));
        $html .= '<form id="adminForm" class="popup-field-from" method="post" action="' . wp_nonce_url($adminurl ,"save-feild").'">';
        $html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Field Title', 'js-support-ticket')) . '<font class="required-notifier">*</font></div>
                    <div class="popup-field-obj">' . JSSTformfield::text('fieldtitle', isset($data->fieldtitle) ? $data->fieldtitle : 'text', '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        if ($data->cannotunpublish == 0 || $data->cannotshowonlisting == 0) {
            $html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('User Published', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('published', $yesno, isset($data->published) ? $data->published : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            if ($data->userfieldtype != 'admin_only') {
                $html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Visitor Published', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('isvisitorpublished', $yesno, isset($data->isvisitorpublished) ? $data->isvisitorpublished : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            }

            $html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Required', 'js-support-ticket')) . '</div>
                    <div class="popup-field-obj">' . JSSTformfield::select('required', $yesno, isset($data->required) ? $data->required : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        }
        if ($data->cannotsearch == 0) {
            $html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('User Search', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('search_user', $yesno, isset($data->search_user) ? $data->search_user : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            if ($data->userfieldtype != 'admin_only') {
                // visitor search is not in use
                /*$html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Visitor Search', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('search_visitor', $yesno, isset($data->search_visitor) ? $data->search_visitor : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';*/
            }        
        }
        if ($data->isuserfield == 1 || $data->cannotshowonlisting == 0) {
            $html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Show On Listing', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('showonlisting', $yesno, isset($data->showonlisting) ? $data->showonlisting : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
        }
        $html .= JSSTformfield::hidden('form_request', 'jssupportticket');
        $html .= JSSTformfield::hidden('id', $data->id);
        $html .= JSSTformfield::hidden('isuserfield', $data->isuserfield);
        $html .= JSSTformfield::hidden('fieldfor', $data->fieldfor);
        $html .='<div class="js-submit-container js-col-lg-10 js-col-md-10 js-col-md-offset-1 js-col-md-offset-1">
                    ' . JSSTformfield::submitbutton('save', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button'));
        if ($data->isuserfield == 1) {
            $html .= '<a class="button" style="margin-left:10px;" id="user-field-anchor" href="?page=fieldordering&jstlay=adduserfeild&jssupportticketid=' . esc_attr($data->id) .'&fieldfor='.esc_attr($data->fieldfor).'&formid='.esc_attr($data->multiformid).'"> ' . esc_html(__('Advanced', 'js-support-ticket')) . ' </a>';
        }

        $html .='</div>
            </form>';
        $html = jssupportticketphplib::JSST_htmlentities($html);
        return wp_json_encode($html);
    }

    function deleteUserField($id){
        if (is_numeric($id) == false)
           return false;
        $query = "SELECT field,field,fieldfor FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE id = ".esc_sql($id);
        $result = jssupportticket::$_db->get_row($query);
        if ($this->userFieldCanDelete($result) == true) {
            $row = JSSTincluder::getJSTable('fieldsordering');
            if (!$row->delete($id)) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                JSSTmessage::setMessage(esc_html(__('Field has not been deleted', 'js-support-ticket')),'error');
            } else {
                $query = "SELECT id,visible_field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE visible_field LIKE '%".esc_sql($result->field)."%'";
                $results = jssupportticket::$_db->get_results($query);
                foreach ($results as $value) {
                    $visible_field =  jssupportticketphplib::JSST_str_replace($result->field.',', '', $value->visible_field);
                    $visible_field =  jssupportticketphplib::JSST_str_replace(','.$result->field, '', $visible_field);
                    $visible_field =  jssupportticketphplib::JSST_str_replace($result->field, '', $visible_field);

                    $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '".esc_sql($visible_field)."' WHERE id = ".esc_sql($value->id);
                    jssupportticket::$_db->query($query);
                    if (jssupportticket::$_db->last_error != null) {

                        JSSTincluder::getJSModel('systemerror')->addSystemError();
                    }
                }
                $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE depandant_field = '".esc_sql($result->field)."'";
                $result = jssupportticket::$_db->get_var($query);
                if (isset($result)) {
                    $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET depandant_field = '' WHERE id = ".esc_sql($result);
                    jssupportticket::$_db->query($query);
                }
                JSSTmessage::setMessage(esc_html(__('Field has been deleted', 'js-support-ticket')),'updated');
            }
        }else{
            JSSTmessage::setMessage(esc_html(__('Field has not been deleted', 'js-support-ticket')),'error');
        }
        return false;
    }

    function enforceDeleteUserField($id){
        if (is_numeric($id) == false)
           return false;
        $query = "SELECT field,fieldfor FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE id = ".esc_sql($id);
        $result = jssupportticket::$_db->get_row($query);
        if ($this->userFieldCanDelete($result) == true) {
            $row = JSSTincluder::getJSTable('fieldsordering');
            $row->delete($id);
        }
        return false;
    }

    function userFieldCanDelete($field) {
        $fieldname = $field->field;
        $fieldfor = $field->fieldfor;

        //if($fieldfor == 1){//for deleting a ticket field
            $table = "tickets";
        //}
        $query = ' SELECT
                    ( SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_'.$table.'` WHERE
                        params LIKE \'%"' . esc_sql($fieldname) . '":%\'
                    )
                    AS total';
        $total = jssupportticket::$_db->get_var($query);
        if ($total > 0)
            return false;
        else
            return true;
    }

    function getUserfieldsfor($fieldfor,$multiformid='') {
        if (!is_numeric($fieldfor))
            return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' isvisitorpublished = 1 ';
        } else {
            $published = ' published = 1 ';
        }
        $inquery = '';
        if (isset($multiformid) && $multiformid != '') {
            $inquery = " AND multiformid = ".esc_sql($multiformid);
        }
        $query = "SELECT field,userfieldparams,userfieldtype,fieldtitle FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = " . esc_sql($fieldfor) . " AND isuserfield = 1 AND " . $published;
        $query .= $inquery." ORDER BY field ";
        $fields = jssupportticket::$_db->get_results($query);
        return $fields;
    }

    function getUserUnpublishFieldsfor($fieldfor) {
        if (!is_numeric($fieldfor))
            return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' isvisitorpublished = 0 ';
        } else {
            $published = ' published = 0 ';
        }
        $query = "SELECT field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = " . esc_sql($fieldfor) . " AND isuserfield = 1 AND " . $published;
        $fields = jssupportticket::$_db->get_results($query);
        return $fields;
    }

    function getFieldTitleByFieldfor($fieldfor) {
        if (!is_numeric($fieldfor))
            return false;

        $query = "SELECT field,fieldtitle FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = " . esc_sql($fieldfor) ;
        $fields = jssupportticket::$_db->get_results($query);
        $fielddata = array();
        foreach ($fields as $value) {
            $fielddata[$value->field] = $value->fieldtitle;
        }
        return $fielddata;
    }

    function getUserFieldbyId($id,$fieldfor) {
        if ($id) {
            if (is_numeric($id) == false)
                return false;
            $query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($id);
            jssupportticket::$_data[0]['userfield'] = jssupportticket::$_db->get_row($query);
            $params = jssupportticket::$_data[0]['userfield']->userfieldparams;
            $visibleparams = jssupportticket::$_data[0]['userfield']->visibleparams;
            jssupportticket::$_data[0]['userfieldparams'] = !empty($params) ? json_decode($params, True) : '';
            jssupportticket::$_data[0]['visibleparams'] = !empty($visibleparams) ? json_decode($visibleparams, True) : '';
            if (!empty($visibleparams)) {
                $pId = json_decode(jssupportticket::$_data[0]['userfield']->visibleparams);
                $query = "SELECT isuserfield FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($pId->visibleParent);
                $fieldType = jssupportticket::$_db->get_var($query);
                if (isset($fieldType) && $fieldType == 1) { 
                    $visibleparams = json_decode($visibleparams, True);
                    $query = "SELECT userfieldparams AS params FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE id = " . esc_sql($visibleparams['visibleParent']);
                    $options = jssupportticket::$_db->get_var($query);
                    $options = json_decode($options);
                    foreach ($options as $key => $option) {
                        $fieldtypes[$key] = (object) array('id' => $option, 'text' => $option);
                    }
                } else {
                    $query = "SELECT departmentname AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_departments";
                    $fieldtypes = jssupportticket::$_db->get_results($query);
                }
                jssupportticket::$_data[0]['visibleValue'] = $fieldtypes;
            }else{
                jssupportticket::$_data[0]['visibleValue'] = '';
            }
        }
        jssupportticket::$_data[0]['fieldfor'] = $fieldfor;
        return;
    }
    function getFieldsForListing($fieldfor) {
        if (is_numeric($fieldfor) == false)
            return false;
        $query = "SELECT field, showonlisting FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE  fieldfor =  " . esc_sql($fieldfor) ." ORDER BY ordering";
        $fields = jssupportticket::$_db->get_results($query);
        $fielddata = array();
        foreach ($fields AS $field) {
            $fielddata[$field->field] = $field->showonlisting;
        }
        return $fielddata;
    }

    function DataForDepandantField(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'data-for-depandant-field') ) {
            die( 'Security check Failed' );
        }
        $val = JSSTrequest::getVar('fvalue');
        $childfield = JSSTrequest::getVar('child');
        $query = "SELECT userfieldparams,fieldtitle,depandant_field,field FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE field = '".esc_sql($childfield)."'";
        $data = jssupportticket::$_db->get_row($query);
        $decoded_data = json_decode($data->userfieldparams);
        $comboOptions = array();
        $flag = 0;
        foreach ($decoded_data as $key => $value) {
            $key = html_entity_decode($key);
            if($key==$val){
               for ($i=0; $i <count($value) ; $i++) {
                   $comboOptions[] = (object)array('id' => $value[$i], 'text' => $value[$i]);
                   $flag = 1;
               }
            }
        }
        $jsFunction = '';
        if ($data->depandant_field != null) {
            $wpnonce = wp_create_nonce("data-for-depandant-field");
            $jsFunction = "getDataForDepandantField('".$wpnonce."','" . $data->field . "','" . $data->depandant_field . "',1);";
        }
        $textvar =  ($flag == 1) ? esc_html(__('Select', 'js-support-ticket')).' '.esc_html($data->fieldtitle) : '';
        $html = JSSTformfield::select($childfield, $comboOptions, '',$textvar, array('data-validation' => '','class' => 'inputbox one js-form-select-field js-ticket-custom-select', 'onchange' => $jsFunction));
        $html = jssupportticketphplib::JSST_htmlentities($html);
        $phtml = wp_json_encode($html);
        return $phtml;
    }

    function sanitize_custom_field($arg) {
        if (is_array($arg)) {
            // foreach($arg as $ikey){
            return array_map(array($this,'sanitize_custom_field'), $arg);
            // }
        }
        return jssupportticketphplib::JSST_htmlentities($arg, ENT_QUOTES, 'UTF-8');
    }

    function getDataForVisibleField($field) {
		$field = esc_sql($field);
        $field_array = jssupportticketphplib::JSST_str_replace(",", "','", $field);
        $query = "SELECT visibleparams FROM ". jssupportticket::$_db->prefix ."js_ticket_fieldsordering WHERE  field IN ('". $field_array ."')";
        $fields = jssupportticket::$_db->get_results($query);
        $data = array();
        foreach ($fields as $item) {
            $d = json_decode($item->visibleparams);
            $d->visibleParentField = Self::getChildForVisibleField($d->visibleParentField);
            $data[] = $d;
        }
        return $data;
    }

    static function getChildForVisibleField($field) {
		$field = esc_sql($field);
        $oldField = jssupportticketphplib::JSST_explode(',',$field);
        $newField = $oldField[sizeof($oldField) - 1];
        $query = "SELECT visible_field FROM ". jssupportticket::$_db->prefix ."js_ticket_fieldsordering WHERE  field = '". $newField ."'";
        $queryRun = jssupportticket::$_db->get_var($query);
        if (isset($queryRun) && $queryRun != '') {
            $data = jssupportticketphplib::JSST_explode(',',$queryRun);
            foreach ($data as $value) {
                $field = $field.','.$value;
                $field = Self::getChildForVisibleField($field);
            }
        }        
        return $field;
    }

}

?>
