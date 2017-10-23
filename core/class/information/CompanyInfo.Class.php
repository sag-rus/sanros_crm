<?php

class CompanyInfo{

  private $connect;
  private $directory;

  public function __construct(){
    $config = ConfigCRM::getInstance();
    $this->connect = $config->connect;
    $this->directory = $config->directory;
  }

  public function get_info(){
    $directory = $this->directory;
  	include_once($directory."/config.php");
  	$conf = new JConfig;
  	$array = array();
  	$array["firma"] = $conf->firma;
  	$array["email"] = $conf->email;
  	$array["legal-address"] = $conf->leg_address;
  	$array["separate-address"] = $conf->sep_address;
  	$array["phone"] = $conf->tel_firma;
  	$array["website"] = $conf->web_site;
  	$array["INN"] = $conf->INN;
  	$array["KPP"] = $conf->KPP;
  	$array["BIK"] = $conf->BIK;
  	$array["OGRN"] = $conf->OGRN;
  	$array["KS"] = $conf->KS;
  	$array["bank"] = $conf->bank;
  	$array["reck"] = $conf->reck;
  	$array["director"] = $conf->director;
  	$array["director-pad"] = $conf->director_pad;
  	$array["booker"] = $conf->booker;
  	$array["reestr"] = $conf->reestr;
  	$array["licensia"] = $conf->licensia;
    return $array;
  }

}

?>
