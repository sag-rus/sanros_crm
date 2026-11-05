<?php
// Repair a single CRM status from a read-only, authenticated website response.
if(PHP_SAPI!=='cli'||posix_geteuid()!==0)exit(1);
require dirname(__DIR__).'/core/comfort-booking-reservations.php';
$id=filter_var($argv[1]??'',FILTER_VALIDATE_INT);if(!$id||$id<1)throw new RuntimeException('Pass request ID');
$p=cbr_db();$d=cbr_request(['action'=>'detail','requestId'=>$id,'actor'=>1,'actorRights'=>6]);
if(($d['booking']['status']??'')!=='cancelled')throw new RuntimeException('Website booking is not cancelled');
$q=$p->prepare('SELECT id,id_obj,status,status_san,active FROM reckoning WHERE id=?');$q->execute([$id]);$r=$q->fetch(PDO::FETCH_ASSOC);
if(!$r||(string)$r['id_obj']!==(string)$d['booking']['hotelId'])throw new RuntimeException('Hotel mismatch');
$dir='/var/backups/comfort-booking-cancellation';if(!is_dir($dir)&&!mkdir($dir,0700,true))throw new RuntimeException('Backup directory failed');
$path=$dir.'/'.$id.'-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
if(file_put_contents($path,json_encode($r))===false)throw new RuntimeException('Backup failed');chmod($path,0600);
echo json_encode(cbr_sync_cancelled($p,$id,$d)),"\n";
