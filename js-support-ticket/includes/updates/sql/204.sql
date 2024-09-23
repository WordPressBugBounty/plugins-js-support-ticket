INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES('read_utf_ticket_via_email','1','ticketviaemail');

ALTER TABLE `#__js_ticket_tickets` ADD `hash` varchar(200) COLLATE 'utf8_general_ci' NULL AFTER `params`;

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.0.4','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','204','default');
