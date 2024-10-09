<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script('responsivetablejs',JSST_PLUGIN_URL.'includes/js/responsivetable.js');
JSSTmessage::getMessage();
?>
<!-- main wrapper -->
<div id="jsstadmin-wrapper">
    <div id="userpopupblack" style="display:none;"></div>
    <div id="userpopup" style="display:none;"></div>
    <!-- left menu -->
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <!-- top bar -->
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Slug','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_html(__('Help','js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Slug','js-support-ticket')); ?></h1>
            <a class="jsstadmin-add-link button" title="<?php echo esc_html(__('reset','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=slug&task=resetallslugs&action=jstask")); ?>">
                <?php echo esc_html(__('Reset All','js-support-ticket')); ?>
            </a>
        </div>
        <?php
        $jssupportticket_js ='
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

            function showPopupAndSetValues(id,slug) {//Showing PopUp
                slug = jQuery("td#td_"+id).html();
                slug_for_edit = id;
                jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "slug", task: "getOptionsForEditSlug",id:id ,slug:slug, "_wpnonce":"'. esc_attr(wp_create_nonce("get-options-for-edit-slug")).'"}, function (data) {
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
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
        <!-- page content -->
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <!-- filter form -->
            <form class="js-filter-form slug-configform" name="jsstadmin-form" id="conjsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug&task=savehomeprefix"),"save-home-prefix")); ?>">
                <?php echo wp_kses(JSSTformfield::text('prefix', jssupportticket::$_config['home_slug_prefix'], array('class' => 'inputbox js-form-input-field', 'placeholder' => esc_html(__('Home Slug','js-support-ticket')).' '. esc_html(__('Prefix','js-support-ticket')))),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Save','js-support-ticket')), array('class' => 'button js-form-search')),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'),JSST_ALLOWED_TAGS); ?>
                <div class="js-form-help-text">
                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/view-job-information.png" />
                    <?php echo esc_html(__('This prefix will be added to slug incase of homepage links','js-support-ticket')); ?>
                </div>
            </form>
            <!-- filter form -->
            <form class="js-filter-form slug-configform" name="jsstadmin-form" id="conjsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug&task=saveprefix"),"save-prefix")); ?>">
                <?php echo wp_kses(JSSTformfield::text('prefix', jssupportticket::$_config['slug_prefix'], array('class' => 'inputbox js-form-input-field', 'placeholder' => esc_html(__('Slug','js-support-ticket')).' '. esc_html(__('Prefix','js-support-ticket')))),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Save','js-support-ticket')), array('class' => 'button js-form-search')),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'),JSST_ALLOWED_TAGS); ?>
                <div class="js-form-help-text">
                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/view-job-information.png" />
                    <?php echo esc_html(__('This prefix will be added to slug incase of conflict','js-support-ticket')); ?>
                </div>
            </form>
            <!-- filter form -->
            <form class="js-filter-form" name="jsstadmin-form" id="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug"),"slug")); ?>">
                <?php echo wp_kses(JSSTformfield::text('slug', jssupportticket::$_data['slug'], array('class' => 'inputbox js-form-input-field', 'placeholder' => esc_html(__('Search By Slug','js-support-ticket')))),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Search','js-support-ticket')), array('class' => 'button js-form-search')),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset','js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')),JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'),JSST_ALLOWED_TAGS); ?>
            </form>
            <?php
                if (!empty(jssupportticket::$_data[0])) {
                    ?>
                    <form id="js-list-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=slug&task=saveSlug"),"save-slug")); ?>">
                        <table id="js-support-ticket-table" class="js-support-ticket-table">
                            <thead>
                                <tr class="js-support-ticket-table-heading">
                                    <th class="left">
                                        <?php echo esc_html(__('Slug List','js-support-ticket')); ?>
                                    </th>
                                    <th class="left">
                                        <?php echo esc_html(__('Description','js-support-ticket')); ?>
                                    </th>
                                    <th>
                                        <?php echo esc_html(__('Action','js-support-ticket')); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $pagenum = JSSTrequest::getVar('pagenum', 'get', 1);
                                    $pageid = ($pagenum > 1) ? '&pagenum=' . $pagenum : '';
                                    foreach (jssupportticket::$_data[0] as $row){
                                        ?>
                                        <tr>
                                            <td class="left" id="<?php echo 'td_'.esc_attr($row->id);?>">
                                                <?php echo esc_html($row->slug);?>
                                            </td>
                                            <td class="left">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($row->description));?>
                                            </td>
                                            <td>
                                                <a class="action-btn" href="#" onclick="showPopupAndSetValues(<?php echo esc_js($row->id); ?>)" title="<?php echo esc_html(__('edit','js-support-ticket')); ?>">
                                                    <img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit.png" alt="<?php echo esc_html(__('edit','js-support-ticket')); ?>">
                                                </a>
                                            </td>
                                        </tr>
                                        <?php echo wp_kses(JSSTformfield::hidden($row->id, $row->slug),JSST_ALLOWED_TAGS);?>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                        <!-- Hidden Fields -->
                        <div class="js-filter-form-action-wrp">
                            <?php echo wp_kses(JSSTformfield::submitbutton('btnsubmit', esc_html(__('Save','js-support-ticket')), array('class' => 'button savebutton js-form-act-btn js-form-act-btn')),JSST_ALLOWED_TAGS); ?>
                            <div class="js-form-act-msg">
                                <?php echo esc_html(__('This button will only save slugs on current page','js-support-ticket')); ?> !
                            </div>
                        </div>
                        <?php echo wp_kses(JSSTformfield::hidden('task', ''),JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('pagenum', ($pagenum > 1) ? $pagenum : ''),JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'),JSST_ALLOWED_TAGS); ?>
                    </form>
                    <?php
                    if (jssupportticket::$_data[1]) {
                        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
                    }
                } else {
                    JSSTlayout::getNoRecordFound();
                }
            ?>
        </div>
    </div>
</div>
