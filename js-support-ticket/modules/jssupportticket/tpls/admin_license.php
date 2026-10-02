<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * License & Add-ons. (2 October 2026)
 *
 * ---------------------------------------------------------------------------
 * WHY ONE PAGE
 * ---------------------------------------------------------------------------
 *
 * There used to be two screens: License (the key, and a table of what it
 * covers) and Install Add-ons (a second table with checkboxes), under two
 * different menus. Both answered the same question - "what do I have, and what
 * do I do next?" - and somebody who had just pasted their key had to go and
 * find the other one to get anything installed.
 *
 * Here, the key field is always on screen, and each of the nine add-ons is a
 * card that says once what state it is in and offers the one action that fits.
 * The buttons work without a page load (JSSTlicense::ajaxKey() and
 * ajaxAddon()), so the page can show each add-on being installed; without
 * JavaScript they fall back to the controller's tasks.
 *
 * GRANTED AND INSTALLED ARE STILL DIFFERENT QUESTIONS: a key can grant an
 * add-on that is not on the site, and an add-on can be on the site without
 * being granted. Each card says which.
 */

if ( ! class_exists( 'JSSTlicense' ) ) {
    echo esc_html( __( 'This screen is not available.', 'js-support-ticket' ) );
    return;
}

$jsst_state   = isset( jssupportticket::$jsst_data['licstate'] ) ? jssupportticket::$jsst_data['licstate'] : JSSTlicense::state();
$jsst_key     = isset( jssupportticket::$jsst_data['lickey'] ) ? jssupportticket::$jsst_data['lickey'] : JSSTlicense::key();
$jsst_masked  = isset( jssupportticket::$jsst_data['licmasked'] ) ? jssupportticket::$jsst_data['licmasked'] : '';
$jsst_rows    = isset( jssupportticket::$jsst_data['instrows'] ) ? (array) jssupportticket::$jsst_data['instrows'] : array();
$jsst_status  = (string) $jsst_state['status'];
$jsst_has_key = '' !== trim( $jsst_key );
$jsst_current = 'active' === $jsst_status;
$jsst_gated   = class_exists( 'JSSTlicencegate' ) ? (array) JSSTlicencegate::gated() : array();
$jsst_offer   = JSSTlicense::offer();
$jsst_free    = JSSTlicense::freeUpgradeFrom();
$jsst_can     = array(
    'install'  => current_user_can( 'install_plugins' ),
    'activate' => current_user_can( 'activate_plugins' ),
    'update'   => current_user_can( 'update_plugins' ),
);
$jsst_pricing = 'https://jshelpdesk.com/pricing/';

/*
 * How each add-on looks: an icon, a colour, and three things it does in words
 * a customer would use. The full summary stays in JSSTbundle; this is the
 * short version a card has room for.
 */
$jsst_look = array(
    'js-support-ticket-agents'        => array( 'groups', '#2563eb', '', array(
        __( 'Agent roles and teams that decide who sees what', 'js-support-ticket' ),
        __( 'Tickets assigned automatically, four ways', 'js-support-ticket' ),
        __( 'Time tracking, merging and a credential vault', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-servicelevels' => array( 'clock', '#b45309', '', array(
        __( 'Response and resolution targets on your working hours', 'js-support-ticket' ),
        __( 'Due dates, overdue warnings and escalation', 'js-support-ticket' ),
        __( 'Automation rules, auto close and recurring tickets', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-emailsuite'    => array( 'email-alt', '#0e7490', '', array(
        __( 'Turn a mailbox into tickets: IMAP, Gmail or Microsoft 365', 'js-support-ticket' ),
        __( 'Send through a real mail server', 'js-support-ticket' ),
        __( 'One email template per language', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-knowledge'     => array( 'book', '#15803d', '', array(
        __( 'A knowledge base with public and agent-only articles', 'js-support-ticket' ),
        __( 'FAQs shown before the ticket form', 'js-support-ticket' ),
        __( 'Downloads and announcements', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-aiagent'       => array( 'lightbulb', '#7c3aed', __( 'New', 'js-support-ticket' ), array(
        __( 'Suggests answers while the customer types', 'js-support-ticket' ),
        __( 'Replies from your own content, with sources', 'js-support-ticket' ),
        __( 'A review mode and one master switch', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-experience'    => array( 'format-chat', '#db2777', __( 'New: live chat', 'js-support-ticket' ), array(
        __( 'More than one ticket form', 'js-support-ticket' ),
        __( 'Live chat on your site (beta)', 'js-support-ticket' ),
        __( 'A satisfaction question when a ticket closes', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-commerce'      => array( 'cart', '#c2410c', '', array(
        __( 'WooCommerce and Easy Digital Downloads orders on the ticket', 'js-support-ticket' ),
        __( 'Envato purchase codes checked before a ticket is accepted', 'js-support-ticket' ),
        __( 'Paid support by ticket, credit or plan', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-integrations'  => array( 'admin-plugins', '#0f766e', '', array(
        __( 'A REST API and webhooks in both directions', 'js-support-ticket' ),
        __( 'Alerts in Slack, Teams, Google Chat, Telegram and Discord', 'js-support-ticket' ),
        __( 'Browser notifications and Mailchimp', 'js-support-ticket' ),
    ) ),
    'js-support-ticket-reporting'     => array( 'chart-bar', '#4f46e5', '', array(
        __( 'Analytics with scheduled XLSX and PDF exports', 'js-support-ticket' ),
        __( 'Retention rules with legal holds', 'js-support-ticket' ),
        __( 'Deletions only after approval', 'js-support-ticket' ),
    ) ),
);

/*
 * Each add-on's state, decided once, here. The card, the counts, the filter
 * and "Install all" all read this, so they cannot disagree.
 */
$jsst_cards   = array();
$jsst_running = 0;
$jsst_todo    = 0;

foreach ( $jsst_rows as $jsst_product => $jsst_row ) {
    $jsst_ver = $jsst_row['installed'] ? JSSTlicense::versionState( $jsst_product ) : null;

    if ( ! $jsst_has_key ) {
        $jsst_st = $jsst_row['installed'] ? 'waiting' : 'locked';
    } elseif ( $jsst_row['installed'] && isset( $jsst_gated[ $jsst_product ] ) ) {
        $jsst_st = 'gated';
    } elseif ( $jsst_row['active'] ) {
        if ( null !== $jsst_ver && 'ready' === $jsst_ver['state'] && $jsst_can['update'] ) {
            $jsst_st = 'update';
        } else {
            $jsst_st = $jsst_current ? 'active' : 'paused';
        }
    } elseif ( $jsst_row['installed'] ) {
        $jsst_st = 'off';
    } elseif ( ! $jsst_row['granted'] ) {
        $jsst_st = 'notplan';
    } elseif ( ! $jsst_current ) {
        $jsst_st = 'renew';
    } else {
        $jsst_st = $jsst_can['install'] ? 'none' : 'noperm';
    }

    if ( in_array( $jsst_st, array( 'active', 'paused', 'update' ), true ) ) {
        ++$jsst_running;
    }
    if ( in_array( $jsst_st, array( 'none', 'off', 'update' ), true ) ) {
        ++$jsst_todo;
    }

    $jsst_cards[ $jsst_product ] = array(
        'row'   => $jsst_row,
        'state' => $jsst_st,
        'ver'   => $jsst_ver,
        'look'  => isset( $jsst_look[ $jsst_product ] ) ? $jsst_look[ $jsst_product ] : array( 'admin-plugins', '#4f46e5', '', array() ),
    );
}
$jsst_total = count( $jsst_cards );

/*
 * The state of the licence, said once at the top: a head, a chip and a
 * sentence. With a key, JSSTlicense::notice() decides first - the same answer
 * the banner on every other admin screen gives, so this page and that banner
 * cannot say two different things. The banner stands down on this page.
 */
$jsst_notice = $jsst_has_key ? JSSTlicense::notice() : null;
$jsst_until  = '' !== $jsst_free ? JSSTlicense::freeUpgradeUntil() : '';
$jsst_offer_line = $jsst_offer ? sprintf(
    /* translators: 1: a date; 2: the loyalty price, e.g. $79; 3: the regular price, e.g. $99; 4: a percentage */
    __( 'From %1$s your subscription renews at %2$s a year instead of %3$s - a %4$d%% loyalty discount that stays for as long as you remain subscribed. You do not need to do anything.', 'js-support-ticket' ),
    JSSTlicense::offerDate( $jsst_offer ),
    $jsst_offer['price_text'],
    $jsst_offer['regular_text'],
    (int) $jsst_offer['percent']
) : '';

if ( ! $jsst_has_key ) {
    $jsst_tone = 'free';
    $jsst_chip = __( 'Free', 'js-support-ticket' );
    $jsst_head = __( 'You are using the free help desk', 'js-support-ticket' );
    $jsst_body = esc_html( array() !== $jsst_gated || count( JSSTlicense::installed() ) > 0
        ? __( 'No license key on this site yet. The add-ons installed here stay switched off until a license is activated - paste your key from your purchase email below. Development and staging copies are free to activate.', 'js-support-ticket' )
        : __( 'Paste your license key below to unlock the nine add-ons. Your tickets and settings stay as they are.', 'js-support-ticket' ) );
} elseif ( array() !== $jsst_gated ) {
    $jsst_tone = 'bad';
    $jsst_chip = __( 'Needs attention', 'js-support-ticket' );
    $jsst_head = sprintf(
        /* translators: %d: number of add-ons */
        _n( '%d add-on is installed but switched off on this site.', '%d add-ons are installed but switched off on this site.', count( $jsst_gated ), 'js-support-ticket' ),
        count( $jsst_gated )
    );
    $jsst_body = esc_html( 'other_site' === JSSTlicencegate::reason()
        ? __( 'This site\'s address has changed since the license was activated here. Use Check again below to activate it at this address.', 'js-support-ticket' )
        : ( 'not_included' === JSSTlicencegate::reason()
            ? __( 'Your license does not include them. Use Check again below if you have just changed plan.', 'js-support-ticket' )
            : __( 'This site has not confirmed its license with the license server since the add-ons were updated. Use Check again below; it takes a second.', 'js-support-ticket' ) ) );
} elseif ( null !== $jsst_notice ) {
    $jsst_tone = 'urgent' === $jsst_notice['level'] ? 'bad' : 'warn';
    $jsst_chip = in_array( $jsst_status, array( 'expired', 'suspended' ), true ) ? __( 'Expired', 'js-support-ticket' ) : __( 'Needs attention', 'js-support-ticket' );
    $jsst_head = (string) $jsst_notice['head'];
    $jsst_body = wp_kses_post( $jsst_notice['body'] );
} elseif ( $jsst_current && '' !== $jsst_free ) {
    $jsst_tone = 'info';
    $jsst_chip = $jsst_offer
        ? sprintf(
            /* translators: %d: a percentage, e.g. 20 */
            __( '%d%% loyalty discount', 'js-support-ticket' ),
            (int) $jsst_offer['percent']
        )
        : __( 'Carried over', 'js-support-ticket' );
    $jsst_head = '' !== $jsst_until
        ? sprintf(
            /* translators: %s: a date */
            __( 'All nine add-ons are yours at no extra charge until %s', 'js-support-ticket' ),
            $jsst_until
        )
        : __( 'All nine add-ons are yours at no extra charge', 'js-support-ticket' );
    $jsst_body = esc_html( trim( sprintf(
        /* translators: %s: the old plan's name, e.g. Basic */
        __( 'You were on %s. Your license now includes everything in Pro.', 'js-support-ticket' ),
        $jsst_free
    ) . ' ' . $jsst_offer_line ) );
} elseif ( $jsst_current ) {
    $jsst_tone = 'ok';
    $jsst_chip = __( 'Active', 'js-support-ticket' );
    $jsst_head = '' !== (string) $jsst_state['plan']
        ? sprintf(
            /* translators: %s: plan name, e.g. Pro */
            __( '%s is active on this site', 'js-support-ticket' ),
            ucfirst( (string) $jsst_state['plan'] )
        )
        : __( 'Your license is active on this site', 'js-support-ticket' );
    $jsst_renews = JSSTlicense::niceDate( $jsst_state['expires_at'] );
    $jsst_body   = esc_html( trim( ( '' !== $jsst_renews
        ? sprintf(
            /* translators: %s: a date */
            __( 'Renews on %s, with updates and support.', 'js-support-ticket' ),
            $jsst_renews
        )
        : __( 'Updates and support are included.', 'js-support-ticket' ) ) . ' ' . $jsst_offer_line ) );
} else {
    $jsst_tone = 'warn';
    $jsst_chip = __( 'Not checked', 'js-support-ticket' );
    $jsst_head = __( 'This site has not heard from the license server yet', 'js-support-ticket' );
    $jsst_body = esc_html( __( 'This site has not heard from the license server yet. Use Check again below to ask.', 'js-support-ticket' ) );
}

/* Sites in use, as the server reports them: production, staging and
   development are counted separately, and only the ones the plan includes. */
$jsst_slots = (array) ( isset( $jsst_state['slots'] ) ? $jsst_state['slots'] : array() );
$jsst_kind  = (string) $jsst_state['kind'];
$jsst_kinds = array(
    'production'  => __( 'Live sites', 'js-support-ticket' ),
    'staging'     => __( 'Staging', 'js-support-ticket' ),
    'development' => __( 'Development', 'js-support-ticket' ),
);

/* Strings the page's script needs, translated here. */
$jsst_js = array(
    'ajax'  => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'jsst-lp' ),
    'total' => $jsst_total,
    't'     => array(
        'checking'   => __( 'Checking your key with jshelpdesk.com...', 'js-support-ticket' ),
        'emptykey'   => __( 'Paste the license key from your purchase email.', 'js-support-ticket' ),
        'release'    => __( 'Release the license from this site? The add-ons stop getting updates here until you add the key again. Nothing is deleted.', 'js-support-ticket' ),
        'install'    => __( 'Downloading and installing...', 'js-support-ticket' ),
        'activate'   => __( 'Switching on...', 'js-support-ticket' ),
        'update'     => __( 'Updating...', 'js-support-ticket' ),
        /* translators: %s: version number */
        'active'     => __( 'Active · %s', 'js-support-ticket' ),
        'activeonly' => __( 'Active', 'js-support-ticket' ),
        'again'      => __( 'Try again', 'js-support-ticket' ),
        'off'        => __( 'Installed, switched off', 'js-support-ticket' ),
        'switchon'   => __( 'Switch on', 'js-support-ticket' ),
        'failed'     => __( 'The connection dropped. Reload the page and try again.', 'js-support-ticket' ),
        /* translators: %s: add-on name */
        'saydone'    => __( '%s is installed and switched on.', 'js-support-ticket' ),
        /* translators: %s: add-on name */
        'sayfail'    => __( '%s could not be installed.', 'js-support-ticket' ),
        /* translators: 1: add-ons switched on, 2: add-ons in all */
        'sum'        => __( '%1$s of %2$s active', 'js-support-ticket' ),
        /* translators: %s: number of add-ons that need an action */
        'sumtodo'    => __( '%s waiting for you', 'js-support-ticket' ),
        'all'        => __( 'Install all', 'js-support-ticket' ),
        /* translators: %s: number of add-ons */
        'rest'       => __( 'Finish the remaining %s', 'js-support-ticket' ),
        /* translators: %s: number of add-ons */
        'tabactive'  => __( 'Active (%s)', 'js-support-ticket' ),
        /* translators: %s: number of add-ons */
        'tabtodo'    => __( 'To do (%s)', 'js-support-ticket' ),
    ),
);

$jsst_task = function ( $jsst_task, $jsst_nonce, $jsst_extra = '' ) {
    return wp_nonce_url( admin_url( 'admin.php?page=jssupportticket&task=' . $jsst_task . '&action=jstask' . $jsst_extra ), $jsst_nonce );
};

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude( 'jsstadminsidemenu' ); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader( array(
            'title' => __( 'License & Add-ons', 'js-support-ticket' ),
        ) ); ?>
        <div id="jsstadmin-data-wrp" class="jsst-lp" data-lp="<?php echo esc_attr( wp_json_encode( $jsst_js ) ); ?>">

            <section class="jsst-lp-box jsst-lp-hero is-<?php echo esc_attr( $jsst_tone ); ?>" aria-labelledby="jsst-lp-head">
                <div class="jsst-lp-hero-top">
                    <svg class="jsst-lp-ring" width="76" height="76" viewBox="0 0 84 84" role="img"
                         aria-label="<?php echo esc_attr( sprintf( $jsst_js['t']['sum'], number_format_i18n( $jsst_running ), number_format_i18n( $jsst_total ) ) ); ?>">
                        <circle cx="42" cy="42" r="34" class="jsst-lp-ring-bg"/>
                        <circle cx="42" cy="42" r="34" class="jsst-lp-ring-fg" data-count="<?php echo esc_attr( $jsst_running ); ?>"
                                style="stroke-dashoffset:<?php echo esc_attr( $jsst_total ? round( 213.6 * ( 1 - $jsst_running / $jsst_total ), 1 ) : 213.6 ); ?>"/>
                        <text x="42" y="48" text-anchor="middle" class="jsst-lp-ring-text"><?php echo esc_html( $jsst_running . '/' . $jsst_total ); ?></text>
                    </svg>
                    <div class="jsst-lp-hero-text">
                        <div class="jsst-lp-hero-headline">
                            <h2 id="jsst-lp-head"><?php echo esc_html( $jsst_head ); ?></h2>
                            <span class="jsst-lp-chip is-<?php echo esc_attr( $jsst_tone ); ?>"><?php echo esc_html( $jsst_chip ); ?></span>
                        </div>
                        <p><?php echo $jsst_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?></p>
                        <?php if ( $jsst_has_key ) {
                            /* The facts, in a line: plan, date, support, AI. */
                            $jsst_facts    = array();
                            $jsst_old      = array(
                                'basic'        => __( 'Basic', 'js-support-ticket' ),
                                'standard'     => __( 'Standard', 'js-support-ticket' ),
                                'professional' => __( 'Professional', 'js-support-ticket' ),
                                'addons'       => __( 'Individual add-ons', 'js-support-ticket' ),
                            );
                            $jsst_old_tier = JSSTlicense::legacyTier();
                            if ( '' !== (string) $jsst_state['plan'] ) {
                                $jsst_facts[] = array( __( 'Plan', 'js-support-ticket' ), ucfirst( (string) $jsst_state['plan'] ) );
                            } elseif ( '' === $jsst_free && isset( $jsst_old[ $jsst_old_tier ] ) ) {
                                $jsst_facts[] = array( __( 'Plan', 'js-support-ticket' ), sprintf(
                                    /* translators: %s: the old plan's name, e.g. Professional */
                                    __( '%s (from before 5.0.0)', 'js-support-ticket' ),
                                    $jsst_old[ $jsst_old_tier ]
                                ) );
                            }
                            if ( '' !== (string) $jsst_state['expires_at'] ) {
                                $jsst_facts[] = array(
                                    in_array( $jsst_status, array( 'expired', 'suspended' ), true ) ? __( 'Ended', 'js-support-ticket' ) : __( 'Renews', 'js-support-ticket' ),
                                    JSSTlicense::niceDate( $jsst_state['expires_at'] ),
                                );
                            }
                            $jsst_plandata = ( $jsst_current && class_exists( 'JSSTplans' ) ) ? JSSTplans::currentPlan() : array();
                            if ( ! empty( $jsst_plandata['support'] ) ) {
                                $jsst_facts[] = array( __( 'Support', 'js-support-ticket' ), (string) $jsst_plandata['support'] );
                            }
                            if ( ! empty( $jsst_plandata['ai'] ) && (int) $jsst_plandata['ai'] > 0 ) {
                                $jsst_facts[] = array( __( 'AI included', 'js-support-ticket' ), sprintf(
                                    /* translators: %s: number of AI requests included each month */
                                    __( '%s requests a month, on top of your own key', 'js-support-ticket' ),
                                    number_format_i18n( (int) $jsst_plandata['ai'] )
                                ) );
                            }
                            if ( array() !== $jsst_facts ) { ?>
                                <dl class="jsst-lp-facts">
                                    <?php foreach ( $jsst_facts as $jsst_fact ) { ?>
                                        <div><dt><?php echo esc_html( $jsst_fact[0] ); ?></dt><dd><?php echo esc_html( $jsst_fact[1] ); ?></dd></div>
                                    <?php } ?>
                                </dl>
                            <?php }
                        } ?>
                    </div>
                </div>

                <div class="jsst-lp-keyarea">
                    <?php /* The key field is always here, with or without a key.
                             page, task and action ride in the URL: admin.php finds
                             the screen from $_GET['page'], and the task dispatcher
                             reads action=jstask from the query string only. */ ?>
                    <form method="post" id="jsst-lp-keyform" class="jsst-lp-keyform"
                          action="<?php echo esc_url( admin_url( 'admin.php?page=jssupportticket&task=savelicensekey&action=jstask' ) ); ?>">
                        <?php wp_nonce_field( 'jsst-lic-save' ); ?>
                        <label for="jsst-lic-key"><?php echo esc_html( __( 'License key', 'js-support-ticket' ) ); ?></label>
                        <div class="jsst-lp-keyrow">
                            <input type="text" id="jsst-lic-key" name="licensekey" value="" autocomplete="off" spellcheck="false"
                                   aria-describedby="jsst-lp-keymsg"
                                   placeholder="<?php echo esc_attr( $jsst_has_key && '' !== $jsst_masked ? $jsst_masked : __( 'Paste the key from your purchase email', 'js-support-ticket' ) ); ?>">
                            <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html( $jsst_has_key
                                ? __( 'Replace key', 'js-support-ticket' )
                                : __( 'Activate', 'js-support-ticket' ) ); ?></button>
                            <?php if ( $jsst_has_key ) { ?>
                                <a class="jsst-btn" data-lp-key="check" href="<?php echo esc_url( $jsst_task( 'rechecklicense', 'jsst-lic-recheck' ) ); ?>"><?php echo esc_html( __( 'Check again', 'js-support-ticket' ) ); ?></a>
                            <?php } ?>
                        </div>
                        <?php if ( $jsst_has_key ) { ?>
                            <p class="jsst-lp-keyhint"><?php echo esc_html( __( 'Your key is saved on this site. Paste a different key only to replace it.', 'js-support-ticket' ) ); ?></p>
                        <?php } ?>
                        <p id="jsst-lp-keymsg" class="jsst-lp-keymsg" aria-live="polite"></p>
                    </form>

                    <?php
                    $jsst_bars = array();
                    foreach ( $jsst_kinds as $jsst_k => $jsst_label ) {
                        $jsst_s = isset( $jsst_slots[ $jsst_k ] ) ? (array) $jsst_slots[ $jsst_k ] : array();
                        if ( (int) ( $jsst_s['allowed'] ?? 0 ) > 0 ) {
                            $jsst_bars[ $jsst_k ] = array( $jsst_label, (int) ( $jsst_s['used'] ?? 0 ), (int) $jsst_s['allowed'] );
                        }
                    }
                    if ( $jsst_has_key && array() !== $jsst_bars ) { ?>
                        <div class="jsst-lp-sites">
                            <?php foreach ( $jsst_bars as $jsst_k => $jsst_bar ) {
                                $jsst_pct = min( 100, (int) round( 100 * $jsst_bar[1] / max( 1, $jsst_bar[2] ) ) ); ?>
                                <div class="jsst-lp-site<?php echo $jsst_k === $jsst_kind ? ' is-here' : ''; ?>">
                                    <div class="jsst-lp-site-label">
                                        <span><?php echo esc_html( $jsst_bar[0] ); ?><?php if ( $jsst_k === $jsst_kind ) { ?> <em><?php echo esc_html( __( 'this site', 'js-support-ticket' ) ); ?></em><?php } ?></span>
                                        <span><?php echo esc_html( sprintf(
                                            /* translators: 1: sites in use, 2: sites the licence allows */
                                            __( '%1$s of %2$s', 'js-support-ticket' ),
                                            number_format_i18n( $jsst_bar[1] ),
                                            number_format_i18n( $jsst_bar[2] )
                                        ) ); ?></span>
                                    </div>
                                    <div class="jsst-lp-bar<?php echo $jsst_bar[1] >= $jsst_bar[2] ? ' is-full' : ''; ?>"><i style="width:<?php echo esc_attr( $jsst_pct ); ?>%"></i></div>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if ( 'development' === $jsst_kind ) { ?>
                            <p class="jsst-lp-keyhint"><?php echo esc_html( __( 'Local and private addresses are treated as development sites. They come with their own allowance and are not taken from the sites you paid for.', 'js-support-ticket' ) ); ?></p>
                        <?php } elseif ( 'staging' === $jsst_kind ) { ?>
                            <p class="jsst-lp-keyhint"><?php echo esc_html( __( 'Staging sites have their own allowance: one alongside each site on your plan.', 'js-support-ticket' ) ); ?></p>
                        <?php } ?>
                    <?php } ?>

                    <?php if ( $jsst_has_key ) { ?>
                        <div class="jsst-lp-keylinks">
                            <?php if ( JSSTlicense::shouldRenew() ) { ?>
                                <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url( JSSTlicense::ACCOUNT ); ?>" target="_blank" rel="noopener"><?php echo esc_html( __( 'Renew license', 'js-support-ticket' ) ); ?></a>
                            <?php } ?>
                            <a href="<?php echo esc_url( $jsst_offer && '' !== $jsst_offer['manage_url'] ? $jsst_offer['manage_url'] : JSSTlicense::ACCOUNT ); ?>" target="_blank" rel="noopener"><?php echo esc_html( __( 'Manage subscription', 'js-support-ticket' ) ); ?></a>
                            <a href="<?php echo esc_url( $jsst_pricing ); ?>" target="_blank" rel="noopener"><?php echo esc_html( __( 'Upgrade plan', 'js-support-ticket' ) ); ?></a>
                            <a class="jsst-lp-release" data-lp-key="release" href="<?php echo esc_url( $jsst_task( 'releaselicense', 'jsst-lic-release' ) ); ?>"><?php echo esc_html( __( 'Release from this site', 'js-support-ticket' ) ); ?></a>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <?php if ( ! $jsst_has_key ) { ?>
                <section class="jsst-lp-box jsst-lp-promo">
                    <div>
                        <h2><?php echo esc_html( __( 'Every plan includes all nine add-ons', 'js-support-ticket' ) ); ?></h2>
                        <p><?php echo esc_html( __( 'Plans start at $99 a year for one site. Every plan comes with a 14-day refund, and the free plugin keeps working whatever you decide.', 'js-support-ticket' ) ); ?></p>
                    </div>
                    <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url( $jsst_pricing ); ?>" target="_blank" rel="noopener"><?php echo esc_html( __( 'See plans', 'js-support-ticket' ) ); ?></a>
                </section>
            <?php } ?>

            <?php
            /* Older, separate add-ons still installed: a summary and a link to
               "Your Add-ons", which has the migration with its preview and
               undo. (Roadmap 4.5-PRO-02) */
            if ( class_exists( 'JSSTlegacy' ) && JSSTlegacy::any() ) { ?>
                <section class="jsst-lp-box jsst-lp-promo is-plain">
                    <div>
                        <h2><?php echo esc_html( __( 'You still have older, separate add-ons installed', 'js-support-ticket' ) ); ?></h2>
                        <p><?php echo esc_html( __( 'They keep running as they are. Moving one onto the new bundles is previewed first and can be undone.', 'js-support-ticket' ) ); ?></p>
                    </div>
                    <a class="jsst-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=jssupportticket&jstlay=legacy' ) ); ?>"><?php echo esc_html( __( 'Your Add-ons', 'js-support-ticket' ) ); ?></a>
                </section>
            <?php } ?>

            <?php
            /* Finish the upgrade from the add-ons sold before 5.0.0
               (JSSTupgradeassistant), moved here from Install Add-ons. */
            if ( class_exists( 'JSSTupgradeassistant' ) && $jsst_can['install'] ) {
                $jsst_plan = JSSTupgradeassistant::plan();
                $jsst_log  = get_option( JSSTupgradeassistant::OPTION_LOG, array() );
                if ( ! empty( $jsst_plan['steps'] ) || ! empty( $jsst_plan['waiting'] ) || ! empty( $jsst_plan['kept'] ) ) { ?>
                <div class="jsst-card jsst-upgrade-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php
                            echo esc_html( '' !== $jsst_free
                                ? ( '' !== $jsst_until
                                    ? sprintf(
                                        /* translators: 1: the old plan's name, e.g. Basic; 2: a date */
                                        __( 'Finish your upgrade - you were on %1$s, and your license includes all nine add-ons at no extra charge until %2$s', 'js-support-ticket' ),
                                        $jsst_free,
                                        $jsst_until
                                    )
                                    : sprintf(
                                        /* translators: %s: the old plan's name, e.g. Basic */
                                        __( 'Finish your upgrade - you were on %s, and your license now includes all nine add-ons at no extra charge', 'js-support-ticket' ),
                                        $jsst_free
                                    ) )
                                : ( $jsst_plan['allnine']
                                    ? __( 'Finish your upgrade - your account includes all nine add-ons', 'js-support-ticket' )
                                    : __( 'Finish your upgrade', 'js-support-ticket' ) ) ); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <?php if ( ! empty( $jsst_plan['steps'] ) ) { ?>
                            <p><?php echo esc_html( __( 'This site still runs add-ons from before 5.0.0. Their features now come from the bundles below or from JS Help Desk itself. One click does the following, one step at a time. Nothing is deleted, and every setting is kept.', 'js-support-ticket' ) ); ?></p>
                            <ol class="jsst-upgrade-steps">
                                <?php foreach ( $jsst_plan['steps'] as $jsst_step ) { ?>
                                    <li><?php
                                    if ( 'install' === $jsst_step['do'] ) {
                                        /* translators: %s: add-on name */
                                        echo esc_html( sprintf( __( 'Install %s', 'js-support-ticket' ), JSSTupgradeassistant::label( $jsst_step['slug'] ) ) );
                                    } elseif ( 'activate' === $jsst_step['do'] ) {
                                        /* translators: %s: add-on name */
                                        echo esc_html( sprintf( __( 'Switch on %s', 'js-support-ticket' ), JSSTupgradeassistant::label( $jsst_step['slug'] ) ) );
                                    } else {
                                        echo esc_html( sprintf(
                                            /* translators: 1: old add-on name, 2: what provides it now */
                                            __( 'Switch off the old %1$s add-on (now provided by %2$s)', 'js-support-ticket' ),
                                            JSSTupgradeassistant::legacyLabel( $jsst_step['slug'] ),
                                            'core' === $jsst_step['by'] ? __( 'JS Help Desk itself', 'js-support-ticket' ) : JSSTupgradeassistant::label( $jsst_step['by'] )
                                        ) );
                                    } ?></li>
                                <?php } ?>
                            </ol>
                            <p class="jsst-lic-actions">
                                <button type="button" class="jsst-btn jsst-btn-primary" id="jsst-upgrade-go"
                                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'jsst-upgrade-step' ) ); ?>"
                                        data-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"><?php echo esc_html( __( 'Finish upgrade', 'js-support-ticket' ) ); ?></button>
                            </p>
                            <ul class="jsst-upgrade-log" id="jsst-upgrade-log" aria-live="polite"></ul>
                        <?php } ?>
                        <?php foreach ( $jsst_plan['waiting'] as $jsst_bundle => $jsst_why ) { ?>
                            <div class="jsst-lic-alert"><?php
                            $jsst_name = JSSTupgradeassistant::label( $jsst_bundle );
                            if ( 'nokey' === $jsst_why ) {
                                /* translators: %s: add-on name */
                                echo esc_html( sprintf( __( '%s replaces add-ons on this site, but it can only be installed once the license key is on this site.', 'js-support-ticket' ), $jsst_name ) );
                            } elseif ( 'notcurrent' === $jsst_why ) {
                                /* translators: %s: add-on name */
                                echo esc_html( sprintf( __( '%s replaces add-ons on this site, but it can only be installed while the license is current. The old add-ons stay switched on until then.', 'js-support-ticket' ), $jsst_name ) );
                            } else {
                                /* translators: %s: add-on name */
                                echo esc_html( sprintf( __( '%s replaces add-ons on this site, but your license does not include it. The old add-ons stay switched on.', 'js-support-ticket' ), $jsst_name ) );
                            } ?></div>
                        <?php } ?>
                        <?php foreach ( $jsst_plan['kept'] as $jsst_slug => $jsst_why ) {
                            if ( 'unknown' !== $jsst_why ) { continue; } ?>
                            <div class="jsst-lic-notice"><?php
                                /* translators: %s: old add-on name */
                                echo esc_html( sprintf( __( 'The old %s add-on has no replacement in 5.0.0, so it is left as it is.', 'js-support-ticket' ), JSSTupgradeassistant::legacyLabel( $jsst_slug ) ) ); ?></div>
                        <?php } ?>
                    </div>
                </div>
                <script>
                (function () {
                    var go = document.getElementById('jsst-upgrade-go');
                    if (!go) { return; }
                    var log = document.getElementById('jsst-upgrade-log');
                    function line(text, bad) { var li = document.createElement('li'); li.textContent = text; if (bad) { li.className = 'is-error'; } log.appendChild(li); }
                    var tries = 0;
                    function step() {
                        var body = new FormData();
                        body.append('action', 'jsst_upgrade_step');
                        body.append('_ajax_nonce', go.getAttribute('data-nonce'));
                        fetch(go.getAttribute('data-url'), { method: 'POST', credentials: 'same-origin', body: body })
                            .then(function (r) { return r.json(); })
                            .then(function (r) {
                                if (!r || !r.success) { line((r && r.data && r.data.message) || <?php echo wp_json_encode( __( 'The step failed. Reload the page and try again.', 'js-support-ticket' ) ); ?>, true); go.disabled = false; return; }
                                var d = r.data;
                                if (d.message) { line(d.message, !d.ok && !d.retry); }
                                if (!d.ok && d.retry) { tries += 1; if (tries <= 5) { setTimeout(step, d.retry * 1000); return; } }
                                if (!d.ok) { go.disabled = false; return; }
                                tries = 0;
                                if (d.remaining > 0) { step(); } else { line(<?php echo wp_json_encode( __( 'Done. Reloading...', 'js-support-ticket' ) ); ?>); setTimeout(function () { window.location.reload(); }, 1200); }
                            })
                            .catch(function () { line(<?php echo wp_json_encode( __( 'The connection dropped. Reload the page - the upgrade carries on from where it stopped.', 'js-support-ticket' ) ); ?>, true); go.disabled = false; });
                    }
                    go.addEventListener('click', function () { go.disabled = true; step(); });
                })();
                </script>
                <?php } elseif ( ! empty( $jsst_log['steps'] ) ) {
                    $jsst_last = end( $jsst_log['steps'] ); ?>
                <div class="jsst-lic-notice jsst-lic-notice-ok"><?php echo esc_html( sprintf(
                    /* translators: 1: a date, 2: number of steps */
                    __( 'Upgrade finished on %1$s: %2$d steps done.', 'js-support-ticket' ),
                    date_i18n( get_option( 'date_format' ), (int) $jsst_last['when'] ),
                    count( array_filter( $jsst_log['steps'], function ( $jsst_s ) { return ! empty( $jsst_s['ok'] ); } ) )
                ) ); ?></div>
                <?php }
            } ?>

            <section class="jsst-lp-addons" aria-labelledby="jsst-lp-addons-head">
                <div class="jsst-lp-addons-head">
                    <div>
                        <h2 id="jsst-lp-addons-head"><?php echo esc_html( __( 'Add-ons', 'js-support-ticket' ) ); ?></h2>
                        <p class="jsst-lp-sum" id="jsst-lp-sum"><?php echo esc_html( $jsst_has_key
                            ? sprintf( $jsst_js['t']['sum'], number_format_i18n( $jsst_running ), number_format_i18n( $jsst_total ) )
                                . ( $jsst_todo ? ' · ' . sprintf( $jsst_js['t']['sumtodo'], number_format_i18n( $jsst_todo ) ) : '' )
                            : __( 'Nine packs that grow the free help desk', 'js-support-ticket' ) ); ?></p>
                    </div>
                    <?php if ( $jsst_has_key ) { ?>
                        <div class="jsst-lp-tools">
                            <div class="jsst-lp-tabs" role="group" aria-label="<?php echo esc_attr( __( 'Show', 'js-support-ticket' ) ); ?>" hidden>
                                <button type="button" data-filter="all" aria-pressed="true"><?php echo esc_html( sprintf(
                                    /* translators: %s: number of add-ons */
                                    __( 'All %s', 'js-support-ticket' ),
                                    number_format_i18n( $jsst_total )
                                ) ); ?></button>
                                <button type="button" data-filter="active" aria-pressed="false"></button>
                                <button type="button" data-filter="todo" aria-pressed="false"></button>
                            </div>
                            <button type="button" class="jsst-btn jsst-btn-primary jsst-lp-all" hidden></button>
                        </div>
                    <?php } ?>
                </div>

                <div class="jsst-lp-grid">
                    <?php foreach ( $jsst_cards as $jsst_product => $jsst_card ) {
                        $jsst_row  = $jsst_card['row'];
                        $jsst_st   = $jsst_card['state'];
                        $jsst_ver  = $jsst_card['ver'];
                        $jsst_lk   = $jsst_card['look'];
                        ?>
                        <article class="jsst-lp-pack is-<?php echo esc_attr( $jsst_st ); ?>" style="--jsst-pack:<?php echo esc_attr( $jsst_lk[1] ); ?>"
                                 data-product="<?php echo esc_attr( $jsst_product ); ?>" data-state="<?php echo esc_attr( $jsst_st ); ?>"
                                 data-name="<?php echo esc_attr( $jsst_row['name'] ); ?>" aria-labelledby="jsst-lp-n-<?php echo esc_attr( $jsst_product ); ?>">
                            <div class="jsst-lp-pack-head">
                                <span class="jsst-lp-tile" aria-hidden="true"><span class="dashicons dashicons-<?php echo esc_attr( $jsst_lk[0] ); ?>"></span></span>
                                <h3 id="jsst-lp-n-<?php echo esc_attr( $jsst_product ); ?>"><?php echo esc_html( $jsst_row['name'] ); ?></h3>
                                <?php if ( '' !== $jsst_lk[2] ) { ?><span class="jsst-lp-badge"><?php echo esc_html( $jsst_lk[2] ); ?></span><?php } ?>
                            </div>
                            <?php if ( array() !== $jsst_lk[3] ) { ?>
                                <ul class="jsst-lp-feat">
                                    <?php foreach ( $jsst_lk[3] as $jsst_line ) { ?><li><?php echo esc_html( $jsst_line ); ?></li><?php } ?>
                                </ul>
                            <?php } elseif ( '' !== $jsst_row['summary'] ) { ?>
                                <p class="jsst-lp-feat"><?php echo esc_html( $jsst_row['summary'] ); ?></p>
                            <?php } ?>
                            <div class="jsst-lp-foot">
                                <?php switch ( $jsst_st ) {
                                    case 'locked': ?>
                                        <span class="jsst-lp-st"><span class="dashicons dashicons-lock" aria-hidden="true"></span><?php echo esc_html( __( 'Included in every plan', 'js-support-ticket' ) ); ?></span>
                                        <?php break;
                                    case 'waiting': ?>
                                        <span class="jsst-lp-st"><i class="jsst-lp-dot is-warn"></i><?php echo esc_html( __( 'Installed, waiting for your license', 'js-support-ticket' ) ); ?></span>
                                        <?php break;
                                    case 'gated': ?>
                                        <span class="jsst-lp-st is-bad"><i class="jsst-lp-dot is-bad"></i><?php echo esc_html( __( 'Switched off: license not confirmed', 'js-support-ticket' ) ); ?></span>
                                        <a class="jsst-btn" data-lp-key="check" href="<?php echo esc_url( $jsst_task( 'rechecklicense', 'jsst-lic-recheck' ) ); ?>"><?php echo esc_html( __( 'Check again', 'js-support-ticket' ) ); ?></a>
                                        <?php break;
                                    case 'active': ?>
                                        <span class="jsst-lp-st is-ok"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php echo esc_html( '' !== $jsst_row['version'] ? sprintf( $jsst_js['t']['active'], $jsst_row['version'] ) : $jsst_js['t']['activeonly'] ); ?></span>
                                        <?php break;
                                    case 'paused': ?>
                                        <span class="jsst-lp-st"><i class="jsst-lp-dot is-warn"></i><?php echo esc_html( __( 'Working, updates paused', 'js-support-ticket' ) ); ?></span>
                                        <?php break;
                                    case 'update': ?>
                                        <span class="jsst-lp-st<?php echo $jsst_ver['security'] ? ' is-bad' : ''; ?>"><i class="jsst-lp-dot <?php echo $jsst_ver['security'] ? 'is-bad' : 'is-warn'; ?>"></i><?php echo esc_html( $jsst_ver['security']
                                            ? sprintf(
                                                /* translators: %s: version number */
                                                __( 'Security fix %s is ready', 'js-support-ticket' ),
                                                $jsst_ver['latest']
                                            )
                                            : sprintf(
                                                /* translators: %s: version number */
                                                __( 'Version %s is ready', 'js-support-ticket' ),
                                                $jsst_ver['latest']
                                            ) ); ?></span>
                                        <?php $jsst_file = $jsst_row['file']; ?>
                                        <a class="jsst-btn jsst-btn-primary" data-lp-do="update" href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $jsst_file ) ), 'upgrade-plugin_' . $jsst_file ) ); ?>"><?php echo esc_html( __( 'Update', 'js-support-ticket' ) ); ?></a>
                                        <?php break;
                                    case 'off': ?>
                                        <span class="jsst-lp-st"><i class="jsst-lp-dot"></i><?php echo esc_html( $jsst_js['t']['off'] ); ?></span>
                                        <?php if ( $jsst_can['activate'] ) { ?>
                                            <a class="jsst-btn jsst-btn-primary" data-lp-do="activate" href="<?php echo esc_url( $jsst_task( 'activateaddon', 'jsst-activate-addon_' . $jsst_product, '&product=' . rawurlencode( $jsst_product ) ) ); ?>"><?php echo esc_html( $jsst_js['t']['switchon'] ); ?></a>
                                        <?php } ?>
                                        <?php break;
                                    case 'none': ?>
                                        <span class="jsst-lp-st"><i class="jsst-lp-dot"></i><?php echo esc_html( __( 'Not installed', 'js-support-ticket' ) ); ?></span>
                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=jssupportticket&task=installaddons&action=jstask' ) ); ?>">
                                            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'jsst-install-addons' ) ); ?>">
                                            <input type="hidden" name="products[]" value="<?php echo esc_attr( $jsst_product ); ?>">
                                            <button type="submit" class="jsst-btn jsst-btn-primary" data-lp-do="install"><?php echo esc_html( __( 'Install', 'js-support-ticket' ) ); ?></button>
                                        </form>
                                        <?php break;
                                    case 'noperm': ?>
                                        <span class="jsst-lp-st"><i class="jsst-lp-dot"></i><?php echo esc_html( __( 'Not installed', 'js-support-ticket' ) ); ?></span>
                                        <?php break;
                                    case 'renew': ?>
                                        <span class="jsst-lp-st"><i class="jsst-lp-dot is-warn"></i><?php echo esc_html( __( 'Renew to install', 'js-support-ticket' ) ); ?></span>
                                        <?php break;
                                    case 'notplan': ?>
                                        <span class="jsst-lp-st"><i class="jsst-lp-dot"></i><?php echo esc_html( __( 'Not in your plan', 'js-support-ticket' ) ); ?></span>
                                        <a href="<?php echo esc_url( $jsst_pricing ); ?>" target="_blank" rel="noopener"><?php echo esc_html( __( 'Upgrade plan', 'js-support-ticket' ) ); ?></a>
                                        <?php break;
                                } ?>
                            </div>
                        </article>
                    <?php } ?>
                </div>
                <p class="screen-reader-text" id="jsst-lp-live" aria-live="polite"></p>
            </section>

            <p class="jsst-lp-footer">
                <a href="https://jshelpdesk.com/docs/" target="_blank" rel="noopener"><?php echo esc_html( __( 'Documentation', 'js-support-ticket' ) ); ?></a>
                <?php if ( ! $jsst_has_key ) { ?>
                    <a href="<?php echo esc_url( $jsst_pricing ); ?>" target="_blank" rel="noopener"><?php echo esc_html( __( 'Compare plans', 'js-support-ticket' ) ); ?></a>
                <?php } ?>
            </p>
        </div>
    </div>
</div>
<script>
(function () {
    var root = document.querySelector('.jsst-lp');
    if (!root || !window.fetch) { return; }
    var cfg = JSON.parse(root.getAttribute('data-lp'));
    var t = cfg.t;
    var busy = false;
    var live = document.getElementById('jsst-lp-live');
    var TODO = ['none', 'off', 'update', 'failed'];
    var RUNNING = ['active', 'paused', 'update'];

    function fmt(s) { var a = arguments, i = 0; return s.replace(/%(\d\$)?s/g, function (m, n) { return n ? a[parseInt(n, 10)] : a[++i]; }); }
    function post(action, data) {
        var body = new FormData();
        body.append('action', action);
        body.append('nonce', cfg.nonce);
        Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
        return fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', body: body })
            .then(function (r) { return r.json(); })
            .catch(function () { return { success: false, data: { message: t.failed } }; });
    }
    function el(tag, cls, text) { var e = document.createElement(tag); if (cls) { e.className = cls; } if (text) { e.textContent = text; } return e; }

    /* ---- the key ---- */
    var form = document.getElementById('jsst-lp-keyform');
    var msg = document.getElementById('jsst-lp-keymsg');
    function say(kind, text) {
        msg.className = 'jsst-lp-keymsg is-' + kind;
        msg.textContent = '';
        if (kind === 'busy') { msg.appendChild(el('span', 'jsst-lp-spin')); }
        if (kind === 'ok') { msg.appendChild(el('span', 'dashicons dashicons-yes-alt jsst-lp-pop')); }
        msg.appendChild(document.createTextNode(' ' + text));
    }
    function keyDo(what, extra) {
        if (busy) { return; }
        busy = true;
        say('busy', t.checking);
        post('jsst_lp_key', Object.assign({ do: what }, extra || {})).then(function (r) {
            busy = false;
            var m = (r && r.data && r.data.message) || t.failed;
            if (r && r.success) { say('ok', m); setTimeout(function () { window.location.reload(); }, 1400); }
            else { say('bad', m); }
        });
    }
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var v = form.licensekey.value.trim();
            if (!v) { say('bad', t.emptykey); form.licensekey.focus(); return; }
            keyDo('activate', { key: v });
        });
    }
    root.addEventListener('click', function (e) {
        var a = e.target.closest('[data-lp-key]');
        if (!a) { return; }
        e.preventDefault();
        var what = a.getAttribute('data-lp-key');
        if (what === 'release' && !window.confirm(t.release)) { return; }
        if (form && !form.contains(a)) { form.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        keyDo(what);
    });

    /* ---- the add-ons ---- */
    var cards = Array.prototype.slice.call(root.querySelectorAll('.jsst-lp-pack'));
    var tabs = root.querySelector('.jsst-lp-tabs');
    var all = root.querySelector('.jsst-lp-all');
    var sum = document.getElementById('jsst-lp-sum');
    var ring = root.querySelector('.jsst-lp-ring-fg');
    var ringText = root.querySelector('.jsst-lp-ring-text');
    var filter = 'all';

    function count(list) { return cards.filter(function (c) { return list.indexOf(c.getAttribute('data-state')) !== -1; }).length; }
    function refresh() {
        var run = count(RUNNING), todo = count(TODO);
        if (ring) {
            ring.style.strokeDashoffset = (213.6 * (1 - run / Math.max(1, cfg.total))).toFixed(1);
            ringText.textContent = run + '/' + cfg.total;
        }
        if (!tabs) { return; }
        sum.textContent = fmt(t.sum, run, cfg.total) + (todo ? ' · ' + fmt(t.sumtodo, todo) : '');
        tabs.hidden = false;
        tabs.querySelector('[data-filter="active"]').textContent = fmt(t.tabactive, run);
        tabs.querySelector('[data-filter="todo"]').textContent = fmt(t.tabtodo, todo);
        var allNew = count(['none']) === cards.length;
        all.hidden = todo === 0;
        all.textContent = allNew ? t.all : fmt(t.rest, todo);
        cards.forEach(function (c) {
            var s = c.getAttribute('data-state');
            c.hidden = !(filter === 'all' || (filter === 'active' ? RUNNING.indexOf(s) !== -1 : TODO.indexOf(s) !== -1 || s === 'work'));
        });
    }
    if (tabs) {
        tabs.addEventListener('click', function (e) {
            var b = e.target.closest('button');
            if (!b) { return; }
            filter = b.getAttribute('data-filter');
            tabs.querySelectorAll('button').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
            refresh();
        });
    }

    function setState(card, state) { card.setAttribute('data-state', state); card.className = 'jsst-lp-pack is-' + state; }
    function run(card, what) {
        return new Promise(function (done) {
            var foot = card.querySelector('.jsst-lp-foot');
            setState(card, 'work');
            foot.textContent = '';
            var box = el('div', 'jsst-lp-work');
            box.appendChild(el('span', 'jsst-lp-st', t[what]));
            var bar = el('div', 'jsst-lp-bar is-busy');
            bar.appendChild(el('i'));
            box.appendChild(bar);
            foot.appendChild(box);
            refresh();
            post('jsst_lp_addon', { do: what, product: card.getAttribute('data-product') }).then(function (r) {
                foot.textContent = '';
                var name = card.getAttribute('data-name');
                if (r && r.success) {
                    setState(card, 'active');
                    var ok = el('span', 'jsst-lp-st is-ok jsst-lp-pop');
                    ok.appendChild(el('span', 'dashicons dashicons-yes-alt'));
                    ok.appendChild(document.createTextNode(r.data.version ? fmt(t.active, r.data.version) : t.activeonly));
                    foot.appendChild(ok);
                    live.textContent = fmt(t.saydone, name);
                } else {
                    var off = r && r.data && r.data.state === 'off';
                    setState(card, off ? 'off' : 'failed');
                    foot.appendChild(el('span', 'jsst-lp-st is-bad', (r && r.data && r.data.message) || t.failed));
                    var again = el('button', 'jsst-btn jsst-btn-primary', off ? t.switchon : t.again);
                    again.type = 'button';
                    again.setAttribute('data-lp-do', off ? 'activate' : what);
                    foot.appendChild(again);
                    live.textContent = fmt(t.sayfail, name);
                }
                refresh();
                done();
            });
        });
    }
    root.addEventListener('click', function (e) {
        var b = e.target.closest('[data-lp-do]');
        if (!b) { return; }
        e.preventDefault();
        if (busy) { return; }
        busy = true;
        run(b.closest('.jsst-lp-pack'), b.getAttribute('data-lp-do')).then(function () { busy = false; });
    });
    if (all) {
        all.addEventListener('click', function () {
            if (busy) { return; }
            busy = true;
            all.disabled = true;
            var queue = cards.filter(function (c) { return TODO.indexOf(c.getAttribute('data-state')) !== -1; });
            (function next() {
                var c = queue.shift();
                if (!c) { busy = false; all.disabled = false; return; }
                var s = c.getAttribute('data-state');
                c.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                run(c, s === 'off' ? 'activate' : (s === 'update' ? 'update' : 'install')).then(next);
            })();
        });
    }

    /* The ring fills in on arrival, unless motion is turned down. */
    if (ring && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        var target = ring.style.strokeDashoffset;
        ring.style.transition = 'none';
        ring.style.strokeDashoffset = '213.6';
        ring.getBoundingClientRect();
        ring.style.transition = '';
        requestAnimationFrame(function () { ring.style.strokeDashoffset = target; });
    }
    refresh();
})();
</script>
