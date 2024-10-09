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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('GDPR Fields','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('GDPR Fields', 'js-support-ticket')); ?></h1>
            <a title="<?php echo esc_html(__('Add','js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=gdpr&jstlay=addgdprfield"><img alt="<?php echo esc_html(__('Add','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Add GDPR Field', 'js-support-ticket')); ?></a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <?php if (!empty(jssupportticket::$_data[0])) { ?>
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
                    foreach (jssupportticket::$_data[0] AS $field) {
                        $termsandconditions_text = '';
                        $termsandconditions_linktype = '';
                        $termsandconditions_link = '';
                        $termsandconditions_page = '';
                        if(isset($field->userfieldparams) && $field->userfieldparams != '' ){
                            $userfieldparams = json_decode($field->userfieldparams,true);
                            $termsandconditions_text = isset($userfieldparams['termsandconditions_text']) ? $userfieldparams['termsandconditions_text'] :'' ;
                            $termsandconditions_linktype = isset($userfieldparams['termsandconditions_linktype']) ? $userfieldparams['termsandconditions_linktype'] :'' ;
                            $termsandconditions_link = isset($userfieldparams['termsandconditions_link']) ? $userfieldparams['termsandconditions_link'] :'' ;
                            $termsandconditions_page = isset($userfieldparams['termsandconditions_page']) ? $userfieldparams['termsandconditions_page'] :'' ;
                            if($termsandconditions_linktype == 2){
                                $page_title_link = get_the_title($termsandconditions_page);
                            }else{
                                $page_title_link = $termsandconditions_link;
                            }
                        }?>
                        <tr class="js-filter-form-data">
                            <td class="left">
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Field Title', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <a href="?page=gdpr&jstlay=addgdprfield&jssupportticketid=<?php echo esc_attr($field->id); ?>" title="<?php echo esc_html(__('Field Title','js-support-ticket')); ?>">
                                    <?php echo esc_html($field->fieldtitle); ?>
                                </a>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Field Text', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <?php echo esc_html($termsandconditions_text); ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Required', 'js-support-ticket'));echo " : "; ?>
                                </span>
                                <?php if ($field->required == 1) { ?>
                                    <img alt="<?php echo esc_html(__('good','js-support-ticket')); ?>" height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" />
                                <?php }else{ ?>
                                    <img alt="<?php echo esc_html(__('Close','js-support-ticket')); ?>" height="15" width="15" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/close.png'; ?>" />
                                <?php } ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Ordering', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php  echo esc_html($field->ordering); ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Link Type', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php if($termsandconditions_linktype == 2){
                                    echo esc_html(__('Wordpress Page','js-support-ticket'));
                                }else{
                                    echo esc_html(__('Direct URL','js-support-ticket'));
                                } ?>
                            </td>
                            <td>
                                <span class="js-support-ticket-table-responsive-heading">
                                    <?php echo esc_html(__('Page Title or URL', 'js-support-ticket')); echo " : "; ?>
                                </span>
                                <?php echo esc_html($page_title_link); ?>
                            </td>
                            <td>
                                <a title="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" class="action-btn" href="?page=gdpr&jstlay=addgdprfield&jssupportticketid=<?php echo esc_attr($field->id); ?>"><img alt="<?php echo esc_html(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit.png" /></a>&nbsp;&nbsp;
                                <a title="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" class="action-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=gdpr&task=deletegdpr&action=jstask&gdprid='.esc_attr($field->id),'delete-gdpr'));?>"><img alt="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" /></a>
                            </td>
                        </tr>
                    <?php
            }
                ?>
                </table>
        </div>
            <?php
            // if (jssupportticket::$_data[1]) {
            //     echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
            // }
        } else {
            JSSTlayout::getNoRecordFound();
        }
        ?>
    </div>
</div>
