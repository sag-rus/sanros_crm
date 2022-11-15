<?php

function save_file_1C_sync($connect, $id){


	$array = array(
		array("document", "N", 2, 0),
		array("id", "C", 8, 0),
		array("date", "D"),
		array("post_name", "C", 100),
		array("post_code", "C", 100),
		array("post_inn", "C", 100),
		array("group", "C", 8),
		array("nom", "C", 100),
		array("nom_name", "C", 100),
		array("number", "N", 2, 0),
		array("sum", "N", 10, 2)
	);

	$filename = "temp/1C/CRM.dbf";
	if (file_exists($filename)) unlink($filename);

	if(!$DBF = dbase_create($filename, $array)) {
		// echo 'Невозможно создать файл'; exit;
	}

	$index = 0;
	$data = array_unique(explode("-", $id));
	foreach($data as $id){
		if($id){
			$row = $connect->getRow("SELECT id, sum, DATE_FORMAT(date_z, '%Y%m%d') as date, id_obj, id_tour, agency, payer, turist FROM reckoning WHERE id=?i", $id);
			if($row["id"]){
				if($row["id_tour"]){
					$tour = $connect->getRow("SELECT 1C_full_name, inn, 1C_code FROM tour_operator WHERE id=?i", $row["id_tour"]);
					$full_name = mb_convert_encoding($tour["1C_full_name"], "windows-1251", "UTF-8");
					$inn = $tour["inn"];
					$code = $tour["1C_code"];
				}else{
					$object = $connect->getRow("SELECT 1C_full_name, inn, 1C_code FROM object WHERE id=?i", $row["id_obj"]);
					$full_name = mb_convert_encoding($object["1C_full_name"], "windows-1251", "UTF-8");
					$inn = $object["inn"];
					$code = $object["1C_code"];
				}

				$length = strlen($row["id"]);
				if($length < 6){
					$add = "";
					for($i = 1; $i <= (6 - $length); $i++)
						$add.= "0";
					$row["id"] = $add.$row["id"];
				}

				$object_name = $connect->getOne("SELECT name FROM object WHERE id=?i", $row["id_obj"]);
				$nomenclature_name = mb_convert_encoding($object_name, "windows-1251", "UTF-8");
				$nomenclature = $connect->getOne("SELECT nomenclature FROM object WHERE id=?i", $row["id_obj"]);
				$name = convert_cyr_string($full_name, "w", "d");
				$nomenclature_name = convert_cyr_string($nomenclature_name, "w", "d");
				dbase_add_record($DBF, array(1, $row["id"], $row["date"], $name, $code, $inn, "", $nomenclature, $nomenclature_name, 1, $row["sum"]));
				$code = "";
				$group = "";
				if($row["agency"]){
					$agency = $connect->getRow("SELECT name, inn, 1C_code FROM agency WHERE id=?i", $row["agency"]);
					$full_name = mb_convert_encoding($agency["name"], "windows-1251", "UTF-8");
					$inn = $agency["inn"];
					$code = $agency["1C_code"];
				}elseif($row["payer"]){
					$payer = $connect->getRow("SELECT name, inn, type, 1C_code FROM payer WHERE id=?i", $row["payer"]);
					$full_name = mb_convert_encoding(str_replace("  ", " ", $payer["name"]), "windows-1251", "UTF-8");
					$inn = $payer["inn"];
					$code = $payer["1C_code"];
					if($payer["type"] == 1){
						$group = "00000029";
						$code = "";
					}
				}else{
					$payer = $connect->getRow("SELECT surname, name, otch FROM klient WHERE id=?i", $row["turist"]);
					$full_name = mb_convert_encoding(str_replace("  ", " ", $payer["surname"]." ".$payer["name"]." ".$payer["otch"]), "windows-1251", "UTF-8");
					$group = "00000029";
					$code = "";
				}
				$name = convert_cyr_string($full_name, "w", "d");
				dbase_add_record($DBF, array(2, $row["id"], $row["date"], $name, $code, $inn, $group, $nomenclature, $nomenclature_name, 1, $row["sum"]));
			}
		}
	}

	dbase_close($DBF);

	header("Content-Description: File Transfer");
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=CRM.dbf");
	header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Cache-Control: must-revalidate");
	header("Pragma: public");
	header("Content-Length: ".filesize($filename));
	readfile($filename);

	// file_put_contents($filename, "");
}

?>
