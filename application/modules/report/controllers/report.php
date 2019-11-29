<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Ledger_model', 'lm', TRUE);
        $this->load->model('Account_model', 'am', TRUE);

        $this->properti = $this->property->get();
        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));

        $this->currency   = $this->load->library('currency_lib');
        $this->user       = $this->load->library('admin_lib');
        $this->account    = $this->load->library('account_lib');
        $this->balance = new Balance_account_lib();
        $this->period = new Period_lib();
        $this->period = $this->period->get();
        $this->cla = new Classification_lib();

        $this->load->library('fusioncharts');
        $this->swfCharts  = base_url().'public/flash/Column3D.swf';
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
    }

    private $properti, $modul, $title, $currency, $account,$api, $acl, $balance,$period, $cla;
    private $user;
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    private $operating=0;
    private $investing=0;
    private $financing=0;

    function index()
    {
//        $this->start();
//        redirect('main');
    }
    
    // api version
    function profitloss()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){ 
                
        $this->form_validation->set_rules('csmonth', 'Start Month', 'required|callback_valid_report');
        $this->form_validation->set_rules('cemonth', 'End Month', 'required|callback_valid_report');
        $this->form_validation->set_rules('tsyear', 'Start Year', 'required|numeric|callback_valid_report');
        $this->form_validation->set_rules('teyear', 'End Year', 'required|numeric|callback_valid_report');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('ctype', 'Report Type', 'required');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $month = $this->input->post('csmonth');
            $emonth = $this->input->post('cemonth');
            $year = $this->input->post('tsyear');
            $eyear = $this->input->post('teyear');
            $cur = $this->input->post('ccurrency');
            $data['company'] = $this->properti['name'];
            $rtype = $this->input->post('ctype');

            $data['months']  = get_month($month);
            $data['emonths'] = get_month($emonth);
            $data['years'] = $year;
            $data['eyears'] = $eyear;
            $data['currency'] = $cur;
            
            if ($rtype == 0){ $fname = 'labarugi_standard'; 
                $result = $this->get_pl($cur,$month,$year,$emonth,$eyear);
                $this->output['result'] = $result[0];
                $this->output['summary'] = $result[1];
            }
            elseif ($rtype == 1){ $fname = 'labarugi_2kolom';
               $result1 = $this->get_pl($cur,$month,$year,$month,$year);
               $result2 = $this->get_pl($cur,$emonth,$eyear,$emonth,$eyear);
               $this->output['result1'] = $result1[0];
               $this->output['summary1'] = $result1[1]; 
               $this->output['result2'] = $result2[0];
               $this->output['summary2'] = $result2[1]; 
            }
            elseif ($rtype == 2){ $fname = 'labarugi_yeartodate'; 
               $result1 = $this->get_pl($cur,$month,$year,$month,$year);
               $result2 = $this->get_pl_year_to_end($cur,$emonth,$eyear,$emonth,$eyear);
               $this->output['result1'] = $result1[0];
               $this->output['summary1'] = $result1[1]; 
               $this->output['result2'] = $result2[0];
               $this->output['summary2'] = $result2[1]; 
            }
            elseif ($rtype == 3){ $fname = 'labarugi_12kolom'; 
               $mo = $this->get_bulan($month,$year);
               $result = null;
               for ($x = 1; $x <=12; $x++){
                 $result[$x] = $this->get_pl($cur,$mo[$x][0],$mo[$x][1],$mo[$x][0],$mo[$x][1]);
                 $this->output['result'.$x] = $result[$x][0];
                 $this->output['summary'.$x] = $result[$x][1];
               }
            }
            //  $this->load->view($fname, $data);
        }else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }
    
    private function get_bulan($month,$year)
    {
        $a = $month;
        $value = null;
        for ($x = 1; $x <= 12; $x++)
        {
          if ($a > 12){$a=1; $year=$year+1;}		
          $value[$x][0] = $a;
          $value[$x][1] = $year;
          $a++;
        }
        return $value;
    }
    
    private function get_pl($cur='IDR',$month,$year,$emonth,$eyear){
        
        // operating calculation 
        $data = null;
        $data['pendapatan_usaha'] = $this->split_acc($this->am->get_account(16)->result(),$cur,$month,$year,$emonth,$eyear); // pendapatan usaha
        $data['pendapatan_luar_usaha'] = $this->split_acc($this->am->get_account(21)->result(),$cur,$month,$year,$emonth,$eyear); // Pendapatan Luar Usaha
        $data['pendapatan_usaha_lain'] = $this->split_acc($this->am->get_account(37)->result(),$cur,$month,$year,$emonth,$eyear); // Pendapatan Usaha Lainnya

        $data['biaya_usaha'] = $this->split_acc($this->am->get_account(15)->result(),$cur,$month,$year,$emonth,$eyear); // biaya usaha
        $data['biaya_operasional'] = $this->split_acc($this->am->get_account(19)->result(),$cur,$month,$year,$emonth,$eyear); // Biaya adm / umum
        $data['biaya_non_operaional'] = $this->split_acc($this->am->get_account(24)->result(),$cur,$month,$year,$emonth,$eyear); // Biaya Non Operasional 
        $data['biaya_usaha_lain'] = $this->split_acc($this->am->get_account(17)->result(),$cur,$month,$year,$emonth,$eyear); // Biaya Usaha Lain
        $data['pengeluaran_luar_usaha'] = $this->split_acc($this->am->get_account(25)->result(),$cur,$month,$year,$emonth,$eyear);  // Pengeluaran Luar Usaha 

        // sum
        $sum = null;
        $sum['pendapatan_usaha'] = $this->am->get_balance_by_classification($cur,16,$month,$year,$emonth,$eyear);
        $sum['pendapatan_luar_usaha'] = $this->am->get_balance_by_classification($cur,21,$month,$year,$emonth,$eyear);
        $sum['pendapatan_usaha_lain'] = $this->am->get_balance_by_classification($cur,37,$month,$year,$emonth,$eyear);

        $sum['biaya_usaha'] = $this->am->get_balance_by_classification($cur,15,$month,$year,$emonth,$eyear); // biaya usaha
        $sum['biaya_operasional'] = $this->am->get_balance_by_classification($cur,19,$month,$year,$emonth,$eyear); // Biaya adm / umum
        $sum['biaya_non_operaional'] = $this->am->get_balance_by_classification($cur,24,$month,$year,$emonth,$eyear); // Biaya Non Operasional 
        $sum['biaya_usaha_lain'] = $this->am->get_balance_by_classification($cur,17,$month,$year,$emonth,$eyear); // Biaya Usaha Lain
        $sum['pengeluaran_luar_usaha'] = $this->am->get_balance_by_classification($cur,25,$month,$year,$emonth,$eyear); // Pengeluaran Luar Usaha 

        $sum['total_pendapatan'] = $sum['pendapatan_usaha']+$sum['pendapatan_usaha_lain'];
        $sum['total_biaya_pendapatan'] = $sum['biaya_usaha']+$sum['biaya_usaha_lain'];
        $sum['gross_margin'] = $sum['total_pendapatan']-$sum['total_biaya_pendapatan'];

        $sum['total_pengeluaran_operasional'] = $sum['biaya_operasional'];
        $sum['total_pengeluaran_non_operasional'] = $sum['biaya_non_operaional'];
        $sum['operating_profit'] = $sum['gross_margin']-$sum['total_pengeluaran_operasional']-$sum['total_pengeluaran_non_operasional'];
        $sum['total_pendapatan_lain'] = $sum['pendapatan_luar_usaha'];
        $sum['total_pengeluaran_lain'] = $sum['pengeluaran_luar_usaha'];
        $sum['net_profit'] = $sum['operating_profit']+$sum['total_pendapatan_lain']-$sum['total_pengeluaran_lain'];
        
        $result[0] = $data; $result[1] = $sum;
        return $result;
    }
    
    private function split_acc($val,$cur,$month,$year,$emonth,$eyear,$type=null){
        
        $output = null;
        if (!$type){
            foreach ($val as $res) {
                $bl = $this->am->get_period_balance($cur,$res->id,$month,$year,$emonth,$eyear)->row();
                $output[] = array("code" => $res->code, "name" => $res->name, "amount" => floatval($bl->vamount));
            }
        }else{
            foreach ($val as $res) {
                $bl = $this->am->get_annual_period_balance($cur,$res->id,$eyear)->row();
                $output[] = array("code" => $res->code, "name" => $res->name, "amount" => floatval($bl->vamount));
            } 
        }
        return $output;
    }
    
    private function get_pl_year_to_end($cur='IDR',$month,$year,$emonth,$eyear){
        
        // operating calculation 
        $data = null;
        $data['pendapatan_usaha'] = $this->split_acc($this->am->get_account(16)->result(),$cur,$month,$year,$emonth,$eyear,1); // pendapatan usaha
        $data['pendapatan_luar_usaha'] = $this->split_acc($this->am->get_account(21)->result(),$cur,$month,$year,$emonth,$eyear,1); // Pendapatan Luar Usaha
        $data['pendapatan_usaha_lain'] = $this->split_acc($this->am->get_account(37)->result(),$cur,$month,$year,$emonth,$eyear,1); // Pendapatan Usaha Lainnya

        $data['biaya_usaha'] = $this->split_acc($this->am->get_account(15)->result(),$cur,$month,$year,$emonth,$eyear,1); // biaya usaha
        $data['biaya_operasional'] = $this->split_acc($this->am->get_account(19)->result(),$cur,$month,$year,$emonth,$eyear,1); // Biaya adm / umum
        $data['biaya_non_operaional'] = $this->split_acc($this->am->get_account(24)->result(),$cur,$month,$year,$emonth,$eyear,1); // Biaya Non Operasional 
        $data['biaya_usaha_lain'] = $this->split_acc($this->am->get_account(17)->result(),$cur,$month,$year,$emonth,$eyear,1); // Biaya Usaha Lain
        $data['pengeluaran_luar_usaha'] = $this->split_acc($this->am->get_account(25)->result(),$cur,$month,$year,$emonth,$eyear,1);  // Pengeluaran Luar Usaha 

        // sum
        $sum = null;
        $sum['pendapatan_usaha'] = $this->am->get_balance_anual_by_classification($cur,16,$year,$eyear);
        $sum['pendapatan_luar_usaha'] = $this->am->get_balance_anual_by_classification($cur,21,$year,$eyear);
        $sum['pendapatan_usaha_lain'] = $this->am->get_balance_anual_by_classification($cur,37,$year,$eyear);

        $sum['biaya_usaha'] = $this->am->get_balance_anual_by_classification($cur,15,$year,$eyear); // biaya usaha
        $sum['biaya_operasional'] = $this->am->get_balance_anual_by_classification($cur,19,$year,$eyear); // Biaya adm / umum
        $sum['biaya_non_operaional'] = $this->am->get_balance_anual_by_classification($cur,24,$year,$eyear); // Biaya Non Operasional 
        $sum['biaya_usaha_lain'] = $this->am->get_balance_anual_by_classification($cur,17,$year,$eyear); // Biaya Usaha Lain
        $sum['pengeluaran_luar_usaha'] = $this->am->get_balance_anual_by_classification($cur,25,$year,$eyear); // Pengeluaran Luar Usaha 

        $sum['total_pendapatan'] = $sum['pendapatan_usaha']+$sum['pendapatan_usaha_lain'];
        $sum['total_biaya_pendapatan'] = $sum['biaya_usaha']+$sum['biaya_usaha_lain'];
        $sum['gross_margin'] = $sum['total_pendapatan']-$sum['total_biaya_pendapatan'];

        $sum['total_pengeluaran_operasional'] = $sum['biaya_operasional'];
        $sum['total_pengeluaran_non_operasional'] = $sum['biaya_non_operaional'];
        $sum['operating_profit'] = $sum['gross_margin']-$sum['total_pengeluaran_operasional']-$sum['total_pengeluaran_non_operasional'];
        $sum['total_pendapatan_lain'] = $sum['pendapatan_luar_usaha'];
        $sum['total_pengeluaran_lain'] = $sum['pengeluaran_luar_usaha'];
        $sum['net_profit'] = $sum['operating_profit']+$sum['total_pendapatan_lain']-$sum['total_pengeluaran_lain'];
        
        $result[0] = $data; $result[1] = $sum;
        return $result;
    }
    
    // =========================  batas profit loss report  ==================================
    
    public function valid_report($val=null)
    {
        $smonth = $this->input->post('csmonth');
        $emonth = $this->input->post('cemonth');
        $syear = $this->input->post('tsyear');
        $eyear = $this->input->post('teyear');
        
        if ($syear > $eyear)
        {
           $this->form_validation->set_message('valid_report', "Invalid Year..!!");
           return FALSE;
        }
        else
        {
            if ($syear == $eyear) { if ($smonth > $emonth){ $this->form_validation->set_message('valid_report', "Invalid Month..!!"); return FALSE; }else { return TRUE; }}
            else { return TRUE; }
        }
    }
    
    // balance sheet
    function balancesheet()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){ 
                
        $this->form_validation->set_rules('csmonth', 'Start Month', 'required|callback_valid_report');
        $this->form_validation->set_rules('cemonth', 'End Month', 'required|callback_valid_report');
        $this->form_validation->set_rules('tsyear', 'Start Year', 'required|numeric|callback_valid_report');
        $this->form_validation->set_rules('teyear', 'End Year', 'required|numeric|callback_valid_report');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        $this->form_validation->set_rules('ctype', 'Report Type', 'required');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $month = $this->input->post('csmonth');
            $emonth = $this->input->post('cemonth');
            $year = $this->input->post('tsyear');
            $eyear = $this->input->post('teyear');
            $cur = $this->input->post('ccurrency');
            $data['company'] = $this->properti['name'];
            $rtype = $this->input->post('ctype');

            $data['months']  = get_month($month);
            $data['emonths'] = get_month($emonth);
            $data['years'] = $year;
            $data['eyears'] = $eyear;
            $data['currency'] = $cur;
            
            if ($rtype == 0){ $fname = 'neraca_standard'; 
                $result = $this->get_bs($cur,$month,$year,$emonth,$eyear);
                $this->output['result'] = $result[0];
                $this->output['summary'] = $result[1];
            }
            elseif ($rtype == 1){ $fname = 'neraca_2kolom';
               $result1 = $this->get_bs($cur,$month,$year,$month,$year);
               $result2 = $this->get_bs($cur,$emonth,$eyear,$emonth,$eyear);
               $this->output['result1'] = $result1[0];
               $this->output['summary1'] = $result1[1]; 
               $this->output['result2'] = $result2[0];
               $this->output['summary2'] = $result2[1]; 
            }
            elseif ($rtype == 2){ $fname = 'neraca_12kolom'; 
               $mo = $this->get_bulan($month,$year);
               $result = null;
               
               for ($x = 1; $x <=12; $x++){
                 $result[$x] = $this->get_bs($cur,$mo[$x][0],$mo[$x][1],$mo[$x][0],$mo[$x][1]);
                 $this->output['result'.$x] = $result[$x][0];
                 $this->output['summary'.$x] = $result[$x][1];
               }
            }
            //  $this->load->view($fname, $data);
        }else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }
    
    private function get_bs($cur='IDR',$month,$year,$emonth,$eyear){
        
        // harta 
        $data = null;
        $data['kas'] = $this->split_acc_bs($this->am->get_all_account(7)->result(),$cur,$month,$year,$emonth,$eyear); 
        $data['bank'] = $this->split_acc_bs($this->am->get_all_account(8)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['piutangusaha'] = $this->split_acc_bs($this->am->get_all_account(20)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['piutangnonusaha'] = $this->split_acc_bs($this->am->get_all_account(27)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['persediaan'] = $this->split_acc_bs($this->am->get_all_account(14)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['biayadimuka'] = $this->split_acc_bs($this->am->get_all_account(13)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['investasi'] = $this->split_acc_bs($this->am->get_all_account(29)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['hartawujud'] = $this->split_acc_bs($this->am->get_all_account(26)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['hartatakwujud'] = $this->split_acc_bs($this->am->get_all_account(30)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['hartalain'] = $this->split_acc_bs($this->am->get_all_account(31)->result(),$cur,$month,$year,$emonth,$eyear);
        
        // kewajiban
        $data['hutangusaha'] = $this->split_acc_bs($this->am->get_all_account(10)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['pendapatandimuka'] = $this->split_acc_bs($this->am->get_all_account(34)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['hutangpanjang'] = $this->split_acc_bs($this->am->get_all_account(35)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['hutangnonusaha'] = $this->split_acc_bs($this->am->get_all_account(32)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['hutanglain'] = $this->split_acc_bs($this->am->get_all_account(36)->result(),$cur,$month,$year,$emonth,$eyear);

        // modal
        $data['modal'] = $this->split_acc_bs($this->am->get_all_account(22)->result(),$cur,$month,$year,$emonth,$eyear);
        $data['laba'] = $this->split_acc_bs($this->am->get_all_account(18)->result(),$cur,$month,$year,$emonth,$eyear,null,1);

        // sum
        $sum = null;
        
        // harta
        $sum['kas'] = $this->get_balance_by_cla($cur,7,$month,$year,$emonth,$eyear);
        $sum['bank'] = $this->get_balance_by_cla($cur,8,$month,$year,$emonth,$eyear);
        $sum['piutangusaha'] = $this->get_balance_by_cla($cur,20,$month,$year,$emonth,$eyear);
        $sum['piutangnonusaha'] = $this->get_balance_by_cla($cur,27,$month,$year,$emonth,$eyear);

        $sum['persediaan'] = $this->get_balance_by_cla($cur,14,$month,$year,$emonth,$eyear);
        $sum['biayadimuka'] = $this->get_balance_by_cla($cur,13,$month,$year,$emonth,$eyear);
        $sum['investasi'] = $this->get_balance_by_cla($cur,29,$month,$year,$emonth,$eyear); 
        $sum['hartawujud'] = $this->get_balance_by_cla($cur,26,$month,$year,$emonth,$eyear); 
        $sum['hartatakwujud'] = $this->get_balance_by_cla($cur,30,$month,$year,$emonth,$eyear);
        $sum['hartalain'] = $this->get_balance_by_cla($cur,31,$month,$year,$emonth,$eyear); 
        
        
        // kewajiban
        $sum['hutangusaha'] = $this->get_balance_by_cla($cur,10,$month,$year,$emonth,$eyear); 
        $sum['pendapatandimuka'] = $this->get_balance_by_cla($cur,34,$month,$year,$emonth,$eyear);
        $sum['hutangpanjang'] = $this->get_balance_by_cla($cur,35,$month,$year,$emonth,$eyear); 
        $sum['hutangnonusaha'] = $this->get_balance_by_cla($cur,32,$month,$year,$emonth,$eyear);
        $sum['hutanglain'] = $this->get_balance_by_cla($cur,36,$month,$year,$emonth,$eyear); 
        
        // modal
        $sum['modal'] = $this->get_balance_by_cla($cur,22,$month,$year,$emonth,$eyear); 
        $sum['laba'] = $this->get_balance_by_cla($cur,18,$month,$year,$emonth,$eyear,1);
        
        
        $sum['total_asset'] = $sum['kas']+$sum['bank']+$sum['piutangusaha']+$sum['piutangnonusaha']+$sum['persediaan']+
                              $sum['biayadimuka']+$sum['investasi']+$sum['hartawujud']+$sum['hartatakwujud']+$sum['hartalain'];
        
        $sum['total_liabilities'] = $sum['hutangusaha']+$sum['pendapatandimuka']+$sum['hutangpanjang']+$sum['hutangnonusaha']+$sum['hutanglain'];
        $sum['total_libilities_equity'] = $sum['total_liabilities']+$sum['modal']+$sum['laba'];
        
        $result[0] = $data; $result[1] = $sum;
        return $result;
    }
    
    private function get_balance_by_cla($cur='IDR',$cla,$month=0,$year=0,$emonth=0,$eyear=0,$laba=null)
    {
        $trans = 0;
        $bl = $this->balance->get_balance_by_cla($cur,$cla, $month, $year);
        $trans = $this->am->get_balance_by_classification($cur,$cla,$month,$year,$emonth,$eyear);
        if ($laba){ return floatval($bl->end); }else{ return floatval($bl->beginning+$trans); }
    }
    
    private function split_acc_bs($val,$cur,$month,$year,$emonth,$eyear,$type=null,$laba=null){
        
        $output = null;
        if (!$type){
            foreach ($val as $res) {
                $bl = $this->balance->get($res->id, $month, $year);
                $begin=0; $end=0;
                if ($bl){ $begin=$bl->beginning; $end=$bl->end; }
                $trans = $this->am->get_period_balance($cur,$res->id,$month,$year,$emonth,$eyear)->row();
                $balance = 0;
                if ($laba){ $balance = floatval($end);}else{ $balance = floatval($begin)+floatval($trans->vamount);}
                $output[] = array("code" => $res->code, "name" => $res->name, "amount" => $balance);
            }
        }
        return $output;
    }
        
    // balance sheet
    
    // trial balance
    
     private $totbegin_d = 0;
     private $totbegin_c = 0;
     private $tottrans_d = 0;
     private $tottrans_c = 0;
     private $totend_d = 0;
     private $totend_c = 0;
    
    function trialbalance()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){ 
                
        $this->form_validation->set_rules('csmonth', 'Start Month', 'required|callback_valid_report');
        $this->form_validation->set_rules('cemonth', 'End Month', 'required|callback_valid_report');
        $this->form_validation->set_rules('tsyear', 'Start Year', 'required|numeric|callback_valid_report');
        $this->form_validation->set_rules('teyear', 'End Year', 'required|numeric|callback_valid_report');
        $this->form_validation->set_rules('ccurrency', 'Currency', 'required');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $month = $this->input->post('csmonth');
            $emonth = $this->input->post('cemonth');
            $year = $this->input->post('tsyear');
            $eyear = $this->input->post('teyear');
            $cur = $this->input->post('currency');
            $data['company'] = $this->properti['name'];

            $data['months']  = get_month($month);
            $data['emonths'] = get_month($emonth);
            $data['years'] = $year;
            $data['eyears'] = $eyear;
            $data['currency'] = $cur;
            
            $output = null;
            $account = $this->am->get_all_account()->result();
            foreach ($account as $res) {
                $begin_d = $this->get_begin_balance($cur, $res->id, $res->classification_id, $month, $year, 'debit');
                $begin_c = $this->get_begin_balance($cur, $res->id, $res->classification_id, $month, $year, 'credit');
                $trans_d = $this->get_trans($cur,$res->id,$month,$year,$emonth,$eyear,$res->classification_id,'debit');
                $trans_c = $this->get_trans($cur,$res->id,$month,$year,$emonth,$eyear,$res->classification_id,'credit');
                $end_d = $this->get_end_balance($cur,$res->id,$month,$year,$emonth,$eyear,$res->classification_id,'debit');
                $end_c = $this->get_end_balance($cur,$res->id,$month,$year,$emonth,$eyear,$res->classification_id,'credit');
                
                $output[] = array("code" => $res->code, "name" => $res->name, "begin_debit" => floatval($begin_d), "begin_credit" => floatval($begin_c), "trans_debit" => floatval($trans_d), "trans_credit" => floatval($trans_c), "end_debit" => floatval($end_d), "end_credit" => floatval($end_c));
                $this->totbegin_d = floatval($this->totbegin_d+$begin_d);
                $this->totbegin_c = floatval($this->totbegin_c+$begin_c);
                $this->tottrans_d = floatval($this->tottrans_d+$trans_d);
                $this->tottrans_c = floatval($this->tottrans_c+$trans_c);
                $this->totend_d   = floatval($this->totend_d+$end_d);
                $this->totend_c   = floatval($this->totend_c+$end_c);
            }
            
            $this->output['result'] = $output;
            $this->output['summary'] = array("begin_debit" => $this->totbegin_d, "begin_credit" => $this->totbegin_c, "trans_debit" => $this->tottrans_d, "trans_credit" => $this->tottrans_c, "end_debit" => $this->totend_d, "end_credit" => $this->totend_c);
            
        }else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }
    
    private function get_trans($cur='IDR',$acc,$m,$y,$em,$ey,$clid,$type)
    {
        $res = $this->am->get_period_balance($cur,$acc,$m,$y,$em,$ey)->row();
        $res = $res->vamount;

        $debit = 0;
        $credit = 0;

        if ($this->cla->get_type($clid) == 'harta'){ if ($res > 0){ $debit = $res; } else{ $credit = abs($res); }}
        if ($this->cla->get_type($clid) == 'biaya'){ if ($res > 0){ $debit = $res;} else{ $credit = abs($res); }}
        if ($this->cla->get_type($clid) == 'kewajiban'){ if ($res > 0){ $credit = $res;} else{ $debit = abs($res); }}
        if ($this->cla->get_type($clid) == 'modal'){ if ($res > 0){ $credit = $res;} else{ $debit = abs($res); }}
        if ($this->cla->get_type($clid) == 'pendapatan'){ if ($res > 0){ $credit = $res;} else{ $debit = abs($res); }}

        if ($type == 'debit'){ return $debit; } elseif ($type == 'credit'){ return $credit; }
    }
    
    private function get_end_balance($cur='IDR',$acc,$m,$y,$em,$ey,$clid,$type)
    {
        $bl = $this->balance->get($acc,$m,$y,$cur);

        $res = $this->am->get_period_balance($cur,$acc,$m,$y,$em,$ey)->row();
        $res = floatval($bl->beginning)+floatval($res->vamount);
        $debit = 0; $credit = 0;

        if ($this->cla->get_type($clid) == 'harta'){ if ($res > 0){ $debit = $res; } else{ $credit = abs($res); }}
        if ($this->cla->get_type($clid) == 'biaya'){ if ($res > 0){ $debit = $res;} else{ $credit = abs($res); }}
        if ($this->cla->get_type($clid) == 'kewajiban'){ if ($res > 0){ $credit = $res;} else{ $debit = abs($res); }}
        if ($this->cla->get_type($clid) == 'modal'){ if ($res > 0){ $credit = $res;} else{ $debit = abs($res); }}
        if ($this->cla->get_type($clid) == 'pendapatan'){ if ($res > 0){ $credit = $res;} else{ $debit = abs($res); }}

        if ($type == 'debit'){ return $debit; } elseif ($type == 'credit'){ return $credit; }
    }
        
    private function get_begin_balance($cur,$acc,$clid,$m,$y,$type)
    {
        $bl = $this->balance->get($acc,$m,$y,$cur);
        $debit = 0; $credit = 0;

        if ($this->cek_acc($clid) == FALSE){ $debit=0; $credit=0;}
        else
        {            
            if ($this->cek_acc($clid) == 'harta'){if ($bl->beginning > 0){ $debit = $bl->beginning; }else{ $credit = abs($bl->beginning); }}
            if ($this->cek_acc($clid) == 'kewajiban'){if ($bl->beginning > 0){ $credit = $bl->beginning; }else{ $debit = abs($bl->beginning); }}
            if ($this->cek_acc($clid) == 'modal'){if ($bl->beginning > 0){ $credit = $bl->beginning; }else{ $debit = abs($bl->beginning); }}
        }

        if ($type == 'debit'){ return $debit; } elseif ($type == 'credit'){ return $credit; }
    }
    
    private function cek_acc($clid)
    {
        $cl = new Classification();
        $cl->where('id', $clid)->get();
        $type = $cl->type;
        if ($type == 'harta'){ return $type; } 
        elseif ($type == 'kewajiban'){ return $type; }
        elseif ($type == 'modal'){ return $type; }
        else { return FALSE; }
    }
    
    // trial balance
    
    // ============= cash flow  ==========================
    
    function cashflow()
    {
       if ($this->acl->otentikasi2($this->title) == TRUE){ 
                
        $this->form_validation->set_rules('start', 'Start Year', 'required');
        $this->form_validation->set_rules('end', 'End Year', 'required');
        $this->form_validation->set_rules('currency', 'Currency', 'required');
        
        if ($this->form_validation->run($this) == TRUE)
        {
            $start = $this->input->post('start');
            $end = $this->input->post('end');
            $cur = $this->input->post('currency');
            $data['company'] = $this->properti['name'];

            $data['start']  = tglin($start);
            $data['end'] = tglin($end);
            $data['currency'] = $cur;
            
             // operating activity
            $data = null;
            $data['piutangusaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,20,$start,$end)->result(),$cur,$start,$end);
            $data['piutangnonusaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,27,$start,$end)->result(),$cur,$start,$end);
            $data['persediaan'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,14,$start,$end)->result(),$cur,$start,$end);
            $data['hutangusaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,10,$start,$end)->result(),$cur,$start,$end);
            $data['pendapatanmuka'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,34,$start,$end)->result(),$cur,$start,$end);
            $data['pendapatanusaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,16,$start,$end)->result(),$cur,$start,$end);
            $data['pendapatanusahalain'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,37,$start,$end)->result(),$cur,$start,$end);
            $data['biayausaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,15,$start,$end)->result(),$cur,$start,$end);
            $data['biayausahalain'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,17,$start,$end)->result(),$cur,$start,$end);
            $data['biayaadm'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,19,$start,$end)->result(),$cur,$start,$end);
            
            // investment activity
            $data['biayadimuka'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,13,$start,$end)->result(),$cur,$start,$end,1);
            $data['investasipanjang'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,29,$start,$end)->result(),$cur,$start,$end,1);
            $data['hartaberwujud'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,26,$start,$end)->result(),$cur,$start,$end,1);
            $data['hartatakberwujud'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,30,$start,$end)->result(),$cur,$start,$end,1);
            $data['hartalain'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,31,$start,$end)->result(),$cur,$start,$end,1);
            $data['biayanonoperasional'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,24,$start,$end)->result(),$cur,$start,$end,1);
            
            // financing activity
            $data['hutangjangkapanjang'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,35,$start,$end)->result(),$cur,$start,$end,2);
            $data['hutangnonusaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,32,$start,$end)->result(),$cur,$start,$end,2);
            $data['hutanglain'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,36,$start,$end)->result(),$cur,$start,$end,2);
            $data['modal'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,22,$start,$end)->result(),$cur,$start,$end,2);
            $data['laba'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,18,$start,$end)->result(),$cur,$start,$end,2);
            $data['pendapatanluarusaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,21,$start,$end)->result(),$cur,$start,$end,2);
            $data['pengeluaranluarusaha'] = $this->split_acc_cf($this->am->get_cash_flow_acc($cur,25,$start,$end)->result(),$cur,$start,$end,2);
            
            $this->output['result'] = $data;
            $this->output['summary']['total_operating'] = $this->operating;
            $this->output['summary']['total_investing'] = $this->investing;
            $this->output['summary']['total_financing'] = $this->financing;
            $this->output['summary']['total_cashflow'] = floatval($this->operating+$this->investing+$this->financing);
            
        }else{ $this->error = validation_errors(); $this->status = 401; }
        }else{ $this->reject_token(); }
        $this->api->response(array('error' => $this->error, 'content' => $this->output), $this->status); 
    }
    
    private function split_acc_cf($val,$cur,$start,$end,$type=0){
        
        $output = null;
        foreach ($val as $res) {
            $bl = $this->get_cf_trans($cur,$res->id,$start,$end);
            if ($type == 0){ $this->operating = $this->operating+$bl;}
            elseif ($type == 1){ $this->investing = $this->investing+$bl;}
            elseif ($type == 2){ $this->financing = $this->financing+$bl;}
            $output[] = array("code" => $res->code, "name" => $res->name, "amount" => floatval($bl));
        }
        return $output;
    }
    
    private function get_cf_trans($cur='IDR',$acc,$start,$end)
    {
        $res = $this->am->get_cash_flow($cur,$acc,$start,$end);
        $type = $this->account->get_acc_type($acc);
        $result = 0;
        if ($type == 'harta'){ if ($res > 0){ $result = 0 - $res; }else { $result = abs($res); } }
        elseif ($type == 'biaya'){ if ($res > 0){ $result = 0 - $res; }else { $result = abs($res); } }
        elseif ($type == 'pendapatan'){ if ($res > 0){ $result = $res; }else { $result = 0-$res; } }
        elseif ($type == 'kewajiban'){ if ($res > 0){ $result = $res; }else { $result = 0-$res; } }
        elseif ($type == 'modal'){ if ($res > 0){ $result = $res; }else { $result = 0-$res; } }
        return $result;
    }
 
    // ============ end cash flow =====================
        
    private function get_balance($acc=null)
    {
        $ps = new Period();
        $gl = new Gl();
        $bl = new Balance();
        $ps->get();
        
        $gl->where('approved', 1);
        $gl->where('MONTH(dates)', $ps->month);
        $gl->where('YEAR(dates)', $ps->year)->get();
        
        $bl->where('month', $ps->month);
        $bl->where('year', $ps->year);
        $bl->where('account_id', $acc)->get();
                
        $this->load->model('Account_model','am',TRUE);
        $val = $this->am->get_balance($acc,$ps->month,$ps->year)->row_array();
        
        $res[0] = $bl->beginning; //begin
        $res[1] = $bl->beginning + $val['vamount']; //end
        $res[2] = $val['vamount']; // mutation
        $res[3] = $val['debit']; // debit
        $res[4] = $val['credit']; // credit
        
        return $res;
        
    }
    
    // ====================================== CLOSING ======================================
    function reset_process(){ } 


}

?>