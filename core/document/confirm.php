<?php

function review_confirm($connect, $type, $id, $type_PDF = "PDF"){
	global $directory;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
	$full_firma = $conf->full_firma;
	$email = $conf->Email;
	$leg_address = $conf->leg_address;
	$sep_address = $conf->sep_address;
	$tel = $conf->tel_firma;
	$fax = $conf->fax_firma;
	$web_site = $conf->web_site;
	$INN = $conf->INN;
	$KPP = $conf->KPP;
	$director = $conf->director;
	$reestr = $conf->reestr;
	$today = date("d.m.Y");

	$row = $connect->getRow("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date, id_obj, turist, rest, id_user FROM reckoning WHERE id=?i", $id);
	$turist = $row["turist"];
	$rest = $row["rest"];
	$date = $row["date"];
	$object = get_object($connect, $row["id_obj"], "type");
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$img = $_COOKIE["img"];

	$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $row["id_user"]);
	$row = $connect->getRow("SELECT address, telephone FROM office WHERE id=?i", $office);
	if($row["address"]){
		$tel = $row["telephone"];
		$sep_address = $row["address"];
	}

	ob_start();
?>
	<div class="border">
		<table>
		<tr>
			<td style="width: 600px"><img src="images/logo-menu.png" style="float: left;" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<p style="font-size: 11pt;"><strong><?php echo $firma; ?></strong><br />
				<strong>ИНН/КПП:</strong> <?php echo $INN; ?>/<?php echo $KPP; ?><br />
				<strong>Юридический адрес:</strong> <?php echo $leg_address; ?><br />
				<strong>Обособленное подразделение:</strong> <?php echo $sep_address; ?><br />
				<strong>Реестровый номер:</strong> <?php echo $reestr; ?><br />
				<strong>Тел.:</strong> <?php echo $tel; ?> <strong>Факс:</strong> <?php echo $fax; ?><br />
				<strong>Email:</strong> <?php echo $email; ?><br />
				<strong>Сайт:</strong> <?php echo $web_site; ?></p>
			</td>
		</tr>
		</table><br />
		<p style="text-align: center; font-size: 17pt; margin: 0px;">ПОДТВЕРЖДЕНИЕ БРОНИРОВАНИЯ</p>
		<p style="margin-top: 0px;"><strong>Заявка № <?php echo $id; ?></strong><br />
		<strong>Дата заявки:</strong> <?php echo $date; ?><br /></p>

		<table border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th style="width: 170px;">Размещение</th>
			<th style="width: 65px;">Заезд</th>
			<th style="width: 65px;">Выезд</th>
			<th style="width: 60px;">Дней</th>
			<th style="width: 250px;">Тип номера</th>
		</tr>
		<?php
			$data = $connect->getAll("SELECT days, DATE_FORMAT(date_z, '%d.%m.%Y') as date, id_room, note, sum, type, add_one_day FROM position_reck WHERE schet=?i", $id);
			foreach($data as $row){
				$id_room = $row['id_room'];
				$note = $row['note'];
				$days = $row['days'];
				$sum = $row['sum'];
				$add_one_day = $row["add_one_day"];
				if($add_one_day != 1)
					$days--;
				$date_z = $row["date"];
				$date2 = strToTime($row["date"]);
				$d = date_sum($date2, $days);
				$date_v = date("d.m.Y", $d);
				if($row["type"] == 1)
					$type_price = "за чел/сутки";
				elseif($row["type"] == 2)
					$type_price = "за номер";
				elseif($row["type"] == 3)
					$type_price = "за заезд";
				$days_sum = $days;
				if($add_one_day == 0)
					$days_sum--;
				$room = get_room($connect, $id_room, "view_schet");
				echo "<tr>";
				echo "<td style='width: 170px;'>".$object."</td>";
				echo "<td style='width: 65px;'>".$date_z."</td>";
				echo "<td style='width: 65px;'>".$date_v."</td>";
				echo "<td style='width: 60px;' align='center'>".$row["days"]."</td>";
				echo "<td style='width: 250px;'>".$room.$note." ".$sum." руб. ".$type_price."</td>";
				echo "</tr>";
			}
		?>
		</table>
		<p style="text-transform: uppercase; font-weight: bold;">ИНФОРМАЦИЯ О КЛИЕНТАХ:</p>
		<table border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th style="width: 420px;">Фамилия Имя Отчество</th>
			<th style="width: 110px;">Дата рождения</th>
			<th style="width: 120px;">Номер документа</th>
		</tr>
		<?php
			if($rest){
				$rest = explode(",", $rest);
				foreach($rest as $turist){
					if($turist){
						$row = $connect->getRow("SELECT surname, name, otch, passport, date, birth_certificate FROM klient WHERE id=?i", $turist);
						$fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
						$trans = get_translit($row["surname"])."_".$id;
						$passport = $row["passport"];
						if(!$passport)
							$passport = $row["birth_certificate"];
						$date_b = date_change($row["date"], ".");
						echo "<tr>";
						echo "<td>".$fio."</td>";
						echo "<td style='width: 110px; text-align: center;'>".$date_b."</td>";
						echo "<td style='width: 120px; text-align: center;'>".$passport."</td>";
						echo "</tr>";
					}
				}
			}
		?>
		</table>
		<br />
		<table cellpadding="0" cellspacing="0">
		<tr>
			<td>Руководитель предприятия</td>
			<td width="200" height="120">
			<?php if($img == 1)
				echo "<img src='images/pechat/pechat1.jpg' />";
			?>
			</td>
			<td>(<?php echo $director; ?>)</td>
		</tr>
		<tr>
			<td width="700" colspan="3" align="right"><p style="font-size: 11pt; text-align: right;"><strong>С уважением, <?php echo $manager; ?></strong><br />Email: <?php echo $email; ?><br />Факс: +7(843)<?php echo $fax; ?></p></td>
		</tr>
		</table>

	</div>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	padding: 20px;
	border: 1px solid black;
	height: 1020px;
	width: 685px;
	margin: 0 auto;
}

td{
	padding: 4px;
	font-size: 11pt;
	vertical-align: middle;
}

th{
	padding: 4px;
	text-align: center;
	font-weight: bold;
	background: #a4a4a4;
	color: #fff;
}


</style>

<?php
	$content = ob_get_clean();
	if($type == "HTML")
		echo $content;
	elseif($type == "PDF"){
		include_once($directory."/core/lib/html2PDF/html2pdf.class.php");
		$pdf = new HTML2PDF("P", "A4", "en", array(0, 0, 0, 0), "UTF-8");
		$pdf->WriteHTML($content);
		if($type_PDF == "email"){
			$file = "temp/forms/".$trans.".pdf";
			$pdf->Output($file, "F");
			return $file;
		}else
			$pdf->Output($trans.".pdf");
	}
}

?>
