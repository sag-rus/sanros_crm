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
echo "$n checks passed\n";
