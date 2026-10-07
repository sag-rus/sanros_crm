<?php
// Receives wire DATA, never code, through bounded stdin after installation from published Git.
if (PHP_SAPI!=='cli') exit(1);
require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
require_once __DIR__.'/../app/Support/LucianoWirePacketFile.php';
use App\Support\LucianoWireEnvelope as Wire;
use App\Support\LucianoWirePacketFile as Packet;
try {
    if (count($argv)!==4 || !in_array($argv[1],['kazan','sochi'],true)
        || !preg_match('/^[a-f0-9]{64}$/D',$argv[2]) || !preg_match('/^[a-f0-9]{40}$/D',$argv[3])) {
        throw new RuntimeException('Usage: luciano-wire-stage.php kazan|sochi WIRE_SHA256 INSTALLED_GIT_SHA < wire-DATA');
    }
    $prefix='cd '.escapeshellarg(dirname(__DIR__)).' && git ';$lines=[];$exit=0;
    exec($prefix.'rev-parse HEAD 2>&1',$lines,$exit);
    if ($exit!==0 || implode("\n",$lines)!==$argv[3]) throw new RuntimeException('Installed Git HEAD differs');
    $paths=['tools/luciano-wire-stage.php','app/Support/LucianoWirePacketFile.php','app/Support/LucianoWireEnvelope.php'];
    $quoted=implode(' ',array_map('escapeshellarg',$paths));$lines=[];
    exec($prefix.'ls-files --error-unmatch -- '.$quoted.' 2>&1',$lines,$exit);
    if ($exit!==0 || count($lines)!==count($paths)) throw new RuntimeException('Packet staging code is not tracked');
    $lines=[];exec($prefix.'diff --quiet HEAD -- '.$quoted.' 2>&1',$lines,$exit);
    if ($exit!==0) throw new RuntimeException('Packet staging code differs from installed commit');
    $json=stream_get_contents(STDIN,Wire::MAX_WIRE_BYTES+1);
    $report=Packet::stage($argv[1],$argv[2],$json);$report['installed_git_sha']=$argv[3];
    $report['memory_limit']=ini_get('memory_limit');$report['peak_memory_bytes']=memory_get_peak_usage(true);
    echo Wire::encode($report),PHP_EOL;
} catch (Throwable $error) { fwrite(STDERR,$error->getMessage().PHP_EOL);exit(1); }
