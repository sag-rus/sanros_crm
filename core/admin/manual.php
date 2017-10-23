<?php

function show_manual_directory(){
  $dir = $_POST["dir"];
  $directory_manual = ConfigCRM::getInstance()->directory."/manual";
  $directory = $directory_manual.$dir;
  $files = File::display_directory($directory);
  $answer = array(
    "exist" => 0,
    "desc" => ""
  );
  if(is_array($files)){
    $json = array();
    $config = $directory_manual."/config";
    $open = File::open_file($config);
    if($open !== FALSE){
      $json = json_decode($open, TRUE);
    }
    if(isset($json[$dir])){
      $answer["desc"] = $json[$dir];
    }
    foreach($files as $index => $file){
      if($file["name"] == "config"){
        unset($files[$index]);
      }else{
        $files[$index]["path"] = str_replace($directory_manual, "", $file["path"]);
        $files[$index]["desc"] = "Без описания";
        if($file["type"] == "dir" AND isset($json[$files[$index]["path"]])){
          $files[$index]["desc"] = $json[$files[$index]["path"]];
        }
      }
    }
    $answer["exist"] = 1;
    $answer["files"] = $files;
  }
  return json_encode($answer);
}

function create_file_manual(){
  $dir = $_POST["dir"];
  $name = get_translit($_POST["name"]);
  $type = $_POST["type"];
  $directory = ConfigCRM::getInstance()->directory."/manual/".$dir."/";
  if($type == "dir"){
    File::create_directory($directory.$name);
  }elseif($type == "file"){
    File::create_file($directory.$name.".txt");
  }
}

function open_file_manual(){
  $file = $_POST["file"];
  $dir_file = ConfigCRM::getInstance()->directory."/manual/".$file;
  $open = File::open_file($dir_file);
  $answer = array(
    "exist" => 0
  );
  if($open !== FALSE){
    $answer["exist"] = 1;
    $answer["text"] = $open;
    $answer["text-view"] = str_replace("\n", "<br />", $open);
    $answer["name"] = $file;
  }
  return json_encode($answer);
}

function update_file_manual(){
  $file = $_POST["file"];
  $text = $_POST["text"];
  $dir_file = ConfigCRM::getInstance()->directory."/manual/".$file;
  File::update_file($dir_file, $text);
}

function search_manual(){
  $dir = $_POST["dir"];
  $search = $_POST["search"];
  $directory_manual = ConfigCRM::getInstance()->directory."/manual";
  $directory = $directory_manual.$dir;
  $files = File::scan_directory($directory);
  $answer = array(
    "exist" => 0
  );
  if(is_array($files)){
    foreach($files as $index => $file){
      $text = file_get_contents($file["path"]);
      if(stripos($text, $search) !== FALSE){
        $file["path"] = str_replace($directory_manual, "", $file["path"]);
        $answer["files"][] = $file;
      }
    }
    $answer["exist"] = 1;
  }
  return json_encode($answer);
}

function update_desc_directory_manual(){
  $dir = $_POST["dir"];
  $desc = $_POST["desc"];
  $json = array();
  $config = ConfigCRM::getInstance()->directory."/manual/config";
  $open = File::open_file($config);
  if($open !== FALSE){
    $json = json_decode($open, TRUE);
  }else{
    File::create_file($config);
  }
  $json[$dir] = $desc;
  $json = json_encode($json);
  File::update_file($config, $json);
}

?>
