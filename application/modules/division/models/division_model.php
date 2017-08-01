<?php

class Division_model extends Custom_Model
{   
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('division');
        $this->tableName = 'division';
    }
    
    protected $field = array('id', 'name', 'role', 'basic_salary', 'consumption', 'transportation', 'overtime', 'created', 'updated', 'deleted');
    protected $com;
    
    function get($limit)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('id', 'desc'); 
        $this->db->limit($limit);
        return $this->db->get(); 
    }
    
}

?>