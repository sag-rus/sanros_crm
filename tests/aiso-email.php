<?php
// Run the real AISO persistence block and CreateClient with an in-memory DB double.
// Never bootstrap the endpoint: that would connect to the production database.
require_once __DIR__.'/../core/class/turist/CreateClient.Class.php';
$functions = file_get_contents(__DIR__.'/../core/functions.php');
$start = strpos($functions, 'function clear_email(');
$end = strpos($functions, 'function clear_telephone(', $start);
if ($start === false || $end === false) { throw new RuntimeException('Email validator not found'); }
eval(substr($functions, $start, $end - $start));
function first_symbol_to_title($v) { return $v; }
function clean_user_name_data($v) { return $v; }
function clear_telephone($v) { return preg_replace('/[^0-9]/', '', $v); }
function get_address_by_ip($v) { return ''; }
class ConfigCRM {
    public $connect;
    private static $instance;
    public static function getInstance() {
        if (!self::$instance) { self::$instance = new self; }
        return self::$instance;
    }
}
class AisoMemoryDatabase {
    public $clients = [];
    public $note;
    public $requestClient;
    public function getOne($sql, ...$args) {
        foreach ($this->clients as $id => $row) {
            $phone = end($args);
            if ($row['telephone'] === $phone && (count($args) === 1 || $row['email'] === '' || $row['email'] === $args[0])) {
                return $id;
            }
        }
        return null;
    }
    public function getRow($sql, $id) { return $this->clients[$id]; }
    public function query($sql, ...$args) {
        if (strpos($sql, 'INSERT INTO klient(') === 0) {
            $this->clients[count($this->clients) + 1] = ['email' => $args[5], 'telephone' => $args[4], 'otch' => $args[3]];
        } elseif (strpos($sql, 'UPDATE klient SET email=') === 0) {
            $this->clients[$args[1]]['email'] = $args[0];
        } elseif (strpos($sql, 'INSERT INTO `reckoning`') === 0) {
            $this->note = $args[0];
            if (!preg_match("/NULL, '([0-9]+)', NULL, '0', '1'/", $sql, $matches)) {
                throw new RuntimeException('Request client link not found');
            }
            $this->requestClient = (int)$matches[1];
        } else {
            throw new RuntimeException('Unexpected database operation');
        }
    }
    public function insertId() { return count($this->clients); }
}
$source = file_get_contents(__DIR__.'/../aiso_save_data.php');
$start = strpos($source, "parse_str(\$_POST['data'], \$data);");
$end = strpos($source, "\$response['ok'] = 1;", $start);
if ($start === false || $end === false) { throw new RuntimeException('AISO block not found'); }
$block = substr($source, $start, $end - $start);
$cases = [
    ['valid', 'Client@example.com', null, 'client@example.com'],
    ['spaces and plus', ' Client+bot@Example.com ', null, 'client+bot@example.com'],
    ['missing', null, null, ''],
    ['empty', '', null, ''],
    ['whitespace', '  ', null, ''],
    ['invalid', 'not-an-email', null, ''],
    ['array', ['client@example.com'], null, ''],
    ['existing empty', 'client@example.com', '', 'client@example.com'],
    ['existing same', 'client@example.com', 'client@example.com', 'client@example.com'],
    ['existing without new email', null, 'old@example.com', 'old@example.com'],
    ['existing different', 'new@example.com', 'old@example.com', 'new@example.com'],
];
foreach ($cases as $case) {
    list($label, $input, $existing, $expected) = $case;
    $connect = new AisoMemoryDatabase;
    if ($existing !== null) { $connect->clients[1] = ['email' => $existing, 'telephone' => '70000000000', 'otch' => '']; }
    ConfigCRM::getInstance()->connect = $connect;
    $fields = ['fio' => 'AISO test', 'phone' => '+70000000000', 'message' => 'Test', 'url' => 'https://example.com', 'ip' => '127.0.0.1'];
    if ($input !== null) { $fields['email'] = $input; }
    $_POST['data'] = http_build_query($fields);
    eval($block);
    $actual = $connect->clients[$connect->requestClient]['email'];
    if (($actual ?: '') !== $expected) { throw new RuntimeException($label.': client email mismatch'); }
    $validInput = is_string($input) ? (clear_email(trim($input)) ?: '') : '';
    $expectedLine = $validInput !== '' ? 'E-mail: '.htmlspecialchars($validInput, ENT_QUOTES, 'UTF-8').'<br>' : '';
    if (($expectedLine !== '' && strpos($connect->note, $expectedLine) === false) || ($expectedLine === '' && strpos($connect->note, 'E-mail:') !== false)) {
        throw new RuntimeException($label.': request email mismatch');
    }
    if ($existing !== null && $existing !== '' && $connect->clients[1]['email'] !== $existing) {
        throw new RuntimeException($label.': previous email overwritten');
    }
    if ($existing === '' && count($connect->clients) !== 1) { throw new RuntimeException('Existing client duplicated'); }
}
echo 'PASS: '.count($cases)." AISO client and request persistence cases; no real DB writes\n";
