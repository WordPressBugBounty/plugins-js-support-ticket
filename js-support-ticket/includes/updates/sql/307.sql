REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','3.0.7','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','307','default');


CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_categories` (
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `status` tinyint(1) DEFAULT '1',
    `ordering` int(11) DEFAULT NULL,
    PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_ai_models` (
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `status` tinyint(1) DEFAULT '1',
    `ordering` int(11) DEFAULT NULL,
    PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_languages` (
    `code` varchar(10) NOT NULL,
    `name` varchar(255) NOT NULL,
    `status` tinyint(1) DEFAULT '1',
    `ordering` int(11) DEFAULT NULL,
    PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_use_cases` (
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` text,
    `category_code` varchar(255) DEFAULT NULL,
    `schema_data` longtext,
    `status` tinyint(1) DEFAULT '1',
    `ordering` bigint(20) DEFAULT NULL,
    PRIMARY KEY (`code`),
    KEY `category_code` (`category_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_wrappers` (
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` text,
    `use_case_code` varchar(255) DEFAULT NULL,
    `featured` tinyint(1) DEFAULT '0',
    `base` tinyint(1) DEFAULT '0',
    `status` tinyint(1) DEFAULT '1',
    `ordering` bigint(20) DEFAULT NULL,
    PRIMARY KEY (`code`),
    KEY `use_case_code` (`use_case_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_block_templates` (
    `type` varchar(50) NOT NULL,
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `status` tinyint(1) DEFAULT '1',
    PRIMARY KEY (`type`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_settings` (
    `setting_key` varchar(255) NOT NULL,
    `setting_value` text,
    PRIMARY KEY (`setting_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__js_ticket_zywrap_usage_logs` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `trace_id` varchar(255) DEFAULT NULL,
    `wrapper_code` varchar(255) DEFAULT NULL,
    `model_code` varchar(255) DEFAULT NULL,
    `prompt_tokens` int(11) DEFAULT '0',
    `completion_tokens` int(11) DEFAULT '0',
    `total_tokens` int(11) DEFAULT '0',
    `credits_used` bigint(20) DEFAULT '0',
    `latency_ms` int(11) DEFAULT '0',
    `status` varchar(50) DEFAULT 'success',
    `error_message` text,
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `wrapper_idx` (`wrapper_code`),
    KEY `model_idx` (`model_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;