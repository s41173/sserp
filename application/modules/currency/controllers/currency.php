<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Currency extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Currency_model', 'model', TRUE);

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
        $result = $this->model->get_last($this->modul['limit'])->result(); 
	foreach($result as $res){ $this->output[] = array ("id" => $res->id, "code" => $res->code, "name" => $res->name); }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);       
    }
    
    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){ 
       $val = $this->model->get_by_id($uid)->row();
       if ($val->publish == 0){ $lng = array('publish' => 1); }else { $lng = array('publish' => 0); }
       $this->model->update($uid,$lng);
       echo 'true|Status Changed...!';
       }else{ echo "error|Sorry, you do not have the right to change publish status..!"; }
    }
    
    function defaults($uid = null)
    {        
       if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){ 
           
        $val = $this->model->get_default()->row();
        $lng = array('defaults' => 0);
        $this->model->update($val->id,$lng);

        $lng = array('defaults' => 1);
        $this->model->update($uid,$lng);  
        echo 'true|Defaults Changed..!';
           
       }else{ echo "error|Sorry, you do not have the right to change publish status..!"; }
    }

    function delete($uid,$type='hard')
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
       if ($type == 'soft'){
           if ($this->model->delete($uid) == true){ $this->error = "$this->title successfully removed..!"; }else{ $this->reject('Failed to delete'); }
       }
       else{ if ($this->model->force_delete($uid) == true){ $this->error = "$this->title successfully removed..!"; }else{ $this->reject('Failed to delete'); } }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status); 
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

            // Form validation
            $this->form_validation->set_rules('tcode', 'Name', 'required|callback_valid_currency');
            $this->form_validation->set_rules('tname', 'Name', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {
                $currency = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'), 'created' => date('Y-m-d H:i:s'));
                if ($this->model->add($currency) == true){ $this->error = $this->title.' successfully saved'; }else{ $this->reject('Failed to post'); }
            }
            else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get($uid=null)
    {        
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
            $currency = $this->model->get_by_id($uid)->row();
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $currency), $this->status); 
    }


    public function valid_currency($name)
    {
        if ($this->model->valid('code',$name) == FALSE)
        {
            $this->form_validation->set_message('valid_currency', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_currency($name,$id)
    {
	if ($this->model->validating('code',$name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_currency', 'This currency is already registered!');
            return FALSE;
        }
        else { return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 

	// Form validation
        $this->form_validation->set_rules('tcode', 'Name', 'required|callback_validation_currency['.$uid.']');
        $this->form_validation->set_rules('tname', 'Name', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $currency = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'));
            if ($this->model->update($uid, $currency) == true){ $this->error = 'Data successfully saved..'; }else{ $this->reject('Failed to post'); }
        }
        else{ $this->reject(validation_errors()); }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status); 
    }

}

?>