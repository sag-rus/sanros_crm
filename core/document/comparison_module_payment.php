<?php

function comparison_module_payment($connect, $object_id, $rate = 1, $month = 3) {
  global $directory;

  if($object_id <= 0)
    return;

  $company = new CompanyInfo;
  $array["company"] = $company->get_info();
  $comparison = new ComparisonObject;
  $rate = $comparison->select_rate_index($rate, $month);
  $array["product"]["name"] = "Оплата услуги «Сравнение цен конкурентов» по тарифу «".$rate["name"]."»";
  $array["product"]["month"] = $rate["month"];
  $array["product"]["price"] = $rate["price"];
  $array["product"]["payer"] = get_object($connect, $object_id, "full_and_place");
  $array["product"]["bid"] = $object_id;
  $data = $array;
  ob_start();
  ?>
  <div class="content">
    <table cellpadding="5" cellspacing="0">
      <tr>
        <td style="border: none; width: 900px;">
          <p style="margin-top: 10px;"><?php echo $data["company"]["firma"]; ?><br />
            <strong>Адрес:</strong> <?php echo $data["company"]["legal-address"]; ?><br />
            <strong>Тел.:</strong> <?php echo $data["company"]["phone"]; ?> <br />
            <strong>Email:</strong> <?php echo $data["company"]["email"]; ?> <strong>Сайт:</strong> <?php echo $data["company"]["website"]; ?></p>
        </td>
      </tr>
    </table>
    <br />
    <table cellpadding="5" cellspacing="0">
      <tr>
        <td width="330">ИНН <?php echo $data["company"]["INN"]; ?></td>
        <td width="330">КПП <?php echo $data["company"]["KPP"]; ?></td>
        <td width="83" align="center" rowspan="2" valign="middle">Сч. №</td>
        <td width="200" rowspan="2" valign="middle"><?php echo $data["company"]["reck"]; ?></td>
      </tr>
      <tr>
        <td colspan="2">Получатель<br /><?php echo $data["company"]["firma"]; ?></td>
      </tr>
      <tr>
        <td colspan="2" rowspan="2">Банк получателя<br /><?php echo $data["company"]["bank"]; ?></td>
        <td align="center">БИК</td>
        <td><?php echo $data["company"]["BIK"]; ?></td>
      </tr>
      <tr>
        <td align="center">К/C №</td>
        <td><?php echo $data["company"]["KS"]; ?></td>
      </tr>
    </table>
    <p><span class="bold_head">СЧЕТ № <?php echo $data["product"]["bid"]; ?></span><br />
      Заказчик: <?php echo $data["product"]["payer"]; ?><br />
      Плательщик: <?php echo $data["product"]["payer"]; ?></p>
    <table cellpadding="5" cellspacing="0">
      <tr>
        <th width="40">№</th>
        <th width="600">Наименование услуг</th>
        <th width="70">Ед/изм</th>
        <th width="70">Кол-во</th>
        <th width="70">Цена</th>
        <th width="70">Сумма</th>
      </tr>
      <tr>
        <th width="40">1</th>
        <th width="600" style="text-align: left">
          <?php echo $data["product"]["name"]; ?>
          <br />
          Кол-во месяцев: <?php echo $data["product"]["month"]; ?>
        </th>
        <th width="70">шт</th>
        <th width="70">1</th>
        <th width="70"><?php echo $data["product"]["price"]; ?></th>
        <th width="70"><?php echo $data["product"]["price"]; ?></th>
      </tr>
      <tr>
        <td rowspan="7" colspan="3" style="text-align: left; border: none;">
          <table class="head" width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td width="150" valign="bottom">Руководитель предприятия</td>
              <td width="100" rowspan="2" height="100">
                <img src="<?=$directory;?>/images/pechat/pechat.jpg" />
              </td>
              <td valign="bottom">(<?php echo $data["company"]["director"]; ?>)</td>
            </tr>
            <tr>
              <td valign="top"><br /><br />Главный бухгалтер</td>
              <td valign="top"><br /><br />(<?php echo $data["company"]["booker"]; ?>)</td>
            </tr>
          </table>
        </td>
        <td colspan="2" style="text-align: right; border: none"><strong>Итого:</strong></td>
        <td align="center"><?php echo $data["product"]["price"]; ?></td>
      </tr>
      <tr>
        <td colspan="2" style="text-align: right; border: none"><strong>Без налога (НДС):</strong></td>
        <td align="center">-</td>
      </tr>
      <tr>
        <td colspan="2" style="text-align: right; border: 1px solid #fff;"><strong>Всего к оплате:</strong></td>
        <td align="center"><?php echo $data["product"]["price"]; ?></td>
      </tr>
    </table>
  </div>

  <style type="text/css">

    .content{
      font-family: freesans, sans-serif;
      width: 800px;
      font-size: 11pt;
      margin: 0 auto;
    }

    td, th{
      padding: 4px;
      font-size: 11pt;
      vertical-align: middle;
      border: 1px solid black;
    }

    th{
      text-align: center;
      font-weight: normal;
    }

    .head td{
      border: none;
    }

    .bold_head{
      font-size: 12pt;
      font-weight: bold;
    }

  </style>
  <?php
  $HTML = ob_get_clean();

  include($directory."/core/lib/html2PDF/html2pdf.class.php");
  $pdf = new HTML2PDF("L", "A4", "en", array(0, 0, 0, 0), "UTF-8");
  $pdf->WriteHTML($HTML);
  $pdf->Output("payment.pdf");
}

?>