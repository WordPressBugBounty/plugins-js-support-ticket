UPDATE `#__js_ticket_config` SET `configfor` = 'email', `addon` = '' WHERE `#__js_ticket_config`.`configname` = 'new_ticket_mail_to_admin';
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('redirect_after_checkout', '', 'default', 'paidsupport');

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.1.9','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','219','default');
