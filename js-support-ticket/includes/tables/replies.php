<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

class JSSTrepliesTable extends JSSTtable {

	public $id = '';
	public $uid = '';
	public $ticketid = '';
	public $name = '';
	public $message = '';
	public $staffid = '';
	public $rating = '';
	public $status = '';
	public $created = '';
	public $ticketviaemail = '';
	public $viewed_by = '';
	public $viewed_on = '';
	public $aireplymode = '';

	function __construct() {
		parent::__construct('replies', 'id'); // tablename, primarykey
	}

}

?>
