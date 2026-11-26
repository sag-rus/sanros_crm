<?php
require __DIR__.'/../core/comfort-booking-payments.php';
$n=0;function paymentCheck($ok,$why){global $n;if(!$ok)throw new RuntimeException($why);$n++;}
$p=new PDO('sqlite::memory:');$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$p->exec('CREATE TABLE reckoning(id INTEGER PRIMARY KEY,sum TEXT,agency INTEGER,id_dis INTEGER,id_com INTEGER,commission_value TEXT,status INTEGER,cb_submission_key TEXT)');
$p->exec("INSERT INTO reckoning VALUES(1,'1600.00',0,0,0,'0',3,'fixture'),(2,'1600.00',0,0,0,'0',3,NULL)");
$p->exec('CREATE TABLE payment(id INTEGER PRIMARY KEY,schet INTEGER,sum TEXT,type INTEGER,pay_method INTEGER,status INTEGER DEFAULT 2,class TEXT DEFAULT "schet")');
$p->exec('CREATE TABLE bonus(schet INTEGER,sum TEXT)');
$p->exec('CREATE TABLE discount(id INTEGER,value TEXT,type INTEGER)');
$p->exec('CREATE TABLE commission(id INTEGER,value TEXT)');
paymentCheck(cb_payment_summary($p,1,'1600')['state']==='unpaid','no payments');
$p->exec("INSERT INTO payment(id,schet,sum,type,pay_method) VALUES(1,1,'600',1,2)");
$s=cb_payment_summary($p,1,'1600');paymentCheck($s['paid']==='600.00'&&$s['remaining']==='1000.00'&&$s['state']==='partial','posted cash deposit counted');
$p->exec("INSERT INTO payment VALUES(2,1,'300',1,5,1,'schet'),(3,1,'999',1,5,0,'schet'),(4,1,'888',3,1,2,'schet'),(5,1,'777',2,4,2,'schet'),(6,1,'666',2,2,2,'other'),(7,2,'555',2,2,2,'schet')");
$s=cb_payment_summary($p,1,'1600');paymentCheck($s['paid']==='600.00'&&$s['pending']==='300.00','holds, cancelled payments, supplier payments, pay-at-hotel and other requests excluded');
$p->exec("INSERT INTO payment(id,schet,sum,type,pay_method) VALUES(8,1,'1000',2,7)");
$s=cb_payment_summary($p,1,'1600');paymentCheck($s['state']==='paid'&&$s['remaining']==='0.00','cash plus posted QR fully paid');
$p->exec("INSERT INTO payment(id,schet,sum,type,pay_method) VALUES(9,1,'100',5,2)");
$s=cb_payment_summary($p,1,'1600');paymentCheck($s['paid']==='1500.00'&&$s['remaining']==='100.00','refund restores outstanding amount');
$s=cb_payment_summary($p,1,'1400');paymentCheck($s['state']==='overpaid'&&$s['overpaid']==='100.00','overpayment after price decrease');
paymentCheck(cb_payment_summary($p,1,'1600',true)['state']==='cancelled','cancelled state independent of payments');
paymentCheck(cb_invoice_status(1,true)===3&&cb_invoice_status(1,false)===1&&cb_invoice_status(2,false)===3,'CB new invoice and ordinary invoice transitions');
foreach([3,4,5,6,7,9,12,13] as $status)paymentCheck(cb_invoice_status($status,true)===$status,'invoice does not reset an existing financial or closed status');
$p->exec("INSERT INTO discount VALUES(1,'10',1);UPDATE reckoning SET id_dis=1 WHERE id=1;INSERT INTO bonus VALUES(1,'-40')");
paymentCheck(cb_customer_total($p,1,'1600')==='1400.00','ordinary discount and bonus deductions preserved');
$p->exec('UPDATE reckoning SET id_dis=0 WHERE id=1;DELETE FROM bonus');
class PaymentAdapter {
    public $p;function __construct($p){$this->p=$p;}
    function query($sql,...$values){$sql=preg_replace('/\?[is]/','?',$sql);if(strpos($sql,'UPDATE ')===0)$sql=preg_replace('/ LIMIT 1$/','',$sql);$q=$this->p->prepare($sql);$q->execute($values);return $q;}
    function getRow($sql,...$values){return $this->query($sql,...$values)->fetch(PDO::FETCH_ASSOC);}
    function getOne($sql,...$values){return $this->query($sql,...$values)->fetchColumn();}
    function getAll($sql,...$values){return $this->query($sql,...$values)->fetchAll(PDO::FETCH_ASSOC);}
}
require __DIR__.'/../core/functions.php';
$db=new PaymentAdapter($p);
paymentCheck(get_sum_for_pay($db,1)===100.0,'ordinary payment dialog receives CB outstanding amount');
$p->exec('UPDATE reckoning SET status=5 WHERE id=1');
paymentCheck(get_sum_for_pay($db,1)===100.0,'final payment remains included regardless of current request status');
paymentCheck((float)get_sum_for_pay($db,2)===1600.0,'ordinary request keeps its existing calculation');
echo "$n payment checks passed\n";
