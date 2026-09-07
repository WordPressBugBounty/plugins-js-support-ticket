<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpagination {

    private static $_limit;
    private static $_offset;

    static function setLimit($jsst_limit){
        if(is_numeric($jsst_limit))
            self::$_limit = $jsst_limit;
    }

    static function getLimit(){
        return (int) self::$_limit;
    }

    static function setOffset($jsst_offset){
        if(is_numeric($jsst_offset))
            self::$_offset = $jsst_offset;
    }

    static function getOffset(){
        return (int) self::$_offset;
    }

    static function getPagination($jsst_total,$jsst_layout=null) {
        if(!is_numeric($jsst_total)) return false;
        $jsst_pagenum = isset($_GET['pagenum']) ? jssupportticket::JSST_sanitizeData(absint($_GET['pagenum'])) : 1; // JSST_sanitizeData() function uses wordpress santize functions
        if(!self::getLimit()){
            self::setLimit(jssupportticket::$_config['pagination_default_page_size']); // number of rows in page
        }
        $jsst_offset = ( $jsst_pagenum - 1 ) * self::$_limit;
        self::setOffset($jsst_offset);
        $jsst_num_of_pages = ceil($jsst_total / self::$_limit);
        $jsst_num_of_pages = ($jsst_num_of_pages > 0) ? ceil($jsst_num_of_pages) : floor($jsst_num_of_pages);
        
        $jsst_list = "";
        $jsst_list = JSSTrequest::getVar('list'); //for my ticket only
        $jsst_layargs = add_query_arg('pagenum', '%#%');
        
        if($jsst_layout != null && get_option( 'permalink_structure' ) != ""){
            if($jsst_list){
                //$jsst_layargs = add_query_arg(array('pagenum'=>'%#%' , 'jshdlay'=>$jsst_layout));
                $jsst_layargs = add_query_arg(array('pagenum'=>'%#%' , 'jshdlay'=>$jsst_layout, 'list'=>$jsst_list));
            }else{
                $jsst_layargs = add_query_arg(array('pagenum'=>'%#%' , 'jshdlay'=>$jsst_layout));
            }
        }
        // Every template prints this markup through wp_kses_post(), and kses reads
        // the first colon in a URL as a scheme and throws away everything before
        // it. The plugin's own pretty URLs put "date-start:2026-08-02" in the
        // path, so a root-relative link left kses as "2026-09-02/?pagenum=2":
        // relative, resolved by the browser against the current directory, one
        // path segment longer than the rewrite rule allows - and the agent got
        // "page not found" instead of page two. An absolute URL puts a real
        // scheme in the first colon, so kses passes it through untouched.
        $jsst_layargs = self::absoluteUrl($jsst_layargs);

        $jsst_result = paginate_links(array(
            'base' => $jsst_layargs,
            'format' => '',
            'prev_next' => true,
            'prev_text' => esc_html(__('Previous', 'js-support-ticket')),
            'next_text' => esc_html(__('Next', 'js-support-ticket')),
            'total' => $jsst_num_of_pages,
            'current' => $jsst_pagenum,
            'add_args' => false,
        ));
        return $jsst_result;
    }

    /**
     * The same link, addressed absolutely.
     *
     * add_query_arg() builds on REQUEST_URI and so returns a root-relative URI.
     * That is fine in HTML and wrong after kses, which is why getPagination()
     * calls this before handing the base to paginate_links().
     */
    private static function absoluteUrl($jsst_url) {
        if ($jsst_url == '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $jsst_url)) {
            return $jsst_url;
        }
        // The admin can live on another host from the front end, so ask for the
        // origin that belongs to the side this link was built on.
        $jsst_parts = wp_parse_url(is_admin() ? admin_url() : home_url());
        if (empty($jsst_parts['host'])) {
            return $jsst_url;
        }
        $jsst_scheme = empty($jsst_parts['scheme']) ? (is_ssl() ? 'https' : 'http') : $jsst_parts['scheme'];
        $jsst_origin = $jsst_scheme . '://' . $jsst_parts['host'];
        if (!empty($jsst_parts['port'])) {
            $jsst_origin .= ':' . $jsst_parts['port'];
        }
        return $jsst_origin . '/' . ltrim($jsst_url, '/');
    }

    static function isLastOrdering($jsst_total, $jsst_pagenum) {
        if(!is_numeric($jsst_total)) return false;
        if(!is_numeric($jsst_pagenum)) return false;
        $jsst_maxrecord = $jsst_pagenum * jssupportticket::$_config['pagination_default_page_size'];
        if ($jsst_maxrecord >= $jsst_total)
            return false;
        else
            return true;
    }

}

?>
