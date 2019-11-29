<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
include_once 'definer.php';
class Product extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Product_model', 'model', TRUE);

        $this->properti = $this->property->get();

        $this->modul = $this->components->get(strtolower(get_class($this)));
        $this->title = strtolower(get_class($this));
        $this->role = new Role_lib();
        $this->category = new Categoryproduct_lib();
        $this->manufacture = new Manufacture_lib();
        $this->attribute = new Attribute_lib();
        $this->attribute_product = new Attribute_product_lib();
        $this->attribute_list = new Attribute_list_lib();
        $this->currency = new Currency_lib();
        $this->product = new Product_lib();
        $this->wt = new Warehouse_transaction_lib();
        $this->branch = new Branch_lib();
        $this->conversi = new Conversion_lib();
        $this->period = new Period_lib();
        $this->period = $this->period->get();
        $this->stockledger = new Stock_ledger_lib();
        $this->stock = new Stock_lib();
        
        $this->api = new Api_lib();
        $this->acl = new Acl();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token'); 
    }

    private $properti, $modul, $title, $product, $wt, $branch, $conversi, $period, $stockledger, $stock;
    private $role, $category, $manufacture, $attribute, $attribute_product, $attribute_list, $currency, $api, $acl;
    
    protected $error = null;
    protected $status = 200;
    protected $output = null;
    
    function search(){
      if ($this->acl->otentikasi1($this->title) == TRUE){  
          
        $datax = (array)json_decode(file_get_contents('php://input')); 
        $name = null;
        if (isset($datax['filter'])){ $name = $datax['filter']; }
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; }else{ $this->limitx = $this->modul['limit']; }
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
        if ($name != null){ $result = $this->model->search_name($name, $this->limitx, $this->offsetx)->result();
        
            $resx = null;
            foreach($result as $res)
            {
               if ($res->image){$img = $this->properti['image_url'].'product/'.$res->image;}else{ $img = null; }
               $qty = $this->stockledger->get_qty($res->id, $this->branch->get_branch(), $this->period->month, $this->period->year);  
               $resx[] = array ("id"=>$res->id, "category"=>$this->category->get_name($res->category), "brand"=>$this->manufacture->get_name($res->manufacture),
                                "image"=> $img, "sku"=>$res->sku, "name"=>$res->name, "model"=>$res->model, 
                                "price"=>floatval($res->price), "net_price"=>floatval($res->price-$res->discount), "qty"=>$qty,
                                "color"=>$res->color, "size"=> $res->size, "log"=> $this->decodedd->log, "branch"=> $this->branch->get_name($this->branch->get_branch()),
                                "publish"=>$res->publish
                               );
            }
            $data['result'] = $resx; $this->output = $data;
        }
        
      }else{ $this->reject_token(); }
      $this->response('content'); 
    }
    
    function index()
    {
        if ($this->acl->otentikasi1($this->title) == TRUE){
        $datax = (array)json_decode(file_get_contents('php://input')); 
        if (isset($datax['offset'])){ $this->offsetx = $datax['offset']; }
        
        $branch = null; $cat=null; $col=null; $size=null; $publish=null; $sku=null; $brand=null; $limit=100;
        if (isset($datax['branch'])){ $branch = $datax['branch']; }
        if (isset($datax['category'])){ $cat = $datax['category']; }
        if (isset($datax['color'])){ $col = $datax['color']; }
        if (isset($datax['size'])){ $size = $datax['size']; }
        if (isset($datax['publish'])){ $publish = $datax['publish']; }
        if (isset($datax['sku'])){ $sku = $datax['sku']; }
        if (isset($datax['brand'])){ $brand = $datax['brand']; }
        if (isset($datax['limit'])){ $this->limitx = $datax['limit']; $limit = $datax['limit']; }
        else{ $this->limitx = $this->modul['limit']; }
        
        if($branch == null && $cat == null && $col == null && $size == null && $publish == null && $sku == null){ 
            $branch = $this->branch->get_branch();
            $result = $this->model->get_last($this->limitx, $this->offsetx)->result();   
        }
        else{ 
            if ($sku != null){ $result = $this->model->search_sku($sku)->result();   }
            else{ $result = $this->model->search($cat,$col,$size,$brand,$publish,$limit)->result();  }       
        }
        
        $resx = null;
	foreach($result as $res)
	{
           if ($res->image){$img = $this->properti['image_url'].'product/'.$res->image;}else{ $img = null; }
           $qty = $this->stockledger->get_qty($res->id, $branch, $this->period->month, $this->period->year);  
           $resx[] = array ("id"=>$res->id, "category"=>$this->category->get_name($res->category), "brand"=>$this->manufacture->get_name($res->manufacture),
                            "image"=> $img, "sku"=>$res->sku, "name"=>$res->name, "model"=>$res->model, 
                            "price"=>floatval($res->price), "net_price"=>floatval($res->price-$res->discount), "qty"=>$qty,
                            "color"=>$res->color, "size"=> $res->size, "log"=> $this->decodedd->log, "branch"=> $this->branch->get_name($branch),
                            "publish"=>$res->publish, 
                           );
	}
        $data['result'] = $resx; $data['chart'] = $this->chart(); $this->output = $data;
        }else{ $this->reject_token(); }
        $this->response('content');
    } 
    
    private function chart()
    {
        $data = $this->category->get();
        $datax = array();
        $branch = $this->decodedd->branch;
        
        foreach ($data as $res) 
        {  
           $point = array("label" => $res->name , "y" => $this->product->get_product_based_category($res->id, $branch, $this->period->month, $this->period->year));
           array_push($datax, $point);      
        }
        return $datax;
    }
    
    function publish($uid = null)
    {
       if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
       $val = $this->model->get_by_id($uid)->row();
       if ($val->publish == 0){ $lng = array('publish' => 1); }else { $lng = array('publish' => 0); }
       if ($this->model->update($uid,$lng) == true){ $this->error = 'Status Changed...!'; }else{ $this->reject(); }
       }else { $this->reject_token('Invalid Token or Expired..!'); }
       $this->response();
    }
    
    function update_all(){
        
       if ($this->acl->otentikasi2($this->title) == TRUE){ 
           
        $category = $this->input->post('category');
        $size = $this->input->post('size');
        $color = $this->input->post('color');
        $publish = $this->input->post('publish');
        
        $cek = $this->input->post('cek');
        $jumlah = count($cek);
        
        if($cek)
        {
          $jumlah = count($cek);
          for ($i=0; $i<$jumlah; $i++)
          {      
            $product = array('category' => $category, 'size' => $size, 'color' => $color, 'publish' => $publish);    
            $this->model->update($cek[$i], $product);
          }
            
          $mess = intval($jumlah)." ".$this->title."successfully updated..!!";
          $this->error = $mess;
        }
        else
        { 
          $mess = "No $this->title Selected..!!";
          $this->reject($mess);
        }
       }else { $this->reject_token('Invalid Token or Expired..!'); }
       $this->response();
    }
    
    function delete_all($type='soft')
    {
      if ($this->acl->otentikasi3($this->title) == TRUE){
      
        $cek = $this->input->post('cek');
        $jumlah = count($cek);

        if($cek)
        {
          $jumlah = count($cek);
          $x = 0;
          for ($i=0; $i<$jumlah; $i++)
          {
             if ($this->valid_qty($cek[$i]) == TRUE){
                if ($type == 'soft') { $this->delete($cek[$i]); }
                else { $this->remove_img($cek[$i],'force');
                       $this->attribute_product->force_delete_by_product($cek[$i]);
                       $this->model->force_delete($cek[$i]);  }
                $x=$x+1;
             }
          }
          $res = intval($jumlah-$x);
          $mess = "$res $this->title successfully removed &nbsp; - &nbsp; $x related to another component..!!";
          $this->error = $mess;
        }
        else
        { 
          $mess = "No $this->title Selected..!!";
          $this->reject($mess);
        }
      }else { $this->reject_token('Invalid Token or Expired..!'); }
      $this->response();
      
    }

    function delete($uid)
    {
        if ($this->acl->otentikasi3($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
                
            if ($this->valid_qty($uid) == TRUE){
               $this->remove_img($uid); 
               if ($this->model->delete($uid) == true){ $this->error = $this->title.'successfully removed..!'; }else{ $this->reject(); }
            }
            else{ $this->reject("Invalid Product Qty...!"); }    
        }else { $this->reject_token('Invalid Token or Expired..!'); }
        $this->response();
    }

    function add()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){ 

	// Form validation
        $this->form_validation->set_rules('tsku', 'SKU', 'callback_valid_sku');
        $this->form_validation->set_rules('tname', 'Name', 'required|callback_valid_name');
        $this->form_validation->set_rules('tmodel', 'Model', 'required|callback_valid_model');
        $this->form_validation->set_rules('ccur', 'Currency', 'required');
        $this->form_validation->set_rules('ccategory', 'Category', 'required');
        $this->form_validation->set_rules('cmanufacture', 'Manufacture', 'required');

        if ($this->form_validation->run($this) == TRUE)
        {
            $config['upload_path'] = $this->properti['url_upload'].'/images/product/';
            $config['file_name'] = split_space($this->input->post('tname'));
            $config['allowed_types'] = 'jpg|gif|png|jpeg';
            $config['overwrite'] = true;
            $config['max_size']	= '50000';
            $config['max_width']  = '30000';
            $config['max_height']  = '30000';
            $config['remove_spaces'] = TRUE;
    
            $this->load->library('upload', $config);
            
            if (!$this->input->post('tsku')){ $sku = $this->category->get_code($this->input->post('ccategory')).'-0'.$this->model->counter();
            }else { $sku = $this->input->post('tsku'); }
//
            if ( !$this->upload->do_upload("userfile")) // if upload failure
            {
                $info['file_name'] = null;
                $data['error'] = $this->upload->display_errors();
                $product = array('name' => strtolower($this->input->post('tname')), 'permalink' => split_space($this->input->post('tname')),
                                  'sku' => $sku, 'model' => $this->input->post('tmodel'), 
                                  'currency' => $this->input->post('ccur'), 'category' => $this->input->post('ccategory'),
                                  'manufacture' => $this->input->post('cmanufacture'),
                                  'image' => null, 'created' => date('Y-m-d H:i:s'));
            }
            else
            {
                $info = $this->upload->data();
                $this->crop_image($info['file_name']);
                
                $product = array('name' => strtolower($this->input->post('tname')), 'permalink' => split_space($this->input->post('tname')),
                                  'sku' => $sku, 'model' => $this->input->post('tmodel'), 
                                  'currency' => $this->input->post('ccur'), 'category' => $this->input->post('ccategory'),
                                  'manufacture' => $this->input->post('cmanufacture'),
                                  'image' => $info['file_name'], 'created' => date('Y-m-d H:i:s'));
            }

            if ($this->model->add($product) != true && $this->upload->display_errors()){ $this->error = $this->upload->display_errors(); $this->status = 401;
            }else{ $this->error = $this->title.' successfully saved..!'; }            
        }
        else{ $this->reject(validation_errors()); }
        }else { $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('c');
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
        $img = $this->model->get_by_id($id)->row();
        $img = $img->image;
        if ($img){ $img = $this->properti['image_url'].'product/'.$img; @unlink("$img");}
    }
    
    function details($uid=null)
    {    
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){ 
        $product = $this->model->get_by_id($uid)->row();
        
        $this->output = array("sku"=>$product->sku,"category"=>$this->category->get_name($product->category),"brand"=>$this->manufacture->get_name($product->manufacture),"name"=>$product->name,"model"=>$product->model,"currency"=>$product->currency,
                              "price"=>idr_format($product->pricelow).' - '.idr_format($product->price),"qty"=>$product->qty, "image"=> $this->properti['image_url'].'product/'.$product->image,
                              "dimension_class"=>$product->dimension_class, "weight"=>$product->weight, "dimension"=>$product->dimension, "color"=>$product->color, "size"=>$product->size,
                              "unit_cost"=>$this->conversi->calculate($this->stock->unit_cost($uid)), "last_cost"=>$this->conversi->calculate($this->stock->get_last_stock_price($uid)));
                
       }else{ $this->reject_token('Invalid Token or Expired..!'); }
       $this->response('content');
    }
    
    function get($uid=null)
    {        
        if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){
        
        $product = $this->model->get_by_id($uid)->row();
        if ($product->image != null){ $img = $this->properti['image_url'].'product/'.$product->image; }else{ $img = null; }
        
        $data['sku'] = $product->sku;
        $data['category'] = $product->category;
        $data['manufacture'] = $product->manufacture;
        $data['name'] = $product->name;
        $data['model'] = $product->model;
        $data['permalink'] = $product->permalink;
        $data['currency'] = $product->currency;
        $data['description'] = $product->description;
        $data['sdesc'] = $product->shortdesc;
        $data['spec'] = $product->spesification;
        $data['metatitle'] = $product->meta_title;
        $data['metadesc'] = $product->meta_desc;
        $data['metakeywords'] = $product->meta_keywords;
        $data['price'] = $product->price;
        $data['lowprice'] = $product->pricelow;
        $data['discount'] = $product->discount;
        $data['qty'] = $product->qty;
        $data['min'] = $product->min_order;
        $data['image'] = $img;
        $data['dclass'] = $product->dimension_class;
        $data['weight'] = $product->weight;
        $data['disc_p'] = @intval($product->discount/$product->price*100);
        $data['dimension'] = $product->dimension;
        $data['color'] = $product->color;
        $data['size'] = $product->size;
        $data['convertion_unit_cost'] = $this->conversi->calculate($this->stock->unit_cost($uid));
        $data['convertion_last_cost'] = $this->conversi->calculate($this->stock->get_last_stock_price($uid));
        
        
        if ($product->dimension)
        {
            $dimension = explode('x', $product->dimension);
            $data['length'] = $dimension[0];
            $data['width'] = $dimension[1];
            $data['height'] = $dimension[2];
        }
        else{
            $data['length'] = '';
            $data['width'] = '';
            $data['height'] = '';
        }

        if ($product->related){
            $related = explode(',', $product->related);
            $data['related'] = $related;
        }
        $this->output = $data;
        }else{ $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('content');
    }
    
    function image_gallery($pid=null)
    {        
       if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){
        
        $result = $this->model->get_by_id($pid)->row();
        for ($i=1; $i<=5; $i++)
        {   
            switch ($i) {
                case 1:$url = $result->url1; break;
                case 2:$url = $result->url2; break;
                case 3:$url = $result->url3; break;
                case 4:$url = $result->url4; break;
                case 5:$url = $result->url5; break;
            }
            $this->output[] = array("name"=>'Image'.$i, "image"=>$url);
        }
        
      }else{ $this->reject_token('Invalid Token or Expired..!'); }
      $this->response('content');
    }
    
    function valid_image($val)
    {
        if ($val == 0)
        {
            if (!$this->input->post('turl')){ $this->form_validation->set_message('valid_image','Image Url Required..!'); return FALSE; }
            else { return TRUE; }            
        }
    }
    
    function add_image($pid)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){

            // Form validation            
            $this->form_validation->set_rules('cname', 'Image Attribute', 'required|');
            $this->form_validation->set_rules('turl', 'Image Url', 'required');

            if ($this->form_validation->run($this) == TRUE)
            {  
                $attr = array('url'.$this->input->post('cname') => $this->input->post('turl'));
                $this->model->update($pid, $attr);
                $this->error = 'Image posted'; 
            }
            else{ $this->reject(validation_errors()); }
        }else{ $this->reject_token('Invalid Token or Expired..!'); }
        $this->response();
    }
    
    function valid_qty($pid)
    {
        $qty = $this->stockledger->get_qty($pid, null, $this->period->month, $this->period->year);
        if ($qty != 0){
           $this->form_validation->set_message('valid_qty', "Product Qty is greater than 0..!");
           return FALSE; 
        }else{ return TRUE; }
    }
    
    function valid_low_price($lowprice){
        $price = $this->input->post('tprice');
        if ($lowprice > $price){ $this->form_validation->set_message('valid_low_price', "Invalid Low-Price..!"); return FALSE; }
        else{ return TRUE; }
    }
    
    function valid_attribute($attr,$pid)
    {
        
        if($this->attribute_product->valid($attr, $pid) == FALSE)
        {
          $this->form_validation->set_message('valid_attribute', "Attribute Registered..!");
          return FALSE;
        }
        else{ return TRUE; }
    }

    function valid_role($val)
    {
        if(!$val)
        {
          $this->form_validation->set_message('valid_role', "role type required.");
          return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_sku($val)
    {
        if ($this->model->valid('sku',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_sku','SKU registered..!');
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_deleted($val,$id){
      $val = $this->model->get_by_id($id)->row();
      if ($val->deleted != NULL){ $this->form_validation->set_message('valid_deleted', "Product Already Deleted!"); return FALSE; }
      else{ return TRUE; }
    }
   
    function validating_sku($val,$id)
    {
	if ($this->model->validating('sku',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_sku', "SKU registered!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_name($val)
    {
        if ($this->model->valid('name',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_name','Name registered..!');
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validating_name($val,$id)
    {
	if ($this->model->validating('name',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_name', "Name registered!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    function valid_model($val)
    {
        if ($this->model->valid('model',$val) == FALSE)
        {
            $this->form_validation->set_message('valid_model','Model registered..!');
            return FALSE;
        }
        else{ return TRUE; }
    }

    function validating_model($val,$id)
    {
	if ($this->model->validating('model',$val,$id) == FALSE)
        {
            $this->form_validation->set_message('validating_model', "Model registered!");
            return FALSE;
        }
        else{ return TRUE; }
    }
    
    private function crop_image($filename){
        
        $config['image_library'] = 'gd2';
        $config['source_image'] = './images/product/'.$filename;
        $config['maintain_ratio'] = TRUE;
        $config['width']	= 300;
        $config['height']	= 300;

        $this->load->library('image_lib', $config); 
        $this->image_lib->resize();
    }

    // Fungsi update untuk mengupdate db
    function update($uid,$param=0)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE && $this->model->valid_add_trans($uid, $this->title) == TRUE){

	// Form validation
        if ($param == 1)
        {
            $this->form_validation->set_rules('tsku', 'SKU', 'required|callback_validating_sku['.$uid.']|callback_valid_deleted['.$uid.']');
            $this->form_validation->set_rules('ccategory', 'Category', 'required');
            $this->form_validation->set_rules('cmanufacture', 'Manufacture', 'required');
            $this->form_validation->set_rules('tname', 'Product Name', 'required|callback_validating_name['.$uid.']');
            $this->form_validation->set_rules('tmodel', 'Product Model', 'required|callback_validating_model['.$uid.']');
            $this->form_validation->set_rules('ccur', 'Currency', 'required');
            $this->form_validation->set_rules('tdesc', 'Description', '');
            $this->form_validation->set_rules('tshortdesc', 'Short Description', '');
            
            if ($this->form_validation->run($this) == TRUE)
            {
                // start update 1
                $config['upload_path'] = $this->properti['url_upload'].'/images/product/';
                $config['file_name'] = split_space($this->input->post('tname'));
                $config['allowed_types'] = 'jpg|gif|png';
                $config['overwrite'] = true;
                $config['max_size']	= '50000';
                $config['max_width']  = '30000';
                $config['max_height']  = '30000';
                $config['remove_spaces'] = TRUE;

                $this->load->library('upload', $config);
                $data['error'] = null;
                
                if ( !$this->upload->do_upload("userfile")) // if upload failure
                {
                    $info['file_name'] = null;
                    $data['error'] = $this->upload->display_errors();
                    $product = array('name' => strtolower($this->input->post('tname')), 'permalink' => split_space($this->input->post('tname')),
                                     'sku' => $this->input->post('tsku'), 'model' => $this->input->post('tmodel'), 
                                     'currency' => $this->input->post('ccur'), 'category' => $this->input->post('ccategory'),
                                     'manufacture' => $this->input->post('cmanufacture'), 'shortdesc' => $this->input->post('tshortdesc'),
                                     'description' => $this->input->post('tdesc'));
                }
                else
                {
                    $info = $this->upload->data();
                    $this->crop_image($info['file_name']);
                    
                    $product = array('name' => strtolower($this->input->post('tname')), 'permalink' => split_space($this->input->post('tname')),
                                      'sku' => $this->input->post('tsku'), 'model' => $this->input->post('tmodel'), 
                                      'currency' => $this->input->post('ccur'), 'category' => $this->input->post('ccategory'),
                                      'manufacture' => $this->input->post('cmanufacture'), 'shortdesc' => $this->input->post('tshortdesc'),
                                      'description' => $this->input->post('tdesc'),
                                      'image' => $info['file_name']);
                }
                
                if ($this->model->update($uid, $product) != true && $this->upload->display_errors()){ $this->reject($this->upload->display_errors());}else{ $this->error = 'Transaction Posted'; }
                // end update 1
            }
            else{ $this->reject(validation_errors()); }
        }
        elseif ($param == 2)
        {
            $product = array('meta_title' => $this->input->post('tmetatitle'), 'meta_desc' => $this->input->post('tmetadesc'),
                             'meta_keywords' => $this->input->post('tmetakeywords'), 'spesification' => $this->input->post('tspec')
                             );
            $this->model->update($uid, $product);
            $this->error = 'Transaction posted';
        }
        elseif ($param == 3)
        {
            $this->form_validation->set_rules('tprice', 'Price', 'required|numeric');
            $this->form_validation->set_rules('tlowprice', 'Low-Price', 'required|numeric|callback_valid_low_price');
            $this->form_validation->set_rules('tdisc_p', 'Discount Percentage', 'numeric');
            $this->form_validation->set_rules('tdiscount', 'Discount', 'required|numeric');
            $this->form_validation->set_rules('tmin', 'Minimum Order', 'required|numeric');
            
            if ($this->form_validation->run($this) == TRUE){
               
                $this->edit_qty($uid, $this->input->post('tqty'));
                $product = array('price' => $this->input->post('tprice'), 'pricelow' => $this->input->post('tlowprice'),
                                 'discount' => $this->input->post('tdiscount'),
                                 'min_order' => $this->input->post('tmin')
                                 );
                
              $this->model->update($uid, $product);
              $this->error = 'Transaction price and qty has successfully updated!';
            }else{ $this->reject(validation_errors()); }
        }
        elseif ($param == 4)
        {
            $this->form_validation->set_rules('tlength', 'Length (Dimension)', 'numeric');
            $this->form_validation->set_rules('twidth', 'Width (Dimension)', 'numeric');
            $this->form_validation->set_rules('theight', 'Height (Dimension)', 'numeric');
            $this->form_validation->set_rules('cdimension', 'Dimension Class', '');
            $this->form_validation->set_rules('tweight', 'Weight', 'numeric');
            $this->form_validation->set_rules('ccolor', 'Color', 'required');
            $this->form_validation->set_rules('csize', 'Size', 'required');
            
            $dimension = $this->input->post('tlength').'x'.$this->input->post('twidth').'x'.$this->input->post('theight');
            $product = array('dimension' => $dimension, 'dimension_class' => $this->input->post('cdimension'),
                             'weight' => $this->input->post('tweight'), 'color' => $this->input->post('ccolor'), 
                             'size' => $this->input->post('csize'), 'related' => !empty($this->input->post('crelated')) ? split_array($this->input->post('crelated')) : null
                             );
            
            $this->model->update($uid, $product);
            $this->error = 'Transaction dimension has successfully updated!';
        }

        }else{ $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('c');
    }
    
    private function edit_qty($pid,$eqty)
    {
        $res = $this->model->get_by_id($pid)->row();
        $begin = $res->qty;
        if ($begin > $eqty){ // pengurangan
            $this->wt->add(date('Y-m-d H:i:s'), '', $res->currency, $pid, 0, intval($begin-$eqty), 0, 0, $this->decodedd->log); 
        }
        elseif ($begin < $eqty) // penambahan
        {
           $this->wt->add(date('Y-m-d H:i:s'), '', $res->currency, $pid, intval($eqty-$begin), 0, 0, 0, $this->decodedd->log); 
        }
    }
    
    function report()
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){ 

        $data['rundate'] = tglin(date('Y-m-d'));
//        $data['log'] = $this->decoded->log;
        $data['category'] = $this->category->get_name($this->input->post('ccategory'));
        $data['manufacture'] = $this->manufacture->get_name($this->input->post('cmanufacture'));
        $data['year'] = $this->input->post('tyear');
        $data['month'] = $this->input->post('cmonth');
        
        $data['branch_id'] = $this->input->post('cbranch');
        $data['branch'] = $this->branch->get_name($this->input->post('cbranch'));
        $data['month'] = $this->input->post('cmonth');
        $data['year'] = $this->input->post('tyear');

//        Property Details
        $data['company'] = $this->properti['name'];
//        $data['reports'] = $this->model->report($this->input->post('ccategory'), $this->input->post('cmanufacture'))->result();
        
        $items = null;
        foreach($this->model->report($this->input->post('ccategory'), $this->input->post('cmanufacture'))->result() as $res)
	{
           $qty = $this->stockledger->get_qty($res->id, $this->input->post('cbranch'), $this->input->post('cmonth'), $this->input->post('tyear'));  
           $items[] = array ("id"=>$res->id, "category"=>$this->category->get_name($res->category), "brand"=>$this->manufacture->get_name($res->manufacture),
                            "image"=> $this->properti['image_url'].'product/'.$res->image, "sku"=>$res->sku, "name"=>$res->name, "model"=>$res->model, 
                            "price"=>floatval($res->price), "net_price"=>floatval($res->price-$res->discount), "qty"=>$qty,
                            "color"=>$res->color, "size"=> $res->size, "branch"=> $this->branch->get_name($this->input->post('cbranch')),
                            "publish"=>$res->publish, 
                           );
	}
        $data['items'] = $items; $this->output = $data;
        
        }else { $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('c');
    }
    
    function export_csv(){
        
      if ($this->acl->otentikasi2($this->title) == TRUE){  
        $stts = 'true';
        $error = null;
        $sku = $this->input->post('tsku');
        $qty = $this->input->post('tqty');
        
        if ($this->product->valid('sku', $sku) == FALSE){
            
            $name = $this->product->get_name_by_sku($sku);
            
            for ($i=0; $i<$qty; $i++){
              $list[$i] = array('sku'=> $sku, 'name'=> strtoupper(split_space($name)));
            }
        
            $fichier = $sku.'_'.split_space($name).'.csv';
            header( "Content-Type: text/csv;charset=utf-8" );
            header( "Content-Disposition: attachment;filename=\"$fichier\"" );
            header("Pragma: no-cache");
            header("Expires: 0");

            $fp= fopen('php://output', 'w');

            foreach ($list as $fields) 
            {
               fputcsv($fp, $fields, ',');
            }
            fclose($fp);
            exit();
            $this->error = "CSV Exported...!!";
            
        }else{ $this->reject('SKU Not Available..!'); }
        
      }else { $this->reject_token('Invalid Token or Expired..!'); }
      $this->response();
    }
    
    function import()
    {
      if ($this->acl->otentikasi2($this->title) == TRUE){  
       $data['error'] = null;
        // ==================== upload ========================

       $config['upload_path']   = './uploads/';
       $config['file_name']     = 'product';
       $config['allowed_types'] = '*';
//            $config['allowed_types'] = 'csv';
       $config['overwrite']     = TRUE;
       $config['max_size']	     = '10000';
       $config['remove_spaces'] = TRUE;
       $this->load->library('upload', $config);

       if ( !$this->upload->do_upload("userfile")){ $this->reject($this->upload->display_errors()); }
       else
       { 
          // success page 
         $status = $this->import_product($config['file_name'].'.csv');
         $info = $this->upload->data(); 
         if ($status == false){ $this->reject('Import Failed..!'); }else{ $this->error = 'CSV Successful Uploaded'; }
       }   
       }else { $this->reject_token('Invalid Token or Expired..!'); }
      $this->response();
    }
    
    private function import_product($filename)
    {
        $this->load->helper('file');
//        $csvreader = new CSVReader();
        $csvreader = $this->load->library('csvreader');
        $filename = './uploads/'.$filename;
        
        $result = $csvreader->parse_file($filename);
        $stts = true;
        foreach($result as $res)
        {
           if(isset($res['SKU']) && isset($res['CATEGORY']) && isset($res['MANUFACTURE']) && isset($res['NAME']) && isset($res['MODEL']) && isset($res['QTY']) && isset($res['PRICE']))
           {
              if ($this->valid_sku($res['SKU']) == TRUE  && $this->valid_name($res['NAME']) == TRUE)
              {
                $account = array(
                             'sku' => $res['SKU'],
                             'category' => $this->category->get_id_based_code(strtoupper($res['CATEGORY'])),
                             'manufacture' => $this->manufacture->get_id($res['MANUFACTURE']),
                             'name' => $res['NAME'],
                             'model' => $res['MODEL'],
                             'qty' => 0,
                             'price' => $res['PRICE'],
                             'publish' => 0,
                             'created' => date('Y-m-d H:i:s'));
                $this->model->add($account);
                $this->stockledger->create($this->model->max_id(), $this->branch->get_branch(), $this->period->month, $this->period->year);
              }else{ $stts = false; }
           }else{ $stts = false; }              
        }
        return $stts;
    }
    
    function download()
    {
       $this->load->helper('download');
        
       $data = file_get_contents("uploads/sample/product_sample.csv"); // Read the file's contents
       $name = 'product_sample.csv';    
       force_download($name, $data);
    }
    
    
//    ====================== kartu stock =======================================
    
    function stock_card($pid)
    {
        if ($this->acl->otentikasi1($this->title) == TRUE && $this->model->valid_add_trans($pid, $this->title) == TRUE){

        $product = $this->model->get_by_id($pid)->row();
        
        $data['log'] = $this->decodedd->log;
        $data['code'] = $product->sku;
        $data['brand'] = $this->manufacture->get_name($product->manufacture);
        $data['category'] = $this->category->get_name($product->category);
        $data['name'] = $product->name;
        $data['currency'] = $product->currency;
        $data['qty'] = $product->qty;
        $data['unit'] = $product->unit;
        
        $branch = $this->branch->get_branch_session();
        $data['open'] = $this->stockledger->get_trans($pid,$branch, $this->period->month, $this->period->year,'openqty');
        $data['trans'] = $this->wt->get_monthly($pid, $branch, $this->period->month, $this->period->year)->result();
        
        $this->output = $data;
        }else{ $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('content');
    }
    
//    ================================= ledger ==================================
    
    function ledger($pid=null)
    {
        if ($this->acl->otentikasi2($this->title) == TRUE){
        
        if ($pid == null){ $pid = $this->product->get_id_by_sku($this->input->post('titem')); }
        if ($pid){ $product = $this->model->get_by_id($pid)->row(); $pname = ': '.$product->name;}
        else { $pname = null;}
        
        $start = $this->input->post('start'); $end = $this->input->post('end');
        if (!$start && !$end){ $start = date('Y-m-01');  $end = date('Y-m-t'); }
                
        $items = null;
        foreach ($this->wt->get_transaction($pid, $this->input->post('cbranch'), $start, $end)->result() as $res) {
            $items[] = array("id"=>$res->id, "code"=>$res->code, "branch"=>$this->branch->get_name($res->branch_id), "date"=>tglin($res->dates), "debit"=>floatval($res->debit), "credit"=> floatval($res->credit), "log"=>$res->log);
        }
        $data['items'] = $items;
        
        $data['begin'] = $this->stockledger->get_prev_balance($pid, $this->input->post('cbranch'), $start, $this->period->month, $this->period->year);
        $data['debit'] = $this->wt->get_sum_qty($pid, $this->input->post('cbranch'), $start, $end, 0);
        $data['credit'] = $this->wt->get_sum_qty($pid, $this->input->post('cbranch'), $start, $end, 1);
        $data['mutation'] = $data['debit']-$data['credit'];
        $data['end'] = floatval($data['begin']+$data['mutation']);
        
        $data['graph'] = $this->chart_ledger($pid, $this->input->post('cbranch'), $start, $end);
        $this->output = $data;
        }else{ $this->reject_token('Invalid Token or Expired..!'); }
        $this->response('content');
    }
    
    private function chart_ledger($pid=null,$branch=null,$start=null,$end=null)
    {   
        if ($pid){
            
        $opening = $this->stockledger->get_prev_balance($pid, $branch, $start, $this->period->month, $this->period->year);    
//        $opening = $this->stockledger->get_trans($pid, $branch, $this->period->month, $this->period->year, 'openqty');
        $rest = 0;
        $data = $this->wt->get_transaction($pid, $branch, $start, $end)->result();
        $datax = array();
        foreach ($data as $res) 
        {  
           if ($res->debit > 0){ $rest = intval($rest+$res->debit); }
           if ($res->credit > 0){ $rest = intval($rest-$res->credit); }
           $point = array("label" => tglin($res->dates) , "y" => intval($opening+$rest));
           array_push($datax, $point);      
        }
//        echo json_encode($datax, JSON_NUMERIC_CHECK);
        return $datax;
        }
    }
    
    function calculate_balance(){  
      if ($this->acl->otentikasi2($this->title) == TRUE){ 
        if ( $this->stockledger->closing() == TRUE){ $this->error = 'Calculate Processed'; }else{ $this->reject('Failure Calculating'); }
      }else{ $this->reject('Invalid Token or Expired..!'); }
      $this->response();
    }
   
    // ====================================== CLOSING ======================================
    function reset_process(){ $this->model->closing(); $this->model->closing_trans(); } 

}

?>