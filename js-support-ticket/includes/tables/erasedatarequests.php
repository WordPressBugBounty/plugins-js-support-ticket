<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

class JSSTerasedatarequestsTable extends JSSTtable {

	public $id = '';
	public $uid = '';
	public $subject = '';
	public $message = '';
	public $status = '';
	public $created = '';


	function __construct() {
		parent::__construct('erasedatarequests', 'id'); // tablename, primarykey
	}

}

?>
