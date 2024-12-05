<?php

function select_add_one_day_object($connect){
	$id = $_POST["id"];
	$type = $connect->getOne("SELECT add_one_day FROM object WHERE id=?i", $id);
?>
	<?php if($type == 1){ ?>
		<label>
			<input type="radio" value="1" onclick="view_date_out()" name="days" checked /> сутками
		</label>
	<?php	}elseif($type == 0){ ?>
		<label>
			<input type="radio" value="0" onclick="view_date_out()" name="days" checked /> днями</label>
	<?php }else{ ?>
		<label>
			<input type="radio" value="1" onclick="view_date_out()" name="days" checked /> сутками
		</label>
		<label>
			<input type="radio" value="0" onclick="view_date_out()" name="days" /> днями
		</label>
	<?php } ?>
<?php
}

function select_reward_object($connect){
	$id = $_POST["id"];
	$date = $_POST["date"];
	return get_reward_object($connect, $id, $date);
}

function save_all($connect){
	global $name_user, $session_login;
	$today = date("Y-m-d");

	$surmane = $_POST["surmane"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];
	if(empty($otch)) $otch = "";
	$sex = null;
	if(isset($_POST['sex'])) {
	    $sex = (int)$_POST['sex'];
	    if($sex !== 0 && $sex !== 1) {
	        $sex = null;
        }
    }

	$email = $_POST["email"];
	if(empty($email)) $email = NULL;
	$passport = $_POST["passport"];
	if(empty($passport)) $passport = '';
	$passport = str_replace(" ", "", $passport);
	$passport = (int)$passport;
	if($passport == 0)
		$passport = NULL;
	$output = $_POST["output"];

	$date_pas = $_POST["date_pas"];
	if(empty($date_pas)) $date_pas = NULL;

	$date = $_POST["date"];
	if(empty($date)) $date = NULL;

	$address = $_POST["address"];
	if(empty($address)) $address = '';
	$telephone = $_POST["telephone"];

	if(empty($telephone))
	    $telephone = NULL;

	$id_obj = $_POST["id_obj"];
	$id_tour = $_POST["id_tour"];
	if(empty($id_tour)) $id_tour = NULL;
	$id_room = $_POST["id_room"];
	$sum = $_POST["sum"];
	$number = $_POST["number"];
	$discount = $_POST["discount"];
	if(empty($discount))
	    $discount = NULL;
	$commis = $_POST["commis"];
	$number_turist = $_POST["number_turist"];
	$note = $_POST["note"];
	if(empty($note)) $note = '';
	$days = $_POST["days"];
	$date_z = $_POST["date_z"];
	$type_price = $_POST["type"];
	$add_one_day = $_POST["add_one_day"];

    if(is_numeric($sum) AND is_numeric($number)){

          $original_data = [
            'surname' => $surmane,
            'name' => $name,
            'otch' => $otch,
            'telephone' => $telephone,
            'date' => $date,
            'address' => $address,
            'email' => $email,
            'passport' => $passport,
            'output' => $output,
            'date_pas' => $date_pas,
            'note' => $note,
            'date_reg' => $today,
            'sex' => $sex
          ];
        if(is_null($sex))
            $connect->query("INSERT INTO klient(`surname`, `name`, otch, telephone, `date`, address, email, passport, `output`, date_pas, note, date_reg, original_data) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $surmane, $name, $otch, $telephone, $date, $address, $email, $passport, $output, $date_pas, $note, $today, json_encode($original_data));
        else
            $connect->query("INSERT INTO klient(`surname`, `name`, otch, sex, telephone, `date`, address, email, passport, `output`, date_pas, note, date_reg, original_data) VALUES (?s, ?s, ?s, ?i, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)", $surmane, $name, $otch, $sex, $telephone, $date, $address, $email, $passport, $output, $date_pas, $note, $today, json_encode($original_data));

        $client = $connect->insertId();
		$connect->query("INSERT INTO reckoning(`date`, `turist`, `manager`, `id_user`, `id_obj`, `id_tour`, number_turist, rest, id_dis) VALUES (?s, ?i, ?s, ?i, ?i, ?s, ?i, ?i, ?s)", $today, $client, $name_user, $session_login, $id_obj, $id_tour, $number_turist, $client, $discount);
		$bid = $connect->insertId();
		setCookie("reck", $bid);
		$connect->query("INSERT INTO position_reck(id_room, `sum`, `number`, schet, note, `type`, days, date_z, add_one_day, reward) VALUES (?i, ?s, ?i, ?i, ?s, ?i, ?i, ?s, ?s, ?s)", $id_room, $sum, $number, $bid, $note, $type_price, $days, $date_z, $add_one_day, $commis);
		recalculation_sum($connect, $bid);
		save_schet_to_history($connect, $bid);
		change_arrival_date($connect, $bid);

        $connect->query("UPDATE booking SET update_bid = 1, `updated`=NOW(), `confirm`=0, `data`='' WHERE bid = ?i", $bid);

        return $client;
	}
	return FALSE;
}

function new_reckoning($connect){
	$type = $_POST["type"];
	global $name_user;
	ob_start();
?>
<div class="form-horizontal">
	<div class="form-group form-group-margin">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading"><i class="fa fa-file-text-o"></i> Новая заявка</div>
				<div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Тип заявки</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="reck_type" onblur="reckoning_type_checker();">
                                <option value="0">Стандартная</option>
                                <option value="1">Для сертификата</option>
                            </select>
                        </div>
                    </div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Заезд</label>
						<div class="col-sm-5" style="padding-right: 0px;">
							<input type="text" class="form-control datepicker" id="arrival" onBlur="verification_input_data('date_z', '1')">
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control" placeholder="Дней" id="days" onKeyPress="validate_input()" onBlur="verification_input_data('days', '1'); view_date_out();">
						</div>
						<div class="col-sm-2 mark-date_z mark-days"></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Считаем дни</label>
						<div class="col-sm-7">
							<div class="well well-sm" id="add_one_day">&nbsp;</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Выезд</label>
						<div class="col-sm-7">
							<div class="well well-sm" id="view_date_v">&nbsp;</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Объект</label>
						<div class="col-sm-7" id="object_name" name="new-reck">
							<input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" onBlur="verification_input_data('object', '1');">
						</div>
						<div class="col-sm-2 mark-object"></div>
					</div>
					<div class="form-group tour-operator-html" style="display: none;">
						<label class="col-sm-3 control-label">Туроператор</label>
						<div class="col-sm-7 html">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Отдыхающих</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" value="1" id="number_turist" onKeyPress="validate_input()">
						</div>
						<div class="col-sm-2 mark-number_turist"></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Менеджер</label>
						<div class="col-sm-7">
							<div class="well well-sm" id="manager"><?php echo $name_user; ?></div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Вознаграждение</label>
						<div class="col-sm-7">
							<?php echo get_reward_select(); ?>
						</div>
					</div>
					<?php if($type == "agency"){
					?>
					<div class="form-group">
						<label class="col-sm-3 control-label">Комиссия</label>
						<div class="col-sm-7">
							<?php echo get_select_commis($connect); ?>
						</div>
					</div>
					<?php }else{ ?>
					<div class="form-group">
						<label class="col-sm-3 control-label">Скидка (%)</label>
						<div class="col-sm-7">
							<?php echo get_select_discount($connect); ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="panel panel-default new-reckoning">
				<div class="panel-body">
					<div class="form-group">
						<label class="col-sm-3 control-label">Номер</label>
						<div class="col-sm-7" id="klient_room">
							<div class="well well-sm">&nbsp;</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Цена</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="sum" onKeyPress="validate_sum()" onBlur="verification_input_data('sum', '1');">
						</div>
						<div class="col-sm-3">
							<select class="form-control" id="type" onchange="change_label_number()">
								<option value="1">за чел/сутки</option>
								<option value="2">за номер (дом)</option>
								<option value="3">за заезд</option>
							</select>
						</div>
						<div class="col-sm-2 mark-sum"></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" id="label_number">Кол-во<br /><strong>отдыхающих</strong></label>
						<div class="col-sm-7">
							<input type="text" class="form-control" id="number" value="1" onKeyPress="validate_input()" onBlur="verification_input_data('number', '1');">
						</div>
						<div class="col-sm-2 mark-number"></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" id="label_number">Примечание</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" id="note">
						</div>
						<div class="col-sm-2 mark-note"></div>
					</div>
				</div>
				<div class="panel-footer">
					<div class="form-group form-group-margin">
						<div class="col-sm-12" style="text-align: right;">
							<button type="button" class="btn btn-success btn-sm" onClick="save_new_schet()"><i class="fa fa-check-circle"></i> Сохранить</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_schet($connect){
	global $session_login;

	$type_schet = $_POST['type_schet'];
    $today = date("Y-m-d");
    $client = $_POST["id_klient"];
    $note = $_POST["note"];

    if(empty($note)) $note = '';

    if($type_schet == 0) {
      $id_obj = $_POST["id_obj"];
      $id_tour = $_POST["id_tour"];
      if(empty($id_tour)) $id_tour = NULL;
      $id_room = $_POST["id_room"];
      $sum = $_POST["sum"];
      $number = $_POST["number"];
      $number_turist = $_POST["number_turist"];
      $days = $_POST["days"];
      $date_z = $_POST["date_z"];
      $type = $_POST["type"];
      if(empty($type)) $type = 0;
      $type_price = $_POST["type_price"];
      $id_com = $_POST["id_com"];
      if(empty($id_com)) $id_com = NULL;
      $discount = $_POST["id_dis"];
      if(empty($discount)) $discount = NULL;
      $commis = $_POST["commis"];
      $add_one_day = $_POST["add_one_day"];
      if(empty($add_one_day))
          $add_one_day = 1;


      $a = 2;
      if (is_numeric($sum) AND (is_numeric($number))) {
        if ($type === "agency") {
          $connect->query("INSERT INTO reckoning(id_com, date, agency, id_user, date_z, id_obj, id_tour, number_turist) VALUES (?i, ?s, ?i, ?i, ?s, ?i, ?s, ?i)", $id_com, $today, $client, $session_login, $date_z, $id_obj, $id_tour, $number_turist);
        }
        else {

          //$payer = $connect->query("SELECT payer FROM  WHERE turist=?i AND payer!='' ORDER BY id DESC", $client);
          $payer = NULL;
          $connect->query("INSERT INTO reckoning(date, turist, id_user, date_z, payer, id_obj, id_tour, number_turist, id_dis, rest) VALUES (?s, ?i, ?i, ?s, ?s, ?i, ?s, ?i, ?s, ?i)", $today, $client, $session_login, $date_z, $payer, $id_obj, $id_tour, $number_turist, $discount, $client);
        }
        $id = $connect->insertId();
        setCookie("reck", $id);
        $connect->query("INSERT INTO position_reck(id_room, sum, note, schet, number, type, days, date_z, add_one_day, reward) VALUES (?i, ?s, ?s, ?i, ?i, ?i, ?i, ?s, ?s, ?s)", $id_room, $sum, $note, $id, $number, $type_price, $days, $date_z, $add_one_day, $commis);
        recalculation_sum($connect, $id);
        save_schet_to_history($connect, $id);
        change_arrival_date($connect, $id);
        return $id;
      }
      return FALSE;
    }
    else {
      $payer = NULL;
      $connect->query("INSERT INTO reckoning(date, turist, id_user, payer, rest, type) VALUES (?s, ?i, ?i, ?s, ?i, ?i)", $today, $client, $session_login, $payer, $client,1);
      $id = $connect->insertId();
      setCookie("reck", $id);
      save_schet_to_history($connect, $id);
      recalculation_sum($connect, $id);
      return $id;
    }
    return FALSE;
}

function edit_schet($connect){
	$id = $_POST["id"];
	$data = $connect->getRow("SELECT type, date, number_turist, id_obj, agency, id_com, id_dis, note, status_san, date_schet_san, schet_san, state_program, exclude_bank_commission, bank_com_auto_excluded, children_rest, is_test, far_east, afl FROM reckoning WHERE id=?i", $id);
	$arr = array();
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog modal-edit-reckoning">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Редактирование заявки №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-reck">
                    <input type="hidden" value="<?=$data['type'];?>" id="reck_type">
                    <?php if($data['type'] == 0) { ?>
                    <div class="form-group">
                        <label class="col-sm-4 control-label"><span class="span_link" onclick="$('.change-object').toggle(); $('.tour-operator-html').hide();">Изменить объект</span></label>
                    </div>
                    <div class="form-group change-object" style="display: none;">
                        <label class="col-sm-4 control-label">Объект</label>
                        <div class="col-sm-8" id="object_name" name="edit-reck">
                            <input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')">
                        </div>
                    </div>
                    <div class="form-group tour-operator-html" style="display: none;">
                        <label class="col-sm-4 control-label">Туроператор</label>
                        <div class="col-sm-8 html">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Кол-во туристов</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="number_turist" value="<?php echo $data['number_turist']; ?>" onkeypress="validate_input()" maxlength="4">
                        </div>
                    </div>
                  <?php
                  if($data["agency"]){
                    ?>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Комиссия</label>
                          <div class="col-sm-8">
                            <?php echo get_select_commis($connect, $data["id_com"]); ?>
                          </div>
                      </div>
                    <?php
                  }//else{
                    ?>
                      <div class="form-group">
                          <label class="col-sm-4 control-label">Скидка</label>
                          <div class="col-sm-8">
                            <?php echo get_select_discount($connect, $data["id_dis"]); ?>
                          </div>
                      </div>
                    <?php
                  //}
                  ?>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">№ счета санатория</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="schet_san" value="<?php echo $data['schet_san']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Гос. субсидии</label>
                        <div class="col-sm-8">
                            <input type="checkbox" class="form-control" id="state_program"<?php if($data['state_program']) { ?> checked<?php } ?>>
                        </div>
                    </div>
                    <div class="form-group<?php if(!$data['state_program']) { ?> hidden<?php } ?>">
                        <label class="col-sm-4 control-label">Детский отдых</label>
                        <div class="col-sm-8">
                            <input type="checkbox" class="form-control" id="children_rest"<?php if($data['children_rest']) { ?> checked<?php } ?>>
                        </div>
                    </div>
                        <div class="form-group<?php if(!$data['state_program']) { ?> hidden<?php } ?>">
                            <label class="col-sm-4 control-label">Дальний Восток</label>
                            <div class="col-sm-8">
                                <input type="checkbox" class="form-control" id="far_east"<?php if($data['far_east']) { ?> checked<?php } ?>>
                            </div>
                        </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Убрать комиссию при оплате</label>
                        <div class="col-sm-8">
						<input type="checkbox" <?php if ($data['bank_com_auto_excluded']==1) echo 'disabled="disabled"';?> class="form-control" id="exclude_bank_commission"<?php if($data['exclude_bank_commission']) { ?> checked<?php } ?>>
						<?php if ($data['bank_com_auto_excluded']==1) echo '<em style="font-size: 10px;">комиссия была убрана автоматически, т.к. это была первая заявка туриста</em>'?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Тестовая заявка</label>
                        <div class="col-sm-8">
                            <input type="checkbox" class="form-control" id="is_test"<?php if($data['is_test']) { ?> checked<?php } ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Дата счета санатория</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control datepicker" id="date_schet_san" value="<?php echo $data['date_schet_san']; ?>">
                        </div>
                    </div>
                    <?php } ?>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label" id="label_number">Примечание</label>
						<div class="col-sm-8">
							<textarea class="form-control" id="note_schet"><?php echo $data['note']; ?></textarea>
						</div>
					</div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Номер участика мили Аэрофлот</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="afl" value="<?php echo $data['afl']; ?>">
                        </div>
                    </div>					
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="update_schet('<?php echo $id; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_schet($connect){
	$note = "";
    $note_schet = str_replace("plus", "+", $_POST["note"]);
    if(empty($note_schet))
        $note_schet = '';
    $reck_type = $_POST["reck_type"];
    $id = $_POST["id"];

    if($reck_type == 0) {
      $reward = "";
      $id_dis = "";
      $check = $_POST["check"];
      $id_com = $_POST["id_com"];
      $state_program = isset($_POST['state_program']) ? (int)$_POST['state_program'] : 0;
      $children_rest = isset($_POST['children_rest']) ? (int)$_POST['children_rest'] : 0;
      $far_east = isset($_POST['far_east']) ? (int)$_POST['far_east'] : 0;
	  $afl = isset($_POST['afl']) ? $_POST['afl'] : '';

      $is_test = isset($_POST['is_test']) ? (int)$_POST['is_test'] : 0;
      $exclude_bank_commission = isset($_POST['exclude_bank_commission']) ? $_POST['exclude_bank_commission'] : 0;

      $number_turist = $_POST["number_turist"];
      $id_obj = $_POST["id_obj"];
      if(empty($id_obj)) $id_obj = NULL;
      $id_tour = $_POST["id_tour"];
      if(empty($id_tour))
          $id_tour = NULL;

      $schet_san = $_POST["schet_san"];
      $date_schet_san = $_POST["date_schet_san"];
      if(empty($schet_san))
          $schet_san = NULL;
      if(empty($date_schet_san))
            $date_schet_san = NULL;

      if(isset($_POST["reward"]))
        $reward = $_POST["reward"];
      if(isset($_POST["id_dis"]))
        $id_dis = $_POST["id_dis"];

      if(empty($id_dis))
          $id_dis = NULL;

      if(!$state_program) {
          $children_rest = 0;
          $far_east = 0;
      }

      $row = $connect->getRow("SELECT turist, agency, status, status_san, number_turist, id_obj, id_com, id_dis, date_schet_san, schet_san, state_program, children_rest, exclude_bank_commission, is_test, far_east FROM reckoning WHERE id=?i", $id);

      if($row["number_turist"] != $number_turist)
        $note.= " Отдыхающих (старое - ".$row["number_turist"].");";
      if(($row["id_com"] != $id_com) AND ($id_com)){
        $value = $connect->getOne("SELECT value FROM commission WHERE id=?i", $row["id_com"]);
        $note.= " Комиссия (старое - ".$value."%);";
      }
      if(($row["date_schet_san"] != $date_schet_san AND $date_schet_san != "") OR ($row["schet_san"] != $schet_san))
        $note.= " Счет санатория (старый - ".$row["schet_san"]." от ".date_change($row["date_schet_san"]).");";

      if($row['state_program'] != $state_program) {
          $note .= $state_program ? ' Включены гос. субсидии' : ' Отключены гос. субсидии';
      }

        if($row['children_rest'] != $children_rest) {
            $note .= $children_rest ? ' Включен детский отдых' : ' Отключен детский отдых';
        }

        if($row['far_east'] != $far_east) {
            $note .= $far_east ? ' Включен Дальний Восток' : ' Отключен Дальний Восток';
        }

        if($row['is_test'] != $is_test) {
            $note .= $is_test ? ' Включен тестовый режим' : ' Отключен тестовый режим';
        }

        if($row['exclude_bank_commission'] != $exclude_bank_commission) {
          $note .= $exclude_bank_commission ? ' Убрана комиссия банка при оплате' : ' Включена комиссия банка при оплате';
      }

      if($note){
        $note = "Изменен. ".$note;
        save_schet_to_history($connect, $id, $note);
      }
      $obj = $row["id_obj"];
      //echo $id_obj.' '.$fsdfs';
      if($id_obj && $check == "1" AND ($obj != $id_obj)){
        $note = "Изменен объект. Старый - ".get_object($connect, $obj).";";
        changes_reckoning_cabinet($connect, $id, "object");
        save_schet_to_history($connect, $id, $note);
        $connect->query("UPDATE reckoning SET id_obj=?i, id_tour=?s, state_program = ?i, exclude_bank_commission = ?i, children_rest = ?i, is_test = ?i, far_east = ?i  WHERE id=?i", $id_obj, $id_tour, $state_program, $exclude_bank_commission, $children_rest, $is_test, $far_east, $id);
        $connect->query("DELETE FROM position_reck WHERE schet=?i", $id);
        if($connect->getOne("SELECT klient.login FROM reckoning, klient WHERE reckoning.id=?i AND reckoning.turist=klient.id", $id))
          send_mail_client_changes($connect, $id);
        elseif($connect->getOne("SELECT agency FROM reckoning WHERE id=?i", $id))
          send_mail_agency_changes($connect, $id);
      }elseif($check == "1")
        $connect->query("UPDATE reckoning SET id_tour=?s WHERE id=?i", $id_tour, $id);
      if(($row["id_com"] != $id_com) AND ($id_com != ""))
        $connect->query("UPDATE reckoning SET id_com=?i WHERE id=?i", $id_com, $id);
      $dis = $row["id_dis"];
      if($dis == 0)
        $dis = "";
      //if(($row["turist"]) AND ($id_dis != $dis)){
        if($dis){
          $row = $connect->getRow("SELECT value, type FROM discount WHERE id=?i", $dis);
          if($row["type"] == 1)
            $type_dis = "%";
          else
            $type_dis = " руб.";
          $discount = $row["value"].$type_dis;
        }else
          $discount = "Без скидки";
        $connect->query("UPDATE reckoning SET id_dis=?s WHERE id=?i", $id_dis, $id);
        save_schet_to_history($connect, $id, "Изменена скидка. Старый - ".$discount);
      //}
      $connect->query("UPDATE reckoning SET number_turist=?i, note=?s, schet_san=?s, date_schet_san=?s, state_program = ?i, exclude_bank_commission = ?i, children_rest = ?i, is_test = ?i, far_east = ?i, afl = ?s WHERE id=?i", $number_turist, $note_schet, $schet_san, $date_schet_san, $state_program, $exclude_bank_commission, $children_rest, $is_test, $far_east, $afl, $id);
	  echo $connect->last_query();
      recalculation_sum($connect, $id);
    }
    else {
      $connect->query("UPDATE reckoning SET note=?s WHERE id=?i", $note_schet, $id);
    }
}

function add_new_position($connect){
    global $id_rights;
	$id = $_POST["id"];
	$data = $connect->getRow("SELECT add_one_day, reward FROM position_reck WHERE schet=?i", $id);
	$reward = $data["reward"];
	$add_one_day = $data["add_one_day"];
	$data = $connect->getRow("SELECT type, id_obj, date_z FROM reckoning WHERE id=?i", $id);
	$reck_type = $data['type'];
	$id_obj = $data["id_obj"];
	$date_z = $data["date_z"];
	$type_add_one_day = $connect->getOne("SELECT add_one_day FROM object WHERE id=?i", $id_obj);
	$check = array();
	$check[$add_one_day] = "CHECKED";
	$check_add_one_day = "";
	if($type_add_one_day != 0)
		$check_add_one_day.= "<label><input type='radio' value='1' onclick='view_date_out()' name='days' ".$check[1]." /> сутками</label>";
	if($type_add_one_day != 1)
		$check_add_one_day.= " <label><input type='radio' value='0' onclick='view_date_out()' name='days' ".$check[0]." /> днями</label>";
	$select_type = "<option value='1'>за чел/сутки</option><option value='2'>за номер (дом)</option><option value='3'>за заезд</option>";


	$service_reckoning = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
	$service = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head DESC, type, id");
	$html_services_1 = "";
    $html_services_2 = "";

	foreach($service as $row){
		$checked = "";
		if(in_array($row["id"], $service_reckoning))
			$checked = " CHECKED ";
		if($row["type"] != 2)
			$html_services_1.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</labe><br />";
		else
			$html_services_2.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</label><br />";
	}

	ob_start();
?>
<div class="modal fade model-reconing">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новая позиция<?php if($reck_type == 1) { ?> (сертификат)<?php }?>. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-position">
					<div class="row">
						<div class="col-sm-<?php if($reck_type == 0) { ?>6<?php } else {?>12<?php }?>">
                            <?php if($reck_type == 0) { ?>
							<div class="form-group">
								<label class="col-sm-4 control-label">Номер</label>
								<div class="col-sm-8">
									<?php echo select_rooms($connect, $id_obj); ?>
								</div>
							</div>
                            <?php } ?>

							<div class="form-group">
								<label class="col-sm-4 control-label">Цена</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="sum" onKeyPress="validate_sum()">
                                    <input type="hidden" name="id_rights" value="<?=($id_rights?$id_rights:0);?>">
								</div>
							</div>

                            <?php if($reck_type == 0) { ?>
							<div class="form-group">
								<label class="col-sm-4 control-label">Вознаграждение</label>
								<div class="col-sm-8">
									<?php echo get_reward_select($reward, "check"); ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Заезд</label>
								<div class="col-sm-8">
									<input type="text" class="form-control datepicker" id="arrival" value="<?php echo $date_z; ?>" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Дней</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="days" onkeypress="validate_input()" onblur="view_date_out()" maxlength="3">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Выезд</label>
								<div class="col-sm-8">
									<div class="well well-sm" id="view_date_v">&nbsp;</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Считаем дни</label>
								<div class="col-sm-8">
									<div class="well well-sm" id="add_one_day"><?php echo $check_add_one_day; ?></div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Тип</label>
								<div class="col-sm-8">
									<select class="form-control" id="type" onchange="change_label_number()">
										<?php echo $select_type; ?>
									</select>
								</div>
							</div>
                            <?php } ?>
							<div class="form-group">
								<label class="col-sm-4 control-label" id="label_number">Кол-во</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="number">
								</div>
							</div>
							<div class="form-group form-group-margin">
								<label class="col-sm-4 control-label" id="label_number">Примечание</label>
								<div class="col-sm-8">
									<textarea class="form-control" id="note"></textarea>
                                    <input type="hidden" id="reck_type" value="<?=$reck_type;?>">
								</div>
							</div>
						</div>
                        <?php if($reck_type == 0) { ?>
						<div class="col-sm-6">
							<div class="form-group form-group-margin">
								<label class="col-sm-4 control-label">В стоимость входит</label>
								<div class="col-sm-8" style="margin-top: 20px;">
									<?php echo $html_services_1."<hr />".$html_services_2; ?>
								</div>
							</div>
						</div>
                        <?php } ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="save_new_position('<?php echo $id; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_position($connect){
	$id = $_POST["id"];
    $sum = $_POST["sum"];
    $number = $_POST["number"];
    $note = $_POST["note"];
    $reck_type = $_POST["reck_type"];
    $reward = 100.0;

    if($reck_type == 0) {
      $id_room = $_POST["id_room"];
      $reward = $_POST["reward"];
      $type = $_POST["type"];
      $days = $_POST["days"];
      $date_z = $_POST["date_z"];
      $add_one_day = $_POST["add_one_day"];
      if(empty($add_one_day))
          $add_one_day = 0;

      if($add_one_day == 0) {
          $add_one_day = 0;
      }

      $connect->query("INSERT INTO position_reck(id_room, sum, number, schet, note, type, days, date_z, add_one_day, reward) VALUES (?i, ?s, ?i, ?i, ?s, ?i, ?i, ?s, ?s, ?s)", $id_room, $sum, $number, $id, $note, $type, $days, $date_z, $add_one_day, $reward);
      $last = $connect->insertId();

      $services = $_POST["services"];

      if(count($services)) {
        $connect->query("UPDATE reckoning SET id_services=?s WHERE id=?i", implode("_", json_decode($services)), $id);
      }

      changes_reckoning_cabinet($connect, $id, "position", $last, "all");
      recalculation_sum($connect, $id);
      change_arrival_date($connect, $id);
      if($connect->getOne("SELECT klient.login FROM reckoning, klient WHERE reckoning.id=?i AND reckoning.turist=klient.id", $id))
        send_mail_client_changes($connect, $id);
      elseif($connect->getOne("SELECT agency FROM reckoning WHERE id=?i", $id))
        send_mail_agency_changes($connect, $id);
    }
    else {
      $connect->query("INSERT INTO position_reck(sum, number, schet, note, reward) VALUES (?i, ?i, ?i, ?s, ?s)", $sum, $number, $id, $note, $reward);
      $last = $connect->insertId();
      changes_reckoning_cabinet($connect, $id, "position", $last, "all");
      recalculation_sum($connect, $id);
      if($connect->getOne("SELECT klient.login FROM reckoning, klient WHERE reckoning.id=?i AND reckoning.turist=klient.id", $id))
        send_mail_client_changes($connect, $id);
    }
}

function edit_position($connect){
    global $id_rights;
	$id = $_POST["id"];
	$data = $connect->getRow("SELECT id_room, sum, number, note, type, days, date_z, add_one_day, reward, schet, id_service FROM position_reck WHERE id=?i", $id);
	$reck = $data["schet"];
	$id_room = $data["id_room"];
	$reward = $data["reward"];
    $obj_row = $connect->getRow("SELECT id_obj, type FROM reckoning WHERE id=?i", $reck);
	$id_obj = $obj_row['id_obj'];
	$type_add_one_day = $connect->getOne("SELECT add_one_day FROM object WHERE id=?i", $id_obj);
	$select_room = "";
	if($id_room == 0 AND $data["id_service"])
		$select_room = "<option value='0'>Штраф</option>";
	else
		$select_room = select_rooms($connect, $id_obj, $id_room);
	$check = array(0 => "", 1 => "");
	$select = array(1 => "", 2 => "", 3 => "");
	$add_one_day = "";
	$check[$data["add_one_day"]] = "CHECKED";
	if($type_add_one_day != 0)
		$add_one_day.= "<label><input type='radio' value='1' onclick='view_date_out()' name='days' ".$check[1]." /> сутками</label>";
	if($type_add_one_day != 1)
		$add_one_day.= " <label><input type='radio' value='0' onclick='view_date_out()' name='days' ".$check[0]." /> днями</label>";
	$select[$data["type"]] = " SELECTED ";
	$select_type = "<option value='1' ".$select[1].">за чел/сутки</option><option value='2' ".$select[2].">за номер (дом)</option><option value='3' ".$select[3].">за заезд</option>";


	$service_reckoning = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $reck));
	$service = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head DESC, type, id");

	$html_services_1 = "";
    $html_services_2 = "";

    foreach($service as $row){
		$checked = "";
		if(in_array($row["id"], $service_reckoning))
			$checked = " CHECKED ";
		if($row["type"] != 2)
			$html_services_1.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</labe><br />";
		else
			$html_services_2.= "<label class='control-label'><input type='checkbox' ".$checked." class='services' value='".$row["id"]."' />".$row["name"]."</label><br />";
	}


	ob_start();
?>
<div class="modal fade model-reconing">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Редактирование позиции. Заявка №<?php echo $reck; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-position">
					<div class="row">
						<div class="col-sm-<?php if($obj_row['type'] == 0) { ?>6<?php } else {?>12<?php } ?>">
                            <input type="hidden" value="<?=$obj_row['type'];?>" id="reck_type">
                            <?php if($obj_row['type'] == 0) { ?>
							<div class="form-group">
								<label class="col-sm-4 control-label">Номер</label>
								<div class="col-sm-8">
									<?php echo $select_room; ?>
								</div>
							</div>
                            <?php } ?>
							<div class="form-group">
								<label class="col-sm-4 control-label">Цена</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="sum" value="<?php echo $data['sum']; ?>" onKeyPress="validate_sum()">
                                    <input type="hidden" name="id_rights" value="<?=($id_rights?$id_rights:0);?>">
                                </div>
							</div>
                            <?php if($obj_row['type'] == 0) { ?>
							<div class="form-group">
								<label class="col-sm-4 control-label">Вознаграждение</label>
								<div class="col-sm-8">
									<?php echo get_reward_select($reward, "check"); ?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Заезд</label>
								<div class="col-sm-8">
									<input type="text" class="form-control datepicker" id="arrival" value="<?php echo $data['date_z']; ?>" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Дней</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="days" value="<?php echo $data['days']; ?>" onkeypress="validate_input()" onblur="view_date_out()" maxlength="3">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Выезд</label>
								<div class="col-sm-8">
									<div class="well well-sm" id="view_date_v">&nbsp;</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Считаем дни</label>
								<div class="col-sm-8">
									<div class="well well-sm" id="add_one_day"><?php echo $add_one_day; ?></div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">Тип</label>
								<div class="col-sm-8">
									<select class="form-control" id="type" onchange="change_label_number()">
										<?php echo $select_type; ?>
									</select>
								</div>
							</div>
                            <?php } ?>
							<div class="form-group">
								<label class="col-sm-4 control-label" id="label_number">Кол-во</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="number" value="<?php echo $data['number']; ?>">
								</div>
							</div>
							<div class="form-group form-group-margin">
								<label class="col-sm-4 control-label" id="label_number">Примечание</label>
								<div class="col-sm-8">
									<textarea class="form-control" id="note"><?php echo $data['note']; ?></textarea>
								</div>
							</div>
						</div>
                        <?php if($obj_row['type'] == 0) { ?>
						<div class="col-sm-6">
							<div class="form-group form-group-margin">
								<label class="col-sm-4 control-label">В стоимость входит</label>
								<div class="col-sm-8" style="margin-top: 20px;">
									<?php echo $html_services_1."<hr />".$html_services_2; ?>
								</div>
							</div>
						</div>
                        <?php } ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..."  onclick="update_position('<?php echo $id; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_position($connect){
	$array_type = array(1 => "за чел/сутки", 2 => "за номер", 3 => "за заезд");
	$id = $_POST["id"];
    $number= $_POST["number"];
    $sum = $_POST["sum"];
    $note = str_replace("plus", "+", $_POST["note"]);
    $reck_type = $_POST["reck_type"];

    if($reck_type == 0) {
      $id_room = (int)$_POST["id_room"];
      if(!$id_room)
          $id_room = NULL;
      $days = $_POST["days"];
      $date_z = $_POST["date_z"];

      $reward = $_POST["reward"];
      $type = $_POST["type"];
      $add_one_day = $_POST["add_one_day"];

      $note_history = "";
      $row = $connect->getRow("SELECT id_room, sum, number, note, type, days, reward, schet, date_z FROM position_reck WHERE id=?i", $id);
      $schet = $row["schet"];

      $services = $_POST["services"];

      if(count($services)) {
        $connect->query("UPDATE reckoning SET id_services=?s WHERE id=?i", implode("_", json_decode($services)), $schet);
      }

      $status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $schet);
      $change_cabinet = 0;
      $change_quota = 0;
      if($row["id_room"] != $id_room){
        $note_history.= " Номер: (старое - ".$connect->getOne("SELECT name FROM room WHERE id=?i", $row["id_room"]).");";
        changes_reckoning_cabinet($connect, $schet, "position", $id, "room");
        $change_cabinet = 1;
      }
      if($row["sum"] != $sum){
        $note_history.= " Цена: (старое - ".$row["sum"].");";
        changes_reckoning_cabinet($connect, $schet, "position", $id, "sum");
        $change_cabinet = 1;
      }
      if($row["days"] != $days){
        $note_history.= " Дней: (старое - ".$row["days"].");";
        changes_reckoning_cabinet($connect, $schet, "position", $id, "days");
        $change_cabinet = 1;
        $change_quota = 1;
      }
      if($row["number"] != $number){
        $note_history.= " Кол-во: (старое - ".$row["number"].");";
        changes_reckoning_cabinet($connect, $schet, "position", $id, "number");
        $change_cabinet = 1;
      }
      if($row["note"] != $note){
        $note_history.= " Прим.: (старое - ".$row["note"].");";
        changes_reckoning_cabinet($connect, $connect, $schet, "position", $id, "note");
        $change_cabinet = 1;
      }
      if($row["reward"] != $reward)
        $note_history.= " Вознаграждение: (старое - ".$row["reward"]."%);";
      if($row["type"] != $type){
        $note_history.= " Тип: (старое - ".$array_type[$row["type"]].");";
        changes_reckoning_cabinet($connect, $schet, "position", $id, "type");
        $change_cabinet = 1;
      }
      if($row["date_z"] != $date_z){
        changes_reckoning_cabinet($connect, $schet, "position", $id, "date_z");
        $change_cabinet = 1;
        $change_quota = 1;
      }
      if($note_history){
        $note_history = "Изменен. ".$note_history;
        save_schet_to_history($connect, $schet, $note_history);
      }
      $connect->query("UPDATE position_reck SET id_room=?s, sum=?s, number=?i, note=?s, type=?i, days=?i, date_z=?s, add_one_day=?s, reward=?s WHERE id=?i", $id_room, $sum, $number, $note, $type, $days, $date_z, $add_one_day, $reward, $id);
      if($change_cabinet == 1){
        if($connect->getOne("SELECT klient.login FROM reckoning, klient WHERE reckoning.id=?i AND reckoning.turist=klient.id", $schet))
          send_mail_client_changes($connect, $schet);
        elseif($connect->getOne("SELECT agency FROM reckoning WHERE id=?i", $schet))
          send_mail_agency_changes($connect, $schet);
      }
      if($change_quota == 1){
        check_status_booking_quota($connect, $schet, $id);
      }
      recalculation_sum($connect, $schet);
      change_arrival_date($connect, $schet);
      if($change_cabinet == 1 AND $status == 3)
        return "check";
    }
    else {
      $note_history = "";
      $row = $connect->getRow("SELECT id_room, sum, number, note, type, days, reward, schet, date_z FROM position_reck WHERE id=?i", $id);
      $schet = $row["schet"];
      $status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $schet);
      if($row["sum"] != $sum){
        $note_history.= " Цена: (старое - ".$row["sum"].");";
        changes_reckoning_cabinet($connect, $schet, "position", $id, "sum");
        $change_cabinet = 1;
      }
      if($row["number"] != $number){
        $note_history.= " Кол-во: (старое - ".$row["number"].");";
        changes_reckoning_cabinet($connect, $schet, "position", $id, "number");
        $change_cabinet = 1;
      }
      if($row["note"] != $note){
        $note_history.= " Прим.: (старое - ".$row["note"].");";
        changes_reckoning_cabinet($connect, $connect, $schet, "position", $id, "note");
        $change_cabinet = 1;
      }
      if($note_history){
        $note_history = "Изменен. ".$note_history;
        save_schet_to_history($connect, $schet, $note_history);
      }

      $connect->query("UPDATE position_reck SET sum=?s, number=?i, note=?s WHERE id=?i", $sum, $number, $note,$id);
      if($change_cabinet == 1){
        if($connect->getOne("SELECT klient.login FROM reckoning, klient WHERE reckoning.id=?i AND reckoning.turist=klient.id", $schet))
          send_mail_client_changes($connect, $schet);
      }
      recalculation_sum($connect, $schet);
      if($change_cabinet == 1 AND $status == 3)
        return "check";
    }
}

function show_form_reset_status_bid($connect){
	$position = $_POST["position"];
	$bid = $connect->getOne("SELECT schet FROM position_reck WHERE id=?i", $position);
	$status = $connect->getOne("SELECT status FROM reckoning WHERE id=?i", $bid);
	if($status == 3){
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Заявка изменена. Вернуть заявку в неподтвержденные?</h4>
			</div>
			<div class="modal-footer text-center">
				<button type="button" class="btn btn-success btn-sm" onclick="confirm_reset_status_bid('<?php echo $bid; ?>')"><i class="fa fa-check-circle"></i> Вернуть в неподтвержденные</button>
			</div>
		</div>
	</div>
</div>
<?php
	}
}

function confirm_reset_status_bid($connect){
	$id = $_POST["id"];
	$connect->query("UPDATE reckoning SET status=2 WHERE id=?i AND status=3", $id);
	save_schet_to_history($connect, $id, "Заявка возвращена в неподтвержденные");
}

function add_new_turist(){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новый отдыхающий. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-turist">
					<div class="form-group">
						<label class="col-sm-4 control-label">Фамилия</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="surname" onKeyUp="find_klient(event, 'surname', 'klient', 'select_turist_to_schet', '<?php echo $id; ?>')" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Имя</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name" onFocus="find_klient_blur()" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Отчество</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="otch" onFocus="find_klient_blur()" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата рождения</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date_b" onFocus="find_klient_blur()" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Паспорт</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="passport" onkeypress="passport_space()" onFocus="find_klient_blur()" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Свид. о рождении</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="birth_certificate"  onFocus="find_klient_blur()" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="save_new_klient('<?php echo $id; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_new_turist($connect){
	$id = $_POST["id"];
	$surname = $_POST["surname"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];
	if(empty($otch)) $otch = NULL;
	$date = $_POST["date"];
	if(empty($date)) $date = NULL;
	$passport = $_POST["passport"];
	if(empty($passport)) $passport = NULL;
	$sex = null;

    if(isset($_POST['sex'])) {
        $sex = (int)$_POST['sex'];
        if($sex !== 0 && $sex !== 1) {
          $sex = null;
        }
    }

	$birth_certificate = $_POST["birth_certificate"];
      $original_data = [
        'surname' => $surname,
        'name' => $name,
        'otch' => $otch,
        'passport' => $passport,
        'date' => $date,
        'birth_certificate' => $birth_certificate,
        'sex' => $sex
      ];

    if(is_null($sex))
        $connect->query("INSERT INTO klient(name, surname, otch, passport, date, birth_certificate, original_data) VALUES(?s, ?s, ?s, ?s, ?s, ?s, ?s)", $name, $surname, $otch, $passport, $date, $birth_certificate, json_encode($original_data));
	else
	    $connect->query("INSERT INTO klient(name, surname, otch, sex, passport, date, birth_certificate, original_data) VALUES(?s, ?s, ?s, ?i, ?s, ?s, ?s, ?s)", $name, $surname, $otch, $sex, $passport, $date, $birth_certificate, json_encode($original_data));

	$last_id = $connect->insertId();
	$rest = $connect->getOne("SELECT rest FROM reckoning WHERE id=?i", $id);
	if($rest == "")
		$rest = $last_id;
	else
		$rest = $rest.",".$last_id;
	$connect->query("UPDATE reckoning SET rest=?s WHERE id=?i", $rest, $id);
	changes_reckoning_cabinet($connect, $id, "turist", $last_id, "all");
}

function select_turist_to_schet($connect){
	$id = $_POST["id"];
	$schet = $_POST["schet"];
	$rest = $connect->getOne("SELECT rest FROM reckoning WHERE id=?i", $schet);
	$have = 0;
	if(!$rest)
		$rest = $id;
	else{
		$turists = explode(",", $rest);
		foreach($turists as $turist){
			if($turist == $id)
				$have = 1;
		}
		if($have == 0)
			$rest = $rest.",".$id;
	}
	if($have == 0){
		$rest = explode(",", $rest);
		$rest = array_diff($rest, array(''));
		$finish = implode(",", $rest);
		$connect->query("UPDATE reckoning SET rest=?s WHERE id=?i", $finish, $schet);
		changes_reckoning_cabinet($connect, $schet, "turist", $id, "all");
		return 1;
	}
}

function edit_klient_reck($connect){
	$id = $_POST["id"];
	$schet = $_POST["schet"];
	$data = $connect->getRow("SELECT surname, name, otch, passport, date, output, date_pas, birth_certificate FROM klient WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить отдыхающего</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-turist">
					<div class="form-group">
						<label class="col-sm-4 control-label">Фамилия</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="surname" value="<?php echo $data['surname']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Имя</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name" value="<?php echo $data['name']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Отчество</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="otch" value="<?php echo $data['otch']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата рождения</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date_b" value="<?php echo $data['date']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Паспорт</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="passport" onkeypress="passport_space()" value="<?php echo $data['passport']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Кем выдан</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="output" value="<?php echo $data['output']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата выдачи</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date_pass" value="<?php echo $data['date_pas']; ?>">
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Свид. о рождении</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="birth_certificate" value="<?php echo $data['birth_certificate']; ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="update_klient_schet('<?php echo $id; ?>', '<?php echo $schet; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_klient_schet($connect){
	$row = $connect->getRow("SELECT surname, name, otch, passport, output, birth_certificate FROM klient WHERE id=?i", $id);
	$id = $_POST["id"];
	$schet = $_POST["schet"];
	$surname = $_POST["surname"];
	$name = $_POST["name"];
	$otch = $_POST["otch"];
	$date = $_POST["date"];
	if(empty($date)) $date = NULL;
	$passport = $_POST["passport"];
	if(empty($passport)) $passport = NULL;
	$output = $_POST["output"];
	if(empty($output)) $output = NULL;
	$date_pass = $_POST["date_pass"];
	if(empty($date_pass)) $date_pass = NULL;

	$birth_certificate = $_POST["birth_certificate"];
	if(empty($birth_certificate)) $birth_certificate = NULL;
	$connect->query("UPDATE klient SET surname=?s, name=?s, otch=?s, date=?s, passport=?s, output=?s, date_pas=?s, birth_certificate=?s WHERE id=?i", $surname, $name, $otch, $date, $passport, $output, $date_pass, $birth_certificate, $id);
	$payer = $connect->getOne("SELECT id FROM payer WHERE id_turist=?i", $id);
	if($payer)
		$connect->query("UPDATE payer SET date_b=?s, passport=?s WHERE id=?i", $date, $passport." ".$output." ".date_change($date_pass, "."), $payer);
}

function remove_klient_schet($connect){
	$id = $_POST["id"];
	$turist = $_POST["turist"];
	$rest = str_replace($turist, "", $connect->getOne("SELECT rest FROM reckoning WHERE id=?i", $id));
	$rest = array_diff(explode(",", $rest), array(""));
	$finish = implode(",", $rest);
	$connect->query("UPDATE reckoning SET rest=?s WHERE id=?i", $finish, $id);
}

function view_bonus_client($connect){
	global $id_rights;
	$id = $_POST["id"];
	$array_type = get_status_array($connect, "type_bonus");
	$data = $connect->getAll("SELECT id, active, DATE_FORMAT(date, '%e.%m.%Y') as date, sum, schet, type, note FROM bonus WHERE turist=?i", $id);
	$table = "";
	foreach($data as $row){
		$id_bonus = $row["id"];
		$bonus = $row["sum"];
		$date = $row["date"];
		$schet = $row["schet"];
		$note = $row["note"];
		$type = $row["type"];
		$active = $row["active"];
		$bg_color = "";
		if($active == 0)
			$bg_color = "danger";
		if($schet AND $type == 1){
			$reck = $connect->getRow("SELECT sum, id_obj FROM reckoning WHERE id=?i", $schet);
			$note = get_object($connect, $reck["id_obj"]).", ".$reck["sum"];
		}
		if($bonus < 0)
			$style = "style='color: red'";
		else
			$style = "style='color: green'";
		$table.= "<tr class='bonus-turist-".$id_bonus." ".$bg_color."'>";
		$table.= "<td width='100'>".$date."</td>";
		$table.= "<td width='150'>".$array_type[$type]."</td>";
		$table.= "<td width='50'".$style.">".$bonus."</td>";
		$table.= "<td width='300'>".$note."</td>";
		$table.= "<td width='50'>";
		if($id_rights == 5){
			$table.= "<button class='btn btn-link btn-xs' onclick='check_status_bonus($id_bonus)'><i class='fa fa-check'></i></button>";
		}
		$table.= "</td>";
		$table.= "</tr>";
	}
	if($table)
		$html = "<table class='table table-condensed'><tr><th>Дата</th><th>Тип</th><th>Бонусы</th><th>Причина</th><th></th></tr>".$table."</table>";
	else
		$html = "Бонусов пока нет";
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-gift"></i> Бонусы туриста</div>
	<?php if(count($data)){ ?>
		<?php echo $html; ?>
	<?php }else{ ?>
		<div class="panel-body">
			Бонусов пока не добавлено
		</div>
	<?php } ?>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function check_status_bonus($connect){
	$id = $_POST["id"];
	$active = $connect->getOne("SELECT active FROM bonus WHERE id=?i", $id);
	$new = 1;
	if($active == 1){
		$new = 0;
	}
	$connect->query("UPDATE bonus SET active=?i WHERE id=?i", $new, $id);
	return $new;
}

function view_schet_client($connect){
	$id = $_POST["id"];
	$col = $_POST["type"];
	$st_agent = "";
	if($col != "agency")
		$col = "turist";
	$table = "";
	$html = "";
	$data = $connect->getAll("SELECT DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, id, sum, id_user, id_obj, rest, status, status_agent, active FROM reckoning WHERE $col=?i ORDER BY date", $id);
	foreach($data as $row){
		$active = $row["active"];
		$reck = $row["id"];
		$bgColor = "";
		if($active == 2)
			$bgColor = "style='background: #d9ffb3;'";
		elseif($active == 3)
			$bgColor = "style='background: #FFCFCF;'";
		$object = get_object($connect, $row["id_obj"]);
		$date = $row["date_z"];
		$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
		$sum = $row["sum"];
		$status = $row["status"];
		$status_agent = $row["status_agent"];
		$turists = explode(",", $row["rest"]);
		$turists = array_diff($turists, array(""));
		$rest = isset($turists[0])?$turists[0]:null;
		$turist = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $rest);
		$status = $connect->getOne("SELECT name FROM status WHERE id=?i", $status);
		$table.= "<tr onclick='view_schet(".$reck.")' class='tr_reck' ".$bgColor.">";
		$table.= "<td width='25'>".$reck."</td>";
		$table.=" <td width='75' class='td_left'><strong>".$date."</strong></td>";
		$table.= "<td width='150' class='td_left'> ".$object."</td>";
		$table.= "<td width='100' class='td_left'>".$turist."</td>";
		$table.= "<td width='80'>".$sum."</td>";
		$table.= "<td width='120'>".$manager."</td>";
		$table.= "<td width='120'>".$status."</td>";
		if($col == "agency"){
			$status_agent = $connect->getOne("SELECT name FROM status_agent WHERE id=?i", $status_agent);
			$table.= "<td width='100'>".$status_agent."</td>";
			$st_agent = "<th>Отчет агента</th>";
		}
		$table.= "</tr>";
	}
	$table2 = "";
	if($col != "agency"){
		$data = $connect->getAll("SELECT DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, id, sum, id_user, id_obj, rest, status, status_agent, turist FROM reckoning WHERE rest LIKE '%?i%' ORDER BY date", $id);
		foreach($data as $row){
			$reck = $row["id"];
			$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
			$sum = $row["sum"];
			$status = $row["status"];
			$status_agent = $row["status_agent"];
			$turists = explode(",", $row["rest"]);
			$turists = array_diff($turists, array(""));
			$check = 0;
			foreach($turists as $rest){
				if($rest == $id){
					$check = 1;
					break;
				}
			}
			if($check == 1 AND $row["turist"] != $id){
				$object = get_object($connect, $row["id_obj"]);
				$date = $row["date_z"];
				$turist = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $id);
				$status = $connect->getOne("SELECT name FROM status WHERE id=?i", $status);
				$table2.= "<tr onclick='view_schet(".$reck.")' class='tr_reck'>";
				$table2.= "<td width='25'>".$reck."</td>";
				$table2.=" <td width='75' class='td_left'><strong>".$date."</strong></td>";
				$table2.= "<td width='150' class='td_left'> ".$object."</td>";
				$table2.= "<td width='100' class='td_left'>".$turist."</td>";
				$table2.= "<td width='80'>".$sum."</td>";
				$table2.= "<td width='120'>".$manager."</td>";
				$table2.= "<td width='120'>".$status."</td>";
				$table2.= "</tr>";
			}
		}
	}
	if($table2)
		$table2 = "<tr><td colspan='7'><strong>Как отдыхающий</strong></td></tr>".$table2;
	$table.= $table2;
	if($table)
		$html.= "<table id='all_schet_klient' class='table table-hover table-condensed'><tr><th>№</th><th>Заезд</th><th>Объект</th><th>Отдыхающий</th><th>Сумма</th><th>Менеджер</th><th>Статус</th>".$st_agent."</tr>".$table."</table>";
	if(!$table AND $col != "agency"){
		$row = $connect->getRow("SELECT id, note, DATE_FORMAT(date, '%e.%m.%Y') as date FROM reminder WHERE turist=?i", $id);
		if($row["id"]){
			$date = $row["date"];
			$note = $row["note"];
			$style = "";
			if(strtotime($date) <= time())
				$style = "style='background: #FFD8D8'";
			$html.= "<div class='reminder_block'".$style." id='rem_".$id."'>";
			$html.= "<strong>Примечание:</strong> ".$note."<br />";
			$html.= "<strong>Дата уведомления:</strong> ".$date;
			$html.= "</div>";
		}
	}
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-files-o"></i> Заявки туриста</div>
	<?php if($html){ ?>
		<?php echo $html; ?>
	<?php }else{ ?>
		<div class="panel-body">
			Заявок пока не добавлено
		</div>
	<?php } ?>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function get_manager_change($connect){
	$id = $_POST["id"];
	global $id_rights, $session_login;
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить менеджера. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal change-manager">
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Менеджер</label>
						<div class="col-sm-8">
							<?php echo get_managers($connect,"","",$id_rights,$session_login,true); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="save_manager_to_reck('<?php echo $id; ?>')" class="btn btn-success"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function change_manager_reckoning($connect){
	$id = $_POST["id"];
	$manager = $_POST["manager"];
	$name = $connect->getOne("SELECT name FROM users WHERE id=?i", $manager);
	$connect->query("UPDATE reckoning SET id_user=?i, manager=?s WHERE id=?i", $manager, $name, $id);
	save_schet_to_history($connect, $id, "Изменил менеджера");
	save_notification($connect, "Вам присвоена заявка №".$id, $manager);
}

function add_service_reckoning($connect){
	$id = $_POST["id"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новая услуга. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Услуга</label>
						<div class="col-sm-8">
							<?php echo get_select_table($connect, "service_schet", "", "", "service"); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="save_service_reckoning('<?php echo $id; ?>')" class="btn btn-success btn-update"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_service_reckoning($connect){
	$id = $_POST["id"];
	$service = $_POST["service"];
	$date_z = $connect->getOne("SELECT date_z FROM reckoning WHERE id=?i", $id);
	$connect->query("INSERT INTO position_reck(id_room, id_service, schet, date_z) VALUES (0, ?i, ?i, ?s)", $service, $id, $date_z);
}

function show_schet_klient($connect){
	global $id_rights, $session_login;
	$html = "";
	$document = "";
	$id = $_POST["id"];
	$klient = $_POST["klient"];
	$type = $_POST["type"];
	if(isset($_COOKIE["reck"]))
		SetCookie("reck","");
	$row = $connect->getRow("SELECT type, agency, turist, holding_sum, DATE_FORMAT(date, '%d.%m.%Y') as date, sum, status, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v, id_obj, id_tour, rest, status_san, number_turist, id_com, id_dis, note, active, status_agent, schet_san, DATE_FORMAT(date_schet_san, '%d.%m.%Y') as date_schet_san, id_user, website, guaranteed, reason_delete, changes, doc_schet_san, note_bid, correction, commission_value, state_program, bnovo, afl FROM reckoning WHERE id=?i", $id);
	$active = $row["active"];
	$reck_type = $row["type"];
	if($type == "agency")
		$kl = $row["agency"];
	else
		$kl = $row["turist"];
	$check = 0;
	$arr_rest = explode(",", $row["rest"]);
	foreach($arr_rest as $t){
		if($t == $klient){
			$check = 1;
			break;
		}
	}
	if($kl != $klient AND $check != 1) {
	    //echo $kl;
      return FALSE;
    }
	$changes = json_decode($row["changes"], TRUE);
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$bnovo = $row['bnovo'];
	$afl = $row['afl'];
	$turist = $row["turist"];
	$agency = $row["agency"];
	$itog_sum = $row["sum"];
	$id_com = $row["id_com"];
	$id_dis = $row["id_dis"];
	$id_user = $row["id_user"];
	$correction = $row["correction"];
	$date = month_transform($row["date"]);
	$date_z = month_transform($row["date_z"]);
	$date_v = month_transform($row["date_v"]);
	$status = $row["status"];
	$id_obj = $row["id_obj"];
	$id_tour = $row["id_tour"];
	$note_schet = $row["note"];
	$rest = $row["rest"];
	$website = $row["website"];
	$state_program = $row['state_program'];
	$guaranteed = $row["guaranteed"];
	$number_turist = $row["number_turist"];
	$status_san = $row["status_san"];
	$status_agent = $row["status_agent"];
	$schet_san = $row["schet_san"];
	$date_schet_san = $row["date_schet_san"];
	$object = get_object($connect, $id_obj, "full_and_place");
	$reason_delete = $row["reason_delete"];
	$doc_schet_san = $row["doc_schet_san"];
	$note_bid = $row["note_bid"];
	$check_cabinet = 0;
	$state_program =$row['state_program'];

	if(($turist AND $connect->getOne("SELECT id FROM klient WHERE id=?i AND login!=''", $turist)) OR ($agency AND $connect->getOne("SELECT id FROM agency WHERE id=?i AND login!=''", $agency)))
		$check_cabinet = 1;
	if($reason_delete AND $reason_delete != 1 AND $active == 3)
		$note_schet.= " Причина удаления: ".$connect->getOne("SELECT name FROM reason_delete WHERE id=?i", $reason_delete);
	if($reason_delete AND $active != 3 AND ($status == 6 OR $status == 8))
		$note_schet.= " Причина аннуляции: ".$connect->getOne("SELECT name FROM reason_delete WHERE id=?i", $reason_delete);
	$name_status = $connect->getOne("SELECT name FROM status WHERE id=?i", $status);
	$name_status_san = $connect->getOne("SELECT name FROM status_san WHERE id=?i", $status_san);
	$name_status_agent = $connect->getOne("SELECT name FROM status_agent WHERE id=?i", $status_agent);
	$bonus_str = "";
	if($agency != ''){
		$znak = $agency;
		$col = "agency";
	}else{
		$znak = $turist;
		$col = "turist";
		$bon_sum = $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $id);
		if($bon_sum){
			$dopusk = "";
			if($itog_sum > 0){
				if(($itog_sum * 0.10) < abs($bon_sum))
					$dopusk = " <i class='fa fa-exclamation-triangle icon_warning' title='Использовано бонусов больше, чем 5% от стоимости путевки'></i>";
			}
			$bonus_str = "<strong>Использовано бонусов:</strong> <span class='bonus'>".abs($bon_sum)."</span>".$dopusk."<span style=\"display: none;\">SELECT sum FROM bonus WHERE schet=$id AND sum < 0</span><br />";
		}
	}
	$class = "default";
	if($status == 5)
		$class = "success";
	if($status == 6)
		$class = "danger";
	if ($status!=3 && $status!=4) $status_string = '<div style="padding: 2px">Статус заявки<br /><h3><span class="label label-'.$class.'" data-reckoning-status="'.$status.'">'.$name_status.'</span></h3></div>';
	else $status_string = '<div style="padding: 2px">Статус заявки<br /><h3><span class="label label-'.$class.'" data-reckoning-status="'.$status.'">'.$name_status.'</span></h3><br></div><div style="padding: 2px 8px; "><a href="/CRM/core/cron/check-info.php?bid='.$id.'" target="_blank">проверить оплату</a></div>';
	if ($bnovo==1) $status_string .= '<div style="padding: 8px 8px; "><a href="/CRM/bnovo-cancel.php?id='.$id.'" target="_blank" style="font-weight: bold";>ОТМЕНИТЬ БРОНЬ В BNOVO</a></div>';
	if ($bnovo==2) $status_string .= '<div style="padding: 5px 8px; font-weight: bold;">Эта заявка была ОТМЕНЕНА в BNOVO<br></div>';
	$class = "default";
	if($status_san == 1)
		$class = "success";
	$status_string.= "<div style='padding: 2px'>В санаторий<br /><h3><span class='label label-".$class."'>".$name_status_san."</span></h3></div>";
	if($status == 5 AND $agency)
		$status_string.= "<div style='padding: 2px'>Отчета агента<br /><h3><span class='label label-default'>".$name_status_agent."</span></h3></div>";
	$commis = "";
	if($id_com){
		$commis_agency = $connect->getOne("SELECT value FROM commission WHERE id=?i", $id_com);
		$sum_agency = get_reward_agency($connect, $id);
		$commis = "<strong>Комиссия: </strong>".$commis_agency."% (".number_format(add_null($sum_agency), 2, ".", "").")<br />";
	}/*else*/if($id_dis) {
	   $discount =  $connect->getRow("SELECT `value`, `type` FROM discount WHERE id=?i", $id_dis);
	   if($discount) {
	     $discount_str = $discount['value'].($discount['type'] == 1?"%":" руб.");
         $commis .= "<strong>Скидка: </strong>".$discount_str."<br />";

       }
    }
	$div_warning = "";
	if($agency){
		$row = $connect->getRow("SELECT schet, putevka FROM agency_document WHERE id_reck=?i", $id);
		$document = "Счет: ";
		if($row['schet'] == 0 OR $row['schet'] == 1)
			$document.= "не сформирован<br />";
		elseif($row['schet'] == 2)
			$document.= "сформирован<br />";
		$document.= "Путевка: ";
		if($row['putevka'] == 0)
			$document.= "не разрешен<br />";
		elseif($row['putevka'] == 1)
			$document.= "разрешен<br />";
		elseif($row['putevka'] == 2)
			$document.= "сформирован<br />";
		if($status == 5 AND ($row["putevka"] == 0 OR !$row["putevka"]))
			$div_warning = "<button onclick='agency_document(\"".$id."\", \"putevka\")' class='btn btn-danger btn-xs'><i class='fa fa-exclamation-triangle'></i> Разрешить распечатать путевку</button><br />";
	}
	$guaranteed_image = "";
	if($guaranteed)
		$guaranteed_image = "&nbsp;<i class='fa fa-star icon_star'></i>";
	$reward_sum = add_null(get_reward_schet($connect, $id));
	$style = "";
	if($reward_sum < 0){
		$style = "color: red;";
		$reward_sum.= " исправьте вознаграждение";
	}
	$payment_div = "";
	$arr = get_payment($connect, $id, 1);
	$payment_showed = FALSE;
	foreach($arr as $payments){
	    $payment_el_class = "";

	    if($payments['status'] == 1) {
          $payment_el_class .= ' not-confirmed';
        }
        elseif ($payments['status'] == 0) {
          $payment_el_class .= ' cancelled';
        }

		$payment_div.= '<div class="payment-element'.$payment_el_class.'" data-payment-id="'.$payments['id'].'"><strong>Предоплата:</strong> '.$payments["sum"];
		if($id_rights > 3 AND ($active == 0 OR $active == 1) AND $payments["pay_method"] != "сертификатом") {
		  $buttons = '<span style="float: right;">';
		  if($payments['status'] != 1) {
		    $buttons .= '<button type="button" class="btn btn-default btn-xs" onclick="edit_payment('. $payments['id'] . ')">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>';

		    if($payments['status'] != 0 && $id_rights > 4)
		        $buttons .= ' &nbsp; <button type="button" class="btn btn-danger btn-xs" onclick="delete_payment_prepare(' . $payments['id'] . ')">&nbsp;<i class="fa fa-trash-o"></i>&nbsp;</button>';
          }
          $buttons .= '</span>';
          $payment_div .= $buttons;
        }
		$payment_div.= "<br /><strong>Дата и время платежа:</strong> ".$payments['datetime']."<br />";
		$payment_div.= "<strong>Способ:</strong> ".$payments['pay_method']."<br />";

		if($payments["datetime"] != $payments['datetime_processed']) {
		    if(is_null($payments['datetime_processed'])) {
              $payment_div.= '<strong class="text-danger">Не обработан</strong>'.'<br />';
            }
            else {
              $payment_div.= "<strong>Обработан:</strong> ".$payments['datetime_processed']."<br />";
            }
        }

        $payment_div .= '<hr />';

		if($payments['status'] == 1 && ($id_rights > 3 || ($session_login > 0 && $session_login == $id_user))) {
		    $payment_div .= '<div class="row clearfix payment-actions-block">';
		    $hide_confirm = '';
		    if($payment_showed)
		        $hide_confirm = ' hidden';

		    $payment_div .= '<div class="col-sm-6"><button class="btn btn-sm btn-success pull-right payment-confirm-button'.$hide_confirm.'" onclick="confirm_payment_show_modal('. $payments['id'] . ')">Подтвердить</button></div>';
            $payment_div .= '<div class="col-sm-6"><button class="btn btn-sm btn-danger pull-left" onclick="cancel_payment_show_modal('. $payments['id'] . ')">Отменить</button></div>';
            $payment_div .='</div>';
        }
		$payment_div .= '</div>';
		$payment_showed = true;
	}
	$arr = get_payment($connect, $id, 2);
	foreach($arr as $payments){

		if($status_san == 5 OR $status_san == 4)
			$add = " в санаторий";
		else
			$add = "";
        $payment_div .= '<div class="payment-element">';
		$payment_div.= "<strong>Оплата".$add.":</strong> ".$payments['sum'];
		$payment_div.= '<span style="float: right;">';
          if($id_rights > 4 && $active == 0 && in_array($payments['pay_method_int'],[1,2,3,5])) {
            $payment_div.= "<button type='button' class='btn btn-danger btn-xs' style='float: right;' onclick='delete_payment_prepare(\"".$payments['id']."\")'>&nbsp;<i class='fa fa-trash-o'></i>&nbsp;</button>";
          }

		if($id_rights > 3 AND $active == 0 AND $payments['pay_method'] != 'сертификатом')
			$payment_div.= "<button type='button' class='btn btn-default btn-xs' style='float: right;' onclick='edit_payment(\"".$payments['id']."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button>";
        $payment_div.= '</span>';

      $payment_div.= "<br /><strong>Дата и время платежа:</strong> ".$payments['datetime']."<br />";
      $payment_div.= "<strong>Способ:</strong> ".$payments['pay_method']."<br />";

      if($payments["datetime"] != $payments['datetime_processed']) {
        if(is_null($payments['datetime_processed'])) {
          $payment_div.= '<strong class="text-danger">Не обработан</strong>'.'<br />';
        }
        else {
          $payment_div.= "<strong>Обработан:</strong> ".$payments['datetime_processed']."<br />";
        }
      }

      $payment_div .= '<hr />';

      $payment_div .= '</div>';
	}
	$arr = get_payment($connect, $id, 3);
	foreach($arr as $payments){
        $payment_div .= '<div class="payment-element">';
		$payment_div.= "<strong>Предоплата в санаторий:</strong> ".$payments["sum"];
	if($id_rights > 3 AND $active == 0)
		$payment_div.= "<span style='float: right;'><button type='button' class='btn btn-default btn-xs' onclick='edit_payment(\"".$payments['id']."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button> <button type='button' class='btn btn-danger btn-xs' onclick='delete_payment_prepare(\"".$payments["id"]."\")'>&nbsp;<i class='fa fa-trash-o'></i>&nbsp;</button></span>";
		$payment_div.= "<br /><strong>Дата:</strong> ".$payments["date"]."<br />";
		$payment_div.= "<strong>Номер платежного поручения:</strong> ".$payments["pay_number"]."<br /><hr />";
        $payment_div .= '</div>';
	}
	$arr = get_payment($connect, $id, 4);
	foreach($arr as $payments){
        $payment_div .= '<div class="payment-element">';
		$payment_div.= "<strong>Оплата в санаторий:</strong> ".$payments["sum"];
		if($id_rights > 3 AND $active == 0)
			$payment_div.= "<span style='float: right;'><button type='button' class='btn btn-default btn-xs' onclick='edit_payment(\"".$payments['id']."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button> <button type='button' class='btn btn-danger btn-xs' onclick='delete_payment_prepare(\"".$payments["id"]."\")'>&nbsp;<i class='fa fa-trash-o'></i>&nbsp;</button></span>";
		$payment_div.= "<br /><strong>Дата:</strong> ".$payments["date"]."<br />";
		$payment_div.= "<strong>Номер платежного поручения:</strong> ".$payments["pay_number"]."<br /><hr />";
        $payment_div .= '</div>';
	}
	$arr = get_payment($connect, $id, 5);
	foreach($arr as $payments){
        $payment_div .= '<div class="payment-element">';
		$payment_div.= "<strong>Возврат:</strong> ".$payments["sum"];
		if($id_rights > 3 AND $active == 0)
			$payment_div.= "<button type='button' class='btn btn-default btn-xs' style='float: right;' onclick='edit_payment(\"".$payments['id']."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button>";
		$payment_div.= "<br /><strong>Дата:</strong> ".$payments["date"]."<br />";
		$payment_div.= "<strong>Способ:</strong> ".$payments["pay_method"]."<br />";
		$payment_div.= "<strong>Номер платежного поручения:</strong> ".$payments["pay_number"]."<br /><hr />";
        $payment_div .= '</div>';
	}

    $arr = get_payment($connect, $id, 6);
    foreach($arr as $payments){
        if($status_san == 5 OR $status_san == 4)
          $add = " в санаторий";
        else
          $add = "";
        $payment_div .= '<div class="payment-element">';
        $payment_div.= "<strong>Доплата".$add.":</strong> ".$payments['sum'];
        if($id_rights > 3 AND $active == 0 AND $payments['pay_method'] != 'сертификатом')
          $payment_div.= "<button type='button' class='btn btn-default btn-xs' style='float: right;' onclick='edit_payment(\"".$payments['id']."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button>";
        $payment_div.= "<br /><strong>Дата и время платежа:</strong> ".$payments['datetime']."<br />";
        $payment_div.= "<strong>Способ:</strong> ".$payments['pay_method']."<br />";

        if($payments["datetime"] != $payments['datetime_processed']) {
          if(is_null($payments['datetime_processed'])) {
            $payment_div.= '<strong class="text-danger">Не обработан</strong>'.'<br />';
          }
          else {
            $payment_div.= "<strong>Обработан:</strong> ".$payments['datetime_processed']."<br />";
          }
        }

        $payment_div .= '<hr />';

        $payment_div .= '</div>';
    }

	if($document)
		$html.= "<button onclick=\"$('#document_div').toggle();\" class='btn btn-default btn-xs'><i class='fa fa-file-text-o'></i> Документы</button><br /><div style='display: none;' id='document_div'><hr />".$document."<hr /></div>";
	if($note_schet){
		$note_schet = str_replace("\n", "<br />", $note_schet);
		$html.= "<span><br /><strong>Примечание:</strong><br />".$note_schet."</span>";
	}
	if($doc_schet_san){
		$array = json_decode($doc_schet_san, TRUE);
		foreach($array as $index => $schet_san_el){
			$text = "Счет от санатория";
			if($schet_san_el["type"] == "garant")
				$text = "Гарантийное письмо";
			if($schet_san_el["type"] == "return")
				$text = "Заявление на возврат";
			if($schet_san_el["type"] == "resetting")
				$text = "Заявление перезачет";
			$html.= "<div class='alert alert-success well-sm'><i class='fa fa-file-word-o'></i> <a href='temp/schet/".$schet_san_el["doc"]."' target='_blank' class='alert-link'>".$text."</a> <button type='button' class='btn btn-danger btn-xs' onclick='delete_schet_san(\"".$id."\", \"".$index."\")'>&nbsp;<i class='fa fa-times-circle'></i>&nbsp;</button></div>";
		}
	}
	    $html .= "<div class='well well-sm'>";
        if($reck_type == 0) {
          $html .= "<button type='button' class='btn btn-default btn-xs' onclick='form_upload_document(\"" . $id . "\", \"bill\")'><i class='fa fa-upload icon_download'></i> Счет санатория</button>";
          $html .= "&nbsp;<button type='button' class='btn btn-default btn-xs' onclick='form_upload_document(\"" . $id . "\", \"garant\")'><i class='fa fa-upload icon_download'></i> Гарант. письмо</button>";
        }

        $html.= "&nbsp;<button type='button' class='btn btn-default btn-xs' onclick='form_upload_document(\"".$id."\", \"return\")'><i class='fa fa-upload icon_download'></i> Возврат</button>";
		$html.= "&nbsp;<button type='button' class='btn btn-default btn-xs' onclick='form_upload_document(\"".$id."\", \"resetting\")'><i class='fa fa-upload icon_download'></i> Перезачет</button></div>";
	if($active == 1)
	if($active == 1)
		$html.= "<div class='alert alert-danger'><i class='fa fa-exclamation-triangle'></i> Счет от санатория отложен</div>";
	$data = $connect->getAll("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date, sum, type FROM time_payment WHERE id_schet=?i ORDER BY type DESC", $id);
	$time_payment = "";
	foreach($data as $row){
		if($time_payment)
			$time_payment.= "<br />";
		if($row["type"] == 1)
			$time_payment.= "<i class='fa fa-credit-card'></i> Срок оплаты до <strong>".$row["date"]."</strong>";
		if($row["type"] == 2)
			$time_payment.= "<i class='fa fa-credit-card'></i> Предоплата ".$row["sum"]." до <strong>".$row["date"]."</strong>";
	}
	if($time_payment)
		$html.= "<div class='alert alert-info'>".$time_payment."</div>";
	$table = "";
	$check_quota = 0;
	$confirm_booking_quota = 0;
	if($connect->getOne("SELECT id FROM booking WHERE bid=?i", $id)){
		$confirm_booking_quota = $connect->getOne("SELECT confirm FROM booking WHERE bid=?i", $id);
		$count_check_quota = $connect->getOne("SELECT COUNT(*) FROM position_reck WHERE schet=?i AND ratePlan>0", $id);
		if($count_check_quota == 1)
			$check_quota = 1;
	}
	$data = $connect->getAll("SELECT id, id_room, id_service, number, sum, note, type, days, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, add_one_day, reward, ratePlan FROM position_reck WHERE schet=?i", $id);
	foreach($data as $row){
		$id_pos = $row["id"];
		if($check_quota == 1 AND $row["ratePlan"] > 0)
			$button = "<button type='button' class='btn btn-default btn-xs' onclick='edit_position_reck(\"".$id_pos."\", \"".$id."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button>";
		elseif($active == 0 OR $active == 1)
			$button = "<button type='button' class='btn btn-default btn-xs' onclick='edit_position_reck(\"".$id_pos."\", \"".$id."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button>&nbsp;<button type='button' class='btn btn-danger btn-xs' onclick='remove_position_reck(\"".$id_pos."\", \"".$id."\")'>&nbsp;<i class='fa fa-trash-o'></i>&nbsp;</button>";
		else
			$button = "";
		if($row["ratePlan"] > 0)
			$button.= "  <span class='label label-primary'>Q</span>";
		$id_room = $row["id_room"];
		if($id_room == 0)
			$room = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $row["id_service"]);
		else
			$room = get_room($connect, $id_room, "full", "view_schet");
		$sum = $row["sum"];
		$days = $row["days"];
		$reward = $row["reward"];
		$add_one_day = $row["add_one_day"];
		if($add_one_day == 1)
			$add_one_day = "Сутки";
		else
			$add_one_day = "Дни";
		$zaezd = $row["date_z"];
		$number = $row["number"];
		$note = $row["note"];
		$type_price = $row["type"];
		if($type_price == 1)
			$type_price = "За чел/сутки";
		elseif($type_price == 2)
			$type_price = "За номер (дом)";
		elseif($type_price == 3)
			$type_price = "За заезд";
		$table.= "<tr>";

		if($reck_type == 0) {
          $class = get_class_change($changes, "position", $id_pos, 'room');
          $table .= "<td width='200' " . $class . ">" . $room . "</td>";
        }

		$class = get_class_change($changes, 'position', $id_pos, 'number');
		$table.= "<td width='15' style='text-align: center;' ".$class.">".$number."</td>";
        if($reck_type == 0) {
          $class = get_class_change($changes, 'position', $id_pos, 'date_z');
          $table .= "<td width='70' " . $class . ">" . str_replace("-", ".", $zaezd) . "</td>";
          $class = get_class_change($changes, 'position', $id_pos, 'days');
          $table .= "<td width='40' style='text-align: center;' " . $class . ">" . $days . "</td>";
        }
        $class = get_class_change($changes, 'position', $id_pos, 'reward');
		$table.= "<td width='70' style='text-align: center;' ".$class.">".$reward."% (".get_reward_schet_position($connect, $id_pos).")</td>";

		if($reck_type == 0) {
            $table .= "<td width='40' style='text-align: center;'>" . $add_one_day . "</td>";
        }

        $class = get_class_change($changes, 'position', $id_pos, 'sum');
		$table.= "<td width='50' ".$class." style='text-align:center;'>".$sum."</td>";

          if($reck_type == 0) {
            $class = get_class_change($changes, 'position', $id_pos, 'type');
            $table .= "<td width='100' " . $class . ">" . $type_price . "</td>";
          }
		$class = get_class_change($changes, 'position', $id_pos, 'note');
		$table.= "<td width='100' ".$class.">".$note."</td>";
		$table.= "<td width='80'>".$button."</td>";
		$table.= "</tr>";
	}
	if($table) {
      if($reck_type == 0) {
        $table_room = "<table class='table table-condensed'><tr class='text-center'><th>Номер</th><th>N</th><th>Заезд</th><th>Дней</th><th>Воз-ие</th><th></th><th>Цена</th><th>Тип</th><th>Примечание</th><th></th></tr>" . $table . "</table>";
      }
      else {
        $table_room = "<table class='table table-condensed'><tr class='text-center'><th>N</th><th>Воз-ие</th><th>Цена</th><th>Примечание</th><th></th></tr>" . $table . "</table>";
      }
    }
	$table = "";
	$rest = explode(",", $rest);
	foreach($rest as $tur){
		if($tur){
			$row = $connect->getRow("SELECT surname, name, otch, passport, DATE_FORMAT(date, '%d.%m.%Y') as date, birth_certificate FROM klient WHERE id=?i", $tur);
			$button = "";
			if($active == 0 OR $active == 1)
				$button = "<button type='button' class='btn btn-default btn-xs' onclick='edit_klient_reck(\"".$tur."\", \"".$id."\")'>&nbsp;<i class='fa fa-pencil'></i>&nbsp;</button>&nbsp;<button type='button' class='btn btn-danger btn-xs' onclick='remove_klient_reck(\"".$tur."\", \"".$id."\")'>&nbsp;<i class='fa fa-trash-o'></i>&nbsp;</button>";
			if($reck_type == 0)
			    $button.= "&nbsp;<button type='button' class='btn btn-info btn-xs' onclick='show_dover(\"".$id."\", \"".$tur."\")'>Доверенность</button>";
			if(!$row["passport"])
				if($row["birth_certificate"])
					$row["passport"] = $row["birth_certificate"]." Св. о рожд.";
			$table.= "<tr>";
			$table.= "<td width='450'>".$row["surname"]." ".$row["name"]." ".$row["otch"]."</td>";
			$table.= "<td width='69'>".date_check($row["date"])."</td>";
			$table.= "<td width='145'>".$row["passport"]."</td>";
			$table.= "<td>".$button."</td>";
			$table.= "</tr>";
		}
	}
	if($table)
		$table_turist = "<table class='table table-condensed'><tr><th>Фамилия Имя Отчество</th><th>Дата</th><th>Паспорт</th><th></th>".$table."</table>";
	else
        $table_turist = "";

	$class = "info";
	if($status == 5)
		$class = "success";
	if($active == 3 OR $status == 6)
		$class = "danger";
	$data_object = $connect->getRow("SELECT website, arrival, leaving FROM object WHERE id=?i", $id_obj);
	$data_object["contract"] = select_object_contract($connect, $id_obj);
	ob_start();
?>

<?php if($turist AND $connect->getOne("SELECT id FROM reckoning WHERE turist=?i AND (status<5 OR status=9) AND id!=?i AND active!=3", $turist, $id)){ ?>
	<div class="alert alert-danger pointer" style="margin-bottom: 5px" onclick="klient_schet()">Смотреть другие заявки в работе</div>
<?php } ?>
<?php if(($status <= 4 OR $status == 9) AND $connect->getOne("SELECT id FROM object WHERE id=?i AND check_places != 0", $id_obj)){ ?>
	<div class="alert alert-success pointer quota-object-bid" bid="<?php echo $id; ?>" style="margin-bottom: 5px" onclick="show_quota_object_bid(<?php echo $id_obj; ?>)">
		<h3>Для объекта есть квота мест</h3>
		<button class="btn btn-success btn-sm pull-right"><i class="fa fa-calendar-plus-o"></i> Смотреть квоту</button>
		<div class="clearfix"></div>
	</div>
<?php if($confirm_booking_quota == 1){ ?>
	<div class="alert alert-success" style="margin-bottom: 5px"><i class="fa fa-check-circle"></i> Заявка подтвержденна системой</div>
<?php } ?>
<?php } ?>

<div class="panel panel-<?php echo $class; ?>">
	<div class="panel-heading">
		<i class="fa fa-file-text-o"></i> Заявка <h3><strong><?php echo $id; ?></strong><?php echo $guaranteed_image; ?></h3>&nbsp;&nbsp;&nbsp;
		<button type="button" class="btn btn-default btn-sm" onclick="klient_schet()">&nbsp;<i class="fa fa-angle-double-left"></i>&nbsp;</button>
		<button type="button" class="btn btn-default btn-sm reload-btn" onclick="view_schet(<?php echo $id; ?>)">&nbsp;<i class="fa fa-repeat fa-spin_hover"></i>&nbsp;</button>
	<?php if($id_user AND ($active == 0 OR $active == 1)){ ?>
		<button type="button" class="btn btn-default btn-sm" onclick="edit_schet(<?php echo $id; ?>)">&nbsp;<i class="fa fa-pencil pointer icon_edit"></i>&nbsp;</button>
	<?php } ?>
	<button type="button" class="btn btn-default btn-sm" onclick="show_history_schet(<?php echo $id; ?>)">История</button>
	<?php if($check_cabinet == 1){ ?>
		<button type="button" class="btn btn-default btn-sm" onclick="show_talk_reckoning(<?php echo $id; ?>)">Переписка</button>
	<?php } ?>
	<?php if(($status <= 4) AND ($turist) AND all_klient_bonus($connect, $turist) > 0){ ?>
		<button type="button" class="btn btn-success btn-sm" onclick="select_bonus_schet(<?php echo $id; ?>)">Бонусы</button>
	<?php } ?>
	<?php if($id_user){ ?>
		<button type="button" class="btn btn-default btn-sm" id="btn-menu-document" onclick="show_menu_document(<?php echo $id; ?>)">&nbsp;<i class="fa fa-file-text-o"></i>&nbsp;<i class="fa fa-angle-down"></i>&nbsp;</button>
	<?php } ?>
	<button type="button" class="btn btn-default btn-sm" id="other<?php echo $id; ?>" onclick="show_but_div('<?php echo $id; ?>')">Ещё&nbsp;<i class="fa fa-angle-down"></i>&nbsp;</button>
	<?php if(in_array($id_rights,[5,6])){ ?>
	<button type="button" class="btn btn-primary btn-sm" id="admin-<?php echo $id; ?>" onclick="show_but_div_admin('<?php echo $id; ?>')">Админ <i class="fa fa-angle-down"></i></button>
	<?php } ?>
	<?php if($connect->getOne("SELECT id FROM rating WHERE schet=?i AND status=3", $id)){ ?>
		<button type="button" class="btn btn-success btn-sm" onclick="view_rating_schet('<?php echo $id; ?>')">&nbsp;<i class="fa fa-comments-o"></i> Отзыв</button>
	<?php } ?>
	</div>
	<div class="form-horizontal panel-body">
		<div class="form-group" style="margin-bottom: 0;">
			<div class="col-sm-5 desc-schet">
				<?php echo $div_warning; ?>
				<strong>Дата добавления:</strong> <?php echo $date; ?><br />
                <?php if($reck_type == 0) { ?>
                    <?php if($website){ ?>
                        <strong>Сайт:</strong> <a href="#"><?php echo $website; ?></a><br />
                    <?php } ?>
                    <strong>Объект:</strong> <?php echo $object; ?> <span class="label label-success pointer object-info"><i class="fa fa-info"></i> Информация</span><br />
                    <?php if($id_tour){ ?>
                        <strong>Туроператор: </strong><?php echo $connect->getOne("SELECT name FROM tour_operator WHERE id=?i", $id_tour); ?><br />
                    <?php } ?>
                    <strong>Заезд:</strong> с <?php echo $date_z." по ".$date_v; ?><br />
                    <strong>Кол-во отдыхающих:</strong> <?php echo $number_turist; ?><br />
                <?php } ?>
				<strong>Менеджер:</strong> <?php echo $manager; ?><br />
				<?php if($schet_san){ ?>
					<strong>Счет санатория:</strong> №<?php echo $schet_san; ?> от <?php echo month_transform($date_schet_san); ?><br />
				<?php } ?>
				<strong>Стоимость путевки:</strong> <?php echo $itog_sum; ?><br />
				<?php echo $bonus_str; ?>
				<?php echo $commis; ?>
				<?php if($correction != 0){ ?>
					<strong>Поправка:</strong> <?php echo $correction; ?><br />
				<?php } ?>
				<strong>Итоговое вознаграждение:</strong> <span style="<?php echo $style; ?>; text-decoration: underline; cursor: pointer;" id="span_reward" onmouseover="show_reward_schet('<?php echo $id; ?>')" onmouseout="$('#div_buttons').remove()"><?php echo $reward_sum; ?></span><br>
				<strong>Гос. субсидии:</strong> <?=($state_program) ? 'Да' : 'Нет';?><br>
				<strong>Номер участника мили Аэрофлот:</strong> <?=$afl?><br>
                <?php if($payment_div){ ?>
					<div><button class="btn btn-default btn-xs" onclick="$('.payment-schet').show(); $('.desc-schet').hide();"><i class="fa fa-credit-card"></i> Платежи</button></div>
				<?php } ?>
			</div>
			<div class="col-sm-5 payment-schet" style="display: none">
				<?php echo $payment_div; ?>
				<div><button class="btn btn-default btn-xs" onclick="$('.payment-schet').hide(); $('.desc-schet').show();"><i class="fa fa-pencil-square-o"></i> Описание</button></div>
			</div>
			<div class="col-sm-4">
				<?php echo $html; ?>
			</div>
			<div class="col-sm-3">
				<?php echo $status_string; ?>
			</div>
		</div>
	</div>
	<?php echo $table_room.$table_turist; ?>
	<?php if($note_bid){ ?>
		<table class="table table-condensed">
		<tr>
			<th>Примечание</th>
		</tr>
		<tr>
			<td><?php echo $note_bid; ?></td>
		</tr>
		</table>
	<?php } ?>
	<?php if($active == 0){ ?>
	<div class="panel-footer">
		<button type="button" onclick="add_new_position('<?php echo $id; ?>')" class="btn btn-default btn-sm"><i class="fa fa-plus-circle"></i> Добавить позицию</button>&nbsp;&nbsp;
		<?php if($reck_type == 0) { ?>
            <button type="button" onclick="add_new_turist('<?php echo $id; ?>')" class="btn btn-default btn-sm"><i class="fa fa-plus-circle"></i> Добавить туриста</button>&nbsp;&nbsp;
		    <button type="button" onclick="add_service_reckoning('<?php echo $id; ?>')" class="btn btn-default btn-sm"><i class="fa fa-plus-circle"></i> Добавить услугу</button>
        <?php } ?>
		<button type="button" onclick="edit_note_bid_reckoning('<?php echo $id; ?>')" class="btn btn-default btn-sm"><i class="fa fa-plus-circle"></i> Примечание</button>
	</div>
	<?php } ?>
</div>
<?php
	$data = array();
	$data["html"] = ob_get_clean();
	$data["object-info"] = $data_object;
	return json_encode($data);
}

function cancel_payment($connect) {
    $id = (int)$_POST["id"];
    $payment = new \App\lib\payment\Sberbank\BookingPayment();
    include_once(__DIR__.'/class/turist/DisplayClient.Class.php');
    include_once(__DIR__.'/class/mail/SendMail.Class.php');
    include_once(__DIR__.'/class/mail/SendMailTurist.Class.php');

    return json_encode($payment->cancelPayment($id));
}

function confirm_payment($connect) {
  $id = (int)$_POST["id"];
  $payment = new \App\lib\payment\Sberbank\BookingPayment();
  include_once(__DIR__.'/class/turist/DisplayClient.Class.php');
  include_once(__DIR__.'/class/bonus/DisplayBonus.Class.php');
  include_once(__DIR__.'/class/mail/SendMail.Class.php');
  include_once(__DIR__.'/class/mail/SendMailTurist.Class.php');
  return json_encode($payment->confirmPayment($id));
}

function edit_payment($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT * FROM payment WHERE id=?i", $id);
	$select = array(1 => "", 2 => "", 3 => "", 4 => "", '5-1' => "", '5-2' => "", '7' => "");
	if($row["pay_method"] != 5)
	{
        $select[$row["pay_method"]] = " selected ";
    }
    else {
	    if($row['terminal'])
	        $select["5-2"] = ' selected ';
	    else
	        $select["5-1"] = ' selected ';
    }
	$type = $row["type"];
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменение платежа. Заявка №<?php echo $row["schet"]; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-payment">
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date_payment" value="<?php echo $row['date']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Сумма</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="sum_payment" value="<?php echo $row['sum']; ?>" onKeyPress="validate_sum('sum_payment')" />
						</div>
					</div>
					<?php if($type == 1 OR $type == 2 OR $type == 5){ ?>
						<div class="form-group">
							<label class="col-sm-4 control-label">Способ оплаты</label>
							<div class="col-sm-8">
								<select class="form-control" id="pay_method">
									<option value="1" <?php echo $select[1]; ?>>Безналичный</option>
									<option value="2" <?php echo $select[2]; ?>>Наличными</option>
									<option value="4" <?php echo $select[4]; ?>>На месте</option>
									<option value="5-1" <?php echo $select['5-1']; ?>>Банковской картой</option>
                                    <option value="5-2" <?php echo $select['5-2']; ?>>Банковской картой через терминал</option>
									<option value="7" <?php echo $select['7']; ?>>СБП</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-label">Офис</label>
							<div class="col-sm-8">
								<?php echo get_office_for_pay($connect, $row["office"]); ?>
							</div>
						</div>
					<?php } ?>
					<?php if($type == 3 OR $type == 4 OR $type == 5){ ?>
						<div class="form-group">
							<label class="col-sm-4 control-label">Номер плат.поручения</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="pay_number" value="<?php echo $row['pay_number']; ?>" />
							</div>
						</div>
					<?php } ?>
					<?php if($type == 2){ ?>
						<div class="form-group">
							<label class="col-sm-4 control-label">Предоплата</label>
							<div class="col-sm-8">
								<input type="checkbox" id="pay_to_prepay" />
							</div>
						</div>
					<?php } ?>
					<?php if($type == 4){ ?>
						<div class="form-group">
							<label class="col-sm-4 control-label">Предоплата в санаторий</label>
							<div class="col-sm-8">
								<input type="checkbox" id="pay_to_prepay" />
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="save_payment('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}


function delete_payment_prepare($connect){
  $id = $_POST["id"];
  $row = $connect->getRow("SELECT * FROM payment WHERE id=?i", $id);
  $select[$row["pay_method"]] = " SELECTED ";
  $type = $row["type"];
  ob_start();
  ?>
    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Удаление платежа. Заявка №<?php echo $row["schet"]; ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        Вы уверены, что хотите удалить платеж?
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" onclick="delete_payment('<?php echo $id; ?>',true)"><i class="fa fa-check-circle"></i> Да</button>
                    <button type="button" class="btn btn-danger btn-sm"  data-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>
  <?php
  $html = ob_get_clean();
  return $html;
}

function delete_payment($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date, sum, pay_number, type, schet FROM payment WHERE id=?i", $id);
	$connect->query("DELETE FROM payment WHERE id=?i AND type!=5 LIMIT 1", $id);
	$schet = $row["schet"];
	$new_status = 0;
	$new_status_san = -1;
	if($row["type"] == 1) {
      if (!get_payment($connect, $schet, 1) AND !get_payment($connect, $schet, 2)) {
        $new_status = 3;
      }
      $type = "предоплата";
    }
	elseif($row["type"] == 2) {
      $new_status = 3;

      if (get_payment($connect, $schet, 1)) {
        $new_status = 4;
      }

      if (get_payment($connect, $schet, 2)) {
        $new_status = 5;
      }

      $type = "оплата";

	}elseif($row["type"] == 3){
		if(!get_payment($connect, $schet, 3) AND !get_payment($connect, $schet, 4))
			$new_status_san = 0;
		$type = "предоплата в санаторий";
	}elseif($row["type"] == 4){
		if(get_payment($connect, $schet, 3))
			$new_status_san = 3;
		else
			$new_status_san = 0;
		$type = "оплата в санаторий";
	}
	if($type){
		$note = "Удален платеж ".$type.". Cумма: ".$row["sum"].", дата: ".$row["date"];
		save_schet_to_history($connect, $schet, $note);
	}
	if($new_status > 0){
		$connect->query("UPDATE reckoning SET status=?i WHERE id=?i", $new_status, $schet);
		save_schet_to_history($connect, $schet, "Изменение статуса из-за удаления платежа");
	}
	if($new_status_san >= 0){
		$connect->query("UPDATE reckoning SET status_san=?i WHERE id=?i", $new_status_san, $schet);
		save_schet_to_history($connect, $schet, "Изменение статуса из-за удаления платежа");
	}
	return $schet;
}

function update_payment($connect){
    include_once __DIR__.'/../config.php';
    $config = new JConfig();
	$id = $_POST["id"];
	$date = $_POST["date_payment"];
	$sum = (float)str_replace(',','.',$_POST["sum_payment"]);
	$pay_method = (string)$_POST["pay_method"];
	$terminal = 0;
	$bank_com = NULL;
	if(empty($pay_method))
	    $pay_method = 0;
	elseif($pay_method === '5-1' || $pay_method === '5') {
	    $pay_method = 5;
	    $bank_com = $config->BANK_COM_SBERBANK;
    }
    elseif($pay_method === '5-2') {
	    $pay_method = 5;
	    $terminal = 1;
        $bank_com = $config->BANK_COM_SBERBANK_TERMINAL;
    }

	$pay_number = $_POST["pay_number"];
	$pay_to_prepay = $_POST["pay_to_prepay"];
	$office = $_POST["office"];
	$row = $connect->getRow("SELECT date, created, processed, sum, pay_method, pay_number, type, schet, office, terminal, bank_com FROM payment WHERE id=?i", $id);

	if($pay_method == $row['pay_method'] && $pay_method == 5) {
	    if($terminal == $row['terminal']) {
          $bank_com = $row["bank_com"];
        }
    }

	$schet = $row["schet"];
	$type = $row["type"];
	$note = "";
	$array = $connect->getRow("SELECT status, status_san, turist, sum FROM reckoning WHERE id=?i", $schet);
	if($row["date"] != $date)
		$note = "Дата (старое - ".$row["date"].")";
	if($row["sum"] != $sum)
		$note.= " Сумма (старое - ".$row["sum"].")";
	if($row["pay_number"] != $pay_number)
		$note.= " Номер пл.пор. (старое - ".$row["pay_number"].")";
	if(($row["pay_method"] != $pay_method) AND ($type ==1 OR $type == 2 OR $type == 5)){
		if($row["pay_method"] == 1)
			$row["pay_method"] = "безналичный";
		elseif($row["pay_method"] == 2)
			$row["pay_method"] = "наличными";
		elseif($row["pay_method"] == 4)
			$row["pay_method"] = "на месте";
		else if($row["pay_method"] == 5)
		    $row["pay_method"] = "банковской картой";
		if($terminal)
		    $row["pay_method"] .= " через терминал";
		$note.= " Способ (старое - ".$row["pay_method"].")";
	}
	elseif($terminal != $row['terminal'] && $row["pay_method"] == 5) {
	  if($terminal)
	      $note.= " Способ (старое - банковской картой)";
	  else
	      $note.= " Способ (старое - банковской картой через терминал)";
    }

	if($note){
		if($type == 1) $note_type = "предоплата";
		elseif($type == 2) $note_type = "оплата";
		elseif($type == 3) $note_type = "предоплата в санаторий";
		elseif($type == 4) $note_type = "оплата в санаторий";
		elseif($type == 5) $note_type = "возврат";
		$note = "Изменен платеж ".$note_type.": ".$note;
	}
	if($pay_to_prepay == "true" AND $array["status"] == 5 AND $type == 2){
		$connect->query("UPDATE reckoning SET status=4 WHERE id=?i", $schet);
		$connect->query("UPDATE payment SET type=1 WHERE id='$id'");
		save_schet_to_history($connect, $schet, "Оплата изменена на предоплату");
	}
	if($pay_to_prepay == "true" AND $array["status_san"] == 1 AND $type == 4){
		$connect->query("UPDATE reckoning SET status_san=3 WHERE id=?i", $schet);
		$connect->query("UPDATE payment SET type=3 WHERE id=?i", $id);
		save_schet_to_history($connect, $schet, "Оплата в санаторий изменена на предоплату в санаторий");
	}
	if($office != $row["office"]){
		$connect->query("UPDATE payment SET office=?i WHERE id=?i", $office, $id);
		save_schet_to_history($connect, $schet, "Изменен офис оплаты");
	}
	if($note)
		save_schet_to_history($connect, $schet, $note);
	$date_t = strtotime($date);
	if($row['created'] != $row['processed'])
	    $connect->query("UPDATE payment SET date=?s, processed = ?i, sum=?s, pay_method=?s, pay_number=?s, terminal=?i, bank_com=?s WHERE id=?i", $date, $date_t, $sum, $pay_method, $pay_number, $terminal, $bank_com, $id);
	else
        $connect->query("UPDATE payment SET date=?s, created = ?i, processed = ?i, sum=?s, pay_method=?s, pay_number=?s, terminal=?i, bank_com=?s WHERE id=?i", $date, $date_t, $date_t, $sum, $pay_method, $pay_number,$terminal, $bank_com, $id);
  return $schet;
}

function show_history_schet($connect){
	$id = $_POST["id"];
	$table = "";
	$pagination = "";
	$result = 0;
	$page = 1;
	$data = $connect->getAll("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date, time, id_user, new_status, new_status_san, note FROM history_schet WHERE id_schet=?i ORDER BY id", $id);
	foreach($data as $row){
		$result++;
		$time = $row["time"];
		$date = $row["date"];
		$note = $row["note"];
		$status = $connect->getOne("SELECT name FROM status WHERE id=?i", $row["new_status"]);
		$status_san = $connect->getOne("SELECT name FROM status_san WHERE id=?i", $row["new_status_san"]);
		$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
		if($result == 10){
			$style_nav = "";
			if($page == 1)
				$style_nav = " active";
			$result = 0;
			$pagination.= "<li onclick='show_page_history(\"".$page."\")' class='page-".$page.$style_nav."'><a>".$page."</a></li>";
			$page++;
		}
		$style = "";
		if($page > 1)
			$style = " style='display: none;' ";
		$table.= "<tr class='tr-history tr-".$page."' ".$style.">";
		$table.= "<td width='100' valign='top'>".$status."</td>";
		$table.= "<td width='100' valign='top'>".$status_san."</td>";
		$table.= "<td width='100' valign='top'>".$date."</td>";
		$table.= "<td width='70' valign='top'>".$time."</td>";
		$table.= "<td width='70' valign='top'>".$manager."</td>";
		$table.= "<td width='300' valign='top'>".$note."</td>";
		$table.= "</tr>";
	}
	if($page > 1)
		$pagination.= "<li onclick='show_page_history(\"".$page."\")' class='page-".$page."'><a>".$page."</a></li>";
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading">
		<button type="button" class="btn btn-default btn-xs" onclick="view_schet('<?php echo $id; ?>')">&nbsp;<i class="fa fa-angle-double-left"></i>&nbsp;</button>
		История по заявке <h3><?php echo $id; ?></h3>
	</div>
	<?php if(!count($data)){ ?>
		<div class="panel-body">
			Истории по счету нет
		</div>
	<?php }else{ ?>
		<table class="table table-condensed">
		<tr>
			<th>Статус</th>
			<th>Статус сан</th>
			<th>Дата</th>
			<th>Время</th>
			<th>Менеджер</th>
			<th>Примечание</th>
		</tr>
		<?php echo $table; ?>
		</table>
		<?php if($pagination){ ?>
		<div class="panel-body">
			<ul class="pagination pagination-sm">
				<?php echo $pagination; ?>
			</ul>
		</div>
		<?php } ?>
	<?php } ?>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function view_rating_schet($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT DATE_FORMAT(date_send, '%d.%m.%Y') as date, clean, comfort, location, staff, ratio, leisure, treatment, id_obj, positive, negative, advice, photos, company_rating FROM rating WHERE schet=?i", $id);
	$count = 6;
	$average = $row["clean"] + $row["comfort"] + $row["location"] + $row["staff"] + $row["treatment"] + $row["leisure"] + $row["ratio"];
	if($row["treatment"] == 0)
		$row["treatment"] = "-";
	else
		$count++;
	$average = round($average / $count, 2);
	ob_start();
?>
<div class="form-horizontal panel panel-success">
	<div class="panel-heading">
		<button type="button" class="btn btn-default btn-xs" onclick="view_schet('<?php echo $id; ?>')">&nbsp;<i class="fa fa-angle-double-left"></i>&nbsp;</button>
		Отзыв к заявке <h3><?php echo $id; ?></h3>
	</div>
	<div class="list-group">
		<div class="list-group-item">
			<i class="fa fa-star"></i> <?php echo $average; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<i class="fa fa-trash-o"></i> <?php echo $row["clean"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<i class="fa fa-home"></i> <?php echo $row["comfort"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<i class="fa fa-globe"></i> <?php echo $row["location"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<i class="fa fa-male"></i> <?php echo $row["staff"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<i class="fa fa-plus-square"></i> <?php echo $row["treatment"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<i class="fa fa-gamepad"></i> <?php echo $row["leisure"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<i class="fa fa-shopping-cart"></i> <?php echo $row["ratio"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</div>
	<?php if($row["positive"]){ ?>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Положительное</label>
				<div class="col-sm-10">
					<div class="text-success"><?php echo $row["positive"]; ?></div>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if($row["negative"]){ ?>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Отрицательное</label>
				<div class="col-sm-10">
					<div class="text-danger"><?php echo $row["negative"]; ?></div>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if($row["advice"]){ ?>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Совет</label>
				<div class="col-sm-10">
					<div class="text-info"><?php echo $row["advice"]; ?></div>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if($row["company_rating"]){ ?>
		<div class="list-group-item">
			<div class="form-group form-group-margin">
				<label class="col-sm-2 control-label-element">Работа компании</label>
				<div class="col-sm-10">
					<div><?php echo $row["company_rating"]; ?></div>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if($row["photos"]){ ?>
		<div class="form-group">
			<label class="col-sm-2 control-label-element">Фото отдыха</label>
			<div class="col-sm-10">
			<?php
				$photos = json_decode($row["photos"], TRUE);
				foreach($photos as $photo){ ?>
				<a href="http://xn----7sbaalrb2cl7afpc.xn--p1ai/client/images/rating/big/<?php echo $photo; ?>" target="_blank"><img src="http://xn----7sbaalrb2cl7afpc.xn--p1ai/client/images/rating/thumb/<?php echo $photo; ?>" class="img-thumbnail" /></a>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function select_object_rooms($connect){
	$object = $_POST["id_obj"];
	return select_rooms($connect, $object);
}

function edit_note_bid_reckoning($connect){
	$id = $_POST["id"];
	$note = $connect->getOne("SELECT note_bid FROM reckoning WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить примечание. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Примечание</label>
						<div class="col-sm-8">
							<textarea class="form-control note-bid"><?php echo $note; ?></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success btn-update" data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Сохранение..." onclick="update_note_bid_reckoning('<?php echo $id; ?>')"><i class="fa fa-check"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_note_bid_reckoning($connect){
	$id = $_POST["id"];
	$note = str_replace("plus", "+", $_POST["note"]);
	$connect->query("UPDATE reckoning SET note_bid=?s WHERE id=?i LIMIT 1", $note, $id);
}

function select_bonus_reckoning($connect){
	$id = $_POST["id"];
	$turist = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $id);
	if(!$turist)
		return FALSE;
	$bonus = all_klient_bonus($connect, $turist);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Использовать бонусы. Заявка №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal select-bonus">
					<div class="form-group">
						<label class="col-sm-4 control-label">Всего бонусов</label>
						<div class="col-sm-8">
							<div class="well-sm alert-success"><?php echo $bonus; ?></div>
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Использовать</label>
						<div class="col-sm-5">
							<input type="text" class="form-control input-sm get-bonus" onkeypress="validate_input()" />
						</div>
						<div class="col-sm-3">
							<button type="button" class="btn btn-success btn-xs" onclick="$('.get-bonus').val('<?php echo $bonus; ?>')"><i class="fa fa-check-circle-o"></i> Всё</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success btn-sm" onclick="save_bonus_schet('<?php echo $id; ?>')"><i class="fa fa-check"></i> Использовать бонусы</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function save_bonus_reckoning($connect){
	$id = $_POST["id"];
	$bonus = $_POST["bonus"];
	$turist = $connect->getOne("SELECT turist FROM reckoning WHERE id=?i", $id);
	$all_bonus = all_klient_bonus($connect, $turist);
	if($all_bonus < $bonus){
		return 0;
	}else{
		$bonus = (-1) * $bonus;
		$today = date("Y-m-d");
		$row = $connect->getRow("SELECT sum, id FROM bonus WHERE schet=?i AND sum < 0", $id);
		$id_bonus = $row["id"];
		if($id_bonus){
			$bonus+= $row["sum"];
			$connect->query("UPDATE bonus SET date=?s, sum=?i WHERE id=?i", $today, $bonus, $id_bonus);
		}else
			$connect->query("INSERT INTO bonus(date, schet, turist, sum, cause) VALUES (?s, ?i, ?i, ?i, 1)", $today, $id, $turist, $bonus);
		save_schet_to_history($connect, $id, "Использованы бонусы".$bonus);
		return 1;
	}
}

function show_form_upload_document(){
?>
<div class="modal fade">
	<div class="modal-dialog form-attach">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Загрузите файл</h4>
			</div>
			<div class="modal-body atach-file-info"></div>
			<div class="modal-footer center">
				<div class="div-download">
					<button type="button" class="btn btn-info download-file-button"><i class="fa fa-upload"></i> Загрузить</button>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}

function upload_document($connect){
	global $session_login;
	$id = $_POST["id"];
	$type = $_POST["type"];
	$file = str_replace("../", "", $_POST["file"]);
	if($type == "chat"){
		$name = uniqId()."_".basename($file);
		$connect->query("INSERT INTO chat_message(chat, user, attach) VALUES (?i, ?i, ?s)", $id, $session_login, $name);
		copy($file, "temp/chat/".$name);
		return write_message_chat($connect, $connect->insertId());
	}elseif($type == "news"){
		$name = basename($file);
		$type = exif_imagetype($file);
		if($type != 1 AND $type != 2 AND $type != 3){
			unlink($file);
			return 1;
		}
		$new = "temp/news/".$id."/".$name;
		if(file_exists($new))
			return 2;
		copy($file, $new);
		return $new;
	}else{
		$documents = json_decode($connect->getOne("SELECT doc_schet_san FROM reckoning WHERE id=?i", $id), TRUE);
		$document = $id."_".count($documents)."_".basename($file);
		$index = count($documents);
		$documents[$index]["doc"] = $document;
		$documents[$index]["type"] = $type;
		$connect->query("UPDATE reckoning SET doc_schet_san=?s WHERE id=?i", json_encode($documents), $id);
		copy($file, "temp/schet/".$document);
	}
	unlink($file);
}


function delete_schet_san($connect){
	$id = $_POST["id"];
	$index = $_POST["index"];
	$documents = json_decode($connect->getOne("SELECT doc_schet_san FROM reckoning WHERE id=?i", $id), TRUE);
	$file = $documents[$index]["doc"];
	unset($documents[$index]);
	sort($documents);
	$connect->query("UPDATE reckoning SET doc_schet_san=?s WHERE id=?i", json_encode($documents), $id);
	unlink("temp/schet/".$file);
}


function display_reward_schet($connect){
	$id = $_POST["id"];
	$row = get_reward_schet($connect, $id, "EACH");
?>
	<span>Сумма путевки: <?php echo $row["sum"]; ?></span>
	<span>Вознаграждение: <span style="color: green; display: inline; padding: 0px;"><?php echo $row["reward"]; ?></span></span>
	<?php if($row["agency"] != 0){ ?>
		<span>Комиссия: <span style="color: red; display: inline; padding: 0px;"><?php echo $row["agency"]; ?></span></span>
	<?php } ?>
	<?php if($row["bonus"] != 0){ ?>
		<span>Бонусы: <span style="color: red; display: inline; padding: 0px;"><?php echo $row["bonus"]; ?></span></span>
	<?php } ?>
	<?php if($row["discount"] != 0){ ?>
		<span>Скидка: <span style="color: red; display: inline; padding: 0px;"><?php echo $row["discount"]; ?></span></span>
	<?php } ?>
	<?php if($row["bank_com"] != 0){ ?>
		<span>Комиссия банка: <span style="color: red; display: inline; padding: 0px;"><?php echo $row["bank_com"]; ?></span> (<?php echo $row["bank_com_procent"]?>%)</span>
	<?php } ?>
	<?php if($row["correction"] > 0){ ?>
		<span>Поправка: <span style="color: green; display: inline; padding: 0px;"><?php echo $row["correction"]; ?></span></span>
	<?php } ?>
	<?php if($row["correction"] < 0){ ?>
		<span>Поправка: <span style="color: red; display: inline; padding: 0px;"><?php echo $row["correction"]; ?></span></span>
	<?php } ?>
	<hr />
	<span>Итог: <span style="color: green; display: inline; padding: 0px;"><?php echo $row["itog"]; ?></span></span>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_set_reward($connect){
	$id = $_POST["id"];
	$reward = $connect->getOne("SELECT reward FROM reckoning WHERE id=?i", $id);
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Вознаграждение заявки №<?php echo $id; ?></h4>
			</div>
			<div class="modal-body form-horizontal">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">Вознаграждение</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="reward" value="<?php echo $reward; ?>">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="set_reward('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function set_reward($connect){
	$id = $_POST["id"];
	$reward = $_POST["reward"];
	$connect->query("UPDATE reckoning SET reward=?s WHERE id=?i LIMIT 1", $reward, $id);
}

function remove_position($connect){
	$id = $_POST["id"];
	$reck = $_POST["reck"];
	$row = $connect->getRow("SELECT id_room, sum, number, note, type FROM position_reck WHERE id=?i", $id);
	$room = get_room($connect, $row["id_room"]);
	if($row["type"] == 1)
		$type = "за чел/сутки";
	if($type == 2)
		$type = "за номер";
	if($row["type"] == 3)
		$type = "за заезд";
	$note_history = "Удалена позиция. Номер ".$room."; Цена ".$row["sum"]."; Кол-во ".$row["number"]."; Прим. ".$row["note"]."; Тип: ".$type."";
	save_schet_to_history($connect, $reck, $note_history);
	check_status_booking_quota($connect, $reck, $id);
	$connect->query("DELETE FROM position_reck WHERE id=?i", $id);
	recalculation_sum($connect, $reck);
	change_arrival_date($connect, $reck);
}

function delete_bonus_form_reckoning($connect){
	$id = $_POST["id"];
	$connect->query("DELETE FROM bonus WHERE schet=?i AND sum<0", $id);
	save_schet_to_history($connect, $id, "Удаление бонусов из заявки");
}

function show_form_outweigh_reckoning($connect){
	$id = $_POST["id"];
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Перевесить заявку №<?php echo $id; ?> на агентство</h4>
			</div>
			<div class="modal-body form-horizontal outweight-reckoning">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label">ID агентства</label>
					<div class="col-sm-8">
						<input type="text" class="form-control agency-id" />
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="outweigh_reckoning_to_agency('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function outweigh_reckoning_to_agency($connect){
	$id = $_POST["id"];
	$agency = $_POST["agency"];
	if($connect->getOne("SELECT id FROM agency WHERE id=?i", $agency)){
		$connect->query("UPDATE reckoning SET turist=NULL, agency=?i WHERE id=?i", $agency, $id);
		return 1;
	}
	return FALSE;
}

function show_talk_reckoning($connect){
	$id = $_POST["id"];
	$talk = $connect->getOne("SELECT id FROM talk WHERE id_reck=?i", $id);
	if(!$id)
		return FALSE;
	$data = $connect->getAll("SELECT id FROM message_talk WHERE talk=?i ORDER BY date", $talk);
?>
	<div class="form-horizontal panel panel-default talk-client">
		<div class="panel-heading">
			<button type="button" class="btn btn-default btn-xs" onclick="view_schet(<?php echo $id; ?>)">&nbsp;<i class="fa fa-angle-double-left"></i>&nbsp;</button>
			Беседа по заявке <h3><?php echo $id; ?></h3>
		</div>
		<div class="panel-body talk-messages">
			<?php foreach($data as $row){ echo write_talk_message($connect, $row["id"]); } ?>
		</div>
		<div class="panel-footer text-right">
			<textarea class="form-control text-answer" style="margin-bottom: 10px"></textarea>
			<button type="button" class="btn btn-success btn-send-answer btn-sm" onclick="answer_client_question('<?php echo $talk; ?>', '<?php echo $id; ?>')"><i class="fa fa-envelope-o"></i> Отправить</button>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_menu_document($connect){
	global $id_rights;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id_obj, status, status_san FROM reckoning WHERE id=?i", $id);
	$status = $row["status"];
	$status_san = $row["status_san"];
	$id_obj = $row["id_obj"];

?>
	<span class="hr_label"><hr /></span>
	<span class="hr_label">Документы<hr /></span>
	<?php if($status > 1){ ?>
		<span onclick="show_bron_forma(<?php echo $id; ?>)">Лист бронирования</span>
		<span onclick="show_confirm(<?php echo $id; ?>)">Подтверждение бронирования</span>
	<?php } ?>
	<?php if($status > 2){ ?>
		<span onclick="show_bill(<?php echo $id; ?>, 2)">Счет</span>
		<span onclick="show_obmen(<?php echo $id; ?>)">Обменная путевка</span>
	<?php } ?>
	<?php if($status == 5 AND $id_obj == 22){ ?>
		<span onclick="show_napravlenie(<?php echo $id; ?>)">Направление</span>
	<?php } ?>
	<?php if($status == 6){ ?>
		<span onclick="show_cancel(<?php echo $id; ?>)">Аннуляция</span>
	<?php } ?>
	<span onclick="show_contract(<?php echo $id; ?>, 1)">Договор</span>
	<span onclick="show_contract(<?php echo $id; ?>, 2)">Договор туристский</span>
	<span class="hr_label"><hr /></span>
<?php
}

function show_menu_bid($connect){
	global $id_rights, $session_login;
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT status, holding_sum, status_san, date_z, agency, turist, status_agent, id_user, active, changes, id_user FROM reckoning WHERE id=?i", $id);
	$status = $row["status"];
	$status_san = $row["status_san"];
	$date_z = $row["date_z"];
	$agency = $row["agency"];
	$status_agent = $row["status_agent"];
	$active = $row["active"];
	$changes = $row["changes"];
	$turist = $row["turist"];
	$user = $row["id_user"];
	$cabinet = $connect->getOne("SELECT login FROM klient WHERE id=?i", $turist);
?>
	<?php if(($active == 0 OR $active == 1) AND $user){ ?>
		<span class="hr_label"><hr />Действия<hr /></span>
		<?php if($status == 1){ ?>
			<span onclick="show_bron(<?php echo $id; ?>, 2)">Лист бронирования</span>
		<?php } ?>
		<?php if($status == 2){ ?>
			<span onclick="show_bill(<?php echo $id; ?>)">Выставить счет</span>
		<?php } ?>
		<?php if(($status == 1 || $id_rights > 5) && $row['holding_sum'] == 0){ ?>
			<span onclick="reckoning_to_upsorted(<?php echo $id; ?>)">Удалить</span>
		<?php } ?>
		<?php if($status < 4){ ?>
			<span onclick="reckoning_put_aside(<?php echo $id; ?>)">Отложить</span>
		<?php } ?>
		<?php if(($status == 9) && $row['holding_sum'] == 0){ ?>
			<span onclick="reckoning_from_aside(<?php echo $id; ?>)">Вернуть в работу</span>
            <span onclick="reckoning_to_deposit(<?=$id;?>)">Перевести в депозит</span>
			<span onclick="reckoning_to_upsorted(<?php echo $id; ?>)">Удалить</span>
		<?php } ?>
		<?php if(($status == 4 OR $status == 3 OR $status == 7) AND $id_rights > 5){ ?>
			<span onclick="pay_schet(<?php echo $id; ?>)">Оплачено</span>
			<span onclick="prepay_schet(<?php echo $id; ?>)">Предоплата</span>
		<?php } ?>
		<?php if(($status == 4 OR $status == 3 OR $status == 7) AND $id_rights > 3 AND !$agency){ ?>
			<span onclick="pay_by_certificate(<?php echo $id; ?>)">Оплата сертификатом</span>
		<?php } ?>
		<?php if(($status == 3 OR $status == 4) AND $id_rights <= 4){ ?>
			<span onclick="request_pay_schet(<?php echo $id; ?>)">Запрос оплаты</span>
		<?php } ?>
		<?php if($status == 6 AND $id_rights > 3){ ?>
			<span onclick="return_cancel(<?php echo $id; ?>)">Вернуть в работу</span>
            <span onclick="reckoning_to_deposit(<?=$id;?>)">Перевести в депозит</span>
        <?php } ?>
        <?php if($status == 12 AND $id_rights > 4){ ?>
            <span onclick="return_cancel(<?php echo $id; ?>)">Вернуть в работу</span>
        <?php } ?>
		<?php if(($status == 7 OR $status == 8 OR $status == 10 OR $status == 11) AND $id_rights > 3){ ?>
			<span onclick="return_schet(<?php echo $id; ?>, <?php echo $status; ?>)">Вернуть</span>
		<?php } ?>
		<?php if($id_rights > 3){
			$check_payment = $connect->getOne("SELECT id FROM payment WHERE (type=1 OR type=2) AND schet=?i", $id);
			$check_return = $connect->getOne("SELECT id FROM return_query WHERE id_reck=?i", $id);
		?>
			<?php if($check_payment AND (!$check_return || $id_rights > 5)){ ?>
				<span onclick="return_oplata_query(<?php echo $id; ?>)">Заявка на возврат</span>
			<?php } ?>
			<?php if($check_payment){ ?>
				<span onclick="return_oplata(<?php echo $id; ?>)">Возврат</span>
			<?php } ?>
			<?php if($status == 5 AND $id_rights > 5){ ?>
				<span onclick="remove_payment(<?php echo $id; ?>)">Снять оплату</span>
				<span onclick="block_reckoning(<?php echo $id; ?>)">Заблокировать</span>
			<?php } ?>
		<?php } ?>
		<?php if($status == 5 AND !$agency){ ?>
			<span onclick="show_send_mail(<?php echo $id; ?>, 'obmen')">Выслать путевку</span>
		<?php } ?>
		<?php if($status == 5 AND $agency){ ?>
			<?php if(!$connect->getOne("SELECT id FROM agency_document WHERE id_reck=?i", $id) OR $connect->getOne("SELECT id FROM agency_document WHERE putevka=0 id_reck=?i", $id)){ ?>
				<span onclick="agency_document(<?php echo $id; ?>, 'putevka')">Путевка доступна</span>
			<?php } ?>
		<?php } ?>
		<?php if(($id_rights > 3) AND ($status != 6) AND ($status != 9)){ ?>
			<span onclick="review_cancel(<?php echo $id; ?>, 6)">Аннулировать</span>
            <span onclick="reckoning_to_deposit(<?php echo $id; ?>, 6)">Перевести в депозит</span>
        <?php } ?>
		<?php if(($id_rights > 3) AND ($status != 13)){ ?>
			<span onclick="review_reject(<?php echo $id; ?>, 13)">Перевести в отказную</span>
        <?php } ?>        
		<?php if($id_rights > 3 OR ($id_rights == 3 AND $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login) == $connect->getOne("SELECT office FROM users WHERE id=?i", $user)) OR $user == $session_login){ ?>
			<span onclick="change_manager(<?php echo $id; ?>)">Изменить менеджера</span>
		<?php } ?>
		<?php if($id_rights > 3 AND $changes){ ?>
			<span onclick="delete_changes_reckoning(<?php echo $id; ?>)">Принять изменения</span>
		<?php } ?>
		<?php if($active == 0){ ?>
			<span onclick="postponed_san_reckoning(<?php echo $id; ?>)">Отложить счет санатория</span>
		<?php }else{ ?>
			<span onclick="return_san_reckoning(<?php echo $id; ?>)">Вернуть счет санатория</span>
		<?php } ?>
		<span onclick="show_set_reward(<?php echo $id; ?>)">Установить вознаграждение</span>
		<span onclick="set_time_payment(<?php echo $id; ?>)">Установить сроки оплаты</span>
		<?php if($id_rights > 3){ ?>
			<span onclick="show_form_correction_reckoning(<?php echo $id; ?>)">Установить поправку</span>
		<?php } ?>
		<?php if($id_rights > 3 AND $agency){ ?>
			<span onclick="show_form_commission_reckoning(<?php echo $id; ?>)">Установить комиссию</span>
		<?php } ?>
		<?php if($id_rights <= 3 AND $status != 6){ ?>
			<span onclick="review_cancel(<?php echo $id; ?>, 8)">Запрос аннуляции</span>
		<?php } ?>
		<span class="hr_label"><hr /></span>
	<?php }else{ ?>
		<?php if($id_rights > 3 AND $active == 2){ ?>
			<span class="hr_label">Действия<hr /></span>
			<span onclick="unblock_reckoning(<?php echo $id; ?>)">Разблокировать</span>
			<span class="hr_label"><hr /></span>
		<?php } ?>
	<?php } ?>

	<?php if($agency AND ($status > 2 || (isset($_SESSION["change_to_agency"]["id"]) && $_SESSION["change_to_agency"]["id"] == $id))){ ?>
		<span class="hr_label">Отправка почты<hr /></span>
		<?php if($status > 2){ ?>
			<span onclick="show_send_mail(<?php echo $id; ?>, 'schet')">Отправить счет</span>
		<?php } ?>
		<?php if($status == 5){ ?>
			<span onclick="show_send_mail(<?php echo $id; ?>, 'obmen')">Отправить путевку</span>
		<?php } ?>
	<?php } ?>

	<?php if($status == 5 AND $agency){ ?>
		<span class="hr_label">Отчет агента<hr /></span>
		<span onclick="show_report_agency(<?php echo $id; ?>)">Отчет агента</span>
		<?php if($id_rights > 3 AND $status_agent == 0){ ?>
			<span onclick="sent_report_agent(<?php echo $id; ?>)">Отчет агента выслан</span>
		<?php } ?>
		<?php if($id_rights > 3 AND $status_agent == 1){ ?>
			<span onclick="received_report_agent(<?php echo $id; ?>)">Отчет агента получен</span>
		<?php } ?>
		<span class="hr_label"><hr /></span>
	<?php } ?>

	<?php if($id_rights > 5 AND $active == 0 AND $user){ ?>
			<span class="hr_label">Оплата в санаторий<hr /></span>
		<?php if($status_san == 2){ ?>
			<span onclick="pay_schet_san(<?php echo $id; ?>)">Оплачено в санаторий</span>
			<span onclick="return_schet_san(<?php echo $id; ?>)">Не оплачивать в санаторий</span>
		<?php } ?>
		<?php if($status_san == 6){ ?>
			<span onclick="prepay_schet_san(<?php echo $id; ?>)">Предоплата в санаторий</span>
			<span onclick="return_schet_san(<?php echo $id; ?>)">Не оплачивать в санаторий</span>
		<?php } ?>
		<?php if($status_san == 0 OR $status_san == 3){ ?>
			<span onclick="permit_pay_schet_san(<?php echo $id; ?>)">Разрешить оплату в сан</span>
			<span onclick="permit_prepay_schet_san(<?php echo $id; ?>)">Разрешить предоплату в сан</span>
		<?php } ?>
		<?php if($status_san == 5 AND $status != 5){ ?>
			<span onclick="permit_prepay_schet_san(<?php echo $id; ?>)">Разрешить предоплату в сан</span>
		<?php } ?>
		<?php if($status_san == 5 AND $status == 5){ ?>
			<span onclick="permit_pay_schet_san(<?php echo $id; ?>)">Разрешить оплату в сан</span>
		<?php } ?>
			<span class="hr_label"><hr /></span>
	<?php } ?>

	<?php if(!$user OR $active == 3){ ?>
		<hr /><span class="hr_label">Новая заявка<hr /></span>
		<?php if($active != 3 && $row['holding_sum'] == 0){ ?>
			<span onclick="reckoning_to_upsorted(<?php echo $id; ?>)">Удалить</span>
		<?php } ?>
		<?php if($id_rights >= 5 AND $active == 3 && $row['holding_sum'] == 0){ ?>
			<span onclick="delete_reckoning(<?php echo $id; ?>)">Удалить навсегда</span>
		<?php } ?>
		<?php if($active == 3){ ?>
			<span onclick="reestablish_reckoning(<?php echo $id; ?>)">Восстановить</span>
		<?php } ?>
		<?php if($id_rights > 3 AND $active == 0){ ?>
			<span onclick="change_manager(<?php echo $id; ?>)">Назначить</span>
		<?php }elseif($active == 0){ ?>
			<span onclick="assign_reckoning(<?php echo $id; ?>)">Забрать</span>
		<?php } ?>
		<span class="hr_label"><hr /></span>
	<?php } ?>

	<span onclick="check_pechat()"><input type="checkbox"  id="pechat_check" />Штамп</span>
	<span onclick="check_writing()"><input type="checkbox" id="writing_check" />Бланк</span>

<?php
}

?>
