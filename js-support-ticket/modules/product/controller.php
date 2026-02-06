<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTproductController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'products');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_products':
                    JSSTincluder::getJSModel('product')->getProducts();
                    break;
                case 'admin_addproduct':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('product')->getProductForForm($jsst_id);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'product');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $jsst_nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    static function saveproduct() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-product-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('product')->storeProduct($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=product&jstlay=products");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'product','jstlay'=>'products'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deleteproduct() {
        $jsst_id = JSSTrequest::getVar('productid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-product-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('product')->removeProduct($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=product&jstlay=products");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'product','jstlay'=>'products'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changestatus() {
        $jsst_id = JSSTrequest::getVar('productid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('product')->changeStatus($jsst_id);
        $jsst_url = admin_url("admin.php?page=product&jstlay=products");
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function ordering() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'ordering') ) {
            die( 'Security check Failed' );
        }
        $jsst_id = JSSTrequest::getVar('productid');
        JSSTincluder::getJSModel('product')->setOrdering($jsst_id);
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        $jsst_url = "admin.php?page=product&jstlay=products";
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_productController = new JSSTproductController();
?>
