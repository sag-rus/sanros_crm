<?php
// Install through published Git first. This migration only creates a dormant state, never a snapshot.
if (PHP_SAPI!=='cli') exit(1);
require_once __DIR__.'/../app/Support/LucianoWireEnvelope.php';
require_once __DIR__.'/../app/Support/LucianoWireDestination.php';
require_once __DIR__.'/../app/Support/LucianoStateOnboarding.php';
use App\Support\LucianoWireEnvelope as Wire;
use App\Support\LucianoStateOnboarding as Onboarding;

$pdo=null;$lock=null;
try {
    $mode=$argv[1]??null;$hotel=$argv[2]??null;
    if (!in_array($mode,['preflight','apply'],true) || !in_array($hotel,['kazan','sochi'],true)
        || count($argv)!==($mode==='preflight'?3:6)) throw new RuntimeException('Usage: preflight kazan|sochi OR apply kazan|sochi EVIDENCE_SHA BACKUP_PATH INSTALLED_GIT_SHA');
    $object=$hotel==='kazan'?'1096':'1658';$backup=null;
    if ($mode==='apply') {
        $lucianoEvidenceSha=$argv[3];$backup=$argv[4];$head=$argv[5];
        if (!preg_match('/^[a-f0-9]{64}$/D',$lucianoEvidenceSha) || !preg_match('/^[a-f0-9]{40}$/D',$head)
            || !preg_match('#^/var/tmp/luciano-crm-'.$object.'-before-state-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.json$#D',$backup)) {
            throw new RuntimeException('Invalid onboarding digest, backup path or installed commit');
        }
        $root=dirname(__DIR__);$prefix='cd '.escapeshellarg($root).' && git ';
        $lines=[];$exit=0;exec($prefix.'rev-parse HEAD 2>&1',$lines,$exit);
        if ($exit!==0 || implode("\n",$lines)!==$head) throw new RuntimeException('Installed Git HEAD differs');
        $paths=['tools/luciano-state-onboarding.php','app/Support/LucianoStateOnboarding.php',
            'app/Support/LucianoWireEnvelope.php','app/Support/LucianoWireDestination.php'];
        $quoted=implode(' ',array_map('escapeshellarg',$paths));$lines=[];
        exec($prefix.'ls-files --error-unmatch -- '.$quoted.' 2>&1',$lines,$exit);
        if ($exit!==0 || count($lines)!==count($paths)) throw new RuntimeException('Onboarding code is not tracked in installed Git');
        $lines=[];exec($prefix.'diff --quiet HEAD -- '.$quoted.' 2>&1',$lines,$exit);
        if ($exit!==0) throw new RuntimeException('Owned onboarding code differs from installed commit');
    }
    $argv=[__FILE__,'crm',dirname(__DIR__)];
    require '/home/rustem/price-tonia-auto-sync-v1/bootstrap.php';
    if ($mode==='apply') {
        $lock='luciano_crm_state_'.$object;
        $held=rows($pdo,'SELECT GET_LOCK(?,0) AS held',[$lock]);
        if (count($held)!==1 || (string)$held[0]['held']!=='1') { $lock=null;throw new RuntimeException('Own Luciano state migration is busy'); }
    }
    $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$pdo->beginTransaction();
    $schema=rows($pdo,'SHOW COLUMNS FROM external_price_state');$fields=array_column($schema,'Field');
    $engines=rows($pdo,"SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='external_price_state'");
    if (count($engines)!==1) throw new RuntimeException('Missing state storage engine');
    $objects=rows($pdo,'SELECT id,name FROM object WHERE id=?',[$object]);
    $states=rows($pdo,"SELECT * FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?".($mode==='apply'?' FOR UPDATE':''),[$object]);
    $count=(int)rows($pdo,"SELECT COUNT(*) AS n FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object])[0]['n'];
    $plan=Onboarding::plan($hotel,$objects,$states,$count,$fields,$engines[0]['ENGINE']);
    if ($mode==='preflight') {
        $pdo->rollBack();echo Wire::encode(['preflight_only'=>true,'utc'=>gmdate('c'),'plan'=>$plan]),PHP_EOL;
    } else {
        // The deployed bootstrap/common contracts use $expected in their own include scope.
        // Keep this immutable CLI digest under a dedicated name across bootstrap.
        if (!hash_equals($lucianoEvidenceSha,$plan['evidence_sha256'])) throw new RuntimeException('Scoped onboarding evidence changed; run a fresh preflight');
        if ($plan['action']==='already_dormant') {
            $pdo->rollBack();echo Wire::encode(['already_dormant'=>true,'database_written'=>false,'snapshot_created'=>false,'activated'=>false]),PHP_EOL;
        } else {
            // Preserve the absent-state evidence before the only INSERT. Never reuse or overwrite a backup.
            $data=Wire::encode(['format'=>'luciano-crm-state-before-v1','utc'=>gmdate('c'),'installed_git_sha'=>$head,'plan'=>$plan]);
            $oldMask=umask(0077);$f=@fopen($backup,'x+b');umask($oldMask);
            if (!$f) throw new RuntimeException('Exclusive backup could not be created');
            try {
                if (!flock($f,LOCK_EX|LOCK_NB)) throw new RuntimeException('Backup lock failed');
                $stat=fstat($f);
                if (($stat['mode']&0170000)!==0100000 || ($stat['mode']&0777)!==0600) throw new RuntimeException('Backup must be regular 0600 DATA');
                $offset=0;while ($offset<strlen($data)) { $n=fwrite($f,substr($data,$offset));if (!$n) throw new RuntimeException('Backup write failed');$offset+=$n; }
                if (!fflush($f)) throw new RuntimeException('Backup flush failed');
                rewind($f);if (stream_get_contents($f)!==$data) throw new RuntimeException('Backup read-back failed');
                $pathStat=lstat($backup);$after=fstat($f);
                if (!$pathStat || is_link($backup) || $pathStat['dev']!==$after['dev'] || $pathStat['ino']!==$after['ino']
                    || $after['size']!==strlen($data)) throw new RuntimeException('Backup file identity changed');
            } finally { fclose($f); }
            $values=Onboarding::insertValues($plan,gmdate('Y-m-d H:i:s'));
            $sql='INSERT INTO external_price_state (`'.implode('`,`',array_keys($values)).'`) VALUES ('.implode(',',array_fill(0,count($values),'?')).')';
            $stmt=$pdo->prepare($sql);$stmt->execute(array_values($values));
            if ($stmt->rowCount()!==1) throw new RuntimeException('Expected exactly one dormant state INSERT');
            $after=rows($pdo,"SELECT * FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?",[$object]);
            $countAfter=(int)rows($pdo,"SELECT COUNT(*) AS n FROM external_price_snapshot WHERE source='price_tonia_ru' AND crm_object_id=?",[$object])[0]['n'];
            $verified=Onboarding::plan($hotel,$objects,$after,$countAfter,$fields,$engines[0]['ENGINE']);
            if ($verified['action']!=='already_dormant') throw new RuntimeException('Dormant state read-back failed');
            $pdo->commit();
            echo Wire::encode(['object_id'=>$object,'dormant_state_created'=>true,'installed_git_sha'=>$head,
                'backup_path'=>$backup,'backup_sha256'=>hash('sha256',$data),'database_written'=>true,
                'hotel_modified'=>false,'snapshot_created'=>false,'activated'=>false,'delivery_ready'=>false]),PHP_EOL;
        }
    }
} catch (Throwable $error) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR,$error->getMessage().PHP_EOL);exit(1);
} finally {
    if ($pdo && $lock!==null) rows($pdo,'SELECT RELEASE_LOCK(?) AS released',[$lock]);
}
