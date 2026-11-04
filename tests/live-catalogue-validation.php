<?php
require __DIR__.'/../tools/live-catalogue-validation.php';
$item=['object_id'=>47,'provider_id'=>'4345','revision'=>str_repeat('a',64),'checked_at'=>gmdate('c')];
$cat=$item+['complete'=>true,'rooms'=>[['code'=>'12','name'=>'Номер','description'=>'Описание','main_places'=>2,'extra_places'=>0,'photos'=>['https://price.tonia.ru/uploads/live/47/'.str_repeat('b',64).'.jpg']]],'rates'=>[['code'=>'20','name'=>'Тариф','description'=>'Описание']]];
lcValid($cat,$item);$tests=1;
foreach(['identity','provider','revision','time','stale','future','incomplete','rooms','rates','duplicate','photo','capacity','description']as $case){
 $bad=$cat;
 switch($case){
 case 'identity':$bad['object_id']=13;break;
 case 'provider':$bad['provider_id']='7733';break;
 case 'revision':$bad['revision']=str_repeat('c',64);break;
 case 'time':$bad['checked_at']='2020-01-01';break;
 case 'stale':$bad['checked_at']=gmdate('c',time()-200000);break;
 case 'future':$bad['checked_at']=gmdate('c',time()+1000);break;
 case 'incomplete':$bad['complete']=false;break;
 case 'rooms':$bad['rooms']=[];break;
 case 'rates':$bad['rates']=[];break;
 case 'duplicate':$bad['rooms'][]=$bad['rooms'][0];break;
 case 'photo':$bad['rooms'][0]['photos']=['https://example.com/test.jpg'];break;
 case 'capacity':$bad['rooms'][0]['main_places']=null;break;
 case 'description':$bad['rates'][0]['description']=[];break;
 }
 $manifest=$item;if(in_array($case,['stale','future']))$manifest['checked_at']=$bad['checked_at'];
 try{lcValid($bad,$manifest);throw new LogicException('Accepted invalid '.$case);}catch(RuntimeException $e){$tests++;}
}
echo $tests," catalogue validation checks passed\n";
