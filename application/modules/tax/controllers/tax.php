<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tax extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Tax_model', 'model', TRUE);

        $this->properti = $this->property->get();
//        $this->acl->otentikasi();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
                
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title, $acl, $api;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $result = $this->model->get_last($this->modul['limit'])->result(); 
        
	foreach($result as $res)
	{ $this->output[] = array ("id"=>$res->id, "code"=>$res->code, "name"=>$res->name, "value" => floatval($res->value)); }
        
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }

    function delete($uid,$type='hard')
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        if ($type == 'soft'){
           if ($this->model->delete($uid) == true){ $this->error = "$this->title successfully soft removed..!"; }else{
              $this->error = 'Failed to delete'; $this->status = 401;
           }
       }
       else{  $this->model->force_delete($uid);  $this->error = "$this->title successfully removed..!"; }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tcode', 'Name', 'required|callback_valid_tax');
        $this->form_validation->set_rules('tname', 'Name', 'required');
        $this->form_validation->set_rules('tvalue', 'Value', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $tax = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                         'value' => floatval($this->input->post('tvalue')/100), 'created' => date('Y-m-d H:i:s'));

            if ( $this->model->add($tax) == true){ $this->error = $this->title.' successfully saved..!'; }else{ $this->error = 'Failure to posted';$this->status = 401;  }             
        }
        else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get($uid=null)
    {        
      if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        $tax = $this->model->get_by_id($uid)->row();
                
      }else{ $this->reject_token(); }
      $this->api->response(array('error' => $this->error, 'content' => $tax), $this->status); 
    }


    public function valid_tax($name)
    {
        if ($this->model->valid('code',$name) == FALSE)
        {
            $this->form_validation->set_message('valid_tax', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_tax($name,$id)
    {
	if ($this->model->validating('code',$name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_tax', 'This tax is already registered!');
            return FALSE;
        }
        else { return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){

            // Form validation
            $this->form_validation->set_rules('tcode', 'Name', 'required|callback_validation_tax['.$uid.']');
            $this->form_validation->set_rules('tname', 'Name', 'required');
            $this->form_validation->set_rules('tvalue', 'Value', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {
                $tax = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                             'value' => floatval($this->input->post('tvalue')/100));
                
                if ($this->model->update($uid, $tax) == true){ $this->error = 'Data successfully saved..'; }else{ $this->error = 'Failed to posted'; $this->status = 401; }
            }
            else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }
    
}

?>