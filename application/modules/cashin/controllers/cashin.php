<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cashin extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Cashin_trans_model', 'transmodel', TRUE);
        $this->load->model('Cashin_model', 'cmodel', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->user = new Admin_lib();
        $this->journalgl = new Journalgl_lib();
        $this->account = new Account_lib();
        $this->customer = new Customer_lib();
        $this->load->library('terbilang');
        $this->ledger  = new Cash_ledger_lib();

        $this->model = new Cashins();
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title,$model,$customer,$ledger, $api, $acl;
    private $user,$currency,$account,$journalgl;

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
        
        if(!$date){ $result = $this->cmodel->get_last($this->limitx, $this->offsetx)->result(); }
        else{ $result = $this->cmodel->search($date)->result(); }
        $resx = null;
	foreach($result as $res)
	{
           $resx[] = array ("id"=>$res->id, "no"=>$res->no, "notes"=>$res->notes, "dates"=>tglin($res->dates), "desc"=>$res->desc, 
                                    "currency"=>$res->currency, "customer"=>$this->customer->get_name($res->customer), "account"=>$this->get_acc($res->acc), 
                                    "posted"=>$res->approved, "amount"=>$res->amount, "log"=> $this->decodedd->log);
	}
        $data['result'] = $resx; $data['counter'] = $this->counter(); $data['asset'] = $this->account->combo_asset();
        $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    } 
    
    private function get_acc($acc)
    {
        return $this->account->get_code($acc).' : '.$this->account->get_name($acc);
    }

    function confirmation($pid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->cmodel->valid_add_trans($pid, $this->title) == TRUE){
        $cash = $this->model->where('id', $pid)->get();

        if ($cash->approved == 1) { $this->reject("$this->title already approved..!"); }
        elseif ($cash->amount == 0){ $this->reject("$this->title has no value..!");  }
        elseif ($this->valid_period($cash->dates) == FALSE ){ $this->reject("$this->title has invalid period..!"); }
        else
        {
            // tambah fungsi calculate balance account
            //$this->calculate_account_balance($cash->id);
            $this->model->approved = 1;
            $this->model->save();
            $this->model->clear();
            $cash1 = $this->model->where('id', $pid)->get();
            $transs = $this->transmodel->get_last_item($pid)->result();
             
            // add cash ledger
            $this->ledger->remove($cash1->dates, "CR-000".$cash1->no);
            $this->ledger->add($this->get_acc_type($cash1->acc), "CR-000".$cash1->no, $cash1->currency, $cash1->dates, $cash1->amount, 0);
            $account  = $cash1->acc;
            
             $cm = new Control_model();
        
             $this->journalgl->new_journal('0'.$cash1->no, $cash1->dates,'CIN', $cash1->currency, 'Payment from : '.$this->customer->get_name($cash1->customer), $cash1->amount, $this->session->userdata('log'));
             $dpid = $this->journalgl->get_journal_id('CIN','0'.$cash1->no);
               
             foreach ($transs as $trans) 
             {
                 $this->journalgl->add_trans($dpid,$trans->account_id,0,$trans->balance); // kas, bank, kas kecil ( credit )
             }
             $this->journalgl->add_trans($dpid,$account,$cash1->amount,0); // kas, bank, kas kecil ( debit )
             $this->error = "$this->title CR-000$cash->no confirmed..!";
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


    function delete($uid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->cmodel->valid_add_trans($uid, $this->title) == TRUE){ 
        $val = $this->model->where('id', $uid)->get();

        if ( $this->valid_period($val->dates) == TRUE )
        { 
           if ($val->approved == 1)
           {
             $this->ledger->remove($val->dates, "CR-000".$val->no); // cash ledger      
             $this->journalgl->remove_journal('CIN', '0'.$val->no);
             $val->approved = 0;
             $val->save();
             $this->error = "$this->title successfully rollback..!";
           }
           else
           {
             $this->transmodel->delete_po($uid);
             $val->delete();
             $this->session->set_flashdata('message', "1 $this->title successfully removed..!");
             $this->error = "$this->title successfully removed..!";
           }
        }
        else{ $this->reject("Can't removed, invalid period..!"); } 
        }else { $this->reject_token(); }
       $this->response();
    }

    private function counter()
    {
        $res = 0;
        if ( $this->model->count() > 0 )
        {
           $this->model->select_max('no')->get();
           $res = intval($this->model->no+1);
        }
        else{ $res = 1; }
        return $res;
    }
    
    private function max_id()
    {
        $res = 0;
        if ( $this->model->count() > 0 )
        {
           $this->model->select_max('id')->get();
           $res = intval($this->model->id);
        }
        else{ $res = 1; }
        return $res;
    }

    function add()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('ccustomer', 'Customer', 'required');
        $this->form_validation->set_rules('tno', 'No', 'required|numeric|callback_valid_no');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('cacc', 'Account', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $this->model->customer = $this->input->post('ccustomer');
            $this->model->no       = $this->input->post('tno');
            $this->model->acc      = $this->input->post('cacc');
            $this->model->dates    = $this->input->post('tdate');
            $this->model->currency = $this->input->post('ccurrency');
            $this->model->notes    = $this->input->post('tnote');
            $this->model->desc     = $this->input->post('tdesc');
            $this->model->log      = $this->decodedd->log;
            $this->model->created  = date('Y-m-d H:i:s');

            $this->model->save();
            $this->error = "One $this->title data successfully saved!|";
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response();
    }
    
    private function valid_add_trans($id)
    {
        if (!$id){ redirect($this->title); }
        $cash = $this->model->where('id',$id)->get();
        if (!$cash){ redirect($this->title); }
    }
    
    function get($id=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->cmodel->valid_add_trans($id, $this->title) == TRUE){
        
        $cash = $this->model->where('id',$id)->get();
        
        $data['code'] = $cash->no;
        $data['dates'] = tglin($cash->dates);
        $data['customerid'] = $cash->customer;
        $data['customer'] = $this->customer->get_name($cash->customer);
        $data['currency'] = $cash->currency;
        $data['note'] = $cash->notes;
        $data['desc'] = $cash->desc;
        $data['account_id'] = $cash->acc;
        $data['account'] = $this->get_acc($cash->acc);
        $data['total'] = floatval($cash->amount);
        
        $items = null;
        foreach ($this->transmodel->get_last_item($cash->id)->result() as $res) {
            $items[] = array ("id"=>$res->id, "account"=> $this->get_acc($res->account_id), "amount"=>$res->balance);    
        }
        $data['items'] = $items;
        $this->output = $data;
        
        }else { $this->reject_token(); }
        $this->response('content');
    }


//    ======================  Item Transaction   ===============================================================

    function add_item($po=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->cmodel->valid_add_trans($po, $this->title) == TRUE){ 
        $this->form_validation->set_rules('titem', 'Item Name', 'required');
        $this->form_validation->set_rules('tcredit', 'Credit', 'required|numeric');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($po) == TRUE)
        {
            $pitem = array('cash_id' => $po, 
                           'account_id' => $this->account->get_id_code($this->input->post('titem')),
                           'balance' => $this->input->post('tcredit'));
            
            $this->transmodel->add($pitem);
            $this->update_trans($po);
            $this->error = 'transaction posted';
        }
        elseif ( $this->valid_confirmation($po) != TRUE ){ $this->reject("Can't change value - Journal approved..!"); }
        else{ $this->reject(validation_errors()); } 
        }else { $this->reject_token(); }
        $this->response();
    }

    private function update_trans($po)
    {
        $total = $this->transmodel->total($po);
        $this->model->where('id', $po)->get();
        $this->model->amount = $total['balance'];
        $this->model->save();
    }

    function delete_item($id)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->transmodel->cek_trans('id',$id)){
            
        $jid = $this->transmodel->get_by_id($id)->row();
        if ( $this->valid_confirmation($jid->cash_id) == TRUE )
        {
            $this->transmodel->force_delete($id);
            $this->update_trans($jid->cash_id);
            $this->error = 'Transaction removed..!';
        }
        else{ $this->reject("Journal approved, can't deleted..!"); }
        }else { $this->reject_token(); }
        $this->response();
    }
//    ==========================================================================================

    // Fungsi update untuk mengupdate db
    function update($jid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->cmodel->valid_add_trans($jid, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('cacc', 'Account', 'required');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($jid) == TRUE)
        {
            $this->model->where('id',$jid)->get();

            $this->model->dates    = $this->input->post('tdate');
            $this->model->acc      = $this->input->post('cacc');
            $this->model->notes    = $this->input->post('tnote');
            $this->model->desc     = $this->input->post('tdesc');
            $this->model->log      = $this->decodedd->log;
            $this->model->updated  = date('Y-m-d H:i:s');

            $this->model->save();
            $this->error = "One $this->title data successfully updated!|";
        }
        elseif ($this->valid_confirmation($jid) != TRUE){ $this->reject("Journal approved, can't deleted..!"); }
        else{ $this->reject(validation_errors()); }
        }else { $this->reject_token(); }
        $this->response();
    }


    public function valid_period($date=null)
    {
        $p = new Period();
        $p->get();

        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));

        if ( intval($p->month) != intval($month) || intval($p->year) != intval($year) )
        {
            $this->form_validation->set_message('valid_period', "Invalid Period.!");
            return FALSE;
        }
        else {  return TRUE; }
    }

    public function valid_no($no)
    {
        $val = $this->model->where('no', $no)->count();
        if ($val > 0)
        {
            $this->form_validation->set_message('valid_no', "Order No already registered.!");
            return FALSE;
        }
        else {  return TRUE; }
    }

    public function valid_confirmation($id)
    {
        $val = $this->model->where('id', $id)->get();

        if ($val->approved == 1)
        {
            $this->form_validation->set_message('valid_confirmation', "Can't change value - Journal approved..!.!");
            return FALSE;
        }
        else {  return TRUE; }
    }

// ===================================== PRINT ===========================================

   function invoice($po=null)
   {
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->cmodel->valid_add_trans($po, $this->title) == TRUE){ 
       
       $cash = $this->model->where('id', $po)->get();

       $data['p_name'] = $this->properti['name'];
       $data['pono'] = $cash->no;
       $data['podate'] = tglin($cash->dates);
       $data['customer'] = $this->customer->get_name($cash->customer);
       $data['desc'] = $cash->desc;
       $data['notes'] = $cash->notes;
       $data['user'] = $this->user->get_username($cash->user);
       $data['currency'] = $cash->currency;
       $data['acc'] = $this->get_acc($cash->acc);
       $data['log'] = $this->decodedd->log;
       $data['amount'] = $cash->amount;
       
       if ($cash->currency == 'IDR'){ $data['terbilang'] = $this->terbilang->baca($cash->amount).' Rupiah'; }
       else { $data['terbilang'] = $this->terbilang->baca($cash->amount); }

       $items = null;
       foreach ($this->transmodel->get_last_item($cash->id)->result() as $res) {
           $items[] = array("account"=> $this->get_acc($res->account_id), "amount"=>$res->balance);
       }
       $data['items'] = $items;
       $this->output = $data;
       
       }else { $this->reject_token(); }
       $this->response('content');
   }

// ====================================== REPORT =========================================

    function report()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){ 

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
        $items=null;
        foreach ($this->get_report_search($cur,$start,$end) as $res) {
           $items[] = array ("id"=>$res->id, "no"=>$res->no, "notes"=>$res->notes, "dates"=>tglin($res->dates), "desc"=>tglin($res->desc), 
                           "currency"=>$res->currency, "customer"=>$this->customer->get_name($res->customer), "account"=>$this->get_acc($res->acc), 
                           "posted"=>$res->approved, "amount"=>$res->amount, "log"=> $this->decodedd->log);
        }
        $data['items'] = $items;  $this->output = $data;
        }else { $this->reject_token(); }
        $this->response('content');
    }
    
    private function get_report_search($cur,$start,$end)
    {
       if ($start != '' || $end != '') { $this->model->where_between('dates', "'".$start."'", "'".$end."'"); }
       $this->model->where('currency', $cur);
       return $this->model->where('approved', 1)->get();
    }

// ====================================== REPORT =========================================
// 
   // ====================================== CLOSING ====================================== 
   function reset_process(){ $this->transmodel->closing_trans();  $this->transmodel->closing(); } 
    
    
}

?>