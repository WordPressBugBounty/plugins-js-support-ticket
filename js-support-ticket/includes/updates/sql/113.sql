UPDATE `#__js_ticket_fieldsordering` SET fieldfor = 1 WHERE field = 'phone';
ALTER TABLE `#__js_ticket_tickets` ADD `attachmentdir` VARCHAR( 50 );
UPDATE `#__js_ticket_tickets` SET attachmentdir = CONCAT( 'ticket_', id );
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','1.1.3','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','113','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productcode','jsticket','default');
