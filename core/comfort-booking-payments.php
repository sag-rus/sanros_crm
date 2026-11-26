<?php
// Read the ordinary CRM payment ledger; no separate CB payments or bank operations.
function cb_payment_row($db, $sql, $id) {
    if ($db instanceof PDO) { $q=$db->prepare($sql);$q->execute([(int)$id]);return $q->fetch(PDO::FETCH_ASSOC)?:[]; }
    return $db->getRow(str_replace('?', '?i', $sql), (int)$id)?:[];
}
function cb_customer_payments($db, $id) {
    // 0 = cancelled, 1 = bank hold awaiting confirmation, 2 = posted.
    $r=cb_payment_row($db, "SELECT COALESCE(SUM(CASE WHEN status=2 AND type IN (1,2) THEN sum ELSE 0 END),0) AS received, COALESCE(SUM(CASE WHEN status=2 AND type=5 THEN sum ELSE 0 END),0) AS refunded, COALESCE(SUM(CASE WHEN status=1 AND type IN (1,2) THEN sum ELSE 0 END),0) AS pending FROM payment WHERE schet=? AND class='schet' AND pay_method<>4", $id);
    $out=[];foreach(['received','refunded','pending'] as $key)$out[$key]=number_format((float)($r[$key]??0),2,'.','');
    $out['paid']=number_format((float)$out['received']-(float)$out['refunded'],2,'.','');
    return $out;
}
function cb_customer_total($db, $id, $bookingTotal) {
    // Match ordinary invoice deductions. The CB 12% supplier reward is never a deduction.
    $r=cb_payment_row($db,'SELECT agency,id_dis,id_com,commission_value FROM reckoning WHERE id=?',$id);
    $total=(float)$bookingTotal;
    if(!empty($r['agency'])) {
        $commission=cb_payment_row($db,'SELECT value FROM commission WHERE id=?',$r['id_com']);
        $total-=(float)$r['commission_value']>0?(float)$r['commission_value']:round((float)$bookingTotal*(float)($commission['value']??0)/100,2);
    } elseif(!empty($r['id_dis'])) {
        $d=cb_payment_row($db,'SELECT value,type FROM discount WHERE id=?',$r['id_dis']);
        if($d)$total-=(int)$d['type']===1?(float)$bookingTotal*(float)$d['value']/100:(float)$d['value'];
    }
    if(empty($r['agency'])) {
        $bonus=cb_payment_row($db,'SELECT sum FROM bonus WHERE schet=? AND sum<0 LIMIT 1',$id);
        $total-=abs((float)($bonus['sum']??0));
    }
    return number_format(max(0,$total),2,'.','');
}
function cb_payment_summary($db, $id, $bookingTotal, $cancelled=false) {
    $p=cb_customer_payments($db,$id);$p['total']=cb_customer_total($db,$id,$bookingTotal);
    $difference=(int)round((float)$p['total']*100)-(int)round((float)$p['paid']*100);
    $p['remaining']=number_format(max(0,$difference)/100,2,'.','');
    $p['overpaid']=number_format(max(0,-$difference)/100,2,'.','');
    $p['state']=$cancelled?'cancelled':($difference<0?'overpaid':($difference===0?'paid':((float)$p['paid']>0?'partial':'unpaid')));
    return $p;
}
function cb_invoice_status($status, $isCb) {
    return (int)$status===2 || ($isCb && (int)$status===1) ? 3 : (int)$status;
}
