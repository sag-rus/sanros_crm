<?php
// Only called by the authenticated price_tonia_ru branch of kostyl_booking.php.
function price_tonia_booking_validate($data, $connect) {
    price_tonia_booking_guests($data);
    $arrival = DateTimeImmutable::createFromFormat('!d.m.Y', (string)$data->date);
    $departure = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$data->departure);
    if (!$arrival || !$departure || $arrival->format('d.m.Y') !== $data->date || $departure->format('Y-m-d') !== $data->departure) throw new RuntimeException('Invalid dates');
    $nights = (int)$arrival->diff($departure)->format('%r%a');
    if ($nights < 1 || $nights > 30 || (int)$data->days !== $nights || (int)$data->adults < 1 || (int)$data->adults > 3 || (int)$data->childs !== 0 || (int)$data->tl !== 0 || (int)$data->bnovo !== 0) throw new RuntimeException('Invalid stay');
    $inclusive = $connect->getOne("SELECT billing_basis FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?i", (int)$data->id_obj) === 'day_inclusive';
    $chargeEnd = $inclusive ? $departure->modify('+1 day')->format('Y-m-d') : $data->departure;
    $maxChargeDays = $inclusive ? 31 : 30;
    $positions = json_decode($data->position, true);
    if (!is_array($positions) || !$positions || count($positions)>$maxChargeDays) throw new RuntimeException('Invalid positions');
    $next = $arrival->format('Y-m-d'); $sum=0; $room=null; $rate=null;
    foreach($positions as $p) {
        if (!isset($p['date'],$p['days'],$p['price'],$p['id_room'],$p['rate']) || $p['date'] !== $next || (int)$p['days'] < 1 || (int)$p['days'] > $maxChargeDays || (int)$p['number'] !== 1 || !in_array((int)$p['type_index'], [1,2], true) || !preg_match('/^\d{1,10}\.\d{2}$/D', (string)$p['price'])) throw new RuntimeException('Invalid segment');
        if ($room !== null && ($room !== (int)$p['id_room'] || $rate !== (int)$p['rate'])) throw new RuntimeException('Mixed rooms');
        $room=(int)$p['id_room']; $rate=(int)$p['rate'];
        $parts=explode('.', $p['price']); $sum+=((int)$parts[0]*100+(int)$parts[1])*(int)$p['days'];
        $next=(new DateTimeImmutable($next))->modify('+'.(int)$p['days'].' days')->format('Y-m-d');
    }
    if (!preg_match('/^\d{1,10}\.\d{2}$/D',(string)$data->sum)) throw new RuntimeException('Invalid amount');
    $parts=explode('.', $data->sum);
    if ($next !== $chargeEnd || $sum !== (int)$parts[0]*100+(int)$parts[1]) throw new RuntimeException('Amount mismatch');
    if (!$connect->getOne("SELECT id FROM room WHERE id=?i AND id_obj=?i AND active=0", $room, (int)$data->id_obj)) throw new RuntimeException('Invalid room');
    if (!$connect->getOne("SELECT c.id FROM external_price_catalog c JOIN external_price_snapshot s ON s.id=c.snapshot_id WHERE s.snapshot_key=?s AND s.crm_object_id=?i AND c.crm_room_id=?i AND c.crm_rate_id=?i AND s.source='price_tonia_ru' LIMIT 1", (string)$data->price_snapshot, (int)$data->id_obj, $room, $rate)) throw new RuntimeException('Invalid catalogue');
    if (!$connect->getOne("SELECT id FROM rate_plan WHERE id=?i AND object=?i AND status=1", $rate, (int)$data->id_obj)) throw new RuntimeException('Invalid rate');
    return $positions;
}

// Keep each submitted person, including guests with identical names.
function price_tonia_booking_guests($data) {
    $guests = isset($data->guests) ? $data->guests : null;
    if (!is_array($guests) || count($guests) !== (int)$data->adults) throw new RuntimeException('Invalid guests');
    $result = [];
    foreach ($guests as $guest) {
        $guest = (array)$guest;
        if (!isset($guest['fio']) || !is_string($guest['fio'])) throw new RuntimeException('Invalid guest');
        $fio = trim(preg_replace('/\s+/u', ' ', strip_tags($guest['fio'])));
        if (mb_strlen($fio) < 2 || mb_strlen($fio) > 200) throw new RuntimeException('Invalid guest name');
        $parts = explode(' ', $fio, 3);
        $result[] = ['surname'=>$parts[0], 'name'=>isset($parts[1])?$parts[1]:'', 'otch'=>isset($parts[2])?$parts[2]:''];
    }
    return $result;
}
function price_tonia_booking_attach_guests($data, $connect, $creator, $bookingId, $firstId) {
    $names = price_tonia_booking_guests($data);
    $ids = [(int)$firstId];
    foreach (array_slice($names, 1) as $name) {
        // No shared contact details: CRM otherwise merges the second person by telephone.
        $guestId = (int)$creator->create_client($name);
        if ($guestId < 1) throw new RuntimeException('Guest creation failed');
        $ids[] = $guestId;
    }
    $connect->query("UPDATE reckoning SET rest=?s, number_turist=?i WHERE id=?i", implode(',', $ids), count($ids), $bookingId);
    return implode(',', $ids);
}
