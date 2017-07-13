<script type="text/javascript" src="<?php echo base_url();?>public/javascripts/FusionCharts.js"></script>
	
<?php 

$atts = array(
		  'class'      => 'refresh',
		  'title'      => 'add po',
		  'width'      => '850',
		  'height'     => '600',
		  'scrollbars' => 'no',
		  'status'     => 'yes',
		  'resizable'  => 'yes',
		  'screenx'    =>  '\'+((parseInt(screen.width) - 800)/2)+\'',
		  'screeny'    =>  '\'+((parseInt(screen.height) - 600)/2)+\'',
		);
		
$atts1 = array(
	  'class'      => 'refresh',
	  'title'      => 'Purchase Invoice',
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
					
					<td> <label for="tname">NO : CD-00</label></td> <td></td> <td><input type="text" class="" id="tno" name="tno" size="5" title="No" 
					value="<?php echo set_value('tno', isset($default['no']) ? $default['no'] : ''); ?>" onkeyup="checkdigit(this.value, 'tno')" />  </td> 
					
					<td> <label for="tname"> Cust </label></td> <td>:</td> <td> 
					    <input type="text" readonly name="tcust" id="tcust" size="25" title="Customer" /> 
				<!--		<a class="refresh" id="tombol" href="<?php //echo site_url("vendor/get_list/"); ?>"> [ ... ] </a> -->
						<?php echo anchor_popup(site_url("vendor/get_list/"), '[ ... ]', $atts1); ?>
					</td> 
					
					<td> <label for=""> &nbsp; &nbsp; Date </label></td> <td>:</td> 
					<td> <input type="Text" name="tdate" id="d1" title="Start date" size="10" class="form_field" /> 
				         <img src="<?php echo base_url();?>/jdtp-images/cal.gif" onclick="javascript:NewCssCal('d1','yyyymmdd')" style="cursor:pointer"/>
					</td> 
					
					<td colspan="3" align="right"> 
					<input type="submit" name="submit" class="button" title="Klik tombol untuk proses data" value="Search" /> 
					<input type="reset" name="reset" class="button" title="Klik tombol untuk proses data" value=" Cancel " /> 
					</td>
					
					</tr> 
				</table>	
			</form>			  
	</fieldset>
</div>


<div id="webadmin2">
	
	<form name="search_form" class="myform" method="post" action="<?php echo ! empty($form_action_del) ? $form_action_del : ''; ?>">
     <?php echo ! empty($table) ? $table : ''; ?>
	 <div class="paging"> <?php echo ! empty($pagination) ? $pagination : ''; ?> </div>
	</form>	
	
	<table align="right" style="margin:10px 0px 0 0; padding:3px; " width="100%" bgcolor="#D9EBF5">
	<tbody>
		<tr> 
		   <td align="right"> 
		   <?php echo anchor_popup(site_url("ap_payment/add"), 'CREATE NEW PAYMENT', $atts); ?>
		   <?php echo anchor_popup(site_url("ap_payment/report"), 'REPORT', $atts1); ?>
           <?php echo anchor_popup(site_url("ap_payment/payable"), 'PAYABLE CARD', $atts1); ?>
		   </td> 
		</tr>
	</tbody>
	</table>

		
	<!-- links -->
	<div class="buttonplace"> <?php if (!empty($link)){foreach($link as $links){echo $links . '';}} ?> </div>

	
</div>

