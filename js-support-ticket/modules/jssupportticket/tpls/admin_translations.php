<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Translations: which languages this help desk is installed in, and the files
 * to download. The installing itself happens without anyone opening this
 * screen (JSSTtranslations). (1 Oct 2026)
 */
$jsst_tr = isset(jssupportticket::$jsst_data['translations']) ? jssupportticket::$jsst_data['translations'] : JSSTtranslations::rows();
$jsst_site = array_filter($jsst_tr['rows'], function ($jsst_r) { return $jsst_r['site']; });
$jsst_offered = array_filter($jsst_tr['rows'], function ($jsst_r) { return !empty($jsst_r['entry']) && $jsst_r['entry']['locale'] === $jsst_r['locale']; });
$jsst_result = isset($_GET['jsst_tr']) ? sanitize_key(wp_unslash($_GET['jsst_tr'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the outcome of a nonce-checked action, only shown
$jsst_date = function ($jsst_version) {
    $jsst_t = strtotime((string) $jsst_version);
    return $jsst_t ? wp_date(get_option('date_format'), $jsst_t) : (string) $jsst_version;
};
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Translations', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <?php
            $jsst_trl = isset($_GET['jsst_trl']) ? sanitize_text_field(wp_unslash($_GET['jsst_trl'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- which language the nonce-checked action installed, only shown
            if (('added' === $jsst_result || 'addednowp' === $jsst_result) && '' !== $jsst_trl) { ?>
                <div class="notice notice-success inline"><p><?php
                    echo esc_html(sprintf(
                        /* translators: %s: a language name, e.g. Français */
                        __('%s is installed.', 'js-support-ticket'),
                        JSSTtranslations::languageName($jsst_trl, JSSTtranslations::entryFor($jsst_trl, get_site_transient(JSSTtranslations::T_MANIFEST)), true)
                    )); ?>
                    <?php if ('added' === $jsst_result) { ?>
                        <a class="jsst-btn" href="<?php echo esc_url(JSSTtranslations::actionUrl('useme', $jsst_trl)); ?>"><?php echo esc_html(__('Use it for me', 'js-support-ticket')); ?></a>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('options-general.php#WPLANG')); ?>"><?php echo esc_html(__('Use it for the whole site', 'js-support-ticket')); ?></a>
                    <?php } else { ?>
                        <?php echo esc_html(__('WordPress itself has no language pack for it, so it cannot be chosen as the site language yet.', 'js-support-ticket')); ?>
                    <?php } ?></p></div>
            <?php } elseif ('useme' === $jsst_result) { ?>
                <div class="notice notice-success inline"><p><?php echo esc_html(__('Your own language is changed. The rest of the site keeps its language.', 'js-support-ticket')); ?></p></div>
            <?php } elseif ('done' === $jsst_result) { ?>
                <div class="notice notice-success inline"><p><?php echo esc_html(__('Translations are up to date.', 'js-support-ticket')); ?></p></div>
            <?php } elseif ('failed' === $jsst_result) { ?>
                <div class="notice notice-error inline"><p><?php echo esc_html(__('Not every translation could be installed. The reason is shown beside it below.', 'js-support-ticket')); ?></p></div>
            <?php } ?>

            <?php if ('' !== $jsst_tr['listerror']) { ?>
                <div class="notice notice-warning inline"><p><?php echo esc_html($jsst_tr['listerror']); ?></p></div>
            <?php } ?>
            <?php if (!JSSTtranslations::canWrite()) { ?>
                <div class="notice notice-warning inline"><p><?php echo esc_html(__('This site does not allow plugins to install language files (DISALLOW_FILE_MODS). Download the files below and upload them to wp-content/languages/plugins/.', 'js-support-ticket')); ?></p></div>
            <?php } elseif (!JSSTtranslations::automatic()) { ?>
                <div class="notice notice-info inline"><p><?php echo esc_html(__('Automatic installation is switched off on this site (JSST_TRANSLATIONS_AUTO). Use Update now to install a language.', 'js-support-ticket')); ?></p></div>
            <?php } ?>

            <div class="jsst-status-card <?php echo esc_attr(empty($jsst_site) || !in_array('failed', wp_list_pluck($jsst_site, 'status'), true) ? 'jsst-status-ok' : 'jsst-status-warn'); ?>">
                <div class="jsst-status-title"><?php echo esc_html(__('This site\'s languages', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('The help desk installs the translation for your site\'s language, and for every language WordPress has installed for your users, by itself - when it is installed or updated, when the site language changes, and once a day for improvements.', 'js-support-ticket')); ?></div>
                <?php if (empty($jsst_site)) { ?>
                    <div class="jsst-status-note"><?php echo esc_html(__('This site is in English, so no translation is needed. To use another language, change the Site Language under Settings > General.', 'js-support-ticket')); ?></div>
                <?php } else { ?>
                    <table class="jsst-status-table">
                        <thead><tr>
                            <th><?php echo esc_html(__('Language', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Translation', 'js-support-ticket')); ?></th>
                            <th></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($jsst_site as $jsst_r) {
                            $jsst_e = $jsst_r['entry'];
                            ?>
                            <tr>
                                <td><?php echo esc_html($jsst_r['name']); ?> <span class="jsst-status-path"><?php echo esc_html($jsst_r['locale']); ?></span></td>
                                <td><?php
                                    switch ($jsst_r['status']) {
                                        case 'current':
                                            echo '<span class="jsst-status-flag jsst-status-flag-ok">' . esc_html(__('Installed', 'js-support-ticket')) . '</span>';
                                            break;
                                        case 'update':
                                            echo '<span class="jsst-status-flag jsst-status-flag-warn">' . esc_html(__('Update available', 'js-support-ticket')) . '</span>';
                                            break;
                                        case 'missing':
                                            echo '<span class="jsst-status-flag jsst-status-flag-warn">' . esc_html(__('Not installed yet', 'js-support-ticket')) . '</span>';
                                            break;
                                        case 'failed':
                                            echo '<span class="jsst-status-flag jsst-status-flag-bad">' . esc_html(__('Could not be installed', 'js-support-ticket')) . '</span>';
                                            break;
                                        case 'own':
                                            echo '<span class="jsst-status-flag jsst-status-flag-ok">' . esc_html(__('Your own file', 'js-support-ticket')) . '</span>';
                                            break;
                                        default:
                                            echo '<span class="jsst-status-flag">' . esc_html(__('No translation yet', 'js-support-ticket')) . '</span>';
                                    }
                                    if ('' !== $jsst_r['error'] && 'current' !== $jsst_r['status']) {
                                        echo '<div class="jsst-status-error">' . esc_html($jsst_r['error']) . '</div>';
                                    } ?></td>
                                <td><?php
                                    if ($jsst_e) {
                                        echo esc_html(sprintf(
                                            /* translators: 1: a percentage, 2: a date */
                                            __('%1$d%% translated, updated %2$s', 'js-support-ticket'),
                                            $jsst_e['percent'],
                                            $jsst_date($jsst_e['version'])
                                        ));
                                        if ($jsst_e['locale'] !== $jsst_r['locale']) {
                                            echo '<div class="jsst-status-when">' . esc_html(sprintf(
                                                /* translators: %s: a language name, e.g. French (France) */
                                                __('Using the %s translation, the nearest there is.', 'js-support-ticket'),
                                                JSSTtranslations::languageName($jsst_e['locale'], $jsst_e, true)
                                            )) . '</div>';
                                        }
                                    } elseif ('own' === $jsst_r['status']) {
                                        echo esc_html(__('A file someone added to wp-content/languages/plugins/. It is used as it is.', 'js-support-ticket'));
                                    } else {
                                        echo esc_html(__('Nobody has translated the help desk into this language yet.', 'js-support-ticket'));
                                    } ?></td>
                                <td class="jsst-tr-actions"><?php
                                    if ($jsst_e && JSSTtranslations::canWrite() && 'current' !== $jsst_r['status']) { ?>
                                        <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url(JSSTtranslations::actionUrl('install', $jsst_r['locale'])); ?>"><?php echo esc_html('missing' === $jsst_r['status'] || 'failed' === $jsst_r['status'] ? __('Install now', 'js-support-ticket') : __('Update now', 'js-support-ticket')); ?></a>
                                    <?php } elseif (get_user_locale() === $jsst_r['locale']) { ?>
                                        <span class="jsst-status-when"><?php echo esc_html(__('Your language', 'js-support-ticket')); ?></span>
                                    <?php } elseif (in_array($jsst_r['locale'], get_available_languages(), true)) { ?>
                                        <a class="jsst-btn" href="<?php echo esc_url(JSSTtranslations::actionUrl('useme', $jsst_r['locale'])); ?>"><?php echo esc_html(__('Use it for me', 'js-support-ticket')); ?></a>
                                    <?php } ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <p>
                    <a class="jsst-btn" href="<?php echo esc_url(JSSTtranslations::actionUrl('sync')); ?>"><?php echo esc_html(__('Check for updates', 'js-support-ticket')); ?></a>
                    <?php if ('en_US' !== get_user_locale()) { ?>
                        <a class="jsst-btn" href="<?php echo esc_url(JSSTtranslations::actionUrl('useme', 'en_US')); ?>"><?php echo esc_html(__('Use English for me', 'js-support-ticket')); ?></a>
                    <?php } ?>
                    <?php if ($jsst_tr['checked']) { ?>
                        <span class="jsst-status-when"><?php echo esc_html(sprintf(
                            /* translators: %s: a date and time */
                            __('Last checked %s', 'js-support-ticket'),
                            wp_date(get_option('date_format') . ' ' . get_option('time_format'), $jsst_tr['checked'])
                        )); ?></span>
                    <?php } ?>
                </p>
            </div>

            <?php if (!empty($jsst_offered)) { ?>
                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html(__('All translations', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(__('Install adds a language to this site - WordPress\'s own and the help desk\'s - so it can be chosen for the whole site or for one person. The files are for a site that cannot download by itself, or to change the wording: the .po file is the one you edit (with Poedit or Loco Translate), the .mo file is what WordPress reads. Both go in wp-content/languages/plugins/.', 'js-support-ticket')); ?></div>
                    <table class="jsst-status-table">
                        <thead><tr>
                            <th><?php echo esc_html(__('Language', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Translated', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Updated', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Files', 'js-support-ticket')); ?></th>
                            <th></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($jsst_offered as $jsst_r) {
                            $jsst_e = $jsst_r['entry']; ?>
                            <tr>
                                <td><?php echo esc_html($jsst_r['name']); ?> <span class="jsst-status-path"><?php echo esc_html($jsst_r['locale']); ?></span></td>
                                <td><?php echo esc_html($jsst_e['percent'] . '%'); ?></td>
                                <td><?php echo esc_html($jsst_date($jsst_e['version'])); ?></td>
                                <td class="jsst-tr-files">
                                    <?php if ('' !== $jsst_e['po']) { ?><a href="<?php echo esc_url($jsst_e['po']); ?>" download>.po</a><?php } ?>
                                    <?php if ('' !== $jsst_e['mo']) { ?><a href="<?php echo esc_url($jsst_e['mo']); ?>" download>.mo</a><?php } ?>
                                    <a href="<?php echo esc_url($jsst_e['package']); ?>">.zip</a>
                                </td>
                                <td class="jsst-tr-actions"><?php
                                    if ($jsst_r['site'] && 'current' === $jsst_r['status']) { ?>
                                        <span class="jsst-status-flag jsst-status-flag-ok"><?php echo esc_html(__('Installed', 'js-support-ticket')); ?></span>
                                    <?php } elseif (JSSTtranslations::canWrite()) { ?>
                                        <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url(JSSTtranslations::actionUrl($jsst_r['site'] ? 'install' : 'add', $jsst_r['locale'])); ?>"><?php echo esc_html($jsst_r['site'] && 'update' === $jsst_r['status'] ? __('Update now', 'js-support-ticket') : __('Install', 'js-support-ticket')); ?></a>
                                    <?php } ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('Changing the wording', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('A newer translation replaces the files in wp-content/languages/plugins/, so wording you change there is lost at the next update. To keep your own wording, save it with Loco Translate in its Custom location, which updates never touch, or switch automatic updates off by adding define(\'JSST_TRANSLATIONS_AUTO\', false); to wp-config.php.', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('Your language is missing, or a word is wrong? Open a ticket at jshelpdesk.com - corrections go into the next update for everyone.', 'js-support-ticket')); ?></div>
            </div>

        </div>
    </div>
</div>
