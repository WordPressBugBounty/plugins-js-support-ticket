<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTslugModel {

    private $_params_flag;
    private $_params_string;

    function __construct() {
        $this->_params_flag = 0;
    }

    function getSlug() {
        // Filter
        $slug = jssupportticket::$_search['slug']['slug'];

        $inquery = '';
        if ($slug != null){
            $inquery .= " AND slug.slug LIKE '%".esc_sql($slug)."%'";
        }
        jssupportticket::$_data['slug'] = $slug;

        // Pagination
        $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_slug AS slug WHERE slug.status = 1 ";
        $query .= $inquery;
        $total = jssupportticket::$_db->get_var($query);

        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        //Data
        $query = "SELECT *
                  FROM ".jssupportticket::$_db->prefix ."js_ticket_slug AS slug WHERE slug.status = 1 ";
        $query .= $inquery;
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);

        return;
    }


    function storeSlug($data) {
        if (empty($data)) {
            return false;
        }
        $row = JSSTincluder::getJSTable('slug');
        foreach ($data as $id => $slug) {
            if($id != '' && is_numeric($id)){
                $slug = sanitize_title($slug);
                if($slug != ''){
                    $query = "SELECT COUNT(id) FROM " . jssupportticket::$_db->prefix . "js_ticket_slug
                            WHERE slug = '" . esc_sql($slug)."' ";
                    $slug_flag = jssupportticket::$_db->get_var($query);
                    if($slug_flag > 0){
                        continue;
                    }else{
                        $row->update(array('id' => $id, 'slug' => $slug));
                    }
                }
            }
        }
        update_option('rewrite_rules', '');
        JSSTmessage::setMessage(esc_html(__('Slug(s) has been stored', 'js-support-ticket')), 'updated');
        return;
    }

    function savePrefix($data) {
        if (empty($data)) {
            return false;
        }
        $data['prefix'] = ($data['prefix']);
        if($data['prefix'] == ''){
            JSSTmessage::setMessage(esc_html(__('Prefix has not been stored', 'js-support-ticket')), 'error');
            return;
        }
        $query = "UPDATE " . jssupportticket::$_db->prefix . "js_ticket_config
                    SET configvalue = '".esc_sql($data['prefix'])."'
                    WHERE configname = 'slug_prefix'";
        if(jssupportticket::$_db->query($query)){
            update_option('rewrite_rules', '');
            JSSTmessage::setMessage(esc_html(__('Prefix has been stored', 'js-support-ticket')), 'updated');
            return;
        }else{
            update_option('rewrite_rules', '');
        	JSSTmessage::setMessage(esc_html(__('Prefix has not been stored', 'js-support-ticket')), 'error');
            return;
        }
    }

    function saveHomePrefix($data) {
        if (empty($data)) {
            return false;
        }
        $data['prefix'] = ($data['prefix']);
        if($data['prefix'] == ''){
            JSSTmessage::setMessage(esc_html(__('Prefix has not been stored', 'js-support-ticket')), 'error');
            return;
        }
        $query = "UPDATE " . jssupportticket::$_db->prefix . "js_ticket_config
                    SET configvalue = '".esc_sql($data['prefix'])."'
                    WHERE configname = 'home_slug_prefix'";
        if(jssupportticket::$_db->query($query)){
            update_option('rewrite_rules', '');
            JSSTmessage::setMessage(esc_html(__('Prefix has been stored', 'js-support-ticket')), 'updated');
            return;
        }else{
            update_option('rewrite_rules', '');
            JSSTmessage::setMessage(esc_html(__('Prefix has not been stored', 'js-support-ticket')), 'error');
            return;
        }
    }

    function resetAllSlugs() {
        $query = "UPDATE " . jssupportticket::$_db->prefix . "js_ticket_slug
                    SET slug = defaultslug ";
        if(jssupportticket::$_db->query($query)){
            update_option('rewrite_rules', '');
            JSSTmessage::setMessage(esc_html(__('Slug(s) has been stored', 'js-support-ticket')), 'updated');
            return;
        }else{
            update_option('rewrite_rules', '');
            JSSTmessage::setMessage(esc_html(__('Slug(s) has been stored', 'js-support-ticket')), 'updated');
            return;
        }
    }

    function getOptionsForEditSlug() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-options-for-edit-slug') ) {
            die( 'Security check Failed' );
        }
        $slug = JSSTrequest::getVar('slug');
        $html = '<span class="userpopup-top">
                    <span id="userpopup-heading" class="userpopup-heading" >' . esc_html(__("Edit","js-support-ticket"))." ". esc_html(__("Slug", "js-support-ticket")) . '</span>
                        <img alt="'. esc_html(__("Close","js-support-ticket")).'" onClick="closePopup();" class="userpopup-close" src="'. JSST_PLUGIN_URL.'includes/images/close-icon-white.png" />
                    </span>';
        $html .= '<div class="userpopup-search">
                    <div class="popup-field-title">' . esc_html(__('Slug','js-support-ticket')).' '. esc_html(__('Name','js-support-ticket')) . ' <span style="color: red;"> *</span></div>
                         <div class="popup-field-obj">' . JSSTformfield::text('slugedit', isset($slug) ? jssupportticketphplib::JSST_trim($slug) : 'text', '', array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
        $html .='<div class="popup-act-btn-wrp">
                    ' . JSSTformfield::button('save', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button savebutton popup-act-btn','onClick'=>'getFieldValue();'));
        $html .='</div>';
        $html = jssupportticketphplib::JSST_htmlentities($html);
        return json_encode($html);
    }

    function getDefaultSlugFromSlug($layout) {
        $query = "SELECT  defaultslug FROM `".jssupportticket::$_db->prefix."js_ticket_slug` WHERE defaultslug = '".esc_sql($layout)."'";
        $val = jssupportticket::$_db->get_var($query);
        return sanitize_title($val);
    }

    function getSlugFromFileName($layout,$module) {
        $query = "SELECT slug FROM `".jssupportticket::$_db->prefix."js_ticket_slug` WHERE filename = '".esc_sql($layout)."'";
        $val = jssupportticket::$_db->get_var($query);
        return $val;
    }

    function getSlugString($home_page = 0) {
        global $wp_rewrite;
        $rules = json_encode($wp_rewrite->rules);
        $query = "SELECT slug AS value FROM `".jssupportticket::$_db->prefix."js_ticket_slug`";
        $val = jssupportticket::$_db->get_results($query);
        $string = '';
        $bstring = '';
        //$rules = json_encode($rules);
        $prefix = JSSTincluder::getJSModel('configuration')->getConfigValue('slug_prefix');
        $homeprefix = JSSTincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
        foreach ($val as $slug) {
            if($home_page == 1){
                $slug->value = $homeprefix.$slug->value;
            }
            if(strpos($rules,$slug->value) === false){
                $string .= $bstring. $slug->value;
            }else{
                $string .= $bstring.$prefix. $slug->value;
            }
            $bstring = '|';
        }
        return $string;
    }

    function getRedirectCanonicalArray() {
        global $wp_rewrite;
        $slug_prefix = JSSTincluder::getJSModel('configuration')->getConfigValue('slug_prefix');
        $homeprefix = JSSTincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
        $rules = json_encode($wp_rewrite->rules);
        $query = "SELECT slug AS value FROM `".jssupportticket::$_db->prefix."js_ticket_slug`";
        $val = jssupportticket::$_db->get_results($query);
        $string = array();
        $bstring = '';
        foreach ($val as $slug) {
            $slug->value = $homeprefix.$slug->value;
            $string[] = $bstring.$slug->value;
            $bstring = '/';
        }
        return $string;
    }

    function getAdminSearchFormDataSlug(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'slug') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['slug'] = JSSTrequest::getVar('slug');
        $jsst_search_array['search_from_slug'] = 1;
        return $jsst_search_array;
    }

}

?>
