<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * Help - the 49 tutorial videos, and the two ways to reach a person.
 *
 * This was 555 lines of hand-written tiles on a `jssticketadmin-help-*`
 * vocabulary of its own, each of the 49 links carrying its own copy of the
 * same `video-icon.jpg`. Fifty identical images distinguish nothing - they
 * say 'video' 49 times on a page where everything is a video - so the icon
 * is said once per group, inline, and the 49 requests are gone.
 *
 * The links are data now. A tutorial is a title and a URL; writing that as
 * markup meant 11 lines each and six near-identical section blocks, which is
 * how the Knowledge Base heading came to be built by string concatenation
 * while the other five are plain. Adding a video is now one array line.
 */
$jsst_helpgroups = array(
    array(
        'heading' => __('Tickets', 'js-support-ticket'),
        'videos'  => array(
            'https://www.youtube.com/watch?v=zmQ4bpqSYnk' => __('Ticket creation', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=Gcss-ybwiXk' => __('Visitor ticket creation', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=Yi3zPvGdGG4' => __('How to set ticket auto close', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=S7KWbUHvmmk' => __('How to reopen closed ticket', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=Z8_9tIve4Mg' => __('How to lock a ticket', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=p3vT2vhSkjk' => __('How to add private note', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=SW9b9lBthbc' => __('View ticket history', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=c7whQ6F70yM' => __('How to setup custom fields', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=CQRgkw3e5KQ' => __('Set ticket auto overdue', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=xziaXK3DKCM' => __('Manually set ticket overdue', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=HnnJTe6lYc4' => __('How to merge tickets', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=Q8GhQQmeMU4' => __('How to export tickets', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=-eh4XuDwXoY' => __('How use help topic', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=hewCQ0S37V8' => __('How to change department', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=gmI25bv5cGA' => __('How to use multi-forms', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=3ndoMZ760Fk' => __('How to paid support', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=RBbmVEkE14E' => __('How to use canned response', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=iKslva_FkTg' => __('How to add private credentials', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=yZi_BRyAQl8' => __('How to ban/unban user', 'js-support-ticket'),
        ),
    ),
    array(
        'heading' => __('Agents', 'js-support-ticket'),
        'videos'  => array(
            'https://www.youtube.com/watch?v=hOvN-_6Qf8g' => __('Agent system', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=N7JF1qEVRhQ' => __('Agent auto assign', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=ZtCivvtAURU' => __('Manually assign ticket to agent', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=1J0JSXrr1hY' => __('How to edit time', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=oSzJz9FDzsY' => __('How to use time tracking', 'js-support-ticket'),
        ),
    ),
    array(
        'heading' => __('Configurations', 'js-support-ticket'),
        'videos'  => array(
            'https://www.youtube.com/watch?v=SJjHk50buw0' => __('How to set max open ticket', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=9ORIFf6jPPg' => __('How to show counts', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=-78pMXbZy8o' => __('How to set Captcha', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=T3HRojY2UN4' => __('User options', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=Hq1UzmUqFIA' => __('How to set login redirect', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=qloE9WQM4rE' => __('How to set fields ordering', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=jyM4iW8uROY' => __('How to enable social login', 'js-support-ticket'),
        ),
    ),
    array(
        'heading' => __('Setup', 'js-support-ticket'),
        'videos'  => array(
            'https://www.youtube.com/watch?v=Honmzw892ZE' => __('How to setup', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=6qjMe1Ppbck' => __('How to enable email piping', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=4_wrnx8ka0E' => __('How to set SMTP', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=LvsrMtEqRms' => __('How to solve email notification problem', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=Nnu2iJQ99Tk' => __('How to translate', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=oOOr869FOyA' => __('How to set colors', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=mN6xsD2u2CI' => __('How to add shortcodes', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=K0K6vEANnRU' => __('How to install addons', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=sQwVewHk9Lg' => __('How to enable desktop notifications', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=qloE9WQM4rE' => __('How to set fields ordering', 'js-support-ticket'),
        ),
    ),
    array(
        'heading' => __('Knowledge Base', 'js-support-ticket') . ', ' . __('Downloads', 'js-support-ticket') . ', ' . __('Announcements', 'js-support-ticket') . ', ' . __('FAQs', 'js-support-ticket'),
        'videos'  => array(
            'https://www.youtube.com/watch?v=sQBflPjxPEw' => __('How to use knowledge base', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=6-WfiCXB0ZM' => __('How to use downloads', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=XhWXu2RlFds' => __('How to add announcement', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=lF58MTzV2aQ' => __('How to create FAQ', 'js-support-ticket'),
        ),
    ),
    array(
        'heading' => __('Misc', 'js-support-ticket'),
        'videos'  => array(
            'https://www.youtube.com/watch?v=kiNyGRqXtAs' => __('How to use email cc', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=a5eXxHLB7qU' => __('How to use internal mail', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=pdIRcBgtxjw' => __('Use front-end widgets', 'js-support-ticket'),
            'https://www.youtube.com/watch?v=t0VUBYDmKpU' => __('How to enable admin widgets', 'js-support-ticket'),
        ),
    ),
);

/* One play mark, reused. An <img> per link was 49 requests for one picture. */
$jsst_playicon = '<svg class="jsst-hub-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
                 . '<circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.6"/>'
                 . '<path d="M10 8.5l6 3.5-6 3.5z" fill="currentColor"/></svg>';
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Guides & Videos', 'js-support-ticket'),
            'crumbs' => array(array('text' => __('Plugin Support', 'js-support-ticket'), 'url' => '')),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <p class="jsst-lede">
                <?php echo esc_html(__('Short videos for everything the desk does, and a way to reach us when a video is not enough. Every link opens on YouTube in a new tab.', 'js-support-ticket')); ?>
            </p>

            <div class="jsst-cards">
                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('We are here to help you', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('JS Help Desk is a professional, simple, easy to use and complete customer support system.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <a class="jsst-btn" target="_blank" rel="noopener noreferrer" href="https://www.youtube.com/channel/UCTZ5RPtOzGcsRwRbOTjypmA"><?php echo esc_html(__('View All Videos', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('JS Help Desk Support', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__("JS Help Desk delivers timely customer support if you have any query then we're here to show you the way.", 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <a class="jsst-btn jsst-btn-primary" target="_blank" rel="noopener noreferrer" href="https://jshelpdesk.com/support/"><?php echo esc_html(__('Submit Ticket', 'js-support-ticket')); ?></a>
                    </div>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-body">
                    <?php foreach ($jsst_helpgroups AS $jsst_group) { ?>
                        <p class="jsst-groupheading"><?php echo esc_html($jsst_group['heading']); ?></p>
                        <div class="jsst-hub jsst-hub-vid">
                            <?php foreach ($jsst_group['videos'] AS $jsst_url => $jsst_title) { ?>
                                <a class="jsst-hub-item" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($jsst_url); ?>">
                                    <span class="jsst-hub-name">
                                        <?php /* Printed, not wp_kses'd. kses lowercases attribute names, so
                                                 `viewBox` becomes `viewbox` and the icon loses its
                                                 coordinate system, and `stroke-width` is not on the
                                                 allow list at all. This is a constant defined above
                                                 with nothing user-supplied in it, and it is how
                                                 layout.php prints its own icons. */ ?>
                                        <?php echo $jsst_playicon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html($jsst_title); ?>
                                    </span>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>
</div>
