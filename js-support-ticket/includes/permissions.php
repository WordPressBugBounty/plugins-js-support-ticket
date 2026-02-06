<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpermissions {

    static function checkPermission($jsst_userid, $jsst_permissionfor) {
        if(!is_numeric($jsst_userid)){
            return false;
        }
        $jsst_query = "SELECT perm_allowed.status
					FROM `" . jsjobs::$_db->prefix . "jsjobs_permissions` AS perm
					JOIN `" . jsjobs::$_db->prefix . "jsjobs_permissions_allowed` AS perm_allowed ON perm_allowed.permissionid = perm.id
					WHERE perm.permissions = '".esc_sql($jsst_permissionfor)."' AND perm_allowed.userid = ".esc_sql($jsst_userid);
        $jsst_result = jsjobs::$_db->get_var($jsst_query);
        return $jsst_result;
    }

}

?>
