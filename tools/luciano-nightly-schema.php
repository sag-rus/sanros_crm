<?php
// Add the lossless Luciano one-night model to the existing permitted model set.
if (PHP_SAPI!=='cli') exit(1);
try {
    if(count($argv)!==3 || !in_array($argv[1],['check','apply'],true) || !in_array($argv[2],['crm','site'],true)) throw new RuntimeException('Usage: luciano-nightly-schema.php check|apply crm|site');
    $mode=$argv[1];$role=$argv[2];
    $pdo=(function($role){$argv=[__FILE__,$role,dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';return $pdo;})($role);
    $plan=[];
    foreach(['external_price_observation','external_daily_price'] as $table){
        $create=$pdo->query('SHOW CREATE TABLE '.$table)->fetch(PDO::FETCH_NUM)[1];
        $found=[];
        foreach(explode("\n",$create) as $line) if(strpos($line,'CHECK')!==false && strpos($line,'`price_model`')!==false)$found[]=$line;
        if(count($found)!==1 || !preg_match('/CONSTRAINT `([a-zA-Z0-9_]+)` CHECK \(`price_model` in \((.*)\)\)/',$found[0],$m))throw new RuntimeException('Unexpected model constraint for '.$table);
        $current=str_replace(' ','',$m[2]);
        if($current==="'unknown','daily_independent','stay_dependent','nightly'")continue;
        if($current!=="'unknown','daily_independent','stay_dependent'")throw new RuntimeException('Unreviewed model set');
        $plan[$table]='ALTER TABLE `'.$table.'` DROP CONSTRAINT `'.$m[1]."`, ADD CONSTRAINT `".$m[1]."` CHECK (`price_model` in ('unknown','daily_independent','stay_dependent','nightly'))";
    }
    if($mode==='apply')foreach($plan as $sql)$pdo->exec($sql);
    echo json_encode(['mode'=>$mode,'role'=>$role,'tables'=>array_keys($plan),'data_changed'=>false]),"\n";
}catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}
