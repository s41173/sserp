<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Balance extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Balance_model', 'model', TRUE);
        $this->load->model('Account_model', 'am', TRUE);
        
        $this->properti = $this->property->get();
//        $this->acl->otentikasi();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->account = new Account_lib();
        $this->balancelib = new Balance_account_lib();
        $this->period = new Period_lib();
        
        $this->journal = new Journalgl_lib();
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $account,$balancelib, $period, $journal;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    private function fill_balance()
    {
       $ps = new Period();
       $bl = new Balances();
       $ps->get(); 
       
       if ($bl->where('month', $ps->start_month)->where('year', $ps->start_year)->count() == 0)
       {
          $accounts = $this->account->get();
          foreach ($accounts as $account){ $this->balancelib->fill($account->id, $ps->month, $ps->year, 0, 0); } 
       }
       
       $bl->where('account_id IS NULL')->delete();
    }
    
    function index()
    {
       if ($this->acl->otentikasi1($this->title) == TRUE){ 
            
         $result = $this->am->get_begin_saldo_account()->result();
         foreach($result as $res)
         {  
            $this->output[] = array ("id" => $res->id, "currency" => $res->currency,
                                     "code" => $res->code, "name" => $res->name, "balance" => $this->get_balance($res->id));
         }   
       }else{ $this->reject_token(); }
       $this->response('content');
    }   
    
    function reset(){
        if ($this->acl->otentikasi3($this->title) == TRUE){
            $ps = $this->period->get();
            if ( $ps->month == $ps->start_month && $ps->year == $ps->start_year ){
                if ($this->balancelib->reset() == true){ $this->error = 'Balance Reset Processed';
                }else{ $this->error = 'Balance Reset Unsuccessull'; $this->status = 401; }
            }else{ $this->error = 'error|Invalid Begin Period'; $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->response();
    }
    
    private function get_balance($acc=null)
    {
        $ps = $this->period->get();
        $bl = new Balances();
        
        $bl->where('account_id', $acc);
        $bl->where('month', $ps->start_month);
        $bl->where('year', $ps->start_year)->get(); 
        return floatval($bl->beginning);
    }

    function get($uid=0)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
            $acc_lib = new Account_lib();
            $role = array("id" => $uid, "code" => $acc_lib->get_code($uid), "name" => $acc_lib->get_name($uid), "balance" => $this->get_balance($uid));
        }else { $this->reject_token(); }
        $this->response('c');
    }

    // Fungsi update untuk mengupdate db
    function update($uid=0)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && isset($uid)){
               
	// Form validation
        $this->form_validation->set_rules('tbalance', 'Balance', 'required|numeric|callback_valid_setting['.$uid.']');

        if ($this->form_validation->run($this) == TRUE)
        {       
            $ps = $this->period->get();
            $bl = new Balances();

            $bl->where('account_id', $uid);
            $bl->where('month', $ps->month);
            $bl->where('year', $ps->year)->get();
                        
            $bl->beginning = $this->input->post('tbalance');
            $bl->vamount = $this->journal->calculate_account_amount($uid, $this->input->post('tbalance'));
            $bl->end = $this->input->post('tbalance');
            $bl->save();
            $this->update_historical();
            $this->error = 'Data successfully saved..!';
        }
        else{ $this->error = validation_errors(); $this->status = 401; }
        }else { $this->reject_token(); }
        $this->response();
    }
    
    private function update_historical()
    {
        $bl = new Balances();
        $ps = $this->period->get();
        $val = 0;
        
        $bl->select_sum('vamount');
        $bl->where('month', $ps->month);
        $bl->where('year', $ps->year)->get();
        $val = $bl->vamount;
        $bl->clear();        
        
        $bls = new Balances();
        $bls->where('account_id', 23);
        $bls->where('month', $ps->month);
        $bls->where('year', $ps->year)->get();
        
        $bls->beginning = $val;
        $bls->vamount = 0;
        $bls->save();
    }
    
    // fungsi validasi berlaku jika period sesuai dengan tanggal start
    public function valid_setting($val,$acid)
    {
        $ps = $this->period->get();
        if ($acid == 23)
        {
           $this->form_validation->set_message('valid_setting', "Balance can't change..!");
           return FALSE; 
        }
        elseif ( $ps->month != $ps->start_month || $ps->year != $ps->start_year )
        {
           $this->form_validation->set_message('valid_setting', "Period is not appropriate..!");
           return FALSE; 
        }
        else { return TRUE; }
    }

    private function previous_month()
    {
        $ps = $this->period->get();
        
        $prevmonth = 0;
        $prevyear = 0;
        
        if ($ps->start_month == 1){ $prevmonth = 12; $prevyear = intval($ps->start_year-1); }
        else { $prevmonth = intval($ps->start_month-1); $prevyear = $ps->start_year; }
        
        $totalday = get_total_days($prevmonth);
        
        return $totalday.'-'.$prevmonth.'-'.$prevyear;
    }


// ====================================== REPORT =========================================
    
}

?>