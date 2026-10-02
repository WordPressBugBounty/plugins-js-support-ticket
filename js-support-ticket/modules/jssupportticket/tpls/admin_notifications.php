<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The notification centre. (Roadmap 4.5-UX-02)
 *
 * What happened that concerns you, what you can do about it from here, and how
 * you want to be told next time.
 */
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <?php /* The menu this reader is entitled to, not the administration column.
             A notification is addressed to one person and every agent role has
             this screen, so hard-coding the administrator menu here handed an
             agent the whole of wp-admin's help desk menu - departments,
             configuration, e-mail templates - every link of which refuses them.
             JSSTsidemenu is the one place that knows which column belongs to
             whom. (Roadmap 4.5-UX-02) */ ?>
    <?php JSSTsidemenu::render(); ?>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Notifications', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php
            JSSTnavigation::renderNav();
            include(JSST_PLUGIN_PATH . 'modules/jssupportticket/tpls/notificationspanel.php');
            ?>
        </div>
    </div>
</div>
