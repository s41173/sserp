<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="shortcut icon" href="<?php echo base_url().'images/fav_icon.png';?>" >
<title> <?php echo isset($title) ? $title : ''; ?>  </title>
<style media="all">
	table{ font-family:Tahoma, Geneva, sans-serif; font-size:11px;}
	h4{ font-family:"Times New Roman", Times, serif; font-size:14px; font-weight:600;}
	.clear{clear:both;}
	table th{ background-color:#EFEFEF; padding:4px 0px 4px 0px; border-top:1px solid #000000; border-bottom:1px solid #000000;}
    p{ font-family:Tahoma, Geneva, sans-serif; font-size:12px; margin:0; padding:0;}
	legend{ font-family:Tahoma, Geneva, sans-serif; font-size:13px; margin:0; padding:0; font-weight:600;}
	.tablesum{ font-size:13px;}
	.strongs{ font-weight:normal; font-size:12px; border-top:1px dotted #000000; }
	.poder{ border-bottom:0px solid #000000; color:#0000FF;}
</style>

    <link rel="stylesheet" type="text/css" href="<?php echo base_url().'js-old/pivot/' ?>pivot.css">
    <script type="text/javascript" src="<?php echo base_url().'js-old/pivot/' ?>jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/pivot/' ?>jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/pivot/' ?>jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/pivot/' ?>pivot.js"></script>
    
    <script type="text/javascript">
        $(document).ready(function () {
          	
			var input = $("#input")
			$("#output").pivotUI(input);
			$("#input").hide();
        });
    </script>

</head>

<body>

<div style="width:100%; border:0px solid blue; font-family:Arial, Helvetica, sans-serif; font-size:12px;">
	
	<div style="border:0px solid red; float:left;">
		<table border="0">
    		<tr> <td> Currency </td> <td> : </td> <td> <?php echo $currency; ?> </td> </tr>
			<tr> <td> Period </td> <td> : </td> <td> <?php echo tglin($start); ?> :: <?php echo tglin($end); ?> </td> </tr>
			<tr> <td> Run Date </td> <td> : </td> <td> <?php echo $rundate; ?> </td> </tr>
			<tr> <td> Log </td> <td> : </td> <td> <?php echo $log; ?> </td> </tr>
		</table>
	</div>

	<center>
	   <div style="border:0px solid green; width:230px;">	
	       <h4> <?php echo isset($company) ? $company : ''; ?> <br> Purchase Order Report </h4>
	   </div>
	</center>
	
	<div class="clear"></div>
	
	<div style="width:100%; border:0px solid brown; margin-top:20px; border-bottom:1px dotted #000000; ">
    
        <div id='jqxWidget'>
        <div style='margin-top: 10px;' id="output"> </div>
        </div>
        
		<table id="input" border="0" width="100%">
		   
           <thead>
           <tr>
 	       <th> No </th> <th> Date </th> <th> Request </th> <th> Order No </th> <th> Vendor </th> <th> Acc </th> <th> Sub Total </th> <th> Tax </th> 
           <th> Costs </th> <th> Purchase Total </th> <th> Payment </th> <th> Refund </th> <th> Refund Amount </th>  <th> Balance </th> <th> Status </th> 
           <th> Stock-IN </th>
		   </tr>
           </thead>
		   
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
		  
          <tbody> 
		  <?php 
		  
		  	  function purchase_status($val)
			  { if ($val == 0){ $val = 'debt'; } else { $val = 'settled'; } return $val; }	
		      
              function ap($val){ if ($val > 0){ return 'CD-00'.$val; }else { return "-"; } }
		  
		      $i=1; 
			  if ($purchases)
			  {
				foreach ($purchases as $purchase)
				{	
				   echo " 
				<tr> 
				       <td class=\"strongs\">".$i."</td> 
					   <td class=\"strongs\">".tglin($purchase->dates)."</td> 
					   <td class=\"strongs\"> PR-00".$purchase->request."</td>
					   <td class=\"strongs\"> PO-00".$purchase->no."</td> 
					   <td class=\"strongs\">".$purchase->prefix.' '.$purchase->name."</td> 
					   <td class=\"strongs\">".ucfirst($purchase->acc)."</td> 
					   <td class=\"strongs\" align=\"right\">".intval($purchase->total - $purchase->tax)."</td> 
					   <td class=\"strongs\" align=\"right\">".$purchase->tax."</td>
					   <td class=\"strongs\" align=\"right\">".$purchase->costs."</td> 
					   <td class=\"strongs\" align=\"right\">".intval($purchase->total + $purchase->costs)."</td> 
					   <td class=\"strongs\" align=\"right\">".$purchase->p1."</td>
					   <td class=\"strongs\" align=\"right\">".ap($purchase->ap_over)."</td>
					   <td class=\"strongs\" align=\"right\">".$purchase->over_amount."</td>
					   <td class=\"strongs\" align=\"right\">".$purchase->p2."</td> 
					   <td class=\"strongs\" align=\"center\">".purchase_status($purchase->status)."</td> 
					   <td class=\"strongs\" align=\"center\"> </td> 
				   </tr>";
				   $i++;
				}
			  }  
		  ?>
		  </tbody> 
		</table>
	</div>

</div>
<a style="float:left; margin:10px;" title="Back" href="<?php echo site_url('purchase'); ?>"> 
  <img src="<?php echo base_url().'images/back.png'; ?>"> 
</a>
</body>
</html>
