<?php
require __DIR__.'/../core/comfort-booking-request-state.php';
set_error_handler(function($severity,$message){throw new ErrorException($message,0,$severity);});
$n=0;function check($v,$m){global $n;if(!$v)throw new RuntimeException($m);$n++;}
function rejects($f,$m){$thrown=false;try{$f();}catch(RuntimeException $e){$thrown=true;}check($thrown,$m);}
$d=['booking'=>['number'=>'fixture','hotelId'=>'101','status'=>'new','echoToken'=>'new-token','arrivalDate'=>'2026-12-14','departureDate'=>'2026-12-17',
 'roomStays'=>[['adults'=>1,'children'=>1,'total'=>['amountAfterTaxes'=>'4500.00'],'guests'=>[
 ['lastName'=>'Existing','firstName'=>'Adult'],['lastName'=>'New','firstName'=>'Child','isChild'=>true,'age'=>7]]]]],
 'selection'=>['roomId'=>202,'rateId'=>303], 'delivery'=>'confirmed',
 'confirmation'=>['externalNumber'=>'TL-fixture','status'=>'new','echoToken'=>'new-token'], 'history'=>[['action'=>'modify','actor_id'=>7]]];
check(cbr_documents_ready($d),'current creation acknowledged');
foreach(['waiting','error'] as $state){$x=$d;$x['delivery']=$state;check(!cbr_documents_ready($x),'pending/error blocks documents');}
$x=$d;$x['confirmation']['echoToken']='old-token';check(!cbr_documents_ready($x),'previous revision cannot unlock documents');
$x=$d;unset($x['confirmation']);$x['externalNumber']='TL-old';check(!cbr_documents_ready($x),'fallback number is not acknowledgement');
$x=$d;$x['confirmation']['error']=['code'=>123];check(!cbr_documents_ready($x),'channel error blocks documents');
$x=$d;$x['booking']['status']='modified';check(!cbr_documents_ready($x),'old creation acknowledgement cannot unlock modification');
$x['confirmation']['status']='modified';check(cbr_documents_ready($x),'current modification acknowledged');
$x['booking']['status']=$x['confirmation']['status']='cancelled';check(!cbr_documents_ready($x),'cancelled booking blocks documents');
$p=new PDO('sqlite::memory:');$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$p->exec('CREATE TABLE reckoning (id INTEGER PRIMARY KEY,id_obj INTEGER,status INTEGER,status_san INTEGER,active INTEGER,date_z TEXT,date_v TEXT,sum TEXT,number_turist INTEGER,children_rest INTEGER,rest TEXT)');
$p->exec('CREATE TABLE position_reck (id INTEGER PRIMARY KEY,schet INTEGER,id_room INTEGER,ratePlan TEXT,sum TEXT,number INTEGER,type INTEGER,days INTEGER,date_z TEXT,add_one_day INTEGER,id_service INTEGER)');
$p->exec('CREATE TABLE klient (id INTEGER PRIMARY KEY AUTOINCREMENT,surname TEXT,name TEXT,otch TEXT,date_reg TEXT,original_data TEXT,passport TEXT)');
$p->exec('CREATE TABLE history_schet (date TEXT,time TEXT,id_schet INTEGER,id_user INTEGER,new_status INTEGER,new_status_san INTEGER,note TEXT)');
$p->exec("INSERT INTO reckoning VALUES(100,101,1,0,0,'2026-12-10','2026-12-11','1000.00',1,0,'1')");
$p->exec("INSERT INTO position_reck VALUES(20,100,200,'300','1000.00',1,1,1,'2026-12-10',0,0)");
$p->exec("INSERT INTO klient VALUES(1,'Existing','Adult','','2026-01-01',NULL,'keep-passport')");
check(cbr_sync_request($p,100,$d)['changed'],'sync modified dates, room, price, occupants');
$r=$p->query('SELECT * FROM reckoning')->fetch(PDO::FETCH_ASSOC);$pos=$p->query('SELECT * FROM position_reck')->fetch(PDO::FETCH_ASSOC);
check($r['date_z']==='2026-12-14'&&$r['date_v']==='2026-12-17'&&$r['sum']==='4500.00','request dates and total');
check((int)$r['number_turist']===2&&$r['rest']==='1,2','guest count and ordered guest links');
check((int)$r['children_rest']===0,'unrelated children holiday flag preserved');
check((int)$pos['id_room']===202&&$pos['ratePlan']==='303'&&(int)$pos['days']===3&&(int)$pos['type']===3&&(int)$pos['number']===1&&(int)$pos['add_one_day']===1&&$pos['sum']==='4500.00','invoice position is whole stay, not price per day or per person');
check($p->query('SELECT passport FROM klient WHERE id=1')->fetchColumn()==='keep-passport','existing guest passport retained');
check((int)$p->query('SELECT id_user FROM history_schet')->fetchColumn()===7,'change audited');
check(!cbr_sync_request($p,100,$d)['changed'],'repeat sync idempotent');
check((int)$p->query('SELECT COUNT(*) FROM klient')->fetchColumn()===2&&(int)$p->query('SELECT COUNT(*) FROM history_schet')->fetchColumn()===1,'repeat has no guest or audit duplicates');
// A lost request update after a successful position update recovers without duplicate children.
$p->exec("UPDATE reckoning SET rest='1',number_turist=1");check(cbr_sync_request($p,100,$d)['changed'],'partial legacy write recovered');
check((int)$p->query('SELECT COUNT(*) FROM klient')->fetchColumn()===2,'recovery reuses created guest');
$d['booking']['roomStays'][0]['guests'][0]['lastName']='Replacement';
check(cbr_sync_request($p,100,$d)['changed'],'guest replacement synchronized');
check($p->query('SELECT surname FROM klient WHERE id=1')->fetchColumn()==='Existing','shared customer is not renamed');
check($p->query('SELECT rest FROM reckoning')->fetchColumn()==='3,2','updated guest attached');
$d['booking']['roomStays'][0]['children']=0;array_pop($d['booking']['roomStays'][0]['guests']);cbr_sync_request($p,100,$d);
check($p->query('SELECT rest FROM reckoning')->fetchColumn()==='3'&&(int)$p->query('SELECT number_turist FROM reckoning')->fetchColumn()===1,'removed guest detached but record retained');
$x=$d;$x['booking']['roomStays'][0]['guests'][0]['firstName']='<script>alert(1)</script>';rejects(function()use($p,$x){cbr_sync_request($p,100,$x);},'markup rejected before copying to legacy CRM');
$x=$d;$x['booking']['hotelId']='999';rejects(function()use($p,$x){cbr_sync_request($p,100,$x);},'hotel mismatch rejected');
$x=$d;$x['booking']['departureDate']='2026-02-30';rejects(function()use($p,$x){cbr_sync_request($p,100,$x);},'invalid dates rejected');
$x=$d;$x['booking']['roomStays'][0]['adults']=2;rejects(function()use($p,$x){cbr_sync_request($p,100,$x);},'incomplete guests rejected');
$x=$d;$x['booking']['roomStays'][]=$x['booking']['roomStays'][0];rejects(function()use($p,$x){cbr_sync_request($p,100,$x);},'unexpected multiple stays rejected');
$p->exec("INSERT INTO position_reck VALUES(21,100,0,'','100.00',1,3,1,'2026-12-14',1,9)");rejects(function()use($p,$d){cbr_sync_request($p,100,$d);},'manual extra service preserved and flagged');
check((int)$p->query('SELECT COUNT(*) FROM position_reck')->fetchColumn()===2,'no deletion of extra service');
require __DIR__.'/../core/status.php';
require __DIR__.'/../core/reckoning.php';
require __DIR__.'/../core/report/report.php';
class CbActionFixture {
    public $manager=0;
    function getRow($sql,...$args){return ['id'=>100,'id_obj'=>101,'cb_submission_key'=>'fixture','status'=>1,'status_san'=>0,'active'=>0,'holding_sum'=>0,'date_z'=>'2026-12-14','agency'=>0,'turist'=>1,'status_agent'=>0,'id_user'=>$this->manager,'changes'=>0];}
    function getOne($sql,...$args){return 0;}
    function query($sql,...$args){throw new RuntimeException('Unexpected write: '.$sql);}
}
$connect=new CbActionFixture;$id_rights=6;$session_login=7;$_POST=['id'=>100,'status'=>6];
foreach(['show_form_review_cancel','save_cancelation','save_cancelation_not_writing','form_reckoning_to_upsorted','reckoning_to_upsorted','delete_reckoning'] as $fn){
    http_response_code(200);ob_start();$fn($connect);$out=ob_get_clean();check(http_response_code()===409&&strpos($out,'Comfort Booking')!==false,'direct unsafe action blocked: '.$fn);
}
foreach([0,7] as $manager){
    $connect->manager=$manager;ob_start();show_menu_bid($connect);$out=ob_get_clean();
    check(strpos($out,'reckoning_to_upsorted(')===false&&strpos($out,'delete_reckoning(')===false&&strpos($out,'review_cancel(')===false,'unsafe actions absent for assigned/unassigned CB request');
}
$_POST=['id'=>'100','type'=>'cancel'];$out=update_mass_reckoning($connect);check(strpos($out,'обычная аннуляция недоступна')!==false,'mass cancellation cannot bypass CB cancellation');
echo "$n request synchronization checks passed\n";
