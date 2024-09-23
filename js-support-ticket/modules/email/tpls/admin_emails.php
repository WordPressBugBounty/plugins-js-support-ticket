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
                        <li><?php echo esc_html(__('System Emails','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></h1>
            <a title="<?php echo esc_html(__('Add','js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=email&jstlay=addemail"><img alt="<?php echo esc_html(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Add Email', 'js-support-ticket')); ?></a>
            <a target="blank" href="https://www.youtube.com/watch?v=4_wrnx8ka0E" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_html(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                <?php echo esc_html(__('Watch Video','js-support-ticket')); ?>
            </a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=email&jstlay=emails"),"emails")); ?>">
                <?php echo wp_kses(JSSTformfield::text('email', jssupportticket::$_data['filter']['email'], array('placeholder' => esc_html(__('Email', 'js-support-ticket')),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <span id="js-systemail" class="js-admin-infotitle"><img alt="<?php echo esc_html(__('info','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/infoicon.png" /><?php echo esc_html(__('System email used for sending email', 'js-support-ticket')); ?></span>
            <?php if (!empty(jssupportticket::$_data[0])) { ?>
            <table id="js-support-ticket-table">
                <tr class="js-support-ticket-table-heading">
                    <th class="left w60"><?php echo esc_html(__('Email Address', 'js-support-ticket')); ?></th>
                    <th><?php echo esc_html(__('Auto Response', 'js-support-ticket')); ?></th>
                    <!-- <th><?php /* echo esc_html(__('Priority','js-support-ticket')); */ ?></th> -->
                    <th><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                    <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                </tr>
                <?php
                foreach (jssupportticket::$_data[0] AS $email) {
                    $autoresponse = ($email->autoresponse == 1) ? 'good.png' : 'close.png';
                    ?>
                    <tr>
                        <td class="left w60"><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Email Address', 'js-support-ticket'));
                echo " : "; ?></span><a title="<?php echo esc_html(__('Email','js-support-ticket')); ?>" href="?page=email&jstlay=addemail&jssupportticketid=<?php echo esc_attr($email->id); ?>"><?php echo esc_html($email->email); ?></a></td>
                        <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Auto Response', 'js-support-ticket'));
                echo " : "; ?></span><img alt="<?php echo esc_html(__('Auto Response','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/<?php echo esc_attr($autoresponse); ?>" /></td>
                        <!-- <td><span class="js-support-ticket-table-responsive-heading"><?php /* echo esc_html(__('Priority','js-support-ticket'));echo " : "; ?></span><?php echo esc_html($email->priority); */ ?></td> -->
                        <td><span class="js-support-ticket-table-responsive-heading"><?php echo esc_html(__('Created', 'js-support-ticket'));
                echo " : "; ?></span><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($email->created))); ?></td>
                        <td >
                            <a title="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" class="action-btn" href="?page=email&jstlay=addemail&jssupportticketid=<?php echo esc_attr($email->id); ?>"><img alt="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit.png" /></a>
                            <a title="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=email&task=deleteemail&action=jstask&emailid=' .esc_attr($email->id),'delete-email')); ?>"><img alt="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                        </td>
                    </tr>
                <?php }
            ?>
            </table>
            <?php
            if (jssupportticket::$_data[1]) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
            }
        } else {// User is guest
            JSSTlayout::getNoRecordFound();
        }
        ?>
        </div>
    </div>
</div>
