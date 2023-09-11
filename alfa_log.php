<?php

$log = PHP_EOL."START ALFALOG".PHP_EOL;
file_put_contents('/var/www/html/CRM/alfalogs/alfa_deposit_log_'.date('Y-m-d').'.txt', date('d.m.Y H:i:s').' '.$log, FILE_APPEND);
chmod('/var/www/html/CRM/alfalogs/alfa_deposit_log_'.date('Y-m-d').'.txt', 0777);


?>