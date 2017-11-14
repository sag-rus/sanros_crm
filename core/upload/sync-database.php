<?php

function sync_server_database($connect){
  global $directory;

  $insert_records = 1;

  $tables = array("object", "room", "housing", "date_price", "ranges", "price", "rate_plan");//, "date_price", "ranges", "price"
  // $tables = array("object", "room", "housing", "rate_plan");
  $file = $directory."/core/sync/file/dump.txt";
  $fp = fopen($file, "w");

  foreach($tables as $table){
  	$query = "";
  	$result = $connect->getRow("SHOW CREATE TABLE `".$table."`");
  	if($table == "object"){
  		unset($result["image"]);
  		unset($result["service_info"]);
  	}
  	$query = "\nDROP TABLE IF EXISTS `".$table."`;\n".$result["Create Table"].";\n";
  	fwrite($fp, $query);
  	$query = "";
  	$query_ins = "\nINSERT INTO `".$table."` VALUES ";
  	fwrite($fp, $query_ins);
  	$i = 1;
  	$row_insert = $connect->getAll("SELECT * FROM `".$table."`");
  	foreach($row_insert as $row){

  		if($table == "object"){
  			$row["image"] = "";
  			$row["service_info"] = "";
  		}

  		$query = "";
  		foreach($row as $field){
  			if(is_null($field))
  				$field = "NULL";
  			else
  				$field = "'".$connect->escapeString($field)."'";
  			if($query == "")
  				$query = $field;
  			else
  				$query = $query.", ".$field;
  		}
  		if($i > $insert_records){
  			$query_ins = ";\nINSERT INTO `".$table."` VALUES ";
  			fwrite($fp, $query_ins);
  			$i = 1;
  		}
  		if($i == 1)
  			$q = "(".$query.")";
  		else
  			$q=",(".$query.")";
  		fwrite($fp, $q);
  		$i++;
  	}
  	fwrite($fp, ";\n");
  }

  	fclose($fp);

  	$name = "dump-base";

  	$connect_server = connect_to_server_directory();
  	$server_file = "/var/www/default-site/public_html/sync/file/".$name.".txt";

  	if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
  		echo "Не удалось загрузить файл на сервер";
  	//ftp_chmod($connect_server, 0777, $server_file);
  	ftp_quit($connect_server);

  	$data = request_to_sync(array("func" => "imports_mysql_base", "name" => $name));
    return $data;

}

?>
