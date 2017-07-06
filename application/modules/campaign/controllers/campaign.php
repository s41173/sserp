<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Campaign extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Campaign_model', '', TRUE);

        $this->properti = $this->property->get();
        $this->acl->otentikasi();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->role = new Role_lib();
        $this->category = new News_category_lib();
        $this->language = new Language_lib();
        $this->article = new Article_lib();
        $this->customer = new Customer_lib();
        $this->subscriber = new Subscriber_lib();
    }

    private $properti, $modul, $title, $article;
    private $role, $category, $language, $customer,$subscriber;

    function index()
    {
       $this->get_last(); 
    }
     
    public function getdatatable($search=null,$cat='null',$type='null',$publish='null')
    {
        if(!$search){ $result = $this->Campaign_model->get_last($this->modul['limit'])->result(); }
        else {$result = $this->Campaign_model->search($cat,$type,$publish)->result(); }
	
        $output = null;
        if ($result){
                
         foreach($result as $res)
	 {             
	   $output[] = array ($res->id, $res->email_from, $res->email_to, $res->type, $res->category, 
                              $this->article->get_name($res->article_id), tglin($res->dates), $res->publish,
                              $res->created, $res->updated, $res->deleted
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
    
//    =========================== ajax ==========================================
      function get_article_combo($category)
      {
          $combo = $this->article->combo_category($category);
          $js = "class='form-control' id='carticle' tabindex='-1' style='width:100%;' "; 
          echo form_dropdown('carticle', $combo, isset($default['article']) ? $default['article'] : '', $js);
      }
         
//    =========================== ajax ==========================================
    
    function get_last()
    {
        $this->acl->otentikasi1($this->title);

        $data['title'] = $this->properti['name'].' | Administrator  '.ucwords('Campaign Manager');
        $data['h2title'] = 'Campaign Manager';
        $data['main_view'] = 'campaign_view';

	$data['form_action'] = site_url($this->title.'/add_process');
        $data['form_action_update'] = site_url($this->title.'/update_process');
        $data['form_action_confirmation'] = site_url($this->title.'/confirmation_process');
        $data['form_action_report'] = site_url($this->title.'/report_process');
        $data['form_action_del'] = site_url($this->title.'/delete_all');
        $data['link'] = array('link_back' => anchor('main/','Back', array('class' => 'btn btn-danger')));

        $data['category'] = $this->category->combo_all('name');
        $data['category_id'] = $this->category->combo_all();
        $data['language'] = $this->language->combo_all();
        $data['email'] = $this->property->combo_email();
        $data['email_all'] = $this->property->combo_email('param');
        $data['article'] = $this->article->combo();
        
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
        $this->table->set_heading('#','No', 'From', 'Category', 'Date', 'Type', 'Subject', 'Action');

        $data['table'] = $this->table->generate();
        $data['source'] = site_url($this->title.'/getdatatable');
            
        // Load absen view dengan melewatkan var $data sbgai parameter
	$this->load->view('template', $data);
    }
    
    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title,'ajax') == TRUE){ 
       $val = $this->Campaign_model->get_by_id($uid)->row();
       if ($val->publish == 0){ $lng = array('publish' => 1); }else { $lng = array('publish' => 0); }
       $this->Campaign_model->update($uid,$lng);
       echo 'true|Status Changed...!';
       }else{ echo "error|Sorry, you do not have the right to change publish status..!"; }
    }
    
    function delete_all()
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
             $this->Campaign_model->delete($cek[$i]);
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
            $this->Campaign_model->delete($uid);
            
            $this->session->set_flashdata('message', "1 $this->title successfully removed..!");

            echo "true|1 $this->title successfully removed..!";
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
        
    }

    function add_process()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){

            $data['title'] = $this->properti['name'].' | Administrator  '.ucwords('Campaign Manager');
            $data['h2title'] = 'Campaign Manager';
            $data['form_action'] = site_url($this->title.'/add_process');
            $data['link'] = array('link_back' => anchor('admin/','<span>back</span>', array('class' => 'back')));

            // Form validation
            $this->form_validation->set_rules('cfrom', 'Email-From', 'required');
            $this->form_validation->set_rules('cto', 'Target', 'required');
            $this->form_validation->set_rules('rtype', 'Campaign Type', 'required');
            $this->form_validation->set_rules('ccategory', 'Article Category', 'required');
            $this->form_validation->set_rules('carticle', 'Article', 'required');
            $this->form_validation->set_rules('tsubject', 'Subject', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {  
                $campaign = array(
                'email_from' => $this->input->post('cfrom'),
                'email_to' => $this->split_array($this->input->post('cto')),
                'type' => $this->input->post('rtype'),
                'subject' => $this->input->post('tsubject'),
                'category' => strtolower($this->category->get_name($this->input->post('ccategory'))),
                'article_id' => $this->input->post('carticle'), 
                'created' => date('Y-m-d H:i:s'));

                $this->Campaign_model->add($campaign);
                $this->session->set_flashdata('message', "One $this->title data successfully saved!");
                echo 'true|Data successfully saved..!|';
            }
            else{  echo 'error|'.validation_errors(); }
        }
        else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    private function cek_tick($val)
    {
        if (!$val)
        { return 0;} else { return 1; }
    }
    
    private function split_array($val)
    { return implode(",",$val); }
    
    function remove_img($id)
    {
        $img = $this->Campaign_model->get_by_id($id)->row();
        $img = $img->icon;
        if ($img){ $img = "./images/component/".$img; unlink("$img"); }
    }

    // Fungsi update untuk menset texfield dengan nilai dari database
    function update($uid=null)
    {        
        $campaign = $this->Campaign_model->get_by_id($uid)->row();
        $this->session->set_userdata('langid', $campaign->id);
        
        echo $campaign->id.'|'.$campaign->email_from.'|'.$campaign->email_to.'|'.$campaign->type.'|'.$campaign->category.'|'.
        strtolower($this->category->get_id($campaign->category)).'|'.$campaign->article_id.'|'.$campaign->dates.'|'.$campaign->publish.'|'.
        $campaign->subject;
    }
 
    function valid($val)
    {
        if ($this->Campaign_model->valid('title',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_modul', $this->title.' registered');
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validating($val)
    {
	$id = $this->session->userdata('langid');
	if ($this->Campaign_model->validating('title',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_modul', "This $this->title name is already registered!");
            return FALSE;
        }
        else{ return TRUE; }
    }

    // Fungsi update untuk mengupdate db
    function update_process()
    {
        if ($this->acl->otentikasi_admin($this->title) == TRUE){

        $data['title'] = $this->properti['name'].' | Campaignistrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'admin_update';
	$data['form_action'] = site_url($this->title.'/update_process');
	$data['link'] = array('link_back' => anchor('admin/','<span>back</span>', array('class' => 'back')));

	// Form validation
        $this->form_validation->set_rules('cfrom', 'Email-From', 'required');
        $this->form_validation->set_rules('cto', 'Target', 'required');
        $this->form_validation->set_rules('rtype', 'Campaign Type', 'required');
        $this->form_validation->set_rules('ccategory', 'Article Category', 'required');
        $this->form_validation->set_rules('carticle', 'Article', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $campaign = array(
                'email_from' => $this->input->post('cfrom'),
                'email_to' => $this->split_array($this->input->post('cto')),
                'type' => $this->input->post('rtype'),
                'subject' => $this->input->post('tsubject'),
                'category' => strtolower($this->category->get_name($this->input->post('ccategory'))),
                'article_id' => $this->input->post('carticle'));
            
	    $this->Campaign_model->update($this->session->userdata('langid'), $campaign);
            $this->session->set_flashdata('message', "One $this->title has successfully updated!");
            
            $this->session->unset_userdata('langid');
            echo 'true|Data successfully saved..!';

        }
        else{ echo 'error|'.validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; }
    }
    
    function confirmation_process()
    {
       if ($this->acl->otentikasi_admin($this->title) == TRUE){

        $data['title'] = $this->properti['name'].' | Campaignistrator  '.ucwords($this->modul['title']);
        $data['h2title'] = $this->modul['title'];
        $data['main_view'] = 'admin_update';
	$data['form_action'] = site_url($this->title.'/update_process');
	$data['link'] = array('link_back' => anchor('admin/','<span>back</span>', array('class' => 'back')));

	// Form validation
        $this->form_validation->set_rules('tcdates', 'Confirmation Date', 'required');
        $this->form_validation->set_rules('cstts', 'Confirm Status', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $type = $this->Campaign_model->get_by_id($this->session->userdata('langid'))->row();
            
            if ($this->input->post('cstts') == 0){
               $campaign = array(
                'dates' => null,
                'publish' => $this->input->post('cstts')); 
                $stts = true;
            }else{
               $campaign = array(
                'dates' => $this->input->post('tcdates'),
                'publish' => $this->input->post('cstts')); 
               
               // sending campaign 
               if ($type->type == 'email'){ $stts = $this->mail_campaign($this->session->userdata('langid')); }
               elseif ($type->type == 'sms'){ /* sms campaign */ }
            }
            
            if ($stts == true){
              $this->Campaign_model->update($this->session->userdata('langid'), $campaign);
              $this->session->set_flashdata('message', "One $this->title has successfully updated!");
              
              $this->session->unset_userdata('langid');
              echo 'true|Data Successfully Saved..!'; 
            }else { echo 'error|Sent Email Failed...!!'; }
	    
        }
        else{ echo 'error|'.validation_errors(); }
        }else { echo "error|Sorry, you do not have the right to edit $this->title component..!"; } 
    }
    
    private function get_customer_type($val)
    {
       $hasil = array();
       $i=0;
       if ($val == 'customer'){ $result = $this->customer->get_cust_type('customer'); foreach ($result as $res) { $hasil[$i] = $res->email; $i++; } }
       elseif ($val == 'member'){ $result = $this->customer->get_cust_type('member'); foreach ($result as $res) { $hasil[$i] = $res->email; $i++; } }       
       elseif ($val == 'subscriber'){ $result = $this->subscriber->get(); foreach ($result as $res) { $hasil[$i] = $res->email; $i++; } }
       return $hasil;
    }
    
    private function mail_campaign($pid)
    {   
        // property display
       $data['p_logo'] = $this->properti['logo'];
       $data['p_name'] = $this->properti['name'];
       $data['p_site_name'] = $this->properti['sitename'];

       $campaign = $this->Campaign_model->get_by_id($pid)->row();
       $res = explode(',', $campaign->email_to);
       $val1 = array(); $val2 = array(); $val3 = array();
       
       if (count($res) == 1){ $val1 = $this->get_customer_type($res[0]); }
       else if (count($res) == 2){ $val1 = $this->get_customer_type($res[0]); $val2 = $this->get_customer_type($res[1]); }
       else if (count($res) == 3){ $val1 = $this->get_customer_type($res[0]); $val2 = $this->get_customer_type($res[1]); $val3 = $this->get_customer_type($res[2]); }
       
       $to = array_merge($val1,$val2,$val3);
//       print_r(array_values($to));
       
       $data['from'] = $campaign->email_from;
       $data['to'] = $campaign->email_to;
       $data['type'] = $campaign->type;
       $data['category'] = $campaign->category;
       $data['article'] = $this->article->get_name($campaign->article_id);
       $data['dates'] = tglin($campaign->dates).' - '. timein($campaign->dates);
       $data['content'] = $this->article->get_content($campaign->article_id);
      
       $html = $this->load->view('campaign_invoice_email',$data,true);
//       $html = $this->load->view('order_email',$data,true);
        
        // email send
        $this->load->library('email');
        $config['charset']  = 'utf-8';
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'html';

        $this->email->initialize($config);
        $this->email->from($campaign->email_from, $data['p_name']);
        $this->email->to($to);
        $this->email->cc($this->properti['cc_email']); 

        $this->email->subject($campaign->subject);
        $this->email->message($html);
//        $pdfFilePath = FCPATH."/downloads/".$no.".pdf";
//
        if (!$this->email->send()){ return false; }else{ return true;  }
    }
    
    function mail_test()
    {   
        // property display
       $data['p_logo'] = $this->properti['logo'];
       $data['p_name'] = $this->properti['name'];
       $data['p_site_name'] = $this->properti['sitename'];    
      
//       $html = $this->load->view('campaign_invoice_email',$data,true);
       $html = $this->load->view('order_email',$data,true);
        
        // email send
        $this->load->library('email');
        $config['charset']  = 'utf-8';
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'text';

        $this->email->initialize($config);
        $this->email->from('info@delicaindonesia.com', $data['p_name']);
//        $this->email->to($to);
        $this->email->to('sanjaya.kiran@gmail.com');
        $this->email->cc($this->properti['cc_email']); 

        $this->email->subject('Test Pesan');
        $this->email->message($html);
//        $pdfFilePath = FCPATH."/downloads/".$no.".pdf";
//
//        if (!$this->email->send()){ return false; echo 'tidak terkirim'; }else{ return true; echo 'terkirim';   }
        $this->email->send();
        echo $this->email->print_debugger();
    }
    
    function report_process()
    {
        $this->acl->otentikasi2($this->title);
        $data['title'] = $this->properti['name'].' | Report '.ucwords($this->modul['title']);

        $data['rundate'] = tglin(date('Y-m-d'));
        $data['log'] = $this->session->userdata('log');
        $period = $this->input->post('campaignperiod');  
        
        $start = picker_between_split($period, 0);
        $end = picker_between_split($period, 1);
        
        $from = $this->input->post('cfrom');
        $type = $this->input->post('rtype');
        $category = $this->input->post('ccategory');

        $data['start'] = tglin($start);
        $data['end'] = tglin($end);
        
//        Property Details
        $data['company'] = $this->properti['name'];
        $data['reports'] = $this->Campaign_model->report($start, $end, $from, $type, $category)->result();
        
        $this->load->view('campaign_report', $data);
    } 
    
            // Fungsi update untuk menset texfield dengan nilai dari database
    function receipt($param=0,$type='invoice')
    {
        $campaign = $this->Campaign_model->get_by_id($param)->row();
        
        $data['title'] = $this->properti['name'].' | Invoice '.ucwords($this->modul['title']).' | CMP-0'.$campaign->id;
        
        if ($campaign){
                
            // property
            $data['p_name'] = $this->properti['sitename'];
            $data['p_address'] = $this->properti['address'];
            $data['p_city'] = $this->properti['city'];
            $data['p_zip']  = $this->properti['zip'];
            $data['p_phone']  = $this->properti['phone1'];
            $data['p_email']  = $this->properti['email'];
            $data['p_logo']  = $this->properti['logo'];

            // campaign details
            $data['from'] = $campaign->email_from;
            $data['to'] = $campaign->email_to;
            $data['type'] = $campaign->type;
            $data['category'] = $campaign->category;
            $data['subject'] = $campaign->subject;
            $data['article'] = $this->article->get_name($campaign->article_id);
            $data['dates'] = tglin($campaign->dates).' - '. timein($campaign->dates);
            $data['content'] = $this->article->get_content($campaign->article_id);

            $this->load->view('campaign_invoice', $data);
        }
    }

}

?>