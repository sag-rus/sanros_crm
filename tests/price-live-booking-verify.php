<?php
require __DIR__.'/../price_tonia_booking.php';
require __DIR__.'/../core/price_live_booking_verify.php';
class LiveBookingFixture {
    public $id,$provider,$inactive=false;
    function __construct($id){$this->id=$id;$this->provider=$id===47?'4345':'7733';}
    function getAll($sql,...$args){if($args!==[$this->id])throw new LogicException('Unscoped catalogue');return [['provider_id'=>$this->provider,'revision'=>str_repeat('a',64),'payload_json'=>json_encode(['object_id'=>$this->id,'provider_id'=>$this->provider,'revision'=>str_repeat('a',64),'rooms'=>[['code'=>'123','crm_id'=>10]],'rates'=>[['code'=>'456','crm_id'=>20]]])]];}
    function getOne($sql,...$args){if($args[1]!==$this->id)throw new LogicException('Unscoped inventory');return !$this->inactive;}
}
function liveAssert($v){if(!$v)throw new LogicException('Assertion failed');}
function liveReject($f){try{$f();}catch(RuntimeException $e){return;}throw new LogicException('Accepted invalid live booking');}
foreach([47,207]as $id){
 $db=new LiveBookingFixture($id);$arrival=(new DateTimeImmutable('today'))->modify('+10 days');$departure=$arrival->modify('+6 days')->format('Y-m-d');
 $p=['id_room'=>10,'rate'=>20,'date'=>$arrival->format('Y-m-d'),'departure'=>$departure,'days'=>6,'number'=>1,'type_index'=>3,'price_basis'=>'stay_total','price'=>'75600.00'];
 $d=(object)['id_obj'=>$id,'date'=>$arrival->format('d.m.Y'),'departure'=>$departure,'days'=>6,'adults'=>2,'childs'=>0,'tl'=>0,'bnovo'=>0,'position'=>json_encode([$p]),'sum'=>'75600.00','price_snapshot'=>'price-live:'.str_repeat('a',64),'guests'=>[['fio'=>'Первый Тест'],['fio'=>'Второй Тест']]];
 $response=['status'=>'available','object_id'=>$id,'provider_id'=>$db->provider,'criteria'=>['arrival'=>$p['date'],'departure'=>$departure,'adults'=>2,'children'=>[]],'expires_at'=>time()+300,'offers'=>[['room_code'=>'123','rate_code'=>'456','total'=>'75600.00','currency'=>'RUB']]];
 $fetch=function($object,$criteria)use($response,$id){liveAssert($object===$id&&$criteria===$response['criteria']);return $response;};
 liveAssert(price_live_booking_verify($d,$db,$fetch)===[$p]);
 foreach(['sum'=>'1.00','days'=>7,'adults'=>3,'price_snapshot'=>'price-live:'.str_repeat('b',64),'guests'=>[['fio'=>'Один Тест']]]as $k=>$v){$bad=clone $d;$bad->$k=$v;liveReject(function()use($bad,$db,$fetch){price_live_booking_verify($bad,$db,$fetch);});}
 foreach(['type_index'=>2,'id_room'=>11,'rate'=>21,'number'=>2,'days'=>5,'price'=>'12600.00','departure'=>$p['date']]as $k=>$v){$bad=clone $d;$bp=$p;$bp[$k]=$v;$bad->position=json_encode([$bp]);liveReject(function()use($bad,$db,$fetch){price_live_booking_verify($bad,$db,$fetch);});}
 foreach(['status'=>'pending','object_id'=>999,'provider_id'=>'wrong','expires_at'=>time()-1,'offers'=>[]]as $k=>$v){$bad=$response;$bad[$k]=$v;liveReject(function()use($d,$db,$bad){price_live_booking_verify($d,$db,function()use($bad){return $bad;});});}
 $changed=$response;$changed['offers'][0]['total']='80000.00';liveReject(function()use($d,$db,$changed){price_live_booking_verify($d,$db,function()use($changed){return $changed;});});
 $db->inactive=true;liveReject(function()use($d,$db,$fetch){price_live_booking_verify($d,$db,$fetch);});
}
$endpoint=file_get_contents(__DIR__.'/../kostyl_booking.php');liveAssert(strpos($endpoint,'if($type_index == 3 && !$price_tonia_booking)')!==false);
echo "PASS live CRM: exact totals, mappings, source confirmation, stale/foreign/tampered quotes and inactive inventory\n";
