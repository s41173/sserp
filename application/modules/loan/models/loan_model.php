<?php

class Loan_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('loan');
        $this->tableName = 'loan';
    }
    
    protected $field = array('id', 'employee_id', 'currency', 'amount', 'created', 'updated', 'deleted');
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

    function search($employee_id=null)
    {
        $this->db->select('id, employee_id, employee.type, amount');
        $this->db->from($this->tableName); // from table dengan join nya
        $this->cek_null($employee_id, 'employee_id');
        $this->db->where('deleted', $this->deleted);
        return $this->db->get(); 
    }
    
    function report($e_type=null)
    {
        $this->db->select('loan.id, loan.employee_id, employee.type, loan.amount');
        $this->db->from('loan, employee');
        $this->db->where('employee.id = loan.employee_id');
        $this->cek_null($e_type, 'employee.type');
        $this->db->where('deleted', $this->deleted);
        return $this->db->get(); 
    }
    
    function total_chart($cur='IDR')
    {
        $this->db->select_sum('amount');

        $this->db->from($this->tableName);
        $this->cek_null($cur,"currency");
//        $this->cek_null($month,"MONTH(date)");
//        $this->cek_null($year,"YEAR(date)");
        $this->db->where('deleted', $this->deleted);
        $query = $this->db->get()->row_array();
        return $query['amount'];
    }
    
    function delete_amount()
    {
        $this->db->where('amount', 0);
        $this->db->delete($this->tableName); // perintah untuk delete data dari db
    }
    
}

?>