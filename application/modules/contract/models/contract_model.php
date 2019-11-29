<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Contract_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->tableName = $this->com->get_table($this->com->get_id('contract'));
        $this->com = $this->com->get_id('contract');
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $com,$field;
    
    function get_last($limit, $offset=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('id', 'desc'); 
        $this->db->limit($limit, $offset);
        return $this->db->get(); 
    }
    
    function search($date=null,$cust=null,$type=null)
    {   
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null_string($date, 'dates');
        $this->cek_null_string($cust, 'cust_id');
        $this->cek_null_string($type, 'type');
        $this->db->order_by('id', 'desc'); 
        return $this->db->get(); 
    }
        
    function search_list($content=null)
    {   
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null($content, 'content');
        $this->db->where('status',1);
        $this->db->order_by('sku', 'asc'); 
        return $this->db->get(); 
    }
    
    function report($cat=null,$manufacture=null)
    {   
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null($cat, 'category');
        $this->cek_null($manufacture, 'manufacture');
        
        $this->db->order_by('name', 'asc'); 
        return $this->db->get(); 
    }
    
    function counter()
    {
        $this->db->select_max('id');
        $test = $this->db->get($this->tableName)->row_array();
        $userid=$test['id'];
	$userid = intval($userid+1);
	return $userid;
    }
    
    function max_id()
    {
        $this->db->select_max('id');
        $test = $this->db->get($this->tableName)->row_array();
        $userid=$test['id'];
	$userid = intval($userid);
	return $userid;
    }    

}

?>