<?php
require __DIR__.'/../core/comfort-booking-documents.php';
require __DIR__.'/../core/document/comfort_booking_voucher.php';
function docCheck($ok,$message) {if(!$ok)throw new RuntimeException($message);}
$person=['firstName'=>'Имя','lastName'=>'Тестовый','middleName'=>'Заказчик','phone'=>'+70000000000'];
$guest=['firstName'=>'Ребёнок','lastName'=>'Тестовый'];
$data=['booking'=>['number'=>'fixture','arrivalDate'=>'2026-12-15','departureDate'=>'2026-12-17','arrivalTime'=>'14:00','departureTime'=>'12:00','currencyCode'=>'RUB','customer'=>$person,'paymentInfo'=>['prepayment'=>['value'=>'0.00']],'paymentMethodComment'=>'Оплата при заезде','roomStays'=>[['adults'=>1,'children'=>1,'total'=>['amountAfterTaxes'=>'1600.00'],'guests'=>[$person,$guest]]]],'selection'=>['room'=>'Номер <test>','rate'=>'Основной'],'hotel'=>['name'=>'Тестовый отель'],'externalNumber'=>'TL-fixture',
'documents'=>['customer'=>['birthDate'=>'1980-01-02','passport'=>'0000 000000','passportIssuedBy'=>'Тестовый орган','passportIssueDate'=>'2020-02-03','birthCertificate'=>'FORBIDDEN-CUSTOMER-CERT'],
'guests'=>[['birthDate'=>'1980-01-02','passport'=>'1111 111111','passportIssuedBy'=>'Орган <test>','passportIssueDate'=>'2020-02-03'],['birthDate'=>'2019-01-02','birthCertificate'=>'I-ТЕСТ №000000']]]];
$html=cb_voucher_html(999,$data,['Компания'=>'Тест']);
foreach(['ВАУЧЕР','TL-fixture','15.12.2026','17.12.2026','02.01.1980','03.02.2020','I-ТЕСТ №000000','Тестовый орган','1111 111111','0000 000000'] as $text)docCheck(strpos($html,$text)!==false,'Missing '.$text);
docCheck(strpos($html,'<test>')===false&&strpos($html,'&lt;test&gt;')!==false,'HTML escaping');
docCheck(strpos($html,'FORBIDDEN-CUSTOMER-CERT')===false,'no certificate for customer');
$payer=cb_doc_payer($data,['type'=>1,'passport'=>'STALE','name'=>'OLD']);docCheck($payer['passport']==='0000 000000'&&$payer['name']==='Тестовый Имя Заказчик','website customer overrides stale payer in memory');
$legal=['type'=>2,'name'=>'Company'];docCheck(cb_doc_payer($data,$legal)===$legal,'legal payer retained');
foreach(['contract.php','old_contract.php'] as $file){$code=file_get_contents(__DIR__.'/../core/document/'.$file);docCheck(strpos($code,'cb_doc_guest_rows($cbDocument)')!==false&&strpos($code,'cb_doc_customer($cbDocument)')!==false,'both contracts use local data');}
foreach(['core/document/obmen.php','core/status.php'] as $file)docCheck(strpos(file_get_contents(__DIR__.'/../'.$file),'cbr_document_guard($connect,$id,$cbDocument)')!==false,'voucher guarded on dialog and download');
if (($argv[1]??'')==='--pdf') {
    require __DIR__.'/../core/lib/html2PDF/html2pdf.class.php';
    $pdf=new HTML2PDF('P','A4','en',array(0,0,0,0),'UTF-8');$pdf->WriteHTML($html);$bytes=$pdf->Output('fixture.pdf','S');
    docCheck(substr($bytes,0,5)==='%PDF-','PDF generated');
    file_put_contents(sys_get_temp_dir().'/cb-document-fixture.pdf',$bytes);
    echo 'PDF fixture: '.strlen($bytes)." bytes\n";
}
echo "CRM document rendering checks passed\n";
