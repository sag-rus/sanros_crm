<?php
require_once __DIR__.'/comfort-booking-reservations.php';
require_once __DIR__.'/comfort-booking-objects.php';

function cbr_is_request($connect, $id) {
    $r=$connect->getRow('SELECT id,id_obj,cb_submission_key FROM reckoning WHERE id=?i',(int)$id);
    if(!$r)throw new RuntimeException('Заявка не найдена.');
    if(!empty($r['cb_submission_key']))return true;
    $objects=cb_objects([$r],$failed);
    if($failed)throw new RuntimeException('Не удалось проверить канал объекта. Повторите позже.');
    return isset($objects[(int)$id]);
}
function cbr_documents_ready(array $data) {
    $b=$data['booking']??[];$c=$data['confirmation']??[];
    return in_array($b['status']??'',['new','modified'],true)
        && ($data['delivery']??'')==='confirmed' && !empty($c['externalNumber']) && empty($c['error'])
        && !empty($b['echoToken']) && hash_equals((string)$b['echoToken'],(string)($c['echoToken']??''))
        && ($c['status']??'')===$b['status'];
}
function cbr_lock(PDO $p,$id) {
    if($p->getAttribute(PDO::ATTR_DRIVER_NAME)!=='mysql')return;
    $q=$p->prepare('SELECT GET_LOCK(?,10)');$q->execute(['cbr-request-'.(int)$id]);
    if((int)$q->fetchColumn()!==1)throw new RuntimeException('Данные брони обновляются. Повторите действие.');
}
function cbr_unlock(PDO $p,$id) {
    if($p->getAttribute(PDO::ATTR_DRIVER_NAME)==='mysql'){$q=$p->prepare('SELECT RELEASE_LOCK(?)');$q->execute(['cbr-request-'.(int)$id]);}
}
/** The caller holds the request lock from BEFORE fetching the authenticated website response. */
function cbr_sync_request(PDO $p,$id,array $data) {
    $b=$data['booking']??null;if(!$b)return null;
    if(($b['status']??'')==='cancelled')return cbr_sync_cancelled($p,$id,$data);
    if(!in_array($b['status']??'',['new','modified'],true))throw new RuntimeException('Неизвестный статус брони.');
    $q=$p->prepare('SELECT * FROM reckoning WHERE id=?');$q->execute([$id]);$r=$q->fetch(PDO::FETCH_ASSOC);
    if(!$r||(string)$r['id_obj']!==(string)($b['hotelId']??''))throw new RuntimeException('Объект заявки не совпадает с бронью.');
    if((int)$r['active']===3)throw new RuntimeException('Заявка удалена. Требуется проверка.');
    $stay=$b['roomStays'][0]??[];$selection=$data['selection']??[];
    $arrival=DateTimeImmutable::createFromFormat('!Y-m-d',$b['arrivalDate']??'');
    $departure=DateTimeImmutable::createFromFormat('!Y-m-d',$b['departureDate']??'');
    $guests=$stay['guests']??[];$count=(int)($stay['adults']??0)+(int)($stay['children']??0);
    $price=$stay['total']['amountAfterTaxes']??null;
    if(count($b['roomStays']??[])!==1||!$arrival||!$departure||$arrival->format('Y-m-d')!==$b['arrivalDate']||$departure->format('Y-m-d')!==$b['departureDate']||$departure<=$arrival||$count<1||count($guests)!==$count||!is_numeric($price)||$price<0||$price>999999.99||empty($selection['roomId'])||empty($selection['rateId']))throw new RuntimeException('Некорректные данные брони; документы заблокированы.');
    foreach($guests as $g){
        if(empty($g['lastName'])||empty($g['firstName']))throw new RuntimeException('Не заполнены данные гостя.');
        foreach(['lastName','firstName','middleName'] as $field){
            $value=$g[$field]??'';
            if(!is_string($value)||preg_match('/[<>\x00-\x1f]/',$value))throw new RuntimeException('Недопустимые символы в данных гостя.');
        }
    }
    $q=$p->prepare('SELECT * FROM position_reck WHERE schet=? ORDER BY id');$q->execute([$id]);$positions=$q->fetchAll(PDO::FETCH_ASSOC);
    // A CB request represents exactly one roomStay. Never erase additional/manual services silently.
    if(count($positions)!==1||!empty($positions[0]['id_service']))throw new RuntimeException('Состав позиций заявки требует проверки перед синхронизацией.');
    $position=$positions[0];$guestIds=array_values(array_filter(array_map('intval',explode(',',(string)$r['rest']))));
    $people=[];foreach($guestIds as $gid){$q=$p->prepare('SELECT * FROM klient WHERE id=?');$q->execute([$gid]);$people[$gid]=$q->fetch(PDO::FETCH_ASSOC);}
    $used=[];$rest=[];$changed=false;
    foreach($guests as $i=>$g){
        $names=[trim($g['lastName']),trim($g['firstName']),trim($g['middleName']??'')];$found=0;
        foreach($people as $gid=>$person)if($person&&!isset($used[$gid])&&[$person['surname'],$person['name'],(string)$person['otch']]===$names){$found=$gid;break;}
        if(!$found){
            // Recover an interrupted MyISAM sync without creating duplicate guests; never rename a shared customer.
            $marker=json_encode(['cb_request'=>(int)$id,'guest_key'=>hash('sha256',json_encode([$i,$names,!empty($g['isChild']),$g['age']??null]))]);
            $q=$p->prepare('SELECT id FROM klient WHERE original_data=? LIMIT 1');$q->execute([$marker]);$found=(int)$q->fetchColumn();
            if(!$found){$q=$p->prepare('INSERT INTO klient (surname,name,otch,date_reg,original_data) VALUES (?,?,?,?,?)');$q->execute([$names[0],$names[1],$names[2],date('Y-m-d'),$marker]);$found=(int)$p->lastInsertId();}
        }
        $used[$found]=true;$rest[]=$found;
    }
    $target=['date_z'=>$b['arrivalDate'],'date_v'=>$b['departureDate'],'sum'=>number_format((float)$price,2,'.',''),'number_turist'=>$count,'rest'=>implode(',',$rest)];
    $pt=['id_room'=>(int)$selection['roomId'],'ratePlan'=>(string)$selection['rateId'],'sum'=>$target['sum'],'number'=>1,'type'=>3,'days'=>(int)$arrival->diff($departure)->days,'date_z'=>$target['date_z'],'add_one_day'=>1];
    foreach($target as $k=>$v)if((string)$r[$k]!== (string)$v)$changed=true;
    foreach($pt as $k=>$v)if((string)$position[$k]!== (string)$v)$changed=true;
    if(!$changed)return ['status'=>(int)$r['status'],'changed'=>false];
    // Durable before-image first: legacy CRM tables are MyISAM (no transactional rollback).
    $actor=0;foreach($data['history']??[] as $h)if(in_array($h['action']??'',['create','modify'],true)){$actor=(int)($h['actor_id']??0);break;}
    $note='Синхронизация брони Comfort Booking №'.$b['number'].'. До изменения: '.json_encode(['request'=>array_intersect_key($r,$target),'position'=>$position],JSON_UNESCAPED_UNICODE);
    $now=new DateTimeImmutable('now',new DateTimeZone('Europe/Moscow'));
    $q=$p->prepare('INSERT INTO history_schet (date,time,id_schet,id_user,new_status,new_status_san,note) VALUES (?,?,?,?,?,?,?)');$q->execute([$now->format('Y-m-d'),$now->format('H:i:s'),$id,$actor,$r['status'],$r['status_san'],$note]);
    $q=$p->prepare('UPDATE position_reck SET id_room=?,ratePlan=?,sum=?,number=?,type=?,days=?,date_z=?,add_one_day=? WHERE id=? AND schet=?');$q->execute(array_merge(array_values($pt),[$position['id'],$id]));
    $q=$p->prepare('UPDATE reckoning SET date_z=?,date_v=?,sum=?,number_turist=?,rest=? WHERE id=?');$q->execute(array_merge(array_values($target),[$id]));
    return ['status'=>(int)$r['status'],'changed'=>true];
}
function cbr_document_data($connect,$id) {
    global $session_login,$id_rights;
    static $checked=[];
    if(array_key_exists((int)$id,$checked))return $checked[(int)$id];
    if(!cbr_is_request($connect,$id))return null;
    $p=cbr_db();$q=$p->prepare('SELECT id,id_user,active FROM reckoning WHERE id=?');$q->execute([$id]);$r=$q->fetch(PDO::FETCH_ASSOC);
    if(!cbr_scope(['id'=>(int)$session_login,'rights'=>(int)$id_rights],$r))throw new RuntimeException('Нет доступа к заявке.');
    cbr_lock($p,$id);
    // Keep the lock through document rendering, preventing a concurrent CRM modification.
    register_shutdown_function(function()use($p,$id){cbr_unlock($p,$id);});
    $data=cbr_request(['action'=>'detail','requestId'=>(int)$id,'actor'=>(int)$session_login,'actorRights'=>(int)$id_rights]);
    if(!cbr_documents_ready($data))throw new RuntimeException('Документы доступны после подтверждения текущего создания или изменения брони TravelLine.');
    cbr_sync_request($p,$id,$data);
    $checked[(int)$id]=$data;
    return $data;
}
function cbr_document_guard($connect,$id,&$data=null) {
    try{$data=cbr_document_data($connect,$id);return true;}
    catch(Throwable $e){http_response_code(409);echo '<p class="text-danger">'.htmlspecialchars($e instanceof PDOException?'Не удалось синхронизировать заявку. Повторите позже.':$e->getMessage(),ENT_QUOTES,'UTF-8').'</p>';return false;}
}
function cbr_ordinary_action_allowed($connect,$id) {
    try{if(!cbr_is_request($connect,$id))return true;$message='Отмена доступна только в блоке «Бронь Comfort Booking». Обычное удаление запрещено.';}
    catch(Throwable $e){$message='Не удалось проверить канал объекта. Действие не выполнено.';}
    http_response_code(409);echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');return false;
}
