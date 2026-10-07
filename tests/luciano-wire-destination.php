<?php
require __DIR__.'/../app/Support/LucianoWireDestination.php';
use App\Support\LucianoWireDestination as Destination;

function destinationReject($callback) {
    try { $callback(); } catch (RuntimeException $error) { return; }
    throw new RuntimeException('Unsafe initial destination was accepted');
}
$base=['source'=>'price_tonia_ru','crm_object_id'=>'1096','external_property_key'=>'14',
    'import_enabled'=>'0','publish_enabled'=>'0','active_snapshot_id'=>null,'stale_after_seconds'=>null,'timezone'=>'Europe/Moscow'];
$checks=0;
foreach ([['kazan',$base],['sochi',array_replace($base,['crm_object_id'=>1658,'external_property_key'=>15,'import_enabled'=>0,'publish_enabled'=>0])]] as $case) {
    $r=Destination::initial($case[0],[$case[1]],0);
    if (!$r['initial_destination_verified'] || !$r['dormant'] || $r['database_written'] || $r['activation_allowed'] || $r['delivery_ready']) throw new RuntimeException('Incorrect preflight result');
    $checks++;
}
foreach ([[],[$base,$base],['unexpected'=>$base]] as $states) {
    destinationReject(function()use($states){Destination::initial('kazan',$states,0);});$checks++;
}
foreach ([1,-1,'0',0.0,null] as $count) {
    destinationReject(function()use($base,$count){Destination::initial('kazan',[$base],$count);});$checks++;
}
foreach (['foreign','sochi',null] as $hotel) {
    destinationReject(function()use($base,$hotel){Destination::initial($hotel,[$base],0);});$checks++;
}
foreach ([['source'=>'other'],['crm_object_id'=>'1658'],['external_property_key'=>'15'],['crm_object_id'=>'01096'],
    ['external_property_key'=>'014'],['crm_object_id'=>1096.0],['external_property_key'=>14.0],
    ['import_enabled'=>1],['publish_enabled'=>'1'],['import_enabled'=>false],['publish_enabled'=>0.0],
    ['active_snapshot_id'=>0],['active_snapshot_id'=>'1'],['stale_after_seconds'=>0],['stale_after_seconds'=>'900'],
    ['timezone'=>'UTC'],['timezone'=>null]] as $change) {
    $state=array_replace($base,$change);
    destinationReject(function()use($state){Destination::initial('kazan',[$state],0);});$checks++;
}
foreach (array_keys($base) as $field) {
    $state=$base;unset($state[$field]);
    destinationReject(function()use($state){Destination::initial('kazan',[$state],0);});$checks++;
}
echo "PASS $checks Luciano initial destination isolation/dormancy/freshness fixtures; no database writes\n";
