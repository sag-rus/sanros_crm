<?php

function show_change_password(){
?>
	<div class="form-horizontal panel panel-default change-password">
		<div class="panel-heading"><i class="fa fa-key"></i> Изменение пароля</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-sm-4 control-label">Введите старый пароль</label>
				<div class="col-sm-8">
					<input type="password" class="form-control old-password" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">Введите новый пароль</label>
				<div class="col-sm-8">
					<input type="password" class="form-control new-password" />
				</div>
			</div>
			<div class="form-group form-group-margin">
				<label class="col-sm-4 control-label">Повтор нового пароля</label>
				<div class="col-sm-8">
					<input type="password" class="form-control repeat-new-password" />
				</div>
			</div>
		</div>
		<div class="panel-footer text-right">
			<button type="button" class="btn btn-success btn-sm" onclick="update_my_password()"><i class="fa fa-check-circle"></i> Сохранить новый пароль</button>
		</div>
	</div>
<?php
}

function update_my_password($connect){
	global $session_login;
	$old = md5($_POST["old"]);
	$new = $_POST["new"];
	$repeat = $_POST["repeat"];
	if($new == $repeat AND $new != "" AND $connect->getOne("SELECT id FROM users WHERE id=?i AND password=?s", $session_login, $old)){
		$connect->query("UPDATE users SET password=?s WHERE id=?i", md5($new), $session_login);
		return 1;
	}
	return 0;
}

function show_my_chat_log($connect){
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body form-chat-log">
		<div class="form-group form-group-margin">
			<label class="col-sm-2 control-label">Дата</label>
			<div class="col-sm-5">
				<input type="text" class="form-control datepicker" id="date-chat" />
			</div>
			<div class="col-sm-5"></div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-success btn-sm" onclick="filter_my_chat_log()"><i class="fa fa-search"></i> Применить</button>
	</div>
</div>
<div class="result-chat-log"></div>
<?php
}

function filter_chat_log($connect){
	global $session_login;
	$date = $_POST["date"];
?>
<?php
	$chats = $connect->getAll("SELECT id, website, date, review FROM sitehelp_chat WHERE manager=?i AND date=?s", $session_login, $date);
	foreach($chats as $chat){
		$count = $connect->getOne("SELECT COUNT(*) FROM sitehelp_message WHERE chat=?i", $chat["id"]);
?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<span class="label-name pointer" data-toggle="collapse" aria-expanded="false" aria-controls="collapse-<?php echo $chat['id']; ?>" data-target="#collapse-<?php echo $chat['id']; ?>"><?php echo $chat["website"]; ?></span>
			<span class="pull-right">
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

?>
