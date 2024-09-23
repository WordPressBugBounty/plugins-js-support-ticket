<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTdeactivation {

    static function jssupportticket_deactivate() {
      wp_clear_scheduled_hook('jssupporticket_updateticketstatus');
      wp_clear_scheduled_hook('jssupporticket_ticketviaemail');
      $timestamp = wp_next_scheduled( 'jsst_delete_expire_session_data' );
      wp_unschedule_event( $timestamp, 'jsst_delete_expire_session_data' );
      $id = jssupportticket::getPageid();
      jssupportticket::$_db->get_var("UPDATE `" . jssupportticket::$_db->prefix . "posts` SET post_status = 'draft' WHERE ID = ".esc_sql($id));

      //Delete capabilities
      $role = get_role( 'administrator' );
      $role->remove_cap( 'jsst_support_ticket' );
    }

    static function jssupportticket_tables_to_drop() {
        global $wpdb;
        $tables = array(
           $wpdb->prefix."js_ticket_fieldsordering",
           $wpdb->prefix."js_ticket_faqs",
           $wpdb->prefix."js_ticket_departments",
           $wpdb->prefix."js_ticket_attachments",
           $wpdb->prefix."js_ticket_config",
           $wpdb->prefix."js_ticket_email",
           $wpdb->prefix."js_ticket_emailtemplates",
           $wpdb->prefix."js_ticket_priorities",
           $wpdb->prefix."js_ticket_replies",
           $wpdb->prefix."js_ticket_system_errors",
           $wpdb->prefix."js_ticket_tickets",
           $wpdb->prefix."js_ticket_erasedatarequests",
           $wpdb->prefix."js_ticket_users",
           $wpdb->prefix."js_ticket_multiform",
           $wpdb->prefix."js_ticket_slug",
        );
        return $tables;
    }

}

?>
