<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Add New Vendor </h4>
</div>
<div class="modal-body">

 <!-- error div -->
 <div class="alert alert-success success"> </div>
 <div class="alert alert-warning warning"> </div>
 <div class="alert alert-error error"> </div>
 
 <!-- form add -->
<div class="x_panel" >

<div class="x_content">

<form id="upload_form" data-parsley-validate class="form-horizontal form-label-left" method="POST" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
   			
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tname" name="tname" placeholder="Name">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>

      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control" id="tcp" name="tcp" placeholder="Contact Person">
        <span class="fa fa-user form-control-feedback right" aria-hidden="true"></span> 
      </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tphone" name="tphone" placeholder="Phone No">
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span> 
      </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control" id="tmobile" name="tmobile" placeholder="Mobile">
        <span class="fa fa-user form-control-feedback right" aria-hidden="true"></span> 
      </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="email" class="form-control has-feedback-left" id="temail" name="temail" placeholder="Email">
        <span class="fa fa-envelope form-control-feedback left" aria-hidden="true"></span> 
      </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <select name="ctype" id="ctype" class="form-control"> 
            <option value="personal"> Personal </option> 
            <option value="company"> Company </option>  
        </select>
      </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tnpwp" name="tnpwp" placeholder="NPWP">
        <span class="fa fa-sticky-note-o form-control-feedback left" aria-hidden="true"></span> 
      </div>

      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control" id="tfax" name="tfax" placeholder="Fax">
        <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span> 
      </div>
      
      <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
        <textarea name="taddress" id="taddress" class="form-control" placeholder="Address"></textarea>
          <input type="checkbox" id="cshipbox" name="cshipbox" checked value="1">
          <small style="color:#2A3F54;"> *) Use this address for shipping </small>
      </div>
    
      <!-- pembatas div -->
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
      </div>
       <!-- pembatas div -->
        
     <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
<?php $js = "class='select2_single form-control' placeholder='Select City' id='ccity' tabindex='-1' style='width:100%;' "; 
echo form_dropdown('ccity', $city, isset($default['city']) ? $default['city'] : '', $js); ?>
     </div>
    
     <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
        <div class="select_box"></div>
     </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="tzip" name="tzip" placeholder="ZIP">
        <span class="fa fa-file-archive-o form-control-feedback left" aria-hidden="true"></span> 
      </div>
    
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control" id="twebsite" name="twebsite" placeholder="Website">
        <span class="fa fa-internet-explorer form-control-feedback right" aria-hidden="true"></span> 
      </div>
    
       <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control has-feedback-left" id="taccname" name="taccname" placeholder="Acc Name">
        <span class="fa fa-sticky-note-o form-control-feedback left" aria-hidden="true"></span> 
      </div>

      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
        <input type="text" class="form-control" id="taccno" name="taccno" placeholder="Acc No">
        <span class="fa fa-sticky-note-o form-control-feedback right" aria-hidden="true"></span> 
      </div>
    
      <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
        <textarea name="tbank" id="tbank" class="form-control" placeholder="Bank Details"></textarea>
      </div>
      
      <!-- pembatas div -->
      <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
      </div>
       <!-- pembatas div --> 

      <div class="clearfix"></div> 
      
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
          <div class="btn-group">         
            <button type="submit" class="btn btn-primary" id="button">Save</button>
            <button type="button" id="bclose" class="btn btn-danger" data-dismiss="modal">Close</button>
            <button type="reset" id="breset" class="btn btn-warning">Reset</button>
          </div>
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