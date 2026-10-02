<?php
if(!defined('ABSPATH'))
    die('Restricted Access');
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
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Products', 'js-support-ticket'),
            'count'   => isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0,
            'actions' => array(
                array('text' => __('Add Product', 'js-support-ticket'), 'url' => admin_url('admin.php?page=product&jstlay=addproduct'), 'icon' => 'plus'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=product&jstlay=products"),"products")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('title', jssupportticket::$jsst_data['filter']['title'], array('placeholder' => esc_html(__('Search products', 'js-support-ticket')),'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
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
                            <th class="jsst-col-name"><?php echo esc_html(__('Product', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $jsst_product) {
                            $jsst_editurl = admin_url('admin.php?page=product&jstlay=addproduct&jssupportticketid=' . $jsst_product->id);
                            ?>
                            <tr id="id_<?php echo esc_attr($jsst_product->id); ?>">
                                <td class="jsst-col-grab">
                                    <span class="jsst-grab" role="img" aria-label="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>" title="<?php echo esc_attr(__('Drag to reorder','js-support-ticket')); ?>"></span>
                                </td>
                                <th scope="row" class="jsst-col-name">
                                    <a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_product->product)); ?></a>
                                </th>
                                <td class="jsst-col-fit">
                                    <a class="jsst-mark<?php echo ($jsst_product->status == 1) ? ' jsst-mark-on' : ''; ?>" title="<?php echo esc_attr(__('Change status','js-support-ticket')); ?>" href="<?php echo esc_url(wp_nonce_url('?page=product&task=changestatus&action=jstask&productid='. $jsst_product->id,'change-status-'.$jsst_product->id));?>"><?php echo ($jsst_product->status == 1) ? esc_html(__('Active', 'js-support-ticket')) : esc_html(__('Disabled', 'js-support-ticket')); ?></a>
                                </td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=product&task=deleteproduct&action=jstask&productid='.esc_attr($jsst_product->id),'delete-product-'.$jsst_product->id));?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
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
                    <?php echo wp_kses(JSSTformfield::hidden('ordering_for', 'product'), JSST_ALLOWED_TAGS); ?>
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
                        __('No products found.', 'js-support-ticket'),
                        __('A product lets a customer say what their ticket is about.', 'js-support-ticket'),
                        __('Add product', 'js-support-ticket'),
                        admin_url('admin.php?page=product&jstlay=addproduct')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
