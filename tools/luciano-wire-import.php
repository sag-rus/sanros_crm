<?php
// Initial import only. Rehearsal executes and verifies the same transaction, then rolls it back.
if (PHP_SAPI!=='cli') exit(1);
foreach (['Envelope','ImportPlan','Destination','RowCursor','InitialConsumer'] as $part)
    require_once __DIR__.'/../app/Support/LucianoWire'.$part.'.php';
require_once __DIR__.'/../app/Support/LucianoStateOnboarding.php';
try {
    if (count($argv)!==6 || !in_array($argv[1],['rehearse','apply'],true)
        || !in_array($argv[2],['kazan','sochi'],true) || !preg_match('/^[a-f0-9]{64}$/D',$argv[4])
        || !preg_match('/^[a-f0-9]{40}$/D',$argv[5]))
        throw new RuntimeException('Usage: luciano-wire-import.php rehearse|apply kazan|sochi PACKET SHA256 INSTALLED_COMMIT');
    $mode=$argv[1];$hotel=$argv[2];$file=$argv[3];$sha=$argv[4];$commit=$argv[5];
    $git='git -C '.escapeshellarg(dirname(__DIR__)).' ';
    exec($git.'rev-parse HEAD',$head,$rc);
    if ($rc || implode('', $head)!==$commit) throw new RuntimeException('Installed commit differs');
    exec($git.'diff --quiet HEAD -- app/Support tools/luciano-wire-import.php',$out,$rc);
    if ($rc) throw new RuntimeException('Import code differs from installed commit');
    if (is_link($file) || !is_file($file) || filesize($file)>App\Support\LucianoWireEnvelope::MAX_WIRE_BYTES)
        throw new RuntimeException('Invalid staged packet');
    $json=file_get_contents($file);
    $connection=(function () {
        $argv=[__FILE__,'crm',dirname(__DIR__)];
        require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';
        require '/home/rustem/price-tonia-auto-sync-v1/common.php';
        return $pdo;
    })();
    $result=App\Support\LucianoWireInitialConsumer::apply($connection,$hotel,$json,$sha,$mode==='rehearse');
    $result['installed_commit']=$commit;
    echo App\Support\LucianoWireEnvelope::encode($result),PHP_EOL;
} catch (Throwable $e) {fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}
