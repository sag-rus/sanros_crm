<?php

session_start();
$func = $_GET["func"];

if($func && function_exists($func)){
	$result = $func();
	echo $result;
}

function upload_image(){
	$image = str_replace(" ", "_", $_FILES["file"]["tmp_name"]);
	if($image AND $_FILES["file"]["type"] == "image/jpeg"){
		$type = $_GET["type"];
		if($type == "object"){
			list($width, $height, $type, $attr) = getimagesize($image);
			if($width < 840)
				return 2;
			if(($width / $height) < 4/3)
				return 3;
		}
		if($type == "method"){
			list($width, $height, $type, $attr) = getimagesize($image);
			if($width < 400)
				return 2;
		}
		if($type == "region" OR $type == "direction"){
			list($width, $height, $type, $attr) = getimagesize($image);
			if($width < 400)
				return 2;
		}
		$folder = "../temp/upload/";
		$new_file = basename(uniqId()).".jpg";
		$new_image = $folder.$new_file;

		if(is_uploaded_file($image)){
			if(move_uploaded_file($image, $new_image)){
				$_SESSION["new_photo"] = "photo/temp/".$new_file;
				return "temp/upload/".$new_file;
			}
		}else
			return 1;
	}else
		return 0;
	unlink($image);
}

function upload_profile_image(){
	$image = str_replace(" ", "_", $_FILES["file"]["tmp_name"]);
	if($image AND $_FILES["file"]["type"] == "image/jpeg"){
		list($width, $height, $type, $attr) = getimagesize($image);
		if($width < 100 AND $height < 100)
			return 2;
		$folder = "../temp/upload/";
		$new_file = basename(uniqId()).".jpg";
		$new_image = $folder.$new_file;

		if(is_uploaded_file($image)){
			if(move_uploaded_file($image, $new_image)){
				$_SESSION["new_photo"] = "temp/upload/".$new_file;
				return "temp/upload/".$new_file;
			}
		}else
			return 1;
	}else
		return 0;
	unlink($image);
}

function upload_document(){
	$document = str_replace(" ", "_", $_FILES["file"]["tmp_name"]);
	if($document){
		$folder = "../temp/upload/";
		$new_file = basename($_FILES["file"]["name"]);
		$new_document = $folder.$new_file;

		if(is_uploaded_file($document)){
			if(move_uploaded_file($document, $new_document)){
				$_SESSION["new_photo"] = "photo/temp/".$new_file;
				return "temp/upload/".$new_file;
			}
		}else
			return 1;
	}
	unlink($document);
}

function upload_schet(){
	include_once("functions.php");
	$file = $_FILES["file"]["tmp_name"];
	if($file){
		$papka = "../temp/upload/";
		$new_file = $papka.get_translit(str_replace(" ", "_", $_FILES["file"]["name"]));

		if(is_uploaded_file($file)){
			if(move_uploaded_file($file, $new_file))
				return $new_file;
		}
	}
	return 0;
}

?>
