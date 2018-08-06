<?php

function object_agency_report($connect) {
  global $directory;

  include_once($directory . "/config.php");
  $conf = new JConfig;

  $types  = [
    1 => 'в санатории',
    2 => 'в отеле',
    3 => 'в мини-отеле',
    4 => 'в пансионате',
    5 => 'в доме отдыха',
    6 => 'на базе отдыха',
    7 => 'в SPA-Отеле',
    8 => 'в детском лагере',
    9 => 'на курорте',
    10 => 'в гостинице',
    11 => 'на турбазе',
    12 => 'в пансионате с лечением',
    13 => 'в клинике-санатории'
  ];

  $id = isset($_GET['id'])?(int)$_GET['id']:0;

  $objRow = $connect->getRow('SELECT `id`, `full_name`, `name`, `type`, `reward`, `main_post_name`, `main_post_fio` FROM object WHERE id = ?i LIMIT 1',$id);

  if(!$objRow)
      $id = 0;

  if($id > 0) {

    $objectContract = $connect->getRow("SELECT `id`, `number`, `type` FROM object_contract WHERE object = ?i AND `active` = 1 AND `type` = 'object' ORDER BY `id` DESC LIMIT 1",$id);

    if($objectContract) {


        $monthArS = [
          NULL,
          'января',
          'февраля',
          'марта',
          'апреля',
          'мая',
          'июня',
          'июля',
          'августа',
          'сентября',
          'октября',
          'ноября',
          'декабря'
        ];


        $month = isset($_GET['month']) ? (int) $_GET['month'] : 1;

        if ($month <= 0 || $month > 12) {
          $month = 1;
        }

        $year = isset($_GET['year']) ? (int) $_GET['year'] : date("Y");

        if ($year > date("Y")) {
          $year = date("Y");
        }

      $objReck = $connect->getAll("SELECT `id`, `sum`, `date_z`, `date_v`, `rest`, `reward` FROM  `reckoning` WHERE `id_obj` = ?i AND `status` = 5 AND `status_san` = 1 AND MONTH(`date_z`) = ?i AND YEAR(`date_z`) = ?i ORDER BY `date_z` ASC", $id,$month, $year);


      $monthLastDay = date("t", strtotime("01." . $month . "." . $year));


        ob_start();
        ?>
          <style type="text/css">

              .border {
                  font-family: freesans, sans-serif;
                  padding: 20px;
                  max-width: 685px;
                  margin: 0 auto;
                  font-size: 10pt;
              }
              table {
                  font-size: 10pt;
              }

              table th,
              table td {
                  padding: 15px;
              }

              .head {
                  text-align: center;
                  font-size: 15pt;
                  font-weight: bold;
                  text-decoration: underline;
              }
              p {
                  margin: 8px 0px;
              }
              td {
                  padding-left: 5px;
              }
              .pod_tbl td {
                  padding-left: 0px;
              }
              h1, h2, h3 {
                  text-align: center;
              }

          </style>
          <!DOCTYPE html>
          <html>
          <head>

          </head>
          <body>
          <div class="border">
              <h1>Отчет агента № <?= $month; ?> от «<?= $monthLastDay; ?>» <?= $monthArS[$month]; ?>  <?= $year ?> г.</h1>
              <h3>За период с «01» <?= $monthArS[$month]; ?> <?= $year ?> г. по «<?= $monthLastDay; ?>» <?= $monthArS[$month]; ?>  <?= $year ?>г.</h3>
              <h3>по агентскому договору № <?=$objectContract['number'];?></h3>
              <h3 style="text-align: left; font-weight: 100;"><b>Комиссионер:</b> ООО ТА «САНАТА-ТРЕВЕЛ»</h3>
              <h3 style="text-align: left; font-weight: 100;"><b>Комитент:</b> <?=$objRow['full_name'];?></h3>
              <table border="1" cellpadding="0" cellspacing="0">
                  <thead>
                    <tr>
                        <th rowspan="2">
                            №
                        </th>
                        <th rowspan="2">
                            Ф.И.О. отдыхающего
                        </th>
                        <th colspan="2">
                            Период пребывания <?=(is_null($objRow['type']) || !isset($types[$objRow['type']]))?'в санатории':$types[$objRow['type']]?> «Ромашкино»
                        </th>
                        <th rowspan="2">
                            № заявки
                        </th>
                        <th rowspan="2">
                            Стоимость путевки
                        </th>
                        <th rowspan="2">
                            Сумма агентского вознаграждения
                        </th>
                        <th rowspan="2">Платежное поручение</th>
                    </tr>
                    <tr>
                        <th>
                            Дата заезда
                        </th>
                        <th>
                            Дата выезда
                        </th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i = 0; $sum = 0; $rewardSum = 0; foreach ($objReck as $reck) { $i++; $sum+= $reck['sum'];
                        $restString = "";
                        $reward = 0;
                        $paymentsStr = "";
                        $payments = $connect->getAll("SELECT `id`, `date`, `sum`, `type`, `pay_number` FROM `payment` WHERE `schet` = ?i AND `type` IN (3,4) AND `status` = 2",$reck['id']);


                        foreach ($payments as $payment) {

                            if($paymentsStr)
                                $paymentsStr .= "<br><br>";

                            $paymentsStr .= number_format($payment['sum'],2,","," ")." - п/п ".$payment['pay_number']." от ".date("d.m.Y", strtotime($payment['date']));
                        }

                        if($reck['reward'])
                            $reward = (float)$reck['reward'];
                        elseif ($objRow['reward'])
                            $reward = $reck['sum']*$objRow['reward']/100;

                        $rewardSum += $reward;

                        if($reck['rest']) {
                            $klients = $connect->getAll("SELECT `name`, `surname`, `otch` FROM `klient` WHERE `id` IN (".$reck['rest'].")");
                            foreach ($klients AS $klient) {
                                if($restString)
                                    $restString .= "<br><br> ";

                                $restString .= $klient['surname']." ".$klient['name']." ".$klient['otch'];
                            }
                        }
                      ?>
                      <tr>
                          <td>
                            <?=$i;?>
                          </td>
                          <td>
                            <?=$restString;?>
                          </td>
                          <td>
                            <?=date("d.m.Y",strtotime($reck['date_z']));?>
                          </td>
                          <td>
                            <?=date("d.m.Y",strtotime($reck['date_v']));?>
                          </td>
                          <td>
                            <?=$reck['id'];?>
                          </td>
                          <td>
                            <?=number_format($reck['sum'],2,","," ");?>
                          </td>
                          <td>
                            <?=number_format($reward,2,","," ");?>
                          </td>
                          <td>
                            <?=$paymentsStr;?>
                          </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                  <tfoot>
                    <tr>
                        <th>Итого</th>
                        <th colspan="4">

                        </th>
                        <th>
                          <?=number_format($sum,2,","," ");?>
                        </th>
                        <th>
                          <?=number_format($rewardSum,2,","," ");?>
                        </th>
                        <th></th>
                    </tr>
                  </tfoot>
              </table>
              <br><br>
              <table border="0" cellpadding="0" cellspacing="0">
                  <tbody>
                    <tr>
                        <td style="padding-left: 0;">
                            Комиссионер:<br>
                            Генеральный директор ООО ТА «САНАТА-ТРЕВЕЛ»<br><br>
                            ________________Терентьева О. Б.
                        </td>
                        <td style="padding-left: 0;">
                            Комитент:<br>
                            <?=$objRow['main_post_name'];?><br><br>
                            ________________<?=$objRow['main_post_fio'];?>
                        </td>
                    </tr>
                  </tbody>
              </table>

          </div>
          </body>
          </html>
    <?php
    } else { ?>
      Не указан действуюший договор объекта
    <?php } ?>
    <?php
    echo ob_get_clean();
  }
}
?>