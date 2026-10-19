<?php
if(PHP_SAPI!=='cli')exit(1);
require '/var/www/html/CRM/core/price_tonia_live.php';
$lock=fopen('/var/lib/price-tonia-live/worker.lock','c');if(!flock($lock,LOCK_EX|LOCK_NB))exit;
$rows=ptl_query("SELECT * FROM price_tonia_live_checks WHERE status IN ('queued','running') ORDER BY id LIMIT 20")->fetchAll();
foreach($rows as $row){ptl_expire($row['booking_id']);if(strtotime($row['expires_at'].' UTC')<=time())continue;try{ptl_refresh($row);}catch(Throwable $e){error_log('Price check '.$row['id'].': '.$e->getMessage());}}
