<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * Reports — the pieces every report screen is made of. (Design system migration)
 *
 * Four screens drew the same three things by hand: a row of donut tiles, a
 * titled section, and a person's name beside their figures. The donut itself
 * is NOT rewritten - it is a pure-CSS component in status_graph.css, 101
 * progress steps of rotation, and it works. What was wrong was everything
 * around it: `js-admin-subtitle` is a #32373c charcoal bar, the tiles are
 * square and shadowless, and the section wrapper is a bare white float.
 *
 * `js-ticket-count` and its tile chrome are deliberately NOT reused here.
 * They are shared with the front-end control panel and with admin_tickets,
 * which is under a standing do-not-edit rule, so restyling them would reach
 * screens this change has no business touching. The donut's INNER markup is
 * kept exactly; only the containers around it are new, and the sizing those
 * containers used to supply is re-declared against `.jsst-dial`.
 */

if (!function_exists('jsst_report_section_open')) {

    /**
     * A titled block. Replaces `js-admin-report` + the charcoal
     * `js-admin-subtitle` with an ordinary card and card head.
     */
    function jsst_report_section_open($jsst_title, $jsst_args = array()) {
        $jsst_args = wp_parse_args($jsst_args, array('half' => false, 'sub' => '', 'tools' => ''));
        ?>
        <div class="jsst-card<?php echo $jsst_args['half'] ? ' jsst-card-half' : ''; ?>">
            <div class="jsst-card-head">
                <h2 class="jsst-card-title"><?php echo esc_html($jsst_title); ?></h2>
                <?php if ($jsst_args['tools'] !== '') { ?><div class="jsst-card-tools"><?php echo wp_kses_post($jsst_args['tools']); ?></div><?php } ?>
                <?php if ($jsst_args['sub'] !== '') { ?><p class="jsst-card-sub"><?php echo esc_html($jsst_args['sub']); ?></p><?php } ?>
            </div>
            <div class="jsst-card-body">
        <?php
    }

    function jsst_report_section_close() {
        ?>
            </div>
        </div>
        <?php
    }

    /**
     * A chart's container. Google Charts draws an SVG sized to whatever the
     * container measured at draw time, so the only thing this has to do is be
     * the right width and not be told a pixel height in a style attribute.
     */
    function jsst_report_graph($jsst_id, $jsst_height = 400) {
        ?>
        <div class="jsst-chart" id="<?php echo esc_attr($jsst_id); ?>" style="height:<?php echo (int) $jsst_height; ?>px"></div>
        <?php
    }

    /**
     * One donut tile. `$jsst_fill` is the existing colour class that paints
     * the ring (js-ticket-open, js-ticket-close, ...); `$jsst_tone` is the
     * existing text colour class (js-ticket-green, js-ticket-red, ...). Both
     * are kept because they are what status_graph.css and admincss.css
     * already colour, and because the ring colour carries the meaning.
     *
     * The count now sits inside the ring. The old tile put it in the label as
     * "Open ( 6 )" and left the middle of the donut empty, which is the one
     * place on the tile the eye already goes.
     */
    function jsst_report_tile($jsst_args) {
        $jsst_args = wp_parse_args($jsst_args, array(
            'pct' => 0, 'fill' => '', 'tone' => '', 'count' => 0,
            'label' => '', 'title' => '', 'href' => '', 'tab' => '', 'small' => false,
        ));
        $jsst_pct = max(0, min(100, (int) $jsst_args['pct']));
        $jsst_tag = $jsst_args['href'] !== '' ? 'a' : 'span';
        ?>
        <<?php echo esc_html($jsst_tag); ?> class="jsst-stat<?php echo $jsst_args['small'] ? ' jsst-stat-sm' : ''; ?>"
            <?php if ($jsst_args['href'] !== '') { ?>href="<?php echo esc_url($jsst_args['href']); ?>"<?php } ?>
            <?php if ($jsst_args['tab'] !== '') { ?>data-tab-number="<?php echo esc_attr($jsst_args['tab']); ?>"<?php } ?>
            title="<?php echo esc_attr($jsst_args['title'] !== '' ? $jsst_args['title'] : $jsst_args['label']); ?>">
            <span class="jsst-dial">
                <?php /* The donut, exactly as status_graph.css expects it. */ ?>
                <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_pct); ?>">
                    <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_pct); ?>">
                        <div class="circle">
                            <div class="mask full"><div class="fill <?php echo esc_attr($jsst_args['fill']); ?>"></div></div>
                            <div class="mask half">
                                <div class="fill <?php echo esc_attr($jsst_args['fill']); ?>"></div>
                                <div class="fill fix"></div>
                            </div>
                            <div class="shadow"></div>
                        </div>
                        <div class="inset"><span class="jsst-dial-num"><?php echo esc_html(number_format_i18n((int) $jsst_args['count'])); ?></span></div>
                    </div>
                </div>
            </span>
            <span class="jsst-stat-label <?php echo esc_attr($jsst_args['tone']); ?>"><?php echo esc_html($jsst_args['label']); ?></span>
            <span class="jsst-stat-pct"><?php
                /* translators: %s is a percentage of all tickets. */
                echo esc_html(sprintf(__('%s%% of all', 'js-support-ticket'), number_format_i18n($jsst_pct))); ?></span>
        </<?php echo esc_html($jsst_tag); ?>>
        <?php
    }

    /**
     * Percentages for the five standing figures, worked out once. Every
     * report screen recomputed these inline and two of them disagreed about
     * what to do when the total is zero.
     */
    function jsst_report_percents($jsst_totals) {
        $jsst_all = isset($jsst_totals['allticket']) ? (int) $jsst_totals['allticket'] : 0;
        $jsst_pc = function ($jsst_key) use ($jsst_totals, $jsst_all) {
            if ($jsst_all === 0 || empty($jsst_totals[$jsst_key])) return 0;
            return (int) round(((int) $jsst_totals[$jsst_key] / $jsst_all) * 100);
        };
        return array(
            'open'     => $jsst_pc('openticket'),
            'answered' => $jsst_pc('answeredticket'),
            'overdue'  => $jsst_pc('overdueticket'),
            'closed'   => $jsst_pc('closeticket'),
            'all'      => $jsst_all === 0 ? 0 : 100,
        );
    }

    /**
     * The JS that keeps a Google chart the width of its container.
     *
     * Google Charts measures the container once, at draw time, and never
     * again - so a window resized after the page loaded left a 734px chart in
     * a 600px card and the whole page scrolled sideways. Verified: at 1242px
     * a fresh load fits exactly, and resizing to 962px left scrollWidth at
     * 1218. Redrawing on resize is the fix; it is debounced because a drag
     * fires resize continuously and each redraw is a full re-render.
     */
    function jsst_report_redraw_js($jsst_fns) {
        $jsst_list = implode(',', array_map('esc_js', (array) $jsst_fns));
        return "
    var jsstCharts = [{$jsst_list}];
    var jsstRedrawTimer = null;
    var jsstLastWidth = jQuery(window).width();
    jQuery(window).on('resize', function () {
        if (jQuery(window).width() === jsstLastWidth) { return; }
        jsstLastWidth = jQuery(window).width();
        clearTimeout(jsstRedrawTimer);
        jsstRedrawTimer = setTimeout(function () {
            for (var i = 0; i < jsstCharts.length; i++) {
                try { jsstCharts[i](); } catch (e) {}
            }
        }, 200);
    });
";
    }

    /**
     * The ticket list the three detail screens end with. Identical on all
     * three but for the first cell, so only that is passed in.
     *
     * It was a `js-admin-report-tickets` table of bare `<tr>`s with no thead
     * and no tbody, and a hand-written `js-support-ticket-table-responsive-heading`
     * span inside every cell repeating the column name for narrow screens.
     * That span is gone: responsivetable.js already copies each column name
     * onto its cells as `data-th` and stacks the row below 600px - but it
     * reads `thead th` and iterates `tbody tr`, so a table with neither was
     * skipped and the stacking these screens needed never ran.
     */
    function jsst_report_tickets_open() {
        ?>
        <div class="jsst-table-wrap">
            <?php /* No `id="js-support-ticket-table"`. That id carries the whole
                     legacy table skin from admincss.css:386 - `tr th` with a #32373c
                     charcoal background and 20px padding on every cell - and an id
                     selector beats `.jsst-table thead th`, so the bar this migration
                     removed came straight back and the six columns of padding pushed
                     the table to 1582px inside a 1092px card. responsivetable.js
                     matches `table.jsst-table` as well as that id, so the stacking
                     survives the id going. */ ?>
            <table class="jsst-table">
                <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                        <?php if (in_array('feedback', jssupportticket::$_active_addons)) { ?>
                            <th scope="col"><?php echo esc_html(__('Rating', 'js-support-ticket')); ?></th>
                        <?php }
                        if (in_array('timetracking', jssupportticket::$_active_addons)) { ?>
                            <th scope="col"><?php echo esc_html(__('Time Taken', 'js-support-ticket')); ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
        <?php
    }

    function jsst_report_tickets_close() {
        ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Status, priority, created, and the two optional columns. The colours
     * are the ones configured per status and per priority in the database,
     * so they are inline on the element - there is no class that can carry a
     * value the administrator chose.
     */
    function jsst_report_ticket_cells($jsst_ticket) {
        if (!in_array($jsst_ticket->status, array(5, 6)) && $jsst_ticket->isoverdue == 1) {
            $jsst_status  = __('Overdue', 'js-support-ticket');
            $jsst_fg      = '#FFFFFF';
            $jsst_bg      = '#DB624C';
        } else {
            $jsst_status  = $jsst_ticket->statustitle;
            $jsst_fg      = $jsst_ticket->statuscolour;
            $jsst_bg      = $jsst_ticket->statusbgcolour;
        }
        ?>
        <td><span class="jsst-pill" style="background:<?php echo esc_attr($jsst_bg); ?>;color:<?php echo esc_attr($jsst_fg); ?>"><?php echo esc_html($jsst_status); ?></span></td>
        <?php /* `->priority`, not `->prioritytitle`. The query aliases the
                 status name to `statustitle` but selects the priority name as
                 plain `priority`, so the two columns of this one row are not
                 named alike (model.php: `priority.priority, ... status.status
                 AS statustitle`). */ ?>
        <td><span class="jsst-pill" style="background:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>;color:#fff"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span></td>
        <td><span class="jsst-table-sub"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], strtotime($jsst_ticket->created))); ?></span></td>
        <?php if (in_array('feedback', jssupportticket::$_active_addons)) { ?>
            <td><?php if ($jsst_ticket->rating > 0) { ?>
                    <span class="jsst-score-num"><?php echo esc_html($jsst_ticket->rating); ?></span><span class="jsst-score-of"><?php
                        /* translators: %s is the highest possible rating. */
                        echo ' ' . esc_html(sprintf(__('Out of %s', 'js-support-ticket'), number_format_i18n(5))); ?></span>
                <?php } else { ?>
                    <span class="jsst-table-sub"><?php echo esc_html(__('NA', 'js-support-ticket')); ?></span>
                <?php } ?></td>
        <?php }
        if (in_array('timetracking', jssupportticket::$_active_addons)) {
            $jsst_h = floor($jsst_ticket->time / 3600);
            $jsst_m = floor(floor($jsst_ticket->time / 60) % 60);
            $jsst_s = floor($jsst_ticket->time % 60); ?>
            <td><span class="jsst-table-sub"><?php echo esc_html(sprintf('%02d:%02d:%02d', $jsst_h, $jsst_m, $jsst_s)); ?></span></td>
        <?php }
    }

    /** A person inside a ticket row: avatar, who, and what they asked. */
    function jsst_report_ticket_person($jsst_ticket) {
        ?>
        <span class="jsst-repwho">
            <span class="jsst-repavatar"><?php echo wp_kses(jsst_get_avatar($jsst_ticket->uid), JSST_ALLOWED_TAGS); ?></span>
            <span>
                <span class="jsst-repname"><?php echo esc_html($jsst_ticket->name); ?></span>
                <span class="jsst-repsub"><?php echo esc_html($jsst_ticket->subject); ?></span>
                <span class="jsst-repsub"><?php echo esc_html($jsst_ticket->email); ?></span>
            </span>
        </span>
        <?php
    }
}
