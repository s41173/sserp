<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Journalgl extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Journal_model', 'jm', TRUE);
        
        $this->properti = $this->property->get();
//        $this->acl->otentikasi();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
       
        $this->currency = new Currency_lib();
        $this->user = new Admin_lib();
        $this->journaltype = new Journaltype_lib();
        $this->account = new Account_lib();
        $this->classi = new Classification_lib();
        $this->ledger = new Ledger_lib();
        $this->period = new Period_lib();

        $this->model = new Gl();
        $this->mitem = new Transaction();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title,$model,$mitem,$journaltype;
    private $user,$currency,$account,$classi,$ledger,$period, $api, $acl;

    function set_profit_loss(){
      if ($this->acl->otentikasi1($this->title) == TRUE){  
        $this->ledger->set_profit_loss();
        $this->error = 'Processing Calculate Profit Loss';
      }else{ $this->reject_token(); }
      $this->response();
    }
    
    public function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $datax = (array)json_decode(file_get_contents('php://input')); 
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
            
        if(!isset($datax['code']) && !isset($datax['no']) && !isset($datax['date'])){ 
            $result = $this->jm->get_last($this->limitx, $this->offsetx)->result(); 
            $this->count = $this->jm->get_last($this->limitx, $this->offsetx,1); 
        }
        else { $result = $this->jm->search($datax['code'],$datax['no'],$datax['date'])->result(); 
               $this->count = $this->jm->search($datax['code'],$datax['no'],$datax['date'],1);  
        }
        
        if ($result){
          foreach($result as $res)
          {  
             $this->resx[] = array ("id" => $res->id, "no" => $res->no, "code" => $res->code,
                                      "date" => $res->dates, "currency" => $res->currency, "notes" => $res->notes, 
                                      "balance" => $res->balance, "posted" => $res->approved);
          }
        }
        
        $data['record'] = $this->count; 
        $data['result'] = $this->resx;
        $this->output = $data;
            
        }else{ $this->reject_token(); }
        $this->response('c');
    }   
//    ===================== approval ===========================================

    private function post_status($val)
    {
       if ($val == 0) {$class = "notapprove"; }
       elseif ($val == 1){$class = "approve"; }
       return $class;
    }

    function confirmation($pid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->jm->valid_add_trans($pid, $this->title) == TRUE){
            $journal = $this->model->where('id', $pid)->get();
            $ps = $this->period->get();

            if ($journal->approved == 1) { $this->error = "$this->title already approved..!"; $this->status = 403; }
            elseif ($journal->balance == 0){ $this->error = "$this->title has no value..!"; $this->status = 403; }
            else
            {
                if ($this->cek_cf($pid) == TRUE){$this->model->cf = 1;}else{$this->model->cf = 0;}
                $this->model->approved = 1;
                $this->model->save();
                $this->ledger->set_profit_loss($journal->currency);
                $this->error = $journal->code."-".$journal->no." confirmed..!";
            }
        }else{ $this->valid_404($this->jm->valid_add_trans($pid, $this->title)); $this->reject_token(); }
        $this->response();
    }
    
    private function cek_cf($pid)
    {
       $ac = new Account_lib();
       $result = $this->mitem->where('gl_id', $pid)->get();
       $res = FALSE;
       foreach ($result as $val){ if ($ac->get_classi($val->account_id) == 7 || $ac->get_classi($val->account_id) == 8){ $res = TRUE; break; } }
       return $res;
    }


//    ===================== approval ===========================================

    private function delete_by_code($uid){
       $val = $this->model->where('id', $uid)->get(); 
       $this->mitem->where('gl_id', $uid)->get();
       $this->mitem->delete_all();
       $val->delete();
       $this->ledger->set_profit_loss($val->currency);
       $this->error = "1 $this->title successfully removed..!";
    }

    function delete($uid=0,$code=null)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE){
            
            if ($code != null){ 
               $val = $this->model->where('code',$code)->where('no',$uid)->get(); 
               $this->delete_by_code($val->id);
            }
            else{ $val = $this->model->where('id', $uid)->get(); 
                $cur = $this->model->currency;
                $jid = $this->jm->cek_trans('id',$uid);
                if ( $jid == TRUE )
                { 
                   if ($val->approved == 1) {
                       $val->approved = 0;
                       $val->save();
                       $this->ledger->set_profit_loss($cur);
                       $this->error = "1 $this->title successfully rollback..!";
                   }else{
                      $this->mitem->where('gl_id', $uid)->get();
                      $this->mitem->delete_all();
                      $val->delete();
                      $this->error = "1 $this->title successfully removed..!";
                   }
                }
                else{ $this->error = "Journal-ID not found..!"; $this->status = 404; } 
            }   
        }else{ $this->reject_token(); }
        $this->response();
    }

    function counter($type='GJ')
    { 
        if ($this->acl->otentikasi1($this->title) == TRUE && isset($type)){
            $res = 0;
            if ( $this->model->count() > 0 )
            {
               $this->model->select_max('no');
               $this->model->where('code', $type)->get();
               $res = intval($this->model->no)+1;
            }  
            else{ $res = 1; }
            $this->error = $res;
        }else{ $this->reject_token(); }
       $this->response();
    }
    
    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tno', 'No', 'required|numeric|callback_valid_no');
        $this->form_validation->set_rules('ctype', 'CodeType', 'required|callback_valid_type');
//        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required|callback_valid_period');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('cpost', 'Posted', 'numeric');

        if ($this->form_validation->run($this) == TRUE)
        {
            $decoded = $this->api->otentikasi('decoded');
            
            if ($this->input->post('cpost') != 1){ $post = 0; }else{ $post = 1; }
            $this->model->no       = $this->input->post('tno');
            $this->model->code     = $this->input->post('ctype');
            $this->model->dates    = $this->input->post('tdate');
            $this->model->currency = strtoupper($this->input->post('ccurrency'));
            $this->model->docno    = $this->input->post('tdocno');
            $this->model->notes    = $this->input->post('tnote');
            $this->model->desc     = $this->input->post('tdesc');
            $this->model->log      = $decoded->log;
            $this->model->approved = $post;
            $this->model->created  = date('Y-m-d H:i:s');
            
            if ($this->model->save() != true){ $this->reject();
            }else{ $this->jm->log('create'); $this->output = $this->jm->get_latest(); } 
        }
        else{ $this->reject(validation_errors(),400); }
        }else { $this->reject_token(); }
        $this->response('c');
    }

    // fungsi get jurnal type
    
    function get($id=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->jm->valid_add_trans($id, $this->title) == TRUE){
        
            $journal = $this->jm->get_by_id($id)->row();
            $data['no'] = $journal->no;
            $data['type'] = $journal->code;

            $data['dates'] = $journal->dates;
            $data['currency'] = $journal->currency;
            $data['note'] = $journal->notes;
            $data['desc'] = $journal->desc;
            $data['docno'] = $journal->docno;
            $data['balance'] = $journal->balance;

            $data['total'] = $journal->balance;
            $res = $this->get_debit_credit($id);
            $data['debit']   = $res[0];
            $data['credit']  = $res[1];
            $data['balance'] = $res[2]; 
            
            foreach ($this->jm->get_transaction($id)->result() as $res) {
                $this->resx[] = array ("id" => $res->id, "acc_id" => $res->account_id, 
                                       "acc_code" => $this->account->get_code($res->account_id), "acc_name" => $this->account->get_name($res->account_id),
                                       "debit" => $res->debit, "credit" => $res->credit, "vamount" => $res->vamount);
            }
            $data['items'] = $this->resx;
            $this->output = $data;

        }else{ $this->valid_404($this->jm->valid_add_trans($id, $this->title)); $this->reject_token(); }
        $this->response('c');
    }

//    ======================  Item Transaction   ===============================================================
    function add_multiple_item($uid=0){
      if ($this->acl->otentikasi2($this->title) == TRUE && $this->jm->valid_add_trans($uid, $this->title)){
        $journalgl = new Journalgl_lib();  
        $datax = (array)json_decode(file_get_contents('php://input')); 
        $x=0;
        for ($i=0;$i<count($datax);$i++){
          if (isset($datax[$i]->item) && isset($datax[$i]->debit) && isset($datax[$i]->credit)){
              if ($this->valid_coa($datax[$i]->item) == TRUE && floatval($datax[$i]->debit) != 0 || floatval($datax[$i]->credit) != 0){
                $journalgl->add_trans($uid,$this->account->get_id_code($datax[$i]->item),floatval($datax[$i]->debit),floatval($datax[$i]->credit));
              }else{ $x++; $this->error = $x." - Invalid COA"; }
          }
          else{ $this->reject('Invalid JSON Format'); break; }
        }
      }else { $this->reject_token(); }
      $this->response();
    }

    function add_item($uid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->jm->valid_add_trans($uid, $this->title) == TRUE){
        
            $this->form_validation->set_rules('titem', 'Item Name', 'required|callback_valid_coa');
            $this->form_validation->set_rules('tdebit', 'Debit', 'required|numeric');
            $this->form_validation->set_rules('tcredit', 'Credit', 'required|numeric');

            $jid = $this->jm->cek_trans('id',$uid);
            
            if ($this->form_validation->run($this) == TRUE && $jid == TRUE && $this->valid_confirmation($uid) == TRUE)
            {
                $this->mitem->gl_id = $uid;
                $this->mitem->account_id = $this->account->get_id_code($this->input->post('titem'));
                $this->mitem->debit = $this->input->post('tdebit');
                $this->mitem->credit = $this->input->post('tcredit');
                $this->mitem->vamount = $this->calculate_vamount($this->account->get_id_code($this->input->post('titem')), $this->input->post('tdebit'), $this->input->post('tcredit'));

                $this->mitem->save();
                $this->update_trans($uid);
                $this->error = 'Transaction Saved';
            }
            elseif ( $this->valid_confirmation($uid) != TRUE ){ $this->reject("Can't change value - Journal approved..!"); }
            elseif ( $jid != TRUE ){ $this->reject("Journal-ID not found..!"); }
            else{ $this->reject(validation_errors(),400); }
            
       }else { $this->valid_404($this->jm->valid_add_trans($uid, $this->title)); $this->reject_token(); }
       $this->response();
    }

    private function calculate_vamount($acc,$debit=0,$credit=0)
    {
        $type = $this->classi->get_type($this->account->get_classi($acc));
        $res = 0;

        if ($type == 'harta'){ $res = 0 + $debit - $credit; }
        elseif ($type == 'kewajiban'){ $res = 0 - $debit + $credit; }
        elseif ($type == 'modal'){ $res = 0 - $debit + $credit; }
        elseif ($type == 'pendapatan'){ $res = 0 - $debit + $credit; }
        elseif ($type == 'biaya'){ $res = 0 + $debit - $credit; }
        return $res;
    }

    private function update_trans($po)
    {
        if ($this->cek_balance($po) == TRUE)
        {
            $this->mitem->select_sum('debit');
            $this->mitem->where('gl_id',$po)->get();

            $this->model->where('id', $po)->get();
            $this->model->balance = $this->mitem->debit;
        }
        else
        {
            $this->model->where('id', $po)->get();
            $this->model->balance = 0;
        }

        $this->model->save();
    }

    private function cek_balance($id)
    {
        $this->mitem->select_sum('debit');
        $this->mitem->select_sum('credit');
        $this->mitem->where('gl_id',$id)->get();
        $debit = intval($this->mitem->debit);
        $credit = intval($this->mitem->credit);
        if ($debit!=$credit){ return FALSE; } else{ return TRUE; }
    }

    private function get_debit_credit($id)
    {
        $this->mitem->select_sum('debit');
        $this->mitem->select_sum('credit');
        $this->mitem->where('gl_id',$id)->get();
        $debit = $this->mitem->debit;
        $credit = $this->mitem->credit;

        $res = null;
        $res[0] = $debit;
        $res[1] = $credit;
        $res[2] = $debit-$credit;
        return $res;
    }

    function delete_item($id)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && isset($id) && $this->jm->get_glid($id) != null){

        $jid = $this->jm->get_glid($id)->row();            
        $jid = $jid->gl_id;
        
        if ($this->valid_confirmation($jid) == TRUE )
        {
            $this->mitem->where('id',$id)->get();
            $this->mitem->delete();
            $this->update_trans($jid);
            $this->error = 'Transaction removed..!';
        }
        else{ $this->reject("Journal approved, can't deleted..!"); }
       }
       elseif ($this->jm->get_glid($id) == null){ $this->reject("Invalid Journal-ID"); }
       else { $this->reject_token(); }
       $this->response();
    }
//    ==========================================================================================

    // Fungsi update untuk mengupdate db
    function update($jid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->jm->valid_add_trans($jid, $this->title) == TRUE){

	// Form validation
        $this->form_validation->set_rules('tdocno', 'Document-No', 'required');
        $this->form_validation->set_rules('tdate', 'Invoice Date', 'required');
        $this->form_validation->set_rules('tnote', 'Note', 'required');
        $this->form_validation->set_rules('tdesc', 'Description', '');

        $validjurnal = $this->jm->cek_trans('id',$jid);
        if ($this->form_validation->run($this) == TRUE && $validjurnal == TRUE && $this->valid_confirmation($jid) == TRUE)
        {
            $decoded = $this->api->otentikasi('decoded');
            $this->model->where('id',$jid)->get();

            $this->model->dates    = $this->input->post('tdate');
            $this->model->docno    = $this->input->post('tdocno');
            $this->model->notes    = $this->input->post('tnote');
            $this->model->desc     = $this->input->post('tdesc');
            $this->model->log      = $decoded->log;

            if ($this->model->save() == true){ $this->error = "One $this->title data successfully updated..!"; }
            else{ $this->reject(); }
        }
        elseif ( $this->valid_confirmation($uid) != TRUE ){ $this->reject("Can't change value - Journal approved..!"); }
        elseif ( $validjurnal != TRUE ){ $this->reject("Journal-ID not found..!"); }
        else{ $this->reject(validation_errors(),400); }
       }else { $this->valid_404($this->jm->valid_add_trans($jid, $this->title));  $this->reject_token(); }
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
    
    public function valid_type($type){
        
        if ($this->journaltype->valid_code($type) == FALSE){
            $this->form_validation->set_message('valid_type', "Unknown journal code..!");
            return FALSE;
        }else{ return TRUE; }
    }
    
    public function valid_coa($val){
        
        if ($this->account->valid_coa($val) == FALSE){ $this->form_validation->set_message('valid_coa', "Unregistered COA..!"); return FALSE; }
        else{ return TRUE; }
    }

    public function valid_no($no)
    {
        $this->model->where('code', $this->input->post('ctype'));
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

// ====================================== REPORT =========================================

    function report()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){
        
            $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
            $this->form_validation->set_rules('cjournal', 'Jurnal Type', 'required|callback_valid_type');
            $this->form_validation->set_rules('tstart', 'Start Period', 'required');
            $this->form_validation->set_rules('tend', 'End Period', 'required');
            
            if ($this->form_validation->run($this) == TRUE)
            {
                $data['title'] = $this->properti['name'].' | Report '.ucwords($this->modul['title']);
                $cur = $this->input->post('ccurrency');
                $journal = $this->input->post('cjournal');
                $start = $this->input->post('tstart');
                $end = $this->input->post('tend');
                
                $data['currency'] = $cur;
                $data['start'] = $start;
                $data['end'] = $end;
                $data['rundate'] = tgleng(date('Y-m-d'));
                $data['log'] = $this->session->userdata('log');

                // Property Details
                $data['company'] = $this->properti['name'];
                $trans = $this->jm->report($cur,$journal,$start,$end)->result();
                
                foreach ($trans as $res) {
                    $data['transaction'][] =  array ("id" => $res->id, "no" => $res->no, "code" => $res->code,
                                          "date" => $res->dates, "currency" => $res->currency, "notes" => $res->notes, 
                                          "balance" => $res->balance, "posted" => $res->approved);
                }
                
                $this->output = $data;
            }
            else{ $this->reject(validation_errors(),400); }
       }else { $this->reject_token(); }
       $this->response('c');
    }
    
        // ====================================== CLOSING ====================================== 
   function reset_process(){ 
       if ($this->acl->otentikasi3($this->title) == TRUE){
          $this->jm->closing(); $this->jm->closing_trans(); 
          $this->error = 'Closing Process Successfull';
       }else { $this->reject_token(); }
       $this->api->response(array('error' => $this->error), $this->status);  
   }


// ====================================== REPORT =========================================

}

?>