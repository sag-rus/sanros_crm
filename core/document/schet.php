<?php

function review_schet($connect, $type = "PDF", $id, $for = ""){
	global $directory, $session_login;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
	$leg_address = $conf->leg_address;
	$sep_address = $conf->sep_address;
	$email = $conf->Email;
	$tel = $conf->tel_firma;
	$fax = $conf->fax_firma;
	$web_site = $conf->web_site;
	$INN = $conf->INN;
	$KPP = $conf->KPP;
	$BIK = $conf->BIK;
	$KS = $conf->KS;
	$bank = $conf->bank;
	$reck = $conf->reck;
	$director = $conf->director;
	$booker = $conf->booker;
	$reestr = $conf->reestr;
	$pay_days = isset($_GET['pay_days'])?(int)$_GET['pay_days']:1;
    $pay_date = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date FROM time_payment WHERE id_schet=?i AND type=1", $id);


	$pay_days_strings = [
        '0' => 'банковских дней',
        '1' => 'банковский день',
        '2' => 'банковских дня',
        '3' => 'банковских дня',
        '4' => 'банковских дня',
        '5' => 'банковских дней',
        '6' => 'банковских дней',
        '7' => 'банковских дней',
        '8' => 'банковских дней',
        '9' => 'банковских дней'
    ];


	$pay_days_strings2 = [
	  '0' => 'рабочих дней',
      '1' => 'рабочего дня',
      '2' => 'рабочих дней',
      '3' => 'рабочих дней',
      '4' => 'рабочих дней',
      '5' => 'рабочих дней',
      '6' => 'рабочих дней',
      '7' => 'рабочих дней',
      '8' => 'рабочих дней',
      '9' => 'рабочих дней'
    ];



	if($pay_days <= 0)
	    $pay_days = 1;

    $pay_days_last_char = (string)$pay_days;
    $pay_days_last_char = mb_substr($pay_days_last_char,mb_strlen($pay_days_last_char)-1,1);

    $type_date = $_GET["date"];
	$data = $connect->getAll("SELECT position_reck.date_z, position_reck.number, reckoning.payer, position_reck.days, reckoning.id_obj, position_reck.id_room, position_reck.note, reckoning.turist, reckoning.agency, reckoning.id_dis, reckoning.id_com, reckoning.date, reckoning.status_san, position_reck.reward, position_reck.sum, position_reck.type, position_reck.add_one_day, position_reck.id_service, reckoning.agency, reckoning.date, reckoning.status FROM position_reck, reckoning WHERE reckoning.id=?i AND position_reck.schet=?i", $id, $id);
	$index = 0;
	$itog_sum = 0;
	$itog_num = 0;
	$table = "";
	$date_reck = 0;
	foreach($data as $row){
	    $date_reck = $row['date'];
	    if(!$row['agency'] && (strtotime($date_reck) >= strtotime("07.09.2018") || $row['status'] == 3)) {
          $BIK = $conf->BIK2;
          $KS = $conf->KS2;
          $bank = $conf->bank2;
          $reck = $conf->reck2;
        }

		$days = $row["days"];
		$note = $row["note"];
		$turist = $row["turist"];
		$agency = $row["agency"];
		$id_obj = $row["id_obj"];
		$id_room = $row["id_room"];
		$sum = $row["sum"];
		$type_price = $row["type"];
		$number = $row["number"];
		$payer = $row["payer"];
		$add_one_day = $row["add_one_day"];
		$status_san = $row["status_san"];
		$id_service = $row["id_service"];
		if($type_date == "today")
			$date = date("d-m-Y");
		elseif($type_date == "create")
			$date = date_change($row["date"]);
		else 
		    $date = date("d-m-Y",strtotime($type_date));
		
		$date = month_transform($date);
		$date_z = date_change($row["date_z"]);
		$date2 = strToTime($date_z);
		$days_sum = $days;
		if($add_one_day == 0)
			$days_sum--;
		$date2 = date_sum($date2, $days_sum);
		$date_v = date("d-m-Y", $date2);
		$date_v = str_replace("-", ".", $date_v);
		$date_z = str_replace("-", ".", $date_z);
		if(!$agency)
			$payer = $connect->getOne("SELECT name FROM payer WHERE id=?i", $payer);
		else
			$payer = $connect->getOne("SELECT name FROM agency WHERE id=?i", $agency);
		$trans = get_translit($payer);
		if($id_obj != "96")
			$object = get_object($connect, $id_obj, "type");
		else
			$object = get_object($connect, $connect->getOne("SELECT id_obj FROM room WHERE id=?i", $id_room), "type");
		$room = get_room($connect, $id_room, "full");
		$index++;
		$sum2 = calculate_position($sum, $number, $type_price, $days);
		$sum2 = add_null($sum2);
		if($type_price == 1 OR $type_price == 2){
			$sum = $sum * $days;
			$sum = add_null($sum);
		}
		$table.= "<tr><td width='20' align='center'>".$index."</td>";
		if($id_room != 0)
			$table.= "<td width='350' style='max-width: 30%;'>".$object." c ".$date_z." по ".$date_v." (".$days." дн., ".$room.") ".$note."</td>";
		else
			$table.= "<td width='350' style='max-width: 30%;'>".$connect->getOne("SELECT name FROM service_schet WHERE id=?i", $id_service)." ".$note."</td>";
		$table.= "<td width='40' align='center'>шт</td>";
		$table.= "<td width='40' align='center'>".$number."</td>";
		$table.= "<td width='60' align='center'>".str_replace(".", "-", $sum)."</td>";
		$table.= "<td width='60' align='center'>".str_replace(".", "-", $sum2)."</td></tr>";
		$itog_sum+= $sum2;

		$itog_num+= $number;
		if($agency)
			$commis = $connect->getOne("SELECT value FROM commission WHERE id=?i", $row["id_com"]);
		else{
			$id_dis = $row["id_dis"];
			$discount = $connect->getRow("SELECT `value`, `type` FROM discount WHERE id=?i", $id_dis);
			$bonus = abs($connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $id));
		}
	}
	$itog = $itog_sum;
	$sum3 = $itog_sum;
	if($agency){
		$sum3 = get_reward_agency($connect, $id);
		$itog_sum = $itog_sum - $sum3;
	}elseif($discount){
		//if($type_dis == 1){
        if($discount['type'] == 1) {
          $sum3 = ($discount['value'] / 100) * $itog;
          $type_dis = "%";
        }
        else {
          $sum3 = $discount['value'];
          $type_dis = " руб.";
        }

		$itog_sum = $itog_sum - $sum3;
	}
	if(isset($bonus) && $bonus > 0)
		$itog_sum = $itog_sum - $bonus;
	$prepay_sum = $connect->getOne("SELECT sum(sum) FROM payment WHERE schet=?i AND type=1", $id);
	if($prepay_sum > 0){
		$itog_sum-= $prepay_sum;
		$prepay_sum = add_null($prepay_sum);
	}
	$itog_sum = add_null(round($itog_sum, 2));
	$sum3 = add_null($sum3);
	$itog = add_null($itog);
	$bonus = isset($bonus)?add_null($bonus):add_null(null);
	$col_str = 5;
	$img = isset($_COOKIE["img"])?$_COOKIE["img"]:null;
	$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login);
	$row = $connect->getRow("SELECT address, bank, rs, ks, bik, inn, kpp, telephone FROM office WHERE id=?i", $office);
	if($row["bank"]){
	    if(!(strtotime($date_reck) >= strtotime("07.09.2018") || $row['status'] == 3)) {
          $BIK = $row["bik"];
          $KS = $row["ks"];
          $bank = $row["bank"];
          $reck = $row["rs"];
        }
		$sep_address = $row["address"];
		$tel = $row["telephone"];
	}

	$services = array();
	$service_reckoning = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
	$data = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head, sort_order, name DESC, type, id");
	foreach($data as $row){
		if(in_array($row["id"], $service_reckoning)) {
			$services_update[] = $row["id"];
			$services[] = $row["name"];
		}
	}

	ob_start();
?>
	<div class="content">
	<table cellpadding="5" cellspacing="0">
	<tr>
		<td style="border: none; text-align: right; width: 730px;">
            <?php if(!$pay_date) { ?>
			<span class="bold_head" style="color: #DB0E0E;">ДЕЙСТВИТЕЛЕН В ТЕЧЕНИЕ <?=$pay_days;?> <?=mb_strtoupper($pay_days_strings2[$pay_days_last_char]);?></span><br />
            <?php } ?>
			<span class="bold_head" style="color: #DB0E0E;">При оплате обязательно указывайте номер счета</span>
		</td>
	</tr>
	<tr>
		<td style="border: none; width: 730px;">
			<br />
			<img src="images/logo-menu.png" /><br />
			<p style="margin-top: 10px;"><?php echo $firma; ?><br />
		<strong>Юридический адрес:</strong> <?php echo $leg_address; ?><br />
		<strong>Обособленное подразделение:</strong> <?php echo $sep_address; ?><br />
		<strong>Тел.:</strong> <?php echo $tel; ?> <strong>Факс:</strong> <?php echo $fax; ?><br />
		<strong>Email:</strong> <?php echo $email; ?> <strong>Сайт:</strong> <?php echo $web_site; ?></p>
		</td>
	</tr>
	</table>
	<p class="bold_head" style="color: #DB0E0E; margin: 10px 0;">ВНИМАНИЕ! ЗАПОЛНИТЬ ПРАВИЛЬНО: <?php echo $firma; ?></p>
	<table cellpadding="5" cellspacing="0">
	<tr>
		<td width="230">ИНН <?php echo $INN; ?></td>
		<td width="230">КПП <?php echo $KPP; ?></td>
		<td width="53" align="center" rowspan="2" valign="middle">Сч. №</td>
		<td width="180" rowspan="2" valign="middle"><?php echo $reck; ?></td>
	</tr>
	<tr>
		<td colspan="2">Получатель<br /><?php echo $firma; ?></td>
	</tr>
	<tr>
		<td colspan="2" rowspan="2">Банк получателя<br /><?php echo $bank; ?></td>
		<td align="center">БИК</td>
		<td><?php echo $BIK; ?></td>
	</tr>
	<tr>
		<td align="center">К/C №</td>
		<td><?php echo $KS; ?></td>
	</tr>
	</table>
	<p><span class="bold_head">СЧЕТ № <?php echo $id." от ".str_replace("-", ".", $date); ?></span><br />
	Заказчик: <?php echo $payer; ?><br />
	Плательщик: <?php echo $payer; ?></p>
	<table cellpadding="5" cellspacing="0">
	<tr>
		<th width="20">№</th>
		<th width="450">Наименование санаторно-курортных услуг</th>
		<th width="40">Ед/изм</th>
		<th width="40">Кол-во</th>
		<th width="60">Цена</th>
		<th width="60">Сумма</th>
	</tr>

		<?php echo $table; ?>

	<tr>
		<td rowspan="7" colspan="3" style="text-align: left; border: none;"><br />Всего наименований <?php echo $itog_num; ?>, на сумму <?php echo str_replace(".", "-", $itog_sum); ?> рублей<br />
			<?php
				$arr = explode(".", $itog_sum);
				$itog_sum_string = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
				$itog_sum_string = first_symbol_to_title($itog_sum_string);
				echo $itog_sum_string;
			?>
			<br />
			<?php if(count($services)){ ?>
				<strong>В стоимость путевки входит:</strong>
				<?=implode(', ', $services)?>
			<?php } ?>
			<table class="head" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td width="150" valign="bottom">Руководитель предприятия</td>
				<td width="100" rowspan="2" height="100">
				<?php if($img == 1)
					echo "<img src='images/pechat/pechat.jpg' />";
				?>
				</td>
				<td valign="bottom">(<?php echo $director; ?>)</td>
			</tr>
			<tr>
				<td valign="top"><br /><br />Главный бухгалтер</td>
				<td valign="top"><br /><br />(<?php echo $booker; ?>)</td>
			</tr>
			</table>
		</td>
		<td colspan="2" style="text-align: right; border: none"><strong>Итого:</strong></td>
		<td align="center"><?php echo str_replace(".", "-", $itog); ?></td>
	</tr>
		<?php
			if($agency OR ($id_dis AND $sum3 > 0)){
				if($agency){
					$nad = "Комиссия (".$commis."%):";
				}else
					$nad = "Скидка (".$discount['value'].$type_dis."):";
				echo "<tr><td colspan='2' style='text-align: right; border: none'><strong>".$nad."</strong></td><td align='center'>".str_replace(".", "-", $sum3)."</td></tr>";
				$col_str--;
			}
			if($bonus > 0){
				echo "<tr><td colspan='2' style='text-align: right; border: none'><strong>Бонусы:</strong></td><td align='center'>".str_replace(".", "-", $bonus)."</td></tr>";
				$col_str--;
			}
			if($prepay_sum > 0){
				echo "<tr><td colspan='2' style='text-align: right; border: none'><strong>Предоплата:</strong></td><td align='center'>".str_replace(".", "-", $prepay_sum)."</td></tr>";
				$col_str--;
			}
		?>
	<tr>
		<td colspan="2" style="text-align: right; border: none"><strong>Без налога (НДС):</strong></td>
		<td align="center">-</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right; border: 1px solid #fff;"><strong>Всего к оплате:</strong></td>
		<td align="center"><?php echo str_replace(".", "-", $itog_sum); ?></td>
	</tr>
<?php
	$prepay_time = $connect->getRow("SELECT sum, DATE_FORMAT(date, '%d.%m.%Y') as date FROM time_payment WHERE id_schet=?i AND type=2", $id);
	if($prepay_time || $pay_date){
		$col_str--;
?>
	<tr>
		<td colspan="3" style="border: none;">
			<div style=" width: 200px">
            <?php if($prepay_time) { ?>
			<strong>Аванс(<?php echo $prepay_time["sum"]; ?> рублей) внести до <?php echo $prepay_time["date"]; ?>.</strong>
            <?php } ?>
<?php
		if($pay_date){
?>
			<br /><strong>Окончательный расчет не позднее <?php echo $pay_date; ?></strong>
		<?php } ?>
			</div>
		</td>
	</tr>
	<?php }elseif($agency){
		$col_str--;
?>
	<tr>
		<td colspan="3" style="border: none;">
			<div style=" width: 200px">
				<strong style="color: red">Срок оплаты <?=$pay_days;?> <?=$pay_days_strings[$pay_days_last_char];?></strong>
			</div>
		</td>
	</tr>
	<?php } ?>
	<?php
		for($i=1; $i<=$col_str; $i++)
			echo "<tr><td colspan='3' style='border: none;'>&nbsp;<br /></td></tr>";
	?>
	</table>
	<?php if(!$agency) echo "<span class='bold_head'>Оплатив счет, Вы получите 2% бонус на следующий заказ</span>"; ?>
	</div>

<style type="text/css">

.content{
	font-family: freesans, sans-serif;
	width: 600px;
	font-size: 9.5pt;
	margin: 0 auto;
}

table, tr {
    max-width: 100%;
    display: block;
}

td, th{
	padding: 2px;
	font-size: 9.5pt;
	vertical-align: middle;
	border: 1px solid black;
    display: inline-block;
}

th{
	text-align: center;
	font-weight: normal;
}

.head td{
	border: none;
}

.bold_head{
	font-size: 12pt;
	font-weight: bold;
}

</style>

<?php
	$content = ob_get_clean();
	if($type == "HTML"){
		echo $content;
	?>
	<script type="text/javascript">
		window.onload = function(){
			setTimeout("print()", 1000);
		}
	</script>
	<?php
	}elseif($type == "PDF"){
		include($directory."/core/lib/html2PDF/html2pdf.class.php");
		$pdf = new HTML2PDF("P", "A4", "en", array(0, 0, 0, 0), "UTF-8");
		$pdf->WriteHTML($content);
		if($for == "email"){
			$file = $directory."/temp/forms/bron".$id.".pdf";
			$pdf->Output($file, "F");
			return $file;
		}else
			$pdf->Output($trans."_s_".$id.".pdf");
	}

}

?>
