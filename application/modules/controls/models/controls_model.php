<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Controls_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {   
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->tableName = $this->com->get_table($this->com->get_id('controls'));
        $this->com = $this->com->get_id('controls');
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $field = array('id', 'no', 'desc', 'account_id', 'modul', 'status');
    protected $com;
    
    function get_last($limit, $offset=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('no', 'asc'); 
        $this->db->limit($limit, $offset);
        return $this->db->get(); 
    }
    
    function counter()
    {
        $this->db->select_max('no');
        $test = $this->db->get($this->tableName)->row_array();
        $userid=$test['no'];
	$userid = intval($userid+1);
	return $userid;
    }

}

?>