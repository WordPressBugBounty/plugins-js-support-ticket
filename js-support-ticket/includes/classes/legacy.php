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
if (class_exists('JSSTlegacy')) {
    return;
}

/**
 * The legacy compatibility programme. (Roadmap 4.5-PRO-02)
 *
 * Consolidating up to thirty-eight add-ons into one Pro companion only works if
 * moving is boring. A customer who has been buying these add-ons since 2019 has
 * paid for capability, and the consolidation must not cost them any of it - not
 * a feature, not a setting, not a row of data, and not the money they already
 * spent. Everything in this class exists to make that true, and to make it true
 * in a way that can be undone if it turns out not to be.
 *
 * There are five parts.
 *
 * **What you have.** Which legacy add-ons are on this site, at what version,
 * with what settings and which licence. Nothing can be promised about a
 * migration until the thing being migrated has been looked at.
 *
 * **What you have already bought.** A valid legacy licence key entitles this
 * site to that module under Pro, whether or not a Pro key has been entered. A
 * customer who bought the Agents add-on outright does not lose Agents because we
 * changed how we package things, and a lifetime licence stays a lifetime
 * licence. This is a floor, never a ceiling: entitlement is added by legacy
 * keys and never taken away by them.
 *
 * **Moving, reversibly.** Every migration is previewed first, takes a full
 * snapshot before it changes anything, and hands back a token that puts
 * everything back. Nothing is deleted at any point.
 *
 * **The thing that makes all of that necessary.** Deactivating a legacy add-on
 * is not a safe operation, and this is the single most important fact in this
 * file. Twenty-three of the twenty-seven add-ons run this on deactivation:
 *
 *     SELECT configvalue, configname FROM js_ticket_config WHERE addon = '<tag>'
 *     UPDATE js_ticket_config SET configvalue = 0 WHERE configname = ...
 *
 * That is every one of the add-on's settings set to zero, with the previous
 * values stashed in an option of the add-on's own for a later reactivation. If
 * the companion takes over after that, it takes over a module whose settings
 * have all been zeroed - so a customer who moves to Pro finds their overdue
 * thresholds, their piping mailbox and their assignment rules all silently
 * reset, with no error and nothing obviously wrong until the first ticket goes
 * to the wrong place.
 *
 * Worse, two of them - Automatic Assignment and Email CC - both zero the rows
 * tagged `addon = 'email'` rather than rows of their own. Deactivate one and
 * then the other and the second one faithfully stashes what the first one left
 * behind, which is a set of zeroes. The original values are then gone from
 * everywhere they were ever kept. That is a real data-loss bug in the add-ons
 * as they stand, and it is exactly why migration cannot be "deactivate the old
 * one and activate the new one".
 *
 * So a migration here snapshots the settings *before* the deactivation and puts
 * them back *after* it. The add-on's own hook still runs and still zeroes
 * everything - we do not fight it, because fighting it would mean editing
 * twenty-three plugins we do not control on the customer's site - and then the
 * values are written back from the snapshot. The customer sees nothing happen,
 * which is the entire objective.
 *
 * **Staying compatible.** Legacy class names and hooks keep working for at
 * least two major versions, and the date they stop is published rather than
 * discovered.
 */
class JSSTlegacy {

    /**
     * When compatibility for the legacy add-ons ends.
     *
     * The roadmap's floor is twelve months after release, and this is
     * deliberately further out than that: it is a published promise, and a
     * published date may be moved back but must never be brought forward. A
     * customer planning a migration around this date has to be able to trust
     * it more than we trust our own schedule.
     */
    const SUPPORT_ENDS = '2027-12-31';

    /** Where migrations are recorded: token => what it did. */
    const OPTION_MIGRATIONS = 'jsst_legacy_migrations';

    /** Prefix for one migration's snapshot. One option per token. */
    const BACKUP_PREFIX = 'jsst_legacy_backup_';

    /** What legacy licence keys were found to entitle. */
    const OPTION_ENTITLEMENTS = 'jsst_legacy_entitlements';

    /**
     * Where a legacy add-on's settings and bookkeeping actually live, wherever
     * that differs from the module slug.
     *
     * Almost every add-on tags its settings rows with its own slug and names its
     * options after it, so almost every entry here would be the slug repeated
     * three times. Only the exceptions are written down - a table of defaults
     * is a table nobody reads, and the three lines below are the ones that
     * matter.
     *
     * configtag:  the value in js_ticket_config.addon.
     * options:    the prefix of the add-on's jsst-addon-*-version and
     *             -active-state rows. Deliberately separate from the licence
     *             key's namespace, which is always the directory slug: the
     *             updater and the premium-plugin model both build
     *             'transaction_key_for_js-support-ticket-' . <slug> from the
     *             active-plugins list, so a key is per plugin directory even
     *             where two plugins share their version bookkeeping. Treating
     *             those as one namespace made two add-ons look unlicensed on a
     *             site that had paid for both.
     * restore:    the option the add-on stashes its settings in when it is
     *             deactivated. Read rather than written: it is how a site that
     *             has *already* been through a bare deactivation can be repaired
     *             instead of only being protected in future.
     * shared:     true when the configtag is not this add-on's alone. A shared
     *             tag cannot be re-tagged and cannot be reasoned about
     *             row-by-row, so it is snapshotted and restored whole and the
     *             screen says so.
     */
    private static $jsst_exceptions = array(
        /* Both of these zero every row tagged 'email', which is neither of them
           and both of them. Nothing here tries to work out which rows belong to
           which add-on, because nothing can: the tag is the only evidence and it
           does not say. */
        'agentautoassign' => array(
            'configtag' => 'email',
            'options'   => 'email',
            'restore'   => 'JSTAgentautoassignactivation_config_string',
            'shared'    => true,
        ),
        'emailcc' => array(
            'configtag' => 'email',
            'options'   => 'email',
            'restore'   => 'JSTEmailccactivation_config_string',
            'shared'    => true,
        ),
        /* Tagged singular, installed plural. Left alone rather than corrected:
           re-tagging is a write to a customer's settings table to fix a
           cosmetic inconsistency, and the alias below costs nothing. */
        'multilanguageemailtemplates' => array(
            'configtag' => 'multilanguageemailtemplate',
            'restore'   => 'JSTMultiLangEmailTemp_config_string',
        ),
        /* Instant Resolve, which the AI Agent add-on replaced in 6.0. The entry
           stays for the site that still has the old plugin on disk, and the new
           add-on reads the same option when it adopts. (Roadmap 6.0-AI-02) */
        'instantresolve' => array('restore' => 'JSTInstantResolveActivation_config_string'),
        'aiagent' => array('restore' => 'JSTAIAgentActivation_config_string'),
        'agent' => array('restore' => 'JSTAgentactivation_config_string'),
        'aipoweredreply' => array('restore' => 'JSTaipoweredreplyactivation_config_string'),
        'announcement' => array('restore' => 'JSTAnnouncementactivation_config_string'),
        'autoclose' => array('restore' => 'JSTautocloseactivation_config_string'),
        'download' => array('restore' => 'JSTDownloadactivation_config_string'),
        'easydigitaldownloads' => array('restore' => 'JSTeasydigitaldownloadsactivation_config_string'),
        'emailpiping' => array('restore' => 'JSTemailpipingactivation_config_string'),
        'envatovalidation' => array('restore' => 'JSTenvatovalidationactivation_config_string'),
        'faq' => array('restore' => 'JSTFaqactivation_config_string'),
        'feedback' => array('restore' => 'JSTFeedbackactivation_config_string'),
        'knowledgebase' => array('restore' => 'JSTKnowledgebaseactivation_config_string'),
        'mail' => array('restore' => 'JSTMailactivation_config_string'),
        'mailchimp' => array('restore' => 'JSTmailchimpactivation_config_string'),
        'multiform' => array('restore' => 'JSTMultiformactivation_config_string'),
        'notification' => array('restore' => 'JSTNotificationactivation_config_string'),
        'overdue' => array('restore' => 'JSToverdueactivation_config_string'),
        'paidsupport' => array('restore' => 'JSTpaidsupportactivation_config_string'),
        'privatecredentials' => array('restore' => 'JSTprivatecredentialsactivation_config_string'),
    );

    /**
     * The full footprint of one legacy add-on, exceptions folded into defaults.
     */
    public static function footprint($jsst_slug) {
        $jsst_default = array(
            'configtag' => $jsst_slug,
            'options'   => $jsst_slug,
            'restore'   => '',
            'shared'    => false,
        );
        $jsst_exception = isset(self::$jsst_exceptions[$jsst_slug]) ? self::$jsst_exceptions[$jsst_slug] : array();
        return apply_filters('jsst_legacy_footprint', array_merge($jsst_default, $jsst_exception), $jsst_slug);
    }

    /** The plugin file a legacy add-on is activated as. */
    public static function pluginFile($jsst_slug) {
        return 'js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php';
    }

    /* ------------------------------------------------------------------ *
     * What you have
     * ------------------------------------------------------------------ */

    /**
     * The version of an installed legacy add-on, or ''.
     *
     * Read from the plugin header rather than from the jsst-addon-*-version
     * option, because that option is written by the add-on's own update check
     * and a site whose update check has not run since the files were replaced
     * has an option a version behind the code that is actually running. The
     * header cannot be wrong about what is on disk.
     */
    public static function version($jsst_slug) {
        $jsst_file = WP_PLUGIN_DIR . '/' . self::pluginFile($jsst_slug);
        if (!file_exists($jsst_file)) {
            return '';
        }
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $jsst_data = get_plugin_data($jsst_file, false, false);
        return isset($jsst_data['Version']) ? (string) $jsst_data['Version'] : '';
    }

    /**
     * How many settings rows each add-on tag owns, in one query.
     *
     * inventory() used to call settings() per module, which is twenty-seven
     * queries to render one screen and twenty-seven more every time anything
     * else asked. The screen only ever shows the count, so counting is all this
     * does; preview() and migrate() still read the values, and they only ever
     * deal with one module at a time.
     */
    public static function settingCounts() {
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT addon, COUNT(*) AS total FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE addon <> '' GROUP BY addon"
        );
        $jsst_counts = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_counts[$jsst_row->addon] = (int) $jsst_row->total;
        }
        return $jsst_counts;
    }

    /** The settings rows a legacy add-on owns, as configname => configvalue. */
    public static function settings($jsst_slug) {
        $jsst_tag = self::footprint($jsst_slug)['configtag'];
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT configname, configvalue FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE addon = %s",
            $jsst_tag
        ));
        $jsst_settings = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_settings[$jsst_row->configname] = $jsst_row->configvalue;
        }
        return $jsst_settings;
    }

    /**
     * Is there anything on this site for the Your Add-ons screen to talk about?
     *
     * Separate from inventory() and deliberately cheap, because the side menu
     * asks it on every admin page load and inventory() is not: that reads and
     * parses up to twenty-seven plugin headers and runs a query apiece, which is
     * a fine price for the screen itself and an absurd one for deciding whether
     * to draw a link. This is array work over a list the bootstrap already
     * built, plus one option read for sites that have finished migrating and
     * still need the way back.
     */
    public static function any() {
        if (JSSTpro::legacyAddons()) {
            return true;
        }
        foreach (self::migrations() as $jsst_record) {
            if (empty($jsst_record['rolledback'])) {
                return true;
            }
        }
        /* An add-on that is installed but deactivated still belongs on that
           screen — it is the state a half-finished migration leaves behind, and
           the screen is where somebody goes to understand it. Checked last
           because it is the only part that touches the filesystem, and only
           reached on a site with no active add-ons at all. */
        foreach (array_keys(JSSTpro::modules()) as $jsst_slug) {
            if (file_exists(WP_PLUGIN_DIR . '/' . self::pluginFile($jsst_slug))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Every module, with what this site actually has of it.
     *
     * Built for the screen, but the states it names are the ones the rest of
     * this class branches on, so they are worked out here once rather than
     * inferred separately in three places.
     */
    public static function inventory() {
        $jsst_migrated = self::migrations();
        $jsst_counts = self::settingCounts();
        $jsst_rows = array();

        foreach (JSSTpro::modules() as $jsst_slug => $jsst_module) {
            $jsst_installed = file_exists(WP_PLUGIN_DIR . '/' . self::pluginFile($jsst_slug));
            $jsst_active = JSSTpro::legacyActive($jsst_slug);
            $jsst_record = isset($jsst_migrated[$jsst_slug]) ? $jsst_migrated[$jsst_slug] : false;

            if (!$jsst_installed && $jsst_record === false) {
                /* Never had the add-on. Nothing to migrate, nothing to say, and
                   listing it would bury the handful of rows that do matter in
                   twenty that do not. */
                continue;
            }

            $jsst_footprint = self::footprint($jsst_slug);
            $jsst_settingcount = isset($jsst_counts[$jsst_footprint['configtag']])
                ? $jsst_counts[$jsst_footprint['configtag']]
                : 0;

            if ($jsst_active) {
                $jsst_state = JSSTpro::provides($jsst_slug) ? 'ready' : 'waiting';
            } elseif ($jsst_record !== false && empty($jsst_record['rolledback'])) {
                $jsst_state = 'migrated';
            } else {
                $jsst_state = 'inactive';
            }

            $jsst_rows[$jsst_slug] = array(
                'slug'        => $jsst_slug,
                'label'       => $jsst_module['label'],
                'installed'   => $jsst_installed,
                'active'      => $jsst_active,
                'version'     => self::version($jsst_slug),
                'settings'    => $jsst_settingcount,
                'shared'      => !empty($jsst_footprint['shared']),
                'configtag'   => $jsst_footprint['configtag'],
                'state'       => $jsst_state,
                'entitled'    => self::entitled($jsst_slug),
                'permanent'   => self::permanent($jsst_slug),
                'token'       => ($jsst_record !== false) ? $jsst_record['token'] : '',
                'migrated'    => ($jsst_record !== false) ? $jsst_record['when'] : '',
                'rolledback'  => ($jsst_record !== false && !empty($jsst_record['rolledback'])),
            );
        }
        return $jsst_rows;
    }

    /* ------------------------------------------------------------------ *
     * What you have already bought
     * ------------------------------------------------------------------ */

    /**
     * Read the per-add-on licence keys this site already holds and record what
     * they entitle it to under Pro.
     *
     * The keys are where the old licensing left them: one option per add-on
     * holding the key, and one option per key holding the last status the
     * licence server gave it. Both are read rather than re-checked - a customer
     * opening this screen should not have their migration blocked because our
     * server is slow this afternoon, and a key that was good enough to run the
     * add-on yesterday is good enough to entitle the module today.
     *
     * A key with a recorded status and no recorded expiry is treated as a
     * lifetime commitment. That is the generous reading, and it is the correct
     * one: lifetime licences were sold, the record of which ones is on our
     * server rather than here, and being wrong in the customer's favour on a
     * site they already paid for costs us far less than the alternative.
     */
    public static function reconcile() {
        $jsst_entitlements = array();

        foreach (array_keys(JSSTpro::modules()) as $jsst_slug) {
            /* Keyed by the slug, never by the footprint's option prefix. See
               the note on $jsst_exceptions: they are different namespaces and
               only two add-ons make that visible. */
            $jsst_key = get_option('transaction_key_for_js-support-ticket-' . $jsst_slug, '');
            if (!is_string($jsst_key) || $jsst_key === '') {
                continue;
            }
            $jsst_status = get_option('key_status_for_js-support-ticket_' . $jsst_key, null);
            if ($jsst_status === null) {
                /* A key with no recorded status has never been checked, which is
                   ordinary on a site whose daily job has not run since the key
                   was entered. Entitled, because the key exists and somebody had
                   to buy it to have it. */
                $jsst_status = 1;
            }
            if ((int) $jsst_status !== 1) {
                continue;
            }
            /* Recorded as permanent, and that is a decision rather than a
               reading of the data: this site holds a key and a status, and it
               has never held an expiry date for one — jsst_check_license_status()
               resolves expiry into a single site-wide warning flag and keeps no
               per-key record. So there is nothing local that could say a legacy
               entitlement has run out, and inventing a date to fill the gap
               would be guessing against the customer. Which keys were bundles
               and which were lifetime is known on the licence server; until that
               mapping is published, every key found here is honoured. */
            $jsst_entitlements[$jsst_slug] = array(
                'key'       => $jsst_key,
                'permanent' => true,
                'found'     => time(),
            );
        }

        update_option(self::OPTION_ENTITLEMENTS, $jsst_entitlements, false);
        self::$jsst_entitlements = $jsst_entitlements;
        return $jsst_entitlements;
    }

    /**
     * What the legacy keys on this site entitle it to.
     *
     * Held in a static as well as an option because JSSTpro::running() asks this
     * for every module on every request — twenty-seven times through augment()
     * alone — and a filter that runs twenty-seven times is twenty-seven chances
     * for a plugin doing something expensive in it to be blamed on us.
     */
    public static function entitlements() {
        if (self::$jsst_entitlements !== null) {
            return self::$jsst_entitlements;
        }
        $jsst_stored = get_option(self::OPTION_ENTITLEMENTS, null);
        if (!is_array($jsst_stored)) {
            $jsst_stored = self::reconcile();
        }
        self::$jsst_entitlements = apply_filters('jsst_legacy_entitlements', $jsst_stored);
        return self::$jsst_entitlements;
    }

    /** This request's answer, or null before anything has asked. */
    private static $jsst_entitlements = null;

    /**
     * Does an already-purchased legacy licence cover this module?
     *
     * Asked by JSSTpro alongside the Pro licence, never instead of it. The two
     * are a union: whichever of them says yes, the answer is yes.
     */
    public static function entitled($jsst_slug) {
        $jsst_entitlements = self::entitlements();
        return isset($jsst_entitlements[$jsst_slug]);
    }

    /** Is that entitlement one this site will keep regardless of renewal? */
    public static function permanent($jsst_slug) {
        $jsst_entitlements = self::entitlements();
        return !empty($jsst_entitlements[$jsst_slug]['permanent']);
    }

    /* ------------------------------------------------------------------ *
     * Moving, reversibly
     * ------------------------------------------------------------------ */

    /**
     * What migrating one module would do, without doing any of it.
     *
     * Returns the list a person reads before pressing the button, or a WP_Error
     * naming the one thing standing in the way. It is deliberately specific
     * about the settings - the count alone would be reassuring and useless, and
     * the whole point of the preview is that somebody can recognise their own
     * configuration in it.
     */
    public static function preview($jsst_slug) {
        if (JSSTpro::module($jsst_slug) === false) {
            return new WP_Error('jsst_legacy_unknown', esc_html(__('There is no such module.', 'js-support-ticket')));
        }
        if (!JSSTpro::legacyActive($jsst_slug)) {
            return new WP_Error('jsst_legacy_inactive', esc_html(__('That add-on is not active on this site, so there is nothing to move.', 'js-support-ticket')));
        }
        if (!JSSTpro::provides($jsst_slug)) {
            return new WP_Error('jsst_legacy_notcarried', esc_html(__('The Pro companion on this site does not carry that module yet. Moving now would switch the feature off, so it is not offered until the companion can take over.', 'js-support-ticket')));
        }
        if (!JSSTpro::licensed() && !self::entitled($jsst_slug)) {
            return new WP_Error('jsst_legacy_unentitled', esc_html(__('Nothing on this site entitles it to that module under Pro — neither a Pro licence nor the add-on’s own key. Moving would take the feature away.', 'js-support-ticket')));
        }

        $jsst_footprint = self::footprint($jsst_slug);
        return array(
            'slug'      => $jsst_slug,
            'label'     => JSSTpro::module($jsst_slug)['label'],
            'version'   => self::version($jsst_slug),
            'settings'  => self::settings($jsst_slug),
            'configtag' => $jsst_footprint['configtag'],
            'shared'    => !empty($jsst_footprint['shared']),
            'options'   => self::optionSnapshot($jsst_slug),
            'plugin'    => self::pluginFile($jsst_slug),
        );
    }

    /**
     * The add-on's own option rows, as they stand.
     *
     * Only the bookkeeping ones - version, active state, licence key, key
     * status and its deactivation stash. That is the whole of what an add-on
     * keeps in wp_options; everything a customer would recognise as a setting
     * is in js_ticket_config and is handled separately.
     */
    private static function optionSnapshot($jsst_slug) {
        $jsst_footprint = self::footprint($jsst_slug);
        $jsst_prefix = $jsst_footprint['options'];
        $jsst_names = array(
            'jsst-addon-' . $jsst_prefix . '-version',
            'jsst-addon-' . $jsst_prefix . '-active-state',
            /* The key by slug, and the version bookkeeping by prefix. On all
               but two add-ons those are the same string; on those two,
               snapshotting the wrong one would restore an empty licence. */
            'transaction_key_for_js-support-ticket-' . $jsst_slug,
        );
        if ($jsst_footprint['restore'] !== '') {
            $jsst_names[] = $jsst_footprint['restore'];
        }

        $jsst_snapshot = array();
        foreach ($jsst_names as $jsst_name) {
            $jsst_value = get_option($jsst_name, null);
            if ($jsst_value !== null) {
                $jsst_snapshot[$jsst_name] = $jsst_value;
            }
        }
        return $jsst_snapshot;
    }

    /**
     * Move one module from its legacy add-on onto Pro.
     *
     * The order is the whole of the design and none of it is negotiable:
     *
     *   1. Snapshot everything first, and write the snapshot to the database
     *      before touching anything. A snapshot taken after the first change is
     *      not a snapshot, and one held in memory is lost by the fatal it exists
     *      to protect against.
     *   2. Switch the module on in Pro, so that the moment the add-on stops
     *      answering, something else already is.
     *   3. Deactivate the add-on. Its own deactivation hook runs here and zeroes
     *      every one of its settings. That is expected, it is why step 1 exists,
     *      and it is not prevented — preventing it would mean interfering with
     *      twenty-three plugins on somebody else's site.
     *   4. Put the settings back from the snapshot.
     *
     * @return array|WP_Error The token that undoes this, or why it did not run.
     */
    public static function migrate($jsst_slug) {
        $jsst_preview = self::preview($jsst_slug);
        if (is_wp_error($jsst_preview)) {
            return $jsst_preview;
        }

        /* Taken before anything moves, and only for the module that can change
           the answer. Fingerprinting every migration would put a few thousand
           capability calls in front of switching on Mailchimp, which nobody
           thanks you for; Agents is the module whose tables the permission
           service reads, so it is the module whose migration has to prove it
           changed nothing. */
        $jsst_checks = self::changesPermissions($jsst_slug);
        $jsst_before = $jsst_checks ? self::permissionFingerprint() : array();

        $jsst_token = wp_generate_password(24, false, false);
        $jsst_backup = array(
            'slug'      => $jsst_slug,
            'settings'  => $jsst_preview['settings'],
            'options'   => $jsst_preview['options'],
            'plugin'    => $jsst_preview['plugin'],
            'chosen'    => JSSTpro::chose($jsst_slug),
            'version'   => $jsst_preview['version'],
            'taken'     => time(),
        );
        update_option(self::BACKUP_PREFIX . $jsst_token, $jsst_backup, false);

        /* Recorded before the change rather than after it. A migration that
           fataled half way through is the one case where the record matters
           most, and a record written at the end is the one that would not be
           there. */
        self::record($jsst_slug, $jsst_token);

        JSSTpro::setChosen($jsst_slug, true);

        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        /* Deactivated, never deleted. Deleting runs the add-on's uninstall.php,
           which drops the tables the module is about to start reading — the
           customer's knowledge base, their feedback, their time entries. The
           files stay on disk, inert, which also means the undo below is a
           reactivation rather than a reinstallation. */
        deactivate_plugins($jsst_preview['plugin'], true);

        self::restoreSettings($jsst_preview['configtag'], $jsst_preview['settings']);

        /* Asked again on the other side, through the same service, and recorded
           against the migration whether or not anything moved. A migration that
           reports "no permission changed" is worth as much as one that reports a
           change — it is the difference between having checked and having
           assumed. */
        $jsst_drift = array();
        if ($jsst_checks && $jsst_before !== array()) {
            $jsst_drift = self::permissionDrift($jsst_before, self::permissionFingerprint());
            self::recordDrift($jsst_slug, $jsst_drift, true);
        } elseif ($jsst_checks) {
            self::recordDrift($jsst_slug, array(), false);
        }

        return array(
            'token'    => $jsst_token,
            'settings' => count($jsst_preview['settings']),
            'checked'  => ($jsst_checks && $jsst_before !== array()),
            'drift'    => $jsst_drift,
        );
    }

    /**
     * Is this a module whose migration could change who may do what?
     *
     * Only Agents, and saying so as a list rather than as an `if` because the
     * Teams and role-matrix work due later in this release adds modules with the
     * same property, and the place to add them is one line here rather than a
     * condition somebody has to find.
     */
    public static function changesPermissions($jsst_slug) {
        return in_array($jsst_slug, apply_filters('jsst_legacy_permission_modules', array('agent')), true);
    }

    private static function recordDrift($jsst_slug, $jsst_drift, $jsst_checked) {
        $jsst_migrations = self::migrations();
        if (!isset($jsst_migrations[$jsst_slug])) {
            return;
        }
        $jsst_migrations[$jsst_slug]['drift'] = $jsst_drift;
        $jsst_migrations[$jsst_slug]['checked'] = (bool) $jsst_checked;
        update_option(self::OPTION_MIGRATIONS, $jsst_migrations, false);
    }

    /**
     * Put a migration back.
     *
     * Reactivates the add-on, restores its options and rewrites the settings
     * from the snapshot — in that order, because the add-on's activation hook
     * reads those options and would otherwise reinstall against the wrong
     * version, and because its reactivation may write settings of its own that
     * the snapshot then has the last word on.
     */
    public static function rollback($jsst_token) {
        $jsst_backup = get_option(self::BACKUP_PREFIX . $jsst_token, null);
        if (!is_array($jsst_backup) || !isset($jsst_backup['slug'])) {
            return new WP_Error('jsst_legacy_notoken', esc_html(__('There is no record of that migration, so there is nothing to undo.', 'js-support-ticket')));
        }
        $jsst_slug = $jsst_backup['slug'];

        foreach ((array) $jsst_backup['options'] as $jsst_name => $jsst_value) {
            update_option($jsst_name, $jsst_value);
        }

        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (file_exists(WP_PLUGIN_DIR . '/' . $jsst_backup['plugin'])) {
            $jsst_failed = activate_plugin($jsst_backup['plugin'], '', false, true);
            if (is_wp_error($jsst_failed)) {
                return $jsst_failed;
            }
        }

        self::restoreSettings(self::footprint($jsst_slug)['configtag'], (array) $jsst_backup['settings']);
        JSSTpro::setChosen($jsst_slug, !empty($jsst_backup['chosen']));

        $jsst_migrations = self::migrations();
        if (isset($jsst_migrations[$jsst_slug])) {
            $jsst_migrations[$jsst_slug]['rolledback'] = time();
            update_option(self::OPTION_MIGRATIONS, $jsst_migrations, false);
        }
        return true;
    }

    /**
     * Write settings values back onto their config rows.
     *
     * Only rows that already exist are written, and each by name: this runs
     * immediately after a deactivation hook that has just set them all to zero,
     * and its job is to undo exactly that and nothing else. It never creates a
     * row, because a row the snapshot knows about and the table does not is a
     * setting the add-on deleted on purpose.
     */
    private static function restoreSettings($jsst_tag, $jsst_settings) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_config';
        foreach ((array) $jsst_settings as $jsst_name => $jsst_value) {
            jssupportticket::$_db->update(
                $jsst_table,
                array('configvalue' => $jsst_value),
                array('configname' => $jsst_name, 'addon' => $jsst_tag)
            );
        }
    }

    /** Every migration this site has run, as slug => record. */
    public static function migrations() {
        $jsst_stored = get_option(self::OPTION_MIGRATIONS, array());
        return is_array($jsst_stored) ? $jsst_stored : array();
    }

    private static function record($jsst_slug, $jsst_token) {
        $jsst_migrations = self::migrations();
        $jsst_migrations[$jsst_slug] = array(
            'token'      => $jsst_token,
            'when'       => time(),
            'by'         => get_current_user_id(),
            'rolledback' => 0,
        );
        update_option(self::OPTION_MIGRATIONS, $jsst_migrations, false);
    }

    /* ------------------------------------------------------------------ *
     * Not losing purchased capability
     * ------------------------------------------------------------------ */

    /**
     * Who can do what, right now, as one comparable structure.
     *
     * This is the check that matters most in the whole programme, and it exists
     * because of one module. Agents is not a feature that either works or does
     * not — it is the thing that decides who may open which ticket, who may
     * close one, and who may see a department they were never meant to see. Its
     * permissions come from tables the add-on owns, joined through
     * js_ticket_staff.uid, and a migration that quietly changed one of those
     * joins would not break anything visible. It would hand somebody access
     * they had not been given, and nobody would find out from a screen.
     *
     * So the migration does not assume the permissions survived. It asks. Every
     * person the permission inspector knows about is put to the capability
     * service for every action it defines, before the change and again after
     * it, and the two answers are compared. Because both readings go through
     * JSSTcapability rather than through the add-on, the comparison is valid
     * whichever side of the migration each one was taken on — that is precisely
     * what the shared capability service was built for, and this is the first
     * thing to actually need it.
     *
     * Returns an empty array where the inspector is unavailable, which is a
     * front-end request or a bootstrap that has lost the include. A fingerprint
     * that could not be taken compares equal to nothing and is reported as not
     * taken, never as "no change".
     *
     * @return array wpuid => array(action => bool)
     */
    public static function permissionFingerprint() {
        if (!class_exists('JSSTcapability') || !class_exists('JSSTpermissioninspector')) {
            return array();
        }
        $jsst_actions = array_keys(JSSTcapability::actions());
        $jsst_fingerprint = array();

        /* Guests included deliberately, as wpuid 0. An unauthenticated request
           is the one actor nobody thinks to check and the only one whose gaining
           an permission is an outright vulnerability rather than an
           inconvenience. */
        $jsst_people = JSSTpermissioninspector::people();
        $jsst_people[0] = '';

        foreach (array_keys($jsst_people) as $jsst_wpuid) {
            $jsst_actor = JSSTcapability::actor((int) $jsst_wpuid);
            $jsst_answers = array();
            foreach ($jsst_actions as $jsst_action) {
                $jsst_answers[$jsst_action] = (bool) JSSTcapability::can($jsst_action, array(), $jsst_actor);
            }
            $jsst_fingerprint[(int) $jsst_wpuid] = $jsst_answers;
        }
        return $jsst_fingerprint;
    }

    /**
     * What changed between two fingerprints.
     *
     * Both directions are reported and they are not the same kind of news. A
     * permission gained is a security problem and is listed first; a permission
     * lost is a support problem, which is worse for the customer's morning and
     * better for their data. Neither is ever summarised away — "3 changes" tells
     * an administrator nothing they can act on.
     *
     * @return array of array(wpuid, action, was, now, direction)
     */
    public static function permissionDrift($jsst_before, $jsst_after) {
        $jsst_drift = array();
        foreach ((array) $jsst_before as $jsst_wpuid => $jsst_was) {
            if (!isset($jsst_after[$jsst_wpuid])) {
                continue;
            }
            foreach ((array) $jsst_was as $jsst_action => $jsst_wasallowed) {
                if (!array_key_exists($jsst_action, $jsst_after[$jsst_wpuid])) {
                    continue;
                }
                $jsst_nowallowed = $jsst_after[$jsst_wpuid][$jsst_action];
                if ($jsst_wasallowed === $jsst_nowallowed) {
                    continue;
                }
                $jsst_drift[] = array(
                    'wpuid'     => (int) $jsst_wpuid,
                    'action'    => $jsst_action,
                    'was'       => (bool) $jsst_wasallowed,
                    'now'       => (bool) $jsst_nowallowed,
                    'direction' => $jsst_nowallowed ? 'gained' : 'lost',
                );
            }
        }
        /* Gains first. If a migration handed somebody access, that is the line
           the administrator has to read, and it must not be below forty lines
           about a colleague who can no longer reassign tickets. */
        usort($jsst_drift, array(__CLASS__, 'driftOrder'));
        return $jsst_drift;
    }

    public static function driftOrder($jsst_a, $jsst_b) {
        if ($jsst_a['direction'] !== $jsst_b['direction']) {
            return ($jsst_a['direction'] === 'gained') ? -1 : 1;
        }
        if ($jsst_a['wpuid'] !== $jsst_b['wpuid']) {
            return ($jsst_a['wpuid'] < $jsst_b['wpuid']) ? -1 : 1;
        }
        return strcmp($jsst_a['action'], $jsst_b['action']);
    }

    /** The drift a migration recorded, ready for the screen. */
    public static function driftFor($jsst_slug) {
        $jsst_migrations = self::migrations();
        if (!isset($jsst_migrations[$jsst_slug]['drift']) || !is_array($jsst_migrations[$jsst_slug]['drift'])) {
            return array();
        }
        $jsst_rows = array();
        foreach ($jsst_migrations[$jsst_slug]['drift'] as $jsst_row) {
            $jsst_user = ($jsst_row['wpuid'] > 0) ? get_userdata($jsst_row['wpuid']) : false;
            $jsst_def = class_exists('JSSTcapability') ? JSSTcapability::definition($jsst_row['action']) : false;
            $jsst_rows[] = array_merge($jsst_row, array(
                'person' => $jsst_user ? $jsst_user->display_name : __('A visitor who is not signed in', 'js-support-ticket'),
                'label'  => ($jsst_def !== false) ? $jsst_def['label'] : $jsst_row['action'],
            ));
        }
        return $jsst_rows;
    }

    /* ------------------------------------------------------------------ *
     * Staying compatible
     * ------------------------------------------------------------------ */

    /**
     * Config tags that should count as available because a Pro module is
     * providing what used to own them.
     *
     * The configuration screen decides whether to show a settings row by asking
     * whether its `addon` tag is in jssupportticket::$_active_addons. For almost
     * every module the tag is the slug and JSSTpro::augment() has already put it
     * there. For the three that tag their rows with something else, the rows
     * would be invisible on a migrated site — the settings would still be in the
     * table, still be honoured by the code, and simply not appear on any screen,
     * which is close to the worst way for a setting to go wrong.
     *
     * These aliases are appended to the same list, so those rows come back with
     * no change to the configuration model at all.
     */
    public static function configAliases() {
        $jsst_aliases = array();
        foreach (array_keys(JSSTpro::modules()) as $jsst_slug) {
            if (!JSSTpro::running($jsst_slug)) {
                continue;
            }
            $jsst_tag = self::footprint($jsst_slug)['configtag'];
            if ($jsst_tag !== $jsst_slug && !in_array($jsst_tag, $jsst_aliases, true)) {
                $jsst_aliases[] = $jsst_tag;
            }
        }
        return $jsst_aliases;
    }

    /**
     * Keep legacy class names resolvable for code that still refers to them.
     *
     * Almost nothing needs this, and the reason is worth knowing: PHP class
     * names are case-insensitive, so the add-on's JSSTFaqModel and the module's
     * JSSTfaqModel are one name. Every model, table and controller a third party
     * might call therefore keeps working by itself once the companion provides
     * the module.
     *
     * What does not survive is the add-on's own bootstrap and activation
     * classes — JSSTFaq, JSTFaqactivation — which live in the plugin file rather
     * than in the module and which go away with it. Code calling those is rare
     * and is almost always asking one question: is this add-on here? An empty
     * stub answers that correctly, and answering it wrongly by not existing at
     * all turns a third-party site's integration into a fatal on the day its
     * customer migrates.
     *
     * Registered as an autoloader rather than as declarations, so nothing is
     * defined on a request that never asks — and so the day this is withdrawn,
     * withdrawing it is deleting one hook.
     */
    public static function registerShims() {
        spl_autoload_register(array(__CLASS__, 'shim'));
    }

    public static function shim($jsst_class) {
        /* Cheapest possible rejection first. This runs for every class PHP
           cannot find anywhere else, on every request, including classes
           belonging to other people's plugins — so the common case has to be a
           string comparison and nothing more. */
        $jsst_wanted = strtolower($jsst_class);
        if (strpos($jsst_wanted, 'jsst') !== 0 && strpos($jsst_wanted, 'jst') !== 0) {
            return;
        }
        $jsst_names = self::shimNames();
        if (!isset($jsst_names[$jsst_wanted])) {
            return;
        }
        /* A stub, not a reimplementation. It exists so that class_exists() and a
           stray method call return something rather than ending the request; the
           module itself is what actually does the work, and it is already
           loaded. */
        class_alias('JSSTlegacyshimbase', $jsst_class);
    }

    /**
     * The legacy class names worth answering for, worked out once.
     *
     * Built on the first miss rather than at registration: the map costs a pass
     * over every module including a filesystem check apiece, and the great
     * majority of requests never fail to find a class at all. Held in a static
     * afterwards, because the requests that do miss tend to miss repeatedly.
     */
    private static function shimNames() {
        if (self::$jsst_shimnames !== null) {
            return self::$jsst_shimnames;
        }
        self::$jsst_shimnames = array();
        foreach (array_keys(JSSTpro::modules()) as $jsst_slug) {
            if (JSSTpro::legacyActive($jsst_slug) || !JSSTpro::running($jsst_slug)) {
                continue;
            }
            /* The two shapes the add-ons use, lowercased because that is how PHP
               compares class names anyway. */
            self::$jsst_shimnames['jsst' . $jsst_slug] = true;
            self::$jsst_shimnames['jst' . $jsst_slug . 'activation'] = true;
        }
        return self::$jsst_shimnames;
    }

    private static $jsst_shimnames = null;

    /** Is legacy compatibility still within its published window? */
    public static function supported() {
        return (strtotime(self::SUPPORT_ENDS . ' 23:59:59') >= time());
    }

    /** How many whole days of that window are left. Zero once it has passed. */
    public static function supportRemaining() {
        $jsst_left = strtotime(self::SUPPORT_ENDS . ' 23:59:59') - time();
        return ($jsst_left > 0) ? (int) ceil($jsst_left / DAY_IN_SECONDS) : 0;
    }
}

/**
 * The stand-in every shimmed legacy class becomes. (Roadmap 4.5-PRO-02)
 *
 * Deliberately does nothing. Its whole purpose is to exist, so that a
 * class_exists() check answers yes and a stray method call returns null instead
 * of ending the request with a fatal on somebody's live help desk.
 */
class JSSTlegacyshimbase {
    public static $_addon_version = '';
    public function __call($jsst_name, $jsst_args) { return null; }
    public static function __callStatic($jsst_name, $jsst_args) { return null; }
}
