
 <!-- Datatables CSS -->
<link href="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />

<link href="<?php echo base_url(); ?>js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />

<link href="<?php echo base_url(); ?>css/icheck/flat/green.css" rel="stylesheet" type="text/css">

<script src="<?php echo base_url(); ?>js/moduljs/log.js"></script>
<script src="<?php echo base_url(); ?>js-old/register.js"></script>

 <!-- Date time picker -->
 <script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 
 <!-- Include Date Range Picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />

<script type="text/javascript">

	var sites_add  = "<?php echo site_url('log/add_process/');?>";
	var sites_edit = "<?php echo site_url('log/update_process/');?>";
	var sites_del  = "<?php echo site_url('log/delete/');?>";
	var sites_invoice  = "<?php echo site_url('log/invoice/');?>";
	var source = "<?php echo $source;?>";
	
</script>

          <div class="row"> 
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel" >
              
              <!-- xtitle -->
              <div class="x_title">
                 <h2> Filter </h2>
                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a> </li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a> </li>
                </ul>
                
                <div class="clearfix"></div>
              </div>
              <!-- xtitle -->
                
                <div class="x_content">
              
           <!-- searching form -->
           <form id="searchform" class="form-inline">
              
       <div class="form-group">
           <label> Log-ID : </label> <br>
          <input type="number" class="form-control" id="tlog_search" name="tlog" style="width:110px;" /> 
       </div>   
               
               <div class="form-group">
                <label> Username </label> <br>
         <?php $js = "class='form-control select2_single' id='cuser_search' tabindex='-1' style='width:160px;' "; 
         echo form_dropdown('cuser', $user, isset($default['']) ? $default[''] : '', $js); ?>
              </div>  
               
               <div class="form-group">
                <label> Activity </label> <br>
         <?php $js = "class='form-control select2_single' id='cactivity_search' tabindex='-1' style='width:150px;' "; 
         echo form_dropdown('cactivity', $activity, isset($default['']) ? $default[''] : '', $js); ?>
              </div>  
               
               <div class="form-group">
                <label> Component </label> <br>
         <?php $js = "class='form-control select2_single' id='cmodul_search' tabindex='-1' style='width:150px;' "; 
         echo form_dropdown('cmodul', $modul, isset($default['']) ? $default[''] : '', $js); ?>
              </div>  
                        
               
       <div class="form-group">
           <label> Dates : </label> <br>
          <input type="text" title="Date" class="form-control" id="ds1" name="tdates" style="width:100px;" /> 
       </div>   
              
              <div class="btn-group"> <br>
               <button type="submit" class="btn btn-primary button_inline"> Filter </button>
               <button type="reset" onClick="" class="btn btn-success button_inline"> Clear </button>
               <button type="button" onClick="load_data();" class="btn btn-danger button_inline"> Reset </button>
              </div>
          </form> <br>

           
        <!-- searching form -->
                    
          <form class="form-inline" id="cekallform" method="post" action="<?php echo ! empty($form_action_del) ? $form_action_del : ''; ?>">
                  <!-- table -->
                  
                  <?php echo ! empty($table) ? $table : ''; ?>            
                  
                  <div class="form-group" id="chkbox">
                    Check All : 
                    <button type="submit" id="cekallbutton" class="btn btn-danger btn-xs">
                       <span class="glyphicon glyphicon-trash"></span>
                    </button>
                  </div>
                  <!-- Check All Function -->
                  
          </form>       
             </div>

               <!-- Trigger the modal with a button --> 
   <!--<button type="button" onClick="resets();" class="btn btn-primary" data-toggle="modal" data-target="#myModal"> <i class="fa fa-plus"></i>&nbsp;Add New </button>-->
               
               <!-- links -->
	           <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
               <!-- links -->
               
               <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal3"> Report  </button>
                             
            </div>
          </div>
      
    
      <!-- Modal - Add Form -->
      <div class="modal fade" id="myModal" role="dialog">
         <?php //$this->load->view('admin_form'); ?>      
      </div>
      <!-- Modal - Add Form -->
      
      <!-- Modal Edit Form -->
      <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	     <?php //$this->load->view('admin_update'); ?> 
      </div>
      <!-- Modal Edit Form -->
      
      <!-- Modal - Report Form -->
      <div class="modal fade" id="myModal3" role="dialog">
         <?php $this->load->view('log_report_panel'); ?>    
      </div>
      <!-- Modal - Report Form -->
      
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