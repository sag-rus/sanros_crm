<?php

function report_agent($connect, $all_id){
	global $directory;

	include_once($directory."/config.php");
	$conf = new JConfig;
	$firma = $conf->firma;
	$email = $conf->Email;
	$leg_address = $conf->leg_address;
	$sep_address = $conf->sep_address;
	$tel = $conf->tel_firma;
	$fax = $conf->fax_firma;
	$INN = $conf->INN;
	$KPP = $conf->KPP;
	$director = $conf->director;
	$director_pad = $conf->director_pad;
	$today = date("d.m.Y");

	$content = "";

	$all_id = explode("_", $all_id);
	$all_id = array_diff($all_id, array(""));

	foreach($all_id as $id){
		$row = $connect->getRow("SELECT DATE_FORMAT(reckoning.date_z, '%d.%m.%Y') as date_z, reckoning.sum, reckoning.id_obj, reckoning.agency, reckoning.id_com, position_reck.days FROM reckoning, position_reck WHERE reckoning.id=?i AND reckoning.id=position_reck.schet", $id);
		$object = get_object($connect, $row["id_obj"], "type");
		$date_z = $row["date_z"];
		$date_z_trans = month_transform($row["date_z"]);
		$days = $row["days"];
		$sum = add_null($row["sum"]);
		$id_com = $row["id_com"];
		$agency_contract = select_agency_contract($connect, $row["agency"], "all");
		$row = $connect->getRow("SELECT name, legal_address, inn, kpp FROM agency WHERE id=?i", $row["agency"]);
		$agency = $row["name"];
		$leg_address_agency = $row["legal_address"];
		$INN_agency = $row["inn"];
		$KPP_agency = $row["kpp"];
		$value = $connect->getOne("SELECT value FROM commission WHERE id=?i", $id_com);
		$reward = round(get_reward_agency($connect, $id), 2);
		$reward = add_null($reward);
		$oplata = 0;
		$payment_return = 0;
		$data = $connect->getAll("SELECT sum, type FROM payment WHERE schet=?i AND (type=1 OR type=2 OR type=5)", $id);
		foreach($data as $row) {
		    if($row['type'] == 5)
		        $payment_return += $row['sum'];
		    else
		        $oplata += $row["sum"];
        }
		$date = $connect->getOne("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date FROM history_schet WHERE id_schet=?i AND new_status=3 ORDER BY id", $id);
		ob_start();
?>
	<div class="border">
	<p style="font-weight: bold;">Компания: <?php echo $firma; ?><br />
	Юридический адрес: <?php echo $leg_address; ?><br />
	Обособленное подразделение: <?php echo $sep_address.", факс: ".$fax; ?><br />
	Агент: <?php echo $agency; ?><br />
	Юридический адрес: <?php echo $leg_address_agency; ?><br />
	Агентский договор: №<?php echo $agency_contract["number"]." действующий до ".$agency_contract["date_cont"]; ?></p>
	<p class="head">ОТЧЕТ АГЕНТА № <?php echo $id; ?> от <?php echo $date_z_trans." г."; ?></p>
	<p align="center" style="margin-bottom: 5px; top: -10px; position: relative;">К счету № <?php echo $id; ?> от <?php echo month_transform($date); ?></p>
	<table border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td width="300">Объект размещения</td>
		<td width="350"><?php echo $object; ?></td>
	</tr>
	<tr>
		<td width="300">Даты заезда</td>
		<td width="350"><?php echo str_replace("-", ".", $date_z)." на ".$days." дней"; ?></td>
	</tr>
	<tr>
		<td width="300">Стоимость тура, руб.</td>
		<td width="350"><?php echo number_format($sum,2,'.',' ')." руб."; ?></td>
	</tr>
	<tr>
		<td width="300">Величина агентского Вознаграждения, руб.</td>
		<td width="350"><?php echo number_format($reward,2,'.',' ')." руб."; ?></td>
	</tr>
	<tr>
		<td width="300">Оплачено Агентом, руб.</td>
		<td width="350"><?php echo  number_format($oplata,2,'.',' ')." руб."; ?></td>
	</tr>
    <?php if($payment_return > 0) { ?>
    <tr>
        <td width="300">Возврат Агенту, руб.</td>
        <td width="350"><?php echo number_format($payment_return,2,'.',' ')." руб."; ?></td>
    </tr>
    <?php } ?>
	</table>
	<p class="head">АКТ ВЫПОЛНЕННЫХ РАБОТ № <?php echo $id; ?> от <?php echo $date_z_trans." г."; ?></p>

	<p>АГЕНТ <strong><?php echo $agency; ?></strong>, в лице <span style="text-decoration: underline;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</span><br />
	и Компания <?php echo $firma; ?>, в лице Генерального директора <?php echo $director_pad; ?>, действуюший на основании «Устава» составили настоящий акт о следующем:<br />
	Агент реализовал путевку на сумму: <strong><?php echo $sum; ?> рублей</strong>
		<?php
			$arr = explode(".", $sum);
			$itog_sum_string = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
			$itog_sum_string = first_symbol_to_title($itog_sum_string);
			echo " (".$itog_sum_string.").";
		?>
	<br />
	Агентское вознаграждение составляет: <strong><?php echo $reward; ?> рублей</strong>
		<?php
			$arr = explode(".", $reward);
			$itog_sum_string = convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек";
			$itog_sum_string = first_symbol_to_title($itog_sum_string);
			echo " (".$itog_sum_string.").";
		?>
	<br />
	Вышеперечисленные услуги выполненые полностью и в срок.<br />Стороны претензий по качеству и срокам оканания услуг не имеют.
	</p>

	<p class="head">Юридические адреса, реквизиты и подписи сторон:</p>
	<table border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td width="325">КОМПАНИЯ:<br /><?php echo $firma."<br />".$leg_address; ?></td>
		<td width="325" valign="top">АГЕНТ:<br /><?php echo $agency; ?></td>
	</tr>
	<tr>
		<td width="325">ИНН/КПП:<br /><?php echo $INN."/".$KPP; ?></td>
		<td width="325">ИНН/КПП:<br /><?php echo $INN_agency."/".$KPP_agency; ?></td>
	</tr>
	<tr>
		<td width="325">Обособленное подразделение:<br /><?php echo $sep_address; ?></td>
		<td width="325" valign="top">Юридический адрес:<br /><?php echo $leg_address_agency; ?></td>
	</tr>
	<tr>
		<td width="325">
			<table width="100%" class="pod_tbl">
			<tr>
				<td width="220">Ген.директор</td>
				<td style="text-align: right">/<?php echo $director; ?>/</td>
			</tr>
			</table>
		</td>
		<td width="325">
			<table width="100%" class="pod_tbl">
			<tr>
				<td width="200">Директор</td>
				<td align="right">/&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;/</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="325">Дата: <?php echo $date_z_trans; ?> г.</td>
		<td width="325">Дата:</td>
	</tr>
	<tr>
		<td width="325" align="center">М.П.</td>
		<td width="325" align="center">М.П.</td>
	</tr>
	</table>
	</div>

<?php
		$content.= ob_get_clean();
	}
		echo $content;
		ob_start();
?>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	padding: 20px;
	height: 990px;
	width: 685px;
	margin: 0 auto;
	font-size: 10pt;
}

table{
	font-size: 10pt;
}

.head{
	text-align: center;
	font-size: 15pt;
	font-weight: bold;
	text-decoration: underline;
}

p{
	margin: 8px 0px;
}

td{
	padding-left: 5px;
}

.pod_tbl td{
	padding-left: 0px;
}

</style>

<?php
		$content = ob_get_clean();
		echo $content;
}

?>
