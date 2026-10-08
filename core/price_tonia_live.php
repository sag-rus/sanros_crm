<?php
// PHP 7.1 compatible. Shared by the CRM panel, authenticated endpoint and CLI checks.
function ptl_db(){
    static $p;if($p)return $p;
    require_once (defined('PTL_CRM_ROOT')?PTL_CRM_ROOT:dirname(__DIR__)).'/config.php';$c=new JConfig;
    $p=new PDO('mysql:host='.$c->host.';dbname='.$c->db.';charset=utf8',$c->user,$c->password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);return $p;
}
function ptl_query($sql,$args=[]){$q=ptl_db()->prepare($sql);$q->execute($args);return $q;}
function ptl_connected_object($id){
    return (bool)ptl_query("SELECT 1 FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=? AND import_enabled=1 LIMIT 1",[(int)$id])->fetchColumn();
}
function ptl_payload($id){
    $r=ptl_query('SELECT r.id,r.id_obj,r.date_z,r.date_v,r.number_turist,r.children_rest,o.default_price_type,r.note FROM reckoning r JOIN object o ON o.id=r.id_obj WHERE r.id=?',[$id])->fetch();
    if(!$r || !ptl_connected_object((int)$r['id_obj']))throw new RuntimeException('Для этой заявки источник price.tonia.ru не подключён.');
    if($r['children_rest'] || $r['number_turist']<1 || $r['number_turist']>3)throw new RuntimeException('Автоматическая проверка поддерживает 1–3 взрослых без детей.');
    $rows=ptl_query('SELECT id_room,ratePlan,date_z,days,number,type,add_one_day,sum FROM position_reck WHERE schet=? AND id_room>0 ORDER BY date_z,id',[$id])->fetchAll();
    if(!$rows)throw new RuntimeException('В заявке не выбран номер.');
    $inclusive=ptl_query("SELECT billing_basis FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?",[$r['id_obj']])->fetchColumn()==='day_inclusive';
    $chargeEnd=$inclusive?date('Y-m-d',strtotime($r['date_v'].' +1 day')):$r['date_v'];
    $first=$rows[0];$next=$r['date_z'];$amount=0;
    foreach($rows as $row){
        if($row['id_room']!=$first['id_room'] || $row['ratePlan']!=$first['ratePlan'] || $row['number']!=1 || $row['type']!=2 || $row['add_one_day']!=($inclusive?0:1) || $row['days']<1 || $row['date_z']!==$next)throw new RuntimeException('Для проверки нужен один номер и один тариф на непрерывный период проживания.');
        $next=date('Y-m-d',strtotime($next.' +'.(int)$row['days'].' days'));$amount+=(int)round((float)$row['sum']*100)*(int)$row['days'];
    }
    if($next!==$chargeEnd || $r['date_z']<date('Y-m-d') || strtotime($r['date_v'])-strtotime($r['date_z'])>60*86400)throw new RuntimeException('Проверьте даты заявки: доступен будущий заезд до 60 ночей.');
    $map=ptl_query("SELECT rm.external_property_key,rm.external_room_key,rt.external_rate_key FROM external_price_mapping rm JOIN external_price_mapping rt ON rt.source=rm.source AND rt.crm_object_id=rm.crm_object_id AND rt.external_room_key=rm.external_room_key AND rt.external_property_key=rm.external_property_key AND rt.entity_type='rate' AND rt.mapping_status='verified' AND rt.crm_id=? WHERE rm.source='price_tonia_ru' AND rm.crm_object_id=? AND rm.entity_type='room' AND rm.mapping_status='verified' AND rm.crm_id=?",[$first['ratePlan'],$r['id_obj'],$first['id_room']])->fetchAll();
    if(count($map)!==1)throw new RuntimeException('Не найдено однозначное соответствие номера и тарифа в price.tonia.ru.');
    $m=$map[0];return ['booking_id'=>(int)$id,'property_id'=>(int)$m['external_property_key'],'crm_object_id'=>(int)$r['id_obj'],'room_key'=>$m['external_room_key'],'rate_key'=>$m['external_rate_key'],'arrival'=>$r['date_z'],'departure'=>$r['date_v'],'adults'=>(int)$r['number_turist'],'quoted_total'=>number_format($amount/100,2,'.',''),'room_name'=>(string)ptl_query('SELECT name FROM room WHERE id=?',[$first['id_room']])->fetchColumn(),'rate_name'=>(string)ptl_query('SELECT name FROM rate_plan WHERE id=?',[$first['ratePlan']])->fetchColumn()];
}
function ptl_latest($id){return ptl_query('SELECT * FROM price_tonia_live_checks WHERE booking_id=? ORDER BY id DESC LIMIT 1',[$id])->fetch();}
function ptl_active($row){return $row && in_array($row['status'],['queued','running'],true);}
function ptl_expire($id){
    ptl_query("UPDATE price_tonia_live_checks SET status='error', result_json=?,updated_at=UTC_TIMESTAMP() WHERE booking_id=? AND status IN ('queued','running') AND expires_at<=UTC_TIMESTAMP()",[json_encode(['message'=>'Проверка превысила время ожидания. Запустите её повторно.'],JSON_UNESCAPED_UNICODE),$id]);
}
function ptl_start($id,$user){
    $p=ptl_db();$lock='ptl_booking_'.(int)$id;
    if(!ptl_query('SELECT GET_LOCK(?,3)',[$lock])->fetchColumn())throw new RuntimeException('Проверка уже запускается. Обновите её статус.');
    try{
        ptl_expire($id);$row=ptl_latest($id);
        if(ptl_active($row))return $row;
        $v=ptl_payload($id);$json=json_encode($v,JSON_UNESCAPED_UNICODE);$uuid=bin2hex(random_bytes(16));
        ptl_query("INSERT INTO price_tonia_live_checks(booking_id,request_id,user_id,payload_json,payload_hash,status,expires_at,created_at,updated_at) VALUES(?,?,?,?,?,'queued',DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE),UTC_TIMESTAMP(),UTC_TIMESTAMP())",[$id,$uuid,$user,$json,hash('sha256',$json)]);
        return ptl_latest($id);
    }finally{ptl_query('SELECT RELEASE_LOCK(?)',[$lock]);}
}
function ptl_refresh($row){
    if(!ptl_active($row))return $row;
    $v=json_decode($row['payload_json'],true);unset($v['quoted_total'],$v['room_name'],$v['rate_name']);
    $v['request_id']=$row['request_id'];$v['expires_at']=strtotime($row['expires_at'].' UTC');
    $path='/var/lib/price-tonia-live/token';
    if(!is_readable($path))throw new RuntimeException('Сервис проверки временно недоступен.');
    $curl=curl_init('https://price.tonia.ru/api/live-check');
    curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($v),CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json','Authorization: Bearer '.trim(file_get_contents($path))],CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>8]);
    $raw=curl_exec($curl);$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);curl_close($curl);$data=json_decode((string)$raw,true);
    if($code!==200 || !is_array($data) || ($data['request_id']??'')!==$row['request_id'] || !in_array($data['status']??'',['queued','running','available','unavailable','error'],true))throw new RuntimeException('Нет связи с сервисом проверки. Статус будет запрошен повторно.');
    ptl_query("UPDATE price_tonia_live_checks SET status=?,result_json=?,updated_at=UTC_TIMESTAMP() WHERE request_id=? AND status IN ('queued','running')",[$data['status'],json_encode($data['result'],JSON_UNESCAPED_UNICODE),$row['request_id']]);
    return ptl_latest($row['booking_id']);
}
function ptl_state($id,$refresh=false){
    ptl_expire($id);$row=ptl_latest($id);$warning=null;
    if($refresh && ptl_active($row)){try{$row=ptl_refresh($row);}catch(Throwable $e){$warning=$e->getMessage();}}
    try{$current=ptl_payload($id);$eligible=true;$reason=null;}catch(Throwable $e){$current=null;$eligible=false;$reason=$e->getMessage();}
    return ['eligible'=>$eligible,'reason'=>$reason,'warning'=>$warning,'active'=>ptl_active($row),'status'=>$row?$row['status']:'idle','request_id'=>$row?$row['request_id']:null,'parameters'=>$row?json_decode($row['payload_json'],true):$current,'result'=>$row?json_decode($row['result_json'],true):null,'stale'=>$row && (!$current || hash('sha256',json_encode($current,JSON_UNESCAPED_UNICODE))!==$row['payload_hash']),'created_at'=>$row?$row['created_at']:null];
}
function ptl_panel($id){
    try{
        $r=ptl_query('SELECT r.id_obj FROM reckoning r WHERE r.id=?',[$id])->fetch();
        if(!$r || !ptl_connected_object((int)$r['id_obj']))return;
        if(empty($_SESSION['ptl_csrf']))$_SESSION['ptl_csrf']=bin2hex(random_bytes(32));
        $state=ptl_state($id);$dom='price-live-'.(int)$id;
        echo '<div id="'.$dom.'" style="margin:12px 0; padding:10px; border:1px solid #ccc; border-radius:4px"><strong>Проверка на сайте объекта</strong><br><button type="button" class="btn btn-primary btn-sm ptl-start" style="margin:8px 0" disabled>Загрузка статуса…</button><div class="ptl-result" aria-live="polite"></div></div>';
        echo '<script>(function(){var initial='.json_encode($state,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';var csrf='.json_encode($_SESSION['ptl_csrf']).';var booking='.(int)$id.';var dom='.json_encode($dom).';';
        readfile(__DIR__.'/price-tonia-live-panel.js');echo '})();</script>';
    }catch(Throwable $e){error_log('Price live panel: '.$e->getMessage());echo '<div class="text-muted">Сервис проверки временно недоступен.</div>';}
}
