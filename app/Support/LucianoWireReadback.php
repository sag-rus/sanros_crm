<?php
namespace App\Support;

/** Pure comparison of supplied read-back evidence. It cannot import, activate or send an ACK. */
final class LucianoWireReadback
{
    public static function verify($hotel, $expectedWireSha, array $states, array $snapshots,
        array $inboxes, array $reconstructedPayload, array $sectionFields): array
    {
        return self::verifyPhase($hotel,$expectedWireSha,$states,$snapshots,$inboxes,$reconstructedPayload,$sectionFields,false);
    }

    /** A ready import is dormant and produces no receipt that could be sent to the source. */
    public static function verifyReady($hotel, $expectedWireSha, array $states, array $snapshots,
        array $inboxes, array $reconstructedPayload, array $sectionFields): array
    {
        return self::verifyPhase($hotel,$expectedWireSha,$states,$snapshots,$inboxes,$reconstructedPayload,$sectionFields,true);
    }

    private static function verifyPhase($hotel, $expectedWireSha, array $states, array $snapshots,
        array $inboxes, array $reconstructedPayload, array $sectionFields, bool $ready): array
    {
        $scopes=['kazan'=>['1096','14'],'sochi'=>['1658','15']];
        if (!is_string($hotel) || !isset($scopes[$hotel]) || !is_string($expectedWireSha)
            || !preg_match('/^[a-f0-9]{64}$/D',$expectedWireSha)
            || count($states)!==1 || !isset($states[0]) || !is_array($states[0])
            || count($snapshots)!==1 || !isset($snapshots[0]) || !is_array($snapshots[0])
            || count($inboxes)!==1 || !isset($inboxes[0]) || !is_array($inboxes[0])) {
            throw new \RuntimeException('Ambiguous or missing Luciano read-back evidence');
        }
        $scope=$scopes[$hotel];$state=$states[0];$snapshot=$snapshots[0];$inbox=$inboxes[0];
        self::fields($state,['source','crm_object_id','external_property_key','import_enabled','publish_enabled',
            'active_snapshot_id','stale_after_seconds','timezone']);
        self::fields($snapshot,['id','source','crm_object_id','snapshot_key','schema_version','status','checksum',
            'row_count','manifest_json','coverage_from','coverage_to_exclusive','generated_at','checked_from','checked_to',
            'imported_at','published_at','error_summary']);
        self::fields($inbox,['snapshot_id','sender','chunk_key','payload_sha256','row_count','status','payload_json',
            'received_at','applied_at','error_summary']);
        $sid=self::positiveId($snapshot['id']);
        $phaseMatches=$ready
            ? (in_array($state['import_enabled'],[0,'0'],true) && in_array($state['publish_enabled'],[0,'0'],true)
                && $state['active_snapshot_id']===null && $snapshot['status']==='ready' && $snapshot['published_at']===null)
            : (in_array($state['import_enabled'],[1,'1'],true) && in_array($state['publish_enabled'],[1,'1'],true)
                && self::identity($state['active_snapshot_id'],$sid) && $snapshot['status']==='published');
        if ($ready) {
            self::fields($state,['upstream_property_key','local_property_id','billing_basis','last_attempt_at','last_success_at','last_error','created_at','updated_at']);
            self::fields($snapshot,['upstream_snapshot_key','created_at']);
            $provider=$hotel==='kazan'?'3026':'434';
            if (!self::identity($state['upstream_property_key'],$provider) || $state['local_property_id']!==null
                || $state['billing_basis']!=='night' || $state['last_attempt_at']!==null || $state['last_success_at']!==null
                || $state['last_error']!==null || $snapshot['upstream_snapshot_key']!==$snapshot['snapshot_key']) {
                throw new \RuntimeException('Ready import destination identity or dormant metadata differs');
            }
            foreach (['created_at','updated_at'] as $field) self::utc($state[$field]);
            self::utc($snapshot['created_at']);
            if ($state['updated_at']<$state['created_at']) throw new \RuntimeException('Invalid dormant state interval');
        }
        if ($state['source']!=='price_tonia_ru' || !self::identity($state['crm_object_id'],$scope[0])
            || !self::identity($state['external_property_key'],$scope[1])
            || !$phaseMatches || $state['stale_after_seconds']!==null
            || $state['timezone']!=='Europe/Moscow' || $snapshot['source']!=='price_tonia_ru'
            || !self::identity($snapshot['crm_object_id'],$scope[0]) || !self::identity($snapshot['schema_version'],'2')
            || $snapshot['error_summary']!==null
            || !self::identity($inbox['snapshot_id'],$sid) || $inbox['sender']!=='price'
            || $inbox['chunk_key']!=='luciano-wire-v1' || $inbox['status']!=='applied' || $inbox['error_summary']!==null) {
            throw new \RuntimeException($ready?'Luciano destination is not a dormant ready import':'Luciano destination is not the verified applied active snapshot');
        }
        self::utc($snapshot['imported_at']);
        if (!$ready) self::utc($snapshot['published_at']);
        self::utc($inbox['received_at']);self::utc($inbox['applied_at']);
        if ($inbox['applied_at']<$inbox['received_at'] || (!$ready && $snapshot['published_at']<$snapshot['imported_at'])
            || ($ready && ($snapshot['imported_at']!==$inbox['applied_at'] || $snapshot['created_at']>$snapshot['imported_at']))) {
            throw new \RuntimeException('Invalid Luciano apply/publication interval');
        }
        $json=$inbox['payload_json'];
        if (!is_string($json) || strlen($json)<1 || strlen($json)>LucianoWireEnvelope::MAX_WIRE_BYTES
            || !hash_equals($expectedWireSha,hash('sha256',$json))) throw new \RuntimeException('Stored wire checksum differs');
        $wire=json_decode($json,true);
        if (json_last_error()!==JSON_ERROR_NONE || !is_array($wire) || LucianoWireEnvelope::encode($wire)!==$json) {
            throw new \RuntimeException('Stored wire is not canonical');
        }
        $validated=LucianoWireImportPlan::validate($wire,'crm',$sectionFields);
        $envelope=$validated['envelope'];$payload=$envelope['payload'];$header=$payload['snapshot'];
        if ($wire['crm_object_id']!==$scope[0] || $wire['external_property_key']!==$scope[1]
            || $snapshot['snapshot_key']!==$wire['snapshot_key'] || $snapshot['checksum']!==$wire['payload_sha256']
            || $inbox['payload_sha256']!==$wire['payload_sha256']
            || !self::identity($snapshot['row_count'],$header['row_count'])
            || !self::identity($inbox['row_count'],$header['row_count'])) throw new \RuntimeException('Stored snapshot header differs');
        foreach (['coverage_from','coverage_to_exclusive','generated_at','checked_from','checked_to'] as $field) {
            if (!array_key_exists($field,$header) || $snapshot[$field]!==$header[$field]) throw new \RuntimeException('Stored coverage/provenance differs');
        }
        if (!is_string($snapshot['manifest_json'])) throw new \RuntimeException('Missing original transport header');
        $manifest=json_decode($snapshot['manifest_json'],true);
        if (json_last_error()!==JSON_ERROR_NONE || !is_array($manifest) || !isset($manifest['transport_header'])
            || LucianoWireEnvelope::encode($manifest['transport_header'])!==LucianoWireEnvelope::encode($header)) {
            throw new \RuntimeException('Original transport header was not preserved');
        }
        if ($ready && (($manifest['transport_sender']??null)!=='price'
            || ($manifest['transport_payload_sha256']??null)!==$wire['payload_sha256']
            || ($manifest['transport_mappings_sha256']??null)!==$wire['mappings_sha256'])) {
            throw new \RuntimeException('Ready import manifest checksums differ');
        }
        // Compare the whole reconstructed payload, including every row, FK, scalar and provenance field.
        // Counts alone or a snapshot checksum column cannot prove a completed import.
        $reconstructedJson=LucianoWireEnvelope::encode($reconstructedPayload);
        if (!hash_equals($wire['payload_sha256'],hash('sha256',$reconstructedJson))
            || $reconstructedJson!==LucianoWireEnvelope::encode($payload)) throw new \RuntimeException('Full snapshot read-back differs from original');
        $reconstructedEnvelope=$envelope;$reconstructedEnvelope['payload']=$reconstructedPayload;
        if (!hash_equals($wire['decoded_sha256'],hash('sha256',LucianoWireEnvelope::encode($reconstructedEnvelope)))) {
            throw new \RuntimeException('Reconstructed envelope differs from original');
        }
        if ($ready) return ['readback_matches_original'=>true,'ready_import_verified'=>true,
            'snapshot_key'=>$wire['snapshot_key'],'wire_sha256'=>$expectedWireSha,'payload_sha256'=>$wire['payload_sha256'],
            'section_counts'=>$validated['section_counts'],'database_written'=>false,'activated'=>false,'published'=>false,
            'receipt_generated'=>false,'ack_sent'=>false,'catalogue_mapping_verified'=>false,'consumer_integrated'=>false,'delivery_ready'=>false];
        return ['readback_matches_original'=>true,'section_counts'=>$validated['section_counts'],
            'receipt'=>['format'=>'luciano-wire-receipt-v1','receiver'=>'crm','status'=>'applied',
                'snapshot_key'=>$wire['snapshot_key'],'payload_sha256'=>$wire['payload_sha256'],
                'mappings_sha256'=>$wire['mappings_sha256'],'wire_sha256'=>$expectedWireSha,
                'decoded_sha256'=>$wire['decoded_sha256'],'section_counts'=>$validated['section_counts']],
            'database_written'=>false,'ack_sent'=>false,'catalogue_mapping_verified'=>false,
            'consumer_integrated'=>false,'delivery_ready'=>false];
    }

    private static function fields(array $row, array $fields): void
    {
        foreach ($fields as $field) if (!array_key_exists($field,$row)) throw new \RuntimeException('Incomplete read-back evidence');
    }

    private static function identity($value, $expected): bool
    {
        return (is_int($value) || is_string($value)) && (string)$value===$expected;
    }

    private static function positiveId($value): string
    {
        if ((!is_int($value) && !is_string($value)) || !preg_match('/^[1-9][0-9]{0,19}$/D',(string)$value)) {
            throw new \RuntimeException('Invalid snapshot ID');
        }
        if (strlen((string)$value)===20 && strcmp((string)$value,'18446744073709551615')>0) throw new \RuntimeException('Snapshot ID overflows storage');
        return (string)$value;
    }

    private static function utc($value): void
    {
        if (!is_string($value)) throw new \RuntimeException('Missing applied timestamp');
        $date=\DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$value,new \DateTimeZone('UTC'));
        if (!$date || $date->format('Y-m-d H:i:s')!==$value) throw new \RuntimeException('Invalid applied UTC timestamp');
    }
}
