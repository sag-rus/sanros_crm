<?php
//return;
	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/config.php");
	$conf = new JConfig;
	$sync = $conf->sync_base;
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	include_once($directory."/core/sync/API/object.php");

	$connect = connect_to_MySQL_directory();

/*	$data = request_to_sync(array("func" => "check_data_sync"));
	foreach($data["update"] as $row){
		$query = json_decode($row["data"], TRUE);
		$func = $query["func"];
		if(function_exists($func)){
			$func($connect, $query);
		}
	}
*/
//	if(!$data["today_rest"]){
		//$date = date("Y-m-d");
		//$rest = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date_z<=?s AND date_v>?s AND status=5", $date, $date);
		//request_to_sync(array("func" => "update_const", "value" => $rest, "type" => "today_rest", "date" => $date));
//	}

	$insert_records = 1;

	//$tables = array("methods", "profile", "infa", "comfort", "type_object", "region", "object", "room", "services", "housing", "date_price", "ranges", "price", "place", "object_room", "reservation");

	$tables = array("object", "room", "housing", "date_price", "ranges", "price", "rate_plan");//, "date_price", "ranges", "price"
	$tables = array("object", "room", "housing");
	//$tables = array("booking_module_object");
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
/*		if($table == "object" AND $row["id"] == 658){
			$row["description"] = "";
			$row["id_service"] = "";
echo 1;
		}*/
		$query = "";
		foreach($row as $field){
			if(is_null($field))
				$field = "NULL";
			else
				$field = "'".mysql_escape_string( $field )."'";
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


/*	$result = "CREATE TABLE `reckoning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(2) DEFAULT '1',
  `status_san` int(2) DEFAULT '0',
  `turist` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	$query = "\nDROP TABLE IF EXISTS `reckoning`;\n".$result.";\n";
	fwrite($fp, $query);
	$query = "";
	$query_ins = "\nINSERT INTO `reckoning` VALUES ";
	fwrite($fp, $query_ins);
	$i = 1;
	$row_insert = $connect->getAll("SELECT reckoning.id, reckoning.status, reckoning.status_san, reckoning.rest FROM `reckoning`, `reservation` WHERE reckoning.id=reservation.id_reck");
	foreach($row_insert as $row){
		$query = "";
		$rest = explode(",", $row["rest"]);
		foreach($rest as $turist){
			if($turist != ""){
				$row["rest"] = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $rest[0]);
				break;
			}
		}
		foreach($row as $field){
			if(is_null($field))
				$field = "NULL";
			else
				$field = "'".mysql_escape_string( $field )."'";
			if($query == "")
				$query = $field;
			else
				$query = $query.", ".$field;
		}
		if($i > $insert_records){
			$query_ins = ";\nINSERT INTO `reckoning` VALUES ";
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

*/
/*
	$result = "CREATE TABLE `agency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `module` varchar(255),
  `module_email` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	$query = "\nDROP TABLE IF EXISTS `agency`;\n".$result.";\n";
	fwrite($fp, $query);
	$query = "";
	$query_ins = "\nINSERT INTO `agency` VALUES ";
	fwrite($fp, $query_ins);
	$i = 1;
	$row_insert = $connect->getAll("SELECT id, name, module, module_email FROM `agency`");
	foreach($row_insert as $row){
		$query = "";
		foreach($row as $field){
			if(is_null($field))
				$field = "NULL";
			else
				$field = "'".mysql_escape_string( $field )."'";
			if($query == "")
				$query = $field;
			else
				$query = $query.", ".$field;
		}
		if($i > $insert_records){
			$query_ins = ";\nINSERT INTO `agency` VALUES ";
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



*/
	fclose($fp);

	$name = "dump-base";

	$connect_server = connect_to_server_directory();
	$server_file = "/var/www/default-site/public_html/sync/file/".$name.".txt";

	if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
		echo "Не удалось загрузить файл на сервер";
	ftp_chmod($connect_server, 0777, $server_file);
	ftp_quit($connect_server);

	$data = request_to_sync(array("func" => "imports_mysql_base", "name" => $name));

?>
