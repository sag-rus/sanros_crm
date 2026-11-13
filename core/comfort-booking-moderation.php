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
        if(in_array($status,[400,409],true) && !empty($data['error'])) throw new DomainException($data['error']);
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
function cbm_session_csrf() {
    if (empty($_SESSION['cbm_csrf'])) $_SESSION['cbm_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['cbm_csrf'];
}
function cbm_allowed($user, $action, $method, $csrf, $expectedCsrf) {
    if(!$user || (int)$user['rights']<2 || $method!=='POST') return false;
    if(in_array($action,['list','count','analytics','server-metrics'],true)) return true;
    if($action==='calendar-ticket')return is_string($expectedCsrf) && strlen($expectedCsrf)===64 && hash_equals($expectedCsrf,(string)$csrf);
    return (int)$user['rights']>=3 && in_array($action,['approve','disable','bulk-approve','bulk-disable','editorial-read','editorial-save'],true)
        && is_string($expectedCsrf) && strlen($expectedCsrf)===64 && hash_equals($expectedCsrf,(string)$csrf);
}

function cbm_calendar_ticket($id,$actor,$ua,$secret=null,$now=null) {
    $id=(int)$id;$actor=(int)$actor;if($id<=0||$actor<=0)throw new InvalidArgumentException('Некорректный объект.');
    if($secret===null)$secret=trim((string)@file_get_contents('/etc/sanata/comfort-booking-moderation-token'));
    if(strlen($secret)!==64)throw new RuntimeException('Связь с сайтом не настроена.');
    if($now===null)$now=time();
    $claims=['aud'=>'cb-calendar','object'=>$id,'actor'=>$actor,'iat'=>$now,'exp'=>$now+60,'jti'=>bin2hex(random_bytes(32)),'ua'=>hash('sha256',$ua)];
    $body=rtrim(strtr(base64_encode(json_encode($claims,JSON_UNESCAPED_SLASHES)),'+/','-_'),'=');
    return ['object'=>$id,'ticket'=>$body.'.'.hash_hmac('sha256',$body,hash_hmac('sha256','comfort-booking-calendar-v1',$secret,true)),'entryUrl'=>'https://xn----7sba6aaba8akdsdekah.xn--p1ai/api/comfort_booking_calendar'];
}

// CRM sync sends object.id as the site resort ID (core/upload/sync-objects-api.php).
// active=1 is archived, active=0 is enabled. Check the name as a second identity key.
function cbm_reconcile_replacements() {
    $data=cbm_request(['action'=>'replacements']);
    if(!isset($data['ids']) || !is_array($data['ids']))throw new RuntimeException('Не удалось проверить замены объектов.');
    if(!$data['ids'])return;
    require_once dirname(__DIR__).'/config.php';$c=new JConfig;
    $p=new PDO('mysql:host='.$c->host.';dbname='.$c->db.';charset=utf8',$c->user,$c->password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $q=$p->prepare('UPDATE object SET active=1,synchronized=0 WHERE id=? AND name=? AND active=0');
    foreach($data['ids'] as $row) {
        if(!isset($row['id'],$row['name'])||(int)$row['id']<=0)throw new RuntimeException('Некорректный журнал замен.');
        $q->execute([(int)$row['id'],$row['name']]);
        $check=$p->prepare('SELECT active FROM object WHERE id=? AND name=?');$check->execute([(int)$row['id'],$row['name']]);
        $active=$check->fetchColumn();
        if($active===false || (int)$active===0)throw new RuntimeException('Замена на сайте сохранена, но отключение аналога в СРМ требует проверки: ID '.(int)$row['id']);
    }
}

function cbm_replacement_name($id) {
    if((int)$id<=0)return '';
    require_once dirname(__DIR__).'/config.php';$c=new JConfig;
    $p=new PDO('mysql:host='.$c->host.';dbname='.$c->db.';charset=utf8',$c->user,$c->password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $q=$p->prepare('SELECT name FROM object WHERE id=? AND active=0');$q->execute([(int)$id]);$name=$q->fetchColumn();
    if($name===false)throw new DomainException('Аналог не найден среди активных объектов СРМ. Обновите список.');
    return (string)$name;
}
