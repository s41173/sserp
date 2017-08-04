<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Update - Payroll Adjustment </h4>
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
	  'title'      => 'Employee - List',
	  'width'      => '800',
	  'height'     => '600',
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 800)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 600)/2)+\'',
);

?>

<form id="edit_form_non" data-parsley-validate class="form-horizontal form-label-left" method="POST" action="<?php echo $form_action_update; ?>" 
      enctype="multipart/form-data">
        
     <div class="form-group">
       <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Employee </label>
      <div class="col-md-5 col-sm-6 col-xs-12">
        <table>
            <tr>
         <td> <input id="titem_update" class="form-control col-md-3 col-xs-12" type="text" readonly name="tnip" required> </td>
            </tr>
        </table>  
      </div>
     </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Time Work </label>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <input type="number" class="form-control" id="ttime" name="ttime" size="3" maxlength="2" title="Time Work" required /> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Amount </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <input type="number" class="form-control" name="tamount" id="tamount" size="15" title="Amount" required />
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Consumption </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
<input type="number" class="form-control" id="tconsumption" name="tconsumption" size="10" title="Consumption Costs" required /> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Transportation </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <input type="number" class="form-control" id="ttransport" name="ttransport" size="10" title="Transport Costs"  required />
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Bonus </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
       <input type="number" class="form-control" id="tbonus" name="tbonus" size="10" title="Bonus" required value=""  /> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">  </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
      <textarea name="tbonusremarks" id="tbonusremarks" class="form-control" cols="45" rows="3" placeholder="Bonus Remarks"></textarea>
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Deduction </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
       <input type="number" class="form-control" id="tinsurance" name="tinsurance" size="10" title="Insurance" required /> 
      </div>
    </div>


      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 btn-group">
          <button type="submit" class="btn btn-primary" id="button">Save</button>
          <button type="button" id="bclose" class="btn btn-danger" data-dismiss="modal">Close</button>
          <button type="button" id="breset_update" class="btn btn-warning">Reset</button>
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