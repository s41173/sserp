<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Product Report </h4>
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
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Category </label>
        <div class="col-md-7 col-sm-9 col-xs-12">
          <?php $js = "class='form-control' id='ccategory' tabindex='-1' style='width:100%;' "; 
           echo form_dropdown('ccategory', $category, isset($default['category']) ? $default['category'] : '', $js); ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Manufacture </label>
        <div class="col-md-7 col-sm-9 col-xs-12">
          <?php $js = "class='form-control' id='cmanufacture' style='width:100%;'"; 
         echo form_dropdown('cmanufacture', $manufacture, isset($default['manufacture']) ? $default['manufacture'] : '', $js); ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Branch </label>
        <div class="col-md-7 col-sm-9 col-xs-12">
          <?php $js = "class='form-control' id='cmanufacture' style='width:100%;'"; 
         echo form_dropdown('cbranch', $branch_all, isset($default['branch']) ? $default['branch'] : '', $js); ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Period </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <table>
           <tr> 
               <td>
                   <?php $js = "class='form-control' id='cmonth' style='width:150px;'"; 
                   echo form_dropdown('cmonth', $month, isset($default['month']) ? $default['month'] : '', $js); ?>
               </td>
               <td>
                   <input class="form-control" name="tyear" type="number" maxlength="4" value="<?php echo date('Y'); ?>">
               </td>
           </tr>
          </table>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Type </label>
        <div class="col-md-3 col-sm-9 col-xs-12">     
			<select name="ctype" class="form-control">
              <option value="0"> Summary </option>
              <option value="1"> Pivottable </option>
            </select>
        </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
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