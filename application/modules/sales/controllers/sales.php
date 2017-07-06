<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'definer.php';

class Sales extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Sales_model', '', TRUE);
        $this->load->model('Sales_item_model', 'sitem', TRUE);

        $this->properti = $this->property->get();
//        $this->acl->otentikasi();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->role = new Role_lib();
        $this->currency = new Currency_lib();
        $this->sales = new Product_lib();
        $this->customer = new Customer_lib();
        $this->payment = new Payment_lib();
        $this->city = new City_lib();
        $this->product = new Product_lib();
        $this->shipping = new Shipping_lib();
        $this->bank = new Bank_lib();
        $this->category = new Categoryproduct_lib();
    }

    private $properti, $modul, $title, $sales, $wt ,$shipping, $bank;
    private $role, $currency, $customer, $payment, $city, $product ,$category;
    
    function index()
    {
//         echo constant("RADIUS_API");
       $this->session->unset_userdata('start'); 
       $this->session->unset_userdata('end');
       $this->get_last(); 
    }
    
    // function untuk memeriksa input user dari form sebagai admin
    function json_process()
    {
        $datax = (array)json_decode(file_get_contents('php://input')); 

        $username = $datax['user'];
        $password = $datax['pass'];

            if ($username == 'admin' && $password == 'admin')
            {
                $this->mail_invoice(7);
                $response = array(
                  'Success' => true,
		  'User' => $datax['user'],
                  'Info' => 'Login Success Lah'); 
            }
            else
            {
                $response = array(
                'Success' => false,
                'Info' => 'Invalid Login..!!');
            }
            
        $this->output
        ->set_status_header(201)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($response))
        ->_display();
        exit;

    }
    
//     ============== ajax ===========================
    
    function ongkir($ori,$dest,$courier,$jenis=null)
    {
        if (!$jenis){ echo $this->city->get_ongkir_combo($ori, $dest, $courier); }
        else { echo idr_format($jenis); }  
    }
    
    function get_customer($id)
    {
        if ($id){
          $cust = $this->customer->get_details($id)->row();
          echo $cust->email.'|'.$cust->shipping_address;
        }else { echo "|"; }
    }
    
    function get_product($pid)
    {
        $res = $this->product->get_detail_based_id($pid);
        echo intval($res->price-$res->discount);
    }


//     ============== ajax ===========================
     
    public function getdatatable($search=null,$cust='null',$payment='null',$confirm='null')
    {
        if(!$search){ $result = $this->Sales_model->get_last($this->modul['limit'])->result(); }
        else {$result = $this->Sales_model->search($cust,$payment,$confirm)->result(); }
	
        $output = null;
        if ($result){
                
         foreach($result as $res)
	 {
           $total = intval($res->amount+$res->tax+$res->cost);  
           if ($res->paid_date){ $status = 'S'; }else{ $status = 'C'; } 
           if ($this->shipping->cek_shiping_based_sales($res->id) == true){ $ship = 'Shipped'; }else{ $ship = '-'; } // shipping status
           
	   $output[] = array ($res->id, 'SO-0'.$res->id, tglin($res->dates), $this->customer->get_name($res->cust_id), idr_format($total),
                              idr_format($res->shipping), $status, $ship, $res->confirmation
                             );
	 } 
         
        $this->output
         ->set_status_header(200)
         ->set_content_type('application/json', 'utf-8')
         ->set_output(json_encode($output))
         ->_display();
         exit;  
        }
    }

    function get_last()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords('Sales Order');
        $data['h2title'] = 'Sales Order';
        $data['main_view'] = 'sales_view';
	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all/hard');
        $data['form_action_report'] = site_url($this->title.'/report_process');
        $data['form_action_import'] = site_url($this->title.'/import');
        $data['form_action_confirmation'] = site_url($this->title.'/payment_confirmation');
        $data['link'] = array('link_back' => anchor('main/','Back', array('class' => 'btn btn-danger')));

        $data['customer'] = $this->customer->combo();
        $data['bank'] = $this->bank->combo();
        $data['array'] = array('','');
        $data['month'] = combo_month();
        $data['year'] = date('Y');
        $data['default']['month'] = date('n');
        
	// ---------------------------------------- //
 
        $config['first_tag_open'] = $config['last_tag_open']= $config['next_tag_open']= $config['prev_tag_open'] = $config['num_tag_open'] = '<li>';
        $config['first_tag_close'] = $config['last_tag_close']= $config['next_tag_close']= $config['prev_tag_close'] = $config['num_tag_close'] = '</li>';

        $config['cur_tag_open'] = "<li><span><b>";
        $config['cur_tag_close'] = "</b></span></li>";

        // library HTML table untuk membuat template table class zebra
        $tmpl = array('table_open' => '<table id="datatable-buttons" class="table table-striped table-bordered">');

        $this->table->set_template($tmpl);
        $this->table->set_empty("&nbsp;");

        //Set heading untuk table
        $this->table->set_heading('#','No', 'Code', 'Date', 'Customer', 'Balance', 'Ship-Cost', 'Status', 'Ship-Status', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url($this->title.'/getdatatable/');
        $data['graph'] = site_url()."/sales/chart/".$this->input->post('cmonth').'/'.$this->input->post('tyear');
            
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }
    
    function chart($month=null,$year=null)
    {   
        $data = $this->category->get();
        $datax = array();
        foreach ($data as $res) 
        {  
           $tot = $this->Sales_model->get_sales_qty_based_category($res->id,$month,$year); 
           $point = array("label" => $res->name , "y" => $tot);
           array_push($datax, $point);      
        }
        echo json_encode($datax, JSON_NUMERIC_CHECK);
    }
    
    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){ 
//       $val = $this->Sales_model->get_by_id($uid)->row();
//       if ($val->approved == 0){ $lng = array('approved' => 1); }else { $lng = array('approved' => 0); }
//       $this->Sales_model->update($uid,$lng);
//       echo 'true|Status Changed...!';
         echo "error|Please make confirmation transaction, to change this status...!";
       }else{ echo "error|Sorry, you do not have the right to change publish status..!"; }
    }
    
    function delete_all($type='soft')
    {
      if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
      
        $cek = $this->input->post('cek');
        $jumlah = count($cek);

        if($cek)
        {
          $jumlah = count($cek);
          $x = 0;
          for ($i=0; $i<$jumlah; $i++)
          {
             if ($type == 'soft') { $this->Sales_model->delete($cek[$i]); }
             else { $this->shipping->delete_by_sales($cek[$i]);
                    $this->Sales_model->force_delete($cek[$i]);  
             }
             $x=$x+1;
          }
          $res = intval($jumlah-$x);
          //$this->session->set_flashdata('message', "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!");
          $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
          echo 'true|'.$mess;
        }
        else
        { //$this->session->set_flashdata('message', "No $this->title Selected..!!"); 
          $mess = "No $this->title Selected..!!";
          echo 'false|'.$mess;
        }
      }else{ echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
      
    }

    function delete($uid)
    {
        if ($this->acl->otentikasi_admin($this->title,'ajax') == TRUE){
            $this->Sales_model->delete($uid);
            
            $this->session->set_flashdata('message', "1 $this->title successfully removed..!");

            echo "true|1 $this->title successfully removed..!";
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
        
    }
    
    function add($param=0)
    {

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = 'Create New '.$this->modul['title'];
        $data['main_view'] = 'sales_form';
        if ($param == 0){$data['form_action'] = site_url($this->title.'/add_process'); $data['counter'] = $this->Sales_model->counter(); }
        else { $data['form_action'] = site_url($this->title.'/update_process'); $data['counter'] = $param; }
	
        $data['link'] = array('link_back' => anchor($this->title,'Back', array('class' => 'btn btn-danger')));
        $data['form_action_trans'] = site_url($this->title.'/add_item/0'); 
        $data['form_action_shipping'] = site_url($this->title.'/shipping/0'); 

        $data['customer'] = $this->customer->combo();
        $data['payment'] = $this->payment->combo();
        $data['source'] = site_url($this->title.'/getdatatable');
        $data['graph'] = site_url()."/sales/chart/";
        $data['city'] = $this->city->combo_city_combine();
        $data['default']['dates'] = date("Y/m/d");
        $data['product'] = $this->product->combo();
        
        $data['items'] = $this->sitem->get_last_item(0)->result();

        $this->load->view('template', $data);
    }

    function add_process()
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'category_view';
	$data['form_action'] = site_url($this->title.'/add_process');
	$data['link'] = array('link_back' => anchor('category/','<span>back</span>', array('class' => 'back')));

	// Form validation
        $this->form_validation->set_rules('ccustomer', 'Customer', 'required');
        $this->form_validation->set_rules('tdates', 'Transaction Date', 'required');
        $this->form_validation->set_rules('tduedates', 'Transaction Due Date', 'required');
        $this->form_validation->set_rules('cpayment', 'Payment Type', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $sales = array('cust_id' => $this->input->post('ccustomer'), 'dates' => date("Y-m-d H:i:s"),
                           'due_date' => $this->input->post('tduedates'), 'payment_id' => $this->input->post('cpayment'), 
                           'created' => date('Y-m-d H:i:s'));

            $this->Sales_model->add($sales);
            echo "true|One $this->title data successfully saved!|".$this->Sales_model->counter(1);
            $this->session->set_flashdata('message', "One $this->title data successfully saved!");
//            redirect($this->title.'/update/'.$this->Sales_model->counter(1));
        }
        else{ $data['message'] = validation_errors(); echo "error|".validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }

    }
    
    function add_item($sid=0)
    { 
       if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){ 
       if ($sid == 0){ echo 'error|Sales ID not saved'; }
       else {
       
         // Form validation
        $this->form_validation->set_rules('cproduct', 'Product', 'required|callback_valid_product['.$sid.']');
        $this->form_validation->set_rules('tqty', 'Qty', 'required|numeric');
        $this->form_validation->set_rules('tprice', 'Price', 'required|numeric');
        $this->form_validation->set_rules('ctax', 'Tax Type', 'required');

            if ($this->form_validation->run($this) == TRUE && $this->valid_confirm($sid) == TRUE)
            {
                $amt_price = intval($this->input->post('tqty')*$this->input->post('tprice'));
                $tax = intval($this->input->post('ctax')*$amt_price);
                $sales = array('product_id' => $this->input->post('cproduct'), 'sales_id' => $sid,
                               'qty' => $this->input->post('tqty'), 'tax' => $tax, 'weight' => $this->product->get_weight($this->input->post('cproduct')),
                               'price' => $this->input->post('tprice'), 'amount' => intval($amt_price+$tax));

                $this->sitem->add($sales);
                $this->update_trans($sid);
                echo "true|Sales Transaction data successfully saved!|";
            }
            else{ echo "error|".validation_errors(); }  
        }
       }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    private function update_trans($sid)
    {
        $totals = $this->sitem->total($sid);
        $price = intval($totals['qty']*$totals['price']);
        
        // shipping total        
        $transaction = array('tax' => $totals['tax'], 'total' => $price, 'amount' => intval($totals['tax']+$price), 'shipping' => $this->shipping->total($sid));
	$this->Sales_model->update($sid, $transaction);
    }
    
    function delete_item($id,$sid)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->valid_confirm($sid) == TRUE){ 
        
        $this->sitem->delete($id); // memanggil model untuk mendelete data
        $this->update_trans($sid);
        $this->session->set_flashdata('message', "1 item successfully removed..!"); // set flash data message dengan session
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
        redirect($this->title.'/update/'.$sid);
    }
    
    private function split_array($val)
    { return implode(",",$val); }
   
    function shipping($sid=0)
    { 
       if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){ 
       if ($sid == 0){ echo 'error|Sales ID not saved'; }
       else {
       
        $sales = $this->Sales_model->get_by_id($sid)->row();
           
         // Form validation
        $this->form_validation->set_rules('ccity', 'City', 'required');
        $this->form_validation->set_rules('tshipaddkurir', 'Shipping Address', 'required');
        $this->form_validation->set_rules('ccourier', 'Courier Service', 'required');
        $this->form_validation->set_rules('cpackage', 'Package Type', '');
        $this->form_validation->set_rules('tweight', 'Weight', 'required|numeric');

            if ($this->form_validation->run($this) == TRUE && $this->valid_confirm($sid) == TRUE)
            {
                $city = explode('|', $this->input->post('ccity'));
                $package = explode('|', $this->input->post('cpackage'));
                $param = array('sales_id' => $sid, 'shipdate' => null,
                               'courier' => $this->input->post('ccourier'), 'dest' => $city[1], 'dest_id' => $city[0],
                               'dest_desc' => $this->input->post('tshipaddkurir'), 'package' => $package[0],
                               'weight' => $this->input->post('tweight'), 'rate' => $this->input->post('rate'),
                               'amount' => intval($this->input->post('rate')*$this->input->post('tweight')));
                
                $this->shipping->create($sid, $param);
                $this->update_trans($sid);
                echo "true|Shipping Transaction data successfully saved!|";
            }
            else{ echo "error|".validation_errors(); }  
        }
       }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    // Fungsi update untuk menset texfield dengan nilai dari database
    function update($param=0)
    {
        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = 'Update '.$this->modul['title'];
        $data['main_view'] = 'sales_form';
        $data['form_action'] = site_url($this->title.'/update_process/'.$param); 
        $data['form_action_trans'] = site_url($this->title.'/add_item/'.$param); 
        $data['form_action_shipping'] = site_url($this->title.'/shipping/'.$param); 
        $data['counter'] = $param; 
	
        $data['link'] = array('link_back' => anchor($this->title,'Back', array('class' => 'btn btn-danger')));

        $data['customer'] = $this->customer->combo();
        $data['payment'] = $this->payment->combo();
        $data['source'] = site_url($this->title.'/getdatatable');
        $data['graph'] = site_url()."/sales/chart/";
        $data['city'] = $this->city->combo_city_combine();
        $data['product'] = $this->product->combo();
        
        $sales = $this->Sales_model->get_by_id($param)->row();
        $customer = $this->customer->get_details($sales->cust_id)->row();
        $data['default']['customer'] = $sales->cust_id;
        $data['default']['email'] = $customer->email;
        $data['default']['ship_address'] = $customer->shipping_address;
        $data['default']['dates'] = $sales->dates;
        $data['default']['due_date'] = $sales->due_date;
        $data['default']['payment'] = $sales->payment_id;
        $data['total'] = $sales->total;
        $data['shipping'] = $sales->shipping;
        $data['tot_amt'] = intval($sales->amount+$sales->shipping);
        
        // weight total
        $total = $this->sitem->total($param);
        $data['weight'] = round($total['weight']);
        $data['tax']    = $sales->tax;
        
        // shipping details
        $shipping = $this->shipping->get_detail_based_sales($param);
        if ($shipping){
           
           $dst = array($shipping->dest_id,$shipping->dest); 
           $pck = array($shipping->package, $shipping->rate);
           
           $data['default']['dest'] = implode('|', $dst);
           $data['default']['dest_desc'] = $shipping->dest_desc; 
           $data['package'] = implode('|', $pck);
           $data['default']['courier'] = $shipping->courier;
           $data['default']['package'] = $shipping->package;
           $data['default']['rate'] = $shipping->rate;
        }
        
        // transaction table
        $data['items'] = $this->sitem->get_last_item($param)->result();
        $this->load->view('template', $data);
    }
    
        // Fungsi update untuk menset texfield dengan nilai dari database
    function invoice($param=0,$type='invoice')
    {
        $data['title'] = $this->properti['name'].' | Invoice '.ucwords($this->modul['title']).' | SO-0'.$param;
        $sales = $this->Sales_model->get_by_id($param)->row();
        
        if ($sales){
                
            // property
            $data['p_name'] = $this->properti['sitename'];
            $data['p_address'] = $this->properti['address'];
            $data['p_city'] = $this->properti['city'];
            $data['p_zip']  = $this->properti['zip'];
            $data['p_phone']  = $this->properti['phone1'];
            $data['p_email']  = $this->properti['email'];
            $data['p_logo']  = $this->properti['logo'];

            // customer details
            $customer = $this->customer->get_details($sales->cust_id)->row();
            $data['c_name'] = strtoupper($customer->first_name.' '.$customer->last_name);
            $data['c_email'] = $customer->email;
            $data['c_address'] = $customer->shipping_address;
            $data['c_phone'] = $customer->phone1.' / '.$customer->phone2;
            $data['c_city'] = $this->city->get_name($customer->city);
            $data['c_zip'] = $customer->zip;

            // sales
            $data['so'] = 'SO-0'.$param;
            $data['dates'] = tglin($sales->dates);
            $data['due_date'] = tglin($sales->due_date);
            $data['payment'] = $this->payment->get_name($sales->payment_id);

            if ($sales->paid_date){ $data['paid'] = 'Paid'; }else { $data['paid'] = 'Unpaid'; }
            $data['total'] = $sales->total;
            $data['shipping'] = idr_format($sales->shipping);
            $data['tot_amt'] = idr_format(intval($sales->amount+$sales->shipping));
            $data['amount'] = idr_format($sales->amount);

            // weight total
            $total = $this->sitem->total($param);
            $data['weight'] = round($total['weight']);
            $data['tax']    = $sales->tax;

            // shipping details
            $shipping = $this->shipping->get_detail_based_sales($param);
            $data['ship_date'] = tglin($shipping->shipdate);
            $data['courier'] = strtoupper($shipping->courier);
            $data['package'] = $shipping->package;
            $data['awb'] = strtoupper($shipping->awb);
            $data['rate'] = $shipping->rate;
            $data['dest_desc'] = $shipping->dest_desc;
            $data['dest'] = $shipping->dest;

            if (!$sales->paid_date){ $data['ship_status'] = 'Not Shipped'; }else { $data['ship_status'] = 'Shipped'; } 

            // transaction table
            $data['items'] = $this->sitem->get_last_item($param)->result();
            if ($type == 'invoice'){ $this->load->view('sales_invoice', $data); }else{
                $this->load->view('shipping_invoice', $data);
            }
        }
    }
    
    function update_process($param)
    {
        if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'sales_form';
        $data['form_action'] = site_url($this->title.'/update_process/'.$param); 
	$data['link'] = array('link_back' => anchor('category/','<span>back</span>', array('class' => 'back')));

	// Form validation
        $this->form_validation->set_rules('ccustomer', 'Customer', 'required');
        $this->form_validation->set_rules('tdates', 'Transaction Date', 'required');
        $this->form_validation->set_rules('tduedates', 'Transaction Due Date', 'required');
        $this->form_validation->set_rules('cpayment', 'Payment Type', 'required');

        if ($this->form_validation->run($this) == TRUE && $this->valid_confirm($param) == TRUE && $this->valid_items($param) == TRUE)
        {
            $sales = array('cust_id' => $this->input->post('ccustomer'),
                           'due_date' => $this->input->post('tduedates'), 'payment_id' => $this->input->post('cpayment'), 
                           'updated' => date('Y-m-d H:i:s'));

            $this->Sales_model->update($param, $sales);
            $this->mail_invoice($param); // send email confirmation
            $this->session->set_flashdata('message', "One $this->title data successfully saved!");
            echo "true|One $this->title data successfully saved!|".$param;
        }
        else{ echo "error|". validation_errors(); $this->session->set_flashdata('message', validation_errors()); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
        //redirect($this->title.'/update/'.$param);
    }
    
    function confirmation($sid)
    {
        $sales = $this->Sales_model->get_by_id($sid)->row();
	$this->session->set_userdata('langid', $sales->id);
        
        echo $sid.'|'.$sales->sender_name.'|'.$sales->sender_acc.'|'.$sales->sender_bank.'|'.$sales->sender_amount.'|'.$sales->bank_id.'|'.$sales->confirmation.'|'.
             tglin($sales->paid_date).'|'.date("H:i:s", $sales->paid_date);
    }
    
    function payment_confirmation()
    {
       if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'sales_form';
	$data['link'] = array('link_back' => anchor('category/','<span>back</span>', array('class' => 'back')));

	// Form validation
        $this->form_validation->set_rules('tcdates', 'Confirmation Date', 'required');
        $this->form_validation->set_rules('taccname', 'Account Name', 'required');
        $this->form_validation->set_rules('taccno', 'Account No', 'required');
        $this->form_validation->set_rules('taccbank', 'Account Bank', 'required');
        $this->form_validation->set_rules('tamount', 'Amount', 'required|numeric');
        $this->form_validation->set_rules('cbank', 'Merchant Bank', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            if ($this->input->post('cstts') == '1'){
                $sales = array('confirmation' => 1, 'updated' => date('Y-m-d H:i:s'));
                $stts = 'confirmed!';
                // lakukan action pengurangan stock
                $this->change_product($this->session->userdata('langid'));
                
                $this->Sales_model->update($this->session->userdata('langid'), $sales);
                // lakukan action email ke customer
                $status = $this->mail_invoice($this->session->userdata('langid'));
            }
            else { $sales = array('confirmation' => 0, 'updated' => date('Y-m-d H:i:s')); 
                   $stts = 'unconfirmed!'; 
                   // lakukan action pengurangan stock
                   $this->change_product($this->session->userdata('langid'),0);
                $status = true;
                $this->Sales_model->update($this->session->userdata('langid'), $sales);
            }
            
            if ($status == true){
               echo "true|One $this->title data payment successfully ".$stts;  
            }else { echo "error|Error Sending Mail...!! ";   }
        }
        else{ echo "error|". validation_errors(); $this->session->set_flashdata('message', validation_errors()); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; } 
    }
    
    private function mail_invoice($pid)
    {   
        // property display
       $data['p_logo'] = $this->properti['logo'];
       $data['p_name'] = $this->properti['name'];
       $data['p_site_name'] = $this->properti['sitename'];
       $data['p_address'] = $this->properti['address'];
       $data['p_zip'] = $this->properti['zip'];
       $data['p_city'] = $this->properti['city'];
       $data['sites_url'] = constant("BASE_URL");
       
       $sales = $this->Sales_model->get_by_id($pid)->row();
       $cust = $this->customer->get_details($sales->cust_id)->row();
       $shipping = $this->shipping->get_detail_based_sales($pid);
      
       $data['so_no']   = 'DISO-0'.$pid;
       $data['so_date'] = tglin($sales->dates).' '. timein($sales->dates);
       $data['c_name'] = ucfirst($cust->first_name.' '.$cust->last_name);
       $data['c_phone'] = $cust->phone1.' / '.$cust->phone2;
       $data['payment'] = $this->payment->get_name($sales->payment_id);
       $data['courier'] = strtoupper($shipping->courier);
       $data['package'] = strtoupper($shipping->package);
       $data['ship_address'] = $shipping->dest_desc;
       $data['sub_total'] = num_format($sales->amount);
       $data['shipping_amt'] = num_format($sales->shipping);
       $data['total'] = num_format(floatval($sales->amount+$sales->shipping));
       
       $data['item'] = $this->sitem->get_last_item($pid)->result();
       
       if($sales->confirmation == 0){ 
          $data['status'] = 'Pending'; 
          $html = $this->load->view('sales_order_credit',$data,true);
          $subject = 'Konfirmasi Pesanan - '.$data['so_no'].' - '.$data['p_name'];
       }else{ $data['status'] = 'Lunas'; 
         $html = $this->load->view('sales_order_lunas',$data,true); 
         $subject = 'Pembayaran Sukses - '.$data['so_no'].' - '.$data['p_name'];
       }
         
        // email send
        $this->load->library('email');
        $config['charset']  = 'utf-8';
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'html';

        $this->email->initialize($config);
        $this->email->from($this->properti['billing_email'], $this->properti['name']);
        $this->email->to($cust->email);
        $this->email->cc($this->properti['cc_email']); 

        $this->email->subject($subject);
        $this->email->message($html);
//        $pdfFilePath = FCPATH."/downloads/".$no.".pdf";

        if (!$this->email->send()){ return false; }else{ return true;  }
    }
    
    private function change_product($sid,$type=1)
    {
        $item = $this->sitem->get_last_item($sid)->result();
        if ($type==1){ foreach ($item as $res) { $this->product->min_qty($res->product_id,$res->qty); } }
        else{ foreach ($item as $res) { $this->product->add_qty($res->product_id,$res->qty); } }
    }
    
    function valid_product($id,$sid)
    {
        if ($this->sitem->valid_product($id,$sid) == FALSE)
        {
            $this->form_validation->set_message('valid_product','Product already listed..!');
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_name($val)
    {
        if ($this->Sales_model->valid('name',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_name','Name registered..!');
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_confirm($sid)
    {
        if ($this->Sales_model->valid_confirm($sid) == FALSE)
        {
            $this->form_validation->set_message('valid_confirm','Sales Already Confirmed..!');
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_items($sid)
    {
        if ($this->sitem->valid_items($sid) == FALSE)
        {
            $this->form_validation->set_message('valid_items',"Empty Transaction..!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function report_process()
    {
        $this->acl->otentikasi2($this->title);
        $data['title'] = $this->properti['name'].' | Report '.ucwords($this->modul['title']);

        $data['rundate'] = tglin(date('Y-m-d'));
        $data['log'] = $this->session->userdata('log');
        $period = $this->input->post('reservation');  
        $start = picker_between_split($period, 0);
        $end = picker_between_split($period, 1);
        $paid = $this->input->post('cpaid');
        $shipped = $this->input->post('cshipped');
        $confirm = $this->input->post('cconfirm');

        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        if (!$paid){ $data['paid'] = ''; }elseif ($paid == 1){ $data['paid'] = 'Paid'; }else { $data['paid'] = 'Unpaid'; }
        if (!$confirm){ $data['confirm'] = ''; }elseif ($confirm == 1){ $data['confirm'] = 'Confirmed'; }else { $data['confirm'] = 'Unconfirmed'; }
        
//        Property Details
        $data['company'] = $this->properti['name'];
        $data['reports'] = $this->Sales_model->report($start,$end,$paid,$shipped,$confirm)->result();
//        
        if ($this->input->post('ctype') == 0){ $this->load->view('sales_report', $data); }
        else { $this->load->view('sales_pivot', $data); }
    }   

}

?>