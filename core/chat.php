<?php

$smiles = array(":)" => "happy",
		":-/" => "hmm",
		":(" => "sadm",
		":|" => "norm",
		":P" => "tongue",
		":0" => "oh",
		">=|" => "mad",
		";)" => "wink"
	);

function open_my_chat($connect){
	global $session_login;
	$size = $connect->getOne("SELECT size_chat FROM chat_users WHERE id_user=?i", $session_login);
?>
<div id="chat-message" class="chat-message-<?php echo $size; ?>">
	<div class="heading-chat">
		<div class="btn-group btn-chat-window">
			<div class="btn-group">
				<button class="btn btn-default btn-lt btn-show-chat" onclick="show_users_my_chat()">Рабочий чат <span class="count-new-message"></span></button>
			</div>
			<div class="btn-group">
				<button class="btn btn-default btn-lt btn-show-sitehelp" onclick="show_sitehelp_chat()">Сайтхэлп <span class="count-new-message"></span></button>
			</div>
		</div>
		<div class="pull-right">
			<button class="btn btn-default btn-lt" onclick="show_mailing_chat_message()" title="Разослать сообщение"><i class="fa fa-users"></i></button>
			<button class="btn btn-danger btn-lt" onclick="hide_chat_message()"><i class="fa fa-times-circle"></i></button>
		</div>
	</div>
	<div class="panel-chat">
		<div class="message-body"></div>
	</div>
</div>
<?php
}

function show_smile_chat(){
	global $smiles;
?>
	<div style="width: 130px">
<?php
	foreach($smiles as $code => $smile){
?>
	<img src="images/chat/smile/<?php echo $smile; ?>.png" class="chat-smile" code="<?php echo $code; ?>" />
<?php
	}
?>
	</div>
<?php
}

function write_message_chat($connect, $message){
	global $session_login, $directory, $smiles;
	ob_start();
	$row = $connect->getRow("SELECT text, attach, DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date, user, read_message FROM chat_message WHERE id=?i", $message);
	$attach = $row["attach"];
	$icon = "";
	if($row["user"] != $session_login)
		$class = " alert-info chat-message-user ";
	else{
		$class = " alert-success chat-my-message ";
		$icon = "<i class='fa fa-square-o'></i>";
		if($row["read_message"] == 1)
			$icon = "<i class='fa fa-check-square-o'></i>";
	}
	$text = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href='$3' target='_blank'>Ссылка</a>", $row["text"]);
	$text = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href='http://$3' target='_blank'>Ссылка</a>", $text);
	foreach($smiles as $code => $smile)
		$text = str_replace($code, '<img src="images/chat/smile/'.$smile.'.png" />', $text);
?>
	<div class="alert chat-message-view <?php echo $class; ?>">
		<?php echo $text; ?>
		<?php if($attach){
			$file = "temp/chat/".$attach;
			?>
			<?php if(file_is_image($directory."/".$file)){ ?>
				<a href="<?php echo $file; ?>" target="_blank">
					Картинка
				</a>
			<?php }else{ ?>
				<i class="fa fa-paperclip"></i> <a class="alert-link" href="<?php echo $file; ?>" target="_blank">Прикрепленный файл</a>
			<?php } ?>
		<?php } ?>
		<span class="message-date"><?php echo $icon; ?> <?php echo $row["date"]; ?></span>
	</div>
	<div class="clearfix"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function write_message_sitehelp($connect, $message){
	$row = $connect->getRow("SELECT text, DATE_FORMAT(time, '%H:%i') as date, user, type FROM sitehelp_message WHERE id=?i", $message);
	if($row["type"] == "client")
		$class = " alert-info chat-message-user ";
	elseif($row["type"] == "manager")
		$class = " alert-success chat-my-message ";
	else
		$class =  " chat-message-action ";
	$user = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]);
	ob_start();
?>
	<div class="alert chat-message-view <?php echo $class; ?>">
		<?php echo $row["text"]; ?>
		<span class="message-date"><?php echo $user; ?> <?php echo $row["date"]; ?></span>
	</div>
	<div class="clearfix"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_users_my_chat($connect){
	global $session_login;
?>
	<div class="form-horizontal list-group chat-users-block form-group-margin">
<?php
	$data = $connect->getAll("SELECT users.id, users.photo, users.name, users.chat_status FROM users, chat_room, chat_message WHERE read_message=0 AND ((second_user=?i AND first_user=users.id) OR (first_user=?i AND second_user=users.id)) AND chat_room.id=chat_message.chat AND chat_message.user!=?i AND users.id!=?i GROUP BY users.id", $session_login, $session_login, $session_login, $session_login);
	if($data){
?>
	<div class="list-group-item chat-group-name">
		<i class="fa fa-angle-double-right"></i> Новые сообщения
	</div>
<?php
	}
	foreach($data as $row){
		$user = $row["id"];
		if($row["photo"])
			$photo = "data:image/jpg;base64,".$row["photo"];
		else
			$photo = "images/NoPicture.jpg";
		$chat = $connect->getOne("SELECT id FROM chat_room WHERE (first_user=?i AND second_user=?i) OR (first_user=?i AND second_user=?i)", $session_login, $user, $user, $session_login);
		$no_read = $connect->getOne("SELECT COUNT(*) FROM chat_message WHERE chat=?i AND user=?i AND read_message=0", $chat, $user);
?>
		<div class="list-group-item list-group-item-small list-hover-item list-group-item-danger" onclick="show_chat_room('<?php echo $user; ?>')">
			<img src="<?php echo $photo; ?>" class="chat-avatar-small" />
			<span class="label-name"><?php echo $row["name"]; ?></span>
			<span class="label label-success label-chat pull-right"><?php echo $no_read; ?></span>
			<div class="clearfix"></div>
		</div>
<?php
	}
	$data = $connect->getAll("SELECT id, photo, name, chat_status, id_group FROM users WHERE chat_status!=2 AND id!=?i AND id_group!='' ORDER BY id_group", $session_login);
	$old_group = "";
	foreach($data as $row){
		if($old_group != $row["id_group"]){
			$group = $connect->getOne("SELECT name FROM groups WHERE id=?i", $row["id_group"]);
?>
	<div class="list-group-item chat-group-name">
		<i class="fa fa-angle-double-right"></i> <?php echo $group; ?>
	</div>
<?php
			$old_group = $row["id_group"];
		}
		$user = $row["id"];
		if($row["photo"])
			$photo = "data:image/jpg;base64,".$row["photo"];
		else
			$photo = "images/NoPicture.jpg";
		$status = "warning";
		if($row["chat_status"] == 1)
			$status = "success";
		elseif($row["chat_status"] == 0)
			$status = "danger";
?>
	<div class="list-group-item list-group-item-small list-hover-item" onclick="show_chat_room('<?php echo $user; ?>')">
		<img src="<?php echo $photo; ?>" class="chat-avatar-small" />
		<span class="label-name"><?php echo $row["name"]; ?></span>
		<span class="label label-<?php echo $status; ?> label-chat pull-right">&nbsp;</span>
		<div class="clearfix"></div>
	</div>
<?php
	}
?>
<?php if(!$data){ ?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> Все собеседники offline</div>
<?php } ?>
	</div>
<?php
}

function show_chat_room($connect){
	global $session_login, $directory;
	$data = array();
	$user = $_POST["user"];
	$type = $_POST["type"];
	$row = $connect->getRow("SELECT photo, name FROM users WHERE id=?i", $user);
	if($row["photo"])
		$photo = "data:image/jpg;base64,".$row["photo"];
	else
		$photo = "images/NoPicture.jpg";
	$chat = $connect->getOne("SELECT id FROM chat_room WHERE (first_user=?i AND second_user=?i) OR (first_user=?i AND second_user=?i)", $session_login, $user, $user, $session_login);
	if(!$chat){
		$connect->query("INSERT INTO chat_room(first_user, second_user) VALUES(?i, ?i)", $session_login, $user);
		$chat = $connect->insertId();
	}
	ob_start();
?>
	<div class="chat-login">
		<div class="pull-right">
			<button class="btn btn-default btn-lt" onclick="show_chat_room('<?php echo $user; ?>', 'all')" title="Показать все сообщения"><i class="fa fa-commenting"></i></button>
			<button class="btn btn-primary btn-lt" onclick="form_upload_document('<?php echo $chat; ?>', 'chat')"><i class="fa fa-paperclip"></i></button>
			<button class="btn btn-default btn-lt" onclick="show_users_my_chat()"><i class="fa fa-reply"></i></button>
		</div>
		<img src="<?php echo $photo; ?>" class="chat-avatar" />
		<span class="label-name"><?php echo $row["name"]; ?></span>
		<div class="clearfix"></div>
		<hr />
	</div>
		<div class="chat-message-block">
<?php
	if($type == "all")
		$data = $connect->getAll("SELECT id FROM chat_message WHERE chat=?i ORDER BY id", $chat);
	else{
		$count = $connect->getOne("SELECT COUNT(*) FROM chat_message WHERE chat=?i", $chat) - 20;
		if($count < 0)
			$count = 0;
		$data = $connect->getAll("SELECT id FROM chat_message WHERE chat=?i ORDER BY id LIMIT ?i, 20", $chat, $count);
	}
	foreach($data as $row)
		echo write_message_chat($connect, $row["id"]);
?>
		<div class="scrollTo"></div>
	</div>
	<div class="clearfix"></div>
	<div class="row" style="margin-top: 5px">
	<div class="input-group">
		<input type="text" class="form-control send-new-message" onKeyPress="if(event.keyCode == 13) send_new_message_chat()" placeholder="Тескт сообщения..." chat="<?php echo $chat; ?>" />
		<span class="input-group-btn">
			<button class="btn btn-default btn-sm smile-button" type="button" style="padding: 4px 10px;" onclick="show_smile_chat()"><i class="fa fa-smile-o"></i></button>
			<button class="btn btn-default btn-sm" type="button" style="padding: 4px 10px;" onclick="send_new_message_chat()"><i class="fa fa-angle-double-right"></i></button>
		</span>
	</div>
</div>
<?php
	$data["html"] = ob_get_clean();
	$connect->query("UPDATE chat_message SET read_message=1 WHERE user=?i AND chat=?i", $user, $chat);
	$data["new"] = check_new_messages($connect);
	return json_encode($data);
}

function send_new_message_chat($connect){
	global $session_login;
	$chat = $_POST["chat"];
	$text = $_POST["text"];
	$text = str_replace("plus", "+", $text);
	if(!$connect->getOne("SELECT id FROM chat_room WHERE (first_user=?i OR second_user=?i) AND id=?i", $session_login, $session_login, $chat) AND $text != "")
		return FALSE;
	$connect->query("INSERT INTO chat_message(chat, user, text) VALUES(?i, ?i, ?s)", $chat, $session_login, $text);
	return write_message_chat($connect, $connect->insertId());
}





function new_message_sitehelp($connect){
	global $session_login;
	$data = array();
	$jid = $_POST["jid"];
	$text = $_POST["text"];
	$website = $_POST["website"];
	$time = $_POST["time"];
	$current_jid = $_POST["current_jid"];
	$chat = $connect->getOne("SELECT id FROM sitehelp_chat WHERE turist=?s", $jid);
	if($chat){
		$connect->query("UPDATE sitehelp_chat SET status=0 WHERE id=?i", $chat);
		if(!$connect->getOne("SELECT id FROM sitehelp_message WHERE time_chat=?i AND chat=?i", $time, $chat))
			$connect->query("INSERT INTO sitehelp_message(chat, text, type, time_chat) VALUES(?i, ?s, 'client', ?i)", $chat, $text, $time);
	}else{
		$connect->query("INSERT INTO sitehelp_chat(turist, manager, website, date) VALUES(?s, ?i, ?s, ?s)", $jid, $session_login, $website, date("Y-m-d"));
		$chat = $connect->insertId();
		$connect->query("INSERT INTO sitehelp_message(chat, text, type, time_chat) VALUES(?i, ?s, 'client', ?i)", $chat, $text, $time);
		$data["request"] = 1;
		$data["jid"] = $jid;
		$data["name"] = $website;
	}
	$message = $connect->insertId();
	if($current_jid == $jid)
		$connect->query("UPDATE sitehelp_message SET read_message=1 WHERE id=?i", $message);
	$data["html"] = write_message_sitehelp($connect, $message);
	$data["new"]["sitehelp"] = $connect->getOne("SELECT COUNT(*) FROM sitehelp_chat, sitehelp_message WHERE sitehelp_chat.manager=?i AND sitehelp_message.type!='manager' AND read_message=0 AND sitehelp_message.chat=sitehelp_chat.id", $session_login);
	$data["new"]["all"] = $data["new"]["sitehelp"];
	return json_encode($data);
}


function show_sitehelp_chat($connect){
	global $session_login;
?>
	<div class="form-horizontal list-group chat-users-block">
<?php
	$data = $connect->getAll("SELECT id, website, status FROM sitehelp_chat WHERE manager=?i AND status=0", $session_login);
?>
	<div class="list-group-item chat-group-name">
		<i class="fa fa-angle-double-right"></i> Беседы
	</div>
<?php
	foreach($data as $row){
		$chat = $row["id"];
		$no_read = $connect->getOne("SELECT COUNT(*) FROM sitehelp_message WHERE chat=?i AND read_message=0 AND type!='manager'", $chat);
		$time = $connect->getOne("SELECT DATE_FORMAT(time, '%H:%i') as time FROM sitehelp_message WHERE chat=?i ORDER BY id DESC", $chat);
		$class = "";
		if($no_read > 0)
			$class = " list-group-item-danger ";
		$status = " manager-online ";
		if($row["status"] == 1)
			$status = " manager-offline ";
?>
		<div class="list-group-item list-group-item-small list-hover-item <?php echo $class; ?>" onclick="show_sitehelp_room('<?php echo $chat; ?>')">
			<span class="manager-status <?php echo $status; ?>"></span>
			<span class="label-name"><?php echo $row["website"]; ?></span>
			(<?php echo $time; ?>)
		<?php if($no_read > 0){ ?>
			<span class="label label-success label-chat pull-right"><?php echo $no_read; ?></span>
		<?php } ?>
			<div class="clearfix"></div>
		</div>
<?php
	}
?>
<?php if(!$data){ ?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> Новых сообщений нет</div>
<?php } ?>
	</div>
<?php
}

function show_sitehelp_room($connect){
	global $session_login;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, turist, website, address, status, type, id_client FROM sitehelp_chat WHERE id=?i", $id);
	$jid = $row["turist"];
	$from = $connect->getOne("SELECT login FROM chat_users WHERE id_user=?i", $session_login);
	$status = " manager-online ";
	if($row["status"] == 1)
		$status = " manager-offline ";
	$cabinet = "";
	if($row["type"] != "site"){
		if($row["type"] == "client")
			$cabinet = select_name_klient($connect, $row["id_client"]);
		elseif($row["type"] == "agency")
			$cabinet = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["id_client"]);
		elseif($row["type"] == "object")
			$cabinet = $connect->getOne("SELECT name FROM object WHERE id_account=?i", $row["id_client"]);
	}
	ob_start();
?>
	<div class="sitehelp-room" jid="<?php echo $jid; ?>" from="<?php echo $from; ?>" room="<?php echo $row['id']; ?>">
		<div>
			<span class="manager-status <?php echo $status; ?>"></span>
			<span class="label-name"><?php echo $row["website"]; ?></span>
		</div>
		<?php if($row["address"] != ""){ ?>
			<div>
				<i><i class="fa fa-home"></i> <?php echo $row["address"]; ?></i>
			</div>
		<?php } ?>
		<?php if($cabinet != "" AND $row["type"] != "object"){ ?>
			<span class="link" onclick="select_klient('<?php echo $row['id_client']; ?>', '<?php echo $row['type']; ?>')">
				<i><i class="fa fa-user"></i> <?php echo $cabinet; ?></i>
			</span>
		<?php }elseif($cabinet != ""){ ?>
				<i><i class="fa fa-user"></i> <?php echo $cabinet; ?></i>
		<?php } ?>
		<div class="clearfix"></div>
		<div class="pull-right">
			<button class="btn btn-default btn-lt btn-template-manager"><i class="fa fa-align-justify"></i> Шаблон</button>
			<button class="btn btn-default btn-lt btn-change-manager"><i class="fa fa-exchange"></i> Перевод</button>
			<button class="btn btn-default btn-lt" onclick="show_sitehelp_chat()"><i class="fa fa-reply"></i> Назад</button>
			<button class="btn btn-warning btn-lt" onclick="trash_sitehelp_chat()"><i class="fa fa-trash"></i></button>
		</div>
		<div class="clearfix"></div>
		<hr />
	</div>
		<div class="chat-message-block">
<?php
	$data = $connect->getAll("SELECT id FROM sitehelp_message WHERE chat=?i ORDER BY id", $id);
	foreach($data as $row)
		echo write_message_sitehelp($connect, $row["id"]);
?>
		<div class="scrollTo"></div>
	</div>
	<div class="clearfix"></div>
	<div class="row" style="margin-top: 5px">
		<div class="input-group">
			<input type="text" class="form-control send-new-message" onKeyPress="send_new_message_sitehelp(event)" placeholder="Тескт сообщения..." chat="<?php echo $chat; ?>" />
			<span class="input-group-btn">
				<button class="btn btn-default btn-sm" type="button" style="padding: 4px 10px;" onclick="send_new_message_sitehelp()"><i class="fa fa-angle-double-right"></i></button>
			</span>
		</div>
	</div>
</div>
<?php
	$data["html"] = ob_get_clean();
	$connect->query("UPDATE sitehelp_message SET read_message=1 WHERE chat=?i", $id);
	$data["new"] = check_new_messages($connect);
	return json_encode($data);
}

function send_new_message_sitehelp($connect){
	global $session_login;
	$jid = $_POST["jid"];
	$text = str_replace("plus", "+", $_POST["text"]);
	$chat = $connect->getOne("SELECT id FROM sitehelp_chat WHERE turist=?s", $jid);
	$connect->query("INSERT INTO sitehelp_message(chat, user, text, type) VALUES(?i, ?i, ?s, 'manager')", $chat, $session_login, $text);
	return write_message_sitehelp($connect, $connect->insertId());
}

function show_change_manager_sitehelp($connect){
	global $session_login, $CHAT_GROUP;
	$chat = $_POST["chat"];
	$type = $connect->getOne("SELECT type FROM sitehelp_chat WHERE id=?i", $chat);
?>
	<div class="list-group list-group-margin">
<?php
	if($type == "site")
		$data = $connect->getAll("SELECT id, id_user, user_group FROM chat_users WHERE id_user!=?i AND status=1 AND user_group=1", $session_login);
	else
		$data = $connect->getAll("SELECT id, id_user, user_group FROM chat_users WHERE id_user!=?i AND status=1", $session_login);
	foreach($data as $row){
		$user = $connect->getRow("SELECT id, name, photo FROM users WHERE id=?i", $row["id_user"]);
		if($user["photo"])
			$photo = "data:image/jpg;base64,".$user["photo"];
		else
			$photo = "images/NoPicture.jpg";
		$group = $CHAT_GROUP[$row["user_group"]]["name"];
		$id_user = $user["id"];
	?>
		<div class="list-group-item list-group-item-small list-hover-item" onclick="change_manager_sitehelp('<?php echo $id_user; ?>')">
			<img src="<?php echo $photo; ?>" class="chat-avatar-small" />
			<span class="label-name"><?php echo $user["name"]." (".$group.")"; ?></span>
			<div class="clearfix"></div>
		</div>
	<?php
	}
?>
	</div>
<?php
}

function change_manager_sitehelp($connect){
	global $session_login;
	$chat = $_POST["chat"];
	$manager = $_POST["manager"];
	$text = "Перевод беседы на другого оператора";
	$connect->query("UPDATE sitehelp_chat SET manager=?i WHERE id=?i", $manager, $chat);
	$connect->query("INSERT INTO sitehelp_message(chat, text, type, user) VALUES (?i, ?s, 'action', ?i)", $chat, $text, $session_login);
	$data["to"] = $connect->getOne("SELECT turist FROM sitehelp_chat WHERE id=?i", $chat);
	$data["from"] = $connect->getOne("SELECT login FROM chat_users WHERE id_user=?i", $session_login);
	return json_encode($data);
}

function show_template_sitehelp($connect){
?>
	<div class="list-group list-group-margin">
<?php
	$data = $connect->getAll("SELECT name, text FROM sitehelp_template");
	foreach($data as $row){
	?>
		<div class="list-group-item list-group-item-small list-hover-item" onclick="send_template_sitehelp('<?php echo $row['text']; ?>')">
			<span class="label-name"><?php echo $row["name"]; ?></span>
			<div class="clearfix"></div>
		</div>
	<?php
	}
?>
	</div>
<?php
}

function trash_sitehelp_chat($connect){
	$chat = $_POST["chat"];
	$connect->query("UPDATE sitehelp_chat SET status=1 WHERE id=?i", $chat);
}

function show_mailing_chat_message($connect){
	global $session_login;
	$group_array = get_status_array($connect, "groups");
?>
	<div class="form-horizontal list-group chat-users-block">
<?php
	$data = $connect->getAll("SELECT id, photo, name, chat_status, id_group FROM users WHERE chat_status!=0 AND id!=?i ORDER BY id_group", $session_login);
	foreach($data as $row){
		$user = $row["id"];
		if($row["photo"])
			$photo = "data:image/jpg;base64,".$row["photo"];
		else
			$photo = "images/NoPicture.jpg";
		$status = "warning";
		if($row["chat_status"] == 1)
			$status = "success";
		elseif($row["chat_status"] == 0)
			$status = "danger";
		$group = $group_array[$row["id_group"]];
?>
			<div class="list-group-item list-group-item-small">
				<label><input type="checkbox" class="user" value="<?php echo $user; ?>" /><?php echo $row["name"]; ?> (<?php echo $group; ?>)</label>
			</div>
<?php
	}
?>
			<div class="list-group-item list-group-item-small">
				<label><input type="checkbox" id="all_user" onclick="check_all('user')" />Выбрать всех</label>
			</div>
		<div class="clearfix"></div>
		<div class="list-group-item-small">
			<textarea class="form-control new-message"></textarea>
		</div>
		<div class="text-right list-group-item-small">
			<button type="button" class="btn btn-success btn-send-message" onclick="send_mailing_chat_message()">Отправить сообщение</button>
		</div>
	</div>
<?php
}

function send_mailing_chat_message($connect){
	global $session_login;
	$users = explode("_", $_POST["users"]);
	$users = array_diff($users, array(""));
	$message = $_POST["message"];
	foreach($users as $user){
		$chat = $connect->getOne("SELECT id FROM chat_room WHERE (first_user=?i AND second_user=?i) OR (first_user=?i AND second_user=?i)", $session_login, $user, $user, $session_login);
		if(!$chat){
			$connect->query("INSERT INTO chat_room(first_user, second_user) VALUES(?i, ?i)", $session_login, $user);
			$chat = $connect->insertId();
		}
		if($chat)
			$connect->query("INSERT INTO chat_message(chat, user, text) VALUES(?i, ?i, ?s)", $chat, $session_login, $message);
	}
}

?>
