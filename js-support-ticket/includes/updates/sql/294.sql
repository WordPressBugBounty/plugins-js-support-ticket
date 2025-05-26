REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode','2.9.4','default');
REPLACE INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('productversion','294','default');


INSERT INTO `#__js_ticket_config` (`configname`, `configvalue`, `configfor`) 
VALUES ('ticket_replies_ordering', 'ASC', 'ticket');


ALTER TABLE `#__js_ticket_fieldsordering` 
ADD `placeholder` varchar(255) DEFAULT NULL AFTER `section` ,
ADD `description` varchar(255) DEFAULT NULL AFTER `placeholder` ,
ADD `readonly` tinyint(4) DEFAULT 0 AFTER `visibleparams` ,
ADD `search_admin` tinyint(4) DEFAULT NULL AFTER `search_user` ,
ADD `adminonly` tinyint(1) DEFAULT 0 AFTER `readonly` ,
ADD `defaultvalue` varchar(255) DEFAULT NULL AFTER `adminonly`;


ALTER TABLE `#__js_ticket_tickets` 
ADD `token` VARCHAR(1000) DEFAULT NULL AFTER `ticketid` ,
ADD `productid` INT DEFAULT NULL AFTER `customticketno`; 


CREATE TABLE IF NOT EXISTS `#__js_ticket_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` varchar(60) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `#__js_ticket_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(60) DEFAULT NULL,
  `statuscolour` varchar(7) DEFAULT NULL,
  `statusbgcolour` varchar(7) DEFAULT NULL,
  `sys` int(1) DEFAULT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7;


INSERT INTO `#__js_ticket_statuses` (`id`, `status`, `statuscolour`, `statusbgcolour`, `sys`, `ordering`)
VALUES (1, 'New', '#FFFFFF', '#5bb12f', 1, 1),
  (2, 'Waiting Reply', '#FFFFFF', '#28abe3', 1, 2),
  (3, 'In Progress', '#FFFFFF', '#69d2e7', 1, 3),
  (4, 'Replied', '#FFFFFF', '#FFB613', 1, 4),
  (5, 'Closed', '#FFFFFF', '#ed1c24', 1, 5),
  (6, 'Close due to merge', '#FFFFFF', '#ed1c24', 1, 6);


UPDATE `#__js_ticket_tickets` SET status = 6 WHERE status = 5;
UPDATE `#__js_ticket_tickets` SET status = 5 WHERE status = 4;
UPDATE `#__js_ticket_tickets` SET status = 4 WHERE status = 3;
UPDATE `#__js_ticket_tickets` SET status = 3 WHERE status = 2;
UPDATE `#__js_ticket_tickets` SET status = 2 WHERE status = 1;
UPDATE `#__js_ticket_tickets` SET status = 1 WHERE status = 0;


UPDATE `#__js_ticket_fieldsordering` SET userfieldtype = 'text', adminonly = 1 WHERE userfieldtype = 'admin_only';


UPDATE `#__js_ticket_fieldsordering` SET search_admin = 1 WHERE search_user = 1 AND isuserfield = 1;


UPDATE `#__js_ticket_fieldsordering` SET cannotunpublish = 0 WHERE field = 'email';
UPDATE `#__js_ticket_fieldsordering` SET cannotunpublish = 0 WHERE field = 'fullname';
UPDATE `#__js_ticket_fieldsordering` SET cannotunpublish = 0 WHERE field = 'priority';
UPDATE `#__js_ticket_fieldsordering` SET cannotunpublish = 0 WHERE field = 'issuesummary';


UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 1, cannotsearch = 0, search_admin = 1, search_user = 0 WHERE field = 'fullname';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 0, cannotsearch = 0, search_admin = 1, search_user = 1 WHERE field = 'email';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 0, cannotsearch = 0, search_admin = 0, search_user = 0 WHERE field = 'phone';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 1, cannotsearch = 0, search_admin = 1, search_user = 1 WHERE field = 'department';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 0, cannotsearch = 0, search_admin = 0, search_user = 0 WHERE field = 'helptopic';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 1, cannotsearch = 0, search_admin = 1, search_user = 1 WHERE field = 'priority';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 1, cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'assignto';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 0, cannotsearch = 0, search_admin = 0, search_user = 0 WHERE field = 'duedate';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 0, showonlisting = 1, cannotsearch = 0, search_admin = 1, search_user = 1 WHERE field = 'status';
UPDATE `#__js_ticket_fieldsordering` SET cannotshowonlisting = 1, showonlisting = 0, cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'envatopurchasecode';


UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 0, search_admin = 1, search_user = 1 WHERE field = 'subject';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 0, search_admin = 0, search_user = 1 WHERE field = 'wcorderid';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 0, search_admin = 0, search_user = 1 WHERE field = 'eddorderid';


UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'users';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'premade';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'issuesummary';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'attachments';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'internalnotetitle';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'rating';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'remarks';

UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 0, search_admin = 0, search_user = 0 WHERE field = 'wcproductid';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 1, search_admin = 0, search_user = 0 WHERE field = 'eddproductid';
UPDATE `#__js_ticket_fieldsordering` SET cannotsearch = 0, search_admin = 0, search_user = 0 WHERE field = 'eddlicensekey';


UPDATE `#__js_ticket_fieldsordering` AS t1
JOIN `#__js_ticket_fieldsordering` AS t2
  ON JSON_UNQUOTE(JSON_EXTRACT(t1.visibleparams, '$.visibleParent')) + 0 = t2.id
SET t1.visibleparams = JSON_ARRAY(
    JSON_ARRAY(
        JSON_OBJECT(
            'visibleParentField', JSON_UNQUOTE(JSON_EXTRACT(t1.visibleparams, '$.visibleParentField')),
            'visibleParent', t2.field,
            'visibleCondition', JSON_UNQUOTE(JSON_EXTRACT(t1.visibleparams, '$.visibleCondition')),
            'visibleValue', JSON_UNQUOTE(JSON_EXTRACT(t1.visibleparams, '$.visibleValue')),
            'visibleLogic', 'AND'
        )
    )
)
WHERE t1.visibleparams IS NOT NULL
  AND t1.visibleparams != ''
  AND JSON_VALID(t1.visibleparams)
  AND JSON_TYPE(JSON_EXTRACT(t1.visibleparams, '$')) = 'OBJECT';


INSERT INTO `#__js_ticket_fieldsordering` (
  `field`, `fieldtitle`, `ordering`, `section`, `fieldfor`,
  `published`, `sys`, `cannotunpublish`, `required`, `size`, `maxlength`, `isuserfield`, `userfieldtype`, `depandant_field`,
  `showonlisting`, `cannotshowonlisting`, `search_admin`, `search_user`,
  `cannotsearch`, `isvisitorpublished`, `search_visitor`, `userfieldparams`, 
  `multiformid`
)
SELECT
  'product', 'Product', 
  COALESCE(MAX(fo.ordering) + 1, 1), 
  '10', 1, 0, 0, 0, 
  0, NULL, NULL, NULL, NULL, NULL, 
  0, 0, 0, 0, 0, 0, 0, NULL, 
  forms.multiformid
FROM (
  SELECT DISTINCT multiformid
  FROM `#__js_ticket_fieldsordering`
  WHERE multiformid > 0
) AS forms
LEFT JOIN `#__js_ticket_fieldsordering` fo ON fo.multiformid = forms.multiformid
GROUP BY forms.multiformid;


INSERT INTO `#__js_ticket_fieldsordering` (
  `field`, `fieldtitle`, `ordering`, `section`, `fieldfor`,
  `published`, `sys`, `cannotunpublish`, `required`, `size`, `maxlength`, `isuserfield`, `userfieldtype`, `depandant_field`,
  `showonlisting`, `cannotshowonlisting`, `search_admin`, `search_user`,
  `cannotsearch`, `isvisitorpublished`, `search_visitor`, `userfieldparams`, 
  `multiformid`
)
SELECT
  'termsandconditions1', 'Terms and Conditions 1', 
  COALESCE(MAX(fo.ordering) + 1, 1), 
  '10', 1, 0, 0, 0, 
  1, NULL, NULL, NULL, NULL, NULL, 
  0, 1, 0, 0, 1, 0, 0, '{"termsandconditions_text":"I agree to the terms and conditions.","termsandconditions_linktype":"3"}', 
  forms.multiformid
FROM (
  SELECT DISTINCT multiformid
  FROM `#__js_ticket_fieldsordering`
  WHERE multiformid > 0
) AS forms
LEFT JOIN `#__js_ticket_fieldsordering` fo ON fo.multiformid = forms.multiformid
GROUP BY forms.multiformid;


INSERT INTO `#__js_ticket_fieldsordering` (
  `field`, `fieldtitle`, `ordering`, `section`, `fieldfor`,
  `published`, `sys`, `cannotunpublish`, `required`, `size`, `maxlength`, `isuserfield`, `userfieldtype`, `depandant_field`,
  `showonlisting`, `cannotshowonlisting`, `search_admin`, `search_user`,
  `cannotsearch`, `isvisitorpublished`, `search_visitor`, `userfieldparams`, 
  `multiformid`
)
SELECT
  'termsandconditions2', 'Terms and Conditions 2', 
  COALESCE(MAX(fo.ordering) + 1, 1), 
  '10', 1, 0, 0, 0, 
  1, NULL, NULL, NULL, NULL, NULL, 
  0, 1, 0, 0, 1, 0, 0, '{"termsandconditions_text":"I understand my personal info may be stored.","termsandconditions_linktype":"3"}', 
  forms.multiformid
FROM (
  SELECT DISTINCT multiformid
  FROM `#__js_ticket_fieldsordering`
  WHERE multiformid > 0
) AS forms
LEFT JOIN `#__js_ticket_fieldsordering` fo ON fo.multiformid = forms.multiformid
GROUP BY forms.multiformid;


INSERT INTO `#__js_ticket_fieldsordering` (
  `field`, `fieldtitle`, `ordering`, `section`, `fieldfor`,
  `published`, `sys`, `cannotunpublish`, `required`, `size`, `maxlength`, `isuserfield`, `userfieldtype`, `depandant_field`,
  `showonlisting`, `cannotshowonlisting`, `search_admin`, `search_user`,
  `cannotsearch`, `isvisitorpublished`, `search_visitor`, `userfieldparams`, 
  `multiformid`
)
SELECT
  'termsandconditions3', 'Terms and Conditions 3', 
  COALESCE(MAX(fo.ordering) + 1, 1), 
  '10', 1, 0, 0, 0, 
  1, NULL, NULL, NULL, NULL, NULL, 
  0, 1, 0, 0, 1, 0, 0, '{"termsandconditions_text":"I acknowledge response times may vary.","termsandconditions_linktype":"3"}', 
  forms.multiformid
FROM (
  SELECT DISTINCT multiformid
  FROM `#__js_ticket_fieldsordering`
  WHERE multiformid > 0
) AS forms
LEFT JOIN `#__js_ticket_fieldsordering` fo ON fo.multiformid = forms.multiformid
GROUP BY forms.multiformid;





