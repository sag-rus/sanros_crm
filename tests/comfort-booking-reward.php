<?php
require __DIR__.'/../core/comfort-booking-reward.php';
set_error_handler(function($severity,$message){throw new ErrorException($message,0,$severity);});
function check($value,$message){if(!$value)throw new RuntimeException($message);}
function get_reward_object($db,$object,$date){return $db->legacy($object,$date);}
$db = new class {
    public $calls=0;
    public $key=null;
    function legacy($id,$date){$this->calls++;check($id===7&&$date==='2027-01-16','legacy arguments preserved');return 7;}
    function getOne($sql,$id){check($id===100,'request checked');return $this->key;}
};
check(cb_intake_reward($db,7,'2027-01-16')===7,'ordinary intake retains object commission');
define('CB_SITE_INTAKE',true);
check(cb_intake_reward($db,7,'2027-01-16')===12 && $db->calls===1,'authenticated CB intake bypasses ordinary object reward');
check(cb_position_reward($db,100,9)===9,'ordinary edit preserves chosen reward');
$db->key=str_repeat('a',64);
check(cb_position_reward($db,100,0)===12,'CB edit cannot reset reward');
$p=new PDO('sqlite::memory:');$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$p->exec('CREATE TABLE reckoning (id INTEGER PRIMARY KEY,cb_submission_key TEXT,status INTEGER,status_san INTEGER)');
$p->exec('CREATE TABLE position_reck (id INTEGER PRIMARY KEY,schet INTEGER,reward REAL,id_room INTEGER,id_service INTEGER)');
$p->exec('CREATE TABLE history_schet (date TEXT,time TEXT,id_schet INTEGER,id_user INTEGER,new_status INTEGER,new_status_san INTEGER,note TEXT)');
$p->exec("INSERT INTO reckoning VALUES (1,'signed-cb',6,0),(2,NULL,1,0),(3,'',1,0)");
$p->exec('INSERT INTO position_reck VALUES (1,1,0,99,NULL),(2,1,5,0,7),(3,2,8,99,NULL),(4,3,9,99,NULL),(5,1,NULL,99,0)');
$before=cb_reward_rows($p,1);
check(count($before)===2,'only CB room positions selected, including null reward');
check(cb_backfill_reward($p,1,$before)===2,'cancelled CB request repaired without changing status');
check((int)$p->query('SELECT status FROM reckoning WHERE id=1')->fetchColumn()===6,'cancellation preserved');
check(cb_reward_rows($p,2)===[]&&cb_reward_rows($p,3)===[],'ordinary requests excluded');
check((float)$p->query('SELECT reward FROM position_reck WHERE id=2')->fetchColumn()===5.0,'extra service excluded');
check(cb_reward_rows($p,1)===[]&&cb_backfill_reward($p,1,[])===0,'backfill idempotent');
check((int)$p->query('SELECT COUNT(*) FROM history_schet')->fetchColumn()===1,'one durable audit');
$failed=false;try{cb_backfill_reward($p,1,$before);}catch(RuntimeException $e){$failed=true;}
check($failed,'stale backup rejected');
echo "Comfort Booking reward checks passed\n";
