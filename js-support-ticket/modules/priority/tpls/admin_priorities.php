<?php
if(!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Priorities - the reference list screen for the admin design system.
 *
 * A filter line, then one panel of rows: the name and its detail stacked,
 * small values in the middle, the row verbs at the end. The default is a
 * radio because exactly one priority can be default.
 *
 * Rows are still `id_<n>` so saveordering receives what it always has.
 */
$jsst_jssupportticket_js ="
    function resetFrom() {
        document.getElementById('title').value = '';
        document.getElementById('jssupportticketform').submit();
    }
    jQuery(document).ready(function () {
        jQuery('table.jsst-table tbody').sortable({
            handle : '.jsst-grab',
            axis : 'y',
            update  : function () {
                jQuery('.jsst-orderbar').slideDown('slow');
                var abc =  jQuery('table.jsst-table tbody').sortable('serialize');
                jQuery('input#fields_ordering_new').val(abc);
            }
        });
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
JSSTmessage::getMessage();

$jsst_hasoverdue = in_array('overdue', jssupportticket::$_active_addons);
$jsst_total = isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0;
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Priorities', 'js-support-ticket'),
            'count'   => $jsst_total,
            'actions' => array(
                array('text' => __('Add Priority', 'js-support-ticket'), 'url' => admin_url('admin.php?page=priority&jstlay=addpriority'), 'icon' => 'plus'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=priority&jstlay=priorities"),"priorities")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('title', jssupportticket::$jsst_data['filter']['title'], array('placeholder' => esc_html(__('Search priorities', 'js-support-ticket')),'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button(esc_html(__('Reset', 'js-support-ticket')), esc_html(__('Reset', 'js-support-ticket')), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
                <div class="jsst-filterbar-end">
                    <label class="jsst-filterbar-label" for="pagesize"><?php echo esc_html(__('Rows per page', 'js-support-ticket')); ?></label>
                    <?php echo wp_kses(JSSTformfield::select('pagesize', array((object) array('id'=>20,'text'=>20), (object) array('id'=>50,'text'=>50), (object) array('id'=>100,'text'=>100)), jssupportticket::$jsst_data['filter']['pagesize'], '', array('class' => 'jsst-select','onchange'=>'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>

            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=jssupportticket&task=saveordering"),"save-ordering")); ?>">
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-grab"><span class="screen-reader-text"><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></span></th>
                            <th class="jsst-col-name"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></th>
                            <?php if($jsst_hasoverdue){ ?>
                                <th class="jsst-col-fit"><?php echo esc_html(__('Ticket Overdue', 'js-support-ticket')); ?></th>
                            <?php } ?>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Public', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $jsst_pagenum = JSSTrequest::getVar('pagenum', 'get', 1);
                        foreach (jssupportticket::$jsst_data[0] AS $jsst_priority) {
                            $jsst_interval = (int) $jsst_priority->overdueinterval;
                            if ($jsst_priority->overduetypeid == 1) {
                                $jsst_ticketoverduetype = _n('Day', 'Days', $jsst_interval, 'js-support-ticket');
                            } else {
                                $jsst_ticketoverduetype = _n('Hour', 'Hours', $jsst_interval, 'js-support-ticket');
                            }
                            $jsst_colour = $jsst_priority->prioritycolour;
                            $jsst_editurl = '?page=priority&jstlay=addpriority&jssupportticketid='.$jsst_priority->id;
                            $jsst_url = '?page=priority&task=makedefault&action=jstask&priorityid='.esc_attr($jsst_priority->id);
                            if($jsst_pagenum > 1){
                                $jsst_url .= '&pagenum=' . $jsst_pagenum;
                            }
                            ?>
                            <tr id="id_<?php echo esc_attr($jsst_priority->id); ?>"<?php echo ($jsst_priority->isdefault == 1) ? ' class="jsst-row-default"' : ''; ?>>
                                <td class="jsst-col-grab">
                                    <span class="jsst-grab" role="img" aria-label="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>" title="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>"></span>
                                </td>
                                <th scope="row" class="jsst-col-name">
                                    <span class="jsst-ident">
                                        <span class="jsst-swatch" style="background:<?php echo esc_attr($jsst_colour); ?>" aria-hidden="true"></span>
                                        <span class="jsst-ident-text">
                                            <a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_priority->priority)); ?></a>
                                            <span class="jsst-table-sub jsst-mono"><?php echo esc_html($jsst_colour); ?></span>
                                        </span>
                                    </span>
                                </th>
                                <?php if($jsst_hasoverdue){ ?>
                                    <td class="jsst-col-fit">
                                        <?php if ($jsst_priority->overdueinterval !== '' && $jsst_priority->overdueinterval !== null) { ?>
                                            <span class="jsst-chip"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_priority->overdueinterval)); ?> <?php echo esc_html($jsst_ticketoverduetype); ?></span>
                                        <?php } else { ?>
                                            <span class="jsst-dash" aria-hidden="true">&mdash;</span>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                <td class="jsst-col-fit">
                                    <?php if ($jsst_priority->ispublic == 1) { ?>
                                        <span class="jsst-mark jsst-mark-on"><?php echo esc_html(__('Public', 'js-support-ticket')); ?></span>
                                    <?php } else { ?>
                                        <span class="jsst-mark"><?php echo esc_html(__('Private', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                </td>
                                <td class="jsst-col-fit">
                                    <?php if ($jsst_priority->isdefault == 1) { ?>
                                        <span class="jsst-radio is-on" role="img" aria-label="<?php echo esc_attr(__('Default','js-support-ticket')); ?>"><span class="jsst-radio-dot"></span><span class="jsst-radio-label"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></span></span>
                                    <?php } else { ?>
                                        <a class="jsst-radio" href="<?php echo esc_url(wp_nonce_url($jsst_url, 'make-default-'.$jsst_priority->id)); ?>" title="<?php echo esc_attr(__('Make this the default','js-support-ticket')); ?>"><span class="jsst-radio-dot"></span><span class="jsst-radio-label"><?php echo esc_html(__('Set default', 'js-support-ticket')); ?></span></a>
                                    <?php } ?>
                                </td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=priority&task=deletepriority&action=jstask&priorityid='.esc_attr($jsst_priority->id),'delete-priority-'.$jsst_priority->id));?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                    <?php echo wp_kses(JSSTformfield::hidden('fields_ordering_new', '123'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('ordering_for', 'priority'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('pagenum_for_ordering', JSSTrequest::getVar('pagenum', 'get', 1)), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-orderbar" style="display: none;">
                        <span class="jsst-orderbar-note"><?php echo esc_html(__('You changed the order of this list.', 'js-support-ticket')); ?></span>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Ordering', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </form>
                <?php JSSTlayout::adminPager(jssupportticket::$jsst_data[1]); ?>
            <?php } else { ?>
                <div class="jsst-card">
                    <?php JSSTlayout::adminEmpty(
                        __('No priorities found.', 'js-support-ticket'),
                        __('A priority is how urgent a ticket is, and the colour agents see against it.', 'js-support-ticket'),
                        __('Add priority', 'js-support-ticket'),
                        admin_url('admin.php?page=priority&jstlay=addpriority')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
