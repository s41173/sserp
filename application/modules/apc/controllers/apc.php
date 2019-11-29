<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Apc extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->properti = $this->property->get();
        
        $this->load->model('Apc_model', '', TRUE);
        $this->load->model('Apc_trans_model', '', TRUE);

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->unit = new Unit_lib();
        $this->user = new Admin_lib();
        $this->tax = new Tax_lib();
        $this->cost = new Cost_lib();
        $this->ps = new Period_lib();
        $this->ledger = new Cash_ledger_lib();
        $this->journalgl = new Journalgl_lib();
        $this->account = new Account_lib();
        $this->model = new Apcmodel();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $cost,$ps, $model, $ledger, $account;
    private $user,$tax,$journalgl,$currency,$unit,$api,$acl;
    
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
        
        if(!$date){ $result = $this->Apc_model->get_last($this->limitx, $this->offsetx)->result(); }
        else{ $result = $this->Apc_model->search($dates)->result(); }
        $resx = null;
	foreach($result as $res)
	{
           $resx[] = array ("id"=>$res->id, "no"=>$res->no, "notes"=>$res->notes, "dates"=>tglin($res->dates), "desc"=>$res->desc, 
                            "currency"=>$res->currency, "account"=>$this->get_acc($res->account), 
                            "posted"=>$res->approved, "amount"=>floatval($res->amount), "log"=> $this->decodedd->log);
	}
        $data['result'] = $resx; $data['counter'] = $this->Apc_model->counter(); $data['asset'] = $this->account->combo_asset();
        $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    } 
    
    private function get_acc($acc){ return $this->account->get_code($acc).' : '.$this->account->get_name($acc); }
        

    private function status($val=null)
    { switch ($val) { case 0: $val = 'D'; break; case 1: $val = 'S'; break; } return $val; }
//    ===================== approval ===========================================

    private function post_status($val)
    {
       if ($val == 0) {$class = "notapprove"; }
       elseif ($val == 1){$class = "approve"; }
       return $class;
    }

    function confirmation($pid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->Apc_model->valid_add_trans($pid, $this->title) == TRUE){
        $ap = $this->model->where('id',$pid)->get();

        if ($ap->approved == 1){ $this->reject("transaction already approved..!"); }
        if ($ap->amount == 0){ $this->reject("transaction has no value..!"); }
        else
        {
            $total = $ap->amount;
            if ($total == 0){ $this->reject("transaction has no value..!"); }
            else
            {
                $this->model->approved = 1;
                $this->model->status = 1;
                $this->model->save();
                $this->model->clear();
                
                $ap1 = $this->model->where('id',$pid)->get();

                //  create journal gl
                
                $cm = new Control_model();
                $account  = $ap1->account;                
                
                // create journal- GL
                $this->journalgl->new_journal('0'.$ap1->no,$ap1->dates,'DJC',$ap1->currency,$ap1->notes,$ap1->amount, $this->session->userdata('log'));
               
                $transs = $this->Apc_trans_model->get_last_item($pid)->result(); 
                $dpid = $this->journalgl->get_journal_id('DJC','0'.$ap1->no);
                
                foreach ($transs as $trans) 
                {
//                    $this->cost->get_acc($trans->cost);
                    $this->journalgl->add_trans($dpid,$this->cost->get_acc($trans->cost),$trans->amount,0); // biaya
                }
                
                $this->journalgl->add_trans($dpid,$account,0,$ap1->amount); // kas, bank, kas kecil
                $this->error = "DJC-00$ap1->no confirmed..!";
            }
        }
        }else { $this->reject_token(); }
        $this->response();
    }

//    ================================== approval ===========================================


    function delete($uid)
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->Apc_model->valid_add_trans($uid, $this->title) == TRUE){ 
        $val = $this->Apc_model->get_by_id($uid)->row();

        if ($val->approved == 1){ $this->void($uid); }
        elseif ( $this->valid_period($val->dates) == TRUE ) // cek journal harian sudah di approve atau belum
        {            
            // remove cash ledger
            $this->ledger->remove($val->dates, "DJC-00".$val->no);
            
            $this->Apc_trans_model->delete_po($uid);
            $this->Apc_model->force_delete($uid);
            $this->error = "$this->title successfully removed..!";
        }
        elseif ( $this->valid_period($val->dates) != TRUE ){ $this->reject('Invalida Period'); }
        else{ $this->reject("transaction can't removed, journal approved..!"); } 
       }else { $this->reject_token(); }
       $this->response();
    }
    
    private function void($uid)
    {
       $val = $this->model->where('id',$uid)->get();
       if ($this->valid_period($val->dates) == TRUE)
       {
           $this->journalgl->remove_journal('DJC', '0'.$val->no); // journal gl
           
           $val->approved = 0;
           $val->status = 0;
           $val->save();
           $this->error = "transaction successfull voided..!";
       }
       else { $this->reject("Invalid Period..!");  }
    }
        
    function add()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tno', 'DJ - No', 'required|numeric|callback_valid_no');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tdesc', 'Decsription', '');
        $this->form_validation->set_rules('cacc', 'Account', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
           $trans = array('no' => $this->input->post('tno'), 'status' => 0,
                          'dates' => $this->input->post('tdate'), 'account' => $this->input->post('cacc'), 
                          'currency' => "IDR", 'notes' => $this->input->post('tnote'), 
                          'desc' => $this->input->post('tdesc'), 'user' => $this->decodedd->userid,
                          'log' => $this->decodedd->log, 'created' => date('Y-m-d H:i:s'));
            
            if ($this->Apc_model->add($trans) == true){ $this->error = 'transaction successfully saved..!'; }else{ $this->reject(); }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response();
    }


//    ======================  Item Transaction   ===============================================================

    function add_item($pid=null)
    {   
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->Apc_model->valid_add_trans($pid, $this->title) == TRUE){
           
            $this->form_validation->set_rules('ccost', 'Cost Type', 'required');
            $this->form_validation->set_rules('tstaff', 'Staff', 'required');
            $this->form_validation->set_rules('tamount', 'Amount', 'required|numeric');

            if ($this->valid_transaction($pid) == TRUE && $this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE)
            {
                $pitem = array('apc_id' => $pid, 'cost' => $this->input->post('ccost'),
                               'notes' => $this->input->post('tnotes'),
                               'staff' => $this->input->post('tstaff'),
                               'amount' => $this->input->post('tamount'));

                $this->Apc_trans_model->add($pitem);
                $this->update_trans($pid);
                $this->error = 'Transaction Posted';
            }
            elseif ( $this->valid_confirmation($pid) != TRUE ){ $this->reject("Can't change value - Journal approved..!"); }
            elseif ( $this->valid_transaction($pid) != TRUE ){ $this->reject("Can't change value - Transaction Not Created..!"); }
            else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response();
    }
    
    function get_item($id)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->Apc_trans_model->valid_add_trans($id, $this->title) == TRUE){
         $this->output = $this->Apc_trans_model->get_by_id($id)->row();  
       }else{ $this->reject_token(); }
       $this->response('content');
    }
    
    function edit_item($id)
    {
      if ($this->acl->otentikasi2($this->title) == TRUE && $this->Apc_trans_model->valid_add_trans($id, $this->title) == TRUE){
        $ap = $this->Apc_trans_model->get_by_id($id)->row();
        
        $this->form_validation->set_rules('tnotes', 'Notes', 'required');
        $this->form_validation->set_rules('ccost', 'Cost Type', 'required');
        $this->form_validation->set_rules('tstaff', 'Staff', 'required');
        $this->form_validation->set_rules('tamount', 'Amount', 'required|numeric');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($ap->apc_id) == TRUE)
        {
            $pitem = array('notes' => $this->input->post('tnotes'), 
                           'cost' => $this->input->post('ccost'),
                           'staff' => $this->input->post('tstaff'),
                           'amount' => $this->input->post('tamount'));
            
            $this->Apc_trans_model->update($id,$pitem);
            $this->update_trans($ap->apc_id);
            $this->error = 'Transaction Posted';
        }elseif ($this->valid_confirmation($ap->apc_id) != TRUE){ $this->reject("Can't change value - Journal approved..!"); }
      }else{ $this->reject_token(); }
      $this->response();
    }

    private function update_trans($pid)
    {
        $totals = $this->Apc_trans_model->total($pid);
        
        $this->model->where('id', $pid)->get();
        $this->model->amount = intval($totals['amount']);
        $this->model->save();
    }

    function delete_item($id)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->Apc_trans_model->valid_add_trans($id, $this->title) == TRUE){
            $pid = $this->Apc_trans_model->get_by_id($id)->row();
            if ($this->valid_confirmation($pid->apc_id) == TRUE){

            $val = $this->Apc_trans_model->get_by_id($id)->row();

            $this->Apc_trans_model->force_delete($id); // memanggil model untuk mendelete data
            $this->update_trans($val->apc_id);
            $this->error = 'Transaction removed..!';

            }else { $this->reject("Transaction posted, can't delete item"); }
        }else { $this->reject_token(); }
        $this->response();
    }
//    ==========================================================================================

    // Fungsi update untuk mengupdate db
    function update($pid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->Apc_model->valid_add_trans($pid, $this->title) == TRUE){
        
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tdesc', 'Description', '');
        $this->form_validation->set_rules('cacc', 'Account', 'required');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE)
        { 
            // cash ledger
            $val = $this->model->where('id',$pid)->get();
            $this->ledger->remove($val->dates, "DJC-00".$val->no);
            
            $this->model->where('id',$pid)->get();
            
            $this->model->dates    = $this->input->post('tdate');
            $this->model->account  = $this->input->post('cacc');
            $this->model->notes    = $this->input->post('tnote');
            $this->model->desc     = $this->input->post('tdesc');
            $this->model->user     = $this->decodedd->userid;
            $this->model->log      = $this->decodedd->log;
            $this->model->updated  = date('Y-m-d H:i:s');
            
            $this->ledger->add($this->model->acc, "DJC-00".$this->model->no, $this->model->currency, $this->model->dates, 0, $this->model->amount);
            $this->model->save();
            $this->update_trans($pid);
            $this->error = "Transaction data successfully updated!";
        }
        elseif ($this->valid_confirmation($pid) != TRUE){ $this->reject("Can't change value - Order approved..!"); }
        else{ $this->reject(validation_errors()); }
       }else{ $this->reject_token(); }
       $this->response();
    }

    public function valid_period($date=null)
    {
        $p = new Period();
        $p->get();

        $month = date('n', strtotime($date));
        $year  = date('Y', strtotime($date));

        if ( intval($p->month) != intval($month) || intval($p->year) != intval($year) )
        {
            if (cek_previous_period($month, $year) == TRUE){ return TRUE; }
            else { $this->form_validation->set_message('valid_period', "Invalid Period.!"); return FALSE; }
        }
        else {  return TRUE; }
    }
    
    public function valid_vendor($name)
    {
        if ($this->vendor->valid_vendor($name) == FALSE)
        {
            $this->form_validation->set_message('valid_vendor', "Invalid Vendor.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

   public function valid_no($no)
   {
        if ($this->Apc_model->valid_no($no) == FALSE)
        {
            $this->form_validation->set_message('valid_no', "Order No already registered.!");
            return FALSE;
        }
        else {  return TRUE; }
   }

    public function valid_confirmation($pid)
    {
        $ap = $this->model->where('id', $pid)->get();

        if ($ap->approved == 1)
        {
            $this->form_validation->set_message('valid_confirmation', "Can't change value - Order approved..!.!");
            return FALSE;
        }
        else { return TRUE; }
    }
    
    public function valid_transaction($id)
    {
        $val = $this->model->where('id', $id)->count();

        if ($val == 0)
        {
            $this->form_validation->set_message('valid_transaction', "Transaction Not Created...!");
            return FALSE;
        }
        else { return TRUE; }
    }

    public function valid_rate($rate)
    {
        if ($rate == 0)
        {
            $this->form_validation->set_message('valid_rate', "Rate can't 0..!");
            return FALSE;
        }
        else {  return TRUE; }
    }

// ===================================== PRINT ===========================================
    

   function get($id=null)
   {
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->Apc_model->valid_add_trans($id, $this->title) == TRUE){
       $ap = $this->model->where('id', $id)->get();

       $data['code'] = $ap->no;
       $data['date'] = tglin($ap->dates);
       $data['notes'] = $ap->notes;
       $data['user'] = $this->user->get_username($ap->user);
       $data['currency'] = $ap->currency;
       $data['docno'] = $ap->docno;
       $data['log'] = $this->decodedd->log;
       $data['account'] = $this->get_acc($ap->account);

       $data['amount'] = floatval($ap->amount);
       $terbilang = $this->load->library('terbilang');
       if ($ap->currency == 'IDR')
       { $data['terbilang'] = ucwords($terbilang->baca($ap->amount)).' Rupiah'; }
       else { $data['terbilang'] = ucwords($terbilang->baca($ap->amount)); }
       
       if($ap->approved == 1){ $stts = 'A'; }else{ $stts = 'NA'; }
       $data['stts'] = $stts;
       $data['accounting'] = $this->properti['accounting'];
       $data['manager'] = $this->properti['manager'];

       $items = null;
       foreach ($this->Apc_trans_model->get_last_item($ap->id)->result() as $res) {
            $items[] = array ("id"=>$res->id, "note"=>$res->notes, "account"=> $this->account->get_code($this->cost->get_acc($res->cost)), "amount"=>floatval($res->amount));    
       }
       $data['items'] = $items;  $this->output = $data;
       
       }else{ $this->reject_token(); }
       $this->response('content');
   }
   
// ===================================== PRINT ===========================================

// ====================================== REPORT =========================================

    function report()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){ 
       
        $cur = 'IDR';
        $type = $this->input->post('ctype');
        $acc = $this->account->get_id_code($this->input->post('titem'));
        
        $start = $this->input->post('start');
        $end = $this->input->post('end');

        $data['currency'] = strtoupper($cur);
        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        $data['rundate'] = tgleng(date('Y-m-d'));
        $data['log'] = $this->decodedd->log;

//        Property Details
        $data['company'] = $this->properti['name'];
        $report = $this->Apc_model->report($acc,$cur,$start,$end)->result();
        $report_cat = $this->Apc_model->report_category($acc,$cur,$start,$end)->result();
        $items = null;
        
        if ($this->input->post('ctype') == 0){
            
            foreach ($report as $res) {
              $items[] = array ("id"=>$res->id, "no"=> $res->no, "code"=>'DJC-00'.$res->no, "notes"=>$res->notes, "dates"=>tglin($res->dates), "desc"=>$res->desc,
                                "currency"=>$res->currency, "account"=>$this->get_acc($res->account), 
                                "posted"=>$res->approved, "amount"=>floatval($res->amount), "log"=> $this->decodedd->log);
            }
        }elseif($this->input->post('ctype') == 1 ){
            foreach ($report_cat as $res) {
              $items[] = array ("id"=>$res->id, "no"=> $res->no, "code"=>'DJC-00'.$res->no, "dates"=>tglin($res->dates), "desc"=>$res->desc, 
                                "currency"=>$res->currency, "account"=>$this->get_acc($res->account), 
                                "posted"=>$res->approved, "notes"=>$res->notes, "staff"=>$res->staff, "amount"=>floatval($res->amount), "log"=> $this->decodedd->log);   
            }
        }
        $data['items'] = $items; $this->output =$data;
        
        }else { $this->reject_token(); }
        $this->response('content');
    }


// ====================================== REPORT =========================================
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->Apc_model->closing(); $this->Apc_trans_model->closing(); $this->Apc_trans_model->closing_trans(); }
}

?>