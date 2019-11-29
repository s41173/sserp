<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ap_payment extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Appayment_model', 'model', TRUE);
        $this->load->model('Payment_trans_model', 'transmodel', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = $this->load->library('currency_lib');
        $this->load->library('bank_lib');
        $this->vendor = new Vendor_lib();
        $this->user = new Admin_lib();
        $this->journalgl = new Journalgl_lib();
        $this->cek = new Checkout_lib();
        $this->purchase = new Purchase_lib();
        $this->purchase_return = new Purchase_return_lib();
        $this->account = new Account_lib();
        $this->ledger = new Cash_ledger_lib();
        $this->trans = new Trans_ledger_lib();
        $this->bank = new Bank_lib();
        $this->period = new Period_lib();
        $this->period = $this->period->get();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $trans, $bank, $period, $acl, $api;
    private $vendor,$user,$cek,$purchase,$purchase_return, $currency, $account,$ledger;

    protected $error = null;
    protected $status = 200;
    protected $output = null;

    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $datax = (array)json_decode(file_get_contents('php://input')); 
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
        
        $date = null; $vendor=null;
        if (isset($datax['date'])){ $date = $datax['date']; }
        if (isset($datax['vendor'])){ $vendor = $datax['vendor']; }
        
        if(!$date){ $result = $this->model->get_last($this->limitx, $this->offsetx)->result(); }
        else{ $result = $this->model->search($vendor,$date)->result(); }
        $resx = null;
	foreach($result as $res)
	{
           $resx[] = array ("id"=>$res->id, "no"=>$res->no, "dates"=>tglin($res->dates), "docno"=>$res->docno, 
                            "currency"=>$res->currency, "vendor"=>$this->vendor->get_vendor_name($res->vendor), "account"=>$this->get_acc($res->acc), 
                            "checkno"=>$res->check_no, "posted"=>$res->approved, 
                            "amount"=>floatval($res->amount+$res->over+$res->late), "log"=> $this->decodedd->log);
	}
        $data['result'] = $resx; $data['counter'] = $this->model->counter(); $data['asset'] = $this->account->combo_asset();
        $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    } 
    
    private function get_acc($acc)
    { return $this->account->get_code($acc).' : '.$this->account->get_name($acc);}
    
    
    private function acc($val=null)
    { switch ($val) { case 'bank': $val = 'Bank'; break; case 'cash': $val = 'Cash'; break; case 'pettycash': $val = 'Petty Cash'; break; } return $val; }
//    ===================== approval ===========================================

    function confirmation($pid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
        $appayment = $this->model->get_by_id($pid)->row();
        $code = 'PO'; 

        if ($appayment->approved == 1){ $this->reject("transaction already approved..!"); }
        else
        {
            $total = $appayment->amount;
            if ($total <= 0){ $this->reject("CD-00$appayment->no has no value..!"); }
            elseif ($this->cek_po_settled($pid,$code) == FALSE || $this->cek_pr_settled($pid) == FALSE)
            {  $this->reject("Transaction $appayment->no has been settled..!"); }
            elseif ($this->valid_check_no($appayment->no,$pid) == FALSE ){ $this->reject("CD-00$appayment->no check no registered..!"); }
            else
            {
                $this->settled_po($pid,$code); // fungsi untuk mensettled kan semua po
                
                // membuat kartu hutang
                $this->trans->add($appayment->acc, 'CD', $appayment->no, $appayment->currency, $appayment->dates, intval($appayment->amount+$appayment->discount-$appayment->late), 0, $appayment->vendor, 'AP'); 
               
               $data = array('approved' => 1);
               $this->model->update($pid, $data);
                
               $cm = new Control_model();
               if ($appayment->post_dated == 1){ $account = $cm->get_id(35); }else { $account = $appayment->account; } // bank atau giro
               
               $ap   = $cm->get_id(11);
               $cost = $cm->get_id(5); // biaya denda
               $discount = $cm->get_id(3); // potongan pembelian
               
               $this->journalgl->new_journal('0'.$appayment->no,$appayment->dates,'CD', strtoupper($appayment->currency), 'Payment for : '.$this->get_trans_code($appayment->no).' - '. $this->vendor->get_vendor_name($appayment->vendor).' - '.$this->acc($appayment->acc), $appayment->amount, $this->decodedd->log);
               $dpid = $this->journalgl->get_journal_id('CD','0'.$appayment->no);
               
               // cash ledger
               $this->ledger->add($appayment->acc, "CD-00".$appayment->no, $appayment->currency, $appayment->dates, 0, $appayment->amount);
               
               if ($appayment->late > 0){ $this->journalgl->add_trans($dpid,$cost,$appayment->late,0); } // denda keterlambatan
               $this->journalgl->add_trans($dpid,$ap,intval($appayment->amount-$appayment->late),0); // hutang usaha
               $this->journalgl->add_trans($dpid,$account,0,$appayment->amount); // kas, bank, kas kecil
               
               if ($appayment->discount > 0)
               {
                  $this->journalgl->new_journal('0'.$appayment->no,$appayment->dates,'PD', strtoupper($appayment->currency), 'Purchase Discount : '.$this->get_trans_code($appayment->no).' - '. $this->vendor->get_vendor_name($appayment->vendor).' - '.$this->acc($appayment->acc), $appayment->amount, $this->decodedd->log);
                  $pdid = $this->journalgl->get_journal_id('PD','0'.$appayment->no);
                  
                  $this->journalgl->add_trans($pdid,$ap,$appayment->discount,0); // hutang usaha
                  $this->journalgl->add_trans($pdid,$discount,0,$appayment->discount); // potongan pembelian
               }
               $this->error = "CD-00$appayment->no confirmed..!";
            }
        }
        }else { $this->reject_token(); }
        $this->response();
    }

    private function get_trans_code($po)
    {
        $val = $this->transmodel->get_last_item($po)->result();
        $ress=null;

        foreach ($val as $res)
        { $ress = $ress.$res->code.'-00'.$res->no.','; }

        return $ress;
    }

    private function valid_check_no($no=null,$pid=null)
    {
        $val = $this->model->get_by_no($no)->row();
        if ($val->check_no != null)
        {
            if ($this->model->cek_no($val->check_no,$pid) == FALSE)
            { return FALSE; } else { return TRUE; }
        }
        else { return TRUE; }
    }

    private function settled_po($no,$code='PO')
    {
        $vals = $this->transmodel->get_last_trans($no,$code)->result();
        
        if ($code == 'PO')
        {
            foreach ($vals as $val)
            {
                $p2 = $this->purchase->get_po($val->no);
                $p2 = $p2->p2;
             
                if ($val->amount - $p2 >= 0)
                {
                   $data = array('status' => 1,'p2' => $p2-$val->amount);
                   $this->purchase->settled_po($val->no,$data);
                }
                else
                {
                    $datax = array('p2' => $p2-$val->amount);
                    $this->purchase->settled_po($val->no,$datax);
                }
            }
        }  
    }

    private function settled_pr($no)
    {
        $vals = $this->transmodel->get_last_trans($no,'PR')->result();
        $data = array('status' => 1);

        foreach ($vals as $val)
        {  $this->purchase_return->settled_pr($val->no,$data); }
    }

    function unsettled_po($no,$code)
    {
        $vals = $this->transmodel->get_last_trans($no,$code)->result();
        
        if ($code == 'PO')
        {
            foreach ($vals as $val)
            {
                $p2 = $this->purchase->get_po($val->no);
                $p2 = $p2->p2;
                $data = array('status' => 0, 'p2'=> $val->amount+$p2);
                $this->purchase->settled_po($val->no,$data);
            }
        } 
    }

    private function unsettled_pr($no)
    {
        $vals = $this->transmodel->get_last_trans($no,'PR')->result();
        $data = array('status' => 0);

        foreach ($vals as $val)
        {  $this->purchase_return->settled_pr($val->no,$data); }
    }

    private function cek_po_settled($id,$code='PO')
    {
        $vals = $this->transmodel->get_last_trans($id,$code)->result();
        $res = FALSE;
        
        if ($code == 'PO')
        {
            foreach ($vals as $val)
            { if ($this->purchase->cek_settled($val->no) == FALSE){ $res = FALSE; break; } else { $res = TRUE; } }
        }

        return $res;
    }

    private function cek_pr_settled($no)
    {
        $vals = $this->transmodel->get_last_trans($no,'PR')->result();
        $num  = $this->transmodel->get_last_trans($no,'PR')->num_rows();
        $res = TRUE;

        if ($num > 0)
        {
           foreach ($vals as $val)
           {
              if ($this->purchase_return->cek_settled($val->no) == FALSE)
              {
                  $res = FALSE;
                  break;
              }
              else { $res = TRUE; }
           }
        }

        return $res;
    }

    private function cek_confirmation($po=null,$page=null)
    {
        $appayment = $this->model->get_ap_payment_by_no($po)->row();

        if ( $appayment->approved == 1 )
        {
           $this->session->set_flashdata('message', "Can't change value - CD-00$po approved..!"); // set flash data message dengan session
           if ($page){ redirect($this->title.'/'.$page.'/'.$po); } else { redirect($this->title); }
        }
    }


//    ===================== approval ===========================================

    function delete($uid)
    {
        if ($this->acl->otentikasi3($this->title,'ajax') == TRUE){
        $appayment = $this->model->get_by_id($uid)->row();
        $po = $appayment->no;
        $code = 'PO';
        
        if ( $this->valid_period($appayment->dates) == TRUE && $this->valid_credit_over($appayment->no) == TRUE ) 
        {
            if ($appayment->approved == 1){ $this->rollback($uid, $po,$code); $this->error = "transaction successfully rollback..!"; }
            else { $this->remove($uid, $po, $code); $this->error = "transaction successfully removed..!"; }
        }
        elseif ($this->valid_period($appayment->dates) != TRUE){ $this->reject("Invalid Period"); }
        else{ $this->reject("transaction can't removed, journal approved, related to another component..!"); } 
        }else { $this->reject_token(); }
        $this->response();
    }
    
    private function rollback($uid,$po,$code)
    {
       $this->unsettled_po($uid,$code);
//       $this->unsettled_pr($po);
       $this->journalgl->remove_journal('CD', '0'.$po);
       $this->journalgl->remove_journal('PD', '0'.$po);
       
       // rollback kartu hutang 
      $appayment = $this->model->get_by_id($uid)->row();
      $this->trans->remove($appayment->dates, 'CD', $appayment->no);
      $this->ledger->remove($appayment->dates, "CD-00".$appayment->no); // remove cash ledger
       
       $data = array('approved' => 0);
       $this->model->update($uid, $data); 
    }
    
    private function remove($uid,$po,$code='PO')
    {
       // remove cash ledger
       $val = $this->model->get_by_id($uid)->row();
       $this->ledger->remove($val->dates, "CD-00".$val->no); 
        
       $this->transmodel->delete_payment($uid); // model to delete appayment item
       $this->model->force_delete($uid); // memanggil model untuk mendelete data 
    }
    
    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('cvendor', 'Vendor', 'required');
//        $this->form_validation->set_rules('tcheck', 'Check No', 'callback_valid_check|callback_valid_check_no');
        $this->form_validation->set_rules('tdate', 'Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('trate', 'Rate', 'required|numeric|callback_valid_rate');

        if ($this->form_validation->run($this) == TRUE)
        {
            $check = $this->input->post('tcheckaccno').'|'.$this->input->post('tcheckaccname').'|'.$this->input->post('tccbank');
            $appayment = array('vendor' => $this->input->post('cvendor'), 'docno' => $this->input->post('tdocno'),
                               'no' => $this->model->counter(), 'check_no' => null, 'dates' => $this->input->post('tdate'), 
                               'currency' => $this->input->post('ccurrency'), 'acc' => $this->input->post('cacc'), 'rate' => $this->input->post('trate'),
                               'amount' => 0, 'user' => $this->decodedd->userid, 'log' => $this->decodedd->log,
                               'check_acc' => $check);

            if ($this->model->add($appayment) == true){ $this->error = $this->title.' successfully saved..!'; }else{ $this->reject(); }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response();
    }
    
    function get($pid=null)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
        
        $appayment = $this->model->get_by_id($pid)->row();
         
        $data['venid'] = $appayment->vendor;
        $data['code'] = $appayment->no;
        $data['vendor'] = $this->vendor->get_vendor_name($appayment->vendor);
        $data['date'] = $appayment->dates;
        $data['currency'] = $appayment->currency;
        $data['check'] = $appayment->check_no;
        $data['balance'] = $appayment->amount;
        $data['tdiscount'] = $appayment->discount;
        $data['late'] = $appayment->late;
        $data['acc'] = $appayment->acc;
        $data['docno'] = $appayment->docno;
        $data['rate'] = $appayment->rate;
        $data['no'] = $appayment->no;
        $data['status'] = $appayment->post_dated;
        $data['bank'] = $appayment->account;
        
        $check = explode('|', $appayment->check_acc);
        $data['checkaccno'] = $check[0];
        $data['checkaccname'] = $check[1];
        $data['checkaccbank'] = $check[2];

        $data['user'] = $this->user->get_username($appayment->user);

//      ============================ Check  =========================================

        $data['default']['due'] = $appayment->due;
        $data['default']['balancecek'] = $appayment->amount;

//      ============================ Purchase Item  =========================================
//        $data['items'] = $this->transmodel->get_last_item($pid)->result();
        
        $items = null;
        foreach ($this->transmodel->get_last_item($pid)->result() as $res) {           
           $items[] = array("id"=>$res->id,"purchase"=>$res->code.'-00'.$res->no,
                            "amount"=> floatval($res->amount), "discount"=>floatval($res->discount));
        }
        $data['items'] = $items; $this->output = $data;
       }else { $this->reject_token(); }
       $this->response('content');
    }

//    ======================  Item Transaction   ===============================================================

    function add_item($pid=null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){ 
           
        $this->form_validation->set_rules('titem', 'Transaction', 'required|callback_valid_po');
        $this->form_validation->set_rules('tnominal', 'Nominal', 'required|numeric');
        $this->form_validation->set_rules('tdiscount', 'Discount', 'required|numeric');
        $this->form_validation->set_rules('tamount', 'Amount', 'required|numeric');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE && $pid != null)
        {
            $code = 'PO';
            $amount = $this->input->post('tamount');

            $pitem = array('ap_payment' => $pid, 'code' => $code, 'no' => $this->input->post('titem'), 'nominal' => $this->input->post('tnominal'), 
                           'discount' => $this->input->post('tdiscount'), 'amount' => $this->calculate_rate($pid,$amount));
            
            $this->transmodel->add($pitem);
            $this->update_trans($pid,$code);
            $this->error = "Transaction posted";
        }
        elseif ( $this->valid_confirmation($pid) != TRUE ){ $this->reject("Can't change value - Journal approved..!"); }
        elseif (!$pid){ $this->reject("Can't change value - Journal not created..!"); }
        else{ $this->reject(validation_errors()); } 
       }else { $this->reject_token(); }
       $this->response();
    }
    
    private function update_trans($pid,$code='PO')
    {
        $totals = $this->transmodel->total($pid,$code);
        $res = $totals['amount'];
        
        $val = $this->model->get_by_id($pid)->row();
        $res = $res+$val->late;
        
        $appayment = array('amount' => $res, 'discount' => $totals['discount']);
	$this->model->update($pid, $appayment);
    }

    private function calculate_rate($pid,$amount)
    {
        $rate = $this->model->get_by_id($pid)->row();
        $rate = $rate->rate;
        return $rate*$amount;
    }

    function add_return($po=null)
    {
        $this->cek_confirmation($po,'add_trans');

        $this->form_validation->set_rules('treturn', 'Return Transaction', 'required|callback_valid_pr');

        if ($this->form_validation->run($this) == TRUE)
        {
            $purchase = $this->purchase_return->get_pr($this->input->post('treturn'));

            $pitem = array('ap_payment' => $po, 'code' => 'PR', 'no' => $this->input->post('treturn'), 'amount' => $purchase->balance);
            $this->transmodel->add($pitem);
            $this->update_trans($po);

            echo 'true';
        }
        else{ echo validation_errors(); }
    }

    function delete_item($id)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->transmodel->valid_trans($id) == TRUE){ 
            $pid = $this->transmodel->get_by_id($id)->row();
            if ($this->valid_confirmation($pid->ap_payment) == TRUE){
               $this->transmodel->delete($id);
               $this->update_trans($pid->ap_payment,'PO');
               $this->error = 'Transaction removed..!';
            }else{ $this->reject("Journal approved, can't deleted..!"); }
        }else { $this->reject_token(); }
        $this->response();
    }
//    ==========================================================================================

    // Fungsi update untuk mengupdate db
    function update($pid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tcheck', 'Check No', 'callback_valid_check');
        $this->form_validation->set_rules('tdate', 'Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('cbank', 'Bank', 'required');
        $this->form_validation->set_rules('tdue', 'Due Date', 'callback_valid_check');
        $this->form_validation->set_rules('tbalancecek', 'Cheque Balance', 'required|numeric');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE)
        {   
            $check = $this->input->post('tcheckaccno').'|'.$this->input->post('tcheckaccname').'|'.$this->input->post('tccbank');
            $appayment = array('log' => $this->decodedd->log, 'acc' => $this->input->post('cacc'), 'dates' => $this->input->post('tdate'), 
                               'account' => $this->input->post('cbank'), 'late' => $this->input->post('tlate'),
                               'due' => setnull($this->input->post('tdue')), 'post_dated' => $this->input->post('cpost'),
                               'check_acc' => $check, 'check_no' => $this->cek_null($this->input->post('tcheck')));

            $this->model->update($pid, $appayment);
            
            $val = $this->model->get_by_id($pid)->row();
            $code = 'PO'; 
            $this->update_trans($pid,$code);
            
            if ($this->input->post('tbalancecek') > $val->amount){ $appayment1 = array('over' => intval($this->input->post('tbalancecek')-$val->amount), 'over_stts' => 1); }
            else{ $appayment1 = array('over' => 0, 'over_stts' => 0); }
            
            $this->model->update($pid, $appayment1);   
            $this->error = "Transaction data successfully updated!";
        }
        elseif ($this->valid_confirmation($pid) != TRUE){ $this->reject("Can't change value - Order approved..!"); }
        else{ $this->reject(validation_errors()); }
        }else { $this->reject_token(); }
        $this->response();
    }
    
    public function valid_period($date=null)
    {
        $month = date('n', strtotime($date));
        $year  = date('Y', strtotime($date));
        
        if ( intval($this->period->month) != intval($month) || intval($this->period->year) != intval($year) )
        {
           $this->form_validation->set_message('valid_period', "Invalid Period.!"); return FALSE;
        }
        else {  return TRUE; }
    }

    public function valid_rate($rate)
    {
        if ($rate == 0)
        {
            $this->form_validation->set_message('valid_rate', "Rate can't 0.!");
            return FALSE;
        }
        else { return TRUE; }
    }

    public function valid_confirmation($pid)
    {
        $val = $this->model->get_by_id($pid)->row();

        if ($val->approved == 1)
        {
            $this->form_validation->set_message('valid_confirmation', "Can't change value - Order approved..!.!");
            return FALSE;
        }
        else {  return TRUE; }
    }

    public function valid_pr($no)
    {
        if ($this->transmodel->get_item_based_po($no,'PR') == FALSE)
        {
            $this->form_validation->set_message('valid_pr', "PR already registered to journal.!");
            return FALSE;
        }
        else { return TRUE; }
    }

    
    public function valid_po($no)
    {
        if ($this->transmodel->get_item_based_po($no,'PO') == FALSE)
        {
            $this->form_validation->set_message('valid_po', "PO already registered to journal.!");
            return FALSE;
        }
        else { return TRUE; }
    }

    function valid_check($val)
    {
        $acc = $this->input->post('tacc');

        if ($acc == 'bank')
        {
            if ($val == null) { $this->form_validation->set_message('valid_check', "Check No / Field Required..!"); return FALSE; }
            else { return TRUE; }
        }
        else { return TRUE; }
    }
    
    function valid_credit_over($no)
    {
        $val = $this->model->get_by_no($no)->row();

        if ($val->credit_over == 1)
        {
           $this->form_validation->set_message('valid_credit_over', "Transaction Has Credited To Another Transaction..!"); return FALSE;
        }
        else { return TRUE; }
    }

// ===================================== PRINT ===========================================

   function invoice($pid=0)
   {
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){

       $appayment = $this->model->get_by_id($pid)->row();
       $code = 'PO';

       $data['pono'] = $appayment->no;
       $data['acc'] = strtoupper($this->acc($appayment->acc));
       $data['podate'] = tgleng($appayment->dates);
       $data['bank'] = $this->account->get_code($appayment->account).' : '.$this->account->get_name($appayment->account);
       $data['docno'] = $appayment->docno;
       $data['vendor'] = $this->vendor->get_vendor_name($appayment->vendor);
       $data['ven_bank'] = $this->vendor->get_vendor_bank($appayment->vendor);
       $data['amount'] = floatval($appayment->amount);
       $data['late'] = floatval($appayment->late);
       $data['over'] = floatval($appayment->over);
       $data['check'] = $appayment->check_no;
       
       $check = explode('|', $appayment->check_acc);
       $data['checkaccno'] = $check[0];
       $data['checkacc'] = $check[1].'-'.$check[2];
       $data['check_type'] = "";
       $data['type'] = '';
       $data['voucher'] = '';
       $data['due'] = isset($appayment->due) ? tglin($appayment->due) : '';

       $items = null;
       foreach ($this->transmodel->get_po_details($pid)->result() as $res) {           
           $items[] = array("id"=>$res->id,"purchase"=>$res->code.'-00'.$res->no.'-'.$res->notes.' - '.tglin($res->dates),
                            "amount"=> floatval($res->amount+$res->discount), "discount"=>floatval($res->discount), "balance"=>floatval($res->amount));
       }
       
       $data['items'] = $items;
       $terbilang = $this->load->library('terbilang');
       if ($appayment->currency == 'IDR')
       { $data['terbilang'] = ucwords($terbilang->baca($appayment->amount+$appayment->over)).' Rupiah'; }
       else { $data['terbilang'] = ucwords($terbilang->baca($appayment->amount+$appayment->over)); }
       
       $data['accounting'] = $this->properti['accounting'];
       $data['manager'] = $this->properti['manager'];
       
       $this->output = $data;
       }else { $this->reject_token(); }
       $this->response('content');
   }

// ===================================== PRINT ===========================================

    private function cek_null($val=null)
    { if ($val) { return $val; } else { return NULL; } }


    //    ================================ REPORT =====================================

    function report()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){

        $vendor = $this->input->post('cvendor');
        $acc = $this->input->post('cacc');
        $cur = $this->input->post('ccurrency');
        
        $start = $this->input->post('start');
        $end = $this->input->post('end');

        $data['currency'] = $cur;
        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        $data['acc'] = $acc;
        $data['rundate'] = tgleng(date('Y-m-d'));
        $data['log'] = $this->decodedd->log;

//        Property Details
        $data['company'] = $this->properti['name'];
//        $data['reports'] = $this->model->report($vendor,$start,$end,$acc,$cur)->result();

        $items = null;
        foreach ($this->model->report($vendor,$start,$end,$acc,$cur)->result() as $res) {
            
            if ($res->credit_over == 0){ $over = '-'; }else{ $over = 'Y'; }
            
            $items[] = array ("id"=>$res->id, "code"=>"CD-00".$res->no, "dates"=>tglin($res->dates), "docno"=>$res->docno, 
                              "currency"=>$res->currency, "vendor"=>$res->prefix.' '.$res->name, "account"=>$this->get_acc($res->acc), 
                              "checkno"=>$res->check_no, "posted"=>$res->approved, 
                              "amount"=>floatval($res->amount+$res->over+$res->late), "discount"=>floatval($res->discount), "late"=>floatval($res->late), 
                              "balance"=>floatval($res->amount), 'over'=>floatval($res->over), "balance_over"=>floatval($res->amount+$res->over), "credit_status" => $over,
                              "log"=> $this->decodedd->log);
        }
        $total = $this->model->total($vendor,$start,$end,$acc,$cur);
        $data['total'] = floatval($total['amount']);
        $data['items'] = $items;
        $this->output = $data;
        
       }else { $this->reject_token(); }
       $this->response('content');
    }

//    ================================ REPORT =====================================
    
//    ================================ AJAX =====================================
    
    function get_po()
    {
       if ($this->input->post('po')) 
       {
          $purchase = $this->purchase->get_po($this->input->post('po'));
          echo intval($purchase->p2);
       }
       else { echo '0'; }
    }
    
    function payable()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){

        $vendor = $this->input->post('cvendor');
        $cur = $this->input->post('ccurrency');
        
        $start = $this->input->post('start');
        $end = $this->input->post('end');

        $data['currency'] = $cur;
        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        
        $data['rundate'] = tgleng(date('Y-m-d'));
        $data['log'] = $this->decodedd->log;
        
        // properti
        $data['company'] = $this->properti['name'];
        
        $trans = new Trans_ledger_lib();
        $data['vendor'] = $this->vendor->get_vendor_name($vendor);
        $data['open'] = $trans->get_sum_transaction_open_balance_ap(null, $cur, $start, $vendor, 'AP');
//        $data['trans'] = $trans->get_transaction_ap(null, $cur, $start, $end, $vendor, 'AP')->result();
        
        $opentrans = floatval($data['open']);
        $items = null;
        foreach ($trans->get_transaction_ap(null, $cur, $start, $end, $vendor, 'AP')->result() as $res) {
            
            $items[] = array("date"=> tglin($res->dates), "code"=>$res->code, "transcode"=>$res->code.'-00'.$res->no,
                             "debit"=>floatval($res->debit), "credit"=>floatval($res->credit), "balance"=> $this->trans($opentrans,$res->debit,$res->credit)
                            );
        }
        $data['items'] = $items; $this->output = $data;
        }else { $this->reject_token(); }
       $this->response('content');
    }
    
    private function trans($open,$in,$out){ $res = intval($in-$out); return floatval($open+$res); }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); $this->transmodel->closing(); }
}

?>