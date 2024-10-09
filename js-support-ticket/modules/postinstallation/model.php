<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTPostinstallationModel {

    function updateInstallationStatusConfiguration(){
            $flag = get_option('jssupport_post_installation');
            if($flag == false){
                add_option( 'jssupport_post_installation', '1', '', 'yes' );
            }else{
                update_option( 'jssupport_post_installation', '1');
            }
    }

	function storeConfigurations($data){
        if (empty($data))
            return false;
        $error = false;
        unset($data['action']);
        unset($data['form_request']);
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        foreach ($data as $key => $value) {
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_config` SET `configvalue` = '" . esc_sql($value) . "' WHERE `configname`= '" . esc_sql($key) . "'";
            jssupportticket::$_db->query($query);
            if(jssupportticket::$_db->last_error == null){
                $status = 0;
            }else{
                $status = 1;
            }
        }
        if ($status == 0) {
            JSSTmessage::setMessage(esc_html(__('Configuration','js-support-ticket')).' '. esc_html(__('has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Configuration','js-support-ticket')).' '. esc_html(__('has not been changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    function getConfigurationValues() {
        $this->updateInstallationStatusConfiguration();
        $query = "SELECT configname,configvalue
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` ";//WHERE configfor != 'ticketviaemail'";
        $data = jssupportticket::$_db->get_results($query);
        
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        foreach ($data AS $config) {
            jssupportticket::$_data[0][$config->configname] = $config->configvalue;
        }
        return;
    }


    function getPageList() {
        $query = "SELECT ID AS id, post_title AS text FROM `" . jssupportticket::$_db->prefix . "posts` WHERE post_type = 'page' AND post_status = 'publish' ";
        $pages = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $pages;
    }

}?>