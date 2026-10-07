<?php
// PDO/SQL is simulated entirely in memory; no connection, production rows, queue, booking or ACK exists.
require __DIR__.'/luciano-wire-readback.php';
require __DIR__.'/../app/Support/LucianoWireDestination.php';
require __DIR__.'/../app/Support/LucianoStateOnboarding.php';
require __DIR__.'/../app/Support/LucianoWireRowCursor.php';
require __DIR__.'/../app/Support/LucianoWireInitialConsumer.php';
use App\Support\LucianoWireInitialConsumer as Consumer;
use App\Support\LucianoWireEnvelope as Wire;

class LucianoMemoryStatement extends PDOStatement {
    private $db;private $sql;private $rows=[];private $affected=0;
    public function __construct($db,$sql){$this->db=$db;$this->sql=$sql;}
    public function execute($values=null): bool {
        list($this->rows,$this->affected)=$this->db->run($this->sql,$values??[]);
        return !($this->db->failure==='silent INSERT' && strpos($this->sql,'INSERT INTO `external_price_snapshot`')===0);
    }
    public function fetchAll($mode=PDO::FETCH_ASSOC,...$args): array {return $this->rows;}
    public function rowCount(): int {return $this->affected;}
}
class LucianoMemoryConnection extends PDO {
    public $original;public $state;public $failure;public $tables=[];public $sql=[];
    public $writes=0;public $commits=0;public $rollbacks=0;public $released=0;public $packet=67108864;
    public $driver='mysql';public $countBefore=0;public $engine='InnoDB';public $busy=false;
    private $active=false;private $saved;private $last='0';private $ids=[];
    public function __construct($e,$failure=null){
        $this->original=$e['payload'];$this->failure=$failure;
        $object=$e['payload']['crm_object_id'];$property=$e['payload']['external_property_key'];
        $this->state=['source'=>'price_tonia_ru','crm_object_id'=>$object,'external_property_key'=>$property,
            'import_enabled'=>'0','publish_enabled'=>'0','active_snapshot_id'=>null,'stale_after_seconds'=>null,
            'timezone'=>'Europe/Moscow','billing_basis'=>'night','local_property_id'=>null,
            'upstream_property_key'=>$object==='1096'?'3026':'434','last_attempt_at'=>null,'last_success_at'=>null,
            'last_error'=>null,'created_at'=>'2026-10-07 07:21:29','updated_at'=>'2026-10-07 07:21:29'];
    }
    // PHP 7.1 treats these PHP 8 compatibility annotations as ordinary line comments.
    #[\ReturnTypeWillChange]
    public function getAttribute($attribute){return $attribute===PDO::ATTR_DRIVER_NAME?$this->driver:true;}
    #[\ReturnTypeWillChange]
    public function prepare($sql,$options=[]){$this->sql[]=$sql;return new LucianoMemoryStatement($this,$sql);}
    #[\ReturnTypeWillChange]
    public function quote($value,$type=PDO::PARAM_STR){return $this->failure==='quote failed'?false:"'".str_replace(["\\","'"],["\\\\","\\'"],$value)."'";}
    #[\ReturnTypeWillChange]
    public function lastInsertId($name=null){return $this->failure==='invalid row ID'?'01':$this->last;}
    #[\ReturnTypeWillChange]
    public function exec($sql){$this->sql[]=$sql;return $this->failure==='isolation' ? false:0;}
    public function inTransaction(): bool {return $this->active;}
    public function beginTransaction(): bool {$this->saved=$this->tables;$this->active=true;return true;}
    public function commit(): bool {if ($this->failure==='commit')return false;$this->active=false;$this->commits++;return true;}
    public function rollBack(): bool {$this->tables=$this->saved;$this->active=false;$this->rollbacks++;return true;}
    public function run($sql,$values){
        $sections=['catalog'=>'external_price_catalog','observations'=>'external_price_observation',
            'daily'=>'external_daily_price','restrictions'=>'external_stay_restriction','offers'=>'external_stay_offer'];
        if (strpos($sql,'SHOW COLUMNS FROM ')===0) {
            $table=substr($sql,18);
            if ($table==='external_price_state')$fields=array_keys($this->state);
            else {
                $section=array_search($table,$sections,true);
                $fields=array_keys($this->original['data'][$section][0]??['catalog_key'=>null,'observation_key'=>null,'min_nights'=>null]);
                $fields=array_values(array_diff($fields,['catalog_key','observation_key']));
                $fields=array_merge(['id','snapshot_id'],$fields);
                if ($section!=='catalog')$fields[]='catalog_id';
                if ($section==='observations')$fields[]='observation_key';
                elseif ($section!=='catalog')$fields[]='observation_id';
            }
            return [array_map(function($f){return ['Field'=>$f];},$fields),0];
        }
        if (strpos($sql,'SELECT GET_LOCK')===0)return [[['held'=>$this->busy?'0':'1']],0];
        if (strpos($sql,'SELECT RELEASE_LOCK')===0){$this->released++;return [[['released'=>'1']],0];}
        if (strpos($sql,'SELECT TABLE_NAME,ENGINE')===0)return [array_map(function($t){return ['TABLE_NAME'=>$t,'ENGINE'=>$this->engine];},$values),0];
        if (strpos($sql,'SELECT @@max_allowed_packet')===0)return [[['packet'=>$this->packet]],0];
        if (strpos($sql,'SELECT id,name FROM object ')===0)return [[['id'=>$values[0],'name'=>$values[0]==='1096'?'Luciano':'Luciano Sochi сан кур']],0];
        if (strpos($sql,'SELECT * FROM external_price_state ')===0){
            $state=$this->state;
            if ($this->failure==='state changed' && !empty($this->tables['external_sync_inbox']))$state['import_enabled']='1';
            return [[$state],0];
        }
        if (strpos($sql,'SELECT COUNT(*) AS n FROM external_price_snapshot ')===0)return [[['n'=>$this->countBefore+count($this->tables['external_price_snapshot']??[])]],0];
        if (strpos($sql,'INSERT INTO `')===0) {
            preg_match('/^INSERT INTO `([a-z_]+)` \((.+)\) VALUES /',$sql,$match);
            $table=$match[1];$fields=explode('`,`',trim($match[2],'`'));$row=array_combine($fields,$values);
            if ($this->failure==='row INSERT' && $table==='external_stay_offer')throw new RuntimeException('Simulated row insert failure');
            $id=(string)(($this->ids[$table]??0)+1);$this->ids[$table]=(int)$id;$this->last=$id;
            $row['id']=$id;
            if ($table==='external_price_snapshot')$row+=['published_at'=>null,'error_summary'=>null];
            if ($table==='external_sync_inbox')$row+=['error_summary'=>null];
            $this->tables[$table][$id]=$row;$this->writes++;
            return [[],($this->failure==='row affected count' && $table==='external_stay_offer')?0:1];
        }
        if (strpos($sql,'UPDATE external_price_snapshot ')===0) {
            $this->writes++;$r=&$this->tables['external_price_snapshot'][$values[3]];
            wireAssert($r['source']==='price_tonia_ru' && $r['crm_object_id']===$values[4] && $r['snapshot_key']===$values[5] && $r['status']==='staging');
            $r['status']='ready';$r['row_count']=$values[0];$r['checksum']=$values[1];$r['imported_at']=$values[2];unset($r);
            return [[], $this->failure==='completion UPDATE'?0:1];
        }
        if (strpos($sql,'SELECT * FROM external_price_snapshot ')===0){
            $row=$this->tables['external_price_snapshot'][$values[1]];
            if ($this->failure==='snapshot metadata')$row['checked_from']='2026-10-07 09:00:00';
            return [[$row],0];
        }
        if (strpos($sql,'SELECT * FROM external_sync_inbox ')===0){
            $row=array_values($this->tables['external_sync_inbox'])[0];
            if ($this->failure==='inbox bytes')$row['payload_json'].=' ';
            if ($this->failure==='inbox metadata')$row['row_count']='2';
            return [[$row],0];
        }
        throw new RuntimeException('Unreviewed SQL in memory fixture: '.$sql);
    }
}
function tPayload($db,$snapshot){
    $sections=['catalog'=>'external_price_catalog','observations'=>'external_price_observation',
        'daily'=>'external_daily_price','restrictions'=>'external_stay_restriction','offers'=>'external_stay_offer'];
    $data=[];$cats=[];$obs=[];
    foreach($sections as $section=>$table){$data[$section]=[];
        foreach($db->tables[$table]??[] as $row){
            wireAssert($row['snapshot_id']===$snapshot['id']);
            if($section==='catalog'){$key=hash('sha256',json_encode([$row['entity_type'],$row['external_room_key'],$row['external_rate_key']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));$cats[$row['id']]=$key;$row['catalog_key']=$key;}
            if($section==='observations')$obs[$row['id']]=$row['observation_key'];
            if(isset($row['catalog_id'])){$row['catalog_key']=$cats[$row['catalog_id']];unset($row['catalog_id']);}
            if(array_key_exists('observation_id',$row)){$row['observation_key']=$row['observation_id']===null?null:$obs[$row['observation_id']];unset($row['observation_id']);}
            unset($row['id'],$row['snapshot_id']);$data[$section][]=$row;
        }
    }
    $payload=$db->original;$payload['data']=$data;
    if($db->failure==='full payload')$payload['data']['offers'][0]['total_amount']='95300.00';
    return $payload;
}
$checks=0;
foreach([['1096','14'],['1658','15']] as $scope){
    $a=readbackEvidence($scope[0],$scope[1]);$e=Wire::unpack(json_decode($a[4][0]['payload_json'],true),'crm');$json=Wire::encode(Wire::pack($e));
    $pdo=new LucianoMemoryConnection($e);$before=$pdo->state;
    $report=Consumer::apply($pdo,$a[0],$json,hash('sha256',$json));
    wireAssert($pdo->commits===1 && $pdo->rollbacks===0 && $pdo->released===1 && !$pdo->inTransaction());$checks++;
    wireAssert($report['status']==='ready' && !$report['activated'] && !$report['published'] && !$report['ack_sent'] && !$report['outbox_created'] && !$report['delivery_ready']);$checks++;
    wireAssert($pdo->state===$before && count($pdo->tables['external_price_snapshot'])===1 && count($pdo->tables['external_sync_inbox'])===1);$checks++;
    wireAssert(array_values($pdo->tables['external_sync_inbox'])[0]['payload_json']===$json);$checks++;
    wireAssert(Wire::encode(tPayload($pdo,array_values($pdo->tables['external_price_snapshot'])[0]))===Wire::encode($e['payload']));$checks++;
    $keys=array_keys($pdo->tables);sort($keys);$expected=['external_price_snapshot','external_sync_inbox','external_price_catalog','external_price_observation','external_daily_price','external_stay_restriction','external_stay_offer'];sort($expected);
    wireAssert($keys===$expected);$checks++;
    wireAssert($pdo->writes===9);$checks++;
    // A retry cannot create a second initial or update existing rows.
    $writes=$pdo->writes;wireReject(function()use($pdo,$a,$json){Consumer::apply($pdo,$a[0],$json,hash('sha256',$json));});
    wireAssert($pdo->writes===$writes && count($pdo->tables['external_price_snapshot'])===1);$checks++;
}
$a=readbackEvidence();$e=Wire::unpack(json_decode($a[4][0]['payload_json'],true),'crm');$json=Wire::encode(Wire::pack($e));
foreach(['isolation','row INSERT','silent INSERT','row affected count','invalid row ID','completion UPDATE','snapshot metadata','full payload','inbox bytes','inbox metadata','state changed','commit','quote failed'] as $failure){
    $pdo=new LucianoMemoryConnection($e,$failure);
    wireReject(function()use($pdo,$json){Consumer::apply($pdo,'kazan',$json,hash('sha256',$json));});
    wireAssert($pdo->commits===0 && !$pdo->inTransaction() && $pdo->released===1 && $pdo->tables===[]);$checks++;
}
foreach(['busy','engine','packet','existing initial','active','provider','timezone','stale','wrong hotel','bad digest','bad JSON','wrong driver'] as $case){
    $pdo=new LucianoMemoryConnection($e);$hotel='kazan';$input=$json;$sha=hash('sha256',$json);
    switch($case){
        case 'busy':$pdo->busy=true;break;
        case 'engine':$pdo->engine='MyISAM';break;
        case 'packet':$pdo->packet=16385;break;
        case 'existing initial':$pdo->countBefore=1;break;
        case 'active':$pdo->state['active_snapshot_id']='1';break;
        case 'provider':$pdo->state['upstream_property_key']='434';break;
        case 'timezone':$pdo->state['timezone']='UTC';break;
        case 'stale':$pdo->state['stale_after_seconds']='900';break;
        case 'wrong hotel':$hotel='sochi';break;
        case 'bad digest':$sha=str_repeat('0',64);break;
        case 'bad JSON':$input.=' ';$sha=hash('sha256',$input);break;
        case 'wrong driver':$pdo->driver='sqlite';break;
    }
    wireReject(function()use($pdo,$hotel,$input,$sha){Consumer::apply($pdo,$hotel,$input,$sha);});
    wireAssert($pdo->writes===0 && $pdo->commits===0 && !$pdo->inTransaction());$checks++;
}
$pdo=new LucianoMemoryConnection($e);$pdo->beginTransaction();
wireReject(function()use($pdo,$json){Consumer::apply($pdo,'kazan',$json,hash('sha256',$json));});
wireAssert($pdo->inTransaction() && $pdo->writes===0 && $pdo->rollbacks===0 && $pdo->released===0);$checks++;
$pdo->rollBack();
$empty=$e;$empty['payload']['data']['restrictions']=[];$empty=rehash($empty);$input=Wire::encode(Wire::pack($empty));
$pdo=new LucianoMemoryConnection($empty);$report=Consumer::apply($pdo,'kazan',$input,hash('sha256',$input));
wireAssert($report['section_counts']['restrictions']===0 && !isset($pdo->tables['external_stay_restriction']) && $pdo->commits===1);$checks++;
$bad=$e;$bad['payload']['data']['offers'][0]['observation_key']=null;$bad=rehash($bad);$input=Wire::encode(Wire::pack($bad));
$pdo=new LucianoMemoryConnection($bad);
wireReject(function()use($pdo,$input){Consumer::apply($pdo,'kazan',$input,hash('sha256',$input));});
wireAssert($pdo->writes===0 && $pdo->released===0 && $pdo->rollbacks===0);$checks++;
echo "PASS $checks Luciano initial transaction memory-model isolation/rollback/readback fixtures; no native SQL or DB connection\n";
