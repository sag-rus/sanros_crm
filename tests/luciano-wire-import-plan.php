<?php
require __DIR__.'/luciano-wire-envelope.php';
require __DIR__.'/../app/Support/LucianoWireImportPlan.php';
use App\Support\LucianoWireImportPlan as Plan;
use App\Support\LucianoWireEnvelope as Wire;

function planEnvelope($object='1096',$property='14',$sender='price') {
    $e=envelope($object,$property,$sender);
    $key=hash('sha256',json_encode(['rate','59767','707838']));
    $catalog=['catalog_key'=>$key,'entity_type'=>'rate','external_room_key'=>'59767','external_rate_key'=>'707838'];
    $base=['catalog_key'=>$key,'adults'=>'3','currency'=>'RUB','availability_state'=>'observed_available',
        'checked_at'=>'2026-10-05 18:54:34','expires_at'=>null,'price_basis'=>'room_per_night_for_occupancy','price_model'=>'nightly'];
    $one=$base+['observation_key'=>'one','external_room_key'=>'59767','external_rate_key'=>'707838','children_count'=>'0',
        'arrival'=>'2026-12-31','departure'=>'2027-01-01','nights'=>'1','total_amount'=>'22650.00',
        'nightly_breakdown_json'=>'[{"amount":"22650.00","date":"2026-12-31"}]'];
    $two=$one; $two['observation_key']='two';$two['departure']='2027-01-02';$two['nights']='2';$two['total_amount']='47650.00';
    $two['price_basis']='stay_total';$two['price_model']='stay_dependent';
    $two['nightly_breakdown_json']='[{"amount":"22650.00","date":"2026-12-31"},{"amount":"25000.00","date":"2027-01-01"}]';
    $daily=$base+['observation_key'=>'one','stay_date'=>'2026-12-31','amount'=>'22650.00'];
    $offer=[];foreach (['catalog_key','adults','currency','availability_state','checked_at','expires_at','price_basis','price_model','observation_key','arrival','departure','nights','total_amount','nightly_breakdown_json'] as $f) $offer[$f]=$two[$f];
    $offer['offer_key']='offer-two';
    $rule=['catalog_key'=>$key,'observation_key'=>null,'min_nights'=>'20'];
    $e['payload']['data']=['catalog'=>[$catalog],'observations'=>[$one,$two],'daily'=>[$daily],'offers'=>[$offer],'restrictions'=>[$rule]];
    $e['payload']['snapshot']['coverage_from']='2026-10-05';$e['payload']['snapshot']['coverage_to_exclusive']='2027-01-01';
    return rehash($e);
}
function planFields($e) { $s=[];foreach ($e['payload']['data'] as $k=>$rows) $s[$k]=array_keys($rows[0]);return $s; }
$checks=0;
foreach ([['1096','14','price','crm'],['1658','15','price','crm'],['1096','14','crm','site'],['1658','15','crm','site']] as $scope) {
    $e=planEnvelope($scope[0],$scope[1],$scope[2]);$r=Plan::validate(Wire::pack($e),$scope[3],planFields($e));
    wireAssert(Wire::encode($r['envelope'])===Wire::encode($e));
    wireAssert($r['schema_and_references_validated'] && !$r['database_written'] && !$r['consumer_integrated'] && !$r['delivery_ready']);
    $checks+=2;
}
$e=planEnvelope();$fields=planFields($e);
foreach (['missing schema','missing field','extra field','duplicate field','bad catalogue hash','duplicate catalogue','missing rate',
    'room as rate','duplicate observation','wrong room','wrong rate','unknown observation','null daily observation','null offer observation',
    'wrong quote catalogue','wrong adult','currency mismatch','timestamp mismatch','expiry mismatch','nightly from two nights',
    'nightly total mismatch','date outside coverage','invalid date','duplicate daily','exact total mismatch','duplicate offer',
    'duplicate stay identity','exact nightly mismatch','incomplete breakdown','wrong breakdown date','overprecision','negative money',
    'child occupancy','wrong departure','eight nights','four adults','nightly basis','exact basis','wrong source nightly sum','invalid coverage'] as $case) {
    $bad=$e;$schema=$fields;$d=&$bad['payload']['data'];
    switch ($case) {
        case 'missing schema': unset($schema['offers']);break;
        case 'missing field': array_pop($schema['daily']);break;
        case 'extra field': $schema['daily'][]='unexpected';break;
        case 'duplicate field': $schema['daily'][]='amount';break;
        case 'bad catalogue hash': $d['catalog'][0]['catalog_key']='bad';break;
        case 'duplicate catalogue': $d['catalog'][]=$d['catalog'][0];break;
        case 'missing rate': $d['observations'][0]['catalog_key']='missing';break;
        case 'room as rate': $d['catalog'][0]['entity_type']='room';$k=hash('sha256',json_encode(['room','59767','707838']));$d['catalog'][0]['catalog_key']=$k;foreach ($d as $s=>&$rs) if ($s!=='catalog') foreach($rs as &$r) $r['catalog_key']=$k;unset($r,$rs);break;
        case 'duplicate observation': $d['observations'][]=$d['observations'][0];break;
        case 'wrong room': $d['observations'][0]['external_room_key']='9';break;
        case 'wrong rate': $d['observations'][0]['external_rate_key']='9';break;
        case 'unknown observation': $d['daily'][0]['observation_key']='missing';break;
        case 'null daily observation': $d['daily'][0]['observation_key']=null;break;
        case 'null offer observation': $d['offers'][0]['observation_key']=null;break;
        case 'wrong quote catalogue': $d['offers'][0]['catalog_key']='missing';break;
        case 'wrong adult': $d['daily'][0]['adults']='2';break;
        case 'currency mismatch': $d['offers'][0]['currency']='USD';break;
        case 'timestamp mismatch': $d['daily'][0]['checked_at']='2026-10-06 21:29:46';break;
        case 'expiry mismatch': $d['offers'][0]['expires_at']='2027-01-01 00:00:00';break;
        case 'nightly from two nights': $d['daily'][0]['observation_key']='two';$d['daily'][0]['price_basis']='stay_total';$d['daily'][0]['price_model']='stay_dependent';break;
        case 'nightly total mismatch': $d['daily'][0]['amount']='67950.00';break;
        case 'date outside coverage': $bad['payload']['snapshot']['coverage_to_exclusive']='2026-12-31';break;
        case 'invalid date': $d['observations'][0]['arrival']='2026-02-30';break;
        case 'duplicate daily': $d['daily'][]=$d['daily'][0];$bad['payload']['snapshot']['row_count']='2';break;
        case 'exact total mismatch': $d['offers'][0]['total_amount']='95300.00';break;
        case 'duplicate offer': $d['offers'][]=$d['offers'][0];break;
        case 'duplicate stay identity': $d['offers'][]=$d['offers'][0];$d['offers'][1]['offer_key']='new-key';break;
        case 'exact nightly mismatch': $d['offers'][0]['nightly_breakdown_json']='[{"amount":"23825.00","date":"2026-12-31"},{"amount":"23825.00","date":"2027-01-01"}]';break;
        case 'incomplete breakdown': $d['observations'][1]['nightly_breakdown_json']='[]';break;
        case 'wrong breakdown date': $d['observations'][1]['nightly_breakdown_json']=str_replace('2027-01-01','2027-01-02',$d['observations'][1]['nightly_breakdown_json']);break;
        case 'overprecision': $d['observations'][0]['total_amount']='22650.001';break;
        case 'negative money': $d['observations'][0]['total_amount']='-22650.00';break;
        case 'child occupancy': $d['observations'][0]['children_count']='1';break;
        case 'wrong departure': $d['observations'][0]['departure']='2027-01-02';break;
        case 'eight nights': $d['observations'][1]['nights']='8';break;
        case 'four adults': $d['observations'][0]['adults']='4';break;
        case 'nightly basis': $d['observations'][0]['price_basis']='stay_total';break;
        case 'exact basis': $d['observations'][1]['price_model']='nightly';break;
        case 'wrong source nightly sum': $d['observations'][1]['total_amount']='47650.01';break;
        case 'invalid coverage': $bad['payload']['snapshot']['coverage_from']='2027-01-01';break;
    }
    unset($d);$bad=rehash($bad);
    wireReject(function() use($bad,$schema){Plan::validate(Wire::pack($bad),'crm',$schema);});$checks++;
}
echo "PASS $checks Luciano full import preflight fixtures; no INSERT, file, queue or booking\n";
