<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Edit - Attendance </h4>
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
    
<form id="edit_form_non" data-parsley-validate class="form-horizontal form-label-left" method="POST" action="<?php echo $form_action_update; ?>" enctype="multipart/form-data">
     
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Employee </label>
      <div class="col-md-5 col-sm-5 col-xs-12">
          <input id="temployee" class="form-control" type="text" readonly name="tnip" required style="width:200px;">
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Period </label>
      <div class="col-md-5 col-sm-4 col-xs-12">
    <table>
     <tr> <td> <input id="tmonth" name="tmonth" class="form-control" type="text" readonly required style="width:120px;"> </td>
     <td> <input type="text" name="tyear" id="tyear_update" class="form-control" maxlength="4" style="width:80px;" value="<?php echo date('Y'); ?>"> </td>
     </tr> 
     </table>
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Presence </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input id="tpresence" class="form-control col-md-12 col-xs-12" type="number" name="tpresence" required value="0">
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Late </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input id="tlate" class="form-control col-md-12 col-xs-12" type="number" name="tlate" required value="0">
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
          <button type="button" id="breset_add" class="btn btn-warning">Reset</button>
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