<?php

class File{

  public static function display_directory($directory){
    if(is_dir($directory)){
      $answer = array();
      $open = opendir($directory);
  		while(FALSE !== ($file = readdir($open))){
  			if($file != "." AND $file != ".."){
          $path = $directory."/".$file;
          $path = str_replace("//", "/", $path);
          $append = array(
            "name" => $file,
            "type" => "file",
            "path" => $path
          );
          if(is_dir($path)){
            $append["type"] = "dir";
          }
          $answer[] = $append;
  			}
  		}
      return $answer;
    }
    return FALSE;
  }

  public static function create_directory($directory){
    if(!file_exists($directory) AND $directory != ""){
      mkdir($directory);
    }
  }

  public static function create_file($file){
    if(!file_exists($file) AND $file != ""){
      $fp = fopen($file, "x");
      fclose($fp);
    }
  }

  public static function open_file($file){
    if(is_file($file) AND $file != ""){
      $text = file_get_contents($file);
      return $text;
    }
    return FALSE;
  }

  public static function update_file($file, $text){
    if(is_file($file) AND $file != ""){
      file_put_contents($file, $text);
      return $file;
    }
    return FALSE;
  }

  public static function scan_directory($directory){
    $result = array();
    if(is_dir($directory)){
      $scan = scandir($directory);
      foreach($scan as $key => $file){
        if($file != "." AND $file != ".."){
          if(is_dir($directory."/".$file)){
            $result+= self::scan_directory($directory."/".$file);
          }else{
            $path = $directory."/".$file;
            $path = str_replace("//", "/", $path);
            $append = array(
              "name" => $file,
              "type" => "file",
              "path" => $path
            );
            if(is_dir($path)){
              $append["type"] = "dir";
            }
            $result[$path] = $append;
          }
        }
      }
    }
    return $result;
  }

}

?>
