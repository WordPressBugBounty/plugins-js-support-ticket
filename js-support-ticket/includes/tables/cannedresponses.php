<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

class JSSTcannedresponsesTable extends JSSTtable {

	public $id = '';
	public $departmentid = '';
	public $title = '';
	public $answer = '';
	public $created = '';
	public $updated = '';
	public $status = '';

	function __construct() {
		parent::__construct('department_message_premade', 'id'); // tablename, primarykey
	}

}

?>