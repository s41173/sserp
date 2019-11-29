<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stock_transfer extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Stock_transfer_model', 'model', TRUE);
        $this->load->model('Stock_transfer_item_model', 'transmodel', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency = new Currency_lib();
        $this->load->library('unit_lib');
        $this->product = new Product_lib();
        $this->user = new Admin_lib();
        $this->wt = new Warehouse_transaction_lib();
//        $this->opname = new Opname();
        $this->journalgl = new Journalgl_lib();
        $this->account = new Account_lib();
        $this->branch = new Branch_lib();
        $this->stock = new Stock_lib();
        $this->stockledger = new Stock_ledger_lib();
        $this->period = new Period_lib();
        $this->period = $this->period->get();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title, $stockvalue=0, $journalgl, $stock, $stockledger;
    private $user,$product,$wt,$opname,$currency,$account,$branch,$period,$api,$acl;
    
    protected $error = null;
    protected $status = 200;
    protected $output = null;

    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $datax = (array)json_decode(file_get_contents('php://input')); 
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
        $date = null; if (isset($datax['date'])){ $date = $datax['date']; }
        
        if($date == null){ $result = $this->model->get_last($this->limitx, $this->offsetx)->result();}
        else{ $result = $this->model->search($date)->result(); }
        
        $resx = null;
	foreach($result as $res)
	{   
           $resx[] = array ("id"=>$res->id, "no"=>$res->no, "date"=>tglin($res->dates), "currency"=>$res->currency,
                            "from"=> $this->branch->get_name($res->branch_from), "to"=> $this->branch->get_name($res->branch_to), "description"=>$res->desc, "staff"=>$res->staff, 
                            "user"=>$this->user->get_username($res->user),
                            "log"=> $res->log, "posted"=>$res->approved
                           );
	}
        $data['result'] = $resx; $data['counter'] = $this->model->counter(); $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    } 
    
    // =============== approval ===========================================

    private function post_status($val)
    {
       if ($val == 0) {$class = "notapprove"; }
       elseif ($val == 1){$class = "approve"; }
       return $class;
    }

    function confirmation($pid)
    {
       if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){   
        $stock_adjustment = $this->model->get_by_id($pid)->row();

        if ($stock_adjustment->approved == 1) { $this->reject("$this->title already approved..!"); }
        elseif ($stock_adjustment->balance == 0){ $this->reject("error|Invalid Balance...!"); }
        else
        {
            // start transaction 
            $this->db->trans_start();
            $data = array('approved' => 1);
            $this->model->update($pid, $data);
           
           // create journal
           $this->create_journal($pid, $stock_adjustment->branch_from, $stock_adjustment->branch_to, $stock_adjustment->dates, 'IDR', 'TRAJ-00'.$stock_adjustment->no, $stock_adjustment->no, $stock_adjustment->balance); // create journal

           // add wt
           $this->add_warehouse_transaction($stock_adjustment->id);
           $this->db->trans_complete();
           
           if ($this->db->trans_status() === FALSE){ $this->reject("TRAJ-00$stock_adjustment->no failed confirmed..!");  }
           else { $this->reject("IAJ-00$stock_adjustment->no confirmed..!"); }
        }
      }else{ $this->reject_token(); }
      $this->response();
    }
    
    private function create_journal($pid,$from,$to,$date,$currency,$code,$no,$amount)
    {
        $stock_from  = $this->branch->get_acc($from, 'stock'); // stock
        $stock_to    = $this->branch->get_acc($to, 'stock'); // stock
        
        $this->journalgl->new_journal('00'.$no,$date,'TRAJ',$currency,$code,intval($amount), $this->session->userdata('log'));
        $jid = $this->journalgl->get_journal_id('TRAJ','00'.$no);
        
        $this->journalgl->add_trans($jid, $stock_to, $amount, 0); // stock to bertambah
        $this->journalgl->add_trans($jid,$stock_from, 0, $amount); // stock berkurang
    }

    private function add_warehouse_transaction($pid)
    {
        $val  = $this->model->get_by_id($pid)->row();
        $list = $this->transmodel->get_last_item($pid)->result();

        foreach ($list as $value)
        {
           $this->wt->add( $val->dates, 'TRAJ-00'.$val->no, $val->branch_from, $val->currency, $value->product_id, 0, $value->qty,
                           $value->price, $value->amount, $this->session->userdata('log')); 
           
           $this->wt->add( $val->dates, 'TRAJ-00'.$val->no, $val->branch_to, $val->currency, $value->product_id, $value->qty, 0,
                           $value->price, $value->amount, $this->session->userdata('log')); 
        }
    }

    private function del_warehouse_transaction($po=0)
    {
        $val  = $this->model->get_stock_adjustment_by_no($po)->row();
        $this->wt->remove($val->dates, 'TRAJ-00'.$po);        
    }
//    ===================== approval ===========================================


    function delete($uid)
    {
      if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        $val = $this->model->get_by_id($uid)->row();

        if ( $val->approved == 1 ){ $this->rollback($uid,$val->no); }
        else{ $this->remove($uid,$val->no);}

      }else{ $this->reject_token(); }
      $this->response();
    }
    
    private function rollback($uid,$po)
    {
       $this->db->trans_start(); 
       $this->journalgl->remove_journal('TRAJ', '00'.$po); // journal gl  
       $this->del_warehouse_transaction($po); 
       $data = array('approved' => 0);
       $this->model->update($uid, $data);
       $this->db->trans_complete();
       
       if ($this->db->trans_status() === FALSE){ $this->reject("1 $this->title canceled rollback..!"); }
       else{ $this->error = "1 $this->title successfully rollback..!"; }
    }
    
    private function remove($uid)
    {
       $this->db->trans_start(); 
       $stockadj = $this->model->get_by_id($uid)->row(); 
       $stockitem = $this->transmodel->get_last_item($uid)->result();
       
       if ($stockitem)
       {
          foreach($stockitem as $res)
          {   
           if ($res->type == 'out'){ $this->stock->rollback('SA', $stockitem->stock_adjustment, $res->id);   }
           elseif ($res->type == 'in') { 
               $this->stock->increase_stock($res->product_id, $stockadj->dates, $res->qty); 
           }
          } 
       }

       $this->transmodel->delete_po($uid);
       $this->model->force_delete($uid); 
       $this->db->trans_complete();
       
       if ($this->db->trans_status() === FALSE){ $this->reject("1 $this->title canceled removed..!"); }
       else { $this->error = "1 $this->title successfully removed..!"; }
    }

    private function cek_relation($id=null)
    { $return = $this->return_stock->cek_relation($id, $this->title); if ($return == TRUE) { return TRUE; } else { return FALSE; } }
    
    function add()
    {
         if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tno', 'IAJ - No', 'required|numeric|callback_valid_no');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tstaff', 'Workshop Staff', 'required');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('cfrom', 'From Outlet', 'required|callback_valid_branch');
        $this->form_validation->set_rules('cto', 'To Outlet', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $stock_adjustment = array('no' => $this->input->post('tno'), 'approved' => 0, 'staff' => $this->input->post('tstaff'), 
                                      'currency' => $this->input->post('ccurrency'), 'dates' => $this->input->post('tdate'), 
                                      'branch_from' => $this->input->post('cfrom'), 'branch_to' => $this->input->post('cto'),
                                      'desc' => $this->input->post('tnote'), 'user' => $this->decodedd->userid,
                                      'log' => $this->decodedd->log, 'created' => date('Y-m-d H:i:s'));

            if ($this->model->add($stock_adjustment) == true){ $this->error = $this->model->max_id();}else{ $this->error = 'Failure Saved..'; $this->status = 401; }
        }
        else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token(); }
        $this->response(); 
    }
    
    function get($id)
    {
      if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($id, $this->title) == TRUE){  
       
        $cash = $this->model->get_by_id($id)->row();
        $data['no'] = $cash->no;
        $data['id'] = $id;
        $data['dates'] = $cash->dates;
        $data['staff'] = $cash->staff;
        $data['currency'] = $cash->currency;
        $data['note'] = $cash->desc;
        $data['from'] = $cash->branch_from;
        $data['to'] = $cash->branch_to;
        
        $items = null;
        foreach ($this->transmodel->get_last_item($id)->result() as $value) {
            $items[] = array("id"=>$value->id,"product_id"=>$value->product_id, "sku"=> $this->product->get_sku($value->product_id), "unit"=> $this->product->get_unit($value->product_id),
                             "product"=> $this->product->get_name($value->product_id), "qty"=>$value->qty,
                             "amount"=>floatval($value->price));
        }
        $data['items'] = $items;
        $this->output = $data;
      }else{ $this->reject_token(); }
      $this->response('c');
    }


//    ======================  Item Transaction   ===============================================================

    function add_item($pid=null)
    {         
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){  
        $this->form_validation->set_rules('tproduct', 'Item Name', 'required|callback_valid_request['.$this->input->post('tqty').']');
        $this->form_validation->set_rules('tqty', 'Qty', 'required|numeric');   

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE)
        {
            $qty = $this->input->post('tqty');

            // start transaction 
            $this->db->trans_start();
            $id = $this->transmodel->counter();
            $price = $this->stock->min_stock($this->product->get_id_by_sku($this->input->post('tproduct')),
                                             $qty, $pid, 'TR', $id);
            
            $this->stock->rollback('TR', $pid, $id);
            $pitem = array('id' => $id, 'product_id' => $this->product->get_id_by_sku($this->input->post('tproduct')), 
                           'stock_transfer' => $pid, 'qty' => $qty, 'price' => $price, 'amount' => intval($qty*$price));

            $this->transmodel->add($pitem);
            $this->update_trans($pid);
            $this->db->trans_complete();
           
            if ($this->db->trans_status() == FALSE){ $this->reject(); } else { $this->error = "Transaction Posted"; }
        }
        else{ $this->reject(validation_errors()); }
      }else{ $this->reject_token(); }
      $this->response();
    }
    
    private function update_trans($pid)
    {
        $total = $this->transmodel->total($pid);
        $pitem = array('balance' => $total['amount']);
        $this->model->update($pid, $pitem);
    }

    function delete_item($id)
    {
      if ($this->acl->otentikasi2($this->title) == TRUE && $this->transmodel->valid_add_trans($id) == TRUE){
        
        $stockitem = $this->transmodel->get_item_by_id($id);
        $stockadj = $this->model->get_by_id($stockitem->stock_transfer)->row();
        
        if ( $this->valid_confirmation($stockitem->stock_transfer) == TRUE ){
            
        $this->transmodel->delete($id);
        $this->update_trans($stockitem->stock_transfer);
        $this->error = 'Transaction removed..!';
        
        }else{ $this->reject("Journal approved, can't deleted..!"); }
        }else{ $this->reject_token(); }
      $this->response();
    }
    
//    ==========================================================================================

    function update($pid=null)
    {
      if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){ 
	// Form validation
        $this->form_validation->set_rules('tno', 'IAJ - No', 'required|numeric');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tstaff', 'Workshop Staff', 'required');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('cfrom', 'From Outlet', 'required|callback_validating_branch['.$pid.']');
        $this->form_validation->set_rules('cto', 'To Outlet', 'required');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirmation($pid) == TRUE)
        {   
            $stock_adjustment = array('staff' => $this->input->post('tstaff'), 
                                      'currency' => $this->input->post('ccurrency'), 'dates' => $this->input->post('tdate'), 
                                      'branch_from' => $this->input->post('cfrom'), 'branch_to' => $this->input->post('cto'),
                                      'desc' => $this->input->post('tnote'), 'user' => $this->decodedd->userid,
                                      'log' => $this->decodedd->log);

            if ($this->model->update($pid,$stock_adjustment) == true){ $this->error = "$this->title data successfully updated!|";}else{ $this->error = 'Failure Saved..'; $this->status = 401; }
        }
        elseif ($this->valid_confirmation($pid) != TRUE){ $this->reject("Journal approved, can't deleted..!"); }
        else{ $this->reject(validation_errors()); }
      }else{ $this->reject_token(); }
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
    
    public function valid_account($acc)
    {
        if ($this->input->post('ctype') == 'in')
        {
            if (!$acc){ $this->form_validation->set_message('valid_account', "Account Chart Required.!"); return FALSE; }
            else { return TRUE; }
        }
        else { return TRUE; }
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
    
    public function valid_branch($from)
    {
        $to = $this->input->post('cto');
        if ($from == $to)
        {
            $this->form_validation->set_message('valid_branch', "Source & Destination Can't Equal..!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    public function validating_branch($from,$id)
    {
        $to = $this->input->post('cto');
        
        if ($this->transmodel->get_last_item($id)->num_rows() > 0){
            $this->form_validation->set_message('validating_branch', "Can't Change Source / Destination..!");
            return FALSE;
        }
        elseif ($from == $to){
            $this->form_validation->set_message('validating_branch', "Source & Destination Can't Equal..!");
            return FALSE;
        }
        else {  return TRUE; }
    }
    
    function valid_request($product,$request)
    {
        $branch = $this->input->post('tfrom');
        $pid = $this->product->get_id_by_sku($product);
        $qty = $this->stockledger->get_qty($pid, $branch, $this->period->month, $this->period->year);
        
        if ($request > $qty){
           $this->form_validation->set_message('valid_request', "Qty Not Enough..!");
           return FALSE;
        }else{ return TRUE; }
    }

    public function valid_opname($desc)
    {
        if ( $this->opname->cek_begindate() == FALSE )
        {
           $this->form_validation->set_message('valid_opname', "Inventory Taking Not Created...!!");
           return FALSE;
        }
        else { return TRUE; }
    }

    public function valid_confirmation($pid)
    {
        $stockin = $this->model->get_by_id($pid)->row();

        if ( $stockin->approved == 1 )
        {
           $this->form_validation->set_message('valid_confirmation', "Can't change value - transaction approved..!");
           return FALSE;
        }
        else { return TRUE; }
    }

// ===================================== PRINT ===========================================
  
   function invoice($pid=null)
   {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){  

       $stock_adjustment = $this->model->get_by_id($pid)->row();

       $data['no'] = $stock_adjustment->no;
       $data['podate'] = tglin($stock_adjustment->dates);
       $data['user'] = $this->user->get_username($stock_adjustment->user);
       $data['staff'] = $stock_adjustment->staff;
       $data['log'] = $this->decodedd->log;
       $data['from'] = $this->branch->get_name($stock_adjustment->branch_from);
       $data['to'] = $this->branch->get_name($stock_adjustment->branch_to);
       
       $items = null;
       foreach ($this->transmodel->get_last_item($pid)->result() as $value) {
           $items[] = array("id"=>$value->id,"product_id"=>$value->product_id, "sku"=> $this->product->get_sku($value->product_id), "unit"=> $this->product->get_unit($value->product_id),
                            "product"=> $this->product->get_name($value->product_id), "qty"=>$value->qty,
                            "amount"=>floatval($value->price));
       }
       $data['items'] = $items;
       $this->output = $data;
       }else{ $this->reject_token(); }
      $this->response('c');
   }

// ===================================== PRINT ===========================================

// ====================================== REPORT =========================================


    function report()
    {
      if ($this->acl->otentikasi1($this->title) == TRUE){

        $start = $this->input->post('start');
        $end = $this->input->post('end');
        
        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        $data['rundate'] = tgleng(date('Y-m-d'));
        $data['log'] = $this->decodedd->log;

//        $data['reports'] = $this->model->report($start,$end)->result();
//        $data['reports_category'] = $this->model->report_category($start,$end)->result();
        
        $items = null;
        foreach ($this->model->report_category($start,$end)->result() as $res) {
            
            $items[] = array ("id"=>$res->id, "code"=>'TRAJ-00'.$res->no, "no"=>$res->no, "date"=>tglin($res->dates),
                              "from"=> $this->branch->get_name($res->branch_from), "to"=> $this->branch->get_name($res->branch_to), "description"=>$res->desc, "staff"=>$res->staff, 
                              "product"=> $this->product->get_name($res->product_id), 
                              "sku"=> $this->product->get_sku($res->product_id), "unit"=> $this->product->get_unit($res->product_id), 
                              "qty"=>$res->qty, "amount"=>floatval($res->price), "balance"=>floatval($res->qty*$res->price)
                             );
        }
        $data['items'] = $items;
        $this->output = $data;
        
      }else{ $this->reject_token(); }
      $this->response('c');
    }


// ====================================== REPORT =========================================
    
// ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); $this->transmodel->closing(); } 

}

?>