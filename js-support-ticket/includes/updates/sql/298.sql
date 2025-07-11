SET SQL_MODE='ALLOW_INVALID_DATES';

REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.9.8','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','298','default');

INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) 
VALUES ('show_avatar', '2', 'default');

ALTER TABLE `#__js_ticket_tickets` ENGINE = InnoDB;
ALTER TABLE `#__js_ticket_replies` ENGINE = InnoDB;


ALTER TABLE `#__js_ticket_tickets` ADD FULLtext(subject);
ALTER TABLE `#__js_ticket_tickets` ADD FULLtext(message);
ALTER TABLE `#__js_ticket_replies` ADD FULLtext(message);


ALTER TABLE `#__js_ticket_tickets` 
ADD `aireplymode` tinyint(4) DEFAULT 0 AFTER `productid`;
ALTER TABLE `#__js_ticket_replies` 
ADD `aireplymode` tinyint(4) DEFAULT 0 AFTER `viewed_on`;
