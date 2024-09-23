REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.4.9','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','249','default');

INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES('support_custom_img', '0', 'default');

ALTER TABLE `#__js_ticket_fieldsordering` ADD `visible_field` varchar(250) DEFAULT NULL AFTER `depandant_field`;
ALTER TABLE `#__js_ticket_fieldsordering` ADD `visibleparams` longtext DEFAULT NULL AFTER `userfieldparams`;
