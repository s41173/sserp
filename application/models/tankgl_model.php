<?php

class Tankgl_model extends DataMapper
{
//   var $has_one = array("country");
   var $table = "tank_gl";
//   var $has_many = array("tank_ledger");
   var $auto_populate_has_many = TRUE;
   var $auto_populate_has_one = TRUE;

   function __construct($id = NULL)
   {
      parent::__construct($id);
   }

}

/* End of file user.php */
/* Location: ./application/models/user.php */