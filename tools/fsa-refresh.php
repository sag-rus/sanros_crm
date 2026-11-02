<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__.'/../core/fsa-classification.php';
$options = getopt('', ['apply', 'object:', 'force', 'cached-plan']);
$apply = isset($options['apply']); $only = isset($options['object']) ? (int)$options['object'] : 0;
if (isset($options['object']) && $only < 1) { fwrite(STDERR,"Invalid object ID\n"); exit(1); }
if ($apply && isset($options['cached-plan'])) { fwrite(STDERR,"Cached plan cannot be applied\n"); exit(1); }
$state = getenv('FSA_STATE_DIR') ?: '/var/lib/sanata-fsa';
try {
    if ($apply) {
        $lock = fopen($state.'/refresh.lock','c');
        if (!$lock || !flock($lock, LOCK_EX|LOCK_NB)) throw new RuntimeException('Refresh already running or state directory unavailable');
    }
    $root = dirname(__DIR__); chdir($root);
    require_once $root.'/config.php'; require_once $root.'/core/functions.php'; require_once $root.'/core/lib/Mysql.Class.php';
    $db = connect_to_MySQL_directory();
    $where = $only ? $db->parse('id=?i', $only) : "(accr_id<>'' OR id IN (SELECT id_obj FROM accr_data WHERE id_obj>0))";
    $objects = $db->getAll('SELECT id,name,full_name,address,accr_id,accr_data FROM object WHERE '.$where.' ORDER BY id');
    $memo = []; $failures = 0; $summary = ['objects'=>0,'changed'=>0,'unresolved'=>0,'errors'=>0]; $report = [];
    foreach ($objects as $object) {
        $summary['objects']++;
        try {
            $rows = $db->getAll('SELECT * FROM accr_data WHERE id_obj=?i OR ext_id=?s', $object['id'], $object['accr_id']);
            $anchor = [];
            foreach ($rows as $r) if ($r['ext_id'] === $object['accr_id']) $anchor = json_decode($r['data'],true)['hotel']['main'] ?? [];
            // Search only the existing public registry cache. Never infer a new relation from an owner's name alone.
            $term = preg_replace('/\([^)]*\)/u', '', $object['name']);
            $term = trim(preg_replace('/[%_\\\\-]+/u', '%', $term));
            if (mb_strlen($term,'UTF-8') >= 3) {
                $extra = $db->getAll('SELECT * FROM accr_data WHERE name LIKE ?s LIMIT 61', '%'.$term.'%');
                if (count($extra) <= 60) $rows = array_merge($rows, $extra);
            }
            $candidates = []; $seen = []; $unavailable = false;
            foreach ($rows as $row) {
                $ext = $row['ext_id']; if (isset($seen[$ext])) continue; $seen[$ext] = true;
                $row['main'] = json_decode($row['data'],true)['hotel']['main'] ?? [];
                if (fsaIsBuilding($row['main']) || !fsaNameMatches($object,$row['main'])) continue;
                if ($ext !== $object['accr_id'] && !fsaSameOwnerRegion($anchor,$row['main'])) continue;
                if ((int)$row['id_obj'] !== 0 && (int)$row['id_obj'] !== (int)$object['id']) continue;
                if (!isset($options['cached-plan'])) {
                    if (!isset($memo[$ext])) {
                        try {
                            if (!isset($options['force']) && strtotime(($row['data_datetime'] ?? '').' UTC') > time()-72000) {
                                $decoded = json_decode($row['data'],true); fsaValidate($decoded,$ext);
                                $memo[$ext] = ['main'=>$decoded['hotel']['main'],'data'=>$row['data'], 'checkedAt'=>gmdate('c',strtotime($row['data_datetime'].' UTC'))];
                            } else {
                                $memo[$ext] = fsaFetch($ext); $failures = 0;
                            }
                        } catch (Throwable $e) { $memo[$ext] = ['error'=>$e->getMessage()]; $failures++; }
                    }
                    if (isset($memo[$ext]['error'])) { $unavailable = true; continue; }
                    $row = array_merge($row,$memo[$ext]);
                    // The identity must still match after fetching, including owner and region.
                    if (!fsaSameOwnerRegion($anchor,$row['main'])) { $unavailable = true; continue; }
                    if ($apply) fsaSaveCache($db,$row);
                } else $row['checkedAt'] = $row['data_datetime'];
                $candidates[] = $row;
            }
            if ($unavailable) throw new RuntimeException('Candidate unavailable or changed identity; previous data retained');
            $selected = fsaSelect($object,$candidates,$anchor);
            $entry = ['object_id'=>(int)$object['id'],'name'=>$object['name'],'previous'=>$object['accr_id'],
                'selected'=>$selected ? $selected['ext_id'] : null,'record'=>$selected ? $selected['main']['registerRecord'] : null,
                'status'=>$selected ? $selected['main']['status']['name'] : 'needs_review',
                'candidates'=>array_map(function($r){return ['id'=>$r['ext_id'],'name'=>$r['main']['fullName'],'status'=>$r['main']['status']['name']];},$candidates)];
            if ($selected) $projection = fsaProjection($selected,$selected['checkedAt']);
            else { $summary['unresolved']++; $projection = ['scope'=>'unresolved','reason'=>'No unique whole-property record']; }
            if ($apply && fsaPublish($db,$object,$selected,$projection,$state.'/backups')) $summary['changed']++;
            $report[] = $entry; echo json_encode($entry,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n";
        } catch (Throwable $e) {
            $summary['errors']++; $entry = ['object_id'=>(int)$object['id'],'name'=>$object['name'],'error'=>$e->getMessage()];
            $report[] = $entry; echo json_encode($entry,JSON_UNESCAPED_UNICODE)."\n";
            if ($failures >= 5) throw new RuntimeException('Registry unavailable; stopped after five failures');
        }
    }
    if ($apply) {
        $json = json_encode(['at'=>gmdate('c'),'summary'=>$summary,'objects'=>$report],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        $tmp = $state.'/report.json.tmp';
        if (file_put_contents($tmp,$json,LOCK_EX) !== strlen($json) || !rename($tmp,$state.'/report.json')) throw new RuntimeException('Report write failed');
    }
    echo json_encode(['summary'=>$summary])."\n";
    exit($summary['errors'] ? 1 : 0);
} catch (Throwable $e) { fwrite(STDERR,$e->getMessage()."\n"); exit(1); }
