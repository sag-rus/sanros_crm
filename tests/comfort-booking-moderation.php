<?php
require __DIR__.'/../core/comfort-booking-moderation.php';
$n=0;function ok($v,$msg){global $n;if(!$v)throw new RuntimeException($msg);$n++;}
$token=str_repeat('b',64);$user=['id'=>7,'rights'=>3];
ok(cbm_allowed($user,'list','POST','',''),'authenticated list');
ok(cbm_allowed($user,'approve','POST',$token,$token),'editor approval');
ok(cbm_allowed($user,'disable','POST',$token,$token),'editor disable');
ok(!cbm_allowed(false,'list','POST','',''),'anonymous rejected');
ok(!cbm_allowed(['rights'=>1],'list','POST','',''),'view-only user rejected');
ok(cbm_allowed(['rights'=>2],'list','POST','',''),'creation role can view same as TL menu');
ok(!cbm_allowed(['rights'=>2],'disable','POST',$token,$token),'creation role cannot disable');
ok(!cbm_allowed($user,'disable','GET',$token,$token),'GET mutation denied');
ok(!cbm_allowed($user,'disable','POST','wrong',$token),'CSRF denied');
ok(!cbm_allowed($user,'disable','POST','',''),'empty CSRF denied');
ok(!cbm_allowed($user,'enable','POST',$token,$token),'unsupported reactivation denied');
ok(cbm_allowed($user,'count','POST','',''),'authenticated pending counter');
ok(!cbm_allowed(false,'count','POST','',''),'anonymous counter denied');
ok(cbm_allowed(['rights'=>2],'calendar-ticket','POST',$token,$token),'staff can issue calendar ticket');
ok(!cbm_allowed(['rights'=>2],'calendar-ticket','POST','bad',$token),'calendar ticket CSRF required');
ok(!cbm_allowed(false,'calendar-ticket','POST',$token,$token),'calendar ticket requires CRM login');
ok(!cbm_allowed(['rights'=>2],'calendar-ticket','GET',$token,$token),'calendar ticket POST only');
$secret=str_repeat('a',64);$ticket=cbm_calendar_ticket(123,7,'Test UA',$secret,1000);
list($payload,$signature)=explode('.',$ticket['ticket']);$claims=json_decode(base64_decode(strtr($payload,'-_','+/')),true);
ok($claims['object']===123 && $claims['actor']===7 && $claims['exp']===1060 && $claims['iat']===1000 && $claims['aud']==='cb-calendar','ticket binds object actor purpose and 60 second expiry');
ok($claims['ua']===hash('sha256','Test UA') && strlen($claims['jti'])===64,'browser binding and unpredictable nonce');
ok(hash_equals(hash_hmac('sha256',$payload,hash_hmac('sha256','comfort-booking-calendar-v1',$secret,true)),$signature),'domain separated signature');
ok(cbm_calendar_ticket(123,7,'Test UA',$secret,1000)['ticket']!==$ticket['ticket'],'each click gets unique ticket');
echo "$n checks passed\n";

foreach(['editorial-read','editorial-save'] as $action){
 ok(cbm_allowed($user,$action,'POST',$token,$token),'editor can access texts');
 ok(!cbm_allowed(['rights'=>2],$action,'POST',$token,$token),'reader cannot edit texts');
 ok(!cbm_allowed($user,$action,'POST','wrong',$token),'text editor requires CSRF');
 ok(!cbm_allowed(false,$action,'POST',$token,$token),'anonymous text access denied');
}
echo "$n checks including editorial access passed\n";

foreach(['bulk-approve','bulk-disable'] as $action){
 ok(cbm_allowed($user,$action,'POST',$token,$token),'bulk action allowed for moderator');
 ok(!cbm_allowed(['rights'=>2],$action,'POST',$token,$token),'reader cannot bulk moderate');
 ok(!cbm_allowed($user,$action,'POST','wrong',$token),'bulk action needs CSRF');
 ok(!cbm_allowed($user,$action,'GET',$token,$token),'bulk action requires POST');
}
echo "$n checks including bulk permissions passed\n";
