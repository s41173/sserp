<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report_reference extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('Main_model', '', TRUE);
        
        $this->properti = $this->property->get();
        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        
        $this->load->library('user_agent');
        $this->properti = $this->property->get();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    var $title = 'report_reference';
    var $limit = null;
    private $properti,$api,$acl;

    protected $error = null;
    protected $status = 200;
    protected $output = null;

    function index()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){  
       
       // period
        $ps = new Period();
        $ps = $ps->get();
        $data['month'] = get_month($ps->month);
        $data['year'] = $ps->year;
        
       $data['asset'] = $this->get_asset($this->input->post('cassettype'));
       $data['operating'] = $this->get_operating($this->input->post('coperatingtype'));
       $data['asset12'] = $this->get_asset12();
       $data['cashbank'] = $this->get_cash_bank();
       $data['income12'] = $this->get_income12($this->input->post('ctype'));
       $data['outcome12'] = $this->get_outcome12();
       $data['jenis'] = $this->input->post('ctype');
              
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $data), $this->status); 
    }
    
        // api purpose
    function get_income(){
        
        if ($this->acl->otentikasi1($this->title) == TRUE){  
            
            $start = $this->input->post('tstart');
            $end = $this->input->post('tend');
            $cur = $this->input->post('ccur');
            $period = $this->input->post('period');
            $type = $this->input->post('type');
            
            $ps = new Period();
            $ps = $ps->get(); 
            $model = new Account_model();
            $cla = 16;
            $trans = 0;
            // 0 == 1 hari, 1 == 1 bulan, 2 == 1 tahun, 3 == custom

            if ($period == 0){ 
                if ($type == 0){ $trans = $model->get_balance_period_by_classification($cur,$cla,$start,$start); }
                else{ 
$trans = $model->get_balance_period_by_classification($cur,$cla,$start,$start)+$model->get_balance_period_by_classification($cur,21,$start,$start)+$model->get_balance_period_by_classification($cur,37,$start,$start);
                }                
            }
            elseif ($period == 1){ 
                if ($type == 0){ $trans = $model->get_balance_by_classification($cur,$cla,$ps->month,$ps->year,$ps->month,$ps->year); }
                else{
$trans = $model->get_balance_by_classification($cur,$cla,$ps->month,$ps->year,$ps->month,$ps->year)+$model->get_balance_by_classification($cur,21,$ps->month,$ps->year,$ps->month,$ps->year)+$model->get_balance_by_classification($cur,37,$ps->month,$ps->year,$ps->month,$ps->year);
                }
            }
            elseif ($period == 2){
                if ($type == 0){ $trans = $model->get_balance_anual_by_classification($cur,$cla,$ps->year,$ps->year); }
                else{
$trans = $model->get_balance_anual_by_classification($cur,$cla,$ps->year,$ps->year)+$model->get_balance_anual_by_classification($cur,21,$ps->year,$ps->year)+$model->get_balance_anual_by_classification($cur,37,$ps->year,$ps->year);
                }
            }
            elseif ($period == 3){ 
                if ($type == 0){ $trans = $model->get_balance_period_by_classification($cur,$cla,$start,$end); }
else{ $trans = $model->get_balance_period_by_classification($cur,$cla,$start,$end)+$model->get_balance_period_by_classification($cur,21,$start,$end)+$model->get_balance_period_by_classification($cur,37,$start,$end); }                
            }
            
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $trans), $this->status); 
    }
    
    function get_outcome(){
        
        if ($this->acl->otentikasi1($this->title) == TRUE){  
            
            $start = $this->input->post('tstart');
            $end = $this->input->post('tend');
            $cur = $this->input->post('ccur');
            $period = $this->input->post('period');
            $type = $this->input->post('type');
            
            $ps = new Period();
            $ps = $ps->get(); 
            $model = new Account_model();
            $cla = 15;
            $trans = 0;
            // 0 == 1 hari, 1 == 1 bulan, 2 == 1 tahun, 3 == custom

            if ($period == 0){ 
                if ($type == 0){ $trans = $model->get_balance_period_by_classification($cur,$cla,$start,$start)+$model->get_balance_period_by_classification($cur,19,$start,$start); }
                else{ 
$trans = $model->get_balance_period_by_classification($cur,$cla,$start,$start)+$model->get_balance_period_by_classification($cur,19,$start,$start)+$model->get_balance_period_by_classification($cur,24,$start,$start)+$model->get_balance_period_by_classification($cur,17,$start,$start)+$model->get_balance_period_by_classification($cur,25,$start,$start);
                }                
            }
            elseif ($period == 1){ 
                if ($type == 0){ $trans = $model->get_balance_by_classification($cur,$cla,$ps->month,$ps->year,$ps->month,$ps->year)+$model->get_balance_by_classification($cur,19,$ps->month,$ps->year,$ps->month,$ps->year); }
                else{
$trans = $model->get_balance_by_classification($cur,$cla,$ps->month,$ps->year,$ps->month,$ps->year)+$model->get_balance_by_classification($cur,19,$ps->month,$ps->year,$ps->month,$ps->year)+$model->get_balance_by_classification($cur,24,$ps->month,$ps->year,$ps->month,$ps->year)+$model->get_balance_by_classification($cur,17,$ps->month,$ps->year,$ps->month,$ps->year)+$model->get_balance_by_classification($cur,25,$ps->month,$ps->year,$ps->month,$ps->year);
                }
            }
            elseif ($period == 2){
                if ($type == 0){ $trans = $model->get_balance_anual_by_classification($cur,$cla,$ps->year,$ps->year)+$model->get_balance_anual_by_classification($cur,19,$ps->year,$ps->year); }
                else{
$trans = $model->get_balance_anual_by_classification($cur,$cla,$ps->year,$ps->year)+$model->get_balance_anual_by_classification($cur,19,$ps->year,$ps->year)+$model->get_balance_anual_by_classification($cur,24,$ps->year,$ps->year)+$model->get_balance_anual_by_classification($cur,17,$ps->year,$ps->year)+$model->get_balance_anual_by_classification($cur,25,$ps->year,$ps->year);
                }
            }
            elseif ($period == 3){ 
                if ($type == 0){ $trans = $model->get_balance_period_by_classification($cur,$cla,$start,$end)+$model->get_balance_period_by_classification($cur,19,$start,$end); }
else{ $trans = $model->get_balance_period_by_classification($cur,$cla,$start,$end)+$model->get_balance_period_by_classification($cur,19,$start,$end)+$model->get_balance_period_by_classification($cur,24,$start,$end)+$model->get_balance_period_by_classification($cur,17,$start,$end)+$model->get_balance_period_by_classification($cur,25,$start,$end); }                
            }
            
       }else{ $this->reject_token(); }
       $this->api->response(array('error' => $this->error, 'content' => $trans), $this->status); 
    }
    
    function get_asset($month=null)
    {        
        $ps = new Period();
        $ps = $ps->get();

        if (!$month){ $month = $ps->month; }
        //        $data = $this->db->select('OrderDate, ProductName, Quantity')->from('coba')->get()->result();
        $model = new Account_model();
        
        $kas = $this->get_trans('IDR', 7, $month, $ps->year);
        $bank = $this->get_trans('IDR', 8, $month, $ps->year);
        $biayadimuka = $this->get_trans('IDR', 13, $month, $ps->year);
        $piutangusaha = $this->get_trans('IDR', 20, $month, $ps->year);
        $piutangnonusaha = $this->get_trans('IDR', 27, $month, $ps->year);
        $persediaan = $this->get_trans('IDR', 14, $month, $ps->year);
        $investasi = $this->get_trans('IDR', 29, $month, $ps->year);
        $hartawujud = $this->get_trans('IDR', 26, $month, $ps->year);
        $hartatakwujud = $this->get_trans('IDR', 30, $month, $ps->year);
        $hartalain = $this->get_trans('IDR', 31, $month, $ps->year);
        
        $data = array(
                    array("label" => "Kas", "y" => $kas),
                    array("label" => "Bank", "y" => $bank),
                    array("label" => "Biaya Dibayar Dimuka", "y" => $biayadimuka),
                    array("label" => "Piutang Usaha", "y" => $piutangusaha),
                    array("label" => "Piutang Non Usaha", "y" => $piutangnonusaha),
                    array("label" => "Persediaan", "y" => $persediaan),
                    array("label" => "Investasi Jangka Panjang", "y" => $investasi),
                    array("label" => "Harta Tetap Berwujud", "y" => $hartawujud),
                    array("label" => "Harta Tetap Tidak Berwujud", "y" => $hartatakwujud),
                    array("label" => "Harta Lainnya", "y" => $hartalain)
                );

//       echo json_encode($data, JSON_NUMERIC_CHECK);
        return $data;
    }
    
    private function get_trans($cur,$cla,$month,$year)
    {
        $model = new Account_model();
        $begin = $model->get_begining_balance_classification_by_month($cur,$cla,$month,$year);
        $trans = $model->get_balance_by_classification($cur,$cla,$month,$year,$month,$year);
        return intval($begin+$trans);
    }
    
    function get_operating()
    {        
        $ps = new Period();
        $ps = $ps->get();

        //        $data = $this->db->select('OrderDate, ProductName, Quantity')->from('coba')->get()->result();
        $model = new Account_model();
        
        $jan = $model->get_end_balance('IDR',21,1,$ps->year);
        $feb = $model->get_end_balance('IDR',21,2,$ps->year);
        $mar = $model->get_end_balance('IDR',21,3,$ps->year);
        $apr = $model->get_end_balance('IDR',21,4,$ps->year);
        $may = $model->get_end_balance('IDR',21,5,$ps->year);
        $jun = $model->get_end_balance('IDR',21,6,$ps->year);
        $jul = $model->get_end_balance('IDR',21,7,$ps->year);
        $aug = $model->get_end_balance('IDR',21,8,$ps->year);
        $sep = $model->get_end_balance('IDR',21,9,$ps->year);
        $oct = $model->get_end_balance('IDR',21,10,$ps->year);
        $nov = $model->get_end_balance('IDR',21,11,$ps->year);
        $dec = $model->get_end_balance('IDR',21,12,$ps->year);
        
        $data = array(
                    array("label" => "Jan", "y" => intval($jan)),
                    array("label" => "Feb", "y" => intval($feb)),
                    array("label" => "Mar", "y" => intval($mar)),
                    array("label" => "Apr", "y" => intval($apr)),
                    array("label" => "May", "y" => intval($may)),
                    array("label" => "Jun", "y" => intval($jun)),
                    array("label" => "Jul", "y" => intval($jul)),
                    array("label" => "Aug", "y" => intval($aug)),
                    array("label" => "Sep", "y" => intval($sep)),
                    array("label" => "Oct", "y" => intval($oct)),
                    array("label" => "Nov", "y" => intval($nov)),
                    array("label" => "Dec", "y" => intval($dec))
                );

       return $data;
    }
    
    function get_asset12()
    {        
        $ps = new Period();
        $ps = $ps->get();

        //        $data = $this->db->select('OrderDate, ProductName, Quantity')->from('coba')->get()->result();
        
        $data = array(
                    array("label" => "Jan", "y" => $this->calculate_asset('IDR',1,$ps->year)),
                    array("label" => "Feb", "y" => $this->calculate_asset('IDR',2,$ps->year)),
                    array("label" => "Mar", "y" => $this->calculate_asset('IDR',3,$ps->year)),
                    array("label" => "Apr", "y" => $this->calculate_asset('IDR',4,$ps->year)),
                    array("label" => "May", "y" => $this->calculate_asset('IDR',5,$ps->year)),
                    array("label" => "Jun", "y" => $this->calculate_asset('IDR',6,$ps->year)),
                    array("label" => "Jul", "y" => $this->calculate_asset('IDR',7,$ps->year)),
                    array("label" => "Aug", "y" => $this->calculate_asset('IDR',8,$ps->year)),
                    array("label" => "Sep", "y" => $this->calculate_asset('IDR',9,$ps->year)),
                    array("label" => "Oct", "y" => $this->calculate_asset('IDR',10,$ps->year)),
                    array("label" => "Nov", "y" => $this->calculate_asset('IDR',11,$ps->year)),
                    array("label" => "Dec", "y" => $this->calculate_asset('IDR',12,$ps->year))
                );

       return $data;
    }
    
    function get_cash_bank()
    {        
        $ps = new Period();
        $ps = $ps->get();

        //        $data = $this->db->select('OrderDate, ProductName, Quantity')->from('coba')->get()->result();
        $model = new Account_model();
        $data = $model->get_cash_group_account()->result();
        
        $datax = array();
        foreach ($data as $res) 
        {
           $point = array("label" => $res->code.' : '.$res->name , "y" => $this->get_balance_acc($res->id, $ps->month, $ps->year));
           array_push($datax, $point);      
        }
        
//        echo json_encode($datax, JSON_NUMERIC_CHECK);
        return $datax;
    }
    
    private function get_balance_acc($acc,$month,$year)
    {
       $model = new Account_model();
       $vamount = $model->get_balance($acc, $month, $year)->row_array();
       $vamount = intval($vamount['vamount']);
       $start = $model->get_start_balance('IDR',$acc,$month,$year);
       return intval($start+$vamount);
    }
    
    private function calculate_asset($cur='IDR',$month,$year)
    {
       $model = new Account_model();
        
       $kas = $model->get_end_balance_classification('IDR', 7, $month, $year); // kas
       $bank = $model->get_end_balance_classification('IDR', 8, $month, $year); // bank
       $biayadimuka = $model->get_end_balance_classification('IDR', 13, $month, $year); // biayadimuka
       $piutangusaha = $model->get_end_balance_classification('IDR', 20, $month, $year); // piutangusaha
       $piutangnonusaha = $model->get_end_balance_classification('IDR', 27, $month, $year); // piutangnonusaha
       $persediaan = $model->get_end_balance_classification('IDR', 14, $month, $year); // persediaan
       $investasi = $model->get_end_balance_classification('IDR', 29, $month, $year); // investasi
       $hartawujud = $model->get_end_balance_classification('IDR', 26, $month, $year); // hartawujud
       $hartatakwujud = $model->get_end_balance_classification('IDR', 30, $month, $year); // hartatakwujud
       $hartalain = $model->get_end_balance_classification('IDR', 31, $month, $year); // hartalain 
       
       return intval($kas+$bank+$biayadimuka+$piutangusaha+$piutangnonusaha+$persediaan+$investasi+$hartawujud+
                     $hartatakwujud+$hartalain);
    }
    
    
    // income 12 bulan
    function get_income12($type=null)
    {    
       $ps = new Period();
       $ps = $ps->get(); 
       $data = null;
        
        if (!$type || $type == 0 || $type == 2)
        { 
          $cla = 16;
          $data = array(
                array("label" => "Jan", "y" => $this->get_trans_month('IDR',$cla,1,$ps->year)),
                array("label" => "Feb", "y" => $this->get_trans_month('IDR',$cla,2,$ps->year)),
                array("label" => "Mar", "y" => $this->get_trans_month('IDR',$cla,3,$ps->year)),
                array("label" => "Apr", "y" => $this->get_trans_month('IDR',$cla,4,$ps->year)),
                array("label" => "May", "y" => $this->get_trans_month('IDR',$cla,5,$ps->year)),
                array("label" => "Jun", "y" => $this->get_trans_month('IDR',$cla,6,$ps->year)),
                array("label" => "Jul", "y" => $this->get_trans_month('IDR',$cla,7,$ps->year)),
                array("label" => "Aug", "y" => $this->get_trans_month('IDR',$cla,8,$ps->year)),
                array("label" => "Sep", "y" => $this->get_trans_month('IDR',$cla,9,$ps->year)),
                array("label" => "Oct", "y" => $this->get_trans_month('IDR',$cla,10,$ps->year)),
                array("label" => "Nov", "y" => $this->get_trans_month('IDR',$cla,11,$ps->year)),
                array("label" => "Dec", "y" => $this->get_trans_month('IDR',$cla,12,$ps->year))
            );
        }
        elseif ($type == 1)
        {             
            $data = array(
                array("label" => "Jan", "y" => intval($this->get_trans_month('IDR',16,1,$ps->year)+$this->get_trans_month('IDR',21,1,$ps->year)+$this->get_trans_month('IDR',37,1,$ps->year))),
                array("label" => "Feb", "y" => intval($this->get_trans_month('IDR',16,2,$ps->year)+$this->get_trans_month('IDR',21,2,$ps->year)+$this->get_trans_month('IDR',37,2,$ps->year))),
                array("label" => "Mar", "y" => intval($this->get_trans_month('IDR',16,3,$ps->year)+$this->get_trans_month('IDR',21,3,$ps->year)+$this->get_trans_month('IDR',37,3,$ps->year))),
                array("label" => "Apr", "y" => intval($this->get_trans_month('IDR',16,4,$ps->year)+$this->get_trans_month('IDR',21,4,$ps->year)+$this->get_trans_month('IDR',37,4,$ps->year))),
                array("label" => "May", "y" => intval($this->get_trans_month('IDR',16,5,$ps->year)+$this->get_trans_month('IDR',21,5,$ps->year)+$this->get_trans_month('IDR',37,5,$ps->year))),
                array("label" => "Jun", "y" => intval($this->get_trans_month('IDR',16,6,$ps->year)+$this->get_trans_month('IDR',21,6,$ps->year)+$this->get_trans_month('IDR',37,6,$ps->year))),
                array("label" => "Jul", "y" => intval($this->get_trans_month('IDR',16,7,$ps->year)+$this->get_trans_month('IDR',21,7,$ps->year)+$this->get_trans_month('IDR',37,7,$ps->year))),
                array("label" => "Aug", "y" => intval($this->get_trans_month('IDR',16,8,$ps->year)+$this->get_trans_month('IDR',21,8,$ps->year)+$this->get_trans_month('IDR',37,8,$ps->year))),
                array("label" => "Sep", "y" => intval($this->get_trans_month('IDR',16,9,$ps->year)+$this->get_trans_month('IDR',21,9,$ps->year)+$this->get_trans_month('IDR',37,9,$ps->year))),
                array("label" => "Oct", "y" => intval($this->get_trans_month('IDR',16,10,$ps->year)+$this->get_trans_month('IDR',21,10,$ps->year)+$this->get_trans_month('IDR',37,10,$ps->year))),
                array("label" => "Nov", "y" => intval($this->get_trans_month('IDR',16,11,$ps->year)+$this->get_trans_month('IDR',21,11,$ps->year)+$this->get_trans_month('IDR',37,11,$ps->year))),
                array("label" => "Dec", "y" => intval($this->get_trans_month('IDR',16,12,$ps->year)+$this->get_trans_month('IDR',21,12,$ps->year)+$this->get_trans_month('IDR',37,12,$ps->year)))
            );
        }
                    
//       echo json_encode($data, JSON_NUMERIC_CHECK);
        return $data;
    }
    
    private function get_trans_month($cur,$cla,$month,$year)
    {
        $model = new Account_model();
        $trans = $model->get_balance_by_classification($cur,$cla,$month,$year,$month,$year);
        return intval($trans);
    }
        
    function get_outcome12()
    {    
       $ps = new Period();
       $ps = $ps->get(); 
         
        $data = array(
            array("label" => "Jan", "y" => $this->calculate_outcome('IDR',1,$ps->year)),
            array("label" => "Feb", "y" => $this->calculate_outcome('IDR',2,$ps->year)),
            array("label" => "Mar", "y" => $this->calculate_outcome('IDR',3,$ps->year)),
            array("label" => "Apr", "y" => $this->calculate_outcome('IDR',4,$ps->year)),
            array("label" => "May", "y" => $this->calculate_outcome('IDR',5,$ps->year)),
            array("label" => "Jun", "y" => $this->calculate_outcome('IDR',6,$ps->year)),
            array("label" => "Jul", "y" => $this->calculate_outcome('IDR',7,$ps->year)),
            array("label" => "Aug", "y" => $this->calculate_outcome('IDR',8,$ps->year)),
            array("label" => "Sep", "y" => $this->calculate_outcome('IDR',9,$ps->year)),
            array("label" => "Oct", "y" => $this->calculate_outcome('IDR',10,$ps->year)),
            array("label" => "Nov", "y" => $this->calculate_outcome('IDR',11,$ps->year)),
            array("label" => "Dec", "y" => $this->calculate_outcome('IDR',12,$ps->year)),
        );
                    
//       echo json_encode($data, JSON_NUMERIC_CHECK);
        return $data;
    }
    
    private function calculate_outcome($cur='IDR',$month,$year)
    {
       $model = new Account_model();
       
       $hpp = intval($model->get_balance_by_classification($cur,15,$month,$year,$month,$year));
       $operational = intval($model->get_balance_by_classification($cur,19,$month,$year,$month,$year));
       
       $nonoperational = intval($model->get_balance_by_classification($cur,24,$month,$year,$month,$year));
       $othercost = intval($model->get_balance_by_classification($cur,17,$month,$year,$month,$year));
       $outcost = intval($model->get_balance_by_classification($cur,25,$month,$year,$month,$year));
              
       return intval($hpp+$operational+$nonoperational+$othercost+$outcost);
    }
    
    private function user_agent()
    {
        $agent=null;
        if ($this->agent->is_browser()){  $agent = $this->agent->browser().' '.$this->agent->version();}
        elseif ($this->agent->is_robot()){ $agent = $this->agent->robot(); }
        elseif ($this->agent->is_mobile()){ $agent = $this->agent->mobile(); }
        else{ $agent = 'Unidentified User Agent'; }
        return $agent." - ".$this->agent->platform();
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ } 
    
}

?>