<style type="text/css">@import url("<?php echo base_url() . 'css/style.css'; ?>");</style>
<style type="text/css">@import url("<?php echo base_url() . 'development-bundle/themes/base/ui.all.css'; ?>");</style>
<style type="text/css">@import url("<?php echo base_url() . 'css/jquery.fancybox-1.3.4.css'; ?>");</style>

<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/register.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/datetimepicker_css.js"></script> 

<script type="text/javascript">
var uri = "<?php echo site_url('ajax')."/"; ?>";
var baseuri = "<?php echo base_url(); ?>";
var site = "<?php echo site_url();?>";
</script>

<script type="text/javascript">

$(document).ready(function(){
		
	$('#ajaxform,#ajaxform2').submit(function() {
		$.ajax({
			type: 'POST',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
			// $('#result').html(data);
			if (data == "true"){ location.reload(true);}
			else{ document.getElementById("errorbox").innerHTML = data; }
			}
		})
		return false;
	});	
	
	
	$('#ctype').change(function() {
		
		var type = $("#ctype").val();
					
		$.ajax({
		type: 'POST',
		url: site +"/ap_payment/get_voucher_no",
		data: "ctype="+ type,
		success: function(data)
		{
		   document.getElementById("tvoucherno").value = data;
		}
		})
		return false;
	});
	
/* end document */		
});

</script>

<style>
        .refresh{ border:1px solid #AAAAAA; color:#000; padding:2px 5px 2px 5px; margin:0px 2px 0px 2px; background-color:#FFF;}
		.refresh:hover{ background-color:#CCCCCC; color: #FF0000;}
		.refresh:visited{ background-color:#FFF; color: #000000;}	
</style>

<?php 
		
$atts1 = array(
	  'class'      => 'refresh',
	  'title'      => 'add cust',
	  'width'      => '600',
	  'height'     => '400',
	  'scrollbars' => 'no',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 600)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 400)/2)+\'',
);

?>

<div id="webadmin">
	<div class="title"> <?php $flashmessage = $this->session->flashdata('message'); ?> </div>
	<p class="message"> <?php echo ! empty($message) ? $message : '' . ! empty($flashmessage) ? $flashmessage : ''; ?> </p>
	
	<div id="errorbox" class="errorbox"> <?php echo validation_errors(); ?> </div>
	
	<fieldset class="field"> <legend> AP - Payment </legend>
	<form name="modul_form" class="myform" id="form" method="post" action="<?php echo $form_action; ?>">
				<table>
					<tr> 
						<td> <label for="tvendor"> Vendor </label> </td> <td>:</td>
						<td> <input type="text" class="required" name="tvendor" id="tcust" size="25" title="Name" /> &nbsp; 
						<?php echo anchor_popup(site_url("vendor/get_list/"), '[ ... ]', $atts1); ?>
					</tr>
					
					<tr>	
						<td> <label for="ctype"> Tax Type </label> </td> <td>:</td>
	                    <td> <select name="ctype" id="ctype">
                        	 <option value=""> -- </option> 
                             <option value="1" <?php echo set_select('ctype', '1', isset($default['type']) && $default['type'] == '1' ? TRUE : FALSE); ?> > Tax </option> 
                             <option value="0" <?php echo set_select('ctype', '0', isset($default['type']) && $default['type'] == '0' ? TRUE : FALSE); ?> > Non </option> 
                             </select> - &nbsp;
                             <input type="text" name="tvoucherno" id="tvoucherno" size="5" onkeyup="checkdigit(this.value, 'tvoucherno')" />
                        </td>
					</tr>
                    
                    <tr>	
						<td> <label for="ctranstype"> Trans Type </label> </td> <td>:</td>
	                    <td> <select name="ctranstype" id="ctranstype">
                             <option value="PO" <?php echo set_select('ctranstype', 'PO', isset($default['type']) && $default['type'] == 'PO' ? TRUE : FALSE); ?> > PURCHASE </option> 
                             <option value="PRINTING" <?php echo set_select('ctranstype', 'PRINTING', isset($default['type']) && $default['type'] == 'PRINTING' ? TRUE : FALSE); ?> > PRINTING </option> 
                             </select>
                        </td>
					</tr>
							
					
					<tr> <td> <label for="cacc"> Account </label> </td> <td>:</td> <td>  
		  <select name="cacc" class="required">
	      <option value="bank" <?php echo set_select('cacc', 'bank', isset($default['acc']) && $default['acc'] == 'bank' ? TRUE : FALSE); ?> /> Bank </option>
          <option value="cash" <?php echo set_select('cacc', 'cash', isset($default['acc']) && $default['acc'] == 'cash' ? TRUE : FALSE); ?> /> Cash </option>
	      <option value="pettycash" <?php echo set_select('cacc', 'pettycash', isset($default['acc']) && $default['acc'] == 'pettycash' ? TRUE : FALSE); ?> /> Petty Cash </option>
		  </select>
			<br />  </td> </tr>
					

					<tr>	
						 <td> <label for="tdate"> Date </label> </td> <td>:</td>
						 <td>  
						   <input type="Text" name="tdate" id="d1" title="Invoice date" size="10" class="required" /> 
				           <img src="<?php echo base_url();?>/jdtp-images/cal.gif" onclick="javascript:NewCssCal('d1','yyyymmdd')" style="cursor:pointer"/> &nbsp; <br />
						</td>
					</tr>
										
			<tr>	
			<td> <label for="tname"> Currency </label> </td> <td>:</td>
			<td> <?php $js = 'class="required"'; echo form_dropdown('ccurrency', $currency, isset($default['currency']) ? $default['currency'] : '', $js); ?> &nbsp; <br /> </td>
			</tr>
            
            <tr>
				<td> <label for="trate"> Rate </label></td> <td>:</td> 
				<td><input type="text" id="trate" name="trate" size="10" title="Rate" onKeyUp="checkdigit(this.value, 'trate')" /> <br />  </td> 
			</tr>
					
					<tr>	
						<td> <label for="tuser"> AP - Dept </label> </td> <td>:</td>
	     <td> <input type="text" class="required" readonly="readonly" name="tuser" size="15" title="User" value="<?php echo isset($user) ? $user : ''; ?>" /> &nbsp; <br /> </td>
					</tr>
					   
				</table>
				<p style="margin:15px 0 0 0; float:right;">
					<input type="submit" name="submit" class="button" title="Klik tombol untuk proses data" value=" Save " /> 
					<input type="reset" name="reset" class="button" title="Klik tombol untuk proses data" value=" Cancel " />
				</p>	
			</form>			  
	</fieldset>
</div>

