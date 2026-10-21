<?php
require_once __DIR__.'/../core/document/contract_tourist_prices.php';
$t=[['surname'=>'Иванова','name'=>'Анна','otch'=>'Петровна'],['surname'=>'Иванов','name'=>'Алексей','otch'=>'Петрович']];
$n=['Иванова А.П. - 12 300,50 руб., Иванов А. П. 8000 руб.'];
$html=contract_tourist_prices($t,$n,'20300.50');
if(strpos($html,'Иванова Анна Петровна — 12 300,50 руб.')===false || strpos($html,'Иванов Алексей Петрович — 8 000,00 руб.')===false)throw new RuntimeException('Wrong breakdown');
foreach([[$t,$n,'20301'],[$t,[],'20300.50'],[$t,array_merge($n,['Иванов А.П. 9000 руб.']),'20300.50']] as $args){
    $failed=false;try{contract_tourist_prices(...$args);}catch(RuntimeException $e){$failed=true;}
    if(!$failed)throw new RuntimeException('Unsafe breakdown accepted');
}
echo "PASS: individual prices, initials, decimals, missing/ambiguous prices and total validation\n";
