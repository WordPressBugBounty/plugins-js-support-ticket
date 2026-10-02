<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTblocks')) {
    return;
}

/**
 * The portal, in blocks. (Roadmap 4.0-UX-07)
 *
 * Building a page in WordPress means the block editor, and has for years. This
 * plugin has offered shortcodes and nothing else, which is not merely
 * old-fashioned: a shortcode in the block editor is a grey box with some text
 * in it, so somebody laying out a support page cannot see what they are
 * building until they publish and look.
 *
 * The design here is deliberately thin, and the roadmap asked for exactly
 * that - "keep shortcode adapters". Every block renders by calling the
 * shortcode that already works:
 *
 *   Nothing is reimplemented.  A block is a name, an icon and a preview around
 *                              a shortcode. There is one implementation of the
 *                              ticket form on this site, not two, so a fix to
 *                              it fixes both ways of putting it on a page.
 *   Shortcodes keep working.   Every page anybody has already built is
 *                              untouched. This adds a second door, it does not
 *                              move the room.
 *   No build step.            This plugin has no package manager and no
 *                              compiler, so the editor script is written in
 *                              plain JavaScript against the APIs WordPress
 *                              already ships - wp.blocks, wp.element,
 *                              wp.serverSideRender. Introducing a toolchain to
 *                              add seven blocks would be the largest change in
 *                              the release and the hardest to maintain.
 *
 * A block whose add-on is not installed is not registered at all. Offering
 * somebody an FAQ block that renders an empty space because they do not have
 * the FAQ add-on is worse than not offering it: it looks like the block is
 * broken rather than absent.
 *
 * The rendered output is wrapped in the same scope 4.5-FE-09 introduced, so
 * what a block puts on a page inherits the theme's typography and is protected
 * from the handful of things themes most often break - and so the design
 * tokens are available to it.
 */
class JSSTblocks {

    /** The namespace every block here lives under. */
    const NS = 'js-support-ticket';

    /** The editor script handle. */
    const HANDLE = 'jsst-blocks';

    /* =====================================================================
     * Registration
     * ================================================================== */

    public static function registerHooks() {
        add_action('init', array(__CLASS__, 'register'));
        add_filter('block_categories_all', array(__CLASS__, 'category'), 10, 1);
    }

    /**
     * What each block is, and which shortcode it is.
     *
     * @return array
     */
    public static function blocks() {
        $jsst_blocks = array(
            'portal' => array(
                'title'       => __('Help Desk portal', 'js-support-ticket'),
                'description' => __('The whole customer portal: their tickets, a way to raise one, and everything the desk offers them.', 'js-support-ticket'),
                'icon'        => 'sos',
                'shortcode'   => 'jssupportticket',
                'addon'       => '',
            ),
            'ticket-form' => array(
                'title'       => __('Raise a ticket', 'js-support-ticket'),
                'description' => __('The form on its own, for a contact page that should do one thing.', 'js-support-ticket'),
                'icon'        => 'edit',
                'shortcode'   => 'jssupportticket_addticket',
                'addon'       => '',
            ),
            'ticket-list' => array(
                'title'       => __('My tickets', 'js-support-ticket'),
                'description' => __('What this customer has already asked, and where each one has got to.', 'js-support-ticket'),
                'icon'        => 'list-view',
                'shortcode'   => 'jssupportticket_mytickets',
                'addon'       => '',
            ),
            'knowledge' => array(
                'title'       => __('Knowledge Base', 'js-support-ticket'),
                'description' => __('Articles customers can search before they write in.', 'js-support-ticket'),
                'icon'        => 'book',
                'shortcode'   => 'jssupportticket_knowledgebase',
                'addon'       => 'knowledgebase',
                'variants'    => array(
                    'all'     => 'jssupportticket_knowledgebase',
                    'latest'  => 'jssupportticket_knowledgebase_latest',
                    'popular' => 'jssupportticket_knowledgebase_popular',
                ),
            ),
            'faq' => array(
                'title'       => __('FAQs', 'js-support-ticket'),
                'description' => __('The questions you are asked most often.', 'js-support-ticket'),
                'icon'        => 'editor-help',
                'shortcode'   => 'jssupportticket_faqs',
                'addon'       => 'faq',
                'variants'    => array(
                    'all'     => 'jssupportticket_faqs',
                    'latest'  => 'jssupportticket_faqs_latest',
                    'popular' => 'jssupportticket_faqs_popular',
                ),
            ),
            'announcements' => array(
                'title'       => __('Announcements', 'js-support-ticket'),
                'description' => __('What you want every customer to see before they ask.', 'js-support-ticket'),
                'icon'        => 'megaphone',
                'shortcode'   => 'jssupportticket_announcements',
                'addon'       => 'announcement',
                'variants'    => array(
                    'all'     => 'jssupportticket_announcements',
                    'latest'  => 'jssupportticket_announcements_latest',
                    'popular' => 'jssupportticket_announcements_popular',
                ),
            ),
        );
        return apply_filters('jsst_blocks', $jsst_blocks);
    }

    /**
     * Register every block whose feature is actually on this site.
     *
     * The render callback is the whole implementation. There is deliberately
     * no `save` on the JavaScript side either - these are dynamic blocks, so
     * what is stored in the post is the block's name and its settings, and the
     * markup is produced fresh on every request. A ticket list saved into post
     * content would be somebody's queue as it looked the day the page was
     * edited.
     */
    public static function register() {
        if (!function_exists('register_block_type')) {
            return;
        }
        self::registerScript();
        foreach (self::blocks() as $jsst_name => $jsst_block) {
            if ($jsst_block['addon'] !== '' && !in_array($jsst_block['addon'], jssupportticket::$_active_addons)) {
                continue;
            }
            register_block_type(self::NS . '/' . $jsst_name, array(
                'api_version'     => 2,
                'title'           => $jsst_block['title'],
                'description'     => $jsst_block['description'],
                'category'        => 'jsst',
                'icon'            => $jsst_block['icon'],
                'editor_script'   => self::HANDLE,
                'attributes'      => array(
                    'variant' => array('type' => 'string', 'default' => 'all'),
                ),
                'render_callback' => array(__CLASS__, 'render'),
            ));
        }
    }

    /** A place in the inserter that is this plugin's own. */
    public static function category($jsst_categories) {
        foreach ((array) $jsst_categories as $jsst_category) {
            if (isset($jsst_category['slug']) && $jsst_category['slug'] === 'jsst') {
                return $jsst_categories;
            }
        }
        $jsst_own = array(array(
            'slug'  => 'jsst',
            'title' => class_exists('JSSTbrand') ? JSSTbrand::name() : __('JS Help Desk', 'js-support-ticket'),
        ));
        return array_merge((array) $jsst_categories, $jsst_own);
    }

    /**
     * Render one block by running the shortcode behind it.
     *
     * @param array  $jsst_attributes
     * @param string $jsst_content
     * @param object $jsst_block
     */
    public static function render($jsst_attributes, $jsst_content = '', $jsst_block = null) {
        $jsst_name = '';
        if (is_object($jsst_block) && isset($jsst_block->name)) {
            $jsst_name = str_replace(self::NS . '/', '', $jsst_block->name);
        }
        $jsst_all = self::blocks();
        if ($jsst_name === '' || !isset($jsst_all[$jsst_name])) {
            return '';
        }
        $jsst_definition = $jsst_all[$jsst_name];
        $jsst_shortcode = $jsst_definition['shortcode'];
        $jsst_variant = isset($jsst_attributes['variant']) ? (string) $jsst_attributes['variant'] : 'all';
        if (!empty($jsst_definition['variants']) && isset($jsst_definition['variants'][$jsst_variant])) {
            $jsst_shortcode = $jsst_definition['variants'][$jsst_variant];
        }
        /* shortcode_exists() rather than trusting the map: an add-on can be
           deactivated between the page being built and the page being read,
           and do_shortcode on something unregistered prints the raw tag to a
           customer. */
        if (!shortcode_exists($jsst_shortcode)) {
            return '';
        }
        $jsst_output = do_shortcode('[' . $jsst_shortcode . ']');
        $jsst_class = 'jsst-block jsst-block-' . sanitize_html_class($jsst_name);
        if (class_exists('JSSTbrand')) {
            $jsst_brand = JSSTbrand::brand();
            if (!empty($jsst_brand['safelayout'])) {
                $jsst_class .= ' jsst-safe';
            }
        }
        $jsst_wrapper = function_exists('get_block_wrapper_attributes')
            ? get_block_wrapper_attributes(array('class' => $jsst_class))
            : 'class="' . esc_attr($jsst_class) . '"';
        return '<div ' . $jsst_wrapper . '>' . $jsst_output . '</div>';
    }

    /* =====================================================================
     * The editor
     * ================================================================== */

    /**
     * The editor script, and what it needs to know.
     *
     * Registered rather than enqueued: naming it as each block's
     * editor_script is what makes WordPress load it only where blocks are
     * being edited.
     */
    public static function registerScript() {
        if (!function_exists('wp_register_script')) {
            return;
        }
        $jsst_deps = array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n');
        if (wp_script_is('wp-server-side-render', 'registered')) {
            $jsst_deps[] = 'wp-server-side-render';
        }
        wp_register_script(
            self::HANDLE,
            JSST_PLUGIN_URL . 'includes/js/blocks.js',
            $jsst_deps,
            jssupportticket::assetVersion('includes/js/blocks.js'),
            true
        );
        $jsst_forjs = array();
        foreach (self::blocks() as $jsst_name => $jsst_block) {
            if ($jsst_block['addon'] !== '' && !in_array($jsst_block['addon'], jssupportticket::$_active_addons)) {
                continue;
            }
            $jsst_forjs[] = array(
                'name'        => self::NS . '/' . $jsst_name,
                'title'       => $jsst_block['title'],
                'description' => $jsst_block['description'],
                'icon'        => $jsst_block['icon'],
                'variants'    => !empty($jsst_block['variants']) ? array_keys($jsst_block['variants']) : array(),
            );
        }
        wp_localize_script(self::HANDLE, 'jsstBlocks', array(
            'blocks'   => $jsst_forjs,
            'category' => 'jsst',
            'strings'  => array(
                'showing'  => __('Showing', 'js-support-ticket'),
                'all'      => __('Everything', 'js-support-ticket'),
                'latest'   => __('The latest', 'js-support-ticket'),
                'popular'  => __('The most read', 'js-support-ticket'),
                'settings' => __('What to show', 'js-support-ticket'),
                'note'     => __('This is rendered by the help desk when the page is viewed, so what you see here is what a visitor gets.', 'js-support-ticket'),
            ),
        ));
    }
}
