<?php
// PHP 7.1. Pure submission contract only; never accepts or creates a booking.
final class LucianoBookingQuote
{
    private static function id($value)
    {
        if ((!is_int($value) && !is_string($value)) || !preg_match('/^[1-9][0-9]*$/D',(string)$value)
            || filter_var($value,FILTER_VALIDATE_INT)===false) throw new RuntimeException('Invalid canonical ID');
        return (int)$value;
    }
    private static function zero($value)
    {
        if ($value!==0 && $value!=='0') throw new RuntimeException('Unsupported children or external booking mode');
    }
    private static function date($value,$format)
    {
        if (!is_string($value)) throw new RuntimeException('Invalid date');
        $date=DateTimeImmutable::createFromFormat('!'.$format,$value,new DateTimeZone('UTC'));
        if (!$date || $date->format($format)!==$value) throw new RuntimeException('Invalid date');
        return $date;
    }
    private static function money($value)
    {
        if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]{0,9})\.([0-9]{2})$/D',$value,$parts)) throw new RuntimeException('Invalid exact RUB amount');
        $amount=(int)$parts[1]*100+(int)$parts[2];
        if ($amount<1) throw new RuntimeException('Nonpositive amount');
        return $amount;
    }
    private static function field(stdClass $data,$name)
    {
        if (!property_exists($data,$name)) throw new RuntimeException('Missing submission field');
        return $data->$name;
    }

    public static function parse(stdClass $data,$today)
    {
        if (get_class($data)!=='stdClass') throw new RuntimeException('Plain submission object required');
        $object=self::id(self::field($data,'id_obj'));
        $scopes=[1096=>[14,3026],1658=>[15,434]];
        if (!isset($scopes[$object])) throw new RuntimeException('Unsupported Luciano object');
        foreach (['childs','tl','bnovo'] as $flag) self::zero(self::field($data,$flag));
        $adults=self::id(self::field($data,'adults'));
        if ($adults>3) throw new RuntimeException('Only 1–3 adults supported');
        $arrival=self::date(self::field($data,'date'),'d.m.Y');
        $departure=self::date(self::field($data,'departure'),'Y-m-d');
        self::date($today,'Y-m-d');
        $nights=(int)$arrival->diff($departure)->format('%r%a');
        $arrivalText=$arrival->format('Y-m-d');$departureText=$departure->format('Y-m-d');
        if ($nights<1 || $nights>7 || self::id(self::field($data,'days'))!==$nights || $arrivalText<$today
            || $arrivalText<'2026-10-05' || $arrivalText>'2027-04-30') throw new RuntimeException('Unsupported stay');
        $snapshot=self::field($data,'price_snapshot');
        if (!is_string($snapshot) || !preg_match('/^reload-v1-luciano-initial-'.$object.'-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D',$snapshot)) throw new RuntimeException('Invalid own initial snapshot key');
        $json=self::field($data,'position');
        if (!is_string($json) || strlen($json)>16384) throw new RuntimeException('Invalid positions JSON');
        $positions=json_decode($json,true,8);
        if (json_last_error()!==JSON_ERROR_NONE || !is_array($positions) || array_keys($positions)!==[0] || !is_array($positions[0])) throw new RuntimeException('One complete package position required');
        $p=$positions[0];
        foreach (['id_room','rate','place','number','type_index','date','days','departure','price'] as $key) {
            if (!array_key_exists($key,$p)) throw new RuntimeException('Incomplete package position');
        }
        $allowed=['id_room','rate','place','number','type_index','date','days','departure','price','price_basis'];
        if (array_diff(array_keys($p),$allowed)) throw new RuntimeException('Unknown package position field');
        $room=self::id($p['id_room']);$rate=self::id($p['rate']);
        $type=self::id($p['type_index']);
        if (self::id($p['number'])!==1 || self::id($p['days'])!==$nights || $p['date']!==$arrivalText || $p['departure']!==$departureText
            || $type!==($nights===1?2:3)) throw new RuntimeException('Invalid package coverage or charging type');
        if (($type===3 && (!array_key_exists('price_basis',$p) || $p['price_basis']!=='stay_total'))
            || ($type===2 && array_key_exists('price_basis',$p) && $p['price_basis']!=='nightly')) throw new RuntimeException('Invalid package price basis');
        if (($type===3 && $p['place']!=='Цена за весь срок проживания')
            || ($type===2 && $p['place']!==0 && $p['place']!=='0')) throw new RuntimeException('Invalid package placement label');
        $amount=self::money($p['price']);
        // Both supported shapes contain one full occupancy package. No factor
        // for adults, room capacity, number of nights or the checkout date.
        if ($amount!==self::money(self::field($data,'sum'))) throw new RuntimeException('Amount differs from package');
        $guests=self::field($data,'guests');
        if (!is_array($guests) || count($guests)!==$adults || array_keys($guests)!==range(0,$adults-1)) throw new RuntimeException('Invalid guest list');
        $names=[];
        foreach ($guests as $guest) {
            if (!$guest instanceof stdClass || get_class($guest)!=='stdClass' || !property_exists($guest,'fio') || !is_string($guest->fio) || strlen($guest->fio)>4096
                || !preg_match('//u',$guest->fio) || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/',$guest->fio)) throw new RuntimeException('Invalid guest name');
            $name=preg_replace('/\s+/u',' ',trim(strip_tags($guest->fio)));
            if (!is_string($name) || mb_strlen($name,'UTF-8')<2 || mb_strlen($name,'UTF-8')>200) throw new RuntimeException('Invalid guest name');
            $names[]=$name;
        }
        return ['crm_object_id'=>$object,'property_id'=>$scopes[$object][0],'provider_id'=>$scopes[$object][1],
            'crm_room_id'=>$room,'crm_rate_id'=>$rate,'arrival'=>$arrivalText,'departure'=>$departureText,'adults'=>$adults,'nights'=>$nights,
            'snapshot_key'=>$snapshot,'quoted_total'=>$p['price'],'price_basis'=>$type===3?'stay_total':'nightly','billing_basis'=>'night',
            'positions'=>$positions,'guests'=>$names,'validation_only'=>true,'source_quote_verified'=>false,
            'catalogue_mapping_verified'=>false,'booking_accepted'=>false,'database_written'=>false];
    }
}
