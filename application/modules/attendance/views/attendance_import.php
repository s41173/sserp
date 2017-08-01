<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Import CSV - Employee List </h4>
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

<form id="upload_form_non" data-parsley-validate class="form-horizontal form-label-left" method="POST"
      action="<?php echo $form_action_import; ?>" enctype="multipart/form-data" >
     
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Period </label>
      <div class="col-md-5 col-sm-4 col-xs-12">
    <table>
     <tr> <td> <select name="cmonth" class="form-control">
                    <option value="" selected="selected"> -- </option>
                    <option value="1"> January </option>
                    <option value="2"> February </option>
                    <option value="3"> March </option>
                    <option value="4"> April </option>
                    <option value="5"> May </option>
                    <option value="6"> June </option>
                    <option value="7"> July </option>
                    <option value="8"> August </option>
                    <option value="9"> September </option>
                    <option value="10"> October </option>
                    <option value="11"> November </option>
                    <option value="12"> December </option>
                 </select> </td>
     <td> <input type="text" name="tyear" id="" class="form-control" maxlength="4" style="width:80px;" value="<?php echo date('Y'); ?>"> </td>
     </tr> 
     </table>
      </div>
    </div>    
    
    <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> CSV File </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="file" id="" class="input-medium" title="Upload" name="userfile" /> <br>
      </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 btn-group">
          <button type="submit" class="btn btn-primary" id="button">Save</button>
          <button type="button" id="bclose" class="btn btn-danger" data-dismiss="modal">Close</button>
          <button type="reset" id="breset" class="btn btn-warning">Reset</button>
          <a class="btn btn-default" href="<?php echo site_url('attendance/download'); ?>"> Download CSV - Format </a>
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