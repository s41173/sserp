
 <!-- Datatables CSS -->
<link href="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>css/icheck/flat/green.css" rel="stylesheet" type="text/css">

<script src="<?php echo base_url(); ?>js/moduljs/payroll.js"></script>
<script src="<?php echo base_url(); ?>js-old/register.js"></script>

<!-- Date time picker -->
 <script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!--canvas js-->
<script type="text/javascript" src="<?php echo base_url().'js-old/' ?>canvasjs.min.js"></script>
 
 <!-- Include Date Range Picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />


<script type="text/javascript">

	var sites_add  = "<?php echo site_url('payroll/add_process/');?>";
	var sites_edit = "<?php echo site_url('payroll/update_process/');?>";
	var sites_del  = "<?php echo site_url('payroll/delete/');?>";
	var sites_get  = "<?php echo site_url('payroll/update/');?>";
    var sites_ajax  = "<?php echo site_url('payroll/');?>";
    var sites_details  = "<?php echo site_url('payroll_trans/get/');?>";
	var source = "<?php echo $source;?>";
    
    var url  = "<?php echo $graph;?>";
	
    $(document).ready(function (e) {
    
     //chart render
	
	$.getJSON(url, function (result) {
		
		var chart = new CanvasJS.Chart("chartcontainer", {

			theme: "theme1",//theme1
			axisY:{title: "", },
  		    animationEnabled: true, 
			data: [
				{
					type: "column",
					dataPoints: result
				}
			]
		});

		chart.render();
	});
	
	//chart render
        
    // document ready end	
    });
	
</script>

          <div class="row"> 
          
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel" >
              
              <!-- xtitle -->
              <div class="x_title">
                
               <h2> Payroll Filter </h2>
                
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
          <label> Period </label> <br>

          <select name="cmonth" id="cmonth" class="form-control">
            <option value="" selected="selected"> -- </option>
            <option value="1"> January </option>
            <option value="2"> February </option>
            <option value="3"> March </option>
            <option value="4"> April </option>
            <option value="5"> May </option>
            <option value="6"> June </option>
            <option value="7"> July </option>
            <option value="8"> August </option>
            <option value="9"> September </option>
            <option value="10"> October </option>
            <option value="11"> November </option>
            <option value="12"> December </option>
         </select> - 
         <input type="text" name="tyear" id="tyear" class="form-control" maxlength="4" style="width:80px;" value="<?php echo date('Y'); ?>"> &nbsp;
      </div> 
              
              <div class="btn-group"> 
                <label></label> <br>  
               <button type="submit" class="btn btn-primary button_inline"> Filter </button>
               <button type="reset" onClick="" class="btn btn-success button_inline"> Clear </button>
               <button type="button" onClick="load_data();" class="btn btn-danger button_inline"> Reset </button>
              </div>
          </form> <br>
           
           <!-- searching form -->
           
              
          <form class="form-inline" id="cekallform" method="post" action="<?php echo ! empty($form_action_del) ? $form_action_del : ''; ?>">
                  <!-- table -->
                  
                  <?php echo ! empty($table) ? $table : ''; ?>            
                  
          </form>       
             </div>

               <!-- Trigger the modal with a button --> 
   <div class="btn-group">
    <button type="button" class="btn btn-primary" onclick="reset();" data-toggle="modal" data-target="#myModal"> <i class="fa fa-plus"></i>&nbsp;Add New </button>
   <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal3"> Report  </button>

   <!-- links -->
   <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
   <!-- links -->
   </div>
                             
            </div>
                
            <div class="x_panel">

           <!-- xtitle -->
              <div class="x_title">
               <h2> Payroll Chart </h2>

                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a> </li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a> </li>
                </ul>

                <div class="clearfix"></div>
              </div>
              <!-- xtitle -->

            <div class="x_content">
                <div id="chartcontainer" style="height:250px; width:100%;"></div>
            </div>    
                    
              </div>        
                
          </div>  
    
      <!-- Modal - Add Form -->
      <div class="modal fade" id="myModal" role="dialog">
         <?php $this->load->view('payroll_form'); ?>      
      </div>
      <!-- Modal - Add Form -->
              
       <!-- Modal - Add Form -->
      <div class="modal fade" id="myModal2" role="dialog">
         <?php $this->load->view('payroll_update'); ?>      
      </div>
      <!-- Modal - Add Form -->
      
      
      <!-- Modal - Report Form -->
      <div class="modal fade" id="myModal3" role="dialog">
         <?php $this->load->view('payroll_report_panel'); ?>    
      </div>
      <!-- Modal - Report Form -->
              
      <!-- Modal - Print Form -->
      <div class="modal fade" id="myModal4" role="dialog">
         <?php $this->load->view('payroll_invoice_form'); ?>    
      </div>
      <!-- Modal - Print Form -->
      
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
