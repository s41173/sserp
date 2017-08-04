
 <!-- Datatables CSS -->
<link href="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>css/icheck/flat/green.css" rel="stylesheet" type="text/css">

<script src="<?php echo base_url(); ?>js/moduljs/attendance_details.js"></script>
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

	var sites_add  = "<?php echo site_url('attendance/add_process/');?>";
	var sites_edit = "<?php echo site_url('attendance/update_process/');?>";
	var sites_del  = "<?php echo site_url('attendance/delete/');?>";
    var sites_del_attendance  = "<?php echo site_url('attendance/delete_attendance/');?>";
	var sites_get  = "<?php echo site_url('attendance/update/');?>";
    var sites_details  = "<?php echo site_url('attendance/details/');?>";
	var source = "<?php echo $source;?>";    
</script>

<?php
    
$atts1 = array(
	  'class'      => 'btn btn-primary button_inline',
	  'title'      => 'Attendance Report - List',
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
                
                <div class="x_content"> 
              
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
               
               <!-- links -->
	           <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
               <!-- links -->
               </div>
                             
            </div>     
                
          </div>  
          
      <!-- Modal - Report Form -->
      <div class="modal fade" id="myModal" role="dialog">
         <?php $this->load->view('attendance_form'); ?>    
      </div>
      <!-- Modal - Report Form -->
              
       <!-- Modal - Import Form -->
      <div class="modal fade" id="myModal2" role="dialog">
         <?php $this->load->view('attendance_report_panel'); ?>    
      </div>
      <!-- Modal - Import Form -->
              
      <!-- Modal - Report Form -->
      <div class="modal fade" id="myModal4" role="dialog">
         <?php $this->load->view('attendance_update'); ?>    
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
