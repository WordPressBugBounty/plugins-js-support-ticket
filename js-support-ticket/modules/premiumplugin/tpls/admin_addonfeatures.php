<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php JSSTmessage::getMessage(); ?>
<div id="jsstadmin-wrapper" class="jsstadmin-add-on-page-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Addons List','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Addons List", 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <div class="jsstadmin-add-on-page-wrp">
                <div class="add-on-banner">
                    <img class="add-on-banner-left-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/left-image.png" alt="<?php echo esc_html(__('left image','js-support-ticket')); ?>"/>
                    <img class="add-on-banner-center-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/logo.png" alt="<?php echo esc_html(__('Logo','js-support-ticket')); ?>" />
                    <img class="add-on-banner-right-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/right-image.png" alt="<?php echo esc_html(__('right image','js-support-ticket')); ?>" />
                </div>
                <div class="add-on-page-cnt">
                    <div class="add-on-sec-header">
                        <h1 class="add-on-header-tit"><?php echo esc_html(__('Add-On’s For Help Desk','js-support-ticket')); ?></h1>
                        <div class="add-on-header-text"><?php echo esc_html(__('Get trusted WordPress add on’s. Guaranteed to work fast, safe to use, beautifully coded, packed with features and easy to use.','js-support-ticket')); ?></div>
                    </div>
                    <div class="add-on-msg">
                        <h3 class="add-on-msg-txt"><?php echo esc_html(__('Save big with an exclusive membership plan today!','js-support-ticket')); ?></h3>
                        <a title="<?php echo esc_html(__('Show','js-support-ticket')); ?>" href="https://jshelpdesk.com/pricing/" class="add-on-msg-btn"><i class="fa fa-cart"></i> <?php echo esc_html(__('show bundle pack','js-support-ticket')); ?></a>
                    </div>
                    <div class="add-on-list">
                        <div class="add-on-item agent">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/agent.png" alt="<?php echo esc_html(__('Agent','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Agents','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Add agents and assign roles and permissions to provide assistance and support to customer support tickets.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/agents/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item close-tkt">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/ticket-auto-close.png" alt="<?php echo esc_html(__('Ticket auto close','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Ticket Auto Close','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Define rules for ticket to auto close. Ticket will be auto close after specific interval of time which can be set by admin.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/close-ticket/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item feedback">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/feedback.png" alt="<?php echo esc_html(__('Feedback','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Feedback','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Get the survey from your customers on ticket closing to improve your quality of services and assistance.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/feedback/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item help-topic">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/helptopic.png" alt="<?php echo esc_html(__('helptopic','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Helptopic','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Help topics help users to find and select the area with which they need assistance.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/helptopic/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item private-note">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/private-note.png" alt="<?php echo esc_html(__('private note','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Private Note','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__("The private note is used as reminders or to give other agents insights into the ticket issue. User Won't see the private notes.",'js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/internal-note/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item kb">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/kb.png" alt="<?php echo esc_html(__('Knowledgebase','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Knowledge Base','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Stop losing productivity on repetitive queries,Build your knowledge base, group solutions by topics to facilitate users.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/knowledge-base/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item max-tkt">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/max-ticket.png" alt="<?php echo esc_html(__('max ticket','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Max Tickets','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Enables admin to set N numbers of tickets for users to create and set N numbers of Ticket to open for agents separately.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/max-ticket/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item merge-tkt">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/merge-tickets.png" alt="<?php echo esc_html(__('merge tickets','js-support-ticket')); ?>"/>
                            <div class="add-on-name"><?php echo esc_html(__('Merge Tickets','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Enables agents to merge two tickets of the same user into one instead of dealing with the same issue on many tickets.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/merge-ticket/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item overdue-tkt">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/ticket-overdue.png" alt="<?php echo esc_html(__('Ticket Overdue','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Ticket Overdue','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Defines rules or set specific intervals of time to make ticket auto overdue.The ticket can overdue by type or overdue by Cronjob.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/overdue/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item smtp">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/smtp.png" alt="<?php echo esc_html(__('SMTP','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('SMTP','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('SMTP enables you to add custom mail protocol to send and receive emails within the js help desk.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/smtp/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item multilanguagetemplate">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/multilanguageemailtemplates.png" alt="<?php echo esc_html(__('SMTP','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Multi Language Email Templates','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('It allows to create language-based email templates for all JS Help Desk email templates.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/multi-language-email-templates" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item tkt-histry">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/ticket-history.png" alt="<?php echo esc_html(__('Ticket History','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Ticket History','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Displays complete ticket history along with the ticket status, currently assigned user and other actions performed on each ticket.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/ticket-history/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item canned-resp">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/canned-responses.png" alt="<?php echo esc_html(__('Canned Responses','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Canned Responses','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Canned Responses are pre-populated messages that allows support agents to respond quickly to customer issues.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/canned-responses/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item email-piping">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/email-piping.png" alt="<?php echo esc_html(__('Email Piping','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Email Piping','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Enables users to reply to the tickets via email without the need to login to the support system first.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/email-piping/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item time-tracking">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/time-tracking.png" alt="<?php echo esc_html(__('time tracking','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Time Tracking','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Track the time spent on each ticket by each agent and each reply. Report the admin on how much time is spent on each ticket.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/time-tracking/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item user-opt">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/user-options.png" alt="<?php echo esc_html(__('user options','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('User Options','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('User options enable you to add Google Re-captcha or JS Help Desk Re-captcha for a registration form.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/user-options/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item tkt-actions">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/ticket-actions.png" alt="<?php echo esc_html(__('ticket actions','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Ticket Actions','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Get multiple action options on each ticket like Print Ticket, Lock Ticket, Transfer ticket, etc.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/actions/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item announcements">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/announcments.png" alt="<?php echo esc_html(__('Announcements','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Announcements','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Make unlimited announcements associated with support system to get customer interaction.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/announcements/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item ban-email">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/ban-email.png" alt="<?php echo esc_html(__('Ban Email','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Ban Email','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Ban Email allows you to block email of any user to restrict him to create new tickets.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/ban-email/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item desk-notif">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/desktop-notifications.png" alt="<?php echo esc_html(__('desktop notifications','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Descktop Notifications','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('The Desktop notifications will keep you up to date about anything happens on your support system.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/desktop-notification/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item export">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/export.png" alt="<?php echo esc_html(__('Export','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Export','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Save the ticket as a PDF in your system or the admin will also be able to export all the data inside of Ticket.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/export/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item downloads">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/downloads.png" alt="<?php echo esc_html(__('Downloads','js-support-ticket')); ?>"/>
                            <div class="add-on-name"><?php echo esc_html(__('Downloads','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Create downloads to ensure the user to get downloads from downloads.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/downloads/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item faq">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/faq.png" alt="<?php echo esc_html(__('FAQ','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('FAQ','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Tired of getting tickets about the same problems? Add FAQs to drastically reduce the number of common questions from users.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/faq/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item admin-widg">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/admin-widgets.png" alt="<?php echo esc_html(__('admin widgets','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Admin Widgets','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Get immediate data of your support operations as soon as you log into your WordPress administration area.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/admin-widget/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item internal-mail">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/internal-mail.png" alt="<?php echo esc_html(__('internal mail','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Internal Mail','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Use internal email to send emails to one agent to another agent with in support ticket.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/internal-mail/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                        <div class="add-on-item fe-widget">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/frontend-widget.png" alt="<?php echo esc_html(__('frontend widget','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Front-End Widget','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Widgets in WordPress allow you to add content and features in the widgetized areas of your theme.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/widget/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                        <div class="add-on-item email-piping">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/privatecredentials.png" alt="<?php echo esc_html(__('Private Credentials','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Private Credentials','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__("Collect your customer's private data, sensitive information from credit card to health information and store them encrypted.",'js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/widget/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                        <div class="add-on-item help-topic">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/woocommerce.png" alt="<?php echo esc_html(__('woocommerce support','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('WooCommerce Support','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('JS Help Desk WooCommerce provides the much-needed bridge between your WooCommerce store and the JS Help Desk.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/widget/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                        <div class="add-on-item paid-support">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/paid-support.png" alt="<?php echo esc_html(__('Paid Support','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Paid Support','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Paid Support is the easiest way to integrate and manage payments for your support tickets.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/paid-support/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                        <div class="add-on-item envato">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/envato.png" alt="<?php echo esc_html(__('envato','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Envato','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__("Without valid Envato, license clients won't be able to open a new ticket.",'js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/envato/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                        <div class="add-on-item mail-chimp">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/mail-chimp.png" alt="<?php echo esc_html(__('mail chimp','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Mail Chimp','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('The Mail Chimp add-on adds a new checkbox to the registration form for prompting new users to subscribe your email-list.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/mail-chimp/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                        <div class="add-on-item easy-digi-dwnlds">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/easy-digital-downloads.png" alt="<?php echo esc_html(__('easy digital downloads','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Easy Digital Downloads','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('EDD offers customers to open new tickets just one click from their EDD account with optionally validating the license keys.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/easy-digital-download/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
			<div class="add-on-item email-cc">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/emailcc.png" alt="<?php echo esc_html(__('email cc','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Email Cc','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('CC(Carbon Copy) - the people who should know about the information which is being shared and the people included are able to see who is there in the list.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/email-cc/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item multiform">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/multiform.png" alt="<?php echo esc_html(__('multiform','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Multiform','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('Multiform allows user to add more than one form based on requirements.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/multi-forms/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item agentautoassign">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/agent-auto-assign.png" alt="<?php echo esc_html(__('agent auto assign','js-support-ticket')); ?>" />
                            <div class="add-on-name"><?php echo esc_html(__('Agent Auto Assign','js-support-ticket')); ?></div>

                            <div class="add-on-txt"><?php echo esc_html(__('When auto assignment is enabled and a ticket is created, an appropriate agent is automatically assigned to the ticket and it is moved to the Assigned state.','js-support-ticket')); ?></div>
                            <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" href="https://jshelpdesk.com/product/agent-auto-assign/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>
                        <div class="add-on-item sociallogin">
                            <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/sociallogin.png" alt="login" />
                            <div class="add-on-name"><?php echo esc_html(__('Social Login','js-support-ticket')); ?></div>
                            <div class="add-on-txt"><?php echo esc_html(__('Users can login from their social media accounts. They can create a new account or use social media accounts.','js-support-ticket')); ?></div>
                            <a href="https://jshelpdesk.com/product/social-login/" class="add-on-btn"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                        </div>

                    </div>
                    <div class="add-on-sec-header">
                        <h1 class="add-on-header-tit"><?php echo esc_html(__('JS Help Desk Add-Ons Bundle Pack','js-support-ticket')); ?></h1>
                        <div class="add-on-header-text"><?php echo esc_html(__('Save big with an exclusive membership plan today!','js-support-ticket')); ?></div>
                    </div>
                    <div class="add-on-bundle-pack-list">
                        <div class="add-on-bundle-pack-item basic">
                            <div class="add-on-bundle-pack-name"><?php echo esc_html(__('Basic','js-support-ticket')); ?></div>
                            <?php /* <div class="add-on-bundle-pack-price">$69<span>/ year</span></div> */ ?>
                            <ul class="add-on-bundle-pack-feat">
                                <li><?php echo esc_html(__('Unlimited Agents','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Ticket Actions','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Ticket Auto Close','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('FAQ','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Helptopic','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Ticket History','js-support-ticket')); ?></li>
                                <li><a title="<?php echo esc_html(__('Show all','js-support-ticket')); ?>" target="_blank" href="https://jshelpdesk.com/pricing/#compare-wrap"><?php echo esc_html(__('Show all','js-support-ticket')); ?></a></li>
                            </ul>
                            <div class="add-on-bundle-pack-btn">
                                <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" target="_blank" href="https://jshelpdesk.com/pricing/"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                            </div>
                        </div>
                        <div class="add-on-bundle-pack-item standard">
                            <div class="add-on-bundle-pack-name"><?php echo esc_html(__('Standard','js-support-ticket')); ?></div>
                            <?php /* <div class="add-on-bundle-pack-price">$99<span>/ year</span></div>*/ ?>
                            <ul class="add-on-bundle-pack-feat">
                                <li><strong><?php echo esc_html(__('Everything in basic included and','js-support-ticket')); ?> </strong></li>
                                <li><?php echo esc_html(__('Export','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Announcements','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Internal Mail','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Private Note','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Canned Response','js-support-ticket')); ?></li>
                                <li><a title="<?php echo esc_html(__('Show all','js-support-ticket')); ?>" target="_blank" href="https://jshelpdesk.com/pricing/#compare-wrap"><?php echo esc_html(__('Show all','js-support-ticket')); ?></a></li>
                            </ul>
                            <div class="add-on-bundle-pack-btn">
                                <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" target="_blank" href="https://jshelpdesk.com/pricing/"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                            </div>
                        </div>
                        <div class="add-on-bundle-pack-item professional">
                            <div class="add-on-bundle-pack-name"><?php echo esc_html(__('Professional','js-support-ticket')); ?></div>
                            <?php /* <div class="add-on-bundle-pack-price">$149<span>/ year</span></div>*/ ?>
                            <ul class="add-on-bundle-pack-feat">
                                <li><strong><?php echo esc_html(__('Everything in standard included and','js-support-ticket')); ?></strong></li>
                                <li><?php echo esc_html(__('Feedback','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Knowledge Base','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Merge Tickets','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Email Piping','js-support-ticket')); ?></li>
                                <li><?php echo esc_html(__('Time Tracking','js-support-ticket')); ?></li>
                                <li><strong><?php echo esc_html(__('All Future Addons','js-support-ticket')); ?></strong></li>
                                <li><a title="<?php echo esc_html(__('Show all','js-support-ticket')); ?>" target="_blank" href="https://jshelpdesk.com/pricing/#compare-wrap"><?php echo esc_html(__('Show all','js-support-ticket')); ?></a></li>
                            </ul>
                            <div class="add-on-bundle-pack-btn">
                                <a title="<?php echo esc_html(__('buy now','js-support-ticket')); ?>" target="_blank" href="https://jshelpdesk.com/pricing/"><?php echo esc_html(__('buy now','js-support-ticket')); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
