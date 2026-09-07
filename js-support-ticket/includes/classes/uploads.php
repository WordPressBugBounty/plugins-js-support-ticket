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
                $jsst_query = jssupportticket::$_db->prepare("SELECT attachmentdir FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = %d", $this->jsst_ticketid);
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
        //
        // Roadmap 3.2-CORE-03: every exit from here now runs through the single
        // cleanup at the bottom. The early returns this method used to take left
        // the upload_dir override registered for the rest of the request, which
        // sent any later upload (this plugin's or another's) into the ticket
        // attachment folder. A problem with one file also aborted the loop, so
        // the attachments after it were dropped without a word to the customer.
        $jsst_result = array();
        $jsst_file_directory = '';
        $jsst_files = isset($_FILES['filename']) ? filter_var_array($_FILES['filename']) : null;

        if (is_array($jsst_files) && isset($jsst_files['name']) && is_array($jsst_files['name'])) {
            foreach ($jsst_files['name'] as $jsst_key => $jsst_value) {
                if (!$jsst_files['name'][$jsst_key]) {
                    continue;
                }
                $jsst_file = array(
                        'name'     => $jsst_files['name'][$jsst_key],
                        'type'     => $jsst_files['type'][$jsst_key],
                        'tmp_name' => $jsst_files['tmp_name'][$jsst_key],
                        'error'    => $jsst_files['error'][$jsst_key],
                        'size'     => $jsst_files['size'][$jsst_key]
                        );
                $jsst_displayname = sanitize_file_name($jsst_files['name'][$jsst_key]);
                $jsst_uploadfilesize = $jsst_file['size'] / 1024; //kb
                if($jsst_uploadfilesize > $jsst_filesize){
                    JSSTmessage::setMessage(
                        sprintf(
                            /* translators: %s: the attachment file name */
                            esc_html(__('"%s" was not attached because it is larger than the maximum allowed file size.', 'js-support-ticket')),
                            esc_html($jsst_displayname)
                        ),
                        'error',
                        'attachments'
                    );
                    continue;
                }
                $jsst_filetyperesult = wp_check_filetype($jsst_displayname);
                if(empty($jsst_filetyperesult['ext']) || empty($jsst_filetyperesult['type'])){
                    // Previously skipped in silence, so the ticket arrived
                    // without the screenshot the customer thought they attached.
                    JSSTmessage::setMessage(
                        sprintf(
                            /* translators: %s: the attachment file name */
                            esc_html(__('"%s" was not attached because that file type is not allowed.', 'js-support-ticket')),
                            esc_html($jsst_displayname)
                        ),
                        'error',
                        'attachments'
                    );
                    continue;
                }
                $jsst_document_file_types = JSSTincluder::getJSModel('configuration')->getConfigValue('file_extension');
                if(!stristr($jsst_document_file_types, $jsst_filetyperesult['ext'])){
                    JSSTmessage::setMessage(
                        sprintf(
                            /* translators: %s: the attachment file name */
                            esc_html(__('"%s" was not attached because that file type is not allowed.', 'js-support-ticket')),
                            esc_html($jsst_displayname)
                        ),
                        'error'
                    );
                    continue;
                }

                // What the file actually contains, not what its name claims.
                // Runs on the temporary upload, before anything is moved
                // anywhere permanent, and gives a malware scanner its chance.
                // (Roadmap 4.0-SEC-03)
                $jsst_guard = JSSTattachmentguard::validate($jsst_file['tmp_name'], $jsst_displayname);
                if ($jsst_guard !== true) {
                    JSSTmessage::setMessage(
                        sprintf(
                            /* translators: 1: the attachment file name, 2: why it was refused */
                            esc_html(__('"%1$s" was not attached. %2$s', 'js-support-ticket')),
                            esc_html($jsst_displayname),
                            $jsst_guard
                        ),
                        'error'
                    );
                    continue;
                }

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
                    JSSTmessage::setMessage(
                        isset($jsst_result['error']) ? $jsst_result['error'] : esc_html(__('The attachment could not be uploaded.', 'js-support-ticket')),
                        'error'
                    );
                }
            }
        }
        // generate index file
        if (!empty($jsst_file_directory)) {
            JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_file_directory);
            $this->protectTicketAttachmentDirectory($jsst_file_directory);
        }
        // Set everything back to normal.
        remove_filter( 'upload_dir', array($this,'jssupportticket_upload_dir'));
        return;
    }

    // Ticket attachments are only ever served via the ownership-checked PHP download
    // endpoints, never by direct URL, so block direct web access to the storage folder.
    /**
     * Protect the directory this attachment landed in, and its parent.
     *
     * Both, because the parent is the one an attacker would try to list and the
     * child is the one holding the file. The guard writes an index.php and a
     * web.config alongside the .htaccess this used to write on its own, and it
     * no longer needs WP_Filesystem to have been initialised to do anything at
     * all. (Roadmap 4.0-SEC-03)
     */
    private function protectTicketAttachmentDirectory($jsst_file_directory){
        JSSTattachmentguard::protectDirectory($jsst_file_directory);
        JSSTattachmentguard::protectDirectory(dirname($jsst_file_directory));
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
        $jsst_query = jssupportticket::$_db->prepare("SELECT attachmentdir FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = %d", $jsst_idsarray[0]);
        $jsst_foldername = jssupportticket::$_db->get_var($jsst_query);

        $jsst_path = $jsst_path . '/' . $jsst_foldername;
        if (!file_exists($jsst_path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        }
        $this->protectTicketAttachmentDirectoryPlain(dirname($jsst_path));

        file_put_contents($jsst_path . '/' . sanitize_file_name($jsst_key), $jsst_value); // save the file
        return true;
    }

    // Same protection as protectTicketAttachmentDirectory() but for call sites that
    // don't have WP_Filesystem initialized.
    private function protectTicketAttachmentDirectoryPlain($jsst_ticket_dir){
        $jsst_htaccess_file = $jsst_ticket_dir . '/.htaccess';
        if (!file_exists($jsst_htaccess_file)) {
            $jsst_htaccess_contents = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
            @file_put_contents($jsst_htaccess_file, $jsst_htaccess_contents);
        }
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
        $jsst_filename = '';
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
                    $this->protectTicketAttachmentDirectory($jsst_file_directory);
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
                    $this->protectTicketAttachmentDirectory($jsst_file_directory);
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
