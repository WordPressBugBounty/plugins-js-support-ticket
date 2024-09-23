INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES('anonymous_name_on_ticket_reply','2','ticket');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES('show_email_on_ticket_reply','1','ticket');

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.4.2','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','242','default');

ALTER TABLE `#__js_ticket_fieldsordering` ADD `multiformid` INT DEFAULT 1 AFTER `search_visitor`;
ALTER TABLE `#__js_ticket_tickets` ADD `multiformid` INT DEFAULT 1  AFTER `helptopicid`;
