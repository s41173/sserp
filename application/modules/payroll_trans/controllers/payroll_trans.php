<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payroll_trans extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Payroll_trans_model', 'am', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->user = new Admin_lib();
        $this->employee = new Employee_lib();
        $this->model = new Payroll_transs();
        $this->payroll = new Payroll_lib();
        $this->division = new Division_lib();
        $this->loan = new Loan_lib();
    }

    private $properti, $modul, $title,$dept,$employee;
    private $user,$currency,$model,$payroll,$division,$loan;

    function index()
    {
       $this->get();
    }
    
    // ajax
    
        // payroll
    function get_salary()
    {
        $division = new Division_lib();
        $employee = new Employee_lib;
        $experience = new Experience_lib();
        $attendance = new Attendance_lib();
        
        $month = $this->input->post('month');
        $year  = $this->input->post('year');
        
        // attendance
        $att = $attendance->get($employee->get_id_by_nip($this->input->post('nip')), $month, $year);
        
        if ($att){ $res[0] = $att->presence; $res[1] = $att->overtime; }
        else{ $res[0] = 0; $res[1] = 0; }
//        // attendance
//        
        $salary = $division->get_salary_details($employee->get_division_by_nip($this->input->post('nip')));
        
        if ($salary)
        {
            if ($experience->count($employee->get_id_by_nip($this->input->post('nip'))) > 0)
            {
                $exbonus = $experience->get_amount($employee->get_id_by_nip($this->input->post('nip')));
                echo $salary->basic_salary.'|'.intval($exbonus->consumption*$res[0]).'|'.intval($exbonus->transportation).'|'.intval($salary->overtime*$res[1]).'|'.$exbonus->amount.'|'.
                     $exbonus->bonus.'|'.$exbonus->principal.'|'.$exbonus->principal_helper.'|'.$exbonus->head_department.'|'.
                     $exbonus->home_room.'|'.$exbonus->picket.'|'.$exbonus->insurance;
            }
            else
            {
               echo $salary->basic_salary.'|'.intval($salary->consumption*$res[0]).'|'.intval($salary->transportation).'|'.intval($salary->overtime*$res[1]).'|'.
                    '0|0|0|0|0|0|0|0'; 
            }
        }
        else { echo "0|0|0|0|0|0|0|0|0|0|0|0"; }
        
    }
    
    function get_loan()
    {
        $employee = $this->employee->get_id_by_nip($this->input->post('nip'));
        $loan = $this->loan->get($employee);
        if ($loan){ echo $loan; }else{ echo 0; }
    }
    
    // ajax done
    
    public function getdatatable($pid)
    {
        $result = $this->model->where('payroll_id',$pid)->get(); 
        $output = null;
        if ($result){
            
         foreach($result as $res)
	 { 
             
	   $output[] = array ($res->id, $res->payroll_id, $this->employee->get_name($res->employee_id).' - '.$this->employee->get_nip($res->employee_id), 
                              ucfirst($res->payment_type), idr_format($res->basic_salary), idr_format($res->experience), idr_format($res->consumption),
                              idr_format($res->transportation), idr_format($res->overtime), idr_format($res->bonus), idr_format($res->late), idr_format($res->loan),
                              idr_format($res->tax), idr_format($res->insurance), idr_format($res->other_discount), idr_format($res->amount), $res->user, $res->log,
                              $this->division->get_name($this->employee->get_division_by_id($res->employee_id))
                            );
	 } 
         
        $this->output
         ->set_status_header(200)
         ->set_content_type('application/json', 'utf-8')
         ->set_output(json_encode($output))
         ->_display();
         exit;  
        }
    }
    
    function get($pid=0)
    {
        $this->acl->otentikasi1($this->title);  
        if($pid!=0){ $this->session->set_userdata('payid',$pid); }
        else { $this->session->unset_userdata('payid');  redirect('payroll'); }
        
        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords('Payroll Transaction');
        $data['h2title'] = $this->modul['title'].' : PYJ-0'.$pid;
        $data['main_view'] = 'payroll_trans_view';
	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all');
        $data['form_action_report'] = site_url($this->title.'/report_process');
        $data['form_action_export'] = site_url($this->title.'/export_process');
        $data['link'] = array('link_back' => anchor('payroll','Back', array('class' => 'btn btn-danger')));
	// ---------------------------------------- //
        
        $data['currency'] = $this->currency->combo();
        $data['division'] = $this->division->combo_all();
        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table id="datatable-buttons" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('No', 'Payment Type', 'Division', 'Name', 'Amount', 'User', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url($this->title.'/getdatatable/'.$pid);
        $data['graph'] = site_url($this->title.'/chart/');
            
        $payroll = $this->payroll->get($this->session->userdata('payid'));
        $data['month'] = isset($payroll->month) ? $payroll->month : '0';
        $data['year']  =  isset($payroll->year) ? $payroll->year : '0';
        $data['dates'] = $payroll->end;
        
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }

    function xget($pid=0)
    {
        $this->acl->otentikasi1($this->title);
        if($pid!=0){ $this->session->set_userdata('payid',$pid); }
        else { $this->session->unset_userdata('payid');  redirect('payroll'); }

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'].' : PYJ-00'.$pid;
        $data['main_view'] = 'payroll_trans_view';
	$data['form_action'] = site_url($this->title.'/search/'.$pid);
        $data['link'] = array('link_back' => anchor('payroll','<span>back</span>', array('class' => 'back')));
        
        $data['dept'] = $this->dept->combo_all();        
	// ---------------------------------------- //
        $result = $this->model->where('payroll_id',$pid)->get();
        $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="1" class="tablemaster">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        $this->table->set_heading('No', 'Type', 'Payment Type', 'Name', 'Department', 'Amount', 'User', '#');

        $i = 0;
        foreach ($result as $res)
        {
            $this->table->add_row
            (
                ++$i, $res->type, $res->payment_type, $this->employee->get_name($res->employee_id).' - '.$this->employee->get_nip($res->employee_id), $this->dept->get_name($res->dept), number_format($res->amount), $res->user,
                anchor_popup($this->title.'/details/'.$res->id,'<span>print</span>',$this->atts).' '.
                anchor($this->title.'/delete/'.$res->id,'<span>delete</span>',array('class'=> 'delete', 'title' => 'delete' ,'onclick'=>"return confirm('Are you sure you will delete this data?')"))
            );
        }

        $data['table'] = $this->table->generate();
	$this->load->view('template', $data);
    }
    
    function search($pid=0)
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'payroll_trans_view';
	$data['form_action'] = site_url($this->title.'/search/'.$pid);
        $data['link'] = array('link_back' => anchor($this->title.'/get/'.$pid,'<span>back</span>', array('class' => 'back')));
        $data['dept'] = $this->dept->combo_all();
	// ---------------------------------------- //
        $result = $this->am->search($this->input->post('ctype'), $this->input->post('cdept'), $pid)->result();
  
        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="1" class="tablemaster">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('No', 'Type', 'Payment Type', 'Name', 'Department', 'Amount', 'User', '#');

        $i = 0;
        foreach ($result as $res)
        {
            $this->table->add_row
            (
                ++$i, $res->type, $res->payment_type, $this->employee->get_name($res->employee_id).' - '.$this->employee->get_nip($res->employee_id), $this->dept->get_name($res->dept), number_format($res->amount), $res->user,
                anchor_popup($this->title.'/details/'.$res->id,'<span>print</span>',$this->atts).' '.
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
        $this->form_validation->set_rules('cpayment', 'Payment Type', 'required|callback_valid_approval');
        $this->form_validation->set_rules('cdept', 'Department', 'callback_valid_dept');
        $this->form_validation->set_rules('tnip', 'Employee Nip', 'required|numeric|callback_valid_employee');
        $this->form_validation->set_rules('tbasic', 'Presence', 'required|numeric');
        $this->form_validation->set_rules('texperience', 'Experience', 'required|numeric');
        $this->form_validation->set_rules('tovertime', 'Overtime', 'required|numeric');
        $this->form_validation->set_rules('tlate', 'Late Charges', 'required|numeric');
        $this->form_validation->set_rules('tconsumption', 'Consumption', 'required|numeric');
        $this->form_validation->set_rules('ttransport', 'Transport', 'required|numeric');
        $this->form_validation->set_rules('tbonus', 'Bonus', 'required|numeric');
//        $this->form_validation->set_rules('tprincipal', 'Principal', 'required|numeric');
//        $this->form_validation->set_rules('tpks', 'Principal Helper', 'required|numeric');
//        $this->form_validation->set_rules('tkajur', 'Head Department', 'required|numeric');
//        $this->form_validation->set_rules('troom', 'Students Guardian', 'required|numeric');
//        $this->form_validation->set_rules('tpicket', 'Picket', 'required|numeric');
        $this->form_validation->set_rules('tloan', 'Loan', 'required|numeric|callback_valid_loan');
        $this->form_validation->set_rules('ttax', 'Tax', 'required|numeric');
        $this->form_validation->set_rules('tinsurance', 'Insurance', 'required|numeric');
        $this->form_validation->set_rules('tother', 'Other', 'required|numeric');
        $this->form_validation->set_rules('ttotal', 'Total', 'required|numeric|callback_valid_nol['.$this->input->post('tloan').']');
        
        
        if ($this->form_validation->run($this) == TRUE)
        {
//            if($this->input->post('ctype') == 'salary'){ $dept = 0; }else{ $dept = $this->input->post('cdept'); }
            
            $this->model->type             = 'salary';
            $this->model->payment_type     = $this->input->post('cpayment');
            $this->model->payroll_id       = $this->session->userdata('payid');
            $this->model->dept             = 0;
            $this->model->employee_id      = $this->employee->get_id_by_nip($this->input->post('tnip'));
            $this->model->basic_salary     = $this->input->post('tbasic');
            $this->model->experience       = $this->input->post('texperience');
            $this->model->consumption      = $this->input->post('tconsumption');
            $this->model->transportation   = $this->input->post('ttransport');
            $this->model->overtime         = $this->input->post('tovertime');
            $this->model->late             = $this->input->post('tlate');
            $this->model->bonus            = $this->input->post('tbonus');
//            $this->model->principal        = $this->input->post('tprincipal');
//            $this->model->principal_helper = $this->input->post('tpks');
//            $this->model->head_department  = $this->input->post('tkajur');
//            $this->model->home_room        = $this->input->post('troom');
//            $this->model->picket           = $this->input->post('tpicket');
            $this->model->loan             = $this->input->post('tloan');
            $this->model->tax              = $this->input->post('ttax');
            $this->model->insurance        = $this->input->post('tinsurance');
            $this->model->other_discount   = $this->input->post('tother');
            $this->model->user             = $this->session->userdata('username'); 
            $this->model->log              = $this->session->userdata('log');
//            
            $this->model->save();
            $salary = intval($this->input->post('tbasic')+$this->input->post('texperience'));
            
            $this->edit_payroll($this->session->userdata('payid'), $this->input->post('ctype'), $salary, 
                                $this->input->post('tbonus'), $this->input->post('tconsumption'), $this->input->post('ttransport'),
                                $this->input->post('tovertime'), $this->input->post('tlate'), $this->input->post('tloan'),
                                $this->input->post('tinsurance'), $this->input->post('ttax'), $this->input->post('tother'),
                                'add'
                               );
            $this->calculate();
            $this->edit_loan('paid',$this->input->post('tloan'),$this->employee->get_id_by_nip($this->input->post('tnip')), $this->session->userdata('log'));
//            $this->session->set_flashdata('message', "One $this->title data successfully saved!");

            echo 'true|'.$this->title.' successfully saved..!';
        }
        else{ echo "error|".validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    private function edit_loan($type,$amount,$employee,$log)
    {
        $loantrans = new Loan_trans_lib();
        $val = $this->payroll->get($this->session->userdata('payid'));
        if ($amount > 0)
        {
            if ($type == 'paid')
            {
              $loantrans->add($val->dates, $employee, $val->currency, $amount, $type, $val->acc, 'Payroll - '.$val->month.' - '.$val->year, $log);   
            }
            elseif ($type == 'borrow'){$loantrans->remove($val->dates, $employee, $val->currency, $amount);} 
        }
    }
    
    private function edit_payroll($pid,$type,$salary,$bonus,$consumption,$transport,$overtime,$late,$loan,$insurance,$tax,$other,$transtype)
    {
        $honor = 0;
        $balance = $this->calculate();
        if($type == 'honor'){ $honor = $salary; $salary=0; }
        $this->payroll->update_balance($pid, $honor, $salary, $bonus, $consumption, $transport, $overtime, $late, $loan, $insurance, $tax, $other, $balance, $transtype);
    }
    
    private function calculate()
    {
        $this->model->select_max('id');
        $id = $this->model->get();
        $id = intval($id->id);
        $val = $this->model->where('id', $id)->get();
        
        // salary
        $salary = intval($val->basic_salary + $val->experience + $val->consumption + $val->transportation + $val->overtime + $val->bonus +
                         $val->principal + $val->principal_helper + $val->head_department + $val->home_room + $val->picket);
        
        $loan = intval($val->loan + $val->late + $val->tax + $val->other_discount + $val->insurance);
        $this->model->where('id', $id)->get();
        $this->model->amount = $salary-$loan;
        $this->model->save();
        return $salary-$loan;
    }
    
    function delete($uid)
    {
       if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
        
        if ($this->valid_approval($this->session->userdata('payid')) == TRUE)
        {
           $this->model->where('id', $uid)->get();
           $employee = $this->model->employee_id;
           $loan     = $this->model->loan;
           
           $salary = intval($this->model->basic_salary + $this->model->experience + 
                            $this->model->principal + $this->model->principal_helper +
                            $this->model->head_department + $this->model->home_room + $this->model->picket);
           
           $this->edit_payroll($this->session->userdata('payid'), 
                               $this->model->type, $salary, $this->model->bonus, 
                               $this->model->consumption, $this->model->transportation,
                               $this->model->overtime, $this->model->late, 
                               $this->model->loan, $this->model->insurance,
                               $this->model->tax, $this->model->other_discount, 'min');
           
           $this->edit_loan('borrow', $loan, $employee, $this->session->userdata('log'));
//           
           $this->model->where('id', $uid)->get();
           $this->model->delete(); 
          
           echo "true|1 $this->title successfully removed..!";
        }
        else{ echo "error|Journal approved, can't remove..!"; }  
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    function details($uid)
    {
        $this->acl->otentikasi2($this->title);
        
        //properti
        $data['company'] = ucfirst($this->properti['name']);
        $data['address'] = $this->properti['address'];
        $data['phone1']  = $this->properti['phone1'];
        $data['phone2']  = $this->properti['phone2'];
        $data['fax']     = $this->properti['fax'];
        $data['website'] = $this->properti['sitename'];
        $data['email']   = $this->properti['email'];

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        
        $this->model->where('id', $uid)->get();
        $payroll = $this->payroll->get($this->model->payroll_id);
        
        $data['date']        = tglin($payroll->dates);
        $data['cur']         = strtoupper($payroll->currency);
        $data['period']      = get_month($payroll->month).' - '.$payroll->year;
        $data['nip']         = $this->employee->get_nip($this->model->employee_id);
        $data['employee']    = $this->employee->get_name($this->model->employee_id);
        $data['division']    = $this->division->get_name($this->employee->get_division_by_id($this->model->employee_id));
        $data['dept']        = '';
        $data['basic']       = number_format($this->model->basic_salary);
        $data['experience']  = number_format($this->model->experience);
        $data['consumption'] = number_format($this->model->consumption);
        $data['transportation']   = number_format($this->model->transportation);
        $data['overtime']         = number_format($this->model->overtime);
        $data['bonus']            = number_format($this->model->bonus);
        $data['principal']        = number_format($this->model->principal);
        $data['principal_helper'] = number_format($this->model->principal_helper);
        $data['head_department']  = number_format($this->model->head_department);
        $data['home_room']        = number_format($this->model->home_room);
        $data['picket']           = number_format($this->model->picket);
        $data['late']             = number_format($this->model->late);
        $data['loan']             = number_format($this->model->loan);
        $data['tax']              = number_format($this->model->tax);
        $data['insurance']        = number_format($this->model->insurance);
        $data['other_discount']   = number_format($this->model->other_discount);
        $data['amount']           = number_format($this->model->amount);
        $data['user']             = $this->model->user;
        $data['log']              = $this->model->log;
        
        if ($this->model->type == 'salary'){ $this->load->view('receipt', $data); }
        else { $this->load->view('honor_receipt', $data); }
        
    }
    
    public function valid_approval()
    {
        if ($this->payroll->cek_approval($this->session->userdata('payid')) == FALSE)
        {
            $this->form_validation->set_message('valid_approval', "Journal PYJ-0".$this->session->userdata('payid')." already approved..!");
            return FALSE;
        }
        else { return TRUE; }
    }
    
    public function valid_dept($dept)
    {
        $employee = $this->employee->get_id_by_nip($this->input->post('tnip'));
        $type     = $this->input->post('ctype');
        
        if ($type == 'honor')
        {
            $val = $this->model->where('employee_id', $employee)->where('dept', $dept)->where('payroll_id', $this->session->userdata('payid'))->count();
            if ($val > 0){ $this->form_validation->set_message('valid_dept', "Employee [".$this->input->post('tnip')."] already registered..!"); return FALSE; }
            else { return TRUE; }
        }
        else { return TRUE; }
    }
    
    public function valid_employee($nip)
    {
        $employee = $this->employee->get_id_by_nip($nip);
        $type = 'salary';
        if ($type == "salary")
        {
           $val = $this->model->where('employee_id', $employee)->where('payroll_id', $this->session->userdata('payid'))->where('type', 'salary')->count();
           if($val > 0)
           {
              $this->form_validation->set_message('valid_employee', "Employee [$nip] already registered....!!");
              return FALSE; 
           }
           else { return TRUE; }
        }
        else { return TRUE; }
    }
    
    public function valid_loan($payment)
    {
        $employee = $this->employee->get_id_by_nip($this->input->post('tnip'));
        if (!$this->loan->get($employee)){ $loan = 0; }else { $loan = $this->loan->get($employee); }
        
        if ($payment > $loan)
        {
            $this->form_validation->set_message('valid_loan', "$loan = Invalid Loan Payment..!");
            return FALSE;
        }
        else{ return TRUE; } 
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
    
    public function valid_nol($total,$loan)
    {
        if ($loan > 0){ return TRUE;}
        else 
        {
           if ($total < 0){ $this->form_validation->set_message('valid_nol', "No value..!"); return FALSE; }
           else { return TRUE; } 
        }  
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
        
        $data['company'] = $this->properti['name'];  
        
        $pay = $this->payroll->get($this->session->userdata('payid'));
        $data['month'] = $pay->month;
        $data['year']  = $pay->year;
        $data['payid'] = $this->session->userdata('payid');
        
//        if ($this->input->post('ctype') == 'salary'){ $dept = 0; }
//        elseif( $this->input->post('ctype') == 'honor' ){ $dept = $this->input->post('cdept'); }
//        else { $dept = $this->input->post('cdept'); }
           
        if ($this->input->post('cdivision'))
        {
           $data['results'] = $this->am->report($this->input->post('cdivision'), 'salary', 0, $this->session->userdata('payid'))->result(); 
        }
        else { $data['results'] = $this->am->search('salary', 0, $this->session->userdata('payid'))->result(); }
        
        if ($this->input->post('crtype') == 0){ $this->load->view('payroll_trans_report', $data); }
        elseif ($this->input->post('crtype') == 1){ $this->load->view('payroll_trans_summary', $data); }
        elseif ($this->input->post('crtype') == 2){ $this->load->view('payroll_trans_pivot', $data); }
        else if( $this->input->post('crtype') == 3){ $this->load->view('payroll_trans_slip', $data); }
        
    }
    
    public function export_process()
    {
        $data['log']     = $this->session->userdata('log');
        $data['company'] = $this->properti['name'];
        $data['address'] = $this->properti['address'];
        $data['phone1']  = $this->properti['phone1'];
        $data['phone2']  = $this->properti['phone2'];
        $data['fax']     = $this->properti['fax'];
        $data['website'] = $this->properti['sitename'];
        $data['email']   = $this->properti['technical_email'];
        $data['payment'] = strtoupper($this->input->post('cpayment'));
        $data['payid'] = $this->session->userdata('payid');
        
        $data['company'] = $this->properti['name'];  
        
        $pay = $this->payroll->get($this->session->userdata('payid'));
        $data['month'] = $pay->month;
        $data['year']  = $pay->year;
        $data['cur']   = $pay->currency;
        $data['dates'] = date('Ymd', strtotime($pay->dates));
        $data['notes'] = $pay->notes;
        $data['accname'] = $this->properti['account'];
        $data['accno'] = $this->properti['acc_no'];
        
        if ($this->input->post('ctype') == 'salary'){ $dept = 0; }
        elseif( $this->input->post('ctype') == 'honor' ){ $dept = $this->input->post('cdept'); }
        else { $dept = $this->input->post('cdept'); }
           
        if ($this->input->post('cdivision'))
        {
           $data['results'] = $this->am->report($this->input->post('cdivision'), 'salary', 0, $this->session->userdata('payid'), $this->input->post('cpayment'))->result(); 
           $amount = $this->am->sum_report($this->input->post('cdivision'), 'salary', 0, $this->session->userdata('payid'), $this->input->post('cpayment'))->row_array();
           $count = $this->am->count_report($this->input->post('cdivision'), 'salary', 0, $this->session->userdata('payid'), $this->input->post('cpayment'));
        }
        else 
        {
           $data['results'] = $this->am->search('salary', 0, $this->session->userdata('payid'), $this->input->post('cpayment'))->result(); 
           $amount = $this->am->sum_search('salary', 0, $this->session->userdata('payid'), $this->input->post('cpayment'))->row_array(); 
           $count = $this->am->count_search('salary', 0, $this->session->userdata('payid'), $this->input->post('cpayment')); 
        }
        
        $data['amount'] = intval($amount['amount']);
        $data['count'] = $count;
        
        $this->load->view('payroll_trans_export', $data);
        
    }
    
}

?>