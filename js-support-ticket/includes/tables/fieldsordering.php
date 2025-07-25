<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

class JSSTfieldsorderingTable extends JSSTtable {

	public $id = '';
	public $field = '';
	public $fieldtitle = '';
	public $ordering = '';
	public $section = '';
	public $placeholder = '';
	public $description = '';
	public $fieldfor = '';
	public $published = '';
	public $sys = '';
	public $cannotunpublish = '';
	public $required = '';
	public $size = '';
	public $maxlength = '';
	public $cols = '';
	public $rows = '';
	public $isuserfield = '';
	public $userfieldtype = '';
	public $depandant_field = '';
	public $visible_field = '';
	public $showonlisting = '';
	public $cannotshowonlisting = '';
	public $search_user = '';
	public $search_admin = '';
	public $cannotsearch = '';
	public $isvisitorpublished = '';
	public $search_visitor = '';
	public $userfieldparams = '';
	public $multiformid = '';
	public $visibleparams = '';
	public $readonly = '';
	public $adminonly = '';
	public $defaultvalue = '';

	function __construct() {
		parent::__construct('fieldsordering', 'id'); // tablename, primarykey
	}

}

?>
