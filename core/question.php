<?php

function show_question_agency($connect){
	$category_array = get_status_array($connect, "question_category");
?>
	<div class="form-horizontal panel panel-default">
		<div class="panel-heading"><i class="fa fa-comments-o"></i> Вопросы из Личного кабинета агентств</div>
		<div class="list-group">
<?php
	$data = $connect->getAll("SELECT talk.id, talk.client, talk.category, talk.id_reck, DATE_FORMAT(max(message_talk.date), '%d.%m.%Y %H:%i:%s ') as time FROM talk JOIN message_talk ON message_talk.talk=talk.id WHERE talk.type='agency' GROUP BY talk.id ORDER BY message_talk.active, max(message_talk.date) DESC LIMIT 30");
	foreach($data as $row){
		$talk = $row["id"];
		$id_reck = $row["id_reck"];
		$time = $row["time"];
		$new = $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i AND active=0 AND type='client'", $talk);
		$client = $row["client"];
		$agency = $connect->getOne("SELECT short_name FROM agency WHERE id=?i", $client);
		$category = $category_array[$row["category"]];
		$class = "";
		if($new > 0)
			$class = " list-group-item-info ";
	?>
		<div class="list-group-item form-group <?php echo $class; ?>" style="margin: 0">
			<div class="col-sm-4">
				<a class="link" onclick="select_agency(<?php echo $client; ?>)"><i class="fa fa-user"></i> <?php echo $agency; ?></a>
			</div>
			<div class="col-sm-4">
				<?php echo $category; ?>
				<?php if($id_reck){
					$manager = "";
					$user = $connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $id_reck);
					if($user)
						$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $user);
				?>

					<a class="link" onclick="show_turist(<?php echo $client; ?>, <?php echo $id_reck; ?>, 'agency')">№<?php echo $id_reck; ?></a> <?php echo $manager; ?>
				<?php } ?>
			</div>
			<div class="col-sm-2">
				<?php echo $time; ?>
			</div>
			<div class="col-sm-2">
				<button type="button" class="btn btn-success btn-xs" onclick="view_talk('<?php echo $talk; ?>')"><i class="fa fa-comment-o"></i> Смотреть</button>
			<?php if($new > 0){ ?>
				<span class="label label-danger"><i class="fa fa-envelope-o"></i> <?php echo $new; ?></span>
			<?php } ?>
			</div>
		</div>
	<?php
	}
	?>
		</div>
	</div>
	<?php
}

function show_question_client($connect){
	$category_array = get_status_array($connect, "question_category");
?>
	<div class="form-horizontal panel panel-default">
		<div class="panel-heading"><i class="fa fa-comments-o"></i> Вопросы из Личного кабинета туриста</div>
		<div class="list-group">
<?php
	$data = $connect->getAll("SELECT talk.id, talk.client, talk.category, talk.id_reck, DATE_FORMAT(max(message_talk.date), '%d.%m.%Y %H:%i:%s') as time FROM talk JOIN message_talk ON message_talk.talk=talk.id WHERE talk.type='turist' AND message_talk.type='client' GROUP BY talk.id ORDER BY message_talk.active, max(message_talk.date) DESC LIMIT 100");
	foreach($data as $row){
		$talk = $row["id"];
		$time = $row["time"];
		$new = $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i AND active=0 AND type='client'", $talk);
		$client = $row["client"];
		$id_reck = $row["id_reck"];
		$fio = select_name_klient($connect, $client);
		$category = $category_array[$row["category"]];
		$class = "";
		if($new > 0)
			$class = " list-group-item-info ";
	?>
		<div class="list-group-item form-group <?php echo $class; ?>" style="margin: 0">
			<div class="col-sm-4">
				<a class="link" onclick="select_klient(<?php echo $client; ?>)"><i class="fa fa-user"></i> <?php echo $fio; ?></a>
			</div>
			<div class="col-sm-4">
				<?php echo $category; ?>
				<?php if($id_reck){
					$manager = "";
					$user = $connect->getOne("SELECT id_user FROM reckoning WHERE id=?i", $id_reck);
					if($user)
						$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $user);
				?>

					<a class="link" onclick="show_turist(<?php echo $client; ?>, <?php echo $id_reck; ?>, 'turist')">№<?php echo $id_reck; ?></a> <?php echo $manager; ?>
				<?php } ?>
			</div>
			<div class="col-sm-2">
				<?php echo $time; ?>
			</div>
			<div class="col-sm-2">
				<button type="button" class="btn btn-success btn-xs" onclick="view_talk('<?php echo $talk; ?>')"><i class="fa fa-comment-o"></i> Смотреть</button>
			<?php if($new > 0){ ?>
				<span class="label label-danger"><i class="fa fa-envelope-o"></i> <?php echo $new; ?></span>
			<?php } ?>
			</div>
		</div>
	<?php
	}
	?>
		</div>
	</div>
	<?php
}

function show_question_object($connect){
	$category_array = get_status_array($connect, "question_category");
?>
	<div class="form-horizontal panel panel-default">
		<div class="panel-heading"><i class="fa fa-comments-o"></i> Вопросы из Личного кабинета объекта</div>
		<div class="list-group">
<?php
	$data = $connect->getAll("SELECT talk.id, talk.client, talk.category, talk.id_reck, DATE_FORMAT(max(message_talk.date), '%d.%m.%Y %H:%i:%s') as time FROM talk LEFT JOIN message_talk ON message_talk.talk=talk.id WHERE talk.type='object' GROUP BY talk.id ORDER BY message_talk.active, max(message_talk.date) DESC LIMIT 30");
	foreach($data as $row){
		$talk = $row["id"];
		$id_reck = $row["id_reck"];
		$time = $row["time"];
		$new = $connect->getOne("SELECT COUNT(*) FROM message_talk WHERE talk=?i AND active=0 AND type='client'", $talk);
		$client = $row["client"];
		if ($client>0) {
			$object = $connect->getOne("SELECT name FROM object WHERE id_account=?i", $client);
		} else $object = 'Обращение без авторизации';
		$category = $category_array[$row["category"]];
		$class = "";
		if($new > 0)
			$class = " list-group-item-info ";
	?>
		<div class="list-group-item form-group <?php echo $class; ?>" style="margin: 0">
			<div class="col-sm-4">
				<i class="fa fa-home"></i> <?php echo $object; ?>
			</div>
			<div class="col-sm-4">
				<?php echo $category; ?>
			</div>
			<div class="col-sm-2">
				<?php echo $time; ?>
			</div>
			<div class="col-sm-2">
				<button type="button" class="btn btn-success btn-xs" onclick="view_talk(<?php echo $talk; ?>)"><i class="fa fa-comment-o"></i> Смотреть</button>
			<?php if($new > 0){ ?>
				<span class="label label-danger"><i class="fa fa-envelope-o"></i> <?php echo $new; ?></span>
			<?php } ?>
			</div>
		</div>
	<?php
	}
	?>
		</div>
	</div>
	<?php
}

function view_talk($connect){
	$html = "";
	$no_comment = 1;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT client, type, id_reck FROM talk WHERE id=?i", $id);
	$type = $row["type"];
	$id_client = $row["client"];
	if($type == "turist")
		$client = select_name_klient($connect, $id_client);
	elseif($type == "object"){
		$object = $connect->getOne("SELECT id FROM object WHERE id_account=?i", $id_client);
		$client = get_object($connect, $object, "place");
	}else
		$client = $connect->getOne("SELECT short_name FROM agency WHERE id=?i", $id_client);
	$bid = $row["id_reck"];
	$data = $connect->getAll("SELECT id FROM message_talk WHERE talk=?i ORDER BY date", $id);
	if($connect->getOne("SELECT id FROM message_talk WHERE type='client' AND talk=?i AND active=0", $id))
		$no_comment = 0;
	ob_start();
?>
	<button type="button" class="btn btn-warning btn-xs" onclick="show_prev_page()"><i class="fa fa-angle-double-left"></i> назад</button>
	<div class="form-horizontal panel panel-default talk-client" style="margin-top: 10px;">
		<div class="panel-heading">
			<i class="fa fa-comments-o"></i> Беседа с <?php echo $client; ?>
		</div>
		<div class="panel-body talk-messages">
			<?php foreach($data as $row){ echo write_talk_message($connect, $row["id"]); } ?>
		</div>
		<div class="panel-footer text-right">
			<textarea class="form-control text-answer" style="margin-bottom: 10px"></textarea>
		<?php if($bid){ ?>
			<button type="button" class="btn btn-primary btn-sm" onclick="show_turist(<?php echo $id_client; ?>, <?php echo $bid; ?>, '<?php echo $type; ?>')"><i class="fa fa-file-text"></i> Перейти в заявку</button>
		<?php } ?>
			<button type="button" class="btn btn-success btn-sm" onclick="answer_client_question(<?php echo $id; ?>)"><i class="fa fa-envelope-o"></i> Отправить</button>
		<?php if($no_comment == 0){ ?>
			<button type="button" class="btn btn-default btn-sm" onclick="no_comment_client_question(<?php echo $id; ?>)"><i class="fa fa-check-circle"></i> Оставить без комментария</button>
		<?php } ?>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function no_comment_client_question($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE message_talk SET active=1 WHERE type='client' AND talk=?i", $id);
}

function write_talk_message($connect, $id){
	$row = $connect->getRow("SELECT DATE_FORMAT(date, '%H:%i:%s %d.%m.%Y') as date_mess, text, type, user, active FROM message_talk WHERE id=?i", $id);
	$new = "";
	if($row["active"] == 0)
		$new = "<span class='label label-primary'>new</span>";
	if($row["type"] == "client"){
		$class = " message-client panel-info ";
		$label = $row["date_mess"];
	}else{
		$class = " message-manager panel-success ";
		$label = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["user"]).", ".$row["date_mess"];
	}
	$text = $row["text"];
	$text = str_replace("\n", "<br />", $text);
	ob_start();
?>
	<div class="panel message-talk <?php echo $class; ?>">
		<div class="panel-heading"><?php echo $new; ?> <?php echo $text; ?></div>
		<div class="panel-footer text-right"><?php echo $label; ?></div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

?>
