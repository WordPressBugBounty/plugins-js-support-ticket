REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.7.2','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','272','default');
UPDATE `#__js_ticket_fieldsordering` SET fieldtitle = 'EDD Order ID' WHERE field = 'eddorderid';
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('tplink_login_logout_staff', '1', 'tplink');
