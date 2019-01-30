<?php

function show_methods($connect){
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-user-md"></i> Методы лечения</div>
	<div class="list-group">
<?php
	$data = $connect->getAll("SELECT id, name, description FROM methods ORDER BY name");
	foreach($data as $row){
		$id = $row["id"];
		$image = "temp/method/".$id.".jpg";
		if(!file_exists($image))
			$image = "temp/defaul.jpg";
	?>
	<div class="list-group-item method-<?php echo $id; ?>">
		<div class="form-group">
			<div class="col-sm-3">
				<img src="<?php echo $image; ?>" class="img-head-small pointer" onclick="add_image_method('<?php echo $id; ?>')" />
				<span class="name"><?php echo $row["name"]; ?></span>
			</div>
			<div class="col-sm-7 desc">
				<?php echo $row["description"]; ?>
			</div>
			<div class="col-sm-2 text-center">
				<button type="button" class="btn btn-default btn-sm" onclick="edit_method('<?php echo $id; ?>')"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-danger btn-sm" onclick="delete_method('<?php echo $id; ?>')"><i class="fa fa-times"></i></button>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<?php
	}
?>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function edit_method($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, description FROM methods WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить метод лечения</h4>
			</div>
			<div class="modal-body form-horizontal edit-method">
				<div class="form-group">
					<label class="col-sm-4 control-label">Название</label>
					<div class="col-sm-8">
						<input type="text" class="form-control name-method" value="<?php echo $row['name']; ?>" />
					</div>
				</div>
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Описание</label>
					<div class="col-sm-8">
						<textarea class="form-control desc-method"><?php echo $row['description']; ?></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_method('<?php echo $id; ?>')"><i class="fa fa-check"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_method($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$desc = $_POST["desc"];
	$connect->query("UPDATE methods SET name=?s, description=?s WHERE id=?i", $name, $desc, $id);
}

function delete_method($connect){
	$id = $_POST["id"];
	$data = $connect->getAll("SELECT id, id_methods FROM object WHERE id_methods LIKE '%?i%'", $id);
	foreach($data as $row){
		$object = $row["id"];
		$methods = explode("_", $row["id_methods"]);
		$change = 0;
		foreach($methods as $index => $method){
			if($method == $id){
				unset($methods[$index]);
				$change = 1;
			}
		}
		if($change == 1)
			$connect->query("UPDATE object SET id_methods=?s, synchronized=0 WHERE id=?i", implode("_", $methods), $object);
	}
	$connect->query("DELETE FROM methods WHERE id=?i", $id);
}

?>
