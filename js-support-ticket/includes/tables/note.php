<?php

if(!defined('ABSPATH'))
	die('Restricted Access');

/**
 * Internal notes table. Part of the free core. (Roadmap 4.0-CORE-02)
 *
 * Only loaded when the stand-alone Private Note add-on is not active; the same
 * js_ticket_notes table is used either way, so existing notes keep working.
 * (Roadmap 4.0-CORE-19)
 */
class JSSTnoteTable extends JSSTtable {

	public $id = '';
	public $ticketid = '';
	public $staffid = '';
	public $title = '';
	public $note = '';
	public $status = '';
	public $created = '';
	public $filename = '';
	public $filesize = '';

	function __construct() {
		parent::__construct('notes', 'id'); // tablename, primarykey
	}

}
