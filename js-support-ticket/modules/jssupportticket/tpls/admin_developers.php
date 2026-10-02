<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The developer reference. (Roadmap 4.0-DATA-03)
 *
 * Everything on this page is read out of the plugin's own source when it is
 * generated, and nothing on it is maintained by hand. That is the only way a
 * reference of this size stays true: a hook renamed in a release renames itself
 * here, and one that is deleted disappears.
 *
 * The order is the order somebody integrating actually needs it in. The events
 * come first because they are the part that is a promise — versioned, with a
 * described payload — while the hooks below them are a description of where
 * this code calls out, which is useful and is not the same thing. The schema
 * history is last because it is what somebody reads when an upgrade has gone
 * wrong rather than when they are building.
 */
if (!class_exists('JSSThooks')) {
    echo esc_html(__('The developer reference is not available.', 'js-support-ticket'));
    return;
}

$jsst_summary = isset(jssupportticket::$jsst_data['dvsummary']) ? jssupportticket::$jsst_data['dvsummary'] : array();
$jsst_hooks   = isset(jssupportticket::$jsst_data['dvhooks']) ? jssupportticket::$jsst_data['dvhooks'] : array();
$jsst_events  = isset(jssupportticket::$jsst_data['dvevents']) ? jssupportticket::$jsst_data['dvevents'] : array();
$jsst_places  = isset(jssupportticket::$jsst_data['dvplaces']) ? jssupportticket::$jsst_data['dvplaces'] : array();
$jsst_schema  = isset(jssupportticket::$jsst_data['dvschema']) ? jssupportticket::$jsst_data['dvschema'] : array();
$jsst_guards  = isset(jssupportticket::$jsst_data['dvguards']) ? jssupportticket::$jsst_data['dvguards'] : array();
$jsst_api     = isset(jssupportticket::$jsst_data['dvapi']) ? jssupportticket::$jsst_data['dvapi'] : array();
$jsst_kind    = isset(jssupportticket::$jsst_data['dvkind']) ? jssupportticket::$jsst_data['dvkind'] : '';
$jsst_where   = isset(jssupportticket::$jsst_data['dvwhere']) ? jssupportticket::$jsst_data['dvwhere'] : '';
$jsst_search  = isset(jssupportticket::$jsst_data['dvsearch']) ? jssupportticket::$jsst_data['dvsearch'] : '';
$jsst_scope   = isset(jssupportticket::$jsst_data['dvscope']) ? jssupportticket::$jsst_data['dvscope'] : 'ours';
$jsst_total   = isset(jssupportticket::$jsst_data['dvtotal']) ? jssupportticket::$jsst_data['dvtotal'] : 0;

$jsst_action   = wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=refreshhooks&action=jstask'), 'jsst-hooks');
$jsst_download = wp_nonce_url(admin_url('admin-ajax.php?action=jsst_hookdocs'), 'jsst-hookdocs');
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('For Developers', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <p class="jsst-lede">
                <?php echo esc_html(__('Everything this help desk offers somebody writing code against it: the events it announces, every action and filter it fires with the values each one passes, the REST API, and what each release did to the database. All of it is read out of the source when this page is generated, so it describes the code you actually have rather than the code somebody documented once.', 'js-support-ticket')); ?>
            </p>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('What there is', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <a class="jsst-btn" href="<?php echo esc_url($jsst_download); ?>"><?php echo esc_html(__('Download it as Markdown', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <div class="jsst-card-body">
                    <div class="jsst-metrics">
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_summary['events']) ? $jsst_summary['events'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Events', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_summary['actions']) ? $jsst_summary['actions'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Actions', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_summary['filters']) ? $jsst_summary['filters'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Filters', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_summary['sites']) ? $jsst_summary['sites'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Places they fire from', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_summary['files']) ? $jsst_summary['files'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Files read', 'js-support-ticket')); ?></span>
                        </div>
                    </div>
                    <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                        <div class="jsst-btnrow">
                            <button type="submit" class="jsst-btn"><?php echo esc_html(__('Read the source again', 'js-support-ticket')); ?></button>
                            <span class="jsst-formfoot-note"><?php
                                /* translators: %s is a date and time. */
                                echo esc_html(sprintf(__('Last read %s. It is read again by itself when the plugin version changes; this button is for when you have just edited a file.', 'js-support-ticket'),
                                    isset($jsst_summary['when']) ? $jsst_summary['when'] : '')); ?></span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Events', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('The part of this that is a promise. Each event is versioned and carries a described payload; subscribe to jsst_event for all of them, or to jsst_event_<name> for one. Everything in the plugin that reacts to something happening — service levels, automation, notifications, webhooks — is a subscriber to this same stream.', 'js-support-ticket')); ?></p>
                </div>
                <div class="jsst-card-body jsst-card-flush">
                    <div class="jsst-table-wrap">
                        <table class="jsst-table jsst-table-code">
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo esc_html(__('Event', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Version', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Always carries', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('May carry', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Old hook still fired', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_events AS $jsst_event) { ?>
                                <tr>
                                    <td>
                                        <span class="jsst-table-name"><code><?php echo esc_html($jsst_event['name']); ?></code></span>
                                        <span class="jsst-table-sub"><?php echo esc_html($jsst_event['label']); ?></span>
                                    </td>
                                    <td class="jsst-num"><?php echo esc_html($jsst_event['version']); ?></td>
                                    <td><span class="jsst-table-sub"><code><?php echo esc_html(implode(', ', $jsst_event['required'])); ?></code></span></td>
                                    <td><span class="jsst-table-sub"><code><?php echo esc_html(implode(', ', $jsst_event['optional'])); ?></code></span></td>
                                    <td><span class="jsst-table-sub"><?php echo esc_html($jsst_event['legacy'] !== '' ? $jsst_event['legacy'] : '—'); ?></span></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="jsst-card-foot">
                    <p class="jsst-fhelp"><?php echo esc_html(__('The old hook in the last column is still fired for every event that had one before the contract existed, so an add-on written against the old name keeps working. New code should use the event.', 'js-support-ticket')); ?></p>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Actions and filters', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('Found by reading the files. The values are the names the calling code uses for them — what the source says, rather than a guess at what they contain — and where somebody left a comment above the call, it is shown.', 'js-support-ticket')); ?></p>
                </div>
                <div class="jsst-card-body">
                    <form class="jsst-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                        <input type="hidden" name="page" value="jssupportticket" />
                        <input type="hidden" name="jstlay" value="developers" />
                        <div class="jsst-formgrid">
                            <div class="jsst-frow jsst-frow-sm">
                                <label class="jsst-flabel" for="dvsearch"><?php echo esc_html(__('Find', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval"><input type="search" id="dvsearch" name="dvsearch" value="<?php echo esc_attr($jsst_search); ?>" placeholder="<?php echo esc_attr(__('part of a name', 'js-support-ticket')); ?>" /></div>
                            </div>
                            <div class="jsst-frow jsst-frow-sm">
                                <label class="jsst-flabel" for="dvkind"><?php echo esc_html(__('Kind', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select name="dvkind" id="dvkind">
                                        <option value=""><?php echo esc_html(__('both', 'js-support-ticket')); ?></option>
                                        <option value="action" <?php selected($jsst_kind, 'action'); ?>><?php echo esc_html(__('actions', 'js-support-ticket')); ?></option>
                                        <option value="filter" <?php selected($jsst_kind, 'filter'); ?>><?php echo esc_html(__('filters', 'js-support-ticket')); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="jsst-frow jsst-frow-md">
                                <label class="jsst-flabel" for="dvwhere"><?php echo esc_html(__('Fired by', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select name="dvwhere" id="dvwhere">
                                        <option value=""><?php echo esc_html(__('anything', 'js-support-ticket')); ?></option>
                                        <?php foreach ($jsst_places AS $jsst_place => $jsst_count) { ?>
                                            <option value="<?php echo esc_attr($jsst_place); ?>" <?php selected($jsst_where, $jsst_place); ?>><?php
                                                /* translators: 1: a plugin or "Core", 2: how many call sites it has. */
                                                echo esc_html(sprintf(__('%1$s (%2$d)', 'js-support-ticket'), $jsst_place, $jsst_count)); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="jsst-frow jsst-frow-md">
                                <label class="jsst-flabel" for="dvscope"><?php echo esc_html(__('Whose', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select name="dvscope" id="dvscope">
                                        <option value="ours" <?php selected($jsst_scope, 'ours'); ?>><?php echo esc_html(__('this product\'s own', 'js-support-ticket')); ?></option>
                                        <option value="all" <?php selected($jsst_scope, 'all'); ?>><?php
                                            /* translators: %d is how many hooks belong to WordPress or another plugin. */
                                            echo esc_html(sprintf(__('everything, including %d that are not ours', 'js-support-ticket'),
                                                (int) (isset($jsst_summary['foreign']) ? $jsst_summary['foreign'] : 0))); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="jsst-frow jsst-frow-action">
                                <button type="submit" class="jsst-btn"><?php echo esc_html(__('Show these', 'js-support-ticket')); ?></button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($jsst_hooks)) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('Nothing matches that.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                        <p class="jsst-hint"><?php
                            /* translators: 1: how many hooks are shown, 2: how many there are in all. */
                            echo esc_html(sprintf(__('Showing %1$d of %2$d.', 'js-support-ticket'), count($jsst_hooks), (int) $jsst_total)); ?></p>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table jsst-table-code jsst-table-hooks">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo esc_html(__('Hook', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('It is passed', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('Fires from', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_hooks AS $jsst_hook) { ?>
                                    <tr>
                                        <td>
                                            <span class="jsst-table-name"><code><?php echo esc_html($jsst_hook['name']); ?></code></span>
                                            <span class="jsst-table-sub">
                                                <span class="jsst-pill jsst-pill-<?php echo ($jsst_hook['kind'] === 'action') ? 'info' : 'ok'; ?>"><span class="jsst-dot"></span><?php
                                                    echo esc_html($jsst_hook['kind'] === 'action' ? __('action', 'js-support-ticket') : __('filter', 'js-support-ticket')); ?></span>
                                                <?php if (!empty($jsst_hook['dynamic'])) { ?>
                                                    <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php echo esc_html(__('name built at runtime', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                                <?php if (empty($jsst_hook['ours'])) { ?>
                                                    <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('not ours — fired here, defined elsewhere', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                            </span>
                                            <?php if ($jsst_hook['note'] !== '') { ?>
                                                <span class="jsst-table-sub"><?php echo esc_html($jsst_hook['note']); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if (empty($jsst_hook['args'])) { ?>
                                                <span class="jsst-table-sub"><?php echo esc_html(__('nothing', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <div class="jsst-chips">
                                                    <?php foreach ($jsst_hook['args'] AS $jsst_arg) { ?>
                                                        <span class="jsst-chip"><code><?php echo esc_html($jsst_arg); ?></code></span>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php foreach (array_slice($jsst_hook['sites'], 0, 3) AS $jsst_site) { ?>
                                                <span class="jsst-table-sub"><code><?php echo esc_html($jsst_site['file'] . ':' . $jsst_site['line']); ?></code></span>
                                            <?php } ?>
                                            <?php if (count($jsst_hook['sites']) > 3) { ?>
                                                <span class="jsst-table-sub"><?php
                                                    /* translators: %d is how many further places fire the same hook. */
                                                    echo esc_html(sprintf(_n('and %d place more', 'and %d places more', count($jsst_hook['sites']) - 3, 'js-support-ticket'), count($jsst_hook['sites']) - 3)); ?></span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
                <div class="jsst-card-foot">
                    <p class="jsst-fhelp"><?php
                        /* translators: %d is how many hooks belong to WordPress or another plugin. */
                        echo esc_html(sprintf(__('This list is the extension points this product offers. It fires %d hooks belonging to WordPress and to other plugins as well — where it extends their pages, or reads their data during a migration — and those are shown only when you ask for everything, because they are not ours to promise.', 'js-support-ticket'),
                            (int) (isset($jsst_summary['foreign']) ? $jsst_summary['foreign'] : 0))); ?></p>
                    <p class="jsst-fhelp"><?php echo esc_html(__('A hook whose name is built at runtime is listed with a star: do_action("jsst_event_" . $name) appears as jsst_event_*, because naming every expansion would mean running the code and pretending the stem is the whole name would mislead whoever subscribed to it.', 'js-support-ticket')); ?></p>
                </div>
            </div>

            <div class="jsst-cards">
                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('The database, release by release', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Read from the upgrade files themselves. Releases that changed no table are left out.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body jsst-card-flush">
                        <div class="jsst-table-wrap">
                            <table class="jsst-table jsst-table-code">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo esc_html(__('Release', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('Added', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('Changed', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_schema AS $jsst_release) { ?>
                                    <tr>
                                        <td><span class="jsst-table-name"><?php echo esc_html($jsst_release['version']); ?></span></td>
                                        <td>
                                            <?php if (empty($jsst_release['created'])) { ?>
                                                <span class="jsst-table-sub"><?php echo esc_html(__('—', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <div class="jsst-chips">
                                                    <?php foreach ($jsst_release['created'] AS $jsst_table) { ?>
                                                        <span class="jsst-chip"><code><?php echo esc_html($jsst_table); ?></code></span>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </td>
                                        <td class="jsst-num"><?php echo esc_html(count($jsst_release['altered'])); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Tables that repair themselves', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Each of these is created and brought up to date by its own class when the feature is first reached, so a table lost in a restore comes back without a reinstall. The version is what that class is at.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body jsst-card-flush">
                        <div class="jsst-table-wrap">
                            <table class="jsst-table jsst-table-code">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo esc_html(__('Looked after by', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('At', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('Tables', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_guards AS $jsst_guard) { ?>
                                    <tr>
                                        <td><span class="jsst-table-name"><code><?php echo esc_html($jsst_guard['class']); ?></code></span></td>
                                        <td><span class="jsst-table-sub"><?php echo esc_html($jsst_guard['version']); ?></span></td>
                                        <td>
                                            <div class="jsst-chips">
                                                <?php foreach ($jsst_guard['tables'] AS $jsst_table) { ?>
                                                    <span class="jsst-chip"><code><?php echo esc_html($jsst_table); ?></code></span>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($jsst_api)) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('And over HTTP', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <dl class="jsst-facts">
                            <div>
                                <dt><?php echo esc_html(__('REST namespace', 'js-support-ticket')); ?></dt>
                                <dd><code><?php echo esc_html($jsst_api['namespace']); ?></code></dd>
                            </div>
                            <div>
                                <dt><?php echo esc_html(__('Operations', 'js-support-ticket')); ?></dt>
                                <dd class="jsst-num"><?php echo esc_html($jsst_api['operations']); ?></dd>
                            </div>
                            <div>
                                <dt><?php echo esc_html(__('Answering', 'js-support-ticket')); ?></dt>
                                <dd><?php echo esc_html(!empty($jsst_api['enabled']) ? __('yes', 'js-support-ticket') : __('switched off', 'js-support-ticket')); ?></dd>
                            </div>
                        </dl>
                        <p class="jsst-fhelp"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=api')); ?>"><?php echo esc_html(__('The API screen lists every operation with the permission that governs it, and links to the machine-readable description.', 'js-support-ticket')); ?></a></p>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
