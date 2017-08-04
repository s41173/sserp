<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Purchase - Report </h4>
</div>
<div class="modal-body">
 
 <!-- form add -->
<div class="x_panel" >
<div class="x_title">
  
  <div class="clearfix"></div>
</div>
<div class="x_content">

<form id="" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
action="<?php echo $form_action_export; ?>" enctype="multipart/form-data">
    
     <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Division </label>
        <div class="col-md-6 col-sm-9 col-xs-12">     
<?php $js = 'id="cdivision" class="form-control"';
echo form_dropdown('cdivision', $division, isset($default['division']) ? $default['division'] : '', $js); ?>
        </div>
    </div>    
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Payment Type </label>
        <div class="col-md-4 col-sm-9 col-xs-12">     
<select name="cpayment" id="cpayment" class="form-control">
    <option value="transfer"> Transfer </option>
    <option value="cash"> Cash </option>
 </select>
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
    <div class="modal-footer"> </div>
</div>
  
</div>