<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTfieldorderingModel {

    function getFieldOrderingForList($jsst_fieldfor) {
        if(!is_numeric($jsst_fieldfor)){
            return false;
        }
	    $jsst_formid = jssupportticket::$jsst_data['formid'];
        if (isset($jsst_formid) && $jsst_formid != null) {
            $jsst_inquery = " AND multiformid = ".intval($jsst_formid);
        }
    	else{
            $jsst_inquery = " AND multiformid = ".JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
    	}

        // Pagination
        /*
          $jsst_query = "SELECT COUNT(`id`) FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE published = 1 AND fieldfor = 1";
          $jsst_total = jssupportticket::$_db->get_var($jsst_query);
          jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);
         */

        // Data
//        $jsst_query = "SELECT * FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE published = 1 AND fieldfor = 1 ORDER BY ordering LIMIT ".JSSTpagination::getOffset().", ".JSSTpagination::getLimit();
        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = ".esc_sql($jsst_fieldfor);
        $jsst_query .= $jsst_inquery." ORDER BY ordering ";

        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function changePublishStatus($jsst_id, $jsst_status) {
        if (!is_numeric($jsst_id))
            return false;
        if ($jsst_status == 'publish') {
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET published = 1 WHERE id = " . esc_sql($jsst_id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as published', 'js-support-ticket')),'updated');
        } elseif ($jsst_status == 'unpublish') {
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET published = 0 WHERE id = " . esc_sql($jsst_id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as unpublished', 'js-support-ticket')),'updated');
        }
        return;
    }

    function changeVisitorPublishStatus($jsst_id, $jsst_status) {
        if (!is_numeric($jsst_id))
            return false;
        if ($jsst_status == 'publish') {
            $jsst_query = "SELECT adminonly FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($jsst_id);
            $jsst_adminonly = jssupportticket::$_db->get_var($jsst_query);
            if(!empty($jsst_adminonly)){
                JSSTmessage::setMessage(esc_html(__('Field cannot be mark as published', 'js-support-ticket')),'error');
            }else{
                $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET isvisitorpublished = 1 WHERE id = " . esc_sql($jsst_id) . " AND cannotunpublish = 0";
                jssupportticket::$_db->query($jsst_query);
                if (jssupportticket::$_db->last_error != null) {
                    JSSTincluder::getJSModel('systemerror')->addSystemError();
                }
                JSSTmessage::setMessage(esc_html(__('Field mark as published', 'js-support-ticket')),'updated');
            }
        } elseif ($jsst_status == 'unpublish') {
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET isvisitorpublished = 0 WHERE id = " . esc_sql($jsst_id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as unpublished', 'js-support-ticket')),'updated');
        }
        return;
    }

    function changeRequiredStatus($jsst_id, $jsst_status) {
        if (!is_numeric($jsst_id))
            return false;

        // $jsst_query = "SELECT field FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE id =".esc_sql($jsst_id);
        // $jsst_child = jssupportticket::$_db->get_var($jsst_query);
        // $jsst_query = "SELECT count(id) FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE visible_field = '".esc_sql($jsst_child)."'";
        // $jsst_count = jssupportticket::$_db->get_var($jsst_query);
        // if ($jsst_count > 0) {
        //     JSSTmessage::setMessage(esc_html(__('Field cannot mark as required', 'js-support-ticket')), 'error');
        //     return;
        // }
        if ($jsst_status == 'required') {
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET required = 1 WHERE id = " . esc_sql($jsst_id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as required', 'js-support-ticket')),'updated');
        } elseif ($jsst_status == 'unrequired') {
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET required = 0 WHERE id = " . esc_sql($jsst_id) . " AND cannotunpublish = 0";
            jssupportticket::$_db->query($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            JSSTmessage::setMessage(esc_html(__('Field mark as not required', 'js-support-ticket')),'updated');
        }
        return;
    }

    function changeOrder($jsst_id, $jsst_action) {
        if (!is_numeric($jsst_id))
            return false;
        if ($jsst_action == 'down') {
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f1, `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f2
                        SET f1.ordering = f1.ordering - 1 WHERE f1.ordering = f2.ordering + 1 AND f1.fieldfor = f2.fieldfor
                        AND f2.id = " . esc_sql($jsst_id);
            jssupportticket::$_db->query($jsst_query);
            $jsst_query = " UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET ordering = ordering + 1 WHERE id = " . esc_sql($jsst_id);
            jssupportticket::$_db->query($jsst_query);
            JSSTmessage::setMessage(esc_html(__('Field ordering down', 'js-support-ticket')),'updated');
        } elseif ($jsst_action == 'up') {
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f1, `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` AS f2 SET f1.ordering = f1.ordering + 1
                        WHERE f1.ordering = f2.ordering - 1 AND f1.fieldfor = f2.fieldfor AND f2.id = " . esc_sql($jsst_id);
            jssupportticket::$_db->query($jsst_query);
            $jsst_query = " UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET ordering = ordering - 1 WHERE id = " . esc_sql($jsst_id);
            jssupportticket::$_db->query($jsst_query);
            JSSTmessage::setMessage(esc_html(__('Field ordering up', 'js-support-ticket')),'updated');
        }
        return;
    }

    function getFieldsOrderingforForm($jsst_fieldfor,$jsst_formid='') {
        if (!is_numeric($jsst_fieldfor))
            return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' published = 1 ';
        }
	    if(!isset($jsst_formid) || $jsst_formid==''){
		    $jsst_formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
	    }
        if(!is_numeric($jsst_formid)) return false;
        // admin only check
        $jsst_adminonly = '';
        if ($jsst_fieldfor == 1) {
            if( in_array('agent',jssupportticket::$_active_addons) ){
                $jsst_agent = JSSTincluder::getJSModel('agent')->isUserStaff();
            }else{
                $jsst_agent = false;
            }
            if(!is_admin() && !$jsst_agent){
                $jsst_adminonly = ' AND adminonly != 1 ';
            }
        }
        $jsst_query = "SELECT  * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE ".$jsst_published." AND fieldfor =  " . esc_sql($jsst_fieldfor);
        if ($jsst_fieldfor == 1) {
            $jsst_query .= " AND multiformid =  " . intval($jsst_formid);
        }
        $jsst_query .=  esc_sql($jsst_adminonly) . " ORDER BY ordering ";
        jssupportticket::$jsst_data['fieldordering'] = jssupportticket::$_db->get_results($jsst_query);
        return;
    }

    function checkIsFieldRequired($jsst_field,$jsst_formid='') {
        if(!isset($jsst_formid) || $jsst_formid==''){
            $jsst_formid = JSSTincluder::getJSmodel('ticket')->getDefaultMultiFormId();
        }
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' published = 1 ';
        }
        $jsst_query = "SELECT required FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE ".$jsst_published." AND fieldfor =  1 AND  field =  '".esc_sql($jsst_field)."' AND multiformid =  " . intval($jsst_formid);
        $jsst_required = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_required;
    }

    function storeUserField($jsst_data) {
        if (empty($jsst_data)) {
            return false;
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions
        if(!is_numeric($jsst_data['fieldfor'])) return false;
        if ($jsst_data['isuserfield'] == 1) {
            // value to add as field ordering
            if ($jsst_data['id'] == '') { // only for new
                $jsst_query = "SELECT max(ordering) FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor=".esc_sql($jsst_data['fieldfor']);
                $jsst_var = jssupportticket::$_db->get_var($jsst_query);
                $jsst_data['ordering'] = $jsst_var + 1;
                if(isset($jsst_data['userfieldtype']) && ($jsst_data['userfieldtype'] == 'file' || $jsst_data['userfieldtype'] == 'termsandconditions' ) ){
                    $jsst_data['cannotsearch'] = 1;
                    $jsst_data['cannotshowonlisting'] = 1;
                }else{
                    $jsst_data['cannotshowonlisting'] = 0;
                    $jsst_data['cannotsearch'] = 0;
                }
                $jsst_query = "SELECT max(id) FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering ";
                $jsst_var = jssupportticket::$_db->get_var($jsst_query);
                $jsst_var = $jsst_var + 1;
                $jsst_fieldname = 'ufield_'.$jsst_var;
            }else{
                $jsst_fieldname = !empty($jsst_data['field']) ? $jsst_data['field'] : '';
            }
            if ($jsst_data['userfieldtype'] == 'termsandconditions') { // only for terms and conditions
                $jsst_data['required'] = 1;
            }

            $jsst_params = array();
            //code for depandetn field
            if (isset($jsst_data['userfieldtype']) && $jsst_data['userfieldtype'] == 'depandant_field') {
                if ($jsst_data['id'] != '') {
                    //to handle edit case of depandat field
                    $jsst_data['arraynames'] = $jsst_data['arraynames2'];
                }
                if (!empty($jsst_data['arraynames'])) {
                    $jsst_valarrays = jssupportticketphplib::JSST_explode('_JSST_Unique_88a9e3_', $jsst_data['arraynames']);
                    $jsst_empty_flag = 0;
                    $jsst_key_flag = '';
                    foreach ($jsst_valarrays as $jsst_key => $jsst_value) {
                        if($jsst_key != $jsst_key_flag){
                            $jsst_key_flag = $jsst_key;
                            $jsst_empty_flag = 0;
                        }
                        $jsst_keyvalue = $jsst_value;
                        $jsst_value = jssupportticketphplib::JSST_str_replace(' ','__',$jsst_value);
                        $jsst_value = jssupportticketphplib::JSST_str_replace('.','___',$jsst_value);
                        // This check was previously commented out, but caused errors when child values were empty.
                        // Uncommented and handled properly by Hamza to avoid runtime issues with empty inputs.
                        if ( isset($jsst_data[$jsst_value]) && $jsst_data[$jsst_value] != null) {
                            $jsst_keyvalue = jssupportticketphplib::JSST_htmlentities($jsst_keyvalue);
                            $jsst_params[$jsst_keyvalue] = array_filter($jsst_data[$jsst_value]);
                            $jsst_empty_flag = 1;
                        }
                    }
                    if($jsst_empty_flag == 0){
                        JSSTmessage::setMessage(esc_html(__('Please Insert At least one value for every option', 'js-support-ticket')), 'error');
                        return 2 ;
                    }
                }
                $jsst_flagvar = $this->updateParentField($jsst_data['parentfield'], $jsst_fieldname, $jsst_data['fieldfor']);
                if ($jsst_flagvar == false) {
                    JSSTmessage::setMessage(esc_html(__('Parent field has not been stored', 'js-support-ticket')), 'error');
                }
            }
            if (!empty($jsst_data['values'])) {
                foreach ($jsst_data['values'] as $jsst_key => $jsst_value) {
                    if ($jsst_value != null) {
                        $jsst_value = jssupportticketphplib::JSST_str_replace('[','',$jsst_value);
                        $jsst_value = jssupportticketphplib::JSST_str_replace(']','',$jsst_value);
                        $jsst_params[] = jssupportticketphplib::JSST_trim($jsst_value);
                    }
                }
            }

            // 
            $jsst_visible = [];

            if (isset($jsst_data['visibleParent']) && is_array($jsst_data['visibleParent'])) {

                // new start

                if (!empty($jsst_data['id'])) {
                    $jsst_query = "SELECT id, visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE visible_field LIKE '%" . esc_sql($jsst_fieldname) . "%' AND multiformid = ".esc_sql($jsst_data['multiformid']);
                    $jsst_query_results = jssupportticket::$_db->get_results($jsst_query);
                    
                    if (!empty($jsst_query_results)) {
                        foreach ($jsst_query_results as $jsst_query_result) {
                            $jsst_query_fieldname = $jsst_query_result->visible_field;
                            $jsst_query_fieldname = jssupportticketphplib::JSST_str_replace(',' . $jsst_fieldname, '', $jsst_query_fieldname);
                            $jsst_query_fieldname = jssupportticketphplib::JSST_str_replace($jsst_fieldname, '', $jsst_query_fieldname);
                            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($jsst_query_fieldname) . "' WHERE id = " . esc_sql($jsst_query_result->id) . " AND multiformid = ".esc_sql($jsst_data['multiformid']);
                            jssupportticket::$_db->query($jsst_query);
                        }
                    }
                }

                // new end
                $jsst_visibleParents = $jsst_data['visibleParent'];
                $jsst_visibleValues = isset($jsst_data['visibleValue']) ? $jsst_data['visibleValue'] : [];
                $jsst_visibleConditions = isset($jsst_data['visibleCondition']) ? $jsst_data['visibleCondition'] : [];
                $jsst_visibleLogics = isset($jsst_data['visibleLogic']) ? $jsst_data['visibleLogic'] : [];

                $jsst_final = []; // Final grouped structure
                $jsst_currentAndGroup = []; // Current AND group

                foreach ($jsst_visibleParents as $jsst_index => $jsst_parentFieldId) {
                    if (
                        isset($jsst_visibleParents[$jsst_index]) && $jsst_visibleParents[$jsst_index] !== '' &&
                        isset($jsst_visibleValues[$jsst_index]) && $jsst_visibleValues[$jsst_index] !== '' &&
                        isset($jsst_visibleConditions[$jsst_index]) && $jsst_visibleConditions[$jsst_index] !== ''
                    ) {
                        $jsst_fieldname = $jsst_fieldname ?? ''; // Just in case
                        $jsst_logic = isset($jsst_visibleLogics[$jsst_index]) ? $jsst_visibleLogics[$jsst_index] : '';

                        // Build the current row
                        $jsst_row = [
                            'visibleParentField' => $jsst_fieldname,
                            'visibleParent' => $jsst_visibleParents[$jsst_index],
                            'visibleCondition' => $jsst_visibleConditions[$jsst_index],
                            'visibleValue' => $jsst_visibleValues[$jsst_index],
                            'visibleLogic' => $jsst_logic
                        ];

                        if ($jsst_logic === 'AND') {
                            // New AND group starts
                            if (!empty($jsst_currentAndGroup)) {
                                $jsst_final[] = $jsst_currentAndGroup;
                                $jsst_currentAndGroup = [];
                            }
                            $jsst_currentAndGroup[] = $jsst_row;
                        } else {
                            // Continue current AND group (OR rows)
                            $jsst_currentAndGroup[] = $jsst_row;
                        }

                        // --- your database update code ---
                        $jsst_query = "SELECT visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE field = '" . esc_sql($jsst_visibleParents[$jsst_index]) . "' AND multiformid = ".esc_sql($jsst_data['multiformid']);
                        $jsst_old_fieldname = jssupportticket::$_db->get_var($jsst_query);
                        $jsst_new_fieldname = $jsst_fieldname;

                        if (!empty($jsst_data['id'])) {
                            $jsst_old_fieldname = jssupportticketphplib::JSST_str_replace(',' . $jsst_fieldname, '', $jsst_old_fieldname);
                            $jsst_old_fieldname = jssupportticketphplib::JSST_str_replace($jsst_fieldname, '', $jsst_old_fieldname);
                        }

                        if (!empty($jsst_old_fieldname)) {
                            $jsst_new_fieldname = $jsst_old_fieldname . ',' . $jsst_new_fieldname;
                        }

                        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($jsst_new_fieldname) . "' WHERE field = '" . esc_sql($jsst_visibleParents[$jsst_index]) . "' AND multiformid = ".esc_sql($jsst_data['multiformid']);
                        jssupportticket::$_db->query($jsst_query);

                        if (jssupportticket::$_db->last_error != null) {
                            JSSTincluder::getJSModel('systemerror')->addSystemError();
                        }
                    }
                }
                // After finishing all rows
                if (!empty($jsst_currentAndGroup)) {
                    $jsst_final[] = $jsst_currentAndGroup;
                }

                // Now sanitize and save the final nested array
                $jsst_visible_array = array_map(array($this, 'sanitize_custom_field'), $jsst_final);
                $jsst_visibleparams = wp_json_encode(stripslashes_deep($jsst_visible_array));

            } else if (!empty($jsst_data['id'])) {
                if ($jsst_data['fieldfor'] != 3) {
                    $jsst_data['visibleparams'] = '';
                    // If editing old field
                    $jsst_query = "SELECT id, visible_field FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE visible_field LIKE '%" . esc_sql($jsst_fieldname) . "%' AND multiformid = ".esc_sql($jsst_data['multiformid']);
                    $jsst_query_results = jssupportticket::$_db->get_results($jsst_query);
                    if (!empty($jsst_query_results)) {
                        foreach ($jsst_query_results as $jsst_query_result) {
                            if (isset($jsst_query_result)) {
                                $jsst_query_fieldname = $jsst_query_result->visible_field;
                                $jsst_query_fieldname = jssupportticketphplib::JSST_str_replace(',' . $jsst_fieldname, '', $jsst_query_fieldname);
                                $jsst_query_fieldname = jssupportticketphplib::JSST_str_replace($jsst_fieldname, '', $jsst_query_fieldname);
                                $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '" . esc_sql($jsst_query_fieldname) . "' WHERE id = " . esc_sql($jsst_query_result->id);
                                jssupportticket::$_db->query($jsst_query);
                            }
                        }
                    }
                }
            }
            if (isset($jsst_data['userfieldtype']) && $jsst_data['userfieldtype'] == 'termsandconditions') { // to manage terms and condition field
                if ($jsst_data['termsandconditions_linktype'] == 1) {
                    $jsst_params['termsandconditions_link'] = $jsst_data['termsandconditions_link'];
                } else if ($jsst_data['termsandconditions_linktype'] == 2) {
                    $jsst_params['termsandconditions_page'] = $jsst_data['termsandconditions_page'];
                }
                $jsst_params['termsandconditions_text'] = $jsst_data['termsandconditions_text'];
                $jsst_params['termsandconditions_linktype'] = $jsst_data['termsandconditions_linktype'];
            }

                // $jsst_params = wp_json_encode($jsst_params);
                $jsst_params_array = array_map(array($this,'sanitize_custom_field'), $jsst_params);
                $jsst_userfieldparams = wp_json_encode(stripslashes_deep($jsst_params_array));

            //}
            // for default value
            $jsst_data['defaultvalue'] = '';
            if($jsst_data['userfieldtype'] == "combo" || $jsst_data['userfieldtype'] == "radio" || $jsst_data['userfieldtype'] == "multiple" || $jsst_data['userfieldtype'] == "checkbox" || $jsst_data['userfieldtype'] == "depandant_field") {
                $jsst_data['defaultvalue'] = !empty($jsst_data['defaultvalue_select']) ? $jsst_data['defaultvalue_select'] : '';
            } else {
                $jsst_data['defaultvalue'] = !empty($jsst_data['defaultvalue_input']) ? $jsst_data['defaultvalue_input'] : '';
            }
        }else{
            $jsst_fieldname = $jsst_data['field'];
            $jsst_data['userfieldtype'] = '';
            $jsst_data['defaultvalue'] = !empty($jsst_data['defaultvalue_input']) ? $jsst_data['defaultvalue_input'] : '';
            // get data for system fields of type terms ans conditions
            if (in_array($jsst_data['field'], ['termsandconditions1', 'termsandconditions2', 'termsandconditions3'])) { // to manage terms and condition field
                if ($jsst_data['termsandconditions_linktype'] == 1) {
                    $jsst_params['termsandconditions_link'] = $jsst_data['termsandconditions_link'];
                } else if ($jsst_data['termsandconditions_linktype'] == 2) {
                    $jsst_params['termsandconditions_page'] = $jsst_data['termsandconditions_page'];
                }
                $jsst_params['termsandconditions_text'] = $jsst_data['termsandconditions_text'];
                $jsst_params['termsandconditions_linktype'] = $jsst_data['termsandconditions_linktype'];
                $jsst_params_array = array_map(array($this,'sanitize_custom_field'), $jsst_params);
                $jsst_userfieldparams = wp_json_encode(stripslashes_deep($jsst_params_array));
            }
        }

        // for adminonly
        if(!empty($jsst_data['adminonly'])){
            $jsst_data['isvisitorpublished'] = 0;
            $jsst_data['search_visitor'] = 0;
            $jsst_data['search_user'] = 0;
        }

        $jsst_data['field'] = $jsst_fieldname;
        $jsst_data['section'] = 10;

        /*if (!empty($jsst_data['depandant_field']) && $jsst_data['depandant_field'] != null ) {

            $jsst_query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering where
            field = '". esc_sql($jsst_data['depandant_field'])."'";
            $jsst_child = jssupportticket::$_db->get_row($jsst_query);
            $jsst_parent = $jsst_data;
            $jsst_flagvar = $this->updateChildField($jsst_parent, $jsst_child);
            if ($jsst_flagvar == false) {
                JSSTmessage::setMessage(esc_html(__('Child fields has not been stored', 'js-support-ticket')), 'error');
            }
        }*/

        $jsst_row = JSSTincluder::getJSTable('fieldsordering');
        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        if (!empty($jsst_userfieldparams)) {
            $jsst_data['userfieldparams'] = $jsst_userfieldparams;
        }
        if (!empty($jsst_visibleparams)) {
            $jsst_data['visibleparams'] = $jsst_visibleparams;
        }
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 1) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            JSSTmessage::setMessage(esc_html(__('Field has not been stored', 'js-support-ticket')), 'error');
        } else {
            JSSTmessage::setMessage(esc_html(__('Field has been stored', 'js-support-ticket')), 'updated');
            // update the dependent fields data if exist
            if (!empty($jsst_data['depandant_field']) && $jsst_data['depandant_field'] != null ) {

                $jsst_query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering where
                field = '". esc_sql($jsst_data['depandant_field'])."'";
                $jsst_child = jssupportticket::$_db->get_row($jsst_query);
                
                /* get parent saved data */
                $jsst_query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering where
                id = '". esc_sql($jsst_data['id'])."'";
                $jsst_parent = jssupportticket::$_db->get_row($jsst_query);
                /* get parent saved data */
                
                // $jsst_parent = $jsst_data;
                $jsst_flagvar = $this->updateChildField($jsst_parent, $jsst_child);
                if ($jsst_flagvar == false) {
                    JSSTmessage::setMessage(esc_html(__('Child fields has not been stored', 'js-support-ticket')), 'error');
                }
            }
        }
        return 1;
    }

    function updateField($jsst_data) {
        if (empty($jsst_data)) {
            return false;
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_inquery = '';
        $jsst_clasue = '';
        if(isset($jsst_data['fieldtitle']) && $jsst_data['fieldtitle'] != null){
            $jsst_inquery .= $jsst_clasue." fieldtitle = '". esc_sql($jsst_data['fieldtitle'])."'";
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['published']) && $jsst_data['published'] != null){
            $jsst_inquery .= $jsst_clasue." published = ". esc_sql($jsst_data['published']);
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['isvisitorpublished']) && $jsst_data['isvisitorpublished'] != null){
            $jsst_inquery .= $jsst_clasue." isvisitorpublished = ". esc_sql($jsst_data['isvisitorpublished']);
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['placeholder']) && $jsst_data['placeholder'] != null){
            $jsst_inquery .= $jsst_clasue." placeholder = '". esc_sql($jsst_data['placeholder']) ."'";
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['description']) && $jsst_data['description'] != null){
            $jsst_inquery .= $jsst_clasue." description = '". esc_sql($jsst_data['description']) . "'";
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['required']) && $jsst_data['required'] != null){
            $jsst_inquery .= $jsst_clasue." required = ". esc_sql($jsst_data['required']);
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['search_user']) && $jsst_data['search_user'] != null){
            $jsst_inquery .= $jsst_clasue." search_user = ". esc_sql($jsst_data['search_user']);
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['search_admin']) && $jsst_data['search_admin'] != null){
            $jsst_inquery .= $jsst_clasue." search_admin = ". esc_sql($jsst_data['search_admin']);
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['search_visitor']) && $jsst_data['search_visitor'] != null){
            $jsst_inquery .= $jsst_clasue." search_visitor = ". esc_sql($jsst_data['search_visitor']);
            $jsst_clasue = ' , ';
        }
        if(isset($jsst_data['showonlisting']) && $jsst_data['showonlisting'] != null){
            $jsst_inquery .= $jsst_clasue." showonlisting = ". esc_sql($jsst_data['showonlisting']);
            $jsst_clasue = ' , ';
        }

        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET ".$jsst_inquery." WHERE id = " . esc_sql($jsst_data['id']) ;
        jssupportticket::$_db->query($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        JSSTmessage::setMessage(esc_html(__('Field has been updated', 'js-support-ticket')),'updated');

        return;
    }

    function updateParentField($jsst_parentfield, $jsst_field, $jsst_fieldfor) {
        if(!is_numeric($jsst_fieldfor)) return false;
        if(!is_numeric($jsst_parentfield)) return false;
        if(empty($jsst_field)) return false;

        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET depandant_field = '" . esc_sql($jsst_field) . "' WHERE id = " . esc_sql($jsst_parentfield)." AND fieldfor = ".esc_sql($jsst_fieldfor);
        jssupportticket::$_db->query($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return true;
    }

    function updateChildField($jsst_parent, $jsst_child){
        if(!is_numeric($jsst_child->id)) return false;
        $jsst_childfieldparams = json_decode( $jsst_child->userfieldparams,TRUE);
        $jsst_parentfieldparams = json_decode( $jsst_parent->userfieldparams,TRUE);

        // $jsst_parentfieldparams = stripslashes_deep($jsst_parentfieldparams);
        // $jsst_childfieldparams = stripslashes_deep($jsst_childfieldparams);

        $jsst_childNew = [];

        foreach ($jsst_parentfieldparams as $jsst_parentKey => $jsst_parentValue) {
            $jsst_childKeys = is_array($jsst_parentValue) ? $jsst_parentValue : [$jsst_parentValue];

            foreach ($jsst_childKeys as $jsst_childKey) {
                if (isset($jsst_childfieldparams[$jsst_childKey])) {
                    $jsst_childNew[$jsst_childKey] = $jsst_childfieldparams[$jsst_childKey];
                } else {
                    $jsst_childNew[$jsst_childKey] = '';
                }
            }
        }
        //$jsst_childNew = wp_json_encode( stripslashes_deep($jsst_childNew) );
        $jsst_childNew = wp_json_encode( $jsst_childNew  );
        $jsst_child->userfieldparams = $jsst_childNew;
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET userfieldparams = '" . esc_sql($jsst_childNew) . "' WHERE id = " . esc_sql($jsst_child->id);
        jssupportticket::$_db->query($jsst_query);
        if (jssupportticket::$_db->last_error != null) {

            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return true;
    }

    function getFieldsForComboByFieldFor() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-fields-for-combo-by-fieldfor') ) {
            die( 'Security check Failed' );
        }
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        $jsst_parentfield = JSSTrequest::getVar('parentfield');
        if(!is_numeric($jsst_fieldfor)) return false;
        $jsst_wherequery = '';
        if(isset($jsst_parentfield) && $jsst_parentfield !='' ){
            $jsst_query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($jsst_fieldfor)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo'OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($jsst_parentfield) . "' ";
            $jsst_parent = jssupportticket::$_db->get_var($jsst_query);
            $jsst_wherequery = ' OR id = '.esc_sql($jsst_parent);
        }
        $jsst_query = "SELECT fieldtitle AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($jsst_fieldfor)." AND multiformid = ".intval($jsst_formid)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo' OR userfieldtype = 'depandant_field') AND (depandant_field = '' ".esc_sql($jsst_wherequery)." ) ";
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        if(isset($jsst_parentfield) && $jsst_parentfield !='' ){
            $jsst_query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($jsst_fieldfor)." AND (userfieldtype = 'radio' OR userfieldtype = 'combo'OR userfieldtype = 'depandant_field') AND depandant_field = '" . esc_sql($jsst_parentfield) . "' ";
            $jsst_parent = jssupportticket::$_db->get_var($jsst_query);
        }
        $jsst_nonce = wp_create_nonce("get-section-to-fill-values-".$jsst_fieldfor);
        $jsst_jsFunction = 'getDataOfSelectedField("'.$jsst_nonce.'");';
        $jsst_html = JSSTformfield::select('parentfield', $jsst_data, (isset($jsst_parent) && $jsst_parent !='') ? $jsst_parent : '', esc_html(__('Select', 'js-support-ticket')) .'&nbsp;'. esc_html(__('Parent Field', 'js-support-ticket')), array('onchange' => $jsst_jsFunction, 'class' => 'inputbox one js-form-select-field', 'data-validation' => 'required'));
        $jsst_html = jssupportticketphplib::JSST_htmlentities($jsst_html);
        $jsst_data = wp_json_encode($jsst_html);
        return $jsst_data;
    }

    function getFieldsForVisibleCombobox($jsst_fieldfor, $jsst_multiformid, $jsst_field='', $jsst_cid='') {
        if(!is_numeric($jsst_fieldfor)) return false;
        $jsst_wherequery = '';
        if(isset($jsst_field) && $jsst_field !='' ){
            $jsst_query = "SELECT id FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE fieldfor = ".esc_sql($jsst_fieldfor)." AND (userfieldtype IN ( 'combo', 'text', 'checkbox', 'date', 'email', 'radio', 'multiple') ) AND visible_field = '" . esc_sql($jsst_field) . "' ";
            $jsst_parent = jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_parent) {
                $jsst_wherequery = ' OR id = '.esc_sql($jsst_parent);
            }
        }
        $jsst_wherequeryforedit = '';
        if(isset($jsst_cid) && $jsst_cid !='' ){
            $jsst_wherequeryforedit = ' AND id != '.esc_sql($jsst_cid);
        }
        
        // Base fields always included
        $jsst_builtin_fields = ['email', 'fullname', 'phone', 'subject', 'department', 'priority'];

        // Conditionally add 'helptopic' if the addon is active
        /*if (in_array('helptopic', jssupportticket::$_active_addons)) {
            $jsst_builtin_fields[] = 'helptopic';
        }*/

        // Convert to comma-separated string for SQL IN clause
        $jsst_builtin_fields_sql = "'" . implode("','", array_map('esc_sql', $jsst_builtin_fields)) . "'";

        // Build the final SQL query
        $jsst_query = "
        SELECT fieldtitle AS text, field AS id 
            FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering 
            WHERE (
                fieldfor = " . esc_sql($jsst_fieldfor) . " 
                AND multiformid = '" . esc_sql($jsst_multiformid) . "' 
                AND field IN ($jsst_builtin_fields_sql) 
                $jsst_wherequeryforedit $jsst_wherequery
            ) 
            OR (
                fieldfor = " . esc_sql($jsst_fieldfor) . " 
                AND multiformid = '" . esc_sql($jsst_multiformid) . "' 
                AND userfieldtype IN ('combo', 'text', 'checkbox', 'date', 'email', 'radio', 'multiple') 
                $jsst_wherequeryforedit $jsst_wherequery
            )";
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_data;
    }

    function getChildForVisibleCombobox($jsst_perentid = null , $jsst_default = null) {
        $jsst_isAjaxCall = JSSTrequest::getVar('isAjaxCall');
        if ($jsst_isAjaxCall == 1) {
            $jsst_nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $jsst_nonce, 'get-child-for-visible-combobox') ) {
                die( 'Security check Failed' );
            }
        }
        if ($jsst_perentid == null) {
            $jsst_perentid = JSSTrequest::getVar('val');
        }
        if (empty($jsst_perentid)){
            return false;
        }

        $jsst_query = "SELECT isuserfield, userfieldtype, field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '" . esc_sql($jsst_perentid)."'";
        $jsst_fieldType = jssupportticket::$_db->get_row($jsst_query);
        $jsst_showComboBox = false;
        if (isset($jsst_fieldType->isuserfield) && $jsst_fieldType->isuserfield == 1) {
            $jsst_query = "SELECT userfieldparams AS params FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '" . esc_sql($jsst_perentid) . "'";
            $jsst_options = jssupportticket::$_db->get_var($jsst_query);
            $jsst_options = json_decode($jsst_options);
            foreach ($jsst_options as $jsst_key => $jsst_option) {
                $jsst_fieldtypes[$jsst_key] = (object) array('id' => $jsst_option, 'text' => $jsst_option);
            }
            if (in_array($jsst_fieldType->userfieldtype, ['combo', 'checkbox', 'radio', 'multiple'])) {
                $jsst_showComboBox = true;
            }
        } else if ($jsst_fieldType->field == 'department') {
            $jsst_showComboBox = true;
            $jsst_query = "SELECT departmentname AS text ,id FROM " . jssupportticket::$_db->prefix . "js_ticket_departments";
            $jsst_fieldtypes = jssupportticket::$_db->get_results($jsst_query);
        } else if ($jsst_fieldType->field == 'helptopic') {
            $jsst_showComboBox = true;
            $jsst_query = "SELECT id, topic AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_help_topics` WHERE status = 1";
            $jsst_query.= "  ORDER BY ordering ASC";
            $jsst_fieldtypes = jssupportticket::$_db->get_results($jsst_query);
        } else if ($jsst_fieldType->field == 'priority') {
            $jsst_showComboBox = true;
            $jsst_query = "SELECT id, priority AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`";
            $jsst_query .= 'ORDER BY ordering ASC';
            $jsst_fieldtypes = jssupportticket::$_db->get_results($jsst_query);
        }
        //
        $jsst_combobox = false;
        if($jsst_showComboBox){
            $jsst_combobox = JSSTformfield::select('visibleValue[]', $jsst_fieldtypes, isset($jsst_default) ? $jsst_default : '', '', array('class' => 'inputbox one js-form-select-field js-form-input-field-visible'));
        } else {
            $jsst_combobox = JSSTformfield::text('visibleValue[]', isset($jsst_default) ? $jsst_default : '', array('class' => 'inputbox one js-form-input-field js-form-input-field-visible'));
        }
        return jssupportticketphplib::JSST_htmlentities($jsst_combobox);
    }

    function getConditionsForVisibleCombobox($jsst_perentid = null , $jsst_default = null) {
        $jsst_isAjaxCall = JSSTrequest::getVar('isAjaxCall');
        if ($jsst_isAjaxCall == 1) {
            $jsst_nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $jsst_nonce, 'get-conditions-for-visible-combobox') ) {
                die( 'Security check Failed' );
            }
        }
        if ($jsst_perentid == null) {
            $jsst_perentid = JSSTrequest::getVar('val');
        }
        if (empty($jsst_perentid)){
            return false;
        }
        $Conditions = array(
        (object) array('id' => 1, 'text' => esc_html(__('Equal', 'js-support-ticket'))),
        (object) array('id' => 0, 'text' => esc_html(__('Not Equal', 'js-support-ticket'))));

        $jsst_query = "SELECT isuserfield, userfieldtype, field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '" . esc_sql($jsst_perentid) . "'";
        $jsst_fieldType = jssupportticket::$_db->get_row($jsst_query);
        if (empty($jsst_fieldType->isuserfield)) {
            if ($jsst_fieldType->field == 'email' || $jsst_fieldType->field == 'fullname' || $jsst_fieldType->field == 'phone' || $jsst_fieldType->field == 'subject' || $jsst_fieldType->field == 'issuesummary') {
                $Conditions = array(
                (object) array('id' => 2, 'text' => esc_html(__('Contain', 'js-support-ticket'))),
                (object) array('id' => 3, 'text' => esc_html(__('Not Contain', 'js-support-ticket'))));
            }
        } else {
            if (!in_array($jsst_fieldType->userfieldtype, ['combo', 'checkbox', 'radio', 'multiple'])) {
                $Conditions = array(
                (object) array('id' => 2, 'text' => esc_html(__('Contain', 'js-support-ticket'))),
                (object) array('id' => 3, 'text' => esc_html(__('Not Contain', 'js-support-ticket'))));
            }
        }
        $jsst_combobox = false;
        if(!empty($Conditions)){
            $jsst_combobox = JSSTformfield::select('visibleCondition[]', $Conditions, isset($jsst_default) ? $jsst_default : '', '', array('class' => 'inputbox one js-form-select-field js-form-input-field-visible'));
        }
        return jssupportticketphplib::JSST_htmlentities($jsst_combobox);
    }

    function getSectionToFillValues() {
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-section-to-fill-values-'.$jsst_fieldfor) ) {
            die( 'Security check Failed' );
        }
        $jsst_field = JSSTrequest::getVar('pfield');
        if(!is_numeric($jsst_field)){
            return false;
        }
        $jsst_query = "SELECT userfieldparams FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id=".esc_sql($jsst_field);
        $jsst_data = jssupportticket::$_db->get_var($jsst_query);
        $jsst_datas = json_decode($jsst_data);
        $jsst_html = '';
        $jsst_fieldsvar = '';
        $jsst_comma = '';
        foreach ($jsst_datas as $jsst_data) {
            if(is_array($jsst_data)){
                for ($jsst_i = 0; $jsst_i < count($jsst_data); $jsst_i++) {
                    $jsst_fieldsvar .= $jsst_comma . "$jsst_data[$jsst_i]";
                    $jsst_textvar = $jsst_data[$jsst_i];
                    $jsst_textvar = jssupportticketphplib::JSST_str_replace(' ','__',$jsst_textvar);
                    $jsst_textvar = jssupportticketphplib::JSST_str_replace('.','___',$jsst_textvar);
                    $jsst_divid = $jsst_textvar;
                    $jsst_js_value = esc_js($jsst_divid);
                    $jsst_textvar .='[]';
                    $jsst_html .= "<div class='jsst-user-dd-field-wrap'>";
                    $jsst_html .= "<div class='jsst-user-dd-field-title'>" . esc_html($jsst_data[$jsst_i]) . "</div>";
                    $jsst_html .= "<div class='jsst-user-dd-field-value combo-options-fields' id=" . esc_attr($jsst_divid) . ">
                                    <span class='input-field-wrapper'>
                                        " . wp_kses(JSSTformfield::text($jsst_textvar, '', array('class' => 'inputbox one user-field')), JSST_ALLOWED_TAGS) . "
                                        <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete.png' />
                                    </span>
                                    <input type='button' class='jsst-button-link button user-field-val-button' id='depandant-field-button' onClick='getNextField(\"" . $jsst_js_value . "\", this);'  value='Add More' />
                                </div>";
                    $jsst_html .= "</div>";
                    $jsst_comma = '_JSST_Unique_88a9e3_';
                }
            }else{
                $jsst_fieldsvar .= $jsst_comma . "$jsst_data";
                $jsst_textvar = $jsst_data;
                $jsst_textvar = jssupportticketphplib::JSST_str_replace(' ','__',$jsst_textvar);
                $jsst_textvar = jssupportticketphplib::JSST_str_replace('.','___',$jsst_textvar);
                $jsst_divid = $jsst_textvar;
                $jsst_js_value = esc_js($jsst_divid);
                $jsst_textvar .='[]';
                $jsst_html .= "<div class='jsst-user-dd-field-wrap'>";
                $jsst_html .= "<div class='jsst-user-dd-field-title'>" . esc_html($jsst_data) . "</div>";
                $jsst_html .= "<div class='jsst-user-dd-field-value combo-options-fields' id=" . esc_attr($jsst_divid) . ">
                                <span class='input-field-wrapper'>
                                    " . wp_kses(JSSTformfield::text($jsst_textvar, '', array('class' => 'inputbox one user-field')), JSST_ALLOWED_TAGS) . "
                                    <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete.png' />
                                </span>
                                <input type='button' class='jsst-button-link button user-field-val-button' id='depandant-field-button' onClick=\"getNextField('" . $jsst_js_value . "', this);\"  value='Add More' />
                            </div>";
                $jsst_html .= "</div>";
                $jsst_comma = '_JSST_Unique_88a9e3_';
            }

        }
        $jsst_html .= " <input type='hidden' name='arraynames' value=\"" . esc_attr($jsst_fieldsvar) . "\" />";
        $jsst_html = jssupportticketphplib::JSST_htmlentities($jsst_html);
        $jsst_html = wp_json_encode($jsst_html);
        return $jsst_html;
    }

    function getOptionsForFieldEdit() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-options-for-field-edit') ) {
            die( 'Security check Failed' );
        }
        $jsst_field = JSSTrequest::getVar('field');
		if(!is_numeric($jsst_field)) return false;
        $jsst_yesno = array(
            (object) array('id' => 1, 'text' => esc_html(__('Yes', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('No', 'js-support-ticket'))));

        $jsst_query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id=".esc_sql($jsst_field);
        $jsst_data = jssupportticket::$_db->get_row($jsst_query);

        $jsst_html = '<div class="userpopup-top">
                    <div class="userpopup-heading" >
                    ' . esc_html(__("Edit Field", 'js-support-ticket')) . '
                    </div>
                    <img id="popup_cross" class="userpopup-close" onClick="close_popup();" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/close-icon-white.png" alt="'. esc_html(__('Close','js-support-ticket')).'">
                </div>';
        $jsst_nonce_id = isset($jsst_data->id) ? $jsst_data->id : '';
        $jsst_adminurl = admin_url("?page=fieldordering&task=savefeild&formid=".esc_attr($jsst_data->multiformid));
        $jsst_html .= '<form id="adminForm" class="popup-field-from" method="post" action="' . wp_nonce_url($jsst_adminurl ,"save-feild-".$jsst_nonce_id).'">';
        $jsst_html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Field Title', 'js-support-ticket')) . '<font class="required-notifier">*</font></div>
                    <div class="popup-field-obj">' . JSSTformfield::text('fieldtitle', isset($jsst_data->fieldtitle) ? $jsst_data->fieldtitle : 'text', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        if ($jsst_data->cannotunpublish == 0 || $jsst_data->cannotshowonlisting == 0) {
            $jsst_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('User Published', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('published', $jsst_yesno, isset($jsst_data->published) ? $jsst_data->published : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            $jsst_html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Visitor Published', 'js-support-ticket')) . '</div>
                    <div class="popup-field-obj">' . JSSTformfield::select('isvisitorpublished', $jsst_yesno, isset($jsst_data->isvisitorpublished) ? $jsst_data->isvisitorpublished : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        }

        $jsst_html .= '<div class="popup-field-wrapper">
                <div class="popup-field-title">' . esc_html(__('Place Holder', 'js-support-ticket')) . '</div>
                <div class="popup-field-obj">' . JSSTformfield::text('placeholder', isset($jsst_data->placeholder) ? $jsst_data->placeholder : '', array('class' => 'inputbox one','maxlength'=>225)) . '</div>
            </div>';

        $jsst_html .= '<div class="popup-field-wrapper">
                <div class="popup-field-title">' . esc_html(__('Description', 'js-support-ticket')) . '</div>
                <div class="popup-field-obj">' . JSSTformfield::text('description', isset($jsst_data->description) ? $jsst_data->description : '', array('class' => 'inputbox one','maxlength'=>225)) . '</div>
            </div>';
        if ($jsst_data->cannotunpublish == 0 || $jsst_data->cannotshowonlisting == 0) {

            $jsst_html .= '<div class="popup-field-wrapper">
                    <div class="popup-field-title">' . esc_html(__('Required', 'js-support-ticket')) . '</div>
                    <div class="popup-field-obj">' . JSSTformfield::select('required', $jsst_yesno, isset($jsst_data->required) ? $jsst_data->required : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                </div>';
        }
        if ($jsst_data->cannotsearch == 0) {
            $jsst_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('User Search', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('search_user', $jsst_yesno, isset($jsst_data->search_user) ? $jsst_data->search_user : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            $jsst_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Admin Search', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('search_admin', $jsst_yesno, isset($jsst_data->search_admin) ? $jsst_data->search_admin : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
            if (!empty($jsst_data->adminonly)) {
                // visitor search is not in use
                /*$jsst_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Visitor Search', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('search_visitor', $jsst_yesno, isset($jsst_data->search_visitor) ? $jsst_data->search_visitor : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';*/
            }        
        }
        if ($jsst_data->isuserfield == 1 || $jsst_data->cannotshowonlisting == 0) {
            $jsst_html .= '<div class="popup-field-wrapper">
                        <div class="popup-field-title">' . esc_html(__('Show On Listing', 'js-support-ticket')) . '</div>
                        <div class="popup-field-obj">' . JSSTformfield::select('showonlisting', $jsst_yesno, isset($jsst_data->showonlisting) ? $jsst_data->showonlisting : 0, '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
        }
        $jsst_html .= JSSTformfield::hidden('form_request', 'jssupportticket');
        $jsst_html .= JSSTformfield::hidden('id', $jsst_data->id);
        $jsst_html .= JSSTformfield::hidden('isuserfield', $jsst_data->isuserfield);
        $jsst_html .= JSSTformfield::hidden('fieldfor', $jsst_data->fieldfor);
        $jsst_html .='<div class="js-submit-container js-col-lg-10 js-col-md-10 js-col-md-offset-1 js-col-md-offset-1">
                    ' . JSSTformfield::submitbutton('save', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button'));
        if ($jsst_data->isuserfield == 1) {
            $jsst_html .= '<a class="button" style="margin-left:10px;" id="user-field-anchor" href="?page=fieldordering&jstlay=adduserfeild&jssupportticketid=' . esc_attr($jsst_data->id) .'&fieldfor='.esc_attr($jsst_data->fieldfor).'&formid='.esc_attr($jsst_data->multiformid).'"> ' . esc_html(__('Advanced', 'js-support-ticket')) . ' </a>';
        }

        $jsst_html .='</div>
            </form>';
        $jsst_html = jssupportticketphplib::JSST_htmlentities($jsst_html);
        return wp_json_encode($jsst_html);
    }

    function deleteUserField($jsst_id){
        if (is_numeric($jsst_id) == false)
           return false;
        $jsst_query = "SELECT field,field,fieldfor FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE id = ".esc_sql($jsst_id);
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
        if ($this->userFieldCanDelete($jsst_result) == true) {
            $jsst_row = JSSTincluder::getJSTable('fieldsordering');
            if (!$jsst_row->delete($jsst_id)) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                JSSTmessage::setMessage(esc_html(__('Field has not been deleted', 'js-support-ticket')),'error');
            } else {
                $jsst_query = "SELECT id,visible_field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE visible_field LIKE '%".esc_sql($jsst_result->field)."%'";
                $jsst_results = jssupportticket::$_db->get_results($jsst_query);
                foreach ($jsst_results as $jsst_value) {
                    $jsst_visible_field =  jssupportticketphplib::JSST_str_replace($jsst_result->field.',', '', $jsst_value->visible_field);
                    $jsst_visible_field =  jssupportticketphplib::JSST_str_replace(','.$jsst_result->field, '', $jsst_visible_field);
                    $jsst_visible_field =  jssupportticketphplib::JSST_str_replace($jsst_result->field, '', $jsst_visible_field);

                    $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET visible_field = '".esc_sql($jsst_visible_field)."' WHERE id = ".esc_sql($jsst_value->id);
                    jssupportticket::$_db->query($jsst_query);
                    if (jssupportticket::$_db->last_error != null) {

                        JSSTincluder::getJSModel('systemerror')->addSystemError();
                    }
                }
                $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE depandant_field = '".esc_sql($jsst_result->field)."'";
                $jsst_result = jssupportticket::$_db->get_var($jsst_query);
                if (isset($jsst_result)) {
                    $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET depandant_field = '' WHERE id = ".esc_sql($jsst_result);
                    jssupportticket::$_db->query($jsst_query);
                }
                JSSTmessage::setMessage(esc_html(__('Field has been deleted', 'js-support-ticket')),'updated');
            }
        }else{
            JSSTmessage::setMessage(esc_html(__('Field has not been deleted', 'js-support-ticket')),'error');
        }
        return false;
    }

    function enforceDeleteUserField($jsst_id){
        if (is_numeric($jsst_id) == false)
           return false;
        $jsst_query = "SELECT field,fieldfor FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE id = ".esc_sql($jsst_id);
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
        if ($this->userFieldCanDelete($jsst_result) == true) {
            $jsst_row = JSSTincluder::getJSTable('fieldsordering');
            $jsst_row->delete($jsst_id);
        }
        return false;
    }

    function userFieldCanDelete($jsst_field) {
        $jsst_fieldname = $jsst_field->field;
        $jsst_fieldfor = $jsst_field->fieldfor;

        //if($jsst_fieldfor == 1){//for deleting a ticket field
            $jsst_table = "tickets";
        //}
        $jsst_query = ' SELECT
                    ( SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_'.$jsst_table.'` WHERE
                        params LIKE \'%"' . esc_sql($jsst_fieldname) . '":%\'
                    )
                    AS total';
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        if ($jsst_total > 0)
            return false;
        else
            return true;
    }

    function getUserfieldsfor($jsst_fieldfor,$jsst_multiformid='') {
        if (!is_numeric($jsst_fieldfor))
            return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' published = 1 ';
        }
        $jsst_inquery = '';
        if (isset($jsst_multiformid) && $jsst_multiformid != '') {
            $jsst_inquery = " AND multiformid = ".esc_sql($jsst_multiformid);
        }
        $jsst_query = "SELECT field,userfieldparams,userfieldtype,fieldtitle FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = " . esc_sql($jsst_fieldfor) . " AND isuserfield = 1 AND " . $jsst_published;
        $jsst_query .= $jsst_inquery." ORDER BY field ";
        $jsst_fields = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_fields;
    }

    function getUserUnpublishFieldsfor($jsst_fieldfor) {
        if (!is_numeric($jsst_fieldfor))
            return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' isvisitorpublished = 0 ';
        } else {
            $jsst_published = ' published = 0 ';
        }
        $jsst_query = "SELECT field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = " . esc_sql($jsst_fieldfor) . " AND isuserfield = 1 AND " . $jsst_published;
        $jsst_fields = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_fields;
    }

    function getFieldTitleByFieldfor($jsst_fieldfor,$jsst_formid='') {
        if (!is_numeric($jsst_fieldfor))
            return false;
        if (is_admin()) {
            $jsst_published = '';
        } else if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' AND isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' AND published = 1 ';
        }
        $jsst_inquery = '';
        if (isset($jsst_formid) && $jsst_formid == 0) {
            $jsst_defaultformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $jsst_inquery = " AND multiformid = ".esc_sql($jsst_defaultformid);
        } elseif (isset($jsst_formid) && $jsst_formid != '') {
            $jsst_inquery = " AND multiformid = ".intval($jsst_formid);
        }
        $jsst_query = "SELECT field,fieldtitle FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = " . esc_sql($jsst_fieldfor) . $jsst_published;
        $jsst_query .= $jsst_inquery;
        $jsst_fields = jssupportticket::$_db->get_results($jsst_query);
        $jsst_fielddata = array();
        foreach ($jsst_fields as $jsst_value) {
            $jsst_fielddata[$jsst_value->field] = $jsst_value->fieldtitle;
        }
        return $jsst_fielddata;
    }

    function getUserFieldbyId($jsst_id,$jsst_fieldfor) {
        if ($jsst_id) {
            if (is_numeric($jsst_id) == false)
                return false;
            $jsst_query = "SELECT * FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE id = " . esc_sql($jsst_id);
            jssupportticket::$jsst_data[0]['userfield'] = jssupportticket::$_db->get_row($jsst_query);
            $jsst_params = jssupportticket::$jsst_data[0]['userfield']->userfieldparams;
            $jsst_visibleparams = jssupportticket::$jsst_data[0]['userfield']->visibleparams;
            jssupportticket::$jsst_data[0]['userfieldparams'] = !empty($jsst_params) ? json_decode($jsst_params, True) : '';
        }
        jssupportticket::$jsst_data[0]['fieldfor'] = $jsst_fieldfor;
        return;
    }
    function getFieldsForListing($jsst_fieldfor, $jsst_formid='') {
        if (is_numeric($jsst_fieldfor) == false)
            return false;
        if (is_admin()) {
            $jsst_published = '';
        } else if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' AND isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' AND published = 1 ';
        }
        $jsst_inquery = '';
        if (isset($jsst_formid) && $jsst_formid == 0) {
            $jsst_defaultformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $jsst_inquery = " AND multiformid = ".esc_sql($jsst_defaultformid);
        } elseif (isset($jsst_formid) && $jsst_formid != '') {
            $jsst_inquery = " AND multiformid = ".intval($jsst_formid);
        }
        $jsst_query = "SELECT field, showonlisting FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE showonlisting = 1 AND fieldfor =  " . esc_sql($jsst_fieldfor) . esc_sql($jsst_published);
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY ordering";
        $jsst_fields = jssupportticket::$_db->get_results($jsst_query);
        $jsst_fielddata = array();
        foreach ($jsst_fields AS $jsst_field) {
            $jsst_fielddata[$jsst_field->field] = $jsst_field->showonlisting;
        }
        return $jsst_fielddata;
    }
    function getAdminSystemFieldsForSearch() {
        
        if(in_array('multiform', jssupportticket::$_active_addons)){
            $jsst_query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f LEFT JOIN " . jssupportticket::$_db->prefix . "js_ticket_multiform m ON f.multiformid = m.id WHERE f.search_admin = 1 AND f.published = 1 AND (f.isuserfield IS NULL OR f.isuserfield != 1) ";
            $jsst_query .= " ORDER BY m.is_default DESC, f.ordering ASC";
        } else {
            $jsst_formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $jsst_formFilter = " AND f.multiformid = " . intval($jsst_formid);
            $jsst_query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f WHERE f.search_admin = 1 AND f.published = 1 AND (f.isuserfield IS NULL OR f.isuserfield != 1) ";
            $jsst_query .= $jsst_formFilter;
            $jsst_query .= " ORDER BY f.ordering ASC";
        }
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);

        $jsst_fielddata = array();
        foreach ($jsst_results as $jsst_row) {
            // Only set the field once to prioritize the first (default) occurrence
            if (!isset($jsst_fielddata[$jsst_row->field])) {
                $jsst_fielddata[$jsst_row->field] = $jsst_row->fieldtitle;
            }
        }
        return $jsst_fielddata;
    }
    function getUserSystemFieldsForSearch() {
                // Determine published column based on user type
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' f.isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' f.published = 1 ';
        }

        if(in_array('multiform', jssupportticket::$_active_addons)){
            // Query with LEFT JOIN and ordering to prioritize default form
            $jsst_query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f LEFT JOIN " . jssupportticket::$_db->prefix . "js_ticket_multiform m ON f.multiformid = m.id WHERE f.search_user = 1 AND ".$jsst_published." AND (f.isuserfield IS NULL OR f.isuserfield != 1)";
            $jsst_query .= " ORDER BY m.is_default DESC, f.ordering ASC";
        } else {
            $jsst_formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
            $jsst_formFilter = " AND f.multiformid = " . intval($jsst_formid);
            // Query with LEFT JOIN and ordering to prioritize default form
            $jsst_query = "SELECT f.field, f.fieldtitle FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering f WHERE f.search_user = 1 AND ".$jsst_published." AND (f.isuserfield IS NULL OR f.isuserfield != 1)";
            $jsst_query .= $jsst_formFilter;
            $jsst_query .= " ORDER BY f.ordering ASC";
        }

        $jsst_results = jssupportticket::$_db->get_results($jsst_query);

        $jsst_fielddata = array();
        foreach ($jsst_results as $jsst_row) {
            // Only keep the first (preferred) version of each field
            if (!isset($jsst_fielddata[$jsst_row->field])) {
                $jsst_fielddata[$jsst_row->field] = $jsst_row->fieldtitle;
            }
        }

        return $jsst_fielddata;
    }
    function getPublishedFieldsForTicketDetail($jsst_formid='') {
        if(!isset($jsst_formid) || $jsst_formid==''){
            $jsst_formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        if(!is_numeric($jsst_formid)) return false;
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' published = 1 ';
        }
        $jsst_query = "SELECT field, showonlisting FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE ".$jsst_published." AND fieldfor = 1 AND multiformid =  " . intval($jsst_formid) ;
        $jsst_fields = jssupportticket::$_db->get_results($jsst_query);
        $jsst_fielddata = array();
        foreach ($jsst_fields AS $jsst_field) {
            $jsst_fielddata[$jsst_field->field] = $jsst_field->showonlisting;
        }
        return $jsst_fielddata;
    }

    function DataForDepandantField(){
        $jsst_childfield = JSSTrequest::getVar('child');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'data-for-depandant-field-'.$jsst_childfield) ) {
            die( 'Security check Failed' );
        }
        $jsst_val = JSSTrequest::getVar('fvalue');
        $jsst_query = "SELECT userfieldparams,fieldtitle,depandant_field,field FROM `".jssupportticket::$_db->prefix."js_ticket_fieldsordering` WHERE field = '".esc_sql($jsst_childfield)."'";
        $jsst_data = jssupportticket::$_db->get_row($jsst_query);
        $jsst_decoded_data = json_decode($jsst_data->userfieldparams);
        $jsst_comboOptions = array();
        $jsst_flag = 0;
        foreach ($jsst_decoded_data as $jsst_key => $jsst_value) {
            $jsst_key = html_entity_decode($jsst_key);
            if($jsst_key==$jsst_val){
               for ($jsst_i=0; $jsst_i <count($jsst_value) ; $jsst_i++) {
                   $jsst_comboOptions[] = (object)array('id' => $jsst_value[$jsst_i], 'text' => $jsst_value[$jsst_i]);
                   $jsst_flag = 1;
               }
            }
        }
        $jsst_jsFunction = '';
        if ($jsst_data->depandant_field != null) {
            $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_data->depandant_field);
            $jsst_jsFunction = "getDataForDepandantField('".$jsst_wpnonce."','" . $jsst_data->field . "','" . $jsst_data->depandant_field . "',1);";
        }
        $jsst_textvar =  ($jsst_flag == 1) ? esc_html(__('Select', 'js-support-ticket')).' '.esc_html($jsst_data->fieldtitle) : '';
        $jsst_html = JSSTformfield::select($jsst_childfield, $jsst_comboOptions, '',$jsst_textvar, array('data-validation' => '','class' => 'inputbox one js-form-select-field js-ticket-custom-select', 'onchange' => $jsst_jsFunction));
        $jsst_html = jssupportticketphplib::JSST_htmlentities($jsst_html);
        $jsst_phtml = wp_json_encode($jsst_html);
        return $jsst_phtml;
    }

    function sanitize_custom_field($jsst_arg) {
        if (is_array($jsst_arg)) {
            // foreach($jsst_arg as $jsst_ikey){
            return array_map(array($this,'sanitize_custom_field'), $jsst_arg);
            // }
        }
        return jssupportticketphplib::JSST_htmlentities($jsst_arg, ENT_QUOTES, 'UTF-8');
    }

    function getDataForVisibleField($jsst_field) {
        $jsst_field = esc_sql($jsst_field);
        $jsst_field_array = jssupportticketphplib::JSST_str_replace(",", "','", $jsst_field);

        $jsst_query = "SELECT field, visibleparams FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE field IN ('" . $jsst_field_array . "')";
        $jsst_fields = jssupportticket::$_db->get_results($jsst_query);

        $jsst_data = array();

        if (!empty($jsst_fields)) {
            foreach ($jsst_fields as $jsst_item) {
                $jsst_fieldname = $jsst_item->field;

                $jsst_decoded = json_decode($jsst_item->visibleparams);

                // Initialize array for this field if not set
                if (!isset($jsst_data[$jsst_fieldname])) {
                    $jsst_data[$jsst_fieldname] = array();
                }


                if (is_array($jsst_decoded)) {
                    // New system: multiple AND/OR groups
                    foreach ($jsst_decoded as $jsst_group) {
                        if (isset($jsst_group) && is_array($jsst_group)) {
                            foreach ($jsst_group as $jsst_d) {
                                $jsst_d->visibleParentField = self::getChildForVisibleField($jsst_d->visibleParentField);
                            }
                            $jsst_data[$jsst_fieldname][] = $jsst_group; // Save group
                        } else {
                            // fallback
                            $jsst_group->visibleParentField = self::getChildForVisibleField($jsst_group->visibleParentField);
                            $jsst_data[$jsst_fieldname][] = $jsst_group;
                        }
                    }
                } elseif (is_object($jsst_decoded)) {
                    // Old system: simple condition
                    $jsst_decoded->visibleParentField = self::getChildForVisibleField($jsst_decoded->visibleParentField);
                    $jsst_data[$jsst_fieldname][] = $jsst_decoded;
                }
            }
        }

        return $jsst_data;
    }

    function getDataForVisibleField01($jsst_field) {
        $jsst_field = esc_sql($jsst_field);
        $jsst_field_array = jssupportticketphplib::JSST_str_replace(",", "','", $jsst_field);

        $jsst_query = "SELECT field, visibleparams FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE field IN ('" . $jsst_field_array . "')";
        $jsst_fields = jssupportticket::$_db->get_results($jsst_query);

        $jsst_data = array();

        if (!empty($jsst_fields)) {
            foreach ($jsst_fields as $jsst_item) {
                $jsst_fieldname = $jsst_item->field;
                $jsst_decoded = json_decode($jsst_item->visibleparams);

                // Initialize array for this field if not set
                if (!isset($jsst_data[$jsst_fieldname])) {
                    $jsst_data[$jsst_fieldname] = array();
                }

                if (is_array($jsst_decoded)) {
                    // New case: multiple conditions
                    foreach ($jsst_decoded as $jsst_d) {
                        $jsst_d->visibleParentField = self::getChildForVisibleField($jsst_d->visibleParentField);
                        $jsst_data[$jsst_fieldname][] = $jsst_d;
                    }
                } elseif (is_object($jsst_decoded)) {
                    // Old case: single condition
                    $jsst_decoded->visibleParentField = self::getChildForVisibleField($jsst_decoded->visibleParentField);
                    $jsst_data[$jsst_fieldname][] = $jsst_decoded;
                }
            }
        }

        return $jsst_data;
    }

    static function getChildForVisibleField($jsst_field) {
		$jsst_field = esc_sql($jsst_field);
        $jsst_oldField = jssupportticketphplib::JSST_explode(',',$jsst_field);
        $jsst_newField = $jsst_oldField[sizeof($jsst_oldField) - 1];
        $jsst_query = "SELECT visible_field FROM ". jssupportticket::$_db->prefix ."js_ticket_fieldsordering WHERE  field = '". $jsst_newField ."'";
        $jsst_queryRun = jssupportticket::$_db->get_var($jsst_query);
        if (isset($jsst_queryRun) && $jsst_queryRun != '') {
            $jsst_data = jssupportticketphplib::JSST_explode(',',$jsst_queryRun);
            foreach ($jsst_data as $jsst_value) {
                $jsst_field = $jsst_field.','.$jsst_value;
                $jsst_field = Self::getChildForVisibleField($jsst_field);
            }
        }        
        return $jsst_field;
    }    

    function getHtmlForORRow() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-html-for-or-row') ) {
            die( 'Security check Failed' );
        }
        
        $jsst_orid = JSSTrequest::getVar("nextorid");
        $jsst_fieldfor = JSSTrequest::getVar("fieldfor");
        $jsst_formid = JSSTrequest::getVar("formid");
        $jsst_field = JSSTrequest::getVar("field");
        $jsst_id = JSSTrequest::getVar("id");
        $jsst_equalnotequal = array(
            (object) array('id' => 1, 'text' => esc_html(__('Equal', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('Not Equal', 'js-support-ticket'))));
        $jsst_html = "
        <div id='js_or_row_". $jsst_orid ."'>
            <div class='js-form-visible-subheading'>
                ". esc_html(__('OR', 'js-support-ticket')) ."
            </div>
            <div class='js-form-value'>
                ". wp_kses(JSSTformfield::hidden('visibleLogic[]', 'OR'), JSST_ALLOWED_TAGS) ."
                ". wp_kses(JSSTformfield::select('visibleParent[]', JSSTincluder::getJSModel('fieldordering')->getFieldsForVisibleCombobox($jsst_fieldfor, $jsst_formid,$jsst_field,$jsst_id), '', esc_html(__('Select Parent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field js-form-input-field-visible', 'onchange' => 'getChildForVisibleCombobox(this.value, '.$jsst_orid.');getConditionsForVisibleCombobox(this.value, '.$jsst_orid.');')), JSST_ALLOWED_TAGS) ."
                <span class='visibleValueWrp'>
                    ". wp_kses(JSSTformfield::select('visibleValue[]', '', '', esc_html(__('Select Child', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                </span>
                <span class='visibleConditionWrp'>
                    ". wp_kses(JSSTformfield::select('visibleCondition[]', $jsst_equalnotequal, '', esc_html(__('Select Condition', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                </span>
                <div class='js-visible-conditions-body-row'>
                    <div class='js-visible-conditions-body-value'>
                        <span onclick=\"deleteOrRow('js_or_row_". $jsst_orid ."')\" class='js-visible-conditions-delbtn'>
                            <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete-2.png' />
                        </span>
                    </div>
                </div>
            </div>
        </div>
        ";
        $jsst_html = jssupportticketphplib::JSST_htmlentities($jsst_html);
        return wp_json_encode($jsst_html);
    }

    function getHtmlForANDRow() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-html-for-and-row') ) {
            die( 'Security check Failed' );
        }
        
        $jsst_andid = JSSTrequest::getVar("nextandid");
        $jsst_orid = JSSTrequest::getVar("nextorid");
        $jsst_fieldfor = JSSTrequest::getVar("fieldfor");
        $jsst_formid = JSSTrequest::getVar("formid");
        $jsst_field = JSSTrequest::getVar("field");
        $jsst_id = JSSTrequest::getVar("id");
        $jsst_equalnotequal = array(
            (object) array('id' => 1, 'text' => esc_html(__('Equal', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('Not Equal', 'js-support-ticket'))));

        $jsst_html = "
        <div class='js-form-visible-andwrp' id='js_and_row_". $jsst_andid ."'>
            <div class='js-form-visible-subheading'>
                ". esc_html(__('AND', 'js-support-ticket')) ."
            </div>
            <div class='js-form-wrapper js-form-visible-wrapper' >
                <div class='js-form-value' id='js_or_row_". $jsst_orid ."'>
                    ". wp_kses(JSSTformfield::hidden('visibleLogic[]', 'AND'), JSST_ALLOWED_TAGS) ."
                    ". wp_kses(JSSTformfield::select('visibleParent[]', JSSTincluder::getJSModel('fieldordering')->getFieldsForVisibleCombobox($jsst_fieldfor, $jsst_formid,$jsst_field,$jsst_id), '', esc_html(__('Select Parent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field js-form-input-field-visible', 'onchange' => 'getChildForVisibleCombobox(this.value, '.$jsst_orid.');getConditionsForVisibleCombobox(this.value, '.$jsst_orid.');')), JSST_ALLOWED_TAGS) ."
                    <span class='visibleValueWrp'>
                        ". wp_kses(JSSTformfield::select('visibleValue[]', '', '', esc_html(__('Select Child', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                    </span>
                    <span class='visibleConditionWrp'>
                        ". wp_kses(JSSTformfield::select('visibleCondition[]', $jsst_equalnotequal, '', esc_html(__('Select Condition', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS) ."
                    </span>
                    <div class='js-visible-conditions-body-row'>
                        <div class='js-visible-conditions-body-value'>
                            <span onclick=\"deleteOrRow('js_or_row_". $jsst_orid ."')\" class='js-visible-conditions-delbtn'>
                                <img class='input-field-remove-img' src='" . JSST_PLUGIN_URL . "includes/images/delete-2.png' />
                            </span>
                        </div>
                    </div>
                </div>
                <div class='js-form-visible-or-row'></div>
                <div class='js-visible-conditions-addbtn-wrp'>
                    <span class='js-form-visible-addmore' onclick='getMoreORRow(this, ". esc_js($jsst_fieldfor) .", ". esc_js($jsst_formid) .")'>
                        <img alt='". esc_html(__('OR', 'js-support-ticket')) ."' class='input-field-remove-img' src='". esc_url(JSST_PLUGIN_URL) ."includes/images/plus-icon.png'>
                        ". esc_html(__('OR', 'js-support-ticket')) ."
                    </span>
                </div>
            </div>
        </div>
        ";
        $jsst_html = jssupportticketphplib::JSST_htmlentities($jsst_html);
        return wp_json_encode($jsst_html);
    }

}

?>
