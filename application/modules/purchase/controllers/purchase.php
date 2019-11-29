<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Purchase extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Purchase_model', 'model', TRUE);
        $this->load->model('Purchase_item_model', 'transmodel', TRUE);

        $this->properti = $this->property->get();
//        $this->acl->otentikasi();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->unit = new Unit_lib();
        $this->vendor = new Vendor_lib();
        $this->user = new Admin_lib();
        $this->tax = new Tax_lib();
        $this->journalgl = new Journalgl_lib();
        $this->product = new Product_lib();
        $this->ap = new Ap_payment_lib();
        $this->branch = new Branch_lib();
        $this->stock = new Stock_lib();
        $this->wt = new Warehouse_transaction_lib();
        $this->trans = new Trans_ledger_lib();
        $this->pr = new Purchase_return_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        $this->decoded = $this->api->otentikasi('decoded');
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $request, $branch, $stock, $wt, $trans,$decoded;
    private $vendor,$user,$tax,$journalgl,$product,$currency,$unit,$ap,$pr,$api,$acl;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
            
            $datax = (array)json_decode(file_get_contents('php://input'));
            if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
            if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
            
            $vendor = null; $date = null;
            if (isset($datax['vendor'])){ $vendor = $datax['vendor']; }
            if (isset($datax['date'])){ $date = $datax['date']; }
            if($vendor == null & $date == null){ $result = $this->model->get_last_purchase($this->limitx, $this->offsetx)->result(); }
            else {$result = $this->model->search($vendor,$date)->result(); }  

            foreach($result as $res)
            {
                $this->output[] = array ("id"=>$res->id, "code"=>'PO-'.$res->no, "no"=>$res->no, "docno"=>$res->docno, "currency"=>strtoupper($res->currency), "date"=>tglin($res->dates), "account"=>ucfirst($res->acc), "vendor"=>$this->vendor->get_vendor_name($res->vendor),
                                         "note"=>$res->notes, "amount"=>floatval($res->total + $res->costs), "balance"=>floatval($res->p2), "trans_status"=>$this->status($res->status),
                                         "status"=>$res->approved);
            }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }

    // function get purchase list dengan parameter status 0 atau 1
    function get_list()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
            
          $datax = (array)json_decode(file_get_contents('php://input'));
          $vendor = null; $status = null;

          if (isset($datax['vendor'])){ $vendor = $datax['vendor']; }
          if (isset($datax['status'])){ $status = $datax['status']; }
            
          $purchases = $this->model->get_purchase_list('IDR',$vendor,$status)->result();
          foreach ($purchases as $res)
          {
              $this->output[] = array ("id"=>$res->id, "code"=>'PO-'.$res->no, "no"=>$res->no, "docno"=>$res->docno, "currency"=>strtoupper($res->currency), "date"=>tglin($res->dates), "account"=>ucfirst($res->acc), "vendor"=>$this->vendor->get_vendor_name($res->vendor),
                                       "note"=>$res->notes, "amount"=>floatval($res->total+$res->costs), "balance"=>floatval($res->p2), "trans_status"=>$this->status($res->status),
                                       "status"=>$res->approved);             
          }
        
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }
    
    // api function
    function item_list($po)
    {
        $this->acl->otentikasi($this->title);
        $purchase = $this->model->get_purchase_by_no($po)->row();
        $items = $this->transmodel->get_last_item($po)->result();
        
        $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="1" class="tablemaster">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('No', 'Product', 'Qty', 'Unit');

        $i = 0;
        foreach ($items as $res)
        {
            $this->table->add_row
            ( ++$i, $this->product->get_name($res->product), $res->qty, $this->product->get_unit($res->product) );
        }

        $data['table'] = $this->table->generate();
        $this->load->view('purchase_item_list', $data); 
    }
    
    private function status($val=0)
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
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
        $purchase = $this->model->get_by_id($pid)->row();

        if ($purchase->approved == 1){ $this->error = "$this->title already approved..!"; $this->status = 401; }
        elseif ($this->valid_period($purchase->dates) == FALSE){ $this->error = "Invalid Period..!"; $this->status = 401; }
        else
        {
            $total = $purchase->total;
            if ($total == 0 && $purchase->p2 == 0){ $this->error = "$this->title has no value..!"; $this->status = 401; }
            else
            {
                $this->over_status($purchase->no); // over status
                                
                // add stock & warehouse transaction
                $this->add_stock($pid);
                
                // membuat kartu hutang
                if ($purchase->status == 0){ $this->trans->add($purchase->acc, 'PO', $purchase->no, $purchase->currency, $purchase->dates, 0, $purchase->p2, $purchase->vendor, 'AP'); }

                //  create journal
                $this->create_po_journal($pid, $purchase->dates, strtoupper($purchase->currency), 'PO-00'.$purchase->no.'-'.$purchase->notes, 'PJ',
                                         $purchase->no, 'AP', $purchase->total + $purchase->costs, $purchase->p1,$purchase->p2);
                
                $data = array('approved' => 1);
                if ($this->model->update($pid, $data) == true){ $this->error = "PO-0$purchase->no confirmed..!";}else{ $this->error = 'Failed to confirm'; $this->status = 401; }
            }
        }
        
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }
    
    private function add_stock($pid)
    {
       $purchase = $this->model->get_by_id($pid)->row();
       $list = $this->transmodel->get_last_item($pid)->result();
       
       foreach ($list as $res) {
           $this->stock->add_stock($res->product, $purchase->dates, $res->qty, $res->amount); // adding stock
           $this->wt->add($purchase->dates, 'PO-00'.$purchase->no, $this->branch->get_branch(), $purchase->currency, $res->product, $res->qty, 0,
                           $res->price, $res->price*$res->qty,
                           $this->decoded->log);
       }
    }
    
    private function rollback_stock($pid)
    {
       $purchase = $this->model->get_by_id($pid)->row();
       $list = $this->transmodel->get_last_item($pid)->result();
       
       foreach ($list as $res) {
           $this->stock->increase_stock($res->product, $purchase->dates,$res->qty); // rollback stock
       }
       $this->wt->remove($purchase->dates, 'PO-00'.$purchase->no);    
    }
    
    private function cek_confirmation($po=null,$page=null)
    {
        $purchase = $this->model->get_purchase_by_no($po)->row();

        if ( $purchase->approved == 1 )
        {
           $this->session->set_flashdata('message', "Can't change value - PO-00$po approved..!"); // set flash data message dengan session
           if ($page){ redirect($this->title.'/'.$page.'/'.$po); } else { redirect($this->title); } 
        }
    }

//    ===================== approval ===========================================


    private function create_po_journal($pid,$date,$currency,$code,$codetrans,$no,$type,$amount,$p1,$p2)
    {
        $cm = new Control_model();
        
        $landed   = $cm->get_id(1); // biaya pengiriman barang
        $tax      = $cm->get_id(9); // pajak dibayar dimuka
//        $stock    = $cm->get_id(47); // piutang persediaan 
        $stock    = $this->branch->get_default_acc_branch();
        $ap       = $cm->get_id(11); // hutang usaha
        $bank     = $cm->get_id(12); // bank
        $kas      = $cm->get_id(13); // kas
        $kaskecil = $cm->get_id(14); // kas kecil
        $account = 0;
        
        $purchase = $this->model->get_by_id($pid)->row();
        switch ($purchase->acc) { case 'bank': $account = $bank; break; case 'cash': $account = $kas; break; case 'pettycash': $account = $kaskecil; break; }
        
        if ($p1 > 0)
        {  
           // create journal- GL
           $this->journalgl->new_journal('0'.$no,$date,'PJ',$currency,$code,$amount, $this->decoded->log);
           $this->journalgl->new_journal('0'.$no,$date,'CD',$currency,'DP Payment : PJ-00'.$no,$p1, $this->decoded->log);
           
           $jid = $this->journalgl->get_journal_id('PJ','0'.$purchase->no);
           $dpid = $this->journalgl->get_journal_id('CD','0'.$purchase->no);
           
           $this->journalgl->add_trans($jid,$stock,$purchase->total-$purchase->tax-$purchase->discount-$purchase->over_amount,0); // tambah persediaan
           $this->journalgl->add_trans($jid,$ap,0,$purchase->p1+$purchase->p2); // hutang usaha
           if ($purchase->tax > 0){ $this->journalgl->add_trans($jid,$tax,$purchase->tax,0); } // pajak pembelian
           if ($purchase->costs > 0){ $this->journalgl->add_trans($jid,$landed,$purchase->costs,0); } // landed costs
           
           //DP proses
           $this->journalgl->add_trans($dpid,$ap,$purchase->p1,0); // potongan hutang usaha
           $this->journalgl->add_trans($dpid,$account,0,$purchase->p1); // potongan bank pembelian
           
        }
        else 
        { 
           $this->journalgl->new_journal('0'.$no,$date,'PJ',$currency,$code,$amount, $this->decoded->log);
           
           $jid = $this->journalgl->get_journal_id('PJ','0'.$purchase->no);
            
           $this->journalgl->add_trans($jid,$stock,$purchase->total-$purchase->tax-$purchase->discount-$purchase->over_amount,0); // tambah persediaan
           $this->journalgl->add_trans($jid,$ap,0,$purchase->p1+$purchase->p2); // hutang usaha
           if ($purchase->tax > 0){ $this->journalgl->add_trans($jid,$tax,$purchase->tax,0); } // pajak pembelian
           if ($purchase->costs > 0){ $this->journalgl->add_trans($jid,$landed,$purchase->costs,0); } // landed costs
        }
    }

    function delete($uid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
            
            $val = $this->model->get_by_id($uid)->row();
            if ( $this->valid_period($val->dates) == TRUE && $this->ap->cek_relation_trans($val->no,'no','PO') == TRUE && $this->pr->cek_relation($val->no, 'purchase'))
            {
               if ($val->approved == 1){ $this->error = $this->rollback($uid,$val->no); } else { $this->error = $this->remove($uid,$val->no); }
            }
            elseif ($this->valid_period($val->dates) != TRUE){ $this->error = 'Invalid Period'; $this->status = 401; }
            else{ $this->error =  "$this->title can't removed, journal approved, related to another component..!"; $this->status = 401; } 

        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }
    
    private function rollback($uid,$po)
    {
      $this->journalgl->remove_journal('PJ', '0'.$po); // journal gl
      $this->journalgl->remove_journal('CD', '0'.$po);   
      $this->over_status($po, 1);
      
      // rollback stock & warehouse transaction
      $this->rollback_stock($uid);
      
      // rollback kartu hutang
      $val = $this->model->get_by_id($uid)->row();
      $this->trans->remove($val->dates, 'PO', $val->no);
      
      $trans = array('approved' => 0);
      $this->model->update($uid, $trans);
      return "$this->title successfully rollback..!";
    }
    
    private function remove($uid,$po)
    {
       $this->transmodel->delete_po($uid); // model to delete purchase item
       $this->model->force_delete($uid); // memanggil model untuk mendelete data
       return "$this->title successfully removed..!";
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('cvendor', 'Vendor', 'required');
        $this->form_validation->set_rules('tno', 'PO - No', 'required|numeric|callback_valid_no');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tshipping', 'Shipping', 'required');
        $this->form_validation->set_rules('tdocno', 'Doc NO', '');

        if ($this->form_validation->run($this) == TRUE)
        {
//            if ($this->input->post('tpr')){ $this->add_request($this->input->post('tno'), $this->input->post('tpr')); }
            $purchase = array('vendor' => $this->input->post('cvendor'), 'no' => $this->input->post('tno'), 
                              'request' => 0, 'status' => 0, 'docno' => $this->input->post('tdocno'),
                              'dates' => $this->input->post('tdate'), 'acc' => $this->input->post('cacc'), 'currency' => $this->input->post('ccurrency'), 
                              'notes' => $this->input->post('tnote'), 'desc' => $this->input->post('tdesc'), 'shipping_date' => $this->input->post('tshipping'), 
                              'user' => $this->decoded->userid,
                              'log' => $this->decoded->log);
            
            if ($this->model->add($purchase) == true){ $this->error = $this->model->max_id();}else{ $this->error = 'Failure Saved..'; $this->status = 401; }
        }
        else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }
    
    private function add_request($po,$req)
    {
       $res = $this->request->get_request($req)->result();  
       
       foreach ($res as $val)
       {
           $pitem = array('product' => $val->product, 'purchase_id' => $po, 'qty' => $val->qty, 'price' => 0, 'amount' => 0, 'tax' => 0);
           $this->transmodel->add($pitem);
       }
    }

    function get($pid=null)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
           
            $purchase = $this->model->get_by_id($pid)->row();

            $data['no'] = $purchase->no;
            $data['over'] = $this->ap->combo_over($purchase->vendor,$purchase->currency);

            $data['vendor'] = $purchase->vendor;
            $data['request'] = $purchase->request;
            $data['date'] = $purchase->dates;
            $data['acc'] = $purchase->acc;
            $data['currency'] = $purchase->currency;
            $data['note'] = $purchase->notes;
            $data['desc'] = $purchase->desc;
            $data['shipping'] = $purchase->shipping_date;
            $data['user'] = $this->user->get_username($purchase->user);
            $data['docno'] = $purchase->docno;

            $data['tax'] = $purchase->tax;
            $data['totaltax'] = $purchase->total;
            $data['p1'] = $purchase->p1;
            $data['costs'] = $purchase->costs;
            $data['total'] = $purchase->p2;

            $data['over'] = $purchase->ap_over;
            $data['overamount'] = $purchase->over_amount;

    //        ============================ Purchase Item  =========================================
            $items = null;
            foreach ($this->transmodel->get_last_item($pid)->result() as $value) {
                $items[] = array("id"=>$value->id,"purchase_id"=>$value->purchase_id,"product_id"=>$value->product,"sku"=> $this->product->get_sku($value->product),
                                 "product"=> $this->product->get_name($value->product), "qty"=>$value->qty,"price"=>floatval($value->price),"tax"=>floatval($value->tax),"total"=>floatval($value->amount),"amount"=> floatval($value->tax+$value->amount));
            }
            $data['items'] = $items;
        
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
    }

    
    function get_item($id=null,$pid=null)
    {
       if ($this->acl->otentikasi1($this->title) == TRUE && isset($id) && isset($pid)){
           
          $data = null; 
          if ($this->transmodel->valid_id($id) == FALSE){ $this->error = 'Invalid Transaction ID'; $this->status = 401; } 
          if ($this->model->valid_add_trans($pid, $this->title) == FALSE){ $this->error = 'Invalid Purchase ID'; $this->status = 401; } 
          
          if ($this->error == null){
            $val = $this->transmodel->get_by_id($id)->row();
//            $data['taxcombo'] = $this->tax->combo();
            $data['product_id'] = $val->product;
            $data['sku'] = $this->product->get_sku($val->product);
            $data['product'] = $this->product->get_name($val->product);
            $data['qty'] = $val->qty;
            $data['amount'] = $val->price;
            $data['tax'] = $val->tax;
          }
                    
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
    }
    
    function edit_item($id,$pid)
    {   
        if ($this->acl->otentikasi2($this->title) == TRUE && isset($id) && isset($pid)){
        
        $purchase = $this->model->get_by_id($pid)->row();
        $data = null; 
        if ($this->transmodel->valid_id($id) == FALSE){ $this->error = 'Invalid Transaction ID'; $this->status = 401; } 
        if ($this->model->valid_add_trans($pid, $this->title) == FALSE){ $this->error = 'Invalid Purchase ID'; $this->status = 401; }     
        if ($this->valid_confirmation($purchase->no) == FALSE){ $this->error = "Can't change value - Order approved..!"; $this->status = 401; }     
           
        if ($this->error == null){
            $this->form_validation->set_rules('titem', 'Item Name', 'required');
            $this->form_validation->set_rules('tqty', 'Qty', 'required|numeric');
            $this->form_validation->set_rules('tamount', 'Unit Price', 'required');
            $this->form_validation->set_rules('ctax', 'Tax', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {
                $pitem = array('product' => $this->product->get_id_by_sku($this->input->post('titem')), 'purchase_id' => $pid, 'qty' => $this->input->post('tqty'),
                               'price' => $this->input->post('tamount'),
                               'amount' => $this->input->post('tqty') * $this->input->post('tamount'),
                               'tax' => $this->tax->calculate($this->input->post('ctax'),$this->input->post('tqty'),$this->input->post('tamount')));
                if ( $this->transmodel->update($id,$pitem) == true && $this->update_trans($pid) == true){ $this->error = 'Item transaction posted..!';}else{
                   $this->error = 'Failure to posted transaction..!'; $this->status = 401; 
                }
            }
        }
        
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status); 
    }
    
//    ======================  Item Transaction   ===============================================================

    function add_item($pid=null)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
            $po = $this->model->get_by_id($pid)->row();

            $this->form_validation->set_rules('titem', 'Item Name', 'required');
            $this->form_validation->set_rules('tqty', 'Qty', 'required|numeric');
            $this->form_validation->set_rules('tamount', 'Unit Price', 'required');
            $this->form_validation->set_rules('ctax', 'Tax', 'required');

            if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($po->no) == TRUE && $pid != null)
            {   
                $pitem = array('product' => $this->product->get_id_by_sku($this->input->post('titem')), 'purchase_id' => $pid, 'qty' => $this->input->post('tqty'),
                               'price' => $this->input->post('tamount'),
                               'amount' => $this->input->post('tqty') * $this->input->post('tamount'),
                               'tax' => $this->tax->calculate($this->input->post('ctax'),$this->input->post('tqty'),$this->input->post('tamount')));
               
                if ($this->transmodel->add($pitem) == true && $this->update_trans($pid) == true){ $this->error = 'Item transaction posted..!'; 
                }else{ $this->error = 'Failure to posted transaction..!'; $this->status = 401; }
            }
            elseif ( $this->valid_confirmation($po->no) != TRUE ){ $this->error = "Can't change value - Journal approved..!"; $this->status = 401; }
            elseif (!$pid){ $this->error = "Can't change value - Journal not created..!"; $this->status = 401; }
            else{ $this->error = validation_errors(); $this->status = 401; } 
            
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }

    private function update_trans($pid)
    {
        $totals = $this->transmodel->total($pid);
        $purchase = array('tax' => $totals['tax'], 'total' => $totals['amount'] + $totals['tax']);
	return $this->model->update($pid, $purchase);
    }

    function delete_item($id)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && isset($id)){ 
            
            $pid = $this->transmodel->get_by_id($id)->row();
            $purchase = $this->model->get_by_id($pid->purchase_id)->row();
        
            if ($this->transmodel->valid_id($id) == true && $this->valid_confirmation($purchase->no) == TRUE){
                if ($this->transmodel->delete($id) == true && $this->update_trans($pid->purchase_id) == true){ $this->error = 'Transaction removed..!';
                }else{ $this->error = 'Failure to posted transaction..!'; $this->status = 401;  }
            }
            else{ $this->error = "Journal approved, can't deleted..!"; $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }
//    ==========================================================================================

    // Fungsi update untuk mengupdate db
    function update($pid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
   
	// Form validation
        $this->form_validation->set_rules('cvendor', 'Vendor', 'required');
        $this->form_validation->set_rules('tno', 'PO - No', 'required|numeric|callback_valid_confirmation');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tshipping', 'Shipping', 'required');
        $this->form_validation->set_rules('tcosts', 'Landed Cost', 'required|numeric');
        $this->form_validation->set_rules('tp1', 'Down Payment', 'required|numeric');
        $this->form_validation->set_rules('cover', 'Credit / Debit Memo', 'required|numeric|callback_validation_over['.$this->input->post('tno').']');

        if ($this->form_validation->run($this) == TRUE)
        {
            $purchases = $this->model->get_by_id($pid)->row();
            $purchase = array('vendor' => $this->input->post('cvendor'), 'log' => $this->decoded->log, 'docno' => $this->input->post('tdocno'),
                              'dates' => $this->input->post('tdate'), 'acc' => $this->input->post('cacc'), 'currency' => $this->input->post('ccurrency'),
                              'notes' => $this->input->post('tnote'), 'desc' => $this->input->post('tdesc'),
                              'shipping_date' => $this->input->post('tshipping'), 'user' => $this->user->get_id($this->input->post('tuser')),
                              'costs' => $this->input->post('tcosts'), 'p1' => $this->input->post('tp1'), 'over_amount' => $this->input->post('toveramount'), 'ap_over' => $this->input->post('cover'),
                              'p2' => $this->calculate_balance($this->input->post('tcosts'),$purchases->total,$this->input->post('tp1'),$this->input->post('toveramount')),
                              'status' => $this->get_status($this->calculate_balance($this->input->post('tcosts'),$purchases->total,$this->input->post('tp1'),$this->input->post('toveramount')))
                             );
//
            if ($this->model->update($pid, $purchase) == true){ $this->error = "One $this->title data successfully updated!";}else{ $this->error = 'Failure to posted transaction..!'; $this->status = 401; }
        }
        else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error), $this->status); 
    }
    
    private function over_status($po,$type=0)
    {
       $purchases = $this->model->get_purchase_by_no($po)->row(); 
       
       if ($purchases->ap_over != 0){ $data = array('credit_over' => 1); }
       else { $data = array('credit_over' => 0); }
       
       if ($type != 0){ $data = array('credit_over' => 0); }
       return $this->ap->set_over_stts($purchases->ap_over, $data);
    }

    private function calculate_balance($cost,$total,$p1,$over)
    {
        $res=0;
        $res = $cost + $total;
        $res = $res - $p1 - $over;
        return $res;
    }

    private function get_status($p2=null)
    { if ($p2 == 0){ return 1; } else { return 0; } }

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
        if ($this->model->valid_no($no) == FALSE)
        {
            $this->form_validation->set_message('valid_no', "Order No already registered.!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    function validation_over($no,$po)
    {
	if ($no != 0)
        {
            $val = $this->model->get_purchase_by_no($po)->row();
            
            if ($this->model->validating_over($no,$val->id) == FALSE)
            {
               $this->form_validation->set_message('validation_over', 'This Credit / Debit Memo is already registered!');
               return FALSE;  
            }
            else { return TRUE; }
        }
        else{ return TRUE; }
    }

    public function valid_confirmation($no)
    {
        $purchase = $this->model->get_purchase_by_no($no)->row();

        if ($purchase->approved == 1)
        {
            $this->form_validation->set_message('valid_confirmation', "Can't change value - Order approved..!.!");
            return FALSE;
        }
        else {  return TRUE; }
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

   function invoice($pid=null)
   {
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
       $this->acl->otentikasi2($this->title);
       $purchase = $this->model->get_by_id($pid)->row();
       $vendor = $this->vendor->get_by_id($purchase->vendor)->row();

       $data['pono'] = $purchase->no;
       $data['logo'] = $this->properti['logo'];
       $data['podate'] = tgleng($purchase->dates);
       $data['vendor'] = $vendor->prefix.' '.$vendor->name;
       $data['address'] = $vendor->address;
       $data['city'] = $vendor->city;
       $data['phone'] = $vendor->phone1;
       $data['phone2'] = $vendor->phone2;
       $data['desc'] = '';
       $data['user'] = $this->user->get_username($purchase->user);
       $data['currency'] = strtoupper($purchase->currency);
       $data['docno'] = $purchase->docno;
       $data['log'] = $this->decoded->log;

       $data['cost'] = $purchase->costs;
       $data['p2'] = $purchase->p2;
       $data['p1'] = $purchase->p1;
       $data['over'] = $purchase->over_amount;
       
       if ($purchase->ap_over > 0){ $data['ap_over'] = 'CD-00'.$purchase->ap_over.' / '.tglin($this->ap->get_dates($purchase->ap_over)); }
       else { $data['ap_over'] = ""; }
       
       // terbilang
        $amount = explode('.', $purchase->p2);
       $tt = new Terbilang();
       if ($purchase->currency == 'IDR'){  $data['terbilang'] = $tt->baca($amount[0]).' rupiah'; }
       else { $data['terbilang'] = $tt->baca($amount[0]); } 
       
       $items = null;
       foreach ($this->transmodel->get_last_item($pid)->result() as $value) {
            $items[] = array("id"=>$value->id,"purchase_id"=>$value->purchase_id,"product_id"=>$value->product,"sku"=> $this->product->get_sku($value->product),
                             "product"=> $this->product->get_name($value->product), "qty"=>$value->qty,"price"=>floatval($value->price),"tax"=>floatval($value->tax),"total"=>floatval($value->amount),"amount"=> floatval($value->tax+$value->amount));
       }
       $data['items'] = $items;

       // property display
       $data['p_name'] = $this->properti['name'];
       $data['paddress'] = $this->properti['address'];
       $data['p_phone1'] = $this->properti['phone1'];
       $data['p_phone2'] = $this->properti['phone2'];
       $data['p_city'] = ucfirst($this->properti['city']);
       $data['p_zip'] = $this->properti['zip'];
       $data['p_npwp'] = '';

       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
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
        
        $type = $this->input->post('ctype');
        $status = $this->input->post('cstatus');
        $acc = $this->input->post('cacc');

        $data['currency'] = strtoupper($cur);
        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        $data['acc'] = $acc;
        $data['rundate'] = tglin(date('Y-m-d'));
        $data['log'] = $this->decoded->log;

//        Property Details
        $data['company'] = $this->properti['name'];

        $output[] = $this->model->report($vendor,$cur,$start,$end,$status,$acc)->result();
        $data['result'] = $output;
        $total = $this->model->total($vendor,$cur,$start,$end,$status,$acc);
        
        $data['total'] = $total['total'] - $total['tax'];
        $data['tax'] = $total['tax'];
        $data['p1'] = $total['p1'];
        $data['p2'] = $total['p2'];
        $data['costs'] = $total['costs'];
        $data['ptotal'] = $total['total'] + $total['costs'];
        $this->output = $data;
        
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
        
//        if ($type == '1'){ $page = "purchase_report_details"; }elseif ($type == '0'){ $page = "purchase_report"; }elseif ($type == '2'){ $page = "purchase_pivot"; }
//        if ($this->input->post('cformat') == 0){  $this->load->view($page, $data); }
//        elseif ($this->input->post('cformat') == 1)
//        {
//            $pdf = new Pdf();
//            $pdf->create($this->load->view($page, $data, TRUE));
//        }
    }
    
    
    function report_product()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){
        $data['title'] = $this->properti['name'].' | Report '.ucwords($this->modul['title']);

        $product = $this->product->get_id_by_sku($this->input->post('titem'));
        $cur = $this->input->post('ccurrency');
        $start = $this->input->post('start');
        $end = $this->input->post('end');

        $data['currency'] = strtoupper($cur);
        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        $data['rundate'] = tgleng(date('Y-m-d'));
        $data['log'] = $this->decoded->log;

//        Property Details  
        $data['company'] = $this->properti['name'];
        $output[] = $this->model->report_product($product,$cur,$start,$end)->result();
        $data['result'] = $output;
        $this->output = $data;
        
//        $page = "purchase_product_report";
//        $this->load->view($page, $data);
        
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
        
//        if ($type == '0'){ $page = "purchase_product_report"; }elseif ($type == '1'){ $page = "purchase_product_pivot"; }
//        if ($this->input->post('cformat') == 0){  $this->load->view($page, $data); }
//        elseif ($this->input->post('cformat') == 1)
//        {
//            $pdf = new Pdf();
//            $pdf->create($this->load->view($page, $data, TRUE));
//        }
    }
    
// ====================================== REPORT =========================================
    
// ====================================== AJAX =========================================    
   
    function get_over()
    {
       $no = $this->input->post('tno'); 
       if (!$no){ echo '0'; }else { echo $this->ap->get_over_payment($no); }
    }
    
   // ====================================== CLOSING ======================================
   function reset_process(){ $this->model->closing(); $this->transmodel->closing(); }

}

?>