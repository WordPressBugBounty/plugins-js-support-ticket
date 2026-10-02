<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * What the AI Agent screens read. (Roadmap 6.0-AI-01)
 *
 * Nothing here asks a model anything. Every function on this class answers a
 * question about the site's own configuration — what is switched on, which lane
 * it runs in, whether it is set up, what it has been doing — because the screens
 * this serves exist to answer "what is this plugin doing with my customers'
 * tickets", and a screen that has to make a request to a third party in order to
 * tell you that is not answering the question.
 */
class JSSTaiagentModel {

    /* ------------------------------------------------------------------ *
     * The picture
     * ------------------------------------------------------------------ */

    /**
     * Everything the Overview screen shows, in one read.
     */
    function getOverview() {
        jssupportticket::$jsst_data['aipolicy']   = self::policyState();
        jssupportticket::$jsst_data['aisurfaces'] = self::surfaces();
        jssupportticket::$jsst_data['aiengines']  = self::engineState();
        jssupportticket::$jsst_data['aiusage']    = self::usage();
        jssupportticket::$jsst_data['aicorpus']   = self::corpus();
        return;
    }

    /**
     * What the AI has to answer from. (Roadmap 6.0-AI-02)
     *
     * The one number this screen was missing, and the one that decides whether
     * any of the rest of it does anything. Every lane can be on, an engine
     * chosen and a key stored, and the desk will still answer nothing if no
     * source holds a document it is allowed to read - and until this was here
     * it said so nowhere. An agent seeing an empty suggestions panel could not
     * tell "nothing similar has been asked" from "this feature is broken", and
     * neither could the person who set it up.
     *
     * Counted the way the Knowledge Sources screen counts it, from the same
     * `JSSTaisources::state()` rows, so the two screens cannot disagree.
     */
    static function corpus() {
        $jsst_out = array('documents' => 0, 'live' => 0, 'sources' => 0, 'available' => false);
        if (!class_exists('JSSTaisources')) {
            return $jsst_out;
        }
        $jsst_out['available'] = true;
        $jsst_rows = JSSTaisources::state();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out['sources']++;
            if (isset($jsst_row['health']) && $jsst_row['health'] === 'ok') {
                $jsst_out['live']++;
            }
            $jsst_out['documents'] += (int) (isset($jsst_row['eligible']) ? $jsst_row['eligible'] : 0);
        }
        return $jsst_out;
    }

    function getSettings() {
        jssupportticket::$jsst_data['aipolicy']  = self::policyState();
        jssupportticket::$jsst_data['aiengines'] = self::engineState();
        jssupportticket::$jsst_data['aitest']    = get_transient('jsst_ai_lasttest');
        return;
    }

    /**
     * Everything the Knowledge Sources screen shows. (Roadmap 6.0-AI-02)
     *
     * One source is opened at a time, chosen by &src=<type>, for the same
     * reason the Integrations screen opens one connector at a time: six document
     * browsers on one page is a page nobody reads, and five of them would be
     * queries nobody asked for.
     *
     * The console runs here rather than in the template because it is a read
     * that can take a second and templates in this plugin do not make those.
     */
    function getSources() {
        $jsst_state = class_exists('JSSTaisources') ? JSSTaisources::state() : array();

        /* Which source is open. Only a source whose add-on is present can be:
           an absent one has no documents to govern, and opening it would say
           "nothing published yet" when the truth is the add-on is missing. An
           unknown or absent key falls back to the first available source
           rather than erroring: it can only come from an edited URL, a source a
           filter removed, or an add-on switched off since the link was made. */
        $jsst_present = array_keys(array_filter($jsst_state, function ($jsst_row) {
            return !empty($jsst_row['present']);
        }));
        $jsst_open = sanitize_key(JSSTrequest::getVar('src', '', ''));
        if ($jsst_open === '' || !in_array($jsst_open, $jsst_present, true)) {
            $jsst_open = empty($jsst_present) ? '' : $jsst_present[0];
        }

        /* Every available source's first page is drawn up front, so the
           Documents tabs switch in place instead of reloading the page. Search
           and paging apply only to the source that is open -- they are the one
           case that still goes back to the server. */
        $jsst_search  = trim((string) JSSTrequest::getVar('find', '', ''));
        $jsst_alldocs = array();
        if (class_exists('JSSTaisources')) {
            foreach ($jsst_present as $jsst_key) {
                $jsst_isopen = ($jsst_key === $jsst_open);
                $jsst_alldocs[$jsst_key] = JSSTaisources::documents($jsst_key, array(
                    'page'   => $jsst_isopen ? (int) JSSTrequest::getVar('dp', '', 1) : 1,
                    'search' => $jsst_isopen ? $jsst_search : '',
                ));
            }
        }

        $jsst_question = trim((string) JSSTrequest::getVar('ask', '', ''));
        $jsst_profile  = sanitize_key(JSSTrequest::getVar('profile', '', 'reply'));

        jssupportticket::$jsst_data['aipolicy']      = self::policyState();
        jssupportticket::$jsst_data['aisources']     = $jsst_state;
        jssupportticket::$jsst_data['aisourcemodes'] = class_exists('JSSTaisources') ? JSSTaisources::modes() : array();
        jssupportticket::$jsst_data['aisourceopen']  = $jsst_open;
        jssupportticket::$jsst_data['aisourcedocs']  = $jsst_alldocs;
        jssupportticket::$jsst_data['aisourcefind']  = $jsst_search;
        jssupportticket::$jsst_data['aiconsole']     = class_exists('JSSTaisources')
            ? JSSTaisources::console($jsst_question, $jsst_profile)
            : array('ran' => false, 'question' => '', 'profile' => 'reply', 'snippets' => array(),
                    'diagnostics' => array(), 'engine' => '', 'note' => '');

        /* What language everything is in belongs on this screen and not on a
           screen of its own. (Roadmap 6.0-AI-09) It is a rule about which of
           the site's own documents may answer a question, which is exactly what
           the rest of this page is - and a language policy read anywhere other
           than beside the corpus it governs is a policy nobody connects to the
           thing it changes. The survey is taken fresh here, because this is the
           one place somebody comes to check it. */
        $jsst_have_lang = class_exists('JSSTailanguages');
        jssupportticket::$jsst_data['ailangmode']    = $jsst_have_lang ? JSSTailanguages::mode() : '';
        jssupportticket::$jsst_data['ailangmodes']   = $jsst_have_lang ? JSSTailanguages::modes() : array();
        jssupportticket::$jsst_data['ailangsurvey']  = $jsst_have_lang ? JSSTailanguages::survey(true) : array('languages' => array(), 'total' => 0, 'known' => false);
        jssupportticket::$jsst_data['ailangsite']    = $jsst_have_lang ? JSSTailanguages::siteLanguage() : '';
        jssupportticket::$jsst_data['ailangknown']   = $jsst_have_lang ? JSSTailanguages::known() : array();
        jssupportticket::$jsst_data['ailangunsearchable'] = $jsst_have_lang ? JSSTailanguages::unsearchable() : array();
        return;
    }

    /**
     * The approval queue. (Roadmap 6.0-AI-03)
     *
     * Waiting first and decided below it, because this screen is a queue
     * somebody works through rather than a report they read. The decided half
     * is there so a withdrawal is one click from the thing that was sent - the
     * moment an answer turns out to be wrong is the moment nobody wants to go
     * looking for a second screen.
     */
    function getApprovals() {
        $jsst_have = class_exists('JSSTaireview');

        jssupportticket::$jsst_data['aipolicy']   = self::policyState();
        jssupportticket::$jsst_data['aipending']  = $jsst_have ? JSSTaireview::pending(50) : array();
        jssupportticket::$jsst_data['aidecided']  = $jsst_have ? JSSTaireview::decided(30) : array();
        jssupportticket::$jsst_data['aicounts']   = $jsst_have ? JSSTaireview::counts() : array();
        jssupportticket::$jsst_data['aistates']   = $jsst_have ? JSSTaireview::states() : array();
        jssupportticket::$jsst_data['aimodes']    = $jsst_have ? JSSTaireview::modes() : array();
        jssupportticket::$jsst_data['aimode']     = $jsst_have ? JSSTaireview::mode() : '';
        return;
    }

    /**
     * Shadow mode. (Roadmap 6.0-AI-04)
     *
     * The pairing sweep runs here, on a screen read, rather than on a hook in
     * the reply path. It is reporting: a minute late costs nothing, and it
     * keeps a feature nobody is blocked on out of the query every ticket in the
     * product runs.
     */
    function getShadow() {
        $jsst_have = class_exists('JSSTaireview');
        if ($jsst_have) {
            JSSTaireview::pairShadows(50);
        }

        /* Counted against the threshold set right now, not the one that applied
           when each answer was written: the question this screen answers is
           "what would happen if I switched autopilot on today". */
        $jsst_threshold = class_exists('JSSTairollout') ? JSSTairollout::threshold() : 85;

        jssupportticket::$jsst_data['aipolicy']    = self::policyState();
        jssupportticket::$jsst_data['aishadows']   = $jsst_have ? JSSTaireview::shadows(30) : array();
        jssupportticket::$jsst_data['aishadowsum'] = $jsst_have ? JSSTaireview::shadowStats($jsst_threshold) : array();
        jssupportticket::$jsst_data['aimodes']     = $jsst_have ? JSSTaireview::modes() : array();
        jssupportticket::$jsst_data['aimode']      = $jsst_have ? JSSTaireview::mode() : '';
        jssupportticket::$jsst_data['aistatuses']  = self::statusLabels();
        return;
    }

    /**
     * Ticket status id => label, for the outcome column.
     *
     * Read from the site's own status table rather than hard-coded, because a
     * desk that renamed "Closed" to "Resolved" should see its own word.
     */
    static function statusLabels() {
        $jsst_out = array();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_statuses';

        if (jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return $jsst_out;
        }
        foreach ((array) jssupportticket::$_db->get_results("SELECT id, status FROM `" . $jsst_table . "`") as $jsst_row) {
            $jsst_out[(string) (int) $jsst_row->id] = (string) $jsst_row->status;
        }
        return $jsst_out;
    }

    /**
     * The rollout controls. (Roadmap 6.0-AI-06)
     *
     * The coverage sample runs on the screen read for the same reason the
     * shadow pairing does: it is reporting, and it belongs nowhere near the
     * path a customer's ticket takes.
     */
    function getAutopilot() {
        $jsst_have = class_exists('JSSTairollout');

        jssupportticket::$jsst_data['aipolicy']    = self::policyState();
        jssupportticket::$jsst_data['airules']     = $jsst_have ? JSSTairollout::rules() : array();
        jssupportticket::$jsst_data['aistate']     = $jsst_have ? JSSTairollout::explain() : array();
        jssupportticket::$jsst_data['aipaused']    = $jsst_have ? JSSTairollout::paused() : false;
        jssupportticket::$jsst_data['aiaudiences'] = $jsst_have ? JSSTairollout::audiences() : array();
        jssupportticket::$jsst_data['aidelays']    = $jsst_have ? JSSTairollout::delays() : array();
        jssupportticket::$jsst_data['aifallbacks'] = $jsst_have ? JSSTairollout::fallbacks() : array();
        jssupportticket::$jsst_data['aicoverage']  = $jsst_have ? JSSTairollout::coverage() : array();
        jssupportticket::$jsst_data['aireversal']  = $jsst_have ? JSSTairollout::reversal() : array();
        jssupportticket::$jsst_data['aidepts']     = self::departments();
        jssupportticket::$jsst_data['aimode']      = class_exists('JSSTaireview') ? JSSTaireview::mode() : '';
        jssupportticket::$jsst_data['aiwaiting']   = class_exists('JSSTaireview')
            ? (int) JSSTaireview::counts()[JSSTaireview::STATE_HELD] : 0;
        return;
    }

    /**
     * The site's departments, id => name.
     *
     * Read straight from the table rather than through the departments model,
     * because this screen wants a flat list of every department including the
     * ones a particular agent cannot see - a rollout is a site-wide rule.
     */
    static function departments() {
        $jsst_out = array();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_departments';

        if (jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return $jsst_out;
        }
        foreach ((array) jssupportticket::$_db->get_results(
                     "SELECT id, departmentname FROM `" . $jsst_table . "` ORDER BY departmentname ASC") as $jsst_row) {
            $jsst_out[(int) $jsst_row->id] = (string) $jsst_row->departmentname;
        }
        return $jsst_out;
    }

    /**
     * What the AI has cost, and the caps. (Roadmap 6.0-AI-07)
     *
     * Two windows are read rather than one, because "today" and "this month"
     * are answers to different questions - one is "is something running away
     * right now", the other is "what will the invoice say" - and a screen that
     * makes somebody pick between them makes them navigate to find out.
     */
    function getUsage() {
        $jsst_have = class_exists('JSSTaiusage');

        jssupportticket::$jsst_data['aipolicy']   = self::policyState();
        jssupportticket::$jsst_data['aibudget']   = $jsst_have ? JSSTaiusage::budget() : array();
        jssupportticket::$jsst_data['aiday']      = $jsst_have ? JSSTaiusage::spend('day') : array();
        jssupportticket::$jsst_data['aimonth']    = $jsst_have ? JSSTaiusage::spend('month') : array();
        jssupportticket::$jsst_data['aialerts']   = $jsst_have ? JSSTaiusage::alerts() : array();
        jssupportticket::$jsst_data['aibyfeature']= $jsst_have ? JSSTaiusage::breakdown('feature', 'month') : array();
        jssupportticket::$jsst_data['aibymodel']  = $jsst_have ? JSSTaiusage::breakdown('model', 'month') : array();
        jssupportticket::$jsst_data['aibyfunded'] = $jsst_have ? JSSTaiusage::breakdown('funded', 'month') : array();
        jssupportticket::$jsst_data['aitoptickets'] = $jsst_have ? JSSTaiusage::topTickets('month') : array();
        jssupportticket::$jsst_data['aiallowance'] = $jsst_have ? JSSTaiusage::allowance() : 0;
        jssupportticket::$jsst_data['aiallowused'] = $jsst_have ? JSSTaiusage::allowanceUsed() : 0;
        jssupportticket::$jsst_data['aiengines']   = self::engineState();
        jssupportticket::$jsst_data['aifallback']  = class_exists('JSSTaiengine') ? JSSTaiengine::fallbackId() : '';
        jssupportticket::$jsst_data['aienginelist'] = class_exists('JSSTaiengine') ? JSSTaiengine::engines() : array();
        jssupportticket::$jsst_data['aiunpriced']  = $jsst_have ? self::unpricedModels() : array();
        return;
    }

    /**
     * Models that have been used this month and have no price.
     *
     * Read from what was actually called rather than from the engine list: the
     * models a site is billed for are the ones it used, and a warning about a
     * model nobody has asked for is noise on a screen that has to be believed.
     */
    static function unpricedModels() {
        $jsst_out = array();
        foreach (JSSTaiusage::breakdown('model', 'month', 50) as $jsst_row) {
            if ($jsst_row['unpriced'] > 0 && $jsst_row['key'] !== '') {
                $jsst_out[] = $jsst_row;
            }
        }
        return $jsst_out;
    }

    /**
     * The setup checklist. (Roadmap 6.0-AI-11)
     *
     * The preview runs here, from the query string, the way the retrieval
     * console on Knowledge Sources does - it is a read with a question in it,
     * and making it a POST would mean a redirect that loses the answer.
     */
    function getSetup() {
        $jsst_have = class_exists('JSSTaisetup');

        $jsst_ask = trim((string) JSSTrequest::getVar('ask', '', ''));
        jssupportticket::$jsst_data['aipreview'] = ($jsst_have && $jsst_ask !== '')
            ? JSSTaisetup::preview($jsst_ask) : false;

        jssupportticket::$jsst_data['aipolicy']   = self::policyState();
        jssupportticket::$jsst_data['aisteps']    = $jsst_have ? JSSTaisetup::state() : array();
        jssupportticket::$jsst_data['ainext']     = $jsst_have ? JSSTaisetup::next() : '';
        jssupportticket::$jsst_data['aiprogress'] = $jsst_have ? JSSTaisetup::progress() : array();
        jssupportticket::$jsst_data['aiasked']    = $jsst_ask;
        return;
    }

    /**
     * The knowledge gaps, grouped, with the expensive question asked last.
     * (Roadmap 6.0-AI-12)
     *
     * Two things this does that the class deliberately does not. It decides how
     * many clusters a screen shows, because that is a screen's business and not
     * a register's. And it asks `covered()` only about the ones being drawn:
     * that call runs the real retriever, so asking it about every cluster in a
     * busy quarter would be four hundred retrievals to render one page. The
     * ones below the fold are shown without a coverage verdict rather than with
     * a guessed one - a screen that says "we may already answer this" on no
     * evidence is worse than one that says nothing.
     */
    function getGaps() {
        $jsst_have = class_exists('JSSTaigaps');

        $jsst_days = (int) JSSTrequest::getVar('days', '', 90);
        if (!in_array($jsst_days, array(7, 30, 90, 180), true)) $jsst_days = 90;

        $jsst_kind = sanitize_key(JSSTrequest::getVar('kind', '', ''));
        $jsst_show = (JSSTrequest::getVar('show', '', '') === 'hidden');

        $jsst_clusters = $jsst_have
            ? JSSTaigaps::clusters(array('days' => $jsst_days, 'kind' => $jsst_kind))
            : array();

        /* Dismissed clusters are filtered here rather than in the register,
           because "show me the ones I hid" is a thing somebody asks and a
           register that had already dropped them could not answer it. */
        $jsst_list = array();
        foreach ($jsst_clusters as $jsst_cluster) {
            if ($jsst_show !== !empty($jsst_cluster['dismissed'])) continue;
            $jsst_list[] = $jsst_cluster;
        }

        $jsst_hidden = 0;
        foreach ($jsst_clusters as $jsst_cluster) {
            if (!empty($jsst_cluster['dismissed'])) $jsst_hidden++;
        }

        /* Twenty is about as far as anybody reads on a page asking them to go
           and write something, and the list is sorted by demand, so the ones
           past it are by definition the ones fewest people asked. */
        $jsst_list = array_slice($jsst_list, 0, 20);

        foreach ($jsst_list as $jsst_i => $jsst_cluster) {
            $jsst_list[$jsst_i]['covered'] = ($jsst_i < 8) ? JSSTaigaps::covered($jsst_cluster) : null;
            $jsst_list[$jsst_i]['brief']   = JSSTaigaps::brief($jsst_cluster);
            $jsst_list[$jsst_i]['write']   = JSSTaigaps::authorUrl($jsst_cluster);
            $jsst_list[$jsst_i]['writefaq'] = JSSTaigaps::authorUrl($jsst_cluster, 'faq');
        }

        jssupportticket::$jsst_data['aipolicy']   = self::policyState();
        jssupportticket::$jsst_data['aigaps']     = $jsst_list;
        jssupportticket::$jsst_data['aigapstats'] = $jsst_have ? JSSTaigaps::stats($jsst_days) : array();
        jssupportticket::$jsst_data['aigapkinds'] = $jsst_have ? JSSTaigaps::kinds() : array();
        jssupportticket::$jsst_data['aigapdays']  = $jsst_days;
        jssupportticket::$jsst_data['aigapkind']  = $jsst_kind;
        jssupportticket::$jsst_data['aigaphidden'] = $jsst_hidden;
        jssupportticket::$jsst_data['aigapshowing'] = $jsst_show;
        jssupportticket::$jsst_data['aigapready'] = ($jsst_have && JSSTaigaps::available());
        return;
    }

    function getAudit() {
        jssupportticket::$jsst_data['aipolicy']  = self::policyState();
        jssupportticket::$jsst_data['aijournal'] = class_exists('JSSTaipolicy') ? JSSTaipolicy::journal() : array();
        jssupportticket::$jsst_data['aiengines'] = self::engineState();
        jssupportticket::$jsst_data['airuns']    = self::runs(40);
        return;
    }

    /** The switchboard as a screen reads it. */
    static function policyState() {
        if (!class_exists('JSSTaipolicy')) {
            return array('enabled' => false, 'lanes' => array(), 'states' => array(), 'redaction' => '', 'notice' => '');
        }
        return array(
            'enabled'   => JSSTaipolicy::enabled(),
            'lanes'     => JSSTaipolicy::lanes(),
            'states'    => JSSTaipolicy::laneStates(),
            'redaction' => JSSTaipolicy::redactionMode(),
            'modes'     => JSSTaipolicy::redactionModes(),
            'notice'    => JSSTaipolicy::notice(),
        );
    }

    /** Every engine, whether it is set up, and whether its lane is open. */
    static function engineState() {
        if (!class_exists('JSSTaiengine')) {
            return array('current' => '', 'engines' => array());
        }
        $jsst_rows = array();
        foreach (JSSTaiengine::engines() as $jsst_id => $jsst_def) {
            $jsst_rows[$jsst_id] = array(
                'id'          => $jsst_id,
                'label'       => $jsst_def['label'],
                'lane'        => isset($jsst_def['lane']) ? $jsst_def['lane'] : 'hosted',
                'blurb'       => isset($jsst_def['blurb']) ? $jsst_def['blurb'] : '',
                'keyhint'     => isset($jsst_def['keyhint']) ? $jsst_def['keyhint'] : '',
                'nokey'       => !empty($jsst_def['nokey']),
                'recommended' => !empty($jsst_def['recommended']),
                'models'      => isset($jsst_def['models']) ? $jsst_def['models'] : array(),
                'configured'  => JSSTaiengine::configured($jsst_id),
                'laneopen'    => class_exists('JSSTaipolicy') ? JSSTaipolicy::allows(isset($jsst_def['lane']) ? $jsst_def['lane'] : 'hosted') : true,
                'stored'      => JSSTaiengine::keyHint($jsst_id),
            );
        }
        return array(
            'current'  => JSSTaiengine::currentId(),
            'model'    => JSSTaiengine::model(),
            'engines'  => $jsst_rows,
            'endpoint' => (string) get_option(JSSTaiengine::OPT_LOCAL_ENDPOINT, ''),
            'localmodel' => (string) get_option(JSSTaiengine::OPT_LOCAL_MODEL, ''),
            'localtimeout' => JSSTaiengine::localTimeout(),
        );
    }

    /* ------------------------------------------------------------------ *
     * What AI actually does on this site
     * ------------------------------------------------------------------ */

    /**
     * Every place in the product where something AI-shaped happens, its lane,
     * and whether it is on.
     *
     * This list is the honest answer to the question the four separate AI menus
     * could never answer between them. Each row says what happens, whether
     * anything leaves the server when it does, whether it is switched on, and
     * where the switch is — so a site owner can read one table instead of
     * opening four screens and assembling the answer themselves.
     *
     * Rows are declared even when the add-on that provides them is not
     * installed. Saying "this exists and you do not have it" is more use than a
     * shorter list that leaves somebody wondering whether they have found
     * everything.
     */
    static function surfaces() {
        $jsst_ir = in_array('aiagent', jssupportticket::$_active_addons);
        $jsst_config = jssupportticket::$_config;
        $jsst_settings = admin_url('admin.php?page=aiagent&jstlay=aiagent_settings');

        $jsst_surfaces = array(
            array(
                'key'      => 'deflect',
                'label'    => esc_html(__('Suggested articles on the ticket form', 'js-support-ticket')),
                // Canned responses stay agent-only: customers see articles and FAQs.
                'blurb'    => esc_html(__('Matching articles and FAQs shown on the ticket form while the customer types. Core searches your own content; the AI Agent add-on ranks it better.', 'js-support-ticket')),
                'lane'     => 'onsite',
                /* Core's own search answers when no add-on claims it, so this
                   works on every site. The switch mirrors getInstantResolveSearch():
                   an absent row means on. */
                'present'  => true,
                'on'       => (!isset($jsst_config['aiagent_enable']) || $jsst_config['aiagent_enable'] == 1),
                'where'    => $jsst_settings . '#AIAgentSuggestions',
                'wherename'=> esc_html(__('Settings', 'js-support-ticket')),
                'needs'    => '',
                'needskey' => false,
            ),
            array(
                'key'      => 'pastreplies',
                'label'    => esc_html(__('Similar past replies for agents', 'js-support-ticket')),
                'blurb'    => esc_html(__('Replies to earlier tickets on the same subject, offered beside the reply box. Was the AI Powered Reply add-on.', 'js-support-ticket')),
                'lane'     => 'onsite',
                'present'  => true,
                'on'       => (isset($jsst_config['ticket_aipoweredreply']) && (int) $jsst_config['ticket_aipoweredreply'] > 0),
                'where'    => admin_url('admin.php?page=configuration&jsstconfigid=ticket'),
                'wherename'=> esc_html(__('Configurations', 'js-support-ticket')),
                'needs'    => '',
                'needskey' => false,
            ),
            array(
                'key'      => 'triage',
                'label'    => esc_html(__('Reading new tickets (department and mood)', 'js-support-ticket')),
                'blurb'    => esc_html(__('Suggests a department and flags upset customers when a ticket arrives. It only suggests; nothing is moved or sent.', 'js-support-ticket')),
                'lane'     => 'hosted',
                'present'  => class_exists('JSSTaitriage'),
                'on'       => (class_exists('JSSTaitriage') && JSSTaitriage::anyAutomatic()),
                'where'    => $jsst_settings . '#AISwitches',
                'wherename'=> esc_html(__('Settings', 'js-support-ticket')),
                'needs'    => '',
                'needskey' => true,
            ),
            array(
                'key'      => 'copilot',
                'label'    => esc_html(__('Copilot buttons inside a ticket', 'js-support-ticket')),
                'blurb'    => esc_html(__('Summarise, translate, pull out the details, draft a reply: buttons on the admin ticket page. Nothing is ever sent to the customer.', 'js-support-ticket')),
                'lane'     => 'hosted',
                'present'  => class_exists('JSSTcopilot'),
                'on'       => class_exists('JSSTcopilot'),
                'where'    => $jsst_settings . '#AIEngine',
                'wherename'=> esc_html(__('Settings', 'js-support-ticket')),
                'needs'    => '',
                'needskey' => true,
            ),
            array(
                'key'      => 'summaries',
                'label'    => esc_html(__('Written answer on the ticket form', 'js-support-ticket')),
                'blurb'    => esc_html(__('A short answer above the suggested articles, written from them, with its sources. Separate from automatic replies below.', 'js-support-ticket')),
                'lane'     => 'hosted',
                'present'  => $jsst_ir,
                'on'       => ($jsst_ir && isset($jsst_config['aiagent_ai_enable']) && $jsst_config['aiagent_ai_enable'] == 1),
                'where'    => $jsst_ir ? $jsst_settings . '#AIAgentAI' : '',
                'wherename'=> esc_html(__('Settings', 'js-support-ticket')),
                'needs'    => esc_html(__('AI Agent add-on', 'js-support-ticket')),
                'needskey' => true,
            ),
            array(
                'key'      => 'autopilot',
                'label'    => esc_html(__('Automatic replies to tickets', 'js-support-ticket')),
                'blurb'    => esc_html(__('An answer sent without an agent reading it first, when confidence and grounding both clear the configured floor.', 'js-support-ticket')),
                'lane'     => 'hosted',
                'present'  => $jsst_ir,
                /* The pause counts as off here, deliberately: this table is
                   read to answer "is this happening to my customers right now",
                   and a paused desk is one where it is not. (Roadmap 6.0-AI-06) */
                'on'       => ($jsst_ir && class_exists('JSSTairollout') && JSSTairollout::live()),
                'where'    => admin_url('admin.php?page=aiagent&jstlay=aiagent_autopilot'),
                'wherename'=> esc_html(__('Rules', 'js-support-ticket')),
                'needs'    => esc_html(__('AI Agent add-on', 'js-support-ticket')),
                'needskey' => true,
            ),
            array(
                'key'      => 'livechat',
                'label'    => esc_html(__('AI answers in live chat', 'js-support-ticket')),
                'blurb'    => esc_html(__('The AI answers chat visitors first and hands over to a person when it cannot. Uses the same sources and approval rules as automatic replies.', 'js-support-ticket')),
                'lane'     => 'hosted',
                'present'  => ($jsst_ir && in_array('livechat', jssupportticket::$_active_addons)),
                /* usable() is the chat's own full answer: first responder set to
                   AI, chat channel allowed, approvals not propose-only. */
                'on'       => (class_exists('JSSTlivechatai') && JSSTlivechatai::usable()),
                'where'    => in_array('livechat', jssupportticket::$_active_addons) ? admin_url('admin.php?page=livechat&jstlay=livechat_settings') : '',
                'wherename'=> esc_html(__('Live Chat settings', 'js-support-ticket')),
                'needs'    => esc_html(__('AI Agent and Customer Experience add-ons', 'js-support-ticket')),
                'needskey' => true,
            ),
            array(
                'key'      => 'translate',
                'label'    => esc_html(__('First-draft e-mail template translations', 'js-support-ticket')),
                'blurb'    => esc_html(__('A starting translation of a transactional template, for a person to correct.', 'js-support-ticket')),
                'lane'     => 'hosted',
                /* `JSSTtemplatelocales`, which is what the class is called.
                   This asked for `JSSTlocales` - a name nothing in the product
                   has ever declared - so both cells answered false on every
                   site, including one with the add-on installed and translating
                   happily. A row that cannot report anything but "not here" is
                   worse than no row: this table exists to tell somebody which
                   AI surfaces are live, and it was answering for this one
                   without ever asking. (Roadmap 6.0-AI-07) */
                'present'  => class_exists('JSSTtemplatelocales'),
                'on'       => (class_exists('JSSTtemplatelocales') && method_exists('JSSTtemplatelocales', 'canTranslate') && JSSTtemplatelocales::canTranslate()),
                'where'    => admin_url('admin.php?page=emailtemplate'),
                'wherename'=> esc_html(__('E-mail Templates', 'js-support-ticket')),
                'needs'    => esc_html(__('Multi-language E-mail Templates add-on', 'js-support-ticket')),
                'needskey' => true,
            ),
        );

        /* Each row's real state is the switch AND its lane: a surface whose lane
           is shut is off however its own setting reads, and saying so here is
           what stops the table from disagreeing with what actually happens. */
        /* A row that needs an engine is not live without one, whatever its own
           switch says - that is what let the Copilot read "Live" on a site with
           no key. The group is what the Overview sorts by: usable now, needs a
           key, needs an add-on. */
        $jsst_engine = (class_exists('JSSTaiengine') && JSSTaiengine::usable());
        foreach ($jsst_surfaces as $jsst_index => $jsst_surface) {
            $jsst_laneopen = class_exists('JSSTaipolicy') ? JSSTaipolicy::allows($jsst_surface['lane']) : true;
            $jsst_haskey = (empty($jsst_surface['needskey']) || $jsst_engine);
            $jsst_surfaces[$jsst_index]['laneopen'] = $jsst_laneopen;
            $jsst_surfaces[$jsst_index]['haskey'] = $jsst_haskey;
            $jsst_surfaces[$jsst_index]['live'] = ($jsst_surface['present'] && $jsst_surface['on'] && $jsst_laneopen && $jsst_haskey);
            $jsst_surfaces[$jsst_index]['group'] = !$jsst_surface['present'] ? 'addon' : ($jsst_haskey ? 'now' : 'key');
        }

        return apply_filters('jsst_ai_surfaces', $jsst_surfaces);
    }

    /* ------------------------------------------------------------------ *
     * What it has been doing
     * ------------------------------------------------------------------ */

    /**
     * The numbers, from wherever each engine keeps them.
     *
     * Deliberately not one merged total. The hosted proxy meters credits, the
     * bring-your-own-key path counts tokens against somebody else's bill, and a
     * local model costs nothing per request at all — adding those together
     * produces a number that is true of nothing. Each is reported beside the
     * engine it belongs to. (Roadmap 6.0-AI-07 builds budgets on top of this.)
     */
    static function usage() {
        $jsst_usage = array(
            'zywrap'  => array('runs' => 0, 'tokens' => 0, 'errors' => 0, 'credits' => 0),
            'copilot' => array('runs' => 0, 'failed' => 0, 'intokens' => 0, 'outtokens' => 0),
        );

        $jsst_prefix = jssupportticket::$_db->prefix . 'js_ticket_';
        $jsst_table = $jsst_prefix . 'zywrap_usage_logs';
        $jsst_exists = jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare("SHOW TABLES LIKE %s", $jsst_table)
        );
        if ($jsst_exists === $jsst_table) {
            $jsst_row = jssupportticket::$_db->get_row(
                "SELECT COUNT(*) AS runs, COALESCE(SUM(total_tokens),0) AS tokens,
                        COALESCE(SUM(credits_used),0) AS credits,
                        SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) AS errors
                 FROM `" . $jsst_table . "`"
            );
            if ($jsst_row) {
                $jsst_usage['zywrap'] = array(
                    'runs'    => (int) $jsst_row->runs,
                    'tokens'  => (int) $jsst_row->tokens,
                    'credits' => (int) $jsst_row->credits,
                    'errors'  => (int) $jsst_row->errors,
                );
            }
        }

        if (class_exists('JSSTcopilot')) {
            $jsst_usage['copilot'] = JSSTcopilot::usage();
        }

        return $jsst_usage;
    }

    /**
     * Recent runs from every engine in one list, newest first.
     *
     * The two logs have nothing in common but a timestamp — one is rows in a
     * table written by the proxy, the other is an option written by the Copilot
     * — so they are normalised here into the four fields a person reads: when,
     * what was asked, which engine, and whether it worked. Ticket text is in
     * neither of them and is not added.
     */
    static function runs($jsst_limit = 40) {
        $jsst_limit = max(1, min(200, (int) $jsst_limit));
        $jsst_rows = array();

        /* The meter is the record now, and it is the first one that covers
           every engine: before 6.0-AI-07 this stitched together Zywrap's own
           table, the Copilot's option log and nothing at all for the local
           lane, and the three could not be added up. (Roadmap 6.0-AI-07) */
        $jsst_since = 0;
        if (class_exists('JSSTaiusage') && JSSTaiusage::available()) {
            foreach (JSSTaiusage::recent($jsst_limit) as $jsst_run) {
                $jsst_when = strtotime($jsst_run->created);
                if ($jsst_since === 0 || $jsst_when < $jsst_since) {
                    $jsst_since = $jsst_when;
                }
                $jsst_rows[] = array(
                    'when'   => $jsst_when,
                    'engine' => (string) $jsst_run->engine,
                    'what'   => JSSTaiusage::featureLabel((string) $jsst_run->feature),
                    'model'  => (string) $jsst_run->model,
                    'tokens' => ((int) $jsst_run->intokens) + ((int) $jsst_run->outtokens),
                    'ms'     => (int) $jsst_run->ms,
                    'ok'     => !empty($jsst_run->ok),
                    'error'  => (string) $jsst_run->error,
                    'who'    => (int) $jsst_run->userid,
                    'ticket' => (int) $jsst_run->ticketid,
                    'cost'   => (float) $jsst_run->cost,
                );
            }
        }

        /* The two older logs are still read, but only for what happened before
           the meter existed. Reading them for anything after that would list
           every Zywrap call twice - once from its own table and once from ours
           - and a doubled audit log is worse than a short one. */
        $jsst_legacy = array();

        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_zywrap_usage_logs';
        $jsst_exists = jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare("SHOW TABLES LIKE %s", $jsst_table)
        );
        if ($jsst_exists === $jsst_table) {
            $jsst_logs = jssupportticket::$_db->get_results(
                jssupportticket::$_db->prepare(
                    "SELECT trace_id, wrapper_code, model_code, total_tokens, latency_ms, status, error_message, created_at
                     FROM `" . $jsst_table . "` ORDER BY id DESC LIMIT %d",
                    $jsst_limit
                )
            );
            foreach ((array) $jsst_logs as $jsst_log) {
                $jsst_legacy[] = array(
                    'when'   => strtotime($jsst_log->created_at . ' UTC'),
                    'engine' => 'Zywrap',
                    'what'   => (string) $jsst_log->wrapper_code,
                    'model'  => (string) $jsst_log->model_code,
                    'tokens' => (int) $jsst_log->total_tokens,
                    'ms'     => (int) $jsst_log->latency_ms,
                    'ok'     => ($jsst_log->status !== 'error'),
                    'error'  => (string) $jsst_log->error_message,
                    'who'    => 0,
                );
            }
        }

        if (class_exists('JSSTcopilot')) {
            $jsst_actions = JSSTcopilot::actions();
            foreach (JSSTcopilot::recentRuns() as $jsst_run) {
                $jsst_key = isset($jsst_run['action']) ? $jsst_run['action'] : '';
                $jsst_legacy[] = array(
                    'when'   => isset($jsst_run['when']) ? (int) $jsst_run['when'] : 0,
                    'engine' => esc_html(__('Copilot', 'js-support-ticket')),
                    'what'   => isset($jsst_actions[$jsst_key]['label']) ? $jsst_actions[$jsst_key]['label'] : $jsst_key,
                    'model'  => isset($jsst_run['model']) ? (string) $jsst_run['model'] : '',
                    'tokens' => (isset($jsst_run['intokens']) ? (int) $jsst_run['intokens'] : 0)
                              + (isset($jsst_run['outtokens']) ? (int) $jsst_run['outtokens'] : 0),
                    'ms'     => 0,
                    'ok'     => !empty($jsst_run['ok']),
                    'error'  => isset($jsst_run['error']) ? (string) $jsst_run['error'] : '',
                    'who'    => isset($jsst_run['user']) ? (int) $jsst_run['user'] : 0,
                    'ticket' => isset($jsst_run['ticket']) ? (int) $jsst_run['ticket'] : 0,
                );
            }
        }

        foreach ($jsst_legacy as $jsst_row) {
            if ($jsst_since === 0 || $jsst_row['when'] < $jsst_since) {
                $jsst_rows[] = $jsst_row;
            }
        }

        usort($jsst_rows, function ($jsst_a, $jsst_b) {
            if ($jsst_a['when'] === $jsst_b['when']) return 0;
            return ($jsst_a['when'] < $jsst_b['when']) ? 1 : -1;
        });

        return array_slice($jsst_rows, 0, $jsst_limit);
    }

}
