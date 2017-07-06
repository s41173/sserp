<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Branch_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('branch');
        $this->tableName = 'branch';
    }
    
    protected $field = array('id', 'code', 'name', 'address', 'phone', 'mobile', 'email', 'city', 'zip', 'image', 'publish', 'defaults', 'sales_account', 'stock_account', 'created', 'updated', 'deleted');
    protected $com;
    
    function get_last($limit, $offset=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('name', 'asc'); 
        $this->db->limit($limit, $offset);
        return $this->db->get(); 
    }
    
     function get_default()
    {
        $this->db->where('defaults', 1);
        return $this->db->get($this->tableName);
    }

}

?>