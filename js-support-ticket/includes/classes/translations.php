<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * Translations, delivered from jshelpdesk.com. (1 October 2026)
 *
 * The plugin no longer carries its languages. Fifteen of them were 47 MB of
 * the download - every site paid for all of them to use one - and the list
 * could only grow. Instead this installs the one a site actually uses, the
 * way WordPress installs its own: into wp-content/languages/plugins/, where
 * WordPress looks first and where a plugin update cannot delete it.
 *
 * Nobody has to click anything. It runs when the help desk is installed or
 * updated, when the site language changes, when WordPress adds a language
 * (an admin choosing one for their profile), and once a day for improved
 * translations. One file per language covers the core and all nine packs:
 * they share the js-support-ticket text domain.
 *
 * What it downloads is data: a .mo (what WordPress reads) and the .po it was
 * made from (what a person edits). Never .l10n.php - that is PHP, and a
 * plugin may not fetch code from its own server. Every package is checked
 * against the SHA-256 the list gives for it before anything is written.
 *
 * Nothing about the site is sent: a plain GET for a static list and a
 * static file, with a user agent that does not name the site.
 *
 * The 4.x code this replaces fetched 4.x files from an old CloudFront address
 * into the plugin's own folder, where every update wiped them.
 */
class JSSTtranslations {

    const DOMAIN = 'js-support-ticket';
    const SOURCE = 'https://jshelpdesk.com/translations/v5/index.json';
    const OPT_STATE = 'jsst_translations';
    const T_MANIFEST = 'jsst_translations_manifest';
    const T_LOCK = 'jsst_translations_lock';
    const CRON = 'jsst_translations_daily';
    const RETRY_AFTER = 21600; // six hours between automatic retries of a failed language
    const MAX_FILE = 15728640; // 15 MB; a complete translation is about 1.5 MB

    public static function registerHooks() {
        add_action(self::CRON, array(__CLASS__, 'cron'));
        add_action('admin_init', array(__CLASS__, 'maybeSync'));
        /* The site language changed, or WordPress just installed something -
           possibly a new language, possibly a new version of this plugin.
           Forgetting the signature makes the next admin page check again. */
        add_action('update_option_WPLANG', array(__CLASS__, 'forget'));
        add_action('add_option_WPLANG', array(__CLASS__, 'forget'));
        add_action('upgrader_process_complete', array(__CLASS__, 'forget'), 30);
        add_action('admin_notices', array(__CLASS__, 'notice'));
        add_action('admin_post_jsst_translations', array(__CLASS__, 'handlePost'));
        add_action('wp_ajax_jsst_translations_install', array(__CLASS__, 'ajaxInstall'));
    }

    /**
     * Whether to install translations without being asked. On unless a site
     * says otherwise: define('JSST_TRANSLATIONS_AUTO', false) in wp-config.php,
     * or the jsst_translations_auto filter. The Translations screen still
     * works either way.
     */
    public static function automatic() {
        $jsst_on = !defined('JSST_TRANSLATIONS_AUTO') || JSST_TRANSLATIONS_AUTO;
        return (bool) apply_filters('jsst_translations_auto', $jsst_on);
    }

    /** Whether this site lets plugins write language files at all. */
    public static function canWrite() {
        return !function_exists('wp_is_file_mod_allowed') || wp_is_file_mod_allowed('download_language_pack');
    }

    /** Where the list of translations lives. A constant for testing against another server. */
    public static function sourceUrl() {
        $jsst_url = defined('JSST_TRANSLATIONS_URL') ? JSST_TRANSLATIONS_URL : self::SOURCE;
        return (string) apply_filters('jsst_translations_source', $jsst_url);
    }

    /* ------------------------------------------------------------------
     * State
     * ---------------------------------------------------------------- */

    public static function state() {
        $jsst_state = get_option(self::OPT_STATE, array());
        return wp_parse_args(is_array($jsst_state) ? $jsst_state : array(), array(
            'sig'       => '',
            'checked'   => 0,
            'installed' => array(), // locale => array(version, from, at)
            'failed'    => array(), // locale => array(at, error)
        ));
    }

    private static function save($jsst_state) {
        update_option(self::OPT_STATE, $jsst_state, true);
    }

    /** Make the next admin page check again. Hooked; takes whatever the hook passes. */
    public static function forget() {
        $jsst_state = self::state();
        $jsst_state['sig'] = '';
        self::save($jsst_state);
    }

    /**
     * The languages this site needs: its own, and every language WordPress has
     * installed, because an admin can choose any of those for their profile.
     * English needs nothing.
     */
    public static function wantedLocales() {
        $jsst_locales = array(get_locale());
        if (function_exists('get_available_languages')) {
            $jsst_locales = array_merge($jsst_locales, get_available_languages());
        }
        $jsst_locales = array_values(array_unique(array_filter($jsst_locales, function ($jsst_l) {
            return is_string($jsst_l) && '' !== $jsst_l && 'en_US' !== $jsst_l && self::validLocale($jsst_l);
        })));
        return array_slice($jsst_locales, 0, 20);
    }

    private static function validLocale($jsst_locale) {
        return (bool) preg_match('/^[a-z]{2,3}(_[A-Z]{2})?(_[a-z0-9]+)?$/', (string) $jsst_locale);
    }

    private static function signature() {
        return md5(implode(',', self::wantedLocales()) . '|' . jssupportticket::$_currentversion . '|' . self::sourceUrl());
    }

    /* ------------------------------------------------------------------
     * When to look
     * ---------------------------------------------------------------- */

    /**
     * On an admin page: check when something changed since the last look, or
     * when a failed language is due another try. Usually nothing to do - one
     * option read.
     */
    public static function maybeSync() {
        if (wp_doing_ajax() || !current_user_can('install_languages')) {
            return;
        }
        if (!wp_next_scheduled(self::CRON)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CRON);
        }
        $jsst_state = self::state();
        $jsst_due = $jsst_state['sig'] !== self::signature();
        foreach ($jsst_state['failed'] as $jsst_f) {
            if ((int) $jsst_f['at'] < time() - self::RETRY_AFTER) {
                $jsst_due = true;
            }
        }
        if ($jsst_due) {
            self::sync(false);
        }
    }

    public static function cron() {
        self::sync(false, true);
    }

    /**
     * Bring every language this site needs up to date.
     *
     * @param bool $jsst_force   Asked for by a person: ask the server again, retry failures now.
     * @param bool $jsst_refresh Ask the server again even if the list is cached.
     * @return array The new state.
     */
    public static function sync($jsst_force = false, $jsst_refresh = false) {
        $jsst_state = self::state();
        $jsst_state['sig'] = self::signature();
        if ((!$jsst_force && !self::automatic()) || !self::canWrite()) {
            self::save($jsst_state);
            return $jsst_state;
        }
        $jsst_wanted = self::wantedLocales();
        if (empty($jsst_wanted)) {
            $jsst_state['failed'] = array();
            self::save($jsst_state);
            return $jsst_state;
        }
        if (!$jsst_force && get_transient(self::T_LOCK)) {
            return $jsst_state;
        }
        set_transient(self::T_LOCK, 1, 120);

        $jsst_manifest = self::manifest($jsst_force || $jsst_refresh);
        $jsst_state['checked'] = time();
        foreach ($jsst_wanted as $jsst_locale) {
            if (is_wp_error($jsst_manifest)) {
                $jsst_state['failed'][$jsst_locale] = array('at' => time(), 'error' => $jsst_manifest->get_error_message());
                continue;
            }
            $jsst_entry = self::entryFor($jsst_locale, $jsst_manifest);
            if (!$jsst_entry || self::isCurrent($jsst_locale, $jsst_entry, $jsst_state)) {
                unset($jsst_state['failed'][$jsst_locale]);
                continue;
            }
            if (!$jsst_force && isset($jsst_state['failed'][$jsst_locale])
                && (int) $jsst_state['failed'][$jsst_locale]['at'] > time() - self::RETRY_AFTER) {
                continue;
            }
            $jsst_done = self::install($jsst_locale, $jsst_entry, $jsst_state);
            if (is_wp_error($jsst_done)) {
                $jsst_state['failed'][$jsst_locale] = array('at' => time(), 'error' => $jsst_done->get_error_message());
            } else {
                unset($jsst_state['failed'][$jsst_locale]);
            }
        }
        // A language the site no longer uses is no longer a failure worth showing.
        $jsst_state['failed'] = array_intersect_key($jsst_state['failed'], array_flip($jsst_wanted));
        self::save($jsst_state);
        delete_transient(self::T_LOCK);
        return $jsst_state;
    }

    /* ------------------------------------------------------------------
     * The list on jshelpdesk.com
     * ---------------------------------------------------------------- */

    /**
     * The list of translations, cached for twelve hours.
     *
     * @return array|WP_Error locale => entry
     */
    public static function manifest($jsst_refresh = false) {
        if (!$jsst_refresh) {
            $jsst_cached = get_site_transient(self::T_MANIFEST);
            if (is_array($jsst_cached)) {
                return $jsst_cached;
            }
        }
        $jsst_url = self::sourceUrl();
        $jsst_response = wp_remote_get($jsst_url, array(
            'timeout'    => 15,
            'user-agent' => 'JS Help Desk/' . jssupportticket::$_currentversion,
        ));
        if (is_wp_error($jsst_response)) {
            return new WP_Error('jsst_tr_unreachable', sprintf(
                /* translators: %s: the reason the connection failed */
                __('jshelpdesk.com could not be reached (%s).', 'js-support-ticket'),
                $jsst_response->get_error_message()
            ));
        }
        $jsst_code = (int) wp_remote_retrieve_response_code($jsst_response);
        $jsst_data = json_decode((string) wp_remote_retrieve_body($jsst_response), true);
        if (200 !== $jsst_code || !is_array($jsst_data) || !isset($jsst_data['translations']) || !is_array($jsst_data['translations'])) {
            return new WP_Error('jsst_tr_badlist', sprintf(
                /* translators: %d: an HTTP status code */
                __('The list of translations could not be read (HTTP %d).', 'js-support-ticket'),
                $jsst_code
            ));
        }
        $jsst_out = array();
        foreach ($jsst_data['translations'] as $jsst_e) {
            $jsst_e = self::cleanEntry($jsst_e, $jsst_url);
            if ($jsst_e) {
                $jsst_out[$jsst_e['locale']] = $jsst_e;
            }
        }
        set_site_transient(self::T_MANIFEST, $jsst_out, 12 * HOUR_IN_SECONDS);
        return $jsst_out;
    }

    /** One entry of the list, checked; links resolved against the list's own address. */
    private static function cleanEntry($jsst_e, $jsst_base) {
        if (!is_array($jsst_e) || empty($jsst_e['locale']) || !self::validLocale($jsst_e['locale'])
            || empty($jsst_e['version']) || empty($jsst_e['package'])
            || empty($jsst_e['sha256']) || !preg_match('/^[a-f0-9]{64}$/', (string) $jsst_e['sha256'])) {
            return null;
        }
        $jsst_clean = array(
            'locale'  => (string) $jsst_e['locale'],
            'name'    => isset($jsst_e['name']) ? sanitize_text_field($jsst_e['name']) : (string) $jsst_e['locale'],
            'native'  => isset($jsst_e['native']) ? sanitize_text_field($jsst_e['native']) : '',
            'version' => sanitize_text_field($jsst_e['version']),
            'percent' => isset($jsst_e['percent']) ? max(0, min(100, (int) $jsst_e['percent'])) : 0,
            'sha256'  => (string) $jsst_e['sha256'],
        );
        foreach (array('package', 'po', 'mo') as $jsst_k) {
            $jsst_clean[$jsst_k] = isset($jsst_e[$jsst_k]) ? self::resolveUrl((string) $jsst_e[$jsst_k], $jsst_base) : '';
        }
        return '' === $jsst_clean['package'] ? null : $jsst_clean;
    }

    private static function resolveUrl($jsst_url, $jsst_base) {
        if ('' === $jsst_url) {
            return '';
        }
        if (!preg_match('#^https?://#i', $jsst_url)) {
            $jsst_url = trailingslashit(dirname($jsst_base)) . ltrim($jsst_url, '/');
        }
        // https only, unless the list itself was fetched over http (a local test server).
        $jsst_scheme = strtolower((string) wp_parse_url($jsst_url, PHP_URL_SCHEME));
        $jsst_basescheme = strtolower((string) wp_parse_url($jsst_base, PHP_URL_SCHEME));
        if ('https' !== $jsst_scheme && !('http' === $jsst_scheme && 'http' === $jsst_basescheme)) {
            return '';
        }
        return esc_url_raw($jsst_url);
    }

    /**
     * The translation for a locale: its own, or the nearest one. Swiss German
     * gets German, Mexican Spanish gets Spanish, Portugal gets Brazilian
     * Portuguese - closer than English. Not Chinese: Traditional readers are
     * not served by Simplified.
     */
    public static function entryFor($jsst_locale, $jsst_manifest) {
        if (!is_array($jsst_manifest)) {
            return null;
        }
        if (isset($jsst_manifest[$jsst_locale])) {
            return $jsst_manifest[$jsst_locale];
        }
        $jsst_lang = strtok($jsst_locale, '_');
        if ('zh' === $jsst_lang) {
            return null;
        }
        foreach ($jsst_manifest as $jsst_e) {
            if (strtok($jsst_e['locale'], '_') === $jsst_lang) {
                return $jsst_e;
            }
        }
        return null;
    }

    private static function isCurrent($jsst_locale, $jsst_entry, $jsst_state) {
        $jsst_have = isset($jsst_state['installed'][$jsst_locale]) ? $jsst_state['installed'][$jsst_locale] : null;
        return $jsst_have
            && $jsst_have['version'] === $jsst_entry['version']
            && $jsst_have['from'] === $jsst_entry['locale']
            && file_exists(self::file($jsst_locale, 'mo'));
    }

    public static function file($jsst_locale, $jsst_ext) {
        return WP_LANG_DIR . '/plugins/' . self::DOMAIN . '-' . $jsst_locale . '.' . $jsst_ext;
    }

    /* ------------------------------------------------------------------
     * Installing
     * ---------------------------------------------------------------- */

    /**
     * Download, check and install one language.
     *
     * @param string $jsst_locale The locale it is installed as.
     * @param array  $jsst_entry  The list's entry (maybe a nearby locale's).
     * @param array  $jsst_state  Updated in place.
     * @return true|WP_Error
     */
    public static function install($jsst_locale, $jsst_entry, &$jsst_state) {
        if (!self::canWrite()) {
            return new WP_Error('jsst_tr_nowrite', __('This site does not allow plugins to install language files (DISALLOW_FILE_MODS).', 'js-support-ticket'));
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $jsst_tmp = download_url($jsst_entry['package'], 60);
        if (is_wp_error($jsst_tmp)) {
            return new WP_Error('jsst_tr_download', sprintf(
                /* translators: %s: the reason the download failed */
                __('The download failed (%s).', 'js-support-ticket'),
                $jsst_tmp->get_error_message()
            ));
        }
        $jsst_hash = hash_file('sha256', $jsst_tmp);
        if (!hash_equals($jsst_entry['sha256'], (string) $jsst_hash)) {
            wp_delete_file($jsst_tmp);
            return new WP_Error('jsst_tr_checksum', __('The downloaded file did not match its checksum, so it was not installed.', 'js-support-ticket'));
        }
        $jsst_base = self::DOMAIN . '-' . $jsst_entry['locale'];
        $jsst_files = self::readZip($jsst_tmp, array($jsst_base . '.mo', $jsst_base . '.po'));
        wp_delete_file($jsst_tmp);
        if (is_wp_error($jsst_files)) {
            return $jsst_files;
        }
        $jsst_mo = $jsst_files[$jsst_base . '.mo'];
        $jsst_magic = substr($jsst_mo, 0, 4);
        if ("\xde\x12\x04\x95" !== $jsst_magic && "\x95\x04\x12\xde" !== $jsst_magic) {
            return new WP_Error('jsst_tr_badfile', __('The downloaded translation is not a valid .mo file.', 'js-support-ticket'));
        }

        global $wp_filesystem;
        if (!WP_Filesystem() || !$wp_filesystem) {
            return new WP_Error('jsst_tr_fs', __('WordPress could not open its files for writing.', 'js-support-ticket'));
        }
        $jsst_dir = WP_LANG_DIR . '/plugins';
        if (!$wp_filesystem->is_dir($jsst_dir) && !wp_mkdir_p($jsst_dir)) {
            return new WP_Error('jsst_tr_dir', __('The folder wp-content/languages/plugins could not be created.', 'js-support-ticket'));
        }
        /* A file this did not put there is someone's: an older download, or
           wording an admin changed by hand. Kept beside it rather than lost. */
        if (!isset($jsst_state['installed'][$jsst_locale])) {
            foreach (array('po', 'mo') as $jsst_ext) {
                if (file_exists(self::file($jsst_locale, $jsst_ext))) {
                    $wp_filesystem->move(self::file($jsst_locale, $jsst_ext), self::file($jsst_locale, $jsst_ext) . '.bak-' . gmdate('Ymd'), true);
                }
            }
        }
        $jsst_po = isset($jsst_files[$jsst_base . '.po']) ? $jsst_files[$jsst_base . '.po'] : '';
        if ('' !== $jsst_po && !$wp_filesystem->put_contents(self::file($jsst_locale, 'po'), $jsst_po, FS_CHMOD_FILE)) {
            return new WP_Error('jsst_tr_write', __('The folder wp-content/languages/plugins is not writable.', 'js-support-ticket'));
        }
        if (!$wp_filesystem->put_contents(self::file($jsst_locale, 'mo'), $jsst_mo, FS_CHMOD_FILE)) {
            return new WP_Error('jsst_tr_write', __('The folder wp-content/languages/plugins is not writable.', 'js-support-ticket'));
        }
        /* WordPress prefers a .l10n.php to a .mo of the same name, so an older
           one would hide what was just installed. */
        if (file_exists(self::file($jsst_locale, 'l10n.php'))) {
            $wp_filesystem->delete(self::file($jsst_locale, 'l10n.php'));
        }
        // The same cache WordPress clears after installing a language pack.
        wp_cache_delete(md5(WP_LANG_DIR . '/plugins/'), 'translation_files');

        $jsst_state['installed'][$jsst_locale] = array(
            'version' => $jsst_entry['version'],
            'from'    => $jsst_entry['locale'],
            'at'      => time(),
        );
        return true;
    }

    /**
     * The named files from a zip, as strings.
     *
     * @return array|WP_Error name => contents; the .mo is required.
     */
    private static function readZip($jsst_zip, $jsst_names) {
        $jsst_out = array();
        if (class_exists('ZipArchive')) {
            $jsst_z = new ZipArchive();
            if (true !== $jsst_z->open($jsst_zip)) {
                return new WP_Error('jsst_tr_zip', __('The downloaded translation could not be opened.', 'js-support-ticket'));
            }
            foreach ($jsst_names as $jsst_n) {
                $jsst_stat = $jsst_z->statName($jsst_n);
                if ($jsst_stat && $jsst_stat['size'] <= self::MAX_FILE) {
                    $jsst_out[$jsst_n] = (string) $jsst_z->getFromName($jsst_n);
                }
            }
            $jsst_z->close();
        } else {
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
            $jsst_z = new PclZip($jsst_zip);
            $jsst_list = $jsst_z->extract(PCLZIP_OPT_BY_NAME, $jsst_names, PCLZIP_OPT_EXTRACT_AS_STRING);
            if (!is_array($jsst_list)) {
                return new WP_Error('jsst_tr_zip', __('The downloaded translation could not be opened.', 'js-support-ticket'));
            }
            foreach ($jsst_list as $jsst_f) {
                if (isset($jsst_f['stored_filename'], $jsst_f['content']) && strlen($jsst_f['content']) <= self::MAX_FILE) {
                    $jsst_out[$jsst_f['stored_filename']] = (string) $jsst_f['content'];
                }
            }
        }
        if (empty($jsst_out[$jsst_names[0]])) {
            return new WP_Error('jsst_tr_zip', __('The downloaded translation could not be opened.', 'js-support-ticket'));
        }
        return $jsst_out;
    }

    /* ------------------------------------------------------------------
     * What the screens show
     * ---------------------------------------------------------------- */

    /**
     * One row per language: this site's languages first, then every other
     * translation on offer.
     */
    public static function rows() {
        $jsst_state = self::state();
        $jsst_manifest = get_site_transient(self::T_MANIFEST);
        if (!is_array($jsst_manifest)) {
            $jsst_manifest = self::manifest();
        }
        $jsst_listerror = is_wp_error($jsst_manifest) ? $jsst_manifest->get_error_message() : '';
        $jsst_manifest = is_array($jsst_manifest) ? $jsst_manifest : array();
        $jsst_wanted = self::wantedLocales();
        $jsst_rows = array();
        foreach (array_unique(array_merge($jsst_wanted, array_keys($jsst_manifest))) as $jsst_locale) {
            $jsst_entry = in_array($jsst_locale, $jsst_wanted, true)
                ? self::entryFor($jsst_locale, $jsst_manifest)
                : $jsst_manifest[$jsst_locale];
            $jsst_have = isset($jsst_state['installed'][$jsst_locale]) ? $jsst_state['installed'][$jsst_locale] : null;
            $jsst_present = file_exists(self::file($jsst_locale, 'mo'));
            if (!in_array($jsst_locale, $jsst_wanted, true)) {
                $jsst_status = 'offered';
            } elseif (isset($jsst_state['failed'][$jsst_locale]) && !$jsst_present) {
                $jsst_status = 'failed';
            } elseif (!$jsst_entry) {
                $jsst_status = $jsst_present ? 'own' : 'none';
            } elseif ($jsst_present && $jsst_have && self::isCurrent($jsst_locale, $jsst_entry, $jsst_state)) {
                $jsst_status = 'current';
            } elseif ($jsst_present) {
                $jsst_status = 'update';
            } else {
                $jsst_status = 'missing';
            }
            $jsst_rows[] = array(
                'locale'  => $jsst_locale,
                'name'    => self::languageName($jsst_locale, $jsst_entry),
                'site'    => in_array($jsst_locale, $jsst_wanted, true),
                'entry'   => $jsst_entry,
                'have'    => $jsst_have,
                'status'  => $jsst_status,
                'error'   => isset($jsst_state['failed'][$jsst_locale]) ? $jsst_state['failed'][$jsst_locale]['error'] : '',
            );
        }
        return array('rows' => $jsst_rows, 'listerror' => $jsst_listerror, 'checked' => (int) $jsst_state['checked']);
    }

    /**
     * A language's name. In a list, "French (France) - Français"; inside a
     * sentence ($jsst_own), the language's own name alone, because an English
     * name reads wrongly in the middle of a translated sentence.
     */
    public static function languageName($jsst_locale, $jsst_entry = null, $jsst_own = false) {
        if ($jsst_entry && $jsst_entry['locale'] === $jsst_locale) {
            if ($jsst_own && '' !== $jsst_entry['native']) {
                return $jsst_entry['native'];
            }
            return $jsst_entry['native'] && $jsst_entry['native'] !== $jsst_entry['name']
                ? $jsst_entry['name'] . ' - ' . $jsst_entry['native'] : $jsst_entry['name'];
        }
        $jsst_known = get_site_transient('available_translations'); // WordPress's own list, if it has fetched it
        if (is_array($jsst_known) && isset($jsst_known[$jsst_locale])) {
            $jsst_k = $jsst_known[$jsst_locale];
            if ($jsst_own && !empty($jsst_k['native_name'])) {
                return $jsst_k['native_name'];
            }
            if (!empty($jsst_k['english_name'])) {
                return $jsst_k['english_name'];
            }
        }
        return $jsst_locale;
    }

    /** Where Try again, Update now and Check for updates post to. */
    public static function actionUrl($jsst_do, $jsst_locale = '') {
        $jsst_args = array('action' => 'jsst_translations', 'do' => $jsst_do);
        if ('' !== $jsst_locale) {
            $jsst_args['locale'] = $jsst_locale;
        }
        return wp_nonce_url(add_query_arg($jsst_args, admin_url('admin-post.php')), 'jsst-translations');
    }

    public static function screenUrl() {
        return admin_url('admin.php?page=jssupportticket&jstlay=translations');
    }

    /** Update now, Try again, Check for updates. */
    public static function handlePost() {
        if (!current_user_can('install_languages')) {
            wp_die(esc_html__('You are not allowed to install languages on this site.', 'js-support-ticket'));
        }
        check_admin_referer('jsst-translations');
        $jsst_do = isset($_GET['do']) ? sanitize_key(wp_unslash($_GET['do'])) : 'sync';
        $jsst_locale = isset($_GET['locale']) ? sanitize_text_field(wp_unslash($_GET['locale'])) : '';
        $jsst_result = 'done';
        $jsst_extra = array();
        if ('install' === $jsst_do && self::validLocale($jsst_locale)) {
            $jsst_result = is_wp_error(self::installOne($jsst_locale, true)) ? 'failed' : 'done';
        } elseif ('add' === $jsst_do && self::validLocale($jsst_locale)) {
            /* A language this site does not have yet: WordPress's own language
               pack first, so the language can be chosen under Settings >
               General and in a profile, then the help desk's. */
            $jsst_wp = self::installWordPressLanguage($jsst_locale);
            $jsst_done = self::installOne($jsst_locale, true);
            $jsst_result = is_wp_error($jsst_done) ? 'failed' : ($jsst_wp ? 'added' : 'addednowp');
            $jsst_extra['jsst_trl'] = $jsst_locale;
        } elseif ('useme' === $jsst_do && ('en_US' === $jsst_locale
            || (self::validLocale($jsst_locale) && in_array($jsst_locale, get_available_languages(), true)))) {
            // Just this admin: the rest of the site keeps its language.
            update_user_meta(get_current_user_id(), 'locale', $jsst_locale);
            $jsst_result = 'useme';
        } else {
            $jsst_state = self::sync(true, true);
            $jsst_result = empty($jsst_state['failed']) ? 'done' : 'failed';
        }
        $jsst_back = wp_get_referer();
        if (!$jsst_back || false === strpos($jsst_back, 'jstlay=translations')) {
            $jsst_back = self::screenUrl();
        }
        wp_safe_redirect(add_query_arg(array_merge(array('jsst_tr' => $jsst_result), $jsst_extra), remove_query_arg(array('jsst_tr', 'jsst_trl'), $jsst_back)));
        exit;
    }

    /**
     * WordPress's own language pack for a locale, from WordPress.org - the same
     * download Settings > General makes when a language is chosen there.
     *
     * @return bool Whether WordPress now has the language.
     */
    public static function installWordPressLanguage($jsst_locale) {
        if (in_array($jsst_locale, get_available_languages(), true)) {
            return true;
        }
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        if (!wp_can_install_language_pack()) {
            return false;
        }
        return (bool) wp_download_language_pack($jsst_locale);
    }

    /**
     * Install one language now, asked for by a person.
     *
     * @return true|WP_Error
     */
    public static function installOne($jsst_locale, $jsst_refresh = false) {
        $jsst_manifest = self::manifest($jsst_refresh);
        if (is_wp_error($jsst_manifest)) {
            $jsst_result = $jsst_manifest;
        } else {
            $jsst_entry = self::entryFor($jsst_locale, $jsst_manifest);
            if (!$jsst_entry) {
                return new WP_Error('jsst_tr_none', __('There is no translation for this language yet.', 'js-support-ticket'));
            }
            $jsst_state = self::state();
            $jsst_result = self::install($jsst_locale, $jsst_entry, $jsst_state);
        }
        $jsst_state = isset($jsst_state) ? $jsst_state : self::state();
        if (is_wp_error($jsst_result)) {
            $jsst_state['failed'][$jsst_locale] = array('at' => time(), 'error' => $jsst_result->get_error_message());
        } else {
            unset($jsst_state['failed'][$jsst_locale]);
        }
        self::save($jsst_state);
        return $jsst_result;
    }

    /** The setup wizard's Download button: this site's language. */
    public static function ajaxInstall() {
        if (!current_user_can('install_languages') || !check_ajax_referer('jsst-translations', '_wpnonce', false)) {
            wp_send_json_error(array('message' => __('You are not allowed to install languages on this site.', 'js-support-ticket')));
        }
        $jsst_done = self::installOne(get_locale(), true);
        if (is_wp_error($jsst_done)) {
            wp_send_json_error(array('message' => $jsst_done->get_error_message()));
        }
        wp_send_json_success();
    }

    /**
     * For the setup wizard: the site language, when a translation for it is
     * on offer and not installed yet. Normally it is installed before anyone
     * reaches the wizard, and the step does not appear.
     *
     * @return array|null array(locale, name)
     */
    public static function siteLanguagePending() {
        $jsst_locale = get_locale();
        if ('en_US' === $jsst_locale || !self::validLocale($jsst_locale) || file_exists(self::file($jsst_locale, 'mo'))) {
            return null;
        }
        $jsst_manifest = self::manifest();
        $jsst_entry = self::entryFor($jsst_locale, $jsst_manifest);
        if (!$jsst_entry) {
            return null;
        }
        return array('locale' => $jsst_locale, 'name' => self::languageName($jsst_locale, $jsst_entry));
    }

    /**
     * The one banner: the help desk is in English on a site that is not,
     * because its translation could not be installed. Updates that fail stay
     * quiet - the installed translation still works - and show on the
     * Translations screen.
     */
    public static function notice() {
        if (!current_user_can('install_languages') || !class_exists('JSSTupgradeassistant')) {
            return;
        }
        $jsst_screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$jsst_screen || in_array($jsst_screen->id, array('update', 'update-network'), true)) {
            return;
        }
        if (isset($_GET['jstlay']) && 'translations' === $_GET['jstlay']) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- which screen, nothing acted on
            return; // the screen says it itself
        }
        $jsst_state = self::state();
        $jsst_missing = array();
        foreach ($jsst_state['failed'] as $jsst_locale => $jsst_f) {
            if (!file_exists(self::file($jsst_locale, 'mo'))) {
                $jsst_missing[$jsst_locale] = $jsst_f;
            }
        }
        if (empty($jsst_missing)) {
            return;
        }
        $jsst_locale = isset($jsst_missing[get_user_locale()]) ? get_user_locale() : key($jsst_missing);
        JSSTupgradeassistant::printBanner(
            'warning',
            sprintf(
                /* translators: %s: a language name, e.g. French */
                __('JS Help Desk could not install its %s translation', 'js-support-ticket'),
                self::languageName($jsst_locale, self::entryFor($jsst_locale, get_site_transient(self::T_MANIFEST)), true)
            ),
            sprintf(
                /* translators: %s: the reason, e.g. "The download failed (...)." */
                __('The help desk is shown in English until it is installed. %s', 'js-support-ticket'),
                $jsst_missing[$jsst_locale]['error']
            ),
            array(
                array(self::actionUrl('sync'), __('Try again', 'js-support-ticket'), true),
                array(self::screenUrl(), __('Translations', 'js-support-ticket'), false),
            ),
            'translations'
        );
    }
}
