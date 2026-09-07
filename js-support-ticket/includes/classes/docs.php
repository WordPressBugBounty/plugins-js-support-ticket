<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTdocs')) {
    return;
}

/**
 * The diagnostic pages, and the link from an error to the right one.
 * (Roadmap 4.0-OPS-03)
 *
 * Documentation written the way somebody arrives at it. Nobody opens a help
 * page to learn about the email subsystem; they open it because a customer says
 * they never got a reply. So every page here starts from the thing that went
 * wrong and is a list of checks in the order worth doing them, with the screen
 * that answers each one linked from the step itself.
 *
 * The other half is that errors carry their page with them. A message that says
 * "the mailbox could not be reached" and stops has handed the reader a search
 * box; the same message with a link has handed them the next five minutes of
 * their afternoon. Every setMessage() call may name a page id, and the id is
 * checked against this catalogue at render time, so a link is either right or
 * absent — never a promise of a page that does not exist.
 *
 * Kept in code rather than in a table, and in one file rather than beside the
 * feature each page is about, because it is the reference that has to stay true
 * when the features move. It ships with the plugin, works offline, and cannot
 * drift out of sync with a website nobody remembers to update.
 */
class JSSTdocs {

    /** Where the pages live. */
    public static function url($jsst_id = '') {
        $jsst_url = admin_url('admin.php?page=jssupportticket&jstlay=diagnostics');
        if ($jsst_id !== '' && self::exists($jsst_id)) {
            $jsst_url .= '&topic=' . rawurlencode($jsst_id) . '#jsst-doc-' . rawurlencode($jsst_id);
        }
        return $jsst_url;
    }

    public static function exists($jsst_id) {
        $jsst_pages = self::pages();
        return isset($jsst_pages[$jsst_id]);
    }

    public static function page($jsst_id) {
        $jsst_pages = self::pages();
        return isset($jsst_pages[$jsst_id]) ? $jsst_pages[$jsst_id] : null;
    }

    /**
     * Every page, grouped the way the roadmap asks for: the portal a customer
     * sees, the desk an agent works, email, migrations, permissions, and the
     * things that go wrong underneath all of them.
     *
     * A step is a sentence and, where one exists, the screen that answers it.
     * Steps are ordered by what is most often the cause, not by what is most
     * interesting — the first check on a mail page is whether WordPress can send
     * mail at all, because it usually cannot.
     */
    public static function pages() {
        $jsst_status = admin_url('admin.php?page=jssupportticket&jstlay=systemstatus');
        $jsst_mail   = admin_url('admin.php?page=email&jstlay=emailhealth');
        $jsst_agents = admin_url('admin.php?page=jssupportticket&jstlay=agentaccess');
        $jsst_config = admin_url('admin.php?page=configuration');
        $jsst_import = admin_url('admin.php?page=thirdpartyimport&jstlay=importdata');
        $jsst_copilot= admin_url('admin.php?page=copilot&jstlay=copilot');

        $jsst_pages = array(

            /* ---------------------------------------------------------- *
             * Email
             * ---------------------------------------------------------- */
            'email-not-sending' => array(
                'group'   => esc_html(__('Email', 'js-support-ticket')),
                'title'   => esc_html(__('A notification never arrived', 'js-support-ticket')),
                'symptom' => esc_html(__('A customer or agent says they were never told about a ticket or a reply. The ticket itself is fine — the message about it did not arrive.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Send a test message from Email Health. If it fails here, nothing else on this page matters — WordPress itself cannot send mail on this site.', 'js-support-ticket')),
                        'link' => $jsst_mail,
                        'as'   => esc_html(__('Email Health', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check whether an SMTP plugin is installed. A default WordPress install sends through the server\'s mail command, which most hosts either disable or send straight to spam. Email Health names the sender it is using.', 'js-support-ticket')),
                        'link' => $jsst_mail,
                        'as'   => esc_html(__('Email Health', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check the background queue. Notifications are queued rather than sent inside the request, so a queue nobody is working means every screen says the reply was posted and no mail ever leaves.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check that the template for this event is switched on. A disabled template is silent by design and looks identical to a delivery failure.', 'js-support-ticket')),
                        'link' => admin_url('admin.php?page=emailtemplate'),
                        'as'   => esc_html(__('Email Templates', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Ask the recipient to look in spam, and check the sender domain matches this site. Mail sent as an address at a domain this server is not authorised to send for is usually silently discarded.', 'js-support-ticket')),
                    ),
                ),
            ),

            'email-piping' => array(
                'group'   => esc_html(__('Email', 'js-support-ticket')),
                'title'   => esc_html(__('Replies sent by email are not appearing on tickets', 'js-support-ticket')),
                'symptom' => esc_html(__('Customers reply to a notification and nothing shows up on the ticket, or new mail to the support address never becomes a ticket.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Check that collection is scheduled and running. Mail is fetched on a schedule, so a stalled scheduler means a mailbox filling up quietly.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check the mailbox credentials and that the host allows outbound IMAP or POP connections. A wrong password and a blocked port fail the same way from here.', 'js-support-ticket')),
                        'link' => admin_url('admin.php?page=emailpiping'),
                        'as'   => esc_html(__('Email Piping', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check the banned address list. A customer whose address was banned has their mail accepted and dropped.', 'js-support-ticket')),
                        'link' => admin_url('admin.php?page=banemail'),
                        'as'   => esc_html(__('Banned Emails', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Read the collection log on Email Health. It records every run: which mailboxes were opened, how many messages were in them, and what each message became. A message that was read and deliberately dropped — a reply to a closed ticket, mail to a mailbox set to collect new tickets only — is named there, and looks like nothing at all everywhere else.', 'js-support-ticket')),
                        'link' => $jsst_mail,
                        'as'   => esc_html(__('Email Health', 'js-support-ticket')),
                    ),
                ),
            ),

            /* ---------------------------------------------------------- *
             * The desk an agent works
             * ---------------------------------------------------------- */
            'scheduled-work' => array(
                'group'   => esc_html(__('Troubleshooting', 'js-support-ticket')),
                'title'   => esc_html(__('Nothing is happening in the background', 'js-support-ticket')),
                'symptom' => esc_html(__('Notifications are not going out, mail is not being collected, retention is not clearing anything, or an import or conversion has sat at the same number for a long time.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Open System Status and read the Scheduled work card. Anything marked Not scheduled or Overdue is the answer.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('If WP-Cron is switched off in wp-config.php, check that a real cron job calls wp-cron.php. That configuration looks perfectly healthy until somebody asks why nothing has been sent for a week.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('On a quiet site, WordPress only runs scheduled work when somebody visits. A site with no visitors has no scheduler — a real cron job is the fix.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check the queue for jobs that gave up. A job that failed three times is left with its reason on it rather than being retried forever.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                ),
            ),

            'attachments' => array(
                'group'   => esc_html(__('Troubleshooting', 'js-support-ticket')),
                'title'   => esc_html(__('An attachment will not upload, or will not download', 'js-support-ticket')),
                'symptom' => esc_html(__('Attaching a file fails with no explanation, or a file that was attached cannot be opened again.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Check the folder permissions card. An upload that fails with no message is almost always a directory this site cannot write to.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Compare the file size against the upload limit the server allows. The environment card lists both limits, and the smaller one wins.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check the allowed file types. A rejected extension is a deliberate refusal, not a fault.', 'js-support-ticket')),
                        'link' => $jsst_config,
                        'as'   => esc_html(__('Configurations', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Attachments are served through the plugin rather than by direct URL, so a link copied from the file system will not open. Use the link on the ticket.', 'js-support-ticket')),
                    ),
                ),
            ),

            /* ---------------------------------------------------------- *
             * Permissions
             * ---------------------------------------------------------- */
            'agent-permissions' => array(
                'group'   => esc_html(__('Permissions', 'js-support-ticket')),
                'title'   => esc_html(__('An agent cannot see or do something', 'js-support-ticket')),
                'symptom' => esc_html(__('An agent reports a missing menu, an empty ticket list, or a button that says they do not have permission.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Open Agent Access and find them. It shows their WordPress role, their help desk role, the departments they cover and what that adds up to, in one place — which is usually enough on its own.', 'js-support-ticket')),
                        'link' => $jsst_agents,
                        'as'   => esc_html(__('Agent Access', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('An empty ticket list is nearly always department scope rather than permissions: an agent sees the departments they are assigned to and no others.', 'js-support-ticket')),
                        'link' => $jsst_agents,
                        'as'   => esc_html(__('Agent Access', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check the help desk role rather than the WordPress role. A WordPress administrator with no help desk role assigned still sees very little.', 'js-support-ticket')),
                        'link' => admin_url('admin.php?page=role'),
                        'as'   => esc_html(__('Agent Roles', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('If the menu itself is missing, the capability was never granted. Deactivating and reactivating the plugin reconciles roles and capabilities from a single definition and repairs it.', 'js-support-ticket')),
                    ),
                ),
            ),

            'portal-access' => array(
                'group'   => esc_html(__('Customer portal', 'js-support-ticket')),
                'title'   => esc_html(__('Customers cannot reach the portal or their tickets', 'js-support-ticket')),
                'symptom' => esc_html(__('The support page is blank, sends people to a login they cannot pass, or shows a customer none of the tickets they have raised.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Check the page still contains the help desk shortcode. A theme change or a page rebuild is the usual way it goes missing, and the result is a blank page rather than an error.', 'js-support-ticket')),
                        'link' => admin_url('edit.php?post_type=page'),
                        'as'   => esc_html(__('Pages', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Check whether guests are allowed to open tickets, or whether an account is required. Both are valid settings and they look very different to a customer.', 'js-support-ticket')),
                        'link' => $jsst_config,
                        'as'   => esc_html(__('Configurations', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('A customer sees the tickets attached to their account. Tickets raised by email before they had an account, or under another address, belong to a different customer record.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('If the portal is styled wrongly rather than missing, the generated colour stylesheet may not be writable. The folder permissions card says so.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                ),
            ),

            /* ---------------------------------------------------------- *
             * Migrations
             * ---------------------------------------------------------- */
            'migration' => array(
                'group'   => esc_html(__('Migrations', 'js-support-ticket')),
                'title'   => esc_html(__('Importing from another help desk', 'js-support-ticket')),
                'symptom' => esc_html(__('Moving from SupportCandy, Fluent Support or Awesome Support, or an import that has stopped, duplicated something, or brought less across than expected.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Preview before importing. The preview counts every record in the source and, more importantly, names what will be dropped because the add-on that holds it is not active here — a decision to make before the old help desk is deleted.', 'js-support-ticket')),
                        'link' => $jsst_import,
                        'as'   => esc_html(__('Import Data', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Take a database backup first. The import can be rolled back exactly, but a backup is what covers everything a rollback is not designed for.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('An import that stopped part way has not lost anything. Every record it created is recorded, so it can be rolled back and run again — records already brought across are skipped rather than duplicated.', 'js-support-ticket')),
                        'link' => admin_url('admin.php?page=thirdpartyimport&jstlay=importresult'),
                        'as'   => esc_html(__('Import Report', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Read the checks that run afterwards. They compare what arrived against the source, and they are the reason to keep the old help desk a little longer or to retire it.', 'js-support-ticket')),
                        'link' => admin_url('admin.php?page=thirdpartyimport&jstlay=importresult'),
                        'as'   => esc_html(__('Import Report', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Do not delete the old help desk until those checks are clean. Once it is gone, anything missing cannot be fetched again.', 'js-support-ticket')),
                    ),
                ),
            ),

            'csv-import' => array(
                'group'   => esc_html(__('Migrations', 'js-support-ticket')),
                'title'   => esc_html(__('A CSV file will not import', 'js-support-ticket')),
                'symptom' => esc_html(__('The importer refuses the file, or reports rows it cannot take.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Download the template and compare the header row. Column order does not matter and extra columns are ignored, but subject, message and email must be present under those names.', 'js-support-ticket')),
                        'link' => admin_url('admin.php?page=export&jstlay=csvimport'),
                        'as'   => esc_html(__('Import from CSV', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('A status, priority or department that is not one of yours is refused rather than swapped for a default. Check the spelling against your own lists — a whole file importing into the wrong department is worse than a file that stops.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Dates must be YYYY-MM-DD or YYYY-MM-DD HH:MM:SS. A spreadsheet showing 14/03/2026 is usually holding something else underneath — format the column as text before saving.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Save as CSV in UTF-8. A file saved in the spreadsheet\'s own format is not a CSV, whatever the extension says.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Every row problem is listed with its line number. Fix those lines and import again, or import the good rows now and the rest afterwards — nothing is created twice.', 'js-support-ticket')),
                    ),
                ),
            ),

            'storage-engine' => array(
                'group'   => esc_html(__('Troubleshooting', 'js-support-ticket')),
                'title'   => esc_html(__('A table would not convert to InnoDB', 'js-support-ticket')),
                'symptom' => esc_html(__('The table storage card refuses a table, or a conversion run reports one it could not do.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Read the reason next to the table. It names the index and the width, and there are only two reasons a conversion is refused.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('An index wider than the limit is a database that is old enough to cap index keys at 767 bytes. Upgrading the database raises the limit to 3072 and the table converts unchanged.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('A FULLTEXT index needs a database new enough to carry one on InnoDB. Upgrading the database, or dropping an index nothing searches on, both clear it.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Tables already converted are left alone and can be put back on the engine they came from. The data is identical either way — only the engine changes.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                ),
            ),

            /* ---------------------------------------------------------- *
             * The Copilot
             * ---------------------------------------------------------- */
            'copilot' => array(
                'group'   => esc_html(__('Troubleshooting', 'js-support-ticket')),
                'title'   => esc_html(__('The AI Copilot is not answering', 'js-support-ticket')),
                'symptom' => esc_html(__('A Copilot button reports a rejected key, an unreachable provider, or nothing at all.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Press Test connection. It sends one short question and no ticket content, which separates a wrong key from everything else in one click.', 'js-support-ticket')),
                        'link' => $jsst_copilot,
                        'as'   => esc_html(__('AI Copilot', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('A rejected key is usually a key from a different provider, or one that has been revoked. Keys are never shown again after saving, so replace rather than inspect.', 'js-support-ticket')),
                        'link' => $jsst_copilot,
                        'as'   => esc_html(__('AI Copilot', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Could not reach the provider means outbound HTTPS from this server is blocked, which is common on shared hosting and has nothing to do with the key.', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Every run, including the ones that failed, is listed with its reason on the Copilot screen. Start there rather than from the button.', 'js-support-ticket')),
                        'link' => $jsst_copilot,
                        'as'   => esc_html(__('AI Copilot', 'js-support-ticket')),
                    ),
                ),
            ),

            /* ---------------------------------------------------------- *
             * Asking for help
             * ---------------------------------------------------------- */
            'reporting-a-problem' => array(
                'group'   => esc_html(__('Troubleshooting', 'js-support-ticket')),
                'title'   => esc_html(__('Reporting a problem so it can be answered once', 'js-support-ticket')),
                'symptom' => esc_html(__('Something is wrong that none of these pages covers, and you want to report it without three rounds of questions first.', 'js-support-ticket')),
                'steps'   => array(
                    array(
                        'do'   => esc_html(__('Download the debug file and attach it. It carries the versions, limits, scheduled work, queue state and recent errors — the questions that would otherwise be asked first. Passwords, API keys and licence keys are removed before it is written.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Quote the short identifier shown at the top of the System Status page. Every log line from that page load carries it, which is how a report is matched to what the site was actually doing.', 'js-support-ticket')),
                        'link' => $jsst_status,
                        'as'   => esc_html(__('System Status', 'js-support-ticket')),
                    ),
                    array(
                        'do'   => esc_html(__('Say what you did, what you expected, and what happened instead. An error message quoted exactly is worth more than a description of it.', 'js-support-ticket')),
                    ),
                ),
            ),
        );

        return apply_filters('jsst_docs_pages', $jsst_pages);
    }

    /** The pages arranged by their group, for the contents list. */
    public static function grouped() {
        $jsst_grouped = array();
        foreach (self::pages() as $jsst_id => $jsst_page) {
            $jsst_grouped[$jsst_page['group']][$jsst_id] = $jsst_page;
        }
        return $jsst_grouped;
    }

}
