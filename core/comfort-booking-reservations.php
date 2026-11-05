<?php
require_once __DIR__.'/comfort-booking-moderation.php';

function cbr_db() {
    require_once dirname(__DIR__).'/config.php';$c=new JConfig;
    return new PDO('mysql:host='.$c->host.';dbname='.$c->db.';charset=utf8',$c->user,$c->password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
}
function cbr_scope($user,$request,$write=false) {
    if(!$user||!$request||!in_array((int)$user['rights'],[2,3,4,5,6],true))return false;
    if((int)$user['rights']<4 && (int)$request['id_user']!==(int)$user['id'])return false;
    return !$write||((int)$user['rights']>=3 && (int)$request['active']!==3);
}
function cbr_allowed($user,$request,$action,$csrf,$expected) {
    if(!cbr_scope($user,$request,$action!=='detail'))return false;
    if($action==='detail')return true;
    return in_array($action,['hotels','offers','prepare','commit'],true)&&is_string($expected)&&strlen($expected)===64&&hash_equals($expected,(string)$csrf);
}
function cbr_request(array $payload) {
    $secret=trim((string)@file_get_contents('/etc/sanata/comfort-booking-moderation-token'));
    if(strlen($secret)!==64)throw new RuntimeException('Связь с сайтом не настроена.');
    // Separate cryptographic purpose: a moderation/calendar signature cannot authorize booking changes.
    $key=hash_hmac('sha256','comfort-booking-reservations-v1',$secret);
    $body=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$time=(string)time();$nonce=bin2hex(random_bytes(32));
    $signature=hash_hmac('sha256',"POST\n".$time."\n".$nonce."\n".hash('sha256',$body),$key);
    $host='xn----7sba6aaba8akdsdekah.xn--p1ai';$ch=curl_init('https://'.$host.'/api/comfort_booking_reservations');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>45,
        CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_RESOLVE=>[$host.':443:10.8.0.150'],
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-CB-Time: '.$time,'X-CB-Nonce: '.$nonce,'X-CB-Signature: '.$signature]]);
    $response=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$data=is_string($response)?json_decode($response,true):null;
    if($status<200||$status>=300||!is_array($data)||empty($data['success'])){
        if(in_array($status,[400,409],true)&&!empty($data['error']))throw new DomainException($data['error']);
        throw new RuntimeException('Нет достоверного ответа сайта. Обновите состояние брони. Для повтора отправки используйте то же подтверждение.');
    }
    return $data['data'];
}

/** Only pass the authenticated website response, never browser-supplied booking data. */
function cbr_sync_cancelled(PDO $p, $requestId, array $data) {
    $booking=$data['booking']??[];
    if(($booking['status']??'')!=='cancelled')return null;
    if(empty($booking['number'])||empty($booking['hotelId']))throw new RuntimeException('Не удалось проверить отменённую бронь.');
    $mysql=$p->getAttribute(PDO::ATTR_DRIVER_NAME)==='mysql';$lock='cbr-cancel-'.(int)$requestId;
    if($mysql){$q=$p->prepare('SELECT GET_LOCK(?,5)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1)throw new RuntimeException('Статус заявки обновляется. Повторите обновление.');}
    try {
        $q=$p->prepare('SELECT id,id_obj,status,status_san,active FROM reckoning WHERE id=?');$q->execute([$requestId]);$r=$q->fetch(PDO::FETCH_ASSOC);
        if(!$r||(string)$r['id_obj']!==(string)$booking['hotelId'])throw new RuntimeException('Объект заявки не совпадает с отменённой бронью.');
        if((int)$r['active']===3)return null;
        if((int)$r['status']===6)return ['status'=>6,'changed'=>false];
        $actor=0;foreach($data['history']??[] as $h){if(in_array($h['action']??'',['cancel','waive'],true)){$actor=(int)$h['actor_id'];break;}}
        $q=$p->prepare('UPDATE reckoning SET status=6 WHERE id=? AND status=? AND active<>3');$q->execute([$requestId,$r['status']]);
        if($q->rowCount()!==1)throw new RuntimeException('Статус заявки изменился. Повторите обновление.');
        $note='Автоматическая аннуляция заявки: бронь Comfort Booking №'.$booking['number'].' отменена на сайте. Доставка отмены в TravelLine: '.($data['delivery']??'unknown').'. Денежный возврат не выполнялся.';
        $now=new DateTimeImmutable('now',new DateTimeZone('Europe/Moscow'));
        $q=$p->prepare('INSERT INTO history_schet (date,time,id_schet,id_user,new_status,new_status_san,note) VALUES (?,?,?,?,6,?,?)');
        $q->execute([$now->format('Y-m-d'),$now->format('H:i:s'),$requestId,$actor,$r['status_san'],$note]);
        return ['status'=>6,'changed'=>true];
    } finally {if($mysql){$q=$p->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]);}}
}
