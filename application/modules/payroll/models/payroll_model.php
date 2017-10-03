<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payroll_model extends Custom_Model
{   
    protected $logs;
    
    function __construct()
    {
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->com = $this->com->get_id('payroll');
        $this->tableName = 'payroll';
    }
    
    protected $field = array('id', 'month', 'year', 'start', 'end', 'dates', 'currency', 'acc', 'log', 'total_honor', 'total_salary',
                             'total_bonus', 'total_consumption',
                             'total_transportation', 'total_overtime', 'total_late', 'total_loan', 'total_insurance', 'total_tax',
                             'total_other', 'balance', 'notes' ,'approved',
                             'created', 'updated', 'deleted');
    protected $com;
    
    function get($limit, $year=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->cek_null($year,"year");
        $this->db->order_by('dates', 'desc');
        $this->db->where('deleted', $this->deleted);
        $this->db->limit($limit);
        return $this->db->get(); 
    }
    
    function search($month, $year)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->cek_null_string($month,"month");
        $this->cek_null_string($year,"year");
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('dates', 'desc');
        return $this->db->get(); 
    }

//    =========================================  REPORT  =================================================================

    function report($cur='IDR', $year=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->db->where('currency', $cur);
        $this->cek_null($year,"year");
        $this->db->order_by('dates', 'asc');
        $this->db->where('approved', 1);
        return $this->db->get(); 
    }

    function total_chart($month,$year,$cur='IDR')
    {
        $this->db->select_sum('balance');

        $this->db->from($this->tableName);
        $this->cek_null($cur,"currency");
        $this->db->where('approved', 1);
        $this->cek_null($month,"month");
        $this->cek_null($year,"year");
        $query = $this->db->get()->row_array();
        return floatval($query['balance']);
    }

//    =========================================  REPORT  =================================================================

}

?>