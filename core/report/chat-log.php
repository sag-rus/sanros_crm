<?php

function show_chat_users(){
?>
<div class="btn-group btn-group-justified head-menu-chat">
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-chat-log" onclick="show_chat_log()"><i class="fa fa-file-text-o"></i> Логи чата</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-chat-operator" onclick="show_sitehelp_operator()"><i class="fa fa-user"></i> Операторы</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-chat-template" onclick="show_sitehelp_template()"><i class="fa fa-align-justify"></i> Шаблоны</button>
	</div>
</div>
<div class="chat-body" style="margin-top: 10px"></div>
<?php
}

function show_chat_log($connect){
	$manager = "";
	$data = $connect->getAll("SELECT id_user FROM chat_users");
	foreach($data as $row){
		$user = $row["id_user"];
		$name = $connect->getOne("SELECT name FROM users WHERE id=?i", $user);
		$manager = "<option value='".$user."'>".$name."</option>";
	}
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body form-chat-log">
		<div class="form-group form-group-margin">
			<label class="col-sm-2 control-label">Дата</label>
			<div class="col-sm-4">
				<input type="text" class="form-control datepicker" id="date-chat" value="<?php echo date('Y-m-d'); ?>" />
			</div>
			<label class="col-sm-2 control-label">Менеджер</label>
			<div class="col-sm-4">
				<select class="form-control manager-chat">
					<option value="">не выбран</option>
					<?php echo $manager; ?>
				</select>
			</div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-success btn-sm" onclick="filter_chat_log_users()"><i class="fa fa-search"></i> Применить</button>
	</div>
</div>
<div class="result-chat-log"></div>
<?php
}

function filter_chat_log_users($connect){
	$date = $_POST["date"];
	$manager = $_POST["manager"];
	$zapros = "";
	if($manager)
		$zapros = " AND manager=".$manager;
?>
<?php
	$chats = $connect->getAll("SELECT id, website, date, review, manager, source FROM sitehelp_chat WHERE date=?s ".$zapros, $date);
	foreach($chats as $chat){
		$count = $connect->getOne("SELECT COUNT(*) FROM sitehelp_message WHERE chat=?i", $chat["id"]);
		$name = $connect->getOne("SELECT name FROM users WHERE id=?i", $chat["manager"]);
		$icon = select_source_icon($chat["source"]);
?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<span class="label-name pointer" data-toggle="collapse" aria-expanded="false" aria-controls="collapse-<?php echo $chat['id']; ?>" data-target="#collapse-<?php echo $chat['id']; ?>"><?php echo $chat["website"]; ?> (<?php echo $name; ?>)</span>
			<span class="pull-right">
				<?php echo $icon; ?>
			<?php if($chat["review"] != ""){ ?>
				<span class="btn btn-warning btn-xs"><i class="fa fa-comments-o"></i></span>
			<?php } ?>
				<span class="btn btn-default btn-xs"><?php echo $count; ?> <i class="fa fa-envelope-o"></i></span>
			</span>
		</div>
		<div class="collapse" id="collapse-<?php echo $chat['id']; ?>">
			<div class="panel-body">
			<?php if($chat["review"] != ""){ ?>
				<div class="alert alert-danger"><i class="fa fa-comments-o"></i> отзыв: <?php echo $chat["review"]; ?></div>
			<?php } ?>
<?php
		$data = $connect->getAll("SELECT id FROM sitehelp_message WHERE chat=?i ORDER BY id", $chat["id"]);
		foreach($data as $row){
			echo write_message_sitehelp($connect, $row["id"]);
		}
?>
			</div>
		</div>
	</div>
<?php
	}
?>
	</div>
<?php
}

function show_sitehelp_operator($connect){
	global $CHAT_GROUP;
?>
	<div class="list-group form-horizontal">
<?php
	$data = $connect->getAll("SELECT id_user, status, user_group FROM chat_users WHERE last_visit!='0000-00-00 00:00:00' ORDER BY user_group");
	foreach($data as $row){
		$user = $row["id_user"];
		$status = $row["status"];
		$group = $CHAT_GROUP[$row["user_group"]]["name"];
		$row = $connect->getRow("SELECT name, photo FROM users WHERE id=?i", $user);
		$manager = $row["name"];
		$photo = "images/NoPicture.jpg";
		if($row["photo"])
			$photo = "data:image/jpg;base64,".$row["photo"];
		$sitehelp_status = "manager-offline";
		if($status == 1)
			$sitehelp_status = "manager-online";
?>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<div class="col-sm-2">
					<img src="<?php echo $photo; ?>" class="chat-avatar" />
				</div>
				<div class="col-sm-2">
					<div class="manager-status <?php echo $sitehelp_status; ?>"></div>
					<span class="label-name"><?php echo $manager; ?></span>
				</div>
				<div class="col-sm-8">
					<?php echo $group; ?>
				</div>
			</div>
		</div>
<?php
	}
?>
	</div>
<?php
}

function show_sitehelp_template($connect){
?>
	<div class="form-horizontal panel panel-default">
		<div class="panel-body sitehelp-template-block">
			<div class="form-group">
				<label class="col-sm-4 control-label control-label-left">
					Название
				</label>
				<label class="col-sm-8 control-label control-label-left">
					Текст
				</label>
			</div>
<?php
	$data = $connect->getAll("SELECT id, name, text FROM sitehelp_template");
	foreach($data as $row){
	?>
			<div class="form-group sitehelp-tepmlate" number="<?php echo $row['id']; ?>">
				<div class="col-sm-4">
					<input type="text" class="form-control template-name" value="<?php echo $row['name']; ?>" />
				</div>
				<div class="col-sm-8">
					<input type="text" class="form-control template-text" value="<?php echo $row['text']; ?>" />
				</div>
			</div>
	<?php
	}
?>
			<div class="form-group sitehelp-tepmlate">
				<div class="col-sm-4">
					<input type="text" class="form-control template-name" />
				</div>
				<div class="col-sm-8">
					<input type="text" class="form-control template-text" />
				</div>
			</div>
		</div>
		<div class="panel-footer text-right">
			<button class="btn btn-default btn-sm" onclick="append_sitehelp_template()"><i class="fa fa-plus-circle"></i> Добавить</button>
			<button class="btn btn-success btn-sm" onclick="save_sitehelp_template()"><i class="fa fa-check-circle"></i> Сохранить</button>
		</div>
	</div>
<?php
}

function save_sitehelp_template($connect){
	$data = json_decode($_POST["data"], TRUE);
	foreach($data as $template){
		$number = $template["number"];
		$name = $template["name"];
		$text = $template["text"];
		if($name != "" AND $text != ""){
			if($number)
				$connect->query("UPDATE sitehelp_template SET name=?s, text=?s WHERE id=?i", $name, $text, $number);
			else
				$connect->query("INSERT INTO sitehelp_template(name, text) VALUES (?s, ?s)", $name, $text);
		}
	}
}

?>
