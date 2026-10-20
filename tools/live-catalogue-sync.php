<?php
// Daily delivery worker. Identical checked_at+revision is applied once; failed receipts retry without reimport.
if(PHP_SAPI!=='cli')exit(1);
$role='crm';$root=dirname(__DIR__);$dir='/var/lib/price-tonia-live';
function lcHttp($url,$token,$data=null){
    $c=curl_init($url);$headers=['Authorization: Bearer '.$token,'Accept: application/json'];
    $options=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>60,CURLOPT_HTTPHEADER=>$headers];
    if($data!==null){$options[CURLOPT_POST]=true;$options[CURLOPT_POSTFIELDS]=json_encode($data);$options[CURLOPT_HTTPHEADER][]='Content-Type: application/json';}
    curl_setopt_array($c,$options);$raw=curl_exec($c);$code=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
    if($code!==200||!is_string($raw)||strlen($raw)>2000000)throw new RuntimeException('download_failed');
    $v=json_decode($raw,true);if(!is_array($v))throw new RuntimeException('download_failed');return $v;
}
function lcSave($path,$state){$tmp=$path.'.'.getmypid().'.tmp';$json=json_encode($state,JSON_UNESCAPED_UNICODE);if(!is_string($json)||file_put_contents($tmp,$json,LOCK_EX)!==strlen($json))throw new RuntimeException('State write failed');chmod($tmp,0600);if(!rename($tmp,$path))throw new RuntimeException('State rename failed');}
require_once __DIR__.'/live-catalogue-validation.php';

try {
    $token=trim(file_get_contents($dir.'/token'));if(strlen($token)<32)throw new RuntimeException('Credential missing');
    $lock=fopen($dir.'/catalogue-sync.lock','c');if(!$lock)throw new RuntimeException('Lock unavailable');if(!flock($lock,LOCK_EX|LOCK_NB))exit(0);
    $path=$dir.'/catalogue-sync.json';$state=is_file($path)?json_decode(file_get_contents($path),true):[];if(!is_array($state))throw new RuntimeException('Invalid state');
    $manifest=lcHttp('https://price.tonia.ru/api/live-catalogues/manifest',$token);$fail=false;
    foreach($manifest['objects']as $item){
        $id=$item['object_id'];if(!is_int($id)||$id<1||!preg_match('/^[a-f0-9]{64}$/D',$item['revision']))throw new RuntimeException('Invalid manifest');
        $key=$item['revision'].'@'.$item['checked_at'];$current=$state[$id]??[];
        if(($current['key']??'')===$key&&($current['status']??'')==='success')continue;
        if(!in_array('--retry',$argv,true)&&($current['status']??'')==='error'&&($current['attempt_at']??0)>time()-1800)continue;
        try {
            if(($current['applied_key']??'')!==$key){
                $url=$role==='crm'?'https://price.tonia.ru/api/live-search/'.$id.'/catalogue':'http://10.10.10.10/CRM/core/price-live-catalogue-export.php?object_id='.$id;
                $cat=lcHttp($url,$token);lcValid($cat,$item);
                $args=[PHP_BINARY,$root.'/tools/'.($role==='crm'?'sbor-live-catalogue.php':'sbor-live-import.php'),'apply',(string)$id];if($role==='crm')$args[]='-';
                $process=proc_open(implode(' ',array_map('escapeshellarg',$args)),[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);
                if(!is_resource($process))throw new RuntimeException('import_failed');
                fwrite($pipes[0],json_encode($cat,JSON_UNESCAPED_UNICODE));fclose($pipes[0]);$output=stream_get_contents($pipes[1]);fclose($pipes[1]);stream_get_contents($pipes[2]);fclose($pipes[2]);$exit=proc_close($process);
                $receipt=json_decode($output,true);if($exit!==0||($receipt['revision']??null)!==$item['revision'])throw new RuntimeException('import_failed');
                $current=['applied_key'=>$key,'applied_at'=>gmdate('c'),'revision'=>$item['revision'],'checked_at'=>$item['checked_at'],'rooms'=>$receipt['rooms'],'rates'=>$receipt['rates']];$state[$id]=$current;lcSave($path,$state);
            }
            lcHttp('https://price.tonia.ru/api/live-catalogues/receipt',$token,['object_id'=>$id,'stage'=>$role,'status'=>'success','revision'=>$item['revision'],'checked_at'=>$item['checked_at']]);
            $current+=['key'=>$key];$current['status']='success';$current['error']=null;$current['attempt_at']=time();$state[$id]=$current;lcSave($path,$state);
            echo json_encode(['object_id'=>$id,'stage'=>$role,'status'=>'success','at'=>gmdate('c')]),"\n";
        }catch(Throwable $e){
            $fail=true;$code=in_array($e->getMessage(),['download_failed','import_failed'],true)?$e->getMessage():'import_failed';
            $current['status']='error';$current['error']=$code;$current['attempt_at']=time();$state[$id]=$current;lcSave($path,$state);
            try{lcHttp('https://price.tonia.ru/api/live-catalogues/receipt',$token,['object_id'=>$id,'stage'=>$role,'status'=>'error','revision'=>$item['revision'],'checked_at'=>$item['checked_at'],'error'=>$code]);}catch(Throwable $ignored){}
            echo json_encode(['object_id'=>$id,'stage'=>$role,'status'=>'error','error'=>$code,'at'=>gmdate('c')]),"\n";
        }
    }
    exit($fail?1:0);
}catch(Throwable $e){fwrite(STDERR,"Catalogue delivery failed; no unverified data imported\n");exit(1);}
