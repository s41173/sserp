<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Payment_model', 'model', TRUE);

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

    
    public function index($search=null)
    {
       if ($this->acl->otentikasi1($this->title) == TRUE){ 
        $result = $this->model->get_last($this->modul['limit'])->result(); 
	foreach($result as $res){
            $this->output[] = array ("id" => $res->id, "name" => $res->name, "image" => base_url().'images/payment/'.$res->image,
                                     "order"=>$res->orders, "acc_no"=>$res->acc_no, "acc_name"=>$res->acc_name 
                                    ); 
        }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);   
    }
    
    function defaults($uid = null)
    {        
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
           
        $val = $this->model->get_default()->row();
        $lng = array('defaults' => 0);
        $this->model->update($val->id,$lng);

        $lng = array('defaults' => 1);
        $this->model->update($uid,$lng);  
        $this->error = 'Defaults Changed..!'; 
           
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $currency), $this->status); 
    }

    function delete($uid,$type='soft')
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
         if ($this->model->delete($uid) == true){ $this->error = "$this->title successfully soft removed..!"; }else{ $this->reject('Failed to deleted');} 

       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status);
    }

    private function cek_relation($id)
    {
        $product = $this->product->cek_relation($id, $this->title);
        if ($product == TRUE) { return TRUE; } else { return FALSE; }
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_valid_payment');
        $this->form_validation->set_rules('torder', 'Order', 'required|numeric');
        $this->form_validation->set_rules('taccno', 'Account No', '');
        $this->form_validation->set_rules('taccname', 'Account Name', '');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = './images/payment/';
            $config['file_name'] = split_space($this->input->post('tname'));
            $config['allowed_types'] = 'jpg|gif|png';
            $config['overwrite'] = true;
            $config['max_size']	= '10000';
            $config['max_width']  = '10000';
            $config['max_height']  = '10000';
            $config['remove_spaces'] = TRUE;

            $this->load->library('upload', $config);
//
            if ( !$this->upload->do_upload("userfile")) // if upload failure
            {
                $info['file_name'] = null;
                $data['error'] = $this->upload->display_errors();
                $payment = array('name' => strtolower($this->input->post('tname')),
                                 'orders' => $this->input->post('torder'), 
                                 'acc_no' => $this->input->post('taccno'), 
                                 'acc_name' => $this->input->post('taccname'), 
                                 'pos' => $this->input->post('cpos'), 
                                 'image' => null, 'created' => date('Y-m-d H:i:s'));
            }
            else
            {
                $info = $this->upload->data();
                $payment = array('name' => strtolower($this->input->post('tname')), 
                                 'orders' => $this->input->post('torder'), 
                                 'acc_no' => $this->input->post('taccno'), 
                                 'acc_name' => $this->input->post('taccname'), 
                                 'pos' => $this->input->post('cpos'), 
                                 'image' => $info['file_name'], 'created' => date('Y-m-d H:i:s'));
            }
            
            if ($this->model->add($payment) != true && $this->upload->display_errors()){ $this->error = $this->upload->display_errors(); $this->status = 401;
            }else{ $this->error = $this->title.' successfully saved..!'; }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 

    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get($uid=null)
    {        
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
        $res = $this->model->get_by_id($uid)->row();
        $this->output = array ("id" => $res->id, "name" => $res->name, "image" => base_url().'images/payment/'.$res->image,
                               "order"=>$res->orders, "acc_no"=>$res->acc_no, "acc_no"=>$res->acc_name, "pos"=>$res->pos, "default"=>$res->defaults 
                              ); 
       
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }


    public function valid_payment($name)
    {
        if ($this->model->valid('name',$name) == FALSE)
        {
            $this->form_validation->set_message('valid', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_payment($name,$id)
    {
	if ($this->model->validating('name',$name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation', 'This payment is already registered!');
            return FALSE;
        }
        else { return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tname', 'Name', 'required|max_length[100]|callback_validation_payment['.$uid.']');
        $this->form_validation->set_rules('torder', 'Order', 'required|numeric');
        $this->form_validation->set_rules('taccno', 'Account No', '');
        $this->form_validation->set_rules('taccname', 'Account Name', '');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = './images/payment/';
            $config['file_name'] = split_space($this->input->post('tname'));
            $config['allowed_types'] = 'gif|jpg|png';
            $config['overwrite'] = true;
            $config['max_size']	= '10000';
            $config['max_width']  = '10000';
            $config['max_height']  = '10000';
            $config['remove_spaces'] = TRUE;

            $this->load->library('upload', $config);

            if ( !$this->upload->do_upload("userfile")) // if upload failure
            {
                $data['error'] = $this->upload->display_errors();
                
                $payment = array('name' => strtolower($this->input->post('tname')),
                                 'orders' => $this->input->post('torder'), 
                                 'acc_no' => $this->input->post('taccno'), 
                                 'pos' => $this->input->post('cpos'), 
                                 'acc_name' => $this->input->post('taccname'));
                
                $img = null;
            }
            else
            {
                $info = $this->upload->data();
                $payment = array('name' => strtolower($this->input->post('tname')),
                                 'orders' => $this->input->post('torder'), 
                                 'acc_no' => $this->input->post('taccno'), 
                                 'acc_name' => $this->input->post('taccname'), 
                                 'pos' => $this->input->post('cpos'), 
                                 'image' => $info['file_name']);
                
                $img = base_url().'images/payment/'.$info['file_name'];
            }
            
            if ($this->model->update($uid, $payment) != true && $this->upload->display_errors()){ $this->reject($this->upload->display_errors());
            }else{ $this->error = $this->title.' successfully saved..!'; }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
    }
    
    function remove_image($uid)
    {
       $img = $this->model->get_payment_by_id($uid)->row();
       $img = $img->image;
       if ($img){ $img = "./images/payment/".$img; unlink("$img"); } 
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); }

}

?>