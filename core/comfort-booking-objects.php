<?php
require_once __DIR__.'/comfort-booking-moderation.php';
function cb_objects(array $rows, &$failed=false){
    $result=[];$failed=false;
    try {foreach(array_chunk($rows,500) as $batch){$requests=[];foreach($batch as $r)$requests[]=['requestId'=>(int)$r['id'],'hotelId'=>max(0,(int)$r['id_obj'])];
        if(!$requests)continue;$answer=cbm_request(['action'=>'report-objects','requests'=>$requests]);
        if(!isset($answer['objects'])||!is_array($answer['objects']))throw new RuntimeException('Invalid lookup');
        foreach($answer['objects'] as $o)$result[(int)$o['requestId']]=$o;
    }}catch(Throwable $e){$failed=true;error_log('Comfort Booking object lookup unavailable');}
    return $result;
}
function cb_object_html(array $o){return htmlspecialchars($o['name'],ENT_QUOTES,'UTF-8').' <span class="label label-danger" title="Comfort Booking">CB</span>';}
