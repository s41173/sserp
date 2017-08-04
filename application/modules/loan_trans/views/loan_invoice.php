<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title> Loan - LT-00<?php echo isset($pono) ? $pono : ''; ?></title>
<style media="all">

	#logo { margin:0 0 0 75px;}
	#logotext{ font-size:12px; text-align:center; margin:0; }
	p { margin:0; padding:0; font-size:11px;}
	#pono{ font-size:18px; padding:0; margin:0 5px 10px 0; text-align:left;}
	
	table.product
	{ border-collapse:collapse; width:100%; }
	
	table.product,table.product th
	{	border: 1px solid black; font-size:13px; font-weight:bold; padding:4px 0 4px 0; }
	
	table.product,table.product td
	{	border: 1px solid black; font-size:12px; font-weight:normal; padding:3px 0 3px 0; text-align:center; }
	
	table.product td.left { text-align:left; padding:3px 5px 3px 10px; }
	table.product td.right { text-align:right; padding:3px 10px 3px 5px; }
	
</style>
</head>
    
<script type="text/javascript">
    
    function closeWindow() {
        setTimeout(function() {
        window.close();
        }, 300000);
        
        window.print();
    }
    
</script>  

<body onLoad="closeWindow();">

<div style="width:750px; font-family:Arial, Helvetica, sans-serif; font-size:12px;"> 
	
	<h2 style="font-size:18px; font-weight:normal; text-align:center;"> <?php echo $p_name; ?> <br> Loan Receipt  </h2>
    <div style="clear:both; "></div> 
	
	<div style="width:350px; border:0px solid #000; float:left;">
		<table style="font-size:11px;">
 		    <tr> <td> Received By </td> <td>:</td> <td> <?php echo $employee; ?> </td> </tr>
			<tr> <td> Currency </td> <td>:</td> <td> <?php echo $currency; ?> </td> </tr>
            <tr> <td> Log </td> <td>:</td> <td> <?php echo $log; ?> </td> </tr>
		</table>
	</div>
	
	<div style="width:200px; border:0px solid red; float:right;">
		<table style="font-size:11px;">
            <tr> <td> No </td> <td>:</td> <td> <?php echo 'LT-00'.$pono; ?> </td> </tr>
			<tr> <td> Tgl </td> <td>:</td> <td> <?php echo $podate; ?> </td> </tr>
		</table>
	</div>
	
	<div style="clear:both; "></div>
	
	<div style="margin:3px 0 0 0; border-bottom:0px dotted #000;">
		
		<table class="product">

		 <tr> <th> Notes </th>  <th> Amount </th> </tr>
		 <tr> <td class="left"> <?php echo $notes; ?> </td> <td class="right"> <b> <?php echo number_format($amount); ?> </b> </td> </tr>
			
		</table>
		
		<div style="float:left; width:600px; border:0px solid #000; margin:5px 0 5px 0;">  
			<table style="font-size:10pt;">
				<tr> <td> Terbilang </td> <td>:</td> 
				     <td> <b> #<?php echo ucfirst($terbilang); ?># </b> </td> 
				</tr>
			</table>
		</div>
		
		<div style="clear:both; "></div>
		
		<div style="width:620px; border:0px solid #000; float:right; margin:3px 0px 0 0;">
		<style>
			.sig{ font-size:11px; width:100%; float:right; text-align:center;}
			.sig td{ width:155px;}
		</style>
			<table border="0" class="sig">
				<tr> <td> Disetujui : </td> <td> Diketahui : </td> <td> Dibayar Oleh : </td> <td> Yang Menerima : </td> </tr>
			</table> <br> <br> <br> <br> <br> 
			
			<table border="0" class="sig">
				<tr> <td> Direktur </td> <td> Accounting </td> <td> ( <?php echo $user; ?> ) </td> <td> ( <?php echo $employee ?> ) </td> </tr>
			</table>
		</div>
		
		<!--<div style="float:right;">
			
			<table>
				<p> &nbsp; &nbsp; Dipesan Oleh, &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Disetujui Oleh, </p> <br> <br> <br> <br>
				<p style="text-align:right;"> ( <?php echo $user; ?> ) &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (_______________) </p>
				<p> &nbsp; &nbsp; &nbsp; Purchasing  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  Direktur Utama </p>
			</table>
			<br>
		</div> -->
		
		<div style="clear:both; ">
		
	</div>	
	
</div>

</body>
</html>
