<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The customers screen's body, rendered identically in both workspaces.
 * (Roadmap 4.5-FE-02)
 *
 * Included by admin_customers.php and customers.php, which supply their own
 * chrome and nothing else. It is a template rather than a method on
 * JSSTnavigation because this screen is a form and a table with paging - the
 * kind of thing that gets restyled per shell - while the navigation strip and
 * the home panels are the same six blocks everywhere and are printed by the
 * class.
 *
 * A customer here is an e-mail address: a guest ticket has no account behind
 * it, so the address is the only identifier every ticket carries. That has one
 * visible consequence, and it is left visible rather than papered over - a
 * customer with no account has no history link, because the history query is
 * asked by account id and there is nothing to ask it with.
 */
$jsst_shell = JSSTnavigation::shell();
$jsst_list = isset(jssupportticket::$jsst_data['customerlist']) ? jssupportticket::$jsst_data['customerlist'] : array();
$jsst_companies = isset(jssupportticket::$jsst_data['customercompanies']) ? jssupportticket::$jsst_data['customercompanies'] : array();
$jsst_search = isset(jssupportticket::$jsst_data['customersearch']) ? jssupportticket::$jsst_data['customersearch'] : '';
$jsst_company = isset(jssupportticket::$jsst_data['customercompany']) ? jssupportticket::$jsst_data['customercompany'] : '';
$jsst_start = isset(jssupportticket::$jsst_data['customerstart']) ? (int) jssupportticket::$jsst_data['customerstart'] : 0;
$jsst_who = isset(jssupportticket::$jsst_data['customerwho']) ? (int) jssupportticket::$jsst_data['customerwho'] : 0;
$jsst_history = isset(jssupportticket::$jsst_data['customerhistory']) ? jssupportticket::$jsst_data['customerhistory'] : array();
$jsst_rows = (isset($jsst_list['rows']) && is_array($jsst_list['rows'])) ? $jsst_list['rows'] : array();
$jsst_total = isset($jsst_list['total']) ? (int) $jsst_list['total'] : 0;
$jsst_limit = isset($jsst_list['limit']) ? (int) $jsst_list['limit'] : 25;
$jsst_dateformat = isset(jssupportticket::$_config['date_format']) ? jssupportticket::$_config['date_format'] : 'Y-m-d';
$jsst_base = JSSTnavigation::url(JSSTnavigation::CUSTOMERS, $jsst_shell);
?>
<div class="jsst-people">

    <p class="jsst-people-lede">
        <?php echo esc_html(__('Everybody whose tickets you can see, and the companies they write in from. The list is scoped exactly as your queue is - it cannot show you a customer whose tickets you could not open.', 'js-support-ticket')); ?>
    </p>

    <form class="jsst-people-search" method="get" action="<?php echo esc_url($jsst_base); ?>">
        <?php
        /* Every route argument re-emitted as a hidden field. A GET form throws
           away whatever was in the action's query string, which on the backend
           desk is the page= that says which screen this is. */
        $jsst_route = JSSTnavigation::sections();
        $jsst_route = isset($jsst_route[JSSTnavigation::CUSTOMERS]['routes'][$jsst_shell])
            ? $jsst_route[JSSTnavigation::CUSTOMERS]['routes'][$jsst_shell] : array();
        foreach ($jsst_route AS $jsst_key => $jsst_value) { ?>
            <input type="hidden" name="<?php echo esc_attr($jsst_key); ?>" value="<?php echo esc_attr($jsst_value); ?>">
        <?php } ?>
        <?php if ($jsst_company !== '') { ?>
            <input type="hidden" name="company" value="<?php echo esc_attr($jsst_company); ?>">
        <?php } ?>
        <label class="jsst-people-label" for="jsst-people-term"><?php echo esc_html(__('Find a customer', 'js-support-ticket')); ?></label>
        <input id="jsst-people-term" type="text" name="search" value="<?php echo esc_attr($jsst_search); ?>" placeholder="<?php echo esc_attr(__('Name or e-mail address', 'js-support-ticket')); ?>">
        <button type="submit" class="jsst-people-go"><?php echo esc_html(__('Search', 'js-support-ticket')); ?></button>
        <?php if ($jsst_search !== '' || $jsst_company !== '') { ?>
            <a class="jsst-people-clear" href="<?php echo esc_url($jsst_base); ?>"><?php echo esc_html(__('Clear', 'js-support-ticket')); ?></a>
        <?php } ?>
    </form>

    <?php
    /* The rail is records first and derived domains after, and a desk with no
       records gets exactly the derived list v4.5 drew. The two are labelled
       differently on purpose: "Acme Holdings Ltd" and "acme.example" are
       different kinds of claim, and letting them look the same is how somebody
       comes to believe they have set up an account they have not.
       (Roadmap 5.5-COM-06) */
    $jsst_rail = class_exists('JSSTcompanies')
        ? JSSTcompanies::rail(isset($jsst_companies['rows']) ? $jsst_companies['rows'] : array(), 12)
        : array();
    $jsst_named = false;
    foreach ($jsst_rail AS $jsst_railrow) {
        if (empty($jsst_railrow['derived'])) { $jsst_named = true; break; }
    }
    ?>
    <?php if (!empty($jsst_rail)) { ?>
        <h2 class="jsst-people-group"><?php echo esc_html(__('Companies', 'js-support-ticket')); ?></h2>
        <p class="jsst-people-note">
            <?php echo esc_html($jsst_named
                ? __('The companies written down on this desk, and after them the e-mail domains everybody else writes in from. A written-down company can be renamed, can name a contractor on a personal address as one of its people, and can hold a contract; a domain is only a grouping.', 'js-support-ticket')
                : __('Grouped by the part of the address after the @, because nobody has written a company down on this desk yet. It is the grouping you already make in your head when three tickets arrive from the same firm - and it puts everybody on a public mail provider into one very large row, which is why it is a reading aid rather than a customer database.', 'js-support-ticket')); ?>
        </p>
        <ul class="jsst-people-companies">
            <?php foreach ($jsst_rail AS $jsst_row) {
                $jsst_pick = $jsst_row['derived'] ? $jsst_row['company'] : ('co:' . $jsst_row['id']);
                $jsst_on = (strtolower($jsst_company) === strtolower($jsst_pick)); ?>
                <li class="jsst-people-company <?php echo $jsst_on ? 'jsst-people-company-on' : ''; ?>">
                    <a href="<?php echo esc_url(add_query_arg(array('company' => $jsst_on ? false : $jsst_pick, 'search' => ($jsst_search !== '') ? $jsst_search : false), $jsst_base)); ?>">
                        <span class="jsst-people-domain"><?php echo esc_html($jsst_row['company']); ?></span>
                        <span class="jsst-people-figures">
                            <?php
                            echo esc_html(sprintf(
                                /* translators: 1: how many people, 2: how many tickets */
                                __('%1$d people, %2$d tickets', 'js-support-ticket'),
                                (int) $jsst_row['people'], (int) $jsst_row['tickets']));
                            if ($jsst_row['derived'] && $jsst_named) {
                                echo ' — ' . esc_html(__('a domain, not a record', 'js-support-ticket'));
                            }
                            ?>
                        </span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <h2 class="jsst-people-group">
        <?php
        if ($jsst_company !== '') {
            $jsst_headname = $jsst_company;
            if (strpos($jsst_company, 'co:') === 0 && class_exists('JSSTcompanies')) {
                $jsst_headrow = JSSTcompanies::get((int) substr($jsst_company, 3));
                $jsst_headname = $jsst_headrow ? $jsst_headrow->name : $jsst_company;
            }
            /* translators: %s: a company name, or an e-mail domain standing in for one */
            echo esc_html(sprintf(__('Customers at %s', 'js-support-ticket'), $jsst_headname));
        } else {
            echo esc_html(__('Customers', 'js-support-ticket'));
        }
        ?>
    </h2>

    <?php if (empty($jsst_rows)) { ?>
        <p class="jsst-people-empty"><?php echo esc_html(__('No customers match that.', 'js-support-ticket')); ?></p>
    <?php } else { ?>
        <div class="jsst-people-wrap">
            <table class="jsst-people-table">
                <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html(__('Customer', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Company', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Tickets', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Open', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Last activity', 'js-support-ticket')); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($jsst_rows AS $jsst_row) {
                    $jsst_domain = strstr((string) $jsst_row->email, '@');
                    $jsst_domain = ($jsst_domain === false) ? '' : strtolower(substr($jsst_domain, 1));
                    /* The company they were written down as, where somebody
                       wrote one down; otherwise the domain, as before. */
                    $jsst_rowcompany = class_exists('JSSTcompanies')
                        ? JSSTcompanies::forEmail($jsst_row->email) : false;
                    ?>
                    <tr class="<?php echo ((int) $jsst_row->uid > 0 && (int) $jsst_row->uid === $jsst_who) ? 'jsst-people-on' : ''; ?>">
                        <th scope="row">
                            <?php if ((int) $jsst_row->uid > 0) { ?>
                                <a href="<?php echo esc_url(add_query_arg(array('customer' => (int) $jsst_row->uid, 'company' => ($jsst_company !== '') ? $jsst_company : false, 'search' => ($jsst_search !== '') ? $jsst_search : false), $jsst_base)); ?>">
                                    <?php echo esc_html($jsst_row->name != '' ? $jsst_row->name : $jsst_row->email); ?>
                                </a>
                            <?php } else { ?>
                                <?php echo esc_html($jsst_row->name != '' ? $jsst_row->name : $jsst_row->email); ?>
                                <span class="jsst-people-guest"><?php echo esc_html(__('no account', 'js-support-ticket')); ?></span>
                            <?php } ?>
                            <span class="jsst-people-email"><?php echo esc_html($jsst_row->email); ?></span>
                        </th>
                        <td><?php if ($jsst_rowcompany) { ?>
                                <?php echo esc_html($jsst_rowcompany->name); ?>
                                <span class="jsst-people-email"><?php echo esc_html($jsst_domain); ?></span>
                            <?php } else {
                                echo esc_html($jsst_domain);
                            } ?></td>
                        <td><?php echo esc_html($jsst_row->tickets); ?></td>
                        <td><?php echo esc_html($jsst_row->openticket); ?></td>
                        <td>
                            <?php echo ($jsst_row->lastactivity != '')
                                ? esc_html(date_i18n($jsst_dateformat, jssupportticketphplib::JSST_strtotime($jsst_row->lastactivity)))
                                : ''; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if ($jsst_total > $jsst_limit) { ?>
            <div class="jsst-people-paging">
                <?php if ($jsst_start > 0) { ?>
                    <a href="<?php echo esc_url(add_query_arg(array('start' => max(0, $jsst_start - $jsst_limit), 'company' => ($jsst_company !== '') ? $jsst_company : false, 'search' => ($jsst_search !== '') ? $jsst_search : false), $jsst_base)); ?>"><?php echo esc_html(__('Previous', 'js-support-ticket')); ?></a>
                <?php } ?>
                <span class="jsst-people-of">
                    <?php
                    echo esc_html(sprintf(
                        /* translators: 1: first row shown, 2: last row shown, 3: how many there are */
                        __('%1$d to %2$d of %3$d', 'js-support-ticket'),
                        $jsst_start + 1, min($jsst_total, $jsst_start + $jsst_limit), $jsst_total));
                    ?>
                </span>
                <?php if (($jsst_start + $jsst_limit) < $jsst_total) { ?>
                    <a href="<?php echo esc_url(add_query_arg(array('start' => $jsst_start + $jsst_limit, 'company' => ($jsst_company !== '') ? $jsst_company : false, 'search' => ($jsst_search !== '') ? $jsst_search : false), $jsst_base)); ?>"><?php echo esc_html(__('Next', 'js-support-ticket')); ?></a>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if ($jsst_who > 0 && !empty($jsst_history['rows'])) { ?>
        <h2 class="jsst-people-group"><?php echo esc_html(__('What they have asked before', 'js-support-ticket')); ?></h2>
        <ul class="jsst-people-history">
            <?php foreach ($jsst_history['rows'] AS $jsst_ticket) { ?>
                <li>
                    <a href="<?php echo esc_url(JSSTnavigation::ticketUrl((int) $jsst_ticket->id, $jsst_shell)); ?>">
                        <span class="jsst-people-ref"><?php echo esc_html($jsst_ticket->ticketid); ?></span>
                        <?php echo esc_html($jsst_ticket->subject); ?>
                    </a>
                    <span class="jsst-people-when">
                        <?php echo esc_html(date_i18n($jsst_dateformat, jssupportticketphplib::JSST_strtotime($jsst_ticket->created))); ?>
                    </span>
                </li>
            <?php } ?>
        </ul>
    <?php } elseif ($jsst_who > 0) { ?>
        <p class="jsst-people-empty"><?php echo esc_html(__('Nothing of theirs is in your scope.', 'js-support-ticket')); ?></p>
    <?php } ?>
</div>
