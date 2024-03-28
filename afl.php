<?php
use GuzzleHttp\Client;

require_once __DIR__."/vendor/autoload.php";
$directory = dirname(__FILE__);
define("_FOLDERSITE_", $directory);

include_once($directory."/config.php");
$conf = new JConfig;
$sync = $conf->sync_base;
$unisender_api_key = $conf->unisender_api_key;
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/Mysql.Class.php");

include_once($directory."/core/lib/mail.php");
include_once($directory."/core/lib/sms.php");
include_once($directory."/core/lib/PHPMailer/class.phpmailer.php");

$connect = connect_to_MySQL_directory();
$config = ConfigCRM::getInstance();
$config->connect = $connect;
$config->directory = $directory;
$config->clientCabinet = $clientCabinet;
$config->objectCabinet = $objectCabinet;

$configNew = \App\lib\CRM\Config\Client::getInstance();
$configNew->connect = $connect;
$configNew->directory = $directory;
$configNew->clientCabinet = $clientCabinet;
$configNew->objectCabinet = $objectCabinet;



/* ---------------------------- */

$partner_id = 'SNA';
$partner_service_term_code = 'SNAB';

$file = '1'.$partner_id.' POSTING DATA  '.date('Ymd').'  0                                                                        '.PHP_EOL;
$count = 0;
$text = '';
$items = $connect->getAll("SELECT * FROM `reckoning` WHERE `afl`<>'' AND `afl_worked`=0 AND `status`=5 AND `status_san`=1 AND `date_v`<=NOW() - INTERVAL 60 DAY ");
foreach($items as $item) {
    
    $text .= '<strong>Заявка №'.$item['id'].'</strong><br>';
    $text .= 'Стоимость путевки '.$item['sum'].' руб.<br>';
    $bon_sum = $connect->getOne("SELECT sum FROM bonus WHERE schet=?i AND sum < 0", $item['id']);
    $text .= 'Использовано бонусов: '.$bon_sum.'<br>';
    $cost = $item['sum'] + $bon_sum;
    $text .= 'Итоговая стоимость: '.$cost.' руб.<br>';
    $miles = (int)($cost / 60);
    $text .= 'Миль к начислению '.$miles.'<br>';
    $text .= '<br><br>';
    if (strlen($item['afl']<10)) {
        $item['afl'] = '           '.$item['afl'];
        $item['afl'] = substr($item['afl'], -10);
    }
    $fam = $connect->getOne("SELECT surname FROM klient WHERE id=?i", $item['turist']);
    $fam = mb_strtoupper(get_translit($fam).'                                ');
    $fam = substr($fam, 0, 30);
    $name = $connect->getOne("SELECT name FROM klient WHERE id=?i", $item['turist']);
    $name = mb_strtoupper(get_translit($name));
    if ($name=='') $name = ' ';
    $miles = (string)$miles;
    $miles = '00000000'.$miles;
    $miles = substr($miles, -7);
    $file .= 'I'.$partner_id.$partner_service_term_code.'       '.$item['afl'].$fam.$name[0].' '.date('Ymd').$miles.PHP_EOL;
    $count++;
    if ($_GET['send']=='1') {
        //$connect->query("UPDATE `reckoning` SET `afl_worked`=1 WHERE id='$item[id]'");
        echo $connect->last_query().'<br>';
    }
}
$count = (string)$count;
$count = '00000000'.$count;
$count = substr($count, -7);
$file .= '9'.$partner_id.' POSTING DATA  0         TOTAL RECORDS:'.$count;


if ($_GET['send']=='') {
    echo $text;
    echo '<a href="/CRM/afl.php?send=1">Отправить отчет по почте в Аэрофлот</a>';
} else {

    $connect->query("INSERT INTO `alf_log` SET `id`=0, `datetime`=NOW(), `text`=?s, `file`=?s", $text, $file);

    file_put_contents('afl.txt', $file);

    send_mail('sagrus@yandex.ru', 'Отчет по начисленным милям', 'Отчет по начисленным милям', false, false, '/var/www/html/CRM/afl.txt');

    echo '<strong>Письмо с файлом отправлено</strong>';

    /*echo 'FILE:<br>';
    echo '<pre>';
    echo $file;
    echo '</pre>';*/
   
}


?>