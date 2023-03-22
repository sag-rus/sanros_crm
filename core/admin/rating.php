<?php

function show_rating_menu($connect){
	$rating = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=2");
	$comment = $connect->getOne("SELECT COUNT(*) FROM rating_comment WHERE status=0");
?>
	<ul class="nav nav-tabs menu-rating">
		<li onclick="show_rating()" class="show-rating"><a><i class="fa fa-comments-o"></i> Отзывы
		<?php if($rating > 0){ ?>
			<span class="badge count-red"><?php echo $rating; ?></span>
		<?php } ?>
		</a></li>
		<li onclick="show_rating_comment()" class="show-comment-rating"><a><i class="fa fa-comment-o"></i> Комментарии
		<?php if($comment > 0){ ?>
			<span class="badge count-red"><?php echo $comment; ?></span>
		<?php } ?>
		</a></li>
		<li onclick="admin_rating_comment()" class="admin-comment-rating"><a><i class="fa fa-commenting-o"></i> Оставить комментарий</li>
	</ul>
	<div class="rating-content" style="padding-top: 10px"></div>
<?php
}

function select_rating_admin($connect){
	if($connect->getOne("SELECT id FROM rating WHERE status=2")){
		$row = $connect->getRow("SELECT id, schet, id_obj, DATE_FORMAT(date, '%d.%m.%Y') as date, DATE_FORMAT(date_send, '%d.%m.%Y') as date_send, comfort, location, staff, ratio, treatment, clean, leisure, positive, negative, advice, company_rating, photos, from_whence FROM rating WHERE status=2");
		$id = $row["id"];
		if($row["treatment"] == 0)
			$row["treatment"] = "без лечения";
		elseif($row["company"] == 2)
			$company = "да";
		$object = get_object($connect, $row["id_obj"]);
		$from_whence = "форма";
		if($row["from_whence"] == "cabinet")
			$from_whence = "личный кабинет";
	ob_start();
?>
	<div class="form-horizontal panel panel-info">
		<div class="panel-heading">
			<i class="fa fa-comment-o"></i> Новый отзыв
		</div>
		<div class="list-group">
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Объект</label>
					<div class="col-sm-9">
						<?php echo $object; ?> (заявка №<?php echo $row["schet"]; ?>)
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Дата отправки письма</label>
					<div class="col-sm-9">
						<?php echo $row["date"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Откуда отзыв</label>
					<div class="col-sm-9">
						<?php echo $from_whence; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Дата отзыва</label>
					<div class="col-sm-9">
						<?php echo $row["date_send"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Чистота</label>
					<div class="col-sm-3">
						<?php echo $row["clean"]; ?>
					</div>
					<label class="col-sm-3 control-label-element">Комфорт</label>
					<div class="col-sm-3">
						<?php echo $row["comfort"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Месторасположение</label>
					<div class="col-sm-3">
						<?php echo $row["location"]; ?>
					</div>
					<label class="col-sm-3 control-label-element">Персонал</label>
					<div class="col-sm-3">
						<?php echo $row["staff"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Досуг</label>
					<div class="col-sm-3">
						<?php echo $row["leisure"]; ?>
					</div>
					<label class="col-sm-3 control-label-element">Лечение</label>
					<div class="col-sm-3">
						<?php echo $row["treatment"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Соотношение цена/качество</label>
					<div class="col-sm-3">
						<?php echo $row["ratio"]; ?>
					</div>
					<div class="col-sm-6"></div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Положительное</label>
					<div class="col-sm-9" style="color: #19500E">
						<?php echo $row["positive"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Отрицательное</label>
					<div class="col-sm-9" style="color: #F00">
						<?php echo $row["negative"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Совет</label>
					<div class="col-sm-9">
						<?php echo $row["advice"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Отзыв о системе</label>
					<div class="col-sm-9">
						<?php echo $row["company_rating"]; ?>
					</div>
				</div>
			</div>
			<?php if($row["photos"] != ""){ ?>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Фото отдыха</label>
					<div class="col-sm-9">
				<?php
					$photos = json_decode($row["photos"], TRUE);
					foreach($photos as $photo){
				?>
					<a href="http://xn----7sba6aaba8akdsdekah.xn--p1ai/client/images/rating/big/<?php echo $photo; ?>" target="_blank"><img src="http://xn----7sba6aaba8akdsdekah.xn--p1ai/client/images/rating/thumb/<?php echo $photo; ?>" class="img-thumbnail" /></a>
				<?php
					}
				?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="panel-footer text-right">
			<button  type="button" class="btn btn-success btn-sm" onclick="confirm_rating('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Принять</button>
			<button  type="button" class="btn btn-default btn-sm" onclick="edit_rating('<?php echo $id; ?>')"><i class="fa fa-pencil"></i> Изменить</button>
			<button  type="button" class="btn btn-danger btn-sm" onclick="delete_rating('<?php echo $id; ?>')"><i class="fa fa-trash-o"></i> В архив</button>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	}else{
		$all = $connect->getOne("SELECT COUNT(*) FROM rating WHERE schet!=''");
		$read = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=1 AND schet!=''");
		$confirm = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=3 AND schet!=''");
		$trash = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=4 AND schet!=''");
		$confirm_text = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=3 AND (positive!='' OR negative!='' OR advice!='') AND schet!=''");
		$confirm_photo = $connect->getOne("SELECT COUNT(*) FROM rating WHERE status=3 AND photos!='' AND schet!=''");
		$percent_read = round((($read / $all) * 100), 2);
		$percent_confirm = round((($confirm / $all) * 100), 2);
		$percent_trash = round((($trash / $all) * 100), 2);
		$percent_confirm_text = round((($confirm_text / $confirm) * 100), 2);
		$percent_confirm_photo = round((($confirm_photo / $confirm) * 100), 2);

		$fake_all = $connect->getOne("SELECT COUNT(*) FROM rating WHERE schet='' OR schet IS NULL");
?>
	<div class="form-horizontal panel panel-info">
		<div class="panel-heading">
			<i class="fa fa-comments-o"></i> Статистика по отзывам
		</div>
		<div class="list-group">
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Выслано писем с отзывами</label>
					<div class="col-sm-8">
						<?php echo $all; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Прочитано писем (но нет отзыва)</label>
					<div class="col-sm-8">
						<?php echo $read; ?> (<?php echo $percent_read; ?>)%
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<hr />
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Отзывов принято</label>
					<div class="col-sm-8">
						<?php echo $confirm; ?> (<?php echo $percent_confirm; ?>)%
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Принятых отзывов с текстом</label>
					<div class="col-sm-8">
						<?php echo $confirm_text; ?> (<?php echo $percent_confirm_text; ?>)%
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Принятых отзывов с фото</label>
					<div class="col-sm-8">
						<?php echo $confirm_photo; ?> (<?php echo $percent_confirm_photo; ?>)%
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Отзывов в архиве</label>
					<div class="col-sm-8">
						<?php echo $trash; ?> (<?php echo $percent_trash; ?>)%
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<hr />
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Фейковых отзывов</label>
					<div class="col-sm-8">
						<?php echo $fake_all; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer text-right">
			<button type="button" class="btn btn-info btn-sm" onclick="add_new_rating()"><i class="fa fa-smile-o"></i> Новый отзыв</button>
		</div>
	</div>
<?php
		$html = ob_get_clean();
	}
	return $html;
}

function add_new_rating(){
?>
<div class="modal fade bs-example-modal-lg">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Новый отзыв</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal new-rating">
					<div class="form-group">
						<label class="col-sm-3 control-label">Объект</label>
						<div class="col-sm-9" id="object_name">
							<input type="text" class="form-control" id="object" onkeyup="find_klient(event, 'object', 'object', 'use_object')" name="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Турист</label>
						<div class="col-sm-3">
							<input type="text" class="form-control turist" />
						</div>
						<label class="col-sm-3 control-label">Дата</label>
						<div class="col-sm-3">
							<input type="text" class="form-control datepicker" id="date" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">С какого сайта</label>
						<div class="col-sm-9">
							<select class="form-control site-from">
								<option value=""></option>
								<option value="tripadvisor.ru">tripadvisor.ru</option>
								<option value="otzovik.com">otzovik.com</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Положительное</label>
						<div class="col-sm-9">
							<textarea class="form-control" id="positive"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Отрицательное</label>
						<div class="col-sm-9">
							<textarea class="form-control" id="negative"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Совет</label>
						<div class="col-sm-9">
							<textarea class="form-control" id="advice"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label control-label-padding">
							Чистота
						</label>
						<div class="col-sm-3 btn-group block-clean" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="2">&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="3">&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="4">&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="5">&nbsp;5&nbsp;</label>
						</div>

						<label class="col-sm-3 control-label control-label-padding">
							Комфорт
						</label>
						<div class="col-sm-3 btn-group block-comfort" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="2">&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="3">&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="4">&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="5">&nbsp;5&nbsp;</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label control-label-padding">
							Персонал
						</label>
						<div class="col-sm-3 btn-group block-staff" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="2">&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="3">&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="4">&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="5">&nbsp;5&nbsp;</label>
						</div>

						<label class="col-sm-3 control-label control-label-padding">
							Лечение
						</label>
						<div class="col-sm-3 btn-group block-treatment" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="2">&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="3">&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="4">&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="5">&nbsp;5&nbsp;</label>
						</div>
					</div>
					<div class="form-group">

						<label class="col-sm-3 control-label control-label-padding">
							Досуг
						</label>
						<div class="col-sm-3 btn-group block-leisure" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="2">&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="3">&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="4">&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="5">&nbsp;5&nbsp;</label>
						</div>

						<label class="col-sm-3 control-label control-label-padding">
							Цена/качество
						</label>
						<div class="col-sm-3 btn-group block-ratio" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="2">&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="3">&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="4">&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="5">&nbsp;5&nbsp;</label>
						</div>

					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label control-label-padding">
							Месторасположение
						</label>
						<div class="col-sm-3 btn-group block-location" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="2">&nbsp;2&nbsp;</label>&nbsp;&nbsp;
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="3">&nbsp;3&nbsp;</label>&nbsp;&nbsp;
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="4">&nbsp;4&nbsp;</label>&nbsp;&nbsp;
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="5">&nbsp;5&nbsp;</label>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="save_new_rating()"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function save_new_rating($connect){
	$site_from = $_POST["site"];
	$connect->query("INSERT INTO rating(status, clean, comfort, staff, leisure, location, treatment, ratio, positive, negative, advice, date_send, turist, id_obj, site_from) VALUES (3, ?i, ?i, ?i, ?i, ?i, ?i, ?i, ?s, ?s, ?s, ?s, ?s, ?i, ?s)", $_POST["clean"], $_POST["comfort"], $_POST["staff"], $_POST["leisure"], $_POST["location"], $_POST["treatment"], $_POST["ratio"], $_POST["positive"], $_POST["negative"], $_POST["advice"], $_POST["date"], $_POST["turist"], $_POST["object"], $site_from);
}


function edit_rating($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT id_obj, clean, comfort, location, staff, ratio, leisure, treatment, positive, negative, advice, company_rating FROM rating WHERE id=?i", $id);
	$radio = array("clean" => array(0, 1, 2, 3, 4, 5), "comfort" => array(0, 1, 2, 3, 4, 5), "location" => array(0, 1, 2, 3, 4, 5), "staff" => array(0, 1, 2, 3, 4, 5), "ratio" => array(0, 1, 2, 3, 4, 5), "leisure" => array(0, 1, 2, 3, 4, 5), "treatment" => array(0, 1, 2, 3, 4, 5));
	$radio["clean"][$row["clean"]] = " checked ";
	$radio["comfort"][$row["comfort"]] = " checked ";
	$radio["location"][$row["location"]] = " checked ";
	$radio["staff"][$row["staff"]] = " checked ";
	$radio["ratio"][$row["ratio"]] = " checked ";
	$radio["leisure"][$row["leisure"]] = " checked ";
	$radio["treatment"][$row["treatment"]] = " checked ";
?>
<div class="modal fade bs-example-modal-lg">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить отзыв «<?php echo get_object($connect, $row["id_obj"], "type"); ?>»</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal edit-rating">
					<div class="form-group">
						<label class="col-sm-6 control-label control-label-left">Положительное</label>
						<label class="col-sm-6 control-label control-label-left">Отрицательное</label>
					</div>
					<div class="form-group">
						<div class="col-sm-6">
							<textarea class="form-control" id="positive"><?php echo $row["positive"]; ?></textarea>
						</div>
						<div class="col-sm-6">
							<textarea class="form-control" id="negative"><?php echo $row["negative"]; ?></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-6 control-label control-label-left">Совет</label>
						<label class="col-sm-6 control-label control-label-left">Отзыв о системе</label>
					</div>
					<div class="form-group">
						<div class="col-sm-6">
							<textarea class="form-control" id="advice"><?php echo $row["advice"]; ?></textarea>
						</div>
						<div class="col-sm-6">
							<textarea class="form-control company-rating"><?php echo $row["company_rating"]; ?></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label control-label-padding">
							Чистота
						</label>
						<div class="col-sm-3 btn-group block-clean" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="2" <?php echo $radio["clean"][2]; ?>>&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="3" <?php echo $radio["clean"][3]; ?>>&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="4" <?php echo $radio["clean"][4]; ?>>&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="clean" value="5" <?php echo $radio["clean"][5]; ?>>&nbsp;5&nbsp;</label>
						</div>

						<label class="col-sm-3 control-label control-label-padding">
							Комфорт
						</label>
						<div class="col-sm-3 btn-group block-comfort" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="2" <?php echo $radio["comfort"][2]; ?>>&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="3" <?php echo $radio["comfort"][3]; ?>>&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="4" <?php echo $radio["comfort"][4]; ?>>&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="comfort" value="5" <?php echo $radio["comfort"][5]; ?>>&nbsp;5&nbsp;</label>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label control-label-padding">
							Персонал
						</label>
						<div class="col-sm-3 btn-group block-staff" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="2" <?php echo $radio["staff"][2]; ?>>&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="3" <?php echo $radio["staff"][3]; ?>>&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="4" <?php echo $radio["staff"][4]; ?>>&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="staff" value="5" <?php echo $radio["staff"][5]; ?>>&nbsp;5&nbsp;</label>
						</div>

						<label class="col-sm-3 control-label control-label-padding">
							Лечение
						</label>
						<div class="col-sm-3 btn-group block-treatment" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="2" <?php echo $radio["treatment"][2]; ?>>&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="3" <?php echo $radio["treatment"][3]; ?>>&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="4" <?php echo $radio["treatment"][4]; ?>>&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="treatment" value="5" <?php echo $radio["treatment"][5]; ?>>&nbsp;5&nbsp;</label>
						</div>
					</div>
					<div class="form-group">

						<label class="col-sm-3 control-label control-label-padding">
							Досуг
						</label>
						<div class="col-sm-3 btn-group block-leisure" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="2" <?php echo $radio["leisure"][2]; ?>>&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="3" <?php echo $radio["leisure"][3]; ?>>&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="4" <?php echo $radio["leisure"][4]; ?>>&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="leisure" value="5" <?php echo $radio["leisure"][5]; ?>>&nbsp;5&nbsp;</label>
						</div>

						<label class="col-sm-3 control-label control-label-padding">
							Цена/качество
						</label>
						<div class="col-sm-3 btn-group block-ratio" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="2" <?php echo $radio["ratio"][2]; ?>>&nbsp;2&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="3" <?php echo $radio["ratio"][3]; ?>>&nbsp;3&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="4" <?php echo $radio["ratio"][4]; ?>>&nbsp;4&nbsp;</label>
								<label class="btn btn-xs btn-primary"><input type="radio" name="ratio" value="5" <?php echo $radio["ratio"][5]; ?>>&nbsp;5&nbsp;</label>
						</div>

					</div>
					<div class="form-group form-group-margin">
						<label class="col-sm-3 control-label control-label-padding">
							Месторасположение
						</label>
						<div class="col-sm-3 btn-group block-location" data-toggle="buttons">
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="2" <?php echo $radio["location"][2]; ?>>&nbsp;2&nbsp;</label>&nbsp;&nbsp;
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="3" <?php echo $radio["location"][3]; ?>>&nbsp;3&nbsp;</label>&nbsp;&nbsp;
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="4" <?php echo $radio["location"][4]; ?>>&nbsp;4&nbsp;</label>&nbsp;&nbsp;
								<label class="btn btn-xs btn-primary"><input type="radio" name="location" value="5" <?php echo $radio["location"][5]; ?>>&nbsp;5&nbsp;</label>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_rating('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_rating($connect){
	$id = $_POST["id"];
	$positive = $_POST["positive"];
	$negative = $_POST["negative"];
	$advice = $_POST["advice"];
	$company = $_POST["company"];
	$clean = $_POST["clean"];
	$treatment = $_POST["treatment"];
	$staff = $_POST["staff"];
	$location = $_POST["location"];
	$leisure = $_POST["leisure"];	
	$ratio = $_POST["ratio"];
	$comfort = $_POST["comfort"];
	$connect->query("UPDATE rating SET clean=?i, staff=?i, location=?i, leisure=?i, ratio=?i, comfort=?i, positive=?s, negative=?s, advice=?s, company_rating=?s, synchronized = 0 WHERE id=?i", $clean, $staff, $location, $leisure, $ratio, $comfort, $positive, $negative, $advice, $company, $id);
	if($treatment)
		$connect->query("UPDATE rating SET treatment=?i, synchronized = 0 WHERE id=?i", $treatment, $id);
}

function confirm_rating($connect){
	$connect->query("UPDATE rating SET status=3, synchronized = 0 WHERE id=?i", $_POST["id"]);
	save_crm_user_history($connect, 'Отзыв c ID='.$_POST['id'].' принят');
	$data = check_rating_count($connect);
	return json_encode($data);
}

function delete_rating($connect){
	$connect->query("UPDATE rating SET status=4, synchronized = 0 WHERE id=?i", $_POST["id"]);
	save_crm_user_history($connect, 'Отзыв c ID='.$_POST['id'].' отправлен в архив');
}

function show_rating_comment($connect){
	if($connect->getOne("SELECT id FROM rating_comment WHERE status=0")){
		$row = $connect->getRow("SELECT id, DATE_FORMAT(time, '%d.%m.%Y') as date, name, email, text, rating, website FROM rating_comment WHERE status=0");
		$id = $row["id"];
		$rating = $connect->getRow("SELECT positive, negative, advice, company_rating FROM rating WHERE id=?i", $row["rating"]);
	ob_start();
?>
	<div class="form-horizontal panel panel-info">
		<div class="panel-heading">
			<i class="fa fa-comment-o"></i> Новый комментарий
		</div>
		<div class="list-group">
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Отзыв</label>
					<div class="col-sm-9">
				<?php if($rating["positive"]){ ?>
					<div class="alert alert-success"><i class="fa fa-plus-circle"></i> <?php echo $rating["positive"]; ?></div>
				<?php } ?>
				<?php if($rating["negative"]){ ?>
					<div class="alert alert-danger"><i class="fa fa-minus-circle"></i> <?php echo $rating["negative"]; ?></div>
				<?php } ?>
				<?php if($rating["advice"]){ ?>
					<div class="alert alert-info"><i class="fa fa-thumbs-o-up"></i> <?php echo $rating["advice"]; ?></div>
				<?php } ?>
				<?php if($rating["company_rating"]){ ?>
					<div class="alert alert-default"><i class="fa fa-smile-o"></i> <?php echo $rating["company_rating"]; ?></div>
				<?php } ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Дата комментария</label>
					<div class="col-sm-9">
						<?php echo $row["date"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Сайт</label>
					<div class="col-sm-9">
						<?php echo $row["website"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Имя</label>
					<div class="col-sm-9">
						<?php echo $row["name"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Email</label>
					<div class="col-sm-9">
						<?php echo $row["email"]; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-3 control-label-element">Комментарий</label>
					<div class="col-sm-9">
						<?php echo $row["text"]; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer text-right">
			<button  type="button" class="btn btn-success btn-sm" onclick="confirm_rating_comment('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Принять</button>
			<button  type="button" class="btn btn-default btn-sm" onclick="edit_rating_comment('<?php echo $id; ?>')"><i class="fa fa-pencil"></i> Изменить</button>
			<button  type="button" class="btn btn-danger btn-sm" onclick="delete_rating_comment('<?php echo $id; ?>')"><i class="fa fa-trash-o"></i> В архив</button>
		</div>
	</div>
<?php
	$html = ob_get_clean();
	}else{
		$all = $connect->getOne("SELECT COUNT(*) FROM rating_comment");
		$confirm = $connect->getOne("SELECT COUNT(*) FROM rating_comment WHERE status=1");
		$trash = $connect->getOne("SELECT COUNT(*) FROM rating_comment WHERE status=2");
?>
	<div class="form-horizontal panel panel-info">
		<div class="panel-heading">
			<i class="fa fa-comments-o"></i> Статистика по комментариям
		</div>
		<div class="list-group">
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Всего комментариев</label>
					<div class="col-sm-8">
						<?php echo $all; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Комментариев принято</label>
					<div class="col-sm-8">
						<?php echo $confirm; ?>
					</div>
				</div>
			</div>
			<div class="list-group-item list-hover-item">
				<div class="form-group form-group-margin">
					<label class="col-sm-4 control-label-element">Комментариев в архиве</label>
					<div class="col-sm-8">
						<?php echo $trash; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
		$html = ob_get_clean();
	}
	return $html;
}

function edit_rating_comment($connect){
	$id = $_POST["id"];
	$row = $connect->getRow("SELECT name, email, text FROM rating_comment WHERE id=?i", $id);
?>
<div class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
				<h4 class="modal-title">Изменить комментарий к отзыву</h4>
			</div>
			<div class="modal-body edit-rating-comment">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label control-label-left">Имя</label>
						<div class="col-sm-9">
							<input type="text" class="form-control name" value="<?php echo $row['name']; ?>" />
						</div>
					</div>
				</div>
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label control-label-left">Email</label>
						<div class="col-sm-9">
							<input type="text" class="form-control email" value="<?php echo $row['email']; ?>" />
						</div>
					</div>
				</div>
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label control-label-left">Комментарий</label>
						<div class="col-sm-9">
							<textarea class="form-control text"><?php echo $row["text"]; ?></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="update_rating_comment('<?php echo $id; ?>')"><i class="fa fa-check-circle"></i> Сохранить</button>
			</div>
		</div>
	</div>
</div>
<?php
}

function update_rating_comment($connect){
	$id = $_POST["id"];
	$name = $_POST["name"];
	$email = $_POST["email"];
	$text = $_POST["text"];
	$connect->query("UPDATE rating_comment SET name=?s, email=?s, text=?s WHERE id=?i", $name, $email, $text, $id);
}

function confirm_rating_comment($connect){
	$connect->query("UPDATE rating_comment SET status=1 WHERE id=?i", $_POST["id"]);
	$data = check_rating_count($connect);
	return json_encode($data);
}

function delete_rating_comment($connect){
	$connect->query("UPDATE rating_comment SET status=2 WHERE id=?i", $_POST["id"]);
}

function admin_rating_comment(){
?>
<div class="panel panel-default form-horizontal admin-rating-comment">
	<div class="panel-body">
		<div class="form-group">
			<label class="col-sm-3 control-label">Номер заявки</label>
			<div class="col-sm-9">
				<input type="text" class="form-control number-bid" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Автор</label>
			<div class="col-sm-9">
				<input type="text" class="form-control author" value="Администратор" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Комментарий</label>
			<div class="col-sm-9">
				<textarea class="form-control rating-comment"></textarea>
			</div>
		</div>
	</div>
	<div class="panel-footer text-right">
		<button class="btn btn-sm btn-success" onclick="send_admin_rating_comment()"><i class="fa fa-check-circle"></i> Сохранить комментарий</button>
	</div>
</div>
<?php
}

function send_admin_rating_comment($connect){
	$bid = $_POST["bid"];
	$author = $_POST["author"];
	$text = $_POST["comment"];
	$rating = $connect->getOne("SELECT id FROM rating WHERE schet=?i", $bid);
	if($text AND $rating){
		$connect->query("INSERT INTO rating_comment(rating, name, text, website) VALUES (?i, ?s, ?s, 'CRM')", $rating, $author, $text);
?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> Ваш комментарий успешно сохранен.</div>
<?php
	}
}

?>
