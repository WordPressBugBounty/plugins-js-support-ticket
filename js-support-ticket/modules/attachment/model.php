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
            ? JSSTincluder::getJSModel('ticket')->validateTicketReadForUser($jsst_id)
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
        if (!JSSTroles::canAttachFiles()) {
            if (self::filesWerePosted('filename')) {
                JSSTmessage::setMessage(esc_html(__('Your files were not attached. You are not allowed to attach files.', 'js-support-ticket')), 'error');
            }
            return;
        }
        JSSTincluder::getObjectClass('uploads')->storeTicketAttachment($jsst_data, $this);
        return;
    }

    /** Did the form actually send a file in this field (single or many)? */
    public static function filesWerePosted($jsst_field) {
        if (empty($_FILES[$jsst_field]['name'])) {
            return false;
        }
        foreach ((array) $_FILES[$jsst_field]['name'] as $jsst_name) {
            if ($jsst_name !== '') {
                return true;
            }
        }
        return false;
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
                ? JSSTincluder::getJSModel('ticket')->validateTicketReadForUser($jsst_object->ticketid)
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
        $jsst_query = "SELECT ticket.attachmentdir AS foldername,ticket.id AS ticketid,attach.filename,attach.deleted  "
                . " FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` AS attach "
                . " JOIN `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket ON ticket.id = attach.ticketid "
                . " WHERE attach.id = %d";
        $jsst_object = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (empty($jsst_object)) {
            JSSTincluder::notFound();
        }
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
                    if(JSSTincluder::getJSModel('ticket')->validateTicketReadForUser($jsst_ticketid)){
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
            $jsst_file = $jsst_path . '/' . jssupportticketphplib::JSST_basename($jsst_filename);
            // Streams the file and ends the request; says why when it cannot.
            JSSTattachmentserver::send($jsst_file, array('inline' => $jsst_inline, 'deleted' => !empty($jsst_object->deleted)));
        }else{
            JSSTincluder::notFound();
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
                    if(JSSTincluder::getJSModel('ticket')->validateTicketReadForUser($jsst_ticketid)){
                        $jsst_download = true;
                    }
                }
            }
        }else{ // user is visitor
            $jsst_download = JSSTincluder::getJSModel('ticket')->validateTicketDetailForVisitor($jsst_ticketid);
        }

        // If the user fails all checks, block the download and show a 404 page
        if ($jsst_download != true) {
            JSSTincluder::notFound();
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
            JSSTincluder::notFound();
        }

        $jsst_file = JSSTattachmentserver::attachmentRoot() . '/ticket/' . $jsst_foldername . '/' . jssupportticketphplib::JSST_basename($jsst_filename);
        // Streams the file and ends the request; says why when it cannot.
        JSSTattachmentserver::send($jsst_file);
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
        JSSTattachmentserver::makeTempFolder($jsst_path);
        $jsst_randomfolder = $this->getRandomFolderName($jsst_path);
        $jsst_path .= '/' . $jsst_randomfolder;

        JSSTattachmentserver::makeTempFolder($jsst_path);
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

        // Only the files that are really there: one missing or cleaned-up file
        // used to stop the whole zip, with the server path in the message.
        $jsst_files = isset($jsst_directory) ? JSSTattachmentserver::existingFiles($jsst_directory, $jsst_scanned_directory) : array();
        if (empty($jsst_files)) {
            JSSTattachmentserver::removeTempFolder($jsst_path);
            JSSTattachmentserver::nothingToZip();
        }
        $jsst_v_list = $jsst_archive->create($jsst_files, PCLZIP_OPT_REMOVE_PATH, realpath($jsst_directory));
        if ($jsst_v_list == 0) {
            error_log('JS Help Desk: zip of attachments failed - ' . $jsst_archive->errorInfo()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            JSSTattachmentserver::removeTempFolder($jsst_path);
            JSSTattachmentserver::nothingToZip();
        }

        // The zip is temporary: sent, then its folder is removed.
        JSSTattachmentserver::send($jsst_path . '/alldownloads.zip', array(
            'base' => JSST_PLUGIN_PATH . 'zipdownloads',
            'name' => 'alldownloads.zip',
            'after' => function () use ($jsst_path) { JSSTattachmentserver::removeTempFolder($jsst_path); },
        ));
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
        JSSTattachmentserver::makeTempFolder($jsst_base_path);
        $jsst_randomfolder = $this->getRandomFolderName($jsst_base_path);
        $jsst_path = $jsst_base_path . '/' . $jsst_randomfolder;

        JSSTattachmentserver::makeTempFolder($jsst_path);
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

        // Only the files that are really there: one missing or cleaned-up file
        // used to stop the whole zip, with the server path in the message.
        $jsst_files = isset($jsst_directory) ? JSSTattachmentserver::existingFiles($jsst_directory, $jsst_scanned_directory) : array();
        if (empty($jsst_files)) {
            JSSTattachmentserver::removeTempFolder($jsst_path);
            JSSTattachmentserver::nothingToZip();
        }
        $jsst_v_list = $jsst_archive->create($jsst_files, PCLZIP_OPT_REMOVE_PATH, realpath($jsst_directory));
        if ($jsst_v_list == 0) {
            error_log('JS Help Desk: zip of attachments failed - ' . $jsst_archive->errorInfo()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            JSSTattachmentserver::removeTempFolder($jsst_path);
            JSSTattachmentserver::nothingToZip();
        }

        // The zip is temporary: sent, then its folder is removed.
        JSSTattachmentserver::send($jsst_path . '/alldownloads.zip', array(
            'base' => $jsst_base_path,
            'name' => 'alldownloads.zip',
            'after' => function () use ($jsst_path) { JSSTattachmentserver::removeTempFolder($jsst_path); },
        ));
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
