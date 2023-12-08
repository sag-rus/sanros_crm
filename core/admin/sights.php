<?php

function show_sights_menu(){
	ob_start();
?>
	<ul class="nav nav-tabs menu-sights">
		<li onclick="add_new_sight()" class="new-sight"><a><i class="fa fa-plus-circle"></i> Новое место</a></li>
		<li onclick="view_sights()" class="view-sights"><a><i class="fa fa-university"></i> Все места</a></li>
		<li onclick="upload_sights1()" class="upload-sights"><a><i class="fa fa-upload"></i> Загрузить</a></li>
	</ul>
	<div class="sights-content" style="padding-top: 10px"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function add_new_sight(){
	global $connect;
	ob_start();
	$regions = $connect->getAll("SELECT id,name FROM direction_object ORDER BY name");
?>
	<div class="form-horizontal panel panel-default add-new-sight">
		<div class="panel-heading"><i class="fa fa-plus-circle"></i> Новое место</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-sm-3 control-label">Название</label>
				<div class="col-sm-9">
					<input type="text" class="form-control name" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Фотография для списка</label>
				<div class="col-sm-9">
					<input type="file" class="form-control" name="image" value="">
					<div class="input-message-block" data-for="image"></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Фотография для верха страницы</label>
				<div class="col-sm-9">
					<input type="file" class="form-control" name="slider" value="">
					<div class="input-message-block" data-for="slider"></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Фотогалерея</label>
				<div class="col-sm-9">
					<input type="file" class="form-control" name="photogallery" value="">
					<div class="input-message-block" data-for="photogallery"></div>
				</div>
			</div>			
			<div class="form-group">
				<label class="col-sm-3 control-label">Описание</label>
				<div class="col-sm-9">
					<textarea class="form-control description"></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Адрес</label>
				<div class="col-sm-9">
					<input type="text" class="form-control address" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Широта</label>
				<div class="col-sm-9">
					<input type="text" class="form-control latitude" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Долгота</label>
				<div class="col-sm-9">
					<input type="text" class="form-control longitude" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Расположение</label>
				<div class="col-sm-9">
					<select class="form-control place">
						<option value="0"></option>
						<?php
						foreach ($regions as $region) {
							?><option value="<?=$region['id']?>"><?=$region['name']?></option><?php
						}
						?>
					</select>
				</div>
			</div>			
		</div>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-success btn-sm" onclick="save_sight()"><i class="fa fa-check-circle"></i> Сохранить</button>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_sight($connect){
	$name = $_POST["name"];
	$description = $_POST["description"];
	$address = $_POST["address"];
	$latitude = $_POST["latitude"];
	$longitude = $_POST["longitude"];
	$place = $_POST["place"];
	$connect->query("INSERT INTO sights(name, description, address, latitude, longitude, place) VALUES (?s, ?s, ?s, ?s, ?s, ?i)", $name, $description, $address, $latitude, $longitude, $place);

	$entity = [
		'id' => $connect->insertId(),
		'type' => 'sights'
	];

	$boundsArrayImage = [];
	$boundsArraySlider = [];
	$boundsArrayPhotoGallery = [];

	$boundsArrayImage = files_to_bounds($connect,$entity,'image',isset($_POST['image'])?$_POST['image']:[]);
	$boundsArraySlider = files_to_bounds($connect,$entity,'slider',isset($_POST['slider'])?$_POST['slider']:[]);
	$boundsArrayPhotoGallery = files_to_bounds($connect,$entity,'photogallery',isset($_POST['photogallery'])?$_POST['photogallery']:[]);

	set_bounds($connect,$boundsArrayImage,'image');
	set_bounds($connect,$boundsArraySlider,'slider');
	set_bounds($connect,$boundsArrayPhotoGallery,'photogallery');
}

function view_sights($connect){
	$data = $connect->getAll("SELECT * FROM sights");
	ob_start();
?>
	<div class="form-horizontal">
		<div class="form-group form-group-margin">
<?php
	foreach($data as $row){
		$region = $connect->getRow("SELECT id,name FROM direction_object WHERE id=?i", $row['place']);
		$id = $row["id"];
		$entity = $row;
		$entity['type'] = 'sights';

		$image = bounds_to_files($connect,load_bounds($connect,$entity,'image'));

?>
	<div class="col-sm-6 sight-<?php echo $id; ?>">
		<div class="panel panel-info">
			<div class="panel-heading"><i class="fa fa-university"></i> <?php echo $row["name"]; ?></div>
			<div class="panel-body">
				<?php echo $row["description"]; ?>
				<div class="well well-sm" style="margin-top: 5px"><strong><i class="fa fa-globe"></i> Расположение</strong> <?php echo $region["name"]; ?></div>
				<div class="well well-sm" style="margin-top: 5px"><strong><i class="fa fa-globe"></i> Адрес</strong> <?php echo $row["address"]; ?></div>
				<div class="well well-sm" style="margin-top: 5px"><strong><i class="fa fa-map-marker"></i> Координаты</strong> <?php echo $row["latitude"]." ".$row["longitude"]; ?></div>
				<div class="well well-sm sight-image" style="margin-top: 5px">				
			<?php $folder = "temp/sights/".$id;
			if (file_exists($folder)) {
				echo '<strong>Старые фото: </strong><br><br>';
				$folder_open = opendir($folder);
				while($image = readdir($folder_open)){
					if(($image != '.') AND ($image != '..') AND ($image)){ ?>
	<!--				<div style="display: inline-block; position: relative">-->
						<img src="<?php echo $folder.'/'.$image; ?>" class="img-thumbnail" style="height: 100px" />
	<!--					<span class="icon_close">asd</span>
					</div>-->
					<?php }
				} 
			}
			if (is_array($image) && $image[0]['uri']!='') {
				echo '<br><br><strong>Новое фото: </strong><br><br>';
				echo '<img src="'.$image[0]['uri'].'" class="img-thumbnail" style="height: 100px" />';
			}
			?>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-footer" style="text-align: right">
				<button type="button" class="btn btn-default btn-sm" onclick="edit_sight('<?php echo $id; ?>')"><i class="fa fa-pencil"></i></button>
				<!--<button type="button" class="btn btn-info btn-sm" onclick="add_new_image_sight('<?php echo $id; ?>')"><i class="fa fa-image"></i></button>-->
				<button type="button" class="btn btn-danger btn-sm" onclick="del_sight('<?php echo $id; ?>')"><i class="fa fa-close"></i></button>
			</div>
		</div>
	</div>
<?php
	}
?>
	<?php if(!$data){ ?>
		<div class="col-sm-12">
			<div class="alert alert-info"><i class="fa fa-info-circle"></i> Мест не добавлено</div>
		</div>
	<?php } ?>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function edit_sight($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM sights WHERE id=?i", $id);
	$regions = $connect->getAll("SELECT id,name FROM direction_object ORDER BY name");

	$entity = $row;
	$entity['type'] = 'sights';	
	ob_start();
?>
	<div class="form-horizontal panel panel-default edit-sight">
		<div class="panel-heading"><i class="fa fa-plus-circle"></i> Новое место</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-sm-3 control-label">Название</label>
				<div class="col-sm-9">
					<input type="text" class="form-control name" value="<?php echo $row['name']; ?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Фотография для списка</label>
				<div class="col-sm-9">
					<input type="file" class="form-control" name="image" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'image'))));?>">
					<div class="input-message-block" data-for="image"></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Фотография для верха страницы</label>
				<div class="col-sm-9">
					<input type="file" class="form-control" name="slider" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'slider'))));?>">
					<div class="input-message-block" data-for="slider"></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Фотогалерея</label>
				<div class="col-sm-9">
					<input type="file" class="form-control" name="photogallery" value="<?=htmlspecialchars(json_encode(bounds_to_files($connect,load_bounds($connect,$entity,'photogallery'))));?>">
					<div class="input-message-block" data-for="photogallery"></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Описание</label>
				<div class="col-sm-9">
					<textarea class="form-control description"><?php echo $row["description"]; ?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Адрес</label>
				<div class="col-sm-9">
					<input type="text" class="form-control address" value="<?php echo $row['address']; ?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Широта</label>
				<div class="col-sm-9">
					<input type="text" class="form-control latitude" value="<?php echo $row['latitude']; ?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Долгота</label>
				<div class="col-sm-9">
					<input type="text" class="form-control longitude" value="<?php echo $row['longitude']; ?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Расположение</label>
				<div class="col-sm-9">
					<select class="form-control place">
						<option value="0"></option>
						<?php
						foreach ($regions as $region) {
							$sel = '';
							if ($row['place']==$region['id']) $sel = 'selected="selected"';
							?><option value="<?=$region['id']?>" <?=$sel?>><?=$region['name']?></option><?php
						}
						?>
					</select>
				</div>
			</div>			
		</div>
		<div class="panel-footer" style="text-align: right">
			<button type="button" class="btn btn-success btn-sm" onclick="update_sight('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			<button type="button" class="btn btn-danger btn-sm" onclick="view_sights()"><i class="fa fa-times-circle"></i> Отмена</button>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_sight($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$description = $_POST["description"];
	$address = $_POST["address"];
	$latitude = $_POST["latitude"];
	$longitude = $_POST["longitude"];
	$place = $_POST["place"];
	$connect->query("UPDATE sights SET name=?s, description=?s, address=?s, latitude=?s, longitude=?s, place=?i WHERE id=?i", $name, $description, $address, $latitude, $longitude, $place, $id);

	$entity = [
		'id' => $id,
		'type' => 'sights'
	];

	$boundsArrayImage = [];
	$boundsArraySlider = [];
	$boundsArrayPhotoGallery = [];

	$boundsArrayImage = files_to_bounds($connect,$entity,'image',isset($_POST['image'])?$_POST['image']:[]);
	$boundsArraySlider = files_to_bounds($connect,$entity,'slider',isset($_POST['slider'])?$_POST['slider']:[]);
	$boundsArrayPhotoGallery = files_to_bounds($connect,$entity,'photogallery',isset($_POST['photogallery'])?$_POST['photogallery']:[]);	

	remove_bounds($connect,$entity,'image');
	remove_bounds($connect,$entity,'slider');
	remove_bounds($connect,$entity,'photogallery');

	set_bounds($connect,$boundsArrayImage,'image');	
	set_bounds($connect,$boundsArraySlider,'slider');	
	set_bounds($connect,$boundsArrayPhotoGallery,'photogallery');	
}

?>
