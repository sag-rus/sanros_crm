<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_api($connect){
	global $directory;

	try {

		$client = new \GuzzleHttp\Client();
		$directions = $connect->getAll("SELECT `id`, `name`, `name_rod` FROM `direction_object` WHERE `id_country` = 1 AND `synchronized` = 0");
		$directionsCond = "";
		foreach ($directions as $direction) {

			if($directionsCond)
				$directionsCond .= ' OR ';

			$directionsCond .= '`region`.`id_direction` = '.$direction['id'];

			$directionAr = [
				'name' => $direction['name'],
				'name_genitive' => $direction['name_rod'],
				'parent_id' => 0,
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

		$regions = $connect->getAll("SELECT `region`.`id` AS `id`, `region`.`name` AS `name`, `region`.`name_rod` AS `name_rod`, `region`.`id_direction` AS `id_direction`, `direction_object`.`name` AS `direction_name` FROM `region` INNER JOIN `direction_object` ON `region`.`id_direction` = `direction_object`.`id` WHERE `region`.`id_country` = 1 AND `region`.`synchronized` = 0");

		foreach ($regions as $region) {
			$regionAr = [
				'name' => $region['name'],
				'name_genitive' => $region['name_rod'],
				'parent_id' => $region['id_direction'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
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

		$regional_directions = $connect->getAll("SELECT `direction_object`.`id` AS `id`, `direction_object`.`name` AS `name`, `direction_object`.`name_rod` AS `name_rod`, `direction_object`.`id_reg` AS `id_reg`, `region`.`name` AS `name_reg`, `region`.`id_direction` AS `region_direction_id`, `direction_object2`.`name` AS `dir_name` FROM `direction_object` INNER JOIN `region` ON `region`.`id` = `direction_object`.`id_reg` INNER JOIN `direction_object` AS `direction_object2` ON `direction_object2`.`id` = `region`.`id_direction` WHERE (`direction_object`.`id_country` = 0 OR `direction_object`.`id_country` IS NULL)  AND `direction_object`.`id_reg` > 0 AND `direction_object`.`synchronized` = 0");


		foreach ($regional_directions as $regional_direction) {
			$regionalDirectionAr = [
				'name' => $regional_direction['name'],
				'name_genitive' => $regional_direction['name_rod'],
				'parent_id' => $regional_direction['id_reg'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
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
			$profileAr['status'] = 1;


			$res = $client->request('POST',"https://sites.tonia.ru/api/object/profile/set/".$profile['id'],[
				'form_params' => $profileAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("UPDATE `profile` SET `synchronized` = '1' WHERE `id` = ?i",$profile['id']);
					//$connect->query("UPDATE `object` SET `synchronized` = '0' WHERE `type` = ?i",$type['id']);
				}
			}
		}


		$objects = $connect->getAll("SELECT `object`.`id` AS `id`, `object`.`name` AS `name`, `object`.`url_name` AS `url_name`, `object`.`id_reg` AS `region_id`, `object`.`region_direction_id` AS `region_direction_id`, `object`.`direction` AS `direction`, `object`.`active` AS `active`, `object`.`note` AS `note`, `object`.`type` AS `type`, `object`.`full_name` AS `full_name`, `object`.`address` AS `address`, `object`.`telephone` AS `telephone`, `type_object`.`name` AS `type_name` FROM `object` LEFT JOIN `type_object` ON `object`.`type` = `type_object`.`id` WHERE `object`.`synchronized` = 0 AND `object`.`type` IS NOT NULL AND `object`.`id_reg` > 0");

		foreach ($objects as $object) {
			$objectAr = [];
			$objectAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$objectAr["id"] = $object['id'];
			$objectAr["name"] = $object['name'];
			$objectAr['full_name'] = $object['full_name'];
			$objectAr['type'] = $object['type'];
			$objectAr['status'] = (int)(!$object['active']);
			$objectAr['region_id'] = $object['region_id'];

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
					if(is_null($object['url_name']))
						$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` IS NULL",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address']);
					else
						$connect->query("UPDATE `object` SET `synchronized` = '1' WHERE `id` = ?i AND `name` = ?s AND `full_name` = ?s AND `type` = ?i AND `active` = ?i AND `id_reg` = ?i AND `note` = ?s AND `address` = ?s AND `url_name` = ?s",$object['id'],$object['name'],$object['full_name'],$object['type'],$object['active'],$object['region_id'],$object['note'],$object['address'],$object['url_name']);

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
