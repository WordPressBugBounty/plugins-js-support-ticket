INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('0d607e93d5af0655351743b41ed67944', '', 'firebase'), ('apiKey_firebase', '', 'firebase'), ('authDomain_firebase', '', 'firebase'), ('databaseURL_firebase', '', 'firebase'), ('projectId_firebase', '', 'firebase'), ('storageBucket_firebase', '', 'firebase'), ('messagingSenderId_firebase', '', 'firebase'), ('server_key_firebase', '', 'firebase'), ('logo_for_desktop_notfication_url', '', 'firebase');
ALTER TABLE `#__js_ticket_tickets` ADD `notificationid` INT NOT NULL AFTER `hash`;

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.1.2','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','212','default');
