<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_item_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    var $tableName = 'purchase_item';
    
    function get_last_item($po)
    {
        $this->db->select('id, purchase_id, product, qty, price, tax, amount');
        $this->db->from($this->tableName);
        $this->db->where('purchase_id', $po);
        $this->db->order_by('id', 'asc'); 
        return $this->db->get(); 
    }
    
    function get_by_id($id)
    {
        $this->db->select('id, purchase_id, product, qty, price, tax, amount');
        $this->db->from($this->tableName);
        $this->db->where('id', $id);
        $this->db->order_by('id', 'asc'); 
        return $this->db->get();   
    }
    
    function valid_id($id){
        $this->db->select('id, purchase_id, product, qty, price, tax, amount');
        $this->db->where('id', $id);
        $num = $this->db->get($this->tableName)->num_rows();
        if ($num > 0){ return TRUE; }else{ return FALSE; }
    }

    function total($po)
    {
        $this->db->select_sum('tax');
        $this->db->select_sum('amount');
        $this->db->where('purchase_id', $po);
        return $this->db->get($this->tableName)->row_array();
    }

    function delete($uid)
    {
        $this->db->where('id', $uid);
        return $this->db->delete($this->tableName); // perintah untuk delete data dari db
    }

    function delete_po($uid)
    {
        $this->db->where('purchase_id', $uid);
        return $this->db->delete($this->tableName); // perintah untuk delete data dari db
    }
    
    function add($users)
    {
        return $this->db->insert($this->tableName, $users);
    }
    
    function update($uid, $users)
    {
        $this->db->where('id', $uid);
        return $this->db->update($this->tableName, $users);
    }
    
    function closing(){
        $this->db->truncate($this->tableName); 
    }

}

?>