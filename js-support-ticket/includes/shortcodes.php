<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTshortcodes {

    function __construct() {
        add_shortcode('jssupportticket', array($this, 'show_main_ticket'));
        add_shortcode('jssupportticket_addticket', array($this, 'show_form_ticket'));
        if( in_array('multiform', jssupportticket::$_active_addons) ){
            add_shortcode('jssupportticket_addticket_multiform', array($this, 'show_form_ticket_for_multiform'));
        }
        add_shortcode('jssupportticket_mytickets', array($this, 'show_my_ticket'));
    }

    function show_main_ticket($raw_args, $content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $defaults = array(
            'jstmod' => '',
            'jstlay' => '',
        );
        $sanitized_args = shortcode_atts($defaults, $raw_args);
        if(isset(jssupportticket::$_data['sanitized_args']) && !empty(jssupportticket::$_data['sanitized_args'])){
            jssupportticket::$_data['sanitized_args'] += $sanitized_args;
        }else{
            jssupportticket::$_data['sanitized_args'] = $sanitized_args;
        }
        $pageid = get_the_ID();
        jssupportticket::setPageID($pageid);
        JSSTincluder::include_slug('');
        $content .= ob_get_clean();
        return $content;
    }

    function show_form_ticket($raw_args, $content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $pageid = get_the_ID();
        jssupportticket::setPageID($pageid);
        $module = JSSTRequest::getVar('jstmod', '', 'ticket');
        $layout = JSSTRequest::getVar('jstlay', '', 'addticket');
        if ($layout != 'addticket' && $layout != 'staffaddticket') {
            JSSTincluder::include_file($module);
        } else {
            $defaults = array(
                'job_type' => '',
                'city' => '',
                'company' => '',
            );
            $sanitized_args = shortcode_atts($defaults, $raw_args);
            if(isset(jssupportticket::$_data['sanitized_args']) && !empty(jssupportticket::$_data['sanitized_args'])){
                jssupportticket::$_data['sanitized_args'] += $sanitized_args;
            }else{
                jssupportticket::$_data['sanitized_args'] = $sanitized_args;
            }
            jssupportticket::$_data['short_code_header'] = 'addticket';
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                $id = JSSTrequest::getVar('jssupportticketid');
                $per_task = ($id == null) ? 'Add Ticket' : 'Edit Ticket';
                jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($per_task);
                if (jssupportticket::$_data['permission_granted']) {
                    JSSTincluder::getJSModel('ticket')->getTicketsForForm($id);
                }
                JSSTincluder::include_file('staffaddticket', 'agent');
            } else {
                JSSTincluder::getJSModel('ticket')->getTicketsForForm(null);
                JSSTincluder::include_file('addticket', 'ticket');
            }
        }
        $content .= ob_get_clean();
        return $content;
    }

    function show_form_ticket_for_multiform($raw_args, $content = null) {
        $formid = $raw_args['formid'];
        //default set of parameters for the front end shortcodes
        ob_start();
        $pageid = get_the_ID();
        jssupportticket::setPageID($pageid);
        $module = JSSTRequest::getVar('jstmod', '', 'ticket');
        $layout = JSSTRequest::getVar('jstlay', '', 'addticket');
        if ($layout != 'addticket' && $layout != 'staffaddticket') {
            JSSTincluder::include_file($module);
        } else {
            $defaults = array(
                'job_type' => '',
                'city' => '',
                'company' => '',
            );
            $sanitized_args = shortcode_atts($defaults, $raw_args);
            if(isset(jssupportticket::$_data['sanitized_args']) && !empty(jssupportticket::$_data['sanitized_args'])){
                jssupportticket::$_data['sanitized_args'] += $sanitized_args;
            }else{
                jssupportticket::$_data['sanitized_args'] = $sanitized_args;
            }
            jssupportticket::$_data['short_code_header'] = 'addticket';
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                $id = JSSTrequest::getVar('jssupportticketid');
                $per_task = ($id == null) ? 'Add Ticket' : 'Edit Ticket';
                jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($per_task);
                if (jssupportticket::$_data['permission_granted']) {
                    JSSTincluder::getJSModel('ticket')->getTicketsForForm($id, $formid);
                }
                JSSTincluder::include_file('staffaddticket', 'agent');
            } else {
                JSSTincluder::getJSModel('ticket')->getTicketsForForm(null, $formid);
                JSSTincluder::include_file('addticket', 'ticket');
            }
        }
        $content .= ob_get_clean();
        return $content;
    }

    function show_my_ticket($raw_args, $content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $pageid = get_the_ID();
        jssupportticket::setPageID($pageid);
        $module = JSSTRequest::getVar('jstmod', '', 'ticket');
        $layout = JSSTRequest::getVar('jstlay', '', 'myticket');
        if ($layout != 'myticket' && $layout != 'staffmyticket') {
            JSSTincluder::include_file($module);
        } else {
            $defaults = array(
                'list' => '',
                'ticketid' => '',
            );
            $list = JSSTrequest::getVar('list', 'get', null);
            $ticketid = JSSTrequest::getVar('ticketid', null, null);
            $args = shortcode_atts($defaults, $raw_args);
            if(isset(jssupportticket::$_data['sanitized_args']) && !empty(jssupportticket::$_data['sanitized_args'])){
                jssupportticket::$_data['sanitized_args'] += $args;
            }else{
                jssupportticket::$_data['sanitized_args'] = $args;
            }
            if ($list == null)
                $list = $args['list'];
            if ($ticketid == null)
                $ticketid = $args['ticketid'];
            jssupportticket::$_data['short_code_header'] = 'myticket';
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                JSSTincluder::getJSModel('ticket')->getStaffTickets();
                JSSTincluder::include_file('staffmyticket', 'agent');
            } else {
                JSSTincluder::getJSModel('ticket')->getMyTickets($list, $ticketid);
                JSSTincluder::include_file('myticket', 'ticket');
            }
        }
        $content .= ob_get_clean();
        return $content;
    }

}

$shortcodes = new JSSTshortcodes();
?>
