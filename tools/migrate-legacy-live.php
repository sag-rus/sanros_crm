<?php
// Run only after the legacy delivery worker has been quiesced; see docs/legacy-live-migration.md.
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/legacy-live-migration.php';
try {
 $role='crm';$mode=$argv[1]??'';$id=(int)($argv[2]??0);$scope=pllScope($id);
 if(count($argv)!==3||!in_array($mode,['plan','prepare'],true))throw new RuntimeException('Usage: migrate-legacy-live.php plan|prepare OBJECT_ID');
 $pdo=(function()use($role){$argv=[__FILE__,$role,dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';return $pdo;})();
 foreach(['auto_price_delivery_'.$id,'price_tonia_transfer','price_live_catalogue_'.$id]as $lock)if((int)pllRows($pdo,'SELECT GET_LOCK(?,0) held',[$lock])[0]['held']!==1)throw new RuntimeException('Legacy/live transfer is busy');
 $s=pllRows($pdo,'SELECT * FROM external_price_state WHERE crm_object_id=?',[$id]);if(count($s)!==1)throw new RuntimeException('Expected one legacy source');pllIdentity($s[0],$id);
 $m=pllRows($pdo,"SELECT * FROM external_price_mapping WHERE source='price_tonia_ru' AND crm_object_id=?",[$id]);
 $extra=pllRows($pdo,'SELECT * FROM object WHERE id=?',[$id]);if(count($extra)!==1)throw new RuntimeException('Object missing');
 foreach(['check_places', 'travelline_id', 'bnovo'] as $flag)if(!empty($extra[0][$flag])&&!($role==='site'&&$id===3&&$flag==='travelline_id'&&(int)$extra[0][$flag]===999))throw new RuntimeException('Unexpected external integration');
 $summary=['object_id'=>$id,'role'=>$role,'provider_id'=>$scope[0],'legacy_property_id'=>$scope[1],'mappings'=>count($m),'mode'=>$mode];
 if($mode==='plan'){echo json_encode($summary)."\n";exit;}
 // A complete, verified new dictionary is required before stopping the old import.
 $raw=@file_get_contents('https://price.tonia.ru/api/live-search/'.$id.'/catalogue',false,stream_context_create(['http'=>['timeout'=>30]]));$cat=json_decode($raw?:'',true);
 if(($cat['object_id']??null)!==$id||($cat['provider_id']??null)!==$scope[0]||($cat['source']??null)!=='price_tonia_live'||empty($cat['rooms'])||empty($cat['rates'])||!preg_match('/^[a-f0-9]{64}$/D',$cat['revision']??''))throw new RuntimeException('Validated live catalogue unavailable');
 $used=[];
 foreach(['rooms'=>['room','id_obj'],'rates'=>['rate_plan','object']]as $section=>$meta)foreach($cat[$section]as $item){
    $code=$item['code']??'';if(!preg_match('/^[1-9][0-9]{0,9}$/D',$code))throw new RuntimeException('Invalid source code');
    $native=pllRows($pdo,'SELECT id,id_tl FROM '.$meta[0].' WHERE '.$meta[1].'=? AND id_tl=?',[$id,$code]);
    if(count($native)>1)throw new RuntimeException('Ambiguous native source code');
    $mapped=pllMapping($m,$section,$code);
    if($mapped){
        $row=pllRows($pdo,'SELECT id,id_tl FROM '.$meta[0].' WHERE id=? AND '.$meta[1].'=?',[$mapped,$id]);
        if(count($row)!==1||(!empty($row[0]['id_tl'])&&(string)$row[0]['id_tl']!==$code)||($native&&(int)$native[0]['id']!==$mapped))throw new RuntimeException('Legacy/native identity collision');
        $native=$row;
    }
    if($native){$key=$section.':'.$native[0]['id'];if(isset($used[$key]))throw new RuntimeException('Two source codes share one identity');$used[$key]=true;}
 }
 $pdo->exec("CREATE TABLE IF NOT EXISTS price_live_legacy_migration (object_id INT UNSIGNED PRIMARY KEY,provider_id VARCHAR(32) NOT NULL,phase VARCHAR(16) NOT NULL,backup_path VARCHAR(255) NOT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
 $old=pllRows($pdo,'SELECT * FROM price_live_legacy_migration WHERE object_id=?',[$id]);
 if($old){pllGuard($pdo,$id,$role);echo json_encode($summary+['already_prepared'=>true,'backup'=>$old[0]['backup_path']])."\n";exit;}
 $backup='/var/tmp/price-legacy-'.$role.'-'.$id.'-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(4)).'.json';$f=fopen($backup,'x');if(!$f)throw new RuntimeException('Backup unavailable');chmod($backup,0600);
 $data=json_encode(['state'=>$s,'mappings'=>$m,'extra'=>$extra],JSON_UNESCAPED_UNICODE);if(!is_string($data))throw new RuntimeException('Backup encoding failed');if(fwrite($f,$data)!==strlen($data))throw new RuntimeException('Backup incomplete');fclose($f);
 $pdo->beginTransaction();
 $pdo->prepare("UPDATE external_price_state SET import_enabled=0".($role==='site'?'':',publish_enabled=0').",updated_at=UTC_TIMESTAMP() WHERE source='price_tonia_ru' AND crm_object_id=?")->execute([$id]);
 
 $pdo->prepare("INSERT INTO price_live_legacy_migration (object_id,provider_id,phase,backup_path,created_at,updated_at) VALUES (?,?,'prepared',?,UTC_TIMESTAMP(),UTC_TIMESTAMP())")->execute([$id,$scope[0],$backup]);
 $pdo->commit();echo json_encode($summary+['backup'=>$backup])."\n";
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
