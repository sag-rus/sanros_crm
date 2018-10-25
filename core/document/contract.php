<?php

function review_contract($connect, $type, $id){
	global $directory, $session_login;

	include_once($directory."/config.php");

	$conf = new JConfig;
	$firma = $conf->firma;
	$full_firma = $conf->full_firma;
	$leg_address = $conf->leg_address;
	$sep_address = $conf->sep_address;
	$sep_address_ufa = $conf->sep_address_ufa;
	$sep_address_ulyan = $conf->sep_address_ulyan;
	$sep_address_samara = $conf->sep_address_samara;
	$tel = $conf->tel_firma;
	$tel_ufa = $conf->ufa;
	$tel_ulyan = $conf->ulan;
	$tel_samara = $conf->samara;
	$fax = $conf->fax_firma;
	$INN = $conf->INN;
	$KPP = $conf->KPP;
	$BIK = $conf->BIK;
	$OGRN = $conf->OGRN;
	$KS = $conf->KS;
	$bank = $conf->bank;
	$reck = $conf->reck;
	$director = $conf->director;
	$director_pad = $conf->director_pad;
	$dog_str = $conf->dog_str;
	$reestr = $conf->reestr;

	$dates = "";
	$prepay = "";
	$date_to = "";
	if(isset($_GET["dates"]))
		$dates = $_GET["dates"];
	if(isset($_GET["prepay"]))
		$prepay = $_GET["prepay"];
	if(isset($_GET["date_to"]))
		$date_to = $_GET["date_to"];

	$row = $connect->getRow("SELECT reckoning.date, reckoning.date_z, reckoning.date_v, reckoning.sum, reckoning.id_obj, reckoning.turist, reckoning.payer, reckoning.number_turist, reckoning.rest, reckoning.id_services, position_reck.id_room, position_reck.days, reckoning.id_dis, reckoning.type AS reck_type FROM reckoning, position_reck WHERE reckoning.id=?i AND position_reck.schet=?i", $id, $id);
	$days = $row["days"];
	$reck_type = $row["reck_type"];
	$date_z_schet = date_change($row["date_z"]);
	$date_v_schet = date_change($row["date_v"]);
	$date_create = date_change($row["date"], ".");
	$id_obj = $row["id_obj"];
	$sum = $row["sum"];
	$sum_pay = $sum;
	$id_dis = $row["id_dis"];
	$id_room = $row["id_room"];
	$number_turist = $row["number_turist"];
	$rest = explode(",", $row["rest"]);
	$services_array = explode("_", $row["id_services"]);
	$payer = $row["payer"];
	$payers = $connect->getRow("SELECT * FROM payer WHERE id=?i", $payer);
	$object = get_object($connect, $id_obj, "full_and_place");
	$room = get_room($connect, $id_room, "full");
	$bonus_str = "";
	$prepay_str = "";
	$raz_str = "";
	$table = "";
	$max_days = 0;
	$sale = $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum<0", $id) * (-1);
	if($id_dis){
		$row = $connect->getRow("SELECT value, type FROM discount WHERE id=?i", $id_dis);
		if($row["type"] == 1){
			$sale+= $sum_pay * ($row["value"] / 100);
		}else
			$sum = $sum - $row["value"];
	}
	$sum_pay-= $sale;
	$sale = add_null($sale);
	$arr = explode(".", $sale);
	if(!$arr[0])
		$arr[0] = 0;
	if(!isset($arr[1]) || !$arr[1])
		$arr[1] = 0;
	$itog_sum_sale = first_symbol_to_title(convert_number_to_string($arr[0])." рублей ".(isset($arr[1])?$arr[1]:0)." копеек");
	$arr = explode(".", add_null($sum_pay));
	$itog_sum_pay = first_symbol_to_title(convert_number_to_string($arr[0])." рублей ".(isset($arr[1])?$arr[1]:0)." копеек");

	if($prepay AND $date_to){
		$prepay = add_null($prepay);
		$arr = explode(".", $prepay);
		$itog_sum_prepay = first_symbol_to_title(convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек");
		$sum_prepay = add_null($sum_pay - $prepay);
		$arr = explode(".", $sum_prepay);
		$raz_string = first_symbol_to_title(convert_number_to_string($arr[0])." рублей ".$arr[1]." копеек");
		$prepay_str = "<br />Внесен аванс в размере ".$prepay." рублей РФ (".$itog_sum_prepay."). Остаток в размере ".$sum_prepay." рублей РФ (".$raz_string.") необходимо оплатить до ".str_replace("-", ".", date_change($date_to))." года.";
	}

	if($dates == "today")
		$date_doc = date("d.m.Y");
	else
		$date_doc = $date_create;
	$today = month_transform($date_doc);

	$services_default = "";
	$services_string = "";
	$data = $connect->getAll("SELECT id, name, type FROM price_includes ORDER BY type, sort_order, name");
	foreach($data as $row){
		$type = $row["type"];
		$id_s = $row["id"];
		$name_service = $row["name"];
		$check = 0;
		foreach($services_array as $id_service){
			if($id_s == $id_service AND $type == 0){
				$services_default.= "<strong>".$name_service.":</strong> Да<br />";
				$check = 1;
				break;
			}elseif($id_s == $id_service AND $type != 0){
				if($services_string)
					$services_string.= ", ";
				$services_string.= $name_service;
				$check = 1;
				break;
			}
		}
		if($type == 0 AND $check == 0)
			$services_default.= "<strong>".$name_service.":</strong> Нет<br />";
	}

	$data = $connect->getAll("SELECT date_z, days, id_room, id_service, note, add_one_day, sum, number FROM position_reck WHERE schet=?i", $id);
	foreach($data as $row){
		$days = $row["days"];
		$number = $row["number"];
		$date_z = date_change($row["date_z"]);
		$add_one_day = $row["add_one_day"];
		$pos_sum = $row['sum'];
		$days_sum = $days;
		if($add_one_day == 0)
			$days_sum--;
		$date_v = date_sum($date_z, $days_sum);
		$date_v = date("d.m.Y", $date_v);
		$note = $row["note"];
		if($row["id_room"])
			$room = get_room($connect, $row["id_room"], "full", "view_schet");
		else
			$room = $connect->getOne("SELECT name FROM service_schet WHERE id=?i", $row["id_service"]);
		if($days > $max_days){
			$max_days = $days;
			$max_date_v = $date_v;
		}
		$table.= "<tr>";
		if($reck_type == 1)
		    $table.= "<td style='width: 480px;' valign='middle'>Сертификат ".$note."</td>";
		else
          $table.= "<td style='width: 480px;' valign='middle'>".$room." ".$note."</td>";

		$table.= "<td style='width: 100px;' align='center' valign='middle'>".$number."</td>";
        if($reck_type == 1) {
          $table.= "<td style='width: 120px;' align='center' valign='middle'>".$pos_sum."</td>";
          $table.= "<td style='width: 120px;' align='center' valign='middle'>".($pos_sum*$number)."</td>";
        }
        else {
          $table.= "<td style='width: 120px;' align='center' valign='middle'>".$date_z."</td>";
          $table.= "<td style='width: 120px;' align='center' valign='middle'>".$date_v."</td>";
        }
		$table.= "</tr>";
	}
	$img = isset($_COOKIE['img'])?$_COOKIE['img']:null;
	$director_pad = "Генерального директора $director_pad, действующего на основании Устава";
	$city_office = "Казань";
	$image = "pechat1";
	$post = "Генеральный директор";
	$office = $connect->getOne("SELECT office FROM users WHERE id=?i", $session_login);
	if($office){
		$row = $connect->getRow("SELECT name, present_text, present, post, address, telephone, print_image, bik, bank, ks, rs FROM office WHERE id=?i", $office);
		if($row["present"]){
			$director = $row["present"];
			$director_pad = $row["present_text"];
			$post = $row["post"];
			$sep_address = $row["address"];
			$tel = $row["telephone"];
			$fax = isset($row["fax"])?$row['fax']:null;
			$city_office = $row["name"];
			if($row["print_image"])
				$image = $row["print_image"];
		}
		if($row["bank"]){
			$BIK = $row["bik"];
			$KS = $row["ks"];
			$bank = $row["bank"];
			$reck = $row["rs"];
		}
	}

	$brand_info = false;

	if($id == 65742)
	    $brand_info = true;

	ob_start();
?>
	<div class="border-off">
	<p class="head" style="font-size: 14pt;">Договор № <?php echo $id; ?><br /><?php if($reck_type == 0) { ?>реализации санаторно-курортной путёвки<?php } else { ?>реализации туристического продукта<?php } ?></p>
	<table style="font-weight: bold;">
	<tr>
		<td style="width: 550px;">г. <?php echo $city_office; ?></td>
		<td><?php echo $date_doc; ?></td>
	</tr>
	</table>
	<p align="justify"><?php echo $full_firma." (".($brand_info?"торговая марка «САНАТОРИИ РОССИИ»":$firma).")"; ?> в лице <?php echo $director_pad; ?>, именуемое в дальнейшем ФИРМА, с одной стороны, и
		<?php
			echo $payers["name"];
			if($payers["type"] == 2)
				echo " (".$payers["short"].") в лице ".$payers["post"]." ".$payers["present"].", действующего на основании ".$payers["doc"];
		?>, именуемый в дальнейшем КЛИЕНТ, с другой стороны, вместе именуемые СТОРОНЫ, заключили настоящий договор о нижеследующем:</p>
	<p class="head">1. Предмет договора</p>
	<p>1.1. На условиях и в сроки, установленные настоящим Договором, ФИРМА обязуется реализовать санаторно-курортную путёвку (оказать следующий комплекс туристских услуг указанным ниже <?php if($id == 66100) { ?>клиентам<?php } else { ?>туристам<?php } ?>), а КЛИЕНТ обязуется оплатить эту санаторно-курортную путёвку:<br /><br />
	<strong>Объект:</strong>  <?php echo $object; ?><br />
      <?php if($reck_type == 0) {?>
        <strong>Начало тура:</strong>  <?php echo $date_z_schet; ?><br />
        <strong>Окончание тура:</strong>  <?php echo $date_v_schet; ?><br />
      <?php }?>
	<strong>Отдыхающих:</strong> <?php echo $number_turist; ?><br />
	<?php echo $services_default; ?>
	<strong>В стоимость входит:</strong> <?php echo $services_string; ?><br />
    <?php if($id == 70172) { ?>
        <strong>Примечание: </strong> лечение назначается врачом санатория<br><br>
    <?php } ?>

	<table border="1" cellspacing="0" cellpadding="5">
	<tr>
		<th align="center"><?php if($reck_type == 0) {?>Номер<?php } else {?>Наименование<?php }?></th>
		<th align="center">Кол-во</th>
        <?php if($reck_type == 0) {?>
            <th align="center">Заезд</th>
		    <th align="center">Выезд</th>
        <?php } else { ?>
            <th align="center">Цена</th>
            <th align="center">Сумма</th>
        <?php }?>
	</tr>
	<?php echo $table; ?>
	</table><br />

	1.2. <?php if($id == 66100) { ?>Клиенты<?php } else { ?>Туристы<?php } ?>, совершающие путешествие на условиях настоящего Договора:</p>
	<table border="1" cellpadding="5" cellspacing="0" width="100%">
	<tr>
		<th style="width: 300px">Фамилия имя отчество</th>
		<th style="width: 150px">Дата рождения</th>
		<th style="width: 150px">№ документа</th>
	</tr>
	<?php
		foreach($rest as $turists){
			if($turists){
				$row = $connect->getRow("SELECT surname, name, otch, date, passport, telephone, email, date_pas, birth_certificate, output FROM klient WHERE id=?i", $turists);
				$turist = $row["surname"]." ".$row["name"]." ".$row["otch"];
				$passport = $row["passport"];
				if($payers["type"] == 1 AND !$payers["passport"]){
					$payers["passport"] = $passport;
					$payers["output"] = $row["output"];
					$payers["telephone"] = $row["telephone"];
					$payers["email"] = $row["email"];
					$payers["date_passport"] = date_change($row["date_pas"]);
				}
				if(!$passport)
					$passport = $row["birth_certificate"];
				echo "<tr>";
				echo "<td>".$turist."</td>";
				echo "<td align='center'>".date_change($row["date"])."</td>";
				echo "<td align='center'>".$passport."</td>";
				echo "</tr>";
			}
		}
	?>
	</table><br />

	<p class="head">2. Сведения о туроператоре</p>
	<p>2.1. Туроператором, являющимся непосредственным исполнителем бронирования санаторно-курортной путёвки, поименованной в п. 1.1 настоящего Договора, является следующее юридическое лицо:<br />
	полное и сокращенное наименования:<br />
	<?php echo $full_firma."<br />".$firma; ?><br />
	<strong>Адрес:</strong> <?php echo $leg_address; ?><br />
	<strong>Адрес обособленного подразделения:</strong> <?php echo $sep_address; ?><br />
	<strong>ОГРН:</strong> <?php echo $OGRN; ?><br />
	<strong>ИНН/КПП:</strong> <?php echo $INN."/".$KPP; ?><br />
	<strong>Реестровый номер:</strong> <?php echo $reestr; ?><br /><br />

	<strong>Размер финансового обеспечения на <?=date("Y");?> год:</strong> 500 000 рублей (Пятьсот тысяч рублей 00 копеек) до 28.02.2019 г.;<br />
	<strong>Номер, дата и срок действия договора страхования гражданской ответственности за неисполнение или ненадлежащее исполнение обязательств по договору о реализации санаторно-курортной путёвки:</strong><br />Договор страхования № 2490/18-49 от 20.02.2018, срок действия с 01.03.2018 по 28.02.2019 г.<br />
	<strong>Наименование, адрес (место нахождения) и почтовый адрес организации, предоставившей финансовое обеспечение:</strong><br /> АО «Страховая компания ГАЙДЕ», 191119, г. Санкт-Петербург, Лиговский пр-т, д. 108, лит. А</p>

	<p class="head">3. Порядок реализации <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?></p>
	<p>3.1. Возникновение у ФИРМЫ обязанности реализовать КЛИЕНТУ <?php if($id == 66100) { ?>санаторно-курортную путевку<?php } else { ?>турпродукт<?php } ?>, <?php if($id == 66100) { ?>поименованную<?php } else { ?>поименованный<?php } ?> в п.1.1 настоящего Договора, происходит после подтверждения  бронирования <?php if($id == 66100) { ?>данной санаторно-курортной путевки для клиентов<?php } else { ?>данного турпродукта для туристов<?php } ?>, указанных в п. 1.2 настоящего Договора.. До момента подтверждения бронирования настоящий договор является предварительным с отлагательным условием подтверждения бронирования <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?>.<br />
	3.2. Для получения подтверждения ФИРМА обязана направить в объект размещения заявку на бронирование.<br />
	Для оформления такой заявки и дальнейшего исполнения ФИРМОЙ настоящего Договора КЛИЕНТ обязан предоставить ФИРМЕ не позднее чем за 20 рабочих дней до начала путешествия, если иное не предусмотрено настоящим Договором, документы  (паспорт РФ, свидетельства о рождении на детей до 14-ти  лет).<br />
	3.3. При неподтверждении заявки,  в течение 5-ти (пяти) рабочих дней с момента подписания СТОРОНАМИ настоящего Договора, права и обязанности СТОРОН по реализации <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?> не возникают и КЛИЕНТУ полностью возвращаются денежные средства, внесенные им согласно п. 4.4 настоящего Договора, если иное не оговорено дополнительно.<br />
	3.4. При наличии подтверждения, ФИРМА обязывается реализовать КЛИЕНТУ заказанный <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?> при условии полной оплаты КЛИЕНТОМ его стоимости, установленной в статье 4 настоящего Договора.<br />
	3.5. При реализации <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?> ФИРМА обязана передать КЛИЕНТУ основную информацию о потребительских свойствах санаторно-курортной путёвки и выдать сопроводительные документы, необходимые для реализации услуг, входящих в санаторно-курортную путёвку, КЛИЕНТУ или лицам совершающим путешествие (<?php if($id == 66100) { ?>клиентам<?php } else { ?>туристам<?php } ?>).<br />
	3.6. КЛИЕНТ обязан ознакомиться  с каталогами, предоставленными ФИРМОЙ, в которых представлены сведения о местах размещений и иными документами, предложенными ФИРМОЙ.<br >
	КЛИЕНТ обязан проинформировать об этих сведениях сопровождающих его лиц, а в случае приобретения санаторно-курортной путёвки для других лиц  - лиц, совершающих путешествие, а также обязан передать им иную полученную от ФИРМЫ согласно п. 3.5 настоящего Договора информацию и ознакомить их с условиями настоящего Договора.<br />
	3.7. КЛИЕНТ обязан получить в установленное время и месте турпутевку и сопроводительные документы, заблаговременно согласовав с ФИРМОЙ место получения  документов, согласно п. 3.5 настоящего Договора.<br />
	3.8. ФИРМА обязана обеспечить предоставление всего комплекса услуг, входящих в санаторно-курортную путёвку, с надлежащим уровнем качества.</p>
	<p class="head">4. Стоимость санаторно-курортной путёвки и порядок оплаты</p>
		<?php $arr = explode(".", $sum); ?>
	<p>4.1. Стоимость санаторно-курортной путёвки на дату заключения настоящего Договора, составляет <?php echo $arr[0]; ?> рублей <?php echo add_null($arr[1]); ?> копеек РФ
		<?php
			$itog_sum_string = convert_number_to_string($arr[0])." рублей ".add_null($arr[1])." копеек";
			$itog_sum_string = first_symbol_to_title($itog_sum_string);
			echo " (".$itog_sum_string.")";
		?>
		, из них скидка составляет <?php echo $sale; ?> рублей РФ (<?php echo $itog_sum_sale; ?>). Итого стоимость санаторно-курортной путёвки составляет <?php echo $sum_pay; ?> рублей РФ (<?php echo $itog_sum_pay; ?>)
		<?php echo $prepay_str; ?>
		<br />
	4.2. КЛИЕНТ  одновременно с подписанием сторонами настоящего Договора вносит  в счет оплаты стоимости санаторно-курортной путёвки <?php if($payers["type"] != 2) echo "в кассу или "; ?>на расчетный счет ФИРМЫ аванс в размере полной стоимости тура.<br />
	4.3. При подтверждении бронирования <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?> туроператором КЛИЕНТ оплачивает в течение 5 рабочих дней с момента подтверждения 100% стоимости санаторно-курортной путёвки, с учетом ранее внесенных авансов.<br />
	4.4. Факт полной оплаты санаторно-курортной путёвки подтверждается оформленной ФИРМОЙ обменной путёвкой и служит основанием для оформления и передачи комплекта сопроводительных документов.</p>
	<p class="head">5. Срок действия и порядок расторжения настоящего Договора.</p>
	<p>5.1. Настоящий Договор считается заключенным в качестве предварительного договора с момента подписания его СТОРОНАМИ. Настоящий договор, устанавливающий права и обязанности СТОРОН по реализации <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?>, считается заключенным при условии подтверждения туроператором бронирования <?php if($id == 66100) { ?>санаторно-курортной путевки<?php } else { ?>турпродукта<?php } ?> и с момента этого подтверждения.<br />
	5.2 Настоящий Договор действует до момента окончания путешествия либо до срока оказания последней услуги, включенной в подтвержденную санаторно-курортную путёвку.<br />
	5.3. В случае нарушения КЛИЕНТОМ порядка оплаты санаторно-курортной путёвки, определенного п.п. 4.3-4.4 настоящего Договора, ФИРМА имеет право расторгнуть настоящий Договор в одностороннем порядке с возложением убытков (фактически понесенных расходов) объекта размещения, на счет КЛИЕНТА.<br />
	5.4. КЛИЕНТ имеет право без каких-либо штрафных санкций отказаться от санаторно-курортной путёвки в срок не позднее 15 (пятнадцати) суток до заезда. Отказ от санаторно-курортной путёвки оформляется исключительно в письменном виде. Если клиент отказался от санаторно-курортной путёвки менее чем за 15 суток до заезда (при 100% оплате), ФИРМА имеет право взыскать с КЛИЕНТА фактически понесенные расходы<?php if($id_obj == 16 OR $id_obj == 23 OR $id_obj == 5 OR $id_obj == 39){ ?> в размере стоимости 3-х койко-дней<?php } ?>.<br />
	5.5. При изменении сроков заезда после произведения 100% оплаты путевки, санаторно-курортная путёвка аннулируется, тем самым изменяются прежние условия договора, и оформляется заявка на другую путевку в прежнем порядке.<br />
	5.6. При опоздании клиента в здравницу, зачет опозданий и возврат денежных средств за дни опоздания не производится. Отпущенные дни восстанавливаются только при наличии больничных листов или справок транспортных компаний, по вине которых произошла задержка.<br />
<!--
	5.4. КЛИЕНТ имеет право расторгнуть настоящий Договор в любое время в одностороннем порядке без объяснения причин или по причинам, не связанным с выполнением ФИРМОЙ своих обязательств. Признание одностороннего расторжения настоящего Договора  возникает с даты письменного объявления КЛИЕНТА об отказе. А при аннуляции тура на даты заездов в период «высокого» сезона данные  расходы составляют:<br />
	- в срок от 45 (сорока пяти) до 40 (сорока) суток – денежная сумма, эквивалентная 10 (десяти) % стоимости тура;<br />
	- в срок от 39 (тридцати девяти) до 31 (тридцати одних) суток – денежная сумма, эквивалентная 70 (семидесяти) % стоимости тура;<br />
	- в срок,  менее 30 (тридцати) суток – денежная сумма, эквивалентная 95 (девяноста пяти) % от стоимости тура.<br />
	К «высоким» датам заездов относятся Новый год и Рождество (период с 24 декабря по 12 января), а также период с 25 апреля по 12 мая. Дополнительно «высоким» сезоном являются  периоды заездов туристов, включающие общегосударственные праздники Российской Федерации.</p>
-->
	<p class="head">6. Порядок и сроки предъявления КЛИЕНТОМ требований об уплате денежной суммы по договору страхования гражданской ответственности за неисполнение или ненадлежащее исполнение обязательств по договору о реализации санаторно-курортной путёвки. Основания для осуществления выплат.</p>
	<p>6.1. В случаях неисполнения или ненадлежащего исполнения туроператором обязательств по оказанию услуг КЛИЕНТУ, входящих в санаторно-курортную путёвку по настоящему Договору, при наличии оснований для уплаты денежной суммы по договору страхования гражданской ответственности за неисполнение или ненадлежащее исполнение обязательств по договору о реализации санаторно-курортной путёвки КЛИЕНТ вправе в пределах суммы финансового обеспечения предъявить письменное требование об уплате денежной суммы непосредственно гаранту - организации, предоставившей финансовое обеспечение и указанной в п. 2.1 настоящего Договора.<br />
	6.2. Письменное требование КЛИЕНТА об уплате денежной суммы по договору страхования гражданской ответственности за неисполнение или ненадлежащее исполнение обязательств по договору о реализации санаторно-курортной путёвки должно быть предъявлено гаранту в течение срока действия финансового обеспечения.<br />
	6.3. Основанием для уплаты денежной суммы по договору страхования гражданской ответственности за неисполнение или ненадлежащее исполнение обязательств по договору о реализации санаторно-курортной путёвки является факт установления обязанности туроператора возместить КЛИЕНТУ реальный ущерб, возникший в результате неисполнения или ненадлежащего исполнения туроператором указанных в п. 6.1 настоящего Договора обязательств, если это является существенным нарушением условий такого договора.<br />
	Существенным нарушением условий настоящего Договора признается нарушение, которое влечет для КЛИЕНТА такой ущерб, что он в значительной степени лишается того, на что был вправе рассчитывать при заключении договора, в частности:<br />
	неисполнение обязательств по оказанию КЛИЕНТУ входящих в санаторно-курортную путёвку услуг по перевозке и (или) размещению;<br />
	наличие в санаторно-курортной путёвки существенных недостатков, включая существенные нарушения требований к качеству и безопасности санаторно-курортной путёвки.<br />
	6.4. Обязанность туроператора возместить КЛИЕНТУ ущерб, установленный п. 6.3 настоящего Договора, устанавливается письменным признанием туроператора обоснованности претензий КЛИЕНТА или по решению суда.</p>
	<p class="head">7. Порядок разрешения споров.</p>
	<p>7.1. В случае обнаружения ненадлежащего исполнения или неисполнения Договора или ненадлежащего оказания или неоказания туроператором заказанных услуг, КЛИЕНТ обязан незамедлительно в письменном виде уведомить об этом представителя ФИРМЫ для своевременного принятия мер. Если КЛИЕНТА не удовлетворяют меры, принятые на месте для устранения претензий, он имеет право в течение 20 дней со дня окончания срока действия настоящего Договора предъявить письменную претензию ФИРМЕ, которая обязана дать официальный ответ на нее в течение 10 дней.<br />
	7.2. Все споры или разногласия, возникающие между СТОРОНАМИ по настоящему Договору или в связи с ним, разрешаются путем переговоров между СТОРОНАМИ.<br />
	7.3. В случае невозможности разрешения разногласий путем переговоров, стороны руководствуются действующим законодательством РФ.</p>
	<p class="head">8. Особые условия.</p>
	<p>8.1. Подписывая настоящий Договор, КЛИЕНТ подтверждает, что до его сведения ФИРМОЙ доведена полная и исчерпывающая информация, предусмотренная ФЗ «О защите прав потребителей»  и ФЗ «Об основах туристской деятельности в РФ».<br />
	8.2. В соответствии с законодательством  РФ авиабилеты  и страховые  полисы являются самостоятельными договорами между КЛИЕНТОМ и авиаперевозчиком или страховщиком. В случаях изменения времени вылета авиарейсов и связанные с этим изменения объема и сроков  туристских услуг, ответственность несет авиаперевозчик.<br />
	8.3. Возврат стоимости авиабилетов на регулярные рейсы производится согласно условиям применения тарифа авиаперевозчика.. Покрытие расходов по страховым случаям обеспечивается страховым полисом и решается КЛИЕНТОМ самостоятельно со страховой компанией.</p>
	<br /><br /><br /><br /><br />
	<p class="head">9. Прочие условия.</p>
	<p>9.1. Настоящий Договор составлен в двух экземплярах, обладающих равной юридической силой,  на русском языке  и хранится по одному у каждой из Сторон. Все документы (договор, счет, обменная путевка, доверенность), переданные факсимильной и электронной связью, имеют юридическую силу.<br />
	9.2. Все изменения  и дополнения к настоящему Договору должны быть составлены в письменной  форме и подписаны обеими Сторонами.</p>
	<p class="head">10. Реквизиты и подписи сторон.</p>
    <br />
	<table>
	<tr>
		<td valign="top" width="400">
			<p><strong>ФИРМА:</strong> <?php echo $firma; ?><br /><?php if($brand_info) { ?> (торговая марка «САНАТОРИИ РОССИИ»)<br><?php } ?>
			<strong>ИНН/КПП:</strong> <?php echo $INN."/".$KPP; ?><br />
			<strong>ОГРН:</strong> <?php echo $OGRN; ?><br />
			<strong>Юридический адрес:</strong><br /><?php echo $leg_address; ?><br /><br />
          <?php if($fax){ ?>
              <strong>Факс:</strong> <?php echo $fax; ?><br />
          <?php } ?>
            <strong>р/с:</strong> <?php echo $reck; ?><br />
            в <?php echo $bank; ?><br />
            <strong>к/с:</strong> <?php echo $KS; ?><br />
            <strong>БИК:</strong> <?php echo $BIK; ?><br /><br />

            <strong>Обособленное подразделение:</strong><br /><?php echo $sep_address; ?><br />
			<strong>Тел.:</strong> <?php echo $tel; ?><br /><br />

                <?php echo $post; ?><br /><?php echo $firma; ?><?php if($brand_info) { ?> (торговая марка «САНАТОРИИ РОССИИ»)<?php } ?>:
			<?php if($img == 1){
			?>
				<table>
				<tr>
					<td><img src="images/pechat/<?php echo $image; ?>.jpg?v=2" /></td>
					<td valign="middle"><?php echo $director; ?></td>
				</tr>
				</table>
			<?php
				}else
					echo "_______________________".$director;
			?>
			</p>
		</td>
		<td valign="top">
			<?php
				if($payers["type"] == 2){
			?>
					<strong>КЛИЕНТ:</strong> <?php echo $payers["short"]; ?><br />
					<strong>ИНН/КПП:</strong> <?php echo $payers["inn"]."/".$payers["kpp"]; ?><br />
					<strong>Юридический адрес:</strong><br /><?php echo $payers["ur_address"]; ?><br />
					<strong>Почтовый адрес:</strong><br /><?php echo $payers["address"]; ?><br />
					<strong>р/с:</strong> <?php echo $payers["rs"]; ?><br />
					<strong>к/с:</strong> <?php echo $payers["ks"]; ?><br />
					<strong>БИК:</strong> <?php echo $payers["bik"]; ?><br />
					<strong>Банк:</strong> <?php echo $payers["bank"]; ?><br />
				<?php if($payers["bin"]){ ?>
					<strong>БИК:</strong> <?php echo $payers["bin"]; ?><br />
					<strong>ИИК:</strong> <?php echo $payers["iik"]; ?><br />
				<?php } ?>
					<br />
					<?php echo $payers["post_im"]; ?><br />
					<?php echo $payers["short"]; ?>
					<table>
					<tr>
						<td width="100" height="180"></td>
						<td valign="middle"><?php echo $payers["present_im"]; ?></td>
					</tr>
					</table>
			<?php
				}else{
					echo "<p><strong>КЛИЕНТ:</strong> ".$payers["name"]."<br />";
					if($payers["passport"]){
						echo "<strong>ПАСПОРТ:</strong> ".substr_replace($payers["passport"], " ", 4, 0)."<br />";
						if(isset($payers['output']) && $payers["output"])
							echo "<strong>ВЫДАН:</strong> ".$payers["output"]."<br />";
						if(isset($payers['date_passport']) && $payers["date_passport"])
							echo "<strong>ДАТА ВЫДАЧИ:</strong> ".$payers["date_passport"]."<br />";
						if(isset($payers['telephone']) && $payers["telephone"])
							echo "<strong>ТЕЛЕФОН:</strong> ".$payers["telephone"]."<br />";
						if(isset($payers['email']) && $payers["email"])
							echo "<strong>EMAIL:</strong> ".$payers["email"];
					}
					echo "<br /><br /><br /><strong>ПОДПИСЬ:</strong> ____________</p>";
				}
			?>
			</div>
		</td>
	</tr>
	</table>

<style type="text/css">
.border{
	font-family: dejavusans, sans-serif;
	line-height: 14px;
	width: 750px;
	text-align: justify;
	margin: 0 auto;
	font-size: 10px;
}

p{
	text-align: justify;
}

td, th{
	font-size: 11px;
}

.head{
	font-size: 11pt;
	font-weight: bold;
	text-align: center;
	margin: 0;
	line-height: 16px;
}
th{
	text-align: center;
	padding: 5px;
	font-weight: bold;
}
td{
	padding: 5px;
}
</style>

<?php
	$content = ob_get_clean();
	echo $content;
}

?>
<script type="text/javascript">
	window.onload = function(){
		setTimeout("print()", 1000);
	}
</script>
