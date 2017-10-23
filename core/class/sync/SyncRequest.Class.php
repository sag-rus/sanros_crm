<?php

class SyncRequest{

  private function __construct(){}

  public static function sync($params){
    $config = ConfigCRM::getInstance();
    $url = $config->sync["link"];
    $string = http_build_query($params);
  	$options = array("http" =>
  		array(
  			"method"  => "POST",
  			"header"  => "Content-type: application/x-www-form-urlencoded",
  			"content" => $string
  		)
  	);
  	$context = stream_context_create($options);
  	$result = file_get_contents($url, FALSE, $context);
  	$array = json_decode($result, TRUE);
  	return $array;
  }

}

?>
