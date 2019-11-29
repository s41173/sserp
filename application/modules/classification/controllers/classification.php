<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Classification extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Classification_model', 'model', TRUE);
        $this->load->model('Account_model', 'am', TRUE);

        $this->properti = $this->property->get();
        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->account = new Account_lib();
        $this->period = new Period_lib();
        $this->balance = new Balance_account_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');    
    }

    private $properti, $modul, $title;
    private $account, $balance, $period;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    public function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
            $result = $this->model->get_last($this->modul['limit'])->result();
            if ($result){
             foreach($result as $res){ 
                 $this->output[] = array ("id" => $res->id, "no" => $res->no, "name" => $res->name, "type" => $res->type, "status" => $res->status); 
             } 
            }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);
    }
    
    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){ 
        if ($uid){
            $val = $this->model->get_by_id($uid)->row();
            if ($val->status == 0){ $lng = array('status' => 1); }else { $lng = array('status' => 0); }
            $this->model->update($uid,$lng); $this->error = 'true|Status Changed...!';
        }else{ $this->error = 'Parameter Required'; $this->status = 401; }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);
    }
    
    function delete_all()
    {
      if ($this->acl->otentikasi_admin() == TRUE){
      
        $cek = $this->input->post('cek');
        $jumlah = count($cek);

        if($cek)
        {
          $jumlah = count($cek);
          $x = 0;
          for ($i=0; $i<$jumlah; $i++)
          {
             $this->model->force_delete($cek[$i]);
             $x=$x+1;
          }
          $res = intval($jumlah-$x);
          //$this->session->set_flashdata('message', "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!");
          $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
          $this->error = $mess;
        }
        else
        { //$this->session->set_flashdata('message', "No $this->title Selected..!!"); 
          $mess = "No $this->title Selected..!!";
          $this->error = $mess; $this->status = 401;
        }
      }else{ $this->reject_token(); }
      $this->api->response(array('error' => $this->error), $this->status);
    }

    function delete($uid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && isset($uid)){
            if ( $this->cek_relation($uid) == TRUE && $this->cek_status($uid) == TRUE)
            {
              $this->model->force_delete($uid);
              $this->error = $this->title."successfully removed..!"; $this->status = 401;
            }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);
    }
    
    private function cek_status($id)
    {
        $val = $this->model->get_by_id($id)->row();
        if ($val->status == 1){ return FALSE; } else { return TRUE; }
    }

    private function cek_relation($id)
    {
        return $this->account->cek_classi($id);
    }

    function add()
    {
        if ($this->acl->otentikasi3($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tcode', 'Code', 'required|callback_valid_classification');
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_valid_name');
        $this->form_validation->set_rules('ctype', 'Acc Type', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {   
            $value = array('name' => strtoupper($this->input->post('tname')), 'no' => $this->input->post('tcode'),
                           'type' => $this->input->post('ctype'), 'created' => date('Y-m-d H:i:s'));
            
            $this->model->add($value);
            $this->error = $this->title.' successfully saved..!';
        }
        else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status);
    }
    
    function balance($uid=0,$cur='IDR',$laba=null)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE && isset($uid)){
        
        $month = $this->period->get('month');
        $year = $this->period->get('year');
        $trans = 0;
        $bl = $this->balance->get_balance_by_cla($cur,$uid, $month, $year);
        $trans = $this->am->get_balance_by_classification($cur,$uid,$month,$year,$month,$year);
        $this->output = floatval($bl->beginning+$trans);
//        if ($laba){ return floatval($bl->end); }else{ return floatval($bl->beginning+$trans); }
        }else{ $this->reject_token(); }
        $this->response('c');
    }

    function get($uid)
    {   
       if ($this->api->otentikasi() == TRUE){ 
        if($uid){ $val = $this->model->get_by_id($uid)->row(); }else{ $this->error = 'Parameter Required'; $this->status = 401; }
       }else { $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $val), $this->status);
    }

    public function valid_classification($val)
    {   
        if ($this->model->valid('no',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_classification'," $this->title no registered..!");
            return FALSE;
        }
        else{ return TRUE; }   
    }

    public function valid_name($val)
    {
        if ($this->model->valid('name',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_name'," $this->title name registered..!");
            return FALSE;
        }
        else{ return TRUE; }   
    }

    function validation_classification($val,$id)
    {   
	if ($this->model->validating('no',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_classification', "Classification registered!");
            return FALSE;
        }
        else{ return TRUE; }   
    }

    function validation_name($val,$id)
    {
	if ($this->model->validating('name',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_name', "Classification name registered!");
            return FALSE;
        }
        else{ return TRUE; }   
    }

    function update($uid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE){

        $this->form_validation->set_rules('tcode', 'Code', 'required|callback_validation_classification['.$uid.']');
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_validation_name['.$uid.']');
        $this->form_validation->set_rules('ctype', 'Acc Type', 'required');

        if ($this->form_validation->run($this) == TRUE && isset($uid))
        {
            $value = array('name' => strtoupper($this->input->post('tname')), 'no' => $this->input->post('tcode'),
                           'type' => $this->input->post('ctype'));
            
            $this->model->update($uid, $value);
            $this->error = 'Data successfully saved..!'; $this->status = 401;
        }
        else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status);
    }
    
    // ====================================== CLOSING ====================================== 
   function reset_process(){ $this->model->closing(); }

}

?>