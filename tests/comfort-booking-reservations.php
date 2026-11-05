<?php
require __DIR__.'/../core/comfort-booking-reservations.php';
$n=0;function ok($v,$message){global $n;if(!$v)throw new RuntimeException($message);$n++;}
$request=['id'=>100,'id_user'=>7,'active'=>0];$csrf=str_repeat('a',64);
foreach([2,3,4,5,6] as $rights){$u=['id'=>7,'rights'=>$rights];ok(cbr_allowed($u,$request,'detail','',''),'owner reads');
    ok(cbr_allowed($u,$request,'prepare',$csrf,$csrf)===($rights>=3),'write rights');
    ok(!cbr_allowed($u,$request,'prepare','forged',$csrf),'CSRF');
    $other=$request;$other['id_user']=8;ok(cbr_scope($u,$other)===($rights>=4),'request ownership');}
ok(!cbr_allowed(false,$request,'detail','',''),'no anonymous access');
$request['active']=3;ok(!cbr_allowed(['id'=>7,'rights'=>5],$request,'commit',$csrf,$csrf),'deleted request immutable');
ok(!cbr_allowed(['id'=>7,'rights'=>5],$request,'invented',$csrf,$csrf),'unknown action');
echo "$n checks passed\n";
