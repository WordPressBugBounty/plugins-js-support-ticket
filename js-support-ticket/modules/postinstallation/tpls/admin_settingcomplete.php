<?php
if (!defined('ABSPATH')) die('Restricted Access');
/**
 * Quick setup: done. Also drawn for the old "stepfour" address, which the
 * feedback step still redirects to.
 *
 * Points on to the Setup checklist, which checks the things a settings form
 * cannot: a portal page, a sending address, an agent, a real ticket.
 */
$jsst_wizard_current = 'done';
?>
<?php /* No side menu here: setup is one focused task, and the breadcrumb is the way out. */ ?>
<div id="jsstadmin-wrapper" class="jsst-wizard-page">
    <div id="jsstadmin-data">
        <?php include __DIR__ . '/admin_wizardnav.php'; ?>
        <div id="jsstadmin-data-wrp">
            <?php $jsst_wizard_part = 'steps'; include __DIR__ . '/admin_wizardnav.php'; ?>
            <?php JSSTmessage::getMessage(); ?>
            <div class="jsst-card">
                <div class="jsst-empty">
                    <div class="jsst-wizard-donemark" aria-hidden="true">&#10003;</div>
                    <h2 class="jsst-empty-title"><?php echo esc_html(__('Your settings are saved', 'js-support-ticket')); ?></h2>
                    <p class="jsst-empty-text"><?php echo esc_html(__('Next, the Setup checklist confirms your help desk can really take a ticket: a page for customers, an address to send from, someone to answer, and one test ticket end to end.', 'js-support-ticket')); ?></p>
                    <div class="jsst-empty-act">
                        <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=setup')); ?>"><?php echo esc_html(__('Open the Setup checklist', 'js-support-ticket')); ?></a>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Go to dashboard', 'js-support-ticket')); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
