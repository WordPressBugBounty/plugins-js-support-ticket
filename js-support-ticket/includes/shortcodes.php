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

    function show_main_ticket($jsst_raw_args, $jsst_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $jsst_defaults = array(
            'jstmod' => '',
            'jstlay' => '',
        );
        $jsst_sanitized_args = shortcode_atts($jsst_defaults, $jsst_raw_args);
        if(isset(jssupportticket::$jsst_data['sanitized_args']) && !empty(jssupportticket::$jsst_data['sanitized_args'])){
            jssupportticket::$jsst_data['sanitized_args'] += $jsst_sanitized_args;
        }else{
            jssupportticket::$jsst_data['sanitized_args'] = $jsst_sanitized_args;
        }
        $jsst_pageid = get_the_ID();
        jssupportticket::setPageID($jsst_pageid);
        JSSTincluder::include_slug('');
        $jsst_content .= ob_get_clean();
        return $jsst_content;
    }

    function show_form_ticket($jsst_raw_args, $jsst_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $jsst_pageid = get_the_ID();
        jssupportticket::setPageID($jsst_pageid);
        $jsst_module = JSSTRequest::getVar('jstmod', '', 'ticket');
        $jsst_layout = JSSTRequest::getVar('jstlay', '', 'addticket');
        if ($jsst_layout != 'addticket' && $jsst_layout != 'staffaddticket') {
            JSSTincluder::include_file($jsst_module);
        } else {
            $jsst_defaults = array(
                'job_type' => '',
                'city' => '',
                'company' => '',
            );
            $jsst_sanitized_args = shortcode_atts($jsst_defaults, $jsst_raw_args);
            if(isset(jssupportticket::$jsst_data['sanitized_args']) && !empty(jssupportticket::$jsst_data['sanitized_args'])){
                jssupportticket::$jsst_data['sanitized_args'] += $jsst_sanitized_args;
            }else{
                jssupportticket::$jsst_data['sanitized_args'] = $jsst_sanitized_args;
            }
            jssupportticket::$jsst_data['short_code_header'] = 'addticket';
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                $jsst_id = JSSTrequest::getVar('jssupportticketid');
                $jsst_per_task = ($jsst_id == null) ? 'Add Ticket' : 'Edit Ticket';
                jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
                if (jssupportticket::$jsst_data['permission_granted']) {
                    JSSTincluder::getJSModel('ticket')->getTicketsForForm($jsst_id);
                }
                JSSTincluder::include_file('staffaddticket', 'agent');
            } else {
                JSSTincluder::getJSModel('ticket')->getTicketsForForm(null);
                JSSTincluder::include_file('addticket', 'ticket');
            }
        }
        $jsst_content .= ob_get_clean();
        return $jsst_content;
    }

    function show_form_ticket_for_multiform($jsst_raw_args, $jsst_content = null) {
        $jsst_formid = $jsst_raw_args['formid'];
        //default set of parameters for the front end shortcodes
        ob_start();
        $jsst_pageid = get_the_ID();
        jssupportticket::setPageID($jsst_pageid);
        $jsst_module = JSSTRequest::getVar('jstmod', '', 'ticket');
        $jsst_layout = JSSTRequest::getVar('jstlay', '', 'addticket');
        if ($jsst_layout != 'addticket' && $jsst_layout != 'staffaddticket') {
            JSSTincluder::include_file($jsst_module);
        } else {
            $jsst_defaults = array(
                'job_type' => '',
                'city' => '',
                'company' => '',
            );
            $jsst_sanitized_args = shortcode_atts($jsst_defaults, $jsst_raw_args);
            if(isset(jssupportticket::$jsst_data['sanitized_args']) && !empty(jssupportticket::$jsst_data['sanitized_args'])){
                jssupportticket::$jsst_data['sanitized_args'] += $jsst_sanitized_args;
            }else{
                jssupportticket::$jsst_data['sanitized_args'] = $jsst_sanitized_args;
            }
            jssupportticket::$jsst_data['short_code_header'] = 'addticket';
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                $jsst_id = JSSTrequest::getVar('jssupportticketid');
                $jsst_per_task = ($jsst_id == null) ? 'Add Ticket' : 'Edit Ticket';
                jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
                if (jssupportticket::$jsst_data['permission_granted']) {
                    JSSTincluder::getJSModel('ticket')->getTicketsForForm($jsst_id, $jsst_formid);
                }
                JSSTincluder::include_file('staffaddticket', 'agent');
            } else {
                JSSTincluder::getJSModel('ticket')->getTicketsForForm(null, $jsst_formid);
                JSSTincluder::include_file('addticket', 'ticket');
            }
        }
        $jsst_content .= ob_get_clean();
        return $jsst_content;
    }

    function show_my_ticket($jsst_raw_args, $jsst_content = null) {
        //default set of parameters for the front end shortcodes
        ob_start();
        $jsst_pageid = get_the_ID();
        jssupportticket::setPageID($jsst_pageid);
        $jsst_module = JSSTRequest::getVar('jstmod', '', 'ticket');
        $jsst_layout = JSSTRequest::getVar('jstlay', '', 'myticket');
        if ($jsst_layout != 'myticket' && $jsst_layout != 'staffmyticket') {
            JSSTincluder::include_file($jsst_module);
        } else {
            $jsst_defaults = array(
                'list' => '',
                'ticketid' => '',
            );
            $jsst_list = JSSTrequest::getVar('list', 'get', null);
            $jsst_ticketid = JSSTrequest::getVar('ticketid', null, null);
            $jsst_args = shortcode_atts($jsst_defaults, $jsst_raw_args);
            if(isset(jssupportticket::$jsst_data['sanitized_args']) && !empty(jssupportticket::$jsst_data['sanitized_args'])){
                jssupportticket::$jsst_data['sanitized_args'] += $jsst_args;
            }else{
                jssupportticket::$jsst_data['sanitized_args'] = $jsst_args;
            }
            if ($jsst_list == null)
                $jsst_list = $jsst_args['list'];
            if ($jsst_ticketid == null)
                $jsst_ticketid = $jsst_args['ticketid'];
            jssupportticket::$jsst_data['short_code_header'] = 'myticket';
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                JSSTincluder::getJSModel('ticket')->getStaffTickets();
                JSSTincluder::include_file('staffmyticket', 'agent');
            } else {
                JSSTincluder::getJSModel('ticket')->getMyTickets($jsst_list, $jsst_ticketid);
                JSSTincluder::include_file('myticket', 'ticket');
            }
        }
        $jsst_content .= ob_get_clean();
        return $jsst_content;
    }

}

$jsst_shortcodes = new JSSTshortcodes();
?>
