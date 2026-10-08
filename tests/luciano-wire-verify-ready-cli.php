<?php
// Negative CLI boundaries fail before deployed bootstrap, file operations or a DB connection.
$entry=__DIR__.'/../tools/luciano-wire-verify-ready.php';
$key='reload-v1-luciano-initial-1096-5f47dae0-8273-46ad-8056-faaf703e909b';$sha=str_repeat('a',64);
$cases=[[],['kazan'],['other',$key,$sha],['kazan',$key,'bad'],['sochi',$key,$sha],
    ['kazan',str_replace('1096','01096',$key),$sha],['kazan',strtoupper($key),$sha],
    ['kazan','/home/rustem/packet.json',$sha],['kazan',$key,$sha,'--apply'],['kazan',$key,$sha,'--ack']];
foreach ($cases as $args) {
    $parts=array_merge([PHP_BINARY,$entry],$args);$command=implode(' ',array_map('escapeshellarg',$parts)).' 2>&1';
    exec($command,$output,$exit);
    if ($exit!==1 || strpos(implode("\n",$output),'bootstrap')!==false) throw new RuntimeException('CLI boundary reached deployed runtime');
    $output=[];
}
echo 'PASS '.count($cases)." ready verifier CLI rejection fixtures before bootstrap/DB; no import or ACK\n";
