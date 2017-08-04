<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Division extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Division_model', 'dm', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->user = $this->load->library('admin_lib');
//        $this->dept = $this->load->library('dept_lib');
        $this->employee = new Employee_lib();
        $this->overtime = new Overtime_lib();
        $this->model = new Divisions();
    }

    private $properti, $modul, $title,$dept,$employee,$overtime;
    private $user,$currency,$model;

    function index()
    {
       $this->get_last();
    }
    
    public function getdatatable($search=null)
    {
        if(!$search){ $result = $this->dm->get($this->modul['limit'])->result(); }
        
        if ($result){
	foreach($result as $res)
	{
	   $output[] = array ($res->id, $res->name, $res->role, $res->basic_salary, $res->consumption, $res->transportation, $res->overtime);
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
        $data['main_view'] = 'division_view';
	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all');
        $data['link'] = array('link_back' => anchor('main/','Back', array('class' => 'btn btn-danger')));

        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table id="datatable-buttons" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('#','No', 'Name', 'Role', 'Basic Salary', 'Consumption', 'Transportation', 'Overtime', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url('division/getdatatable');
            
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }

    function add_process()
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){
        
	// Form validation
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_valid_name['.$this->input->post('crole').']');
        $this->form_validation->set_rules('crole', 'Role', 'required');
        $this->form_validation->set_rules('tbasic', 'Basic Salary', 'required|numeric');
        $this->form_validation->set_rules('tconsumption', 'Consumption', 'required|numeric');
        $this->form_validation->set_rules('ttransport', 'Transport', 'required|numeric');
        $this->form_validation->set_rules('tovertime', 'Overtime', 'required|numeric');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->name           = $this->input->post('tname');
            $this->model->role           = $this->input->post('crole');
            $this->model->basic_salary   = $this->input->post('tbasic');
            $this->model->consumption    = $this->input->post('tconsumption');
            $this->model->transportation = $this->input->post('ttransport');
            $this->model->overtime       = $this->input->post('tovertime');
            
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
        
        if ($this->employee->cek_relation($uid,'division_id') == TRUE && $this->overtime->cek_relation($uid,'division_id') == TRUE)
        {
           $this->dm->delete($uid);
           echo "true|1 $this->title successfully removed..!";
        }
        else{ $this->session->set_flashdata('message', "This $this->title still has employees..!"); }     
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
            if ($this->employee->cek_relation($cek[$i],'division_id') == TRUE && $this->overtime->cek_relation($cek[$i],'division_id') == TRUE)
            {
               $this->dm->delete($cek[$i]);
               echo "true|1 $this->title successfully removed..!";
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
    
    function update($uid)
    {
        $division = $this->dm->get_by_id($uid)->row_array();
        $this->session->set_userdata('langid', $uid);
        echo implode('|', $division);
    }

    // Fungsi update untuk mengupdate db
    function update_process()
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){

	// Form validation
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_validating_name['.$this->input->post('crole').']');
        $this->form_validation->set_rules('crole', 'Role', 'required');
        $this->form_validation->set_rules('tbasic', 'Basic Salary', 'required|numeric');
        $this->form_validation->set_rules('tconsumption', 'Consumption', 'required|numeric');
        $this->form_validation->set_rules('ttransport', 'Transport', 'required|numeric');
        $this->form_validation->set_rules('tovertime', 'Overtime', 'required|numeric');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->where('id', $this->session->userdata('langid'))->get();
            
            $this->model->name           = $this->input->post('tname');
            $this->model->role           = $this->input->post('crole');
            $this->model->basic_salary   = $this->input->post('tbasic');
            $this->model->consumption    = $this->input->post('tconsumption');
            $this->model->transportation = $this->input->post('ttransport');
            $this->model->overtime       = $this->input->post('tovertime');  
            $this->model->save();

            echo 'true|Data successfully saved..!';
        }
        else{ echo 'error|'.validation_errors(); }
        
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    public function valid_name($name,$role)
    {
        $val = $this->model->where('name', $name)->where('role', $role)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('valid_name', "Division - [$name : $role] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    public function validating_name($name,$role)
    {
        $this->model->where_not_in('id', $this->session->userdata('langid'));
        $val = $this->model->where('name', $name)->where('role', $role)->count();

        if ($val > 0)
        {
            $this->form_validation->set_message('validating_name', "Division [$name : $role] Already Registered..!");
            return FALSE;
        }
        else{ return TRUE; }
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
                
        $data['results'] = $this->dm->get($this->modul['limit'])->result();
        
        $this->load->view('division_report', $data);
    }
    
    public function excel()
    {
        //load our new PHPExcel library
        $excel = new Excel_lib();
        $query = $this->db->get('division');
        $excel->create($query, 'division');
    }
    
    public function pdf()
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
        $data['results'] = $this->dm->search()->result();
//        $this->load->view('division_report', $data, TRUE);
        
        // pdf
        $pdf = new Pdf();
        $pdf->create($this->load->view('division_report', $data, TRUE),'division');
    }

}

?>
