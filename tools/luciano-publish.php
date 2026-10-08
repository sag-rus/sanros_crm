<?php
if(PHP_SAPI!=='cli')exit(1);
require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
require_once __DIR__.'/../app/Support/LucianoWireImportPlan.php';
try{
    if(count($argv)!==3||!in_array($argv[1],['check','apply'],true)||!in_array($argv[2],['kazan','sochi'],true))throw new RuntimeException('Usage: luciano-publish.php check|apply kazan|sochi');
    $apply=$argv[1]==='apply';$hotel=$argv[2];$object=$hotel==='kazan'?'1096':'1658';$property=$hotel==='kazan'?'14':'15';$provider=$hotel==='kazan'?'3026':'434';$role='crm';
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';require '/home/rustem/price-tonia-auto-sync-v1/common.php';return $pdo;})();
    if((string)rows($pdo,'SELECT GET_LOCK(?,0) held',['luciano_crm_initial_'.$object])[0]['held']!=='1')throw new RuntimeException('Initial operation busy');
    $pdo->beginTransaction();$st=tState($pdo,$object,true);$sn=rows($pdo,"SELECT * FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
    if(count($sn)!==1)throw new RuntimeException('Expected exactly one initial snapshot');$snap=$sn[0];$sid=$snap['id'];
    $already=$snap['status']==='published'&&(string)$st['active_snapshot_id']===(string)$sid&&(int)$st['import_enabled']===1&&(int)$st['publish_enabled']===1;
    if(!$already&&($snap['status']!=='ready'||$st['active_snapshot_id']!==null||(int)$st['import_enabled']!==0||(int)$st['publish_enabled']!==0))throw new RuntimeException('Unexpected publication state');
    if((string)$st['external_property_key']!==$property||(string)$st['upstream_property_key']!==$provider||$st['billing_basis']!=='night'||$st['timezone']!=='Europe/Moscow'||$st['stale_after_seconds']!==null)throw new RuntimeException('Wrong source identity');
    $source=rows($pdo,"SELECT enabled FROM external_price_source WHERE source='price_tonia_ru'");if(count($source)!==1||(int)$source[0]['enabled']!==1)throw new RuntimeException('Source is disabled');
    $in=rows($pdo,"SELECT * FROM external_sync_inbox WHERE snapshot_id=? AND sender='price' AND chunk_key='luciano-wire-v1'",[$sid]);if(count($in)!==1)throw new RuntimeException('Missing receipt evidence');
    $wire=json_decode($in[0]['payload_json'],true);$fields=[];foreach(tSections()as $s=>$t)$fields[$s]=tFieldNames($pdo,$t,$s);
    $valid=App\Support\LucianoWireImportPlan::validate($wire,$role,$fields);
    if(tHash(tPayload($pdo,$snap))!==$wire['payload_sha256']||$snap['checksum']!==$wire['payload_sha256'])throw new RuntimeException('Full source readback differs');
    $cat=rows($pdo,'SELECT * FROM external_price_catalog WHERE snapshot_id=?',[$sid]);
    foreach($cat as $c){
        if($c['status']!=='active'||!$c['crm_room_id'])throw new RuntimeException('Unmapped room');
        $r=rows($pdo,'SELECT id FROM room WHERE id=? AND id_obj=? AND active=0',[$c['crm_room_id'],$object]);if(count($r)!==1)throw new RuntimeException('Inactive or foreign room');
        $type=$c['entity_type'];$id=$c['crm_room_id'];
        if($type==='rate'){$id=$c['crm_rate_id'];$r=rows($pdo,'SELECT id FROM rate_plan WHERE id=? AND object=? AND status=1',[$id,$object]);if(count($r)!==1)throw new RuntimeException('Inactive or foreign rate');}
        $map=rows($pdo,"SELECT crm_id FROM external_price_mapping WHERE source='price_tonia_ru' AND crm_object_id=? AND external_property_key=? AND entity_type=? AND external_room_key=? AND external_rate_key=? AND mapping_status='verified'",[$object,$property,$type,$c['external_room_key'],$c['external_rate_key']]);
        if(count($map)!==1||(string)$map[0]['crm_id']!==(string)$id)throw new RuntimeException('Mapping does not match catalogue');
    }
    foreach(['external_daily_price','external_stay_offer']as $table){$r=rows($pdo,'SELECT COUNT(*) n FROM '.$table.' p JOIN external_price_catalog c ON c.id=p.catalog_id AND c.snapshot_id=p.snapshot_id WHERE p.snapshot_id=? AND (p.crm_room_id IS NULL OR p.crm_rate_id IS NULL OR p.crm_room_id<>c.crm_room_id OR p.crm_rate_id<>c.crm_rate_id)',[$sid]);if((int)$r[0]['n'])throw new RuntimeException('Quote mapping differs');}
    if($apply&&!$already){$now=gmdate('Y-m-d H:i:s');$pdo->prepare("UPDATE external_price_snapshot SET status='published',published_at=? WHERE id=? AND status='ready'")->execute([$now,$sid]);$pdo->prepare("UPDATE external_price_state SET active_snapshot_id=?,import_enabled=1,publish_enabled=1,last_success_at=?,updated_at=? WHERE source='price_tonia_ru' AND crm_object_id=?")->execute([$sid,$now,$now,$object]);$pdo->commit();}else $pdo->rollBack();
    echo tEncode(['object'=>$object,'snapshot_id'=>$sid,'snapshot_key'=>$snap['snapshot_key'],'published'=>$apply||$already,'full_source_verified'=>true,'catalogue_verified'=>true]),"\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
