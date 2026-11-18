<?php
require_once __DIR__.'/lib/excel/SimpleXLSX.php';
require_once __DIR__.'/lib/excel/SimpleXLSXGen.php';

final class ComfortBookingMetadataExcel
{
    const HEADERS = ['ID объекта', 'Название', 'Title', 'Description', 'H1', 'Keywords'];
    const FIELDS = ['title', 'description', 'h1', 'keywords'];
    const LIMITS = [500, 2000, 500, 2000];
    const MAX_BYTES = 5242880;
    const MAX_ROWS = 5000;

    public static function export(array $rows): string
    {
        $data = [self::HEADERS];
        foreach ($rows as $row) {
            $cells = [(string)$row['id'], (string)$row['name']];
            foreach (self::FIELDS as $key) {
                $cells[] = (string)$row['fields'][$key];
            }
            // Raw strings prevent Excel formulas and the library's HTML/type autodetection.
            $data[] = array_map(['Shuchkin\SimpleXLSXGen', 'raw'], $cells);
        }
        $book = \Shuchkin\SimpleXLSXGen::fromArray($data, 'Метатеги');
        $book->freezePanes('C2')->autoFilter('A1:F'.count($data));
        foreach ([18, 42, 70, 90, 65, 65] as $i => $width) {
            $book->setColWidth($i + 1, $width);
        }
        return (string)$book;
    }

    public static function read(string $path): array
    {
        self::checkArchive($path);
        $book = \Shuchkin\SimpleXLSX::parseFile($path);
        if (!$book || $book->sheetsCount() !== 1) {
            throw new InvalidArgumentException('Нужен файл .xlsx с одним листом в формате выгрузки.');
        }
        list($columns, $height) = $book->dimension();
        if ($columns !== 6 || $height > self::MAX_ROWS + 1) {
            throw new InvalidArgumentException('В файле должно быть 6 колонок и не более 5000 объектов.');
        }
        $result = [];
        $seen = [];
        foreach ($book->readRows(0, self::MAX_ROWS + 1) as $index => $cells) {
            $cells = array_map(function ($value) {
                return trim(str_replace(["\r\n", "\r"], "\n", (string)$value));
            }, $cells);
            if ($index === 0) {
                if ($cells !== self::HEADERS) {
                    throw new InvalidArgumentException('Заголовки колонок должны совпадать с выгрузкой: '.implode(', ', self::HEADERS).'.');
                }
                continue;
            }
            if (implode('', $cells) === '') {
                continue;
            }
            $line = $index + 1;
            if (count($cells) !== 6 || !preg_match('/^[1-9][0-9]{0,9}$/D', $cells[0]) ||
                (float)$cells[0] > 4294967295 || isset($seen[$cells[0]])) {
                throw new InvalidArgumentException('Строка '.$line.': некорректный или повторный ID объекта.');
            }
            $fields = [];
            foreach (self::FIELDS as $i => $key) {
                $value = $cells[$i + 2];
                if (!mb_check_encoding($value, 'UTF-8') || mb_strlen($value, 'UTF-8') > self::LIMITS[$i] ||
                    preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $value)) {
                    throw new InvalidArgumentException('Строка '.$line.': проверьте '.$key.' (до '.self::LIMITS[$i].' символов).');
                }
                $fields[$key] = $value;
            }
            $seen[$cells[0]] = true;
            $result[] = ['line' => $line, 'id' => (int)$cells[0], 'fields' => $fields];
        }
        if (!$result) {
            throw new InvalidArgumentException('В файле нет объектов для обновления.');
        }
        return $result;
    }

    private static function checkArchive(string $path): void
    {
        if (!is_file($path) || filesize($path) > self::MAX_BYTES) {
            throw new InvalidArgumentException('Размер файла .xlsx должен быть не больше 5 МБ.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CHECKCONS) !== true) {
            throw new InvalidArgumentException('Не удалось открыть файл .xlsx.');
        }
        try {
            $total = 0;
            if ($zip->numFiles > 100) {
                throw new InvalidArgumentException('Слишком сложный файл. Используйте формат выгрузки.');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                $total += $entry['size'];
                if ($total > 33554432) {
                    throw new InvalidArgumentException('Слишком большой объём данных в Excel.');
                }
                // Read with a bound before the reader unpacks the workbook in memory.
                $data = $zip->getFromIndex($i, $entry['size'] + 1);
                if ($data === false || strlen($data) !== $entry['size']) {
                    throw new InvalidArgumentException('Повреждён файл Excel.');
                }
                if (preg_match('/\.(xml|rels)$/i', $entry['name'])) {
                    self::checkXml($data);
                }
            }
        } finally {
            $zip->close();
        }
    }

    private static function checkXml(string $xml): void
    {
        // Reject alternate XML encodings and DTDs before the library parses any XML.
        if (strpos($xml, "\0") !== false || preg_match('/<!DOCTYPE|<!ENTITY/i', $xml)) {
            throw new InvalidArgumentException('Недопустимое содержимое Excel.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $doc = new DOMDocument();
            if (!$doc->loadXML($xml, LIBXML_NONET)) {
                throw new InvalidArgumentException('Повреждён XML в файле Excel.');
            }
            $xpath = new DOMXPath($doc);
            if ($xpath->query('//*[local-name()="f"] | //*[local-name()="c" and (@t="e" or @t="b")]')->length) {
                throw new InvalidArgumentException('Ячейки должны содержать текстовые значения. Замените формулы их значениями.');
            }
            foreach ($xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]') as $row) {
                if ((int)$row->getAttribute('r') > self::MAX_ROWS + 1) {
                    throw new InvalidArgumentException('В файле должно быть не более 5000 строк объектов.');
                }
                foreach ($row->childNodes as $cell) {
                    if ($cell instanceof DOMElement && $cell->localName === 'c' &&
                        !preg_match('/^[A-F][1-9][0-9]{0,3}$/D', $cell->getAttribute('r'))) {
                        throw new InvalidArgumentException('Используйте шесть колонок из выгрузки.');
                    }
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
