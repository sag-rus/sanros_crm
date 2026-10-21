<?php
// Amounts come from the booking notes, not from an equal division of the total.
function contract_tourist_prices(array $tourists,array $notes,$total){
    $lines=[];$sum=0;
    foreach($tourists as $tourist){
        $initials=preg_quote($tourist['surname'],'/').'\\s+'.preg_quote(mb_substr($tourist['name'],0,1,'UTF-8'),'/').'\\.\\s*'.preg_quote(mb_substr($tourist['otch'],0,1,'UTF-8'),'/').'\\.';
        $matches=[];
        foreach($notes as $note){
            if(preg_match_all('/(?<![\\p{L}])'.$initials.'\\s*(?:[-–—]\\s*)?([0-9]+(?:[ \\x{00A0}][0-9]{3})*(?:[.,][0-9]{1,2})?)\\s*руб/u',$note,$found)){
                foreach($found[1] as $amount)$matches[]=(int)round((float)str_replace([" ","\u{00A0}",','],['','','.'],$amount)*100);
            }
        }
        $matches=array_values(array_unique($matches));
        if(count($matches)!==1)throw new RuntimeException('В примечаниях заявки не указана однозначная стоимость для каждого туриста.');
        $amount=$matches[0];$sum+=$amount;
        $name=trim($tourist['surname'].' '.$tourist['name'].' '.$tourist['otch']);
        $lines[]=htmlspecialchars($name,ENT_QUOTES,'UTF-8').' — '.number_format($amount/100,2,',',' ').' руб.';
    }
    if(!$lines || $sum!==(int)round((float)$total*100))throw new RuntimeException('Стоимость по туристам не совпадает с итогом договора с учётом скидки.');
    return '<br />В том числе стоимость путёвки для каждого туриста с учётом скидки:<br />'.implode('<br />',$lines).'<br />';
}
