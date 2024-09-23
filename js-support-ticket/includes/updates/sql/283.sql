REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.8.3','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','283','default');

UPDATE `#__js_ticket_config` SET `addon` = 'actions' WHERE `configname` = 'print_ticket_user';
UPDATE `#__js_ticket_config` SET `addon` = 'agent' WHERE `configname` = 'ticket_reassign_admin';
UPDATE `#__js_ticket_config` SET `addon` = 'agent' WHERE `configname` = 'ticket_reassign_user';

UPDATE `#__js_ticket_config` SET `addon` = 'actions' WHERE `configname` = 'ticket_department_transfer_admin';
UPDATE `#__js_ticket_config` SET `addon` = 'actions' WHERE `configname` = 'ticket_department_transfer_staff';
UPDATE `#__js_ticket_config` SET `addon` = 'actions' WHERE `configname` = 'ticket_department_transfer_user';

UPDATE `#__js_ticket_config` SET `addon` = 'emailpiping' WHERE `configname` = 'ticket_reply_closed_ticket_user';