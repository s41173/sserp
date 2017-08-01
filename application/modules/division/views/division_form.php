<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Add Division </h4>
</div>
<div class="modal-body">
 
 <!-- error div -->
 <div class="alert alert-success success"> </div>
 <div class="alert alert-warning warning"> </div>
 <div class="alert alert-error error"> </div>
 
 <!-- form add -->
<div class="x_panel" >
<div class="x_title">
  
  <div class="clearfix"></div> 
</div>
<div class="x_content">

<form id="upload_form_non" data-parsley-validate class="form-horizontal form-label-left" method="POST" action="<?php echo $form_action; ?>" 
      enctype="multipart/form-data">
     
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Name </label>
      <div class="col-md-5 col-sm-5 col-xs-12">
        <input id="tname" class="form-control col-md-1 col-xs-12" type="text" name="tname" required placeholder="Division Name">
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Role </label>
      <div class="col-md-4 col-sm-4 col-xs-12">
        <select name="crole" class="form-control"> 
<option value="honor"<?php echo set_select('crole', 'honor', isset($default['role']) && $default['role'] == 'honor' ? TRUE : FALSE); ?>> Honor 
</option>
<option value="staff"<?php echo set_select('crole', 'staff', isset($default['role']) && $default['role'] == 'staff' ? TRUE : FALSE); ?>> Staff 
</option>
<option value="officer"<?php echo set_select('crole', 'officer', isset($default['role']) && $default['role'] == 'officer' ? TRUE : FALSE); ?>> Officer </option>
<option value="manager"<?php echo set_select('crole', 'manager', isset($default['role']) && $default['role'] == 'manager' ? TRUE : FALSE); ?>> 
Manager </option>
<option value="director"<?php echo set_select('crole', 'director', isset($default['role']) && $default['role'] == 'director' ? TRUE : FALSE); ?>> 
Director </option>
             </select> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Basic Salary </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input id="tbasic" class="form-control col-md-12 col-xs-12" type="number" name="tbasic" required value="0">
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Comsuption / Meal </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input id="tconsumption" class="form-control col-md-12 col-xs-12" type="number" name="tconsumption" required value="0">
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Transportation </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input id="ttransport" class="form-control col-md-12 col-xs-12" type="number" name="ttransport" required value="0">
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Overtime </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input id="tovertime" class="form-control col-md-12 col-xs-12" type="number" name="tovertime" required value="0">
      </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 btn-group">
          <button type="submit" class="btn btn-primary" id="button">Save</button>
          <button type="button" id="bclose" class="btn btn-danger" data-dismiss="modal">Close</button>
          <button type="button" id="breset" class="btn btn-warning">Reset</button>
          </div>
      </div>
  </form> 

</div>
</div>
<!-- form add -->

</div>
    <div class="modal-footer"> </div>
</div>
  
</div>