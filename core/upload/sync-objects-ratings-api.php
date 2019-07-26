<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_ratings_api($connect){

	try {

		$client = new \GuzzleHttp\Client();
		$ratings = $connect->getAll("SELECT DISTINCT `rating`.`id` AS `id`, `rating`.`status` AS `status`, `rating`.`clean` AS `clean`, `rating`.`comfort` AS `comfort`, `rating`.`location` AS `location`, `rating`.`staff` AS `staff`, `rating`.`ratio` AS `ratio`, `rating`.`leisure` AS `leisure`, `rating`.`treatment` AS `treatment`, `rating`.`id_obj` AS `id_obj`, `rating`.`positive` AS `positive`, `rating`.`negative` AS `negative`, `rating`.`date_send` AS `date_send`, `rating`.`company_rating` AS `company_rating`, `rating`.`turist` AS `turist`, `rating`.`advice` AS `advice`, `klient`.`name` AS `klient_name` FROM `rating` LEFT JOIN `reckoning` ON `rating`.`schet` = `reckoning`.`id` LEFT JOIN `klient` ON `reckoning`.`turist` = `klient`.`id` WHERE `rating`.`date_send` IS NOT NULL AND `rating`.`average` > 0 AND `rating`.`synchronized` = 0 AND `rating`.`id_obj` > 0");
		foreach ($ratings as $rating) {
			$ratingSum = $rating['clean']+$rating['comfort']+$rating['location']+$rating['staff']+$rating['ratio']+$rating['leisure']+$rating['treatment'];

			$ratingDel = 6;
			if($rating['treatment'] > 0)
				$ratingDel++;

			$turistName = (string)$rating['turist'];

			if(mb_strlen($turistName) === 0) {
				$turistName = (string)$rating['klient_name'];
			}

			$ratingAr = [
				'id' => $rating['id'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'status' => ($rating['status'] > 2?1:0),
				'created' => strtotime($rating['date_send']),
				'resort_id' => $rating['id_obj'],
				'positive' => (string)$rating['positive'],
				'negative' => (string)$rating['negative'],
				'advice' => (string)$rating['advice'],
				'author_name' => $turistName,
				'average' => round($ratingSum/$ratingDel,1),
				'company_rating' => (string)$rating['company_rating'],
				'has_company_rating' => (int)(mb_strlen((string)$rating['company_rating']) > 0),
				'uid' => 1
			];

			echo round($ratingSum/$ratingDel,1).PHP_EOL;

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/rating/set/".$rating['id'],[
				'form_params' => $ratingAr
			]);

			$res = json_decode($res->getBody()->getContents(),true);

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
						$connect->query("UPDATE `rating` SET `synchronized` = '1' WHERE `id` = ?i", $rating['id']);
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
