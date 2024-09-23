REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.3.0','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','230','default');
CREATE TABLE IF NOT EXISTS `#__js_ticket_jshdsessiondata` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `usersessionid` char(64) NOT NULL,
  `sessionmsg` text CHARACTER SET utf8 NOT NULL,
  `sessionexpire` bigint(32) NOT NULL,
  `sessionfor` varchar(25) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


