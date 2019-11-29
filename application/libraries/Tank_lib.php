<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tank_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->ledger = new Tankledger_lib();
        $this->balance = new Tank_balance_lib();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->tableName = $this->com->get_table($this->com->get_id('tank'));
        $this->com = $this->com->get_id('tank');
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $com,$field;
    private $ledger,$balance;

    function cek_relation($id,$type)
    {
       $this->db->where($type, $id);
       $query = $this->db->get($this->tableName)->num_rows();
       if ($query > 0) { return FALSE; } else { return TRUE; }
    }

    function valid_sku($sku){
        
       $this->db->where('sku', $sku);
       $val = $this->db->get($this->tableName)->num_rows(); 
       if ($val > 0){ return TRUE; }else{ return FALSE; }
    }

    function valid_qty($pid,$qty)
    {
       $this->db->select('id, name, qty');
       $this->db->where('id', $pid);
       $res = $this->db->get($this->tableName)->row();
       if ($res->qty - $qty < 0){ return FALSE; } else { return TRUE; }
    }
    
    function get_id_by_sku($name=null)
    {
        if ($name)
        {
           $this->db->select('id');
           $this->db->where('sku', $name);
           $res = $this->db->get($this->tableName)->row();
           if ($res){ return $res->id; }else{ return 0; }
        }
    }
    
    function get_details($uid=null,$type=null)
    {
        $this->db->select($this->field);
        $this->db->where('id', $uid);
        $res = $this->db->get($this->tableName);
        if ($res->num_rows() > 0){
            $res = $res->row();
            if ($type){ return $res->$type; }else{ return $res; }
        }
    }
    
    function get_detail_based_sku($sku=null)
    {
        if ($sku)
        {
           $this->db->select($this->field);
           $this->db->where('sku', $sku);
           $res = $this->db->get($this->tableName)->row();
           if ($res){ return $res; }else{ return null; }
        }
    }
    
    function get_all()
    {
      $this->db->select($this->field);
      $this->db->where('deleted', $this->deleted);
      $this->db->where('status', 1);
      $this->db->order_by('sku', 'asc');
      return $this->db->get($this->tableName);
    }
    
    function get_qty($pid,$month=0,$year=0){
        
        $opening = floatval($this->balance->get($pid, $month, $year, 'beginning'));
        $qty = $this->ledger->get_ledger($pid, $month, $year, 'sum');
        $qty = floatval($qty['vamount']);
        return $opening+$qty;
    }
    
    function combo($type=null)
    {
        if ($type){ $data['options'][''] = '--Select--'; }
        $this->db->select($this->field);
        $this->db->where('deleted', $this->deleted);
        $this->db->where('status', 1);
        $val = $this->db->get($this->tableName)->result();
        if ($val){ foreach($val as $row){$data['options'][$row->id] = ucfirst($row->sku);} }
        else { $data['options'][''] = '--'; }        
        return $data;
    }
    
    function combo_publish($id)
    {
        $this->db->select($this->field);
        $this->db->where('deleted', $this->deleted);
        $this->db->where('status', 1);
        $this->db->where_not_in('id', $id);
        $val = $this->db->get($this->tableName)->result();
        if ($val){ foreach($val as $row){$data['options'][$row->id] = ucfirst($row->sku);} }
        else { $data['options'][''] = '--'; }        
        return $data;
    }
    
    function combo_content()
    {
        $this->db->select('content');
        $this->db->distinct();
        $this->db->where('deleted', $this->deleted);
        if ($this->db->get($this->tableName)->num_rows()>0){
            $val = $this->db->get($this->tableName)->result();
            foreach($val as $row){$data['options'][$row->content] = strtoupper($row->content);}
        }else{ $data['options']['CPO'] = strtoupper('CPO'); }
        return $data;
    }
    
    
}

/* End of file Property.php */