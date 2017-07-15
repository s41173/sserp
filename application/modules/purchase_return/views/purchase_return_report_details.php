<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="shortcut icon" href="<?php echo base_url().'images/fav_icon.png';?>" >
<title> <?php echo isset($title) ? $title : ''; ?>  </title>
<style media="all">
	table{ font-family:"Tahoma", Times, serif; font-size:11px;}
	h4{ font-family:"Tahoma", Times, serif; font-size:14px; font-weight:600;}
	.clear{clear:both;}
	table th{ background-color:#EFEFEF; padding:4px 0px 4px 0px; border-top:1px solid #000000; border-bottom:1px solid #000000;}
    p{ font-family:"Tahoma", Times, serif; font-size:12px; margin:0; padding:0;}
	legend{font-family:"Tahoma", Times, serif; font-size:13px; margin:0; padding:0; font-weight:600;}
	.tablesum{ font-size:13px;}
	.strongs{ font-weight:normal; font-size:12px; border-top:1px dotted #000000; }
	.poder{ border-bottom:0px solid #000000; color:#0000FF;}
</style>
</head>

<body>

<div style="width:100%; border:0px solid blue; font-family:Arial, Helvetica, sans-serif; font-size:12px;">
	
	<div style="border:0px solid red; float:left;">
		<table border="0">
    		<tr> <td> Currency </td> <td> : </td> <td> <?php echo $currency; ?> </td> </tr>
			<tr> <td> Period </td> <td> : </td> <td> <?php echo tglin($start); ?> to <?php echo tglin($end); ?> </td> </tr>
			<tr> <td> Run Date </td> <td> : </td> <td> <?php echo $rundate; ?> </td> </tr>
			<tr> <td> Log </td> <td> : </td> <td> <?php echo $log; ?> </td> </tr>
		</table>
	</div>

	<center>
	   <div style="border:0px solid green; width:230px;">	
	       <h4> <?php echo isset($company) ? $company : ''; ?> <br> Purchase Return - Report </h4>
	   </div>
	</center>
	
	<div class="clear"></div>
	
	<div style="width:100%; border:0px solid brown; margin-top:20px; border-bottom:1px dotted #000000; ">
	
		<table border="0" width="100%">
		   <tr>
 	       <th> No </th> <th> Date </th> <th> Order No </th> <th> Account </th> <th> Vendor </th> <th> Total </th> <th> Tax </th> <th> Costs </th>           <th> Balance </th> <th> Status </th> 
		   </tr>
		   
		   <!--<tr>
		   		<td class="strongs"> 1 </td>
				<td class="strongs"> Wed, 12 Jan 1989 </td>
				<td class="strongs"> PO-001 </td>
				<td class="strongs"> PT. Dswip Kreasindo </td>
				<td class="strongs" align="right"> 1.000.000 </td>
				<td class="strongs" align="right"> 100.000 </td>
				<td class="strongs" align="right"> 1.100.000 </td>
				<td class="strongs" align="right"> 600.000 </td>
				<td class="strongs" align="right"> 500.000 </td>
		   		<td class="strongs" align="center"> Settled </td>
		   </tr> -->
		   
		  <?php 
		  
		  	  function xstatus($val)
			  { if ($val == 0){ $val = 'Credit'; } else { $val = 'Settled'; } return $val; }	
			  
			  function product($pid){ $pro = new Product_lib(); return $pro->get_name($pid); }
			  function unit($pid){ $pro = new Products_lib(); return $pro->get_unit($pid); }
			  
			  function poder($po)
			  {
				 $CI =& get_instance();
				 $poder = $CI->transmodel->get_last_item($po)->result();
				 $i=1;
			
				foreach ($poder as $res)
				{
				   echo "
				   <tr>
				   <td class=\"poder\"> </td>
				   <td class=\"poder\"> </td>
				   <td class=\"poder\">$i</td>
				   <td class=\"poder\">".product($res->product)."</td>
				   <td class=\"poder\">".$res->qty."</td>
				   <td class=\"poder\" align=\"right\">".number_format($res->price)."</td>
				   <td class=\"poder\" align=\"right\">".number_format($res->tax)."</td>
				   <td class=\"poder\" align=\"right\">".number_format($res->amount)."</td>
				   </tr>";
				   $i++;
				} 
			
			  }
			  
		  
		      $i=1; 
			  if ($purchase_returns)
			  {
				foreach ($purchase_returns as $purchase)
				{	
				   echo " 
				   <tr> 
				       <td class=\"strongs\">".$i."</td> 
					   <td class=\"strongs\">".tglin($purchase->dates)."</td> 
					   <td class=\"strongs\"> PR-00".$purchase->no."</td> 
					   <td class=\"strongs\">".ucfirst($purchase->acc)."</td> 
					   <td class=\"strongs\">".$purchase->prefix.' '.$purchase->name."</td> 
					   <td class=\"strongs\" align=\"right\">".number_format($purchase->total - $purchase->tax)."</td> 
					   <td class=\"strongs\" align=\"right\">".number_format($purchase->tax)."</td>
					   <td class=\"strongs\" align=\"right\">".number_format($purchase->costs)."</td> 
					   <td class=\"strongs\" align=\"right\">".number_format($purchase->total + $purchase->costs)."</td> 
					   <td class=\"strongs\" align=\"center\">".xstatus($purchase->status)."</td> 
				   </tr>";
				   poder($purchase->no); echo "<br/>";
				   $i++; 
				}
			  }  
		  ?>
		   
		</table>
	</div>
	
	<div style="border:0px solid red; float:right; margin:15px 0px 0px 0px;">
	   <fieldset> <legend>Summary</legend>
			<table class="tablesum">			
				<tr> <td> Sub Total </td> <td> : </td> <td align="right"> <?php echo number_format($total); ?> </td> </tr>
				<tr> <td> Tax </td> <td> : </td> <td align="right"> <?php echo number_format($tax); ?> </td> </tr>
				<tr> <td> Landed Cost </td> <td> : </td> <td align="right"> <?php echo number_format($costs); ?> </td> </tr>
				<tr> <td> Balance </td> <td> : </td> <td align="right"> <?php echo number_format($balance); ?> </td> </tr>
			</table>
		</fieldset>
	</div>

</div>
<a style="float:left; margin:10px;" title="Back" href="<?php echo site_url('purchase_return'); ?>"> 
  <img src="<?php echo base_url().'images/back.png'; ?>"> 
</a>
</body>
</html>
