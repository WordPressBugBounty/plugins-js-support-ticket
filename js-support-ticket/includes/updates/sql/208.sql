ALTER TABLE `#__js_ticket_tickets` 
	ADD `mergestatus` TINYINT(4) NOT NULL DEFAULT '0' AFTER `feedbackemail`, 
	ADD `mergewith` INT(11) DEFAULT NULL AFTER `mergestatus`, 
	ADD `mergenote` TEXT DEFAULT NULL AFTER `mergewith`, 
	ADD `mergedate` datetime DEFAULT NULL AFTER `mergenote`,
	ADD `multimergeparams` TEXT DEFAULT NULL AFTER `mergedate`,
	ADD `mergeuid` INT(11) DEFAULT NULL AFTER `multimergeparams`;

ALTER TABLE `#__js_ticket_replies` ADD `mergemessage` TINYINT(1) NOT NULL DEFAULT '0' AFTER `ticketviaemail`;


REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.0.8','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','208','default');