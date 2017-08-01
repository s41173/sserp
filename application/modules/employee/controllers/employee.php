<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Employee extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Employee_model', 'em', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->user = new Admin_lib();
//        $this->dept = $this->load->library('dept_lib');
        $this->division = new Division_lib();
        $this->model = new Employees();
        $this->loan = new Loan_lib(); 
        $this->payrolltrans = new Payroll_trans_lib();
        $this->emlib = new Employee_lib();
    }

    private $properti, $modul, $title,$dept,$loan,$payrolltrans,$emlib;
    private $user,$currency,$student,$finance,$model,$division;

    function index()
    {
       $this->emlib->inactive();
       $this->get_last();
    }
    
    public function getdatatable($search=null,$division='null',$role='null',$active='null')
    {
        $this->emlib->inactive();
        if(!$search){ $result = $this->em->get($this->modul['limit'])->result(); }
        else{ $result = $this->em->search($division,$role,$active)->result(); }
	
        $output = null;
        if ($result){
            
         foreach($result as $res)
	 { 
	   $output[] = array ($res->id, $res->nip, $res->attcode, $res->name, $res->type, $res->work_time, $res->role, $this->division->get_name($res->division_id), $res->mobile, $res->active);
	 } 
         
        $this->output
         ->set_status_header(200)
         ->set_content_type('application/json', 'utf-8')
         ->set_output(json_encode($output))
         ->_display();
         exit;  
        }
    }
    
    function get_last()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords('Product Manager');
        $data['h2title'] = 'Employee List';
        $data['main_view'] = 'employee_view';
	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all');
        $data['form_action_report'] = site_url($this->title.'/report_process');
        $data['form_action_import'] = site_url($this->title.'/import_process');
        $data['link'] = array('link_back' => anchor('main/','Back', array('class' => 'btn btn-danger')));

        $data['division'] = $this->division->combo();
	// ---------------------------------------- //

        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table id="datatable-buttons" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('#','No', 'Nip', 'Att-Code', 'Name', 'Type', 'Role', 'Division', 'Phone', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url($this->title.'/getdatatable');
        $data['graph'] = site_url('employee/chart/');
            
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }

    public function chart()
    {
        $inactive = $this->model->where('active', 0)->count();
        $non = $this->model->where('type', 'non')->where('active', 1)->count();
        $academic = $this->model->where('type', 'academic')->where('active', 1)->count();
        
        $data = array(
                    array("label" => "Academic", "legendText" => "Academic", "y" => $academic),
                    array("label" => "Non Academic", "legendText" => "Non Academic", "y" => $non),
                    array("label" => "Inactive", "legendText" => "Inactive", "y" => $inactive)
                );

       echo json_encode($data, JSON_NUMERIC_CHECK);
    }
    
    
    function get_list($target='titem')
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'employee_list';

        if ($this->input->post('tnip')){ $result = $this->model->where('nip', $this->input->post('tnip'))->where('active', 1)->get(); }
        else{$result = $this->model->where('active', 1)->get();} 
        
        $tmpl = array('table_open' => '<table id="example" width="100%" cellspacing="0" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('No', 'Nip', 'Name', 'Division', '#');

        $i = 0;
        foreach ($result as $res)
        {
           $val = $res->nip;
           
           $data = array('name' => 'button', 'type' => 'button', 'class' => 'btn btn-primary', 'content' => 'Select', 'onclick' => 'setvalue(\''.$val.'\',\''.$target.'\')');

            $this->table->add_row
            (
                ++$i, $res->nip, strtoupper($res->name), $this->division->get_name($res->division_id),
                form_button($data)
            );
        }

            $data['table'] = $this->table->generate();
            $data['form_action'] = site_url('employees/get_list');
            $this->load->view('employee_list', $data);
    }

    
    function details($id)
    {
        $data['log']     = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
        $data['paddress'] = $this->properti['address'];
        $data['phone1']  = $this->properti['phone1'];
        $data['phone2']  = $this->properti['phone2'];
        $data['fax']     = $this->properti['fax'];
        $data['website'] = $this->properti['sitename'];
        $data['email']   = $this->properti['email'];
        
        $this->model->where('id', $id)->get();
       
        if ($this->model->type == 1){ $section = 'Academic'; }else { $section = 'Non Academic'; }
        if ($this->model->dept_id == 0){ $dept = 'General'; }else { $dept = $this->dept->get_name($this->model->dept_id); }
        if ($this->model->genre == 'm'){ $genre = 'Male'; }else { $genre = 'Female'; }
        if ($this->model->status == 'yes'){ $status = 'Married'; }elseif ($this->model->status == 'no') { $status = 'Not Married'; }else { $status = 'No Status'; }
        
        $data['section']   = $section; 
        $data['dept']      = $dept;
        $data['nip']       = $this->model->nip;
        $data['name']      = ucfirst($this->model->first_title.' '.$this->model->name.' '.$this->model->end_title);
        $data['first']     = $this->model->first_title;
        $data['end']       = $this->model->end_title;
        $data['nickname']  = $this->model->nickname;
        $data['genre']     = $genre;
        $data['bornplace'] = $this->model->born_place;
        $data['borndate']  = tglincomplete($this->model->born_date);
        $data['religion']  = $this->model->religion;
        $data['ethnic']    = $this->model->ethnic;
        $data['marital']   = $status;
        $data['idno']      = $this->model->id_no;
        $data['address']   = $this->model->address;
        $data['phone']     = $this->model->phone;
        $data['mobile']    = $this->model->mobile;
        $data['email']     = $this->model->email;
        $data['image']     = base_url().'images/employee/'.$this->model->image;
        $data['desc']      = $this->model->desc;
        
        $this->load->view('employee_detail', $data);
    }
        
    function add_process()
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){
                
        $data['division'] = $this->division->combo(); 
        
	// Form validation
        $this->form_validation->set_rules('cdivision', 'Division', 'required');
        $this->form_validation->set_rules('crole', 'Role', 'required');
        $this->form_validation->set_rules('tnip', 'NIP', 'required|numeric|callback_valid_nip');
        $this->form_validation->set_rules('tatt', 'Att Code', 'required|numeric|callback_valid_att');
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_valid_name['.$this->input->post('tnip').']');
        $this->form_validation->set_rules('tmobile', 'Mobile', 'required');
        $this->form_validation->set_rules('temail', 'Email', 'valid_email');
        $this->form_validation->set_rules('tjoined', 'Join Date', 'required');
        
        // account
        $this->form_validation->set_rules('taccname', 'Account Name', '');
        $this->form_validation->set_rules('taccno', 'Account No', 'numeric');
        $this->form_validation->set_rules('tbank', 'Bank', '');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            
            $this->model->division_id = $this->input->post('cdivision');
            $this->model->role        = $this->input->post('crole');
            $this->model->nip         = $this->input->post('tnip');
            $this->model->attcode     = $this->input->post('tatt');
            $this->model->name        = $this->input->post('tname');
            $this->model->nickname    = $this->input->post('tnickname');
            $this->model->mobile      = $this->input->post('tmobile');
            $this->model->email       = $this->input->post('temail');
            $this->model->genre       = $this->input->post('cgenre');
            $this->model->joined      = setnull($this->input->post('tjoined'));
            $this->model->resign      = intval(date('Y', strtotime($this->input->post('tdate')))+60).'-12-31';
            $this->model->active      = 1;
            
            // ==================== upload ========================
            
            $config['upload_path']   = './images/employee/';
            $config['file_name']     = $this->input->post('tnip');
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['overwrite']     = TRUE;
            $config['max_size']	     = '30000';
            $config['max_width']     = '10000';
            $config['max_height']    = '10000';
            $config['remove_spaces'] = TRUE;
            
            $this->load->library('upload', $config);
            
            if ( !$this->upload->do_upload("userfile")){ $data['error'] = $this->upload->display_errors();  $this->model->image = 'default.png';}
            else{ $info = $this->upload->data();  $this->model->image = $info['file_name']; }
            
            // ==================== upload ========================
            
            $this->model->save();
            echo "true|One $this->title data successfully saved!";
        }
        else{ echo "error|".validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
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
            $this->delete($cek[$i]);
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
        if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
        if ($this->loan->cek_loan($uid) == TRUE && $this->payrolltrans->cek_payroll($uid) == TRUE)
        {
            $img = $this->model->where('id', $uid)->get()->image;
            if ($img != 'default.png'){ $img = "./images/employee/".$img; @unlink("$img"); }

            $this->em->delete($uid);
            echo "true|1 $this->title successfully removed..!";
        }
        else { echo "error|1 $this->title still have loan & transaction..!";  }
        }else {echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    function update($uid)
    {
        $this->em->valid_add_trans($uid, $this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'employee_update';
	$data['form_action'] = site_url($this->title.'/update_process');
        $data['link'] = array('link_back' => anchor($this->title,'Back', array('class' => 'btn btn-danger')));
        $data['source'] = site_url($this->title.'/getdatatable');
        $data['division'] = $this->division->combo(); 
        
        $this->model->where('id', $uid)->get();
        
        $data['default']['name']      = $this->model->name;
        $data['default']['first']     = $this->model->first_title;
        $data['default']['end']       = $this->model->end_title;
        $data['default']['nickname']  = $this->model->nickname;
        $data['default']['genre']     = $this->model->genre;
        $data['default']['division']  = $this->model->division_id;
        $data['default']['role']      = $this->model->role;
        $data['default']['nip']       = $this->model->nip;
        $data['default']['att']       = $this->model->attcode;
        $data['default']['email']     = $this->model->email;
        $data['default']['image']     = base_url().'images/employee/'.$this->model->image;
        $data['default']['joined']    = $this->model->joined;
        $data['default']['mobile']    = $this->model->mobile;
        $data['default']['phone']     = $this->model->phone;
        
        
        $data['default']['time']      = $this->model->work_time;
        $data['default']['dept']      = $this->model->dept_id;
        $data['default']['bornplace'] = $this->model->born_place;
        $data['default']['borndate']  = $this->model->born_date;
        $data['default']['religion']  = $this->model->religion;
        $data['default']['ethnic']    = $this->model->ethnic;
        $data['default']['married']   = $this->model->status;
        $data['default']['idno']      = $this->model->id_no;
        $data['default']['address']   = $this->model->address;
        
        
        $data['default']['desc']      = $this->model->desc;
        $data['default']['bank']      = $this->model->bank_name;
        $data['default']['accno']     = $this->model->acc_no;
        $data['default']['accname']   = $this->model->acc_name;
        $data['default']['resign']   = $this->model->resign;
        $data['default']['subject']  = $this->model->subject;
        
	$this->session->set_userdata('langid', $this->model->id);
        $this->load->view('template', $data);
    }

    // Fungsi update untuk mengupdate db
    function update_process($param=0)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){
            
        $data['division'] = $this->division->combo();     
        $data['form_action'] = site_url($this->title.'/update_process');
        $this->model->where('id', $this->session->userdata('langid'))->get();
	// Form validation
        
        if ($param == 1){
            
            $this->form_validation->set_rules('cdivision', 'Division', 'callback_valid_division['.$this->input->post('csection').']');
            $this->form_validation->set_rules('crole', 'Role', 'required');
            $this->form_validation->set_rules('tnip', 'NIP', 'required|numeric|callback_validating_nip');
            $this->form_validation->set_rules('tatt', 'Att Code', 'required|numeric|callback_validating_att');
            $this->form_validation->set_rules('tname', 'Name', 'required|callback_validating_name['.$this->input->post('tnip').']');
            $this->form_validation->set_rules('tnickname', 'Nick Name', '');
            $this->form_validation->set_rules('cgenre', 'Genre', '');
            $this->form_validation->set_rules('tjoined', 'Join Date', 'required');
            $this->form_validation->set_rules('tphone', 'Phone', '');
            $this->form_validation->set_rules('tmobile', 'Mobile', '');
            $this->form_validation->set_rules('temail', 'Email', 'valid_email');
            
            if ($this->form_validation->run($this) == TRUE){
                
                $this->model->division_id = $this->input->post('cdivision');
                $this->model->role        = $this->input->post('crole');
                $this->model->nip         = $this->input->post('tnip');
                $this->model->attcode     = $this->input->post('tatt');
                $this->model->name        = $this->input->post('tname');
                $this->model->nickname    = $this->input->post('tnickname');
                $this->model->genre       = $this->input->post('cgenre');
                $this->model->phone       = $this->input->post('tphone');
                $this->model->mobile      = $this->input->post('tmobile');
                $this->model->email       = $this->input->post('temail');
                $this->model->joined      = setnull($this->input->post('tjoined'));
                
                // ==================== upload ========================
            
                $config['upload_path']   = './images/employee/';
                $config['file_name']     = $this->input->post('tnip');
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['overwrite']     = TRUE;
                $config['max_size']	 = '30000';
                $config['max_width']     = '10000';
                $config['max_height']    = '10000';
                $config['remove_spaces'] = TRUE;

                $this->load->library('upload', $config);

                if ( !$this->upload->do_upload("userfile")){ $data['error'] = $this->upload->display_errors(); }
                else{ $info = $this->upload->data();  $this->model->image = $info['file_name']; }

                // ==================== upload ========================

                $this->model->save();
                $this->session->set_flashdata('message', "One $this->title has successfully updated!");

            }else{
                $this->session->set_flashdata('message', validation_errors());
            }
            redirect($this->title.'/update/'.$this->session->userdata('langid'));
        }
        elseif($param == 2){
            
            $this->form_validation->set_rules('ctime', 'Time Work', 'required');
            $this->form_validation->set_rules('tbornplace', 'Born Place', '');
            $this->form_validation->set_rules('tborndate', 'Born Date', '');
            $this->form_validation->set_rules('creligion', 'Religion', '');
            $this->form_validation->set_rules('tethnic', 'Ethnic', '');
            $this->form_validation->set_rules('rmarried', 'Marital Status', '');
            $this->form_validation->set_rules('tidno', 'ID-No', '');
            $this->form_validation->set_rules('taddress', 'Address', '');
            
            if ($this->form_validation->run($this) == TRUE){
                
                $this->model->work_time   = $this->input->post('ctime');
                $this->model->born_place  = $this->input->post('tbornplace');
                $this->model->born_date   = $this->input->post('tborndate');
                $this->model->religion    = $this->input->post('creligion');
                $this->model->ethnic      = $this->input->post('tethnic');
                $this->model->status      = $this->input->post('rmarried');
                $this->model->id_no       = $this->input->post('tidno');
                $this->model->address     = $this->input->post('taddress');
                
                $this->model->save();
                $this->session->set_flashdata('message', "One $this->title has successfully updated!");
                
            }else{ $this->session->set_flashdata('message', validation_errors()); } 
            redirect($this->title.'/update/'.$this->session->userdata('langid'));
        }
        elseif ($param == 3){
           
            $this->form_validation->set_rules('tdesc', 'Description', '');
            $this->form_validation->set_rules('taccname', 'Account Name', '');
            $this->form_validation->set_rules('taccno', 'Account No', 'numeric');
            $this->form_validation->set_rules('tbank', 'Bank', '');
            $this->form_validation->set_rules('tresign', 'Resign Date', 'callback_valid_resign['.$this->input->post('tjoined').']');
            $this->form_validation->set_rules('tsubject', 'Subject Lesson', '');
            
             if ($this->form_validation->run($this) == TRUE){
                
                $this->model->desc        = $this->input->post('tdesc');
                $this->model->bank_name   = $this->input->post('tbank');
                $this->model->acc_name    = $this->input->post('taccname');
                $this->model->acc_no      = $this->input->post('taccno');
                $this->model->resign      = setnull($this->input->post('tresign'));
                $this->model->subject     = $this->input->post('tsubject');
                
                $this->model->save();
                $this->session->set_flashdata('message', "One $this->title has successfully updated!");
                
            }else{ $this->session->set_flashdata('message', validation_errors()); } 
            redirect($this->title.'/update/'.$this->session->userdata('langid'));
            
        }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    function download()
    {
       $this->load->helper('download');
        
       $data = file_get_contents("uploads/sample/employee_sample.csv"); // Read the file's contents
       $name = 'employee_sample.csv';    
       force_download($name, $data);
    }
    
    
    function import_process()
    {
        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'employee_import';
	$data['form_action_import'] = site_url($this->title.'/import_process');
        $data['error'] = null;
	
//        $this->form_validation->set_rules('userfile', 'Import File', '');
        
//        if ($this->form_validation->run($this) == TRUE)
//        {
             // ==================== upload ========================
            
            $config['upload_path']   = './uploads/';
            $config['file_name']     = 'employee';
            $config['allowed_types'] = '*';
            $config['overwrite']     = TRUE;
            $config['max_size']	     = '1000';
            $config['remove_spaces'] = TRUE;
            $this->load->library('upload', $config);
            
            if ( !$this->upload->do_upload("userfile"))
            { 
               $data['error'] = $this->upload->display_errors(); 
               echo "error|".$this->upload->display_errors();
            }
            else
            { 
               // success page 
              $this->import_attendance($config['file_name'].'.csv');
              $info = $this->upload->data(); 
              $this->session->set_flashdata('message', "One $this->title data successfully imported!");
              echo "true|One $this->title data successfully imported!";
            }                
        
    }
    
    private function import_attendance($filename)
    {
        $this->load->helper('file');
        $emp = new Employee_lib();
        $csvreader = $this->load->library('csvreader');
        $filename = './uploads/'.$filename;
        
        $result = $csvreader->parse_file($filename);
        
        foreach($result as $res)
        {
           if($this->valid_coloumn($res) == TRUE)
           {  
             if ($this->validation_import($res['DIVISION'],$res['ATTCODE'],$res['NIP']) == TRUE)
             {  
                $emp->save($this->division->get_id($res['DIVISION']), 0, $res['ATTCODE'], $res['NIP'], $res['NAME']).'<br>'; 
             } 
           } 
        }
    }
    
    private function valid_coloumn($res)
    {
        if(isset($res['DIVISION']) && isset($res['ATTCODE']) && isset($res['NIP']) && isset($res['NAME']))
        { return TRUE; }else { return FALSE; }
    }
    
    private function validation_import($division=null,$attcode=null,$nip=null)
    {
        $res[0] = FALSE;
        $res[1] = FALSE;
        $res[2] = FALSE;
        $res[3] = FALSE;
        $res[4] = FALSE;
        
        if ($this->division->get_id($division)){ $res[0] = TRUE;}
        if ($this->valid_att($attcode) == TRUE){ $res[1] = TRUE; }
        if ($this->valid_nip($nip) == TRUE){ $res[2] = TRUE; }
        
        if ($res[0] == TRUE && $res[1] == TRUE && $res[2] == TRUE){ return TRUE; }else { return FALSE; }
    }
    
    public function valid_division($division,$section)
    {
        if ($section == 'non')
        { if (!$division){ $this->form_validation->set_message('valid_division', "Division required..!"); return FALSE; } else{ return TRUE;} }
    }
    
    public function valid_section($section,$dept)
    {
        if ($section == 'academic')
        {
            if (!$dept){ $this->form_validation->set_message('valid_section', "Department required..!"); return FALSE; }
            else { return TRUE; }
        }
        else { return TRUE; }
    }

    public function valid_nip($nip)
    {
        $val = $this->model->where('nip', $nip)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('valid_nip', "Employee [$nip] already registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function valid_resign($resign,$joined)
    {
        
        if ($resign <= $joined)
        {
            $this->form_validation->set_message('valid_resign', "Invalid Resign Date..!"); return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function valid_att($code)
    {
        $val = $this->model->where('attcode', $code)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('valid_att', "Employee [$code] already registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function validating_nip($nip)
    {
        $val = $this->model->where_not_in('id', $this->session->userdata('langid'))->where('nip', $nip)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('validating_nip', "NIP [$nip] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function valid_name($name,$nip)
    {
        $this->model->where('name', $name);
        $val = $this->model->where('nip', $nip)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('valid_name', "Employee [$nip - $name] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function validating_name($name,$nip)
    {
        $this->model->where_not_in('id', $this->session->userdata('langid'));
        $this->model->where('name', $name);
        $val = $this->model->where('nip', $nip)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('validating_name', "Employee [$nip - $name] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function validating_att($att)
    {
        $this->model->where_not_in('id', $this->session->userdata('langid'));
        $val = $this->model->where('attcode', $att)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('validating_att', "Employee Att-Code [$att] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function report_process()
    {
        $data['log']     = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
        $data['address'] = $this->properti['address'];
        $data['phone1']  = $this->properti['phone1'];
        $data['phone2']  = $this->properti['phone2'];
        $data['fax']     = $this->properti['fax'];
        $data['website'] = $this->properti['sitename'];
        $data['email']   = $this->properti['email'];
        
//        $data['department'] = $this->dept->get_name($this->input->post('cdept'));
        $data['division']   = $this->division->get_name($this->input->post('cdivision'));
        $data['log'] = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
        
        $period = $this->input->post('reservation');  
        $start = picker_between_split($period, 0);
        $end = picker_between_split($period, 1);
                
        $data['results'] = $this->em->report($this->input->post('cdivision'), $this->input->post('crole') ,$this->input->post('cstatus'),$start,$end)->result();
       
        if ($this->input->post('cptype') == 0){ $this->load->view('employee_report', $data); }
        else { $this->load->view('employee_pivot', $data); }
    }

}

?>