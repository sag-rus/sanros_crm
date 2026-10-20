<?php
// Read-only catalogue transport for the website. No customer or booking data.
if(PHP_SAPI==='cli'){http_response_code(404);exit;}
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try {
    $file='/var/lib/price-tonia-live/token';$expected=is_file($file)?trim(file_get_contents($file)):'';
    $header=$_SERVER['HTTP_AUTHORIZATION']??($_SERVER['REDIRECT_HTTP_AUTHORIZATION']??'');
    if($expected===''||!hash_equals('Bearer '.$expected,$header)){http_response_code(403);echo '{"error":"forbidden"}';exit;}
    $id=filter_input(INPUT_GET,'object_id',FILTER_VALIDATE_INT);if(!$id)throw new RuntimeException('Invalid ID');
    $pdo=(function(){$argv=[__FILE__,'crm',dirname(__DIR__)];require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';return $pdo;})();
    $s=$pdo->prepare('SELECT payload_json FROM price_live_catalogue WHERE object_id=?');$s->execute([$id]);$raw=$s->fetchColumn();
    if(!$raw){http_response_code(404);echo '{"error":"not_ready"}';exit;}
    echo $raw;
}catch(Throwable $e){http_response_code(503);echo '{"error":"catalogue_unavailable"}';}
