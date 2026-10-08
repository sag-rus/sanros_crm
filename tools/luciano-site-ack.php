<?php
if(PHP_SAPI!=='cli')exit(1);
foreach(['Envelope','ImportPlan']as $p)require __DIR__.'/../app/Support/LucianoWire'.$p.'.php';
try{
    if(count($argv)!==2||!in_array($argv[1],['kazan','sochi'],true))throw new RuntimeException('Usage: luciano-site-ack.php kazan|sochi < SITE_RECEIPT');
    $object=$argv[1]==='kazan'?'1096':'1658';$raw=stream_get_contents(STDIN,16385);if(strlen($raw)>16384)throw new RuntimeException('Receipt too large');$receipt=json_decode($raw,true);
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';require '/home/rustem/price-tonia-auto-sync-v1/common.php';return $pdo;})();
    $pdo->beginTransaction();$st=tState($pdo,$object,true);$out=rows($pdo,"SELECT o.*,s.snapshot_key,s.checksum,s.status snapshot_status FROM external_sync_outbox o JOIN external_price_snapshot s ON s.id=o.snapshot_id WHERE s.source='price_tonia_ru' AND s.crm_object_id=? AND o.destination='site' FOR UPDATE",[$object]);
    if(count($out)!==1)throw new RuntimeException('Expected one site outbox');$o=$out[0];
    if($o['snapshot_status']!=='published'||(string)$st['active_snapshot_id']!==(string)$o['snapshot_id']||!in_array($o['status'],['pending','failed','delivered'],true)||$o['lock_token']!==null)throw new RuntimeException('Outbox not ready');
    $wire=json_decode($o['payload_json'],true);$fields=[];foreach(tSections()as $s=>$t)$fields[$s]=tFieldNames($pdo,$t,$s);$valid=App\Support\LucianoWireImportPlan::validate($wire,'site',$fields);
    $expected=['format'=>'luciano-wire-receipt-v1','receiver'=>'site','status'=>'applied','snapshot_key'=>$wire['snapshot_key'],'payload_sha256'=>$wire['payload_sha256'],'mappings_sha256'=>$wire['mappings_sha256'],'wire_sha256'=>hash('sha256',$o['payload_json']),'decoded_sha256'=>$wire['decoded_sha256'],'section_counts'=>$valid['section_counts']];
    if(!is_array($receipt)||tEncode($receipt)!==tEncode($expected)||$o['event_key']!=='luciano-wire-v1-'.$wire['snapshot_key']||$o['checksum']!==$wire['payload_sha256'])throw new RuntimeException('Site receipt differs from sent packet');
    if($o['status']!=='delivered')$pdo->prepare("UPDATE external_sync_outbox SET status='delivered',acknowledged_snapshot_key=?,acknowledged_checksum=?,delivered_at=?,last_error=NULL WHERE id=?")->execute([$wire['snapshot_key'],$wire['payload_sha256'],gmdate('Y-m-d H:i:s'),$o['id']]);
    elseif($o['acknowledged_snapshot_key']!==$wire['snapshot_key']||$o['acknowledged_checksum']!==$wire['payload_sha256'])throw new RuntimeException('Stored ACK differs');
    $pdo->commit();echo tEncode(['object'=>$object,'status'=>'delivered','snapshot_key'=>$wire['snapshot_key']]),"\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
