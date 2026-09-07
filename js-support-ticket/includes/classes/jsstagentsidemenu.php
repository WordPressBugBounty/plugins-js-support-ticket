<?php
/**
 * JS Support Ticket - Agent left menu. (Roadmap 4.0-SEC-04)
 *
 * The administrator side menu is administration: departments, priorities,
 * statuses, e-mail templates, configuration. An agent holds none of the
 * capabilities those screens are gated on, so rendering it for them would be a
 * column of links that all refuse. Rendering nothing was worse — it left the
 * 280px container empty, a blank white strip down the whole page.
 *
 * So this is the short version: the screens an agent actually has. It reads the
 * same markup and classes as the administrator menu, so it inherits the styling
 * and the collapse behaviour with no extra CSS.
 *
 * @package js-support-ticket
 * @subpackage templates
 */

if (!defined('ABSPATH')) {
    die('Restricted Access');
}

$jsst_c = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : null;
$jsst_layout = isset($_GET['jstlay']) ? sanitize_text_field(wp_unslash($_GET['jstlay'])) : null;
?>
<div id="jsstadmin-logo">
    <a title="<?php echo esc_attr__('JS HelpDesk System', 'js-support-ticket'); ?>" class="jsst-anchor" href="<?php echo esc_url(admin_url('admin.php?page=ticket')); ?>">
        <div class="logo-icon">
            <img alt="<?php echo esc_attr__('JS HelpDesk', 'js-support-ticket'); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/left-icons/menu/logo.png">
        </div>
        <span class="logo-text"><?php echo esc_attr__('JS HelpDesk', 'js-support-ticket'); ?></span>
    </a>
</div>
<ul class="jsstadmin-sidebar-menu tree">
    <li class="menu-header"><?php echo esc_html__('Main', 'js-support-ticket'); ?></li>
    <li class="<?php if ($jsst_c === 'ticket') { echo 'active'; } ?>">
        <a href="<?php echo esc_url(admin_url('admin.php?page=ticket')); ?>" title="<?php echo esc_attr__('Tickets', 'js-support-ticket'); ?>">
            <span class="jsst_text"><?php echo esc_html__('Tickets', 'js-support-ticket'); ?></span>
        </a>
    </li>
    <?php
    // Only when the capability is actually there to use it. (Roadmap 4.0-CORE-03)
    if (JSSTmergedaddon::featureEnabled('cannedresponses') && JSSTroles::canReplyPublicly()) { ?>
        <li class="<?php if ($jsst_c === 'cannedresponses') { echo 'active'; } ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses')); ?>" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>">
                <span class="jsst_text"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></span>
            </a>
        </li>
    <?php } ?>
    <?php
    // Matches the Knowledge Base entry on the agent's own WordPress menu, which
    // is gated on the same capability.
    if (in_array('knowledgebase', jssupportticket::$_active_addons) && JSSTroles::canAuthorKnowledge()) { ?>
        <li class="<?php if ($jsst_c === 'knowledgebase') { echo 'active'; } ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase')); ?>" title="<?php echo esc_attr__('Knowledge Base', 'js-support-ticket'); ?>">
                <span class="jsst_text"><?php echo esc_html__('Knowledge Base', 'js-support-ticket'); ?></span>
            </a>
        </li>
    <?php } ?>
    <li>
        <a href="<?php echo esc_url(admin_url('profile.php')); ?>" title="<?php echo esc_attr__('My Profile', 'js-support-ticket'); ?>">
            <span class="jsst_text"><?php echo esc_html__('My Profile', 'js-support-ticket'); ?></span>
        </a>
    </li>
</ul>
