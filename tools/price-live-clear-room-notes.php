<?php
// Remove only the exact technical note written by the live PRICE importer.
if(PHP_SAPI!=='cli')exit(1);
try {
    $mode=$argv[1]??'';
    if(!in_array($mode,['plan','apply'],true)||count($argv)!==2)throw new RuntimeException('Usage: price-live-clear-room-notes.php plan|apply');
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';return $pdo;})();
    $rows=$pdo->query("SELECT r.id,r.id_obj,r.note,r.synchronized FROM room r JOIN price_live_catalogue c ON c.object_id=r.id_obj WHERE BINARY r.note=BINARY CONCAT('Каталог PRICE / TravelLine ',c.provider_id,'. Актуальная цена рассчитывается на выбранные даты.') ORDER BY r.id_obj,r.id")->fetchAll(PDO::FETCH_ASSOC);
    $objects=[];foreach($rows as $r)$objects[$r['id_obj']]=($objects[$r['id_obj']]??0)+1;
    $report=['mode'=>$mode,'rooms'=>count($rows),'objects'=>$objects];
    if($mode==='apply'&&$rows){
        $backup='/var/tmp/price-live-room-notes-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(3)).'.json';
        $f=fopen($backup,'x');if(!$f)throw new RuntimeException('Backup unavailable');
        if(!chmod($backup,0600))throw new RuntimeException('Cannot protect backup');
        $json=json_encode($rows,JSON_UNESCAPED_UNICODE);
        if(!is_string($json)||fwrite($f,$json)!==strlen($json))throw new RuntimeException('Backup incomplete');
        fclose($f);$report['backup']=$backup;$report['updated']=0;
        $stmt=$pdo->prepare("UPDATE room SET note='',synchronized=0 WHERE id=? AND id_obj=? AND BINARY note=BINARY ?");
        foreach($rows as $r){$stmt->execute([$r['id'],$r['id_obj'],$r['note']]);$report['updated']+=$stmt->rowCount();}
    }
    echo json_encode($report,JSON_UNESCAPED_UNICODE),"\n";
}catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}
