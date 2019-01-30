<?php

class ComparisonObject{

  protected $connect;
  protected $object;
  private $rate = array(
    0 => [
      "name" => "Пробный",
      "max" => 2,
      "price" => [
        1 => 0,
        3 => 0,
        6 => 0
      ]
    ],
    1 => array(
      "name" => "Начальный",
      "max" => 5,
      "price" => array(
        1 => 1000,
        3 => 2000,
        6 => 3000
      )
    ),
    2 => array(
      "name" => "Стандартный",
      "max" => 10,
      "price" => array(
        1 => 1500,
        3 => 3000,
        6 => 4000
      )
    ),
    3 => array(
      "name" => "Максимальный",
      "max" => 20,
      "price" => array(
        1 => 2000,
        3 => 4000,
        6 => 5000
      )
    )
  );

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->object = $config->object;
  }

  public function create(){
    $connect = $this->connect;
    $object = $this->object;
    $check = $connect->getOne("SELECT id FROM comparison_module_object WHERE object=?i", $object);
		if(!$check){
			$today = date("Y-m-d");
      $validity = date("Y-m-d", strToTime("+2 days"));
      $rate = 0;
			$connect->query("INSERT INTO comparison_module_object(object, date_create, validity_date, rate) VALUES(?i, ?s, ?s, ?i)", $object, $today, $validity, $rate);

			$module = $connect->insertId();
			$connect->query("UPDATE object SET status=2, synchronized=0 WHERE id=?i", $object);
			save_history_object("Создание модуля сравнения цен");
      $answer = array(
        "id" => $module,
        "validity" => $validity,
        "rate" => $rate
      );
			return $answer;
		}
  }

  public function select_rate_index($rate_index, $month){
    $rate = $this->rate;
    if(!isset($rate[$rate_index])){
			$rate_index = 1;
		}
		if(!isset($rate[$rate_index]["price"][$month])){
			$month = 1;
		}
    $array = array(
      "name" => $rate[$rate_index]["name"],
      "price" => $rate[$rate_index]["price"][$month],
      "month" => $month
    );
    return $array;
  }

  public function select_rate(){
    $rate = $this->rate;
    return $rate;
  }

}

?>
