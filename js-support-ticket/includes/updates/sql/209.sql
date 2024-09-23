
ALTER TABLE `#__js_ticket_email` ADD `smtphosttype` INT DEFAULT NULL AFTER `smtpactive`;
ALTER TABLE `#__js_ticket_email` ADD `smtpemailauth` TINYINT DEFAULT NULL AFTER `status`;

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.0.9','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','209','default');
