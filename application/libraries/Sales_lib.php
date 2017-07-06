<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->tableName = 'sales';
    }

    protected $field = array('id', 'dates', 'cust_id', 'amount', 'tax', 'cost', 'total', 'shipping',
                             'payment_id', 'bank_id', 'paid_date', 'paid_contact', 'due_date', 
                             'cc_no', 'cc_name', 'cc_bank', 'sender_name', 'sender_acc', 'sender_bank', 'sender_amount', 'confirmation',
                             'approved', 'log', 'created', 'updated', 'deleted');

    function cek_relation($id,$type)
    {
       $this->db->where($type, $id);
       $query = $this->db->get('product')->num_rows();
       if ($query > 0) { return FALSE; } else { return TRUE; }
    }
    
    function get_detail_sales($id=null)
    {
        if ($id)
        {
           $this->db->select($this->field);
           $this->db->where('id', $id);
           $res = $this->db->get($this->tableName)->row();
           return $res;
        }
    }
    
    function get_transaction_sales($id=null)
    {
        if ($id)
        {
           $this->db->where('sales_id', $id);
           $res = $this->db->get('sales_item');
           return $res;
        }
    }
    
    function total($pid)
    {
        $this->db->select_sum('tax');
        $this->db->select_sum('amount');
        $this->db->select_sum('price');
        $this->db->select_sum('qty');
        $this->db->select_sum('weight');
        $this->db->where('sales_id', $pid);
        return $this->db->get('sales_item')->row_array();
    }
    
    function cek_shiping_based_sales($sid)
    {
       if ($sid)
        {
           $this->db->select($this->field);
           $this->db->where('sales_id', $sid);
           $res = $this->db->get($this->tableName)->row();
           if ($res){
              if ($res->shipdate){ return true; }else{ return false; } 
           }
           
        } 
    }

}

/* End of file Property.php */