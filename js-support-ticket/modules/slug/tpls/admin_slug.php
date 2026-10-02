<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script('responsivetablejs',JSST_PLUGIN_URL.'includes/js/responsivetable.js', array(), jssupportticket::$_config['productversion'], true);
JSSTmessage::getMessage();
?>
<!-- main wrapper -->
<div id="jsstadmin-wrapper">
    <?php JSSTlayout::adminPopupShell(); ?>
    <!-- left menu -->
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <!-- top bar -->
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Slug','js-support-ticket'),
            'actions' => array(array('text' => __('Reset All','js-support-ticket'), 'url' => wp_nonce_url(admin_url('admin.php?page=slug&task=resetallslugs&action=jstask'),'reset-all-slugs'), 'style' => 'danger')),
        )); ?>
        <?php
        $jsst_jssupportticket_js ='
            /*Function to Show popUp,Reset*/
            var slug_for_edit = 0;
            jQuery(document).ready(function () {
                jQuery("div#userpopupblack").click(function () {
                    closePopup();
                });
            });

            function resetFrom() {// Resest Form
                jQuery("input#slug").val("");
                jQuery("form#jsstadmin-form").submit();
            }

            function showPopupAndSetValues(nonce, id,slug) {//Showing PopUp
                slug = jQuery("td#td_"+id).html();
                slug_for_edit = id;
                jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "slug", task: "getOptionsForEditSlug",id:id ,slug:slug, "_wpnonce": nonce}, function (data) {
                    if (data) {
                        var d = jQuery.parseJSON(data);
                        jQuery("div#userpopupblack").css("display", "block");
                        jQuery("div#userpopup").html(jsstDecodeHTML(d));
                        jQuery("div#userpopup").slideDown("slow");
                    }
                });
            }

            function closePopup() {// Close PopUp
                jQuery("div#userpopup").slideUp("slow");
                setTimeout(function () {
                    jQuery("div#userpopupblack").hide();
                    jQuery("div#userpopup").html("");
                }, 700);
            }

            function getFieldValue() {
                var slugvalue = jQuery("#slugedit").val();
                jQuery("input#"+slug_for_edit).val(slugvalue);
                jQuery("td#td_"+slug_for_edit).html(slugvalue);
                closePopup();
            }
        ';
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        ?>
        <!-- page content -->
        <div id="jsstadmin-data-wrp">

            <?php /* The two prefixes are settings, not filters. They were two
                     `js-filter-form` rows above the search box, so the screen
                     opened with three identical-looking bars and no way to tell
                     which one searched and which one saved. */ ?>
            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Prefixes', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('Put in front of an address the plugin builds. Each one saves on its own.', 'js-support-ticket')); ?></p>
                </div>
                <div class="jsst-card-body">
                    <div class="jsst-formgrid">
                        <form class="jsst-frow jsst-frow-md" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug&task=savehomeprefix"),"save-home-prefix")); ?>">
                            <label class="jsst-flabel" for="prefix"><?php echo esc_html(__('Home slug prefix','js-support-ticket')); ?></label>
                            <div class="jsst-fval jsst-inline">
                                <?php echo wp_kses(JSSTformfield::text('prefix', jssupportticket::$_config['home_slug_prefix'], array('class' => 'jsst-input')),JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Save','js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary jsst-inline-save')),JSST_ALLOWED_TAGS); ?>
                            </div>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Added to the slug on links that point at the home page.','js-support-ticket')); ?></p>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'),JSST_ALLOWED_TAGS); ?>
                        </form>
                        <form class="jsst-frow jsst-frow-md" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug&task=saveprefix"),"save-prefix")); ?>">
                            <label class="jsst-flabel" for="prefix"><?php echo esc_html(__('Slug prefix','js-support-ticket')); ?></label>
                            <div class="jsst-fval jsst-inline">
                                <?php echo wp_kses(JSSTformfield::text('prefix', jssupportticket::$_config['slug_prefix'], array('class' => 'jsst-input')),JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Save','js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary jsst-inline-save')),JSST_ALLOWED_TAGS); ?>
                            </div>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Added only when a slug would otherwise clash with one already in use.','js-support-ticket')); ?></p>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'),JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </div>
            </div>

            <form class="jsst-filterbar" name="jsstadmin-form" id="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug"),"slug")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('slug', jssupportticket::$jsst_data['slug'], array('class' => 'jsst-search-input', 'placeholder' => esc_html(__('Search slugs','js-support-ticket')))),JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Search','js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset','js-support-ticket')), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')),JSST_ALLOWED_TAGS); ?>
            </form>

            <?php
                if (!empty(jssupportticket::$jsst_data[0])) {
                    $jsst_pagenum = JSSTrequest::getVar('pagenum', 'get', 1);
                    ?>
                    <form id="js-list-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug&task=saveSlug"),"save-slug")); ?>">
                        <div class="jsst-card">
                            <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th class="jsst-col-name"><?php echo esc_html(__('Slug','js-support-ticket')); ?></th>
                                        <th class="jsst-col-say"><?php echo esc_html(__('Description','js-support-ticket')); ?></th>
                                        <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action','js-support-ticket')); ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (jssupportticket::$jsst_data[0] as $jsst_row){
                                        $jsst_nonce = wp_create_nonce("get-options-for-edit-slug-".$jsst_row->id); ?>
                                        <tr>
                                            <?php /* The cell keeps id="td_<id>": showPopupAndSetValues()
                                                     reads the current slug out of it and getFieldValue()
                                                     writes the edited one back, so the table shows the
                                                     change before the form is posted. */ ?>
                                            <th scope="row" class="jsst-col-name jsst-mono" id="<?php echo 'td_'.esc_attr($jsst_row->id);?>"><?php echo esc_html($jsst_row->slug);?></th>
                                            <td class="jsst-col-say"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_row->description));?></td>
                                            <td class="jsst-col-act">
                                                <span class="jsst-rowactions">
                                                    <a class="jsst-act" href="#" onclick="showPopupAndSetValues('<?php echo esc_js($jsst_nonce); ?>' ,<?php echo esc_js($jsst_row->id); ?>); return false;"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php echo wp_kses(JSSTformfield::hidden($jsst_row->id, $jsst_row->slug),JSST_ALLOWED_TAGS);?>
                                    <?php } ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <?php echo wp_kses(JSSTformfield::hidden('task', ''),JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('pagenum', ($jsst_pagenum > 1) ? $jsst_pagenum : ''),JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'),JSST_ALLOWED_TAGS); ?>
                        <div class="jsst-orderbar">
                            <span class="jsst-orderbar-note"><?php echo esc_html(__('Saves the slugs on this page only.','js-support-ticket')); ?></span>
                            <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Save','js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')),JSST_ALLOWED_TAGS); ?>
                        </div>
                    </form>
                    <?php JSSTlayout::adminPager(jssupportticket::$jsst_data[1]); ?>
                <?php } else { ?>
                    <div class="jsst-card">
                        <?php JSSTlayout::adminEmpty(
                            __('No slugs found.', 'js-support-ticket'),
                            __('A slug is the readable part of an address the plugin builds for a page.', 'js-support-ticket')
                        ); ?>
                    </div>
                <?php }
            ?>
        </div>
    </div>
</div>
