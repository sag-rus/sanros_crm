<?php

function review_forma_certificate($connect, $type, $id){
	global $directory;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
	$director = $conf->director;

	$row = $connect->getRow("SELECT klient, code, sum, date_pay FROM certificate WHERE id=?i", $id);
	$key = $row["code"];
	$sum = $row["sum"];
    $date_pay = NULL;
	if(!is_null($row["date_pay"])) $date_pay = date("d.m.Y",strtotime($row["date_pay"]));
	ob_start();
?>

	<div class="border" style="display: block; background-repeat: no-repeat; background-size: 100%;">
        <div style="text-align: center; width: 690px; margin-top: 100px;"><img src="<?=$directory?>/images/sanata_logo.png"></div>
		<br /><br /><br /><br />
        <p style="text-align: center; font-size: 24pt; color: #3e2d27; margin-bottom: 0; margin-top: 0;">ПОДАРОЧНЫЙ</p>
		<p style="text-align: center; font-size: 40pt; font-weight: bold; color: #3e2d27; margin-bottom: 0; margin-top: 10px;">СЕРТИФИКАТ</p>
        <p style="text-align: center; font-size: 24pt; color: #3e2d27; margin-bottom: 0; margin-top: 150px;">№<span style="text-decoration: underline; margin-left: 10px;"><?php echo $key; ?></span></p>
        <p style="text-align: center; font-size: 24pt; color: #3e2d27; margin-bottom: 0; margin-top: 10px;">на сумму</p>
        <p style="text-align: center; font-size: 24pt; color: #3e2d27; margin-bottom: 0; margin-top: 10px;"><span style="text-decoration: underline; margin-left: 10px;"><?php echo $sum; ?> рублей</span></p>
        <p style="text-align: center; font-size: 12pt; color: #3e2d27; margin-bottom: 0; margin-top: 30px;">Сертификат действителен при оплате путевки в любой санаторий России через компанию ООО ТА «САНАТА-ТРЕВЕЛ»</p>
        <p style="text-align: centНовая заявкаer; font-size: 12pt; color: #3e2d27; margin-bottom: 0; margin-top: 10px;">Действителен до 31.12.<?=(date("Y")+1)?></p>
        <table align="center" style="width: 650px; margin-top: 180px;">
        <tr>
            <td colspan="3">
                <?php if(!is_null($date_pay)) { ?>Дата <?=$date_pay;?><?php } ?>
            </td>
        </tr>
		<tr>
			<td style="width: 250px" style="vertical-align: middle">Генеральный директор<br /><?php echo $firma; ?></td>
			<td style="width: 200px" style="vertical-align: middle"><img src="images/pechat/pechat1.jpg" /></td>
			<td style="width: 200px" style="vertical-align: middle"><?php echo $director; ?></td>
		</tr>
		</table>
	</div>

<style type="text/css">

.border{
	font-family: freesans, Times;
	padding: 20px;
	height: 1029px;
	width: 710px;
	margin: 0 auto;
    border: 2px solid #3e2d27;
}

.atomic-central {
    display: block;
    margin-left: auto;
    margin-right: auto;
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
