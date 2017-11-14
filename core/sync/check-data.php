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
	$tables = array("object", "room", "housing", "date_price", "ranges", "price", "rate_plan");//, "date_price", "ranges", "price"
	$tables = array("object", "room", "housing");
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
					$field = "'".$field."'";
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
	ftp_chmod($connect_server, 0777, $server_file);
	ftp_quit($connect_server);

	$data = request_to_sync(array("func" => "imports_mysql_base", "name" => $name));

?>
