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
        $jsst_slug = jssupportticket::$_search['slug']['slug'];

        $jsst_inquery = '';
        if ($jsst_slug != null){
            $jsst_inquery .= jssupportticket::$_db->prepare(" AND slug.slug LIKE %s", '%'.$jsst_slug.'%');
        }
        jssupportticket::$jsst_data['slug'] = $jsst_slug;

        // Pagination
        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_slug AS slug WHERE slug.status = 1 ";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);

        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        //Data
        $jsst_query = "SELECT *
                  FROM ".jssupportticket::$_db->prefix ."js_ticket_slug AS slug WHERE slug.status = 1 ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);

        return;
    }


    function storeSlug($jsst_data) {
        if (empty($jsst_data)) {
            return false;
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_row = JSSTincluder::getJSTable('slug');
        $jsst_taken = array();
        $jsst_saved = 0;
        foreach ($jsst_data as $jsst_id => $jsst_slug) {
            if($jsst_id != '' && is_numeric($jsst_id)){
                $jsst_slug = sanitize_title($jsst_slug);
                if($jsst_slug != ''){
                    /* Unchanged: every slug on the page is posted with the form. */
                    if ($jsst_slug === (string) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare("SELECT slug FROM " . jssupportticket::$_db->prefix . "js_ticket_slug WHERE id = %d", $jsst_id))) {
                        continue;
                    }
                    /* Used by another page: refused, and now said so - the
                       screen used to report "stored" over a slug it had
                       skipped. The row's own unchanged slug is not a clash. */
                    $jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(id) FROM " . jssupportticket::$_db->prefix . "js_ticket_slug
                            WHERE slug = %s AND id != %d", $jsst_slug, $jsst_id);
                    $jsst_slug_flag = jssupportticket::$_db->get_var($jsst_query);
                    if($jsst_slug_flag > 0){
                        $jsst_taken[] = $jsst_slug;
                        continue;
                    }else{
                        $jsst_row->update(array('id' => $jsst_id, 'slug' => $jsst_slug));
                        ++$jsst_saved;
                    }
                }
            }
        }
        update_option('rewrite_rules', '');
        if (!empty($jsst_taken)) {
            JSSTmessage::setMessage(esc_html(sprintf(
                /* translators: %s: slug(s), comma separated */
                __('Not saved, already used by another page: %s', 'js-support-ticket'),
                implode(', ', $jsst_taken)
            )), 'error');
        }
        if ($jsst_saved > 0 || empty($jsst_taken)) {
            JSSTmessage::setMessage(esc_html(__('Slug(s) has been stored', 'js-support-ticket')), 'updated');
        }
        return;
    }

    function savePrefix($jsst_data) {
        if (empty($jsst_data)) {
            return false;
        }
        $jsst_data['prefix'] = sanitize_title($jsst_data['prefix']);
        if($jsst_data['prefix'] == ''){
            JSSTmessage::setMessage(esc_html(__('Prefix has not been stored', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_query = jssupportticket::$_db->prepare("UPDATE " . jssupportticket::$_db->prefix . "js_ticket_config
                    SET configvalue = %s
                    WHERE configname = 'slug_prefix'", $jsst_data['prefix']);
        if(jssupportticket::$_db->query($jsst_query)){
            update_option('rewrite_rules', '');
            JSSTmessage::setMessage(esc_html(__('Prefix has been stored', 'js-support-ticket')), 'updated');
            return;
        }else{
            update_option('rewrite_rules', '');
        	JSSTmessage::setMessage(esc_html(__('Prefix has not been stored', 'js-support-ticket')), 'error');
            return;
        }
    }

    function saveHomePrefix($jsst_data) {
        if (empty($jsst_data)) {
            return false;
        }
        $jsst_data['prefix'] = sanitize_title($jsst_data['prefix']);
        if($jsst_data['prefix'] == ''){
            JSSTmessage::setMessage(esc_html(__('Prefix has not been stored', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_query = jssupportticket::$_db->prepare("UPDATE " . jssupportticket::$_db->prefix . "js_ticket_config
                    SET configvalue = %s
                    WHERE configname = 'home_slug_prefix'", $jsst_data['prefix']);
        if(jssupportticket::$_db->query($jsst_query)){
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
        $jsst_query = "UPDATE " . jssupportticket::$_db->prefix . "js_ticket_slug
                    SET slug = defaultslug ";
        if(jssupportticket::$_db->query($jsst_query)){
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
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-options-for-edit-slug-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_slug = JSSTrequest::getVar('slug');
        $jsst_html = '<span class="userpopup-top">
                    <span id="userpopup-heading" class="userpopup-heading" >' . esc_html(__("Edit","js-support-ticket"))." ". esc_html(__("Slug", "js-support-ticket")) . '</span>
                        <img alt="'. esc_html(__("Close","js-support-ticket")).'" onClick="closePopup();" class="userpopup-close" src="'. esc_url(JSST_PLUGIN_URL).'includes/images/close-icon-white.png" />
                    </span>';
        $jsst_html .= '<div class="userpopup-search">
                    <div class="popup-field-title">' . esc_html(__('Slug','js-support-ticket')).' '. esc_html(__('Name','js-support-ticket')) . ' <span style="color: red;"> *</span></div>
                         <div class="popup-field-obj">' . JSSTformfield::text('slugedit', jssupportticketphplib::JSST_trim((string) $jsst_slug), array('class' => 'inputbox one', 'data-validation' => 'required')) . '</div>
                    </div>';
        $jsst_html .='<div class="popup-act-btn-wrp">
                    ' . JSSTformfield::button('save', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button savebutton popup-act-btn','onClick'=>'getFieldValue();'));
        $jsst_html .='</div>';
        $jsst_html = jssupportticketphplib::JSST_htmlentities($jsst_html);
        return wp_json_encode($jsst_html);
    }

    function getDefaultSlugFromSlug($jsst_layout) {
        $jsst_query = jssupportticket::$_db->prepare("SELECT  defaultslug FROM `".jssupportticket::$_db->prefix."js_ticket_slug` WHERE slug = %s", $jsst_layout);
        $jsst_val = jssupportticket::$_db->get_var($jsst_query);
        return sanitize_title($jsst_val);
    }


    /**
     * The desk destinations that arrived after this table was first seeded.
     * (Roadmap 4.5-FE-02, 4.5-UX-02, 6.0-CH-01)
     *
     * `js_ticket_slug` is written once, by activation, and never again - so a
     * layout added in a later release has no row, `getSlugFromFileName()`
     * returns null, and `jssupportticket::makeUrl()` appends no route segment
     * at all. The link then points at whatever page the reader is standing on,
     * which is why Chat, Customers and Notifications appeared in the agent's
     * menu and reloaded the current page when clicked. Nothing errored and
     * nothing was refused: the URL was simply the one they were already at.
     *
     * Four rows rather than a migration file, because that is what this is: the
     * seed list in `includes/activation.php` grew and existing sites need to
     * catch up. `INSERT ... SELECT ... WHERE NOT EXISTS` so it is safe to run
     * repeatedly and cannot disturb a slug somebody has renamed - if the
     * filename is already there, in any spelling, this leaves it alone.
     *
     * The rewrite rules are built from this table by `paramregister.php`, so a
     * new row is not reachable until they are flushed; that happens once, here,
     * and only when a row was actually added.
     */
    public static function ensureDeskSlugs() {
        $jsst_wanted = array(
            'workspacehome' => array('desk-home', 'slug for the agent desk home'),
            'staffchat'     => array('staff-chat', 'slug for the agent chat console'),
            'customers'     => array('customers', 'slug for the customers page'),
            'notifications' => array('notifications', 'slug for the notifications page'),
        );

        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_slug';
        if (jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return false;
        }

        $jsst_added = 0;
        foreach ($jsst_wanted as $jsst_filename => $jsst_row) {
            $jsst_exists = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                'SELECT id FROM `' . $jsst_table . '` WHERE filename = %s', $jsst_filename));
            if ($jsst_exists !== null) {
                continue;
            }
            jssupportticket::$_db->insert($jsst_table, array(
                'slug'        => $jsst_row[0],
                'defaultslug' => $jsst_row[0],
                'filename'    => $jsst_filename,
                'description' => $jsst_row[1],
                'status'      => 1,
            ));
            $jsst_added++;
        }
        return $jsst_added;
    }

    function getSlugFromFileName($jsst_layout,$jsst_module) {
        $jsst_query = jssupportticket::$_db->prepare("SELECT slug FROM `".jssupportticket::$_db->prefix."js_ticket_slug` WHERE filename = %s", $jsst_layout);
        $jsst_val = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_val;
    }

    function getSlugString($jsst_home_page = 0) {
        global $wp_rewrite;
        $jsst_rules = wp_json_encode($wp_rewrite->rules);
        $jsst_query = "SELECT slug AS value FROM `".jssupportticket::$_db->prefix."js_ticket_slug`";
        $jsst_val = jssupportticket::$_db->get_results($jsst_query);
        $jsst_string = '';
        $jsst_bstring = '';
        //$jsst_rules = wp_json_encode($jsst_rules);
        $jsst_prefix = JSSTincluder::getJSModel('configuration')->getConfigValue('slug_prefix');
        $jsst_homeprefix = JSSTincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
        foreach ($jsst_val as $jsst_slug) {
            if($jsst_home_page == 1){
                $jsst_slug->value = $jsst_homeprefix.$jsst_slug->value;
            }
            if(jssupportticketphplib::JSST_strpos($jsst_rules,$jsst_slug->value) === false){
                $jsst_string .= $jsst_bstring. $jsst_slug->value;
            }else{
                $jsst_string .= $jsst_bstring.$jsst_prefix. $jsst_slug->value;
            }
            $jsst_bstring = '|';
        }
        return $jsst_string;
    }

    function getRedirectCanonicalArray() {
        global $wp_rewrite;
        $jsst_slug_prefix = JSSTincluder::getJSModel('configuration')->getConfigValue('slug_prefix');
        $jsst_homeprefix = JSSTincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
        $jsst_rules = wp_json_encode($wp_rewrite->rules);
        $jsst_query = "SELECT slug AS value FROM `".jssupportticket::$_db->prefix."js_ticket_slug`";
        $jsst_val = jssupportticket::$_db->get_results($jsst_query);
        $jsst_string = array();
        $jsst_bstring = '';
        foreach ($jsst_val as $jsst_slug) {
            $jsst_slug->value = $jsst_homeprefix.$jsst_slug->value;
            $jsst_string[] = $jsst_bstring.$jsst_slug->value;
            $jsst_bstring = '/';
        }
        return $jsst_string;
    }

    function getAdminSearchFormDataSlug(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'slug') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_search_array = array();
        $jsst_search_array['slug'] = JSSTrequest::getVar('slug');
        $jsst_search_array['search_from_slug'] = 1;
        return $jsst_search_array;
    }

}

?>
