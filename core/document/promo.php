<?php

function review_promo($connect, $region = 1, $type = "PDF"){
	global $directory;

	include_once($directory."/config.php");
	$array = array();
	$data = $connect->getAll("SELECT id FROM object WHERE id_reg=?i", $region);
	foreach($data as $row){
		$object = $row["id"];
		$promo_object = array();
		$promotions = $connect->getAll("SELECT id, title, text FROM promotions WHERE active!=0 AND id_obj=?i", $object);
		foreach($promotions as $promo){
			$id = $promo["id"];
			$promo_object[$id] = array();
			$promo_object[$id]["title"] = $promo["title"];
			$promo_object[$id]["text"] = $promo["text"];
		}
		if(count($promo_object) > 0){
			$array[$object] = array();
			$array[$object]["name"] = get_object($connect, $object, "type");
			$array[$object]["promo"] = $promo_object;
		}
	}
	$name_region = $connect->getOne("SELECT name FROM region WHERE id=?i", $region);
	$date = date("d.m.Y");

	ob_start();
?>
	<page class="border" backtop="190px" backbottom="40px">
		<page_header>
			<img src="images/promo/header.jpg" style="width: 100%; margin-bottom: 10px" />
			<p class="title-page">
				Акции региона <?php echo $name_region; ?> от <?php echo $date; ?>
				<hr />
			</p>
		</page_header>
		<page_footer>
			<img src="images/promo/footer.jpg" style="width: 100%" />
		</page_footer>
		<div>
		<?php foreach($array as $object){ ?>
		<div class="border-bottom">
			<table>
			<tr>
				<td style="width: 50%">
					<div class="title-object"><?php echo $object["name"]; ?></div>
				</td>
				<td style="width: 50%">
			<?php foreach($object["promo"] as $promo){ ?>
					<div class="promo-block">
						<div class="title-promo"><?php echo $promo["title"]; ?></div>
						<div class="text-promo"><?php echo $promo["text"]; ?></div>
					</div>
			<?php } ?>
				</td>
			</tr>
			</table>
		</div>
		<?php } ?>
		</div>
	</page>

<style type="text/css">

.border{
	font-family: freesans, sans-serif;
	padding: 0px;
	height: 1000px;
	width: 730px;
	margin: 0 auto;
	padding-top: 100px;
	page-break-after:always;
}

.border-bottom{
	border-bottom: 3px solid #000;
}

.border table{
	width: 720px;
}

.promo-block{
	margin-bottom: 15px;
}

.title-page{
	text-align: center;
	font-size: 20pt;
	font-family: freesans, sans-serif;
	color: #C40505;
}

.title-object{
	font-size: 25px;
	font-weight: bold;
	text-align: center;
	color: #707070;
}

.title-promo{
	font-size: 14pt;
	color: #052B00;
}

.text-promo{
	font-size: 12pt;
}

td{
	padding: 4px;
	font-size: 9pt;
	vertical-align: middle;
}

th{
	padding: 4px;
	font-size: 9pt;
	text-align: center;
	font-weight: bold;
	background: #a4a4a4;
	color: #fff;
}

p{
	margin: 0 8px;
}

</style>

<?php
	$content = ob_get_clean();
	if($type == "HTML"){
		echo $content;
	}elseif($type == "PDF"){
		include($directory."/core/lib/html2PDF/html2pdf.class.php");
		$pdf = new HTML2PDF("P", "A4", "en", array(0, 0, 0, 0), "UTF-8");
		$pdf->WriteHTML($content);
		$pdf->Output("promo.pdf");
	}

}

?>
