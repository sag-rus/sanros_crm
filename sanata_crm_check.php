<?php
// URL для открытия
$url = 'http://sagrus.ru/sanata_crm_check.php';

// Создаем контекст для запроса
$options = array(
    'http' => array(
        'method' => 'GET',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'timeout' => 30
    )
);

$context = stream_context_create($options);

// Пытаемся открыть URL
$response = @file_get_contents($url, false, $context);

?>