<?php

namespace App\Support;

/** Validate the entire scoped packet before a future consumer begins any INSERT. */
final class LucianoWireImportPlan
{
    public static function validate(array $wire, string $receiver, array $sectionFields): array
    {
        $e=LucianoWireEnvelope::unpack($wire,$receiver);
        $data=$e['payload']['data']; $header=$e['payload']['snapshot'];
        $sections=['catalog','observations','daily','restrictions','offers'];
        $keys=array_keys($sectionFields); sort($keys); $wanted=$sections; sort($wanted);
        if ($keys!==$wanted) throw new \RuntimeException('Missing destination section schema');
        foreach ($sections as $section) {
            $expected=$sectionFields[$section];
            if (!is_array($expected) || count($expected)!==count(array_unique($expected))) throw new \RuntimeException('Invalid destination schema');
            foreach ($expected as $field) if (!is_string($field) || !preg_match('/^[a-z_]+$/D',$field)) throw new \RuntimeException('Invalid destination field');
            sort($expected);
            foreach ($data[$section] as $row) {
                $actual=array_keys($row); sort($actual);
                if ($actual!==$expected) throw new \RuntimeException('Destination fields differ: '.$section);
            }
        }
        self::date($header['coverage_from']??null); self::date($header['coverage_to_exclusive']??null);
        if ($header['coverage_from']>=$header['coverage_to_exclusive']) throw new \RuntimeException('Invalid coverage');
        $cats=[]; $obs=[]; $daily=[]; $offers=[];
        foreach ($data['catalog'] as $row) {
            $key=$row['catalog_key'];
            $identity=json_encode([$row['entity_type'],$row['external_room_key'],$row['external_rate_key']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            if ($identity===false || $key!==hash('sha256',$identity) || isset($cats[$key])
                || !in_array($row['entity_type'],['room','rate'],true)) throw new \RuntimeException('Invalid or duplicate catalogue identity');
            $cats[$key]=$row;
        }
        foreach ($data['observations'] as $row) {
            $key=$row['observation_key']; $cat=self::rate($cats,$row['catalog_key']);
            if (!is_string($key) || $key==='' || isset($obs[$key])
                || $cat['external_room_key']!==$row['external_room_key'] || $cat['external_rate_key']!==$row['external_rate_key']) throw new \RuntimeException('Observation identity mismatch');
            self::stay($row);
            if ($row['children_count']!=='0') throw new \RuntimeException('Unsupported child occupancy');
            $obs[$key]=$row;
        }
        foreach (['daily','offers','restrictions'] as $section) foreach ($data[$section] as $row) {
            self::rate($cats,$row['catalog_key']);
            $key=$row['observation_key'];
            if ($section==='restrictions' && $key===null) continue;
            if (!is_string($key) || !isset($obs[$key]) || $obs[$key]['catalog_key']!==$row['catalog_key']) throw new \RuntimeException('Missing or mismatched observation reference');
            $source=$obs[$key];
            if ($section==='restrictions') continue;
            foreach (['adults','currency','price_basis','price_model','availability_state','checked_at','expires_at'] as $field) {
                if ($row[$field]!==$source[$field]) throw new \RuntimeException('Quote observation differs: '.$field);
            }
            if ($section==='daily') {
                self::date($row['stay_date']);
                if ($source['nights']!=='1' || $row['stay_date']!==$source['arrival'] || $row['amount']!==$source['total_amount']
                    || $row['stay_date']<$header['coverage_from'] || $row['stay_date']>=$header['coverage_to_exclusive']) throw new \RuntimeException('Nightly row is not an original one-night quote');
                $identity=LucianoWireEnvelope::encode([$row['catalog_key'],$row['stay_date'],$row['adults']]);
                if (isset($daily[$identity])) throw new \RuntimeException('Duplicate daily identity');
                $daily[$identity]=true;
            } else {
                self::stay($row);
                foreach (['arrival','departure','nights','total_amount','nightly_breakdown_json'] as $field) if ($row[$field]!==$source[$field]) throw new \RuntimeException('Exact stay differs from observation');
                if ((int)$row['nights']<2 || !is_string($row['offer_key']) || $row['offer_key']==='' || isset($offers[$row['offer_key']])) throw new \RuntimeException('Invalid or duplicate exact stay');
                $identity=LucianoWireEnvelope::encode([$row['catalog_key'],$row['arrival'],$row['departure'],$row['adults']]);
                if (isset($offers['identity:'.$identity])) throw new \RuntimeException('Duplicate exact stay identity');
                $offers[$row['offer_key']]=true; $offers['identity:'.$identity]=true;
            }
        }
        return ['envelope'=>$e,'section_counts'=>array_map('count',$data),'schema_and_references_validated'=>true,
            'database_written'=>false,'consumer_integrated'=>false,'delivery_ready'=>false];
    }

    private static function rate(array $cats, $key): array
    {
        if (!is_string($key) || !isset($cats[$key]) || $cats[$key]['entity_type']!=='rate') throw new \RuntimeException('Missing rate catalogue reference');
        return $cats[$key];
    }

    private static function date($value): \DateTimeImmutable
    {
        if (!is_string($value)) throw new \RuntimeException('Invalid date');
        $date=\DateTimeImmutable::createFromFormat('!Y-m-d',$value,new \DateTimeZone('UTC'));
        if (!$date || $date->format('Y-m-d')!==$value) throw new \RuntimeException('Invalid date');
        return $date;
    }

    private static function money($value): int
    {
        if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]{0,9})\.([0-9]{2})$/D',$value,$m)) throw new \RuntimeException('Invalid exact money');
        return (int)$m[1]*100+(int)$m[2];
    }

    private static function stay(array $row): void
    {
        $arrival=self::date($row['arrival']); $departure=self::date($row['departure']);
        if (!in_array($row['nights'],['1','2','3','4','5','6','7'],true) || !in_array($row['adults'],['1','2','3'],true)
            || $arrival->modify('+'.$row['nights'].' days')!=$departure || $row['currency']!=='RUB'
            || $row['availability_state']!=='observed_available') throw new \RuntimeException('Invalid stay identity');
        $one=$row['nights']==='1';
        if ($row['price_basis']!==($one?'room_per_night_for_occupancy':'stay_total') || $row['price_model']!==($one?'nightly':'stay_dependent')) throw new \RuntimeException('Invalid stay price basis');
        $days=json_decode($row['nightly_breakdown_json'],true);
        if (json_last_error()!==JSON_ERROR_NONE || !is_array($days) || array_keys($days)!==range(0,count($days)-1) || count($days)!==(int)$row['nights']) throw new \RuntimeException('Incomplete nightly breakdown');
        $total=0;
        foreach ($days as $i=>$day) {
            if (!is_array($day) || count($day)!==2 || !isset($day['date'],$day['amount']) || $day['date']!==$arrival->modify('+'.$i.' days')->format('Y-m-d')) throw new \RuntimeException('Invalid exact stay day');
            $total+=self::money($day['amount']);
        }
        if ($total!==self::money($row['total_amount']) || $total<=0) throw new \RuntimeException('Exact nightly sum differs');
    }
}
