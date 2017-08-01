<?php

class Experience_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    var $table = 'experience_bonus';
    
    function count()
    {
        //method untuk mengembalikan nilai jumlah baris dari database.
        $this->db->from("experience_bonus, employee");
        $this->db->where('experience_bonus.employee_id = employee.id');
        $this->db->where("employee.active", 1);
        return $this->db->count_all_results();
    }
    
    function get($limit=null)
    {
       $this->db->select('experience_bonus.id, experience_bonus.employee_id, experience_bonus.dept, experience_bonus.time_work, experience_bonus.amount,
                           experience_bonus.consumption, experience_bonus.transportation, experience_bonus.bonus, experience_bonus.bonus_remarks, experience_bonus.principal,
                           experience_bonus.principal_helper, experience_bonus.head_department, experience_bonus.home_room, experience_bonus.picket,
                           experience_bonus.insurance, experience_bonus.log,
                           employee.division_id');
       
        $this->db->from("experience_bonus, employee");
        $this->db->where('experience_bonus.employee_id = employee.id');
        $this->db->where("employee.active", 1);
        $this->db->limit($limit);
        $this->db->order_by('employee.name', 'asc');
        return $this->db->get(); 
    }

    function search($division=null)
    {
       $this->db->select('experience_bonus.id, experience_bonus.employee_id, experience_bonus.dept, experience_bonus.time_work, experience_bonus.amount,
                           experience_bonus.consumption, experience_bonus.transportation, experience_bonus.bonus, experience_bonus.bonus_remarks, experience_bonus.principal,
                           experience_bonus.principal_helper, experience_bonus.head_department, experience_bonus.home_room, experience_bonus.picket,
                           experience_bonus.insurance, experience_bonus.log, employee.division_id');
       
        $this->db->from("experience_bonus, employee");
        $this->db->where('experience_bonus.employee_id = employee.id');
        $this->db->where("employee.active", 1);
        $this->cek_null($division, 'employee.division_id');
        return $this->db->get(); 
    }
    
    function report()
    {
        $this->db->select('experience_bonus.id, experience_bonus.employee_id, experience_bonus.dept, experience_bonus.time_work, experience_bonus.amount,
                           experience_bonus.consumption, experience_bonus.transportation, experience_bonus.bonus, experience_bonus.bonus_remarks, experience_bonus.principal,
                           experience_bonus.principal_helper, experience_bonus.head_department, experience_bonus.home_room, experience_bonus.picket,
                           experience_bonus.insurance, experience_bonus.log');
        
        $this->db->from('experience_bonus, employee');
        $this->db->where('employee.id = experience_bonus.employee_id');
        return $this->db->get(); 
    }
    
    private function cek_between($start,$end)
    {
        if ($start == null || $end == null ){return null;}
        else { return $this->db->where("experience.date BETWEEN '".$start."' AND '".$end."'"); }
    }
    
    private function cek_null($val,$field)
    {
        if ($val == ""){return null;}
        else {return $this->db->where($field, $val);}
    }
    
}

?>