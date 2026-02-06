<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTformhandler {

    function __construct() {
        add_action('init', array($this, 'checkFormRequest'));
        add_action('init', array($this, 'checkDeleteRequest'));
    }

    /*
     * Handle Form request
     */

    function checkFormRequest() {
        $jsst_formrequest = JSSTrequest::getVar('form_request', 'post');
        if ($jsst_formrequest == 'jssupportticket') {
            //handle the request
            $jsst_page_id = JSSTRequest::getVar('page_id', 'GET');
            jssupportticket::setPageID($jsst_page_id);
            $jsst_modulename = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_modulename);
            JSSTincluder::include_file($jsst_module);
            $jsst_class = 'JSST' . $jsst_module . "Controller";
            $jsst_task = JSSTrequest::getVar('task');
            $jsst_obj = new $jsst_class;
            $jsst_obj->$jsst_task();
        }
    }

    /*
     * Handle Form request
     */

    function checkDeleteRequest() {
        $jsst_jssupportticket_action = JSSTrequest::getVar('action', 'get');
        if ($jsst_jssupportticket_action == 'jstask') {
            //handle the request
            $jsst_page_id = JSSTRequest::getVar('page_id', 'GET');
            jssupportticket::setPageID($jsst_page_id);
            $jsst_modulename = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_modulename,'','');
            if($jsst_module != ''){
                JSSTincluder::include_file($jsst_module);
                $jsst_class = 'JSST' . $jsst_module . "Controller";
                $jsst_action = JSSTrequest::getVar('task');
                $jsst_obj = new $jsst_class;
                $jsst_obj->$jsst_action();
            }else{
                error_log( print_r( $_REQUEST, true ) );// temporary code to get the case when problem occurs(there are errors in log but no way to find the case that causes them)
            }
        }
    }

}

$jsst_formhandler = new JSSTformhandler();
?>
