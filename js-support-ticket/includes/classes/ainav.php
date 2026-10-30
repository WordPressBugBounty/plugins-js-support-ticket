<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

if (class_exists('JSSTainav')) {
    return;
}

/**
 * The AI Agent's screens, grouped by what an administrator is trying to do.
 *
 * Thirteen menu entries and five older Zywrap screens covered five jobs, and
 * finding the right one meant knowing how the product was built. The screens
 * themselves are unchanged and keep their addresses; this is only the map:
 * five menu entries, and a tab bar on every screen naming its siblings.
 *
 *   Overview           what is live, and the setup checklist
 *   Knowledge          what the AI may read, pages it crawls, what nobody answered
 *   Automatic answers  who gets answered, what waits for a person, how it did
 *   Settings           engine, behaviour, the prompt catalogue, Prompt Lab
 *   Activity           what it cost, what it was asked, what failed
 *
 * The side menu and every tab bar are drawn from groups() below, so a screen
 * added to a group appears in both at once.
 */
class JSSTainav {

    /**
     * Every group, in menu order, and the screens inside it in tab order.
     *
     * A tab's 'when' names the condition it needs: 'addon' (the AI Agent
     * add-on, which owns retrieval and sending), 'engine' (an engine with a
     * key, without which there is no usage to report) or 'zywrapkey' (a
     * stored Zywrap key, without which the Zywrap screens can only report an
     * error). A tab that cannot work is left out of the menu and the tab bar;
     * its screen still opens from a direct link, and nothing is changed.
     */
    public static function groups() {
        return array(
            'overview' => array(
                'label' => __('Overview', 'js-support-ticket'),
                'tabs'  => array(
                    array('page' => 'aiagent', 'layout' => 'aiagent',       'label' => __('Overview', 'js-support-ticket')),
                    array('page' => 'aiagent', 'layout' => 'aiagent_setup', 'label' => _x('Set up', 'AI Agent menu tab, a verb: the page where the AI is set up', 'js-support-ticket'), 'badge' => 'setup', 'when' => 'addon'),
                ),
            ),
            'knowledge' => array(
                /* Named for the screen it opens, not for the idea behind it.
                   "Knowledge" sat a few rows under Help Center > Knowledge Base
                   and read as a shortcut to the articles you write, which this
                   is not -- it governs what the AI may read, which spans the
                   knowledge base, FAQs, canned responses, posts, past tickets
                   and crawled pages. It also disagreed with its own first
                   screen, whose <h1> has always been "Knowledge Sources", and
                   a menu label that does not match the heading it opens is the
                   one thing the naming rule here forbids. */
                'label' => __('Knowledge Sources', 'js-support-ticket'),
                'tabs'  => array(
                    array('page' => 'aiagent', 'layout' => 'aiagent_sources', 'label' => __('What the AI may read', 'js-support-ticket')),
                    array('page' => 'aiagent', 'layout' => 'aiagent_feeds',   'label' => __('Web pages', 'js-support-ticket'), 'when' => 'addon', 'also' => array('aiagent_feedform')),
                    array('page' => 'aiagent', 'layout' => 'aiagent_gaps',    'label' => __('Unanswered Questions', 'js-support-ticket')),
                ),
            ),
            'answers' => array(
                'label' => __('Automatic answers', 'js-support-ticket'),
                'tabs'  => array(
                    array('page' => 'aiagent', 'layout' => 'aiagent_autopilot', 'label' => __('Rules', 'js-support-ticket'), 'when' => 'addon'),
                    array('page' => 'aiagent', 'layout' => 'aiagent_approvals', 'label' => __('Approvals', 'js-support-ticket'), 'badge' => 'approvals', 'when' => 'addon'),
                    array('page' => 'aiagent', 'layout' => 'aiagent_shadow',    'label' => __('Shadow Mode', 'js-support-ticket'), 'when' => 'addon'),
                    array('page' => 'aiagent', 'layout' => 'aiagent_answers',   'label' => __('Results', 'js-support-ticket'), 'when' => 'addon'),
                ),
                'needs_engine' => true,
            ),
            'settings' => array(
                'label' => __('Settings', 'js-support-ticket'),
                'tabs'  => array(
                    array('page' => 'aiagent', 'layout' => 'aiagent_settings',  'label' => __('Settings', 'js-support-ticket')),
                    /* Prompt catalogue and Prompt Lab: links hidden at the desk's
                       request. The screens themselves are untouched and still
                       open at their own addresses
                       (admin.php?page=zywrap&jstlay=zywrap_settings / zywrap_playground).
                    array('page' => 'zywrap',  'layout' => 'zywrap_settings',   'label' => __('Prompt catalogue', 'js-support-ticket'), 'when' => 'zywrapkey'),
                    array('page' => 'zywrap',  'layout' => 'zywrap_playground', 'label' => __('Prompt Lab', 'js-support-ticket'), 'when' => 'zywrapkey'),
                    */
                ),
            ),
            'activity' => array(
                /* Named for the screen it opens, like Knowledge Sources above. */
                'label' => __('Usage & Cost', 'js-support-ticket'),
                'tabs'  => array(
                    /* Always reachable: what was spent and who changed what are
                       worth reading most when the engine is not working now. */
                    array('page' => 'aiagent', 'layout' => 'aiagent_usage', 'label' => __('Usage & Cost', 'js-support-ticket')),
                    array('page' => 'aiagent', 'layout' => 'aiagent_audit', 'label' => __('Audit', 'js-support-ticket')),
                    array('page' => 'zywrap',  'layout' => 'zywrap_logs',   'label' => __('Zywrap requests', 'js-support-ticket'), 'when' => 'zywrapkey'),
                    array('page' => 'zywrap',  'layout' => 'zywrap_errors', 'label' => __('Zywrap errors', 'js-support-ticket'), 'when' => 'zywrapkey'),
                ),
            ),
        );
    }

    /** Is this tab's condition met on this site? */
    private static function available($jsst_tab) {
        if (empty($jsst_tab['when'])) {
            return true;
        }
        if ($jsst_tab['when'] === 'addon') {
            return in_array('aiagent', jssupportticket::$_active_addons);
        }
        if ($jsst_tab['when'] === 'engine') {
            return class_exists('JSSTaiengine') && JSSTaiengine::usable();
        }
        if ($jsst_tab['when'] === 'zywrapkey') {
            return class_exists('JSSTaiengine') && JSSTaiengine::apiKey('zywrap') !== '';
        }
        return true;
    }

    /** A group's tabs this site can open. */
    public static function tabs($jsst_group) {
        $jsst_groups = self::groups();
        if (!isset($jsst_groups[$jsst_group])) {
            return array();
        }
        return array_values(array_filter($jsst_groups[$jsst_group]['tabs'], array(__CLASS__, 'available')));
    }

    /** Admin address of one tab. */
    public static function url($jsst_tab) {
        return admin_url('admin.php?page=' . $jsst_tab['page'] . '&jstlay=' . $jsst_tab['layout']);
    }

    /** Where a group's menu entry goes: its first tab this site can open. */
    public static function groupUrl($jsst_group) {
        $jsst_tabs = self::tabs($jsst_group);
        return $jsst_tabs ? self::url($jsst_tabs[0]) : admin_url('admin.php?page=aiagent');
    }

    /** The group a layout belongs to, or '' (layout without its admin_ prefix). */
    public static function groupOf($jsst_layout) {
        $jsst_layout = preg_replace('/^admin_/', '', (string) $jsst_layout);
        if ($jsst_layout === '') {
            $jsst_layout = 'aiagent';
        }
        foreach (self::groups() as $jsst_key => $jsst_group) {
            foreach ($jsst_group['tabs'] as $jsst_tab) {
                $jsst_also = isset($jsst_tab['also']) ? $jsst_tab['also'] : array();
                if ($jsst_tab['layout'] === $jsst_layout || in_array($jsst_layout, $jsst_also, true)) {
                    return $jsst_key;
                }
            }
        }
        return '';
    }

    /** The number a menu entry or tab carries, or 0. */
    public static function badge($jsst_kind) {
        if ($jsst_kind === 'approvals' && class_exists('JSSTaireview')) {
            $jsst_counts = JSSTaireview::counts();
            return isset($jsst_counts['held']) ? (int) $jsst_counts['held'] : 0;
        }
        if ($jsst_kind === 'setup' && class_exists('JSSTaisetup') && JSSTaisetup::nagging()) {
            return -1; // "to do" rather than a number
        }
        return 0;
    }

    /** A group's combined badge for its menu entry. */
    public static function groupBadge($jsst_group) {
        $jsst_total = 0;
        foreach (self::tabs($jsst_group) as $jsst_tab) {
            if (!empty($jsst_tab['badge'])) {
                $jsst_b = self::badge($jsst_tab['badge']);
                if ($jsst_b < 0) {
                    return -1;
                }
                $jsst_total += $jsst_b;
            }
        }
        return $jsst_total;
    }

    /**
     * Are automatic answers reaching customers while setup is unfinished?
     *
     * Only the add-on sends, and only through an engine that works, so without
     * either nothing is going out whatever the rollout switch says.
     */
    public static function sendingUnfinished() {
        $jsst_status = self::status();
        return $jsst_status['state'] === 'sending' && !empty($jsst_status['unfinished']);
    }

    /**
     * Where AI stands, in one answer every AI screen shows.
     *
     * Each screen used to work this out for itself, so one page said "AI is
     * replying to customers automatically" while the next said no engine was
     * set up. This is asked once and drawn the same way everywhere: what is
     * true now, why, and the one thing to press to change it.
     *
     * @return array state, tone (ok|info|warn|off), title, text, actions[]
     */
    public static function status() {
        static $jsst_memo = null;
        if ($jsst_memo !== null) return $jsst_memo;

        $jsst_settings = admin_url('admin.php?page=aiagent&jstlay=aiagent_settings');
        $jsst_rules    = admin_url('admin.php?page=aiagent&jstlay=aiagent_autopilot');
        $jsst_addon    = in_array('aiagent', jssupportticket::$_active_addons);
        $jsst_out = array('state' => '', 'tone' => 'info', 'title' => '', 'text' => '', 'actions' => array(), 'unfinished' => false);

        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled()) {
            $jsst_out['state'] = 'off';
            $jsst_out['tone']  = 'off';
            $jsst_out['title'] = __('AI is switched off', 'js-support-ticket');
            $jsst_out['text']  = __('Nothing is sent anywhere. Suggested articles from your own content still work.', 'js-support-ticket');
            $jsst_out['actions'][] = array(__('Switch AI on', 'js-support-ticket'), $jsst_settings . '#AISwitches', true);
            return $jsst_memo = $jsst_out;
        }

        if (!class_exists('JSSTaiengine') || !JSSTaiengine::usable()) {
            $jsst_out['state'] = 'noengine';
            $jsst_out['tone']  = 'warn';
            $jsst_out['title'] = __('AI is not working yet', 'js-support-ticket');
            if (class_exists('JSSTaiengine')) {
                /* The one reason, said plainly: which engine is chosen and what
                   is missing for it, rather than "no engine is ready". */
                $jsst_id    = JSSTaiengine::currentId();
                $jsst_label = JSSTaiengine::currentLabel();
                $jsst_lane  = JSSTaiengine::currentLane();
                $jsst_lanes = class_exists('JSSTaipolicy') ? JSSTaipolicy::lanes() : array();
                if (class_exists('JSSTaipolicy') && !JSSTaipolicy::allows($jsst_lane)) {
                    $jsst_out['text'] = sprintf(
                        /* translators: 1: engine name, 2: lane name, e.g. "Local model" */
                        __('Your engine is %1$s, but the "%2$s" lane is switched off.', 'js-support-ticket'),
                        $jsst_label, isset($jsst_lanes[$jsst_lane]['label']) ? wp_strip_all_tags($jsst_lanes[$jsst_lane]['label']) : $jsst_lane);
                    $jsst_out['actions'][] = array(__('Switch it on', 'js-support-ticket'), $jsst_settings . '#AISwitches', true);
                } else {
                    $jsst_out['text'] = ($jsst_id === 'local')
                        ? sprintf(__('Your engine is %s, but it has no address or model name yet.', 'js-support-ticket'), $jsst_label)
                        : sprintf(__('Your engine is %s, but it has no key yet.', 'js-support-ticket'), $jsst_label);
                    $jsst_out['actions'][] = array(__('Finish the engine', 'js-support-ticket'), $jsst_settings . '#AIEngine', true);
                }
                /* Another engine that would work right now, offered by name. */
                foreach (array_keys(JSSTaiengine::engines()) as $jsst_other) {
                    if ($jsst_other !== $jsst_id && JSSTaiengine::usable($jsst_other)) {
                        $jsst_def = JSSTaiengine::byId($jsst_other);
                        $jsst_out['actions'][] = array(sprintf(__('Use %s instead', 'js-support-ticket'), $jsst_def['def']['label']), $jsst_settings . '#AIEngine', false);
                        break;
                    }
                }
            }
            $jsst_out['text'] .= ' ' . __('Suggested articles from your own content still work.', 'js-support-ticket');
            return $jsst_memo = $jsst_out;
        }

        $jsst_engine = JSSTaiengine::currentLabel();
        $jsst_on     = $jsst_addon && class_exists('JSSTairollout') && JSSTairollout::on();
        $jsst_paused = $jsst_on && JSSTairollout::paused();
        $jsst_mode   = class_exists('JSSTaireview') ? JSSTaireview::mode() : '';

        if (!$jsst_on) {
            $jsst_out['state'] = 'ready';
            $jsst_out['tone']  = 'ok';
            $jsst_out['title'] = sprintf(__('AI is ready (%s)', 'js-support-ticket'), $jsst_engine);
            $jsst_out['text']  = __('Automatic answers are off. Agents get suggestions and drafts; nothing is sent to customers by itself.', 'js-support-ticket');
            if ($jsst_addon) $jsst_out['actions'][] = array(__('Automatic answers', 'js-support-ticket'), $jsst_rules, false);
        } elseif ($jsst_paused) {
            $jsst_out['state'] = 'paused';
            $jsst_out['tone']  = 'info';
            $jsst_out['title'] = __('Automatic answers are paused', 'js-support-ticket');
            $jsst_out['text']  = sprintf(__('%s is ready. Nothing is written or sent until you resume.', 'js-support-ticket'), $jsst_engine);
            $jsst_out['actions'][] = array(__('Resume', 'js-support-ticket'), wp_nonce_url(admin_url('admin.php?page=aiagent&task=pauseautopilot&action=jstask&resume=1'), 'jsst-aiagent-pause'), true);
        } elseif ($jsst_mode === (class_exists('JSSTaireview') ? JSSTaireview::MODE_NEVER : 'never')) {
            $jsst_out['state'] = 'proposing';
            $jsst_out['tone']  = 'info';
            $jsst_out['title'] = __('AI writes answers, nothing is sent', 'js-support-ticket');
            $jsst_out['text']  = __('Shadow mode: every answer is kept for you to compare. Customers receive nothing from AI.', 'js-support-ticket');
        } elseif ($jsst_mode === (class_exists('JSSTaireview') ? JSSTaireview::MODE_ALWAYS : 'always')) {
            $jsst_out['state'] = 'approving';
            $jsst_out['tone']  = 'info';
            $jsst_out['title'] = __('AI writes answers, a person approves each one', 'js-support-ticket');
            $jsst_out['text']  = __('Nothing reaches a customer until somebody presses Approve.', 'js-support-ticket');
            $jsst_out['actions'][] = array(__('Approvals', 'js-support-ticket'), admin_url('admin.php?page=aiagent&jstlay=aiagent_approvals'), false);
        } else {
            $jsst_out['state'] = 'sending';
            $jsst_out['unfinished'] = class_exists('JSSTaisetup') && !JSSTaisetup::complete();
            $jsst_out['tone']  = $jsst_out['unfinished'] ? 'warn' : 'ok';
            $jsst_out['title'] = __('AI is answering customers automatically', 'js-support-ticket');
            $jsst_out['text']  = $jsst_out['unfinished']
                ? __('Setup is not finished. Pause until you have checked what it will send.', 'js-support-ticket')
                : sprintf(__('%s sends answers that clear your confidence floor; the rest wait for a person.', 'js-support-ticket'), $jsst_engine);
        }

        if (in_array($jsst_out['state'], array('sending', 'proposing', 'approving'), true)) {
            $jsst_out['actions'][] = array(__('Pause automatic answers', 'js-support-ticket'),
                wp_nonce_url(admin_url('admin.php?page=aiagent&task=pauseautopilot&action=jstask'), 'jsst-aiagent-pause'), $jsst_out['state'] === 'sending');
        }
        if (!empty($jsst_out['unfinished'])) {
            $jsst_out['actions'][] = array(__('Finish setup', 'js-support-ticket'), admin_url('admin.php?page=aiagent&jstlay=aiagent_setup'), false);
        }
        return $jsst_memo = $jsst_out;
    }

    /** The status line, on every AI screen. One line: what is true, and the fix. */
    private static function renderStatus() {
        $jsst_s = self::status();
        if ($jsst_s['state'] === '') return;
        echo '<div class="jsst-ai-status jsst-ai-status-' . esc_attr($jsst_s['tone']) . '" role="status">'
            . '<span class="jsst-ai-status-dot" aria-hidden="true"></span>'
            . '<span class="jsst-ai-status-text"><strong>' . esc_html($jsst_s['title']) . '.</strong> ' . esc_html($jsst_s['text']) . '</span>';
        if (!empty($jsst_s['actions'])) {
            echo '<span class="jsst-ai-status-acts">';
            $jsst_here = isset($_GET['jstlay']) ? sanitize_key(wp_unslash($_GET['jstlay'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- which screen, nothing changes
            foreach ($jsst_s['actions'] as $jsst_a) {
                // A button to the screen you are already on goes nowhere.
                $jsst_target = (string) wp_parse_url($jsst_a[1], PHP_URL_QUERY);
                parse_str($jsst_target, $jsst_q);
                if ($jsst_here !== '' && isset($jsst_q['jstlay']) && $jsst_q['jstlay'] === $jsst_here && empty($jsst_q['task'])
                        && strpos($jsst_a[1], '#') === false) continue; // a #section still scrolls
                echo '<a class="jsst-btn jsst-btn-sm' . (!empty($jsst_a[2]) ? ' jsst-btn-primary' : '') . '" href="' . esc_url($jsst_a[1]) . '">' . esc_html($jsst_a[0]) . '</a>';
            }
            echo '</span>';
        }
        echo '</div>';
    }

    /**
     * Draw the tab bar for the screen being shown, plus the group's notice.
     *
     * Called by each AI screen as the first thing inside #jsstadmin-data-wrp.
     */
    public static function render($jsst_layout) {
        $jsst_group = self::groupOf($jsst_layout);
        if ($jsst_group === '') {
            return;
        }
        $jsst_layout = preg_replace('/^admin_/', '', (string) $jsst_layout);
        $jsst_tabs = self::tabs($jsst_group);
        $jsst_groups = self::groups();

        self::renderStatus();

        if (count($jsst_tabs) > 1) {
            echo '<ul class="jsst-tabs jsst-ai-tabs" aria-label="' . esc_attr($jsst_groups[$jsst_group]['label']) . '">';
            foreach ($jsst_tabs as $jsst_tab) {
                $jsst_also = isset($jsst_tab['also']) ? $jsst_tab['also'] : array();
                $jsst_on = ($jsst_tab['layout'] === $jsst_layout || in_array($jsst_layout, $jsst_also, true)
                    || ($jsst_layout === '' && $jsst_tab['layout'] === 'aiagent'));
                echo '<li><a class="jsst-tab' . ($jsst_on ? ' is-on' : '') . '" href="' . esc_url(self::url($jsst_tab)) . '"'
                    . ($jsst_on ? ' aria-current="page"' : '') . '>' . esc_html($jsst_tab['label']);
                if (!empty($jsst_tab['badge'])) {
                    $jsst_b = self::badge($jsst_tab['badge']);
                    if ($jsst_b < 0) {
                        echo ' <span class="jsst-menu-badge">' . esc_html__('to do', 'js-support-ticket') . '</span>';
                    } elseif ($jsst_b > 0) {
                        echo ' <span class="jsst-menu-badge">' . esc_html(number_format_i18n($jsst_b)) . '</span>';
                    }
                }
                echo '</a></li>';
            }
            echo '</ul>';
        }

    }
}
