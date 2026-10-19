<?php
require __DIR__.'/../core/price_live_manager.php';
function managerCheck($v){if(!$v)throw new LogicException('Failed manager assertion');}
function managerReject($f){try{$f();}catch(RuntimeException $e){return;}throw new LogicException('Invalid manager parameters accepted');}
foreach(ptl_live_providers()as $id=>$provider){
 $a=(new DateTimeImmutable('today'))->modify('+10 days');$d=$a->modify('+6 days');
 $r=['id'=>1,'id_obj'=>$id,'date_z'=>$a->format('Y-m-d'),'date_v'=>$d->format('Y-m-d'),'number_turist'=>2,'children_rest'=>0];
 $p=['id_room'=>10,'ratePlan'=>20,'date_z'=>$r['date_z'],'days'=>6,'number'=>1,'type'=>3,'add_one_day'=>1,'sum'=>'75600.00'];
 $c=['object_id'=>$id,'provider_id'=>$provider,'rooms'=>[['crm_id'=>10,'code'=>'123','name'=>'Room']],'rates'=>[['crm_id'=>20,'code'=>'456','name'=>'Rate']]];
 $v=ptl_live_payload($r,[$p],$c);managerCheck($v['quoted_total']==='75600.00');
 foreach(['type'=>2,'id_room'=>11,'ratePlan'=>21,'days'=>7,'number'=>2,'sum'=>'NaN']as $k=>$value){$bad=$p;$bad[$k]=$value;managerReject(function()use($r,$bad,$c){ptl_live_payload($r,[$bad],$c);});}
 $response=['status'=>'available','object_id'=>$id,'provider_id'=>$provider,'expires_at'=>time()+300,'criteria'=>['arrival'=>$r['date_z'],'departure'=>$r['date_v'],'adults'=>2,'children'=>[]],'offers'=>[['room_code'=>'123','rate_code'=>'456','total'=>'80000.00','currency'=>'RUB']]];
 $result=ptl_live_result($v,$response);managerCheck($result['status']==='available'&&$result['result']['total']==='80000.00');
 managerCheck(ptl_live_result($v,['status'=>'pending'])['status']==='running');
 $bad=$response;$bad['offers']=[];managerCheck(ptl_live_result($v,$bad)['status']==='unavailable');$bad['partial']=true;managerCheck(ptl_live_result($v,$bad)['status']==='error');
 foreach(['object_id'=>999,'provider_id'=>'wrong','expires_at'=>time()-1]as $k=>$value){$bad=$response;$bad[$k]=$value;managerReject(function()use($v,$bad){ptl_live_result($v,$bad);});}
}
echo "PASS live manager: exact totals, catalogue mappings, price changes, unavailable/partial/pending, expired and foreign replies\n";
