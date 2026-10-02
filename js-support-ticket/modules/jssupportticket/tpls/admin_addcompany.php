<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Add / edit a company. (Roadmap 5.5-COM-06)
 *
 * The record first; once it exists, the people named on it and the account
 * numbers counted from their tickets follow underneath.
 */
if (!class_exists('JSSTcompanies')) {
    echo esc_html(__('This is not available.', 'js-support-ticket'));
    return;
}
$jsst_edit      = isset(jssupportticket::$jsst_data['companyedit']) ? jssupportticket::$jsst_data['companyedit'] : false;
$jsst_people    = isset(jssupportticket::$jsst_data['companypeople']) ? jssupportticket::$jsst_data['companypeople'] : array();
$jsst_summary   = isset(jssupportticket::$jsst_data['companysummary']) ? jssupportticket::$jsst_data['companysummary'] : false;
$jsst_editid    = ($jsst_edit && isset($jsst_edit->id)) ? (int) $jsst_edit->id : 0;
$jsst_saveurl   = admin_url('admin.php?page=jssupportticket&task=savecompany');
$jsst_personurl = admin_url('admin.php?page=jssupportticket&task=savecompanyperson');
$jsst_listurl   = admin_url('admin.php?page=jssupportticket&jstlay=companies');
$jsst_dateformat = jssupportticket::$_config['date_format'];

/* One company's field, or its default when a new one is being written.
   Guarded because a template is a file that can be included twice. */
if (!function_exists('jsst_company_value')) {
    function jsst_company_value($jsst_edit, $jsst_field, $jsst_default = '') {
        if (!is_object($jsst_edit) || !isset($jsst_edit->{$jsst_field}) || $jsst_edit->{$jsst_field} === null) {
            return $jsst_default;
        }
        return $jsst_edit->{$jsst_field};
    }
}
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_editid > 0 ? __('Edit Company', 'js-support-ticket') : __('Add Company', 'js-support-ticket'),
            'sub'    => $jsst_editid > 0 ? $jsst_edit->name : '',
            'crumbs' => array(array('text' => __('Companies', 'js-support-ticket'), 'url' => $jsst_listurl)),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url($jsst_saveurl); ?>">
                <?php wp_nonce_field('jsst-company'); ?>
                <input type="hidden" name="form_request" value="jssupportticket" />
                <input type="hidden" name="companyid" value="<?php echo esc_attr($jsst_editid); ?>" />
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The company', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="coname"><?php echo esc_html(__('Name', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><input type="text" id="coname" name="coname" value="<?php echo esc_attr(jsst_company_value($jsst_edit, 'name')); ?>" /></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('What a report will call them. Renaming this later changes one row and every screen agrees.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="cotier"><?php echo esc_html(__('Customer tier', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><input type="text" id="cotier" name="cotier" value="<?php echo esc_attr(jsst_company_value($jsst_edit, 'tier')); ?>" /></div>
                                </div>
                                <p class="jsst-fhelp"><?php echo esc_html(__('The name of a tier your service-level policies already match on. It is named here and defined there — a promise lives in one place, with the policy that makes it.', 'js-support-ticket')); ?></p>
                                <div class="jsst-frow jsst-frow-lg">
                                    <label class="jsst-flabel" for="codomains"><?php echo esc_html(__('E-mail domains', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><textarea id="codomains" name="codomains" rows="3"><?php echo esc_textarea(jsst_company_value($jsst_edit, 'domains')); ?></textarea></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('One per line, or separated by commas. Everybody writing in from these belongs to this company unless another company has named them individually. A mail provider is refused with the reason — it would put every customer with a free address inside this account.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></span>
                                    <label class="jsst-check" for="costatus">
                                        <input type="checkbox" id="costatus" name="costatus" value="1" <?php checked($jsst_editid === 0 || (int) jsst_company_value($jsst_edit, 'status', 1) === JSSTcompanies::STATUS_ACTIVE); ?> />
                                        <span><?php echo esc_html(__('Active', 'js-support-ticket')); ?></span>
                                    </label>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('An archived company keeps its history and its numbers, and matches nothing new — no domain of theirs resolves and no supervisor of theirs reads a colleague.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The contract', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="coref"><?php echo esc_html(__('Contract reference', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><input type="text" id="coref" name="coref" value="<?php echo esc_attr(jsst_company_value($jsst_edit, 'contractref')); ?>" /></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="costart"><?php echo esc_html(__('Contract starts', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><input type="date" id="costart" name="costart" value="<?php echo esc_attr(jsst_company_value($jsst_edit, 'contractstart')); ?>" /></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="coend"><?php echo esc_html(__('Contract ends', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><input type="date" id="coend" name="coend" value="<?php echo esc_attr(jsst_company_value($jsst_edit, 'contractend')); ?>" /></div>
                                </div>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Both dates may be left empty, and a company with neither counts as in force. Nothing here refuses a ticket — the dates answer "were they entitled to that on the day they asked?".', 'js-support-ticket')); ?></p>
                                <div class="jsst-frow jsst-frow-full">
                                    <label class="jsst-flabel" for="conotes"><?php echo esc_html(__('Notes', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><textarea id="conotes" name="conotes" rows="3"><?php echo esc_textarea(jsst_company_value($jsst_edit, 'notes')); ?></textarea></div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="jsst-formfoot">
                        <?php if ($jsst_editid > 0) { ?>
                            <a class="jsst-btn jsst-btn-danger" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=deletecompany&action=jstask&companyid=' . $jsst_editid), 'jsst-company-delete')); ?>"
                               onclick="return confirm('<?php echo esc_js(__('Delete this company? Their tickets are untouched — the company was never written on to one — and they become ordinary tickets again.', 'js-support-ticket')); ?>');"><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></a>
                        <?php } else { ?>
                            <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <?php } ?>
                        <a class="jsst-btn" href="<?php echo esc_url($jsst_listurl); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html(__('Save', 'js-support-ticket')); ?></button>
                    </div>
                </div>
            </form>

            <?php if ($jsst_editid > 0) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('The people in it', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Somebody named here belongs to this company whatever their address ends in — and does not belong to any other, even one whose domain matches theirs.', 'js-support-ticket')); ?></p>
                    </div>
                    <?php if (empty($jsst_people)) { ?>
                        <?php JSSTlayout::adminEmpty(__('Nobody named individually', 'js-support-ticket'), __('The domains above are doing all the work. That is a perfectly ordinary setup — name somebody only when they are on the wrong domain, or when they need to read their colleagues\' tickets.', 'js-support-ticket')); ?>
                    <?php } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table jsst-table-dense">
                                <thead>
                                    <tr>
                                        <th class="jsst-col-name"><?php echo esc_html(__('Address', 'js-support-ticket')); ?></th>
                                        <th class="jsst-col-fit"><?php echo esc_html(__('What they are', 'js-support-ticket')); ?></th>
                                        <th class="jsst-col-fit"><?php echo esc_html(__('Named', 'js-support-ticket')); ?></th>
                                        <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_people AS $jsst_person) { ?>
                                    <tr>
                                        <th scope="row" class="jsst-col-name"><?php echo esc_html($jsst_person->email); ?></th>
                                        <td class="jsst-col-fit"><?php echo esc_html($jsst_person->personrole === JSSTcompanies::ROLE_SUPERVISOR
                                                ? __('Supervisor — reads colleagues\' tickets', 'js-support-ticket')
                                                : __('Contact', 'js-support-ticket')); ?></td>
                                        <td class="jsst-col-fit"><?php echo esc_html($jsst_person->created ? date_i18n($jsst_dateformat, strtotime($jsst_person->created)) : ''); ?></td>
                                        <td class="jsst-col-act">
                                            <span class="jsst-rowactions">
                                                <a class="jsst-act jsst-act-danger" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=removecompanyperson&action=jstask&companyid=' . $jsst_editid . '&coemail=' . rawurlencode($jsst_person->email)), 'jsst-company-person-remove')); ?>"><?php echo esc_html(__('Remove', 'js-support-ticket')); ?></a>
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                    <form class="jsst-card-foot" method="post" action="<?php echo esc_url($jsst_personurl); ?>">
                        <?php wp_nonce_field('jsst-company-person'); ?>
                        <input type="hidden" name="form_request" value="jssupportticket" />
                        <input type="hidden" name="companyid" value="<?php echo esc_attr($jsst_editid); ?>" />
                        <div class="jsst-formgrid">
                            <div class="jsst-frow jsst-frow-md jsst-frow-last">
                                <label class="jsst-flabel" for="coemail"><?php echo esc_html(__('E-mail address', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval"><input type="email" id="coemail" name="coemail" value="" /></div>
                            </div>
                            <div class="jsst-frow jsst-frow-sm jsst-frow-last">
                                <label class="jsst-flabel" for="corole"><?php echo esc_html(__('What they are', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select id="corole" name="corole">
                                        <option value="<?php echo esc_attr(JSSTcompanies::ROLE_CONTACT); ?>"><?php echo esc_html(__('Contact', 'js-support-ticket')); ?></option>
                                        <option value="<?php echo esc_attr(JSSTcompanies::ROLE_SUPERVISOR); ?>"><?php echo esc_html(__('Supervisor', 'js-support-ticket')); ?></option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="jsst-btn"><?php echo esc_html(__('Name them', 'js-support-ticket')); ?></button>
                            <p class="jsst-fhelp"><?php echo esc_html(__('A contact belongs to the company and sees only their own tickets. A supervisor also sees every ticket this company has raised in their My Tickets list, including their colleagues\' — read-only: they cannot reply to, close or reopen one that is not theirs. It needs an account on the site with this exact e-mail address.', 'js-support-ticket')); ?></p>
                        </div>
                    </form>
                </div>

                <?php if ($jsst_summary) { ?>
                    <div class="jsst-card">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('The account', 'js-support-ticket')); ?></h2>
                            <p class="jsst-card-sub"><?php echo esc_html(__('Counted from their tickets each time this page is drawn.', 'js-support-ticket')); ?></p>
                        </div>
                        <div class="jsst-card-body">
                            <div class="jsst-metrics">
                                <span class="jsst-metric">
                                    <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_summary['tickets']); ?></span>
                                    <span class="jsst-metric-label"><?php echo esc_html(__('Tickets', 'js-support-ticket')); ?></span>
                                </span>
                                <span class="jsst-metric">
                                    <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_summary['openticket']); ?></span>
                                    <span class="jsst-metric-label"><?php echo esc_html(__('Open', 'js-support-ticket')); ?></span>
                                </span>
                                <span class="jsst-metric">
                                    <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_summary['people']); ?></span>
                                    <span class="jsst-metric-label"><?php echo esc_html(__('People writing in', 'js-support-ticket')); ?></span>
                                </span>
                                <span class="jsst-metric<?php if ($jsst_summary['spend'] === null) { ?> jsst-metric-quiet<?php } ?>">
                                    <span class="jsst-metric-value jsst-num"><?php
                                        echo esc_html($jsst_summary['spend'] === null
                                            ? '—'
                                            : trim($jsst_summary['currency'] . ' ' . number_format_i18n((float) $jsst_summary['spend'], 2))); ?></span>
                                    <span class="jsst-metric-label"><?php echo esc_html(__('Spend', 'js-support-ticket')); ?></span>
                                </span>
                            </div>
                            <?php if ($jsst_summary['spend'] === null) { ?>
                                <p class="jsst-hint"><?php echo esc_html(__('A dash rather than a zero: nothing installed here knows what this company has paid. The WooCommerce and Easy Digital Downloads integrations answer this when they are running.', 'js-support-ticket')); ?></p>
                            <?php } ?>
                            <?php if ($jsst_summary['firstseen']) { ?>
                                <p class="jsst-hint"><?php echo esc_html(sprintf(
                                    /* translators: 1: date of first ticket, 2: date of last activity */
                                    __('First wrote in %1$s, last heard from %2$s.', 'js-support-ticket'),
                                    date_i18n($jsst_dateformat, strtotime($jsst_summary['firstseen'])),
                                    date_i18n($jsst_dateformat, strtotime($jsst_summary['lastactivity'])))); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
