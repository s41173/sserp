<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel"> Category - Update </h4>
        </div>
        
 <div class="modal-body"> 
 
 <!-- error div -->
 <div class="alert alert-success success"> </div>
 <div class="alert alert-warning warning"> </div>
 <div class="alert alert-error error"> </div>

 <!-- form edit -->
 <form id="edit_form_non" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
 action="<?php echo $form_action_update; ?>" enctype="multipart/form-data">
     
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Code </label>
      <div class="col-md-3 col-sm-2 col-xs-12">
        <input id="tcode_update" class="form-control col-md-1 col-xs-12" type="number" name="tcode"  readonly required>
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Name </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
        <input id="tname_update" class="form-control col-md-7 col-xs-12" type="text" name="tname" required>
      </div>
    </div>
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Type </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
        <select name="ctype" id="ctype_update" class="form-control">
            <option value="harta"> Harta </option>
            <option value="modal"> Modal </option>
            <option value="kewajiban"> Kewajiban </option>
            <option value="pendapatan"> Pendapatan </option>
            <option value="biaya"> Biaya </option>
         </select>
      </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
          <button type="submit" class="btn btn-primary" id="button">Save</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
          </div>
      </div>
  </form> 
  <!-- form edit -->
  
  </div>
 </div>
</div>