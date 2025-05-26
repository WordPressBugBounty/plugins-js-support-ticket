<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

class JSSTproductsTable extends JSSTtable {

	public $id = '';
	public $product = '';
	public $status = '';
	public $ordering = '';

	function __construct() {
		parent::__construct('products', 'id'); // tablename, primarykey
	}

}

?>