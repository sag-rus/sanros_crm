<?php

class StatusBookingModuleObject{

  private static $status = array(
    1 => "Неподтвержденная",
    2 => "Неоплаченная",
    3 => "Частично оплаченная",
    4 => "Оплаченная",
    5 => "Аннулированная"
  );

  public static function select_status(){
    return self::$status;
  }

}

?>
