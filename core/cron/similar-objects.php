<?php

  $directory = dirname(__FILE__)."/../..";

  include_once($directory."/core/functions.php");
  include_once($directory."/core/lib/Mysql.Class.php");
  include_once($directory."/core/class/object.class.php");

  $connect = connect_to_MySQL_directory();

  $similar_class = new Object_CRM($connect);
  $data = $connect->getAll("SELECT id FROM object WHERE (similar is NULL OR similar='') AND active!=2 AND id_reg!=''");
  foreach($data as $row){
    $id = $row["id"];
    echo $id." ";
  	$similar = $similar_class->similar_objects($id);
    $similar_string = "";
    if(count($similar) > 0)
      $similar_string = implode("_", $similar);
    if($similar_string != "")
  	 $connect->query("UPDATE object SET similar=?s WHERE id=?i", $similar_string, $id);
  }

?>
