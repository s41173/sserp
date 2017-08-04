<?php

class Loan_trans_model extends Custom_Model
{   
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('loan_trans');
        $this->tableName = 'loan_trans';
    }
    
    protected $field = array('id', 'employee_id', 'currency', 'acc', 'type', 'notes', 'dates', 'amount', 'log', 'created', 'updated', 'deleted');
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

    function search($employee_id=null,$date=null, $type=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); // from table dengan join nya
        $this->cek_null_string($employee_id, 'employee_id');
        $this->cek_null_string($date, 'dates');
        $this->cek_null_string($type, 'type');
        $this->db->where('deleted', $this->deleted);
        return $this->db->get(); 
    }
    
    function report($start=null, $end=null, $t_type=null)
    {
        $this->db->select('loan_trans.id, employee.type, loan_trans.type as trans_type, loan_trans.notes,  loan_trans.employee_id, loan_trans.dates, loan_trans.amount, loan_trans.log');
        $this->db->from('loan_trans, employee');
        $this->db->where('employee.id = loan_trans.employee_id');
        $this->between('loan_trans.dates', $start, $end);
        $this->cek_null($t_type, 'loan_trans.type');
        $this->db->where('loan_trans.deleted', $this->deleted);
        return $this->db->get(); 
    }
   
    
    function total_chart($month,$year,$cur='IDR')
    {
        $this->db->select_sum('amount');

        $this->db->from($this->tableName);
        $this->cek_null($cur,"currency");
        $this->cek_null($month,"MONTH(date)");
        $this->cek_null($year,"YEAR(date)");
        $this->db->where('type', 'borrow');
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