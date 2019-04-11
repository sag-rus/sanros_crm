<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_ratings_api($connect){

	try {

		$client = new \GuzzleHttp\Client();
		$ratings = $connect->getAll("SELECT `id`, `status`, `clean`, `comfort`, `location`, `staff`, `ratio`, `leisure`, `treatment`, `id_obj`, `positive`, `negative`, `date_send`, `company_rating`, `turist`, `advice` FROM `rating` WHERE `date_send` IS NOT NULL AND `average` > 0 AND `synchronized` = 0 AND `id_obj` > 0");
		foreach ($ratings as $rating) {
			$ratingAr = [
				'id' => $rating['id'],
				'token' => '7db0d2680968f87e33dd3db9a4b5db38d373ba8a9f42ca7dc97d6f14711efaa4',
				'status' => ($rating['status'] > 2?1:0),
				'created' => strtotime($rating['date_send']),
				'resort_id' => $rating['id_obj'],
				'positive' => (string)$rating['positive'],
				'negative' => (string)$rating['negative'],
				'advice' => (string)$rating['advice'],
				'author_name' => (string)$rating['turist'],
				'average' => round(($rating['clean']+$rating['comfort']+$rating['location']+$rating['staff']+$rating['ratio']+$rating['leisure']+$rating['treatment'])/14,1),
				'company_rating' => (string)$rating['company_rating'],
				'uid' => 1
			];

			echo $rating['id'];

			$res = $client->request('POST',"https://sites.tonia.ru/api/resort/rating/set/".$rating['id'],[
				'form_params' => $ratingAr
			]);

			print_r($res->getBody()->getContents());

			$res = json_decode($res->getBody(),true);

			if(array_key_exists('success',$res)) {
				$success = (bool)(int)$res['success'];
				if($success) {
						$connect->query("UPDATE `rating` SET `synchronized` = '1' WHERE `id` = ?i", $rating['id']);
				}
			}
			break;

		}



		return true;
	}
	catch (Exception $e) {
		echo $e->getMessage();
		return false;
	}

}

?>
