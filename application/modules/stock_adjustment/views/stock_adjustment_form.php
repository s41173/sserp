 <!-- Datatables CSS -->
<link href="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>css/icheck/flat/green.css" rel="stylesheet" type="text/css">

<!-- Date time picker -->
 <script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 
 <!-- Include Date Range Picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />


<style type="text/css">
  a:hover { text-decoration:none;}
</style>

<script src="<?php echo base_url(); ?>js/moduljs/stock_adjustment.js"></script>
<script src="<?php echo base_url(); ?>js-old/register.js"></script>

<script type="text/javascript">

	var sites_add  = "<?php echo site_url('stock_adjustment/add_process/');?>";
	var sites_edit = "<?php echo site_url('stock_adjustment/update_process/');?>";
	var sites_del  = "<?php echo site_url('stock_adjustment/delete/');?>";
	var sites_get  = "<?php echo site_url('stock_adjustment/update/');?>";
    var sites  = "<?php echo site_url('stock_adjustment/');?>";
	var source = "<?php echo $source;?>";
	
</script>

          <div class="row"> 
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel" >
              
              <!-- xtitle -->
              <div class="x_title">
              
                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a> </li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a> </li>
                </ul>
                
                <div class="clearfix"></div>
              </div>
              <!-- xtitle -->
                
                <div class="x_content">
                    
<?php
    
$atts1 = array(
	  'class'      => 'btn btn-primary button_inline',
	  'title'      => 'COA - List',
	  'width'      => '600',
	  'height'     => '400',
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 600)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 400)/2)+\'',
);
                    
$atts2 = array(
	  'class'      => 'btn btn-primary button_inline',
	  'title'      => 'Product',
	  'width'      => '800',
	  'height'     => '600',
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 800)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 600)/2)+\'',
);

?>
  
  <div id="step-1">
    <!-- form -->
    <form id="journalformdata" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
    action="<?php echo $form_action; ?>" >
		
    <style type="text/css">
       .xborder{ border: 1px solid red;}
       #custtitlebox{ height: 90px; background-color: #E0F7FF; border-top: 3px solid #2A3F54; margin-bottom: 10px; }
        #amt{ color: #000; margin-top: 35px; text-align: right; font-weight: bold;}
        #amt span{ color: blue;}
        .labelx{ font-weight: bold; color: #000;}
        #table_summary{ font-size: 12px; color: #000;}
        .amt{ text-align: right;}
    </style>

<!-- form atas   -->
    <div class="row">
       
<!-- div untuk customer place  -->
       <div id="custtitlebox" class="col-md-12 col-sm-12 col-xs-12">
            
           <div class="form-group">
               
               <div class="col-md-1 col-sm-12 col-xs-12">
                   <label class="control-label labelx"> No </label> <br>
     <input type="number" name="tno" id="tno" class="form-control" style="width:80px;" value="<?php echo $code; ?>">  
     <input type="hidden" name="tid" value="<?php echo $pid; ?>">
               </div>
               
               <div class="col-md-2 col-sm-12 col-xs-12">
                   <label class="control-label labelx"> Currency </label> <br>
         <?php  $js = "class='form-control' id='ccurrency' tabindex='-1' style='width:150px; float:left; margin-right:10px;' ";
	     echo form_dropdown('ccurrency', $currency, isset($default['currency']) ? $default['currency'] : '', $js); ?>
               </div>
               
               <div class="col-md-2 col-sm-12 col-xs-12">
                   <label class="control-label labelx"> Transaction Date </label>
           <input type="text" title="Date" class="form-control" id="ds1" name="tdate" required
           value="<?php echo isset($default['dates']) ? $default['dates'] : '' ?>" /> 
               </div>
               
               <div class="col-md-4 col-sm-12 col-xs-12">
                   <label class="control-label labelx"> Branch / Outlet </label>
<?php $js = "class='form-control' id='cbranch' tabindex='-1' style='min-width:150px;' "; 
echo form_dropdown('cbranch', $branch, isset($default['branch']) ? $default['branch'] : '', $js); ?>
               </div>
               
           </div>
           
       </div>
<!-- div untuk account place  -->
        
 <div class="col-md-4 col-sm-12 col-xs-12">
                   <label class="control-label labelx"> Note </label>
<textarea id="" name="tnote" style="width:100%;" rows="3"><?php echo isset($default['note']) ? $default['note'] : '' ?></textarea>
               </div>


<!-- div staff -->
    <div class="col-md-4 col-sm-12 col-xs-12">
       
       <div class="col-md-12 col-sm-12 col-xs-12">
          <label class="control-label labelx"> Operational Staff </label>
          <input type="text" name="tstaff" class="form-control" placeholder="Operational Staff" value="<?php echo isset($default['staff']) ? $default['staff'] : '' ?>">
       </div> 
        
    </div>
<!-- div staff -->

</div>
<!-- form atas   -->
      
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-4 col-sm-3 col-xs-12 col-md-offset-9">
          <div class="btn-group">    
          <button type="submit" class="btn btn-success" id="button"> Save </button>
          <button type="reset" class="btn btn-danger" id=""> Cancel </button>
          <a class="btn btn-primary" href="<?php echo site_url('cashin/add/'); ?>"> New Transaction </a>
          </div>
        </div>
      </div>
      
	</form>
      
    <!-- end div layer 1 -->
      
<!-- form transaction table  -->
      
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
    
 <!-- searching form -->
           
   <form id="ajaxtransform" class="form-inline" method="post" action="<?php echo $form_action_item; ?>">
      
       <div class="form-group">
        <label class="control-label labelx"> Product </label> <br>
           <input id="titems" class="form-control col-md-3 col-xs-12" type="text" readonly name="tproduct" required style="width:150px;">
          <?php echo anchor_popup(site_url("product/get_list/titems"), '[ ... ]', $atts2); ?>
           <input type="hidden" name="tbranchid" value="<?php echo isset($default['branch']) ? $default['branch'] : '' ?>">
          &nbsp;
      </div>
       
      <div class="form-group">
        <label class="control-label labelx"> Type </label> <br>
        <select name="ctype" class="form-control">
            <option value="in"> IN </option>
            <option value="out"> OUT </option>
        </select> &nbsp;
      </div>
       
      <div class="form-group">
        <label class="control-label labelx"> Qty </label> <br>
        <input type="number" name="tqty" id="tqty" class="form-control" style="width:90px;" maxlength="5" required value="0"> &nbsp;
      </div>   
       
      <div class="form-group">
        <label class="control-label labelx"> Amount </label> <br>
        <input type="number" name="tamount" id="tamount" class="form-control" style="width:130px;" maxlength="8" required value="0"> &nbsp;
      </div>      
       
      <div class="form-group">
        <label class="control-label labelx"> Account </label> <br>
           <input id="titem" class="form-control col-md-2 col-xs-12" type="text" readonly name="titem" required style="width:100px;">
          <?php echo anchor_popup(site_url("account/get_list/"), '[ ... ]', $atts1); ?>
          &nbsp;
      </div>   

      <div class="form-group"> <br>
       <div class="btn-group">          
       <button type="submit" class="btn btn-primary button_inline"> Post </button>
       <button type="reset" onClick="" class="btn btn-danger button_inline"> Reset </button>
       </div>
      </div>
       
  </form> <br>


   <!-- searching form --> 
        
    </div>
    
<!-- table -->
  <div class="col-md-12 col-sm-12 col-xs-12">  
    <div class="table-responsive">
      <table class="table table-striped jambo_table bulk_action">
        <thead>
          <tr class="headings">
            <th class="column-title"> No </th>
            <th class="column-title"> Product </th>
            <th class="column-title"> Type </th>
            <th class="column-title"> Qty </th>
            <th class="column-title"> Balance </th>
            <th class="column-title"> Account </th>
            <th class="column-title no-link last"><span class="nobr">Action</span>
            </th>
            <th class="bulk-actions" colspan="7">
              <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
            </th>
          </tr>
        </thead>

        <tbody>
            
        <?php
            
            function account($val)
            {
                $acc = new Account_lib();
                return $acc->get_name($val);
            }
            
            function product($val)
            {
                $acc = new Product_lib();
                return $acc->get_name($val);
            }
            
            function unit($val)
            {
                $acc = new Product_lib();
                return $acc->get_unit($val);
            }
            
            if ($items)
            {
                $i=1;
                foreach($items as $res)
                {
                    echo "
                     <tr class=\"even pointer\">
                        <td> ".$i." </td>
                        <td>".product($res->product_id)." </td>
                        <td>".strtoupper($res->type)." </td>
                        <td>".$res->qty.''.unit($res->product_id)." </td>
                        <td class=\"a-right a-right \"> ".idr_format($res->price)." </td>
                        <td>".account($res->account)." </td>
<td class=\" last\"> 
<a class=\"btn btn-danger btn-xs text-remove\" id=\"".$res->id."\"> <i class=\"fa fas-2x fa-trash\"> </i> </a> 
</td>
                      </tr>
                    "; $i++;
                }
            }
            
        ?> 

        </tbody>
      </table>
    </div>
    </div>
<!-- table -->
    
</div>
<!-- form transaction table  -->  
        
  </div>
                  
     </div>
       
       <div class="btn-group">
         <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#myModal4"> CSV-Import  </button>
         <!-- links -->
         <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
         <!-- links -->
       </div>
                  
      <!-- Modal - Import Form -->
      <div class="modal fade" id="myModal4" role="dialog">
        <?php $this->load->view('stock_adjustment_import'); ?>    
      </div>
      <!-- Modal - Import Form -->
                     
    </div>
  </div>
      
      <script src="<?php echo base_url(); ?>js/icheck/icheck.min.js"></script>
      
       <!-- Datatables JS -->
        <script src="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.bootstrap.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/jszip.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/pdfmake.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/vfs_fonts.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.fixedHeader.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.keyTable.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.responsive.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.scroller.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.js"></script>
    
    <!-- jQuery Smart Wizard -->
    <script type="text/javascript" src="<?php echo base_url(); ?>js/wizard/jquery.smartWizard.js"></script>
        
        <!-- jQuery Smart Wizard -->
    <script type="text/javascript">
      $(document).ready(function() {
        $('#wizard').smartWizard();

        $('#wizard_verticle').smartWizard({
          transitionEffect: 'slide'
        });

      });
    </script>
    <!-- /jQuery Smart Wizard -->
    
    
