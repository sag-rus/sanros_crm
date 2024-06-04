<?php

include_once(__DIR__."/../../config.php");
include_once(__DIR__."/../lib/sms.php");
$conf = new JConfig;
$sync = $conf->sync_base;
$CRM = $conf->CRM;
$unisender_api_key = $conf->unisender_api_key;

function see_office($connect){
    global $id_rights;
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-users"></i> Офис</div>
	<div class="list-group">
<?php
	$data = $connect->getAll("SELECT id, name FROM office");
	foreach($data as $row){
		$id = $row["id"];
	?>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<div class="col-sm-9"><?php echo $row["name"]; ?></div>
				<div class="col-sm-3">
                  <?php if($id_rights > 5) { ?>
					<button type="button" class="btn btn-default btn-xs" onclick="edit_office('<?php echo $id; ?>')">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
					<button type="button" class="btn btn-default btn-xs" onclick="edit_office_bank('<?php echo $id; ?>')">&nbsp;<i class="fa fa-university"></i>&nbsp;</button>
                  <?php } ?>
				</div>
			</div>
		</div>
	<?php
	}
?>
	</div>
</div>
<?php
}

function edit_office($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, address, telephone, present, present_text, post, print_image FROM office WHERE id=?i", $id);
	ob_start();
?>
<div class="panel panel-default edit-office">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Редактирование офиса</div>
	<div class="form-horizontal panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Название</label>
			<div class="col-sm-9">
				<input type="text" class="form-control name" value="<?php echo $row['name']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Адрес</label>
			<div class="col-sm-9">
				<input type="text" class="form-control address" value="<?php echo $row['address']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Телефон</label>
			<div class="col-sm-9">
				<input type="text" class="form-control telephone" value="<?php echo $row['telephone']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Представитель</label>
			<div class="col-sm-9">
				<input type="text" class="form-control present" value="<?php echo $row['present']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Представитель (текст)</label>
			<div class="col-sm-9">
				<input type="text" class="form-control present_text" value="<?php echo $row['present_text']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Должность</label>
			<div class="col-sm-9">
				<input type="text" class="form-control post" value="<?php echo $row['post']; ?>" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-3 control-label">Печать</label>
			<div class="col-sm-9">
				<input type="text" class="form-control print_image" value="<?php echo $row['print_image']; ?>" />
			</div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-success btn-sm" onclick="update_office('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
		<button type="button" class="btn btn-danger btn-sm" onclick="see_office()"><i class="fa fa-times-circle"></i> Отмена</button>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_office($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$address = $_POST["address"];
	$telephone = str_replace("plus", "+", $_POST["telephone"]);
	$present = $_POST["present"];
	$present_text = $_POST["present_text"];
	$post = $_POST["post"];
	$image = $_POST["image"];
	$connect->query("UPDATE office SET name=?s, address=?s, telephone=?s, present=?s, present_text=?s, post=?s, print_image=?s WHERE id=?i", $name, $address, $telephone, $present, $present_text, $post, $image, $id);
}

function edit_office_bank($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, bank, kpp, inn, rs, ks, bik FROM office WHERE id=?i", $id);
	$row = clear_quotes($row);
	ob_start();
?>
<div class="panel panel-default edit-office">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Реквизиты банка. Офис <?php echo $row["name"]; ?></div>
	<div class="form-horizontal panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Банк</label>
			<div class="col-sm-9">
				<input type="text" class="form-control bank" value="<?php echo $row['bank']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Рас.счет</label>
			<div class="col-sm-9">
				<input type="text" class="form-control rs" value="<?php echo $row['rs']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Кор.счет</label>
			<div class="col-sm-9">
				<input type="text" class="form-control ks" value="<?php echo $row['ks']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">ИНН</label>
			<div class="col-sm-9">
				<input type="text" class="form-control inn" value="<?php echo $row['inn']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">КПП</label>
			<div class="col-sm-9">
				<input type="text" class="form-control kpp" value="<?php echo $row['kpp']; ?>" />
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-3 control-label">БИК</label>
			<div class="col-sm-9">
				<input type="text" class="form-control bik" value="<?php echo $row['bik']; ?>" />
			</div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-success btn-sm" onclick="update_office_bank('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
		<button type="button" class="btn btn-danger btn-sm" onclick="see_office()"><i class="fa fa-times-circle"></i> Отмена</button>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_office_bank($connect){
	$id = $_POST["id"];
	$bank = $_POST["bank"];
	$kpp = $_POST["kpp"];
	$inn = $_POST["inn"];
	$rs = $_POST["rs"];
	$ks = $_POST["ks"];
	$bik = $_POST["bik"];
	$connect->query("UPDATE office SET bank=?s, inn=?s, kpp=?s, rs=?s, ks=?s, bik=?s WHERE id=?i", $bank, $inn, $kpp, $rs, $ks, $bik, $id);
}

function see_users($connect){
    global $id_rights;
?>
<div class="panel panel-default">
	<div class="panel-heading"><i class="fa fa-user"></i> Пользователи</div>
	<table class="table table-hover table-condensed">
<?php
	$data = $connect->getAll("SELECT photo, id, login, name, date_last_in, rights FROM users");
	foreach($data as $user){
		$id = $user["id"];
		if($user["photo"])
			$photo = "data:image/jpg;base64,".$user["photo"];
		else
			$photo = "images/NoPicture.jpg";
	?>
		<tr>
			<td width="10%"><img src="<?php echo $photo; ?>" class="img-thumbnail" style="height: 30px" /></td>
			<td width="30%"><?php echo $user["name"]; ?></td>
			<td width="25%"><?php echo $user["date_last_in"]; ?></td>
			<td width="20%"><?php echo $connect->getOne("SELECT name FROM rights WHERE id=?i", $user["rights"]); ?></td>
			<td width="15%">
                <?php if($id_rights > 5) { ?>
                    <button type="button" class="btn btn-default btn-xs" onclick="edit_user('<?php echo $id; ?>')">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
                    <button type="button" class="btn btn-primary btn-xs" onclick="add_photo_profile('<?php echo $id; ?>', 'user')">&nbsp;<i class="fa fa-file-image-o"></i>&nbsp;</button>
                <?php } ?>
			<?php if($connect->getOne("SELECT id FROM session WHERE login=?s AND request=1", $user["login"])){ ?>
				<button type="button" class="btn btn-danger btn-xs btn-bomb-<?php echo $id; ?>" onclick="check_request_user('<?php echo $id; ?>')">&nbsp;<i class="fa fa-bomb"></i>&nbsp;</button>
			<?php } ?>
			</td>
		</tr>
	<?php
	}
?>
	</table>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-primary btn-sm" onclick="add_new_users()"><i class="fa fa-plus-circle"></i> Добавить нового</button>
	</div>
</div>
<?php
}

function see_accounts($connect){
    global $id_rights;
  ?>
    <?php if($id_rights > 5) { ?>
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-user"></i> Пользователи</div>
        <table class="table table-hover table-condensed">
          <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>E-MAIL (login)</th>
                <th>Моб. телефон</th>
                <th>Промо-код</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $data = $connect->getAll("SELECT `klient`.`id` AS `account_id`, `klient`.`name` AS `account_name`, `klient`.`surname` AS `account_surname`, `klient`.`otch` AS `account_otch`, `login`, `klient`.`phone` AS `account_phone`, `doctor_card`.`status` AS `card_status`, `doctor_card`.`promo` AS `promo` FROM klient INNER JOIN `doctor_card` ON `doctor_card`.`uid` = `klient`.`id` WHERE `type` = 2 AND `login` IS NOT NULL AND login !='' ORDER BY (`doctor_card`.`status` = 1) DESC");
          foreach($data as $user){
            ?>
              <tr>
                  <td><?=$user['account_id'];?></td>
                  <td><?=$user['account_surname'];?> <?=$user['account_name'];?> <?=$user['account_otch'];?></td>
                  <td><?=$user['login'];?></td>
                  <td>+<?=$user['account_phone'];?></td>
                  <td><?=$user['promo'];?></td>
                  <td>
                      <?php
                      switch ((int)$user['card_status']) {
                        case 0:
                            echo '<div class="text-danger">Новый</div>';
                            break;
                        case 1: echo '<div class="text-warning">На модерации</div>';
                            break;
                        case 2: echo '<div class="text-danger">Не прошёл модерацию</div>';
                            break;
                        case 3: echo '<div class="text-success">Активирован</div>';
                            break;
                        case 4: echo '<div class="text-success">Заблокирован</div>';
                            break;
                      }
                      ?>
                  </td>
                  <td>
                      <button type="button" class="btn btn-default btn-xs" onclick="edit_account(<?=$user['account_id']?>)">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
                  </td>
              </tr>
            <?php
          }
          ?>
          </tbody>
        </table>
    </div>
    <?php } else  { ?>
    <div class="panel panel-default">
        <div class="panel-body alert-danger">
            У Вас нет прав для доступа к данной странице...
        </div>
    </div>
    <?php } ?>
  <?php
}

function edit_user($connect){
	$id = $_POST["id"];
	$user = $connect->getRow("SELECT login, name, surname, email, telephone, rights, channel, note, office, id_group, class FROM users WHERE id=?i", $id);
	$select_rights = get_select_rights($connect, $user["rights"]);
	ob_start();
?>
<div class="panel panel-default edit-user">
	<div class="panel-heading"><i class="fa fa-pencil"></i> Редактирование пользователя</div>
	<div class="form-horizontal panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Логин</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="login" value="<?php echo $user['login']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Новый пароль</label>
			<div class="col-sm-9">
				<input type="password" class="form-control" id="password" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Повтор пароля</label>
			<div class="col-sm-9" id="regions">
				<input type="password" class="form-control" id="password_1" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Доступ</label>
			<div class="col-sm-9">
				<?php echo $select_rights; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Имя</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="name" value="<?php echo $user['name']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Фамилия</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="surname" value="<?php echo $user['surname']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Телефон</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="telephone" value="<?php echo $user['telephone']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Email</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="email" value="<?php echo $user['email']; ?>" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Офис</label>
			<div class="col-sm-9">
				<?php echo get_select_table($connect, "office", "", $user["office"], "user-office", 1); ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Группа</label>
			<div class="col-sm-9">
				<?php echo get_select_table($connect, "groups", "", $user["id_group"], "user-group", 1); ?>
			</div>
		</div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Роль</label>
            <div class="col-sm-9">
                <select id="user-role" class="form-control">
                    <option value="0"<?php if($user['class'] != 1) { ?> selected<?php } ?>>Пользователь</option>
                    <option value="1"<?php if($user['class'] == 1) { ?> selected<?php } ?>>Менеджер</option>
                </select>
            </div>
        </div>
	</div>
	<div class="form-horizontal panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="button" class="btn btn-success btn-sm" onclick="update_user('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
				<button type="button" class="btn btn-danger btn-sm" onclick="see_users()"><i class="fa fa-times-circle"></i> Отмена</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function edit_account($connect){
  $id = $_POST["id"];
  $user = $connect->getRow("SELECT `klient`.`id` AS `account_id`, `klient`.`name` AS `account_name`, `klient`.`surname` AS `account_surname`, `klient`.`otch` AS `account_otch`, `klient`.`login` AS `account_login`, `klient`.`phone` AS `account_phone`, `doctor_card`.`status` AS `card_status`, `doctor_card`.`moderation_comment` AS `card_moderation_comment` FROM klient INNER JOIN `doctor_card` ON `doctor_card`.`uid` = `klient`.`id` WHERE `type` = 2 AND `login` IS NOT NULL AND login !='' AND `klient`.`id` = ?i LIMIT 1",$id);
  ob_start();
  ?>
    <?php
    if($user) {
      ?>
        <div class="panel panel-default edit-account">
            <div class="panel-heading"><i class="fa fa-pencil"></i>
                Редактирование пользователя <?= $user['account_login']; ?></div>
            <div class="form-horizontal panel-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label">ID</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="account-id" value="<?= $user['account_id']; ?>" disabled>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Логин</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="login" value="<?= $user['account_login']; ?>" disabled>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Мобильный телефон</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="phone" value="+<?= $user['account_phone']; ?>" disabled>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Фамилия *</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="surname" value="<?= $user['account_surname']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Имя *</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name" value="<?= $user['account_name']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Отчество *</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="otch" value="<?= $user['account_otch']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Статус</label>
                    <div class="col-sm-9">
                        <select class="form-control" id="status">
                            <option value="1"<?php if($user['card_status'] == 1) { ?> selected<?php } ?>>На модерации</option>
                            <option value="2"<?php if($user['card_status'] == 2) { ?> selected<?php } ?>>Не прошёл модерацию</option>
                            <option value="3"<?php if($user['card_status'] == 3) { ?> selected<?php } ?>>Активирован</option>
                            <option value="4"<?php if($user['card_status'] == 4) { ?> selected<?php } ?>>Заблокирован</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Комментарий модератора</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="moderation-comment"><?=$user['card_moderation_comment'];?></textarea>
                    </div>
                </div>
            </div>
            <div class="form-horizontal panel-footer">
                <div class="form-group form-group-margin">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="button" class="btn btn-success btn-sm"
                                onclick="update_account('<?php echo $id; ?>')">
                            <i class="fa fa-check-circle"></i> Сохранить
                        </button>
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="see_accounts()"><i
                                    class="fa fa-times-circle"></i> Отмена
                        </button>
                    </div>
                </div>
            </div>
        </div>
      <?php
    }
    else {
    ?>
    <div class="panel panel-default">
        <div class="row">
            <div class="col-md-12 text-danger text-center">
                <div class="panel-body">
                    Ошибка! Пользователь не найден...
                </div>
            </div>
            <div class="col-md-12 text-center">
                <div class="panel-body">
                    <button type="button" class="btn btn-danger btn-sm" onclick="see_accounts()">Назад</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
  <?php
  $html = ob_get_clean();
  return $html;
}


function update_user($connect){
	$id = $_POST["id"];
	$login = $_POST["login"];
	$password = "";
	if($_POST["password"])
		$password = md5($_POST["password"]);
	$name = $_POST["name"];
	$surname = $_POST["surname"];
	$email = $_POST["email"];
	$telephone = $_POST["telephone"];
	$rights = $_POST["rights"];
	$office = $_POST["office"];
	$group = $_POST["group"];
	$role = (int)$_POST['role'];

	if($connect->getOne("SELECT id FROM users WHERE id!=?i AND login=?s", $id, $login))
		return 2;

	if($role)
	    $connect->query("UPDATE users SET login=?s, name=?s, surname=?s, email=?s, telephone=?s, rights=?i, office=?s, id_group=?s, class = ?i WHERE id=?i", $login, $name, $surname, $email, $telephone, $rights, $office, $group, $role, $id);
	else
	    $connect->query("UPDATE users SET login=?s, name=?s, surname=?s, email=?s, telephone=?s, rights=?i, office=?s, id_group=?s, class = NULL WHERE id=?i", $login, $name, $surname, $email, $telephone, $rights, $office, $group, $id);

	if($password)
		$connect->query("UPDATE users SET password=?s WHERE id=?i", $password, $id);
	return 1;
}


function update_account($connect){
  $id = $_POST["id"];
  $name = "";
  $surname = "";
  $otch = "";
  $status = 0;
  $moderation_comment = "";

  $respAr = [
    'title' => '',
    'msg' => '',
    'success' => 0
  ];

  if(isset($_POST['name']))
      $name = trim($_POST["name"]);

  if(isset($_POST['surname']))
      $surname = trim($_POST["surname"]);

  if(isset($_POST["otch"]))
      $otch = trim($_POST["otch"]);

  if(isset($_POST['status']))
      $status = (int)$_POST['status'];

  if(isset($_POST['moderation_comment']))
      $moderation_comment = trim($_POST['moderation_comment']);

  if($name && $surname && $otch && in_array($status,[0,1,2,3,4])) {
      if(($status === 0 || $status === 2) && !$moderation_comment) {
        $respAr['msg'] = "Укажите причину блокировки аккаунта в комментарии модератора";
      }
      else {
        $respAr['success'] = 1;
        $user = $connect->getRow("SELECT `id`, `phone` FROM `klient` WHERE `id` = ?i LIMIT 1",$id);
        $doctorCard = $connect->getRow("SELECT `status` FROM `doctor_card` WHERE `uid` = ?i LIMIT 1",$id);

        if($doctorCard && $user) {
          $doctorCard['status'] = (int)$doctorCard['status'];
          if($status === 3)
            $moderation_comment = "";

          if($doctorCard['status'] !== $status) {
              $msg = "Уважаемый ".$name." ".$otch."!";

              if($status === 0)
                  $msg .= " Ваш аккаунт перевед в режим нового. Подброности вы можете уточнить в личном кабинете.";
              elseif($status === 1)
                $msg .= " Ваш аккаунт переведён в режим модерации. Подробности вы можете уточнить в личном кабинете.";
              elseif($status === 2)
                $msg .= " Ваш аккаунт не прошёл модерацию. Подробности вы можете уточнить в личном кабинете.";
              elseif ($status === 3)
                $msg .= " Ваш аккаунт успешно прошёл модерацию и теперь активен. Поздравляем и надеемся на долгое и плодотворное сотрудничество!";
              elseif ($status === 4)
                $msg .= " Ваш аккаунт заблокирован. Подброности вы можете уточнить в личном кабинете.";

              send_sms($connect,$user['phone'],NULL,$msg,"moderation_result");

          }

          $connect->query("UPDATE `klient` SET `name` = ?s, `surname` = ?s, `otch` = ?s WHERE id = ?i LIMIT 1",$name,$surname,$otch,$id);
          $connect->query("UPDATE `doctor_card` SET `status` = ?i, `moderation_comment` = ?s WHERE `uid` = ?i", $status, $moderation_comment, $id);
        }
        else {
            $respAr['msg'] = "Не найдена карточка врача";
        }

      }
  }
  else {
      $respAr['msg'] = "Некорректные входные данные";
  }

  return json_encode($respAr);
}

function add_new_user($connect){
	$select_rights = get_select_rights($connect);
	ob_start();
?>
<div class="panel panel-default new-user">
	<div class="panel-heading"><i class="fa fa-plus-square-o"></i> Новый пользователь</div>
	<div class="form-horizontal panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Логин</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="login" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Пароль</label>
			<div class="col-sm-9">
				<input type="password" class="form-control" id="password" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Повтор пароля</label>
			<div class="col-sm-9">
				<input type="password" class="form-control" id="password_1" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Доступ</label>
			<div class="col-sm-9">
				<?php echo $select_rights; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Имя</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="name" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Фамилия</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="surname" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Телефон</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="telephone" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Email</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="email" />
			</div>
		</div>
	</div>
	<div class="form-horizontal panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="button" class="btn btn-success btn-sm" onclick="save_user()"><i class="fa fa-check-circle"></i> Сохранить</button>
				<button type="button" class="btn btn-danger btn-sm" onclick="see_users()"><i class="fa fa-times-circle"></i> Отмена</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_user($connect){
	$login = $_POST["login"];
	$password = md5($_POST["password"]);
	$name = $_POST["name"];
	$surname = $_POST["surname"];
	$email = $_POST["email"];
	$telephone = $_POST["telephone"];
	$rights = $_POST["rights"];

	if($connect->getOne("SELECT id FROM users WHERE login=?s", $login))
		return 2;
	$connect->query("INSERT INTO users(login, password, name, surname, email, telephone, rights, date_last_in) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?i, 'Никогда')", $login, $password, $name, $surname, $email, $telephone, $rights);
	return 1;
}

function show_form_photo_profile(){
?>
<div class="modal fade">
	<div class="modal-dialog form-photo">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Загрузите фото</h4>
			</div>
			<div class="modal-body new-photo"></div>
			<div class="modal-footer center">
				<div class="div-download">
					<button type="button" class="btn btn-info download-photo-button"><i class="fa fa-upload"></i> Загрузить</button>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}

function resize_photo(){
	$photo = $_SESSION["new_photo"];
	$left = $_POST["left"];
	$top = $_POST["top"];

	$image = imageCreateFromJPEG($photo);
	$real_w = imagesx($image);
	$real_h = imagesy($image);
	$raz = ($real_w / 300);
	if($real_w > $real_h)
		$crop = $real_h;
	else
		$crop = $real_w;
	$left = $left * $raz;
	$top = $top * $raz;
	$new_image = imageCreateTrueColor(300, 300);
	imageCopyResampled($new_image, $image, 0, 0, $left, $top, 300, 300, $crop, $crop);
	imageJPEG($new_image, $photo, 75);
	imageDestroy($new_image);
	return $photo;
}

function save_photo_profile($connect){
	$id = $_POST["id"];
	$type = $_POST["type"];
	$photo = $_SESSION["new_photo"];
	$base64 = insert_base64_encoded_image($photo);
	if($base64 != ""){
		if($type == "user" OR $type == "chat")
			$connect->query("UPDATE users SET photo=?s WHERE id=?i", $base64, $id);
		else
			$connect->query("UPDATE object SET image=?s, synchronized=0 WHERE id=?i", $base64, $id);
		return "data:image/jpg;base64,".$base64;
	}else
		return FALSE;
}

function check_request_user($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE session SET request=0 WHERE login=?s", $connect->getOne("SELECT login FROM users WHERE id=?i", $id));
}

function show_cabinet_object($connect){
	$disabled = "";
	ob_start();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<button class="btn btn-info btn-sm" onclick="new_object_account()"><i class="fa fa-plus-circle"></i> Новый аккаунт</button>
	</div>
	<div class="form-horizontal">
<?php
	$data = $connect->getAll("SELECT id, login, email FROM object_account");
	foreach($data as $row){
		$id = $row["id"];
		$email = $row["email"];
		$disabled = "";
		$objects = $connect->getAll("SELECT id, name, type, image, id_reg, city FROM object WHERE id_account=?i", $id);
?>
		<div class="panel-success object-account-<?php echo $id; ?>" style="margin-top: 5px">
			<div class="panel-heading"><span class="h4 name-login"><?php echo $row["login"]; ?></span></div>
			<div class="list-group">
			<?php
				foreach($objects as $object){
					$image = "temp/default.jpg";
					if($object["image"])
						$image = "data:image/jpg;base64,".$object["image"];
					$address = $connect->getOne("SELECT name FROM region WHERE id=?i", $object["id_reg"]);
					if($object["city"])
					$address.= ", ".$object["city"];
					$type = $connect->getOne("SELECT name FROM type_object WHERE id=?i", $object["type"]);
			?>
				<div class="list-group-item object-<?php echo $object['id']; ?>">
					<div class="form-group form-group-margin">
						<div class="col-sm-10">
							<img src="<?php echo $image; ?>" class="img-head-small" />
							<?php echo $type." ".$object["name"]; ?>
							<?php if($address){ ?>
								<address><i class="fa fa-map-marker"></i> <?php echo $address; ?></address>
							<?php } ?>
						</div>
						<div class="col-sm-2 text-right">
							<button class="btn btn-default btn-sm" onclick="delete_object_account('<?php echo $object['id']; ?>')"><i class="fa fa-times-circle text-danger"></i></button>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="panel-footer">
				<button class="btn btn-default btn-sm" onclick="history_object_account(<?php echo $id; ?>)"><i class="fa fa-history"></i> История</button>
				<div class="pull-right">
					<button class="btn btn-success btn-sm" onclick="append_new_object_account(<?php echo $id; ?>)"><i class="fa fa-plus-circle"></i> Добавить</button>
					<button class="btn btn-default btn-sm" onclick="edit_object_account(<?php echo $id; ?>)"><i class="fa fa-pencil"></i> Изменить</button>
			<?php if(!$email){
				$disabled = " disabled='disabled' ";
			?>
					<button class="btn btn-danger btn-sm btn-assign-object-account" onclick="show_assign_email_object_account(<?php echo $id; ?>)">@ Присвоить email</button>
			<?php } ?>
					<button class="btn btn-info btn-sm btn-form-login-object-account" <?php echo $disabled; ?> onclick="show_send_login_object_account(<?php echo $id; ?>)"><i class="fa fa-envelope-o"></i> Выслать логин</button>
				</div>
			</div>
		</div>
<?php } ?>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_object_account($connect){
	$login = $_POST["login"];
	$pass = $_POST["pass"];
	if(!$connect->getOne("SELECT id FROM object_account WHERE login=?s", $login) AND $login AND $pass){
		$connect->query("INSERT INTO object_account(login, password) VALUES(?s, ?s)", $login, md5($pass));
		return 1;
	}
	return FALSE;
}

function edit_object_account($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, login, email FROM object_account WHERE id=?i", $id);
	if(!$row["email"])
		$row["email"] = "";
	return json_encode($row);
}

function update_object_account($connect){
	$id = $_POST["id"];
	$login = $_POST["login"];
	$pass = $_POST["pass"];
	$email = $_POST["email"];
	$result = 0;
	if(!$connect->getOne("SELECT id FROM object_account WHERE id!=?i AND login=?s", $id, $login) AND $login){
		$connect->query("UPDATE object_account SET login=?s, email=?s WHERE id=?i", $login, $email, $id);
		if($pass)
			$connect->query("UPDATE object_account SET password=?s WHERE id=?i", md5($pass), $id);
		$result = 1;
	}
	return json_encode($result);
}

function append_new_object_account(){
	$id = $_POST["id"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Добавить новый объект</h4>
			</div>
			<div class="modal-body form-horizontal append-object">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Объект</label>
					<div class="col-sm-8" id="object_name">
						<input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" onblur="verification_input_data('object', '1');" name="">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success btn-sm" onclick="save_new_object_account('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_object_account($connect){
	$id = $_POST["id"];
	$object = $_POST["object"];
	if(!$connect->getOne("SELECT id_account FROM object WHERE id=?i", $object)){
		$connect->query("UPDATE object SET id_account=?i, synchronized=0 WHERE id=?i", $id, $object);
		return 1;
	}
	return FALSE;
}

function delete_object_account($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE object SET id_account='', synchronized=0 WHERE id=?i", $id);
}

function check_changes_cabinet_object($connect){
?>
	<div class="panel-group" id="change-cabinet" role="tablist" aria-multiselectable="true">
<?php
	$data = $connect->getAll("SELECT id, name, description, description_check, id_services, id_account FROM object WHERE status=2");
	foreach($data as $row){
		$id_account = $row["id_account"];
		$services = json_decode($row["id_services"], TRUE);
		$array_services = $connect->getAll("SELECT id, name, icon FROM services");
		$id = $row["id"];
		if($id){
?>
	<div class="form-horizontal panel panel-info">
		<div class="panel-heading pointer" role="button" data-toggle="collapse" data-parent="#change-cabinet" href="#object-<?= $id; ?>" aria-expanded="true" aria-controls="object-<?= $id; ?>">
			<i class="fa fa-comment-o"></i> Изменение описания объекта <?php echo $row["name"]; ?>
		</div>
		<div id="object-<?= $id; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
			<div class="list-group">
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Описание для сайта</label>
						<div class="col-sm-9">
							<?php $description = trim($row["description"]); echo $description[0] == '\'' ? substr($description, 1, -1) : $description; ?>
						</div>
					</div>
				</div>
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label">Описание от объекта</label>
						<div class="col-sm-9">
							<?php echo $row["description_check"]; ?>
						</div>
					</div>
				</div>
			<?php foreach($array_services as $service){
				$icon = "";
				if($service["icon"])
					$icon = "<i class='fa ".$service["icon"]."'></i>";
				$id_s = $service["id"];
			?>
				<div class="list-group-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label"><?php echo $icon." ".$service["name"]; ?></label>
						<div class="col-sm-9">
							<?php echo $services[$id_s]; ?>
						</div>
					</div>
				</div>
			<?php } ?>
			</div>
			<div class="panel-footer text-right">
				<button type="button" class="btn btn-default btn-sm" onclick="history_object_account(<?php echo $id_account; ?>)"><i class="fa fa-history"></i> История</button>
				<button type="button" class="btn btn-default btn-sm" onclick="edit_description_object_account(<?php echo $id; ?>)"><i class="fa fa-pencil"></i> Изменить</button>
				<button type="button" class="btn btn-success btn-sm" onclick="confirm_description_object_account(<?php echo $id; ?>)"><i class="fa fa-check-circle"></i> Принять изменения</button>
				<!--<button type="button" class="btn btn-success btn-sm" onclick="upload_object_price_on_server(<?php echo $id; ?>)" title="Загрузить на сайт цены номеров объекта по датам"><i class="fa fa-check-circle"></i> Загрузить цены на сайты</button>-->
			</div>
		</div>
	</div>
<?php
		}
	}
?>
	</div>
<?php
}

function edit_description_object_account($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id, name, description, description_check, id_services FROM object WHERE id=?i", $id);
	$services = json_decode($row["id_services"], TRUE);
	$array_services = $connect->getAll("SELECT id, name, icon FROM services");
?>
<div class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Редактировать описание объекта <?php echo $row["name"]; ?></h4>
			</div>
			<div class="modal-body form-horizontal edit-object">
				<div class="form-group">
					<label class="col-sm-3 control-label">Описание на сайте</label>
					<div class="col-sm-9">
						<textarea class="form-control head-description" id="head-description"><?php $description = trim($row['description']); echo $description[0] == '\'' ? substr($description, 1, -1) : $description; ?></textarea>
					</div>
					<script>
						/*$(function() {
                          ClassicEditor.create( document.querySelector( '#head-description' ), {
                            toolbar: [ 'headings', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ],
                            heading: {
                              options: [
                                { modelElement: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                { modelElement: 'heading1', viewElement: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                                { modelElement: 'heading2', viewElement: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                              ]
                            }
                          }).then( editor => {
                            console.log( 'Editor was initialized', editor );
                            object_edit_description_editor = editor;
                          }).catch(
                              error => {
                            console.log(error);
                          });
						});*/
					</script>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Описание от объекта</label>
					<div class="col-sm-9">
						<div class="well well-sm"><?php echo $row["description_check"]; ?></div>
					</div>
				</div>
			<?php foreach($array_services as $service){
				$icon = "";
				if($service["icon"])
					$icon = "<i class='fa ".$service["icon"]."'></i>";
				$id_s = $service["id"];
			?>
				<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo $icon." ".$service["name"]; ?></label>
					<div class="col-sm-9">
						<input type="text" class="form-control" name="<?php echo $id_s; ?>" value="<?php echo $services[$id_s]; ?>" />
					</div>
				</div>
			<?php } ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_description_object_account('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_description_object_account($connect){
	$id = $_POST["id"];
	$desc = $_POST["desc"];
	$service = $_POST["service"];
	$connect->query("UPDATE object SET description=?s, id_services=?s, synchronized=0 WHERE id=?i", $desc, $service, $id);
}

function confirm_description_object_account($connect){
	$id = $_POST["id"];
	//$desc = $connect->getOne("SELECT description_check FROM object WHERE id=?i", $id);
	//$connect->query("UPDATE object SET status=1, description=?s, synchronized=0 WHERE id=?i", $desc, $id);
	$connect->query("UPDATE object SET status=1, synchronized=0 WHERE id=?i", $id);
}

function history_object_account(){
	$id = $_POST["id"];
	$account = new ObjectAccount;
	$data = $account->history_object_account($id);
	return json_encode($data);
}

function see_groups($connect){
    global $id_rights;
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-users"></i> Группы</div>
	<div class="list-group">

<?php
	$data = $connect->getAll("SELECT id, name FROM groups");
	foreach($data as $row){
		$id = $row["id"];
	?>
		<div class="list-group-item list-hover-item">
			<div class="form-group form-group-margin">
				<div class="col-sm-9"><?php echo $row["name"]; ?></div>
				<div class="col-sm-3">
                  <?php if($id_rights > 5) { ?>
					<button type="button" class="btn btn-default btn-xs" onclick="edit_group('<?php echo $id; ?>')">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
                  <?php } ?>
				</div>
			</div>
		</div>
	<?php
	}
?>
	</div>
  <?php if($id_rights > 5) { ?>
	<div class="panel-footer right">
		<button type="button" class="btn btn-default btn-sm" onclick="add_new_group()"><i class="fa fa-plus-circle"></i> Новая группа</button>
	</div>
  <?php } ?>
</div>
<?php
}

function add_new_group(){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новая группа</h4>
			</div>
			<div class="modal-body form-horizontal new-group">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label">Название</label>
					<div class="col-sm-9">
						<input type="text" class="form-control name-group" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_new_group()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_group($connect){
	$name = $_POST["name"];
	$connect->query("INSERT INTO groups(name) VALUES (?s)", $name);
}

function edit_group($connect){
	$id = $_POST["id"];
	$name = $connect->getOne("SELECT name FROM groups WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить группу</h4>
			</div>
			<div class="modal-body form-horizontal edit-group">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label">Название</label>
					<div class="col-sm-9">
						<input type="text" class="form-control name-group" value="<?php echo $name; ?>" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_group('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_group($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$connect->query("UPDATE groups SET name=?s WHERE id=?i", $name, $id);
}

function show_request_object($connect){
	$data = $connect->getAll("SELECT id, time, object, telephone, email, address, website, website_object, comment, source, status FROM object_request WHERE status>=0 ORDER BY status ASC, time DESC");
	return json_encode($data);
}

function show_card_request_object($connect){
	$object = $_POST["object"];
	$row = $connect->getRow("SELECT * FROM object_request WHERE id=?i", $object);
?>
	<div class="form-horizontal panel panel-info">
		<div class="panel-heading">
			<i class="fa fa-plus-circle"></i> Заявка на добавление нового объекта
		</div>
		<div class="list-group">
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Дата заявки</label>
					<div class="col-sm-9">
						<?php echo $row["time"]; ?>
					</div>
				</div>
			</div>			
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Юридическое название компании</label>
					<div class="col-sm-9">
						<?php echo $row["urobject"]; ?>
					</div>
				</div>
			</div>			
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Название объекта</label>
					<div class="col-sm-9">
						<?php echo $row["object"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Почтовый адрес</label>
					<div class="col-sm-9">
						<?php echo $row["address"]; ?>
					</div>
				</div>
			</div>	
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Юридический адрес</label>
					<div class="col-sm-9">
						<?php echo $row["uraddress"]; ?>
					</div>
				</div>
			</div>	
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">ИНН</label>
					<div class="col-sm-9">
						<?php echo $row["inn"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">КПП</label>
					<div class="col-sm-9">
						<?php echo $row["kpp"]; ?>
					</div>
				</div>
			</div>	
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">ФИО сотрудника для контактов</label>
					<div class="col-sm-9">
						<?php echo $row["fio"]; ?>
					</div>
				</div>
			</div>	
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Телефон</label>
					<div class="col-sm-9">
						<?php echo $row["telephone"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Email</label>
					<div class="col-sm-9">
						<?php echo $row["email"]; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer text-right">
			<?php if ($row['status']==0) {?>
			<button class="btn btn-danger btn-sm" onclick="delete_request_object(<?php echo $object; ?>)"><i class="fa fa-check-circle"></i> Удалить</button> &nbsp 
			<button class="btn btn-info btn-sm" onclick="edit_request_object(<?php echo $object; ?>)"><i class="fa fa-check-circle"></i> Редактировать</button> &nbsp 			
			<button class="btn btn-success btn-sm" onclick="confirm_request_object(<?php echo $object; ?>)"><i class="fa fa-check-circle"></i> Принять</button>
			<?php } ?>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function delete_request_object($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE object_request SET status=-1 WHERE id=?i", $id);
}


function edit_request_object($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM `object_request` WHERE id=?i", $id);
?>
<form class="edit_request_object_form">
<div class="modal fade edit-procedure-modal edit-object">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить заявку</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-procedure">
					<div class="form-group">
						<label class="col-sm-4 control-label">Юридическое название компании</label>
						<div class="col-sm-8">
							<input type="text" class="form-control urobject" name="urobject" value="<?php echo $row['urobject']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Название объекта полное</label>
						<div class="col-sm-8">
							<input type="text" class="form-control object" name="object" value="<?php echo $row['object']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Название объекта кратко</label>
						<div class="col-sm-8">
							<input type="text" class="form-control name" name="name" value="<?php echo $row['namme']; ?>">
						</div>
					</div>					
					<div class="form-group">
						<label class="col-sm-4 control-label">Тип объекта</label>
						<div class="col-sm-8">
							<?php echo get_select_table($connect, "type_object", "", $row["type"], "type", 1, ""); ?>
						</div>
					</div>	
					
					<div class="form-group">
						<label class="col-sm-4 control-label">Направление</label>
						<div class="col-sm-8">
							<?=get_select_table($connect, "direction_object", "(`id_reg` IS NULL OR `id_reg` = 0) AND `id_country` = 1", $row["direction"], "direction_object", 1, "");?>
						</div>
					</div>
					<div class="form-group<?php if(!$row['direction']) { ?> hidden<?php } ?>">
						<label class="col-sm-4 control-label">Регион</label>
						<div class="col-sm-8">
							<select class="form-control object_region" id="object_region" name="object_region">
								<option value="0"<?php if(!$row['id_reg']) { ?> selected<?php } ?>>Не выбран</option>
								<?php foreach ($regions as $region) { ?>
								<option value="<?=$region['id'];?>"<?php if($row['id_reg'] == $region['id']) { ?> selected<?php } ?>><?=$region['name'];?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group<?php if(!$row['id_reg'] || count($region_directions) === 0) { ?> hidden<?php } ?>">
						<label class="col-sm-4 control-label">Региональное направление</label>
						<div class="col-sm-8">
							<select class="form-control region_direction_id" id="region_direction_id" name="region_direction_id">
								<option value="0"<?php if(!$row['region_direction_id']) { ?> selected<?php } ?>>Не выбрано</option>
							<?php foreach ($region_directions as $region_direction) { ?>
								<option value="<?=$region_direction['id'];?>"<?php if($row['region_direction_id'] == $region_direction['id']) { ?> selected<?php } ?>><?=$region_direction['name'];?></option>
							<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Широта</label>
						<div class="col-sm-8">
							<input type="text" class="form-control latitude" name="latitude" value="<?php echo $row['latitude']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Долгота</label>
						<div class="col-sm-8">
							<input type="text" class="form-control longitude" name="longitude" value="<?php echo $row['longitude']; ?>">
						</div>
					</div>																			
					<div class="form-group">
						<label class="col-sm-4 control-label">Юридический адрес</label>
						<div class="col-sm-8">
							<input type="text" class="form-control uraddress" name="uraddress" value="<?php echo $row['uraddress']; ?>">
						</div>
					</div>	
					<div class="form-group">
						<label class="col-sm-4 control-label">ИНН</label>
						<div class="col-sm-8">
							<input type="text" class="form-control inn" name="inn" value="<?php echo $row['inn']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">КПП</label>
						<div class="col-sm-8">
							<input type="text" class="form-control kpp" name="kpp" value="<?php echo $row['kpp']; ?>">
						</div>
					</div>																			
					<div class="form-group">
						<label class="col-sm-4 control-label">ФИО сотрудника для контактов</label>
						<div class="col-sm-8">
							<input type="text" class="form-control fio" name="fio" value="<?php echo $row['fio']; ?>">
						</div>
					</div>					
					<div class="form-group">
						<label class="col-sm-4 control-label">Телефон</label>
						<div class="col-sm-8">
							<input type="text" class="form-control telephone" name="telephone" value="<?php echo $row['telephone']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">E-mail</label>
						<div class="col-sm-8">
							<input type="text" class="form-control email" name="email" value="<?php echo $row['email']; ?>">
						</div>
					</div>											
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-update-procedure" onclick="update_request_object('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
			
		</div>
	</div>
</div>
</form>
<?php
}

function confirm_request_object($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE object_request SET status=1 WHERE id=?i", $id);
}

function show_assign_email_object_account($connect){
	$result = array();
	$account = $_POST["account"];
	$data = $connect->getAll("SELECT id, email FROM object WHERE id_account=?i", $account);
	foreach($data as $row){
		$array = json_decode($row["email"], TRUE);
		$object = get_object($connect, $row["id"]);
		foreach($array as $value){
			$count = count($result);
			$result[$count] = array();
			$result[$count]["email"] = $value["value"];
			$result[$count]["note"] = $value["note"];
			$result[$count]["object"] = $object;
		}
	}
	return json_encode($result);
}

function assign_email_object_account($connect){
	$result = 0;
	$account = $_POST["account"];
	$email = $_POST["email"];
	if($account AND $email){
		$connect->query("UPDATE object_account SET email=?s WHERE id=?i", $email, $account);
		$result = 1;
	}
	return json_encode($result);
}

?>
