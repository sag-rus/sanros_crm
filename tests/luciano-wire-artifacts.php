<?php
require __DIR__.'/luciano-wire-envelope.php';
require __DIR__.'/../app/Support/LucianoWireArtifacts.php';
use App\Support\LucianoWireEnvelope as Wire;
use App\Support\LucianoWireArtifacts as Artifacts;
$checks=0;
function artifactCheck($ok) { global $checks; if (!$ok) throw new RuntimeException('Artifact assertion failed'); $checks++; }
function artifactReject($fn) { global $checks; wireReject($fn); $checks++; }
foreach ([['1096','14','price','crm'],['1658','15','price','crm'],['1096','14','crm','site'],['1658','15','crm','site']] as $scope) {
    $e=envelope($scope[0],$scope[1],$scope[2]);$w=Wire::pack($e);$a=Artifacts::inspect($w,$scope[3]);
    artifactCheck($a['original_payload_sha256']===$e['payload_sha256'] && $a['original_mappings_sha256']===$e['mappings_sha256']);
    artifactCheck($a['original_envelope_sha256']===hash('sha256',Wire::encode($e)) && $a['original_envelope_bytes']===strlen(Wire::encode($e)));
    artifactCheck($a['daily_row_count']===1 && $a['total_section_rows']===5 && count($a['section_counts'])===5);
    artifactCheck(Wire::encode(Wire::unpack(json_decode($a['proposed_inbox_payload_json'],true),$scope[3]))===Wire::encode($e));
    artifactCheck($a['wire_sha256']===hash('sha256',$a['proposed_inbox_payload_json']) && $a['wire_bytes']===strlen($a['proposed_inbox_payload_json']));
    artifactCheck($a['proposed_inbox_chunk_key']==='luciano-wire-v1' && $a['proposed_inbox_chunk_key']!=='whole-v1');
    foreach (['packet_path','export_path','catalogue_reserve_path'] as $path) {
        artifactCheck(strpos($a[$path],'/var/tmp/price-tonia-luciano-wire-v1-'.$scope[0].'-')===0
            && strpos($a[$path],'/home/')===false && strpos($a[$path],'..')===false && strlen(basename($a[$path]))<255);
    }
    artifactCheck($a['requires_exclusive_regular_files'] && $a['requires_file_mode']==='0600'
        && !$a['file_written'] && !$a['database_imported'] && !$a['consumer_integrated'] && !$a['delivery_ready']);
}
$w=Wire::pack(envelope());
artifactReject(function() use ($w) { Artifacts::inspect($w,'site'); });
$bad=$w;$bad['snapshot_key'].='/../../outside';
artifactReject(function() use ($bad) { Artifacts::inspect($bad,'crm'); });
$bad=$w;$bad['external_property_key']='15';
artifactReject(function() use ($bad) { Artifacts::inspect($bad,'crm'); });
$bad=$w;$bad['encoded_envelope']='not a packet';
artifactReject(function() use ($bad) { Artifacts::inspect($bad,'crm'); });
echo "PASS $checks Luciano wire artifact fixtures; no filesystem or database installation\n";
