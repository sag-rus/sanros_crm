<?php
// Scoped, source-code based catalogue creation. Existing legacy rows are never rewritten.
if(PHP_SAPI!=='cli')exit(1);
function lrows($p,$sql,$args=[]){$s=$p->prepare($sql);$s->execute($args);return $s->fetchAll(PDO::FETCH_ASSOC);}
function linsert($p,$table,$values){
    foreach(lrows($p,'SHOW COLUMNS FROM `'.$table.'`') as $f){
        if($f['Field']==='id'||array_key_exists($f['Field'],$values)||$f['Null']==='YES'||$f['Default']!==null||strpos($f['Extra'],'auto_increment')!==false)continue;
        $values[$f['Field']]=preg_match('/int|decimal|float|double/',$f['Type'])?0:'';
    }
    $sql='INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($values)).'`) VALUES ('.implode(',',array_fill(0,count($values),'?')).')';
    $s=$p->prepare($sql);$s->execute(array_values($values));return (string)$p->lastInsertId();
}
try{
    if(count($argv)!==3||!in_array($argv[1],['plan','apply'],true)||!in_array($argv[2],['kazan','sochi'],true))throw new RuntimeException('Usage: luciano-catalogue-map.php plan|apply kazan|sochi');
    $apply=$argv[1]==='apply';$hotel=$argv[2];$object=$hotel==='kazan'?'1096':'1658';$property=$hotel==='kazan'?'14':'15';
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';require '/home/rustem/price-tonia-auto-sync-v1/common.php';return $pdo;})();
    if((string)lrows($pdo,'SELECT GET_LOCK(?,0) held',['luciano_crm_initial_'.$object])[0]['held']!=='1')throw new RuntimeException('Luciano import busy');
    $states=lrows($pdo,"SELECT * FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
    $sn=lrows($pdo,"SELECT * FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
    if(count($states)!==1||count($sn)!==1||$sn[0]['status']!=='ready'||$states[0]['active_snapshot_id']!==null||(int)$states[0]['import_enabled']!==0||(int)$states[0]['publish_enabled']!==0)throw new RuntimeException('Expected one dormant ready initial');
    $snapshot=$sn[0];$sid=$snapshot['id'];
    if(!hash_equals($snapshot['checksum'],hash('sha256',tEncode(tPayload($pdo,$snapshot)))))throw new RuntimeException('Source readback differs');
    $cat=lrows($pdo,'SELECT * FROM external_price_catalog WHERE snapshot_id=? ORDER BY entity_type DESC,id',[$sid]);$plan=[];$entities=[];
    foreach($cat as $r){
        $type=$r['entity_type'];$code=$type==='room'?$r['external_room_key']:$r['external_rate_key'];$key=$type.':'.$code;
        if(!in_array($type,['room','rate'],true)||!preg_match('/^[1-9][0-9]*$/D',$code))throw new RuntimeException('Invalid source identity');
        if(isset($entities[$key]))continue;
        $table=$type==='room'?'room':'rate_plan';$owner=$type==='room'?'id_obj':'object';$active=$type==='room'?'active':'status';$wanted=$type==='room'?'0':'1';
        $rows=lrows($pdo,'SELECT * FROM '.$table.' WHERE '.$owner.'=? AND id_tl=?',[$object,$code]);
        if(count($rows)>1||($rows&&(string)$rows[0][$active]!==$wanted))throw new RuntimeException('Ambiguous or inactive existing source identity '.$key);
        $meta=json_decode($r['metadata_json'],true);$legacy=$meta[$type==='room'?'legacy_room':'legacy_rate'];
        $values=['id_tl'=>$code,$owner=>$object,'name'=>$r['name'],'description'=>$r['description'],$active=>$wanted];
        if($type==='room')$values+=['main_place'=>max(1,(int)($legacy['main_places']??$legacy['capacity']??1)),'add_place'=>(int)($legacy['extra_places']??0),'wo_bed_place'=>0,'square'=>$legacy['area']??0];
        $entities[$key]=['id'=>$rows?(string)$rows[0]['id']:null,'table'=>$table,'values'=>$values];
        $plan[]=['entity'=>$key,'action'=>$rows?'reuse':'create','crm_id'=>$rows?(string)$rows[0]['id']:null];
    }
    // Complete preflight before creating any nontransactional room rows.
    $old=lrows($pdo,"SELECT * FROM external_price_mapping WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
    foreach($old as $m){$code=$m['entity_type']==='room'?$m['external_room_key']:$m['external_rate_key'];$e=$entities[$m['entity_type'].':'.$code]??null;
        if(!$e||$m['external_property_key']!==$property||$m['mapping_status']!=='verified'||(string)$m['crm_id']!==$e['id'])throw new RuntimeException('Existing mapping differs');}
    if($apply){
        $backup='/var/tmp/luciano-catalogue-before-'.$object.'-'.gmdate('YmdHis').'.json';$f=fopen($backup,'x');if(!$f)throw new RuntimeException('Backup unavailable');chmod($backup,0600);fwrite($f,json_encode(['snapshot'=>$snapshot,'plan'=>$plan,'mappings'=>$old],JSON_UNESCAPED_UNICODE));fclose($f);
        foreach($entities as &$e)if($e['id']===null)$e['id']=linsert($pdo,$e['table'],$e['values']);unset($e);
        $pdo->beginTransaction();$now=gmdate('Y-m-d H:i:s');
        foreach($cat as $r){$room=$entities['room:'.$r['external_room_key']]['id'];$rate=$r['entity_type']==='rate'?$entities['rate:'.$r['external_rate_key']]['id']:null;
            $matches=lrows($pdo,"SELECT * FROM external_price_mapping WHERE source='price_tonia_ru' AND crm_object_id=? AND entity_type=? AND external_property_key=? AND external_room_key=? AND external_rate_key=?",[$object,$r['entity_type'],$property,$r['external_room_key'],$r['external_rate_key']]);
            $id=$r['entity_type']==='room'?$room:$rate;
            if(!$matches)linsert($pdo,'external_price_mapping',['source'=>'price_tonia_ru','crm_object_id'=>$object,'entity_type'=>$r['entity_type'],'external_property_key'=>$property,'external_room_key'=>$r['external_room_key'],'external_rate_key'=>$r['external_rate_key'],'crm_id'=>$id,'mapping_status'=>'verified','checked_at'=>$now,'last_seen_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
            elseif(count($matches)!==1||(string)$matches[0]['crm_id']!==$id)throw new RuntimeException('Mapping changed');
            $s=$pdo->prepare('UPDATE external_price_catalog SET crm_room_id=?,crm_rate_id=? WHERE id=? AND snapshot_id=?');$s->execute([$room,$rate,$r['id'],$sid]);
        }
        foreach(['external_daily_price','external_stay_offer'] as $table){$s=$pdo->prepare('UPDATE '.$table.' p JOIN external_price_catalog c ON c.id=p.catalog_id AND c.snapshot_id=p.snapshot_id SET p.crm_room_id=c.crm_room_id,p.crm_rate_id=c.crm_rate_id WHERE p.snapshot_id=?');$s->execute([$sid]);}
        if(!hash_equals($snapshot['checksum'],hash('sha256',tEncode(tPayload($pdo,$snapshot)))))throw new RuntimeException('Mapping modified source');
        $pdo->commit();
    }
    echo json_encode(['object'=>$object,'applied'=>$apply,'plan'=>$plan,'ids'=>array_map(function($e){return $e['id'];},$entities),'backup'=>$backup??null],JSON_UNESCAPED_UNICODE),"\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
