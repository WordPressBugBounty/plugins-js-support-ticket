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
                        <li><?php echo esc_html(__('Email Templates','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Email Templates', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <?php $nonce_id = isset(jssupportticket::$_data[0]->id) ? jssupportticket::$_data[0]->id : '';?>
            <div class="js-email-menu">
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'tk-nw') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=tk-nw" title="<?php echo esc_html(__('New Ticket','js-support-ticket')); ?>"><?php echo esc_html(__('New Ticket', 'js-support-ticket')); ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'sntk-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=sntk-tk" title="<?php echo esc_html(__('Agent Ticket','js-support-ticket')); ?>"><?php echo esc_html(__('Agent Ticket', 'js-support-ticket')); ?><?php if (!in_array('agent', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <?php /*<span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ew-md') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ew-md" title="<?php echo esc_html(__('New Department','js-support-ticket')); ?>"><?php echo esc_html(__('New Department', 'js-support-ticket')); ?></a></span> */ ?>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ew-sm') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ew-sm" title="<?php echo esc_html(__('New Agent','js-support-ticket')); ?>"><?php echo esc_html(__('New Agent', 'js-support-ticket')); ?><?php if (!in_array('agent', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <?php /*<span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ew-ht') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ew-ht" title="<?php echo esc_html(__('New Help Topic','js-support-ticket')); ?>"><?php echo esc_html(__('New Help Topic', 'js-support-ticket')); ?></a></span> */ ?>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'rs-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=rs-tk" title="<?php echo esc_html(__('Reassign Ticket','js-support-ticket')); ?>"><?php echo esc_html(__('Reassign Ticket', 'js-support-ticket')); ?><?php if (!in_array('agent', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'cl-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=cl-tk" title="<?php echo esc_html(__('Close Ticket','js-support-ticket')); ?>"><?php echo esc_html(__('Close Ticket', 'js-support-ticket')); ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'dl-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=dl-tk" title="<?php echo esc_html(__('Delete Ticket','js-support-ticket')); ?>"><?php echo esc_html(__('Delete Ticket', 'js-support-ticket')); ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'mo-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=mo-tk" title="<?php echo esc_html(__('Mark overdue','js-support-ticket')); ?>"><?php echo esc_html(__('Mark Overdue', 'js-support-ticket')); ?><?php if (!in_array('overdue', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'be-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=be-tk" title="<?php echo esc_html(__('Ban Email','js-support-ticket')); ?>"><?php echo esc_html(__('Ban Email', 'js-support-ticket')); ?><?php if (!in_array('banemail', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'be-trtk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=be-trtk" title="<?php echo esc_html(__('Ban Email Try To Create Ticket','js-support-ticket')); ?>"><?php echo esc_html(__('Ban Email Try To Create Ticket', 'js-support-ticket')); ?><?php if (!in_array('banemail', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'dt-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=dt-tk" title="<?php echo esc_html(__('Department Transfer','js-support-ticket')); ?>"><?php echo esc_html(__('Department Transfer', 'js-support-ticket')); ?><?php if (!in_array('actions', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ebct-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ebct-tk" title="<?php echo esc_html(__('Ban Email and Close Ticket', 'js-support-ticket')); ?>"><?php echo esc_html(__('Ban Email and Close Ticket', 'js-support-ticket')); ?><?php if (!in_array('banemail', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ube-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ube-tk" title="<?php echo esc_html(__('Unban Email', 'js-support-ticket')); ?>"><?php echo esc_html(__('Unban Email', 'js-support-ticket')); ?><?php if (!in_array('banemail', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'rsp-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=rsp-tk" title="<?php echo esc_html(__('Response Ticket', 'js-support-ticket')); ?>"><?php echo esc_html(__('Response Ticket', 'js-support-ticket')); ?><?php if (!in_array('agent', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'rpy-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=rpy-tk" title="<?php echo esc_html(__('Reply Ticket', 'js-support-ticket')); ?>"><?php echo esc_html(__('Reply Ticket', 'js-support-ticket')); ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'tk-ew-ad') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=tk-ew-ad" title="<?php echo esc_html(__('New Ticket Admin Alert', 'js-support-ticket')); ?>"><?php echo esc_html(__('New Ticket Admin Alert', 'js-support-ticket')); ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'lk-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=lk-tk" title="<?php echo esc_html(__('Lock Ticket', 'js-support-ticket')); ?>"><?php echo esc_html(__('Lock Ticket', 'js-support-ticket')); ?><?php if (!in_array('actions', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ulk-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ulk-tk" title="<?php echo esc_html(__('Unlock Ticket', 'js-support-ticket')); ?>"><?php echo esc_html(__('Unlock Ticket', 'js-support-ticket')); ?><?php if (!in_array('actions', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'minp-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=minp-tk" title="<?php echo esc_html(__('In Progress Ticket', 'js-support-ticket')); ?>"><?php echo esc_html(__('In Progress Ticket', 'js-support-ticket')); ?><?php if (!in_array('actions', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'pc-tk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=pc-tk" title="<?php echo esc_html(__('Ticket Priority Is Changed By', 'js-support-ticket')); ?>"><?php echo esc_html(__('Ticket Priority Is Changed By', 'js-support-ticket')); ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ml-ew') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ml-ew" title="<?php echo esc_html(__('New Mail Received', 'js-support-ticket')); ?>"><?php echo esc_html(__('New Mail Received', 'js-support-ticket')); ?><?php if (!in_array('mail', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'ml-rp') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=ml-rp" title="<?php echo esc_html(__('New Mail Message Received', 'js-support-ticket')); ?>"><?php echo esc_html(__('New Mail Message Received', 'js-support-ticket')); ?><?php if (!in_array('mail', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'fd-bk') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=fd-bk" title="<?php echo esc_html(__('Feedback Email To User', 'js-support-ticket')); ?>"><?php echo esc_html(__('Feedback Email To User', 'js-support-ticket')); ?><?php if (!in_array('feedback', jssupportticket::$_active_addons)) { ?><span style="color: red;"> *</span><?php } ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'no-rp') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=no-rp" title="<?php echo esc_html(__('User Reply On Closed Ticket', 'js-support-ticket')); ?>"><?php echo esc_html(__('User Reply On Closed Ticket', 'js-support-ticket')); ?></a></span>
                <span class="js-email-menu-link <?php if (jssupportticket::$_data[1] == 'del-data') echo 'selected'; ?>"><a class="js-email-link" href="?page=emailtemplate&for=del-data" title="<?php echo esc_html(__('Data Deleted', 'js-support-ticket')); ?>"><?php echo esc_html(__('Data Deleted', 'js-support-ticket')); ?></a></span>
            </div>
            <!-- default template -->
            <?php
            $class = '';
            $showformdata = false;
            if (!in_array(jssupportticket::$_data[1], ['del-data','ml-rp','ml-ew','rpy-tk','rsp-tk','ube-tk','be-trtk','be-tk','dl-tk','ew-sm']) && in_array('multiform', jssupportticket::$_active_addons)) {
                $showformdata = true;
            }
            if ($showformdata || in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                $class = 'js-custom-email-body'; ?>
                <div class="js-ticket-email-templates-wrapper">
                    <div class="js-ticket-default-template-section">
                        <div class="js-ticket-default-template-title">
                            <?php echo esc_html(__('Default Email Template', 'js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-default-template-description">
                            <?php
                            if (in_array('multiform', jssupportticket::$_active_addons) && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                                echo esc_html(__('This template is used for all forms and languages unless a specific custom template is created.', 'js-support-ticket'));
                            } elseif (in_array('multiform', jssupportticket::$_active_addons)) {
                                echo esc_html(__('This template is used for all forms unless a specific form template is created.', 'js-support-ticket'));
                            } else {
                                echo esc_html(__('This template is used for all languages unless a specific language template is created.', 'js-support-ticket'));
                            } ?>
                        </div>
                        <a href="?page=emailtemplate&for=<?php echo esc_attr( jssupportticket::$_data[1] ); ?>&defaultTemp=1" class="js-ticket-button js-ticket-view-edit-default">
                            <?php echo esc_html(__('View/Edit Default Template', 'js-support-ticket')); ?>
                        </a>
                    </div>

                    <div class="js-ticket-form-specific-templates-section">
                    <div class="js-ticket-form-specific-templates-title">
                        <?php
                        if (in_array('multiform', jssupportticket::$_active_addons) && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                            echo esc_html(__('Form & Language-Specific Email Templates', 'js-support-ticket'));
                        } elseif (in_array('multiform', jssupportticket::$_active_addons)) {
                            echo esc_html(__('Form-Specific Email Templates', 'js-support-ticket'));
                        } else {
                            echo esc_html(__('Language-Specific Email Templates', 'js-support-ticket'));
                        } ?>
                    </div>
                    <div class="js-ticket-form-specific-templates-description">
                        <?php
                        if (in_array('multiform', jssupportticket::$_active_addons) && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                            echo esc_html(__('Customize email content for individual form, for specific language, or a combination of both. If no custom template exists for a given form or language, the default template will be used.', 'js-support-ticket'));
                        } elseif (in_array('multiform', jssupportticket::$_active_addons)) {
                            echo esc_html(__('Customize email content for individual form. If no custom template exists for a form, the default template will be used.', 'js-support-ticket'));
                        } else {
                            echo esc_html(__('Customize email content for specific language. If no custom template exists for a language, the default template will be used.', 'js-support-ticket'));
                        } ?>
                    </div>
                    <div class="js-ticket-form-selection-wrapper">
                        <form class="js-ticket-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'emailtemplate', 'task'=>'savecustomemailtemplate')),"save-form-email-template")); ?>">
                            <?php
                            if ($showformdata) {
                                echo wp_kses(JSSTformfield::select('multiformid', JSSTincluder::getJSModel('multiform')->getMultiFormForCombobox(jssupportticket::$_data[0]->templatefor), '', esc_html(__('-- Select a Form --', 'js-support-ticket')), array('class' => 'inputbox one js-ticket-select-form')), JSST_ALLOWED_TAGS);                            
                            }
                            if (in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
                                echo wp_kses(JSSTformfield::select("language_id", JSSTincluder::getJSModel("multilanguageemailtemplates")->getLangForCombobox() ,'',__("-- Select a Language --", "js-support-ticket"), array("class" => "inputbox one js-ticket-select-form")), JSST_ALLOWED_TAGS);
                            } ?>
                            <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Create Template', 'js-support-ticket')), array('class' => 'js-ticket-button js-ticket-create-edit-template')), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('id', ''), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('templatefor', jssupportticket::$_data[0]->templatefor), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('for', jssupportticket::$_data[1]), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'emailtemplate_savecustomemailtemplate'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>

                    <div class="js-ticket-existing-custom-templates">
                        <?php
                        if (!empty(jssupportticket::$_data[0]->multiTemplates)) { ?>
                            <div class="js-ticket-existing-custom-templates-title">
                                <?php echo esc_html(__('Existing Custom Templates', 'js-support-ticket')); ?>
                            </div>
                            <table class="js-ticket-custom-templates-table" id="jsst-import-data-result-table">
                                <thead>
                                    <tr>
                                        <?php
                                        if ($showformdata) { ?>
                                            <th><?php echo esc_html(__('Form Name', 'js-support-ticket')); ?></th>
                                            <th><?php echo esc_html(__('Department Name', 'js-support-ticket')); ?></th>
                                            <?php
                                        }
                                        if (in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) { ?>
                                            <th><?php echo esc_html(__('Language', 'js-support-ticket')); ?></th>
                                            <?php
                                        } ?>
                                        <th><?php echo esc_html(__('Actions', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach (jssupportticket::$_data[0]->multiTemplates as $key => $multiTemplate) {
                                        if (empty($multiTemplate->formname) && empty($multiTemplate->language)) {
                                            continue;
                                        } ?>
                                        <tr>
                                            <?php
                                            if ($showformdata) { ?>
                                                <td><?php echo esc_html($multiTemplate->formname); ?></td>
                                                <td><?php echo esc_html($multiTemplate->departmentname); ?></td>
                                                <?php
                                            }
                                            if (in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) { ?>
                                                <td>
                                                    <?php 
                                                    if (!empty($multiTemplate->language_name)) {
                                                        echo esc_html($multiTemplate->language_name);
                                                    }
                                                    ?>
                                                </td>
                                                <?php
                                            } ?>
                                            <td>
                                                <a href="?page=emailtemplate&for=<?php echo esc_attr( jssupportticket::$_data[1] ); ?>&formid=<?php echo esc_attr( $multiTemplate->formid ); ?>&langcode=<?php echo esc_attr( $multiTemplate->language ); ?>" class="js-ticket-action-link js-ticket-edit"><?php echo esc_html(__( 'Edit', 'js-support-ticket' ) ); ?></a>
                                                <span class="js-ticket-action-separator">|</span>
                                                <a onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete', 'js-support-ticket')); ?>'); " href="<?php echo esc_url(wp_nonce_url('?page=emailtemplate&task=deleteformemailtemplate&action=jstask&templateid='.esc_attr($multiTemplate->template_id).'&for='.esc_attr(jssupportticket::$_data[1]).'&source='.esc_attr($multiTemplate->source),'delete-template-'.$multiTemplate->template_id));?>" class="js-ticket-action-link js-ticket-delete"><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></a>
                                                <span class="js-ticket-action-separator">|</span>
                                                <a href="?page=emailtemplate&for=tk-nw&formid=<?php echo esc_html($multiTemplate->formid); ?>" class="js-ticket-action-link js-ticket-preview"><?php echo esc_html(__('Preview', 'js-support-ticket')); ?></a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php 
                        } ?>
                    </div>
                    </div>
                </div>
                <?php
            } ?>
            <!-- custom template -->
            <form method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=emailtemplate&task=saveemailtemplate"),"save-email-template-".$nonce_id)); ?>">
                <div class="js-email-body <?php echo esc_attr( $class ); ?>">
                    <!-- Now add the Dropdown for the Languages -->
                    <?php
                    if (!empty(jssupportticket::$_data[0]->language_name) || !empty(jssupportticket::$_data[0]->multiformname)) { ?>
                        <div class="js-ticket-card-wrapper">
                            <?php
                            if (!empty(jssupportticket::$_data[0]->multiformname)) { ?>
                                <div class="js-ticket-card-item">
                                    <div class="js-ticket-card-icon">
                                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/left-icons/menu/system-email.png" />
                                    </div>
                                    <div class="js-ticket-card-content">
                                        <div class="js-ticket-card-label">
                                            <?php echo esc_html(__('Form:', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-ticket-card-value js-ticket-card-value-bold">
                                            <?php echo esc_html(jssupportticket::$_data[0]->multiformname); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            if (!empty(jssupportticket::$_data[0]->language_name)) { ?>
                                <div class="js-ticket-card-item">
                                    <div class="js-ticket-card-icon">
                                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/left-icons/menu/download.png" />
                                    </div>
                                    <div class="js-ticket-card-content">
                                        <div class="js-ticket-card-label">
                                            <?php echo esc_html(__('Language:', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-ticket-card-value js-ticket-card-value-bold">
                                            <?php echo esc_html(jssupportticket::$_data[0]->language_name); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } ?>
                        </div>
                        <?php
                    } ?>

                    <div class="js-form-wrapper">
                        <div class="a-js-form-title"><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></div>
                        <div class="a-js-form-field"><?php echo wp_kses(JSSTformfield::text('subject', jssupportticket::$_data[0]->subject, array('class' => 'inputbox', 'style' => 'width:100%;')), JSST_ALLOWED_TAGS) ?></div>
                    </div>
                    <div class="js-form-wrapper">
                        <div class="a-js-form-title"><?php echo esc_html(__('Body', 'js-support-ticket')); ?></div>
                        <div class="a-js-form-field"><?php wp_editor(jssupportticket::$_data[0]->body, 'body', array('media_buttons' => false)); ?></div>
                    </div>
                    <div class="js-email-parameters">
                        <div class="js-email-parameter-heading"><?php echo esc_html(__('Parameters', 'js-support-ticket')); ?></div>
                        <?php
                        if (jssupportticket::$_data[1] == 'tk-nw') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{HELP_TOPIC} : <?php echo esc_html(__('Help Topic', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL} : <?php echo esc_html(__('Email', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{MESSAGE} : <?php echo esc_html(__('Message','js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <?php 
                            foreach (jssupportticket::$_data[2] as $field ) {
                                if($field->userfieldtype != 'file'){ ?>
                                    <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                                    <?php
                                }
                            }
                        } elseif (jssupportticket::$_data[1] == 'sntk-tk') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{HELP_TOPIC} : <?php echo esc_html(__('Help Topic', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL} : <?php echo esc_html(__('Email', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{MESSAGE} : <?php echo esc_html(__('Message','js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <?php 
                            foreach (jssupportticket::$_data[2] as $field ) {
                                if($field->userfieldtype != 'file'){ ?>
                                    <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                                    <?php
                                }
                            }
                        } elseif (jssupportticket::$_data[1] == 'ew-md') {
                            ?>
                            <span class="js-email-paramater">{DEPARTMENT_TITLE} : <?php echo esc_html(__('Department title', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'ew-gr') {
                            ?>
                            <span class="js-email-paramater">{GROUP_TITLE} : <?php echo esc_html(__('Group Title', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'ew-sm') {
                            ?>
                            <span class="js-email-paramater">{STAFF_MEMBER_NAME} : <?php echo esc_html(__('Agent name', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'ew-ht') {
                            ?>
                            <span class="js-email-paramater">{HELPTOPIC_TITLE} : <?php echo esc_html(__('Help topic title', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT_TITLE} : <?php echo esc_html(__('Department title', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'rs-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{STAFF_MEMBER_NAME} : <?php echo esc_html(__('Agent name', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field) ;?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'cl-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{FEEDBACKURL} : <?php echo esc_html(__('Feedback URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'dl-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'mo-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'be-tk') {
                            ?>
                            <span class="js-email-paramater">{EMAIL_ADDRESS} : <?php echo esc_html(__('Email Address', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'be-trtk') {
                            ?>
                            <span class="js-email-paramater">{EMAIL_ADDRESS} : <?php echo esc_html(__('Email Address', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'dt-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT_TITLE} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'ebct-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL_ADDRESS} : <?php echo esc_html(__('Email Address', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETID} : <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'ube-tk') {
                            ?>
                            <span class="js-email-paramater">{EMAIL_ADDRESS} : <?php echo esc_html(__('Email Address', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'rsp-tk') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL} : <?php echo esc_html(__('Email', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{MESSAGE} : <?php echo esc_html(__('Message','js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'rpy-tk') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL} : <?php echo esc_html(__('Email', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{MESSAGE} : <?php echo esc_html(__('Message','js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'tk-ew-ad') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL} : <?php echo esc_html(__('Email', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{MESSAGE} : <?php echo esc_html(__('Message','js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'lk-tk') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL} : <?php echo esc_html(__('Email', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'ulk-tk') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{EMAIL} : <?php echo esc_html(__('Email', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'minp-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'pc-tk') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKINGID} : <?php echo esc_html(__('Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY_TITLE} : <?php echo esc_html(__('Priority', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKETURL} : <?php echo esc_html(__('Ticket URL', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_HISTORY} : <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'ml-ew') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{STAFF_MEMBER_NAME} : <?php echo esc_html(__('Agent name', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{MESSAGE} : <?php echo esc_html(__('Message','js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'ml-rp') {
                            ?>
                            <span class="js-email-paramater">{SUBJECT} : <?php echo esc_html(__('Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{STAFF_MEMBER_NAME} : <?php echo esc_html(__('Agent name', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{MESSAGE} : <?php echo esc_html(__('Message','js-support-ticket')); ?></span>
                            <?php
                        } elseif (jssupportticket::$_data[1] == 'fd-bk') {
                            ?>
                            <span class="js-email-paramater">{USER_NAME} : <?php echo esc_html(__('User Name', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TICKET_SUBJECT} : <?php echo esc_html(__('Ticket Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{TRACKING_ID} : <?php echo esc_html(__('Ticket Tracking ID', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{CLOSE_DATE} : <?php echo esc_html(__('Close Date', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'no-rp') {
                            ?>
                            <span class="js-email-paramater">{TICKET_SUBJECT} : <?php echo esc_html(__('Ticket Subject', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{DEPARTMENT} : <?php echo esc_html(__('Department', 'js-support-ticket')); ?></span>
                            <span class="js-email-paramater">{PRIORITY} : <?php echo esc_html(__('Ticket Priority', 'js-support-ticket')); ?></span>
                            <?php foreach (jssupportticket::$_data[2] as $field ) {
                                    if($field->userfieldtype != 'file'){ ?>
                                        <span class="js-email-paramater">{<?php echo esc_html($field->field);?>} : <?php echo esc_html($field->fieldtitle); ?></span>
                            <?php   }
                                }
                        } elseif (jssupportticket::$_data[1] == 'del-data') {
                            ?>
                            <span class="js-email-paramater">{USERNAME} : <?php echo esc_html(__('Username', 'js-support-ticket')); ?></span>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="js-form-button">
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Email Template', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                    </div>
                    <?php
                    if(count(jssupportticket::$_active_addons) < 36 ){  ?>
                        <div class="js-sugestion-alert-wrp js-email-msg">
                            <div class="js-sugestion-alert">
                                <strong>
                                    <?php echo esc_html(__('Note:', 'js-support-ticket')); ?>
                                </strong>
                                <?php echo esc_html(__('Features marked with', 'js-support-ticket')); ?>
                                <span>*</span>
                                <?php echo esc_html(__('are only available with its own addon.', 'js-support-ticket')); ?>
                            </div>
                        </div>
                        <?php
                    } ?>
                </div>

                <?php
                $jssupportticket_js ="
                    jQuery(document).ready(function(){
                        jQuery('#save').click(function(){
                            var subject = jQuery('#subject').val();
                            var body = jQuery('#body').val();
                            if(subject=='' && body==''){
                                alert('Please Fill the Subject and body');
                                return false;
                            }
                        });
                    });
                ";
                wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
                if (!empty(jssupportticket::$_data[0]->language_id)) {
                    $language_id = jssupportticket::$_data[0]->language_id;
                } else {
                    $language_id = '';
                }
                ?>

                <?php echo wp_kses(JSSTformfield::hidden('id', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', jssupportticket::$_data[0]->created), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('templatefor', jssupportticket::$_data[0]->templatefor), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('multiformid', jssupportticket::$_data[0]->multiformid), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('for', jssupportticket::$_data[1]), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'emailtemplate_saveemailtemplate'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('callfor', 'emailtemplate'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('multitemp_id', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('lang_id', $language_id), JSST_ALLOWED_TAGS); ?>
            </form>
        </div>
    </div>
</div>
<?php
$jssupportticket_js ="
    function scrollToFormByParam() {
        const urlParams = new URLSearchParams(window.location.search);
        const formId = urlParams.get('formid');
        const langCode = urlParams.get('langcode');
        const defaultTemp = urlParams.get('defaultTemp');

        if (formId || defaultTemp || langCode) {
            const target = jQuery('.js-email-body');

            if (target.length) {
                // Define how many pixels before the target you want to scroll
                const offsetPixels = 60; // You can change this value to your preference

                jQuery('html, body').animate({
                    scrollTop: target.offset().top - offsetPixels
                }, 600);
            }
        }
    }

    jQuery(document).ready(function($) {
        jQuery('select#lang_id').prop('disabled', true);
        jQuery.validate();
        scrollToFormByParam();

        // Optional: re-run scroll when popstate triggers (back/forward buttons)
        $(window).on('popstate', scrollToFormByParam);
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
