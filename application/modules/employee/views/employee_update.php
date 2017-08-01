
 <!-- Datatables CSS -->
<link href="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>css/icheck/flat/green.css" rel="stylesheet" type="text/css">

<!-- Date time picker -->
 <script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 
 <!-- Include Date Range Picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />


<style type="text/css">
  a:hover { text-decoration:none;}
</style>

<script src="<?php echo base_url(); ?>js/moduljs/employee.js"></script>
<script src="<?php echo base_url(); ?>js-old/register.js"></script>

<script type="text/javascript">

	var sites_add  = "<?php echo site_url('employee/add_process/');?>";
	var sites_edit = "<?php echo site_url('employee/update_process/');?>";
	var sites_del  = "<?php echo site_url('employee/delete/');?>";
	var sites_get  = "<?php echo site_url('employee/update/');?>";
	var source = "<?php echo $source;?>";
	
</script>

          <div class="row"> 
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel" >
              
              <!-- xtitle -->
              <div class="x_title">
              
                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a> </li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a> </li>
                </ul>
                
                <div class="clearfix"></div>
              </div>
              <!-- xtitle -->
                
                <div class="x_content">
                      
                  
             <!-- Smart Wizard -->
<div id="wizard" class="form_wizard wizard_horizontal">
  
  <ul class="wizard_steps">
    <li>
      <a href="#step-1">
        <span class="step_no">1</span>
        <span class="step_descr"> <small> Information 1 </small> </span>
      </a>
    </li>
    <li>
      <a href="#step-2">
        <span class="step_no">2</span>
        <span class="step_descr"> <small> Information 2 </small> </span>
      </a>
    </li>
    <li>
      <a href="#step-3">
        <span class="step_no">3</span>
        <span class="step_descr"> <small> Information 3 </small> </span>
      </a>
    </li>
  </ul>
  
  <div id="errors" class="alert alert-danger alert-dismissible fade in" role="alert"> 
     <?php $flashmessage = $this->session->flashdata('message'); ?> 
	 <?php echo ! empty($message) ? $message : '' . ! empty($flashmessage) ? $flashmessage : ''; ?> 
  </div>
  
  <div id="step-1">
    <!-- form -->
    <form id="" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
    action="<?php echo $form_action.'/1'; ?>" 
      enctype="multipart/form-data">
		
      <div class="form-group">  
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sku"> Name </label>
	  <div class="col-md-4 col-sm-6 col-xs-12">
        <input type="text" class="form-control" name="tname" size="35" title="Name"
        value="<?php echo set_value('tname', isset($default['name']) ? $default['name'] : ''); ?>" /> 
      </div>
      </div>
	
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Nick Name </label>
        <div class="col-md-4 col-sm-6 col-xs-12">
         <input type="text" class="form-control" name="tnickname" size="25" title="Nick Name"
                 value="<?php echo set_value('tnickname', isset($default['nickname']) ? $default['nickname'] : ''); ?>" /> 
        </div>
      </div>
      
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name"> Attendance Code </label>
        <div class="col-md-3 col-sm-6 col-xs-12">
        <input type="text" class="form-control" name="tatt" size="15" title="Att Code" 
                 value="<?php echo set_value('tatt', isset($default['att']) ? $default['att'] : ''); ?>" /> 
        </div>
      </div>
      
       <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name"> NIP </label>
        <div class="col-md-3 col-sm-6 col-xs-12">
        <input type="text" class="form-control" name="tnip" size="15" title="NIP" 
                 value="<?php echo set_value('tnip', isset($default['nip']) ? $default['nip'] : ''); ?>" /> 
        </div>
      </div>
            
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Email </label>
        <div class="col-md-3 col-sm-6 col-xs-12">
  	       <input type="text" class="form-control" name="temail" size="35" title="Email"
                 value="<?php echo set_value('temail', isset($default['email']) ? $default['email'] : ''); ?>" />
        </div>
      </div>
        
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Phone </label>
        <div class="col-md-3 col-sm-6 col-xs-12">
<input type="text" class="form-control" name="tphone" size="15" title="Phone"
                 value="<?php echo set_value('tphone', isset($default['phone']) ? $default['phone'] : ''); ?>" />
        </div>
      </div>
      
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Mobile </label>
        <div class="col-md-3 col-sm-6 col-xs-12">
<input type="text" class="form-control" name="tmobile" size="15" title="Mobile"
                 value="<?php echo set_value('tmobile', isset($default['mobile']) ? $default['mobile'] : ''); ?>" />
        </div>
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
        <div class="col-md-3 col-sm-6 col-xs-12">
<select name="cgenre" class="form-control"> 
<option value="m"<?php echo set_select('cgenre', 'm', isset($default['genre']) && $default['genre'] == 'm' ? TRUE : FALSE); ?>> Male </option> <option value="f"<?php echo set_select('cgenre', 'f', isset($default['genre']) && $default['genre'] == 'f' ? TRUE : FALSE); ?>> Female </option> </select>
        </div>
      </div>
        
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Role </label>
        <div class="col-md-3 col-sm-6 col-xs-12">
<select name="crole" class="form-control"> 
<option value="honor"<?php echo set_select('crole', 'honor', isset($default['role']) && $default['role'] == 'honor' ? TRUE : FALSE); ?>> Honor 
</option>
<option value="staff"<?php echo set_select('crole', 'staff', isset($default['role']) && $default['role'] == 'staff' ? TRUE : FALSE); ?>> Staff 
</option>
<option value="officer"<?php echo set_select('crole', 'officer', isset($default['role']) && $default['role'] == 'officer' ? TRUE : FALSE); ?>> Officer </option>
<option value="manager"<?php echo set_select('crole', 'manager', isset($default['role']) && $default['role'] == 'manager' ? TRUE : FALSE); ?>> 
Manager </option>
<option value="director"<?php echo set_select('crole', 'director', isset($default['role']) && $default['role'] == 'director' ? TRUE : FALSE); ?>> 
Director </option>
             </select>
        </div>
      </div> 
        
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Joined </label>
        <div class="col-md-2 col-sm-6 col-xs-12">
<input type="Text" name="tjoined" id="ds1" title="Join date" size="10" class="form-control" 
                 value="<?php echo set_value('tjoined', isset($default['joined']) ? $default['joined'] : ''); ?>" /> 
        </div>
      </div>
      
      <div class="form-group">
      <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12"> Image </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="file" id="uploadImage" accept="image/*" class="input-medium" title="Upload" name="userfile" /> <br>
          <img id="catimg" style=" max-width:100px; height:auto;" src="<?php echo isset($default['image']) ? $default['image'] : '' ?>">
      </div>
      </div>
      
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-3 col-sm-3 col-xs-12 col-md-offset-3">
          <button type="submit" class="btn btn-primary" id="button"> Save General </button>
        </div>
      </div>
      
	</form>
    <!-- end div layer 1 -->
  </div>
  
  <!-- div 2 -->
  <div id="step-2">
     
    <!-- form -->
<form id="ajaxformdata2" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
action="<?php echo $form_action.'/2'; ?>" >

      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Work Time </label>
        <div class="col-md-3 col-sm-6 col-xs-12">
<select name="ctime" class="form-control"> 
   <option value="0"<?php echo set_select('ctime', '0', isset($default['time']) && $default['time'] == '0' ? TRUE : FALSE); ?>> 0 </option>
   <option value="1-5"<?php echo set_select('ctime', '1-5', isset($default['time']) && $default['time'] == '1-5' ? TRUE : FALSE); ?>> 1-5 </option>
   <option value=">5"<?php echo set_select('ctime', '>5', isset($default['time']) && $default['time'] == '>5' ? TRUE : FALSE); ?>> >5 </option>
             </select>
        </div>
      </div>
    
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Date Of Birth </label>
        <div class="col-md-9 col-sm-9 col-xs-12">
            <table>
            <tr> <td>
<input type="text" class="form-control" name="tbornplace" size="15" title="Born Place"
value="<?php echo set_value('tbornplace', isset($default['bornplace']) ? $default['bornplace'] : ''); ?>" />
                              
            </td> <td>
<input type="Text" name="tborndate" id="ds2" title="Born Date" size="10" class="form-control" 
                 value="<?php echo set_value('tborndate', isset($default['borndate']) ? $default['borndate'] : ''); ?>" /> 
            </td>    
            </tr>    
            </table>
        </div>
      </div>
      
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Religion </label>
        <div class="col-md-3 col-sm-9 col-xs-12">
<select name="creligion" class="form-control"> 
<option value="moeslim"
<?php echo set_select('creligion', 'moeslim', isset($default['religion']) && $default['religion'] == 'moeslim' ? TRUE : FALSE); ?>> 
Moeslim 
</option> 

<option value="christian"
<?php echo set_select('creligion', 'christian', isset($default['religion']) && $default['religion'] == 'christian' ? TRUE : FALSE); ?>> 
Christian </option> 

<option value="catholic"<?php echo set_select('creligion', 'catholic', isset($default['religion']) && $default['religion'] == 'catholic' ? TRUE : FALSE); ?> > Catholic </option> 

<option value="hindu"<?php echo set_select('creligion', 'hindu', isset($default['religion']) && $default['religion'] == 'hindu' ? TRUE : FALSE); ?>> Hindu </option> 

<option value="buddha"<?php echo set_select('creligion', 'buddha', isset($default['religion']) && $default['religion'] == 'buddha' ? TRUE : FALSE); ?>> Buddha </option> 

<option value="others"<?php echo set_select('creligion', 'others', isset($default['religion']) && $default['religion'] == 'others' ? TRUE : FALSE); ?>> Others </option> 
                 </select> 
            
        </div>
      </div>
      
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Ethnic </label>
        <div class="col-md-3 col-sm-9 col-xs-12">
<input type="text" name="tethnic" size="15" title="Ethnic" class="form-control"
                 value="<?php echo set_value('tethnic', isset($default['ethnic']) ? $default['ethnic'] : ''); ?>" /> 
        </div>
      </div>
    
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Marital status </label>
        <div class="col-md-3 col-sm-9 col-xs-12">
<input type="radio" class="flat" name="rmarried" value="yes" 
 <?php echo set_radio('rmarried', 'yes', isset($default['married']) && $default['married'] == 'yes' ? TRUE : FALSE); ?> /> Yes  
 <input type="radio" class="flat" name="rmarried" value="no" 
 <?php echo set_radio('rmarried', 'no', isset($default['married']) && $default['married'] == 'no' ? TRUE : FALSE); ?> /> No
 <input type="radio" class="flat" name="rmarried" value="" 
 <?php echo set_radio('rmarried', '', isset($default['married']) && $default['married'] == '' ? TRUE : FALSE); ?> /> (No Data)
        </div>
      </div>
    
       <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> ID - No </label>
        <div class="col-md-3 col-sm-9 col-xs-12">
<input type="text" name="tidno" size="20" title="ID - No" class="form-control"
                 value="<?php echo set_value('tidno', isset($default['idno']) ? $default['idno'] : ''); ?>" />        </div>
      </div>
    
       <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Address </label>
        <div class="col-md-5 col-sm-9 col-xs-12">
<textarea name="taddress" cols="40" rows="3" class="form-control"><?php echo set_value('taddress', isset($default['address']) ? $default['address'] : ''); ?></textarea>
        </div>
      </div>
      
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-3 col-sm-3 col-xs-12 col-md-offset-3">
          <button type="submit" class="btn btn-primary" id="button"> Save Data </button>
        </div>
      </div>
      
	</form>
    <!-- end div layer 2 -->
     
  </div>
  <!-- div 2 -->
    
   <!-- div 3 -->
  <div id="step-3">
     
    <!-- form -->
<form id="ajaxformdata" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
action="<?php echo $form_action.'/3'; ?>" >

      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Description </label>
        <div class="col-md-4 col-sm-6 col-xs-12">
<textarea name="tdesc" cols="40" rows="2" class="form-control"><?php echo set_value('tdesc', isset($default['desc']) ? $default['desc'] : ''); ?></textarea>
        </div>
      </div>
    
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Account - No </label>
        <div class="col-md-3 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="taccno" size="20" title="Account No"
                 value="<?php echo set_value('taccno', isset($default['accno']) ? $default['accno'] : ''); ?>" />
        </div>
      </div>
    
     <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Account - Name </label>
        <div class="col-md-3 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="taccname" size="30" title="Account Name"
                 value="<?php echo set_value('taccname', isset($default['accname']) ? $default['accname'] : ''); ?>" /> 
        </div>
      </div>
    
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-2 col-xs-12"> Bank Name </label>
        <div class="col-md-4 col-sm-9 col-xs-12">
<textarea name="tbank" class="form-control" cols="35" rows="3"><?php echo set_value('tbank', isset($default['bank']) ? $default['bank'] : ''); ?></textarea> 
        </div>
      </div>
      
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Resign </label>
        <div class="col-md-2 col-sm-6 col-xs-12">
<input type="Text" name="tresign" id="ds3" title="Resign Date" size="10" class="form-control" 
                 value="<?php echo set_value('tresign', isset($default['resign']) ? $default['resign'] : ''); ?>" /> 
        </div>
      </div>
    
      <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Subject Lesson </label>
        <div class="col-md-2 col-sm-6 col-xs-12">
<input type="text" class="form-control" name="tsubject" size="25" title="Subject Lesson"
                 value="<?php echo set_value('tsubject', isset($default['subject']) ? $default['subject'] : ''); ?>" /> 
        </div>
      </div>


      
      <div class="ln_solid"></div>
      <div class="form-group">
        <div class="col-md-3 col-sm-3 col-xs-12 col-md-offset-3">
          <button type="submit" class="btn btn-primary" id="button"> Save Data </button>
        </div>
      </div>
      
	</form>
    <!-- end div layer 2 -->
     
  </div>
  <!-- div 3 -->    
  
</div>
<!-- End SmartWizard Content -->
                      
     </div>
       
       <!-- links -->
       <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?>
       <!-- links -->
                     
    </div>
  </div>
      
      <script src="<?php echo base_url(); ?>js/icheck/icheck.min.js"></script>
      
       <!-- Datatables JS -->
        <script src="<?php echo base_url(); ?>js/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.bootstrap.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/jszip.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/pdfmake.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/vfs_fonts.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.fixedHeader.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.keyTable.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.responsive.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/responsive.bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.scroller.min.js"></script>
        <script src="<?php echo base_url(); ?>js/datatables/dataTables.tableTools.js"></script>
    
    <!-- jQuery Smart Wizard -->
    <script src="<?php echo base_url(); ?>js/wizard/jquery.smartWizard.js"></script>
        
        <!-- jQuery Smart Wizard -->
    <script>
      $(document).ready(function() {
        $('#wizard').smartWizard();

        $('#wizard_verticle').smartWizard({
          transitionEffect: 'slide'
        });

      });
    </script>
    <!-- /jQuery Smart Wizard -->
    
    
