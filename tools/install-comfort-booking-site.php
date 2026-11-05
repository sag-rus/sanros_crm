<?php
if(PHP_SAPI!=='cli'||posix_geteuid()!==0)exit(1);
require dirname(__DIR__).'/core/comfort-booking-reservations.php';$p=cbr_db();
$fields=$p->query('SHOW COLUMNS FROM reckoning')->fetchAll(PDO::FETCH_COLUMN);
if(!in_array('cb_submission_key',$fields,true))$p->exec('ALTER TABLE reckoning ADD cb_submission_key CHAR(64) NULL, ADD cb_site_complete TINYINT NOT NULL DEFAULT 0, ADD UNIQUE KEY cb_site_submission (cb_submission_key)');
$p->exec('CREATE TABLE IF NOT EXISTS cb_site_nonce (nonce CHAR(64) PRIMARY KEY,expires BIGINT NOT NULL) ENGINE=InnoDB');
if(in_array('--cancel-test-198967',$argv,true)){
 $r=$p->query('SELECT id,id_obj,status,date_z,date_v FROM reckoning WHERE id=198967')->fetch(PDO::FETCH_ASSOC);
 if(!$r||(int)$r['id_obj']!==1100000023)throw new RuntimeException('Unexpected test request');
 $site=cbr_request(['action'=>'detail','requestId'=>198967,'actor'=>1,'actorRights'=>6]);
 if(!empty($site['booking']))throw new RuntimeException('Booking exists; cancellation must use channel workflow');
 if((int)$r['status']!==6){
  if((int)$r['status']!==1)throw new RuntimeException('Test request changed');
  $dir='/var/backups/comfort-booking-test-198967';if(!is_dir($dir))mkdir($dir,0700,true);
  file_put_contents($dir.'/before.json',json_encode($r));chmod($dir.'/before.json',0600);
  $p->exec("UPDATE reckoning SET status=6,note=CONCAT(COALESCE(note,''),'\nТестовая заявка отменена по поручению Рустема. Бронь Comfort Booking не создавалась.') WHERE id=198967 AND status=1");
 }
 echo "Test 198967 cancelled; no channel booking exists.\n";
}
echo "Website intake schema ready.\n";
