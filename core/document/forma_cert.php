<?php

function review_forma_certificate($connect, $type, $id){
	global $directory;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
	$director = $conf->director;

	$row = $connect->getRow("SELECT klient, code, sum FROM certificate WHERE id=?i", $id);
	$key = $row["code"];
	$sum = $row["sum"];
	ob_start();
?>

	<div class="border">
		<p style="text-align: center; font-size: 18pt;">Единая Служба Бронирования<br />«КурортИнфо»</p>
		<br /><br /><br /><br /><br />
		<p style="text-align: center; font-size: 21pt; font-weight: bold;">СЕРТИФИКАТ</p>
		<p style="text-align: center; font-size: 15pt;">Код №<?php echo $key; ?></p>
		<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
		<p style="text-align: center; font-size: 13pt;">Сертификат дает право приобретения туристических услуг<br />от компании <?php echo $firma ;?> на сумму<br /><br /><span style="font-size: 21pt; font-weight: bold;"><?php echo $sum; ?> рублей</span></p>
		<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
		<table align="center" style="width: 650px">
		<tr>
			<td style="width: 250px" style="vertical-align: middle">Генеральный директор<br /><?php echo $firma; ?></td>
			<td style="width: 200px" style="vertical-align: middle"><img src="images/pechat/pechat1.jpg" /></td>
			<td style="width: 200px" style="vertical-align: middle"><?php echo $director; ?></td>
		</tr>
		</table>
		<br /><br /><br /><br /><br /><br />
		<p style="text-align: center; font-size: 11pt;">Действителен до 31.12.2017</p>
	</div>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	padding: 20px;
	height: 1020px;
	width: 710px;
	margin: 0 auto;
}

</style>

<?php
	$content = ob_get_clean();
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
