<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('GDPR Fields','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('GDPR Fields', 'js-support-ticket')); ?></h1>
            <a title="<?php echo esc_attr(__('Add','js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=gdpr&jstlay=addgdprfield"><img alt = "<?php echo esc_attr(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Add GDPR Field', 'js-support-ticket')); ?></a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <table id="js-support-ticket-table">
                    <tr class="js-support-ticket-table-heading">
                        <th class="left"><?php echo esc_html(__('Field Title', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Field Text', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Required', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Link Type', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Link', 'js-support-ticket')); ?></th>
                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                    </tr>
                    <?php
                    foreach (jssupportticket::$jsst_data[0] AS $jsst_field) {
                        $jsst_termsandconditions_text = '';
                        $jsst_termsandconditions_linktype = '';
                        $jsst_termsandconditions_link = '';
                        $jsst_termsandconditions_page = '';
                        if(isset($jsst_field->userfieldparams) && $jsst_field->userfieldparams != '' ){
                            $jsst_userfieldparams = json_decode($jsst_field->userfieldparams,true);
                            $jsst_termsandconditions_text = isset($jsst_userfieldparams['termsandconditions_text']) ? $jsst_userfieldparams['termsandconditions_text'] :'' ;
                            $jsst_termsandconditions_linktype = isset($jsst_userfieldparams['termsandconditions_linktype']) ? $jsst_userfieldparams['termsandconditions_linktype'] :'' ;
                            $jsst_termsandconditions_link = isset($jsst_userfieldparams['termsandconditions_link']) ? $jsst_userfieldparams['termsandconditions_link'] :'' ;
                            $jsst_termsandconditions_page = isset($jsst_userfieldparams['termsandconditions_page']) ? $jsst_userfieldparams['termsandconditions_page'] :'' ;
                            if($jsst_termsandconditions_linktype == 2){
                                $jsst_page_title_link = get_the_title($jsst_termsandconditions_page);
                            }else{
                                $jsst_page_title_link = $jsst_termsandconditions_link;
                            }
                        }?>
                        <tr class="js-filter-form-data">
                            <td class="left">
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Field Title', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <a href="?page=gdpr&jstlay=addgdprfield&jssupportticketid=<?php echo esc_attr($jsst_field->id); ?>" title="<?php echo esc_attr(__('Field Title','js-support-ticket')); ?>">
                                    <?php echo esc_html($jsst_field->fieldtitle); ?>
                                </a>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Field Text', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <?php echo esc_html($jsst_termsandconditions_text); ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Required', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <?php if ($jsst_field->required == 1) { ?>
                                    <img alt = "<?php echo esc_attr(__('good','js-support-ticket')); ?>" height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" />
                                <?php }else{ ?>
                                    <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/close.png'; ?>" />
                                <?php } ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Ordering', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php  echo esc_html($jsst_field->ordering); ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Link Type', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php if($jsst_termsandconditions_linktype == 2){
                                    echo esc_html(__('Wordpress Page','js-support-ticket'));
                                }else if($jsst_termsandconditions_linktype == 1){
                                    echo esc_html(__('Direct URL','js-support-ticket'));
                                }else{
                                    echo esc_html(__('None','js-support-ticket'));
                                } ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Page Title or URL', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php echo esc_html($jsst_page_title_link); ?>
                            </td>
                            <td>
                                <a title="<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="action-btn" href="?page=gdpr&jstlay=addgdprfield&jssupportticketid=<?php echo esc_attr($jsst_field->id); ?>"><img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit.png" /></a>&nbsp;&nbsp;
                                <a title="<?php echo esc_attr(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=gdpr&task=deletegdpr&action=jstask&gdprid='.esc_attr($jsst_field->id),'delete-gdpr'));?>"><img alt = "<?php echo esc_attr(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                            </td>
                        </tr>
                    <?php
            }
                ?>
                </table>
        </div>
            <?php
            // if (jssupportticket::$jsst_data[1]) {
            //     echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
            // }
        } else {
            JSSTlayout::getNoRecordFound();
        }
        ?>
    </div>
</div>
