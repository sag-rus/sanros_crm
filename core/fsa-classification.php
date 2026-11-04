<?php
// PHP 7.1 compatible. Only whole-property registry records may be published.
function fsaText($value) {
    $value = mb_strtolower((string)$value, 'UTF-8');
    $value = trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', str_replace('ё', 'е', $value)));
    return str_replace(['публичное акционерное общество','открытое акционерное общество','закрытое акционерное общество',
        'акционерное общество','общество с ограниченной ответственностью'], ['пао','оао','зао','ао','ооо'], $value);
}
function fsaIsBuilding($main) {
    return (bool)preg_match('/\b(корпус[\p{L}]*|корп\.|литер[\p{L}]*|коттедж(?:и|ей)?\b|апартамент[\p{L}]*|виллы\b|спальн[\p{L}]*\s+здани[\p{L}]*)/ui',
        ($main['fullName'] ?? '').' '.($main['shortName'] ?? ''));
}
function fsaNameMatches($object, $main) {
    $name = preg_replace('/\([^)]*\)/u', '', $object['name']);
    $name = fsaText($name);
    $name = trim(preg_replace('/\b(санаторий|профилакторий|пансионат|отель|гостиница|клиника|спа|spa)\b/u', '', $name));
    $name = preg_replace('/\s+/u', ' ', $name);
    if (mb_strlen($name, 'UTF-8') < 3) return false;
    foreach (['shortName', 'fullName'] as $field) {
        $source = fsaText($main[$field] ?? '');
        $owner = fsaText($main['ownerName'] ?? '');
        // A resort name appearing only in the owner's name is not a match (e.g. Siberia / Kurort Belokurikha).
        if ($owner !== '' && $source !== $owner && substr($source, -strlen($owner)) === $owner) {
            $source = trim(substr($source, 0, -strlen($owner)));
        }
        if (strpos(' '.$source.' ', ' '.$name.' ') !== false) return true;
    }
    return false;
}
function fsaSameOwnerRegion($a, $b) {
    return !empty($a['ownerInn']) && (string)$a['ownerInn'] === (string)($b['ownerInn'] ?? '')
        && !empty($a['region']['id']) && (string)$a['region']['id'] === (string)($b['region']['id'] ?? '');
}
function fsaValidate($data, $id) {
    $main = $data['hotel']['main'] ?? null;
    if (!is_array($data) || ($data['resortId'] ?? '') !== $id || !is_array($main)
        || !is_string($main['registerRecord'] ?? null) || trim($main['registerRecord']) === ''
        || !is_string($main['fullName'] ?? null) || trim($main['fullName']) === ''
        || !is_string($main['status']['name'] ?? null) || trim($main['status']['name']) === '') {
        throw new RuntimeException('Invalid registry response');
    }
    if (isset($main['status']['endDate']) && $main['status']['endDate'] !== '') {
        $date = $main['status']['endDate'];
        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)
            || date('Y-m-d', strtotime($date)) !== $date) throw new RuntimeException('Invalid registry date');
    }
    return $main;
}
function fsaSelect($object, $candidates, $anchor) {
    $eligible = [];
    foreach ($candidates as $row) {
        $main = $row['main'];
        if (fsaIsBuilding($main) || !fsaNameMatches($object, $main)) continue;
        if ($row['ext_id'] !== $object['accr_id'] && !fsaSameOwnerRegion($anchor, $main)) continue;
        if ((int)$row['id_obj'] !== 0 && (int)$row['id_obj'] !== (int)$object['id']) continue;
        $eligible[$row['ext_id']] = $row;
    }
    if (count($eligible) === 1) return reset($eligible);
    // A single active property may supersede terminated duplicates, never another active property.
    $active = array_filter($eligible, function ($r) { return ($r['main']['status']['name'] ?? '') === 'Действует'; });
    if (count($active) === 1) {
        foreach ($eligible as $row) {
            if (!in_array($row['main']['status']['name'], ['Действует', 'Прекращен', 'Прекращён', 'Исключен', 'Исключён'], true)) return null;
        }
        return reset($active);
    }
    return null;
}
function fsaProjection($row, $checkedAt) {
    $main = $row['main'];
    $result = ['registerRecord'=>$main['registerRecord'], 'status'=>$main['status']['name'],
        'scope'=>'object', 'sourceId'=>$row['ext_id'], 'sourceName'=>$main['fullName'], 'checkedAt'=>$checkedAt];
    if (!empty($main['status']['endDate'])) $result['endDate'] = $main['status']['endDate'];
    return $result;
}
function fsaFetch($id) {
    if (!preg_match('/^[a-f0-9-]{36}$/D', $id)) throw new RuntimeException('Invalid registry ID');
    $path = getenv('FSA_API_KEY_FILE') ?: '/etc/sanata/fsa-api-key';
    $key = getenv('FSA_API_KEY') ?: (is_readable($path) ? trim(file_get_contents($path)) : '');
    if ($key === '' || preg_match('/[\r\n]/', $key)) throw new RuntimeException('Registry credential unavailable');
    $curl = curl_init('https://tourism.fsa.gov.ru/api/v1/export/resorts/'.$id.'/get');
    $body = ''; $overflow = false;
    curl_setopt_array($curl, [CURLOPT_HTTPHEADER=>['Api-Key: '.$key, 'Accept: application/json'],
        CURLOPT_CONNECTTIMEOUT=>10, CURLOPT_TIMEOUT=>30, CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_SSL_VERIFYPEER=>true,
        CURLOPT_WRITEFUNCTION=>function ($ch, $part) use (&$body, &$overflow) {
            if (strlen($body) + strlen($part) > 4000000) { $overflow = true; return 0; }
            $body .= $part; return strlen($part);
        }]);
    $ok = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl);
    if ($ok === false || $status !== 200 || $overflow) throw new RuntimeException('Registry HTTP failure '.$status);
    $data = json_decode($body, true); fsaValidate($data, $id);
    return ['data'=>$body, 'main'=>$data['hotel']['main'], 'checkedAt'=>gmdate('c')];
}
function fsaQuery($db, $sql, ...$args) {
    $result = $db->query($sql, ...$args);
    if ($result === false) throw new RuntimeException('Classification database write failed');
    return $result;
}
function fsaSaveCache($db, $row) {
    $main = $row['main'];
    $addresses = array_map(function ($a) { return $a['name'] ?? ''; }, $main['addressList'] ?? []);
    $address = implode('; ', $addresses);
    fsaQuery($db, 'UPDATE accr_data SET name=?s,address=?s,data=?s,data_datetime=?s,search=?s WHERE id=?i AND ext_id=?s',
        mb_substr($main['fullName'],0,255), mb_substr($address,0,255), $row['data'], gmdate('Y-m-d H:i:s',strtotime($row['checkedAt'])),
        $main['fullName'].' '.$address.' '.$main['registerRecord'], $row['id'], $row['ext_id']);
}
function fsaPublish($db, $object, $row, $projection, $backupDir) {
    $lockName = 'sanata-fsa:'.($row ? $row['ext_id'] : 'object-'.$object['id']);
    if ((int)$db->getOne('SELECT GET_LOCK(?s, 0)', $lockName) !== 1) throw new RuntimeException('Classification is being updated; retry');
    try {
        if ($row) {
            $claim = $db->getRow('SELECT id_obj FROM accr_data WHERE id=?i AND ext_id=?s', $row['id'], $row['ext_id']);
            if (!$claim || ((int)$claim['id_obj'] !== 0 && (int)$claim['id_obj'] !== (int)$object['id'])) {
                throw new RuntimeException('Registry record claimed by another object; review required');
            }
        }
        return fsaPublishLocked($db, $object, $row, $projection, $backupDir);
    } finally { $db->getOne('SELECT RELEASE_LOCK(?s)', $lockName); }
}
function fsaPublishLocked($db, $object, $row, $projection, $backupDir) {
    $id = $row ? $row['ext_id'] : $object['accr_id'];
    $json = json_encode($projection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) throw new RuntimeException('Invalid projection');
    if ($object['accr_id'] === $id && $object['accr_data'] === $json) return false;
    $links = $db->getAll('SELECT id,ext_id,id_obj FROM accr_data WHERE id_obj=?i', $object['id']);
    $backup = json_encode(['at'=>gmdate('c'),'object'=>$object,'links'=>$links], JSON_UNESCAPED_UNICODE);
    $file = $backupDir.'/'.gmdate('Ymd-His').'-'.$object['id'].'-'.bin2hex(random_bytes(4)).'.json';
    if (!is_dir($backupDir) || file_put_contents($file, $backup, LOCK_EX) !== strlen($backup)) throw new RuntimeException('Backup unavailable');
    chmod($file, 0600);
    // object is MyISAM: one compare-and-swap UPDATE publishes all fields, without pretending to have a cross-table transaction.
    fsaQuery($db, 'UPDATE object SET accr_id=?s,accr_data=?s,synchronized=0 WHERE id=?i AND accr_id=?s AND accr_data=?s',
        $id, $json, $object['id'], $object['accr_id'], $object['accr_data']);
    if ($db->affectedRows() !== 1) throw new RuntimeException('Object changed concurrently; retry');
    if ($row) {
        fsaQuery($db, 'UPDATE accr_data SET id_obj=0 WHERE id_obj=?i AND ext_id<>?s', $object['id'], $id);
        fsaQuery($db, 'UPDATE accr_data SET id_obj=?i WHERE id=?i AND (id_obj=0 OR id_obj=?i)', $object['id'], $row['id'], $object['id']);
    }
    return true;
}
