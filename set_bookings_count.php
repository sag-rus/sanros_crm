<?php
include_once("core/functions.php");
include_once("core/lib/Mysql.Class.php");
include_once("config.php");
$conf = new JConfig;
$connect = connect_to_MySQL();
$rows = $connect->getAll("SELECT id FROM object WHERE bookings_count_update <= NOW() - INTERVAL 3 DAY ORDER BY `bookings_count_update` ASC ");

if($rows){
	foreach($rows as $row) {
        $cnt = $connect->getOne('SELECT count(id) as cnt FROM `reckoning` WHERE `id_obj`=?i', $row['id']);
        echo 'cnt='.$cnt.'<br>';
        $connect->query("UPDATE `object` SET `bookings_count`='$cnt', `bookings_count_update`=NOW() WHERE `id`='$row[id]'");
        echo "UPDATE `object` SET `bookings_count`='$cnt', `bookings_count_update`=NOW() WHERE `id`='$row[id]'<br>";

    }
}
?>


