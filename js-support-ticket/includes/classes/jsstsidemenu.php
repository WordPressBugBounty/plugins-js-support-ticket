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
if (class_exists('JSSTsidemenu')) {
    return;
}

/**
 * Which left menu a screen gets, in one place.
 *
 * Every admin screen opens with the same 280px container, and each template used
 * to fill it itself. That produced two different wrong answers on the screens an
 * agent can actually reach:
 *
 *   - the ticket list wrapped the include in current_user_can('jsst_support_ticket')
 *     and emitted the container regardless, so an agent got an empty white strip
 *     down the whole page;
 *   - the canned responses screens included the administrator menu with no check
 *     at all, so an agent got the full administration column — departments,
 *     configuration, e-mail templates — every link of which refuses them.
 *
 * The ticket detail screen already had this right. This is that logic, lifted out
 * so there is one implementation instead of a copy per template.
 */
class JSSTsidemenu {

    /**
     * Print the left-menu container and the menu that belongs in it.
     *
     * Administrators get the administration menu. Anybody who can work the queue
     * but is not an administrator gets the short agent menu. Anybody else gets
     * the container with the class that collapses it, so no empty strip is left
     * behind.
     */
    public static function render() {
        $jsst_admin = current_user_can(JSSTroles::CAP_ADMIN);
        $jsst_agent = !$jsst_admin && current_user_can(JSSTroles::CAP_TICKETS);
        ?>
        <div id="jsstadmin-leftmenu" class="<?php echo esc_attr($jsst_admin || $jsst_agent ? '' : 'jsstadmin-leftmenu-empty'); ?>">
            <?php
            if ($jsst_admin) {
                JSSTincluder::getClassesInclude('jsstadminsidemenu');
            } elseif ($jsst_agent) {
                JSSTincluder::getClassesInclude('jsstagentsidemenu');
            }
            ?>
        </div>
        <?php
    }

}
