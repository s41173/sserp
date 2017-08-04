<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="shortcut icon" href="<?php echo base_url().'images/fav_icon.png';?>" >
<title> <?php echo isset($title) ? $title : ''; ?>  </title>
<style media="all">
	table{ font-family:"Arial", Times, serif; font-size:9pt;}
	table th{ font-family:arial; font-size:9pt;}
	h4{ font-family:"Arial", Times, serif; font-size:14pt; font-weight:600; margin:0;}
	.clear{clear:both;}
	table th{ background-color:#000; color:#fff; padding:4px 0px 4px 0px; border-top:1px solid #000000; border-bottom:1px solid #000000;}
    p{ font-family:"Arial", Times, serif; font-size:12px; margin:0; padding:0;}
	legend{font-family:"Arial", Times, serif; font-size:13px; margin:0; padding:0; font-weight:600;}
	.tablesum{ font-size:13px;}
	.strongs{ font-weight:normal; font-size:12px; border-top:1px dotted #000000; border-right:1px dotted #000; text-transform:capitalize; }
	.poder{ border-bottom:0px solid #000000; color:#0000FF;}
	.red{ color:#00C; font-weight:bold; font-size:9pt;}
</style>
</head>

<body onLoad="">

<div style="width:100%; border:0px solid blue; font-family:Arial, Helvetica, sans-serif; font-size:12px;">
	
	<div style="border:0px solid red; float:left;">
		<table border="0">
            <tr> <td> Period </td> <td> : </td> <td> <?php echo get_month($month).' - '.$year; ?> </td> </tr>
            <tr> <td> Run Date </td> <td> : </td> <td> <?php echo date('d-m-Y'); ?> </td> </tr>
			<tr> <td> Log </td> <td> : </td> <td> <?php echo $log; ?> </td> </tr>
		</table>
	</div>

	<center>
	   <div style="border:0px solid green; width:500px;">	
	       <h4> <?php echo isset($company) ? $company : ''; ?> </h4>
           <p style="margin:5px; padding:0;"> <?php echo $address; ?> <br> Telp. <?php echo $phone1.' - '.$phone2; ?> <br>
               Website : <?php echo $website; ?> &nbsp; &nbsp; Email : <?php echo $email; ?> </p>
	   </div>
	</center> <hr>
    
    <p style="text-align:center; font-size:14pt; font-weight:bold;"> Payroll Sheet </p>
	
	<div class="clear"></div>
	
	<div style="width:100%; border:0px solid brown; margin-top:20px; border:1px solid #000; ">
	
		<table border="0" width="100%">
		   <tr>
  	       <th> No </th> <th> Division </th> <th> Employee </th> <th> Dept </th> <th> Amount </th> <th> Signature </th>
		   </tr>
		    
		  <?php 
		  
		  	  function employee($val)
			  {
				  $emp = new Employee_lib();
				  if ($val == 0) { $res = 'Non'; } else { $res = $emp->get_name($val); } return $res;
			  }	
			  
			  function dept($val)
			  { 
//			    $dept = new Dept_lib(); 
//				if ($val == 0) { $res = 'Non'; }else { $res = $dept->get_name($val); } return $res;
                  return '';
			  }
			  
			  function division($val)
			  {
				  $division = new Division_lib();
				  $employee = new Employee_lib();
				  $division_id = $employee->get_division_by_id($val);
				  return $division->get_name($division_id);
		      }
			  		  		  
		      $i=1; 
			  if ($results)
			  {
				 $tot_salary = 0; 
				 $tot_experience = 0; 
				 $tot_consumption = 0; 
				 $tot_transport = 0; 
				 $tot_overtime = 0; 
				 $tot_bonus = 0; 
				 $tot_principal = 0; 
				 $tot_principal_helper = 0; 
				 $tot_head = 0; 
				 $tot_home = 0; 
				 $tot_picket = 0; 
				 $tot_loan = 0; 
				 $tot_late = 0; 
				 $tot_tax = 0; 
				 $tot_insurance = 0; 
				 $tot_other = 0; 
				 $tot_amount = 0; 
				  
				foreach ($results as $res)
				{	
				   echo " 
				   <tr> 
				       <td class=\"strongs\" align=\"center\">".$i."</td> 
					   <td class=\"strongs\">".division($res->employee_id)."</td>
					   <td class=\"strongs\">".employee($res->employee_id)."</td>
					   <td class=\"strongs\" align=\"righ\">".dept($res->dept)."</td>
					   <td class=\"strongs\" align=\"right\">".number_format($res->amount)."</td>
					   <td class=\"strongs\" align=\"center\"></td>
				   </tr>";
				   $i++;
				   
				   // total
				   $tot_salary = $tot_salary + $res->basic_salary;
				   $tot_experience = $tot_experience + $res->experience;
				   $tot_consumption = $tot_consumption + $res->consumption;
				   $tot_transport = $tot_transport + $res->transportation;
				   $tot_overtime = $tot_overtime + $res->overtime;
				   $tot_bonus = $tot_bonus + $res->bonus;
				   $tot_principal = $tot_principal + $res->principal;
				   $tot_principal_helper = $tot_principal_helper + $res->principal_helper;
				   $tot_head   = $tot_head + $res->head_department;
				   $tot_home   = $tot_home + $res->home_room;
				   $tot_picket = $tot_picket + $res->picket;
				   $tot_loan   = $tot_loan + $res->loan;
				   $tot_late   = $tot_late + $res->late;
				   $tot_tax    = $tot_tax + $res->tax;
				   $tot_insurance = $tot_insurance + $res->insurance;
				   $tot_other = $tot_other + $res->other_discount;
				   $tot_amount = $tot_amount + $res->amount;
				}
			  }  
		  ?>
        
        <tr>  
        <td colspan="3"></td>
        <td class="strongs red" align="right"> Total : </td>
        <td class="strongs red" align="right"> <?php echo number_format(isset($tot_amount) ? $tot_amount : 0); ?> </td>
        </tr>  
          
		</table>
	</div>

</div>
<a style="float:left; margin:10px;" title="Back" href="<?php echo site_url('payroll_trans/get/'.$payid); ?>"> 
  <img src="<?php echo base_url().'images/back.png'; ?>"> 
</a>
</body>
</html>
