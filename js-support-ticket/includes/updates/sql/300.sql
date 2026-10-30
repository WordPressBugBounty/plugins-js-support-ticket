SET SQL_MODE='ALLOW_INVALID_DATES';

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','3.0.0','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','300','default');


-- Each schema change runs only when information_schema says it is missing, so this file is safe to apply twice.
SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_emailtemplates` ADD `multiformid` tinyint(4) DEFAULT NULL AFTER `status`', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_emailtemplates' AND COLUMN_NAME = 'multiformid');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;


INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestdownloads_staff', '2', 'cplink', 'download');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestannouncements_staff', '2', 'cplink', 'announcement');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestkb_staff', '2', 'cplink', 'knowledgebase');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestfaqs_staff', '2', 'cplink', 'faq');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_ticketstats_user', '1', 'cplink', NULL);