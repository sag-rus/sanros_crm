<?php

function review_cancel($connect, $type, $id){
	global $directory;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
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

	$row = $connect->getRow("SELECT cause, note FROM cancellation WHERE schet=?i", $id);
	$cause = $row["cause"];
	$note = $row["note"];
	$row = $connect->getRow("SELECT reckoning.id_obj, position_reck.id_room, reckoning.turist, reckoning.rest, DATE_FORMAT(reckoning.date_z, '%d.%m.%Y') as date_z, reckoning.id_user, DATE_FORMAT(reckoning.date_schet_san, '%d.%m.%Y') as date_schet_san, reckoning.schet_san FROM reckoning, position_reck WHERE reckoning.id=?i AND position_reck.schet=reckoning.id", $id);
	$date_z = $row["date_z"];
	$id_obj = $row["id_obj"];
	$id_room = $row["id_room"];
	$turist = array_diff(explode(",", $row["rest"]), array(""));
	$turist = $turist[0];
	$manager = $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]);
	$san_date = $row["date_schet_san"];
	$san_schet = $row["schet_san"];
	$row = $connect->getRow("SELECT name, surname, otch FROM klient WHERE id=?i", $turist);
	$fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
	$trans = get_translit($row["surname"]);
	$object = get_object($connect, $id_obj, "full");
	$room = get_room($connect, $id_room);
	$img = $_COOKIE["img"];
	$date_can = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date FROM history_schet WHERE id_schet=?i AND new_status=6 ORDER BY id LIMIT 1", $id);
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
				<strong>Адрес:</strong> <?php echo $leg_address; ?><br />
				<strong>Реестровый номер:</strong> <?php echo $reestr; ?><br />
				<strong>Тел.:</strong> <?php echo $tel; ?> <strong>Факс:</strong> <?php echo $fax; ?><br />
				<strong>Email:</strong> <?php echo $email; ?><br />
				<strong>Сайт:</strong> <?php echo $web_site; ?></p>
			</td>
		</tr>
		</table>
		<p style="text-align: center; font-size: 17pt; margin: 0px;">АННУЛЯЦИЯ</p>

		<p><strong>Куда:</strong> <?php echo $object; ?><br /><br />
		Просим Вас аннулировать счет №<?php echo $san_schet." от ".$san_date; ?><br />
		<?php if($date_can){ ?>
		<strong>Дата аннуляции:</strong> <?php echo str_replace("-", "." , $date_can); ?><br />
		<?php } ?>
		<strong>Турист:</strong> <?php echo $fio; ?><br />
		<strong>Дата заезда:</strong> <?php echo $date_z; ?><br />
		<strong>Номер:</strong> <?php echo $room; ?><br />
		<strong>Причина:</strong> <?php echo $cause; ?><br />
		<?php if($note) echo "<strong>Примечание:</strong> ".$note."<br /><br />"; else echo "<br />"; ?>
		Приносим свои извинения.</p>
		<table>
		<tr>
			<td valign="middle">Руководитель предприятия</td>
			<td width="200" height="120">
			<?php if($img == 1)
				echo "<img src='images/pechat/pechat1.jpg' />";
			?>
			</td>
			<td valign="middle">(<?php echo $director; ?>)</td>
		</tr>
		<tr>
			<td width="100" colspan="2"></td>
			<td style="width: 235px; text-align: right;">С уважением, <?php echo $manager; ?></td>
		</tr>
		</table>
	</div>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	font-size: 13pt;
	padding: 20px;
	border: 1px solid black;
	height: 1030px;
	width: 705px;
	margin: 0 auto;
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
		$pdf = new HTML2PDF("H", "A4", "en", array(0, 0, 0, 0), "UTF-8");
		$pdf->WriteHTML($content);
		$pdf->Output($trans.".pdf");
	}
}

?>
