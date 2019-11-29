<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Transfer extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Transfer_model', 'model', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency  = $this->load->library('currency_lib');
        $this->user      = $this->load->library('admin_lib');
        $this->journalgl = $this->load->library('journalgl_lib');
        $this->account = new Account_lib();
        $this->ledger  = new Cash_ledger_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $account, $ledger, $api, $acl;
    private $vendor,$user,$currency,$journalgl;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $datax = (array)json_decode(file_get_contents('php://input')); 
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
            
        $date = null;
        if (isset($datax['date'])){ $date = $datax['date']; }
        
        if(!$date){ $result = $this->model->get_last_transfer($this->limitx, $this->offsetx)->result(); }
        else{ $result = $this->model->search($date)->result(); }
        $resx = null;
	foreach($result as $res)
	{
           $resx[] = array ("id"=>$res->id, "no"=>$res->no, "notes"=>$res->notes, "dates"=>tglin($res->dates), 
                                    "currency"=>$res->currency, "from"=>$this->get_acc($res->from), "to"=>$this->get_acc($res->to), 
                                    "posted"=>$res->approved, "amount"=>$res->amount);
	}
        $data['result'] = $resx; $data['counter'] = $this->model->counter(); $data['asset'] = $this->account->combo_asset();
        $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    }
    
    private function get_acc($acc){ return $this->account->get_code($acc).' : '.$this->account->get_name($acc);}

    function confirmation($pid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
        $transfer = $this->model->get_transfer_by_id($pid)->row();

        if ($transfer->approved == 1){ $this->reject("$this->title already approved..!"); }
        else
        {
            $total = $transfer->amount;
            if ($total == 0){ $this->reject("$this->title has no value..!"); }
            else
            {
                $data = array('approved' => 1);
                $this->model->update_id($pid, $data);

                //  create journal
                $cm = new Control_model();
                
                $from = $transfer->from; 
                $to = $transfer->to;
                
                 // add cash ledger
                $this->ledger->remove($transfer->dates, "TR-00".$transfer->no);
                $this->ledger->add($this->get_acc_type($transfer->to), "TR-00".$transfer->no, $transfer->currency, $transfer->dates, $transfer->amount, 0);

                $this->journalgl->new_journal('0'.$transfer->no,$transfer->dates,'TR',$transfer->currency, 'Transfer from : '.$this->acc_type($transfer->from).' to '.$this->acc_type($transfer->to), $transfer->amount, $this->decodedd->log);
                $dpid = $this->journalgl->get_journal_id('TR','0'.$transfer->no);
                
                $this->journalgl->add_trans($dpid,$to,$transfer->amount,0); // to
                $this->journalgl->add_trans($dpid,$from,0,$transfer->amount); // from
                $this->error = "TR-0$transfer->no confirmed..!";
            }
        }
        }else { $this->reject_token(); }
        $this->response();
    }
    
    private function get_acc_type($account)
    {
       if ($this->account->get_classi($account) == 7) { return 'cash'; }
       elseif ($this->account->get_classi($account) == 8) { return 'bank'; }
       else { return 'bank'; }
    }

//    ===================== approval ===========================================

    function delete($uid=null)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        $transfer = $this->model->get_by_id($uid)->row();

        if ($this->valid_period($transfer->dates) == TRUE ) // cek journal harian sudah di approve atau belum
        {
            if ($transfer->approved == 1)
            {
              $this->ledger->remove($transfer->dates, "TR-00".$transfer->no); // cash ledger    
              $this->journalgl->remove_journal('TR', '0'.$transfer->no);
              $data = array('approved' => 0);
              $this->model->update_id($uid, $data);
              $this->error = "1 $this->title successfully rollback..!";
            }
            else 
            {  $this->ledger->remove($transfer->dates, "TR-00".$transfer->no); // cash ledger  
               if ($this->model->force_delete($uid) == true){ $this->error = "1 $this->title successfully removed..!"; } 
            }
        }
        else{ $this->reject("1 $this->title can't removed, journal approved..!"); } 
        }else { $this->reject_token(); }
        $this->response();
    }

    function add()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tno', 'GJ - No', 'required|numeric|callback_valid_no');
        $this->form_validation->set_rules('tdate', 'Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tamount', 'Amount', 'required|numeric');
        $this->form_validation->set_rules('cfrom', 'From', 'required');
        $this->form_validation->set_rules('cto', 'To', 'required|callback_valid_acc');

        if ($this->form_validation->run($this) == TRUE)
        {
            $transfer = array('no' => $this->input->post('tno'), 'from' => $this->input->post('cfrom'), 'to' => $this->input->post('cto'),
                              'dates' => $this->input->post('tdate'), 'currency' => $this->input->post('ccurrency'), 'notes' => $this->input->post('tnote'),
                              'amount' => $this->input->post('tamount'), 'log' => $this->decodedd->log);
            
            if ($this->model->add($transfer) == true){ $this->error = $this->title.' successfully saved..!'; }else{ $this->reject(); }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response();
    }

    function get($uid=null)
    {        
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
         $this->output = $this->model->get_by_id($uid)->row_array();
       }else { $this->reject_token(); }
       $this->response('content');
    }
    
    
    // Fungsi update untuk mengupdate db
    function update($uid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tdate', 'Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tamount', 'Amount', 'required|numeric');
        $this->form_validation->set_rules('cfrom', 'From', 'required');
        $this->form_validation->set_rules('cto', 'To', 'required|callback_valid_acc');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($uid) == TRUE)
        {
            $transfer = array('from' => $this->input->post('cfrom'), 'to' => $this->input->post('cto'),
                              'dates' => $this->input->post('tdate'), 'currency' => $this->input->post('ccurrency'), 'notes' => $this->input->post('tnote'),
                              'amount' => $this->input->post('tamount'), 'log' => $this->decodedd->log);

            if ($this->model->update_id($uid, $transfer) == true){ $this->error = 'Data successfully saved..'; }else{ $this->reject(); }
        }
        elseif ($this->valid_confirmation($uid) != TRUE){ $this->reject("Journal approved, can't updated..!"); }
        else{ $this->reject(validation_errors()); }
        }else { $this->reject_token(); }
        $this->response();
    }
    
    public function valid_confirmation($id)
    {
        $val = $this->model->get_by_id($id)->row();

        if ($val->approved == 1)
        {
            $this->form_validation->set_message('valid_confirmation', "Can't change value - Journal approved..!.!");
            return FALSE;
        }
        else {  return TRUE; }
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

    public function valid_no($no)
    {
        if ($this->model->valid_no($no) == FALSE)
        {
            $this->form_validation->set_message('valid_no', "Order No already registered.!");
            return FALSE;
        }
        else {  return TRUE; }
    }

    public function valid_acc($val)
    {
        $from = $this->input->post('cfrom');
        if ( $val == $from )
        {
            $this->form_validation->set_message('valid_acc', "Invalid Account.!");
            return FALSE;
        }
        else { return TRUE; }
    }
    

// ===================================== PRINT ===========================================

   function invoice($id=null)
   {
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($id, $this->title) == TRUE){
       $ap = $this->model->get_by_id($id)->row();

       $data['h2title'] = 'Print Invoice '.$this->modul['title'];

       $data['pono'] = $ap->no;
       $data['podate'] = tglin($ap->dates);
       $data['notes'] = $ap->notes;
       $data['from'] = $this->acc_type($ap->from);
       $data['to'] = $this->acc_type($ap->to);
       $data['currency'] = $ap->currency;
       $data['log'] = $this->decodedd->log;

       $data['amount'] = $ap->amount;
       $terbilang = $this->load->library('terbilang');
       if ($ap->currency == 'IDR')
       { $data['terbilang'] = ucwords($terbilang->baca($ap->amount)).' Rupiah'; }
       else { $data['terbilang'] = ucwords($terbilang->baca($ap->amount)); }
       
       if($ap->approved == 1){ $stts = 'A'; }else{ $stts = 'NA'; }
       $data['stts'] = $stts;
       $this->output = $data;
       
       }else { $this->reject_token(); }
       $this->response('content');
   }
   
    private function acc_type($val=null){ return $this->account->get_code($val).'-'.$this->account->get_name($val); }
   
// ===================================== PRINT ===========================================

// ====================================== REPORT =========================================

    function report()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){
            $cur = $this->input->post('ccurrency');
            $start = $this->input->post('start');
            $end = $this->input->post('end');

            $data['currency'] = $cur;
            $data['start'] = tglin($start);
            $data['end'] = tglin($end);
            $data['rundate'] = tglin(date('Y-m-d'));
            $data['log'] = $this->decodedd->log;

    //        Property Details
            $data['company'] = $this->properti['name'];

            $result = null;
            foreach ($this->model->report($cur,$start,$end)->result() as $res) {
               $result[] = array ("id"=>$res->id, "no"=>$res->no, "notes"=>$res->notes, "dates"=>tglin($res->dates), 
                                    "currency"=>$res->currency, "from"=>$this->get_acc($res->from), "to"=>$this->get_acc($res->to), 
                                    "posted"=>$res->approved, "amount"=>$res->amount);
            }
            $data['result'] = $result;  $this->output = $data;
        }else { $this->reject_token(); }
        $this->response('content');
    }

// ====================================== REPORT =========================================
    
// ====================================== CLOSING ======================================
   function reset_process(){ $this->model->closing(); }     

}

?>