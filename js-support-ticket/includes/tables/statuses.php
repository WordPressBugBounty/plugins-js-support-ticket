<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

class JSSTstatusesTable extends JSSTtable {

	public $id = '';
	public $status = '';
	public $statuscolour = '';
	public $statusbgcolour = '';
	public $sys = '';
	public $ordering = '';

	function __construct() {
		parent::__construct('statuses', 'id'); // tablename, primarykey
	}

}

?>