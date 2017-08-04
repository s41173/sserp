<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Payroll - Report </h4>
</div>
<div class="modal-body">
 
 <!-- form add -->
<div class="x_panel" >
<div class="x_title">
  
  <div class="clearfix"></div> 
</div>
<div class="x_content">   
    
<form id="" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
action="<?php echo $form_action_report; ?>" enctype="multipart/form-data">
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Currency </label>
      <div class="col-md-5 col-sm-4 col-xs-12">
          <select name="ccurrency" class="form-control">
            <option value="IDR">IDR</option>
            <option value="Dollar">Dollar</option>
          </select>
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Period </label>
      <div class="col-md-5 col-sm-4 col-xs-12">
          <input type="text" name="tyear" id="" class="form-control" maxlength="4" style="width:80px;" value="<?php echo date('Y'); ?>"> 
      </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
          <div class="btn-group"> 
           <button type="submit" class="btn btn-primary">Post</button>
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