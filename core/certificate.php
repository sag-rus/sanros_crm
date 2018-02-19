<?php

function show_certificate($connect){
?>
<div class="form-horizontal panel panel-default">
	<div class="panel-heading"><i class="fa fa-file-powerpoint-o"></i> Сертификаты</div>
	<table class="table">
<?php
	$data = $connect->getAll("SELECT id, klient, schet, DATE_FORMAT(date, '%d.%m.%Y') as date, sum, status FROM certificate");
	foreach($data as $row){
		$id = $row["id"];
		$note = "";
		if($row["schet"])
			$note = "Использован в счете №".$row["schet"];
		$class = "";
		if($row["status"] == 4)
			$class = "success";
		if($row["status"] == 6)
			$class = "info";
		if($row["status"] == 5)
			$class = "danger";
		$status = $connect->getOne("SELECT name FROM status_cert WHERE id=?i", $row["status"]);
	?>
		<tr class="<?php echo $class; ?>">
			<td width="25%"><?php echo select_name_klient($connect, $row["klient"]); ?></td>
			<td width="10%"><?php echo $row["sum"]; ?></td>
			<td width="15%"><?php echo $status; ?></td>
			<td width="10%"><?php echo $row["date"]; ?></td>
			<td width="20%"><?php echo $note; ?></td>
			<td width="20%">
				<button type="button" class="btn btn-primary btn-xs btn-<?php echo $id; ?>" onclick="show_menu_certificate('<?php echo $id; ?>')"><i class="fa fa-angle-double-down"></i> Действия</button>
				<button type="button" class="btn btn-default btn-xs" onclick="show_history_certificate('<?php echo $id; ?>')"><i class="fa fa-history"></i> История</button>
			</td>
		</tr>
	<?php
	}
?>
	</table>
</div>
<?php
}

function show_history_certificate($connect){
	$id = $_POST["id"];
	$data = $connect->getAll("SELECT DATE_FORMAT(date, '%d.%m.%Y') as date, time, status, id_user FROM history_cert WHERE id_cert=?i", $id);
	if(!count($data))
		return FALSE;
	$html = "";
	foreach($data as $row){
		ob_start();
	?>
		<tr>
			<td width="40%"><?php echo $row["date"]." ".$row["time"]; ?></td>
			<td width="40%"><?php echo $connect->getOne("SELECT name FROM status_cert WHERE id=?i", $row["status"]); ?></td>
			<td width="20%"><?php echo $connect->getOne("SELECT name FROM users WHERE id=?i", $row["id_user"]); ?></td>
		</tr>
	<?php
		$html.= ob_get_clean();
	}
	ob_start();
?>
<div class="modal fade bs-example-modal-lg">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">История сертификата</h4>
			</div>
			<div class="modal-body">
				<table class="table table-border table-condensed">
					<tr>
						<th>Дата</th>
						<th>Статус</th>
						<th>Менеджер</th>
					</tr>
					<?php echo $html; ?>
				</table>
			</div>
		</div>
	</div>
</div>
<?php
	$html = ob_get_clean();
	return $html;
}

function show_menu_certificate($connect){
	global $id_rights;
	$id = $_POST["id"];
	$status = $connect->getOne("SELECT status FROM certificate WHERE id=?i", $id);
?>
	<?php if($status == 1 AND $id_rights > 3){ ?>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 4)">Оплатить</span>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 5)">Аннулировать</span>
	<?php }elseif($status == 1){ ?>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 2)">Запрос оплаты</span>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 3)">Запрос аннуляции</span>
	<?php }elseif($status == 2 AND $id_rights > 3){ ?>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 4)">Оплатить</span>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 1)">Вернуть</span>
	<?php }elseif($status == 3 AND $id_rights > 3){ ?>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 5)">Аннулировать</span>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 1)">Вернуть</span>
	<?php }elseif($status == 4 AND $id_rights > 3){ ?>
		<span onclick="change_status_certificate('<?php echo $id; ?>', 5)">Аннулировать</span>
	<?php } ?>
	<span onclick="schet_certificate('<?php echo $id; ?>')">Счет</span>
	<span onclick="show_certificate_forma('<?php echo $id; ?>')">Сертификат</span>
<?php
}

function change_status_certificate($connect){
	$id = $_POST["id"];
	$status = $_POST["status"];
	$connect->query("UPDATE certificate SET status=?i WHERE id=?i", $status, $id);
	if($status == 4){
		$sum = $connect->getOne("SELECT sum FROM certificate WHERE id=?i", $id);
		$timestamp = date("U");
		$connect->query("INSERT INTO payment(schet, class, type, sum, pay_method, date, created, processed) VALUES (?i, 'cert', 2, ?i, 2, ?s, ?i, ?i)", $id, $sum, date("Y-m-d"), $timestamp, $timestamp);
	}
	save_certificate_to_history($connect, $id);
}

?>
