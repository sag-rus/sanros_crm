<?php
// Read-only projection of website booking documents. Never writes to klient/payer.
function cb_doc_escape($value) { return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8'); }
function cb_doc_date($value) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/D',(string)$value) ? implode('.',array_reverse(explode('-',$value))) : '';
}
function cb_doc_name(array $person) { return trim(implode(' ',[$person['lastName']??'',$person['firstName']??'',$person['middleName']??''])); }
function cb_doc_identity(array $doc) {
    $parts=[];
    foreach (['passport'=>'Паспорт','passportIssuedBy'=>'Кем выдан','passportIssueDate'=>'Дата выдачи','birthCertificate'=>'Свид. о рождении'] as $key=>$label) {
        $value=$doc[$key]??'';
        if ($value!=='') $parts[]=cb_doc_escape($label.': '.($key==='passportIssueDate'?cb_doc_date($value):$value));
    }
    return implode('<br />',$parts);
}
function cb_doc_guest_rows(array $data) {
    $html='';
    foreach ($data['booking']['roomStays'][0]['guests'] as $i=>$guest) {
        $doc=$data['documents']['guests'][$i]??[];
        $html.='<tr><td style="width:180px;vertical-align:top">'.cb_doc_escape(cb_doc_name($guest)).'</td><td style="width:85px;vertical-align:top">'.cb_doc_escape(cb_doc_date($doc['birthDate']??'')).'</td><td style="width:340px;vertical-align:top">'.cb_doc_identity($doc).'</td></tr>';
    }
    return $html;
}
function cb_doc_customer(array $data) {
    $person=$data['booking']['customer'];$doc=$data['documents']['customer']??[];
    unset($doc['birthCertificate']);
    $html='<p><strong>Заказчик:</strong> '.cb_doc_escape(cb_doc_name($person));
    if (!empty($doc['birthDate'])) $html.='<br />Дата рождения: '.cb_doc_escape(cb_doc_date($doc['birthDate']));
    $identity=cb_doc_identity($doc);if ($identity!=='') $html.='<br />'.$identity;
    foreach (['phone'=>'Телефон','email'=>'E-mail'] as $key=>$label) if (!empty($person[$key])) $html.='<br />'.$label.': '.cb_doc_escape($person[$key]);
    return $html.'</p>';
}
function cb_doc_payer(array $data, $payer) {
    // A separately selected legal entity remains the contractual payer.
    if ((int)($payer['type']??0)===2) return $payer;
    $person=$data['booking']['customer'];$doc=$data['documents']['customer']??[];
    return array_merge(is_array($payer)?$payer:[],['type'=>1,'address'=>'','name'=>cb_doc_escape(cb_doc_name($person)),
        'passport'=>cb_doc_escape($doc['passport']??''),'output'=>cb_doc_escape($doc['passportIssuedBy']??''),
        'date_passport'=>cb_doc_escape(cb_doc_date($doc['passportIssueDate']??'')),
        'telephone'=>cb_doc_escape($person['phone']??''),'email'=>cb_doc_escape($person['email']??'')]);
}
