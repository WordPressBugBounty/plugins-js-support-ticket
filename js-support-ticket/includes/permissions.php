<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpermissions {

    static function checkPermission($userid, $permissionfor) {
        if(!is_numeric($userid)){
            return false;
        }
        $query = "SELECT perm_allowed.status
					FROM `" . jsjobs::$_db->prefix . "jsjobs_permissions` AS perm
					JOIN `" . jsjobs::$_db->prefix . "jsjobs_permissions_allowed` AS perm_allowed ON perm_allowed.permissionid = perm.id
					WHERE perm.permissions = '".esc_sql($permissionfor)."' AND perm_allowed.userid = ".esc_sql($userid);
        $result = jsjobs::$_db->get_var($query);
        return $result;
    }

}

?>
