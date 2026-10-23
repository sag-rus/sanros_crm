<?php
require __DIR__.'/../price_tonia_booking.php';
require __DIR__.'/../core/price_live_booking_verify.php';
class LiveBookingFixture {
    public $id,$provider,$inactive=false;
    function __construct($id){$this->id=$id;$this->provider=[47=>'4345',207=>'7733',13=>'23294',4=>'6811',25=>'18786',19=>'6263',29=>'6482',662=>'7822',10=>'17784',37=>'8830',50=>'7826',482=>'43592',1005=>'22525',16=>'6894',58=>'6538',23=>'6893',471=>'651',82=>'9712',56=>'1084',70=>'65227',24=>'23664',6=>'18577',39=>'6906',53=>'6281',410=>'47801',507=>'15955',55=>'15957',83=>'17814',141=>'13121',558=>'15955',84=>'8016',534=>'18602',859=>'1222',492=>'22850',470=>'20191',51=>'48479',52=>'381',582=>'14219',821=>'6459',177=>'67567',117=>'56945',120=>'64627',253=>'5673',132=>'23634',146=>'64623',170=>'493',187=>'61292',198=>'1628',258=>'15177',225=>'1269',233=>'26544',248=>'7256',250=>'1630',260=>'7032',263=>'12563',270=>'1712',274=>'5914',275=>'1308',281=>'57411',295=>'63714'][$id];}
    function getAll($sql,...$args){if($args!==[$this->id])throw new LogicException('Unscoped catalogue');return [['provider_id'=>$this->provider,'revision'=>str_repeat('a',64),'payload_json'=>json_encode(['object_id'=>$this->id,'provider_id'=>$this->provider,'revision'=>str_repeat('a',64),'rooms'=>[['code'=>'123','crm_id'=>10]],'rates'=>[['code'=>'456','crm_id'=>20]]])]];}
    function getOne($sql,...$args){if($args[1]!==$this->id)throw new LogicException('Unscoped inventory');return !$this->inactive;}
}
function liveAssert($v){if(!$v)throw new LogicException('Assertion failed');}
function liveReject($f){try{$f();}catch(RuntimeException $e){return;}throw new LogicException('Accepted invalid live booking');}
foreach([47,207,13,4,25,19,29,662,10,37,50,482,1005,16,58,23,471,82,56,70,24,6,39,53,410,507,55,83,141,558,84,534,859,492,470,51,52,582,821,177,117,120,253,132,146,170,187,198,258,225,233,248,250,260,263,270,274,275,281,295]as $id){
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
