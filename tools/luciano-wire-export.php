<?php
if(PHP_SAPI!=='cli')exit(1);
foreach(['Envelope','ImportPlan','Readback','SiteExport'] as $p)require_once __DIR__.'/../app/Support/LucianoWire'.$p.'.php';
try{
    if(count($argv)!==2||!in_array($argv[1],['kazan','sochi'],true))throw new RuntimeException('Usage: luciano-wire-export.php kazan|sochi');
    $hotel=$argv[1];$object=$hotel==='kazan'?'1096':'1658';
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';require '/home/rustem/price-tonia-auto-sync-v1/common.php';return $pdo;})();
    $pdo->beginTransaction();$st=rows($pdo,"SELECT * FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=? FOR UPDATE",[$object]);$sn=rows($pdo,"SELECT * FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
    if(count($sn)!==1)throw new RuntimeException('Ambiguous initial');$sid=$sn[0]['id'];
    $in=rows($pdo,"SELECT * FROM external_sync_inbox WHERE snapshot_id=? AND sender='price' AND chunk_key='luciano-wire-v1'",[$sid]);if(count($in)!==1)throw new RuntimeException('Missing source inbox');
    $fields=[];foreach(tSections()as $s=>$t)$fields[$s]=tFieldNames($pdo,$t,$s);
    $maps=array_map('tScalars',rows($pdo,"SELECT entity_type,external_property_key,external_room_key,external_rate_key,crm_id,mapping_status FROM external_price_mapping WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]));
    $result=App\Support\LucianoWireSiteExport::build($hotel,hash('sha256',$in[0]['payload_json']),$st,$sn,$in,tPayload($pdo,$sn[0]),$fields,$maps);
    $wire=$result['wire'];$json=tEncode($wire);$event='luciano-wire-v1-'.$wire['snapshot_key'];
    $old=rows($pdo,"SELECT * FROM external_sync_outbox WHERE snapshot_id=? AND destination='site'",[$sid]);
    if($old){if(count($old)!==1||$old[0]['payload_json']!==$json||$old[0]['event_key']!==$event)throw new RuntimeException('Existing outbox differs');}
    else tInsert($pdo,'external_sync_outbox',['snapshot_id'=>$sid,'destination'=>'site','event_key'=>$event,'status'=>'pending','payload_json'=>$json,'created_at'=>gmdate('Y-m-d H:i:s')]);
    $pdo->commit();$file='/var/tmp/luciano-'.$hotel.'-site-wire.json';tWrite($file,$wire);$receipt='/var/tmp/luciano-'.$hotel.'-crm-receipt.json';tWrite($receipt,$result['source_crm_receipt']);
    echo tEncode(['packet'=>$file,'wire_sha256'=>$result['wire_sha256'],'receipt'=>$receipt,'mapping_count'=>$result['mapping_count'],'section_counts'=>$result['section_counts']]),"\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
