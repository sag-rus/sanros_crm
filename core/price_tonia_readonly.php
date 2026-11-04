<?php
// Both PRICE schemes own their prices; manual CRM editors must not overwrite them.
function price_tonia_readonly_url($db, $objectId) {
    $objectId = (int)$objectId;
    if ($objectId < 1) return null;
    if ($db->getOne('SELECT object_id FROM price_live_catalogue WHERE object_id=?i', $objectId)) {
        return 'https://price.tonia.ru/live-catalogues';
    }
    $state = $db->getRow("SELECT external_property_key FROM external_price_state WHERE source='price_tonia_ru' AND crm_object_id=?i AND (import_enabled=1 OR publish_enabled=1)", $objectId);
    if (!$state) return null;
    $key = (string)$state['external_property_key'];
    return 'https://price.tonia.ru/'.(ctype_digit($key) && (int)$key > 0 ? 'properties/'.(int)$key : '');
}
function price_tonia_readonly_notice($url) {
    return '<div class="alert alert-info">Объект подключен через микросервис <a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener noreferrer">price.tonia.ru</a></div>';
}
function price_tonia_block_price_write($db, array $references) {
    $objects = [];
    foreach ($references as $kind => $ids) foreach (array_unique((array)$ids) as $id) {
        $id = (int)$id;
        if ($id < 1) continue;
        if ($kind === 'object') $objects[] = $id;
        elseif ($kind === 'price') {
            // Resolve both sides from the stored row, not the submitted object ID.
            $row = $db->getRow('SELECT id_room,id_range FROM price WHERE id=?i', $id);
            if ($row) {
                $objects[] = $db->getOne('SELECT id_obj FROM room WHERE id=?i', $row['id_room']);
                $objects[] = $db->getOne('SELECT id_obj FROM ranges WHERE id=?i', $row['id_range']);
            }
        } else {
            $tables = ['date'=>'date_price', 'range'=>'ranges', 'room'=>'room'];
            if (!isset($tables[$kind])) throw new InvalidArgumentException('Unknown price reference');
            $objects[] = $db->getOne('SELECT id_obj FROM '.$tables[$kind].' WHERE id=?i', $id);
        }
    }
    foreach (array_unique($objects) as $objectId) {
        if (price_tonia_readonly_url($db, $objectId)) {
            http_response_code(403);
            echo 'Объект подключен через микросервис price.tonia.ru. Ручное редактирование цен недоступно.';
            return true;
        }
    }
    return false;
}
