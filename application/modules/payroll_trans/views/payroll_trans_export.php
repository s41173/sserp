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
	.strongs{ font-weight:normal; font-size:12px; border-top:1px dotted #000000; border-right:1px dotted #000; }
	.poder{ border-bottom:0px solid #000000; color:#0000FF;}
	.red{ color:#00C; font-weight:bold; font-size:9pt;}
</style>

<!-- jqgrid -->

    <link rel="stylesheet" href="<?php echo base_url().'js-old/jxgrid/' ?>css/jqx.base.css" type="text/css" />
    
	<script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxdata.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxcheckbox.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxscrollbar.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxlistbox.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxdropdownlist.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxmenu.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.sort.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.filter.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.columnsresize.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.columnsreorder.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.selection.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.pager.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.aggregates.js"></script>
    <script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxdata.export.js"></script>
	<script type="text/javascript" src="<?php echo base_url().'js-old/jxgrid/' ?>js/jqxgrid.export.js"></script>
    
     <script type="text/javascript">
        $(document).ready(function () {
          
			var rows = $("#table tbody tr");
                // select columns.
                var columns = $("#table thead th");
                var data = [];
                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];
                    var datarow = {};
                    for (var j = 0; j < columns.length; j++) {
                        // get column's title.
                        var columnName = $.trim($(columns[j]).text());
                        // select cell.
                        var cell = $(row).find('td:eq(' + j + ')');
                        datarow[columnName] = $.trim(cell.text());
                    }
                    data[data.length] = datarow;
                }
                var source = {
                    localdata: data,
                    datatype: "array",
                    datafields:
                    [
                        { name: "Acc-No", type: "string" },
                        { name: "Acc-Name", type: "string" },
                        { name: "Cur", type: "string" },
						{ name: "Amount", type: "string" },
                        { name: "Notes", type: "string" },
						{ name: "Count", type: "string" },
						{ name: "Date", type: "string" },
						{ name: "Email", type: "string" }
                    ]
                };
			
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#jqxgrid").jqxGrid(
            {
                width: '100%',
				source: dataAdapter,
				sortable: true,
				filterable: true,
				pageable: true,
				altrows: true,
				enabletooltips: true,
				filtermode: 'excel',
				autoheight: true,
				columnsresize: true,
				columnsreorder: true,
				showstatusbar: true,
				statusbarheight: 30,
				showaggregates: true,
				autoshowfiltericon: false,
                columns: [
                  { text: 'Acc-No', dataField: 'Acc-No', width: 200 },
				  { text: 'Acc-Name', dataField: 'Acc-Name', width: 200 },
				  { text: 'Cur', dataField: 'Cur', width: 100 },
				  { text: 'Amount', dataField: 'Amount', width: 120, cellsalign: 'right' },
				  { text: 'Notes', dataField: 'Notes' },
				  { text: 'Count', dataField: 'Count', width: 90 },
				  { text: 'Date', dataField: 'Date', width: 100 },
				  { text: 'Email', dataField: 'Email', width: 150 },
                ]
            });
			
			$('#jqxgrid').jqxGrid({ pagesizeoptions: ['50', '100', '250', '500', '1000', '2000', '3000']}); 
			
			$("#bexport").click(function() {
				
				var type = $("#crtype").val();	
				if (type == 0){ $("#jqxgrid").jqxGrid('exportdata', 'html', 'CSV-Export'); }
				else if (type == 1){ $("#jqxgrid").jqxGrid('exportdata', 'xls', 'CSV-Export'); }
				else if (type == 2){ $("#jqxgrid").jqxGrid('exportdata', 'pdf', 'CSV-Export'); }
				else if (type == 3){ $("#jqxgrid").jqxGrid('exportdata', 'csv', 'CSV-Export'); }
			});
			
			$("#table").hide();
			
        });
    </script>

</head>

<body onLoad="">

<div style="width:100%; border:0px solid blue; font-family:Arial, Helvetica, sans-serif; font-size:12px;">
	
	<div style="border:0px solid red; float:left;">
		<table border="0">
            <tr> <td> Period </td> <td> : </td> <td> <?php echo get_month($month).' - '.$year; ?> </td> </tr>
            <tr> <td> Currency </td> <td> : </td> <td> <?php echo $cur.' / '.$payment; ?> </td> </tr>
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
    
    <p style="text-align:center; font-size:14pt; font-weight:bold;"> Payroll Transaction Export </p>
	
	<div class="clear"></div>
	
	<div style="width:100%; border:0px solid brown; margin:20px 0px 0px 10px; ">
    
    	<div id='jqxWidget'>
        <div style='margin-top: 30px;' id="jqxgrid"> </div>
        
        <table style="float:right; margin:5px;">
        <tr>
        <td> <input type="button" id="bexport" value="Export"> - </td>
        <td> 
        <select id="crtype"> <option value="0"> HTML </option> <option value="1"> XLS </option>  <option value="2"> PDF </option> 
        <option value="3"> CSV </option> 
        </select>
        </td>
        </tr>
        </table>
        
        </div>
	
		<table id="table" border="0" width="100%">
		   
           <thead>
           <tr>
           <th> Acc-No </th> <th> Acc-Name </th> <th> Cur </th> <th> Amount </th> <th> Notes </th> <th> Count </th> <th> Date </th> <th> Email </th>
           </tr>
           </thead>
		    
          <tbody> 
          
          <tr>
           <td align="left"><?php echo $accno; ?></td>
           <td align="left"><?php echo $accname; ?></td>
           <td align="center"><?php echo $cur; ?></td>
           <td align="right"><?php echo $amount; ?></td>
           <td align="left"><?php echo $notes; ?></td>
           <td align="center"><?php echo $count; ?></td>
           <td align="left"><?php echo $dates; ?></td>
           <td align="left"><?php echo $email; ?></td>
           </tr>
           
		  <?php 
		  
		  	  function employee($val)
			  {
				  $emp = new Employee_lib();
				  if ($val == 0) { $res = 'Non'; } else { $res = $emp->get_name($val); } return $res;
			  }	
			  
			  function acc_no($val)
			  {
				 $employee = new Employee_lib();
				 return $employee->get_acc_no($val); 
			  }
			  		  		  
		      $i=1; 
			  if ($results)
			  {
				  
				foreach ($results as $res)
				{	
				   echo " 
				   <tr> 
				       <td align=\"left\">".acc_no($res->employee_id)."</td> 
					   <td>".strtoupper(employee($res->employee_id))."</td>
					   <td align=\"center\">".$cur."</td>
					   <td align=\"right\">".$res->amount."</td>
					   <td align=\"left\">".$notes."</td>
					   <td align=\"center\"></td>
					   <td align=\"left\">".$dates."</td>
					   <td align=\"left\">".$i."</td>
				   </tr>";
				   $i++;
				}
			  }  
		  ?>
         
        </tbody>  
		</table>
	</div>

</div>
<a style="float:left; margin:10px;" title="Back" href="<?php echo site_url('payroll_trans/get/'.$payid); ?>"> 
  <img src="<?php echo base_url().'images/back.png'; ?>"> 
</a>
</body>
</html>
