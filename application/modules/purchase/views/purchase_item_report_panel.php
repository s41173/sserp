<div class="modal-dialog">
        
<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"> Purchase Item - Report </h4>
</div>
<div class="modal-body">
 
 <!-- form add -->
<div class="x_panel" >
<div class="x_title">
  
  <div class="clearfix"></div>
</div>
<div class="x_content">

<?php
    
$atts1 = array(
	  'class'      => 'btn btn-primary button_inline',
	  'title'      => 'Product - List',
	  'width'      => '800',
	  'height'     => '600',
	  'scrollbars' => 'yes',
	  'status'     => 'yes',
	  'resizable'  => 'yes',
	  'screenx'    =>  '\'+((parseInt(screen.width) - 800)/2)+\'',
	  'screeny'    =>  '\'+((parseInt(screen.height) - 600)/2)+\'',
);

?>    
    
<form id="" data-parsley-validate class="form-horizontal form-label-left" method="POST" 
action="<?php echo $form_action_product; ?>" enctype="multipart/form-data">
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Product </label>
        <div class="col-md-8 col-sm-9 col-xs-12">     
        <table>
            <tr>
            <td> <input id="titem" class="form-control col-md-3 col-xs-12" type="text" readonly name="titem" required> </td>
            <td> <?php echo anchor_popup(site_url("product/get_list/titem/"), '[ ... ]', $atts1); ?> </td>
            </tr>
        </table>    
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Currency </label>
        <div class="col-md-3 col-sm-12 col-xs-12">     
            <?php $js = "class='form-control' id='ccur' tabindex='-1' style='min-width:170px;' "; 
            echo form_dropdown('ccurrency', $currency, isset($default['currency']) ? $default['currency'] : '', $js); ?>
        </div>
    </div>
    
     <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"> Period </label>
        <div class="col-md-9 col-sm-9 col-xs-12">     
<input type="text" readonly style="width: 200px" name="reservation" id="d1" class="form-control active" value=""> 
        </div>
    </div>

      <div class="ln_solid"></div>
      <div class="form-group">
          <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
          <div class="btn-group"> 
           <button type="submit" class="btn btn-primary">Post</button>
           <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          </div>
          </div>
      </div>
    
  </form> 
  <div id="err"></div>
</div>
</div>
<!-- form add -->

</div>
    <div class="modal-footer"> </div>
</div>
  
</div>