INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('private_credentials_secretkey', '', 'privatecredentials', 'privatecredentials');

SET SQL_MODE='ALLOW_INVALID_DATES';
ALTER TABLE `#__js_ticket_tickets` ADD `wcorderid` bigint NULL AFTER `notificationid`, ADD `wcitemid` bigint NULL AFTER `wcorderid`;

INSERT INTO `#__js_ticket_fieldsordering` (`id`, `field`, `fieldtitle`, `ordering`, `section`, `fieldfor`, `published`, `sys`, `cannotunpublish`, `required`, `size`, `maxlength`, `cols`, `rows`, `isuserfield`, `userfieldtype`, `depandant_field`, `showonlisting`, `cannotshowonlisting`, `search_user`, `cannotsearch`, `isvisitorpublished`, `search_visitor`, `userfieldparams`) VALUES
(NULL, 'wcorderid', 'Order ID', 16, '10', 1, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 1, 0, 1, 1, NULL),
(NULL, 'wcproductid', 'Product', 17, '10', 1, 1, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 1, 0, NULL);

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.1.5','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','215','default');
