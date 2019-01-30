<?php

require_once __DIR__.'/../../vendor/autoload.php';

function sync_objects_api($connect){
	global $directory;
	$objects = $connect->getAll("SELECT `id`, `name`, `id_reg` AS `region_id`, `active`, `note`, `type`, `full_name`, `address`, `telephone`");
}

?>
