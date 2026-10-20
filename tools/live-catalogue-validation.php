<?php
function lcValid($cat,$item){
    if(!is_array($cat)||!is_array($item))throw new RuntimeException('download_failed');
    if(($cat['object_id']??null)!==$item['object_id']||($cat['provider_id']??null)!==$item['provider_id']||($cat['revision']??null)!==$item['revision']||($cat['checked_at']??null)!==$item['checked_at']||($cat['complete']??null)!==true||empty($cat['rooms'])||empty($cat['rates'])||strtotime($cat['checked_at'])<time()-172800||strtotime($cat['checked_at'])>time()+300)throw new RuntimeException('download_failed');
    foreach(['rooms','rates']as $section){
        if(!is_array($cat[$section])||count($cat[$section])>1000)throw new RuntimeException('download_failed');
        $seen=[];foreach($cat[$section]as $row){
            if(!is_array($row)||!is_string($row['code']??null)||!preg_match('/^[1-9][0-9]{0,9}$/D',$row['code'])||isset($seen[$row['code']])||!is_string($row['name']??null)||trim($row['name'])===''||mb_strlen($row['name'])>255||!is_string($row['description']??null))throw new RuntimeException('download_failed');
            $seen[$row['code']]=true;
            if($section==='rooms'){
                if(!is_int($row['main_places']??null)||$row['main_places']<1||!is_int($row['extra_places']??null)||$row['extra_places']<0||!is_array($row['photos']??null))throw new RuntimeException('download_failed');
                foreach($row['photos']as $url)if(!is_string($url)||!preg_match('~^https://price\.tonia\.ru/uploads/live/'.$item['object_id'].'/[a-f0-9]{64}\.(jpg|png|webp)$~D',$url))throw new RuntimeException('download_failed');
            }
        }
    }
}
