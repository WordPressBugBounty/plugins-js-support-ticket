<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTincluder {

    function __construct() {

    }

    /*
     * Includes files
     */

    public static function include_file($jsst_filename, $jsst_module_name = null) {
        $jsst_filename = jssupportticketphplib::JSST_clean_file_path($jsst_filename);
        $jsst_module_name = jssupportticketphplib::JSST_clean_file_path($jsst_module_name);
        if ($jsst_module_name != null) {
            $jsst_file_path = JSSTincluder::getPluginPath($jsst_module_name,'file',$jsst_filename);
            if (file_exists(JSST_PLUGIN_PATH . 'includes/css/inc-css/' . $jsst_module_name . '-' . $jsst_filename . '.css.php')) {
                require_once(JSST_PLUGIN_PATH . 'includes/css/inc-css/' . $jsst_module_name . '-' . $jsst_filename . '.css.php');
            }
            //include_once $jsst_path . 'modules/' . $jsst_module_name . '/tpls/' . $jsst_filename . '.php';
			if (locate_template('js-support-ticket/' . $jsst_module_name . '-' . $jsst_filename . '.php', 1, 1)) {
			   return;
			}

            if(file_exists($jsst_file_path)){
                include_once $jsst_file_path; //
            }else{
                $jsst_file_path = JSSTincluder::getPluginPath('premiumplugin','file','missingaddon');
                include_once $jsst_file_path; //
            }
        } else {
            $jsst_file_path = JSSTincluder::getPluginPath($jsst_filename,'file');
            if(file_exists($jsst_file_path)){
                include_once $jsst_file_path; //
            }else{
                $jsst_file_path = JSSTincluder::getPluginPath('premiumplugin','file');
                include_once $jsst_file_path; //
            }
        }
        return;
    }

    /*
     * Static function to handle the page slugs
     */

    public static function include_slug($jsst_page_slug) {
        include_once JSST_PLUGIN_PATH . 'modules/js-support-ticket-controller.php';
    }

    /*
     * Static function for the model object
     */

    public static function getJSModel($jsst_modelname) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_modelname,'model');
        include_once $jsst_file_path;
        $jsst_classname = "JSST" . $jsst_modelname . 'Model';
        $jsst_obj = new $jsst_classname();
        return $jsst_obj;
    }

    /*
     * Static function for the classes objects
     */

    public static function getObjectClass($jsst_classname) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_classname,'class');

        include_once $jsst_file_path;
        $jsst_classname = 'JSST'.$jsst_classname;
        $jsst_obj = new $jsst_classname();
        return $jsst_obj;
    }

    public static function getClassesInclude($jsst_classname) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_classname,'class');
        include_once $jsst_file_path;
    }

    /*
     * Static function for the controller object
     */

    public static function getJSController($jsst_controllername) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_controllername,'controller');

        include_once $jsst_file_path;
        $jsst_classname = "JSST".$jsst_controllername . "Controller";
        $jsst_obj = new $jsst_classname();
        return $jsst_obj;
    }

    /*
     * Static function for the Table Class Object
     */

    public static function getJSTable($jsst_tableclass) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_tableclass,'table');
        require_once JSST_PLUGIN_PATH . 'includes/tables/table.php';
        include_once $jsst_file_path;
        $jsst_classname = "JSST" . $jsst_tableclass . 'Table';
        $jsst_obj = new $jsst_classname();
        return $jsst_obj;
    }

    /*
     *  Identify file path to include or require this fucntion helps to accommodate addon calls
     */

    public static function getPluginPath($jsst_module,$jsst_type,$jsst_file_name = '') {
        $jsst_module = jssupportticketphplib::JSST_clean_file_path($jsst_module);
        $jsst_file_name = jssupportticketphplib::JSST_clean_file_path($jsst_file_name);

        $jsst_addons_secondry = array('articles','articleattachmet','banemaillog','downloadattachment','roleaccessdepartments','rolepermissions','useraccessdepartments','userpermissions', 'role', 'acl_roles', 'acl_role_access_departments', 'acl_role_permissions', 'categories' ,'email_banlist', 'acl_user_access_departments','articles_attachments','email_banlist','acl_user_permissions', 'facebook', 'linkedin','socialUser');
		$jsst_new_addon_entry = "";
		$jsst_new_addon_entry = apply_filters('jsst_ticket_include_thirdparty_addon_in_array',$jsst_addons_secondry);
		if($jsst_new_addon_entry){
			$jsst_addons_secondry[] = $jsst_new_addon_entry;
		}
		$jsst_new_addon_layoutname = "";
		$jsst_new_addon_layoutname = apply_filters('jsst_ticket_include_thirdparty_addon_layoutname',false);

        if(in_array($jsst_module, jssupportticket::$_active_addons)){
            $jsst_path = WP_PLUGIN_DIR.'/'.'js-support-ticket-'.$jsst_module.'/';
            switch ($jsst_type) {
                case 'file':
                    if($jsst_file_name != ''){
                        $jsst_file_path = $jsst_path . 'module/tpls/' . $jsst_file_name . '.php';
                    }else{
                        $jsst_file_path = $jsst_path . 'module/controller.php';
                    }
                    break;
                case 'model':
                    $jsst_file_path = $jsst_path . 'module/model.php';
                    break;
                case 'class':
                    $jsst_file_path = $jsst_path . 'classes/' . $jsst_module . '.php';
                    break;
                case 'controller':
                    $jsst_file_path = $jsst_path . 'module/controller.php';
                    break;
                case 'table':
                    $jsst_file_path = $jsst_path . 'includes/' . $jsst_module . '-table.php';
                    break;
            }

        }elseif(in_array($jsst_module, $jsst_addons_secondry)){ // to handle the case of modules that are submodules for some addon
            $jsst_parent_module = '';
            switch ($jsst_module) {// to identify addon for submodules.
                case 'articles':
                case 'articleattachmet':
                case 'articles_attachments':
                case 'categories':
                    $jsst_parent_module = 'knowledgebase';
                    break;
                case 'banemaillog':
                case 'email_banlist':
                case 'email_banlist':
                    $jsst_parent_module = 'banemail';
                    break;
                case 'downloadattachment':
                    $jsst_parent_module = 'download';
                    break;
                case 'roleaccessdepartments':
                case 'rolepermissions':
                case 'useraccessdepartments':
                case 'userpermissions':
                case 'role':
                case 'acl_roles':
                case 'acl_role_access_departments':
                case 'acl_user_access_departments':
                case 'acl_role_permissions':
                case 'acl_user_permissions':
                    $jsst_parent_module = 'agent';
                    break;
                case 'facebook':
                case 'linkedin':
                case 'socialUser':
                    $jsst_parent_module = 'sociallogin';
                    break;
                case $jsst_new_addon_entry:
                    $jsst_parent_module = $jsst_new_addon_layoutname;
            }

            $jsst_path = WP_PLUGIN_DIR.'/'.'js-support-ticket-'.$jsst_parent_module.'/';
            if(in_array($jsst_parent_module, jssupportticket::$_active_addons)){
                switch ($jsst_type) {
                    case 'file':
                        if($jsst_file_name != ''){
                            $jsst_file_path = $jsst_path . $jsst_module.'/tpls/' . $jsst_file_name . '.php';
                        }else{
                            $jsst_file_path = $jsst_path . $jsst_module.'/controller.php';
                        }
                        break;
                    case 'model':
                        $jsst_file_path = $jsst_path . $jsst_module.'/model.php';
                        break;

                    case 'class':
                        $jsst_file_path = $jsst_path . 'classes/' . $jsst_module . '.php';
                        break;
                    case 'controller':
                        $jsst_file_path = $jsst_path . $jsst_module.'/controller.php';
                        break;
                    case 'table':
                        $jsst_file_path = $jsst_path . 'includes/' . $jsst_module . '-table.php';
                        break;
                }
            }else{
                $jsst_file_path = JSSTincluder::getPluginPath('premiumplugin','file');
            }
        }else{
            $jsst_path = JSST_PLUGIN_PATH;
            switch ($jsst_type) {
                case 'file':
                    if($jsst_file_name != ''){
                        $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/tpls/' . $jsst_file_name . '.php';
                    }else{
                        $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/controller.php';
                    }
                    break;
                case 'model':
                        $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/model.php';
                    break;

                case 'class':
                    $jsst_file_path = $jsst_path . 'includes/classes/' . $jsst_module . '.php';
                    break;
                case 'controller':
                        $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/controller.php';
                    break;
                case 'table':
                    $jsst_file_path = $jsst_path . 'includes/tables/' . $jsst_module . '.php';;
                    break;
            }
        }
        return $jsst_file_path;
    }

}

$jsst_includer = new JSSTincluder();
?>
