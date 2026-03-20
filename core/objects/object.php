<?php

function show_modal_new_object(){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новый объект</h4>
			</div>
			<div class="modal-body form-horizontal new-object">
				<div class="form-group">
					<div class="col-sm-12">
						<div class="alert alert-info"><i class="fa fa-info-circle"></i> Укажите название объекта, а также место (в скобках), где он расположен</div>
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="new_object" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_object()"><i class="fa fa-check"></i> Сохранить новый объект</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_object($connect){
	$name = $_POST["name"];
	$connect->query("INSERT INTO object(name) VALUES (?s)", $name);
	return $connect->insertId();
}

function show_modal_new_room($connect){
	$id = $_POST["id"];
	$object = $connect->getOne("SELECT name FROM object WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новую категорию номера</h4>
			</div>
			<div class="modal-body form-horizontal new-room">
				<div class="form-group">
					<label class="col-sm-4 control-label">Объект</label>
					<div class="col-sm-8">
						<div class="well well-sm"><?php echo $object; ?></div>
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Номер</label>
					<div class="col-sm-8">
						<input type="text" class="form-control new-room-object" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_room_to_new_object('<?php echo $id; ?>')"><i class="fa fa-check"></i> Сохранить новый объект</button>
			</div>
		</div>
	</div>
</div>
<?php
}


function save_new_room_object($connect){
	$object = $_POST["object"];
	$room = $_POST["room"];
	$connect->query("INSERT INTO room(name, id_obj) VALUES (?s, ?i)", $room, $object);
}

function objects_menu(){
	global $id_rights;
	ob_start();
?>
	<ul class="nav nav-tabs nav-justified menu-object">
		<li class="li-object" onclick="search_object()"><a><i class="fa fa-home"></i> Объекты</a></li>
		<li class="li-no-price" onclick="find_object_no_price()"><a><i class="fa fa-warning"></i> Нет цен</a></li>
	<?php if($id_rights > 3){ ?>
		<li class="li-reservation" onclick="search_object_reservation()"><a><i class="fa fa-calendar"></i> Блоки мест</a></li>
		<li class="li-search-reservation" onclick="show_form_search_engine_reservation()"><a><i class="fa fa-search-plus"></i> Поиск</a></li>
	<?php } ?>
		<li class="li-promo" onclick="menu_all_promotions()"><a><i class="fa fa-star"></i> Акции</a></li>
		<li class="li-rating" onclick="view_all_rating()"><a><i class="fa fa-comments-o"></i> Отзывы</a></li>
	<?php if($id_rights > 3){ ?>
		<li class="li-commission" onclick="view_all_commission_object()"><a><i class="fa fa-percent"></i> Вознаграждение</a></li>
	<?php } ?>
	</ul>
	<div class="data-object" style="padding-top: 10px"></div>
	<div class="clearfix"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function search_object_reservation($connect){
	$data = $connect->getAll("SELECT room.id_obj FROM room, object_room WHERE object_room.id_category=room.id GROUP BY room.id_obj");
	foreach($data as $row){
		$id = $row["id_obj"];
		if(check_free_place_object($connect, $id)){
			$row = $connect->getRow("SELECT name, type FROM object WHERE id=?i", $id);
			$image = get_object_image($connect, $id);
			$address = get_object_address($connect, $id);
			$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
	?>
	<div class="col-sm-6">
		<div class="well well-sm">
			<div class="form-group form-group-bottom">
				<div class="col-sm-2">
					<img src="<?php echo $image; ?>" class="img-thumbnail" />
				</div>
				<div class="col-sm-10">
					<?php echo $type." ".$row["name"]; ?>
					<address><i class="fa fa-map-marker"></i> <?php echo $address; ?></address>
					<button class="btn btn-info btn-xs" onclick="view_calendar_rooms('<?php echo $id; ?>')">Выбрать <i class="fa fa-angle-double-right"></i></button>
				</div>
			</div>
		<div class="clearfix"></div>
		</div>
	</div>
	<?php
		}
	}
}

function show_head_page_object($connect){
	$first = array();
	$func = "";
	$data = $connect->getAll("SELECT id, name FROM object ORDER BY name");
	foreach($data as $row){
		$short_name = $row["name"];
		$id = $row["id"];
		$first[$id] = mb_strtoupper(mb_substr($short_name, 0, 1, "UTF-8"), "UTF-8");
	}
	$first = array_unique($first);
	$first_symbol = array("latin" => "", "rus" => "");
	$isRus = 0;
	foreach($first as $symbol){
		$symbol_up = str_replace(" ", "&nbsp", strToUpper($symbol));
		if($isRus == 0){
			$pattern = '/[а-яА-Я]+/';
			preg_match($pattern, $symbol, $matches);
			if(sizeof($matches) > 0)
				$isRus = 1;
		}
		if($isRus == 0)
			$first_symbol["latin"].= "<li onclick='find_object(\"".$symbol."\")'><a>".$symbol_up."</a></li>";
		else
			$first_symbol["rus"].= "<li onclick='find_object(\"".$symbol."\")'><a>".$symbol_up."</a></li>";
	}
	$end_price = json_decode($connect->getOne("SELECT value FROM constant WHERE name='end-price-object'"), TRUE);
?>
	<?php if($end_price){ ?>
	<div class="alert alert-warning" style="margin-bottom: 10px">
		<strong>Заканчиваются цены:</strong>
		<?php foreach($end_price as $object){ ?>
		<a onclick="view_object('<?php echo $object; ?>')" class="alert-link"><?php echo get_object($connect, $object); ?></a>
 		<?php } ?>
	<?php } ?>
	</div>
	<div class="input-group">
		<span class="input-group-addon"><i class="fa fa-search"></i></span>
		<input type="text" id="object" class="form-control" placeholder="Название объекта" onkeyup="find_klient(event, 'object', 'object', 'view_object')" />
	</div>
	<div>
		<ul class="pagination pagination-sm">
			<?php echo $first_symbol["latin"]; ?>
		<br /><br />
			<?php echo $first_symbol["rus"]; ?>
		</ul>
	</div>
	<div class="form-horizontal">
		<div class="well-sm alert-info">Регионы России</div>
		<div class="div-region">
	<?php
		$data = $connect->getAll("SELECT id, name FROM region WHERE active=0 AND id_country=1 ORDER BY name");
		foreach($data as $row){
			$id_reg = $row["id"];
	?>
			<div class="col-sm-4 well well-sm region-<?php echo $id_reg; ?> pointer" onclick="find_object_by_region('<?php echo $id_reg; ?>')">
				<?php echo $row["name"]; ?>
			</div>
	<?php } ?>
            <div class="col-sm-4 well well-sm region-<?php echo $id_reg; ?> pointer" onclick="find_object_by_region(0);">
              Регион не указан
            </div>
		</div>
		<div class="clearfix"></div>
		<div class="well-sm alert-info">Страны</div>
		<div class="div-country">
	<?php
		$data = $connect->getAll("SELECT id, name FROM country WHERE id=2");
		foreach($data as $row){
			$id_country = $row["id"];
	?>
			<div class="col-sm-4 well well-sm country-<?php echo $id_country; ?> pointer" onclick="find_object_by_country('<?php echo $id_country; ?>')">
				<?php echo $row["name"]; ?>
			</div>
	<?php } ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="result-object"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function find_object_no_price($connect){
	$data = array();
	$no_price_object = json_decode($connect->getOne("SELECT value FROM constant WHERE name='no-price-object'"), TRUE);
	foreach($no_price_object as $object){
		$row = $connect->getRow("SELECT name, id_reg FROM object WHERE id=?i", $object);
		if(!isset($data[$row["id_reg"]]))
			$data[$row["id_reg"]] = array();
		$data[$row["id_reg"]][$object] = $row["name"];
	}
	foreach($data as $id_region => $objects){
		$region = $connect->getOne("SELECT name FROM region WHERE id=?i", $id_region);
?>
	<div class="panel panel-success">
		<div class="panel-heading">
			<?php echo $region; ?>
		</div>
		<div class="list-group">
<?php
			foreach($objects as $id_object => $object){
?>
			<div class="list-group-item list-hover-item" onclick="view_object(<?php echo $id_object; ?>)">
				<?php echo $object; ?>
			</div>
<?php
			}
?>
		</div>
	</div>
<?php
	}
}

function select_object($connect){
	global $id_rights;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, image, type, name, full_name, telephone, email, fax, address, arrival, leaving, add_one_day, regular_com, up_com, reward, website, note_reward, check_places FROM object WHERE id=?i", $id);
	$image = "images/object/defaul.jpg";
	$website = $row["website"];
	if($row["image"])
		$image = "data:image/jpg;base64,".$row["image"];
	$add_day = "днями";
	if($row["add_one_day"] == 1)
		$add_day = "сутками";
	elseif($row["add_one_day"] == 2)
		$add_day = "неопред.";
	$object = get_object($connect, $id, "place");
	$quota = $row["check_places"];
	$array = json_decode($row["telephone"], TRUE);
	$telephone = "";

	if(is_array($array)) {
      foreach($array as $value){
        if($telephone)
          $telephone.= "<br />";
        $telephone.= "<strong>".$value["value"]."</strong> ".$value["note"];
      }
    }

	$array = json_decode($row["email"], TRUE);
	$email = "";

	if(is_array($array)) {
      foreach($array as $value){
        if($email)
          $email.= "<br />";
        $email.= "<strong>".$value["value"]."</strong> ".$value["note"];
      }
    }

	$dogovor_object = select_object_contract($connect, $id);
	ob_start();
?>
<button type="button" class="btn btn-warning btn-xs" onclick="show_prev_page()"><i class="fa fa-angle-double-left"></i> вернуться назад</button>
<div class="form-horizontal panel panel-primary" style="margin-top: 10px">
	<div class="panel-heading"><?php echo $object; ?>&nbsp;&nbsp;<i class="fa fa-ellipsis-h pointer" onclick="show_menu_object('<?php echo $id; ?>')" id="object-active"></i></div>
	<div class="panel-body">
		<div class="form-group form-group-margin">
			<div class="col-sm-2 center">
				<img class="img-thumbnail" src="<?php echo $image; ?>" />
				<?php if($quota > 0){ ?>
				<div>
					<span class="pointer" onclick="show_quota_object_card(<?php echo $id; ?>)" title="Квота мест">
					<?php if($quota == 1){ ?>
						<i class="fa fa-text-width fa-4x text-success"></i>
					<?php } ?>
					<?php if($quota == 2){ ?>
						<i class="fa fa-check-square fa-4x text-success"></i>
					<?php } ?>
					<?php if($quota == 3){ ?>
						<i class="fa fa-product-hunt fa-4x text-success"></i>
					<?php } ?>
					</span>
				</div>
				<?php } ?>
			</div>
			<div class="col-sm-10">
				<div class="form-horizontal list-group">
					<?php if($telephone){ ?>
					<div class="list-group-item">
						<div class="form-group form-group-margin">
							<label class="col-sm-3 control-label-element">Телефон</label>
							<div class="col-sm-9">
								<?php echo $telephone; ?>
							</div>
						</div>
					</div>
					<?php } ?>
					<?php if($email){ ?>
					<div class="list-group-item">
						<div class="form-group form-group-margin">
							<label class="col-sm-3 control-label-element">Email</label>
							<div class="col-sm-9">
								<?php echo $email; ?>
							</div>
						</div>
					</div>
					<?php } ?>
					<div class="list-group-item">
						<div class="form-group form-group-margin">
							<label class="col-sm-3 control-label-element">Официальный сайт</label>
							<div class="col-sm-3">
								<a href="<?php echo $website; ?>" target="_blank"><?php echo str_replace("http://", "", $website); ?></a>
							</div>
							<label class="col-sm-3 control-label-element">Считаем</label>
							<div class="col-sm-3">
								<?php echo $add_day; ?>&nbsp;
							</div>
						</div>
					</div>
					<?php if($row["arrival"]){ ?>
					<div class="list-group-item">
						<div class="form-group form-group-margin">
							<label class="col-sm-3 control-label-element">Заезд</label>
							<div class="col-sm-3">
								<?php echo $row["arrival"]; ?>
							</div>
							<label class="col-sm-3 control-label-element">Выезд</label>
							<div class="col-sm-3">
								<?php echo $row["leaving"]; ?>
							</div>
						</div>
					</div>
					<?php } ?>
					<div class="list-group-item">

						<form class="form-inline" data-object-id="<?=$row['id']?>">
						   <div class="form-group form-group-margin" data-id="">
					           <div class="col-sm-6">
				                   <label class="col-sm-6 control-label">Комиссия агентствам</label>
				                   <div class="col-sm-6 input-group object-<?=$row['id']?>">
			                           <input type="number" min="0" step="1" class="form-control update regular-value" name="regular_com" value="<?=$row["regular_com"]; ?>" <?=$id_rights <= 3 ? 'disabled' : '' ?> />

				                   <span class="input-group-addon">%</span> 
				                   </div>
					           </div>
					           <div class="col-sm-6">
				                   <label class="col-sm-6 control-label">Вознаграждение</label>
				                   <div class="col-sm-6 input-group object-<?=$row['id']?>">
			                           <input type="number" min="0" step="1" class="form-control update reward-value" name="reward"  value="<?=$row["reward"]; ?>" <?=$id_rights <= 3 ? 'disabled' : '' ?> />
				                   <span class="input-group-addon">%</span>
				                   </div>
					           </div>
						    </div>
						</form>
<!--
						<div class="form-group form-group-margin">
							<label class="col-sm-3 control-label-element">Комиссия агентствам</label>
							<div class="col-sm-3">
								<?php echo $row["regular_com"]; ?>%
							</div>
							<label class="col-sm-3 control-label-element">Вознаграждение</label>
							<div class="col-sm-3">
								<?php echo $row["reward"]; ?>%
							</div>
						</div>
-->
					</div>
						<?php if($row["note_reward"] != ""){ ?>
					<div class="list-group-item">
						<div class="form-group form-group-margin">
							<label class="col-sm-3 control-label-element">Примечание к вознаграждению</label>
							<div class="col-sm-9">
								<?php echo $row["note_reward"]; ?>
							</div>
						</div>
					</div>
						<?php } ?>
					<div class="list-group-item">
						<div class="form-group form-group-margin">
							<label class="col-sm-3 control-label-element">Договор</label>
							<div class="col-sm-9">
								<div class="contracts-object">
							<?php foreach($dogovor_object as $dogovor){ ?>
									<div class="contract-object-<?php echo $dogovor['id']; ?>">
										<?php echo view_object_contract($dogovor); ?>
									</div>
							<?php } ?>
								</div>
							<?php if($id_rights >= 4){ ?>
								<div class="pull-right">
									<button class="btn btn-sm btn-primary" onclick="add_new_contract_object(<?php echo $id; ?>)">Новый договор</button>
								</div>
							<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="btn-group btn-group-justified nav-object" data-object-id="<?=$id;?>">
			<div class="btn-group">
				<button type="button" class="btn btn-default desc-object" onclick="view_description_object('<?php echo $id; ?>')"><i class="fa fa-pencil-square-o"></i> Описание</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn btn-default room-object" onclick="view_object_rooms('<?php echo $id; ?>')"><i class="fa fa-codepen"></i> Номера</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn btn-default price-object" onclick="view_dates_price_object('<?php echo $id; ?>')"><i class="fa fa-rub"></i></i> Цены</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn btn-default promo-object" onclick="view_promotions_object('<?php echo $id; ?>')"><i class="fa fa-star"></i> Акции</button>
			</div>
		</div>
	</div>
</div>
<div id="infa_object"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_menu_object(){
	global $id_rights;
	$id = $_POST["id"];
?>
	<span onclick="edit_object_info('<?php echo $id; ?>')">Редактировать</span>
	<span onclick="edit_object_sync_info('<?php echo $id; ?>')">Синхронизация 1С</span>
    <span onclick="object_agency_report('<?php echo $id; ?>')">Отчет агента</span>
<?php
}

function edit_object($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, full_name, type, telephone, email, fax, address, full_name, leaving, arrival, add_one_day, regular_com, up_com, reward, website, note_reward FROM object WHERE id=?i", $id);
	$select = array(0, 1, 2);
	$select[$row["add_one_day"]] = " SELECTED ";
	$add_one_day = "<select class='form-control' id='add_one_day'><option value='0' ".$select[0].">днями</option><option value='1' ".$select[1].">сутками</option><option value='2' ".$select[2].">неопределенно</option></select>";
	$telephone = json_decode($row["telephone"], TRUE);
	$email = json_decode($row["email"], TRUE);
	$row = clear_quotes($row);
	ob_start();
?>
<div class="form-horizontal panel panel-default edit">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Изменить объект «<?php echo $row["name"]; ?>»</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Полное название</label>
			<div class="col-sm-9">
				<input type="text" id="full_name" class="form-control" value="<?php echo $row['full_name']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Адрес</label>
			<div class="col-sm-9">
				<input type="text" id="address" class="form-control" value="<?php echo $row['address']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Официальный сайт</label>
			<div class="col-sm-9">
				<input type="text" class="form-control object-website" value="<?php echo $row['website']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Примечание к вознаграждению</label>
			<div class="col-sm-9">
				<textarea class="form-control object-note-reward"><?php echo $row["note_reward"]; ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Считаем</label>
			<div class="col-sm-3">
				<?php echo $add_one_day; ?>
			</div>
			<label class="col-sm-3 control-label">Факс</label>
			<div class="col-sm-3">
				<input type="text" id="fax" class="form-control" value="<?php echo $row['fax']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Заезд</label>
			<div class="col-sm-3">
				<input type="text" id="arrival" class="form-control" value="<?php echo $row['arrival']; ?>" />
			</div>
			<label class="col-sm-3 control-label">Выезд</label>
			<div class="col-sm-3">
				<input type="text" id="leaving" class="form-control" value="<?php echo $row['leaving']; ?>" />
			</div>
		</div>
		<div class="form-group telephone">
			<div class="col-sm-5">Телефон</div>
			<div class="col-sm-5">Примечание</div>
		<?php foreach($telephone as $value){ ?>
			<div class="object_infa">
				<div class="col-sm-5">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<input type="text" class="form-control value" value="<?php echo $value['value']; ?>" />
					</div>
				</div>
				<div class="col-sm-5">
					<input type="text" class="form-control note" value="<?php echo $value['note']; ?>" />
				</div>
				<div class="col-sm-2">
					<button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove()"><i class="fa fa-times-circle"></i> Удалить</button>
				</div>
			</div>
		<?php } ?>
			<div class="new object_infa">
				<div class="col-sm-5">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<input type="text" class="form-control value" />
					</div>
				</div>
				<div class="col-sm-5">
					<input type="text" class="form-control note" />
				</div>
				<div class="col-sm-2">
					<button class="btn btn-success btn-xs" onclick="add_new_contact_object('telephone')"><i class="fa fa-plus-circle"></i> Добавить</button>
				</div>
			</div>
		</div>
		<div class="form-group email">
			<div class="col-sm-5">Email</div>
			<div class="col-sm-5">Примечание</div>
		<?php foreach($email as $value){ ?>
			<div class="object_infa">
				<div class="col-sm-5">
					<div class="input-group">
						<span class="input-group-addon">@</span>
						<input type="text" class="form-control value" value="<?php echo $value['value']; ?>" />
					</div>
				</div>
				<div class="col-sm-5">
					<input type="text" class="form-control note" value="<?php echo $value['note']; ?>" />
				</div>
				<div class="col-sm-2">
					<button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove()"><i class="fa fa-times-circle"></i> Удалить</button>
				</div>
			</div>
		<?php } ?>
			<div class="new object_infa">
				<div class="col-sm-5">
					<div class="input-group">
						<span class="input-group-addon">@</span>
						<input type="text" class="form-control value" />
					</div>
				</div>
				<div class="col-sm-5">
					<input type="text" class="form-control note" />
				</div>
				<div class="col-sm-2">
					<button class="btn btn-success btn-xs" onclick="add_new_contact_object('email')"><i class="fa fa-plus-circle"></i> Добавить</button>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm" onclick="save_object_info(<?php echo $id; ?>)"><i class="fa fa-check-circle"></i> Сохранить</button>
		<button type="button" class="btn btn-danger btn-sm" onclick="view_object(<?php echo $id; ?>)"><i class="fa fa-times-circle"></i> Отмена</button>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_object_info($connect){
	$id = $_POST["id"];
	$website = $_POST["website"];
	if(!substr_count($website, "http"))
		$website = "http://".$website;
	$connect->query("UPDATE object SET full_name=?s, telephone=?s, email=?s, fax=?s, address=?s, arrival=?s, leaving=?s, add_one_day=?s, note_reward=?s, website=?s, synchronized=0 WHERE id=?i", $_POST["name"], $_POST["telephone"], $_POST["email"], $_POST["fax"], $_POST["address"], $_POST["arrival"], $_POST["leaving"], $_POST["add_one_day"], $_POST["note_reward"], $website, $id);
}

function select_name_object($connect){
	$id = $_POST["id"];
	return get_object($connect, $id);
}

function show_review_rating($connect){
    global $id_rights, $session_login;
	ob_start();
?>
<div class="form-horizontal panel panel-default edit">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Менеджер</label>
			<div class="col-sm-9">
				<?php echo get_managers($connect, "filter","", $id_rights, $session_login); ?>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-3 control-label">Объект</label>
			<div class="col-sm-9" id="object_name">
				<input type="text" class="form-control id-object" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" name="">
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-primary btn-sm" onclick="search_rating()"><i class="fa fa-search"></i> Найти</button>
	</div>
</div>
<div id="rating-html"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function search_rating($connect){
	global $id_rights;
	$id_obj = $_POST["id_obj"];
	$zapros_for_mysql = "";
	if($id_obj)
		$zapros_for_mysql = " id_obj=$id_obj ";
	if($zapros_for_mysql)
		$zapros_for_mysql.= "AND";
	$zapros_for_mysql.= " (status=3 OR status=4) ";
	$data = $connect->getAll("SELECT id, status, id_obj, schet, clean, comfort, location, staff, ratio, leisure, treatment, DATE_FORMAT(date_send, '%d.%m.%Y') as date, positive, negative, advice, company_rating FROM rating WHERE ".$zapros_for_mysql." ORDER BY date_send DESC");
	if(!$data)
		return "<div class='alert alert-info'><i class='fa fa-exclamation-triangle'></i> Ничего не найдено</div>";
?>
	<table class="table table-condensed table-bordered">
	<tr>
		<th>Дата</th>
		<th>Объект</th>
		<th title="Средняя оценка"><i class="fa fa-star icon_star"></i></th>
		<th colspan="2"></th>
	</tr>
<?php
	foreach($data as $row){
		$object = get_object($connect, $row["id_obj"]);
		$count = 6;
		$average = $row["clean"] + $row["comfort"] + $row["location"] + $row["staff"] + $row["treatment"] + $row["leisure"] + $row["ratio"];
		if($row["treatment"])
			$count++;
		if(!$row["schet"])
			 $row["schet"] = "создан";
		$average = round($average * 2 / $count, 1);
		$class = "";
		if($row["status"] == 4)
			$class = " class='danger' ";
		ob_start();
?>
		<tr <?php echo $class; ?>>
			<td width="10%"><?php echo $row["date"]; ?></td>
			<td width="10%"><?php echo $object." (".$row["schet"].")"; ?></td>
			<td width="5%"><?php echo $average; ?></td>
			<td width="70%">
				<?php if($row["positive"]){ ?>
					<div class="alert alert-success"><i class="fa fa-plus-circle"></i> <?php echo $row["positive"]; ?></div>
				<?php } ?>
				<?php if($row["negative"]){ ?>
					<div class="alert alert-danger"><i class="fa fa-minus-circle"></i> <?php echo $row["negative"]; ?></div>
				<?php } ?>
				<?php if($row["advice"]){ ?>
					<div class="alert alert-info"><i class="fa fa-thumbs-o-up"></i> <?php echo $row["advice"]; ?></div>
				<?php } ?>
				<?php if($row["company_rating"]){ ?>
					<div class="alert alert-default"><i class="fa fa-smile-o"></i> <?php echo $row["company_rating"]; ?></div>
				<?php } ?>
			</td>
			<td width="5%" class="center">
				<?php if($id_rights == 5){ ?>
					<button class="btn btn-default btn-sm" onclick="edit_rating('<?php echo $row['id']; ?>')">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
				<?php } ?>
			</td>
		</tr>
<?php
	}
?>
	</table>
<?php
}

function view_description_object($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, type, id_profile, id_methods, id_infa, id_services, service_info FROM object WHERE id=?i", $id);
	$images = $connect->getAll("SELECT name, basename FROM image WHERE id_subject=?i", $id);
?>
<div class="panel panel-default">
	<div class="form-horizontal list-group">
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Инфраструктура</label>
				<div class="col-sm-10">
					<?php echo mb_strtolower(parse_index_string($connect, $row["id_infa"], "infa", "_", ", "), "UTF-8"); ?>
				</div>
			</div>
		</div>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Профили лечения</label>
				<div class="col-sm-10">
					<?php echo mb_strtolower(parse_index_string($connect, $row["id_profile"], "profile", "_", ", "), "UTF-8"); ?>
				</div>
			</div>
		</div>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Методы лечения</label>
				<div class="col-sm-10">
					<?php echo mb_strtolower(parse_index_string($connect, $row["id_methods"], "methods", "_", ", "), "UTF-8"); ?>
				</div>
			</div>
		</div>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Служебная информация</label>
				<div class="col-sm-10">
					<?php echo str_replace("\n", "<br />", $row["service_info"]); ?>
				</div>
			</div>
		</div>
        <div class="list-group-item list-hover-item">
	        <div class="form-group form-group-margin">
               <label class="col-sm-2 control-label-element">Услуги объекта</label>
               <div class="col-sm-10">
                       <?php
                       $services = json_decode($row["id_services"], TRUE);
                       $type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
                       $array_services = $connect->getAll("SELECT id, name, icon FROM services");
                       ?>
                       <ul class="list-unstyled list-icons">
                       
                       <?php foreach($array_services as $service){
                       $icon = "";
                       if($service["icon"])
                           $icon = "<i class='fa ".$service["icon"]."'></i>";
                       $id_s = $service["id"];
                       if(!isset($services[$id_s]))
                           $services[$id_s] = "";
                       ?>
                       <li class="row">
                           <div class="col-sm-4">
                                   <?php echo $icon." ".$service["name"]; ?> 
                           </div>
                           <div class="col-sm-8">
                                   <?=$services[$id_s] ? $services[$id_s] : ' - ' ?>
                           </div>
                       </li>
                       <?php } ?>
                   </ul>
               </div>
	       </div>
		</div>

		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Картинки</label>
				<div class="col-sm-10">
					<?php foreach($images as $image){
					if(!$image["name"])
						$image["name"] = "не указано";
					?>
					<a href="images/service/<?php echo $image['basename']; ?>" target="_blank"><?php echo $image['name']; ?></a>&nbsp;
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-default btn-sm" onclick="edit_service_information_object('<?php echo $id; ?>', 'object')"><i class="fa fa-pencil"></i> Изменить служебную информацию</button>
		<button type="button" class="btn btn-success btn-sm" onclick="add_new_picture_object('<?php echo $id; ?>')"><i class="fa fa-picture-o"></i> Новая картинка</button>
	</div>
</div>
<?php
}

function form_new_document($connect){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Загрузить новую картинку</h4>
			</div>
			<div class="modal-body form-horizontal new-document">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Название файла</label>
					<div class="col-sm-8">
						<input type="text" class="form-control name-document" />
					</div>
				</div>
			</div>
			<div class="modal-footer text-center">
				<button type="button" class="btn btn-primary btn-sm" id="uploadButton"><i class="fa fa-file-image-o"></i> Выбрать фото</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function upload_new_image_object($connect){
	$id = $_POST["id"];
	$photo = $_POST["file"];
	$name = $_POST["name"];
	$basename = basename($photo);
	$connect->query("INSERT INTO image(name, id_subject) VALUES (?s, ?i)", $name, $id);
	$last = $connect->insertId();
	$basename = $last."_".$basename;
	$connect->query("UPDATE image SET basename=?s WHERE id=?i", $basename, $last);
	copy($photo, "images/service/".$basename);
	unlink($photo);
}

function find_object($connect){
	$head = "";
	$region = "";
	$country = "";
	$direction = "";
	$zapros_for_mysql = "";
	if(isset($_POST["head"]))
		$head = $_POST["head"];
	if(isset($_POST["region"]))
		$region = $_POST["region"];
	if(isset($_POST["country"]))
		$country = $_POST["country"];
	if(isset($_POST["direction"]))
		$direction = $_POST["direction"];
	if($region)
		$zapros_for_mysql = " id_reg=".$region;
	elseif ($region === '0')
        $zapros_for_mysql = " id_reg IS NULL";
	elseif($head)
		$zapros_for_mysql = " name LIKE '$head%' ";
	elseif($direction)
		$zapros_for_mysql = " direction=".$direction;
	$object = "";
	$direction_html = "";
	if($region){
      $data = $connect->getAll("SELECT id, name FROM direction_object WHERE id_reg=?i", $region);
      foreach($data as $row){
        $id_dir = $row["id"];
        ob_start();
        ?>
          <div class="col-sm-4 well well-sm pointer direction-<?php echo $id_dir; ?>" onclick="find_object_by_direction('<?php echo $id_dir; ?>')">
            <?php echo $row["name"]; ?>
          </div>
        <?php
        $direction_html.= ob_get_clean();
      }
	}elseif($country){
		$data = $connect->getAll("SELECT id, name FROM direction_object WHERE id_country=?i", $country);
		foreach($data as $row){
			$id_dir = $row["id"];
			ob_start();
	?>
		<div class="col-sm-4 well well-sm pointer direction-<?php echo $id_dir; ?>" onclick="find_object_by_direction('<?php echo $id_dir; ?>')">
			<?php echo $row["name"]; ?>
		</div>
	<?php
			$direction_html.= ob_get_clean();
		}
	}
	$data = $connect->getAll("SELECT id, type, name, image FROM object WHERE ".$zapros_for_mysql." AND active=0 ORDER BY name");
	foreach($data as $row){
		$image = "images/object/defaul.jpg";
		if($row["image"])
			$image = "data:image/jpg;base64,".$row["image"];
		$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
		ob_start();
?>
	<div class="col-sm-4 well well-sm">
		<img src="<?php echo $image; ?>" class="img-head-small" />
		<p class="pointer" onclick="view_object('<?php echo $row['id']; ?>')"><i class="fa fa-home"></i> <?php echo $type." ".$row["name"]; ?></p>
		<div class="clearfix"></div>
	</div>
<?php
		$object.= ob_get_clean();
	}
	ob_start();
?>
<?php if(!$direction){ ?>
	<div class="form-horizontal">
	<?php if($direction_html){ ?>
		<div class="alert alert-info well-sm">Направления</div>
		<div class="div-direction">
			<?php echo $direction_html; ?>
		</div>
		<div class="clearfix"></div>
		<div class="div-object">
	<?php } ?>
		<div class="alert alert-info well-sm">Объекты</div>
		<div>
			<?php echo $object; ?>
		</div>
	<?php ?>
	</div>
<?php }else{ ?>
	<div class="alert alert-info well-sm">Объекты</div>
	<div>
		<?php echo $object; ?>
	</div>
<?php } ?>
<?php
	$html = ob_get_clean();
	echo $html;
}

function view_object_rooms($connect){
	global $directory;
?>
	<div class="list-group panel panel-default form-horizontal rooms">
<?php
	$object = $_POST["id"];
	$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $object);
	$data = $connect->getAll("SELECT id, name, active FROM room WHERE id_obj=?i ORDER BY active, priority, housing DESC", $object);
	foreach($data as $row){
		$room = $row["id"];
		$name = get_room($connect, $room, "full");
		$class = "";
		if($row["active"] == 1)
			$class = "list-group-item-danger";
		$folder = $directory."/temp/images/".$region."/".$object."/".$room."/small/";
		$have = 0;
		if(is_dir($folder)) {
          $folder_open = opendir($folder);
          while($image = readdir($folder_open)){
            if(($image != ".") AND ($image != "..") AND ($image)){
              $have = 1;
              break;
            }
          }
        }
	?>
	<div class="list-group-item div-room-<?php echo $room; ?> <?php echo $class; ?>" room="<?php echo $room; ?>">
		<div class="form-group form-group-margin">
			<div class="col-sm-1 text-center">
				<i class="fa fa-align-justify handle pointer"></i>
			</div>
			<div class="col-sm-7 name-room-<?php echo $room; ?>">
				<?php echo $name; ?>
                <button class="btn btn-default btn-xs" onclick="edit_room('<?=$room;?>',true)" title="Редактировать"><i class="fa fa-pencil"></i></button>
            </div>
		</div>
	</div>
	<?php
	}
?>
	</div>
<?php
}

function update_priority_room($connect){
	$data = json_decode($_POST["data"], TRUE);
	foreach($data as $index => $room)
		$connect->query("UPDATE room SET priority=?i, synchronized = 0 WHERE id=?i", $index + 1, $room);
}

function edit_object_sync_info($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT 1C_full_name, inn, 1C_code, nomenclature, bank_login FROM object WHERE id=?i", $id);
	$row = clear_quotes($row);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить информацию <?php echo get_object($connect, $id, "type"); ?></h4>
			</div>
			<div class="modal-body form-horizontal edit-object">
				<div class="form-group">
					<label class="col-sm-4 control-label">Полное название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control full-name-object" value="<?php echo $row['1C_full_name']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">ИНН</label>
					<div class="col-sm-8">
						<input type="text" class="form-control inn-object" value="<?php echo $row['inn']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Код контрагента</label>
					<div class="col-sm-8">
						<input type="text" class="form-control code-1C" value="<?php echo $row['1C_code']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Код номенклатуры</label>
					<div class="col-sm-8">
						<input type="text" class="form-control nomenclature" value="<?php echo $row['nomenclature']; ?>" />
					</div>
				</div>
				<!--<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Логин Альфа-банк</label>
					<div class="col-sm-8">
						<input type="text" class="form-control bank-login" value="<?php echo $row['bank_login']; ?>" />
					</div>
				</div>-->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_object_sync_info(<?php echo $id; ?>)"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_object_sync_info($connect){
	$_POST = clear_array($_POST);
	$id = $_POST["id"];
	$full = $_POST["full"];
	$code = trim($_POST["code"]);
	$inn = trim($_POST["inn"]);
	$nomenclature = $_POST["nomenclature"];
	$bank_login = $_POST["login"];
	$connect->query("UPDATE object SET 1C_full_name=?s, inn=?s, 1C_code=?s, nomenclature=?s, bank_login=?s, synchronized=0 WHERE id=?i", $full, $code, $inn, $nomenclature, $bank_login, $id);
}

function edit_service_information_object($connect){
	$id = $_POST["id"];
	$info = $connect->getOne("SELECT service_info FROM object WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить информацию объекта</h4>
			</div>
			<div class="modal-body form-horizontal service-information">

				<div class="form-group form-group-margin">
					<div class="col-sm-12">
						<textarea class="form-control information-object"><?php echo $info; ?></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_service_information_object('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_service_information_object($connect){
	$id = $_POST["id"];
	$info = strip_tags($_POST["info"]);
	$connect->query("UPDATE object SET service_info=?s, synchronized=0 WHERE id=?i", $info, $id);
}

function view_all_commission_object($connect){
	global $id_rights;
	$result = array("right" => 0, "region" => array());
	if($id_rights > 3)
		$result["right"] = 1;
	$data = $connect->getAll("SELECT id, name FROM region WHERE id_country=1 ORDER BY name");
	foreach($data as $row){
		$id_region = $row["id"];
		$region = get_translit(str_replace(" ", "-", $row["name"]));
		$result["region"][$region] = array("name" => $row["name"], "object" => array());
		$objects = $connect->getAll("SELECT id, name, type, regular_com, reward, note_reward FROM object WHERE active=0 AND id_reg=?i", $id_region);
		foreach($objects as $object){
			$id = $object["id"];
			$result["region"][$region]["object"][$id] = array();
			$result["region"][$region]["object"][$id]["name"] = get_object($connect, $object["id"], "place");
			$result["region"][$region]["object"][$id]["reward"] = $object["reward"];
			if(!is_null($object['note_reward']))
			    $result["region"][$region]["object"][$id]["note_reward"] = $object["note_reward"];
			else
                $result["region"][$region]["object"][$id]["note_reward"] = "";
          $result["region"][$region]["object"][$id]["commis"] = $object["regular_com"];
		}
	}
	return json_encode($result);
}

function update_commission_object($connect){
	$object = $_POST["object"];
	$regular = $_POST["regular"];
	$reward = $_POST["reward"];
	$note_reward = $_POST["note_reward"];
	$connect->query("UPDATE object SET regular_com=?s, reward=?s, note_reward=?s, synchronized=0 WHERE id=?i", $regular, $reward, $note_reward, $object);
	$row = $connect->getRow("SELECT regular_com, reward, note_reward FROM object WHERE id=?i", $object);
	if(is_null($row['note_reward']))
      $row['note_reward'] = '';
	return json_encode($row);
}

function select_object_contract($connect, $object){
	//$today = date("Y-m-d");
	//$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, type, active, status FROM object_contract WHERE object=?i AND date>=?s ORDER BY date DESC", $object);
	$today = date("Y-m-d", strtotime('2020-01-01'));
	$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, type, active, status FROM object_contract WHERE object=?i AND date>=?s ORDER BY date DESC", $object, $today);
	return $data;
}

function view_object_contract($dogovor){
	global $id_rights;
	$type_dogovor = "Договор санатория";
	if($dogovor["type"] == "sanata")
		$type_dogovor = "Договор Саната";
	$class_label = "danger";
	$label = "Скан не получен";
	if($dogovor["status"] == 1 OR $dogovor["status"] == 2)
		$class_label = "success";
	if($dogovor["status"] == 1)
		$label = "Скан получен";
	if($dogovor["status"] == 2)
		$label = "Оригинал получен";
	ob_start();
?>
	<div class="form-group">
		<div class="col-sm-6">
			<?php echo $type_dogovor." № ".$dogovor["number"].", действует до ".$dogovor["date_cont"]; ?>
			<span class="label label-<?php echo $class_label; ?>"><?php echo $label; ?></span>
		</div>
		<div class="col-sm-6">
		<?php if($id_rights >= 4){ ?>
			<button class="btn btn-default btn-xs" onclick="edit_contract_object(<?php echo $dogovor['id']; ?>)"><i class="fa fa-pencil"></i> изменить</button>
		<?php if($dogovor["status"] == 0){ ?>
			<button class="btn btn-success btn-xs" onclick="update_status_contract_object(<?php echo $dogovor['id']; ?>, 1)"><i class="fa fa-check"></i> скан получен</button>
		<?php } ?>
		<?php if($dogovor["status"] == 1){ ?>
			<button class="btn btn-success btn-xs" onclick="update_status_contract_object(<?php echo $dogovor['id']; ?>, 2)"><i class="fa fa-check"></i> оригинал получен</button>
		<?php } ?>
		<?php if($dogovor["status"] == 1 OR $dogovor["status"] == 2){ ?>
			<button class="btn btn-danger btn-xs" onclick="update_status_contract_object(<?php echo $dogovor['id']; ?>, 0)"><i class="fa fa-times"></i> сбросить статус</button>
		<?php } ?>
		<?php } ?>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_contract_object($connect){
	$object = $_POST["object"];
	$type = $_POST["type"];
	$date = $_POST["date"];
	$number = $_POST["number"];
	$connect->query("INSERT INTO object_contract(object, type, number, date) VALUES (?i, ?s, ?s, ?s)", $object, $type, $number, $date);
	$id = $connect->insertId();
	$data = array();
	$row = $connect->getRow("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, type, active, status FROM object_contract WHERE id=?i", $id);
	$data["id"] = $id;
	$data["html"] = view_object_contract($row);
	return json_encode($data);
}

function edit_contract_object($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT type, number, date FROM object_contract WHERE id=?i", $id);
	return json_encode($row);
}

function update_contract_object($connect){
	$id = $_POST["id"];
	$type = $_POST["type"];
	$date = $_POST["date"];
	$number = $_POST["number"];
	$connect->query("UPDATE object_contract SET type=?s, number=?s, date=?s WHERE id=?i", $type, $number, $date, $id);
	$row = $connect->getRow("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, type, active, status FROM object_contract WHERE id=?i", $id);
	$html = view_object_contract($row);
	return json_encode($html);
}

function update_status_contract_object($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$connect->query("UPDATE object_contract SET status=?i WHERE id=?i", $status, $id);
	$row = $connect->getRow("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date_cont, number, type, active, status FROM object_contract WHERE id=?i", $id);
	$html = view_object_contract($row);
	return json_encode($html);
}

function tl_webhooks($connect) {

	$html = '
			<div class="form-horizontal panel panel-default">
				<div class="panel-heading">
					Запросы на добавление объектов из Travelline
				</div>	
				<div class="list-group">
			';

	$items = $connect->getAll("SELECT * FROM 1_tl_webhook WHERE `eventType`='PropertyAdded' AND  `content_api_data`<>'' AND `datetime`> NOW() - INTERVAL 3 MONTH ORDER BY id DESC");
	foreach ($items as $item) {
		if (!$item['worked']) $worked = 'warning'; else $warning = '';
		$item['content_api_data'] = json_decode($item['content_api_data'], true);
		$del_btn = '';
		if ($item['worked']==1) $del_btn = '<button type="button" class="btn btn-danger btn-xs" onclick="tl_webhook_del('.$item['id'].')">Удалить</button>';		
		if ($item['worked']==2) {
			$item['worked'] = '<strong style="color: green;">обработано</strong>';
		} else {
			$item['worked'] = 'не обработано';
		}
		$html .= '
				<div class="list-group-item form-group '.$worked.'" style="margin: 0">
					<div class="col-sm-1">
						'.$item['id'].'
					</div>				
					<div class="col-sm-2">
						'.date('d.m.Y H:i:s', strtotime($item['datetime'])).'	
					</div>					
					<div class="col-sm-4">
						<i class="fa fa-home"></i> '.$item['content_api_data']['name'].'
					</div>
					<div class="col-sm-1">
						'.$item['entityId'].'
					</div>
					<div class="col-sm-2">
						'.$item['worked'].'
					</div>
					<div class="col-sm-2">
						<button type="button" class="btn btn-success btn-xs" onclick="tl_webhook('.$item['id'].')">Смотреть</button>						
						'.$del_btn.'
					</div>
				</div>		
		';
	}

	$html .= '</div></div><style>.list-group-item.warning {background-color: #fcf8e3}</style>';

	return $html;
}

function AddBR($str) {
	$str = str_replace("\r\n", '<br>', $str);
	$str = str_replace("\r", '<br>', $str);
	$str = str_replace("\n", '<br>', $str);
	return $str;
}

function tl_work_object_images($connect, $images, $id_content) {
	$directory = dirname(__FILE__)."/../..";
	$img_num = 0;

	foreach ($images as $key => $image) {

		$img_uploaded = $connect->getRow("SELECT * FROM app_models_site_bound WHERE `tl_img_source`=?s", $image['url']);

		if (!$img_uploaded) {

			echo 'copying image...:'.$image['url'].'<br>';
			copy($image['url'], $directory.'/temp/content'.$id_content.'_image'.$key.'.tmp');
			if (file_exists($directory.'/temp/content'.$id_content.'_image'.$key.'.tmp')) {
				$imageRes = multipart_upload($connect, $directory.'/temp/content'.$id_content.'_image'.$key.'.tmp');
				if (is_array($imageRes) && array_key_exists('id',$imageRes) && $imageRes['id'] > 0 && $id_content > 0) {
					$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`,`status`,`uid`,`sort`,`name`,`entity1_type`,`entity1_id`,`entity2_type`,`entity2_id`,`title`,`description`,`tl_img_source`) VALUES (".time().",".time().",1,1,0,'photogallery','content',?i,'file',?i,'','',?s)",$id_content,$imageRes['id'],$image['url']);
					echo $connect->last_query().'<br>';
					echo 'inserting image...<br>';
				}
				unlink($directory.'/temp/content'.$id_content.'_image'.$key.'.tmp');
			}

			$img_num++;
			if ($img_num >= 8) break;

		}
	}	
}

function tl_work_room_images($connect, $images, $room_id) {
	$directory = dirname(__FILE__)."/../..";
	$img_num = 0;

	$connect->query("DELETE FROM `app_models_site_bound` WHERE `entity1_type` = 'room' AND `entity1_id` = $room_id" );

	foreach ($images as $key => $image) {

		copy($image['url'], $directory.'/temp/room'.$room_id.'_image'.$key.'.tmp');
		if (file_exists($directory.'/temp/room'.$room_id.'_image'.$key.'.tmp')) {
			$imageRes = multipart_upload($connect, $directory.'/temp/room'.$room_id.'_image'.$key.'.tmp');
			if (is_array($imageRes) && array_key_exists('id',$imageRes) && $imageRes['id'] > 0 && $room_id > 0) {
				$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`,`status`,`uid`,`sort`,`name`,`entity1_type`,`entity1_id`,`entity2_type`,`entity2_id`,`title`,`description`) VALUES (".time().",".time().",1,1,0,'image','room',?i,'file',?i,'','')", $room_id, $imageRes['id']);
			}
			unlink($directory.'/temp/room'.$room_id.'_image'.$key.'.tmp');
		}

		$img_num++;
		if ($img_num >= 5) break;

	}
}

function tl_work_room_comfort($amenities) {
	$id_comfort = [];
	$id_best_comfort = [];
	foreach ($amenities as $comfort) {
		if ($comfort['code']=='cable_television') $id_comfort[] = '1';
		if ($comfort['code']=='karaoke') $id_comfort[] = '2';
		if ($comfort['code']=='wifi_internet') $id_best_comfort[] = '3';
		if ($comfort['code']=='bathroom_with_wc') $id_comfort[] = '4';
		if ($comfort['code']=='tv' || $comfort['code']=='flat_screen_TV' || $comfort['code']=='two_tv') $id_best_comfort[] = '5';
		if ($comfort['code']=='telephone' || $comfort['code']=='ip_telephone' || $comfort['code']=='two_line_phone') $id_comfort[] = '6';
		if ($comfort['code']=='refrigerator') $id_comfort[] = '7';
		if ($comfort['code']=='kettle') $id_comfort[] = '8';
		if ($comfort['code']=='hairdryer') $id_comfort[] = '9';
		if ($comfort['code']=='mini_kitchen') $id_comfort[] = '10';
		if ($comfort['code']=='sauna') $id_comfort[] = '11';
		if ($comfort['code']=='air_conditioning') $id_best_comfort[] = '12';
		if ($comfort['code']=='double_bed' || $comfort['code']=='king_bed' || $comfort['code']=='queen_bed') $id_comfort[] = '13';
		if ($comfort['code']=='shower_cabin') $id_comfort[] = '14';
		if ($comfort['code']=='single_bed' || $comfort['code']=='two_single_beds') $id_comfort[] = '15';
		if ($comfort['code']=='balcony') $id_comfort[] = '16';
		if ($comfort['code']=='loggia') $id_comfort[] = '17';
		if ($comfort['code']=='sofa_bed' || $comfort['code']=='studio_couch' || $comfort['code']=='folding_sofa'|| $comfort['code']=='non_folding_sofa') $id_comfort[] = '18';
		if ($comfort['code']=='armchair') $id_comfort[] = '19';
		if ($comfort['code']=='folding_armchair') $id_comfort[] = '20';
		if ($comfort['code']=='jacuzzi_bathroom') $id_comfort[] = '21';
		if ($comfort['code']=='journal_table') $id_comfort[] = '22';
		if ($comfort['code']=='radio') $id_comfort[] = '23';
		if ($comfort['code']=='soft_furniture') $id_comfort[] = '24';
		if ($comfort['code']=='microwave') $id_comfort[] = '25';
		if ($comfort['code']=='safe') $id_best_comfort[] = '26';
		if ($comfort['code']=='iron') $id_comfort[] = '27';
		if ($comfort['code']=='closet_for_clothes' || $comfort['code']=='sliding_door_wardrobe') $id_comfort[] = '28';
		if ($comfort['code']=='bidet') $id_comfort[] = '29';
		if ($comfort['code']=='minibar') $id_best_comfort[] = '30';
		if ($comfort['code']=='fan') $id_comfort[] = '31';
		if ($comfort['code']=='hydromassage_bath') $id_comfort[] = '32';
		//if ($comfort['code']=='minibar') $id_comfort[] = '33'; //Бильярд
		if ($comfort['code']=='bathroom	' || $comfort['code']=='bathtub') $id_comfort[] = '34';
		if ($comfort['code']=='satellite_television') $id_comfort[] = '35';
		if ($comfort['code']=='fireplace') $id_comfort[] = '36';
		//if ($comfort['code']=='satellite_television') $id_comfort[] = '37'; //Шифоньер
		if ($comfort['code']=='full_bed' || $comfort['code']=='two_full_beds') $id_comfort[] = '38';
		if ($comfort['code']=='desk') $id_comfort[] = '39';
		//if ($comfort['code']=='satellite_television') $id_comfort[] = '40'; //Спортивные тренажеры
		if ($comfort['code']=='pool' || $comfort['code']=='swimming_pool') $id_comfort[] = '41';
		if ($comfort['code']=='bathrobe' || $comfort['code']=='slippers') $id_comfort[] = '42';
		if ($comfort['code']=='clothes_airer' ||  $comfort['code']=='drying_cabinet') $id_comfort[] = '43';
		//if ($comfort['code']=='desk') $id_comfort[] = '44'; //Швейный набор
		//if ($comfort['code']=='desk') $id_comfort[] = '45'; //Одноразовые средства гигиены
		//if ($comfort['code']=='desk') $id_comfort[] = '46'; //Щетка, лопатка для обуви
		if ($comfort['code']=='shower') $id_comfort[] = '47'; 
		//if ($comfort['code']=='desk') $id_comfort[] = '48'; //Минибар по запросу
		if ($comfort['code']=='coffee_machine') $id_comfort[] = '49';
		if ($comfort['code']=='mini_fridge') $id_comfort[] = '50';
		if ($comfort['code']=='washing_machine') $id_comfort[] = '51';
		if ($comfort['code']=='set_of_dishes' || $comfort['code']=='cookware') $id_comfort[] = '52';
	}
	$id_comfort = array_unique($id_comfort);
	sort($id_comfort);
	$id_comfort = implode('_', $id_comfort).'_';

	$id_best_comfort = array_unique($id_best_comfort);
	sort($id_best_comfort);
	$id_best_comfort = implode('_', $id_best_comfort).'_';

	$res = [];
	$res['id_comfort'] = $id_comfort;
	$res['id_best_comfort'] = $id_best_comfort;
	return $res;
}

function tl_webhook_work($connect) {
	$directory = dirname(__FILE__)."/../..";

	$data = $connect->getRow("SELECT * FROM 1_tl_webhook WHERE `id`=$_POST[id]");
	$webhook = json_decode($data['content_api_data'], true);

	if (!is_array($webhook) || count($webhook)==0) return;

	if ($data['id_obj']==0) {
		//Случай когда создается новый объект по webhook'у

		//Создаем новый объект
		$url_name = mb_strtolower($webhook['name']);
		$url_name = str_replace(' ', '-', $url_name);
		$connect->query("INSERT INTO `object` SET 
			`id`=0, 
			`id_tl`=$webhook[id], 
			`name`='$webhook[name]', 
			`url_name`='$url_name', 
			`url_name_origin`='$url_name', 
			`id_reg`='$data[id_reg]', 
			`region_direction_id`='$data[region_direction_id]',
			`active`=0,
			`check_places`=1,			
			`type`='$data[id_type]',
			`full_name`='$webhook[name]',
			`address`='".$webhook['contactInfo']['address']['addressLine']."',
			`latitude`='".$webhook['contactInfo']['address']['latitude']."',
			`longitude`='".$webhook['contactInfo']['address']['longitude']."',
			`id_account`=0,
			`direction`='$data[id_direction]'
		");
		$last_id = $connect->insertId();

		$directionUrl = $connect->getOne("SELECT `name` FROM `direction_object` WHERE `id_country` = 1 AND `id` = ?i", $data['id_direction']);
		$path = '/'.change_text_url($directionUrl);
		$regionUrl = $connect->getOne("SELECT `name` FROM `region` WHERE `region`.`id_country` = 1 AND `region`.`id` = ?i", $data['id_reg']);
		$path .= '/' . change_text_url($regionUrl);
		if($data['region_direction_id'] && $data['region_direction_id']>0) {
			$regionalDirectionUrl = $connect->getOne("SELECT `name` FROM `direction_object` WHERE (`direction_object`.`id_country` = 0 OR `direction_object`.`id_country` IS NULL)  AND `direction_object`.`id_reg` > 0 AND `direction_object`.`id` = ?i", $data['region_direction_id']);
			$path .= '/'. change_text_url($regionalDirectionUrl);
		}
		$type_name = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $data['id_type']);
		$path .= '/'.change_text_url($type_name) . '-' . $url_name;

		$connect->query("UPDATE `object` SET `path`='$path' WHERE `id`=$last_id");

		$content = $connect->getRow("SELECT * FROM `sites_contents` WHERE `path`='$path'");
		if (!$content) {
			$connect->query("INSERT INTO `sites_contents` 
				SET `id`=0, 
				`status`=1, 
				`created`=".time().", 
				`published`=".time().", 
				`changed`=".time().", 
				`type`='settings', 
				`rss_aggregator_link`='', 
				`site_id`=38, 
				`title`='$webhook[name] - цена %GOD%', 
				`title_h1` = '$webhook[name]', 
				`title_h2` = 'Лечение в $webhook[name]', 
				`summary`='', 
				`snippet_summary`='', 
				`body`='<p>".$webhook['description']."</p>', 
				`body2`='', 
				`path`='$path', 
				`description`='Путевки в $webhook[name] по выгодной цене.', 
				`keywords` = '$webhook[name], бронирование, цены', 
				`breadcrumb_title`='$webhook[name]', 
				`imgs_no_index`=1"
			);
			$id_content = $connect->insertId();

			//загружаем фотографии объекта - максимум 8!
			tl_work_object_images($connect, $webhook['images'], $id_content);
			/*$img_num = 0;
			foreach ($webhook['images'] as $key => $image) {
				echo 'copying image...:'.$image['url'].'<br>';
				copy($image['url'], $directory.'/temp/content'.$id_content.'_image'.$key.'.tmp');
				if (file_exists($directory.'/temp/content'.$id_content.'_image'.$key.'.tmp')) {
					$imageRes = multipart_upload($connect, $directory.'/temp/content'.$id_content.'_image'.$key.'.tmp');
					if (is_array($imageRes) && array_key_exists('id',$imageRes) && $imageRes['id'] > 0 && $id_content > 0) {
						$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`,`status`,`uid`,`sort`,`name`,`entity1_type`,`entity1_id`,`entity2_type`,`entity2_id`,`title`,`description`) VALUES (".time().",".time().",1,1,0,'photogallery','content',?i,'file',?i,'','')",$id_content,$imageRes['id']);
						echo 'inserting image...<br>';
					}
					unlink($directory.'/temp/content'.$id_content.'_image'.$key.'.tmp');
				}
				$img_num++;
				if ($img_num >= 8) break;
			}*/

			sync_site_content($connect, $id_content);

			sync_bounds($connect,[
				'type' => 'content',
				'id' => $id_content
			]);
			echo 'sync_site_content runned...<br>';
		} else {
			echo 'content exits.. path='.$content['path'].'<br>';
		}
		//Создаем материал

		$data['id_obj'] = $last_id; //ПРИСВОИТЬ АЙДИШНИК НОВОГО ОБЪЕКТА ДЛЯ СОЗДАНЯИ ТАРИФОВ И ОСТАЛЬНОГО!!
		$connect->query("UPDATE `1_tl_webhook` SET `id_obj`=$last_id WHERE id=$_POST[id]");
	} else {
		//Случай когда информация из webhook добавляется к новому объекту
		$connect->query("UPDATE `object` SET `active`=0, `synchronized`=0, `id_tl`=$webhook[id], check_places=1 WHERE id=$data[id_obj]");

		//Удаляем корпуса объекта! - не будем удалять, НО: в новых номерах не будет указывать housing!
		//$connect->query("DELETE FROM `housing` WHERE id_obj=$data[id_obj]");

		//Деактивируем тарифы объекта!
		$connect->query("UPDATE `rate_plan` SET `status`=0, `synchronized`=0 WHERE object=$data[id_obj]");
		//$connect->query("DELETE FROM `rate_plan` WHERE object=$data[id_obj]");
		//!! ДВЕ СТРОКИ ВЫШЕ ПОМЕНЯТЬ МЕСТАМИ!!!

		//Деактивируем номера объекта!
		$connect->query("UPDATE `room` SET `active`=1, `synchronized`=0 WHERE id_obj=$data[id_obj]");
		//$connect->query("DELETE FROM `room` WHERE id_obj=$data[id_obj]");
		//!! ДВЕ СТРОКИ ВЫШЕ ПОМЕНЯТЬ МЕСТАМИ!!!

		//Деактивируем детские размещения объекта!
		$connect->query("UPDATE `child_occupancy` SET `status`=0, `synchronized`=0 WHERE id_obj=$data[id_obj]");

		//connect обычные размещения объекта!
		$connect->query("DELETE `place` SET `status`=0, `synchronized`=0 WHERE id_obj=$data[id_obj]");
	}
	//Создаем тарифы из webhook
	foreach ($webhook['ratePlans'] as $rate) {
		$rate['name'] = strip_tags($rate['name']);
		$rate['description'] = AddBR(strip_tags($rate['description']));
		$connect->query("INSERT INTO `rate_plan` SET `id`=0, `id_tl`=?i, `object`=?i, `name`=?s, `description`=?s", $rate['id'], $data['id_obj'], $rate['name'], $rate['description']);
	}

	//Создаем детские размещения из webhook
	$childs = [];
	foreach ($webhook['roomTypes'] as $room) {
		foreach ($room['placements'] as $place) {
			if (isset($place['minAge']) && isset($place['maxAge'])) {
				$current = $place['minAge'].'-'.$place['maxAge'];
				if (!in_array($current, $childs)) $childs[] = $current;
			}
		}
	}
	$childs_ids = [];
	if (count($childs)>0) {
		foreach ($childs as $child) {
			$ages = explode('-', $child);
			if (count($ages)==2) {
				$connect->query("INSERT INTO `child_occupancy` SET `id`=0, `status`=1, `id_obj`=?i, `age_from`=?i, `age_to`=?i", $data['id_obj'], $ages[0], $ages[1]);
				$child_id = $connect->insertId();
				if (!isset($childs_ids[$child])) $childs_ids[$child] = $child_id;
			}
		}
	}

	//Создаем номера и размещения из webhook
	foreach ($webhook['roomTypes'] as $room) {

		$room['name'] = strip_tags($room['name']);
		$room['description'] = AddBR(strip_tags($room['description']));		

		//Обрабатываем оснащение номеров
		/*$id_comfort = [];
		$id_best_comfort = [];
		foreach ($room['amenities'] as $comfort) {
			if ($comfort['code']=='cable_television') $id_comfort[] = '1';
			if ($comfort['code']=='karaoke') $id_comfort[] = '2';
			if ($comfort['code']=='wifi_internet') $id_best_comfort[] = '3';
			if ($comfort['code']=='bathroom_with_wc') $id_comfort[] = '4';
			if ($comfort['code']=='tv' || $comfort['code']=='flat_screen_TV' || $comfort['code']=='two_tv') $id_best_comfort[] = '5';
			if ($comfort['code']=='telephone' || $comfort['code']=='ip_telephone' || $comfort['code']=='two_line_phone') $id_comfort[] = '6';
			if ($comfort['code']=='refrigerator') $id_comfort[] = '7';
			if ($comfort['code']=='kettle') $id_comfort[] = '8';
			if ($comfort['code']=='hairdryer') $id_comfort[] = '9';
			if ($comfort['code']=='mini_kitchen') $id_comfort[] = '10';
			if ($comfort['code']=='sauna') $id_comfort[] = '11';
			if ($comfort['code']=='air_conditioning') $id_best_comfort[] = '12';
			if ($comfort['code']=='double_bed' || $comfort['code']=='king_bed' || $comfort['code']=='queen_bed') $id_comfort[] = '13';
			if ($comfort['code']=='shower_cabin') $id_comfort[] = '14';
			if ($comfort['code']=='single_bed' || $comfort['code']=='two_single_beds') $id_comfort[] = '15';
			if ($comfort['code']=='balcony') $id_comfort[] = '16';
			if ($comfort['code']=='loggia') $id_comfort[] = '17';
			if ($comfort['code']=='sofa_bed' || $comfort['code']=='studio_couch' || $comfort['code']=='folding_sofa'|| $comfort['code']=='non_folding_sofa') $id_comfort[] = '18';
			if ($comfort['code']=='armchair') $id_comfort[] = '19';
			if ($comfort['code']=='folding_armchair') $id_comfort[] = '20';
			if ($comfort['code']=='jacuzzi_bathroom') $id_comfort[] = '21';
			if ($comfort['code']=='journal_table') $id_comfort[] = '22';
			if ($comfort['code']=='radio') $id_comfort[] = '23';
			if ($comfort['code']=='soft_furniture') $id_comfort[] = '24';
			if ($comfort['code']=='microwave') $id_comfort[] = '25';
			if ($comfort['code']=='safe') $id_best_comfort[] = '26';
			if ($comfort['code']=='iron') $id_comfort[] = '27';
			if ($comfort['code']=='closet_for_clothes' || $comfort['code']=='sliding_door_wardrobe') $id_comfort[] = '28';
			if ($comfort['code']=='bidet') $id_comfort[] = '29';
			if ($comfort['code']=='minibar') $id_best_comfort[] = '30';
			if ($comfort['code']=='fan') $id_comfort[] = '31';
			if ($comfort['code']=='hydromassage_bath') $id_comfort[] = '32';
			//if ($comfort['code']=='minibar') $id_comfort[] = '33'; //Бильярд
			if ($comfort['code']=='bathroom	' || $comfort['code']=='bathtub') $id_comfort[] = '34';
			if ($comfort['code']=='satellite_television') $id_comfort[] = '35';
			if ($comfort['code']=='fireplace') $id_comfort[] = '36';
			//if ($comfort['code']=='satellite_television') $id_comfort[] = '37'; //Шифоньер
			if ($comfort['code']=='full_bed' || $comfort['code']=='two_full_beds') $id_comfort[] = '38';
			if ($comfort['code']=='desk') $id_comfort[] = '39';
			//if ($comfort['code']=='satellite_television') $id_comfort[] = '40'; //Спортивные тренажеры
			if ($comfort['code']=='pool' || $comfort['code']=='swimming_pool') $id_comfort[] = '41';
			if ($comfort['code']=='bathrobe' || $comfort['code']=='slippers') $id_comfort[] = '42';
			if ($comfort['code']=='clothes_airer' ||  $comfort['code']=='drying_cabinet') $id_comfort[] = '43';
			//if ($comfort['code']=='desk') $id_comfort[] = '44'; //Швейный набор
			//if ($comfort['code']=='desk') $id_comfort[] = '45'; //Одноразовые средства гигиены
			//if ($comfort['code']=='desk') $id_comfort[] = '46'; //Щетка, лопатка для обуви
			if ($comfort['code']=='shower') $id_comfort[] = '47'; 
			//if ($comfort['code']=='desk') $id_comfort[] = '48'; //Минибар по запросу
			if ($comfort['code']=='coffee_machine') $id_comfort[] = '49';
			if ($comfort['code']=='mini_fridge') $id_comfort[] = '50';
			if ($comfort['code']=='washing_machine') $id_comfort[] = '51';
			if ($comfort['code']=='set_of_dishes' || $comfort['code']=='cookware') $id_comfort[] = '52';
		}
		$id_comfort = array_unique($id_comfort);
		sort($id_comfort);
		$id_comfort = implode('_', $id_comfort).'_';

		$id_best_comfort = array_unique($id_best_comfort);
		sort($id_best_comfort);
		$id_best_comfort = implode('_', $id_best_comfort).'_';*/

		$room_comforts = tl_work_room_comfort($room['amenities']);

		$connect->query("INSERT INTO `room` SET `id`=0, `id_tl`=?i, `active`=0, `id_obj`=?i, `name`=?s, `description`=?s, `main_place`=?i, `add_place`=?i, `wo_bed_place`=?i, `square`=?s, `id_comfort`=?s, `id_best_comfort`=?s", $room['id'], $data['id_obj'], $room['name'], $room['description'], $room['occupancy']['adultBed'], $room['occupancy']['extraBed'], $room['occupancy']['childWithoutBed'], $room['size']['value'], $room_comforts['id_comfort'], $room_comforts['id_best_comfort']);
		$room_id = $connect->insertId();

		//загружаем фотографии номера - максимум 4 на номер!
		tl_work_room_images($connect, $room['images'], $room_id);
		//tl_work_room_images - С УДАЛЕНИЕМ  ИМЕЮЩИХСЯ ЗАПИСЕЙ В app_models_site_bound с entity1_id=id_room ВСЕГДА!
		/*$img_num = 0;
		foreach ($room['images'] as $key => $image) {
			copy($image['url'], $directory.'/temp/room'.$room_id.'_image'.$key.'.tmp');
			if (file_exists($directory.'/temp/room'.$room_id.'_image'.$key.'.tmp')) {
				$imageRes = multipart_upload($connect, $directory.'/temp/room'.$room_id.'_image'.$key.'.tmp');
				if (is_array($imageRes) && array_key_exists('id',$imageRes) && $imageRes['id'] > 0 && $room_id > 0) {
					$connect->query("INSERT INTO `app_models_site_bound` (`created`, `changed`,`status`,`uid`,`sort`,`name`,`entity1_type`,`entity1_id`,`entity2_type`,`entity2_id`,`title`,`description`) VALUES (".time().",".time().",1,1,0,'image','room',?i,'file',?i,'','')",$room_id,$imageRes['id']);
				}
				unlink($directory.'/temp/room'.$room_id.'_image'.$key.'.tmp');
			}
			$img_num++;
			if ($img_num >= 4) break;
		}*/

		foreach ($room['placements'] as $place) {
			if ($place['kind']=='Adult' && $place['count']>0) {
				//Создаем осн.взр.размещения
				$occu = [];
				$occu['adult_on_main_place'] = $place['count'];
				$export_id = get_place_export_id($room_id, $occu);
				$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." взр. на осн.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `adult_on_main_place`=?i", $export_id, $data['id_obj'], $room_id, $place['count']);
			}
			if ($place['kind']=='ExtraAdult' && $place['count']>0) {
				//Создаем доп.взр.размещения
				$place['count'] = 1;
				$occu = [];
				$occu['adult_on_add_place'] = $place['count'];
				$export_id = get_place_export_id($room_id, $occu);
				$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." взр. на доп.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `adult_on_add_place`=?i", $export_id, $data['id_obj'], $room_id, $place['count']);
			}
			if ($place['kind']=='Child' && $place['count']>0 && isset($place['minAge']) && isset($place['maxAge']) && isset($childs_ids[$place['minAge'].'-'.$place['maxAge']])) {
				//Создаем осн.дет.размещения
				$place['count'] = 1;
				$occu = [];
				$occu['id_child_on_main_place'] = $childs_ids[$place['minAge'].'-'.$place['maxAge']];
				$occu['child_on_main_place'] = $place['count'];
				$export_id = get_place_export_id($room_id, $occu);
				$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." реб. (".$place['minAge'].'-'.$place['maxAge']." лет) на осн.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `id_child_on_main_place`=?i, `child_on_main_place`=?i", $export_id, $data['id_obj'], $room_id, $childs_ids[$place['minAge'].'-'.$place['maxAge']], $place['count']);
			}
			if ($place['kind']=='ExtraChild' && $place['count']>0 && isset($place['minAge']) && isset($place['maxAge']) && isset($childs_ids[$place['minAge'].'-'.$place['maxAge']])) {
				//Создаем доп.дет.размещения
				$place['count'] = 1;
				$occu = [];
				$occu['id_child_on_add_place'] = $childs_ids[$place['minAge'].'-'.$place['maxAge']];
				$occu['child_on_add_place'] = $place['count'];
				$export_id = get_place_export_id($room_id, $occu);
				$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." реб. (".$place['minAge'].'-'.$place['maxAge']." лет) на доп.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `id_child_on_add_place`=?i, `child_on_add_place`=?i", $export_id, $data['id_obj'], $room_id, $childs_ids[$place['minAge'].'-'.$place['maxAge']], $place['count']);
			}			
			if ($place['kind']=='ChildBandWithoutBed' && $place['count']>0 && isset($place['minAge']) && isset($place['maxAge']) && isset($childs_ids[$place['minAge'].'-'.$place['maxAge']])) {
				//Создаем дет.размещения без места
				$place['count'] = 1;
				$occu = [];
				$occu['id_child_no_place'] = $childs_ids[$place['minAge'].'-'.$place['maxAge']];
				$occu['child_no_place'] = $place['count'];
				$export_id = get_place_export_id($room_id, $occu);
				$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." реб. (".$place['minAge'].'-'.$place['maxAge']." лет) без места', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `id_child_no_place`=?i, `child_no_place`=?i", $export_id, $data['id_obj'], $room_id, $childs_ids[$place['minAge'].'-'.$place['maxAge']], $place['count']);
			}
		}

	}

	//Запускаем синхрон на сайт обновленных данных
	sync_objects_api($connect);
	echo 'sync_objects_api runned...<br>';

	// !!!!ОТКРЫТЬ В КОНЦЕ!!! 
	$connect->query("UPDATE `1_tl_webhook` SET `worked`=2 WHERE id=$_POST[id]");
}

function tl_webhook_work_modified($connect, $id) {

	$data = $connect->getRow("SELECT * FROM 1_tl_webhook WHERE `id`=$id");
	$webhook = json_decode($data['content_api_data'], true);

	if (!is_array($webhook) || count($webhook)==0) {
		$connect->query("UPDATE `1_tl_webhook` SET `worked`=3, `id_obj`=?i WHERE id=$id", $data['id_obj']);
		return;
	}

	if ($webhook['id']==13409) {
		//отключаем обработки properymodified апи для долины алтая
		$connect->query("UPDATE `1_tl_webhook` SET `worked`=2 WHERE id=$id");
		return;
	}

	if ($webhook['id']==1024) {
		//отключаем обработки properymodified апи для долины алтая
		$connect->query("UPDATE `1_tl_webhook` SET `worked`=2 WHERE id=$id");
		return;
	}	

	$object = $connect->getRow("SELECT * FROM object WHERE `id_tl`=$webhook[id]");

	if ($object && isset($object['id'])) {

		echo 'object found id='.$object['id'].'<br>';

		$data['id_obj'] = $object['id'];		

		$content = $connect->getRow("SELECT * FROM `sites_contents` WHERE `path`='$object[path]'");
		if ($content && isset($content['id'])) {
			$id_content = $content['id'];

			//обновляем фотографии объекта
			tl_work_object_images($connect, $webhook['images'], $id_content);

			sync_bounds($connect,[
				'type' => 'content',
				'id' => $id_content
			]);
		}

		//Деактивируем тарифы объекта!
		$connect->query("UPDATE `rate_plan` SET `status`=0 WHERE `object`=$data[id_obj]");

		//Обработка тарифов из webhook
		foreach ($webhook['ratePlans'] as $rate) {
			echo 'work rate id='.$rate['id'].'<br>';
			$rate['name'] = strip_tags($rate['name']);
			$rate['description'] = AddBR(strip_tags($rate['description']));
			$rate_existing = $connect->getRow("SELECT * FROM rate_plan WHERE `id_tl`=$rate[id]");
			if ($rate_existing && isset($rate_existing['id'])) {
				echo 'rate tl_id='.$rate['id'].' exists<br>';
				$connect->query("UPDATE `rate_plan` SET `status`=1, `name`=?s, `description`=?s WHERE id=$rate_existing[id]", $rate['name'], $rate['description']);
			} else {
				echo 'rate Id='.$rate['id'].' NOT exists<br>';
				$connect->query("INSERT INTO `rate_plan` SET `id`=0, `id_tl`=?i, `object`=?i, `name`=?s, `description`=?s", $rate['id'], $data['id_obj'], $rate['name'], $rate['description']);
			}
		}	
		//Помечаем все тарифы объекта как НЕ синхронизированные
		$connect->query("UPDATE `rate_plan` SET `synchronized`=0 WHERE `object`=$data[id_obj]");



		//Создаем детские размещения из webhook
		$childs = [];
		foreach ($webhook['roomTypes'] as $room) {
			foreach ($room['placements'] as $place) {
				if (isset($place['minAge']) && isset($place['maxAge'])) {
					$current = $place['minAge'].'-'.$place['maxAge'];
					if (!in_array($current, $childs)) $childs[] = $current;
				}
			}
		}
		//Деактивируем детские размещения объекта!
		$connect->query("UPDATE `child_occupancy` SET `status`=0 WHERE `id_obj`=$data[id_obj]");
		//Обработка детских размещений объекта!
		$childs_ids = [];
		if (count($childs)>0) {
			foreach ($childs as $child) {

				$ages = explode('-', $child);

				if (count($ages)==2) {

					$child_occu_existing = $connect->getRow("SELECT * FROM child_occupancy WHERE `id_obj`=?i AND `age_from`=?i AND `age_to`=?i", $data['id_obj'], $ages[0], $ages[1]);

					if ($child_occu_existing && isset($child_occu_existing['id'])) {
						echo 'child occu'.$ages[0].'-'.$ages[1].' exists<br>';
						$child_id = $child_occu_existing['id'];
						$connect->query("UPDATE `child_occupancy` SET `status`=1 WHERE id=$child_occu_existing[id]");
					} else {
						echo 'child occu'.$ages[0].'-'.$ages[1].' NOT exists<br>';
						$connect->query("INSERT INTO `child_occupancy` SET `id`=0, `status`=1, `id_obj`=?i, `age_from`=?i, `age_to`=?i", $data['id_obj'], $ages[0], $ages[1]);
						$child_id = $connect->insertId();
					}		

					if (!isset($childs_ids[$child])) $childs_ids[$child] = $child_id;
				}
			}
		}	
		//Помечаем все детские размещения объекта как НЕ синхронизированные
		$connect->query("UPDATE `child_occupancy` SET `synchronized`=0 WHERE `id_obj`=$data[id_obj]");


		//Деактивируем размещения объекта!
		$connect->query("UPDATE `place` SET `status`=0 WHERE `id_obj`=$data[id_obj]");		
		//Деактивируем все номера объекта!
		//0 - номер активен
		//1 - номер неактивен!
		$connect->query("UPDATE `room` SET `active`=1 WHERE `id_obj`=$data[id_obj]");
		//Обработка номеров из webhook
		foreach ($webhook['roomTypes'] as $room) {
			echo 'work room id='.$room['id'].'<br>';
			$room['name'] = strip_tags($room['name']);
			$room['description'] = AddBR(strip_tags($room['description']));
			$room_comforts = tl_work_room_comfort($room['amenities']);

			$room_existing = $connect->getRow("SELECT * FROM room WHERE `id_tl`=$room[id]");
			if ($room_existing && isset($room_existing['id'])) {
				echo 'room exists<br>';
				$room_comforts = tl_work_room_comfort($room['amenities']);
				//$connect->query("INSERT INTO `room` SET `id`=0, `id_tl`=?i, `active`=0, `id_obj`=?i, `name`=?s, `description`=?s, `main_place`=?i, `add_place`=?i, `wo_bed_place`=?i, `square`=?s, `id_comfort`=?s, `id_best_comfort`=?s", $room['id'], $data['id_obj'], $room['name'], $room['description'], $room['occupancy']['adultBed'], $room['occupancy']['extraBed'], $room['occupancy']['childWithoutBed'], $room['size']['value'], $room_comforts['id_comfort'], $room_comforts['id_best_comfort']);
				$connect->query("UPDATE `room` SET `active`=0, `name`=?s, `description`=?s, `main_place`=?i, `add_place`=?i, `wo_bed_place`=?i, `square`=?s, `id_comfort`=?s, `id_best_comfort`=?s WHERE id=$room_existing[id]", $room['name'], $room['description'], $room['occupancy']['adultBed'], $room['occupancy']['extraBed'], $room['occupancy']['childWithoutBed'], $room['size']['value'], $room_comforts['id_comfort'], $room_comforts['id_best_comfort']);
				$room_id = $room_existing['id'];
			} else {
				echo 'room Id='.$room['id'].' NOT exists<br>';
				$connect->query("INSERT INTO `room` SET `id`=0, `id_tl`=?i, `active`=0, `id_obj`=?i, `name`=?s, `description`=?s, `main_place`=?i, `add_place`=?i, `wo_bed_place`=?i, `square`=?s, `id_comfort`=?s, `id_best_comfort`=?s", $room['id'], $data['id_obj'], $room['name'], $room['description'], $room['occupancy']['adultBed'], $room['occupancy']['extraBed'], $room['occupancy']['childWithoutBed'], $room['size']['value'], $room_comforts['id_comfort'], $room_comforts['id_best_comfort']);
				$room_id = $connect->insertId();
			}
			//обновление фотографий номера
			tl_work_room_images($connect, $room['images'], $room_id);

			//Обновление размещений номера
			foreach ($room['placements'] as $place) {
				if ($place['kind']=='Adult' && $place['count']>0) {
					//Обрабатываем осн.взр.размещения
					$occu = [];
					$occu['adult_on_main_place'] = $place['count'];
					$export_id = get_place_export_id($room_id, $occu);

					$place_existing = $connect->getRow("SELECT * FROM place WHERE `id_obj`=?i AND `id_room`=?i AND `export_id`=?s", $data['id_obj'], $room_id, $export_id);
					echo $connect->last_query().'<br>';
					if ($place_existing && isset($place_existing['id'])) {
						$connect->query("UPDATE `place` SET `status`=1 WHERE id=$place_existing[id]");
						echo $connect->last_query().'<br>';
					} else {
						$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." взр. на осн.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `adult_on_main_place`=?i", $export_id, $data['id_obj'], $room_id, $place['count']);
						echo $connect->last_query().'<br>';
					}
				}
				if ($place['kind']=='ExtraAdult' && $place['count']>0) {
					//Обрабатываем доп.взр.размещения
					$place['count'] = 1;
					$occu = [];
					$occu['adult_on_add_place'] = $place['count'];
					$export_id = get_place_export_id($room_id, $occu);

					$place_existing = $connect->getRow("SELECT * FROM place WHERE `id_obj`=?i AND `id_room`=?i AND `export_id`=?s", $data['id_obj'], $room_id, $export_id);
					if ($place_existing && isset($place_existing['id'])) {
						$connect->query("UPDATE `place` SET `status`=1 WHERE id=$place_existing[id]");
					} else {
						$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." взр. на доп.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `adult_on_add_place`=?i", $export_id, $data['id_obj'], $room_id, $place['count']);
					}
				}
				if ($place['kind']=='Child' && $place['count']>0 && isset($place['minAge']) && isset($place['maxAge']) && isset($childs_ids[$place['minAge'].'-'.$place['maxAge']])) {
					//Обрабатываем осн.дет.размещения
					$place['count'] = 1;
					$occu = [];
					$occu['id_child_on_main_place'] = $childs_ids[$place['minAge'].'-'.$place['maxAge']];
					$occu['child_on_main_place'] = $place['count'];
					$export_id = get_place_export_id($room_id, $occu);
					$place_existing = $connect->getRow("SELECT * FROM place WHERE `id_obj`=?i AND `id_room`=?i AND `export_id`=?s", $data['id_obj'], $room_id, $export_id);
					if ($place_existing && isset($place_existing['id'])) {
						$connect->query("UPDATE `place` SET `status`=1 WHERE id=$place_existing[id]");
					} else {
						$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." реб. (".$place['minAge'].'-'.$place['maxAge']." лет) на осн.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `id_child_on_main_place`=?i, `child_on_main_place`=?i", $export_id, $data['id_obj'], $room_id, $childs_ids[$place['minAge'].'-'.$place['maxAge']], $place['count']);
					}
				}
				if ($place['kind']=='ExtraChild' && $place['count']>0 && isset($place['minAge']) && isset($place['maxAge']) && isset($childs_ids[$place['minAge'].'-'.$place['maxAge']])) {
					//Обрабатываем доп.дет.размещения
					$place['count'] = 1;
					$occu = [];
					$occu['id_child_on_add_place'] = $childs_ids[$place['minAge'].'-'.$place['maxAge']];
					$occu['child_on_add_place'] = $place['count'];
					$export_id = get_place_export_id($room_id, $occu);
					$place_existing = $connect->getRow("SELECT * FROM place WHERE `id_obj`=?i AND `id_room`=?i AND `export_id`=?s", $data['id_obj'], $room_id, $export_id);
					if ($place_existing && isset($place_existing['id'])) {
						$connect->query("UPDATE `place` SET `status`=1 WHERE id=$place_existing[id]");
					} else {
						$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." реб. (".$place['minAge'].'-'.$place['maxAge']." лет) на доп.месте', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `id_child_on_add_place`=?i, `child_on_add_place`=?i", $export_id, $data['id_obj'], $room_id, $childs_ids[$place['minAge'].'-'.$place['maxAge']], $place['count']);
					}
				}			
				if ($place['kind']=='ChildBandWithoutBed' && $place['count']>0 && isset($place['minAge']) && isset($place['maxAge']) && isset($childs_ids[$place['minAge'].'-'.$place['maxAge']])) {
					//Обрабатываем дет.размещения без места
					$place['count'] = 1;
					$occu = [];
					$occu['id_child_no_place'] = $childs_ids[$place['minAge'].'-'.$place['maxAge']];
					$occu['child_no_place'] = $place['count'];
					$export_id = get_place_export_id($room_id, $occu);
					$place_existing = $connect->getRow("SELECT * FROM place WHERE `id_obj`=?i AND `id_room`=?i AND `export_id`=?s", $data['id_obj'], $room_id, $export_id);
					if ($place_existing && isset($place_existing['id'])) {
						$connect->query("UPDATE `place` SET `status`=1 WHERE id=$place_existing[id]");
					} else {
						$connect->query("INSERT INTO `place` SET `id`=0, `status`=1, `name`='".$place['count']." реб. (".$place['minAge'].'-'.$place['maxAge']." лет) без места', `export_id`=?s, `id_obj`=?i, `id_room`=?i, `type`=1, `id_child_no_place`=?i, `child_no_place`=?i", $export_id, $data['id_obj'], $room_id, $childs_ids[$place['minAge'].'-'.$place['maxAge']], $place['count']);
					}
				}
			}//foreach ($room['placements'] as $place) {
		}	
		//Помечаем все номера объекта как НЕ синхронизированные
		$connect->query("UPDATE `room` SET `synchronized`=0 WHERE `id_obj`=$data[id_obj]");		
		//Помечаем все размещения объекта как НЕ синхронизированные
		$connect->query("UPDATE `place` SET `synchronized`=0 WHERE `id_obj`=$data[id_obj]");		
		//запускаем синхрон!
		sync_objects_api($connect);
	}

	$connect->query("UPDATE `1_tl_webhook` SET `worked`=2, `id_obj`=?i WHERE id=$id", $data['id_obj']);
}

function tl_webhook_save_params($connect) {
	$id_type = (int)$_POST['id_type'];
	$id_direction = (int)$_POST['id_direction'];
	$id_reg = (int)$_POST['id_reg'];
	$region_direction_id = (int)$_POST['region_direction_id'];

	$connect->query("UPDATE `1_tl_webhook` SET `id_type`=$id_type, `id_direction`=$id_direction, `id_reg`=$id_reg, `region_direction_id`=$region_direction_id  WHERE id=$_POST[id]");
}

function tl_webhook_del_obj($connect) {
	$connect->query("UPDATE `1_tl_webhook` SET `id_obj`=0 WHERE id=$_POST[id]");
}

function set_object_for_webhook($connect) {
	$connect->query("UPDATE `1_tl_webhook` SET `id_obj`=$_POST[id_obj] WHERE id=$_POST[id_webhook]");
}

function tl_webhook_del($connect) {
	$connect->query("DELETE FROM `1_tl_webhook` WHERE id=$_POST[id]");
}

function tl_webhook($connect) {

	$id = $_POST['id'];

	$item = $connect->getRow("SELECT * FROM 1_tl_webhook WHERE `id`=$id");
	$worked = $item['worked'];
	$id_type = $item['id_type'];
	$id_direction = $item['id_direction'];
	$id_reg = $item['id_reg'];
	$region_direction_id = $item['region_direction_id'];
	$id_obj = $item['id_obj'];
	$item = json_decode($item['content_api_data'], true);	

	$regions = [];
	if($id_direction) {
	    $regions = $connect->getAll("SELECT `id`, `name` FROM `region` WHERE `id_direction` = ?i", $id_direction);
    }	

	$region_directions = [];
	if($id_reg) {
      $region_directions = $connect->getAll("SELECT `id`, `name` FROM `direction_object` WHERE `id_reg` = ?i", $id_reg);
    }	
	
	if ($id_obj>0) $object = $connect->getRow("SELECT * FROM object WHERE `id`=$id_obj");

	$html = '
			<div id="id_webhook" class="hidden">'.$id.'</div>
			<div class="form-horizontal panel panel-default">
				<div class="panel-heading">
					Просмотр запроса #'.$id.'
				</div>	
				<div class="list-group">
					<div class="list-group-item form-group " style="margin: 0">
						<div class="col-sm-12">
			';

	//$html .= '<pre>'.print_r($item, true).'</pre>';
	if ($worked==1) {
		if ($id_obj==0) {
			$html .= '<input type="text" id="find_object_for_webhook" class="form-control" placeholder="выберите объекта из имеющихся для занесения информации из запроса" onkeyup="find_klient(event, \'find_object_for_webhook\', \'object\', \'set_object_for_webhook\')"><br><br>';
			$html .= '<div class="edit-object">';
			$html .= '
				<div class="form-group">
					<label class="col-sm-3 control-label">Тип объекта</label>
					<div class="col-sm-9">
						'.get_select_table($connect, "type_object", "", $id_type, "type_object", 1, "").'
					</div>
				</div>';
			$html .= '
				<div class="form-group">
					<label class="col-sm-3 control-label">Направление</label>
					<div class="col-sm-9">
						'.get_select_table($connect, "direction_object", "(`id_reg` IS NULL OR `id_reg` = 0) AND `id_country` = 1", $id_direction, "direction-object", 1, "").'
					</div>
				</div>';
			$html .= '
        		<div class="form-group '; if(!$id_direction) { $html .= 'hidden'; } $html .= '">
            		<label class="col-sm-3 control-label">Регион</label>
					<div class="col-sm-9">
						<select class="form-control" id="object_region">
							<option value="0" >Не выбран</option>';
							foreach ($regions as $region) {
								if ($id_reg == $region['id']) $sel = 'selected'; else  $sel = '';
								$html .= '<option value="'.$region['id'].'" '.$sel.'>'.$region['name'].'</option>';
							}
						$html .= ' </select>
					</div>
				</div>';
			$html .= '
        		<div class="form-group '; if(!$id_reg || count($region_directions) === 0) { $html .= 'hidden'; } $html .= '">
					<label class="col-sm-3 control-label">Региональное направление</label>
					<div class="col-sm-9">
						<select class="form-control" id="region_direction_id">
							<option value="0">Не выбрано</option>';
							foreach ($region_directions as $region_direction) {
								if ($region_direction_id == $region_direction['id']) $sel = 'selected'; else  $sel = '';
								$html .= '<option value="'.$region_direction['id'].'" '.$sel.'>'.$region_direction['name'].'</option>';
							}
						$html .= ' </select>
					</div>
				</div>';
			$html .= '</div>';

			$html .= ' <button type="button" class="btn btn-success" onclick="tl_webhook_save_params('.$id.')">Сохранить параметры для создаваемого объекта</button><br><br>';

			if ($id_type!=0 && $id_direction!=0 && $id_reg!=0) {
				$html .= ' <button type="button" class="btn btn-success" onclick="tl_webhook_work('.$id.')">Cоздать новый объект на основе данных из запроса</button><br><br>';
			} 
		} else {
			$html .= '<strong>Присвоенный объект из имеющихся</strong>: '.$object['name'].' ('.$object['address'].')<br><br>';
			$html .= ' <button type="button" class="btn btn-danger" onclick="tl_webhook_del_obj('.$id.')">удалить связку с присвоенным объектом</button>';;
			$html .= ' <button style="margin-left: 30px;" type="button" class="btn btn-success" onclick="tl_webhook_work('.$id.')">перенести данные из запроса в объект</button>';
			$html .= '<br>';
			$html .= '<br>';
		}
	} else {
		$html .= '<strong style="color:green">Данные из запроса были обработаны и добавлены в объект: </strong><br>';
		$html .= $object['name'].' ('.$object['address'].')<br>';
		$html .= '<a href="https://санатории-россии.рф'.$object['path'].'" target="_blank">https://санатории-россии.рф'.$object['path'].'</a><br><br>';
		$html .= '<strong>ID объекта внутренний (санатории-россии.рф)</strong>: '.$object['id'].'<br><br>';
	}
	
	$html .= '<strong>ID объекта внешний (travelline)</strong>: '.$item['id'].'<br><br>';
	$html .= '<strong>Название объекта</strong>: '.$item['name'].'<br>';
	$html .= '<strong>Описание:</strong><br>'.AddBR(strip_tags($item['description'])).'<br><br>';	
	//$html .= '<strong>Фотографии объекта</strong>:<br>';
	$html .= '<br>';
	$img_num = 0;
	foreach ($item['images'] as $image) {
		$html .= '<a href="'.$image['url'].'" target="_blank"><img src="'.$image['url'].'" style="width: 150px; display: inline-block; vertical-align: middle;"><a/> ';
		$img_num++;
		if ($img_num>=15) break;
	}
	$html .= '<br><br>';
	$html .= '<strong>Адрес</strong>: '.$item['contactInfo']['address']['addressLine'].'<br>';
	$html .= '<strong>Координаты</strong>: '.$item['contactInfo']['address']['latitude'].':'.$item['contactInfo']['address']['longitude'].'<br>';
	$html .= '<strong>Телефоны</strong>: ';
	foreach ($item['contactInfo']['phones'] as $phone) {
		$html .= $phone['phoneNumber'].' ('.$phone['techType'].'), ';	
	}
	$html .= '<br>';
	$html .= '<strong>Заезд</strong>: '.$item['policy']['checkInTime'].'<br>';
	$html .= '<strong>Выезд</strong>: '.$item['policy']['checkOutTime'].'<br><br>';
	$html .= '<strong>Тарифные планы</strong>:<br><br>';
	foreach ($item['ratePlans'] as $key => $rate) {
		$html .= ($key + 1).'. <strong>'.$rate['name'].'</strong><br>Описание:  '.strip_tags($rate['description']).'<br><br>';	
	}	
	$html .= '<br>';
	$html .= '<strong>Номера</strong>:<br><br>';
	foreach ($item['roomTypes'] as $room) {
		$html .= '<h3>'.$room['name'].'</h3><br><br>';
		//$html .= '<strong>Фотографии номера</strong>:<br>';
		$img_num = 0;
		foreach ($room['images'] as $image) {
			$html .= '<a href="'.$image['url'].'" target="_blank"><img src="'.$image['url'].'" style="width: 150px; display: inline-block; vertical-align: middle;"><a/> ';
			$img_num++;
			if ($img_num>=15) break;
		}
		$html .= '<br><br>';
		$html .= '<strong>Площадь:</strong> '.$room['size']['value'].'<br>';	
		if (isset($room['occupancy']['adultBed'])) $html .= '<strong>Осн. мест: </strong> '.$room['occupancy']['adultBed'].'<br>';
		if (isset($room['occupancy']['extraBed'])) $html .= '<strong>Доп. мест:</strong>  '.$room['occupancy']['extraBed'].'<br>';
		if (isset($room['occupancy']['childWithoutBed'])) $html .= '<strong>Без места:</strong>  '.$room['occupancy']['childWithoutBed'].'<br>';
		//$html .= '<strong>Описание:</strong><br>'.AddBR(strip_tags($room['description'])).'<br>';	
		$html .= '<strong>Варианты размещений</strong>:<br>';
		foreach ($room['placements'] as $place) {
			if ($place['kind']=='Adult') $html .= '- '.$place['count'].'-местное взрослое<br>';
			if ($place['kind']=='ExtraAdult') $html .= '- взрослый на доп.месте<br>';
			if ($place['kind']=='Child') $html .= '- ребенок ('.$place['minAge'].' - '.$place['maxAge'].' лет) на осн.месте<br>';
			if ($place['kind']=='ExtraChild') $html .= '- ребенок ('.$place['minAge'].' - '.$place['maxAge'].' лет) на доп.месте<br>';
			if ($place['kind']=='ChildBandWithoutBed') $html .= '- ребенок ('.$place['minAge'].' - '.$place['maxAge'].' лет) без места<br>';
		}
		$html .= '<br><br><br>';
		
	}	


	$html .= '</div></div></div></div>';

	return $html;
}

?>