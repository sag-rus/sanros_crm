<?php
/** Authenticated live PRICE booking validation. Reads only; never creates a booking. */
function price_live_booking_verify($data, $db, callable $fetch = null) {
    $providers=[47=>'4345',207=>'7733',13=>'23294',4=>'6811',25=>'18786',19=>'6263',29=>'6482',662=>'7822',10=>'17784',37=>'8830',50=>'7826',482=>'43592',1005=>'22525',16=>'6894',58=>'6538',23=>'6893',471=>'651',82=>'9712',56=>'1084',70=>'65227',24=>'23664',6=>'18577',39=>'6906',53=>'6281',410=>'47801',507=>'15955',55=>'15957',83=>'17814',141=>'13121',558=>'15955'];$id=(int)($data->id_obj??0);
    if(!isset($providers[$id]))throw new RuntimeException('Unknown live property');
    price_tonia_booking_guests($data);
    $arrival=DateTimeImmutable::createFromFormat('!d.m.Y',(string)($data->date??''));
    $departure=DateTimeImmutable::createFromFormat('!Y-m-d',(string)($data->departure??''));
    if(!$arrival||!$departure||$arrival->format('d.m.Y')!==$data->date||$departure->format('Y-m-d')!==$data->departure)throw new RuntimeException('Invalid live dates');
    $nights=(int)$arrival->diff($departure)->format('%r%a');$adults=(int)($data->adults??0);
    if($nights<1||$nights>30||(int)($data->days??0)!==$nights||$adults<1||$adults>3||(int)($data->childs??-1)!==0||(int)($data->tl??-1)!==0||(int)($data->bnovo??-1)!==0)throw new RuntimeException('Invalid live stay');
    $positions=json_decode($data->position??'',true);
    if(!is_array($positions)||count($positions)!==1||!isset($positions[0]))throw new RuntimeException('Invalid live positions');$p=$positions[0];
    if(($p['date']??null)!==$arrival->format('Y-m-d')||($p['departure']??null)!==$data->departure||(int)($p['days']??0)!==$nights||(int)($p['number']??0)!==1||(int)($p['type_index']??0)!==3||($p['price_basis']??null)!=='stay_total'||!is_string($p['price']??null)||!preg_match('/^[1-9][0-9]{0,9}\.\d{2}$/D',$p['price'])||$p['price']!==(string)($data->sum??''))throw new RuntimeException('Invalid live total');
    $rows=$db->getAll('SELECT provider_id,revision,payload_json FROM price_live_catalogue WHERE object_id=?i',$id);
    if(count($rows)!==1||$rows[0]['provider_id']!==$providers[$id]||($data->price_snapshot??null)!=='price-live:'.$rows[0]['revision'])throw new RuntimeException('Invalid live catalogue');
    $cat=json_decode($rows[0]['payload_json'],true);
    if(!is_array($cat)||($cat['object_id']??null)!==$id||($cat['provider_id']??null)!==$providers[$id]||($cat['revision']??null)!==$rows[0]['revision'])throw new RuntimeException('Invalid live identity');
    $room=null;$rate=null;
    foreach($cat['rooms']as $r)if((int)$r['crm_id']===(int)($p['id_room']??0)){if($room!==null)throw new RuntimeException('Ambiguous room');$room=$r['code'];}
    foreach($cat['rates']as $r)if((int)$r['crm_id']===(int)($p['rate']??0)){if($rate!==null)throw new RuntimeException('Ambiguous rate');$rate=$r['code'];}
    if($room===null||$rate===null||!$db->getOne('SELECT id FROM room WHERE id=?i AND id_obj=?i AND active=0',(int)$p['id_room'],$id)||!$db->getOne('SELECT id FROM rate_plan WHERE id=?i AND object=?i AND status=1',(int)$p['rate'],$id))throw new RuntimeException('Inactive live inventory');
    $criteria=['arrival'=>$arrival->format('Y-m-d'),'departure'=>$data->departure,'adults'=>$adults,'children'=>[]];
    $response=$fetch?$fetch($id,$criteria):price_live_booking_fetch($id,$criteria);
    if(!is_array($response)||($response['status']??null)!=='available'||($response['object_id']??null)!==$id||($response['provider_id']??null)!==$providers[$id]||($response['expires_at']??0)<=time())throw new RuntimeException('Live quote unavailable');
    foreach($criteria as $k=>$v)if(($response['criteria'][$k]??null)!==$v)throw new RuntimeException('Live criteria differ');
    $found=false;foreach($response['offers']??[]as $offer)if(($offer['room_code']??null)===$room&&($offer['rate_code']??null)===$rate&&($offer['currency']??null)==='RUB'&&($offer['total']??null)===$p['price'])$found=true;
    if(!$found)throw new RuntimeException('Live price changed');
    return $positions;
}
function price_live_booking_fetch($id,array $criteria) {
    $token=@file_get_contents('/var/lib/price-tonia-live/token');if(!is_string($token)||trim($token)==='')throw new RuntimeException('Live credentials unavailable');
    $requestId=bin2hex(random_bytes(16));$curl=curl_init('https://price.tonia.ru/api/live-search/'.$id.'/search');
    curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($criteria),CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.trim($token),'X-Request-ID: '.$requestId],CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>65]);
    $raw=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);curl_close($curl);
    if(!in_array($status,[200,202,503],true)||!is_string($raw)||strlen($raw)>2000000)throw new RuntimeException('Live verification failed');
    return json_decode($raw,true);
}
