<?php
// The CRM stores no catalogue or moderation state. All data comes from the website.
function cbm_request(array $payload) {
    $secret=trim((string)@file_get_contents('/etc/sanata/comfort-booking-moderation-token'));
    if(strlen($secret)!==64) throw new RuntimeException('Связь с сайтом ещё не настроена.');
    $body=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $time=(string)time();$nonce=bin2hex(random_bytes(32));
    $signature=hash_hmac('sha256',"POST\n".$time."\n".$nonce."\n".hash('sha256',$body),$secret);
    $host='xn----7sba6aaba8akdsdekah.xn--p1ai';
    $ch=curl_init('https://'.$host.'/api/comfort_booking_moderation');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>40,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
        CURLOPT_RESOLVE=>[$host.':443:10.8.0.150'],
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-CB-Time: '.$time,'X-CB-Nonce: '.$nonce,'X-CB-Signature: '.$signature]]);
    $response=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    $data=is_string($response)?json_decode($response,true):null;
    if($status<200 || $status>=300 || !is_array($data) || empty($data['success'])) {
        if($status===409) throw new DomainException('Статус объекта уже изменён. Обновите список.');
        throw new RuntimeException('Не удалось получить ответ сайта. Обновите список и проверьте статус перед повторным действием.');
    }
    return $data['data'];
}
function cbm_user() {
    require_once dirname(__DIR__).'/config.php';$c=new JConfig;
    $p=new PDO('mysql:host='.$c->host.';dbname='.$c->db.';charset=utf8',$c->user,$c->password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $q=$p->prepare('SELECT u.id,u.rights FROM session s JOIN users u ON u.login=s.login AND u.password=s.password WHERE s.id_session=? AND u.dostup=1 AND u.rights>1');
    $q->execute([(string)($_COOKIE['session']??'')]);return $q->fetch(PDO::FETCH_ASSOC);
}
function cbm_allowed($user, $action, $method, $csrf, $expectedCsrf) {
    if(!$user || (int)$user['rights']<2 || $method!=='POST') return false;
    if(in_array($action,['list','count'],true)) return true;
    return (int)$user['rights']>=3 && in_array($action,['approve','disable'],true)
        && is_string($expectedCsrf) && strlen($expectedCsrf)===64 && hash_equals($expectedCsrf,(string)$csrf);
}
