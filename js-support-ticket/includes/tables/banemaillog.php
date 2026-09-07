<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

/**
 * Blocked-sender log table. Part of the free core. (Roadmap 4.0-CORE-12)
 *
 * Same js_ticket_banlist_log table and columns as the add-on, so an existing log
 * is read and written unchanged. (Roadmap 4.0-CORE-19)
 */
class JSSTbanemaillogTable extends JSSTtable {

	public $id = '';
	public $loggeremail = '';
	public $title = '';
	public $log = '';
	public $logger = '';
	public $ipaddress = '';
	public $created = '';

	function __construct() {
		parent::__construct('banlist_log', 'id'); // tablename, primarykey
	}

}

?>
