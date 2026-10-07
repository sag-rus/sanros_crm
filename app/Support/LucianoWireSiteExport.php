<?php
namespace App\Support;

/** Pure Site packet construction from fully verified CRM read-back and scoped mapping evidence. */
final class LucianoWireSiteExport
{
    public static function build($hotel, $sourceWireSha, array $states, array $snapshots,
        array $inboxes, array $payload, array $sectionFields, array $mappingRows): array
    {
        $readback=LucianoWireReadback::verify($hotel,$sourceWireSha,$states,$snapshots,$inboxes,$payload,$sectionFields);
        $property=$payload['external_property_key'];
        $wanted=[];$rooms=[];
        foreach ($payload['data']['catalog'] as $row) {
            $type=$row['entity_type'];$room=$row['external_room_key'];$rate=$row['external_rate_key'];
            if (!in_array($type,['room','rate'],true) || !is_string($room) || $room===''
                || ($type==='room' ? $rate!==null && $rate!=='' : !is_string($rate) || $rate==='')) {
                throw new \RuntimeException('Unsupported export catalogue identity');
            }
            $rooms[$room]=true;
            $wanted[self::key($type,$room,$type==='room'?'':$rate)]=true;
        }
        // Rate associations also require their room mapping even in a rate-only catalogue fixture.
        foreach ($rooms as $room=>$unused) $wanted[self::key('room',(string)$room,'')]=true;
        $maps=[];$idOwners=['room'=>[],'rate'=>[]];$rateIds=[];
        foreach ($mappingRows as $row) {
            if (!is_array($row)) throw new \RuntimeException('Malformed export mapping row');
            $fields=array_keys($row);sort($fields);
            $expected=['entity_type','external_property_key','external_room_key','external_rate_key','crm_id','mapping_status'];sort($expected);
            if ($fields!==$expected || $row['external_property_key']!==$property
                || !in_array($row['entity_type'],['room','rate'],true) || $row['mapping_status']!=='verified'
                || !is_string($row['external_room_key']) || $row['external_room_key']===''
                || !is_string($row['external_rate_key']) || !is_string($row['crm_id'])
                || !preg_match('/^[1-9][0-9]{0,19}$/D',$row['crm_id'])) {
                throw new \RuntimeException('Unverified or foreign export mapping');
            }
            $type=$row['entity_type'];$room=$row['external_room_key'];$rate=$row['external_rate_key'];$id=$row['crm_id'];
            if (($type==='room' && $rate!=='') || ($type==='rate' && $rate==='')) throw new \RuntimeException('Invalid mapping identity');
            $key=self::key($type,$room,$rate);
            if (!isset($wanted[$key]) || isset($maps[$key])) throw new \RuntimeException('Extra or duplicate export mapping');
            // Same hotel-wide rate code may occur on several rooms. Distinct source codes never collapse by name.
            $owner=$type==='room'?$room:$rate;
            if (isset($idOwners[$type][$id]) && $idOwners[$type][$id]!==$owner) throw new \RuntimeException('Mapping ID collapses distinct source identities');
            if ($type==='rate' && isset($rateIds[$rate]) && $rateIds[$rate]!==$id) throw new \RuntimeException('Hotel-wide rate has conflicting CRM IDs');
            $idOwners[$type][$id]=$owner;if ($type==='rate') $rateIds[$rate]=$id;
            unset($row['external_property_key']);$maps[$key]=$row;
        }
        if (count($maps)!==count($wanted)) throw new \RuntimeException('Incomplete export mapping coverage');
        // Stable mapping order keeps checksums independent of database row order.
        ksort($maps,SORT_STRING);$mappings=array_values($maps);
        $envelope=['sender'=>'crm','payload'=>$payload,'payload_sha256'=>$readback['receipt']['payload_sha256'],
            'mappings'=>$mappings,'mappings_sha256'=>hash('sha256',LucianoWireEnvelope::encode($mappings))];
        $wire=LucianoWireEnvelope::pack($envelope);
        $site=LucianoWireImportPlan::validate($wire,'site',$sectionFields);
        if ($site['section_counts']!==$readback['section_counts']
            || LucianoWireEnvelope::encode($site['envelope']['payload'])!==LucianoWireEnvelope::encode($payload)) {
            throw new \RuntimeException('Site export changed original payload');
        }
        return ['export_plan_only'=>true,'sender'=>'crm','receiver'=>'site','wire'=>$wire,
            'wire_sha256'=>hash('sha256',LucianoWireEnvelope::encode($wire)),
            'original_payload_sha256'=>$wire['payload_sha256'],'source_crm_receipt'=>$readback['receipt'],
            'section_counts'=>$site['section_counts'],'mapping_count'=>count($mappings),
            'mapping_identities_checked'=>true,'legacy_catalogue_rows_verified'=>false,'site_schema_verified'=>false,
            'photos_verified'=>false,'database_written'=>false,'file_written'=>false,'outbox_created'=>false,
            'ack_sent'=>false,'consumer_integrated'=>false,'delivery_ready'=>false];
    }

    private static function key($type,$room,$rate): string
    {
        return LucianoWireEnvelope::encode([$type,$room,$rate]);
    }
}
