<?php

function review_dover($connect, $type, $id, $turist, $type_PDF = "PDF"){
	global $directory;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
	$email = $conf->Email;
	$leg_address = $conf->leg_address;
	$INN = $conf->INN;
	$KPP = $conf->KPP;
	$BIK = $conf->BIK;
	$KS = $conf->KS;
	$bank = $conf->bank;
	$reck = $conf->reck;
	$director = $conf->director;
	$booker = $conf->booker;

	$today = date("d.m.Y");
	$table = "";

	$prop = array(1 => "Один", 2 => "Два", 3 => "Три", 4 => "Четыре", 5 => "Пять", 6 => "Шесть", 7 => "Семь", 8 => "Восемь", 9 => "Девять", 10 => "Десять");
	$row = $connect->getRow("SELECT id_obj, DATE_FORMAT(date_z, '%d.%m.%Y') as date_z, DATE_FORMAT(date_v, '%d.%m.%Y') as date_v, manager, rest, number_turist, DATE_FORMAT(date_schet_san, '%d.%m.%Y') as date_schet_san, schet_san, turist, note_bid FROM reckoning WHERE id=?i", $id);
	$id_obj = $row["id_obj"];
	$note_bid = $row['note_bid'];
	$number_turist = $row["number_turist"];
	$date_z = $row["date_z"];
	$date_v = $row["date_v"];
	$date_san = $row["date_schet_san"];
	$schet_san = $row["schet_san"];
	if(!$schet_san)
		$schet_san = "<span style='color:red;'>не указан № счета санатория</span>";
	if(!$turist)
		$turist = $a['turist'];
	$row = $connect->getRow("SELECT surname, name, otch, passport, output, DATE_FORMAT(date_pas, '%d.%m.%Y') as date_pas FROM klient WHERE id=?i", $turist);
	$fio = $row["surname"]." ".$row["name"]." ".$row["otch"];
	$trans = get_translit($row["surname"])."_".$id;
	$passport = substr_replace($row["passport"], ' ', 4, 0);
	$output = $row["output"];
	$date_pass = $row["date_pas"];
	$img = $_COOKIE["img"];
	$index = 0;
	$row = $connect->getRow("SELECT id_room, days, number FROM position_reck WHERE schet=?i", $id);
	$days = $row["days"];
	$number_turist.= " (".$prop[$number_turist].")";
	$index++;
	$room = get_room($connect, $row["id_room"]);
	$putevka = naimenovanie($id_obj, $room, $date_z, $date_v, $days);

	if($id == 53740) {
		$putevka .="<br />".$note_bid;
	}

	$table.= "<tr>";
	$table.= "<td width='50' align='center'>".$index."</td>";
	$table.= "<td width='380'>".$putevka."</td>";
	$table.= "<td width='100' align='center'>".number($id_obj)."</td>";
	$table.= "<td width='130' align='center'>".$number_turist."</td>";
	$table.= "</tr>";
	if($id_obj == 57){
		$service = $connect->getOne("SELECT id_service FROM position_reck WHERE schet=?i AND id_room=0", $id);
		if($service){
			$index++;
			$service = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $service);
			$table.= "<tr>";
			$table.= "<td width='50' align='center'>".$index."</td>";
			$table.= "<td width='380'>".$service."</td>";
			$table.= "<td width='100' align='center'>".number($id_obj)."</td>";
			$table.= "<td width='130' align='center'>".$number_turist."</td>";
			$table.= "</tr>";
		}
	}
	$date_dei = date_sum($date_z, $days + 1);
	$date_dei = date("d.m.Y", $date_dei);
	$date_out = date_sum($date_z, -1);
	$date_out = date("d.m.Y", $date_out);
	ob_start();

	if(naimenovanie($id_obj)){
?>
	<div class="border">
	<p align="right">Типовая межотраслевая форма № М-2а<br />
	Утверждена постановлением<br />
	Госкомстата России от 30.10.97 № 71а<br /><br />
	<table cellpadding="3" cellspacing="0" align="right">
	<tr>
		<td style="border: none"></td>
		<td style="border: 1px solid black;" width="80" align="center">Коды</td>
	</tr>
	<tr>
		<td style="border: none">Форма по ОКУД&nbsp;</td>
		<td style="border: 2px solid black;" align="center">0315002</td>
	</tr>
	<tr>
		<td style="border: none">по ОКПО&nbsp;</td>
		<td style="border: 2px solid black;" align="center">74097191</td>
	</tr>
	</table>
	</p>
	<p>Организация <?php echo $firma.", ".$INN."/".$KPP.", ".$leg_address; ?><div style="width: 600px; margin-left: 75px;"><hr /></div></p>
	<p class="head">Доверенность № <?php echo $id; ?></p>
	<p>Дата выдачи <?php echo month_transform($date_out)." г."; ?><br /><br />
	Доверенность действительна по <?php echo month_transform($date_dei); ?></p>
	<span><?php echo $firma.", ".$INN."/".$KPP.", ".$leg_address; ?></span><br />
	<hr />
	<p class="label">наименование потребителя и его адрес</p><br />
	<span><?php echo $firma.", ".$INN."/".$KPP.", ".$leg_address; ?></span><br />
	<hr />
	<p class="label">наименование плательщика и его адрес</p><br />
	<?php echo "Счет № ".$reck." в ".$bank.", БИК ".$BIK." корр.сч. ".$KS;?><br /><br />
	<p>Доверенность выдана: <?php echo $fio; ?><br />
	Паспорт: <?php echo $passport; ?><br />
	Кем выдан: <?php echo $output; ?><br />
	Дата выдачи: <?php echo $date_pass." г."; ?><br />
	На получение от <?php echo full_name($id_obj); ?><br />
	товарно-материальных ценностей по
		<?php
			if($id_obj != 31)
				echo "счету № ".$schet_san." от ".$date_san;
			else
				echo "договору № 0010/16/64 от 01.02.2018";
		?>
		</p>
	<p align="center" class="head" style="font-size: 10pt;">ПЕРЕЧЕНЬ ТОВАРНО-МАТЕРИАЛЬНЫХ ЦЕННОСТЕЙ, ПОДЛЕЖАЩИХ ПОЛУЧЕНИЮ</p>
	<table cellpadding="5" cellspacing="0" border="1">
	<tr>
		<th width="50">№<br />п/п</th>
		<th width="380">Наименование</th>
		<th width="100">Ед. изм.</th>
		<th width="130">Количество<br />(прописью)</th>
	</tr>
	<?php echo $table; ?>
	</table>
	<br /><br />Подпись лица, получившего доверенность___________________________________удостоверяем.<br /><br />

	<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td width="250" valign="bottom"><br />Руководитель предприятия</td>
		<td width="200" rowspan="2" height="100">
		<?php if($img == 1)
			echo "<img src='images/pechat/pechat.jpg' />";
		?>
		</td>
		<td valign="bottom"><br />(<?php echo $director; ?>)</td>
	</tr>
	<tr>
		<td valign="top"><br /><br />Главный бухгалтер</td>
		<td valign="top"><br /><br />(<?php echo $booker; ?>)</td>
	</tr>
	</table>

	</div>
	<?php
	}else{
		echo "<div class='border'><p>Доверенность на данный санаторий не добавлена.</p></div>";
	}

	?>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	padding: 20px;
	height: 1030px;
	width: 715px;
	margin: 0 auto;
	font-size: 9pt;
}

table{
	font-size: 10pt;
}

.head{
	text-align: center;
	font-size: 14pt;
	margin: 5px;
	display: block;
	font-weight: bold;
}

.label{
	font-size: 90%;
	text-align: center;
	margin: 0;
	margin-top: -5px;
}

th{
	text-align: center;
	vertical-align: middle;
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
			$file = $directory."/temp/forms/dover".$id.".pdf";
			$pdf->Output($file, "F");
			return $file;
		}else
			$pdf->Output($trans.".pdf");
	}
}

function naimenovanie($id_obj, $room = "", $date_z = "", $date_v = "", $days = ""){
	$date_z = str_replace("-", ".", $date_z);
	$date_v = str_replace("-", ".", $date_v);
	$html = "";
	if($id_obj == 3) //Бакирово
		$html = "Санаторий \"Бакирово\" ".$room." ".$date_z."-".$date_v;
	elseif($id_obj == 6) //варзи-ятчи
		$html = "Санаторий \"Варзи-Ятчи\"";
	elseif($id_obj == 59) //ян
		$html = "Путевка";
	elseif($id_obj == 4) //балкыш
		$html = "Путевка";
	elseif($id_obj == 31) //лениногорский
		$html = "Путевка в санаторий-профилакторий";
	elseif($id_obj == 15) //джалильский
		$html = "Путевка в санаторий-профилакторий";
	elseif($id_obj == 60) //янган-тау
		$html = "Санаторий \"Янган-Тау\" с ".$date_z." по ".$date_v." ".$room;
	elseif($id_obj == 50) //ува
		$html = "Санаторий \"Ува\" ".$room;
	elseif($id_obj == 57) //юматово
		$html = "Санаторий \"Юматово\" ".$room." с ".$date_z." на ".$days."дн";
	elseif($id_obj == 48) //ставрополь
		$html = "Санаторий \"Ставрополь\" ".$room." с ".$date_z." на ".$days."дн";
	elseif($id_obj == 28)
		$html = "Путевка в санаторий \"Космос\"";
	elseif($id_obj == 18)
		$html = "ООО \"Санаторий-профилакторий \"Здоровье\"";
	elseif($id_obj == 416)
		$html = "Путевка";
	elseif($id_obj == 22)
		$html = "Санаторно-курортная путевка";
	elseif($id_obj == 670)
		$html = "Санаторно-курортная путевка";
	elseif($id_obj == 492)
		$html = "Путевка санаторно-курортная (ББ)";
	elseif($id_obj == 495)
		$html = "Путевка в СП \"Агидель\" ".$room." с ".$date_z." на ".$days."дн";
	elseif($id_obj == 497)
		$html = "Путевка в СП \"Хазино\" ".$room." ".$date_z."-".$date_v;
	elseif($id_obj == 47)
		$html = "Путевка в СП \"Сосновый бор\" ".$room." ".$date_z."-".$date_v;
	elseif($id_obj == 673)
		$html = "Санаторий «Сибирь» (Белокуриха) ".$room." ".$date_z."-".$date_v;
    elseif($id_obj == 545)
      $html = "Санаторий-профилакторий «Бодрость» ".$room." ".$date_z."-".$date_v;
    elseif ($id_obj == 20)
      $html = "Путевка";
	return $html;
}

function full_name($id_obj){
	$html = "";
	if($id_obj == 3) //Бакирово
		$html = "ЛПЧУП санаторий \"Бакирово\"";
	elseif($id_obj == 6) //варзи-ятчи
		$html = "ООО \"Санаторий Варзи-Ятчи\"";
	elseif($id_obj == 4) //балкыш
		$html = "ООО \"Санаторий Балкыш\"";
	elseif ($id_obj == 17)
      $html = "ЛПЧУП \"Санаторий Жемчужина\"";
    elseif ($id_obj == 20)
      $html = 'Управление социальными объектами ПАО "Татнефть" им.В.Д.Шашина';
	elseif($id_obj == 59) //ян
		$html = "ПАО \"Татнефть\" им. В.Д.Шашина";
	elseif($id_obj == 31) //лениногорский
		$html = "ПАО \"Татнефть\" им.В.Д.Шашина НГДУ \"Лениногорскнефть\" "; // ПАО" Татнефть" им.В.Д.Шашина НГДУ "Лениногорскнефть"
	elseif($id_obj == 15) //лениногорский
		$html = "ПАО \"Татнефть\" им.В.Д.Шашина НГДУ \"Джалильнефть\" "; // ПАО" Татнефть" им.В.Д.Шашина НГДУ "Джалильнефть"
	elseif($id_obj == 60) //янган-тау
		$html = "ГУП санаторий \"Янган-Тау\" РБ";
	elseif($id_obj == 50) //ува
		$html = "ООО \"Санаторий Ува\"";
	elseif($id_obj == 57) //юматово
		$html = "Государственное унитарное предприятие санаторий \"Юматово\" Республики Башкортостан";
	elseif($id_obj == 48) //ставрополь
		$html = "Санаторий-профилакторий Ставрополь \"ОАО КуйбышевАзот\"";
	elseif($id_obj == "28")
		$html = "ОАО \"Татнефть\" им.В.Д.Шашина НГДУ Прикамнефть";
	elseif($id_obj == 18)
		$html = "ООО \"Санаторий-профилакторий \"Здоровье\"";
	elseif($id_obj == 416)
		$html = "Многопрофильный центр медицины и реабилитации «Курорт Увильды»";
	elseif($id_obj == 22)
		$html = "ЛПЧУП санаторий «Ижминводы»";
	elseif($id_obj == 670)
		$html = "Акционерное общество «Курорт Белокуриха»";	
	elseif($id_obj == 492)
		$html = "ОАО \"Санаторий \"Саранский\"";
	elseif($id_obj == 495)
		$html = "ОАО \"БАШНЕФТЬ-СЕРВИС\"";
	elseif($id_obj == 497)
		$html = "ОАО \"БАШНЕФТЬ-СЕРВИС\"";
	elseif($id_obj == 673)
		$html = "Санаторий «Сибирь» (Россия, Алтайский край, Белокуриха)";
    elseif($id_obj == 545)
        $html = "Санаторий-профилакторий «Бодрость»";
	return $html;
}

function number($id_obj){
	if($id_obj == 57 OR $id_obj == 59 OR $id_obj == 3 OR $id_obj == 31 OR $id_obj == 15 OR $id_obj == 28 OR $id_obj == 670 OR $id_obj == 22 OR $id_obj == 492 OR $id_obj == 495 OR $id_obj == 673)
		return "шт";
	else
		return "";
}

?>
