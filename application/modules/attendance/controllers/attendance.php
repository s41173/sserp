<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Attendance extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Attendance_model', 'am', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency =  new Currency_lib();
        $this->user = new Admin_lib();
//        $this->dept = $this->load->library('dept_lib');
        $this->employee = new Employee_lib();
        $this->model = new Attendances();
        $this->period = new Period_lib();
        $this->period = $this->period->get();
    }

    private $properti, $modul, $title,$dept,$employee;
    private $user,$currency,$model, $period;

    function index()
    {
       $this->get_last();
    }
    
    public function getdatatable($search=null,$nip='null',$month='null',$year='null')
    {
        if(!$search){ $result = $this->model->where('year', $this->period->year)->group_by('month')->get($this->modul['limit']);  }
        else{
            if ($nip != 'null'){$nip = $this->employee->get_id_by_nip($nip); }
            $result = $this->am->search($nip, $month, $year)->result();
        }
        
        if ($result){
	foreach($result as $res)
	{
	   $output[] = array ($res->id, get_month($res->month).'-'.$res->year, $this->get_sum($res->month, $res->year),
                              $res->month, $res->year);
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

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'attendance_view';
	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_report'] = site_url($this->title.'/report_process');
        $data['form_action_import'] = site_url($this->title.'/import_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all');
        $data['link'] = array('link_back' => anchor('main/','Back', array('class' => 'btn btn-danger')));

        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table id="datatable-buttons" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('#','No', 'Period', 'Total Employee', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url($this->title.'/getdatatable');
            
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }
    
    function xget_last()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'attendance_view';
	$data['form_action'] = site_url($this->title.'/search');
        $data['link'] = array('link_back' => anchor('payroll_reference/','<span>back</span>', array('class' => 'back')));
        
        $data['dept'] = $this->dept->combo_all();
        
	$uri_segment = 3;
        $offset = $this->uri->segment($uri_segment);
        
        $p = new Period();
        $p->get();
        
	// ---------------------------------------- //
        $result = $this->model->where('year', $p->year)->group_by('month')->get($this->modul['limit'], $offset);
        $num_rows = $this->model->count();

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
            $this->table->set_heading('No', 'Period', 'Total Employee', '#');
//
            $i = 0 + $offset;
            foreach ($result as $res)
            {
                $this->table->add_row
                (
                    ++$i, get_month($res->month).'-'.$res->year, $this->get_sum($res->month, $res->year),
                    anchor($this->title.'/details/'.$res->month.'/'.$res->year,'<span>details</span>',array('class' => 'details1', 'title' => '')).' '.    
                    anchor($this->title.'/delete_attendance/'.$res->month.'/'.$res->year,'<span>delete</span>',array('class'=> 'delete', 'title' => 'delete' ,'onclick'=>"return confirm('Are you sure you will delete this data?')"))
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
    
    private function get_sum($month,$year)
    {
       return $this->model->where('year', $year)->where('month', $month)->count();
    }
    
    function details($param)
    {
        $this->acl->otentikasi1($this->title);

        $val = explode("-", $param);
        $month = $val[0];
        $year = $val[1];
        
        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'attendance_view';
	$data['form_action'] = site_url($this->title.'/search');
        $data['link'] = array('link_back' => anchor($this->title,'<span>back</span>', array('class' => 'back')));
        
        $result = $this->model->where('month', $month)->where('year', $year)->get();
        
         // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="1" class="tablemaster">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('No', 'Period', 'Name', 'Presence', 'Late', 'Overtime', '#');
//
        $i = 0;
        foreach ($result as $res)
        {
            $this->table->add_row
            (
                ++$i, get_month($res->month).'-'.$res->year, $this->employee->get_name($res->employee_id).' - '.$this->employee->get_nip($res->employee_id), $res->presence, $res->late, $res->overtime,
                anchor($this->title.'/update/'.$res->id,'<span>details</span>',array('class' => 'update', 'title' => '')).' '.    
                anchor($this->title.'/delete/'.$res->id,'<span>delete</span>',array('class'=> 'delete', 'title' => 'delete' ,'onclick'=>"return confirm('Are you sure you will delete this data?')"))
            );
        }
//
        $data['table'] = $this->table->generate();
        $this->load->view('template', $data);
    }
    
    function search()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'attendance_view';
	$data['form_action'] = site_url($this->title.'/search');
        $data['link'] = array('link_back' => anchor($this->title,'<span>back</span>', array('class' => 'back')));
        $data['dept'] = $this->dept->combo_all();
	// ---------------------------------------- //
        $result = $this->am->search($this->employee->get_id_by_nip($this->input->post('tnip')), $this->input->post('cmonth'), $this->input->post('tyear'))->result();
  
        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="1" class="tablemaster">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('No', 'Period', 'Name', 'Presence', 'Late', 'Overtime', '#');
//
        $i = 0;
        foreach ($result as $res)
        {
            $this->table->add_row
            (
                ++$i, get_month($res->month).'-'.$res->year, $this->employee->get_name($res->employee_id).' - '.$this->employee->get_nip($res->employee_id), $res->presence, $res->late, $res->overtime,
                anchor($this->title.'/update/'.$res->id,'<span>details</span>',array('class' => 'update', 'title' => '')).' '.    
                anchor($this->title.'/delete/'.$res->id,'<span>delete</span>',array('class'=> 'delete', 'title' => 'delete' ,'onclick'=>"return confirm('Are you sure you will delete this data?')"))
            );
        }
//
        $data['table'] = $this->table->generate();
	$this->load->view('template', $data);
    }
    
    function import_process()
    {
        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'attendance_import';
	$data['form_action'] = site_url($this->title.'/import_process');
        $data['error'] = null;
	
        $this->form_validation->set_rules('cmonth', 'Period Month', 'required|callback_valid_period['.$this->input->post('tyear').']');
        $this->form_validation->set_rules('tyear', 'Period Year', 'required|callback_valid_year');
        $this->form_validation->set_rules('userfile', 'Import File', '');
        
        if ($this->form_validation->run($this) == TRUE)
        {
             // ==================== upload ========================
            
            $config['upload_path']   = './uploads/';
            $config['file_name']     = $this->input->post('cmonth').'-'.$this->input->post('tyear');
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
              $this->import_attendance($this->input->post('cmonth'),$this->input->post('tyear'),$config['file_name'].'.csv');
              $info = $this->upload->data(); 
              $this->session->set_flashdata('message', "One $this->title data successfully imported!");
              echo "true|One $this->title data successfully imported!";
            }                
        }
        else { echo "error|".validation_errors(); }
        
    }
    
    private function import_attendance($month,$year,$filename)
    {
        $this->load->helper('file');
        $att = new Attendance_lib();
        $csvreader = $this->load->library('csvreader');
        $filename = './uploads/'.$filename;
        
        $result = $csvreader->parse_file($filename);
        
        foreach($result as $res)
        {
           if(isset($res['CODE']) && isset($res['NAME']) && isset($res['WORKING']) && isset($res['LATE']) && isset($res['OVERTIME']))
           {
             $eid = $this->employee->get_id_by_att($res['CODE']);
             if ($eid)
             {  
               $att->save($eid, $month, $year, intval($res['WORKING']), intval($res['LATE']), 
                          intval($res['OVERTIME']), $this->session->userdata('log'));
             }      
           }              
        }
    }
    
    function download()
    {
       $this->load->helper('download');
        
       $data = file_get_contents("uploads/sample/attendance_sample.csv"); // Read the file's contents
       $name = 'attendance_sample.csv';    
       force_download($name, $data);
    }
    
    function add_process()
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){
        
	// Form validation
        $this->form_validation->set_rules('tnip', 'Employee Nip', 'required|numeric|callback_valid_absence');
        $this->form_validation->set_rules('cmonth', 'Period Month', 'required');
        $this->form_validation->set_rules('tyear', 'Period Year', 'required|callback_valid_year');
        $this->form_validation->set_rules('tpresence', 'Presence', 'required|numeric');
        $this->form_validation->set_rules('tlate', 'Late', 'required|numeric');
        $this->form_validation->set_rules('tovertime', 'Overtime', 'required|numeric');
        
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->employee_id  = $this->employee->get_id_by_nip($this->input->post('tnip'));
            $this->model->month        = $this->input->post('cmonth');
            $this->model->year         = $this->input->post('tyear');
            $this->model->presence     = $this->input->post('tpresence');
            $this->model->late         = $this->input->post('tlate');
            $this->model->overtime     = $this->input->post('tovertime');
            $this->model->log          = $this->session->userdata('log');
            
            $this->model->save();
            $this->session->set_flashdata('message', "One $this->title data successfully saved!");
            echo 'true|'.$this->title.' successfully saved..!';
        }
        else{ echo "error|".validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }

    }
    
    function delete($uid)
    {
        if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
        $this->model->where('id', $uid)->get();
        if ($this->valid_period($this->model->month, $this->model->year) == TRUE)
        {
            $this->model->delete(); 
            $this->session->set_flashdata('message', "1 $this->title successfully removed..!");
            echo "true|1 $this->title successfully removed..!";
        }
        else{ echo "error|Invalid period..!"; }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    function delete_attendance($param)
    {
       $val = explode("-", $param);
       $month = $val[0];
       $year = $val[1];
       if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
       if ($this->valid_period($month, $year) == TRUE)
       {
          $this->model->where('month', $month)->where('year', $year)->get()->delete_all();
          $this->session->set_flashdata('message', "Period $month - $year successfully removed..!");
          echo "true|1 $this->title successfully removed..!";
       }
       else{ echo "error|Invalid period..!"; }
       }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    function update($uid)
    {
//        $this->acl->otentikasi2($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'attendance_update';
	$data['form_action'] = site_url($this->title.'/update_process');
	$data['link'] = array('link_back' => anchor($this->title,'<span>back</span>', array('class' => 'back')));
        
        $this->model->where('id', $uid)->get();
        
        $data['default']['employee']    = $this->employee->get_name($this->model->employee_id);
        $data['default']['month']       = $this->model->month;
        $data['default']['year']        = $this->model->year;
        $data['default']['presence']    = $this->model->presence;
        $data['default']['late']        = $this->model->late;
        $data['default']['overtime']    = $this->model->overtime;
        
	$this->session->set_userdata('curid', $this->model->id);
        $this->load->view('attendance_update', $data);
    }
    
    // Fungsi update untuk mengupdate db
    function update_process()
    {
        $this->acl->otentikasi3($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'student_form';
	$data['form_action'] = site_url($this->title.'/update_process');

	// Form validation
        $this->form_validation->set_rules('tmonth', 'Periode', 'required|numeric|callback_valid_period['.$this->input->post('tyear').']');
        $this->form_validation->set_rules('tpresence', 'Presence', 'required|numeric');
        $this->form_validation->set_rules('tlate', 'Late', 'required|numeric');
        $this->form_validation->set_rules('tovertime', 'Overtime', 'required|numeric');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->where('id', $this->session->userdata('curid'))->get();
            
            $this->model->presence     = $this->input->post('tpresence');
            $this->model->late         = $this->input->post('tlate');
            $this->model->overtime     = $this->input->post('tovertime');
            $this->model->log          = $this->session->userdata('log');
            
            $this->model->save();
            $this->session->set_flashdata('message', "One $this->title has successfully updated!");
//            redirect($this->title.'/update/'.$this->session->userdata('curid'));
            
            echo 'true'; 
        }
        else
        {
//            $this->load->view('attendance_update', $data);
           echo validation_errors();
//            redirect($this->title.'/update/'.$this->session->userdata('curid'));
        }
        
        $this->session->unset_userdata('curid');
    }
    
    public function valid_absence($nip)
    {
        $employee = $this->employee->get_id_by_nip($nip);
        
        $this->model->where('month', $this->input->post('cmonth'));
        $this->model->where('year', $this->input->post('tyear'));
        $val = $this->model->where('employee_id', $employee)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('valid_absence', "Employee [$nip] period ".$this->input->post('cmonth')." - ".$this->input->post('tyear')." : already registered..!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    public function valid_period($month=0,$year=0)
    {
        $p = new Period();
        $p->get();

        if ( intval($p->month) != intval($month) || intval($p->year) != intval($year) )
        {
            $this->form_validation->set_message('valid_period', "Invalid Period.!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
     public function valid_import($month=0,$year=0)
    {
        $val = $this->model->where('month', $month)->where('year', $year)->count();
        if ($val > 0)
        {
            $this->form_validation->set_message('valid_import', "Invalid Import.!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    public function valid_year($year=0)
    {
        $p = new Period();
        $p->get();

        if ( intval($p->year) != intval($year) )
        {
            $this->form_validation->set_message('valid_year', "Invalid Year.!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    
    public function report()
    {
        $this->acl->otentikasi2($this->title);

        $data['title'] = $this->properti['name'].' | Administrator Report '.ucwords($this->modul['title']);
        $data['h2title'] = 'Report '.$this->modul['title'];
	$data['form_action'] = site_url($this->title.'/report_process');
        $data['link'] = array('link_back' => anchor($this->title,'<span>back</span>', array('class' => 'back')));
        $data['dept'] = $this->dept->combo_all(); 
        
        $this->load->view('attendance_report_panel', $data);
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
        
        $data['log'] = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
        
        $data['month'] = $this->input->post('cmonth');
        $data['year'] = $this->input->post('tyear');
                
        $data['results'] = $this->am->report(null, $this->input->post('cmonth'), $this->input->post('tyear'))->result();
        
        $this->load->view('attendance_report', $data);
    }

}

?>