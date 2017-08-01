<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Add - Employee </h4>
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

<form id="upload_form" data-parsley-validate class="form-horizontal form-label-left" method="POST" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
   
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tname" name="tname" placeholder="Name">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>

      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tnickname" name="tnickname" placeholder="Nick Name">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tatt" name="tatt" placeholder="Att Code">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>

      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tnip" name="tnip" placeholder="NIP">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>
    
       <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="email" class="form-control has-feedback-left" id="temail" name="temail" placeholder="Email">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>

      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tmobile" name="tmobile" placeholder="Mobile">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>

      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Division </label>
        <div class="col-md-6 col-sm-6 col-xs-12">
    <?php $js = "class='form-control' id='cdivision' tabindex='-1' style='width:250px;' "; 
	      echo form_dropdown('cdivision', $division, isset($default['division']) ? $default['division'] : '', $js); ?>
        </div>
      </div>
    
     <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Genre </label>
        <div class="col-md-4 col-sm-6 col-xs-12">
         <select name="cgenre" class="form-control"> 
<option value="m"<?php echo set_select('cgenre', 'm', isset($default['genre']) && $default['genre'] == 'm' ? TRUE : FALSE); ?>> Male </option> <option value="f"<?php echo set_select('cgenre', 'f', isset($default['genre']) && $default['genre'] == 'f' ? TRUE : FALSE); ?>> Female </option> </select>
        </div>
      </div>
    
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Role </label>
        <div class="col-md-4 col-sm-6 col-xs-12">
         <select class="form-control" name="crole">
            <option value="honor"> Honor </option>
            <option value="staff"> Staff </option>
            <option value="officer"> Officer </option>
            <option value="manager"> Manager </option>
            <option value="director"> Director </option>
          </select>
        </div>
      </div>
                                      
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Joined </label>
        <div class="col-md-4 col-sm-6 col-xs-12">
        <input type="text" title="Date" class="form-control" id="ds1" name="tjoined" required
           value="<?php echo isset($default['date']) ? $default['date'] : '' ?>" />  
        </div>
      </div>
                  
      <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Image </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="file" id="uploadImage" accept="image/*" class="input-medium" title="Upload" name="userfile" /> <br>
            <img id="catimg" style=" max-width:50px; height:auto;">
      </div>
      </div>
       
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3 btn-group">
          <button type="submit" class="btn btn-primary" id="button">Save</button>
          <button type="button" id="bclose" class="btn btn-danger" data-dismiss="modal">Close</button>
          <button type="reset" id="breset" class="btn btn-warning">Reset</button>
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