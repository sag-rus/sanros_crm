<?php

function review_napravlenie($connect, $id){
	global $directory;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
	$today = date("d.m.Y");

	$row = $connect->getRow("SELECT id_obj, date_z, date_v, rest, schet_san, date_schet_san FROM reckoning WHERE id=?i", $id);
	$object = get_object($connect, $row["id_obj"]);
	$rest = explode(",", $row["rest"]);
	foreach($rest as $turist){
		if($turist)
			break;
	}
	$fio = select_name_klient($connect, $turist);
	$schet_san = $row["schet_san"];
	$date_schet_san = month_transform($row["date_schet_san"]);
	$date_z = month_transform($row["date_z"]);
	$date_v = month_transform($row["date_v"]);
	$payment_array = get_payment($connect, $id, 4);
	$payment = $payment_array[1];
	ob_start();
?>

	<div class="border">
		<p style="text-align: center; font-size: 11pt;"><?php echo $firma; ?><hr /><span style="font-size: 9pt;">(наименование, адрес организации, реквизиты)</span></p>
		
		<br /><br /><br />
		<p style="text-align: center; font-size: 14pt; font-weight: bold;">НАПРАВЛЕНИЕ №</p>
		<br />
		<p><?php echo $firma; ?> направляет на санаторно-курортное лечение в<br />(наименование организации)</p>
		<br />
		<table>
		<tr>
			<td width="150"><?php echo $object; ?></td>
			<td><?php echo $fio; ?><br />(Ф.И.О. направленного на лечение)</td>
		</tr>
		</table>
		<br />
		<table>
		<tr>
			<td width="150">C <?php echo $date_z; ?></td>
			<td>по  <?php echo $date_v; ?></td>
		</tr>
		<tr>
			<td width="150">Cчет № <?php echo $schet_san; ?></td>
			<td>от <?php echo $date_schet_san; ?></td>
		</tr>
		<tr>
			<td width="150">П/пор № <?php echo $payment['pay_number']; ?></td>
			<td>от <?php echo $payment['date']; ?></td>
		</tr>
		</table>
		<br /><br /><br /><br />
		<table>
		<tr>
			<td width="300"><img src='images/pechat/pechat.jpg' /></td>
			<td width="200" valign="middle">Дата <?php echo $today; ?></td>
		</tr>
		</table>
	</div>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	padding: 70px;
	height: 820px;
	width: 650px;
	margin: 0 auto;
	font-size: 10pt;
}

</style>

<?php
	$content = ob_get_clean();
	$type = "PDF";
	if($type == "HTML")
		echo $content;
	elseif($type == "PDF"){
		include($directory."/core/lib/html2PDF/html2pdf.class.php"); 
		$pdf = new HTML2PDF("H", "A4", "en", array(0, 0, 0, 0), "UTF-8");
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
