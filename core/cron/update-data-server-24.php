<?php

	$directory = dirname(__FILE__)."/../..";
	define("EARTH_RADIUS", 6372795);
	include_once($directory."/config.php");
	$conf = new JConfig;
	$sync = $conf->sync_base;
	include_once($directory."/core/functions.php");
	include_once($directory."/core/lib/Mysql.Class.php");
	include_once $directory."/core/upload/price.php";
	include_once $directory."/core/upload/default.php";
	$connect = connect_to_MySQL_directory();

	upload_price_on_server($connect,false,"25n",true);

?>
