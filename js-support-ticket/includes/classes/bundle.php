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
if (class_exists('JSSTbundle')) {
    return;
}

/* And the guard above is not enough on its own, which is why the class below is
   declared inside a conditional. PHP binds an unconditional top-level class when
   the file is *compiled*, before a single line of it runs - so a `return` at file
   scope does not stop the declaration, and a second route to this file is a
   "Cannot declare class JSSTbundle" fatal rather than a no-op. That second route
   now exists on purpose: every superseded add-on loads this file itself, because
   WordPress sorts `active_plugins` and every `js-support-ticket-<slug>` therefore
   compiles before `js-support-ticket` does. `include_once` deduplicates by
   resolved path and would normally settle it, but a symlinked plugin directory
   gives the same file two paths - and this class is now on the critical path of
   twenty-five other plugins' bootstraps, which is not a place to rely on that.
   (Roadmap 6.5-ECO-01) */
if (!class_exists('JSSTbundle')) {

/**
 * The nine add-ons. (Roadmap 6.5-ECO-01)
 *
 * A customer assembling a help desk out of thirty separate add-on plugins is
 * doing our packaging work for us, and doing it in the worst possible place:
 * the Plugins screen, where every one of the thirty is a row that can be
 * updated on its own, licensed on its own, and broken on its own. Eleven of
 * them went into the free core in 4.0 because they were things a credible help
 * desk simply has. This class is what happens to the rest: they are grouped by
 * the job somebody is trying to do, and each group ships as one plugin.
 *
 *   Agents & Teams · Service Levels & Automation · Email · Knowledge &
 *   Self-Service · AI Agent · Customer Experience · Commerce · Integrations ·
 *   Reporting & Compliance
 *
 * ## What this class is, and what it deliberately is not
 *
 * It is a resolver and nothing else. It holds which bundle carries which
 * modules, it makes those modules answer the availability checks already
 * written throughout the plugin, and it points the includer at the files. It
 * holds no licence logic - `JSSTpro` owns entitlement and goes on owning it -
 * and it holds no module code, because a bundle is a directory of the same
 * modules that were always there, moved rather than rewritten.
 *
 * That last point is the whole design. Nothing about a merged module changes:
 * the same model, the same controller, the same templates, the same tables, the
 * same `js_ticket_config` rows with the same `addon` tags, the same
 * `page=<slug>` admin URLs. A bundle changes which directory a file is read
 * from and nothing else, which is the only way to promise that thirty add-ons'
 * worth of behaviour survives being repackaged.
 *
 * ## The precedence, which is the entire compatibility story
 *
 * **An installed bundle wins, and the stand-alone add-on it replaces goes
 * quiet.** A site that updates and finds js-support-ticket-knowledge beside its
 * old js-support-ticket-faq is served by the bundle from that moment: the
 * bundle's model, controller, templates, hooks and settings screens, and the
 * old plugin doing nothing at all until somebody removes it.
 *
 * An earlier draft of this class had it the other way round - the legacy add-on
 * won and the bundle stood down - copying `JSSTpro`, where it is right. It is
 * not right here, and the difference is what the two things are for. Pro is a
 * companion somebody *adds*; a bundle is the add-on itself, *reissued*. A
 * customer who updates and gets both is not choosing between two products, they
 * are mid-upgrade, and leaving them on the older of the two copies means the
 * fixes and features in the update do not reach them until they notice a
 * Plugins screen row and act on it. Worse, it is the arrangement where the two
 * can disagree: the bundle's tables and settings are the same rows, so an
 * update that changes how a setting is read reaches half the code.
 *
 * **"Goes quiet" has to be true in both directions, and only one of them is in
 * this file.** Core-side, everything routed through `JSSTincluder` - every
 * model, controller, table and template, which is every path this product reads
 * data through - resolves into the bundle, and that works whatever version of
 * the old add-on is installed, including one that has never heard of bundles.
 * Add-on-side, each stand-alone bootstrap asks `supersedes()` before it
 * includes a file or registers a hook, and returns. Both are needed: without
 * the first, an out-of-date add-on would go on serving its own files; without
 * the second, its hooks would fire beside the bundle's and its classes would
 * win the `class_exists()` race that has twice taken this product's whole site
 * down.
 *
 * **What is deliberately not done is deactivating anything.** Twenty-three of
 * the twenty-seven add-ons zero every one of their settings rows on
 * deactivation - see `JSSTlegacy`, which exists because of that - so a class
 * that tidied up on the customer's behalf would silently reset their overdue
 * thresholds and their piping mailbox. The old plugin is left switched on and
 * inert, told to be removed, and `protectSettings()` below stands guard over
 * the moment somebody does it.
 *
 * ## Why the snapshot exists
 *
 * `augment()` adds the bundles' modules to `jssupportticket::$_active_addons`,
 * after which that array answers "is this feature available?" - which is what
 * the several hundred `in_array('agent', ...)` tests scattered through core,
 * the menus and the templates actually mean. But "is there a
 * js-support-ticket-agent directory on disk?" is a different question, and it
 * is the one the precedence rule above turns on. Once augmentation has run it
 * cannot be recovered from the live array, so it is captured here first.
 */
class JSSTbundle {

    /**
     * Which bundle carries which modules.
     *
     * `modules` are module slugs in the includer's sense: a directory of
     * controller, model and templates that `JSSTincluder::getPluginPath()`
     * resolves, and a string that appears in `jssupportticket::$_active_addons`,
     * in `js_ticket_config.addon`, and as a `page=` slug in wp-admin. They are
     * the legacy add-ons' own slugs, unchanged, because changing one would
     * orphan a settings row and a bookmark for every site that ever used it.
     *
     * `screens` are this bundle's own layouts - the features that came out of
     * the Pro companion, which never had a module each and were reached as
     * `page=pro&jstlay=<layout>`. They move to `page=<bundle>&jstlay=<layout>`
     * and the old address redirects, which is the one visible change in this
     * whole piece of work.
     *
     * `label` is the name a customer would say, and is what the Plugins screen,
     * the control panel and the Free-versus-Pro matrix all read, so there is one
     * list to keep honest rather than four.
     */
    private static $jsst_bundles = array(

        'agents' => array(
            'label'   => 'Agents & Teams',
            'summary' => 'Who answers, what they may see, and the work of running a desk: agents and agent roles, teams and visibility, collaboration, automatic assignment, merging duplicates, time tracking and the credential vault.',
            /* Private Credentials is here rather than in Integrations, where an
               early draft of this grouping put it. It integrates with nothing:
               it is a store only agents read, and it belongs beside the people
               who read it. The Pro manifest has always grouped it this way. */
            'modules' => array('agent', 'agentautoassign', 'mergeticket', 'timetracking', 'privatecredentials'),
            'screens' => array(),
        ),

        'servicelevels' => array(
            'label'   => 'Service Levels & Automation',
            'summary' => 'What the desk has promised and what it does about it on its own: due dates, overdue marking and escalation, response and resolution targets counted in the desk\'s own working week, auto close, automation rules and recurring tickets.',
            /* Two modules, not three. Service levels are not an add-on and never
               were - 5.0-SLA-01 built them inside Ticket Overdue, where the due
               date already lived, and they are reached as page=overdue&jstlay=sla.
               A site looking for "the SLA add-on" is looking for this one. */
            'modules' => array('overdue', 'autoclose'),
            'screens' => array(),
        ),

        /* Not `email`. The slug becomes a member of $_active_addons, and
           getPluginPath() reads that array as "there is a
           js-support-ticket-<slug> directory carrying this module" - so a bundle
           called `email` would send core's own modules/email into a plugin
           directory that has no such module, which is a fatal rather than a
           missing feature. */
        'emailsuite' => array(
            'label'   => 'Email',
            'summary' => 'Mail in and mail out: an inbox turned into the help desk over IMAP, fixed addresses copied onto each kind of notification, sending through a real mail server, one template per language, and messages between agents.',
            'modules' => array('emailpiping', 'emailcc', 'smtp', 'multilanguageemailtemplates', 'mail'),
            'screens' => array(),
        ),

        'knowledge' => array(
            'label'   => 'Knowledge & Self-Service',
            'summary' => 'What a customer can answer without you: categorised articles with agent-only and public visibility, short questions and answers in front of the ticket form, files to fetch, and announcements.',
            'modules' => array('knowledgebase', 'faq', 'download', 'announcement'),
            'screens' => array(),
        ),

        /* The AI Agent keeps the slug it shipped with in 6.0. It is the one
           bundle that needs no move at all: the consolidation it represents -
           AI Powered Reply into the free core, Instant Resolve replaced
           outright - already happened, and its module is already shared with
           core through JSSTincluder::sharedModules(). It is listed so that the
           packaging screen can show all nine, and so that nothing reading this
           manifest has to special-case the ninth.

           The Copilot is not in `modules` and that is deliberate. It is free in
           core, gated on nothing but an AI engine being configured, and moving
           its code in here would take a working feature away from every site
           that has one. It is grouped with the AI Agent on the screen and
           nowhere else. */
        'aiagent' => array(
            'label'   => 'AI Agent',
            'summary' => 'Answers from your own documentation: suggestions while the customer types, grounded replies after, and automatic answers when you trust them - under one master switch, one review mode and one meter.',
            'modules' => array(),
            'screens' => array(),
        ),

        'experience' => array(
            'label'   => 'Customer Experience',
            'summary' => 'Everything the customer meets: more than one ticket form, live chat, a satisfaction question when a ticket closes, your branding, and a portal they can install.',
            /* Live chat is here rather than with the AI Agent, whose engine it
               borrows, and rather than in Integrations, where `chat` means
               something else entirely - notifying Slack and Teams, which is
               agents being told rather than a customer talking. It is a
               customer-facing surface with its own hours, its own routing and
               its own transport, so it sits with the other customer-facing
               surfaces. */
            'modules' => array('multiform', 'feedback', 'livechat'),
            'screens' => array('admin_branding'),
        ),

        'commerce' => array(
            'label'   => 'Commerce',
            'summary' => 'Who bought what, and what that entitles them to: orders and products on the ticket for WooCommerce and Easy Digital Downloads, Envato purchase codes checked before a ticket is accepted, paid support by ticket, credit or plan, and entitlements.',
            'modules' => array('woocommerce', 'easydigitaldownloads', 'envatovalidation', 'paidsupport'),
            'screens' => array('admin_entitlements'),
        ),

        'integrations' => array(
            'label'   => 'Integrations',
            'summary' => 'Everything that talks to something outside: the REST API and its published documentation, webhooks in both directions with a delivery log, the connector register, chat notification to Slack, Teams, Google Chat, Telegram and Discord, browser notifications, and Mailchimp.',
            'modules' => array('notification', 'mailchimp'),
            'screens' => array('admin_api', 'admin_webhooks', 'admin_connectors', 'admin_chat'),
        ),

        'reporting' => array(
            'label'   => 'Reporting & Compliance',
            'summary' => 'What happened and what may be kept: analytics with scheduled XLSX and PDF exports, retention with legal holds and approved deletions, and the background work board.',
            'modules' => array(),
            'screens' => array('admin_analytics', 'admin_exports', 'admin_retention', 'admin_jobs'),
        ),
    );

    /**
     * Add-ons a bundle replaced outright, rather than took over as a module.
     * module slug => the bundle that replaced it.
     *
     * ## Why this cannot be an entry in `$jsst_bundles['modules']`
     *
     * Everything above is a module that *moved*: the same model, controller and
     * templates, read from a bundle directory instead of a stand-alone one. The
     * whole supersession machinery is built on that - `provides()` asks the
     * filesystem whether the bundle really ships `modules/<slug>/`, and
     * `owner()` is what `modulePath()` resolves through. Listing a module here
     * that no bundle ships would send the includer at a directory that is not
     * there, which is a fatal rather than a missing feature.
     *
     * Instant Resolve did not move. The AI Agent replaced it - different tables,
     * different settings, a different retrieval path - so there is no
     * `modules/instantresolve` for `provides()` to find, and there never will
     * be. Which left it in the one position the design has no answer for:
     * superseded in fact, and invisible to every check that exists to say so.
     * `supersedes()` answered false, so an updated Instant Resolve would never
     * have stood down; `superseded()` skipped it, so there was no Plugins-screen
     * row, no notice and no settings guard; and on a site that upgraded with the
     * old plugin still switched on, its cron went on running against tables its
     * activation had never created, writing `Table ... doesn't exist` into the
     * log every time WordPress fired it.
     *
     * So a retired add-on is a second, narrower kind of superseded: the bundle
     * that replaced it is installed, and core routes nothing to the old plugin
     * at all. That last part is what makes `retiredDirs()` below safe. For a
     * module-carrying legacy add-on, unhooking would be reckless - core may
     * still be resolving templates out of it mid-upgrade. For one that was
     * replaced outright there is nothing to resolve, so its hooks can only
     * compete with the replacement's.
     *
     * `aipoweredreply` is deliberately not here. It went into the free core, not
     * into a bundle, and `JSSTmergedaddon` already owns that case down to the
     * settings restore.
     */
    private static $jsst_retired = array(
        'instantresolve' => 'aiagent',
    );

    /** The retired map, filterable so a later release need not edit this file. */
    public static function retired() {
        return (array) apply_filters('jsst_retired_addons', self::$jsst_retired);
    }

    /**
     * The bundle that replaced this add-on outright, or '' when none did or the
     * replacement is not installed here.
     *
     * The "installed" half matters for the same reason it does in
     * `supersedes()`: a site running the old Instant Resolve and no AI Agent has
     * not been superseded by anything, and must carry on exactly as it always
     * has.
     */
    public static function retiredBy($jsst_module) {
        $jsst_retired = self::retired();
        if (!isset($jsst_retired[$jsst_module])) {
            return '';
        }
        return self::active($jsst_retired[$jsst_module]) ? $jsst_retired[$jsst_module] : '';
    }

    /**
     * Plugin directories whose `jsst*` hooks should be taken back off.
     *
     * Filtered onto `JSSTmergedaddon::suppressLegacyHooks()`, which already does
     * this work for capabilities absorbed into the free core and does it by
     * asking reflection which file defines a callback - so it stays right across
     * add-on versions, including the ones written before any of this existed.
     *
     * Only retired add-ons, never the twenty-three a bundle carries as modules.
     * Those are still being read from by `JSSTincluder` on a site mid-upgrade.
     */
    public static function retiredDirs($jsst_dirs) {
        foreach (self::retired() as $jsst_module => $jsst_slug) {
            if (self::legacyActive($jsst_module) && self::active($jsst_slug)) {
                $jsst_dirs[] = wp_normalize_path(
                    WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_module . '/');
            }
        }
        /* And every add-on a bundle has taken over, not only the ones it
           replaced outright. (Roadmap 6.5-ECO-01)
         *
         * This used to be unsafe and is not any more, and the difference is
         * worth writing down. While a bundle stood down for a module whose old
         * add-on was active, the add-on's hooks were the only ones registered -
         * twenty-eight of them across a full site - so taking them off would
         * have removed the feature rather than the duplicate. The bundles boot
         * beside the add-ons now, so the bundle's own registrations are there,
         * and what is left on the old add-on is a second implementation of
         * something already being served: the two racing to answer the same
         * hook, which is what put an order panel on a ticket twice and what
         * made a fatal out of a method the old add-on never wrote.
         *
         * `superseded()` is the same list the Plugins screen and the notice are
         * built from, so a customer cannot be told an add-on is superseded on
         * one screen and have it still answering on another. */
        foreach (self::superseded() as $jsst_module => $jsst_ignored) {
            $jsst_dirs[] = wp_normalize_path(
                WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_module . '/');
        }
        return array_values(array_unique($jsst_dirs));
    }

    /**
     * What the product is sold as, for every screen that lists it.
     * (Roadmap 6.5-ECO-01)
     *
     * Four screens listed add-ons and each held its own copy of the names and
     * the sentences: the Addons List, the dashboard's "Available Addons", the
     * add-on status page (through `getJSSTAddonsArray()`) and the licence key
     * screen. All four still named the twenty-five plugins that were merged, so
     * the plugin advertised products that no longer exist and described the nine
     * that do in nobody's words at all.
     *
     * One reader, from the one manifest. The label and the summary are the
     * manifest's own - the same sentence a customer reads on the Plugins screen,
     * in the dashboard tile and on the status page - because four copies of a
     * sentence is four sentences the moment one of them is edited.
     *
     * `image` is the tile art. The nine have none of their own yet, so each
     * points at the closest of the twenty-five, which is honest enough for a
     * placeholder and never a missing file.
     */
    public static function catalogue() {
        $jsst_art = array(
            'agents'        => 'agent.png',
            'servicelevels' => 'ticket-auto-close.png',
            'emailsuite'    => 'email-piping.png',
            'knowledge'     => 'kb.png',
            'aiagent'       => 'instantresolve.png',
            'experience'    => 'multiform.png',
            'commerce'      => 'woocommerce.png',
            'integrations'  => 'mail-chimp.png',
            'reporting'     => 'ticket-history.png',
        );
        $jsst_out = array();
        foreach (self::bundles() as $jsst_slug => $jsst_bundle) {
            $jsst_file = 'js-support-ticket-' . $jsst_slug;
            $jsst_out[$jsst_file] = array(
                'slug'        => $jsst_slug,
                'title'       => $jsst_bundle['label'],
                'description' => $jsst_bundle['summary'],
                'plugin_file' => $jsst_file . '/' . $jsst_file . '.php',
                'url'         => 'https://jshelpdesk.com/product/' . $jsst_slug . '/',
                'image'       => isset($jsst_art[$jsst_slug]) ? $jsst_art[$jsst_slug] : 'logo.png',
                'installed'   => is_dir(self::bundlePath($jsst_slug)),
                'active'      => self::active($jsst_slug),
            );
        }
        return $jsst_out;
    }

    /**
     * Cache-busting version for a file a bundle ships.
     *
     * `jssupportticket::assetVersion()` does this for core, and resolves its
     * argument against `JSST_PLUGIN_PATH` - which is core's directory, so a
     * bundle cannot use it. This is the same rule for a file anywhere: the
     * product version with the file's own modification time appended, so the
     * URL changes exactly when the file does and never otherwise.
     *
     * Worth stating why it matters more here than it looks. A bundle's assets
     * were enqueued as `?ver=1.0.0` - a constant on the class, not a number
     * anybody bumps - so every edit to a stylesheet or a script shipped under a
     * URL the browser had already cached. The fix reached new visitors and
     * nobody else, and "it works for me after a hard reload" is the least
     * useful bug report a customer can send.
     *
     * Falls back to the version it is given, then to the product version, so a
     * file that cannot be stat'ed still enqueues.
     *
     * @param string $jsst_file     Absolute path to the asset.
     * @param string $jsst_fallback Version to use when it cannot be stat'ed.
     */
    public static function assetVersion($jsst_file, $jsst_fallback = '') {
        $jsst_version = ($jsst_fallback !== '') ? $jsst_fallback
            : (isset(jssupportticket::$_config['productversion'])
                ? jssupportticket::$_config['productversion']
                : jssupportticket::$_currentversion);
        if (!file_exists($jsst_file)) {
            return $jsst_version;
        }
        $jsst_mtime = filemtime($jsst_file);
        return $jsst_mtime ? $jsst_version . '.' . $jsst_mtime : $jsst_version;
    }

    /**
     * The add-on slugs that were active as plugin directories in their own
     * right, captured before augment() added the bundles' modules to the list.
     *
     * See the class docblock: after augmentation the live array cannot tell a
     * module that has a directory of its own from one that a bundle carries,
     * and the precedence rule needs exactly that distinction.
     */
    private static $jsst_legacy = null;

    /**
     * Modules that something asked for - a stand-alone add-on's directory or
     * the Pro companion - and augment() left out because their bundle is not
     * running them. module => bundle slug. What the switched-off notice lists.
     */
    private static $jsst_switched_off = array();

    /** Module => bundle, for modules still served by their old add-on. */
    private static $jsst_legacy_only = array();

    /* ------------------------------------------------------------------ *
     * The manifest
     * ------------------------------------------------------------------ */

    /**
     * Every bundle, filterable so that a bundle shipped after this release can
     * add itself without this file being edited.
     */
    public static function bundles() {
        return apply_filters('jsst_bundles', self::$jsst_bundles);
    }

    /** One bundle's manifest row, or false. */
    public static function bundle($jsst_slug) {
        $jsst_bundles = self::bundles();
        return isset($jsst_bundles[$jsst_slug]) ? $jsst_bundles[$jsst_slug] : false;
    }

    /**
     * Modules that are part of another module rather than features of their own.
     *
     * `JSSTincluder` has always resolved these into their parent add-on's
     * directory - `js-support-ticket-knowledgebase/articles/model.php` - through
     * a branch of its own, further down than the bundle branch. That branch is
     * the gap this map closes, and it is a real one: without it a site whose
     * Knowledge bundle is serving the knowledge base would still read its
     * article model out of the old plugin, so "the old one is doing nothing"
     * would be true of the screens and false of the code underneath them - and
     * would stop being true of anything at all the moment the customer did what
     * we told them and deleted it.
     *
     * The list is copied from that branch deliberately rather than derived from
     * it: the includer's version is a switch inside a function, and the two are
     * checked against each other by a test rather than by hoping.
     */
    private static $jsst_submodules = array(
        'articles'             => 'knowledgebase',
        'articleattachmet'     => 'knowledgebase',
        'articles_attachments' => 'knowledgebase',
        'categories'           => 'knowledgebase',
        'downloadattachment'   => 'download',
        'role'                 => 'agent',
        'acl_roles'            => 'agent',
        'acl_role_access_departments'  => 'agent',
        'acl_role_permissions'         => 'agent',
        'acl_user_access_departments'  => 'agent',
        'acl_user_permissions'         => 'agent',
        'roleaccessdepartments'        => 'agent',
        'rolepermissions'              => 'agent',
        'useraccessdepartments'        => 'agent',
        'userpermissions'              => 'agent',
    );

    /** The module a submodule belongs to, or '' when it is not one. */
    public static function parentOf($jsst_module) {
        return isset(self::$jsst_submodules[$jsst_module]) ? self::$jsst_submodules[$jsst_module] : '';
    }

    /** Every submodule, as submodule => parent module. */
    public static function submodules() {
        return self::$jsst_submodules;
    }

    /**
     * Which bundle carries this module, or '' if no bundle does.
     *
     * Answered from the manifest alone, so it is true whether or not the bundle
     * is installed - callers that need "installed and carrying it" ask
     * running() instead.
     *
     * A submodule is carried by whichever bundle carries its parent. It is not
     * listed in the manifest and must not be: the manifest is what a customer
     * is sold, and "Articles" is not a thing anybody bought separately from the
     * knowledge base.
     */
    public static function owner($jsst_module) {
        $jsst_parent = self::parentOf($jsst_module);
        if ($jsst_parent !== '') {
            $jsst_module = $jsst_parent;
        }
        foreach (self::bundles() as $jsst_slug => $jsst_bundle) {
            if (in_array($jsst_module, $jsst_bundle['modules'], true)) {
                return $jsst_slug;
            }
        }
        return '';
    }

    /**
     * Which bundle owns this Pro-era screen, or '' if none does.
     *
     * The ten screens that came out of the Pro companion are the one place a
     * bundle is addressed by layout rather than by module, because they never
     * had a module each: all ten were `page=pro&jstlay=<layout>` served by one
     * controller. Splitting them means splitting that switch, and this is the
     * map that says where each case went.
     */
    public static function screenOwner($jsst_layout) {
        foreach (self::bundles() as $jsst_slug => $jsst_bundle) {
            if (in_array($jsst_layout, $jsst_bundle['screens'], true)) {
                return $jsst_slug;
            }
        }
        return '';
    }

    /**
     * Every Pro-era screen this bundle claims, or an empty list.
     *
     * A separate reader from `screenOwner()` because the two questions run in
     * opposite directions and both are asked often: "who owns this layout" when
     * a link is drawn or a request is routed, and "what does this bundle draw"
     * when the plugin decides whether it is a wp-admin page at all.
     */
    public static function screensOf($jsst_slug) {
        $jsst_bundle = self::bundle($jsst_slug);
        return ($jsst_bundle === false) ? array() : $jsst_bundle['screens'];
    }

    /**
     * Does the installed bundle actually ship the screens it claims?
     *
     * The filesystem is asked rather than the manifest, for the same reason
     * `provides()` asks it about modules: the manifest says what a bundle *is*
     * and the directory says what this copy of it *ships*, and those differ
     * during a release. A bundle whose screens are declared but whose
     * `modules/<slug>` directory is not there yet must go on being served by
     * the Pro companion, because the alternative - routing a customer to
     * `page=reporting&jstlay=analytics` and having the includer find nothing
     * there - is a blank screen with nothing anywhere saying why, which is the
     * exact failure the includer's allow-list comment already records having
     * cost this codebase a debugging session.
     *
     * A bundle that claims no screens answers false, which is what makes the
     * five module-only bundles cost nothing here.
     */
    public static function screensRunning($jsst_slug) {
        if (self::screensOf($jsst_slug) === array() || !self::active($jsst_slug)) {
            return false;
        }
        return is_dir(self::bundlePath($jsst_slug) . 'modules/' . $jsst_slug);
    }

    /**
     * Which `page=` slug should serve this Pro-era layout right now.
     *
     * The one function the rest of the product asks about the screen split, and
     * the reason it answers a page slug rather than a boolean: every caller -
     * the side menu drawing a link, a task handler working out where to send
     * somebody back to, the Pro controller deciding whether this screen is
     * still its own - wants the same string, and computing it in three places
     * is how two of them come to disagree after a bundle ships.
     *
     * It falls back to `pro` rather than to `''`. Every one of these screens
     * came from the Pro companion and is still there; a site that has Pro and
     * no bundles is the ordinary case today, and it must go on getting exactly
     * the addresses it has always had.
     */
    public static function screenPage($jsst_layout) {
        $jsst_slug = self::screenOwner($jsst_layout);
        if ($jsst_slug !== '' && self::screensRunning($jsst_slug)) {
            return $jsst_slug;
        }
        return 'pro';
    }

    /**
     * Is this Pro-era screen being served by a bundle rather than by Pro?
     *
     * Asked from Pro's side, where it decides whether to draw the screen at all
     * or to hand the request on. Phrased as its own function rather than as
     * `screenPage() !== 'pro'` at each call site so that the Pro controller
     * reads as what it is doing - standing down - rather than as a string
     * comparison somebody has to work out.
     */
    public static function screenMoved($jsst_layout) {
        return self::screenPage($jsst_layout) !== 'pro';
    }

    /**
     * Every bundle slug that is currently serving screens of its own.
     *
     * This is the list the admin page registration walks: a bundle only needs
     * `add_submenu_page` if it draws something, and the four that do are
     * exactly the four that came out of Pro.
     */
    public static function screenBundles() {
        $jsst_found = array();
        foreach (self::bundles() as $jsst_slug => $jsst_bundle) {
            if (self::screensRunning($jsst_slug)) {
                $jsst_found[] = $jsst_slug;
            }
        }
        return $jsst_found;
    }

    /** Every module every bundle carries, flattened. */
    public static function allModules() {
        $jsst_all = array();
        foreach (self::bundles() as $jsst_bundle) {
            foreach ($jsst_bundle['modules'] as $jsst_module) {
                $jsst_all[] = $jsst_module;
            }
        }
        return $jsst_all;
    }

    /* ------------------------------------------------------------------ *
     * What is installed
     * ------------------------------------------------------------------ */

    /**
     * The pre-augmentation list: what was on disk as a plugin directory.
     *
     * Falls back to the live array when augment() has not run, which is the
     * case in the unit-test bootstrap and on any path that reaches a resolver
     * before the plugin has finished constructing itself. Before augmentation
     * the two are the same list, so the fallback is correct rather than merely
     * safe.
     */
    private static function legacyList() {
        if (self::$jsst_legacy !== null) {
            return self::$jsst_legacy;
        }
        return is_array(jssupportticket::$_active_addons) ? jssupportticket::$_active_addons : array();
    }

    /**
     * Is `js-support-ticket-<slug>` an active plugin on this site?
     *
     * Asked of the plugin list itself rather than of
     * `jssupportticket::$_active_addons`, and that is not a detail. That array
     * is the answer to "is this feature available?", which `JSSTpro::augment()`
     * and `augment()` below both widen - so by the time anything reads it, a
     * module in it may be one no plugin directory carries. The two questions
     * this class asks are about directories: is the bundle really installed,
     * and is the add-on it replaces really still switched on. Nothing else can
     * answer those.
     *
     * It is also what makes the answer correct in a request that has changed
     * which plugins are active - an activation handler, or a test - where the
     * snapshot taken during the bootstrap is by definition out of date.
     */
    private static function pluginActive($jsst_slug) {
        /* `jsst_bundle_enabled` lets an installed, active add-on say it is not
           running on this site, and the core then treats it exactly as it
           treats one that is not installed: its modules, screens and settings
           are not offered, and an older stand-alone add-on, if there is one,
           goes on serving instead. The paid add-ons answer it from their own
           licence check (their includes/licence-gate.php); the core decides
           nothing here. Asked of every slug, add-on or bundle, so the answer
           is the same wherever the question is put. (30 September 2026) */
        if (!apply_filters('jsst_bundle_enabled', true, $jsst_slug)) {
            return false;
        }
        $jsst_file = 'js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php';
        if (in_array($jsst_file, (array) get_option('active_plugins', array()), true)) {
            return true;
        }
        /* Network-activated plugins are in neither the site option nor this
           blog's list, and a multisite agency running the help desk across
           forty sites is precisely the customer these bundles are for. */
        if (is_multisite()) {
            $jsst_network = (array) get_site_option('active_sitewide_plugins', array());
            return isset($jsst_network[$jsst_file]);
        }
        return false;
    }

    /**
     * Is this bundle installed and active as a plugin of its own?
     */
    public static function active($jsst_slug) {
        return self::pluginActive($jsst_slug);
    }

    /**
     * Is the stand-alone add-on this module used to come from still active?
     *
     * No longer the precedence rule - an installed bundle wins whatever this
     * answers. What it decides now is whether there is an older plugin sitting
     * there doing nothing, which is what the Plugins-screen row, the notice and
     * the settings guard are all about.
     */
    public static function legacyActive($jsst_module) {
        return self::pluginActive($jsst_module);
    }

    /** Where a bundle's files are. */
    public static function bundlePath($jsst_slug) {
        return WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_slug . '/';
    }

    /**
     * Does the installed bundle actually carry this module's code?
     *
     * Checked against the filesystem rather than against the manifest, for the
     * reason `JSSTpro::provides()` gives: the manifest says what a bundle *is*
     * and the directory says what this copy of it *ships*. Those differ during
     * a release, and resolving a module into a directory that is not there is a
     * fatal rather than a missing feature.
     */
    public static function provides($jsst_module) {
        $jsst_slug = self::owner($jsst_module);
        if ($jsst_slug === '' || !self::active($jsst_slug)) {
            return false;
        }
        /* A submodule is owned with its parent, and the parent's directory is
           what is checked. A bundle that ships the knowledge base ships its
           articles: treating them as separately shippable would let one request
           read the article list from the bundle and the article itself from the
           old plugin, which is the half-loaded state this whole mechanism is
           built to make impossible. */
        $jsst_parent = self::parentOf($jsst_module);
        $jsst_check = ($jsst_parent !== '') ? $jsst_parent : $jsst_module;
        return is_dir(self::bundlePath($jsst_slug) . 'modules/' . $jsst_check);
    }

    /**
     * Should this module be served from its bundle right now?
     *
     * One question, not two: has an installed bundle got the code. Whether the
     * stand-alone add-on it replaces is also still active is not part of the
     * answer - see the precedence note in the class docblock. That add-on is
     * superseded rather than competed with, and `supersedes()` is the same fact
     * asked from its side.
     *
     * The Pro companion is a different matter and keeps its precedence, which
     * is settled in `JSSTincluder::getPluginPath()` by asking Pro first rather
     * than by anything here. Pro is a companion somebody chose to add; a bundle
     * is an add-on reissued.
     */
    public static function running($jsst_module) {
        return self::provides($jsst_module);
    }

    /**
     * Is this module's stand-alone add-on superseded by an installed bundle?
     *
     * The gate a legacy add-on's own bootstrap asks before it includes a single
     * file or registers a single hook. Deliberately the whole of what an add-on
     * has to know: not which bundle, not what version, not whether the customer
     * has been told - one boolean, so that the line added to twenty-seven
     * plugins is the same line in all twenty-seven and cannot be got subtly
     * wrong in one of them.
     *
     * It answers false unless a bundle is actually installed *and* ships the
     * code, so an add-on asking it on a site with no bundles carries on exactly
     * as it always has. That is the property that makes it safe to ship this
     * line in an add-on update long before the bundle reaches anybody.
     */
    public static function supersedes($jsst_module) {
        /* Or replaced outright by an installed bundle rather than carried by one
           - see `$jsst_retired`. Same answer to the add-on asking it, for the
           same reason: the thing that replaced this is here, so stand down. */
        return self::provides($jsst_module) || self::retiredBy($jsst_module) !== '';
    }

    /* ------------------------------------------------------------------ *
     * Resolution
     * ------------------------------------------------------------------ */

    /**
     * Where a bundled module's file lives, or '' if this module is not one.
     *
     * `getPluginPath()` asks this after `JSSTpro::modulePath()` and before its
     * own add-on branch, so a bundle wins over core's fallback and loses to a
     * stand-alone add-on - running() having already said no in that case.
     *
     * The layout inside a bundle is the core layout, `modules/<slug>/model.php`
     * rather than `module/model.php`, matching the Pro companion. Thirty add-ons
     * each having their own directory shape is one of the things this work
     * exists to end, and a bundle is a modular monolith, so it is shaped like
     * one.
     */
    public static function modulePath($jsst_module, $jsst_type, $jsst_file_name = '') {
        /* A bundle that owns screens is addressable as a module under its own
           slug, and is the only module in this product whose name is a bundle
           name. That is not a special case invented here: the Pro companion has
           always worked this way - `page=pro` is a module called `pro` served
           out of `js-support-ticket-pro/module/` - and a bundle taking those
           screens over has to be reachable by exactly the same machinery, or
           the form handler cannot find `JSSTreportingController` and every Save
           on those screens silently does nothing.

           Asked before running(), because running() is about the merged add-ons
           and answers false for a bundle slug: no bundle's `modules` list
           contains a bundle. */
        if (self::screensRunning($jsst_module)) {
            $jsst_path = self::bundlePath($jsst_module);
        } else {
            if (!self::running($jsst_module)) {
                return '';
            }
            $jsst_path = self::bundlePath(self::owner($jsst_module));
        }
        /* A shared module is the exception to "owned whole", and the only one.
           Core and a bundle each ship part of it on purpose - core the screens
           everybody has, the bundle the screens only its customers have - so
           here, and nowhere else, the question is asked file by file: if core
           ships this particular file, core's copy is the one, and returning ''
           lets JSSTincluder::getPluginPath() fall through to it.

           Only files core actually ships are given up. Anything else still
           comes from the bundle, so the half it owns cannot go missing, and a
           module that is not shared is unaffected by any of this.
           (Roadmap 6.5-FORM-02) */
        if (class_exists('JSSTincluder')
                && method_exists('JSSTincluder', 'sharedModules')
                && in_array($jsst_module, JSSTincluder::sharedModules(), true)) {
            $jsst_core = JSSTincluder::corePath($jsst_module, $jsst_type, $jsst_file_name);
            if ($jsst_core !== '' && file_exists($jsst_core)) {
                return '';
            }
        }
        switch ($jsst_type) {
            case 'file':
                $jsst_file = ($jsst_file_name !== '')
                    ? $jsst_path . 'modules/' . $jsst_module . '/tpls/' . $jsst_file_name . '.php'
                    : $jsst_path . 'modules/' . $jsst_module . '/controller.php';
                break;
            case 'model':
                $jsst_file = $jsst_path . 'modules/' . $jsst_module . '/model.php';
                break;
            case 'class':
                $jsst_file = $jsst_path . 'includes/classes/' . $jsst_module . '.php';
                /* A bundle keeps a *module's* classes in a directory of that
                   module's own - `includes/classes/agent/teams.php`,
                   `includes/classes/overdue/sla.php` - and only its own
                   bundle-wide screen classes flat beside them
                   (`analytics.php`, `brand.php`). All nine are built that way.
                   The flat spelling above is the second of those and was the
                   only one asked for, which is right until a class is named
                   after the module carrying it.
                   `JSSTprivatecredentials` is: the file is
                   `includes/classes/privatecredentials/privatecredentials.php`,
                   the flat path does not exist, and `getObjectClass()` includes
                   whatever it is handed and instantiates it unconditionally. So
                   closing a ticket with the Private Credentials module
                   installed - the one thing that calls it - ended in two
                   include warnings and a fatal, after the ticket had already
                   been closed and its e-mail sent. (Roadmap 6.5-ECO-01)

                   Checked rather than assumed, unlike the bundle-versus-core
                   choice below: this is two spellings of a path inside one
                   bundle, not two copies of a feature in two plugins, so
                   picking the one that is actually there cannot half-load
                   anything. */
                if (!file_exists($jsst_file)) {
                    $jsst_nested = $jsst_path . 'includes/classes/' . $jsst_module
                        . '/' . ($jsst_file_name !== '' ? $jsst_file_name : $jsst_module) . '.php';
                    if (file_exists($jsst_nested)) {
                        $jsst_file = $jsst_nested;
                    }
                }
                break;
            case 'controller':
                $jsst_file = $jsst_path . 'modules/' . $jsst_module . '/controller.php';
                break;
            case 'table':
                $jsst_file = $jsst_path . 'includes/tables/' . $jsst_module . '.php';
                break;
            default:
                return '';
        }
        /* Returned whether or not the file is there, for the reason
           JSSTpro::modulePath() gives: provides() has already established that
           this bundle owns the module, and a module is owned whole. Handing
           back core's copy of one file because the bundle happened not to ship
           it is how two implementations of one feature end up half-loaded
           together, which is a worse failure than the missing file it would
           paper over. */
        return $jsst_file;
    }

    /* ------------------------------------------------------------------ *
     * Making the rest of the plugin see it
     * ------------------------------------------------------------------ */

    /**
     * Make every module a bundle is running answer the availability checks that
     * are already written all over this plugin.
     *
     * Same argument as `JSSTpro::augment()`, and called immediately after it:
     * there are several hundred `in_array('faq', jssupportticket::$_active_addons)`
     * tests in core, in the menus, in the admin side menu and in the templates,
     * and every one of them means "is this feature available?". Rewriting them
     * all to ask a new question would be a very large change with a very large
     * number of places to get one wrong, and a module missed would be a feature
     * that silently does not appear. Answering the question they already ask is
     * the same outcome with none of that risk.
     *
     * Three things depend on this beyond the availability checks, and each one
     * would be a silent, hard-to-diagnose loss if it were missed:
     *
     *  - **The wp-admin pages.** `jssupportticketadmin.php` registers
     *    `add_submenu_page` with the add-on's own slug as the menu slug, gated
     *    on this array. Without augmentation `page=overdue` is not a registered
     *    page, and WordPress answers "Sorry, you are not allowed to access this
     *    page" - which reads as a permissions problem and is not one.
     *  - **The settings screens.** Every configuration row is tagged in
     *    `js_ticket_config.addon` with the add-on's slug, and the configuration
     *    screen decides what to draw by looking that tag up in this array.
     *    Without augmentation those settings are in the table, honoured by the
     *    code, and on no screen - a worse way to lose a setting than deleting
     *    it, because nothing says so.
     *  - **The front-end desk.** `includes/header.php` chooses staff layouts
     *    over customer ones by asking this array about `agent`.
     *
     * It also works the other way: a module that a bundle carries is available
     * only while that bundle is running it. The stand-alone add-on on its own,
     * or the Pro companion on its own, no longer makes it available - the
     * licence server decides which bundles a customer's old add-ons are worth,
     * and a module whose bundle is not here is a module this site was not given.
     * Everything above follows from this one array, so the menus, pages,
     * settings and templates all agree without asking a second question.
     *
     * Called from the bootstrap immediately after the list is built and after
     * JSSTpro::augment(), and before anything has had a chance to read it.
     */
    public static function augment() {
        if (self::$jsst_legacy !== null) {
            return;
        }
        /* Captured after Pro has augmented, and deliberately so. Pro took its
           own snapshot before adding anything, so the array at this moment is
           the directory list plus Pro's modules - and a module Pro is already
           serving must not then be resolved into a bundle as well. Treating
           Pro's contribution as "legacy" here is what stops that: running()
           sees the module as already provided and stands down, exactly as it
           does for a stand-alone add-on. */
        self::$jsst_legacy = is_array(jssupportticket::$_active_addons) ? jssupportticket::$_active_addons : array();

        /* A bundled module stays only while its bundle runs it, whoever put it
           in the list - a stand-alone add-on's directory or the Pro companion.
           Which of those then serves the file is still getPluginPath()'s
           business and unchanged; this only decides whether it is offered. */
        /* An old add-on whose bundle is not here keeps working, served from
           its own directory by getPluginPath()'s add-on branch, as it did
           under 4.0.0. Decided 29 Sep 2026: a customer whose licence does not
           include the bundle - expired, or a free add-on key - updates the
           core from wordpress.org, often automatically, and must not lose a
           feature they bought and were using. The bundle, once installed,
           still wins: running() answers true and the bundle branch comes
           first. So nothing lands in $jsst_switched_off any more and its
           notice stays quiet; what is still on an old add-on is kept in
           $jsst_legacy_only for anything that wants to offer the bundle. */
        $jsst_available = array();
        foreach (self::$jsst_legacy as $jsst_module) {
            $jsst_available[] = $jsst_module;
            if (self::owner($jsst_module) !== '' && !self::running($jsst_module)) {
                self::$jsst_legacy_only[$jsst_module] = self::owner($jsst_module);
            }
        }
        foreach (self::allModules() as $jsst_module) {
            if (!in_array($jsst_module, $jsst_available, true) && self::running($jsst_module)) {
                $jsst_available[] = $jsst_module;
            }
        }
        /* And the four bundles that took screens over from the Pro companion,
           under their own slugs. `page=reporting` is a registered wp-admin page
           only if `jssupportticketadmin.php` finds `reporting` in this array -
           without it WordPress answers "Sorry, you are not allowed to access
           this page", which reads as a permissions problem and is not one. This
           is the same reason Time Tracking had to be added to the feature-page
           list when it grew its first screen, written down there at length.

           Adding it here is safe in the way adding a settings alias is not: a
           bundle slug is a real directory, so the second job this array does -
           `getPluginPath()` reading it as "there is a js-support-ticket-<slug>
           directory carrying this module" - is *true* of it, and resolves to
           the bundle's own controller and templates. */
        foreach (self::screenBundles() as $jsst_slug) {
            if (!in_array($jsst_slug, $jsst_available, true)) {
                $jsst_available[] = $jsst_slug;
            }
        }
        jssupportticket::$_active_addons = $jsst_available;
    }

    /* ------------------------------------------------------------------ *
     * Settings tags
     * ------------------------------------------------------------------ */

    /**
     * The `js_ticket_config.addon` tags of every module a bundle is running,
     * where that tag is not simply the module's slug.
     *
     * Three add-ons tag their settings rows with something other than their own
     * name: Automatic Assignment and Email CC both write `email`, and
     * Multilingual Email Templates writes the tag singular while installing
     * itself plural. `JSSTlegacy` already had to know all three for the
     * migration, so they are read from its footprint rather than written down a
     * second time here and allowed to drift apart. (Roadmap 4.5-PRO-02)
     */
    public static function configAliases() {
        $jsst_aliases = array();
        if (!class_exists('JSSTlegacy')) {
            return $jsst_aliases;
        }
        foreach (self::allModules() as $jsst_module) {
            if (!self::running($jsst_module)) {
                continue;
            }
            $jsst_tag = JSSTlegacy::footprint($jsst_module)['configtag'];
            if ($jsst_tag !== $jsst_module && !in_array($jsst_tag, $jsst_aliases, true)) {
                $jsst_aliases[] = $jsst_tag;
            }
        }
        return $jsst_aliases;
    }

    /**
     * Should a settings row carrying this `addon` tag be drawn?
     *
     * This exists because the obvious implementation is a latent fatal, and the
     * fatal is worth writing down so nobody reintroduces it.
     *
     * The obvious implementation is to push the alias into
     * `jssupportticket::$_active_addons` and let the configuration screen's
     * existing `in_array()` find it - which is what `JSSTpro::augment()` used to
     * do. But that array has a second, unrelated job: `getPluginPath()` reads it
     * as "there is a js-support-ticket-<slug> directory carrying this module".
     * Automatic Assignment and Email CC both tag their rows `email`, so pushing
     * that alias tells the includer there is a `js-support-ticket-email` plugin
     * carrying core's own `modules/email` - and the next `getJSModel('email')`
     * includes a path that does not exist and instantiates a class that was
     * never declared. That is a fatal on every screen that sends mail, not a
     * missing setting.
     *
     * It has never fired only because the Pro companion ships no module
     * directories yet, so `JSSTpro::running()` answers false for all of them and
     * the alias list comes back empty. A bundle carrying Email CC would have
     * woken it on the first request.
     *
     * So settings tags are answered here instead, and the availability array is
     * left to mean exactly one thing.
     */
    public static function settingsTagActive($jsst_tag) {
        /* No tag means the setting belongs to free core, and the column says
           that two ways: most rows hold SQL NULL and a couple hold the empty
           string. The check this replaced was `$config->addon == ''`, which is
           loose and so answered true for both; `=== ''` is strict and answered
           false for NULL, which quietly took **every** untagged setting off the
           configuration screen - attachments, captcha, anonymous replies, the
           AI defaults, 126 rows on a normal desk. The screen showed nothing and
           said nothing, because each field is drawn behind an isset() on data
           that never arrived. (Roadmap 6.5-ECO-01) */
        if ($jsst_tag === '' || $jsst_tag === null) {
            return true;
        }
        if (in_array($jsst_tag, (array) jssupportticket::$_active_addons, true)) {
            return true;
        }
        if (in_array($jsst_tag, self::configAliases(), true)) {
            return true;
        }
        /* Pro's own aliases, for a site whose modules come from the companion
           rather than from a bundle. Asked second because it is the rarer case
           and it walks the whole Pro manifest to answer. */
        if (class_exists('JSSTlegacy') && in_array($jsst_tag, JSSTlegacy::configAliases(), true)) {
            return true;
        }
        return false;
    }

    /* ------------------------------------------------------------------ *
     * What a site actually has
     * ------------------------------------------------------------------ */

    /**
     * Every stand-alone add-on on this site that a bundle would otherwise
     * carry.
     *
     * This is the list the "you can deactivate these now" notice is built from,
     * and the list a migration screen would work through. It is deliberately
     * not acted on automatically: deactivating a legacy add-on is not a safe
     * operation - twenty-three of them zero every one of their config rows on
     * the way out - and `JSSTlegacy` exists because of exactly that. Nothing
     * here may pre-empt it.
     */
    public static function supersededAddons($jsst_slug) {
        $jsst_bundle = self::bundle($jsst_slug);
        if ($jsst_bundle === false || !self::active($jsst_slug)) {
            return array();
        }
        $jsst_found = array();
        foreach ($jsst_bundle['modules'] as $jsst_module) {
            if (self::legacyActive($jsst_module)) {
                $jsst_found[] = $jsst_module;
            }
        }
        return $jsst_found;
    }

    /**
     * Every stand-alone add-on on this site that an installed bundle has taken
     * over, as module slug => bundle slug.
     *
     * Not the same question as `supersededAddons()` above, which answers it for
     * one bundle. This is the site-wide list, and it is what the Plugins screen
     * rows, the admin notice and the settings guard are all built from - one
     * list, so a customer cannot be told a plugin is superseded on one screen
     * and left unprotected on another.
     */
    public static function superseded() {
        $jsst_found = array();
        foreach (self::bundles() as $jsst_slug => $jsst_bundle) {
            foreach ($jsst_bundle['modules'] as $jsst_module) {
                if (self::legacyActive($jsst_module) && self::provides($jsst_module)) {
                    $jsst_found[$jsst_module] = $jsst_slug;
                }
            }
        }
        /* And the ones a bundle replaced outright, which no `modules` list names
           and which were therefore missing from every screen this list feeds -
           the whole point of `$jsst_retired`. */
        foreach (self::retired() as $jsst_module => $jsst_slug) {
            if (self::legacyActive($jsst_module) && self::active($jsst_slug)) {
                $jsst_found[$jsst_module] = $jsst_slug;
            }
        }
        return $jsst_found;
    }

    /* ------------------------------------------------------------------ *
     * Telling the customer, and guarding what happens when they act
     * ------------------------------------------------------------------ */

    /** Where a superseded add-on's settings are parked across its deactivation. */
    const OPT_GUARD = 'jsst_bundle_settings_guard';

    /** The option namespace this class's table snapshots are recorded under. */
    const SNAPSHOT_NS = 'jsst_bundle_addon';

    /** User meta holding which set of replaced add-ons this admin dismissed. */
    const META_DISMISSED = 'jsst_bundle_notice_dismissed';

    /**
     * The tables each superseded add-on's uninstall.php drops that the bundle
     * replacing it now owns. (Roadmap 6.5-ECO-02)
     *
     * This is the list that did not exist, and its absence is the whole bug.
     * `protectSettings()` parked a deactivating add-on's *settings*, which is
     * the loud failure - twenty-three of these add-ons zero their config rows
     * on the way out. Deletion is the quiet one: WordPress runs the add-on's
     * own uninstall.php, that file drops its tables, and the tables are the
     * bundle's now. A site that did exactly what the notice told it to do -
     * deactivate the old add-on, then delete it - lost every table named below,
     * thirty of them.
     *
     * Taken from each add-on's own uninstall.php - the archived copies in
     * wp-content/plugins-13.zip, which is the last archive that still has them.
     * Every draft of this map that read a bundle's *activation* SQL instead got
     * it wrong, and got it wrong in both directions, because activation SQL
     * answers a different question: what the bundle creates, not what the
     * add-on's uninstall destroys.
     *
     *   Too little. `feedback`, `instantresolve` and `autoclose` were missing
     *   outright, the last of them because its uninstall drops a table its
     *   activation never created.
     *
     *   Too much. Eight tables were listed here that no add-on's uninstall has
     *   ever dropped, because they are the bundles' own. The collaboration
     *   tables - js_ticket_collab_drafts, js_ticket_mentions,
     *   js_ticket_watchers - are created by Agents & Teams' own collab.php, and
     *   the workflow, recurring and SLA tables by Service Levels' activation
     *   SQL. The stand-alone add-ons those entries named are Ticket Agent
     *   1.3.2, Auto Close 1.1.0 and Ticket Overdue 1.1.6, and not one line of
     *   any of them has ever heard of collaboration, workflows or service
     *   levels: 5.0-SLA-01 built SLA inside the overdue *module*, not inside
     *   the add-on of that name. Ticket Overdue's uninstall drops nothing at
     *   all, which is why it has no entry here now.
     *
     *   Live chat, which used to have an entry, never shipped stand-alone -
     *   there is no js-support-ticket-livechat in any archive, so there is no
     *   uninstall.php to guard against. That entry came off the Customer
     *   Experience bundle's activation SQL, which is also where the warning
     *   below about js_ticket_tickets and js_ticket_users came from: core's
     *   tables, mentioned by a bundle's activation, never any add-on's to drop
     *   and never to be renamed over by a restore.
     *
     * So when a module is added here, its DROP TABLE list comes out of the
     * archive and is diffed against this map; it is never inferred from
     * activation SQL, and never from the module's name. A module whose
     * uninstall drops nothing gets no entry - `mergeticket`, `smtp`,
     * `woocommerce`, `easydigitaldownloads`, `envatovalidation`, `paidsupport`
     * and `mailchimp` are all absent for that reason, and each of those has
     * been read rather than assumed.
     *
     * Padding the lists is not free, even though `restore()` discards the
     * snapshot of a table that is still live. `snapshotBeforeUninstall()`
     * copies every table named here, and js_ticket_sla_clocks is a row per
     * ticket - so the two entries that guarded nothing bought a full copy of
     * some of the largest tables on the desk, on the uninstall path, against an
     * uninstall that was never going to touch them. Generous *gating* is the
     * cheap kind of generous, and `snapshotBeforeUninstall()` says why it is
     * worth paying for; a generous table list is not the same bargain.
     *
     * Deliberately NOT derived at runtime: this has to be right during an
     * uninstall, when the add-on being deleted is half gone and its own files
     * are the wrong thing to be parsing.
     */
    private static $jsst_owned_tables = array(
        // Agents & Teams
        'agent' => array('js_ticket_staff', 'js_ticket_userrole', 'js_ticket_acl_roles',
            'js_ticket_acl_permissions', 'js_ticket_acl_role_permissions',
            'js_ticket_acl_role_access_departments', 'js_ticket_acl_user_permissions',
            'js_ticket_acl_user_access_departments'),
        'agentautoassign'    => array('js_ticket_agentautoassign'),
        'privatecredentials' => array('js_ticket_privatecredentials'),
        'timetracking'       => array('js_ticket_staff_time'),
        // Knowledge & Self-Service
        'announcement'  => array('js_ticket_announcements'),
        'download'      => array('js_ticket_downloads', 'js_ticket_downloads_attachments'),
        'faq'           => array('js_ticket_faqs'),
        'knowledgebase' => array('js_ticket_articles', 'js_ticket_articles_attachments',
            'js_ticket_categories'),
        // Email
        'emailcc'                     => array('js_ticket_emailcc'),
        'emailpiping'                 => array('js_ticket_ticketsemail'),
        'mail'                        => array('js_ticket_staff_mail'),
        'multilanguageemailtemplates' => array('js_ticket_multilanguageemailtemplates'),
        // Customer Experience
        'multiform' => array('js_ticket_multiform'),
        'feedback'  => array('js_ticket_feedbacks'),
        // Integrations
        'notification' => array('js_ticket_notification_data'),
        /* Service Levels & Automation, which is one entry of one table.
         *
         * js_ticket_announcements is the whole of the autoclose entry and that
         * is not a mistake: the old Auto Close add-on's uninstall.php drops the
         * *announcements* table, and drops nothing else. Almost certainly a
         * copy-paste from the Announcement add-on that nobody caught, and
         * harmless while both were that customer's own add-ons - but Knowledge
         * & Self-Service owns that table now, so deleting Auto Close destroys
         * the announcements of a desk that never had an announcements add-on to
         * delete. The map has to describe what the uninstall actually does and
         * not what it ought to do, which is the same reason `overdue` is not
         * here at all: its uninstall deletes one option and drops no table.
         */
        'autoclose' => array('js_ticket_announcements'),
        /* Instant Resolve, retired rather than carried: the AI Agent bundle
           replaced it outright and carries no module for it. Its tables are
           still read by the code that replaced it, so its uninstall is still
           destroying live data - retired is not the same as unused. */
        'instantresolve' => array('js_ticket_instantfix_data', 'js_ticket_instantfix_sources',
            'js_ticket_instantfix_analytics', 'js_ticket_autopilot_logs', 'js_ticket_ir_chunks'),
    );

    /** The owned-table map, filterable so a bundle can add its own. */
    public static function ownedTables($jsst_module) {
        $jsst_map = apply_filters('jsst_bundle_owned_tables', self::$jsst_owned_tables);
        return isset($jsst_map[$jsst_module]) ? (array) $jsst_map[$jsst_module] : array();
    }

    /**
     * Copy a superseded add-on's tables before its uninstall.php drops them.
     *
     * Gated on the map alone, and NOT on supersedes(), which is the obvious way
     * to write this and is wrong. supersedes() asks whether the replacement is
     * *active right now*: retiredBy() returns '' the moment the replacing
     * bundle is deactivated. So a site that switched a bundle off and then
     * tidied up the old add-on got no snapshot at all - tested, and it dropped
     * five Instant Resolve tables that core still reads.
     *
     * Membership of $jsst_owned_tables is the honest test, because that is
     * exactly what the map records: tables that outlive the add-on whose
     * uninstall drops them. Whether the thing that now reads them happens to be
     * switched on this afternoon does not change who the data belongs to. Being
     * generous here costs a copy that restore() will discard, and restore()
     * never overwrites a live table - so the failure mode of snapshotting too
     * much is a moment of disk, while the failure mode of snapshotting too
     * little is the customer's data.
     */
    public static function snapshotBeforeUninstall($jsst_plugin) {
        $jsst_module = JSSTaddonsnapshot::slugFromPlugin($jsst_plugin);
        if ($jsst_module === '') {
            return;
        }
        $jsst_tables = self::ownedTables($jsst_module);
        if (empty($jsst_tables)) {
            return;
        }
        JSSTaddonsnapshot::snapshot(self::SNAPSHOT_NS, $jsst_module, $jsst_tables);
    }

    /** Put back whatever that uninstall actually removed. */
    public static function restoreAfterDelete($jsst_plugin, $jsst_deleted) {
        if (!$jsst_deleted) {
            return;
        }
        $jsst_module = JSSTaddonsnapshot::slugFromPlugin($jsst_plugin);
        if ($jsst_module === '') {
            return;
        }
        /* supersedes() is deliberately not re-asked here. By this point the
           add-on's directory is gone, and anything that reads the filesystem to
           decide can answer differently than it did a moment ago in
           snapshotBeforeUninstall(). A snapshot recorded under this namespace
           is proof enough that we took one and therefore that we should put it
           back; restore() is a no-op when there is nothing recorded. */
        if (JSSTaddonsnapshot::restore(self::SNAPSHOT_NS, $jsst_module)) {
            update_option('jsst_bundle_restored_' . $jsst_module, 1, false);
        }
    }

    public static function registerHooks() {
        add_action('admin_notices', array(__CLASS__, 'notice'));
        add_action('wp_ajax_jsst_dismiss_bundle_notice', array(__CLASS__, 'dismissNotice'));
        add_action('admin_notices', array(__CLASS__, 'switchedOffNotice'));

        /* Take a retired add-on's own hooks back off. Registered here rather
           than acted on here: `JSSTmergedaddon::register()` runs a sweep before
           this line and another on `plugins_loaded` at PHP_INT_MAX, and it is
           the second one that matters - it is the last moment before `init`, and
           it catches an add-on that hooked itself from its own `plugins_loaded`
           callback. Which is exactly what the old Instant Resolve does with its
           cron. (Roadmap 6.5-ECO-01) */
        add_filter('jsst_suppressed_addon_dirs', array(__CLASS__, 'retiredDirs'));

        /* One row under each superseded plugin, on the screen where somebody is
           already looking at that plugin and deciding what to do with it. An
           admin notice alone would be dismissed and never seen again; a row
           under the plugin is there every time they look. */
        foreach (self::superseded() as $jsst_module => $jsst_ignored) {
            add_action('after_plugin_row_' . JSSTlegacy::pluginFile($jsst_module),
                array(__CLASS__, 'pluginRow'), 10, 2);
        }

        /* Both halves of the guard, and they only mean anything together. */
        add_action('deactivate_plugin', array(__CLASS__, 'holdSettings'), 1, 1);
        add_action('deactivated_plugin', array(__CLASS__, 'restoreSettings'), 99, 1);
        /* And the same guard over deletion, which this class did not have.
           Deactivation was covered because it is the loud failure; deleting the
           add-on runs its uninstall.php, which drops tables the bundle now owns,
           and nothing stood between that and the customer's data.
           (Roadmap 6.5-ECO-02) */
        add_action('pre_uninstall_plugin', array(__CLASS__, 'snapshotBeforeUninstall'), 10, 1);
        add_action('deleted_plugin', array(__CLASS__, 'restoreAfterDelete'), 10, 2);
    }

    /**
     * "These are now part of a bundle. You can remove them."
     *
     * Shown on help desk screens only. A site administrator who has never heard
     * of this plugin should not meet its packaging decisions on their posts
     * list, and a notice that appears everywhere is a notice people learn to
     * dismiss without reading - which is the opposite of what this one needs.
     */
    public static function notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $jsst_screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $jsst_here = ($jsst_screen && strpos((string) $jsst_screen->id, 'jssupportticket') !== false)
            || ($jsst_screen && $jsst_screen->id === 'plugins');
        if (!$jsst_here) {
            return;
        }
        $jsst_superseded = self::superseded();
        if (empty($jsst_superseded)) {
            return;
        }
        /* Dismissible, and the dismissal holds for this set of add-ons only:
           another one being replaced later is news again. The row under each
           old plugin on the Plugins screen is not dismissible and goes on
           saying it for as long as the plugin is there. */
        $jsst_key = self::noticeKey($jsst_superseded);
        if (get_user_meta(get_current_user_id(), self::META_DISMISSED, true) === $jsst_key) {
            return;
        }
        /* Grouped under the plugin that replaces them, and named the way the
           Plugins screen names them.

           This was one run-on line of `module → Bundle` pairs joined by
           middots: nine of them on a full site, the bundle's name repeated
           after every one, and the left-hand side a raw module slug -
           `agentautoassign`, `envatovalidation` - which is an internal name
           the customer has never seen. Nobody could match that list against
           the plugins they actually have.

           So: one heading per bundle, the add-ons under it, and each add-on
           called what its own plugin header calls it, because that is the
           string the reader is looking at on the Plugins screen while they
           read this. The slug is kept beside it only where the header cannot
           be read. */
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $jsst_groups = array();
        foreach ($jsst_superseded as $jsst_module => $jsst_slug) {
            $jsst_bundle = self::bundle($jsst_slug);
            $jsst_label = ($jsst_bundle === false) ? $jsst_slug : $jsst_bundle['label'];
            $jsst_path = WP_PLUGIN_DIR . '/' . JSSTlegacy::pluginFile($jsst_module);
            $jsst_name = '';
            if (@is_readable($jsst_path)) {
                $jsst_header = get_plugin_data($jsst_path, false, false);
                $jsst_name = isset($jsst_header['Name']) ? trim($jsst_header['Name']) : '';
            }
            $jsst_groups[$jsst_label][] = ($jsst_name !== '') ? $jsst_name : $jsst_module;
        }
        ksort($jsst_groups);

        /* Every one of these headers begins "JS Help Desk ", so at nine groups
           the phrase is printed twenty-five times and pushes the only part that
           distinguishes them off the end of the column. It is dropped once,
           said once in the heading, and dropped only where it is actually the
           prefix - a header that reads differently is left exactly as it is,
           because matching this list against the Plugins screen is the whole
           reason the names are here rather than the module slugs. */
        $jsst_prefix = 'JS Help Desk ';
        foreach ($jsst_groups as $jsst_label => $jsst_addons) {
            foreach ($jsst_addons as $jsst_i => $jsst_one) {
                if (strpos($jsst_one, $jsst_prefix) === 0) {
                    $jsst_groups[$jsst_label][$jsst_i] = substr($jsst_one, strlen($jsst_prefix));
                }
            }
        }

        echo '<div class="notice notice-warning is-dismissible jsst-bundle-notice"><p><strong>'
            . esc_html(__('These help desk add-ons have been replaced.', 'js-support-ticket'))
            . '</strong> '
            /* Which of the pair is running is the one fact a reader acts on, so
               it is worth being exact about. The bundle wins: `getPluginPath()`
               resolves every model, controller, table and template into it, and
               `retiredDirs()` takes the old add-on's callbacks back off the
               hooks the bundle now serves. The old plugin is switched on and
               inert.

               This paragraph said the reverse until now - that the older plugin
               was the one running and the bundle stood aside - which was an
               earlier draft of the precedence rule, reverted in the class
               docblock above but never here. Getting it backwards is the
               expensive direction: it invites somebody to keep the plugin that
               is doing nothing and deactivate the one serving their desk. */
            . esc_html(__('Each pair is one feature packaged two ways, listed under the plugin that now carries it. The newer plugin is already running and the older one is switched on but idle, so nothing runs twice and nothing is lost. Deactivate the older one whenever it suits you.', 'js-support-ticket'))
            . ' <em>' . esc_html(__('Names below drop the shared "JS Help Desk" prefix.', 'js-support-ticket')) . '</em>'
            . '</p>';
        /* Nine headings each followed by its own bulleted list ran to about
           thirty-five lines - half a screen of notice above the screen the
           customer opened. The same twenty-five names fit in a third of that
           as one block per bundle flowed into columns: the browser decides how
           many fit, `break-inside` keeps a bundle and its add-ons together, and
           at phone width it is a single column and reads exactly as before. */
        echo '<div style="columns:3 17em;column-gap:2.4em;margin:.2em 0 .6em;">';
        foreach ($jsst_groups as $jsst_label => $jsst_addons) {
            sort($jsst_addons);
            echo '<div style="break-inside:avoid;-webkit-column-break-inside:avoid;page-break-inside:avoid;margin:0 0 .7em;">'
                . '<strong>' . esc_html($jsst_label) . '</strong> '
                . '<span style="color:#646970;">' . esc_html(__('replaces', 'js-support-ticket')) . '</span><br>'
                . '<span style="color:#50575e;">' . esc_html(implode(', ', $jsst_addons)) . '</span>'
                . '</div>';
        }
        echo '</div>';
        echo '<p style="margin:.2em 0 .8em;color:#50575e;">'
            . esc_html(__('Your settings are kept when you remove it. These add-ons blank their own settings on deactivation, so the help desk takes a copy first and puts them back afterwards.', 'js-support-ticket'))
            . '</p></div>';
        /* WordPress draws the dismiss button and hides the notice; this only
           remembers that it was dismissed. */
        echo '<script>jQuery(document).on("click", ".jsst-bundle-notice .notice-dismiss", function () {'
            . 'jQuery.post(ajaxurl, {action: "jsst_dismiss_bundle_notice", key: ' . wp_json_encode($jsst_key)
            . ', _wpnonce: ' . wp_json_encode(wp_create_nonce('jsst-dismiss-bundle-notice')) . '});'
            . '});</script>';
    }

    /** Which set of replaced add-ons a dismissal was for. */
    private static function noticeKey($jsst_superseded) {
        $jsst_modules = array_keys($jsst_superseded);
        sort($jsst_modules);
        return md5(implode(',', $jsst_modules));
    }

    /** Remember that this admin dismissed the replaced add-ons notice. */
    public static function dismissNotice() {
        check_ajax_referer('jsst-dismiss-bundle-notice');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(null, 403);
        }
        $jsst_key = isset($_POST['key']) ? sanitize_key(wp_unslash($_POST['key'])) : '';
        if ($jsst_key !== self::noticeKey(self::superseded())) {
            wp_send_json_error(null, 400);
        }
        update_user_meta(get_current_user_id(), self::META_DISMISSED, $jsst_key);
        wp_send_json_success();
    }

    /** Modules still served by their old add-on because their bundle is not here. */
    public static function legacyOnly() {
        return self::$jsst_legacy_only;
    }

    /** Modules augment() left out because their bundle is not running them. */
    public static function switchedOff() {
        return self::$jsst_switched_off;
    }

    /**
     * "These features are switched off until their bundle is here."
     *
     * The other side of augment() leaving a module out. A site that updates
     * the core with only the old add-ons installed loses those features on
     * the next page load, and without this nothing says why - the menu entry
     * is simply greyed out. So this names each bundle, what it would bring
     * back, and the one thing that brings it back, which depends on where the
     * site stands with it:
     *
     *  - active but an older build that does not carry the module: update it;
     *  - installed but switched off: activate it;
     *  - granted by the licence but not installed: install it;
     *  - the old key has not been carried to the new licence server yet: wait,
     *    and do not tell anyone they have lost something before the server
     *    has said so;
     *  - otherwise the licence does not include it.
     *
     * Installing and activating are the Install Add-ons screen's job and the
     * snooze is the licence notice's, so this links to one and reuses the
     * other rather than doing either itself. Not shown on those two screens,
     * which say all of this in the page.
     */
    public static function switchedOffNotice() {
        if (empty(self::$jsst_switched_off) || !current_user_can('activate_plugins')
            || !class_exists('JSSTlicense') || !JSSTlicense::noticeScreen()) {
            return;
        }
        if (isset($_GET['page'], $_GET['jstlay'])
            && 'jssupportticket' === sanitize_key(wp_unslash($_GET['page']))
            && 'license' === sanitize_key(wp_unslash($_GET['jstlay']))) {
            return;
        }
        $jsst_modules = array_keys(self::$jsst_switched_off);
        sort($jsst_modules);
        /* Keyed on what is switched off, so a dismissal covers this situation
           and not the next one - a second add-on losing its bundle later is
           news again. */
        $jsst_key = 'packs' . substr(md5(implode(',', $jsst_modules)), 0, 10);
        if (JSSTlicense::snoozed($jsst_key)) {
            return;
        }

        $jsst_by_bundle = array();
        foreach (self::$jsst_switched_off as $jsst_module => $jsst_slug) {
            $jsst_meta = class_exists('JSSTpro') ? JSSTpro::module($jsst_module) : false;
            $jsst_by_bundle[$jsst_slug][] = ($jsst_meta !== false && !empty($jsst_meta['label'])) ? $jsst_meta['label'] : $jsst_module;
        }
        ksort($jsst_by_bundle);

        $jsst_installed = JSSTlicense::installed();
        $jsst_pending = !JSSTlicense::hasKey() && '' === (string) get_option(JSSTlicense::OPT_ADOPTED, '');
        $jsst_lines = '';
        $jsst_to_install = false;
        $jsst_not_included = false;
        foreach ($jsst_by_bundle as $jsst_slug => $jsst_labels) {
            $jsst_bundle = self::bundle($jsst_slug);
            $jsst_name = ($jsst_bundle === false) ? $jsst_slug : $jsst_bundle['label'];
            if (self::active($jsst_slug)) {
                $jsst_state = __('installed, but this version does not include them yet. Update it.', 'js-support-ticket');
            } elseif (isset($jsst_installed[JSSTlicense::productSlug($jsst_slug)])) {
                $jsst_state = __('installed but switched off. Activate it.', 'js-support-ticket');
                $jsst_to_install = true;
            } elseif (JSSTlicense::grants($jsst_slug)) {
                $jsst_state = __('included in your license but not installed yet. Install it.', 'js-support-ticket');
                $jsst_to_install = true;
            } elseif ($jsst_pending) {
                $jsst_state = __('your existing license is still being checked.', 'js-support-ticket');
            } else {
                $jsst_state = __('not included in your license.', 'js-support-ticket');
                $jsst_not_included = true;
            }
            $jsst_lines .= '<li><strong>' . esc_html($jsst_name) . '</strong> ('
                . esc_html(implode(', ', $jsst_labels)) . ') - ' . esc_html($jsst_state) . '</li>';
        }

        $jsst_actions = '';
        $jsst_actions .= sprintf('<a class="button button-primary" href="%1$s">%2$s</a> ',
            esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')),
            $jsst_to_install ? esc_html__('Install add-ons', 'js-support-ticket')
                : (JSSTlicense::hasKey() ? esc_html__('License & Add-ons', 'js-support-ticket') : esc_html__('Add license key', 'js-support-ticket')));
        if ($jsst_not_included) {
            $jsst_actions .= sprintf('<a class="button" href="%1$s" target="_blank" rel="noopener">%2$s</a> ',
                esc_url(JSSTlicense::ACCOUNT),
                esc_html__('Your account', 'js-support-ticket'));
        }
        $jsst_actions .= sprintf('<a href="%1$s">%2$s</a>',
            esc_url(wp_nonce_url(add_query_arg('jsst_lic_snooze', $jsst_key), 'jsst-lic-snooze-' . $jsst_key)),
            esc_html__('Remind me next week', 'js-support-ticket'));

        echo '<div class="notice notice-warning"><p><strong>'
            . esc_html(__('Some help desk features are switched off.', 'js-support-ticket'))
            . '</strong> '
            . esc_html(__('They now come in add-on packs, and this site does not have the pack that carries them. Nothing has been deleted: their data and settings are kept and come back as soon as the pack is active.', 'js-support-ticket'))
            . '</p><ul style="list-style:disc;margin-left:1.5em;">' . $jsst_lines . '</ul>'
            . '<p>' . $jsst_actions . '</p></div>';
    }

    /** The row under the plugin itself. */
    public static function pluginRow($jsst_file, $jsst_data) {
        $jsst_columns = function_exists('get_current_screen') ? 4 : 4;
        echo '<tr class="plugin-update-tr active"><td colspan="' . (int) $jsst_columns
            . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>'
            /* Same correction as the notice above, and for the same reason: the
               newer plugin is the one serving this feature, not this one.
               (6.5-ECO-01) */
            . esc_html(__('Replaced by a newer JS Help Desk plugin, which is already serving this feature. This one is switched on but idle — deactivate it whenever it suits you. Your settings are kept.', 'js-support-ticket'))
            . '</p></div></td></tr>';
    }

    /**
     * Take a copy of a superseded add-on's settings before it is switched off.
     *
     * This is the single most important function in this file, and the reason
     * is in `JSSTlegacy`: twenty-three of the twenty-seven add-ons run an
     * UPDATE that sets every one of their settings rows to zero when they are
     * deactivated. We are telling customers to deactivate these. Telling them
     * that without this would be telling them to reset their overdue
     * thresholds, their piping mailbox and their assignment rules, with nothing
     * saying so and nothing obviously wrong until a ticket goes to the wrong
     * place.
     *
     * We do not stop the add-on's hook running - fighting it would mean editing
     * plugin files on the customer's site - we simply put the values back
     * afterwards, which is exactly what `JSSTlegacy::migrate()` does for a
     * migration and is the same rule applied to somebody tidying up by hand.
     */
    public static function holdSettings($jsst_plugin) {
        if (!class_exists('JSSTlegacy')) {
            return;
        }
        foreach (self::superseded() as $jsst_module => $jsst_ignored) {
            if (JSSTlegacy::pluginFile($jsst_module) !== $jsst_plugin) {
                continue;
            }
            $jsst_held = get_option(self::OPT_GUARD, array());
            $jsst_held = is_array($jsst_held) ? $jsst_held : array();
            $jsst_held[$jsst_module] = array(
                'tag'      => JSSTlegacy::footprint($jsst_module)['configtag'],
                'settings' => JSSTlegacy::settings($jsst_module),
            );
            update_option(self::OPT_GUARD, $jsst_held, false);
            return;
        }
    }

    /**
     * And put them back.
     *
     * Written by configname within the add-on's own tag, so a row the add-on
     * did not zero is written back to the value it already has - the operation
     * is idempotent, which matters because the commonest case after this ships
     * is an add-on that stood down at bootstrap, never registered its
     * deactivation hook, and zeroed nothing at all.
     *
     * The copy is deleted afterwards. Keeping it would mean a second
     * deactivation months later restoring settings from before the first one.
     */
    public static function restoreSettings($jsst_plugin) {
        $jsst_held = get_option(self::OPT_GUARD, array());
        if (!is_array($jsst_held) || empty($jsst_held)) {
            return;
        }
        foreach ($jsst_held as $jsst_module => $jsst_row) {
            if (!class_exists('JSSTlegacy') || JSSTlegacy::pluginFile($jsst_module) !== $jsst_plugin) {
                continue;
            }
            $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_config';
            foreach ((array) $jsst_row['settings'] as $jsst_name => $jsst_value) {
                jssupportticket::$_db->update($jsst_table,
                    array('configvalue' => $jsst_value),
                    array('configname' => $jsst_name, 'addon' => $jsst_row['tag']));
            }
            unset($jsst_held[$jsst_module]);
            update_option(self::OPT_GUARD, $jsst_held, false);
            return;
        }
    }

    /** Every bundle installed on this site. */
    public static function activeBundles() {
        $jsst_found = array();
        foreach (self::bundles() as $jsst_slug => $jsst_ignored) {
            if (self::active($jsst_slug)) {
                $jsst_found[] = $jsst_slug;
            }
        }
        return $jsst_found;
    }
}
}
