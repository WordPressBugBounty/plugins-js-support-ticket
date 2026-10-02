<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Retiring MailChimp. (Roadmap 5.5-SEC-02)
 *
 * The same shape as the other retirement screens in this product: what you
 * have, what replaces it, and nothing switched off on your behalf.
 */
if (!class_exists('JSSTconsent')) {
    echo esc_html(__('This is not available.', 'js-support-ticket'));
    return;
}
$jsst_survey   = isset(jssupportticket::$jsst_data['consentsurvey']) ? jssupportticket::$jsst_data['consentsurvey'] : array();
$jsst_settings = isset(jssupportticket::$jsst_data['consentsettings']) ? jssupportticket::$jsst_data['consentsettings'] : array();
$jsst_history  = isset(jssupportticket::$jsst_data['consenthistory']) ? jssupportticket::$jsst_data['consenthistory'] : array();
$jsst_recipes  = isset($jsst_survey['recipes']) ? $jsst_survey['recipes'] : array();
$jsst_dateformat = jssupportticket::$_config['date_format'];
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Marketing Consent', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <p class="jsst-lede">
                <?php echo esc_html(__('The newsletter box on the registration form used to make one API call to one company and throw the answer away. What it actually produces is a consent — somebody saying yes, at a moment, having been shown a particular sentence — and that is a record you may have to produce years later. It is kept here now, announced as an event anything can act on, and passed to whichever marketing tool you use.', 'js-support-ticket')); ?>
            </p>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Where this site stands', 'js-support-ticket')); ?></h2>
                </div>
                <div class="jsst-card-body">
                    <div class="jsst-metrics">
                        <span class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_survey['granted']) ? $jsst_survey['granted'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('People who have said yes', 'js-support-ticket')); ?></span>
                        </span>
                        <span class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_survey['recorded']) ? $jsst_survey['recorded'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Answers recorded', 'js-support-ticket')); ?></span>
                        </span>
                    </div>
                    <p class="jsst-status-note"><?php
                        if (!empty($jsst_survey['addon'])) {
                            echo esc_html(__('The MailChimp add-on is still active. It is no longer doing anything — core reads the checkbox now — and you can deactivate it whenever you like. Its API key and list id stay where they are and the Mailchimp recipe below keeps using them, so your subscribers are unaffected.', 'js-support-ticket'));
                        } else {
                            echo esc_html(__('The MailChimp add-on is not active on this site. The checkbox on the registration form works regardless — it is core\'s now.', 'js-support-ticket'));
                        } ?></p>
                    <p class="jsst-hint"><?php echo esc_html(__('Nothing here deletes a consent, empties a mailing list or switches an add-on off. A consent withdrawn is written down as a withdrawal rather than removed, because "they opted out on the 4th" is a fact somebody may have to prove exactly as much as the opting in.', 'js-support-ticket')); ?></p>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('What is asked, and where the answer goes', 'js-support-ticket')); ?></h2>
                </div>
                <div class="jsst-card-body">
                    <form class="jsst-form" method="post" action="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&task=savemarketing')); ?>">
                        <?php wp_nonce_field('jsst-marketing'); ?>
                        <input type="hidden" name="form_request" value="jssupportticket" />
                        <div class="jsst-formgrid">
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="statement"><?php echo esc_html(__('The sentence beside the box', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval"><textarea id="statement" name="statement" rows="2"><?php echo esc_textarea(isset($jsst_settings['statement']) ? $jsst_settings['statement'] : ''); ?></textarea></div>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Kept on every consent as it stood at the moment that person agreed to it. Rewriting it here changes what is asked from now on and never what anybody has already agreed to.', 'js-support-ticket')); ?></p>
                            </div>
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="recipe"><?php echo esc_html(__('Carry it onward with', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select id="recipe" name="recipe">
                                        <?php foreach ($jsst_recipes AS $jsst_key => $jsst_recipe) { ?>
                                            <option value="<?php echo esc_attr($jsst_key); ?>" <?php selected(isset($jsst_settings['recipe']) ? $jsst_settings['recipe'] : '', $jsst_key); ?> <?php disabled(empty($jsst_recipe['available'])); ?>>
                                                <?php echo esc_html($jsst_recipe['label']);
                                                if (empty($jsst_recipe['available'])) {
                                                    echo ' — ' . esc_html(__('not available on this site', 'js-support-ticket'));
                                                } ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php foreach ($jsst_recipes AS $jsst_key => $jsst_recipe) { ?>
                                    <p class="jsst-fhelp"><strong><?php echo esc_html($jsst_recipe['label']); ?></strong> — <?php echo esc_html($jsst_recipe['note']); ?></p>
                                <?php } ?>
                            </div>
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="listid"><?php echo esc_html(__('FluentCRM list', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval"><input type="text" id="listid" name="listid" value="<?php echo esc_attr(isset($jsst_settings['listid']) ? $jsst_settings['listid'] : ''); ?>" /></div>
                                <p class="jsst-fhelp"><?php echo esc_html(__('One or more list ids, separated by commas. Only used by the FluentCRM recipe — Mailchimp uses the list the add-on was already configured with.', 'js-support-ticket')); ?></p>
                            </div>
                        </div>
                        <div class="jsst-btnrow">
                            <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html(__('Save', 'js-support-ticket')); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('What people have answered', 'js-support-ticket')); ?></h2>
            <div class="jsst-card">
                <div class="jsst-card-body jsst-card-flush">
                    <?php if (empty($jsst_history)) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('Nothing recorded yet', 'js-support-ticket')); ?></p>
                            <p class="jsst-empty-text"><?php echo esc_html(__('Nobody has answered the newsletter question since this release. Answers given before it were never recorded anywhere — the add-on made an API call and kept nothing — so there is nothing to bring across.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo esc_html(__('When', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('Who', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('Answer', 'js-support-ticket')); ?></th>
                                        <th scope="col"><?php echo esc_html(__('What they were shown', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_history AS $jsst_row) { ?>
                                    <tr>
                                        <td><?php echo esc_html(date_i18n($jsst_dateformat . ' H:i', strtotime($jsst_row->created))); ?></td>
                                        <th scope="row"><?php echo esc_html($jsst_row->email); ?></th>
                                        <td><?php echo esc_html(((int) $jsst_row->granted === 1)
                                            ? __('yes', 'js-support-ticket') : __('no', 'js-support-ticket')); ?></td>
                                        <td><?php echo esc_html($jsst_row->statement); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>
</div>
