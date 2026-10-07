<?php
require_once __DIR__.'/../core/luciano_booking_quote.php';
require_once __DIR__.'/../core/luciano_manager_quote.php';
$checks=0;
function lbq_assert($value,$label) { global $checks; ++$checks; if (!$value) throw new RuntimeException($label); }
function lbq_input($object,$adults,$nights,$arrival='2026-11-23',$amount='28900.37') {
    $depart=(new DateTimeImmutable($arrival))->modify('+'.$nights.' days')->format('Y-m-d');
    $position=['id_room'=>123,'rate'=>456,'place'=>$nights===1?0:'Цена за весь срок проживания','number'=>1,
        'type_index'=>$nights===1?2:3,'date'=>$arrival,'days'=>$nights,'departure'=>$depart,'price'=>$amount];
    if ($nights>1) $position['price_basis']='stay_total';
    $guests=[]; for ($i=0;$i<$adults;++$i) $guests[]=(object)['fio'=>'<b>Гость</b>   Пример'];
    return (object)['id_obj'=>$object,'adults'=>$adults,'childs'=>0,'tl'=>0,'bnovo'=>0,'date'=>(new DateTimeImmutable($arrival))->format('d.m.Y'),
        'departure'=>$depart,'days'=>$nights,'sum'=>$amount,'position'=>json_encode([$position],JSON_UNESCAPED_UNICODE),
        'price_snapshot'=>'reload-v1-luciano-initial-'.$object.'-12345678-1234-1234-1234-123456789abc','guests'=>$guests];
}
function lbq_bad($data,$label,$today='2026-10-07') {
    try { LucianoBookingQuote::parse($data,$today); }
    catch (RuntimeException $e) { lbq_assert(true,$label); return; }
    throw new RuntimeException('Accepted: '.$label);
}
function lbq_position($data,$key,$value) {
    $p=json_decode($data->position,true);$p[0][$key]=$value;$data->position=json_encode($p,JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION);return $data;
}
foreach ([1096,1658] as $object) foreach ([1,2,3] as $adults) foreach ([1,2,7] as $nights) {
    $data=lbq_input($object,$adults,$nights);$r=LucianoBookingQuote::parse($data,'2026-10-07');
    lbq_assert($r['crm_object_id']===$object && $r['property_id']===($object===1096?14:15) && $r['provider_id']===($object===1096?3026:434),'scope');
    lbq_assert($r['quoted_total']==='28900.37' && $r['nights']===$nights && $r['adults']===$adults,'full package exactly once');
    lbq_assert($r['price_basis']===($nights===1?'nightly':'stay_total'),'basis');
    lbq_assert(count($r['guests'])===$adults && $r['guests']===array_fill(0,$adults,'Гость Пример'),'duplicate names preserved');
    lbq_assert(!$r['booking_accepted'] && !$r['source_quote_verified'] && !$r['catalogue_mapping_verified'] && !$r['database_written'] && $r['validation_only'],'no authority or writes');
    lbq_assert($r['positions']===json_decode($data->position,true),'original position preserved');
    // Explicitly synthetic in-memory conversion only, never a request or DB row.
    $manager=LucianoManagerQuote::build(['id'=>7,'id_obj'=>$object,'number_turist'=>$adults,'children_rest'=>0,'date_z'=>$r['arrival'],'date_v'=>$r['departure']],
        [['id_room'=>$r['crm_room_id'],'ratePlan'=>$r['crm_rate_id'],'date_z'=>$r['arrival'],'days'=>$nights,'number'=>1,
          'type'=>$nights===1?2:3,'add_one_day'=>1,'sum'=>$r['quoted_total']]],'2026-10-07');
    lbq_assert($manager['quoted_total']===$r['quoted_total'] && $manager['price_basis']===$r['price_basis'],'manager amount cross-contract');
    $strings=clone $data;foreach (['id_obj','adults','days','childs','tl','bnovo'] as $field) $strings->$field=(string)$strings->$field;
    lbq_assert(LucianoBookingQuote::parse($strings,'2026-10-07')===$r,'canonical DB strings');
}
foreach ([1096,1658] as $object) {
    foreach (['2026-12-31','2027-04-30'] as $date) {
        $r=LucianoBookingQuote::parse(lbq_input($object,3,7,$date,'0.01'),'2026-10-07');
        lbq_assert($r['departure']===(new DateTimeImmutable($date))->modify('+7 days')->format('Y-m-d') && $r['quoted_total']==='0.01','cross-year or May checkout');
    }
    $r=LucianoBookingQuote::parse(lbq_input($object,1,2,'2026-11-23','9999999999.99'),'2026-10-07');
    lbq_assert($r['quoted_total']==='9999999999.99','maximum exact cents');
    $base=lbq_input($object,2,2);
    foreach (['id_obj','adults','childs','tl','bnovo','date','departure','days','sum','position','price_snapshot','guests'] as $field) {
        $d=clone $base;unset($d->$field);lbq_bad($d,'missing '.$field);
    }
    foreach ([1096.0,1658.0,true,false,'01096',' 1096','1096 ','1.096e3','+1096',null,1097,'9223372036854775808'] as $value) {
        $d=clone $base;$d->id_obj=$value;lbq_bad($d,'object coercion or foreign');
    }
    foreach (['adults','days'] as $field) foreach ([2.0,true,false,'02','2 ','+2','2e0',null,0,'9223372036854775808'] as $value) {
        $d=clone $base;$d->$field=$value;lbq_bad($d,'canonical '.$field);
    }
    foreach (['childs','tl','bnovo'] as $field) foreach ([false,true,0.0,'00','0 ',null,1,[]] as $value) {
        $d=clone $base;$d->$field=$value;lbq_bad($d,'mode '.$field);
    }
    foreach ([28900,28900.37,true,false,null,[],'028900.37','+28900.37','28900.370','28900.3','2.890037e4','28900,37','0.00','-1.00','10000000000.00'] as $value) {
        $d=clone $base;$d->sum=$value;lbq_bad($d,'amount coercion or precision');
        lbq_bad(lbq_position(clone $base,'price',$value),'position money');
    }
    foreach (['id_room','rate','number','type_index','days'] as $field) foreach ([2.0,true,false,'02','+2',null,0] as $value) lbq_bad(lbq_position(clone $base,$field,$value),'position '.$field);
    foreach (['type_index'=>2,'number'=>2,'days'=>1,'departure'=>'2026-11-24','date'=>'2026-11-24','price_basis'=>'nightly','price'=>'57800.74'] as $field=>$value) lbq_bad(lbq_position(clone $base,$field,$value),'wrong exact package '.$field);
    foreach (['price_basis','id_room','rate','place','days','price','date','departure','number','type_index'] as $field) {
        $d=clone $base;$p=json_decode($d->position,true);unset($p[0][$field]);$d->position=json_encode($p);lbq_bad($d,'missing position '.$field);
    }
    foreach (['{}','null','[]','[null]','{bad','[[1]]',str_repeat(' ',16385)] as $json) { $d=clone $base;$d->position=$json;lbq_bad($d,'JSON shape or size'); }
    $d=clone $base;$p=json_decode($d->position,true);$d->position=json_encode([$p[0],$p[0]]);lbq_bad($d,'duplicate positions');
    lbq_bad(lbq_position(clone $base,'place','Цена за одну ночь'),'incorrect exact placement label');
    lbq_bad(lbq_position(clone $base,'add_one_day',2),'unknown inclusive-day field');
    $d=clone $base;$d->price_snapshot=str_replace((string)$object,$object===1096?'1658':'1096',$d->price_snapshot);lbq_bad($d,'foreign snapshot');
    foreach (['reload-v1-old','luciano-initial-123',null,[],true] as $key) { $d=clone $base;$d->price_snapshot=$key;lbq_bad($d,'invalid key'); }
    foreach (['31.02.2027','2026-11-23',null,23] as $value) { $d=clone $base;$d->date=$value;lbq_bad($d,'arrival'); }
    foreach (['2026-11-23','2026-11-22','2026-02-31',null] as $value) { $d=clone $base;$d->departure=$value;lbq_bad($d,'departure'); }
    foreach ([0,8] as $nights) lbq_bad(lbq_input($object,2,$nights),'stay length');
    foreach (['2026-10-04','2027-05-01'] as $date) lbq_bad(lbq_input($object,2,2,$date),'arrival bounds');
    lbq_bad($base,'past arrival','2026-11-24');
    lbq_bad($base,'invalid today','2026-02-31');
    $d=clone $base;$d->adults=4;lbq_bad($d,'too many adults');
    foreach ([null,[],[(object)['fio'=>'Гость Пример']],['Гость Пример','Гость Пример'],[(object)['fio'=>false],(object)['fio'=>'Гость Пример']],
        [(object)['fio'=>"Гость\0Пример"],(object)['fio'=>'Гость Пример']],[(object)['fio'=>"\xff"],(object)['fio'=>'Гость Пример']],
        [(object)['fio'=>'А'],(object)['fio'=>'Гость Пример']],[(object)['fio'=>str_repeat('А',201)],(object)['fio'=>'Гость Пример']]] as $guests) {
        $d=clone $base;$d->guests=$guests;lbq_bad($d,'guest list or name');
    }
    $d=lbq_input($object,2,1);lbq_bad(lbq_position($d,'type_index',3),'one-night exact rejected');
    $d=lbq_input($object,2,1);lbq_bad(lbq_position($d,'price_basis','stay_total'),'one-night stay basis rejected');
    $d=lbq_input($object,2,1);lbq_bad(lbq_position($d,'place',false),'coercible nightly placement');
    $d=clone $base;$d->guests=[(object)['fio'=>str_repeat('<br>',1100).'Гость Пример'],(object)['fio'=>'Гость Пример']];lbq_bad($d,'bounded raw guest text');
}
echo 'PASS '.$checks." Luciano submission-contract assertions (memory only; no acceptance or DB)\n";
