SET SQL_MODE='ALLOW_INVALID_DATES';

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','3.0.0','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','300','default');


ALTER TABLE `#__js_ticket_emailtemplates` 
ADD `multiformid` tinyint(4) DEFAULT NULL AFTER `status`;


INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestdownloads_staff', '2', 'cplink', 'download');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestannouncements_staff', '2', 'cplink', 'announcement');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestkb_staff', '2', 'cplink', 'knowledgebase');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_latestfaqs_staff', '2', 'cplink', 'faq');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('cplink_ticketstats_user', '1', 'cplink', NULL);