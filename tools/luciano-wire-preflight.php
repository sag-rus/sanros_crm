<?php
// CRM CLI only, read-only. No import/activation/ACK action is exposed by this entry point.
if (PHP_SAPI!=='cli') exit(1);
require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
require_once __DIR__.'/../app/Support/LucianoWireImportPlan.php';
require_once __DIR__.'/../app/Support/LucianoWireDestination.php';
use App\Support\LucianoWireEnvelope as Wire;
use App\Support\LucianoWireImportPlan as Plan;
use App\Support\LucianoWireDestination as Destination;

try {
    if (count($argv)!==4 || !in_array($argv[1],['kazan','sochi'],true)
        || !preg_match('/^[a-f0-9]{64}$/D',$argv[3])) throw new RuntimeException('Usage: luciano-wire-preflight.php kazan|sochi PACKET SHA256');
    $hotel=$argv[1];$path=$argv[2];$expectedSha=$argv[3];
    $object=$hotel==='kazan'?'1096':'1658';$property=$hotel==='kazan'?'14':'15';
    $keyPattern='reload-v1-luciano-initial-'.$object.'-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}';
    if (!preg_match('#^/var/tmp/price-tonia-luciano-wire-v1-'.$object.'-('.$keyPattern.')-crm-packet\.json$#D',$path,$match)) throw new RuntimeException('Foreign or unsupported packet path');
    $stat=lstat($path);
    if (!$stat || is_link($path) || ($stat['mode']&0170000)!==0100000 || ($stat['mode']&0777)!==0600
        || $stat['size']<1 || $stat['size']>Wire::MAX_WIRE_BYTES) throw new RuntimeException('Packet must be a bounded exclusive regular 0600 file');
    $f=fopen($path,'rb');if (!$f) throw new RuntimeException('Packet cannot be read');
    try {
        if (!flock($f,LOCK_SH|LOCK_NB)) throw new RuntimeException('Packet is busy');
        $opened=fstat($f);
        if ($opened['dev']!==$stat['dev'] || $opened['ino']!==$stat['ino'] || $opened['size']!==$stat['size']) throw new RuntimeException('Packet identity changed');
        $json=stream_get_contents($f,Wire::MAX_WIRE_BYTES+1);
        $after=fstat($f);
        if (!is_string($json) || strlen($json)!==$stat['size'] || $after['size']!==$stat['size']
            || $after['mtime']!==$stat['mtime'] || $after['ctime']!==$stat['ctime'] || !hash_equals($expectedSha,hash('sha256',$json))) throw new RuntimeException('Packet checksum or identity changed');
    } finally { fclose($f); }
    $wire=json_decode($json,true);
    if (json_last_error()!==JSON_ERROR_NONE || !is_array($wire) || Wire::encode($wire)!==$json
        || ($wire['crm_object_id']??null)!==$object || ($wire['external_property_key']??null)!==$property
        || ($wire['snapshot_key']??null)!==$match[1]) throw new RuntimeException('Packet scope or canonical framing differs');
    // Resolve connection through the existing deployed bootstrap, never through copied credentials.
    // All SQL following bootstrap is SELECT/SHOW; native transport import/dry-run is never invoked.
    $argv=[__FILE__,'crm',dirname(__DIR__)];
    require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';
    require '/home/rustem/price-tonia-auto-sync-v1/common.php';
    $pdo->beginTransaction();
    try {
        $fields=[];
        foreach (tSections() as $section=>$table) $fields[$section]=tFieldNames($pdo,$table,$section);
        $packet=(int)rows($pdo,'SELECT @@max_allowed_packet AS packet')[0]['packet'];
        $state=rows($pdo,"SELECT source,crm_object_id,external_property_key,import_enabled,publish_enabled,active_snapshot_id,stale_after_seconds,timezone FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
        $snapshots=(int)rows($pdo,"SELECT COUNT(*) AS n FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object])[0]['n'];
        $destination=Destination::initial($hotel,$state,$snapshots);
        $validated=Plan::validate($wire,'crm',$fields);$e=$validated['envelope'];$data=$e['payload']['data'];$header=$e['payload']['snapshot'];
        // Conservative quoted-SQL bounds, not an executed INSERT or native-protocol proof.
        // Future generated IDs are bounded at 20 digits; observations/price data remain intact.
        $bound=function($table,$values) use($pdo) {
            $sql='INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($values)).'`) VALUES (';
            $literals=[];foreach ($values as $value) {
                if ($value===null) $literals[]='NULL';
                else { $quoted=$pdo->quote((string)$value);if ($quoted===false) throw new RuntimeException('SQL quote failed');$literals[]=$quoted; }
            }
            return strlen($sql.implode(',',$literals).')')+16384;
        };
        $max=[];
        foreach (tSections() as $section=>$table) {
            $max[$section]=0;
            foreach ($data[$section] as $row) {
                $row['snapshot_id']='18446744073709551615';
                if ($section==='catalog') unset($row['catalog_key']);
                else {
                    $row['catalog_id']=$row['catalog_key']===null?null:'18446744073709551615';unset($row['catalog_key']);
                    if ($section!=='observations') { $row['observation_id']=$row['observation_key']===null?null:'18446744073709551615';unset($row['observation_key']); }
                }
                $max[$section]=max($max[$section],$bound($table,$row));
            }
        }
        $max['compressed_inbox']=$bound('external_sync_inbox',['snapshot_id'=>'18446744073709551615','sender'=>'price',
            'chunk_key'=>'luciano-wire-v1','payload_sha256'=>$wire['payload_sha256'],'row_count'=>$header['row_count'],
            'status'=>'applied','payload_json'=>$json,'received_at'=>gmdate('Y-m-d H:i:s'),'applied_at'=>gmdate('Y-m-d H:i:s')]);
        $report=['preflight_only'=>true,'utc'=>gmdate('c'),'hotel'=>$hotel,'object_id'=>$object,'snapshot_key'=>$wire['snapshot_key'],
            'wire_sha256'=>$expectedSha,'original_payload_sha256'=>$wire['payload_sha256'],'counts'=>$validated['section_counts'],
            'wire_bytes'=>strlen($json),'original_envelope_bytes'=>$wire['decoded_bytes'],'crm_max_allowed_packet'=>$packet,
            'quoted_sql_upper_bounds_with_16k_margin'=>$max,'sql_bounds_within_limit'=>max($max)<$packet,
            'exact_python_framing_tested'=>false,
            'scoped_destination_state'=>$state,'scoped_snapshot_count'=>$snapshots,'pdo_emulate_prepares'=>$pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES),
            'destination_preflight'=>$destination,
            'native_sql_executed'=>false,'gateway_called'=>false,'file_written'=>false,'database_written'=>false,
            'consumer_integrated'=>false,'delivery_ready'=>false];
        $pdo->rollBack();
    } catch (Throwable $error) { if ($pdo->inTransaction()) $pdo->rollBack();throw $error; }
    echo Wire::encode($report),PHP_EOL;
} catch (Throwable $error) { fwrite(STDERR,$error->getMessage().PHP_EOL);exit(1); }
