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
            $query = "SELECT adminonly FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($id);
            $adminonly = jssupportticket::$_db->get_var($query);
            if(!empty($adminonly)){
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
        if(!is_numeric($formid)) return false;
        // admin only check
        $adminonly = '';
        if ($fieldfor == 1) {
            if( in_array('agent',jssupportticket::$_active_addons) ){
                $agent = JSSTincluder::getJSModel('agent')->isUserStaff();
            }else{
                $agent = false;
            }
            if(!is_admin() && !$agent){
                $adminonly = ' AND adminonly != 1 ';
            }
        }
        $query = "SELECT  * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE ".$published." AND fieldfor =  " . esc_sql($fieldfor);
        if ($fieldfor == 1) {
            $query .= " AND multiformid =  " . esc_sql($formid);
        }
        $query .=  esc_sql($adminonly) . " ORDER BY ordering ";
        jssupportticket::$_data['fieldordering'] = jssupportticket::$_db->get_results($query);
        return;
    }

    function checkIsFieldRequired($field,$formid='') {
        if(!isset($formid) || $formid==''){
            $formid = JSSTincluder::getJSmodel('ticket')->getDefaultMultiFormId();
        }
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' isvisitorpublished = 1 ';
        } else {
            $published = ' published = 1 ';
        }
        $query = "SELECT required FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE ".$published." AND fieldfor =  1 AND  field =  '".esc_sql($field)."' AND multiformid =  " . esc_sql($formid);
        $required = jssupportticket::$_db->get_var($query);
        return $required;
    }

    function storeUserField($data) {
        if (empty($data)) {
            return false;
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        if(!is_numeric($data['fieldfor'])) return false;
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
                $fieldname = !empty($data['field']) ? $data['field'] : '';
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
                if (!empty($data['arraynames'])) {
                    $valarrays = jssupportticketphplib::JSST_explode('_JSST_Unique_88a9e3_', $data['arraynames']);
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
                        // This check was previously commented out, but caused errors when child values were empty.
                        // Uncommented and handled properly by Hamza to avoid runtime issues with empty inputs.
                        if ( isset($data[$value]) && $data[$value] != null) {
                            $keyvalue = jssupportticketphplib::JSST_htmlentities($keyvalue);
                            $params[$keyvalue] = array_filter($data[$value]);
                            $empty_flag = 1;
                        }
                    }
                    if($empty_flag == 0){
                        JSSTmessage::setMessage(esc_html(__('Please Insert At least one value for every option', 'js-support-ticket')), 'error');
                        return 2 ;
                    }
                }
                $flagvar = $this->updateParentField($data['parentfield'], $fieldname, $data['fieldfor']);
                if ($flagvar == false) {
                    JSSTmessage::setMessage(esc_html(__('Parent field has not been stored', 'js-support-ticket')), 'error');
                }
            }
            if (!empty($data['values'])) {
                foreach ($data['values'] as $key => $value) {
                    if ($value != null) {
                        $value = jssupportticketphplib::JSST_str_replace('[','',$value);
                        $value = jssupportticketphplib::JSST_str_replace(']','',$value);
                        $params[] = jssupportticketphplib::JSST_trim($value);
                    }
                }
            }

            // 
            $visible = [];

            if (isset($data['visibleParent']) && is_array($data['visibleParent'])) {

                // new start

                if (!empty($data['id'])) {
                    $query = "SELECT id, visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE visible_field LIKE '%" . esc_sql($fieldname) . "%' AND multiformid = ".esc_sql($data['multiformid']);
                    $query_results = jssupportticket::$_db->get_results($query);
                    
                    if (!empty($query_results)) {
                        foreach ($query_results as $query_result) {
                            $query_fieldname = $query_result->visible_field;
                            $query_fieldname = jssupportticketphplib::JSST_str_replace(',' . $fieldname, '', $query_fieldname);
                            $query_fieldname = jssupportticketphplib::JSST_str_replace($fieldname, '', $query_fieldname);
                            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($query_fieldname) . "' WHERE id = " . esc_sql($query_result->id) . " AND multiformid = ".esc_sql($data['multiformid']);
                            jssupportticket::$_db->query($query);
                        }
                    }
                }

                // new end
                $visibleParents = $data['visibleParent'];
                $visibleValues = isset($data['visibleValue']) ? $data['visibleValue'] : [];
                $visibleConditions = isset($data['visibleCondition']) ? $data['visibleCondition'] : [];
                $visibleLogics = isset($data['visibleLogic']) ? $data['visibleLogic'] : [];

                $final = []; // Final grouped structure
                $currentAndGroup = []; // Current AND group

                foreach ($visibleParents as $index => $parentFieldId) {
                    if (
                        isset($visibleParents[$index]) && $visibleParents[$index] !== '' &&
                        isset($visibleValues[$index]) && $visibleValues[$index] !== '' &&
                        isset($visibleConditions[$index]) && $visibleConditions[$index] !== ''
                    ) {
                        $fieldname = $fieldname ?? ''; // Just in case
                        $logic = isset($visibleLogics[$index]) ? $visibleLogics[$index] : '';

                        // Build the current row
                        $row = [
                            'visibleParentField' => $fieldname,
                            'visibleParent' => $visibleParents[$index],
                            'visibleCondition' => $visibleConditions[$index],
                            'visibleValue' => $visibleValues[$index],
                            'visibleLogic' => $logic
                        ];

                        if ($logic === 'AND') {
                            // New AND group starts
                            if (!empty($currentAndGroup)) {
                                $final[] = $currentAndGroup;
                                $currentAndGroup = [];
                            }
                            $currentAndGroup[] = $row;
                        } else {
                            // Continue current AND group (OR rows)
                            $currentAndGroup[] = $row;
                        }

                        // --- your database update code ---
                        $query = "SELECT visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE field = '" . esc_sql($visibleParents[$index]) . "' AND multiformid = ".esc_sql($data['multiformid']);
                        $old_fieldname = jssupportticket::$_db->get_var($query);
                        $new_fieldname = $fieldname;

                        if (!empty($data['id'])) {
                            $old_fieldname = jssupportticketphplib::JSST_str_replace(',' . $fieldname, '', $old_fieldname);
                            $old_fieldname = jssupportticketphplib::JSST_str_replace($fieldname, '', $old_fieldname);
                        }

                        if (!empty($old_fieldname)) {
                            $new_fieldname = $old_fieldname . ',' . $new_fieldname;
                        }

                        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($new_fieldname) . "' WHERE field = '" . esc_sql($visibleParents[$index]) . "' AND multiformid = ".esc_sql($data['multiformid']);
                        jssupportticket::$_db->query($query);

                        if (jssupportticket::$_db->last_error != null) {
                            JSSTincluder::getJSModel('systemerror')->addSystemError();
                        }
                    }
                }
                // After finishing all rows
                if (!empty($currentAndGroup)) {
                    $final[] = $currentAndGroup;
                }

                // Now sanitize and save the final nested array
                $visible_array = array_map(array($this, 'sanitize_custom_field'), $final);
                $visibleparams = wp_json_encode(stripslashes_deep($visible_array));

            } else if (!empty($data['id'])) {
                if ($data['fieldfor'] != 3) {
                    $data['visibleparams'] = '';
                    // If editing old field
                    $query = "SELECT id, visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE visible_field LIKE '%" . esc_sql($fieldname) . "%' AND multiformid = ".esc_sql($data['multiformid']);
                    $query_results = jssupportticket::$_db->get_results($query);
                    if (!empty($query_results)) {
                        foreach ($query_results as $query_result) {
                            if (isset($query_result)) {
                                $query_fieldname = $query_result->visible_field;
                                $query_fieldname = jssupportticketphplib::JSST_str_replace(',' . $fieldname, '', $query_fieldname);
                                $query_fieldname = jssupportticketphplib::JSST_str_replace($fieldname, '', $query_fieldname);
                                $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($query_fieldname) . "' WHERE id = " . esc_sql($query_result->id);
                                jssupportticket::$_db->query($query);
                            }
                        }
                    }
                }
            }
            if (isset($data['userfieldtype']) && $data['userfieldtype'] == 'termsandconditions') { // to manage terms and condition field
                if ($data['termsandconditions_linktype'] == 1) {
                    $params['termsandconditions_link'] = $data['termsandconditions_link'];
                } else if ($data['termsandconditions_linktype'] == 2) {
                    $params['termsandconditions_page'] = $data['termsandconditions_page'];
                }
                $params['termsandconditions_text'] = $data['termsandconditions_text'];
                $params['termsandconditions_linktype'] = $data['termsandconditions_linktype'];
            }

                // $params = wp_json_encode($params);
                $params_array = array_map(array($this,'sanitize_custom_field'), $params);
                $userfieldparams = wp_json_encode(stripslashes_deep($params_array));

            //}
            // for default value
            $data['defaultvalue'] = '';
            if($data['userfieldtype'] == "combo" || $data['userfieldtype'] == "radio" || $data['userfieldtype'] == "multiple" || $data['userfieldtype'] == "checkbox" || $data['userfieldtype'] == "depandant_field") {
                $data['defaultvalue'] = !empty($data['defaultvalue_select']) ? $data['defaultvalue_select'] : '';
            } else {
                $data['defaultvalue'] = !empty($data['defaultvalue_input']) ? $data['defaultvalue_input'] : '';
            }
        }else{
            $fieldname = $data['field'];
            $data['userfieldtype'] = '';
            $data['defaultvalue'] = !empty($data['defaultvalue_input']) ? $data['defaultvalue_input'] : '';
            // get data for system fields of type terms ans conditions
            if (in_array($data['field'], ['termsandconditions1', 'termsandconditions2', 'termsandconditions3'])) { // to manage terms and condition field
                if ($data['termsandconditions_linktype'] == 1) {
                    $params['termsandconditions_link'] = $data['termsandconditions_link'];
                } else if ($data['termsandconditions_linktype'] == 2) {
                    $params['termsandconditions_page'] = $data['termsandconditions_page'];
                }
                $params['termsandconditions_text'] = $data['termsandconditions_text'];
                $params['termsandconditions_linktype'] = $data['termsandconditions_linktype'];
                $params_array = array_map(array($this,'sanitize_custom_field'), $params);
                $userfieldparams = wp_json_encode(stripslashes_deep($params_array));
            }
        }

        // for adminonly
        if(!empty($data['adminonly'])){
            $data['isvisitorpublished'] = 0;
            $data['search_visitor'] = 0;
            $data['search_user'] = 0;
        }

        $data['field'] = $fieldname;
        $data['section'] = 10;

        /*if (!empty($data['depandant_field']) && $data['depandant_field'] != null ) {

            $query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering where
            field = '". esc_sql($data['depandant_field'])."'";
            $child = jssupportticket::$_db->get_row($query);
            $parent = $data;
            $flagvar = $this->updateChildField($parent, $child);
            if ($flagvar == false) {
                JSSTmessage::setMessage(esc_html(__('Child fields has not been stored', 'js-support-ticket')), 'error');
            }
        }*/

        $row = JSSTincluder::getJSTable('fieldsordering');
        $data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        if (!empty($userfieldparams)) {
            $data['userfieldparams'] = $userfieldparams;
        }
        if (!empty($visibleparams)) {
            $data['visibleparams'] = $visibleparams;
        }
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
            // update the dependent fields data if exist
            if (!empty($data['depandant_field']) && $data['depandant_field'] != null ) {

                $query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering where
                field = '". esc_sql($data['depandant_field'])."'";
                $child = jssupportticket::$_db->get_row($query);
                
                /* get parent saved data */
                $query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering where
                id = '". esc_sql($data['id'])."'";
                $parent = jssupportticket::$_db->get_row($query);
                /* get parent saved data */
                
                // $parent = $data;
                $flagvar = $this->updateChildField($parent, $child);
                if ($flagvar == false) {
                    JSSTmessage::setMessage(esc_html(__('Child fields has not been stored', 'js-support-ticket')), 'error');
                }
            }
        }
        return 1;
    }

    function updateField($data) {
        if (empty($data)) {
            return false;
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
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
        if(isset($data['placeholder']) && $data['placeholder'] != null){
            $inquery .= $clasue." placeholder = '". esc_sql($data['placeholder']) ."'";
            $clasue = ' , ';
        }
        if(isset($data['description']) && $data['description'] != null){
            $inquery .= $clasue." description = '". esc_sql($data['description']) . "'";
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
        if(isset($data['search_admin']) && $data['search_admin'] != null){
            $inquery .= $clasue." search_admin = ". esc_sql($data['search_admin']);
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
        if(!is_numeric($fieldfor)) return false;
        if(!is_numeric($parentfield)) return false;
        if(empty($field)) return false;

        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET depandant_field = '" . esc_sql($field) . "' WHERE id = " . esc_sql($parentfield)." AND fieldfor = ".esc_sql($fieldfor);
        jssupportticket::$_db->query($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return true;
    }

    function updateChildField($parent, $child){
        if(!is_numeric($child->id)) return false;
        $childfieldparams = json_decode( $child->userfieldparams,TRUE);
        $parentfieldparams = json_decode( $parent->userfieldparams,TRUE);

        // $parentfieldparams = stripslashes_deep($parentfieldparams);
        // $childfieldparams = stripslashes_deep($childfieldparams);

        $childNew = [];

        foreach ($parentfieldparams as $parentKey => $parentValue) {
            $childKeys = is_array($parentValue) ? $parentValue : [$parentValue];

            foreach ($childKeys as $childKey) {
                if (isset($childfieldparams[$childKey])) {
                    $childNew[$childKey] = $childfieldparams[$childKey];
                } else {
                    $childNew[$childKey] = '';
                }
            }
        }
        //$childNew = wp_json_encode( stripslashes_deep($childNew) );
        $childNew = wp_json_encode( $childNew  );
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
        $formid = JSSTrequest::getVar('formid');
        $fieldfor = JSSTrequest::getVar('fieldfor');
        $parentfield = JSSTrequest::getVar('parentfield');
        if(!is_numeric($fieldfor)) return false;
        $wherequery = '';
        if(isset($parentfield) && $parentfield !='' ){
            $query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($fieldfor)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo'OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($parentfield) . "' ";
            $parent = jssupportticket::$_db->get_var($query);
            $wherequery = ' OR id = '.esc_sql($parent);
        }
        $query = "SELECT fieldtitle AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($fieldfor)." AND multiformid = ".esc_sql($formid)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo' OR userfieldtype = 'depandant_field') AND (depandant_field = '' ".esc_sql($wherequery)." ) ";
        $data = jssupportticket::$_db->get_results($query);
        if(isset($parentfield) && $parentfield !='' ){
            $query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($fieldfor)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo'OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($parentfield) . "' ";
            $parent = jssupportticket::$_db->get_var($query);
        }
        $nonce = wp_create_nonce("get-section-to-fill-values-".$fieldfor);
        $jsFunction = 'getDataOfSelectedField("'.$nonce.'");';
        $html = JSSTformfield::select('parentfield', $data, (isset($parent) && $parent !='') ? $parent : '', esc_html(__('Select', 'js-support-ticket')) .'&nbsp;'. esc_html(__('Parent Field', 'js-support-ticket')), array('onchange' => $jsFunction, 'class' => 'inputbox one js-form-select-field', 'data-validation' => 'required'));
        $html = jssupportticketphplib::JSST_htmlentities($html);
        $data = wp_json_encode($html);
        return $data;
    }

    function getFieldsForVisibleCombobox($fieldfor, $multiformid, $field='', $cid='') {
        if(!is_numeric($fieldfor)) return false;
        $wherequery = '';
        if(isset($field) && $field !='' ){
            $query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($fieldfor)." AND (userfieldtype IN ( 'combo', 'text', 'checkbox', 'date', 'email', 'radio', 'multiple') ) AND visible_field = '" . esc_sql($field) . "' ";
            $parent = jssupportticket::$_db->get_var($query);
            if ($parent) {
                $wherequery = ' OR id = '.esc_sql($parent);
            }
        }
        $wherequeryforedit = '';
        if(isset($cid) && $cid !='' ){
            $wherequeryforedit = ' AND id != '.esc_sql($cid);
        }
        
        // Base fields always included
        $builtin_fields = ['email', 'fullname', 'phone', 'subject', 'department', 'priority'];

        // Conditionally add 'helptopic' if the addon is active
        /*if (in_array('helptopic', jssupportticket::$_active_addons)) {
            $builtin_fields[] = 'helptopic';
        }*/

        // Convert to comma-separated string for SQL IN clause
        $builtin_fields_sql = "'" . implode("','", array_map('esc_sql', $builtin_fields)) . "'";

        // Build the final SQL query
        $query = "
        SELECT fieldtitle AS text, field AS id 
            FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering 
            WHERE (
                fieldfor = " . esc_sql($fieldfor) . " 
                AND multiformid = '" . esc_sql($multiformid) . "' 
                AND field IN ($builtin_fields_sql) 
                $wherequeryforedit $wherequery
            ) 
            OR (
                fieldfor = " . esc_sql($fieldfor) . " 
                AND multiformid = '" . esc_sql($multiformid) . "' 
                AND userfieldtype IN ('combo', 'text', 'checkbox', 'date', 'email', 'radio', 'multiple') 
                $wherequeryforedit $wherequery
            )";
        $data = jssupportticket::$_db->get_results($query);
        return $data;
    }

    function getChildForVisibleCombobox($perentid = null , $default = null) {
        $isAjaxCall = JSSTrequest::getVar('isAjaxCall');
        if ($isAjaxCall == 1) {
            $nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $nonce, 'get-child-for-visible-combobox') ) {
                die( 'Security check Failed' );
            }
        }
        if ($perentid == null) {
            $perentid = JSSTrequest::getVar('val');
        }
        if (empty($perentid)){
            return false;
        }

        $query = "SELECT isuserfield, userfieldtype, field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '" . esc_sql($perentid)."'";
        $fieldType = jssupportticket::$_db->get_row($query);
        $showComboBox = false;
        if (isset($fieldType->isuserfield) && $fieldType->isuserfield == 1) {
            $query = "SELECT userfieldparams AS params FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '" . esc_sql($perentid) . "'";
            $options = jssupportticket::$_db->get_var($query);
            $options = json_decode($options);
            foreach ($options as $key => $option) {
                $fieldtypes[$key] = (object) array('id' => $option, 'text' => $option);
            }
            if (in_array($fieldType->userfieldtype, ['combo', 'checkbox', 'radio', 'multiple'])) {
                $showComboBox = true;
            }
        } else if ($fieldType->field == 'department') {
            $showComboBox = true;
            $query = "SELECT departmentname AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_departments";
            $fieldtypes = jssupportticket::$_db->get_results($query);
        } else if ($fieldType->field == 'helptopic') {
            $showComboBox = true;
            $query = "SELECT id, topic AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE status = 1";
            $query.= "  ORDER BY ordering ASC";
            $fieldtypes = jssupportticket::$_db->get_results($query);
        } else if ($fieldType->field == 'priority') {
            $showComboBox = true;
            $query = "SELECT id, priority AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`";
            $query .= 'ORDER BY ordering ASC';
            $fieldtypes = jssupportticket::$_db->get_results($query);
        }
        //
        $combobox = false;
        if($showComboBox){
            $combobox = JSSTformfield::select('visibleValue[]', $fieldtypes, isset($default) ? $default : '', '', array('class' => 'inputbox one js-form-select-field js-form-input-field-visible'));
        } else {
            $combobox = JSSTformfield::text('visibleValue[]', isset($default) ? $default : '', array('class' => 'inputbox one js-form-input-field js-form-input-field-visible'));
        }
        return jssupportticketphplib::JSST_htmlentities($combobox);
    }

    function getConditionsForVisibleCombobox($perentid = null , $default = null) {
        $isAjaxCall = JSSTrequest::getVar('isAjaxCall');
        if ($isAjaxCall == 1) {
            $nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $nonce, 'get-conditions-for-visible-combobox') ) {
                die( 'Security check Failed' );
            }
        }
        if ($perentid == null) {
            $perentid = JSSTrequest::getVar('val');
        }
        if (empty($perentid)){
            return false;
        }
        $Conditions = array(
        (object) array('id' => 1, 'text' => esc_html(__('Equal', 'js-support-ticket'))),
        (object) array('id' => 0, 'text' => esc_html(__('Not Equal', 'js-support-ticket'))));

        $query = "SELECT isuserfield, userfieldtype, field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '" . esc_sql($perentid) . "'";
        $fieldType = jssupportticket::$_db->get_row($query);
        if (empty($fieldType->isuserfield)) {
            if ($fieldType->field == 'email' || $fieldType->field == 'fullname' || $fieldType->field == 'phone' || $fieldType->field == 'subject' || $fieldType->field == 'issuesummary') {
                $Conditions = array(
                (object) array('id' => 2, 'text' => esc_html(__('Contain', 'js-support-ticket'))),
                (object) array('id' => 3, 'text' => esc_html(__('Not Contain', 'js-support-ticket'))));
            }
        } else {
            if (!in_array($fieldType->userfieldtype, ['combo', 'checkbox', 'radio', 'multiple'])) {
                $Conditions = array(
                (object) array('id' => 2, 'text' => esc_html(__('Contain', 'js-support-ticket'))),
                (object) array('id' => 3, 'text' => esc_html(__('Not Contain', 'js-support-ticket'))));
            }
        }
        $combobox = false;
        if(!empty($Conditions)){
            $combobox = JSSTformfield::select('visibleCondition[]', $Conditions, isset($default) ? $default : '', '', array('class' => 'inputbox one js-form-select-field js-form-input-field-visible'));
        }
        return jssupportticketphplib::JSST_htmlentities($combobox);
    }

    function getSectionToFillValues() {
        $fieldfor = JSSTrequest::getVar('fieldfor');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-section-to-fill-values-'.$fieldfor) ) {
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
                    $js_value = esc_js($divid);
                    $textvar .='[]';
                    $html .= "<div class='jsst-user-dd-field-wrap'>";
                    $html .= "<div class='jsst-user-dd-field-title'>" . esc_html($data[$i]) . "</div>";
                    $html .= "<div class='jsst-user-dd-field-value combo-options-fields' id=" . esc_attr($divid) . ">
                                    <span class='input-field-wrapper'>
                                        " . wp_kses(JSSTformfield::text($textvar, '', array('class' => 'inputbox one user-field')), JSST_ALLOWED_TAGS) . "
                                        <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete.png' />
                                    </span>
                                    <input type='button' class='jsst-button-link button user-field-val-button' id='depandant-field-button' onClick='getNextField(\"" . $js_value . "\", this);'  value='Add More' />
                                </div>";
                    $html .= "</div>";
                    $comma = '_JSST_Unique_88a9e3_';
                }
            }else{
                $fieldsvar .= $comma . "$data";
                $textvar = $data;
                $textvar = jssupportticketphplib::JSST_str_replace(' ','__',$textvar);
                $textvar = jssupportticketphplib::JSST_str_replace('.','___',$textvar);
                $divid = $textvar;
                $js_value = esc_js($divid);
                $textvar .='[]';
                $html .= "<div class='jsst-user-dd-field-wrap'>";
                $html .= "<div class='jsst-user-dd-field-title'>" . esc_html($data) . "</div>";
                $html .= "<div class='jsst-user-dd-field-value combo-options-fields' id=" . esc_attr($divid) . ">
                                <span class='input-field-wrapper'>
                                    " . wp_kses(JSSTformfield::text($textvar, '', array('class' => 'inputbox one user-field')), JSST_ALLOWED_TAGS) . "
                                    <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete.png' />
                                </span>
                                <input type='button' class='jsst-button-link button user-field-val-button' id='depandant-field-button' onClick=\"getNextField('" . $js_value . "', this);\"  value='Add More' />
                            </div>";
                $html .= "</div>";
                $comma = '_JSST_Unique_88a9e3_';
            }

        }
        $html .= " <input type='hidden' name='arraynames' value=\"" . esc_attr($fieldsvar) . "\" />";
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
        $nonce_id = isset($data->id) ? $data->id : '';
        $adminurl = admin_url("?page=fieldordering&task=savefeild&formid=".esc_attr($data->multiformid));
        $html .= '<form id="adminForm" class="popup-field-from" method="post" action="' . wp_nonce_url($adminurl ,"save-feild-".$nonce_id).'">';
        $html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Field Title', 'js-support-ticket')) . '<font class="required-notifier">*</font></div>
                    <div class="popup-field-obj">' . JSSTformfield::text('fieldtitle', isset($data->fieldtitle) ? $data->fieldtitle : 'text', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        if ($data->cannotunpublish == 0 || $data->cannotshowonlisting == 0) {
            $html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('User Published', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('published', $yesno, isset($data->published) ? $data->published : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            $html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Visitor Published', 'js-support-ticket')) . '</div>
                    <div class="popup-field-obj">' . JSSTformfield::select('isvisitorpublished', $yesno, isset($data->isvisitorpublished) ? $data->isvisitorpublished : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        }

        $html .= '<div class="popup-field-wrapper">
                <div class="popup-field-title">' . esc_html(__('Place Holder', 'js-support-ticket')) . '</div>
                <div class="popup-field-obj">' . JSSTformfield::text('placeholder', isset($data->placeholder) ? $data->placeholder : '', array('class' => 'inputbox one','maxlength'=>225)) . '</div>
            </div>';

        $html .= '<div class="popup-field-wrapper">
                <div class="popup-field-title">' . esc_html(__('Description', 'js-support-ticket')) . '</div>
                <div class="popup-field-obj">' . JSSTformfield::text('description', isset($data->description) ? $data->description : '', array('class' => 'inputbox one','maxlength'=>225)) . '</div>
            </div>';
        if ($data->cannotunpublish == 0 || $data->cannotshowonlisting == 0) {

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
            $html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Admin Search', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('search_admin', $yesno, isset($data->search_admin) ? $data->search_admin : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            if (!empty($data->adminonly)) {
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

    function getFieldTitleByFieldfor($fieldfor,$formid='') {
        if (!is_numeric($fieldfor))
            return false;
        if (is_admin()) {
            $published = '';
        } else if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' AND isvisitorpublished = 1 ';
        } else {
            $published = ' AND published = 1 ';
        }
        $inquery = '';
        if (isset($formid) && $formid == 0) {
            $defaultformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $inquery = " AND multiformid = ".esc_sql($defaultformid);
        } elseif (isset($formid) && $formid != '') {
            $inquery = " AND multiformid = ".esc_sql($formid);
        }
        $query = "SELECT field,fieldtitle FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = " . esc_sql($fieldfor) . $published;
        $query .= $inquery;
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
        }
        jssupportticket::$_data[0]['fieldfor'] = $fieldfor;
        return;
    }
    function getFieldsForListing($fieldfor, $formid='') {
        if (is_numeric($fieldfor) == false)
            return false;
        if (is_admin()) {
            $published = '';
        } else if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' AND isvisitorpublished = 1 ';
        } else {
            $published = ' AND published = 1 ';
        }
        $inquery = '';
        if (isset($formid) && $formid == 0) {
            $defaultformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $inquery = " AND multiformid = ".esc_sql($defaultformid);
        } elseif (isset($formid) && $formid != '') {
            $inquery = " AND multiformid = ".esc_sql($formid);
        }
        $query = "SELECT field, showonlisting FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE showonlisting = 1 AND fieldfor =  " . esc_sql($fieldfor) . esc_sql($published);
        $query .= $inquery;
        $query .= " ORDER BY ordering";
        $fields = jssupportticket::$_db->get_results($query);
        $fielddata = array();
        foreach ($fields AS $field) {
            $fielddata[$field->field] = $field->showonlisting;
        }
        return $fielddata;
    }
    function getAdminSystemFieldsForSearch() {
        
        if(in_array('multiform', jssupportticket::$_active_addons)){
            $query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f LEFT JOIN " . jssupportticket::$_db->prefix . "js_ticket_multiform m ON f.multiformid = m.id WHERE f.search_admin = 1 AND f.published = 1 AND (f.isuserfield IS NULL OR f.isuserfield != 1) ";
            $query .= " ORDER BY m.is_default DESC, f.ordering ASC";
        } else {
            $formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $formFilter = " AND f.multiformid = " . esc_sql($formid);
            $query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f WHERE f.search_admin = 1 AND f.published = 1 AND (f.isuserfield IS NULL OR f.isuserfield != 1) ";
            $query .= $formFilter;
            $query .= " ORDER BY f.ordering ASC";
        }
        $results = jssupportticket::$_db->get_results($query);

        $fielddata = array();
        foreach ($results as $row) {
            // Only set the field once to prioritize the first (default) occurrence
            if (!isset($fielddata[$row->field])) {
                $fielddata[$row->field] = $row->fieldtitle;
            }
        }
        return $fielddata;
    }
    function getUserSystemFieldsForSearch() {
                // Determine published column based on user type
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' f.isvisitorpublished = 1 ';
        } else {
            $published = ' f.published = 1 ';
        }

        if(in_array('multiform', jssupportticket::$_active_addons)){
            // Query with LEFT JOIN and ordering to prioritize default form
            $query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f LEFT JOIN " . jssupportticket::$_db->prefix . "js_ticket_multiform m ON f.multiformid = m.id WHERE f.search_user = 1 AND ".$published." AND (f.isuserfield IS NULL OR f.isuserfield != 1)";
            $query .= " ORDER BY m.is_default DESC, f.ordering ASC";
        } else {
            $formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $formFilter = " AND f.multiformid = " . esc_sql($formid);
            // Query with LEFT JOIN and ordering to prioritize default form
            $query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f WHERE f.search_user = 1 AND ".$published." AND (f.isuserfield IS NULL OR f.isuserfield != 1)";
            $query .= $formFilter;
            $query .= " ORDER BY f.ordering ASC";
        }

        $results = jssupportticket::$_db->get_results($query);

        $fielddata = array();
        foreach ($results as $row) {
            // Only keep the first (preferred) version of each field
            if (!isset($fielddata[$row->field])) {
                $fielddata[$row->field] = $row->fieldtitle;
            }
        }

        return $fielddata;
    }
    function getPublishedFieldsForTicketDetail($formid='') {
        if(!isset($formid) || $formid==''){
            $formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        if(!is_numeric($formid)) return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' isvisitorpublished = 1 ';
        } else {
            $published = ' published = 1 ';
        }
        $query = "SELECT field, showonlisting FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE ".$published." AND fieldfor = 1 AND multiformid =  " . esc_sql($formid) ;
        $fields = jssupportticket::$_db->get_results($query);
        $fielddata = array();
        foreach ($fields AS $field) {
            $fielddata[$field->field] = $field->showonlisting;
        }
        return $fielddata;
    }

    function DataForDepandantField(){
        $childfield = JSSTrequest::getVar('child');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'data-for-depandant-field-'.$childfield) ) {
            die( 'Security check Failed' );
        }
        $val = JSSTrequest::getVar('fvalue');
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
            $wpnonce = wp_create_nonce("data-for-depandant-field-".$data->depandant_field);
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

        $query = "SELECT field, visibleparams FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE field IN ('" . $field_array . "')";
        $fields = jssupportticket::$_db->get_results($query);

        $data = array();

        if (!empty($fields)) {
            foreach ($fields as $item) {
                $fieldname = $item->field;

                $decoded = json_decode($item->visibleparams);

                // Initialize array for this field if not set
                if (!isset($data[$fieldname])) {
                    $data[$fieldname] = array();
                }


                if (is_array($decoded)) {
                    // New system: multiple AND/OR groups
                    foreach ($decoded as $group) {
                        if (isset($group) && is_array($group)) {
                            foreach ($group as $d) {
                                $d->visibleParentField = self::getChildForVisibleField($d->visibleParentField);
                            }
                            $data[$fieldname][] = $group; // Save group
                        } else {
                            // fallback
                            $group->visibleParentField = self::getChildForVisibleField($group->visibleParentField);
                            $data[$fieldname][] = $group;
                        }
                    }
                } elseif (is_object($decoded)) {
                    // Old system: simple condition
                    $decoded->visibleParentField = self::getChildForVisibleField($decoded->visibleParentField);
                    $data[$fieldname][] = $decoded;
                }
            }
        }

        return $data;
    }

    function getDataForVisibleField01($field) {
        $field = esc_sql($field);
        $field_array = jssupportticketphplib::JSST_str_replace(",", "','", $field);

        $query = "SELECT field, visibleparams FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE field IN ('" . $field_array . "')";
        $fields = jssupportticket::$_db->get_results($query);

        $data = array();

        if (!empty($fields)) {
            foreach ($fields as $item) {
                $fieldname = $item->field;
                $decoded = json_decode($item->visibleparams);

                // Initialize array for this field if not set
                if (!isset($data[$fieldname])) {
                    $data[$fieldname] = array();
                }

                if (is_array($decoded)) {
                    // New case: multiple conditions
                    foreach ($decoded as $d) {
                        $d->visibleParentField = self::getChildForVisibleField($d->visibleParentField);
                        $data[$fieldname][] = $d;
                    }
                } elseif (is_object($decoded)) {
                    // Old case: single condition
                    $decoded->visibleParentField = self::getChildForVisibleField($decoded->visibleParentField);
                    $data[$fieldname][] = $decoded;
                }
            }
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

    function getHtmlForORRow() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-html-for-or-row') ) {
            die( 'Security check Failed' );
        }
        
        $orid = JSSTrequest::getVar("nextorid");
        $fieldfor = JSSTrequest::getVar("fieldfor");
        $formid = JSSTrequest::getVar("formid");
        $field = JSSTrequest::getVar("field");
        $id = JSSTrequest::getVar("id");
        $equalnotequal = array(
            (object) array('id' => 1, 'text' => esc_html(__('Equal', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('Not Equal', 'js-support-ticket'))));
        $html = "
        <div id='js_or_row_". $orid ."'>
            <div class='js-form-visible-subheading'>
                ". esc_html(__('OR', 'js-support-ticket')) ."
            </div>
            <div class='js-form-value'>
                ". wp_kses(JSSTformfield::hidden('visibleLogic[]', 'OR'), JSST_ALLOWED_TAGS) ."
                ". wp_kses(JSSTformfield::select('visibleParent[]', JSSTincluder::getJSModel('fieldordering')->getFieldsForVisibleCombobox($fieldfor, $formid,$field,$id), '', esc_html(__('Select Parent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field js-form-input-field-visible', 'onchange' => 'getChildForVisibleCombobox(this.value, '.$orid.');getConditionsForVisibleCombobox(this.value, '.$orid.');')), JSST_ALLOWED_TAGS) ."
                <span class='visibleValueWrp'>
                    ". wp_kses(JSSTformfield::select('visibleValue[]', '', '', esc_html(esc_html(__('Select Child', 'js-support-ticket'))), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                </span>
                <span class='visibleConditionWrp'>
                    ". wp_kses(JSSTformfield::select('visibleCondition[]', $equalnotequal, '', esc_html(__('Select Condition', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                </span>
                <div class='js-visible-conditions-body-row'>
                    <div class='js-visible-conditions-body-value'>
                        <span onclick=\"deleteOrRow('js_or_row_". $orid ."')\" class='js-visible-conditions-delbtn'>
                            <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete-2.png' />
                        </span>
                    </div>
                </div>
            </div>
        </div>
        ";
        $html = jssupportticketphplib::JSST_htmlentities($html);
        return wp_json_encode($html);
    }

    function getHtmlForANDRow() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-html-for-and-row') ) {
            die( 'Security check Failed' );
        }
        
        $andid = JSSTrequest::getVar("nextandid");
        $orid = JSSTrequest::getVar("nextorid");
        $fieldfor = JSSTrequest::getVar("fieldfor");
        $formid = JSSTrequest::getVar("formid");
        $field = JSSTrequest::getVar("field");
        $id = JSSTrequest::getVar("id");
        $equalnotequal = array(
            (object) array('id' => 1, 'text' => esc_html(__('Equal', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('Not Equal', 'js-support-ticket'))));

        $html = "
        <div class='js-form-visible-andwrp' id='js_and_row_". $andid ."'>
            <div class='js-form-visible-subheading'>
                ". esc_html(__('AND', 'js-support-ticket')) ."
            </div>
            <div class='js-form-wrapper js-form-visible-wrapper' >
                <div class='js-form-value' id='js_or_row_". $orid ."'>
                    ". wp_kses(JSSTformfield::hidden('visibleLogic[]', 'AND'), JSST_ALLOWED_TAGS) ."
                    ". wp_kses(JSSTformfield::select('visibleParent[]', JSSTincluder::getJSModel('fieldordering')->getFieldsForVisibleCombobox($fieldfor, $formid,$field,$id), '', esc_html(__('Select Parent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field js-form-input-field-visible', 'onchange' => 'getChildForVisibleCombobox(this.value, '.$orid.');getConditionsForVisibleCombobox(this.value, '.$orid.');')), JSST_ALLOWED_TAGS) ."
                    <span class='visibleValueWrp'>
                        ". wp_kses(JSSTformfield::select('visibleValue[]', '', '', esc_html(esc_html(__('Select Child', 'js-support-ticket'))), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                    </span>
                    <span class='visibleConditionWrp'>
                        ". wp_kses(JSSTformfield::select('visibleCondition[]', $equalnotequal, '', esc_html(__('Select Condition', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                    </span>
                    <div class='js-visible-conditions-body-row'>
                        <div class='js-visible-conditions-body-value'>
                            <span onclick=\"deleteOrRow('js_or_row_". $orid ."')\" class='js-visible-conditions-delbtn'>
                                <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete-2.png' />
                            </span>
                        </div>
                    </div>
                </div>
                <div class='js-form-visible-or-row'></div>
                <div class='js-visible-conditions-addbtn-wrp'>
                    <span class='js-form-visible-addmore' onclick='getMoreORRow(this, ". esc_js($fieldfor) .", ". esc_js($formid) .")'>
                        <img alt='". esc_html(__('OR', 'js-support-ticket')) ."' class='input-field-remove-img' src='". esc_url(JSST_PLUGIN_URL) ."includes/images/plus-icon.png'>
                        ". esc_html(__('OR', 'js-support-ticket')) ."
                    </span>
                </div>
            </div>
        </div>
        ";
        $html = jssupportticketphplib::JSST_htmlentities($html);
        return wp_json_encode($html);
    }

}

?>
