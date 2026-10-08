<?php
require_once __DIR__.'/luciano_booking_quote.php';

/** Revalidate a Luciano submission against the active, source-backed CRM quote. No writes. */
function luciano_booking_verify($data, $db) {
    $q=LucianoBookingQuote::parse($data,(new DateTimeImmutable('today',new DateTimeZone('Europe/Moscow')))->format('Y-m-d'));
    $states=$db->getAll("SELECT st.*,sn.snapshot_key,sn.status AS snapshot_status,sn.checksum,sn.manifest_json FROM external_price_state st JOIN external_price_snapshot sn ON sn.id=st.active_snapshot_id AND sn.source=st.source AND sn.crm_object_id=st.crm_object_id JOIN external_price_source src ON src.source=st.source AND src.enabled=1 WHERE st.source='price_tonia_ru' AND st.crm_object_id=?i",$q['crm_object_id']);
    if(count($states)!==1)throw new RuntimeException('Luciano prices are not published');
    $st=$states[0];
    if((string)$st['external_property_key']!==(string)$q['property_id'] || (string)$st['upstream_property_key']!==(string)$q['provider_id']
        || (string)$st['import_enabled']!=='1' || (string)$st['publish_enabled']!=='1' || $st['billing_basis']!=='night'
        || $st['timezone']!=='Europe/Moscow' || $st['snapshot_status']!=='published' || $st['snapshot_key']!==$q['snapshot_key']
        || $st['stale_after_seconds']!==null)throw new RuntimeException('Luciano publication differs');
    $manifest=json_decode($st['manifest_json'],true);$header=$manifest['transport_header']??[];
    $origin=json_decode($header['origin_manifest_json']??'',true);
    if(($manifest['transport_payload_sha256']??null)!==$st['checksum'] || !preg_match('/^[a-f0-9]{64}$/D',$st['checksum'])
        || ($header['snapshot_key']??null)!==$q['snapshot_key'] || ($origin['builder']??null)!=='luciano-initial-payload-v1')throw new RuntimeException('Luciano snapshot provenance differs');
    $sid=(int)$st['active_snapshot_id'];
    $catalog=$db->getAll("SELECT c.* FROM external_price_catalog c JOIN room r ON r.id=c.crm_room_id AND r.id_obj=?i AND r.active=0 JOIN rate_plan t ON t.id=c.crm_rate_id AND t.object=?i AND t.status=1 WHERE c.snapshot_id=?i AND c.entity_type='rate' AND c.status='active' AND c.crm_room_id=?i AND c.crm_rate_id=?i",$q['crm_object_id'],$q['crm_object_id'],$sid,$q['crm_room_id'],$q['crm_rate_id']);
    if(count($catalog)!==1)throw new RuntimeException('Luciano catalogue is ambiguous');
    $c=$catalog[0];$cid=(int)$c['id'];
    if($q['nights']===1)$rows=$db->getAll('SELECT * FROM external_daily_price WHERE snapshot_id=?i AND catalog_id=?i AND stay_date=?s AND adults=?i',$sid,$cid,$q['arrival'],$q['adults']);
    else $rows=$db->getAll('SELECT * FROM external_stay_offer WHERE snapshot_id=?i AND catalog_id=?i AND arrival=?s AND departure=?s AND nights=?i AND adults=?i',$sid,$cid,$q['arrival'],$q['departure'],$q['nights'],$q['adults']);
    if(count($rows)!==1)throw new RuntimeException('Luciano source quote is absent or ambiguous');
    $row=$rows[0];
    $observations=$db->getAll('SELECT * FROM external_price_observation WHERE snapshot_id=?i AND id=?i AND catalog_id=?i',$sid,(int)$row['observation_id'],$cid);
    if(count($observations)!==1)throw new RuntimeException('Luciano observation is absent');
    $o=$observations[0];$amount=$q['nights']===1?$row['amount']:$row['total_amount'];
    foreach([$row,$o] as $r){
        if($r['currency']!=='RUB' || $r['availability_state']!=='observed_available'
            || $r['price_basis']!==($q['nights']===1?'room_per_night_for_occupancy':'stay_total')
            || $r['price_model']!==($q['nights']===1?'nightly':'stay_dependent')
            || (int)$r['adults']!==$q['adults'] || ($r['expires_at']!==null && $r['expires_at']<=gmdate('Y-m-d H:i:s')))throw new RuntimeException('Luciano quote is not eligible');
    }
    if($amount!==$q['quoted_total'] || $o['total_amount']!==$amount || $o['arrival']!==$q['arrival'] || $o['departure']!==$q['departure']
        || (int)$o['nights']!==$q['nights'] || (string)$o['children_count']!=='0' || $o['evidence']!=='explicit' || $o['parser_version']!=='luciano-package-v1'
        || $o['external_room_key']!==$c['external_room_key'] || $o['external_rate_key']!==$c['external_rate_key']
        || (int)$row['crm_room_id']!==$q['crm_room_id'] || (int)$row['crm_rate_id']!==$q['crm_rate_id'])throw new RuntimeException('Luciano package differs from source');
    $context=json_decode($o['request_context_json'],true);$source=json_decode($o['source_data'],true);
    if(!is_array($context) || ($context['provider_id']??null)!==$q['provider_id'] || ($context['children']??null)!==0
        || ($context['arrival']??null)!==$q['arrival'] || ($context['departure']??null)!==$q['departure'] || ($context['adults']??null)!==$q['adults']
        || !is_array($source) || !array_key_exists('minimum_nights',$source)
        || ($source['minimum_nights']!==null && (!is_int($source['minimum_nights']) || $source['minimum_nights']>$q['nights'])))throw new RuntimeException('Luciano source restrictions differ');
    $month=substr($q['arrival'],0,7);$stage=$origin['sources'][$month]??[];
    if(($context['source_month']??null)!==$month || ($source['source_month']??null)!==$month
        || !isset($stage['stage_sha256']) || ($context['stage_sha256']??null)!==$stage['stage_sha256'])throw new RuntimeException('Luciano collection identity differs');
    $breakdown=json_decode($o['nightly_breakdown_json'],true);$sum=0;
    if(!is_array($breakdown)||count($breakdown)!==$q['nights'])throw new RuntimeException('Incomplete source breakdown');
    foreach($breakdown as $i=>$day){
        $date=(new DateTimeImmutable($q['arrival']))->modify('+'.$i.' days')->format('Y-m-d');
        if(($day['date']??null)!==$date || !is_string($day['amount']??null) || !preg_match('/^(0|[1-9][0-9]*)\.([0-9]{2})$/D',$day['amount'],$m))throw new RuntimeException('Invalid source breakdown');
        $sum+=(int)$m[1]*100+(int)$m[2];
    }
    $parts=explode('.',$amount);
    if($sum!==(int)$parts[0]*100+(int)$parts[1])throw new RuntimeException('Source total differs from breakdown');
    $rules=$db->getAll('SELECT min_nights,closed FROM external_stay_restriction WHERE snapshot_id=?i AND catalog_id=?i AND date_from=?s AND adults_scope=?i',$sid,$cid,$q['arrival'],$q['adults']);
    if(count($rules)>1)throw new RuntimeException('Ambiguous Luciano restrictions');
    foreach($rules as $rule)if(($rule['closed']!==null && (int)$rule['closed']!==0) || (int)$rule['min_nights']>$q['nights'])throw new RuntimeException('Luciano minimum stay is not met');
    return $q['positions'];
}
