<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTattachmentModel {

    /**
     * May the current user see the attachments belonging to this ticket?
     *
     * One answer for the edit form and for the detail page. They used to ask the
     * question separately and identically, which is how the two drifted: fixing
     * the form's copy left the detail page still hiding attachments from an
     * agent who holds the role but has no row in the Agents add-on's staff
     * table. The capability the Help Desk Agent role is built on is the honest
     * test, and getDownloadAttachmentById() already accepts it for serving the
     * files themselves. (Roadmap 4.0-SEC-04)
     */
    private function mayReadTicketAttachments($jsst_id) {
        if (JSSTroles::canManageHelpDesk() || current_user_can(JSSTroles::CAP_TICKETS)) {
            return true;
        }
        if (in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            return true;
        }
        // Not staff: the ticket has to be their own.
        $jsst_owns_ticket = (!JSSTincluder::getObjectClass('user')->isguest())
            ? JSSTincluder::getJSModel('ticket')->validateTicketDetailForUser($jsst_id)
            : JSSTincluder::getJSModel('ticket')->validateTicketDetailForVisitor($jsst_id);
        return (bool) $jsst_owns_ticket;
    }

    /**
     * The ticket's own attachments, for the edit form.
     *
     * Who counts as help-desk staff here used to be answered by the Agents
     * add-on alone: manage_options, or an agent row in that add-on's table. A
     * site that gives somebody the Help Desk Agent role without the add-on - or
     * with the add-on deactivated - therefore showed that agent a ticket they
     * are allowed to work on with its attachments silently missing. The
     * capability the role is built on is the honest test, and it is the same one
     * getDownloadAttachmentById() already accepts for serving those files, so
     * listing them grants nothing that was not already downloadable.
     * (Roadmap 4.0-SEC-04)
     */
    function getAttachmentForForm($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if (!$this->mayReadTicketAttachments($jsst_id)) {
            return false;
        }
        $jsst_query = "SELECT filename,filesize,id
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments`
                    WHERE ticketid = %d and replyattachmentid = 0";
        jssupportticket::$jsst_data[5] = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    /**
     * The attachments on one reply, or the ticket's own when $jsst_replyattachmentid
     * is 0 - which is what the ticket detail page asks for.
     */
    function getAttachmentForReply($jsst_id, $jsst_replyattachmentid) {
        if (!is_numeric($jsst_id))
            return false;
        if (!is_numeric($jsst_replyattachmentid))
            return false;
        if (!$this->mayReadTicketAttachments($jsst_id)) {
            return false;
        }
        $jsst_query = "SELECT filename,filesize,deleted,id
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments`
                    WHERE ticketid = %d AND replyattachmentid = %d";
        $jsst_result = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare($jsst_query, $jsst_id, $jsst_replyattachmentid));
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
        $jsst_id = absint( $jsst_id );

        if ( empty( $jsst_id ) ) {
            return false;
        }
        $jsst_query = "SELECT ticket.attachmentdir AS foldername,ticket.id AS ticketid,attach.filename  "
                . " FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` AS attach "
                . " JOIN `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket ON ticket.id = attach.ticketid "
                . " WHERE attach.id = %d";
        $jsst_obj = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (empty($jsst_obj)) {
            return false;
        }
        if(!current_user_can('manage_options') && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
            if (!JSSTincluder::getObjectClass('user')->isguest()) {
                $jsst_current_uid = JSSTincluder::getObjectClass('user')->uid();
                $jsst_ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($jsst_obj->ticketid);
                if ($jsst_current_uid != $jsst_ticket_uid) {
                    return false;
                }
            } else {
                if (!JSSTincluder::getJSModel('ticket')->validateTicketDetailForVisitor($jsst_obj->ticketid)) {
                    return false;
                }
            }
        }
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
                . " WHERE attach.id = %d";
        $jsst_object = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (!$jsst_object) {
            return false;
        }
        if (!current_user_can('manage_options') && !(in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())) {
            $jsst_owns_ticket = (!JSSTincluder::getObjectClass('user')->isguest())
                ? JSSTincluder::getJSModel('ticket')->validateTicketDetailForUser($jsst_object->ticketid)
                : JSSTincluder::getJSModel('ticket')->validateTicketDetailForVisitor($jsst_object->ticketid);
            if (!$jsst_owns_ticket) {
                return false;
            }
        }
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_foldername = $jsst_object->foldername;
        $jsst_filename = $jsst_object->filename;

        // A URL that goes through PHP, not one that points into the uploads
        // directory. The old direct URL was readable by anyone who had it —
        // there is no permission check on a static file — and on a server where
        // the directory deny rules do apply it was simply broken. Either way it
        // was the wrong answer. (Roadmap 4.0-SEC-03)
        //
        // The folder and file name stay out of the URL: the attachment id is
        // enough to serve it, and the storage path is nobody's business.
        unset($jsst_datadirectory, $jsst_foldername, $jsst_filename);
        if (is_admin()) {
            return admin_url('admin.php?page=ticket&task=viewattachment&action=jstask&id=' . (int) $jsst_id);
        }
        return jssupportticket::makeUrl(array(
            'jstmod' => 'ticket',
            'task'   => 'viewattachment',
            'action' => 'jstask',
            'id'     => (int) $jsst_id,
        ));
    }


    /**
     * Stream one attachment to whoever is allowed to have it.
     *
     * $jsst_inline is what lets the same permission check serve a thumbnail as
     * well as a download. Only real images are ever sent inline, and the type is
     * taken from the image header rather than from the file name, so a file that
     * merely claims to be a PNG cannot talk a browser into rendering it.
     * (Roadmap 4.0-SEC-03)
     */
    function getDownloadAttachmentById($jsst_id, $jsst_inline = false){
        if(!is_numeric($jsst_id)) return false;
        $jsst_query = "SELECT ticket.attachmentdir AS foldername,ticket.id AS ticketid,attach.filename  "
                . " FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` AS attach "
                . " JOIN `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket ON ticket.id = attach.ticketid "
                . " WHERE attach.id = %d";
        $jsst_object = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
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
                $jsst_imageinfo = $jsst_inline ? @getimagesize($jsst_file) : false;
                $jsst_disposition = 'attachment';
                $jsst_contenttype = 'application/octet-stream';
                if (!empty($jsst_imageinfo['mime'])) {
                    // Verified from the header, never from the extension.
                    $jsst_contenttype = $jsst_imageinfo['mime'];
                    $jsst_disposition = 'inline';
                }
                header('Content-Description: File Transfer');
                header('Content-Type: ' . $jsst_contenttype);
                // Stops a browser from second-guessing the type it was given.
                header('X-Content-Type-Options: nosniff');
                header('Content-Disposition: ' . $jsst_disposition . '; filename="' . jssupportticketphplib::JSST_basename($jsst_file) . '"');
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

        // --- ADDED SECURITY CHECK: Copied from getDownloadAttachmentById ---
        $jsst_ticketid = intval($jsst_id);
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

        // If the user fails all checks, block the download and show a 404 page
        if ($jsst_download != true) {
            include( get_query_template( '404' ) );
            exit;
        }
        // -------------------------------------------------------------------

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
        echo $jsst_wp_filesystem->get_contents($jsst_file);
        exit;
    }

    function getAllDownloads() {
        $jsst_downloadid = absint( JSSTrequest::getVar('downloadid') );
        //if not admin and agent
        // check for ticket owner only in case of user
        if(!current_user_can('manage_options') && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
            // in case of user check for ticket owner
            if (!JSSTincluder::getObjectClass('user')->isguest()) {
                $jsst_current_uid = JSSTincluder::getObjectClass('user')->uid();
                $jsst_ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($jsst_downloadid);
                if ($jsst_current_uid != $jsst_ticket_uid) {
                    return;
                }
            } else {
                if (!JSSTincluder::getJSModel('ticket')->validateTicketDetailForVisitor($jsst_downloadid)) {
                    return;
                }
            }   
        }
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
        $jsst_downloadid = absint( JSSTrequest::getVar('downloadid') );
        if (!is_numeric($jsst_downloadid)) {
            return;
        }
        if(!current_user_can('manage_options') && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
            $jsst_reply_ticketid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare("SELECT ticketid FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE id = %d", $jsst_downloadid));
            if (!JSSTincluder::getObjectClass('user')->isguest()) {
                $jsst_current_uid = JSSTincluder::getObjectClass('user')->uid();
                $jsst_ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($jsst_reply_ticketid);
                if ($jsst_current_uid != $jsst_ticket_uid) {
                    return;
                }
            } else {
                if (!JSSTincluder::getJSModel('ticket')->validateTicketDetailForVisitor($jsst_reply_ticketid)) {
                    return;
                }
            }
        }
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
