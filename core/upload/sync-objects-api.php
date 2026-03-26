<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_api($connect){
	global $session_login;

	if(!sync_files($connect)) {
		return FALSE;
	}

	try {

		$client = new \GuzzleHttp\Client(['verify' => false]);
		$directions = $connect->getAll("SELECT `id`, `name`, `name_rod`, `description`, `meta_desc`, `sort` FROM `direction_object` WHERE `id_country` = 1 AND `synchronized` = 0");
		$directionsCond = "";
		foreach ($directions as $direction) {

			if($directionsCond)
				$directionsCond .= ' OR ';

			$directionsCond .= '`region`.`id_direction` = '.$direction['id'];

			$directionAr = [
				'name' => $direction['name'],
				'name_genitive' => $direction['name_rod'],
				'parent_id' => 0,
				'description' => $direction['description']?$direction['description']:$direction['meta_desc'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'sort' => $direction['sort'],
				//'uri' => '/направления/'.change_text_url($direction['name']),
				'uri' => '/'.change_text_url($direction['name']),
				'status' => 1
			];

			//echo "Отправка запроса на  https://sites.tonia.ru/api/location/direction/set/".$direction['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/location/direction/set/".$direction['id'],[
				'form_params' => $directionAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				/*echo 'SUCCESS1:<br>';
				echo '<pre>';
				print_r($res);
				echo '</pre>';*/
				$success = (bool)(int)$res['success'];
				if($success) {
					//echo 'SUCCESS2:<br>';
					$connect->query("UPDATE `direction_object` SET `synchronized` = '1' WHERE `id` = ?i",$direction['id']);
					$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `direction` = ?i", $direction['id']);
				}
			} else {
				/*echo 'ERROR:<br>';
				echo '<pre>';
				print_r($res);
				echo '</pre>';*/
			}


		}

		$regions = $connect->getAll("SELECT `region`.`id` AS `id`, `region`.`name` AS `name`, `region`.`name_rod` AS `name_rod`, `region`.`id_direction` AS `id_direction`, `region`.`state_program` AS `state_program`, `region`.`state_program_start_timestamp` AS `state_program_start_timestamp`, `region`.`state_program_end_timestamp` AS `state_program_end_timestamp`, `direction_object`.`name` AS `direction_name`, `region`.`description` AS `description`, `region`.`meta_desc` AS `meta_desc` FROM `region` INNER JOIN `direction_object` ON `region`.`id_direction` = `direction_object`.`id` WHERE `region`.`id_country` = 1 AND `region`.`synchronized` = 0");

		foreach ($regions as $region) {
			$regionAr = [
				'name' => $region['name'],
				'name_genitive' => $region['name_rod'],
				'state_program' => $region['state_program'],
				'state_program_start_timestamp' => $region['state_program_start_timestamp'],
				'state_program_end_timestamp' => $region['state_program_end_timestamp'],
				'parent_id' => $region['id_direction'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'description' => $region['description']?$region['description']:$region['meta_desc'],
				//'uri' => '/направления/'.change_text_url($region['direction_name']).'/'.change_text_url($region['name']),
				'uri' => '/'.change_text_url($region['direction_name']).'/'.change_text_url($region['name']),
				'status' => 1
			];

			//echo "Отправка запроса на  https://sites.tonia.ru/api/location/region/set/".$region['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/location/region/set/".$region['id'],[
				'form_params' => $regionAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				/*echo 'SUCCESS1:<br>';
				echo '<pre>';
				print_r($res);
				echo '</pre>';*/
				if($success) {
					echo 'SUCCESS2:<br>';
					$connect->query("UPDATE `region` SET `synchronized` = '1' WHERE `id` = ?i",$region['id']);
					$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `id_reg` = ?i", $region['id']);
					$connect->query("UPDATE `direction_object` SET `synchronized` = '0' WHERE `id_reg` = ?i AND (`direction_object`.`id_country` = 0 OR `direction_object`.`id_country` IS NULL)", $region['id']);
				}
			} else {
				/*echo 'ERROR:<br>';
				echo '<pre>';
				print_r($res);
				echo '</pre>';*/
			}


		}

		$regional_directions = $connect->getAll("SELECT `direction_object`.`id` AS `id`, `direction_object`.`name` AS `name`, `direction_object`.`name_rod` AS `name_rod`, `direction_object`.`id_reg` AS `id_reg`, `region`.`name` AS `name_reg`, `region`.`id_direction` AS `region_direction_id`, `direction_object2`.`name` AS `dir_name`, `direction_object`.`description` AS `description`, `direction_object`.`sort` AS `sort` FROM `direction_object` INNER JOIN `region` ON `region`.`id` = `direction_object`.`id_reg` INNER JOIN `direction_object` AS `direction_object2` ON `direction_object2`.`id` = `region`.`id_direction` WHERE (`direction_object`.`id_country` = 0 OR `direction_object`.`id_country` IS NULL)  AND `direction_object`.`id_reg` > 0 AND `direction_object`.`synchronized` = 0");

		foreach ($regional_directions as $regional_direction) {
			$regionalDirectionAr = [
				'name' => $regional_direction['name'],
				'name_genitive' => $regional_direction['name_rod'],
				'parent_id' => $regional_direction['id_reg'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'description' => $regional_direction['description'],
				'sort' => $regional_direction['sort'],
				//'uri' => '/направления/'.change_text_url($regional_direction['dir_name']).'/'.change_text_url($regional_direction['name_reg']).'/'.change_text_url($regional_direction['name']),
				'uri' => '/'.change_text_url($regional_direction['dir_name']).'/'.change_text_url($regional_direction['name_reg']).'/'.change_text_url($regional_direction['name']),
				'status' => 1
			];

			//echo "Отправка запроса на https://sites.tonia.ru/api/location/regional_direction/set/".$regional_direction['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/location/regional_direction/set/".$regional_direction['id'],[
				'form_params' => $regionalDirectionAr
			]);


			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				/*echo 'SUCCESS1:<br>';
				echo '<pre>';
				print_r($res);
				echo '</pre>';*/
				if($success) {
					echo 'SUCCESS2:<br>';
					$connect->query("UPDATE `direction_object` SET `synchronized` = '1' WHERE `id` = ?i",$regional_direction['id']);
					$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `region_direction_id` = ?i", $regional_direction['id']);
				}
			} else {
				/*echo 'ERROR:<br>';
				echo '<pre>';
				print_r($res);
				echo '</pre>';*/
			}


		}

		$types = $connect->getAll("SELECT `id`, `name`, `name_prepositional` FROM `type_object` WHERE `synchronized` = 0");

		foreach ($types as $type) {
			$typeAr = [];
			$typeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$typeAr["id"] = $type['id'];
			$typeAr["name"] = $type['name'];
			$typeAr['name_prepositional'] = $type['name_prepositional'];
			$typeAr['status'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/type/set/".$type['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/type/set/".$type['id'],[
				'form_params' => $typeAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `type_object` SET `synchronized` = '1' WHERE `id` = ?i",$type['id']);
					$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `type` = ?i",$type['id']);
				}
			}
		}

		$profiles = $connect->getAll("SELECT `id`, `name`, `description` FROM `profile` WHERE `synchronized` = 0");

		foreach ($profiles as $profile) {
			$profileAr = [];
			$profileAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$profileAr["id"] = $profile['id'];
			$profileAr["name"] = $profile['name'];
			$profileAr["description"] = $profile['description'];
			$profileAr['uri'] = '/профили-лечения/'.change_text_url($profile['name']);
			$profileAr['status'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/object/profile/set/".$profile['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/object/profile/set/".$profile['id'],[
				'form_params' => $profileAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					if(!sync_bounds($connect,[
						'type' => 'treatment_profile',
						'id' => $profile['id']
					])) {
						return FALSE;
					}
					else {
						$connect->query("UPDATE `profile` SET `synchronized` = '1' WHERE `id` = ?i",$profile['id']);
					}
				}
			}
		}

		$methods = $connect->getAll("SELECT `id`, `name`, `description` FROM `methods` WHERE `synchronized` = 0");

		foreach ($methods as $method) {
			$methodAr = [];
			$methodAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$methodAr["id"] = $method['id'];
			$methodAr["name"] = $method['name'];
			$methodAr["description"] = $method['description'];
			$methodAr['uri'] = '/методы-лечения/'.change_text_url($method['name']);
			$methodAr['status'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/object/method/set/".$method['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/object/method/set/".$method['id'],[
				'form_params' => $methodAr
			]);

			$res = json_decode($res->getBody(),true);

			/*echo 'res=';
			echo '<pre>';
			print_r($res);
			echo '</pre>';*/

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					if(!sync_bounds($connect,[
						'type' => 'treatment_method',
						'id' => $method['id']
					])) {
						return FALSE;
					}
					else {
						$connect->query("UPDATE `methods` SET `synchronized` = '1' WHERE `id` = ?i",$method['id']);
					}
				}
			}
		}

		$sights = $connect->getAll("SELECT * FROM `sights` WHERE `synchronized` = 0 and `path`<>''");

		foreach ($sights as $sight) {
			$sightAr = [];
			$sightAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$sightAr["id"] = $sight['id'];
			$sightAr["name"] = $sight['name'];
			$sightAr["description"] = $sight['description'];
			$sightAr["address"] = $sight['address'];
			$sightAr["latitude"] = $sight['latitude'];
			$sightAr["longitude"] = $sight['longitude'];
			$sightAr["location_source_id"] = 0;
			$sightAr["source_id"] = $sight['id'];
			$sightAr['uri'] = $sight['path'];
			$sightAr['status'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/sight/set/".$sight['id'].'<br>';


			$res = $client->request('POST',"https://sites.tonia.ru/api/sight/set/".$sight['id'],[
				'form_params' => $sightAr
			]);

			$res = json_decode($res->getBody(),true);

			/*echo 'res=';
			echo '<pre>';
			print_r($res);
			echo '</pre>';*/

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					if(!sync_bounds($connect,[
						'type' => 'sights',
						'id' => $sight['id']
					])) {
						//return FALSE;
						$connect->query("UPDATE `sights` SET `synchronized` = '1' WHERE `id` = ?i",$sight['id']);
					}
					else {
						$connect->query("UPDATE `sights` SET `synchronized` = '1' WHERE `id` = ?i",$sight['id']);
					}
				}
			}
		}
		
		
		$months = $connect->getAll("SELECT * FROM `months` WHERE `synchronized` = 0 and `path`<>''");

		foreach ($months as $month) {
			$monthAr = [];
			$monthAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$monthAr["id"] = $month['id'];
			$monthAr['status'] = 1;
			$monthAr["id_location"] = $month['id_location'];
			$monthAr["id_month"] = $month['id_month'];
			$monthAr["path"] = $month['path'];
			$monthAr["title"] = $month['title'];
			$monthAr["description"] = $month['description'];
			$monthAr["h1"] = $month['h1'];
			$monthAr['text'] = $month['text'];
			$monthAr['additional_text'] = $month['additional_text'];

			//echo "Отправка запроса на https://sites.tonia.ru/api/month/set/".$month['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/month/set/".$month['id'],[
				'form_params' => $monthAr
			]);

			$res = json_decode($res->getBody(),true);

			/*echo '<pre>';
			echo 'res=';
			print_r($res);
			echo '</pre>';*/

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `months` SET `synchronized` = '1' WHERE `id` = ?i",$month['id']);
				} else {
					$connect->query("UPDATE `months` SET `synchronized` = '1' WHERE `id` = ?i",$month['id']);
				}
			}
		}		


		$procedures = $connect->getAll("SELECT * FROM `procedure` WHERE `synchronized` = 0");

		foreach ($procedures as $procedure) {
			$procedureAr = [];
			$procedureAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$procedureAr["id"] = $procedure['id'];
			$procedureAr["name"] = $procedure['name'];
			$procedureAr["description"] = $procedure['description'];
			$procedureAr['uri'] = '/процедуры/'.change_text_url($procedure['name']);
			$procedureAr['status'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/object/procedure/set/".$procedure['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/object/procedure/set/".$procedure['id'],[
				'form_params' => $procedureAr
			]);

			$res = json_decode($res->getBody(),true);

			/*echo 'res=';
			echo '<pre>';
			print_r($res);
			echo '</pre>';*/

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					if(!sync_bounds($connect,[
						'type' => 'treatment_procedure',
						'id' => $procedure['id']
					])) {
						//return FALSE;
						$connect->query("UPDATE `procedure` SET `synchronized` = '1' WHERE `id` = ?i",$procedure['id']);
					}
					else {
						$connect->query("UPDATE `procedure` SET `synchronized` = '1' WHERE `id` = ?i",$procedure['id']);
					}
				}
			}
		}		

		$promotions = $connect->getAll("SELECT `id`, `title`, `text`, `id_obj`, `id_room`, `active`, `date`, `date_end` FROM `promotions` WHERE `synchronized` = 0");

		foreach ($promotions as $promotion) {
			$promotionAr = [];
			$promotionAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$promotionAr["id"] = $promotion['id'];
			$promotionAr["resort_id"] = $promotion['id_obj'];
			$promotionAr["room_id"] = $promotion['id_room'];
			$promotionAr["title"] = $promotion['title'];
			$promotionAr["body"] = $promotion['text'];
			$promotionAr['status'] = ($promotion['active'] > 0)?1:0;
			$promotionAr['start_timestamp'] = strtotime($promotion['date']);
			$promotionAr['end_timestamp'] = strtotime($promotion['date_end']);
			$promotionAr['uid'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/resort/promo/set/".$promotion['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/promo/set/".$promotion['id'],[
				'form_params' => $promotionAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `promotions` SET `synchronized` = '1' WHERE `id` = ?i",$promotion['id']);
				}
				else {
					/*echo $res['msg'].": ".$promotion['id'].'<br>';
					print_r($res['fail_messages']);*/
					break;
				}
			}
		}


		$housings = $connect->getAll("SELECT `id`, `name`, `id_obj`, `description` FROM `housing` WHERE `synchronized` = 0");

		foreach ($housings as $housing) {
			$housingAr = [];
			$housingAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$housingAr["id"] = $housing['id'];
			$housingAr['name'] = $housing['name'];
			$housingAr['description'] = $housing['description'];
			$housingAr['resort_id'] = $housing['id_obj'];
			$housingAr['status'] = 1;
			$housingAr['uid'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/resort/housing/set/".$housing['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/housing/set/".$housing['id'],[
				'form_params' => $housingAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `housing` SET `synchronized` = '1' WHERE `id` = ?i",$housing['id']);
				}
				else {
					/*echo $res['msg'].": ".$housing['id'].'<br>';
					print_r($res['fail_messages']);*/
					break;
				}
			}
		}

		$rates = $connect->getAll("SELECT `id`, `name`, `object`, `status`, `description`, `food`, `min_days`, `max_days`, `start_date`, `end_date` FROM `rate_plan` WHERE `synchronized` = 0");
		foreach ($rates as $rate) {
			$rateAr = [];
			$rateAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$rateAr["id"] = $rate['id'];
			$rateAr['name'] = $rate['name'];
			$rateAr['description'] = $rate['description'];
			$rateAr['resort_id'] = $rate['object'];
			$rateAr['status'] = $rate['status']>0?1:0;
			$rateAr['uid'] = 1;
			$rateAr['food'] = $rate['food'];
			$rateAr['min_days'] = (int)$rate['min_days'];
			$rateAr['max_days'] = (int)$rate['max_days'];
			$rateAr['start_date'] = $rate['start_date'] ? strtotime($rate['start_date']) : 0;
			$rateAr['end_date'] = $rate['end_date'] ? strtotime($rate['end_date']) : 0;

			//echo "Отправка запроса на https://sites.tonia.ru/api/resort/price/rate/set/".$rate['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/rate/set/".$rate['id'],[
				'form_params' => $rateAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `rate_plan` SET `synchronized` = '1' WHERE `id` = ?i",$rate['id']);
				}
				else {
					/*echo $res['msg'].": ".$rate['id'].'<br>';
					print_r($res['fail_messages']);*/
					break;
				}
			}
		}

		$comforts = $connect->getAll("SELECT `id`, `name`, `icon`, `type` FROM `comfort` WHERE `synchronized` = 0");

		foreach ($comforts as $comfort) {
			$comfortAr = [];
			$comfortAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$comfortAr['id'] = $comfort['id'];
			$comfortAr['status'] = 1;
			$comfortAr['name'] = $comfort['name'];
			$comfortAr['icon'] = $comfort['icon'];
			$comfortAr['type'] = $comfort['type'];
			$comfortAr['uid'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/resort/room/comfort/set/".$comfort['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/room/comfort/set/".$comfort['id'],[
				'form_params' => $comfortAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `comfort` SET `synchronized` = '1' WHERE `id` = ?i",$comfort['id']);
				}
				else {
					//echo $res['msg'].": ".$comfort['id'].'<br>';
					//print_r($res['fail_messages']);
					break;
				}
			}
		}

		$infrastructures = $connect->getAll("SELECT `id`, `name` FROM `infa` WHERE `synchronized` = 0");

		foreach ($infrastructures as $infrastructure) {
			$infrastructureAr = [];
			$infrastructureAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$infrastructureAr['id'] = $infrastructure['id'];
			$infrastructureAr['status'] = 1;
			$infrastructureAr['name'] = $infrastructure['name'];
			$infrastructureAr['uid'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/resort/infrastructure/set/".$infrastructure['id'].'<br>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/infrastructure/set/".$infrastructure['id'],[
				'form_params' => $infrastructureAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `infa` SET `synchronized` = '1' WHERE `id` = ?i",$infrastructure['id']);
				}
				else {
					//echo $res['msg'].": ".$infrastructure['id'].'<br>';
					//print_r($res['fail_messages']);
					break;
				}
			}
		}


		$objects = $connect->getAll("SELECT `object`.`id` AS `id`,`object`.`similar` AS `similar`,`object`.`bnovo` AS `bnovo`, `object`.`checked_sonata` AS `checked_sonata`, `object`.`accr_data` AS `accr_data`, `object`.`name` AS `name`, `object`.`url_name` AS `url_name`, `object`.`url_name_origin` AS `url_name_origin`, `object`.`id_reg` AS `region_id`, `object`.`region_direction_id` AS `region_direction_id`, `object`.`direction` AS `direction`, `object`.`active` AS `active`, `object`.`note` AS `note`, `object`.`type` AS `type`, `object`.`full_name` AS `full_name`, `object`.`city` AS `city`, `object`.`city_genitive` AS `city_genitive`, `object`.`address` AS `address`, `object`.`telephone` AS `telephone`, `object`.`email` AS `email`, `object`.`id_profile` AS `id_profile`, `object`.`id_methods` AS `id_methods`, `object`.`id_procedures` AS `id_procedures`, `object`.`id_infa` AS `id_infa`, `object`.`check_places` AS `check_places`, `object`.`default_price_type` AS `default_price_type`, `object`.`description` AS `description`, `object`.`state_program` AS `state_program`, `object`.`children_rest` AS `children_rest`, (`object`.`image` IS NOT NULL) AS `has_thumbnail`, `type_object`.`name` AS `type_name`, `object`.`uri_schema` AS `uri_schema`, `object`.`longitude`, `object`.`latitude`, `object`.`featured` AS `featured`, `object`.`selected` AS `selected`, `object`.`recommended` AS `recommended`, `object`.`popular_in_kurort` AS `popular_in_kurort`, `object`.`bookings_count` AS `bookings_count` FROM `object` LEFT JOIN `type_object` ON `object`.`type` = `type_object`.`id` WHERE `object`.`synchronized` = 0 AND `object`.`type` IS NOT NULL AND `object`.`id_reg` > 0 LIMIT 50");

		foreach ($objects as $object) {
			$objectAr = [];
			$objectAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$objectAr["id"] = $object['id'];
			$objectAr["name"] = $object['name'];
			$objectAr['full_name'] = $object['full_name'];
			$objectAr['city'] = $object['city'];
			$objectAr['city_genitive'] = $object['city_genitive'];
			$objectAr['type'] = $object['type'];
			$objectAr['status'] = (int)(!$object['active']);
			$objectAr['region_id'] = $object['region_id'];
			$objectAr['prices_api'] = $object['check_places'];
			$objectAr['default_price_type'] = $object['default_price_type'];
			$objectAr['description'] = (string)$object['description'];
			$objectAr['has_thumbnail'] = (int)$object['has_thumbnail'];
			$objectAr['phone'] = '';
			$objectAr['email'] = '';
			$objectAr['longitude'] = $object['longitude'];
			$objectAr['bnovo'] = $object['bnovo'];
			$objectAr['checked_sonata'] = $object['checked_sonata'];
			$objectAr['accr_data'] = $object['accr_data'];
			$objectAr['latitude'] = $object['latitude'];
			$objectAr['featured'] = $object['featured'];
			$objectAr['similar'] = $object['similar'];
			$objectAr['selected'] = $object['selected'];
			$objectAr['recommended'] = $object['recommended'];
			$objectAr['popular_in_kurort'] = $object['popular_in_kurort'];
			$objectAr['bookings_count'] = $object['bookings_count'];
			$objectAr['state_program'] = $object['state_program'];
			$objectAr['children_rest'] = $object['children_rest'];

			$phonesAr = json_decode($object['telephone'],true);
			$emailsAr = json_decode($object['email'],true);

			if(is_array($phonesAr) && count($phonesAr) > 0) {
				$objectAr['phone'] = $phonesAr[0]['value'];
			}

			if(is_array($emailsAr) && count($emailsAr) > 0) {
				$objectAr['email'] = $emailsAr[0]['value'];
			}

			if(mb_strlen($objectAr['description']) > 2) {
				if (mb_substr($objectAr['description'], 0, 1) === "'")
					$objectAr['description'] = mb_substr($objectAr['description'],1);

				if (mb_substr($objectAr['description'], mb_strlen($objectAr['description'])-1, 1) === "'")
					$objectAr['description'] = mb_substr($objectAr['description'],0,mb_strlen($objectAr['description'])-1);

				$stripTags = strip_tags($objectAr['description']);
				if($stripTags === '' || $stripTags === '&nbsp;') {
					$objectAr['description'] = '';
				}

			}

			if(is_null($object['type_name'])) {
				$object['type_name'] = 'Санаторий';
			}

			if(!is_null($object['direction'])) {
				$objectAr['direction_id'] = $object['direction'];
			}

			if(!is_null($object['region_direction_id'])) {
				$objectAr['regional_direction_id'] = $object['region_direction_id'];
			}

			$objectAr['note'] = $object['note'];
			$objectAr['address'] = $object['address'];
			if($object['uri_schema'] == 2) {
				$objectAr['uri'] = change_text_url($object['type_name']) . '-' . $object['url_name'];
				$objectAr['uri_type'] = 1;

				if(!is_null($object['direction'])) {
                    $directionUrl = $connect->getOne("SELECT `name` FROM `direction_object` WHERE `id_country` = 1 AND `id` = ?i", $object['direction']);
                    //$objectArFullUri = '/направления/'.change_text_url($directionUrl);
                    $objectArFullUri = '/'.change_text_url($directionUrl);
                    if($objectAr['region_id'] && mb_strlen($object['url_name_origin']) > 0) {
                        $regionUrl = $connect->getOne("SELECT `name` FROM `region` WHERE `region`.`id_country` = 1 AND `region`.`id` = ?i", $objectAr['region_id']);
                        $objectArFullUri .= '/' . change_text_url($regionUrl);
                        if(!is_null($object['region_direction_id']) && $object['region_direction_id']) {
                            $regionalDirectionUrl = $connect->getOne("SELECT `name` FROM `direction_object` WHERE (`direction_object`.`id_country` = 0 OR `direction_object`.`id_country` IS NULL)  AND `direction_object`.`id_reg` > 0 AND `direction_object`.`id` = ?i", $object['region_direction_id']);
                            $objectArFullUri .= '/'. change_text_url($regionalDirectionUrl);
                        }

                        $objectArFullUri .= '/'.$objectAr['uri'];

						//echo "UPDATE `object` SET `path`='$objectArFullUri' WHERE `id`=$object[id]<br><br>";

						$connect->query("UPDATE `object` SET `path`='$objectArFullUri' WHERE `id`=$object[id]");

                        $content = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `status` <> 2 AND `path` = ?s AND `type` != 'redirect' AND `site_id` = '38'", '/объект/'.$object['url_name_origin']);

						if($content) {

							$connect->query("UPDATE `sites_contents` SET `synchronized` = 0, `path` = ?s WHERE id = ?i AND `site_id`", $objectArFullUri, $content['id']);
                        	$redirectMain = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `status` <> 2 AND `path` = ?s AND `redirect_path` = ?s AND `type` = 'redirect' AND `site_id` = '38'", '/объект/'.$object['url_name_origin'], $objectArFullUri);
                        	if(!$redirectMain) {
								$timestamp = gmdate("U");
                        		$connect->query("INSERT INTO `sites_contents` (`type`, `status`, `created`, `published`, `changed`, `site_id`, `title`, `path`, `redirect_path`) VALUES ('redirect', 1, '".$timestamp."', '".$timestamp."', '".$timestamp."', '38', 'Редирект', ?s, ?s)", '/объект/'.$object['url_name_origin'], $objectArFullUri);
							}

							$redirectDescription = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `status` <> 2 AND `path` = ?s AND `redirect_path` = ?s AND `type` = 'redirect' AND `site_id` = '38'", '/объект/'.$object['url_name_origin'].'/описание', $objectArFullUri);
							if(!$redirectDescription) {
								$timestamp = gmdate("U");
								$connect->query("INSERT INTO `sites_contents` (`type`, `status`, `created`, `published`, `changed`, `site_id`, `title`, `path`, `redirect_path`) VALUES ('redirect', 1, '".$timestamp."', '".$timestamp."', '".$timestamp."', '38', 'Редирект', ?s, ?s)", '/объект/'.$object['url_name_origin'].'/описание', $objectArFullUri);
							}

							$redirectPromo = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `status` <> 2 AND `path` = ?s AND `redirect_path` = ?s AND `type` = 'redirect' AND `site_id` = '38'", '/объект/'.$object['url_name_origin'].'/акции', $objectArFullUri);
							if(!$redirectPromo) {
								$timestamp = gmdate("U");
								$connect->query("INSERT INTO `sites_contents` (`type`, `status`, `created`, `published`, `changed`, `site_id`, `title`, `path`, `redirect_path`) VALUES ('redirect', 1, '".$timestamp."', '".$timestamp."', '".$timestamp."', '38', 'Редирект', ?s, ?s)", '/объект/'.$object['url_name_origin'].'/акции', $objectArFullUri);
							}

							$redirectReviews = $connect->getRow("SELECT `id` FROM `sites_contents` WHERE `status` <> 2 AND `path` = ?s AND `redirect_path` = ?s AND `type` = 'redirect' AND `site_id` = '38'", '/объект/'.$object['url_name_origin'].'/отзывы', $objectArFullUri.'/отзывы');
							if(!$redirectReviews) {
								$timestamp = gmdate("U");
								$connect->query("INSERT INTO `sites_contents` (`type`, `status`, `created`, `published`, `changed`, `site_id`, `title`, `path`, `redirect_path`) VALUES ('redirect', 1, '".$timestamp."', '".$timestamp."', '".$timestamp."', '38', 'Редирект', ?s, ?s)", '/объект/'.$object['url_name_origin'].'/отзывы', $objectArFullUri.'/отзывы');
							}
						}
                    }
                }
			} else {
            	$objectAr['uri'] = '/объект/' . $object['url_name'];
            	$objectAr['uri_type'] = 0;
          	}

          	echo "Отправка запроса на https://sites.tonia.ru/api/object/set/".$object['id'].'<br>';
          	echo '<pre>';
		  	print_r($objectAr);   
          	echo '</pre>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/object/set/".$object['id'],[
				'form_params' => $objectAr
			]);


			$res = json_decode($res->getBody(),true);
          	echo '<pre>Результат отправки на https://sites.tonia.ru/api/object/set/'.$object['id'].'<br>';
		  	print_r($res);   
          	echo '</pre>';
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					if (trim($res['new_uri'])!='') {
						$connect->query("UPDATE `object` SET `path` = '?s' WHERE `id` = ?i", $res['new_uri'], $object['id']);
						//echo $connect->last_query().'<br>';
					}


					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'treatment_profile'", $object['id']);
					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'treatment_method'", $object['id']);
					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'treatment_procedure'", $object['id']);
					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'infrastructure'", $object['id']);

					$objectProfiles = explode("_",trim($object['id_profile']));
					$objectMethods = explode("_",trim($object['id_methods']));
					$objectProcedures = explode("_",trim($object['id_procedures']));
					$objectInfrastructures = explode("_",trim($object['id_infa']));

					foreach ($objectProfiles as $objectProfile) {
						$objectProfile = (int)$objectProfile;
						if($objectProfile > 0) {
							$timestamp = gmdate("U");
							$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`, `status`, `uid`, `sort`, `name`, `entity1_type`, `entity1_id`, `entity2_type`, `entity2_id`, `title`, `description`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?s, ?i, ?s, ?s)", $timestamp, $timestamp, 1, 1, 0, 'treatment_profile', 'resort', $object['id'], 'treatment_profile', $objectProfile, '', '');
						}
					}

					foreach ($objectMethods as $objectMethod) {
						$objectMethod = (int)$objectMethod;
						if($objectMethod > 0) {
							$timestamp = gmdate("U");
							$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`, `status`, `uid`, `sort`, `name`, `entity1_type`, `entity1_id`, `entity2_type`, `entity2_id`, `title`, `description`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?s, ?i, ?s, ?s)", $timestamp, $timestamp, 1, 1, 0, 'treatment_method', 'resort', $object['id'], 'treatment_method', $objectMethod, '', '');
						}
					}

					foreach ($objectProcedures as $objectProcedure) {
						$objectProcedure = (int)$objectProcedure;
						if($objectProcedure > 0) {
							$timestamp = gmdate("U");
							$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`, `status`, `uid`, `sort`, `name`, `entity1_type`, `entity1_id`, `entity2_type`, `entity2_id`, `title`, `description`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?s, ?i, ?s, ?s)", $timestamp, $timestamp, 1, 1, 0, 'treatment_procedure', 'resort', $object['id'], 'treatment_procedure', $objectProcedure, '', '');
						}
					}					

					foreach ($objectInfrastructures as $objectInfrastructure) {
						$objectInfrastructure = (int)$objectInfrastructure;
						if($objectInfrastructure > 0) {
							$timestamp = gmdate("U");
							$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`, `status`, `uid`, `sort`, `name`, `entity1_type`, `entity1_id`, `entity2_type`, `entity2_id`, `title`, `description`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?s, ?i, ?s, ?s)", $timestamp, $timestamp, 1, 1, 0, 'infrastructure', 'resort', $object['id'], 'infrastructure', $objectInfrastructure, '', '');
						}
					}

					if(!sync_bounds($connect,[
						'type' => 'resort',
						'id' => $object['id']
					])) {
						return FALSE;
					}
					else {
						if(is_null($object['url_name']))
							$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` IS NULL",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address']);
						else
							$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` = ?s",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address'],$object['url_name']);
					}
					/*if(is_null($object['url_name']))
						$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` IS NULL",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address']);
					else
						$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` = ?s",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address'],$object['url_name']);*/
				}
			}
		}


		$rooms = $connect->getAll("SELECT `id`, `name`, `active`, `id_obj`, `housing`, `square`, `food`, `note`, `description`, `main_place`, `add_place`, `wo_bed_place`, `priority`, `id_comfort`, `id_best_comfort`, `price_places`, `accessible_places` FROM `room` WHERE `synchronized` = 0");

		foreach ($rooms as $room) {
			$roomAr = [];
			$roomAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$roomAr['id'] = $room['id'];
			$roomAr['status'] = (int)(!$room['active']);
			$roomAr['name'] = $room['name'];
			$roomAr['resort_id'] = $room['id_obj'];
			$roomAr['housing_id'] = $room['housing'];
			$roomAr['square'] = (float)$room['square'];
			$roomAr['food'] = $room['food'];
			$roomAr['note'] = $room['note'];
			$roomAr['description'] = $room['description'];
			$roomAr['main_places_count'] = $room['main_place'];
			$roomAr['add_places_count'] = $room['add_place'];
			$roomAr['wo_bed_places_count'] = $room['wo_bed_place'];
			$roomAr['sort'] = $room['priority'];
			$roomAr['travelline_prices_json'] = $room['price_places'];
			$roomAr['accessible_places'] = $room['accessible_places'];
			$roomAr['uid'] = 1;

			//echo "Отправка запроса на https://sites.tonia.ru/api/resort/room/set/".$room['id'].'<br>';
			//echo '<pre>';
			//echo '</pre>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/room/set/".$room['id'],[
				'form_params' => $roomAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'room' AND `entity1_id` = ?i AND `name` = 'comfort'", $room['id']);
					$roomComforts = explode("_",trim($room['id_comfort'].$room['id_best_comfort']));

					foreach ($roomComforts as $roomComfort) {
						$roomComfort = (int)$roomComfort;
						if($roomComfort > 0) {
							$timestamp = gmdate("U");
							$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`, `status`, `uid`, `sort`, `name`, `entity1_type`, `entity1_id`, `entity2_type`, `entity2_id`, `title`, `description`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?s, ?i, ?s, ?s)", $timestamp, $timestamp, 1, 1, 0, 'comfort', 'room', $room['id'], 'comfort', $roomComfort, '', '');
						}
					}

					//echo 'run sync_bounds for room with id='.$room['id'].'<br>';

					if(!sync_bounds($connect,[
						'type' => 'room',
						'id' => $room['id']
					])) {
						return FALSE;
					}
					else {
						$connect->query("UPDATE `room` SET `synchronized` = '1' WHERE `id` = ?i",$room['id']);
					}
					//$connect->query("UPDATE `room` SET `synchronized` = '1' WHERE `id` = ?i",$room['id']);
				}
				else {
					/*echo $res['msg'].": ".$room['id'].'<br>';
					print_r($res['fail_messages']);*/
					break;
				}
			}
		}

		$places = $connect->getAll("SELECT * FROM `place` WHERE `synchronized` = 0");

		foreach ($places as $place) {
			$placeAr = [];
			$placeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$placeAr['id'] = $place['id'];
			$placeAr['name'] = $place['name'];
			$placeAr['type'] = $place['type'];
			$placeAr['status'] = $place['status'];
			$placeAr['resort_id'] = $place['id_obj'];
			$placeAr['room_id'] = $place['id_room'];
			$placeAr['travelline_occupancy_data'] = get_place_name($place, true);
			$placeAr['uid'] = 1;

			/*echo "Отправка запроса на https://sites.tonia.ru/api/resort/price/place/set/".$place['id'].'<br>';
			echo '<pre>';
			print_r($placeAr);
			echo '</pre>';*/

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/place/set/".$place['id'],[
				'form_params' => $placeAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `place` SET `synchronized` = '1' WHERE `id` = ?i",$place['id']);
				}
				else {
					/*echo $res['msg'].": ".$place['id'].'<br>';
					print_r($res['fail_messages']);*/
					break;
				}
			}
		}	
		
		$child_places = $connect->getAll("SELECT * FROM `child_occupancy` WHERE `synchronized` = 0");

		foreach ($child_places as $child_place) {
			$placeAr = [];
			$placeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$placeAr['id'] = $child_place['id'];
			$placeAr['status'] = $child_place['status'];
			$placeAr['resort_id'] = $child_place['id_obj'];
			$placeAr['age_from'] = $child_place['age_from'];
			$placeAr['age_to'] = $child_place['age_to'];
			$placeAr['uid'] = 1;

			/*echo "Отправка запроса на https://sites.tonia.ru/api/resort/price/placechild/set/".$child_place['id'].'<br>';
			echo '<pre>';
			print_r($placeAr);
			echo '</pre>';*/

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/price/placechild/set/".$child_place['id'],[
				'form_params' => $placeAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);
			/*echo '<pre>res=';
			print_r($res);
			echo '</pre>';*/
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `child_occupancy` SET `synchronized` = '1' WHERE `id` = ?i",$child_place['id']);
				}
				else {
					//print_r($res);
					break;
				}
			}
		}		

		/*function SyncDatesPack($client, $connect, $datesAr) {
			if (count($datesAr['data'])>0) {
				echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/daterange/set/".$datesAr['id'].'<br>';

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


		function SyncRangesPack($client, $connect, $rangeAr) {
			if (count($rangeAr['data'])>0) {
				echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/range/set/".$rangeAr['id'].'<br>';

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
			

		function SyncPricesPack($client, $connect, $priceAr) {
			if (count($priceAr['data'])>0) {
				echo "Отправка пачки цен на https://sites.tonia.ru/api/resort/price/set/".$priceAr['id'].'<br>';

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

		$prices = $connect->getAll("SELECT `id`, `id_room`, `price`, `id_range`, `active` FROM `price` WHERE `synchronized` = 0 LIMIT 5000");

		$i=0;
		$priceAr = [];
		$priceAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
		$priceAr['id'] = 1;	
		$priceAr['uid'] = 1;	
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
		
		*/
	
		//echo '<br>end of sync';

		return true;
	}
	catch (Exception $e) {
		//echo 'Exception='.$e->getMessage();
		return false;
	}

}

?>
