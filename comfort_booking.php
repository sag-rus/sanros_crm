<?php
require_once __DIR__.'/core/comfort-booking-moderation.php';
session_start();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try {
    $user=cbm_user();$action=(string)($_POST['action']??'');
    if(!cbm_allowed($user,$action,$_SERVER['REQUEST_METHOD']??'',$_POST['csrf']??'',$_SESSION['cbm_csrf']??'')) {
        http_response_code(403);throw new RuntimeException('Нет доступа или истекла сессия. Обновите страницу.');
    }
    if(empty($_SESSION['cbm_csrf'])) $_SESSION['cbm_csrf']=bin2hex(random_bytes(32));
    $csrf=$_SESSION['cbm_csrf'];session_write_close();
    if($action==='list') $data=cbm_request(['action'=>'list','page'=>(int)($_POST['page']??1),'perPage'=>(int)($_POST['perPage']??20),'search'=>(string)($_POST['search']??''),'status'=>(string)($_POST['status']??''),'directionId'=>(string)($_POST['directionId']??'0')]);
    elseif($action==='count') $data=cbm_request(['action'=>'count']);
    else $data=cbm_request(['action'=>$action,'id'=>(int)($_POST['id']??0),'expected'=>(string)($_POST['expected']??''),'actor'=>(int)$user['id']]);
    echo json_encode(['success'=>true,'data'=>$data,'csrf'=>$csrf,'canModerate'=>(int)$user['rights']>=3],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {
    if(http_response_code()===200) http_response_code($e instanceof DomainException?409:400);
    echo json_encode(['success'=>false,'error'=>$e instanceof PDOException?'Не удалось проверить доступ.':$e->getMessage()],JSON_UNESCAPED_UNICODE);
}
