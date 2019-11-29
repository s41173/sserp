<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Journaltype extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Journal_model', 'model', TRUE);
        
        $this->properti = $this->property->get();
        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->classification = new Classification_lib();
        $this->account = new Account_lib();
        $this->component = new Components();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $account, $component;
    private $currency,$classification,$api,$acl;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    public function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
            $result = $this->model->get_last($this->modul['limit'])->result();
            foreach($result as $res)
            {  
               $this->output[] = array ("id" => $res->id, "code" => $res->code, "name" => $res->name, 
                                        "desc" => $res->desc, "removed" => $res->removed);
            }
        
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }    
    
    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

            // Form validation
            $this->form_validation->set_rules('tcode', 'Name', 'required|callback_valid_code');
            $this->form_validation->set_rules('tname', 'Account Code', 'required');
            $this->form_validation->set_rules('tdesc', 'Description', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {
                $account = array('code' => $this->input->post('tcode'), 'desc' => $this->input->post('tdesc'),
                                 'name' => $this->input->post('tname'));

                if ($this->model->add($account) == true){ $this->error = $this->title.' successfully saved..!';}
                else{ $this->error = 'Failure to save data'; $this->status = 401; }
            }
            else{ $this->error = validation_errors(); $this->status = 401; }
        }else { $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status);
    }

    function get($uid=null)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE && isset($uid)){
            $control = $this->model->get_by_id($uid)->row();
        }else { $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $control), $this->status);
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && isset($uid)){
            // Form validation
            $this->form_validation->set_rules('tcode', 'Name', 'required|callback_validating_code['.$uid.']');
            $this->form_validation->set_rules('tname', 'Account Code', 'required');
            $this->form_validation->set_rules('tdesc', 'Description', 'required');
            if ($this->form_validation->run($this) == TRUE)
            {   
                $account = array('code' => $this->input->post('tcode'), 'desc' => $this->input->post('tdesc'),
                                 'name' => $this->input->post('tname'));

                if ($this->model->update($uid, $account) == true){
                  $this->error = 'Data successfully saved..!';    
                }else{ $this->error = 'Failure to save data'; $this->status = 401; }
            }
            else{ $this->error = 'error|'.validation_errors(); $this->status = 401; }
        }else { $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status);
    }
    
    function delete($uid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && isset($uid)){
            if ($this->cek_status($uid) == TRUE)
            {
               $this->model->force_delete($uid);
               $this->error = "$this->title successfully soft removed..!";
            }
            else { $this->error = 'Default control account can not removed..!'; $this->status = 403; }
        }else { $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status);
    }
    
    function delete_all()
    {
      if ($this->acl->otentikasi3($this->title) == TRUE){
      
      $cek = $this->input->post('cek');
      $jumlah = count($cek);

      if($cek)
      {
        $jumlah = count($cek);
        $x = 0;
        for ($i=0; $i<$jumlah; $i++)
        {
           if ( $this->cek_status($cek[$i]) == TRUE ) 
           {
              $this->model->force_delete($cek[$i]); 
           }
           else { $x=$x+1; }  
        }
        $res = intval($jumlah-$x);
        $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
        $this->error = $mess;
      }
      else
      { $mess = "No $this->title Selected..!!"; $this->error = $mess; $this->status = 403; }
      }else { $this->reject_token(); }
      $this->api->response(array('error' => $this->error), $this->status);
    }
    
    private function cek_status($id)
    {
        $res = $this->model->get_by_id($id)->row();
        if ($res->removed == 0){ return TRUE; } else { return FALSE; }
    }


    public function valid_code($code)
    {        
        if ($this->model->valid('code',$code) == FALSE)
        {
            $this->form_validation->set_message('valid_code', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }   
    }

    public function validating_code($code,$id)
    {   
	if ($this->model->validating('code',$code,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_code', 'This '.$this->title.' is already registered!');
            return FALSE;
        }
        else { return TRUE; }  
    }
    
   // ====================================== CLOSING ====================================== 
   function reset_process(){ $this->model->closing(); }

}

?>