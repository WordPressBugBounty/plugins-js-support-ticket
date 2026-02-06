<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTuploads {

    private $jsst_ticketid;
    private $jsst_articleid;
    private $jsst_downloadid;
    private $jsst_categoryid;
    private $jsst_staffid;
    private $jsst_uploadfor;

    function jssupportticket_upload_dir( $jsst_dir ) {
        $jsst_form_request = JSSTrequest::getVar('form_request');
        if($jsst_form_request == 'jssupportticket' OR $this->jsst_uploadfor == 'agent'){
            $jsst_datadirectory = jssupportticket::$_config['data_directory'];
            $jsst_path = $jsst_datadirectory . '/attachmentdata';

            $jsst_foldername = '';

            if($this->jsst_uploadfor == 'ticket'){
                if(!is_numeric($this->jsst_ticketid)) return false;
                $jsst_path = $jsst_path . '/ticket';
                $jsst_query = "SELECT attachmentdir FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = ".esc_sql($this->jsst_ticketid);
                $jsst_foldername = jssupportticket::$_db->get_var($jsst_query);
            }elseif($this->jsst_uploadfor == 'article'){
                $jsst_path = $jsst_path . '/articles/article_'.$this->jsst_articleid;
            }elseif($this->jsst_uploadfor == 'download'){
                $jsst_path = $jsst_path . '/downloads/download_'.$this->jsst_downloadid;
            }elseif($this->jsst_uploadfor == 'category'){
                $jsst_path = $jsst_datadirectory . '/knowledgebasedata/categories/category_'.$this->jsst_categoryid;
            }elseif($this->jsst_uploadfor == 'agent'){
                $jsst_path = $jsst_datadirectory . '/staffdata/staff_'.$this->jsst_staffid;
            }

            $jsst_userpath = $jsst_path . '/' . $jsst_foldername;
            $jsst_array = array(
                'path'   => $jsst_dir['basedir'] . '/' . $jsst_userpath,
                'url'    => $jsst_dir['baseurl'] . '/' . $jsst_userpath,
                'subdir' => '/'. $jsst_userpath,
            ) + $jsst_dir;
            return $jsst_array;
        }elseif($this->jsst_uploadfor == 'notificationlogo'){
            $jsst_datadirectory = jssupportticket::$_config['data_directory'];
            $jsst_path = $jsst_datadirectory;
            return $jsst_path;

        }else{
            return $jsst_dir;
        }
    }

    function storeTicketAttachment($jsst_data, $jsst_caller){
        $jsst_ticketid = $jsst_data['ticketid'];
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $this->jsst_ticketid = $jsst_ticketid;
        $this->jsst_uploadfor = 'ticket';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        if(!isset($_FILES['filename'])){
            return;
        }
        $jsst_files = filter_var_array($_FILES['filename']);

        if(!is_array($jsst_files['name'])){
            return;
        }

        foreach ($jsst_files['name'] as $jsst_key => $jsst_value) {
            if ($jsst_files['name'][$jsst_key]) {
                $jsst_file = array(
                        'name'     => $jsst_files['name'][$jsst_key],
                        'type'     => $jsst_files['type'][$jsst_key],
                        'tmp_name' => $jsst_files['tmp_name'][$jsst_key],
                        'error'    => $jsst_files['error'][$jsst_key],
                        'size'     => $jsst_files['size'][$jsst_key]
                        );
                $jsst_uploadfilesize = $jsst_file['size'] / 1024; //kb
                if($jsst_uploadfilesize > $jsst_filesize){
                    JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
                    return;
                }
                $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['filename']['name'][$jsst_key]));
                if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
                    $jsst_document_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
                    if(stristr($jsst_document_file_types, $jsst_filetyperesult['ext'])){

                        $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                        if ( $jsst_result && ! isset( $jsst_result['error'] ) ) {
                            // Get the folder where the file was uploaded
                            $jsst_file_directory = dirname($jsst_result['file']);
                            $jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
                            $jsst_replyattachmentid = isset($jsst_data['replyattachmentid']) ? $jsst_data['replyattachmentid'] : '';
                            $jsst_result = $jsst_caller->storeTicketAttachment($jsst_ticketid, $jsst_replyattachmentid, $jsst_uploadfilesize, $jsst_filename);
                        } else {
                            /**
                             * Error generated by _wp_handle_upload()
                             * @see _wp_handle_upload() in wp-admin/includes/file.php
                             */
                            JSSTmessage::setMessage($jsst_result['error'], 'error');
                        }
                    }
                }
            }
        }
        // generate index file
        if (!empty($jsst_file_directory)) {
            JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        return;
    }

    function storeTicketViaEmailAttachment($jsst_idsarray,$jsst_key,$jsst_value){
        $jsst_ticketid = $jsst_idsarray[0];
        if(!is_numeric($jsst_ticketid))
            return;
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_maindir = wp_upload_dir();
        $jsst_path = $jsst_maindir['basedir'];
        $jsst_path = $jsst_path .'/'.$jsst_datadirectory;
        if (!file_exists($jsst_path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        }
        $jsst_path = $jsst_path . '/attachmentdata';
        if (!file_exists($jsst_path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        }
        $jsst_path = $jsst_path . '/ticket';
        if (!file_exists($jsst_path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        }
        $jsst_query = "SELECT attachmentdir FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = ".esc_sql($jsst_idsarray[0]);
        $jsst_foldername = jssupportticket::$_db->get_var($jsst_query);

        $jsst_path = $jsst_path . '/' . $jsst_foldername;
        if (!file_exists($jsst_path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        }

        file_put_contents($jsst_path . '/' . $jsst_key, $jsst_value); // save the file
        return true;
    }

    function storeArticleAttachment($jsst_data, $jsst_caller){
        $jsst_id = $jsst_data['id'];
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $this->jsst_articleid = $jsst_id;
        $this->jsst_uploadfor = 'article';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        if(!isset($_FILES['filename'])){
            return;
        }
        $jsst_files = filter_var_array($_FILES['filename']);
        if(!is_array($jsst_files['name'])){
            return;
        }

        foreach ($jsst_files['name'] as $jsst_key => $jsst_value) {
            if ($jsst_files['name'][$jsst_key]) {
                $jsst_file = array(
                        'name'     => $jsst_files['name'][$jsst_key],
                        'type'     => $jsst_files['type'][$jsst_key],
                        'tmp_name' => $jsst_files['tmp_name'][$jsst_key],
                        'error'    => $jsst_files['error'][$jsst_key],
                        'size'     => $jsst_files['size'][$jsst_key]
                        );
                $jsst_uploadfilesize = $jsst_file['size'] / 1024; //kb
                if($jsst_uploadfilesize > $jsst_filesize){
                    JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
                    return;
                }

                $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['filename']['name'][$jsst_key]));
                if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
                    $jsst_document_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
                    if(stristr($jsst_document_file_types, $jsst_filetyperesult['ext'])){

                        $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                        if ( $jsst_result && ! isset( $jsst_result['error'] ) ) {
                            // Get the folder where the file was uploaded
                            $jsst_file_directory = dirname($jsst_result['file']);
                            $jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
                            $jsst_result = $jsst_caller->storeArticleAttachmet($jsst_id , $jsst_uploadfilesize, $jsst_filename);
                        } else {
                            /**
                             * Error generated by _wp_handle_upload()
                             * @see _wp_handle_upload() in wp-admin/includes/file.php
                             */
                            JSSTmessage::setMessage($jsst_result['error'], 'error');
                        }
                    }
                }
            }
        }
        // generate index file
        if (!empty($jsst_file_directory)) {
            JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        return;
    }

    function storeDownloadAttachment($jsst_data, $jsst_caller){
        $jsst_id = $jsst_data['id'];
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $this->jsst_downloadid = $jsst_id;
        $this->jsst_uploadfor = 'download';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        if(!isset($_FILES['filename'])){
            return;
        }
        $jsst_files = filter_var_array($_FILES['filename']);
        if(!is_array($jsst_files['name'])){
            return;
        }

        foreach ($jsst_files['name'] as $jsst_key => $jsst_value) {
            if ($jsst_files['name'][$jsst_key]) {
                $jsst_file = array(
                        'name'     => $jsst_files['name'][$jsst_key],
                        'type'     => $jsst_files['type'][$jsst_key],
                        'tmp_name' => $jsst_files['tmp_name'][$jsst_key],
                        'error'    => $jsst_files['error'][$jsst_key],
                        'size'     => $jsst_files['size'][$jsst_key]
                        );
                $jsst_uploadfilesize = $jsst_file['size'] / 1024; //kb
                if($jsst_uploadfilesize > $jsst_filesize){
                    JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
                    return;
                }
                $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['filename']['name'][$jsst_key]));
                if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
                    $jsst_document_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
                    if(stristr($jsst_document_file_types, $jsst_filetyperesult['ext'])){
                        $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                        if ( $jsst_result && ! isset( $jsst_result['error'] ) ) {
                            // Get the folder where the file was uploaded
                            $jsst_file_directory = dirname($jsst_result['file']);
                            $jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
                            $jsst_result = $jsst_caller->storeDownloadAttachment($jsst_id , $jsst_uploadfilesize, $jsst_filename);
                        } else {
                            /**
                             * Error generated by _wp_handle_upload()
                             * @see _wp_handle_upload() in wp-admin/includes/file.php
                             */
                            JSSTmessage::setMessage($jsst_result['error'], 'error');
                        }
                    }
                }
            }
        }
        // generate index file
        if (!empty($jsst_file_directory)) {
            JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        return;
    }

    function uploadCategoryLogo($jsst_id , $jsst_caller){

        if(!is_numeric($jsst_id))
            return false;
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $this->jsst_categoryid = $jsst_id;
        $this->jsst_uploadfor = 'category';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        $jsst_file = array(
            'name'     => sanitize_file_name($_FILES['filename']['name']),
            'type'     => jssupportticket::JSST_sanitizeData($_FILES['filename']['type']),
            'tmp_name' => jssupportticket::JSST_sanitizeData($_FILES['filename']['tmp_name']),
            'error'    => jssupportticket::JSST_sanitizeData($_FILES['filename']['error']),
            'size'     => jssupportticket::JSST_sanitizeData($_FILES['filename']['size']),
        ); // JSST_sanitizeData() function uses wordpress santize functions
        $jsst_uploadfilesize = $jsst_file['size'] / 1024; //kb
        if($jsst_uploadfilesize > $jsst_filesize){
            JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
            return;
        }

        $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['filename']['name']));
        if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
            $jsst_image_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');

            if(stristr($jsst_image_file_types, $jsst_filetyperesult['ext'])){

                $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                if ( $jsst_result && ! isset( $jsst_result['error'] ) ) {
                    // Get the folder where the file was uploaded
                    $jsst_file_directory = dirname($jsst_result['file']);
                    $jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
                    $jsst_result = $jsst_caller->storeCategoryLogo($jsst_id , $jsst_filename);
                    // generate index file
                    JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
                } else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    JSSTmessage::setMessage($jsst_result['error'], 'error');
                }
            }
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        return;
    }

    function uploadStaffLogo($jsst_id , $jsst_caller){
        if(!is_numeric($jsst_id))
            return false;
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $this->jsst_staffid = $jsst_id;
        $this->jsst_uploadfor = 'agent';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        $jsst_file = array(
            'name'     => sanitize_file_name($_FILES['filename']['name']),
            'type'     => jssupportticket::JSST_sanitizeData($_FILES['filename']['type']),
            'tmp_name' => jssupportticket::JSST_sanitizeData($_FILES['filename']['tmp_name']),
            'error'    => jssupportticket::JSST_sanitizeData($_FILES['filename']['error']),
            'size'     => jssupportticket::JSST_sanitizeData($_FILES['filename']['size']),
        ); // JSST_sanitizeData() function uses wordpress santize functions
        $jsst_uploadfilesize = $jsst_file['size'] / 1024; //kb
        if($jsst_uploadfilesize > $jsst_filesize){
            JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['filename']['name']));
        if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
            $jsst_image_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
            if(stristr($jsst_image_file_types, $jsst_filetyperesult['ext'])){

                $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                if ( $jsst_result && ! isset( $jsst_result['error'] ) ) {
                    // Get the folder where the file was uploaded
                    $jsst_file_directory = dirname($jsst_result['file']);
                    $jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
                    $jsst_result = $jsst_caller->storeStaffLogo($jsst_id , $jsst_filename);
                    // generate index file
                    JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
                } else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    JSSTmessage::setMessage($jsst_result['error'], 'error');
                }
            }
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        return;
    }

    function storeTicketCustomUploadFile($jsst_id, $jsst_field){
        if(!isset($_FILES[$jsst_field])){
            return;
        }
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        $this->jsst_ticketid = $jsst_id;
        $this->jsst_uploadfor = 'ticket';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        $jsst_file = array(
            'name'     => sanitize_file_name($_FILES[$jsst_field]['name']),
            'type'     => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['type']),
            'tmp_name' => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['tmp_name']),
            'error'    => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['error']),
            'size'     => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['size'])
        ); // JSST_sanitizeData() function uses wordpress santize functions
        $jsst_uploadfilesize = jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['size']) / 1024; //kb // JSST_sanitizeData() function uses wordpress santize functions
        if($jsst_uploadfilesize > $jsst_filesize){
            JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES[$jsst_field]['name']));
        if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
            $jsst_image_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
            if(strstr($jsst_image_file_types, $jsst_filetyperesult['ext'])){

                $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                if (isset( $jsst_result['error'] ) ) {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    JSSTmessage::setMessage($jsst_result['error'], 'error');
                }else{
                    $jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
                    // Get the folder where the file was uploaded
                    $jsst_file_directory = dirname($jsst_result['file']);
                    // generate index file
                    JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
                }
            }
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        //to store name of custom file in params
        JSSTincluder::getJSModel('ticket')->storeUploadFieldValueInParams($jsst_id,$jsst_filename,$jsst_field);
        return;
    }

	function uploadInternalNoteAttachment($jsst_id,$jsst_field){
        if(!isset($_FILES[$jsst_field])){
            return;
        }
        $jsst_filename = '';
        $jsst_filesize = '';
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        $this->jsst_ticketid = $jsst_id;
        $this->jsst_uploadfor = 'ticket';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        $jsst_file = array(
            'name'     => sanitize_file_name($_FILES[$jsst_field]['name']),
            'type'     => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['type']),
            'tmp_name' => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['tmp_name']),
            'error'    => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['error']),
            'size'     => jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['size'])
        ); // JSST_sanitizeData() function uses wordpress santize functions
        $jsst_uploadfilesize = jssupportticket::JSST_sanitizeData($_FILES[$jsst_field]['size']) / 1024; //kb // JSST_sanitizeData() function uses wordpress santize functions
        if($jsst_uploadfilesize > $jsst_filesize){
            JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES[$jsst_field]['name']));
        if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
            $jsst_image_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
            if(strstr($jsst_image_file_types, $jsst_filetyperesult['ext'])){

                $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                if (isset( $jsst_result['error'] ) ) {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    JSSTmessage::setMessage($jsst_result['error'], 'error');
                }else{
					$jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
					$jsst_filesize = $jsst_file['size'];
                    // Get the folder where the file was uploaded
                    $jsst_file_directory = dirname($jsst_result['file']);
                    // generate index file
                    JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
				}
            }
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
		if($jsst_filename != '' && $jsst_filesize != ''){
			$jsst_array = array('filename' => $jsst_filename, 'filesize' => $jsst_filesize);
			return $jsst_array;
		}else{
			return false;
		}
	}

    function uploadDesktopNotificationLogo(){
        $jsst_filesize = jssupportticket::$_config['file_maximum_size'];
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $this->jsst_uploadfor = 'notificationlogo';
        // Register our path override.
        add_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
        $jsst_result = array();
        $jsst_file = array(
            'name'     => sanitize_file_name($_FILES['logo_for_desktop_notfication']['name']),
            'type'     => jssupportticket::JSST_sanitizeData($_FILES['logo_for_desktop_notfication']['type']),
            'tmp_name' => jssupportticket::JSST_sanitizeData($_FILES['logo_for_desktop_notfication']['tmp_name']),
            'error'    => jssupportticket::JSST_sanitizeData($_FILES['logo_for_desktop_notfication']['error']),
            'size'     => jssupportticket::JSST_sanitizeData($_FILES['logo_for_desktop_notfication']['size']),
        ); // JSST_sanitizeData() function uses wordpress santize functions
        $jsst_uploadfilesize = $jsst_file['size'] / 1024; //kb
        if($jsst_uploadfilesize > $jsst_filesize){
            JSSTmessage::setMessage(esc_html(__('Error file size too large', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_filetyperesult = wp_check_filetype(sanitize_file_name($_FILES['logo_for_desktop_notfication']['name']));
        if(!empty($jsst_filetyperesult['ext']) && !empty($jsst_filetyperesult['type'])){
            $jsst_image_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
            if(stristr($jsst_image_file_types, $jsst_filetyperesult['ext'])){
                $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
                if ( $jsst_result && ! isset( $jsst_result['error'] ) ) {
                    $jsst_filename = jssupportticketphplib::JSST_basename( $jsst_result['file'] );
                    $jsst_result = JSSTincluder::getJSModel('configuration')->storeDesktopNotificationLogo($jsst_filename);
                } else {
                    JSSTmessage::setMessage($jsst_result['error'], 'error');
                }
            }
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        return;
    }

}

?>
