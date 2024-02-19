<?php

require_once __DIR__.'/../../vendor/autoload.php';


	if(!sync_files($connect)) {
		return FALSE;
	}


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
            /*echo '<pre>res';
            print_r($res);
            echo '</pre>';*/
            
            if(array_key_exists('success',$res)) {
                $success = (bool)(int)$res['success'];
                if($success) {
                    foreach ($priceAr['data'] as $price) { 
                        echo "UPDATE `price` SET `synchronized` = '1' WHERE `id` = $price[id]<br>";
                        $connect->query("UPDATE `price` SET `synchronized` = '1' WHERE `id` = ?i",$price['id']);
                    }
                }
                else {
                    echo $res['msg'].": ".$price['id'].'<br>';
                    print_r($res['fail_messages']);
                }
            }	
        }			
    }    

	try {

		$client = new \GuzzleHttp\Client(['verify' => false]);

		//$prices = $connect->getAll("SELECT `id`, `id_room`, `price`, `id_range`, `active` FROM `price` WHERE `synchronized` = 0 AND ".$pricesYearWhere." LIMIT 5000");
		$prices = $connect->getAll("SELECT `id`, `id_room`, `price`, `id_range`, `active` FROM `price` WHERE `synchronized` = 0 LIMIT 5000");

		//if ($session_login==75) {
			//синхронизация цен по новому - пачками
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
				if ($i>=50) {
					$start = time();
					echo 'start timestamp='.$start.'<br>';
					SyncPricesPack($client, $connect, $priceAr);
					$end = time();
					echo 'start timestamp='.$end.'<br>';
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

		echo '<br>end of sync';

		return true;
	}
	catch (Exception $e) {
		echo 'Exception='.$e->getMessage();
		return false;
	}

?>
