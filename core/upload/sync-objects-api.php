<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_api($connect){
	global $directory;

	try {

		$client = new \GuzzleHttp\Client();
		$objects = $connect->getAll("SELECT `id`, `name`, `url_name`, `id_reg` AS `region_id`, `active`, `note`, `type`, `full_name`, `address`, `telephone` FROM `object` WHERE `synchronized` = 0 AND `type` IS NOT NULL");

		foreach ($objects as $object) {
			$objectAr = [];
			$objectAr["token"] = '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4';
			$objectAr["id"] = $object['id'];
			$objectAr["name"] = $object['name'];
			$objectAr['full_name'] = $object['full_name'];
			$objectAr['type'] = $object['type'];
			$objectAr['status'] = (int)(!$object['active']);
			$objectAr['region_id'] = $object['region_id'];
			$objectAr['note'] = $object['note'];
			$objectAr['address'] = $object['address'];
			$objectAr['uri'] = $object['url_name'];



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

		$directions = $connect->getAll("SELECT `id`, `name`, `name_rod` FROM `direction_object` WHERE `id_country` = 1 AND `synchronized` = 0");

		foreach ($directions as $direction) {
			$directionAr = [
				'name' => $direction['name'],
				'name_genitive' => $direction['name_rod'],
				'parent_id' => 0,
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'uri' => '/направления/'.change_text_url($direction['name'])
			];

			$res = $client->request('POST',"https://sites.tonia.ru/api/location/direction/set/".$direction['id'],[
				'form_params' => $directionAr
			]);

			$res = json_decode($res->getBody(),true);
			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
						$connect->query("UPDATE `direction_object` SET `synchronized` = '1' WHERE `id` = ?i",$direction['id']);
				}
			}


		}

		return true;
	}
	catch (Exception $e) {
		return false;
	}

}

?>
