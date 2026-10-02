<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTPostinstallationModel {

    function updateInstallationStatusConfiguration(){
            $jsst_flag = get_option('jssupport_post_installation');
            if($jsst_flag == false){
                add_option( 'jssupport_post_installation', '1', '', 'yes' );
            }else{
                update_option( 'jssupport_post_installation', '1');
            }
    }

    function storeConfigurations($jsst_data){
        if (empty($jsst_data))
            return false;

        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_error = false;
        unset($jsst_data['action']);
        unset($jsst_data['form_request']);

        // Sanitize all input data
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions

        // Additional security for specific parameters
        if (isset($jsst_data['support_custom_img'])) {
            $jsst_data['support_custom_img'] = sanitize_file_name($jsst_data['support_custom_img']); // Prevent directory traversal
        }

        foreach ($jsst_data as $jsst_key => $jsst_value) {
            $jsst_query = jssupportticket::$_db->prepare("UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_config` SET `configvalue` = %s WHERE `configname`= %s", $jsst_value, $jsst_key);
            jssupportticket::$_db->query($jsst_query);

            // Track status for error handling
            if (jssupportticket::$_db->last_error == null) {
                $jsst_status = 0;
            } else {
                $jsst_status = 1;
            }
        }

        if ($jsst_status == 0) {
            JSSTmessage::setMessage(esc_html(__('Configuration', 'js-support-ticket')) . ' ' . esc_html(__('has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Configuration', 'js-support-ticket')) . ' ' . esc_html(__('has not been changed', 'js-support-ticket')), 'error');
        }

        return;
    }

    function getConfigurationValues() {
        $this->updateInstallationStatusConfiguration();
        $jsst_query = "SELECT configname,configvalue
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` ";//WHERE configfor != 'ticketviaemail'";
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        foreach ($jsst_data AS $jsst_config) {
            jssupportticket::$jsst_data[0][$jsst_config->configname] = $jsst_config->configvalue;
        }
        return;
    }


    /**
     * The quick-setup wizard's steps, in order, for this site.
     *
     * One list, so every screen numbers the steps the same way. Translation
     * appears only when a language pack is offered and Feedback only with the
     * feedback add-on - the same conditions the controller uses to skip those
     * screens, so a step shown here is never one the controller skips.
     *
     * @return array key => array('label', 'layout')
     */
    function getWizardSteps() {
        $jsst_steps = array(
            'general' => array('label' => __('General', 'js-support-ticket'), 'layout' => 'stepone'),
            'tickets' => array('label' => __('Tickets', 'js-support-ticket'), 'layout' => 'steptwo'),
        );
        if (class_exists('JSSTtranslations') && JSSTtranslations::siteLanguagePending()) {
            $jsst_steps['translation'] = array('label' => __('Translation', 'js-support-ticket'), 'layout' => 'translationoption');
        }
        if (in_array('feedback', jssupportticket::$_active_addons)) {
            $jsst_steps['feedback'] = array('label' => __('Feedback', 'js-support-ticket'), 'layout' => 'stepthree');
        }
        $jsst_steps['done'] = array('label' => __('Done', 'js-support-ticket'), 'layout' => 'settingcomplete');
        return $jsst_steps;
    }

    function getPageList() {
        $jsst_query = "SELECT ID AS id, post_title AS text FROM `" . jssupportticket::$_db->prefix . "posts` WHERE post_type = 'page' AND post_status = 'publish' ";
        $jsst_pages = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_pages;
    }

}?>
