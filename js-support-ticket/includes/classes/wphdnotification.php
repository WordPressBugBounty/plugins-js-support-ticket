<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTwphdnotification {

    function __construct( ) {

    }

    public function addSessionNotificationDataToTable($message, $msgtype, $sessiondatafor = 'notification',$ticketid = null){
        if($message == ''){
            if(!is_numeric($message))
                return false;
        }
        global $wpdb;
        $data = array();
        $update = false;
        if(isset($_COOKIE['_wpjshd_session_']) && isset(jssupportticket::$_jshdsession->sessionid)){
            if($sessiondatafor == 'notification'){
                $data = $this->getNotificationDatabySessionId($sessiondatafor);
                if(empty($data)){
                    $data['msg'][0] = $message;
                    $data['type'][0] = $msgtype;
                }else{
                    $update = true;
                    $count = count($data['msg']);
                    $data['msg'][$count] = $message;
                    $data['type'][$count] = $msgtype;
                }
            }elseif($sessiondatafor == 'submitform'){
                $data = $this->getNotificationDatabySessionId($sessiondatafor,true);
                $data = $message;
            }elseif($sessiondatafor == 'ticket_time_start_'){
                $data = $this->getNotificationDatabySessionId($sessiondatafor.$ticketid);
                $sessiondatafor = $sessiondatafor.$ticketid;
                if($data != ""){
                    $update = true;
                }
                $data = $message;
            }
            if($sessiondatafor == 'jssupportticket_spamcheckid'){
                $data = $this->getNotificationDatabySessionId($sessiondatafor);
                if($data != ""){
                    $update = true;
                    $data = $message;
                }else{
                    $data = $message;
                }
            }
            if($sessiondatafor == 'jssupportticket_rot13'){
                $data = $this->getNotificationDatabySessionId($sessiondatafor);
                if($data != ""){
                    $update = true;
                    $data = $message;
                }else{
                    $data = $message;
                }
            }
            if($sessiondatafor == 'jssupportticket_spamcheckresult'){
                $data = $this->getNotificationDatabySessionId($sessiondatafor);
                if($data != ""){
                    $update = true;
                    $data = $message;
                }else{
                    $data = $message;
                }
            }
            $data = wp_json_encode($data , true);
            $sessionmsg = jssupportticketphplib::JSST_safe_encoding($data);
            if(!$update){
                $wpdb->insert( "{$wpdb->prefix}js_ticket_jshdsessiondata", array("usersessionid" => jssupportticket::$_jshdsession->sessionid, "sessionmsg" => $sessionmsg, "sessionexpire" => jssupportticket::$_jshdsession->sessionexpire, "sessionfor" => $sessiondatafor) );
            }else{
                $wpdb->update( "{$wpdb->prefix}js_ticket_jshdsessiondata", array("sessionmsg" => $sessionmsg), array("usersessionid" => jssupportticket::$_jshdsession->sessionid , 'sessionfor' => $sessiondatafor) );
            }
        }
        return false;
    }

    public function getNotificationDatabySessionId($sessionfor , $deldata = false){
        if(jssupportticket::$_jshdsession->sessionid == '')
            return false;
        $query = "SELECT sessionmsg FROM `" . jssupportticket::$_db->prefix . "js_ticket_jshdsessiondata` WHERE usersessionid = '" . esc_sql(jssupportticket::$_jshdsession->sessionid) . "' AND sessionfor = '" . esc_sql($sessionfor) . "' AND sessionexpire > '" . time() . "'";
        $data = jssupportticket::$_db->get_var($query);
        if(!empty($data)){
            $data = jssupportticketphplib::JSST_safe_decoding($data);
            $data = json_decode( $data , true);
        }
        if($deldata){
            jssupportticket::$_db->delete(jssupportticket::$_db->prefix . "js_ticket_jshdsessiondata", array( 'usersessionid' => jssupportticket::$_jshdsession->sessionid , 'sessionfor' => $sessionfor) );
        }
        return $data;
    }

}

?>
