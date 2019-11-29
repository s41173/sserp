
 <!-- Datatables CSS -->
<link href="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>css/icheck/flat/green.css" rel="stylesheet" type="text/css">

<script src="<?php echo base_url(); ?>js/moduljs/tank_ledger.js"></script>
<script src="<?php echo base_url(); ?>js-old/register.js"></script>

<!--canvas js-->
<script type="text/javascript" src="<?php echo base_url().'js-old/' ?>canvasjs.min.js"></script>

<!-- Date time picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 
 <!-- Include Date Range Picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />

<style type="text/css">
    .normal_p{ text-decoration: line-through; margin: 0;}
    .discount_p { color: red;}
</style>

<!-- bootstrap toogle -->
<!--<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>-->

<script type="text/javascript">
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
                   
              <div class="x_panel">
                  
                  <!-- searching form -->
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
                  
<form id="xsearchform" class="form-inline" method="post" action="<?php echo $form_action; ?>">
    
              <div class="form-group">
                <label> Tank Storage </label> <br>  
                <input id="titem" class="form-control" type="text" readonly name="titem" required style="width:120px;">
                <?php echo anchor_popup(site_url("tank/get_list/"), '[ ... ]', $atts1); ?>
              </div>
               
              <div class="form-group">
              <label> Period </label> <br>
<input type="text" readonly style="width: 200px" name="reservation" id="d1" class="form-control active" value=""> 
              </div>
              
              <div class="form-group">
               <label>.</label> <br>
               <div class="btn-group">      
                <button type="submit" class="btn btn-primary button_inline" name="bsubmit" value="filter"> Filter </button>
                <button type="submit" class="btn btn-success button_inline" name="bsubmit" value="card"> Card </button>
                <a class="btn btn-danger button_inline" href="<?php echo site_url('tank/ledger'); ?>"> Reset </a>
               </div>
              </div>
          </form> <br>

           
           <!-- searching form -->
                    
                   <!-- xtitle -->
                      <div class="x_title">
                       <h2> Chart </h2>

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
                  
              <!--  batas xtitle 2  -->    
                
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
                       
             <!-- table -->
            <div class="table-responsive"> <?php echo ! empty($table) ? $table : ''; ?> </div>
<!-- table attribute -->    
                
            <!-- table attribute -->
<style type="text/css">
    .nilai{ font-weight: bold; color: darkred;}
</style>
                    
          <table style="float:left; margin-right:55px; margin-bottom:20px;">
<tr> 
    <td> <label> Beginning : &nbsp; </label> </td> 
    <td> <label class="nilai"> <?php echo number_format($begin,2); ?> </label> </td> 
</tr>
<tr> 
    <td> <label> End : &nbsp; </label> </td> 
    <td> <label class="nilai"> <?php echo number_format($end,2); ?> </label> </td> 
</tr>
          </table>

          <table style="float:left; margin-right:55px;">
<tr> 
    <td> <label> Debit : &nbsp; </label> </td> 
    <td> <label class="nilai"> <?php echo number_format($debit,2); ?> </label> </td> 
</tr>
<tr> 
    <td> <label> Credit : &nbsp; </label> </td> 
    <td> <label class="nilai"> <?php echo number_format($credit,2); ?> </label> </td> 
</tr>
          </table>
                    
          <table style="float:left;"> 
<tr> 
    <td> <label> Mutation : &nbsp; </label> </td> 
    <td> <label class="nilai"> <?php echo number_format($mutation,2); ?> </label> </td> 
</tr>
          </table> <div class="clear"></div>
<!-- table attribute -->      
                
                
             </div>
               
               <!-- links -->
	           <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
               <!-- links -->
               
               <a href="<?php echo site_url('tank/calculate_balance/page'); ?>" class="btn btn-warning">
                   Calculate Ending Balance
               </a>
                             
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
