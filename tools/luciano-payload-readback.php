<?php
// Same canonical transport data as tPayload, encoding each row once before sorting.
// This avoids repeated JSON serialization inside the O(n log n) comparator on large snapshots.
function lucianoPayload($pdo,array $snapshot){
    $data=[];$catalog=[];$observations=[];
    foreach(tSections()as $section=>$table){
        $encoded=[];
        foreach(rows($pdo,'SELECT * FROM '.$table.' WHERE snapshot_id=? ORDER BY id',[$snapshot['id']])as $r){
            if($section==='catalog'){$catalog[$r['id']]=tCatalogKey($r);$r['catalog_key']=$catalog[$r['id']];}
            if($section==='observations')$observations[$r['id']]=$r['observation_key'];
            if(array_key_exists('catalog_id',$r)){$r['catalog_key']=$r['catalog_id']===null?null:$catalog[$r['catalog_id']];unset($r['catalog_id']);}
            if(array_key_exists('observation_id',$r)){$r['observation_key']=$r['observation_id']===null?null:$observations[$r['observation_id']];unset($r['observation_id']);}
            unset($r['id'],$r['snapshot_id'],$r['crm_room_id'],$r['crm_rate_id']);$encoded[]=tEncode(tScalars($r));
        }
        sort($encoded,SORT_STRING);$data[$section]=array_map(function($v){return json_decode($v,true);},$encoded);
    }
    $manifest=json_decode($snapshot['manifest_json'],true);if(!isset($manifest['transport_header']))throw new RuntimeException('Original Luciano transport header absent');
    $state=tState($pdo,$snapshot['crm_object_id']);
    return ['format'=>'price-tonia-snapshot-v1','source'=>'price_tonia_ru','crm_object_id'=>(string)$snapshot['crm_object_id'],'external_property_key'=>(string)$state['external_property_key'],'snapshot'=>$manifest['transport_header'],'data'=>$data];
}
