REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.6.6','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','266','default');

INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES
	('cplink_export_ticket_staff', '1', 'cplink', 'export');

INSERT INTO `#__js_ticket_slug` (`id`, `slug`, `defaultslug`, `filename`, `description`, `status`) VALUES
	(56, 'agent-export', 'export', 'export', 'slug for export page', 1);
