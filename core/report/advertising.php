<?php

function select_direct_campain($connect){
	$array = array();
	$data = $connect->getAll("SELECT id_campaign, name, website, object FROM st_direct_campaign");
	foreach($data as $row){
		$campain = array();
		$campain["name"] = $row["name"];
		$array[$row["id_campaign"]] = $campain;
	}
	return json_encode($array);
}

function form_report_advertising($connect){
	$data = array();
	$start = $_POST["start"];
	$end = $_POST["end"];
	if(!$end)
		$end = $start;
	$campains = json_decode($_POST["data"]);
	foreach($campains as $campain => $check){
		$array = array();
		$row = $connect->getRow("SELECT name, website, object FROM st_direct_campaign WHERE id_campaign=?i", $campain);
		$url = $connect->getOne("SELECT url FROM st_website WHERE id=?i", $row["website"]);
		$object = $row["object"];
		$array["name"] = $row["name"];
		$array["reward"] = 0;
		if($check == 1){
			$array["count"] = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE website=?s AND date>=?s AND date<=?s AND id_obj=?i AND source=2", $url, $start, $end, $object);
			$array["count-work"] = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status<4 AND active=0 AND website=?s AND date>=?s AND date<=?s AND id_obj=?i AND source=2", $url, $start, $end, $object);
			$array["count-pay"] = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (status=4 OR status=5) AND website=?s AND date>=?s AND date<=?s AND id_obj=?i AND source=2", $url, $start, $end, $object);
			$data_reward = $connect->getAll("SELECT id FROM reckoning WHERE (status=4 OR status=5) AND website=?s AND date>=?s AND date<=?s AND id_obj=?i AND source=2", $url, $start, $end, $object);
		}else{
			$array["count"] = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ((website=?s AND source=2) OR (website IS NULL)) AND date>=?s AND date<=?s AND id_obj=?i", $url, $start, $end, $object);
			$array["count-work"] = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ((website=?s AND source=2) OR (website IS NULL)) AND status<4 AND active=0 AND date>=?s AND date<=?s AND id_obj=?i", $url, $start, $end, $object);
			$array["count-pay"] = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE ((website=?s AND source=2) OR (website IS NULL)) AND (status=4 OR status=5) AND date>=?s AND date<=?s AND id_obj=?i", $url, $start, $end, $object);
			$data_reward = $connect->getAll("SELECT id FROM reckoning WHERE ((website=?s AND source=2) OR (website IS NULL)) AND (status=4 OR status=5) AND date>=?s AND date<=?s AND id_obj=?i", $url, $start, $end, $object);
		}
		foreach($data_reward as $row){
			$array["reward"]+= get_reward_schet($connect, $row["id"]);
		}
		$array["reward"] = add_null($array["reward"]);
		$array["spend"] = $connect->getOne("SELECT SUM(sum_spend) FROM st_direct_stat WHERE id_campaign=?i AND date>=?s AND date<=?s", $campain, $start, $end);
		if(!$array["spend"])
			$array["spend"] = 0;
		$array["itog"] = $array["reward"] - $array["spend"];
		$data[] = $array;
	}
	return json_encode($data);
}

?>
