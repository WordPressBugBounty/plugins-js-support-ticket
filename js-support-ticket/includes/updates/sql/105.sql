INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('login_redirect', '2', 'default');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('count_on_myticket', '1', 'default');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','1.0.5','default') ON DUPLICATE KEY UPDATE configvalue = '1.0.5';
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','105','default') ON DUPLICATE KEY UPDATE configvalue = '105';
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productcode','jsticket','default') ON DUPLICATE KEY UPDATE configvalue = 'jsticket';