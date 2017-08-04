<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Payroll - Invoice Form </h4>
</div>
<div class="modal-body">
 
 <!-- form add -->
<div class="x_panel" >
<div class="x_title">
  
  <div class="clearfix"></div> 
</div>
<div class="x_content">   
    
<?php
    
$atts1 = array(
	  'class'      => 'btn btn-primary',
	  'title'      => 'Attendance Report - List',
	  'width'      => '800',
	  'height'     => '600',
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes'
);

?>
    
<form id="" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
action="<?php ?>" enctype="multipart/form-data">
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Invoice Type </label>
      <div class="col-md-5 col-sm-4 col-xs-12">
          <?php echo anchor_popup(site_url("payroll/salary_invoice"), 'Salary Invoice', $atts1); ?>
          <?php echo anchor_popup(site_url("payroll/finance_invoice"), 'Finance Invoice', $atts1); ?>
      </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
          <div class="btn-group"> 
           <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          </div>
          </div>
      </div>
  </form> 
  <div id="err"></div>
</div>
</div>
<!-- form add -->

</div>
    <div class="modal-footer">
      
    </div>
  </div>
  
</div>