<?php
// Public booking pages verified against price.tonia.ru properties on 2026-10-19.
// Add the booking page here when connecting a new object to Price.
function ptl_booking_pages(){
    return [
        47 => 'https://s-bor.com/booking/',
        207 => 'https://mednehama.ru/booking/',
        13 => 'https://www.san-delfin.ru/booking/',
        4 => 'https://www.balkish.org/?page_id=490',
        25 => 'https://sankaragai.ru/booking/',
        19 => 'https://green-kurort.ru/?tl-booking-open=true',
        29 => 'https://krasnousolsk.ru/booking/',
        662 => 'https://gorkihotel.ru/?tl-booking-open=true',
        30 => 'https://krutushka-kazan.ru/booking/',
        3 => 'https://www.bakirovo.com/booking/',
        57 => 'https://yumatovo.ru/booking/',
        22 => 'https://ижмин.рф/booking/',
        46 => 'https://sbereg.ru/on-lajn-bronirovanie',
        313 => 'https://skk-znanie.ru/booking/',
        14 => 'https://demidkovo.ru/booking/',
        667 => 'https://www.sanrussia.ru/booking/',
        94 => 'https://booking.aleancollection.ru/?hotel=aa66ad8c-9ba2-11eb-9464-005056b233c6',
        848 => 'https://booking.aleancollection.ru/?hotel=9f6f7fd2-6877-11eb-945c-005056b233c6',
        1085 => 'https://booking.aleancollection.ru/?hotel=9307310d-44fd-11eb-9457-005056b233c6',
        1084 => 'https://booking.aleancollection.ru/?hotel=58bf0f10-4541-11eb-9457-005056b233c6',
        1096 => 'https://www.luciano.ru/bronirovanie/',
        1658 => 'https://luciano-sochi.ru/bronirovanie/',
    ];
}
function ptl_booking_link(array $booking){
    $pages=ptl_booking_pages();
    $url=isset($pages[(int)$booking['id_obj']])?$pages[(int)$booking['id_obj']]:null;
    if(!$url)return null;
    $zone=new DateTimeZone('UTC');
    $arrival=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$booking['date_z'],$zone);
    $departure=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$booking['date_v'],$zone);
    // Keep the page accessible even for an incomplete booking; never invent dates.
    if(!$arrival || !$departure || $arrival->format('Y-m-d')!==$booking['date_z'] || $departure->format('Y-m-d')!==$booking['date_v'] || $departure<$arrival)return $url;
    $alean=parse_url($url,PHP_URL_HOST)==='booking.aleancollection.ru';
    if($alean){
        $params=['datefrom'=>$arrival->format('d.m.Y'),'dateto'=>$departure->format('d.m.Y')];
    }else{
        $days=(int)$arrival->diff($departure)->days+($booking['billing_basis']==='day_inclusive'?1:0);
        if($days<1)return $url;
        $params=['date'=>$arrival->format('Y-m-d'),'nights'=>$days];
    }
    if((int)$booking['number_turist']>0)$params['adults']=(int)$booking['number_turist'];
    return $url.(strpos($url,'?')===false?'?':'&').http_build_query($params,'','&',PHP_QUERY_RFC3986);
}
function ptl_booking_link_html(array $booking){
    $url=ptl_booking_link($booking);
    if(!$url)return '';
    return '<div class="ptl-booking-link" style="margin-top:10px"><a href="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'" target="_blank" rel="noopener noreferrer">Посмотреть наличие и цены по этой заявке на сайте объекта</a></div>';
}
