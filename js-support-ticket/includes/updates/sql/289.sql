REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.8.9','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','289','default');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) 
VALUES('show_read_receipt_to_user_on_reply','2','ticket'), 
('show_read_receipt_to_agent_on_reply','2','ticket'), 
('show_read_receipt_to_admin_on_reply','2','ticket');

ALTER TABLE `#__js_ticket_replies` ADD `viewed_by` int(11) DEFAULT NULL;
ALTER TABLE `#__js_ticket_replies` ADD `viewed_on` datetime DEFAULT NULL;


