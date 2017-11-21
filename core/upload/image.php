<?php

function check_folder_image($connect){
	$url = "temp/images/";
	$url_object = "temp/object/";
	$mistake = 0;
	$data = $connect->getAll("SELECT id FROM region WHERE id_country=1");
	foreach($data as $row){
		$id_reg = $row["id"];
		$url_s = $url.$id_reg;
		$res = make_folder($url_s);
		if($res){
			$data2 = $connect->getAll("SELECT id FROM object WHERE id_reg=?i AND active=0", $id_reg);
			foreach($data2 as $row){
				$id_obj = $row["id"];
				$url_s = $url.$id_reg."/".$id_obj;
				$res = make_folder($url_s);
				$url_s = $url_object.$id_obj;
				make_folder($url_s);
				$url_s = $url_object.$id_obj."/small";
				make_folder($url_s);
				$url_s = $url_object.$id_obj."/big";
				make_folder($url_s);
				$url_s = $url_object.$id_obj."/origin";
				make_folder($url_s);
				$url_s = $url_object.$id_obj."/840";
				make_folder($url_s);
				if($res){
					$data3 = $connect->getAll("SELECT id FROM room WHERE id_obj=?i AND active=0", $id_obj);
					foreach($data3 as $row){
						$id_room = $row["id"];
						$url_s = $url.$id_reg."/".$id_obj."/".$id_room;
						$res = make_folder($url_s);
						$url_s = $url.$id_reg."/".$id_obj."/".$id_room."/small";
						$res = make_folder($url_s);
						$url_s = $url.$id_reg."/".$id_obj."/".$id_room."/big";
						$res = make_folder($url_s);
						$url_s = $url.$id_reg."/".$id_obj."/".$id_room."/mobile";
						$res = make_folder($url_s);
						$url_s = $url.$id_reg."/".$id_obj."/".$id_room."/origin";
						$res = make_folder($url_s);
					}
				}
			}
		}
	}
}

function show_upload_objects($connect){
	$html = "";
	$data = $connect->getAll("SELECT id, name FROM region WHERE id_country=1");
	foreach($data as $row){
		$id_reg = $row["id"];
		$name = $row["name"];
		$object = break_columns($connect, "object", 5, "", " WHERE id_reg=$id_reg AND (active=0 OR active=1) ");
		if($object){
			ob_start();
	?>
	<div class="panel panel-default check-div region-<?php echo $id_reg; ?>">
		<div class="panel-heading">
			<i class="fa fa-caret-right"></i>
			<?php echo $name; ?>
		</div>
		<div class="panel-body">
			<?php echo $object; ?>
		</div>
		<div class="panel-footer">
			<button type="button" class="btn btn-success btn-xs" onclick="upload_image_server('<?php echo $id_reg; ?>')"><i class="fa fa-cloud-upload"></i> Загрузить на сервер</button>
		</div>
	</div>
	<?php
			$html.= ob_get_clean();
		}
	}
	return $html;
}

function upload_new_image($connect){
	$id = $_POST["id"];
	$type = $_POST["type"];
	$url = $_POST["url"];
	$cut = $_POST["cut"];

	$width_small = 100;
	$height_big = 300;
	$width_method = 400;
	if($cut == 1)
		cut_image($url, $_POST["left"], $type);
	$image = imageCreateFromJPEG($url);

	if($image){
		if($type == "room"){
			$object = $connect->getOne("SELECT id_obj FROM room WHERE id=?i", $id);
			$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $object);
			$dir = "temp/images/".$region."/".$object."/".$id;
			if(!file_exists("temp/images/".$region))
				mkdir("temp/images/".$region, 0777);
			if(!file_exists("temp/images/".$region."/".$object))
				mkdir("temp/images/".$region."/".$object, 0777);
			if(!file_exists($dir)){
				mkdir($dir, 0777);
				mkdir($dir."/small", 0777);
				mkdir($dir."/big", 0777);
				mkdir($dir."/mobile", 0777);
			}

			$file = get_next_name($dir."/small");
			$real_w = imagesx($image);
			$real_h = imagesy($image);
			$raz = ($real_h / $height_big);
			$width = round($real_w / $raz);
			image_resize($image, $dir."/big/".$file, $width, $height_big);
			$raz = ($real_w / $width_small);
			$height = round($real_h / $raz);
			image_resize($image, $dir."/small/".$file, $width_small, $height);
			$raz = ($real_w / 250);
			$height = round($real_h / $raz);
			image_resize($image, $dir."/mobile/".$file, 250, $height);
		}elseif($type == "object"){
			$file = uniqid().".jpg";
			$dir = "temp/object/".$id;

			if(!file_exists($dir)){
				mkdir($dir, 0777);
			}
			
			if(!file_exists($dir."/small")){
				mkdir($dir."/small", 0777);
			}
			
			if(!file_exists($dir."/big")){
				mkdir($dir."/big", 0777);
			}
			
			if(!file_exists($dir."/840")){
				mkdir($dir."/840", 0777);
			}

			$real_w = imagesx($image);
			$real_h = imagesy($image);
			$raz = ($real_h / $height_big);
			$width = round($real_w / $raz);
			image_resize($image, $dir."/big/".$file, $width, $height_big);
			$raz = ($real_w / $width_small);
			$height = round($real_h / $raz);
			image_resize($image, $dir."/small/".$file, $width_small, $height);
			$raz = ($real_w / 840);
			$height = round($real_h / $raz);
			image_resize($image, $dir."/840/".$file, 840, $height);
			// var_dump(error_get_last());
			unlink($photo);
		}elseif($type == "method"){
			$file = $id.".jpg";
			$dir = "temp/method/";
			$real_w = imagesx($image);
			$real_h = imagesy($image);
			$raz = ($real_w / $width_method);
			$height = round($real_h / $raz);
			image_resize($image, $dir.$file, $width_method, $height);
			unlink($photo);
		}elseif($type == "region"){
			$file = $id.".jpg";
			$dir = "temp/region/";
			$real_w = imagesx($image);
			$real_h = imagesy($image);
			$raz = ($real_w / $width_method);
			$height = round($real_h / $raz);
			image_resize($image, $dir.$file, $width_method, $height);
			unlink($photo);
		}elseif($type == "direction"){
			$file = $id.".jpg";
			$dir = "temp/direction/";
			$real_w = imagesx($image);
			$real_h = imagesy($image);
			$raz = ($real_w / $width_method);
			$height = round($real_h / $raz);
			image_resize($image, $dir.$file, $width_method, $height);
			unlink($photo);
		}elseif($type == "sight"){
			$file = uniqid().".jpg";
			$dir = "temp/sights/".$id."/";
			if(!file_exists($dir))
				mkdir($dir, 0777);
			$real_w = imagesx($image);
			$real_h = imagesy($image);
			$raz = ($real_w / $width_method);
			$height = round($real_h / $raz);
			image_resize($image, $dir.$file, $width_method, $height);
			unlink($photo);
			echo $dir.$file;
		}
		imageDestroy($image);
		unlink($url);
	}
}

function remove_image_room(){
	$folder = $_POST["folder"];
	$image = $_POST["image"];
	unlink($folder."/big/".$image);
	unlink($folder."/small/".$image);
	unlink($folder."/mobile/".$image);
}

function remove_image_object(){
	global $directory;
	$object = $_POST["object"];
	$image = $_POST["image"].".jpg";
	$folder = $directory."/temp/object/".$object;

	$image = explode('.', $image);
	array_pop($image);
	$image = implode('.', $image);

	foreach(new RecursiveDirectoryIterator($folder) as $path=>$subfolder) {
		$img_path = $path . DIRECTORY_SEPARATOR . $image; 
		if(file_exists($img_path)) unlink($img_path);
	}
// exit;
// 	unlink($folder."/big/".$image);
// 	unlink($folder."/small/".$image);
	// var_dump(error_get_last());
}


function upload_image_server(){
	global $directory;
	$region = $_POST["region"];
	$objects = explode("_", $_POST["object"]);
	$ftp_folder = "/var/www/default-site/public_html/price/images";
	$local_dir = $directory."/temp/images";
	$connect_server = connect_to_server();
	//if($connect_server == 1 OR $connect_server == 2)
	//	return "<div class='alert alert-danger'><i class='fa fa-exclamation-triangle'></i> К сожалению, произошла ошибка</div>";
	foreach($objects as $object){
		if($object){
			$local = $local_dir."/".$region."/".$object;
			$ftp = $ftp_folder."/".$region."/".$object;
			ftp_mkdir($connect_server, $ftp_folder."/".$region);
			ftp_chmod($connect_server, 0777, $ftp_folder."/".$region);
			ftp_mkdir($connect_server, $ftp_folder."/".$region."/".$object);
			ftp_chmod($connect_server, 0777, $ftp_folder."/".$region."/".$object);
			do_upload_images($connect_server, $local, $ftp);
		}
	}
	ftp_close($connect_server);
	return "<div class='alert alert-success'><i class='fa fa-picture-o'></i> Картинки загружены</div>";
}

function ftp_rdel ($connect_server, $path) {

  if (@ftp_delete ($connect_server, $path) === false) {

    if ($children = @ftp_nlist ($connect_server, $path)) {
      foreach ($children as $p)
        ftp_rdel ($connect_server,  $p);
    }

    @ftp_rmdir ($connect_server, $path);
  }
}

function upload_image_object_server($connect){
	global $directory;
	$object = $_POST["object"];
	$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $object);
	$ftp_folder = "/var/www/default-site/public_html/price/images";
	$local_dir = $directory."/temp/images";

	$local = $local_dir."/".$region."/".$object;
	$ftp = $ftp_folder."/".$region."/".$object;

	$connect_server = connect_to_server();
	if(ftp_nlist($connect_server,$ftp_folder."/".$region) == false)
	    ftp_mkdir($connect_server, $ftp_folder."/".$region);

	ftp_chmod($connect_server, 0777, $ftp_folder."/".$region);

    if(ftp_nlist($connect_server,$ftp_folder."/".$region."/".$object) == false) {
      ftp_mkdir($connect_server, $ftp_folder . "/" . $region . "/" . $object);
      ftp_chmod($connect_server, 0777, $ftp_folder."/".$region."/".$object);
    }

	do_upload_images($connect_server, $local, $ftp);

	$ftp_folder = "/var/www/default-site/public_html/price/object/images/".$object;
	$local_dir = "temp/object/".$object;

	ftp_rdel($connect_server, $ftp_folder);

  return "Test ".$local;

	if(ftp_nlist($connect_server, $ftp_folder) == false)
	    ftp_mkdir($connect_server, $ftp_folder);

	ftp_chmod($connect_server, 0777, $ftp_folder);

	if(is_dir($local_dir)) {
      $folder = opendir($local_dir);
      while($file = readdir($folder)){
        if(($file != ".") AND ($file != "..") AND ($file)){
          $local_file = $local_dir."/".$file;

          if((int)is_dir($local_file)){
            ftp_mkdir($connect_server, $ftp_folder . '/' . $file);
            ftp_chmod($connect_server, 0777, $ftp_folder . '/' . $file);
          }
        }
      }

      do_upload_images($connect_server, $local_dir, $ftp_folder);
      ftp_close($connect_server);
    }

	return "<div class='alert alert-success'><i class='fa fa-picture-o'></i> Картинки загружены</div>";
}

function do_upload_images($connect_server, $local_dir, $ftp_dir){
	$folder = opendir($local_dir);
	$check = '';
	while($file = readdir($folder)){
		if(($file != ".") AND ($file != "..") AND ($file)){
			$local_file = $local_dir."/".$file;
			$ftp_file = $ftp_dir."/".$file;
			if(is_file($local_file)){

              /*if(!ftp_nlist($connect_server,$ftp_dir)) {
                ftp_mkdir($connect_server, $ftp_dir);
                ftp_chmod($connect_server, 0644, $ftp_file);
              }*/

              ftp_put($connect_server, $ftp_file, $local_file, FTP_BINARY);
              ftp_chmod($connect_server, 0644, $ftp_file);
			}else
				do_upload_images($connect_server, $local_file, $ftp_file);
		}
	}
	return FALSE;
}

function cut_image($url, $left, $type){
	$left = ($left - 2) * 3 / 4;
	$image = imageCreateFromJPEG($url);
	$real_w = imagesx($image);
	$real_h = imagesy($image);
	if($real_w / $real_h == 4 / 3)
		return TRUE;
	$raz = ($real_h / 300);
	$w = ($real_w / $raz);
	$right = $w - ($left + 400);
	$left = $left * $raz;
	$right = $right * $raz;

	if($type == "object"){
		$new_image = imageCreateTrueColor(840, 630);
		imageCopyResampled($new_image, $image, 0, 0, $left, 0, 840, 630, $real_w - ($right + $left), $real_h);
	}else{
		$new_image = imageCreateTrueColor(400, 300);
		imageCopyResampled($new_image, $image, 0, 0, $left, 0, 400, 300, $real_w - ($right + $left), $real_h);
	}
	imageJPEG($new_image, $url, 100);
	imageDestroy($new_image);
}

function image_resize($image, $name_pic, $width, $height){
	$new_image = imageCreateTrueColor($width, $height);
	imageCopyResampled($new_image, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
	imageJPEG($new_image, $name_pic, 51);
	imageDestroy($new_image);
}

function get_next_name($url){
	$folder = opendir($url);
	$max = 0;
	while($file = readdir($folder)){
		if($max < (int)$file)
			$max = (int)$file;
	}
	$max++;
	return $max.".jpg";
}

?>
