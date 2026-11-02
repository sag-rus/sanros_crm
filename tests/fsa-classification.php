<?php
require __DIR__.'/../core/fsa-classification.php';
$n=0;
function check($ok,$name){global $n;if(!$ok)throw new RuntimeException($name);$n++;}
$o=['id'=>60,'name'=>'Янган-Тау','accr_id'=>'body','accr_data'=>'old'];
$main=['fullName'=>'Акционерное общество Санаторий "Янган-Тау"','shortName'=>'АО Санаторий "Янган-Тау"',
    'ownerName'=>'Акционерное общество Санаторий "Янган-Тау"','ownerInn'=>'123','region'=>['id'=>2],
    'registerRecord'=>'СTEST','status'=>['name'=>'Действует','endDate'=>'2028-08-15']];
$property=['id'=>1,'ext_id'=>'whole','id_obj'=>60,'main'=>$main];
$body=$property;$body['ext_id']='body';$body['main']['fullName']='Санаторий «Янган-Тау» Корпус 14';
check(fsaSelect($o,[$body,$property],$main)['ext_id']==='whole','property selected regardless of last building');
check(fsaSelect($o,[$property,$body],$main)['ext_id']==='whole','order independent');
check(fsaSelect($o,[$body],$main)===null,'building never published');
foreach(['Корпус №1','корп. 3','Литер А','Коттедж 8, 9','Спальное здание 2','Санаторий Янган-Тау апартаменты','Санаторий Янган-Тау виллы'] as $label){$m=$main;$m['fullName']=$label;check(fsaIsBuilding($m),$label);}
$other=$property;$other['ext_id']='other';
check(fsaSelect($o,[$property,$other],$main)===null,'two active records need review');
$other['main']['status']['name']='Прекращен';
check(fsaSelect($o,[$property,$other],$main)['ext_id']==='whole','active supersedes terminated duplicate');
$other['main']['status']['name']='Приостановлен';
check(fsaSelect($o,[$property,$other],$main)===null,'suspended is not assumed obsolete');
$bad=$property;$bad['main']['ownerInn']='999';check(fsaSelect($o,[$bad],$main)===null,'different owner');
$bad=$property;$bad['main']['region']['id']=3;check(fsaSelect($o,[$bad],$main)===null,'different region');
$bad=$property;$bad['id_obj']=99;check(fsaSelect($o,[$bad],$main)===null,'other CRM object');
$bel=['name'=>'Белокуриха'];$siberia=['fullName'=>'Санаторий Сибирь АО Курорт Белокуриха','ownerName'=>'АО Курорт Белокуриха'];
check(!fsaNameMatches($bel,$siberia),'owner name is not resort name');
$kurgazak=$main;$kurgazak['fullName']='Гостинично-сервисный комплекс «Кургазак» АО Санаторий «Янган-Тау»';$kurgazak['shortName']=$kurgazak['fullName'];
check(!fsaNameMatches($o,$kurgazak),'legal owner abbreviations do not match another property');
check(!fsaNameMatches(['name'=>'Янга'],$main),'partial word not matched');
$projection=fsaProjection($property,'2026-11-02T12:00:00+00:00');check($projection['scope']==='object','verified scope');
$property['main']['status']=['name'=>'Прекращен'];$p=fsaProjection($property,'now');check(!isset($p['endDate']) && $p['status']==='Прекращен','missing date clears old expiration');
$id='00f70eca-8029-4f68-8977-438ebedd66ad';$d=['resortId'=>$id,'hotel'=>['main'=>$main]];
check(fsaValidate($d,$id)===$main,'valid response');
foreach(['identity','status','date','json','name','record'] as $case){
 $b=$d;switch($case){case 'identity':$b['resortId']='wrong';break;case 'status':unset($b['hotel']['main']['status']);break;case 'date':$b['hotel']['main']['status']['endDate']='2028-02-31';break;case 'json':$b=null;break;case 'name':$b['hotel']['main']['fullName']='';break;case 'record':$b['hotel']['main']['registerRecord']='';break;}
 try{fsaValidate($b,$id);throw new LogicException('accepted '.$case);}catch(RuntimeException $e){check(true,$case);}
}
class FsaTestDb {
 public $writes=[];public $affected=1;public $owner=60;
 function getOne(...$args){return 1;}
 function getRow(...$args){return ['id_obj'=>$this->owner];}
 function query(...$args){$this->writes[]=$args;return true;}
 function getAll(...$args){return [];}
 function affectedRows(){return $this->affected;}
}
$db=new FsaTestDb();$row=$property+['data'=>'{}','checkedAt'=>'2026-11-02T12:00:00+00:00'];
fsaSaveCache($db,$row);check(strpos($db->writes[0][0],'NOW()')===false && in_array('2026-11-02 12:00:00',$db->writes[0],true),'cache reuse does not slide successful check timestamp');
$dir=sys_get_temp_dir().'/fsa-test-'.bin2hex(random_bytes(5));mkdir($dir);
try{
 $db=new FsaTestDb();fsaPublish($db,$o,$property,$p,$dir);
 check(strpos($db->writes[0][0],'synchronized=0')!==false,'publication enqueued');
 check(strpos($db->writes[0][0],'AND accr_id=?s AND accr_data=?s')!==false,'compare and swap');
 check(count(glob($dir.'/*.json'))===1,'backup before write');
 $unchanged=$o;$unchanged['accr_id']=$property['ext_id'];$unchanged['accr_data']=json_encode($p,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
 $db=new FsaTestDb();check(fsaPublish($db,$unchanged,$property,$p,$dir)===false && count($db->writes)===0,'unchanged response does not republish');
 $db=new FsaTestDb();$db->owner=99;
 try{fsaPublish($db,$o,$property,$p,$dir);throw new LogicException('stole another claim');}catch(RuntimeException $e){check(count($db->writes)===0,'recheck source claim under lock');}

 $db=new FsaTestDb();$db->affected=0;
 try{fsaPublish($db,$o,$property,$p,$dir);throw new LogicException('overwrote concurrent change');}catch(RuntimeException $e){check(count($db->writes)===1,'no link changes after concurrent modification');}
}finally{foreach(glob($dir.'/*.json') as $f)unlink($f);rmdir($dir);}
echo $n." classification checks passed\n";
