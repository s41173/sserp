<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once 'definer.php';

require_once APPPATH.'libraries/jwt/JWT.php';
use \Firebase\JWT\JWT;

class Main extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Main_model', '', TRUE);
        
        $this->load->library('property');
        $this->load->library('user_agent');
        $this->properti = $this->property->get();

//        $this->acl->otentikasi();
        $this->period = new Period_lib();
        $this->period = $this->period->get();
        $this->customer = new Customer_lib();
        $this->vendor = new Vendor_lib();
        $this->api = new Api_lib();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');  
    }

    var $title = 'main';
    var $limit = null;
    private $properti,$period,$customer,$vendor,$api;
    protected $error = null;
    protected $status = 200;
    protected $output = null;

    private function user_agent()
    {
        $agent=null;
        if ($this->agent->is_browser()){  $agent = $this->agent->browser().' '.$this->agent->version();}
        elseif ($this->agent->is_robot()){ $agent = $this->agent->robot(); }
        elseif ($this->agent->is_mobile()){ $agent = $this->agent->mobile(); }
        else{ $agent = 'Unidentified User Agent'; }
        return $agent." - ".$this->agent->platform();
    }
    
    function index()
    {
        if ($this->api->otentikasi() == TRUE){
           $status = 200;

           $output['useragent'] = $this->user_agent();
           $output['month'] = get_month($this->period->month);
           $output['year'] = $this->period->year;
           $output['com_name'] = $this->properti['name'];
           $output['min_product'] = $this->get_min_product();
           $output['check_out'] = $this->get_check_out();
           $output['ar_list'] = $this->get_ar_list();
           $output['ap_list'] = $this->get_ap_list();
           $output['ap_chart'] = $this->ap_chart();
           $output['ar_chart'] = $this->ar_chart();

        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status);
    }
    
    // ================ API =================
    
    private function get_min_product(){
        
        $output = null;
        $result = $this->Main_model->get_min_product()->result();
        foreach($result as $res){
          $output[] = array ("sku" => $res->sku, "name" => $res->name, "qty" => $res->qty, "unit" => $res->unit, "currency" => $res->currency);
        }
        return $output;
    }
    
    private function get_check_out(){
        
        $output = null;
        $result = $this->Main_model->checkout('ap_payment')->result();

        foreach($result as $res){
            $output[] = array ("code" => 'CR-00'.$res->no, "check_no" => $res->check_no, "currency" => $res->currency,
                               "date" => tglin($res->dates), "due" => tglin($res->due), "amount" => idr_format($res->amount));
        }
        return $output;
    }
   
    private function ar_chart()
    {        
        $val1 = $this->Main_model->get_last_ar_between(30,0)->row_array();
        $val2 = $this->Main_model->get_last_ar_between(60,30)->row_array();
        $val3 = $this->Main_model->get_last_ar_between(90,60)->row_array();
        $val4 = $this->Main_model->get_last_ar(90)->row_array();
        
        $data = array(
                    array(
                        "label" => "0 - 30 Day",
                        "y" => floatval($val1['amount'])
                    ),
                    array(
                        "label" => "30 - 60 Day",
                        "y" => floatval($val2['amount'])
                    ),
                    array(
                        "label" => "60 - 90 Day",
                        "y" => floatval($val3['amount'])
                    ),
                    array(
                        "label" => "> 90 Day",
                        "y" => floatval($val4['amount'])
                    )
                );
       return $data;
    }
    
    private function get_ar_list(){
        
        $output = null;
        $result = $this->Main_model->get_ar_list()->result();
        foreach($result as $res){
            $output[] = array ("code" => 'SO-0'.$res->id, "date" => tglin($res->dates), "customer" => $this->customer->get_name($res->cust_id), "amount" => idr_format($res->amount));
        }
        return $output;
    }
    
    private function ap_chart()
    {        
        $val1 = $this->Main_model->get_last_ap_between(30,0)->row_array();
        $val2 = $this->Main_model->get_last_ap_between(60,30)->row_array();
        $val3 = $this->Main_model->get_last_ap_between(90,60)->row_array();
        $val4 = $this->Main_model->get_last_ap(90)->row_array();
        
        $data = array(
                    array(
                        "label" => "0 - 30 Day",
                        "y" => floatval($val1['total'])
                    ),
                    array(
                        "label" => "30 - 60 Day",
                        "y" => floatval($val2['total'])
                    ),
                    array(
                        "label" => "60 - 90 Day",
                        "y" => floatval($val3['total'])
                    ),
                    array(
                        "label" => "> 90 Day",
                        "y" => floatval($val4['total'])
                    )
                );
       return $data;
    }

    
    private function get_ap_list(){
        
        $output = null;
        $result = $this->Main_model->get_ap_list()->result();
        foreach($result as $res){
            $output[] = array ("code" => 'PO-0'.$res->no, "date" => tglin($res->dates), "vendor" => $this->vendor->get_vendor_name($res->vendor), "amount" => idr_format($res->p2));
        }
        return $output;
    }
    
    // api mobile purpose
    function ap_sum(){
        if ($this->api->otentikasi() == TRUE){
          $result = $this->Main_model->get_ap_sum($this->input->post('type'),$this->input->post('currency'), $this->input->post('tstart'),$this->input->post('tend'));
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $result), $this->status); 
    }
    
    function ar_sum(){
        if ($this->api->otentikasi() == TRUE){
          $result = $this->Main_model->get_ar_sum($this->input->post('type'),$this->input->post('currency'), $this->input->post('tstart'),$this->input->post('tend'));
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $result), $this->status); 
    }
    
    // ------- ringkasan mobile ---------------
    
    // tempel d modul pembelian
    private function ap_summary($cur = 'IDR'){
      $result['apcredit'] = $this->Main_model->get_ap_sum_credit($cur);
      $result['apoverdue'] = $this->Main_model->get_ap_sum_credit_overdue($cur);
      $result['appaymentsum'] = $this->Main_model->get_ap_payment_sum($cur);
      return $result;
    }
    
    // tempel d modul penjualan
    private function ar_summary($cur = 'IDR'){
//    jumlah penjualan yang belum d bayar / status 0
      $result['arcredit'] = $this->Main_model->get_ar_sum_credit($cur);
      $result['aroverdue'] = $this->Main_model->get_ar_sum_credit_overdue($cur);
      $result['arpaymentsum'] = $this->Main_model->get_ar_payment_sum($cur);
      return $result;
    }
    
}

?>