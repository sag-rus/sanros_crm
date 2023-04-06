<?php

function review_bron($connect, $type, $id, $type_PDF = "PDF"){
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

	$row = $connect->getRow("SELECT id_obj, turist, id_user, rest, number_turist, note_bid FROM reckoning WHERE id=?i", $id);
	$turist = $row["turist"];
	$id_obj = $row["id_obj"];
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$rest = $row["rest"];
	$number_turist = $row["number_turist"];
	$note_bid = $row["note_bid"];
	$object = get_object($connect, $id_obj, "full");
	$img = $_COOKIE["img"];
	$trans = "";

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
			<td valign="top"><span style="font-weight: bold; font-size: 16pt;">№<?php echo $id; ?></span></td>
		</tr>
		<tr>
			<td colspan="3">
				<p style="font-size: 11pt;"><strong><?php echo $firma; ?></strong><br />
				<strong>ИНН/КПП:</strong> <?php echo $INN; ?>/<?php echo $KPP; ?><br />
				<strong>Юридический адрес:</strong> <?php echo $leg_address; ?><br />
                <strong>Почтовый/Фактический адрес:</strong> <?php echo $leg_address; ?><br />
                <strong>Реестровый номер:</strong> <?php echo $reestr; ?><br />
				<strong>Тел.:</strong> <?php echo $tel; ?> <br />
				<strong>Email:</strong> <?php echo $email; ?><br />
				<strong>Сайт:</strong> <?php echo $web_site; ?></p>
			</td>
		</tr>
		</table>
		<p style="text-align: center; font-size: 17pt; margin: 0px;">ЛИСТ БРОНИРОВАНИЯ</p>

		<p style="margin-top: 0px;"><strong>Для:</strong> Отдел Реализации<br />
		<strong>Дата заявки:</strong> <?php echo $today; ?><br />
		</p>

		<table border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th style="width: 160px;">Размещение</th>
			<th style="width: 55px;">Заезд</th>
			<th style="width: 55px;">Выезд</th>
			<th style="width: 40px;">Дней</th>
			<th style="width: 35px;">Кол-во</th>
			<th style="width: 100px;">Тип номера</th>
			<th style="width: 65px;">Цена</th>
			<th style="width: 40px;">Прим</th>
		</tr>
		<?php
			$data = $connect->getAll("SELECT number, days, date_z, id_room, id_service, note, sum, type, add_one_day FROM position_reck WHERE schet=?i", $id);
			foreach($data as $row){
				$id_room = $row["id_room"];
				$id_service = $row["id_service"];
				$note = $row["note"];
				$days = $row["days"];
				$number = $row["number"];
				$add_one_day = $row["add_one_day"];
				$date_z = date_change($row["date_z"]);
				$date2 = strToTime($date_z);
				$date_z = str_replace("-", ".", $date_z);
				$sum = $row["sum"];
				if($row["type"] == 1)
					$type_price = "за чел/сутки";
				elseif($row["type"] == 2)
					$type_price = "за номер";
				elseif($row["type"] == 3)
					$type_price = "за заезд";
				$days_sum = $days;
				if($add_one_day == 0)
					$days_sum--;
				$d = date_sum($date2, $days_sum);
				$date_v = date("d-m-Y", $d);
				$date_v = str_replace("-", ".", $date_v);
				$room = get_room($connect, $row["id_room"], "full", "view_schet");
				if($id_service)
					$room = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $id_service);
				echo "<tr>";
				echo "<td style='width: 140px;'>".$object."</td>";
				echo "<td style='width: 48px;'>".$date_z."</td>";
				echo "<td style='width: 48px;'>".$date_v."</td>";
				echo "<td style='width: 40px;' align='center'>".$days."</td>";
				echo "<td style='width: 28px;' align='center'>".$number."</td>";
				echo "<td style='width: 80px;'>".$room."</td>";
				echo "<td style='width: 40px;'>".$sum." руб. ".$type_price."</td>";
				echo "<td style='width: 40px;'>".$note."</td>";
				echo "</tr>";
			}
		?>
		</table>
		<br />
		<p style="text-transform: uppercase; font-weight: bold;">ИНФОРМАЦИЯ О КЛИЕНТАХ:</p>
		<table border="1" cellpadding="5" cellspacing="0">
		<tr>
			<th style="width: 20px;">№</th>
			<th style="width: 380px;">Фамилия Имя Отчество</th>
			<th style="width: 100px;">Дата рождения</th>
			<th style="width: 100px;">Номер документа</th>
		</tr>
		<?php
			$index = 0;
			$num = 0;
			if($rest){
				$rest = explode(",", $rest);
				$rest = array_diff($rest, array(""));
				foreach($rest as $turist){
					$index++;
					$row = $connect->getRow("SELECT surname, name, otch, passport, date, birth_certificate FROM klient WHERE id=?i", $turist);
					$fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
					if(!$trans)
						$trans = get_translit($row["surname"])."_".$id;
					$num++;
					$passport = $row["passport"];
					if(!$passport)
						$passport = $row["birth_certificate"];
					$date_b = date_change($row["date"]);
					$date_b = str_replace("-", ".", $date_b);
					echo "<tr>";
					echo "<td style='text-align: center;'>".$index."</td>";
					echo "<td>".$fio."</td>";
					echo "<td style='width: 110px; text-align: center;'>".$date_b."</td>";
					echo "<td style='width: 120px; text-align: center;'>".$passport."</td>";
					echo "</tr>";
				}
			}
			$raz = $number_turist - $num;
			if($raz > 50)
				$raz = 50;
			if($raz > 0){
				for($i = 1; $i <= $raz; $i++){
					$index++;
					echo "<tr>";
					echo "<td style='text-align: center;'>".$index."</td>";
					echo "<td>Турист уточняется</td>";
					echo "<td style='width: 110px;'></td>";
					echo "<td style='width: 120px;'></td>";
					echo "</tr>";
				}
			}
		?>
		</table>
		<?php if($note_bid){ ?>
			<br />
			<table border="1" cellpadding="5" cellspacing="0">
			<tr>
				<th style="width: 690px;">Примечание</th>
			</tr>
			<tr>
				<td style="width: 690px;"><?php echo $note_bid; ?></td>
			</tr>
			</table>


		<?php } ?>
		<br />
		<table cellpadding="0" cellspacing="0">
		<tr>
			<td>Руководитель предприятия</td>
			<td width="200" height="120">
			<?php if($img == 1)
				echo "<img src='images/pechat/pechat1new.jpg' />";
			?>
			</td>
			<td>(<?php echo $director; ?>)</td>
		</tr>
		<tr>
			<td width="700" colspan="3" align="right"><p style="font-size: 11pt; text-align: right;"><strong>Прошу Вас выставить счёт!<br /><span>Заранее благодарю,</span> <?php echo $manager; ?></strong><br />Email: <?php echo $email; ?></p></td>
		</tr>
		</table>
		<br /><br /><br />
		<p style="font-size: 14pt; text-align: center">Просим Вас отправлять счета на электронную почту <strong>2602323@2602323.ru</strong></p>

	</div>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	padding: 20px;
	border: 1px solid black;
	height: 1000px;
	width: 685px;
	margin: 0 auto;
}

td{
	padding: 4px;
	font-size: 9pt;
	vertical-align: middle;
}

th{
	padding: 4px;
	font-size: 9pt;
	text-align: center;
	font-weight: bold;
	background: #a4a4a4;
	color: #fff;
}

p{
	margin: 0 8px;
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
		if($type_PDF == "for_email"){
			$file = $directory."/temp/forms/bron".$id.".pdf";
			$pdf->Output($file, "F");
			return $file;
		}else
			$pdf->Output($trans."_l.pdf");
	}

}

?>
