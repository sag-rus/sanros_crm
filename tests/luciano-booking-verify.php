<?php
require __DIR__.'/luciano-booking-quote.php';
require __DIR__.'/../core/luciano_booking_verify.php';
class LucianoVerifyFixture {
    public $q;public $bad;
    function __construct($q){$this->q=$q;}
    function getAll($sql,...$args){
        $q=$this->q;$amount=$q['quoted_total'];
        if(strpos($sql,'SELECT st.')===0)return [['external_property_key'=>(string)$q['property_id'],'upstream_property_key'=>(string)$q['provider_id'],'import_enabled'=>'1','publish_enabled'=>'1','billing_basis'=>'night','timezone'=>'Europe/Moscow','snapshot_status'=>'published','snapshot_key'=>$q['snapshot_key'],'active_snapshot_id'=>'7','stale_after_seconds'=>null,'checksum'=>str_repeat('a',64),
            'manifest_json'=>json_encode(['transport_payload_sha256'=>str_repeat('a',64),'transport_header'=>['snapshot_key'=>$q['snapshot_key'],'origin_manifest_json'=>json_encode(['builder'=>'luciano-initial-payload-v1','sources'=>[substr($q['arrival'],0,7)=>['stage_sha256'=>str_repeat('b',64)]]])]])]];
        if(strpos($sql,'SELECT c.')===0)return [['id'=>'12','external_room_key'=>'111','external_rate_key'=>'222']];
        if(strpos($sql,'SELECT min_nights')===0)return $this->bad==='minimum'?[['min_nights'=>20,'closed'=>0]]:[];
        $r=['id'=>'31','observation_id'=>'31','currency'=>'RUB','availability_state'=>'observed_available','price_basis'=>$q['nights']===1?'room_per_night_for_occupancy':'stay_total','price_model'=>$q['nights']===1?'nightly':'stay_dependent','adults'=>$q['adults'],'expires_at'=>null,'amount'=>$amount,'total_amount'=>$this->bad==='amount'?'1.00':$amount,'arrival'=>$q['arrival'],'departure'=>$q['departure'],'nights'=>$q['nights'],'children_count'=>'0','evidence'=>'explicit','parser_version'=>'luciano-package-v1','external_room_key'=>'111','external_rate_key'=>'222','crm_room_id'=>$q['crm_room_id'],'crm_rate_id'=>$q['crm_rate_id']];
        $r['request_context_json']=json_encode(['provider_id'=>$q['provider_id'],'children'=>0,'arrival'=>$q['arrival'],'departure'=>$q['departure'],'adults'=>$q['adults'],'source_month'=>substr($q['arrival'],0,7),'stage_sha256'=>str_repeat($this->bad==='stage'?'c':'b',64)]);
        $r['source_data']=json_encode(['minimum_nights'=>null,'source_month'=>substr($q['arrival'],0,7)]);
        $days=[];for($i=0;$i<$q['nights'];$i++)$days[]=['date'=>(new DateTimeImmutable($q['arrival']))->modify('+'.$i.' days')->format('Y-m-d'),'amount'=>$i===0?$amount:'0.00'];
        $r['nightly_breakdown_json']=json_encode($days);return [$r];
    }
}
foreach([1096,1658]as $object)foreach([1,2,7]as $nights)foreach([1,2,3]as $adults){
    $data=lbq_input($object,$adults,$nights,'2027-01-15');$q=LucianoBookingQuote::parse($data,'2026-10-08');$db=new LucianoVerifyFixture($q);
    lbq_assert(luciano_booking_verify($data,$db)===$q['positions'],'verified unchanged position');
    foreach(['amount','minimum','stage']as $bad){$db->bad=$bad;try{luciano_booking_verify($data,$db);throw new LogicException('Accepted corrupt source');}catch(RuntimeException $e){}}
}
echo "PASS both Luciano CRM source validators: 18 package shapes and altered source/minimum rejection\n";
