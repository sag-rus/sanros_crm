<?php
// Entire bank boundary is mocked. No network, credentials, emails or real payments.
namespace App\lib\payment\Alfa {
    function file_put_contents($path,$content,$flags=0){return strlen($content);}
    function curl_init($url){$GLOBALS['fixtureBankUrls'][]=$url;return $url;}
    function curl_setopt($ch,$key,$value){return true;}
    function curl_close($ch){}
    function curl_exec($url){
        if(strpos($url,'getOrderStatusExtended.do')!==false)return json_encode(['orderStatus'=>2,'amount'=>50000]);
        if(strpos($url,'deposit.do')!==false)return json_encode(['ErrorCode'=>0]);
        if(strpos($url,'register.do')!==false)return json_encode(['orderId'=>'fixture-'.count($GLOBALS['fixtureBankUrls'])]);
        return json_encode(['qrStatus'=>'STARTED','renderedQr'=>'fixture-image','payload'=>'https://example.test/pay']);
    }
}
namespace App\lib\CRM\Config { class Client {static $instance;static function getInstance(){if(!self::$instance)self::$instance=new \stdClass;return self::$instance;}} }
namespace App\Model { class Bonus {function create(){}} }
namespace {
require __DIR__.'/comfort-booking-payments.php';
require __DIR__.'/../app/lib/payment/Alfa/BookingPayment.php';
class SendMailTurist {function notification_payment_booking(){}}
class FixtureBank extends \App\lib\payment\Alfa\BookingPayment {
    function __construct($db){$this->connect=$db;$this->booking=1;$this->turist=10;$this->link='https://example.test/';$this->bankInfo=['link'=>'https://bank.example.test/','userName'=>'fixture','password'=>'fixture','commission'=>0,'commission_qr'=>0];}
    protected function getObject($connect,$id,$type_view=''){return 'Fixture';}
    protected function saveNotification($text,$user){}
    protected function saveSchetToHistory($id,$note=''){}
}
foreach(['bank_com_auto_excluded','exclude_bank_commission','is_test','state_program','children_rest','far_east','count_payment','turist','id_obj','id_user'] as $field)$p->exec('ALTER TABLE reckoning ADD COLUMN '.$field.' INTEGER DEFAULT 0');
$p->exec("ALTER TABLE reckoning ADD COLUMN date_v TEXT DEFAULT '2026-12-17'");
$p->exec('ALTER TABLE bonus ADD COLUMN turist INTEGER');
foreach(['date TEXT','created INTEGER','processed INTEGER','bank_com TEXT'] as $field)$p->exec('ALTER TABLE payment ADD COLUMN '.$field);
$p->exec('CREATE TABLE payment_request(id INTEGER PRIMARY KEY,bid INTEGER,type INTEGER,pay_method INTEGER,sum TEXT,bank_com TEXT,order_id TEXT,bid_pay TEXT,status INTEGER DEFAULT 0)');
$p->exec("UPDATE reckoning SET status=4,sum='2000.00',turist=10,exclude_bank_commission=1 WHERE id=1");
$p->exec("CREATE TABLE object(id INTEGER,name TEXT,full_name TEXT,type INTEGER,id_reg INTEGER,city TEXT);INSERT INTO object VALUES(0,'Fixture','Fixture',1,0,'');CREATE TABLE type_object(id INTEGER,name TEXT);INSERT INTO type_object VALUES(1,'Hotel')");
$bank=new FixtureBank($db);$GLOBALS['fixtureBankUrls']=[];
$preview=$bank->checkPayment();
paymentCheck((float)$preview['to-pay']===500.0,'customer payment preview agrees with QR remaining amount');
$answer=$bank->registerPayment('pay','1');
parse_str(parse_url($GLOBALS['fixtureBankUrls'][0],PHP_URL_QUERY),$request);
paymentCheck((int)$request['amount']===50000&&$answer['renderedQr']==='fixture-image','QR charges only remaining amount, including posted final payments and refunds');
paymentCheck((int)$p->query('SELECT pay_method FROM payment_request')->fetchColumn()===7,'QR uses ordinary SBP payment request');
$_GET=['debug'=>0];$before=(int)$p->query('SELECT COUNT(*) FROM payment')->fetchColumn();
paymentCheck($bank->depositPayment($answer['orderNumber'])===1,'ordinary bank confirmation accepts CB payment');
$s=cb_payment_summary($p,1,'2000');
paymentCheck($s['state']==='paid'&&$s['paid']==='2000.00','confirmed QR payment appears in ordinary ledger and CB summary');
paymentCheck((int)$p->query('SELECT status FROM reckoning WHERE id=1')->fetchColumn()===5,'ordinary bank handler sets paid request status');
$bank->depositPayment($answer['orderNumber']);
paymentCheck((int)$p->query('SELECT COUNT(*) FROM payment')->fetchColumn()===$before+1,'repeated bank check does not duplicate successful payment');
$p->exec("UPDATE reckoning SET status=4,sum='1900' WHERE id=1");$GLOBALS['fixtureBankUrls']=[];
paymentCheck($bank->registerPayment('pay','1')===[]&&!$GLOBALS['fixtureBankUrls'],'overpaid CB request creates no extra bank charge');
$p->exec("UPDATE reckoning SET cb_submission_key=NULL,sum='2000' WHERE id=1");$GLOBALS['fixtureBankUrls']=[];
$bank->registerPayment('pay','1');parse_str(parse_url($GLOBALS['fixtureBankUrls'][0],PHP_URL_QUERY),$request);
paymentCheck((int)$request['amount']===110000,'ordinary bank amount calculation remains unchanged');
echo "$n checks including mocked QR flow passed\n";
}
