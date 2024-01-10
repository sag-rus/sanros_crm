<?php

	$loader = require( __DIR__ . '/../../vendor/autoload.php');

	$directory = dirname(__FILE__)."/../..";
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	include_once($directory."/config.php");
	$conf = new JConfig;
	define("BANK_PAYMENT_LINK", $conf->BANK_PAYMENT_LINK);
	define("USERNAME_ALFA", $conf->USERNAME_ALFA);
	define("PASSWORD_ALFA", $conf->PASSWORD_ALFA);
	$connect = connect_to_MySQL_directory();

	$configNew = \App\lib\CRM\Config\Client::getInstance();
	$configNew->connect = $connect;	

	$onlinePaymentInfoAlfa = array(
	  "link" => $conf->BANK_PAYMENT_LINK_ALFA,
	  "commission" => $conf->BANK_COM_ALFA,
	  "commission_qr" => $conf->BANK_COM_ALFA_QR,
	  "userName" => $conf->USERNAME_ALFA,
	  "password" => $conf->PASSWORD_ALFA
	);	

	$configNew->onlinePaymentInfoAlfa = $onlinePaymentInfoAlfa;	

	if ($_GET['debug']=='') {
		count_no_price_object($connect);
		count_published_news($connect);
		check_average_rating($connect);
		$connect->query("UPDATE chat_users SET status=0 WHERE last_visit<CURRENT_TIMESTAMP - INTERVAL (10) MINUTE");
	}
	
	$sql = "SELECT id, bid, bid_pay, order_id FROM payment_request WHERE status=0 or status IS NULL ORDER BY `id` DESC LIMIT 60";
	if ($_GET['bid']!='') $sql = "SELECT id, bid, bid_pay, order_id FROM payment_request WHERE (status=0 or status IS NULL) AND `bid`='$_GET[bid]' ORDER BY `id` DESC";
	$data = $connect->getAll($sql);
	foreach($data as $row){
		$payment = new \App\lib\payment\Alfa\BookingPayment([]);
		$request = $payment->depositPayment($row["bid_pay"]);

		if ($_GET['debug']=='1') {
			echo '<pre>bid_pay='.$row["bid_pay"]."\r\n";
			print_r($request);
			echo '</pre><br>';
		}

		if ($_GET['bid']!='') echo 'Проверка завершена, результат: '.$request.'. Можно закрыть эту вкладку';

		/*$id = $row["id"];
		$bid = $row["bid"];
		$orderId = $row["order_id"];
		$object = $connect->getOne("SELECT id_obj FROM reckoning WHERE id=?i", $bid);

		$url = BANK_PAYMENT_LINK."getOrderStatus.do";
		$data["userName"] = get_login_bank($connect, $object);
		$data["password"] = PASSWORD_ALFA;
		$data["orderId"] = $orderId;
		$data = request_to_url($url, $data);
		$connect->query("UPDATE payment_request SET status=?i WHERE order_id=?s", $data["OrderStatus"], $orderId);*/
	}

function count_no_price_object($connect){
	$today = date("Y-m-d");
	$check_day = date("Y-m-d", strToTime("+30 days"));
	$all_count = 0;
	$object_no_price = array();
	$data = $connect->getAll("SELECT object.id FROM object, region WHERE object.active=0 AND region.active=0 AND object.id_reg=region.id AND object.check_places!=1");
	foreach($data as $row){
		$name_object = $row["name"];
		$object = $row["id"];
		$data2 = $connect->getAll("SELECT ranges.id FROM date_price, ranges WHERE date_price.id_obj=?i AND date_price.end>?s AND ranges.id_date=date_price.id AND ranges.active=0 AND ranges.id_obj=?i", $object, $check_day, $object);
		$count = 0;
		foreach($data2 as $row){
			$range = $row["id"];
			$count+= $connect->getOne("SELECT COUNT(*) FROM price WHERE id_range=?i AND price>0 AND active=0", $range);
		}
		if($count == 0){
			$data2 = $connect->getAll("SELECT ranges.id FROM date_price, ranges WHERE date_price.id_obj=?i AND date_price.end>?s AND ranges.id_date=date_price.id AND ranges.active=0 AND ranges.id_obj=?i", $object, $today, $object);
			foreach($data2 as $row){
				$range = $row["id"];
				$count+= $connect->getOne("SELECT COUNT(*) FROM price WHERE id_range=?i AND price>0 AND active=0", $range);
			}
			if($count == 0){
				$all_count++;
				$object_no_price[] = $object;
			}else
				$object_end_price[] = $object;
		}
	}
	$connect->query("UPDATE constant SET value=?s WHERE name='no-price-object-count'", $all_count);
	$connect->query("UPDATE constant SET value=?s WHERE name='no-price-object'", json_encode($object_no_price));
	$connect->query("UPDATE constant SET value=?s WHERE name='end-price-object'", json_encode($object_end_price));
}

function count_published_news($connect){
	$website_news = array();
	$check_day = strToTime("-14 days");
	$data = $connect->getAll("SELECT website FROM news GROUP BY website");
	foreach($data as $row){
		$website = $row["website"];
		$date = strToTime($connect->getOne("SELECT date FROM news WHERE website=?i ORDER BY date DESC LIMIT 1", $website));
		if($check_day > $date)
			$website_news[] = $website;
	}
	$connect->query("UPDATE constant SET value=?s WHERE name='published-news'", json_encode($website_news));
}

function check_average_rating($connect){
	$data = $connect->getAll("SELECT id, clean, comfort, location, staff, ratio, leisure, treatment FROM rating WHERE status=3 AND average=0");
	foreach($data as $row){
		$id = $row["id"];
		$average_object = 0;
		$count_rating = 6;
		$average = $row["clean"] + $row["comfort"] + $row["location"] + $row["staff"] + $row["treatment"] + $row["leisure"] + $row["ratio"];
		if($row["treatment"] != 0)
			$count_rating++;
		$average = round($average / $count_rating * 2, 1);
		$connect->query("UPDATE rating SET average=?s, synchronized = 0 WHERE id=?i", $average, $id);
	}
}

?>
