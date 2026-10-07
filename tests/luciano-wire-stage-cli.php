<?php
$tool=__DIR__.'/../tools/luciano-wire-stage.php';$sha=str_repeat('a',64);$head=str_repeat('a',40);
$cases=[[],['foreign',$sha,$head],['kazan','bad',$head],['sochi',$sha,'bad'],
    ['kazan',strtoupper($sha),$head],['sochi',$sha,strtoupper($head)],
    ['kazan',$sha,$head,'--apply'],['kazan'],['sochi',$sha]];
$checks=0;
foreach ($cases as $args) {
    $cmd=escapeshellarg(PHP_BINARY).' '.escapeshellarg($tool);
    foreach ($args as $arg) $cmd.=' '.escapeshellarg($arg);
    $lines=[];$exit=0;exec($cmd.' </dev/null 2>&1',$lines,$exit);
    if ($exit!==1 || strpos(implode("\n",$lines),'Usage:')===false) throw new RuntimeException('Unsafe staging CLI arguments accepted');
    $checks++;
}
echo "PASS $checks packet staging CLI negative boundaries before Git/stdin/files\n";
