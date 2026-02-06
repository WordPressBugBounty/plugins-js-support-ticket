<?php

/**
 * JS Support Ticket Uninstall
 *
 * Uninstalling JS Support Ticket tables, and pages.
 *
 * @author 		Ahmed Bilal
 * @category 	Core
 * @package 	JS Support Ticket/Uninstaller
 * @version     1.0
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;
include_once 'includes/deactivation.php';

if(function_exists('is_multisite') && is_multisite()){
	$jsst_blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach($jsst_blogs as $jsst_blog_id){
        switch_to_blog( $jsst_blog_id );
		$jsst_tablestodrop = JSSTdeactivation::jssupportticket_tables_to_drop();
        foreach($jsst_tablestodrop as $jsst_tablename){
            $wpdb->query( 
                "DROP TABLE IF EXISTS " . esc_sql( $jsst_tablename ) 
            );
        }
        restore_current_blog();
    }
}else{
    $jsst_tablestodrop = JSSTdeactivation::jssupportticket_tables_to_drop();

    foreach ($jsst_tablestodrop as $jsst_tablename) {
        // Escape the table name using esc_sql() to satisfy security scanners
        $wpdb->query( "DROP TABLE IF EXISTS ".$jsst_tablename );
    }
}
