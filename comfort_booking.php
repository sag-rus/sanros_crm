<?php
require_once __DIR__.'/core/comfort-booking-moderation.php';
session_start();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try {
    if((int)($_SERVER['CONTENT_LENGTH']??0)>524288)throw new RuntimeException('Слишком большой запрос.');
    $user=cbm_user();$action=(string)($_POST['action']??'');
    if(!cbm_allowed($user,$action,$_SERVER['REQUEST_METHOD']??'',$_POST['csrf']??'',$_SESSION['cbm_csrf']??'')) {
        http_response_code(403);throw new RuntimeException('Нет доступа или истекла сессия. Обновите страницу.');
    }
    $csrf=cbm_session_csrf();
    if($action==='calendar-ticket'){
        $issued=array_values(array_filter($_SESSION['cb_calendar_issued']??[],function($v){return $v>time()-60;}));
        if(count($issued)>=20)throw new RuntimeException('Слишком много переходов. Повторите через минуту.');
        $issued[]=time();$_SESSION['cb_calendar_issued']=$issued;
    }
    session_write_close();
    if($action==='list') cbm_reconcile_replacements();
    if($action==='list') $data=cbm_request(['action'=>'list','page'=>(int)($_POST['page']??1),'perPage'=>(int)($_POST['perPage']??20),'search'=>(string)($_POST['search']??''),'status'=>(string)($_POST['status']??''),'typeId'=>(string)($_POST['typeId']??'0'),'directionId'=>(string)($_POST['directionId']??'0')]);
    elseif($action==='calendar-ticket') $data=cbm_calendar_ticket($_POST['id']??0,$user['id'],(string)($_SERVER['HTTP_USER_AGENT']??''));
    elseif(in_array($action,['editorial-read','editorial-save'],true)) {
        $fields=json_decode((string)($_POST['fields']??'{}'),true);if(!is_array($fields))throw new RuntimeException('Некорректные поля.');
        $data=cbm_request(['action'=>$action,'id'=>(int)($_POST['id']??0),'actor'=>(int)$user['id'],'actorRights'=>(int)$user['rights'],'revision'=>(string)($_POST['revision']??''),'fields'=>$fields]);
    }
    elseif($action==='server-metrics') $data=cbm_request(['action'=>$action,'hours'=>(int)($_POST['hours']??6)]);
    elseif(in_array($action,['count','analytics'],true)) $data=cbm_request(['action'=>$action]);
    else $data=cbm_request(['action'=>$action,'id'=>(int)($_POST['id']??0),'expected'=>(string)($_POST['expected']??''),'actor'=>(int)$user['id'],'replaceId'=>(int)($_POST['replaceId']??0),'replaceName'=>cbm_replacement_name($_POST['replaceId']??0)]);
    if($action==='approve' && !empty($_POST['replaceId']))cbm_reconcile_replacements();
    echo json_encode(['success'=>true,'data'=>$data,'csrf'=>$csrf,'canModerate'=>(int)$user['rights']>=3],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {
    if(http_response_code()===200) http_response_code($e instanceof DomainException?409:400);
    echo json_encode(['success'=>false,'error'=>$e instanceof PDOException?'Не удалось проверить доступ.':$e->getMessage()],JSON_UNESCAPED_UNICODE);
}
