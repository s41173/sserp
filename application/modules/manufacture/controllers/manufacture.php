<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Manufacture extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Manufacture_model', 'model', TRUE);

        $this->properti = $this->property->get();
        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->product = new Product_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title, $acl, $api, $product;
    protected $error = null;
    protected $status = 200;
    protected $output = null;

    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $datax = (array)json_decode(file_get_contents('php://input')); 
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
        
        $result = $this->model->get_last($this->limitx, $this->offsetx)->result();
        $resx = null;
	foreach($result as $res)
	{
           $resx[] = array ("id"=>$res->id, "name"=>$res->name, "orders"=>$res->orders,
                            "image"=>base_url().'images/manufacture/'.$res->image);
	}
        $data['result'] = $resx; $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
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
              $img = $this->model->get_manufacture_by_id($cek[$i])->row();
              $img = $img->image;
              if ($img){ $img = "./images/manufacture/".$img; unlink("$img"); }

              $this->model->delete($cek[$i]); 
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
      if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        if ($type == 'soft'){
           $this->model->delete($uid);
           $this->error = "$this->title successfully soft removed..!";
       }
       else
       {
        if ( $this->cek_relation($uid) == TRUE )
        {
           $img = $this->model->get_by_id($uid)->row();
           $img = $img->image;
           if ($img){ $img = "./images/manufacture/".$img; unlink("$img"); }

           $this->model->delete($uid);
           $this->error = "$this->title successfully removed..!";
        }
        else { $this->reject("$this->title related to another component..!"); } 
       }
      }else{ $this->reject_teok('Invalid Token or Expired..!'); }
      $this->response();
    }

    private function cek_relation($id)
    {
        $product = $this->product->cek_relation($id, $this->title);
        if ($product == TRUE) { return TRUE; } else { return FALSE; }
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

        $data = null;
	// Form validation
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_valid_manufacture');
        $this->form_validation->set_rules('torder', 'Order', 'required|numeric');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = './images/manufacture/';
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
                $manufacture = array('name' => strtolower($this->input->post('tname')),'orders' => $this->input->post('torder'), 
                                     'image' => null, 'created' => date('Y-m-d H:i:s'));
            }
            else
            {
                $info = $this->upload->data();
                $manufacture = array('name' => strtolower($this->input->post('tname')), 'orders' => $this->input->post('torder'), 
                                     'image' => $info['file_name'], 'created' => date('Y-m-d H:i:s'));
            }

            if ($this->model->add($manufacture) != true && $this->upload->display_errors()){ $this->reject($this->upload->display_errors());
            }else{ $this->error = $this->title.' successfully saved..!'; }
            $this->output = $data;
        }
        else{ $this->reject(validation_errors()); }
      }else{ $this->reject_token('Invalid Token or Expired..!'); }
      $this->response('content');

    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function get($uid=null)
    {        
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
        $manufacture = $this->model->get_by_id($uid)->row();
        $data['name'] = $manufacture->name;
        $data['order'] = $manufacture->orders;
        $data['image'] = base_url().'images/manufacture/'.$manufacture->image;
        $this->output = $data;
       }else{ $this->reject_token('Invalid Token or Expired..!'); }
       $this->response('content');
    }


    public function valid_manufacture($name)
    {
        if ($this->model->valid('name',$name) == FALSE)
        {
            $this->form_validation->set_message('valid', "This $this->title is already registered.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validation_manufacture($name,$id)
    {
	if ($this->model->validating('name',$name,$id) == FALSE)
        {
            $this->form_validation->set_message('validation', 'This manufacture is already registered!');
            return FALSE;
        }
        else { return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){

        $data = null;
	// Form validation
        $this->form_validation->set_rules('tname', 'Name', 'required|max_length[100]|callback_validation_manufacture['.$uid.']');
        $this->form_validation->set_rules('torder', 'Order', 'required|numeric');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = './images/manufacture/';
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
                $manufacture = array('name' => strtolower($this->input->post('tname')),'orders' => $this->input->post('torder'));
                $img = null;
            }
            else
            {
                $info = $this->upload->data();
                $manufacture = array('name' => strtolower($this->input->post('tname')),'orders' => $this->input->post('torder'), 'image' => $info['file_name']);
                $img = base_url().'images/manufacture/'.$info['file_name'];
            }

	    if ($this->model->update($uid, $manufacture) != true && $this->upload->display_errors()){ $this->reject($this->upload->display_errors());
            }else{ $this->error = $this->title.' successfully saved..!'; }
            $this->output = $data;
        }
        else{ $this->reject(validation_errors()); }
      }else{ $this->reject_token('Invalid Token or Expired..!'); }
      $this->response('content');
    }
    
    private function remove_image($uid)
    {
       $img = $this->model->get_manufacture_by_id($uid)->row();
       $img = $img->image;
       if ($img){ $img = "./images/manufacture/".$img; unlink("$img"); } 
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); } 

}

?>