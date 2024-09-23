ALTER TABLE `#__js_ticket_departments` ADD COLUMN `sendmail` tinyint NOT NULL DEFAULT '0';

ALTER TABLE `#__js_ticket_fieldsordering` 
ADD `isuserfield` TINYINT AFTER `required` ,
ADD `depandant_field` VARCHAR( 250 ) AFTER `isuserfield` ,
ADD `showonlisting` TINYINT AFTER `depandant_field` ,
ADD `search_user` TINYINT AFTER `showonlisting` ,
ADD `userfieldparams` LONGTEXT AFTER `search_user`,
ADD `userfieldtype` VARCHAR( 250 ) AFTER `isuserfield`,
ADD `size` VARCHAR( 200 ) AFTER `required`,
ADD `maxlength` VARCHAR( 200 ) AFTER `size`,
ADD `cols` VARCHAR( 200 ) AFTER `maxlength` ,
ADD `rows` VARCHAR( 200 ) AFTER `cols`,
ADD `isvisitorpublished` TINYINT AFTER `search_user`,
ADD `search_visitor` TINYINT AFTER `isvisitorpublished`,
ADD `cannotsearch` TINYINT AFTER `search_user`,
ADD `cannotshowonlisting` TINYINT NULL AFTER `showonlisting` ;

ALTER TABLE `#__js_ticket_tickets` ADD `params` LONGTEXT NULL AFTER `attachmentdir`;

CREATE TABLE `#__js_ticket_userfields_bak` LIKE `#__js_ticket_userfields`;
INSERT `#__js_ticket_userfields_bak` SELECT * FROM `#__js_ticket_userfields`;

CREATE TABLE `#__js_ticket_userfield_data_bak` LIKE `#__js_ticket_userfield_data`;
INSERT `#__js_ticket_userfield_data_bak` SELECT * FROM `#__js_ticket_userfield_data`;

CREATE TABLE `#__js_ticket_userfieldvalues_bak` LIKE `#__js_ticket_userfieldvalues`;
INSERT `#__js_ticket_userfieldvalues_bak` SELECT * FROM `#__js_ticket_userfieldvalues`;

INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('last_step_updater','','default');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('cplink_login_logout_user','1','cplink');
INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('cplink_login_logout_staff','1','cplink');

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','1.1.5','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','115','default');
