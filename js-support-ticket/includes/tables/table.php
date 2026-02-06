<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTtable {

    public $isnew = false;
    public $columns = array();
    public $primarykey = '';
    public $tablename = '';

    function __construct($jsst_tbl, $jsst_pk) {
        $this->tablename = jssupportticket::$_db->prefix . 'js_ticket_' . $jsst_tbl;
        $this->primarykey = $jsst_pk;
    }

    public function bind($jsst_data) {
        if ((!is_array($jsst_data)) || (empty($jsst_data)))
            return false;
        if (isset($jsst_data['id']) && !empty($jsst_data['id'])) { // Edit case
            $this->isnew = false;
        } else { // New case
            $this->isnew = true;
        }
        $jsst_result = $this->setColumns($jsst_data);
        return $jsst_result;
    }

    protected function setColumns($jsst_data) {
        if ($this->isnew == true) { // new record insert
            $jsst_array = get_object_vars($this);
            if(isset($jsst_array['id'])){
                unset($jsst_array['id']);
            }
            unset($jsst_array['isnew']);
            unset($jsst_array['primarykey']);
            unset($jsst_array['tablename']);
            unset($jsst_array['columns']);
            foreach ($jsst_array AS $jsst_k => $jsst_v) {
                if (isset($jsst_data[$jsst_k])) {
                    $this->$jsst_k = $jsst_data[$jsst_k];
                }
                $this->columns[$jsst_k] = $this->$jsst_k;
            }
        } else { // update record
            if (isset($jsst_data[$this->primarykey])) {
                foreach ($jsst_data AS $jsst_k => $jsst_v) {
                    if (isset($this->$jsst_k)) {
                        $this->$jsst_k = $jsst_v;
                        $this->columns[$jsst_k] = $jsst_v;
                    }
                }
            } else {
                return false; // record cannot be updated b/c of pk not exist
            }
        }
        return true;
    }

    function store() {
        if ($this->isnew == true) { // new record store
            jssupportticket::$_db->insert($this->tablename, $this->columns);
            if (jssupportticket::$_db->last_error == null) {
                $this->{$this->primarykey} = jssupportticket::$_db->insert_id;
                $jsst_id = jssupportticket::$_db->insert_id;
                //activity log //1 for insert
                //JSSTincluder::getJSModel('tickethistory')->storeActivity(1, $this->tablename, $this->columns, $jsst_id);
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                return false;
            }
        } else { // record updated
            jssupportticket::$_db->update($this->tablename, $this->columns, array($this->primarykey => $this->columns[$this->primarykey]));
            //JSSTincluder::getJSModel('tickethistory')->storeActivity(2, $this->tablename, $this->columns);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                return false;
            }
        }
        return true;
    }

    function update($jsst_data) {
        $jsst_result = $this->bind($jsst_data);
        if ($jsst_result == false) {
            return false;
        }
        $jsst_result = $this->store();
        if ($jsst_result == false) {
            return false;
        }
        return true;
    }

    function delete($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        //data for delete
        //$jsst_data = JSSTincluder::getJSModel('tickethistory')->getDeleteActionDataToStore($this->tablename, $jsst_id);
        jssupportticket::$_db->delete($this->tablename, array($this->primarykey => $jsst_id));
        if (jssupportticket::$_db->last_error == null) {
            //JSSTincluder::getJSModel('tickethistory')->storeActivityLogForActionDelete($jsst_data, $jsst_id);
            return true;
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            return false;
        }
    }

    function check() {
        return true;
    }

    function load($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        $jsst_query = "SELECT * FROM `".$this->tablename."` WHERE ".$this->primarykey." = ".esc_sql($jsst_id);
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
        $jsst_array = get_object_vars($this);
        unset($jsst_array['isnew']);
        unset($jsst_array['primarykey']);
        unset($jsst_array['tablename']);
        unset($jsst_array['columns']);
        foreach ($jsst_array AS $jsst_k => $jsst_v) {
            if (isset($jsst_result->$jsst_k)) {
                $this->$jsst_k = $jsst_result->$jsst_k;
            }
            $this->columns[$jsst_k] = $this->$jsst_k;
        }
        return true;
    }

}

?>
