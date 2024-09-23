REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.3.1','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','231','default');

ALTER TABLE `#__js_ticket_jshdsessiondata` CHANGE `sessionfor` `sessionfor` VARCHAR(125) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
