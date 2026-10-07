<?php
// These cases must fail before the external bootstrap/DB is even reached.
$script=__DIR__.'/../tools/luciano-wire-preflight.php';
$valid='/var/tmp/price-tonia-luciano-wire-v1-1096-reload-v1-luciano-initial-1096-00000000-0000-4000-8000-000000000001-crm-packet.json';
$sha=str_repeat('a',64);
$cases=[
    [],
    ['foreign',$valid,$sha],
    ['kazan',$valid,'invalid'],
    ['kazan','/home/rustem/packet.json',$sha],
    ['sochi',$valid,$sha],
    ['kazan',str_replace('-1096-','-1658-',$valid),$sha],
    ['kazan',str_replace('-crm-packet.json','-site-packet.json',$valid),$sha],
    ['kazan','/var/tmp/../home/packet.json',$sha],
    ['kazan',$valid,$sha,'--apply'],
];
foreach ($cases as $args) {
    $command=escapeshellarg(PHP_BINARY).' '.escapeshellarg($script);
    foreach ($args as $arg) $command.=' '.escapeshellarg($arg);
    $lines=[];$exit=0;exec($command.' 2>&1',$lines,$exit);$output=implode("\n",$lines);
    $expected=strpos($output,'Usage:')!==false || strpos($output,'Foreign or unsupported packet path')!==false;
    if ($exit!==1 || strpos($output,'bootstrap')!==false || strpos($output,'PDO')!==false
        || !$expected) throw new RuntimeException('CLI boundary failed');
}
echo 'PASS '.count($cases)." CLI scope/path/argument rejection checks before DB bootstrap; no apply mode\n";
