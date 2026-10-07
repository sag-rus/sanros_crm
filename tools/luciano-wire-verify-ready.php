<?php
// Read-only retry evidence for an existing dormant ready import. No receipt or ACK is emitted.
if (PHP_SAPI!=='cli') exit(1);
require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
require_once __DIR__.'/../app/Support/LucianoWireImportPlan.php';
require_once __DIR__.'/../app/Support/LucianoWireReadback.php';
use App\Support\LucianoWireEnvelope as Wire;
use App\Support\LucianoWireReadback as Readback;

try {
    if (count($argv)!==4 || !in_array($argv[1],['kazan','sochi'],true)
        || !preg_match('/^[a-f0-9]{64}$/D',$argv[3])) throw new RuntimeException('Usage: luciano-wire-verify-ready.php kazan|sochi SNAPSHOT_KEY WIRE_SHA256');
    $lucianoHotel=$argv[1];$lucianoKey=$argv[2];$lucianoWireSha=$argv[3];
    $lucianoObject=$lucianoHotel==='kazan'?'1096':'1658';
    if (!preg_match('/^reload-v1-luciano-initial-'.$lucianoObject.'-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D',$lucianoKey)) {
        throw new RuntimeException('Foreign or unsupported initial snapshot key');
    }
    // Isolate existing bootstrap variables; only its deployed PDO connection leaves this scope.
    $lucianoPdo=(function () {
        $argv=[__FILE__,'crm',dirname(__DIR__)];
        require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';
        require '/home/rustem/price-tonia-auto-sync-v1/common.php';
        return $pdo;
    })();
    if (!$lucianoPdo instanceof PDO || $lucianoPdo->getAttribute(PDO::ATTR_DRIVER_NAME)!=='mysql'
        || $lucianoPdo->inTransaction()) throw new RuntimeException('Expected idle deployed CRM MySQL connection');
    if ($lucianoPdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ')===false
        || !$lucianoPdo->beginTransaction()) throw new RuntimeException('Could not start consistent read transaction');
    try {
        $lucianoStates=rows($lucianoPdo,"SELECT * FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=? LIMIT 2",[$lucianoObject]);
        $lucianoSnapshots=rows($lucianoPdo,"SELECT * FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=? ORDER BY id LIMIT 2",[$lucianoObject]);
        if (count($lucianoStates)!==1 || count($lucianoSnapshots)!==1
            || $lucianoSnapshots[0]['snapshot_key']!==$lucianoKey) throw new RuntimeException('Expected exactly one owned initial snapshot/state');
        $lucianoSnapshot=$lucianoSnapshots[0];
        $lucianoInboxes=rows($lucianoPdo,"SELECT * FROM external_sync_inbox WHERE snapshot_id=? AND sender='price' AND chunk_key='luciano-wire-v1' LIMIT 2",[$lucianoSnapshot['id']]);
        $lucianoFields=[];
        foreach (tSections() as $section=>$table) $lucianoFields[$section]=tFieldNames($lucianoPdo,$table,$section);
        $lucianoPayload=tPayload($lucianoPdo,$lucianoSnapshot);
        $lucianoVerified=Readback::verifyReady($lucianoHotel,$lucianoWireSha,$lucianoStates,$lucianoSnapshots,
            $lucianoInboxes,$lucianoPayload,$lucianoFields);
        if (!$lucianoPdo->rollBack()) throw new RuntimeException('Could not finish read-only verification');
    } catch (Throwable $error) {
        if ($lucianoPdo->inTransaction()) $lucianoPdo->rollBack();throw $error;
    }
    echo Wire::encode(['read_only'=>true,'utc'=>gmdate('c'),'hotel'=>$lucianoHotel,
        'snapshot_id'=>(string)$lucianoSnapshot['id'],'full_ready_database_readback_verified'=>true,
        'verification'=>$lucianoVerified]),PHP_EOL;
} catch (Throwable $error) { fwrite(STDERR,$error->getMessage().PHP_EOL);exit(1); }
