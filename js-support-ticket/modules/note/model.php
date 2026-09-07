<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Internal notes — part of the free core. (Roadmap 4.0-CORE-02)
 *
 * Loaded only when the stand-alone Private Note add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'note' module to the add-on
 * directory while that add-on is active, so the add-on stays authoritative and
 * nothing is registered or rendered twice. Both implementations read and write
 * the same js_ticket_notes table and the same attachment folders, so existing
 * notes and their files keep working with no migration. (Roadmap 4.0-CORE-19)
 *
 * @mentions, restricted visibility, approvals and compliance export stay Pro.
 *
 * The add-on's hooks (jsstgetnotes, jsst_aadon_getnotes, jsst_reset_aadon_query)
 * and its time-tracking entry points are kept exactly as they were, so a site
 * running the Time Tracking add-on behaves the same after the Private Note
 * add-on is switched off.
 */
class JSSTnoteModel {

    /** Bumped when the table layout below changes. */
    const SCHEMA_VERSION = '400';

    /**
     * Create the notes table if this site never had the add-on, and add anything
     * missing from an older add-on layout. Each column is checked first, so this
     * cannot fail on a table that already has it.
     */
    public static function ensureSchema() {
        // The stored version is only a hint — the attachment columns below are
        // what actually has to be there. A notes table recreated by the legacy
        // add-on has none of them. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_note_schema', self::SCHEMA_VERSION,
                array('js_ticket_notes' => array('filename', 'filesize', 'filedeleted')))) {
            return;
        }
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_notes';
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    ticketid int(11) DEFAULT NULL,
                    staffid int(11) DEFAULT NULL,
                    title varchar(255) DEFAULT NULL,
                    note text,
                    status tinyint(1) DEFAULT NULL,
                    created datetime DEFAULT NULL,
                    filename VARCHAR(300) NULL,
                    filesize VARCHAR(15) NULL,
                    filedeleted TINYINT(1) NOT NULL DEFAULT '0',
                    PRIMARY KEY (id)
                ) " . $jsst_charset);

        $jsst_columns = jssupportticket::$_db->get_col('SHOW COLUMNS FROM `' . $jsst_table . '`', 0);
        if (!is_array($jsst_columns)) {
            $jsst_columns = array();
        }
        $jsst_wanted = array(
            'filename'    => "ADD `filename` VARCHAR(300) NULL",
            'filesize'    => "ADD `filesize` VARCHAR(15) NULL",
            'filedeleted' => "ADD `filedeleted` TINYINT(1) NOT NULL DEFAULT '0'",
        );
        foreach ($jsst_wanted as $jsst_column => $jsst_clause) {
            if (!in_array($jsst_column, $jsst_columns, true)) {
                jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ' . $jsst_clause);
            }
        }

        // Every read is by ticket. (Roadmap 4.0-PERF-01)
        $jsst_indexes = jssupportticket::$_db->get_col('SHOW INDEX FROM `' . $jsst_table . '`', 2);
        if (!is_array($jsst_indexes)) {
            $jsst_indexes = array();
        }
        if (!in_array('jsst_ticketid', $jsst_indexes, true)) {
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_ticketid` (`ticketid`)');
        }

        update_option('jsst_note_schema', self::SCHEMA_VERSION, false);
    }

    /**
     * Notes on one ticket, oldest first, into jssupportticket::$jsst_data[6].
     */
    function getNotes($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid))
            return false;
        self::ensureSchema();

        // Kept so the Time Tracking add-on can still add its select and join.
        do_action('jsstgetnotes');
        do_action('jsst_aadon_getnotes');
        $jsst_query = "SELECT note.*,note.staffid AS userid,user.display_name,user.user_email " . jssupportticket::$_addon_query['select'] . "
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_notes` AS note
                " . jssupportticket::$_addon_query['join'] . "
                LEFT JOIN `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user ON user.id = note.staffid
                WHERE note.ticketid = %d ORDER BY note.id";
        jssupportticket::$jsst_data[6] = jssupportticket::$_db->get_results(
            jssupportticket::$_db->prepare($jsst_query, $jsst_ticketid)
        );
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            jssupportticket::$jsst_data[6] = array();
        }
        do_action('jsst_reset_aadon_query');
        return;
    }

    /**
     * Post an internal note. Agents and administrators only — a note is never
     * visible to the customer.
     */
    function storeTicketInternalNote($jsst_data, $jsst_note) {
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Post Internal Note');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!JSSTroles::canWriteInternalNote()) {
            /* Was manage_options only, which left the whole point of a light
               agent seat unreachable: read the queue, leave a note, never
               answer the customer. Both agent tiers hold CAP_NOTE and
               administrators still pass, so nobody who could post a note
               before has lost it. (Roadmap 4.0-SEC-04) */
            return false;
        }
        self::ensureSchema();
        $jsst_cuid = JSSTincluder::getObjectClass('user')->uid();
        if (!isset($jsst_data['ticketid']) || !is_numeric($jsst_data['ticketid'])) {
            return false;
        }
        $jsst_ticketid = $jsst_data['ticketid'];
        $jsst_data['id'] = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        $jsst_data['internalnotetitle'] = isset($jsst_data['internalnotetitle']) ? $jsst_data['internalnotetitle'] : '';

        $jsst_filesize = 0;
        $jsst_filename = '';
        $jsst_fileresult = $this->uploadFileNote($jsst_ticketid, 'note_attachment');
        if(is_array($jsst_fileresult)){
            if(isset($jsst_fileresult['filename'])){
                $jsst_filename = $jsst_fileresult['filename'];
            }
            if(isset($jsst_fileresult['filesize'])){
                $jsst_filesize = $jsst_fileresult['filesize'];
            }
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);

        $jsst_data['ticketid'] = $jsst_ticketid;
        $jsst_data['title'] = $jsst_data['internalnotetitle'];
        $jsst_data['note'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($jsst_note);
        $jsst_data['filename'] = $jsst_filename;
        $jsst_data['filesize'] = $jsst_filesize;
        $jsst_data['status'] = 1;
        $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
        $jsst_data['staffid'] = $jsst_cuid;

        $jsst_row = JSSTincluder::getJSTable('note');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            $jsst_noteid = $jsst_row->id;

            JSSTmessage::setMessage(esc_html(__('The internal note has been posted', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            if ( in_array('timetracking',jssupportticket::$_active_addons) ){
                JSSTincluder::getJSModel('timetracking')->storeTimeTaken($jsst_data,$jsst_noteid,2);
            }
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The internal note has not been posted', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
        }
        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = $jsst_current_user->display_name;
        $jsst_eventtype = esc_html(__('Post Internal Note', 'js-support-ticket'));
        $jsst_message = esc_html(__('The internal note is posted by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        if(JSSTmergedaddon::featureEnabled('tickethistory')){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }
        // if Close on reply is cheked
        if (isset($jsst_data['closeonreply']) && $jsst_data['closeonreply'] == 1) {
            JSSTincluder::getJSModel('ticket')->closeTicket($jsst_ticketid);
        }

        return;
    }

    /**
     * Remove every note on a ticket, used when the ticket itself goes.
     */
    function removeTicketInternalNote($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid)) {
            return false;
        }
        jssupportticket::$_db->delete(
            jssupportticket::$_db->prefix . 'js_ticket_notes',
            array('ticketid' => $jsst_ticketid),
            array('%d')
        );
        return;
    }

    function uploadFileNote($jsst_id,$jsst_field){
        if(!is_numeric($jsst_id)) return false;
        return JSSTincluder::getObjectClass('uploads')->uploadInternalNoteAttachment($jsst_id,$jsst_field);
    }

    /**
     * Serve a note attachment to an agent or administrator, never to a customer.
     */
    function getDownloadAttachmentById($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT ticket.attachmentdir AS foldername,ticket.id AS ticketid,note.filename
                FROM `".jssupportticket::$_db->prefix."js_ticket_notes` AS note
                JOIN `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket ON ticket.id = note.ticketid
                WHERE note.id = %d",
            $jsst_id
        );
        $jsst_object = jssupportticket::$_db->get_row($jsst_query);
        if (empty($jsst_object) || $jsst_object->filename == '') {
            include( get_query_template( '404' ) );
            exit;
        }
        $jsst_foldername = $jsst_object->foldername;
        $jsst_filename = $jsst_object->filename;
        $jsst_download = false;
        if(!JSSTincluder::getObjectClass('user')->isguest()){
            if(is_admin()){
                $jsst_download = true;
            }else{
                if( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                    $jsst_download = true;
                }
            }
        }
        if($jsst_download == true){
            $jsst_datadirectory = jssupportticket::$_config['data_directory'];
            $jsst_wpdir = wp_upload_dir();
            $jsst_path = $jsst_wpdir['basedir'].'/'.$jsst_datadirectory;
            $jsst_path = $jsst_path . '/attachmentdata';
            $jsst_path = $jsst_path . '/ticket/' . $jsst_foldername;
            // The stored name is the only part of the path that is not fixed, so
            // it is reduced to a bare file name before it is used.
            $jsst_file = $jsst_path . '/' . jssupportticketphplib::JSST_basename($jsst_filename);
            if (!file_exists($jsst_file)) {
                include( get_query_template( '404' ) );
                exit;
            }
            JSSTincluder::getJSModel('jssupportticket')->generateIndexFile($jsst_path);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . jssupportticketphplib::JSST_basename($jsst_file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($jsst_file));
            flush();
            readfile($jsst_file);
            exit();
        }else{
            include( get_query_template( '404' ) );
            exit;
        }
    }

    /**
     * Time recorded against one note. Only reachable while the Time Tracking
     * add-on is providing the controls; kept here so switching the Private Note
     * add-on off does not take note time editing with it.
     */
    function getTimeByNoteID() {
        $jsst_noteid = JSSTrequest::getVar('val');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-time-by-note-id-'.$jsst_noteid) ) {
            die( 'Security check Failed' );
        }
        if(!is_numeric($jsst_noteid)) return false;
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT time.usertime, time.conflict, time.description,time.systemtime
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_time` AS time
                WHERE time.referencefor = 2 AND time.referenceid = %d",
            $jsst_noteid
        );
        $jsst_stime = jssupportticket::$_db->get_row($jsst_query);
        if(!empty($jsst_stime)){
            $jsst_hours = floor($jsst_stime->usertime / 3600);
            $jsst_mins = floor($jsst_stime->usertime / 60);
            $jsst_mins = floor($jsst_mins % 60);
            $jsst_secs = floor($jsst_stime->usertime % 60);

            $jsst_shours = floor($jsst_stime->systemtime / 3600);
            $jsst_smins = floor($jsst_stime->systemtime / 60);
            $jsst_smins = floor($jsst_smins % 60);
            $jsst_ssecs = floor($jsst_stime->systemtime % 60);
            $jsst_result['time'] =  sprintf('%02d:%02d:%02d', $jsst_hours, $jsst_mins, $jsst_secs);
            $jsst_result['desc'] =  $jsst_stime->description == '' ? ' ' : wp_kses_post($jsst_stime->description) ;
            $jsst_result['desc'] =  jssupportticketphplib::JSST_htmlentities($jsst_result['desc']);
            $jsst_result['conflict'] =  $jsst_stime->conflict;
            $jsst_result['systemtime'] =  sprintf('%02d:%02d:%02d', $jsst_shours, $jsst_smins, $jsst_ssecs);
        }else{
            $jsst_result['time'] =  sprintf('%02d:%02d:%02d', 0, 0, 0);
            $jsst_result['desc'] =  '' ;
            $jsst_result['desc'] =  jssupportticketphplib::JSST_htmlentities($jsst_result['desc']);
            $jsst_result['conflict'] =  0;
            $jsst_result['systemtime'] =  sprintf('%02d:%02d:%02d', 0, 0, 0);
        }
        return wp_json_encode($jsst_result);
    }

    /**
     * Correct the time recorded against a note, with the reason for the change.
     */
    function editTime($jsst_data) {
        if (empty($jsst_data))
            return false;
        if(!isset($jsst_data['note-noteid']) || !is_numeric($jsst_data['note-noteid'])){
            return;
        }
        // conflict resolution handling
        $jsst_up_query = '';
        if(isset($jsst_data['time-confilct']) && $jsst_data['time-confilct'] == 1){
            if(isset($jsst_data['time-confilct-combo']) && $jsst_data['time-confilct-combo'] == 1){
                $jsst_up_query = ' , conflict = 0';
            }
        }
        $jsst_noteid = (int) $jsst_data['note-noteid'];
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_time` WHERE referencefor = 2 AND referenceid = %d",
            $jsst_noteid
        );
        $jsst_id = jssupportticket::$_db->get_var($jsst_query);
        $jsst_edited_time = JSSTrequest::getVar('edited_time');
        $jsst_timearray = jssupportticketphplib::JSST_explode(':', $jsst_edited_time);
        if(!isset($jsst_timearray[0]) || !isset($jsst_timearray[1]) || !isset($jsst_timearray[2])){
            $jsst_seconds = 0;
        }else{
            if(is_numeric($jsst_timearray[0]) && is_numeric($jsst_timearray[1]) && is_numeric($jsst_timearray[2])){
                $jsst_seconds = ($jsst_timearray[0] * 3600) + ($jsst_timearray[1] * 60) + $jsst_timearray[2];
            }else{
                return;
            }
        }
        if($jsst_seconds < 0){
            return;
        }
        $jsst_reason = isset($jsst_data['edit_reason']) ? $jsst_data['edit_reason'] : '';
        if($jsst_id > 0){
            $jsst_query = jssupportticket::$_db->prepare(
                "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_staff_time` SET usertime = %d" . $jsst_up_query . ", description = %s WHERE referencefor = 2 AND referenceid = %d",
                $jsst_seconds,
                $jsst_reason,
                $jsst_noteid
            );
            jssupportticket::$_db->query($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
        }else{
            $jsst_query = jssupportticket::$_db->prepare(
                "SELECT staffid,ticketid FROM `" . jssupportticket::$_db->prefix . "js_ticket_notes` WHERE id = %d",
                $jsst_noteid
            );
            $jsst_note = jssupportticket::$_db->get_row($jsst_query);
            if (empty($jsst_note)) {
                return;
            }
            $jsst_created = date_i18n('Y-m-d H:i:s');
            $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
            $jsst_query_array = array(
                'ticketid' => $jsst_note->ticketid,
                'staffid' => $jsst_note->staffid,
                'referencefor' => 2,
                'referenceid' => $jsst_noteid,
                'usertime' => $jsst_seconds,
                'systemtime' => 0,
                'conflict' => 0,
                'description' => isset($jsst_data['edit_reason']) ? $jsst_data['edit_reason'] : '',
                'status' => 1,
                'created' => $jsst_created
            );
            jssupportticket::$_db->replace(jssupportticket::$_db->prefix . 'js_ticket_staff_time', $jsst_query_array);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

}
