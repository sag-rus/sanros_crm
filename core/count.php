<?php

function check_menu_count($connect){
	global $session_login, $id_rights;
	$data = array();
	if(!$session_login)
		return;

	$data["new-bid"] = check_new_bid_count($connect);
	$data["question"] = check_question_count($connect);
	$data["no-price"] = $connect->getOne("SELECT value FROM constant WHERE name='no-price-object-count'");
	if($id_rights > 3){
		$data["rating"] = check_rating_count($connect);
		$data["object"] = check_object_count($connect);
	}
	$data["reminder"] = check_reminder_count($connect);
	$data["check-update"] = check_update_system($connect);
	$data["check-message"] = check_new_messages($connect);
	$data["check-notification"] = check_new_notification($connect);
	$data["check-news"] = check_news_post($connect);
	return json_encode($data);
}

function check_new_bid_count($connect){
	$data = array();
	$data["reckoning"] = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (id_user='' OR id_user IS NULL OR status=10 OR status=11) AND active!=3");
	$data["call"] = $connect->getOne("SELECT COUNT(*) FROM order_call_back WHERE (id_user='' OR id_user IS NULL) AND active!=3");
	return $data;
}

function check_rating_count($connect){
	$data = array();
	$data["rating"] = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=2");
	$data["comment"] = $connect->getOne("SELECT COUNT(*) FROM rating_comment WHERE status=0");
	$data["all"] = $data["comment"] + $data["rating"];
	return $data;
}

function check_object_count($connect){
	$data = array();
	$data["check"] = $connect->getOne("SELECT COUNT(*) FROM object WHERE status=2");
	$data["new"] = $connect->getOne("SELECT COUNT(*) FROM object_request WHERE status=0");
	$data["all"] = $data["check"] + $data["new"];
	return $data;
}

function check_question_count($connect){
	$data = array();
	$data["turist"] = $connect->getOne("SELECT COUNT(*) FROM message_talk, talk WHERE active=0 AND talk.id=message_talk.talk AND talk.type='turist' AND message_talk.type='client'");
	$data["agency"] = $connect->getOne("SELECT COUNT(*) FROM message_talk, talk WHERE active=0 AND talk.id=message_talk.talk AND talk.type='agency' AND message_talk.type='client'");
	$data["object"] = $connect->getOne("SELECT COUNT(*) FROM message_talk, talk WHERE active=0 AND talk.id=message_talk.talk AND talk.type='object' AND message_talk.type='client'");
	$data["all"] = $data["turist"] + $data["agency"] + $data["object"];
	return $data;
}

function check_reminder_count($connect){
	global $id_rights, $session_login;
	$today = date("Y-m-d");
	$future = date("Y-m-d", strToTime("+5 days"));
	$reminder = $connect->getOne("SELECT COUNT(*) FROM reminder WHERE user=?i AND active=1 AND date<=?s", $today);
	if($id_rights > 3){
		$reminder+= $connect->getOne("SELECT COUNT(*) FROM return_query WHERE active=1 AND date<=?s", date("Y-m-d", strToTime("-7 days")));
		$reminder+= $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE status=4 AND date_z<=?s", $today);
	}
	$reminder+= $connect->getOne("SELECT COUNT(*) FROM reckoning, region, object WHERE reckoning.id_obj=object.id AND ((region.id=object.id_reg AND region.id_country!=1) OR (object.id_reg is NULL OR object.id_reg='')) AND reckoning.date_z>?s AND reckoning.date_z<=?s AND status=5 AND id_user=?i GROUP BY reckoning.id", $today, $future, $session_login);
	return $reminder;
}

function check_update_system($connect){
	global $session_login;
	$update = array("update" => 0);
	if($connect->getOne("SELECT id FROM users WHERE id=?i AND update_system=1", $session_login)){
		$update["update"] = 1;
		ob_start();
?>
	<div class="warning-update body-system alert-danger pointer" onclick="location.reload()"><i class="fa fa-exclamation-triangle"></i> Система обновлена. Для корректной работы просьба обновить страницу.</div>
<?php
		$update["html"] = ob_get_clean();
	}
	return $update;
}

function check_new_messages($connect){
	global $session_login;
	$data = array();
	$data["chat"] = $connect->getOne("SELECT COUNT(*) FROM chat_room, chat_message WHERE (chat_room.first_user=?i OR chat_room.second_user=?i) AND chat_message.user!=?i AND read_message=0 AND chat_message.chat=chat_room.id", $session_login, $session_login, $session_login);
	$data["sitehelp"] = $connect->getOne("SELECT COUNT(*) FROM sitehelp_chat WHERE manager=?i AND status=0", $session_login);
	$data["all"] = $data["chat"] + $data["sitehelp"];
	$data["alert"] = $connect->getOne("SELECT alert FROM chat_users WHERE id_user=?i", $session_login);
	$data["sitehelp_login"] = $connect->getOne("SELECT id FROM chat_users WHERE id_user=?i", $session_login);
	$data["status"] = $connect->getOne("SELECT status FROM chat_users WHERE id_user=?i", $session_login);
	$connect->query("UPDATE chat_users SET last_visit=CURRENT_TIMESTAMP WHERE id_user=?i", $session_login);
	return $data;
}

function check_new_notification($connect){
	global $id_rights, $session_login;
	$data = array();
	$data["new"] = $connect->getOne("SELECT COUNT(*) FROM notification WHERE status=0 AND user=?i", $session_login);
	if($id_rights == 4)
		$data["new"]+= $connect->getOne("SELECT COUNT(*) FROM return_query WHERE active=1 AND check_pay=1");
	return $data;
}

function check_news_post($connect){
	$const = $connect->getOne("SELECT value FROM constant WHERE name='published-news'");
	return count(json_decode($const, TRUE));
}

?>
