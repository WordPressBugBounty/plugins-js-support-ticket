<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (jssupportticket::$jsst_data['permission_granted'] == 1) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                if (jssupportticket::$jsst_data['staff_enabled']) {
                    ?>
                    <script type="text/javascript">
                        function resetFrom() {
                            document.getElementById('title').value = '';
                            document.getElementById('departmentid').value = '';
                            document.getElementById('status').value = '';
                            return true;
                        }

                        function addSpaces() {
                            // make problem with sanitization
                            // document.getElementById('topic').value = fillSpaces(document.getElementById('topic').value);
                            return true;
                        }
                    </script>
                    <?php JSSTmessage::getMessage();
                    // JSSTbreadcrumbs::getBreadcrumbs();
                    include_once(JSST_PLUGIN_PATH . 'includes/header.php');

                    $jsst_status = array((object) array('id' => '1', 'text' => __('Active', 'js-support-ticket')),
                        (object) array('id' => '0', 'text' => __('Disabled', 'js-support-ticket'))
                    );
                    ?>
                    <div class="js-ticket-announcement-wrapper">
                        <div class="js-ticket-top-search-wrp">
                            <div class="js-ticket-search-fields-wrp">
                                <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'agentcannedresponses')),"canned-responses")); ?>">
                                    <div class="js-ticket-fields-wrp">
                                        <div class="js-ticket-form-field">
                                            <?php echo wp_kses(JSSTformfield::text('title', jssupportticket::$jsst_data['filter']['title'], array('placeholder' => __('Title', 'js-support-ticket'), 'class'=>'js-ticket-field-input')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="js-ticket-form-field">
                                            <?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), jssupportticket::$jsst_data['filter']['departmentid'], __('Select Department', 'js-support-ticket'), array('class' => 'inputbox js-ticket-field-input')), JSST_ALLOWED_TAGS); ?>
                                        </div>

                                        <div class="js-ticket-form-field">
                                            <?php echo wp_kses(JSSTformfield::select('status', $jsst_status, jssupportticket::$jsst_data['filter']['status'], __('Select Status', 'js-support-ticket'), array('class' => 'inputbox js-ticket-field-input')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <div class="js-ticket-search-form-btn-wrp">
                                        <?php echo wp_kses(JSSTformfield::submitbutton('jsst-go', __('Search', 'js-support-ticket'), array('class' => 'js-search-button')), JSST_ALLOWED_TAGS); ?>
                                        <?php echo wp_kses(JSSTformfield::submitbutton('jsst-reset', __('Reset', 'js-support-ticket'), array('class' => 'js-reset-button', 'onclick' => 'return resetFrom();')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jshdlay', 'premademessages'), JSST_ALLOWED_TAGS); ?>
                                </form>
                            </div>
                        </div>

                        <div class="js-ticket-download-content-wrp">
                            <div class="js-ticket-table-heading-wrp">
                                <div class="js-ticket-table-heading-left">
                                    <?php echo esc_html(__('Canned Responses', 'js-support-ticket')) ?>
                                </div>
                                <div class="js-ticket-table-heading-right">
                                    <a class="js-ticket-table-add-btn" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'addcannedresponse'))); ?>"><span class="js-ticket-table-add-img-wrp"><img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add.png" alt="Add-image"></span><?php echo esc_html(__('Add Canned Response', 'js-support-ticket')) ?></a>
                                </div>
                            </div>
                            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                                <div class="js-ticket-table-wrp">
                                    <div class="js-ticket-table-header">
                                        <div class="js-ticket-table-header-col js-col-md-4 js-col-xs-4"><?php echo esc_html(__('Title', 'js-support-ticket')); ?></div>
                                        <div class="js-ticket-table-header-col js-col-md-4 js-col-xs-4"><?php echo esc_html(__('Department', 'js-support-ticket')); ?></div>
                                        <div class="js-ticket-table-header-col js-col-md-2 js-col-xs-2"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                                        <div class="js-ticket-table-header-col js-col-md-2 js-col-xs-2"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="js-ticket-table-body">
                                        <?php
                                        foreach (jssupportticket::$jsst_data[0] AS $jsst_premade) {
                                            $jsst_status = ($jsst_premade->status == 1) ? 'good.png' : 'close.png'; ?>
                                            <div class="js-ticket-data-row">
                                                <div class="js-ticket-table-body-col js-col-md-4 js-col-xs-4">
                                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Title','js-support-ticket')); ?>:</span>
                                                    <span class="js-ticket-title"><a class="js-ticket-title-anchor" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'addcannedresponse', 'jssupportticketid'=>$jsst_premade->id))); ?>"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_premade->title)); ?></a></span>
                                                </div>
                                                <div class="js-ticket-table-body-col js-col-md-4 js-col-xs-4">
                                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Department','js-support-ticket')); ?>:</span>
                                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_premade->departmentname)); ?>
                                                </div>
                                                <div class="js-ticket-table-body-col js-col-md-2 js-col-xs-2">
                                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Status','js-support-ticket')); ?>:</span>
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/' . esc_attr($jsst_status); ?>" />
                                                </div>
                                                <div class="js-ticket-table-body-col js-col-md-2 js-col-xs-2">
                                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Action','js-support-ticket')); ?>:</span>
                                                    <a class="js-ticket-table-action-btn" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'addcannedresponse', 'jssupportticketid'=>$jsst_premade->id))); ?>"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/downloadicon/edit.png" /></a>
                                                    <a class="js-ticket-table-action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'task'=>'deletepremademessage', 'action'=>'jstask', 'premademessageid'=>$jsst_premade->id, 'jsstpageid'=>get_the_ID())),'delete-premademessage-'.$jsst_premade->id)); ?>"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/downloadicon/delete.png" /></a>
                                                </div>
                                            </div>
                                        <?php
                                        } ?>
                                    </div>
                                </div>
                            <?php } else { // Record Not FOund
                                JSSTlayout::getNoRecordFound();
                            }?>
                        </div>
                        <?php
                        if (jssupportticket::$jsst_data[1]) {
                            echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                        }
                } else {
                    JSSTlayout::getStaffMemberDisable();
                }
            } else { // user not Staff
                JSSTlayout::getNotStaffMember();
            }
        } else {// User is guest
            $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'agentcannedresponses'));
            $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
            JSSTlayout::getUserGuest($jsst_redirect_url);
        }
    } else { // User permission not granted
        JSSTlayout::getPermissionNotGranted();
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
} ?>
</div>
</div>
