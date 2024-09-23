INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES
	('envato_api_key', '', 'envatovalidation', 'envatovalidation'),
	('envato_license_required', '1', 'envatovalidation', 'envatovalidation'),
	('envato_product_ids', '', 'envatovalidation', 'envatovalidation'),
	('mailchimp_api_key', '', 'mailchimp', 'mailchimp'),
	('mailchimp_list_id', '', 'mailchimp', 'mailchimp'),
	('mailchimp_double_optin', '1', 'mailchimp', 'mailchimp'),
	('tickets_ordering', '1', 'default', NULL),
	('cplink_helptopic_agent', '1', 'cplink', 'helptopic'),
	('cplink_cannedresponses_agent', '1', 'cplink', 'cannedresponses'),
	('verify_license_on_ticket_creation', '1', 'default', 'easydigitaldownloads');


UPDATE `#__js_ticket_config` SET `addon`='' WHERE `configname` IN ('ticket_response_to_staff_admin','ticket_response_to_staff_user');

CREATE TABLE IF NOT EXISTS `#__js_ticket_erasedatarequests` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `message` text NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


SET SQL_MODE='ALLOW_INVALID_DATES';

ALTER TABLE `#__js_ticket_tickets`
	ADD `eddorderid` INT NULL AFTER `wcproductid`,
	ADD `eddproductid` INT NULL AFTER `eddorderid`,
	ADD `eddlicensekey` VARCHAR(250) NULL AFTER `eddproductid`,
	ADD `envatodata` text NULL AFTER `eddlicensekey`,
	ADD `paidsupportitemid` bigint(20) NULL AFTER `envatodata`;


INSERT INTO `#__js_ticket_fieldsordering` (`id`, `field`, `fieldtitle`, `ordering`, `section`, `fieldfor`, `published`, `sys`, `cannotunpublish`, `required`, `size`, `maxlength`, `cols`, `rows`, `isuserfield`, `userfieldtype`, `depandant_field`, `showonlisting`, `cannotshowonlisting`, `search_user`, `cannotsearch`, `isvisitorpublished`, `search_visitor`, `userfieldparams`) VALUES
	(NULL, 'envatopurchasecode',	'Envato Purchase Code',	18,	'10',	1,	1,	0,	0,	1,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	NULL,	1,	1,	NULL,	NULL),
	(NULL, 'eddorderid', 'Order ID', 18, '10', 1, 1, 0, 0, 0, NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	0,	NULL,	0, 1,	NULL,	NULL),
	(NULL, 'eddproductid', 'Product', 19, '10', 1, 1, 0, 0, 0,  NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	0, NULL,	0, 1,	NULL,	NULL),
	(NULL, 'eddlicensekey', 'License Key', 20, '10', 1, 1, 0, 0, 0,  NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	0, NULL,	1, 1,	NULL,	NULL);


UPDATE `#__js_ticket_fieldsordering` SET `fieldtitle` = 'Canned Response' WHERE `field` = 'premade';

INSERT INTO `#__js_ticket_emailtemplates` (`id`, `templatefor`, `title`, `subject`, `body`, `created`, `status`) VALUES (28,'delete-user-data','','{SITETITLE}: Delete User Data','<div style=\"background-color: #f7f7f7; margin: 0; padding: 70px 0; width: 100%;\">\n<div style=\"border: 3px dotted #ebecec; width: 600px; display: block; margin: 0 auto; background: #fff;\">\n<div style=\"padding: 15px 20px; background: #3e4095; color: #fff; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #4b4b4d;\">JS Help Desk</div>\n<div style=\"padding: 30px; text-align: center; font-weight: bold; background: #576cf1; color: #fff; text-transform: capitalize; font-size: 22px;\">{SITETITLE}: Data Delete request</div>\n<div style=\"padding: 40px 20px 20px;\">\n<div style=\"padding-bottom: 20px; border-bottom: 1px solid #ebecec;\">\n<div style=\"font-weight: bold; font-size: 18px; margin-bottom: 15px; color: #4b4b4d;\">Dear {USERNAME},</div>\n<div style=\"color: #727376; line-height: 2;\">Your data delete request has been received.</div>\n</div>\n<div style=\"background: #fef2ef; padding: 15px; margin-bottom: 20px; border: 1px solid #eba7a8;\">\n<div style=\"font-weight: bold; font-size: 14px; margin-bottom: 5px; color: #983133; text-transform: uppercase;\">Do not reply TO this E-Mail</div>\n<div style=\"color: #727376; line-height: 2;\">This is an automated e-mail message sent from our support system. Do not reply to this e-mail as we cannot receive your reply!</div>\n</div>\n<div style=\"color: #727376; line-height: 2;\">This email was sent from <span style=\"color: #3e4095; display: inline-block; text-decoration: underline;\">JS Help Desk System</span> to <span style=\"color: #606062; display: inline-block; text-decoration: underline;\">{EMAIL}</span></div>\n</div>\n<div style=\"background: #4b4b4d; padding: 20px; color: #fff; text-align: center; border-bottom: 5px solid #576cf1;\">© 2004-2019 All rights reserved - JS Help Desk WordPress Plugin</div>\n</div>\n</div>',NULL,0);


REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.1.7','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','217','default');
