<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * Email Templates — which message, and what it says.
 *
 * The 24 messages the desk can send used to be 24 hand-written links in a
 * 302px dark column down the left of the content, which is a third level of
 * navigation in a page that already has WordPress's menu and the plugin's own.
 * It stood 1212px tall and pushed the editor - the thing the page is for -
 * to y=827, below the fold on every laptop. They are now one array, rendered
 * as `jsst-srctabs` rows of `a.jsst-chip`, the pattern Knowledge Sources
 * already uses for "pick one of these and the page below changes".
 *
 * Grouping them is the part that makes 24 readable: they answer "when does
 * the desk write to somebody" - when a ticket moves, when the team needs
 * telling, when a sender is banned, when mail arrives, and afterwards.
 *
 * `$jsst_need` is why a message is unavailable, not whether it is: an
 * unavailable message still links, because its template is still stored and
 * still editable, and the asterisk plus the note at the foot say it will not
 * be sent until the addon is on. Removing the links would hide templates
 * people have already written.
 */
$jsst_for   = jssupportticket::$jsst_data[1];
$jsst_tpl   = jssupportticket::$jsst_data[0];
$jsst_nonce_id = isset($jsst_tpl->id) ? $jsst_tpl->id : '';

/* Which addon a message needs, asked once rather than at each of 24 links. */
$jsst_have = array(
    'agent'    => in_array('agent', jssupportticket::$_active_addons),
    'overdue'  => in_array('overdue', jssupportticket::$_active_addons),
    'mail'     => in_array('mail', jssupportticket::$_active_addons),
    'feedback' => in_array('feedback', jssupportticket::$_active_addons),
    'banemail' => JSSTmergedaddon::featureEnabled('banemail'),
    'actions'  => JSSTmergedaddon::featureEnabled('actions'),
);

/* Every msgid here is the one the old template used, unchanged. The two that
   were commented out there (New Department, New Help Topic) stay out. */
$jsst_groups = array(
    array(
        'heading' => __('When a ticket moves', 'js-support-ticket'),
        'items'   => array(
            'tk-nw'    => array(__('New Ticket', 'js-support-ticket'), ''),
            'rpy-tk'   => array(__('Reply Ticket', 'js-support-ticket'), ''),
            'rsp-tk'   => array(__('Response Ticket', 'js-support-ticket'), 'agent'),
            'cl-tk'    => array(__('Close Ticket', 'js-support-ticket'), ''),
            'dl-tk'    => array(__('Delete Ticket', 'js-support-ticket'), ''),
            'rs-tk'    => array(__('Reassign Ticket', 'js-support-ticket'), 'agent'),
            'dt-tk'    => array(__('Department Transfer', 'js-support-ticket'), 'actions'),
            'pc-tk'    => array(__('Ticket Priority Is Changed By', 'js-support-ticket'), ''),
            'minp-tk'  => array(__('In Progress Ticket', 'js-support-ticket'), 'actions'),
            'lk-tk'    => array(__('Lock Ticket', 'js-support-ticket'), 'actions'),
            'ulk-tk'   => array(__('Unlock Ticket', 'js-support-ticket'), 'actions'),
            'mo-tk'    => array(__('Mark Overdue', 'js-support-ticket'), 'overdue'),
            'no-rp'    => array(__('User Reply On Closed Ticket', 'js-support-ticket'), ''),
        ),
    ),
    array(
        'heading' => __('To your team', 'js-support-ticket'),
        'items'   => array(
            'tk-ew-ad' => array(__('New Ticket Admin Alert', 'js-support-ticket'), ''),
            'sntk-tk'  => array(__('Agent Ticket', 'js-support-ticket'), 'agent'),
            'ew-sm'    => array(__('New Agent', 'js-support-ticket'), 'agent'),
        ),
    ),
    array(
        'heading' => __('Banned senders', 'js-support-ticket'),
        'items'   => array(
            'be-tk'    => array(__('Ban Email', 'js-support-ticket'), 'banemail'),
            'be-trtk'  => array(__('Ban Email Try To Create Ticket', 'js-support-ticket'), 'banemail'),
            'ebct-tk'  => array(__('Ban Email And Close Ticket', 'js-support-ticket'), 'banemail'),
            'ube-tk'   => array(__('Unban Email', 'js-support-ticket'), 'banemail'),
        ),
    ),
    array(
        'heading' => __('Mailbox', 'js-support-ticket'),
        'items'   => array(
            'ml-ew'    => array(__('New Mail Received', 'js-support-ticket'), 'mail'),
            'ml-rp'    => array(__('New Mail Message Received', 'js-support-ticket'), 'mail'),
        ),
    ),
    array(
        'heading' => __('Afterwards', 'js-support-ticket'),
        'items'   => array(
            'fd-bk'    => array(__('Feedback Email To User', 'js-support-ticket'), 'feedback'),
            'del-data' => array(__('Data Deleted', 'js-support-ticket'), ''),
        ),
    ),
);

/* What each message actually does - when the desk sends it, and who it
   reaches. The page used to give 24 names and an editor and say neither of
   those things, which is why `Reply Ticket` and `Response Ticket` were
   impossible to tell apart: they are the same moment (a reply is added) seen
   from two sides, one written to your team and one to the customer.

   Read out of modules/email/model.php - for each template key, the
   getTemplateForEmail() call and the sendEmail() recipients that follow it.
   Every recipient here is gated by a switch under Settings > Mail > Ticket
   Operations Email Setting, which is what the note below the list points at. */
$jsst_to = array(
    'user'       => __('The person who opened the ticket', 'js-support-ticket'),
    'admin'      => __('Your admin address', 'js-support-ticket'),
    'agent'      => __('The assigned agent', 'js-support-ticket'),
    'deptmail'   => __('The department address', 'js-support-ticket'),
    'deptagents' => __('Agents in the department', 'js-support-ticket'),
    'newdept'    => __('Agents in the new department', 'js-support-ticket'),
    'banned'     => __('The address being banned', 'js-support-ticket'),
    'unbanned'   => __('The address being unbanned', 'js-support-ticket'),
    'mailto'     => __('Whoever the message is addressed to', 'js-support-ticket'),
);

/* msgid => array(when it is sent, who it goes to). A `when` of '' means the
   desk has no code that sends it - see `be-trtk` below, which is seeded and
   editable but never reached. */
$jsst_sendfacts = array(
    'tk-nw'    => array(__('A ticket is created, however it arrives - the form, e-mail piping or an agent.', 'js-support-ticket'), array('user')),
    'rpy-tk'   => array(__('A reply is added to a ticket, by anyone. This is the copy your team gets; the customer gets Response Ticket.', 'js-support-ticket'), array('admin', 'agent')),
    'rsp-tk'   => array(__('A reply is added to a ticket, by anyone. This is the copy the customer gets; your team gets Reply Ticket.', 'js-support-ticket'), array('user')),
    'cl-tk'    => array(__('A ticket is closed.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'dl-tk'    => array(__('A ticket is deleted.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'rs-tk'    => array(__('A ticket is handed to a different agent.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'dt-tk'    => array(__('A ticket is moved to another department.', 'js-support-ticket'), array('admin', 'agent', 'newdept', 'user')),
    'pc-tk'    => array(__('A ticket changes priority.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'minp-tk'  => array(__('A ticket is marked in progress.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'lk-tk'    => array(__('A ticket is locked, so the customer can no longer reply to it.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'ulk-tk'   => array(__('A locked ticket is opened for replies again.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'mo-tk'    => array(__('A ticket passes its due date and is marked overdue.', 'js-support-ticket'), array('admin', 'agent', 'user')),
    'no-rp'    => array(__('Someone e-mails a reply to a ticket that is already closed.', 'js-support-ticket'), array('user')),
    'tk-ew-ad' => array(__('A ticket is created. This is the copy your desk gets, alongside the one the customer gets.', 'js-support-ticket'), array('admin', 'deptmail')),
    'sntk-tk'  => array(__('A ticket is created, to the agents who could pick it up.', 'js-support-ticket'), array('deptagents')),
    'ew-sm'    => array(__('An agent is added to the desk. It goes to your admin address, not to the new agent.', 'js-support-ticket'), array('admin')),
    'be-tk'    => array(__('An address is added to the ban list.', 'js-support-ticket'), array('admin', 'agent', 'banned')),
    'be-trtk'  => array('', array()),
    'ebct-tk'  => array(__('An address is banned and its open ticket is closed in the same step.', 'js-support-ticket'), array('admin', 'agent', 'banned')),
    'ube-tk'   => array(__('An address is taken off the ban list.', 'js-support-ticket'), array('admin', 'agent', 'unbanned')),
    'ml-ew'    => array(__('A message is sent from the desk mailbox.', 'js-support-ticket'), array('mailto')),
    'ml-rp'    => array(__('A reply is sent from the desk mailbox.', 'js-support-ticket'), array('mailto')),
    'fd-bk'    => array(__('A closed ticket is ready for its feedback request.', 'js-support-ticket'), array('user')),
    'del-data' => array(__('A customer asks for their data to be erased, and it is.', 'js-support-ticket'), array('user')),
);

/* The placeholders, as one table rather than the 270-line if/elseif chain
   that stood here. The chain had drifted from the mailer in six places -
   `{STAFF_MEMBER_NAME}` was offered for four messages and is replaced for
   none of them (the mailer only ever writes `{AGENT_NAME}`), `{TICKETID}`
   was offered for Ban And Close where the mailer writes `{TRACKINGID}`, and
   `{TICKET_SUBJECT}` was offered for User Reply On Closed Ticket where the
   mailer writes `{SUBJECT}`. Following the page's own reference put a
   literal `{STAFF_MEMBER_NAME}` into outgoing mail.

   Every entry below was checked against the $jsst_matcharray the message is
   actually sent with; a table makes that checkable at a glance, which is the
   real reason for the rewrite. */
$jsst_pname = array(
    'USERNAME'         => __('Username', 'js-support-ticket'),
    'USER_NAME'        => __('User Name', 'js-support-ticket'),
    'SUBJECT'          => __('Subject', 'js-support-ticket'),
    'TICKET_SUBJECT'   => __('Ticket Subject', 'js-support-ticket'),
    'TRACKINGID'       => __('Tracking ID', 'js-support-ticket'),
    'TRACKING_ID'      => __('Ticket Tracking ID', 'js-support-ticket'),
    'HELP_TOPIC'       => __('Help Topic', 'js-support-ticket'),
    'EMAIL'            => __('Email', 'js-support-ticket'),
    'EMAIL_ADDRESS'    => __('Email Address', 'js-support-ticket'),
    'MESSAGE'          => __('Message', 'js-support-ticket'),
    'TICKETURL'        => __('Ticket URL', 'js-support-ticket'),
    'FEEDBACKURL'      => __('Feedback URL', 'js-support-ticket'),
    'LINK'             => __('Feedback URL', 'js-support-ticket'),
    'DEPARTMENT'       => __('Department', 'js-support-ticket'),
    'DEPARTMENT_TITLE' => __('Department', 'js-support-ticket'),
    'PRIORITY'         => __('Ticket Priority', 'js-support-ticket'),
    'PRIORITY_TITLE'   => __('Priority', 'js-support-ticket'),
    'TICKET_HISTORY'   => __('Ticket History', 'js-support-ticket'),
    'AGENT_NAME'       => __('Agent name', 'js-support-ticket'),
    'CLOSE_DATE'       => __('Close Date', 'js-support-ticket'),
    'SITETITLE'        => __('Site title', 'js-support-ticket'),
    'CURRENT_YEAR'     => __('Current year', 'js-support-ticket'),
);

/* `{SITETITLE}` and `{CURRENT_YEAR}` are replaced for all 24 and were listed
   for none - they are in every seeded template, so a reader could see them
   working in the body and not find them in the reference. */
$jsst_params_all = array('SITETITLE', 'CURRENT_YEAR');

$jsst_params = array(
    'tk-nw'    => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'MESSAGE', 'TICKETURL', 'DEPARTMENT', 'PRIORITY'),
    'tk-ew-ad' => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'MESSAGE', 'TICKETURL', 'DEPARTMENT', 'PRIORITY'),
    'sntk-tk'  => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'MESSAGE', 'TICKETURL', 'DEPARTMENT', 'PRIORITY'),
    'rpy-tk'   => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'MESSAGE', 'TICKETURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'rsp-tk'   => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'MESSAGE', 'TICKETURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'cl-tk'    => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'MESSAGE', 'TICKETURL', 'FEEDBACKURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'dl-tk'    => array('SUBJECT', 'TRACKINGID'),
    'rs-tk'    => array('SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'AGENT_NAME', 'TICKETURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'dt-tk'    => array('SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'DEPARTMENT_TITLE', 'PRIORITY'),
    'pc-tk'    => array('SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'PRIORITY_TITLE', 'TICKETURL', 'DEPARTMENT', 'TICKET_HISTORY'),
    'minp-tk'  => array('SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'TICKETURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'lk-tk'    => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'TICKETURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'ulk-tk'   => array('USERNAME', 'SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL', 'TICKETURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'mo-tk'    => array('SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'TICKETURL', 'DEPARTMENT', 'PRIORITY', 'TICKET_HISTORY'),
    'no-rp'    => array('SUBJECT', 'DEPARTMENT', 'PRIORITY'),
    'ew-sm'    => array('AGENT_NAME'),
    'be-tk'    => array('EMAIL_ADDRESS'),
    'be-trtk'  => array('EMAIL_ADDRESS'),
    'ebct-tk'  => array('SUBJECT', 'TRACKINGID', 'HELP_TOPIC', 'EMAIL_ADDRESS', 'DEPARTMENT', 'PRIORITY'),
    'ube-tk'   => array('EMAIL_ADDRESS'),
    'ml-ew'    => array('SUBJECT', 'AGENT_NAME', 'MESSAGE'),
    'ml-rp'    => array('SUBJECT', 'AGENT_NAME', 'MESSAGE'),
    'fd-bk'    => array('USER_NAME', 'TICKET_SUBJECT', 'TRACKING_ID', 'CLOSE_DATE', 'LINK', 'DEPARTMENT', 'PRIORITY'),
    'del-data' => array('USERNAME'),
);

/* The messages whose mailer also builds `{<custom field>}` out of the ticket's
   own form data. Reply and Response were missing from the old chain and do
   support them - modules/email/model.php cases 4 and 5 both run the custom
   field loop. */
$jsst_params_custom = array(
    'tk-nw', 'tk-ew-ad', 'sntk-tk', 'rpy-tk', 'rsp-tk', 'cl-tk', 'rs-tk', 'dt-tk',
    'pc-tk', 'minp-tk', 'lk-tk', 'ulk-tk', 'mo-tk', 'no-rp', 'ebct-tk', 'fd-bk',
);

/* The name of the message being edited, for the editor's own heading - so the
   editor says what it is editing without the reader looking back up at the
   chips. */
$jsst_forname = '';
foreach ($jsst_groups as $jsst_group) {
    if (isset($jsst_group['items'][$jsst_for])) {
        $jsst_forname = $jsst_group['items'][$jsst_for][0];
        break;
    }
}

/* Whether this message can be overridden per form or per language. Worked out
   here rather than beside the card that shows it, because the editor renders
   first now and `$jsst_class` is a live hook on it. */
$jsst_showformdata = !in_array($jsst_for, ['del-data','ml-rp','ml-ew','rpy-tk','rsp-tk','ube-tk','be-trtk','be-tk','dl-tk','ew-sm'])
                     && in_array('multiform', jssupportticket::$_active_addons);
$jsst_haslang      = in_array('multilanguageemailtemplates', jssupportticket::$_active_addons);
$jsst_class        = ($jsst_showformdata || $jsst_haslang) ? 'js-custom-email-body' : '';

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Email Templates', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <?php /* The lede carries the one fact the rest of the page cannot: that
                     writing a message here is not what makes it send. The switches are
                     on another screen entirely, and an admin who edits a template and
                     sees nothing arrive has no way to find that out from here. Same
                     rank problem as the live chat note - it answers the question the
                     whole screen raises, so it goes above the answers, not inside one. */ ?>
            <p class="jsst-lede">
                <?php echo esc_html(__('The e-mails the desk sends by itself, with nobody writing them. Pick one below to edit its subject and body.', 'js-support-ticket')); ?>
                <?php echo esc_html(__('Editing a message does not switch it on: whether it goes out at all, and to whom, is set under', 'js-support-ticket')); ?>
                <a href="<?php echo esc_url('?page=configuration&jsstconfigid=mailsetting'); ?>"><?php echo esc_html(__('Ticket Operations Email Setting', 'js-support-ticket')); ?></a>.
            </p>

            <?php /* The message list, beside the editor rather than over it.

                     It has now been three shapes. A 302px dark column of 24 hand-written
                     links (1212px tall, editor at y=827). Then chips in five labelled
                     rows, then five labelled columns - and grouped chips were still a
                     block of scaffolding the reader had to cross before reaching the
                     field they came for, which is what the user kept reporting.

                     A list down the side is not a step before the editor, it is a place
                     the editor lives in: the subject field is at the top of the page and
                     the 24 are always in view and always one click away. The five groups
                     are gone from the surface - the flat list is short enough to scan and
                     the moment and recipients are stated properly in the editor itself.

                     Sticky, and it scrolls on its own when the viewport is short, so the
                     list never pushes the page taller than the editor needs. */ ?>
            <div class="jsst-msgpick">
                <nav class="jsst-msglist" aria-label="<?php echo esc_attr(__('Email Templates', 'js-support-ticket')); ?>">
                    <?php foreach ($jsst_groups AS $jsst_group) {
                        foreach ($jsst_group['items'] AS $jsst_key => $jsst_item) {
                            $jsst_off = !empty($jsst_item[1]) && empty($jsst_have[$jsst_item[1]]); ?>
                            <a class="jsst-msgitem<?php if ($jsst_for == $jsst_key) echo ' jsst-msgitem-on'; ?>"
                               href="<?php echo esc_url('?page=emailtemplate&for=' . $jsst_key); ?>"
                               title="<?php echo esc_attr($jsst_item[0]); ?>"
                               <?php if ($jsst_for == $jsst_key) echo 'aria-current="page"'; ?>><?php
                                echo esc_html($jsst_item[0]);
                                if ($jsst_off) { ?><span class="jsst-chip-na" aria-hidden="true">*</span><span class="screen-reader-text"><?php echo esc_html(__('are only available with its own addon.', 'js-support-ticket')); ?></span><?php }
                            ?></a>
                        <?php }
                    } ?>
                </nav>

                <div class="jsst-msgpane">

            <?php /* Which version of this message is being edited.

                     This was TWO cards and a table sitting BELOW the editor, so the
                     page read pick -> edit -> pick again. `Default Email Template`
                     was a whole card built to hold one link; `Form & Language-Specific
                     Email Templates` held a create form and then a table of the same
                     thing under its own heading. All three answer one question - which
                     version - and that question belongs beside the message picker, not
                     after the field the reader came to type in.

                     One card, the versions as rows of a table so the current one can
                     be marked, and the rare act - making a new version - folded into a
                     <details>. Switching between versions is the common one and is now
                     a click from the top of the page rather than a scroll past the
                     editor. */ ?>
            <?php if ($jsst_showformdata || $jsst_haslang) {
                $jsst_isdefault = empty($jsst_tpl->multiformname) && empty($jsst_tpl->language_name); ?>

                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Which version', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <p class="jsst-card-sub"><?php
                            if ($jsst_showformdata && $jsst_haslang) {
                                echo esc_html(__('The default is sent for every form and every language. A version made for one form or one language is used instead of it, where there is one.', 'js-support-ticket'));
                            } elseif ($jsst_showformdata) {
                                echo esc_html(__('The default is sent for every form. A version made for one form is used instead of it, where there is one.', 'js-support-ticket'));
                            } else {
                                echo esc_html(__('The default is sent for every language. A version made for one language is used instead of it, where there is one.', 'js-support-ticket'));
                            } ?></p>

                        <?php /* A list, not a table. Two columns of which one held a
                                 name and the other three links never needed a header
                                 row, a `Version` / `Actions` legend or cell borders -
                                 that is table furniture around what is really a set of
                                 options, usually one or two of them. The current one is
                                 the row itself: an accent edge and a tinted ground say
                                 "this is the one open below" without a pill having to
                                 announce it. */ ?>
                        <ul class="jsst-versions">
                            <li class="jsst-version<?php if ($jsst_isdefault) echo ' jsst-version-on'; ?>">
                                <span class="jsst-version-main">
                                    <span class="jsst-version-name"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></span>
                                    <?php /* The scope is what tells this row from a form that is itself
                                             named "Default" - and it is the fact the row is here to state. */ ?>
                                    <span class="jsst-version-scope"><?php
                                        if ($jsst_showformdata && $jsst_haslang) {
                                            echo esc_html(__('All forms and languages', 'js-support-ticket'));
                                        } elseif ($jsst_showformdata) {
                                            echo esc_html(__('All forms', 'js-support-ticket'));
                                        } else {
                                            echo esc_html(__('All languages', 'js-support-ticket'));
                                        } ?></span>
                                </span>
                                <span class="jsst-version-act">
                                    <?php if ($jsst_isdefault) { ?>
                                        <span class="jsst-version-now"><?php echo esc_html(__('Editing now', 'js-support-ticket')); ?></span>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url('?page=emailtemplate&for=' . $jsst_for . '&defaultTemp=1'); ?>" class="jsst-act"><?php echo esc_html(__('Edit', 'js-support-ticket')); ?></a>
                                    <?php } ?>
                                </span>
                            </li>
                            <?php if (!empty($jsst_tpl->multiTemplates)) {
                                foreach ($jsst_tpl->multiTemplates AS $jsst_key => $jsst_multiTemplate) {
                                    if (empty($jsst_multiTemplate->formname) && empty($jsst_multiTemplate->language)) {
                                        continue;
                                    }
                                    $jsst_row = '?page=emailtemplate&for=' . $jsst_for . '&formid=' . $jsst_multiTemplate->formid . '&langcode=' . $jsst_multiTemplate->language;
                                    /* Name the version in the words the reader picked it by. The old table
                                       gave Form Name, Department Name and Language a column each, so a
                                       language-only version was two empty cells and a word. */
                                    $jsst_vname = array();
                                    if (!empty($jsst_multiTemplate->formname)) {
                                        $jsst_vname[] = $jsst_multiTemplate->formname;
                                    }
                                    if (!empty($jsst_multiTemplate->language_name)) {
                                        $jsst_vname[] = $jsst_multiTemplate->language_name;
                                    }
                                    $jsst_iscurrent = (!empty($jsst_tpl->multiformname) && $jsst_tpl->multiformname == $jsst_multiTemplate->formname)
                                                      || (!empty($jsst_tpl->language_name) && $jsst_tpl->language_name == $jsst_multiTemplate->language_name); ?>
                                    <li class="jsst-version<?php if ($jsst_iscurrent) echo ' jsst-version-on'; ?>">
                                        <span class="jsst-version-main">
                                            <span class="jsst-version-name"><?php echo esc_html(implode(' / ', $jsst_vname)); ?></span>
                                            <?php if (!empty($jsst_multiTemplate->departmentname)) { ?>
                                                <span class="jsst-version-scope"><?php echo esc_html($jsst_multiTemplate->departmentname); ?></span>
                                            <?php } ?>
                                        </span>
                                        <span class="jsst-version-act">
                                            <?php if ($jsst_iscurrent) { ?>
                                                <span class="jsst-version-now"><?php echo esc_html(__('Editing now', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <a href="<?php echo esc_url($jsst_row); ?>" class="jsst-act"><?php echo esc_html(__('Edit', 'js-support-ticket')); ?></a>
                                            <?php } ?>
                                            <a href="<?php echo esc_url($jsst_row); ?>" class="jsst-act"><?php echo esc_html(__('Preview', 'js-support-ticket')); ?></a>
                                            <a onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=emailtemplate&task=deleteformemailtemplate&action=jstask&templateid='.esc_attr($jsst_multiTemplate->template_id).'&for='.esc_attr($jsst_for).'&source='.esc_attr($jsst_multiTemplate->source),'delete-template-'.$jsst_multiTemplate->template_id)); ?>" class="jsst-act jsst-act-danger"><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></a>
                                        </span>
                                    </li>
                                <?php }
                            } ?>
                        </ul>


                        <?php /* Making a version is the rare act - most desks run the
                                 default and nothing else - so it costs one line until
                                 somebody wants it. */ ?>
                        <details class="jsst-details">
                            <summary><?php
                                if ($jsst_showformdata && $jsst_haslang) {
                                    echo esc_html(__('Add a version for a form or a language', 'js-support-ticket'));
                                } elseif ($jsst_showformdata) {
                                    echo esc_html(__('Add a version for a form', 'js-support-ticket'));
                                } else {
                                    echo esc_html(__('Add a version for a language', 'js-support-ticket'));
                                } ?></summary>
                            <div class="jsst-details-body">
                                <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'emailtemplate', 'task'=>'savecustomemailtemplate')),"save-form-email-template")); ?>">
                                    <div class="jsst-formgrid">
                                        <?php if ($jsst_showformdata) { ?>
                                            <div class="jsst-frow jsst-frow-md">
                                                <label class="jsst-flabel" for="multiformid"><?php echo esc_html(__('Form', 'js-support-ticket')); ?></label>
                                                <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('multiformid', JSSTincluder::getJSModel('multiform')->getMultiFormForCombobox($jsst_tpl->templatefor), '', esc_html(__('Select a Form', 'js-support-ticket')), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS); ?></div>
                                            </div>
                                        <?php }
                                        if ($jsst_haslang) { ?>
                                            <div class="jsst-frow jsst-frow-md">
                                                <label class="jsst-flabel" for="language_id"><?php echo esc_html(__('Language', 'js-support-ticket')); ?></label>
                                                <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select("language_id", JSSTincluder::getJSModel("multilanguageemailtemplates")->getLangForCombobox() ,'',__("Select a Language", "js-support-ticket"), array("class" => "jsst-select")), JSST_ALLOWED_TAGS); ?></div>
                                            </div>
                                        <?php } ?>
                                        <div class="jsst-frow jsst-frow-action">
                                            <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Create Template', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <?php echo wp_kses(JSSTformfield::hidden('id', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('templatefor', $jsst_tpl->templatefor), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('for', $jsst_for), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('action', 'emailtemplate_savecustomemailtemplate'), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>

            <?php } ?>

            <?php /* The editor. `js-email-body` is a live hook, not styling:
                     scrollToFormByParam() at the foot of this file scrolls to
                     it when the page arrives with formid / langcode /
                     defaultTemp, so a reader who clicked Edit lands on the
                     thing they clicked. `$jsst_class` is the second hook. */ ?>
            <form method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=emailtemplate&task=saveemailtemplate"),"save-email-template-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel js-email-body <?php echo esc_attr($jsst_class); ?>">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php
                                /* Say which of the 24 is being edited, so the editor does not
                                   depend on the reader remembering which chip they clicked. */
                                echo esc_html($jsst_forname !== '' ? $jsst_forname : __('Email Templates', 'js-support-ticket')); ?></legend>

                            <?php /* What this message is, before what it says. The name alone
                                     never distinguished Reply Ticket from Response Ticket, or
                                     New Ticket from New Ticket Admin Alert; the moment and the
                                     recipients do, in two lines, without opening the mailer. */
                            $jsst_fact = isset($jsst_sendfacts[$jsst_for]) ? $jsst_sendfacts[$jsst_for] : null;
                            if ($jsst_fact !== null && $jsst_fact[0] === '') { ?>
                                <div class="jsst-notice jsst-notice-warn">
                                    <p><?php echo esc_html(__('Nothing in the desk sends this message. It is stored and you can edit it, but no ticket, ban or mailbox event reaches it.', 'js-support-ticket')); ?></p>
                                </div>
                            <?php } elseif ($jsst_fact !== null) { ?>
                                <dl class="jsst-facts jsst-sendfacts">
                                    <dt><?php echo esc_html(__('Sent when', 'js-support-ticket')); ?></dt>
                                    <dd><?php echo esc_html($jsst_fact[0]); ?></dd>
                                    <dt><?php echo esc_html(__('Goes to', 'js-support-ticket')); ?></dt>
                                    <dd>
                                        <?php foreach ($jsst_fact[1] AS $jsst_who) { ?>
                                            <span class="jsst-pill jsst-pill-off"><?php echo esc_html($jsst_to[$jsst_who]); ?></span>
                                        <?php } ?>
                                    </dd>
                                </dl>
                            <?php } ?>

                            <?php if (!empty($jsst_tpl->language_name) || !empty($jsst_tpl->multiformname)) { ?>
                                <?php /* An override, not the default - say what it overrides. */ ?>
                                <div class="jsst-chips">
                                    <?php if (!empty($jsst_tpl->multiformname)) { ?>
                                        <span class="jsst-chip"><?php echo esc_html(__('Form', 'js-support-ticket') . ': ' . $jsst_tpl->multiformname); ?></span>
                                    <?php }
                                    if (!empty($jsst_tpl->language_name)) { ?>
                                        <span class="jsst-chip"><?php echo esc_html(__('Language', 'js-support-ticket') . ': ' . $jsst_tpl->language_name); ?></span>
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-full">
                                    <label class="jsst-flabel" for="subject"><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('subject', $jsst_tpl->subject, array('class' => 'inputbox')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <label class="jsst-flabel" for="body"><?php echo esc_html(__('Body', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval jsst-editor"><?php wp_editor($jsst_tpl->body, 'body', array('media_buttons' => false)); ?></div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Parameters', 'js-support-ticket')); ?></legend>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Type one of these into the subject or the body and it is replaced by the real value when the message is sent.', 'js-support-ticket')); ?></p>
                            <div class="jsst-params">
                        <?php
                        /* One loop over the table above. `{<custom field>}` is
                           appended for the messages whose mailer builds it, and
                           the two that work everywhere come last so the list
                           still reads specific-first. */
                        $jsst_keys = isset($jsst_params[$jsst_for]) ? $jsst_params[$jsst_for] : array();
                        foreach ($jsst_keys AS $jsst_pkey) {
                            $jsst_plabel = isset($jsst_pname[$jsst_pkey]) ? $jsst_pname[$jsst_pkey] : $jsst_pkey; ?>
                            <span class="jsst-param"><code class="jsst-param-key">{<?php echo esc_html($jsst_pkey); ?>}</code><span class="jsst-param-name"><?php echo esc_html($jsst_plabel); ?></span></span>
                        <?php }
                        if (in_array($jsst_for, $jsst_params_custom) && !empty(jssupportticket::$jsst_data[2])) {
                            foreach (jssupportticket::$jsst_data[2] AS $jsst_field) {
                                if ($jsst_field->userfieldtype != 'file') { ?>
                                    <span class="jsst-param"><code class="jsst-param-key">{<?php echo esc_html($jsst_field->field); ?>}</code><span class="jsst-param-name"><?php echo esc_html($jsst_field->fieldtitle); ?></span></span>
                                <?php }
                            }
                        }
                        foreach ($jsst_params_all AS $jsst_pkey) { ?>
                            <span class="jsst-param"><code class="jsst-param-key">{<?php echo esc_html($jsst_pkey); ?>}</code><span class="jsst-param-name"><?php echo esc_html($jsst_pname[$jsst_pkey]); ?></span></span>
                        <?php }
                        ?>
                            </div>
                        </fieldset>
                    </div>

                    <div class="jsst-formfoot">
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Email Template', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary js-emailtemplate-save')), JSST_ALLOWED_TAGS); ?>
                        <?php if (count(jssupportticket::$_active_addons) < 36) { ?>
                            <span class="jsst-formfoot-note">
                                <?php echo esc_html(__('Features marked with', 'js-support-ticket')); ?>
                                <span class="jsst-chip-na">*</span>
                                <?php echo esc_html(__('are only available with its own addon.', 'js-support-ticket')); ?>
                            </span>
                        <?php } ?>
                    </div>
                </div>

                <?php
                /* Bound by class, not by `#save`. JSSTformfield::submitbutton()
                   takes the id from the name, and both this form's save button and
                   the Create Template button above are name="save" -- so `#save`
                   matched whichever came first in the document, which is Create
                   Template whenever the multiform or multilanguage addon is on.
                   The check has been guarding the wrong button. */
                $jsst_jssupportticket_js ="
                    jQuery(document).ready(function(){
                        jQuery('.js-emailtemplate-save').click(function(){
                            var subject = jQuery('#subject').val();
                            var body = jQuery('#body').val();
                            if(subject=='' && body==''){
                                alert('". esc_js(__('Please fill in the subject and body.', 'js-support-ticket')) ."');
                                return false;
                            }
                        });
                    });
                ";
                wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
                if (!empty(jssupportticket::$jsst_data[0]->language_id)) {
                    $jsst_language_id = jssupportticket::$jsst_data[0]->language_id;
                } else {
                    $jsst_language_id = '';
                }
                ?>

                <?php echo wp_kses(JSSTformfield::hidden('id', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', jssupportticket::$jsst_data[0]->created), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('templatefor', jssupportticket::$jsst_data[0]->templatefor), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('multiformid', jssupportticket::$jsst_data[0]->multiformid), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('for', jssupportticket::$jsst_data[1]), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'emailtemplate_saveemailtemplate'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('callfor', 'emailtemplate'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('multitemp_id', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('lang_id', $jsst_language_id), JSST_ALLOWED_TAGS); ?>
            </form>

                </div>
            </div>

        </div>
    </div>
</div>
<?php
$jsst_jssupportticket_js ="
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
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
?>
