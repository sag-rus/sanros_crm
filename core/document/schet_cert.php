<?php

function review_schet_certificate($connect, $type, $id){
	global $directory;

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

	$row = $connect->getRow("SELECT sum, klient, payer FROM certificate WHERE id=?i", $id);
	$klient = $row["klient"];
	$sum = $row["sum"];
	$number = 1;
	$payer = $row["payer"];
	$payer = $connect->getOne("SELECT name FROM payer WHERE id=?i", $payer);
	$itog_sum = $sum * $number;
	$sum = add_null($sum);
	$itog_sum = add_null($itog_sum);
	$table.= "<tr><td width='30' align='center'>1</td>";
	$table.= "<td width='706'>Подарочный сертификат на сумму ".$sum." рублей</td>";
	$table.= "<td width='40' align='center'>шт</td>";
	$table.= "<td width='40' align='center'>".$number."</td>";
	$table.= "<td width='70' align='center'>".str_replace(".", "-", $sum)."</td>";
	$table.= "<td width='70' align='center'>".str_replace(".", "-", $itog_sum)."</td></tr>";
	ob_start();
?>
	<div class="content">
	<table cellpadding="5" cellspacing="0">
	<tr>
		<td style="border: none; width: 550px;"><p style="margin: 0px;"><?php echo $firma; ?><br />
		<strong>Адрес:</strong> <?php echo $leg_address; ?><br />
		<strong>Тел.:</strong> <?php echo $tel; ?> <strong>Факс:</strong> <?php echo $fax; ?><br />
		<strong>Email:</strong> <?php echo $email; ?> <strong>Сайт:</strong> <?php echo $web_site; ?></p>
		</td>
	<td  style="border: none;" valign="top"><span class="bold_head" style="color: #DB0E0E;">ДЕЙСТВИТЕЛЕН В ТЕЧЕНИИ 3 РАБОЧИХ ДНЕЙ</span></td>
	</tr>
	</table>
	<p class="bold_head" style="color: #DB0E0E; margin: 3px 0;">ВНИМАНИЕ! ЗАПОЛНИТЬ ПРАВИЛЬНО: <?php echo $firma; ?></p>
	<table cellpadding="5" cellspacing="0">
	<tr>
		<td width="330">ИНН <?php echo $INN; ?></td>
		<td width="330">КПП <?php echo $KPP; ?></td>
		<td width="40" align="center" rowspan="2" valign="middle">Сч. №</td>
		<td width="300" rowspan="2" valign="middle"><?php echo $reck; ?></td>
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
	<p><span class="bold_head">СЧЕТ № <?php echo $id." от ".date("d.m.Y"); ?></span><br />
	Заказчик: <?php echo $payer; ?><br />
	Плательщик: <?php echo $payer; ?></p>
	<table cellpadding="5" cellspacing="0">
	<tr>
		<th width="30">№</th>
		<th width="726">Наименование санаторно-курортных услуг</th>
		<th width="40">Ед/изм</th>
		<th width="40">Кол-во</th>
		<th width="70">Цена</th>
		<th width="70">Сумма</th>
	</tr>
		<?php echo $table; ?>
	<tr>
		<td rowspan="4" colspan="3" style="text-align: left; border: none;"><br />Всего наименований <?php echo $number; ?>, на сумму <?php echo str_replace(".", "-", $itog_sum); ?> рублей<br />
			<?php
				$arr = explode(".", $itog_sum);
				$itog_sum_string = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
				$itog_sum_string = first_symbol_to_title($itog_sum_string);
				echo $itog_sum_string;
			?>
			<br />
			<table class="head" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td width="250" valign="bottom">Руководитель предприятия</td>
				<td width="200" rowspan="2" height="100">
					<img src='images/pechat/pechat.jpg' />
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
		<td align="center"><?php echo str_replace(".", "-", $itog_sum); ?></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right; border: none"><strong>Без налога (НДС):</strong></td>
		<td align="center">-</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right; border: 1px solid #fff;"><strong>Всего к оплате:</strong></td>
		<td align="center"><?php echo str_replace(".", "-", $itog_sum); ?></td>
	</tr>
	<tr>
		<td colspan="3" style="border: none;" height="100"></td>
	</tr>
	<?php
		for($i=1; $i<=$col_str; $i++)
			echo "<tr><td colspan='3' style='border: none;'>&nbsp;<br /></td></tr>";
	?>
	</table>
	</div>
	
<style type="text/css">

.content{
	font-family: freesans, sans-serif;
	width: 800px;
	font-size: 9.5pt;
	margin: 0 auto;
}

td, th{
	padding: 2px;
	font-size: 9.5pt;
	vertical-align: middle;
	border: 1px solid black;
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
	if($type == "HTML")
		echo $content;
	elseif($type == "PDF"){
		include($directory."/core/lib/html2PDF/html2pdf.class.php"); 
		$pdf = new HTML2PDF("L", "A4", "en", array(0, 0, 0, 0), "UTF-8");
		$pdf->WriteHTML($content); 
		if($type_PDF == "email"){
			$file = "temp/forms/".$trans.".pdf";
			$pdf->Output($file, 'F');
			return $file;
		}else
			$pdf->Output($trans.".pdf");
	}
}

?>
