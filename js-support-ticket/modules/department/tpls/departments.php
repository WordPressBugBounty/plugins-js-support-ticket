<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (jssupportticket::$_data['permission_granted'] == 1) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                if (jssupportticket::$_data['staff_enabled']) {
                    $jssupportticket_js ="
                        function resetFrom() {
                            document.getElementById('jsst-dept').value = '';
                            return true;
                        }
                        function addSpaces() {
                            return true;
                        }
                    ";
                    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
                    ?>
                    <?php JSSTmessage::getMessage(); ?>
                    <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
                    <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>

                    <div class="js-ticket-department-wrapper">
                        <div class="js-ticket-top-search-wrp">
                            <div class="js-ticket-search-fields-wrp">
                               <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="POST" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments')),"departments")); ?>">
                                    <div class="js-ticket-fields-wrp">
                                        <div class="js-ticket-form-field js-ticket-form-field-download-search">
                                            <?php echo wp_kses(JSSTformfield::text('jsst-dept', jssupportticket::parseSpaces(jssupportticket::$_data['filter']['jsst-dept']), array('placeholder' => esc_html(__('Search', 'js-support-ticket')), 'class' => 'js-ticket-field-input')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="js-ticket-search-form-btn-wrp js-ticket-search-form-btn-wrp-download ">
                                            <?php echo wp_kses(JSSTformfield::submitbutton('jsst-go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'js-search-button', 'onclick' => 'return addSpaces();')), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::submitbutton('jsst-reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'js-reset-button', 'onclick' => 'return resetFrom();')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jshdlay', 'departments'), JSST_ALLOWED_TAGS); ?>
                                </form>
                            </div>
                        </div>
                        <?php if (!empty(jssupportticket::$_data[0])) { ?>
                            <div class="js-ticket-download-content-wrp">
                                <div class="js-ticket-table-heading-wrp">
                                    <div class="js-ticket-table-heading-left">
                                        <?php echo esc_html(__('Search Departments', 'js-support-ticket')); ?>
                                    </div>
                                    <div class="js-ticket-table-heading-right">
                                        <a class="js-ticket-table-add-btn" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'adddepartment'))); ?>"><span class="js-ticket-table-add-img-wrp"><img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add.png" alt="Add-image"></span><?php echo esc_html(__('Add Department', 'js-support-ticket')); ?></a>
                                    </div>
                                </div>
                                <div class="js-ticket-table-wrp">
                                    <div class="js-ticket-table-header">
                                        <div class="js-ticket-table-header-col js-col-md-4 js-col-xs-4"><?php echo esc_html(__('Name', 'js-support-ticket')); ?></div>
                                        <div class="js-ticket-table-header-col js-col-md-3 js-col-xs-3"><?php echo esc_html(__('Outgoing', 'js-support-ticket')); ?></div>
                                        <div class="js-ticket-table-header-col js-col-md-1 js-col-xs-1"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                                        <div class="js-ticket-table-header-col js-col-md-2 js-col-xs-2"><?php echo esc_html(__('Created', 'js-support-ticket')); ?></div>
                                        <div class="js-ticket-table-header-col js-col-md-2 js-col-xs-2"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-table-body">
                                        <?php
                                            foreach (jssupportticket::$_data[0] AS $department) {
                                                $type = ($department->ispublic == 1) ? esc_html(__('Public', 'js-support-ticket')) : esc_html(__('Private', 'js-support-ticket'));
                                                $status = ($department->status == 1) ? 'good.png' : 'close.png'; ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-table-body-col js-col-md-4 js-col-xs-4">
                                                        <span class="js-ticket-display-block"><?php echo esc_html(__('Department','js-support-ticket')); ?>:</span>
                                                        <span class="js-ticket-title"><a class="js-ticket-title-anchor" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'adddepartment', 'jssupportticketid'=>$department->id))); ?>"><?php echo esc_html($department->departmentname); ?></a></span>
                                                    </div>
                                                    <div class="js-ticket-table-body-col js-col-md-3 js-col-xs-3">
                                                        <span class="js-ticket-display-block"><?php echo esc_html(__('Outgoing','js-support-ticket')); ?>:</span>
                                                        <?php echo esc_html($department->outgoingemail); ?>
                                                    </div>
                                                    <div class="js-ticket-table-body-col js-col-md-1 js-col-xs-1">
                                                        <span class="js-ticket-display-block"><?php echo esc_html(__('Status','js-support-ticket')); ?>:</span>
                                                        <img alt="image" src="<?php echo esc_url( JSST_PLUGIN_URL . 'includes/images/' . esc_attr($status)); ?>" />
                                                    </div>
                                                    <div class="js-ticket-table-body-col js-col-md-2 js-col-xs-2">
                                                        <span class="js-ticket-display-block"><?php echo esc_html(__('Created','js-support-ticket')); ?>:</span>
                                                        <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($department->created))); ?>
                                                    </div>
                                                    <div class="js-ticket-table-body-col js-col-md-2 js-col-xs-2">
                                                        <span class="js-ticket-display-block"><?php echo esc_html(__('Action','js-support-ticket')); ?>:</span>
                                                        <a class="js-ticket-table-action-btn" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'adddepartment', 'jssupportticketid'=>$department->id))); ?>"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/downloadicon/edit.png" /></a>&nbsp;&nbsp;
                                                        <a class="js-ticket-table-action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'task'=>'deletedepartment', 'action'=>'jstask', 'departmentid'=>$department->id, 'jsstpageid'=>get_the_ID())),'delete-department')); ?>"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/downloadicon/delete.png" /></a>
                                                    </div>
                                                </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php
                        if (jssupportticket::$_data[1]) {
                            echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
                        }?>
                    </div>
                    <?php
                    } else {
                        JSSTlayout::getNoRecordFound();
                    }
                } else {
                    JSSTlayout::getStaffMemberDisable();
                }
            } else { // user not Staff
                JSSTlayout::getNotStaffMember();
            }
        } else {
            $redirect_url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
            $redirect_url = jssupportticketphplib::JSST_safe_encoding($redirect_url);
            JSSTlayout::getUserGuest($redirect_url);
        }
    } else { // User permission not granted
        JSSTlayout::getPermissionNotGranted();
    }
} else {
    JSSTlayout::getSystemOffline();
} ?>
</div>
