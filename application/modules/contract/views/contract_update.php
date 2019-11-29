
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

<script src="<?php echo base_url(); ?>js/moduljs/shorec.js"></script>
<script src="<?php echo base_url(); ?>js-old/register.js"></script>

<script type="text/javascript">

	var sites_add  = "<?php echo site_url('shorec/add_process/');?>";
	var sites_edit = "<?php echo site_url('shorec/update_process/');?>";
	var sites_del  = "<?php echo site_url('shorec/delete/');?>";
	var sites_get  = "<?php echo site_url('shorec/update/');?>";
    
	var source = "<?php echo $source;?>";
    var url  = "<?php echo $graph;?>";
    
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
                      
                  
             <!-- Smart Wizard -->
<div id="wizard" class="form_wizard wizard_horizontal">
                     <!-- error div -->
 <div class="alert alert-success success"> </div>
 <div class="alert alert-warning warning"> </div>
 <div class="alert alert-error error"> </div>
    
    
  <ul class="wizard_steps">
    <li>
      <a href="#step-1">
        <span class="step_no">1</span>
        <span class="step_descr"> <small> General </small> </span>
      </a>
    </li>
    <li>
      <a href="#step-2">
        <span class="step_no">2</span>
        <span class="step_descr"> <small> Contract Detail </small> </span>
      </a>
    </li>
   
  </ul>
  
<!--
  <div id="errors" class="alert alert-danger alert-dismissible fade in" role="alert"> 
     <?php // $flashmessage = $this->session->flashdata('message'); ?> 
	 <?php // echo ! empty($message) ? $message : '' . ! empty($flashmessage) ? $flashmessage : ''; ?> 
  </div>
-->
  
  <div id="step-1">
    <!-- form -->
    <form id="upload_form_update" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
    action="<?php echo $form_action.'/1'; ?>" 
      enctype="multipart/form-data">
		
      <div class="form-group">  
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sku"> Contract No </label>
	  <div class="col-md-4 col-sm-6 col-xs-12">
<input type="text" class="form-control" required name="tdocno" value="<?php echo isset($default['docno']) ? $default['docno'] : '' ?>">       
<input type="hidden" name="tid" value="<?php echo $uid; ?>">
        
      </div>
      </div>    
        
      <div class="form-group">  
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sku"> Trans Date </label>
	  <div class="col-md-2 col-sm-6 col-xs-12">
        <input type="text" class="form-control" id="ds3" required name="tdates" placeholder="SKU" 
        value="<?php echo isset($default['dates']) ? $default['dates'] : '' ?>">
      </div>
      </div>
        
    <div class="form-group">  
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sku"> Ref No </label>
	  <div class="col-md-4 col-sm-6 col-xs-12">
<input type="text" class="form-control" required name="tdocno" value="<?php echo isset($default['docno']) ? $default['docno'] : '' ?>">       
<input type="hidden" name="tid" value="<?php echo $uid; ?>">
        
      </div>
      </div>  
        
      <div class="form-group">  
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sku"> Customer </label>
	  <div class="col-md-4 col-sm-6 col-xs-12">
<?php $js = "class='form-control select2_single' id='' tabindex='-1' style='width:250px;' "; 
 echo form_dropdown('ccust', $customer, isset($default['']) ? $default[''] : '', $js); ?>
        
      </div>
      </div>  
        
      <div class="form-group">  
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sku"> Period </label>
	  <div class="col-md-4 col-sm-6 col-xs-12">
<table>
    <tr> 
<td> <input type="text" title="Start Date" class="form-control" id="ds2" name="tstart" style="width:100px;" /> </td>
<td> &nbsp; - &nbsp; </td>
<td> <input type="text" title="End Date" class="form-control" id="ds3" name="tend" style="width:100px;" />  </td>
    </tr>
</table>
        
      </div>
      </div>       
        
	             
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Remarks </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
<textarea name="tnote" rows="3" class="form-control"><?php echo isset($default['note']) ? $default['note'] : '' ?></textarea>
        </div>
      </div>
                    
      
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-3 col-sm-3 col-xs-12 col-md-offset-3">
          <button type="submit" class="btn btn-primary" id="button"> Save General </button>
        </div>
      </div>
      
	</form>
    <!-- end div layer 1 -->
  </div>
    
  
  <!-- div 2 -->
  <div id="step-2">
 
      <style type="text/css">
          .border{ border: 1px solid red;}
          .dtable{ border-collapse: separate; border-spacing: 3px; width: 100%; }
          .ck{ float: left; margin: 1px; padding: 0;}
          .control-label{ font-size: 9pt;}
      </style>
      
<div class="col-md-7 col-sm-6 col-xs-12">

<table class="dtable">
    
    <tr>
        <td> <label class="control-label"> Rent Cost : </label> </td> 
        <td> <select name="ctype"> <option value=""> Kg </option> </select> </td>
        <td> <input type="text" class="form-control" name="tvessel"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
        <td> &nbsp; <label class="control-label"> Pipeline Usage Service : </label> </td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
    </tr>
    
    <tr>
        <td> <label class="control-label"> Excess Rent / Kg : </label> </td> <td></td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
        <td> &nbsp; <label class="control-label"> Overtime / Hour : </label> </td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
    </tr>
    
     <tr>
        <td> <label class="control-label"> Truck Loosing / Kg : </label> </td> <td></td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
        <td> &nbsp; <label class="control-label"> Tank Cleaning : </label> </td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
    </tr>
    
     <tr>
        <td> <label class="control-label"> Truck Usage Service / Kg : </label> </td> <td></td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
        <td> &nbsp; <label class="control-label"> OPP/OPT (/kg) : </label> </td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
    </tr>
    
    <tr>
        <td> <label class="control-label"> Inward / Kg : </label> </td> <td></td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
        <td> &nbsp; <label class="control-label"> PBM (/kg) : </label> </td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
    </tr>
    
     <tr>
        <td> <label class="control-label"> Package Handling Service : </label> </td> <td></td>
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
        <td> &nbsp; <label class="control-label"> Oil Boom : </label> </td> 
        <td> <input type="text" class="form-control" name="tshipper"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
    </tr>
    
    <tr>
        <td> <label class="control-label"> Outward / Kg : </label> </td> <td>&nbsp;</td>
        <td> <input type="text" class="form-control" name="tvessel"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
        <td> <label class="control-label">  </label> </td> <td></td>
        <td> </td>
    </tr>
    
    <tr> <td> <br> </td> <td></td> </tr>
    
     <tr>
        <td> </td> <td colspan="2"> </td>
        <td> <label class="control-label"> Pay PBM to : </label> </td>
        <td> <?php $js = "class='form-control select2_single' id='' tabindex='-1' style='width:280px;' "; 
             echo form_dropdown('cvendor', $vendor, isset($default['']) ? $default[''] : '', $js); ?> 
        </td>
    </tr>
        
</table>
    
<table class="dtable" style="margin-top:10px;">
     <tr>
        <td> <label class="control-label"> VAT (%) : </label> </td>
        <td> <input type="text" class="form-control" name="tvessel"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
         
        <td> &nbsp; <label class="control-label"> Pph 4-2 (%) : </label> </td>
        <td> <input type="text" class="form-control" name="tvessel"
           value="<?php //echo isset($default['dimension']) ? $default['dimension'] : '' ?>" /> </td>
         
        <td> &nbsp; <label class="control-label"> Currency : </label> </td>
        <td> <input type="text" class="form-control" name="tvessel"
           value="<?php echo isset($default['currency']) ? $default['currency'] : '' ?>" /> </td>
        
    </tr>
</table>

</div>      
      
<!-- div 2 kanan -->
<div class="col-md-5 col-sm-6 col-xs-12">
     
<!-- Before -->
<fieldset> <legend> Tank List </legend>
    
<?php
    
$atts1 = array(
	  'class'      => 'btn btn-primary button_inline',
	  'title'      => 'COA - List',
	  'width'      => '800',
	  'height'     => '600',
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 800)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 600)/2)+\'',
);

?>

    <form name="modul_form" class="form-inline" method="post" action="">                        
             <div class="form-group">
<input id="titem" class="form-control" type="text" readonly name="titem" required style="width:100px;">
              </div>
              
  <div class="btn-group"> <label>.</label>
      <?php echo anchor_popup(site_url("tank/get_list/"), '[ ... ]', $atts1); ?>
     <button type="submit" class="btn btn-success button_inline"> Select </button>
  </div>
    </form>
    
    <table id="example" width="100%" cellspacing="0" class="table table-striped table-bordered dataTable no-footer" role="grid" aria-describedby="example_info" style="width: 100%;">
<thead>
<tr role="row"><th class="sorting_asc" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-sort="ascending" aria-label="No: activate to sort column descending" style="width: 43px;">No</th><th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Code: activate to sort column ascending" style="width: 103px;">Code</th>
    
    <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Qty (m3): activate to sort column ascending" style="width: 100px;">Qty (kg)</th>

    </tr>
</thead>
<tbody>


<tr role="row" class="odd">
<td class="sorting_1">1</td><td>BS-01</td> <td>3000</td> </tr><tr role="row" class="even">
<td class="sorting_1">2</td><td>BS-0128</td> <td>4200</td> </tr></tbody>
</table>
 
</fieldset>    

    
</div>
      
    <!-- form -->
<!--
<form id="upload_form_update2" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
action="<?php //echo $form_action.'/2'; ?>" >
      
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-3 col-sm-3 col-xs-12 col-md-offset-3">
          <button type="submit" class="btn btn-primary" id="button"> Save Data </button>
        </div>
      </div>
      
</form>
-->
    <!-- end div layer 2 -->
     
  </div>
  <!-- div 2 -->
  
</div>
<!-- End SmartWizard Content -->
                      
     </div>
       
       <!-- links -->
       <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
       <!-- links -->
                     
    </div>
  </div>
              
   <!-- Modal - Add Form -->
  <div class="modal fade" id="myModal" role="dialog">
     <?php //$this->load->view('tank_density_form'); ?>      
  </div>
  <!-- Modal - Add Form -->
      
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
    <script src="<?php echo base_url(); ?>js/wizard/jquery.smartWizard.js"></script>
        
        <!-- jQuery Smart Wizard -->
    <script>
      $(document).ready(function() {
        $('#wizard').smartWizard();

        $('#wizard_verticle').smartWizard({
          transitionEffect: 'slide'
        });

      });
    </script>
    <!-- /jQuery Smart Wizard -->
    
    
