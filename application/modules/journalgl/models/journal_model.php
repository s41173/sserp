<?php

class Journal_model extends Custom_Model
{   
    function __construct()
    {   
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->tableName = 'gls';
        $this->com = $this->com->get_id('journalgl');
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $field;
    
    function count()
    {
        //method untuk mengembalikan nilai jumlah baris dari database.
        return $this->db->count_all($this->table);
    }
    
    function get_last($limit, $offset=null, $count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('id', 'desc'); 
        $this->db->limit($limit, $offset);
        $this->cek_count($count,$limit,$offset);
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }

    function search($code=null,$no=null,$dates=null,$count=0)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->cek_null($code, 'code');
        $this->cek_null($no, 'no');
        $this->cek_null($dates, 'dates');
        $this->db->order_by('dates','asc');
        if ($count==0){ return $this->db->get(); }else{ return $this->db->get()->num_rows(); }
    }
    
    function report($cur=null,$type=null,$start=null,$end=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName);
        $this->cek_null($cur, 'currency');
        $this->cek_null($type, 'code');
        $this->between('dates', $start, $end);
        $this->db->where('approved', 1);
        $this->db->order_by('dates','asc');
        return $this->db->get(); 
    }
    
    function get_transaction($gl=0)
    {
        $this->db->select('id, gl_id, account_id, debit, credit, vamount');
        $this->db->from('transactions'); 
        $this->db->where('gl_id', $gl);
        $this->db->order_by('id', 'asc'); 
        return $this->db->get(); 
    }
    
    function get_glid($id=0)
    {
        $this->db->select('id, gl_id, account_id, debit, credit, vamount');
        $this->db->from('transactions'); 
        $this->db->where('id', $id);
        $res = $this->db->get(); 
        if ($res->num_rows()>0){ return $res; }else{ return null; } 
    }
    
    function closing_trans(){
        $this->db->truncate('transactions'); 
        $this->db->truncate('balances'); 
    }
    
}

?>