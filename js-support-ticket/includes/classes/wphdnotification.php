<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTwphdnotification {

    function __construct( ) {

    }

    public function addSessionNotificationDataToTable($jsst_message, $jsst_msgtype, $jsst_sessiondatafor = 'notification',$jsst_ticketid = null){
        if($jsst_message == ''){
            if(!is_numeric($jsst_message))
                return false;
        }
        global $wpdb;
        $jsst_data = array();
        $jsst_update = false;
        if(isset($_COOKIE['_wpjshd_session_']) && isset(jssupportticket::$_jshdsession->jsst_sessionid)){
            if($jsst_sessiondatafor == 'notification'){
                $jsst_data = $this->getNotificationDatabySessionId($jsst_sessiondatafor);
                if(empty($jsst_data)){
                    $jsst_data['msg'][0] = $jsst_message;
                    $jsst_data['type'][0] = $jsst_msgtype;
                }else{
                    $jsst_update = true;
                    $jsst_count = count($jsst_data['msg']);
                    $jsst_data['msg'][$jsst_count] = $jsst_message;
                    $jsst_data['type'][$jsst_count] = $jsst_msgtype;
                }
            }elseif($jsst_sessiondatafor == 'submitform'){
                $jsst_data = $this->getNotificationDatabySessionId($jsst_sessiondatafor,true);
                $jsst_data = $jsst_message;
            }elseif($jsst_sessiondatafor == 'ticket_time_start_'){
                $jsst_data = $this->getNotificationDatabySessionId($jsst_sessiondatafor.$jsst_ticketid);
                $jsst_sessiondatafor = $jsst_sessiondatafor.$jsst_ticketid;
                if($jsst_data != ""){
                    $jsst_update = true;
                }
                $jsst_data = $jsst_message;
            }
            if($jsst_sessiondatafor == 'jssupportticket_spamcheckid'){
                $jsst_data = $this->getNotificationDatabySessionId($jsst_sessiondatafor);
                if($jsst_data != ""){
                    $jsst_update = true;
                    $jsst_data = $jsst_message;
                }else{
                    $jsst_data = $jsst_message;
                }
            }
            if($jsst_sessiondatafor == 'jssupportticket_rot13'){
                $jsst_data = $this->getNotificationDatabySessionId($jsst_sessiondatafor);
                if($jsst_data != ""){
                    $jsst_update = true;
                    $jsst_data = $jsst_message;
                }else{
                    $jsst_data = $jsst_message;
                }
            }
            if($jsst_sessiondatafor == 'jssupportticket_spamcheckresult'){
                $jsst_data = $this->getNotificationDatabySessionId($jsst_sessiondatafor);
                if($jsst_data != ""){
                    $jsst_update = true;
                    $jsst_data = $jsst_message;
                }else{
                    $jsst_data = $jsst_message;
                }
            }
            $jsst_data = wp_json_encode($jsst_data , true);
            $jsst_sessionmsg = jssupportticketphplib::JSST_safe_encoding($jsst_data);
            if(!$jsst_update){
                jssupportticket::$_db->insert(
                    jssupportticket::$_db->prefix . "js_ticket_jshdsessiondata",
                    array(
                        "usersessionid" => jssupportticket::$_jshdsession->jsst_sessionid,
                        "sessionmsg"    => $jsst_sessionmsg,
                        "sessionexpire" => jssupportticket::$_jshdsession->jsst_sessionexpire,
                        "sessionfor"    => $jsst_sessiondatafor
                    )
                );
            }else{
                jssupportticket::$_db->update(
                    jssupportticket::$_db->prefix . "js_ticket_jshdsessiondata",
                    array("sessionmsg" => $jsst_sessionmsg),
                    array(
                        "usersessionid" => jssupportticket::$_jshdsession->jsst_sessionid,
                        "sessionfor"    => $jsst_sessiondatafor
                    )
                );
            }
        }
        return false;
    }

    public function getNotificationDatabySessionId($jsst_sessionfor , $jsst_deldata = false){
        if(jssupportticket::$_jshdsession->jsst_sessionid == '')
            return false;
        $jsst_query = "SELECT sessionmsg FROM `" . jssupportticket::$_db->prefix . "js_ticket_jshdsessiondata` WHERE usersessionid = '" . esc_sql(jssupportticket::$_jshdsession->jsst_sessionid) . "' AND sessionfor = '" . esc_sql($jsst_sessionfor) . "' AND sessionexpire > '" . time() . "'";
        $jsst_data = jssupportticket::$_db->get_var($jsst_query);
        if(!empty($jsst_data)){
            $jsst_data = jssupportticketphplib::JSST_safe_decoding($jsst_data);
            $jsst_data = json_decode( $jsst_data , true);
        }
        if($jsst_deldata){
            jssupportticket::$_db->delete(jssupportticket::$_db->prefix . "js_ticket_jshdsessiondata", array( 'usersessionid' => jssupportticket::$_jshdsession->jsst_sessionid , 'sessionfor' => $jsst_sessionfor) );
        }
        return $jsst_data;
    }

}

?>
