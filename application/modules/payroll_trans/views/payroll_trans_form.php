<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Add Payroll - Transaction </h4>
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
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Payment Type </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <select name="cpayment" id="cpayment" class="form-control">
            <option value="transfer"> Transfer </option>
            <option value="cash"> Cash </option>
         </select>
         <input type="hidden" id="tmonth" value="<?php echo $month; ?>" />
         <input type="hidden" id="tyear" value="<?php echo $year; ?>" />
      </div>
    </div>
     
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
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Basic Salary </label>
      <div class="col-md-5 col-sm-12 col-xs-12">
          
         <table>
<tr> <td> <input type="number" class="form-control" name="tbasic" id="tbasic" size="10" title="Basic Salary" style="width:120px;" value="0" readonly />  </td>
         <td> <button class="btn btn-primary button_inline" type="button" id="bget"> GET </button> </td>
         </tr> 
         </table>        
      </div>
     </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Ex-Benefit </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" name="texperience" id="texperience" size="10" value="0" title="Experience Benefit" readonly /> 
      </div>
     </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Overtime </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
<input type="number" class="form-control" readonly name="tovertime" id="tovertime" size="10" value="0" title="Overtime" /> 
      </div>
     </div>

     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Consumption </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
  <input type="number" class="form-control" readonly name="tconsumption" id="tconsumption" size="10" value="0" title="Consumption" /> 
      </div>
     </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Transportation </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" readonly name="ttransport" id="ttransport" size="10" value="0" title="Transportation" onkeyup="calculate_aid();" /> 
      </div>
     </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Bonus </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" name="tbonus" id="tbonus" size="10" value="0" title="Bonus" onkeyup="calculate_aid();" /> 
      </div>
     </div>
    
    <fieldset> <legend> Deduction </legend>
    
      <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Late Charges </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" name="tlate" id="tlate" size="10" value="0" title="Late Charges" onkeyup="calculate_aid();" /> 
      </div>
     </div>    
        
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Loan </label>
      <div class="col-md-5 col-sm-12 col-xs-12">
        <table>
            <tr> <td> <input type="text" class="form-control" name="tloan" id="tloan" value="0" size="10" title="Loan" onkeyup="calculate_aid();" /> </td> 
            <td> <button type="button" id="bgetloan" class="btn btn-primary button_inline"> Get Loan </button> </td>
            </tr>
        </table>
      </div>
     </div>
        
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Tax </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" name="ttax" id="ttax" value="0" size="10" title="Tax" onkeyup="calculate_aid();" /> 
      </div>
     </div>
        
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Insurance </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" name="tinsurance" id="tinsurance" value="0" size="10" title="Insurance" onkeyup="calculate_aid();" /> 
      </div>
     </div>
    
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Other </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
        <input type="number" class="form-control" name="tother" id="tother" value="0" size="10" onkeyup="calculate_aid();" title="Other" /> 
      </div>
     </div>
        
     <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> <b> Total </b> </label>
      <div class="col-md-3 col-sm-12 col-xs-12">
<input type="number" class="form-control" name="ttotal" id="ttotal" readonly value="0" size="10" title="Other" /> 
      </div>
     </div>    
        
    </fieldset>

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