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
	var object = $('.id-object').attr('name');
	var date = $('#date').attr('date');
	var turist = $('.turist').val();
	var site = $('.site-from').val();
	if(clean && comfort && staff && leisure && location && treatment && ratio && object && date && turist){
		var str = 'func=save_new_rating&clean=' + clean + '&comfort=' + comfort + '&staff=' + staff + '&leisure=' + leisure + '&location=' + location + '&treatment=' + treatment + '&ratio=' + ratio + '&positive=' + positive + '&negative=' + negative + '&advice=' + advice + '&date=' + date + '&turist=' + turist + '&object=' + object + '&site=' + site;
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
		}
	});
}

function save_sight(){
	var name = $('.add-new-sight .name').val();
	var description = $('.add-new-sight .description').val();
	var address = $('.add-new-sight .address').val();
	var latitude = $('.add-new-sight .latitude').val();
	var longitude = $('.add-new-sight .longitude').val();
	if(!name)
		show_warning('.add-new-sight', 'Введите название');
	else if(!description)
		show_warning('.add-new-sight', 'Введите описание');
	else if(!latitude)
		show_warning('.add-new-sight', 'Укажите широту');
	else if(!longitude)
		show_warning('.add-new-sight', 'Укажите долготу');
	else{
		var str = 'func=save_new_sight&name=' + name + '&description=' + description + '&address=' + address + '&latitude=' + latitude + '&longitude=' + longitude;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				add_new_sight();
			}
		});
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

function edit_sight(id){
	var str = 'func=edit_sight&id=' + id;
	$.ajax({
		url: 'mysql.php',
		type: 'POST',
		data: str,
		success: function(html){
			$('.sights-content').html(html);
		}
	});
}

function update_sight(id){
	var name = $('.edit-sight .name').val();
	var description = $('.edit-sight .description').val();
	var address = $('.edit-sight .address').val();
	var latitude = $('.edit-sight .latitude').val();
	var longitude = $('.edit-sight .longitude').val();
	if(!name)
		show_warning('.edit-sight', 'Введите название');
	else if(!description)
		show_warning('.edit-sight', 'Введите описание');
	else if(!latitude)
		show_warning('.edit-sight', 'Укажите широту');
	else if(!longitude)
		show_warning('.edit-sight', 'Укажите долготу');
	else{
		var str = 'func=update_sight&id=' + id + '&name=' + name + '&description=' + description + '&address=' + address + '&latitude=' + latitude + '&longitude=' + longitude;
		$.ajax({
			url: 'mysql.php',
			type: 'POST',
			data: str,
			success: function(){
				view_sights()
			}
		});
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
	var str = 'func=upload_sights_on_server';
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
	var objEditor = object_edit_description_editor;
	var desc = objEditor.getData();

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
				var class_request = 'danger';
				var object = data[index];
				if(object['status'] == 0)
					class_request = '';
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
  var html = '<div class="modal fade">' +
								'<div class="modal-dialog">' +
									'<div class="modal-content">' +
										'<div class="modal-header">' +
											'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>' +
											'<h4 class="modal-title">Новый сайт</h4>' +
										'</div>' +
										'<div class="modal-body form-horizontal site-name">' +
											'<div class="form-group">' +
												'<label class="col-sm-4 control-label">Название</label>' +
												'<div class="col-sm-8">' +
													'<input type="text" class="form-control" name="name">' +
													'<div class="input-message-block" data-for="name"></div>'+
												'</div>' +
											'</div>' +
											'<div class="form-group">' +
												'<label class="col-sm-4 control-label">URL</label>' +
												'<div class="col-sm-8">' +
													'<input type="text" class="form-control site-url" name="url">' +
													'<div class="input-message-block" data-for="url"></div>'+
												'</div>' +
											'</div>' +
										'</div>' +
										'<div class="modal-loader"></div>'+
										'<div class="modal-footer">' +
											'<button class="btn btn-success btn-sm btn-save-new-site" onclick="save_new_site()" id="btn-save-new-site"><i class="fa fa-check-circle"></i> Добавить</button>' +
										'</div>' +
									'</div>' +
								'</div>' +
							'</div>';


	show_modal(html);
}

function save_new_site() {
	var $button = $('.btn-save-new-site');
	var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
	var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
	var $name = $modalBody.find('input[name="name"]');
	var $nameMsg = $name.parent().find('.input-message-block');
	var name = $name.val().trim();
	var $url = $modalBody.find('input[name="url"]');
  var $urlMsg = $url.parent().find('.input-message-block');

  var url = $url.val().trim();
  $nameMsg.html('');

  var error = false;

  if(name.length > 0) {

	}
	else {
    $nameMsg.html('Это обязательное поле');
		$name.focus();
		error = true;
  }

  if(url.length > 0) {

  }
  else {
    $urlMsg.html('Это обязательное поле');
    if(!error) {
      $url.focus();
      error = true;
		}
  }

  if(!error) {
    show_loader_element($modalLoader);
    $modalBody.addClass('hidden');
    $button.prop('disabled',true);
    var str = 'func=add_new_site&name='+name+"&url="+url;
    $.ajax({
      type: 'POST',
      data: str,
			dataType: 'JSON',
      url: 'mysql.php',
      success: function(data){
      	if(data['success']) {
      		remove_all_windows();
          show_sites_list();
        }
        else {
					$modalLoader.html('');
					$modalBody.removeClass('hidden');
					$modalBody.find('*[data-for="'+data['msg_field']+'"]').html(data['msg']);
				}
      }
    });
	}

}

function show_sites_contents_list(site_id) {
  var str = 'func=show_sites_contents_list&site_id='+site_id;
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
   var html = '<div class="modal fade">' +
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
												'<label class="col-sm-2 control-label">URL картинки</label>' +
												'<div class="col-sm-10">' +
													'<input type="text" class="form-control" name="image">' +
													'<div class="input-message-block" data-for="image"></div>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Адрес страницы</label>' +
												'<div class="col-sm-10">' +
													'<input type="text" class="form-control" name="path">' +
													'<div class="input-message-block" data-for="path"></div>'+
												'</div>' +
											'</div>' +
											'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Тип</label>' +
												'<div class="col-sm-10">' +
													'<select class="form-control" name="type">' +
			 											'<option value="page">Страница</option>'+
			 											'<option value="news">Новость</option>'+
			 										'</select>'+
													'<div class="input-message-block" data-for="type"></div>'+
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
												'<label class="col-sm-2 control-label">Содержимое</label>' +
												'<div class="col-sm-10">' +
													'<textarea class="form-control resizable-textarea" name="body" id="sites_content_body"></textarea>'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Дата и время публикации</label>' +
												'<div class="col-sm-10">' +
													'<input type="datetime-local" name="published" class="form-control">'+
												'</div>' +
											'</div>' +
			 								'<div class="form-group">' +
												'<label class="col-sm-2 control-label">Опубликовано</label>' +
												'<div class="col-sm-10">' +
													'<input type="checkbox" name="status" class="form-control">'+
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
  CKEDITOR.replace('sites_content_body');
}

function set_sites_content() {
  var $button = $('.btn-save-new-sites-content');
  var $modalBody = $button.closest('.modal-dialog').find('.modal-body');
  var $modalLoader = $button.closest('.modal-dialog').find('.modal-loader');
  var $title = $modalBody.find('input[name="title"]');
  var $titleMsg = $title.parent().find('.input-message-block');
  var title = $title.val().trim();
  var content_id = parseInt($modalBody.find('*[name="content_id"]').val());
  $titleMsg.html('');

  var $path = $modalBody.find('input[name="path"]');
  var $pathMsg = $path.parent().find('.input-message-block');
  var path = $path.val().trim();
  $pathMsg.html('');

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

  var $summary = $modalBody.find('textarea[name="summary"]');
  var $summaryMsg = $summary.parent().find('.input-message-block');
  var summary = $summary.val().trim();
  $summaryMsg.html();

  var body = CKEDITOR.instances.sites_content_body.getData();

  var imageUrl = $modalBody.find('*[name="image"]').val();
  var site_id = $modalBody.find('*[name="site_id"]').val();
  var type = $modalBody.find('*[name="type"]').val();
  var keywords = $modalBody.find('*[name="keywords"]').val();
  var published = $modalBody.find('*[name="published"]').val();

  var error = false;

  if(title.length === 0) {
  	$titleMsg.html("Это обязательное поле");
  	if(!error) {
  		$title.focus();
      error = true;
    }
	}

  if(path.length === 0) {
    $pathMsg.html("Это обязательное поле");
    if(!error) {
      $path.focus();
      error = true;
    }
  }

  if(!error) {
    show_loader_element($modalLoader);
    $modalBody.addClass('hidden');
    $button.prop('disabled',true);
    var str = 'func=set_sites_content&title='+title+"&description="+description+"&body="+body+"&site_id="+site_id+"&image="+imageUrl+"&type="+type+"&keywords="+keywords+"&published="+published+"&path="+path+"&summary="+summary+"&status="+status+"&content_id="+content_id;
    $.ajax({
      type: 'POST',
      data: str,
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

function edit_sites_content(id) {
  var str = 'func=edit_sites_content&id='+id;
  $.ajax({
    type: 'POST',
    data: str,
    url: 'mysql.php',
    success: function(html){
      show_modal(html);
      CKEDITOR.replace('sites_content_body');
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
        show_sites_contents_list(site_id);
      }
      else {
        $tableBody.html(data['msg']);
      }
    }
  });
}