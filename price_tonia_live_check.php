<?php
require_once __DIR__.'/core/price_tonia_live.php';
session_start();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try{
    $session=isset($_COOKIE['session'])?(string)$_COOKIE['session']:'';
    $user=ptl_query('SELECT u.id FROM session s JOIN users u ON u.login=s.login AND u.password=s.password WHERE s.id_session=? AND u.dostup=1 AND u.rights>0',[$session])->fetchColumn();
    if(!$user){http_response_code(403);throw new RuntimeException('Требуется вход в СРМ.');}
    if($_SERVER['REQUEST_METHOD']!=='POST' || empty($_SESSION['ptl_csrf']) || !hash_equals($_SESSION['ptl_csrf'],(string)($_POST['csrf']??''))){
        http_response_code(403);throw new RuntimeException('Обновите карточку заявки и повторите действие.');
    }
    session_write_close();$id=(int)($_POST['booking_id']??0);$action=$_POST['action']??'';
    if(!$id || !in_array($action,['start','status'],true))throw new RuntimeException('Некорректный запрос.');
    if($action==='start')ptl_start($id,$user);
    echo json_encode(ptl_state($id,true),JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){if(http_response_code()===200)http_response_code(400);echo json_encode(['error'=>$e instanceof PDOException?'Не удалось получить данные проверки.':$e->getMessage()],JSON_UNESCAPED_UNICODE);}
