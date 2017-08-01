<?php

class Employee_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('employee');
        $this->tableName = 'employee';
    }
    
    protected $field = array('id', 'division_id', 'role', 'attcode', 'nip', 'type', 'work_time', 'dept_id', 'name',
                             'first_title', 'end_title', 'nickname', 'genre', 'born_place', 'born_date', 'religion',
                             'ethnic', 'status', 'id_no', 'address', 'phone', 'mobile', 'email', 'image', 'desc',
                             'bank_name', 'acc_name', 'acc_no', 'joined', 'resign', 'subject', 'active',
                             'created', 'updated', 'deleted');
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

    function search($division='null', $role='null', $active='null')
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); // from table dengan join nya
        $this->cek_null_string($division,"division_id");
        $this->cek_null_string($role,"role");
        $this->cek_null_string($active,"active");
        return $this->db->get(); 
    }
    
    function report($division=null, $role=null, $status=null,$start,$end)
    {
        $this->db->from($this->tableName); // from table dengan join nya
//        $this->cek_null($dept, 'dept_id');
        $this->cek_null($division, 'division_id');
        $this->cek_null($role, 'role');
        
        if($status == 0){ $this->db->where('resign >', $end); }
        elseif ($status == 1){ $this->db->where("joined BETWEEN '".setnull($start)."' AND '".setnull($end)."'"); }
        elseif ($status == 2){ $this->db->where("resign BETWEEN '".setnull($start)."' AND '".setnull($end)."'"); }
        return $this->db->get(); 
    }
    
}

?>