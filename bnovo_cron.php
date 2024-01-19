<?php
use GuzzleHttp\Client;

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');


$log = print_r($_POST, true).PHP_EOL;
file_put_contents('kostyl_booking.txt', $log, FILE_APPEND);


require_once __DIR__."/vendor/autoload.php";
$directory = dirname(__FILE__);
define("_FOLDERSITE_", $directory);

include_once($directory."/config.php");
$conf = new JConfig;
$sync = $conf->sync_base;
$unisender_api_key = $conf->unisender_api_key;
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/Mysql.Class.php");

include_once($directory."/core/lib/mail.php");
include_once($directory."/core/lib/sms.php");
include_once($directory."/core/lib/PHPMailer/class.phpmailer.php");

$clientCabinet = array(
	"link" => $conf->turist_cabinet
);
$objectCabinet = array(
	"link" => $conf->object_cabinet
);
$mail = array(
	"module" => $conf->email_module
);

$connect = connect_to_MySQL_directory();
$config = ConfigCRM::getInstance();
$config->connect = $connect;
$config->directory = $directory;
$config->clientCabinet = $clientCabinet;
$config->objectCabinet = $objectCabinet;

$configNew = \App\lib\CRM\Config\Client::getInstance();
$configNew->connect = $connect;
$configNew->directory = $directory;
$configNew->clientCabinet = $clientCabinet;
$configNew->objectCabinet = $objectCabinet;



/* ---------------------------- */
$response = array();
$id_obj = 922;
$account_id = 34311;


//AUTH
/*$url = 'https://api.reservationsteps.ru/v1/api/auth';
$data = array("username"=> 'info@sanata.online' , "password" => '6CGn3b3qF57lOi5nuxBwiIEzcCOVVXsu');
$postdata = json_encode($data);
$ch = curl_init($url); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$result = json_decode(curl_exec($ch), true);
curl_close($ch);

echo 'token='.$result['token'];*/

//AUTH

function get_bnovo_rooms_availability($id_obj, $account_id, $dfrom, $dto, $id_room_bnovo = false) {
	global $connect;

	$token = get_bnovo_token($connect);

	if (!$id_room_bnovo) {
		$all_rooms = $connect->getAll("SELECT * FROM `bnovo_rooms_mathes` WHERE `id_obj`=?i", $id_obj);
		$rooms = [];
		foreach ($all_rooms as $room) {
			$rooms[] = $room['id_bnovo'];
		}
	} else {
		$rooms = [];
		$rooms[] = $id_room_bnovo;
	}
	
	if (count($rooms)>0) {
		$data = array(
			'token'  => $token,
			'account_id' => $account_id,
			'dfrom' => $dfrom,
			'dto' => $dto,
			'roomtypes' => $rooms,
			'for_ota' => 1
		);

		echo '<pre>$data=';
		print_r($data);
		echo '</pre>';

		$ch = curl_init('https://api.reservationsteps.ru/v1/api/availability?' . http_build_query($data));

		echo 'https://api.reservationsteps.ru/v1/api/availability?' . http_build_query($data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$res = curl_exec($ch);
		curl_close($ch);
		$res = json_decode($res, true);

		//print_r($res);

		if (count($res['availability'])>0)

		foreach ($res['availability'] as $id_room_bnovo => $value) {
			foreach ($value as $date => $cnt) {
				echo "\r\n".$id_room_bnovo.'='.$date.'='.$cnt.'====';
				if($connect->getOne("SELECT COUNT(*) FROM bnovo_availability WHERE id_obj=?i AND account_id=?i AND id_room_bnovo=?i AND `date`=?s", $id_obj, $account_id, $id_room_bnovo, $date) == 0) {
					//INSERT
					$connect->query("INSERT INTO bnovo_availability SET id=0, id_obj=$id_obj, account_id=$account_id, id_room_bnovo=$id_room_bnovo, `date`='$date', cnt='$cnt'");
				} else {
					//UPDATE
					$connect->query("UPDATE bnovo_availability SET `cnt`='$cnt' WHERE id_obj=$id_obj AND account_id=$account_id AND id_room_bnovo=$id_room_bnovo AND `date`='$date'");
				}

			}
		}
	}
}

//get_bnovo_rooms_availability($id_obj, $account_id, date('Y-m-d'), date('Y-m-d', time()+86400*30));

function get_bnovo_rooms_prices($id_obj, $account_id, $dfrom, $dto, $id_plan_bnovo = false, $id_room_bnovo = false) {
	global $connect;

	echo 'get_bnovo_rooms_prices: id_obj='.$id_obj.' $account_id='.$account_id.' $dfrom='.$dfrom.' $dto='.$dto.' plan='.$id_plan_bnovo.' room='.$id_room_bnovo;

	$token = get_bnovo_token($connect);

	$all_plans = $connect->getAll("SELECT * FROM `bnovo_plans_mathes` WHERE `id_obj`=?i", $id_obj);
	$qplans = $plans = [];
	foreach ($all_plans as $plan) {
		$qplans[] = $plan['id_bnovo'];
		$plans[$plan['id_bnovo']] = $plan['id_plan'];
	}
	if ($id_plan_bnovo) {
		$qplans = [];
		$qplans[] = $id_plan_bnovo;
	}
	
	$all_rooms = $connect->getAll("SELECT * FROM `bnovo_rooms_mathes` WHERE `id_obj`=?i", $id_obj);
	$qrooms = $rooms = [];
	foreach ($all_rooms as $room) {
		$qrooms[] = $room['id_bnovo'];
		$rooms[$plan['id_bnovo']] = $room['id_room'];
	}
	if ($id_room_bnovo) {
		$qrooms = [];
		$qrooms[] = $id_room_bnovo;
	}

	$all_occus = $connect->getAll("SELECT * FROM `bnovo_occupancies_mathes` WHERE `id_obj`=?i", $id_obj);
	$occus = [];
	foreach ($all_occus as $occu) {
		$temp = [];
		$temp['id_place'] = $occu['id_place'];
		$temp['id_room'] = $occu['id_room'];
		$occus[$occu['id_bnovo']][] = $temp;
	}


	if (count($qplans)>0 && count($qrooms)>0) {
		$data = array(
			'token'  => $token,
			'account_id' => $account_id,
			'dfrom' => $dfrom,
			'dto' => $dto,
			'plans' => $qplans,
			'roomtypes' => $qrooms,
			'fields' => ['price','min_stay','min_stay_arrival']
		);

		echo '<pre>$data=';
		print_r($data);
		echo '</pre>';

		$ch = curl_init('https://api.reservationsteps.ru/v1/api/plans_data?' . http_build_query($data));

		echo 'https://api.reservationsteps.ru/v1/api/plans_data?' . http_build_query($data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$res = curl_exec($ch);
		curl_close($ch);
		$res = json_decode($res, true);
		print_r($res);

		foreach ($res['plans_data'] as $id_plan_bnovo => $roomdata) {
			foreach ($roomdata as $id_room_bnovo => $date_data) {
				foreach ($date_data as $date => $data) {
					echo $id_plan_bnovo.'='.$id_room_bnovo.'='.$date.'='.$data['price']."\r\n";

					//Проверяем наличие date_price для объекта и создаем если его нет и получаем айдишник
					if($connect->getOne("SELECT COUNT(*) FROM date_price WHERE `id_obj`=$id_obj AND `start`='$date' and `end`='$date' and `active`=0") == 0) {
						//echo "SELECT COUNT(*) FROM date_price WHERE `id_obj`=$id_obj AND `start`='$date' and `end`='$date' and `active`=0\r\n";
						$connect->query("INSERT INTO `date_price` SET `id`=0, `id_obj`=$id_obj, `active`=0, `start`='$date', `end`='$date'");
						$id_date_price = $connect->insertId();
						//echo "INSERT INTO `date_price` SET `id`=0, `id_obj`=$id_obj, `active`=0, `start`='$date', `end`='$date'\r\n";
					} else {
					    $id_date_price = $connect->getOne("SELECT id FROM date_price WHERE `id_obj`=$id_obj AND `start`='$date' and `end`='$date' and `active`=0");
					}
					echo 'id_date_price='.$id_date_price."\r\n";

					if (count($occus[$id_room_bnovo])>0) {
						echo '<pre>$occus[$id_room_bnovo]';
						print_r($occus[$id_room_bnovo]);
						echo '</pre>';
						foreach($occus[$id_room_bnovo] as $occu) {
							//Проверяем наличие нужных записей в ranges
							if($connect->getOne("SELECT COUNT(*) FROM ranges WHERE `id_obj`=$id_obj AND `type`=1 AND `place`='".$occu['id_place']."' AND `id_date`=$id_date_price AND `rate_plan`='".$plans[$id_plan_bnovo]."'") == 0) {
								echo "SELECT COUNT(*) FROM ranges WHERE `id_obj`=$id_obj AND `type`=1 AND `place`='".$occu['id_place']."' AND `id_date`=$id_date_price AND `rate_plan`='".$plans[$id_plan_bnovo]."'"."\r\n";
								$connect->query("INSERT INTO `ranges` SET `id`=0, `name`='from_bnovo', `id_obj`=$id_obj, `active`=0, `show_date`=1, `place`='".$occu['id_place']."', `id_date`=$id_date_price, `counter`=1, `rate_plan`=".$plans[$id_plan_bnovo].", `treatment`=0");
								$id_range = $connect->insertId();
							} else {
								$id_range = $connect->getOne("SELECT id FROM ranges WHERE `id_obj`=$id_obj AND `type`=1 AND `place`='".$occu['id_place']."' AND `id_date`=$id_date_price AND `rate_plan`='".$plans[$id_plan_bnovo]."'");
							}
							echo 'id_range='.$id_range."\r\n";

							echo '<pre>';
							print_r($occu);
							echo '</pre>';
							if ($id_range>0) {							
								if($connect->getOne("SELECT COUNT(*) FROM price WHERE `id_room`=".$occu['id_room']." AND `id_range`=$id_range") == 0) {
									echo "SELECT COUNT(*) FROM price WHERE `id_room`=".$occu['id_room']." AND `id_range`=$id_range"."\r\n";
									$connect->query("INSERT INTO `price` SET `id`=0, `id_room`='".$occu['id_room']."', `price`=".$data['price'].", `id_range`=$id_range, `active`=0, `date_last_save`='".date('H:i:s d.m.Y')."', `manager`='from bnovo'");
									$id_range = $connect->insertId();
									echo "INSERT INTO `price` SET `id`=0, `id_room`='".$occu['id_room']."', `price`=".$data['price'].", `id_range`=$id_range, `active`=0, `date_last_save`='".date('H:i:s d.m.Y')."', `manager`='from bnovo'"."\r\n";
								} else {
									$connect->query("UPDATE price SET `price`=?i, `synchronized`=0 WHERE `id_room`=".$occu['id_room']." AND `id_range`=$id_range", $data['price']);
									echo $connect->last_query()."\r\n";
								}
							}
						}
					}

					
				}
			}
		}

	}

}

//get_bnovo_rooms_prices($id_obj, $account_id, date('Y-m-d'), date('Y-m-d', time()+86400*14));

//echo '4444';

$data = $connect->getRow("SELECT * FROM `bnovo_data_updates` WHERE `worked`=0 ORDER BY id ASC LIMIT 1 ");
if ($data && $data['data']!='') {
	//$connect -> query("UPDATE `bnovo_data_updates` SET `worked`=1, `worked_datetime`=NOW() WHERE `id`=$data[id]");
	echo $connect->last_query()."\r\n";
	echo 'ID записи к обработке: '.$data['id'].'<br>';
	$data = json_decode($data['data'], true);

	$data['hotel_id'] = $id_obj;

	if (count($data['data']['prices'])>0) {
		ECHO '1111';
		$prices = $data['data']['prices'];
		foreach ($prices as $id_plan_bnovo => $rooms_data) {
			foreach ($rooms_data as $id_room_bnovo => $dates) {
				$mindate = 99999999999; $maxdate = 0;
				foreach ($dates as $date) {
					if (strtotime($date)<$mindate) $mindate = strtotime($date);
					if (strtotime($date)>$maxdate) $maxdate = strtotime($date);
				}

				if ($maxdate<>0 && $mindate<>99999999999) {
					echo 'mindate='.date('Y-m-d', $mindate).' maxdate='.date('Y-m-d', $maxdate).' id_room_bnovo='.$id_room_bnovo.' id_plan_bnovo='.$id_plan_bnovo."\r\n";
					get_bnovo_rooms_prices($data['hotel_id'], $data['account_id'], date('Y-m-d', $mindate), date('Y-m-d', $maxdate), $id_plan_bnovo, $id_room_bnovo);
				}
			}
		}
	}

	if (count($data['data']['rooms'])>0) {
		$rooms = $data['data']['rooms'];
		echo '<pre>';
		print_r($rooms);
		echo '</pre>';
		foreach ($rooms as $id_room_bnovo => $dates) {
			$mindate = 99999999999; $maxdate = 0;
			foreach ($dates as $date) {
				if (strtotime($date)<$mindate) $mindate = strtotime($date);
				if (strtotime($date)>$maxdate) $maxdate = strtotime($date);
			}

			if ($maxdate<>0 && $mindate<>99999999999) {
				echo 'mindate='.date('Y-m-d', $mindate).' maxdate='.date('Y-m-d', $maxdate).' id_room_bnovo='.$id_room_bnovo."\r\n";
				get_bnovo_rooms_availability($data['hotel_id'], $data['account_id'], date('Y-m-d', $mindate), date('Y-m-d', $maxdate), $id_room_bnovo);
			}
		}
	}
}

echo '<meta http-equiv="refresh" content="0,URL=/CRM/bnovo_cron.php">';

?>