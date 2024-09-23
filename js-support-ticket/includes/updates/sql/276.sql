REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.7.6','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','276','default');

ALTER TABLE `#__js_ticket_tickets`  ADD `closedby` int(11) DEFAULT NULL  AFTER `closed`;
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`)
 VALUES ('show_multiform_popup', '1', 'ticket', 'multiform'),
 ('show_closedby_on_admin_tickets', '1', 'ticket', NULL),
 ('show_closedby_on_agent_tickets', '1', 'ticket', 'agent'),
 ('show_closedby_on_user_tickets', '1', 'ticket', NULL),
 ('show_assignto_on_admin_tickets', '1', 'ticket', 'agent'),
 ('show_assignto_on_agent_tickets', '1', 'ticket', 'agent'),
 ('show_assignto_on_user_tickets', '1', 'ticket', 'agent');
