<?php

function show_admin_search(){
?>
	<button type="button" class="btn btn-info btn-sm" onclick="select_object_non_region()"><i class="fa fa-search"></i> Поиск новых объектов</button>
	<button type="button" class="btn btn-warning btn-sm" onclick="select_similar_client_admin()"><i class="fa fa-search"></i> Поиск похожих туристов</button>
	<button type="button" class="btn btn-warning btn-sm" onclick="select_similar_name_object()"><i class="fa fa-search"></i> Поиск одинаковых объектов</button>
	<div id="result" style="margin-top: 10px"></div>
<?php
}

function select_object_non_region($connect){
?>
<div class="list-group form-horizontal">
<?php
	$data = $connect->getAll("SELECT id, name FROM object WHERE id_reg IS NULL OR id_reg=''");
	foreach($data as $object){
		$id = $object["id"];
		$name = str_replace(",", " ", $object["name"]);
		$name = str_replace(".", " ", $name);


?>
	<div class="list-group-item object-<?php echo $id; ?>">
		<div class="form-group">
			<div class="col-sm-9">
				<?php echo $object["name"]; ?>
			</div>
			<div class="col-sm-1">
				<button class="btn btn-default btn-sm" onclick="edit_object_admin('<?php echo $id; ?>')"><i class="fa fa-pencil"></i></button>
			</div>
			<div class="col-sm-1">
				<?php if($connect->getOne("SELECT id FROM object WHERE MATCH(name) AGAINST (?s) AND id!=?i", $name, $id)){ ?>
				<button class="btn btn-warning btn-sm" onclick="check_object('<?php echo $id; ?>')"><i class="fa fa-exclamation-triangle"></i></button>
				<?php }else{ ?>
				<span class="btn btn-success btn-sm"><i class="fa fa-check-circle"></i></span>
				<?php } ?>
			</div>
			<div class="col-sm-1">
				<?php if(!$connect->getOne("SELECT id FROM reckoning WHERE id_obj=?i", $id)){ ?>
					<button class="btn btn-danger btn-sm" onclick="delete_object('<?php echo $id; ?>')"><i class="fa fa-trash-o"></i></button>
				<?php } ?>
			</div>
		</div>
	</div>
<?php
	}
?>
</div>
<?php
}

function edit_object_admin($connect){
	$id = $_POST["id"];
	$object = $connect->getRow("SELECT id_reg, name FROM object WHERE id=?i", $id);
	$id_reg = $object["id_reg"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить объект</h4>
			</div>
			<div class="modal-body form-horizontal edit-object">
				<div class="form-group">
					<label class="col-sm-4 control-label">Объект</label>
					<div class="col-sm-8">
						<input type="text" class="form-control name-object" value="<?php echo $object['name']; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Страна</label>
					<div class="col-sm-8">
						<?php echo get_select_table($connect, "country", "", "", "id-country", "", "onchange='select_region_admin()'"); ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Регион</label>
					<div class="col-sm-8" id="regions">
						<?php echo get_select_table($connect, "region", "id_country=1", "", "id-region", "", ""); ?>
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Город</label>
					<div class="col-sm-8">
						<input type="text" class="form-control city-object" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_object_admin('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function select_region_country($connect){
	$country = $_POST["country"];
	return get_select_table($connect, "region", "id_country=".$country, "", "id-region", "", "");
}


function update_object_admin($connect){
	$connect->query("UPDATE object SET name=?s, id_reg=?i, city=?s, synchronized=0 WHERE id=?i", $_POST["name"], $_POST["region"], $_POST["city"], $_POST["id"]);
}

function check_object($connect){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Похожие объекты</h4>
			</div>
			<div class="form-horizontal similar-object">
				<div class="list-group form-group-margin">
<?php
	$id = $_POST["id"];
	$name = str_replace(",", " ", $connect->getOne("SELECT name FROM object WHERE id=?i", $id));
	$name = str_replace(".", " ", $name);
	$data = $connect->getAll("SELECT id FROM object WHERE MATCH(name) AGAINST (?s) AND id!=?i", $name, $id);
	foreach($data as $object){
		$name = get_object($connect, $object["id"]);
		$count = count($connect->getAll("SELECT id FROM reckoning WHERE id_obj=?i", $object["id"]));
?>
			<label class="list-group-item">
				<input type="radio" name="object-radio" value="<?php echo $object['id']; ?>" />
				<?php echo $name; ?>
			</label>
<?php
	}
?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_to_new_object('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Объединить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function unite_objects($connect){
	$id = $_POST["id"];
	$new_id = $_POST["new_id"];
	$connect->query("UPDATE reckoning SET id_obj=?i WHERE id_obj=?i", $new_id, $id);
	$connect->query("UPDATE room SET id_obj=?i, synchronized = 0 WHERE id_obj=?i", $new_id, $id);
	$connect->query("UPDATE rating SET id_obj=?i, synchronized = 0 WHERE id_obj=?i", $new_id, $id);
}

function select_similar_client_admin($connect){
	$data = $connect->getAll("SELECT id, surname, name, otch, telephone, email, login, unlike FROM klient");
	$count = 0;
	$html = "";
	foreach($data as $row){
		$id = $row["id"];
		$login = $row["login"];
		$unlike = $row["unlike"];
		$query = "SELECT id, surname, name, otch, email, telephone, date, login FROM klient WHERE (id!=?i) AND ((surname=?s AND name=?s AND otch=?s)";
		if($row["email"])
			$query.= " OR (email='".$row["email"]."')";
		if($row["telephone"])
			$query.= " OR (telephone='".$row["telephone"]."')";
		$query.= ")";
		if($row["login"])
			$query.= " AND (login='' OR login is NULL)";
		if($unlike){
			$unlike = json_decode($unlike, TRUE);
			$unlike_query = "";
			foreach($unlike as $id_unlike){
				$unlike_query.= " id!=".$id_unlike;
			}
			if($unlike_query)
				$query.= " AND (".$unlike_query.")";
		}
		$similar = $connect->getAll($query, $id, $row["surname"], $row["name"], $row["otch"]);
		$similar_html = "";
		foreach($similar as $like){
			$color = array();
			if($like["surname"] == $row["surname"])
				$color["surname"] = "color: red";
			if($like["telephone"] == $row["telephone"])
				$color["telephone"] = "color: red";
			if($like["email"] == $row["email"])
				$color["email"] = "color: red";
			$object = "";
			$objects = $connect->getAll("SELECT id_obj FROM reckoning WHERE turist=?i", $like["id"]);
			foreach($objects as $id_obj){
				if($object)
					$object.= ", ";
				$object.= get_object($connect, $id_obj["id_obj"]);
			}
			$login = "";
			if($like["login"])
				$login = "&nbsp;&nbsp;<i class='fa fa-user'></i>";
			$similar_html.= "<tr>";
			$similar_html.= "<td style='width: 450px;".$color["surname"]."'>".$like["surname"]." ".$like["name"]." ".$like["otch"]."</td>";
			$similar_html.= "<td style='width: 100px;".$color["email"]."'>".$like["email"]."</td>";
			$similar_html.= "<td style='width: 100px;".$color["telephone"]."'>".$like["telephone"]."</td>";
			$similar_html.= "<td style='width: 100px'>".$object."</td>";
			$similar_html.= "<td style='width: 50px'><input type='radio' name='sim-klient-".$id."' value='".$like["id"]."' /></td>";
			$similar_html.= "</tr>";
		}
		if(count($similar) > 0){
			$object = "";
			$objects = $connect->getAll("SELECT id_obj FROM reckoning WHERE turist=?i", $id);
			foreach($objects as $id_obj){
				if($object)
					$object.= ", ";
				$object.= get_object($connect, $id_obj["id_obj"]);
			}
			ob_start();
?>
	<div class="form-horizontal panel panel-info similar-client-<?php echo $id; ?>" style="width: 800px">
		<div class="panel-heading">
			<?php echo $row["surname"]." ".$row["name"]." ".$row["otch"]; ?>
			<?php if($row["login"]){ ?>
				&nbsp;&nbsp;<i class="fa fa-user"></i>
			<?php } ?>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-sm-2 control-label">Телефон</label>
				<div class="col-sm-4">
					<div class="well well-sm"><?php echo $row["telephone"]; ?>&nbsp;</div>
				</div>
				<label class="col-sm-2 control-label">Email</label>
				<div class="col-sm-4">
					<div class="well well-sm"><?php echo $row["email"]; ?>&nbsp;</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Отдыхал</label>
				<div class="col-sm-4">
					<div class="well well-sm"><?php echo $object; ?>&nbsp;</div>
				</div>
			</div>
		</div>
		<table class="table">
			<?php echo $similar_html; ?>
		</table>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-success btn-xs" onclick="unite_client_admin('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Объединить</button>
			<button type="button" class="btn btn-warning btn-xs" onclick="unlike_client_admin('<?php echo $id; ?>')"><i class="fa fa-exchange"></i> Не похожие</button>
		</div>
	</div>
<?php
			$html.= ob_get_clean();
			$count++;
		}
		if($count >= 5)
			return $html;
	}
	return $html;
}

function select_similar_name_object($connect){
	$data = $connect->getAll("SELECT name FROM object GROUP BY name HAVING COUNT(name) > 1");
	foreach($data as $row){
		$name = $row["name"];
		$objects = $connect->getAll("SELECT id FROM object WHERE name=?s", $name);
?>
	<div class="panel panel-default list-group">
		<div class="panel-heading"><h4><?php echo $name; ?></h4></div>
	<?php foreach($objects as $object){ ?>
		<div class="list-group-item">
			<?php echo get_object($connect, $object["id"], "place"); ?>
		</div>
	<?php } ?>
	</div>
<?php
	}
}

function delete_object_admin($connect){
	$id = $_POST["id"];
	$connect->query("DELETE FROM object WHERE id=?i", $id);
}

?>
