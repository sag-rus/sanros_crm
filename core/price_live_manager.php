<?php
function ptl_live_providers(){return [47=>'4345',207=>'7733',13=>'23294',4=>'6811',25=>'18786',19=>'6263',29=>'6482',662=>'7822',10=>'17784',37=>'8830',50=>'7826',482=>'43592',1005=>'22525',16=>'6894',58=>'6538',23=>'6893',471=>'651',82=>'9712',56=>'1084',70=>'65227',24=>'23664',6=>'18577',39=>'6906',53=>'6281',410=>'47801',507=>'15955',55=>'15957',83=>'17814',141=>'13121',558=>'15955',84=>'8016',534=>'18602',859=>'1222',492=>'22850',470=>'20191',51=>'48479',52=>'381',582=>'14219',821=>'6459',177=>'67567',117=>'56945',120=>'64627',253=>'5673',132=>'23634',146=>'64623',170=>'493',187=>'61292',198=>'1628',258=>'15177',225=>'1269',233=>'26544',248=>'7256',250=>'1630',260=>'7032',263=>'12563',270=>'1712',274=>'5914',275=>'1308',281=>'57411',295=>'63714'];}
function ptl_live_payload(array $r,array $rows,array $cat){
    $id=(int)$r['id_obj'];$providers=ptl_live_providers();
    if(!isset($providers[$id])||($cat['object_id']??null)!==$id||($cat['provider_id']??null)!==$providers[$id]||count($rows)!==1)throw new RuntimeException('Для проверки нужен один номер из каталога PRICE.');
    $p=$rows[0];$a=DateTimeImmutable::createFromFormat('!Y-m-d',$r['date_z']);$d=DateTimeImmutable::createFromFormat('!Y-m-d',$r['date_v']);
    if(!$a||!$d||$a->format('Y-m-d')!==$r['date_z']||$d->format('Y-m-d')!==$r['date_v'])throw new RuntimeException('Проверьте даты заявки.');
    $n=(int)$a->diff($d)->format('%r%a');
    if($n<1||$n>30||$r['date_z']<date('Y-m-d')||(int)$r['number_turist']<1||(int)$r['number_turist']>3||!empty($r['children_rest'])||(int)$p['days']!==$n||$p['date_z']!==$r['date_z']||(int)$p['number']!==1||(int)$p['type']!==3||(int)$p['add_one_day']!==1||!preg_match('/^[1-9][0-9]{0,9}\.\d{2}$/D',(string)$p['sum']))throw new RuntimeException('Для проверки нужен полный заезд до 30 ночей с ценой за весь период и 1–3 взрослыми.');
    $room=null;$rate=null;foreach($cat['rooms']as $v)if((int)$v['crm_id']===(int)$p['id_room']){if($room)throw new RuntimeException('Неоднозначный номер.');$room=$v;}
    foreach($cat['rates']as $v)if((int)$v['crm_id']===(int)$p['ratePlan']){if($rate)throw new RuntimeException('Неоднозначный тариф.');$rate=$v;}
    if(!$room||!$rate)throw new RuntimeException('Номер или тариф отсутствует в актуальном каталоге PRICE.');
    return ['booking_id'=>(int)$r['id'],'source'=>'price_tonia_live','crm_object_id'=>$id,'provider_id'=>$providers[$id],'room_key'=>$room['code'],'rate_key'=>$rate['code'],'arrival'=>$r['date_z'],'departure'=>$r['date_v'],'adults'=>(int)$r['number_turist'],'quoted_total'=>(string)$p['sum'],'room_name'=>$room['name'],'rate_name'=>$rate['name']];
}
function ptl_live_result(array $v,array $response){
    if(($response['status']??'')==='pending')return ['status'=>'running','result'=>null];
    if(($response['status']??'')==='error')return ['status'=>'error','result'=>['message'=>'Не удалось получить актуальные цены. Повторите проверку позже.']];
    if(($response['object_id']??null)!==$v['crm_object_id']||($response['provider_id']??null)!==$v['provider_id']||($response['expires_at']??0)<=time())throw new RuntimeException('Неверный или устаревший ответ PRICE.');
    foreach(['arrival'=>$v['arrival'],'departure'=>$v['departure'],'adults'=>$v['adults'],'children'=>[]]as $key=>$value)if(($response['criteria'][$key]??null)!==$value)throw new RuntimeException('Параметры ответа PRICE не совпадают с заявкой.');
    $checked=$response['checked_at']??gmdate('c');
    if(($response['status']??'')==='unavailable')return ['status'=>'unavailable','result'=>['message'=>'На выбранные даты свободных номеров нет.','checked_at'=>$checked]];
    if(($response['status']??'')!=='available')throw new RuntimeException('Неизвестный статус PRICE.');
    $matches=[];foreach($response['offers']??[]as $o)if(($o['room_code']??null)===$v['room_key']&&($o['rate_code']??null)===$v['rate_key']&&($o['currency']??null)==='RUB'&&preg_match('/^[1-9][0-9]{0,9}\.\d{2}$/D',$o['total']??''))$matches[$o['total']]=true;
    if(count($matches)>1)throw new RuntimeException('Найдены неоднозначные цены.');
    if(!$matches)return ['status'=>!empty($response['partial'])?'error':'unavailable','result'=>['message'=>!empty($response['partial'])?'Не удалось подтвердить выбранный номер и тариф. Повторите проверку позже.':'Выбранный номер по этому тарифу сейчас недоступен.','checked_at'=>$checked]];
    return ['status'=>'available','result'=>['total'=>(string)array_keys($matches)[0],'currency'=>'RUB','checked_at'=>$checked]];
}
