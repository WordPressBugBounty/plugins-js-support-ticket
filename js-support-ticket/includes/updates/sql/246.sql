REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.4.6','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','246','default');
UPDATE `#__js_ticket_config` SET `addon` = '' WHERE `configname` = 'captcha_on_registration';
UPDATE `#__js_ticket_config` SET `configvalue` = '1' WHERE `configname` = 'captcha_on_registration' AND `configvalue` = '';
