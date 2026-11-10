<?php
require_once __DIR__.'/core/comfort-booking-request-state.php';
$locked=false;
session_start();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
try {
    if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||(int)($_SERVER['CONTENT_LENGTH']??0)>65536){http_response_code(403);throw new RuntimeException('Нет доступа.');}
    $user=cbm_user();$p=cbr_db();$id=(int)($_POST['requestId']??0);$action=(string)($_POST['action']??'');
    $q=$p->prepare('SELECT id,id_user,active,id_obj,date_z,date_v,turist,type FROM reckoning WHERE id=?');$q->execute([$id]);$request=$q->fetch(PDO::FETCH_ASSOC);
    if(!cbr_allowed($user,$request,$action,$_POST['csrf']??'',$_SESSION['cbr_csrf']??'')){http_response_code(403);throw new RuntimeException('Нет доступа к этой заявке или истекла сессия.');}
    if(empty($_SESSION['cbr_csrf']))$_SESSION['cbr_csrf']=bin2hex(random_bytes(32));$csrf=$_SESSION['cbr_csrf'];
    $calls=array_values(array_filter($_SESSION['cbr_calls']??[],function($t){return $t>time()-60;}));
    if(count($calls)>=60)throw new RuntimeException('Слишком много запросов. Повторите через минуту.');$calls[]=time();$_SESSION['cbr_calls']=$calls;
    $calendarCsrf=cbm_session_csrf();
    session_write_close();
    $operation=(string)($_POST['operation']??'');
    if($operation==='waive'&&!in_array((int)$user['rights'],[5,6],true)){http_response_code(403);throw new RuntimeException('Отмена без штрафа доступна только администратору.');}
    if($action!=='detail'&&(int)$request['type']!==0)throw new RuntimeException('Бронирование доступно только из обычной туристической заявки.');
    // Only posted form values are untrusted. Identity, role, ownership and paid amount are read on the CRM server.
    $q=$p->prepare("SELECT COALESCE(SUM(CASE WHEN type IN (1,2) THEN sum WHEN type=5 THEN -sum ELSE 0 END),0) FROM payment WHERE schet=? AND class='schet' AND status=1 AND pay_method<>4");$q->execute([$id]);$paid=number_format(max(0,(float)$q->fetchColumn()),2,'.','');
    $input=json_decode((string)($_POST['input']??'{}'),true);if(!is_array($input))throw new RuntimeException('Некорректные данные.');
    cbr_lock($p,$id);$locked=true;
    $data=cbr_request(['action'=>$action,'requestId'=>$id,'actor'=>(int)$user['id'],'actorRights'=>(int)$user['rights'],'paid'=>$paid,
        'operation'=>$operation,'revision'=>(string)($_POST['revision']??''),'token'=>(string)($_POST['token']??''),'search'=>(string)($_POST['search']??''),'input'=>$input]);
    $crmStatus=in_array($action,['detail','commit'],true)?cbr_sync_request($p,$id,$data):null;
    if (!empty($data['history'])) $data['history'] = cbr_history_staff($p, $data['history']);
    $defaults=['hotelId'=>(int)$request['id_obj'],'arrivalDate'=>$request['date_z'],'departureDate'=>$request['date_v']];
    if($action==='detail'){
        $q=$p->prepare('SELECT name,surname,otch,telephone,email FROM klient WHERE id=?');$q->execute([$request['turist']]);$c=$q->fetch(PDO::FETCH_ASSOC)?:[];
        $defaults['customer']=['firstName'=>$c['name']??'','lastName'=>$c['surname']??'','middleName'=>$c['otch']??'','phone'=>$c['telephone']??'','email'=>$c['email']??''];
    }
    echo json_encode(['success'=>true,'data'=>$data,'crmStatus'=>$crmStatus,'csrf'=>$csrf,'calendarCsrf'=>$calendarCsrf,'defaults'=>$defaults,'paid'=>$paid,
        'canEdit'=>cbr_scope($user,$request,true)&&(int)$request['type']===0,'canWaive'=>in_array((int)$user['rights'],[5,6],true)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {
    if(http_response_code()===200)http_response_code($e instanceof DomainException?409:400);
    $message=$e instanceof PDOException?'Не удалось проверить заявку и права доступа.':$e->getMessage();
    echo json_encode(['success'=>false,'error'=>$message],JSON_UNESCAPED_UNICODE);
} finally {if($locked)cbr_unlock($p,$id);}
