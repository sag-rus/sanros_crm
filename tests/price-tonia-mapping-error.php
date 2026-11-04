<?php
require_once __DIR__.'/../core/price_tonia_live.php';
$cases=[
    [0,4248,0,'номер не сопоставлен с Price'],
    [1,null,0,'тариф не выбран'],
    [0,null,0,'номер не сопоставлен с Price; тариф не выбран'],
    [0,0,0,'номер не сопоставлен с Price; тариф не выбран'],
    [1,4248,0,'выбранный тариф не сопоставлен с Price для этого номера'],
    [2,null,0,'для номера найдено несколько соответствий в Price; тариф не выбран'],
    [1,4248,2,'для выбранного тарифа найдено несколько соответствий в Price'],
];
foreach($cases as $case){
    $actual=ptl_mapping_error($case[0],$case[1],$case[2]);
    if($actual!=='Автоматическая проверка недоступна: '.$case[3].'.')throw new RuntimeException($actual);
}
echo 'PASS: '.count($cases).' mapping diagnostics'.PHP_EOL;
