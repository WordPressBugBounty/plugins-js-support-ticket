
SET SQL_MODE='ALLOW_INVALID_DATES';
ALTER TABLE `#__js_ticket_tickets` ADD `wcproductid` bigint NULL AFTER `wcorderid`;

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.1.6','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','216','default');
