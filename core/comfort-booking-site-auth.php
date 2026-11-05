<?php
function cb_site_verify(array $server,string $raw,string $secret,int $now): void {
    $time=$server['HTTP_X_CB_TIME']??'';$nonce=$server['HTTP_X_CB_NONCE']??'';
    if(($server['REMOTE_ADDR']??'')!=='10.8.0.150'||($server['REQUEST_METHOD']??'')!=='POST'||strlen($raw)>65536||strlen($secret)!==64
        ||!ctype_digit($time)||abs($now-(int)$time)>60||!preg_match('/^[a-f0-9]{64}$/D',$nonce)
        ||!hash_equals(hash_hmac('sha256',$time."\n".$nonce."\n".hash('sha256',$raw),hash_hmac('sha256','cb-website-intake-v1',$secret)),(string)($server['HTTP_X_CB_SIGNATURE']??'')))throw new RuntimeException('Access denied');
 }
