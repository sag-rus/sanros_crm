<?php
// Idempotent, explicitly allowlisted live catalogue import. CRM changes are owned by this repository.
if(PHP_SAPI!=='cli')exit(1);
function slRows($p,$sql,$args=[]){$s=$p->prepare($sql);$s->execute($args);return $s->fetchAll(PDO::FETCH_ASSOC);}
function slInsert($p,$table,$v){
    foreach(slRows($p,'SHOW COLUMNS FROM `'.$table.'`') as $f)if(!array_key_exists($f['Field'],$v)&&$f['Null']!=='YES'&&$f['Default']===null&&strpos($f['Extra'],'auto_increment')===false)$v[$f['Field']]=preg_match('/int|decimal|float|double/',$f['Type'])?0:'';
    $s=$p->prepare('INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($v)).'`) VALUES ('.implode(',',array_fill(0,count($v),'?')).')');$s->execute(array_values($v));return (int)$p->lastInsertId();
}
try {
    $mode=$argv[1]??'';if(!in_array($mode,['plan','apply','export'],true)||!in_array(count($argv),[2,3,4],true))throw new RuntimeException('Usage: sbor-live-catalogue.php plan|apply|export [object_id]');
    $objectId=(int)($argv[2]??47);$providers=[47=>'4345',207=>'7733',13=>'23294',4=>'6811',25=>'18786',19=>'6263',29=>'6482',662=>'7822',10=>'17784',37=>'8830',50=>'7826',482=>'43592',1005=>'22525',16=>'6894',58=>'6538',23=>'6893',471=>'651',82=>'9712',56=>'1084',70=>'65227',24=>'23664',6=>'18577',39=>'6906',53=>'6281',410=>'47801'];
    if(!isset($providers[$objectId]))throw new RuntimeException('Unknown live object');$providerId=$providers[$objectId];
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';return $pdo;})();
    if($mode==='export'){$r=slRows($pdo,'SELECT payload_json FROM price_live_catalogue WHERE object_id=?',[$objectId]);if(count($r)!==1)throw new RuntimeException('Catalogue missing');echo $r[0]['payload_json'],"\n";exit;}
    if(($argv[3]??'')==='-'){$raw=stream_get_contents(STDIN,2000001);if(strlen($raw)>2000000)throw new RuntimeException('Catalogue too large');}
    else {
    $curl=curl_init('https://price.tonia.ru/api/live-search/'.$objectId.'/catalogue');curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20]);$raw=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);curl_close($curl);
    if($status!==200||!is_string($raw)||strlen($raw)>2000000)throw new RuntimeException('Catalogue download failed');
    }
    $cat=json_decode($raw,true);
    if(($cat['object_id']??null)!==$objectId||($cat['provider_id']??'')!==$providerId||($cat['source']??'')!=='price_tonia_live'||!preg_match('/^[a-f0-9]{64}$/D',$cat['revision']??'')||empty($cat['rooms'])||empty($cat['rates']))throw new RuntimeException('Unexpected catalogue identity');
    if((int)slRows($pdo,'SELECT GET_LOCK(?,0) held',['price_live_catalogue_'.$objectId])[0]['held']!==1)throw new RuntimeException('Import busy');
    if(slRows($pdo,"SELECT 1 FROM external_price_state WHERE crm_object_id=?",[$objectId]))throw new RuntimeException('Existing PRICE integration must not be overwritten');
    $object=slRows($pdo,'SELECT check_places,travelline_id,bnovo FROM object WHERE id=?',[$objectId]);
    if(count($object)!==1||array_filter($object[0]))throw new RuntimeException('Object has an existing integration');
    $plan=[];$seen=[];
    foreach(['rooms','rates']as $section)foreach($cat[$section]as $i=>$r){
        $code=$r['code']??'';if(!preg_match('/^[1-9][0-9]{0,9}$/D',$code)||empty($r['name'])||mb_strlen($r['name'])>255||isset($seen[$section.':'.$code]))throw new RuntimeException('Invalid catalogue item');$seen[$section.':'.$code]=true;
        if($section==='rooms')foreach($r['photos']as $url)if(!preg_match('~^https://price\.tonia\.ru/uploads/live/'.$objectId.'/[a-f0-9]{64}\.(jpg|png|webp)$~D',$url))throw new RuntimeException('Unexpected photo URL');
        $table=$section==='rooms'?'room':'rate_plan';$owner=$section==='rooms'?'id_obj':'object';
        $old=slRows($pdo,'SELECT * FROM '.$table.' WHERE '.$owner.'=? AND id_tl=?',[$objectId,$code]);
        if(count($old)>1)throw new RuntimeException('Ambiguous native identity');
        $cat[$section][$i]['crm_id']=$old?(int)$old[0]['id']:null;
        $plan[]=['kind'=>$section,'code'=>$code,'action'=>$old?'reuse':'create','id'=>$old?(int)$old[0]['id']:null];
    }
    $deactivate=[];foreach(['rooms'=>['room','id_obj','active',0],'rates'=>['rate_plan','object','status',1]]as $section=>$meta){
        $ids=array_values(array_filter(array_column($cat[$section],'crm_id')));$sql='SELECT id FROM '.$meta[0].' WHERE '.$meta[1].'=? AND '.$meta[2].'=?';$args=[$objectId,$meta[3]];
        if($ids){$sql.=' AND id NOT IN ('.implode(',',array_fill(0,count($ids),'?')).')';$args=array_merge($args,$ids);}$deactivate[$section]=array_map('intval',array_column(slRows($pdo,$sql,$args),'id'));
    }
    if($mode==='plan'){echo json_encode(['deactivate'=>$deactivate,'object_id'=>$objectId,'rooms'=>count($cat['rooms']),'rates'=>count($cat['rates']),'plan'=>$plan],JSON_UNESCAPED_UNICODE),"\n";exit;}
    $pdo->exec("CREATE TABLE IF NOT EXISTS price_live_catalogue (object_id INT UNSIGNED NOT NULL PRIMARY KEY, provider_id VARCHAR(32) NOT NULL, revision CHAR(64) NOT NULL, payload_json LONGTEXT NOT NULL, updated_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $backup='/var/tmp/price-live-crm-'.$objectId.'-before-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(3)).'.json';$f=fopen($backup,'x');if(!$f)throw new RuntimeException('Backup unavailable');chmod($backup,0600);$backupData=json_encode(['rooms'=>slRows($pdo,'SELECT * FROM room WHERE id_obj=?',[$objectId]),'rates'=>slRows($pdo,'SELECT * FROM rate_plan WHERE object=?',[$objectId]),'catalogue'=>slRows($pdo,'SELECT * FROM price_live_catalogue WHERE object_id=?',[$objectId]),'images'=>slRows($pdo,"SELECT b.* FROM app_models_site_bound b JOIN room r ON r.id=b.entity1_id WHERE r.id_obj=? AND b.entity1_type='room' AND b.name='image'",[$objectId])],JSON_UNESCAPED_UNICODE);if(!is_string($backupData)||fwrite($f,$backupData)!==strlen($backupData))throw new RuntimeException('Backup incomplete');fclose($f);
    // MyISAM room rows are recoverable by the unique object/provider code on a retry.
    foreach($cat['rooms']as &$r)if(!$r['crm_id'])$r['crm_id']=slInsert($pdo,'room',['id_obj'=>$objectId,'id_tl'=>$r['code'],'name'=>$r['name'],'description'=>$r['description'],'active'=>0,'main_place'=>$r['main_places'],'add_place'=>$r['extra_places'],'wo_bed_place'=>0,'square'=>$r['area']??0,'note'=>'']);unset($r);
    foreach($cat['rooms']as $r){$s=$pdo->prepare('UPDATE room SET name=?,description=?,main_place=?,add_place=?,square=?,synchronized=0 WHERE id=? AND id_obj=? AND id_tl=?');$s->execute([$r['name'],$r['description'],$r['main_places'],$r['extra_places'],$r['area']??0,$r['crm_id'],$objectId,$r['code']]);}
    $pdo->beginTransaction();$now=time();$photos=0;
    foreach($cat['rates']as &$r)if(!$r['crm_id'])$r['crm_id']=slInsert($pdo,'rate_plan',['object'=>$objectId,'id_tl'=>$r['code'],'name'=>$r['name'],'description'=>$r['description'],'status'=>1]);unset($r);
    foreach($cat['rates']as $r){$s=$pdo->prepare('UPDATE rate_plan SET name=?,description=?,synchronized=0 WHERE id=? AND object=? AND id_tl=?');$s->execute([$r['name'],$r['description'],$r['crm_id'],$objectId,$r['code']]);}
    foreach($cat['rooms']as $r){$s=$pdo->prepare("UPDATE app_models_site_bound SET status=0,changed=? WHERE entity1_type='room' AND entity1_id=? AND name='image'");$s->execute([$now,$r['crm_id']]);}
    foreach($cat['rooms']as $r)foreach($r['photos']as $sort=>$url){
        $existing=slRows($pdo,"SELECT f.id,b.id bound_id FROM core_models_file_file f JOIN app_models_site_bound b ON b.entity2_id=f.id AND b.entity2_type='file' WHERE b.entity1_type='room' AND b.entity1_id=? AND b.name='image' AND f.uri=?",[$r['crm_id'],$url]);
        if(!$existing){$ext=pathinfo($url,PATHINFO_EXTENSION);$id=slInsert($pdo,'core_models_file_file',['created'=>$now,'changed'=>$now,'status'=>1,'uid'=>1,'title'=>$r['name'],'description'=>'PRICE live / object '.$objectId,'uri'=>$url,'mime'=>['jpg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'][$ext],'ext'=>$ext,'usages'=>1]);
            slInsert($pdo,'app_models_site_bound',['created'=>$now,'changed'=>$now,'status'=>1,'uid'=>1,'sort'=>$sort,'name'=>'image','entity1_type'=>'room','entity1_id'=>$r['crm_id'],'entity2_type'=>'file','entity2_id'=>$id,'title'=>$r['name'],'description'=>'PRICE live / object '.$objectId,'tl_img_source'=>$url]);}
        if($existing){$s=$pdo->prepare('UPDATE app_models_site_bound SET status=1,sort=?,changed=?,title=? WHERE id=?');$s->execute([$sort,$now,$r['name'],$existing[0]['bound_id']]);}
        $photos++;
    }
    $s=$pdo->prepare('INSERT INTO price_live_catalogue (object_id,provider_id,revision,payload_json,updated_at) VALUES (?,?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE provider_id=VALUES(provider_id),revision=VALUES(revision),payload_json=VALUES(payload_json),updated_at=VALUES(updated_at)');
    $s->execute([$objectId,$providerId,$cat['revision'],json_encode($cat,JSON_UNESCAPED_UNICODE)]);
    // The validated PRICE catalogue is the only active inventory for this live object.
    foreach(['rooms'=>['room','id_obj','active',0,1],'rates'=>['rate_plan','object','status',1,0]]as $section=>$meta){
        $ids=array_column($cat[$section],'crm_id');$marks=implode(',',array_fill(0,count($ids),'?'));
        foreach([[false,$meta[4]],[true,$meta[3]]]as $change){$sql='UPDATE '.$meta[0].' SET '.$meta[2].'=?, synchronized=0 WHERE '.$meta[1].'=? AND id '.($change[0]?'IN':'NOT IN').' ('.$marks.') AND '.$meta[2].'<>?';$stmt=$pdo->prepare($sql);$stmt->execute(array_merge([$change[1],$objectId],$ids,[$change[1]]));}
    }
    $pdo->commit();
    echo json_encode(['deactivated'=>$deactivate,'object_id'=>$objectId,'rooms'=>count($cat['rooms']),'rates'=>count($cat['rates']),'photos'=>$photos,'backup'=>$backup,'revision'=>$cat['revision']],JSON_UNESCAPED_UNICODE),"\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
