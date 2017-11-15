<?php

function show_form_search_engine_reservation(){
	ob_start();
?>
<div class="form-horizontal panel panel-info search-form">
	<div class="panel-heading"><i class="fa fa-search-plus"></i> Поиск по блокам мест</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-1 control-label">Объект</label>
			<div class="col-sm-3" id="object_name">
				<input type="text" class="form-control id-object" id="object" onkeyup="find_klient('object', 'object', 'use_object')" name="">
			</div>
			<label class="col-sm-1 control-label">Заезд</label>
			<div class="col-sm-2">
				<input type="text" class="form-control date" value="<?php echo date('d.m.Y'); ?>" onchange="$('.search-result1').html('');" />
			</div>
			<label class="col-sm-1 control-label">Дней</label>
			<div class="col-sm-2">
				<select class="form-control days" onchange="$('.search-result1').html('');">
				<?php for($i = 1; $i<= 30; $i++){ ?>
					<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
				</select>
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button class="btn btn-success btn-sm btn-search" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Идет поиск..."  onclick="search_engine_reservation()"><i class="fa fa-hand-o-right"></i> Поиск</button>
	</div>
</div>
<div class="search-result"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function search_engine_reservation($connect){
	date_default_timezone_set("UTC");
	$start = strToTime($_POST["date"]);
	$days = $_POST["days"];
	$object = $_POST["object"];
	$end = $start + ($days - 1) * 86400;
	ob_start();
?>
	<div class="form-horizontal">
<?php
	$write = 0;
	if($object)
		$objects = $connect->getAll("SELECT room.id_obj FROM room, object_room WHERE object_room.id_category=room.id AND room.id_obj=?i GROUP BY room.id_obj", $object);
	else
		$objects = $connect->getAll("SELECT room.id_obj FROM room, object_room WHERE object_room.id_category=room.id GROUP BY room.id_obj");
	foreach($objects as $object){
		$id_obj = $object["id_obj"];
		$name_object = get_object($connect, $id_obj, "type");
		$write_object = 0;
		$rooms = $connect->getAll("SELECT room.id FROM room, object_room WHERE object_room.id_category=room.id AND room.id_obj=?i GROUP BY room.id", $id_obj);
		foreach($rooms as $room){
			$id_room = $room["id"];
			$name_room = get_room($connect, $id_room, "full");
			$check = 0;
			$data = $connect->getAll("SELECT on_sale FROM object_room WHERE active=0 AND id_category=?i", $id_room);
			foreach($data as $row){
				if($check == 1)
					break;
				$on_sale_check = array();
				$on_sale = json_decode($row["on_sale"], TRUE);
				foreach($on_sale as $month => $ranges){
					foreach($ranges as $range){
						$start_range = strToTime($range["d"]."-".$month);
						$end_range = $start_range + ($range["n"] - 1) * 86400;
						$after_start = $end_range + 86400;
						$prev_end = $start_range - 86400;
						$check_sale = 0;
						foreach($on_sale_check as $check_start => $check_end){
							if($prev_end == $check_end){
								$on_sale_check[$check_start] = $end_range;
								$check_sale = 1;
								break;
							}
							if($after_start == $check_start){
								$on_sale_check[$start_range] = $check_end;
								unset($on_sale_check[$check_start]);
								$check_sale = 1;
								break;
							}
						}
						if($check_sale == 0)
							$on_sale_check[$start_range] = $end_range;
					}
				}
				foreach($on_sale_check as $start_range => $end_range){
					if($start_range <= $start AND $end_range >= $end){
						if($write_object == 0){
							$image = get_object_image($connect, $id_obj);
							$address = get_object_address($connect, $id_obj);
							$write_object = 1;
							$write = 1;
				?>
					<div class="panel panel-info">
						<div class="panel-heading">
							<img src="<?php echo $image; ?>" class="img-head-small">
							<?php echo $name_object; ?>
							<address><i class="fa fa-map-marker"></i> <?php echo $address; ?></address>
						</div>
						<div class="panel-body">
				<?php
						}
				?>
					<div class="form-group">
							<div class="col-sm-12">
						<div class="well well-sm">
							<div class="col-sm-8">
								<?php echo $name_room; ?>
							</div>
							<div class="col-sm-4" style="text-align: right">
								<button type="button" class="btn btn-success btn-xs" onclick="show_form_booking_reservation('<?php echo $id_room; ?>')"><i class="fa fa-cart-plus"></i> Забронировать</button>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
					</div>
				<?php
						$check = 1;
						break;
					}
				}
			}
		}
		if($write_object == 1){
	?>
			</div>
		</div>
	<?php
		}
	}
?>
	</div>
<?php
	$html = ob_get_clean();
	if($write == 0)
		$html = "<div class='alert alert-info'><i class='fa fa-info-circle'></i> Ничего не найдено.</div>";
	return $html;
}

function show_form_booking_reservation($connect){
	$room = $_POST["room"];
	$object = get_object($connect, $connect->getOne("SELECT id_obj FROM room WHERE id=?i", $room), "type");
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Бронирование <?php echo $object; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal booking-reservation">
					<div class="form-group">
						<label class="col-sm-3 control-label">Номер</label>
						<div class="col-sm-6">
							<div class="well well-sm"><?php echo get_room($connect, $room, "full"); ?></div>
						</div>
						<div class="col-sm-3">
							<div class="well well-sm booking-arrival">Заезд <span class="date"></span> на <span class="day"></span> дней</div>
						</div>
					</div>
					<div class="form-group"><hr /></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Турист</label>
						<div class="col-sm-3">
							<input type="text" class="form-control surname" placeholder="Фамилия" />
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control name" placeholder="Имя" />
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control otch" placeholder="Отчество" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Контакты</label>
						<div class="col-sm-3">
							<input type="text" class="form-control email" placeholder="Email" />
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control telephone" placeholder="Телефон" />
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control address" placeholder="Адрес" />
						</div>
					</div>
					<div class="form-group"><hr /></div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Данные о заезде</label>
						<div class="col-sm-3">
							<input type="text" class="form-control price" placeholder="Цена" />
						</div>
						<div class="col-sm-3">
							<select class="form-control type">
								<option value="1">за чел/сутки</option>
								<option value="2">за номер (дом)</option>
								<option value="3">за заезд</option>
							</select>
						</div>
						<div class="col-sm-3">
							<input type="text" class="form-control number" placeholder="Кол-во" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="booking_reservation('<?php echo $room; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Бронировать</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function booking_reservation($connect){
	date_default_timezone_set("UTC");
	global $session_login;
	$surname = $_POST["surname"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];
	$email = $_POST["email"];
	$address = $_POST["address"];
	$telephone = $_POST["telephone"];

    $sex = null;
    if(isset($_POST['sex'])) {
        $sex = (int)$_POST['sex'];
        if($sex !== 0 && $sex !== 1) {
          $sex = null;
        }
    }

	$room = $_POST["room"];
	$object = $connect->getOne("SELECT id_obj FROM room WHERE id=?i", $room);
	$reward = $connect->getOne("SELECT reward FROM object WHERE id=?i", $object);
	$price = $_POST["price"];
	$number = $_POST["number"];
	$days = $_POST["days"];
	$date = strToTime($_POST["date"]);
	$end = $date + ($days - 1) * 86400;
	$today = date("Y-m-d");
	$type = $_POST["type"];
	if(is_numeric($price) AND is_numeric($number)){
		$data = $connect->getAll("SELECT id FROM object_room WHERE active=0 AND id_category=?i", $room);
		foreach($data as $row){
			$id_room = $row["id"];
			$convert_date = date("Y-m-d", $date);
			$type_place = check_reservation_date($connect, $id_room, $convert_date, $days);
			if($type_place){
			    $original_data = [
                  'surname' => $surname,
                  'name' => $name,
                  'otch' => $otch,
                  'telephone' => $telephone,
                  'address' => $address,
                  'email' => $email,
                  'sex' => $sex
                ];

			    if(is_null($sex))
			        $connect->query("INSERT INTO klient(surname, name, otch, telephone, address, email, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $telephone, $address, $email, json_encode($original_data));
				else
				    $connect->query("INSERT INTO klient(surname, name, otch, sex, telephone, address, email, original_data) VALUES (?s, ?s, ?s, ?i, ?s, ?s, ?s, ?s)", $surname, $name, $otch, $sex, $telephone, $address, $email, json_encode($original_data));

				$client = $connect->insertId();
				$connect->query("INSERT INTO reckoning(date, turist, id_user, id_obj, rest) VALUES (?s, ?i, ?i, ?i, ?i)", $today, $client, $session_login, $object, $client);
				$bid = $connect->insertId();
				setCookie("reck", $bid);
				$connect->query("INSERT INTO position_reck(id_room, sum, number, schet, type, days, date_z, reward, add_one_day) VALUES (?i, ?i, ?i, ?i, ?i, ?i, ?s, ?s, 0)", $room, $price, $number, $bid, $type, $days, $convert_date, $reward);
				recalculation_sum($connect, $bid);
				save_schet_to_history($connect, $bid, "Новая заявка из гарантированных блоков");
				change_arrival_date($connect, $bid);

				$connect->query("INSERT INTO reservation(room, date, day, status, type_place, id_reck) VALUES (?i, ?s, ?i, 2, ?i, ?i)", $id_room, $convert_date, $days, $type_place, $bid);
				$reserv = $connect->insertId();
				save_reservation_history($connect, $reserv, "Новая заявка");
				clear_sale_calendar_object($connect, $reserv);

				return $client;
			}
		}
	}
	return FALSE;
}

?>
