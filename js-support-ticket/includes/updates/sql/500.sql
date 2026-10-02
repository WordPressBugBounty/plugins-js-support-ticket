REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','5.0.0','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','500','default');

/* What 5.0 creates in core.

   One table, and it is not really 5.0's: js_ticket_jobs belongs to 4.0-PERF-02
   and was written as a lazy create that appeared in no SQL file at all, so a
   site that never opened the feature that needed it silently had no queue - a
   webhook delivery written 'pending' and never sent, and an automation rule
   waiting on a delay that never resumed. Install time is the one moment the
   database user is certain to be allowed to CREATE, which is why it is here as
   well as in the class's own guard.

   Everything else this release added ships in an addon and is created by that
   addon on activation. No collation is stated: the table takes the database's
   own default rather than a development site's. */

/* The background queue everything slow goes through. (Roadmap 5.0-API-04, 4.0-PERF-02) */
CREATE TABLE IF NOT EXISTS `#__js_ticket_jobs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `hook` varchar(100) NOT NULL,
  `args` longtext,
  `groupname` varchar(50) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `priority` tinyint NOT NULL DEFAULT '10',
  `scheduled` datetime DEFAULT NULL,
  `claim` varchar(40) NOT NULL DEFAULT '',
  `claimed` datetime DEFAULT NULL,
  `attempts` tinyint NOT NULL DEFAULT '0',
  `lasterror` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_due` (`status`,`scheduled`,`priority`),
  KEY `jsst_claim` (`claim`),
  KEY `jsst_group` (`groupname`,`status`)
);

/* The notification centre. (Roadmap 4.5-UX-02) */
CREATE TABLE IF NOT EXISTS `#__js_ticket_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staffid` int(11) NOT NULL,
  `wpuid` bigint(20) NOT NULL DEFAULT 0,
  `category` varchar(20) NOT NULL DEFAULT 'watched',
  `eventname` varchar(60) NOT NULL DEFAULT '',
  `ticketid` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text,
  `url` varchar(255) NOT NULL DEFAULT '',
  `actions` text,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `held` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_staff` (`staffid`),
  KEY `jsst_seen` (`seen`),
  KEY `jsst_held` (`held`)
);

/* Who a customer works for. Until 5.5 a company was the part of an e-mail
   address after the @; this is the record v4.5 said it was deferring.
   (Roadmap 5.5-COM-06) */
CREATE TABLE IF NOT EXISTS `#__js_ticket_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(190) NOT NULL DEFAULT '',
  `slug` varchar(190) NOT NULL DEFAULT '',
  `domains` text,
  `tier` varchar(100) NOT NULL DEFAULT '',
  `contractref` varchar(190) NOT NULL DEFAULT '',
  `contractstart` date DEFAULT NULL,
  `contractend` date DEFAULT NULL,
  `notes` text,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_slug` (`slug`),
  KEY `jsst_status` (`status`)
);

/* Who is named on one, and whether they may read their colleagues' tickets.
   The address is the identity and the account id is an optimisation: somebody
   who has only ever written in by e-mail has a ticket and an address and no
   account on this site at all. (Roadmap 5.5-COM-06) */
CREATE TABLE IF NOT EXISTS `#__js_ticket_company_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyid` int(11) NOT NULL DEFAULT 0,
  `email` varchar(190) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT 0,
  `personrole` varchar(20) NOT NULL DEFAULT 'contact',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_company` (`companyid`),
  KEY `jsst_email` (`email`),
  KEY `jsst_uid` (`uid`)
);

/* Marketing consent, which is what the newsletter checkbox on the registration
   form should always have produced. Core reads that box now rather than the
   MailChimp add-on. (Roadmap 5.5-SEC-02) */
CREATE TABLE IF NOT EXISTS `#__js_ticket_marketing_consent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0,
  `email` varchar(190) NOT NULL DEFAULT '',
  `name` varchar(190) NOT NULL DEFAULT '',
  `granted` tinyint(1) NOT NULL DEFAULT 1,
  `source` varchar(20) NOT NULL DEFAULT '',
  `statement` text,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_email` (`email`),
  KEY `jsst_created` (`created`)
);

/* The new-ticket mail's recipient list, when Automatic Assignment is running.
   Read by modules/email/model.php for releases with no row anywhere to read,
   so the setting existed in the code and on no site. INSERT IGNORE, and the
   value is the branch every site already takes, so this adds the choice
   without changing the answer. The install route seeds the same row in
   includes/activation.php - both are needed, for the reason written out at
   the 5.0 core schema block there. */
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`)
  VALUES ('department_email_on_ticket_create', '1', 'default', 'agentautoassign');


/* Core tables that used to be created only on demand, by each class's
   ensureSchema(). Same definitions as includes/activation.php. */

CREATE TABLE IF NOT EXISTS `#__js_ticket_ai_rules` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sourcetype` varchar(20) NOT NULL DEFAULT '',
  `sourceid` bigint(20) NOT NULL DEFAULT '0',
  `verdict` tinyint(1) NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  `setby` bigint(20) NOT NULL DEFAULT '0',
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jsst_doc` (`sourcetype`, `sourceid`),
  KEY `jsst_type` (`sourcetype`, `verdict`)
);

CREATE TABLE IF NOT EXISTS `#__js_ticket_kb_revisions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `articleid` bigint(20) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `content` longtext,
  `savedby` bigint(20) NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_kbrev_article` (`articleid`, `id`),
  KEY `jsst_kbrev_when` (`created`)
);

CREATE TABLE IF NOT EXISTS `#__js_ticket_ai_answers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticketid` bigint(20) NOT NULL DEFAULT '0',
  `replyid` bigint(20) NOT NULL DEFAULT '0',
  `state` varchar(20) NOT NULL DEFAULT 'held',
  `engine` varchar(50) NOT NULL DEFAULT '',
  `confidence` tinyint(4) NOT NULL DEFAULT '0',
  `coverage` tinyint(4) DEFAULT NULL,
  `overlap` tinyint(4) DEFAULT NULL,
  `body` longtext,
  `sources` text,
  `reason` varchar(255) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `decidedby` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `decided` datetime DEFAULT NULL,
  `agentreply` bigint(20) NOT NULL DEFAULT '0',
  `agentbody` longtext,
  `agreement` tinyint(4) DEFAULT NULL,
  `outcome` varchar(30) NOT NULL DEFAULT '',
  `pairedat` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_state` (`state`, `created`),
  KEY `jsst_ticket` (`ticketid`),
  KEY `jsst_reply` (`replyid`)
);

CREATE TABLE IF NOT EXISTS `#__js_ticket_ai_usage` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `engine` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(100) NOT NULL DEFAULT '',
  `lane` varchar(20) NOT NULL DEFAULT '',
  `funded` varchar(10) NOT NULL DEFAULT 'byok',
  `feature` varchar(40) NOT NULL DEFAULT '',
  `ticketid` bigint(20) NOT NULL DEFAULT '0',
  `userid` bigint(20) NOT NULL DEFAULT '0',
  `intokens` int(11) NOT NULL DEFAULT '0',
  `outtokens` int(11) NOT NULL DEFAULT '0',
  `tokensknown` tinyint(1) NOT NULL DEFAULT '1',
  `cost` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `priced` tinyint(1) NOT NULL DEFAULT '1',
  `ms` int(11) NOT NULL DEFAULT '0',
  `ok` tinyint(1) NOT NULL DEFAULT '1',
  `error` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `jsst_when` (`created`),
  KEY `jsst_ticket` (`ticketid`),
  KEY `jsst_funded` (`funded`, `created`)
);

CREATE TABLE IF NOT EXISTS `#__js_ticket_ai_gaps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `kind` varchar(20) NOT NULL DEFAULT 'search',
  `question` varchar(500) NOT NULL DEFAULT '',
  `fingerprint` char(32) NOT NULL DEFAULT '',
  `ticketid` bigint(20) NOT NULL DEFAULT '0',
  `coverage` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jsst_when` (`created`),
  KEY `jsst_kind` (`kind`, `created`),
  KEY `jsst_print` (`fingerprint`)
);

CREATE TABLE IF NOT EXISTS `#__js_ticket_ai_triage` (
  `ticketid` bigint(20) NOT NULL,
  `score` tinyint(4) DEFAULT NULL,
  `mood` varchar(20) NOT NULL DEFAULT '',
  `atrisk` tinyint(1) NOT NULL DEFAULT '0',
  `evidence` varchar(500) NOT NULL DEFAULT '',
  `department` varchar(150) NOT NULL DEFAULT '',
  `urgency` varchar(20) NOT NULL DEFAULT '',
  `reason` varchar(500) NOT NULL DEFAULT '',
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`ticketid`),
  KEY `jsst_triage_score` (`score`),
  KEY `jsst_triage_risk` (`atrisk`, `updated`)
);


/* Columns and indexes added to tables that already exist. This file also runs
   on a fresh install, straight after activation has created them, and MySQL
   has no ADD ... IF NOT EXISTS, so each one is added only when
   information_schema says it is missing. Nothing here can fail on a site that
   already has it. The queue indexes on js_ticket_tickets are left to
   JSSTqueue::ensureSchema(): that is the largest table on a site, and an
   index build there must not hold up the upgrade request. */

/* Canned responses: folders, teams, approval, languages, customer suggestions. */

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD `folder` varchar(80) NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND COLUMN_NAME = 'folder');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD `teamid` bigint(20) NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND COLUMN_NAME = 'teamid');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD `approved` tinyint(1) NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND COLUMN_NAME = 'approved');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD `approvedby` bigint(20) NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND COLUMN_NAME = 'approvedby');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD `language` varchar(8) NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND COLUMN_NAME = 'language');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD `translationof` bigint(20) NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND COLUMN_NAME = 'translationof');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD `customersuggest` tinyint(1) NOT NULL DEFAULT ''0''', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND COLUMN_NAME = 'customersuggest');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD INDEX `jsst_department` (`departmentid`, `status`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND INDEX_NAME = 'jsst_department');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD FULLTEXT `jsst_ir_ft` (`title`, `answer`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND INDEX_NAME = 'jsst_ir_ft');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD FULLTEXT `ft_title` (`title`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND INDEX_NAME = 'ft_title');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_department_message_premade` ADD FULLTEXT `ft_answer` (`answer`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_department_message_premade' AND INDEX_NAME = 'ft_answer');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

/* Help topics: sub-topics, a default topic, an owning agent. (Roadmap 4.0-CORE-20) */

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_help_topics` ADD `parentid` int(11) DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_help_topics' AND COLUMN_NAME = 'parentid');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_help_topics` ADD `staffid` int(11) DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_help_topics' AND COLUMN_NAME = 'staffid');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_help_topics` ADD `isdefault` tinyint(1) NOT NULL DEFAULT ''0''', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_help_topics' AND COLUMN_NAME = 'isdefault');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_help_topics` ADD INDEX `jsst_parent` (`parentid`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_help_topics' AND INDEX_NAME = 'jsst_parent');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

/* Saved views: shared or private. (Roadmap 4.5-UX-01) */

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_saved_views` ADD `visibility` tinyint(1) NOT NULL DEFAULT 0', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_saved_views' AND COLUMN_NAME = 'visibility');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_saved_views` ADD INDEX `jsst_visibility` (`visibility`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_saved_views' AND INDEX_NAME = 'jsst_visibility');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;

/* The four settings core used to spell after Instant Resolve, renamed to
   aiagent_* exactly as JSSTaisources::migrateLegacyConfig() does: the old value
   moves to the new name unless the new row is already there, and the old row
   goes. addon stays NULL - these are core's. */

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`)
  SELECT 'aiagent_enable', `configvalue`, 'aiagent', NULL FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_enable';

DELETE FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_enable';

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`)
  SELECT 'aiagent_min_chars', `configvalue`, 'aiagent', NULL FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_min_chars';

DELETE FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_min_chars';

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`)
  SELECT 'aiagent_max_results', `configvalue`, 'aiagent', NULL FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_max_results';

DELETE FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_max_results';

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`)
  SELECT 'aiagent_analytics', `configvalue`, 'aiagent', NULL FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_analytics';

DELETE FROM `#__js_ticket_config` WHERE `configname` = 'instantresolve_analytics';
