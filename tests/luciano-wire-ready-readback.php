<?php
// Pure supplied evidence only. Never connect to a database or send a receipt.
require __DIR__.'/luciano-wire-readback.php';
use App\Support\LucianoWireReadback as Readback;
use App\Support\LucianoWireEnvelope as Wire;

function readyEvidence($object='1096',$property='14') {
    $a=readbackEvidence($object,$property);
    $a[2][0]['import_enabled']='0';$a[2][0]['publish_enabled']='0';$a[2][0]['active_snapshot_id']=null;
    $a[2][0]+=['upstream_property_key'=>$object==='1096'?'3026':'434','local_property_id'=>null,
        'billing_basis'=>'night','last_attempt_at'=>null,'last_success_at'=>null,'last_error'=>null,
        'created_at'=>'2026-10-07 05:00:00','updated_at'=>'2026-10-07 05:00:00'];
    $a[3][0]['status']='ready';$a[3][0]['published_at']=null;
    $a[3][0]['upstream_snapshot_key']=$a[3][0]['snapshot_key'];$a[3][0]['created_at']='2026-10-07 05:59:59';
    $wire=json_decode($a[4][0]['payload_json'],true);
    $a[3][0]['manifest_json']=Wire::encode(['transport_header'=>$a[5]['snapshot'],'transport_sender'=>'price',
        'transport_payload_sha256'=>$wire['payload_sha256'],'transport_mappings_sha256'=>$wire['mappings_sha256']]);
    return $a;
}
$checks=0;
foreach ([['1096','14'],['1658','15']] as $scope) {
    $a=readyEvidence(...$scope);$v=Readback::verifyReady(...$a);
    wireAssert($v['ready_import_verified'] && $v['readback_matches_original'] && !isset($v['receipt'])
        && !$v['receipt_generated'] && !$v['ack_sent'] && !$v['published'] && !$v['activated']
        && !$v['database_written'] && !$v['delivery_ready']);$checks++;
    wireAssert($v['section_counts']===Readback::verify(...readbackEvidence(...$scope))['section_counts']
        && $a[5]['data']['offers'][0]['total_amount']==='47650.00'
        && $a[5]['data']['offers'][0]['departure']==='2027-01-02');$checks++;
    wireReject(function()use($a){Readback::verify(...$a);});$checks++;
    wireReject(function()use($scope){Readback::verifyReady(...readbackEvidence(...$scope));});$checks++;
}
$base=readyEvidence();
foreach (['hotel','missing state','second snapshot','second inbox','source','object','property','provider','local property',
    'billing','import enabled','publish enabled','active','stale','timezone','attempt','success','state error',
    'state created','state updated','state interval','ready status','published timestamp','upstream key','created timestamp',
    'created after import','import timestamp','apply mismatch','received interval','snapshot id overflow','inbox sender',
    'inbox chunk','inbox error','inbox sha','inbox count','wire bytes','snapshot checksum','snapshot count','manifest sender',
    'manifest payload','manifest mappings','original lower bound','dropped observation','changed exact total','derived daily',
    'changed rule','changed catalogue','changed provenance','schema','float flag','leading zero provider','missing provider'] as $case) {
    $a=$base;
    switch ($case) {
        case 'hotel':$a[0]='sochi';break;
        case 'missing state':$a[2]=[];break;
        case 'second snapshot':$a[3][]=$a[3][0];break;
        case 'second inbox':$a[4][]=$a[4][0];break;
        case 'source':$a[2][0]['source']='other';break;
        case 'object':$a[2][0]['crm_object_id']='1658';break;
        case 'property':$a[2][0]['external_property_key']='15';break;
        case 'provider':$a[2][0]['upstream_property_key']='434';break;
        case 'local property':$a[2][0]['local_property_id']='14';break;
        case 'billing':$a[2][0]['billing_basis']='stay';break;
        case 'import enabled':$a[2][0]['import_enabled']='1';break;
        case 'publish enabled':$a[2][0]['publish_enabled']='1';break;
        case 'active':$a[2][0]['active_snapshot_id']='42';break;
        case 'stale':$a[2][0]['stale_after_seconds']='900';break;
        case 'timezone':$a[2][0]['timezone']='UTC';break;
        case 'attempt':$a[2][0]['last_attempt_at']='2026-10-07 06:00:00';break;
        case 'success':$a[2][0]['last_success_at']='2026-10-07 06:00:00';break;
        case 'state error':$a[2][0]['last_error']='error';break;
        case 'state created':$a[2][0]['created_at']='2026-02-30 06:00:00';break;
        case 'state updated':$a[2][0]['updated_at']=null;break;
        case 'state interval':$a[2][0]['updated_at']='2026-10-07 04:59:59';break;
        case 'ready status':$a[3][0]['status']='staging';break;
        case 'published timestamp':$a[3][0]['published_at']='2026-10-07 06:00:01';break;
        case 'upstream key':$a[3][0]['upstream_snapshot_key'].='x';break;
        case 'created timestamp':$a[3][0]['created_at']=null;break;
        case 'created after import':$a[3][0]['created_at']='2026-10-07 06:00:01';break;
        case 'import timestamp':$a[3][0]['imported_at']=null;break;
        case 'apply mismatch':$a[4][0]['applied_at']='2026-10-07 06:00:01';break;
        case 'received interval':$a[4][0]['received_at']='2026-10-07 06:00:01';break;
        case 'snapshot id overflow':$a[3][0]['id']='18446744073709551616';break;
        case 'inbox sender':$a[4][0]['sender']='crm';break;
        case 'inbox chunk':$a[4][0]['chunk_key']='whole-v1';break;
        case 'inbox error':$a[4][0]['error_summary']='error';break;
        case 'inbox sha':$a[4][0]['payload_sha256']=str_repeat('0',64);break;
        case 'inbox count':$a[4][0]['row_count']='2';break;
        case 'wire bytes':$a[4][0]['payload_json'].=' ';break;
        case 'snapshot checksum':$a[3][0]['checksum']=str_repeat('0',64);break;
        case 'snapshot count':$a[3][0]['row_count']='2';break;
        case 'manifest sender':case 'manifest payload':case 'manifest mappings':
            $m=json_decode($a[3][0]['manifest_json'],true);
            $field=['manifest sender'=>'transport_sender','manifest payload'=>'transport_payload_sha256','manifest mappings'=>'transport_mappings_sha256'][$case];
            $m[$field]='wrong';$a[3][0]['manifest_json']=Wire::encode($m);break;
        case 'original lower bound':$a[3][0]['checked_from']='2026-10-07 06:00:00';break;
        case 'dropped observation':array_pop($a[5]['data']['observations']);break;
        case 'changed exact total':$a[5]['data']['offers'][0]['total_amount']='95300.00';break;
        case 'derived daily':$a[5]['data']['daily'][0]['price_model']='stay_dependent';break;
        case 'changed rule':$a[5]['data']['restrictions'][0]['minimum_nights']='10';break;
        case 'changed catalogue':$a[5]['data']['catalog'][0]['room_name']='Other';break;
        case 'changed provenance':$a[5]['snapshot']['origin_manifest_json']='{}';break;
        case 'schema':array_pop($a[6]['offers']);break;
        case 'float flag':$a[2][0]['import_enabled']=0.0;break;
        case 'leading zero provider':$a[2][0]['upstream_property_key']='03026';break;
        case 'missing provider':unset($a[2][0]['upstream_property_key']);break;
    }
    wireReject(function()use($a){Readback::verifyReady(...$a);});$checks++;
}
echo "PASS $checks ready read-back isolation/full-payload/no-receipt fixtures; no database or ACK called\n";
