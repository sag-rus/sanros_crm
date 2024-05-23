<?php
require_once __DIR__.'/../../vendor/autoload.php';
$directory = dirname(__FILE__)."/../..";

include_once($directory."/config.php");
$conf = new JConfig;
$sync = $conf->sync_base;
include_once($directory."/core/functions.php");
include_once($directory."/core/lib/Mysql.Class.php");
include_once $directory."/core/upload/price.php";
include_once $directory."/core/upload/default.php";
include_once($directory."/core/admin/news.php");
include_once($directory."/core/upload/sync-objects-prices-api.php");
$connect = connect_to_MySQL_directory();

sync_objects_prices_api($connect);