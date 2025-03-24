function show_admin(){
	select_menu('admin_menu', '1');
	var str = 'func=show_admin_search';
	$.ajax({
		type: 'POST',
		url: 'mysql.php',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function select_object_non_region(){
	var str = 'func=select_object_non_region';
	$.ajax({
		type: 'POST',
		url: 'mysql.php',
		data: str,
		success: function(html){
			$('#result').html(html);
		}
	});
	show_loader_element('#result');
}

/*function fetchImage(url){
        const data = fetch(url);
        console.log(data);
        const buffer = data.arrayBuffer();
        const blob = new Blob([buffer], { type: "image/png"});
        return blob;
}*/

function setFile(input, name, url) {
  try {
    fetch(url, {
        method: 'GET'
    })
    .then(response => response.blob())
    .then(blob => {
	    var dt  = new DataTransfer();
	    dt.items.add(new File([blob], name, {type: blob.type}));
	    input[0].files = dt.files;
	    input.change();
	    return true;
    });  	

  }
  catch(err) {
  	alert('Ошибка при вставке файла!');
    console.log('Ошибка при вставке файла:');
    console.dir(err);
    return false;
  }
}

jQuery(function() {
	$('body').on('click', '.get_img_from_url', function(){
		$(this).prev().attr('disabled', 'disabled');
		var str = 'func=get_image_from_url&url='+$(this).prev().val();
		var that = $(this);
		$.ajax({
			type: 'POST',
			url: 'mysql.php',
			data: str,
			success: function(html){
				if (html!='') {
					var input_element = that.parent().find('.multiple-uploader-input');
					var file_name = html.split('/').pop();
					var file_link = html;
					console.log('file_name='+file_name);
					setFile(input_element, file_name, file_link);
					that.prev().val('');
					that.prev().removeAttr('disabled');
					//delete_temp_uploaded_file(file_name);
				}
			}
		});
	});
});

function delete_temp_uploaded_file(file_name){
	var str = 'func=delete_temp_uploaded_file&file_name='+file_name;
	$.ajax({
		type: 'POST',
		url: 'mysql.php',
		data: str,
		success: function(html){
			
		}
	});
}

function select_similar_client_admin(){
	var str = 'func=select_similar_client_admin';
	$.ajax({
		type: 'POST',
		url: 'mysql.php',
		data: str,
		success: function(html){
			$('#result').html(html);
		}
	});
	show_loader('result');
}

function unite_client_admin(id){
	var id_radio = $('.similar-client-'+id+' input:checked').val();
	if(!id_radio)
		show_warning('.similar-client-'+id, 'Не выбран турист');
	else{
		var str = 'func=unite_client&id=' + id + '&id_radio=' + id_radio;
		$.ajax({
			type: 'POST',
			url: 'mysql.php',
			data: str,
			success: function(){
				$('.similar-client-'+id).remove();
			}
		});
	}
}

function unlike_client_admin(id){
	var id_radio = $('.similar-client-'+id+' input:checked').val();
	if(!id_radio)
		show_warning('.similar-client-'+id, 'Не выбран турист');
	else{
		var str = 'func=unlike_client&id=' + id + '&id_radio=' + id_radio;
		$.ajax({
			type: 'POST',
			url: 'mysql.php',
			data: str,
			success: function(){
				$('.similar-client-'+id).remove();
			}
		});
	}
}

function delete_object(id){
	if(confirm('Удалить данный объект?')){
		var str = 'func=delete_object_admin&id=' + id;
		$.ajax({
			type: 'POST',
			url: 'mysql.php',
			data: str,
			success: function(){
				$('.object-'+id).addClass('list-group-item-danger');
			}
		});
	}
}

function edit_object_admin(id){
	var str = 'func=edit_object_admin&id=' + id;
	$.ajax({
		type: 'POST',
		url: 'mysql.php',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_object_admin(id){
	var name = $('.name-object').val();
	var region = $('#id-region').val();
	var city = $('.city-object').val();
	var str = 'func=update_object_admin&id=' + id + '&name=' + name + '&region=' + region + '&city=' + city;
	$.ajax({
		type: 'POST',
		url: 'mysql.php',
		data: str,
		success: function(){
			remove_all_windows();
			select_object_non_region();
			show_alert('Объект изменен...');
		}
	});
}

function select_region_admin(){
	var country = $('#id-country').val();
	var str = 'func=select_region_country&country=' + country;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#regions').html(html);
		}
	});
}

function check_object(id){
	var str = 'func=check_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_to_new_object(id){
	var new_id = $('[name=object-radio]:checked').val();
	if(!new_id)
		show_warning('.similar-object', 'Выберите объект', false);
	else{
		var str = 'func=unite_objects&id=' + id + '&new_id=' + new_id;
		$.ajax({
			type: 'POST',
			url: 'mysql.php',
			data: str,
			success: function(){
				remove_all_windows();
				select_object_non_region();
				show_alert('Готово...');
			}
		});
	}
}

function show_rating_menu(){
	select_menu('rating_menu', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_rating_menu',
		success: function(html){
			$('#body').html(html);
			show_rating();
		}
	});
}

function show_rating(){
	$('.menu-rating li').removeClass('active');
	$('.menu-rating .show-rating').addClass('active');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=select_rating_admin',
		success: function(html){
			$('.rating-content').html(html);
		}
	});
}

function confirm_rating(id){
	var str = 'func=confirm_rating&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			show_alert('Отзыв подтвержден...');
			show_rating();
			show_send_confirm_rating(id);
			check_menu_count_rating(data);
		}
	});
}

function edit_rating(id){
	var str = 'func=edit_rating&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			$('.edit-rating input:checked').trigger('click');
		}
	});
}

function update_rating(id){
	var pos = $('#positive').val();
	var neg = $('#negative').val();
	var advice = $('#advice').val();
	var company = $('.company-rating').val();
	var clean = $('.block-clean input:checked').val();
	var comfort = $('.block-comfort input:checked').val();
	var ratio = $('.block-ratio input:checked').val();
	var location = $('.block-location input:checked').val();
	var treatment = $('.block-treatment input:checked').val();
	var leisure = $('.block-leisure input:checked').val();
	var staff = $('.block-staff input:checked').val();
	var str = 'func=update_rating&id=' + id + '&positive=' + pos + '&negative=' + neg + '&advice=' + advice + '&clean=' + clean + '&treatment=' + treatment + '&staff=' + staff + '&location=' + location + '&leisure=' + leisure + '&ratio=' + ratio + '&comfort=' + comfort + '&company=' + company;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			remove_all_windows();
			show_alert('Сохранено...');
			if($('.li-rating').length)
				search_rating();
			else
				show_rating();
		}
	});
}

function delete_rating(id){
	if(confirm('Отправить отзыв в архив?')){
		var str = 'func=delete_rating&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				show_alert('Отзыв отправлен в архив...');
				show_rating();
			}
		});
	}
}

function add_new_rating(){
	var str = 'func=add_new_rating';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
			show_datepicker();
		}
	});
}

function save_new_rating(){
	var clean = $('.block-clean input:checked').val();
	var comfort = $('.block-comfort input:checked').val();
	var staff = $('.block-staff input:checked').val();
	var leisure = $('.block-leisure input:checked').val();
	var location = $('.block-location input:checked').val();
	var treatment = $('.block-treatment input:checked').val();
	var ratio = $('.block-ratio input:checked').val();
	var positive = $('#positive').val();
	var negative = $('#negative').val();
	var advice = $('#advice').val();
	var company_rating = $('#company_rating').val();
	var object = $('.id-object').attr('name');
	var date = $('#date').attr('date');
	var turist = $('.turist').val();
	var site = $('.site-from').val();
	if(clean && comfort && staff && leisure && location && treatment && ratio && object && date && turist){
		var str = 'func=save_new_rating&clean=' + clean + '&comfort=' + comfort + '&staff=' + staff + '&leisure=' + leisure + '&location=' + location + '&treatment=' + treatment + '&ratio=' + ratio + '&positive=' + positive + '&negative=' + negative + '&advice=' + advice + '&company_rating=' + company_rating + '&date=' + date + '&turist=' + turist + '&object=' + object + '&site=' + site;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				remove_all_windows();
				show_alert('Отзыв сохранен...');
			}
		});
	}else
		show_warning('.new-rating', 'Заполните все поля', false);
}

function show_rating_comment(){
	$('.menu-rating li').removeClass('active');
	$('.menu-rating .show-comment-rating').addClass('active');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: 'func=show_rating_comment',
		success: function(html){
			$('.rating-content').html(html);
		}
	});
}

function edit_rating_comment(id){
	var str = 'func=edit_rating_comment&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			show_modal(html);
		}
	});
}

function update_rating_comment(id){
	var name = $('.edit-rating-comment .name').val();
	var email = $('.edit-rating-comment .email').val();
	var text = $('.edit-rating-comment .text').val();
	if(!text)
		show_warning('.add-new-news', 'Введите текст комментария');
	var str = 'func=update_rating_comment&id=' + id + '&text=' + text + '&name=' + name + '&email=' + email;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			remove_all_windows();
			show_alert('Сохранено...');
			show_rating_comment();
		}
	});
}

function confirm_rating_comment(id){
	var str = 'func=confirm_rating_comment&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			show_rating_comment();
			show_send_confirm_rating_comment(id);
			check_menu_count_rating(data);
		}
	});
}

function delete_rating_comment(id){
	if(confirm('Отправить комментарий в архив?')){
		var str = 'func=delete_rating_comment&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				show_rating_comment();
			}
		});
	}
}

function show_news(){
	select_menu('news-menu');
	var html = '<ul class="nav nav-tabs menu-news"><li onclick="add_new_news()" class="new-news"><a><i class="fa fa-plus-circle"></i> Создать новость</a></li><li onclick="show_websites_news()" class="show-news"><a><i class="fa fa-user-secret"></i> Сайты</a></li></ul><div class="news-content" style="padding-top: 10px"></div>';
	$('#body').html(html);
	add_new_news();
}

function add_new_news(){
	$('.menu-news li').removeClass('active');
	$('.menu-news .new-news').addClass('active');
	var html = '<div class="form-horizontal panel panel-default add-new-news"><div class="panel-heading"><i class="fa fa-plus-circle"></i> Создать новость</div><div class="panel-body"><div class="form-group"><label class="col-sm-2 control-label">Сайт</label><div class="col-sm-4" id="url_website"><input type="text" class="form-control" onkeyup="find_klient(event, \'website\', \'st_website\', \'sel_website\')" id="website" /></div><label class="col-sm-2 control-label">Дата</label><div class="col-sm-4"><input type="text" class="form-control date-news datepicker" id="date-news" /><div class="well well-sm convers-date" label="date-news"></div></div></div><div class="form-group"><label class="col-sm-2 control-label">Заголовок</label><div class="col-sm-4"><input type="text" class="form-control title-news" /></div><label class="col-sm-2 control-label">Ссылка</label><div class="col-sm-3"><input type="text" class="form-control url-news" /></div><div class="col-sm-1"><button class="btn btn-success btn-block btn-lt" onclick="create_link_news()"><i class="fa fa-link"></i></button></div></div><div class="form-group"><label class="col-sm-2 control-label">Картинка</label><div class="col-sm-4"><input type="text" class="form-control text-image" /></div></div><div class="form-group"><label class="col-sm-2 control-label">Meta-описание<div class="label-meta text-danger"></div></label><div class="col-sm-10"><textarea class="form-control desc-news" style="height: 70px" onKeyPress="check_size_limit(\'.desc-news\', 200, \'.label-meta\')"></textarea></div></div><div class="form-group form-group-margin"><label class="col-sm-2 control-label">Текст</label><div class="col-sm-10"><textarea class="form-control text-news" style="height: 200px"></textarea></div></div></div><div class="panel-footer text-right"><button type="button" class="btn btn-success btn-sm" onclick="save_news()"><i class="fa fa-check-circle"></i> Сохранить</button></div></div>';
	$('.news-content').html(html);
	show_datepicker();
}

function create_link_news(){
	var title = $('.add-new-news .title-news').val();
	var link = title.replace(/\"/g, '');
	link = link.replace(/^\s+/, '').replace(/\s+$/, '');
	link = link.replace(/\'/g, '').replace(/ /g, '-').replace(/«/g, '').replace(/»/g, '').replace(/:/g, '').replace(/!/g, '').replace(/--/g, '-').replace(/--/g, '-').replace(/\?/g, '').replace(/\./g, '');
	$('.add-new-news .url-news').val(link);
}

function save_news(){
	var website = '';
	var date = $('.add-new-news .date-news').attr('date');
	var title = $('.add-new-news .title-news').val();
	var url = $('.add-new-news .url-news').val();
	var text = $('.add-new-news .text-news').val();
	var desc = $('.add-new-news .desc-news').val();
	var image = $('.add-new-news .text-image').val();
	if($('#url').length)
		website = $('#url').html();
	clear_mistake('.add-new-news');
	if(!title)
		show_mistake('.add-new-news .title-news');
	else if(!text)
		show_mistake('.add-new-news .text-news');
	else if(!url)
		show_mistake('.add-new-news .url-news');
	else if(!date)
		show_mistake('.add-new-news .date-news');
	else if(!website)
		show_mistake('.add-new-news #website');
	else{
		var str = 'func=save_new_news&date=' + date + '&title=' + title + '&url=' + url + '&website=' + website + '&text=' + text + '&image=' + image + '&desc=' + desc;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			dataType: 'JSON',
			success: function(answer){
				if(answer == 'no')
					alert('Такого сайта не существует');
				else if(answer == 'exist')
					alert('Такая ссылка уже есть');
				else{
					show_news_website(answer);
					show_alert('Новость сохранена...');
				}
			}
		});
	}
}

function show_websites_news(){
	$('.menu-news li').removeClass('active');
	$('.menu-news .show-news').addClass('active');
	var str = 'func=show_websites_news';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="list-group">';
			for(var id in data){
				var news = data[id];
				html+= '<div class="list-group-item"><button class="btn btn-info btn-sm pull-right" onclick="show_news_website(' +id+ ')"><i class="fa fa-angle-double-right"></i> Перейти</button><h4 class="list-group-item-heading">' +news['website']+ ' - ' +news['count']+ '</h4><div class="clearfix"></div></div>';
			}
			html+= '</div>';
			$('.news-content').html(html);
		}
	});
}

function show_news_website(website){
	var str = 'func=show_news_website&website=' + website;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="panel panel-success"><div class="panel-heading"><h4 class="list-group-item-heading">' +data['url']+ '</h4></div><div class="panel-body images-website">';
			for(var id in data['images']){
				var src = data['images'][id];
				html+= '<img src="' +src+ '" class="img-thumbnail img-head" />';
			}
			html+= '</div><div class="panel-footer text-right"><button class="btn btn-info btn-sm" onclick="form_upload_document(' +website+ ', \'news\')"><i class="fa fa-file-image-o"></i> Добавить новую</button> <button class="btn btn-success btn-sm" onclick="upload_images_website(' +website+ ')"><i class="fa fa-cloud-upload"></i> Загрузить картинки</button> <button class="btn btn-success btn-sm" onclick="upload_news_website(' +website+ ')"><i class="fa fa-cloud-upload"></i> Обновить новости</button> <button class="btn btn-success btn-sm" onclick="upload_price_website(' +website+ ')"><i class="fa fa-rub"></i> Обновить цены</button></div></div>';
			for(var index in data['news']){
				var news = data['news'][index];
				var id = news['id'];
				var active = news['active'];
				var bgClass = 'default';
				var label = '<i class="fa fa-check-circle"></i> опубликовать';
				btnClass = 'success';
				if(active == 1){
					bgClass = 'primary';
					btnClass = 'default';
					label = '<i class="fa fa-trash-o"></i> снять с публикации';
				}
				html+= '<div class="panel panel-' +bgClass+ ' news-' +id+ '"><div class="panel-heading"><h4 class="list-group-item-heading">' +news['title']+ '</h4></div><div class="panel-body">' +news['text']+ '</div><div class="panel-footer text-right">' +news['date']+ ' <button class="btn btn-default btn-sm" onclick="edit_news(' +id+ ')"><i class="fa fa-pencil"></i> Изменить</button> <button class="btn btn-' +btnClass+ ' btn-sm check-btn" onclick="check_status_news(' +id+ ')">' +label+ '</button></div></div>';
			}
			$('.news-content').html(html);
		}
	});
}

function check_status_news(id){
	var str = 'func=check_status_news&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(check){
			if(check == 1){
				$('.news-'+id).removeClass('panel-default').addClass('panel-primary');
				$('.news-'+id+' .check-btn').removeClass('btn-success').addClass('btn-default').html('<i class="fa fa-trash-o"></i> снять с публикации');
			}else{
				$('.news-'+id).removeClass('panel-primary').addClass('panel-default');
				$('.news-'+id+' .check-btn').removeClass('btn-default').addClass('btn-success').html('<i class="fa fa-check-circle"></i> опубликовать');
			}

		}
	});
}

function edit_news(id){
	var str = 'func=edit_news&id=' + id+"&cache="+Math.random();
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="form-horizontal panel panel-default edit-news"><div class="panel-heading"><i class="fa fa-plus-circle"></i> Изменить новость</div><div class="panel-body"><div class="form-group"><label class="col-sm-2 control-label">Сайт</label><div class="col-sm-4"><input type="text" class="form-control" readonly value="' +data['website']+ '" /></div><label class="col-sm-2 control-label">Дата</label><div class="col-sm-4"><input type="text" class="form-control" readonly value="' +data['date']+ '" /></div></div><div class="form-group"><label class="col-sm-2 control-label">Заголовок</label><div class="col-sm-4"><input type="text" class="form-control title-news" value="' +escapeHtml(data['title'])+ '" /></div><label class="col-sm-2 control-label">Ссылка</label><div class="col-sm-4"><input type="text" class="form-control" readonly value="' +data['url']+ '" /></div></div><div class="form-group"><label class="col-sm-2 control-label">Картинка</label><div class="col-sm-4"><input type="text" class="form-control image-news" value="' +data['image']+ '" /></div></div><div class="form-group"><label class="col-sm-2 control-label">Описание<div class="label-meta"></div></label><div class="col-sm-10"><textarea class="form-control desc-news" style="height: 70px" onKeyPress="check_size_limit(\'.desc-news\', 200, \'.label-meta\')">' +data['description']+ '</textarea></div></div><div class="form-group form-group-margin"><label class="col-sm-2 control-label">Текст</label><div class="col-sm-10"><textarea class="form-control text-news" style="height: 200px">' +data['text']+ '</textarea></div></div></div><div class="panel-footer text-right"><button type="button" class="btn btn-success btn-sm" onclick="update_news(' +id+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div>';
			$('.news-content').html(html);
		}
	});
}

function update_news(id){
	var title = $('.edit-news .title-news').val();
	var text = $('.edit-news .text-news').val();
	var desc = $('.edit-news .desc-news').val();
	var image = $('.edit-news .image-news').val();
	if(!title)
		show_mistake('.edit-news .title-news', 'Укажите заголовок');
	else if(!text)
		show_warning('.edit-news .text-news', 'Введите текст статьи');
	else{
		var str = 'func=update_news&title=' + title + '&text=' + text + '&id=' + id + '&image=' + image + '&desc=' + desc;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				show_news_website();
				show_alert('Новость изменена...');
			}
		});
	}
}

function upload_news_website(id){
	var str = 'func=upload_news_website&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(a){
			console.log(a);
		}
	});
}

function upload_price_website(id){
	var str = 'func=upload_price_website&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(a){
			console.log(a);
		}
	});
}

function upload_images_website(id){
	var str = 'func=upload_images_website&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(a){
			console.log(a);
		}
	});
}

function see_office(){
	var str = 'func=see_office';
	select_menu('office_menu', '2');
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function edit_office(id){
	var str = 'func=edit_office&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function update_office(id){
	var name = $('.name').val();
	var address = $('.address').val();
	var telephone = $('.telephone').val();
	var present = $('.present').val();
	var present_text = $('.present_text').val();
	var post = $('.post').val();
	var image = $('.print_image').val();
	if(!name)
		show_warning('.edit-office', 'Введите название офиса');
	else{
		telephone = telephone.replace(/\+/g, 'plus');
		var str = 'func=update_office&id=' + id + '&name=' + name + '&address=' + address + '&telephone=' + telephone + '&present=' + present + '&present_text=' + present_text + '&post=' + post + '&image=' + image;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				see_office();
			}
		});
	}
}

function edit_office_bank(id){
	var str = 'func=edit_office_bank&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
		}
	});
}

function update_office_bank(id){
	var bank = $('.bank').val();
	var inn = $('.inn').val();
	var kpp = $('.kpp').val();
	var bik = $('.bik').val();
	var ks = $('.ks').val();
	var rs = $('.rs').val();
	var str = 'func=update_office_bank&id=' + id + '&bank=' + bank + '&rs=' + rs + '&ks=' + ks + '&bik=' + bik + '&inn=' + inn + '&kpp=' + kpp;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			see_office();
		}
	});
}

function sights(){
	select_menu('sights-open', '2');
	var str = 'func=show_sights_menu';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('#body').html(html);
			add_new_sight();
		}
	});
}

function add_new_sight(){
	$('.menu-sights li').removeClass('active');
	$('.menu-sights .new-sight').addClass('active');

	var str = 'func=add_new_sight';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.sights-content').html(html);

		  $('.add-new-sight *[name="image"], .add-new-sight *[name="slider"]').multUploader({
		    action:'mysql.php?func=multipart_upload',
		    fragmentSize:1024*1024,
				maxcount: 1,
		    contentType:['image/jpeg','image/png']
		  });

		  $('.add-new-sight *[name="photogallery"]').multUploader({
		    action:'mysql.php?func=multipart_upload',
		    fragmentSize:1024*1024,
		    contentType:['image/jpeg','image/png']
		  });		  

		}
	});
}

function save_sight(){
	var name = $('.add-new-sight .name').val();
	var description = $('.add-new-sight .description').val();
	var address = $('.add-new-sight .address').val();
	var latitude = $('.add-new-sight .latitude').val();
	var longitude = $('.add-new-sight .longitude').val();
	var place = $('.add-new-sight .place option:selected').val();
	if(!name)
		show_warning('.add-new-sight', 'Введите название');
	else if(!description)
		show_warning('.add-new-sight', 'Введите описание');
	else if(!latitude)
		show_warning('.add-new-sight', 'Укажите широту');
	else if(!longitude)
		show_warning('.add-new-sight', 'Укажите долготу');
	else if(!place)
		show_warning('.add-new-sight', 'Укажите расположение');
	else{

	  var $image = $('.add-new-sight').find('*[name="image"]');
	  var $imageMsg = $image.parent().find('.input-message-block');
	  var image = JSON.parse($image.val().trim());
	  $imageMsg.html("").removeClass('with-bottom-margin');

	  var $slider = $('.add-new-sight').find('*[name="slider"]');
	  var $sliderMsg = $slider.parent().find('.input-message-block');
	  var slider = JSON.parse($slider.val().trim());
	  $sliderMsg.html("").removeClass('with-bottom-margin');	  

	  var $photogallery = $('.add-new-sight').find('*[name="photogallery"]');
	  var $photogalleryMsg = $photogallery.parent().find('.input-message-block');
	  var photogallery = JSON.parse($photogallery.val().trim());
	  $photogalleryMsg.html("").removeClass('with-bottom-margin');	  	  


    $.ajax({
      type: 'POST',
      data: {
      	func: 'save_new_sight',
      	name: name,
				image: image,
				slider: slider,
				photogallery: photogallery,
				description: description,
				address: address,
				latitude: latitude,
				longitude: longitude,
				place: place
		  },
      dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
      	alert('Место добавлено');
        add_new_sight();
      },
      error: function(){
      	console.log('err');
      }
    });

		/*var str = 'func=save_new_sight&name=' + name + '&description=' + description + '&address=' + address + '&latitude=' + latitude + '&longitude=' + longitude + '&place=' + place + '&image=' + image;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				add_new_sight();
			}
		});*/
	}
}

function view_sights(){
	$('.menu-sights li').removeClass('active');
	$('.menu-sights .view-sights').addClass('active');
	show_loader_element('.sights-content');
	var str = 'func=view_sights';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.sights-content').html(html);
		}
	});
}

function del_sight(id){
	if (confirm("Точно удалить?")) {
		var str = 'func=del_sight&id=' + id;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				alert('Место удалено');
				view_sights();
			}
		});
	}
}

function edit_sight(id){
	var str = 'func=edit_sight&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.sights-content').html(html);

		  $('.edit-sight *[name="image"], .edit-sight *[name="slider"]').multUploader({
		    action:'mysql.php?func=multipart_upload',
		    fragmentSize:1024*1024,
				maxcount: 1,
		    contentType:['image/jpeg','image/png']
		  });

		  $('.edit-sight *[name="photogallery"]').multUploader({
		    action:'mysql.php?func=multipart_upload',
		    fragmentSize:1024*1024,
		    contentType:['image/jpeg','image/png']
		  });			  

		}
	});
}

function update_sight(id){
	var name = $('.edit-sight .name').val();
	var description = $('.edit-sight .description').val();
	var address = $('.edit-sight .address').val();
	var latitude = $('.edit-sight .latitude').val();
	var longitude = $('.edit-sight .longitude').val();
	var place = $('.edit-sight .place option:selected').val();
	if(!name)
		show_warning('.edit-sight', 'Введите название');
	else if(!description)
		show_warning('.edit-sight', 'Введите описание');
	else if(!latitude)
		show_warning('.edit-sight', 'Укажите широту');
	else if(!longitude)
		show_warning('.edit-sight', 'Укажите долготу');
	else if(!place)
		show_warning('.add-new-sight', 'Укажите расположение');
	else{

	  var $image = $('.edit-sight').find('*[name="image"]');
	  var $imageMsg = $image.parent().find('.input-message-block');
	  var image = JSON.parse($image.val().trim());
	  $imageMsg.html("").removeClass('with-bottom-margin');

	  var $slider = $('.edit-sight').find('*[name="slider"]');
	  var $sliderMsg = $slider.parent().find('.input-message-block');
	  var slider = JSON.parse($slider.val().trim());
	  $sliderMsg.html("").removeClass('with-bottom-margin');	  

	  var $photogallery = $('.edit-sight').find('*[name="photogallery"]');
	  var $photogalleryMsg = $photogallery.parent().find('.input-message-block');
	  var photogallery = JSON.parse($photogallery.val().trim());
	  $photogalleryMsg.html("").removeClass('with-bottom-margin');		 	  


    $.ajax({
      type: 'POST',
      data: {
      	func: 'update_sight',
      	id: id,
      	name: name,
				image: image,
				slider: slider,
				photogallery: photogallery,
				description: description,
				address: address,
				latitude: latitude,
				longitude: longitude,
				place: place
		  },
      dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
      	alert('Место изменено');
        view_sights();
      }
    });

		/*var str = 'func=update_sight&id=' + id + '&name=' + name + '&description=' + description + '&address=' + address + '&latitude=' + latitude + '&longitude=' + longitude + '&place=' + place;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				view_sights()
			}
		});*/
	}
}

function add_new_image_sight(id){
	var str = 'func=form_new_image&id=' + id + '&type=sight';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			activate_ajax_image('sight');
		}
	});
}

function upload_sights(){
	$('.menu-sights li').removeClass('active');
	$('.menu-sights .upload-sights').addClass('active');
	show_loader_element('.sights-content');
	var str = 'func=sync_objects_api&cache='+Math.random();
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.sights-content').html(html);
		}
	});
}

function check_request_user(id){
	var str = 'func=check_request_user&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(){
			$('.btn-bomb-'+id).remove();
		}
	});
}


function show_cabinet_object(){
	select_menu('account-object', 1);
	var str = 'func=show_cabinet_object';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
			search_cabinet_object();
		}
	});
}

function search_cabinet_object(){
	remove_all_windows();
	var find = $('.search-object').val();
	var str = 'func=search_cabinet_object&find=' + find;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('.object-cabinet-result').html(html);
		}
	});
}

function new_object_account(){
	var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Добавить новый аккаунт</h4></div><div class="modal-body form-horizontal new-object"><div class="form-group"><label class="col-sm-4 control-label">Логин</label><div class="col-sm-8"><input type="text" class="form-control login-object" /></div></div><div class="form-group form-group-margin"><label class="col-sm-4 control-label">Новый пароль</label><div class="col-sm-8"><input type="password" class="form-control pass-object" /></div></div></div><div class="modal-footer"><button class="btn btn-success btn-sm" onclick="save_object_account()"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
	show_modal(html);
}

function save_object_account(){
	var login = $('.login-object').val();
	var pass = $('.pass-object').val();
	if(!login)
		show_warning('.new-object', 'Не введен логин', false);
	else if(!pass)
		show_warning('.new-object', 'Не введен пароль', false);
	else{
		var str = 'func=save_object_account&login=' + login + '&pass=' + pass;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(answer){
				if(answer)
					search_cabinet_object();
				else
					show_warning('.new-object', 'Такой логин уже существует', false);
			}
		});
	}
}

function edit_object_account(id){
	var str = 'func=edit_object_account&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Изменить аккаунт</h4></div><div class="modal-body form-horizontal edit-object"><div class="form-group"><label class="col-sm-4 control-label">Логин</label><div class="col-sm-8"><input type="text" class="form-control login-object" value="' +data['login']+ '" /></div></div><div class="form-group"><label class="col-sm-4 control-label">Новый пароль</label><div class="col-sm-8"><input type="password" class="form-control pass-object" /></div></div><div class="form-group form-group-margin"><label class="col-sm-4 control-label">Email для уведомлений</label><div class="col-sm-8"><input type="text" class="form-control email-object" value="' +data['email']+ '" /></div></div></div><div class="modal-footer"><button class="btn btn-success btn-sm btn-update-object-account"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
			show_modal(html);
			$('.btn-update-object-account').click(function(){
				var login = $('.login-object').val();
				var pass = $('.pass-object').val();
				var email = $('.email-object').val();
				var str = 'func=update_object_account&id=' + id + '&login=' + login + '&pass=' + pass + '&email=' + email;
				$.ajax({
					type: 'POST',
					data: str,
					url: 'mysql.php',
					dataType: 'JSON',
					success: function(result){
						if(result == 1){
							remove_all_windows();
							$('.object-account-'+id+' .name-login').html(login);
						}else
							show_warning('.edit-object', 'Такой логин уже существует', false);
					}
				});
			});
		}
	});
}

function append_new_object_account(id){
	var str = 'func=append_new_object_account&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_object_account(id){
	var object = $('.id-object').attr('name');
	if(!object)
		show_warning('.append-object', 'Выберите объект', false);
	else{
		var str = 'func=save_new_object_account&id=' + id + '&object=' + object;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(answer){
				if(answer)
					show_cabinet_object();
				else
					show_warning('.append-object', 'Объект уже привязан к аккаунту', false);
			}
		});
	}
}

function delete_object_account(id){
	if(confirm('Удалить объект?')){
		var str = 'func=delete_object_account&id=' + id;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(){
				$('.object-'+id).remove();
			}
		});
	}
}

function check_changes_cabinet_object(){
	select_menu('check-object-menu', 1);
	var str = 'func=check_changes_cabinet_object';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function edit_description_object_account(id){
	var str = 'func=edit_description_object_account&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}


function update_description_object_account(id){
	var services = new Array();
	$('.edit-object input').each(function(){
		var value = this.value;
		if(value != ''){
			var id = $(this).attr('name');
			value = value.replace(new RegExp("'",'g'), "");
			value = value.replace(new RegExp("\"",'g'), "");
			services[id] = value;
		}
	});
	var service_string = JSON.stringify(services);
	//var objEditor = object_edit_description_editor;
	//var desc = objEditor.getData();
	var desc = $('#head-description').val();

	var obj = {
		"func": "update_description_object_account",
		"id": id,
		"service": service_string,
		"desc": desc
	};

	$.ajax({
		type: 'POST',
		data: $.param(obj),
		url: 'mysql.php',
		success: function(html){
			remove_all_windows();
			check_changes_cabinet_object();
		}
	});
}

function confirm_description_object_account(id){
	var str = 'func=confirm_description_object_account&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			console.log(html);
			check_changes_cabinet_object();
		}
	});
}

function see_groups(){
	select_menu('group-menu', 1);
	show_loader_element('#body');
	var str = 'func=see_groups';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function restart_sync() {
	select_menu('sync-reboot-menu', 1);
	show_loader_element('#body');
	var str = 'func=sync_reboot';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function add_new_group(id){
	var str = 'func=add_new_group';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function save_new_group(){
	var name = $('.name-group').val();
	if(!name)
		show_warning('.new-group', 'Укажите название группы', false);
	else{
		var str = 'func=save_new_group&name=' + name;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(html){
				see_groups();
			}
		});
	}
}

function edit_group(id){
	var str = 'func=edit_group&id=' + id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function update_group(id){
	var name = $('.name-group').val();
	if(!name)
		show_warning('.edit-group', 'Укажите название группы', false);
	else{
		var str = 'func=update_group&id=' + id + '&name=' + name;
		$.ajax({
			type: 'POST',
			data: str,
			url: 'mysql.php',
			success: function(html){
				see_groups();
			}
		});
	}
}

function select_similar_name_object(){
	var str = 'func=select_similar_name_object';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#result').html(html);
		}
	});
	show_loader_element('#result');
}

function show_request_object(){
	select_menu('new-request-object', 1);
	var str = 'func=show_request_object';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		dataType: 'JSON',
		success: function(data){
			var html = '';
			for(var index in data){
				var class_request = '';
				var object = data[index];
				if(object['status'] == 0)
					class_request = 'warning';
				html+= '<tr class="' +class_request+ '" onclick="show_card_request_object(' +object['id']+ ')">' +
					'<td width="30%">' +object['object']+ '</td>' +
					'<td width="40%">' +object['address']+ '</td>' +
					'<td width="30%">' +object['time']+ '</td>' +
				'</tr>';
			}
			if(html != '')
				html = '<table class="table table-hover">' +html+ '</table>';
			$('#body').html(html);
		}
	});
}

function show_card_request_object(object){
	var str = 'func=show_card_request_object&object=' + object;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function admin_rating_comment(){
	$('.menu-rating li').removeClass('active');
	$('.menu-rating .admin-comment-rating').addClass('active');
	var str = 'func=admin_rating_comment';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.rating-content').html(html);
		}
	});
}

function send_admin_rating_comment(){
	var bid = $('.admin-rating-comment .number-bid').val();
	var comment = $('.admin-rating-comment .rating-comment').val();
	var author = $('.admin-rating-comment .author').val();
	clear_mistake('.admin-rating-comment');
	if(!bid)
		show_mistake('.number-bid');
	else if(!comment)
		show_mistake('.rating-comment');
	else{
		var str = 'func=send_admin_rating_comment&bid=' + bid + '&author=' + author + '&comment=' + comment;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(html){
				$('.rating-content').html(html);
			}
		});
	}
}

function edit_request_object(id){
	var str = 'func=edit_request_object&id=' + id;
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

function delete_request_object(id){
	var str = 'func=delete_request_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			show_request_object();
		}
	});
}

function confirm_request_object(id){
	var str = 'func=confirm_request_object&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			show_request_object();
		}
	});
}

function confirm_request_object_wo_acc(id){
	var str = 'func=confirm_request_object_wo_acc&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			show_request_object();
		}
	});
}

function update_request_object(id){
	let form_data = $('.edit_request_object_form').serialize();
	let str = 'func=update_request_object&id=' + id + '&'+form_data;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			remove_all_windows();
			show_card_request_object(id);
		}
	});
}

function show_assign_email_object_account(account){
	var str = 'func=show_assign_email_object_account&account=' + account;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var body = '';
			for(var count in data){
				body+= '<div class="form-group"><label class="col-sm-6"><input type="radio" name="object-email" value="' +data[count]['email']+ '" /><strong>' +data[count]['email']+ '</strong>' +data[count]['note']+ '</label><div class="col-sm-6">' +data[count]['object']+ '</div></div>';
			}
			var html = '<div class="modal fade"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Выберите почту для уведомлений</h4></div><div class="form-horizontal list-group list-group-margin"><div class="list-group-item email-object">' +body+ '</div></div><div class="modal-footer"><button type="button" class="btn btn-success btn-sm btn-assign-mail"><i class="fa fa-envelope-o"></i> Присвоить</button></div></div></div></div>';
			show_modal(html);
			$('.btn-assign-mail').click(function(){
				var email = $('.email-object input:checked').val();
				if(!email){
					remove_all_windows();
					return;
				}
				var str = 'func=assign_email_object_account&account=' + account + '&email=' + email;
				$.ajax({
					url: 'mysql.php',
					type: 'POST',
					data: str,
					dataType: 'JSON',
					success: function(answer){
						remove_all_windows();
						if(answer == 1){
							$('.object-account-'+account+' .btn-assign-object-account').remove();
							$('.object-account-'+account+' .btn-form-login-object-account').removeAttr('disabled');
						}
					}
				});
			});
		}
	});
}

function show_object_qouta_admin(){
	var str = 'func=show_object_qouta_admin';
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="form-horizontal"><div class="form-group"><div class="col-sm-4" id="object_name"><input type="text" class="form-control" id="object" onkeyup="find_klient(event, \'object\', \'object\', \'use_object\')"></div><div class="col-sm-8"><button class="btn btn-success btn-lt" onclick="add_object_quota_admin()"><i class="fa fa-calendar-plus-o"></i> Добавить объект</button></div></div><div class="quota-object-admin">';
			for(var index in data){
				var object = data[index];
				var class_active = new Array('', '', '');
				var select_active = new Array('', '', '');
				class_active[object['check']] = 'active';
				select_active[object['check']] = 'checked';
				html+= '<div class="form-group object-quota-' +index+ '"><div class="col-sm-4">' +object['name']+ '</div><div class="col-sm-4 btn-group" data-toggle="buttons"><label class="btn btn-primary btn-lt ' +class_active[0]+ '"><input type="radio" name="object-' +index+ '" value="0" checked="' +select_active[0]+ '"> Без квоты мест</label><label class="btn btn-primary btn-lt ' +class_active[1]+ '"><input type="radio" name="object-' +index+ '" value="1" checked="' +select_active[1]+ '"> Travelline</label><label class="btn btn-primary btn-lt ' +class_active[2]+ '"><input type="radio" name="object-' +index+ '" value="2" checked="' +select_active[2]+ '"> Квота мест из ЛК</label><label class="btn btn-primary btn-lt ' +class_active[3]+ '"><input type="radio" name="object-' +index+ '" value="3" checked="' +select_active[3]+ '"> Профкурорт</label></div><div class="col-sm-4"><button class="btn btn-success btn-lt" onclick="update_status_qouta_object(' +index+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div>';
			}
			html+= "</div></div>";
			$('#body').html(html);
		}
	});
}

function select_object_profkurort(){
       select_menu('profkurort-menu', 1);
       var str = 'func=select_object_profkurort';
       $.ajax({
               url: 'mysql.php',
               type: 'POST',
               data: str,
               dataType: 'JSON',
               success: function(data){
                       var html = '<div class="form-horizontal"><div class="form-group"><div class="col-sm-12"><button class="btn btn-danger" onclick="select_objects_profkurort()">Загрузить санатории с Профкурорта</button></div></div>';
                       for(var index in data){
                               var object = data[index];
                               var status_update = 'disabled';
                               var status_room = 'disabled';
                               if(object['sync-id'] == 0)
                                       status_update = '';
                               else
                                       status_room = '';
                               html+= '<div class="form-group object-profkurort-' +index+ '"><div class="col-sm-4">' +object['name']+ '</div><div class="col-sm-4"><input type="text" class="form-control value-profkurort-id" onkeypress="validate_input()" value="' +object['sync-id']+ '" /></div><div class="col-sm-4"><button ' +status_update+ ' class="btn btn-success btn-lt" onclick="update_object_profkurort_id(' +index+ ')"><i class="fa fa-check-circle"></i> Сохранить</button> <button ' +status_room+ ' class="btn btn-info btn-lt" onclick="select_rooms_object_profkurort(' +index+ ')"><i class="fa fa-cubes"></i> Номерной фонд</button></div></div>';
                       }
                       html+= "</div>";
                       $('#body').html(html);
               }
       });
}

function select_objects_profkurort(){
       select_menu('profkurort-menu', 1);
       show_loader_element('#body');
       var str = 'func=select_objects_profkurort';
       $.ajax({
               url: 'mysql.php',
               type: 'POST',
               data: str,
               dataType: 'JSON',
               success: function(data){
                       if(data['ref']){
                               var html = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Код ошибки ' +data['ref']+ '. Ошибка ' +data['mes']+ '</div>';
                       }else{
                               var html = '<div class="list-group"><div class="form-horizontal">';
                               for(var index in data){
                                       var object = data[index];
                                       var id = object['objid'];
                                       html+= '<div class="list-group-item object-' +id+ '"><div class="form-group form-group-margin object-sync-block" room="' +index+ '"><div class="col-sm-5">' +object['objnam']+ '<address>' +object['objaddr']+ '</address></div>';
                                       if(object['sync'] == 0){
                                               html+= '<div class="col-sm-7 object-name-' +id+ '"><button class="btn btn-success btn-sm" onclick="sync_status_qouta_object_profkurort(' +id+ ')"><i class="fa fa-check-circle"></i> Синхронизировать</button></div>';
                                       }else
                                               html+= '<div class="col-sm-7">' +object['name']+ '</div>';
                                       html+= '</div></div>';
                               }
                               html+= '</div></div></div>';
                       }
                       $('#body').html(html);
               }
       });
}

function sync_status_qouta_object_profkurort(id){
       var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">Синхронизация</h4></div><div class="modal-body form-horizontal sync-object"><div class="form-group"><label class="col-sm-4 control-label">Объект в CRM</label><div class="col-sm-8" id="object_name"><input type="text" onkeyup="find_klient(event, \'object\', \'object\', \'use_object\')" id="object" class="form-control id-object" /></div></div></div><div class="modal-footer"><button class="btn btn-success btn-sm btn-update-status-quota"><i class="fa fa-check-circle"></i> Сохранить</button></div></div></div></div>';
       show_modal(html);
       $('.btn-update-status-quota').click(function(){
               var object = $('.id-object').attr('name');
               if(!object)
                       show_warning('.sync-object', 'Выберите объект', false);
               else{
                       var str = 'func=update_status_qouta_object&object=' + object + '&status=3&id=' + id;
                       $.ajax({
                               url: 'mysql.php',
                               type: 'POST',
                               data: str,
                               dataType: 'JSON',
                               success: function(object){
                                       $('.object-name-' +id).html(object);
                                       remove_all_windows();
                               }
                       });
               }
       });
}

function update_object_profkurort_id(object){
       var id = $('.object-profkurort-'+object+' .value-profkurort-id').val();
       var str = 'func=update_object_profkurort_id&object=' + object + '&id=' + id;
       $.ajax({
               url: 'mysql.php',
               type: 'POST',
               data: str,
               dataType: 'JSON',
               success: function(update){
                       if(update > 0){
                               $('.object-profkurort-'+object+' .value-profkurort-id').val(update);
                       }else
                               $('.object-profkurort-'+object+' .value-profkurort-id').val('');
               }
       });
}

function select_rooms_object_profkurort(object){
       select_menu('profkurort-menu', 1);
       var str = 'func=select_rooms_object_profkurort&object=' + object;
       $.ajax({
               url: 'mysql.php',
               type: 'POST',
               data: str,
	           dataType: 'JSON',
               success: function(data){
                       var html = '<div class="list-group"><div class="form-horizontal">';
                       for(var index in data['room']){
                               var room = data['room'][index];
                               var select = room['sync-id'];
                               html+= '<div class="list-group-item"><div class="form-group form-group-margin room-sync-block" room="' +index+ '"><div class="col-sm-6">' +room['name']+ '</div><div class="col-sm-6"><select class="form-control profkurort-room-select"><option value="0"></option>';
                               for(var ind in data['profkurort']){
                                       var room = data['profkurort'][ind];
                                       var id = room['catcod'];
                                       var name = room['catnam'];
                                       var selected = '';
                                       if(id == select)
                                               selected = 'selected';
                                       html+= '<option value="' +id+ '" ' +selected+ '>' +name+ '</option>';
                               }
                               html+= '</select></div></div></div>';
                       }
                       html+= '<div class="list-group-item"><button class="btn btn-danger" onclick="update_rooms_object_profkurort()"><i class="fa fa-check-circle"></i> Сохранить</button></div></div>';
                       $('#body').html(html);
                       $('.profkurort-room-select').each(function(){
                               var value = $(this).val();
                               if(value > 0)
                                       $('.profkurort-room-select option[value=' +value+ ']').attr('disabled', 'disabled');
                               $(this).find('option[value=' +value+ ']').removeAttr('disabled')
                       });

                       $('.profkurort-room-select').change(function(){
                               $('.profkurort-room-select option').removeAttr('disabled');
                               $('.profkurort-room-select').each(function(){
                                       var value = $(this).val();
                                       if(value > 0)
                                               $('.profkurort-room-select option[value=' +value+ ']').attr('disabled', 'disabled');
                                       $(this).find('option[value=' +value+ ']').removeAttr('disabled')
                               });
                       });
               }
       });
}

function update_rooms_object_profkurort(){
       var data = new Object();
       $('.room-sync-block').each(function(){
               var id = $(this).attr('room');
               data[id] = $(this).find('.profkurort-room-select').val();
       });
       var data = JSON.stringify(data);
       var str = 'func=update_rooms_object_profkurort&data=' + data;
       $.ajax({
               url: 'mysql.php',
               type: 'POST',
               data: str,
               success: function(){
                       alert('Сохранено');
               }
       });
}


function add_object_quota_admin(){
	var id = $('.id-object').attr('name');
	if(!$('.object-quota-' +id).length && id){
		var name = $('.id-object span').html();
		var html = '<div class="form-group object-quota-' +id+ '"><div class="col-sm-4">' +name+ '</div><div class="col-sm-4 btn-group" data-toggle="buttons"><label class="btn btn-primary btn-lt active"><input type="radio" name="object-' +id+ '" value="0" checked="checked"> Без квоты мест</label><label class="btn btn-primary btn-lt"><input type="radio" name="object-' +id+ '" value="1"> Travelline</label><label class="btn btn-primary btn-lt"><input type="radio" name="object-' +id+ '" value="2"> Квота мест из ЛК</label></div><div class="col-sm-4"><button class="btn btn-success btn-lt" onclick="update_status_qouta_object(' +id+ ')"><i class="fa fa-check-circle"></i> Сохранить</button></div></div>';
		$('.quota-object-admin').prepend(html);
	}
}

function update_status_qouta_object(object){
	var status = $('.object-quota-' +object+ ' input:checked').val();
	var str = 'func=update_status_qouta_object&object=' + object + '&status=' + status;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(){
			alert('Статус изменен');
		}
	});
}

function history_object_account(id){
	var str = 'func=history_object_account&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		dataType: 'JSON',
		success: function(data){
			var html = '<div class="modal fade"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button><h4 class="modal-title">История</h4></div><div class="modal-body form-horizontal"><table class="table table-condensed">';
			for(var index in data){
				var row = data[index];
				html+= '<tr>' +
					'<td>' +row['date']+ '</td>' +
					'<td>' +row['text']+ '</td>' +
					'</tr>';
			}
			html+= '</table></div></div></div></div>';
			show_modal(html);
		}
	});
}

function show_sites_list(){
  select_menu('sites-list', 1);
  var str = 'func=show_sites_list';
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      $('#body').html(html);
    }
  });
}

function add_new_site() {
	var str = 'func=edit_site';
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			$('.site-modal *[name="favicon"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*1024,
				maxcount: 1,
				contentType:['image/vnd.microsoft.icon','image/x-icon']
			});
		}
	});
}

function save_site() {
	var $button = $('.btn-save-new-site');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
	var $name = $modalBody.find('input[name="name"]');
	var $nameMsg = $name.parent().find('.input-message-block');
	var name = $name.val().trim();
	var $domain = $modalBody.find('input[name="domain"]');
  var $domainMsg = $domain.parent().find('.input-message-block');
  var id = parseInt($modalBody.find('*[name="id"]').val());
  var interface_style = parseInt($modalBody.find('*[name="interface_style"]').val())
	var main_bg_color = $modalBody.find('*[name="main-bg-color"]').val();
  var main_bg_color2 = $modalBody.find('*[name="main-bg-color2"]').val();
	var main_bg_color3 = $modalBody.find('*[name="main-bg-color3"]').val();

  var main_font_color = $modalBody.find('*[name="main-font-color"]').val();
  var main_font_color2 = $modalBody.find('*[name="main-font-color2"]').val();
  var main_link_color = $modalBody.find('*[name="main-link-color"]').val();

  var $branding_name = $modalBody.find('input[name="branding_name"]');
  var $branding_nameMsg = $branding_name.parent().find('.input-message-block');
  var branding_name = $branding_name.val().trim();

  var $branding_slogan = $modalBody.find('input[name="branding_slogan"]');
  var $branding_sloganMsg = $branding_slogan.parent().find('.input-message-block');
  var branding_slogan = $branding_slogan.val().trim();

  var $favicon = $modalBody.find('*[name="favicon"]');
  var $faviconMsg = $favicon.parent().find('.input-message-block');
  var favicon = JSON.parse($favicon.val().trim());
  $faviconMsg.html("").removeClass('with-bottom-margin');

  var head_code = $modalBody.find('*[name="head_code"]').val();
  var pre_body_code = $modalBody.find('*[name="pre_body_code"]').val();
  var post_body_code = $modalBody.find('*[name="post_body_code"]').val();

	var theme = $modalBody.find('*[name="theme"]').val();

  var robots = $modalBody.find('*[name="robots"]').val();

  var domain = $domain.val().trim();
  $nameMsg.html('');

	var $type = $modalBody.find('select[name="type"]');
	var $typeMsg = $type.parent().find('.input-message-block');
	var type = $type.val().trim();

	var $direction_id = $modalBody.find('select[name="direction_id"]');
	var $direction_idMsg = $direction_id.parent().find('.input-message-block');
	var direction_id = $direction_id.val().trim();

	var $region_id = $modalBody.find('select[name="region_id"]');
	var $region_idMsg = $region_id.parent().find('.input-message-block');
	var region_id = $region_id.val().trim();

	var $resorts_ids = $modalBody.find('input[name="resorts_ids"]');
	var $resorts_idsMsg = $resorts_ids.parent().find('.input-message-block');
	var resorts_ids = $resorts_ids.val().trim();

  var error = false;

  if(name.length > 0) {

	}
	else {
    $nameMsg.html('Это обязательное поле');
		$name.focus();
		error = true;
  }

  if(domain.length > 0) {

  }
  else {
    $domainMsg.html('Это обязательное поле');
    if(!error) {
      $domain.focus();
      error = true;
		}
  }

  if(branding_name.length > 0) {

  }
  else {
    $branding_nameMsg.html('Это обязательное поле');
    if(!error) {
      $branding_name.focus();
      error = true;
    }
  }

  if(!error) {
    show_loader_element($modalLoader);
    $modalBody.addClass('hidden');
    $button.prop('disabled',true);
    var obj = {
    	func: "save_site",
			name: name,
			domain: domain,
			robots: robots,
			favicon: favicon,
			id: id,
			main_bg_color: main_bg_color,
			main_bg_color2: main_bg_color2,
			main_bg_color3: main_bg_color3,
      main_font_color: main_font_color,
      main_font_color2: main_font_color2,
			main_link_color: main_link_color,
			interface_style: interface_style,
			head_code: head_code,
			pre_body_code: pre_body_code,
			post_body_code: post_body_code,
			branding_name: branding_name,
			branding_slogan: branding_slogan,
			resorts_ids: resorts_ids,
			type: type,
			direction_id: direction_id,
			region_id: region_id,
			theme: theme
    };
    $.ajax({
      type: 'POST',
      data: obj,
			dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
      	if(data['success']) {
      		remove_all_windows();
          show_sites_list();
        }
        else {
					$modalLoader.html('');
          $button.prop('disabled',false);
          $modalBody.removeClass('hidden');
					$modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
				}
      }
    });
	}

}

function save_site_icons() {
	var $button = $('.btn-save-site-icons');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
	var $favicon = $modalBody.find('*[name="favicon"]');
	var $faviconMsg = $favicon.parent().find('.input-message-block');
	var favicon = JSON.parse($favicon.val().trim());
	$faviconMsg.html("").removeClass('with-bottom-margin');

	var $logo = $modalBody.find('*[name="logo"]');
	var $logoMsg = $logo.parent().find('.input-message-block');
	var logo = JSON.parse($logo.val().trim());
	$logoMsg.html("").removeClass('with-bottom-margin');

	var $icon_16x16 = $modalBody.find('*[name="icon_16x16"]');
	var $icon_16x16Msg = $icon_16x16.parent().find('.input-message-block');
	var icon_16x16 = JSON.parse($icon_16x16.val().trim());
	$icon_16x16Msg.html("").removeClass('with-bottom-margin');

	var $icon_32x32 = $modalBody.find('*[name="icon_32x32"]');
	var $icon_32x32Msg = $icon_32x32.parent().find('.input-message-block');
	var icon_32x32 = JSON.parse($icon_32x32.val().trim());
	$icon_32x32Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_57x57 = $modalBody.find('*[name="icon_apple_57x57"]');
	var $icon_apple_57x57Msg = $icon_apple_57x57.parent().find('.input-message-block');
	var icon_apple_57x57 = JSON.parse($icon_apple_57x57.val().trim());
	$icon_apple_57x57Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_60x60 = $modalBody.find('*[name="icon_apple_60x60"]');
	var $icon_apple_60x60Msg = $icon_apple_60x60.parent().find('.input-message-block');
	var icon_apple_60x60 = JSON.parse($icon_apple_60x60.val().trim());
	$icon_apple_60x60Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_72x72 = $modalBody.find('*[name="icon_apple_72x72"]');
	var $icon_apple_72x72Msg = $icon_apple_72x72.parent().find('.input-message-block');
	var icon_apple_72x72 = JSON.parse($icon_apple_72x72.val().trim());
	$icon_apple_72x72Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_76x76 = $modalBody.find('*[name="icon_apple_76x76"]');
	var $icon_apple_76x76Msg = $icon_apple_76x76.parent().find('.input-message-block');
	var icon_apple_76x76 = JSON.parse($icon_apple_76x76.val().trim());
	$icon_apple_76x76Msg.html("").removeClass('with-bottom-margin');

	var $icon_96x96 = $modalBody.find('*[name="icon_96x96"]');
	var $icon_96x96Msg = $icon_96x96.parent().find('.input-message-block');
	var icon_96x96 = JSON.parse($icon_96x96.val().trim());
	$icon_96x96Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_114x114 = $modalBody.find('*[name="icon_apple_114x114"]');
	var $icon_apple_114x114Msg = $icon_apple_114x114.parent().find('.input-message-block');
	var icon_apple_114x114 = JSON.parse($icon_apple_114x114.val().trim());
	$icon_apple_114x114Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_120x120 = $modalBody.find('*[name="icon_apple_120x120"]');
	var $icon_apple_120x120Msg = $icon_apple_120x120.parent().find('.input-message-block');
	var icon_apple_120x120 = JSON.parse($icon_apple_120x120.val().trim());
	$icon_apple_120x120Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_144x144 = $modalBody.find('*[name="icon_apple_144x144"]');
	var $icon_apple_144x144Msg = $icon_apple_144x144.parent().find('.input-message-block');
	var icon_apple_144x144 = JSON.parse($icon_apple_144x144.val().trim());
	$icon_apple_144x144Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_152x152 = $modalBody.find('*[name="icon_apple_152x152"]');
	var $icon_apple_152x152Msg = $icon_apple_152x152.parent().find('.input-message-block');
	var icon_apple_152x152 = JSON.parse($icon_apple_152x152.val().trim());
	$icon_apple_152x152Msg.html("").removeClass('with-bottom-margin');

	var $icon_apple_180x180 = $modalBody.find('*[name="icon_apple_180x180"]');
	var $icon_apple_180x180Msg = $icon_apple_180x180.parent().find('.input-message-block');
	var icon_apple_180x180 = JSON.parse($icon_apple_180x180.val().trim());
	$icon_apple_180x180Msg.html("").removeClass('with-bottom-margin');

	var $icon_192x192 = $modalBody.find('*[name="icon_192x192"]');
	var $icon_192x192Msg = $icon_192x192.parent().find('.input-message-block');
	var icon_192x192 = JSON.parse($icon_192x192.val().trim());
	$icon_192x192Msg.html("").removeClass('with-bottom-margin');

	var id = parseInt($modalBody.find('*[name="id"]').val());

	var error = false;

	if(!error) {
		show_loader_element($modalLoader);
		$modalBody.addClass('hidden');
		$button.prop('disabled',true);
		var obj = {
			func: "save_site_icons",
			favicon: favicon,
			logo: logo,
			icon_16x16: icon_16x16,
			icon_32x32: icon_32x32,
			icon_apple_57x57: icon_apple_57x57,
			icon_apple_60x60: icon_apple_60x60,
			icon_apple_72x72: icon_apple_72x72,
			icon_apple_76x76: icon_apple_76x76,
			icon_96x96: icon_96x96,
			icon_apple_114x114: icon_apple_114x114,
			icon_apple_120x120: icon_apple_120x120,
			icon_apple_144x144: icon_apple_144x144,
			icon_apple_152x152: icon_apple_152x152,
			icon_apple_180x180: icon_apple_180x180,
			icon_192x192: icon_192x192,
			id: id
		};
		$.ajax({
			type: 'POST',
			data: obj,
			dataType: 'JSON',
			url: 'mysql.php',
			success: function(data){
				if(data['success']) {
					remove_all_windows();
				}
				else {
					$modalLoader.html('');
					$button.prop('disabled',false);
					$modalBody.removeClass('hidden');
					$modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
				}
			}
		});
	}

}


function save_site_tech() {
	var $button = $('.btn-save-site-icons');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');

	var id = parseInt($modalBody.find('*[name="id"]').val());

	var glue_css = parseInt($modalBody.find('*[name="glue_css"]').prop('checked')*1, 10);
	var glue_js = parseInt($modalBody.find('*[name="glue_js"]').prop('checked')*1, 10);
	var compress_css = parseInt($modalBody.find('*[name="compress_css"]').prop('checked')*1, 10);
	var compress_js = parseInt($modalBody.find('*[name="compress_js"]').prop('checked')*1, 10);

	var error = false;

	if(!error) {
		show_loader_element($modalLoader);
		$modalBody.addClass('hidden');
		$button.prop('disabled',true);
		var obj = {
			func: "save_site_tech",
			glue_css: glue_css,
			glue_js: glue_js,
			compress_css: compress_css,
			compress_js: compress_js,
			id: id
		};
		$.ajax({
			type: 'POST',
			data: obj,
			dataType: 'JSON',
			url: 'mysql.php',
			success: function(data){
				if(data['success']) {
					remove_all_windows();
				}
				else {
					$modalLoader.html('');
					$button.prop('disabled',false);
					$modalBody.removeClass('hidden');
					$modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
				}
			}
		});
	}

}

function show_sites_contents_list(site_id) {
	var type = 'all';
	var $type = $('#content-type-filter');
	var q = "";
	var $q = $('#content-text-filter');
	var sort = 'id';
	var $sort = $('#content-sort');
	var body2 = 0;
	var $body2 = $('#content-body2-filter');
	var $filterEmptyFieldName = $('#filter-empty-field-name');
	var $filterFieldHasString = $('#filter-field-has-string');

	if($type.length > 0) {
		type = $type.val();
	}

	if($q.length > 0) {
		q = $q.val().trim();
	}

	if($sort.length > 0) {
		sort = $sort.val();
	}

	if($body2.length > 0) {
		body2 = parseInt($body2.val(),10);
	}

	var str = {
		func:'show_sites_contents_list',
		site_id:site_id,
		type:type,
		q:q,
		sort: sort,
		body2: body2,
    filter_empty_field_name: $filterEmptyFieldName.val(),
		filter_field_has_string: $filterFieldHasString.val()
	};

  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      $('#body').html(html);
    }
  });
}

function show_sites_addresses_list(site_id) {
  var str = 'func=show_sites_addresses_list&site_id='+site_id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      $('#body').html(html);
    }
  });
}

function show_sites_menu_items_list(site_id) {
  var str = 'func=show_sites_menu_items_list&site_id='+site_id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      $('#body').html(html);
    }
  });
}

function show_sites_meta_templates_list(site_id) {
	var str = 'func=show_sites_meta_templates_list&site_id='+site_id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function show_sites_questions_list(site_id) {
	var str = 'func=show_sites_questions_list&site_id='+site_id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			$('#body').html(html);
		}
	});
}

function show_sites_phones_list(site_id) {
  var str = 'func=show_sites_phones_list&site_id='+site_id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      $('#body').html(html);
    }
  });
}

function add_new_sites_content(site_id) {
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
			 								'<div class="form-group">' +
                          '<label class="col-sm-2 control-label">Заголовок h1</label>' +
                          '<div class="col-sm-10">' +
                              '<input type="text" class="form-control" name="title_h1" maxlength="255">' +
                              '<div class="input-message-block" data-for="title_h1"></div>' +
                          '</div>' +
                      '</div>'	+
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Основная картинка</label>' +
												'<div class="col-sm-10">' +
													'<input type="file" class="form-control" name="image">' +
													'<div class="input-message-block" data-for="image"></div>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Тип</label>' +
												'<div class="col-sm-10">' +
													'<select class="form-control" name="type">' +
			 											'<option value="landing">Лэндинг</option>' +
			 											'<option value="page">Страница</option>'+
			 											'<option value="news">Новость</option>'+
			 											'<option value="photogallery">Фотогалерея</option>'+
			 											'<option value="module">Модуль бронирования</option>' +
			 											'<option value="settings">Настройки</option>' +
														'<option value="article">Статья</option>' +
			 											'<option value="info">Полезная информация</option>' +
			 											'<option value="advice">Советы эксперта</option>' +
			 											'<option value="blog_post">Блог</option>' +
			 											'<option value="aggregator">Агрегатор</option>' +
			 											'<option value="redirect">Редирект</option>' +
			 										'</select>'+
													'<div class="input-message-block" data-for="type"></div>'+
												'</div>' +
											'</div>' +
                      '<div class="form-group hidden">' +
                        '<label class="col-sm-2 control-label">Тип агрегатора</label>' +
                        '<div class="col-sm-10">'+
                          '<select class="form-control" name="rss">' +
                            '<option value="0">Страница</option>' +
                            '<option value="1">RSS</option>' +
                          '</select>' +
                        '</div>'+
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
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Адрес страницы</label>' +
												'<div class="col-sm-10">' +
													'<input type="text" class="form-control" name="path" maxlength="512">' +
													'<div class="input-message-block" data-for="path"></div>'+
												'</div>' +
											'</div>' +
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
                      '<div class="form-group">' +
												'<label class="col-sm-2 control-label">Второй заголовок (h2)</label>' +
												'<div class="col-sm-10">' +
													'<input type="text" class="form-control" name="title_h2" maxlength="255">' +
													'<div class="input-message-block" data-for="title_h2"></div>'+
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
                          '<label class="col-sm-2 control-label">ID объекта для отзывов</label>' +
                          '<div class="col-sm-10">' +
                              '<input type="text" class="form-control" name="reviews_objects" value="">' +
                              '<div class="input-message-block" data-for="reviews_objects"></div>' +
                          '</div>' +
                      '</div>' +
			 								'<div class="form-group">' +
                          '<label class="col-sm-2 control-label">Фотографии</label>' +
                          '<div class="col-sm-10">' +
                              '<div class="input-message-block" data-for="photogallery"></div>' +
                              '<input type="file" name="photogallery">' +
                          '</div>' +
                      '</div>' +
			 								'<div class="form-group">' +
                          '<label class="col-sm-2 control-label">Заголовок к фото</label>' +
                          '<div class="col-sm-10">' +
                              '<input type="text" class="form-control" name="photogallery_title" maxlength="255">' +
                              '<div class="input-message-block" data-for="photogallery_title"></div>' +
                          '</div>' +
                      '</div>' +
			 								'<div class="form-group">' +
                          '<label class="col-sm-2 control-label">Ориентация фото</label>'+
                          '<div class="col-sm-10">' +
                              '<select class="form-control" name="photogallery_orientation">' +
                                  '<option value="album">Альбомная</option>' +
                                  '<option value="book">Книжная</option>' +
                              '</select>' +
			 												'<div class="input-message-block" data-for="photogallery_orientation"></div>'+
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
                          '<label class="col-sm-2 control-label">Фото слайдера</label>' +
                          '<div class="col-sm-10">' +
                              '<div class="input-message-block" data-for="slider_photos"></div>' +
                              '<input type="file" name="slider_photos">' +
                          '</div>' +
                      '</div>' +
					  '<div class="form-group">' +
                          '<label class="col-sm-2 control-label">Фото слайдера (моб. версия)</label>' +
                          '<div class="col-sm-10">' +
                              '<div class="input-message-block" data-for="slider_photos_mobile"></div>' +
                              '<input type="file" name="slider_photos_mobile">' +
                          '</div>' +
                      '</div>' +
					   '<div class="form-group">' +
						   '<label class="col-sm-2 control-label">Тип слайдера</label>' +
						   '<div class="col-sm-10">' +
							   '<select class="form-control" name="slider_mode">' +
								   '<option value="0">Стандартный</option>' +
								   '<option value="1">Увеличенный по высоте</option>' +
							   '</select>' +
						   		'<div class="input-message-block" data-for="slider_mode"></div>' +
						   '</div>' +
					   '</div>' +
						'<div class="form-group">' +
                          '<label class="col-sm-2 control-label">Фото для фона</label>' +
                          '<div class="col-sm-10">' +
                              '<div class="input-message-block" data-for="page_bg"></div>' +
                              '<input type="file" name="page_bg">' +
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
												'<label class="col-sm-2 control-label">Анонс</label>' +
												'<div class="col-sm-10">' +
													'<textarea class="form-control" name="summary"></textarea>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Анонс для сниппетов</label>' +
												'<div class="col-sm-10">' +
													'<textarea class="form-control" name="snippet_summary"></textarea>'+
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
		});*/


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
	}
}

function b64EncodeUnicode(str) {
  // first we use encodeURIComponent to get percent-encoded UTF-8,
  // then we convert the percent encodings into raw bytes which
  // can be fed into btoa.
  return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
      function toSolidBytes(match, p1) {
        return String.fromCharCode('0x' + p1);
      }));
}

var Base64 = {

// private property
  _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

// public method for encoding
  encode : function (input) {
    var output = "";
    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
    var i = 0;

    input = Base64._utf8_encode(input);

    while (i < input.length) {

      chr1 = input.charCodeAt(i++);
      chr2 = input.charCodeAt(i++);
      chr3 = input.charCodeAt(i++);

      enc1 = chr1 >> 2;
      enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
      enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
      enc4 = chr3 & 63;

      if (isNaN(chr2)) {
        enc3 = enc4 = 64;
      } else if (isNaN(chr3)) {
        enc4 = 64;
      }

      output = output +
          this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
          this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

    }

    return output;
  },

// public method for decoding
  decode : function (input) {
    var output = "";
    var chr1, chr2, chr3;
    var enc1, enc2, enc3, enc4;
    var i = 0;

    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

    while (i < input.length) {

      enc1 = this._keyStr.indexOf(input.charAt(i++));
      enc2 = this._keyStr.indexOf(input.charAt(i++));
      enc3 = this._keyStr.indexOf(input.charAt(i++));
      enc4 = this._keyStr.indexOf(input.charAt(i++));

      chr1 = (enc1 << 2) | (enc2 >> 4);
      chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
      chr3 = ((enc3 & 3) << 6) | enc4;

      output = output + String.fromCharCode(chr1);

      if (enc3 != 64) {
        output = output + String.fromCharCode(chr2);
      }
      if (enc4 != 64) {
        output = output + String.fromCharCode(chr3);
      }

    }

    output = Base64._utf8_decode(output);

    return output;

  },

// private method for UTF-8 encoding
  _utf8_encode : function (string) {
    string = string.replace(/\r\n/g,"\n");
    var utftext = "";

    for (var n = 0; n < string.length; n++) {

      var c = string.charCodeAt(n);

      if (c < 128) {
        utftext += String.fromCharCode(c);
      }
      else if((c > 127) && (c < 2048)) {
        utftext += String.fromCharCode((c >> 6) | 192);
        utftext += String.fromCharCode((c & 63) | 128);
      }
      else {
        utftext += String.fromCharCode((c >> 12) | 224);
        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
        utftext += String.fromCharCode((c & 63) | 128);
      }

    }

    return utftext;
  },

// private method for UTF-8 decoding
  _utf8_decode : function (utftext) {
    var string = "";
    var i = 0;
    var c = c1 = c2 = 0;

    while ( i < utftext.length ) {

      c = utftext.charCodeAt(i);

      if (c < 128) {
        string += String.fromCharCode(c);
        i++;
      }
      else if((c > 191) && (c < 224)) {
        c2 = utftext.charCodeAt(i+1);
        string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
        i += 2;
      }
      else {
        c2 = utftext.charCodeAt(i+1);
        c3 = utftext.charCodeAt(i+2);
        string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
        i += 3;
      }

    }

    return string;
  }

};

function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function set_sites_content() {
  var $button = $('.btn-save-new-sites-content');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');

  var $title = $modalBody.find('input[name="title"]');
  var $titleMsg = $title.parent().find('.input-message-block');
  var title = $title.val().trim();
  $titleMsg.html('');


	var $title_h1 = $modalBody.find('input[name="title_h1"]');
	var $title_h1Msg = $title_h1.parent().find('.input-message-block');
	var title_h1 = $title_h1.val().trim();
	$title_h1Msg.html('');

  var $title_h2 = $modalBody.find('input[name="title_h2"]');
  var $title_h2Msg = $title_h2.parent().find('.input-message-block');
  var title_h2 = $title_h2.val().trim();
  $title_h2Msg.html('');

  var $rss_aggregator_link = $modalBody.find('input[name="rss_aggregator_link"]');
  var $rss_aggregator_linkMsg = $rss_aggregator_link.parent().find('.input-message-block');
  var rss_aggregator_link = $rss_aggregator_link.val().trim();
  $rss_aggregator_linkMsg.html('');

  var $rss_addition = $modalBody.find('textarea[name="rss_addition"]');
  var $rss_additionMsg = $rss_addition.parent().find('.input-message-block');
  var rss_addition = $rss_addition.val().trim();
  $rss_additionMsg.html('');

  var content_id = parseInt($modalBody.find('*[name="content_id"]').val());

  var $path = $modalBody.find('input[name="path"]');
  var $pathMsg = $path.parent().find('.input-message-block');
  var path = $path.val().trim();
  $pathMsg.html('');

	var $redirect_path = $modalBody.find('input[name="redirect_path"]');
	var $redirect_pathMsg = $redirect_path.parent().find('.input-message-block');
	var redirect_path = $redirect_path.val().trim();
	$redirect_pathMsg.html('');

  var $form_action = $modalBody.find('input[name="form_action"]');
  var $form_actionMsg = $form_action.parent().find('.input-message-block');
  var form_action = $form_action.val().trim();
  $form_actionMsg.html('');


  var $weight = $modalBody.find('input[name="weight"]');
  var $weightMsg = $weight.parent().find('.input-message-block');
  var weight = $weight.val().trim();
  $weightMsg.html('');

	var $sort = $modalBody.find('input[name="sort"]');
	var $sortMsg = $sort.parent().find('.input-message-block');
	var sort = $sort.val().trim();
	$sortMsg.html('');

  var $description = $modalBody.find('textarea[name="description"]');
  var $descriptionMsg = $description.parent().find('.input-message-block');
  var description = $description.val().trim();
  $descriptionMsg.html('');

  var $main_page_fix = $modalBody.find('*[name="main_page_fix"]');
  var main_page_fix;
  if($main_page_fix.prop('checked'))
		main_page_fix = 1;
  else
		main_page_fix = 0;


	var $status = $modalBody.find('*[name="status"]');
	var status;
	if($status.prop('checked'))
		status = 1;
	else
		status = 0;

	var $imgs_no_index = $modalBody.find('*[name="imgs_no_index"]');
	var imgs_no_index;
	if($imgs_no_index.prop('checked'))
		imgs_no_index = 1;
	else
		imgs_no_index = 0;	

  var $path_autogenerate = $modalBody.find('*[name="path_autogenerate"]');
  var path_autogenerate;
  if($path_autogenerate.prop('checked'))
    path_autogenerate = 1;
  else
    path_autogenerate = 0;

	var $rss_aggregation = $modalBody.find('*[name="rss_aggregation"]');
	var rss_aggregation;
	if($rss_aggregation.prop('checked'))
		rss_aggregation = 1;
	else
		rss_aggregation = 0;

  var $second_bg = $modalBody.find('*[name="second_bg"]');
  var second_bg;
  if($second_bg.prop('checked'))
    second_bg = 1;
  else
    second_bg = 0;

  var $summary = $modalBody.find('textarea[name="summary"]');
  var $summaryMsg = $summary.parent().find('.input-message-block');
  var summary = $summary.val().trim();
  $summaryMsg.html("");

  var $summary_cabinet = $modalBody.find('textarea[name="summary_cabinet"]');
  if (typeof $summary_cabinet.val() !== 'undefined') {
  	var summary_cabinet = $summary_cabinet.val().trim();
  } else {
	var summary_cabinet = '';
  }	

  var summary_cabinet_accept = $modalBody.find('.summary_cabinet_accept:checked').val();
  var summary_cabinet_not_accepted_reason = $modalBody.find('.summary_cabinet_not_accepted_reason').val();

	var $snippet_summary = $modalBody.find('textarea[name="snippet_summary"]');
	var $snippet_summaryMsg = $snippet_summary.parent().find('.input-message-block');
	var snippet_summary = $snippet_summary.val().trim();
	$snippet_summaryMsg.html("");

  var $map_code = $modalBody.find('textarea[name="map_code"]');
  var map_code = $map_code.val().trim();

  var $landing_info = $modalBody.find('textarea[name="landing_info"]');
  var landing_info = $landing_info.val().trim();


  var $slider_photos = $modalBody.find('*[name="slider_photos"]');
  var $slider_photosMsg = $slider_photos.parent().find('.input-message-block');
  var slider_photos = JSON.parse($slider_photos.val().trim());
  $slider_photosMsg.html("").removeClass('with-bottom-margin');

	var $slider_photos_mobile = $modalBody.find('*[name="slider_photos_mobile"]');
	var $slider_photos_mobileMsg = $slider_photos_mobile.parent().find('.input-message-block');
	var slider_photos_mobile = JSON.parse($slider_photos_mobile.val().trim());
	$slider_photos_mobileMsg.html("").removeClass('with-bottom-margin');

	var $slider_mode = $modalBody.find('*[name="slider_mode"]');
	var $slider_modeMsg = $slider_mode.parent().find('.input-message-block');
	var slider_mode = $slider_mode.val().trim();
	$slider_modeMsg.html('');

  var $photogallery = $modalBody.find('*[name="photogallery"]');
  var $photogalleryMsg = $photogallery.parent().find('.input-message-block');
  var photogallery = JSON.parse($photogallery.val().trim());
  $photogalleryMsg.html("").removeClass('with-bottom-margin');

  var $image = $modalBody.find('*[name="image"]');
  var $imageMsg = $image.parent().find('.input-message-block');
  var image = JSON.parse($image.val().trim());
  $imageMsg.html("").removeClass('with-bottom-margin');

  var $page_bg = $modalBody.find('*[name="page_bg"]');
  var $page_bgMsg = $page_bg.parent().find('.input-message-block');
  var page_bg = JSON.parse($page_bg.val().trim());
  $page_bgMsg.html("").removeClass('with-bottom-margin');

  var $module_object_id = $modalBody.find('input[name="module_object_id"]');
  var $module_object_idMsg = $module_object_id.parent().find('.input-message-block');
  var module_object_id = $module_object_id.val().trim();
  $module_object_idMsg.html('');

	var $direction_id = $modalBody.find('*[name="direction_id"]');
	var $direction_idMsg = $direction_id.parent().find('.input-message-block');
	var direction_id = parseInt($direction_id.val(),10);
	$direction_idMsg.html('');

	var $region_id = $modalBody.find('*[name="region_id"]');
	var $region_idMsg = $region_id.parent().find('.input-message-block');
	var region_id = parseInt($region_id.val(),10);
	$region_idMsg.html('');

	var $regional_direction_id = $modalBody.find('*[name="regional_direction_id"]');
	var $regional_direction_idMsg = $regional_direction_id.parent().find('.input-message-block');
	var regional_direction_id = parseInt($regional_direction_id.val(),10);
	$regional_direction_idMsg.html('');

	var $module_block = $modalBody.find('select[name="module_block"]');
	var $module_blockMsg = $module_block.parent().find('.input-message-block');
	var module_block = $module_block.val().trim();
	$module_blockMsg.html('');


  	var body = window.sites_content_body.getData();
	if (typeof window.sites_content_body_cabinet !== 'undefined') {
		var body_cabinet = window.sites_content_body_cabinet.getData();
	} else {
		var body_cabinet = '';
	}
	if (typeof $modalBody.find('.body_cabinet_accept:checked') !== 'undefined') {
		var body_cabinet_accept = $modalBody.find('.body_cabinet_accept:checked').val();
	} else {
		var body_cabinet_accept = 0;
	}
	if (typeof $modalBody.find('.body_cabinet_not_accepted_reason') !== 'undefined') {
		var body_cabinet_not_accepted_reason = $modalBody.find('.body_cabinet_not_accepted_reason').val();
	} else {
		var body_cabinet_not_accepted_reason = '';
	}
	var body2 = window.sites_content_body2.getData();
	if (typeof window.sites_content_body2_cabinet !== 'undefined') {
		var body2_cabinet = window.sites_content_body2_cabinet.getData(); 
	} else {
		var body2_cabinet = ''; 
	}
	if (typeof $modalBody.find('.body2_cabinet_accept:checked') !== 'undefined') {
		var body2_cabinet_accept = $modalBody.find('.body2_cabinet_accept:checked').val();
	} else {
		var body2_cabinet_accept = 0;
	}
	if (typeof $modalBody.find('.body2_cabinet_not_accepted_reason') !== 'undefined') {
		var body2_cabinet_not_accepted_reason = $modalBody.find('.body2_cabinet_not_accepted_reason').val();
	} else {
		var body2_cabinet_not_accepted_reason = '';
	}

	var site_id = $modalBody.find('*[name="site_id"]').val();
	var type = $modalBody.find('*[name="type"]').val();
	var rss = parseInt($modalBody.find('*[name="rss"]').val(),10);
	var keywords = $modalBody.find('*[name="keywords"]').val();
	var published = $modalBody.find('*[name="published"]').val();

	var $reviews_objects = $modalBody.find('input[name="reviews_objects"]');
	var $reviews_objectsMsg = $reviews_objects.parent().find('.input-message-block');
	var reviews_objects = $reviews_objects.val().trim();
	$reviews_objectsMsg.html('');

	var $resorts_ids = $modalBody.find('input[name="resorts_ids"]');
	var $resorts_idsMsg = $resorts_ids.parent().find('.input-message-block');
	var resorts_ids = $resorts_ids.val().trim();
	$resorts_idsMsg.html('');

	var $photogallery_title = $modalBody.find('input[name="photogallery_title"]');
	var $photogallery_titleMsg = $photogallery_title.parent().find('.input-message-block');
	var photogallery_title = $photogallery_title.val().trim();
	$photogallery_titleMsg.html('');

	var $breadcrumb_title = $modalBody.find('input[name="breadcrumb_title"]');
	var $breadcrumb_titleMsg = $breadcrumb_title.parent().find('.input-message-block');
	var breadcrumb_title = $breadcrumb_title.val().trim();
	$breadcrumb_titleMsg.html('');

	var $photogallery_orientation = $modalBody.find('*[name="photogallery_orientation"]');
	var $photogallery_orientationMsg = $photogallery_orientation.parent().find('.input-message-block');
	var photogallery_orientation = $photogallery_orientation.val().trim();
	$photogallery_orientationMsg.html('');

	var $aggregate_types = $modalBody.find('*[name="aggregate_types"]:checked');
	var $aggregate_typesFormG = $aggregate_types.closest('.form-group');
	var $aggregate_typesMsg = $modalBody.find('*[name="aggregate_types"]').parent().parent().find('.input-message-block');
	$aggregate_typesMsg.html("");
	var aggregate_types = [], aggrI;

	var $aggregation_by_dates = $('.sites-content-modal *[name="aggregation_by_dates"]');
	var $aggregation_by_datesFormG = $aggregation_by_dates.closest('.form-group');
	var aggregation_by_dates = parseInt($aggregation_by_dates.val(), 10);

	var $aggregation_date_start = $('.sites-content-modal *[name="aggregation_date_start"]');
	var $aggregation_date_startFormG = $aggregation_date_start.closest('.form-group');
	var aggregation_date_start = $aggregation_date_start.val();

	var $aggregation_date_end = $('.sites-content-modal *[name="aggregation_date_end"]');
	var $aggregation_date_endFormG = $aggregation_date_end.closest('.form-group');
	var aggregation_date_end = $aggregation_date_end.val();

	var head_code = $modalBody.find('*[name="head_code"]').val();
	var pre_body_code = $modalBody.find('*[name="pre_body_code"]').val();
	var post_body_code = $modalBody.find('*[name="post_body_code"]').val();
	var phone = $modalBody.find('*[name="phone"]').val().trim();


	for(aggrI = 0; aggrI < $aggregate_types.length; aggrI++) {
		aggregate_types.push(parseInt($($aggregate_types.get(aggrI)).val(),10));
	}

  var error = false;

  if(title.length === 0) {
  	$titleMsg.html("Это обязательное поле");
  	if(!error) {
  		$title.focus();
      error = true;
    }
	}

  if(!(path_autogenerate && (type === 'blog_post' || type === 'news' || type === 'article' || type === 'advice' || type === 'info'))) {
    if(path.length === 0) {
      $pathMsg.html("Это обязательное поле");
      if(!error) {
        $path.focus();
        error = true;
      }
    }
    else if(path[0] !== '/') {
      $pathMsg.html("путь должен начинаться с /");
      if(!error) {
        $path.focus();
        error = true;
      }
    }
  }

  if(weight.length === 0) {
    $weightMsg.html("Это обязательное поле");
    if(!error) {
      $weight.focus();
      error = true;
    }
  }
  else {
  	weight = parseFloat(weight);
  	if(isNaN(weight) || weight < 0 || weight > 1) {
      $weightMsg.html("Введите число от 0 до 1");
      if(!error) {
        $weight.focus();
        error = true;
      }
		}
  }


  if(type === 'redirect') {
  	if(redirect_path.length === 0) {
			$redirect_pathMsg.html("Это обязательное поле");
			if(!error) {
				$redirect_path.focus();
				error = true;
			}
		}
	}

	if(type === 'module') {
    if(module_object_id.length === 0) {
      $module_object_idMsg.html("Это обязательное поле");
      if(!error) {
        $module_object_id.focus();
        error = true;
      }
    }
    else {
      module_object_id = parseInt(module_object_id);
      if(isNaN(module_object_id) || module_object_id <= 0) {
        $module_object_idMsg.html("Некорректный ID");
        if(!error) {
          $module_object_id.focus();
          error = true;
        }
      }
    }

    if(module_block.length === 0) {
      $module_blockMsg.html("Это обязательное поле");
      if(!error) {
        $module_block.focus();
        error = true;
      }
    }

	}
	else {
    module_object_id = 0;
    module_block = "";
	}

  if(type === 'landing') {

    if(title_h2.length === 0) {
      $title_h2Msg.html("Это обязательное поле");
      if(!error) {
        $title_h2.focus();
        error = true;
      }
    }

    /*if(jQuery.isEmptyObject(slider_photos)) {
      $slider_photosMsg.html("Нужно внести фотографии!").addClass('with-bottom-margin');
      if(!error) {
        $slider_photos.focus();
        error = true;
      }
    }*/

    if(form_action.length === 0) {
      $form_actionMsg.html("Это обязательное поле");
      if(!error) {
        $form_action.focus();
        error = true;
      }
    }
    else if(form_action[0] !== '/') {
      $form_actionMsg.html("путь должен начинаться с /");
      if(!error) {
        $form_action.focus();
        error = true;
      }
    }
  }
  else if(type !== 'settings' && type !== 'news' && type !== 'article' && type !== 'info' && type !== 'advice' && type !== 'blog_post' && type !== 'page') {
    form_action = '';
    title_h2 = '';
  }

  /*if(type === 'photogallery') {
    if(jQuery.isEmptyObject(photogallery)) {
      $photogalleryMsg.html("Нужно внести фотографии!").addClass('with-bottom-margin');
      if(!error) {
        $photogallery.focus();
        error = true;
      }
    }
  }*/

  if(type === 'aggregator') {
  	if($aggregate_types.length === 0) {
			$aggregate_typesMsg.html("Необходимо выбрать типы материалов для агрегации!");
  		if(!error) {
  			$($modalBody.find('*[name="aggregate_types"]').get(0)).focus();
  			error = true;
			}
		}

  	if(rss) {
      if(rss_aggregator_link.length === 0) {
        $rss_aggregator_linkMsg.html("Это обязательное поле");
        if(!error) {
          $rss_aggregator_link.focus();
          error = true;
        }
      }
      else if(rss_aggregator_link[0] !== '/') {
        $rss_aggregator_linkMsg.html("путь должен начинаться с /");
        if(!error) {
          $rss_aggregator_link.focus();
          error = true;
        }
      }
    }


	}

  if(!error) {
    show_loader_element($modalLoader);
    $modalBody.addClass('hidden');
    $button.prop('disabled',true);
    $.ajax({
      type: 'POST',
      data: {
      	func: 'set_sites_content',
		title: title,
		title_h1: title_h1,
        title_h2: title_h2,
		aggregate_types: aggregate_types,
        description: description,
		slider_photos: slider_photos,
		slider_photos_mobile: slider_photos_mobile,
		direction_id: direction_id,
		sort: sort,
		region_id: region_id,
		regional_direction_id: regional_direction_id,
        photogallery: photogallery,
		photogallery_title: photogallery_title,
		photogallery_orientation: photogallery_orientation,
		slider_mode: slider_mode,
		breadcrumb_title: breadcrumb_title,
        body: body,
		body_cabinet: body_cabinet,
		body_cabinet_accept: body_cabinet_accept,
		body_cabinet_not_accepted_reason: body_cabinet_not_accepted_reason,
		body2: body2,
		body2_cabinet: body2_cabinet,
		body2_cabinet_accept: body2_cabinet_accept,
		body2_cabinet_not_accepted_reason: body2_cabinet_not_accepted_reason,
	  	head_code: head_code,
	  	pre_body_code: pre_body_code,
	  	post_body_code: post_body_code,
		phone: phone,
        site_id: site_id,
		image: image,
		page_bg: page_bg,
		second_bg: second_bg,
		type: type,
		keywords: keywords,
        published: published,
		path: path,
		redirect_path: redirect_path,
        form_action: form_action,
		summary: summary,
		summary_cabinet: summary_cabinet,
		summary_cabinet_accept: summary_cabinet_accept,
		summary_cabinet_not_accepted_reason: summary_cabinet_not_accepted_reason,
		snippet_summary: snippet_summary,
		status: status,
		imgs_no_index: imgs_no_index,
        path_autogenerate: path_autogenerate,
		content_id: content_id,
		weight: weight,
        module_object_id: module_object_id,
        module_block: module_block,
		map_code: map_code,
        landing_info: landing_info,
		reviews_objects: reviews_objects,
        resorts_ids: resorts_ids,
        rss: rss,
        rss_aggregator_link: rss_aggregator_link,
        rss_addition: rss_addition,
		rss_aggregation: rss_aggregation,
		main_page_fix: main_page_fix,
		aggregation_by_dates: aggregation_by_dates,
		aggregation_date_start: aggregation_date_start,
		aggregation_date_end: aggregation_date_end
	  },
      dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
        if(data['success']) {
          remove_all_windows();
          show_sites_contents_list(site_id);
        }
        else {
          $modalLoader.html('');
          $modalBody.removeClass('hidden');
          $button.prop('disabled',false);
          $modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
        }
      }
    });
  }

}


function save_sites_address() {
  var $button = $('.btn-save-sites-address');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
  var $title = $modalBody.find('input[name="title"]');
  var $titleMsg = $title.parent().find('.input-message-block');
  var title = $title.val().trim();
  var site_id = parseInt($modalBody.find('*[name="site_id"]').val());
  var id = parseInt($modalBody.find('*[name="id"]').val());

  $titleMsg.html('');



  var $sort = $modalBody.find('input[name="sort"]');
  var $sortMsg = $sort.parent().find('.input-message-block');
  var sort = $sort.val().trim();
  $sortMsg.html('');

  var $description = $modalBody.find('textarea[name="description"]');
  var $descriptionMsg = $description.parent().find('.input-message-block');
  var description = $description.val().trim();
  $descriptionMsg.html('');

  var $status = $modalBody.find('*[name="status"]');
  var status;
  if($status.prop('checked'))
    status = 1;
  else
    status = 0;

  var error = false;

  if(title.length === 0) {
    $titleMsg.html("Это обязательное поле");
    if(!error) {
      $title.focus();
      error = true;
    }
  }

  if(sort.length === 0) {
    $sortMsg.html("Это обязательное поле");
    if(!error) {
      $sort.focus();
      error = true;
    }
  }
  else {
    sort = parseInt(sort);
    if(isNaN(sort)) {
      $sortMsg.html("Введите любое целое число");
      if(!error) {
        $sort.focus();
        error = true;
      }
    }
  }


  if(!error) {
    show_loader_element($modalLoader);
    $modalBody.addClass('hidden');
    $button.prop('disabled',true);
    $.ajax({
      type: 'POST',
      data: {
        func: 'save_sites_address',
        title: title,
        description: description,
				id:id,
				sort: sort,
        status: status,
        site_id: site_id
      },
      dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
        if(data['success']) {
          remove_all_windows();
          show_sites_addresses_list(site_id);
        }
        else {
          $modalLoader.html('');
          $modalBody.removeClass('hidden');
          $button.prop('disabled',false);
          $modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
        }
      }
    });
  }

}

function save_sites_menu_item() {
  var $button = $('.btn-save-sites-menu-item');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');

  var $name = $modalBody.find('input[name="name"]');
  var $nameMsg = $name.parent().find('.input-message-block');
  var name = $name.val().trim();
  $nameMsg.html('');

  var $href = $modalBody.find('input[name="href"]');
  var $hrefMsg = $href.parent().find('.input-message-block');
  var href = $href.val().trim();
  $hrefMsg.html('');

  var site_id = parseInt($modalBody.find('*[name="site_id"]').val());
  var menu_id = parseInt($modalBody.find('*[name="menu_id"]').val());
	var parent_id = parseInt($modalBody.find('*[name="parent_id"]').val());
  var id = parseInt($modalBody.find('*[name="id"]').val());



  var $sort = $modalBody.find('input[name="sort"]');
  var $sortMsg = $sort.parent().find('.input-message-block');
  var sort = $sort.val().trim();
  $sortMsg.html('');

  var $status = $modalBody.find('*[name="status"]');
  var status;
  if($status.prop('checked'))
    status = 1;
  else
    status = 0;


  var $main = $modalBody.find('*[name="main"]');
  var main;
  if($main.prop('checked'))
    main = 1;
  else
    main = 0;

  var error = false;

  if(name.length === 0) {
    $nameMsg.html("Это обязательное поле");
    if(!error) {
      $name.focus();
      error = true;
    }
  }

  if(href.length === 0) {
    $hrefMsg.html("Это обязательное поле");
    if(!error) {
      $href.focus();
      error = true;
    }
  }

  if(sort.length === 0) {
    $sortMsg.html("Это обязательное поле");
    if(!error) {
      $sort.focus();
      error = true;
    }
  }
  else {
    sort = parseInt(sort);
    if(isNaN(sort)) {
      $sortMsg.html("Введите любое целое число");
      if(!error) {
        $sort.focus();
        error = true;
      }
    }
  }


  if(!error) {
    show_loader_element($modalLoader);
    $modalBody.addClass('hidden');
    $button.prop('disabled',true);
    $.ajax({
      type: 'POST',
      data: {
        func: 'save_sites_menu_item',
        name: name,
        href: href,
        main: main,
        id:id,
        sort: sort,
        status: status,
        site_id: site_id,
        menu_id: menu_id,
				parent_id: parent_id
      },
      dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
        if(data['success']) {
          remove_all_windows();
          if(data['site_id'])
          	show_sites_menu_items_list(data['site_id']);
          else
          	show_sites_menu_items_list(site_id);
        }
        else {
          $modalLoader.html('');
          $modalBody.removeClass('hidden');
          $button.prop('disabled',false);
          $modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
        }
      }
    });
  }

}

function save_sites_phone() {
  var $button = $('.btn-save-sites-phone');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');

  var $title = $modalBody.find('input[name="title"]');
  var $titleMsg = $title.parent().find('.input-message-block');
  var title = $title.val().trim();
  $titleMsg.html('');

  var $number = $modalBody.find('input[name="number"]');
  var $numberMsg = $number.parent().find('.input-message-block');
  var number = $number.val().trim();
  $numberMsg.html('');

  var site_id = parseInt($modalBody.find('*[name="site_id"]').val());
  var block = $modalBody.find('*[name="block"]').val();
  var id = parseInt($modalBody.find('*[name="id"]').val());



  var $sort = $modalBody.find('input[name="sort"]');
  var $sortMsg = $sort.parent().find('.input-message-block');
  var sort = $sort.val().trim();
  $sortMsg.html('');

  var $status = $modalBody.find('*[name="status"]');
  var status;
  if($status.prop('checked'))
    status = 1;
  else
    status = 0;


  var $main = $modalBody.find('*[name="main"]');
  var main;
  if($main.prop('checked'))
    main = 1;
  else
    main = 0;

  var error = false;

  if(block === 'footer') {
    if(title.length === 0) {
      $titleMsg.html("Это обязательное поле для телефонов в подвале");
      if(!error) {
        $title.focus();
        error = true;
      }
    }
	}

  if(number.length === 0) {
    $numberMsg.html("Это обязательное поле");
    if(!error) {
      $number.focus();
      error = true;
    }
  }

  if(sort.length === 0) {
    $sortMsg.html("Это обязательное поле");
    if(!error) {
      $sort.focus();
      error = true;
    }
  }
  else {
    sort = parseInt(sort);
    if(isNaN(sort)) {
      $sortMsg.html("Введите любое целое число");
      if(!error) {
        $sort.focus();
        error = true;
      }
    }
  }


  if(!error) {
    show_loader_element($modalLoader);
    $modalBody.addClass('hidden');
    $button.prop('disabled',true);
    $.ajax({
      type: 'POST',
      data: {
        func: 'save_sites_phone',
        title: title,
        number: number,
        main: main,
        id:id,
        sort: sort,
        status: status,
        site_id: site_id,
        block: block
      },
      dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
        if(data['success']) {
          remove_all_windows();
          show_sites_phones_list(site_id);
        }
        else {
          $modalLoader.html('');
          $modalBody.removeClass('hidden');
          $button.prop('disabled',false);
          $modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
        }
      }
    });
  }

}


function save_sites_meta_template() {
	var $button = $('.btn-save-sites-meta-template');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');

	var $name = $modalBody.find('input[name="name"]');
	var $nameMsg = $name.parent().find('.input-message-block');
	var name = $name.val().trim();
	$nameMsg.html('');

	var $value = $modalBody.find('input[name="value"]');
	var $valueMsg = $value.parent().find('.input-message-block');
	var value = $value.val().trim();
	$valueMsg.html('');

	var site_id = parseInt($modalBody.find('*[name="site_id"]').val());
	var key = $modalBody.find('*[name="key"]').val();
	var type = $modalBody.find('*[name="type"]').val();
	var subtype = $modalBody.find('*[name="subtype"]').val();

	var id = parseInt($modalBody.find('*[name="id"]').val());


	var $status = $modalBody.find('*[name="status"]');
	var status;
	if($status.prop('checked'))
		status = 1;
	else
		status = 0;


	var error = false;

	if(name.length === 0) {
		$nameMsg.html("Это обязательное поле");
		if(!error) {
			$name.focus();
			error = true;
		}
	}

	if(value.length === 0) {
		$valueMsg.html("Это обязательное поле");
		if(!error) {
			$value.focus();
			error = true;
		}
	}


	if(!error) {
		show_loader_element($modalLoader);
		$modalBody.addClass('hidden');
		$button.prop('disabled',true);
		$.ajax({
			type: 'POST',
			data: {
				func: 'save_sites_meta_template',
				name: name,
				key: key,
				type: type,
				subtype: subtype,
				value: value,
				id:id,
				status: status,
				site_id: site_id
			},
			dataType: 'JSON',
			url: 'mysql.php',
			success: function(data){
				if(data['success']) {
					remove_all_windows();
					show_sites_meta_templates_list(site_id);
				}
				else {
					$modalLoader.html('');
					$modalBody.removeClass('hidden');
					$button.prop('disabled',false);
					$modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
				}
			}
		});
	}

}

function save_sites_question() {
	var $button = $('.btn-save-sites-question');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');

	var $title = $modalBody.find('input[name="title"]');
	var $titleMsg = $title.parent().find('.input-message-block');
	var title = $title.val().trim();
	$titleMsg.html('');

	var $text = $modalBody.find('*[name="text"]');
	var $textMsg = $text.parent().find('.input-message-block');
	var text = $text.val().trim();
	$textMsg.html('');

	var $answer = $modalBody.find('*[name="answer"]');
	var $answerMsg = $answer.parent().find('.input-message-block');
	var answer = $answer.val().trim();
	$answerMsg.html('');

	var $path = $modalBody.find('input[name="path"]');
	var $pathMsg = $path.parent().find('.input-message-block');
	var path = $path.val().trim();
	$pathMsg.html('');

	var $sort = $modalBody.find('input[name="sort"]');
	var $sortMsg = $sort.parent().find('.input-message-block');
	var sort = $sort.val().trim();
	$sortMsg.html('');

	var site_id = parseInt($modalBody.find('*[name="site_id"]').val());

	var id = parseInt($modalBody.find('*[name="id"]').val());


	var $status = $modalBody.find('*[name="status"]');
	var status;
	if($status.prop('checked'))
		status = 1;
	else
		status = 0;


	var error = false;


	if(text.length === 0) {
		$textMsg.html("Это обязательное поле");
		if(!error) {
			$text.focus();
			error = true;
		}
	}


	if(answer.length === 0) {
		$answerMsg.html("Это обязательное поле");
		if(!error) {
			$answer.focus();
			error = true;
		}
	}



	if(!error) {
		show_loader_element($modalLoader);
		$modalBody.addClass('hidden');
		$button.prop('disabled',true);
		$.ajax({
			type: 'POST',
			data: {
				func: 'save_sites_question',
				title: title,
				text: text,
				answer: answer,
				path: path,
				id:id,
				status: status,
				site_id: site_id,
				sort: sort
			},
			dataType: 'JSON',
			url: 'mysql.php',
			success: function(data){
				if(data['success']) {
					remove_all_windows();
					show_sites_questions_list(site_id);
				}
				else {
					$modalLoader.html('');
					$modalBody.removeClass('hidden');
					$button.prop('disabled',false);
					$modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
				}
			}
		});
	}

}


function edit_sites_content(id,copyMode) {
  if(typeof copyMode === 'undefined')
  	copyMode = 0;
  else {
  	copyMode = parseInt(copyMode,10);
  	if(copyMode !== 0 && copyMode !== 1) {
  		copyMode = 0;
		}
	}

	var str = {
  	func:'edit_sites_content',
		id:id,
		copy_mode: copyMode
  };


  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
      var sites_content_body_orig = $('#sites_content_body').val();
	  var sites_content_body_orig_cabinet = $('#sites_content_body_cabinet').val();
      var sites_content_body_orig2 = $('#sites_content_body2').val();
	  var sites_content_body_orig2_cabinet = $('#sites_content_body2_cabinet').val();

		$('#sites_content_body').replaceWith('<div id="sites_content_body"></div>');
		$('#sites_content_body_cabinet').replaceWith('<div id="sites_content_body_cabinet"></div>');
		$('#sites_content_body2').replaceWith('<div id="sites_content_body2"></div>');
		$('#sites_content_body2_cabinet').replaceWith('<div id="sites_content_body2_cabinet"></div>');


		DecoupledEditor
			.create( $('#sites_content_body').get(0), {
				language: 'ru'
			})
			.then( editor3 => {

				$('#sites_content_body').before('<div id="sites_content_body_toolbar_container"></div>');

				const toolbarContainer = $('#sites_content_body_toolbar_container').get(0);

				toolbarContainer.appendChild( editor3.ui.view.toolbar.element );

				window.sites_content_body = editor3;
				window.sites_content_body.setData(sites_content_body_orig);
			})
			.catch( error => {
				console.error( error );
			});

		DecoupledEditor
			.create( $('#sites_content_body_cabinet').get(0), {
				language: 'ru'
			})
			.then( editor4 => {

				$('#sites_content_body_cabinet').before('<div id="sites_content_body_cabinet_toolbar_container"></div>');

				const toolbarContainer_cabinet = $('#sites_content_body_cabinet_toolbar_container').get(0);

				toolbarContainer_cabinet.appendChild( editor4.ui.view.toolbar.element );

				window.sites_content_body_cabinet = editor4;
				window.sites_content_body_cabinet.setData(sites_content_body_orig_cabinet);
			})
			.catch( error => {
				console.error( error );
			});			

		DecoupledEditor
			.create( $('#sites_content_body2').get(0), {
				language: 'ru'
			})
			.then( editor5 => {

				$('#sites_content_body2').before('<div id="sites_content_body2_toolbar_container"></div>');

				const toolbarContainer2 = $('#sites_content_body2_toolbar_container').get(0);

				toolbarContainer2.appendChild( editor5.ui.view.toolbar.element );

				window.sites_content_body2 = editor5;
				window.sites_content_body2.setData(sites_content_body_orig2);
			})
			.catch( error => {
				console.error( error );
			});

		DecoupledEditor
			.create( $('#sites_content_body2_cabinet').get(0), {
				language: 'ru'
			})
			.then( editor6 => {

				$('#sites_content_body2_cabinet').before('<div id="sites_content_body2_cabinet_toolbar_container"></div>');

				const toolbarContainer2_cabinet = $('#sites_content_body2_cabinet_toolbar_container').get(0);

				toolbarContainer2_cabinet.appendChild( editor6.ui.view.toolbar.element );

				window.sites_content_body2_cabinet = editor6;
				window.sites_content_body2_cabinet.setData(sites_content_body_orig2_cabinet);
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

    }
  });
}

function edit_site(id) {
	var str = 'func=edit_site&id='+id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
      $('.site-modal *[name="favicon"]').multUploader({
        action:'mysql.php?func=multipart_upload',
        fragmentSize:1024*1024,
				maxcount: 1,
        contentType:['image/vnd.microsoft.icon','image/x-icon']
      });
    }
  });
}

function edit_site_icons(id) {
	var str = 'func=edit_site_icons&id='+id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
			$('.site-icons-modal *[name="favicon"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/vnd.microsoft.icon','image/x-icon']
			});
			$('.site-icons-modal *[name="logo"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png','image/svg+xml']
			});
			$('.site-icons-modal *[name="icon_16x16"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_32x32"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_57x57"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_60x60"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_72x72"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_76x76"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_96x96"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_114x114"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_120x120"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_144x144"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_152x152"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_apple_180x180"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

			$('.site-icons-modal *[name="icon_192x192"]').multUploader({
				action:'mysql.php?func=multipart_upload',
				fragmentSize:1024*512,
				maxcount: 1,
				contentType:['image/png']
			});

		}
	});
}

function edit_site_tech(id) {
  var str = 'func=edit_site_tech&id='+id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
    }
  });
}

function sites_address(id,site_id) {
	if(typeof id === 'undefined')
		id = 0;

	if(typeof site_id === 'undefined')
		site_id = 0;

  var str = 'func=sites_address&id='+id+"&site_id="+site_id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
    }
  });
}

function sites_menu_item(id,site_id,parent_id) {
  if(typeof id === 'undefined')
    id = 0;

  if(typeof site_id === 'undefined')
    site_id = 0;

	if(typeof parent_id === 'undefined')
		parent_id = 0;

	var str = 'func=sites_menu_item&id='+id+"&site_id="+site_id+'&parent_id='+parent_id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
    }
  });
}

function sites_meta_template(id, site_id) {
	if(typeof id === 'undefined')
		id = 0;

	if(typeof site_id === 'undefined')
		site_id = 0;

	var str = 'func=sites_meta_template&id='+id+"&site_id="+site_id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function sites_question(id, site_id) {
	if(typeof id === 'undefined')
		id = 0;

	if(typeof site_id === 'undefined')
		site_id = 0;

	var str = 'func=sites_question&id='+id+"&site_id="+site_id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function sites_phone(id,site_id) {
  if(typeof id === 'undefined')
    id = 0;

  if(typeof site_id === 'undefined')
    site_id = 0;

  var str = 'func=sites_phone&id='+id+"&site_id="+site_id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
    }
  });
}

function remove_sites_address(id) {
  var str = 'func=remove_sites_address&id='+id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
    }
  });
}

function remove_sites_menu_item(id) {
  var str = 'func=remove_sites_menu_item&id='+id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
    }
  });
}

function remove_sites_phone(id) {
	var str = 'func=remove_sites_phone&id='+id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function remove_sites_content(id) {
	var str = 'func=remove_sites_content&id='+id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}


function remove_sites_meta_template(id) {
	var str = 'func=remove_sites_meta_template&id='+id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function remove_sites_question(id) {
	var str = 'func=remove_sites_question&id='+id;
	$.ajax({
		type: 'POST',
		data: str,
		url: 'mysql.php',
		success: function(html){
			show_modal(html);
		}
	});
}

function remove_sites_content_success(id) {
	var $button = $('.btn-remove-sites-content-success');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
	var site_id = $modalBody.find('*[name="site_id"]');
	var str = 'func=remove_sites_content_success&id='+id+"&site_id="+site_id;
	$modalBody.addClass('hidden');
	show_loader_element($modalLoader);
	$.ajax({
		type: 'POST',
		data: str,
		dataType: 'JSON',
		url: 'mysql.php',
		success: function(data){
			if(data['success']) {
				show_sites_contents_list(site_id);
			}
			else alert("Ошибка при удалении");
			remove_all_windows();
		}
	});
}

function remove_sites_meta_template_success(id) {
	var $button = $('.btn-remove-sites-meta-template-success');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
	var site_id = $modalBody.find('*[name="site_id"]');
	var str = 'func=remove_sites_meta_template_success&id='+id+"&site_id="+site_id;
	$modalBody.addClass('hidden');
	show_loader_element($modalLoader);
	$.ajax({
		type: 'POST',
		data: str,
		dataType: 'JSON',
		url: 'mysql.php',
		success: function(data){
			if(data['success']) {
				show_sites_meta_templates_list(site_id);
			}
			else alert("Ошибка при удалении");
			remove_all_windows();
		}
	});
}

function remove_sites_question_success(id) {
	var $button = $('.btn-remove-sites-question-success');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
	var site_id = $modalBody.find('*[name="site_id"]');
	var str = 'func=remove_sites_question_success&id='+id+"&site_id="+site_id;
	$modalBody.addClass('hidden');
	show_loader_element($modalLoader);
	$.ajax({
		type: 'POST',
		data: str,
		dataType: 'JSON',
		url: 'mysql.php',
		success: function(data){
			if(data['success']) {
				show_sites_questions_list(site_id);
			}
			else alert("Ошибка при удалении");
			remove_all_windows();
		}
	});
}

function remove_sites_address_success(id) {
  var $button = $('.btn-remove-sites-address-success');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
  var site_id = $modalBody.find('*[name="site_id"]');
  var str = 'func=remove_sites_address_success&id='+id+"&site_id="+site_id;
  $modalBody.addClass('hidden');
  show_loader_element($modalLoader);
  $.ajax({
    type: 'POST',
    data: str,
		dataType: 'JSON',
    url: 'mysql.php',
    success: function(data){
      if(data['success']) {
      	show_sites_addresses_list(site_id);
			}
			else alert("Ошибка при удалении");
			remove_all_windows();
    }
  });
}

function remove_sites_menu_item_success(id) {
  var $button = $('.btn-remove-sites-menu-item-success');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
  var site_id = parseInt($modalBody.find('*[name="site_id"]').val());
  var str = 'func=remove_sites_menu_item_success&id='+id+"&site_id="+site_id;
  $modalBody.addClass('hidden');
  show_loader_element($modalLoader);
  $.ajax({
    type: 'POST',
    data: str,
    dataType: 'JSON',
    url: 'mysql.php',
    success: function(data){
      if(data['success']) {
        show_sites_menu_items_list(site_id);
      }
      else alert("Ошибка при удалении");
      remove_all_windows();
    }
  });
}

function remove_sites_phone_success(id) {
  var $button = $('.btn-remove-sites-phone-success');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
  var site_id = $modalBody.find('*[name="site_id"]');
  var str = 'func=remove_sites_phone_success&id='+id+"&site_id="+site_id;
  $modalBody.addClass('hidden');
  show_loader_element($modalLoader);
  $.ajax({
    type: 'POST',
    data: str,
    dataType: 'JSON',
    url: 'mysql.php',
    success: function(data){
      if(data['success']) {
        show_sites_phones_list(site_id);
      }
      else alert("Ошибка при удалении");
      remove_all_windows();
    }
  });
}


function sync_site(site_id) {
  if(typeof site_id === 'undefined')
    site_id = 0;

  var $button = $('.btn-sites-sync');
  var $panel = $button.closest('.panel');
  var $tableBody = $panel.find('.table-body');
  show_loader_element($tableBody);

  var str = 'func=sync_site&site_id='+site_id;
  $.ajax({
    type: 'POST',
    data: str,
    dataType: 'JSON',
    url: 'mysql.php',
    success: function (data) {
      if (data['success']) {
        remove_all_windows();
        if($('.addresses-panel').length > 0) {
			show_sites_addresses_list(site_id);
		}
        else if($('.sites-questions-panel').length > 0) {
        	show_sites_questions_list(site_id);
		}
        else
        	show_sites_contents_list(site_id);
      }
      else {
        $tableBody.html(data['msg']);
      }
    }
  });
}

$(document).on('change','.sites-content-modal select[name="type"]',function (e) {
	var type = $(this).val().trim();
	var $module_object_id = $('.sites-content-modal *[name="module_object_id"]');
  var $moduleObjectFormG = $module_object_id.closest('.form-group');

  var $module_block = $('.sites-content-modal *[name="module_block"]');
  var $moduleBlockFormG = $module_block.closest('.form-group');

  var $slider_photos = $('.sites-content-modal *[name="slider_photos"]');
  var $sliderPhotosFormG = $slider_photos.closest('.form-group');

	var $slider_photos_mobile = $('.sites-content-modal *[name="slider_photos_mobile"]');
	var $sliderPhotosMobileFormG = $slider_photos_mobile.closest('.form-group');

	var $slider_mode = $('.sites-content-modal *[name="slider_mode"]');
	var $slider_modeFormG = $slider_mode.closest('.form-group');

  var $form_action = $('.sites-content-modal *[name="form_action"]');
  var $form_actionFormG = $form_action.closest('.form-group');

	var $breadcrumb_title = $('.sites-content-modal *[name="breadcrumb_title"]');
	var $breadcrumb_titleFormG = $breadcrumb_title.closest('.form-group');

	var $description = $('.sites-content-modal *[name="description"]');
	var $descriptionFormG = $description.closest('.form-group');

	var $summary = $('.sites-content-modal *[name="summary"]');
	var $summaryFormG = $summary.closest('.form-group');

	var $snippet_summary = $('.sites-content-modal *[name="snippet_summary"]');
	var $snippet_summaryFormG = $snippet_summary.closest('.form-group');

	var $keywords = $('.sites-content-modal *[name="keywords"]');
	var $keywordsFormG = $keywords.closest('.form-group');

	var $map_code = $('.sites-content-modal *[name="map_code"]');
	var $map_codeFormG = $map_code.closest('.form-group');

	var $weight = $('.sites-content-modal *[name="weight"]');
	var $weightFormG = $weight.closest('.form-group');

	var $sort = $('.sites-content-modal *[name="sort"]');
	var $sortFormG = $sort.closest('.form-group');

	var $title_h1 = $('.sites-content-modal *[name="title_h1"]');
	var $title_h1FormG = $title_h1.closest('.form-group');

	var $redirect_path = $('.sites-content-modal *[name="redirect_path"]');
	var $redirect_pathFormG = $redirect_path.closest('.form-group');

  var $title_h2 = $('.sites-content-modal *[name="title_h2"]');
  var $title_h2FormG = $title_h2.closest('.form-group');

  var $landing_info = $('.sites-content-modal *[name="landing_info"]');
  var $landing_infoFormG = $landing_info.closest('.form-group');

  var $page_bg = $('.sites-content-modal *[name="page_bg"]');
  var $page_bgFormG = $page_bg.closest('.form-group');

  var $second_bg = $('.sites-content-modal *[name="second_bg"]');
  var $second_bgFormG = $second_bg.closest('.form-group');

  var $photogallery = $('.sites-content-modal *[name="photogallery"]');
  var $photogalleryFormG = $photogallery.closest('.form-group');

	var $image = $('.sites-content-modal *[name="image"]');
	var $imageFormG = $image.closest('.form-group');

	var $photogallery_title = $('.sites-content-modal *[name="photogallery_title"]');
	var $photogallery_titleFormG = $photogallery_title.closest('.form-group');

	var $photogallery_orientation = $('.sites-content-modal *[name="photogallery_orientation"]');
	var $photogallery_orientationFormG = $photogallery_orientation.closest('.form-group');

	var $reviews_objects = $('.sites-content-modal *[name="reviews_objects"]');
	var $reviews_objectsFormG = $reviews_objects.closest('.form-group');

	var $aggregate_types = $('.sites-content-modal *[name="aggregate_types"]');
	var $aggregate_typesFormG = $aggregate_types.closest('.form-group');

  var $resorts_ids = $('.sites-content-modal *[name="resorts_ids"]');
  var $resorts_idsFormG = $resorts_ids.closest('.form-group');


  var $rss = $('.sites-content-modal *[name="rss"]');
  var rss = parseInt($rss.val(),10);
  var $rssFormG = $rss.closest('.form-group');

	var $main_page_fix = $('.sites-content-modal *[name="main_page_fix"]');
	var main_page_fix = parseInt($rss.val(),10);
	var $main_page_fixFormG = $main_page_fix.closest('.form-group');

  var $rss_aggregator_link = $('.sites-content-modal *[name="rss_aggregator_link"]');
  var $rss_aggregator_linkFormG = $rss_aggregator_link.closest('.form-group');

  var $rss_addition = $('.sites-content-modal *[name="rss_addition"]');
  var $rss_additionFormG = $rss_addition.closest('.form-group');

	var $rss_aggregation = $('.sites-content-modal *[name="rss_aggregation"]');
	var $rss_aggregationFormG = $rss_aggregation.closest('.form-group');

  var $path_autogenerate = $('.sites-content-modal *[name="path_autogenerate"]');
  var $path_autogenerateFormG = $path_autogenerate.closest('.form-group');

  var $path = $('.sites-content-modal *[name="path"]');
  var $pathFormG = $path.closest('.form-group');

	var $body = $('.sites-content-modal *[name="body"]');
	var $bodyFormG = $body.closest('.form-group');

	var $body2 = $('.sites-content-modal *[name="body2"]');
	var $body2FormG = $body2.closest('.form-group');

	var $direction_id = $('.sites-content-modal *[name="direction_id"]');
	var $direction_idFormG = $direction_id.closest('.form-group');

	var $region_id = $('.sites-content-modal *[name="region_id"]');
	var $region_idFormG = $region_id.closest('.form-group');

	var $aggregation_by_dates = $('.sites-content-modal *[name="aggregation_by_dates"]');
	var $aggregation_by_datesFormG = $aggregation_by_dates.closest('.form-group');
	var aggregation_by_dates = parseInt($aggregation_by_dates.val(), 10);

	var $aggregation_date_start = $('.sites-content-modal *[name="aggregation_date_start"]');
	var $aggregation_date_startFormG = $aggregation_date_start.closest('.form-group');

	var $aggregation_date_end = $('.sites-content-modal *[name="aggregation_date_end"]');
	var $aggregation_date_endFormG = $aggregation_date_end.closest('.form-group');

	var $head_code = $('.sites-content-modal *[name="head_code"]');
	var $head_codeFormG = $head_code.closest('.form-group');

	var $post_body_code = $('.sites-content-modal *[name="post_body_code"]');
	var $post_body_codeFormG = $post_body_code.closest('.form-group');

	var $pre_body_code = $('.sites-content-modal *[name="pre_body_code"]');
	var $pre_body_codeFormG = $pre_body_code.closest('.form-group');

	var $phone = $('.sites-content-modal *[name="phone"]');
	var $phoneFormG = $phone.closest('.form-group');

	$aggregation_by_datesFormG.addClass('hidden');
	$aggregation_date_startFormG.addClass('hidden');
	$aggregation_date_endFormG.addClass('hidden');

	if(type === 'aggregator') {
		$aggregation_by_datesFormG.removeClass('hidden');
	}

	if(type === 'aggregator' && aggregation_by_dates) {
		$aggregation_date_startFormG.removeClass('hidden');
		$aggregation_date_endFormG.removeClass('hidden');
	}

	if(type === 'redirect') {
		$breadcrumb_titleFormG.addClass('hidden');
		$title_h1FormG.addClass('hidden');
		$imageFormG.addClass('hidden');
		$descriptionFormG.addClass('hidden');
		$summaryFormG.addClass('hidden');
		$snippet_summaryFormG.addClass('hidden');
		$keywordsFormG.addClass('hidden');
		$bodyFormG.addClass('hidden');
		$map_codeFormG.addClass('hidden');
		$weightFormG.addClass('hidden');
		$sortFormG.addClass('hidden');
		$redirect_pathFormG.removeClass('hidden');
		$head_codeFormG.addClass('hidden');
		$pre_body_codeFormG.addClass('hidden');
		$post_body_codeFormG.addClass('hidden');
		$phoneFormG.addClass('hidden');
	}
	else {
		$breadcrumb_titleFormG.removeClass('hidden');
		$title_h1FormG.removeClass('hidden');
		$imageFormG.removeClass('hidden');
		$descriptionFormG.removeClass('hidden');
		$summaryFormG.removeClass('hidden');
		$snippet_summaryFormG.removeClass('hidden');
		$keywordsFormG.removeClass('hidden');
		$bodyFormG.removeClass('hidden');
		$map_code.removeClass('hidden');
		$weightFormG.removeClass('hidden');
		$sortFormG.removeClass('hidden');
		$redirect_pathFormG.addClass('hidden');
		$head_codeFormG.removeClass('hidden');
		$pre_body_codeFormG.removeClass('hidden');
		$post_body_codeFormG.removeClass('hidden');
		$phoneFormG.removeClass('hidden');
	}

	if(type === 'aggregator')  {
		$aggregate_typesFormG.removeClass('hidden');
    $rssFormG.removeClass('hidden');
	}
	else {
		$aggregate_typesFormG.addClass('hidden');
    $rssFormG.addClass('hidden');
    $rss.val(0);
    rss = 0;
  }

	if(rss === 0) {
	  $rss_aggregator_linkFormG.addClass('hidden');
    $rss_additionFormG.addClass('hidden');

  }
	else {
    $rss_aggregator_linkFormG.removeClass('hidden');
    $rss_additionFormG.removeClass('hidden');
  }


	if(type === 'module') {
		$moduleObjectFormG.removeClass('hidden');
    $moduleBlockFormG.removeClass('hidden');
	}
	else {
		$moduleObjectFormG.addClass('hidden');
    $moduleBlockFormG.addClass('hidden');
	}

	if(type === 'landing') {
		$form_actionFormG.removeClass('hidden');
		$reviews_objectsFormG.removeClass('hidden');
	}
	else {
		$form_actionFormG.addClass('hidden');
		$reviews_objectsFormG.addClass('hidden');
	}

	if(type === 'landing' || type === 'settings') {
		$sliderPhotosFormG.removeClass('hidden');
		$slider_modeFormG.removeClass('hidden');
		$sliderPhotosMobileFormG.removeClass('hidden');
	}
	else {
		$sliderPhotosFormG.addClass('hidden');
		$slider_modeFormG.addClass('hidden');
		$sliderPhotosMobileFormG.addClass('hidden');
	}

  if(type === 'landing' || type === 'settings' || type === 'news' || type === 'article' || type === 'info' || type === 'advice' || type === 'blog_post' || type === 'page') {
    $sliderPhotosFormG.removeClass('hidden');
    $sliderPhotosMobileFormG.removeClass('hidden');
    $page_bgFormG.removeClass('hidden');
    $second_bgFormG.removeClass('hidden');
    $title_h2FormG.removeClass('hidden');
    $landing_infoFormG.removeClass('hidden');
		$body2FormG.removeClass('hidden');
	  	$slider_modeFormG.removeClass('hidden');
  }
  else {
    $page_bgFormG.addClass('hidden');
    $second_bgFormG.addClass('hidden');
    $title_h2FormG.addClass('hidden');
    $landing_infoFormG.addClass('hidden');
		$body2FormG.addClass('hidden');

  }

  if(type === 'photogallery' || type === 'landing' || type === 'news' || type === 'page' || type === 'settings' || type === 'article' || type === 'info' || type === 'advice' || type === 'blog_post') {
    $photogalleryFormG.removeClass('hidden');
		$photogallery_titleFormG.removeClass('hidden');
		$photogallery_orientationFormG.removeClass('hidden');
  }
  else {
    $photogalleryFormG.addClass('hidden');
		$photogallery_titleFormG.addClass('hidden');
		$photogallery_orientationFormG.addClass('hidden');
  }

  if(type === 'article' || type === 'news' || type === 'info' || type === 'advice' || type === 'blog_post') {
		$direction_idFormG.removeClass('hidden');
		$main_page_fixFormG.removeClass('hidden');
		$resorts_idsFormG.removeClass('hidden');
		$path_autogenerateFormG.removeClass('hidden');
		if($direction_id.val() > 0) {
			$region_idFormG.removeClass('hidden');
		}
		else {
			$region_idFormG.addClass('hidden');
		}
	}
  else {
		$direction_idFormG.addClass('hidden');
		$region_idFormG.addClass('hidden');
		$main_page_fixFormG.addClass('hidden');
    $resorts_idsFormG.addClass('hidden');
    $path_autogenerateFormG.addClass('hidden');
    $path_autogenerate.prop('checked',false);
    $path.prop('disabled', false);

  }

    if(type === 'settings' || type === 'article' || type === 'news' || type === 'info' || type === 'advice' || type === 'blog_post') {
    	$rss_aggregationFormG.removeClass('hidden');
			if(type === 'article' || type === 'news' || type === 'info' || type === 'advice' || type === 'blog_post') {
				$rss_aggregation.prop('checked',true);
			}
		}
    else {
			$rss_aggregationFormG.addClass('hidden');
			$rss_aggregation.prop('checked',false);
		}

});


$(document).on('change','.sites-content-modal select[name="rss"]',function (e) {
  var $rss = $('.sites-content-modal *[name="rss"]');
  var rss = parseInt($rss.val(),10);
  var $rssFormG = $rss.closest('.form-group');

  var $sort = $('.sites-content-modal *[name="sort"]');
  var sort = parseInt($sort.val(),10);
  var $sortFormG = $sort.closest('.form-group');

  var $weight = $('.sites-content-modal *[name="weight"]');
  var weight = parseInt($weight.val(),10);
  var $weightFormG = $weight.closest('.form-group');

  var $map_code = $('.sites-content-modal *[name="map_code"]');
  var map_code = parseInt($map_code.val(),10);
  var $map_codeFormG = $map_code.closest('.form-group');

  var $rss_aggregator_link = $('.sites-content-modal *[name="rss_aggregator_link"]');
  var $rss_aggregator_linkFormG = $rss_aggregator_link.closest('.form-group');

  var $rss_addition = $('.sites-content-modal *[name="rss_addition"]');
  var $rss_additionFormG = $rss_addition.closest('.form-group');

  var $body = $('.sites-content-modal *[name="body"]');
  var $bodyFormG = $body.closest('.form-group');

  var $summary = $('.sites-content-modal *[name="summary"]');
  var $summaryFormG = $summary.closest('.form-group');

	var $snippet_summary = $('.sites-content-modal *[name="snippet_summary"]');
	var $snippet_summaryFormG = $snippet_summary.closest('.form-group');

  var $keywords = $('.sites-content-modal *[name="keywords"]');
  var $keywordsFormG = $keywords.closest('.form-group');

  var $title_h1 = $('.sites-content-modal *[name="title_h1"]');
  var $title_h1FormG = $title_h1.closest('.form-group');

  var $breadcrumb_title = $('.sites-content-modal *[name="breadcrumb_title"]');
  var $breadcrumb_titleFormG = $breadcrumb_title.closest('.form-group');

  if(rss === 0) {
    $rss_aggregator_linkFormG.addClass('hidden');
    $rss_additionFormG.addClass('hidden');
    $sortFormG.removeClass('hidden');
    $weightFormG.removeClass('hidden');
    $map_codeFormG.removeClass('hidden');
    $bodyFormG.removeClass('hidden');
    $breadcrumb_titleFormG.removeClass('hidden');
    $title_h1FormG.removeClass('hidden');
    $keywordsFormG.removeClass('hidden');
    $summaryFormG.removeClass('hidden');
		$snippet_summaryFormG.removeClass('hidden');

  }
  else {
    $rss_aggregator_linkFormG.removeClass('hidden');
    $rss_additionFormG.removeClass('hidden');
    $sortFormG.addClass('hidden');
    $weightFormG.addClass('hidden');
    $map_codeFormG.addClass('hidden');
    $bodyFormG.addClass('hidden');
    $breadcrumb_titleFormG.addClass('hidden');
    $title_h1FormG.addClass('hidden');
    $keywordsFormG.addClass('hidden');
    $summaryFormG.addClass('hidden');
		$snippet_summaryFormG.addClass('hidden');
	}

});


$(document).on('change','.sites-content-modal select[name="aggregation_by_dates"]',function (e) {
	var $type = $('.sites-content-modal *[name="type"]');
	var $typeFormG = $type.closest('.form-group');
	var type = $type.val();

	var $aggregation_by_dates = $('.sites-content-modal *[name="aggregation_by_dates"]');
	var $aggregation_by_datesFormG = $aggregation_by_dates.closest('.form-group');
	var aggregation_by_dates = parseInt($aggregation_by_dates.val(), 10);

	var $aggregation_date_start = $('.sites-content-modal *[name="aggregation_date_start"]');
	var $aggregation_date_startFormG = $aggregation_date_start.closest('.form-group');

	var $aggregation_date_end = $('.sites-content-modal *[name="aggregation_date_end"]');
	var $aggregation_date_endFormG = $aggregation_date_end.closest('.form-group');

	$aggregation_date_startFormG.addClass('hidden');
	$aggregation_date_endFormG.addClass('hidden');

	if(type === 'aggregator' && aggregation_by_dates) {
		$aggregation_date_startFormG.removeClass('hidden');
		$aggregation_date_endFormG.removeClass('hidden');
	}

});


$(document).on('change','.site-modal select[name="type"]',function (e) {
	var type = $(this).val().trim();
	var $direction_id = $('.site-modal *[name="direction_id"]');
	var direction_id = parseInt($direction_id.val());
	var $direction_idFormG = $direction_id.closest('.form-group');
	var $region_id = $('.site-modal *[name="region_id"]');
	var $region_idFormG = $region_id.closest('.form-group');
	var $resorts_ids = $('.site-modal *[name="resorts_ids"]');
	var $resorts_idsFormG = $resorts_ids.closest('.form-group');

	$region_idFormG.addClass('hidden');

	if(type !== 'global') {
		$direction_idFormG.addClass('hidden');
		$region_idFormG.addClass('hidden');
	}
	else {
		$direction_idFormG.removeClass('hidden');
		if(direction_id > 0) {
			$region_idFormG.removeClass('hidden');
		}
	}

	if(type === 'objects') {
		$resorts_idsFormG.removeClass('hidden');
	}

});

$(document).on('change','.site-modal select[name="direction_id"]',function (e) {
	var $direction_id = $(this);
	var direction_id = parseInt($direction_id.val());
	var $direction_idFormG = $direction_id.closest('.form-group');
	var $region_id = $('.site-modal *[name="region_id"]');
	var $region_idFormG = $region_id.closest('.form-group');

	$region_idFormG.addClass('hidden');
	if(direction_id > 0) {
		$.ajax({
			type: 'GET',
			data: {
				func: 'get_regions_options',
				direction_id: direction_id
			},
			dataType: 'html',
			url: 'mysql.php',
			success: function (data) {
				$region_id.html(data);
				$region_idFormG.removeClass('hidden');
			}
		});
	}
	else {
		$region_id.val(0);
	}

});


$(document).on('change','.sites-content-modal select[name="direction_id"]',function (e) {
	var $obj = $(this);
	var direction = parseInt($obj.val());
	var $form = $obj.closest('.sites-content-modal');
	var $region_id = $form.find('select[name="region_id"]');
	var $region_idFormG = $region_id.closest('.form-group');
	var region_id = parseInt($region_id.val());

	var $regional_direction_id = $form.find('select[name="regional_direction_id"]');
	var $regional_direction_idFormG = $regional_direction_id.closest('.form-group');
	var regional_direction_id = parseInt($regional_direction_id.val(),10);
	$regional_direction_idFormG.addClass('hidden');
	$regional_direction_id.val(0);
	$regional_direction_id.find('*[value!="0"]').remove();

	$region_id.val(0);
	if(direction === 0) {
		$region_idFormG.addClass('hidden');
		region_id = 0;
		$region_id.find('*[value!="0"]').remove();
	}
	else {
		$.ajax({
			type: 'GET',
			data: {
				func: 'get_regions_options',
				direction_id: direction
			},
			dataType: 'html',
			url: 'mysql.php',
			success: function (data) {
				$region_id.html(data);
				$region_idFormG.removeClass('hidden');
			}
		});
	}
});

$(document).on('change','.sites-content-modal select[name="region_id"]',function (e) {
	var $obj = $(this);
	var region = parseInt($obj.val());
	var $form = $obj.closest('.sites-content-modal');

	var $regional_direction_id = $form.find('select[name="regional_direction_id"]');
	var $regional_direction_idFormG = $regional_direction_id.closest('.form-group');
	var regional_direction_id = parseInt($regional_direction_id.val());
	$regional_direction_id.val(0);
	$regional_direction_id.find('*[value!="0"]').remove();
	$regional_direction_idFormG.addClass('hidden');
	if(region > 0) {
		$.ajax({
			type: 'GET',
			data: {
				func: 'get_regions_directions_options',
				region_id: region
			},
			dataType: 'html',
			url: 'mysql.php',
			success: function (data) {
				$regional_direction_id.html(data);
				if($regional_direction_id.find('option').length > 1)
					$regional_direction_idFormG.removeClass('hidden');
			}
		});
	}
});

$(document).on('change','.sites-content-modal *[name="path_autogenerate"]',function (e) {
  var $type = $('.sites-content-modal *[name="type"]');
  var $typeFormG = $type.closest('.form-group');
  var type = $type.val();
  var $path = $('.sites-content-modal *[name="path"]');
  var $pathMsg = $path.parent().find('.input-message-block');
  var path = $path.val().trim();

  var $path_autogenerate = $('.sites-content-modal *[name="path_autogenerate"]');
  var $path_autogenerateFormG = $path_autogenerate.closest('.form-group');
  var path_autogenerate = $path_autogenerate.prop('checked');

  if(path_autogenerate && (type === 'article' || type === 'news' || type === 'info' || type === 'advice' || type === 'blog_post')) {
    $path.prop('disabled', true);
  }
  else {
    $path.prop('disabled', false);
  }
});