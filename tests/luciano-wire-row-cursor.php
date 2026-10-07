<?php
require __DIR__.'/luciano-wire-import-plan.php';
require __DIR__.'/../app/Support/LucianoWireRowCursor.php';
use App\Support\LucianoWireRowCursor as Cursor;
use App\Support\LucianoWireEnvelope as Wire;

$checks=0;
foreach ([['kazan','1096','14'],['sochi','1658','15']] as $scope) {
    $e=planEnvelope($scope[1],$scope[2]);$d=&$e['payload']['data'];
    $d['catalog'][0]['description']="' \\ ` ? Русский\nтекст";
    $d['observations'][0]['placements_json']='[{"quantity":1,"guests":3}]';
    $d['observations'][1]['placements_json']='[{"quantity":1,"guests":3}]';
    $d['offers'][0]['provenance_json']='{"quote_observed_at":null,"checked_at_basis":"collection_start_lower_bound"}';
    $d['restrictions'][0]['evidence_json']='{"minimum_nights":20}';
    // A second source rate/room must retain separate catalogue and observation references.
    $key=hash('sha256',json_encode(['rate','59768','707839']));
    foreach (['catalog','observations','daily','restrictions','offers'] as $section) {
        $extra=$d[$section];
        foreach ($extra as &$row) {
            $row['catalog_key']=$key;
            if (isset($row['external_room_key'])) $row['external_room_key']='59768';
            if (isset($row['external_rate_key'])) $row['external_rate_key']='707839';
            if (isset($row['observation_key'])) $row['observation_key']='second-'.$row['observation_key'];
            if (isset($row['offer_key'])) $row['offer_key']='second-'.$row['offer_key'];
        }
        unset($row);$d[$section]=array_merge($d[$section],$extra);
    }
    $e['payload']['snapshot']['row_count']='2';
    unset($d);$e=rehash($e);$wire=Wire::pack($e);$fields=planFields($e);
    $cursor=new Cursor($scope[0],$wire,$fields,'18446744073709551615');
    $restored=[];$catalog=[];$obs=[];$order=[];$id=100;
    while (($row=$cursor->next())!==null) {
        wireAssert($row===$cursor->next());$checks++;
        $s=$row['section'];$r=$row['values'];$order[]=$s;
        wireAssert(substr_count($row['sql'],'?')===count($row['parameters']) && $row['parameters']===array_values($r));$checks++;
        wireAssert(strpos($row['sql'],'Русский')===false && $r['snapshot_id']==='18446744073709551615');$checks++;
        unset($r['snapshot_id']);$generated=(string)++$id;
        if ($s==='catalog') {
            $key=$e['payload']['data'][$s][$row['row_index']]['catalog_key'];$catalog[$generated]=$key;$r['catalog_key']=$key;
        } else {
            $r['catalog_key']=$catalog[$r['catalog_id']];unset($r['catalog_id']);
            if ($s==='observations') $obs[$generated]=$r['observation_key'];
            else { $r['observation_key']=$r['observation_id']===null?null:$obs[$r['observation_id']];unset($r['observation_id']); }
        }
        $restored[$s][]=$r;$cursor->acceptId($generated);
    }
    wireAssert(Wire::encode($restored)===Wire::encode($e['payload']['data']));$checks++;
    $expectedOrder=[];
    foreach (['catalog','observations','daily','restrictions','offers'] as $section) {
        $expectedOrder=array_merge($expectedOrder,array_fill(0,count($e['payload']['data'][$section]),$section));
    }
    wireAssert($order===$expectedOrder);$checks++;
    $done=$cursor->complete();wireAssert(Wire::encode($done['section_counts'])===Wire::encode(array_map('count',$e['payload']['data'])));$checks++;
    wireAssert(!$done['database_written'] && !$done['native_sql_executed'] && !$done['consumer_integrated'] && !$done['delivery_ready']);$checks++;
    wireAssert($cursor->next()===null && $cursor->complete()===$done);$checks++;
    wireReject(function()use($cursor){$cursor->acceptId('999');});$checks++;
}
$e=planEnvelope();$wire=Wire::pack($e);$fields=planFields($e);
foreach ([0,1,1.0,true,null,'0','01','-1','1.0','1e3',' 1','1 ','18446744073709551616',str_repeat('9',21)] as $id) {
    wireReject(function()use($wire,$fields,$id){new Cursor('kazan',$wire,$fields,$id);});$checks++;
    $c=new Cursor('kazan',$wire,$fields,'9');$c->next();
    wireReject(function()use($c,$id){$c->acceptId($id);});$checks++;
}
foreach ([null,14,'foreign','sochi'] as $hotel) {
    wireReject(function()use($hotel,$wire,$fields){new Cursor($hotel,$wire,$fields,'9');});$checks++;
}
$site=planEnvelope('1096','14','crm');
wireReject(function()use($site){new Cursor('kazan',Wire::pack($site),planFields($site),'9');});$checks++;
foreach (['id','snapshot_id','catalog_id','observation_id','crm_room_id','crm_rate_id'] as $reserved) {
    $bad=$e;$bad['payload']['data']['offers'][0][$reserved]='9';$bad=rehash($bad);
    wireReject(function()use($bad){new Cursor('kazan',Wire::pack($bad),planFields($bad),'9');});$checks++;
}
$bad=$e;$bad['payload']['data']['daily'][0]['observation_key']=null;$bad=rehash($bad);
wireReject(function()use($bad){new Cursor('kazan',Wire::pack($bad),planFields($bad),'9');});$checks++;
$bad=$e;$bad['payload']['data']['catalog'][0]['name`; DROP TABLE x; --']='bad';$bad=rehash($bad);
wireReject(function()use($bad){new Cursor('kazan',Wire::pack($bad),planFields($bad),'9');});$checks++;
$c=new Cursor('kazan',$wire,$fields,'9');
wireReject(function()use($c){$c->acceptId('1');});$checks++;
wireReject(function()use($c){$c->complete();});$checks++;
$c->acceptId('1');$c->next();$c->acceptId('1');$c->next();
wireReject(function()use($c){$c->acceptId('1');});$checks++;
wireAssert($c->next()['values']['observation_key']==='two');$checks++;
$c->acceptId('2');$c->next();$c->acceptId('1');$c->next();$c->acceptId('1');$c->next();$c->acceptId('1');
wireAssert($c->complete()['all_descriptors_consumed']);$checks++;
$empty=$e;$empty['payload']['data']['restrictions']=[];$empty=rehash($empty);
$c=new Cursor('kazan',Wire::pack($empty),$fields,'9');$n=0;
while ($c->next()!==null) {$c->acceptId((string)++$n);}
wireAssert($c->complete()['section_counts']['restrictions']===0);$checks++;
echo "PASS $checks Luciano CRM insertion cursor isolation/lossless FK/SQL fixtures; no SQL executed\n";
