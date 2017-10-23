<?php

function show_chat_setting($connect){
	global $session_login;
	$row = $connect->getRow("SELECT photo, chat_status FROM users WHERE id=?i", $session_login);
	$check = array(0 => array("status" => "", "check" => ""), 1 => array("status" => "", "check" => ""), 2 => array("status" => "", "check" => ""));
	$check[$row["chat_status"]]["status"] = "active";
	$check[$row["chat_status"]]["check"] = "checked";
	if($row["photo"])
		$photo = "data:image/jpg;base64,".$row["photo"];
	else
		$photo = "images/NoPicture.jpg";
	$row = $connect->getRow("SELECT status, login, password, alert, size_chat FROM chat_users WHERE id_user=?i", $session_login);
	$sitehelp_status = $row["status"];
	$login = $row["login"];
	$password = $row["password"];
	$alert = array(0 => array("status" => "", "check" => ""), 1 => array("status" => "", "check" => ""), 2 => array("status" => "", "check" => ""));
	$alert[$row["alert"]]["status"] = "active";
	$alert[$row["alert"]]["check"] = "checked";
	$size = array(1 => array("status" => "", "check" => ""), 2 => array("status" => "", "check" => ""));
	$size[$row["size_chat"]]["status"] = "active";
	$size[$row["size_chat"]]["check"] = "checked";
?>
	<div class="btn-group btn-group-justified btn-setting-chat">
		<div class="btn-group">
			<button class="btn btn-default btn-xs btn-status-chat" onclick="show_status_chat_setting()">статус</button>
		</div>
		<div class="btn-group">
			<button class="btn btn-default btn-xs btn-login-chat" onclick="show_login_chat_setting()">логин</button>
		</div>
		<div class="btn-group">
			<button class="btn btn-default btn-xs btn-sitehelp-chat" onclick="show_sitehelp_chat_setting()">настройки</button>
		</div>
	</div>

	<div class="chat-setting-body" style="margin-top: 10px">
		<div class="form-horizontal status-chat-setting hidden">
			<div class="form-group">
				<label class="col-sm-4 control-label">Мой Статус</label>
				<div class="col-sm-8">
					<div class="btn-group my-chat-status" data-toggle="buttons">
						<label class="btn btn-default btn-lt <?php echo $check[1]['status']; ?>">
							<input type="radio" name="my-status" value="1" <?php echo $check[1]["check"]; ?> /> <i class="fa fa-check-circle"></i> Online
						</label>
						<label class="btn btn-default btn-lt <?php echo $check[2]['status']; ?>">
							<input type="radio" name="my-status" value="2" <?php echo $check[2]["check"]; ?> /> <i class="fa fa-sign-out"></i> Away
						</label>
						<label class="btn btn-default btn-lt <?php echo $check[0]['status']; ?>">
							<input type="radio" name="my-status" value="0" <?php echo $check[0]["check"]; ?> /> <i class="fa fa-times-circle"></i> Off
						</label>
					</div>
				</div>
			</div>
			<div class="form-group form-group-margin">
				<label class="col-sm-4 control-label">Сайтхэлп</label>
				<div class="col-sm-8">
				<?php if($login){ ?>
					<?php if($sitehelp_status == 0){ ?>
					<button type="button" class="btn btn-success btn-lt btn-connect-sitehelp">Войти</button>
					<?php }else{ ?>
					<button type="button" class="btn btn-danger btn-lt btn-connect-sitehelp">Выйти</button>
					<?php } ?>
				<?php }else{ ?>
					<div class="alert-info well-sm">не создан</div>
				<?php } ?>
				</div>
			</div>
		</div>

		<div class="form-horizontal login-chat-setting hidden">
			<div class="form-group">
				<label class="col-sm-4 control-label">
					Логин
				</label>
				<div class="col-sm-8 sitehelp-login-block">
					<input type="text" class="form-control sitehelp-login" value="<?php echo $login; ?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">
					Пароль
				</label>
				<div class="col-sm-8 sitehelp-password-block">
					<input type="text" class="form-control sitehelp-password" value="<?php echo $password; ?>" />
				</div>
			</div>
			<div class="form-group form-group-margin">
				<div class="col-sm-12 text-right">
					<button class="btn btn-success btn-lt" onclick="save_login_sitehelp()"><i class="fa fa-check-circle"></i> Сохранить</button>
				</div>
			</div>
		</div>

		<div class="form-horizontal sitehelp-chat-setting hidden">
			<div class="form-group">
				<label class="col-sm-4 control-label">
					Моя аватарка
				</label>
				<div class="col-sm-8">
					<img src="<?php echo $photo; ?>" class="chat-avatar" />
					<button class="btn btn-default btn-sm" onclick="add_photo_profile('<?php echo $session_login; ?>', 'chat')"><i class="fa fa-picture-o"></i> Изменить</button>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">Уведомление</label>
				<div class="col-sm-8">
					<div class="btn-group my-chat-alert" data-toggle="buttons">
						<label class="btn btn-default btn-lt <?php echo $alert[1]['status']; ?>">
							<input type="radio" name="alert-chat" value="1" <?php echo $alert[1]["check"]; ?> /> <i class="fa fa-check-circle"></i> Да
						</label>
						<label class="btn btn-default btn-lt <?php echo $alert[2]['status']; ?>">
							<input type="radio" name="alert-chat" value="2" <?php echo $alert[2]["check"]; ?> /> <i class="fa fa-wechat"></i> Сайтхэлп
						</label>
						<label class="btn btn-default btn-lt <?php echo $alert[0]['status']; ?>">
							<input type="radio" name="alert-chat" value="0" <?php echo $alert[0]["check"]; ?> /> <i class="fa fa-times-circle"></i> Нет
						</label>
					</div>
				</div>
			</div>
			<div class="form-group form-group-margin">
				<label class="col-sm-4 control-label">Размер</label>
				<div class="col-sm-8">
					<div class="btn-group my-chat-size" data-toggle="buttons">
						<label class="btn btn-default btn-lt <?php echo $size[1]['status']; ?>">
							<input type="radio" name="size-chat" value="1" <?php echo $size[1]["check"]; ?> /> <i class="fa fa-minus-circle"></i> Мелкий
						</label>
						<label class="btn btn-default btn-lt <?php echo $size[2]['status']; ?>">
							<input type="radio" name="size-chat" value="2" <?php echo $size[2]["check"]; ?> /> <i class="fa fa-plus-circle"></i> Крупный
						</label>
					</div>
				</div>
			</div>
		</div>

	</div>

<?php
}

function save_my_chat_status($connect){
	global $session_login;
	$status = $_POST["status"];
	$connect->query("UPDATE users SET chat_status=?i WHERE id=?i", $status, $session_login);
}

function save_my_chat_alert($connect){
	global $session_login;
	$alert = $_POST["alert"];
	$connect->query("UPDATE chat_users SET alert=?i WHERE id_user=?i", $alert, $session_login);
}

function save_my_chat_size($connect){
	global $session_login;
	$size = $_POST["size"];
	$connect->query("UPDATE chat_users SET size_chat=?i WHERE id_user=?i", $size, $session_login);
}

function change_sitehelp_status($connect){
	global $session_login;
	$type = $_POST["type"];
	$answer = array();
	$row = $connect->getRow("SELECT status, login, password FROM chat_users WHERE id_user=?i", $session_login);
	if($row["status"] == 0 OR $type == "connect"){
		$connect->query("UPDATE chat_users SET status=1 WHERE id_user=?i", $session_login);
		$answer["status"] = 1;
		$answer["login"] = $row["login"];
		$answer["password"] = $row["password"];
	}else{
		$connect->query("UPDATE chat_users SET status=0 WHERE id_user=?i", $session_login);
		$answer["status"] = 0;
	}
	return json_encode($answer);
}

function save_login_sitehelp($connect){
	global $session_login;
	$login = $_POST["login"];
	$password = $_POST["password"];
	$chat = $connect->getOne("SELECT id FROM chat_users WHERE id_user=?i", $session_login);
	if($chat)
		$connect->query("UPDATE chat_users SET login=?s, password=?s WHERE id=?i", $login, $password, $chat);
	else
		$connect->query("INSERT INTO chat_users(login, password, id_user) VALUES(?s, ?s, ?i)", $login, $password, $session_login);
}

function logout_sitehelp($connect){
	global $session_login;
	$connect->query("UPDATE chat_users SET status=0 WHERE id_user=?i", $session_login);
}

function select_by_number_reckoning($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, turist, agency FROM reckoning WHERE id=?i", $id);
	return json_encode($row);
}

?>
