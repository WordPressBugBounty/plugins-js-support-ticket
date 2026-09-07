REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','4.0.0','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','400','default');

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('data_retention_on_uninstall', 'preserve', 'default', NULL);

/* Human verification and submission rate limits. (Roadmap 4.0-SEC-01)
   INSERT IGNORE, never REPLACE: configname is the primary key, so an existing
   site keeps whatever it has already configured. captcha_provider is left empty
   on upgrade on purpose — JSSTverification::provider() then derives the provider
   from the pre-4.0 captcha_selection / recaptcha_version settings, so a site
   using reCAPTCHA keeps using reCAPTCHA until an administrator changes it. */
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_provider', '', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_turnstile_sitekey', '', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_turnstile_secret', '', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_hcaptcha_sitekey', '', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_hcaptcha_secret', '', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_recaptcha3_sitekey', '', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_recaptcha3_secret', '', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_score_threshold', '0.5', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_min_submit_seconds', '3', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_pow_bits', '12', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('captcha_fail_open', '1', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('submission_rate_limit', '1', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('submission_rate_limit_max', '5', 'default', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('submission_rate_limit_window', '600', 'default', NULL);

/* Ticket history is part of the free core. (Roadmap 4.0-CORE-01)
   The table is shared with the stand-alone Ticket History add-on, so an existing
   log is kept exactly as it is; only a site that never had the add-on gets a new
   table here. The 4.0 columns and indexes are added by
   JSSTtickethistoryModel::ensureSchema(), which checks each one first and so
   cannot fail on a table that already has them. */
CREATE TABLE IF NOT EXISTS `#__js_ticket_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `referenceid` int(11) DEFAULT NULL,
  `level` int(2) DEFAULT NULL,
  `eventfor` int(2) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `eventtype` varchar(255) DEFAULT NULL,
  `message` text,
  `messagetype` varchar(255) DEFAULT NULL,
  `datetime` timestamp NULL DEFAULT NULL,
  `source` varchar(32) DEFAULT NULL,
  `fieldname` varchar(191) DEFAULT NULL,
  `oldvalue` text,
  `newvalue` text,
  PRIMARY KEY (`id`),
  KEY `jsst_reference` (`referenceid`, `eventfor`),
  KEY `jsst_datetime` (`datetime`)
);

/* Internal notes are part of the free core. (Roadmap 4.0-CORE-02)
   Shared with the stand-alone Private Note add-on in exactly the same way as the
   activity log above: an existing table is left untouched, and anything missing
   from an older layout is added by JSSTnoteModel::ensureSchema(). */
CREATE TABLE IF NOT EXISTS `#__js_ticket_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticketid` int(11) DEFAULT NULL,
  `staffid` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `note` text,
  `status` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `filename` varchar(300) NULL,
  `filesize` varchar(15) NULL,
  `filedeleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `jsst_ticketid` (`ticketid`)
);

/* Canned responses are part of the free core. (Roadmap 4.0-CORE-03)
   Same shared-table rule as above. The full-text indexes Instant Answers needs
   are added by JSSTcannedresponsesModel::ensureSchema(), which checks for each
   one first and suppresses errors on storage engines that cannot build them. */
CREATE TABLE IF NOT EXISTS `#__js_ticket_department_message_premade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `departmentid` varchar(45) DEFAULT NULL,
  `title` varchar(125) DEFAULT NULL,
  `answer` text,
  `created` datetime NOT NULL,
  `updated` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

/* Help topics are part of the free core. (Roadmap 4.0-CORE-06)
   The add-on's layout exactly, shared with it in the same way as the tables
   above: an existing site's topics are left alone, and only a site that never had
   the add-on gets a new table. The renaming, nesting, ownership and default
   columns are 4.0-CORE-20, not this row. */
CREATE TABLE IF NOT EXISTS `#__js_ticket_help_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isactive` tinyint(1) DEFAULT NULL,
  `autoresponce` tinyint(1) DEFAULT NULL,
  `departmentid` int(11) DEFAULT NULL,
  `priorityid` int(11) DEFAULT NULL,
  `topic` varchar(32) DEFAULT NULL,
  `ordering` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_department` (`departmentid`, `status`)
);


/* Blocked senders are part of the free core. (Roadmap 4.0-CORE-12)
   An entry is one address, or "@example.com" for a whole domain. */
CREATE TABLE IF NOT EXISTS `#__js_ticket_email_banlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `submitter` varchar(126) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_email` (`email`)
);

/* The log of what the block list actually stopped. Core from 4.0 as well: a
   block nobody can audit is a block an administrator cannot trust, and the
   screen went away with the add-on. Same table and columns as the add-on, so an
   existing log is picked up as it stands. (Roadmap 4.0-CORE-12) */
CREATE TABLE IF NOT EXISTS `#__js_ticket_banlist_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loggeremail` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `log` text,
  `logger` varchar(255) DEFAULT NULL,
  `ipaddress` varchar(64) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_created` (`created`)
);

/* Retention exclusions. (Roadmap 4.0-CORE-13)
   Empty by default, so upgrading changes nothing about what a run would remove. */
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('autocleanup_exclude_departments', '', 'autocleanup', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('autocleanup_exclude_priorities', '', 'autocleanup', NULL);

/* Ticket tags. (Roadmap 4.0-CORE-17)
   New in 4.0, so there is no add-on layout to match. The slug is unique, which is
   what makes "Billing", "billing" and " BILLING " one tag instead of three. */
CREATE TABLE IF NOT EXISTS `#__js_ticket_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `created` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `jsst_slug` (`slug`)
);

/* Which tickets carry which tags. The pair is unique, so tagging a ticket twice
   with the same tag is a no-op rather than a duplicate row. */
CREATE TABLE IF NOT EXISTS `#__js_ticket_ticket_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticketid` int(11) NOT NULL,
  `tagid` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jsst_ticket_tag` (`ticketid`, `tagid`),
  KEY `jsst_tagid` (`tagid`)
);

/* Topics upgrade. (Roadmap 4.0-CORE-20)
   The parentid, staffid and isdefault columns are added by
   JSSThelptopicModel::ensureSchema(), which checks for each one first — the
   table may belong to the legacy add-on, which has no such columns and must not
   be altered underneath it.

   What is here is the label on the ticket form, and only when the site has not
   changed it. That title is a site's own text: an administrator may already have
   renamed the field to "Category", "Product area" or their own language, and an
   unconditional UPDATE would throw that away. Renaming only the untouched
   default leaves every customised site exactly as it is. */
UPDATE `#__js_ticket_fieldsordering` SET `fieldtitle` = 'Topic'
  WHERE `field` = 'helptopic' AND `fieldtitle` = 'Help Topic';

/* Saved queue views. (Roadmap 4.0-CORE-18)
   One row per named search, owned by the WordPress user who saved it. The
   filters are stored as JSON rather than a column each so that adding a filter
   to the queue later does not need a migration; JSSTqueue::viewKeys() decides
   which keys are read back out, so a row can never smuggle a key the query
   builder does not expect. The queue indexes this release adds to
   js_ticket_tickets are not here — that table predates every version that ships
   this file and its layout differs between installs, so JSSTqueue::ensureSchema()
   checks for each index before adding it. */
CREATE TABLE IF NOT EXISTS `#__js_ticket_saved_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `filters` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_uid` (`uid`)
);
