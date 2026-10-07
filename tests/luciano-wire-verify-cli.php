<?php
// Invalid invocations must stop before the external CRM bootstrap is reached.
$script=__DIR__.'/../tools/luciano-wire-verify.php';
$key='reload-v1-luciano-initial-1096-00000000-0000-4000-8000-000000000001';$sha=str_repeat('a',64);
$cases=[[],['foreign',$key,$sha],['kazan',$key,'invalid'],['sochi',$key,$sha],
    ['kazan',str_replace('1096','1658',$key),$sha],['kazan',$key.'/../outside',$sha],
    ['kazan',strtoupper($key),$sha],['kazan','luciano-initial-00000000-0000-4000-8000-000000000001',$sha],
    ['kazan',$key,$sha,'--ack'],['kazan',$key,$sha,'--apply']];
foreach ($cases as $args) {
    $command=escapeshellarg(PHP_BINARY).' '.escapeshellarg($script);
    foreach ($args as $arg) $command.=' '.escapeshellarg($arg);
    $lines=[];$exit=0;exec($command.' 2>&1',$lines,$exit);$output=implode("\n",$lines);
    if ($exit!==1 || strpos($output,'bootstrap')!==false || strpos($output,'PDO')!==false
        || (strpos($output,'Usage:')===false && strpos($output,'Foreign or unsupported snapshot key')===false)) {
        throw new RuntimeException('Read-back CLI boundary failed');
    }
}
echo 'PASS '.count($cases)." read-back CLI negative scope/key/argument checks before bootstrap; no ACK/apply mode\n";
