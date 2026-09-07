<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

/**
 * Blocked senders table. Part of the free core. (Roadmap 4.0-CORE-12)
 *
 * Same js_ticket_email_banlist table and columns as the add-on, so an existing
 * block list is read and written unchanged. (Roadmap 4.0-CORE-19)
 */
class JSSTbanemailTable extends JSSTtable {

	public $id = '';
	public $email = '';
	public $submitter = '';
	public $uid = '';
	public $created = '';

	function __construct() {
		parent::__construct('email_banlist', 'id'); // tablename, primarykey
	}

}

?>