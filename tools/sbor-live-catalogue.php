<?php
// Initial, idempotent pilot catalogue import. CRM changes are owned by this repository.
if(PHP_SAPI!=='cli')exit(1);
function slRows($p,$sql,$args=[]){$s=$p->prepare($sql);$s->execute($args);return $s->fetchAll(PDO::FETCH_ASSOC);}
function slInsert($p,$table,$v){
    foreach(slRows($p,'SHOW COLUMNS FROM `'.$table.'`') as $f)if(!array_key_exists($f['Field'],$v)&&$f['Null']!=='YES'&&$f['Default']===null&&strpos($f['Extra'],'auto_increment')===false)$v[$f['Field']]=preg_match('/int|decimal|float|double/',$f['Type'])?0:'';
    $s=$p->prepare('INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($v)).'`) VALUES ('.implode(',',array_fill(0,count($v),'?')).')');$s->execute(array_values($v));return (int)$p->lastInsertId();
}
try {
    $mode=$argv[1]??'';if(!in_array($mode,['plan','apply','export'],true)||count($argv)!==2)throw new RuntimeException('Usage: sbor-live-catalogue.php plan|apply|export');
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';return $pdo;})();
    if($mode==='export'){$r=slRows($pdo,'SELECT payload_json FROM price_live_catalogue WHERE object_id=47');if(count($r)!==1)throw new RuntimeException('Catalogue missing');echo $r[0]['payload_json'],"\n";exit;}
    $curl=curl_init('https://price.tonia.ru/api/live-search/47/catalogue');curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20]);$raw=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);curl_close($curl);
    if($status!==200||!is_string($raw)||strlen($raw)>2000000)throw new RuntimeException('Catalogue download failed');
    $cat=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
    if(($cat['object_id']??null)!==47||($cat['provider_id']??'')!=='4345'||($cat['source']??'')!=='price_tonia_live'||!preg_match('/^[a-f0-9]{64}$/D',$cat['revision']??'')||empty($cat['rooms'])||empty($cat['rates']))throw new RuntimeException('Unexpected catalogue identity');
    if((int)slRows($pdo,'SELECT GET_LOCK(?,0) held',['sbor_live_catalogue_47'])[0]['held']!==1)throw new RuntimeException('Import busy');
    if(slRows($pdo,"SELECT 1 FROM external_price_state WHERE crm_object_id=47"))throw new RuntimeException('Existing PRICE integration must not be overwritten');
    $plan=[];$seen=[];
    foreach(['rooms','rates']as $section)foreach($cat[$section]as $i=>$r){
        $code=$r['code']??'';if(!preg_match('/^[1-9][0-9]{0,9}$/D',$code)||empty($r['name'])||mb_strlen($r['name'])>255||isset($seen[$section.':'.$code]))throw new RuntimeException('Invalid catalogue item');$seen[$section.':'.$code]=true;
        if($section==='rooms')foreach($r['photos']as $url)if(!preg_match('~^https://price\.tonia\.ru/uploads/live/47/[a-f0-9]{64}\.(jpg|png|webp)$~D',$url))throw new RuntimeException('Unexpected photo URL');
        $table=$section==='rooms'?'room':'rate_plan';$owner=$section==='rooms'?'id_obj':'object';
        $old=slRows($pdo,'SELECT * FROM '.$table.' WHERE '.$owner.'=47 AND id_tl=?',[$code]);
        if(count($old)>1)throw new RuntimeException('Ambiguous native identity');
        $cat[$section][$i]['crm_id']=$old?(int)$old[0]['id']:null;
        $plan[]=['kind'=>$section,'code'=>$code,'action'=>$old?'reuse':'create','id'=>$old?(int)$old[0]['id']:null];
    }
    if($mode==='plan'){echo json_encode(['object_id'=>47,'rooms'=>count($cat['rooms']),'rates'=>count($cat['rates']),'plan'=>$plan],JSON_UNESCAPED_UNICODE),"\n";exit;}
    $pdo->exec("CREATE TABLE IF NOT EXISTS price_live_catalogue (object_id INT UNSIGNED NOT NULL PRIMARY KEY, provider_id VARCHAR(32) NOT NULL, revision CHAR(64) NOT NULL, payload_json LONGTEXT NOT NULL, updated_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $backup='/var/tmp/sbor-live-crm-before-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(3)).'.json';$f=fopen($backup,'x');if(!$f)throw new RuntimeException('Backup unavailable');chmod($backup,0600);fwrite($f,json_encode(['rooms'=>slRows($pdo,'SELECT * FROM room WHERE id_obj=47'),'rates'=>slRows($pdo,'SELECT * FROM rate_plan WHERE object=47'),'catalogue'=>slRows($pdo,'SELECT * FROM price_live_catalogue WHERE object_id=47')],JSON_UNESCAPED_UNICODE));fclose($f);
    // MyISAM room rows are recoverable by the unique object/provider code on a retry.
    foreach($cat['rooms']as &$r)if(!$r['crm_id'])$r['crm_id']=slInsert($pdo,'room',['id_obj'=>47,'id_tl'=>$r['code'],'name'=>$r['name'],'description'=>$r['description'],'active'=>0,'main_place'=>$r['main_places'],'add_place'=>$r['extra_places'],'wo_bed_place'=>0,'square'=>$r['area']??0,'note'=>'Каталог PRICE / TravelLine 4345. Актуальная цена рассчитывается на выбранные даты.']);unset($r);
    $pdo->beginTransaction();$now=time();$photos=0;
    foreach($cat['rates']as &$r)if(!$r['crm_id'])$r['crm_id']=slInsert($pdo,'rate_plan',['object'=>47,'id_tl'=>$r['code'],'name'=>$r['name'],'description'=>$r['description'],'status'=>1]);unset($r);
    foreach($cat['rooms']as $r)foreach($r['photos']as $sort=>$url){
        $existing=slRows($pdo,"SELECT f.id FROM core_models_file_file f JOIN app_models_site_bound b ON b.entity2_id=f.id AND b.entity2_type='file' WHERE b.entity1_type='room' AND b.entity1_id=? AND b.name='image' AND f.uri=?",[$r['crm_id'],$url]);
        if(!$existing){$ext=pathinfo($url,PATHINFO_EXTENSION);$id=slInsert($pdo,'core_models_file_file',['created'=>$now,'changed'=>$now,'status'=>1,'uid'=>1,'title'=>$r['name'],'description'=>'PRICE live / object 47','uri'=>$url,'mime'=>['jpg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'][$ext],'ext'=>$ext,'usages'=>1]);
            slInsert($pdo,'app_models_site_bound',['created'=>$now,'changed'=>$now,'status'=>1,'uid'=>1,'sort'=>$sort,'name'=>'image','entity1_type'=>'room','entity1_id'=>$r['crm_id'],'entity2_type'=>'file','entity2_id'=>$id,'title'=>$r['name'],'description'=>'PRICE live / object 47','tl_img_source'=>$url]);}
        $photos++;
    }
    $s=$pdo->prepare('INSERT INTO price_live_catalogue (object_id,provider_id,revision,payload_json,updated_at) VALUES (47,?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE provider_id=VALUES(provider_id),revision=VALUES(revision),payload_json=VALUES(payload_json),updated_at=VALUES(updated_at)');
    $s->execute(['4345',$cat['revision'],json_encode($cat,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);$pdo->commit();
    echo json_encode(['object_id'=>47,'rooms'=>count($cat['rooms']),'rates'=>count($cat['rates']),'photos'=>$photos,'backup'=>$backup,'revision'=>$cat['revision']],JSON_UNESCAPED_UNICODE),"\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
