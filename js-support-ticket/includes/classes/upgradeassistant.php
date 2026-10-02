<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
if (class_exists('JSSTupgradeassistant')) {
    return;
}
/**
 * Finish the upgrade from the old add-ons to 5.0.0, in one click.
 *
 * WHY
 *
 * A site updating from 4.0.0 or earlier arrives with its old stand-alone
 * add-ons still active. Its key carries over by itself (JSSTlicense::
 * adoptLegacyKey) and its database upgrades on the first admin page. Its old
 * add-ons keep working (JSSTbundle::augment() no longer switches them off,
 * decided 29 Sep 2026), but they get no more updates, and twelve of them are
 * now part of the free core. This works out what the site needs and does it:
 *
 *   1. install each bundle that replaces an old add-on on this site, if the
 *      licence grants it (JSSTlicense::install, which activates it);
 *   2. then deactivate each old add-on that a bundle or the free core now
 *      provides - only once its replacement is running, so the feature never
 *      goes missing in between.
 *
 * It is started by the administrator (the "Finish upgrade" button), not run by
 * itself: the free plugin lives on wordpress.org, whose guidelines do not
 * allow code from another server to be installed without the site owner
 * choosing to, and a visible run is one whose failure somebody sees. It runs
 * one step per request, so a slow download or a slow host cannot time the
 * whole thing out, and every step recomputes the plan, so it can be stopped,
 * resumed or repeated safely.
 *
 * WHAT IT NEVER DOES
 *
 * - Delete anything. Old add-ons are deactivated, not deleted: deleting runs
 *   their uninstall.php, which drops tables the bundles and core now read.
 * - Touch an old add-on nothing replaces (Widgets, for instance): it stays as
 *   it is, and the screen says so.
 * - Let a deactivation cost a setting. Old add-ons zero their settings rows
 *   when deactivated, and core or a bundle now reads those same rows, so each
 *   add-on's rows are copied before and written back after - for the add-ons
 *   merged into core as well, which JSSTbundle's own guard does not cover.
 *
 * (Built 29 September 2026, from an upgrade test of a 4.0.0 site with 23 old
 * add-ons active.)
 */
class JSSTupgradeassistant {

    /** Record of what was done, for the screen and the License page. */
    const OPTION_LOG = 'jsst_upgrade_assistant';

    /** Screen the assistant lives on. */
    const SCREEN = 'admin.php?page=jssupportticket&jstlay=license';

    public static function registerHooks() {
        add_action('wp_ajax_jsst_upgrade_step', array(__CLASS__, 'ajaxStep'));
        add_action('admin_notices', array(__CLASS__, 'banner'));
    }

    /**
     * Every active old add-on, classified.
     *
     * @return array slug => array('kind' => bundle|retired|core|unknown,
     *                            'bundle' => slug or '', 'file' => plugin file)
     */
    public static function legacyAddons() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $jsst_bundles = class_exists('JSSTbundle') ? JSSTbundle::bundles() : array();
        $jsst_retired = class_exists('JSSTbundle') ? (array) JSSTbundle::retired() : array();
        $jsst_out = array();
        foreach (array_keys(get_plugins()) as $jsst_file) {
            $jsst_dir = dirname($jsst_file);
            if (0 !== strpos($jsst_dir, 'js-support-ticket-') || !is_plugin_active($jsst_file)) {
                continue;
            }
            $jsst_slug = substr($jsst_dir, strlen('js-support-ticket-'));
            if (isset($jsst_bundles[$jsst_slug])) {
                continue; // a 5.0.0 bundle, not an old add-on
            }
            $jsst_owner = class_exists('JSSTbundle') ? (string) JSSTbundle::owner($jsst_slug) : '';
            if ('' !== $jsst_owner) {
                $jsst_kind = 'bundle';
                $jsst_bundle = $jsst_owner;
            } elseif (isset($jsst_retired[$jsst_slug])) {
                $jsst_kind = 'retired';
                $jsst_bundle = (string) $jsst_retired[$jsst_slug];
            } elseif (class_exists('JSSTmergedaddon') && JSSTmergedaddon::isMerged($jsst_slug)) {
                $jsst_kind = 'core';
                $jsst_bundle = '';
            } else {
                $jsst_kind = 'unknown';
                $jsst_bundle = '';
            }
            $jsst_out[$jsst_slug] = array('kind' => $jsst_kind, 'bundle' => $jsst_bundle, 'file' => $jsst_file);
        }
        return $jsst_out;
    }

    /**
     * What the site needs, and what can be done about it now.
     *
     * @return array licence, steps (in order), waiting (bundle => why),
     *               kept (slug => why), extra (granted bundles nothing needs)
     */
    public static function plan() {
        $jsst_legacy = self::legacyAddons();
        $jsst_haskey = class_exists('JSSTlicense') && JSSTlicense::hasKey();
        $jsst_current = $jsst_haskey && JSSTlicense::isActive();

        $jsst_needed = array();
        foreach ($jsst_legacy as $jsst_slug => $jsst_row) {
            if ('' !== $jsst_row['bundle'] && !JSSTbundle::active($jsst_row['bundle'])) {
                $jsst_needed[$jsst_row['bundle']][] = $jsst_slug;
            }
        }

        $jsst_steps = array();
        $jsst_waiting = array();
        foreach ($jsst_needed as $jsst_bundle => $jsst_for) {
            if (!$jsst_haskey) {
                $jsst_waiting[$jsst_bundle] = 'nokey';
            } elseif (!$jsst_current) {
                $jsst_waiting[$jsst_bundle] = 'notcurrent';
            } elseif (!JSSTlicense::grants($jsst_bundle)) {
                $jsst_waiting[$jsst_bundle] = 'notgranted';
            } elseif (file_exists(WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_bundle)) {
                $jsst_steps[] = array('do' => 'activate', 'slug' => $jsst_bundle, 'for' => $jsst_for);
            } else {
                $jsst_steps[] = array('do' => 'install', 'slug' => $jsst_bundle, 'for' => $jsst_for);
            }
        }

        $jsst_kept = array();
        foreach ($jsst_legacy as $jsst_slug => $jsst_row) {
            if ('core' === $jsst_row['kind']) {
                $jsst_steps[] = array('do' => 'deactivate', 'slug' => $jsst_slug, 'by' => 'core');
            } elseif ('' !== $jsst_row['bundle']) {
                /* Only once its replacement is running - or is about to be, by
                   an earlier step in this same plan. A bundle that cannot be
                   installed leaves the old add-on exactly where it is. */
                if (JSSTbundle::active($jsst_row['bundle']) || self::planned($jsst_steps, $jsst_row['bundle'])) {
                    $jsst_steps[] = array('do' => 'deactivate', 'slug' => $jsst_slug, 'by' => $jsst_row['bundle']);
                } else {
                    $jsst_kept[$jsst_slug] = 'bundle:' . $jsst_row['bundle'];
                }
            } else {
                $jsst_kept[$jsst_slug] = 'unknown';
            }
        }

        $jsst_extra = array();
        if ($jsst_current) {
            foreach (array_keys(JSSTbundle::bundles()) as $jsst_bundle) {
                if (!isset($jsst_needed[$jsst_bundle]) && !JSSTbundle::active($jsst_bundle)
                        && JSSTlicense::grants($jsst_bundle)) {
                    $jsst_extra[] = $jsst_bundle;
                }
            }
        }

        return array(
            'licence' => $jsst_current ? 'current' : ($jsst_haskey ? 'notcurrent' : 'nokey'),
            'allnine' => $jsst_current && self::grantsAll(),
            'steps'   => $jsst_steps,
            'waiting' => $jsst_waiting,
            'kept'    => $jsst_kept,
            'extra'   => $jsst_extra,
        );
    }

    private static function planned(array $jsst_steps, $jsst_bundle) {
        foreach ($jsst_steps as $jsst_step) {
            if (in_array($jsst_step['do'], array('install', 'activate'), true) && $jsst_step['slug'] === $jsst_bundle) {
                return true;
            }
        }
        return false;
    }

    private static function grantsAll() {
        foreach (array_keys(JSSTbundle::bundles()) as $jsst_bundle) {
            if (!JSSTlicense::grants($jsst_bundle)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Do the first step of the current plan.
     *
     * @return array step, ok, message, remaining
     */
    public static function runNextStep() {
        $jsst_plan = self::plan();
        if (empty($jsst_plan['steps'])) {
            return array('step' => null, 'ok' => true, 'message' => '', 'remaining' => 0);
        }
        $jsst_step = $jsst_plan['steps'][0];
        $jsst_label = self::label($jsst_step['slug']);
        /* A bundle's activation re-applies settings its old add-on stashed
           when it was last deactivated - possibly years ago - over the ones
           the site has now (29 Sep 2026 test: Knowledge Base and Announcement
           menu links hidden, Envato fields rewritten). Every row that exists
           before the step is written back if the step changed it; rows the
           bundle adds, and rows it renames, are its own business. */
        $jsst_settings_before = in_array($jsst_step['do'], array('install', 'activate'), true) ? self::settingsSnapshot() : array();
        $jsst_busy = false;

        if ('install' === $jsst_step['do']) {
            if (function_exists('set_time_limit')) {
                set_time_limit(120);
            }
            $jsst_result = JSSTlicense::install('js-support-ticket-' . $jsst_step['slug']);
            $jsst_ok = !is_wp_error($jsst_result) && JSSTbundle::active($jsst_step['slug']);
            /* The licence server allows so many requests a minute per address.
               A site installing four bundles back to back can meet that limit;
               it is not a refusal, so the page waits and asks again. */
            $jsst_error_data = is_wp_error($jsst_result) ? $jsst_result->get_error_data() : null;
            $jsst_busy = is_array($jsst_error_data) && isset($jsst_error_data['reason']) && 'rate_limited' === $jsst_error_data['reason'];
            $jsst_message = $jsst_ok
                ? sprintf(__('Installed %s.', 'js-support-ticket'), $jsst_label)
                : sprintf(__('Could not install %1$s: %2$s', 'js-support-ticket'), $jsst_label, is_wp_error($jsst_result) ? $jsst_result->get_error_message() : __('it did not activate.', 'js-support-ticket'));
        } elseif ('activate' === $jsst_step['do']) {
            $jsst_result = activate_plugin('js-support-ticket-' . $jsst_step['slug'] . '/js-support-ticket-' . $jsst_step['slug'] . '.php');
            $jsst_ok = !is_wp_error($jsst_result);
            $jsst_message = $jsst_ok
                ? sprintf(__('Switched on %s.', 'js-support-ticket'), $jsst_label)
                : sprintf(__('Could not switch on %1$s: %2$s', 'js-support-ticket'), $jsst_label, $jsst_result->get_error_message());
        } else {
            $jsst_ok = self::deactivateLegacy($jsst_step['slug']);
            $jsst_by = ('core' === $jsst_step['by']) ? __('JS Help Desk itself', 'js-support-ticket') : self::label($jsst_step['by']);
            $jsst_message = $jsst_ok
                ? sprintf(__('Switched off the old %1$s add-on; %2$s now provides it.', 'js-support-ticket'), self::legacyLabel($jsst_step['slug']), $jsst_by)
                : sprintf(__('Could not switch off the old %s add-on.', 'js-support-ticket'), self::legacyLabel($jsst_step['slug']));
        }

        if (!empty($jsst_settings_before)) {
            $jsst_kept = self::keepSettings($jsst_settings_before);
            if ($jsst_kept > 0) {
                $jsst_message .= ' ' . sprintf(_n('%d existing setting was kept as it was.', '%d existing settings were kept as they were.', $jsst_kept, 'js-support-ticket'), $jsst_kept);
            }
        }

        if (!empty($jsst_settings_before)) {
            $jsst_quiet = self::keepNewFeaturesOff($jsst_settings_before);
            if ($jsst_quiet > 0) {
                $jsst_message .= ' ' . __('Live chat is new to this site, so it was left switched off. Turn it on under Live Chat settings when you want it.', 'js-support-ticket');
            }
        }

        if ($jsst_busy) {
            return array(
                'step'      => $jsst_step,
                'ok'        => false,
                'retry'     => 60,
                'message'   => sprintf(__('The license server asked us to slow down. Trying %s again in a minute.', 'js-support-ticket'), $jsst_label),
                'remaining' => count($jsst_plan['steps']),
            );
        }

        self::log($jsst_step, $jsst_ok, $jsst_message);

        /* A failed step must not be retried in a loop: stop, and let the
           person see the message and decide. */
        $jsst_after = self::plan();
        return array(
            'step'      => $jsst_step,
            'ok'        => $jsst_ok,
            'message'   => $jsst_message,
            'remaining' => $jsst_ok ? count($jsst_after['steps']) : -1,
        );
    }

    /**
     * Switch an old add-on off without losing one of its settings.
     *
     * Deactivated quietly - its own deactivation hook is not run. That hook
     * zeroes the add-on's settings rows (the rows core or a bundle now reads)
     * and calls jshelpdesk.com/setup/ to release a 4.0.0 activation that no
     * longer means anything; one slow answer from there, times twenty add-ons,
     * is a step that times out. The rows are copied first and written back
     * afterwards anyway, so a hook that runs regardless costs nothing.
     */
    private static function deactivateLegacy($jsst_slug) {
        $jsst_file = 'js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php';
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_config';
        $jsst_tag = class_exists('JSSTlegacy') ? JSSTlegacy::footprint($jsst_slug)['configtag'] : $jsst_slug;
        $jsst_held = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT configname, configvalue FROM `{$jsst_table}` WHERE configfor = %s OR addon = %s",
            $jsst_tag, $jsst_slug
        ), ARRAY_A);

        deactivate_plugins($jsst_file, true);

        foreach ((array) $jsst_held as $jsst_row) {
            jssupportticket::$_db->update($jsst_table, array('configvalue' => $jsst_row['configvalue']), array('configname' => $jsst_row['configname']));
        }
        return !is_plugin_active($jsst_file);
    }

    /** Every settings row, name => value. */
    private static function settingsSnapshot() {
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT configname, configvalue FROM `" . jssupportticket::$_db->prefix . "js_ticket_config`", ARRAY_A);
        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[$jsst_row['configname']] = $jsst_row['configvalue'];
        }
        return $jsst_out;
    }

    /**
     * Write back every row that existed before and has a different value now.
     *
     * @return int How many were put back.
     */
    private static function keepSettings(array $jsst_before) {
        $jsst_now = self::settingsSnapshot();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_config';
        $jsst_kept = 0;
        foreach ($jsst_before as $jsst_name => $jsst_value) {
            if (array_key_exists($jsst_name, $jsst_now) && (string) $jsst_now[$jsst_name] !== (string) $jsst_value) {
                jssupportticket::$_db->update($jsst_table, array('configvalue' => $jsst_value), array('configname' => $jsst_name));
                ++$jsst_kept;
            }
        }
        return $jsst_kept;
    }

    /**
     * Settings that put something new in front of the site's visitors, and
     * the value that keeps it hidden.
     *
     * A bundle installed by the upgrade can bring a feature the site never
     * had. The Experience bundle ships live chat switched on, with the AI
     * answering first: on the 29 Sep 2026 upgrade test of a real 4.0.0 site
     * with 38 add-ons (it had Feedback and Multiform, never chat) a chat
     * button appeared on every public page the moment the upgrade finished.
     * Someone who asked for their add-ons to be carried over did not ask for
     * that. A bundle installed by hand keeps its own defaults.
     */
    private static $jsst_new_features_off = array(
        'livechat_enable' => '0',
    );

    /** Switch off what the step added and the site did not have before. */
    private static function keepNewFeaturesOff(array $jsst_before) {
        $jsst_now = self::settingsSnapshot();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_config';
        $jsst_done = 0;
        foreach (self::$jsst_new_features_off as $jsst_name => $jsst_off) {
            if (!array_key_exists($jsst_name, $jsst_before) && array_key_exists($jsst_name, $jsst_now) && (string) $jsst_now[$jsst_name] !== $jsst_off) {
                jssupportticket::$_db->update($jsst_table, array('configvalue' => $jsst_off), array('configname' => $jsst_name));
                ++$jsst_done;
            }
        }
        return $jsst_done;
    }

    private static function log(array $jsst_step, $jsst_ok, $jsst_message) {
        $jsst_log = get_option(self::OPTION_LOG, array());
        $jsst_log = is_array($jsst_log) ? $jsst_log : array();
        $jsst_log['steps'][] = array('when' => time(), 'do' => $jsst_step['do'], 'slug' => $jsst_step['slug'], 'ok' => (bool) $jsst_ok, 'message' => $jsst_message);
        $jsst_log['steps'] = array_slice($jsst_log['steps'], -100);
        update_option(self::OPTION_LOG, $jsst_log, false);
    }

    public static function label($jsst_bundle) {
        $jsst_bundle_data = class_exists('JSSTbundle') ? JSSTbundle::bundle($jsst_bundle) : false;
        return (is_array($jsst_bundle_data) && !empty($jsst_bundle_data['label'])) ? (string) $jsst_bundle_data['label'] : ucfirst((string) $jsst_bundle);
    }

    public static function legacyLabel($jsst_slug) {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $jsst_file = WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php';
        $jsst_name = file_exists($jsst_file) ? (string) get_plugin_data($jsst_file, false, false)['Name'] : '';
        $jsst_name = preg_replace('/^(JS\s*(Support Ticket|Help\s*Desk)\s*[-–:]?\s*)/i', '', $jsst_name);
        /* Names as old add-ons spell them in their own plugin headers, which
           are already on customers' sites and cannot be corrected there: the
           4.x Overdue add-on is "JS Help Desk Ovedue", for one (found 30 Sep
           2026). Whole names only, so no other name is touched. */
        $jsst_fixed = array(
            'Ovedue'    => 'Overdue',
            'BanEmail'  => 'Ban Email',
            'Email cc'  => 'Email CC',
            'Helptopic' => 'Help Topic',
        );
        $jsst_name = trim((string) $jsst_name);
        $jsst_name = isset($jsst_fixed[$jsst_name]) ? $jsst_fixed[$jsst_name] : $jsst_name;
        return '' !== $jsst_name ? $jsst_name : ucfirst($jsst_slug);
    }

    /** One step, over AJAX, for the button on the Install Add-ons screen. */
    public static function ajaxStep() {
        if (!current_user_can('install_plugins') || !current_user_can('activate_plugins')) {
            wp_send_json_error(array('message' => __('You are not allowed to do this.', 'js-support-ticket')), 403);
        }
        check_ajax_referer('jsst-upgrade-step');
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        wp_send_json_success(self::runNextStep());
    }

    /**
     * The banner that points to it.
     *
     * Only while there is something to do, only to people who can do it, and
     * only on the screens where the licence notices already appear. It never
     * says "upgraded to Pro" unless the licence really grants all nine.
     */
    /**
     * The loyalty offer, as a card on the Help Desk Dashboard and the Install
     * Add-ons screen (1 October 2026).
     *
     * Only for a licence the server gave an offer: a carried-over Stripe
     * customer who paid less than the 5.0 price. Pro stays free until the end
     * of the period they paid for, then renews at a permanent loyalty price.
     * "Got it" puts it away until 30 days before that date, when it comes
     * back once; after the date there is nothing left to announce.
     */
    public static function offerNotice() {
        if (!current_user_can('install_plugins') || !class_exists('JSSTlicense')) {
            return;
        }
        $jsst_offer = JSSTlicense::offer();
        if (!$jsst_offer) {
            return;
        }
        $jsst_from = strtotime($jsst_offer['from'] . ' 00:00:00 UTC');
        if (!$jsst_from || $jsst_from <= time() || JSSTlicense::snoozed('offer')) {
            return;
        }
        $jsst_date = JSSTlicense::offerDate($jsst_offer);
        $jsst_was  = JSSTlicense::freeUpgradeFrom();
        $jsst_got  = wp_nonce_url(add_query_arg('jsst_lic_snooze', 'offer'), 'jsst-lic-snooze-offer');
        ?>
        <div class="jsst-offer-card" role="region" aria-label="<?php echo esc_attr(__('Your renewal price', 'js-support-ticket')); ?>">
            <div class="jsst-offer-badge"><?php echo esc_html(sprintf(
                /* translators: %d: a percentage, e.g. 20 */
                __('%d%% loyalty discount', 'js-support-ticket'),
                (int) $jsst_offer['percent']
            )); ?></div>
            <h2 class="jsst-offer-title"><?php echo esc_html(sprintf(
                /* translators: %s: a date */
                __('All nine add-ons are yours at no extra charge until %s', 'js-support-ticket'),
                $jsst_date
            )); ?></h2>
            <p class="jsst-offer-text"><?php
                if ('' !== $jsst_was) {
                    echo esc_html(sprintf(
                        /* translators: %s: the old plan's name, e.g. Basic */
                        __('You were on %s. Your license now includes everything in Pro.', 'js-support-ticket'),
                        $jsst_was
                    )) . ' ';
                }
                echo esc_html(sprintf(
                    /* translators: 1: a date; 2: the loyalty price, e.g. $79; 3: the regular price, e.g. $99; 4: a percentage */
                    __('From %1$s your subscription renews at %2$s a year instead of %3$s - a %4$d%% loyalty discount that stays for as long as you remain subscribed. You do not need to do anything.', 'js-support-ticket'),
                    $jsst_date,
                    $jsst_offer['price_text'],
                    $jsst_offer['regular_text'],
                    (int) $jsst_offer['percent']
                )); ?></p>
            <p class="jsst-offer-actions">
                <?php if ('' !== $jsst_offer['manage_url']) { ?>
                    <a class="button button-primary" href="<?php echo esc_url($jsst_offer['manage_url']); ?>" target="_blank" rel="noopener"><?php echo esc_html(__('Manage subscription', 'js-support-ticket')); ?></a>
                <?php } ?>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')); ?>"><?php echo esc_html(__('See what is included', 'js-support-ticket')); ?></a>
                <a class="jsst-offer-dismiss" href="<?php echo esc_url($jsst_got); ?>"><?php echo esc_html(__('Got it', 'js-support-ticket')); ?></a>
            </p>
        </div>
        <?php
    }

    public static function banner() {
        if (!current_user_can('install_plugins') || !class_exists('JSSTlicense') || !self::bannerScreen()) {
            return;
        }
        if (isset($_GET['page'], $_GET['jstlay']) && 'jssupportticket' === sanitize_key(wp_unslash($_GET['page']))
                && 'license' === sanitize_key(wp_unslash($_GET['jstlay']))) {
            return; // the screen itself says all of this
        }
        $jsst_plan = self::plan();
        $jsst_free = JSSTlicense::freeUpgradeFrom();

        /* The greeting, for a carried-over Basic or Standard licence that now
           includes everything (decided with the import, 13 Sep; worded 1 Oct
           2026). Said in whichever banner is showing, never on its own twice. */
        $jsst_until = '' !== $jsst_free ? JSSTlicense::freeUpgradeUntil() : '';
        $jsst_greeting = '' === $jsst_free ? '' : ('' !== $jsst_until
            ? sprintf(
                /* translators: 1: the old plan's name, e.g. Basic; 2: a date */
                __('Good news: you were on %1$s, and your license now includes all nine add-ons - everything in Pro - at no extra charge until %2$s.', 'js-support-ticket'),
                $jsst_free,
                $jsst_until
            )
            : sprintf(
                /* translators: %s: the old plan's name, e.g. Basic */
                __('Good news: you were on %s, and your license now includes all nine add-ons - everything in Pro - at no extra charge.', 'js-support-ticket'),
                $jsst_free
            ));

        $jsst_installs = false;
        foreach ($jsst_plan['steps'] as $jsst_s) {
            if ('install' === $jsst_s['do'] || 'activate' === $jsst_s['do']) {
                $jsst_installs = true;
                break;
            }
        }

        /* 1. Something can be done now: install, switch on, switch off.
           Unless all it could do is switch off add-ons merged into the core
           while their bundles are waiting for a key, a renewal or a plan:
           then the waiting is the news, said by 2 below. */
        if (!empty($jsst_plan['steps']) && ($jsst_installs || empty($jsst_plan['waiting']))) {
            $jsst_head = '' !== $jsst_greeting
                ? $jsst_greeting
                : ($jsst_plan['allnine']
                    ? __('Your JS Help Desk account includes all nine add-ons - finish the upgrade', 'js-support-ticket')
                    : __('Finish upgrading JS Help Desk', 'js-support-ticket'));
            self::printBanner('info', $jsst_head,
                $jsst_installs
                    ? __('Some of your old add-ons are now part of a 5.0.0 bundle or of JS Help Desk itself. One click installs what replaces them and switches the old ones off. Your settings and tickets stay as they are.', 'js-support-ticket')
                    /* Nothing to install (the licence does not include a bundle,
                       or has expired): promising an install would be untrue. */
                    : __('Some of your old add-ons are now part of JS Help Desk itself. One click switches the old ones off. Your settings and tickets stay as they are.', 'js-support-ticket'),
                array(array(admin_url(self::SCREEN), __('Finish upgrade', 'js-support-ticket'), true)),
                /* Put away like the others since it shows on every admin
                   screen: a notice across the whole of wp-admin that cannot be
                   dismissed is what wordpress.org's guidelines rule out. The
                   Install Add-ons screen still says it all. (1 October 2026) */
                'upg-finish'
            );
            return;
        }

        /* 2. Old add-ons whose replacements cannot be installed yet. This used
           to say nothing at all: a site whose key had not come across from
           4.0.0 kept running old add-ons with no updates, and nobody was told
           (upgrade test, 1 October 2026). Worst reason first; each can be put
           away for a week, because the old add-ons keep working meanwhile. */
        $jsst_why = array_values($jsst_plan['waiting']);
        if (in_array('nokey', $jsst_why, true)) {
            self::printBanner('warning',
                __('Your add-ons from before 5.0.0 still work, but no longer receive updates', 'js-support-ticket'),
                __('Add your license key to install their 5.0.0 versions. The upgrade keeps every setting and ticket, and switches the old add-ons off only once their replacements are running.', 'js-support-ticket'),
                array(array(admin_url('admin.php?page=jssupportticket&jstlay=license'), __('Add your license key', 'js-support-ticket'), true)),
                'upg-nokey'
            );
            return;
        }
        if (in_array('notcurrent', $jsst_why, true)) {
            /* "Not current" is every licence that is not active, and only an
               expired or suspended one is fixed by renewing. A key refused for
               this site - no free slot, not on file, withdrawn - or one never
               checked was being told to renew (upgrade test, 1 October 2026);
               it is told what the licence server actually said instead. */
            $jsst_state  = JSSTlicense::state();
            $jsst_lapsed = in_array((string) $jsst_state['status'], array('expired', 'suspended'), true)
                || in_array((string) $jsst_state['reason'], array('license_expired', 'license_suspended'), true);
            if ($jsst_lapsed) {
                self::printBanner('warning',
                    __('Your add-ons from before 5.0.0 still work, but no longer receive updates', 'js-support-ticket'),
                    __('Your license is not current. Renew it to install their 5.0.0 versions; the old add-ons keep working until then.', 'js-support-ticket'),
                    array(
                        array(JSSTlicense::ACCOUNT, __('Renew your license', 'js-support-ticket'), true),
                        array(admin_url('admin.php?page=jssupportticket&jstlay=license'), __('License & Add-ons', 'js-support-ticket'), false),
                    ),
                    'upg-expired'
                );
            } else {
                $jsst_said = trim((string) $jsst_state['message']);
                self::printBanner('warning',
                    __('Your add-ons from before 5.0.0 still work, but no longer receive updates', 'js-support-ticket'),
                    '' !== $jsst_said
                        ? sprintf(
                            /* translators: %s: the licence server's reason, already a sentence */
                            __('Your license key could not be activated on this site: %s', 'js-support-ticket'),
                            $jsst_said
                        )
                        : __('Your license key has not been confirmed on this site yet. Open License & Add-ons and use Check again.', 'js-support-ticket'),
                    array(array(admin_url('admin.php?page=jssupportticket&jstlay=license'), __('License & Add-ons', 'js-support-ticket'), true)),
                    'upg-refused'
                );
            }
            return;
        }
        if (in_array('notgranted', $jsst_why, true)) {
            self::printBanner('info',
                __('Some of your add-ons from before 5.0.0 are not included in your license', 'js-support-ticket'),
                __('They keep working as they are, without further updates. The Install Add-ons screen lists which ones, and what your license does include.', 'js-support-ticket'),
                array(array(admin_url(self::SCREEN), __('See which', 'js-support-ticket'), false)),
                'upg-notgranted'
            );
            return;
        }

        /* 3. Nothing old to replace, but the free upgrade brought add-ons that
           are not installed yet: the greeting, with the way to get them. */
        if ('' !== $jsst_greeting && !empty($jsst_plan['extra'])) {
            self::printBanner('success', $jsst_greeting,
                __('Install the add-ons your license now includes from the Install Add-ons screen. Nothing changes on this site until you do.', 'js-support-ticket'),
                array(array(admin_url(self::SCREEN), __('Install add-ons', 'js-support-ticket'), true)),
                'upg-welcome'
            );
        }
    }

    /**
     * Where the upgrade banners show: every admin screen.
     *
     * They used to follow the licence notices (Dashboard, Plugins, Updates and
     * page=jssupportticket only), which left out the ticket screens and every
     * screen a site owner returns to after updating - posts, orders, other
     * plugins - so an upgrade could sit unfinished with nobody told. Decided 1
     * October 2026: everywhere in wp-admin, for those who can install plugins,
     * each banner put away for a week with one click. Not on the Install
     * Add-ons screen (it says all of this itself, checked in banner()) nor on
     * WordPress's own update progress screen, which is mid-task.
     */
    private static function bannerScreen() {
        if (!is_admin() || wp_doing_ajax() || !function_exists('get_current_screen')) {
            return false;
        }
        $jsst_screen = get_current_screen();
        if (!$jsst_screen) {
            return false;
        }
        return !in_array($jsst_screen->id, array('update', 'update-network'), true);
    }

    /**
     * One banner. A snooze key makes it dismissible for a week, per user, through
     * the licence notices' own snooze (JSSTlicense::maybeSnooze()).
     *
     * @param string $jsst_level   info, warning or success.
     * @param string $jsst_head    First line.
     * @param string $jsst_body    The rest.
     * @param array  $jsst_buttons Each array(url, label, primary).
     * @param string $jsst_snooze  Snooze key, or '' for a banner that stays.
     */
    public static function printBanner($jsst_level, $jsst_head, $jsst_body, $jsst_buttons, $jsst_snooze) {
        if ('' !== $jsst_snooze && JSSTlicense::snoozed($jsst_snooze)) {
            return;
        }
        $jsst_links = '';
        foreach ($jsst_buttons as $jsst_b) {
            $jsst_links .= sprintf('<a class="button%1$s" href="%2$s">%3$s</a> ', $jsst_b[2] ? ' button-primary' : '', esc_url($jsst_b[0]), esc_html($jsst_b[1]));
        }
        if ('' !== $jsst_snooze) {
            $jsst_links .= sprintf(
                '<a href="%1$s" style="margin-left:6px">%2$s</a>',
                esc_url(wp_nonce_url(add_query_arg('jsst_lic_snooze', $jsst_snooze), 'jsst-lic-snooze-' . $jsst_snooze)),
                esc_html__('Remind me in a week', 'js-support-ticket')
            );
        }
        printf(
            '<div class="notice notice-%1$s"><p><strong>%2$s</strong><br>%3$s</p><p>%4$s</p></div>',
            esc_attr($jsst_level),
            esc_html($jsst_head),
            esc_html($jsst_body),
            $jsst_links // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built escaped above
        );
    }
}
