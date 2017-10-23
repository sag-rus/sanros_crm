<?php

function form_send_password_client($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT email, login FROM klient WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Выслать логин и пароль туристу</h4>
			</div>
			<div class="list-group form-horizontal">
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Турист</label>
						<div class="col-sm-9">
							<?php echo select_name_klient($connect, $id); ?>
						</div>
					</div>
				</div>
					<?php if($row["email"]){ ?>
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Email</label>
						<div class="col-sm-9">
							<?php echo $row["email"]; ?>
						</div>
					</div>
				</div>
						<?php if($row["login"]){ ?>
					<div class="list-group-item list-group-item-danger">
						<i class="fa fa-exclamation-triangle"></i> Логин и пароль уже высылались
					</div>
						<?php } ?>
					<?php }else{ ?>
				<div class="list-group-item list-group-item-danger">
					<i class="fa fa-exclamation-triangle"></i> Email не указан
				</div>
					<?php }?>
			</div>
			<?php if($row["email"]){ ?>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm btn-send-mail" onclick="create_account_client('<?php echo $id; ?>')"><i class="fa fa-envelope-o"></i> Выслать пароль</button>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php
}

function form_send_password_agency($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, email, login FROM agency WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Выслать логин и пароль агентству</h4>
			</div>
			<div class="list-group form-horizontal">
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Агентство</label>
						<div class="col-sm-9">
							<?php echo $row["name"]; ?>
						</div>
					</div>
				</div>
				<?php if($row["email"]){ ?>
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Email</label>
						<div class="col-sm-9">
							<?php echo $row["email"]; ?>
						</div>
					</div>
				</div>
				<?php if($row["login"]){ ?>
				<div class="list-group-item list-group-item-danger">
					<i class="fa fa-exclamation-triangle"></i> Логин и пароль уже высылались
				</div>
				<?php } ?>
				<?php }else{ ?>
				<div class="list-group-item list-group-item-danger">
					<i class="fa fa-exclamation-triangle"></i> Email не указан
				</div>
				<?php }?>
			</div>
			<?php if($row["email"]){ ?>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm btn-send-mail" onclick="send_login_agency('<?php echo $id; ?>')"><i class="fa fa-envelope-o"></i> Выслать пароль</button>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_send_mail($connect){
	$id = $_POST["id"];
	$doc = $_POST["doc"];
	$email = getEmailByReck($connect, $id);
	if($doc == "schet")
		$uved = "о счете";
	elseif($doc == "obmen")
		$uved = "об оплате";
	elseif($doc == "cancel")
		$uved = "об аннуляции";
	elseif($doc == "changes")
		$uved = "об изменениях";
	$row = $connect->getRow("SELECT turist, agency FROM reckoning WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Отправка уведомления <?php echo $uved; ?> №<?php echo $id; ?></h4>
			</div>
			<div class="list-group form-horizontal">
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Получатель</label>
						<div class="col-sm-9">
						<?php if($row["agency"]){ ?>
							<?php echo $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]); ?>
						<?php }else{ ?>
							<?php echo select_name_klient($connect, $row["turist"]); ?>
						<?php } ?>
						</div>
					</div>
				</div>
				<?php if($email){ ?>
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Email</label>
						<div class="col-sm-9">
							<?php echo $email; ?>
						</div>
					</div>
				</div>
				<?php }else{ ?>
				<div class="list-group-item list-group-item-danger">
					<i class="fa fa-exclamation-triangle"></i> Email не указан
				</div>
				<?php }?>
			</div>
			<div class="modal-footer">
				<?php if($row["agency"]){ ?>
					<button type="button" class="btn btn-success btn-sm btn-send-mail" onclick="send_mail_agency('<?php echo $id; ?>', '<?php echo $doc; ?>')"><i class="fa fa-envelope-o"></i> Отправить</button>
				<?php }else{ ?>
					<button type="button" class="btn btn-success btn-sm btn-send-mail" onclick="send_mail_client('<?php echo $id; ?>', '<?php echo $doc; ?>')"><i class="fa fa-envelope-o"></i> Отправить</button>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php
}

function form_send_confirm_rating($connect){
	$id = $_POST["id"];
	$schet = $connect->getOne("SELECT schet FROM rating WHERE id=?i AND status=3", $id);
	$turist = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $schet);
	$row = $connect->getRow("SELECT surname, name, email FROM klient WHERE id=?i", $turist);
	$email = $row["email"];
	if(!$schet OR !$email)
		return FALSE;
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Выслать уведомление о принятии отзыва</h4>
			</div>
			<div class="form-horizontal list-group list-group-margin">
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Турист</label>
						<div class="col-sm-9 label-text">
							<?php echo $row["surname"]." ".$row["name"]; ?>
						</div>
					</div>
				</div>
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Email</label>
						<div class="col-sm-9 label-text">
							<?php echo $email; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm btn-send-mail" onclick="send_mail_confirm_rating('<?php echo $id; ?>')"><i class="fa fa-envelope-o"></i> Выслать уведомление</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function form_send_confirm_rating_comment($connect){
	$id = $_POST["id"];
	$email = array();
	$row = $connect->getRow("SELECT rating, email FROM rating_comment WHERE id=?i", $id);
	$rating = $row["rating"];
	$no_email = $row["email"];
	$reck = $connect->getOne("SELECT schet FROM rating WHERE id=?i", $rating);
	if($reck){
		$client = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $reck);
		$send = $connect->getOne("SELECT email FROM klient WHERE id=?i AND email!=?s", $client, $no_email);
		if($send)
			$email[] = $send;
	}
	$data = $connect->getAll("SELECT email FROM rating_comment WHERE id!=?i AND email!=?s AND email!='' AND rating=?i", $id, $no_email, $rating);
	foreach($data as $row)
		$email[] = $row["email"];
	if(count($email) == 0)
		return FALSE;
	$email = array_unique($email);
	$string = implode(", ", $email);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Выслать уведомление о принятии комментария</h4>
			</div>
			<div class="form-horizontal list-group list-group-margin">
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Email</label>
						<div class="col-sm-9 label-text email-string"><?php echo $string; ?></div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm btn-send-mail" onclick="send_mail_confirm_rating_comment(<?php echo $id; ?>)"><i class="fa fa-envelope-o"></i> Выслать уведомление</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function show_send_login_object_account($connect){
	$account = $_POST["account"];
	$email = $connect->getOne("SELECT email FROM object_account WHERE id=?i", $account);
	return json_encode($email);

?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Выслать логин объекту</h4>
			</div>
			<div class="form-horizontal list-group list-group-margin">
				<div class="list-group-item email-object">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label-element">Выберите email</label>
						<div class="col-sm-9 label-text">
				<?php foreach($data as $row){
					$array = json_decode($row["email"], TRUE);
					$object = get_object($connect, $row["id"]);
					foreach($array as $value){
				?>
					<div class="form-group">
						<label class="col-sm-8">
							<input type="radio" name="object-email" value="<?php echo $value['value']; ?>" />
							<strong><?php echo $value["value"]; ?></strong>
							<?php echo $value["note"]; ?>
						</label>
						<div class="col-sm-4">
							<?php echo $object; ?>
						</div>
					</div>
				<?php
					}
				}
				?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm btn-send-mail" onclick="send_login_object_account(<?php echo $account; ?>)"><i class="fa fa-envelope-o"></i> Выслать логин</button>
			</div>
		</div>
	</div>
</div>
<?php
}

?>
