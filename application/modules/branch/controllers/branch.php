<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Branch extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Branch_model', 'model', TRUE);

        $this->properti = $this->property->get();
        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->product = new Product_lib();
        $this->city = new City_lib();
        $this->conversion = new Conversion_lib();
        $this->account = new Account_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title;
    private $city,$conversion,$account;
    protected $error = null;
    protected $status = 200;
    protected $output = null;

    function index()
    {
       if ($this->acl->otentikasi1($this->title) == TRUE){
           
           $result = $this->model->get_last($this->modul['limit'])->result();
           foreach($result as $res)
           {
               $this->output[] = array ("id" => $res->id, "code" => $res->code, "name" => $res->name, "address" => $res->address, "phone" => $res->phone, 
                                        "mobile" => $res->mobile, "email" => $res->email, "city" => $res->city, "zip" => $res->zip, "logo" => $res->image, 
                                        "publish" => $res->publish, "default" => $res->defaults, "sales_coa" => $res->sales_account, "stock_coa" => $res->stock_account);
           }
       }
       else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);
    }
    
    
    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
            $val = $this->model->get_by_id($uid)->row();
            if ($val->publish == 0){ $lng = array('publish' => 1); }else { $lng = array('publish' => 0); }
            if ($this->model->update($uid,$lng) == true){ $this->error = 'Status Changed...!'; }else{ $this->error = 'Failed to posted'; $this->status = 401; }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status);
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
       $this->api->response(array('error' => $this->error), $this->status);
    }
    

    function delete($uid,$type='soft')
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        if ($type == 'soft'){
           if ($this->model->delete($uid) == true){ $this->error = "$this->title successfully soft removed..!"; }else{ $this->reject('Failed to deleted');}           
       }
       else
       {
            if ( $this->cek_relation($uid) == TRUE )
            {
               $img = $this->model->get_by_id($uid)->row();
               $img = $img->image;
               if ($img){ $img = "./images/branch/".$img; unlink("$img"); }
               if ($this->model->force_delete($uid) == true){ $this->error = "$this->title successfully soft removed..!"; }else{ $this->reject('Failed to deleted');}
            }
            else { $this->reject("$this->title related to another component..!"); } 
       }
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
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){
        
	// Form validation
        $this->form_validation->set_rules('tcode', 'Name', 'required|callback_valid_branch');
        $this->form_validation->set_rules('tname', 'Name', 'required');
        $this->form_validation->set_rules('tmail', 'Email', '');
        $this->form_validation->set_rules('tphone', 'Phone', '');
        $this->form_validation->set_rules('tmobile', 'Mobile', '');
        $this->form_validation->set_rules('ccity', 'Mobile', 'required');
        $this->form_validation->set_rules('tzip', 'Zip', '');
        $this->form_validation->set_rules('taddress', 'Address', '');
        $this->form_validation->set_rules('tsalesacc', 'Sales Account', 'required');
        $this->form_validation->set_rules('tstockacc', 'Stock Account', 'required');
        $this->form_validation->set_rules('tunitacc', 'Unit Cost Account', 'required');
        $this->form_validation->set_rules('taracc', 'AR-Account', 'required');
        $this->form_validation->set_rules('tbankacc', 'Bank-Account', 'required');
        $this->form_validation->set_rules('tcashacc', 'Cash-Account', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = './images/branch/';
            $config['file_name'] = strtolower($this->input->post('tname'));
            $config['allowed_types'] = 'jpg|gif|png';
            $config['overwrite'] = true;
            $config['max_size']	= '1000';
            $config['max_width']  = '3000';
            $config['max_height']  = '3000';
            $config['remove_spaces'] = TRUE;

            $this->load->library('upload', $config);
//
            if ( !$this->upload->do_upload("userfile")) // if upload failure
            {
                $info['file_name'] = null;
                $data['error'] = $this->upload->display_errors();
                $branch = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                                'address' => $this->input->post('taddress'),
                                'phone' => $this->input->post('tphone'), 'mobile' => $this->input->post('tmobile'),
                                'email' => $this->input->post('tmail'), 'city' => $this->input->post('ccity'),
                                'zip' => $this->input->post('tzip'),
                                'sales_account' => $this->account->get_id_code($this->input->post('tsalesacc')),
                                'stock_account' => $this->account->get_id_code($this->input->post('tstockacc')),
                                'unit_cost_account' => $this->account->get_id_code($this->input->post('tunitacc')),
                                'ar_account' => $this->account->get_id_code($this->input->post('taracc')),
                                'bank_account' => $this->account->get_id_code($this->input->post('tbankacc')),
                                'cash_account' => $this->account->get_id_code($this->input->post('tcashacc')),
                                'image' => null, 'created' => date('Y-m-d H:i:s'));
            }
            else
            {
                $info = $this->upload->data();
                $branch = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                                'address' => $this->input->post('taddress'),
                                'phone' => $this->input->post('tphone'), 'mobile' => $this->input->post('tmobile'),
                                'email' => $this->input->post('tmail'), 'city' => $this->input->post('ccity'),
                                'zip' => $this->input->post('tzip'),
                                'sales_account' => $this->account->get_id_code($this->input->post('tsalesacc')),
                                'stock_account' => $this->account->get_id_code($this->input->post('tstockacc')),
                                'unit_cost_account' => $this->account->get_id_code($this->input->post('tunitacc')),
                                'ar_account' => $this->account->get_id_code($this->input->post('taracc')),
                                'bank_account' => $this->account->get_id_code($this->input->post('tbankacc')),
                                'cash_account' => $this->account->get_id_code($this->input->post('tcashacc')),
                                'image' => base_url().'images/branch/'.$info['file_name'], 'created' => date('Y-m-d H:i:s'));
            }

            if ($this->model->add($branch) != true && $this->upload->display_errors()){ $this->error = $this->upload->display_errors(); $this->status = 401;
            }else{ $this->error = $this->title.' successfully saved..!'; }
            
//            if ($this->upload->display_errors()){ echo "warning|".$this->upload->display_errors(); }
//            else { echo 'true|'.$this->title.' successfully saved..!|'.base_url().'images/branch/'.$info['file_name']; }
            
        }
        else{ $this->error = validation_errors(); $this->status = 401; }
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get($uid=null)
    {       
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        $branch = $this->model->get_by_id($uid)->row();
        
        $data = array("id"=>$uid, "code"=> $branch->code, "address"=>$branch->address, "phone"=>$branch->phone, "mobile"=>$branch->mobile,
                      "mobile"=>$branch->mobile, "email"=>$branch->email,"city"=>$branch->city,"zip"=>$branch->zip,"image"=>$branch->image,
                      "sales_acc"=>$this->account->get_code($branch->sales_account), "stock_acc"=>$this->account->get_code($branch->stock_account),
                      "unit_cost_acc"=>$this->account->get_code($branch->unit_cost_account), "ar_acc"=>$this->account->get_code($branch->ar_account),
                      "bank_acc"=>$this->account->get_code($branch->bank_account), "cash_acc"=>$this->account->get_code($branch->cash_account)
                     );
        
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
    }

    public function valid_branch($name)
    {
        if ($this->model->valid('code',$name) == FALSE)
        {
            $this->form_validation->set_message('valid_branch', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_branch($name,$id) 
    {
	if ($this->model->validating('code',$name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation_branch', 'This branch is already registered!');
            return FALSE;
        }
        else { return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tcode', 'Name', 'required|callback_validation_branch['.$uid.']');
        $this->form_validation->set_rules('tname', 'Name', 'required');
        $this->form_validation->set_rules('tmail', 'Email', '');
        $this->form_validation->set_rules('tphone', 'Phone', '');
        $this->form_validation->set_rules('tmobile', 'Mobile', '');
        $this->form_validation->set_rules('ccity', 'Mobile', 'required');
        $this->form_validation->set_rules('tzip', 'Zip', '');
        $this->form_validation->set_rules('taddress', 'Address', '');
        $this->form_validation->set_rules('tsalesacc', 'Sales Account', 'required');
        $this->form_validation->set_rules('tstockacc', 'Stock Account', 'required');
        $this->form_validation->set_rules('tunitacc', 'Unit Cost Account', 'required');
        $this->form_validation->set_rules('taracc', 'AR-Account', 'required');
        $this->form_validation->set_rules('tbankacc', 'Bank-Account', 'required');
        $this->form_validation->set_rules('tcashacc', 'Cash-Account', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = './images/branch/';
            $config['file_name'] = strtolower($this->input->post('tname_update'));
            $config['allowed_types'] = 'gif|jpg|png';
            $config['overwrite'] = true;
            $config['max_size']	= '10000';
            $config['max_width']  = '10000';
            $config['max_height']  = '10000';
            $config['remove_spaces'] = TRUE;

            $this->load->library('upload', $config);

            if ( !$this->upload->do_upload("userfile_update")) // if upload failure
            {
                $data['error'] = $this->upload->display_errors();
                $branch = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                                'address' => $this->input->post('taddress'),
                                'phone' => $this->input->post('tphone'), 'mobile' => $this->input->post('tmobile'),
                                'email' => $this->input->post('tmail'), 'city' => $this->input->post('ccity'),
                                'sales_account' => $this->account->get_id_code($this->input->post('tsalesacc')),
                                'stock_account' => $this->account->get_id_code($this->input->post('tstockacc')),
                                'unit_cost_account' => $this->account->get_id_code($this->input->post('tunitacc')),
                                'ar_account' => $this->account->get_id_code($this->input->post('taracc')),
                                'bank_account' => $this->account->get_id_code($this->input->post('tbankacc')),
                                'cash_account' => $this->account->get_id_code($this->input->post('tcashacc')),
                                'zip' => $this->input->post('tzip'));
                
                $img = null;
            }
            else
            {
                $info = $this->upload->data();                
                $branch = array('name' => strtolower($this->input->post('tname')), 'code' => $this->input->post('tcode'),
                                'address' => $this->input->post('taddress'),
                                'phone' => $this->input->post('tphone'), 'mobile' => $this->input->post('tmobile'),
                                'email' => $this->input->post('tmail'), 'city' => $this->input->post('ccity'),
                                'zip' => $this->input->post('tzip'),
                                'sales_account' => $this->account->get_id_code($this->input->post('tsalesacc')),
                                'stock_account' => $this->account->get_id_code($this->input->post('tstockacc')),
                                'unit_cost_account' => $this->account->get_id_code($this->input->post('tunitacc')),
                                'ar_account' => $this->account->get_id_code($this->input->post('taracc')),
                                'bank_account' => $this->account->get_id_code($this->input->post('tbankacc')),
                                'cash_account' => $this->account->get_id_code($this->input->post('tcashacc')),
                                'image' => base_url().'images/branch/'.$info['file_name']);
                
                $img = base_url().'images/branch/'.$info['file_name'];
            }

            if ($this->model->update($uid, $branch) != true && $this->upload->display_errors()){ $this->reject($this->upload->display_errors());
            }else{ $this->error = $this->title.' successfully saved..!'; }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
    }
    
    function remove_image($uid)
    {
       $img = $this->model->get_by_id($uid)->row();
       $img = $img->image;
       if ($img){ $img = "./images/branch/".$img; unlink("$img"); } 
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing_defaults(); }

}

?>