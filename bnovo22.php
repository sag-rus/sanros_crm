<?php

/* ---------------------------- */
$response = array();
$id_obj = 60;
$account_id = 34311;


//AUTH


$url = 'https://api.reservationsteps.ru/v1/api/auth';
$data = array("username"=> 'info@sanata.online' , "password" => '6CGn3b3qF57lOi5nuxBwiIEzcCOVVXsu');
$postdata = json_encode($data);
$ch = curl_init($url); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$result = curl_exec($ch);
curl_close($ch);
echo '123';
print_r ($result);
echo '123';

//AUTH

?>