<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Add - Payroll Adjustment </h4>
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

<form id="upload_form_non" data-parsley-validate class="form-horizontal form-label-left" method="POST" action="<?php echo $form_action; ?>" 
      enctype="multipart/form-data">
        
     <div class="form-group">
       <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Employee </label>
      <div class="col-md-5 col-sm-6 col-xs-12">
        <table>
            <tr>
                <td> <input id="titem" class="form-control col-md-3 col-xs-12" type="text" readonly name="tnip" required> </td>
                <td> <?php echo anchor_popup(site_url("employee/get_list/"), '[ ... ]', $atts1); ?> </td>
            </tr>
        </table>  
      </div>
     </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Time Work </label>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <input type="number" class="form-control" name="ttime" size="3" maxlength="2" title="Time Work" required
             value="<?php echo set_value('ttime', isset($default['time']) ? $default['time'] : ''); ?>" /> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Amount </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <input type="number" class="form-control" name="tamount" id="" size="15" title="Amount" required
               value="<?php echo set_value('tamount', isset($default['amount']) ? $default['amount'] : '0'); ?>" />
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Consumption </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <input type="number" class="form-control" id="" name="tconsumption" size="10" title="Consumption Costs" required
        value="<?php echo set_value('tconsumption', isset($default['consumption']) ? $default['consumption'] : '0'); ?>"  /> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Transportation </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <input type="text" class="form-control" id="" name="ttransport" size="10" title="Transport Costs"  required
        value="<?php echo set_value('ttransport', isset($default['transport']) ? $default['transport'] : '0'); ?>" />
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Bonus </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
       <input type="number" class="form-control" id="" name="tbonus" size="10" title="Bonus" required
       value="<?php echo set_value('tbonus', isset($default['bonus']) ? $default['bonus'] : '0'); ?>"  /> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">  </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
      <textarea name="tbonusremarks" class="form-control" cols="45" rows="3" placeholder="Bonus Remarks"><?php echo set_value('tbonusremarks', isset($default['bonusremarks']) ? $default['bonusremarks'] : ''); ?></textarea>
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Deduction </label>
      <div class="col-md-4 col-sm-6 col-xs-12">
       <input type="text" class="form-control" id="" name="tinsurance" size="10" title="Insurance" required 
       value="<?php echo set_value('tinsurance', isset($default['insurance']) ? $default['insurance'] : '0'); ?>" /> 
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