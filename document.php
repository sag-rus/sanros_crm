<?php

$function = $_GET["func"];

$array_color_guaranteed = array(1 => "#CCC", 2 => "#C0E8FF" , 3 => "#D7FFDF", 4 => "#F7FF96", 5 => "#FFCACA");
$array_status_guaranteed = array(1 => "В продаже", 2 => "Выставлен счет" , 3 => "Оплачен", 4 => "Отложен", 5 => "Аннулирован");
$array_color_status = array("red" => "#FFCFCF", "green" => "#D9FFBf3");
$array_month = array(1 => "Январь", 2 => "Февраль", 3 => "Март", 4 => "Апрель", 5 => "Май", 6 => "Июнь", 7 => "Июль", 8 => "Август", 9 => "Сентябрь", 10 => "Октябрь", 11 => "Ноябрь", 12 => "Декабрь");
$month_pad = array(1 => "января", 2 => "февраля", 3 => "марта", 4 => "апреля", 5 => "мая", 6 => "июня", 7 => "июля", 8 => "августа", 9 => "сентября", 10 => "октября", 11 => "ноября", 12 => "декабря");
$array_week = array(0 => "Воскресенье", 1 => "Понедельник", 2 => "Вторник", 3 => "Среда", 4 => "Четверг", 5 => "Пятница", 6 => "Суббота");

if($function){

	$directory = dirname(__FILE__);

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
	$BIK = $conf->BIK;
	$OGRN = $conf->OGRN;
	$OKPO = $conf->OKPO;
	$KS = $conf->KS;
	$RS = $conf->RS;
	$bank = $conf->bank;
	$reck = $conf->reck;
	$director = $conf->director;
	$director_pad = $conf->director_pad;
	$booker = $conf->booker;
	$reestr = $conf->reestr;
	$licensia = $conf->licensia;
	$dog_str = $conf->dog_str;
	$bonus_rec = $conf->bonus_rec;
	$bonus_ref = $conf->bonus_ref;

	include_once($directory."/core/lib/Mysql.Class.php");
	include_once($directory."/core/functions.php");
	$connect = connect_to_MySQL();
	if(isset($_COOKIE["session"])){
		$session = $_COOKIE["session"];
		$login = $connect->getOne("SELECT login FROM session WHERE id_session=?s", $session);
		$row = $connect->getRow("SELECT id, name, rights FROM users WHERE login=?s", $login);
		$session_login = $row["id"];
		$name_user = $row["name"];
		$id_rights = $row["rights"];
	}
	$img = "";
	$turist = "";
	$id = $_GET["id"];
	if(isset($_GET["img"]))
		$img = $_GET["img"];
	if(isset($_GET["turist"]))
		$turist = $_GET["turist"];
	$today = date("d.m.Y");
	$version = isset($_GET["ver"])?$_GET["ver"]:null;

	if($function == "review_bron"){

		include_once($directory."/core/document/list_bron.php");
		review_bron($connect, "PDF", $id);

	}elseif($function == "review_confirm"){

		include_once($directory."/core/document/confirm.php");
		review_confirm($connect, "PDF", $id);

	}elseif($function == "review_schet"){

		include_once($directory."/core/document/schet.php");
		review_schet($connect, "PDF", $id);

	}elseif($function == 'review_schet_certificate'){

		include_once($directory."/core/document/schet_cert.php");
		review_schet_certificate($connect, "PDF", $id);

	}elseif($function == "review_obmen"){

		include_once($directory."/core/document/obmen.php");
		review_obmen($connect, "PDF", $id);

	}elseif($function == "review_dover"){
		include_once($directory."/core/document/dover.php");
		review_dover($connect, "PDF", $id, $turist);

	}elseif($function == "review_cancel"){
		include_once($directory."/core/document/cancel.php");
		review_cancel($connect, "PDF", $id);

	}elseif($function == "review_contract"){
		if($version == 1) {
			include_once($directory."/core/document/contract.php");
		} else {
			include_once($directory."/core/document/old_contract.php");
		}
		review_contract($connect, "HTML", $id);

	}elseif($function == "report_agent"){
		include_once($directory."/core/document/report_agent.php");
		report_agent($connect, $id);

	}elseif($function == 'agency_dogovor'){
		include_once($directory."/core/document/agency_dogovor.php");
		review_agency_dogovor($connect, $id);

	}elseif($function == "review_forma_certificate"){

		include_once($directory."/core/document/forma_cert.php");
		review_forma_certificate($connect, "PDF", $id);

	}elseif($function == "review_napravlenie"){

		include_once($directory."/core/document/napravlenie.php");
		review_napravlenie($connect, $id);

	}elseif($function == "save_file_1C_sync"){

		include_once($directory."/core/file/1C.php");
		save_file_1C_sync($connect, $id);

	}elseif($function == "promo_document"){

		$region = $_GET["region"];
		include_once($directory."/core/document/promo.php");
		review_promo($connect, $region);

	}
	elseif ($function === 'object_agency_report') {
		include_once($directory.'/core/document/object_agency_report.php');
    object_agency_report($connect);
	}
	elseif ($function == "comparison_module_payment") {
		$object =  $_GET["object"];
    $rate = 1;
    if(isset($_GET["rate"])){
      $rate = $_GET["rate"];
    }
    $month = 1;
    if(isset($_GET["month"])){
      $month = $_GET["month"];
    }

    include_once($directory."/core/class/ConfigCRM.Class.php");
    include_once($directory."/core/class/information/CompanyInfo.Class.php");
    include_once($directory."/core/class/comparison-object/ComparisonObject.Class.php");
    $config = ConfigCRM::getInstance();
    $config->connect = $connect;
    $config->directory = $directory;

    include_once($directory."/core/document/comparison_module_payment.php");
    comparison_module_payment($connect,$object, $rate, $month);
	}
	else{
		if(function_exists($function))
			$function();
	}
}

?>
