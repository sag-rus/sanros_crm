<?php
namespace App\Support;

/** Read-only initial preflight boundary; it grants no permission to import or activate. */
final class LucianoWireDestination
{
    public static function initial($hotel, array $states, $snapshotCount)
    {
        $scopes=['kazan'=>['1096','14'],'sochi'=>['1658','15']];
        if (!is_string($hotel) || !isset($scopes[$hotel]) || !is_int($snapshotCount)
            || $snapshotCount!==0 || count($states)!==1 || !isset($states[0]) || !is_array($states[0])) {
            throw new \RuntimeException('Luciano initial destination is not empty and unambiguous');
        }
        $state=$states[0];$scope=$scopes[$hotel];
        foreach (['source','crm_object_id','external_property_key','import_enabled','publish_enabled','active_snapshot_id','stale_after_seconds','timezone'] as $field) {
            if (!array_key_exists($field,$state)) throw new \RuntimeException('Incomplete Luciano destination state');
        }
        if ($state['source']!=='price_tonia_ru' || !self::identity($state['crm_object_id'],$scope[0])
            || !self::identity($state['external_property_key'],$scope[1])
            || !in_array($state['import_enabled'],[0,'0'],true) || !in_array($state['publish_enabled'],[0,'0'],true)
            || $state['active_snapshot_id']!==null) {
            throw new \RuntimeException('Luciano initial destination scope or dormant state differs');
        }
        // Historical quote checks remain their original collection lower bounds.
        // An expiry threshold cannot silently relabel them fresh during onboarding.
        if ($state['stale_after_seconds']!==null || $state['timezone']!=='Europe/Moscow') {
            throw new \RuntimeException('Luciano destination freshness or timezone needs review');
        }
        return ['initial_destination_verified'=>true,'object_id'=>$scope[0],'external_property_key'=>$scope[1],
            'snapshot_count'=>0,'dormant'=>true,'freshness_threshold'=>null,'timezone'=>'Europe/Moscow',
            'database_written'=>false,'activation_allowed'=>false,'delivery_ready'=>false];
    }

    private static function identity($value, $expected)
    {
        return (is_int($value) || is_string($value)) && (string)$value===$expected;
    }
}
