<?php
// Withdraw only the unverified Arshan import. Preserve all native records and history.
if (PHP_SAPI !== 'cli') exit(1);
try {
    $mode=$argv[1]??'';
    if (!in_array($mode,['plan','apply'],true)) throw new RuntimeException('Use plan or apply');
    $role='crm'; $id=653;
    $argv=[__FILE__,$role,dirname(__DIR__)];
    require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';
    function wr($p,$sql,$args=[]){$s=$p->prepare($sql);$s->execute($args);return $s->fetchAll(PDO::FETCH_ASSOC);}
    foreach(['price_live_catalogue_653','price_live_import_653'] as $lock)
        if((int)wr($pdo,'SELECT GET_LOCK(?,0) held',[$lock])[0]['held']!==1)throw new RuntimeException('Import busy');
    $current=wr($pdo,'SELECT * FROM price_live_catalogue WHERE object_id=?',[$id]);
    if(!$current){echo "Already withdrawn\n";exit;}
    if(count($current)!==1||$current[0]['provider_id']!=='17175'||($role==='site'&&(int)$current[0]['enabled']!==0))throw new RuntimeException('Unexpected or enabled catalogue');
    $object=wr($pdo,$role==='crm'?'SELECT check_places,travelline_id,bnovo FROM object WHERE id=?':'SELECT prices_api,travelline_id,bnovo FROM app_models_resort_resort WHERE id=?',[$id]);
    if(count($object)!==1||array_filter($object[0]))throw new RuntimeException('Existing integration');
    $files=glob('/var/tmp/price-live-'.$role.'-653-before-*.json');sort($files);
    $original=null;$allImported=['rooms'=>[],'rates'=>[]];
    foreach(array_merge($files,[null]) as $file){
        $b=$file?json_decode(file_get_contents($file),true):['catalogue'=>$current];
        if(!is_array($b)||!isset($b['catalogue']))throw new RuntimeException('Invalid backup');
        if($original===null&&$file&&$b['catalogue']===[]){$original=$b;$originalPath=$file;}
        foreach($b['catalogue'] as $row){
            if((int)$row['object_id']!==653||$row['provider_id']!=='17175')throw new RuntimeException('Foreign backup');
            $cat=json_decode($row['payload_json'],true);
            foreach(['rooms','rates'] as $section)foreach($cat[$section] as $item)$allImported[$section][(int)$item['crm_id']]=true;
        }
    }
    if(!$original||!isset($original['rooms'],$original['rates']))throw new RuntimeException('Original backup missing');
    $meta=$role==='crm'?['rooms'=>['room','id_obj','active',1],'rates'=>['rate_plan','object','status',0]]:['rooms'=>['app_models_resort_room_room','resort_id','status',0],'rates'=>['app_models_resort_price_rate','resort_id','status',0]];
    $snapshot=['catalogue'=>$current];$plan=[];
    foreach($meta as $section=>$m){
        $snapshot[$section]=wr($pdo,'SELECT * FROM '.$m[0].' WHERE '.$m[1].'=?',[$id]);$old=[];
        foreach($original[$section] as $r){if((int)$r[$m[1]]!==653||isset($allImported[$section][(int)$r['id']]))throw new RuntimeException('Original identity collision');$old[(int)$r['id']]=$r;}
        $seen=[];foreach($snapshot[$section] as $r){$rid=(int)$r['id'];$seen[$rid]=true;if(!isset($old[$rid])&&!isset($allImported[$section][$rid]))throw new RuntimeException('Unexpected later native record');}
        foreach($old as $rid=>$r)if(!isset($seen[$rid]))throw new RuntimeException('Original record missing');
        $plan[$section]=['restore'=>count($old),'deactivate'=>count($allImported[$section])];
    }
    if($mode==='plan'){echo json_encode(['object_id'=>653,'original_backup'=>$originalPath,'changes'=>$plan])."\n";exit;}
    $backup='/var/tmp/price-live-'.$role.'-653-withdraw-'.gmdate('YmdHis').'.json';
    $f=fopen($backup,'x');if(!$f)throw new RuntimeException('Backup unavailable');chmod($backup,0600);$raw=json_encode($snapshot);if(!is_string($raw)||fwrite($f,$raw)!==strlen($raw))throw new RuntimeException('Backup incomplete');fclose($f);
    $pdo->beginTransaction();
    foreach($meta as $section=>$m){
        $stmt=$pdo->prepare('UPDATE '.$m[0].' SET '.$m[2].'=?'.($role==='crm'?',synchronized=0':'').' WHERE id=? AND '.$m[1].'=?');
        foreach($allImported[$section] as $rid=>$unused)$stmt->execute([$m[3],$rid,$id]);
        foreach($original[$section] as $r)$stmt->execute([(int)$r[$m[2]],(int)$r['id'],$id]);
    }
    $pdo->prepare('DELETE FROM price_live_catalogue WHERE object_id=? AND provider_id=?')->execute([$id,'17175']);
    $pdo->commit();
    if($role==='site'){require_once dirname(__DIR__).'/vendor/autoload.php';\Maksatech\Core\Core::cacheInit();}
    echo json_encode(['object_id'=>653,'withdrawn'=>true,'backup'=>$backup,'changes'=>$plan])."\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
