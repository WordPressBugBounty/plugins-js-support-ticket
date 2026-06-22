REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','3.1.3','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','313','default');

INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('autocleanup_attachment_interval', '0', 'autocleanup', 'autocleanup');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('autocleanup_ticket_interval', '0', 'autocleanup', 'autocleanup');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('autocleanup_cron_frequency', 'daily', 'autocleanup', 'autocleanup');
