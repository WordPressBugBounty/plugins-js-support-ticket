<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailtemplateModel {

    function getTemplate($jsst_tempfor, $jsst_formid, $jsst_langcode) {
        switch ($jsst_tempfor) {
            case 'tk-nw' : $jsst_tempatefor = 'ticket-new';
                break;
            case 'sntk-tk' : $jsst_tempatefor = 'ticket-staff';
                break;
            case 'ew-md' : $jsst_tempatefor = 'department-new';
                break;
            case 'ew-gr' : $jsst_tempatefor = 'group-new';
                break;
            case 'ew-sm' : $jsst_tempatefor = 'staff-new';
                break;
            case 'ew-ht' : $jsst_tempatefor = 'helptopic-new';
                break;
            case 'rs-tk' : $jsst_tempatefor = 'reassign-tk';
                break;
            case 'cl-tk' : $jsst_tempatefor = 'close-tk';
                break;
            case 'dl-tk' : $jsst_tempatefor = 'delete-tk';
                break;
            case 'mo-tk' : $jsst_tempatefor = 'moverdue-tk';
                break;
            case 'be-tk' : $jsst_tempatefor = 'banemail-tk';
                break;
            case 'be-trtk' : $jsst_tempatefor = 'banemail-trtk';
                break;
            case 'dt-tk' : $jsst_tempatefor = 'deptrans-tk';
                break;
            case 'ebct-tk' : $jsst_tempatefor = 'banemailcloseticket-tk';
                break;
            case 'ube-tk' : $jsst_tempatefor = 'unbanemail-tk';
                break;
            case 'rsp-tk' : $jsst_tempatefor = 'responce-tk';
                break;
            case 'rpy-tk' : $jsst_tempatefor = 'reply-tk';
                break;
            case 'tk-ew-ad' : $jsst_tempatefor = 'ticket-new-admin';
                break;
            case 'lk-tk' : $jsst_tempatefor = 'lock-tk';
                break;
            case 'ulk-tk' : $jsst_tempatefor = 'unlock-tk';
                break;
            case 'minp-tk' : $jsst_tempatefor = 'minprogress-tk';
                break;
            case 'pc-tk' : $jsst_tempatefor = 'prtrans-tk';
                break;
            case 'ml-ew' : $jsst_tempatefor = 'mail-new';
                break;
            case 'ml-rp' : $jsst_tempatefor = 'mail-rpy';
                break;
            case 'fd-bk' : $jsst_tempatefor = 'mail-feedback';
                break;
            case 'no-rp' : $jsst_tempatefor = 'mail-rpy-closed';
                break;
            case 'del-data' : $jsst_tempatefor = 'delete-user-data';
                break;
            default: $jsst_tempatefor = 'ticket-new';
                break;
        }
        if (!empty($jsst_langcode)) {
            $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_multilanguageemailtemplates` WHERE templatefor = '" . esc_sql($jsst_tempatefor) . "' AND language_id = '" . esc_sql($jsst_langcode) . "'";
        } else {
            $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` WHERE templatefor = '" . esc_sql($jsst_tempatefor) . "'";
        }
        if (!empty($jsst_formid)) {
            $jsst_query .= " AND multiformid = " . esc_sql($jsst_formid);
        } else {
            $jsst_query .= " AND (multiformid IS NULL OR multiformid = '')";
        }
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row(($jsst_query));
        $jsst_multiformname = '';
        if(in_array('multiform', jssupportticket::$_active_addons) && !empty(jssupportticket::$jsst_data[0]->multiformid)){
            $jsst_query = "SELECT title
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_multiform` WHERE id = ".esc_sql(jssupportticket::$jsst_data[0]->multiformid);
            $jsst_multiformname = jssupportticket::$_db->get_var($jsst_query);
        }
        jssupportticket::$jsst_data[0]->multiformname = $jsst_multiformname;

        do_action('jssupportticket_load_wp_translation_install');
        $jsst_translations = wp_get_available_translations();
        $jsst_installed = wp_get_installed_translations('core');

        $jsst_language_name = '';
        if(in_array('multilanguageemailtemplates', jssupportticket::$_active_addons) && !empty(jssupportticket::$jsst_data[0]->language_id)){
            $jsst_language_name = isset($jsst_translations[jssupportticket::$jsst_data[0]->language_id]['english_name']) ? $jsst_translations[jssupportticket::$jsst_data[0]->language_id]['english_name'] : ucfirst(str_replace('_', '-', jssupportticket::$jsst_data[0]->language_id));
        }
        jssupportticket::$jsst_data[0]->language_name = $jsst_language_name;
        
        if (in_array('multiform', jssupportticket::$_active_addons) || in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
            
            $jsst_query = '';
            if(in_array('multiform', jssupportticket::$_active_addons)){
                $jsst_query = "
                    (
                        SELECT
                            tmpl.multiformid AS formid,
                            form.title AS formname,
                            department.departmentname,
                            tmpl.id AS template_id,
                            NULL AS language,
                            'main' AS source
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` AS tmpl
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_multiform` AS form
                            ON tmpl.multiformid = form.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                            ON form.departmentid = department.id
                        WHERE tmpl.templatefor = '" . esc_sql($jsst_tempatefor) . "'
                    )
                ";
            }
            if (in_array('multiform', jssupportticket::$_active_addons) && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                $jsst_query .= " UNION ALL ";
            }

            if (in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                if (in_array('multiform', jssupportticket::$_active_addons)) {
                    $jsst_query .= "
                        (
                            SELECT
                                ltmpl.multiformid AS formid,
                                form.title AS formname,
                                department.departmentname,
                                ltmpl.id AS template_id,
                                ltmpl.language_id AS language,
                                'multi' AS source
                            FROM `" . jssupportticket::$_db->prefix . "js_ticket_multilanguageemailtemplates` AS ltmpl
                            LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_multiform` AS form
                                ON ltmpl.multiformid = form.id
                            LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                                ON form.departmentid = department.id
                            WHERE ltmpl.templatefor = '" . esc_sql($jsst_tempatefor) . "'
                        )
                    ";
                } else {
                    $jsst_query .= "
                        (
                            SELECT
                                ltmpl.multiformid AS formid,
                                ltmpl.id AS template_id,
                                ltmpl.language_id AS language,
                                'multi' AS source
                            FROM `" . jssupportticket::$_db->prefix . "js_ticket_multilanguageemailtemplates` AS ltmpl
                            WHERE ltmpl.templatefor = '" . esc_sql($jsst_tempatefor) . "'
                        )
                    ";
                }
            }

            $jsst_list = jssupportticket::$_db->get_results($jsst_query);

            $jsst_langLookup = [];

            if (!empty($jsst_installed['default'])) {
                foreach ($jsst_installed['default'] as $jsst_code => $jsst_value) {
                    $jsst_langLookup[$jsst_code] = isset($jsst_translations[$jsst_code]['english_name']) 
                        ? $jsst_translations[$jsst_code]['english_name'] 
                        : ucfirst(str_replace('_', '-', $jsst_code));
                }
            }

            // Now enrich $jsst_list with language names
            foreach ($jsst_list as $jsst_key => &$jsst_item) {
                if (empty($jsst_item->formname) && empty($jsst_item->language)) {
                    unset($jsst_list[$jsst_key]); // This removes the item from the array
                    continue;
                }

                if (!empty($jsst_item->language)) {
                    $jsst_item->language_name = isset($jsst_langLookup[$jsst_item->language])
                        ? $jsst_langLookup[$jsst_item->language]
                        : ucfirst(str_replace('_', '-', $jsst_item->language));
                } else {
                    $jsst_item->language_name = ''; // or 'Default'
                }
            }

            jssupportticket::$jsst_data[0]->multiTemplates = $jsst_list;
        }

        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        jssupportticket::$jsst_data[2] = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1, $jsst_formid);
        return ;
    }

    //For the Email template
    function storeEmailTemplate($jsst_data) {
        $jsst_data['title'] = isset($jsst_data['title']) ? $jsst_data['title'] : '';
        $jsst_data['status'] = isset($jsst_data['status']) ? $jsst_data['status'] : 1;

        $jsst_row = JSSTincluder::getJSTable('emailtemplates');

        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }
        if ($jsst_error == 0) {
            JSSTmessage::setMessage(esc_html(__('Email template has been stored', 'js-support-ticket')), 'updated');
            if(isset($jsst_data['multiformid']) && empty($jsst_data['multiformid'])) {
                $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` SET multiformid = NULL WHERE multiformid = '0' AND id = ".$jsst_row->id;
                jssupportticket::$_db->query($jsst_query);
            }
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Email template has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    function getDefaultEmailTemplate() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'list-email-template') ) {
            die( 'Security check Failed' );
        }
        $jsst_templatefor = JSSTrequest::getVar('templatefor');
        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` WHERE templatefor = '" . esc_sql($jsst_templatefor) . "'";
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
        $jsst_data =  array('defaultsubject'=>htmlentities($jsst_result->subject),'defaultbody'=>htmlentities($jsst_result->body) , 'defaultid'=>htmlentities($jsst_result->id));
        return wp_json_encode($jsst_data);

    }

    function removeFormEmailTemplate($jsst_id, $jsst_source) {
        if (!is_numeric($jsst_id))
            return false;
        
        if ($jsst_source == 'multi' && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
            $jsst_row = JSSTincluder::getJSTable('multilanguageemailtemplates');
        } else {
            $jsst_row = JSSTincluder::getJSTable('emailtemplates');
        }
        if ($jsst_row->delete($jsst_id)) {
            JSSTmessage::setMessage(esc_html(__('Email tempate has been deleted', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Email tempate has not been deleted', 'js-support-ticket')), 'error');
        }

        return;
    }

}

?>
