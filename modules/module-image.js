function activate_ajax_image(type){

	var button = $('#uploadButton'), interval;

	$.ajax_upload(button, {
		action : 'core/upload.php?func=upload_image&type=' + type,
		name : 'file',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete : function(file, response){
			if(response == 0){
				show_warning('.new-image', 'Неверный тип файла. Только JPG картинки', false);
				this.enable();
			}else if(response == 1){
				show_warning('.new-image', 'Ошибка при загрузке файла', false);
				this.enable();
			}else if(response == 2 && (type == 'method' || type == 'region')){
				show_warning('.new-image', 'Ширина картинки меньше 400px', false);
				this.enable();
			}else if(response == 2){
				show_warning('.new-image', 'Ширина картинки меньше 840px', false);
				this.enable();
			}else if(response == 3){
				show_warning('.new-image', 'Неправильный формат картинки', false);
				this.enable();
			}else{
				$('.new-image .view-photo img').attr('src', response);
				$('.new-image .view-photo img').load(function(){
					$('.new-image .view-photo').show();
					var width = parseInt($('.new-image .view-photo img').width());
					var height = parseInt($('.new-image .view-photo img').height());
					$('.new-image .view-photo img').addClass('upload-image');
					if((height / width) < 0.75 || (type == 'object')){
						width = width * (300 / height);
						$('.new-image .view-photo .view-photo-div').css("width", width);
						$('.new-image .view-photo img').before("<div id='crop' class='crop'></div>");
						$('#crop').draggable({
							containment: 'parent',
							cursor: 'crosshair'
						});
					}
				});
			}
		}
	});
}

function activate_ajax_document(id, type){

	var button = $('.download-file-button'), interval;
	
	$.ajax_upload(button, {
		action : 'core/upload.php?func=upload_schet',
		name: 'file',
		onSubmit: function(file, ext){
			this.disable();
			$('.download-file-button').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Загрузка файла...');
		},
		onComplete: function(file, response){
			$('.download-file-button').removeAttr('disabled').html('<i class="fa fa-upload"></i> Загрузить');
			if(response == 0)
				$('.atach-file-info').html("<div class='alert alert-danger'>Ошибка при загрузке файла</div>");
			else
				upload_document(id, type, response);
		}
	});

}











function save_image_to_server(){
	select_menu('image_menu', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=check_folder_image',
		success: function(html){
			$('#body').html(html);
			show_upload_objects();
		}
	});
	show_loader_element('#body');
}

function show_upload_objects(){
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_upload_objects',
		success: function(html){
			$('#body').html(html);
		}
	});	
}

function upload_image_server(region){
	var object = select_checkbox_element('.region-'+region);
	var str = 'func=upload_image_server&region=' + region + '&object=' + object;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
	show_loader_element('#body');
}

function add_photo_profile(id, type){
	var str = 'func=show_form_photo_profile';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			activate_ajax_image_profile(type);
			$('.form-photo').attr('number', id);
		}
	});
}

function activate_ajax_image_profile(type){
	var button = $('.download-photo-button'), interval;
	$.ajax_upload(button, {
		action : 'core/upload.php?func=upload_profile_image',
		name : 'file',
		onSubmit: function(file, ext){
			$('.download-photo-button').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Загрузка изображения...');
			$('.new-photo .alert').remove();
		},
		onComplete: function(file, response){
			if(response == 0){
				show_warning('.new-photo', 'Неверный тип файла. Только JPG картинки', false);
				$('.download-photo-button').removeAttr('disabled').html('<i class="fa fa-upload"></i> Загрузить');
			}else if(response == 1){
				show_warning('.new-photo', 'Ошибка при загрузке файла', false);
				$('.download-photo-button').removeAttr('disabled').html('<i class="fa fa-upload"></i> Загрузить');
			}else if(response == 2){
				show_warning('.new-photo', 'Маленький размер фото', false);
				$('.download-photo-button').removeAttr('disabled').html('<i class="fa fa-upload"></i> Загрузить');
			}else{
				$('.form-photo .modal-title').html('Обрежьте фото');
				$('.form-photo .new-photo').html('<div class="photo-resize"><div id="crop" class="crop"></div><img src="'+response+'" onload="write_crop()" /></div>');
				$('.form-photo .modal-footer').html("<button type='button' onclick='resize_photo(\""+type+"\")' class='btn btn-info download-photo'><i class='fa fa-scissors'></i> Обрезать фото</button>");
			}
		}
	});
}

function write_crop(){
	var width = $('.new-photo img').width();
	var height = $('.new-photo img').height();
	if(width <= height)
		$("#crop").css('width', width).css('height', width);
	else
		$("#crop").css('width', height).css('height', height);
	$("#crop").draggable({
		containment: 'parent',
		cursor: 'crosshair'
	});
}

function resize_photo(type){
	var position = $('#crop').position();
	var str = 'func=resize_photo&left=' + position['left'] + '&top=' + position['top'];
	$('.modal-footer button').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Редактирование изображения...');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(photo){
			$('.form-photo .modal-title').html('Сохраните фото');
			$('.form-photo .new-photo').html('<div class="center"><img style="width: 100px" src="'+photo+'?v=1" class="img-thumbnail" /></div>');
			$('.form-photo .modal-footer').html("<button type='button' onclick='save_photo(\""+type+"\")' class='btn btn-success download_photo'><i class='fa fa-floppy-o'></i> Сохранить</button>");
		}
	});
}

function save_photo(type){
	var id = $('.form-photo').attr('number');
	var str = 'func=save_photo_profile&id=' + id + '&type=' + type;
	$('.modal-footer button').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Сохранение изображения...');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(image){
			remove_all_windows();
			if(type == 'user')
				see_users();
			else if(type == 'object')
				select_object_about(id);
			else if(type == 'chat' && image)
				$('.chat-avatar').attr('src', image);
		}
	});
}
