<?php


include_once("core/functions.php");
include_once("core/lib/Mysql.Class.php");
include_once("config.php");
$conf = new JConfig;
$connect = connect_to_MySQL();
$rows = $connect->getAll("SELECT name, email, inn FROM object");

$emails = array();
if($rows){
    $count = count($rows);
    ?>
    <ul style="padding:0,margin: 0;list-style:none;">
    <?php
	foreach($rows as $row) {
    ?>
        <li style="border-bottom: 1px solid #000;">
            Название: <?=$row['name'];?><br>
            <?php if($row['email']) { ?>
            E-mail: <?=$row['email'];?><br>
            <?php } ?>
            <?php if($row['inn']) { ?>
                ИНН: <?=$row['inn'];?><br>
            <?php } ?>
        </li>
    <?php
	}
    ?>
    </ul>
    <?php
	echo "Всего: " . $count;
}


