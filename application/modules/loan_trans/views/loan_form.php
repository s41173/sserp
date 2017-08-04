<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Add Loan Transaction </h4>
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
    
<form id="upload_form_non" data-parsley-validate class="form-horizontal form-label-left" method="POST" action="<?php echo $form_action; ?>" 
      enctype="multipart/form-data">
     
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Employee </label>
      <div class="col-md-5 col-sm-5 col-xs-12">
         <table>
         <tr> <td> <input id="titem1" class="form-control" type="text" readonly name="tnip" required style="width:120px;"> </td>
         <td> <?php echo anchor_popup(site_url("employee/get_list/titem1"), '[ ... ]', $atts1); ?> </td>
         </tr> 
         </table>
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Date </label>
      <div class="col-md-3 col-sm-4 col-xs-12">
           <input type="text" title="Date" class="form-control" id="ds1" name="tdate" required
           value="<?php echo isset($default['dates']) ? $default['dates'] : '' ?>" /> 
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Currency </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <?php $js = 'id="ccur" class="form-control"';
        echo form_dropdown('ccur', $currency, isset($default['currency']) ? $default['currency'] : '', $js); ?>
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Account </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <select name="cacc" class="form-control">
	       <option value="bank"> Bank </option>
	       <option value="cash"> Cash </option> 
        </select>
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Type </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <select class="form-control" name="ctype">
             <option value="borrow"> Borrow </option>
             <option value="paid"> Paid </option>
        </select>
      </div>
    </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Notes </label>
      <div class="col-md-6 col-sm-12 col-xs-12">
          <textarea class="form-control" cols="30" rows="2" name="tnotes"></textarea>
      </div>
     </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Loan </label>
      <div class="col-md-5 col-sm-12 col-xs-12">
        <table>
            <tr> <td> <input type="text" readonly class="form-control" name="tloan" id="tloan" size="10" title="Loan" /> </td> 
            <td> <button type="button" id="bgetloan" class="btn btn-primary button_inline"> GET </button> </td>
            </tr>
        </table>
      </div>
     </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Amount </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" name="tamount" id="tamount" size="10" title="Amount" /> 
      </div>
     </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 btn-group">
          <button type="submit" class="btn btn-primary" id="button">Save</button>
          <button type="button" id="bclose" class="btn btn-danger" data-dismiss="modal">Close</button>
          <button type="reset" id="breset_add" class="btn btn-warning">Reset</button>
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