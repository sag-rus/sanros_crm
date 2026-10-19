<?php
require_once __DIR__.'/../core/price-tonia-booking-link.php';
function verify($condition,$message){if(!$condition)throw new RuntimeException($message);}
$b=['id_obj'=>30,'date_z'=>'2026-10-26','date_v'=>'2026-10-31','number_turist'=>2,'billing_basis'=>'night'];
foreach(ptl_booking_pages() as $id=>$page){
    $b['id_obj']=$id;$url=ptl_booking_link($b);parse_str(parse_url($url,PHP_URL_QUERY),$q);
    verify(strpos($url,$page)===0,'Wrong property '.$id);
    if(isset($q['hotel']))verify($q['datefrom']==='26.10.2026' && $q['dateto']==='31.10.2026','Alean dates');
    else verify($q['date']==='2026-10-26' && $q['nights']==='5','TravelLine dates');
    verify($q['adults']==='2','Adults');
}
$b['id_obj']=3;$b['billing_basis']='day_inclusive';
verify(strpos(ptl_booking_link($b),'nights=6')!==false,'Inclusive departure');
$b['date_z']='2024-02-28';$b['date_v']='2024-03-01';
verify(strpos(ptl_booking_link($b),'nights=3')!==false,'Leap year / past dates');
$b['date_z']='2026-02-30';verify(ptl_booking_link($b)===ptl_booking_pages()[3],'Invalid date fallback');
$b['date_z']='2026-10-26';$b['date_v']='2026-10-31';
$html=ptl_booking_link_html($b);verify(strpos($html,'&amp;')!==false && strpos($html,'noopener noreferrer')!==false,'Safe HTML');
$panel=file_get_contents(__DIR__.'/../core/price_tonia_live.php');
verify(strpos($panel,'aria-live="polite"></div>\'.ptl_booking_link_html($r)')!==false,'Footer outside replaceable results');
$js=file_get_contents(__DIR__.'/../core/price-tonia-live-panel.js');verify(strpos($js,'r.url')===false,'No duplicate result link');
echo "PASS: all booking pages, engine dates, inclusive days, invalid dates, safe footer and no duplicate link\n";
