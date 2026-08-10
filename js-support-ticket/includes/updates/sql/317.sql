REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','3.1.7','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','317','default');

ALTER TABLE `#__js_ticket_replies` ADD `is_ai_draft` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__js_ticket_replies` ADD `ticketviaautopilot` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `#__js_ticket_tickets` ADD FULLTEXT `jsst_ir_ft` (`subject`, `message`);

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_enable', '1', 'instantresolve', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_min_chars', '15', 'instantresolve', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_sources', '["kb","faq","canned","posts"]', 'instantresolve', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_analytics', '1', 'instantresolve', NULL);
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_max_results', '5', 'instantresolve', NULL);

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_ai_enable', '2', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_ai_tone', 'professional', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_ai_language', 'auto', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_ai_provider', 'zywrap', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_ai_sources_limit', '3', 'instantresolve', 'instantresolve');

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_enable', '2', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_min_confidence', '85', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_max_replies', '2', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_delay', '0', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_target_departments', '[]', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_target_users', '["logged_in"]', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_blacklist_emails', '', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_blacklist_keywords', 'refund, cancel, lawyer, angry, manager, sue', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_fallback', 'draft_reply', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_autopilot_display_name', 'AI Assistant', 'instantresolve', 'instantresolve');

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_grounding_mode', 'strict_kb', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_min_coverage_deflect', '25', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_min_coverage_reply', '50', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_max_chunks', '4', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_char_budget', '6000', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_require_citation', '1', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_min_overlap', '65', 'instantresolve', 'instantresolve');

INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_token_budget', '1500', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_chunk_chars', '900', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_chunk_overlap', '150', 'instantresolve', 'instantresolve');
INSERT IGNORE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('instantresolve_mmr_lambda', '70', 'instantresolve', 'instantresolve');