<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_prices_api($connect){
	global $session_login;

    try {

		$client = new \GuzzleHttp\Client(['verify' => false]);


		function SyncDatesPack($client, $connect, $datesAr) {
			if (count($datesAr['data'])>0) {
				echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/daterange/set/".$datesAr['id'].'<br>';
				/*echo '<pre>datesAr';
				print_r($datesAr);
				echo '</pre>';*/

				$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/daterange/set/".$datesAr['id'],[
					'form_params' => $datesAr
				]);			
				$res = json_decode($res->getBody()->getContents(),true);
				echo '<pre>res';
				print_r($res);
				echo '</pre>';
				
				if(array_key_exists('success',$res)) {
					$success = (bool)(int)$res['success'];
					if($success) {
						foreach ($datesAr['data'] as $date) { 
							//echo "UPDATE `date_price` SET `synchronized` = '1' WHERE `id` = $date[id]<br>";
							$connect->query("UPDATE `date_price` SET `synchronized` = '1' WHERE `id` = ?i",$date['id']);
						}
					}
					else {
						echo 'Ошибка при сихнонизации SyncDatesPack<br>';
						echo '<pre>';
						print_r($res);
						echo '</pre>';
					}
				}	
			}			
		}		

		//if ($session_login==75) {
			//синхронизация date_price по-новому - пачками

			$dateRanges = $connect->getAll("SELECT `id`, `start`, `end`, `id_obj`, `active` FROM `date_price` WHERE `synchronized` = 0");

			$i = 0;
			$dateRangeAr = [];
			$dateRangeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$dateRangeAr['id'] = 1;
			$dateRangeAr['uid'] = 1;

			foreach ($dateRanges as $dateRange) { 

				if ($i==0) $dateRangeAr['data'] = [];

				if ($dateRange['start']==$dateRange['end']) $bnovo_end_of_date = 86399; else $bnovo_end_of_date = 0;

				$daterangeData = [];
				$daterangeData['id'] = $dateRange['id'];
				$daterangeData['status'] = (int)(!$dateRange['active']);
				$daterangeData['start_timestamp'] = strtotime($dateRange['start']);
				$daterangeData['end_timestamp'] = strtotime($dateRange['end'])+$bnovo_end_of_date;
				$daterangeData['resort_id'] = $dateRange['id_obj'];
		
				$dateRangeAr['data'][] = $daterangeData;
				$i++;
				if ($i>=500) {
					$start = time();
					echo 'start timestamp='.$start.'<br>';
					SyncDatesPack($client, $connect, $dateRangeAr);
					$end = time();
					echo 'end timestamp='.$end.'<br>';
					echo 'between='.($end - $start).'<br>';
					$i=0;
				}
			}
			SyncDatesPack($client, $connect, $dateRangeAr);

		/*} else {
			//синхронизация date_price по-старому - по-одному
			$dateRanges = $connect->getAll("SELECT `id`, `start`, `end`, `id_obj`, `active` FROM `date_price` WHERE `synchronized` = 0");

			foreach ($dateRanges as $dateRange) {

				if ($dateRange['start']==$dateRange['end']) $bnovo_end_of_date = 86399; else $bnovo_end_of_date = 0;

				$dateRangeAr = [];
				$dateRangeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
				$dateRangeAr['id'] = $dateRange['id'];
				$dateRangeAr['status'] = (int)(!$dateRange['active']);
				$dateRangeAr['start_timestamp'] = strtotime($dateRange['start']);
				$dateRangeAr['end_timestamp'] = strtotime($dateRange['end'])+$bnovo_end_of_date;
				$dateRangeAr['resort_id'] = $dateRange['id_obj'];
				$dateRangeAr['uid'] = 1;

				echo "Отправка запроса на https://sites.tonia.ru/api/resort/price/daterange/set/".$dateRange['id'].'<br>';
				echo '<pre>$bnovo_end_of_date = '.$bnovo_end_of_date.' $dateRange';
				print_r($dateRange);
				echo '</pre>';			
				echo '<pre>$dateRangeAr';
				print_r($dateRangeAr);
				echo '</pre>';

				$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/daterange/set/".$dateRange['id'],[
					'form_params' => $dateRangeAr
				]);

				$res = json_decode($res->getBody()->getContents(),true);
				if(array_key_exists('success',$res)) {
					$success = (bool)(int)$res['success'];
					if($success) {
						$connect->query("UPDATE `date_price` SET `synchronized` = '1' WHERE `id` = ?i",$dateRange['id']);
					}
					else {
						echo $res['msg'].": ".$dateRange['id'].'<br>';
						print_r($res['fail_messages']);
						break;
					}
				}
			}
		}*/


		function SyncRangesPack($client, $connect, $rangeAr) {
			if (count($rangeAr['data'])>0) {
				echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/range/set/".$rangeAr['id'].'<br>';
				/*echo '<pre>rangeAr';
				print_r($rangeAr);
				echo '</pre>';*/

				$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/range/set/".$rangeAr['id'],[
					'form_params' => $rangeAr
				]);			
				$res = json_decode($res->getBody()->getContents(),true);
				echo '<pre>res';
				print_r($res);
				echo '</pre>';
				
				if(array_key_exists('success',$res)) {
					$success = (bool)(int)$res['success'];
					if($success) {
						foreach ($rangeAr['data'] as $range) { 
							//echo "UPDATE `ranges` SET `synchronized` = '1' WHERE `id` = $range[id]<br>";
							$connect->query("UPDATE `ranges` SET `synchronized` = '1' WHERE `id` = ?i",$range['id']);
						}
					}
					else {
						echo 'Ошибка при сихнонизации SyncRangesPack<br>';
						echo '<pre>';
						print_r($res);
						echo '</pre>';
					}
				}	
			}			
		}	


		//if ($session_login==75) {
			//синхронизация ranges по-лновому - пачками

			$ranges = $connect->getAll("SELECT `id`, `id_obj`, `name`, `type`, `active`, `show_date`, `place`, `id_date`, `counter`, `rate_plan`, `treatment` FROM `ranges` WHERE `synchronized` = 0");

			$i=0;
			$rangeAr = [];
			$rangeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$rangeAr['id'] = 1;	
			$rangeAr['uid'] = 1;

			foreach ($ranges as $range) { 

				if ($i==0) $rangeAr['data'] = [];

				$rangeData = [];
				$rangeData['id'] = $range['id'];
				$rangeData['name'] = $range['name'];
				$rangeData['type'] = $range['type'];
				$rangeData['status'] = (int)(!$range['active']);
				$rangeData['resort_id'] = $range['id_obj'];
				$rangeData['show_date'] = $range['show_date'];
				$rangeData['place_id'] = $range['place'];
				$rangeData['daterange_id'] = $range['id_date'];
				$rangeData['counter'] = $range['counter'];
				$rangeData['rate_id'] = $range['rate_plan'];
				$rangeData['treatment'] = $range['treatment'];
		
				$rangeAr['data'][] = $rangeData;
				$i++;
				if ($i>=500) {
					$start = time();
					echo 'start timestamp='.$start.'<br>';
					SyncRangesPack($client, $connect, $rangeAr);
					$end = time();
					echo 'end timestamp='.$end.'<br>';
					echo 'between='.($end - $start).'<br>';
					$i=0;
				}
			}
			SyncRangesPack($client, $connect, $rangeAr);			
			
		/*} else {
			//синхронизация ranges по-старому - по-одному
			$ranges = $connect->getAll("SELECT `id`, `id_obj`, `name`, `type`, `active`, `show_date`, `place`, `id_date`, `counter`, `rate_plan`, `treatment` FROM `ranges` WHERE `synchronized` = 0");

			foreach ($ranges as $range) {
				$rangeAr = [];
				$rangeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
				$rangeAr['id'] = $range['id'];
				$rangeAr['name'] = $range['name'];
				$rangeAr['type'] = $range['type'];
				$rangeAr['status'] = (int)(!$range['active']);
				$rangeAr['resort_id'] = $range['id_obj'];
				$rangeAr['show_date'] = $range['show_date'];
				$rangeAr['place_id'] = $range['place'];
				$rangeAr['daterange_id'] = $range['id_date'];
				$rangeAr['counter'] = $range['counter'];
				$rangeAr['rate_id'] = $range['rate_plan'];
				$rangeAr['treatment'] = $range['treatment'];
				$rangeAr['uid'] = 1;
	
				echo "Отправка запроса на https://sites.tonia.ru/api/resort/price/range/set/".$range['id'].'<br>';
	
				$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/range/set/".$range['id'],[
					'form_params' => $rangeAr
				]);
	
				$res = json_decode($res->getBody()->getContents(),true);
				if(array_key_exists('success',$res)) {
					$success = (bool)(int)$res['success'];
					if($success) {
						$connect->query("UPDATE `ranges` SET `synchronized` = '1' WHERE `id` = ?i",$range['id']);
					}
					else {
						echo $res['msg'].": ".$range['id'].'<br>';
						print_r($res['fail_messages']);
						break;
					}
				}
			}
		}*/

		/*$pricesStartYear = 2018;
		$pricesYearWhere = "";
		for($i = $pricesStartYear; $i < date("Y")+1; $i++) {
			if(mb_strlen($pricesYearWhere) > 0) {
				$pricesYearWhere .= " OR";
			}
			else {
				$pricesYearWhere .= " (";
			}

			$pricesYearWhere .= " (date_last_save LIKE '%.".$i."%' OR date_last_save LIKE '%-".$i."%')";
			if($i == date("Y")) {
				$pricesYearWhere .= ") ";
			}
		}*/

		function SyncPricesPack($client, $connect, $priceAr) {
			if (count($priceAr['data'])>0) {
				echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'].'<br>';
				/*echo '<pre>priceAr';
				print_r($priceAr);
				echo '</pre>';*/

				$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'],[
					'form_params' => $priceAr
				]);			
				$res = json_decode($res->getBody()->getContents(),true);
				echo '<pre>res';
				print_r($res);
				echo '</pre>';
				
				if(array_key_exists('success',$res)) {
					$success = (bool)(int)$res['success'];
					if($success) {
						foreach ($priceAr['data'] as $price) { 
							//echo "UPDATE `price` SET `synchronized` = '1' WHERE `id` = $price[id]<br>";
							$connect->query("UPDATE `price` SET `synchronized` = '1' WHERE `id` = ?i",$price['id']);
						}
					}
					else {
						echo 'Ошибка при сихнонизации SyncPricesPack<br>';
						echo '<pre>';
						print_r($res);
						echo '</pre>';
					}
				}	
			}			
		}

		//$prices = $connect->getAll("SELECT `id`, `id_room`, `price`, `id_range`, `active` FROM `price` WHERE `synchronized` = 0 AND ".$pricesYearWhere." LIMIT 5000");
		$prices = $connect->getAll("SELECT `id`, `id_room`, `price`, `id_range`, `active` FROM `price` WHERE `synchronized` = 0 LIMIT 5000");

		//if ($session_login==75) {
			//синхронизация prices по новому - пачками
			$i=0;
			$priceAr = [];
			$priceAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$priceAr['id'] = 1;	
			$priceAr['uid'] = 1;	
			//$priceAr['data'] = [];
			foreach ($prices as $price) { 
				if ($i==0) $priceAr['data'] = [];
				$priceData = [];

				$priceData['id'] = $price['id'];
				$priceData['room_id'] = $price['id_room'];
				$priceData['value'] = (float)$price['price'];
				$priceData['range_id'] = $price['id_range'];
				$priceData['status'] = (int)(!$price['active']);				

				$priceAr['data'][] = $priceData;
				$i++;
				if ($i>=500) {
					$start = time();
					echo 'start timestamp='.$start.'<br>';
					SyncPricesPack($client, $connect, $priceAr);
					$end = time();
					echo 'end timestamp='.$end.'<br>';
					echo 'between='.($end - $start).'<br>';
					$i=0;
				}
			}
			SyncPricesPack($client, $connect, $priceAr);
			/*if (count($priceAr['data'])>0) {
				echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'].'<br>';
				echo '<pre>priceAr';
				print_r($priceAr);
				echo '</pre>';

				$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'],[
					'form_params' => $priceAr
				]);			
				$res = json_decode($res->getBody()->getContents(),true);
				
				if(array_key_exists('success',$res)) {
					$success = (bool)(int)$res['success'];
					if($success) {
						foreach ($prices as $price) { 
							$connect->query("UPDATE `price` SET `synchronized` = '1' WHERE `id` = ?i",$price['id']);
						}
					}
					else {
						echo $res['msg'].": ".$price['id'].'<br>';
						print_r($res['fail_messages']);
					}
				}	
			}*/		
		/*} else {
			//синхронизация цен по старому - по одной
			foreach ($prices as $price) {
				$priceAr = [];
				$priceAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
				$priceAr['id'] = $price['id'];
				$priceAr['room_id'] = $price['id_room'];
				$priceAr['value'] = (float)$price['price'];
				$priceAr['range_id'] = $price['id_range'];
				$priceAr['status'] = (int)(!$price['active']);
				$priceAr['uid'] = 1;

				echo "Отправка запроса на https://sites.tonia.ru/api/resort/price/set/".$price['id'].'<br>';

				$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/set/".$price['id'],[
					'form_params' => $priceAr
				]);

				$res = json_decode($res->getBody()->getContents(),true);
				if(array_key_exists('success',$res)) {
					$success = (bool)(int)$res['success'];
					if($success) {
						$connect->query("UPDATE `price` SET `synchronized` = '1' WHERE `id` = ?i",$price['id']);
					}
					else {
						echo $res['msg'].": ".$price['id'].'<br>';
						print_r($res['fail_messages']);
						break;
					}
				}
			}
		}*/

		echo '<br>end of prices sync';

		return true;
	}
	catch (Exception $e) {
		echo 'Exception='.$e->getMessage();
		return false;
	}

}

?>
