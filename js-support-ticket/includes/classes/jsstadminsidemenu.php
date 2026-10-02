<?php
/**
 * JS Support Ticket - Admin Left Menu
 *
 * This file contains the HTML for the admin sidebar menu.
 *
 * @package js-support-ticket
 * @subpackage templates
 */

if (!defined('ABSPATH')) {
    die('Restricted Access');
}
// Get current page and layout from request
$jsst_c = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : null;
$jsst_layout = isset($_GET['jstlay']) ? sanitize_text_field(wp_unslash($_GET['jstlay'])) : null;
$jsst_ff = isset($_GET['fieldfor']) ? sanitize_text_field(wp_unslash($_GET['fieldfor'])) : null;
$jsst_for = isset($_GET['for']) ? sanitize_text_field(wp_unslash($_GET['for'])) : null;
// System Status reached through Plugin Support's Debug Report entry: that entry
// is the active one then, not System > System Status. The #anchor it also
// carries never reaches the server, so the query string says where it came from.
$jsst_debugreport = ($jsst_c == 'jssupportticket' && $jsst_layout == 'systemstatus' && $jsst_for == 'debugreport');

// Inline script for menu accordion and collapse functionality.
$jsst_jssupportticket_js = '
    jQuery( function() {
        jQuery( ".accordion" ).accordion({
            heightStyle: "content",
            collapsible: true,
            active: true,
        });
    });

    var cookielist = document.cookie.split(";");
    for (var i=0; i<cookielist.length; i++) {
        if (cookielist[i].trim() == "jsst_collapse_admin_menu=1") {
            jQuery("body").addClass("menu-collapsed");
            break;
        }
    }

    jQuery(document).ready(function(){
        var pageWrapper = jQuery("body");
        var sideMenuArea = jQuery("#jsstadmin-leftmenu");
        jQuery("#jsstadmin-menu-toggle").on("click", function (e) {
            e.preventDefault();
            if (pageWrapper.hasClass("menu-collapsed")) {
                pageWrapper.removeClass("menu-collapsed");
                document.cookie = "jsst_collapse_admin_menu=0; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            } else {
                pageWrapper.addClass("menu-collapsed");
                document.cookie = "jsst_collapse_admin_menu=1; expires=Sat, 01 Jan 2050 00:00:00 UTC; path=/";
            }
        });
        
        jQuery(".jsstadmin-sidebar-menu .treeview > a").on("click", function(event) {
            const parentLi = jQuery(this).parent();
            if (parentLi.hasClass("disabled-menu")) {
                event.preventDefault();
                return;
            }

            if (jQuery("body").hasClass("menu-collapsed")) {
                event.preventDefault();
            } else {
                if (parentLi.hasClass("treeview")) {
                    event.preventDefault();
                    if(parentLi.hasClass("active")) {
                        parentLi.removeClass("active");
                    } else {
                        parentLi.addClass("active");
                    }
                }
            }
        });
    });
';
// wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
?>
<?php /* Restore the collapsed rail before anything is painted.
   This runs where it sits - inside the already-open #jsstadmin-leftmenu - so
   the class is on the element before the browser lays the menu out. Doing it
   on DOM ready instead would draw the full 280px sidebar and then snap it to
   88px on every single page load. (Roadmap 5.1-NAV-05) */ ?>
<script>
(function () {
    try {
        var want = document.cookie.split(';').some(function (c) {
            return c.trim() === 'jsst_collapse_admin_menu=1';
        });
        if (!want) { return; }
        var menu = document.getElementById('jsstadmin-leftmenu');
        if (menu) { menu.classList.add('menu-collapsed'); }
        if (document.body) { document.body.classList.add('menu-collapsed'); }
    } catch (e) {}
})();
</script>
<div id="jsstadmin-logo">
    <a title="<?php echo esc_attr__('JS HelpDesk System', 'js-support-ticket'); ?>" class="jsst-anchor" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>">
        <div class="logo-icon">
            <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/left-icons/menu/logo.png">
        </div>
        <span class="logo-text"><?php echo esc_attr__('JS HelpDesk', 'js-support-ticket'); ?></span>
    </a>
    <svg id="jsstadmin-menu-toggle" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
    </svg>
</div>
<div class="jsst-navfind">
    <span class="jsst-navfind-icon" aria-hidden="true"></span>
    <input type="search" id="jsst-navfind" class="jsst-navfind-input" autocomplete="off"
           placeholder="<?php echo esc_attr__('Find a screen', 'js-support-ticket'); ?>"
           aria-label="<?php echo esc_attr__('Find a screen', 'js-support-ticket'); ?>" />
</div>
<p class="jsst-navfind-none" hidden><?php echo esc_html__('Nothing matches.', 'js-support-ticket'); ?></p>
<ul class="jsstadmin-sidebar-menu tree accordion">
    <?php /* How this menu is arranged. (Roadmap 5.1-NAV-02)

       Seventeen sections, each named for its subject, and one rule:

           a screen belongs to the subject somebody would name when looking
           for it - not to the release that added it, and not to a box called
           "System" or "Configuration" or "Main".

       The rule is written here because it was broken twice. Every screen from
       4.0 to 6.0 was appended to the Dashboard group instead, which grew to
       thirty-nine children while seven other headings sat unused. Then the
       tidy-up that followed swung the other way and swept ten unrelated
       screens into one "System" heading - Themes beside Cron URLs beside GDPR
       - which is the same mistake wearing a different label.

       Sections fold, so a section costs one row whether it holds two screens
       or eight. That is why there are seventeen of them and not eight: once
       folding is the mechanic, precision is free, and a heading that names
       one subject is worth more than a heading that names a container.

       If a new screen has no subject here, the answer is a new heading.

       A heading and a group are two levels, and five sections only ever had
       one group in them - so the menu said "Tickets", then "Tickets" again a
       row lower, and the screen itself was a third row down. Those five
       (Overview, Tickets, Customers, Automation & SLA, AI Agent) have no group
       level: their screens hang straight off the heading, which already folds
       and already carries the icon. A section keeps its group level as soon as
       it has two of them to tell apart. Anything the dissolved group's label
       carried moved up to the heading - the AI Agent "off" badge is the only
       one there was.

       A group whose fold holds a single link is written here as a plain link
       already; the rule further down still runs, because a group can become a
       group-of-one at run time when an addon is off. */ ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg><span class="jsst_text"><?php echo esc_html__('Overview', 'js-support-ticket'); ?></span></li>
        <li class="<?php if($jsst_c == 'jssupportticket' && ($jsst_layout == 'controlpanel' || $jsst_layout == '')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>" title="<?php echo esc_attr__('Dashboard', 'js-support-ticket'); ?>"><?php echo esc_html__('Dashboard', 'js-support-ticket'); ?></a></li>
        <?php
        // Setup. Shown until it is finished or put away, with the count of
        // what is left, so an unfinished install says so rather than
        // waiting to be discovered. (Roadmap 4.0-UX-01)
        if (class_exists('JSSTsetup') && !JSSTsetup::dismissed()) {
            $jsst_setupprogress = JSSTsetup::progress();
            if ($jsst_setupprogress['done'] < $jsst_setupprogress['total']) { ?>
                <li class="<?php if($jsst_c == 'postinstallation' && $jsst_layout == 'setup') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=setup')); ?>" title="<?php echo esc_attr__('Setup', 'js-support-ticket'); ?>"><?php echo esc_html__('Setup', 'js-support-ticket'); ?> <span class="jsst-setup-badge"><?php echo esc_html($jsst_setupprogress['total'] - $jsst_setupprogress['done']); ?></span></a></li>
            <?php }
        }
        ?>
        <?php /* (Roadmap 4.5-FE-02) The desk itself: what needs this person
           this morning. Above the administration screens because it is the
           one entry here an administrator working tickets opens daily. */ ?>
        <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'workspacehome') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=workspacehome')); ?>" title="<?php echo esc_attr__('My Desk', 'js-support-ticket'); ?>"><?php echo esc_html__('My Desk', 'js-support-ticket'); ?></a></li>
        <?php /* (Roadmap 4.5-UX-02) What happened that concerns you. Directly
           under My Desk because the two are the same morning: what needs me,
           and what changed while I was away. The count is what is unread. */
        if (class_exists('JSSTnotifications')) {
            /* The staff id comes from the capability service rather than
               from the assignment addon: the notification centre is part of
               free core and must not need a paid addon to know who is
               looking at it. */
            $jsst_nfactor = JSSTcapability::actor();
            $jsst_nfstaff = (int) $jsst_nfactor['staffid'];
            $jsst_nfunseen = ($jsst_nfstaff > 0) ? JSSTnotifications::unseen($jsst_nfstaff) : 0; ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'notifications') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=notifications')); ?>" title="<?php echo esc_attr__('Notifications', 'js-support-ticket'); ?>"><?php echo esc_html__('Notifications', 'js-support-ticket'); ?><?php if ($jsst_nfunseen > 0) { ?> <span class="jsst-setup-badge"><?php echo esc_html($jsst_nfunseen); ?></span><?php } ?></a></li>
        <?php } ?>
        <?php /* License & Add-ons (one page since 2 October 2026; the Addons
           menu below it is gone). In Overview rather than at the foot of the menu
           under Plugin Support (moved 30 September 2026): every licence
           banner in wp-admin points here, and a key that has stopped working
           is something to see without scrolling. The mark appears only when a
           key is stored and not active - expired, suspended, or this site
           removed from it - never for a free install that has no key. */
        $jsst_licenseproblem = (class_exists('JSSTlicense') && JSSTlicense::hasKey() && !JSSTlicense::isActive())
            || (class_exists('JSSTlicencegate') && array() !== JSSTlicencegate::gated()); ?>
        <li class="<?php if($jsst_c == 'jssupportticket' && ($jsst_layout == 'license' || $jsst_layout == 'legacy')) echo 'active'; ?> menu-item-license"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')); ?>" title="<?php echo esc_attr__('License & Add-ons', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg><span class="jsst_text"><?php echo esc_html__('License & Add-ons', 'js-support-ticket'); ?></span><?php if ($jsst_licenseproblem) { ?> <span class="jsst-setup-badge" title="<?php echo esc_attr__('Your license key is not active on this site', 'js-support-ticket'); ?>">!</span><?php } ?></a></li>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg><span class="jsst_text"><?php echo esc_html__('Tickets', 'js-support-ticket'); ?></span></li>
        <li class="<?php if($jsst_c == 'ticket' && $jsst_layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=ticket')); ?>" title="<?php echo esc_attr__('Tickets', 'js-support-ticket'); ?>"><?php echo esc_html__('Tickets', 'js-support-ticket'); ?></a></li>
        <?php
        $jsst_href = admin_url('admin.php?page=ticket&jstlay=addticket&formid=' . JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId());
        $jsst_extra_attributes = '';
        if (in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1) {
            $jsst_href = '#';
            $jsst_extra_attributes = "id=multiformpopup";
        }
        ?>
        <li class="<?php if($jsst_c == 'ticket' && ($jsst_layout == 'addticket')) echo 'active'; ?>"><a <?php echo esc_attr($jsst_extra_attributes); ?> href="<?php echo esc_url($jsst_href); ?>" class="?page=ticket&jstlay=addticket&formid=<?php echo esc_attr(JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId()) ?>" title="<?php echo esc_attr__('Create Ticket', 'js-support-ticket'); ?>"><?php echo esc_html__('Create Ticket', 'js-support-ticket'); ?></a></li>
        <?php if (JSSTmergedaddon::featureEnabled('export')) { ?>
            <li class="<?php if($jsst_c == 'export' && $jsst_layout != 'csvimport') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=export')); ?>" title="<?php echo esc_attr__('Export', 'js-support-ticket'); ?>"><?php echo esc_html__('Export', 'js-support-ticket'); ?></a></li>
        <?php }
        /* The other half of the same job, so it sits beside it. Shown on
           every site: core serves page=export now even where the legacy
           Export add-on is still active, so the import screen behind this
           link is core's. (Roadmap 4.0-DATA-02, 4.0-CORE-19) */
        { ?>
            <li class="<?php if($jsst_c == 'export' && $jsst_layout == 'csvimport') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=export&jstlay=csvimport')); ?>" title="<?php echo esc_attr__('Import from CSV', 'js-support-ticket'); ?>"><?php echo esc_html__('Import from CSV', 'js-support-ticket'); ?></a></li>
        <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg><span class="jsst_text"><?php echo esc_html__('Ticket Setup', 'js-support-ticket'); ?></span></li>
    <li class="<?php if($jsst_c == 'department') echo 'active'; ?> menu-item-departments">
        <a href="<?php echo esc_url(admin_url('admin.php?page=department')); ?>" title="<?php echo esc_attr__('Departments', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 11.25h6m-6 4.5h6M6.75 21v-2.25a2.25 2.25 0 012.25-2.25h6a2.25 2.25 0 012.25 2.25V21" /></svg><span class="jsst_text"><?php echo esc_html__('Departments', 'js-support-ticket'); ?></span></a>
    </li>
    <li class="<?php if($jsst_c == 'priority') echo 'active'; ?> menu-item-priorities">
        <a href="<?php echo esc_url(admin_url('admin.php?page=priority')); ?>" title="<?php echo esc_attr__('Priorities', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg><span class="jsst_text"><?php echo esc_html__('Priorities', 'js-support-ticket'); ?></span></a>
    </li>
    <li class="<?php if($jsst_c == 'status') echo 'active'; ?> menu-item-statuses">
        <a href="<?php echo esc_url(admin_url('admin.php?page=status')); ?>" title="<?php echo esc_attr__('Ticket Statuses', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg><span class="jsst_text"><?php echo esc_html__('Ticket Statuses', 'js-support-ticket'); ?></span></a>
    </li>
    <?php if(JSSTmergedaddon::featureEnabled('helptopic')){ ?>
        <li class="<?php if($jsst_c == 'helptopic') echo 'active'; ?>">
            <a class="" href="?page=helptopic" title="<?php echo esc_attr__('Ticket Topics', 'js-support-ticket'); ?>" title="<?php echo esc_attr(__('Ticket Topics' , 'js-support-ticket')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 26 24" stroke="currentColor" stroke-width="2" 
                    class="jsst_menu-icon" width="22" height="22">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M8 12h.01M12 12h.01M16 12h.01
                        M23 12c0-4.418-4.48-8-10-8S3 7.582 3 12
                        c0 1.638.502 3.197 1.378 4.48L3 21
                        l5.448-1.742c1.284.877 2.843 1.378 4.48 1.378
                        5.52 0 10-3.582 10-8z"/>
                </svg>
                <span class="jsst_text"><?php echo esc_html(__('Ticket Topics' , 'js-support-ticket')); ?></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-helptopic">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Ticket Topics', 'js-support-ticket'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 26 24" stroke="currentColor" stroke-width="2"  class="jsst_menu-icon" width="22" height="22">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M8 12h.01M12 12h.01M16 12h.01
                        M23 12c0-4.418-4.48-8-10-8S3 7.582 3 12
                        c0 1.638.502 3.197 1.378 4.48L3 21
                        l5.448-1.742c1.284.877 2.843 1.378 4.48 1.378
                        5.52 0 10-3.582 10-8z"/>
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Ticket Topics', 'js-support-ticket'); ?></span>
            </a>
        </li>
    <?php } ?>
    <li class="<?php if($jsst_c == 'product') echo 'active'; ?> menu-item-products">
        <a href="<?php echo esc_url(admin_url('admin.php?page=product')); ?>" title="<?php echo esc_attr__('Products', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l.383-1.437M7.5 14.25L5.106 5.165A2.25 2.25 0 002.854 3H2.25" /></svg><span class="jsst_text"><?php echo esc_html__('Products', 'js-support-ticket'); ?></span></a>
    </li>
        <?php /* What the ticket form asks. One entry, one name, on every
           desk - which it was not until 6.5. A desk without the Multiform
           addon used to get a greyed "Forms" and a real "Fields" beside it,
           pointing at a second, older editor for the same thing; the two had
           drifted far enough apart that which one you had decided whether
           you could delete a question. There is one screen now, and the
           addon's contribution to it is the register of forms at the top.
           It sits in Ticket Setup: it is configured once, not used every day,
           like the departments and topics beside it. (Roadmap 5.0-FORM-01, 6.5-FORM-02) */ ?>
    <li class="<?php if($jsst_c == 'multiform' || $jsst_c == 'fieldordering') echo 'active'; ?> menu-item-forms"><a href="<?php echo esc_url(admin_url('admin.php?page=multiform&jstlay=forms')); ?>" title="<?php echo esc_attr__('Forms', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg><span class="jsst_text"><?php echo esc_html__('Forms', 'js-support-ticket'); ?></span></a></li>
    <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if(($jsst_c == 'feedback' && $jsst_layout != 'satisfaction') || ($jsst_c == 'fieldordering' && $jsst_ff == 2) ) echo 'active'; ?>">
            <a class="" href="#" title="<?php echo esc_attr(__('Feedback','js-support-ticket')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="jsst_menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M17 16h.01" />
                </svg>
                <span class="jsst_text"><?php echo esc_html(__('Feedback', 'js-support-ticket')); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($jsst_c == 'feedback' && ($jsst_layout == 'feedbacks')) echo 'active'; ?>">
                    <a href="?page=feedback&jstlay=feedbacks" title="<?php echo esc_attr(__('Feedback','js-support-ticket')); ?>">
                        <?php echo esc_html(__('Feedback','js-support-ticket')); ?>
                    </a>
                </li>
                <li class="<?php if($jsst_c == 'fieldordering') echo 'active'; ?>">
                    <a href="?page=fieldordering&fieldfor=2" title="<?php echo esc_attr(__('Feedback Fields' , 'js-support-ticket')); ?>">
                        <?php echo esc_html(__('Feedback Fields','js-support-ticket')); ?>
                    </a>
                </li>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-feedbacks">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Feedback', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M17 16h.01" /></svg><span class="jsst_text"><?php echo esc_html__('Feedback', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php if(JSSTmergedaddon::featureEnabled('cannedresponses')){ ?>
        <li class="<?php if($jsst_c == 'cannedresponses') echo 'active'; ?> menu-item-cannedresponses">
            <a href="<?php echo esc_url(admin_url('admin.php?page=cannedresponses')); ?>" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg><span class="jsst_text"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-cannedresponses">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Canned Responses', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg><span class="jsst_text"><?php echo esc_html__('Canned Responses', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Customers', 'js-support-ticket'); ?></span></li>
    <?php /* (Roadmap 4.5-FE-02) The people behind the tickets, and the
       companies they write in from - scoped to what the reader may see, which
       is why it is not the WordPress users list. */ ?>
    <?php /* A group like every other top-level row, which this was not until
       6.5. It was the one plain `<li>` in the whole menu, and the difference is
       not decorative: `.jsstadmin-sidebar-menu > li.treeview > a::after` is what
       draws the `>` on the right of every other row, so this one sat there
       alone with nothing in that column and read as though it had failed to
       finish drawing.

       Companies moved in here from the Dashboard group at the same time, which
       is where it belonged all along - the comment it used to carry said it sat
       "next to nothing else in this list", and this is the something else. The
       two are one subject: who the people writing in are, and who they work
       for. (Roadmap 4.5-FE-02, 5.5-COM-06) */ ?>
        <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'customers') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=customers')); ?>" title="<?php echo esc_attr__('Customers', 'js-support-ticket'); ?>"><?php echo esc_html__('Customers', 'js-support-ticket'); ?></a></li>
        <?php /* Guarded on the capability rather than on an add-on, because
           this is the one screen in the group that decides what a customer
           may read. A reader without it does not get a greyed row: the
           feature is not missing from the site, it is not theirs. */
        if (class_exists('JSSTcapability') && JSSTcapability::can(JSSTcapability::COMPANY_MANAGE)) { ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && ($jsst_layout == 'companies' || $jsst_layout == 'addcompany')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=companies')); ?>" title="<?php echo esc_attr__('Companies', 'js-support-ticket'); ?>"><?php echo esc_html__('Companies', 'js-support-ticket'); ?></a></li>
        <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg><span class="jsst_text"><?php echo esc_html__('Agents & Access', 'js-support-ticket'); ?></span></li>
    <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if(($jsst_c == 'agent' && $jsst_layout != 'visibility' && $jsst_layout != 'editvisibility') || ($jsst_c == 'agentautoassign' && $jsst_layout != 'routing')) echo 'active'; ?> menu-item-agents">
            <a href="#" title="<?php echo esc_attr__('Agents', 'js-support-ticket'); ?>">
                <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <!-- Head -->
                    <circle cx="12" cy="8" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Shoulders -->
                    <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M4 20c0-3.5 3.6-6 8-6s8 2.5 8 6"/>
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Agents', 'js-support-ticket'); ?></span>
            </a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($jsst_c == 'agent' && $jsst_layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agent')); ?>" title="<?php echo esc_attr__('Agents', 'js-support-ticket'); ?>"><?php echo esc_html__('Agents', 'js-support-ticket'); ?></a></li>
                <?php /* "Agent Auto Assign" was listed here until 6.5, and its
                   screen is no longer listed anywhere. (Roadmap 5.0-AUT-02)

                   It was a second assignment engine: its own rules table, its
                   own condition builder, one action, and a hook inside
                   storeTickets() that fired before anything else in the
                   product saw the ticket. Its rules live in Automation now,
                   where they say the same thing - when a ticket is created, if
                   these conditions, assign it to this agent and stop - beside
                   every other rule the desk runs, with a dry run to rehearse
                   them and a record of why each one fired.

                   The old address still answers and redirects there. Who gets
                   the next ticket when no rule names anybody is Assignment,
                   under Automation & SLA. */ ?>
                <?php /* Teams and Availability, which were in the Dashboard group
                   until 6.5. Both are about the people this group lists:
                   how they are grouped, and when they are here. */ ?>
                <?php /* (Roadmap 4.5-FE-05) Only where there are agents to group. */
                if (JSSTincluder::screenExists('agent', 'teams')) { ?>
                    <li class="<?php if($jsst_c == 'agent' && ($jsst_layout == 'teams' || $jsst_layout == 'addteam')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agent&jstlay=teams')); ?>" title="<?php echo esc_attr__('Teams', 'js-support-ticket'); ?>"><?php echo esc_html__('Teams', 'js-support-ticket'); ?></a></li>
                <?php } ?>
                <?php /* (Roadmap 4.5-FE-06) Hours, leave, presence and workload. Next
                   to Teams because a team's calendar is where an agent's hours come
                   from when they have not set their own. */
                if (JSSTincluder::screenExists('agentautoassign', 'availability')) { ?>
                    <li class="<?php if($jsst_c == 'agentautoassign' && ($jsst_layout == 'availability' || $jsst_layout == 'editavailability')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agentautoassign&jstlay=availability')); ?>" title="<?php echo esc_attr__('Hours & Leave', 'js-support-ticket'); ?>"><?php echo esc_html__('Hours & Leave', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
        <li class="<?php if($jsst_c == 'role') echo 'active'; ?> menu-item-roles">
            <a href="<?php echo esc_url(admin_url('admin.php?page=role')); ?>" title="<?php echo esc_attr__('Roles', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12c0-5.03 4.403-9 9.75-9s9.75 3.97 9.75 9-4.403 9-9.75 9-9.75-3.97-9.75-9z" /></svg><span class="jsst_text"><?php echo esc_html__('Agent Roles', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-agents">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Agents', 'js-support-ticket'); ?>">
                <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <!-- Head -->
                    <circle cx="12" cy="8" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- Shoulders -->
                    <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M4 20c0-3.5 3.6-6 8-6s8 2.5 8 6"/>
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Agents', 'js-support-ticket'); ?></span>
            </a>
        </li>
        <li class="disabled-menu menu-item-roles">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Agent Roles', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12c0-5.03 4.403-9 9.75-9s9.75 3.97 9.75 9-4.403 9-9.75 9-9.75-3.97-9.75-9z" /></svg><span class="jsst_text"><?php echo esc_html__('Agent Roles', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php /* Who may do what, and why the desk said yes. Under User Management
       rather than under a system heading because the question is always
       asked about a person: what this agent may reach, which tickets
       they may see, and - when the answer surprises somebody - which
       rule produced it. (Roadmap 4.0-SEC-04, 4.5-FE-04, 4.5-ARCH-04) */ ?>
    <li class="treeview <?php if(($jsst_c == 'jssupportticket' && ($jsst_layout == 'agentaccess' || $jsst_layout == 'permissioninspector')) || ($jsst_c == 'agent' && ($jsst_layout == 'visibility' || $jsst_layout == 'editvisibility'))) echo 'active'; ?> menu-item-access">
        <a href="#" title="<?php echo esc_attr__('Access & Permissions', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg><span class="jsst_text"><?php echo esc_html__('Access & Permissions', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <?php // Roadmap 4.0-SEC-04 ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'agentaccess') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=agentaccess')); ?>" title="<?php echo esc_attr__('Agent Access', 'js-support-ticket'); ?>"><?php echo esc_html__('Agent Access', 'js-support-ticket'); ?></a></li>
            <?php /* (Roadmap 4.5-FE-04) Only where there are agents to scope. */
            if (JSSTincluder::screenExists('agent', 'visibility')) { ?>
                <li class="<?php if($jsst_c == 'agent' && ($jsst_layout == 'visibility' || $jsst_layout == 'editvisibility')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agent&jstlay=visibility')); ?>" title="<?php echo esc_attr__('Visibility Rules', 'js-support-ticket'); ?>"><?php echo esc_html__('Visibility Rules', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php // Roadmap 4.5-ARCH-04 ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'permissioninspector') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=permissioninspector')); ?>" title="<?php echo esc_attr__('Permission Check', 'js-support-ticket'); ?>"><?php echo esc_html__('Permission Check', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg><span class="jsst_text"><?php echo esc_html__('Automation & SLA', 'js-support-ticket'); ?></span></li>

    <?php /* What the desk does without being asked, and what it has promised
       to do. Under Ticket Management because all four act on a ticket:
       the promise it is measured against, the rules that move it, the
       ones raised on a schedule, and who it is given to.
       
       Three add-ons own these screens between them. A screen whose add-on
       is not running is shown greyed out, the way Feedback is under Ticket
       Setup, so the heading never opens onto nothing. */ ?>
            <?php /* (Roadmap 5.0-SLA-01) What the desk has promised, next to the
               hours those promises are counted in. The addon owns the screen,
               so the link is offered where the addon is running - a one-person
               desk with no agent addon still makes promises, but a desk without
               this one makes none it can measure. */
            if (JSSTincluder::screenExists('overdue', 'sla')) { ?>
                <li class="<?php if($jsst_c == 'overdue' && ($jsst_layout == 'sla' || $jsst_layout == 'addsla')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=overdue&jstlay=sla')); ?>" title="<?php echo esc_attr__('Service Levels', 'js-support-ticket'); ?>"><?php echo esc_html__('Service Levels', 'js-support-ticket'); ?></a></li>
            <?php } else { ?>
                <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('Service Levels', 'js-support-ticket'); ?>"><?php echo esc_html__('Service Levels', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php /* (Roadmap 5.0-AUT-01) The rules that run on their own. Above
               Service Levels because a rule can act on an SLA warning, so this
               is the screen somebody goes to after writing a policy. */
            if (JSSTincluder::screenExists('autoclose', 'workflow')) { ?>
                <li class="<?php if($jsst_c == 'autoclose' && ($jsst_layout == 'workflow' || $jsst_layout == 'addworkflow')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=autoclose&jstlay=workflow')); ?>" title="<?php echo esc_attr__('Automation', 'js-support-ticket'); ?>"><?php echo esc_html__('Automation', 'js-support-ticket'); ?><?php
                    $jsst_wfwaiting = class_exists('JSSTworkflow') ? JSSTworkflow::approvalCount() : 0;
                    if ($jsst_wfwaiting > 0) { ?> <span class="jsst-setup-badge"><?php echo esc_html($jsst_wfwaiting); ?></span><?php } ?></a></li>
            <?php } else { ?>
                <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('Automation', 'js-support-ticket'); ?>"><?php echo esc_html__('Automation', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php /* (Roadmap 5.0-AUT-04) Work the desk raises for itself. */
            if (JSSTincluder::screenExists('autoclose', 'recurring')) { ?>
                <li class="<?php if($jsst_c == 'autoclose' && ($jsst_layout == 'recurring' || $jsst_layout == 'addrecurring')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=autoclose&jstlay=recurring')); ?>" title="<?php echo esc_attr__('Recurring Tickets', 'js-support-ticket'); ?>"><?php echo esc_html__('Recurring Tickets', 'js-support-ticket'); ?></a></li>
            <?php } else { ?>
                <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('Recurring Tickets', 'js-support-ticket'); ?>"><?php echo esc_html__('Recurring Tickets', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php /* (Roadmap 5.0-AUT-02) Who gets the next ticket. Only where
               there are agents to give it to. */
            if (JSSTincluder::screenExists('agentautoassign', 'routing') && in_array('agent', jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($jsst_c == 'agentautoassign' && $jsst_layout == 'routing') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=agentautoassign&jstlay=routing')); ?>" title="<?php echo esc_attr__('Assignment', 'js-support-ticket'); ?>"><?php echo esc_html__('Assignment', 'js-support-ticket'); ?></a></li>
            <?php } else { ?>
                <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('Assignment', 'js-support-ticket'); ?>"><?php echo esc_html__('Assignment', 'js-support-ticket'); ?></a></li>
            <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg><span class="jsst_text"><?php echo esc_html__('Help Center', 'js-support-ticket'); ?></span></li>
    <?php /* Categories is the one thing the rest of this group hangs off: the
             Knowledge Base, FAQs, Announcements and Downloads all read
             js_ticket_categories, and this is the only screen that edits it. It
             therefore leads the group rather than trailing it. */
    if(in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
        <li class="<?php if($jsst_c == 'knowledgebase' && ($jsst_layout == 'listcategories' || $jsst_layout == 'addcategory')) echo 'active'; ?> menu-item-categories">
            <a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase&jstlay=listcategories')); ?>" title="<?php echo esc_attr__('Categories', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg><span class="jsst_text"><?php echo esc_html__('Categories', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php if(in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
        <li class="<?php if($jsst_c == 'knowledgebase' && ($jsst_layout == 'listarticles' || $jsst_layout == 'addarticle')) echo 'active'; ?> menu-item-kb">
            <a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase&jstlay=listarticles')); ?>" title="<?php echo esc_attr__('Knowledge Base', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg><span class="jsst_text"><?php echo esc_html__('Knowledge Base', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-kb">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Knowledge Base', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg><span class="jsst_text"><?php echo esc_html__('Knowledge Base', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php if(in_array('faq', jssupportticket::$_active_addons)){ ?>
        <li class="<?php if($jsst_c == 'faq') echo 'active'; ?> menu-item-faqs">
            <a href="<?php echo esc_url(admin_url('admin.php?page=faq')); ?>" title="<?php echo esc_attr__('FAQs', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg><span class="jsst_text"><?php echo esc_html__('FAQs', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-faqs">
            <a href="#" title="<?php echo esc_attr__('FAQs', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg><span class="jsst_text"><?php echo esc_html__('FAQs', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php if(in_array('announcement', jssupportticket::$_active_addons)){ ?>
        <li class="<?php if($jsst_c == 'announcement') echo 'active'; ?> menu-item-announcements">
            <a href="<?php echo esc_url(admin_url('admin.php?page=announcement')); ?>" title="<?php echo esc_attr__('Announcements', 'js-support-ticket'); ?>">
                <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" 
                    viewBox="0 0 28 24" width="22" height="22" stroke="currentColor" stroke-width="2">
                    <!-- Megaphone body -->
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 10l13-5v14l-13-5v-4z" />
                    <!-- Broadcast waves -->
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9c1.5 1 1.5 5 0 6" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7c2.5 2 2.5 8 0 10" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 5c3.5 3 3.5 12 0 15" />
                </svg>
                <span class="jsst_text"><?php echo esc_html__('Announcements', 'js-support-ticket'); ?></span>
            </a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-announcements">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Announcements', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84a3.75 3.75 0 01-5.68 0M19.5 6.375a9 9 0 01-12.728 0" /></svg><span class="jsst_text"><?php echo esc_html__('Announcements', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php if(in_array('download', jssupportticket::$_active_addons)){ ?>
        <li class="<?php if($jsst_c == 'download') echo 'active'; ?> menu-item-download">
            <a href="<?php echo esc_url(admin_url('admin.php?page=download')); ?>" title="<?php echo esc_attr__('Downloads', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" /></svg><span class="jsst_text"><?php echo esc_html__('Downloads', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-download">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Downloads', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" /></svg><span class="jsst_text"><?php echo esc_html__('Downloads', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php if(in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
        <?php /* Content Review (formerly Governance) sits with the content rather
                 than under AI, because it is about the content - the AI is only
                 the loudest reader of it. (Roadmap 6.0-KB-01) */
        if (JSSTincluder::screenExists('knowledgebase', 'kbgovernance')) { ?>
        <li class="<?php if($jsst_c == 'knowledgebase' && $jsst_layout == 'kbgovernance') echo 'active'; ?> menu-item-kbreview">
            <a href="<?php echo esc_url(admin_url('admin.php?page=knowledgebase&jstlay=kbgovernance')); ?>" title="<?php echo esc_attr__('Content Review', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Content Review', 'js-support-ticket'); ?></span></a>
        </li>
        <?php } ?>
    <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.125A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.875L5.999 12zm0 0h7.5" /></svg><span class="jsst_text"><?php echo esc_html__('Outgoing Email', 'js-support-ticket'); ?></span></li>
    <li class="treeview <?php if($jsst_c == 'email') echo 'active'; ?> menu-item-systememails">
        <a href="#" title="<?php echo esc_attr__('System Emails', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg><span class="jsst_text"><?php echo esc_html__('System Emails', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($jsst_c == 'email' && $jsst_layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=email')); ?>" title="<?php echo esc_attr__('System Emails', 'js-support-ticket'); ?>"><?php echo esc_html__('System Emails', 'js-support-ticket'); ?></a></li>
            <?php // Roadmap 4.0-OPS-01 ?>
            <li class="<?php if($jsst_c == 'email' && $jsst_layout == 'emailhealth') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=email&jstlay=emailhealth')); ?>" title="<?php echo esc_attr__('Email Health', 'js-support-ticket'); ?>"><?php echo esc_html__('Email Health', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="<?php if($jsst_c == 'emailtemplate') echo 'active'; ?> menu-item-emailtemplates">
        <a href="<?php echo esc_url(admin_url('admin.php?page=emailtemplate')); ?>" title="<?php echo esc_attr__('Email Templates', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg><span class="jsst_text"><?php echo esc_html__('Email Templates', 'js-support-ticket'); ?></span></a>
    </li>
    <?php /* Which translations exist, which have gone stale, which have lost a
       placeholder and which will render backwards. Its own comment always
       said it belonged "beside the e-mail screens"; from 6.5 it is. */
    if (JSSTincluder::screenExists('multilanguageemailtemplates', 'locales')) { ?>
        <li class="treeview <?php if($jsst_c == 'multilanguageemailtemplates') echo 'active'; ?> menu-item-emaillanguages">
            <a href="#" title="<?php echo esc_attr__('Email Languages', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" /></svg><span class="jsst_text"><?php echo esc_html__('Email Languages', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.5-GLB-01) Which translations exist, which have
                   gone stale, which have lost a placeholder, and which will render
                   backwards. Beside the e-mail screens because that is what it is
                   about. */
                if (JSSTincluder::screenExists('multilanguageemailtemplates', 'locales')) { ?>
                    <li class="<?php if($jsst_c == 'multilanguageemailtemplates') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=multilanguageemailtemplates&jstlay=locales')); ?>" title="<?php echo esc_attr__('Email Languages', 'js-support-ticket'); ?>"><?php echo esc_html__('Email Languages', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>
    <?php if(in_array('emailcc', jssupportticket::$_active_addons)){ ?>
        <li class="<?php if($jsst_c == 'emailcc') echo 'active'; ?> menu-item-emailcc">
            <a href="<?php echo esc_url(admin_url('admin.php?page=emailcc')); ?>" title="<?php echo esc_attr__('Email CC', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 1.5a10.5 10.5 0 100 21 10.5 10.5 0 000-21z" /></svg><span class="jsst_text"><?php echo esc_html__('Email CC', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-emailcc">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Email CC', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 1.5a10.5 10.5 0 100 21 10.5 10.5 0 000-21z" /></svg><span class="jsst_text"><?php echo esc_html__('Email CC', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" /></svg><span class="jsst_text"><?php echo esc_html__('Incoming Email', 'js-support-ticket'); ?></span></li>
    <?php if(in_array('emailpiping', jssupportticket::$_active_addons)){ ?>
        <li class="treeview <?php if($jsst_c == 'emailpiping') echo 'active'; ?> menu-item-emailpiping">
            <a href="#" title="<?php echo esc_attr__('Email Piping', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg><span class="jsst_text"><?php echo esc_html__('Email Piping', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($jsst_c == 'emailpiping' && $jsst_layout != 'inbox') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailpiping')); ?>" title="<?php echo esc_attr__('Email Piping', 'js-support-ticket'); ?>"><?php echo esc_html__('Email Piping', 'js-support-ticket'); ?></a></li>
                <?php /* (Roadmap 5.5-CH-01) How each mailbox signs in, where its
                   mail lands, why replies are or are not threading, and what has
                   bounced. Beside the mailbox list rather than inside it: that
                   form sets up a connection, this screen explains what the
                   connection has been doing. */ ?>
                <?php if (JSSTincluder::screenExists('emailpiping', 'inbox')) { ?>
                <li class="<?php if($jsst_c == 'emailpiping' && $jsst_layout == 'inbox') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=emailpiping&jstlay=inbox')); ?>" title="<?php echo esc_attr__('Inbox', 'js-support-ticket'); ?>"><?php echo esc_html__('Inbox', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-emailpiping">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Email Piping', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg><span class="jsst_text"><?php echo esc_html__('Email Piping', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php /* Mail / Internal Mail is hidden from the menu: the internal mailbox is
       being retired (its screen moves messages onto tickets). The pages still
       work at their addresses for anybody finishing that move. */ ?>
    <?php if(JSSTmergedaddon::featureEnabled('banemail')){ ?>
        <li class="treeview <?php if($jsst_c == 'banemail' || $jsst_c == 'banemaillog') echo 'active'; ?> menu-item-banemails">
            <a href="#" title="<?php echo esc_attr__('Ban Emails', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg><span class="jsst_text"><?php echo esc_html__('Ban Emails', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <li class="<?php if($jsst_c == 'banemail' && $jsst_layout == '') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=banemail')); ?>" title="<?php echo esc_attr__('Banned Emails', 'js-support-ticket'); ?>"><?php echo esc_html__('Banned Emails', 'js-support-ticket'); ?></a></li>
                <?php // The ban log is a Pro feature. (Roadmap 4.0-CORE-12)
                if (JSSTmergedaddon::featureEnabled('banemail')) { ?>
                <li class="<?php if($jsst_c == 'banemaillog') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=banemaillog')); ?>" title="<?php echo esc_attr__('Ban Log', 'js-support-ticket'); ?>"><?php echo esc_html__('Ban Log', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-banemails">
            <a href="<?php echo esc_url('#'); ?>" title="<?php echo esc_attr__('Ban Emails', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg><span class="jsst_text"><?php echo esc_html__('Ban Emails', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg><span class="jsst_text"><?php echo esc_html__('Live Chat', 'js-support-ticket'); ?><?php if (class_exists('JSSTlayout')) { echo JSSTlayout::betaBadge(); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
    <!-- ====== END AI AGENT ====== -->

    <?php /* Live chat, when its add-on is installed. Its own group rather than
             a line under AI Agent: it is a different surface with its own
             hours, its own queue and its own console, and the only thing it
             shares with the AI Agent is where its answers come from.
             (Roadmap 6.0-CH-01) */ ?>
    <?php /* Built like every other group here, which it was not until 6.5. It
             had no icon, so its label started where everybody else's icon did
             and nothing at all showed when the sidebar was collapsed; its
             label had no `jsst_text`; its header linked to a page instead of
             toggling, so clicking the group jumped to Conversations rather
             than opening it; its `<ul>` was missing `jsstadmin-sidebar-submenu`,
             which is what carries the max-height collapse, so this one group
             sat permanently open; and it disappeared entirely when the add-on
             was absent, where every other paid group leaves a greyed row. */ ?>
    <?php /* Flat under the section header, like every other group here.

             This was a `treeview` called Live Chat sitting as the ONLY item
             inside a `menu-header` also called Live Chat - the word twice and
             two clicks to reach Conversations, for a group of three. A section
             that contains one collapsible whose children are the real
             destinations is a level of menu that carries no information.

             The three are now siblings of the header, which is what AI Agent,
             Outgoing Email and the rest already do. The greyed branch matches
             how the AI group greys its add-on rows. */ ?>
    <?php if (in_array('livechat', jssupportticket::$_active_addons)) { ?>
        <li class="<?php if($jsst_c == 'livechat' && ($jsst_layout == '' || $jsst_layout == 'livechat')) echo 'active'; ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=livechat')); ?>" title="<?php echo esc_attr__('Conversations', 'js-support-ticket'); ?>"><?php echo esc_html__('Conversations', 'js-support-ticket'); ?></a>
        </li>
        <li class="<?php if($jsst_c == 'livechat' && $jsst_layout == 'livechat_history') echo 'active'; ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=livechat&jstlay=livechat_history')); ?>" title="<?php echo esc_attr__('History', 'js-support-ticket'); ?>"><?php echo esc_html__('History', 'js-support-ticket'); ?></a>
        </li>
        <li class="<?php if($jsst_c == 'livechat' && $jsst_layout == 'livechat_settings') echo 'active'; ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=livechat&jstlay=livechat_settings')); ?>" title="<?php echo esc_attr__('Settings', 'js-support-ticket'); ?>"><?php echo esc_html__('Settings', 'js-support-ticket'); ?></a>
        </li>
    <?php } else { ?>
        <li class="disabled-menu"><a class="jsstadmin-sidebar-submenu-grey" href="javascript:void(0);" title="<?php echo esc_attr__('Conversations', 'js-support-ticket'); ?>"><?php echo esc_html__('Conversations', 'js-support-ticket'); ?></a></li>
        <li class="disabled-menu"><a class="jsstadmin-sidebar-submenu-grey" href="javascript:void(0);" title="<?php echo esc_attr__('History', 'js-support-ticket'); ?>"><?php echo esc_html__('History', 'js-support-ticket'); ?></a></li>
        <li class="disabled-menu"><a class="jsstadmin-sidebar-submenu-grey" href="javascript:void(0);" title="<?php echo esc_attr__('Settings', 'js-support-ticket'); ?>"><?php echo esc_html__('Settings', 'js-support-ticket'); ?></a></li>
    <?php } ?>
    <?php
    /* Worked out before the heading, because the heading carries the "off"
       badge: these used to sit under it, where the group that owned the
       badge began, and reading $jsst_ai_off above its own assignment is an
       undefined variable every time this menu is drawn. */
    $jsst_ir_active = in_array('aiagent', jssupportticket::$_active_addons);
    $jsst_ai_here = ($jsst_c == 'aiagent' || $jsst_c == 'zywrap');
    $jsst_ai_lab = (class_exists('JSSTaiengine') && JSSTaiengine::apiKey('zywrap') !== '');
    $jsst_ai_off = (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled());
    ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg><span class="jsst_text"><?php echo esc_html__('AI Agent', 'js-support-ticket'); ?><?php if (class_exists('JSSTlayout')) { echo JSSTlayout::betaBadge(); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ($jsst_ai_off) { ?><span class="jsst-menu-badge"><?php echo esc_html__('off', 'js-support-ticket'); ?></span><?php } ?></span></li>
    <?php /* ===== AI AGENT ===== (Roadmap 6.0-AI-01)

       One menu where there were three. Instant Resolve, Zywrap AI and AI
       Copilot each had their own group, each with its own switches and its own
       log, and between them they could not answer the one question a site owner
       brings to this part of the product: is this thing sending my customers'
       tickets somewhere, and what is it costing me. Overview, Settings and Audit
       are core and always here; the three retrieval screens belong to the add-on
       and are greyed the same way every other add-on's are when it is absent.

       Prompt Lab is the old Zywrap Playground under a name that says what it is
       for. It appears only once a Zywrap key exists, because without one it is a
       screen that can only report an error. */
    ?>
        <?php /* Five entries, one per job, drawn from JSSTainav::groups() - the
                 same map that puts the tab bar on every AI screen, so the menu
                 and the tabs cannot disagree. Every screen that used to have
                 its own entry is a tab inside one of these, at the address it
                 always had. The badges moved with their screens: "to do" on
                 Overview (setup), the waiting count on Automatic answers. */
        if (class_exists('JSSTainav')) {
            $jsst_ai_group_now = $jsst_ai_here ? JSSTainav::groupOf($jsst_layout) : '';
            foreach (JSSTainav::groups() as $jsst_ai_gkey => $jsst_ai_g) {
                if (!JSSTainav::tabs($jsst_ai_gkey)) { continue; }
                $jsst_ai_badge = JSSTainav::groupBadge($jsst_ai_gkey); ?>
                <li class="<?php if ($jsst_ai_group_now === $jsst_ai_gkey) echo 'active'; ?>">
                    <a href="<?php echo esc_url(JSSTainav::groupUrl($jsst_ai_gkey)); ?>" title="<?php echo esc_attr($jsst_ai_g['label']); ?>">
                        <?php echo esc_html($jsst_ai_g['label']); ?>
                        <?php if ($jsst_ai_badge < 0) { ?><span class="jsst-menu-badge"><?php echo esc_html__('to do', 'js-support-ticket'); ?></span><?php }
                        elseif ($jsst_ai_badge > 0) { ?><span class="jsst-menu-badge"><?php echo esc_html(number_format_i18n($jsst_ai_badge)); ?></span><?php } ?>
                    </a>
                </li>
            <?php }
        } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg><span class="jsst_text"><?php echo esc_html__('Reports', 'js-support-ticket'); ?></span></li>
    <?php /* Volume over time and the files it is sent out as. (Roadmap
       5.0-ANA-05, 5.0-ANA-01) */
    if (in_array(JSSTbundle::screenPage('admin_analytics'), jssupportticket::$_active_addons) || in_array(JSSTbundle::screenPage('admin_exports'), jssupportticket::$_active_addons)) { ?>
        <li class="treeview <?php if($jsst_layout == 'analytics' || $jsst_layout == 'exports') echo 'active'; ?> menu-item-analytics">
            <a href="#" title="<?php echo esc_attr__('Analytics', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3M9 11.25v1.5M12 9v4.5m3-6.75v6.75" /></svg><span class="jsst_text"><?php echo esc_html__('Analytics', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.0-ANA-05) Volume over time, cohorts and the
                   per-agent numbers, above the governance screens because it is
                   read weekly rather than quarterly. */
                $jsst_analytics_page = JSSTbundle::screenPage('admin_analytics');
                if (in_array($jsst_analytics_page, jssupportticket::$_active_addons)) { ?>
                    <li class="<?php if($jsst_c == $jsst_analytics_page && $jsst_layout == 'analytics') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_analytics_page . '&jstlay=analytics')); ?>" title="<?php echo esc_attr__('Analytics', 'js-support-ticket'); ?>"><?php echo esc_html__('Analytics', 'js-support-ticket'); ?></a></li>
                <?php } ?>
                <?php /* (Roadmap 5.0-ANA-01) Files produced on their own. The
                   export button itself stays on the Export screen. */
                $jsst_exports_page = JSSTbundle::screenPage('admin_exports');
                if (in_array($jsst_exports_page, jssupportticket::$_active_addons)) { ?>
                    <li class="<?php if($jsst_c == $jsst_exports_page && $jsst_layout == 'exports') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_exports_page . '&jstlay=exports')); ?>" title="<?php echo esc_attr__('Scheduled Exports', 'js-support-ticket'); ?>"><?php echo esc_html__('Scheduled Exports', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>
    <li class="treeview <?php if($jsst_c == 'reports' || ($jsst_c == 'feedback' && $jsst_layout == 'satisfaction')) echo 'active'; ?> menu-item-reports">
        <a href="#" title="<?php echo esc_attr__('Reports', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg><span class="jsst_text"><?php echo esc_html__('Reports', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($jsst_c == 'reports' && ($jsst_layout == 'overallreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=overallreport')); ?>" title="<?php echo esc_attr__('Overall Statistics', 'js-support-ticket'); ?>"><?php echo esc_html__('Overall Statistics', 'js-support-ticket'); ?></a></li>
            <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
            <li class="<?php if($jsst_c == 'reports' && ($jsst_layout == 'staffreport' || $jsst_layout == 'staffdetailreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=staffreport')); ?>" title="<?php echo esc_attr__('Agent Reports', 'js-support-ticket'); ?>"><?php echo esc_html__('Agent Reports', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <li class="<?php if($jsst_c == 'reports' && ($jsst_layout == 'departmentreport' || $jsst_layout == 'departmentdetailreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=departmentreport')); ?>" title="<?php echo esc_attr__('Department Reports', 'js-support-ticket'); ?>"><?php echo esc_html__('Department Reports', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($jsst_c == 'reports' && ($jsst_layout == 'userreport' || $jsst_layout == 'userdetailreport')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=userreport')); ?>" title="<?php echo esc_attr__('User Reports', 'js-support-ticket'); ?>"><?php echo esc_html__('User Reports', 'js-support-ticket'); ?></a></li>
            <?php /* "Satisfaction Report" was here until 6.5. It and the
               Satisfaction entry below it answered the same question from the
               same answers and printed different numbers - a mean scaled to a
               percentage against the share of people who were happy - because
               they were written five years apart against two tables. There is
               one table now and one entry. The old address still answers and
               redirects to this one. */ ?>
            <?php /* The verdict itself, next to the report of it. Moved out of
               the Dashboard group in 6.5. (Roadmap 5.0-ANA-03) */ ?>
            <?php /* (Roadmap 5.0-ANA-03) What the customers thought, next to the
               hours and the exports because all three are what a manager reads
               at the end of a month rather than during a day. */
            if (JSSTincluder::screenExists('feedback', 'satisfaction')) { ?>
                <li class="<?php if($jsst_c == 'feedback' && $jsst_layout == 'satisfaction') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=feedback&jstlay=satisfaction')); ?>" title="<?php echo esc_attr__('Satisfaction', 'js-support-ticket'); ?>"><?php echo esc_html__('Satisfaction', 'js-support-ticket'); ?></a></li>
            <?php } ?>
        </ul>
    </li>
    <?php /* Hours worked and what they are worth. The badge counts entries
       waiting to be allowed, which is the one thing on this header that
       holds somebody up. (Roadmap 5.0-ANA-02) */
    if (JSSTincluder::screenExists('timetracking', 'time')) { ?>
        <li class="treeview <?php if($jsst_c == 'timetracking') echo 'active'; ?> menu-item-time">
            <a href="#" title="<?php echo esc_attr__('Time', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Time', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.0-ANA-02) Hours worked and what they are worth,
                   beside the exports because both are what a manager reads at the
                   end of a month. The badge counts entries waiting to be allowed,
                   which is the only thing here that holds up somebody's invoice. */
                if (JSSTincluder::screenExists('timetracking', 'time')) { ?>
                    <li class="<?php if($jsst_c == 'timetracking') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=timetracking&jstlay=time')); ?>" title="<?php echo esc_attr__('Time', 'js-support-ticket'); ?>"><?php echo esc_html__('Time', 'js-support-ticket'); ?><?php
                        $jsst_tmwaiting = class_exists('JSSTtime') ? JSSTtime::pendingCount() : 0;
                        if ($jsst_tmwaiting > 0) { ?> <span class="jsst-setup-badge"><?php echo esc_html($jsst_tmwaiting); ?></span><?php } ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Commerce & Integrations', 'js-support-ticket'); ?></span></li>
    <?php /* What each customer has bought and what is left of it. Its own
       comment always said it belonged "beside the commerce screens";
       this is them. (Roadmap 5.5-COM-04) */
    if (JSSTincluder::screenExists('paidsupport', 'credits')) { ?>
        <li class="treeview <?php if($jsst_c == 'paidsupport') echo 'active'; ?> menu-item-paidsupport">
            <a href="#" title="<?php echo esc_attr__('Paid Support', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Paid Support', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.5-COM-04) What each customer has bought and what
                   is left of it. Beside the commerce screens rather than beside the
                   queues, because it is read by whoever sells the support. */
                if (JSSTincluder::screenExists('paidsupport', 'credits')) { ?>
                    <li class="<?php if($jsst_c == 'paidsupport') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=paidsupport&jstlay=credits')); ?>" title="<?php echo esc_attr__('Paid Support', 'js-support-ticket'); ?>"><?php echo esc_html__('Paid Support', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-paidsupport">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Paid Support', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Paid Support', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php /* What each account is allowed, which is what the sale promised.
       (Roadmap 5.5-COM-05) */
    if (in_array(JSSTbundle::screenPage('admin_entitlements'), jssupportticket::$_active_addons)) { ?>
        <li class="treeview <?php if(in_array($jsst_layout, array('entitlements', 'addentitlement'), true)) echo 'active'; ?> menu-item-entitlements">
            <a href="#" title="<?php echo esc_attr__('Entitlements', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.746 3.746 0 0121 12z" /></svg><span class="jsst_text"><?php echo esc_html__('Entitlements', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.5-COM-05) What each account is allowed. Next to
                   Retention because both are policy about customers rather than
                   about tickets. */
                $jsst_entitlements_page = JSSTbundle::screenPage('admin_entitlements');
                if (in_array($jsst_entitlements_page, jssupportticket::$_active_addons)) { ?>
                    <li class="<?php if($jsst_c == $jsst_entitlements_page && in_array($jsst_layout, array('entitlements', 'addentitlement'), true)) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_entitlements_page . '&jstlay=entitlements')); ?>" title="<?php echo esc_attr__('Entitlements', 'js-support-ticket'); ?>"><?php echo esc_html__('Entitlements', 'js-support-ticket'); ?><?php
                        if (class_exists('JSSTentitlements')) {
                            $jsst_ensum = JSSTentitlements::summary();
                            /* Rehearsing with rules switched on is a state somebody
                               forgets they are in, and the badge is how they are
                               reminded that nothing is actually being enforced. */
                            if (!empty($jsst_ensum['enabled']) && !empty($jsst_ensum['rehearse']) && $jsst_ensum['live'] > 0) { ?> <span class="jsst-setup-badge">1</span><?php }
                        } ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-entitlements">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Entitlements', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.746 3.746 0 0121 12z" /></svg><span class="jsst_text"><?php echo esc_html__('Entitlements', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <?php /* The way other software talks to this desk, and the way it is
       talked back to. (Roadmap 5.0-API-01, 5.0-API-02, 5.0-API-03) */
    if (in_array(JSSTbundle::screenPage('admin_api'), jssupportticket::$_active_addons) || in_array(JSSTbundle::screenPage('admin_webhooks'), jssupportticket::$_active_addons)) { ?>
        <li class="treeview <?php if($jsst_layout == 'api' || $jsst_layout == 'webhooks') echo 'active'; ?> menu-item-api">
            <a href="#" title="<?php echo esc_attr__('API & Webhooks', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" /></svg><span class="jsst_text"><?php echo esc_html__('API & Webhooks', 'js-support-ticket'); ?><?php if (class_exists('JSSTlayout')) { echo JSSTlayout::betaBadge(); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.0-API-01) The way other software talks to this
                   desk, and (5.0-API-02, 5.0-API-03) the way it is talked back to. */
                $jsst_api_page = JSSTbundle::screenPage('admin_api');
                if (in_array($jsst_api_page, jssupportticket::$_active_addons)) { ?>
                    <li class="<?php if($jsst_c == $jsst_api_page && $jsst_layout == 'api') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_api_page . '&jstlay=api')); ?>" title="<?php echo esc_attr__('API', 'js-support-ticket'); ?>"><?php echo esc_html__('API', 'js-support-ticket'); ?></a></li>
                <?php } else { ?>
                    <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('API', 'js-support-ticket'); ?>"><?php echo esc_html__('API', 'js-support-ticket'); ?></a></li>
                <?php } ?>
                <?php $jsst_webhooks_page = JSSTbundle::screenPage('admin_webhooks');
                if (in_array($jsst_webhooks_page, jssupportticket::$_active_addons)) { ?>
                    <li class="<?php if($jsst_c == $jsst_webhooks_page && $jsst_layout == 'webhooks') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_webhooks_page . '&jstlay=webhooks')); ?>" title="<?php echo esc_attr__('Webhooks', 'js-support-ticket'); ?>"><?php echo esc_html__('Webhooks', 'js-support-ticket'); ?></a></li>
                <?php } else { ?>
                    <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('Webhooks', 'js-support-ticket'); ?>"><?php echo esc_html__('Webhooks', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-api">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('API & Webhooks', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" /></svg><span class="jsst_text"><?php echo esc_html__('API & Webhooks', 'js-support-ticket'); ?><?php if (class_exists('JSSTlayout')) { echo JSSTlayout::betaBadge(); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></a>
        </li>
    <?php } ?>
    <?php /* The catalogue of what this desk is connected to, and the rooms
       the team is already in. (Roadmap 5.5-CH-04, 5.5-CH-02) */
    if (in_array(JSSTbundle::screenPage('admin_connectors'), jssupportticket::$_active_addons) || in_array(JSSTbundle::screenPage('admin_chat'), jssupportticket::$_active_addons)) { ?>
        <li class="treeview <?php if($jsst_layout == 'connectors' || $jsst_layout == 'chat') echo 'active'; ?> menu-item-connectors">
            <a href="#" title="<?php echo esc_attr__('Integrations', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg><span class="jsst_text"><?php echo esc_html__('Integrations', 'js-support-ticket'); ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.5-CH-04) Everything this desk talks to that is
                   not this desk, on the terms they all share. Next to the webhooks
                   because outbound webhooks are the same idea with one destination
                   rather than a catalogue of them. */
                $jsst_connectors_page = JSSTbundle::screenPage('admin_connectors');
                if (in_array($jsst_connectors_page, jssupportticket::$_active_addons)) { ?>
                    <li class="<?php if($jsst_c == $jsst_connectors_page && $jsst_layout == 'connectors') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_connectors_page . '&jstlay=connectors')); ?>" title="<?php echo esc_attr__('Integrations', 'js-support-ticket'); ?>"><?php echo esc_html__('Integrations', 'js-support-ticket'); ?><?php
                        $jsst_confailing = class_exists('JSSTconnectors') ? (int) JSSTconnectors::summary()['failing'] : 0;
                        if ($jsst_confailing > 0) { ?> <span class="jsst-setup-badge"><?php echo esc_html($jsst_confailing); ?></span><?php } ?></a></li>
                <?php } else { ?>
                    <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('Integrations', 'js-support-ticket'); ?>"><?php echo esc_html__('Integrations', 'js-support-ticket'); ?></a></li>
                <?php } ?>
                <?php /* (Roadmap 5.5-CH-02) The rooms the team is already in. Under
                   Integrations because a channel is one of the things this desk
                   talks to, and the routing is the part that is not. */
                $jsst_chat_page = JSSTbundle::screenPage('admin_chat');
                if (in_array($jsst_chat_page, jssupportticket::$_active_addons)) { ?>
                    <li class="<?php if($jsst_c == $jsst_chat_page && $jsst_layout == 'chat') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_chat_page . '&jstlay=chat')); ?>" title="<?php echo esc_attr__('Team Chat', 'js-support-ticket'); ?>"><?php echo esc_html__('Team Chat', 'js-support-ticket'); ?></a></li>
                <?php } else { ?>
                    <li class="disabled-menu"><a href="javascript:void(0);" title="<?php echo esc_attr__('Team Chat', 'js-support-ticket'); ?>"><?php echo esc_html__('Team Chat', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } else { ?>
        <li class="disabled-menu menu-item-connectors">
            <a href="javascript:void(0);" title="<?php echo esc_attr__('Integrations', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg><span class="jsst_text"><?php echo esc_html__('Integrations', 'js-support-ticket'); ?></span></a>
        </li>
    <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.438 1.15-.438 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" /></svg><span class="jsst_text"><?php echo esc_html__('Appearance', 'js-support-ticket'); ?></span></li>
    <li class="treeview <?php if($jsst_c == 'themes' || $jsst_layout == 'branding') echo 'active'; ?> menu-item-themes">
        <a href="#" title="<?php echo esc_attr__('Themes', 'js-support-ticket'); ?>">
            <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="6" x2="20" y2="6" stroke-linecap="round"></line>
                <circle cx="8" cy="6" r="1.6"></circle>
                <line x1="4" y1="12" x2="20" y2="12" stroke-linecap="round"></line>
                <circle cx="14" cy="12" r="1.6"></circle>
                <line x1="4" y1="18" x2="20" y2="18" stroke-linecap="round"></line>
                <circle cx="12" cy="18" r="1.6"></circle>
            </svg>
            <span class="jsst_text"><?php echo esc_html__('Themes', 'js-support-ticket'); ?></span>
        </a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($jsst_c == 'themes' && ($jsst_layout == 'themes')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=themes&jstlay=themes')); ?>" title="<?php echo esc_attr__('Themes', 'js-support-ticket'); ?>"><?php echo esc_html__('Themes', 'js-support-ticket'); ?></a></li>
            <?php /* What the customer sees and what the e-mail says it came
               from, beside the other screen that decides how the portal looks.
               (Roadmap 4.5-FE-09) */ ?>
            <?php /* (Roadmap 4.5-FE-09) What the customer sees and what the
               e-mail says it came from. */
            $jsst_branding_page = JSSTbundle::screenPage('admin_branding');
            if (in_array($jsst_branding_page, jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($jsst_c == $jsst_branding_page && $jsst_layout == 'branding') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_branding_page . '&jstlay=branding')); ?>" title="<?php echo esc_attr__('Branding', 'js-support-ticket'); ?>"><?php echo esc_html__('Branding', 'js-support-ticket'); ?></a></li>
            <?php } ?>
        </ul>
    </li>
    <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'shortcodes') echo 'active'; ?> menu-item-shortcodes">
        <a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=shortcodes')); ?>" title="<?php echo esc_attr__('Shortcodes', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" /></svg><span class="jsst_text"><?php echo esc_html__('Shortcodes', 'js-support-ticket'); ?></span></a>
    </li>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg><span class="jsst_text"><?php echo esc_html__('Privacy & Security', 'js-support-ticket'); ?></span></li>
    <li class="treeview <?php if($jsst_c == 'gdpr' || $jsst_layout == 'retention' || ($jsst_c == 'jssupportticket' && $jsst_layout == 'marketing')) echo 'active'; ?> menu-item-gdpr">
        <a href="#" title="<?php echo esc_attr__('GDPR', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286zm0 13.036h.008v.008h-.008v-.008z" /></svg><span class="jsst_text"><?php echo esc_html__('GDPR', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($jsst_c == 'gdpr' && ($jsst_layout == 'gdprfields' || $jsst_layout == 'addgdprfield')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=gdpr&jstlay=gdprfields')); ?>" title="<?php echo esc_attr__('GDPR Fields', 'js-support-ticket'); ?>"><?php echo esc_html__('GDPR Fields', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($jsst_c == 'gdpr' && ($jsst_layout == 'erasedatarequests')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=gdpr&jstlay=erasedatarequests')); ?>" title="<?php echo esc_attr__('Erase Data Requests', 'js-support-ticket'); ?>"><?php echo esc_html__('Erase Data Requests', 'js-support-ticket'); ?></a></li>
            <?php /* Retention and marketing consent, from the Dashboard group.
               Both are policy about what may be held and for how long, which
               is the question this group already answered. (Roadmap 5.0-ANA-04,
               5.5-SEC-02) */ ?>
            <?php /* (Roadmap 5.0-ANA-04) How long things are kept and what may
               never go. The badge is a deletion waiting for somebody, which is
               the one thing here that holds something up. */
            $jsst_retention_page = JSSTbundle::screenPage('admin_retention');
            if (in_array($jsst_retention_page, jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($jsst_c == $jsst_retention_page && $jsst_layout == 'retention') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_retention_page . '&jstlay=retention')); ?>" title="<?php echo esc_attr__('Retention', 'js-support-ticket'); ?>"><?php echo esc_html__('Retention', 'js-support-ticket'); ?><?php
                    if (class_exists('JSSTretention') && JSSTretention::waitingCount() > 0) { ?> <span class="jsst-setup-badge">1</span><?php } ?></a></li>
            <?php } ?>
            <?php /* (Roadmap 5.5-SEC-02) Retiring the MailChimp add-on: the
               consent a newsletter checkbox should always have recorded. Shown
               wherever the add-on is or has been installed, and wherever
               somebody has already answered the question. */
            if (in_array('mailchimp', jssupportticket::$_active_addons)
                    || (class_exists('JSSTconsent') && get_option(JSSTconsent::OPT_SCHEMA, '') !== '')) { ?>
                <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'marketing') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=marketing')); ?>" title="<?php echo esc_attr__('Marketing Consent', 'js-support-ticket'); ?>"><?php echo esc_html__('Marketing Consent', 'js-support-ticket'); ?></a></li>
            <?php } ?>
        </ul>
    </li>
    <?php /* The most sensitive thing this desk stores, and every way in to
       it. (Roadmap 5.5-SEC-01, 5.0-SEC-01) */
    if (JSSTincluder::screenExists('privatecredentials', 'vault') || class_exists('JSSTauthmatrix')) { ?>
        <li class="treeview <?php if(($jsst_c == 'privatecredentials') || ($jsst_c == 'jssupportticket' && $jsst_layout == 'security')) echo 'active'; ?> menu-item-security">
            <a href="#" title="<?php echo esc_attr__('Security', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg><span class="jsst_text"><?php echo esc_html__('Security', 'js-support-ticket'); ?><?php if (class_exists('JSSTlayout')) { echo JSSTlayout::betaBadge(); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></a>
            <ul class="jsstadmin-sidebar-submenu treeview-menu">
                <?php /* (Roadmap 5.5-SEC-01) The most sensitive thing this desk
                   stores, and the screen that says where its key is. Badged when
                   the key is the database fallback, because a site that has
                   silently stopped protecting its customers' passwords must not
                   look identical to one that has not. */
                if (JSSTincluder::screenExists('privatecredentials', 'vault')) { ?>
                    <li class="<?php if($jsst_c == 'privatecredentials') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=privatecredentials&jstlay=vault')); ?>" title="<?php echo esc_attr__('Credentials', 'js-support-ticket'); ?>"><?php echo esc_html__('Credentials', 'js-support-ticket'); ?><?php
                        if (class_exists('JSSTcredentialvault')) {
                            $jsst_vaultkey = JSSTcredentialvault::keySource();
                            if (empty($jsst_vaultkey['safe'])) { ?> <span class="jsst-setup-badge">1</span><?php }
                        } ?></a></li>
                <?php } ?>
                <?php /* (Roadmap 5.0-SEC-01) Every entry point and what guards it,
                   read out of the source. Beside For Developers and System Status
                   because all three are read rather than set, and because the
                   question it answers - what can reach this desk - is the one
                   somebody asks on the same visit as what version am I on. */
                if (class_exists('JSSTauthmatrix')) { ?>
                    <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'security') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=security')); ?>" title="<?php echo esc_attr__('Ways In', 'js-support-ticket'); ?>"><?php echo esc_html__('Ways In', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg><span class="jsst_text"><?php echo esc_html__('System', 'js-support-ticket'); ?></span></li>
    <li class="treeview <?php if($jsst_c == 'configuration' || $jsst_c == 'slug' || ($jsst_c == 'jssupportticket' && $jsst_layout == 'translations')) echo 'active'; ?> menu-item-configurations">
        <a href="#" title="<?php echo esc_attr__('Configurations', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.438.995s.145.755.438.995l1.003.827c.485.4.665 1.102.26 1.431l-1.296 2.247a1.125 1.125 0 01-1.37.49l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.332.183-.582.495-.645.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.37-.49l-1.296-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.437-.995s-.145-.755-.437-.995l-1.004-.827a1.125 1.125 0 01-.26-1.431l1.296-2.247a1.125 1.125 0 011.37-.49l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.645-.87l.213-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg><span class="jsst_text"><?php echo esc_html__('Configurations', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <li class="<?php if($jsst_c == 'configuration' && $jsst_layout != 'cronjoburl') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=configuration&jsstconfigid=general')); ?>" title="<?php echo esc_attr__('Configurations', 'js-support-ticket'); ?>"><?php echo esc_html__('Configurations', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($jsst_c == 'configuration' && $jsst_layout == 'cronjoburl') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=configuration&jstlay=cronjoburl')); ?>" title="<?php echo esc_attr__('Cron Job URLs', 'js-support-ticket'); ?>"><?php echo esc_html__('Cron Job URLs', 'js-support-ticket'); ?></a></li>
            <?php /* The portal's own address. A setting, and it sat in the
               Dashboard group until 6.5. */ ?>
            <li class="<?php if($jsst_c == 'slug' && ($jsst_layout == 'slug')) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=slug&jstlay=slug')); ?>" title="<?php echo esc_attr__('Slug', 'js-support-ticket'); ?>"><?php echo esc_html__('Slug', 'js-support-ticket'); ?></a></li>
            <?php /* Which languages the help desk is installed in, and the .po and
               .mo to download. It was on this menu in 4.x and went missing in
               5.0.0. (1 Oct 2026) */
            if (class_exists('JSSTtranslations') && current_user_can('install_languages')) { ?>
                <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'translations') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=translations')); ?>" title="<?php echo esc_attr__('Translations', 'js-support-ticket'); ?>"><?php echo esc_html__('Translations', 'js-support-ticket'); ?></a></li>
            <?php } ?>
        </ul>
    </li>
    
    <?php /* The Addons menu held one entry, Install Add-ons, which is now
             part of License & Add-ons under Overview. (2 October 2026) */ ?>
    <li class="treeview <?php if($jsst_c == 'thirdpartyimport') echo 'active'; ?> menu-item-import">
        <a href="#" title="<?php echo esc_attr__('Import Data', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg><span class="jsst_text"><?php echo esc_html__('Import Data', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <?php /* The counted preview and its report. (Roadmap 4.0-DATA-01)

               The original Import Data screen is no longer listed here. It runs
               the same importers this does and nothing else — no source count
               beforehand, no journal, no rollback — so offering both was
               offering the same import twice, once without a way back. The
               screen itself is untouched and still answers at
               ?page=thirdpartyimport&jstlay=importdata for anyone who has it
               bookmarked or is part way through an import on it. */ ?>
            <li class="<?php if($jsst_c == 'thirdpartyimport' && $jsst_layout == 'migrationpreview') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=thirdpartyimport&jstlay=migrationpreview')); ?>" title="<?php echo esc_attr__('Import with Preview', 'js-support-ticket'); ?>"><?php echo esc_html__('Import with Preview', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($jsst_c == 'thirdpartyimport' && $jsst_layout == 'migrationresult') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=thirdpartyimport&jstlay=migrationresult')); ?>" title="<?php echo esc_attr__('Import Report', 'js-support-ticket'); ?>"><?php echo esc_html__('Import Report', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <?php /* Is it working, and if not, what stopped. Five screens somebody
       opens in one sitting and in roughly this order: what the desk
       says about itself, what it is storing that in, what has not run,
       what to do about it, and what it has already written down.
       (Roadmap 4.0-OPS-02, 4.0-PERF-03, 5.0-API-04, 4.0-OPS-03) */ ?>
    <li class="treeview <?php if($jsst_c == 'systemerror' || $jsst_layout == 'jobs' || ($jsst_c == 'jssupportticket' && (($jsst_layout == 'systemstatus' && !$jsst_debugreport) || $jsst_layout == 'storageengine' || $jsst_layout == 'diagnostics'))) echo 'active'; ?> menu-item-systemstatus">
        <a href="#" title="<?php echo esc_attr__('System Status', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z" /></svg><span class="jsst_text"><?php echo esc_html__('System Status', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <?php // Roadmap 4.0-OPS-02 ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'systemstatus' && !$jsst_debugreport) echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=systemstatus')); ?>" title="<?php echo esc_attr__('System Status', 'js-support-ticket'); ?>"><?php echo esc_html__('System Status', 'js-support-ticket'); ?></a></li>
            <?php /* (Roadmap 4.0-PERF-03) */ ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'storageengine') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=storageengine')); ?>" title="<?php echo esc_attr__('Storage Engine', 'js-support-ticket'); ?>"><?php echo esc_html__('Storage Engine', 'js-support-ticket'); ?></a></li>
            <?php /* (Roadmap 5.0-API-04) Beside System Status rather than beside
               the API, because it is a diagnostic and that is where somebody
               goes when something has stopped happening. */
            $jsst_jobs_page = JSSTbundle::screenPage('admin_jobs');
            if (in_array($jsst_jobs_page, jssupportticket::$_active_addons)) { ?>
                <li class="<?php if($jsst_c == $jsst_jobs_page && $jsst_layout == 'jobs') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=' . $jsst_jobs_page . '&jstlay=jobs')); ?>" title="<?php echo esc_attr__('Background Work', 'js-support-ticket'); ?>"><?php echo esc_html__('Background Work', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php /* (Roadmap 4.0-OPS-03) */ ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'diagnostics') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=diagnostics')); ?>" title="<?php echo esc_attr__('When Something Goes Wrong', 'js-support-ticket'); ?>"><?php echo esc_html__('When Something Goes Wrong', 'js-support-ticket'); ?></a></li>
            <li class="<?php if($jsst_c == 'systemerror') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=systemerror')); ?>" title="<?php echo esc_attr__('System Errors', 'js-support-ticket'); ?>"><?php echo esc_html__('System Errors', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <?php /* Read rather than set: the hooks and events, the two workspaces
       compared. (Roadmap 4.0-DATA-03, 4.5-FE-01) */ ?>
    <li class="treeview <?php if($jsst_c == 'jssupportticket' && ($jsst_layout == 'developers' || $jsst_layout == 'workspaceparity')) echo 'active'; ?> menu-item-developers">
        <a href="#" title="<?php echo esc_attr__('For Developers', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" /></svg><span class="jsst_text"><?php echo esc_html__('For Developers', 'js-support-ticket'); ?></span></a>
        <ul class="jsstadmin-sidebar-submenu treeview-menu">
            <?php /* (Roadmap 4.0-DATA-03) The hooks, the events and the schema
               history, read out of the source. Next to System Status because
               both are read rather than set. */
            if (class_exists('JSSThooks')) { ?>
                <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'developers') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=developers')); ?>" title="<?php echo esc_attr__('For Developers', 'js-support-ticket'); ?>"><?php echo esc_html__('For Developers', 'js-support-ticket'); ?></a></li>
            <?php } ?>
            <?php // Roadmap 4.5-FE-01 ?>
            <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'workspaceparity') echo 'active'; ?>"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=workspaceparity')); ?>" title="<?php echo esc_attr__('Workspace Parity', 'js-support-ticket'); ?>"><?php echo esc_html__('Workspace Parity', 'js-support-ticket'); ?></a></li>
        </ul>
    </li>
    <li class="menu-header"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.712 4.33a9.027 9.027 0 011.652 1.306c.51.51.944 1.064 1.306 1.652M16.712 4.33l-3.448 4.138m3.448-4.138a9.014 9.014 0 00-9.424 0M19.67 7.288l-4.138 3.448m4.138-3.448a9.014 9.014 0 010 9.424m-4.138-5.976a3.736 3.736 0 00-.88-1.388 3.737 3.737 0 00-1.388-.88m2.268 2.268a3.765 3.765 0 010 2.528m-2.268-4.796a3.765 3.765 0 00-2.528 0m4.796 4.796c-.181.506-.475.982-.88 1.388a3.736 3.736 0 01-1.388.88m2.268-2.268l4.138 3.448m0 0a9.027 9.027 0 01-1.306 1.652c-.51.51-1.064.944-1.652 1.306m0 0l-3.448-4.138m3.448 4.138a9.014 9.014 0 01-9.424 0m5.976-4.138a3.765 3.765 0 01-2.528 0m0 0a3.736 3.736 0 01-1.388-.88 3.737 3.737 0 01-.88-1.388m2.268 2.268L7.288 19.67m0 0a9.024 9.024 0 01-1.652-1.306 9.027 9.027 0 01-1.305-1.652m0 0l4.138-3.448M4.33 16.712a9.014 9.014 0 010-9.424m4.138 5.976a3.765 3.765 0 010-2.528m0 0c.181-.506.475-.982.88-1.388a3.736 3.736 0 011.388-.88m-2.268 2.268L4.33 7.288m6.406 1.18L7.288 4.33m0 0a9.024 9.024 0 00-1.652 1.306A9.025 9.025 0 004.33 7.288" /></svg><span class="jsst_text"><?php echo esc_html__('Plugin Support', 'js-support-ticket'); ?></span></li>
    <?php /* Plugin Support is its own group, last and outside System, so it
       can be found with every other group folded. The debug report is made
       on System Status; this entry is the way in for somebody asked for one. */ ?>
    <?php /* The licence moved up to Overview (30 September 2026). */ ?>
    <li class="<?php if($jsst_c == 'jssupportticket' && $jsst_layout == 'help') echo 'active'; ?> menu-item-help">
        <a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=help')); ?>" title="<?php echo esc_attr__('Guides & Videos', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg><span class="jsst_text"><?php echo esc_html__('Guides & Videos', 'js-support-ticket'); ?></span></a>
    </li>
    <li class="menu-item-contactsupport">
        <a href="https://jshelpdesk.com/support/" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr__('Contact Support', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg><span class="jsst_text"><?php echo esc_html__('Contact Support', 'js-support-ticket'); ?></span></a>
    </li>
    <li class="<?php if($jsst_debugreport) echo 'active'; ?> menu-item-debugreport">
        <a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=systemstatus&for=debugreport#jsst-debug-report')); ?>" title="<?php echo esc_attr__('Debug Report', 'js-support-ticket'); ?>"><svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg><span class="jsst_text"><?php echo esc_html__('Debug Report', 'js-support-ticket'); ?></span></a>
    </li>
</ul>
<?php if(in_array('multiform', jssupportticket::$_active_addons)){ ?>
    <?php JSSTlayout::adminFormPicker(); ?>
<?php }
$jsst_jssupportticket_js ='
    jQuery(document).ready(function ($) {
        jQuery("a#multiformpopup").click(function (e) {
            e.preventDefault();
            var url = jQuery("a#multiformpopup").prop("class");
            jQuery("div#multiformpopupblack").show();
            var ajaxurl ="'.admin_url('admin-ajax.php').'";
            jsShowLoading();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "multiform", task: "getmultiformlistajax", url:url, "_wpnonce":"'.esc_attr(wp_create_nonce("get-multi-form-list-ajax")).'"}, function (data) {
                if(data){
                    jsHideLoading();
                    jQuery("div#records").html("");
                    jQuery("div#records").html(data);
                    // setUserLink(); generate error
                    jQuery("div#multiformpopup").slideDown("slow");
                }
            });
        });

        jQuery("div#multiformpopupblack , .multiformpopup-header-close-img").click(function (e) {
            jQuery("div#multiformpopup").slideUp("slow", function () {
                jQuery("div#multiformpopupblack").hide();
            });
        });
    });

    function makeMultiFormUrl(id){
        var oldUrl = jQuery("a.js-multiformpopup-link").attr("id"); // Get current url
        var newUrl = oldUrl+"&formid="+id; // Create new url
        window.location.href = newUrl;
    }

    function jsShowLoading(){
        jQuery("div#black_wrapper_translation").show();
        jQuery("div#jstran_loading").show();
    }

    function jsHideLoading(){
        jQuery("div#black_wrapper_translation").hide();
        jQuery("div#jstran_loading").hide();
    }
';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_jssupportticket_js = "
    // --- Groups of one ---
    // A submenu with one item in it is never worth a chevron: it is two clicks
    // to reach a screen you could have reached in one, and on 'Download' the
    // single child was called 'Downloads', so it was also a disclosure whose
    // reward was the same word with an s on it. Any group of one collapses
    // into a plain link, taking the child's address and the child's name -
    // the child is what the screen is actually called.
    //
    // The template writes these as plain links now, so on a normal load this
    // finds nothing left to do. It stays because it is the only thing that can
    // catch a group that becomes a group-of-one at run time - because an addon
    // is inactive, or because the 'Add X' entry went - and because it is what
    // decides the rule the template follows: take the child's address and the
    // child's name, the child being what the screen is actually called.
    document.querySelectorAll('.jsstadmin-sidebar-menu > li.treeview').forEach(function (li) {
        var sub = li.querySelector('.jsstadmin-sidebar-submenu');
        if (!sub) { return; }
        var kids = sub.querySelectorAll(':scope > li');
        if (kids.length !== 1) { return; }
        var kid = kids[0].querySelector('a');
        var own = li.querySelector(':scope > a');
        if (!kid || !own || !kid.getAttribute('href')) { return; }
        var kidText = (kid.textContent || '').trim();
        if (!kidText) { return; }
        own.setAttribute('href', kid.getAttribute('href'));
        var ownSpan = own.querySelector('.jsst_text');
        if (ownSpan) { ownSpan.textContent = kidText; }
        own.setAttribute('title', kidText);
        if (kids[0].classList.contains('active')) { li.classList.add('active'); }
        li.classList.remove('treeview');
        li.classList.add('jsst-nav-flat');
        sub.parentNode.removeChild(sub);
    });

    // --- Accordion Menu Logic ---
    document.querySelectorAll('.jsstadmin-sidebar-menu .treeview > a').forEach(item => {
        item.addEventListener('click', event => {
            const parentLi = item.parentElement;
            // Prevent default link behavior only if there is a submenu to toggle
            if (parentLi.querySelector('.jsstadmin-sidebar-submenu .treeview-menu')) {
                event.preventDefault();
            }

            const menu = document.getElementById('jsstadmin-leftmenu');

            // Do not allow opening accordion if menu is collapsed
            if (menu.classList.contains('menu-collapsed')) {
                return;
            }

            // Prevent interaction with disabled items
            if (parentLi.classList.contains('disabled-menu')) {
                event.preventDefault();
                return;
            }

            // Toggle active class
            if (parentLi.classList.contains('active')) {
                parentLi.classList.remove('active');
            } else {
                // One group open at a time. This line was written and left
                // commented; with eleven sections and forty-five groups,
                // letting every group stay open is how the menu reached three
                // screens tall. The group holding the current page keeps its
                // place - closing what you are looking at to open something
                // else is not a trade anybody asked for.
                document.querySelectorAll('.jsstadmin-sidebar-menu .treeview.active').forEach(function (el) {
                    if (el !== parentLi && !el.classList.contains('jsst-nav-here')) {
                        el.classList.remove('active');
                    }
                });
                parentLi.classList.add('active');
            }
        });
    });

    // --- Expand/Collapse Menu Logic ---
    const toggleButton = document.getElementById('jsstadmin-menu-toggle');
    const logoLink = document.querySelector('#jsstadmin-logo .jsst-anchor'); // Select the logo link
    const menu = document.getElementById('jsstadmin-leftmenu');
    const body = document.body;

    // Create a reusable function to toggle the menu state
    const toggleMenu = (event) => {
        // Prevent the default link behavior for the logo click
        event.preventDefault();

        menu.classList.toggle('menu-collapsed');
        body.classList.toggle('menu-collapsed');

        // Remember it. The cookie was written and read by the block at the top
        // of this file, whose wp_add_inline_script is commented out - so the
        // collapse has never survived a refresh. This is the live handler.
        if (menu.classList.contains('menu-collapsed')) {
            document.cookie = 'jsst_collapse_admin_menu=1; expires=Sat, 01 Jan 2050 00:00:00 UTC; path=/';
        } else {
            document.cookie = 'jsst_collapse_admin_menu=0; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        }

        // Close any open submenus when collapsing the main menu
        if (menu.classList.contains('menu-collapsed')) {
            document.querySelectorAll('.jsstadmin-sidebar-menu .treeview.active').forEach(el => el.classList.remove('active'));
        }
    };

    // Attach the event listener to both the button and the logo
    toggleButton.addEventListener('click', toggleMenu);
    logoLink.addEventListener('click', toggleMenu);

    // --- Sections, folding, and finding ---
    // Seventeen headings only fit because a folded section costs one row. One
    // section is open at a time; the one holding the current page opens on
    // arrival, and whatever you open last is remembered, so the menu you come
    // back to is the menu you left.
    //
    // Fold state and filter state are decided in one place - render() - rather
    // than each writing style.display behind the other's back, which is how a
    // filtered row ends up hidden by a fold it does not belong to.
    (function () {
        var list = document.querySelector('.jsstadmin-sidebar-menu');
        if (!list) { return; }
        var box  = document.getElementById('jsst-navfind');
        var none = document.querySelector('.jsst-navfind-none');
        var rows = Array.prototype.slice.call(list.children);
        var KEY  = 'jsst_nav_section';

        // Number every row with the section it belongs to.
        var sections = [], cursor = -1;
        rows.forEach(function (row) {
            if (row.classList.contains('menu-header')) {
                cursor++;
                row.setAttribute('data-jsst-sec', cursor);
                row.setAttribute('role', 'button');
                row.setAttribute('tabindex', '0');
                sections.push({ head: row, items: [] });
            } else if (cursor > -1) {
                row.setAttribute('data-jsst-sec', cursor);
                sections[cursor].items.push(row);
            }
        });
        if (!sections.length) { return; }

        // Open the section holding the current page; else the remembered one;
        // else the first.
        // Nothing highlighted at all? Several groups only mark themselves
        // active for particular layouts - Knowledge Base wants listarticles or
        // addarticle - so a bare page= address highlights nothing, and a bare
        // page= address is exactly what this menu's own links and WordPress's
        // menu both use. Rather than correct forty-three hand-written
        // conditions, match the address against the menu's own hrefs: an exact
        // page+layout hit beats a page-only hit, which beats nothing.
        if (!list.querySelector('li.active')) {
            var params = new URLSearchParams(window.location.search);
            var wantPage = params.get('page');
            var wantLay = params.get('jstlay') || '';
            if (wantPage) {
                var best = null, bestScore = -1;
                Array.prototype.forEach.call(list.querySelectorAll('a[href]'), function (a) {
                    var raw = a.getAttribute('href');
                    // A group's own anchor is a bare hash, and a hash resolves
                    // against the CURRENT address - so every one of them looked
                    // like a perfect match for wherever you already were, and
                    // the first in the menu won. Only real destinations count.
                    if (!raw || raw.charAt(0) === '#' || raw.indexOf('javascript:') === 0) { return; }
                    var u;
                    try { u = new URL(raw, window.location.href); } catch (e) { return; }
                    if (u.searchParams.get('page') !== wantPage) { return; }
                    var lay = u.searchParams.get('jstlay') || '';
                    var score = (lay === wantLay) ? 2 : (lay === '' ? 1 : 0);
                    if (score > bestScore) { bestScore = score; best = a; }
                });
                if (best) {
                    var ownLi = best.closest('li');
                    if (ownLi) { ownLi.classList.add('active'); }
                    var topLi = best.closest('.jsstadmin-sidebar-menu > li');
                    if (topLi) { topLi.classList.add('active'); }
                }
            }
        }

        // Two different things get remembered here, and only one of them is an
        // instruction.
        //
        //   Closing everything is deliberate: -1 is a real answer, it survives
        //   refreshes and navigation, and nothing reopens until a heading is
        //   clicked.
        //
        //   Leaving some other section open is not deliberate, it is residue.
        //   Carrying it across a navigation meant arriving at Priorities with
        //   Customers expanded and the section you were actually in shut -
        //   which reads as a bug even though it was doing as it was told.
        //
        // So: an explicit close wins over everything; otherwise the page you
        // are on decides, and the stored value is only the fallback for a page
        // that belongs to no section.
        var stored = null;
        try {
            var raw = window.localStorage.getItem(KEY);
            if (raw !== null) {
                var saved = parseInt(raw, 10);
                if (!isNaN(saved) && saved >= -1 && saved < sections.length) { stored = saved; }
            }
        } catch (e) {}

        var open;
        if (stored === -1) {
            open = -1;
        } else {
            var active = list.querySelector('li.active[data-jsst-sec]');
            if (active) {
                open = parseInt(active.getAttribute('data-jsst-sec'), 10) || 0;
            } else {
                open = (stored === null) ? 0 : stored;
            }
        }

        var hereRow = list.querySelector('li.active[data-jsst-sec]');
        var hereSection = hereRow ? parseInt(hereRow.getAttribute('data-jsst-sec'), 10) : -1;

        var render = function () {
            var q = box ? box.value.trim().toLowerCase() : '';
            list.classList.toggle('jsst-nav-filtering', q !== '');
            var shown = 0;
            sections.forEach(function (sec, n) {
                var hits = 0;
                sec.items.forEach(function (row) {
                    var show;
                    if (q) {
                        show = (row.textContent || '').toLowerCase().indexOf(q) !== -1;
                    } else {
                        show = (n === open);
                    }
                    row.style.display = show ? '' : 'none';
                    if (show && q) { hits++; }
                });
                // While filtering, a heading with nothing under it goes too.
                sec.head.style.display = (q && !hits) ? 'none' : '';
                sec.head.classList.toggle('jsst-sec-open', !q && n === open);
                sec.head.setAttribute('aria-expanded', (!q && n === open) ? 'true' : 'false');
                // Closed stays closed - but the section holding the page you
                // are on still says so, or closing everything costs you the
                // only answer to where am I.
                sec.head.classList.toggle('jsst-sec-here', !q && n === hereSection && n !== open);
                shown += hits;
            });
            if (none) { none.hidden = !(q && shown === 0); }
        };

        var toggle = function (n) {
            open = (open === n) ? -1 : n;
            try { window.localStorage.setItem(KEY, open); } catch (e) {}
            render();
        };

        sections.forEach(function (sec, n) {
            sec.head.addEventListener('click', function () {
                // In the collapsed rail the children are not on screen, so a
                // fold would toggle something invisible. Open the sidebar on
                // that subject instead.
                var rail = document.getElementById('jsstadmin-leftmenu');
                if (rail && rail.classList.contains('menu-collapsed')) {
                    rail.classList.remove('menu-collapsed');
                    document.body.classList.remove('menu-collapsed');
                    document.cookie = 'jsst_collapse_admin_menu=0; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
                    open = n;
                    try { window.localStorage.setItem(KEY, open); } catch (e) {}
                    render();
                    return;
                }
                toggle(n);
            });
            sec.head.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(n); }
            });
        });

        if (box) {
            box.addEventListener('input', render);
            // Escape clears, which is what a filter box is expected to do.
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && box.value !== '') {
                    e.stopPropagation();
                    box.value = '';
                    render();
                }
            });
            // '/' focuses the filter from anywhere, unless you are typing.
            document.addEventListener('keydown', function (e) {
                if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) { return; }
                var t = e.target, tag = t && t.tagName;
                if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (t && t.isContentEditable)) { return; }
                e.preventDefault();
                box.focus();
                box.select();
            });
        }

        render();
    })();

    // The group holding the current page, marked so the group-level accordion
    // leaves it open when another group is opened. `li.active`, not
    // `li.treeview.active`: it may have been flattened into a plain link a
    // moment ago, and it is still the group you are in.
    var here = document.querySelector('.jsstadmin-sidebar-menu > li.active');
    if (here) { here.classList.add('jsst-nav-here'); }
";
wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
?>
