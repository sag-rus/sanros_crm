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
