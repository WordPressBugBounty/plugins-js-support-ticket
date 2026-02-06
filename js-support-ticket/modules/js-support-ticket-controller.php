<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class jssupportticketController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_module = JSSTrequest::getVar('jstmod', null, 'jssupportticket');
        JSSTincluder::include_file($jsst_module);
    }

}

$jsst_jssupportticketController = new jssupportticketController();
?>
