<?php

function upload_sights_on_server($connect){
	global $directory;
	$connect_server = connect_to_server();
	//if($connect_server == 1)
	//	return "Ошибка соединения";
	//if($connect_server == 2)
	//	return "Не удалось авторизироваться";

	$file = $directory."/temp/sights.xml";
	$xml = new DomDocument("1.0", "utf-8");
	$sights = $xml->appendChild($xml->createElement("sights"));
	$data = $connect->getAll("SELECT id, name, description, latitude, longitude, address FROM sights");
	foreach($data as $row){
		$id = $row["id"];
		$sight = $sights->appendChild($xml->createElement("sight"));
		$sight->setAttribute("id", $row["id"]);
		$sight->setAttribute("name", $row["name"]);
		$sight->setAttribute("latitude", $row["latitude"]);
		$sight->setAttribute("longitude", $row["longitude"]);
		$sight->setAttribute("address", $row["address"]);
		$sight->appendChild($xml->createTextNode($row["description"]));
	}

	$xml->formatOutput = true;
	$xml->save($file);

	$ftp_folder = "/var/www/default-site/public_html/price/image/sights";
	$local_dir = $directory."/temp/sights";
	do_upload_images($connect_server, $local_dir, $ftp_folder);

	$server_file = "/var/www/default-site/public_html/price/XML/overall/sights.xml";
	if(!ftp_put($connect_server, $server_file, $file, FTP_ASCII))
		return "Не удалось загрузить файл на сервер";
	ftp_chmod($connect_server, 0644, $server_file);
	ftp_quit($connect_server);
	return "<div class='alert alert-success'><i class='fa fa-check-square-o'></i> Загрузка завершена!</div>";
}

?>
