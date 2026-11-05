<?php
require __DIR__.'/../core/comfort-booking-reservations.php';
$n=0;function ok($v,$message){global $n;if(!$v)throw new RuntimeException($message);$n++;}
$request=['id'=>100,'id_user'=>7,'active'=>0];$csrf=str_repeat('a',64);
foreach([2,3,4,5,6] as $rights){$u=['id'=>7,'rights'=>$rights];ok(cbr_allowed($u,$request,'detail','',''),'owner reads');
    ok(cbr_allowed($u,$request,'prepare',$csrf,$csrf)===($rights>=3),'write rights');
    ok(!cbr_allowed($u,$request,'prepare','forged',$csrf),'CSRF');
    $other=$request;$other['id_user']=8;ok(cbr_scope($u,$other)===($rights>=4),'request ownership');}
ok(!cbr_allowed(false,$request,'detail','',''),'no anonymous access');
$request['active']=3;ok(!cbr_allowed(['id'=>7,'rights'=>5],$request,'commit',$csrf,$csrf),'deleted request immutable');
ok(!cbr_allowed(['id'=>7,'rights'=>5],$request,'invented',$csrf,$csrf),'unknown action');
echo "$n checks passed\n";
$p=new PDO('sqlite::memory:');$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$p->exec('CREATE TABLE reckoning (id INTEGER PRIMARY KEY,id_obj INTEGER,status INTEGER,status_san INTEGER,active INTEGER)');
$p->exec('CREATE TABLE history_schet (date TEXT,time TEXT,id_schet INTEGER,id_user INTEGER,new_status INTEGER,new_status_san INTEGER,note TEXT)');
$p->exec('INSERT INTO reckoning VALUES (100,101,1,0,0),(101,101,1,0,3)');
$d=['booking'=>['number'=>'test','hotelId'=>'101','status'=>'new'],'delivery'=>'confirmed','history'=>[['action'=>'cancel','actor_id'=>7]]];
ok(cbr_sync_cancelled($p,100,$d)===null,'active booking does not cancel request');
$d['booking']['status']='cancelled';$d['delivery']='waiting';
ok(cbr_sync_cancelled($p,100,$d)['changed']===true,'saved cancellation immediately cancels CRM while channel delivery pending');
ok((int)$p->query('SELECT status FROM reckoning WHERE id=100')->fetchColumn()===6,'CRM cancelled status persisted');
ok((int)$p->query('SELECT id_user FROM history_schet')->fetchColumn()===7,'cancelling actor audited');
ok(cbr_sync_cancelled($p,100,$d)['changed']===false,'repeat is idempotent');
$d['delivery']='confirmed';cbr_sync_cancelled($p,100,$d);
ok((int)$p->query('SELECT COUNT(*) FROM history_schet')->fetchColumn()===1,'acknowledgement does not duplicate history');
ok(cbr_sync_cancelled($p,101,$d)===null,'deleted CRM request preserved');
$d['booking']['hotelId']='999';$rejected=false;try{cbr_sync_cancelled($p,100,$d);}catch(RuntimeException $e){$rejected=true;}
ok($rejected,'mismatched property rejected');
echo "$n checks including cancellation sync passed\n";
