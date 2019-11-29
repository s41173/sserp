<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Checkout extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Checkout_model', 'model', TRUE);
        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->bank = new Bank_lib();
        $this->vendor = new Vendor_lib();
        $this->account = new Account_lib();
        $this->ap_payment = new Ap_payment_lib();
        $this->ar_payment = new Ar_payment_lib();
        $this->journal = new Journalgl_lib();
        $this->currency = new Currency_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    private $properti, $modul, $title, $journal, $currency, $api, $acl;
    private $bank,$vendor,$account,$ap_payment,$ar_payment;

    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $datax = (array)json_decode(file_get_contents('php://input')); 
        $no = null; $start = null; $end=null; $type=null;
        if (isset($datax['no'])){ $no = $datax['no']; }
        if (isset($datax['start'])){ $start = $datax['start']; }
        if (isset($datax['end'])){ $end = $datax['end']; }
        if (isset($datax['type'])){ $type = $datax['type']; }
//        
        $result = $this->model->search($no, $start, $end, $type)->result();
        $code = $this->code($type);
        
        $resx = null;
	foreach($result as $res)
	{
           $resx[] = array ("code"=>$code.$res->no, "no"=>$res->no, "dates"=>tglin($res->dates), "due"=>tglin($res->due), "type"=>$type,
                            "currency"=>$res->currency, "checkno"=>$res->check_no, "account_id"=>$res->account, "account"=>$this->get_acc($res->account), 
                            "amount"=>floatval($res->amount), "log"=> $this->decodedd->log);
	}
        $data['result'] = $resx;
        $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    }  
    
    private function get_acc($acc){ return $this->account->get_code($acc).' : '.$this->account->get_name($acc); }

    private function code($val)
    {
        if ($val == 'purchase') { $val = 'CD-00'; } elseif ($val == 'ap') { $val = 'DJ-00'; }
        elseif ($val == 'ar') { $val = 'DJ-000'; }
        elseif ($val == 'ar_refund') { $val = 'RF-00'; } elseif ($val == 'nar_refund') { $val = 'NRF-00'; }
        return $val;
    }
    
    function process()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){ 
           
       // Form validation
       $this->form_validation->set_rules('no', 'Check-No', 'required');
       $this->form_validation->set_rules('type', 'Type', 'required');
       $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|is_natural_no_zero');
       $this->form_validation->set_rules('acc', 'Account', 'required');
       $this->form_validation->set_rules('due', 'Due Date', 'required');
           
       if ($this->form_validation->run($this) == TRUE){
    //       $no,$type,$amount=0,$acc,$due,$cur
           $cur = 'IDR';
           $no = $this->input->post('no');
           $type = $this->input->post('type');
           $amount = $this->input->post('amount');
           $acc = $this->input->post('acc');
           $due = $this->input->post('due');
           $code = '00';

           if ($type == 'purchase'){ $this->ap_payment->set_post_stts($no, $data); $notes = 'AP-Payment : CD-00'.$no; }
           elseif ($type == 'ap'){ $this->ap_payment_cash->set_post_stts($no, $data); $notes = 'AP : DJ-00'.$no; }
           elseif ($type == 'ar'){ $this->ar_payment->set_post_stts($no, $data); $notes = 'AR : CR-0'.$no; $code = '000'; }
           
           if ($this->journal->cek_journal($code.$no, 'GJ', $due, strtoupper($cur)) == TRUE){
           $data = array('post_dated_stts' => 1);

           $cm = new Control_model();
           $ap       = $cm->get_id(35); // hutang giro
           $ar       = $cm->get_id(61); // piutang giro
           $account  = $acc;                
            // create journal- GL

           $this->journal->new_journal($code.$no,$due,'GJ', strtoupper($cur),'Cheque Process '.$notes,$amount, $this->session->userdata('log'));
           $dpid = $this->journal->get_journal_id('GJ',$code.$no);

           if ($type='ar'){
               $this->journal->add_trans($dpid,$account,$amount,0); // kas
               $this->journal->add_trans($dpid,$ar,0,$amount); // piutang giro
           }else{ $this->journal->add_trans($dpid,$account,0,$amount); // kas
               $this->journal->add_trans($dpid,$ap,$amount,0); // hutang giro
           }

           $this->error = "Checkout journal created..!";
           } else{ $this->reject("Journal already processed..!"); }
           
       }else{ $this->reject(validation_errors()); }
       }else { $this->reject_token(); }
       $this->response();
    }

    private function alert_date($due)
    {
        $due = strtotime($due);
        $now = strtotime(date('Y-m-d'));
        $res = null;
        if ($now > $due) { $res = "btn btn-success btn-xs"; } else { $res = "btn btn-danger btn-xs"; } return $res;
    }

    function report()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){ 
        
        $type = $this->input->post('type');        
        $start = $this->input->post('start');
        $end = $this->input->post('end');

        $data['start'] = $start;
        $data['end'] = $end;
        $data['rundate'] = tgleng(date('Y-m-d'));
        $data['log'] = $this->decodedd->log;
        $data['type'] = $type;

        // Property Details
        $data['company'] = $this->properti['name'];
//        $data['reports'] = $this->model->report($start,$end,$type)->result();
        
        $items=null;
        foreach ($this->model->report($start,$end,$type)->result() as $res) {
           
           $val = $this->bank->get_details($res->bank)->row();
           if ($val){ $bank = $val->acc_no.' - '.$val->currency.' - '.$val->acc_bank; }else{ $bank = ''; }
            
           $items[] = array ("no"=>$res->no, "dates"=>tglin($res->dates), "due"=>tglin($res->due), "type"=>$type,
                             "bank"=> $bank, "checkno"=>$res->check_no, "vendor"=> $this->vendor->get_vendor_name($res->vendor), 
                             "status"=> $this->purchase_status($res->due), 
                             "amount"=>floatval($res->amount), "log"=> $this->decodedd->log);
        }
        $data['items'] = $items;  $this->output = $data;

        }else { $this->reject_token(); }
        $this->response('content');
    }
    
    private function purchase_status($due)
    {
       $due = strtotime($due);
       $now = strtotime(date('Y-m-d'));
       $res = null;
       if ($now > $due) { $res = "paid"; } else { $res = "waiting"; } return $res;
    }
    
    
    // ====================================== CLOSING ======================================
    function reset_process(){  } 
   
}

?>