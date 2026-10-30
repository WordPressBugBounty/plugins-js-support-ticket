SET SQL_MODE='ALLOW_INVALID_DATES';

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.9.8','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','298','default');

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) 
VALUES ('show_avatar', '2', 'default');

ALTER TABLE `#__js_ticket_tickets` ENGINE = InnoDB;
ALTER TABLE `#__js_ticket_replies` ENGINE = InnoDB;


-- Each schema change runs only when information_schema says it is missing, so this file is safe to apply twice.
SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_tickets` ADD FULLTEXT `subject` (`subject`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_tickets' AND INDEX_NAME = 'subject');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;
SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_tickets` ADD FULLTEXT `message` (`message`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_tickets' AND INDEX_NAME = 'message');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;
SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_replies` ADD FULLTEXT `message` (`message`)', 'DO 0') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_replies' AND INDEX_NAME = 'message');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;


SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_tickets` ADD `aireplymode` tinyint(4) DEFAULT 0 AFTER `productid`', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_tickets' AND COLUMN_NAME = 'aireplymode');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;
SET @jsst_sql = (SELECT IF(COUNT(*) = 0, 'ALTER TABLE `#__js_ticket_replies` ADD `aireplymode` tinyint(4) DEFAULT 0 AFTER `viewed_on`', 'DO 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '#__js_ticket_replies' AND COLUMN_NAME = 'aireplymode');
PREPARE jsst_stmt FROM @jsst_sql;
EXECUTE jsst_stmt;
DEALLOCATE PREPARE jsst_stmt;
