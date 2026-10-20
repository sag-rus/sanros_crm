<?php
require __DIR__.'/../core/price.php';
require __DIR__.'/../core/sync/API/object.php';
class CheckAuthObjectCabinet { static function check_authorization(){ return true; } }
class PriceLockDb {
    public $writes=[];
    function getOne($sql,...$args) {
        $id=(int)$args[0];
        if (strpos($sql,'FROM price_live_catalogue')!==false) return in_array($id,[47,558,9999],true)?$id:false;
        if (strpos($sql,'SELECT id_obj FROM ')===0) return [101=>47,102=>3,103=>2,104=>558][$id]??false;
        throw new LogicException('Unexpected read: '.$sql);
    }
    function getRow($sql,...$args) {
        if (strpos($sql,'FROM external_price_state')!==false) return (int)$args[0]===3?['external_property_key'=>'4']:false;
        if (strpos($sql,'FROM price WHERE')!==false) return (int)$args[0]===201?['id_room'=>101,'id_range'=>103]:false;
        throw new LogicException('Unexpected row read: '.$sql);
    }
    function query($sql,...$args) {$this->writes[]=[$sql,$args];}
}
function checkLock($ok,$message){if(!$ok)throw new RuntimeException($message);}
$db=new PriceLockDb;
checkLock(price_tonia_readonly_url($db,3)==='https://price.tonia.ru/properties/4','Legacy destination');
checkLock(price_tonia_readonly_url($db,558)==='https://price.tonia.ru/live-catalogues','Live destination');
checkLock(price_tonia_readonly_url($db,9999)!==null,'Future catalogues must be protected without an allowlist');
checkLock(price_tonia_readonly_url($db,2)===null,'Manual object must remain editable');
foreach([3,47,558] as $id) {
    $_POST=['id'=>$id];ob_start();view_dates_price_object($db);$html=ob_get_clean();
    checkLock(strpos($html,'Объект подключен через микросервис')!==false && strpos($html,'Новые даты')===false,'Readonly prices tab');
}
$cases=[
 ['save_date_price_manager',['id'=>47]],
 ['update_date_price_manager',['id'=>101]],['delete_date_price_manager',['id'=>101]],
 ['update_price_manager',['id'=>201,'room'=>103,'range'=>103]],
 ['update_price_manager',['id'=>0,'room'=>103,'range'=>101]],
 ['update_price_manager',['id'=>0,'room'=>101,'range'=>103]],
 ['update_range_manager',['id'=>103,'date'=>101]],
 ['update_range_manager',['id'=>101,'date'=>103]],
 ['save_range_manager',['object'=>2,'date'=>101]],
 ['delete_range_manager',['id'=>101]],
 ['copy_date_price',['date'=>103,'new_date'=>101]],
 ['copy_date_price',['date'=>101,'new_date'=>103]],
 ['reestablish_price_date',['date'=>101]],['update_range_counter',['id'=>101]],
 ['edit_date_price_manager',['id'=>101]],['edit_date_price_manager',['object'=>47]],
 ['edit_range_manager',['id'=>101]],['show_dates_copy_prices',['date'=>101]]
];
foreach($cases as [$fn,$post]) {
    $_POST=$post;http_response_code(200);ob_start();$fn($db);$text=ob_get_clean();
    checkLock(http_response_code()===403 && strpos($text,'Ручное редактирование')!==false && !$db->writes,$fn.' must block before any write');
}
foreach(['save_new_date_price_account','update_date_price_account','save_new_range_account','del_range_account','update_range_account'] as $fn){
    ob_start();$fn($db,['object'=>47,'id'=>101]);ob_end_clean();checkLock(!$db->writes,$fn.' must block');
}
// A spoofed unprotected object and a mixed batch cannot write even its first price.
ob_start();save_price_object_account($db,['object'=>2,'data'=>json_encode([1=>[103=>[103=>100],101=>[103=>200]]])]);ob_end_clean();
checkLock(!$db->writes,'Batch must be fully checked before writing');
$_POST=['id'=>2,'start'=>'2026-12-01','end'=>'2026-12-08'];save_date_price_manager($db);
checkLock(count($db->writes)===1,'Manual object date editing must remain available');
echo "PRICE read-only UI and write guards passed\n";
