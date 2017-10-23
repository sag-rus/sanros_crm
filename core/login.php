<?php

function write_authorization_form($connect){
	ob_start();
?>
<div class="popup-substrate">
	<div class="form-horizontal form-authorization">
		<div class="form-group">
			<div class="col-sm-12">
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-user"></i></span>
					<input type="text" class="form-control" id="login" placeholder="Логин" onKeyPress="if(event.keyCode == 13) login()">
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-12">
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-key"></i></span>
					<input type="password" class="form-control" id="password" placeholder="Пароль" onKeyPress="if(event.keyCode == 13) login()">
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-12">
				<button type="button" style="width: 100%;" class="btn btn-success btn-sm" onClick="login()"><i class="fa fa-sign-in"></i> Вход</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function check_login($connect){
	if(isset($_COOKIE["session"]) AND !isset($_POST["password"])){
		$session = $_COOKIE["session"];
		$row = $connect->getRow("SELECT login, password FROM session WHERE id_session=?s", $session);
		$login = $row["login"];
		$password = $row["password"];
	}else{
		if(!isset($_POST["login"]))
			return;
		$login = $_POST["login"];
		$password = md5($_POST["password"]);
	}
	if($login AND $password){
		$row = $connect->getRow("SELECT password, dostup FROM users WHERE login=?s", $login);
		$true_pass = $row["password"];
		if(($true_pass == $password) AND ($row["dostup"] == 1)){
			$id_login = $connect->getOne("SELECT id FROM users WHERE login=?s", $login);
			$session = md5(uniqid());
			setCookie("session", $session, time()+60*60*24);
			$connect->query("DELETE FROM session WHERE login=?s", $login);
			$connect->query("INSERT INTO session(id_session, login, password) VALUES(?s, ?s, ?s)", $session, $login, $password);
			$connect->query("UPDATE users SET date_last_in=?s WHERE login=?s", date("d-m-Y H:i:s"), $login);
			$connect->query("UPDATE users SET update_system=0 WHERE id=?i", $id_login);
			$connect->query("UPDATE users SET chat_status=1 WHERE id=?i", $id_login);
			return $login;
		}elseif($row["dostup"] == 0)
			return FALSE;
		elseif($true_pass != $password){
			if(isset($_COOKIE["session"]))
				setCookie("session", "");
		}
	}elseif(isset($_COOKIE["session"])){
		setCookie("session", "");
	}
	return FALSE;
}

function logout($connect){
	global $session_login;
	setCookie("session", "");
	logout_sitehelp($connect);
	$connect->query("UPDATE users SET chat_status=0 WHERE id=?i", $session_login);
}

?>
