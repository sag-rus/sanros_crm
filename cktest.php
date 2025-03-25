<?php
	$version = "05-03-2025-2";
    $loader = require( __DIR__ . '/vendor/autoload.php');
    /*$payment = new \App\lib\payment\Sberbank\BookingPayment([

    ]);
    die();*/

?>

<!DOCTYPE html>
<html>
<head>

	<title>CRM БОНУСЫ</title>

	<script type="text/javascript" src="js/jquery-1.9.1.js"></script>
    <script type="text/javascript" src="js/ckeditor/ckeditor.js?v=<?=$version;?>"></script>
    <script type="text/javascript" src="js/ckeditor/ru.js?v=<?=$version;?>"></script>
    <script type="text/javascript" src="js/highcharts.js"></script>
	<script type="text/javascript" src="js/ajaxupload.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="js/jquery.metadata.js"></script>
	<script type="text/javascript" src="js/fullcalendar.js"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>
	<script type="text/javascript" src="js/jquery.inputmask.js"></script>
	<script type="text/javascript" src="js/jquery.inputmask.date.extensions.js"></script>
	<script type="text/javascript" src="js/jquery.ba-throttle-debounce.min.js"></script>
	<script type="text/javascript" src="js/jquery.mousewheel.js"></script>
	<script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
	<script type="text/javascript" src="js/strophe/strophe.js"></script>
	<script type="text/javascript" src="js/strophe/plugin.roster.js"></script>
	<script type="text/javascript" src="modules/function-cktest.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-image.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-schet.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-users.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-object.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-price.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-admin.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-chat.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-head.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-agency.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-report.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-touroperator.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-profit.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-mail.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-reminder.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-promotions.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-question.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-profile.js?ver=<?php echo $version; ?>"></script>
	<script type="text/javascript" src="modules/module-manual.js?ver=<?php echo $version; ?>"></script>
    <script type="text/javascript" src="https://cdn.tonia.ru/static-assets/damirez-uploader/js/damirez-uploader.js?ver=<?php echo $version; ?>"></script>
    <!--<script type="text/javascript" src="/CRM/damirez-uploader.js?ver=<?php echo $version; ?>"></script>-->
    <!--link rel="stylesheet" href="js/ckeditor/style.css"-->
	<link href="font/font-awesome/css/font-awesome.css" rel="stylesheet">
	<link rel="stylesheet" href="css/style.css?ver=<?php echo $version; ?>">
	<link rel="stylesheet" href="css/jquery-ui.css?ver=<?php echo $version; ?>">
	<link rel="stylesheet" href="css/style-calendar.css?ver=<?php echo $version; ?>">
	<link rel="stylesheet" href="css/style-color.css?ver=<?php echo $version; ?>">
	<link rel="stylesheet" href="css/style-chat.css?ver=<?php echo $version; ?>">
	<link rel="stylesheet" href="css/dots.css">
	<link href="css/fullcalendar.css" rel="stylesheet" />
	<link href="css/bootstrap.css?ver=<?php echo $version; ?>" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.tonia.ru/static-assets/damirez-uploader/css/damirez-uploader.css?ver=<?php echo $version; ?>">
    <!--<link rel="stylesheet" href="/CRM/css/damirez-uploader.css?ver=<?php echo $version; ?>">-->
	<link rel="shortcut icon" href="favicon.ico">
    <meta charset="utf-8" />

</head>
<body>
</body>

<?php if ($_SERVER['REMOTE_ADDR']=='10.10.11.5') { ?>
TEST:
<div id="sites_content_body_test"></div>
<button onClick="add_new_sites_content2(1)">open</button>
<script>

function add_new_sites_content2(site_id) {
   var html = '<div class="modal fade sites-content-modal">' +
								'<div class="modal-dialog">' +
									'<div class="modal-content">' +
										'<div class="modal-header">' +
											'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>' +
											'<h4 class="modal-title">Новый материал</h4>' +
										'</div>' +
										'<div class="modal-body form-horizontal site-name">' +
											'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Заголовок</label>' +
												'<div class="col-sm-10">' +
													'<input type="text" class="form-control" name="title" maxlength="255">' +
			 										'<input type="hidden" value="'+site_id+'" name="site_id">'+
													'<input type="hidden" value="0" name="content_id">'+
													'<div class="input-message-block" data-for="title"></div>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
                          '<label class="col-sm-2 control-label">Заголовок к крошкам</label>' +
                          '<div class="col-sm-10">' +
                              '<input type="text" class="form-control" name="breadcrumb_title" maxlength="255">' +
                              '<div class="input-message-block" data-for="breadcrumb_title"></div>' +
                          '</div>' +
                      '</div>'+

			 								'<div class="form-group hidden">'+
												'<label class="col-sm-2 control-label">Агрегация по датам</label>'+
												'<div class="col-sm-10">'+
												'<select class="form-control" name="aggregation_by_dates">'+
													'<option value="0" selected="">Нет</option>'+
													'<option value="1">Да</option>'+
												'</select>'+
												'</div>'+
											'</div>'+
			 								'<div class="form-group hidden">'+
                          '<label class="col-sm-2 control-label">Начальная дата</label>'+
                          '<div class="col-sm-10">'+
                              '<input type="datetime-local" name="aggregation_date_start" class="form-control" value="1970-01-01T03:00">'+
                          '</div>'+
                      '</div>'+
			 								'<div class="form-group hidden">'+
                          '<label class="col-sm-2 control-label">Конечная дата</label>'+
                          '<div class="col-sm-10">'+
                              '<input type="datetime-local" name="aggregation_date_end" class="form-control" value="1970-01-01T03:00">'+
                          '</div>'+
                      '</div>'+
                      '<div class="form-group hidden">' +
                          '<label class="col-sm-2 control-label">Адрес основного агрегатора</label>' +
                          '<div class="col-sm-10">' +
                              '<input type="text" class="form-control" name="rss_aggregator_link" value="" maxlength="512">' +
                              '<div class="input-message-block" data-for="rss_aggregator_link"></div>' +
                          '</div>' +
                      '</div>' +
                      '<div class="form-group hidden">' +
                          '<label class="col-sm-2 control-label">Дополнения в RSS</label>' +
                          '<div class="col-sm-10">' +
                              '<textarea class="form-control" name="rss_addition"></textarea>' +
                          '</div>' +
                      '</div>' +
                      '<div class="form-group hidden">'+
                          '<label class="col-sm-2 control-label">Генерировать адрес</label>' +
                          '<input type="checkbox" name="path_autogenerate">'+
                      '</div>'+
			 								'<div class="form-group hidden">' +
												'<label class="col-sm-2 control-label">Адрес редиректа</label>' +
												'<div class="col-sm-10">' +
													'<input type="text" class="form-control" name="redirect_path" maxlength="512">' +
													'<div class="input-message-block" data-for="redirect_path"></div>'+
												'</div>' +
											'</div>' +
											 '<div class="form-group with-bottom-margin hidden">' +
													'<label class="col-sm-2 control-label">Список материалов</label>' +
													'<div class="col-sm-10">' +
													'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="1" id="aggregate_types_0"> <label class="control-label" for="aggregate_types_0">Лэндинг</label>' +
													'</div>' +
													'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="2" id="aggregate_types_1"> <label class="control-label" for="aggregate_types_1">Фотогалерея</label>' +
													'</div>' +
													'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="3" id="aggregate_types_2"> <label class="control-label" for="aggregate_types_2">Новость</label>' +
													'</div>' +
													'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="4" id="aggregate_types_3"> <label class="control-label" for="aggregate_types_3">Страница</label>' +
													'</div>' +
			 										'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="6" id="aggregate_types_4"> <label class="control-label" for="aggregate_types_4">Настройки</label>' +
													'</div>' +
													'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="7" id="aggregate_types_5"> <label class="control-label" for="aggregate_types_5">Статья</label>' +
													'</div>' +
													'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="8" id="aggregate_types_6"> <label class="control-label" for="aggregate_types_6">Полезная информация</label>' +
													'</div>' +
			 										'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="10" id="aggregate_types_7"> <label class="control-label" for="aggregate_types_7">Советы эксперта</label>' +
													'</div>' +
													'<div class="checkbox-container">' +
														'<input type="checkbox" class="form-control" name="aggregate_types" value="11" id="aggregate_types_8"> <label class="control-label" for="aggregate_types_8">Блог</label>' +
													'</div>' +
													'<div class="with-bottom-margin"></div>' +
													'<div class="input-message-block" data-for="aggregate_types"></div>' +
												'</div>' +
			                 '</div>' +
			 								'<div class="form-group hidden">' +
												'<label class="col-sm-2 control-label">ID объекта</label>' +
			 									'<div class="col-sm-10">'+
			 										'<input type="number" class="form-control" min="1" name="module_object_id">' +
													'<div class="input-message-block" data-for="module_object_id"></div>'+
			 									'</div>'+
			 								'</div>'+
			 								'<div class="form-group hidden">' +
                          '<label class="col-sm-2 control-label">Блок модуля</label>'+
                          '<div class="col-sm-10">' +
                              '<select class="form-control" name="module_block">' +
                                  '<option value="">Выберите блок для отображения...</option>' +
                                  '<option value="rooms">Номера и цены</option>' +
                                  '<option value="desc">Описание</option>' +
                                  '<option value="promo">Акции</option>' +
                                  '<option value="rating">Отзывы</option>' +
                              '</select>' +
			 												'<div class="input-message-block" data-for="module_block"></div>'+
                          '</div>' +
                      '</div>' +
                      '<div class="form-group">' +
												'<label class="col-sm-2 control-label">Адрес для формы поиска</label>' +
												'<div class="col-sm-10">' +
													'<input type="text" class="form-control" name="form_action" maxlength="512">' +
													'<div class="input-message-block" data-for="form_action"></div>'+
                        '</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Двухуровневый фон</label>' +
												'<div class="col-sm-10">' +
													'<input type="checkbox" name="second_bg" class="form-control">'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Мета-описание</label>' +
												'<div class="col-sm-10">' +
													'<textarea class="form-control" name="description"></textarea>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Ключевые слова (через запятую)</label>' +
												'<div class="col-sm-10">' +
													'<textarea class="form-control" name="keywords"></textarea>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Содержимое</label>' +
												'<div class="col-sm-10">' +
													'<textarea class="form-control resizable-textarea" name="body" id="sites_content_body"></textarea>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Доп. содержимое</label>' +
												'<div class="col-sm-10">' +
													'<textarea class="form-control resizable-textarea" name="body2" id="sites_content_body2"></textarea>'+
												'</div>' +
											'</div>' +
			                 '<div class="form-group hidden">' +
			                     '<label class="col-sm-2 control-label">Направление</label>' +
													 '<div class="col-sm-10">' +
															'<select class="form-control direction-selector" name="direction_id">' +
																'<option value="0">Без направления</option>' +
			 													'<option value="32">Дальний Восток</option>' +
																'<option value="24">Крым</option>' +
																'<option value="21">Поволжье</option>' +
																'<option value="25">Северный Кавказ</option>' +
																'<option value="28">Северо-Запад</option>' +
																'<option value="29">Сибирь</option>' +
																'<option value="22">Урал</option>' +
																'<option value="26">Центр России</option>' +
																'<option value="23">Юг России</option>' +
															'</select>'+
													 '</div>' +
											'</div>' +
			                				'<div class="form-group hidden">' +
													'<label class="col-sm-2 control-label">Регион</label>' +
													'<div class="col-sm-10">' +
											 				'<select class="form-control" name="region_id">' +
											 					'<option value="0">Без региона</option>' +
											 				'</select>' +
											 		'</div>' +
										 	'</div>' +
			 								'<div class="form-group hidden">' +
													'<label class="col-sm-2 control-label">Рег. направление</label>' +
													'<div class="col-sm-10">' +
															'<select class="form-control" name="regional_direction_id">' +
																	 '<option value="0">Не выбрано</option>' +
															'</select>' +
													'</div>' +
											'</div>' +
										    '<div class="form-group hidden">' +
												'<label class="col-sm-2 control-label">ID объектов</label>' +
												'<div class="col-sm-10">' +
												  '<input class="form-control" type="text" name="resorts_ids">' +
												  '<div class="input-message-block" data-for="resorts_ids"></div>' +
												'</div>' +
										    '</div>' +
			 								'<div class="form-group">' +
											  '<label class="col-sm-2 control-label">Код карты</label>' +
											  '<div class="col-sm-10">' +
												  '<textarea class="form-control" name="map_code"></textarea>' +
											  '</div>' +
										  '</div>' +
										  '<div class="form-group">' +
											  '<label class="col-sm-2 control-label">Вводный текст</label>' +
											  '<div class="col-sm-10">' +
												  '<textarea class="form-control" name="landing_info"></textarea>' +
											  '</div>' +
										  '</div>' +
	                                       '<div class="form-group">' +
												  '<label class="col-sm-2 control-label">Код в блоке head</label>' +
												  '<div class="col-sm-10">' +
													  '<textarea class="form-control" name="head_code"></textarea>' +
												  '</div>' +
	                                       '</div>' +
	                                       '<div class="form-group">' +
												  '<label class="col-sm-2 control-label">Код в начале элемента body</label>' +
												  '<div class="col-sm-10">' +
													  '<textarea class="form-control" name="pre_body_code"></textarea>' +
												  '</div>' +
	                                       '</div>' +
										   '<div class="form-group">' +
											  '<label class="col-sm-2 control-label">Код в конце элемента body</label>' +
											  '<div class="col-sm-10">' +
												  '<textarea class="form-control" name="post_body_code"></textarea>' +
											  '</div>' +
										    '</div>' +
											 '<div class="form-group">' +
											 '<label class="col-sm-2 control-label">Телефон</label>' +
											 '<div class="col-sm-10">' +
											 '<input type="text" class="form-control" name="phone">' +
											 '</div>' +
											 '</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Дата и время публикации</label>' +
												'<div class="col-sm-10">' +
													'<input type="datetime-local" name="published" class="form-control">'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Вес материала (для Sitemap)</label>' +
												'<div class="col-sm-10">' +
													'<input type="number" name="weight" class="form-control" min="0" max="1" value="0.9">'+
													'<div class="input-message-block" data-for="weight"></div>'+
												'</div>' +
											'</div>' +
											'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Вес материала (сортировка)</label>' +
												'<div class="col-sm-10">' +
													'<input type="number" name="sort" class="form-control" value="0">'+
													'<div class="input-message-block" data-for="sort"></div>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Разрешить RSS-агрегацию</label>' +
												'<div class="col-sm-10">' +
													'<input type="checkbox" name="rss_aggregation" class="form-control">'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Опубликовано</label>' +
												'<div class="col-sm-10">' +
													'<input type="checkbox" name="status" class="form-control">'+
												'</div>' +
											'</div>' +
											'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Закрепить на главной</label>' +
												'<div class="col-sm-10">' +
													'<input type="checkbox" name="main_page_fix" class="form-control">'+
												'</div>' +
											'</div>' +
										'</div>' +
										'<div class="modal-loader"></div>'+
										'<div class="modal-footer">' +
											'<button class="btn btn-success btn-sm btn-save-new-sites-content" onclick="set_sites_content()" id="btn-save-new-sites-content"><i class="fa fa-check-circle"></i> Добавить</button>' +
										'</div>' +
									'</div>' +
								'</div>' +
							'</div>';

	//$('#sites_content_body_test').html(html);
    show_modal(html);

	$('#sites_content_body').replaceWith('<div id="sites_content_body"></div>');
	$('#sites_content_body2').replaceWith('<div id="sites_content_body2"></div>');

	DecoupledEditor
		.create( $('#sites_content_body').get(0), {
			language: 'ru'
		})
		.then( editor => {

			$('#sites_content_body').before('<div id="sites_content_body_toolbar_container"></div>');

			const toolbarContainer = $('#sites_content_body_toolbar_container').get(0);

			toolbarContainer.appendChild( editor.ui.view.toolbar.element );

			window.sites_content_body = editor;
		})
		.catch( error => {
			console.error( error );
		});

	/*DecoupledEditor
		.create( $('#sites_content_body2').get(0), {
			language: 'ru'
		})
		.then( editor2 => {

			$('#sites_content_body2').before('<div id="sites_content_body2_toolbar_container"></div>');

			const toolbarContainer2 = $('#sites_content_body2_toolbar_container').get(0);

			toolbarContainer2.appendChild( editor2.ui.view.toolbar.element );

			window.sites_content_body2 = editor2;
		})
		.catch( error => {
			console.error( error );
		});


  $('.sites-content-modal *[name="slider_photos"], .sites-content-modal *[name="slider_photos_mobile"], .sites-content-modal *[name="photogallery"]').multUploader({
    action:'mysql.php?func=multipart_upload',
    fragmentSize:1024*1024,
    contentType:['image/jpeg','image/png']
  });

  $('.sites-content-modal *[name="image"], .sites-content-modal *[name="page_bg"]').multUploader({
    action:'mysql.php?func=multipart_upload',
    fragmentSize:1024*1024,
		maxcount: 1,
    contentType:['image/jpeg','image/png']
  });

  var $typeFilter = $('#content-type-filter');
  var typeFilter = 'all';
  if($typeFilter.length > 0) {
  	typeFilter = $typeFilter.val();
	}

  if(typeFilter !== 'all') {
		$('.sites-content-modal *[name="type"]').val(typeFilter).change();
	}*/
}


	$(document).ready(function (e) {

        //add_new_sites_content2(1);

		/*DecoupledEditor
			.create( $('#sites_content_body_test').get(0), {
				language: 'ru'
			})
			.then( editor4 => {

				$('#sites_content_body_test').before('<div id="sites_content_body_test_toolbar_container"></div>');

				const toolbarContainer_cabinet = $('#sites_content_body_test_toolbar_container').get(0);

				toolbarContainer_cabinet.appendChild( editor4.ui.view.toolbar.element );

				window.sites_content_body_test = editor4;
				window.sites_content_body_test.setData('123');
			})
			.catch( error => {
				console.error( error );
			});*/		
    });
</script>
<?php } ?>
</html>