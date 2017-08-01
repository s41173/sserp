<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Experience extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Experience_model', 'em', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->user = new Admin_lib();
//        $this->dept = new Dept_lib();
        $this->employee = new Employee_lib();
        $this->division = new Division_lib();
        $this->model = new Experiences();
    }

    private $properti, $modul, $title,$dept,$employee,$division;
    private $user,$currency,$model;

    function index()
    {
       $this->get_last();
    }
    
    public function getdatatable($search=null,$division='null')
    {
        if(!$search){ $result = $this->em->get($this->modul['limit'])->result(); }
        else{ $result = $this->em->search($division)->result(); }
	
        $output = null;
        if ($result){
            
         foreach($result as $res)
	 { 
           $total = intval($res->amount+$res->bonus+$res->principal+$res->principal_helper+$res->head_department+$res->home_room+$res->picket-$res->insurance);  
	   $output[] = array ($res->id, $this->employee->get_name($res->employee_id), $res->time_work, $res->amount, idr_format($res->consumption), idr_format($res->transportation),
                              $res->bonus, $res->bonus_remarks, $res->principal, $res->principal_helper, $res->head_department,
                              $res->home_room, $res->picket, idr_format($res->insurance), idr_format($total), $this->division->get_name($res->division_id));
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
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'experience_view';
	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all');
        $data['form_action_report'] = site_url($this->title.'/report_process');
        $data['link'] = array('link_back' => anchor('main/','Back', array('class' => 'btn btn-danger')));

	// ---------------------------------------- //
        $data['division'] = $this->division->combo();
        
        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table id="datatable-buttons" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('#','No', 'Employee', 'Division', 'Time Work', 'Consumption', 'Transportation', '(-)Insurance', 'Amount', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url($this->title.'/getdatatable');
        $data['graph'] = site_url('employee/chart/');
            
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }
    
    function xget_last()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'experience_view';
	$data['form_action'] = site_url($this->title.'/search');
        $data['link'] = array('link_back' => anchor('payroll_reference/','<span>back</span>', array('class' => 'back')));
        
	$uri_segment = 3;
        $offset = $this->uri->segment($uri_segment);
        
	// ---------------------------------------- //
//        $result = $this->model->get($this->modul['limit'], $offset);
        $result = $this->em->get($this->modul['limit'], $offset)->result();
        $num_rows = $this->em->count();

        if ($num_rows > 0)
        {
	    $config['base_url'] = site_url($this->title.'/get_last');
            $config['total_rows'] = $num_rows;
            $config['per_page'] = $this->modul['limit'];
            $config['uri_segment'] = $uri_segment;
            $this->pagination->initialize($config);
            $data['pagination'] = $this->pagination->create_links(); //array menampilkan link untuk pagination.
            // akhir dari config untuk pagination
//            
//
            // library HTML table untuk membuat template table class zebra
            $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="1" class="tablemaster">');

            $this->table->set_template($tmpl);
            $this->table->set_empty("&nbsp;");

            //Set heading untuk table
            $this->table->set_heading('No', 'Name', 'Dept', 'Time Work (Years)', 'Consumption', 'Transportation', '(-)Insurance', 'Amount', '#');
//
            $i = 0 + $offset;
            foreach ($result as $res)
            {
                $total = intval($res->amount+$res->bonus+$res->principal+$res->principal_helper+$res->head_department+$res->home_room+$res->picket-$res->insurance);
                $this->table->add_row
                (
                    ++$i, $this->employee->get_name($res->employee_id).' - '.$this->employee->get_nip($res->employee_id), $this->dept->get_name($res->dept), $res->time_work, number_format($res->consumption), number_format($res->transportation), number_format($res->insurance), number_format($total),
                    anchor($this->title.'/update/'.$res->id,'<span>details</span>',array('class' => 'update', 'title' => '')).' '.    
                    anchor($this->title.'/delete/'.$res->id,'<span>delete</span>',array('class'=> 'delete', 'title' => 'delete' ,'onclick'=>"return confirm('Are you sure you will delete this data?')"))
                );
            }
//
            $data['table'] = $this->table->generate();
        }
        else
        {
            $data['message'] = "No $this->title data was found!";
        }
//
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }
    
    function search()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'experience_view';
	$data['form_action'] = site_url($this->title.'/search');
        $data['link'] = array('link_back' => anchor($this->title,'<span>back</span>', array('class' => 'back')));
        
	// ---------------------------------------- //
        if ($this->input->post('tname')){ $result = $this->em->search($this->employee->get_id_by_nip($this->input->post('tname')))->result(); }
        elseif ($this->input->post('tvalue')){ $result = $this->em->search($this->employee->get_id_by_name($this->input->post('tvalue')))->result(); }
//        $result = $this->em->search($this->employee->get_id_by_nip($this->input->post('tname')))->result();
  
        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="1" class="tablemaster">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('No', 'Name', 'Dept', 'Time Work (Years)', 'Consumption', 'Transportation', '(-)Insurance', 'Amount', '#');
//
        $i = 0;
        foreach ($result as $res)
        {
            $total = intval($res->amount+$res->bonus+$res->principal+$res->principal_helper+$res->head_department+$res->home_room+$res->picket-$res->insurance);
            $this->table->add_row
            (
                ++$i, $this->employee->get_name($res->employee_id).' - '.$this->employee->get_nip($res->employee_id), $this->dept->get_name($res->dept), $res->time_work, number_format($res->consumption), number_format($res->transportation), number_format($res->insurance), number_format($total),
                anchor($this->title.'/update/'.$res->id,'<span>details</span>',array('class' => 'update', 'title' => '')).' '.    
                anchor($this->title.'/delete/'.$res->id,'<span>delete</span>',array('class'=> 'delete', 'title' => 'delete' ,'onclick'=>"return confirm('Are you sure you will delete this data?')"))
            );
        }
//
        $data['table'] = $this->table->generate();
	$this->load->view('template', $data);
    }
    
    
    function add_process()
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){
        
	// Form validation
        $this->form_validation->set_rules('tnip', 'Employee Nip', 'required|numeric|callback_valid_employee');
        $this->form_validation->set_rules('ttime', 'Time Work', 'required|numeric');
        $this->form_validation->set_rules('tamount', 'Experiences Amount', 'required|numeric');
        $this->form_validation->set_rules('tconsumption', 'Consumption', 'required|numeric');
        $this->form_validation->set_rules('ttransport', 'Transport', 'required|numeric');
        $this->form_validation->set_rules('tbonus', 'Bonus', 'required|numeric');
        $this->form_validation->set_rules('tbonusremarks', 'Bonus Remarks', '');
//        $this->form_validation->set_rules('tprincipal', 'Principal', 'required|numeric');
//        $this->form_validation->set_rules('tpks', 'Principal Helper', 'required|numeric');
//        $this->form_validation->set_rules('tkajur', 'Head Department', 'required|numeric');
//        $this->form_validation->set_rules('troom', 'Students Guardian', 'required|numeric');
//        $this->form_validation->set_rules('tpicket', 'Picket', 'required|numeric');
        $this->form_validation->set_rules('tinsurance', 'Insurance', 'required|numeric');
        
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->employee_id  = $this->employee->get_id_by_nip($this->input->post('tnip'));
            $this->model->time_work    = $this->input->post('ttime');
            $this->model->amount       = $this->input->post('tamount');
            $this->model->consumption    = $this->input->post('tconsumption');
            $this->model->transportation = $this->input->post('ttransport');
            $this->model->bonus          = $this->input->post('tbonus');
            $this->model->bonus_remarks  = $this->input->post('tbonusremarks');
//            $this->model->principal        = $this->input->post('tprincipal');
//            $this->model->principal_helper = $this->input->post('tpks');
//            $this->model->head_department  = $this->input->post('tkajur');
//            $this->model->home_room        = $this->input->post('troom');
//            $this->model->picket           = $this->input->post('tpicket');
            $this->model->insurance        = $this->input->post('tinsurance');
            $this->model->dept = '0';
            
            $this->model->save();
            $this->session->set_flashdata('message', "One $this->title data successfully saved!");
//            redirect($this->title.'/add');
            echo "true|One $this->title data successfully saved!";
        }
        else{ echo "error|".validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    function delete($uid)
    {
        if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
            $this->model->where('id', $uid)->get();
            $this->model->delete(); 
            echo "true|1 $this->title successfully removed..!";
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
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
            $this->delete($cek[$i]);
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
    
    function update($uid)
    {   
        $experience = $this->model->where('id', $uid)->get();
        
	$this->session->set_userdata('langid', $this->model->id);
        echo $experience->id.'|'. $this->employee->get_nip($experience->employee_id).'|'.$experience->time_work.'|'.$experience->amount.'|'.$experience->consumption.'|'.
             $experience->transportation.'|'.$experience->bonus.'|'.$experience->bonus_remarks.'|'.$experience->insurance;
        
    }

    // Fungsi update untuk mengupdate db
    function update_process()
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'student_form';
	$data['form_action'] = site_url($this->title.'/update_process');

	// Form validation
        $this->form_validation->set_rules('ttime', 'Time Work', 'required|numeric');
        $this->form_validation->set_rules('tamount', 'Experiences Amount', 'required|numeric');
        $this->form_validation->set_rules('tconsumption', 'Consumption', 'required|numeric');
        $this->form_validation->set_rules('ttransport', 'Transport', 'required|numeric');
        $this->form_validation->set_rules('tbonus', 'Bonus', 'required|numeric');
        $this->form_validation->set_rules('tbonusremarks', 'Bonus Remarks', '');
//        $this->form_validation->set_rules('tprincipal', 'Principal', 'required|numeric');
//        $this->form_validation->set_rules('tpks', 'Principal Helper', 'required|numeric');
//        $this->form_validation->set_rules('tkajur', 'Head Department', 'required|numeric');
//        $this->form_validation->set_rules('troom', 'Students Guardian', 'required|numeric');
//        $this->form_validation->set_rules('tpicket', 'Picket', 'required|numeric');
        $this->form_validation->set_rules('tinsurance', 'Insurance', 'required|numeric');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->where('id', $this->session->userdata('langid'))->get();
            
            $this->model->time_work  = $this->input->post('ttime');
            $this->model->amount     = $this->input->post('tamount'); 
            $this->model->consumption    = $this->input->post('tconsumption');
            $this->model->transportation = $this->input->post('ttransport');
            $this->model->bonus          = $this->input->post('tbonus');
            $this->model->bonus_remarks  = $this->input->post('tbonusremarks');
//            $this->model->principal        = $this->input->post('tprincipal');
//            $this->model->principal_helper = $this->input->post('tpks');
//            $this->model->head_department  = $this->input->post('tkajur');
//            $this->model->home_room        = $this->input->post('troom');
//            $this->model->picket           = $this->input->post('tpicket');
            $this->model->insurance        = $this->input->post('tinsurance');
            
            $this->model->save();
            $this->session->set_flashdata('message', "One $this->title has successfully updated!");
//            redirect($this->title.'/update/'.$this->session->userdata('curid'));
            
            echo 'true|Data successfully saved..!';
        }
        else{ echo 'error|'.validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    public function valid_employee($nip)
    {
        if ($this->employee->get_type($this->employee->get_id_by_nip($nip)) == 'academic')
        {
          $val = $this->model->where('employee_id', $this->employee->get_id_by_nip($nip))->where('dept', $this->input->post('cdept'))->count();
        }
        else { $val = $this->model->where('employee_id', $this->employee->get_id_by_nip($nip))->count(); }
        
        if ($val > 0)
        {
            $this->form_validation->set_message('valid_employee', "Employee [$nip] - already registered..!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    public function valid_dept($dept,$employee)
    {
        if ($this->employee->get_type($employee) == 'academic')
        {
            if(!$dept){ $this->form_validation->set_message('valid_dept', "Department Required..!"); return FALSE; }
            else { return TRUE; }
        }
        else { return TRUE; }
    }
    
    public function valid_period($date=null)
    {
        $p = new Period();
        $p->get();

        $month = date('n', strtotime($date));
        $year  = date('Y', strtotime($date));

        if ( intval($p->month) != intval($month) || intval($p->year) != intval($year) )
        {
            $this->form_validation->set_message('valid_period', "Invalid Period.!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    
    public function report()
    {
        $data['log']     = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
        $data['address'] = $this->properti['address'];
        $data['phone1']  = $this->properti['phone1'];
        $data['phone2']  = $this->properti['phone2'];
        $data['fax']     = $this->properti['fax'];
        $data['website'] = $this->properti['sitename'];
        $data['email']   = $this->properti['email'];
        
        $data['log'] = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
                
        $data['results'] = $this->em->report()->result();
        
        $this->load->view('experience_report', $data);
    }

}

?>