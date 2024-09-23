ALTER TABLE `#__js_ticket_departments` ADD COLUMN `isdefault` tinyint NOT NULL DEFAULT '0' AFTER `status`;
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('system_slug','jssupportticket','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','1.0.8','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','108','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productcode','jsticket','default');
