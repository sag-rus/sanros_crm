<?php

function show_payment_report_menu(){
	ob_start();
?>
<div class="btn-group small-menu-report">
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-all" onclick="payment_report_general()"><i class="fa fa-tasks"></i> Общий</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-month" onclick="payment_report_month()"><i class="fa fa-calendar"></i> По месяцам</button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn btn-default btn-sm btn-return" onclick="return_query_report()"><i class="fa fa-mail-reply"></i> Ожидание возврата</button>
	</div>
</div>
<div id="panel" style="margin-top: 10px"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function general_payment_report(){
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-2 control-label">Дата оплаты</label>
			<div class="col-sm-3">
				<input type="text" class="form-control datepicker" id="date_opl" />
			</div>
			<div class="col-sm-3">
				<input type="text" class="form-control datepicker" id="date_opl2" />
			</div>
			<label class="col-sm-2 control-label">Способ оплаты</label>
			<div class="col-sm-2">
				<select class="form-control" id="method_opl">
					<option value="">Не выбран</option>
					<option value="2">Наличный</option>
					<option value="1">Безналичный</option>
					<option value="3">Сертификатом</option>
					<option value="4">На месте</option>
					<option value="5">Банковской картой</option>
                    <option value="5-1">-- Банковской картой через личный кабинет</option>
                    <option value="5-2">----- Банковской картой с холдированием</option>
                    <option value="5-3">----- Банковской картой без холдирования</option>
                    <option value="5-4">-- Банковской картой через терминал</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">Тип оплаты</label>
			<div class="col-sm-4">
				<select class="form-control" id="type_opl">
					<option value="">Не выбран</option>
					<option value="1">Предоплата</option>
					<option value="2">Оплата</option>
                    <option value="3">Доплата</option>
				</select>
			</div>
			<label class="col-sm-2 control-label">Оплата</label>
			<div class="col-sm-4">
				<select class="form-control" id="type_pay">
					<option value="1">Клиента</option>
					<option value="2">В санаторий</option>
					<option value="3">Возврат</option>
					<option value="4">Все</option>
				</select>
			</div>
		</div>
        <div class="form-group">
            <div class="col-sm-2">
                <input type="checkbox" id="show-holdings" class="pull-right">
            </div>
            <label class="col-sm-4 control-label text-left" style="padding-top: 0;">Показывать замороженные на данный момент платежи</label>
        </div>
	</div>
	<div class="panel-footer" style="text-align: right">
		<button type="button" class="btn btn-success btn-sm" onclick="filter_payment()"><i class="fa fa-search"></i> Применить</button>
		<button type="button" class="btn btn-warning btn-sm btn-hide" style="display: none" onclick="filter_payment_update()"><i class="fa fa-spinner"></i> Обновить</button>
		</div>
	</div>
</div>
<div id="filter_res"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function month_payment_report($connect){
	global $array_month;
	$month_select = "";
	foreach($array_month as $key => $month)
		$month_select.= "<option value='".$key."'>".$month."</option>";
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-2 control-label">Месяц</label>
			<div class="col-sm-4">
				<select class="form-control" id="month">
					<option value="">не выбран</option>
					<?php echo $month_select; ?>
				</select>
			</div>
			<label class="col-sm-2 control-label">Год</label>
			<div class="col-sm-4">
				<select class="form-control" id="year">
					<option value="">не выбран</option>
                      <?php for($year = 2013; $year<= date("Y"); $year++){ ?>
                          <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                      <?php } ?>
				</select>
			</div>
		</div>
		<div class="form-group form-group-margin">
			<label class="col-sm-2 control-label">Способ оплаты</label>
			<div class="col-sm-4">
				<?php echo select_payment_method(); ?>
			</div>
			<label class="col-sm-2 control-label">Регион</label>
			<div class="col-sm-4">
				<?php echo get_select_table($connect, "region", "active=0", "", "regions", 1); ?>
			</div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<button type="button" class="btn btn-success btn-sm" onclick="filter_payment_report_month()"><i class="fa fa-search"></i> Применить</button>
	</div>
</div>
<div class="payment-result"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function filter_payment($connect){
	global $session_login;
	$office_color = array(1 => "#FFF", 2 => "#AAA6FF", 3 => "#98F58B", 4 => "#FFB6D3");
	if((isset($_POST["type"]) AND $_POST["type"] == "update") AND $_COOKIE["filter"]){
		$cook = $_COOKIE['filter'];
		$mas = explode(";;;", $cook);
		foreach($mas as $value){
			$arr = explode("===", $value);
			if($arr[0] == "date_opl")
				$_POST["date_opl"] = $arr[1];
			elseif($arr[0] == "date_opl2")
				$_POST["date_opl2"] = $arr[1];
			elseif($arr[0] == "method_opl")
				$_POST["method_opl"] = $arr[1];
			elseif($arr[0] == "type_opl")
				$_POST["type_opl"] = $arr[1];
			elseif($arr[0] == "type_pay")
				$_POST["type_pay"] = $arr[1];
		}
	}
	$date_opl = $_POST["date_opl"];
	$date_opl2 = $_POST["date_opl2"];
	$method_opl = (int)$_POST["method_opl"];
	$type_opl = $_POST["type_opl"];
	$type_pay_tbl = $_POST["type_pay"];
	$showHoldings = (int)$_POST["show_holdings"];
	$cardPaymentTypes = (int)$_POST["card_payment_types"];
	$count = $_POST;
	$id_file = array();
	$str = "";
	$html = "";
	foreach($_POST as $key => $value){
		$count--;
		$str.= $key."===".$value;
		if($str)
			$str.= ";;;";
	}
	SetCookie("filter", $str);

	$zapros_for_mysql = "";
	$array = array("all_pay" => 0, "all_num" => 0, "pay_bez" => 0, "pay_nal" => 0, "pay_other" => 0, "num_bez" => 0, "num_nal" => 0, "num_other" => 0, "num_prepay" => 0, "prepay" => 0, "num_feepay" => 0, "feepay" => 0, "num_pay" => 0, "pay" => 0, "num_place" => 0, "pay_place" => 0, "num_card" => 0, "pay_card" => 0, "num_san" =>0, "pay_san" => 0, "num_cert" => 0, "pay_cert" => 0, "num_ret" => 0, "return" => 0, "reward" => 0);
	$office = $connect->getAll("SELECT id, name FROM office");
	foreach($office as $row){
		$id_office = $row["id"];
		$array["office"][$id_office]["name"] = $row["name"];
		$array["office"][$id_office]["all_num"] = 0;
		$array["office"][$id_office]["all_pay"] = 0;
		$array["office"][$id_office]["num_bez"] = 0;
		$array["office"][$id_office]["pay_bez"] = 0;
		$array["office"][$id_office]["num_nal"] = 0;
		$array["office"][$id_office]["pay_nal"] = 0;
		$array["office"][$id_office]["num_cert"] = 0;
		$array["office"][$id_office]["pay_cert"] = 0;
		$array["office"][$id_office]["num_place"] = 0;
		$array["office"][$id_office]["pay_place"] = 0;
		$array["office"][$id_office]["num_card"] = 0;
		$array["office"][$id_office]["pay_card"] = 0;
		$array["office"][$id_office]["num_san"] = 0;
		$array["office"][$id_office]["pay_san"] = 0;
		$array["office"][$id_office]["num_prepay"] = 0;
		$array["office"][$id_office]["prepay"] = 0;
		$array["office"][$id_office]["num_pay"] = 0;
		$array["office"][$id_office]["pay"] = 0;
		$array["office"][$id_office]["num_ret"] = 0;
		$array["office"][$id_office]["return"] = 0;
		$array["office"][$id_office]["reward"] = 0;
		$array["office"][$id_office]["all_reward"] = 0;
	}
	if($date_opl != ""){
	    $date_opl_t = strtotime($date_opl);
		if($date_opl2) {
		  $date_opl2_t = strtotime($date_opl2)+86400;
		  if($showHoldings) {
            $zapros_for_mysql .= " ((`payment`.`processed` IS NULL AND `payment`.`date` >= '$date_opl' AND `payment`.`date` <= '$date_opl2' AND `payment`.`status` = 2) OR (`payment`.`processed` IS NOT NULL AND `payment`.`processed` >= '".$date_opl_t."' AND `payment`.`processed` < '".$date_opl2_t."') OR (`payment`.`status` = 1 AND `payment`.`created` >= '".$date_opl_t."' AND `payment`.`created` < '".$date_opl2_t."')) ";
          }
          else {
            $zapros_for_mysql .= " ((`payment`.`processed` IS NULL AND `payment`.`date` >= '$date_opl' AND `payment`.`date` <= '$date_opl2' AND `payment`.`status` = 2) OR (`payment`.`processed` IS NOT NULL AND `payment`.`processed` >= '".$date_opl_t."' AND `payment`.`processed` < '".$date_opl2_t."')) ";
          }
        }
		else {
          $date_opl2_t = $date_opl_t+86400;
          if($showHoldings) {
            $zapros_for_mysql .= "((`payment`.`processed` IS NULL AND `payment`.`date` = '$date_opl' AND `payment`.`status` = 2)  OR (`payment`.`processed` IS NOT NULL AND `payment`.`processed` >= '".$date_opl_t."' AND `payment`.`processed` < '".$date_opl2_t."') OR (`payment`.`status` = 1 AND `payment`.`created` >= '".$date_opl_t."' AND `payment`.`created` < '".$date_opl2_t."')) ";
          }
          else {
            $zapros_for_mysql .= "((`payment`.`processed` IS NULL AND `payment`.`date` = '$date_opl' AND `payment`.`status` = 2)  OR (`payment`.`processed` IS NOT NULL AND `payment`.`processed` >= '".$date_opl_t."' AND `payment`.`processed` < '".$date_opl2_t."')) ";
          }
        }
	}
	elseif (!$showHoldings) {
      $zapros_for_mysql .= "(`payment`.`status` != 1)";
    }

	if($method_opl != ""){
		if($zapros_for_mysql)
			$zapros_for_mysql.= " AND ";
		$zapros_for_mysql.= " pay_method='$method_opl' ";
		if($cardPaymentTypes) {
          if($zapros_for_mysql)
            $zapros_for_mysql.= " AND ";
          if($cardPaymentTypes === 1)
              $zapros_for_mysql .= " `payment`.`terminal` = 0 ";
          elseif ($cardPaymentTypes === 2)
              $zapros_for_mysql .= " `payment`.`created` != `payment`.`processed` AND `payment`.`terminal` = 0";
          elseif ($cardPaymentTypes === 3)
              $zapros_for_mysql .= " `payment`.`created` = `payment`.`processed` AND `payment`.`terminal` = 0";
          elseif ($cardPaymentTypes === 4)
              $zapros_for_mysql .= " `payment`.`terminal` = 1 ";
        }
	}

	if($type_opl != ""){
		if($zapros_for_mysql)
			$zapros_for_mysql.= " AND ";
		if($type_opl == 1)
			$zapros_for_mysql.= "(`payment`.`type`=1 OR `payment`.`type`=3)";
		elseif($type_opl == 2)
			$zapros_for_mysql.= "(`payment`.`type`=2 OR `payment`.`type`=4)";
		elseif ($type_opl == 3)
            $zapros_for_mysql.= "(`payment`.`type`=6)";
	}
	if($type_pay_tbl == 1){
		$zapros_for_mysql.= " AND (`payment`.`type`=1 OR `payment`.`type`=2 OR `payment`.`type`=6)";
		$th_pay = "<th width='70'>Способ<br />платежа</th>";
	}elseif($type_pay_tbl == 2){
		$zapros_for_mysql.= " AND (`payment`.`type`=3 OR `payment`.`type`=4)";
		$th_pay = "<th width='70'>Номер<br />плат.пор.</th>";
	}elseif($type_pay_tbl == 3){
		$zapros_for_mysql.= " AND (`payment`.`type`=5)";
		$th_pay = "<th width='70'>Способ<br />платежа</th><th width='70'>Номер<br />плат.пор.</th>";
	}else
		$th_pay = "<th width='70'>Способ<br />платежа</th><th width='70'>Номер<br />плат.пор.</th>";
	if(mb_strlen($zapros_for_mysql) > 0)
	    $zapros_for_mysql .= " AND `payment`.`status` != 0 AND `payment`.`pay_method` != 3 AND `payment`.`class` != 'cert'";
	else
        $zapros_for_mysql .= " `payment`.`status` != 0 AND `payment`.`pay_method` != 3 AND `payment`.`class` != 'cert'";

	$zapros_for_mysql_cond = $zapros_for_mysql;
	$zapros_for_mysql = "SELECT `payment`.`id`, `payment`.`processed`, DATE_FORMAT(payment.date, '%d.%m.%Y') as date, payment.sum, `users`.`office`, `payment`.`status` AS payment_status, `payment`.`type`, payment.pay_method, payment.pay_number, payment.schet, payment.class, payment.bank_com, reckoning.rest, reckoning.id_obj, reckoning.sum as sum_reck, reckoning.id_user, reckoning.agency, reckoning.id_obj, reckoning.turist, DATE_FORMAT(reckoning.date_z, '%d.%m.%Y') as date_z, reckoning.status, reckoning.status_san FROM payment LEFT JOIN reckoning ON reckoning.id=payment.schet LEFT JOIN users ON `reckoning`.`id_user`=`users`.`id` WHERE ".$zapros_for_mysql." ORDER BY payment.id";

	$data = $connect->getAll($zapros_for_mysql);

	$pay_groups = [];
	$all_reward = 0;
	foreach($data as $row){
		$all_fio = "";
		$id = $row["schet"];
		$payment_id = $row['id'];

		if(in_array($row['type'],[1,2,6])) {
          if (!isset($pay_groups[$id])) {
            $pay_groups[$id] = [$row['id']];
          }
          else {
            $pay_groups[$id][] = $row['id'];
          }
        }
        elseif ($row['type'] == 5) {
		    //$reward -=
        }

		$id_file[] = $id;
		$class = $row["class"];
		$date = $row["date"];
		if(!is_null($row['processed']))
		    $date = date("d.m.Y",$row["processed"]);

		$date_z = $row["date_z"];
		$sum = $row["sum"];
		$status = $row["status"];
		$status_san = $row["status_san"];
		$type_pay = $row["type"];
		$sum_reck = $row["sum_reck"];
		$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
		$type_opl = $row["pay_method"];
		$pay_number = $row["pay_number"];
		$bank_com = $row["bank_com"];
		$office_pay = $row["office"];
		if($type_opl == 5){
			$office_pay = $connect->getOne("SELECT office FROM users WHERE id=?i", $row["id_user"]);
		}
		$object = get_object($connect, $row["id_obj"]);
		if(!$pay_number)
			$pay_number = "-";
		if($row["agency"]){
			$param = "agency";
			$type = $row["agency"];
			$array_klient = explode(",", $row["rest"]);
		}else{
			$param = "turist";
			$array_klient = explode(",", $row["rest"]);
			$type = $row["turist"];
		}
		if($row["rest"] == "" AND !$surname)
			$all_fio = "Отдыхающий не указан";
		$array_klient = array_diff($array_klient, array(""));
		foreach($array_klient as $tur){
			$turist = $connect->getRow("SELECT name, surname, otch FROM klient WHERE id=?i", $tur);
			$fio = $turist["surname"]." ".$turist["name"]." ".$turist["otch"];
			if($fio AND $all_fio)
				$all_fio.= "<br />";
			$all_fio.= $fio;
		}
		//if(($status == 4 AND $type_pay == 1) OR ($status == 5 AND $type_pay == 2) OR ($status_san == 3 AND $type_pay == 3) OR ($type_pay == 4) OR ($type_pay == 5)){
			$array["all_num"]++;
			$array["all_pay"]+= $sum;
			if($type_pay == 2 OR $type_pay == 4)
				$array["reward"]+= 0;
			$color = "";
			if($office_pay > 0){
				$array["office"][$office_pay]["all_num"]++;
				$array["office"][$office_pay]["all_pay"]+= $sum;
				$color = $office_color[$office_pay];
			}

			if($row['payment_status'] == 1) {
              $color = '#ffeb3b85';
            }

			if($type_opl == 1){
				$array["num_bez"]++;
				$array["pay_bez"]+= $sum;
				if($office_pay > 0){
					$array["office"][$office_pay]["num_bez"]++;
					$array["office"][$office_pay]["pay_bez"]+= $sum;
				}
				$type_opl_text = "Безнал";
			}elseif($type_opl == 2){
				$array["num_nal"]++;
				$array["pay_nal"]+= $sum;
				if($office_pay > 0){
					$array["office"][$office_pay]["num_nal"]++;
					$array["office"][$office_pay]["pay_nal"]+= $sum;
				}
				$type_opl_text = "Нал";
			}elseif($type_opl == 3){
				$array["num_cert"]++;
				$array["pay_cert"]+= $sum;
				$array["office"][$office_pay]["num_cert"]++;
				$array["office"][$office_pay]["pay_cert"]+= $sum;
				$type_opl_text = "Серт";
			}elseif($type_opl == 4){
				$array["num_place"]++;
				$array["pay_place"]+= $sum;
				if($office_pay > 0){
					$array["office"][$office_pay]["num_place"]++;
					$array["office"][$office_pay]["pay_place"]+= $sum;
				}
				$type_opl_text = "На месте";
			}elseif($type_opl == 5){
				$array["num_card"]++;
				if($sum <= 100)
					$sum = add_null($sum - 3.5);
				else
					$sum = add_null(((100 - $bank_com)/100) * $sum);
				$array["pay_card"]+= $sum;
				if($office_pay > 0){
					$array["office"][$office_pay]["num_card"]++;
					$array["office"][$office_pay]["pay_card"]+= $sum;
				}
				$type_opl_text = "Банк.карт.";
			}
			if($type_pay == 3 OR $type_pay == 4){
				$type_opl_text = "-";
				$array["num_san"]++;
				$array["pay_san"]+= $sum;
				if($office_pay > 0){
					$array["office"][$office_pay]["num_san"]++;
					$array["office"][$office_pay]["pay_san"]+= $sum;
				}
			}
			if($type_pay == 1 OR $type_pay == 3){
				$type_pay_text = "Предоплата";
				$array["num_prepay"]++;
				$array["prepay"]+= $sum;
				$array["office"][$office_pay]["num_prepay"]++;
				$array["office"][$office_pay]["prepay"]+= $sum;
			}elseif($type_pay == 2 OR $type_pay == 4){
				$type_pay_text = "Оплата";
				$array["num_pay"]++;
				$array["pay"]+= $sum;
				if($office_pay > 0){
					$array["office"][$office_pay]["num_pay"]++;
					$array["office"][$office_pay]["pay"]+= $sum;
				}
			}
			elseif ($type_pay == 6) {
              $type_pay_text = "Доплата";
              $array["num_feepay"]++;
              $array["feepay"]+= $sum;
              if($office_pay > 0){
                if(!isset($array["office"][$office_pay]["num_feepay"]))
                    $array["office"][$office_pay]["num_feepay"] = 0;

                $array["office"][$office_pay]["num_feepay"]++;

                if(!isset($array["office"][$office_pay]["feepay"]))
                  $array["office"][$office_pay]["feepay"] = 0;

                $array["office"][$office_pay]["feepay"]+= $sum;
              }
            }
			elseif($type_pay == 5){
				$type_pay_text = "Возврат";
				$array["num_ret"]++;
				$array["return"]+= $sum;
				if($office_pay > 0){
					$array["office"][$office_pay]["num_ret"]++;
					$array["office"][$office_pay]["return"]+= $sum;
				}
			}
			$bg_class = "";
			$style = "";
			if($sum > $sum_reck AND $type_pay != 5)
				$bg_class = " alert alert-danger ";
			$func = "onclick='show_turist(\"".$type."\", \"".$id."\", \"".$param."\")'";
			if($class == "cert"){
				$date_z = "";
				$object = "сертификат";
				$sum_reck = "";
				$color = "";
				$manager = "";
				$tur = $connect->getOne("SELECT klient FROM zapros_for_mysql WHERE id=?i", $id);
				$all_fio = select_name_klient($connect, $tur);
				$func = "";
				$id = "";
			}


			//блок расчета прибыли по платежу - начало
            if($class == "cert")
                $pay_reward = $row['sum'];
            else
                $pay_reward = 0;
            $all_pays = $connect->getAll("SELECT id FROM payment WHERE (type = 1 OR type = 2 OR type = 6) AND schet = ?i AND `payment`.`status` != 0", $id);
            $all_pays_count = count($all_pays);
              $pay_ar1 = [];
              $pay_ar2 = [];
              foreach ($all_pays as $all_pay_index => $all_pays_el)
              {
                if($all_pays_el['id'] == $payment_id) {
                  if($all_pays_count-1 != $all_pay_index)
                    $pay_ar1[] = $all_pays_el['id'];
                  else {
                    $pay_ar2[] = $all_pays_el['id'];
                  }
                }
              }

              if(count($pay_ar1) > 0) {
                $test_reward = get_reward_schet($connect, $id, "", TRUE, FALSE, $pay_ar1);
                $pay_reward += $test_reward;
              }

              if(count($pay_ar2) > 0) {

                $test_reward = get_reward_schet($connect, $id, "", TRUE, TRUE, $pay_ar2,$all_pays_count != (count($pay_ar1)+count($pay_ar2)));
                $pay_reward += $test_reward;
              }
              //echo $id." ".$all_pays_count." ".count($pay_ar1)." ".count($pay_ar2)." ".$pay_reward."<br />";
            //блок расчета прибыли по платежу - конец

            $all_reward += $pay_reward;
              if($office_pay > 0){
                $array["office"][$office_pay]["all_reward"]+= $pay_reward;
              }


			$html.= "<tr class='".$bg_class."' ".$func." style='background: ".$color."!important;'>";
			$html.= "<td valign='top' align='center'>".$id."</td>";
			$html.= "<td valign='top'>".$all_fio."</td>";
			$html.= "<td valign='top'>".$object."</td>";
			$html.= "<td valign='top' style='text-align: center;'>".$date."</td>";
			$html.= "<td valign='top' style='text-align: center;'>".$date_z."</td>";
			$html.= "<td valign='top' style='text-align: center;'>".$sum_reck."</td>";
			$html.= "<td valign='top' style='text-align: center;'>".$sum."</td>";
			$html.= "<td valign='top' style='text-align: center;'>".$type_pay_text."</td>";
            $html.= "<td valign='top' style='text-align: center;'>".$pay_reward."</td>";
			$html.= "<td valign='top' style='text-align: center;'>".$manager."</td>";
			if($type_pay_tbl == 1 OR $type_pay_tbl == 3 OR $type_pay_tbl == 4)
				$html.= "<td valign='top' style='text-align: center;'>".$type_opl_text."</td>";
			if($type_pay_tbl == 2 OR $type_pay_tbl == 3 OR $type_pay_tbl == 4)
				$html.= "<td valign='top' style='text-align: center;'>".$pay_number."</td>";
			$html.= "</tr>";
		//}
	}

	foreach ($pay_groups as $reck_id => $pay_array) {
	    $all_pays = $connect->getAll("SELECT `id`, `office` FROM payment WHERE (type = 1 OR type = 2 OR type = 6) AND schet = ?i AND `payment`.`status` != 0", $reck_id);
	    $office_g = $connect->getOne("SELECT `users`.`office` FROM `reckoning` LEFT JOIN `users` ON `reckoning`.`id_user`=`users`.`id` WHERE `reckoning`.`id` = ?i", $reck_id);
	    $all_pays_count = count($all_pays);
	    /*if($all_pays_count === count($pay_array)) {
          $array["reward"] += get_reward_schet($connect, $reck_id, "", TRUE);
        }
        else {*/
	        $pay_ar1 = [];
	        $pay_ar2 = [];
	        foreach ($all_pays as $all_pay_index => $all_pays_el)
	        {
	            if(in_array($all_pays_el['id'],$pay_array)) {
	                if($all_pays_count-1 != $all_pay_index)
	                    $pay_ar1[] = $all_pays_el['id'];
	                else {
	                    $pay_ar2[] = $all_pays_el['id'];
                    }
                }
            }
            $reck_pay_reward = 0;
            if(count($pay_ar1) > 0) {
              $test_reward = get_reward_schet($connect, $reck_id, "", TRUE, FALSE, $pay_ar1);
              $reck_pay_reward += $test_reward;
            }

	        if(count($pay_ar2) > 0) {
	          $test_reward = get_reward_schet($connect, $reck_id, "", TRUE, TRUE, $pay_ar2,$all_pays_count != (count($pay_ar1)+count($pay_ar2)));
	          $reck_pay_reward += $test_reward;
            }
        //}
      $array['reward'] += $reck_pay_reward;
	  if($office_g) {
        $array["office"][$office_g]["reward"] += $reck_pay_reward;
      }
      //echo " <br />".$reck_id." ".$reck_pay_reward."<br />";
    }

	if(!$html)
		return "<div class='alert alert-info'><i class='fa fa-info-circle'></i> Ничего не найдено</div>";
	ob_start();
?>
	<div class="form-horizontal">
		<div class="form-group panel panel-success" style="margin: 0; margin-bottom: 5px">
			<div class="panel-heading"><i class="fa fa-users"></i> По всем офисам</div>
			<div class="panel-body">
				<div class="col-sm-12">
					Всего платежей
					<?php echo $array["all_num"]; ?> на сумму <?php echo number_format($array["all_pay"], 2, ",", " "); ?>
				</div>
				<div class="clearfix"></div>
				<hr />
				<?php if($array["num_nal"]){ ?>
				<div class="col-sm-6">
					Наличным способом
					<?php echo $array["num_nal"]; ?> на сумму <?php echo number_format($array["pay_nal"], 2, ',', ' '); ?>
				</div>
				<?php } ?>
				<?php if($array["num_bez"]){ ?>
				<div class="col-sm-6">
					Безналичным способом <?php echo $array["num_bez"]; ?> на сумму <?php echo number_format($array["pay_bez"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($array["num_san"]){ ?>
				<div class="col-sm-6">
					Оплаты в санаторий <?php echo $array["num_san"]; ?> на сумму <?php echo number_format($array["pay_san"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($array["num_cert"]){ ?>
				<div class="col-sm-6">
					Подарочным сертификатом <?php echo $array["num_cert"]; ?> на сумму <?php echo number_format($array["pay_cert"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($array["num_place"]){ ?>
				<div class="col-sm-6">
					Оплата на месте <?php echo $array["num_place"]; ?> на сумму <?php echo number_format($array["pay_place"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($array["num_card"]){ ?>
				<div class="col-sm-6">
					Оплата банковской картой <?php echo $array["num_card"]; ?> на сумму <?php echo number_format($array["pay_card"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<div class="clearfix"></div>
				<hr />
				<?php if($array["num_ret"]){ ?>
				<div class="col-sm-6">
					Возврат <?php echo $array["num_ret"]; ?> на сумму <?php echo number_format($array["return"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($array["num_prepay"]){ ?>
				<div class="col-sm-4">
					Предоплата <?php echo $array["num_prepay"]; ?> на сумму <?php echo number_format($array["prepay"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($array["num_pay"]){ ?>
				<div class="col-sm-4">
					Оплата <?php echo $array["num_pay"]; ?> на сумму <?php echo number_format($array["pay"], 2, ",", " "); ?>
				</div>
				<?php } ?>

				<?php if($array["num_feepay"]){ ?>
				<div class="col-sm-4">
					Доплата <?php echo $array["num_feepay"]; ?> на сумму <?php echo number_format($array["feepay"], 2, ",", " "); ?>
				</div>
				<?php } ?>

				<div class="clearfix"></div>
				<hr />
				<div class="col-sm-6">
					Общее вознаграждение на сумму <?php echo number_format($array["reward"], 2, ",", " ")." (сумма прибыли по каждому платежу ".number_format($all_reward, 2, ",", " ").") "; ?>
				</div>
			</div>
		</div>
	<?php foreach($array["office"] as $office => $data){
		if($data["all_num"] > 0){
	?>
		<div class="form-group panel panel-info" style="margin: 0; margin-bottom: 5px">
			<div class="panel-heading"><i class="fa fa-home"></i> Офис <?php echo $data["name"]; ?></div>
			<div class="panel-body">
				<div class="col-sm-12">
					Всего платежей
					<?php echo $data["all_num"]; ?> на сумму <?php echo number_format($data["all_pay"], 2, ",", " "); ?>
				</div>
				<div class="clearfix"></div>
				<hr />
				<?php if($data["num_nal"]){ ?>
				<div class="col-sm-6">
					Наличным способом
					<?php echo $data["num_nal"]; ?> на сумму <?php echo number_format($data["pay_nal"], 2, ',', ' '); ?>
				</div>
				<?php } ?>
				<?php if($data["num_bez"]){ ?>
				<div class="col-sm-6">
					Безналичным способом <?php echo $data["num_bez"]; ?> на сумму <?php echo number_format($data["pay_bez"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($data["num_san"]){ ?>
				<div class="col-sm-6">
					Оплаты в санаторий <?php echo $data["num_san"]; ?> на сумму <?php echo number_format($data["pay_san"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($data["num_cert"]){ ?>
				<div class="col-sm-6">
					Подарочным сертификатом <?php echo $data["num_cert"]; ?> на сумму <?php echo number_format($data["pay_cert"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($data["num_place"]){ ?>
				<div class="col-sm-6">
					Оплата на месте <?php echo $data["num_place"]; ?> на сумму <?php echo number_format($data["pay_place"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($data["num_card"]){ ?>
				<div class="col-sm-6">
					Оплата банковской картой <?php echo $data["num_card"]; ?> на сумму <?php echo number_format($data["pay_card"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<div class="clearfix"></div>
				<hr />
				<?php if($data["num_ret"]){ ?>
				<div class="col-sm-6">
					Возврат <?php echo $data["num_ret"]; ?> на сумму <?php echo number_format($data["return"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($data["num_prepay"]){ ?>
				<div class="col-sm-4">
					Предоплата <?php echo $data["num_prepay"]; ?> на сумму <?php echo number_format($data["prepay"], 2, ",", " "); ?>
				</div>
				<?php } ?>
				<?php if($data["num_pay"]){ ?>
				<div class="col-sm-4">
					Оплата <?php echo $data["num_pay"]; ?> на сумму <?php echo number_format($data["pay"], 2, ",", " "); ?>
				</div>
				<?php } ?>

                <?php if(isset($data["num_feepay"]) && $data["num_feepay"]){ ?>
                  <div class="col-sm-4">
                      Доплата <?php echo $data["num_feepay"]; ?> на сумму <?php echo number_format($data["feepay"], 2, ",", " "); ?>
                  </div>
                <?php } ?>

                <?php if($data["reward"]){ ?>
                    <div class="clearfix"></div>
                    <hr />
                    <div class="col-sm-6">
                        Общее вознаграждение по офису на сумму <?php echo number_format($data["reward"], 2, ",", " ")." (сумма прибыли по каждому платежу ".number_format($data['all_reward'], 2, ",", " ").") "; ?>
                    </div>
                <?php } ?>

			</div>
		</div>
		<?php } ?>
	<?php } ?>
		<div class="text-right" style="margin: 10px">
			<a class="btn btn-success btn-sm" href="document.php?func=save_file_1C_sync&id=<?php echo implode('-', $id_file); ?>" target="_blank"><i class="fa fa-file-text-o"></i> Сохранить в файл</a>
		</div>
	</div>
	<table class="tbl-filter table table-hover table-condensed">
	<thead>
	<tr id="filter_tr">
		<th width="25">№</th>
		<th width="150">ФИО</th>
		<th width="100">Объект</th>
		<th width="80" class="{dateFormat: 'ddmmyyyy'}">Дата платежа</th>
		<th width="80" class="{dateFormat: 'ddmmyyyy'}">Дата заезда</th>
		<th width="80">Сумма путевки</th>
		<th width="80">Сумма платежа</th>
		<th width="80">Тип платежа</th>
        <th width="80">Прибыль</th>
		<th width="90">Менеджер</th>
		<?php echo $th_pay; ?>
	</tr>
	</thead>
	<tbody>
		<?php echo $html; ?>
	</tbody>
	</table>
<?php
	$html = ob_get_clean();
	return $html;
}

function filter_payment_month($connect){
	global $array_week, $array_month;
	$year = $_POST["year"];
	$month = $_POST["month"];
	$region = $_POST["region"];
	$method = $_POST["method"];
	$query = "";
	if($method)
		$query = "payment.pay_method=".$method;
	if($region){
		if($query)
			$query.= " AND ";
		$query.= get_objects_by_region($connect, $region, "reckoning", "");
	}
	if($query)
		$query = " AND ".$query;
	$html = "";
	if($month){
		$max = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		for($day = 1; $day <= $max; $day++){
			$date = $year."-".$month."-".$day;
			$date1t = strtotime($date);
			$date2t = $date1t+86400;
			$array = array("sum_opl" => 0, "count_opl" => 0, "sum_opl_san" => 0, "count_opl_san" => 0);
			$data = $connect->getAll("SELECT payment.sum FROM payment, reckoning WHERE payment.schet=reckoning.id AND (payment.type=1 OR payment.type=2) AND ((`payment`.`processed` IS NULL AND `payment`.`date`=?s AND `payment`.`status` = 2) OR (`payment`.`processed` IS NOT NULL AND `payment`.`processed`>= ?i AND `payment`.`processed`< ?i AND `payment`.`status` = 2)) AND `payment`.`status` != 0 AND `payment`.`pay_method` != 3".$query, $date,$date1t,$date2t);
			foreach($data as $row){
				$array["sum_opl"]+= $row["sum"];
				$array["count_opl"]++;
			}
			$data = $connect->getAll("SELECT payment.sum FROM payment, reckoning WHERE payment.schet=reckoning.id AND (payment.type=3 OR payment.type=4) AND ((`payment`.`processed` IS NULL AND `payment`.`date`=?s AND `payment`.`status` = 2) OR (`payment`.`processed` IS NOT NULL AND `payment`.`processed`>= ?i AND `payment`.`processed`< ?i AND `payment`.`status` = 2)) AND `payment`.`status` != 0 AND `payment`.`pay_method` != 3".$query, $date,$date1t,$date2t);
			foreach($data as $row){
				$array["sum_opl_san"]+= $row["sum"];
				$array["count_opl_san"]++;
			}
			$week = date("w", strToTime($date));
			ob_start();
		?>
			<tr>
				<td style="width: 20%"><?php echo $day.".".$month.".".$year; ?></td>
				<td style="width: 20%"><?php echo $array_week[$week]; ?></td>
				<td style="width: 10%"><?php echo $array["count_opl"]; ?></td>
				<td style="width: 20%"><?php echo number_format($array["sum_opl"], 2, ",", " "); ?></td>
				<td style="width: 10%"><?php echo $array["count_opl_san"]; ?></td>
				<td style="width: 20%"><?php echo number_format($array["sum_opl_san"], 2, ",", " "); ?></td>
			</tr>
		<?php
			$html.= ob_get_clean();
		}
		?>
		<table class="table table-hover table-condensed tbl-payment">
		<thead>
		<tr>
			<th rowspan="2">Дата</th>
			<th rowspan="2">День недели</th>
			<th colspan="2">Оплаты туриста</th>
			<th colspan="2">Оплаты в санаторий</th>
		</tr>
		<tr>
			<th>Кол-во</th>
			<th>Сумма</th>
			<th>Кол-во</th>
			<th>Сумма</th>
		</tr>
		</thead>
		<tbody>
			<?php echo $html; ?>
		</tbody>
		</table>
		<?php
	}else{
		for($month = 1; $month <= 12; $month++){
			$max = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$first = $year."-".$month."-1";
			$end = $year."-".$month."-".$max;
			$first_t = strtotime($first);
			$end_t = strtotime($end)+86400;
			$array = array("sum_opl" => 0, "count_opl" => 0, "sum_opl_san" => 0, "count_opl_san" => 0);
			$data = $connect->getAll("SELECT payment.sum FROM payment, reckoning WHERE payment.schet=reckoning.id AND (payment.type=1 OR payment.type=2) AND ((`payment`.`processed` IS NULL AND payment.date>=?s AND payment.date<=?s AND `payment`.`status` = 2) OR (`payment`.`processed` IS NOT NULL AND `payment`.`status` = 2 AND `payment`.`processed` >= ?i AND `payment`.`processed` < ?i)) AND `payment`.`status` != 0".$query, $first, $end, $first_t, $end_t);
			foreach($data as $row){
				$array["sum_opl"]+= $row["sum"];
				$array["count_opl"]++;
			}
			$data = $connect->getAll("SELECT payment.sum FROM payment, reckoning WHERE payment.schet=reckoning.id AND (payment.type=3 OR payment.type=4) AND ((`payment`.`processed` IS NULL AND payment.date>=?s AND payment.date<=?s AND `payment`.`status` = 2) OR (`payment`.`processed` IS NOT NULL AND `payment`.`status` = 2 AND `payment`.`processed` >= ?i AND `payment`.`processed` < ?i)) AND `payment`.`status` != 0".$query, $first, $end, $first_t, $end_t);
			foreach($data as $row){
				$array["sum_opl_san"]+= $row["sum"];
				$array["count_opl_san"]++;
			}
			ob_start();
		?>
			<tr>
				<td style="width: 20%"><?php echo $array_month[$month]; ?></td>
				<td style="width: 15%"><?php echo $array["count_opl"]; ?></td>
				<td style="width: 25%"><?php echo number_format($array["sum_opl"], 2, ",", " "); ?></td>
				<td style="width: 15%"><?php echo $array["count_opl_san"]; ?></td>
				<td style="width: 25%"><?php echo number_format($array["sum_opl_san"], 2, ",", " "); ?></td>
			</tr>
		<?php
			$html.= ob_get_clean();
		}
		?>
		<table class="table table-hover table-condensed tbl-payment">
		<thead>
		<tr>
			<th rowspan="2">Месяц</th>
			<th colspan="2">Оплаты туриста</th>
			<th colspan="2">Оплаты в санаторий</th>
		</tr>
		<tr>
			<th>Кол-во</th>
			<th>Сумма</th>
			<th>Кол-во</th>
			<th>Сумма</th>
		</tr>
		</thead>
		<tbody>
			<?php echo $html; ?>
		</tbody>
		</table>
		<?php
	}
}

function plan_report($connect){
    global $id_rights, $session_login;
    $managers = get_managers($connect,"","",$id_rights,$session_login);
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
        <?php if(!is_null($managers)) { ?>
            <div class="form-group">
                <label class="col-sm-4 control-label">Менеджер</label>
                <div class="col-sm-8">
                    <?php echo $managers; ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Месяц</label>
                <div class="col-sm-8">
                    <?php echo get_month_profit(); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Тип отчёта</label>
                <div class="col-sm-8">
                    <select class="form-control" id="report-type">
                        <option value="1">Заявки</option>
                        <option value="2">Платежи</option>
                    </select>
                </div>
            </div>
        <?php } else { ?>
          <div class="warning">У Вас нет доступа к данной информации...</div>
        <?php }?>
	</div>
    <?php if(!is_null($managers)) { ?>
        <div class="panel-footer">
            <div class="form-group form-group-margin">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="button" class="btn btn-success btn-sm" onclick="view_my_profit()"><i class="fa fa-search"></i> Показать</button>
                    <?php if($id_rights > 5)  { ?>
                    <button type="button" class="btn btn-success btn-sm" onclick="view_all_profit()"><i class="fa fa-search"></i> По всем</button>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php }?>
</div>
<div id="result"></div>
<?php
}

function view_all_profit($connect){
	$year_month = $_POST["month"];
	$arr = explode("-", $year_month);
	if(!isset($arr[1]))
		$arr[1] = "";
	$year = $arr[1];
	$month = $arr[0];
	if(!$month){
		$year = date("Y");
		$month = date("m");
		$date_start_month = date("Y-m-1");
		$date_end_month = date("Y-m-d", mktime(0, 0, 0, date("m")+1, 0, date("Y")));
	}else{
		$date_start_month = date($year."-".$month."-1");
		$date_end_month = date("Y-m-d", mktime(0, 0, 0, $month + 1, 0, $year));
	}
	$plan = 0;
	$all_raz = 0;
	$all_profit_stand = 0;
	$all_profit_reg = 0;
	$all_profit = 0;
	$all_plan = 0;
	$all_reward = 0;
	$all_reward_reg = 0;
	$html = "";
	$users = $connect->getAll("SELECT id, name FROM users");
	foreach($users as $user){
		$id_man = $user["id"];
		$manager = $user["name"];
		$row = $connect->getRow("SELECT id, plan, commission, commission_region FROM plan WHERE manager=?i AND year=?i AND month=?i AND  commission > 0", $id_man, $year, $month);
		if($row["id"]){
			$plan = $row["plan"];
			$commission = $row["commission"];
			$commission_region = $row["commission_region"];
			$reward = 0;
			$reward_reg = 0;
			$all_plan+= $plan;
			$data = $connect->getAll("SELECT reckoning.id, reckoning.date, reckoning.date_z, region.man_reward_scheme AS man_reward_scheme FROM reckoning INNER JOIN object ON object.id=reckoning.id_obj LEFT OUTER JOIN region ON region.id = object.id_reg WHERE reckoning.id_user=?i AND reckoning.status=5 AND reckoning.date_z >= ?s AND reckoning.date_z <= ?s", $id_man, $date_start_month, $date_end_month);
			foreach($data as $reck_row){
				$id = $reck_row["id"];
				$reward_schet = get_reward_schet($connect, $id);
                if($reck_row['man_reward_scheme'] == 1 && strtotime($reck_row['date_z']) >= strtotime("01.11.2017")) {
                    $reward_reg +=$reward_schet;
                }
				$reward+= $reward_schet;
			}
			$raz = $reward - $plan;
			if($raz >= 0){
				$color = "green";
				$profit_stand = round($raz * ($commission / 100), 2);
				$profit_reg = round($reward_reg * ($commission_region / 100), 2);
			}else{
				$color = "red";
				$profit_stand = 0;
                $profit_reg = 0;
			}
			$profit = $profit_reg+$profit_stand;
			$all_raz+= $raz;
			$all_profit_stand +=(float)$profit_stand;
			$all_profit_reg +=(float)$profit_reg;
			$all_reward+= $reward;
			$all_reward_reg+=$reward_reg;
			$all_profit+= $profit;
			//if($reward_reg == 0)
			  //  $reward_reg = "";
			$html.= "<tr>";
			$html.= "<td width='130'>".$manager."</td>";
			$html.= "<td width='100'>".$plan."</td>";
			$html.= "<td width='100'>".$reward."</td>";
			$html.= '<td width="100">'.$reward_reg.'</td>';
			$html.= "<td width='100' style='color: ".$color."'>".$raz."</td>";
			$html.= "<td width='100'>".$profit_stand."</td>";
            $html.= "<td width='100'>".$profit_reg."</td>";
            $html.= "<td width='100'>".$profit."</td>";
			$html.= "</tr>";
		}
	}
	if($html)
		$html = "<table class='table table-condensed'><tr><th>Менеджер</th><th>План</th><th>Факт</th><th>Факт по спец. регионам</th><th>Прибыль</th><th>З/п станд.</th><th>З/п по спец. рег.</th><th>З/п общая</th></tr>".$html."<tr><td><strong>Итого</strong></td><td><strong>".$all_plan."</strong></td><td><strong>".$all_reward."</strong></td><td><strong>".$all_reward_reg."</strong></td><td><strong>".$all_raz."</strong></td><td><strong>".$all_profit_stand."</strong></td><td><strong>".$all_profit_reg."</strong></td><td><strong>".$all_profit."</strong></td></tr></table>";
	else
		$html = "Данных не найдено";
	return $html;
}

function block_reckoning_month($connect){
	global $id_rights;
	$start = $_POST["start"];
	$end = $_POST["end"];
	$user = $_POST["user"];
	if($id_rights > 3){
		$data = $connect->getAll("SELECT id FROM reckoning WHERE id_user=?i AND status=5 AND active=0 AND date_z >= ?s AND date_z <= ?s", $user, $start, $end);
		foreach($data as $row){
			$id = $row["id"];
			$connect->query("UPDATE reckoning SET active=2 WHERE id=?i", $id);
			save_schet_to_history($connect, $id, "Заявка заблокирована");
		}
	}
}

function calendar_report($connect){
	global $array_month;
	$rest = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE date_z<=?s AND date_v>?s AND status=5", date("Y-m-d"), date("Y-m-d"));
	$month = 1;
	$year = 2013;
	$current_month = date("m");
	$current_year = date("Y");
	$html = "<div style='float: left;'>".$year." год<br />";
	while($current_year >= $year){
		$max_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$first = $year."-".$month."-1";
		$end = $year."-".$month."-".$max_day;
		$count = $connect->getOne("SELECT COUNT(*) FROM reckoning WHERE (status=5 OR status=4) AND date_z>=?s AND date_z<=?s", $first, $end);
		$data = $connect->getAll("SELECT count FROM arrivals WHERE date>=?s AND date<=?s", $first, $end);
		foreach($data as $row)
			$count+= $row["count"];
		$html.= $array_month[$month]." : <strong>".$count."</strong><br />";
		$month++;
		if($month > 12){
			$month = 1;
			$year++;
			if($current_year >= $year)
				$html.= "</div><div style='float: left; margin-left: 10px;'>".$year." год<br />";
		}
	}
	$html.= "</div>";
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Объект</label>
			<div class="col-sm-9" id="object_name">
				<input type="text" class="form-control id-object" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" onblur="verification_input_data('object', '1');" name="">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Сегодня отдыхает</label>
			<div class="col-sm-9">
				<div class="alert alert-info">
					<?php echo $rest; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="calendar" style="width: 750px; display: inline-block"></div>
<div id="month_arrival" style="display: inline-block; vertical-align: top; margin: 10px;"><?php echo $html; ?></div><br /><br />
<span id="itog" class="name_head"></span>
<div class="result"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function filter_calendar($connect){
	global $month_array;
	$array = array();
	$data = array();
	$year = $_POST["year"];
	$object = $_POST["object"];
	if($object)
		$object = " AND id_obj=$object";
	$month = $_POST['month'];
	$max_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
	for($day = 1; $day <= $max_day; $day++){
		$date = $year."-".$month."-".$day;
		$count = count($connect->getAll("SELECT id FROM reckoning WHERE (status=5 OR status=4) AND date_z=?s".$object, $date));
		$arrival = $connect->getAll("SELECT count FROM arrivals WHERE date=?s".$object, $date);
		foreach($arrival as $row)
			$count+= $row["count"];
		$count_no_opl = count($connect->getAll("SELECT id FROM reckoning WHERE status=3 AND date_z=?s".$object, $date));
		$itog+= $count;
		if($count_no_opl){
			$itog_no_opl+= $count_no_opl;
			$count.= " (".$count_no_opl.")";
		}
		if($count){
			$data["title"] = (string)$count;
			$data["start"] = $date;
			$array[] = $data;
		}
	}
	$data = array();
	$data["data"] = $array;
	$data["itog"] = $itog." (".$itog_no_opl.")";
	if($data["itog"])
		$data["itog"] = "Всего заездов: ".$data["itog"];
	return json_encode($data);
}

function find_reckoning_calendar($connect){
	$date = $_POST["date"];
	$object = $_POST["object"];
	if($object)
		$object = " AND id_obj=$object";
	$data = $connect->getAll("SELECT id, status, id_obj, sum, rest, id_user, agency, turist FROM reckoning WHERE date_z=?s AND (status=5 OR status=4 OR status=3) ORDER BY status DESC, id_obj".$object, $date);
	foreach($data as $row){
		$rest = "";
		$status = "";
		if($row["agency"]){
			$param = "agency";
			$type = $row["agency"];
		}else{
			$param = "turist";
			$type = $row["turist"];
		}
		if($row["status"] == 3)
			$status = " (неоплачен) ".$connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
		$object = get_object($connect, $row["id_obj"]);
		$array = explode(",", $row["rest"]);
		foreach($array as $turist){
			if($turist){
				$turist_row = $connect->getRow("SELECT surname, name, otch FROM klient WHERE id=?i", $turist);
				if($rest)
					$rest.= "<br />";
				$rest.= $turist_row["surname"]." ".$turist_row["name"]." ".$turist_row["otch"];
			}
		}
		if(!$rest)
			$rest = "Отдыхающий не указан";
		ob_start();
	?>
		<tr>
			<td style="width: 20%">
				<a href="#" onclick="show_turist('<?php echo $type; ?>', '<?php echo $id_schet; ?>', '<?php echo $param; ?>')"><?php echo $row["id"]; ?></a>
				<?php echo $status; ?></td>
			<td style="width: 30%"><?php echo $object; ?></td>
			<td style="width: 35%"><?php echo $rest; ?></td>
			<td style="width: 15%"><?php echo $row["sum"]; ?></td>
		</tr>
	<?php
		$html.= ob_get_clean();
	}
	if($html)
		$html = "<table class='table table-bordered table-conserved'><tr><th>№</th><th>Объект</th><th>Отдыхающий</th><th>Стоимость</th></tr>".$html."</table>";
	return $html;
}

function history_report(){
	ob_start();
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-body">
		<div class="form-group form-group-margin">
			<label class="col-sm-2 control-label">Дата</label>
			<div class="col-sm-5">
				<input type="text" class="form-control datepicker" id="date_1" />
			</div>
			<div class="col-sm-5">
				<input type="text" class="form-control datepicker" id="date_2" />
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="form-group form-group-margin">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="button" class="btn btn-success btn-sm" onclick="filter_history()"><i class="fa fa-search"></i> Применить</button>
			</div>
		</div>
	</div>
</div>
<div id="filter_res"></div>
<?php
	$html = ob_get_clean();
	return $html;
}

function filter_history($connect){
	$date1 = $_POST["date_1"];
	$date2 = $_POST["date_2"];
	$ar_status = get_status_array($connect, "status");
	$ar_status_san = get_status_array($connect, "status_san");
	if($date2){
		$zapros_for_mysql = "date>='".$date1."' AND date<='".$date2."'";
	}else
		$zapros_for_mysql = "date='".$date1."'";
	$data = $connect->getAll("SELECT id, DATE_FORMAT(date, '%d.%m.%Y') as date, time, id_schet, new_status, note, new_status_san, id_user FROM history_schet WHERE ".$zapros_for_mysql." LIMIT 1000");
	foreach($data as $row){
		$date = $row["date"]." ".$row["time"];
		$id_schet = $row["id_schet"];
		$status = $row["new_status"];
		$status_san = $row["new_status_san"];
		$note = $row["note"];
		$id = $a["id"];
		$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
		$row = $connect->getRow("SELECT new_status, new_status_san FROM history_schet WHERE id_schet=?i AND id<?i ORDER BY id DESC", $id_schet, $id);
		$new_status = $row["new_status"];
		$new_status_san = $row["new_status_san"];
		$note_status = "";
		if($new_status){
			if($new_status != $status){
				$note_status = "Изменение статуса (".$ar_status[$new_status]." -> ".$ar_status[$status].")";
			}
			if($new_status_san != $status_san){
				if($note_status)
					$note_status.= "<br />";
				$note_status = "Изменение статуса санатория (".$ar_status_san[$new_status_san]." -> ".$ar_status_san[$status_san].")";
			}
			if($note_status)
				$note = $note_status."<br />".$note;
		}
		$row = $connect->getRow("SELECT turist, agency, rest, id_obj FROM reckoning WHERE id=?i", $id_schet);
		$object = get_object($connect, $row["id_obj"]);
		if($row["turist"])
			$client = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $row["turist"]);
		else
			$client = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
		$html.= "<tr>";
		$html.= "<td width='30' valign='top'>".$id_schet."</td>";
		$html.= "<td width='140' valign='top'>".$date."</td>";
		$html.= "<td width='200' valign='top'>".$client."</td>";
		$html.= "<td width='150' valign='top'>".$object."</td>";
		$html.= "<td width='350' valign='top'>".$note."</td>";
		$html.= "<td width='70' valign='top'>".$manager."</td>";
		$html.= "</tr>";
	}
	if($html)
		$html = "<table class='table table-condensed' id='tbl_filter'><thead><tr><th>№</th><th class='{dateFormat: \"ddmmyyyy\"}'>Дата</th><th>Клиент</th><th>Объект</th><th>Изменения</th><th>Менеджер</th></tr></thead><tbody>".$html."</tbody></table>";
	else
		$html = "Ничего не найдено";
	return $html;
}

function return_query_report($connect){
	$data = $connect->getAll("SELECT id, id_reck, date_create, DATE_FORMAT(date, '%d.%m.%Y') as date_stat, sum, type_pay, check_pay FROM return_query WHERE active=1 ORDER BY date");
	ob_start();
?>
	<?php if(!$data){ ?>
		<div class="alert alert-info">Ничего не найдено</div>
	<?php }else{ ?>
		<table class="table table-hover table-condensed">
		<thead>
		<tr>
			<th>Заявка</th>
			<th>Клиент</th>
			<th>Офис</th>
			<th class="{dateFormat: 'ddmmyyyy'}">Создан</th>
			<th class="{dateFormat: 'ddmmyyyy'}">Заявление</th>
			<th>Сумма</th>
			<th>Способ оплаты</th>
			<th></th>
		</tr>
		</thead>
		<tbody>
<?php
	$itog = 0;
	foreach($data as $row){
		$id = $row["id"];
		$reck = $row["id_reck"];
		$sum = $row["sum"];
		$create = date("d.m.Y", $row["date_create"]);
		$date = $row["date_stat"];
		if($date == "00.00.0000")
			$date = "";
		$check = "";
		$class = "";
		if($row["check_pay"] == 1){
			$check = " checked ";
			$class = " class='success' ";
			$itog+= $sum;
		}
		if($row["type_pay"] == 1)
			$type_pay = "безналичный";
		elseif($row["type_pay"] == 5)
			$type_pay = "банковской картой";
		else
			$type_pay = "наличными";
		$row = $connect->getRow("SELECT turist, agency, id_user FROM reckoning WHERE id=?i", $reck);
		if($row["agency"]){
			$param = "agency";
			$type = $row["agency"];
			$klient = $connect->getOne("SELECT name FROM agency WHERE id=?i", $row["agency"]);
		}else{
			$param = "turist";
			$type = $row["turist"];
			$klient = select_name_klient($connect, $row["turist"]);
		}
		$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $row["id_user"]);
		$name_office = $connect->getOne("SELECT name FROM office WHERE id=?i", $office);
		ob_start();
?>
		<tr <?php echo $class; ?>>
			<td width="5%" onclick="show_turist('<?php echo $type; ?>', '<?php echo $reck; ?>', '<?php echo $param; ?>')"><?php echo $reck; ?></td>
			<td width="20%" onclick="show_turist('<?php echo $type; ?>', '<?php echo $reck; ?>', '<?php echo $param; ?>')"><?php echo $klient; ?></td>
			<td width="10%"><?php echo $name_office; ?></td>
			<td width="10%"><?php echo $create; ?></td>
			<td width="10%"><?php echo $date; ?></td>
			<td width="15%"><?php echo $sum; ?></td>
			<td width="15%"><?php echo $type_pay; ?></td>
			<td width="15%">
				<button class="btn btn-default btn-xs" onclick="edit_return_query('<?php echo $id; ?>')">&nbsp;<i class="fa fa-pencil"></i>&nbsp;</button>
				<button class="btn btn-danger btn-xs" onclick="delete_return_query('<?php echo $id; ?>')">&nbsp;<i class="fa fa-times-circle"></i>&nbsp;</button>
<?php if($date != ""){ ?>
				<label class="btn btn-success btn-xs"><input type="checkbox" <?php echo $check; ?> onclick="check_return_query(<?php echo $id; ?>)"></label>
<?php } ?>
			</td>
		</tr>
<?php
		$type = "no-date";
		if($date != "")
			$type = "date";
		$html[$type].= ob_get_clean();
	}
	echo $html["date"].$html["no-date"];
	if($itog > 0){
?>
		<tr>
			<td colspan="4"></td>
			<td>Итого</td>
			<td><?php echo add_null($itog); ?></td>
			<td colspan="2"></td>
		</tr>
	<?php } ?>
		</tbody>
		</table>
<?php
	}
	$html = ob_get_clean();
	return $html;
}

function edit_return_query($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT sum, type_pay, date FROM return_query WHERE id=?i", $id);
	$select = array($row["type_pay"] => "SELECTED");
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить заявку на возврат</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-query">
					<div class="form-group">
						<label class="col-sm-4 control-label">Дата заявления</label>
						<div class="col-sm-8">
							<input type="text" class="form-control datepicker" id="date-return" value="<?php echo $row['date']; ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">Сумма возврата</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="sum-return" value="<?php echo $row['sum']; ?>" />
						</div>
					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-4 control-label">Способ оплаты</label>
						<div class="col-sm-8">
							<select id="type-pay" class="form-control">
								<option value="1" <?php echo $select[1]; ?>>безналичный</option>
								<option value="2" <?php echo $select[2]; ?>>наличными</option>
								<option value="5" <?php echo $select[5]; ?>>банковской картой</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success btn-sm" onclick="update_return_query('<?php echo $id; ?>')"><i class="fa fa-check"></i> Применить</button>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function update_oplata_query($connect){
	$id = $_POST["id"];
	$sum = $_POST["sum"];
	$date = $_POST["date"];

	if(empty($date))
	    $date = NULL;

	$type = $_POST["type"];
	$connect->query("UPDATE return_query SET date=?s, sum=?s, type_pay=?i WHERE id=?i", $date, $sum, $type, $id);
}

function delete_oplata_query($connect){
	$id = $_POST["id"];
	$connect->query("DELETE FROM return_query WHERE id=?i", $id);
}

function check_return_query($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT check_pay, id_reck FROM return_query WHERE id=?i", $id);
	$check = 1;
	$note = "Разрешение на возврат";
	if($row["check_pay"] == 1){
		$check = 0;
		$note = "Снятие разрешение на возврат";
	}
	$connect->query("UPDATE return_query SET check_pay=?i WHERE id=?i", $check, $id);
	save_schet_to_history($connect, $row["id_reck"], $note);
}

function calc_payment_to_san($connect){
	$array = explode("_", $_POST["id"]);
	$array = array_diff($array, array(""));
	$all_oplata = 0;
	$all_id = 0;
	$all_sum = 0;
	$san_oplata = 0;
	$san_prepay = 0;
	$arr_obj = array(1 => 1, 3 => 1, 15 => 1, 18 => 1, 20 => 1, 28 => 1, 31 => 1, 34 => 1, 35 => 1, 54 => 1, 59 => 1, 12 => 1, 42 => 1, 45 => 1);
	foreach($array as $id){
		$row = $connect->getRow("SELECT sum, id_obj FROM reckoning WHERE id=?i", $id);
		$sum = $row["sum"];
		$id_obj = $row["id_obj"];
		if($arr_obj[$id_obj])
			$oplata = $sum;
		else
			$oplata = $sum - get_reward_schet($connect, $id, "ONLY_SAN");
		$all_oplata+= $oplata;
		$all_sum+= $sum;
		$all_id++;
		$array = get_payment($connect, $id, 4);
		foreach($array as $payment)
			$san_oplata+= $payment["sum"];
		$array = get_payment($connect, $id, 3);
		foreach($array as $payment)
			$san_prepay+= $payment["sum"];
	}
	$all_oplata = $all_oplata - $san_prepay;
	ob_start();
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Сумма оплаты в санаторий</h4>
			</div>
			<div class="list-group form-horizontal">
				<div class="list-group-item list-hover-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-6 control-label-element">Всего заявок</label>
						<div class="col-sm-6">
							<?php echo $all_id; ?>
						</div>
					</div>
				</div>
				<div class="list-group-item list-hover-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-6 control-label-element">На сумму</label>
						<div class="col-sm-6">
							<?php echo number_format($all_sum, 2, ",", " "); ?>
						</div>
					</div>
				</div>
				<div class="list-group-item list-hover-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-6 control-label-element">К оплате в санаторий</label>
						<div class="col-sm-6">
							<?php echo number_format($all_oplata, 2, ",", " "); ?>
						</div>
					</div>
				</div>
				<div class="list-group-item list-hover-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-6 control-label-element">Оплачено в санаторий</label>
						<div class="col-sm-6">
							<?php echo number_format($san_oplata, 2, ",", " "); ?>
						</div>
					</div>
				</div>
				<div class="list-group-item list-hover-item">
					<div class="form-group form-group-margin">
						<label class="col-sm-6 control-label-element">Предоплат в санаторий</label>
						<div class="col-sm-6">
							<?php echo number_format($san_prepay, 2, ",", " "); ?>
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

function get_schet_san($connect){
	$all_id = explode("_", $_POST["all_id"]);
	$array = array();
	$no_schet = array();
	foreach($all_id as $id){
		$document = 0;
		$docs = json_decode($connect->getOne("SELECT doc_schet_san FROM reckoning WHERE id=?i", $id), TRUE);
		foreach($docs as $doc){
			if($doc["type"] == "bill"){
				$index = count($array);
				$array["schet"][$index]["id"] = $id;
				$array["schet"][$index]["doc"] = $doc["doc"];
				$document = 1;
			}
		}
		if($document == 0){
			if($array["no-schet"])
				$array["no-schet"].= ", ";
			$array["no-schet"].= $id;
		}
	}
	echo json_encode($array);
}

function update_mass_reckoning($connect){
	$arr_id = explode("_", $_POST["id"]);
	$arr_id = array_diff($arr_id, array(""));
	$type = $_POST["type"];
	foreach($arr_id as $id){
		$row = $connect->getRow("SELECT status, status_san, active FROM reckoning WHERE id=?i", $id);
		$status = $row["status"];
		$status_san = $row["status_san"];
		$active = $row["active"];
		if($type == "cancel"){
			if($status == 8){
				$connect->query("UPDATE reckoning SET status=6 WHERE id=?i", $id);
				$connect->query("DELETE from bonus WHERE schet=?i", $id);
				save_schet_to_history($connect, $id);
			}else
				$html.= "<div class='alert alert-danger'>Внимание! Статус заявки №".$id." невозможно измененить.</div>";
		}elseif($type == "return_cancel"){
			if($status == 8){
				$new = $connect->getRow("SELECT new_status FROM history_schet WHERE id_schet=?i AND new_status!=8 ORDER BY id DESC", $id);
				$connect->query("UPDATE reckoning SET status=?i WHERE id=?i", $new, $id);
				save_schet_to_history($id, "Возврат");
			}else
				$html.= "<div class='alert alert-danger'>Внимание! Статус заявки №".$id." невозможно измененить.</div>";
		}elseif($type == "permit_san"){
			if($status_san == 0 OR $status_san == 3){
				$connect->query("UPDATE reckoning SET status_san=2 WHERE id=?i", $id);
				save_schet_to_history($connect, $id);
			}else
				$html.= "<div class='alert alert-danger'>Внимание! Статус заявки №".$id." невозможно измененить.</div>";
		}elseif($type == "permit_san_prepay"){
			if($status_san == 0 OR $status_san == 3){
				$connect->query("UPDATE reckoning SET status_san=6 WHERE id=?i", $id);
				save_schet_to_history($connect, $id);
			}else
				$html.= "<div class='alert alert-danger'>Внимание! Статус заявки №".$id." невозможно измененить.</div>";
		}elseif($type == "return_san"){
			if($status_san == 2 OR $status_san == 6){
				$connect->query("UPDATE reckoning SET status_san=0 WHERE id=?i", $id);
				save_schet_to_history($connect, $id);
			}else
				$html.= "<div class='alert alert-danger'>Внимание! Статус заявки №".$id." невозможно измененить.</div>";
		}elseif($type == block){
			if($status == 5 AND $active != 2){
				$connect->query("UPDATE reckoning SET active=2 WHERE id=?i", $id);
				save_schet_to_history($connect, $id, "Заявка заблокирована");
			}else
				$html.= "<div class='alert alert-danger'>Внимание! Статус заявки №".$id." невозможно измененить.</div>";
		}
	}
	return $html;
}

function report_request_payment($connect){
	$data = $connect->getAll("SELECT bid, sum, type, DATE_FORMAT(time, '%d.%m.%Y %H:%i:%s') as date, status FROM payment_request");
	return json_encode($data);
}

function report_expected_cash_receipts($connect){
	$array = array();
	$after = 5;
	$day = date("w");
	if($day == 5 OR $day == 4)
		$after = 4;
	$today = strToTime(date("Y-m-d"));
	$date = date("Y-m-d", strToTime("-".$after." days"));
	$data = $connect->getAll("SELECT id, date, schet, sum FROM payment WHERE pay_method=5 AND date>=?s ORDER BY date", $date);
	foreach($data as $row){
		$id = $row["id"];
		$time = strToTime($row["date"]) + 86400;
		$week = date("w", $time);
		$day = ($today - $time) / 86400;
		$go = 3;
		if($week == 3)
			$go = 5;
		if($week == 4 OR $week == 5 OR $week == 6)
			$go = 4;
		$raz = $go - $day;
		if($raz >= 0){
			$text = "через ".$raz." дня";
			if($raz == 1)
				$text = "через 1 день";
			if($raz == 0)
				$text = "сегодня";
			if(!isset($array[$time]))
				$array[$time] = array("sum" => 0, "pay" => array());
			$array[$time]["sum"]+= $row["sum"];
			$array[$time]["pay"][$id] = array();
			$array[$time]["pay"][$id]["date"] = month_transform(date_change($row["date"]));
			$array[$time]["pay"][$id]["bid"] = $row["schet"];
			$array[$time]["pay"][$id]["sum"] = $row["sum"];
			$array[$time]["pay"][$id]["day"] = $text;
		}
	}
	foreach($array as $index => $row){
		$array[$index]["sum"] = number_format($row["sum"], 2, ",", " ");
	}
	return json_encode($array);
}

?>
