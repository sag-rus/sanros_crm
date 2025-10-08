var old_i;

function object(id){
	select_menu('price_menu', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=select_country_admin&id=' + id,
		success: function(html){
			$('#body').html(html);
			select_region();
			select_direction('country');
		}
	});
}

function select_region(id, type){
	var direction = $('#direction-country').val();
	var country = $('#country').val();
	if((!direction || type == 'country') && type != 'direction')
		select_direction('country');
	else{
		var str = 'func=select_region_admin&country=' + country + '&id=' + id + '&direction=' + direction;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.regions').html(html);
				select_object();
			}
		});
	}
}

function select_object(id, type, type_direction){
	var region = $('#region').val();
	if(!region)
		region = '';
	if(type_direction == 'region')
		var direction = $('#direction-region').val();
	else{
		select_direction('region');
		var direction = $('#direction-country').val();
	}
	var str = 'func=select_object_admin&region=' + region + '&id=' + id + '&direction=' + direction + '&type=' + type_direction;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.objects').html(html);
			select_menu_object();
		}
	});
}

function select_direction(type){
	var region = $('#region').val();
	var country = $('#country').val();
	var str = 'func=select_direction_admin&region=' + region + '&country=' + country + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.direction-'+type).html(html);
			if(type == 'country')
				select_region('', 'direction');
		}
	});
}

function add_new_country(){
	var str = 'func=add_new_country';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function add_new_region(){
	var country = $('#country').val();
	var str = 'func=add_new_region&country=' + country;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function add_new_direction(type){
	var region = $('#region').val();
	var country = $('#country').val();
	var str = 'func=add_new_direction&region=' + region + '&country=' + country + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function add_new_object(){
	var region = $('#region').val();
	var str = 'func=add_new_object&region=' + region;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_country(){
	var country = $('.name-country').val();
	if(!country)
		show_warning('.new-country', 'Введите название', false);
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: 'func=save_new_country&name=' + country,
			success: function(id){
				remove_all_windows();
				object(id);
				show_alert('Страна сохранена...');
			}
		});
	}
}

function save_new_region(country){
	var region = $('.new-region-modal .name-region').val();
	var man_reward_scheme = $('.new-region-modal .man_reward_scheme').val();
	if(!region)
		show_warning('.new-region', 'Введите название', false);
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: 'func=save_new_region&name=' + region + '&country=' + country+'&man_reward_scheme='+man_reward_scheme,
			success: function(id){
				if(!id)
					show_warning('.new-region', 'Такой регион уже существует', false);
				else{
					remove_all_windows();
					select_region(id);
					show_alert('Регион сохранен...');
				}
			}
		});
	}
}

function save_new_direction(id, type){
	var direction = $('.name-direction').val();
	var name_rod = $('.name-rod-direction').val();
	var sort = $('.name-sort-direction').val();
	if(!direction)
		show_warning('.new-direction', 'Введите название', false);
	else{
		var str = 'func=save_new_direction&name=' + direction + '&id=' + id + '&type=' + type+'&name_rod='+name_rod+'&sort='+sort;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_direction(type);
				show_alert('Направление сохранено...');
			}
		});
	}
}

function save_new_object_region(region){
	var object = $('.name-object').val();
	if(!object)
		show_warning('.new-object', 'Введите название', false);
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: 'func=save_new_object_region&name=' + object + '&region=' + region,
			success: function(id){
				remove_all_windows();
				select_object(id);
				show_alert('Объект сохранен...');
			}
		});
	}
}

function select_menu_object(object,noCheckLocation){
	if(!object)
		var object = $('#object-admin').val();

	if(typeof noCheckLocation === 'undefined')
		noCheckLocation = false;

	var str = 'func=select_menu_object&id=' + object+'&no_check_location='+(noCheckLocation*1);
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-menu').html(html);
			select_object_about(object);
		}
	});
}

function select_object_room(){
	$('.menu-object li').removeClass('active');
	$('.menu-room').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var str = 'func=select_object_room&id=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function select_object_occupancies(){
	$('.menu-object li').removeClass('active');
	$('.menu-occupancies').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var str = 'func=select_object_occupancies&id=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function del_room_occupancy(id){
	if(confirm('Точно удалить?')){
		var str = 'func=del_room_occupancy&id=' + id;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(){
				$('#room_occupancy_'+id).remove();
			}
		});
	}
}

function new_room_occupancy(id){
	$('.new-room-occupancy').remove();
	var id_obj = $('.object-menu .menu-object').attr('object');
	var str = 'func=room_occupancy&id=' + id + '&id_obj=' + id_obj;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.new-room-occupancy').remove();
			$('.tbl-room').append(html);
		}
	});
}

function save_room_occupancy(id){
	var id_obj = $('.object-menu .menu-object').attr('object');
	
	str = 'func=save_room_occupancy&id=' + id + '&id_obj=' + id_obj + '&' + $('.new-room-occupancy-form').serialize();

	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_object_occupancies();
			show_alert('Размещение сохранено...');
		}
	});

}

function del_obj_cert(){
	if(confirm('Точно удалить?')) {
		$('.menu-object li').removeClass('active');
		$('.menu-object-cert').addClass('active');
		var object = $('.object-menu .menu-object').attr('object');
		var str = 'func=show_obj_cert&id=' + object + '&clear_accr=1';
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.object-infa').html(html);
			}
		});
	}
}

function select_obj_cert(id_accr){
	$('.menu-object li').removeClass('active');
	$('.menu-object-cert').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var accr_search = $('#accr_search').val();
	var str = 'func=show_obj_cert&id=' + object + '&id_accr='+id_accr;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function search_obj_cert(){
	$('.menu-object li').removeClass('active');
	$('.menu-object-cert').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var accr_search = $('#accr_search').val();
	var str = 'func=show_obj_cert&id=' + object + '&accr_search='+accr_search;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}


function show_obj_cert(){
	$('.menu-object li').removeClass('active');
	$('.menu-object-cert').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var str = 'func=show_obj_cert&id=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}


function select_object_child_occupancies(){
	$('.menu-object li').removeClass('active');
	$('.menu-child_occupancies').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var str = 'func=select_object_child_occupancies&id=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function new_child_occupancy(id){
	$('.new-child-occupancy').remove()
	var str = 'func=child_occupancy&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.new-child-occupancy').remove();
			$('.tbl-room').append(html);
		}
	});
}

function del_child_occupancy(id){
	if(confirm('Точно удалить?')){
		var str = 'func=del_child_occupancy&id=' + id;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(data){
				if (data=='used_on_occupancies') alert('Это детское размещение используется в размещениях. Удаление невозможно.');
				else $('#child_occupancy_'+id).remove();
			}
		});
	}
}

function save_child_occupancy(id){
	var id_obj = $('.object-menu .menu-object').attr('object');
	var age_from = $('#age_from').val();
	var age_to = $('#age_to').val();
	var str = 'func=save_child_occupancy&id=' + id + '&id_obj=' + id_obj + '&age_from=' + age_from + '&age_to=' + age_to;
	if(!age_from || !age_to)
		show_warning('.new-room-div', 'Введите возраст от и до');
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_object_child_occupancies();
				show_alert('Размещение сохранено...');
			}
		});
	}
}


function select_object_housing(){
	$('.menu-object li').removeClass('active');
	$('.menu-housing').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var str = 'func=select_object_housing&id=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function add_new_housing(id){
	var str = 'func=add_new_housing&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_housing(id){
	var name = $('.name-housing').val();
	var desc = $('.desc-housing').val();
	if(!name)
		show_warning('.new-housing', 'Введите название корпуса', false);
	else{
		var str = 'func=save_new_housing&id=' + id + '&name=' + name + '&desc=' + desc;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_object_housing();
			}
		});
	}
}

function select_object_upload(object){
	$('.menu-object li').removeClass('active');
	$('.menu-upload').addClass('active');
	var html = '<button type="button" data-toggle="tooltip" data-placement="bottom" title="Загрузить на сайты цены номеров объекта по датам" class="btn btn-primary" onclick="upload_object_price_on_server(' +object+ ')" data-toggle="tooltip" data-placement="bottom" title="Загрузить на сайты цены намеров объекта по датам"><i class="fa fa-cloud-upload"></i> Обновить цены объекта на сайтах</button> <button type="button" class="btn btn-primary" onclick="upload_images_object(' +object+ ')" data-toggle="tooltip" data-placement="bottom" title="Выгрузить все фото объекта"><i class="fa fa-picture-o"></i> Загрузить фото объекта</button>';
	html+= '<hr />';
	html+= '<button type="button" data-toggle="tooltip" data-placement="bottom" title="Обновление цены номеров для всех объектов" class="btn btn-success" onclick="upload_price_on_server()"><i class="fa fa-cloud-upload"></i> Обновить цены для всех объектов</button> <button type="button" class="btn btn-success" onclick="upload_information_object()" data-toggle="tooltip" data-placement="bottom" title="Синхронизировать все объекты (название, описание, лечение, отзывы, номерной фонд и т.п.), регионы и направления"><i class="fa fa-cloud-upload"></i> Обновить информацию</button> <button type="button" class="btn btn-primary" onclick="update_rating_on_site()" data-toggle="tooltip" data-placement="bottom" title="Обновить отзывы объекта"><i class="fa fa-cloud-upload"></i> Обновить отзывы</button> <button type="button" class="btn btn-info" onclick="upload_methods()" data-toggle="tooltip" data-placement="bottom" title="Обновить методы объектов (название, описание, изображение)"><i class="fa fa-medkit"></i> Обновить методы лечения</button> <!--button type="button" class="btn btn-success"  data-toggle="tooltip" data-placement="bottom" title="Синхронизация баз данных CRM и сервера: добавление новых объектов и обвление типа квоты для существующих" onclick="sync_server_database()"><i class="fa fa-refresh"></i> Синхронизировать данные БД на tonia.ru</button>-->';
//	html+= "<a href='upload.php?func=download_price_object&id=" +object+ "' class='btn btn-info btn-sm' target='_blank' style='text-decoration: none'><i class='fa fa-download'></i> Скачать цены HTML</a><br /><br />";
//	html+= "<button type='button' class='btn btn-danger btn-sm' onclick='delete_photo_from_server(\"" +object+ "\")'><i class='fa fa-times-circle'></i> Удалить все фото на сервере</button>";
	html += ' <button type="button" class="btn btn-success"  data-toggle="tooltip" data-placement="bottom" title="Выгрузить информацию об объектах в новое API" onclick="sync_objects_api();"><i class="fa fa-refresh"></i> Синхронизировать объекты</button>';
	html += ' <button type="button" class="btn btn-success"  data-toggle="tooltip" data-placement="bottom" title="Синхронизировать отзывы через API" onclick="sync_objects_ratings_api();"><i class="fa fa-refresh"></i> Синхронизировать отзывы</button>';

	$('.object-infa').html(html);
}

function upload_price_on_server(){
	show_loader_element('.object-infa');
	var str = 'func=upload_price_on_server';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(answer){
			if(answer)
				$('.object-infa').html(answer);
			else
				$('.menu-object .label-success').trigger('click');
		}
	});
}

function upload_information_object(){
	show_loader_element('.object-infa');
	var str = 'func=upload_information_object&cache='+Math.random();
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(answer){
			if(answer)
				$('.object-infa').html(answer);
			else
				$('.menu-object .label-success').trigger('click');
		}
	});
}

function sync_objects_api(){
	show_loader_element('.object-infa');
	var str = 'func=sync_objects_api&cache='+Math.random();
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(answer){
			if(answer)
				$('.object-infa').html(answer);
			else
				$('.menu-object .label-success').trigger('click');
		}
	});
}

function sync_objects_ratings_api(){
	show_loader_element('.object-infa');
	var str = 'func=sync_objects_ratings_api&cache='+Math.random();
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(answer){
			if(answer)
				$('.object-infa').html(answer);
			else
				$('.menu-object .label-success').trigger('click');
		}
	});
}

function upload_images_object(object){
	show_loader_element('.object-infa');
	var str = 'func=upload_image_object_server&object=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function select_object_about(object){
	if(!object)
		object = $('#object-admin').val();
	$('.menu-object li').removeClass('active');
	$('.menu-infa').addClass('active');
	var str = 'func=select_object_about&id=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function service(){
	old_i = 0;
	select_menu('service_open', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=see_services',
		success: function(html){
			$('#body').html(html);
		}
	});

}

function add_new_services(){
	var str = 'func=new_service';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_services(){
	var name = $('#name').val();
	var icon = $('#icon').val();
	if(!name)
		show_warning('.new-service', 'Укажите название', false);
	else{
		var str = 'func=save_new_services&name=' + name + '&icon=' + icon;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				show_alert('Сохранено...');
				service();
			}
		});
	}
}

function edit_service(id){
	var str = 'func=edit_service&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_service(id){
	var name = $('#name').val();
	var icon = $('#icon').val();
	if(!name)
		show_warning('.edit-service', 'Укажите название', false);
	else{
		var str = 'func=update_service&id=' + id + '&name=' + name + '&icon=' + icon;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				show_alert('Сохранено...');
				service();
			}
		});
	}
}

function months(id_location = -1){
	select_menu('months_open', '2');
	alert(id_location);
	if (id_location==-1) {
		alert('000');
		if ($('.filter_location:visible').length>0) {
			alert('123');
			id_location = $('.filter_location:visible option:selected').val();
			alert(id_location);
		}
	}
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_months&id_location='+id_location,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function save_month(id){
	var active = $('.new-month .active').is(':checked') ? 1 : 0;
	var id_location = $('.new-month .id_location option:selected').val();
	var id_month = $('.new-month .id_month option:selected').val();
	var title = $('.new-month .month_title').val();
	var desc = $('.new-month .month_description').val();
	var h1 = $('.new-month .month_h1').val();
	if(!title || !desc || !h1)
		show_warning('.new-month', 'Вы не ввели title, description и h1', false);
	else{
		var str = 'func=save_month&id='+id+'&active='+active+'&id_location='+id_location+'&id_month='+id_month+'&title='+title+'&desc='+desc+'&h1='+h1;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				months();
			}
		});
	}
}

function edit_month(id){
	var str = 'func=edit_month&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			show_modal(html);
		}
	});
}

function add_new_month(){
	var str = 'func=edit_month';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function procedure(){
	old_i = 0;
	select_menu('procedure_open', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_procedure',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function add_new_procedure(){
	var str = 'func=new_procedure';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_procedure(){
	var name = $('.new-procedure .name').val();
	if(!name)
		show_warning('.new-procedure', 'Укажите название', false);
	else{
		var str = 'func=save_new_procedure&name=' + name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				procedure();
			}
		});
	}
}

function edit_procedure(id){
	var str = 'func=edit_procedure&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			show_modal(html);
			$('.edit-procedure-modal *[name="image"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*1024,
				maxcount: 1,
				contentType:['image/svg+xml']
			});
		}
	});
}

function update_procedure(id){
	var $button = $('.btn-update-procedure');
	var name = $('.edit-procedure .name').val();
	var description = $('.edit-procedure .description').val();

	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');


	var $image = $modalBody.find('*[name="image"]');
	var $imageMsg = $image.parent().find('.input-message-block');
	var image = JSON.parse($image.val().trim());
	$imageMsg.html("").removeClass('with-bottom-margin');

	if(!name)
		show_warning('.edit-procedure', 'Укажите название', false);
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: {
				func: 'update_procedure',
				name: name,
				id: id,
				description: description,
				image: image
			},
			success: function(){
				procedure();
			}
		});
	}
}







function profile(){
	old_i = 0;
	select_menu('profile_open', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_profile',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function add_new_profile(){
	var str = 'func=new_profile';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_profile(){
	var name = $('.new-profile .name').val();
	if(!name)
		show_warning('.new-profile', 'Укажите название', false);
	else{
		var str = 'func=save_new_profile&name=' + name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				profile();
			}
		});
	}
}

function edit_profile(id){
	var str = 'func=edit_profile&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			show_modal(html);
			$('.edit-profile-modal *[name="image"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*1024,
				maxcount: 1,
				contentType:['image/svg+xml']
			});
		}
	});
}

function update_profile(id){
	var $button = $('.btn-update-profile');
	var name = $('.edit-profile .name').val();
	var description = $('.edit-profile .description').val();

	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');


	var $image = $modalBody.find('*[name="image"]');
	var $imageMsg = $image.parent().find('.input-message-block');
	var image = JSON.parse($image.val().trim());
	$imageMsg.html("").removeClass('with-bottom-margin');

	if(!name)
		show_warning('.edit-profile', 'Укажите название', false);
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: {
				func: 'update_profile',
				name: name,
				id: id,
				description: description,
				image: image
			},
			success: function(){
				profile();
			}
		});
	}
}

function methods(){
	old_i = 0;
	select_menu('methods_open', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_methods',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function add_image_method(id){
	var str = 'func=form_new_image&id=' + id + '&type=method';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			activate_ajax_image('method');
		}
	});
}

function edit_method(id){
	var str = 'func=edit_method&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			remove_all_windows();
			show_modal(html);

			$('.edit-method-modal *[name="image"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*1024,
				maxcount: 1,
				contentType:['image/svg+xml']
			});

		}
	});
}

function update_method(id){
	var $button = $('.btn-update-method');

	var name = $('.name-method').val();
	var desc = $('.desc-method').val();

	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');

	var $image = $modalBody.find('*[name="image"]');
	var $imageMsg = $image.parent().find('.input-message-block');
	var image = JSON.parse($image.val().trim());
	$imageMsg.html("").removeClass('with-bottom-margin');

	if(!name)
		show_warning('.edit-method', 'Введите название метода', false);
	else{

		var data = {
			func: 'update_method',
			id: id,
			name: name,
			desc: desc,
			image: image
		}

		$.ajax({
			type: 'POST',
			data: data,
			url: 'mysql.php',
			success: function(){
				remove_all_windows();
				$('.method-'+id+' .name').html(name);
				$('.method-'+id+' .desc').html(desc);
			}
		});
	}
}

function delete_method(id){
	if(confirm('Удалить метод лечения?')){
		var str = 'func=delete_method&id=' + id;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(){
				$('.method-'+id).remove();
			}
		});
	}
}

function infrastructure(){
	old_i = 0;
	select_menu('infa_open', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_infrastructure',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function add_new_infrastructure(){
	var str = 'func=new_infrastructure';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_infrastructure(){
	var name = $('.new-infrastructure .name').val();
	if(!name)
		show_warning('.new-infrastructure', 'Укажите название', false);
	else{
		var str = 'func=save_new_infrastructure&name=' + name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				infrastructure();
			}
		});
	}
}

function edit_infrastructure(id){
	var str = 'func=edit_infrastructure&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_infrastructure(id){
	var name = $('.edit-infrastructure .name').val();
	if(!name)
		show_warning('.edit-infrastructure', 'Укажите название', false);
	else{
		var str = 'func=update_infrastructure&name=' + name + '&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				infrastructure();
			}
		});
	}
}

function comfort(){
	old_i = 0;
	select_menu('comfort_open', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=see_comfort',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function change_type_comfort(id){
	var str = 'func=change_type_comfort&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(type){
			$('.icon-star-'+id).removeClass('btn-warning').removeClass('btn-info');
			if(type == 0)
				$('.icon-star-'+id).addClass('btn-info');
			else
				$('.icon-star-'+id).addClass('btn-warning');
		}
	});
}

function add_new_comfort(){
	var str = 'func=new_comfort';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_comfort(){
	var name = $('#name').val();
	var icon = $('#icon').val();
	if(!name)
		show_warning('.new-comfort', 'Укажите название', false);
	else{
		var str = 'func=save_new_comfort&name=' + name + '&icon=' + icon;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				show_alert('Сохранено...');
				comfort();
			}
		});
	}
}

function edit_comfort(id){
	var str = 'func=edit_comfort&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_comfort(id){
	var name = $('#name').val();
	var icon = $('#icon').val();
	if(!name)
		show_warning('.edit-comfort', 'Укажите название', false);
	else{
		var str = 'func=update_comfort&id=' + id + '&name=' + name + '&icon=' + icon;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				show_alert('Сохранено...');
				comfort();
			}
		});
	}
}

function new_room(id){
	$('.edit-room').remove();
	$('.new-room').remove()
	var str = 'func=add_new_room&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.tbl-room').append(html);
		}
	});
}

function save_new_room(){
	var id_obj = $('.object-menu .menu-object').attr('object');
	var main_place = $('#main_place').val();
	var add_place = $('#add_place').val();
	var wo_bed_place = $('#wo_bed_place').val();
	var housing = $('#housing_object').val();
	var name = $('#name').val();
	var note = $('#note').val();
	var square = $('#square').val();
	var food = $('#food').val();
	var str = 'func=save_new_room&id_obj=' + id_obj + '&name_room=' + name + '&main_place=' + main_place + '&add_place=' + add_place + '&wo_bed_place=' + wo_bed_place + '&note=' + note + '&food=' + food + '&housing=' + housing + '&square=' + square;
	str+= '&comfort=' + select_checkbox('comfort', 'comfort');
	str+= '&best_comfort=' + select_checkbox('comfort', 'best_comfort');
	if(!name)
		show_warning('.new-room-div', 'Введите назнание номера');
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_object_room();
				show_alert('Номер сохранен...');
			}
		});
	}
}

function edit_room(id,manager){
	if(typeof manager === 'undefined')
		manager = false;
	manager *=1;
	$('.edit-room').remove();
	$('.new-room').remove();
	var str = 'func=edit_room&id=' + id+'&manager='+manager;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			if(manager) {
				$('.div-room-'+id+' .form-group').after(html);
			}
			else $('#str'+id).after(html);
      $('.edit-room-div *[name="image"]').multUploader({
        action:'mysql.php?func=multipart_upload',
        fragmentSize:1024*1024,
        contentType:['image/jpeg','image/png']
      });
		}
	});
}

function update_room(id,manager){
	if(typeof manager === 'undefined')
		manager = false;
	var $modalBody = $('.edit-room-div');
	var name = $('#name').val();
	var note = $('#note').val();
	var main_place = $('#main_place').val();
	var add_place = $('#add_place').val();
	var wo_bed_place = $('#wo_bed_place').val();
	var housing = $('#housing_object').val();
	var square = $('#square').val();
	var food = $('#food').val();
	var $image = $modalBody.find('*[name="image"]');
	var image = JSON.parse($image.val().trim());

	var data = {
		func: 'update_room',
		name_room: name,
		id: id,
		note: note,
		main_place: main_place,
		add_place: add_place,
		wo_bed_place: wo_bed_place,
		housing: housing,
		food: food,
		square: square,
		comfort: '',
    	image: image
  	};
	data.comfort = select_checkbox('comfort', 'comfort');
	data.best_comfort = select_checkbox('comfort', 'best_comfort');
	if(!name)
		show_warning('.edit-room-div', 'Введите назнание номера');
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: data,
			success: function() {
				if(manager) {
          			view_object_rooms($('.nav-object').attr('data-object-id'));
				} else {
          			select_object_room();
        		}
				show_alert('Номер изменен...');
			}
		});
	}
}

function room_check_archive(id){
	var str = 'func=room_check_archive&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(active){
			if($('.name-room-'+id).length){
				if(active == 0){
					$('.div-room-'+id).removeClass('list-group-item-danger');
					$('.div-room-'+id+' .btn-trash').addClass('btn-danger').removeClass('btn-primary');
					$('.div-room-'+id+' .btn-trash i').addClass('fa-trash').removeClass('fa-reply');
				}else{
					$('.div-room-'+id).addClass('list-group-item-danger');
					$('.div-room-'+id+' .btn-trash').removeClass('btn-danger').addClass('btn-primary');
					$('.div-room-'+id+' .btn-trash i').removeClass('fa-trash').addClass('fa-reply');
				}
			}else
				select_object_room();
			show_alert('Готово...');
		}
	});
}

function room_delete(id){
	if(confirm("Удалить номер?")){
		var str = 'func=delete_room&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_object_room();
				show_alert('Готово...');
			}
		});
	}
}

function edit_main_data_object(id){
	var str = 'func=edit_main_data_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function update_main_data_object(id){
	var similar = "";
	$('.similar-object input').each(function(){
		var value = parseInt(this.value);
		if(value > 0){
			if(similar != "")
				similar+= "_";
			similar+= value;
		}
	});

	var q = $('#description-object').val();

	var obj = {
		"func": "update_main_data_object",
		"id": id,
		"similar": similar,
		"active": parseInt($('#active').prop('checked')*1),
		"external_booking": $('#external_booking').val(),
		"type": $('#type_object').val(),
		"name": $('#name_object').val(),
		"full_name": $('#full_name').val(),
		"trust_full_name": $('#trust_full_name').val(),
		"trust_name_template": $('#trust_name_template').val(),
		"trust_number": $('#trust_number').val(),
		"main_post_name": $('#main_post_name').val(),
		"main_post_fio": $("#main_post_fio").val(),
		"default_price_type": $('#default_price_type').val(),
		"city": $('#city_object').val(),
		'city_genitive': $('#city_genitive').val(),
		"latitude": $('#latitude').val(),
		"longitude": $('#longitude').val(),
		"weather": $('#weather').val(),
		"direction": $('#direction-object').val(),
		"region_id": $('#object_region').val(),
		"region_direction_id": $('#region_direction_id').val(),
		"source_booking": parseInt($('#source_booking').prop('checked')*1),
		"fast_booking": parseInt($('#fast_booking').prop('checked')*1),
		"booking_uri": $("#booking_uri").val().trim(),
		"uri_schema": $("#uri_schema").val(),
		"url_name": $('#url_name').val(),
		'state_program': $('#state-program').prop('checked') ? 1 : 0,
		'children_rest': $('#children-rest').prop('checked') ? 1 : 0,
		'featured': $('#featured').prop('checked') ? 1 : 0,
		'selected': $('#selected').prop('checked') ? 1 : 0,

		'w_therapy': $('#w_therapy').prop('checked') ? 1 : 0,
		'wo_therapy': $('#wo_therapy').prop('checked') ? 1 : 0,
		'mother_and_child': $('#mother_and_child').prop('checked') ? 1 : 0,
		'for_invalid': $('#for_invalid').prop('checked') ? 1 : 0,
		'all_inc': $('#all_inc').prop('checked') ? 1 : 0,
		'open_buffet': $('#open_buffet').prop('checked') ? 1 : 0,
		'near_sea': $('#near_sea').prop('checked') ? 1 : 0,
		'near_black_sea': $('#near_black_sea').prop('checked') ? 1 : 0,
		'in_forest': $('#in_forest').prop('checked') ? 1 : 0,
		'in_hill': $('#in_hill').prop('checked') ? 1 : 0,
		'near_water': $('#near_water').prop('checked') ? 1 : 0,
		'near_river': $('#near_river').prop('checked') ? 1 : 0,
		'near_volga_river': $('#near_volga_river').prop('checked') ? 1 : 0,
		'near_lake': $('#near_lake').prop('checked') ? 1 : 0,
		'beach': $('#beach').prop('checked') ? 1 : 0,
		'on_coast': $('#on_coast').prop('checked') ? 1 : 0,
		'w_pool': $('#w_pool').prop('checked') ? 1 : 0,
		'cashback': $('#cashback').prop('checked') ? 1 : 0,
		'w_spa': $('#w_spa').prop('checked') ? 1 : 0,
		'w_buvet': $('#w_buvet').prop('checked') ? 1 : 0,
		'w_radon': $('#w_radon').prop('checked') ? 1 : 0,
		'only_summer': $('#only_summer').prop('checked') ? 1 : 0,
		'checked_sonata': $('#checked_sonata').prop('checked') ? 1 : 0,
		"stars": $('#stars').val(),
		"description": q
	};
	// var str = 'func=update_main_data_object&id=' + id;
	// str += '&similar=' + similar;
	// str += '&type=' + $('#type_object').val();
	// str += '&name=' + $('#name_object').val();
	// str += '&full_name=' + $('#full_name').val();
	// str += '&city=' + $('#city_object').val();
	// str += '&latitude=' + $('#latitude').val();
	// str += '&longitude=' + $('#longitude').val();
	// str += '&weather=' + $('#weather').val();
	// str += '&direction=' + $('#direction-object').val();
	// str += '&description=' + q;
	if(!$('#name_object').val())
		show_warning('.edit-object', 'Укажите название объекта');
	else{
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: $.param(obj),
			success: function(){
				select_object_about(id);
				show_alert('Объект изменен...');
			}
		});
	}
}

function edit_desc_object(id){
	var str = 'func=edit_desc_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function update_desc_object(id){
	var str = 'func=update_desc_object&id=' + id;
	str += '&profile=' + select_checkbox('profile');
	str += '&method=' + select_checkbox('methods');
	str += '&infa=' + select_checkbox('infa');
	str += '&procedure=' + select_checkbox('procedure');
	str += '&medical_factors=' + $('.medical-factors').val();
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_object_about(id);
			show_alert('Объект изменен...');
		}
	});
}

function edit_services_object(id){
	var str = 'func=edit_services_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}

function update_services_object(id){
	var services = new Array();
	$('.edit-services input').each(function(){
		var value = this.value;
		if(value != ''){
			var id = $(this).attr('name');
			value = value.replace(new RegExp("'",'g'), "");
			value = value.replace(new RegExp("\"",'g'), "");
			services[id] = value;
		}
	});
	var str = 'func=update_services_object&id=' + id;
	str += '&services=' + JSON.stringify(services);
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_object_about(id);
			show_alert('Объект изменен...');
		}
	});
}

function update_rating_on_site(){
	show_loader_element('.object-infa');
	var str = 'func=upload_rating_object';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(a){
			if(a != "")
				$('.object-infa').html(a);
			else{
				$('.menu-object .label-success').trigger('click');
				show_alert('Загрузка завершена...');
			}
		}
	});
}

function upload_methods(){
	show_loader_element('.object-infa');
	var str = 'func=upload_method_on_server';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(a){
			if(a != "")
				alert(a);
			else{
				$('.menu-object .label-success').trigger('click');
				show_alert('Загрузка завершена...');
			}
		}
	});
}

function update_reservation_on_server(){
	show_loader('rooms');
	var str = 'func=upload_reserv_object_on_server';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(a){
			if(a != "")
				alert(a);
			else{
				$('.menu-object .label-success').trigger('click');
				show_alert('Загрузка завершена...');
			}
		}
	});
}

function delete_photo_from_server(id){
	show_loader('rooms');
	var str = 'func=delete_photo_from_server&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(a){
			if(a != "")
				alert(a);
			else{
				$('.menu-object .label-success').trigger('click');
				show_alert('Загрузка завершена...');
			}
		}
	});
}

function view_images_room(id){
	var str = 'func=view_images_room&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function view_images_object(id){
	var str = 'func=view_images_object&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_popup(html, 'big');
		}
	});
}

function add_new_image_room(id){
	var str = 'func=form_new_image&id=' + id + '&type=room';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			activate_ajax_image();
		}
	});
}

function add_new_image_object(id){
	var str = 'func=form_new_image&id=' + id + '&type=object';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			activate_ajax_image('object');
		}
	});
}


function upload_new_image(id, type, button){
	$(button).attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-pulse"></i> Сохранение...');
	var cut = '', left;
	if($('.new-image .view-photo #crop').length){
		cut = 1;
		var position = $('.new-image .view-photo #crop').position();
		left = position.left;
	}
	var url = $('.new-image .view-photo img').attr('src');
	var str = 'func=upload_new_image&id=' + id + '&type=' + type + '&cut=' + cut + '&left=' + left + '&url=' + url;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(url){
			remove_all_windows();
			if(type == 'room')
				add_new_image_room(id);
			else if(type == 'method')
				$('.method-'+id+' .img-head-small').attr('src', 'temp/method/'+id+'.jpg');
			else if(type == 'region' || type == 'direction'){
			}else if(type == 'sight'){
				var html = "<img src='"+url+"' class='img-thumbnail' style='height: 100px' />";
				$('.sight-'+id+' .sight-image').append(html);
			}else
				add_new_image_object(id);
		}
	});
}

function remove_image_room(folder, image){
	var str = 'func=remove_image_room&folder=' + folder + '&image=' + image;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
		}
	});
}

function remove_image_object(object, image){
	var str = 'func=remove_image_object&object=' + object + '&image=' + image;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			$('img[src*="' + image + '"]').closest('.faded').remove();
		}
	});
}

function edit_housing(id){
	var str = 'func=edit_housing&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_housing(id){
	var name = $('.name-housing').val();
	var desc = $('.desc-housing').val();
	if(!name)
		show_warning('.edit-housing', 'Укажите название корпуса', false);
	else{
		var str = 'func=update_housing&id=' + id + '&name=' + name + '&desc=' + desc;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_object_housing();
				show_alert('Готово...');
			}
		});
	}
}

function add_new_similar_object(){
	var html = "<input type='text' class='form-control similar-object-input' />&nbsp;";
	$('.similar-object').append(html);
}

function deleteTLdata(id){
	if (confirm('Вы уверены?')) {
		var str = 'func=deletetldata&id='+id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				alert('Интеграция отключена');
			}
		});
	}
}

function object_check_archive(id, status){
	var str = 'func=object_check_archive&id=' + id + '&status=' + status;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			select_menu_object();
		}
	});
}

function copy_room(id){
	if(confirm('Копировать номер?')){
		var str = 'func=copy_room&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				select_object_room();
			}
		});
	}
}

function select_object_sights(id){
	$('.menu-object li').removeClass('active');
	$('.menu-sights').addClass('active');
	var str = 'func=select_object_sights&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
	show_loader_element('.object-infa');
}

function add_image_region(){
	var id = $('#region').val();
	var str = 'func=form_new_image&id=' + id + '&type=region';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			activate_ajax_image('region');
		}
	});
}

function select_object_image(object){
	$('.menu-object li').removeClass('active');
	$('.menu-image').addClass('active');
	var str = 'func=select_object_image&id=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
		}
	});
}


function check_completeness_object(){
	$('.menu-object li').removeClass('active');
	$('.menu-check-object').addClass('active');
	show_loader_element('.object-infa');
	var str = 'func=check_completeness_object';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.object-infa').html(html);
			$('[data-toggle="tooltip"]').tooltip();
		}
	});
}

function edit_region(){
	var region = $('.regions #region').val();
	var str = 'func=edit_region&id=' + region;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_region(id){
	var name = $('.edit-region .name-region').val();
	var direction = $('.edit-region #direction-region').val();
	var description = $('.edit-region .description-region').val();
	var meta_desc = $('.edit-region .meta-desc-region').val();
	var man_reward_scheme = $('.edit-region .man_reward_scheme').val();
	var state_program = $('.edit-region .state-program').prop('checked') ? 1: 0;
	var state_program_start_timestamp = $('.edit-region .state-program-start-timestamp').val();
	var state_program_end_timestamp = $('.edit-region .state-program-end-timestamp').val();


	if(!name)
		show_warning('.edit-region', 'Укажите название региона', false);
	else{
		var str = 'func=update_region&id=' + id + '&name=' + name + '&direction=' + direction + '&description=' + description + '&meta_desc=' + meta_desc+'&man_reward_scheme='+man_reward_scheme+'&state_program='+state_program+'&state_program_start_timestamp='+state_program_start_timestamp+'&state_program_end_timestamp='+state_program_end_timestamp;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				$('.regions #region option:selected').html(name);
			}
		});
	}
}

function edit_direction(type){
	var id = $('#direction-'+type).val();
	var str = 'func=edit_direction&id=' + id + '&type=' + type;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_direction(id, type){
	var name = $('.edit-direction .name-direction').val();
	var name_rod = $('.edit-direction .name-direction-rod').val();
	var sort = $('.edit-direction .sort-direction').val();
	var desc = $('.edit-direction .description-direction').val();
	var meta_desc = $('.edit-direction .meta-desc-direction').val();
	if(!name)
		show_warning('.edit-direction', 'Укажите название направления', false);
	else{
		var str = 'func=update_direction&id=' + id + '&name=' + name + '&description=' + desc + '&meta_desc=' + meta_desc+'&name_rod='+name_rod+'&sort='+sort;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				$('#direction-'+type+' option:selected').html(name);
			}
		});
	}
}

function add_image_direction(type){
	var id = $('#direction-'+type).val();
	if(id){
		var str = 'func=form_new_image&id=' + id + '&type=direction';
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(html){
				show_modal(html);
				activate_ajax_image('direction');
			}
		});
	}
}

function select_object_admin(noCheckLocation){
	if(typeof noCheckLocation === 'undefined')
		noCheckLocation = false;
	var object = $('#object_name .id-object').attr('name');
	if(object) {
		select_menu_object(object, noCheckLocation);
	}
}

function create_uniq_link_object(id){
	var str = 'func=create_uniq_link_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(answer){
			if(!answer)
				alert('Объект с такой ссылкой уже существует');
			else
				select_object_about();
		}
	});
}

function select_object_rate_plan(){
	$('.menu-object li').removeClass('active');
	$('.menu-rate-plan').addClass('active');
	var object = $('.object-menu .menu-object').attr('object');
	var str = 'func=select_object_rate_plan&object=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="panel panel-default form-horizontal"><div class="panel-body">';
			for(var index in data){
				var rate_plan = data[index];
				if(rate_plan['status'] == 1)
					html+= '<div class="form-group"><div class="rate-plan-item clearfix"><div class="rate-plan-item-container clearfix"><div class="col-sm-3">' +rate_plan['name']+ '</div><div class="col-sm-4">' +rate_plan['description']+ '</div><div class="col-sm-3">' + (rate_plan['food'] ? rate_plan['food'] : '') + '</div><div class="col-sm-2"><button type="button" class="btn btn-default btn-xs" onclick="edit_rate_plan(' +rate_plan['id']+ ')"><i class="fa fa-pencil"></i> изменить</button></div></div></div></div>';
				else
					html+= '<div class="form-group"><div class="rate-plan-item in-archieve-element clearfix"><div class="rate-plan-item-container clearfix"><div class="col-sm-3">' +rate_plan['name']+ '</div><div class="col-sm-4">' +rate_plan['description']+ '</div><div class="col-sm-3">' + (rate_plan['food'] ? rate_plan['food'] : '') + '</div><div class="col-sm-2"><button type="button" class="btn btn-default btn-xs" onclick="edit_rate_plan(' +rate_plan['id']+ ')"><i class="fa fa-pencil"></i> изменить</button></div></div></div></div>';
			}
			html+= '</div><div class="panel-footer text-right"><button type="button" class="btn btn-primary btn-sm" onclick="add_new_rate_place(' +object+ ')"><i class="fa fa-plus-circle"></i> Новый тариф</button></div></div>';
			$('.object-infa').html(html);
		}
	});
}

function add_new_rate_place(object){
	var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Добавить новый тариф</h4></div><div class="modal-body form-horizontal new-rate-plan"><div class="form-group form-group-margin"><label class="col-sm-4 control-label">Тариф</label><div class="col-sm-8"><input type="text" class="form-control name-rate-plan" /></div></div></div><div class="modal-footer"><button type="button" class="btn btn-success btn-sm" onclick="save_new_rate_plan(' +object+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
	show_modal(html);
}

function save_new_rate_plan(object){
	var name = $('.name-rate-plan').val();
	if(!name)
		show_warning('.new-rate-plan', 'Введите название тарифа', false);
	else{
		var str = 'func=save_new_rate_plan&object=' + object + '&name=' + name;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_object_rate_plan();
			}
		});
	}
}

function edit_rate_plan(id){
	var str = 'func=edit_rate_plan&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			if(!data['min_days'])
				data['min_days'] = '';

			if(!data['max_days'])
				data['max_days'] = '';

			if(!data['start_date'])
				data['start_date'] = '';

			if(!data['end_date'])
				data['end_date'] = '';

			var html = '<div class="modal fade">' +
											'<div class="modal-dialog">' +
												'<div class="modal-content">' +
													'<div class="modal-header">' +
														'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>' +
														'<h4 class="modal-title">Изменить тариф</h4>' +
													'</div>' +
													'<div class="modal-body form-horizontal edit-rate-plan">' +
														'<div class="form-group">' +
															'<label class="col-sm-4 control-label">Тариф</label>' +
															'<div class="col-sm-8">' +
																'<input type="text" class="form-control name-rate-plan" value="' +data['name']+ '" />' +
															'</div>' +
														'</div>' +
														'<div class="form-group">' +
															'<label class="col-sm-4 control-label">Питание</label>' +
															'<div class="col-sm-8">' +
																'<input type="text" class="form-control food-rate-plan" value="' +(data['food'] ? data['food'] : '')+ '" />' +
															'</div>' +
														'</div>' +
														'<div class="form-group">' +
															'<label class="col-sm-4 control-label">Дата начала</label>' +
															'<div class="col-sm-8">' +
																'<input type="date" class="form-control start-date-rate-plan" value="' +(data['start_date'] ? data['start_date'] : '')+ '" />' +
															'</div>' +
														'</div>' +		
														'<div class="form-group">' +
															'<label class="col-sm-4 control-label">Дата окончания</label>' +
															'<div class="col-sm-8">' +
																'<input type="date" class="form-control end-date-rate-plan" value="' +(data['end_date'] ? data['end_date'] : '')+ '" />' +
															'</div>' +
														'</div>' +					
														'<div class="form-group">' +
																'<label class="col-sm-4 control-label">Мин. кол-во дней</label>' +
																'<div class="col-sm-8">' +
																	'<input type="number" min="0" class="form-control min-days-rate-plan" value="' + (data['min_days'] ? data['min_days'] : '') + '" />' +
																'</div>' +
														'</div>' +
														'<div class="form-group">' +
																'<label class="col-sm-4 control-label">Макс. кол-во дней</label>' +
																'<div class="col-sm-8">' +
																	'<input type="number" min="0" class="form-control max-days-rate-plan" value="' + (data['max_days'] ? data['max_days'] : '') + '" />' +
																'</div>' +
														'</div>' +
														'<div class="form-group">' +
																'<label class="col-sm-4 control-label">Описание</label>' +
																'<div class="col-sm-8">' +
																	'<textarea class="form-control desc-rate-plan">' +data['description']+ '</textarea>' +
																'</div>' +
														'</div>' +
														'<div class="form-group">' +
																'<label class="col-sm-4 control-label">Активен</label>' +
																'<div class="col-sm-8">' +
																	'<input class="form-control status-rate-plan" type="checkbox"'+((data['status'] == 1)?' checked':'')+' name="status">' +
																'</div>' +
														'</div>' +
													'</div>' +
													'<div class="modal-footer">' +
															'<button type="button" class="btn btn-success btn-sm" onclick="update_rate_plan(' +id+ ')"><i class="fa fa-check-circle"></i> Сохранить</button>' +
													'</div>' +
												'</div>' +
											'</div>' +
									'</div>';
			show_modal(html);
		}
	});
}

function update_rate_plan(id){
	var name = $('.name-rate-plan').val();
	var desc = $('.desc-rate-plan').val();
	var food = $('.food-rate-plan').val();
	var min_days = $('.min-days-rate-plan').val();
	var max_days = $('.max-days-rate-plan').val();
	var start_date = $('.start-date-rate-plan').val();
	var end_date = $('.end-date-rate-plan').val();
	var status = 1*$('.status-rate-plan').prop('checked');
	if(!name)
		show_warning('.edit-rate-plan', 'Укажите название тарифа', false);
	else{
		var str = 'func=update_rate_plan&id=' + id + '&name=' + name + '&desc=' + desc + '&food=' + food + '&min_days=' + min_days + '&max_days=' + max_days +'&status='+status+'&start_date='+start_date+'&end_date='+end_date;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				select_object_rate_plan();
			}
		});
	}
}

function sync_server_database(){
	show_loader_element('.object-infa');
	var str = 'func=sync_server_database';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(answer){
			if(answer)
				$('.object-infa').html(answer);
			else
				$('.menu-object .label-success').trigger('click');
		}
	});
}
