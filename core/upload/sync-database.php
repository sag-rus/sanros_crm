<?php

function sync_server_database($connect){
  $directory = __DIR__."/../..";

  $insert_records = 1;

  $tables = array("object", "room", "housing", "date_price", "ranges", "price", "rate_plan");//, "date_price", "ranges", "price"
  // $tables = array("object", "room", "housing", "rate_plan");
  $file = $directory."/core/sync/file/dump.txt";
  $file2 = $directory."/core/sync/file/dump2.txt";
  $file3 = $directory . '/core/sync/file/dump3.txt';
  $fp = fopen($file, "w");
  $fp2 = fopen($file2, "w");
  $fp3 = fopen($file3, "w");

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
			$rid = $row['id'];
  		if($table == "object"){
  			$row["image"] = "";
  			$row["service_info"] = "";
  		}

  		$query = "";
  		foreach($row as $field){
  			if(is_null($field))
  				$field = "NULL";
  			else
  				$field = "'".addslashes(str_replace([";\n",";\r"],"; ",$field))."'";
  			if($query == "")
  				$query = $field;
  			else
  				$query = $query.", ".$field;
  		}
  		if($i > $insert_records){
  			$query_ins = ";\nINSERT INTO `".$table."` VALUES ";

				if($table !== 'room' || $rid < 3000) {
					fwrite($fp, $query_ins);

				}
				elseif ($rid < 6000) {
					fwrite($fp2,$query_ins);
				}
				else {
					fwrite($fp3,$query_ins);
				}
  			$i = 1;



  		}
  		if($i == 1)
  			$q = "(".$query.")";
  		else
  			$q=",(".$query.")";
			if($table !== 'room' || $rid < 3000) {
				fwrite($fp, $q);

			}
			elseif ($rid < 6000) {
				fwrite($fp2,$query_ins);
			}
			else {
				fwrite($fp3, $q);
			}

  		$i++;
  	}
  	fwrite($fp, ";\n");
		fwrite($fp2, ";\n");
	  fwrite($fp3, ";\n");
  }

  	fclose($fp);
  	fclose($fp2);
  	fclose($fp3);

  	$name = "dump-base";

  	$connect_server = connect_to_server_directory();
  	$server_file = "/var/www/default-site/public_html/sync/file/".$name.".txt";

  	if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
  		echo "Не удалось загрузить файл на сервер";
  	ftp_chmod($connect_server, 0777, $server_file);

		$data = request_to_sync(array("func" => "imports_mysql_base", "name" => $name));


		$name = "dump-base2";

		$server_file = "/var/www/default-site/public_html/sync/file/".$name.".txt";

		if(!ftp_put($connect_server, $server_file, $file2, FTP_ASCII))
			echo "Не удалось загрузить файл на сервер";
		ftp_chmod($connect_server, 0777, $server_file);
		ftp_quit($connect_server);

  	$data = request_to_sync(array("func" => "imports_mysql_base", "name" => $name));


	$name = "dump-base3";

	$server_file = "/var/www/default-site/public_html/sync/file/".$name.".txt";

	if(!ftp_put($connect_server, $server_file, $file3, FTP_ASCII))
		echo "Не удалось загрузить файл на сервер";
	ftp_chmod($connect_server, 0777, $server_file);
	ftp_quit($connect_server);

	$data = request_to_sync(array("func" => "imports_mysql_base", "name" => $name));

    return '<div class="alert alert-success">Выгрузка завершена</div>';

}

?>
