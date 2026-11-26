<?php
// Plan by default; run only from the published CRM checkout. No booking API mutations.
if (PHP_SAPI !== 'cli') exit(1);
require dirname(__DIR__).'/core/comfort-booking-request-state.php';
$mode = $argv[1] ?? 'plan';
if (!in_array($mode,['plan','--apply'],true)) throw new RuntimeException('Use plan or --apply');
$db = cbr_db();
$ids = $db->query("SELECT DISTINCT r.id FROM reckoning r JOIN position_reck p ON p.schet=r.id WHERE r.cb_submission_key IS NOT NULL AND r.cb_submission_key<>'' AND p.id_room>0 AND (p.id_service IS NULL OR p.id_service=0) AND (p.reward IS NULL OR p.reward<>12) ORDER BY r.id")->fetchAll(PDO::FETCH_COLUMN);
$changed = 0;
$backup = null;
foreach ($ids as $id) {
    cbr_lock($db,$id);
    try {
        $rows = cb_reward_rows($db,$id);
        if (!$rows) continue;
        if ($mode === '--apply') {
            if ($backup === null) {
                $backup = '/home/rustem/cb-reward-backup-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
                if (!mkdir($backup,0700)) throw new RuntimeException('Backup directory failed');
            }
            $path = $backup.'/'.(int)$id.'.json';
            $json = json_encode($rows);
            if ($json === false) throw new RuntimeException('Backup encoding failed');
            $oldMask = umask(0077);
            $written = file_put_contents($path,$json,LOCK_EX);
            umask($oldMask);
            if ($written !== strlen($json) || file_get_contents($path) !== $json) throw new RuntimeException('Backup failed');
            $changed += cb_backfill_reward($db,$id,$rows);
        }
        echo json_encode(['requestId'=>(int)$id,'positions'=>$rows,'target'=>CB_REWARD_PERCENT]),PHP_EOL;
    } finally { cbr_unlock($db,$id); }
}
echo json_encode(['mode'=>$mode,'requests'=>count($ids),'changedPositions'=>$changed,'backup'=>$backup]),PHP_EOL;
