<?php
/**
 * JS Support Ticket - Agent left menu. (Roadmap 4.0-SEC-04, 4.5-FE-02)
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
    <?php
    /* The desk's own destinations, from the one description of them, so this
       column and the portal's navigation strip cannot end up offering
       different things. Everything below this loop is a screen that belongs to
       wp-admin rather than to the desk. (Roadmap 4.5-FE-02) */
    if (class_exists('JSSTnavigation')) {
        foreach (JSSTnavigation::menu(array('shell' => JSSTworkspace::SHELL_BACKEND)) AS $jsst_item) {
            if ($jsst_item['key'] === JSSTnavigation::TICKET) {
                // The ticket in context is where you already are; a link to it
                // in the left column is a link to this page.
                continue;
            }
            ?>
            <li class="<?php if ($jsst_item['current']) { echo 'active'; } ?>">
                <a href="<?php echo esc_url($jsst_item['url']); ?>" title="<?php echo esc_attr($jsst_item['summary']); ?>">
                    <span class="jsst_text"><?php echo esc_html($jsst_item['label']); ?></span>
                </a>
            </li>
            <?php
        }
    } else { ?>
        <li class="<?php if ($jsst_c === 'ticket') { echo 'active'; } ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=ticket')); ?>" title="<?php echo esc_attr__('Tickets', 'js-support-ticket'); ?>">
                <span class="jsst_text"><?php echo esc_html__('Tickets', 'js-support-ticket'); ?></span>
            </a>
        </li>
    <?php } ?>
    <?php
    // Only when the capability is actually there to use it. (Roadmap 4.0-CORE-03)
    if (JSSTmergedaddon::featureEnabled('cannedresponses') && JSSTroles::canReplyPublicly()) { ?>
        <li class="<?php if ($jsst_c === 'cannedresponses') { echo 'active'; } ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses')); ?>" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>">
                <span class="jsst_text"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></span>
            </a>
        </li>
    <?php } ?>
    <?php /* (Roadmap 4.5-FE-06) An agent's own hours, leave and status. Their
       own only - the controller narrows the roster to them - because booking
       time off is something a person does about themselves rather than a
       favour they ask an administrator for.

       `page=agentautoassign&jstlay=availability`, which is where that screen
       actually lives. This pointed at `page=jssupportticket&jstlay=availability`
       until 6.5: a layout the jssupportticket controller has no case for, so it
       fell to that switch's `default: exit;` and rendered as a blank white page
       - no message, no menu, nothing to read as a cause. There was never a
       second availability screen to build here; the one in the agents add-on
       has shown an agent their own row since it was written.

       Offered only to somebody who has a row to edit. On this product an
       agent is a `js_ticket_staff` record, and holding the WordPress role
       without one is an ordinary half-configured state rather than a broken
       site - the queue, notifications and the desk home all handle it by
       showing that person nothing. Availability cannot: there is no record to
       show and none to write, so the screen refuses, and a menu entry whose
       only outcome is a refusal is the same bug as a menu entry pointing at an
       unregistered slug. It is left out until the administrator adds them to
       the Agents list. */
    if (class_exists('JSSTavailability') && class_exists('JSSTcapability')
            && (int) JSSTcapability::actor()['staffid'] > 0) { ?>
        <li class="<?php if ($jsst_c === 'agentautoassign' && ($jsst_layout === 'availability' || $jsst_layout === 'editavailability')) { echo 'active'; } ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=agentautoassign&jstlay=availability')); ?>" title="<?php echo esc_attr__('My Hours & Leave', 'js-support-ticket'); ?>">
                <span class="jsst_text"><?php echo esc_html__('My Hours & Leave', 'js-support-ticket'); ?></span>
            </a>
        </li>
    <?php } ?>
    <li>
        <a href="<?php echo esc_url(admin_url('profile.php')); ?>" title="<?php echo esc_attr__('My Profile', 'js-support-ticket'); ?>">
            <span class="jsst_text"><?php echo esc_html__('My Profile', 'js-support-ticket'); ?></span>
        </a>
    </li>
</ul>
