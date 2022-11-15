<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_api_test($connect){

	if(!sync_files($connect)) {
		return FALSE;
	}

	try {

		$client = new \GuzzleHttp\Client(['verify' => false]);

		$objects = $connect->getAll("SELECT `object`.`id` AS `id`, `object`.`name` AS `name`, `object`.`url_name` AS `url_name`, `object`.`url_name_origin` AS `url_name_origin`, `object`.`id_reg` AS `region_id`, `object`.`region_direction_id` AS `region_direction_id`, `object`.`direction` AS `direction`, `object`.`active` AS `active`, `object`.`note` AS `note`, `object`.`type` AS `type`, `object`.`full_name` AS `full_name`, `object`.`city` AS `city`, `object`.`city_genitive` AS `city_genitive`, `object`.`address` AS `address`, `object`.`telephone` AS `telephone`, `object`.`email` AS `email`, `object`.`id_profile` AS `id_profile`, `object`.`id_methods` AS `id_methods`, `object`.`id_infa` AS `id_infa`, `object`.`check_places` AS `check_places`, `object`.`default_price_type` AS `default_price_type`, `object`.`description` AS `description`, `object`.`state_program` AS `state_program`, `object`.`children_rest` AS `children_rest`, (`object`.`image` IS NOT NULL) AS `has_thumbnail`, `type_object`.`name` AS `type_name`, `object`.`uri_schema` AS `uri_schema`, `object`.`longitude`, `object`.`latitude`, `object`.`featured` AS `featured`, `object`.`selected` AS `selected` FROM `object` LEFT JOIN `type_object` ON `object`.`type` = `type_object`.`id` WHERE `object`.`id`=711 AND `object`.`type` IS NOT NULL AND `object`.`id_reg` > 0");

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
			$objectAr['latitude'] = $object['latitude'];
			$objectAr['featured'] = $object['featured'];
			$objectAr['selected'] = $object['selected'];
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
			}
			else {
            $objectAr['uri'] = '/объект/' . $object['url_name'];
            $objectAr['uri_type'] = 0;
          }

          echo '<pre>';
          print_r($objectAr);
          echo '</pre>';

			$res = $client->request('POST',"https://sites.tonia.ru/api/object/set/".$object['id'],[
				'form_params' => $objectAr
			]);

			$res = json_decode($res->getBody(),true);

          echo '<pre>';
          print_r($res);
          echo '</pre>';

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
					$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'resort' AND `entity1_id` = ?i AND `name` = 'treatment_profile'", $object['id']);
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
					}
					
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

include_once("../../config.php");
include_once("../mysql.php");
include_once("../lib/Mysql.Class.php");
include_once("../functions.php");


$conf = new JConfig;
$bonus_rec = $conf->bonus_rec;
$bonus_ref = $conf->bonus_ref;
$min_transfer_bonus = $conf->min_transfer_bonus;
$sync_host = $conf->sync_host;
$sync_api = $conf->sync_base;
$directory = dirname(__FILE__);
define("_FOLDERSITE_", $directory);
$configInstance = \App\lib\CRM\Config\Client::getInstance();
$configInstance->clientCabinet = [
"link" => $conf->turist_cabinet
];


$connect = connect_to_MySQL();

sync_objects_api_test($connect);

?>
