INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES 
	('prefix_ticketid', '', 'customticketid', NULL), 
	('suffix_ticketid', '', 'customticketid', NULL),
	('padding_zeros_ticketid', '', 'customticketid', NULL);
	
ALTER TABLE `#__js_ticket_tickets` ADD `customticketno` INT NOT NULL DEFAULT '1' AFTER `paidsupportitemid`; 
ALTER TABLE `#__js_ticket_tickets` CHANGE `ticketid` `ticketid` VARCHAR(35) DEFAULT NULL; 
	
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.6.3','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','263','default');
