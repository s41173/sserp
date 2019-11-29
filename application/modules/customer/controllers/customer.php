<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Customer_model', 'model', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->role = new Role_lib();
        $this->city = new City_lib();
        $this->disctrict = new District_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $customer, $city, $disctrict;
    private $role, $api, $acl;

    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        
            $datax = (array)json_decode(file_get_contents('php://input')); 
            if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
            if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
            
            $city = null; $publlish = null;
            if (isset($datax['city'])){ $city = $datax['city']; }
            if (isset($datax['publish'])){ $publlish = $datax['publish']; }
            if($city == null & $publlish == null){ $result = $this->model->get_last($this->limitx, $this->offsetx)->result(); }
            else {$result = $this->model->search($city,$publlish)->result(); }    

            foreach($result as $res)
            {   
               $this->output[] = array ("id" => $res->id, "name" => $res->first_name.' '.$res->last_name, "type" => $res->type, "address" => $res->address, "ship_address" => $res->shipping_address, 
                                        "phone1" => $res->phone1, "phone2" => $res->phone2, "fax" => $res->fax, "email" => $res->email, "website" => $res->website, "city" => $this->city->get_name($res->city),
                                        "region" => $res->region, "zip" => $res->zip, "notes" => $res->notes, "status" => $res->status, "joined"=>tglin($res->joined)
                                       );
            }  
        
        }else{ $this->reject_token(); }
        $this->response('c');
    }

    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
         $val = $this->model->get_by_id($uid)->row();
         if ($val->status == 0){ $lng = array('status' => 1); }else { $lng = array('status' => 0); }
         $this->model->update($uid,$lng);
         $this->error = 'Status Changed...!';
       }else{ $this->reject_token(); }
       $this->response();
    }
    
    function delete_all($type='soft')
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
             if ($type == 'soft') { $this->model->delete($cek[$i]); }
             else { $this->remove_img($cek[$i],'force');
                    $this->attribute_customer->force_delete_by_customer($cek[$i]);
                    $this->model->force_delete($cek[$i]);  }
             $x=$x+1;
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
      }else{ echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
      
    }

    function delete($uid)
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
         if ($this->model->delete($uid) == true){ $this->error = $this->title.' successfully removed..!'; }else{ $this->reject(); }
       }else { $this->reject_token(); }
       $this->response();
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'category_view';
	$data['form_action'] = site_url($this->title.'/add_process');
	$data['link'] = array('link_back' => anchor('category/','<span>back</span>', array('class' => 'back')));

	// Form validation
        $this->form_validation->set_rules('tfname', 'First Name', 'required');
        $this->form_validation->set_rules('tlname', 'Name', 'required');
        $this->form_validation->set_rules('ctype', 'Customer Type', 'required');
        $this->form_validation->set_rules('taddress', 'Address', 'required');
        $this->form_validation->set_rules('tphone1', 'Phone 1', 'required');
        $this->form_validation->set_rules('tphone2', 'Phone 2', '');
        $this->form_validation->set_rules('temail', 'Email', 'required|valid_email|callback_valid_email');
        $this->form_validation->set_rules('twebsite', 'Website', '');
        $this->form_validation->set_rules('ccity', 'City', 'required');
        $this->form_validation->set_rules('cdistrict', 'District', 'required');
        $this->form_validation->set_rules('tzip', 'Zip', '');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = $this->properti['url_upload'].'/images/customer/';
            $config['file_name'] = split_space($this->input->post('tfname').'_'.waktuindo());
            $config['allowed_types'] = 'jpg|gif|png';
            $config['overwrite'] = true;
            $config['max_size']	= '10000';
            $config['max_width']  = '30000';
            $config['max_height']  = '30000';
            $config['remove_spaces'] = TRUE;

            $this->load->library('upload', $config);
//
            if ( !$this->upload->do_upload("userfile")) // if upload failure
            {
                $info['file_name'] = null;
                $data['error'] = $this->upload->display_errors();
                $customer = array('first_name' => strtolower($this->input->post('tfname')), 
                                  'last_name' => strtolower($this->input->post('tlname')),
                                  'type' => $this->input->post('ctype'), 'address' => $this->input->post('taddress'),
                                  'shipping_address' => $this->input->post('taddress'), 'phone1' => $this->input->post('tphone1'), 'phone2' => $this->input->post('tphone2'),
                                  'email' => $this->input->post('temail'), 'password' => 'password', 
                                  'website' => $this->input->post('twebsite'), 'region' => $this->input->post('cdistrict'),
                                  'city' => $this->input->post('ccity'), 'state' => $this->city->get_province_based_city($this->input->post('ccity')),
                                  'zip' => $this->input->post('tzip'), 'joined' => date('Y-m-d H:i:s'),
                                  'image' => null, 'created' => date('Y-m-d H:i:s'));
            }
            else
            {
                $info = $this->upload->data();
                
                $customer = array('first_name' => strtolower($this->input->post('tfname')), 
                                  'last_name' => strtolower($this->input->post('tlname')),
                                  'type' => $this->input->post('ctype'), 'address' => $this->input->post('taddress'),
                                  'shipping_address' => $this->input->post('taddress'), 'phone1' => $this->input->post('tphone1'), 'phone2' => $this->input->post('tphone2'),
                                  'email' => $this->input->post('temail'), 'password' => 'password', 
                                  'website' => $this->input->post('twebsite'), 'region' => $this->input->post('cdistrict'),
                                  'city' => $this->input->post('ccity'), 'state' => $this->city->get_province_based_city($this->input->post('ccity')),
                                  'zip' => $this->input->post('tzip'), 'joined' => date('Y-m-d H:i:s'),
                                  'image' => $info['file_name'], 'created' => date('Y-m-d H:i:s'));
            }

            if ($this->model->add($customer) != true && $this->upload->display_errors()){ 
                $this->error = $this->upload->display_errors(); $this->status = 401;
            }else{ $this->error = $this->title.' successfully saved..!'; }            
        }
        else{ $this->reject(validation_errors()); }
        }else { $this->reject_token();  }
        $this->response('c');
    }
    
    private function cek_tick($val)
    {
        if (!$val)
        { return 0;} else { return 1; }
    }
    
    private function split_array($val)
    { return implode(",",$val); }
   

    function get($uid=null)
    {        
        if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 

//        $data['city'] = $this->city->combo_city_db();
//        $data['combo_district'] = $this->disctrict->combo_district_db(null);
        
        $customer = $this->model->get_by_id($uid)->row();
        
        $data['fname'] = $customer->first_name;
        $data['lname'] = $customer->last_name;
        $data['type'] = $customer->type;
        $data['address'] = $customer->address;
        $data['shipping'] = $customer->shipping_address;
        $data['phone1'] = $customer->phone1;
        $data['phone2'] = $customer->phone2;
        $data['email'] = $customer->email;
        $data['password'] = $customer->password;
        $data['website'] = $customer->website;
        $data['city'] = $customer->city;
        $data['district'] = $customer->region;
        $data['zip'] = $customer->zip;
        $data['image'] = $this->properti['image_url'].'customer/'.$customer->image;
        $this->output = $data;
        }else { $this->reject_token(); }
        $this->response('c');
    }
    
    function valid_email($val)
    {
        if ($this->model->valid('email',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_email','Email registered..!');
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validating_email($val,$id)
    {
	if ($this->model->validating('email',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_email', "Email registered!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    // Fungsi update untuk mengupdate db
    function update($uid=0)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){

        $this->form_validation->set_rules('tfname', 'SKU', 'required');
        $this->form_validation->set_rules('tlname', 'Name', 'required');
        $this->form_validation->set_rules('ctype', 'Customer Type', 'required');
        $this->form_validation->set_rules('taddress', 'Address', 'required');
        $this->form_validation->set_rules('tphone1', 'Phone 1', 'required');
        $this->form_validation->set_rules('tphone2', 'Phone 2', '');
        $this->form_validation->set_rules('temail', 'Email', 'required|valid_email|callback_validating_email['.$uid.']');
        $this->form_validation->set_rules('twebsite', 'Website', '');
        $this->form_validation->set_rules('ccity', 'City', 'required');
        $this->form_validation->set_rules('cdistrict', 'District', 'required');
        $this->form_validation->set_rules('tzip', 'Zip', '');
            
        if ($this->form_validation->run($this) == TRUE)
        {
            // start update 1
            $config['upload_path'] = $this->properti['url_upload'].'/images/customer/';
            $config['file_name'] = split_space($this->input->post('tfname').'_'.waktuindo());
            $config['allowed_types'] = 'jpg|gif|png';
            $config['overwrite'] = true;
            $config['max_size']	= '10000';
            $config['max_width']  = '30000';
            $config['max_height']  = '30000';
            $config['remove_spaces'] = TRUE;

            $this->load->library('upload', $config);

            if ( !$this->upload->do_upload("userfile")) // if upload failure
            {
                $info['file_name'] = null;
                $data['error'] = $this->upload->display_errors();

                $customer = array('first_name' => strtolower($this->input->post('tfname')), 
                              'last_name' => strtolower($this->input->post('tlname')),
                              'type' => $this->input->post('ctype'), 'address' => $this->input->post('taddress'),
                              'shipping_address' => $this->input->post('tshipping'), 'phone1' => $this->input->post('tphone1'), 'phone2' => $this->input->post('tphone2'),
                              'email' => $this->input->post('temail'), 'password' => 'password', 
                              'website' => $this->input->post('twebsite'), 'region' => $this->input->post('cdistrict'),
                              'city' => $this->input->post('ccity'), 'state' => $this->city->get_province_based_city($this->input->post('ccity')),
                              'zip' => $this->input->post('tzip'));
            }
            else
            {
                $info = $this->upload->data();
                $customer = array('first_name' => strtolower($this->input->post('tfname')), 
                              'last_name' => strtolower($this->input->post('tlname')),
                              'type' => $this->input->post('ctype'), 'address' => $this->input->post('taddress'),
                              'shipping_address' => $this->input->post('tshipping'), 'phone1' => $this->input->post('tphone1'), 'phone2' => $this->input->post('tphone2'),
                              'email' => $this->input->post('temail'), 'password' => 'password', 
                              'website' => $this->input->post('twebsite'), 'region' => $this->input->post('cdistrict'),
                              'city' => $this->input->post('ccity'), 'state' => $this->city->get_province_based_city($this->input->post('ccity')),
                              'zip' => $this->input->post('tzip'), 'image' => $info['file_name']);
            }            
            if ($this->model->update($uid, $customer) != true && $this->upload->display_errors()){ 
                $this->reject($this->upload->display_errors());
            }else{ $this->error = 'Transaction Posted'; }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response('c');
    }
    
    function ajaxcombo_district()
    {
        $cityid = $this->input->post('value');
        if ($cityid != null){
            $district = $this->disctrict->combo_district_db($cityid);
            $js = "class='select2_single form-control' id='cdistrict' tabindex='-1' style='width:100%;' "; 
            echo form_dropdown('cdistrict', $district, isset($default['district']) ? $default['district'] : '', $js);
        }
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); } 
   

}

?>