<?php
// Dedicated site-to-CRM intake over the VPN. Never accepts a browser session as authorization.
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try {
    $raw=file_get_contents('php://input');$time=$_SERVER['HTTP_X_CB_TIME']??'';$nonce=$_SERVER['HTTP_X_CB_NONCE']??'';
    $secret=trim((string)@file_get_contents('/etc/sanata/comfort-booking-moderation-token'));
    require_once __DIR__.'/core/comfort-booking-site-auth.php';
    cb_site_verify($_SERVER,$raw,$secret,time());
    $data=json_decode($raw,true);if(!is_array($data)||!preg_match('/^[a-f0-9]{64}$/D',(string)($data['cb_submission_key']??'')))throw new RuntimeException('Invalid request');
    require_once __DIR__.'/core/comfort-booking-reservations.php';$p=cbr_db();
    $p->exec('DELETE FROM cb_site_nonce WHERE expires<UNIX_TIMESTAMP()');
    $q=$p->prepare('INSERT INTO cb_site_nonce (nonce,expires) VALUES (?,?)');$q->execute([$nonce,time()+120]);
    if(!empty($data['cb_probe'])){echo json_encode(['success'=>1,'probe'=>true]);exit;}
    $q=$p->prepare('SELECT GET_LOCK(?,15)');$q->execute(['cbs_'.substr($data['cb_submission_key'],0,60)]);if((int)$q->fetchColumn()!==1)throw new RuntimeException('Intake busy');
    $q=$p->prepare('SELECT id,cb_site_complete FROM reckoning WHERE cb_submission_key=?');$q->execute([$data['cb_submission_key']]);$existing=$q->fetch(PDO::FETCH_ASSOC);
    if($existing){
        if(!(int)$existing['cb_site_complete']){http_response_code(409);echo json_encode(['success'=>0,'error'=>'Intake incomplete; administrator reconciliation required']);exit;}
        echo json_encode(['success'=>1,'requestId'=>(int)$existing['id']]);exit;
    }
    define('CB_SITE_INTAKE',true);$_POST=['data'=>$data];
    require __DIR__.'/kostyl_booking.php';
} catch(Throwable $e){http_response_code(403);echo json_encode(['success'=>0,'error'=>'Intake unavailable']);}
