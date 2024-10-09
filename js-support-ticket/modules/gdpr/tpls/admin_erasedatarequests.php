<?php
    if(!defined('ABSPATH'))
        die('Restricted Access');

    $jssupportticket_js ="
        function resetFrom() {
            document.getElementById('email').value = '';
            document.getElementById('jssupportticketform').submit();
        }
    ";
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
<?php JSSTmessage::getMessage(); ?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Erase Data Requests','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Erase Data Requests', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=gdpr&jstlay=erasedatarequests"),"erase-data-requests")); ?>">
                <?php echo wp_kses(JSSTformfield::text('email', jssupportticket::$_data['filter']['email'], array('placeholder' => esc_html(__('User Email', 'js-support-ticket')),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if (!empty(jssupportticket::$_data[0])) { ?>
                <table id="js-support-ticket-table">
                    <tr class="js-support-ticket-table-heading">
                        <th class="left"><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></th>
                        <th class="left"><?php echo esc_html(__('Message','js-support-ticket')); ?></th>
                        <th ><?php echo esc_html(__('Email', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Request Status', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    <?php
                    foreach (jssupportticket::$_data[0] AS $request) {
                        ?>
                        <tr>
                            <td class="left">
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Subject', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <?php echo esc_html($request->subject); ?>
                            </td>
                            <td class="left">
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Message','js-support-ticket'));echo " : "; ?>
                                </span>
                                <?php echo wp_kses($request->message, JSST_ALLOWED_TAGS); ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Email', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php echo esc_html($request->user_email); ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Request Status', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php
                                    if($request->status == 1){
                                        echo esc_html(__('Awaiting response','js-support-ticket'));
                                    }elseif($request->status == 2){
                                        echo esc_html(__('Erased identifying data','js-support-ticket'));
                                    }else{
                                        echo esc_html(__('Deleted','js-support-ticket'));
                                    }
                                ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Created', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($request->created))); ?>
                            </td>
                            <td>
                                <a title="<?php echo esc_html(__('Erase identifying data', 'js-support-ticket'));?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure to erase identifying data', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=gdpr&task=eraseidentifyinguserdata&action=jstask&jssupportticketid='.esc_attr($request->uid),'erase-userdata'));?>">
                                    <?php echo esc_html(__('Erase identifying data', 'js-support-ticket'));?>
                                </a>
                                <a title="<?php echo esc_html(__('Delete data', 'js-support-ticket'));?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=gdpr&task=deleteuserdata&action=jstask&jssupportticketid='.esc_attr($request->uid),'delete-userdata'));?>">
                                    <?php echo esc_html(__('Delete data', 'js-support-ticket'));?>
                                </a>
                            </td>
                        </tr>
                    <?php
                }
                ?>
                </table>
                <?php
                if (jssupportticket::$_data[1]) {
                    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
                }
            } else {
                JSSTlayout::getNoRecordFound();
            }
            ?>
    </div>
</div>
