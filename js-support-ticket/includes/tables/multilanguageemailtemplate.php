<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

class JSSTmultilanguageemailtemplateTable extends JSSTtable {

	public $id = '';
	public $language_id = '';
	public $templatefor = '';
	public $subject = '';
	public $body = '';
	public $status = '';
	public $created = '';


	function __construct() {
		parent::__construct('multilanguageemailtemplate', 'id'); // tablename, primarykey
	}

}

?>