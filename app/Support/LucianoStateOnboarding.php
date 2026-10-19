<?php
namespace App\Support;

/** Plan the creation of one dormant CRM state. Never update an existing state or hotel. */
final class LucianoStateOnboarding
{
    const FIELDS=['source','crm_object_id','external_property_key','upstream_property_key','local_property_id',
        'active_snapshot_id','import_enabled','publish_enabled','timezone','stale_after_seconds','last_attempt_at',
        'last_success_at','last_error','created_at','updated_at','billing_basis'];

    public static function plan($hotel, array $objects, array $states, $snapshotCount, array $fields, $engine): array
    {
        $scopes=['kazan'=>['1096','14','3026','Luciano'],'sochi'=>['1658','15','434','Luciano Sochi сан кур']];
        $actual=$fields;$expected=self::FIELDS;sort($actual);sort($expected);
        if (!is_string($hotel) || !isset($scopes[$hotel]) || $engine!=='InnoDB' || $actual!==$expected
            || !is_int($snapshotCount) || $snapshotCount!==0 || count($objects)!==1 || !isset($objects[0])
            || !is_array($objects[0]) || count($states)>1 || (count($states)===1 && (!isset($states[0]) || !is_array($states[0])))) {
            throw new \RuntimeException('Unsupported or nonempty Luciano state onboarding evidence');
        }
        $s=$scopes[$hotel];$object=$objects[0];
        if (!self::identity($object['id']??null,$s[0]) || ($object['name']??null)!==$s[3]) {
            throw new \RuntimeException('Luciano CRM hotel identity differs from reviewed source');
        }
        $row=['source'=>'price_tonia_ru','crm_object_id'=>$s[0],'external_property_key'=>$s[1],
            'upstream_property_key'=>$s[2],'local_property_id'=>null,'active_snapshot_id'=>null,
            'import_enabled'=>'0','publish_enabled'=>'0','timezone'=>'Europe/Moscow','stale_after_seconds'=>null,
            'last_attempt_at'=>null,'last_success_at'=>null,'last_error'=>null,'billing_basis'=>'night'];
        if (count($states)===1) {
            LucianoWireDestination::initial($hotel,$states,$snapshotCount);
            $state=$states[0];
            foreach ($row as $field=>$value) {
                if (!array_key_exists($field,$state) || ($value===null ? $state[$field]!==null
                    : !self::identity($state[$field],$value))) throw new \RuntimeException('Existing Luciano state must be preserved and reviewed');
            }
            foreach (['created_at','updated_at'] as $field) self::utc($state[$field]??null);
            if ($state['created_at']>$state['updated_at']) throw new \RuntimeException('Invalid existing state timestamps');
        }
        $evidence=['hotel'=>$hotel,'objects'=>$objects,'states'=>$states,'snapshot_count'=>$snapshotCount,
            'schema_fields'=>$actual,'engine'=>$engine];
        return ['hotel'=>$hotel,'object_id'=>$s[0],'external_property_key'=>$s[1],'provider_id'=>$s[2],
            'action'=>count($states)===0?'insert_dormant_state':'already_dormant',
            'evidence'=>$evidence,'evidence_sha256'=>hash('sha256',LucianoWireEnvelope::encode($evidence)),
            'insert_values_without_timestamps'=>$row,'database_written'=>false,'hotel_modified'=>false,
            'snapshot_created'=>false,'activation_allowed'=>false,'delivery_ready'=>false];
    }

    public static function insertValues(array $plan, $utc): array
    {
        self::utc($utc);
        if (($plan['action']??null)!=='insert_dormant_state' || !isset($plan['evidence']) || !is_array($plan['evidence'])) {
            throw new \RuntimeException('Only an absent reviewed state may be inserted');
        }
        // Rebuild from evidence so a caller cannot replace the proposed SQL values or enable publication.
        $e=$plan['evidence'];$rebuilt=self::plan($e['hotel'],$e['objects'],$e['states'],$e['snapshot_count'],$e['schema_fields'],$e['engine']);
        if (LucianoWireEnvelope::encode($rebuilt)!==LucianoWireEnvelope::encode($plan)) throw new \RuntimeException('State onboarding plan changed');
        return $rebuilt['insert_values_without_timestamps']+['created_at'=>$utc,'updated_at'=>$utc];
    }

    private static function identity($value, $expected): bool
    {
        return (is_string($value) || is_int($value)) && (string)$value===$expected;
    }

    private static function utc($value): void
    {
        if (!is_string($value)) throw new \RuntimeException('Missing state UTC timestamp');
        $d=\DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$value,new \DateTimeZone('UTC'));
        if (!$d || $d->format('Y-m-d H:i:s')!==$value) throw new \RuntimeException('Invalid state UTC timestamp');
    }
}
