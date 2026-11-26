<?php
// Commission for the authenticated Comfort Booking website intake only.
const CB_REWARD_PERCENT = 12;

function cb_intake_reward($connect, $object, $date) {
    return defined('CB_SITE_INTAKE') && CB_SITE_INTAKE
        ? CB_REWARD_PERCENT : get_reward_object($connect, $object, $date);
}

function cb_position_reward($connect, $requestId, $fallback) {
    $key = $connect->getOne('SELECT cb_submission_key FROM reckoning WHERE id=?i', (int)$requestId);
    return !empty($key) ? CB_REWARD_PERCENT : $fallback;
}

/** Caller holds cbr_lock and has saved the before-image before applying a backfill. */
function cb_reward_rows(PDO $db, $id) {
    $q = $db->prepare("SELECT p.id,p.schet,p.reward FROM position_reck p JOIN reckoning r ON r.id=p.schet WHERE r.id=? AND r.cb_submission_key IS NOT NULL AND r.cb_submission_key<>'' AND p.id_room>0 AND (p.id_service IS NULL OR p.id_service=0) AND (p.reward IS NULL OR p.reward<>?) ORDER BY p.id");
    $q->execute([(int)$id, CB_REWARD_PERCENT]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}
function cb_backfill_reward(PDO $db, $id, array $before) {
    if (!$before) return 0;
    if (cb_reward_rows($db, $id) !== $before) throw new RuntimeException('Commission rows changed; rerun the plan.');
    $q = $db->prepare('SELECT status,status_san FROM reckoning WHERE id=?');
    $q->execute([(int)$id]);
    $request = $q->fetch(PDO::FETCH_ASSOC);
    $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow'));
    $q = $db->prepare('INSERT INTO history_schet (date,time,id_schet,id_user,new_status,new_status_san,note) VALUES (?,?,?,?,?,?,?)');
    $q->execute([$now->format('Y-m-d'),$now->format('H:i:s'),(int)$id,0,$request['status'],$request['status_san'],
        'Вознаграждение Comfort Booking установлено 12%. Прежние значения позиций: '.json_encode($before,JSON_UNESCAPED_UNICODE)]);
    $changed = 0;
    foreach ($before as $row) {
        $q = $db->prepare('UPDATE position_reck SET reward=? WHERE id=? AND schet=? AND (reward=? OR (reward IS NULL AND ? IS NULL))');
        $q->execute([CB_REWARD_PERCENT,$row['id'],(int)$id,$row['reward'],$row['reward']]);
        if ($q->rowCount() !== 1) throw new RuntimeException('Commission row changed during migration.');
        $changed++;
    }
    return $changed;
}
