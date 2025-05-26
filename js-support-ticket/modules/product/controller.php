<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTproductController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'products');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($layout)) {
            switch ($layout) {
                case 'admin_products':
                    JSSTincluder::getJSModel('product')->getProducts();
                    break;
                case 'admin_addproduct':
                    $id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('product')->getProductForForm($id);
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'product');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile($layout) {
        $nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    static function saveproduct() {
        $id = JSSTrequest::getVar('id');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-product-'.$id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('product')->storeProduct($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=product&jstlay=products");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'product','jstlay'=>'products'));
        }
        wp_redirect($url);
        exit;
    }

    static function deleteproduct() {
        $id = JSSTrequest::getVar('productid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-product-'.$id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('product')->removeProduct($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=product&jstlay=products");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'product','jstlay'=>'products'));
        }
        wp_redirect($url);
        exit;
    }

    static function changestatus() {
        $id = JSSTrequest::getVar('productid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-status-'.$id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('product')->changeStatus($id);
        $url = admin_url("admin.php?page=product&jstlay=products");
        $pagenum = JSSTrequest::getVar('pagenum');
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

    static function ordering() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'ordering') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('productid');
        JSSTincluder::getJSModel('product')->setOrdering($id);
        $pagenum = JSSTrequest::getVar('pagenum');
        $url = "admin.php?page=product&jstlay=products";
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

}

$productController = new JSSTproductController();
?>
