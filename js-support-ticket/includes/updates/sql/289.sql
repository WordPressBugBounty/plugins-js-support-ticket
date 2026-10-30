REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.8.9','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','289','default');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) 
VALUES('show_read_receipt_to_user_on_reply','2','ticket'), 
('show_read_receipt_to_agent_on_reply','2','ticket'), 
('show_read_receipt_to_admin_on_reply','2','ticket');

-- Each schema change runs only when information_schema says it is missing, so this file is safe to apply twice.
SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_replies` ADD `viewed_by` int(11) DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_replies' AND COLUMN_NAME = 'viewed_by');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;
SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_replies` ADD `viewed_on` datetime DEFAULT NULL', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_replies' AND COLUMN_NAME = 'viewed_on');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;
