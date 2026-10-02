<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The ticket form's own screen. (Roadmap 6.5-FORM-02)
 *
 * This module used to be the Customer Experience add-on's and is core's now.
 * The reason is worth stating, because it is the whole of the change: the
 * add-on's contribution to forms is that a desk may have more than one of them,
 * not that a desk with the add-on gets a different editor for the one it has.
 * Shipping the editor twice produced exactly what shipping anything twice
 * produces - the copy in the add-on grew a preview, conditions and answer rates
 * while the copy in core kept the edit and delete the other one had dropped,
 * and neither screen was the whole feature.
 *
 * So the screen is here, and it draws the register of forms only where there is
 * one to draw. `multiform` is a shared module (JSSTincluder::sharedModules()),
 * which resolves file by file: core ships the controller and this screen, and
 * anything core does not ship still comes from the add-on - which is how
 * adding, renaming and removing a form goes on being the add-on's own screen.
 */
class JSSTmultiformController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        /* The Forms screen is what page=multiform means. `fieldordering` also
           lands here now - see JSSTfieldorderingController - so both of the
           addresses this feature has ever had open the same screen. */
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'forms');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (!self::canaddfile($jsst_layout)) {
            return;
        }
        switch ($jsst_layout) {
            /* Adding, renaming and removing a form is the add-on's screen and
               stays there: it is the one part of this that is genuinely about
               having several. Core routes to it rather than shipping it, so a
               desk without the add-on cannot reach a screen for a feature it
               does not have. */
            case 'admin_addmultiform':
                if (!in_array('multiform', jssupportticket::$_active_addons)) {
                    JSSTincluder::refuse(admin_url('admin.php?page=multiform&jstlay=forms'));
                }
                $jsst_id = JSSTrequest::getVar('jssupportticketid');
                jssupportticket::$jsst_data['permission_granted'] = true;
                if (in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                    $jsst_per_task = ($jsst_id == null) ? 'Add Multiform' : 'Edit Multiform';
                    jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
                }
                if (jssupportticket::$jsst_data['permission_granted']) {
                    JSSTincluder::getJSModel('multiform')->getMultiformForForm($jsst_id);
                }
                break;

            /* The ticket form: its questions, the conditions between them, what
               each answer has to look like, and how often each one is answered.
               Administrators only - it changes what every customer is asked. */
            /* The register of forms and one form's questions are two screens
               now, not two halves of a long one, but they are the same facts:
               whichever you are on, the controller loads the lot and the
               template takes what it needs. (Roadmap 6.5-FORM-05) */
            case 'admin_forms':
            case 'admin_formquestions':
                if (!current_user_can('manage_options') || !class_exists('JSSTforms')) {
                    JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                }
                $jsst_fmforms = JSSTforms::forms();
                $jsst_fmid = absint(JSSTrequest::getVar('formid', '', 0));
                /* One form on the site means there is nothing to choose, so it
                   opens on that one rather than on a list of one asking
                   somebody to pick it. Which is every desk without the add-on,
                   and most desks with it. */
                if ($jsst_fmid <= 0 && count($jsst_fmforms) === 1) {
                    $jsst_fmid = (int) key($jsst_fmforms);
                }
                /* A formid that names no form - a stale bookmark, or one left
                   over from a form since deleted - falls back to the one this
                   desk actually offers rather than drawing an empty screen. */
                if ($jsst_fmid > 0 && !isset($jsst_fmforms[$jsst_fmid]) && count($jsst_fmforms) === 1) {
                    $jsst_fmid = (int) key($jsst_fmforms);
                }
                /* A desk without the Customer Experience add-on has one form
                   and no way to make another, so a register of it is a list of
                   one asking somebody to pick the only option. Core opens that
                   form's questions instead, which is the screen it was going to
                   reach anyway. Only from the register itself, and only when
                   there is a form to open, so a direct visit to the questions
                   screen cannot bounce back here and loop. */
                if ($jsst_layout === 'admin_forms'
                    && !in_array('multiform', jssupportticket::$_active_addons)
                    && $jsst_fmid > 0) {
                    JSSTincluder::moved(admin_url('admin.php?page=multiform&jstlay=formquestions&formid=' . $jsst_fmid));
                }
                jssupportticket::$jsst_data['fmforms'] = $jsst_fmforms;
                jssupportticket::$jsst_data['fmform'] = ($jsst_fmid > 0) ? JSSTforms::form($jsst_fmid) : false;
                jssupportticket::$jsst_data['fmfields'] = ($jsst_fmid > 0) ? JSSTforms::fields($jsst_fmid) : array();
                jssupportticket::$jsst_data['fmlogic'] = ($jsst_fmid > 0) ? JSSTforms::logic($jsst_fmid) : array();
                jssupportticket::$jsst_data['fmstats'] = ($jsst_fmid > 0) ? JSSTforms::analytics($jsst_fmid) : array();
                jssupportticket::$jsst_data['fmpatterns'] = JSSTforms::patterns();
                jssupportticket::$jsst_data['fmoperators'] = JSSTforms::operators();
                jssupportticket::$jsst_data['fmdepartments'] = JSSTincluder::getJSModel('department')->getDepartmentForCombobox();
                jssupportticket::$jsst_data['fmchoices'] = self::conditionChoices(jssupportticket::$jsst_data['fmfields']);
                break;

            default:
                exit;
        }
        $jsst_module = (is_admin()) ? 'page' : 'jstmod';
        $jsst_module = JSSTrequest::getVar($jsst_module, null, 'multiform');
        JSSTincluder::include_file($jsst_layout, $jsst_module);
    }

    /**
     * The answers a condition can be written against, per question.
     *
     * A condition used to take its value as free text, which for the product's
     * own choosers was not merely awkward but wrong: what a ticket carries for
     * Department is `departmentid`, a number, so an administrator typing
     * "Billing" wrote a rule that could never once match. Nothing said so - the
     * question simply never appeared, and the form looked broken rather than
     * misconfigured.
     *
     * So each chooser hands over its real list. The product's own take id and
     * label apart, because that is what the ticket stores; a question this site
     * added stores the option text itself, so both are the same string there.
     *
     * `users`, `assignto` and `premade` are left out deliberately: they are
     * lists of people and canned replies, thousands of rows on a busy desk, and
     * a condition on "which agent" is an automation rule rather than something
     * the form asks a customer. They keep the free-text box.
     * (Roadmap 6.5-FORM-03)
     */
    private static function conditionChoices($jsst_fields) {
        $jsst_sources = array(
            'department' => array('department', 'getDepartmentForCombobox'),
            'priority'   => array('priority',   'getPriorityForCombobox'),
            'status'     => array('status',     'getStatusForCombobox'),
            'helptopic'  => array('helptopic',  'getHelpTopicsForCombobox'),
            'product'    => array('product',    'getProductForCombobox'),
        );
        $jsst_out = array();
        foreach ((array) $jsst_fields AS $jsst_key => $jsst_field) {
            /* A question this site added carries its own choices already. */
            if (!empty($jsst_field['options'])) {
                foreach ($jsst_field['options'] AS $jsst_option) {
                    $jsst_out[$jsst_key][] = array('v' => (string) $jsst_option, 't' => (string) $jsst_option);
                }
                continue;
            }
            if (!isset($jsst_sources[$jsst_key])) {
                continue;
            }
            list($jsst_module, $jsst_method) = $jsst_sources[$jsst_key];
            $jsst_model = JSSTincluder::getJSModel($jsst_module);
            if (!method_exists($jsst_model, $jsst_method)) {
                continue;
            }
            foreach ((array) $jsst_model->$jsst_method() AS $jsst_row) {
                if (!isset($jsst_row->id)) {
                    continue;
                }
                $jsst_out[$jsst_key][] = array('v' => (string) $jsst_row->id, 't' => (string) $jsst_row->text);
            }
        }
        return $jsst_out;
    }

    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if (wp_verify_nonce($jsst_nonce_value, 'jsst_nonce')) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if (!is_admin() && jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }


    /**
     * The register's own verbs - adding, renaming, removing, reordering a form.
     *
     * They live here because this controller is the one `action=jstask`
     * resolves to now, and they would be unreachable anywhere else. What they
     * act on is still the add-on's: every one of them goes through the
     * multiform model, which core does not ship. So each asks first, and a desk
     * without the add-on is sent back to its form rather than into a model that
     * is not there. (Roadmap 6.5-FORM-02)
     */
    private static function registerOnly() {
        if (in_array('multiform', jssupportticket::$_active_addons)) {
            return true;
        }
        wp_safe_redirect(admin_url('admin.php?page=multiform&jstlay=forms'));
        exit;
    }

    static function savemultiform() {
        self::registerOnly();
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-multiform-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('multiform')->storeMultiform($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=multiform&jstlay=forms");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'multiform', 'jstlay'=>'staffmultiform'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletemultiform() {
        self::registerOnly();
        $jsst_id = JSSTrequest::getVar('multiformid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-multiform-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        JSSTincluder::getJSModel('multiform')->removeMultiform($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=multiform&jstlay=forms");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'multiform', 'jstlay'=>'staffmultiform'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changestatus() {
        self::registerOnly();
        $jsst_id = JSSTrequest::getVar('multiformid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-status-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        JSSTincluder::getJSModel('multiform')->changeStatus($jsst_id);
        $jsst_url = admin_url("admin.php?page=multiform&jstlay=forms");
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changedefault() {
        self::registerOnly();
        $jsst_id = JSSTrequest::getVar('multiformid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-default-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_default = JSSTrequest::getVar('default',null,0);
        JSSTincluder::getJSModel('multiform')->changeDefault($jsst_id,$jsst_default);
        $jsst_url = admin_url("admin.php?page=multiform&jstlay=forms");
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function ordering() {
        self::registerOnly();
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'ordering') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_id = JSSTrequest::getVar('multiformid');
        JSSTincluder::getJSModel('multiform')->setOrdering($jsst_id);
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        $jsst_url = admin_url("admin.php?page=multiform&jstlay=forms");
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }


    /**
     * The Forms screen: the questions, or a copy, or a restore.
     * (Roadmap 5.0-FORM-01)
     *
     * The three are told apart by a hidden field, and all three snapshot the
     * form before they touch it - including the restore, so that restoring the
     * wrong version is itself undoable.
     */
    static function saveform() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-form') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTforms')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_formid = absint( JSSTrequest::getVar('formid', 'post', 0) );
        $jsst_back = admin_url('admin.php?page=multiform&jstlay=forms&formid=' . $jsst_formid);
        if ($jsst_formid <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=multiform&jstlay=forms'));
            exit;
        }

        if (JSSTrequest::getVar('fmcopy', 'post', '') !== '') {
            $jsst_result = JSSTforms::copyFields(absint( JSSTrequest::getVar('copyfrom', 'post', 0) ), $jsst_formid);
            if (is_array($jsst_result)) {
                $jsst_said = sprintf(
                    /* translators: %d is a number of questions. */
                    esc_html(_n('%d question was copied over.', '%d questions were copied over.', (int) $jsst_result['copied'], 'js-support-ticket')),
                    (int) $jsst_result['copied']);
                if (!empty($jsst_result['skipped'])) {
                    $jsst_said .= ' ' . sprintf(
                        /* translators: %s: comma-separated list of question names. */
                        esc_html__('This form already asks these, and they were left exactly as they are: %s.', 'js-support-ticket'),
                        esc_html(implode(', ', $jsst_result['skipped'])));
                }
                JSSTmessage::setMessage($jsst_said, 'updated');
            } else {
                JSSTmessage::setMessage($jsst_result, 'error');
            }
            wp_safe_redirect($jsst_back);
            exit;
        }

        if (JSSTrequest::getVar('fmfields', 'post', '') === '') {
            wp_safe_redirect($jsst_back);
            exit;
        }

        /* The form posts one array per switch rather than one array of rows,
           because that is what an unticked checkbox allows: it posts nothing,
           so a row assembled from the order list - which every field has -
           reads a missing tick as off, which is what it means. */
        $jsst_order = (array) JSSTrequest::getVar('order', 'post', array());
        $jsst_published = (array) JSSTrequest::getVar('published', 'post', array());
        $jsst_visitors = (array) JSSTrequest::getVar('forvisitors', 'post', array());
        $jsst_required = (array) JSSTrequest::getVar('required', 'post', array());
        $jsst_adminonly = (array) JSSTrequest::getVar('adminonly', 'post', array());
        asort($jsst_order, SORT_NUMERIC);
        $jsst_rows = array();
        foreach ($jsst_order as $jsst_field => $jsst_position) {
            $jsst_rows[$jsst_field] = array(
                'published'   => isset($jsst_published[$jsst_field]),
                'forvisitors' => isset($jsst_visitors[$jsst_field]),
                'required'    => isset($jsst_required[$jsst_field]),
                'adminonly'   => isset($jsst_adminonly[$jsst_field]),
            );
        }
        $jsst_result = JSSTforms::saveFields($jsst_formid, $jsst_rows);
        if ($jsst_result !== true) {
            JSSTmessage::setMessage($jsst_result, 'error');
            wp_safe_redirect($jsst_back);
            exit;
        }

        JSSTforms::saveLogic($jsst_formid, (array) JSSTrequest::getVar('logic', 'post', array()));

        $jsst_patterns = (array) JSSTrequest::getVar('pattern', 'post', array());
        $jsst_min = (array) JSSTrequest::getVar('min', 'post', array());
        $jsst_max = (array) JSSTrequest::getVar('max', 'post', array());
        $jsst_rules = array();
        foreach ($jsst_patterns as $jsst_field => $jsst_pattern) {
            $jsst_rules[$jsst_field] = array(
                'pattern' => $jsst_pattern,
                'min'     => isset($jsst_min[$jsst_field]) ? $jsst_min[$jsst_field] : 0,
                'max'     => isset($jsst_max[$jsst_field]) ? $jsst_max[$jsst_field] : 0,
            );
        }
        JSSTforms::saveValidation($jsst_formid, $jsst_rules);

        /* A length pair typed the wrong way round is saved the right way round,
           and said so. Left alone it is a question that refuses every answer a
           customer can give - nothing is both longer than 8 and shorter than 6 -
           and the refusal reads as a broken form rather than as this setting. */
        $jsst_straightened = JSSTforms::straightened();
        if ($jsst_straightened) {
            JSSTmessage::setMessage(esc_html(sprintf(
                /* translators: %s is a list of question names. */
                __('Saved. The shortest and longest were the wrong way round on %s, so they have been swapped — as they were, no answer could have been accepted.', 'js-support-ticket'),
                implode(', ', $jsst_straightened))), 'updated');
        } else {
            JSSTmessage::setMessage(esc_html(__('Saved.', 'js-support-ticket')), 'updated');
        }
        wp_safe_redirect($jsst_back);
        exit;
    }

}

$jsst_multiformController = new JSSTmultiformController();
