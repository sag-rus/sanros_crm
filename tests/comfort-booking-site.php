<?php
require __DIR__.'/../core/comfort-booking-site-auth.php';
$n=0;function ok($v,$msg){global $n;if(!$v)throw new RuntimeException($msg);$n++;}
function reject($s,$b,$k,$t){try{cb_site_verify($s,$b,$k,$t);}catch(Throwable $e){return true;}return false;}
$t=1804413600;$key=str_repeat('a',64);$body='{"cb_probe":true}';$nonce=str_repeat('b',64);
$s=['REMOTE_ADDR'=>'10.8.0.150','REQUEST_METHOD'=>'POST','HTTP_X_CB_TIME'=>(string)$t,'HTTP_X_CB_NONCE'=>$nonce];
$s['HTTP_X_CB_SIGNATURE']=hash_hmac('sha256',$t."\n".$nonce."\n".hash('sha256',$body),hash_hmac('sha256','cb-website-intake-v1',$key));
cb_site_verify($s,$body,$key,$t);ok(true,'valid signed VPN request');
foreach(['REMOTE_ADDR'=>'192.0.2.1','REQUEST_METHOD'=>'GET','HTTP_X_CB_NONCE'=>'short','HTTP_X_CB_SIGNATURE'=>'bad'] as $k=>$v){$bad=$s;$bad[$k]=$v;ok(reject($bad,$body,$key,$t),'reject '.$k);}
ok(reject($s,$body.'x',$key,$t),'body tamper');ok(reject($s,$body,$key,$t+61),'expired');ok(reject($s,$body,$key,$t-61),'future');
$bad=$s;$bad['HTTP_X_CB_SIGNATURE']=hash_hmac('sha256',$t."\n".$nonce."\n".hash('sha256',$body),$key);ok(reject($bad,$body,$key,$t),'wrong key purpose');
echo "Site intake authentication checks: $n\n";
