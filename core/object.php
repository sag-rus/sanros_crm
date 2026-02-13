<?php
$array_type = array(1 => "за чел/сутки", 2 => "за дом/сутки", 3 => "за номер/сутки", 4 => "за заезд");


function select_country_admin($connect){
	$id = $_POST["id"];
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-1 control-label">Поиск</label>
			<div class="col-sm-3" id="object_name">
				<input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" />
			</div>
			<div class="col-sm-2">
				<button class="btn btn-success btn-lt" onclick="select_object_admin(true)">Выбрать <i class="fa fa-angle-double-right"></i></button>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-1 control-label">Страна</label>
			<div class="col-sm-3">
				<?php echo get_select_table($connect, "country", "", $id, "country", "", "onchange='select_region(\"\", \"country\")'"); ?>
			</div>
			<div class="col-sm-2">
				<button class="btn btn-success btn-lt" onclick="add_new_country()">&nbsp;<i class="fa fa-plus-circle"></i>&nbsp;</button>
			</div>
			<label class="col-sm-1 control-label">Направление</label>
			<div class="col-sm-3 direction-country"></div>
			<div class="col-sm-2">
				<button class="btn btn-success btn-lt" onclick="add_new_direction('country')">&nbsp;<i class="fa fa-plus-circle" ></i>&nbsp;</button>
				<button class="btn btn-default btn-lt" onclick="edit_direction('country')">&nbsp;<i class="fa fa-pencil" ></i>&nbsp;</button>
				<button class="btn btn-primary btn-lt" onclick="add_image_direction('country')">&nbsp;<i class="fa fa-image" ></i>&nbsp;</button>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-1 control-label">Регион</label>
			<div class="col-sm-3 regions"></div>
			<div class="col-sm-2">
				<button class="btn btn-success btn-lt" onclick="add_new_region()">&nbsp;<i class="fa fa-plus-circle" ></i>&nbsp;</button>
				<button class="btn btn-default btn-lt" onclick="edit_region()">&nbsp;<i class="fa fa-pencil" ></i>&nbsp;</button>
				<button class="btn btn-primary btn-lt" onclick="add_image_region()">&nbsp;<i class="fa fa-image" ></i>&nbsp;</button>
			</div>
			<label class="col-sm-1 control-label">Направление</label>
			<div class="col-sm-3 direction-region"></div>
			<div class="col-sm-2">
				<button class="btn btn-success btn-lt" onclick="add_new_direction('region')">&nbsp;<i class="fa fa-plus-circle" ></i>&nbsp;</button>
				<button class="btn btn-default btn-lt" onclick="edit_direction('region')">&nbsp;<i class="fa fa-pencil" ></i>&nbsp;</button>
				<button class="btn btn-primary btn-lt" onclick="add_image_direction('region')">&nbsp;<i class="fa fa-image" ></i>&nbsp;</button>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-1 control-label">Объект</label>
			<div class="col-sm-3 objects"></div>
			<div class="col-sm-2">
				<button class="btn btn-success btn-lt" onclick="add_new_object()">&nbsp;<i class="fa fa-plus-circle" ></i>&nbsp;</button>
			</div>
		</div>
	</div>
</div>
<div class="object-menu"></div>
<?php
}

function select_region_admin($connect){
	$id = $_POST["id"];
	$country = $_POST["country"];
	$direction = $_POST["direction"];
	if(!$direction)
		$html = get_select_table($connect, "region", "id_country=".$country." ORDER BY name", $id, "region", "", "onchange='select_object()'");
	else
		$html = get_select_table($connect, "region", "id_direction=".$direction." ORDER BY name", $id, "region", "", "onchange='select_object()'");
	return $html;
}

function select_direction_admin($connect){
	$region = $_POST["region"];
	$country = $_POST["country"];
	$type = $_POST["type"];
	if($type == "region")
		$html = get_select_table($connect, "direction_object", "id_reg=".$region, "", "direction-region", 1, "onchange='select_object(\"\", \"direction\", \"region\")'");
	else
		$html = get_select_table($connect, "direction_object", "id_country=".$country, "", "direction-country", 1, "onchange='select_region(\"\", \"direction\", \"country\")'");
	return $html;
}

function select_object_admin($connect){
	$id = $_POST["id"];
	$region = $_POST["region"];
	$direction = $_POST["direction"];
	$type = $_POST["type"];
	$noCheckLocation = isset($_POST['no_check_location'])?(bool)$_POST['no_check_location']:false;
	if($noCheckLocation || (!$direction && !$region)) {
      $html = get_select_table($connect, "object", "ORDER BY name", $id, "object-admin", "", "onchange='select_menu_object()'");
    }
	elseif($direction AND (!$region OR $type == "region"))
		$html = get_select_table($connect, "object", "direction=".$direction." ORDER BY name", $id, "object-admin", "", "onchange='select_menu_object()'");
	else
		$html = get_select_table($connect, "object", "id_reg=".$region." ORDER BY name", $id, "object-admin", "", "onchange='select_menu_object()'");
	return $html;
}

function add_new_country(){
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новую страну</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-country">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Страна</label>
						<div class="col-sm-9">
							<input type="text" class="form-control name-country" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_country()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_country($connect){
	$name = $_POST["name"];
	$connect->query("INSERT INTO country(name) VALUES (?s)", $name);
	return $connect->insertId();
}

function add_new_region($connect){
	$id = $_POST["country"];
	$country = $connect->getOne("SELECT name FROM country WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade new-region-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новый регион</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-region">
					<div class="form-group">
						<label class="col-sm-3 control-label">Страна</label>
						<div class="col-sm-9">
							<div class="well well-sm"><?php echo $country; ?></div>
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Регион</label>
						<div class="col-sm-9">
							<input type="text" class="form-control name-region" />
						</div>
					</div>
                    <div class="form-group form-group-margin">
                        <label class="col-sm-3 control-label">Доп. вознаграждение менеджеру с заявки</label>
                        <div class="col-sm-9">
                            <select class="form-control man_reward_scheme">
                                <option value="0">Нет</option>
                                <option value="1">Да</option>
                            </select>
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_region('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_region($connect){
	$name = $_POST["name"];
	$country = $_POST["country"];
	$man_reward_scheme = (int)$_POST["man_reward_scheme"];
	if($connect->getOne("SELECT id FROM region WHERE name=?s AND id_country=?i", $name, $country))
		return FALSE;
	$connect->query("INSERT INTO region(name, id_country, man_reward_scheme) VALUES (?s, ?i, ?i)", $name, $country, $man_reward_scheme);
	return $connect->insertId();
}

function add_new_direction($connect){
	$type = $_POST["type"];
	if($type == "region"){
		$id = $_POST["region"];
		$name = $connect->getOne("SELECT name FROM region WHERE id=?i", $id);
		$text = "Регион";
	}else{
		$id = $_POST["country"];
		$name = $connect->getOne("SELECT name FROM country WHERE id=?i", $id);
		$text = "Страна";
	}
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новое направление</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-direction">
					<div class="form-group">
						<label class="col-sm-3 control-label"><?php echo $text; ?></label>
						<div class="col-sm-9">
							<div class="well well-sm"><?php echo $name; ?></div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Название</label>
						<div class="col-sm-9">
							<input type="text" class="form-control name-direction" />
						</div>
					</div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Название в родительном падеже</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control name-rod-direction" />
                        </div>
                    </div>
                    <div class="form-group form-group-margin">
                        <label class="col-sm-3 control-label">Вес (сортировка)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control name-sort-direction" value="0" />
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_direction('<?php echo $id; ?>', '<?php echo $type; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_direction($connect){
	$name = trim($_POST["name"]);
	$name_rod = trim($_POST["name_rod"]);
	$sort = (int)$_POST['sort'];
	$id = $_POST["id"];
	$type = $_POST["type"];
	if($type == "region")
		$column = "id_reg";
	else
		$column = "id_country";
	$connect->query("INSERT INTO direction_object(name, ".$column.",name_rod, sort) VALUES (?s, ?i, ?s, ?i)", $name, $id, $name_rod, $sort);
}

function add_new_object($connect){
	$id = $_POST["region"];
	$region = $connect->getOne("SELECT name FROM region WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новый объект</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-object">
					<div class="form-group">
						<label class="col-sm-3 control-label">Регион</label>
						<div class="col-sm-9">
							<div class="well well-sm"><?php echo $region; ?></div>
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Объект</label>
						<div class="col-sm-9">
							<input type="text" class="form-control name-object" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_object_region('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_object_region($connect){
	$name = $_POST["name"];
	$region = $_POST["region"];
	$connect->query("INSERT INTO object(name, id_reg) VALUES (?s, ?i)", $name, $region);
	return $connect->insertId();
}

function select_menu_object($connect){
	global $directory;
	$id = $_POST["id"];
	$noCheckLocation = isset($_POST['no_check_location'])?(bool)$_POST['no_check_location']:FALSE;
	$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $id);
	if(!$noCheckLocation) {
	    if(!$region)
	        return '<div class="alert alert-danger" style="margin-bottom: 10px"><i class="fa fa-info-circle"></i> Объектов не найдено</div>';
    }
	if(!file_exists($directory."/temp/region/".$region.".jpg")){
?>
	<div class="alert alert-danger" style="margin-bottom: 10px"><i class="fa fa-info-circle"></i> Фото региона не добавлено</div>
	<?php }
?>
	<div class="alert alert-info" style="margin-bottom: 10px"><a href="document.php?func=promo_document&region=<?php echo $region; ?>" target="_blank" class="text-info"><i class="fa fa-star"></i> Акции региона</a></div>
	<ul class="nav nav-tabs nav-justified menu-object" style="margin-bottom: 10px;" object="<?php echo $id; ?>">
		<li class="menu-infa" onclick="select_object_about('<?php echo $id; ?>')"><a><i class="fa fa-home"></i> Описание</a></li>
		<li class="menu-image" onclick="select_object_image('<?php echo $id; ?>')"><a><i class="fa fa-image"></i> Фото</a></li>
		<li class="menu-room" onclick="select_object_room()"><a><i class="fa fa-cubes"></i> Номера</a></li>
		<li class="menu-rate-plan" onclick="select_object_rate_plan()"><a><i class="fa fa-star-o"></i> Тарифы</a></li>
		<li class="menu-occupancies" onclick="select_object_occupancies()"><a><i class="fa fa-sitemap"></i> Размещения</a></li>
		<li class="menu-child_occupancies" onclick="select_object_child_occupancies()"><a><i class="fa fa-child"></i> Детские размещения</a></li>
		<li class="menu-housing" onclick="select_object_housing()"><a><i class="fa fa-building-o"></i> Корпуса</a></li>
		<li class="menu-upload" onclick="select_object_upload('<?php echo $id; ?>')"><a><i class="fa fa-cloud-upload"></i> Загрузка</a></li>
		<li class="menu-check-object" onclick="check_completeness_object()"><a><i class="fa fa-battery-three-quarters"></i> Проверка объектов</a></li>
		<li class="menu-object-cert" onclick="show_obj_cert()"><a><i class="fa fa-certificate"></i> Сертификация</a></li>
	</ul>
	<div class="object-infa"></div>
<?php
}

function select_object_about($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM object WHERE id=?i", $id);
	if(!$row["id"])
		return FALSE;
	$address = $connect->getOne("SELECT name FROM region WHERE id=?i", $row["id_reg"]);
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
	if($row["city"])
		$address.= ", ".$row["city"];
	$services = json_decode($row["id_services"], TRUE);
	$class = " panel-success";
	if($row["active"] == 1)
		$class = " panel-default";
	if($row["active"] == 2)
		$class = " panel-danger";
	$image = "temp/defaul.jpg";
	if($row["image"])
		$image = "data:image/jpg;base64,".$row["image"];
	$url = $row["url_name"];
	ob_start();
?>
<div class="form-horizontal panel <?php echo $class; ?>">
	<div class="panel-heading"><i class="fa fa-home"></i> Описание объекта «<?php echo $type." ".$row["name"]; ?>»</div>
	<div class="list-group">
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">ID</label>
				<div class="col-sm-9"><?php echo $id; ?></div>
			</div>
		</div>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Регион</label>
				<div class="col-sm-9"><?php echo $address; ?></div>
			</div>
		</div>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Адрес</label>
				<div class="col-sm-9"><?php echo $row['address']; ?></div>
			</div>
		</div>		
		<?php if($row["website"]){ ?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Официальный сайт</label>
				<div class="col-sm-9"><a href="<?php echo $row['website']; ?>" target="_blank"><?php echo $row["website"]; ?></a></div>
			</div>
		</div>
		<?php } ?>
			<?php if($row["id_profile"]){ ?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Профили лечения</label>
				<div class="col-sm-9"><?php echo parse_index_string($connect, $row["id_profile"], "profile", "_", ", "); ?></div>
			</div>
		</div>
			<?php } ?>
			<?php if($row["id_methods"]){ ?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Методы лечения</label>
				<div class="col-sm-9"><?php echo parse_index_string($connect, $row["id_methods"], "methods", "_", ", "); ?></div>
			</div>
		</div>
			<?php } ?>
			<?php if($row["medical_factors"]){ ?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Лечебные факторы</label>
				<div class="col-sm-9"><?php echo $row["medical_factors"]; ?></div>
			</div>
		</div>
			<?php } ?>
			<?php if($row["id_infa"]){ ?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Инфраструктура</label>
				<div class="col-sm-9"><?php echo parse_index_string($connect, $row["id_infa"], "infa", "_", ", "); ?></div>
			</div>
		</div>
			<?php } ?>
			<?php if($row["similar"]){ ?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Похожие объекты</label>
				<div class="col-sm-9"><?php echo parse_index_string($connect, $row["similar"], "object", "_", ", "); ?></div>
			</div>
		</div>
			<?php } ?>

		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Услуги объекта</label>
				<div class="col-sm-9">
				<?php if(is_array($services) && count($services)){ ?>
					<div class="text-success"><i class="fa fa-check"></i> указаны</div>
				<?php }else{ ?>
					<div class="text-danger"><i class="fa fa-times"></i> не указаны</div>
				<?php } ?>
				</div>
			</div>
		</div>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Координаты</label>
				<div class="col-sm-9">
				<?php if($row["latitude"] != 0){ ?>
					<div class="text-success"><i class="fa fa-check"></i> определены</div>
				<?php }else{ ?>
					<div class="text-danger"><i class="fa fa-times"></i> не определены</div>
				<?php } ?>
				</div>
			</div>
		</div>
        <div class="list-group-item list-hover-item">
            <div class="form-group form-group-margin">
                <label class="col-sm-3 control-label-element">Действие гос. программы</label>
                <div class="col-sm-9">
                 <?php if($row['state_program']) { ?>
                     Да
                 <?php } else { ?>
                     Нет
                 <?php } ?>
                </div>
            </div>
        </div>

        <div class="list-group-item list-hover-item">
            <div class="form-group form-group-margin">
                <label class="col-sm-3 control-label-element">Детский отдых</label>
                <div class="col-sm-9">
                    <?php if($row['children_rest']) { ?>
                        Да
                    <?php } else { ?>
                        Нет
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="list-group-item list-hover-item">
            <div class="form-group form-group-margin">
                <label class="col-sm-3 control-label-element">Разместить в блок "Идёт активное бронирование"</label>
                <div class="col-sm-9">
                    <?php if($row['featured']) { ?>
                        Да
                    <?php } else { ?>
                        Нет
                    <?php } ?>
                </div>
            </div>
        </div>

		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element">Уникальная ссылка</label>
				<div class="col-sm-9">
			<?php if($url){ ?>
					<a href="http://xn----7sba6aaba8akdsdekah.xn--p1ai/объект/<?php echo $url; ?>" class="btn btn-link btn-xs" target="_blank"><i class="fa fa-link"></i> <?php echo $url; ?></a>
			<?php }else{ ?>
					<div class="text-danger"><i class="fa fa-times"></i> не создана
						<button type="button" class="btn btn-link btn-xs" onclick="create_uniq_link_object('<?php echo $id; ?>')"><i class="fa fa-link"></i> Создать ссылку</button>
					</div>
			<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<img src="<?php echo $image; ?>" class="img-head-small pointer" onclick="add_photo_profile(<?php echo $id; ?>, 'object')" />
		<?php if ($row['check_places'] == '1') {?>
			<button type="button" class="btn btn-danger btn-sm" onclick="deleteTLdata(<?php echo $id; ?>)" style="float: left;"><i class="fa fa-close"></i>&nbsp; Удалить интеграцию с TravelLine</button>
		<?php } ?>
		<button type="button" class="btn btn-success btn-sm" onclick="add_new_image_object(<?php echo $id; ?>)"><i class="fa fa-upload"></i> Загрузить фото</button>
		<button type="button" class="btn btn-default btn-sm" onclick="edit_main_data_object(<?php echo $id; ?>)"><i class="fa fa-pencil"></i> Основные данные</button>
		<button type="button" class="btn btn-default btn-sm" onclick="edit_desc_object(<?php echo $id; ?>)"><i class="fa fa-pencil"></i> Лечение и инфраструктура</button>
		<button type="button" class="btn btn-default btn-sm" onclick="edit_services_object(<?php echo $id; ?>)"><i class="fa fa-pencil"></i> Услуги</button>
		<div class="btn-group">
		<?php if($row["active"] == 1){ ?>
			<button type="button" class="btn btn-success btn-sm" onclick="object_check_archive(<?php echo $id; ?>, 0)">&nbsp;<i class="fa fa-arrow-circle-up"></i>&nbsp;</button>
		<?php }elseif($row["active"] == 2){ ?>
		<?php }else{ ?>
			<button type="button" class="btn btn-default btn-sm" onclick="object_check_archive(<?php echo $id; ?>, 1)">&nbsp;<i class="fa fa-archive"></i>&nbsp;</button>
		<?php } ?>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function edit_main_data_object($connect){
    global $array_type;
    $id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM object WHERE id='$id'");
	$row['external_booking'] = 0;
	if ($row['check_places']==1) $row['external_booking'] = 1;
	if ($row['bnovo']==1) $row['external_booking'] = 2;
	$similar = explode("_", $row["similar"]);
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
	$country = $connect->getOne("SELECT id_country FROM region WHERE id=?i", $row["id_reg"]);
	$regions = [];

	if($row['direction']) {
	    $regions = $connect->getAll("SELECT `id`, `name` FROM `region` WHERE `id_direction` = ?i", $row['direction']);
    }

	$region_directions = [];
	if($row['id_reg']) {
      $region_directions = $connect->getAll("SELECT `id`, `name` FROM `direction_object` WHERE `id_reg` = ?i",$row['id_reg']);
    }
	ob_start();
?>
<div class="form-horizontal panel panel-info edit-object">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Изменить основные данные объекта «<?php echo $type." ".$row["name"]; ?>»</div>
	<div class="panel-body">
        <div class="form-group">
            <label class="col-sm-3 control-label">Объект активен</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control" value="1" id="active"<?php if($row['active'] == 0) echo ' checked';?>>
            </div>
        </div>		
		<div class="form-group">
            <label class="col-sm-3 control-label">Внешняя интеграция</label>
            <div class="col-sm-9">
                <select class="form-control" id="external_booking">
                    <option value="0">Не выбрано</option>
                    <option value="1" <?php if($row['external_booking']==1) { ?> selected<?php } ?>>TravelLine</option>
					<option value="2" <?php if($row['external_booking']==2) { ?> selected<?php } ?>>Bnovo</option>
                </select>
            </div>
        </div>			
		<div class="form-group">
			<label class="col-sm-3 control-label">Название</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="name_object" value="<?php echo $row['name']; ?>">
			</div>
		</div>
	
		<div class="form-group">
			<label class="col-sm-3 control-label">Полное название</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="full_name" value="<?php echo $row['full_name']; ?>">
			</div>
		</div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Должность руководителя</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="main_post_name" value="<?php echo $row['main_post_name']; ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Фамилия и инициалы руководителя</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="main_post_fio" value="<?php echo $row['main_post_fio']; ?>">
            </div>
        </div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Тип объекта</label>
			<div class="col-sm-9">
				<?php echo get_select_table($connect, "type_object", "", $row["type"], "type_object", 1, ""); ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Город</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="city_object" value="<?php echo $row['city']; ?>">
			</div>
		</div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Город в родительном падеже</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="city_genitive" value="<?php echo $row['city_genitive']; ?>">
            </div>
        </div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Направление</label>
			<div class="col-sm-9">
				<?=get_select_table($connect, "direction_object", "(`id_reg` IS NULL OR `id_reg` = 0) AND `id_country` = 1", $row["direction"], "direction-object", 1, "");?>
			</div>
		</div>
        <div class="form-group<?php if(!$row['direction']) { ?> hidden<?php } ?>">
            <label class="col-sm-3 control-label">Регион</label>
            <div class="col-sm-9">
                <select class="form-control" id="object_region">
                    <option value="0"<?php if(!$row['id_reg']) { ?> selected<?php } ?>>Не выбран</option>
                    <?php foreach ($regions as $region) { ?>
                      <option value="<?=$region['id'];?>"<?php if($row['id_reg'] == $region['id']) { ?> selected<?php } ?>><?=$region['name'];?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group<?php if(!$row['id_reg'] || count($region_directions) === 0) { ?> hidden<?php } ?>">
            <label class="col-sm-3 control-label">Региональное направление</label>
            <div class="col-sm-9">
                <select class="form-control" id="region_direction_id">
                    <option value="0"<?php if(!$row['region_direction_id']) { ?> selected<?php } ?>>Не выбрано</option>
                  <?php foreach ($region_directions as $region_direction) { ?>
                      <option value="<?=$region_direction['id'];?>"<?php if($row['region_direction_id'] == $region_direction['id']) { ?> selected<?php } ?>><?=$region_direction['name'];?></option>
                  <?php } ?>
                </select>
            </div>
        </div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Широта</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="latitude" value="<?php echo $row['latitude']; ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Долгота</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="longitude" value="<?php echo $row['longitude']; ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">ID города (погода)</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="weather" value="<?php echo $row['weather']; ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Похожие объекты</label>
			<div class="col-sm-9">
				<span class="similar-object">
			<?php foreach($similar as $id_obj){ ?>
					<input type="text" class="form-control similar-object-input" value="<?php echo $id_obj; ?>">
			<?php } ?>
					<input type="text" class="form-control similar-object-input">
				</span>
				<i class="fa fa-plus-circle icon_add pointer" onclick="add_new_similar_object()"></i>
			</div>
		</div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">Бронирование на официальном сайте (для объектов Travelline)</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control" id="source_booking"<?php if($row['source_booking'] == 1) echo ' checked';?>>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Путь к модулю бронирования</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="booking_uri" value="<?php echo $row['booking_uri']; ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Полное название для доверенности</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="trust_full_name" value="<?php echo htmlspecialchars($row['trust_full_name'], ENT_QUOTES); ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Шаблон названия доверенности</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="trust_name_template" value="<?php echo htmlspecialchars($row['trust_name_template'], ENT_QUOTES); ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Ед. измерения для доверенности</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="trust_number" value="<?php echo htmlspecialchars($row['trust_number'], ENT_QUOTES); ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Быстрое бронирование</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control" id="fast_booking"<?php if($row['fast_booking'] == 1) echo ' checked';?>>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Тип цены Travelline по умолчанию</label>
            <div class="col-sm-9">
                <select class="form-control" id="default_price_type">
                    <?php foreach ($array_type as $key => $type_name) { ?>
                      <option value="<?=$key;?>"<?php if($row['default_price_type'] == $key) { ?> selected<?php } ?>><?=$type_name;?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Адресная схема</label>
            <div class="col-sm-9">
                <select class="form-control" id="uri_schema">
                    <option value="1"<?php if($row['uri_schema'] == 1) { ?> selected<?php } ?>>Старая</option>
                    <option value="2"<?php if($row['uri_schema'] == 2) { ?> selected<?php } ?>>Новая</option>
                </select>
                <div class="std-padding alert-danger">
                    В случае изменения данного параметра не забудьте изменить адрес в настройках сайта санатории-россии.рф!
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Идентификатор для адреса</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="<?=$row['url_name'];?>" id="url_name">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Действие гос. программы</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['state_program']) { ?> checked<?php } ?> id="state-program">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Детский отдых с кешбеком</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['children_rest']) { ?> checked<?php } ?> id="children-rest">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Разместить в блоке "Идёт активное бронирование"</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['featured']) { ?> checked<?php } ?> id="featured">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Разместить в блоке "Выбор отдыхающих"</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['selected']) { ?> checked<?php } ?> id="selected">
            </div>
        </div> 
        <div class="form-group">
            <label class="col-sm-3 control-label">Объект входит в перечень "Мы рекомендуем"</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['recommended']) { ?> checked<?php } ?> id="recommended">
            </div>
        </div> 		

        <div class="form-group">
            <label class="col-sm-3 control-label">Есть отдых c лечением</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['w_therapy']) { ?> checked<?php } ?> id="w_therapy">
            </div>
        </div> 
        <div class="form-group">
            <label class="col-sm-3 control-label">Есть отдых без лечения</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['wo_therapy']) { ?> checked<?php } ?> id="wo_therapy">
            </div>
        </div> 
        <div class="form-group">
            <label class="col-sm-3 control-label">Путевка "мать и дитя"</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['mother_and_child']) { ?> checked<?php } ?> id="mother_and_child">
            </div>
        </div>         
        <div class="form-group">
            <label class="col-sm-3 control-label">Для инвалидов</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['for_invalid']) { ?> checked<?php } ?> id="for_invalid">
            </div>
        </div>         
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">Все включено</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['all_inc']) { ?> checked<?php } ?> id="all_inc">
            </div>
        </div>         
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">Шведский стол</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['open_buffet']) { ?> checked<?php } ?> id="open_buffet">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">На море</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['near_sea']) { ?> checked<?php } ?> id="near_sea">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">На Черном море</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['near_black_sea']) { ?> checked<?php } ?> id="near_black_sea">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">В лесу</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['in_forest']) { ?> checked<?php } ?> id="in_forest">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">В горах</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['in_hill']) { ?> checked<?php } ?> id="in_hill">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">У воды</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['near_water']) { ?> checked<?php } ?> id="near_water">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">У реки</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['near_river']) { ?> checked<?php } ?> id="near_river">
            </div>
        </div>        
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">на Волге</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['near_volga_river']) { ?> checked<?php } ?> id="near_volga_river">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">У озера</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['near_lake']) { ?> checked<?php } ?> id="near_lake">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">С пляжем</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['beach']) { ?> checked<?php } ?> id="beach">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">На побережье</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['on_coast']) { ?> checked<?php } ?> id="on_coast">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">С бассейном</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['w_pool']) { ?> checked<?php } ?> id="w_pool">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">С кешбеком</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['cashback']) { ?> checked<?php } ?> id="cashback">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">Со СПА</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['w_spa']) { ?> checked<?php } ?> id="w_spa">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">С бюветом</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['w_buvet']) { ?> checked<?php } ?> id="w_buvet">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="col-sm-3 control-label">С радоном</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['w_radon']) { ?> checked<?php } ?> id="w_radon">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Работает только в летний сезон</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['only_summer']) { ?> checked<?php } ?> id="only_summer">
            </div>
        </div>        
        <div class="form-group">
            <label class="col-sm-3 control-label">Классифицировано САНАТА</label>
            <div class="col-sm-9">
                <input type="checkbox" class="form-control"<?php if($row['checked_sonata']) { ?> checked<?php } ?> id="checked_sonata">
            </div>
        </div>                
        <div class="form-group">
            <label class="col-sm-3 control-label">Количество звезд</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" value="<?=$row['stars'];?>" id="stars">
            </div>
        </div>


		<div class="form-group form-group-margin">
			<label class="col-sm-3 control-label">Описание</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="description-object" id="description-object"><?php echo $row["description"][0] == '\'' ? substr($row["description"], 1, -1) : $row["description"]; ?></textarea>
			</div>
		</div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm" onclick="update_main_data_object('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
		<button type="button" class="btn btn-danger btn-sm" onclick="select_object_about('<?php echo $id; ?>')"><i class="fa fa-times-circle"></i> Отмена</button>
	</div>
</div>
<script TYPE="">
	$(function() {
      ClassicEditor.create( document.querySelector( '#description-object' ), {
        toolbar: [ 'headings', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ],
        heading: {
          options: [
            { modelElement: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { modelElement: 'heading1', viewElement: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
            { modelElement: 'heading2', viewElement: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
          ]
        }
      })
      .then( editor => {
        console.log( 'Editor was initialized', editor );
        object_description_editor = editor;
      })
      .catch(
        error => {
            console.log(error);
        }
      );
	});
</script>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_main_data_object($connect){
    global $array_type;
	$id = $_POST["id"];
	if (isset($_POST['active'])) {
		if ($_POST['active']==='1') $active = 0;
		if ($_POST['active']==='0') $active = 1;
	}
	$type = $_POST["type"];
	$latitude = (float)$_POST["latitude"];
	$longitude = (float)$_POST["longitude"];
	$external_booking = $_POST["external_booking"];
	$bnovo = 0;
	$check_places = 0;
	if ($external_booking==1) $check_places = 1;
	if ($external_booking==2) $bnovo = 1;
	$name = $_POST["name"];
	$full_name = $_POST["full_name"];
	$city = trim($_POST["city"]);
	$city_genitive = trim($_POST["city_genitive"]);
	$direction = (int)$_POST["direction"];
    $id_reg = (int)$_POST["region_id"];
    $region_direction_id = (int)$_POST['region_direction_id'];
    $uri_schema = isset($_POST["uri_schema"])?(int)$_POST["uri_schema"]:1;
    $state_program = isset($_POST["state_program"])?(int)$_POST["state_program"]:0;
    $children_rest = isset($_POST["children_rest"])?(int)$_POST["children_rest"]:0;
    $featured = isset($_POST["featured"])?(int)$_POST["featured"]:0;
    $selected = isset($_POST["selected"])?(int)$_POST["selected"]:0;
	$recommended = isset($_POST["recommended"])?(int)$_POST["recommended"]:0;
    $url_name = trim($_POST['url_name']);
    $trust_full_name = isset($_POST['trust_full_name']) ? $_POST['trust_full_name'] : '';
    $trust_name_template = isset($_POST['trust_name_template']) ? $_POST['trust_name_template'] : '';
    $trust_number = isset($_POST['trust_number']) ? $_POST['trust_number'] : '';

    if ($state_program) {
        $state_program = 1;
    } else {
        $state_program = 0;
    }

    if ($children_rest) {
        $children_rest = 1;
    } else {
        $children_rest = 0;
    }

    if($featured) {
    	$featured = 1;
	} else {
		$featured = 0;
	}

    if(!in_array($uri_schema,[1,2]))
        $uri_schema = 1;

    if(!$direction) {
        $id_reg = 0;
        $region_direction_id = 0;
    }

    if(!$id_reg) {
        $region_direction_id = 0;
    }

	$similar = $_POST["similar"];
	$weather = $_POST["weather"];
	$description = $connect->escapeString($_POST["description"]);
	$source_booking = (int)$_POST["source_booking"];
	$fast_booking = (int)$_POST["fast_booking"];
	$booking_uri = $_POST["booking_uri"];
	$main_post_name = trim($_POST["main_post_name"]);
    $main_post_fio = trim($_POST["main_post_fio"]);
    $default_price_type = isset($_POST['default_price_type'])?(int)$_POST['default_price_type']:1;

    $w_therapy = ($_POST['w_therapy']==1) ? 1 : 0;
    $wo_therapy = ($_POST['wo_therapy']==1) ? 1 : 0;
    $mother_and_child = ($_POST['mother_and_child']==1) ? 1 : 0;
    $for_invalid = ($_POST['for_invalid']==1) ? 1 : 0;
    $all_inc = ($_POST['all_inc']==1) ? 1 : 0;
    $open_buffet = ($_POST['open_buffet']==1) ? 1 : 0;
    $near_sea = ($_POST['near_sea']==1) ? 1 : 0;
    $near_black_sea = ($_POST['near_black_sea']==1) ? 1 : 0;
    $in_forest = ($_POST['in_forest']==1) ? 1 : 0;
    $in_hill = ($_POST['in_hill']==1) ? 1 : 0;
    $near_water = ($_POST['near_water']==1) ? 1 : 0;
    $near_volga_river = ($_POST['near_volga_river']==1) ? 1 : 0;
    $near_river = ($_POST['near_river']==1) ? 1 : 0;
    $near_lake = ($_POST['near_lake']==1) ? 1 : 0;
    $beach = ($_POST['beach']==1) ? 1 : 0;
    $on_coast = ($_POST['on_coast']==1) ? 1 : 0;
    $w_pool = ($_POST['w_pool']==1) ? 1 : 0;
    $cashback = ($_POST['cashback']==1) ? 1 : 0;
    $w_spa = ($_POST['w_spa']==1) ? 1 : 0;
    $w_buvet = ($_POST['w_buvet']==1) ? 1 : 0;
    $w_radon = ($_POST['w_radon']==1) ? 1 : 0;
    $only_summer = ($_POST['only_summer']==1) ? 1 : 0;
    $checked_sonata = ($_POST['checked_sonata']==1) ? 1 : 0;

    if ($_POST['stars']=='') $stars = 0; else $stars = $_POST['stars'];

    if(!array_key_exists($default_price_type,$array_type))
        $default_price_type = 1;

    $connect->query("UPDATE object SET name=?s, full_name=?s, city=?s, city_genitive = ?s, direction=?s, type=?s, latitude=?s, longitude=?s, similar=?s, weather=?s, description=?s, source_booking=?i, description_check=?s, booking_uri=?s, fast_booking=?i, main_post_name = ?s, main_post_fio = ?s, default_price_type = ?i, id_reg = ?i, region_direction_id = ?i, `uri_schema` = ?i, `url_name` = ?s, `state_program` = ?i, `children_rest` = ?i, synchronized=0, `featured` = ?i, `selected` = ?i, `w_therapy`=?i, `wo_therapy`=?i, `mother_and_child`=?i, `for_invalid`=?i, `all_inc`=?i, `open_buffet`=?i, `near_sea`=?i, `near_black_sea`=?i, `in_forest`=?i, `in_hill`=?i, `near_water`=?i, `near_river`=?i, `near_volga_river`=?i, `near_lake`=?i, `beach`=?i, `on_coast`=?i, `w_pool`=?i, `cashback`=?i, `w_spa`=?i, `w_buvet`=?i, `w_radon`=?i, `only_summer`=?i, `checked_sonata`=?i, `stars`='$stars', `trust_full_name` = ?s, `trust_name_template` = ?s, `trust_number` = ?s, `check_places`=?i, `bnovo`=?i WHERE id=?i", $name, $full_name, $city, $city_genitive, $direction, $type, $latitude, $longitude, $similar, $weather, $description, $source_booking, $description, $booking_uri, $fast_booking, $main_post_name, $main_post_fio, $default_price_type, $id_reg, $region_direction_id,$uri_schema, $url_name, $state_program, $children_rest, $featured, $selected, $w_therapy, $wo_therapy, $mother_and_child, $for_invalid, $all_inc, $open_buffet, $near_sea, $near_black_sea, $in_forest, $in_hill, $near_water, $near_river, $near_volga_river, $near_lake, $beach, $on_coast, $w_pool, $cashback, $w_spa, $w_buvet, $w_radon, $only_summer, $checked_sonata, $trust_full_name, $trust_name_template, $trust_number, $check_places, $bnovo, $id);

	echo $connect->last_query();
	if (isset($active)) {
		$connect->query("UPDATE object SET active=?s WHERE id=?i", $active, $id);
	}
	echo $connect->last_query();

    
}

function edit_desc_object($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, type, id_profile, id_methods, id_procedures, id_infa, medical_factors FROM object WHERE id=?i", $id);
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
	ob_start();
?>
<div class="form-horizontal panel panel-info">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Изменить основные данные объекта «<?php echo $type." ".$row["name"]; ?>»</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Лечебные факторы</label>
			<div class="col-sm-9">
				<textarea class="form-control medical-factors"><?php echo $row["medical_factors"]; ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Профили лечения</label>
			<div class="col-sm-9">
				<div class="check-div">
					<?php echo break_columns($connect, "profile", 5, $row["id_profile"], "ORDER BY name"); ?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Методы лечения</label>
			<div class="col-sm-9">
				<div class="check-div">
					<?php echo break_columns($connect, "methods", 5, $row["id_methods"], "ORDER BY name"); ?>
				</div>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-3 control-label">Инфраструктура</label>
			<div class="col-sm-9">
				<div class="check-div">
					<?php echo break_columns($connect, "infa", 5, $row["id_infa"], "ORDER BY name"); ?>
				</div>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-3 control-label">Процедуры</label>
			<div class="col-sm-9">
				<div class="check-div">
					<?php echo break_columns($connect, "procedure", 5, $row["id_procedures"], "ORDER BY name"); ?>
				</div>
			</div>
		</div>		
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm" onclick="update_desc_object(<?php echo $id; ?>)"><i class="fa fa-check-circle"></i> Сохранить</button>
		<button type="button" class="btn btn-danger btn-sm" onclick="select_object_about(<?php echo $id; ?>)"><i class="fa fa-times-circle"></i> Отмена</button>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_desc_object($connect){
	$id = $_POST["id"];
	$profile = $_POST["profile"];
	$infa = $_POST["infa"];
	$method = $_POST["method"];
	$procedure = $_POST["procedure"];
	$medical_factors = $_POST["medical_factors"];
	$connect->query("UPDATE object SET id_profile=?s, id_methods=?s, id_procedures=?s, id_infa=?s, medical_factors=?s, synchronized=0 WHERE id=?i", $profile, $method, $procedure, $infa, $medical_factors, $id);
}

function edit_services_object($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, type, id_services FROM object WHERE id=?i", $id);
	$services = json_decode($row["id_services"], TRUE);
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
	$array_services = $connect->getAll("SELECT id, name, icon FROM services");
?>
<div class="form-horizontal panel panel-info edit-services">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Изменить основные данные объекта «<?php echo $type." ".$row["name"]; ?>»</div>
	<div class="panel-body">
	<?php foreach($array_services as $service){
		$icon = "";
		if($service["icon"])
			$icon = "<i class='fa ".$service["icon"]."'></i>";
		$id_s = $service["id"];
		if(!isset($services[$id_s]))
			$services[$id_s] = "";
	?>
		<div class="form-group">
			<label class="col-sm-3 control-label"><?php echo $icon." ".$service["name"]; ?></label>
			<div class="col-sm-9">
				<input type="text" class="form-control" name="<?php echo $id_s; ?>" value="<?php echo $services[$id_s]; ?>">
			</div>
		</div>
	<?php } ?>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-success btn-sm" onclick="update_services_object('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
		<button type="button" class="btn btn-danger btn-sm" onclick="select_object_about('<?php echo $id; ?>')"><i class="fa fa-times-circle"></i> Отмена</button>
	</div>
</div>
<?php
}

function update_services_object($connect){
	$id = $_POST["id"];
	$services = $_POST["services"];
	$connect->query("UPDATE object SET id_services=?s, synchronized=0 WHERE id=?i", $services, $id);
}


//OCCUPANCIES
function select_object_occupancies($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	ob_start();
	$row = $connect->getRow("SELECT name, type, id_services FROM object WHERE id=?i", $id);
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);		
	?>
	<div class="panel panel-default">
		<div class="panel-heading"><i class="fa fa-cubes"></i> Варианты размещений объекта «<?php echo $type." ".$row["name"]; ?>»</div>
		<table class="table tbl-room">
		<?php
			//$data = $connect->getAll("SELECT * FROM room_occupancy WHERE id_obj=?i and `status`=1 ORDER BY id ASC", $_POST["id"]);
			$data = $connect->getAll("SELECT * FROM place WHERE id_obj=?i and `status`=1 ORDER BY id ASC", $_POST["id"]);
			foreach($data as $row){
				$room = $connect->getRow("SELECT * FROM room WHERE id=?i", $row["id_room"]);	
				if ($room['housing']>0) {
					$housing = $connect->getOne("SELECT name FROM housing WHERE id=?i", $room['housing']);
					if ($housing) $room['name'] .= ' ('.$housing.')';
				}				
				?>
				<tr id="room_occupancy_<?=$row['id']?>">
					<td><?=$room['name']?></td>
					<td><?=$row['name']?>
					</td>
					<td width="80">
						<button class="btn btn-default btn-xs" onclick="new_room_occupancy('<?=$row['id']?>')" title="Редактировать"><i class="fa fa-pencil"></i></button>
						&nbsp; 
						<button type="button" class="btn btn-danger btn-xs" onclick="del_room_occupancy('<?=$row['id']?>')" title="Удалить"><i class="fa fa-trash-o"></i></button>
					</td>
				</tr>
		<?php
		}
		if(!$data){?>
			<tr>
				<td colspan="3">
					<div class="alert alert-info"><i class="fa fa-info-circle"></i> Записей нет</div>
				</td>
			</tr>
		<?php } ?>
		</table>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-primary btn-sm" onclick="new_room_occupancy('0')"><i class="fa fa-plus-circle"></i> Новое размещение</button>
		</div>
	</div>

	<?php
	$html = ob_get_clean();
	return $html;
}

function del_room_occupancy($connect) {
	//Используется в СРМ и кабинете объекта!
	//$connect->query("UPDATE room_occupancy SET `status`=0 WHERE id=?i", $_POST['id']);
	if (isset($_POST['id_obj'])) $connect->query("UPDATE place SET `status`=0, `synchronized`=0 WHERE id=?i and `id_obj`=?i", $_POST['id'], $_POST['id_obj']);
	else $connect->query("UPDATE place SET `status`=0, `synchronized`=0 WHERE id=?i", $_POST['id']);
	sync_objects_api($connect);
}

function room_occupancy($connect){
	//Используется в СРМ и кабинете объекта!
	$data = false;
	$id_room = '';
	$adult_on_main_place = '';
	$adult_on_add_place = '';
	$id_child_on_main_place = '';
	$child_on_main_place = '';
	$id_child_on_add_place = '';
	$child_on_add_place = '';
	$id_child_no_place = '';
	$child_no_place = '';

	if ($_POST['id']!='0') {
		//$data = $connect->getRow("SELECT * FROM room_occupancy WHERE id=?i", $_POST['id']);
		$data = $connect->getRow("SELECT * FROM place WHERE id=?i", $_POST['id']);
		$id_room = $data['id_room'];
		$adult_on_main_place = $data['adult_on_main_place'];
		$adult_on_add_place = $data['adult_on_add_place'];
		$id_child_on_main_place = $data['id_child_on_main_place'];
		$child_on_main_place = $data['child_on_main_place'];
		$id_child_on_add_place = $data['id_child_on_add_place'];
		$child_on_add_place = $data['child_on_add_place'];
		$id_child_no_place = $data['id_child_no_place'];
		$child_no_place = $data['child_no_place'];
	}
	ob_start();
	?>
	<tr class="new-room-occupancy">
		<td colspan="2">
			<form class="new-room-occupancy-form">
				<div class="form-horizontal panel-body check-div new-room-div">
					<div class="form-group">
						<label class="col-sm-2 control-label">Номер</label>
						<div class="col-sm-4">
							<select class="form-control" name="id_room">
								<option value="0">выберите</option>
								<?php
								$rooms = $connect->getAll("SELECT * FROM room WHERE active=0 AND id_obj=?i ORDER BY name ASC", $_POST['id_obj']);
								foreach ($rooms as $room) {
									if ($room['housing']>0) {
										$housing = $connect->getOne("SELECT name FROM housing WHERE id=?i", $room['housing']);
										if ($housing) $room['name'] .= ' ('.$housing.')';
									}
									if ($id_room==$room['id']) $sel = ' selected="selected"'; else $sel = '';
									?><option value="<?=$room['id']?>" <?=$sel?>><?=$room['name']?></option><?php
								}
								?>
							</select>
						</div>
					</div>					
					<div class="form-group">
						<label class="col-sm-2 control-label">Взрослых на осн.месте</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="adult_on_main_place" name="adult_on_main_place" value="<?=$adult_on_main_place?>">
						</div>
						<label class="col-sm-2 control-label">Взрослых на доп.месте</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="adult_on_add_place" name="adult_on_add_place" value="<?=$adult_on_add_place?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">Детей возраста</label>
						<div class="col-sm-4">
							<select class="form-control" name="id_child_on_main_place">
								<option value="0">выберите</option>
								<?php
								$childs = $connect->getAll("SELECT * FROM child_occupancy WHERE id_obj=?i and `status`=1", $_POST['id_obj']);
								foreach ($childs as $child) {
									if ($id_child_on_main_place==$child['id']) $sel = ' selected="selected"'; else $sel = '';
									?><option value="<?=$child['id']?>" <?=$sel?>>(от <?=$child['age_from']?> до <?=$child['age_to']?>  лет)</option><?php
								}
								?>
							</select>
						</div>
						<label class="col-sm-2 control-label">на осн.месте в кол-ве</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="child_on_main_place" name="child_on_main_place" value="<?=$child_on_main_place?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">Детей возраста</label>
						<div class="col-sm-4">
							<select class="form-control" name="id_child_on_add_place">
								<option value="0">выберите</option>
								<?php
								$childs = $connect->getAll("SELECT * FROM child_occupancy WHERE id_obj=?i and `status`=1", $_POST['id_obj']);
								foreach ($childs as $child) {
									if ($id_child_on_add_place==$child['id']) $sel = ' selected="selected"'; else $sel = '';
									?><option value="<?=$child['id']?>" <?=$sel?>>(от <?=$child['age_from']?> до <?=$child['age_to']?>  лет)</option><?php
								}
								?>
							</select>
						</div>
						<label class="col-sm-2 control-label">на доп.месте в кол-ве</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="child_on_add_place" name="child_on_add_place" value="<?=$child_on_add_place?>">
						</div>
					</div>	
					<div class="form-group">
						<label class="col-sm-2 control-label">Детей возраста</label>
						<div class="col-sm-4">
							<select class="form-control" name="id_child_no_place">
								<option value="0">выберите</option>
								<?php
								$childs = $connect->getAll("SELECT * FROM child_occupancy WHERE id_obj=?i and `status`=1", $_POST['id_obj']);
								foreach ($childs as $child) {
									if ($id_child_no_place==$child['id']) $sel = ' selected="selected"'; else $sel = '';
									?><option value="<?=$child['id']?>" <?=$sel?>>(от <?=$child['age_from']?> до <?=$child['age_to']?>  лет)</option><?php
								}
								?>
							</select>
						</div>
						<label class="col-sm-2 control-label">без места в кол-ве</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="child_no_place" name="child_no_place" value="<?=$child_no_place?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-8 col-sm-4">
							<?php if (!$data) {?>
								<button type="button" class="btn btn-success btn-sm" onclick="save_room_occupancy('0')"><i class="fa fa-check-circle"></i> Сохранить</button>
							<?php } else {?>
								<button type="button" class="btn btn-success btn-sm" onclick="save_room_occupancy('<?=$data['id']?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
							<?php } ?>
							<button type="button" class="btn btn-danger btn-sm" onclick="$('.new-room-occupancy').remove()"><i class="fa fa-times-circle"></i> Отмена</button>
						</div>
					</div>
				</div>
			</form>
		</td>
	</tr>
	<?php
	$html = ob_get_clean();
	return $html;
}


function save_room_occupancy($connect) {
	//Используется в СРМ и кабинете объекта!

	if ($_POST['id_room']==0 || !$_POST['id_room'] || $_POST['id_obj']==0 || !$_POST['id_obj']) {
		return false;
	}

	$_POST['adult_on_main_place'] = $_POST['adult_on_main_place']==''?0:$_POST['adult_on_main_place'];
	$_POST['adult_on_add_place'] = $_POST['adult_on_add_place']==''?0:$_POST['adult_on_add_place'];
	$_POST['child_on_main_place'] = $_POST['child_on_main_place']==''?0:$_POST['child_on_main_place'];
	$_POST['child_on_add_place'] = $_POST['child_on_add_place']==''?0:$_POST['child_on_add_place'];
	$_POST['child_no_place'] = $_POST['child_no_place']==''?0:$_POST['child_no_place'];

	if ($_POST['id_child_on_main_place']==0 && $_POST['child_on_main_place']>0) $_POST['child_on_main_place'] = 0;
	if ($_POST['id_child_on_main_place']!=0 && $_POST['child_on_main_place']==0) $_POST['id_child_on_main_place'] = 0;

	if ($_POST['id_child_on_add_place']==0 && $_POST['child_on_add_place']>0) $_POST['child_on_add_place'] = 0;
	if ($_POST['id_child_on_add_place']!=0 && $_POST['child_on_add_place']==0) $_POST['id_child_on_add_place'] = 0;	

	if ($_POST['id_child_no_place']==0 && $_POST['child_no_place']>0) $_POST['child_no_place'] = 0;
	if ($_POST['id_child_no_place']!=0 && $_POST['child_no_place']==0) $_POST['id_child_no_place'] = 0;		

	if (get_place_name($_POST)=='') return false;

	if ($_POST['id']=='0') {
		//Создаем новое размещение
		$connect->query("INSERT INTO `place` SET 
			`id`=0, 
			`name`=?s,
			`id_obj`=?i, 
			`id_room`=?i, 
			`adult_on_main_place`=?i,
			`adult_on_add_place`=?i,
			`id_child_on_main_place`=?i,
			`child_on_main_place`=?i,
			`id_child_on_add_place`=?i,
			`child_on_add_place`=?i,
			`id_child_no_place`=?i,
			`child_no_place`=?i
			", 
			get_place_name($_POST),
			$_POST['id_obj'], 
			$_POST['id_room'], 
			$_POST['adult_on_main_place'],
			$_POST['adult_on_add_place'],
			$_POST['id_child_on_main_place'],
			$_POST['child_on_main_place'],
			$_POST['id_child_on_add_place'],
			$_POST['child_on_add_place'],
			$_POST['id_child_no_place'],
			$_POST['child_no_place']
		);
		$last_id = $connect->insertId();

		$connect->query("UPDATE `place` SET `export_id`=?s WHERE id=?i", get_place_export_id($_POST['id_room'], $_POST), $last_id);

	} else {
		//меняем имеющееся размещение
		if (isset($_POST['id_obj'])) {
			$connect->query("UPDATE `place` SET 
				`name`=?s,
				`synchronized`=0,
				`export_id`=?s,
				`id_room`=?i, 
				`adult_on_main_place`=?i, 
				`adult_on_add_place`=?i, 
				`id_child_on_main_place`=?i, 
				`child_on_main_place`=?i, 
				`id_child_on_add_place`=?i, 
				`child_on_add_place`=?i,
				`id_child_no_place`=?i, 
				`child_no_place`=?i  
				WHERE `id`=?i and `id_obj`=?i
				", 
				get_place_name($_POST),
				get_place_export_id($_POST['id_room'], $_POST),
				$_POST['id_room'], 
				$_POST['adult_on_main_place'], 
				$_POST['adult_on_add_place'], 
				$_POST['id_child_on_main_place'], 
				$_POST['child_on_main_place'], 
				$_POST['id_child_on_add_place'], 
				$_POST['child_on_add_place'], 
				$_POST['id_child_no_place'], 
				$_POST['child_no_place'],
				$_POST['id'],
				$_POST['id_obj']
			);
		} else {
			$connect->query("UPDATE `place` SET 
				`name`=?s,
				`synchronized`=0,
				`export_id`=?s,
				`id_room`=?i, 
				`adult_on_main_place`=?i, 
				`adult_on_add_place`=?i, 
				`id_child_on_main_place`=?i, 
				`child_on_main_place`=?i, 
				`id_child_on_add_place`=?i, 
				`child_on_add_place`=?i,
				`id_child_no_place`=?i, 
				`child_no_place`=?i  
				WHERE `id`=?i
				", 
				get_place_name($_POST),
				get_place_export_id($_POST['id_room'], $_POST),
				$_POST['id_room'], 
				$_POST['adult_on_main_place'], 
				$_POST['adult_on_add_place'], 
				$_POST['id_child_on_main_place'], 
				$_POST['child_on_main_place'], 
				$_POST['id_child_on_add_place'], 
				$_POST['child_on_add_place'], 
				$_POST['id_child_no_place'], 
				$_POST['child_no_place'],
				$_POST['id']
			);
		}
	}

	sync_objects_api($connect);
}


//OCCUPANCIES



//CHILD OCCU

function select_object_child_occupancies($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, type, id_services FROM object WHERE id=?i", $id);
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);	
	ob_start();
	?>
	<div class="panel panel-default">
		<div class="panel-heading"><i class="fa fa-cubes"></i> Варианты детских размещений объекта «<?php echo $type." ".$row["name"]; ?>»</div>
		<table class="table tbl-room">
		<?php
			$data = $connect->getAll("SELECT * FROM child_occupancy WHERE id_obj=?i and `status`=1 ORDER BY id ASC", $id);
			foreach($data as $row){
			?>
			<tr id="child_occupancy_<?=$row['id']?>">
				<td><?php echo 'дети c возрастом от '.$row['age_from'].' до '.$row['age_to']; ?> лет</td>
				<td width="220">
					<button class="btn btn-default btn-xs" onclick="new_child_occupancy('<?=$row['id']?>')" title="Редактировать"><i class="fa fa-pencil"></i></button>
					&nbsp; &nbsp; 
					<button type="button" class="btn btn-danger btn-xs" onclick="del_child_occupancy('<?=$row['id']?>')" title="Удалить"><i class="fa fa-trash-o"></i></button>
				</td>
			</tr>
		<?php
		}
		if(!$data){?>
			<tr>
				<td colspan="7">
					<div class="alert alert-info"><i class="fa fa-info-circle"></i> Записей нет</div>
				</td>
			</tr>
		<?php } ?>
		</table>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-primary btn-sm" onclick="new_child_occupancy('0')"><i class="fa fa-plus-circle"></i> Новое детское размещение</button>
		</div>
	</div>

	<?php
	$html = ob_get_clean();
	return $html;
}


function child_occupancy($connect){
	//Используется в СРМ и кабинете объекта!
	$data = false;
	$age_from = '';
	$age_to = '';
	if ($_POST['id']!='0') {
		$data = $connect->getRow("SELECT * FROM child_occupancy WHERE id=?i", $_POST['id']);
		$age_from = $data['age_from'];
		$age_to = $data['age_to'];
	}
	ob_start();
	?>
	<tr class="new-child-occupancy">
		<td colspan="2">
			<div class="form-horizontal panel-body check-div new-room-div">
				<div class="form-group">
					<label class="col-sm-2 control-label">Возраст от </label>
					<div class="col-sm-5">
						<input type="text" class="form-control" id="age_from" value="<?=$age_from?>">
					</div>
					<label class="col-sm-1 control-label">до</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="age_to" value="<?=$age_to?>">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-8 col-sm-4">
						<?php if (!$data) {?>
							<button type="button" class="btn btn-success btn-sm" onclick="save_child_occupancy('0')"><i class="fa fa-check-circle"></i> Сохранить</button>
						<?php } else {?>
							<button type="button" class="btn btn-success btn-sm" onclick="save_child_occupancy('<?=$data['id']?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
						<?php } ?>
						<button type="button" class="btn btn-danger btn-sm" onclick="$('.new-child-occupancy').remove()"><i class="fa fa-times-circle"></i> Отмена</button>
					</div>
				</div>
			</div>
		</td>
	</tr>
	<?php
	$html = ob_get_clean();
	return $html;
}

function del_child_occupancy($connect) {
	//Используется в СРМ и кабинете объекта!

	$used_on_occupancies = $connect->getAll("SELECT * FROM `place` WHERE `id_child_on_main_place`=?i OR `id_child_on_add_place`=?i OR `id_child_no_place`=?i",  $_POST['id'], $_POST['id'], $_POST['id']);
	
	if (count($used_on_occupancies)>0) {
		return 'used_on_occupancies';
	}
	else {
		if (isset($_POST["id_obj"])) $connect->query("UPDATE child_occupancy SET `status`=0, `synchronized`=0 WHERE id=?i and id_obj=?i", $_POST['id'], $_POST['id_obj']);
		else $connect->query("UPDATE child_occupancy SET `status`=0, `synchronized`=0 WHERE id=?i", $_POST['id']);
	}
	sync_objects_api($connect);
}

function save_child_occupancy($connect) {
	//Используется в СРМ и кабинете объекта!
	if ($_POST['id']=='0') {
		//Создаем новое размещение
		$connect->query("INSERT INTO child_occupancy SET `id`=0, `id_obj`=?i, `age_from`=?i, `age_to`=?i", $_POST['id_obj'], $_POST['age_from'], $_POST['age_to']);
	} else {
		//меняем имеющееся размещение
		if (isset($_POST["id_obj"])) $connect->query("UPDATE child_occupancy SET `synchronized`=0, `age_from`=?i, `age_to`=?i WHERE `id`=?i and `id_obj`=?i", $_POST['age_from'], $_POST['age_to'], $_POST['id'], $_POST['id_obj']);
		else $connect->query("UPDATE child_occupancy SET `synchronized`=0, `age_from`=?i, `age_to`=?i WHERE `id`=?i", $_POST['age_from'], $_POST['age_to'], $_POST['id']);
	}
	sync_objects_api($connect);
}

//CHILD OCCU











function select_object_room($connect){
	$old_housing = "";
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, type, id_reg FROM object WHERE id=?i", $id);
	$id_reg = $row["id_reg"];
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
	ob_start();
?>
	<div class="panel panel-default">
		<div class="panel-heading"><i class="fa fa-cubes"></i> Номерной фонд объекта «<?php echo $type." ".$row["name"]; ?>»</div>
		<table class="table tbl-room">
<?php
	$data = $connect->getAll("SELECT id, name, note, id_comfort, id_best_comfort, main_place, add_place, wo_bed_place, active, housing, food, square FROM room WHERE id_obj=?i ORDER BY housing DESC, active", $id);
	foreach($data as $row){
		$id_room = $row["id"];
		$class = "";
		if($row["active"] == 1)
			$class = " alert-danger ";
		$square = "";
		$food = "";
		if($row["square"])
			$square = "<i class='fa fa-codepen'></i>&nbsp;<strong>".$row["square"]." кв.м.</strong>";
		if($row["food"])
			$food = "<i class='fa fa-cutlery'></i>&nbsp;<strong>".$row["food"]."</strong>";
		$housing = $row["housing"];
		if($housing != $old_housing){
			$new_housing = $connect->getOne("SELECT name FROM housing WHERE id=?i", $housing);
			if(!$new_housing AND $old_housing)
				$new_housing = "Не определены";
			if($new_housing)
			?>
				<tr><th colspan="7" class="center"><?php echo $new_housing; ?></th></tr>
			<?php
			$old_housing = $housing;
		}
		$image = "temp/defaul.jpg";
		$url = select_image_room($id_reg, $id, $id_room);
		if($url)
			$image = $url;
		$best_comfort = "";
		if($row["id_best_comfort"]){
			$array = explode("_", $row["id_best_comfort"]);
			foreach($array as $index){
				if($index){
					$data_comfort = $connect->getRow("SELECT icon, name FROM comfort WHERE id=?i", $index);
					$best_comfort.= "<i class='fa ".$data_comfort["icon"]." pointer' title='".$data_comfort["name"]."'></i>&nbsp;";
				}
			}
		}
?>
		<tr id="str<?php echo $id_room; ?>" class="<?php echo $class; ?>">
			<td width="140"><img src="<?php echo $image; ?>" class="img-head-small" /><?php echo $row["name"]; ?></td>
			<td width="150"><?php echo $best_comfort; ?></td>
			<td width="150"><?php echo $food; ?></td>
			<td width="100"><i class="fa fa-user"></i> <strong><?php echo $row["main_place"]." + ".$row["add_place"]." (+".$row["wo_bed_place"].")"; ?></strong></td>
			<td width="100"><?php echo $square; ?></td>
			<td width="220"><?php echo $row["note"]; ?></td>
			<td width="220">
				<button class="btn btn-default btn-xs" onclick="edit_room('<?php echo $id_room; ?>')" title="Редактировать"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-default btn-xs" onclick="copy_room('<?php echo $id_room; ?>')" title="Копировать"><i class="fa fa-files-o"></i></button>
				&nbsp;|&nbsp;
		<?php if($row["active"] == 0){ ?>
				<button class="btn btn-warning btn-xs" onclick="room_check_archive('<?php echo $id_room; ?>')" title="В архив"><i class="fa fa-trash-o"></i></button>
		<?php }else{ ?>
				<button class="btn btn-danger btn-xs room_delete" onclick="room_delete('<?php echo $id_room; ?>')" title="Удалить"><i class="fa fa-times-circle"></i></button>
				<button class="btn btn-primary btn-xs" onclick="room_check_archive('<?php echo $id_room; ?>')" title="Из архива"><i class="fa fa-arrow-circle-up"></i></button>
		<?php } ?>
				&nbsp;|&nbsp;
		<?php if($url){ ?>
				<button type="button" class="btn btn-info btn-xs" onclick="view_images_room('<?php echo $id_room; ?>')" title="Фото"><i class="fa fa-picture-o"></i></button>
		<?php } ?>
				<button type="button" class="btn btn-success btn-xs add_new_image_room" onclick="add_new_image_room('<?php echo $id_room; ?>')" title="Новое фото"><i class="fa fa-upload"></i></button>
			</td>
		</tr>
<?php
	}
	if(!$data){
?>
		<tr>
			<td colspan="7">
				<div class="alert alert-info"><i class="fa fa-info-circle"></i> Номерной фонд пуст</div>
			</td>
		</tr>
<?php
	}
?>
		</table>
	<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-primary btn-sm" onclick="new_room('<?php echo $id; ?>')"><i class="fa fa-plus-circle"></i> Новый номер</button>
		</div>
	</div>

<?php
	$html = ob_get_clean();
	return $html;
}

function add_new_room($connect){
	//Используется в СРМ и кабинете объекта!
	$id_obj = $_POST["id"];
	ob_start();
?>
<tr class="new-room"><td colspan="7">
<div class="form-horizontal panel-body check-div new-room-div">
	<div class="form-group">
		<label class="col-sm-2 control-label">Номер</label>
		<div class="col-sm-5">
			<input type="text" class="form-control" id="name">
		</div>
		<label class="col-sm-1 control-label">Корпус</label>
		<div class="col-sm-4">
			<?php echo get_select_table($connect, "housing", "id_obj=".$id_obj, "", "housing_object", 1); ?>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Основные места</label>
		<div class="col-sm-2">
			<select id="main_place" class="form-control"><?php echo get_select_options(1, 30, ""); ?></select>
		</div>
		<label class="col-sm-1 control-label">Доп. места</label>
		<div class="col-sm-2">
			<select id="add_place" class="form-control"><?php echo get_select_options(0, 30, ""); ?></select>
		</div>
		<label class="col-sm-1 control-label">Без места</label>
		<div class="col-sm-2">
			<select id="wo_bed_place" class="form-control"><?php echo get_select_options(0, 5, ""); ?></select>
		</div>		
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Питание</label>
		<div class="col-sm-4">
			<input type="text" class="form-control" id="food">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Площадь (кв.м.)</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="square">
		</div>
		<label class="col-sm-2 control-label">Примечание</label>
		<div class="col-sm-6">
			<input type="text" class="form-control" id="note">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Удобства</label>
		<div class="col-sm-10" id="best_comfort">
			<?php echo break_columns($connect, "comfort", 5, "", "WHERE type=1 ORDER BY name"); ?>
		</div>
		<label class="col-sm-2 control-label for_cabinet" >&nbsp;</label>
		<div class="col-sm-10" id="comfort">
			<?php echo break_columns($connect, "comfort", 5, "", "WHERE type=0 ORDER BY name"); ?>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-8 col-sm-4">
			<button type="button" class="btn btn-success btn-sm" onclick="save_new_room()"><i class="fa fa-check-circle"></i> Сохранить</button>
			<button type="button" class="btn btn-danger btn-sm" onclick="$('.new-room').remove()"><i class="fa fa-times-circle"></i> Отмена</button>
		</div>
	</div>
</div>
</td></tr>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_room($connect){
    if(!isset($_POST['housing']) || empty($_POST['housing']))
        $housing = NULL;
    else
        $housing = (int)$_POST['housing'];
	//print_r($_POST);
	$connect->query("INSERT INTO room(name, id_obj, id_comfort, id_best_comfort, note, main_place, add_place, wo_bed_place, housing, square, food) VALUES (?s, ?i, ?s, ?s, ?s, ?i, ?i, ?i, ?s, ?s, ?s)", $_POST["name_room"], $_POST["id_obj"], $_POST["comfort"], $_POST["best_comfort"], $_POST["note"], $_POST["main_place"], $_POST["add_place"], $_POST["wo_bed_place"], $housing, $_POST["square"], $_POST["food"]);
	//echo $connect->last_query();
	sync_objects_api($connect);
}

function edit_room($connect){
	//Используется в СРМ и кабинете объекта!
	$id = (int)$_POST["id"];
	$row = $connect->getRow("SELECT id_obj, name, id_comfort, id_best_comfort, note, main_place, add_place, housing, food, square FROM room WHERE id=?i", $id);
	$manager = isset($_POST['manager'])?(int)$_POST['manager']:0;
	$entity = [
	  'id' => $id,
      'type' => 'room'
    ];
	ob_start();
?>
<tr class="edit-room"><td colspan="7">
<div class="form-horizontal check-div panel-body edit-room-div">
	<div class="form-group">
		<label class="col-sm-2 control-label">Номер</label>
		<div class="col-sm-5">
			<input type="text" class="form-control" id="name" value="<?php echo $row['name']; ?>">
		</div>
		<label class="col-sm-1 control-label">Корпус</label>
		<div class="col-sm-4">
			<?php echo get_select_table($connect, "housing", "id_obj=".$row["id_obj"], $row["housing"], "housing_object", 1); ?>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Основные места</label>
		<div class="col-sm-2">
			<select id="main_place" class="form-control"><?php echo get_select_options(1, 30, $row["main_place"]); ?></select>
		</div>
		<label class="col-sm-1 control-label">Доп. места</label>
		<div class="col-sm-2">
			<select id="add_place" class="form-control"><?php echo get_select_options(0, 30, $row["add_place"]); ?></select>
		</div>
		<label class="col-sm-1 control-label">Без места</label>
		<div class="col-sm-2">
			<select id="wo_bed_place" class="form-control"><?php echo get_select_options(0, 5, $row["wo_bed_place"]); ?></select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Питание</label>
		<div class="col-sm-4">
			<input type="text" class="form-control" id="food" value="<?php echo $row['food']; ?>">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Площадь (кв.м.)</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="square" value="<?php echo $row['square']; ?>">
		</div>
		<label class="col-sm-2 control-label">Примечание</label>
		<div class="col-sm-6">
			<textarea type="text" class="form-control" id="note"><?php echo $row['note']; ?></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">Удобства</label>
		<div class="col-sm-10" id="best_comfort">
			<?php echo break_columns($connect, "comfort", 5, $row["id_best_comfort"], "WHERE type=1 ORDER BY name"); ?>
		</div>
		<label class="col-sm-2 control-label for_cabinet" >&nbsp;</label>
		<div class="col-sm-10" id="comfort">
			<?php echo break_columns($connect, "comfort", 5, $row["id_comfort"], "WHERE type=0 ORDER BY name"); ?>
		</div>
	</div>
    <div class="form-group form-image-group">
        <label class="col-sm-2 control-label">Фото</label>
        <div class="col-sm-10">
        	<input type="text" class="form-control" name="imageurl" value="" style="width: 90%; display: inline-block;" placeholder="вставьте url изображения"><button class="get_img_from_url" >> > ></button><br><br>
            <input type="file" class="form-control" name="image" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'image'))));?>">
            <div class="input-message-block" data-for="image"></div>
        </div>
    </div>
	<div class="form-group">
		<div class="col-sm-offset-8 col-sm-4">
			<button type="button" class="btn btn-success btn-sm" onclick="update_room('<?php echo $id; ?>',<?=$manager?'true':'false'?>)"><i class="fa fa-check-circle"></i> Сохранить</button>
			<button type="button" class="btn btn-danger btn-sm" onclick="$('.edit-room').remove()"><i class="fa fa-times-circle"></i> Отмена</button>
		</div>
	</div>
</div>
</td></tr>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_room($connect){
	//Используется в СРМ и кабинете объекта!
	$id = (int)$_POST["id"];
	$note = $_POST["note"];
	$main_place = $_POST["main_place"];
	$add_place = $_POST["add_place"];
	$wo_bed_place = $_POST["wo_bed_place"];
	$comfort = $_POST["comfort"];
	$best_comfort = $_POST["best_comfort"];
	$name_room = $_POST["name_room"];
	$housing = (int)$_POST["housing"];
	$square = $_POST["square"];
	$food = $_POST["food"];
	//f (!isset($_POST["id_obj"])) {
		$entity = [
			'id' => $id,
			'type' => 'room'
		];
		$boundsArrayImage = files_to_bounds($connect,$entity,'image',isset($_POST['image'])?$_POST['image']:[]);
		remove_bounds($connect,$entity,'image');
		set_bounds($connect,$boundsArrayImage,'image');
	//}
    if (isset($_POST["id_obj"])) $connect->query("UPDATE room SET name=?s, id_comfort=?s, id_best_comfort=?s, note=?s, main_place=?i, add_place=?i, wo_bed_place=?i, housing=?s, food=?s, square=?s, synchronized = 0 WHERE id=?i and id_obj=?i", $name_room, $comfort, $best_comfort, $note, $main_place, $add_place, $wo_bed_place, $housing, $food, $square, $id, $_POST["id_obj"]);
	else $connect->query("UPDATE room SET name=?s, id_comfort=?s, id_best_comfort=?s, note=?s, main_place=?i, add_place=?i, wo_bed_place=?i, housing=?s, food=?s, square=?s, synchronized = 0 WHERE id=?i", $name_room, $comfort, $best_comfort, $note, $main_place, $add_place, $wo_bed_place, $housing, $food, $square, $id);
	sync_objects_api($connect);
}

function deletetldata($connect) {
	$client = new \GuzzleHttp\Client(['verify' => false]);
	$id = $_POST["id"];
	$res = $client->request('GET',"https://xn----7sba6aaba8akdsdekah.xn--p1ai/api/travelline_clear?id=$id&hash=".md5($id.'TGhioysdByutsadkjzbxcmn13'),[]);
	echo '<pre>';
	print_r($res->getBody()->getContents());
	echo '</pre>';
	echo "UPDATE object SET check_places=0 WHERE id=$id<br>";
	echo "UPDATE room SET accessible_places='', price_places='' WHERE id_obj=$id";
	$connect->query("UPDATE object SET check_places=0 WHERE id=$id");
	$connect->query("UPDATE room SET accessible_places='', price_places='' WHERE id_obj=$id");
}

function object_check_archive($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$connect->query("UPDATE object SET active=?i, synchronized=0 WHERE id=?i", $status, $id);
}

function room_check_archive($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	if (isset($_POST["id_obj"])) $active = $connect->getOne("SELECT active FROM room WHERE id=?i and id_obj=?i", $id, $_POST["id_obj"]);
	else $active = $connect->getOne("SELECT active FROM room WHERE id=?i", $id);
	$new = 0;
	if($active == 0)
		$new = 1;
	if (isset($_POST["id_obj"])) $connect->query("UPDATE room SET active=?i, synchronized = 0 WHERE id=?i and id_obj=?i", $new, $id, $_POST["id_obj"]);
	else $connect->query("UPDATE room SET active=?i, synchronized = 0 WHERE id=?i", $new, $id);
	sync_objects_api($connect);
	return $new;
}

function delete_room($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	if (isset($_POST["id_obj"])) $connect->query("DELETE FROM room WHERE id=?i and id_obj=?i", $id, $_POST["id_obj"]);
	else $connect->query("DELETE FROM room WHERE id=?i", $id);
}

function select_object_housing($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, type FROM object WHERE id=?i", $id);
	$object = $row["name"];
	$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $row["type"]);
	$data = $connect->getAll("SELECT id, name, description FROM housing WHERE id_obj=?i", $id);
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-building-o"></i> Корпуса объекта «<?php echo $type." ".$object; ?>»</div>
	<div class="list-group">
	<?php foreach($data as $row){ ?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-3 control-label-element"><?php echo $row["name"]; ?></label>
				<div class="col-sm-7">
					<?php echo $row["description"]; ?>
				</div>
				<div class="col-sm-2">
					<button type="button" class="btn btn-default btn-xs" onclick="edit_housing('<?php echo $row['id']; ?>')"><i class="fa fa-pencil"></i> Изменить</button>
				</div>
			</div>
		</div>
	<?php } ?>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-info btn-sm" onclick="add_new_housing('<?php echo $id; ?>')"><i class="fa fa-plus"></i> Новый корпус</button>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function add_new_housing($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	return '
	<div class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
					<h4 class="modal-title">Добавить новый корпус</h4>
				</div>
				<div class="modal-body form-horizontal new-housing">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название корпуса</label>
						<div class="col-sm-8">
							<input type="text" class="form-control name-housing" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Описание</label>
						<div class="col-sm-8">
							<textarea class="form-control desc-housing"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success btn-sm" onclick="save_new_housing(\''.$id.'\')"><i class="fa fa-check-circle"></i> Сохранить</button>
				</div>
			</div>
		</div>
	</div>';
}

function save_new_housing($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	$name = $_POST["name"];
	$desc = $_POST["desc"];
	$connect->query("INSERT INTO housing(id_obj, name, description) VALUES (?i, ?s, ?s)", $id, $name, $desc);
	sync_objects_api($connect);
}

function edit_housing($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	if (isset($_POST["id_obj"])) $row = $connect->getRow("SELECT name, description FROM housing WHERE id=?i and id_obj=?i", $id, $_POST['id_obj']);
	else $row = $connect->getRow("SELECT name, description FROM housing WHERE id=?i", $id);
	$row['name'] = htmlspecialchars($row['name']);
	return '
	<div class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
					<h4 class="modal-title">Изменить корпус</h4>
				</div>
				<div class="modal-body form-horizontal edit-housing">
					<div class="form-group">
						<label class="col-sm-4 control-label">Название корпуса</label>
						<div class="col-sm-8">
							<input type="text" class="form-control name-housing" value="'.$row['name'].'">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Описание</label>
						<div class="col-sm-8">
							<textarea class="form-control desc-housing">'.$row["description"].'</textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success btn-sm" onclick="update_housing(\''.$id.'\')"><i class="fa fa-check"></i> Сохранить</button>
				</div>
			</div>
		</div>
	</div>';
}

function update_housing($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	$name = htmlspecialchars_decode($_POST["name"], ENT_NOQUOTES);
	$desc = $_POST["desc"];
	if (isset($_POST["id_obj"])) $connect->query("UPDATE housing SET name=?s, description=?s, synchronized = 0 WHERE id=?i and id_obj=?i", $name, $desc, $id, $_POST["id_obj"]);
	else $connect->query("UPDATE housing SET name=?s, description=?s, synchronized = 0 WHERE id=?i", $name, $desc, $id);
	sync_objects_api($connect);
}


function form_new_image($connect){
	global $directory;
	$id = $_POST["id"];
	$type = $_POST["type"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Загрузить новое фото</h4>
			</div>
			<div class="modal-body form-horizontal new-image">
			<?php if(file_exists($directory."/temp/".$type."/".$id.".jpg")){ ?>
				<div class="form-group">
					<div class="col-sm-12">
						<div class="alert alert-success">Фотография уже добавлена</div>
					</div>
				</div>
			<?php } ?>
				<div class="form-group">
					<div class="col-sm-12 center">
						<button type="button" class="btn btn-primary btn-sm" id="uploadButton"><i class="fa fa-file-image-o"></i> Выбрать фото</button>
					</div>
				</div>
				<div class="form-group view-photo" style="display: none">
					<div class="col-sm-12 center">
						<div style="margin: 0 auto; position: relative;" class="view-photo-div"><img /></div>
					</div>
					<div class="col-sm-12 center">
						<button type="button" class="btn btn-success btn-sm" onclick="upload_new_image('<?php echo $id; ?>', '<?php echo $type; ?>', this)"><i class="fa fa-check-circle"></i> Сохранить фото</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function view_images_object(){
	$id = $_POST["id"];
	$folder = "temp/object/".$id."/big";
	$folder_1280 = "temp/object/".$id."/1280/";
	$folder_open = opendir($folder);
	while($image = readdir($folder_open)){
		if(($image != '.') AND ($image != '..') AND ($image)){
			$uniq_id = uniqid();
			$url = $folder."/".$image."?id=".$uniq_id;
			$cut_image = str_replace(".jpg", "", $image);
			$style = "";
			if(!file_exists($folder_1280.$image))
				$style = "style='background: #F00;'";
			ob_start();
		?>
		<div class="col-sm-3 center image-<?php echo $cut_image; ?>">
			<img src="<?php echo $url; ?>" class="img-thumbnail" <?php echo $style; ?> />
			<p class="center">
				<button type="button" class="btn btn-danger btn-xs" onclick="remove_image_object('<?php echo $id; ?>', '<?php echo $cut_image; ?>')">&nbsp;<i class="fa fa-times-circle"></i>&nbsp;</button>
			</p>
		</div>
		<?php
			$html.= ob_get_clean();
		}
	}
	$html.= "<div class='clearfix'></div>";
	return $html;
}

function copy_room($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, id_obj, id_comfort, id_best_comfort FROM room WHERE id=?i", $id);
	$connect->query("INSERT INTO room(name, id_obj, id_comfort, id_best_comfort) VALUES(?s, ?i, ?s, ?s)", $row["name"]." копия", $row["id_obj"], $row["id_comfort"], $row["id_best_comfort"]);
}

function select_object_image($connect){
	global $directory;
	$object = $_POST["id"];
	$data = array();
	$data2 = [];
	$open = $directory."/temp/object/".$object."/840/";
	if(is_dir($open)){
		$fold = opendir($open);
		while($image = readdir($fold)){
			if(($image != ".") AND ($image != "..") AND ($image)){
			    $expl = explode("_",$image);
			    if(count($expl) > 1) {
                  $data2[$expl[0]] = $image;
                }
                else {
                  $data[] = $image;
                }
			}
		}
	}
	ksort($data2);
	foreach ($data2 as $image) {
	    $data[] = $image;
    }
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-bank"></i> Фото объекта «<?php echo get_object($connect, $object, "type"); ?>»</div>
	<div class="panel-body">
	<script>
		$('[data-toggle="tooltip"]').tooltip();
	</script>
	<?php if($data){
		foreach($data as $image){
	?>
		<div class="col-sm-3 faded">
			<div class="btn-wrapper">
				<button 
					class="btn btn-sm btn-danger" 
					onclick="confirm('Удалить изображение <?=$image?>?') 
							? remove_image_object('<?=$object?>', '<?=$image?>')
							: document.activeElement.blur();"
					data-toggle='tooltip' 
					data-placement='bottom' 
					title='Удалить изображение "<?=$image?>"'
					><i class="fa fa-remove fa-lg"></i></button>
				<button 
						class="btn btn-sm btn-primary" 
						data-toggle='tooltip' 
						data-placement='bottom' 
						title='Удерживайте для перемещения'
						><i class="fa fa-arrows fa-rotate-45 fa-lg"></i></button>
			</div>
			<img src="temp/object/<?=$object?>/840/<?=$image?>" class="img-thumbnail" />
		</div>
	<?php
		}
	?>
	<?php }else{ ?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> Фото объекта не добавлены</div>
	<?php } ?>
	</div>
</div>
<?php
}

function check_completeness_object($connect){
	$regions = $connect->getAll("SELECT id, name FROM region WHERE id_country=1");
	foreach($regions as $region){
		$class = "danger";
		if($connect->getOne("SELECT id FROM object WHERE id_reg=?i AND (active=0 OR active=1)", $region["id"]))
			$class = "default";
?>
	<div class="panel panel-<?php echo $class; ?>">
		<div class="panel-heading"><i class="fa fa-globe"></i> <?php echo $region["name"]; ?></div>
		<table class="table table-hover">
<?php
		$objects = $connect->getAll("SELECT id, id_profile, id_methods, id_infa, id_services, description, latitude, longitude, city, image FROM object WHERE id_reg=?i AND (active=0 OR active=1)", $region["id"]);
		foreach($objects as $object){
			if(!$object["id_profile"] OR !$object["id_methods"] OR !$object["id_infa"] OR !$object["id_services"] OR !$object["id_services"] OR !$object["description"] OR !$object["image"] OR !$object["latitude"] OR !$object["longitude"] OR !$object["city"]){
				$name = get_object($connect, $object["id"], "place");
				$show_room = 0;
				if($connect->getOne("SELECT id FROM room WHERE id_obj=?i", $object["id"]))
					$show_room = 1;
?>
		<tr onclick="select_menu_object('<?php echo $object['id']; ?>')">
			<td width="60%"><?php echo $name; ?></td>
			<td width="5%">
				<?php if($show_room == 0){ ?>
				<i class="fa fa-2x fa-warning text-danger" data-toggle="tooltip" data-placement="top" title="Отсутствует номерной фонд"></i>
				<?php } ?>
			</td>
			<td width="5%">
				<?php if(!$object["id_profile"]){?><i class="fa fa-2x fa-times-circle text-danger" data-toggle="tooltip" data-placement="top" title="Профили лечения"></i><?php }else{ ?><i class="fa fa-2x fa-check-circle text-success" data-toggle="tooltip" data-placement="top" title="Профили лечения"></i><?php } ?>
			</td>
			<td width="5%">
				<?php if(!$object["id_methods"]){?><i class="fa fa-2x fa-times-circle text-danger" data-toggle="tooltip" data-placement="top" title="Методы лечения"></i><?php }else{ ?><i class="fa fa-2x fa-check-circle text-success" data-toggle="tooltip" data-placement="top" title="Методы лечения"></i><?php } ?>
			</td>
			<td width="5%">
				<?php if(!$object["id_infa"]){?><i class="fa fa-2x fa-times-circle text-danger" data-toggle="tooltip" data-placement="top" title="Инфраструктура"></i><?php }else{ ?><i class="fa fa-2x fa-check-circle text-success" data-toggle="tooltip" data-placement="top" title="Инфраструктура"></i><?php } ?>
			</td>
			<td width="5%">
				<?php if(!$object["id_services"]){?><i class="fa fa-2x fa-times-circle text-danger" data-toggle="tooltip" data-placement="top" title="Услуги объекта"></i><?php }else{ ?><i class="fa fa-2x fa-check-circle text-success" data-toggle="tooltip" data-placement="top" title="Услуги объекта"></i><?php } ?>
			</td>
			<td width="5%">
				<?php if(!$object["description"]){?><i class="fa fa-2x fa-times-circle text-danger" data-toggle="tooltip" data-placement="top" title="Описание объекта"></i><?php }else{ ?><i class="fa fa-2x fa-check-circle text-success" data-toggle="tooltip" data-placement="top" title="Описание объекта"></i><?php } ?>
			</td>
			<td width="5%">
				<?php if(!$object["latitude"] OR !$object["longitude"] OR !$object["city"]){?><i class="fa fa-2x fa-times-circle text-danger" data-toggle="tooltip" data-placement="top" title="Координаты и адрес"></i><?php }else{ ?><i class="fa fa-2x fa-check-circle text-success" data-toggle="tooltip" data-placement="top" title="Координаты и адрес"></i><?php } ?>
			</td>
			<td width="5%">
				<?php if(!$object["image"]){?><i class="fa fa-2x fa-times-circle text-danger" data-toggle="tooltip" data-placement="top" title="Главное фото"></i><?php }else{ ?><i class="fa fa-2x fa-check-circle text-success" data-toggle="tooltip" data-placement="top" title="Главное фото"></i><?php } ?>
			</td>
		</tr>
<?php
			}
		}
?>
		</table>
	</div>
<?php
	}
}

function edit_region($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, id_direction, id_country, description, meta_desc, man_reward_scheme, state_program, state_program_start_timestamp, state_program_end_timestamp FROM region WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить регион</h4>
			</div>
			<div class="modal-body form-horizontal edit-region">
				<div class="form-group">
					<label class="col-sm-4 control-label">Название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control name-region" value="<?php echo $row['name']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Описание</label>
					<div class="col-sm-8">
						<textarea class="form-control description-region"><?php echo $row["description"]; ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Направление</label>
					<div class="col-sm-8">
						<?php echo get_select_table($connect, "direction_object", "id_country=".$row["id_country"], $row["id_direction"], "direction-region", 1, ""); ?>
					</div>
				</div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Доп. вознаграждение менеджеру с заявки</label>
                    <div class="col-sm-8">
                        <select class="form-control man_reward_scheme">
                            <option value="0"<?php if($row['man_reward_scheme'] == 0) echo ' selected';?>>Нет</option>
                            <option value="1"<?php if($row['man_reward_scheme'] == 1) echo ' selected';?>>Да</option>
                        </select>
                      <?php ?>
                    </div>
                </div>
				<div class="form-group">
					<label class="col-sm-4 control-label">
						Meta-описание
						<div class="label-meta"></div>
					</label>
					<div class="col-sm-8">
						<textarea class="form-control meta-desc-region" onkeypress="check_size_limit('.meta-desc-region', 250, '.label-meta')"><?php echo $row["meta_desc"]; ?></textarea>
					</div>
				</div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Действие гос. программы</label>
                    <div class="col-sm-8">
                        <input type="checkbox" class="form-control state-program"<?php if($row['state_program']) { ?> checked<?php } ?>>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Начало действия гос. программы</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control state-program-start-timestamp" value="<?=gmdate('Y-m-d', $row['state_program_start_timestamp']+3600*3);?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Окончание действия гос. программы</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control state-program-end-timestamp" value="<?=gmdate('Y-m-d', $row['state_program_end_timestamp']+3600*3);?>">
                    </div>
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_region('<?php echo $id; ?>')"><i class="fa fa-check"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_region($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$direction = $_POST["direction"];
	$description = strip_tags($_POST["description"]);
	$meta_desc = strip_tags($_POST["meta_desc"]);
	$man_reward_scheme = (int)$_POST['man_reward_scheme'];
	$state_program = isset($_POST['state_program']) ? (int)$_POST['state_program'] : 0;
	$state_program_start_timestamp = (isset($_POST['state_program_start_timestamp']) && trim($_POST['state_program_start_timestamp'])) ? strtotime($_POST['state_program_start_timestamp']) : 0;
    $state_program_end_timestamp = (isset($_POST['state_program_end_timestamp']) && trim($_POST['state_program_end_timestamp'])) ? strtotime($_POST['state_program_end_timestamp']) : 0;

	if($state_program)
	    $state_program = 1;
	else {
        $state_program = 0;
    }

	if($state_program_start_timestamp > $state_program_end_timestamp) {
	    $state_program_end_timestamp = $state_program_start_timestamp;
    }

	$connect->query("UPDATE region SET name=?s, description=?s, meta_desc=?s, man_reward_scheme=?i, state_program = ?i, state_program_start_timestamp = ?i, state_program_end_timestamp = ?i, synchronized = 0 WHERE id=?i", $name, $description, $meta_desc, $man_reward_scheme, $state_program, $state_program_start_timestamp, $state_program_end_timestamp, $id);
	if($direction)
		$connect->query("UPDATE region SET id_direction=?i, synchronized = 0 WHERE id=?i", $direction, $id);
}

function edit_direction($connect){
	$id = $_POST["id"];
	$type = $_POST["type"];
	if(!$id)
		return FALSE;
	$row = $connect->getRow("SELECT name, name_rod, description, meta_desc, sort FROM direction_object WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить направление</h4>
			</div>
			<div class="modal-body form-horizontal edit-direction">
				<div class="form-group">
					<label class="col-sm-4 control-label">Название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control name-direction" value="<?php echo $row['name']; ?>" />
					</div>
				</div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Название в род. падеже</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control name-direction-rod" value="<?=$row['name_rod'];?>" />
                    </div>
                </div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Описание</label>
					<div class="col-sm-8">
						<textarea class="form-control description-direction"><?php echo $row["description"]; ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Meta-описание</label>
					<div class="col-sm-8">
						<textarea class="form-control meta-desc-direction"><?php echo $row["meta_desc"]; ?></textarea>
					</div>
				</div>
                <div class="form-group form-group-margin">
                    <label class="col-sm-4 control-label">Вес (сортировка)</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control sort-direction" value="<?=$row['sort'];?>" />
                    </div>
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_direction(<?php echo $id; ?>, '<?php echo $type; ?>')"><i class="fa fa-check"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_direction($connect){
	$id = $_POST["id"];
	$name = trim($_POST["name"]);
	$name_rod = trim($_POST['name_rod']);
	$sort = (int)$_POST['sort'];
	$description = strip_tags($_POST["description"]);
	$meta_desc = strip_tags($_POST["meta_desc"]);
	$connect->query("UPDATE direction_object SET name=?s, name_rod = ?s, description=?s, meta_desc=?s, sort = ?i, `synchronized` = 0 WHERE id=?i", $name, $name_rod, $description, $meta_desc, $sort, $id);
}

function create_uniq_link_object($connect){
	$object = $_POST["id"];

	/*if($connect->getOne("SELECT id FROM object WHERE url_name !='' AND id=?i", $object))
		return;*/

	$name = $connect->getOne("SELECT name FROM object WHERE id=?i", $object);
	$url = change_text_url($name, "object");
	if(!$connect->getOne("SELECT id FROM object WHERE url_name=?s", $url)){
		$connect->query("UPDATE object SET url_name=?s, synchronized=0 WHERE id=?i", $url, $object);
		return 1;
	}
}

function show_object_qouta_admin($connect){
	$array = array();
	$data = $connect->getAll("SELECT id, check_places FROM object WHERE check_places!=0");
	foreach($data as $row){
		$id = $row["id"];
		$array[$id] = array();
		$array[$id]["name"] = get_object($connect, $id, "place");
		$array[$id]["check"] = $row["check_places"];
	}
	return json_encode($array);
}

function update_status_qouta_object($connect){
	$object = $_POST["object"];
	$status = $_POST["status"];
	// $connect->query("UPDATE object SET check_places=?i WHERE id=?i", $status, $object);
	$name = "";

    if ($status == 3) {
	    $id = $_POST["id"];
	    $current = $connect->getOne("SELECT check_places FROM object WHERE id=?i", $object);
        if($id AND $current == 0){
            $connect->query("UPDATE object SET check_places=3, sync_id=?i, synchronized=0 WHERE id=?i", $id, $object);
            $name = get_object($connect, $object, "place");
        } else {
            $name = "Данный санаторий уже выгружает квоту через другой канал";
        }
    } else {
        $connect->query("UPDATE object SET check_places=?i, synchronized=0 WHERE id=?i", $status, $object);
    }
    return json_encode($name);
}

function select_object_rate_plan($connect){
	$object = $_POST["object"];
	$array = array();
	$data = $connect->getAll("SELECT id, name, description, food, status FROM rate_plan WHERE object=?i ORDER BY status DESC", $object);
	foreach($data as $row){
		$id = $row["id"];
		$array[] = [
		  'id' => $id,
          "name" => $row["name"],
          "food" => $row["food"],
          "description" => $row["description"],
          "status" => $row['status']
        ];

	}
	return json_encode($array);
}

function save_new_rate_plan($connect){
	$object = $_POST["object"];
	$name = $_POST["name"];
	$connect->query("INSERT INTO rate_plan(object, name, description) VALUES (?i, ?s, '')", $object, $name);
}

function edit_rate_plan($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, description, food, start_date, end_date, min_days, max_days, status FROM rate_plan WHERE id=?i", $id);
	return json_encode($row);
}

function update_rate_plan($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$desc = $_POST["desc"];
	$food = $_POST["food"];
	$min_days = (int)$_POST["min_days"];
    $max_days = (int)$_POST["max_days"];
    $start_date = strtotime($_POST["start_date"]);
    $end_date = strtotime($_POST["end_date"]);

    if($end_date < $start_date) {
        $end_date = null;
    }
    elseif($end_date > 0) {
        $end_date = date('Y-m-d', $end_date);
    }
    else {
        $end_date = null;
    }

    if($start_date > 0) {
        $start_date = date('Y-m-d', $start_date);
    }
    else {
        $start_date = null;
    }

    if($min_days < 0) {
        $min_days = 0;
    }

    if($max_days < 0) {
        $max_days = 0;
    }

    if($max_days && $max_days < $min_days) {
        $max_days = null;
    }

    if(!$min_days) {
        $min_days = null;
    }

    if(!$max_days) {
        $max_days = null;
    }

	$status = (int)$_POST['status'];
	if($status !== 0 && $status !== 1)
	    $status = 1;

	$connect->query("UPDATE rate_plan SET name=?s, description=?s, food=?s, min_days=?s, max_days=?s, start_date=?s, end_date=?s, status = ?i, synchronized = 0 WHERE id=?i", $name, $desc, $food, $min_days, $max_days, $start_date, $end_date, $status, $id);
}




function show_obj_cert($connect){
	//Используется в СРМ и кабинете объекта!
	$id = $_POST["id"];
	$obj = $connect->getRow("SELECT * FROM object WHERE id=?i", $id);

	if ($_POST['clear_accr']=='1') {
		$connect->query("UPDATE `accr_data` SET `id_obj`=?i WHERE id_obj=?i", 0, $id);
		$connect->query("UPDATE `object` SET `accr_id`='', `accr_data`='', `synchronized`=0 WHERE id=?i", $id);
	}

	if ($_POST['id_accr']!='') {
		$accr_data = $connect->getRow("SELECT * FROM accr_data WHERE id=?i", $_POST['id_accr']);
		$accr_data['data'] = json_decode($accr_data['data'], true);

		$obj_accr_data = [];

        if (isset($accr_data['data']['hotel']['main']['registerRecord']) && trim($accr_data['data']['hotel']['main']['registerRecord'])!='') {
            $obj_accr_data['registerRecord'] = $accr_data['data']['hotel']['main']['registerRecord'];
        }

        if (isset($accr_data['data']['hotel']['main']['status']['name']) && trim($accr_data['data']['hotel']['main']['status']['name'])!='') {
            $obj_accr_data['status'] = $accr_data['data']['hotel']['main']['status']['name'];
        }    

        if (isset($accr_data['data']['hotel']['main']['status']['endDate']) && trim($accr_data['data']['hotel']['main']['status']['endDate'])!='') {
            $obj_accr_data['endDate'] = $accr_data['data']['hotel']['main']['status']['endDate'];
        }        
		
		$connect->query("UPDATE `accr_data` SET `id_obj`=?i WHERE id=?i", $id, $accr_data['id']);
		$connect->query("UPDATE `object` SET `accr_id`=?s, `accr_data`=?s, `synchronized`=0 WHERE id=?i", $accr_data['ext_id'], json_encode($obj_accr_data), $id);

	}

	$obj = $connect->getRow("SELECT * FROM object WHERE id=?i", $id);

	if (trim($obj['accr_data'])!='') {
		$obj['accr_data'] = json_decode($obj['accr_data'], true);
		$accr_data = $connect->getRow("SELECT * FROM accr_data WHERE ext_id=?s", $obj['accr_id']);
		$accr_data['data'] = json_decode($accr_data['data'], true);
		$endDate = '';
		if (trim($obj['accr_data']['endDate'])!='') $endDate = date('d.m.Y', strtotime($obj['accr_data']['endDate']));
		ob_start();
		?>
		<div class="panel panel-default">
			<div class="panel-heading">Сертификация</div>
			<table class="table tbl-room">
				<tbody><tr>
					<td>
						Название объекта в реесте: <?=$obj['name']?> (<?=$obj['full_name']?>)<br>
						Адрес объекта в реесте: <?=$obj['address']?><br><br>
						Название объекта в СРМ: <?=$accr_data['name']?><br>
						Адрес объекта в СРМ: <?=$accr_data['address']?><br><br>						
						<strong>Выбранные данные сертификации:</strong><br>
						Номер реестровой записи: <?=$obj['accr_data']['registerRecord']?><br>
						Статус: <?=$obj['accr_data']['status']?><br>
						до: <?=$endDate?><br><br>
						<button type="button" class="btn btn-danger btn-sm" onclick="del_obj_cert()"><i class="fa fa-close"></i> Удалить данные</button>
					</td>
				</tr>
				</tbody>
			</table>
			<div class="panel-footer" style="text-align: left">
				<strong>Полные данные из tourism.fsa.gov.ru:</strong>
				<pre><?php print_r($accr_data['data']); ?></pre>
			</div>
		</div>		
		<?php		
		$html = ob_get_clean();
	} else {

		$search_html = '';

		if ($_POST['accr_search']!='') {
			$q = str_replace(' ', '%', $_POST['accr_search']);
			$accr_data = $connect->getAll("SELECT * FROM accr_data WHERE `search` LIKE '%$q%'");
			
			if (count($accr_data)>0) {
				$search_html .= '<table class="table tbl-room">'; 
				foreach ($accr_data as $accr_data_item) {
					$accr_data_item['data'] = json_decode($accr_data_item['data'], true);
					$cert = 'Запись: '.$accr_data_item['data']['hotel']['main']['registerRecord'].'<br>';
					$cert .= 'Статус: '.$accr_data_item['data']['hotel']['main']['status']['name'].'<br>';
					$cert .= 'До: '.$accr_data_item['data']['hotel']['main']['status']['endDate'].'<br>';
					$search_html .= '<tr>'; 
					$search_html .= '<td>'; 
					$search_html .= $accr_data_item['name']; 
					$search_html .= '</td>'; 
					$search_html .= '<td>'; 
					$search_html .= $accr_data_item['address']; 
					$search_html .= '</td>'; 					
					$search_html .= '<td width="250">';
					$search_html .= $cert; 
					$search_html .= '</td>';
					$search_html .= '<td><button type="button" class="btn btn-primary btn-sm" onclick="select_obj_cert('.$accr_data_item['id'].')">Выбрать</button></td>'; 										
					$search_html .= '</tr>'; 
				}
				$search_html .= '</table>'; 
			}
		}

		if ($_POST['accr_search']=='') $_POST['accr_search'] = $obj['name'];
		if ($_POST['accr_search']=='') $_POST['accr_search'] = $obj['full_name'];
		ob_start();
		?>
		<div class="panel panel-default">
			<div class="panel-heading">Сертификация - поиск данных</div>
			<table class="table tbl-room">
				<tbody><tr>
					<td>
						<input type="text" id="accr_search" class="form-control" style="width: 50%; display: inline-block; vertical-align: middle;"; placeholder="введите текст для поиска" value="<?=$_POST['accr_search']?>"> &nbsp; <button type="button" class="btn btn-primary btn-sm" onclick="search_obj_cert()" style="display: inline-block; vertical-align: middle; margin-bottom: 0px;"><i class="fa fa-search"></i> поиск</button>
					</td>
				</tr>
				</tbody>
			</table>
			<?=$search_html?>
		</div>		
		<?php		
		$html = ob_get_clean();
	}

	
	return $html;
}

?>
