<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Loan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Loan_model', 'lm', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->user = $this->load->library('admin_lib');
//        $this->dept = new Dept_lib();
        $this->employee = new Employee_lib();
        $this->division = new Division_lib();
        $this->model = new Loans();
    }

    private $properti, $modul, $title,$dept,$employee;
    private $user,$currency,$model,$division;

    function index()
    {
       $this->lm->delete_amount(); 
       $this->get_last();
    }

    public function getdatatable($search=null,$employee='null')
    {
        if(!$search){ $result = $this->lm->get($this->modul['limit'])->result(); }
        else{ $result = $this->lm->search($employee)->result(); }
	
        $output = null;
        if ($result){
            
         foreach($result as $res)
	 { 
	   $output[] = array ($res->id, $this->employee->get_name($res->employee_id), $res->currency, idr_format($res->amount), $res->employee_id);
	 } 
         
        $this->output
         ->set_status_header(200)
         ->set_content_type('application/json', 'utf-8')
         ->set_output(json_encode($output))
         ->_display();
         exit;  
        }
    }
    
    public function chart()
    {   
        $data = array(
                    array("label" => "Loan Total", "legendText" => "Loan", "y" => floatval($this->lm->total_chart()))
                );

       echo json_encode($data, JSON_NUMERIC_CHECK);
    }
    
    function get_last()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords('Product Manager');
        $data['h2title'] = 'Employee Loan';
        $data['main_view'] = 'loan_view';
	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all');
        $data['form_action_report'] = site_url($this->title.'/report_process');
        $data['link'] = array('link_back' => anchor('main/','Back', array('class' => 'btn btn-danger')),
                              'loantrans' => anchor('loan_trans','Loan Transaction', array('class' => 'btn btn-success')));

        $data['division'] = $this->division->combo();
	// ---------------------------------------- //

        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table id="datatable-buttons" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('#','No', 'Employee', 'Currency', 'Amount', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url($this->title.'/getdatatable');
        $data['graph'] = site_url($this->title.'/chart/');
            
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }
    
    function details($uid)
    {
//        $this->acl->otentikasi2($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'loan_form';
	$data['form_action'] = site_url($this->title.'/update_process');
	$data['link'] = array('link_back' => anchor($this->title,'<span>back</span>', array('class' => 'back')));
        
        $data['log']     = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
        $data['address'] = $this->properti['address'];
        $data['phone1']  = $this->properti['phone1'];
        $data['phone2']  = $this->properti['phone2'];
        $data['fax']     = $this->properti['fax'];
        $data['website'] = $this->properti['sitename'];
        $data['email']   = $this->properti['email'];
        
        $loantrans = new Loan_trans_lib();
        $data['results'] = $loantrans->get_by_employee($uid);
        
        $this->model->where('employee_id', $uid)->get();
        $data['balance'] = $this->model->amount;
        
        // Employee
        $data['e_name'] = $this->employee->get_name($uid);
        $data['e_division'] = $this->division->get_name($this->employee->get_division_by_id($uid));
        
        $this->load->view('loan_detail', $data);
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
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_validating_name');
        $this->form_validation->set_rules('tbasic', 'Nick Name', 'required|numeric');
        $this->form_validation->set_rules('tconsumption', 'Consumption', 'required|numeric');
        $this->form_validation->set_rules('ttransport', 'Born Place', 'required|numeric');
        $this->form_validation->set_rules('tovertime', 'Born Date', 'required|numeric');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->where('id', $this->session->userdata('curid'))->get();
            
            $this->model->name           = $this->input->post('tname');
            $this->model->basic_salary   = $this->input->post('tbasic');
            $this->model->consumption    = $this->input->post('tconsumption');
            $this->model->transportation = $this->input->post('ttransport');
            $this->model->overtime       = $this->input->post('tovertime');  
            $this->model->save();
            $this->session->set_flashdata('message', "One $this->title has successfully updated!");
//            redirect($this->title.'/update/'.$this->session->userdata('curid'));
            
            echo 'true'; 
        }
        else
        {
//            $this->load->view('loan_update', $data);
           echo validation_errors();
//            redirect($this->title.'/update/'.$this->session->userdata('curid'));
        }
        
        $this->session->unset_userdata('curid');
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
    
    public function valid_name($name)
    {
        $val = $this->model->where('name', $name)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('valid_name', "Division - [$name] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function validating_name($name,$nip)
    {
        $this->model->where_not_in('id', $this->session->userdata('curid'));
        $val = $this->model->where('name', $name)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('validating_name', "Division [$name] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    
    public function report()
    {
        $this->acl->otentikasi2($this->title);

        $data['title'] = $this->properti['name'].' | Administrator Report '.ucwords($this->modul['title']);
        $data['h2title'] = 'Report '.$this->modul['title'];
	$data['form_action'] = site_url($this->title.'/report_process');
        $data['link'] = array('link_back' => anchor($this->title,'<span>back</span>', array('class' => 'back')));
        
        $this->load->view('loan_report_panel', $data);
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
        $data['type'] = $this->input->post('ctype');
                
        $data['results'] = $this->lm->report($this->input->post('tstart'),$this->input->post('tend'),$this->input->post('ctype'))->result();
        
        $this->load->view('loan_report', $data);
    }

}

?>