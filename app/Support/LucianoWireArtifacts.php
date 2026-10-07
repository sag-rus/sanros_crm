<?php

namespace App\Support;

/** Pure CLI-side receipt/storage plan. It neither imports nor writes files or database rows. */
final class LucianoWireArtifacts
{
    public static function inspect(array $wire, string $receiver): array
    {
        $envelope=LucianoWireEnvelope::unpack($wire,$receiver);
        $payload=$envelope['payload'];
        $json=LucianoWireEnvelope::encode($wire);
        $key=$wire['snapshot_key'];
        $object=$wire['crm_object_id'];
        $prefix='/var/tmp/price-tonia-luciano-wire-v1-'.$object.'-'.$key;
        $counts=[];
        foreach ($payload['data'] as $section=>$rows) $counts[$section]=count($rows);
        return [
            'format'=>LucianoWireEnvelope::FORMAT,
            'receiver'=>$receiver,
            'source'=>$wire['source'],
            'sender'=>$wire['sender'],
            'crm_object_id'=>$object,
            'external_property_key'=>$wire['external_property_key'],
            'snapshot_key'=>$key,
            'original_payload_sha256'=>$wire['payload_sha256'],
            'original_mappings_sha256'=>$wire['mappings_sha256'],
            'original_envelope_sha256'=>$wire['decoded_sha256'],
            'original_envelope_bytes'=>$wire['decoded_bytes'],
            'wire_sha256'=>hash('sha256',$json),
            'wire_bytes'=>strlen($json),
            'section_counts'=>$counts,
            'daily_row_count'=>(int)$payload['snapshot']['row_count'],
            'total_section_rows'=>array_sum($counts),
            // Keep the verified full envelope compressed even in the inbox.
            // A future consumer must use its own receipt discriminator/revalidation.
            'proposed_inbox_chunk_key'=>'luciano-wire-v1',
            'proposed_inbox_payload_json'=>$json,
            'packet_path'=>$prefix.'-'.$receiver.'-packet.json',
            'export_path'=>$prefix.'-'.$receiver.'-export.json',
            'catalogue_reserve_path'=>$prefix.'-'.$receiver.'-catalogue-before.json',
            'requires_exclusive_regular_files'=>true,
            'requires_file_mode'=>'0600',
            'original_rows_and_observation_references_retained'=>true,
            'file_written'=>false,
            'database_imported'=>false,
            'consumer_integrated'=>false,
            'delivery_ready'=>false,
        ];
    }
}
