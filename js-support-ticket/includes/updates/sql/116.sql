CREATE TABLE IF NOT EXISTS `#__js_ticket_fieldsordering` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	 `field` varchar(50) NOT NULL,
	 `fieldtitle` varchar(50) DEFAULT NULL,
	 `ordering` int(11) DEFAULT NULL,
	 `section` varchar(20) DEFAULT NULL,
	 `fieldfor` tinyint(2) DEFAULT NULL,
	 `published` tinyint(1) DEFAULT NULL,
	 `sys` tinyint(1) NOT NULL,
	 `cannotunpublish` tinyint(1) NOT NULL,
	 `required` tinyint(1) NOT NULL DEFAULT '0',
	 `isuserfield` TINYINT,
	 `depandant_field` VARCHAR( 250 ),
	 `showonlisting` TINYINT,`search_user` TINYINT,
	 `userfieldparams` LONGTEXT,
	 `userfieldtype` VARCHAR( 250 ),
	 `size` VARCHAR( 200 ),
	 `maxlength` VARCHAR( 200 ),
	 `cols` VARCHAR( 200 ),
	 `rows` VARCHAR( 200 ),
	 `isvisitorpublished` TINYINT,
	 `search_visitor` TINYINT,
	 `cannotsearch` TINYINT,
	 `cannotshowonlisting` TINYINT NULL, 
	 PRIMARY KEY (`id`),
	 KEY `fieldordering_filedfor` (`fieldfor`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15;

INSERT IGNORE INTO `#__js_ticket_fieldsordering` 
(`id`, `field`, `fieldtitle`, `ordering`, `section`, `fieldfor`, `published`, `sys`, `cannotunpublish`, `required`,`cannotsearch`,`cannotshowonlisting`,`isvisitorpublished`) VALUES 
(1, 'email', 'Email Address', 2, '10', 1, 1, 0, 1, 1, 1,1,1),
(2, 'fullname', 'Full Name', 3, '10', 1, 1, 0, 1, 1, 1,1,1),
(3, 'phone', 'Phone', 4, '10', 1, 1, 0, 0, 0, 1,1,1),
(4, 'department', 'Department', 5, '10', 1, 1, 0, 0, 0, 1,1,1),
(5, 'helptopic', 'Help Topic', 6, '10', 1, 1, 0, 0, 0, 1,1,1),
(6, 'priority', 'Priority', 7, '10', 1, 1, 0, 1, 1, 1,1,1),
(7, 'subject', 'Subject', 8, '10', 1, 1, 0, 1, 1, 1,1,1),
(8, 'premade', 'Premade', 9, '10', 1, 1, 0, 0, 0, 1,1,1),
(9, 'issuesummary', 'Issue Summary', 10, '10', 1, 1, 0, 1, 1, 1,1,1),
(10, 'attachments', 'Attachments', 11, '10', 1, 1, 0, 0, 0, 1,1,1),
(11, 'internalnotetitle', 'Internal Note Title', 12, '10', 1, 1, 0, 0, 0, 1,1,1),
(12, 'assignto', 'Assign To', 13, '10', 1, 1, 0, 0, 0, 1,1,1),
(13, 'duedate', 'Due Date', 14, '10', 1, 1, 0, 0, 0, 1,1,1),
(14, 'status', 'Status', 15, '10', 1, 1, 0, 0, 0, 1,1,1),
(15, 'users', 'Users', 1, '10', 1, 1, 0, 0, 0, 1,1,1);

UPDATE `#__js_ticket_fieldsordering` SET `isvisitorpublished` =  `published`;

UPDATE `#__js_ticket_fieldsordering` SET `cannotsearch` =  `1` WHERE 'id' < 16;
UPDATE `#__js_ticket_fieldsordering` SET `cannotshowonlisting` =  `1` WHERE 'id' < 16;;

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','1.1.6','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','116','default');
