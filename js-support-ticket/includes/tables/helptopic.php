<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

/**
 * Topics table. Part of the free core. (Roadmap 4.0-CORE-06, 4.0-CORE-20)
 *
 * The same js_ticket_help_topics table and the same columns the add-on used, so
 * an existing site's topics are read and written unchanged. (Roadmap 4.0-CORE-19)
 *
 * parentid, staffid and isdefault are added by 4.0-CORE-20. They are added to
 * the table by JSSThelptopicModel::ensureSchema(), which checks for each one
 * first, so a site still running the legacy add-on has a table without them and
 * simply never writes them.
 */
class JSSThelptopicTable extends JSSTtable {

	public $id = '';
	public $isactive = '';
	public $autoresponce = '';
	public $departmentid = '';
	public $priorityid = '';
	public $topic = '';
	public $ordering = '';
	public $created = '';
	public $updated = '';
	public $status = '';
	public $parentid = '';
	public $staffid = '';
	public $isdefault = '';

	function __construct() {
		parent::__construct('help_topics', 'id'); // tablename, primarykey
	}

}

?>