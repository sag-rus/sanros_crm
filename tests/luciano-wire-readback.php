<?php
// Synthetic evidence is used only in this pure fixture; no DB, API or ACK command is called.
require __DIR__.'/luciano-wire-import-plan.php';
require __DIR__.'/../app/Support/LucianoWireReadback.php';
use App\Support\LucianoWireReadback as Readback;
use App\Support\LucianoWireEnvelope as Wire;

function readbackEvidence($object='1096',$property='14') {
    $e=planEnvelope($object,$property);
    $header=&$e['payload']['snapshot'];
    $header['generated_at']='2026-10-06 21:46:06';
    $header['checked_from']='2026-10-05 18:54:34';$header['checked_to']='2026-10-06 21:29:46';
    $header['origin_manifest_json']='{"quote_observed_at":null,"quote_checked_at_basis":"collection_start_lower_bound"}';
    unset($header);$e=rehash($e);$wire=Wire::pack($e);$json=Wire::encode($wire);
    $state=['source'=>'price_tonia_ru','crm_object_id'=>$object,'external_property_key'=>$property,
        'import_enabled'=>'1','publish_enabled'=>'1','active_snapshot_id'=>'42','stale_after_seconds'=>null,'timezone'=>'Europe/Moscow'];
    $snapshot=$e['payload']['snapshot'];unset($snapshot['origin_manifest_json']);
    $snapshot+=['id'=>'42','source'=>'price_tonia_ru','crm_object_id'=>$object,'status'=>'published',
        'checksum'=>$e['payload_sha256'],'manifest_json'=>Wire::encode(['transport_header'=>$e['payload']['snapshot']]),
        'imported_at'=>'2026-10-07 06:00:00','published_at'=>'2026-10-07 06:00:01','error_summary'=>null];
    $inbox=['snapshot_id'=>'42','sender'=>'price','chunk_key'=>'luciano-wire-v1','payload_sha256'=>$e['payload_sha256'],
        'row_count'=>'1','status'=>'applied','payload_json'=>$json,'received_at'=>'2026-10-07 05:59:59',
        'applied_at'=>'2026-10-07 06:00:00','error_summary'=>null];
    return [$object==='1096'?'kazan':'sochi',hash('sha256',$json),[$state],[$snapshot],[$inbox],$e['payload'],planFields($e)];
}
$checks=0;
foreach ([['1096','14'],['1658','15']] as $scope) {
    $args=readbackEvidence($scope[0],$scope[1]);$verified=Readback::verify(...$args);
    $receipt=$verified['receipt'];$wire=json_decode($args[4][0]['payload_json'],true);
    wireAssert($verified['readback_matches_original'] && !$verified['database_written'] && !$verified['ack_sent']
        && !$verified['catalogue_mapping_verified'] && !$verified['consumer_integrated'] && !$verified['delivery_ready']);$checks++;
    wireAssert($receipt['format']==='luciano-wire-receipt-v1' && $receipt['receiver']==='crm' && $receipt['status']==='applied'
        && $receipt['snapshot_key']===$wire['snapshot_key'] && $receipt['wire_sha256']===$args[1]
        && $receipt['payload_sha256']===$wire['payload_sha256'] && $receipt['mappings_sha256']===$wire['mappings_sha256']
        && $receipt['decoded_sha256']===$wire['decoded_sha256'] && count($receipt)===9);$checks++;
    wireAssert($receipt['section_counts']===['catalog'=>1,'daily'=>1,'observations'=>2,'offers'=>1,'restrictions'=>1]
        && $args[5]['data']['offers'][0]['total_amount']==='47650.00'
        && $args[5]['data']['offers'][0]['departure']==='2027-01-02'
        && $args[5]['data']['daily'][0]['checked_at']==='2026-10-05 18:54:34');$checks++;
    // Database drivers may return native integer identifiers and flags; floats/bools are rejected.
    $args[2][0]['crm_object_id']=(int)$scope[0];$args[2][0]['external_property_key']=(int)$scope[1];
    $args[2][0]['import_enabled']=1;$args[2][0]['publish_enabled']=1;$args[2][0]['active_snapshot_id']=42;
    $args[3][0]['id']=42;$args[3][0]['crm_object_id']=(int)$scope[0];$args[3][0]['schema_version']=2;$args[3][0]['row_count']=1;
    $args[4][0]['snapshot_id']=42;$args[4][0]['row_count']=1;
    wireAssert(Readback::verify(...$args)['readback_matches_original']);$checks++;
}
$base=readbackEvidence();
foreach (['wrong hotel','bad sha','empty state','duplicate state','empty snapshot','second snapshot','empty inbox','duplicate inbox',
    'foreign state source','foreign object','cross property','leading zero','float object','dormant import','dormant publish',
    'bool flag','wrong active snapshot','float active snapshot','stale threshold','wrong timezone',
    'foreign snapshot source','foreign snapshot object','bad snapshot id','staging snapshot','old schema','snapshot error',
    'wrong snapshot key','wrong checksum','wrong daily count','lost transport header','altered transport header','changed checked lower bound',
    'changed coverage','invalid imported timestamp','missing published timestamp','publication before import',
    'foreign inbox snapshot','wrong sender','legacy whole-v1','inbox received','inbox error','inbox checksum','inbox count',
    'noncanonical wire','changed stored wire','invalid receive timestamp','missing apply timestamp','apply before receive',
    'changed daily amount','changed exact total','lost observation','changed placement provenance','changed rule','extra row',
    'wrong payload property','changed original manifest','lost schema field'] as $case) {
    $a=$base;
    switch ($case) {
        case 'wrong hotel':$a[0]='sochi';break;
        case 'bad sha':$a[1]='invalid';break;
        case 'empty state':$a[2]=[];break;
        case 'duplicate state':$a[2][]=$a[2][0];break;
        case 'empty snapshot':$a[3]=[];break;
        case 'second snapshot':$a[3][]=$a[3][0];break;
        case 'empty inbox':$a[4]=[];break;
        case 'duplicate inbox':$a[4][]=$a[4][0];break;
        case 'foreign state source':$a[2][0]['source']='other';break;
        case 'foreign object':$a[2][0]['crm_object_id']='999';break;
        case 'cross property':$a[2][0]['external_property_key']='15';break;
        case 'leading zero':$a[2][0]['external_property_key']='014';break;
        case 'float object':$a[2][0]['crm_object_id']=1096.0;break;
        case 'dormant import':$a[2][0]['import_enabled']='0';break;
        case 'dormant publish':$a[2][0]['publish_enabled']='0';break;
        case 'bool flag':$a[2][0]['import_enabled']=true;break;
        case 'wrong active snapshot':$a[2][0]['active_snapshot_id']='43';break;
        case 'float active snapshot':$a[2][0]['active_snapshot_id']=42.0;break;
        case 'stale threshold':$a[2][0]['stale_after_seconds']='900';break;
        case 'wrong timezone':$a[2][0]['timezone']='UTC';break;
        case 'foreign snapshot source':$a[3][0]['source']='other';break;
        case 'foreign snapshot object':$a[3][0]['crm_object_id']='1658';break;
        case 'bad snapshot id':$a[3][0]['id']='042';break;
        case 'staging snapshot':$a[3][0]['status']='staging';break;
        case 'old schema':$a[3][0]['schema_version']='1';break;
        case 'snapshot error':$a[3][0]['error_summary']='failure';break;
        case 'wrong snapshot key':$a[3][0]['snapshot_key'].='x';break;
        case 'wrong checksum':$a[3][0]['checksum']=str_repeat('0',64);break;
        case 'wrong daily count':$a[3][0]['row_count']='2';break;
        case 'lost transport header':$a[3][0]['manifest_json']='{}';break;
        case 'altered transport header':$m=json_decode($a[3][0]['manifest_json'],true);$m['transport_header']['row_count']='2';$a[3][0]['manifest_json']=Wire::encode($m);break;
        case 'changed checked lower bound':$a[3][0]['checked_from']='2026-10-07 06:00:00';break;
        case 'changed coverage':$a[3][0]['coverage_to_exclusive']='2027-04-30';break;
        case 'invalid imported timestamp':$a[3][0]['imported_at']='2026-02-30 06:00:00';break;
        case 'missing published timestamp':$a[3][0]['published_at']=null;break;
        case 'publication before import':$a[3][0]['published_at']='2026-10-07 05:59:59';break;
        case 'foreign inbox snapshot':$a[4][0]['snapshot_id']='43';break;
        case 'wrong sender':$a[4][0]['sender']='crm';break;
        case 'legacy whole-v1':$a[4][0]['chunk_key']='whole-v1';break;
        case 'inbox received':$a[4][0]['status']='received';break;
        case 'inbox error':$a[4][0]['error_summary']='error';break;
        case 'inbox checksum':$a[4][0]['payload_sha256']=str_repeat('0',64);break;
        case 'inbox count':$a[4][0]['row_count']='2';break;
        case 'noncanonical wire':$a[4][0]['payload_json'].="\n";$a[1]=hash('sha256',$a[4][0]['payload_json']);break;
        case 'changed stored wire':$a[4][0]['payload_json'].='x';break;
        case 'invalid receive timestamp':$a[4][0]['received_at']='not UTC';break;
        case 'missing apply timestamp':$a[4][0]['applied_at']=null;break;
        case 'apply before receive':$a[4][0]['applied_at']='2026-10-07 05:59:58';break;
        case 'changed daily amount':$a[5]['data']['daily'][0]['amount']='22650.01';break;
        case 'changed exact total':$a[5]['data']['offers'][0]['total_amount']='95300.00';break;
        case 'lost observation':array_pop($a[5]['data']['observations']);break;
        case 'changed placement provenance':$a[5]['data']['observations'][0]['request_context_json']='{"checked_at_basis":"import_time"}';break;
        case 'changed rule':$a[5]['data']['restrictions'][0]['min_nights']='10';break;
        case 'extra row':$a[5]['data']['catalog'][]=$a[5]['data']['catalog'][0];break;
        case 'wrong payload property':$a[5]['external_property_key']='15';break;
        case 'changed original manifest':$a[5]['snapshot']['origin_manifest_json']='{}';break;
        case 'lost schema field':array_pop($a[6]['daily']);break;
    }
    wireReject(function() use($a){Readback::verify(...$a);});$checks++;
}
foreach ([2,3,4] as $index) foreach (array_keys($base[$index][0]) as $field) {
    $a=$base;unset($a[$index][0][$field]);
    wireReject(function() use($a){Readback::verify(...$a);});$checks++;
}
echo "PASS $checks Luciano applied read-back/receipt pure fixtures; no DB/API/ACK calls\n";
