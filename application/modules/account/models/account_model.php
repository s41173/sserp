<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Account_model extends Custom_Model
{
    protected $logs;
    
    function __construct()
    {        
        parent::__construct();
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->tableName = $this->com->get_table($this->com->get_id('account'));
        $this->com = $this->com->get_id('account');
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $field;
    protected $com;
    
    function get_last($limit, $offset=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->db->order_by('id', 'desc'); 
        $this->db->limit($limit, $offset);
        return $this->db->get(); 
    }
    
    function get_list($clas=null)
    {
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null($clas, 'classification_id');
        $this->db->order_by('code', 'asc'); 
        return $this->db->get(); 
    }
    
    function get_by_code($code){
      $this->db->select($this->field);
      $this->db->from($this->tableName); 
      $this->db->where('deleted', $this->deleted); 
      $this->db->where('code', $code); 
      return $this->db->get(); 
    }
    
    function search($clas=null,$publish=null)
    {   
        $this->db->select($this->field);
        $this->db->from($this->tableName); 
        $this->db->where('deleted', $this->deleted);
        $this->cek_null_string($clas, 'classification_id');
        $this->cek_null_string($publish, 'status');
        
        $this->db->order_by('id', 'asc'); 
        return $this->db->get(); 
    }
    
    function valid_default($uid)
    {
        $this->db->where('id', $uid);
        $query = $this->db->get($this->tableName)->row();
        if($query->default == 1){ return FALSE; }else{ return TRUE; }
    }
    
    function report($cur=null,$stts=null,$cla=null)
    {
        $this->db->select('id, classification_id, currency, code, name, alias, status');
        $this->cek_null($cur, 'currency');
        $this->cek_null($stts, 'status');
        $this->cek_null($cla, 'classification_id');
        $this->db->order_by('code','asc');
        return $this->db->get('accounts');
    }

}

?>