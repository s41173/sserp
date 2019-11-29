<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Unit extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Unit_model', '', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title, $api, $acl;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    public function index()
    {
       if ($this->acl->otentikasi1($this->title) == TRUE){ 
         $result = $this->Unit_model->get_last($this->modul['limit'])->result(); 
	 foreach($result as $res)
	 {
           $this->output[] = array ("id" => $res->id, "code" => $res->code, "name" => $res->name, "desc"=> $res->desc);
	 }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);  
    }
    
    function delete_all()
    {
      if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
      
      $cek = $this->input->post('cek');
      $jumlah = count($cek);

      if($cek)
      {
        $jumlah = count($cek);
        $x = 0;
        for ($i=0; $i<$jumlah; $i++)
        {
           if ( $this->cek_relation($cek[$i]) == TRUE ) 
           {
              $this->Unit_model->delete($cek[$i]); 
           }
           else { $x=$x+1; }
           
        }
        $res = intval($jumlah-$x);
        //$this->session->set_flashdata('message', "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!");
        $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
        echo 'true|'.$mess;
      }
      else
      { //$this->session->set_flashdata('message', "No $this->title Selected..!!"); 
        $mess = "No $this->title Selected..!!";
        echo 'false|'.$mess;
      }
      }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }

    function delete($uid,$type='soft')
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->Unit_model->valid_add_trans($uid, $this->title) == TRUE){ 
          if ($this->Unit_model->force_delete($uid) == true){ $this->error = "$this->title successfully removed..!"; }else{ $this->reject('Failed to delete'); }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status); 
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tcode', 'Name', 'required|callback_valid_unit');
        $this->form_validation->set_rules('tname', 'Name', 'required');
        $this->form_validation->set_rules('tdesc', 'Desc', '');

        if ($this->form_validation->run($this) == TRUE)
        {
            $unit = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                          'desc' => $this->input->post('tdesc'), 'created' => date('Y-m-d H:i:s'));

            if ($this->Unit_model->add($unit) == true){ $this->error = $this->title.' successfully saved..!'; }else{ $this->reject('Failed to post'); }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }

    function get($uid=null)
    {        
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->Unit_model->valid_add_trans($uid, $this->title) == TRUE){  
         $unit = $this->Unit_model->get_by_id($uid)->row();
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $unit), $this->status); 
    }

    public function valid_unit($name)
    {
        if ($this->Unit_model->valid('code',$name) == FALSE)
        {
            $this->form_validation->set_message('valid_unit', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_unit($name,$id)
    {
	if ($this->Unit_model->validating('code',$name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_unit', 'This unit is already registered!');
            return FALSE;
        }
        else { return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->Unit_model->valid_add_trans($uid, $this->title) == TRUE){ 

	// Form validation
        $this->form_validation->set_rules('tcode', 'Name', 'required|callback_validation_unit['.$uid.']');
        $this->form_validation->set_rules('tname', 'Name', 'required');
        $this->form_validation->set_rules('tdesc', 'Desc', '');

        if ($this->form_validation->run($this) == TRUE)
        {
            $unit = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                          'desc' => $this->input->post('tdesc'));
            if ($this->Unit_model->update($uid, $unit) == true){ $this->error = 'Data posted'; }else{ $this->reject('Failed to post'); }
        }
        else{ $this->reject(validation_errors()); }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status); 
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); }

}

?>