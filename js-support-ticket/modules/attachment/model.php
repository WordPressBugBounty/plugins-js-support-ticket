<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTattachmentModel {

    function getAttachmentForForm($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT filename,filesize,id
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments`
                    WHERE ticketid = " . esc_sql($jsst_id) . " and replyattachmentid = 0";
        jssupportticket::$jsst_data[5] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getAttachmentForReply($jsst_id, $jsst_replyattachmentid) {
        if (!is_numeric($jsst_id))
            return false;
        if (!is_numeric($jsst_replyattachmentid))
            return false;
        $jsst_query = "SELECT filename,filesize,id
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments`
                    WHERE ticketid = " . esc_sql($jsst_id) . " AND replyattachmentid = " . esc_sql($jsst_replyattachmentid);
        $jsst_result = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_result;
    }

    function storeAttachments($jsst_data) {
        JSSTincluder::getObjectClass('uploads')->storeTicketAttachment($jsst_data, $this);
        return;
    }

    function storeTicketAttachment($jsst_ticketid, $jsst_replyattachmentid, $jsst_filesize, $jsst_filename) {
        if (!is_numeric($jsst_ticketid))
            return false;
        $jsst_created = date_i18n('Y-m-d H:i:s');
        $jsst_data = array('ticketid' => $jsst_ticketid,
            'replyattachmentid' => $jsst_replyattachmentid,
            'filesize' => $jsst_filesize,
            'filename' => $jsst_filename,
            'status' => 1,
            'created' => $jsst_created
        );

        $jsst_row = JSSTincluder::getJSTable('attachments');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 1) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            return false;
        }
        return true;
    }

    function removeAttachment($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = $jsst_query = "SELECT ticket.attachmentdir AS foldername,ticket.id AS ticketid,attach.filename  "
                . " FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` AS attach "
                . " JOIN `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket ON ticket.id = attach.ticketid "
                . " WHERE attach.id = ". esc_sql($jsst_id);
        $jsst_obj = jssupportticket::$_db->get_row($jsst_query);
        $jsst_filename = $jsst_obj->filename;
        $jsst_foldername = $jsst_obj->foldername;

        $jsst_row = JSSTincluder::getJSTable('attachments');
        if ($jsst_row->delete($jsst_id)) {
            $jsst_datadirectory = jssupportticket::$_config['data_directory'];

            $jsst_maindir = wp_upload_dir();
            $jsst_path = $jsst_maindir['basedir'];
            $jsst_path = $jsst_path .'/'.$jsst_datadirectory;
            $jsst_path = $jsst_path . '/attachmentdata';

            $jsst_path = $jsst_path . '/ticket/'.$jsst_foldername.'/' . $jsst_filename;
            wp_delete_file($jsst_path);
            //$jsst_files = glob($jsst_path.'/*.*');
            //array_map('unlink', $jsst_files); // delete all file in the direcoty
            JSSTmessage::setMessage(esc_html(__('The attachment has been removed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            JSSTmessage::setMessage(esc_html(__('The attachment has not been removed', 'js-support-ticket')), 'error');
        }
    }

    function getAttachmentImage($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        $jsst_query = "SELECT ticket.attachmentdir AS foldername,ticket.id AS ticketid,attach.filename  "
                . " FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` AS attach "
                . " JOIN `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket ON ticket.id = attach.ticketid "
                . " WHERE attach.id = ". esc_sql($jsst_id);
        $jsst_object = jssupportticket::$_db->get_row($jsst_query);
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_foldername = $jsst_object->foldername;
        $jsst_filename = $jsst_object->filename;

        $jsst_maindir = wp_upload_dir();
        $jsst_path = $jsst_maindir['baseurl'];
        $jsst_path = $jsst_path .'/'.$jsst_datadirectory;
        $jsst_path = $jsst_path . '/attachmentdata';
        $jsst_path = $jsst_path . '/ticket/' . $jsst_foldername;
        $jsst_file = $jsst_path . '/'.$jsst_filename;
        return $jsst_file;
    }


    function getDownloadAttachmentById($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        $jsst_query = "SELECT ticket.attachmentdir AS foldername,ticket.id AS ticketid,attach.filename  "
                . " FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` AS attach "
                . " JOIN `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket ON ticket.id = attach.ticketid "
                . " WHERE attach.id = ". esc_sql($jsst_id);
        $jsst_object = jssupportticket::$_db->get_row($jsst_query);
        $jsst_foldername = $jsst_object->foldername;
        $jsst_ticketid = $jsst_object->ticketid;
        $jsst_filename = $jsst_object->filename;
        $jsst_download = false;
        if(!JSSTincluder::getObjectClass('user')->isguest()){
            if(current_user_can('manage_options') || current_user_can('jsst_support_ticket_tickets') ){
                $jsst_download = true;
            }else{
                if( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                    $jsst_download = true;
                }else{
                    if(JSSTincluder::getJSModel('ticket')->validateTicketDetailForUser($jsst_ticketid)){
                        $jsst_download = true;
                    }
                }
            }
        }else{ // user is visitor
            $jsst_download = JSSTincluder::getJSModel('ticket')->validateTicketDetailForVisitor($jsst_ticketid);
        }
        if($jsst_download == true){
            $jsst_datadirectory = jssupportticket::$_config['data_directory'];
            $jsst_maindir = wp_upload_dir();
            $jsst_path = $jsst_maindir['basedir'];
            $jsst_path = $jsst_path .'/'.$jsst_datadirectory;
            $jsst_path = $jsst_path . '/attachmentdata';
            $jsst_path = $jsst_path . '/ticket/' . $jsst_foldername;
            $jsst_file = $jsst_path . '/' . $jsst_filename;

            // Initialize WordPress Filesystem
            global $wp_filesystem;
            if (!function_exists('wp_handle_upload')) {
                do_action('jssupportticket_load_wp_file');
            }
            if ( ! WP_Filesystem() ) {
                return false;
            }
            $jsst_wp_filesystem = $wp_filesystem;

            if ($jsst_wp_filesystem->exists($jsst_file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . jssupportticketphplib::JSST_basename($jsst_file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                
                // Use $jsst_wp_filesystem instead of filesize() and readfile()
                header('Content-Length: ' . $jsst_wp_filesystem->size($jsst_file));
                
                ob_clean();
                flush();
                
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $jsst_wp_filesystem->get_contents($jsst_file);
                exit();
            }
        }else{
            include( get_query_template( '404' ) );
            exit;
        }
    }

    function getDownloadAttachmentByName($jsst_file_name, $jsst_id) {
        if (empty($jsst_file_name) || !is_numeric($jsst_id)) {
            return false;
        }

        $jsst_filename = jssupportticketphplib::JSST_str_replace(' ', '_', $jsst_file_name);
        $jsst_filename = jssupportticketphplib::JSST_clean_file_path($jsst_filename);

        // Using prepare for database security
        $jsst_foldername = jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare(
                "SELECT attachmentdir FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
                $jsst_id
            )
        );

        if (empty($jsst_foldername)) {
            return false;
        }

        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_upload_dir = wp_upload_dir();

        $jsst_path = $jsst_upload_dir['basedir'] . '/' . $jsst_datadirectory . '/attachmentdata/ticket/' . $jsst_foldername;
        $jsst_file = $jsst_path . '/' . $jsst_filename;

        // Initialize WP_Filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        // Check if file exists using WP_Filesystem
        if (!$jsst_wp_filesystem->exists($jsst_file)) {
            return false;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header(
            'Content-Disposition: attachment; filename=' . jssupportticketphplib::JSST_basename($jsst_file)
        );
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        header('Content-Length: ' . (int) $jsst_wp_filesystem->size($jsst_file));
    
        while (ob_get_level()) {
            ob_end_clean();
        }

        flush();

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $wp_filesystem->get_contents($jsst_file);
        exit;
    }

    function getAllDownloads() {
        $jsst_downloadid = JSSTrequest::getVar('downloadid');
        $jsst_ticketattachment = JSSTincluder::getJSModel('ticket')->getAttachmentByTicketId($jsst_downloadid);
        
        if(!class_exists('PclZip')){
            do_action('jssupportticket_load_wp_pcl_zip');
        }
        $jsst_path = JSST_PLUGIN_PATH;
        $jsst_path .= 'zipdownloads';
        JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        $jsst_randomfolder = $this->getRandomFolderName($jsst_path);
        $jsst_path .= '/' . $jsst_randomfolder;

        JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        $jsst_archive = new PclZip($jsst_path . '/alldownloads.zip');
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_maindir = wp_upload_dir();
        $jsst_jpath = $jsst_maindir['basedir'];
        $jsst_jpath = $jsst_jpath .'/'.$jsst_datadirectory;
        $jsst_scanned_directory = [];

        foreach ($jsst_ticketattachment AS $jsst_ticketattachments) {
            $jsst_directory = $jsst_jpath . '/attachmentdata/ticket/' . $jsst_ticketattachments->attachmentdir . '/';
            // $jsst_scanned_directory = array_diff(scandir($jsst_directory), array('..', '.'));
            array_push($jsst_scanned_directory,$jsst_ticketattachments->filename);
        }
        // if(!is_dir($jsst_directory))
        //         return false;

        $jsst_filelist = '';
        foreach ($jsst_scanned_directory AS $jsst_file) {
            $jsst_filelist .= $jsst_directory . '/' . $jsst_file . ',';
        }
        $jsst_filelist = jssupportticketphplib::JSST_substr($jsst_filelist, 0, jssupportticketphplib::JSST_strlen($jsst_filelist) - 1);
        $jsst_v_list = $jsst_archive->create($jsst_filelist, PCLZIP_OPT_REMOVE_PATH, $jsst_directory);

        if ($jsst_v_list == 0) {
            die("Error : '" . wp_kses($jsst_archive->errorInfo(), JSST_ALLOWED_TAGS) . "'");
        }

        $jsst_file = $jsst_path . '/alldownloads.zip';

        // --- Initialize WP_Filesystem ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        if ($jsst_wp_filesystem->exists($jsst_file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . jssupportticketphplib::JSST_basename($jsst_file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            
            // Use WP_Filesystem for size
            header('Content-Length: ' . $jsst_wp_filesystem->size($jsst_file));
            
            if (ob_get_level()) ob_end_clean();
            flush();
            
            // Use WP_Filesystem for reading content
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $jsst_wp_filesystem->get_contents($jsst_file);
        }

        // --- CLEANUP: Use WP_Filesystem for deletion ---
        if ($jsst_wp_filesystem->exists($jsst_file)) {
            $jsst_wp_filesystem->delete($jsst_file);
        }

        $jsst_base_zip_path = JSST_PLUGIN_PATH . 'zipdownloads/' . $jsst_randomfolder;
        
        // Delete index.html and the folder recursively
        if ($jsst_wp_filesystem->exists($jsst_base_zip_path)) {
            $jsst_wp_filesystem->delete($jsst_base_zip_path, true); // true = recursive delete
        }

        exit();
    }

    function getAllReplyDownloads() {
        $jsst_downloadid = JSSTrequest::getVar('downloadid');
        $jsst_replyattachment = JSSTincluder::getJSModel('reply')->getAttachmentByReplyId($jsst_downloadid);
        
        if(!class_exists('PclZip')){
            do_action('jssupportticket_load_wp_pcl_zip');
        }

        $jsst_base_path = JSST_PLUGIN_PATH . 'zipdownloads';
        JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_base_path);
        $jsst_randomfolder = $this->getRandomFolderName($jsst_base_path);
        $jsst_path = $jsst_base_path . '/' . $jsst_randomfolder;

        JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        $jsst_archive = new PclZip($jsst_path . '/alldownloads.zip');
        
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_maindir = wp_upload_dir();
        $jsst_jpath = $jsst_maindir['basedir'] . '/' . $jsst_datadirectory;
        
        $jsst_scanned_directory = [];
        foreach ($jsst_replyattachment AS $jsst_replyattachments) {
            $jsst_directory = $jsst_jpath . '/attachmentdata/ticket/' . $jsst_replyattachments->attachmentdir . '/';
            // $jsst_scanned_directory = array_diff(scandir($jsst_directory), array('..', '.'));
            array_push($jsst_scanned_directory, $jsst_replyattachments->filename);
        }

        // if(!is_dir($jsst_directory))
        //         return false;

        $jsst_filelist = '';
        foreach ($jsst_scanned_directory AS $jsst_file) {
            $jsst_filelist .= $jsst_directory . '/' . $jsst_file . ',';
        }
        $jsst_filelist = jssupportticketphplib::JSST_substr($jsst_filelist, 0, jssupportticketphplib::JSST_strlen($jsst_filelist) - 1);
        
        $jsst_v_list = $jsst_archive->create($jsst_filelist, PCLZIP_OPT_REMOVE_PATH, $jsst_directory);
        if ($jsst_v_list == 0) {
            die("Error : '" . wp_kses($jsst_archive->errorInfo(), JSST_ALLOWED_TAGS) . "'");
        }

        $jsst_file = $jsst_path . '/alldownloads.zip';

        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        // --- FILE DOWNLOAD SECTION ---
        if ($jsst_wp_filesystem->exists($jsst_file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . esc_attr(jssupportticketphplib::JSST_basename($jsst_file)));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            
            // Use floatval or intval to escape the numeric length
            header('Content-Length: ' . floatval($jsst_wp_filesystem->size($jsst_file))); 
            
            if (ob_get_level()) {
                ob_end_clean();
            }
            flush();
            
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $jsst_wp_filesystem->get_contents($jsst_file); 
            exit;
        }

        // --- CLEANUP SECTION ---
        // Using the 'true' parameter in delete() makes it recursive (replaces rmdir and manual index.html deletion)
        if ( $jsst_wp_filesystem->exists($jsst_path)) {
            $jsst_wp_filesystem->delete($jsst_path, true); 
        }

        exit();
    }

    function getRandomFolderName($jsst_path) {
        $jsst_match = '';
        do {
            $jsst_rndfoldername = "";
            $jsst_length = 5;
            $jsst_possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
            $jsst_maxlength = jssupportticketphplib::JSST_strlen($jsst_possible);
            if ($jsst_length > $jsst_maxlength) {
                $jsst_length = $jsst_maxlength;
            }
            $jsst_i = 0;
            while ($jsst_i < $jsst_length) {
                $jsst_char = jssupportticketphplib::JSST_substr($jsst_possible, wp_rand(0, $jsst_maxlength - 1), 1);
                if (!strstr($jsst_rndfoldername, $jsst_char)) {
                    if ($jsst_i == 0) {
                        if (ctype_alpha($jsst_char)) {
                            $jsst_rndfoldername .= $jsst_char;
                            $jsst_i++;
                        }
                    } else {
                        $jsst_rndfoldername .= $jsst_char;
                        $jsst_i++;
                    }
                }
            }
            $jsst_folderexist = $jsst_path . '/' . $jsst_rndfoldername;
            if (file_exists($jsst_folderexist))
                $jsst_match = 'Y';
            else
                $jsst_match = 'N';
        }while ($jsst_match == 'Y');

        return $jsst_rndfoldername;
    }
}

?>
