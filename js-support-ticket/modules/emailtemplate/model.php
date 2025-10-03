<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailtemplateModel {

    function getTemplate($tempfor, $formid, $langcode) {
        switch ($tempfor) {
            case 'tk-nw' : $tempatefor = 'ticket-new';
                break;
            case 'sntk-tk' : $tempatefor = 'ticket-staff';
                break;
            case 'ew-md' : $tempatefor = 'department-new';
                break;
            case 'ew-gr' : $tempatefor = 'group-new';
                break;
            case 'ew-sm' : $tempatefor = 'staff-new';
                break;
            case 'ew-ht' : $tempatefor = 'helptopic-new';
                break;
            case 'rs-tk' : $tempatefor = 'reassign-tk';
                break;
            case 'cl-tk' : $tempatefor = 'close-tk';
                break;
            case 'dl-tk' : $tempatefor = 'delete-tk';
                break;
            case 'mo-tk' : $tempatefor = 'moverdue-tk';
                break;
            case 'be-tk' : $tempatefor = 'banemail-tk';
                break;
            case 'be-trtk' : $tempatefor = 'banemail-trtk';
                break;
            case 'dt-tk' : $tempatefor = 'deptrans-tk';
                break;
            case 'ebct-tk' : $tempatefor = 'banemailcloseticket-tk';
                break;
            case 'ube-tk' : $tempatefor = 'unbanemail-tk';
                break;
            case 'rsp-tk' : $tempatefor = 'responce-tk';
                break;
            case 'rpy-tk' : $tempatefor = 'reply-tk';
                break;
            case 'tk-ew-ad' : $tempatefor = 'ticket-new-admin';
                break;
            case 'lk-tk' : $tempatefor = 'lock-tk';
                break;
            case 'ulk-tk' : $tempatefor = 'unlock-tk';
                break;
            case 'minp-tk' : $tempatefor = 'minprogress-tk';
                break;
            case 'pc-tk' : $tempatefor = 'prtrans-tk';
                break;
            case 'ml-ew' : $tempatefor = 'mail-new';
                break;
            case 'ml-rp' : $tempatefor = 'mail-rpy';
                break;
            case 'fd-bk' : $tempatefor = 'mail-feedback';
                break;
            case 'no-rp' : $tempatefor = 'mail-rpy-closed';
                break;
            case 'del-data' : $tempatefor = 'delete-user-data';
                break;
            default: $tempatefor = 'ticket-new';
                break;
        }
        if (!empty($langcode)) {
            $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_multilanguageemailtemplates` WHERE templatefor = '" . esc_sql($tempatefor) . "' AND language_id = '" . esc_sql($langcode) . "'";
        } else {
            $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` WHERE templatefor = '" . esc_sql($tempatefor) . "'";
        }
        if (!empty($formid)) {
            $query .= " AND multiformid = " . esc_sql($formid);
        } else {
            $query .= " AND (multiformid IS NULL OR multiformid = '')";
        }
        jssupportticket::$_data[0] = jssupportticket::$_db->get_row(($query));
        $multiformname = '';
        if(in_array('multiform', jssupportticket::$_active_addons) && !empty(jssupportticket::$_data[0]->multiformid)){
            $query = "SELECT title
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_multiform` WHERE id = ".esc_sql(jssupportticket::$_data[0]->multiformid);
            $multiformname = jssupportticket::$_db->get_var($query);
        }
        jssupportticket::$_data[0]->multiformname = $multiformname;

        do_action('jssupportticket_load_wp_translation_install');
        $translations = wp_get_available_translations();
        $installed = wp_get_installed_translations('core');

        $language_name = '';
        if(in_array('multilanguageemailtemplates', jssupportticket::$_active_addons) && !empty(jssupportticket::$_data[0]->language_id)){
            $language_name = isset($translations[jssupportticket::$_data[0]->language_id]['english_name']) ? $translations[jssupportticket::$_data[0]->language_id]['english_name'] : ucfirst(str_replace('_', '-', jssupportticket::$_data[0]->language_id));
        }
        jssupportticket::$_data[0]->language_name = $language_name;
        
        if (in_array('multiform', jssupportticket::$_active_addons) || in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
            
            $query = '';
            if(in_array('multiform', jssupportticket::$_active_addons)){
                $query = "
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
                        WHERE tmpl.templatefor = '" . esc_sql($tempatefor) . "'
                    )
                ";
            }
            if (in_array('multiform', jssupportticket::$_active_addons) && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                $query .= " UNION ALL ";
            }

            if (in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                if (in_array('multiform', jssupportticket::$_active_addons)) {
                    $query .= "
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
                            WHERE ltmpl.templatefor = '" . esc_sql($tempatefor) . "'
                        )
                    ";
                } else {
                    $query .= "
                        (
                            SELECT
                                ltmpl.multiformid AS formid,
                                ltmpl.id AS template_id,
                                ltmpl.language_id AS language,
                                'multi' AS source
                            FROM `" . jssupportticket::$_db->prefix . "js_ticket_multilanguageemailtemplates` AS ltmpl
                            WHERE ltmpl.templatefor = '" . esc_sql($tempatefor) . "'
                        )
                    ";
                }
            }

            $list = jssupportticket::$_db->get_results($query);

            $langLookup = [];

            if (!empty($installed['default'])) {
                foreach ($installed['default'] as $code => $value) {
                    $langLookup[$code] = isset($translations[$code]['english_name']) 
                        ? $translations[$code]['english_name'] 
                        : ucfirst(str_replace('_', '-', $code));
                }
            }

            // Now enrich $list with language names
            foreach ($list as $key => &$item) {
                if (empty($item->formname) && empty($item->language)) {
                    unset($list[$key]); // This removes the item from the array
                    continue;
                }

                if (!empty($item->language)) {
                    $item->language_name = isset($langLookup[$item->language])
                        ? $langLookup[$item->language]
                        : ucfirst(str_replace('_', '-', $item->language));
                } else {
                    $item->language_name = ''; // or 'Default'
                }
            }

            jssupportticket::$_data[0]->multiTemplates = $list;
        }

        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        jssupportticket::$_data[2] = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1, $formid);
        return ;
    }

    //For the Email template
    function storeEmailTemplate($data) {
        $data['title'] = isset($data['title']) ? $data['title'] : '';
        $data['status'] = isset($data['status']) ? $data['status'] : 1;

        $row = JSSTincluder::getJSTable('emailtemplates');

        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }
        if ($error == 0) {
            JSSTmessage::setMessage(esc_html(__('Email template has been stored', 'js-support-ticket')), 'updated');
            if(isset($data['multiformid']) && empty($data['multiformid'])) {
                $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` SET multiformid = NULL WHERE multiformid = '0' AND id = ".$row->id;
                jssupportticket::$_db->query($query);
            }
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Email template has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    function getDefaultEmailTemplate() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'list-email-template') ) {
            die( 'Security check Failed' );
        }
        $templatefor = JSSTrequest::getVar('templatefor');
        $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` WHERE templatefor = '" . esc_sql($templatefor) . "'";
        $result = jssupportticket::$_db->get_row($query);
        $data =  array('defaultsubject'=>htmlentities($result->subject),'defaultbody'=>htmlentities($result->body) , 'defaultid'=>htmlentities($result->id));
        return wp_json_encode($data);

    }

    function removeFormEmailTemplate($id, $source) {
        if (!is_numeric($id))
            return false;
        
        if ($source == 'multi' && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
            $row = JSSTincluder::getJSTable('multilanguageemailtemplates');
        } else {
            $row = JSSTincluder::getJSTable('emailtemplates');
        }
        if ($row->delete($id)) {
            JSSTmessage::setMessage(esc_html(__('Email tempate has been deleted', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Email tempate has not been deleted', 'js-support-ticket')), 'error');
        }

        return;
    }

}

?>
