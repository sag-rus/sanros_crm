<?php
// Read-only CRM CLI. Emits evidence only; it never calls import, activation or an ACK endpoint.
if (PHP_SAPI!=='cli') exit(1);
require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
require_once __DIR__.'/../app/Support/LucianoWireImportPlan.php';
require_once __DIR__.'/../app/Support/LucianoWireReadback.php';
use App\Support\LucianoWireEnvelope as Wire;
use App\Support\LucianoWireReadback as Readback;

try {
    if (count($argv)!==4 || !in_array($argv[1],['kazan','sochi'],true)
        || !preg_match('/^[a-f0-9]{64}$/D',$argv[3])) throw new RuntimeException('Usage: luciano-wire-verify.php kazan|sochi SNAPSHOT_KEY WIRE_SHA256');
    $hotel=$argv[1];$key=$argv[2];$sha=$argv[3];$object=$hotel==='kazan'?'1096':'1658';
    if (!preg_match('/^reload-v1-luciano-initial-'.$object.'-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D',$key)) {
        throw new RuntimeException('Foreign or unsupported snapshot key');
    }
    // Use the existing deployed connection and reconstruction contract without copying credentials/code.
    $argv=[__FILE__,'crm',dirname(__DIR__)];
    require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';
    require '/home/rustem/price-tonia-auto-sync-v1/common.php';
    $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $pdo->beginTransaction();
    try {
        $states=rows($pdo,"SELECT source,crm_object_id,external_property_key,import_enabled,publish_enabled,active_snapshot_id,stale_after_seconds,timezone FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=? LIMIT 2",[$object]);
        $snapshots=rows($pdo,"SELECT * FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=? ORDER BY id LIMIT 2",[$object]);
        if (count($states)!==1 || count($snapshots)!==1 || $snapshots[0]['snapshot_key']!==$key) throw new RuntimeException('Expected exactly one owned initial snapshot/state');
        $snapshot=$snapshots[0];
        $inboxes=rows($pdo,"SELECT * FROM external_sync_inbox WHERE snapshot_id=? AND sender='price' AND chunk_key='luciano-wire-v1' LIMIT 2",[$snapshot['id']]);
        $fields=[];foreach (tSections() as $section=>$table) $fields[$section]=tFieldNames($pdo,$table,$section);
        $payload=tPayload($pdo,$snapshot);
        $verified=Readback::verify($hotel,$sha,$states,$snapshots,$inboxes,$payload,$fields);
        $pdo->rollBack();
    } catch (Throwable $error) { if ($pdo->inTransaction()) $pdo->rollBack();throw $error; }
    echo Wire::encode(['read_only'=>true,'utc'=>gmdate('c'),'hotel'=>$hotel,'snapshot_id'=>(string)$snapshot['id'],
        'full_database_readback_verified'=>true,'verification'=>$verified]),PHP_EOL;
} catch (Throwable $error) { fwrite(STDERR,$error->getMessage().PHP_EOL);exit(1); }
