<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_return extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Purchase_return_model', 'model', TRUE);
        $this->load->model('Purchase_return_item_model', 'transmodel', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->unit = $this->load->library('unit_lib');
        $this->currency = $this->load->library('currency_lib');
        
        $this->vendor = new Vendor_lib();
        $this->user = new Admin_lib(); 
        $this->tax = new Tax_lib(); 
        $this->journalgl = new Journalgl_lib();
        $this->product = new Product_lib();
        $this->purchase = new Purchase_lib();
        $this->pitem = new Purchase_item_lib();
        $this->wt = new Warehouse_transaction_lib();
        $this->ap = new Ap_payment_lib();
        $this->stock = new Stock_lib();
        $this->branch = new Branch_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title, $stock, $branch, $api, $acl;
    private $vendor,$user,$tax,$journal,$product,$purchase,$pitem,$wt,$unit,$currency,$journalgl,$ap;

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
        else{ $result = $this->model->search($vendor, $date)->result(); }
        $resx = null;
	foreach($result as $res)
	{
           $resx[] = array ("id"=>$res->id, "no"=>$res->no, "purchase"=>$res->purchase, "notes"=>$res->notes, "dates"=>tglin($res->dates), "docno"=>$res->docno, 
                            "currency"=>$res->currency, "vendor"=>$this->vendor->get_vendor_name($res->vendor), "acc"=>$res->acc, "user"=> $this->decodedd->userid,
                            "posted"=>$res->approved, "status"=> $this->status($res->status), "log"=> $this->decodedd->log, 
                            "tax"=>floatval($res->tax), "cost"=>floatval($res->costs), "amount"=>floatval($res->total + $res->costs), "balance"=>floatval($res->balance), "cash"=>$res->cash
                           );
	}
        $data['result'] = $resx; $data['counter'] = $this->model->counter();
        $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    } 

    private function status($val=null)
    { switch ($val) { case 0: $val = 'C'; break; case 1: $val = 'S'; break; } return $val; }
    
//    ===================== approval ===========================================

    function confirmation($pid)
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){ 
        $purchase_return = $this->model->get_by_id($pid)->row();

        if ($purchase_return->approved == 1){ $this->reject("$this->title already approved..!"); }
        elseif ($this->valid_period($purchase_return->dates) == FALSE){ $this->reject("Invalid Period..!");  }
        else
        {
          //  $this->cek_journal($purchase_return->dates,$purchase_return->currency);
            $total = $purchase_return->total;
            if ($total == 0){ $this->reject("$this->title has no value..!"); }
            else
            {
                $data = array('approved' => 1);
                $this->model->update($pid, $data);

                // journal gl --------------------------------------------------------------------
                 $cm = new Control_model();
        
                 $landed   = $cm->get_id(1); // biaya pengiriman barang
                 $tax      = $cm->get_id(9); // pajak di bayar dimuka
                 $stock    = $cm->get_id(10); // persediaan
                 $ap       = $cm->get_id(11); // hutang usaha
                 $bank     = $cm->get_id(12); // bank
                 $kas      = $cm->get_id(13); // kas
                 $kaskecil = $cm->get_id(14); // kas kecil
                 $account = 0;
                 
                 switch ($purchase_return->acc) { case 'bank': $account = $bank; break; case 'cash': $account = $kas; break; case 'pettycash': $account = $kaskecil; break; }
                 
                 $this->journalgl->new_journal($purchase_return->no,$purchase_return->dates,'PR', strtoupper($purchase_return->currency),
                                               'PR-0'.$purchase_return->no.'-'.$purchase_return->notes, $purchase_return->balance, 
                                               $this->decodedd->log);
                 
                 $jid = $this->journalgl->get_journal_id('PR',$purchase_return->no);
                 
                 $this->journalgl->add_trans($jid,$account,$purchase_return->total,0); // bank - D
                 $this->journalgl->add_trans($jid,$stock,0,$purchase_return->total-$purchase_return->tax); // kurang persediaan - K
                 if ($purchase_return->tax > 0){ $this->journalgl->add_trans($jid,$tax,0,$purchase_return->tax); } // pajak pembelian
                 if ($purchase_return->costs > 0)
                 { 
                   $this->journalgl->add_trans($jid,$landed,$purchase_return->costs,0);  // biaya cost
                   $this->journalgl->add_trans($jid,$account,0,$purchase_return->costs);  // bank - K
                 }
                 
                // min stock
                $this->min_stock($pid);
                 
                // create warehouse transaction
//                $this->add_warehouse_transaction($purchase_return->no);
                $this->error = "PR-00$purchase_return->no confirmed..!";
            }
        }
      }else { $this->reject_token(); }
      $this->response();
    }
    
    private function min_stock($pid)
    {
        $purchase = $this->model->get_by_id($pid)->row();
        $trans = $this->transmodel->get_last_item($pid)->result();
        foreach ($trans as $res) {
            $this->stock->increase_stock($res->product, $purchase->dates, $res->qty);            
            $this->wt->add($purchase->dates, 'PR-'.$purchase->no, $this->branch->get_branch(), $purchase->currency, $res->product, 0, $res->qty, $res->price, $res->qty*$res->price);
        }
    }
    
    private function rollback_stock($pid)
    {
        $purchase = $this->model->get_by_id($pid)->row();
        $trans = $this->transmodel->get_last_item($pid)->result();
        foreach ($trans as $res) {       
            $this->stock->add_stock($res->product, $purchase->dates, $res->qty, $res->price);
            $this->wt->remove($purchase->dates, 'PR-'.$purchase->no);
        }
    }

    function valid_stock($product,$dates)
    {
        $qty = $this->input->post('treturn');
        if ($this->stock->valid_stock($product, $dates, $qty) == FALSE){
           $this->form_validation->set_message('valid_stock', "Invalid Stock..!"); 
           return FALSE; 
        }else{ return TRUE; }
    }

    function valid_confirmation($pid)
    {
        $purchase_return = $this->model->get_by_id($pid)->row();

        if ( $purchase_return->approved == 1 )
        {
           $this->form_validation->set_message('valid_confirmation', "Journal already approved..!"); 
           return FALSE;
        }else { return TRUE; }
    }
//    ===================== approval ===========================================


    function delete($uid)
    {
      if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
        $pr = $this->model->get_by_id($uid)->row();
        
        if ( $this->valid_period($pr->dates) == TRUE && $this->ap->cek_relation_trans($pr->purchase,'no','PR') == TRUE )
        {
           if ($pr->approved == 1){ $this->rollback($uid, $pr->no);  }else { $this->remove($uid, $pr->no);  }
           $this->journalgl->remove_journal('PR', $pr->no); // journal gl
        }
        elseif ($this->valid_period($pr->dates) != TRUE){ $this->reject("Invalid Period"); }
        else{ $this->reject("$this->title can't removed, journal approved, related to another component..!"); ; } 
      }else { $this->reject_token(); }
      $this->response();
    }
    
    private function rollback($uid,$po)
    {
       $this->rollback_stock($uid);
       $trans = array('approved' => 0);
       $this->model->update($uid, $trans);
       $this->error = "$this->title rollback";
    }
    
    private function remove($uid,$po)
    {
       $this->transmodel->delete_po($uid); // model to delete purchase_return item
       $this->model->force_delete($uid); 
       $this->error = "$this->title removed";
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tpo', 'PO', 'required|callback_valid_po');
        $this->form_validation->set_rules('tno', 'PR - No', 'required|numeric|callback_valid_no');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tdocno', 'Doc NO', '');
        $this->form_validation->set_rules('cacc', 'Account', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $vendor = $this->purchase->get_po($this->input->post('tpo'));
            $purchase_return = array('vendor' => $vendor->vendor, 'currency' => $vendor->currency, 'purchase' => $this->input->post('tpo'), 
                                     'no' => $this->input->post('tno'), 'status' => 0, 'docno' => $this->input->post('tdocno'),
                                     'dates' => $this->input->post('tdate'), 'acc' => $this->input->post('cacc'), 'notes' => $this->input->post('tnote'),
                                     'user' => $this->decodedd->userid, 'log' => $this->decodedd->log);
            
            if ($this->model->add($purchase_return) == true){ $this->error = 'transaction successfully saved..!'; }else{ $this->reject(); }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token('Invalid Token or Expired..!'); }
        $this->response();
    }

    function get($pid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){ 
        
        $purchase_return = $this->model->get_by_id($pid)->row();

        $purchase = $this->purchase->get_po($purchase_return->purchase);
        $data['product'] = $this->pitem->combo($purchase->id);
        
        $data['no'] = $purchase_return->no;
        $data['vendor'] = $purchase_return->vendor;
        $data['po'] = $purchase_return->purchase;
        $data['date'] = $purchase_return->dates;
        $data['acc'] = $purchase_return->acc;
        $data['note'] = $purchase_return->notes;
        $data['user'] = $this->user->get_username($purchase_return->user);
        $data['docno'] = $purchase_return->docno;
        $data['currency'] = $purchase_return->currency;

        $data['tax']      = $purchase_return->tax;
        $data['totaltax'] = $purchase_return->total;
        $data['balance']  = $purchase_return->balance;
        $data['costs']    = $purchase_return->costs;

//        ============================ Purchase Item  ===============================================
//        $data['purchase_item'] = $this->pitem->get_last_item($purchase->id)->result();
        $pitems = null;
        foreach ($this->pitem->get_last_item($purchase->id)->result() as $res) {
             $pitems[] = array ("id"=>$res->id, 
                               "product"=> $this->product($res->product,'sku').' - '.$this->product($res->product,'name'), 
                               "qty"=> $res->qty.' - '. $this->product($res->product,'unit'),
                               "unit_price"=>floatval($res->price),
                               "tax"=>floatval($res->tax),
                               "amount"=>floatval($res->amount)
                              );    
        }
        $data['purchase_item'] = $pitems;
//        ============================ Purchase_return Item  =========================================
        $items = null;
        foreach ($this->transmodel->get_last_item($pid)->result() as $res) {
            $items[] = array ("id"=>$res->id, 
                              "product"=> $this->product($res->product,'sku').' - '.$this->product($res->product,'name'), 
                              "qty"=> $res->qty.' - '. $this->product($res->product,'unit'),
                              "unit_price"=>floatval($res->price),
                              "tax"=>floatval($res->tax),
                              "amount"=>floatval($res->amount)
                             );    
        }
        $data['items'] = $items;
        $this->output = $data;
        }else { $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('content');
    }
    
    private function product($val,$type='name')
    {
        if ($type == 'name'){ return $this->product->get_name($val); }
        elseif ($type == 'unit'){return $this->product->get_unit($val); }
        elseif ($type == 'sku'){ return $this->product->get_sku($val); }
    }

//    ======================  Item Transaction   ===============================================================

    function add_item($pid=null)
    {
      if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){  
        $pr = $this->model->get_by_id($pid)->row();
        $purchase = $this->purchase->get_po($pr->purchase);
        $purchase_item = $this->pitem->get_product_item($purchase->id, $this->input->post('cproduct'));
        
        $this->form_validation->set_rules('cproduct', 'Product', 'required|callback_valid_item['.$pid.']|callback_valid_stock['.$purchase->dates.']');
        $this->form_validation->set_rules('treturn', 'Return', 'required|numeric|callback_valid_qty['.$pr->purchase.']');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE)
        {
            $harga = $this->pitem->get_product_item($purchase->id, $this->input->post('cproduct'));
            $price = $harga->price;
            $tax = intval($harga->tax/$harga->qty*$this->input->post('treturn'));
            
            $pitem = array('product' => $this->input->post('cproduct'), 'purchase_return_id' => $pid, 'qty' => $this->input->post('treturn'),
                           'unit' => $this->product->get_unit($this->input->post('cproduct')),
                           'price' => $price, 'amount' => $this->input->post('treturn') * $price + $tax,
                           'tax' => $tax);
            
            if ($this->transmodel->add($pitem) == true){ $this->update_trans($pid); $this->error = 'transaction successfully saved..!'; }else{ $this->reject(); }   
        }
        elseif ($this->valid_confirmation($pid) == FALSE){ $this->reject("Journal already approved..!!"); }
        else{ $this->reject(validation_errors()); }
      }else { $this->reject_token('Invalid Token or Expired..!'); }
      $this->response();
    }

    private function update_trans($pid)
    {
        $totals = $this->transmodel->total($pid);
        $purchase_return = array('tax' => $totals['tax'], 'total' => $totals['amount']);
	$this->model->update($pid, $purchase_return);
    }

    function delete_item($id)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->transmodel->valid_add_trans($id) == TRUE){  
         
            $pid = $this->transmodel->get_by_id($id)->row();    
            if ($this->valid_confirmation($pid->purchase_return_id) == TRUE){

                $this->transmodel->delete($id); // memanggil model untuk mendelete data
                $this->update_trans($pid->purchase_return_id);
                $this->error = 'Transaction removed..!';

            }else{ $this->reject("Journal approved, can't deleted..!"); }
            }else { $this->reject_token('Invalid Token or Expired..!'); }
        $this->response(); 
    }
//    ==========================================================================================

    // Fungsi update untuk mengupdate db
    function update($pid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){ 

	// Form validation
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tcosts', 'Landed Cost', 'numeric');
        $this->form_validation->set_rules('tdocno', 'Docno', '');
        $this->form_validation->set_rules('cacc', 'Account', 'required');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE)
        {
            $purchase_returns = $this->model->get_by_id($pid)->row();

            $purchase_return = array('log' => $this->decodedd->log, 'docno' => $this->input->post('tdocno'),
                                     'dates' => $this->input->post('tdate'), 'acc' => $this->input->post('cacc'), 'notes' => $this->input->post('tnote'),
                                     'user' => $this->decodedd->userid, 'costs' => $this->input->post('tcosts'),
                                     'balance' => floatval($this->input->post('tcosts')+$purchase_returns->total)
                             );
            if ($this->model->update($pid,$purchase_return) == true){ $this->error = 'transaction successfully saved..!'; }else{ $this->reject(); }
        }
        elseif ($this->valid_confirmation($pid) != TRUE){ $this->reject("Journal already approved..!"); }
        else{ $this->reject(validation_errors()); }
       }else { $this->reject_token('Invalid Token or Expired..!'); }
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
            $this->form_validation->set_message('valid_period', "Invalid Period.!");
            return FALSE;
        }
        else {  return TRUE; }
    }

    public function valid_qty($return,$purchase)
    {
        $product = $this->input->post('cproduct');
        
        $purchase_id = $this->purchase->get_po($purchase);
        $qty = $this->pitem->get_product_item($purchase_id->id,$product);
        $qty = $qty->qty;
        
        if ($return > $qty)
        {
            $this->form_validation->set_message('valid_qty', "Invalid Return Qty.!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    public function valid_po($no)
    {
        if ($this->model->valid('purchase',$no) == FALSE)
        {
            $this->form_validation->set_message('valid_po', "Purchase No already registered.!");
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

    public function valid_item($product,$pid)
    {
        if ($this->transmodel->valid_item($product,$pid) == FALSE)
        {
            $this->form_validation->set_message('valid_item', "Product already listed.!");
            return FALSE;
        }
        else {  return TRUE; }
    }

// ===================================== PRINT ===========================================

   function invoice($pid=null)
   {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){ 

       $purchase_return = $this->model->get_by_id($pid)->row();
       $vendor = $this->vendor->get_detail($purchase_return->vendor);

       $data['pono'] = $purchase_return->no;
       $data['logo'] = $this->properti['logo'];
       $data['podate'] = tglin($purchase_return->dates);
       $data['vendor'] = $this->vendor->get_vendor_name($purchase_return->vendor);
       $data['address'] = $vendor->address;
       $data['city'] = $vendor->city;
       $data['phone'] = $vendor->phone1;
       $data['phone2'] = $vendor->phone2;
       $data['user'] = $this->user->get_username($purchase_return->user);
       $data['currency'] = $purchase_return->currency;
       $data['docno'] = $purchase_return->docno;

       $data['cost'] = floatval($purchase_return->costs);
       $data['balance'] = floatval($purchase_return->balance);
       
        $items = null;
        foreach ($this->transmodel->get_last_item($pid)->result() as $res) {
            $items[] = array ("id"=>$res->id, 
                              "product"=> $this->product($res->product,'sku').' - '.$this->product($res->product,'name'), 
                              "qty"=> $res->qty.' - '. $this->product($res->product,'unit'),
                              "unit_price"=>floatval($res->price),
                              "tax"=>floatval($res->tax),
                              "amount"=>floatval($res->amount)
                             );    
        }
        $data['items'] = $items;

       $this->output = $data;
       }else { $this->reject_token('Invalid Token or Expired..!'); }
       $this->response('content');
   }

// ===================================== PRINT ===========================================

// ====================================== REPORT =========================================

    function report()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

        $vendor = $this->input->post('cvendor');
        $cur = $this->input->post('ccurrency');
        
        $start = $this->input->post('start');
        $end = $this->input->post('end');
        $acc = $this->input->post('cacc');

        $data['currency'] = strtoupper($cur);
        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        $data['rundate'] = tglin(date('Y-m-d'));
        $data['log'] = $this->decodedd->log;

//        $data['purchase_returns'] = $this->model->report($cur,$vendor,$start,$end,$acc)->result();
        
        $reports = null;
        foreach ($this->model->report($cur,$vendor,$start,$end,$acc)->result() as $res) {
            $reports[] = array ("id"=>$res->id, 
                                "date"=> tglin($res->dates),
                                "no"=> "PR-00".$res->no,
                                "account"=>floatval($res->price),
                                "vendor"=>$res->prefix.' '.$res->name,
                                "amount"=>floatval($res->total-$res->tax),
                                "tax"=>floatval($res->tax),
                                "cost"=>floatval($res->costs),
                                "balance"=>floatval($res->total-$res->costs),
                                "status"=> $this->xstatus($res->status)
                             );    
        }
        
        $total = $this->model->total($cur,$vendor,$start,$end,$acc);
        $data['total'] = floatval($total['total'] - $total['tax']);
        $data['tax'] = floatval($total['tax']);
        $data['costs'] = floatval($total['costs']);
        $data['balance'] = floatval($total['total'] + $total['costs']);
        $data['items'] = $reports;
        
        $this->output = $data;
        
        }else { $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('content');
        
    }
    
    private function xstatus($val)
    { if ($val == 0){ $val = 'Credit'; } else { $val = 'Settled'; } return $val; }	

// ====================================== REPORT =========================================

   // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); $this->transmodel->closing(); } 
    
}

?>