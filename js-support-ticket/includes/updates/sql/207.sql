ALTER TABLE `#__js_ticket_priorities` ADD `overduetypeid` INT(5) DEFAULT NULL AFTER `priorityurgency`;
ALTER TABLE `#__js_ticket_priorities` ADD `overdueinterval` INT(5) DEFAULT NULL AFTER `overduetypeid`;

ALTER TABLE `#__js_ticket_tickets` ADD `ticketviaemail_id` INT(11) DEFAULT NULL AFTER `ticketviaemail`;

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.0.7','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','207','default');

UPDATE `#__js_ticket_priorities` set overduetypeid = (SELECT configvalue FROM `#__js_ticket_config` WHERE configname = 'ticket_overdue_type' );
UPDATE `#__js_ticket_priorities` set overdueinterval = (SELECT configvalue FROM `#__js_ticket_config` WHERE configname = 'ticket_overdue' );
