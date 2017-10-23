<?php

function select_manager_chat_cabinet($connect, $data){
	global $CHAT_GROUP;
	$answer = array();

	$user_chat = $data["user_chat"];
	$group = $data["group"];
	if(!$group)
		$group = 1;

	$row = $connect->getRow("SELECT id, manager FROM sitehelp_chat WHERE turist=?s", $user_chat);
	$chat = $row["id"];
	if($chat){
		$user = $connect->getOne("SELECT id FROM chat_users WHERE id_user=?i AND status=1", $row["manager"]);
		if(!$user)
			return FALSE;
		$data = $connect->getAll("SELECT id, text, type, user FROM sitehelp_message WHERE chat=?i ORDER BY id", $chat);
		foreach($data as $row){
			$answer["chat"][$row["id"]]["type"] = $row["type"];
			$answer["chat"][$row["id"]]["text"] = $row["text"];
			$answer["chat"][$row["id"]]["user"] = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]);
		}
		$array = explode("@", $user_chat);
		$answer["user"] = $array[0];
		$answer["user_server"] = $user_chat;
		$answer["pass"] = "pass";
		$answer["connect"] = 1;
		$answer["chat-messages"] = 1;
	}else{
		$user = select_id_manager_sitehelp($connect, $group);
		if(!$user)
			return FALSE;
		$count = $connect->getOne("SELECT value FROM constant WHERE name='sitehelp-count'") + 1;
		$connect->query("UPDATE constant SET value=?i WHERE name='sitehelp-count'", $count);
		$answer["user"] = "user-work".$count;
		$answer["user_server"] = $answer["user"]."@185.76.145.105";
		$answer["pass"] = "pass";
		$answer["authorization"] = 1;
	}
	$row = $connect->getRow("SELECT id_user, login, user_group FROM chat_users WHERE id=?i", $user);
	$manager = $connect->getRow("SELECT photo, name FROM users WHERE id=?i", $row["id_user"]);
	$answer["manager"] = $row["login"];
	if($manager["photo"])
		$answer["photo"] = "data:image/jpg;base64,".$manager["photo"];
	else
		$answer["photo"] = "http://tonia.ru/price/images/default.jpg";
	$answer["name"] = $manager["name"];
	$answer["welcome"] = "Здравствуйте! Меня зовут ".$manager["name"].". Буду рад ответить на ваш вопрос прямо сейчас!";
	$answer["post"] = $CHAT_GROUP[$row["user_group"]]["name"];

	return $answer;
}

function select_manager_sitehelp($connect, $data){
	$answer = array();

	$user_sitehelp = $data["user_sitehelp"];
	$object = $data["object"];
	$row = $connect->getRow("SELECT id, manager FROM sitehelp_chat WHERE turist=?s", $user_sitehelp);
	$chat = $row["id"];
	if($chat){
		$user = $connect->getOne("SELECT id FROM chat_users WHERE id_user=?i AND status=1", $row["manager"]);
		if(!$user)
			return FALSE;
		$data = $connect->getAll("SELECT id, text, type, user FROM sitehelp_message WHERE chat=?i ORDER BY id", $chat);
		foreach($data as $row){
			$answer["chat"][$row["id"]]["type"] = $row["type"];
			$answer["chat"][$row["id"]]["text"] = $row["text"];
			$answer["chat"][$row["id"]]["user"] = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]);
		}
		$array = explode("@", $user_sitehelp);
		$answer["user"] = $array[0];
		$answer["user_server"] = $user_sitehelp;
		$answer["pass"] = "pass";
		$answer["connect"] = 1;
		$answer["chat-messages"] = 1;
	}else{
		$group = 1;
		if($data["group"])
			$group = $data["group"];
		$user = select_id_manager_sitehelp($connect, $group, $object);
		if(!$user)
			return FALSE;
		$count = $connect->getOne("SELECT value FROM constant WHERE name='sitehelp-count'") + 1;
		$connect->query("UPDATE constant SET value=?i WHERE name='sitehelp-count'", $count);
		$answer["user"] = "user-work".$count;
		$answer["user_server"] = $answer["user"]."@185.76.145.105";
		$answer["pass"] = "pass";
		$answer["authorization"] = 1;
	}
	$row = $connect->getRow("SELECT id_user, login FROM chat_users WHERE id=?i", $user);
	$manager = $connect->getRow("SELECT photo, name FROM users WHERE id=?i", $row["id_user"]);
	$answer["manager"] = $row["login"];
	if($manager["photo"])
		$answer["photo"] = "data:image/jpg;base64,".$manager["photo"];
	else
		$answer["photo"] = "http://tonia.ru/price/images/default.jpg";
	$answer["name"] = $manager["name"];
	//$answer["contact"] = "Тут можно указать какие либо контакты менеджера";
	$answer["contact"] = "";
	$answer["welcome"] = "Здравствуйте! Меня зовут ".$manager["name"].". Я могу ответить на ваши вопросы! Пожалуйста, не закрывайте окно чата до получения ответов.";

	return $answer;
}

function send_info_consultation($connect, $data){
	$user = $data["user"];
	$chat = $connect->getOne("SELECT id FROM sitehelp_chat WHERE turist=?s", $user);
	if($chat){
		$href = $data["href"];
		$address = get_address_by_ip($data["ip"]);
		$source = select_index_source($data["source"]);
		$connect->query("UPDATE sitehelp_chat SET address=?s, source=?i, href=?s WHERE id=?i", $address, $source, $href, $chat);
		return;
	}
}

function update_info_chat_cabinet($connect, $data){
	$user_chat = $data["user_chat"];
	$session = $data["session"];
	$type = $data["type"];
	$chat = $connect->getOne("SELECT id FROM sitehelp_chat WHERE turist=?s", $user_chat);
	if($chat){
		if($type == "client"){
			$login = $connect->getOne("SELECT login FROM session_account WHERE id_session=?s", $session);
			$client = $connect->getOne("SELECT id FROM klient WHERE login=?s AND login!=''", $login);
			if($client)
				$connect->query("UPDATE sitehelp_chat SET type='client', id_client=?i WHERE id=?i", $client, $chat);
		}elseif($type == "agency"){
			$login = $connect->getOne("SELECT login FROM session_agency WHERE id_session=?s", $session);
			$agency = $connect->getOne("SELECT id FROM agency WHERE login=?s AND login!=''", $login);
			if($agency)
				$connect->query("UPDATE sitehelp_chat SET type='agency', id_client=?i WHERE id=?i", $agency, $chat);
		}elseif($type == "object"){
			$login = $connect->getOne("SELECT login FROM session_object WHERE id_session=?s", $session);
			$account = $connect->getOne("SELECT id FROM object_account WHERE login=?s AND login!=''", $login);
			if($account)
				$connect->query("UPDATE sitehelp_chat SET type='object', id_client=?i WHERE id=?i", $account, $chat);
		}
		return 1;
	}
	return FALSE;
}

function send_sitehelp_chat_email($connect, $data){
	$user_sitehelp = $data["user"];
	$email = $data["email"];
	$chat = $connect->getOne("SELECT id FROM sitehelp_chat WHERE turist=?s", $user_sitehelp);
	if($chat AND filter_var($email, FILTER_VALIDATE_EMAIL)){
		$text = "Посетитель отправил чат на почту";
		$connect->query("INSERT INTO sitehelp_message(chat, text, type) VALUES (?i, ?s, 'action')", $chat, $text);
		$website = $connect->getOne("SELECT website FROM sitehelp_chat WHERE id=?i", $chat);
		$log = "";
		$data = $connect->getAll("SELECT text, DATE_FORMAT(time, '%H:%i') as date, type, user FROM sitehelp_message WHERE chat=?i ORDER BY id", $chat);
		foreach($data as $row){
			$type = $row["type"];
			if($type != "action"){
				$user = "Я";
				if($type == "manager")
					$user = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]);
				ob_start();
	?>
		<div>
			<div>
				<div style="float: right"><?php echo $row["date"]; ?></div>
				<strong><?php echo $user; ?>:</strong> <?php echo $row["text"]; ?>
				<div style="clear: both"></div>
				<hr />
			</div>
		</div>
	<?php
				$log.= ob_get_clean();
			}
		}
		$message = get_template_letter("turist/send-chat");
		$message["title"] = str_replace("<website>", $website, $message["title"]);
		$message["content"] = str_replace("<log>", $log, $message["content"]);
		$message["content"] = str_replace("<website>", $website, $message["content"]);
		$connect->query("INSERT INTO send_mail(email, title, body) VALUES (?s, ?s, ?s)", $email, $message["title"], $message["content"]);
	}
}

function send_review_sitehelp($connect, $data){
	$user_sitehelp = $data["user_sitehelp"];
	$review = $data["review"];
	$chat = $connect->getOne("SELECT id FROM sitehelp_chat WHERE turist=?s", $user_sitehelp);
	if($chat){
		$text = "Посетитель оставил отзыв";
		$connect->query("UPDATE sitehelp_chat SET review=?s WHERE id=?i", $review, $chat);
		$connect->query("INSERT INTO sitehelp_message(chat, text, type) VALUES (?i, ?s, 'action')", $chat, $text);
	}
}

function select_id_manager_sitehelp($connect, $group, $object){
	$users = array();
	$add = "";
	if($object){
		$region = $connect->getOne("SELECT id_reg FROM object WHERE id=?i", $object);
		if($region == 3){
			$data = $connect->getAll("SELECT id FROM users WHERE office=3");
		}elseif($region == 2 OR $region == 14 OR $region == 22 OR $region == 13){
			$data = $connect->getAll("SELECT id FROM users WHERE office=4");
		}elseif($region == 6)
			$data = $connect->getAll("SELECT id FROM users WHERE office=2");
		else
			$data = $connect->getAll("SELECT id FROM users WHERE office=1");
		foreach($data as $row){
			$user = $row["id"];
			if($connect->getOne("SELECT id FROM chat_users WHERE id_user=?i AND status=1", $user)){
				if($add)
					$add.= " OR ";
				$add.= " id_user=".$user;
			}
		}
		if($add)
			$add = " AND (".$add.")";
	}
	$data = $connect->getAll("SELECT id, id_user, login FROM chat_users WHERE status=1 AND user_group=?i ".$add, $group);
	foreach($data as $row)
		$users[$row["id"]] = 1;
	$index = array_rand($users);
	if($index)
		return $index;
	return FALSE;
}

?>
