<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_api($connect){

	if(!sync_files($connect)) {
		return FALSE;
	}

	try {

		$client = new \GuzzleHttp\Client();
		$directions = $connect->getAll("SELECT `id`, `name`, `name_rod`, `description`, `meta_desc` FROM `direction_object` WHERE `id_country` = 1 AND `synchronized` = 0");
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
				'uri' => '/направления/'.change_text_url($direction['name']),
				'status' => 1
			];

			$res = $client->request('POST',"https://sites.tonia.ru/api/location/direction/set/".$direction['id'],[
				'form_params' => $directionAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
						$connect->query("UPDATE `direction_object` SET `synchronized` = '1' WHERE `id` = ?i",$direction['id']);
						$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `direction` = ?i", $direction['id']);
				}
			}


		}

		$regions = $connect->getAll("SELECT `region`.`id` AS `id`, `region`.`name` AS `name`, `region`.`name_rod` AS `name_rod`, `region`.`id_direction` AS `id_direction`, `direction_object`.`name` AS `direction_name`, `region`.`description` AS `description`, `region`.`meta_desc` AS `meta_desc` FROM `region` INNER JOIN `direction_object` ON `region`.`id_direction` = `direction_object`.`id` WHERE `region`.`id_country` = 1 AND `region`.`synchronized` = 0");

		foreach ($regions as $region) {
			$regionAr = [
				'name' => $region['name'],
				'name_genitive' => $region['name_rod'],
				'parent_id' => $region['id_direction'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'description' => $region['description']?$region['description']:$region['meta_desc'],
				'uri' => '/направления/'.change_text_url($region['direction_name']).'/'.change_text_url($region['name']),
				'status' => 1
			];

			$res = $client->request('POST',"https://sites.tonia.ru/api/location/region/set/".$region['id'],[
				'form_params' => $regionAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `region` SET `synchronized` = '1' WHERE `id` = ?i",$region['id']);
					$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `id_reg` = ?i", $region['id']);
				}
			}


		}

		$regional_directions = $connect->getAll("SELECT `direction_object`.`id` AS `id`, `direction_object`.`name` AS `name`, `direction_object`.`name_rod` AS `name_rod`, `direction_object`.`id_reg` AS `id_reg`, `region`.`name` AS `name_reg`, `region`.`id_direction` AS `region_direction_id`, `direction_object2`.`name` AS `dir_name`, `direction_object`.`description` AS `description` FROM `direction_object` INNER JOIN `region` ON `region`.`id` = `direction_object`.`id_reg` INNER JOIN `direction_object` AS `direction_object2` ON `direction_object2`.`id` = `region`.`id_direction` WHERE (`direction_object`.`id_country` = 0 OR `direction_object`.`id_country` IS NULL)  AND `direction_object`.`id_reg` > 0 AND `direction_object`.`synchronized` = 0");


		foreach ($regional_directions as $regional_direction) {
			$regionalDirectionAr = [
				'name' => $regional_direction['name'],
				'name_genitive' => $regional_direction['name_rod'],
				'parent_id' => $regional_direction['id_reg'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'description' => $regional_direction['description'],
				'uri' => '/направления/'.change_text_url($regional_direction['dir_name']).'/'.change_text_url($regional_direction['name_reg']).'/'.change_text_url($regional_direction['name']),
				'status' => 1
			];

			$res = $client->request('POST',"https://sites.tonia.ru/api/location/regional_direction/set/".$regional_direction['id'],[
				'form_params' => $regionalDirectionAr
			]);


			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `direction_object` SET `synchronized` = '1' WHERE `id` = ?i",$regional_direction['id']);
					$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `region_direction_id` = ?i", $regional_direction['id']);
				}
			}


		}

		$types = $connect->getAll("SELECT `id`, `name` FROM `type_object` WHERE `synchronized` = 0");

		foreach ($types as $type) {
			$typeAr = [];
			$typeAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$typeAr["id"] = $type['id'];
			$typeAr["name"] = $type['name'];
			$typeAr['status'] = 1;


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


			$res = $client->request('POST',"https://sites.tonia.ru/api/object/profile/set/".$profile['id'],[
				'form_params' => $profileAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `profile` SET `synchronized` = '1' WHERE `id` = ?i",$profile['id']);
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


			$res = $client->request('POST',"https://sites.tonia.ru/api/object/method/set/".$method['id'],[
				'form_params' => $methodAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `methods` SET `synchronized` = '1' WHERE `id` = ?i",$method['id']);
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
					echo $res['msg'].": ".$promotion['id'].'<br>';
					print_r($res['fail_messages']);
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
					echo $res['msg'].": ".$housing['id'].'<br>';
					print_r($res['fail_messages']);
					break;
				}
			}
		}

		$rates = $connect->getAll("SELECT `id`, `name`, `object`, `status`, `description`, `food`, `days` FROM `rate_plan` WHERE `synchronized` = 0");

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
			$rateAr['days'] = (int)$rate['days'];

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
					echo $res['msg'].": ".$rate['id'].'<br>';
					print_r($res['fail_messages']);
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
					echo $res['msg'].": ".$comfort['id'].'<br>';
					print_r($res['fail_messages']);
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
					echo $res['msg'].": ".$infrastructure['id'].'<br>';
					print_r($res['fail_messages']);
					break;
				}
			}
		}


		$objects = $connect->getAll("SELECT `object`.`id` AS `id`, `object`.`name` AS `name`, `object`.`url_name` AS `url_name`, `object`.`id_reg` AS `region_id`, `object`.`region_direction_id` AS `region_direction_id`, `object`.`direction` AS `direction`, `object`.`active` AS `active`, `object`.`note` AS `note`, `object`.`type` AS `type`, `object`.`full_name` AS `full_name`, `object`.`address` AS `address`, `object`.`telephone` AS `telephone`, `object`.`id_profile` AS `id_profile`, `object`.`id_methods` AS `id_methods`, `object`.`id_infa` AS `id_infa`, `object`.`check_places` AS `check_places`, `object`.`default_price_type` AS `default_price_type`, `type_object`.`name` AS `type_name` FROM `object` LEFT JOIN `type_object` ON `object`.`type` = `type_object`.`id` WHERE `object`.`synchronized` = 0 AND `object`.`type` IS NOT NULL AND `object`.`id_reg` > 0");

		foreach ($objects as $object) {
			$objectAr = [];
			$objectAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$objectAr["id"] = $object['id'];
			$objectAr["name"] = $object['name'];
			$objectAr['full_name'] = $object['full_name'];
			$objectAr['type'] = $object['type'];
			$objectAr['status'] = (int)(!$object['active']);
			$objectAr['region_id'] = $object['region_id'];
			$objectAr['prices_api'] = $object['check_places'];
			$objectAr['default_price_type'] = $object['default_price_type'];

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
			$objectAr['uri'] = change_text_url($object['type_name']).'-'.$object['url_name'];


			$res = $client->request('POST',"https://sites.tonia.ru/api/object/set/".$object['id'],[
				'form_params' => $objectAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					/*$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'treatment_profile'", $object['id']);
					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'treatment_method'", $object['id']);
					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'infrastructure'", $object['id']);

					$objectProfiles = explode("_",trim($object['id_profile']));
					$objectMethods = explode("_",trim($object['id_methods']));
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
					}*/
					if(is_null($object['url_name']))
						$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` IS NULL",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address']);
					else
						$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` = ?s",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address'],$object['url_name']);
				}
			}
		}


		$rooms = $connect->getAll("SELECT `id`, `name`, `active`, `id_obj`, `housing`, `square`, `food`, `note`, `description`, `main_place`, `add_place`, `priority`, `id_comfort`, `id_best_comfort`, `price_places` FROM `room` WHERE `synchronized` = 0");

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
			$roomAr['sort'] = $room['priority'];
			$roomAr['travelline_prices_json'] = $room['price_places'];
			$roomAr['uid'] = 1;

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/room/set/".$room['id'],[
				'form_params' => $roomAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					//$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'room' AND `entity1_id` = ?i AND `name` = 'comfort'", $room['id']);
					$roomComforts = explode("_",trim($room['id_comfort'].$room['id_best_comfort']));

					/*foreach ($roomComforts as $roomComfort) {
						$roomComfort = (int)$roomComfort;
						if($roomComfort > 0) {
							$timestamp = gmdate("U");
							$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`, `status`, `uid`, `sort`, `name`, `entity1_type`, `entity1_id`, `entity2_type`, `entity2_id`, `title`, `description`) VALUES (?i, ?i, ?i, ?i, ?i, ?s, ?s, ?i, ?s, ?i, ?s, ?s)", $timestamp, $timestamp, 1, 1, 0, 'comfort', 'room', $room['id'], 'comfort', $roomComfort, '', '');
						}
					}

					if(!sync_bounds($connect,[
						'type' => 'room',
						'id' => $room['id']
					])) {
						return FALSE;
					}
					else {
						$connect->query("UPDATE `room` SET `synchronized` = '1' WHERE `id` = ?i",$room['id']);
					}*/
					$connect->query("UPDATE `room` SET `synchronized` = '1' WHERE `id` = ?i",$room['id']);
				}
				else {
					echo $res['msg'].": ".$room['id'].'<br>';
					print_r($res['fail_messages']);
					break;
				}
			}
		}

		return true;
	}
	catch (Exception $e) {
		echo $e->getMessage();
		return false;
	}

}

?>
