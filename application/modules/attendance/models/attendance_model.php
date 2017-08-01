<?php

class Attendance_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('attendance');
        $this->tableName = 'attendance';
    }

    protected $com;
    
    protected $field = array('id', 'employee_id', 'month', 'year', 'presence', 'late', 'overtime', 'log', 'created', 'updated', 'deleted');

    function get($limit)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('id', 'desc'); 
        $this->db->limit($limit);
        return $this->db->get(); 
    }
    
    function search($employee_id=null,$month=null,$year=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->cek_null_string($employee_id, 'employee_id');
        $this->cek_null_string($month, 'month');
        $this->cek_null_string($year, 'year');
        $this->db->where('deleted', $this->deleted);
        return $this->db->get(); 
    }
    
    function report($employee_id=null,$month=null,$year=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->cek_null($employee_id, 'employee_id');
        $this->cek_null($month, 'month');
        $this->cek_null($year, 'year');
        $this->db->where('deleted', $this->deleted);
        return $this->db->get(); 
    }
    
}

?>