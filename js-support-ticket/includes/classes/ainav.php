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
                    array('page' => 'aiagent', 'layout' => 'aiagent_setup', 'label' => __('Set up', 'js-support-ticket'), 'badge' => 'setup', 'when' => 'addon'),
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
                    array('page' => 'aiagent', 'layout' => 'aiagent_usage', 'label' => __('Usage & Cost', 'js-support-ticket'), 'when' => 'engine'),
                    array('page' => 'aiagent', 'layout' => 'aiagent_audit', 'label' => __('Audit', 'js-support-ticket'), 'when' => 'engine'),
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
     * Only the add-on sends, so without it nothing is going out whatever the
     * rollout switch says.
     */
    public static function sendingUnfinished() {
        if (!in_array('aiagent', jssupportticket::$_active_addons)) return false;
        if (!class_exists('JSSTairollout') || !JSSTairollout::live()) return false;
        /* Automatic answers can be switched on with approval set to "never
           send, only propose" - every answer is held for a person. Nothing
           reaches a customer then, so "AI is replying to customers
           automatically" was a false alarm. */
        if (class_exists('JSSTaireview') && JSSTaireview::mode() === JSSTaireview::MODE_NEVER) return false;
        return class_exists('JSSTaisetup') && !JSSTaisetup::complete();
    }

    /** The warning, with a Pause button, on every AI screen while that is so. */
    private static function renderLiveWarning() {
        if (!self::sendingUnfinished()) return;
        $jsst_pause = wp_nonce_url(admin_url('admin.php?page=aiagent&task=pauseautopilot&action=jstask'), 'jsst-aiagent-pause');
        echo '<div class="jsst-card jsst-ai-live-warning" role="alert"><div class="jsst-card-body">'
            . '<strong class="jsst-ai-live-title">' . esc_html__('AI is replying to customers automatically', 'js-support-ticket') . '</strong>'
            . '<p class="jsst-hint">' . esc_html__('Setup is not finished yet. Pause automatic answers until you have checked what it will send.', 'js-support-ticket') . '</p>'
            . '<div class="jsst-btnrow">'
            . '<a class="jsst-btn jsst-btn-primary" href="' . esc_url($jsst_pause) . '">' . esc_html__('Pause automatic answers', 'js-support-ticket') . '</a> '
            . '<a class="jsst-btn" href="' . esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_setup')) . '">' . esc_html__('Finish setup', 'js-support-ticket') . '</a>'
            . '</div></div></div>';
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

        self::renderLiveWarning();

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

        /* Automatic answers need something to write them. Said once, on every
           screen of the group, while no engine can answer - the Set up page
           already says it, and these are the screens where settings would
           otherwise be saved for a feature that cannot run. */
        if (!empty($jsst_groups[$jsst_group]['needs_engine'])
                && class_exists('JSSTaiengine') && !JSSTaiengine::usable()) {
            echo '<div class="jsst-card jsst-ai-engine-note"><div class="jsst-card-body"><p class="jsst-hint">'
                . esc_html__('No AI engine is set up, so answers cannot be written yet. Suggested articles from your own content still work.', 'js-support-ticket')
                . ' <a href="' . esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_settings#AIEngine')) . '">'
                . esc_html__('Set up an engine', 'js-support-ticket') . '</a></p></div></div>';
        }
    }
}
