<?php

require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
use App\Support\LucianoWireEnvelope as Wire;

function wireAssert($v) { if (!$v) throw new RuntimeException('Wire assertion failed'); }
function wireReject($fn) {
    try { $fn(); } catch (RuntimeException $e) { return; }
    throw new RuntimeException('Unsafe wire draft accepted');
}
function envelope($object='1096',$property='14',$sender='price') {
    $payload=['format'=>'price-tonia-snapshot-v1','source'=>'price_tonia_ru','crm_object_id'=>$object,
        'external_property_key'=>$property,'snapshot'=>['schema_version'=>'2','row_count'=>'1',
            'snapshot_key'=>'reload-v1-luciano-initial-'.$object.'-00000000-0000-4000-8000-000000000001'],
        'data'=>['catalog'=>[['name'=>'Номер / Казань','description'=>'Фото и оснащение']],
            'observations'=>[['placements_json'=>'[{"code":"3","quantity":1,"guests":3}]',
                'request_context_json'=>'{"quote_observed_at":null,"checked_at_basis":"collection_start_lower_bound_not_individual_observation"}']],
            'daily'=>[['amount'=>'17650.00','adults'=>'2','amount_before_discount'=>null]],
            'offers'=>[['total_amount'=>'47650.00','nights'=>'2',
                'nightly_breakdown_json'=>'[{"date":"2026-12-31","amount":"22650.00"},{"date":"2027-01-01","amount":"25000.00"}]']],
            'restrictions'=>[['min_nights'=>'20','source_data'=>'Минимальное проживание 20 ночей']]]];
    return ['sender'=>$sender,'payload'=>$payload,'mappings'=>[],
        'payload_sha256'=>hash('sha256',Wire::encode($payload)),'mappings_sha256'=>hash('sha256',Wire::encode([]))];
}
function rehash($e) { $e['payload_sha256']=hash('sha256',Wire::encode($e['payload'])); return $e; }

$e=envelope();$wire=Wire::pack($e);
wireAssert(Wire::encode(Wire::unpack($wire,'crm'))===Wire::encode($e));
wireAssert(Wire::encode(Wire::pack($e))===Wire::encode($wire));
echo "PASS deterministic lossless roundtrip preserves package placement, decimals, exact cross-year breakdown and source provenance\n";
foreach ([['1658','15','price','crm'],['1096','14','crm','site'],['1658','15','crm','site']] as $scope) {
    $other=envelope($scope[0],$scope[1],$scope[2]);
    wireAssert(Wire::encode(Wire::unpack(Wire::pack($other),$scope[3]))===Wire::encode($other));
}
echo "PASS both exact scope pairs and price-to-CRM/CRM-to-site role contracts\n";
foreach (['scope','header object','header key','sender','format','encoding','unknown header','payload hash',
    'mapping hash','decoded hash','compressed hash','decoded size','too large decoded','zero size','string size',
    'corrupt base64','noncanonical base64','corrupt gzip','wrong receiver','wrong property'] as $case) {
    $w=$wire;$receiver='crm';
    switch ($case) {
        case 'scope': $w['crm_object_id']='999999'; break;
        case 'header object': $w['crm_object_id']='1658';$w['external_property_key']='15';$w['snapshot_key']=str_replace('1096','1658',$w['snapshot_key']); break;
        case 'header key': $w['snapshot_key']=str_replace('000000000001','000000000002',$w['snapshot_key']); break;
        case 'sender': $w['sender']='crm'; break;
        case 'format': $w['format']='reload-v1'; break;
        case 'encoding': $w['encoding']='none'; break;
        case 'unknown header': $w['unreviewed']=true; break;
        case 'payload hash': $w['payload_sha256']=str_repeat('0',64); break;
        case 'mapping hash': $w['mappings_sha256']=str_repeat('0',64); break;
        case 'decoded hash': $w['decoded_sha256']=str_repeat('0',64); break;
        case 'compressed hash': $w['compressed_sha256']=str_repeat('0',64); break;
        case 'decoded size': $w['decoded_bytes']--; break;
        case 'too large decoded': $w['decoded_bytes']=Wire::MAX_DECODED_BYTES+1; break;
        case 'zero size': $w['decoded_bytes']=0; break;
        case 'string size': $w['decoded_bytes']=(string)$w['decoded_bytes']; break;
        case 'corrupt base64': $w['encoded_envelope']='!'; break;
        case 'noncanonical base64': $w['encoded_envelope'].="\n"; break;
        case 'corrupt gzip': $g=base64_decode($w['encoded_envelope']);$g[12]=chr(ord($g[12])^1);$w['encoded_envelope']=base64_encode($g);$w['compressed_sha256']=hash('sha256',$g); break;
        case 'wrong receiver': $receiver='price'; break;
        case 'wrong property': $w['external_property_key']='15'; break;
    }
    wireReject(function() use($w,$receiver){Wire::unpack($w,$receiver);});
    echo 'PASS reject '.$case."\n";
}
foreach (['foreign envelope','bad source','unsupported schema','missing section','zero daily','row count','nonstring scalar','stale payload digest'] as $case) {
    $bad=$e;
    switch ($case) {
        case 'foreign envelope': $bad['payload']['crm_object_id']='999999'; break;
        case 'bad source': $bad['payload']['source']='untrusted'; break;
        case 'unsupported schema': $bad['payload']['snapshot']['schema_version']='3'; break;
        case 'missing section': unset($bad['payload']['data']['offers']); break;
        case 'zero daily': $bad['payload']['data']['daily']=[]; break;
        case 'row count': $bad['payload']['snapshot']['row_count']='2'; break;
        case 'nonstring scalar': $bad['payload']['data']['daily'][0]['amount']=17650; break;
        case 'stale payload digest': $bad['payload']['data']['daily'][0]['amount']='1.00'; break;
    }
    if ($case!=='stale payload digest')$bad=rehash($bad);
    wireReject(function() use($bad){Wire::pack($bad);});echo 'PASS reject '.$case."\n";
}
// The declared decoded bound is checked before allocation and after inflation.
$large=$e;$large['payload']['data']['catalog'][0]['description']=str_repeat('Сохранить все данные ',10000);$large=rehash($large);
$w=Wire::pack($large);$w['decoded_bytes']=100;
wireReject(function() use($w){Wire::unpack($w,'crm');});
echo "PASS reject compressed expansion beyond declared bound\n";

// Independent Python decoder proves ordinary gzip interoperability; no network.
$f=tempnam(sys_get_temp_dir(),'luciano-wire-');
try {
    file_put_contents($f,Wire::encode($wire));
    $code='import sys,json,base64,gzip,hashlib;w=json.load(open(sys.argv[1]));b=gzip.decompress(base64.b64decode(w["encoded_envelope"],validate=True));assert len(b)==w["decoded_bytes"] and hashlib.sha256(b).hexdigest()==w["decoded_sha256"];e=json.loads(b);assert e["payload"]["data"]["offers"][0]["total_amount"]=="47650.00";assert e["payload_sha256"]==w["payload_sha256"];print("PASS independent Python gzip/UTF8/decimal/checksum decoding")';
    passthru('python3 -c '.escapeshellarg($code).' '.escapeshellarg($f),$exit);wireAssert($exit===0);
} finally { unlink($f); }
