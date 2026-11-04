<?php
// Small pure validation fixtures, no actual artifact paths created by this file.
require __DIR__.'/luciano-wire-envelope.php';
require __DIR__.'/../app/Support/LucianoWirePacketFile.php';
use App\Support\LucianoWireEnvelope as Wire;
use App\Support\LucianoWirePacketFile as Packet;
$checks=0;
foreach ([['kazan','1096','14'],['sochi','1658','15']] as $s) {
    $wire=Wire::pack(envelope($s[1],$s[2]));$json=Wire::encode($wire);$sha=hash('sha256',$json);
    $r=Packet::validate($s[0],$sha,$json);
    wireAssert($r['packet_path']==='/var/tmp/price-tonia-luciano-wire-v1-'.$s[1].'-'.$wire['snapshot_key'].'-crm-packet.json'
        && $r['original_payload_sha256']===$wire['payload_sha256'] && $r['full_codec_verified']);$checks++;
    foreach (['database_written','snapshot_created','activated','outbox_created','gateway_called','ack_sent',
        'schema_and_quotes_validated','consumer_integrated','delivery_ready'] as $flag) { wireAssert($r[$flag]===false);$checks++; }
    foreach (['wrong hotel','wrong hash','short hash','upper hash','newline','space','bad JSON','wrong role',
        'wrong property','altered checksum','bad key','empty','oversized'] as $case) {
        $hotel=$s[0];$digest=$sha;$data=$json;$w=$wire;
        switch($case) {
            case 'wrong hotel':$hotel=$s[0]==='kazan'?'sochi':'kazan';break;
            case 'wrong hash':$digest=str_repeat('0',64);break;
            case 'short hash':$digest='abc';break;
            case 'upper hash':$digest=strtoupper($sha);break;
            case 'newline':$data.="\n";$digest=hash('sha256',$data);break;
            case 'space':$data=' '.$data;$digest=hash('sha256',$data);break;
            case 'bad JSON':$data='{}';$digest=hash('sha256',$data);break;
            case 'wrong role':$data=Wire::encode(Wire::pack(envelope($s[1],$s[2],'crm')));$digest=hash('sha256',$data);break;
            case 'wrong property':$w['external_property_key']='999';$data=Wire::encode($w);$digest=hash('sha256',$data);break;
            case 'altered checksum':$w['decoded_sha256']=str_repeat('0',64);$data=Wire::encode($w);$digest=hash('sha256',$data);break;
            case 'bad key':$w['snapshot_key']='../../file';$data=Wire::encode($w);$digest=hash('sha256',$data);break;
            case 'empty':$data='';$digest=hash('sha256',$data);break;
            case 'oversized':$data=str_repeat('x',Wire::MAX_WIRE_BYTES+1);$digest=hash('sha256',$data);break;
        }
        wireReject(function() use($hotel,$digest,$data){Packet::validate($hotel,$digest,$data);});$checks++;
    }
}
echo "PASS $checks Luciano packet DATA pure validation fixtures; no files/DB/API/ACK\n";
