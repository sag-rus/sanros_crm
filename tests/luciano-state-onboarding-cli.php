<?php
$script=__DIR__.'/../tools/luciano-state-onboarding.php';
$backup='/var/tmp/luciano-crm-1096-before-state-00000000-0000-4000-8000-000000000001.json';
$sha=str_repeat('a',64);$head=str_repeat('b',40);
$cases=[[],['preflight','foreign'],['preflight','kazan','--apply'],['apply','kazan'],
    ['apply','kazan','invalid',$backup,$head],['apply','kazan',$sha,$backup,'invalid'],
    ['apply','sochi',$sha,$backup,$head],['apply','kazan',$sha,'/home/rustem/backup.json',$head],
    ['apply','kazan',$sha,'/var/tmp/../home/backup.json',$head],
    ['apply','kazan',$sha,$backup,$head,'--activate']];
foreach ($cases as $args) {
    $cmd=escapeshellarg(PHP_BINARY).' '.escapeshellarg($script);foreach($args as $arg)$cmd.=' '.escapeshellarg($arg);
    $lines=[];$exit=0;exec($cmd.' 2>&1',$lines,$exit);$out=implode("\n",$lines);
    if ($exit!==1 || strpos($out,'bootstrap')!==false || strpos($out,'PDO')!==false
        || (strpos($out,'Usage:')===false && strpos($out,'Invalid onboarding digest, backup path or installed commit')===false)) throw new RuntimeException('Onboarding CLI guard failed');
}
echo 'PASS '.count($cases)." onboarding CLI negative boundaries before Git/DB/files; no fake live state\n";
