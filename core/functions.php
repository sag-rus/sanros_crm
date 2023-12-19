<?php
$directory = __DIR__."/..";

$gsok = array(61 => "", 62 => "", 63 => "", 64 => "", 71 => "", 138 => "", 494 => "");

spl_autoload_register(function($class){
	$file = _FOLDERSITE_."/core/class/".$class.".Class.php";
	if(file_exists($file)){
		include_once($file);
	}else{
		$path = _FOLDERSITE_."/core/class/";
		$folder = opendir($path);
		if(!is_bool($folder)) {
          while(false !== ($fold = readdir($folder))){
            if($fold != "." AND $fold != ".." AND is_dir($path.$fold)){
              $file = _FOLDERSITE_."/core/class/".$fold."/".$class.".Class.php";
              if(file_exists($file)){
                include_once($file);
                return;
              }
            }
          }
        }
	}
});


function get_place_export_id($id_room, $occu) {
	$occu_name = '';
	if ($occu['adult_on_main_place']>0) $occu_name .= '_a.'.$occu['adult_on_main_place'];
	if ($occu['id_child_on_main_place']>0 && $occu['child_on_main_place']>0) $occu_name .= '_c.'.$occu['child_on_main_place'].'.'.$occu['id_child_on_main_place'];
  if ($occu['adult_on_add_place']>0) $occu_name .= '_e.'.$occu['adult_on_add_place'];
	if ($occu['id_child_on_add_place']>0 && $occu['child_on_add_place']>0) $occu_name .= '_x.'.$occu['child_on_add_place'].'.'.$occu['id_child_on_add_place'].'.1';
	if ($occu['id_child_no_place']>0 && $occu['child_no_place']>0) $occu_name .= '_x.'.$occu['child_no_place'].'.'.$occu['id_child_no_place'].'.0';
	return $id_room.$occu_name;
}

function get_place_name($row) {
	global $connect;
	$res = '';
	if ($row['adult_on_main_place']>0) $res .= $row['adult_on_main_place'].' взр. на осн.месте + ';
	if ($row['adult_on_add_place']>0) $res .= $row['adult_on_add_place'].' взр. на доп.месте + ';
	if ($row['id_child_on_main_place']>0 && $row['child_on_main_place']>0) {
		$child = $connect->getRow("SELECT * FROM child_occupancy WHERE id=?i", $row["id_child_on_main_place"]);	
		$res .= $row['child_on_main_place'].' реб. ('.$child['age_from'].'-'.$child['age_to'].' лет) на осн.месте + ';
	}
	if ($row['id_child_on_add_place']>0 && $row['child_on_add_place']>0) {
		$child = $connect->getRow("SELECT * FROM child_occupancy WHERE id=?i", $row["id_child_on_add_place"]);	
		$res .= $row['child_on_add_place'].' реб. ('.$child['age_from'].'-'.$child['age_to'].' лет) на доп.месте + ';
	}
	if ($row['id_child_no_place']>0 && $row['child_no_place']>0) {
		$child = $connect->getRow("SELECT * FROM child_occupancy WHERE id=?i", $row["id_child_no_place"]);	
		$res .= $row['child_no_place'].' реб. ('.$child['age_from'].'-'.$child['age_to'].' лет) без места + ';
	}
	return trim(trim($res), '+');
}

function save_crm_user_history($connect, $note = ""){
	global $session_login;
	$today = date("Y-m-d");
	$time = date("H:i:s");
	$connect->query("INSERT INTO history_crm(date, time, id_user, note) VALUES(?s, ?s, ?i, ?s)", $today, $time, $session_login, $note);
}

function execute_select_query($connect, $data) {
	if (strpos(mb_strtoupper($data['query']), 'CREATE')===FALSE && 
		  strpos(mb_strtoupper($data['query']), 'ALTER ')===FALSE && 
		  strpos(mb_strtoupper($data['query']), 'INSERT')===FALSE && 
		  strpos(mb_strtoupper($data['query']), 'UPDATE')===FALSE && 
		  strpos(mb_strtoupper($data['query']), 'DROP')===FALSE && 
		  strpos(mb_strtoupper($data['query']), 'TRUNCATE')===FALSE && 
		  strpos(mb_strtoupper($data['query']), 'SELECT')!==FALSE) {

		$result = $connect->getAll($data['query']);
		return $result;
		
	} else return FALSE;
}

function clear_phone($phone) {
  $phone = preg_replace("/[^0-9]/", "", $phone);
  if ($phone[0]=='8') $phone[0]='7';
  if (strlen($phone)==10) $phone='7'.$phone;
  return $phone; 
}

function format_phone($phone = '') {
  if (empty($phone)) {
    return '';
  }
  
  $phone = clear_phone($phone);
  if (strlen($phone)>11) {
    $phone = substr($phone,  0, 11);
  }

  if (strlen($phone) == 11) {
    return preg_replace("/([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])/", "$1 ($2$3$4) $5$6$7-$8$9-$10$11", $phone);
  }

  return $phone;
}

function connect_to_MySQL(){
	include_once(__DIR__."/../config.php");
	$conf = new JConfig;
	$options = array();
	$options["host"] = $conf->host;
	$options["user"] = $conf->user;
	$options["pass"] = $conf->password;
	$options["db"] = $conf->db;

	$connect = new SafeMySQL($options);
	return $connect;
}

function connect_to_MySQL_directory(){
	include_once(__DIR__."/../config.php");
	$conf = new JConfig;
	$options = array();
	$options["host"] = $conf->host;
	$options["user"] = $conf->user;
	$options["pass"] = $conf->password;
	$options["db"] = $conf->db;

	$connect = new SafeMySQL($options);
	return $connect;
}

function connect_to_server(){
    global $directory;
	include_once($directory."/config.php");

	$conf = new JConfig;

  $server = $conf->ftp_server;
	$user_ftp = $conf->ftp_server_user;
	$pass_ftp = $conf->ftp_server_pass;

	$connect_server = ftp_connect($server);
	if(!$connect_server)
		return 1;
	if(!ftp_login($connect_server, $user_ftp, $pass_ftp))
		return 2;
	ftp_pasv($connect_server, TRUE);
	return $connect_server;
}

function connect_to_server_directory(){
	global $directory, $crm_ftp_connect;
	include_once($directory."/config.php");

	$conf = new JConfig;
	$server = $conf->ftp_server;
	$user_ftp = $conf->ftp_server_user;
	$pass_ftp = $conf->ftp_server_pass;

	if(!isset($crm_ftp_connect)) {
      $connect_server = ftp_connect($server);

      if(!$connect_server)
        return 1;
      if(!ftp_login($connect_server, $user_ftp, $pass_ftp))
        return 2;
      ftp_pasv($connect_server, TRUE);

      $crm_ftp_connect = $connect_server;
    }
	else {
	    $connect_server = $crm_ftp_connect;
    }

	return $connect_server;
}

function request_to_sync($params){
	global $directory;

	include_once($directory."/config.php");
  $conf = new JConfig;
  $sync = $conf->sync_base;

	$string = http_build_query($params);
	$options = array("http" =>
		array(
			"method"  => "POST",
			"header"  => "Content-type: application/x-www-form-urlencoded",
			"content" => $string
		)
	);
	$context = stream_context_create($options);
	$result = file_get_contents($sync, FALSE, $context);
	$array = json_decode($result, TRUE);
	return $array;

}

function request_to_url($url, $params){

	$curl = curl_init($url);
	$string = http_build_query($params);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $string);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

	$res = curl_exec($curl);
	$array = json_decode($res, TRUE);
	return $array;

}

function connect_config(){
	global $directory;
	//include_once($directory . _DS_ ."config.php");
	$conf = new JConfig;
	return $conf;
}

function get_login_bank($connect, $object = ""){
	$login = USERNAME_ALFA;
	if(!$object)
		return $login;
	$bank_login = $connect->getOne("SELECT bank_login FROM object WHERE id=?i", $object);
	if($bank_login != "")
		$login = $bank_login;
	return $login;
}

function get_address_by_ip($ip){
    return false;

	if(!$ip)
		return FALSE;
	$url = "http://ipgeobase.ru:7020/geo?ip=".$ip;

    try {
        $xml = @simplexml_load_file($url);
    }
    catch (Throwable $e) {
        return false;
    }

	if(isset($xml->ip) && isset($xml->ip->region) && $xml->ip->region){
		$address = $xml->ip->region;
		//if($xml->ip->city)
		//	$address.= ", ".$xml->ip->city;
		return $address;
	}
	return FALSE;
}

function select_array_table($connect, $table, $where = false){
	$zapros = "SELECT id, name FROM ".$table;
	if($where)
		$zapros.= " WHERE ".$where;
	$result = array();
	$data = $connect->getAll($zapros);
	foreach($data as $row){
		$id = $row["id"];
		$result[$id] = $row["name"];
	}
	return $result;
}

function select_array_reward(){
	$result = array();
	for($i=1; $i<=30; $i++){
		$result[] = $i;
		if($i == 2)
			$result[] = 2.5;
		if($i == 6)
			$result[] = 6.5;
		if($i == 7)
			$result[] = 7.5;
		if($i == 12)
			$result[] = 12.5;
	}
	return $result;
}


function date_check($date){
	$check = str_replace(".", "", $date);
	$check = str_replace("-", "", $check);
	if($check == "00000000")
		return;
	return $date;
}

function clear_quotes($array){
	foreach($array as $key => $value){
		$array[$key] = htmlspecialchars($value);
	}
	return $array;
}

function add_null($sum){
	if($sum != "" AND substr_count($sum, ".") == 0 AND $sum != "00")
		$sum.= ".00";
	elseif($sum == "" OR $sum == "00")
		$sum = "00";
	$p = explode(".", $sum);
	if(strlen($p[count($p)-1]) == 1)
		$sum.= "0";
	return $sum;
}

function date_change($date, $separator = "-", $divider = "-"){
	if($date != "0000-00-00" AND $date != ""){
		$d = explode($divider, $date);
		$date = $d[2].$separator.$d[1].$separator.$d[0];
		return $date;
	}else
		return FALSE;
}

function date_sum($date, $days){
	if(!is_numeric($date))
		$date = strToTime($date);
	$sec = $days * 86400;
	$date2 = $date + $sec;
	return $date2;
}

function month_transform($date){
	if(!$date)
		return;
	$month_pad = array(1 => "января", 2 => "февраля", 3 => "марта", 4 => "апреля", 5 => "мая", 6 => "июня", 7 => "июля", 8 => "августа", 9 => "сентября", 10 => "октября", 11 => "ноября", 12 => "декабря");
	if(substr_count($date, "-"))
		$date2 = explode("-", $date);
	else
		$date2 = explode(".", $date);
	$m = $date2[1];
	if($m == 0)
		return FALSE;
	$month = $month_pad[(int)$m];
	$date = $date2[0]." ".$month." ".$date2[2];
	return $date;
}

function date_transform($date, $year = FALSE){
	global $month_pad;
	if(substr_count($date, "-"))
		$date2 = explode("-", $date);
	else
		$date2 = explode(".", $date);
	$month = $month_pad[(int)$date2[1]];
	if($year == TRUE)
		$date = (int)$date2[2]." ".$month." ".$date2[0];
	else
		$date = (int)$date2[2]." ".$month;
	return $date;
}

function get_status_array($connect, $table){
	$array = array();
	$data = $connect->getAll("SELECT id, name FROM $table");
	foreach($data as $row){
		$id = $row["id"];
		$array[$id] = $row["name"];
	}
	return $array;
}

function get_managers($connect, $type = "", $select = "", $id_rights = NULL, $session_login = NULL, $noAccessCheck = FALSE){
	$html = "<select class='form-control' id='all_manager'>";
	if($type == "filter")
		$html.= "<option value='' SELECTED>Не выбран</option>";

	if($session_login)
	    $user = $connect->getRow("SELECT * FROM users WHERE id = ?i", $session_login);
	else
	    $user = NULL;


	if($noAccessCheck || (!is_null($id_rights) && ($id_rights > 5 || ($user && $user['class'] != 1))))
	    $data = $connect->getAll("SELECT name, id FROM users WHERE dostup=1 AND class=1");
	elseif(!is_null($session_login))
        $data = $connect->getAll("SELECT name, id FROM users WHERE dostup=1 AND class=1 AND id = ?i",$session_login);
	else
	    $data = [];



	if(count($data) > 0) {
      foreach($data as $row){
        $id = $row["id"];
        $selected = "";
        if($select == $id)
          $selected = " SELECTED ";
        if($id == $session_login)
            $html.= "<option value='".$id."' ".$selected.">".$row["name"]." (это Вы)</option>";
        else
            $html.= "<option value='".$id."' ".$selected.">".$row["name"]."</option>";
      }
      $html.= "</select>";
      return $html;
    }
	return NULL;
}

function get_object($connect, $id, $type_view = ""){
	$data_object = $connect->getRow("SELECT name, address, full_name, type, id_reg, city, fast_booking FROM object WHERE id=?i", $id);
	$short = $data_object["name"];
	$full = $data_object["full_name"];
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $data_object["type"]);
	$id_reg = $data_object["id_reg"];
	$city = $data_object["city"];
	if($type_view == "full"){
		$object = $full;
		if(!$object)
			$object = $short;
	}elseif($type_view == "full_and_place"){
		$object = $full;
		if(!$object)
			$object = $short;
		if($city)
			$city = ", ".$city;
		$data_region = $connect->getRow("SELECT name, id_country FROM region WHERE id=?i", $id_reg);
		$region = $data_region["name"];
		$id_country = $data_region["id_country"];
		$country = $connect->getOne("SELECT name FROM country WHERE id=?i", $id_country);
		if($country)
			$object = $object." (".$country.", ".$region.$city.")";
	}elseif($type_view == "full_and_address"){
  	$object = $data_object["full_name"]." (".$data_object['address'].")";
	}elseif($type_view == "place"){
		$object = $short;
		$data_region = $connect->getRow("SELECT name, id_country FROM region WHERE id=?i", $id_reg);
		$region = $data_region["name"];
		$id_country = $data_region["id_country"];
		if($city)
			$city = ", ".$city;
		$country = $connect->getOne("SELECT name FROM country WHERE id=?i", $id_country);
		if($type)
			$type.= " ";
		if($country)
			$object = $type.$object." (".$country.", ".$region.$city.")";
	}elseif($type_view == "type"){
		$object = $type." ".$short;
	}
	elseif ($type_view == "type_and_fast_booking") {
	    $object = [
	      'type' => $type." ".$short,
          'fast_booking' => $data_object['fast_booking']
        ];
    }
	else
		$object = $short;
	return $object;
}

function get_object_address($connect, $id){
	$row = $connect->getRow("SELECT city, id_reg FROM object WHERE id=?i", $id);
	$address = $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]);
	if($row["city"])
		$address.= ", ".$row["city"];
	return $address;
}

function get_object_image($connect, $id){
	$base64 = $connect->getOne("SELECT image FROM object WHERE id=?i", $id);
	$image = "images/object/defaul.jpg";
	if($base64)
		$image = "data:image/jpg;base64,".$base64;
	return $image;
}

function get_objects_by_region($connect, $id_reg, $add = "", $and = " AND "){
    if($id_reg) {
      if($id_reg == "all")
        return "";
      if($add)
        $add.= ".";
      $answer = "";
      $data = $connect->getAll("SELECT id FROM object WHERE id_reg=?i", $id_reg);
      foreach($data as $row){
        $id = $row["id"];
        if($answer)
          $answer.= " OR ";
        $answer.= $add."id_obj=".$id;
      }
      $answer = $and."(".$answer.")";
      return $answer;
    }
    else return "";
}

function get_room($connect, $id, $type = "", $view = ""){
	$data_room = $connect->getRow("SELECT id_obj, name, note, housing, food FROM room WHERE id=?i", $id);
	$room = $data_room["name"];
	$object = $data_room["id_obj"];
	$housing = $data_room["housing"];
	$food = $data_room["food"];
	$note = "";
	if($type == "full"){
		if($housing)
			$note = $connect->getOne("SELECT name FROM housing WHERE id=?i", $housing);
		if($data_room["note"] AND $housing)
			$note.= ", ";
		$note.= $data_room["note"];
		if($note)
			$room.= " (".$note.")";
	}elseif($type == "note"){
		$note = $data_room["note"];
		if($note)
			$room.= " (".$note.")";
		if($food)
			$room.= " питание ".$food;
	}
	if(($view == "view_schet") AND ($object == 61 OR $object == 62 OR $object == 63 OR $object == 64 OR $object == 71 OR $object == 494))
		$room = "<strong>".get_object($connect, $object)."</strong> ".$room;
	return $room;
}

function select_rooms($connect, $object, $id_room = ""){
	if($object == 96){
		$rooms = $connect->getAll("SELECT id, id_obj FROM room WHERE active=0 AND (id_obj=61 OR id_obj=62 OR id_obj=63 OR id_obj=64 OR id_obj=71 OR id_obj=138 OR id_obj=494) ORDER BY id_obj");
		$arr = array(61 => "Каскад", 62 => "Станция", 63 => "Дежавю", 64 => "Альпийские коттеджи", 71 => "Каскад 2", 138 => "Оздоровительный комплекс", 494 => "Маяк");
	}else
		$rooms = $connect->getAll("SELECT id, id_obj FROM room WHERE active=0 AND id_obj=?i ORDER BY name", $object);
	$html = "<select class='select_room form-control'>";
	$id_old = "";
	foreach($rooms as $room){
		$select = "";
		if($room["id"] == $id_room)
			$select = " SELECTED ";
		if($id_old != $room["id_obj"] AND $object == 96){
			$id_old = $room["id_obj"];
			$html.= "<optgroup label='".$arr[$room["id_obj"]]."'></optgroup>";
		}
		$name = get_room($connect, $room["id"], "full");
		$html.= "<option value=".$room["id"]." ".$select.">".$name."</option>";
	}
	$html.= "</select>";
	return $html;
}

function get_reward_select($select = "", $edit = ""){
	global $id_rights;
	$disabled = "";
	if($edit == "check" AND $id_rights <= 3 AND (int)$select > 0)
		$disabled = " disabled='disabled' ";
	$commis = "<select id='commis' class='form-control' ".$disabled.">";
	$commis.= "<option value='0'>Нетто</option>";
	for($i=1; $i<=30; $i++){
		$sel = "";
		if($i == $select)
			$sel = " SELECTED ";
		$commis.= "<option value='".$i."' ".$sel.">".$i."</option>";
		if($i == 2)
			$commis.= "<option value='2.5'>2.5</option>";
		if($i == 6)
			$commis.= "<option value='6.5'>6.5</option>";
		if($i == 7)
			$commis.= "<option value='7.5'>7.5</option>";
		if($i == 12)
			$commis.= "<option value='12.5'>12.5</option>";
	}
	$commis.= "</select>";
	return $commis;
}

function get_select_commis($connect, $select = ""){
	$html = "<select class='form-control' id='id_com'>";
	$data = $connect->getAll("SELECT id, value FROM commission WHERE id_obj='' AND id_agency='' ORDER BY value");
	foreach($data as $commis){
		$value = $commis["value"];
		$id = $commis["id"];
		$sel = "";
		if($id == $select)
			$sel = " SELECTED ";
		$html.= "<option ".$sel." value='".$id."'>".$value."</option>";
	}
	$html.= "</select>";
	return $html;
}

function get_select_discount($connect, $select = ""){
	$html = "<select class='form-control' id='id_dis'><option value=''>Без скидки</option>";
	$data = $connect->getAll("SELECT id, value, type FROM discount WHERE type=1 ORDER BY value");
	foreach($data as $discount){
		$value = $discount["value"];
		$id = $discount["id"];
		$sel = "";
		if($id == $select)
			$sel = " SELECTED ";
		$html.= "<option ".$sel." value='".$id."'>".$value."</option>";
	}
	$html.= "</select>";
	return $html;
}

function get_select_rights($connect, $id_rights_sel = ""){
	global $id_rights;
    $data = $connect->getAll("SELECT name, id FROM rights");
	$html = "<select id='dostup_id' class='form-control'>";

	foreach($data as $right){
	    if($right['id'] <= $id_rights) {
          $name_rights = $right["name"];
          $id = $right["id"];
          $select = "";
          if($id_rights_sel == $id)
            $select = " SELECTED ";
          $html.= "<option ".$select." value='".$id."'>".$name_rights."</option>";
        }
	}
	$html.= "</select>";
	return $html;
}

function insert_base64_encoded_image($img){
	if(!file_exists($img))
		return FALSE;
	$imageSize = getImageSize($img);
	$imageData = base64_encode(file_get_contents($img));
	return $imageData;
}

function get_place_object($connect, $id_obj, $place = ""){
	$html = "<select id='range_place' class='form-control'>";
	$data = $connect->getAll("SELECT * FROM place WHERE id_obj=?i OR id_obj=0 ORDER BY id", $id_obj);
	foreach($data as $row){
		
		$id = $row["id"];

		if($row["type"] == 2) $type = " доп.место"; else $type = " основное";
		if($place == $id)  $select = " SELECTED"; else $select = "";

		if (($row['adult_on_main_place']>0 && $row['adult_on_add_place']>0) || ($row['id_child_on_main_place']>0 && $row['child_on_main_place']>0) || ($row['id_child_on_add_place']>0 && $row['child_on_add_place']>0) || ($row['id_child_no_place']>0 && $row['child_no_place']>0)) $type = '';

		$html.= "<option value='".$id."' ".$select.">".$row["name"].$type."</option>";
	}
	$html.= "</select>";
	return $html;
}

function get_dates_object($connect, $id_obj, $date){
	$today = date("Y-m-d");
	$html = "<select id='range_date' class='form-control'>";
	$data = $connect->getAll("SELECT id, DATE_FORMAT(start, '%e.%m.%Y') as date_start, DATE_FORMAT(end, '%e.%m.%Y') as end FROM date_price WHERE id_obj=?i AND active=0 AND end>=?s ORDER BY start", $id_obj, $today);
	foreach($data as $row){
		$select = "";
		$id = $row["id"];
		$data = $row["date_start"]." - ".$row["end"];
		if($date == $id)
			$select = " SELECTED";
		$html.= "<option value='".$id."' ".$select.">".$data."</option>";
	}
	$html.= "</select>";
	return $html;
}

function get_type_price($type){
	$array = array(1 => "", 2 => "", 3 => "", 4 => "");
	$array[$type] = " SELECTED ";
	$html = "<select id='range_type' class='form-control'>";
	$html.= "<option value='1' ".$array[1].">за чел/сутки</option>";
	$html.= "<option value='2' ".$array[2].">за дом</option>";
	$html.= "<option value='3' ".$array[3].">за номер</option>";
	$html.= "<option value='4' ".$array[4].">за заезд</option>";
	$html.= "</select>";
	return $html;
}

function get_treatment_price($type){
	$treatments = array(0 => 'Не выбрано', 2 => 'Без лечения', 1 => 'С лечением');
	ob_start();
?>
	<select class="form-control" id="treatment_type">
		<?php 
			foreach($treatments as $id => $treatment) {
			?>
			<option value="<?=$id?>" <?=($id == $type) ? "selected" : ""?>><?=$treatment?></option>
			<?php
			}
		?>
	</select>
<?php
	$html = ob_get_clean();
	return $html;
}

function select_payment_method(){
	ob_start();
?>
	<select class="form-control" id="payment-method">
		<option value="">не выбран</option>
		<option value="2">Наличный</option>
		<option value="1">Безналичный</option>
		<option value="3">Сертификатом</option>
		<option value="4">На месте</option>
		<option value="5">Банковской картой</option>
	</select>
<?php
	$html = ob_get_clean();
	return $html;
}

function break_columns($connect, $table, $index, $id_row, $order = "", $where = ""){
	$i = 0;
	$html = "";
	if($id_row != "")
		$id_rows = explode("_", $id_row);
	$array = array();
	$data = $connect->getAll("SELECT id, name FROM ".$table." ".$where." ".$order);
	foreach($data as $row){
		$i++;
		$check = "";
		if($id_row){
			foreach($id_rows as $value){
				if($value == $row["id"])
					$check = " CHECKED ";
			}
		}
		if(!isset($array[$i]))
			$array[$i] = "";
		$array[$i].= "<li><label><input type='checkbox' $check class='".$table."' value='".$row["id"]."' />".$row["name"]."</label></li>";
		if($i >= $index)
			$i = 0;
	}
	foreach($array as $li){
		$html.= "<ul>".$li."</ul>";
	}
	return $html;
}

function parse_index_string($connect, $string, $table, $separator){
	$array = explode($separator, $string);
	$answer = "";
	foreach($array as $index){
		if($index){
			$row = $connect->getRow("SELECT name FROM ".$table." WHERE id=?i", $index);
			if($answer)
				$answer.= ", ".$row["name"];
			else $answer.= $row["name"];
		}
	}
	return $answer;
}

function parse_index_string_to_array($connect, $string, $table, $separator){
	$array = explode($separator, $string);
	$answer = array();
	foreach($array as $index){
		if($index){
			$name = $connect->getOne("SELECT name FROM ".$table." WHERE id=?i", $index);
			$answer[$index] = $name;
		}
	}
	return $answer;
}

function get_image($dir){
	$image = 0;
	if(is_dir($dir)){
		$folder = opendir($dir);
		while(false  !== ($file = readdir($folder))){
			if(($file != '.') AND ($file != '..') AND (is_file($dir."/".$file)))
				$image++;
		}
	}
	return $image;
}

function select_image_room($region, $object, $room){
	global $directory;
	$url = "temp/images/".$region."/".$object."/".$room."/small/";
	$dir = $directory."/temp/images/".$region."/".$object."/".$room."/small/";
	if(is_dir($dir)) {
      $folder = opendir($dir);
      while($image = readdir($folder)){
        if(($image != ".") AND ($image != "..") AND ($image)){
          return $url.$image;
        }
      }
    }
	return FALSE;
}

function get_select_options($start, $end, $select = ""){
	$html = "";
	for($i=$start; $i<=$end; $i++){
		$code = "";
		if($i == $select)
			$code = "SELECTED";
		$html.= "<option value='".$i."' ".$code.">".$i."</option>";
	}
	return $html;
}

function get_select_table($connect, $table, $where, $select, $id, $first = "", $func = ""){
	$zapros = "SELECT id, name FROM ".$table;
	if($where)
		$zapros.= " WHERE ".$where;
	$html = '<select class="form-control" id="'.$id.'" '.$func.' name="'.$id.'">';
	if($first == 1)
		$html.= "<option value='0'>Не выбрано</option>";
	$data = $connect->getAll($zapros);
	foreach($data as $row){
		$sel = "";
		if($row["id"] == $select)
			$sel = " SELECTED ";
		$html.= '<option value="'.$row["id"].'" '.$sel.'>'.$row["name"].'</option>';
	}
	$html.= "</select>";
	return $html;
}

function save_schet_to_history($connect, $id, $note = ""){
	global $session_login;
	$row = $connect->getRow("SELECT status, status_san FROM reckoning WHERE id=?i", $id);
	$new_status = $row['status'];
	$new_status_san = $row['status_san'];
	$today = date("Y-m-d");
	$time = date("H:i:s");
	$connect->query("INSERT INTO history_schet(date, time, id_schet, id_user, new_status, new_status_san, note) VALUES(?s, ?s, ?i, ?i, ?i, ?i, ?s)", $today, $time, $id, $session_login, $new_status, $new_status_san, $note);
}

function save_client_to_history($connect, $id, $note = ""){
	$connect->query("INSERT INTO history_client(client, note) VALUES(?i, ?s)", $id, $note);
}

function save_reservation_history($connect, $id, $note = ""){
	global $session_login;
	$status = $connect->getOne("SELECT status FROM reservation WHERE id=?i", $id);
	$connect->query("INSERT INTO history_reservation(id_reserv, id_user, status, note) VALUES (?i, ?i, ?i, ?s)", $id, $session_login, $status, $note);
}

function check_status_booking_quota($connect, $bid, $position = ""){
	$booking = $connect->getOne("SELECT id FROM booking WHERE bid=?i", $bid);
	if($booking){
		$row = $connect->getRow("SELECT active, status FROM reckoning WHERE id=?i", $bid);
		$active = $row["active"];
		$status = $row["status"];
		if($status == 6 OR $status == 8 OR $active == 3)
			$connect->query("UPDATE booking SET update_bid=1, confirm=0, status='cancelled' WHERE id=?i", $booking);
		elseif($position){
			$ratePlan = $connect->getOne("SELECT ratePlan FROM position_reck WHERE id=?i", $position);
			if($ratePlan >= 0)
				$connect->query("UPDATE booking SET update_bid=1, confirm=0, status='modified' WHERE id=?i", $booking);
		}
	}
}

function save_notification($connect, $text, $user){
	$connect->query("INSERT INTO notification(text, user) VALUES (?s, ?i)", $text, $user);
}

function save_payment($connect, $schet, $sum, $type, $pay_number, $date, $pay_method, $office = 1){
      global $directory;
      include_once($directory."/config.php");
      $conf = new JConfig;
	if($date == "")
		$date = date("Y-m-d");
	$timestamp = date("U",strtotime($date));

	$bank_com = NULL;
	$terminal = 0;
	$pay_method = (string)$pay_method;
	if($pay_method === '5' || $pay_method === '5-1') {
      $bank_com = $conf->BANK_COM_SBERBANK;
	    $pay_method = 5;
    }
    elseif($pay_method === '5-2') {
	    $bank_com = $conf->BANK_COM_SBERBANK_TERMINAL;
	    $terminal = 1;
        $pay_method = 5;
    }

	$connect->query("INSERT INTO payment (schet, sum, date, type, pay_method, pay_number, office, created, processed, bank_com, terminal)
			VALUES (?i, ?s, ?s, ?i, ?s, ?s, ?i, ?i, ?i, ?s, ?i)", $schet, $sum, $date, $type, $pay_method, $pay_number, $office, $timestamp, $timestamp, $bank_com, $terminal);
}

function save_certificate_to_history($connect, $id){
	global $session_login;
	$status = $connect->getOne("SELECT status FROM certificate WHERE id=?i", $id);
	$connect->query("INSERT INTO history_cert(date, time, id_cert, id_user, status) VALUES(?s, ?s, ?i, ?i, ?i)", date("Y-m-d"), date("H:i:s"), $id, $session_login, $status);
}

function get_sum_for_pay($connect, $id){
	$row = $connect->getRow("SELECT sum, agency, id_com, id_dis, status FROM reckoning WHERE id=?i", $id);
	$sum = $row["sum"];
	$raz = 0;
	if($row["agency"]){
		$commis = ($connect->getOne("SELECT value FROM commission WHERE id=?i", $row["id_com"])) / 100;
		$data = $connect->getAll("SELECT id, reward, sum, days, number, type FROM position_reck WHERE schet=?i", $id);
		foreach($data as $position){
			if((int)$position["reward"] != 0)
				$sum_agency+= calculate_position($position["sum"], $position["number"], $position["type"], $position["days"]);
		}
		$raz+= $sum_agency * $commis;
	}
	$bonus = $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum<0", $id);
	if($bonus)
		$raz+= $bonus * (-1);
	if($row["id_dis"]){
        $discount = $connect->getRow("SELECT `value`, `type` FROM discount WHERE id=?i", $row["id_dis"]);
        if($discount) {
          if($discount['type'] == 1)
            $raz+= ($discount['value']/100) * $sum;
          else
            $raz+= $discount['value'];
        }
	}
	if($row["status"] == 4 OR $row["status"] == 7){
		$payment = $connect->getAll("SELECT sum FROM payment WHERE type=1 AND schet=?i", $id);
		foreach($payment as $pay){
			$raz+= $pay["sum"];
		}
	}
	$sum = $sum - $raz;
	return $sum;
}

function get_office_for_pay($connect, $selected = ""){
	global $session_login;
	$html = "<select class='form-control' id='office-pay'>";
	if(!$selected)
		$html.= "<option value=''>Выбрать офис</option>";
	$data = $connect->getAll("SELECT id, name FROM office");
	foreach($data as $row){
		$select = "";
		$id = $row["id"];
		if($id == $selected)
			$select = " SELECTED ";
		$html.= "<option value='".$id."' ".$select.">".$row["name"]."</option>";
	}
	$html.= "</select>";
	return $html;
}

function get_reward_object($connect, $object, $date = ""){
	$reward_object_date = array(
		26 => array(
			[
				"start" => 1483131600,
				"end" => 1483995600,
				"reward" => 7
			]
		),
		5 => array(
			[
				"start" => 1483045200,
				"end" => 9999999999,
				"reward" => 10
			]
		),
		39 => array(
			[
				"start" => 1483045200,
				"end" => 9999999999,
				"reward" => 10
			]
		),
		96 => array(
			[
				"start" => 1482613200,
				"end" => 1483995600,
				"reward" => 5
			]
		)
	);
	if(isset($reward_object_date[$object]) AND $date != ""){
		$time = strToTime($date);
		foreach($reward_object_date[$object] as $reward_object){
			if($reward_object["start"] <= $time AND $reward_object["end"] >= $time)
				return $reward_object["reward"];
		}
	}
	$reward = (int)$connect->getOne("SELECT reward FROM object WHERE id=?i", $object);
	return $reward;
}

function get_payment_reward_by_id($connect,$id) {
  $reward = 0;
  $payment = $connect->getRow("SELECT id, sum, schet FROM payment WHERE pay_method <> '0'", $id);
  if($payment) {
      $reck = $connect->getRow("SELECT id, status, reward FROM reckoning WHERE id=?i", $payment['schet']);

  }
  return $reward;
}

function get_reward_schet($connect, $id, $type = "", $fact = false, $consider_bonus = true, $only_payments = NULL, $has_old_payments = FALSE){
  $array = array("reward" => 0, "agency" => 0, "bonus" => 0, "correction" => 0, "bank_com" => 0, "discount" => 0);
  $reward = 0;
  $reck_reward = $connect->getOne("SELECT reward FROM reckoning WHERE id=?i", $id);
  $bonus = $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $id);
  $reck = $connect->getRow("SELECT id, type, sum, agency, id_com, id_dis, correction, status FROM reckoning WHERE id=?i LIMIT 1", $id);
  $only_payment_state = false;
  if($fact) {
    $add_cond = "";
    if(!is_null($only_payments) && count($only_payments) > 0) {
      $only_payment_state = true;
      foreach($only_payments as $only_payment) {
        if(mb_strlen($add_cond) > 0)
          $add_cond .= " OR ";
        else
          $add_cond .= "(";

        $add_cond .= "id = '".$only_payment."'";
      }
    }

    if(mb_strlen($add_cond) > 0)
      $add_cond .= ") AND ";
    $payments = $connect->getAll("SELECT id, sum FROM payment WHERE ".$add_cond."schet=?i AND class='schet' AND type != 3 AND type != 4 AND type != 5 AND `payment`.`status` != 0", $id);
    $pay_sum = 0;

    foreach ($payments as $payment) {
      $pay_sum += (float)$payment['sum'];
    }
  }

  if($reck_reward > 0) {
    $reward = $reck_reward;
  }
  else{
    $data = $connect->getAll("SELECT id FROM position_reck WHERE schet=?i", $id);
    if($fact) {
      if($reck['status'] != 5) {
        if($reck['type'] == 1) {
          $reward +=$pay_sum;
        }
        else {
          foreach ($data as $row) {
            $reward += get_reward_schet_position_pay($connect, $row["id"], $pay_sum);
            break;
          }
        }
      }
      else {
        if($consider_bonus) {
          //
          if($has_old_payments) {
            if($reck['type'] == 1) {
              $reward +=$pay_sum;
            }
            else {
              foreach ($data as $row) {
                $reward += get_reward_schet_position_pay($connect, $row["id"],$pay_sum);
                break;
              }
            }
          }
          else {
            if($reck['type'] == 1) {
              $reward +=$reck['sum'];
            }
            else {
              foreach ($data as $row) {
                $reward += get_reward_schet_position($connect, $row["id"]);
              }
            }
          }
        }
        else {
          if($reck['type'] == 1) {
            $reward +=$pay_sum;
          }
          else {
            foreach ($data as $row) {
              $reward += get_reward_schet_position_pay($connect, $row["id"],$pay_sum);
              break;
            }
          }
        }
      }
    }
    else {
      if($reck['type'] == 1) {
          $reward +=$reck['sum'];
      }
      else {
        foreach($data as $row)
          $reward+= get_reward_schet_position($connect, $row["id"]);
      }
    }
  }
  $reward = round($reward, 2);

  if($type == "EACH")
    $array["reward"] = add_null($reward);
  if($type == "ONLY_SAN")
    return $reward;

  $raz = 0;
  if($reck["agency"]){
    $value = $connect->getOne("SELECT value FROM commission WHERE id=?i LIMIT 1", $reck["id_com"]);
    if($fact) {
      if($reck['status'] == 5) {
        if($consider_bonus) {
            $commission = get_reward_agency($connect, $id);
        }
      }
    }
    else
      $commission = get_reward_agency($connect, $id);

    if($type == "EACH")
      $array["agency"] = add_null($commission);
    if(isset($commission)) {
        //echo $commission." ";
      $raz += $commission;
    }
  }


  if($consider_bonus) {
    if($bonus){
      $bonus = abs($bonus);
      if(!$fact || $reck['status'] == 5) {
        $raz+= $bonus;
        if($type == "EACH")
          $array["bonus"] = add_null($bonus);
      }
    }
  }

  if($reck["id_dis"]){
    $row = $connect->getRow("SELECT value, type FROM discount WHERE id=?i LIMIT 1", $reck["id_dis"]);
    if($row["type"] == 1) {
      if($fact) {
        if($reck['status'] != 5) {
          $discount = $pay_sum * ($row["value"] / 100);
        }
        else {
          if($consider_bonus) {
              if($has_old_payments) {
                $discount = $pay_sum * ($row["value"] / 100);
              }
              else {
                $discount = $reck['sum'] * ($row["value"] / 100);
              }
          }
        }
      }
      else
        $discount = $reck["sum"] * ($row["value"] / 100);
    }
    else {
      if($consider_bonus)
        $discount = $row["value"];
    }
    if($type == "EACH")
      $array["discount"] = add_null($discount);
    $raz+= $discount;
  }

  if($consider_bonus) {
    if($reck["correction"]){
      if($type == "EACH")
        $array["correction"] = add_null($reck["correction"]);
      $raz-= $reck["correction"];
    }
  }

  $bank_com = 0;
  $payment_status_string = " AND `payment`.`status` != 0";
  if($only_payment_state)
    $data = $connect->getAll("SELECT sum, bank_com, type FROM payment WHERE ".$add_cond."pay_method=5 AND schet=?i".$payment_status_string, $id);
  else
    $data = $connect->getAll("SELECT sum, bank_com, type FROM payment WHERE pay_method in (5,6,7) AND schet=?i".$payment_status_string, $id);


  foreach($data as $row){
    if($row["bank_com"] > 0 && ($row["type"] == 2 || $row["type"] == 6)){
      if($only_payment_state)
        $row["sum"]-= $connect->getOne("SELECT sum FROM payment WHERE ".$add_cond."type=5 AND schet=?i".$payment_status_string, $id);
      else
        $row["sum"]-= $connect->getOne("SELECT sum FROM payment WHERE type=5 AND schet=?i".$payment_status_string, $id);
    }

    if($row["sum"] <= 100)
      $bank_com+= "3.5";
    else
      $bank_com+= $row["sum"] * ($row["bank_com"] / 100);
    if($type == "EACH") {
    	$array["bank_com_procent"] = $row["bank_com"];
      $array["bank_com"] = add_null($bank_com);
    }
  }

  /*if($fact) {
    if($only_payment_state)
      $ret_payments = $connect->getAll("SELECT sum FROM payment WHERE ".$add_cond."type=5 AND schet=?i", $id);
    else
      $ret_payments = $connect->getAll("SELECT sum FROM payment WHERE type=5 AND schet=?i", $id);

    foreach ($ret_payments as $ret_payment) {
        $raz += (float)$ret_payment['sum'];
    }
  }*/

  $raz+= $bank_com;
  $reward = round($reward - $raz, 2);
  //if($reck['id'] == 65139) echo $reck['id']." ".$reward."<br />";

  if($type == "EACH"){
    $array["sum"] = add_null($reck["sum"]);
    $array["itog"] = add_null($reward);
    return $array;
  }else
    return $reward;
}

function get_reward_schet_position($connect, $id){
	$row = $connect->getRow("SELECT reward, sum, number, type, days FROM position_reck WHERE id=?i", $id);
	$reward = $row["reward"];
	$sum = $row["sum"];
	$number = $row["number"];
	$type = $row["type"];
	$days = $row["days"];
	$all_sum = calculate_position($sum, $number, $type, $days);
	$reward = $all_sum * ($reward / 100);
	return add_null($reward);
}

function get_reward_schet_position_pay($connect, $id, $pay_sum = 0){
  $row = $connect->getRow("SELECT reward, sum, number, type, days FROM position_reck WHERE id=?i", $id);
  $reward = $row["reward"];
  $reward = $pay_sum * ($reward / 100);
  return add_null($reward);
}

function get_reward_agency($connect, $id, $sum = NULL){
	$sum_agency = 0;
	$row = $connect->getRow("SELECT commission_value, id_com FROM reckoning WHERE id=?i", $id);
	$commission = $row["commission_value"];
	if($commission > 0)
		return $commission;
	$percent = $connect->getOne("SELECT value FROM commission WHERE id=?i", $row["id_com"]);

	if(!is_null($sum)) {
	    $sum_agency = $sum;
    }
    else {
      $data = $connect->getAll("SELECT id, reward, sum, days, number, type FROM position_reck WHERE schet=?i", $id);
      foreach($data as $row){
        if((int)$row["reward"] != 0)
          $sum_agency+= calculate_position($row["sum"], $row["number"], $row["type"], $row["days"]);
      }
      if($sum_agency == 0)
        $sum_agency = $connect->getOne("SELECT sum FROM reckoning WHERE id=?i", $id);
    }
	return round(($sum_agency / 100) * $percent, 2);
}

function select_agency_contract($connect, $id, $type = "", $day = ""){
	if($day == "cabinet")
		$today = date("Y-m-d", strToTime("+3 days"));
	else
		$today = date("Y-m-d");
	if($type == "all")
		$add = "";
	else
		$add = " AND date >= '$today' ";
	$row = $connect->getRow("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, status FROM ag_contract WHERE agency=?i ".$add." ORDER BY date DESC LIMIT 1", $id);
	if($row["id"])
		return $row;
	return FALSE;
}

function changes_reckoning_cabinet($connect, $id, $change, $id_parametr = false, $parametr = false){
	$changes = $connect->getOne("SELECT changes FROM reckoning WHERE id=?i", $id);
	$array = json_decode($changes, TRUE);
	
	if($id_parametr && $parametr) {
		$array[$change][$id_parametr][$parametr] = 1;
	}
	
	$data = json_encode($array);
	$connect->query("UPDATE reckoning SET changes=?s WHERE id=?i", $data, $id);
}

function calculate_position($sum, $number, $type, $days){
	//Тип 1 - за человека в сутки
	if($type == 1){
		$all_sum = ($sum * $number) * $days;
	//Тип 2 - за номер (дом)
	}elseif($type == 2){
		$all_sum = ($sum * $number) * $days;
	//Тип 3 - за заезд
	}elseif($type == 3){
		$all_sum = $sum * $number;
	}
	return $all_sum;
}

function recalculation_sum($connect, $id){
    $reck_type = $connect->getOne("SELECT type FROM reckoning WHERE id=?i", $id);

	$data = $connect->getAll("SELECT sum, number, type, days FROM position_reck WHERE schet=?i", $id);
	$all_sum = 0;
	foreach($data as $row){
		$sum = $row["sum"];
		$number = $row["number"];
		$type = $row["type"];
		$days = $row["days"];
		if($reck_type == 0)
          $all_sum += calculate_position($sum, $number, $type, $days);
		else
          $all_sum += $sum*$number;
	}
/*	$res = mysql_query("SELECT sum FROM payment WHERE schet='$id' AND type='5'");
	while($a = mysql_fetch_assoc($res)){
		$sum = $a['sum'];
		$all_sum-= $sum;
	}
*/	$connect->query("UPDATE reckoning SET sum=?s WHERE id=?i", $all_sum, $id);
}

function change_arrival_date($connect, $id){
	$date_z = $connect->getOne("SELECT date_z FROM position_reck WHERE schet=?i ORDER BY date_z", $id);
	if($date_z)
		$connect->query("UPDATE reckoning SET date_z=?s WHERE id=?i", $date_z, $id);
	$row = $connect->getRow("SELECT date_z, days, add_one_day FROM position_reck WHERE schet=?i ORDER BY DATE_ADD(date_z, INTERVAL (days + add_one_day) DAY) DESC LIMIT 1", $id);
	$date_z = date_change($row["date_z"]);
	if($date_z){
		$days = $row["days"];
		$add_one_day = $row["add_one_day"];
		if($add_one_day == 0)
			$days--;
		$date_v = date_sum($date_z, $days);
		$date_v = date("Y-m-d", $date_v);
		$connect->query("UPDATE reckoning SET date_v=?s WHERE id=?i", $date_v, $id);
	}
}

function get_month_profit(){
	global $array_month;
	$month = date("m");
	$year_now = date("Y");
	$html = "<select id='months' class='form-control'>";
	$next = $month + 7;
	$year = $year_now;
	if($next > 12){
		$next-= 12;
		$year++;
	}
	for($i=7; $i>1; $i--){
		$next--;
		if($next == 0){
			$next = 12;
			$year--;
		}
		$html.= "<option value='".$next."-".$year."'>".$array_month[$next]." ".$year."</option>";
	}
	$html.= "<option selected value=''>Текущий месяц</option>";
	$next = $month;
	$year = $year_now;
	for($i=5; $i>0; $i--){
		$next--;
		if($next == 0){
			$next = 12;
			$year = $year_now - 1;
		}
		$html.= "<option value='".$next."-".$year."'>".$array_month[$next]." ".$year."</option>";
	}
	$html.= "</select>";
	return $html;
}

function all_klient_bonus($connect, $id){
	$costs = $connect->getAll("SELECT id, active, sum, date FROM bonus WHERE turist=?i AND sum < 0 ORDER BY `date` ASC", $id);
	$sum = 0;
	$today = date("Y-m-d");

  $costsArray = array();

  $bonuses = $connect->getAll("SELECT id, active, sum, date, type, schet, `last_timestamp` FROM bonus WHERE turist=?i AND sum > 0 ORDER BY `date` ASC", $id);
  $bonusList = array();

  foreach ($bonuses as $bonus) {
    	/*$access = false;
    if($bonus['type'] == 4) {
      if($connect->getOne("SELECT id FROM reckoning WHERE date_v>?s AND id=?i", $today, $bonus["schet"])) $access = true;
  	} else {
      $access = true;
    }*/
    if(is_null($bonus['last_timestamp'])) {
      $dateO = new DateTime($bonus['date']);
      $dateO->modify("+1 year");
      $dateO->modify("+6 month");
      $bonus['last_timestamp'] = $dateO->format("U");
    }

    $bonus['timestamp'] = strToTime($bonus['date']);
    $bonusList[] = $bonus;
  }


  foreach ($costs as $cost) {
    $costSum = (int)$cost['sum'];
    foreach ($bonusList as $i => $bonus) {
      if( strtotime($bonusList[$i]['date']) <= strtotime($cost['date']) && $bonusList[$i]['last_timestamp'] >= strtotime($cost['date']) && $costSum < 0) {
        $c_sum = min($bonusList[$i]['sum'],abs($costSum));
        $costSum += $c_sum;
        $bonusList[$i]['sum'] -= $c_sum;
      }
    }
  }

  foreach ($bonusList as $bonus) {
    if($bonus['last_timestamp'] >= strtotime(date("d.m.Y"))) $sum += $bonus['sum'];
  }
	return $sum;
}

function check_bonus_payment_bank_card($connect, $id){
	$bonus = $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $id);
	if($bonus)
		$bonus = abs($bonus);
	else
		return FALSE;
	$reward = 0;
	$reck_reward = $connect->getOne("SELECT reward FROM reckoning WHERE id=?i", $id);
	if($reck_reward > 0)
		$reward = $reck_reward;
	else{
		$data = $connect->getAll("SELECT id FROM position_reck WHERE schet=?i", $id);
		foreach($data as $row)
			$reward+= get_reward_schet_position($connect, $row["id"]);
	}
	$reward = round($reward, 2);
	$reck = $connect->getRow("SELECT sum, id_com, id_dis, correction FROM reckoning WHERE id=?i", $id);
	$raz = 0;
	$bank_com = (BANK_COM * $reck["sum"]) / 100;
	if($reck["id_dis"]){
		$row = $connect->getRow("SELECT value, type FROM discount WHERE id=?i", $reck["id_dis"]);
		if($row["type"] == 1)
			$discount = $reck["sum"] * ($row["value"] / 100);
		else
			$discount = $row["value"];
		$raz+= $discount;
	}
	if($reck["correction"])
		$raz-= $reck["correction"];

	$reward = round($reward - $raz, 2);
	$max_bonus = ($reward / 2) - $bank_com;
	if($max_bonus < $bonus)
		$bonus = $max_bonus;
	if($bonus < 0)
		return FALSE;
	return round($bonus);
}

function all_waiting_bonus($connect, $id){
	$answer = $connect->getAll("SELECT sum, schet FROM bonus WHERE turist=?i", $id);
	$sum = 0;
	$today = date("Y-m-d");
	foreach($answer as $bonus)
		$sum+= $bonus["sum"];
	return $sum;
}

function determine_klient_bonus($connect, $id){
	$sum = 0;
	$data = $connect->getAll("SELECT sum, type FROM bonus WHERE turist=?i", $id);
	foreach($data as $row){
		$type = $row["type"];
		$bonus = $row["sum"];
		if($type == 1 OR $type == 3)
			$sum+= $bonus;
		elseif($type == 2 AND $bonus < 0)
			$sum+= $bonus;
	}
	return $sum;
}

function get_payment($connect, $id, $type = ""){
	$array = array();
	$index = 0;
	$data = $connect->getAll("SELECT id, pay_method, pay_number, status, sum, DATE_FORMAT(date, '%d.%m.%Y') as date, created, processed, terminal FROM payment WHERE schet=?i AND type=?i AND class='schet' ORDER BY (`payment`.`status` = 1) DESC", $id, $type);
	foreach($data as $row){
		$index++;
		$array[$index]["id"] = $row["id"];
		$array[$index]["pay_method_int"] = $row["pay_method"];
		if($row["pay_method"] == 1)
			$array[$index]["pay_method"] = "безналичный";
		elseif($row["pay_method"] == 2)
			$array[$index]["pay_method"] = "наличный";
		elseif($row["pay_method"] == 3)
			$array[$index]["pay_method"] = "сертификатом";
		elseif($row["pay_method"] == 4)
			$array[$index]["pay_method"] = "на месте";
		elseif($row["pay_method"] == 5) {
          $array[$index]["pay_method"] = "банковской картой";

          if($row['terminal'])
              $array[$index]["pay_method"] .= " через терминал";
    }elseif($row["pay_method"] == 6)
			$array[$index]["pay_method"] = "банковской картой";
		elseif($row["pay_method"] == 7)
			$array[$index]["pay_method"] = "СБП";

		$array[$index]["sum"] = add_null($row["sum"]);
		$array[$index]["pay_number"] = $row["pay_number"];
		$array[$index]["date"] = month_transform($row["date"]);
		$array[$index]["datetime"] = month_transform(date("d.m.Y H:i:s",$row["created"]));
		$array[$index]['status'] = $row['status'];
		$array[$index]["datetime_processed"] = NULL;
		if($row['processed']) {
		    $array[$index]["datetime_processed"] = month_transform(date("d.m.Y H:i:s",$row["processed"]));
        }
	}
	return $array;
}

function get_class_change($array, $change, $id_parametr, $parametr){
	global $id_rights;
	if($id_rights > 3){
		if($change == "object" AND $array["object"] == 1)
			return "class='changes'";
		if(isset($array["position"]) AND $change == "position" AND (isset($array["position"][$id_parametr][$parametr]) AND ($array["position"][$id_parametr][$parametr] == 1) OR (isset($array["position"][$id_parametr]["all"]) AND $array["position"][$id_parametr]["all"] == 1)))
			return "class='changes'";
		if(isset($array["turist"]) AND $change == "turist" AND $array["turist"][$id_parametr][$parametr] == 1)
			return "class='changes'";
	}
	return "";
}

function select_name_klient($connect, $id, $type = ""){
	$row = $connect->getRow("SELECT id, name, surname, otch FROM klient WHERE id=?i", $id);
	if(!$row["id"])
		return FALSE;
	if($type == "surname")
		$client = $row["surname"];
	else
		$client = $row["surname"]." ".$row["name"]." ".$row["otch"];
	return $client;
}

function getEmailByReck($connect, $id){
	$row = $connect->getRow("SELECT turist, agency FROM reckoning WHERE id=?i", $id);
	$agency = $row["agency"];
	$turist = $row["turist"];
	if($agency)
		$email = $connect->getOne("SELECT email FROM agency WHERE id=?i", $agency);
	else
		$email = $connect->getOne("SELECT email FROM klient WHERE id=?i", $turist);
	return $email;
}

function check_reservation_date($connect, $room, $date, $day, $id){
	$add_day = "";
	$add_id = "";
	if($connect->getOne("SELECT object.add_one_day FROM object, room WHERE object.id=room.id_obj AND room.id=object_room.id_category AND object_room.id=?i", $room) == 0){
		$day--;
		$add_day = " - 1";
	}
	if($id)
		$add_id = " AND id!=".$id;
	$date2 = date("Y-m-d", strToTime($date) + $day * 86400);
	if($connect->getOne("SELECT id FROM reservation WHERE room=?i AND ((date>=?s AND date<=?s) OR (DATE_ADD(date, INTERVAL (day".$add_day.") DAY)>=?s AND DATE_ADD(date, INTERVAL (day".$add_day.") DAY)<=?s) OR (date<?s AND DATE_ADD(date, INTERVAL (day".$add_day.") DAY)>=?s)) AND active=0 AND type_place=1".$add_id, $room, $date, $date2, $date, $date2, $date, $date2))
		return FALSE;
	if($connect->getOne("SELECT COUNT(*) FROM reservation WHERE room=?i AND ((date>=?s AND date<=?s) OR (DATE_ADD(date, INTERVAL (day".$add_day.") DAY)>=?s AND DATE_ADD(date, INTERVAL (day".$add_day.") DAY)<=?s) OR (date<?s AND DATE_ADD(date, INTERVAL (day".$add_day.") DAY)>=?s)) AND active=0 AND type_place=2".$add_id, $room, $date, $date2, $date, $date2, $date, $date2) > 1)
		return FALSE;
	if($connect->getOne("SELECT id FROM reservation WHERE room=?i AND ((date>=?s AND date<=?s) OR (DATE_ADD(date, INTERVAL (day".$add_day.") DAY)>=?s AND DATE_ADD(date, INTERVAL (day".$add_day.") DAY)<=?s) OR (date<?s AND DATE_ADD(date, INTERVAL (day".$add_day.") DAY)>=?s)) AND active=0 AND type_place=2".$add_id, $room, $date, $date2, $date, $date2, $date, $date2))
		return 2;
	$start = strToTime($date);
	$end = $start + $day * 86400;
	$on_sale = json_decode($connect->getOne("SELECT on_sale FROM object_room WHERE active=0 AND id=?i", $room), TRUE);
	foreach($on_sale as $month => $ranges){
		foreach($ranges as $range){
			$start_range = strToTime($range["d"]."-".$month);
			$end_range = $start_range + ($range["n"] - 1) * 86400;
			$after_start = $end_range + 86400;
			$prev_end = $start_range - 86400;
			$check_sale = 0;
			foreach($on_sale_check as $check_start => $check_end){
				if($prev_end == $check_end){
					$on_sale_check[$check_start] = $end_range;
					$check_sale = 1;
					break;
				}
				if($after_start == $check_start){
					$on_sale_check[$start_range] = $check_end;
					unset($on_sale_check[$check_start]);
					$check_sale = 1;
					break;
				}
			}
			if($check_sale == 0)
				$on_sale_check[$start_range] = $end_range;
		}
	}
	foreach($on_sale_check as $start_range => $end_range)
		if($start_range <= $start AND $end_range >= $end)
			return 1;
	return FALSE;
}

function make_folder($folder){
	if(!is_dir($folder)){
		if(!mkdir($folder))
			return FALSE;
		chmod($folder, 0777);
	}
	return TRUE;
}

function change_text_url($text, $type = ""){
	if($type == "object"){
		$text = str_replace("(", "", $text);
		$text = str_replace(")", "", $text);
		$text = str_replace(".", "", $text);
	}
	if($type == 'new') {
        $text = str_replace(".", "", $text);
        $text = str_replace(",", "-", $text);
        $text = str_replace(";", "-", $text);
        $text = str_replace(":", "-", $text);
        $text = str_replace("*", "-", $text);
        $text = str_replace("#", "-", $text);
        $text = str_replace("$", "-", $text);
        $text = str_replace("%", "-", $text);
        $text = str_replace("@", "-", $text);
        $text = str_replace("^", "-", $text);
        $text = str_replace(";", "-", $text);
        $text = str_replace("&", "", $text);
        $text = str_replace("?", "", $text);
        $text = str_replace("~", "", $text);
        $text = str_replace("'", "", $text);
        $text = str_replace('"', "", $text);
        $text = str_replace('»', "", $text);
        $text = str_replace('«', "", $text);
    }
	$text = str_replace("\"", "", $text);
	$text = str_replace("'", "", $text);
	$text = str_replace(" ", "-", $text);
	$text = mb_strtolower($text, "UTF-8");
	return $text;
}

function get_translit($string){
	$replace = array(
		"'"=>"",
		"`"=>"",
		"а"=>"a", "А"=>"a",
		"б"=>"b", "Б"=>"b",
		"в"=>"v", "В"=>"v",
		"г"=>"g", "Г"=>"g",
		"д"=>"d", "Д"=>"d",
		"е"=>"e", "Е"=>"e",
		"ё"=>"e", "Ё"=>"e",
		"ж"=>"zh", "Ж"=>"zh",
		"з"=>"z", "З"=>"z",
		"и"=>"i", "И"=>"i",
		"й"=>"y", "Й"=>"y",
		"к"=>"k", "К"=>"k",
		"л"=>"l", "Л"=>"l",
		"м"=>"m", "М"=>"m",
		"н"=>"n", "Н"=>"n",
		"о"=>"o", "О"=>"o",
		"п"=>"p", "П"=>"p",
		"р"=>"r", "Р"=>"r",
		"с"=>"s", "С"=>"s",
		"т"=>"t", "Т"=>"t",
		"у"=>"u", "У"=>"u",
		"ф"=>"f", "Ф"=>"f",
		"х"=>"h", "Х"=>"h",
		"ц"=>"c", "Ц"=>"c",
		"ч"=>"ch", "Ч"=>"ch",
		"ш"=>"sh", "Ш"=>"sh",
		"щ"=>"sch", "Щ"=>"sch",
		"ъ"=>"", "Ъ"=>"",
		"ы"=>"y", "Ы"=>"y",
		"ь"=>"", "Ь"=>"",
		"э"=>"e", "Э"=>"e",
		"ю"=>"yu", "Ю"=>"yu",
		"я"=>"ya", "Я"=>"ya",
		"і"=>"i", "І"=>"i",
		"ї"=>"yi", "Ї"=>"yi",
		"є"=>"e", "Є"=>"e"
	);
	$str = iconv("UTF-8","UTF-8//IGNORE", strtr($string, $replace));
	return $str;
}

function convert_number_to_string($num){

	if(!$num OR $num == 0)
		return 0;

	# Все варианты написания чисел прописью от 0 до 999 скомпануем в один небольшой массив
	$m = array(
		array('ноль'),
		array('-','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
		array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать','пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать'),
		array('-','-','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
		array('-','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
		array('-','одна','две')
	);

	# Все варианты написания разрядов прописью скомпануем в один небольшой массив
	$r = array(
		array('...ллион','','а','ов'), // используется для всех неизвестно больших разрядов
		array('тысяч','а','и',''),
		array('миллион','','а','ов'),
		array('миллиард','','а','ов'),
		array('триллион','','а','ов'),
		array('квадриллион','','а','ов'),
		array('квинтиллион','','а','ов')
	);

	$result = array();

	# Разложим исходное число на несколько трехзначных чисел и каждое полученное такое число обработаем отдельно
	foreach(array_reverse(str_split(str_pad($num, ceil(strlen($num)/3) * 3, '0', STR_PAD_LEFT), 3)) as $k => $p){
		$result[$k] = array();

		# Алгоритм, преобразующий трехзначное число в строку прописью
		foreach($n = str_split($p) as $kk => $pp)
			if(!$pp)
				continue;
			else
				switch($kk){
					case 0:
						$result[$k][] = $m[4][$pp];
						break;
					case 1:
						if($pp==1){
							$result[$k][] = $m[2][$n[2]];
							break 2;
						}else
							$result[$k][] = $m[3][$pp];
						break;
					case 2:
						if(($k==1) && ($pp<=2))
							$result[$k][] = $m[5][$pp];
						else
							$result[$k][] = $m[1][$pp];
						break;
				}
			$p*= 1;
			if(!$r[$k])
				$r[$k] = reset($r);

		# Алгоритм, добавляющий разряд, учитывающий окончание руского языка
		if($p && $k)
			switch(true){
				case preg_match("/^[1]$|^\\d*[0,2-9][1]$/",$p):
					$result[$k][] = $r[$k][0].$r[$k][1];
					break;
				case preg_match("/^[2-4]$|\\d*[0,2-9][2-4]$/",$p):
					$result[$k][] = $r[$k][0].$r[$k][2];
					break;
				default:
					$result[$k][] = $r[$k][0].$r[$k][3];
					break;
			}
		$result[$k] = implode(' ', $result[$k]);
	}

	return implode(' ',array_reverse($result));
}

function convert_name($name){
	return first_symbol_to_title(trim($name));
}

function first_symbol_to_title($string = ""){
	if($string == "")
		return "";
	$low = mb_strtolower($string, "UTF-8");
	$upper = mb_strtoupper($string, "UTF-8");
	$new_string = mb_substr($upper, 0, 1, "UTF-8").mb_substr($low, 1, strlen($low), "UTF-8");
	return $new_string;
}

function select_source_icon($source){
	$conf = connect_config();
	$source_array = $conf->source_array;
	if($source AND isset($source_array[$source])){
		$html = '<i class="fa '.$source_array[$source]["icon"].'" title="'.$source_array[$source]["name"].'"></i>';
		return $html;
	}
	return;
}

function select_index_source($code){
	$conf = connect_config();
	$source_array = $conf->source_array;
	foreach($source_array as $key => $row){
	    if(!is_array($row["code"]))
	       $row["code"] = [$row["code"]];

		if(in_array($code,$row["code"])){
			return $key;
		}
	}
	return 1;
}

function check_promotional_code($code, $object, $sum, $dates, $client_id = NULL, $connect = NULL){
	$promotional_code = [
        'sanata2019' => [
            "sum" => 500,
            "min-sum" => 1000
        ],
        'sanata2022' => [
            "procent" => 5,
            "access" => array(26),
            "start" => '2022-11-06 10:00:00',
            "end" => '2022-12-30 12:00:00',
            "work_start" => '2022-11-08 10:00:00',
            "work_end" => '2022-11-16 23:59:59'
        ]        
	];

	$reck_sum = $sum;

	if(!isset($promotional_code[$code]) && !is_null($connect) && mb_strlen($code) > 4 && mb_substr($code,0,4) === 'doc_') {
	    $doctorCard = $connect->getRow("SELECT * FROM `doctor_card` WHERE status = 3 AND promo = ?s",$code);
	    if($doctorCard) {
	        $promotional_code[$code] = [
	          'sum' => 500,
              'min-sum' => 20000
            ];
        }
    }

	if(isset($promotional_code[$code])){
		if(isset($promotional_code[$code]["min-sum"]) && $promotional_code[$code]["min-sum"] > $sum)
			return [
			   'msg' => "Использование промокода ".$code." невозможно: сумма заявки меньше ".$promotional_code[$code]["min-sum"]." рублей"
            ];
		$data = $promotional_code[$code];

		//$sum = $data["sum"];
		if (isset($data['sum'])) $sum = $data["sum"];
		if (isset($data['procent'])) $sum = round($reck_sum*($data["procent"]/100));

		if(isset($data["access"]) AND !in_array($object, $data["access"])){
			return FALSE;
		}

		if(isset($promotional_code[$code]["start"])){
			$start = strToTime($promotional_code[$code]["start"]);
			$end = strToTime($promotional_code[$code]["end"]);
			$check_start = strToTime($dates["arrival"]);
			$check_end = $check_start + $dates["days"] * 86400;
			//if(($check_start >= $start AND $check_start <= $end) OR ($check_end >= $start AND $check_end <= $end) OR ($check_start <= $start AND $check_end >= $end))
			if(!($check_end >= $start AND $check_end <= $end)) {
				return FALSE;
			}
		}

		if(isset($promotional_code[$code]["work_start"]) AND isset($promotional_code[$code]["work_end"])) {
			$start = strToTime($promotional_code[$code]["work_start"]);
			$end = strToTime($promotional_code[$code]["work_end"]);
			if(!(time() >= $start AND time() <= $end)) {
				return FALSE;
			}
		}

		if(!is_null($client_id) && !is_null($connect)) {
		    $promo_using = $connect->getOne("SELECT id FROM promo_code_using WHERE promo_code = ?s AND client_id = ?i LIMIT 1", $code, $client_id);
		    if($promo_using)
		        return FALSE;
        }

		return $sum;
	}
	return FALSE;
}

function date_check_holiday($date, $days, $type){
	$today = time();
	$date_start = strToTime($date);
	$day_sec = 86400;
	$weekend = 0;
	if($type == "w"){
		$day_week_1 = date("w", $date_start);
		$day_week_2 = date("w", $today);
		$ndays = round(($today - $date_start) / 86400);
		if($day_week_1 != 0)
			$ndays-= (7 + 1 - $day_week_1);
		else
			$ndays-= 1;
		if($day_week_2 != 0)
			$ndays-= $day_week_2;
		else
			$ndays-= 7;
		if($day_week_1 == 0)
			$weekend++;
		else
			$weekend+= 2;
		if($day_week_2 == 6)
			$weekend++;
		$week = intval($ndays/7);
		$weekend+= 2 * $week;
	}
	$days+= $weekend;
	$sec = $days * $day_sec;
	$date2 = $date_start + $sec;
	if($today <= $date2)
		return 1;
	else
		return 0;
}

function save_to_PDF($connect, $id, $doc){
	global $directory;
	if($doc == "schet"){
		include_once($directory."/core/document/schet.php");
		$file = review_schet($connect, "PDF", $id, "email");
	}
	if($doc == "obmen"){
		include_once($directory."/core/document/obmen.php");
		$file = review_obmen($connect, "PDF", $id, "email");
	}
	if($doc == "dover"){
		include_once($directory."/core/document/dover.php");
		$object = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $id);
		if(naimenovanie($connect, $object))
			$file = review_dover($connect, "PDF", $id, "", "email");
	}
	return $file;
}

function get_service_information(bool $agency = false){
	global $directory;
	include_once($directory."/config.php");
	$conf = new JConfig;
	$array = array();
	$array["firma"] = $conf->firma;
	$array["online"] = isset($conf->online)?$conf->online:null;
	$array["full_firma"] = $conf->full_firma;
	$array["email"] = $conf->Email;
	$array["new_email"] = $conf->new_email;
	$array["leg_address"] = $conf->leg_address;
	$array["sep_address"] = $conf->sep_address;
	$array["sep_address_ulyan"] = $conf->sep_address_ulyan;
	$array["sep_address_samara"] = $conf->sep_address_samara;
	$array["tel"] = $conf->tel_firma;
	$array["fax"] = $conf->fax_firma;
	$array["web_site"] = $conf->web_site;
	if($agency) {
      $array["BIK"] = $conf->BIK;
      $array["KS"] = $conf->KS;
      $array["bank"] = $conf->bank;
      $array["reck"] = $conf->reck;
    }
    else {
      $array["BIK"] = $conf->BIK2;
      $array["KS"] = $conf->KS2;
      $array["bank"] = $conf->bank2;
      $array["reck"] = $conf->reck2;
    }
	$array["INN"] = $conf->INN;
	$array["KPP"] = $conf->KPP;
	$array["OGRN"] = $conf->OGRN;
	$array["director"] = $conf->director;
	$array["director_pad"] = $conf->director_pad;
	$array["booker"] = $conf->booker;
	$array["reestr"] = $conf->reestr;
	$array["licensia"] = $conf->licensia;
	$array["dog_str"] = $conf->dog_str;
	$array["short_director_pad"] = $conf->short_director_pad;
	$array["work_time"] = "<strong>Будние</strong> - с 8:00 до 20:00<br /><strong>Суббота</strong> - с 10:00 до 15:00<br /><strong>Воскресенье</strong> - выходной";
	return $array;
}

function check_file($file){
	$head = @get_headers($file);
	if(preg_match("|200|", $head[0]))
		return 1;
	return 0;
}

function select_image($region, $object, $room){
	$url = "temp/images/".$region."/".$object."/".$room."/small/";
	$folder = opendir($url);
	while($image = readdir($folder)){
		if(($image != '.') AND ($image != '..') AND ($image))
			return $url.$image;
	}
	return "temp/images/default.jpg";
}

function calculate_distance($lat1, $long1, $lat2, $long2){

	$lat1 = $lat1 * M_PI / 180;
	$lat2 = $lat2 * M_PI / 180;
	$long1 = $long1 * M_PI / 180;
	$long2 = $long2 * M_PI / 180;

	$cl1 = cos($lat1);
	$cl2 = cos($lat2);
	$sl1 = sin($lat1);
	$sl2 = sin($lat2);
	$delta = $long2 - $long1;
	$cdelta = cos($delta);
	$sdelta = sin($delta);

	$y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
	$x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

	$ad = atan2($y, $x);
	$dist = round($ad * EARTH_RADIUS / 1000, 1);

	return $dist;
}

function get_head_image_sight($id){
	$folder = "temp/sights/".$id."/";
	if(file_exists($folder."head.jpg"))
		return $folder."head.jpg";
	else{
		$open = opendir($folder);
		while($image = readdir($open)){
			if(($image != '.') AND ($image != '..') AND ($image))
				return $folder.$image;
		}
	}
	return FALSE;
}

function change_auto_increment($connect, $table){
	$last = $connect->getOne("SELECT id FROM ".$table." ORDER BY id DESC");
	$connect->query("ALTER TABLE ".$table." AUTO_INCREMENT=?i", $last);
}

function file_is_image($file){
	$img = @getimagesize($file);
	if(!$img)
		return FALSE;
	if(!array_key_exists($img[2],  array(1 => "gif", 2 => "jpg", 3 => "png")))
		return FALSE;
	return TRUE;
}

function clear_array($array){
	foreach($array as $index => $value){
		$array[$index] = trim($value);
	}
	return $array;
}

function show_warning_session_expired(){
?>
	<div class="alert alert-danger">
		<i class="fa fa-exclamation-triangle"></i> Скорее всего, ваша сессия истекла.
	</div>
	<div class="text-right margin-top">
		<button class="btn btn-primary btn-sm" onclick="location.reload()"><i class="fa fa-undo"></i> Перезапустить CRM</button>
	</div>
<?php
}

function clear_email($email = ""){
	if($email == ""){
		return;
	}
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		return;
	}
	return mb_strtolower($email, "UTF-8");
}

function clear_telephone($telephone){
	if($telephone == "")
		return "";
	$tel = str_replace("-", "", $telephone);
	$tel = str_replace(" ", "", $tel);
	$tel = str_replace("+", "", $tel);
	$tel = str_replace("-", "", $tel);
	$tel = str_replace("(", "", $tel);
	$tel = str_replace(")", "", $tel);
	$first = $tel[0];
	if($first == 8)
		$tel = substr_replace($tel, "7", 0, 1);
	if($first == 9)
		$tel = "7".$tel;
	return $tel;
}

?>
