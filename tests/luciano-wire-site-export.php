<?php
// Synthetic supplied evidence only: no DB, export file, outbox, API or ACK.
require __DIR__.'/luciano-wire-readback.php';
require __DIR__.'/../app/Support/LucianoWireSiteExport.php';
use App\Support\LucianoWireSiteExport as SiteExport;
use App\Support\LucianoWireEnvelope as Wire;

function exportMaps($property='14') {
    return [
        ['entity_type'=>'room','external_property_key'=>$property,'external_room_key'=>'59767',
            'external_rate_key'=>'','crm_id'=>'100','mapping_status'=>'verified'],
        ['entity_type'=>'rate','external_property_key'=>$property,'external_room_key'=>'59767',
            'external_rate_key'=>'707838','crm_id'=>'200','mapping_status'=>'verified']
    ];
}
function exportArgs($object='1096',$property='14') {
    $a=readbackEvidence($object,$property);$a[]=exportMaps($property);return $a;
}
function exportReseal($a) {
    $e=Wire::unpack(json_decode($a[4][0]['payload_json'],true),'crm');
    $e['payload']=$a[5];$e=rehash($e);$json=Wire::encode(Wire::pack($e));
    $a[1]=hash('sha256',$json);$a[3][0]['checksum']=$e['payload_sha256'];
    $a[4][0]['payload_sha256']=$e['payload_sha256'];$a[4][0]['payload_json']=$json;
    return $a;
}
$checks=0;
foreach ([['1096','14'],['1658','15']] as $scope) {
    $a=exportArgs($scope[0],$scope[1]);$r=SiteExport::build(...$a);
    $e=Wire::unpack($r['wire'],'site');$original=json_decode($a[4][0]['payload_json'],true);
    wireAssert($r['export_plan_only'] && $r['sender']==='crm' && $r['receiver']==='site'
        && $r['mapping_count']===2 && $r['mapping_identities_checked']);$checks++;
    wireAssert(Wire::encode($e['payload'])===Wire::encode($a[5])
        && $r['original_payload_sha256']===$original['payload_sha256']
        && $r['wire']['snapshot_key']===$original['snapshot_key']
        && $r['wire']['decoded_sha256']!==$original['decoded_sha256']
        && $r['source_crm_receipt']['wire_sha256']===$a[1]);$checks++;
    wireAssert($e['payload']['data']['offers'][0]['total_amount']==='47650.00'
        && $e['payload']['data']['offers'][0]['departure']==='2027-01-02'
        && $e['payload']['data']['daily'][0]['checked_at']==='2026-10-05 18:54:34'
        && $e['payload']['data']['restrictions'][0]['min_nights']==='20');$checks++;
    foreach (['legacy_catalogue_rows_verified','site_schema_verified','photos_verified','database_written',
        'file_written','outbox_created','ack_sent','consumer_integrated','delivery_ready'] as $flag) {
        wireAssert($r[$flag]===false);$checks++;
    }
    $a[7]=array_reverse($a[7]);wireAssert(SiteExport::build(...$a)['wire_sha256']===$r['wire_sha256']);$checks++;
    wireReject(function() use($r){Wire::unpack($r['wire'],'crm');});$checks++;
}
$base=exportArgs();
foreach (['missing room','missing rate','duplicate','extra','foreign property','integer property','pending mapping',
    'missing status','extra field','unknown type','room has rate','rate missing code','null rate','null room',
    'integer id','float id','zero id','leading zero id','negative id','overlong id','malformed row',
    'wrong room','wrong rate','wrong hotel','dormant state','second snapshot','legacy inbox',
    'changed daily amount','changed exact total','lost source observation'] as $case) {
    $a=$base;
    switch($case) {
        case 'missing room':array_shift($a[7]);break;
        case 'missing rate':array_pop($a[7]);break;
        case 'duplicate':$a[7][]=$a[7][0];break;
        case 'extra':$a[7][]=$a[7][0];$a[7][2]['external_room_key']='other';break;
        case 'foreign property':$a[7][0]['external_property_key']='15';break;
        case 'integer property':$a[7][0]['external_property_key']=14;break;
        case 'pending mapping':$a[7][0]['mapping_status']='pending';break;
        case 'missing status':unset($a[7][0]['mapping_status']);break;
        case 'extra field':$a[7][0]['name']='same human name';break;
        case 'unknown type':$a[7][0]['entity_type']='object';break;
        case 'room has rate':$a[7][0]['external_rate_key']='707838';break;
        case 'rate missing code':$a[7][1]['external_rate_key']='';break;
        case 'null rate':$a[7][0]['external_rate_key']=null;break;
        case 'null room':$a[7][0]['external_room_key']=null;break;
        case 'integer id':$a[7][0]['crm_id']=100;break;
        case 'float id':$a[7][0]['crm_id']=100.0;break;
        case 'zero id':$a[7][0]['crm_id']='0';break;
        case 'leading zero id':$a[7][0]['crm_id']='0100';break;
        case 'negative id':$a[7][0]['crm_id']='-1';break;
        case 'overlong id':$a[7][0]['crm_id']=str_repeat('9',21);break;
        case 'malformed row':$a[7][0]=null;break;
        case 'wrong room':$a[7][1]['external_room_key']='other';break;
        case 'wrong rate':$a[7][1]['external_rate_key']='other';break;
        case 'wrong hotel':$a[0]='sochi';break;
        case 'dormant state':$a[2][0]['import_enabled']='0';break;
        case 'second snapshot':$a[3][]=$a[3][0];break;
        case 'legacy inbox':$a[4][0]['chunk_key']='whole-v1';break;
        case 'changed daily amount':$a[5]['data']['daily'][0]['amount']='22650.01';break;
        case 'changed exact total':$a[5]['data']['offers'][0]['total_amount']='95300.00';break;
        case 'lost source observation':array_pop($a[5]['data']['observations']);break;
    }
    wireReject(function() use($a){SiteExport::build(...$a);});$checks++;
}
// Hotel-wide source rate IDs remain consistent across rooms; separate codes cannot collapse by name.
$a=$base;$cat=$a[5]['data']['catalog'][0];$cat['external_room_key']='88656';
$cat['catalog_key']=hash('sha256',Wire::encode(['rate','88656','707838']));$a[5]['data']['catalog'][]=$cat;
$room=$a[7][0];$room['external_room_key']='88656';$room['crm_id']='101';$a[7][]=$room;
$rate=$a[7][1];$rate['external_room_key']='88656';$a[7][]=$rate;$a=exportReseal($a);
wireAssert(SiteExport::build(...$a)['mapping_count']===4);$checks++;
$b=$a;$b[7][3]['crm_id']='201';wireReject(function() use($b){SiteExport::build(...$b);});$checks++;
$b=$a;$b[7][2]['crm_id']='100';wireReject(function() use($b){SiteExport::build(...$b);});$checks++;
$b=$a;$b[5]['data']['catalog'][1]['external_rate_key']='643054';
$b[5]['data']['catalog'][1]['catalog_key']=hash('sha256',Wire::encode(['rate','88656','643054']));
$b[7][3]['external_rate_key']='643054';$b=exportReseal($b);
wireReject(function() use($b){SiteExport::build(...$b);});$checks++;
$b[7][3]['crm_id']='201';wireAssert(SiteExport::build(...$b)['mapping_count']===4);$checks++;
echo "PASS $checks Luciano CRM-to-Site lossless export plan fixtures; no DB/file/outbox/API/ACK\n";
