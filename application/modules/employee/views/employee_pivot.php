<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="shortcut icon" href="<?php echo base_url().'images/fav_icon.png';?>" >
<title> <?php echo isset($title) ? $title : ''; ?>  </title>

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
    
    <p style="text-align:center; font-size:14pt; font-weight:bold;"> Employee Pivot Table </p>
	
	<div class="clear"></div>
	
	<div style="width:100%; border:0px solid brown; margin-top:20px; ">
	
    	<div id='output'>
        <div style='margin-top: 30px;' id="jqxgrid"> </div>
        </div>
    
		<table id="input" border="0" width="100%" style="visibility:hidden;">
		   
           <thead>
           <tr>
  	       <th> No </th> <th> NIP </th> <th> Att Code </th> <th> Name </th> <th> Type </th> <th> Period </th> <th> Division </th> <th> Role </th> 
           <th> Department </th> <th> Gender </th> 
           <th> Religion </th> <th> Date Of Birth </th> <th> Marital Status </th> <th> Phone </th> <th> Mobile </th> 
           <th> Email </th> <th> Acc-No </th> <th> Joined </th> <th> Resign </th> <th> Subject </th> <th> Time Work </th> <th> Status </th>
		   </tr>
           </thead>
		    
          <tbody>  
		  <?php 
		  
		  	  function dept($val)
			  {
//				  $dept = new Dept_lib();
//				  if ($val == 0) { $res = 'General'; } else { $res = $dept->get_name($val); } return $res;
                  return '';
			  }	
			  
			  function division($val)
			  {
				  $division = new Division_lib();
				  if ($val == 0) { $res = 'Non Division'; } else { $res = $division->get_name($val); } return $res;
			  }	
			  
			  function estatus($val){ if ($val == 0){ return 'Non Active'; } else { return 'Active'; } }
			  
			  function genre($val){ if($val == 'm'){return 'Male';}else{ return 'Female'; }}
			  
			  function set_null($val){ if ($val){ return tglin($val); }else { return '-'; } }
			  
			  function marital($val)
			  { if($val == 'yes'){ $res = 'Married'; }elseif($val == 'no'){ $res = 'Not Married';}else{ $res = 'No Status';} return $res; }
			  
			  function time_work($val)
			  {
				$now = date('Y');  
				if ($val){ return intval($now-split_date($val,'Y')); }
				else { return '-'; }  
			  }
		  		  
		      $i=1; 
			  if ($results)
			  {
				foreach ($results as $res)
				{	
				   echo " 
				   <tr> 
				       <td class=\"strongs\">".$i."</td> 
					   <td class=\"strongs\">".$res->nip."</td>
					   <td class=\"strongs\">".$res->attcode."</td>
					   <td class=\"strongs\">".$res->first_title.' '.$res->name.' '.$res->end_title."</td>
					   <td class=\"strongs\" align=\"center\">".$res->type."</td>
					   <td class=\"strongs\" align=\"center\">".$res->work_time."</td>
					   <td class=\"strongs\">".division($res->division_id)."</td>
					   <td class=\"strongs\">".ucfirst($res->role)."</td>
					   <td class=\"strongs\">".dept($res->dept_id)."</td>
					   <td class=\"strongs\">".genre($res->genre)."</td>
					   <td class=\"strongs\">".$res->religion."</td>
					   <td class=\"strongs\">".$res->born_place.' , '.tglincomplete($res->born_date)."</td>
					   <td class=\"strongs\">".marital($res->status)."</td>
					   <td class=\"strongs\">".$res->phone."</td>
					   <td class=\"strongs\">".$res->mobile."</td>
					   <td class=\"strongs\">".$res->email."</td>
					   <td class=\"strongs\">".$res->acc_no."</td>
					   <td class=\"strongs\">".tglin($res->joined)."</td>
					   <td class=\"strongs\">".set_null($res->resign)."</td>
					   <td class=\"strongs\">".$res->subject."</td>
					   <td class=\"strongs\">".time_work($res->joined)."</td>
					   <td class=\"strongs\">".estatus($res->active)."</td>
				   </tr>";
				   $i++;
				}
			  }  
			  
		  ?>
         
        </tbody>  
		</table>
	</div>

</div>
<a style="float:left; margin:10px;" title="Back" href="<?php echo site_url('employee'); ?>"> 
  <img src="<?php echo base_url().'images/back.png'; ?>"> 
</a>
</body>
</html>
