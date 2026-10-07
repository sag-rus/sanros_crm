<?php
// PHP 7.1 compatible. Pure, scoped contract; no DB, booking or queue writes.
final class LucianoManagerQuote
{
    public static function scope($object)
    {
        $scopes = [1096 => ['property_id'=>14,'provider_id'=>3026], 1658 => ['property_id'=>15,'provider_id'=>434]];
        if (!isset($scopes[(int)$object])) throw new RuntimeException('Only the two Luciano objects are supported');
        return $scopes[(int)$object];
    }

    private static function integer($value)
    {
        if (filter_var($value,FILTER_VALIDATE_INT)===false || (int)$value<1) throw new RuntimeException('Invalid positive integer');
        return (int)$value;
    }

    private static function date($value)
    {
        if (!is_string($value)) throw new RuntimeException('Invalid date');
        $d=DateTimeImmutable::createFromFormat('!Y-m-d',$value,new DateTimeZone('UTC'));
        if (!$d || $d->format('Y-m-d')!==$value) throw new RuntimeException('Invalid date');
        return $d;
    }

    private static function money($value)
    {
        $value=(string)$value;
        if (!preg_match('/^([0-9]{1,10})(?:\.([0-9]{1,2}))?$/D',$value,$m)) throw new RuntimeException('Invalid exact RUB amount');
        $amount=(int)$m[1]*100+(isset($m[2])?(int)str_pad($m[2],2,'0'):0);
        if ($amount<1) throw new RuntimeException('Nonpositive amount');
        return $amount;
    }

    public static function build(array $booking,array $positions,$today)
    {
        $object=self::integer(isset($booking['id_obj'])?$booking['id_obj']:0);
        $scope=self::scope($object);
        $id=self::integer(isset($booking['id'])?$booking['id']:0);
        $adults=self::integer(isset($booking['number_turist'])?$booking['number_turist']:0);
        if ($adults>3 || !array_key_exists('children_rest',$booking) || filter_var($booking['children_rest'],FILTER_VALIDATE_INT)!==0) throw new RuntimeException('Only 1–3 adults without children are supported');
        $arrival=self::date(isset($booking['date_z'])?$booking['date_z']:null);
        $departure=self::date(isset($booking['date_v'])?$booking['date_v']:null);
        self::date($today);
        $nights=(int)$arrival->diff($departure)->format('%r%a');
        if ($nights<1 || $nights>7 || $arrival->format('Y-m-d')<$today
            || $arrival->format('Y-m-d')<'2026-10-05' || $arrival->format('Y-m-d')>'2027-04-30'
            || !$positions || count($positions)>$nights) throw new RuntimeException('Unsupported or incomplete stay');
        $next=$arrival;$amount=0;$room=null;$rate=null;$basis=null;
        foreach ($positions as $p) {
            foreach (['id_room','ratePlan','date_z','days','number','type','add_one_day','sum'] as $field) {
                if (!array_key_exists($field,$p)) throw new RuntimeException('Incomplete position');
            }
            $r=self::integer($p['id_room']);$t=self::integer($p['ratePlan']);$days=self::integer($p['days']);$type=self::integer($p['type']);
            if (self::integer($p['number'])!==1 || filter_var($p['add_one_day'],FILTER_VALIDATE_INT)!==1 || !in_array($type,[2,3],true)
                || $p['date_z']!==$next->format('Y-m-d') || $days>$nights
                || ($room!==null && ($room!==$r || $rate!==$t || $basis!==$type))) throw new RuntimeException('Mixed, duplicate or non-night position');
            if ($type===3 && (count($positions)!==1 || $days!==$nights || $nights<2)) throw new RuntimeException('Exact stay must be one complete 2–7-night position');
            // A type 3 sum covers the full occupancy package once, including discounts.
            // Only type 2 is explicitly a per-night amount for this one room.
            $amount+=self::money($p['sum'])*($type===2?$days:1);
            $next=$next->modify('+'.$days.' days');$room=$r;$rate=$t;$basis=$type;
        }
        if ($next->format('Y-m-d')!==$departure->format('Y-m-d')) throw new RuntimeException('Position coverage differs from the stay');
        return $scope+['booking_id'=>$id,'crm_object_id'=>$object,'crm_room_id'=>$room,'crm_rate_id'=>$rate,
            'arrival'=>$arrival->format('Y-m-d'),'departure'=>$departure->format('Y-m-d'),'adults'=>$adults,
            'quoted_total'=>intdiv($amount,100).'.'.str_pad((string)($amount%100),2,'0',STR_PAD_LEFT),
            'billing_basis'=>'night','price_basis'=>$basis===3?'stay_total':'nightly','nights'=>$nights];
    }
}
