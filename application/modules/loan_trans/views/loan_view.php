
 <!-- Datatables CSS -->
<link href="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>css/icheck/flat/green.css" rel="stylesheet" type="text/css">

<script src="<?php echo base_url(); ?>js/moduljs/loantrans.js"></script>
<script src="<?php echo base_url(); ?>js-old/register.js"></script>

<!-- Date time picker -->
 <script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 
 <!-- Include Date Range Picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />

<!-- bootstrap toogle -->
<!--<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>-->

<script type="text/javascript">

	var sites_add  = "<?php echo site_url('loan_trans/add_process/');?>";
	var sites_edit = "<?php echo site_url('loan_trans/update_process/');?>";
	var sites_del  = "<?php echo site_url('loan_trans/delete/');?>";
	var sites_ajax  = "<?php echo site_url('loan_trans/');?>";
    var sites_details  = "<?php echo site_url('loan_trans/invoice/');?>";
	var source = "<?php echo $source;?>";    
</script>

<?php
    
$atts1 = array(
	  'class'      => 'btn btn-primary button_inline',
	  'title'      => 'Employee - List',
	  'width'      => '800',
	  'height'     => '600',
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 800)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 600)/2)+\''
);

?>

          <div class="row"> 
          
            <div class="col-md-12 col-sm-12 col-xs-12">
                  
              <!--  batas xtitle 2  -->    
                
              <div class="x_panel" >
                   
              <!-- xtitle -->
              <div class="x_title">
                
               <h2> Attendance Filter </h2>
                
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
                <label> Employee </label> <br>
                      
                 <input id="titem" class="form-control" type="text" readonly name="tnip" required style="width:120px;">
                 <?php echo anchor_popup(site_url("employee/get_list/"), '[ ... ]', $atts1); ?>
              </div> 
               
               <div class="form-group">
                   <label> Dates : </label> <br>
                  <input type="text" title="Date" class="form-control" id="ds1" name="tdates" style="width:120px;" /> 
               </div>
               
                <div class="form-group">
                   <label> Type : </label> <br>
                  <select name="ctype" id="ctype" class="select2_single form-control" style="width:200px">
                    <option value="borrow"> Borrow </option>
                    <option value="paid"> Paid </option>
                 </select>
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
                  
                  <div class="table-responsive">
                    <?php echo ! empty($table) ? $table : ''; ?>            
                  </div>
                  
                  <!-- Check All Function -->
                  
          </form>       
             </div>
               
               <div class="btn-group">  
               <!-- Trigger the modal with a button --> 
<button type="button" onClick="resets();" class="btn btn-primary" data-toggle="modal" data-target="#myModal"> 
    <i class="fa fa-plus"></i>&nbsp;Add New 
</button>
                   
<button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal2"> Report </button>
               
               <!-- links -->
	           <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
               <!-- links -->
               </div>
                             
            </div>     
                
          </div>  
          
      <!-- Modal - Report Form -->
      <div class="modal fade" id="myModal" role="dialog">
         <?php $this->load->view('loan_form'); ?>    
      </div>
      <!-- Modal - Report Form -->
              
       <!-- Modal - Import Form -->
      <div class="modal fade" id="myModal2" role="dialog">
         <?php $this->load->view('loan_report_panel'); ?>    
      </div>
      <!-- Modal - Import Form -->
              
      <!-- Modal - Report Form -->
      <div class="modal fade" id="myModal3" role="dialog">
         <?php //$this->load->view('attendance_import'); ?>    
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
