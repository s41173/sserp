<style type="text/css">@import url("<?php echo base_url() . 'css/style.css'; ?>");</style>
<style type="text/css">@import url("<?php echo base_url() . 'development-bundle/themes/base/ui.all.css'; ?>");</style>
<style type="text/css">@import url("<?php echo base_url() . 'css/jquery.fancybox-1.3.4.css'; ?>");</style>

<script type="text/javascript" src="<?php echo base_url();?>js/register.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/datetimepicker_css.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>/development-bundle/ui/ui.core.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.tools.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/hoverIntent.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/complete.js"></script> 
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/sortir.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.maskedinput-1.3.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/validate.js"></script> 
<script type='text/javascript' src='<?php echo base_url();?>js/jquery.validate.js'></script>  

<script type="text/javascript">
var uri = "<?php echo site_url('ajax')."/"; ?>";
var baseuri = "<?php echo base_url(); ?>";

function cek_session()
{
	$(document).ready(function(){
		$.ajax({
			type: 'POST',
			url: uri +'cek_session',
			data: $(this).serialize(),
			success: function(data){ if (data == 'FALSE'){ window.close(); } }
		})
		return false;	
	}); 
}

function get_loan()
{	
   $(document).ready(function(){
	    var nip = $("#tsearch").val();
		$.ajax({
			type: 'POST',
			url: uri +'get_loan',
			data: "nip="+ nip,
			success: function(data)
			{ document.getElementById("tloan").value = data; }
		})
		return false;
   }); 
}

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
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 600)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 400)/2)+\'',
);

?>
<body onLoad="cek_session();" onUnload="window.opener.location.reload(true);">

<div id="webadmin">
	<div class="title"> <?php $flashmessage = $this->session->flashdata('message'); ?> </div>
	<p class="message"> <?php echo ! empty($message) ? $message : '' . ! empty($flashmessage) ? $flashmessage : ''; ?> </p>
	
	<div id="errorbox" class="errorbox"> <?php echo validation_errors(); ?> </div>
	
	<fieldset class="field"> <legend> Add - Loans </legend>
    <table>
     
	<form name="modul_form" id="ajaxform" class="myform" method="post" action="<?php echo $form_action; ?>">
			       
        <tr>
            <td> <label for="tdate"> Date </label> </td>  <td>:</td>
            <td> <input type="Text" name="tdate" id="d1" title="Born date" size="10" class="form_field" /> 
                 <img src="<?php echo base_url();?>/jdtp-images/cal.gif" onClick="javascript:NewCssCal('d1','yyyymmdd')" style="cursor:pointer"/>
            </td>
        </tr>	
        
        <tr> 
         <td> <label for="tvalue"> Currency </label> </td> <td>:</td> 
         <td> <?php $js = 'id="ccur"'; echo form_dropdown('ccur', $currency, isset($default['currency']) ? $default['currency'] : '', $js); ?> </td>
        </tr> 
        
        <tr> <td> <label for="cacc"> Account </label> </td> <td>:</td> <td>  
			       <select name="cacc" class="required">
	                 <option value="bank" /> Bank </option>
	                 <option value="cash" /> Cash </option>
			       </select> <br />  
              </td>
         </tr>
        
        <tr>
        <td> <label for="tname"> Employee </label> </td> <td>:</td>
        <td> <input type="text" readonly name="tnip" id="tsearch" size="10" title="Name" />
              <?php echo anchor_popup(site_url("employees/get_list/"), '[ ... ]', $atts1); ?>
        </td>
        </tr>
        
      <tr>
      <td> <label for="ctype"> Type </label> </td> <td>:</td>
      <td> <select class="required" name="ctype">
             <option value="borrow"> Borrow </option>
             <option value="paid"> Paid </option>
           </select>
      </td>
      </tr>  
      
      <tr>
        <td> <label for="tnotes"> Notes </label> </td> <td>:</td>
        <td> <input type="text" name="tnotes" size="30" title="Notes" />
        </td>
      </tr>
       
      <tr>
      <td> <label for="tloan"> Loan </label> </td>  <td>:</td>
      <td> <input type="text" readonly name="tloan" id="tloan" size="10" title="Loan" /> 
           <input type="button" value="GET" onClick="get_loan();">
      </td>
      </tr> 
                     				
      <tr>
      <td> <label for="tamount"> Amount </label> </td>  <td>:</td>
      <td> <input type="text" class="required" name="tamount" id="tamount" size="10" title="Amount" 
           onKeyUp="checkdigit(this.value,'tamount')" 
           value="<?php echo set_value('tamount', isset($default['amount']) ? $default['amount'] : ''); ?>" /> 
      </td>
      </tr>
        
        </table>
        <p style="margin:15px 0 0 0; float:right;">
            <input type="submit" name="submit" class="button" title="Klik tombol untuk proses data" value=" Save " /> 
            <input type="reset" name="reset" class="button" title="Klik tombol untuk proses data" value=" Cancel " />
        </p>	
        </form>			  
	</fieldset>
</div>

</body>