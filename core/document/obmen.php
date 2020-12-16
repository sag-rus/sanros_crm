<?php

function review_obmen($connect, $type = "PDF", $id, $for = ""){
	global $directory, $session_login;

	include_once($directory."/config.php");

	$index = "";
	$reduced = "";
	$show_pay = "";
	if(isset($_GET["dubl"]))
		$index = $_GET["dubl"];
	if(isset($_GET["reduced"]))
		$reduced = $_GET["reduced"];
	if(isset($_GET["show_pay"]))
		$show_pay = $_GET["show_pay"];
	if(!$index)
		$index = 1;

	$conf = new JConfig;
	$firma = $conf->firma;
	$email = $conf->Email;
	$tel = $conf->tel_firma;
	$fax = $conf->fax_firma;
	$web_site = $conf->web_site;
	$director = $conf->director;
	$reestr = $conf->reestr;

	$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login);
	$row = $connect->getRow("SELECT address, bank, rs, ks, bik, inn, kpp, telephone FROM office WHERE id=?i", $office);
	if($row["telephone"]){
		$tel = $row["telephone"];
	}


	// $services_update = array();
	$services = array();
	$service_reckoning = explode("_", $connect->getOne("SELECT id_services FROM reckoning WHERE id=?i", $id));
	$data = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY head, sort_order, name DESC, type, id");
	foreach($data as $row){
		if(in_array($row["id"], $service_reckoning)) {
			$services_update[] = $row["id"];
			$services[] = $row["name"];
		}
	}


	// if(count($services_update)) {
	// 	$connect->query("UPDATE reckoning SET id_services=?s WHERE id=?i", implode("_", $services_update), $id);
	// }

	$image = "pechat1";
	$post = "Руководитель предприятия";
	if($office){
		$row = $connect->getRow("SELECT present, post, print_image FROM office WHERE id=?i", $office);
		if($row["present"]){
			$director = $row["present"];
			$post = $row["post"];
			if($row["print_image"])
				$image = $row["print_image"];
		}
	}

	$today = date("d.m.Y");
	$table = "";
	$max_days = 0;
	$trans = "";

	$row = $connect->getRow("SELECT status, status_san, date_v, date_z, id_obj, turist, agency, sum, rest, number_turist, schet_san, date_schet_san, note_bid FROM reckoning WHERE id=?i", $id);
	$status = $row["status"];
	$status_san = $row["status_san"];
	$sum = $row["sum"];
	$turist = $row["turist"];
	$agency = $row["agency"];
	$id_obj = $row["id_obj"];
	$rest = $row["rest"];
	$schet_san = $row["schet_san"];
	$note_bid = $row["note_bid"];
	$date_schet_san = $row["date_schet_san"];
	$date_z_schet = date_change($row["date_z"]);
	$date_v_schet = date_change($row["date_v"]);
	$number_turist = $row["number_turist"];
    $turist_mode = isset($_GET['turist_mode'])?(int)$_GET['turist_mode']:0;
    $row = $connect->getRow("SELECT arrival, leaving FROM object WHERE id=?i", $id_obj);
	$arrival = $row["arrival"];
	$leaving = $row["leaving"];
	$object = get_object($connect, $id_obj, "full_and_place");
	if($rest){
		$rest = explode(",", $rest);
		foreach($rest as $turist){
			if($turist){
				$row = $connect->getRow("SELECT surname, name, otch, passport, date, birth_certificate FROM klient WHERE id=?i", $turist);
				$fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
				if(!$trans)
					$trans = get_translit($row["surname"])."_p_".$id;
				$passport = $row["passport"];
				if(!$passport)
					$passport = $row["birth_certificate"];
				$date = str_replace("-", ".", date_change($row["date"]));
				$table.= "<tr>";
				$table.= "<td>".$fio."</td>";
				$table.= "<td width='105' align='center'>".$date."</td>";
				$table.= "<td width='100' align='center' valign='middle'>".$passport."</td>";
				$table.= "</tr>";
			}
		}
	}
	for($i=1; $i<=$index; $i++){
		if($index > 1){
			$number_schet = $id."/".$i;
		}else
			$number_schet = $id;

		$img = isset($_COOKIE["img"])?$_COOKIE["img"]:null;
		ob_start();

?>

	<table class="content" cellspacing="5">
	<tr>
		<td class="div1">
			<div>
			<table>
			<tr>
				<td valign="top"><img src="images/logo-site.png" style="float: left;" /></td>
				<td valign="top" style="text-align: center; width: 440px;">
					<span><?php echo $firma; ?></span><br />
					<span class="head_span">Реестровый номер <?php echo $reestr; ?></span><br />
					<span class="head_span"><strong>Тел.:</strong> <?php echo $tel; ?></span><br />
					<span class="head_span"><strong>Email:</strong> <?php echo $email; ?></span>
					<span class="head_span"><strong>Сайт:</strong> <?php echo $web_site; ?></span>
				</td>
			</tr>
			</table>
			</div>

			<p class="head">
                <?php if($turist_mode) { ?>
				ТУРИСТИЧЕСКАЯ ПУТЕВКА № <?php echo $number_schet; ?>
                <?php } else { ?>
                    ОБМЕННАЯ ПУТЕВКА № <?php echo $number_schet; ?>
                <?php } ?>
			</p>
			<?php if($schet_san){ ?>
				<p style="text-align: center; margin: 0">(счет № <?php echo $schet_san; ?> от <?php echo date_change($date_schet_san, "."); ?>)</p>
			<?php } ?>
			<br />
			<div><strong>Объект: </strong><?php echo $object; ?></div>
			<table border="1" cellspacing="0" cellpadding="5">
			<tr>
				<th align="center">Номер</th>
				<th align="center">Кол-во</th>
				<th align="center">Заезд</th>
				<th align="center">Выезд</th>
			</tr>
			<?php
				$data = $connect->getAll("SELECT date_z, days, id_room, number, id_service, note, add_one_day FROM position_reck WHERE schet=?i", $id);
				foreach($data as $row){
					$days = $row["days"];
					$date_z = date_change($row["date_z"]);
					$add_one_day = $row["add_one_day"];
					$date2 = strToTime($date_z);
					$date_z = str_replace("-", ".", $date_z);
					$note = $row["note"];
					$number = $row["number"];
					if($note)
						$note = " (".$note.")";
					$days_sum = $days;
					if($add_one_day == 0)
						$days_sum--;
					$date3 = date_sum($date2, $days_sum);
					$date_v = date("d-m-Y", $date3);
					$date_v = str_replace("-", ".", $date_v);
					$id_room = $row["id_room"];
					$id_service = $row["id_service"];
					if($id_service)
						$room = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $id_service);
					else
						$room = get_room($connect, $id_room, "full", "view_schet");
					if($days > $max_days){
						$max_days = $days;
						$max_date_v = $date_v;
					}
					echo "<tr>";
					echo "<td style='width: 320px;'>".$room." ".$note."</td>";
					echo "<td style='width: 50px;' align='center'>".$number."</td>";
					echo "<td style='width: 70px;' align='center'>".$date_z."</td>";
					echo "<td style='width: 70px;' align='center'>".$date_v."</td>";
					echo "</tr>";
				}
			?>
			</table>
			<br />
			<div>
				<?php if($note_bid){ ?>
				<strong>Примечание:</strong> <?php echo $note_bid; ?><br />
				<?php } ?>
				 <?php echo "<strong>Сроки путевки:</strong> ".str_replace("-", ".", $date_z_schet)." - ".str_replace("-", ".", $date_v_schet)."<br />"; ?>
				<?php if($arrival OR $leaving){ ?>
					<strong>Расчетный час:</strong> <?php echo "заезд ".$arrival.", выезд ".$leaving; ?><br />
				<?php } ?>
			</div>
			<div><strong>Отдыхающих: </strong><?php echo $number_turist; ?></div>
			<?php
				if(!$show_pay){
			?>
				<div><strong>Путевок на сумму:</strong> <?php echo $sum; ?>
					<?php
						$arr = explode(".", $sum);
						$itog_sum_string = convert_number_to_string($arr[0]);
						echo "(".$itog_sum_string.") ".$arr[1]." копеек";
					?>
				</div>
				<?php
					if($number_schet == 57676) {
						echo "<div><strong>Предоплата на сумму:</strong> 7500</div>";
						echo "<div><strong>Оплата на месте:</strong> 55000</div>";
					}
				?>
			<?php
				}
			?>
			<?php if(($id_obj == 10 OR $id_obj == 12) AND $status_san == 5){
				$prepay_sum = $connect->getOne("SELECT SUM(sum) FROM payment WHERE schet=?i AND type=1", $id);
				$raz_pay = $sum - $prepay_sum;
				$bonus = abs($connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum<0", $id));
				$prepay_sum = $prepay_sum + $bonus;
				$raz_pay = $raz_pay - $bonus;
			?>
				<div><strong>Особое условие при оплате:</strong>
					Оплата <?php echo add_null($prepay_sum); ?> рублей, остаток <?php echo add_null($raz_pay); ?> рублей на месте при заезде
				</div>
			<?php } ?>
			<?php if(count($services)){ ?>
				<div><strong>В стоимость путевки входит:</strong>
					<?=implode(', ', $services)?>
				</div>
			<?php } ?>
			<div>
			<table border="1" cellspacing="0" cellpadding="0">
			<tr>
				<th style="width: 325px;" align="center">Турист</th>
				<th style="width: 105px;" align="center">Дата рождения</th>
				<th style="width: 100px;" align="center">Документ</th>
			</tr>
			<?php echo $table; ?>
			</table>
			</div>
			<table cellpadding="0" cellspacing="0">
			<tr>
				<td width="200" valign="middle"><?php echo $post; ?></td>
				<td width="200" height="120">
				<?php if($img == 1){ ?>
					<img src="images/pechat/<?php echo $image; ?>.jpg" />
				<?php } ?>
				</td>
				<td valign="middle"><?php echo $director; ?></td>
			</tr>
			</table>
			<div class="sp_note" style="text-align: left">Дата: <?php echo $today; ?></div><br />
		<?php if($id_obj != 96 AND $id_obj != 61 AND $id_obj != 62 AND $id_obj != 63 AND $id_obj != 64 AND $id_obj != 71 AND $id_obj != 138 AND $id_obj != 67 AND $reduced != 1){ ?>
			<div style="margin: 0; font-size: 7pt;"><p style="margin: 0;"><strong>Внимание!</strong> Прием и размещение отдыхающих на лечение производится строго при наличии соответствующих документов:</p><br />
			<table cellspacing="0" cellpadding="1">
			<tr>
				<th align="center" valign="top">Взрослым:</th>
				<th align="center" valign="top">Детям:</th>
			</tr>
			<tr>
				<td style="width: 200px; padding: 0px; margin: 0px; vertical-align: top;">
					<ul style="margin: 0;">
                        <li>Справка об отсутствии контакта с больными COVID-19 в течении предшествующих 14 дней, выданная медицинской организацией не позднее, чем за 3 дня до отъезда.</li>
                        <li>Справку можно получить в поликлинике по месту жительства непосредственно перед отправлением в санаторий.</li>
						<li>общегражданский паспорт;</li>
						<li>обменная путевка;</li>
						<li>санаторно-курортная карта;</li>
						<li>пенсионерам - пенсионное удостоверение;</li>
						<li>полис медицинского страхования;</li>
						<li>для посещения бассейна (если без лечения) - наличие справки от дерматовенеролога (кожника);</li>
					</ul>
				</td>
				<td style="width: 350px; padding: 0px; margin: 0px; vertical-align: top;" valign="top">
					<ul style="margin: 0;">
                        <li>Справка об отсутствии контакта с больными COVID-19 в течении предшествующих 14 дней, выданная медицинской организацией не позднее, чем за 3 дня до отъезда.</li>
                        <li>Справку можно получить в поликлинике по месту жительства непосредственно перед отправлением в санаторий.</li>
						<li>санаторно-курортная карта;</li>
						<li>свидетельство о рождении;</li>
						<li>полис обязательного медицинского страхования ребенка;</li>
						<li>справка о прививках (прививочная карта);</li>
						<li>справка об исследовании на энтеробиоз (месячной давности);</li>
					</ul>
				</td>
			</tr>
			</table>
			</div>
			<div style="margin: 0; font-size: 8pt;"><p style="margin: 0;"><strong>При выезде из санатория.</strong> Не забудьте взять документы, необходимые для получения социального налогового вычета по оплате медицинских услуг:</p>
				<ul>
				<li>отрывной талон санаторно-курортной путевки;</li>
				<li>копия лицензии санаторно-курортного учреждения, заверенная печатью учреждения;</li>
				<li>справка об оплате медицинских услуг для налоговых органов.</li>
				</ul>
			</div>
		<?php }else{ ?>
			<!--<div style="margin: 0; font-size: 7pt;"><p style="margin: 0;"><strong>Внимание!</strong> Прием и размещение отдыхающих производится строго при наличии соответствующих документов:</p><br />
			<table cellspacing="0" cellpadding="1">
			<tr>
				<td style="width: 200px; padding: 0px; margin: 0px; vertical-align: top;">
					<ul style="margin: 0;">
						<li>общегражданский паспорт;</li>
						<li>обменная путевка;</li>
						<li>полис медицинского страхования;</li>
					</ul>
				</td>
			</tr>
			</table>
		</div>-->
		<?php } ?>
		</td>
		<td class="div2">
			<div>
			<table>
			<tr>
				<td valign="top"><img src="images/logo-site.png" style="float: left;" /></td>
				<td style="text-align: center; width: 388px;" valign="top">
					<span><?php echo $firma; ?></span><br />
					<span class="head_span">Реестровый номер <?php echo $reestr; ?></span><br />
					<span class="head_span"><strong>Тел.:</strong> <?php echo $tel; ?><br /><strong>Факс:</strong> <?php echo $fax; ?></span><br />
					<span class="head_span"><strong>Email:</strong> <?php echo $email; ?></span>
					<span class="head_span"><strong>Сайт:</strong> <?php echo $web_site; ?></span>
				</td>
			</tr>
			</table>
			</div>
			<p class="head">ОБРАТНЫЙ ТАЛОН</p>
			<p class="sp_note">к обменной путевке № <?php echo $number_schet; ?><br /><?php if(!$agency) echo "(заполняется ".$firma.")"; ?></p><br />
			<strong>Объект:</strong>
			<?php echo $object; ?><br />
			<div>
			<table border="1" cellspacing="0" cellpadding="5">
			<tr>
				<th style='width: 255px;' align="center">Турист</th>
				<th style='width: 105px;' align="center">Дата рождения</th>
				<th style='width: 130px;' align="center">Документ</th>
			</tr>
				<?php echo $table; ?>
			</table>
			</div><br />
			<strong>Срок действия путевки:</strong><br /><br />
			с <?php echo str_replace("-", ".", $date_z_schet)." по ".str_replace("-", ".", $date_v_schet); ?><br /><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;М.П.<br /><br />
			<div class="sp_note" style="text-align: left">Подпись ответственного лица от санатория __________________<br /><br /><br />Дата: <?php echo $today; ?></div>
		</td>
	</tr>
	</table>

<style type="text/css">

.content{
	font-family: freesans, sans-serif;
	font-size: 9pt;
	width: 900px;
	margin: 0 auto;
}

.head{
	text-align: center;
	font-size: 11pt;
	font-weight: bold;
	margin: 0px;
	display: block;
}

.head_span{
	font-size: 10pt;
}

.div1{
	width: 537px;
	height: 710px;
	border-right: 2px dashed black;
	padding: 0px;
	vertical-align: top;
    font-size: 10px;
}

.div2{
	width: 487px;
	height: 710px;
	padding: 0px;
	vertical-align: top;
	text-align: left;
    font-size: 10px;
}

.sp_border{
	display: block;
	min-height: 20px;
	border-bottom: 1px solid black;
}

.sp_note{
	text-align: center;
	margin: 0;
	font-size: 9pt;

}

</style>

<?php
		$content = ob_get_clean();
	}
	if($type == "HTML")
		echo $content;
	elseif($type == "PDF"){
		include($directory."/core/lib/html2PDF/html2pdf.class.php");
		$pdf = new HTML2PDF("L", "A4", "en", array(0, 0, 0, 0), "UTF-8");
        //$pdf->setTestTdInOnePage(false);
		$pdf->WriteHTML($content);
		if($for == "email"){
			$file = $directory."/temp/forms/putevka".$id.".pdf";
			$pdf->Output($file, "F");
			return $file;
		}else
			$pdf->Output($trans.".pdf");
	}
}

?>
