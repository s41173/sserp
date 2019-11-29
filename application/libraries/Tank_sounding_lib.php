<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tank_sounding_lib extends Custom_Model {
    
    public function __construct($deleted=NULL)
    {
        $this->deleted = $deleted;
        $this->logs = new Log_lib();
        $this->com = new Components();
        $this->tableName = $this->com->get_table($this->com->get_id('sounding'));
        $this->com = $this->com->get_id('sounding');
        $this->field = $this->db->list_fields($this->tableName);
    }
    
    protected $com,$field;

    function cek_relation($id,$type)
    {
       $this->db->where($type, $id);
       $query = $this->db->get($this->tableName)->num_rows();
       if ($query > 0) { return FALSE; } else { return TRUE; }
    }
 
    function get_sounding($dates=null,$tankid=0,$type=null)
    {
       $this->db->select($this->field);
       $this->db->where('dates', $dates);
       $this->db->where('tank_id', $tankid);
       $this->db->where('approved', 1);
       $res = $this->db->get($this->tableName)->row();
       if ($res){ if ($type){ return $res->$type; }else{ return $res; }
       }else{ return 0; }
    }    
    
}

/* End of file Property.php */