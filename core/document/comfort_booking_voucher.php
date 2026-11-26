<?php
function cb_voucher_html($id, array $data, array $company) {
    $b=$data['booking'];$s=$data['selection'];$stay=$b['roomStays'][0];
    $html='<page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm"><style>p { margin:0 0 2mm; } td, th { padding:1mm; }</style>';
    $html.='<h2 style="text-align:center">ВАУЧЕР № '.(int)$id.'</h2>';
    foreach ($company as $label=>$value) if ($value!=='') $html.='<p>'.cb_doc_escape($label).': '.cb_doc_escape($value).'</p>';
    $lines=['Объект'=>$data['hotel']['name']??'', 'Номер брони на сайте'=>$b['number'], 'Номер TravelLine'=>$data['externalNumber'],
        'Заезд'=>cb_doc_date($b['arrivalDate']).' '.($b['arrivalTime']??''),'Выезд'=>cb_doc_date($b['departureDate']).' '.($b['departureTime']??''),
        'Номер'=>$s['room'],'Тариф'=>$s['rate'],'Взрослые'=>$stay['adults'],'Дети'=>$stay['children'],
        'Стоимость'=>$stay['total']['amountAfterTaxes'].' '.$b['currencyCode'],
        'Предоплата'=>$b['paymentInfo']['prepayment']['value'].' '.$b['currencyCode'],'Оплата'=>$b['paymentMethodComment']??''];
    foreach ($lines as $label=>$value) $html.='<p><strong>'.cb_doc_escape($label).':</strong> '.cb_doc_escape($value).'</p>';
    $html.='<h3>Гости</h3><table border="1" cellpadding="5" cellspacing="0"><tr><th style="width:180px">Фамилия Имя Отчество</th><th style="width:85px">Дата рождения</th><th style="width:340px">Документ</th></tr>'.cb_doc_guest_rows($data).'</table>';
    $html.=cb_doc_customer($data);
    return $html.'<p>Предъявите ваучер и документы гостей при заселении.</p></page>';
}
function review_cb_voucher($type, $id, $for, array $data) {
    global $directory;
    require_once $directory.'/config.php';$conf=new JConfig;
    $count=filter_var($_GET['dubl']??1,FILTER_VALIDATE_INT);
    if (!$count || $count<1 || $count>10) {http_response_code(400);echo 'Количество ваучеров: от 1 до 10.';return;}
    $company=['Компания'=>$conf->firma,'Телефон'=>$conf->tel_firma,'E-mail'=>$conf->Email,'Сайт'=>$conf->web_site];
    $content=str_repeat(cb_voucher_html($id,$data,$company),$count);
    if ($type==='HTML') {echo $content;return;}
    require_once $directory.'/core/lib/html2PDF/html2pdf.class.php';
    $pdf=new HTML2PDF('P','A4','en',array(0,0,0,0),'UTF-8');$pdf->setDefaultFont('dejavusans');$pdf->WriteHTML($content);
    if ($for==='email') {$file=$directory.'/temp/forms/putevka'.$id.'.pdf';$pdf->Output($file,'F');return $file;}
    $pdf->Output('voucher-'.$id.'.pdf');
}
