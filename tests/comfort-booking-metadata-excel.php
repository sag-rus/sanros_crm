<?php
require __DIR__.'/../core/comfort-booking-metadata-excel.php';
function check($value, $message) { if (!$value) throw new RuntimeException($message); }
function rejects($work, $message) {
    try { $work(); } catch (InvalidArgumentException $e) { return; }
    throw new RuntimeException($message);
}
$fields = ['title'=>'=1+2 «Ёж» <текст>', 'description'=>"Описание & \"кавычки\"\nСтрока", 'h1'=>'Заголовок', 'keywords'=>'отель, Казань'];
$rows = [['id'=>110000527, 'name'=>'Отель <Маяк>', 'fields'=>$fields]];
$file = tempnam(sys_get_temp_dir(), 'cb-meta-');
try {
    file_put_contents($file, ComfortBookingMetadataExcel::export($rows));
    $read = ComfortBookingMetadataExcel::read($file);
    check($read === [['line'=>2, 'id'=>110000527, 'fields'=>$fields]], 'XLSX round trip preserves literal text and site ID');
    $zip = new ZipArchive(); $zip->open($file);
    check(strpos($zip->getFromName('xl/worksheets/sheet1.xml'), '<f>') === false, 'export never creates formulas');
    $zip->close();
    file_put_contents($file, ComfortBookingMetadataExcel::export(array_merge($rows,$rows)));
    rejects(function() use($file){ComfortBookingMetadataExcel::read($file);}, 'duplicate IDs rejected before mutation');
    $rows[0]['fields']['title'] = str_repeat('я',501);
    file_put_contents($file, ComfortBookingMetadataExcel::export($rows));
    rejects(function() use($file){ComfortBookingMetadataExcel::read($file);}, 'UTF-8 field length');
    $rows[0]['fields'] = array_fill_keys(ComfortBookingMetadataExcel::FIELDS, '');
    file_put_contents($file, ComfortBookingMetadataExcel::export($rows));
    check(ComfortBookingMetadataExcel::read($file)[0]['fields'] === $rows[0]['fields'], 'empty SEO columns stay empty');
    $rows[0]['id'] = '2e5';
    file_put_contents($file, ComfortBookingMetadataExcel::export($rows));
    rejects(function() use($file){ComfortBookingMetadataExcel::read($file);}, 'noninteger ID rejected');
    $rows[0]['id'] = 123;
    foreach (['formula','entity','row','boolean'] as $bad) {
        file_put_contents($file, ComfortBookingMetadataExcel::export($rows));
        $zip = new ZipArchive(); $zip->open($file);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if($bad==='formula') $xml=str_replace('</worksheet>', '<f>1+1</f></worksheet>', $xml);
        if($bad==='boolean') $xml=str_replace('</worksheet>', '<c t="b"><v>1</v></c></worksheet>', $xml);
        if($bad==='entity') $xml=str_replace('<worksheet', '<!DOCTYPE worksheet [<!ENTITY x SYSTEM "file:///etc/passwd">]><worksheet', $xml);
        if($bad==='row') $xml=str_replace('r="A2"', 'r="ZZ999999"', $xml);
        $zip->addFromString('xl/worksheets/sheet1.xml',$xml);$zip->close();
        rejects(function() use($file){ComfortBookingMetadataExcel::read($file);}, $bad.' rejected');
    }
    file_put_contents($file, 'not Excel');
    rejects(function() use($file){ComfortBookingMetadataExcel::read($file);}, 'invalid workbook');
} finally { unlink($file); }
echo "Metadata Excel checks passed\n";
