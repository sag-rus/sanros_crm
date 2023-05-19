<?php
use GuzzleHttp\Client;

header('Content-Type: application/json;charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__."/vendor/autoload.php";
$directory = dirname(__FILE__);
define("_FOLDERSITE_", $directory);

include_once($directory."/config.php");
$conf = new JConfig;
$sync = $conf->sync_base;
$unisender_api_key = $conf->unisender_api_key;
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/Mysql.Class.php");
include_once($directory."/core/admin/news.php");


$clientCabinet = array(
	"link" => $conf->turist_cabinet
);
$objectCabinet = array(
	"link" => $conf->object_cabinet
);
$mail = array(
	"module" => $conf->email_module
);

$connect = connect_to_MySQL_directory();

if(!$connect)
	return;

$client = new GuzzleHttp\Client(['verify' => false]);

$robots = 'User-agent: *'.PHP_EOL;

function AddToRobots($images) {
	global $robots;
	foreach ($images as $image) {
		$filename = $image['uri'];
		$filename = substr($filename, strrpos($filename, '/'), 250);
		$filename = substr($filename, 0, strrpos($filename, '.'));
		$robots .= 'Disallow: *'.$filename.'.*'.PHP_EOL;
	}
}

$content = $connect->getAll("SELECT `id` FROM `sites_contents` WHERE `imgs_no_index` =1 ");
foreach ($content as $item) {
  $entity = [
    'id' => $item['id'],
    'type' => 'content'
  ];
  $images = bounds_to_files($connect,load_bounds($connect,$entity,'image'));
  AddToRobots($images);
  $images = bounds_to_files($connect,load_bounds($connect,$entity,'photogallery'));
  AddToRobots($images);
  $images = bounds_to_files($connect,load_bounds($connect,$entity,'slider_photos'));
	AddToRobots($images);
  $images = bounds_to_files($connect,load_bounds($connect,$entity,'slider_photos_mobile'));
	AddToRobots($images);
  $images = bounds_to_files($connect,load_bounds($connect,$entity,'page_bg'));
	AddToRobots($images);	
}

$res = $client->request('POST', "https://cdn.tonia.ru/api/files/upload/robots" . '?cache=' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 1, 15), [
	'form_params' => [
		'file' => base64_encode($robots)
	]
]);

echo $robots;

file_put_contents('cdn_robots.txt', $robots);

?>