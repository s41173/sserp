<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Employee Report </h4>
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
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Division </label>
        <div class="col-md-6 col-sm-9 col-xs-12">
          <?php $js = "class='select2_single form-control' id='ccategory' tabindex='-1' style='width:100%;' "; 
           echo form_dropdown('cdivision', $division, isset($default['division']) ? $default['division'] : '', $js); ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Role </label>
        <div class="col-md-4 col-sm-6 col-xs-12">
         <select class="form-control" name="crole">
         <option value=""> -- </option>
         <option value="honor"> Honor </option>
         <option value="staff"> Staff </option>
         <option value="officer"> Officer </option>
         <option value="manager"> Manager </option>
         <option value="director"> Director </option>
         </select>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Status </label>
        <div class="col-md-4 col-sm-6 col-xs-12">
          <select name="cstatus" class="form-control">
             <option value="0"> Active </option>
             <option value="1"> Joined </option>
             <option value="2"> Resign </option>
          </select> 
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Period </label>
        <div class="col-md-9 col-sm-9 col-xs-12">     
<input type="text" readonly style="width: 200px" name="reservation" id="d1" class="form-control active" value=""> 
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Type </label>
        <div class="col-md-3 col-sm-9 col-xs-12">     
			<select name="cptype" class="form-control">
              <option value="0"> Summary </option>
              <option value="1"> Pivottable </option>
            </select>
        </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 btn-group">
          <button type="submit" class="btn btn-primary">Post</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
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