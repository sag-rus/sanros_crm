<?php
// Explicitly approved legacy PRICE -> live transitions. Historical snapshots/mappings are retained.
function pllScope($id) {
    $scopes=[3=>['7078',2],14=>['11640',7],22=>['1192',4],30=>['5704',1],46=>['6248',5],57=>['18881',3],313=>['823',6],667=>['11953',8],1096=>['3026',14],1658=>['434',15]];
    if(!isset($scopes[$id]))throw new RuntimeException('Legacy migration is not allowlisted');
    return $scopes[$id];
}
function pllRows($p,$sql,$args=[]) {$s=$p->prepare($sql);$s->execute($args);return $s->fetchAll(PDO::FETCH_ASSOC);}
function pllIdentity(array $s,$id) {
    $scope=pllScope($id);
    if(($s['source']??'')!=='price_tonia_ru'||(int)($s['crm_object_id']??0)!==$id||(string)($s['external_property_key']??'')!==(string)$scope[1]||(!empty($s['upstream_property_key'])&&(string)$s['upstream_property_key']!==$scope[0]))throw new RuntimeException('Legacy source identity differs');
}
function pllGuard($p,$id,$role) {
    $states=pllRows($p,'SELECT * FROM external_price_state WHERE crm_object_id=?',[$id]);
    if(!$states)return false;
    if(count($states)!==1)throw new RuntimeException('Ambiguous legacy source');
    pllIdentity($states[0],$id);
    try {$markers=pllRows($p,'SELECT * FROM price_live_legacy_migration WHERE object_id=?',[$id]);}
    catch(PDOException $e){if($e->getCode()==='42S02')throw new RuntimeException('Legacy migration must be prepared explicitly');throw $e;}
    if(count($markers)!==1||$markers[0]['provider_id']!==pllScope($id)[0]||!in_array($markers[0]['phase'],['prepared','completed'],true)||(int)$states[0]['import_enabled']!==0||(($role==='crm'||$markers[0]['phase']==='completed')&&(int)$states[0]['publish_enabled']!==0))throw new RuntimeException('Legacy import has not been safely retired');
    return true;
}
function pllMapping(array $mappings,$kind,$code) {
    $key=$kind==='rooms'?'external_room_key':'external_rate_key';$type=$kind==='rooms'?'room':'rate';$ids=[];
    foreach($mappings as $m)if($m['entity_type']===$type&&(string)$m[$key]===(string)$code){
        if($m['mapping_status']!=='verified'||(int)$m['crm_id']<1)throw new RuntimeException('Unverified legacy identity');
        $ids[(int)$m['crm_id']]=true;
    }
    if(count($ids)>1)throw new RuntimeException('Ambiguous legacy identity');
    return $ids?(int)array_keys($ids)[0]:null;
}
function pllComplete($p,$id) {
    $p->prepare("UPDATE external_price_state SET import_enabled=0,publish_enabled=0,updated_at=UTC_TIMESTAMP() WHERE source='price_tonia_ru' AND crm_object_id=?")->execute([$id]);
    $p->prepare("UPDATE price_live_legacy_migration SET phase='completed',updated_at=UTC_TIMESTAMP() WHERE object_id=?")->execute([$id]);
}
